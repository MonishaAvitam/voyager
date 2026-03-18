<?php
// fetch_combined_data.php

require './authentication.php'; // admin authentication check 
require './conn.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
}



// check admin
$user_role = $_SESSION['user_role'];




$sql2 = "SELECT sp.*, 
                pm.fullname AS subproject_manager_name, 
                c.contact_name, 
                c.contact_email, 
                c.contact_phone_number, 
                c.contact_id, 
                c.customer_id,
                c.customer_name  -- Add customer_name here
         FROM subprojects sp
         LEFT JOIN project_managers pm ON sp.project_manager = pm.fullname
         LEFT JOIN contacts c ON sp.contact_id = c.contact_id
         WHERE sp.urgency <> 'purple' 
         AND (sp.mark_as_completed <> 'Completed' OR sp.mark_as_completed IS NULL)";

// Apply role-based conditions (same logic as your code)
// Apply role-based conditions
if ($user_role == 2) {
    // Employees only see their assigned subprojects
    $sql2 .= " AND ((sp.assign_to_id = '$user_id' AND (sp.verify_status = '0' OR sp.verify_status IS NULL)) 
               OR sp.verify_by = '$user_id') 
               AND sp.assign_to_status = 1";
} 
// Role 3: managers see ALL subprojects → no extra condition


$sql2 .= " ORDER BY sp.project_id DESC";

// Fetch and combine data
$info_subprojects = $obj_admin->manage_all_info($sql2);

$combined_info = [];

while ($row = $info_subprojects->fetch(PDO::FETCH_ASSOC)) {
    $combined_info[] = $row;
}

// Sort by project_id or subproject_id
usort($combined_info, function ($a, $b) {
    return $b['project_id'] <=> $a['project_id'];
});

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($combined_info);


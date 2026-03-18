<?php
// fetch_combined_data.php

require '../../authentication.php'; // admin authentication check 
require '../../conn.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
}

// Queries for projects and subprojects
$sql = "SELECT 
            p.project_id, 
            p.project_name, 
            p.start_date, 
            p.end_date, 
            pm.fullname AS project_manager_name, 
            c.contact_name, 
            c.contact_email, 
            c.contact_phone_number, 
            c.contact_id, 
            c.customer_id, 
            c.customer_name,
            sp.table_id AS subproject_id,
            sp.subproject_name,
            sp.start_date,
            sp.sub_end_date,
            p.state,
            p.revision_project_id
        FROM projects p
        LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
        LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
        LEFT JOIN subprojects sp ON sp.project_id = p.project_id  
        WHERE p.urgency <> 'purple' AND p.end_date IS NOT NULL AND sp.sub_end_date IS NOT NULL";

$info_projects = $obj_admin->manage_all_info($sql);

$projects = [];

if ($info_projects) {
    while ($row = $info_projects->fetch(PDO::FETCH_ASSOC)) {
        $project_id = $row['project_id'];

        // If project is not in array, add it
        if (!isset($projects[$project_id])) {
            $projects[$project_id] = [
                'project_id' => $row['project_id'],
                'project_name' => $row['project_name'],
                'project_manager_name' => $row['project_manager_name'],
                'contact_name' => $row['contact_name'],
                'contact_email' => $row['contact_email'],
                'contact_phone_number' => $row['contact_phone_number'],
                'contact_id' => $row['contact_id'],
                'customer_id' => $row['customer_id'],
                'customer_name' => $row['customer_name'],
                'start_date' => $row['start_date'],
                'state'=> $row['state'],
                'end_date' => $row['end_date'],
                'revision_project_id' => null, // Main projects have no parent
                'subprojects' => []
            ];
        }

        // If it's a subproject, add it
        if (!empty($row['subproject_id'])) {
            $projects[$row['subproject_id']] = [
                'project_id' => $row['subproject_id'],
                'project_name' => $row['subproject_name'],
                'start_date' => $row['start_date'],
                'end_date' => $row['sub_end_date'],
                'state' => $row['state'],
                'revision_project_id' => $row['project_id'], // Parent ID
            ];
        }
    }

    // Convert associative array to indexed array
    $combined_info = array_values($projects);
} else {
    echo json_encode(["error" => "No data found"]);
    exit;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($combined_info);



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

// Cache settings
$cache_dir = './cache/';
$cache_file = $cache_dir . 'openProjectsCache.json';
$cache_lifetime = 300; // Cache duration in seconds (5 minutes)

// Ensure cache directory exists
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Check if cache exists and is valid
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_lifetime) {
    header('Content-Type: application/json');
    readfile($cache_file);
    exit();
}

// Queries for projects
$sql = "SELECT p.*, pm.fullname AS project_manager_name, 
               c.contact_name, c.contact_email, c.contact_phone_number, 
               c.contact_id, c.customer_id, c.customer_name
        FROM projects p
        LEFT JOIN project_managers pm ON p.project_manager = pm.fullname
        LEFT JOIN contacts c ON p.contact_id = c.contact_id
        WHERE p.urgency NOT IN ('purple', 'cancelled')";

$sql2 = "SELECT sp.*, pm.fullname AS subproject_manager_name, 
                c.contact_name, c.contact_email, c.contact_phone_number, 
                c.contact_id, c.customer_id, c.customer_name
         FROM subprojects sp
         LEFT JOIN project_managers pm ON sp.project_manager = pm.fullname
         LEFT JOIN contacts c ON sp.contact_id = c.contact_id
         WHERE sp.urgency NOT IN ('purple', 'cancelled')
         AND (sp.mark_as_completed <> 'Completed' OR sp.mark_as_completed IS NULL)";

// Apply role-based conditions
if ($user_role == 2) {
    $sql .= " AND ((p.assign_to_id = '$user_id' AND (p.verify_status = '0' OR p.verify_status IS NULL)) OR p.verify_by = '$user_id') AND p.assign_to_status = 1";
    $sql2 .= " AND ((sp.assign_to_id = '$user_id' AND (sp.verify_status = '0' OR sp.verify_status IS NULL)) OR sp.verify_by = '$user_id') AND sp.assign_to_status = 1";
} elseif ($user_role == 3) {
    $sql .= " AND (p.project_managers_id = '$user_id' OR p.verify_by = '$user_id' OR p.assign_to_id = '$user_id')";
    $sql2 .= " AND (sp.project_managers_id = '$user_id' OR sp.verify_by = '$user_id' OR sp.assign_to_id = '$user_id')";
}

$sql .= " ORDER BY p.project_id DESC";
$sql2 .= " ORDER BY sp.project_id DESC";

// Execute queries using MySQLi
$result1 = $conn->query($sql);
$result2 = $conn->query($sql2);

// Check for query errors
if (!$result1 || !$result2) {
    die(json_encode(['error' => 'Database query failed', 'message' => $conn->error]));
}

// Fetch results
$info_projects = [];
while ($row = $result1->fetch_assoc()) {
    $info_projects[] = $row;
}

$info_subprojects = [];
while ($row = $result2->fetch_assoc()) {
    $info_subprojects[] = $row;
}

// Combine project and subproject data
$combined_info = array_merge($info_projects, $info_subprojects);

// Sort by project_id or subproject_id
usort($combined_info, function ($a, $b) {
    return $b['project_id'] <=> $a['project_id'];
});

// Save data to cache
file_put_contents($cache_file, json_encode($combined_info, JSON_PRETTY_PRINT));

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($combined_info);
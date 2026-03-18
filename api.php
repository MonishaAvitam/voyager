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



// Queries for projects
$sql = "SELECT p.*, 
               cat.name AS category_name,
               pm.fullname AS project_manager_name, 
               c.contact_name, c.contact_email, c.contact_phone_number, 
               c.contact_id, c.customer_id, c.customer_name
        FROM projects p
        LEFT JOIN categories cat ON p.category_id = cat.id
        LEFT JOIN project_managers pm ON p.project_manager = pm.fullname
        LEFT JOIN contacts c ON p.contact_id = c.contact_id
        WHERE p.urgency NOT IN ('purple', 'cancelled')";


$sql2 = "SELECT sp.*, 
                cat.name AS category_name,
                pm.fullname AS subproject_manager_name, 
                c.contact_name, c.contact_email, c.contact_phone_number, 
                c.contact_id, c.customer_id, c.customer_name
         FROM subprojects sp
         LEFT JOIN projects p ON sp.project_id = p.project_id
         LEFT JOIN categories cat ON p.category_id = cat.id
         LEFT JOIN project_managers pm ON sp.project_manager = pm.fullname
         LEFT JOIN contacts c ON sp.contact_id = c.contact_id
         WHERE sp.urgency NOT IN ('purple', 'cancelled') 
           AND sp.outsourced = 0
           AND (sp.mark_as_completed <> 'Completed' OR sp.mark_as_completed IS NULL)";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    // Escape for safety (simple, but ideally use prepared statements)
    $search_esc = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');

    $sql .= " AND (
                p.project_id LIKE '%$search_esc%' OR 
                p.revision_project_id LIKE '%$search_esc%' OR
                p.project_name LIKE '%$search_esc%' OR
                p.team_lead LIKE '%$search_esc%' OR
                p.p_team LIKE '%$search_esc%' OR
                p.state LIKE '%$search_esc%' OR
                p.assign_to LIKE '%$search_esc%' OR
                c.customer_name LIKE '%$search_esc%'
            )";

    $sql2 .= " AND (
                sp.project_id LIKE '%$search_esc%' OR 
                sp.subproject_name LIKE '%$search_esc%' OR
                sp.team_lead LIKE '%$search_esc%' OR
                sp.assign_to LIKE '%$search_esc%' OR
                sp.p_team LIKE '%$search_esc%' OR
                c.customer_name LIKE '%$search_esc%'
            )";
}

// Apply role-based conditions (same logic as your code)
if ($user_role == 2) {
    $sql .= " AND ((p.assign_to_id = '$user_id' AND (p.verify_status = '0' OR p.verify_status IS NULL)) OR p.verify_by = '$user_id') AND p.assign_to_status = 1";
    $sql2 .= " AND ((sp.assign_to_id = '$user_id' AND (sp.verify_status = '0' OR sp.verify_status IS NULL)) OR sp.verify_by = '$user_id') AND sp.assign_to_status = 1";
}

$project_id_filter = isset($_GET['project_id']) ? trim($_GET['project_id']) : '';
if ($project_id_filter !== '') {
    $project_id_esc = htmlspecialchars($project_id_filter, ENT_QUOTES, 'UTF-8');
    $sql .= " AND p.project_id = '$project_id_esc'";
    $sql2 .= " AND sp.project_id = '$project_id_esc'";
}
$assign_to_filter = isset($_GET['assign_to']) ? trim($_GET['assign_to']) : '';
if ($assign_to_filter !== '') {
    $assign_to_esc = htmlspecialchars($assign_to_filter, ENT_QUOTES, 'UTF-8');
    $sql .= " AND p.assign_to = '$assign_to_esc'";
    $sql2 .= " AND sp.assign_to = '$assign_to_esc'";
}

$customer_id_filter = isset($_GET['customer_id']) ? trim($_GET['customer_id']) : '';
if ($customer_id_filter !== '') {
    $customer_id_esc = htmlspecialchars($customer_id_filter, ENT_QUOTES, 'UTF-8');
    $sql .= " AND c.customer_id = '$customer_id_esc'";
    $sql2 .= " AND c.customer_id = '$customer_id_esc'";
}

$p_team_filter = isset($_GET['p_team']) ? trim($_GET['p_team']) : '';
if ($p_team_filter !== '') {
    $p_team_esc = htmlspecialchars($p_team_filter, ENT_QUOTES, 'UTF-8');
    $sql .= " AND p.p_team = '$p_team_esc'";
    $sql2 .= " AND sp.p_team = '$p_team_esc'";
}

$state_filter = isset($_GET['state']) ? trim($_GET['state']) : '';
$state_esc = '';
if ($state_filter !== '') {
    $state_esc = htmlspecialchars($state_filter, ENT_QUOTES, 'UTF-8');
}


// Sort params: set default and sanitize
$sort_by = isset($_GET['sort_by']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['sort_by']) : 'project_id';
$sort_order = (isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'asc') ? 'ASC' : 'DESC';

$sortable_columns = [
    "project_id"   => ["p" => "p.project_id",   "sp" => "sp.project_id"],
    "project_name" => ["p" => "p.project_name", "sp" => "sp.subproject_name"],
    "customer_id"  => ["p" => "c.customer_id",  "sp" => "c.customer_id"],
    "customer_name"=> ["p" => "c.customer_name","sp" => "c.customer_name"],
    "state"        => ["p" => "NULL", "sp" => "NULL"], // handled in PHP
    "p_team"       => ["p" => "p.p_team",       "sp" => "sp.p_team"],
    "team_lead"    => ["p" => "p.team_lead",    "sp" => "sp.team_lead"], 
    "assign_to"    => ["p" => "p.assign_to",    "sp" => "sp.assign_to"], 
    "checker"      => ["p" => "p.verify_by_name","sp" => "sp.verify_by_name"], 
    "hours"        => ["p" => "p.EPT","sp" => "sp.sub_EPT"], 
    "ECD"          => ["p" => "p.end_date","sp" => "sp.sub_end_date"]
];


$p_sort_column = $sortable_columns[$sort_by]['p'] ?? 'p.project_id';
$sp_sort_column = $sortable_columns[$sort_by]['sp'] ?? 'sp.project_id';

$sql  .= " ORDER BY $p_sort_column $sort_order";
$sql2 .= " ORDER BY $sp_sort_column $sort_order";




// Fetch and combine dataF
$info_projects = $obj_admin->manage_all_info($sql);
$info_subprojects = $obj_admin->manage_all_info($sql2);

$combined_info = [];

// Create a lookup of project states by project_id
$projectStates = [];
while ($row = $info_projects->fetch(PDO::FETCH_ASSOC)) {
    $projectStates[$row['project_id']] = $row['state'];

    // Apply state filter for projects
    if ($state_esc === '' || $row['state'] === $state_esc) {
        $combined_info[] = $row; // keep original project row
    }
}

// Loop over subprojects and assign parent project state
while ($row = $info_subprojects->fetch(PDO::FETCH_ASSOC)) {
    if (isset($projectStates[$row['project_id']])) {
        $row['state'] = $projectStates[$row['project_id']]; // override with parent project state

        // Apply state filter based on parent project
        if ($state_esc === '' || $row['state'] === $state_esc) {
            $combined_info[] = $row;
        }
    }
}

// Sort by project_id or subproject_id
// Apply dynamic sorting
usort($combined_info, function ($a, $b) use ($sort_by, $sort_order) {
    $valA = $a[$sort_by] ?? null;
    $valB = $b[$sort_by] ?? null;

    if ($valA == $valB) return 0;

    if (strtoupper($sort_order) === 'DESC') {
        return ($valA < $valB) ? -1 : 1;
    } else {
        return ($valA > $valB) ? -1 : 1;
    }
});


// Pagination setup (common for both projects + subprojects)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total count before slicing
$total_count = count($combined_info);

// Slice combined results
$combined_info = array_slice($combined_info, $offset, $limit);

// Return data as JSON with total count
header('Content-Type: application/json');
echo json_encode([
    "total_count" => $total_count,
    "page" => $page,
    "limit" => $limit,
    "data" => $combined_info
]);
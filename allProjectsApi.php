<?php
require './authentication.php'; // Admin authentication
require './conn.php';

// Auth check
$user_id = $_SESSION['admin_id'] ?? null;
$user_name = $_SESSION['name'] ?? null;
$security_key = $_SESSION['security_key'] ?? null;

if ($user_id == null || $security_key == null) {
    header('Location: index.php');
    exit;
}

// User role
$user_role = $_SESSION['user_role'] ?? 0;

// Get search & pagination params
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

// -----------------
// 1️⃣ Base Queries
// -----------------
$sql = "SELECT p.*, 
                pm.fullname AS project_manager_name, 
                c.contact_name, c.contact_email, c.contact_phone_number, 
                c.contact_id, c.customer_id, c.customer_name
            FROM projects p
            LEFT JOIN project_managers pm ON p.project_manager = pm.fullname
            LEFT JOIN contacts c ON p.contact_id = c.contact_id
            WHERE 1";

$sql2 = "SELECT p.state AS state, 
                    sp.*, 
                    pm.fullname AS subproject_manager_name, 
                    c.contact_name, c.contact_email, c.contact_phone_number, 
                    c.contact_id, c.customer_id, c.customer_name
            FROM subprojects sp
            LEFT JOIN project_managers pm ON sp.project_manager = pm.fullname
            LEFT JOIN projects p ON sp.project_id = p.project_id
            LEFT JOIN contacts c ON sp.contact_id = c.contact_id
            WHERE 1";

// 3️⃣ Search Filters
 if (!empty($search)) {
    $sql .= " AND (
                  p.project_id LIKE '%$search%'  
                  OR p.project_name LIKE '%$search%' 
                  OR p.project_manager LIKE '%$search%' 
                  OR pm.fullname LIKE '%$search%' 
                  OR p.assign_to LIKE '%$search%' 
                  OR p.assign_to_id LIKE '%$search%' 
                  OR p.comments LIKE '%$search%'
                  OR c.customer_name LIKE '%$search%' 
                  OR c.contact_name LIKE '%$search%'
              )";

    $sql2 .= " AND (
                  sp.project_id LIKE '%$search%' 
                  OR sp.subproject_name LIKE '%$search%' 
                  OR sp.project_manager LIKE '%$search%' 
                  OR pm.fullname LIKE '%$search%' 
                  OR sp.assign_to LIKE '%$search%' 
                  OR sp.assign_to_id LIKE '%$search%' 
                  OR sp.comments LIKE '%$search%'
                  OR c.customer_name LIKE '%$search%' 
                  OR c.contact_name LIKE '%$search%'
              )";
}



$project_id = isset($_GET['project_id']) ? trim($_GET['project_id']) : "";
$engineer = isset($_GET['engineer']) ? trim($_GET['engineer']) : "";
$customer_id = isset($_GET['customer_id']) ? trim($_GET['customer_id']) : "";
$team = isset($_GET['team']) ? trim($_GET['team']) : "";
$state = isset($_GET['state']) ? trim($_GET['state']) : "";

// Apply filters to projects query
if (!empty($project_id))
    $sql .= " AND p.project_id LIKE '%$project_id%'";
if (!empty($engineer))
    $sql .= " AND p.assign_to = '$engineer'";
if (!empty($customer_id))
    $sql .= " AND c.customer_id = '$customer_id'";
if (!empty($team))
    $sql .= " AND p.p_team = '$team'";

// Apply same filters to subprojects query
if (!empty($project_id))
    $sql2 .= " AND sp.project_id LIKE '%$project_id%'";
if (!empty($engineer))
    $sql2 .= " AND sp.assign_to = '$engineer'";
if (!empty($customer_id))
    $sql2 .= " AND c.customer_id = '$customer_id'";
if (!empty($team))
    $sql2 .= " AND sp.p_team = '$team'";
if (!empty($state)) {
    $stateEscaped = $conn->real_escape_string($state);
    $sql .= " AND TRIM(p.state) = '$stateEscaped'";
    $sql2 .= " AND TRIM(p.state) = '$stateEscaped'";
}



// 4️⃣ Pagination & Order
// -----------------
$allowed_sort_columns = [
    'project_id' => ['projects' => 'p.project_id', 'subprojects' => 'sp.project_id'],
    'project_name' => ['projects' => 'p.project_name', 'subprojects' => 'sp.subproject_name'],
    'customer_id' => ['projects' => 'c.customer_id', 'subprojects' => 'c.customer_id'],
    'state' => ['projects' => 'p.state', 'subprojects' => 'p.state'],
    'assigned_to' => ['projects' => 'p.assign_to', 'subprojects' => 'sp.assign_to'],
    'checker_status' => ['projects' => 'p.checker_status', 'subprojects' => 'sp.checker_status'],
    'p_team' => ['projects' => 'p.p_team', 'subprojects' => 'sp.p_team'], // ✅ added
    'EPT' => ['projects' => 'p.EPT', 'subprojects' => 'sp.sub_EPT'], // ✅ added
    'end_date' => ['projects' => 'p.end_date', 'subprojects' => 'sp.sub_end_date'],
];

$sort_by = $_GET['sort_by'] ?? 'project_id';
$sort_order = (isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'asc') ? 'ASC' : 'DESC';

// fallback to project_id if invalid
if (!isset($allowed_sort_columns[$sort_by])) {
    $sort_by = 'project_id';
}
$sql .= " ORDER BY {$allowed_sort_columns[$sort_by]['projects']} $sort_order";
$sql2 .= " ORDER BY {$allowed_sort_columns[$sort_by]['subprojects']} $sort_order";



// -----------------
// 5️⃣ Execute Queries
// -----------------
try {
    $info_projects = $obj_admin->manage_all_info($sql);
    $info_subprojects = $obj_admin->manage_all_info($sql2);

    // Fetch all data
    $combined_info = array_merge(
        $info_projects->fetchAll(PDO::FETCH_ASSOC),
        $info_subprojects->fetchAll(PDO::FETCH_ASSOC)
    );

    // Sort combined array
    usort($combined_info, function ($a, $b) use ($sort_by, $sort_order) {
        $valA = $a[$sort_by] ?? '';
        $valB = $b[$sort_by] ?? '';

        if ($valA == $valB)
            return 0;
        return ($sort_order === 'ASC') ? (($valA < $valB) ? -1 : 1) : (($valA > $valB) ? -1 : 1);
    });

    // Apply combined pagination
    $combined_info = array_slice($combined_info, $offset, $limit);


    $sort_by = $_GET['sort_by'] ?? 'project_id';
    $sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'asc' ? 'ASC' : 'DESC';

    usort($combined_info, function ($a, $b) use ($sort_by, $sort_order) {
        $valA = $a[$sort_by] ?? '';
        $valB = $b[$sort_by] ?? '';

        if ($valA == $valB)
            return 0;

        if ($sort_order === 'ASC') {
            return ($valA < $valB) ? -1 : 1;
        } else {
            return ($valA > $valB) ? -1 : 1;
        }
    });



    // -----------------
    // 6️⃣ Return JSON
    // -----------------

    // -----------------
    // 6️⃣ Count total rows (without LIMIT/OFFSET)
    // -----------------
    $count_sql = "SELECT COUNT(*) AS total FROM (
    SELECT p.project_id FROM projects p
    LEFT JOIN project_managers pm ON p.project_manager = pm.fullname
    LEFT JOIN contacts c ON p.contact_id = c.contact_id
    WHERE 1
        " . (!empty($project_id) ? " AND p.project_id LIKE '%$project_id%'" : "") . "
        " . (!empty($engineer) ? " AND p.assign_to = '$engineer'" : "") . "
        " . (!empty($customer_id) ? " AND c.customer_id = '$customer_id'" : "") . "
        " . (!empty($team) ? " AND p.p_team = '$team'" : "") . "
        " . (!empty($state) ? " AND TRIM(p.state) = '$state'" : "") . "
    UNION ALL
    SELECT sp.project_id FROM subprojects sp
    LEFT JOIN projects p ON sp.project_id = p.project_id
    LEFT JOIN project_managers pm ON sp.project_manager = pm.fullname
    LEFT JOIN contacts c ON sp.contact_id = c.contact_id
    WHERE 1
        " . (!empty($project_id) ? " AND sp.project_id LIKE '%$project_id%'" : "") . "
        " . (!empty($engineer) ? " AND sp.assign_to = '$engineer'" : "") . "
        " . (!empty($customer_id) ? " AND c.customer_id = '$customer_id'" : "") . "
        " . (!empty($team) ? " AND sp.p_team = '$team'" : "") . "
        " . (!empty($state) ? " AND TRIM(p.state) = '$state'" : "") . "
    ) AS all_data";
    ;

    $total_stmt = $obj_admin->manage_all_info($count_sql);
    $total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_rows = (int) $total_row['total'];

    // -----------------
    // Return JSON with total + data
    // -----------------
    header('Content-Type: application/json');
    echo json_encode([
        'total' => $total_rows,
        'data' => $combined_info
    ]);



} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

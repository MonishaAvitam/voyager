<?php
require '../conn.php';
header('Content-Type: application/json');

$search_query = $_GET['query'] ?? ''; // Get the search term from the frontend
$search_query = $conn->real_escape_string($search_query); // Sanitize the input

$results = [];

// Define the tables to search in
$tables = [
    'potential_project',
    'potential_project_sent_quotation',
    'accepted_quotations',
    'cancelled_quotations'
];

foreach ($tables as $table) {
    if ($table === 'potential_project') {
        // For 'potential_project', search by `sr_num`
        $query = "SELECT `sr_num`, `sales_id`, `client_name`, `company_name`, `project_name`
                  FROM `$table`
                 WHERE `sr_num` LIKE '%$search_query%' 
   OR `client_name` LIKE '%$search_query%'";
    } else {
        // For other tables, include revised_qt
        $query = "SELECT `sales_id`, `client_name`, `company_name`, `project_name`, `revised_qt`
                  FROM `$table`
                 WHERE `sales_id` LIKE '%$search_query%' 
   OR `project_name` LIKE '%$search_query%' 
   OR `client_name` LIKE '%$search_query%'  ";
    }

    $res = $conn->query($query);

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $row['source'] = $table;  // Add table name as source to identify the origin
            $results[] = $row;
        }
    }
}

// Return the results as JSON
echo json_encode($results);
?>

<?php
// fetch_combined_data.php

require './authentication.php'; // Admin authentication check
require './conn.php';

// Admin authentication check
$user_id = $_SESSION['admin_id'] ?? null;
$security_key = $_SESSION['security_key'] ?? null;

if (!$user_id || !$security_key) {
    header('Location: index.php');
    exit();
}

try {
    $response = [];

    // Function to fetch data and add source_table field
    function fetchDataWithSource($query, $sourceTable, $adminObj) {
        $stmt = $adminObj->manage_all_info($query);
        if ($stmt && $stmt->execute()) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as &$row) {
                $row['source_table'] = $sourceTable; // Add source table info
            }
            return $data;
        } else {
            throw new Exception("Error fetching $sourceTable: " . implode(", ", $stmt->errorInfo()));
        }
    }

    // Fetch and store data from each table
    $response['csa_finance_invoiced'] = fetchDataWithSource("SELECT * FROM csa_finance_invoiced", "csa_finance_invoiced", $obj_admin);
    $response['csa_finance_readytobeinvoiced'] = fetchDataWithSource("SELECT * FROM csa_finance_readytobeinvoiced", "csa_finance_readytobeinvoiced", $obj_admin);
    $response['csa_finance_uninvoiced'] = fetchDataWithSource("
    SELECT 
        id, project_id, date, price, comments, last_modified_date, rownumber, 
        service_date, due_date, project_status, 'csa_finance_uninvoiced' AS source_table 
    FROM csa_finance_uninvoiced
    UNION 
    SELECT 
        NULL AS id, project_id, NULL AS date, NULL AS price, comments, NULL AS last_modified_date, 
        NULL AS rownumber, NULL AS service_date, NULL AS due_date, NULL AS project_status, 
        'projects' AS source_table 
    FROM projects 
    WHERE urgency <> 'purple'", 
    "csa_finance_uninvoiced", 
    $obj_admin
);
    $response['ready_to_pay'] = fetchDataWithSource("SELECT * FROM ready_to_pay", "ready_to_pay", $obj_admin);
    $response['unpaidinvoices'] = fetchDataWithSource("SELECT * FROM unpaidinvoices", "unpaidinvoices", $obj_admin);
    $response['paidinvoices'] = fetchDataWithSource("SELECT * FROM paidinvoices", "paidinvoices", $obj_admin);

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>

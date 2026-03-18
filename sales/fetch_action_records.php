<?php
require '../conn.php';
header('Content-Type: application/json');

// Check if either contact_customer_id or customer_id is provided
if (!isset($_GET['contact_customer_id']) && !isset($_GET['customer_id'])) {
    echo json_encode(['records' => []]);
    exit();
}

// Determine which ID to use
$customerId = isset($_GET['contact_customer_id']) 
    ? intval($_GET['contact_customer_id']) 
    : intval($_GET['customer_id']);

$query = "SELECT r.`timestamp`, r.`record`, r.`record_number`, r.`contact_customer_id`, r.`user_id`, a.`fullname`
          FROM `potential_customer_action_record` r
          LEFT JOIN `tbl_admin` a ON r.`user_id` = a.`user_id`
          WHERE r.`contact_customer_id` = ? OR r.`customer_id` = ?
          ORDER BY r.`record_number` DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $customerId, $customerId); // bind same value for both placeholders
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['records' => $records]);

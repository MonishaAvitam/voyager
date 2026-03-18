<?php
header('Content-Type: application/json');
require '../conn.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$customer_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($customer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Customer ID']);
    exit();
}

$query = "UPDATE potential_customer SET status=NULL WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Customer restored successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to restore customer']);
}
?>

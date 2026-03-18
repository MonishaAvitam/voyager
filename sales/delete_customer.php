<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require '../authentication.php';
require '../conn.php';

// Auth check
$user_id = $_SESSION['admin_id'] ?? null;
$security_key = $_SESSION['security_key'] ?? null;
if ($user_id === null || $security_key === null) {
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit();
}

// Get ID from POST
$customerId = $_POST['id'] ?? null;
if (!$customerId || !is_numeric($customerId)) {
    echo json_encode(["success" => false, "message" => "Invalid customer ID"]);
    exit();
}
$customerId = intval($customerId);

try {
    $sql = "UPDATE potential_customer 
            SET status = 'deleted',
                deleted_by_user = ?,
                deleted_timestamp = NOW()
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param("ii", $user_id, $customerId);
    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);

    if ($stmt->affected_rows === 0) throw new Exception("No record found or already deleted");

    echo json_encode(["success" => true, "message" => "Customer marked as deleted successfully"]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit();
}

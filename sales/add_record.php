<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  // Allow all origins
header('Access-Control-Allow-Methods: POST'); // Allow POST method
header('Access-Control-Allow-Headers: Content-Type');

require '../conn.php'; // Ensure correct database connection

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method"]);
    exit();
}

// Retrieve JSON payload
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['customer_id']) || !isset($data['record']) || !isset($data['user_id'])) {
    echo json_encode(["error" => "Invalid input data"]);
    exit();
}

$customer_id = intval($data['customer_id']);
$record = trim($data['record']);
$user_id = intval($data['user_id']);  // ✅ capture user_id
$timestamp = date("Y-m-d H:i:s");

// Get the current highest record number for this customer
$query = "SELECT MAX(record_number) AS max_record_number FROM potential_customer_action_record WHERE customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($max_record_number);
$stmt->fetch();
$stmt->close();

// Set the record_number to 1 if no records exist for the customer, otherwise increment the max record number
$record_number = ($max_record_number === NULL) ? 1 : $max_record_number + 1;

// Insert the new record with the incremented record number
$query = "INSERT INTO potential_customer_action_record (customer_id, record, record_number, timestamp, user_id) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("isisi", $customer_id, $record, $record_number, $timestamp, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Record added successfully"]);
} else {
    echo json_encode(["error" => "Failed to add record: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
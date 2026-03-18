<?php
require '../conn.php';
header('Content-Type: application/json');

// Get customer ID from request
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit();
}

$customerId = intval($_POST['id']);

// 1. Fetch data from potential_customer
$query = "SELECT `date`, `company_code`, `company_name`, `website`, `address`, `name`, `phone_num`, `email`, `account_manager`,`office_id`, `comments` 
          FROM `potential_customer` 
          WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit();
}

$customer = $result->fetch_assoc();
$stmt->close();

// 2. Insert into contacts
$insertQuery = "INSERT INTO `contacts` 
    (`customer_id`, `customer_name`, `contact_name`, `contact_email`, `contact_phone_number`, `address`, `registration_date`,`office_id`, `comments`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmtInsert = $conn->prepare($insertQuery);
$stmtInsert->bind_param(
    "sssssssss",
    $customer['company_code'],                  // customer_id
    $customer['company_name'],        // customer_name
    $customer['name'],                // contact_name
    $customer['email'],               // contact_email
    $customer['phone_num'],           // contact_phone_number
    $customer['address'],             // address
    $customer['date'],                // registration_date
    $customer['office_id'],                // registration_date
    $customer['comments']             // comments
);

if ($stmtInsert->execute()) {
    // 3. Update potential_customer to mark as converted
    $updateQuery = "UPDATE potential_customer SET client_status = 'converted' WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("i", $customerId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    // Get the inserted contact's primary key
    $stmtGetId = $conn->prepare("SELECT contact_id FROM contacts WHERE customer_id = ? ORDER BY contact_id DESC LIMIT 1");
    $stmtGetId->bind_param("s", $customer['company_code']); // same value used for customer_id in contacts
    $stmtGetId->execute();
    $stmtGetId->bind_result($contactCustomerId);
    $stmtGetId->fetch();
    $stmtGetId->close();

    // Update potential_customer_action_record
    $updateActionQuery = "UPDATE `potential_customer_action_record` 
                          SET contact_customer_id = ? 
                          WHERE customer_id = ?";
    $stmtActionUpdate = $conn->prepare($updateActionQuery);
    $stmtActionUpdate->bind_param("is", $contactCustomerId, $customerId);
    $stmtActionUpdate->execute();
    $stmtActionUpdate->close();

    $deleteQuery = "DELETE FROM potential_customer WHERE id = ?";
    $stmtDelete = $conn->prepare($deleteQuery);
    $stmtDelete->bind_param("i", $customerId);
    $stmtDelete->execute();
    $stmtDelete->close();


    echo json_encode(['success' => true, 'message' => 'Potentials Clients Converted to CSA Clients']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to insert into contacts']);
}

$stmtInsert->close();
$conn->close();

<?php
header('Content-Type: application/json');
require '../conn.php';

// Disable any accidental output
ob_clean();

// Get POST values
$customer_id = $_POST['customer_id'] ?? '';
$account_manager = $_POST['account_manager'] ?? '';

// Debug output
$debug = ['received_customer_id' => $customer_id, 'received_account_manager' => $account_manager];

if ($customer_id && $account_manager) {
    $stmt = $conn->prepare("UPDATE potential_customer SET account_manager=? WHERE id=?");
    $stmt->bind_param("si", $account_manager, $customer_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Account Manager updated successfully.',
            'debug' => $debug
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update Account Manager.',
            'debug' => ['sql_error' => $stmt->error] + $debug
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input.',
        'debug' => $debug
    ]);
}
exit;

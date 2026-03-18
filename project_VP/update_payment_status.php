<?php
require '../conn.php';
header('Content-Type: application/json');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $subproject_id = isset($_POST['subproject_id']) ? intval($_POST['subproject_id']) : 0;
    $payment_status = isset($_POST['payment_status']) ? $conn->real_escape_string($_POST['payment_status']) : '';
    $invoice_number = isset($_POST['invoice_number']) ? $conn->real_escape_string($_POST['invoice_number']) : '';
    $payment_date = isset($_POST['payment_date']) ? $conn->real_escape_string($_POST['payment_date']) : null;
    $comments = isset($_POST['comments']) ? $conn->real_escape_string($_POST['comments']) : '';
    
    // Validate data
    if ($subproject_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid subproject ID']);
        exit;
    }
    
    // Check if record exists in paidinvoices table
    $check_sql = "SELECT * FROM paidinvoices WHERE subproject_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $subproject_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $sql = "UPDATE paidinvoices SET 
                payment_status = ?, 
                invoice_number = ?, 
                payment_date = ?, 
                comments = ?,
                updated_at = NOW()
                WHERE subproject_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $payment_status, $invoice_number, $payment_date, $comments, $subproject_id);
    } else {
        // Insert new record
        $sql = "INSERT INTO paidinvoices 
                (subproject_id, payment_status, invoice_number, payment_date, comments, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $subproject_id, $payment_status, $invoice_number, $payment_date, $comments);
    }
    
    // Execute query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating payment status: ' . $stmt->error]);
    }
    
    // Close statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

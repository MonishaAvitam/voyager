<?php
require '../conn.php'; // Database connection file

// Check if data is received via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sales_id = $_POST['sales_id']; // Get the Sales ID
    $company_name = $_POST['company_name'];
    $client_name = $_POST['client_name'];
    $contact_email = $_POST['email_id'];
    $company_address = $_POST['address'];
    $client_contact = $_POST['client_contact'];
    $quote_sent_date = $_POST['quote_sent_date'];
    // $quotation_amount = $_POST['quotation_amount'] ?? 0 ;
    $quotation_amount = isset($_POST['quotation_amount']) && $_POST['quotation_amount'] !== '' ? $_POST['quotation_amount'] : '0.00';
    $enquiry_details = $_POST['enquiry_details'];
    $engineer = $_POST['engineer'];
    $client = $_POST['client'];
    $comments = $_POST['comments'];
    $revised_qt = $_POST['revised_qt'];


    // Ensure Sales ID is not empty
    if (!empty($revised_qt)) {
        $sql = "UPDATE cancelled_quotations SET 
                company_name = ?, 
                client_name = ?, 
                email_id = ?, 
                address = ?, 
                client_contact = ?, 
                quote_sent_date = ?, 
                amount = ?, 
                enquiry_details = ?, 
                engineer = ?,
                client = ?, 
                comments = ? 
                WHERE sales_id = ? AND revised_qt = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssss",  // Changed to "sssssssssssss" to match 13 placeholders
            $company_name,
            $client_name,
            $contact_email,
            $company_address,
            $client_contact,
            $quote_sent_date,
            $quotation_amount,
            $enquiry_details,
            $engineer,
            $client,
            $comments,
            $sales_id,
            $revised_qt  // Added the missing variable
        );
    } else {
        $sql = "UPDATE cancelled_quotations SET 
            company_name = ?, 
            client_name = ?, 
            email_id = ?, 
            address = ?, 
            client_contact = ?, 
            quote_sent_date = ?, 
            amount = ?, 
            enquiry_details = ?, 
            engineer = ?,
            client=?, 
            comments = ? 
            WHERE sales_id = ? AND revised_qt IS NULL";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssss", // 11 placeholders match 11 variables
            $company_name,
            $client_name,
            $contact_email,
            $company_address,
            $client_contact,
            $quote_sent_date,
            $quotation_amount,
            $enquiry_details,
            $engineer,
            $client,
            $comments,
            $sales_id  // Removed `$revised_qt` since it's checked as NULL in SQL
        );
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Quotation updated successfully"]);
        header('location:cancelled_quotations.php');
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update quotation"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Sales ID"]);
}

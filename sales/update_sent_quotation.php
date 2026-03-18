<?php
require '../conn.php'; // Database connection file

// Check if data is received via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sales_id = $_POST['sales_id']; // Get the Sales ID
    $revised_qt = $_POST['revised_qt'] ?? ''; // Get the Revised Quotation (if available)
    $company_name = $_POST['company_name'];
    $client_name = $_POST['contact_name'];
    $contact_email = $_POST['contact_email'];
    $company_address = $_POST['company_address'];
    $client_contact = $_POST['client_contact'];
    $quote_sent_date = $_POST['quote_sent_date'];
    // $quotation_amount = $_POST['quotation_amount'];
    $quotation_amount = isset($_POST['quotation_amount']) && $_POST['quotation_amount'] !== '' ? $_POST['quotation_amount'] : '0.00';
    $enquiry_details = $_POST['enquiry_details'];
    $engineer = $_POST['engineer'];
    $client = $_POST['client'];
    $comments = $_POST['comments'];

    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    // exit();

    // Ensure Sales ID is provided
    if (!empty($revised_qt)) {
        // If revised_qt is provided, update using both sales_id and revised_qt
        $sql = "UPDATE potential_project_sent_quotation SET 
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
            "sssssssssssss",
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
            $revised_qt
        );
    } else {
        // If revised_qt is NOT provided, update using only sales_id
        $sql = "UPDATE potential_project_sent_quotation SET 
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
WHERE sales_id = ? AND revised_qt IS NULL"; 

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssss",
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
            $sales_id
        );
    }

    // Execute query and check for success
    if ($stmt->execute()) {
        header('Location: sent_quotations.php');
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update quotation"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Sales ID"]);
}

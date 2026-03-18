<?php
require '../conn.php'; // Database connection file

// Check if data is received via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sales_id = $_POST['sales_id']; // Get the Sales ID
    $company_name = $_POST['company_name'];
    $client_name = $_POST['client_name'];
    $contact_email = $_POST['contact_email'];
    $company_address = $_POST['company_address'];
    $client_contact = $_POST['client_contact'];
    $quote_sent_date = $_POST['quote_sent_date'];
    $enquiry_details = $_POST['enquiry_details'];
    $engineer = $_POST['engineer'];
    $client_id = $_POST['client_id']; // Fixing mismatch
    $comments = $_POST['comments'];

    // Ensure Sales ID is not empty
    if (!empty($sales_id)) {
        $sql = "UPDATE potential_project SET 
                company_name = ?, 
                client_name = ?, 
                email_id = ?, 
                address = ?, 
                client_contact = ?, 
                quote_sent_date = ?, 
                enquiry_details = ?, 
                engineer = ?, 
                client = ?, 
                comments = ? 
                WHERE sales_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssss",
            $company_name,
            $client_name,
            $contact_email,
            $company_address,
            $client_contact,
            $quote_sent_date,
            $enquiry_details,
            $engineer,
            $client_id,  // Fixed mismatch
            $comments,
            $sales_id
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Quotation updated successfully"]);
            header('location:project_enquiry.php');
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update quotation"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Sales ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>

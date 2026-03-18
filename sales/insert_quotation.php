<?php
require '../conn.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sales_id = $_POST['sales_id'] ?? '';
    $quote_sent_date = $_POST['quote_sent_date'] ?? '';
    $amount = $_POST['quotation_amount'] ?? '';
    $assign_to = $_POST['assign_to'] ?? '';

    if (empty($sales_id) || empty($quote_sent_date) || empty($amount) || empty($assign_to)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    // Fetch related data from `potential_project`
    $query = "SELECT client_name, client_contact, company_name, client,enquiry_details,email_id,address, project_name,more_detials, quote_sent_date, comments, engineer, timestamp
              FROM potential_project WHERE sales_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sales_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "No data found for this RAE Number."]);
        exit();
    }

    $row = $result->fetch_assoc();

    // Insert into `potential_project_sent_quotation`
    $insert_query = "INSERT INTO potential_project_sent_quotation (sales_id, client_name, amount, client_contact, company_name,enquiry_details, email_id,address, client, project_name, quote_sent_date,first_enquiry_date,more_detials, comments, engineer) 
    VALUES (?, ?,?, ?, ?, ?, ?, ?, ?, ?,?, ?,? , ?, ?)";


    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param(
        "ssdssssssssssss",
        $sales_id,
        $row['client_name'],
        $amount,
        $row['client_contact'],
        $row['company_name'],
        $row['enquiry_details'],
        $row['email_id'],
        $row['address'],
        $row['client'],
        $row['project_name'],
        $quote_sent_date,
        $row['quote_sent_date'],
        $row['more_detials'],
        $row['comments'],
        $assign_to
    );


    if ($stmt->execute()) {

        $delete_query = "DELETE FROM potential_project WHERE sales_id=?";
        $stmt=$conn->prepare($delete_query);
        $stmt->bind_param('s',$sales_id);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Quotation successfully moved to sent quotations."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}



// Handle Revised Quotation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['revise_quotation'])) {
    $sales_id = $_POST['sales_id'] ?? '';

    if (empty($sales_id)) {
        echo json_encode(["success" => false, "message" => "Sales ID is required."]);
        exit();
    }

    // Fetch the existing quotation
    $query = "SELECT * FROM potential_project_sent_quotation WHERE sales_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sales_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Original quotation not found."]);
        exit();
    }

    $original = $result->fetch_assoc();

    // Check how many revisions exist
    $query = "SELECT COUNT(*) as count FROM potential_project_sent_quotation WHERE sales_id LIKE ?";
    $likeSalesId = $sales_id . '_R%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $likeSalesId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $revisionNumber = $row['count'] + 1;

    // Generate new revised sales ID
    $new_sales_id = $sales_id . '_R' . $revisionNumber;

    // Insert revised quotation
    $insert_query = "INSERT INTO potential_project_sent_quotation 
        (sales_id, client_name, quotation_amount, client_contact, company_name, client, project_name, 
         quote_sent_date, comments, engineer, timestamp, revised_qt) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param(
        "ssdssssssss",
        $new_sales_id,
        $original['client_name'],
        $original['quotation_amount'],
        $original['client_contact'],
        $original['company_name'],
        $original['client'],
        $original['project_name'],
        $original['quote_sent_date'],
        $original['comments'],
        $original['engineer'],
        $new_sales_id
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "new_sales_id" => $new_sales_id, "message" => "Quotation revised successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
    }

    $stmt->close();
}

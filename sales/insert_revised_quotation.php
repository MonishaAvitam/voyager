<?php
require '../conn.php';

if (isset($_POST['revisedQuotationRequest'])) {
    $sales_id = $_POST['sales_id'] ?? '';
    $quote_sent_date = $_POST['quote_sent_date'] ?? '';
    $amount = $_POST['quotation_amount'] ?? '';
    $enquiry_details = $_POST['enquiry_details'] ?? '';
    $comments = $_POST['comments'] ?? '';

    if (empty($sales_id) || empty($quote_sent_date) || empty($amount)) {
        die("All fields are required.");
    }

    // Get the highest `revised_qt` for this sales_id
    $check_query = "
    SELECT revised_qt FROM potential_project_sent_quotation WHERE sales_id = ? AND revised_qt LIKE 'R%'
    UNION
    SELECT revised_qt FROM cancelled_quotations WHERE sales_id = ? AND revised_qt LIKE 'R%'
    UNION
    SELECT revised_qt FROM accepted_quotations WHERE sales_id = ? AND revised_qt LIKE 'R%'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("sss", $sales_id, $sales_id, $sales_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $highest_revision = 0;

    while ($row = $result->fetch_assoc()) {
        $current_revision = intval(substr($row['revised_qt'], 1)); // Extract the number from "R1", "R2", etc.
        if ($current_revision > $highest_revision) {
            $highest_revision = $current_revision;
        }
    }

    // Set the new revision number (incrementing from the last found revision)
    $new_revision = "R" . ($highest_revision + 1);

    // Fetch existing project details
    $query = "SELECT client_name, client_contact, company_name, client, project_name, engineer, first_enquiry_date ,email_id,address,more_detials 
    FROM potential_project_sent_quotation WHERE sales_id = ? LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sales_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No matching project found.");
    }

    $row = $result->fetch_assoc();

    // Insert the revised quotation with the new `revised_qt`
    $insert_query = "INSERT INTO potential_project_sent_quotation (sales_id, client_name, amount, client_contact, company_name,first_enquiry_date,enquiry_details, client, project_name, quote_sent_date, comments, engineer, revised_qt,email_id,address,more_detials) 
                     VALUES (?, ?, ?, ?, ?,?, ?, ?,? , ?, ?, ?, ?,?,?,?)";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssdsssssssssssss", 
        $sales_id, 
        $row['client_name'], 
        $amount, 
        $row['client_contact'], 
        $row['company_name'], 
        $row['first_enquiry_date'],
        $enquiry_details,
        $row['client'], 
        $row['project_name'], 
        $quote_sent_date, 
        $comments, 
        $row['engineer'], 
        $new_revision,
        $row['email_id'],
        $row['address'],
        $row['more_detials']
    );

    if ($stmt->execute()) {
        echo "<script>alert('Revised quotation added successfully.');</script>";
        echo "<script>window.location.href = 'sent_quotations.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}
?>

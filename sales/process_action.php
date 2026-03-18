<?php
include '../conn.php'; // Ensure this connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action_type'] ?? '';
    $salesId = $_POST['sales_id'] ?? '';
    $revisedID = $_POST['revised_id'] ?? '';

    // Debugging output
    echo "Action: $action, Sales ID: $salesId, Revised ID: '$revisedID' <br>";

    // Fetch record from `potential_project_sent_quotation`
    if ($revisedID === 'null' || $revisedID === NULL || $revisedID === '') {
        $query = "SELECT * FROM `potential_project_sent_quotation` WHERE sales_id = ? AND revised_qt IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $salesId);
    } else {
        $query = "SELECT * FROM `potential_project_sent_quotation` WHERE sales_id = ? AND revised_qt = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $salesId, $revisedID);
    }
    

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    if ($revisedID === 'null' || $revisedID === NULL || $revisedID === '') {
        $stmt->bind_param("s", $salesId); // Only bind sales_id when revised_qt is NULL
    } else {
        $stmt->bind_param("ss", $salesId, $revisedID); // Bind both sales_id and revised_qt when not NULL
    }
        $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No matching quotation found. Ensure sales_id and revised_qt match a valid record.");
    }

    if ($row = $result->fetch_assoc()) {
        // Ensure NULL values are properly handled
        $sr_num = $row['sr_num'] ?? NULL;
        $sales_id = $row['sales_id'] ?? NULL;
        $client_name = $row['client_name'] ?? NULL;
        $enquiry_details = $row['enquiry_details'] ?? NULL;
        $amount = $row['amount'] ?? NULL;
        $client_contact = $row['client_contact'] ?? NULL;
        $company_name = $row['company_name'] ?? NULL;
        $client = $row['client'] ?? NULL;
        $project_name = $row['project_name'] ?? NULL;
        $quote_sent_date = $row['quote_sent_date'] ?? NULL;
        $comments = $row['comments'] ?? NULL;
        $engineer = $row['engineer'] ?? NULL;
        $timestamp = $row['timestamp'] ?? NULL;
        $revisedID = $row['revised_qt'] ?? NULL;
        $accepted_date = date("Y-m-d"); // Gets the current date in YYYY-MM-DD format
        $email_id = $row['email_id'];
        $address = $row['address'];
        $more_details = $row['more_detials'];

        // Determine the destination table
        if ($action === "accept") {
            $targetTable = "accepted_quotations";
            $status = "Accepted";
        } elseif ($action === "cancel") {
            $targetTable = "cancelled_quotations";
            $status = "Cancelled";
        } else {
            die("Invalid action type.");
        }

        // Insert into the appropriate table
        $insertQuery = "INSERT INTO `$targetTable` 
            (`sr_num`, `sales_id`, `client_name`, `enquiry_details`, `amount`, `client_contact`, 
            `company_name`, `client`, `project_name`, `quote_sent_date`,`accepted_date`, `comments`,email_id,address,more_detials, `engineer`, `timestamp`, `status`, `revised_qt`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? , ?,?,?,?, ?, ?, ?, ?)";

        $insertStmt = $conn->prepare($insertQuery);
        if (!$insertStmt) {
            die("Insert Prepare failed: " . $conn->error);
        }

        // Ensure the bind_param has exactly 14 placeholders
        $insertStmt->bind_param(
            "sssssssssssssssssss",
            $sr_num,
            $sales_id,
            $client_name,
            $enquiry_details,
            $amount,
            $client_contact,
            $company_name,
            $client,
            $project_name,
            $quote_sent_date,
            $accepted_date,
            $comments,
            $email_id,
            $address,
            $more_details,
            $engineer,
            $timestamp,
            $status,
            $revisedID
        );


        if ($insertStmt->execute()) {
            // Successfully inserted, now delete from original table
            $deleteQuery = "DELETE FROM `potential_project_sent_quotation` 
            WHERE sales_id = ? AND (revised_qt = ? OR (revised_qt IS NULL AND ? IS NULL))";
        $deleteStmt = $conn->prepare($deleteQuery);
            if (!$deleteStmt) {
                die("Delete Prepare failed: " . $conn->error);
            }
            $deleteStmt->bind_param("sss", $salesId, $revisedID, $revisedID);
            $deleteStmt->execute();

            echo "<script>alert('Quotation successfully $status.'); window.location.href='sent_quotations.php';</script>";
        } else {
            die("Error processing quotation: " . $insertStmt->error);
        }
    } else {
        die("Quotation not found. Ensure sales_id and revised_qt match a valid record.");
    }
    $conn->close();
}

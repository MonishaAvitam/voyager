<?php
require '../conn.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $client_contact = mysqli_real_escape_string($conn, $_POST['client_contact']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $client = mysqli_real_escape_string($conn, $_POST['client']);
    $project_name = mysqli_real_escape_string($conn, $_POST['project_name']);
    $engineer_id = mysqli_real_escape_string($conn, $_POST['engineer']); // Assuming engineer is the user_id
    $enquiry_details = mysqli_real_escape_string($conn, $_POST['enquiry_details']);
    
    // Get the engineer's fullname based on user_id
    $engineer_query = "SELECT fullname FROM tbl_admin WHERE user_id = '$engineer_id'";
    $engineer_result = mysqli_query($conn, $engineer_query);
    $engineer_row = mysqli_fetch_assoc($engineer_result);
    $manager_name = $engineer_row['fullname'] ?? 'Unknown'; // Default to 'Unknown' if not found

    // Get the follow-up comments
    $comments_followup = trim($_POST['comments']); // Trim to remove unnecessary spaces

    // Get existing comments if available
    $existing_comments = ""; // Initialize as empty string
    if (isset($_POST['existing_comments'])) {
        $existing_comments = trim($_POST['existing_comments']); // Fetch existing comments
    }

    // Create the new comment with timestamp
    $current_timestamp = date('Y-m-d'); // Get current timestamp
    $new_comment = "$current_timestamp - $comments_followup --- $manager_name";
    
    // Combine the new comment with existing comments
    $comments = trim("$new_comment\n$existing_comments");

    // Step 1: Get the highest existing sales_id
    $query = "SELECT MAX(CAST(SUBSTRING(sales_id, 2) AS UNSIGNED)) AS max_sales_id 
              FROM (
                  SELECT sales_id FROM potential_project 
                  UNION 
                  SELECT sales_id FROM potential_project_sent_quotation 
                  UNION 
                  SELECT sales_id FROM cancelled_quotations 
                  UNION 
                  SELECT sales_id FROM accepted_quotations
              ) AS combined_sales";

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    $next_sales_id = isset($row['max_sales_id']) ? (int)$row['max_sales_id'] + 1 : 1;

    // Step 2: Format the sales_id as 'S0001', 'S0002', etc.
    $formatted_sales_id = 'S' . str_pad($next_sales_id, 4, '0', STR_PAD_LEFT);

    // Step 3: Insert the record into the database
    $query = "INSERT INTO potential_project (sales_id, client_name, client_contact, company_name, client, project_name, comments, engineer, enquiry_details) 
              VALUES ('$formatted_sales_id', '$client_name', '$client_contact', '$company_name', '$client', '$project_name', '$comments', '$engineer_id', '$enquiry_details')";

    if (mysqli_query($conn, $query)) {
        echo 'Project saved successfully';
    } else {
        error_log("Database error: " . mysqli_error($conn));
        echo 'Error saving project. Please try again.';
    }
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $company_name = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $email_id = $_POST['email_id'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $comments = $_POST['comments'];
    $state = $_POST['state'];
    // Database connection
    require "../conn.php";

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE vendors SET 
            company_name = ?, 
            contact_person = ?, 
            email_id = ?, 
            phone = ?, 
            address = ?, 
            state = ?, 
            comments = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $company_name, $contact_person, $email_id, $phone, $address,$state, $comments, $id);

    if ($stmt->execute()) {
        echo "Vendor updated successfully.";
        header("Location: vendors.php"); // Redirect to the vendor list page
    } else {
        echo "Error updating vendor: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

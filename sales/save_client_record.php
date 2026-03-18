<?php
require '../conn.php'; // Database connection

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['date'];
    $name = $_POST['name'];
    $company_name = $_POST['company_name'];
    $address = $_POST['address'];
    $phone_num = $_POST['phone_num'];
    $email = $_POST['email'];
    $comments = $_POST['comments'];
    $company_code = $_POST['company_code'];
    $account_manager = $_POST['account_manager'];
    $office = $_POST['office'];


    $email_check_query = "SELECT id FROM potential_customer WHERE email = ?";
    $stmt_check = mysqli_prepare($conn, $email_check_query);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        echo "Email already exists.";
        mysqli_stmt_close($stmt_check);
        exit();
    }
    mysqli_stmt_close($stmt_check);
    // Validate required fields
    if (
        empty($date) ||
        empty($name) ||
        empty($company_name) ||
        empty($address) ||
        empty($phone_num) ||
        empty($email) ||
        empty($company_code) ||
        empty($account_manager) ||
        empty($office)
    ) {
        echo "All required fields must be filled.";
        exit();
    }


    // Prepare statement to prevent SQL injection
    $query = "INSERT INTO potential_customer (date, name, company_name, company_code,  address, phone_num, email,account_manager,office_id ,comments) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssss", $date, $name, $company_name, $company_code, $address, $phone_num, $email, $account_manager, $office, $comments);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo "success"; // AJAX will detect this response
        } else {
            echo "Error: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
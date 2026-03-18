<?php
// Check if the form is submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $name = $_POST["customer_name"];
    $email = $_POST["enquiry_details"];
    $message = $_POST["customer_contact"];

    // Prepare response data
    $response = array(
        "name" => $name,
        "email" => $email,
        "message" => $message
    );

    // Echo the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // If the request method is not POST, return an error response
    echo json_encode(array("error" => "Invalid request method."));
}

<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true); // Passing `true` enables exceptions 
if (isset($_POST['send_deliverables'])) {

    try {
        //Server settings 
        $mail->SMTPDebug = 2; // Enable verbose debug output 
        $mail->isSMTP(); // Set mailer to use SMTP 
        $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers 
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username   = "engineering@csaengineering.com.au";
        $mail->Password   = "kezfduovpirmalcs";
        $mail->SMTPSecure = 'tls'; // Enable SSL encryption, TLS also accepted with port 465 
        $mail->Port = 587; // TCP port to connect to 

        //Recipients 
        $mail->setFrom('revanthshiva@csaengineering.com.au', $user_name); //This is the email your form sends From 
        $mail->addAddress($_POST['to_mail']); // Add a recipient address 

        //Content 
        $mail->isHTML(true); // Set email format to HTML 
        $mail->Subject = $_POST['subject'];
        $mail->Body = $_POST['message'];

        // Array of file paths
        $selected_files = isset($_FILES['selected_files']['tmp_name']) ? $_FILES['selected_files']['tmp_name'] : [];

        // Check if any file is selected
        if (!empty($selected_files)) {
            foreach ($selected_files as $index => $file_path) {
                $attachment_name = $_FILES['selected_files']['name'][$index];
                $mail->addAttachment($file_path, $attachment_name);
            }

            // ... the rest of your code ...

            $mail->send();
            $msg_success = 'Mail has been sent';
            header('location:develirables_data.php');
        } else {
            // Handle the case where no files are selected
            echo 'No files selected for attachment.';
        }
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

?>




<?php include 'include/footer.php' ?>


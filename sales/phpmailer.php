<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_username';
    $mail->Password = 'your_password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('sender@example.com', 'Sender Name');

    // Dynamically set the recipient email address
    $mail->addAddress($recipientEmail);

    $mail->isHTML(true);
    $mail->Body = $dynamicContent;

    $mail->send();
    echo 'Email sent successfully to ' . $recipientEmail;
} catch (Exception $e) {
    echo "Failed to send email. Error: {$mail->ErrorInfo}";
}
?>

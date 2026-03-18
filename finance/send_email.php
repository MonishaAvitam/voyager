<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Remove in production

require '../conn.php';
require '../vendor/autoload.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use PhpOffice\PhpWord\TemplateProcessor;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data)
        throw new Exception("Invalid input data");

    $email = $data['email'] ?? null;
    // Create Custom_payslip folder if it doesn't exist
    $customDir = __DIR__ . '/Custom_payslip';
    if (!is_dir($customDir)) {
        mkdir($customDir, 0777, true);
    }

    // Construct new filename
    $month = $data['PAY_MONTH'] ?? 'unknown_month';
    $employeeName = $data['EMPLOYEE_NAME'] ?? 'unknown_employee';
    $employeeNameSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $employeeName); // sanitize filename
    $monthSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $month); // sanitize month

    $newFileName = "payslip_{$monthSafe}_{$employeeNameSafe}.pdf";
    $newFilePath = $customDir . '/' . $newFileName;

    // Move file to Custom_payslip folder with new name
    $originalFile = __DIR__ . '/temp/' . basename($data['file'] ?? '');
    if (!file_exists($originalFile)) {
        throw new Exception("Original file does not exist");
    }
    copy($originalFile, $newFilePath); // copy the file
    $file = $newFilePath; // use this file for email

    if (!$email || !file_exists($file)) {
        throw new Exception("Missing email or file does not exist");
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "engineering@csaengineering.com.au"; // your Gmail
    $mail->Password = "kezfduovpirmalcs";   // Gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("engineering@csaengineering.com.au", "CSA Finance");
    $mail->addAddress($email, $employeeName);

    $mail->isHTML(true);
    $mail->Subject = "PAYMENT INTIMATION " . date("15/m/Y");
    $mail->Body = "Hi,<br><br>
    We have released payment as per attached details through E-Payment. 
    If you do not receive the same within three to five days, please inform us immediately.
    <br><br>Regards,<br>CSA Engineering";
    $mail->addAttachment($file, $employeeName . "_Payslip.pdf");

    $mail->send();


    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

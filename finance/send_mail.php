<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files for authentication, database connection, and PHPMailer
require '../authentication.php'; // Admin authentication check
require '../conn.php'; // Database connection
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Auth check
$user_id = $_SESSION['admin_id'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
    exit();
}

if (isset($_GET['employee_id']) && isset($_GET['payslip_id'])) {
    $employee_id = intval($_GET['employee_id']);
    $payslip_id = intval($_GET['payslip_id']); // Get the payslip_id

    // Fetch employee information including email and record date based on payslip_id
    $sql = "SELECT `payslip_id`, `expenses`, `employee_id`, `fullName`, `payslip_month`, 
                   `email_id`, `salary`, `bank_name`, `account_no`, `total_pay`, 
                   `designation`, `department`, `doj`, `record_date` 
            FROM csa_finance_payslip_records 
            WHERE employee_id = $employee_id AND payslip_id = $payslip_id"; // Use both IDs
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $payslipData = $result->fetch_assoc();

        // Prepare email details
        $email = $payslipData['email_id'];
        $fullName = $payslipData['fullName'];
        $recordDate = $payslipData['record_date']; // Get the record_date

        // Format the date for the filename
        $day = date('d', strtotime($recordDate)); // Day
        $month = date('m', strtotime($recordDate)); // Month
        $year = date('Y', strtotime($recordDate)); // Full year
        $formattedTime = date('His', strtotime($recordDate)); // Current time in HHMMSS format

        // Construct the expected PDF filename in the format "FullName_DDMMYYYY_HHMMSS_EmployeeID.pdf"
        $pdfFileName = "{$fullName}_{$day}{$month}{$year}_{$formattedTime}_{$employee_id}.pdf"; // Adjusted to full date format
        $pdfPath = "./Records/" . $pdfFileName; // Complete path to the PDF

        // Debugging output
        error_log("Expected PDF filename: " . $pdfFileName); // Log the expected PDF filename
        error_log("Expected PDF path: " . $pdfPath); // Log the expected PDF path

        // Check if the PDF file exists
        if (!file_exists($pdfPath)) {
            error_log("PDF file not found: " . $pdfPath); // Log the missing file
            // Log the contents of the Records directory for debugging
            $files = scandir('./Records/');
            error_log("Contents of Records directory: " . implode(', ', $files)); // Log directory contents
            echo "<script>alert('PDF file not found: " . htmlspecialchars($pdfPath) . "'); window.location.href = 'payslip_records.php';</script>"; // Alert and redirect
            exit();
        }

        // Send email
        $mail = new PHPMailer(true);
        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'engineering@csaengineering.com.au'; // Your email
            $mail->Password = 'kezfduovpirmalcs'; // Your email password or App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('engineering@csaengineering.com.au', 'CSA Finance');
            $mail->addAddress($email, $fullName); // Add a recipient

            // Email content
            $mail->isHTML(true);
            // Fetch payslip details including payslip_month and record_date based on payslip_id
            $sql = "SELECT `payslip_id`, `payslip_month`, `record_date` 
        FROM csa_finance_payslip_records 
        WHERE employee_id = $employee_id AND payslip_id = $payslip_id"; // Use both IDs
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $payslipData = $result->fetch_assoc();

                // Extract payslip month and record date
                $payslip_month = $payslipData['payslip_month'];
                $recordDate = $payslipData['record_date'];

                // Set email subject using payslip month and record date
                $mail->Subject = 'PAYMENT INTIMATION for ' . $payslip_month . ' - ' . date("d/m/Y");
            }
                $mail->Body = "Hi $fullName,<br><br>
                           We have released payment as per attached details through E-Payment. 
                           If you do not receive the same within three to five days, please inform us immediately.
                           <br><br>Regards,<br>CSA Engineering";

            // Attach PDF
            $mail->addAttachment($pdfPath, $fullName . '_Payslip.pdf'); // Attach the PDF file

            // Debugging output for attachment
            error_log("Attaching PDF: " . $pdfPath); // Log the attachment attempt

            // Send the email
            if ($mail->send()) {
                echo "<script>alert('Email sent successfully to " . htmlspecialchars($fullName) . "'); window.location.href = 'payslip_records.php?success=email_sent';</script>"; // Alert and redirect
                exit();
            } else {
                throw new Exception('Mail not sent.'); // If mail does not send
            }
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $e->getMessage()); // Log error message
            echo "<script>alert('Mailer Error: " . htmlspecialchars($e->getMessage()) . "'); window.location.href = 'payslip_records.php';</script>"; // Alert and redirect
            exit();
        }
    } else {
        echo "<script>alert('No data found for the employee ID: " . htmlspecialchars($employee_id) . "'); window.location.href = 'payslip_records.php';</script>"; // Alert and redirect for no data
        exit();
    }
} else {
    echo "<script>alert('No employee ID or payslip ID provided.'); window.location.href = 'payslip_records.php';</script>"; // Alert and redirect for no IDs
    exit();
}

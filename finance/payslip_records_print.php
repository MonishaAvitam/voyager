<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // Admin authentication check 
require '../conn.php';

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}

if (isset($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];

    // Fetch payslip records for the employee
    $sql = "SELECT * FROM csa_finance_payslip_records WHERE employee_id = ? ORDER BY payslip_month DESC LIMIT 1"; // Get the most recent payslip
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employee_id); // Assuming employee_id is a string; adjust if it's an integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $payslipRecord = $result->fetch_assoc();
        $fullName = trim($payslipRecord['fullName']); // Just trim the name, do not replace spaces

        // Check if 'record_date' exists in the record
        if (isset($payslipRecord['record_date']) && !empty($payslipRecord['record_date'])) {
            // Fetch the date and time of the record (e.g., '2024-10-22 08:22:32')
            $recordDate = $payslipRecord['record_date'];
            $recordDateTime = $recordDate;
            $formattedDate = date("dmY_His", strtotime($recordDate)) . '_' . $employee_id;

            $pdfFileName = "{$fullName}_{$formattedDate}.pdf";
            $pdfPath = './Records/' . $pdfFileName;


           
            // Debugging output for constructed filename
            echo "Record datetime for employee ID $employee_id: $recordDateTime<br>";
            echo "Constructed PDF filename: $pdfFileName<br>";

            // Check if the file exists
            if (file_exists($pdfPath)) {
                // Output the PDF file
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
                readfile($pdfPath);
                exit;
            } else {
                echo "File not found: $pdfFileName<br>";
                exit; // Stop execution since the file doesn't exist
            }
        } else {
            echo "Timestamp ('record_date') not found for employee ID: $employee_id<br>";
            exit;
        }
    } else {
        echo "No payslip records found for this employee.<br>";
    }
} else {
    echo "No employee ID provided.<br>";
}
?>
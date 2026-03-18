<?php

include '../conn.php'; // Include your database connection file
require '../vendor/autoload.php'; // Include Composer's autoloader

use Google\Client;
use Google\Service\Drive;
use PhpOffice\PhpWord\TemplateProcessor;

// Initialize Google Client
$client = new Client();
$client->setAuthConfig('../google-drive/credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// Path to the DOCX template
$templatePath = './PaySlipTemplate.docx';
$templateProcessor = new TemplateProcessor($templatePath);



// Function to convert numbers to words
function convertNumberToWords($number)
{
    $units = ["", "One ", "Two ", "Three ", "Four ", "Five ", "Six ", "Seven ", "Eight ", "Nine "];
    $teens = ["Ten ", "Eleven ", "Twelve ", "Thirteen ", "Fourteen ", "Fifteen ", "Sixteen ", "Seventeen ", "Eighteen ", "Nineteen "];
    $tens = ["", "", "Twenty ", "Thirty ", "Forty ", "Fifty ", "Sixty ", "Seventy ", "Eighty ", "Ninety "];
    $thousands = ["", "Thousand", "Million", "Billion", "Trillion"];

    if ($number == 0) {
        return "Zero";
    }

    $numInWords = [];
    $i = 0;

    do {
        $chunk = $number % 1000;
        if ($chunk !== 0) {
            $chunkInWords = "";

            // Convert hundreds place
            if ($chunk >= 100) {
                $chunkInWords .= $units[floor($chunk / 100)] . " Hundred ";
                $chunk %= 100;
            }

            // Convert tens and units place
            if ($chunk >= 10 && $chunk <= 19) {
                $chunkInWords .= $teens[$chunk - 10];
            } else {
                $chunkInWords .= $tens[floor($chunk / 10)];
                $chunk %= 10;
                $chunkInWords .= $units[$chunk];
            }

            // Append the chunk with its corresponding thousand unit
            $numInWords[] = trim($chunkInWords) . " " . $thousands[$i];
        }

        $number = floor($number / 1000);
        $i++;
    } while ($number > 0);

    return implode(" ", array_reverse($numInWords));
}

// Get employee_id from request
$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

if ($employee_id > 0) {
    // Fetch payslip record for the specific employee_id
    $result = mysqli_query($conn, "
    SELECT p.`employee_id`, p.`fullName`, p.`email_id`, p.`salary`, p.`expenses`, p.`CL`, p.`SL`, p.`bank_name`, p.`account_no`, p.`total_pay`, p.`payslip_month`, p.`doj`, 
           e.`designation`, e.`department`, p.`record_date`, p.`SL`, p.`CL`,  e.`pan_id`, p.`Others`, p.`deduction`
    FROM `csa_finance_payslip` p
    JOIN `csa_finance_employee_info` e ON p.`employee_id` = e.`employee_id`
    WHERE p.`employee_id` = $employee_id
    ");

    try {
        // Fetch employee info before the loop
        $empIdQuery = mysqli_query($conn, "SELECT `Payslip_Emp_Id`, `CL`, `SL` FROM `csa_finance_employee_info` WHERE `employee_id` = $employee_id");
        $empIdRow = mysqli_fetch_assoc($empIdQuery);

        if (mysqli_num_rows($result) > 0) {
            while ($payslipRow = mysqli_fetch_assoc($result)) {
                // Get values from the payslip, default to 0 if missing or empty
                $employeeName = $payslipRow['fullName'];
                $bankName = $payslipRow['bank_name'];
                $amount = $payslipRow['total_pay'];
                $panId = !empty($payslipRow['pan_id']) ? $payslipRow['pan_id'] : 'N/A';




                $currentDate = new DateTime();

                // Calculate last month
                // Check if a stored month exists in the cookie
                if (isset($_COOKIE['selectedMonth'])) {
                    $selectedMonth = new DateTime($_COOKIE['selectedMonth']);
                } else {
                    $selectedMonth = new DateTime(); // Default to the current month
                }

                // Calculate the last pay period (15th of last month to 14th of this month)
                $lastMonth = clone $selectedMonth;
                $lastMonth->modify('first day of this month')->modify('-1 month')->setDate($lastMonth->format('Y'), $lastMonth->format('m'), 15);

                $presentMonth = clone $selectedMonth;
                $presentMonth->modify('first day of this month')->setDate($presentMonth->format('Y'), $presentMonth->format('m'), 14);

                // Format the payslip month
                $payslip_month = $lastMonth->format('d/m/y') . ' TO ' . $presentMonth->format('d/m/y');







                $fullAccountNumber = $payslipRow['account_no'];
                $maskedAccountNumber = 'xxxx-xxx-' . substr($fullAccountNumber, -4);
                $Doj = $payslipRow['doj'];

                // Default to 0 if empty
                $salary = !empty($payslipRow['salary']) ? floatval($payslipRow['salary']) : 0;
                $otherExpense = !empty($payslipRow['expenses']) ? floatval($payslipRow['expenses']) : 0;
                $deduction = !empty($payslipRow['deduction']) ? floatval($payslipRow['deduction']) : 0;
                // $others =   $payslipRow['Others'] ?? 'N/A';

                // Get CL, SL, PL from the previously fetched employee info
                $cl = isset($empIdRow['CL']) ? floatval($empIdRow['CL']) : 0; // Ensure CL is a float
                $sl = isset($empIdRow['SL']) ? floatval($empIdRow['SL']) : 0; // Ensure SL is a float

                // Calculate total salary
                
                $total_salary = $salary + $otherExpense + (is_numeric($others) ? $others : 0) - $deduction;


                // Convert total salary to words
                $amountInWords = convertNumberToWords($total_salary);
                $bcl = 10 - $cl;
                $bsl = 3 - $sl;

                // Fetch Payslip Employee ID
                $payslipEmpId = $empIdRow['Payslip_Emp_Id'] ?? 'N/A'; // Default to 'N/A' if no result

                // Define replacements
                $replacements = [
                    '{EMPLOYEE_NAME}' => $employeeName,
                    '{BANK_NAME}' => $bankName,
                    '{AMOUNT_IN_WORDS}' => $amountInWords,
                    '{PAY_MONTH}' => $payslip_month,
                    '{SALARY}' => '₹'.$salary,
                    '{DEDUCTION}' => !empty($deduction) ? '₹'.$deduction : '',
                    '{DESIGNATION}' => $payslipRow['designation'],
                    '{DEPARTMENT}' => $payslipRow['department'],
                    '{ACCOUNT_NUMBER}' => $maskedAccountNumber,
                    '{EMPLOYEE_ID}' => $payslipEmpId, // Use the new fetched Payslip_Emp_Id
                    '{PAN_ID}' => $panId,
                    '{DOJ}' => $Doj,
                    '{OTHER_EXPENSES}' => !empty($otherExpense) ? '₹'.$otherExpense : '',
                    '{DS}' => $deduction,
                    '{TOTAL_SALARY}' => '₹'.$total_salary,
                    '{TOTAL_IN_WORDS}' => $amountInWords,
                    '{ACL}' => $cl > 0 ? $cl : 'N/A',  // Show 'N/A' if $cl is 0
                    '{BCL}' => $bcl,
                    '{ASL}' => $sl > 0 ? $sl : 'N/A',  // Show 'N/A' if $sl is 0
                    '{BSL}' => $bsl
                ];

                // Replace placeholders in the template
                foreach ($replacements as $placeholder => $replacement) {
                    $templateProcessor->setValue($placeholder, $replacement);
                }

                // Create directory if it doesn't exist
                $directory = './Records/';
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }

                // Save the modified DOCX file
                $outputPath = $directory . 'modified_document.docx';
                $templateProcessor->saveAs($outputPath);

                // Sanitize employee name for file naming
                $safeEmployeeName = preg_replace('/[\/\\\:\*\?\"<>\|]/', '_', $employeeName);
                $safePayMonthName = preg_replace('/[\/\\\:\*\?\"<>\|]/', '_', $payMonth);
                $safeFileName = $safeEmployeeName . '_' . $safePayMonthName;

                // Upload DOCX to Google Drive
                $fileMetadata = new Drive\DriveFile([
                    'name' => $safeFileName . '.docx',
                    'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ]);

                $content = file_get_contents($outputPath);
                $file = $service->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'uploadType' => 'multipart'
                ]);

                // Convert DOCX to Google Docs
                $copiedFileId = $file->id;
                $newFile = new Drive\DriveFile([
                    'name' => $safeFileName,
                    'mimeType' => 'application/vnd.google-apps.document'
                ]);
                $fileCopy = $service->files->copy($copiedFileId, $newFile);

                // Export Google Docs file as PDF
                $exportMimeType = 'application/pdf';
                $response = $service->files->export($fileCopy->id, $exportMimeType, [
                    'alt' => 'media'
                ]);

                // Save PDF and check
                $pdfPath = $directory . $safeFileName . '.pdf';
                file_put_contents($pdfPath, $response->getBody());

                // Check if the PDF file was created successfully
                if (!file_exists($pdfPath) || filesize($pdfPath) === 0) {
                    echo 'Error: PDF file was not created.';
                    exit;
                }

                // Set headers to display PDF in the browser
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($pdfPath));
                header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');

                // Read and output the PDF file
                ob_clean(); // Clean the output buffer
                flush(); // Flush the system output buffer
                readfile($pdfPath);
                exit;
            }
        } else {
            echo "No records found for employee ID: $employee_id<br>";
        }
    } catch (Exception $error) {
        echo "Error: " . $error->getMessage();
    }
} else {
    echo "Invalid employee ID.";
}

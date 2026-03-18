<?php
// payslip_worker.php

set_time_limit(0);
ini_set('memory_limit', '512M');

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

function convertNumberToWords($number)
{
    $units = ["", "One ", "Two ", "Three ", "Four ", "Five ", "Six ", "Seven ", "Eight ", "Nine "];
    $teens = ["Ten ", "Eleven ", "Twelve ", "Thirteen ", "Fourteen ", "Fifteen ", "Sixteen ", "Seventeen ", "Eighteen ", "Nineteen "];
    $tens = ["", "", "Twenty ", "Thirty ", "Forty ", "Fifty ", "Sixty ", "Seventy ", "Eighty ", "Ninety "];
    $thousands = ["", "Thousand", "Million", "Billion", "Trillion"];

    if ($number == 0)
        return "Zero";

    $numInWords = [];
    $i = 0;

    while ($number > 0) {
        $chunk = $number % 1000;
        if ($chunk != 0) {
            $chunkInWords = "";

            if ($chunk >= 100) {
                $chunkInWords .= $units[floor($chunk / 100)] . " Hundred ";
                $chunk %= 100;
            }

            if ($chunk >= 10 && $chunk <= 19) {
                $chunkInWords .= $teens[$chunk - 10];
            } else {
                $chunkInWords .= $tens[floor($chunk / 10)];
                $chunk %= 10;
                $chunkInWords .= $units[$chunk];
            }

            $numInWords[] = trim($chunkInWords) . " " . $thousands[$i];
        }
        $number = floor($number / 1000);
        $i++;
    }

    return trim(implode(" ", array_reverse($numInWords)));
}

// Setup Google Client & Drive service
$client = new Client();
$client->setAuthConfig('../google-drive/credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// DOCX template path and output directory
$templatePath = './PaySlipTemplate.docx';
$directory = './Records/';
if (!is_dir($directory))
    mkdir($directory, 0777, true);

$batchSize = 5;

$sql = "SELECT * FROM payslip_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT $batchSize";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "No pending jobs to process.\n";
    exit;
}

while ($job = $result->fetch_assoc()) {
    $jobId = $job['id'];
    $employeeId = (int) $job['employee_id'];
    $Batch_no = $job['Batch_no']; // <- Add this line

    $conn->query("UPDATE payslip_queue SET status='processing', updated_at=NOW() WHERE id=$jobId");

    try {
        // Fetch employee payslip data
        $sqlEmp = "SELECT e.email_id, p.*, e.designation, e.department, e.pan_id, e.CL, e.SL, e.Payslip_Emp_Id, e.Name, e.bank_name, e.account_no, e.doj
           FROM batch_email p
           JOIN csa_finance_employee_info e ON p.employee_id = e.employee_id
           WHERE p.employee_id = $employeeId
             AND p.Batch_no = '$Batch_no'
           LIMIT 1";

        $resEmp = $conn->query($sqlEmp);

        if (!$resEmp || $resEmp->num_rows === 0) {
            throw new Exception("No payslip found for employee ID $employeeId");
        }

        $payslipRow = $resEmp->fetch_assoc();

        $templateProcessor = new TemplateProcessor($templatePath);

        $fullAccountNumber = $payslipRow['account_no'];
        $maskedAccountNumber = 'xxxx-xxx-' . substr($fullAccountNumber, -4);

        $currentDate = new DateTime();
        $selectedMonth = isset($_COOKIE['selectedMonth']) ? new DateTime($_COOKIE['selectedMonth']) : new DateTime();

        $payslip_month = $payslipRow['payslip_month'];

        $salary = floatval($payslipRow['salary'] ?? 0);
        $otherExpense = floatval($payslipRow['expenses'] ?? 0);
        $deduction = floatval($payslipRow['deduction'] ?? 0);
        $others = is_numeric($payslipRow['Others']) ? floatval($payslipRow['Others']) : 0;

        $total_salary = $salary + $otherExpense + $others - $deduction;
        $amountInWords = convertNumberToWords($total_salary);

        $cl = floatval($payslipRow['CL'] ?? 0);
        $sl = floatval($payslipRow['SL'] ?? 0);
        $bcl = 10 - $cl;
        $bsl = 3 - $sl;

        $panId = !empty($payslipRow['pan_id']) ? $payslipRow['pan_id'] : 'N/A';
        $Doj = $payslipRow['doj'];
        $employeeName = $payslipRow['fullName'];
        $bankName = $payslipRow['bank_name'];
        $email = $payslipRow['email_id'];
        $payslipEmpId = $payslipRow['Payslip_Emp_Id'] ?? 'N/A';

        $replacements = [
            '{EMPLOYEE_NAME}' => $employeeName,
            '{BANK_NAME}' => $bankName,
            '{AMOUNT_IN_WORDS}' => $amountInWords,
            '{PAY_MONTH}' => $payslip_month,
            '{SALARY}' => '₹' . $salary,
            '{DEDUCTION}' => $deduction > 0 ? '₹' . $deduction : '',
            '{DESIGNATION}' => $payslipRow['designation'],
            '{DEPARTMENT}' => $payslipRow['department'],
            '{ACCOUNT_NUMBER}' => $maskedAccountNumber,
            '{EMPLOYEE_ID}' => $payslipEmpId,
            '{PAN_ID}' => $panId,
            '{DOJ}' => $Doj,
            '{OTHER_EXPENSES}' => $otherExpense > 0 ? '₹' . $otherExpense : '',
            '{DS}' => $deduction,
            '{TOTAL_SALARY}' => '₹' . $total_salary,
            '{TOTAL_IN_WORDS}' => $amountInWords,
            '{ACL}' => $cl > 0 ? $cl : 'N/A',
            '{BCL}' => $bcl,
            '{ASL}' => $sl > 0 ? $sl : 'N/A',
            '{BSL}' => $bsl,
        ];

        foreach ($replacements as $placeholder => $value) {
            $templateProcessor->setValue($placeholder, $value);
        }

        $tempDocxPath = $directory . 'payslip_' . $currentDate->format('dmY_His') . '_' . $employeeId . '.docx';
        $templateProcessor->saveAs($tempDocxPath);

        // Upload DOCX to Google Drive
        $fileMetadata = new DriveFile([
            'name' => basename($tempDocxPath),
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
        $content = file_get_contents($tempDocxPath);
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'uploadType' => 'multipart',
        ]);
        $fileId = $file->id;

        // Copy as Google Docs
        $copiedFileMetadata = new DriveFile([
            'name' => $employeeName,
            'mimeType' => 'application/vnd.google-apps.document',
        ]);
        $copiedFile = $service->files->copy($fileId, $copiedFileMetadata);
        $copiedFileId = $copiedFile->id;

        // Export PDF
        $exportMimeType = 'application/pdf';
        $response = $service->files->export($copiedFileId, $exportMimeType, ['alt' => 'media']);
        $pdfPath = $directory . $employeeName . '_' . $currentDate->format('dmY_His') . '_' . $employeeId . '.pdf';
        file_put_contents($pdfPath, $response->getBody());

        // Cleanup Google Drive files and local docx
        $service->files->delete($fileId);
        $service->files->delete($copiedFileId);
        unlink($tempDocxPath);

        // Send email via PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'engineering@csaengineering.com.au';
        $mail->Password = 'kezfduovpirmalcs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('engineering@csaengineering.com.au', 'CSA Finance');
        $mail->addAddress($email, $employeeName);

        $mail->isHTML(true);
        $mail->Subject = 'PAYMENT INTIMATION ' . date("15/m/Y");
        $mail->Body = "Hi,<br><br>
            We have released payment as per attached details through E-Payment. 
            If you do not receive the same within three to five days, please inform us immediately.
            <br><br>Regards,<br>CSA Engineering";
        $mail->addAttachment($pdfPath, $employeeName . '_Payslip.pdf');
        $mail->send();

        $conn->query("UPDATE csa_finance_payslip SET mail_status = 'sent' WHERE employee_id = $employeeId");

        // Insert payslip record
        $insertPayslipRecordQuery = "INSERT INTO csa_finance_payslip_records (payslip_id, expenses, employee_id, fullName, payslip_month, email_id, salary, bank_name, account_no, total_pay, designation, department, doj, record_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $recordDate = $currentDate->format('Y-m-d H:i:s');
        $stmt = $conn->prepare($insertPayslipRecordQuery);
        if ($stmt) {
            $stmt->bind_param(
                'iisssssssdssss',
                $payslipRow['payslip_id'],
                $otherExpense,
                $employeeId,
                $employeeName,
                $payslip_month,
                $email,
                $salary,
                $bankName,
                $maskedAccountNumber,
                $total_salary,
                $payslipRow['designation'],
                $payslipRow['department'],
                $Doj,
                $recordDate
            );
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception("Failed to prepare payslip records insert statement: " . $conn->error);
        }

        unlink($pdfPath);

        $conn->query("UPDATE payslip_queue SET status = 'completed', updated_at=NOW() WHERE id = $jobId");

        echo "Payslip processed for employee ID $employeeId\n";

    } catch (Exception $ex) {
        $error = $conn->real_escape_string($ex->getMessage());
        $conn->query("UPDATE payslip_queue SET status = 'failed', last_error = '$error', attempts = attempts + 1, updated_at=NOW() WHERE id = $jobId");
        echo "Error processing employee ID $employeeId: " . $ex->getMessage() . "\n";
    }
}

echo "Batch processing complete.\n";
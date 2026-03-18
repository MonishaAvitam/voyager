        <?php
        require '../authentication.php'; // Admin authentication check
        @require 'fpdf/fpdf.php';
        include './include/login_header.php';
        require '../conn.php';

        require '../vendor/autoload.php';

        use Google\Client;
        use Google\Service\Drive;
        use PhpOffice\PhpWord\TemplateProcessor;

        $client = new Client();
        $client->setAuthConfig('../google-drive/credentials.json');
        $client->addScope(Drive::DRIVE);
        $service = new Drive($client);

        // Path to the DOCX template
        $templatePath = './PaySlipTemplate.docx';
        $templateProcessor = new TemplateProcessor($templatePath);


        // Include PHPMailer library
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        require '../PHPMailer/src/Exception.php';
        require '../PHPMailer/src/PHPMailer.php';
        require '../PHPMailer/src/SMTP.php';


        function convertNumberToWords($number)
        {
            $words = "";

            $units = ["", "One ", "Two ", "Three ", "Four ", "Five ", "Six ", "Seven ", "Eight ", "Nine "];
            $teens = ["Ten ", "Eleven ", "Twelve ", "Thirteen ", "Fourteen ", "Fifteen ", "Sixteen ", "Seventeen ", "Eighteen ", "Nineteen "];
            $tens = ["", "", "Twenty ", "Thirty ", "Forty ", "Fifty ", "Sixty ", "Seventy ", "Eighty ", "Ninety "];
            $thousands = ["", "Thousand", "Million", "Billion", "Trillion"];

            $numInWords = [];

            if ($number == 0) {
                $numInWords[] = "Zero";
            } else {
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
                        $numInWords[] = $chunkInWords . " " . $thousands[$i];
                    }

                    $number = floor($number / 1000);
                    $i++;
                } while ($number > 0);
            }

            // Reverse the array and concatenate to get the final result
            $words = implode(" ", array_reverse($numInWords));

            return $words;
        }


        

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['selected_employees']) && is_array($_POST['selected_employees'])) {
                $selectedEmployeeIds = array_map('intval', $_POST['selected_employees']); // Ensure IDs are integers

                // Check if all selected employees exist in csa_finance_payslip
                $employeeIdList = implode(',', $selectedEmployeeIds);
                $payslipCheckQuery = "SELECT COUNT(*) AS total FROM csa_finance_payslip WHERE employee_id IN ($employeeIdList)";
                $result = $conn->query($payslipCheckQuery);

                if ($result) {
                    $row = $result->fetch_assoc();
                    if ($row['total'] != count($selectedEmployeeIds)) {
                        header("Location: payslip.php?error=missing_employee");
                        exit();
                    }
                } else {
                    header("Location: payslip.php?error=query_failed");
                    exit();
                }

                foreach ($selectedEmployeeIds as $selectedEmployeeId) {
                    $employeeId = (int)$selectedEmployeeId;

                    // Fetch employee email
                    $sql = "SELECT email_id FROM csa_finance_employee_info WHERE employee_id = $employeeId";
                    $info = $conn->query($sql);

                    if ($info && $info->num_rows > 0) {
                        $row = $info->fetch_assoc();
                        $email = $row['email_id'];

                        // Fetch payslip data
                        $payslipQuery = "SELECT p.`employee_id`, p.`fullName`, p.`email_id`, p.`salary`, p.`expenses`, 
                                            p.`bank_name`, p.`account_no`, p.`total_pay`, p.`payslip_month`, p.`doj`, 
                                            e.`designation`, e.`department`, p.`record_date`, e.`pan_id`, p.`Others`, p.`deduction`
                                        FROM `csa_finance_payslip` p
                                        JOIN `csa_finance_employee_info` e ON p.`employee_id` = e.`employee_id`
                                        WHERE p.`employee_id` = $employeeId";

                        $payslipResult = mysqli_query($conn, $payslipQuery);

                        if (mysqli_num_rows($payslipResult) > 0) {
                            $payslipRow = mysqli_fetch_assoc($payslipResult);

                            // Process the template for this employee
                            $templateProcessor = new TemplateProcessor($templatePath);
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

                            // Fetch CL, SL, PL from csa_finance_employee_info
                            $empIdQuery = mysqli_query($conn, "SELECT `CL`, `SL`,  `Payslip_Emp_Id` FROM `csa_finance_employee_info` WHERE `employee_id` = $employeeId");
                            $empIdRow = mysqli_fetch_assoc($empIdQuery);

                            // Get values and ensure they are floats
                            $cl = isset($empIdRow['CL']) ? floatval($empIdRow['CL']) : 0;
                            $sl = isset($empIdRow['SL']) ? floatval($empIdRow['SL']) : 0;

                            // Calculate total salary
                            $others = isset($payslipRow['Others']) && is_numeric($payslipRow['Others']) ? floatval($payslipRow['Others']) : 'N/A';

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


                            // Process your template with replacements here...            

                            foreach ($replacements as $placeholder => $replacement) {
                                $templateProcessor->setValue($placeholder, $replacement);
                            }

                            // Define the output path for the modified DOCX file
                            // Define the output path for the modified document
                            // Assuming $employeeId is already defined and $currentDate is a DateTime object
                            $outputPath = './Records/payslip_' . $currentDate->format('dmY_His') . '_' . $employeeId . '.pdf';

                            // Save the modified document
                            $templateProcessor->saveAs($outputPath);

                            // Ensure the directory exists
                            $directory = './Records/';
                            if (!is_dir($directory)) {
                                mkdir($directory, 0777, true);
                            }

                            // Upload DOCX to Google Drive
                            $fileMetadata = new Drive\DriveFile([
                                'name' => basename($outputPath),
                                'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            ]);

                            $content = file_get_contents($outputPath);
                            $file = $service->files->create($fileMetadata, [
                                'data' => $content,
                                'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'uploadType' => 'multipart'
                            ]);

                            $fileId = $file->id;

                            // Convert DOCX to Google Docs format
                            $copiedFileMetadata = new Drive\DriveFile([
                                'name' => $employeeName,
                                'mimeType' => 'application/vnd.google-apps.document'
                            ]);

                            $copiedFile = $service->files->copy($fileId, $copiedFileMetadata);
                            $copiedFileId = $copiedFile->id;

                            // Export Google Docs file as PDF
                            $exportMimeType = 'application/pdf';
                            $response = $service->files->export($copiedFileId, $exportMimeType, [
                                'alt' => 'media'
                            ]);

                            // Define the path for the PDF file
                            $pdfPath = $directory . $employeeName . '_' . $currentDate->format('dmY_His') . '_' . $employeeId . '.pdf';

                            // Save the PDF file
                            file_put_contents($pdfPath, $response->getBody());

                            // Clean up
                            $service->files->delete($fileId); // Delete original DOCX from Google Drive
                            $service->files->delete($copiedFileId); // Delete Google Docs file
                            unlink($outputPath);
                            // Send email with PHPMailer
                            $mail = new PHPMailer(true);
                            try {
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

                                // Attach PDF
                                $mail->addAttachment($pdfPath, $employeeName . '_Payslip.pdf');

                                // Send the email
                                $mail->send();

                                // Update mail_status to 'sent'
                                $conn->query("UPDATE `csa_finance_payslip` SET mail_status = 'sent' WHERE employee_id = $employeeId");

                                // Insert a copy of the payslip data into csa_finance_payslip_records
                                // Prepare the insert query
                                $insertPayslipRecordQuery = "INSERT INTO csa_finance_payslip_records (payslip_id, expenses, employee_id, fullName, 
                                                    payslip_month, email_id, salary, bank_name, account_no, total_pay, designation, 
                                                    department, doj, record_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                                // Format the current date
                                $recordDate = $currentDate->format('Y-m-d H:i:s');

                                // First, fetch the email from csa_finance_employee_info using the employee_id
                                $emailFetchQuery = "SELECT email_id FROM csa_finance_employee_info WHERE employee_id = ?";
                                $emailStmt = $conn->prepare($emailFetchQuery);
                                $emailStmt->bind_param('i', $employeeId); // Bind employee ID to the query
                                $emailStmt->execute();
                                $emailResult = $emailStmt->get_result();

                                if ($emailResult->num_rows > 0) {
                                    $emailRow = $emailResult->fetch_assoc();
                                    $email = $emailRow['email_id']; // Get the email

                                    // Now prepare to insert the data into csa_finance_payslip_records
                                    if ($stmt = $conn->prepare($insertPayslipRecordQuery)) {
                                        // Bind parameters
                                        $stmt->bind_param(
                                            'iisssssssdssss',
                                            $payslipRow['payslip_id'],     // i - integer
                                            $otherExpense,                 // d - double
                                            $employeeId,                   // i - integer
                                            $employeeName,                 // s - string
                                            $payslip_month,                // s - string
                                            $email,                        // s - string (fetched from csa_finance_employee_info)
                                            $salary,                       // d - double
                                            $bankName,                     // s - string
                                            $maskedAccountNumber,          // s - string
                                            $total_salary,                 // d - double
                                            $payslipRow['designation'],    // s - string
                                            $payslipRow['department'],     // s - string
                                            $Doj,                          // s - string
                                            $recordDate                    // s - string (formatted date)
                                        );

                                        // Execute the statement
                                        if ($stmt->execute()) {
                                            echo "Payslip record inserted successfully!";
                                        } else {
                                            echo "Error inserting payslip record: " . $stmt->error;
                                        }

                                        // Close the statement
                                        $stmt->close();
                                    } else {
                                        // Handle preparation error
                                        echo "Error preparing insert statement: " . $conn->error;
                                    }

                                    // Close the email statement
                                    $emailStmt->close();
                                } else {
                                    echo "Error: No email found for employee ID $employeeId";
                                }
                            } catch (Exception $e) {
                                echo "Mailer Error: " . $mail->ErrorInfo;

                                // Update mail_status to 'failed'
                                $conn->query("UPDATE `csa_finance_payslip` SET mail_status = 'failed' WHERE employee_id = $employeeId");
                            }
                        } else {
                            header("Location: payslip.php?error=form");
                            exit();
                        }
                    } else {
                        header("Location: payslip.php?error=missing_email");
                        exit();
                    }
                }

                header("Location: payslip.php?success=emails_sent");
                exit();
            }
        }















        ?>




















        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Select Employee</title>
        </head>

        <body>
            <h1 style="text-align: center;">Select Employee</h1>
        </body>

        </html>
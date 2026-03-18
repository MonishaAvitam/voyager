<?php
require_once '../vendor/autoload.php';
require '../conn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Check for connection errors
if ($conn->connect_error) {
    die('Could not Connect MySql Server:' . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if selected employees data is received and it's an array
    if (isset($_POST['selected_employees']) && is_array($_POST['selected_employees'])) {
        // Get the selected employee IDs
        $selectedEmployeeIds = $_POST['selected_employees'];

        // Check if there are selected employee IDs
        if (!empty($selectedEmployeeIds)) {
            // Construct the SQL query with prepared statements
            $sql = "SELECT Employee_id, Name, contact_number, email_id, bank_name, account_no, salary, doj, department, designation FROM csa_finance_employee_info WHERE Employee_id IN (";
            $sql .= rtrim(str_repeat('?,', count($selectedEmployeeIds)), ',') . ")";

            // Prepare the statement
            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bind_param(str_repeat('i', count($selectedEmployeeIds)), ...$selectedEmployeeIds);

            // Execute the query
            $stmt->execute();

            // Check for errors
            if ($stmt->error) {
                echo "Error executing query: " . $stmt->error;
                exit();
            }

            // Get the result set
            $result = $stmt->get_result();

            // Check if there are any rows returned
            if ($result->num_rows > 0) {
                // Create a new PhpSpreadsheet object
                $spreadsheet = new Spreadsheet();

                // Set up the Excel file (e.g., add headers)
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'Employee ID')
                    ->setCellValue('B1', 'Name')
                    ->setCellValue('C1', 'Contact Number')
                    ->setCellValue('D1', 'Email-Id')
                    ->setCellValue('E1', 'Bank Name')
                    ->setCellValue('F1', 'Account Number')
                    ->setCellValue('G1', 'Salary')
                    ->setCellValue('H1', 'Date of Joining')
                    ->setCellValue('I1', 'Department')
                    ->setCellValue('J1', 'Designation');
                // Add more headers as needed

                // Apply formatting to headers
                $spreadsheet->getActiveSheet()->getStyle('A1:J1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Fetch data from the database and populate the Excel file
                $row = 2;
                while ($employee = $result->fetch_assoc()) {
                    $spreadsheet->getActiveSheet()->setCellValue('A' . $row, $employee['Employee_id']);
                    $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $employee['Name']);
                    $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $employee['contact_number']);
                    $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $employee['email_id']);
                    $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $employee['bank_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('F' . $row, $employee['account_no']);
                    $spreadsheet->getActiveSheet()->setCellValue('G' . $row, $employee['salary']);
                    $spreadsheet->getActiveSheet()->setCellValue('H' . $row, $employee['doj']);
                    $spreadsheet->getActiveSheet()->setCellValue('I' . $row, $employee['department']);
                    $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $employee['designation']);
                    // Add more data cells as needed
                    $row++;
                }

                // Specify the directory where you want to save the Excel file
$directory = "./Excel/";

// Specify the directory where you want to save the Excel file
$directory = "./Excel/";

// Get the current timestamp
$currentTimestamp = date('Ymd_His');

// Construct the file name with the current month and timestamp
$fileName = "employee_data_$currentTimestamp.xlsx";

// Specify the full file path
$filePath = $directory . $fileName;

// Write the Excel file to the specified file path
$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

// Redirect the user to download the Excel file
header("Location: $filePath");

                // Terminate the script
                exit();
            } else {
                // Handle case when no employee IDs are found
                echo "No employee IDs found.";
            }
        } else {
            // Handle case when no employee IDs are selected
            echo "No employee IDs selected.";
        }
    } else {
        // Handle case when selected employee data is not received or is not an array
        echo "Invalid or missing selected employee data.";
    }
}
?>

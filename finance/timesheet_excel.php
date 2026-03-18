<?php
require_once '../vendor/autoload.php';
require '../conn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Check for connection errors
if ($conn->connect_error) {
    die('Could not Connect MySql Server:' . $conn->connect_error);
}

$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m');
$selected_month = date('m', strtotime($selectedDate));
$selected_year = date('Y', strtotime($selectedDate));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedDate'])) {
    $selectedDate = $_POST['selectedDate'];
    $selected_month = date('m', strtotime($selectedDate));
    $selected_year = date('Y', strtotime($selectedDate));
    $formattedDate = date('F Y', strtotime($selectedDate));

    // Check if there is a selected date
    if (!empty($selectedDate)) {
        // Construct the SQL query
        $sql = "
    SELECT 
        ta.fullname,
        ta.user_id,
        IFNULL(ROUND(SUM(md.meetingHours), 2), 0) AS ETMH,  -- Rounded total meeting hours to 2 decimal places
        IFNULL(ROUND((SELECT SUM(ts.working_hours) FROM timesheet ts WHERE ts.user_id = ta.user_id AND MONTH(ts.date_value) = ? AND YEAR(ts.date_value) = ?), 2), 0) AS ETWH, 
        IFNULL(ROUND((SELECT SUM(sj.workingHours) FROM smallJobs sj WHERE sj.user_id = ta.user_id AND MONTH(sj.job_date) = ? AND YEAR(sj.job_date) = ?), 2), 0) AS ETSH, 
        IFNULL(ROUND((SELECT SUM(l.hours) FROM leave_approval l WHERE l.employee_id = ta.user_id AND MONTH(l.leave_from) = ? AND YEAR(l.leave_from) = ?), 2), 0) AS ETLH   
    FROM 
        tbl_admin ta 
    LEFT JOIN 
        meeting_data md ON md.employee_id = ta.user_id AND MONTH(md.meeting_date) = ? AND YEAR(md.meeting_date) = ?
    GROUP BY
        ta.fullname, ta.user_id;
";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bind_param('iiiiiiii', $selected_month, $selected_year, $selected_month, $selected_year, $selected_month, $selected_year, $selected_month, $selected_year);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch data
while ($row = $result->fetch_assoc()) {
    // Process your data
}


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

        // Set up the header
        $sheet = $spreadsheet->getActiveSheet();
        $headerText = "Employee Timesheet for $formattedDate";
        $sheet->setCellValue('A1', $headerText);
        $sheet->mergeCells('A1:F1');

        // Style the header
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Set up the column headers
        $sheet->setCellValue('A2', 'User ID')
            ->setCellValue('B2', 'Name')
            ->setCellValue('C2', 'Meeting Hours')
            ->setCellValue('D2', 'Working Hours')
            ->setCellValue('E2', 'Small Job Hours')
            ->setCellValue('F2', 'Leave Hours');

        // Apply formatting to headers
        $sheet->getStyle('A2:F2')->applyFromArray([
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
        $row = 3;
        while ($employee = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $row, $employee['user_id']);
            $sheet->setCellValue('B' . $row, $employee['fullname']);
            $sheet->setCellValue('C' . $row, $employee['ETMH']);
            $sheet->setCellValue('D' . $row, $employee['ETWH']);
            $sheet->setCellValue('E' . $row, $employee['ETSH']);
            $sheet->setCellValue('F' . $row, $employee['ETLH']);
            $row++;
        }

        // Specify the directory where you want to save the Excel file
        $directory = "./Excel/";

        // Get the current timestamp
        $currentTimestamp = date('Ymd_His');

        // Construct the file name with the current month and timestamp
        $fileName = "employee_data_$selected_month-$selected_year.xlsx";

        // Specify the full file path
        $filePath = $directory . $fileName;

        // Write the Excel file to the specified file path
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Redirect the user to download the Excel file
        header("Location: $filePath");

        // Terminate the script
        exit();
    }
    } else {
        // Handle case when no employee data is found
        echo "No data found for the selected date.";
    }
} else {
    // Handle case when the request method is not POST
    echo "Invalid request method.";
}
?>
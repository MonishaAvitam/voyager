<?php
require_once '../vendor/autoload.php';
require '../conn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

if ($conn->connect_error) {
    die('Could not Connect MySql Server: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['selected_employees']) || !is_array($_POST['selected_employees']) || count($_POST['selected_employees']) === 0) {
        echo "No employees selected.";
        exit();
    }

    // Sanitize and prepare employee IDs
    $selectedEmployeeIds = array_map('intval', $_POST['selected_employees']);

    $placeholders = implode(',', array_fill(0, count($selectedEmployeeIds), '?'));

    $sql = "SELECT 
    e.Employee_id, 
    e.Name AS name,
    e.email_id AS recipientEmail,
IFNULL(p.salary, 0) + IFNULL(p.expenses, 0) AS amount,
    e.bank_name AS bankName,
    e.account_no AS accountNumber,
    e.department,
    e.designation,
    e.contact_number,
    e.doj,
    e.address_city,
    e.address_line1,
    e.address_postcode,
    e.address_state,
    e.account_type,
    e.ifsc_code,
    e.bank_address_line1,
    e.bank_city,
    e.bank_country_code

FROM csa_finance_employee_info e
LEFT JOIN csa_finance_payslip p 
    ON e.Employee_id = p.employee_id

WHERE e.Employee_id IN ($placeholders)";



    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($selectedEmployeeIds)), ...$selectedEmployeeIds);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "No employee data found.";
        exit();
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Airwallex batch transfer');

    $columns = [
        'Transfer to',
        'Transfer method',
        'Currency recipient gets',
        'Transfer amount in currency recipient gets',
        'Currency you pay',
        'Fee paid by',
        'Account name',
        'Bank identifier type',
        'Bank identifier value',
        'Account number',
        'Transfer purpose',
        'Reference',
        'Description (optional)',
        'Recipient type',
        'Country / region',
        'Street address',
        'City',
        'State / Province',
        'Postcode',
        'Request ID (optional)',
    ];

    // Set header row with grey background
    foreach ($columns as $i => $header) {
        $col = Coordinate::stringFromColumnIndex($i + 1);
        $cell = $col . '1';
        $sheet->setCellValue($cell, $header);
        $sheet->getStyle($cell)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],  // White font
                'size' => 10,
                'name' => 'Arial',
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '808080'], // Gray background (#808080)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ]
        ]);
    }
    $sheet->getRowDimension(1)->setRowHeight(39.6);



    $row = 2;
    function normalizeString($str)
    {
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // remove accents
        $str = strtolower(trim($str));                  // lowercase and trim
        $str = preg_replace('/[^a-z]/', '', $str);     // remove non-letters
        return $str;
    }

    while ($employee = $result->fetch_assoc()) {
        $data = [
            'India',                    // A
            'LOCAL',                    // B
            'INR',                      // C
            $employee['amount'],        // D
            'AUD',                      // E
            'Payer',                     // F
            $employee['name'],           // G
            'IFSC code',                 // H
            $employee['ifsc_code'],      // I
            $employee['accountNumber'],  // J
            'Professional / Business Services', // K
            'CSA',                       // L
            '',                          // M
            'Individual',                // N
            'India',                     // O
            $employee['address_line1'],  // P (Street address)
            $employee['address_city'],   // Q (City)
            '',                          // R (State / Province) -> dropdown will overwrite
            $employee['address_postcode'], // S (Postcode)
            '',                          // T (Request ID optional)
        ];



        foreach ($data as $i => $value) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            if (in_array($i, [9])) { // column 10 = index 9 = 'Account number'
                $sheet->setCellValueExplicit($col . $row, $value, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($col . $row, $value);
            }
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
        // Column index for 'State / Province' (1-based)
        $stateColIndex = 18; // Column R
        $stateColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($stateColIndex);

        // List of states for dropdown
        $states = [
            'Andaman and Nicobar Islands',
            'Andhra Pradesh',
            'Arunāchal Pradesh',
            'Assam',
            'Bihār',
            'Chandīgarh',
            'Chhattīsgarh',
            'Delhi',
            'Dādra and Nagar Haveli and Damān and Diu',
            'Goa',
            'Gujarāt',
            'Haryāna',
            'Himāchal Pradesh',
            'Jammu and Kashmīr',
            'Jhārkhand',
            'Karnātaka',
            'Kerala',
            'Ladākh',
            'Lakshadweep',
            'Madhya Pradesh',
            'Mahārāshtra',
            'Manipur',
            'Meghālaya',
            'Mizoram',
            'Nāgāland',
            'Odisha',
            'Pondicherry',
            'Punjab',
            'Rājasthān',
            'Sikkim',
            'Tamil Nādu',
            'Telangāna',
            'Tripura',
            'Uttar Pradesh',
            'Uttarākhand',
            'West Bengal'
        ];

        // Create dropdown validation
        $validation = $sheet->getCell($stateColLetter . $row)->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
            ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowInputMessage(true)
            ->setPromptTitle('Select State')
            ->setPrompt('Please select a state from the dropdown')
            ->setFormula1('"' . implode(',', $states) . '"');

        // Set value if it matches
        // Normalize function: remove accents, lowercase, trim


        // Find matching state in dropdown
        $matchedState = '';
        foreach ($states as $state) {
            if (normalizeString($state) === normalizeString($employee['address_state'])) {
                $matchedState = $state;
                break;
            }
        }

        // Set the dropdown value
        $sheet->setCellValue($stateColLetter . $row, $matchedState);



        $row++;
    }

    $lastColumn = Coordinate::stringFromColumnIndex(count($columns));
    $lastRow = $row - 1;

    $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
        'font' => [
            'name' => 'Arial',
            'size' => 10,
        ],
    ]);



    for ($i = 1; $i <= count($columns); $i++) {
        $colLetter = Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setWidth(20);  // fixed width, adjust number as needed
    }


    $directory = "./Excel/";
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $fileName = "Batch transfer template_" . date('Ymd') . ".xlsx";
    $filePath = $directory . $fileName;

    $writer = new Xlsx($spreadsheet);

    // Clear any output buffer to prevent file corruption
    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit();

}
?>
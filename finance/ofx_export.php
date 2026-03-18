<?php
require_once '../vendor/autoload.php';
require '../conn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

if ($conn->connect_error) {
    die('Could not Connect MySql Server: ' . $conn->connect_error);
}

function convertINRtoAUD($amountInINR)
{
    $url = "https://open.er-api.com/v6/latest/INR";
    $response = file_get_contents($url);
    if ($response === FALSE) {
        return "Error";
    }

    $data = json_decode($response, true);
    if (!isset($data['rates']['AUD'])) {
        return "Error";
    }

    $rate = $data['rates']['AUD'];
    $amountInAUD = floor($amountInINR * $rate * 100) / 100;
    return $amountInAUD;
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
    p.salary + IFNULL(p.expenses, 0) AS amount,  -- ✅ get salary + expenses from records only
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
    e.bank_country_code,
    e.bank_post_code

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

    $columns = [
        'name',
        'recipientEmail',
        'paymentReference',
        'receiverType',
        'companyRegistrationNumber',
        'amount',
        'sourceCurrency',
        'targetCurrency',
        'addressCountryCode',
        'addressCity',
        'addressFirstLine',
        'addressPostCode',
        'addressState',
        'abartn',
        'accountNumber',
        'accountType',
        'BIC',
        'sortCode',
        'ifscCode',
        'bankCode',
        'clabe',
        'IBAN',
        'BSB',
        'bankName',
        'bankAddressFirstLine',
        'bankCity',
        'bankPostcode',
        'Bank Country Code',
        'newZealandBankAccountIdentifier',
        'suffix',
        'bankNumber',
        'branchNumber',
        'branchCode'
    ];

    foreach ($columns as $i => $header) {
        $col = Coordinate::stringFromColumnIndex($i + 1);
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    $row = 2;
    while ($employee = $result->fetch_assoc()) {
        foreach ($columns as $i => $field) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $value = '';

            switch ($field) {
                case 'name':
                    $value = $employee['name'];
                    break;
                case 'recipientEmail':
                    $value = $employee['recipientEmail'];
                    break;
                case 'paymentReference':
                    $value = 'CSA';
                    break;
                case 'receiverType':
                    $value = 'Individual';
                    break;
                case 'companyRegistrationNumber':
                    $value = '';
                    break;
                case 'amount':
                    if (!function_exists('convertINRtoAUD')) {
                        function convertINRtoAUD($amountInINR)
                        {
                            $url = "https://open.er-api.com/v6/latest/INR";
                            $response = file_get_contents($url);
                            if ($response === FALSE) {
                                return "Error fetching rate.";
                            }

                            $data = json_decode($response, true);
                            if (!isset($data['rates']['AUD'])) {
                                return "Invalid API response.";
                            }

                            $rate = $data['rates']['AUD'];

                            // Full precision conversion
                            $amountInAUD = $amountInINR * $rate;
                            $amountInAUD = floor($amountInAUD * 100) / 100;

                            return $amountInAUD;
                        }
                    }

                    $value = convertINRtoAUD($employee['amount']);
                    break;

                case 'sourceCurrency':
                    $value = 'AUD';
                    break;
                case 'targetCurrency':
                    $value = 'INR';
                    break;
                case 'addressCountryCode':
                    $value = 'IN';
                    break;
                case 'addressCity':
                    $value = $employee['address_city'];
                    break;
                case 'addressFirstLine':
                    $value = $employee['address_line1'];
                    break;
                case 'addressPostCode':
                    $value = $employee['address_postcode'];
                    break;
                case 'addressState':
                    $value = $employee['address_state'];
                    break;
                case 'abartn':
                    $value = "";
                    break;
                case 'BIC':
                    $value = "";
                    break;
                case 'sortCode':
                    $value = "";
                    break;
                case 'bankCode':
                    $value = "";
                    break;
                case 'clabe':
                    $value = "";
                    break;
                case 'IBAN':
                    $value = "";
                    break;
                case 'BSB':
                    $value = "";
                    break;
                case 'bankPostcode':
                    $value = $employee['bank_post_code'];
                    break;
                case 'newZealandBankAccountIdentifier':
                    $value = "";
                    break;
                case 'suffix':
                    $value = "";
                    break;
                case 'bankNumber':
                    $value = "";
                    break;
                case 'branchNumber':
                    $value = "";
                    break;
                case 'branchCode':
                    $value = '';
                    break;
                case 'accountNumber':
                    $sheet->setCellValueExplicit($col . $row, $employee['accountNumber'], DataType::TYPE_STRING);
                    continue 2;
                case 'accountType':
                    $value = $employee['account_type'];
                    break;
                case 'ifscCode':
                    $value = $employee['ifsc_code'];
                    break;
                case 'bankName':
                    $value = $employee['bankName'];
                    break;
                case 'bankAddressFirstLine':
                    $value = $employee['bank_address_line1'];
                    break;
                case 'bankCity':
                    $value = $employee['bank_city'];
                    break;
                case 'Bank Country Code':
                    $value = $employee['bank_country_code'];
                    break;
                default:
                    $value = '';
            }

            $sheet->setCellValue($col . $row, $value);
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
        $row++;
    }

    for ($i = 1; $i <= count($columns); $i++) {
        $colLetter = Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }

    $directory = "./Excel/";
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $fileName = "csaPayslip_OFXtemplate_" . date('F') . "_" . date('dmY_His') . ".csv";
    $filePath = $directory . $fileName;

    $writer = new Csv($spreadsheet);
    // Optional CSV settings:
    $writer->setDelimiter(',');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);

    $writer->save($filePath);


    header("Location: $filePath");
    exit();
}

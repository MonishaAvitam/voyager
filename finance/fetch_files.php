<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php';

$employee_id = $_GET['employee_id'] ?? '';
$payslip_month = $_GET['month'] ?? '';

if (empty($employee_id) || empty($payslip_month)) {
    echo "<p class='text-danger text-center'>Invalid parameters.</p>";
    exit;
}

$recordsDir = './Records';
$safeEmployeeId = preg_replace('/[^A-Za-z0-9 _\-]/', '', $employee_id);
$files = glob("$recordsDir/*.pdf");
$matchingFiles = [];

foreach ($files as $filePath) {
    $fileName = basename($filePath);

if (!preg_match('/_' . preg_quote($safeEmployeeId, '/') . '\.pdf$/', $fileName)) {
        continue;
    }

preg_match('/_(\d{2})(\d{2})(\d{4})_/', $fileName, $matches);
    if (count($matches) === 4) {
        [$full, $day, $month, $year] = $matches;
        $fileDate = DateTime::createFromFormat('d-m-Y', "$day-$month-$year");

        if ($fileDate) {
            $formattedMonth = $fileDate->format('M Y');

            if (strpos($payslip_month, 'TO') !== false) {
                [$start, $end] = explode('TO', $payslip_month);
                $endDate = DateTime::createFromFormat('d/m/y', trim($end));
                if ($endDate) {
                    $payslipMonthFormatted = $endDate->format('M Y');

                    if ($formattedMonth === $payslipMonthFormatted) {
                        $matchingFiles[] = [
                            'name' => $fileName,
                            'path' => './Records/' . $fileName
                        ];
                    }
                }
            }
        }
    }
}

if (count($matchingFiles) > 0) {
    echo '<div class="row">';
    foreach ($matchingFiles as $file) {
        $fileName = htmlspecialchars($file['name']);
        $filePath = htmlspecialchars($file['path']);

        echo '<div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">';
        echo '  <div class="card h-100">';
        echo '    <div class="card-body d-flex flex-column justify-content-between align-items-center">';
        echo "      <p class='text-center small mb-3' style='word-break: break-word;' title='$fileName'>$fileName</p>";
        echo '      <div class="d-flex justify-content-center gap-2">';
        echo "        <a href='$filePath' target='_blank' class='btn btn-outline-primary btn-sm' title='View'>";
        echo "          <i class='fas fa-eye'></i>";
        echo '        </a>';
        echo "        <a href='$filePath' download class='btn btn-outline-success btn-sm' title='Download'>";
        echo "          <i class='fas fa-download'></i>";
        echo '        </a>';
        echo '      </div>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo "<p class='text-center'>No files found for this record.</p>";
}


?>
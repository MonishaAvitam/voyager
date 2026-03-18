<?php
// add_payslip_jobs.php
require '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['selected_employees']) && is_array($_POST['selected_employees'])) {
        $selectedEmployees = $_POST['selected_employees'];
        $insertedCount = 0;
        $errors = [];
        $result = $conn->query("SELECT MAX(CAST(SUBSTRING(Batch_no, 7) AS UNSIGNED)) AS max_batch FROM batch_email");
        $row = $result->fetch_assoc();
        $nextBatch = $row['max_batch'] ? $row['max_batch'] + 1 : 1;
        $batchNo = 'BATCH_' . str_pad($nextBatch, 2, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO payslip_queue (employee_id, status, created_at, updated_at, Batch_no) VALUES (?, 'pending', NOW(), NOW(), ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        foreach ($selectedEmployees as $employeeId) {
            $employeeId = (int) $employeeId;

            // Check if a pending/processing job exists
            $checkSql = "SELECT id FROM payslip_queue WHERE employee_id = $employeeId";
            $checkResult = $conn->query($checkSql);
            $conn->query("UPDATE batch_email SET Batch_no = '$batchNo' WHERE employee_id = $employeeId AND Batch_no IS NULL");

            // Always insert a new job
            $stmt->bind_param('is', $employeeId, $batchNo);
            if ($stmt->execute()) {
                $insertedCount++;
            } else {
                $errors[] = "Failed to insert job for Employee ID $employeeId: " . $stmt->error;
            }

        }

        $stmt->close();

        if ($insertedCount > 0) {
            // Redirect with job count parameter in URL, e.g. payslip.php?mail=added&count=5
            header("Location: ../finance/payslip.php?mail=added&count=" . $insertedCount);
            exit;
        } else {
            // No jobs added, output errors or message
            echo "No jobs added or reset.";
            if (!empty($errors)) {
                echo "\nErrors:\n";
                foreach ($errors as $error) {
                    echo "- " . htmlspecialchars($error) . "\n";
                }
            }
        }
    } else {
        echo "No employees selected. Please select at least one employee.";
    }
} else {
    echo "Submit the form to add jobs to the queue.";
}
?>
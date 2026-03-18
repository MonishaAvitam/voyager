<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // Admin authentication check 
require '../conn.php'; // MySQLi Database Connection

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$user_role = $_SESSION['user_role'];

$workingHours = 9.24; // Assuming working hours per day

// Fetch all employees
$allUsers = "SELECT e.*, a.user_id FROM csa_finance_employee_info e 
             LEFT JOIN tbl_admin a ON a.user_id = e.tbl_admin_id";
$result1 = $conn->query($allUsers);

while ($row = $result1->fetch_assoc()) {
    $employee_id = $row['user_id'];

    // Reset leave counts for each employee
    $tcld = 0;
    $tclh = 0;
    $tsld = 0;
    $tslh = 0;

    // Fetch leave records
    $sql = "SELECT * FROM leave_approval WHERE employee_id = ? AND leave_from BETWEEN '2025-01-01' AND '2026-12-31'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($leave = $result->fetch_assoc()) {
            $leaveType = $leave['leave_type'];
            $fromDate = $leave['leave_from'];
            $toDate = $leave['leave_to'];
            $status = $leave['approved'];
            $hours = $leave['hours'];

            $startDate = new DateTime($fromDate);
            $endDate = new DateTime($toDate);

            $totalLeaveDays = 0;

            while ($startDate <= $endDate) {
                if ($startDate->format('N') < 6) { // Monday to Friday
                    $totalLeaveDays++;
                }
                $startDate->modify('+1 day');
            }

            $totalLeaveHours = $hours / 9.24;

            if ($status == 'Approved') {
                if ($leaveType == 'Casual Leave') {
                    $tcld += $totalLeaveDays;
                    $tclh += $totalLeaveHours;
                } elseif ($leaveType == 'Sick Leave') {
                    $tsld += $totalLeaveDays;
                    $tslh += $totalLeaveHours;
                }
            }
        }
    }

    // Close statement for leave query
    $stmt->close();


    // Output Sick Leave Hours and Casual Leave Hours
    echo "Sick Leave days: $tslh <br>";
    echo "Casual Leave days: $tclh <br><br>";

    // Prepare Update Query
    $updateQuery = "UPDATE csa_finance_employee_info SET SL = ?, CL = ? WHERE tbl_admin_id = ?";
    $stmt = $conn->prepare($updateQuery);

    if ($stmt) {
        $stmt->bind_param("ddi", $tslh, $tclh, $employee_id);

        if ($stmt->execute()) {
            // Get Employee_id from csa_finance_employee_info
            $empRes = $conn->query("SELECT Employee_id FROM csa_finance_employee_info WHERE tbl_admin_id = $employee_id");
            if ($empRow = $empRes->fetch_assoc()) {
                $empId = $empRow['Employee_id'];

                // Update payslip based on Employee_id
                $conn->query("UPDATE csa_finance_payslip p
            JOIN csa_finance_employee_info e ON p.employee_id = e.Employee_id
            SET p.SL = e.SL, p.CL = e.CL, p.PL = e.PL
            WHERE e.Employee_id = $empId");

                // Update batch_email only if Batch_no is empty or NULL
                $conn->query("UPDATE batch_email
            SET SL = (SELECT SL FROM csa_finance_employee_info WHERE Employee_id = $empId),
                CL = (SELECT CL FROM csa_finance_employee_info WHERE Employee_id = $empId),
                PL = (SELECT PL FROM csa_finance_employee_info WHERE Employee_id = $empId)
            WHERE employee_id = $empId
              AND (Batch_no IS NULL OR Batch_no = '')");
            }

            echo "Leave balances updated successfully for Employee ID: $employee_id.<br>";
        } else {
            echo "Error updating leave balances for Employee ID: $employee_id - " . $stmt->error . "<br>";
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error . "<br>";
    }
}


header('Location:payslip.php?success=true');


// Close database connection
$conn->close();

?>
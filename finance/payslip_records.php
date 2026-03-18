<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';

if (isset($_GET['delete_project_id'])) {
    $delete_project_id = $_GET['delete_project_id'];

    // SQL query to delete the project
    $sql = "DELETE FROM csa_finance_invoiced WHERE project_id = $delete_project_id";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Project Sent back to dashboard Successfully";
        header('Location:' . $_SERVER['HTTP_REFERER']);
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the Project: ' . $conn->error . '";
    }
}
?>




<!-- dashboard content  -->

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Accounts Receivable</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Records</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Period</th>
                            <th>Name</th>
                            <th>Bank Name</th>
                            <th>Account No</th>
                            <th>Salary</th>
                            <th>Other Expense</th>
                            <th>Mail Sent Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Employee ID</th>
                            <th>Period</th>
                            <th>Name</th>
                            <th>Bank Name</th>
                            <th>Account No</th>
                            <th>Salary</th>
                            <th>Other Expense</th>
                            <th>Mail Sent Date</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>

                        <?php
                        $sql = "SELECT * FROM csa_finance_payslip_records";
                        $info = $obj_admin->manage_all_info($sql);
                        $serial  = 1;
                        $num_row = $info->rowCount();
                        if ($num_row == 0) {
                            echo '<tr><td colspan="7">No Payslip were found</td></tr>';
                        }
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {


                        ?>

                            <tr>

                                <td><?php echo  $row['employee_id']  ?>
                                </td>
                                <td><?php

                                    echo  $row['payslip_month']
                                    ?>
                                </td>
                                <td><?php echo $row['fullName']  ?></td>
                                <td><?php echo $row['bank_name']  ?></td>
                                <td><?php echo  "xxxxx" . substr($row['account_no'], -4) ?></td>
                                <td><?php echo $row['salary']; ?></td>
                                <td>
                                    <?php echo $row['expenses']; ?>
                                </td>
                                <td>
                                    <?php echo date('Y-m-d',strtotime($row['record_date'])); ?>
                                </td>
                                <td>
                                <a class="btn btn-primary" href="payslip_records_print.php?employee_id=<?php echo $row['employee_id'] ?> " target="_blank">View</a>
                                <a class="btn btn-primary" href="send_mail.php?employee_id=<?php echo $row['employee_id']; ?>&payslip_id=<?php echo $row['payslip_id']; ?>">Send Mail</a>
                                </td>

                            </tr>

                        <?php } ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>



<?php
include './include/footer.php';
?>
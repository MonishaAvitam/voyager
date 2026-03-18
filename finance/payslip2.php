<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

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


if (isset($_GET['error']) && $_GET['error'] == 'missing_employee') {
    echo "<script>
        Swal.fire({
            title: 'Email Not Sent',
            text: 'Enter 0 in Expense For Selected Employee',
            icon: 'error',
            confirmButtonText: 'Ok',
            allowOutsideClick: false, // Prevent closing by clicking outside
            allowEscapeKey: false,    // Prevent closing with ESC key
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payslip2.php after pressing OK
                window.location.href = 'payslip2.php';
            }
        });
    </script>";
}
if (isset($_GET['success']) && $_GET['success'] == 'emails_sent') {
    echo "<script>
        Swal.fire({
            title: 'Email Sent Successfully',
            text: '',
            icon: 'success',
            confirmButtonText: 'Ok',
            allowOutsideClick: false, // Prevent closing by clicking outside
            allowEscapeKey: false,    // Prevent closing with ESC key
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payslip2.php after pressing OK
                window.location.href = 'payslip2.php';
            }
        });
    </script>";
}
// check admin
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';

?>




<script>
    function updateUrl(Employee_id) {
        // Parse the current URL's query parameters
        const urlParams = new URLSearchParams(window.location.search);

        // Set the 'progress_id' parameter with the specified Employee_id
        urlParams.set('Employee_id', Employee_id);

        // Get the updated query string
        const updatedQueryString = urlParams.toString();

        // Construct the new URL with the updated query string
        const newUrl = window.location.pathname + '?' + updatedQueryString;

        // Use pushState to update the URL without reloading the page
        window.history.pushState({
            Employee_id: Employee_id
        }, '', newUrl);
    }
</script>

<!-- dashboard content  -->

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payslip</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">PaySlip </h6>
            <div class="d-flex justify-align-center align-items-center ">

                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cleardata">Clear Data</button>&nbsp;
                <button type="button" class="btn btn-success  float-right " onclick="submitForm('generateExcel.php')">Generate Excel</button>
            </div>
        </div>

        <script>
            // Function to handle form submission
            function submitForm(action) {
                // Set the action attribute of the form based on the button clicked
                document.getElementById('myForm').action = action;

                // Submit the form
                document.getElementById('myForm').submit();
            }
        </script>

        <div class="card-body ">
            <div class="table-responsive ">
                <form id="myForm" method="POST">
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
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            echo  $currentDate = date("Y-m-d");  // Get the current date in the format matching your database

                            $sql = "SELECT ei.*, COALESCE(p.expenses, 'Enter Expenses if not Set 0') AS expenses 
                                    FROM csa_finance_employee_info AS ei
                                    LEFT JOIN csa_finance_payslip p ON ei.employee_id = p.employee_id
                                ";

                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No Payslip were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <tr>
                                    <td><?php echo  $row['Employee_id']  ?> <input class="form-control" type="checkbox" name="selected_employees[]" value="<?php echo $row['Employee_id'] ?>">
                                    </td>
                                    <td><?php
                                        $currentDate = new DateTime();

                                        $lastMonth = $currentDate->sub(new DateInterval('P1M'));
                                        $lastMonth->setDate($lastMonth->format('Y'), $lastMonth->format('m'), 15);

                                        $presentMonth = new DateTime();  // Create a new DateTime object for the current date
                                        $presentMonth->setDate($presentMonth->format('Y'), $presentMonth->format('m'), 14);


                                        echo $lastMonth->format('d/m/y') . '  TO  ' . $presentMonth->format('d/m/y');
                                        ?>
                                    </td>
                                    <td><?php echo $row['Name']  ?></td>
                                    <td hidden> <input type="email" name="email_id" value="<?php echo $row['email_id']  ?>" </td>
                                    <td><?php echo $row['bank_name']  ?></td>
                                    <td><?php echo  "xxxxx" . substr($row['account_no'], -4) ?></td>
                                    <td><?php echo $row['salary']; ?></td>
                                    <td>
                                        <div data-toggle="modal" data-target=".bd-progress-modal-sm" href="javascript:void(0);" onclick="updateUrl(<?php echo $row['Employee_id']; ?>)">
                                            <p>
                                                <?php echo $row['expenses']; ?>
                                            </p>
                                        </div>
                                    </td>

                                    <td>
                                        <a class="btn btn-primary" href="print.php?employee_id=<?php echo $row['Employee_id'] ?>">View</a>
                                        <!-- <a class="btn btn-primary" href="send_mail.php?employee_id=<?php echo $row['Employee_id'] ?>">Send Mail</a> -->
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <script>
                        function selectAllCheckboxes() {
                            // Get all checkboxes in the table
                            var checkboxes = document.querySelectorAll('#dataTable input[type="checkbox"]');

                            // Toggle the "checked" property for each checkbox
                            checkboxes.forEach(function(checkbox) {
                                checkbox.checked = !checkbox.checked;
                            });
                        }
                    </script><br>
                    <button type="button" class="btn btn-primary mt-2 align-self-end" onclick="selectAllCheckboxes()">Select All</button>
                    <button type="submit" class="btn btn-primary mt-2 float-right mr-2" id="openAlert" onclick="submitForm('bulk_email.php')">Send Mail to Selected Employees</button><br><br>
                </form>

            </div>
        </div>
    </div>

</div>
<script>
    document.getElementById('openAlert').addEventListener('click', function() {
        // Show the loading alert
        Swal.fire({
            title: 'Sending Mail...',
            text: 'Please wait until the task is completed.',
            allowOutsideClick: false, // Prevent closing by clicking outside
            didOpen: () => {
                Swal.showLoading();
            },
            showCancelButton: false,
            showConfirmButton: true,
            confirmButtonText: 'OK',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.close(); // Close the loading alert when "OK" is clicked
            }
        });
    });
</script>

<!-- Expense  -->

<div class="modal fade bd-progress-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label" for="progress">Set Expenses </label>
                    <input class="form-control" value="0" type="number" name="expenses_data" required>
                    <button class="btn btn-primary mt-2" name="expenses_data_input">SET</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Clear DATA -->

<div class="modal fade " id="cleardata" tabindex="-1" role="dialog" aria-labelledby="cleardata" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <h5>Are you want to delete last month payslip expenses ?</h5>
                    <button class="btn btn-primary mt-2" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary mt-2" name="delete_expenses">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php





if (isset($_POST['delete_expenses'])) {
    $sql = "DELETE FROM csa_finance_payslip";
    $info = $obj_admin->manage_all_info($sql);
    $stmt = $conn->prepare($sql);
    if ($stmt->execute()) {
        $msg_success = "Deleted All Expenses";
        header('location:' . $_SERVER['HTTP_REFERER']);
    } else {
        $msg_error = "Error: " . $conn->error;
    }
}


if (isset($_POST["expenses_data_input"]) && isset($_GET["Employee_id"])) {
    // Get the progress value from the form
    $expenses_data = $_POST['expenses_data'];

    // Get the project ID from the URL parameter or form field
    $employee_id = $_GET["Employee_id"]; // Assuming it's in the URL

    $sql = "SELECT * FROM csa_finance_employee_info WHERE employee_id = $employee_id";  // Add a semicolon here
    $info = $obj_admin->manage_all_info($sql);

    $serial  = 1;

    $num_row = $info->rowCount();
    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

        $fetch_Name =  $row['Name'];
        $fetch_email =  $row['email_id'];
        $fetch_salary =  $row['salary'];
        $fetch_bank_name =  $row['bank_name'];
        $fetch_account_no =  $row['account_no'];
        $fetch_doj =  $row['doj'];
        $fetch_designation =  $row['designation'];
        $fetch_department =  $row['department'];
        $total_pay = $fetch_salary + $expenses_data;

        $currentDate = new DateTime();

        $lastMonth = $currentDate->sub(new DateInterval('P1M'));
        $lastMonth->setDate($lastMonth->format('Y'), $lastMonth->format('m'), 15);

        $presentMonth = new DateTime();  // Create a new DateTime object for the current date
        $presentMonth->setDate($presentMonth->format('Y'), $presentMonth->format('m'), 14);

        $payslip_month = $lastMonth->format('d/m/y') . '  TO  ' . $presentMonth->format('d/m/y');

        // echo $lastMonth->format('d/m/y') . '  TO  ' . $presentMonth->format('d/m/y');


    }

    // You can add additional validation and sanitation here

    $sql = "INSERT INTO csa_finance_payslip (
    expenses, employee_id, fullName, payslip_month, email_id, salary,
    bank_name, account_no, total_pay, designation, department, doj, record_date
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IFNULL(record_date, CURRENT_DATE))
ON DUPLICATE KEY UPDATE
    expenses = VALUES(expenses),
    fullName = VALUES(fullName),
    payslip_month = VALUES(payslip_month),
    email_id = VALUES(email_id),
    salary = VALUES(salary),
    bank_name = VALUES(bank_name),
    account_no = VALUES(account_no),
    total_pay = VALUES(total_pay),
    designation = VALUES(designation),
    department = VALUES(department),
    doj = VALUES(doj),
    record_date = IFNULL(VALUES(record_date), CURRENT_DATE);

            ";





    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssississs", $expenses_data, $employee_id, $fetch_Name, $payslip_month, $fetch_email, $fetch_salary, $fetch_bank_name, $fetch_account_no, $total_pay, $fetch_designation, $fetch_department, $fetch_doj);

    if ($stmt->execute()) {
        $msg_success = "Status Updated";
        header('location:' . $_SERVER['HTTP_REFERER']);
    } else {
        $msg_error = "Error: " . $conn->error;
    }

    // Close the statement and connection

}





include './include/footer.php';
?>
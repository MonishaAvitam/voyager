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


// check admin
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';


?>


<!-- dashboard content  -->

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employees Information</h1>

    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Employee </h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_employee">Add
                Employee</button>


        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>DOJ</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email ID</th>
                            <th>Bank Name</th>
                            <th>Account No</th>
                            <th>Salary</th>
                            <th>PAN ID</th> <!-- New PAN ID column -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Employee ID</th>
                            <th>DOJ</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email ID</th>
                            <th>Bank Name</th>
                            <th>Account No</th>
                            <th>Salary</th>
                            <th>PAN ID</th> <!-- New PAN ID column -->
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        // Fetch data from the database
                        $sql = "SELECT * FROM csa_finance_employee_info";
                        $info = $obj_admin->manage_all_info($sql);
                        $num_row = $info->rowCount();

                        if ($num_row == 0) {
                            echo '<tr><td colspan="10">No Employee were found</td></tr>';
                        }

                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?php echo $row['Payslip_Emp_Id']; ?></td>
                                <td><?php echo $row['doj']; ?></td>
                                <td><?php echo $row['Name']; ?></td>
                                <td><?php echo $row['contact_number']; ?></td>
                                <td><?php echo $row['email_id']; ?></td>
                                <td><?php echo $row['bank_name']; ?></td>
                                <td><?php echo "xxxxxxxx" . substr($row['account_no'], -4); ?></td>
                                <td><?php echo $row['salary']; ?></td>
                                <td><?php echo $row['pan_id']; ?></td> <!-- New PAN ID data -->
                                <td style="text-align: center;">
                                    <a href="edit_employee.php?employee_id=<?php echo $row['Employee_id']; ?>"
                                        class="btn btn-primary btn-sm">
                                        View
                                    </a>
                                </td>

                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<!-- ADD CONTACT FORM -->

<div class="modal fade" id="add_employee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">

                            <!-- Personal Details Column -->
                            <div class="col-md-4">
                                <h5>Personal Details</h5>
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <select class="form-control" id="name" name="dropdown" required>
                                        <option value="">-- Select RAE Employee --</option>
                                        <?php
                                        $sql = "SELECT * FROM tbl_admin 
                      WHERE user_id NOT IN (
                          SELECT tbl_admin_id FROM csa_finance_employee_info
                      ) 
                      ORDER BY fullname";
                                        $info = $obj_admin->manage_all_info($sql);
                                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                            if (strpos($row['fullname'], 'User_Removed') !== false)
                                                continue;
                                            ?>
                                            <option value="<?php echo $row['user_id'] . '|' . $row['fullname'] ?>">
                                                <?php echo $row['fullname'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number</label>
                                    <input type="number" class="form-control" id="phoneNumber" name="phoneNumber"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="email">Email ID</label>
                                    <input type="email" class="form-control" id="email" name="email" >
                                </div>

                                <div class="form-group">
                                    <label for="team">Team</label>
                                    <select class="form-control" id="team" name="team" >
                                        <option value="" disabled selected>Select the Team</option>
                                        <option value="RBT">Robotics and Software Team</option>
                                        <option value="IND">Industries Team</option>
                                        <option value="BLD">Building Team</option>
                                        <option value="MNG">Management</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="doj">Date of Joining</label>
                                    <input type="date" class="form-control" id="doj" name="doj" >
                                </div>

                                <div class="form-group">
                                    <label for="salary">Salary</label>
                                    <input type="number" class="form-control" id="salary" name="salary" >
                                </div>

                                <div class="form-group">
                                    <label for="Designation">Designation</label>
                                    <input type="text" class="form-control" id="Designation" name="Designation"
                                        value="Engineer" >
                                </div>

                                <div class="form-group">
                                    <label for="Department">Department</label>
                                    <input type="text" class="form-control" id="Department" name="Department"
                                        value="Engineering" >
                                </div>
                            </div>
                            <!-- Address Details Column -->
                            <div class="col-md-4">
                                <h5>Address Details</h5>
                                <div class="form-group">
                                    <label for="address_line1">Address Fisrt Line</label>
                                    <input type="text" class="form-control" id="address_line1" name="address_line1"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="address_city">Address City</label>
                                    <input type="text" class="form-control" id="address_city" name="address_city"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="address_state">Address State</label>
                                    <input type="text" class="form-control" id="address_state" name="address_state"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="address_postcode">Address Post Code</label>
                                    <input type="text" class="form-control" id="address_postcode"
                                        name="address_postcode" >
                                </div>
                            </div>


                            <!-- Bank Details Column -->
                            <div class="col-md-4">
                                <h5>Bank Details</h5>
                                <div class="form-group">
                                    <label for="account_number">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="account_type">Account Type</label>
                                    <input type="text" class="form-control" id="account_type_display" value="SWIFT"
                                        disabled>
                                    <input type="hidden" id="account_type" name="account_type" value="SWIFT" >
                                </div>


                                <div class="form-group">
                                    <label for="ifsc_code">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_name">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_address_line1">Bank Address First Line</label>
                                    <input type="text" class="form-control" id="bank_address_line1"
                                        name="bank_address_line1" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_city">Bank City</label>
                                    <input type="text" class="form-control" id="bank_city" name="bank_city" >
                                </div>
                                <div class="form-group">
                                    <label for="bank_city">Bank Post Code</label>
                                    <input type="text" class="form-control" id="bank_post_code" name="bank_post_code" >
                                </div>
                                <div class="form-group">
                                    <label for="pan_id">PAN Number</label>
                                    <input type="text" class="form-control" id="pan_id" name="pan_id" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_country_code">Bank Country Code</label>
                                    <!-- Display as read-only -->
                                    <input type="text" class="form-control" id="bank_country_code_display" value="IN"
                                        disabled>
                                    <!-- Hidden actual input for submission -->
                                    <input type="hidden" name="bank_country_code" value="IN">
                                </div>

                            </div>



                        </div> <!-- /row -->
                    </div> <!-- /container-fluid -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_employee">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>





<?php

// delete contact

if (isset($_GET['delete_employee_id'])) {
    $delete_employee_id = $_GET['delete_employee_id'];

    // SQL query to delete the project
    $sql = "DELETE FROM csa_finance_employee_info WHERE employee_id = $delete_employee_id";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Employee Deleted Successfully";
        header('location:employees.php');
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the Employee: ' . $conn->error . '";
    }
}


if (isset($_POST['add_employee'])) {
    // Extract form data
    $dropdown = $_POST['dropdown'];
    list($user_id, $fullname) = explode('|', $dropdown);

    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $salary = $_POST['salary'];
    $bank_name = $_POST['bank_name'];
    $account_no = $_POST['account_number'];
    $account_type = $_POST['account_type'];
    $ifsc_code = $_POST['ifsc_code'];
    $bank_address_line1 = $_POST['bank_address_line1'];
    $bank_city = $_POST['bank_city'];
    $bank_country_code = $_POST['bank_country_code'];
    $bank_post_code = $_POST['bank_post_code'];

    $doj = $_POST['doj'];
    $Designation = $_POST['Designation'];
    $Department = $_POST['Department'];
    $pan_id = $_POST['pan_id'];
    $team = $_POST['team'];

    $address_line1 = $_POST['address_line1'];
    $address_city = $_POST['address_city'];
    $address_state = $_POST['address_state'];
    $address_postcode = $_POST['address_postcode'];

    // Construct Payslip Employee ID
    $payslip_emp_id = $team . '' . $user_id;

    // Check for duplicate tbl_admin_id
    $check_sql = "SELECT COUNT(*) FROM csa_finance_employee_info WHERE tbl_admin_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($exists);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($exists > 0) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Employee with ID $payslip_emp_id already exists.',
                icon: 'error',
                confirmButtonText: 'Ok',
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        </script>";
    } else {
        // INSERT statement with all new fields
        $sql = "INSERT INTO csa_finance_employee_info 
            (Name, email_id, contact_number, salary, doj, designation, department, pan_id, tbl_admin_id, Payslip_Emp_Id,
             address_line1, address_city, address_state, address_postcode,
             bank_name, account_no, account_type, ifsc_code, bank_address_line1, bank_city,bank_country_code,bank_post_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "sssissssssssssssssssss",
                $fullname,
                $email,
                $phoneNumber,
                $salary,
                $doj,
                $Designation,
                $Department,
                $pan_id,
                $user_id,
                $payslip_emp_id,
                $address_line1,
                $address_city,
                $address_state,
                $address_postcode,
                $bank_name,
                $account_no,
                $account_type,
                $ifsc_code,
                $bank_address_line1,
                $bank_city,
                $bank_country_code,
                $bank_post_code
            );

            if ($stmt->execute()) {
                header('Location: employees.php?success=employee_added');
                exit;
            } else {
                echo "Error executing statement: " . $stmt->error;
            }
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}


?>





<?php
include './include/footer.php';
?>
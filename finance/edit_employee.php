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

$employee_id = $_GET['employee_id'];

$sql = "SELECT * FROM csa_finance_employee_info WHERE employee_id = $employee_id";
$info = $obj_admin->manage_all_info($sql);

$row = $info->fetch(PDO::FETCH_ASSOC); // Fetch the row only once

$tbl_admin_id = $row['tbl_admin_id']; // Fetch tbl_admin_id for later use

?>

<script>
    $(document).ready(function () {
        $('#edit_contact').modal('show');
    });
</script>

<div class="modal fade" id="edit_contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content text-primary">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Employee Details</h5>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">

                            <!-- Personal Details -->
                            <div class="col-md-4">
                                <h5>Personal Details</h5>
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo $row['Name']; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number</label>
                                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber"
                                        value="<?php echo $row['contact_number']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="email">Email ID</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo $row['email_id']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="team">Team</label>
                                    <select class="form-control" id="team" name="team" >
                                        <option value="RBT" <?php echo (strpos($row['Payslip_Emp_Id'], 'RBT') === 0) ? 'selected' : ''; ?>>Robotics and Software Team</option>
                                        <option value="IND" <?php echo (strpos($row['Payslip_Emp_Id'], 'IND') === 0) ? 'selected' : ''; ?>>Industries Team</option>
                                        <option value="BLD" <?php echo (strpos($row['Payslip_Emp_Id'], 'BLD') === 0) ? 'selected' : ''; ?>>Building Team</option>
                                        <option value="MNG" <?php echo (strpos($row['Payslip_Emp_Id'], 'MNG') === 0) ? 'selected' : ''; ?>>Management</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="doj">Date of Joining</label>
                                    <input type="date" class="form-control" id="doj" name="doj"
                                        value="<?php echo $row['doj'] ? date('Y-m-d', strtotime($row['doj'])) : ''; ?>"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="salary">Salary</label>
                                    <input type="number" class="form-control" id="salary" name="salary"
                                        value="<?php echo $row['salary']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="Designation">Designation</label>
                                    <input type="text" class="form-control" id="Designation" name="Designation"
                                        value="<?php echo $row['designation']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="Department">Department</label>
                                    <input type="text" class="form-control" id="Department" name="Department"
                                        value="<?php echo $row['department']; ?>" >
                                </div>
                            </div>
                            <!-- Address Details -->
                            <div class="col-md-4">
                                <h5>Address Details</h5>
                                <div class="form-group">
                                    <label for="address_line1">Address First Line</label>
                                    <input type="text" class="form-control" id="address_line1" name="address_line1"
                                        value="<?php echo $row['address_line1']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="address_city">City</label>
                                    <input type="text" class="form-control" id="address_city" name="address_city"
                                        value="<?php echo $row['address_city']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="address_state">State</label>
                                    <input type="text" class="form-control" id="address_state" name="address_state"
                                        value="<?php echo $row['address_state']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="address_postcode">Post Code</label>
                                    <input type="text" class="form-control" id="address_postcode"
                                        name="address_postcode" value="<?php echo $row['address_postcode']; ?>"
                                        >
                                </div>
                            </div>
                            <!-- Bank Details -->
                            <div class="col-md-4">
                                <h5>Bank Details</h5>
                                <div class="form-group">
                                    <label for="account_number">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number"
                                        value="<?php echo $row['account_no']; ?>" >
                                </div>

                                <div class="form-group">
                                    <!-- <label for="account_type">Account Type</label> -->
                                    <input type="text" hidden class="form-control" id="account_type_display" value="SWIFT"
                                        disabled>
                                    <input type="hidden" name="account_type" value="SWIFT">
                                </div>


                                <div class="form-group">
                                    <label for="ifsc_code">IFSC Code</label>
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code"
                                        value="<?php echo $row['ifsc_code']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_name">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name"
                                        value="<?php echo $row['bank_name']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_address_line1">Bank Address First Line</label>
                                    <input type="text" class="form-control" id="bank_address_line1"
                                        name="bank_address_line1" value="<?php echo $row['bank_address_line1']; ?>"
                                        >
                                </div>

                                <div class="form-group">
                                    <label for="bank_city">Bank City</label>
                                    <input type="text" class="form-control" id="bank_city" name="bank_city"
                                        value="<?php echo $row['bank_city']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_post_code">Bank Post Code</label>
                                    <input type="text" class="form-control" id="bank_post_code" name="bank_post_code"
                                        value="<?php echo $row['bank_post_code']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="pan_id">PAN Number</label>
                                    <input type="text" class="form-control" id="pan_id" name="pan_id"
                                        value="<?php echo $row['pan_id']; ?>" >
                                </div>

                                <div class="form-group">
                                    <label for="bank_country_code">Bank Country Code</label>
                                    <!-- Disabled input (visible but not editable) -->
                                    <input type="text" class="form-control" id="bank_country_code_display" value="IN"
                                        disabled>
                                    <!-- Hidden input (actually submitted in form) -->
                                    <input type="hidden" name="bank_country_code" value="IN">
                                </div>

                            </div>



                        </div> <!-- /row -->
                    </div> <!-- /container-fluid -->
                </div>

                <div class="modal-footer">
                    <div class="me-auto">
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                    </div>

                    <script>
                        function confirmDelete() {
                            if (confirm('Are you sure you want to delete this employee?')) {
                                const form = document.createElement('form');
                                form.method = 'POST';

                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'delete_employee';
                                input.value = '1';

                                form.appendChild(input);
                                document.body.appendChild(form);
                                form.submit();
                            }
                        }
                    </script>

                    <a type="button" class="btn btn-secondary" href="employees.php">Close</a>
                    <button type="submit" class="btn btn-primary" name="update_employee"
                        onclick="return confirmUpdate()">Update</button>
                    <script>
                        function confirmUpdate() {
                            return confirm('Are you sure you want to update this employee?');
                        }
                    </script>
                </div>

            </form>
        </div>
    </div>
</div>


<?php

if (isset($_POST['delete_employee'])) {
    $employee_id = $_GET['employee_id'];

    $delete_sql = "DELETE FROM csa_finance_employee_info WHERE employee_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $employee_id);

    if ($delete_stmt->execute()) {
        header('Location: employees.php?success=employee_deleted');
        exit();
    } else {
        echo "Error deleting employee: " . $delete_stmt->error;
    }
}



// EDIT CONTACTS
// EDIT CONTACTS
if (isset($_POST['update_employee'])) {
    $employee_id = $_GET['employee_id'];  // The employee ID from the URL

    // Fetch tbl_admin_id for the employee (required to regenerate Payslip_Emp_Id)
    $fetch_sql = "SELECT tbl_admin_id FROM csa_finance_employee_info WHERE employee_id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $employee_id);
    $fetch_stmt->execute();
    $fetch_stmt->bind_result($tbl_admin_id);
    $fetch_stmt->fetch();
    $fetch_stmt->close();

    // Extract updated form data
    $fullname = $_POST['name'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $salary = $_POST['salary'];
    $bank_name = $_POST['bank_name'];
    $account_no = $_POST['account_number'];
    $account_type = $_POST['account_type'];
    $ifsc_code = $_POST['ifsc_code'];
    $bank_address_line1 = $_POST['bank_address_line1'];
    $bank_city = $_POST['bank_city'];
    $doj = $_POST['doj'];
    $Designation = $_POST['Designation'];
    $Department = $_POST['Department'];
    $pan_id = $_POST['pan_id'];
    $team = $_POST['team'];
    $address_line1 = $_POST['address_line1'];
    $address_city = $_POST['address_city'];
    $address_state = $_POST['address_state'];
    $address_postcode = $_POST['address_postcode'];
    $bank_country_code = $_POST['bank_country_code'];
    $bank_post_code = $_POST['bank_post_code'];

    // Regenerate Payslip_Emp_Id
    $payslip_emp_id = $team . $tbl_admin_id;

    // Update SQL with all fields
    $sql = "UPDATE csa_finance_employee_info 
            SET 
                Name = ?, 
                email_id = ?, 
                bank_name = ?, 
                account_no = ?, 
                salary = ?, 
                doj = ?, 
                contact_number = ?, 
                department = ?, 
                designation = ?, 
                pan_id = ?, 
                Payslip_Emp_Id = ?, 
                address_line1 = ?, 
                address_city = ?, 
                address_state = ?, 
                address_postcode = ?, 
                account_type = ?, 
                ifsc_code = ?, 
                bank_address_line1 = ?, 
                bank_city = ?,
                bank_country_code=?,
                bank_post_code=?
            WHERE employee_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "ssssissssssssssssssssi",
            $fullname,
            $email,
            $bank_name,
            $account_no,
            $salary,
            $doj,
            $phoneNumber,
            $Department,
            $Designation,
            $pan_id,
            $payslip_emp_id,
            $address_line1,
            $address_city,
            $address_state,
            $address_postcode,
            $account_type,
            $ifsc_code,
            $bank_address_line1,
            $bank_city,
            $bank_country_code,
            $bank_post_code,
            $employee_id
        );

        if ($stmt->execute()) {
            header('Location: employees.php?success=employee_updated');
            exit();
        } else {
            echo "Error executing statement: " . $stmt->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

include("include/footer.php");
?>
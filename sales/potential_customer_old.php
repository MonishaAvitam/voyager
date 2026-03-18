<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include 'include/sidebar.php';
include 'include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];


?>


<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Potential Customer </h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
   
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Potential Customer</h6>
                <!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_contact">Add Contact</button>-->
            </div>

            <div class="card-body">
                <div class="table-responsive p-3  ">
                    <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Enquiry Id</th>
                                <th>Customer Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>S.No</th>
                                <th>Enquiry Id</th>
                                <th>Customer Details</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <!-- ... Your table rows ... -->

                            <?php
                            $sql = "SELECT * FROM enquiry_sales 
                            WHERE potential_customer IS NOT NULL 
                            AND potential_customer != '' 
                            AND (potential_customer_added IS NULL OR potential_customer_added = '')";

                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No enquiries were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <tr>
                                    <td><?php echo $serial; ?></td>
                                    <td><?php echo $row['id'];  ?></td>
                                    <td><?php echo $row['potential_customer'];  ?></td>
                                    <td>
                                        &nbsp;
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add_contact" data-id="<?php echo $row['id']; ?>">Add Contact</button>

                                        &nbsp;
                                        <a title="View" href="viewenquiry.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View Enquiry
                                            </svg></a>&nbsp;&nbsp;
                                        &nbsp;
                                        <a title="Delete" href="?delete_contact_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this Customer ?');" class="btn btn-sm btn-danger">Delete Enquiry
                                            </svg></a>&nbsp;&nbsp;
                                        &nbsp;
                                    </td>
                                </tr>
                            <?php
                                $serial++; // Increment the serial counter here
                            }
                            ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#add_contact').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var enquiryId = button.data('id'); // Extract the ID from the data-id attribute
            var modal = $(this);
            modal.find('#enquiryId').val(enquiryId); // Set the ID in the modal's hidden input field
        });
    });
</script>


<!-- ADD CONTACT FORM -->

<div class="modal fade" id="add_contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Customer Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">

                    <input type="hidden" name="enquiryId" id="enquiryId" value="">

                    <div class="form-group">
                        <label for="customer_id">Customer Id</label>
                        <input type="text" class="form-control" id="customer_id" name="customer_id">
                    </div>
                    <div class="form-group">
                        <label for="customer_id">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name">
                    </div>
                    <div class="form-group">
                        <label for="contactName">Contact Name</label>
                        <input type="text" class="form-control" id="contactName" name="contactName">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber">
                    </div>
                    <div class="form-group">
                        <label for="email">Email ID</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea type="text" class="form-control" id="address" name="address"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_contact">Add Contact</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<?php
include '../conn.php';

// delete contact

if (isset($_GET['delete_contact_id'])) {
    $delete_contact_id = $_GET['delete_contact_id'];

    // SQL query to delete the project
    $sql = "DELETE FROM enquiry_sales WHERE id = $delete_contact_id";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Enquiry Deleted Successfully";
        header('location:potential_customer.php');
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the contact: ' . $conn->error . '";
    }
}


// ADD NEW CONTACTS
if (isset($_POST['add_contact'])) {
    $enquiryId = $_POST['enquiryId'];
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $contactName = $_POST['contactName'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $registration_date = date('y-m-d');

    // Prepare and execute the SQL INSERT statement
    $sql = "INSERT INTO contacts (customer_id, customer_name, contact_name, contact_phone_number, contact_email, address, registration_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssssss", $customer_id, $customer_name, $contactName, $phoneNumber, $email, $address, $registration_date);
        $stmt->execute(); // Execute the prepared statement

        // Check if the insert was successful
        if ($stmt->affected_rows > 0) {
            // Get the last inserted contact_id
            $contact_id = $stmt->insert_id;

            // Prepare and execute the SQL UPDATE statement to update the enquiry_sales table
            $updateSql = "UPDATE enquiry_sales 
                          SET potential_customer_added = 'Added', contact_id = ? 
                          WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);

            if ($updateStmt) {
                $updateStmt->bind_param("ii", $contact_id, $enquiryId);
                $updateStmt->execute();
                $updateStmt->close(); // Close the update statement
            }

            // Redirect to a success page after successful insertion and update
            header('Location: sales_dashboard.php');
        } else {
            echo "Error: Could not insert contact.";
        }

        $stmt->close(); // Close the insert statement
    } else {
        echo "Error: " . $conn->error;
    }
}





?>


<?php

include 'include/footer.php';

?>
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
    header('Location: index.php');
}


// check admin
$user_role = $_SESSION['user_role'];




?>

<?php include './include/sidebar.php'; ?>



<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Vendors</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_vendor">Add Vendors</button>
            </div>

            <div class="card-body">
                <div class="table-responsive p-3">
                    <table class="table shadow-lg table-bordered table-sm" id="dataTable" name="dataTable" style="border: 1px solid #dddddd70">
                        <thead style="height:4.5rem;">
                            <tr class="bg-primary text-light my-2 text-center">
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Company Name</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Contact Person</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Email iD</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Phone Number</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Address</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">State</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Comments</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Actions</th>
                            </tr>
                        </thead>

                        <tbody style="background: #dddddd70;">
                            <!-- ... Your table rows ... -->

                            <?php
                            $sql = "SELECT * from vendors ";

                            $info = $obj_admin->manage_all_info($sql);

                            $serial  = 1;

                            $num_row = $info->rowCount();

                            if ($num_row == 0) {

                                echo '<tr><td colspan="7">No vendors were found</td></tr>';
                            }

                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                            ?>


                                <script>
                                    function updateUrl(id) {
                                        // Parse the current URL's query parameters
                                        const urlParams = new URLSearchParams(window.location.search);

                                        urlParams.set('id', id);

                                        // Get the updated query string
                                        const updatedQueryString = urlParams.toString();

                                        // Construct the new URL with the updated query string
                                        const newUrl = window.location.pathname + '?' + updatedQueryString;

                                        // Use pushState to update the URL without reloading the page
                                        window.history.pushState({
                                            id: id
                                        }, '', newUrl);

                                        // Reload the page
                                        // window.location.reload();
                                    }
                                </script>


                                <tr>
                                    <td class="align-middle"><?php echo $row['company_name'] ?></td>
                                    <td class="align-middle"><?php echo $row['contact_person'] ?></td>
                                    <td class="align-middle"><?php echo $row['email_id'] ?></td>
                                    <td class="align-middle"><?php echo $row['phone'] ?></td>
                                    <td class="align-middle"><?php echo $row['address'] ?></td>
                                    <td class="align-middle"><?php echo $row['state'] ?></td>
                                    <td class="align-middle"><?php echo $row['comments'] ?></td>
                                    <td class="align-middle d-flex justify-content-center">
                                        <div>
                                            <button
                                                type="button"
                                                class="btn-sm btn text-info bg-light"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                onclick="populateModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                Edit
                                            </button>
                                            &nbsp;&nbsp;
                                            <a
                                                title="Delete"
                                                href="?delete_id=<?php echo $row['id']; ?>"
                                                onclick="return confirm('Are you sure you want to delete this contact ?');"
                                                class="btn-sm btn bg-light text-danger">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" action="update_vendor.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="vendorId" name="id">
                    <div class="mb-3">
                        <label for="companyName" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="companyName" name="company_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactPerson" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contactPerson" name="contact_person" required>
                    </div>
                    <div class="mb-3">
                        <label for="emailId" class="form-label">Email</label>
                        <input type="email" class="form-control" id="emailId" name="email_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input class="form-control" id="state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function populateModal(vendor) {
    document.getElementById('vendorId').value = vendor.id;
    document.getElementById('companyName').value = vendor.company_name;
    document.getElementById('contactPerson').value = vendor.contact_person;
    document.getElementById('emailId').value = vendor.email_id;
    document.getElementById('phone').value = vendor.phone;  
    document.getElementById('address').value = vendor.address;
    document.getElementById('state').value = vendor.state;
    document.getElementById('comments').value = vendor.comments;
}

</script>




<div class="modal fade" id="add_vendor" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Vendor Information </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name">
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="email">Email ID</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input class="form-control" id="state" name="state">
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                        <textarea class="form-control" id="comments" name="comments"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_vendor">Add Vendor</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>




<?php
include '../conn.php';

// delete contact

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // SQL query to delete the project
    $sql = "DELETE FROM vendors WHERE id = $delete_id";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Vendor Deleted Successfully";
        header('location:vendors.php');
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the vendor: ' . $conn->error . '";
    }
}


if (isset($_POST['add_vendor'])) {
    // Retrieve and sanitize input
    $company_name = $_POST['company_name'];
    $contact_person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $comments = $_POST['comments'];

    // Prepare and execute the SQL INSERT statement
    $sql = "INSERT INTO vendors (company_name, contact_person, phone, email_id, address,state, comments) VALUES (?, ?,?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssssss", $company_name, $contact_person, $phone, $email, $address,$state ,$comments);
        if ($stmt->execute()) {
            // Success message
            $msg_success = "Vendor Added Successfully";
            header('Location: vendors.php'); // Redirect after successful insertion
            exit(); // Ensure no further processing
        } else {
            // Error during execution
            $msg_error = "Error in adding the vendor: " . $stmt->error;
        }
    } else {
        // Error during preparation
        $msg_error = "Error in preparing the statement: " . $conn->error;
    }
}



?>










<?php

include './include/footer.php';

?>
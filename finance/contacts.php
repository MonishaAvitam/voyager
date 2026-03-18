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
        <h1 class="h3 mb-0 text-gray-800">Contacts</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
  
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Contact Book</h6>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_contact">Add Contact</button>
            </div>

            <div class="card-body">
      <div class="table-responsive p-3">
        <table class="table shadow-lg table-bordered table-sm" id="dataTable" name="dataTable" style="border: 1px solid #dddddd70">
          <thead style="height:4.5rem;">
            <tr class="bg-primary text-light my-2 text-center">
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Company Name</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Company Code</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Contact Person</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Email iD</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Phone Number</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Address</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Comments</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Actions</th>
                            </tr>
                        </thead>
                    
                        <tbody style="background: #dddddd70;">
                            <!-- ... Your table rows ... -->

                            <?php
                            $sql = "SELECT c.* from contacts c ";
                            if ($user_role == 1) {

                                // Admin can see all projects

                                $sql .= " ORDER BY c.contact_id DESC";
                            }

                            $info = $obj_admin->manage_all_info($sql);

                            $serial  = 1;

                            $num_row = $info->rowCount();

                            if ($num_row == 0) {

                                echo '<tr><td colspan="7">No projects were found</td></tr>';
                            }

                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                            ?>


                                <script>
                                    function updateUrl(contact_id) {
                                        // Parse the current URL's query parameters
                                        const urlParams = new URLSearchParams(window.location.search);

                                        // Set the 'contact_id' parameter with the specified contact_id
                                        urlParams.set('contact_id', contact_id);

                                        // Get the updated query string
                                        const updatedQueryString = urlParams.toString();

                                        // Construct the new URL with the updated query string
                                        const newUrl = window.location.pathname + '?' + updatedQueryString;

                                        // Use pushState to update the URL without reloading the page
                                        window.history.pushState({
                                            contact_id: contact_id
                                        }, '', newUrl);

                                        // Reload the page
                                        // window.location.reload();
                                    }
                                </script>


                                <tr>
                                    <td class="align-middle"><?php echo $row['customer_name']  ?></td>
                                    <td class="align-middle"><?php echo $row['customer_id']  ?></td>
                                    <td class="align-middle"><?php echo $row['contact_name']  ?></td>
                                    <td class="align-middle"><?php echo $row['contact_email']  ?></td>
                                    <td class="align-middle"><?php echo $row['contact_phone_number']  ?></td>
                                    <td class="align-middle"><?php echo $row['address']  ?></td>
                                    <td class="align-middle"><?php echo $row['comments']  ?></td>
                                    <td class="align-middle d-flex justify-content-center">


                                     <div>
                                     <a type="button" href="edit_contacts.php?contact_id=<?php echo $row['contact_id']; ?>" class="btn-sm btn text-info bg-light">Edit</a>&nbsp;&nbsp;

                                       


                                        <a title="Delete" href="?delete_contact_id=<?php echo $row['contact_id']; ?>" onclick="return confirm('Are you sure you want to delete this contact ?');" class="btn-sm btn bg-light text-danger"> Delete</a>
                                </div>
                                        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#edit_contact">Edit Contact</button> -->






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





<!-- ADD CONTACT FORM -->

<div class="modal fade" id="add_contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Customer Information  </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="customer_id">Company Code</label>
                        <input type="text" class="form-control" id="customer_id" name="customer_id">
                    </div>
                    <div class="form-group">
                        <label for="customer_id">Company Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name">
                    </div>
                    <div class="form-group">
                        <label for="contactName">Contact Person</label>
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
                    <div class="form-group">
                        <label for="address">Comments</label>
                        <textarea type="text" class="form-control" id="comments" name="comments"></textarea>
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
    $sql = "DELETE FROM contacts WHERE contact_id = $delete_contact_id";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Contact Deleted Successfully";
        header('location:contacts.php');
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the contact: ' . $conn->error . '";
    }
}


// ADD NEW CONTACTS
if (isset($_POST['add_contact'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $contactName = $_POST['contactName'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $comments = $_POST['comments'];
    $registration_date = date('y-m-d');

    // Prepare and execute the SQL INSERT statement
    $sql = "INSERT INTO contacts (customer_id,customer_name, contact_name, contact_phone_number, contact_email,address,comments, registration_date) VALUES (?, ?, ?, ?, ?,?,?,?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssssss", $customer_id, $customer_name, $contactName, $phoneNumber, $email, $address,$comments, $registration_date);
        $stmt->execute(); // Execute the prepared statement

        $msg_success = "Contact Added Successfully";
        header('Location: contacts.php');
    } else {
        echo "Error: " . $conn->error;
        $msg_error = "Error in adding the contact: ' . $conn->error . '";
    }
}



?>










<?php

include './include/footer.php';

?>
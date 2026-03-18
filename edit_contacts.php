<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require './authentication.php'; // admin authentication check 
require './conn.php';
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


$contact_id = $_GET['contact_id'];

$edit_contact_id = $_GET['contact_id'] ?? 0;

?>

    <?php include 'include/sidebar.php'; ?>



    <script>
        // Wait for the document to be ready
        $(document).ready(function() {
            // Show the modal when the page is loaded
            $('#edit_contact').modal('show');
        });
    </script>

    <div class="modal fade" id="edit_contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content text-primary">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Information</h5>

                </div>
                <div class="modal-body">

                <?php
                    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";

                    $info = $obj_admin->manage_all_info($sql);
                    $serial  = 1;
                    $num_row = $info->rowCount();
                    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                    ?>
            
                    <form method="POST">

                        <div class="form-group" hidden>
                            <label for="customer_id">contact_id</label>
                            <input type="text" class="form-control" id="contact_id" name="contact_id" value="<?php echo $row['contact_id'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_id">Customer Id</label>
                            <input type="text" class="form-control" id="customer_id" name="customer_id" value="<?php echo $row['customer_id'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_id">Customer Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo $row['customer_name'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="contactName">Contact Name</label>
                            <input type="text" class="form-control" id="contactName" name="contactName" value="<?php echo $row['contact_name'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email ID</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $row['contact_email'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo $row['contact_phone_number'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea type="text" class="form-control" id="address" name="address"><?php echo $row['address'] ?></textarea>
                        </div>
                        <div class="modal-footer">
                            <a type="button" class="btn btn-secondary" onclick="javascript:history.back()">Close</a>
                            <button type="submit" class="btn btn-primary" name="edit_contact">Update Contact</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>





<?php

}

// EDIT CONTACTS
if (isset($_POST['edit_contact'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $contactName = $_POST['contactName'];
    $phoneNumber = $_POST['phoneNumber'];
    $contact_email = $_POST['contact_email'];
    $contact_id = $_POST['contact_id'];
    $address = $_POST['address'];

    // Prepare and execute the SQL UPDATE statement
    $sql = "UPDATE contacts SET customer_id = ?,customer_name = ?, contact_name = ?, contact_phone_number = ?, contact_email = ?, address = ? WHERE contact_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssssi", $customer_id, $customer_name, $contactName, $phoneNumber, $contact_email, $address, $contact_id);
        $stmt->execute();
        $stmt->close();

        // Redirect or display a success message as needed
        header('Location: contacts.php'); // Redirect to a success page
    } else {
        echo "Error: " . $conn->error;
    }
}


include("include/footer.php");


?>
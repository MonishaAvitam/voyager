<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include 'include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit();
}

// check admin
$user_role = $_SESSION['user_role'];

$contact_id = $_GET['contact_id'] ?? 0; // Ensuring contact_id has a default value if not set

// Prepare the SQL statement using MySQLi
$sql = "SELECT * FROM contacts WHERE contact_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $contact_id); // 'i' denotes that contact_id is an integer
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

if ($row) {
    // Safely access the array keys
    $contact_id = $row['contact_id'] ?? '';
    $customer_id = $row['customer_id'] ?? '';
    $customer_name = $row['customer_name'] ?? '';
    $contact_name = $row['contact_name'] ?? '';
    $contact_email = $row['contact_email'] ?? '';
    $contact_phone_number = $row['contact_phone_number'] ?? '';
    $address = $row['address'] ?? '';
    $comments = $row['comments'] ?? '';

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
                    <form method="POST">
                        <div class="form-group" hidden>
                            <label for="customer_id">Company Code</label>
                            <input type="text" class="form-control" id="contact_id" name="contact_id" value="<?php echo htmlspecialchars($contact_id); ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_id">Company Code</label>
                            <input type="text" class="form-control" id="customer_id" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_id">Company Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>">
                        </div>
                        <div class="form-group">
                            <label for="contactName">Contact Person</label>
                            <input type="text" class="form-control" id="contactName" name="contactName" value="<?php echo htmlspecialchars($contact_name); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email ID</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($contact_phone_number); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea type="text" class="form-control" id="address" name="address"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="address">Comments</label>
                            <textarea type="text" class="form-control" id="comments" name="comments"><?php echo htmlspecialchars($comments); ?></textarea>
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
} else {
    echo "No contact found with ID: $contact_id";
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
    $comments = $_POST['comments'];

    // Prepare and execute the SQL UPDATE statement
    $sql = "UPDATE contacts SET customer_id = ?,customer_name = ?, contact_name = ?, contact_phone_number = ?, contact_email = ?, address = ?,comments = ? WHERE contact_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssssssi", $customer_id, $customer_name, $contactName, $phoneNumber, $contact_email, $address,$comments, $contact_id);
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
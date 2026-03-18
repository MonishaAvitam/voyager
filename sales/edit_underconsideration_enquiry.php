
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
}


// check admin
$user_role = $_SESSION['user_role'];


    $id = $_GET['id'];

    $edit_enquiry_id = $_GET['id'] ?? 0;

    $sql = "SELECT * FROM enquiry_under_consideration WHERE id = $id";  // Add a semicolon here
    $info = $obj_admin->manage_all_info($sql);

    $serial  = 1;

    $num_row = $info->rowCount();
    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
   

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
                    <h5 class="modal-title" id="exampleModalLabel">Update Enquiry</h5>
                   
                </div>
                <div class="modal-body">
                    <form method="POST">

                        <div class="form-group" hidden>
                            <label for="id">id</label>
                            <input type="text" class="form-control" id="id" name="id" value="<?php echo $row['id'] ?>" >
                        </div>
                        <div class="form-group">
    <label for="customer_id">Customer Id</label>
    <input type="text" class="form-control" id="customer_id" name="customer_id" value="<?php echo isset($row['customer_id']) ? $row['customer_id'] : ''; ?>">
</div>
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo $row['customer_name'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_contact">Customer Contact</label>
                            <input type="text" class="form-control" id="customer_contact" name="customer_contact" value="<?php echo $row['customer_contact'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_contact">Customer Email-Id</label>
                            <input type="text" class="form-control" id="customer_emailid" name="customer_emailid" value="<?php echo $row['customer_emailid'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_contact">Customer Address</label>
                            <input type="text" class="form-control" id="customer_address" name="customer_address" value="<?php echo $row['customer_address'] ?>">
                        </div>
                        <div class="form-group">
    <label for="enquiry_details">Enquiry Description</label>
    <textarea rows="5" cols="5" class="form-control" id="enquiry_details" name="enquiry_details"><?php echo $row['enquiry_details'] ?></textarea>
</div>
                        <div class="modal-footer">
                            <a type="button" class="btn btn-secondary" href="underconsideration.php">Close</a>
                            <button type="submit" class="btn btn-primary" name="edit_enquiry">Update Enquiry</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>


    


    <?php

}

      // EDIT CONTACTS
      if (isset($_POST['edit_enquiry'])) {
        $id = $_POST['id'];
        $customer_name = $_POST['customer_name'];
        $customer_contact = $_POST['customer_contact'];
        $customer_emailid = $_POST['customer_emailid'];
        $customer_address = $_POST['customer_address'];
        $customer_id = $_POST['customer_id'];
        $enquiry_details = $_POST['enquiry_details'];
        $lastUpdate = date('Y-m-d H:i:s');
        
        // Prepare and execute the SQL UPDATE statement
        $sql = "UPDATE enquiry_under_consideration SET customer_name = ?, customer_contact = ?, customer_emailid = ?, customer_address = ?, customer_id = ?, enquiry_details = ?, last_updated=? WHERE id = ?";

        // Prepare and execute the SQL UPDATE statement
$sql = "UPDATE enquiry_under_consideration SET customer_name = ?, customer_contact = ?, customer_emailid = ?, customer_address = ?, enquiry_details = ?, last_updated = ?";
$bind_types = "ssssss"; // Bind types for parameters

if ($customer_id !== '') {
    $sql .= ", customer_id = ?";
    $bind_types .= "i"; // Append bind type for customer_id
}

$sql .= " WHERE id = ?";
$bind_types .= "i"; // Append bind type for id

$stmt = $conn->prepare($sql);

if ($stmt) {
    if ($customer_id !== '') {
        // Bind parameters based on dynamically constructed bind types
        $stmt->bind_param($bind_types, $customer_name, $customer_contact, $customer_emailid, $customer_address, $enquiry_details, $lastUpdate, $customer_id, $id);
    } else {
        $stmt->bind_param($bind_types, $customer_name, $customer_contact, $customer_emailid, $customer_address, $enquiry_details, $lastUpdate, $id);
    }
    $stmt->execute();
    $stmt->close();

    // Redirect or display a success message as needed
    header('Location: underconsideration.php'); // Redirect to a success page
} else {
    echo "Error: " . $conn->error;
}

    }


include("include/footer.php");


?>
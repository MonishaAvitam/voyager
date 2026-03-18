<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';



// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];
include './include/login_header.php';
include './include/sidebar.php';



if (isset($_POST['send_to_rae'])) {
    $enquiryId = $_GET['enquiry_id'];
    $team = $_POST['team'];
    $status = 100;


    $moveToRae = "INSERT INTO csa_sales_converted_projects (id, customer_id , potential_customer, enquiry_details, files, date, time,last_updated,team) 
                        SELECT id, contact_id, potential_customer, enquiry_name, folderId, date, time,last_updated,? FROM enquiry_sales WHERE id=? 
                        ";

    $stmtMove = $conn->prepare($moveToRae);

    if ($stmtMove) {
        $stmtMove->bind_param("ss", $team, $enquiryId); 
        $_SESSION['status_success'] = "Enquiry Moved To RAE Successfully";

        if (!$stmtMove->execute()) {
            echo "Error moving record: " . $stmtMove->error;
            // Handle error if moving record fails
        }

        // Update the enquiry_status in enquiry_sales
        $sql = "UPDATE enquiry_sales SET enquiry_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $status, $enquiryId);
        $stmt->execute();
        $stmt->close();

      

        // Close the statement
        $stmtMove->close();

        header('location: sales_dashboard.php');

    }
}


?>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST">

                <div class="modal-body">
                    <select name="team" class="form-control" name="" id="" required>
                        <option value="">Select Team</option>
                        <option value="Building">Building Team</option>
                        <option value="Industrial">Industrial Team</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <a href="sales_dashboard.php" class="btn btn-secondary" >Close</a>
                    <button type="submit" name="send_to_rae" class="btn btn-primary">Send to RAE</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    // Automatically open the modal when the page loads
    $(document).ready(function() {
        $('#exampleModal').modal({
            backdrop: 'static', // Prevents closing the modal when clicking outside
            keyboard: false // Prevents closing the modal with the Esc key
        });

        $('#exampleModal').modal('show');
    });
</script>


<?php include './include/footer.php' ?>
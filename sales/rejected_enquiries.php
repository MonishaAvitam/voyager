<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

require __DIR__ . '/../vendor/autoload.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: ../index.php');
}

// check admin
$user_role = $_SESSION['user_role'];
include 'enquiry.php';

include './include/sidebar.php';


if (isset($_GET['delete_user'])) {
  $action_id = $_GET['id'];

  $sql = "DELETE FROM enquiry_sales WHERE id = :id";
  $sent_po = "sales_dashboard.php";
  $obj_admin->delete_data_by_this_method($sql, $action_id, $sent_po);
  $_SESSION['status_success'] = "Enquiry Deleted Successfully";
}

?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800"> &nbsp;Rejected Enquiries</h1>
  <div class="d-flex align-items-center">
  </div>
</div>


<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="container px-1 px-md-4 py-5 mx-auto">
                <table class="table">
                    <tbody>
                        <?php
                        include '../conn.php';
                        // Fetch data from the database
                        $sql = "SELECT es.*, c.customer_id, c.customer_name FROM enquiry_sales es LEFT JOIN contacts c ON es.contact_id = c.contact_id WHERE enquiry_status = 'Rejected'";
                        $result = $conn->query($sql);

                        // Check if there are rows in the result set
                        if ($result->num_rows > 0) {
                            $sno = 1; // Initialize serial number
                            // Loop through each row in the result set
                            while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td>
                                <div class="card mb-3 custom-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title">Enquiry Id <span class="text-primary font-weight-bold"><?php echo 'S' . $row['id']; ?></span></h5>
                                            <p class="mb-0">Customer Name: <span><?php echo $row['customer_name']; ?></span></p>
                                        </div>
                                        <div class="d-flex flex-column text-sm-right">
                                            <p class="mb-0">Enquiry Date: <span class="font-weight-bold"><?php echo $row['date']; ?></span></p>
                                            <p class="mb-0">Last Updated: <span class="font-weight-bold"><?php echo $row['last_updated']; ?></span></p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='2'>No enquiries found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


    <?php include './include/footer.php'; ?>
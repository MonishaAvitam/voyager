<?php

use Google\Service\ShoppingContent\Resource\Pos;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


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

if (isset($_POST['send_to_rae'])) {
  $enquiryId = $_POST['id'];
  $team = $_POST['team'];
  $status = "Sent to RAE";

  $moveToRae = "INSERT INTO csa_sales_converted_projects (id, customer_id , enquiry_details, files, audio_file, date, time,last_updated,team,enquiry_status) 
                        SELECT id, contact_id, enquiry_details, files, audio_file, date, time,last_updated,?,? FROM enquiry_sales WHERE id=? 
                        ";
  $deleteFromSalesSql = "DELETE FROM enquiry_sales WHERE id = ?";

  $stmtMove = $conn->prepare($moveToRae);
  $stmtdrop = $conn->prepare($deleteFromSalesSql);

  if ($stmtMove) {
    $stmtMove->bind_param("sss", $team, $status, $enquiryId);
    $stmtdrop->bind_param("s", $enquiryId);
    $_SESSION['status_success'] = "Enquiry Moved To RAE Successfully";

    if (!$stmtMove->execute()) {
      echo "Error moving record: " . $stmtMove->error;
      // Handle error if moving record fails
    }

    // Close the statement
    if (!$stmtdrop->execute()) {
      echo "Error moving record: " . $stmtdrop->error;
      // Handle error if moving record fails
    }

    // Close the statement
    $stmtMove->close();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
  $id = $_POST['id'];
  $enquiry_name = $_POST['enquiry_name'];
  $enquiry_status = "Accepted";
  $lastUpdate = date('Y-m-d H:i:s'); // Get current timestamp
  $priority = $_POST['priority'];

  // Check the connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Update database
  $sql = "UPDATE enquiry_sales SET  enquiry_name = ?, last_updated = ?,enquiry_status=?, priority=? WHERE id = ?";

  $stmt = $conn->prepare($sql);

  // Bind parameters
  $stmt->bind_param("ssssi",  $enquiry_name, $lastUpdate, $enquiry_status, $priority, $id);

  // Execute the statement
  if ($stmt->execute()) {
    $_SESSION['status_success'] = "Enquiry Updated Successfully";
    header('Location:' . $_SERVER['HTTP_REFERER']); // Adjusted redirection
    exit(); // Added exit to prevent further execution
  } else {
    // Handle error
    echo "Error: " . $stmt->error;
  }

  // Close the statement and connection
  $stmt->close();
  $conn->close();
}




// Query to get the total number of enquiry
$sql = "SELECT COUNT(*) AS totalenquiry FROM enquiry_sales";
// Execute the query
$results = $conn->query($sql);
// Check if the query was successful
if ($results) {
  // Fetch the result as an associative array
  $row = $results->fetch_assoc();
  $totalenquiry = $row['totalenquiry'];
  // Free the result set
  $results->free();
} else {
  // Handle the error if the query fails
  echo "Error: " . $conn->error;
}


// Query to get the total number of under consideration enquiry
$sql = "SELECT COUNT(*) AS totalunderconsideration FROM enquiry_sales WHERE enquiry_status = 'Under Consideration'";
// Execute the query
$results = $conn->query($sql);
// Check if the query was successful
if ($results) {
  // Fetch the result as an associative array
  $row = $results->fetch_assoc();
  $totalunderconsideration = $row['totalunderconsideration'];
  // Free the result set
  $results->free();
} else {
  // Handle the error if the query fails
  echo "Error: " . $conn->error;
}

// Query to get the total number of enquiry completed
$sql = "SELECT COUNT(*) AS totalcompleted FROM enquiry_sales WHERE enquiry_status = 'Rejected'";
// Execute the query
$results = $conn->query($sql);
// Check if the query was successful
if ($results) {
  // Fetch the result as an associative array
  $row = $results->fetch_assoc();
  $totalcompleted = $row['totalcompleted'];
  // Free the result set
  $results->free();
} else {
  // Handle the error if the query fails
  echo "Error: " . $conn->error;
}


?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800"> &nbsp;High Priority</h1>
  <div class="d-flex align-items-center">
  </div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

 

  <link rel="stylesheet" href="css/custom_card.css">

<div class="container">

    <div class="row">
        <div class="col-md-12">
                        <div class="container px-1 px-md-4 py-5 mx-auto" >
                                        
                                                <tr>
                                                    <td>
                                                    <?php
                                        include '../conn.php';
                                        // Fetch data from the database
                                        $sql = "SELECT es.*, c.customer_id, c.customer_name FROM enquiry_sales es LEFT JOIN contacts c ON es.contact_id = c.contact_id WHERE enquiry_status != 'Rejected'";
                                        $result = $conn->query($sql);

                                        // Check if there are rows in the result set
                                        if ($result->num_rows > 0) {
                                            $sno = 1; // Initialize serial number
                                            // Loop through each row in the result set
                                            while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <?php
// Determine the border class based on priority
$priority = $row['priority'];
$borderClass = '';

switch ($priority) {
    case 'High':
        $borderClass = 'border-high';
        break;
    case 'Medium':
        $borderClass = 'border-medium';
        break;
    case 'Low':
        $borderClass = 'border-low';
        break;
    default:
        $borderClass = ''; // Default or fallback class if needed
}
?>
 <?php if ($row['priority'] == 'High'): ?>
<div class="card mb-3 custom-card <?php echo $borderClass; ?>">
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

    <!-- Progress Bar -->
    <div class="card-body">
        <div class="row d-flex justify-content-center">
            <div class="col-12">
                <ul id="progressbar" class="text-center">
                    <li class="active step0"><p>Enquiry Registered</p></li>
                    <li class="active step0"><p>Priority Set</p></li>
                    <li class="active step0"><p>Quotation Given</p></li>
                    <li class="step0"><p>Converted to RAE Project</p></li>
                </ul>
            </div>
        </div>
        <div class="text-center">
    <?php if ($row['enquiry_status'] != 'Accepted'): ?>
        <button type="button" class="btn btn-primary"
        data-toggle="modal"
        data-target="#acceptModal"
        data-id="<?php echo $row['id']; ?>"
        data-name="<?php echo $row['enquiry_name']; ?>"
        data-date="<?php echo $row['date']; ?>"
        data-last-updated="<?php echo $row['last_updated']; ?>"
        data-priority="<?php echo $row['priority']; ?>">
            Accept
        </button>
    <?php else: ?>
      <button type="button" class="btn btn-secondary" >Quotation</button>
    <?php endif; ?>
    
    <?php if ($row['enquiry_status'] != 'Accepted'): ?>
        <button type="button" class="btn btn-secondary" name="reject_enquiry" data-toggle="modal" data-target="#rejectModal">Reject</button>
    <?php endif; ?>
    
</div>
    </div>
</div>

                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='2'>No enquiries found.</td></tr>";
                                        }
                                        ?>
                            </div>
                        </div>
                    </div>
                </div>



    <div class="modal fade" id="sendToRAE" tabindex="-1" role="dialog" aria-labelledby="sendToRAELabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="sendToRAELabel">Send to RAE</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="POST">
              <select name="team" id="team" class="form-control" required>
                <option value="">Select Team</option>
                <option value="Industrial">Industrial</option>
                <option value="Building">Building</option>
              </select>
              <label for="" class="control-label mt-3">Are you sure you want to convert this enquiry as RAE project?</label>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
            <button type="submit" name="send_to_rae" class="btn btn-danger" id="confirmSendButton">Yes</button>
            <input type="hidden" name="sendProject" value="rae">
            <input type="hidden" name="id" id="userId">
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      $('#sendToRAE').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var Id = button.data('user-id');
        $('#userId').val(Id);
      });
    </script>



    <style>
      /* Define the button style */
      .dashed-button {
        display: inline-block;
        padding: 10px 20px;
        border: 2px dashed blue;
        /* Dashed border with blue color */
        background-color: transparent;
        /* Transparent background */
        color: blue;
        /* Text color */
        text-align: center;
        text-decoration: none;
        font-size: 16px;
        cursor: pointer;
      }

      /* Style the button on hover */
      .dashed-button:hover {
        border: 2px dashed blue;
        /* Dashed border with blue color */

        background-color: rgba(0, 128, 255, 0.1);
        /* Sky blue color with 50% opacity */

        color: blue;
        /* Text color on hover */
        transition: 0.5s;
      }
    </style>

    <?php include './include/footer.php'; ?>
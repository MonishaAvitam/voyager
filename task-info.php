<?php


require 'authentication.php'; // admin authentication check 
require 'conn.php';
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

include 'include/sidebar.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';


//end




$page_name = "Task_Info";
// include('ems_header.php');

// delete project

if (isset($_GET['delete_project_id'])) {
  $delete_project_id = $_GET['delete_project_id'];

  // SQL query to delete the project
  $sql = "DELETE FROM projects WHERE project_id = $delete_project_id";

  if ($conn->query($sql) === TRUE) {
    // Display a success Toastr notification
    $msg_error = "Project Deleted Successfully";
    header('location:index.php');
  } else {
    // Display an error Toastr notification with the PHP error message
    $msg_error = "Error deleting the Project: ' . $conn->error . '";
  }
}
if (isset($_GET['delete_table_id'])) {
  $delete_table_id = $_GET['delete_table_id'];

  // SQL query to delete the project
  $sql = "DELETE FROM subprojects WHERE table_id = $delete_table_id";

  if ($conn->query($sql) === TRUE) {
    // Display a success Toastr notification
    $msg_error = "Sub Project Deleted Successfully";
    header('location: ' . $_SERVER['PHP_SELF']);
  } else {
    // Display an error Toastr notification with the PHP error message
    $msg_error = "Error deleting the Project: ' . $conn->error . '";
  }
}



?>


<?php





// $msg_success = "test";

?>



<!-- dashboard content  -->

<div class="container-fluid">
  <?php
  include 'conn.php'; // Ensure your database connection is included

  $my_projectCount = 0; // Initialize the project count variable
  $projectCount = 0; // Initialize the total project count variable
  $deliverableTabCount = 0; // Initialize the deliverable tab count variable
  $liveProjectsTabCount = 0; // Initialize the live projects tab count variable

  // Assuming you have a variable $loggedInEmployeeID containing the ID of the logged-in employee
  $loggedInEmployeeID = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null; // Adjust this based on your authentication method

  // Check if the user is logged in and their ID is set
  if ($loggedInEmployeeID) {
    // Escape and sanitize the input to prevent SQL injection
    $loggedInEmployeeID = mysqli_real_escape_string($conn, $loggedInEmployeeID);

    // Prepare and execute queries
    $my_projectQuery = "SELECT COUNT(*) AS my_project_count FROM projects WHERE assign_to_id = $loggedInEmployeeID";
    $projectQuery = "SELECT
        (SELECT COUNT(*) FROM projects) +
        (SELECT COUNT(*) FROM deliverable_data) AS total_projects";
    $deliverableTabCountQuery = "SELECT COUNT(*) AS deliverableTabCount FROM deliverable_data WHERE urgency = 'purple'";
    $liveTabCountQuery = "SELECT COUNT(*) AS liveProjectsCount FROM projects WHERE urgency != 'purple'";

    $my_projectResult = mysqli_query($conn, $my_projectQuery);
    $projectResult = mysqli_query($conn, $projectQuery);
    $deliverableTabCountResult = mysqli_query($conn, $deliverableTabCountQuery);
    $liveProjectTabCountResult = mysqli_query($conn, $liveTabCountQuery);

    if ($my_projectResult && $projectResult && $deliverableTabCountResult && $liveProjectTabCountResult) {
      // Fetch the results
      $my_projectRow = mysqli_fetch_assoc($my_projectResult);
      $projectRow = mysqli_fetch_assoc($projectResult);
      $deliverableTabRow = mysqli_fetch_assoc($deliverableTabCountResult);
      $liveProjectTabRow = mysqli_fetch_assoc($liveProjectTabCountResult);

      // Update variables with the counts
      $my_projectCount = $my_projectRow['my_project_count'];
      $projectCount = $projectRow['total_projects'];
      $deliverableTabCount = $deliverableTabRow['deliverableTabCount'];
      $liveProjectsTabCount = $liveProjectTabRow['liveProjectsCount'];
    } else {
      // Handle query execution error
      echo "Query error: " . mysqli_error($conn);
    }
  } else {
    // Handle the case where the user is not logged in or their ID is not set
    echo "User is not logged in or ID is not set.";
  }

  // Close the database connection
  mysqli_close($conn);

  ?>






  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 ">Dashboard</h1>

    <div class="d-flex align-items-center">

    </div>

    <!-- <div >

      <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" style="background-color: #072745; color: #fff;"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
    </div> -->
  </div>


  <script src="./timer.js"></script>



  <!-- Content Row -->
  <div class="row">
    <!-- Number of projects -->
    <?php if ($user_role == 1) { ?>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold  text-uppercase mb-1">
                  All Projects</div>
                <div class="h5 mb-0 font-weight-bold text-gray-300"><?php echo $projectCount; ?></div>
              </div>
              <div class="col-auto">
                <i class="fas fa-calendar fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold  text-uppercase mb-1">
                  Open Projects </div>
                <div class="h5 mb-0 font-weight-bold text-gray-300"><?php echo $liveProjectsTabCount; ?></div>
              </div>
              <div class="col-auto">
                <i class="fas fa-plane fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold  text-uppercase mb-1">
                  Closed Project </div>
                <div class="h5 mb-0 font-weight-bold text-gray-300"><?php echo $deliverableTabCount; ?></div>
              </div>
              <div class="col-auto">
                <i class="fas fa-truck fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>




    <?php } ?>

    <?php if ($user_role == 3) { ?>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold  text-uppercase mb-1" style="color: #072745;">
                  Your Projects</div>
                <div class="h5 mb-0 font-weight-bold text-gray-300"><?php echo $my_projectCount; ?></div>
              </div>
              <div class="col-auto">
                <i class="fas fa-calendar fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div> <?php  } ?>

    <?php if ($user_role == 2) { ?>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold  text-uppercase mb-1" style="color: #072745;">
                  Your Projects</div>
                <div class="h5 mb-0 font-weight-bold text-gray-300"><?php echo $my_projectCount; ?></div>
              </div>
              <div class="col-auto">
                <i class="fas fa-calendar fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>



    <?php } ?>









    <!-- Pending Requests Card Example -->

    <div class="col-xl-3 col-md-6 mb-4">
      <a style="text-decoration: none;" href="time-sheet.php">
        <div class="card  shadow h-100 py-2" style="background-image: url('https://img.freepik.com/free-photo/liquid-marbling-paint-texture-background-fluid-painting-abstract-texture-intensive-color-mix-wallpaper_1258-92022.jpg?w=1380&t=st=1695952054~exp=1695952654~hmac=8bbe4bdb0c7db4fbee508489b16ab87e0d5667e8c5523ca0ab40c7cc0291fdf2'); background-size: cover; background-position: center center;">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <center>
                  <h3 style="color: #fff;">Time Booking</h3>
                </center>
              </div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>


  <div class="container-fluid">
    <style>
      .custom-red {
        background-color: red;
        color: white;
        border-radius: 5px;

        /* Optional, set text color to contrast with the background */
      }

      .custom-orange {
        background-color: orange;
        color: white;
        border-radius: 5px;

      }

      .custom-white {
        background-color: white;
        color: black;
        border-radius: 5px;

      }

      .custom-green {
        background-color: green;
        color: white;
        border-radius: 5px;

      }

      .custom-purple {
        background-color: purple;
        color: white;
        border-radius: 5px;

      }

      a:active {
        text-decoration: none;
      }

      a:hover {
        text-decoration: none;
      }
    </style>


    <?php
    // Initialize upcoming holidays
    $currentDate = date('Y-m-d');
    $upcomingHolidays = array(
      array('1', '2025-01-01', 'Monday', 'New Year Day'),
      array('2', '2025-01-26', 'Friday', 'Australia Day'),
      array('3', '2025-03-25', 'Monday', 'Holi'),
      array('4', '2025-03-29', 'Friday', 'Good Friday'),
      array('5', '2025-04-01', 'Monday', 'Easter Monday'),
      array('6', '2025-04-25', 'Thursday', 'Anzac Day'),
      array('7', '2025-05-06', 'Monday', 'Labour Day'),
      array('8', '2025-06-17', 'Monday', 'Bakrid/Eid al Adha'),
      array('9', '2025-08-15', 'Thursday', 'Independence Day'),
      array('10', '2025-10-07', 'Monday', 'King Birthday'),
      array('11', '2025-11-01', 'Friday', 'Diwali'),
      array('12', '2025-12-24', 'Tuesday', 'Christmas Eve'),
      array('13', '2025-12-25', 'Wednesday', 'Christmas Day'),
      array('14', '2025-12-26', 'Thursday', 'Boxing Day'),
    );

    // Function to send emails using PHPMailer
    function sendEmail($recipient, $subject, $message)
    {



      $mail = new PHPMailer(true); // Passing `true` enables exceptions 

      try {
        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = "engineering@csaengineering.com.au";
        $mail->Password = "kezfduovpirmalcs";
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('revanthshiva@csaengineering.com.au', "CSA Engineering");
        $mail->addAddress($recipient); // Add a recipient

        // Content
        $mail->isHTML(false); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
      } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
    }

    include 'conn.php'; // Assuming this file contains database connection details

    // Check if today is a holiday
    foreach ($upcomingHolidays as $holiday) {
      if ($holiday[1] == $currentDate) {
        // Check if an email has already been sent for this holiday
        $stmt = $conn->prepare('SELECT sent_at FROM sent_emails WHERE holiday_date = ?');
        $stmt->bind_param('s', $currentDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $sentEmail = $result->fetch_assoc();

        if (!$sentEmail) {
          // Send the holiday email
          $stmt2 = $conn->prepare('SELECT email_id, Name FROM csa_finance_employee_info');
          $stmt2->execute();
          $result = $stmt2->get_result();
          $employees = $result->fetch_all(MYSQLI_ASSOC);

          $subject = "Holiday Notice: " . $holiday[3];

          foreach ($employees as $employee) {
            $message = "Dear {$employee['Name']},\n\nToday is " . $holiday[3] . " (" . $holiday[2] . "). Enjoy your holiday!\n\nRegards,\nCSA Engineering";
            sendEmail($employee['email_id'], $subject, $message);

            // Update the sent_emails table to mark the email as sent
            $stmt = $conn->prepare('INSERT INTO sent_emails (holiday_date, holiday_name, sent_at) VALUES (?, ?, NOW())');
            $stmt->bind_param('ss', $currentDate, $holiday[3]);
            $stmt->execute();
          }
        }
      }
    }




    ?>









    <script>
      document.addEventListener("DOMContentLoaded", function() {
        // Get all elements with class "view-list" (View List buttons)
        var btns = document.querySelectorAll('.view-list');

        // Loop through each button and attach a click event listener
        btns.forEach(function(btn) {
          btn.addEventListener('click', function() {
            // Show the modal
            $('#holidayModal').modal('show');
          });
        });
      });
    </script>


    <script>
      $(document).ready(function() {
        $('#HTable').DataTable({
          "searching": false, // Disable search
          "paging": false, // Disable pagination
          "info": false // Disable info text
          // You can disable other options similarly
        });
      });
    </script>




    <!-- dashboard content end -->

    <!-- Begin Page Content -->
    <div class="container-fluid  mt-md-5">

      <?php
      if ($user_role === 1 or $user_role === 3) {
      ?>
        <div class="card shadow mb-4" style="display: none">
          <div class="card-header py-3">
            <div class="row">
              <div class="col-6">
                <h6 class="m-0 font-weight-bold text-primary">Projects needed to be created in RAE </h6>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive p-3  ">
              <table id="senttorae" class="table table-striped table-bordered table-sm" style="width:100%">
                <thead>
                  <tr>
                    <th>Enquiry ID</th>
                    <th>Customer Name</th>
                    <th>Last Updated</th>
                    <th>Team</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tfoot>
                  <tr>
                    <th>Enquiry ID</th>
                    <th>Customer Name</th>
                    <th>Last Updated</th>
                    <th>Team</th>
                    <th>Actions</th>
                  </tr>
                </tfoot>
                <tbody>
                  <!-- ... Your table rows ... -->

                  <?php
                  $sql = "SELECT cp.*, c.* 
        FROM csa_sales_converted_projects cp 
        LEFT JOIN contacts c 
        ON cp.customer_id = c.contact_id 
        WHERE status != 'Projected Created' 
        AND status != 'Hold'";

                  $info = $obj_admin->manage_all_info($sql);
                  $serial  = 1;
                  $num_row = $info->rowCount();
                  if ($num_row == 0) {

                    echo '<tr><td colspan="7">No projects were found</td>
                  <td hidden></td>
                  <td hidden></td>
                  <td hidden></td>
                  <td hidden></td>
                  </tr>';
                  }
                  while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                    <tr>
                      <td>S<?php echo $row['id'];  ?></td>
                      <td> <?php
                            echo !empty($row['customer_name']) ? $row['customer_name'] : $row['potential_customer'];
                            ?></td>
                      <td><?php echo $row['last_updated'];  ?></td>
                      <td><?php echo $row['team'];  ?> </td>
                      <td>
                        &nbsp;
                        <a title="View" class="view-project" href="viewEnquiryDetails.php?id=<?php echo $row['id']; ?>">
                          <i class="fas fa-solid fa-eye" style="color: #7a4edf;"></i>
                          <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                          </svg>
                        </a>
                        &nbsp;
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add_project" data-enquiry-id="<?php echo $row['id']; ?>">Create Project</button>


                        &nbsp;

                      </td>
                    </tr>
                  <?php } ?>

                </tbody>
              </table>
            </div>
          </div>

        </div>


      <?php
      }
      ?>

      <style>
        /* Rounded tabs */

        @media (min-width: 576px) {
          .rounded-nav {
            border-radius: 50rem !important;
          }
        }

        @media (min-width: 576px) {
          .rounded-nav .nav-link {
            border-radius: 50rem !important;
          }
        }
      </style>


      <!-- 714 to 1539 -->











      <!-- <a href="openprojects.php?branch_id=QLD001" class="text-danger font-weight-bold ml-3"> Brisbane, Queensland</a>

<a href="openprojects.php?branch_id=SYD001" class="text-primary font-weight-bold ml-3"> Central Park, Sydney</a>

<a href="openprojects.php?branch_id=QLD002" class="text-danger font-weight-bold ml-3"> Austinville, Queensland </a>

<a href="openprojects.php?branch_id=SYD002" class="text-primary font-weight-bold ml-3"> Mackenzies Bay, Sydney</a>


<a href="openprojects.php?branch_id=" class="text-success font-weight-bold ml-3"> All Branches </a> -->



      <!-- Modal -->
      <div class="modal fade" id="sentToCountModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form class="" action="" method="post" enctype="multipart/form-data">
              <div class="modal-body">
                <p id="modalMessage"></p>
                <p><strong>Project Number:</strong> <span id="modalProjectNumber"></span></p>
                <p><strong>Project Name:</strong> <span id="modalProjectName"></span></p>
              </div>
              <input type="hidden" name="project_id" id="project_id_input">

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="modalYesButton" name="readyToBeInvoiced">Yes, Send</button>
              </div>
          </div>
          </form>
        </div>
      </div>

      <script>
        function showConfirmationModal(event, projectId, projectName, message) {
          // Prevent the button click from triggering the parent <a> tag
          event.stopPropagation();

          // Populate the modal with project information and the appropriate message
          document.getElementById('modalProjectNumber').textContent = projectId;
          document.getElementById('modalProjectName').textContent = projectName;
          document.getElementById('modalMessage').textContent = message;

          // Set the hidden input value
          document.getElementById('project_id_input').value = projectId;

          // Check if the message indicates the project is already invoiced or sent to COUNT
          var yesButton = document.getElementById('modalYesButton');

          // Hide the 'Yes, Send' button if the project is either invoiced or sent to COUNT
          if (message === "Project is Invoiced." || message === "Project is sent to COUNT.") {
            yesButton.style.display = 'none';
          } else {
            yesButton.style.display = 'inline-block';
          }


          // Show the modal
          $('#sentToCountModal').modal('show');
        }
      </script>



      <!-- Goal BOX -->

      <div class="modal fade" id="setGoalModel" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content">
            <div class="form-container mt-2 m-2">
              <form class="" action="" method="post" enctype="multipart/form-data">
                <label class="control-label text-dark" for="">Set Target Date</label>
                <input name="project_id" id="modal-id" hidden></input>
                <input name="project_name" id="modal-name" hidden></input>
                <input name="assign_to" id="modal-assign_to" hidden></input>
                <input class="form-control" type="date" name="goalDate" id="targetdate">
                <button class="btn btn-primary mt-2" name="setGoal">Submit</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <script>
        $('#setGoalModel').on('show.bs.modal', function(event) {
          var button = $(event.relatedTarget); // Button that triggered the modal
          var recipientId = button.data('id'); // Extract info from data-* attributes
          var recipientName = button.data('name'); // Extract info from data-* attributes
          var recipientAssignTo = button.data('assign_to'); // Extract info from data-* attributes

          // Update the modal's content.
          var modal = $(this);
          modal.find('#modal-id').val(recipientId);
          modal.find('#modal-name').val(recipientName);
          modal.find('#modal-assign_to').val(recipientAssignTo);
        });
      </script>

      <!-- Progress Bar -->

      <div class="modal fade bd-progress-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content">
            <div class="form-container mt-2 m-2">
              <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                <label class="control-label" for="progress">Set Progress Status</label>
                <input class="form-control" type="number" name="progress_data_number" max="100" min="0">
                <button class="btn btn-primary mt-2" name="progress_input_data">SET</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade bd-progress-modal-sm_sp" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content">
            <div class="form-container mt-2 m-2">
              <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                <label class="control-label" for="progress">Set Progress Status</label>
                <input class="form-control" type="number" name="progress_data_number" max="100" min="0">
                <button class="btn btn-primary mt-2" name="progress_input_data_sp">SET</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Send To Engineer  -->
      <div class="modal fade" id="assign_to_status_sub" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle">Are you sure?</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="" method="POST">
              <div class="modal-body">
                Send Project to Engineer
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <!-- Use type="submit" to submit the form -->
                <button type="submit" class="btn btn-primary" name="assign_to_status_sub">Send</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="modal fade" id="assign_to_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle">Are you sure?</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="" method="POST">
              <div class="modal-body">
                Send Project to Engineer
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <!-- Use type="submit" to submit the form -->
                <button type="submit" class="btn btn-primary" name="assign_to_status">Send</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- Modal -->

      <div class="modal fade" id="status_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle">Confirm Project Completion</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <form action="process_data-delivery.php?project_id=<?php echo $row['project_id']; ?>" method="POST">
              <div class="modal-body">
                Are you certain you want to mark this project as completed?
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" name="close_project">Confirm</button>
              </div>
            </form>

          </div>
        </div>
      </div>



      <script>
        // When the modal is about to be shown
        $('#status_model').on('show.bs.modal', function(event) {
          // Get the button that triggered the modal
          var button = $(event.relatedTarget);
          // Extract the project_id from the data-project-id attribute
          var projectId = button.data('project-id');
          // Find the form inside the modal
          var modal = $(this);
          // Set the action attribute of the form with the project_id
          modal.find('form').attr('action', 'process_data-delivery.php?project_id=' + projectId);
        });



        function updateUrl_close(projectId) {

          // Parse the current URL's query parameters

          const urlParams = new URLSearchParams(window.location.search);

          // Set the 'progress_id' parameter with the specified projectId

          urlParams.set('project_id', projectId);

          // Get the updated query string

          const updatedQueryString = urlParams.toString();

          // Construct the new URL with the updated query string

          const newUrl = window.location.pathname + '?' + updatedQueryString;

          // Use pushState to update the URL without reloading the page

          window.history.pushState({

            projectId: projectId

          }, '', newUrl);

        }
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

      <script>

      </script>

      <!-- Select file To deliverables -->
      <div class="modal fade" id="filemanager" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle">Send to Deliverables</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="" method="POST">
              <div class="modal-body">

                <h6>Are you certain you want to move this project to the deliverables tab?</h6>
              </div>
              <div class="modal-footer">
                <!-- <label for="">Are You Sure ? </label> -->
                <button href="" class="btn btn-primary " name="send_to_deliverables">Send</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal for Close Confirmation -->
      <div class="modal fade" id="closeSubprojectModal" tabindex="-1" aria-labelledby="closeSubprojectLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="closeSubprojectLabel">Confirm Close Sub-Project</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form action="" method="POST">
              <div class="modal-body">
                <p>Are you sure you want to close this sub-project?</p>
                <input type="hidden" name="table_id" id="table_id">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button href="" name="close_subproject" class="btn btn-danger">Close Sub-Project</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
        $('#closeSubprojectModal').on('show.bs.modal', function(event) {
          var button = $(event.relatedTarget); // Button that triggered the modal
          var tableId = button.data('table-id'); // Extract info from data-* attributes
          var modal = $(this);
          modal.find('.modal-body #table_id').val(tableId);
        });
      </script>




      <!-- SELECT FILES BEFORE DELIVARABLE -->



      <?php
      include 'conn.php';

      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["setGoal"])) {

        $project_id = $_POST['project_id'];
        $project_name = $_POST['project_name'];
        $assign_to = $_POST['assign_to'];

        $setGoaltDate = $_POST['goalDate'];

        $sql = "INSERT INTO rae_goals (project_id, project_name, assign_to, goalDate, user_id) 
        VALUES (?, ?, ?, ?, ?)";

        $stmt1 = $conn->prepare($sql);

        $stmt1->bind_param("isssi", $project_id, $project_name, $assign_to, $setGoaltDate, $user_id);

        $stmt1->execute();

        $stmt1->close();


        header('Location: myGoal.php');
        exit();
      }


      if (isset($_POST["file_manager_deliverables"]) && isset($_GET["file_project_id"])) {
        $project_id = $_GET["file_project_id"]; // Assuming it's in the URL
        header('Location: Gdrive_files.php?file_project_id=' . $project_id);
        exit; // It's a good practice to exit after a header redirect
      }
      if (isset($_POST["send_to_deliverables"]) && isset($_GET["file_project_id"])) {
        $project_id = $_GET["file_project_id"]; // Assuming it's in the URL
        header('Location: process_data-delivery.php?project_id=' . $project_id);
        exit; // It's a good practice to exit after a header redirect
      }


      if (isset($_POST["assign_to_status"]) && isset($_GET["progress_id"])) {
        // Get the project ID from the URL parameter or form field
        $project_id = $_GET["progress_id"]; // Assuming it's in the URL
        $status_value = 1;
        $verify_status = 0;
        $assign_status = 1;
        $project_manager_status = 0;
        $verify_by = NULL;
        $verify_by_name = NULL;

        // You can add additional validation and sanitation here

        // SQL query to update the 'progress' column of the 'projects' table for a specific project
        $sql = "UPDATE projects SET assign_to_status = ? , verify_status = ? , project_manager_status = ? , verify_by = ? ,verify_by_name = ? , assign_status = ? WHERE project_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $status_value, $verify_status, $project_manager_status, $verify_by, $verify_by_name, $assign_status, $project_id);

        if ($stmt->execute()) {
          $msg_success = "Sent To Engineer";
          header('location:index.php');
        } else {
          $msg_error = "Error: " . $conn->error;
        }
      }

      if (isset($_POST["assign_to_status_sub"]) && isset($_GET["table_id"])) {
        // Get the project ID from the URL parameter or form field
        $table_id = $_GET["table_id"]; // Assuming it's in the URL
        $status_value = 1;
        $verify_status = 0;
        $assign_status = 1;
        $project_manager_status = 0;
        $verify_by = NULL;
        $verify_by_name = NULL;

        // You can add additional validation and sanitation here

        // SQL query to update the 'progress' column of the 'projects' table for a specific project
        $sql = "UPDATE subprojects SET assign_to_status = ? , verify_status = ? , project_manager_status = ? , verify_by = ? ,verify_by_name = ? , assign_status = ? WHERE table_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $status_value, $verify_status, $project_manager_status, $verify_by, $verify_by_name, $assign_status, $table_id);

        if ($stmt->execute()) {
          $msg_success = "Sent To Engineer";
          header('location:' . $_SERVER['PHP_SELF']);
        } else {
          $msg_error = "Error: " . $conn->error;
        }
      }

      //change_end_date update 
      if (isset($_POST["change_end_date"]) && isset($_GET["progress_id"])) {
        // Get the progress value from the form
        $change_end_date_value = $_POST['change_end_date_value'];
        $t_end_date = date("Y-m-d", strtotime($change_end_date_value));

        // Get the project ID from the URL parameter or form field
        $project_id = $_GET["progress_id"]; // Assuming it's in the URL

        $sql = "UPDATE projects SET end_date = ? WHERE project_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $t_end_date, $project_id); // Use "si" for a string and an integer

        if ($stmt->execute()) {
          $msg_success = "Status Updated";
          header('Location: index.php');
        } else {
          $msg_error = "Error: " . $stmt->error;
        }
      }
      if (isset($_POST["change_end_date_subproject"]) && isset($_GET["table_id"])) {
        // Get the progress value from the form
        $change_end_date_value = $_POST['change_end_date_value_sp'];
        $t_end_date = date("Y-m-d", strtotime($change_end_date_value));

        // Get the project ID from the URL parameter or form field
        $project_id = $_GET["table_id"]; // Assuming it's in the URL

        $sql = "UPDATE subprojects SET sub_end_date = ? WHERE table_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $t_end_date, $project_id); // Use "si" for a string and an integer

        if ($stmt->execute()) {
          $msg_success = "Status Updated";
          header('Location: index.php');
        } else {
          $msg_error = "Error: " . $stmt->error;
        }
      }

      if (isset($_POST["progress_input_data"]) && isset($_GET["progress_id"])) {
        // Get the progress value from the form
        $progress_status = $_POST['progress_data_number'];

        // Get the project ID from the URL parameter or form field
        $project_id = $_GET["progress_id"]; // Assuming it's in the URL

        // You can add additional validation and sanitation here

        // SQL query to update the 'progress' column of the 'projects' table for a specific project
        $sql = "UPDATE projects SET progress = ? WHERE project_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $progress_status, $project_id);

        if ($stmt->execute()) {
          $msg_success = "Status Updated";
          header('location:index.php');
        } else {
          $msg_error = "Error: " . $conn->error;
        }

        // Close the statement and connection

      }
      if (isset($_POST["progress_input_data_sp"]) && isset($_GET["table_id"])) {
        // Get the progress value from the form
        $progress_status = $_POST['progress_data_number'];

        // Get the project ID from the URL parameter or form field
        $project_id = $_GET["table_id"]; // Assuming it's in the URL

        // You can add additional validation and sanitation here

        // SQL query to update the 'progress' column of the 'projects' table for a specific project
        $sql = "UPDATE subprojects SET sub_progress = ? WHERE table_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $progress_status, $project_id);

        if ($stmt->execute()) {
          $msg_success = "Status Updated";
          header('location:index.php');
        } else {
          $msg_error = "Error: " . $conn->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
      }

      if (isset($_POST["close_subproject"])) {
        $table_id = $_POST['table_id'];

        // Your SQL query to update the sub-project status in the database
        $sql = "UPDATE subprojects SET mark_as_completed = 'Completed' WHERE table_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
          $stmt->bind_param("i", $table_id);
          $stmt->execute();

          if ($stmt->affected_rows > 0) {
            $msg_success = "Sub-Project Closed Successfully";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
          } else {
            // Redirect to the task-info page with an error message
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
          }
        } else {
          echo "Error: " . $conn->error;
        }
      }

      if (isset($_POST["readyToBeInvoiced"])) {

        $project_id = $_POST['project_id'];

        $currentDateTime = date('Y-m-d H:i:s');


        $stmt = $conn->prepare("INSERT INTO csa_finance_readytobeinvoiced (project_id,date) VALUES (?,?)");
        $stmt->bind_param("is", $project_id, $currentDateTime);

        // Execute the statement
        $stmt->execute();

        // Close the statement and connection
        $stmt->close();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
      }

      ?>






      <!-- end  -->




      <!-- End of Main Content -->
      <!-- Send back to user if project not verified  -->
      <?php
      include 'conn.php';


      if (isset($_GET['send_back_project_id'])) {
        // Get the project ID from the URL parameter
        $project_id = $_GET["send_back_project_id"];

        // You can add additional validation and sanitation here

        // Get other data from the URL or from wherever you want
        $status_value = "0";
        $assign_status = 1;
        $assign_to_status = 1;
        $checker_status = 0;
        $project_manager_status = 0;


        // SQL query to insert data into the table
        $sql = "UPDATE projects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL ,assign_status = ?, checker_status = ? , project_manager_status =?, assign_to_status = ? WHERE project_id = ?";
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiiis", $status_value, $assign_status, $checker_status, $project_manager_status, $assign_to_status, $project_id);

        if ($stmt->execute()) {
          $msg_success = "Data Sent  successfully for Rework!";
          header('location:index.php');
        } else {
          $msg_error = "Error: " . $conn->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
      }

      ?>
      <?php
      include 'include/footer.php';
      ?>
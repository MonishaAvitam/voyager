<head>
<!-- jQuery (must be loaded first) -->
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>



    <style>
        td {
            text-align: center;
        }

        /* Custom CSS to increase the size of the select field */
        .select2-container--default .select2-selection--single {
            height: 38px;
            /* Adjust the height of the select field */
            width: 460px
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            /* Center the text vertically */
        }
    </style>

</head>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="modal fade" id="add_subproject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog" role="document">

    <div class="modal-content">

      <div class="modal-header ">

        <h5 class="modal-title" id="exampleModalLabel">Sub project</h5>

        <button class="close" type="button" data-dismiss="modal" aria-label="Close">

          <span aria-hidden="true">×</span>

        </button>

      </div>

      <div class="modal-body">

        <div class="row">

          <div class="col-md-12">

            <div class="form-container">

              <form role="form" action="" method="post" autocomplete="off">

                <div class="form-horizontal">

                  <div class="form-group">

                    <label class="control-label">Choose Project</label>

                    <div class="">

                      <?php

                      $sql = "SELECT project_id, project_name FROM projects ";

                      $info = $obj_admin->manage_all_info($sql);

                      ?>
                      <select class="form-control new_sub_project_id " name="new_sub_project_id" id="new_sub_project_id" required>

                        <option value="">Select Project</option>

                        <?php

                        // Assume you have fetched a list of users with user_id and fullname from your database

                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                          echo '<option value="' . $row['project_id'] . '">' . $row['project_name'] . '</option>';
                        }

                        ?>

                      </select>
                    </div>


                  </div>
                  <div class="form-group">

                    <label class="control-label">Sub-Project Name</label>

                    <div class="">

                      <input type="text" placeholder="Project Name" id="sub_project_name" name="sub_project_name" list="expense" class="form-control" id="default" required>

                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label ">Sub-Project Details</label>

                    <div class="">

                      <textarea placeholder="Sub Project Details" name="sub_project_details" class="form-control" id="sub_project_details" cols="5" rows="5" required></textarea>

                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label">Estimated Hours</label>

                    <div class="">

                      <input class="form-control" type="number" name="t_end_date" id="t_end_date" min="0">



                    </div>

                  </div>

                  
                  <div class="form-group">

                    <label class="control-label">Estimated Date</label>

                    <div class="">

                      <input class="form-control" type="date" name="t_end_date_date" id="t_end_date_date" min="0">



                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label " for="assign_to">Engineer</label>

                    <div class="">

                      <?php

                      $sql = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess = 2 OR raeAccess = 3 OR raeAccess = 1";

                      $info = $obj_admin->manage_all_info($sql);

                      ?>

                      <select class="form-control assign_to"  name="assign_to" id="assign_to" required>

                        <option value="">Select Employee...</option>

                        <?php

                        // Assume you have fetched a list of users with user_id and fullname from your database

                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                          echo '<option value="' . $row['user_id'] . '|' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
                        }

                        ?>

                      </select>

                    </div>

                  </div>



                  <div class="form-group">

                    <label class="control-label" for="project_status">Sub Project Status</label>

                    <div class="">

                      <select class="form-control" id="project_status" name="project_status">

                        <option value="white">Don't Start the Project</option>

                        <option value="green">Ready to Start the Project</option>

                        <option value="purple">Closed</option>

                        <option value="orange">Urgent</option>

                        <option value="red">Very Urgent</option>

                        <option value="yellow">HOLD</option>


                      </select>

                    </div>

                  </div>

                </div>

                <div class="modal-footer">

                  <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>

                  <!-- <a class="btn btn-primary" href="login.html">Logout</a> -->

                  <button type="submit" name="new_sub_project_data" class="btn btn-secondary">Create Sub Project</button>

                </div>

              </form>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    new Choices("#new_sub_project_id", { searchEnabled: true });
    new Choices(".assign_to", { searchEnabled: true });
});

</script>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_sub_project_data"])) {
  // Assuming $conn is your database connection
  include 'conn.php';

  $project_id = $_POST['new_sub_project_id'];

  // Use a prepared statement for the SELECT query
  $sql_select = "SELECT project_manager, project_managers_id, p_team, contact_id, reopen_status, subproject_status FROM projects WHERE project_id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("s", $project_id);

  // Execute the SELECT statement
  if ($stmt_select->execute()) {
    $stmt_select->store_result();
    $stmt_select->bind_result($project_manager, $project_managers_id, $p_team, $contact_id, $reopen_status, $subproject_status);

    // Check if a row was found
    if ($stmt_select->fetch()) {
      // Increment subproject_status
      $subproject_status++;

      // Start a transaction for consistency
      $conn->begin_transaction();

      // Continue with your code...
      $sub_project_name = $_POST['sub_project_name'];
      $sub_project_details = $_POST['sub_project_details'];
      $t_start_date = date("Y-m-d");
      $assign_to_combined = $_POST['assign_to'];
      list($assign_to_id, $assign_to_username) = explode('|', $assign_to_combined);
      $project_status = $_POST['project_status'];
      $outsource = 0;

      $raw_hours = (int)$_POST['t_end_date'];
      $estimated_hours = $raw_hours . "H";
      $number = intval($_POST['t_end_date']); // Ensure it's an integer


      // Create a DateTime object for the current date (today)
      $currentDateTime = new DateTime();

      // Calculate the end date by adding the selected number of days to the current date
      $endDateTime = clone $currentDateTime;
      $t_end_date = $_POST['t_end_date_date'];
      if (empty($project_id) || empty($sub_project_name) || empty($assign_to_id) || empty($t_end_date)) {
        die("Error: Missing required fields!");
    }
    
      echo '<pre>';
      echo print_r($_POST);
      echo '</pre>';
      // exit;  
      // Use a prepared statement for the INSERT query
      $sql_insert = "INSERT INTO subprojects (project_id, subproject_name, subproject_details, contact_id, p_team,outsourced, project_manager, project_managers_id, start_date, sub_end_date, sub_EPT, assign_to_id, assign_to, urgency, reopen_status, subproject_status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,  ?, ?, ?)";

      $stmt_insert = $conn->prepare($sql_insert);

      // Check if the prepare statement was successful
      if ($stmt_insert) {
        $stmt_insert->bind_param("ssssssssssssssss", $project_id, $sub_project_name, $sub_project_details, $contact_id, $p_team,$outsource, $project_manager, $project_managers_id, $t_start_date, $t_end_date, $estimated_hours, $assign_to_id, $assign_to_username, $project_status, $reopen_status, $subproject_status);

        // Execute the statement
        if ($stmt_insert->execute()) {
          echo '<script>alert("Sub Project Created Successfully!"); window.location.href = "openprojects.php";</script>';
          $conn->commit();
      } else {
          $conn->rollback();
          die("Insert Error: " . $stmt_insert->error);
      }

      $stmt_insert->close();
  } else {
      die("Prepare statement failed: " . $conn->error);
  }
} else {
  die("No project found with ID: $project_id");
}
} else {
die("Error executing SELECT statement: " . $stmt_select->error);
}

$conn->close();
}
?>







<!-- end of projects section -->
<head>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

  <style>
    td {
      text-align: center;
    }

    /* For Select2 dropdowns */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #000;
      /* Set the color for selected text */
    }

    .select2-results__option {
      color: #000;
      /* Set the color for dropdown options */
      background-color: #fff;
      /* Ensure background is readable */
    }
  </style>

</head>

<div class="modal fade" id="add_revisionproject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header ">
        <h5 class="modal-title" id="exampleModalLabel">Revision Project</h5>
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
                      $sql = "
                                            SELECT project_id, project_name 
                      FROM projects 
                   

                                        ";

                      $info = $obj_admin->manage_all_info($sql);
                      ?>
                      <select class="form-control" name="revision_project_id" id="revision_project_id" required>
                        <option value="">Select Project</option>
                        <?php
                        // Assume you have fetched a list of users with user_id and fullname from your database
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                          echo '<option value="' . $row['project_id'] . '">' . $row['project_id'] . '_' . $row['project_name'] . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label">Revision-Project Name</label>
                    <div class="">
                      <input type="text" placeholder="Project Name" id="revision_project_name" name="revision_project_name" list="expense" class="form-control" id="default" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label ">Revision-Project Details</label>
                    <div class="">
                      <textarea placeholder="Revision Project Details" name="revision_project_details" class="form-control" id="revision_project_details" cols="5" rows="5" required></textarea>
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
                      <input class="form-control" type="date" name="t_estimate_date" id="t_estimate_date" min="0">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label " for="assign_to">Engineer</label>
                    <div class="">
                      <?php
                      $sql = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess = 2 OR raeAccess = 3 OR raeAccess = 1";
                      $info = $obj_admin->manage_all_info($sql);
                      ?>
                      <select class="form-control" name="assign_to" id="assign_to" required>
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
                    <label class="control-label" for="project_status">Revision Project Status</label>
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
                  <button type="submit" name="revision_project_data" class="btn btn-secondary">Create Revision Project</button>
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
  // Initialize Select2
  $(document).ready(function() {
    $('#revision_project_id').select2();
  });
</script>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["revision_project_data"])) {
  // Assuming $conn is your database connection
  include 'conn.php';

  $project_id = $_POST['revision_project_id'];

  // First query: check the project in 'projects' table
  $sql_select = "SELECT project_manager, project_managers_id, p_team, contact_id, reopen_status 
  FROM projects WHERE project_id = ? AND urgency = 'purple'";

  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("s", $project_id);

  // Execute the first SELECT statement
  if ($stmt_select->execute()) {
    $stmt_select->store_result();

    if ($stmt_select->num_rows > 0) {
      // If found in 'projects' table
      $stmt_select->bind_result($project_manager, $project_managers_id, $p_team, $contact_id, $reopen_status);
      $stmt_select->fetch();
    } else {
      // If not found in 'projects' table, check 'deliverable_data' table
      $sql_select_deliverable = "SELECT project_manager, project_managers_id, p_team, contact_id, reopen_status 
                                     FROM deliverable_data WHERE project_id = ? AND urgency = 'purple'";
      $stmt_select_deliverable = $conn->prepare($sql_select_deliverable);
      $stmt_select_deliverable->bind_param("s", $project_id);

      if ($stmt_select_deliverable->execute()) {
        $stmt_select_deliverable->store_result();

        if ($stmt_select_deliverable->num_rows > 0) {
          // If found in 'deliverable_data' table
          $stmt_select_deliverable->bind_result($project_manager, $project_managers_id, $p_team, $contact_id, $reopen_status);
          $stmt_select_deliverable->fetch();
        } else {
          $msg_error = "No matching project found for project_id: $project_id";
        }

        $stmt_select_deliverable->close();
      } else {
        $msg_error = "Error executing SELECT on deliverable_data: " . $stmt_select_deliverable->error;
      }
    }

    $stmt_select->close();
  } else {
    $msg_error = "Error executing SELECT on projects: " . $stmt_select->error;
  }

  // If there's no error, continue processing the data
  if (!isset($msg_error)) {
    // Second query to check reopen_status for the last matching revision_project_id
    $sql_check = "SELECT reopen_status FROM projects WHERE revision_project_id = ? ORDER BY project_id DESC LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $project_id);

    if ($stmt_check->execute()) {
      $stmt_check->store_result();

      if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($existingReopenStatus);
        $stmt_check->fetch();

        // If reopen_status is NULL or empty, start with "R1"
        if (empty($existingReopenStatus)) {
          $newReopenStatus = 'R1';
        } else {
          // Extract the numeric part and increment
          $numericPart = intval(substr($existingReopenStatus, 1));
          $newNumericPart = $numericPart + 1;
          $newReopenStatus = 'R' . $newNumericPart;
        }
      } else {
        // If no matching project found, set reopen_status to "R1"
        $newReopenStatus = 'R1';
      }

      $stmt_check->close();
    } else {
      $msg_error = "Error executing second SELECT: " . $stmt_check->error;
    }

    // Proceed with inserting data if no error
    if (!isset($msg_error)) {
      $conn->begin_transaction();

      $revision_project_name = $_POST['revision_project_name'];
      $revision_project_details = $_POST['revision_project_details'];
      $t_start_date = date("Y-m-d");

      $assign_to_combined = $_POST['assign_to'];
      list($assign_to_id, $assign_to_username) = explode('|', $assign_to_combined);

      $project_status = $_POST['project_status'];

      // Calculate estimated hours and total hours
      $number = intval($_POST['t_end_date']);
      $estimated_hours = $number . "H";



      // Calculate end date
      $currentDateTime = new DateTime();
      $endDateTime = clone $currentDateTime;
      $t_end_date = $_POST['t_estimate_date'];

      // Insert into projects table
      $sql_insert = "INSERT INTO projects (
                              revision_project_id, project_name, project_details, project_manager, assign_to_id, assign_to,
                              end_date, start_date, EPT, urgency, p_team, 
                              reopen_status, contact_id, project_managers_id
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt_insert = $conn->prepare($sql_insert);
      if ($stmt_insert) {
        $stmt_insert->bind_param(
          "ssssssssssssss",
          $project_id,
          $revision_project_name,
          $revision_project_details,
          $project_manager,
          $assign_to_id,
          $assign_to_username,
          $t_end_date,
          $t_start_date,
          $estimated_hours,
          $project_status,
          $p_team,
          $newReopenStatus,
          $contact_id,
          $project_managers_id
        );

        if ($stmt_insert->execute()) {
          $msg_success = "Revision Project created successfully!";
          $conn->commit();
        } else {
          $msg_error = "Error: " . $stmt_insert->error;
        }

        $stmt_insert->close();
      } else {
        $msg_error = "Prepare statement failed: " . $conn->error;
      }
    }
  }


  if (isset($msg_success)) {
    header('location:index.php?success=' . urlencode($msg_success));
    exit;
  } else {
    header('location:index.php?error=' . urlencode($msg_error));
    exit;
  }
}

?>



<!-- end of projects section -->
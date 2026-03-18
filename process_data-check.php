<?php
require 'authentication.php'; // admin authentication check 
require 'conn.php';



// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
  exit; // Terminate the script after the redirect
}

// check admin
$user_role = $_SESSION['user_role'];
include 'include/login_header.php';

// Check if 'project_id' is set in the URL
if (isset($_GET['project_id'])) {
  $project_id = $_GET['project_id'];
  $status_value = 1; // Changed to an integer
  $assign_status = 0;
  $checker_status = 1;
  $project_manager_status = 0;
  $assign_to_status = 1;


  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_data_verifier'])) {
    // Check if 'assign_to_verifier' is set in POST data
    if (isset($_POST['assign_to_verifier'])) {
      // Split the selected value into user_id and fullname
      list($user_id, $fullname) = explode('|', $_POST['assign_to_verifier']);

      // Use prepared statement to update the record
      $sql = "UPDATE projects SET verify_by = ?, verify_by_name = ?, verify_status = ?, assign_status = ?, checker_status = ?,project_manager_status = ?,assign_to_status = ? WHERE project_id = ?";

      // Prepare the SQL statement
      $stmt = $conn->prepare($sql);

      // Check if 'verify_by' is null and add parameters accordingly
      if ($row['verify_by'] === null) { // Use '===' to check for null values
        $status_value = 1;
        $assign_status = 0;
        $checker_status = 1;
        $project_manager_status = 0;
        $assign_to_status = 1;

      }

      // Define an array to hold the parameter types and values
      $params = ["ssiiiiii", $user_id, $fullname, $status_value, $assign_status, $checker_status,$project_manager_status,$assign_to_status, $project_id];

      // Use call_user_func_array to bind the parameters
      call_user_func_array([$stmt, "bind_param"], $params);

      if ($stmt->execute()) {
        $msg_success = "Verification sent successfully";
        header('Location: openprojects.php');
        exit; // Terminate the script after the redirect
      } else {
        echo "Error updating record: " . $conn->error;
      }
    } else {
      echo "No employee selected for verification.";
    }
  }
} else {
  echo "Missing project ID.";
}

// Close the connection
$conn->close();

?>



<script>
  $(document).ready(function() {
    $("#choose_manager").modal('show');
  });
</script>


<div class="modal fade" id="choose_manager" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered d-flex flex-column justify-content-center" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-container"> <!-- Center-align the form contents -->
              <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                  <label class="control-label" for="assign_to_verifier">Project Checker</label>
                  <div>
                    <?php
                    $sql = "SELECT user_id, fullname, raeAccess FROM tbl_admin WHERE raeAccess = 2 OR raeAccess = 3 OR raeAccess = 1 ORDER BY raeAccess ASC";
                    $info = $obj_admin->manage_all_info($sql);
                    ?>


                    <select class="form-control" name="assign_to_verifier" id="assign_to_verifier">
                      <option value="<?php echo $user_id . '|' . $user_name; ?>">SELF CHECK</option>

                      <?php
                      $currentRole = null;

                      while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        // Display the label when the raeAccess changes
                        if ($currentRole !== $row['raeAccess']) {
                          switch ($row['raeAccess']) {
                            case 3:
                              $label = "Project Manager";
                              break;
                            case 2:
                              $label = "Engineer";
                              break;
                            case 1:
                              $label = "Admin";
                              break;
                            default:
                              $label = "Unknown Role";
                          }
                          echo '<optgroup label="' . $label . '">';
                          $currentRole = $row['raeAccess'];
                        }

                        // Display individual options
                        echo '<option value="' . $row['user_id'] . '|' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
                      }

                      // Close the last optgroup if necessary
                      if ($currentRole !== null) {
                        echo '</optgroup>';
                      }
                      ?>
                    </select>

                  </div>
                </div>

                <button class="btn btn-secondary" type="button" data-dismiss="modal" onclick="window.location.href='index.php'">Cancel</button>

                <button type="submit" name="send_data_verifier" class="btn btn-secondary">Assign</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-warning w-100 text-center">
        <h6 class="text-center pr-4 text-dark font-weight-bold ">* Please fill out the timesheet before sending for checking!</h6>
      </div>
    </div>
  </div>
</div>




<?php
include 'include/sidebar.php';

include 'include/footer.php'


?>
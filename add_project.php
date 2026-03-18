<script>
  document.addEventListener('DOMContentLoaded', function() {
    $('#add_project').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var enquiryId = button.data('enquiry-id'); // Extract info from data-* attributes

      // If necessary, you can also initialize other modal elements here

      var modal = $(this);
      modal.find('#enquiryIdInput').val(enquiryId);
    });
  });
</script>


<div class="modal fade" id="add_project" tabindex="-1" role="dialog" aria-labelledby="addProjectLabel" aria-hidden="true">

  <div class="modal-dialog" role="document">

    <div class="modal-content">

      <div class="modal-header ">


        <h5 class="modal-title" id="exampleModalLabel">Create Project</h5>

        <button class="close" type="button" data-dismiss="modal" aria-label="Close">

          <span aria-hidden="true">×</span>

        </button>

      </div>

      <div class="modal-body">

        <div class="row">

          <div class="col-md-12">

            <div class="form-container">

              <form role="form" action="" method="post" autocomplete="off">
                <div class="form-group " hidden>
                  <label for="enquiryIdInput">Enquiry ID</label>
                  <input type="text" class="form-control" id="enquiryIdInput" name="enquiryId" readonly>
                </div>

                <div class="form-horizontal">

                  <div class="form-group">

                    <label class="control-label">Select Country</label>

                    <div class="">

                      <select class="form-control" name="state" id="state" required onchange="toggleOther()">
                        <option value="">Select Country</option>
                        <option value="SG">SG (Singapore)</option>
                        <option value="IND">IND (India)</option>
                        <option value="OTHER">Other</option>
                      </select>

                      <input
                        type="text"
                        class="form-control mt-2"
                        name="other_state"
                        id="other_state"
                        placeholder="Enter country name"
                        style="display:none;">
                      <script>
                        function toggleOther() {
                          const select = document.getElementById("state");
                          const input = document.getElementById("other_state");

                          if (select.value === "OTHER") {
                            input.style.display = "block";
                            input.required = true;
                          } else {
                            input.style.display = "none";
                            input.required = false;
                            input.value = "";
                          }
                        }
                      </script>


                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label">Project Name</label>

                    <div class="">

                      <input type="text" placeholder="Project Name" id="project_name" name="new_project_name" list="expense" class="form-control" id="default" required>

                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label ">Project Details</label>

                    <div class="">

                      <textarea placeholder="Project Details" name="project_details" class="form-control" id="project_details" cols="5" rows="5" required></textarea>

                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label">Customer</label>

                    <div class="">
                      <?php
                      $sql = "SELECT contact_id, customer_name, contact_name, customer_id FROM contacts";
                      $info = $obj_admin->manage_all_info($sql);
                      ?>

                      <select class="form-control" name="contact_id" id="contact_id" required>
                        <option value="">Select Customer</option>

                        <?php
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                          echo '<option value="' . htmlspecialchars($row['contact_id']) . '">
                (' . htmlspecialchars($row['customer_id']) . ') ' .
                            htmlspecialchars($row['customer_name']) . ' (' .
                            htmlspecialchars($row['contact_name']) . ')
              </option>';
                        }
                        ?>
                      </select>
                    </div>


                  </div>

                  <div class="form-group">

                    <label for="" class="control-label">Select Category</label>

                    <select class="form-control" name="category_id" id="category_id" required>
                      <option value="">Select Category</option>
                      <?php


                      $result = $conn->query("SELECT id, name FROM categories WHERE status = 1");

                      while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                      }
                      ?>
                    </select>


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

                    <label class="control-label " for="project_manager">Project Manager</label>

                    <div class="">

                      <?php

                      $sql = "SELECT user_id, fullname FROM tbl_admin WHERE  raeAccess = 3 or raeAccess = 1";

                      $info = $obj_admin->manage_all_info($sql);

                      ?>

                      <select class="form-control" name="project_manager" id="project_manager" required>

                        <option value="">Select Project Manager</option>

                        <?php


                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                          echo '<option value="' . $row['user_id'] . '|' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
                        }

                        ?>

                      </select>

                    </div>

                  </div>

                  <div class="form-group">

                    <label class="control-label " for="assign_to">Assigned To </label>

                    <div class="">

                      <?php

                      $sql = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess = 2 OR raeAccess = 3 or raeAccess = 1";

                      $info = $obj_admin->manage_all_info($sql);

                      ?>

                      <select class="form-control" name="assign_to" id="assign_to" required>

                        <option value="N/A">Not Assigned</option>
                        <option value="S/P">Has Sub Project</option>

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

                    <label class="control-label" for="project_status">Project Status</label>

                    <div class="">

                      <select class="form-control" id="project_status" name="project_status">

                        <option value="white">Ongoing</option>

                        <option value="green">Accepted</option>

                        <option value="red">Stand Down Or Rejected</option>

                        <option value="yellow">HOLD</option>






                      </select>


                    </div>


                  </div>

                </div>

                <div class="modal-footer">

                  <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>


                  <button type="submit" name="new_project_data" class="btn btn-secondary">Create Project</button>

                </div>

              </form>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>



<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_project_data"])) {




  // Check if the form was submitted and the "Create Project" button was clicked

  $new_project_name = $_POST['new_project_name'];

  $project_details = $_POST['project_details'];

  $contact_id = $_POST['contact_id'];

  $category_id = $_POST['category_id'];
  $state = $_POST['state'];
  $branchCode = 'null';

  $t_start_date = date("Y-m-d");




  // Assuming you have received the selected value in days from the form
  $raw_hours = (int)$_POST['t_end_date'];
  $estimated_hours = $raw_hours . "H";


  $number = intval($_POST['t_end_date']); // Ensure it's an integer






  // Create a DateTime object for the current date (today)
  $currentDateTime = new DateTime();

  // Calculate the end date by adding the selected number of days to the current date
  $endDateTime = clone $currentDateTime;

  // Format the end date as a string in the desired format (Y-m-d)
  $t_end_date = $_POST['t_end_date_date'];





  $project_manager_combined = $_POST['project_manager'];

  list($project_managers_id, $project_manager) = explode('|', $project_manager_combined);

  if ($_POST['assign_to'] == "S/P") {
    $assign_to_username = "S/P";
  } elseif ($_POST['assign_to'] == "N/A") {
    $assign_to_username = "N/A";
  } else {
    $assign_to_combined = $_POST['assign_to'];

    list($assign_to_id, $assign_to_username) = explode('|', $assign_to_combined);
  }


  $project_status = $_POST['project_status'];

  include 'conn.php';

  // Use prepared statements to prevent SQL injection

  $sql = "INSERT INTO projects (branch_code,project_name, project_details, contact_id, category_id, project_manager, project_managers_id, start_date, end_date, EPT, assign_to_id, assign_to, urgency,state)

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?)";

  $stmt = $conn->prepare($sql);

  $stmt->bind_param("ssssssssssssss", $branchCode, $new_project_name, $project_details, $contact_id, $category_id, $project_manager, $project_managers_id, $t_start_date, $t_end_date, $estimated_hours, $assign_to_id, $assign_to_username, $project_status, $state);

  if ($stmt->execute()) {

    // Data was inserted successfully

    $msg_success = "Project created successfully!";
    $project_id = $stmt->insert_id;

    $stmt->close();


    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  } else {

    // Error occurred while inserting data

    $msg_error = "Error: " . $conn->error;


    $stmt->close();


    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $enquiry_id = isset($_POST['enquiryId']) ? $_POST['enquiryId'] : '';
  $enquiry_status = "Projected Created ";
  if (!empty($enquiry_id)) {
    $updateEnquiryStatus1 = " UPDATE csa_sales_converted_projects SET enquiry_status=?,rae_project_id=? WHERE id=?";
    $update1 = $conn->prepare($updateEnquiryStatus1);
    if ($update1) {
      $update1->bind_param("sss", $enquiry_status, $project_id, $enquiry_id);
      $_SESSION['status_success'] = "Projected Created Successfully";

      if (!$update1->execute()) {
        echo "Error moving record: " . $update1->error;
        // Handle error if moving record fails
      }


      // Close the statement
      $update1->close();
    } else {
      echo "Error preparing statement for moving record: " . $conn->error;
      // Handle error if preparing statement fails
    }
  }

  $conn->close();
}

?>

<!-- end of projects section -->
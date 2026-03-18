<?php
require 'authentication.php'; // admin authentication check 

require 'conn.php';

// auth check

$user_id = $_SESSION['admin_id'];

$user_name = $_SESSION['name'];

$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {

  header('Location: index.php');
}


// check admin

$user_role = $_SESSION['user_role'];

//end
?>

<?php include 'include/login_header.php'; ?>
<?php include 'include/sidebar.php'; ?>
<?php include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php'; ?>


<!-- dashboard content  -->


<?php if ($user_role == 1) { ?>

  <div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0 text-gray-800">Deliverable Data</h1>
      <div class="d-flex align-items-center">
      </div>
    </div>


    <!-- Content Row -->
    <div class="container-fluid">


      <div class="card shadow mb-4">

        <div class="card-header py-3">

          <h6 class="m-0 font-weight-bold text-primary">Completed Projects: <?php
                                                                            $branchCode = isset($_SESSION['branch_choice']) ? $_SESSION['branch_choice'] : "ALL-BRANCHES";
                                                                            if ($branchCode === 'SYD01') {
                                                                              echo 'Sydney (SYD01) ';
                                                                            } else if ($branchCode === 'BRI01') {
                                                                              echo 'Brisbane (BRI01) ';
                                                                            } else if (!$branchCode || $branchCode === 'ALL-BRANCHES') {
                                                                              echo 'All Branches';
                                                                            }
                                                                            ?></h6>
          <button type="button" class="btn btn-primary float-right" id="filter-icon" data-toggle="modal" data-target="#filter-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
              <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z" />
            </svg>
            Filter
          </button>

          <div class="modal fade" id="filter-modal" tabindex="-1" aria-labelledby="filter-modal-label" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title text-primary" id="filter-modal-label">Filter Options</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <!-- Add your filter options here -->

                  <label for="branch-filter" class="text-primary">Filter by Branch Code:</label>
                  <select id="branch-filter" class="form-control">
                    <option value="">All</option>
                    <?php

                    require "./conn.php";
                    // Assuming you have a valid $conn connection object
                    $sql_branch_codes = "SELECT DISTINCT branch_code FROM projects WHERE branch_code IS NOT NULL";
                    $result_branch_codes = $conn->query($sql_branch_codes);

                    // Check if we have branch codes
                    if ($result_branch_codes->num_rows > 0) {
                      while ($row = $result_branch_codes->fetch_assoc()) {
                        echo '<option value="' . $row['branch_code'] . '">' . $row['branch_code'] . '</option>';
                      }
                    } else {
                      echo '<option value="">No Branch Codes Available</option>';
                    }
                    ?>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="apply-filter">Apply Filter</button>
                </div>
              </div>
            </div>
          </div>
          <script>
            $(document).ready(function() {
              var dataTable = $('#dataTable').DataTable();

              // Apply filters when "Apply Filter" button is clicked
              document.getElementById("apply-filter").addEventListener("click", function() {
                // Get the selected filter values 
                var selectedBranchFilter = document.getElementById("branch-filter").value;

                // Apply the team filter to the DataTable (assuming team filter is on column 4, change index if needed)

                // Apply the branch filter to the DataTable (assuming branch code filter is on column 7, change index if needed)
                dataTable.column(4).search(selectedBranchFilter).draw();

                // Close the filter modal
                $('#filter-modal').modal('hide');
              });
            });
          </script>


        </div>
        <?php
        // Retrieve the project_id from the URL
        if (isset($_GET['project_id'])) {
          $project_id = $_GET['project_id'];
          // Now, you can use $project_id for fetching data or handling the page.
        } else {
          // Handle case when project_id is not available in the URL
          // echo "Project ID is missing.";
        }
        ?>
        <script>
          $(document).ready(function() {
            // Initialize DataTable
            var table = $('#dataTable').DataTable();

            // Optional: If you want to handle the search when the page loads, you can trigger the search here using the query parameter
            var urlParams = new URLSearchParams(window.location.search);
            var projectId = urlParams.get('project_id'); // Get the project_id from the URL

            if (projectId) {
              // Set the search input value to project ID or any custom format
              table.search(`${projectId}`).draw();
            }
          });
        </script>

        <div class="card-body">

          <div class="table-responsive">

            <table class="table table-bordered table-sm" id="dataTable" width="100%" cellspacing="0">

              <thead>

                <tr>

                  <th>Project Number</th>

                  <th>Project Title</th>

                  <th>Project Manager</th>

                  <th>Project Completion Date</th>
                  <th>Action</th>

                </tr>

              </thead>

              <tfoot>

                <tr>

                  <th>Project Number</th>

                  <th>Project Title</th>

                  <th>Project Manager</th>

                  <th>Project Completion Date</th>

                  <th>Action</th>

                </tr>

              </tfoot>

              <tbody>



                <!-- ... Your table rows ... -->

                <?php



                $sql = "    SELECT 'Project' AS type, p.project_id, p.project_name, p.branch_code, p.p_team, p.project_manager, p.assign_to, p.EPT, p.urgency, p.reopen_status,p.revision_project_id, p.subproject_status, p.contact_id, c.customer_name,p.end_date,p.	projectClosed
               FROM projects p
               LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
               WHERE  p.urgency = 'purple' AND deliverable_mailDate IS NULL";

                if ($user_role == 1) {

                  // Admin can see all projects

                  $sql .= " ORDER BY p.project_id DESC";
                }

                $info = $obj_admin->manage_all_info($sql);

                $serial  = 1;

                $num_row = $info->rowCount();

                if ($num_row < 0) {

                  echo '<tr><td colspan="7">No projects were found</td></tr>';
                }

                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                ?>

                  <tr>
                    <?php
                    $backgroundColor = '';
                    $color = '';
                    if (isset($row['setTarget']) && $row['setTarget'] == 'Active') {
                      if (strtotime($row['setTargetDate']) <= strtotime(date('Y-m-d')) || strtotime($row['setTargetDate']) <= strtotime('+5 days')) {
                        $backgroundColor = "red";
                      } else {
                        $backgroundColor = "orange";
                      }
                    } else {
                      if ($row['urgency'] === 'purple') {
                        $backgroundColor = 'navy'; // Set background to navy blue when urgency is purple
                      } else {
                        $backgroundColor = $row['urgency'];
                      }
                    }
                    $color = ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff';
                    ?>

                    <td style="background-color: <?php echo $backgroundColor; ?>; text-align: center;  color: <?php echo $color; ?>">
                      <?php echo $row['project_id']; ?>
                      <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>

                    </td>
                    <td><?php echo $row['project_name']; ?></td>
                    <td><?php echo $row['project_manager']; ?></td>
                    <td><?php echo $row['projectClosed']; ?></td>
                    <td>


                      <a title="View" class="" href="task-details.php?project_id=<?php echo $row['project_id']; ?>">

                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">

                          <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />

                        </svg>

                      </a>&nbsp;&nbsp;



                        <?php if ($user_role == 1 or $user_role == 3) { ?>
                          <a id="update-project-button" title="Update Project Details" href="edit-project.php?project_id=<?php echo $row['project_id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                              <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001zm-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708l-1.585-1.585z" />
                            </svg></a> &nbsp;&nbsp;
                        <?php } ?>
                        <script>
                    document.getElementById("update-project-button").addEventListener("click", function() {
                      sessionStorage.setItem("lastVisitedURL", window.location.href);
                      console.log("Current URL stored:", window.location.href);
                    });
                  </script>
                      <button type="button" class="btn btn-danger btn-sm"
                        onclick="document.getElementById('completion-dateee').value = '<?php echo $row['projectClosed']; ?>'; updateUrl(<?php echo $row['project_id']; ?>)"
                        data-target="#status_model" data-toggle="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-octagon" viewBox="0 0 16 16">
                          <path d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353L4.54.146zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1H5.1z" />
                          <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                        </svg>
                        Close Project
                      </button>
                      <?php if (($user_role == 1)) {
                        // Assuming you already have the database connection as $conn
                        $project_id = $row['project_id'];
                        // Default button color (blue)
                        $button_color = "btn-primary";
                        $modal_message = "Are you sure you want to send this project to Count?";
                        // Check if the project_id is in csa_finance_invoiced
                        $sql_invoiced = "SELECT 1 FROM csa_finance_invoiced WHERE project_id = ?";
                        $stmt_invoiced = $conn->prepare($sql_invoiced);
                        $stmt_invoiced->bind_param("i", $project_id);
                        $stmt_invoiced->execute();
                        $stmt_invoiced->store_result();
                        if ($stmt_invoiced->num_rows > 0) {
                          // If project_id exists in csa_finance_invoiced, set color to green
                          $button_color = "btn-success";
                          $modal_message = "Project is Invoiced.";
                        } else {
                          // Check if the project_id is in csa_finance_readytobeinvoiced
                          $sql_ready = "SELECT 1 FROM csa_finance_readytobeinvoiced WHERE project_id = ?";
                          $stmt_ready = $conn->prepare($sql_ready);
                          $stmt_ready->bind_param("i", $project_id);
                          $stmt_ready->execute();
                          $stmt_ready->store_result();
                          if ($stmt_ready->num_rows > 0) {
                            // If project_id exists in csa_finance_readytobeinvoiced, set color to red
                            $button_color = "btn-danger";
                            $modal_message = "Project is sent to COUNT.";
                          }
                          $stmt_ready->close();
                        }
                        $stmt_invoiced->close();
                      ?>
                        <button class="btn <?php echo $button_color; ?> btn-sm p-1" style="font-size: 12px;"
                          onclick="showConfirmationModal(event, <?php echo $row['project_id']; ?>, '<?php echo addslashes($row['project_name']); ?>', '<?php echo $modal_message; ?>')">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z" />
                          </svg>
                        </button>
                      <?php } ?>
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


                      <?php
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
                      } ?>
                    </td>

                  </tr>

                <?php } ?>

              </tbody>

            </table>

          </div>

        </div>

      </div>

    </div>


    <script>
      function updateUrl(projectId) {

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

    <!-- Modal -->

    <div class="modal fade" id="status_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">

      <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

          <div class="modal-header">

            <h5 class="modal-title" id="exampleModalLongTitle">Project Delivered</h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">

              <span aria-hidden="true">&times;</span>

            </button>


          </div>

          <form action="" method="POST">

            <div class="modal-body">

              Are you sure want to close this project? If yes, please enter the date on which you have sent mail to client<br>



              <p for="" class="text-danger">Mail Date :<br>
                <input type="date" name="mailDate" id="" class="form-control" required>
              </p>



              <label for="" class=""> Project Completion Date</label>
              <input type="date" name="completion-date" id="completion-dateee" class="form-control" required>










            </div>




            <div class="modal-footer">

              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

              <!-- Use type="submit" to submit the form -->

              <button type="submit" class="btn btn-primary" name="close_project">Confirm</button>

            </div>

          </form>

        </div>

      </div>

    </div>

    <!-- End of Main Content -->

    <!-- Send back to user if project not verified  -->

    <?php

    // Include the database connection file

    include 'conn.php';

    // Check if the "close_project" POST variable is set and if the "project_id" GET parameter is set

    if (isset($_POST["close_project"]) && isset($_GET["project_id"])) {

      // Get the project ID from the URL parameter or form field

      $project_id = intval($_GET["project_id"]); // Assuming it's in the URL
      // $mailDate = intval($_POST["mailDate"]); // Assuming it's in the URL
      $mailDate = $_POST["mailDate"];

      $completionDate = $_POST["completion-date"];

      // $mailDate = $_POST('mailDate');
      // Validate and sanitize the project ID

      if ($project_id <= 0) {

        die("Invalid project ID");
      }

      // Define the new status for the project


      // SQL query to update the 'urgency' column in the 'deliverable_data' table for a specific project

      $sql = "UPDATE projects SET deliverable_mailDate = ?, projectClosed = ? WHERE project_id = ?";

      // Prepare and execute the statement

      $stmt = $conn->prepare($sql);

      if ($stmt === false) {

        die("Error preparing SQL statement: " . $conn->error);
      }

      if ($stmt->bind_param("ssi", $mailDate, $completionDate, $project_id)) {

        if ($stmt->execute()) {

          // Project successfully closed, you can set a success message here

          $msg_success = "Project Closed";

          // Redirect the user back to the previous page

          header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {

          // Error occurred during the SQL execution

          $msg_error = "Error: " . $stmt->error;
        }
      } else {

        die("Error binding parameters: " . $stmt->error);
      }
    }

    if (isset($_GET['send_back_project_id'])) {

      // Get the project ID from the URL parameter

      $project_id = $_GET["send_back_project_id"];

      // You can add additional validation and sanitation here

      // Get other data from the URL or from wherever you want

      $status_value = "0";

      // SQL query to insert data into the table

      $sql = "UPDATE projects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL WHERE project_id = ?";

      // Prepare and execute the statement

      $stmt = $conn->prepare($sql);

      $stmt->bind_param("is", $status_value, $project_id);

      if ($stmt->execute()) {

        $msg_success = "Data inserted successfully!";
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
} else {
  echo "Restricted Access";
}
  ?>
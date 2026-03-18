<?php

require '../authentication.php'; // admin authentication check 
require '../conn.php';
// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: ../index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

// delete project

if (isset($_GET['delete_project_id'])) {
  $delete_project_id = $_GET['delete_project_id'];


  // SQL query to delete the project from the "projects" table
  $sqlProjects = "DELETE FROM projects WHERE project_id = $delete_project_id";

  // SQL query to delete the project from the "deliverable_data" table
  $sqlDeliverables = "DELETE FROM deliverable_data WHERE project_id = $delete_project_id";

  // Perform both deletions in a transaction
  $conn->begin_transaction();

  try {
    // Delete from the "projects" table
    $conn->query($sqlProjects);

    // Delete from the "deliverable_data" table
    $conn->query($sqlDeliverables);

    // If both deletions are successful, commit the transaction
    $conn->commit();

    // Display a success Toastr notification
    $msg_error = "Project Deleted Successfully";
    header('location: ' . $_SERVER['PHP_SELF']);
  } catch (Exception $e) {
    // If an error occurs, roll back the transaction and display an error Toastr notification
    $conn->rollback();
    $msg_error = "Error deleting the Project: " . $e->getMessage();
  } finally {
    // Close the database connection
    $conn->close();
  }
}

if (isset($_GET['delete_project_id_develirables'])) {
  $delete_project_id = $_GET['delete_project_id_develirables'];

  // SQL query to delete the project
  $sql = "DELETE FROM deliverable_data WHERE project_id = $delete_project_id";

  if ($conn->query($sql) === TRUE) {
    // Display a success Toastr notification
    $msg_error = "Project Deleted Successfully";
    header('location: ' . $_SERVER['PHP_SELF']);
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


//end




?>

<?php include 'include/login_header.php'; ?>
<?php include 'include/sidebar.php'; ?>




<!-- dashboard content  -->

<div class="container-fluid">

  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">All Projects</h1>

    <div class="d-flex align-items-center">

    </div>


  </div>







  <!-- Content Row -->



  <div class="container-fluid">








    <!-- Page Heading -->
    <!-- <h1 class="h3 mb-2 text-gray-800">Tables</h1>
  <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
    For more information about DataTables, please visit the <a target="_blank" href="https://datatables.net">official DataTables documentation</a>.</p> -->

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <div>
          <h6 class="m-0 font-weight-bold text-primary">All Projects</h6>
        </div>
        <div class="">
          <a href="#" class="float-right" id="filter-icon" data-toggle="modal" data-target="#filter-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
              <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z" />
            </svg>
          </a>
          <!-- HTML for your filter modal -->
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
                  <label for="team-filter" class="text-primary">Filter by Team:</label>
                  <select id="team-filter" class="form-control">
                    <option value="">All</option>
                    <option value="Building">Building Team</option>
                    <option value="Industrial">Industrial Team</option>
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

              document.getElementById("apply-filter").addEventListener("click", function() {
                // Get the selected filter value
                var selectedFilter = document.getElementById("team-filter").value;

                // Apply the filter to the DataTable
                dataTable.column(3).search(selectedFilter).draw();

                // Close the filter modal
                $('#filter-modal').modal('hide');
              });
            });
          </script>

        </div>

      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Project Number</th>
                <th>Project Title</th>
                <th>Customer Name</th>

                <th>Project Manager</th>
                <th>Team</th>
                <th>Engineer</th>
                <th>EPT</th>
                <th>Action</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Project Number</th>
                <th>Project Title</th>
                <th>Customer Name</th>

                <th>Project Manager</th>
                <th>Team</th>
                <th>Engineer</th>
                <th>EPT</th>
                <th>Action</th>
              </tr>
            </tfoot>
            <tbody>

              <!-- ... Your table rows ... -->
              <?php
              $sql = "
               SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team, p.project_manager, p.assign_to, p.EPT, p.urgency, p.reopen_status, p.subproject_status, p.contact_id, c.customer_name,p.end_date
               FROM projects p
               LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
               
               UNION
               
               SELECT 'Deliverable' AS type, dd.project_id, dd.project_name, dd.p_team, dd.project_manager, dd.assign_to, dd.EPT, dd.urgency, dd.reopen_status, NULL AS subproject_status, dd.contact_id, c.customer_name,dd.end_date
               FROM deliverable_data dd
               LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (dd.contact_id = c.contact_id)
           
           ";

              $info = $obj_admin->manage_all_info($sql);
              $serial  = 1;
              $num_row = $info->rowCount();
              if ($num_row == 0) {
                echo '<tr><td colspan="7">No projects were found</td></tr>';
              }
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
              ?>
                <tr>


                  <td style="background-color: <?php echo $row['urgency']; ?>; 
                  color: <?php echo ($row['urgency'] == 'white' || $row['urgency'] == 'yellow') ? '#000' : '#fff'; ?>; 
                  text-align: center; width: 5%; border-radius: 5px;">


                    <?php echo $row['project_id']; ?>


                    <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>

                  </td>
                  <td><?php echo $row['project_name']; ?></td>
                  <td><?php echo $row['customer_name']; ?></td>
                  <td><?php echo $row['project_manager']; ?></td>
                  <td><?php echo $row['p_team']; ?></td>
                  <td><?php echo $row['assign_to']; ?></td>


                  <td><?php echo $row['EPT']; ?>H</td>

                  <td style="text-align: center;">

                    <a title="View" class="view-project" href="../task-details.php?project_id=<?php echo $row['project_id']; ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                        <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                      </svg>
                    </a>&nbsp;&nbsp;
              
                    <?php if ($user_role == 1 or $user_role == 3 && $row['urgency'] == "purple") {  ?>
                      <a title="open" class="view-project" href="" onclick="updateUrl(<?php echo $row['project_id']; ?>)" data-target="#status_model" data-toggle="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-opencollective" viewBox="0 0 16 16">
                          <path fill-opacity=".4" d="M12.995 8.195c0 .937-.312 1.912-.78 2.693l1.99 1.99c.976-1.327 1.6-2.966 1.6-4.683 0-1.795-.624-3.434-1.561-4.76l-2.068 2.028c.468.781.78 1.679.78 2.732h.04Z" />
                          <path d="M8 13.151a4.995 4.995 0 1 1 0-9.99c1.015 0 1.951.273 2.732.82l1.95-2.03a7.805 7.805 0 1 0 .04 12.449l-1.951-2.03a5.072 5.072 0 0 1-2.732.781H8Z" />
                        </svg>
                      </a>&nbsp;

                    <?php  }  ?>

                    <?php if ($row['urgency'] == "purple") { ?>
                      <?php if ($user_role == 1 or $user_role == 3) { ?>
                       
                      <?php } ?>

                    <?php } else { ?>

                      <?php if ($row['subproject_status'] != NUll) {  ?>


                        <?php if ($user_role == 1 or $user_role == 3) { ?>
                          <a title="Delete" href="?delete_table_id=<?php echo $row['table_id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                            </svg></a>&nbsp;
                        <?php } ?>


                      <?php  } else { ?>

                        <?php if ($user_role == 1 or $user_role == 3 or $user_role == 2) { ?>
                        
                        <?php } ?>


                      <?php  } ?>

                     
                    <?php } ?>

                    <?php if ($user_role == 1 or $user_role == 3) { ?>
                      <a title="Update Project Details" href="edit-project.php?project_id=<?php echo $row['project_id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                                  <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001zm-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708l-1.585-1.585z" />
                                </svg></a>&nbsp;&nbsp;
                          

                      <?php } ?>
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
  <div class="modal fade" id="status_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Reopen Project</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" method="POST">
          <div class="modal-body">
            Are you sure want to Reopen this project ?
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
  // Check if the "close_project" POST variable is set and if the "project_id" GET parameter is set
  if (isset($_POST["close_project"]) && isset($_GET["project_id"])) {
    // Get the project ID from the URL parameter or form field
    $project_id = intval($_GET["project_id"]); // Assuming it's in the URL

    // Validate and sanitize the project ID
    if ($project_id <= 0) {
      die("Invalid project ID");
    }

    // Define the new status for the project
    $project_status = 'green';

    // SQL query to update the 'urgency' column in the 'deliverable_data' table for a specific project
    $sql = "UPDATE deliverable_data SET urgency = ? WHERE project_id = ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
      die("Error preparing SQL statement: " . $conn->error);
    }

    if ($stmt->bind_param("si", $project_status, $project_id)) {
      if ($stmt->execute()) {
        // Project successfully closed, you can set a success message here
        $msg_success = "Project Reopened";
        // Redirect the user back to the previous page
        header('Location: undo_process_data-delivery.php?project_id=' . $project_id);
      } else {
        // Error occurred during the SQL execution
        $msg_error = "Error: " . $stmt->error;
      }
    } else {
      die("Error binding parameters: " . $stmt->error);
    }
  }


  //   if (isset($_POST["send_to_deliverables"]) && isset($_GET["file_project_id"])) {
  //     $project_id = $_GET["file_project_id"]; // Assuming it's in the URL
  //     header('Location: process_data-delivery.php?project_id=' . $project_id);
  //     exit; // It's a good practice to exit after a header redirect
  //   }

  ?>
  <?php
  include 'include/footer.php';
  ?>
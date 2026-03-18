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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




<!-- dashboard content  -->

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Projects</h1>

        <div class="d-flex align-items-center">

        </div>


        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#filter-modal">
    <i class="fas fa-filter"></i> Filter
</button>


    </div>






    <!-- Content Row -->


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
                <!-- Filter by Project ID -->
                <label for="project-id-filter" class="text-primary">Filter by Project ID:</label>
                <input type="number" id="project-id-filter" class="form-control" placeholder="Enter Project ID">

                  <!-- Filter by Engineer -->
          <label for="engineer-filter" class="text-primary mt-3">Filter by Engineer:</label>
<select id="engineer-filter" class="form-control">
    <option value="">Select Engineer</option>
    <?php
    // Assuming you have a valid connection to the database
    require './conn.php';

    // Fetch unique engineers from the projects table
    $sql_engineers = "SELECT DISTINCT assign_to FROM projects WHERE assign_to IS NOT NULL";
    $result_engineers = $conn->query($sql_engineers);

    // Create a dropdown option for each engineer
    if ($result_engineers->num_rows > 0) {
        while ($row = $result_engineers->fetch_assoc()) {
            echo '<option value="' . $row['assign_to'] . '">' . $row['assign_to'] . '</option>';
        }
    } else {
        echo '<option value="">No Engineers Available</option>';
    }
    ?>
</select>










                <!-- Filter by Team -->
                <!-- <label for="team-filter" class="text-primary mt-3">Filter by Team:</label>
                <select id="team-filter" class="form-control">
                    <option value="">Select Team</option>
                    <option value="Industrial">Industrial</option>
                    <option value="Building">Building</option>
                    <option value="IT">IT</option>
                </select> -->

                <!-- Filter by State -->
                <label for="state-filter" class="text-primary mt-3">Filter by State:</label>
                <select id="state-filter" class="form-control">
                    <option value="">Select State</option>
                    <option value="QLD">QLD (Queensland)</option>
                    <option value="NSW">NSW (New South Wales)</option>
                    <option value="WA">WA (Western Australia)</option>
                    <option value="SA">SA (South Australia)</option>
                    <option value="VICTORIA">VICTORIA</option>
                    <option value="N/A">Not Applicable</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="apply-filter">Apply Filter</button>
            </div>
        </div>
    </div>
</div>

    <div class="container-fluid">








        <!-- Page Heading -->
        <!-- <h1 class="h3 mb-2 text-gray-800">Tables</h1>
  <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
    For more information about DataTables, please visit the <a target="_blank" href="https://datatables.net">official DataTables documentation</a>.</p> -->

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">My Projects</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Project Number</th>
                                <th>Project Title</th>
                                <th>Project Manager</th>
                                <th>Engineer</th>
                                <th>State</th>
                                <th>EPT</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Project Number</th>
                                <th>Project Title</th>
                                <th>Project Manager</th>
                                <th>Engineer</th>
                                <th>State</th>
                                <th>EPT</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            <!-- ... Your table rows ... -->
                            <?php
              $sql = "
               SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team,p.state, p.project_manager, p.assign_to, p.EPT, p.urgency, p.reopen_status,p.revision_project_id, p.subproject_status, p.contact_id, c.customer_name,p.end_date
               FROM projects p
               LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
               WHERE p.assign_to_id = $user_id or p.verify_by = $user_id or p.project_managers_id = $user_id
               
               UNION
               
               SELECT 'Deliverable' AS type, dd.project_id, dd.project_name, dd.p_team,dd.state, dd.project_manager, dd.assign_to, dd.EPT, dd.urgency, dd.reopen_status,NULL AS revision_project_id, NULL AS subproject_status, dd.contact_id, c.customer_name,dd.end_date
               FROM deliverable_data dd
               LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (dd.contact_id = c.contact_id)
               WHERE dd.assign_to_id = $user_id or dd.verify_by = $user_id or dd.project_managers_id = $user_id


           
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


                                    <td style="background-color: <?php echo $row['urgency']; ?>; color: #fff; text-align: center; width: 5%; border-radius: 5px;">
                                        <?php echo $row['project_id']; ?>


                                        <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>

                                    </td>
                                    <?php if ($row['subproject_status'] != NUll) {  ?>
                                        <td><?php echo $row['subproject_name']; ?></td>
                                    <?php } else { ?>
                                        <td><?php echo $row['project_name']; ?></td>
                                    <?php } ?>
                                    <td><?php echo $row['project_manager']; ?></td>
                                    <td><?php echo $row['assign_to']; ?></td>
                                    <td><?php echo $row['state']; ?></td>

                                    <?php if ($row['subproject_status'] != NULL) {  ?>

                                        <td><?php echo $row['sub_EPT']; ?></td>
                                    <?php  } else { ?>
                                        <td><?php echo $row['EPT']; ?></td>
                                    <?php } ?>


                                    <td style="text-align: center;">

                                        <a title="View" class="view-project" href="task-details.php?project_id=<?php echo $row['project_id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                                <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                            </svg>
                                        </a>&nbsp;&nbsp;
                               
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

$(document).ready(function() {
    var dataTable = $("#dataTable").DataTable();

    $("#apply-filter").on("click", function() {
        var selectedProjectIdFilter = $("#project-id-filter").val().trim();
        var selectedEngineerFilter = $("#engineer-filter").val().trim();
        var selectedStateFilter = $("#state-filter").val().trim();

        console.log("Project ID:", selectedProjectIdFilter);
        console.log("Engineer:", selectedEngineerFilter);
        console.log("State:", selectedStateFilter);

        // Apply filters (allow partial matching for engineer filter)
        dataTable.columns(0).search(selectedProjectIdFilter).draw();
        dataTable.columns(3).search(selectedEngineerFilter, true, false).draw();

        console.log("Engineer Column Data:", dataTable.column(3).data().toArray());

        dataTable.columns(4).search(selectedStateFilter).draw();
    });
});



</script>


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
    include 'conn.php';
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
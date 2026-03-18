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
include 'enquiry.php';

//end








$page_name = "Task_Info";
// include('ems_header.php');



// delete project

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
    include 'conn.php';
    // Assuming you have a database connection in $conn
    $my_projectCount = 0; // Initialize the project count variable
    $enquiryCount = 0; // Initialize the enquiry count variable
    $projectCount = 0; // Initialize the project count variable

    // Assuming you have a variable $loggedInEmployeeID containing the ID of the logged-in employee
    $loggedInEmployeeID = $_SESSION['admin_id']; // Adjust this based on your authentication method
    $projectQuery = "SELECT COUNT(*) AS project_count FROM projects";


    // Check if the user is logged in and their ID is set
    if (isset($loggedInEmployeeID)) {
        // Escape and sanitize the input to prevent SQL injection
        $loggedInEmployeeID = mysqli_real_escape_string($conn, $loggedInEmployeeID);

        // Execute a query to retrieve the project count for the logged-in employee
        $my_projectQuery = "SELECT COUNT(*) AS my_project_count FROM projects WHERE assign_to_id = $loggedInEmployeeID";
        $projectQuery = "SELECT COUNT(*) AS project_count FROM projects";
        $enquiryQuery = "SELECT COUNT(*) AS enquiry_count FROM enquiry_data";

        $my_projectResult = mysqli_query($conn, $my_projectQuery);
        $enquiryResult = mysqli_query($conn, $enquiryQuery);
        $projectResult = mysqli_query($conn, $projectQuery);

        if ($my_projectResult && $enquiryResult && $projectResult) {
            // Fetch the project count from the result
            $my_projectRow = mysqli_fetch_assoc($my_projectResult);
            $enquiryRow = mysqli_fetch_assoc($enquiryResult);
            $projectRow = mysqli_fetch_assoc($projectResult);

            // Update the variables with the counts
            $my_projectCount = $my_projectRow['my_project_count'];
            $enquiryCount = $enquiryRow['enquiry_count'];
            $projectCount = $projectRow['project_count'];
        } else {
            // Handle query execution error
            echo "Query error: " . mysqli_error($conn);
        }
    } else {
        // Handle the case where the user is not logged in or their ID is not set
    }

    // Close the database connection
    mysqli_close($conn);
    ?>






    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sub Projects</h1>

        <div class="d-flex align-items-center">

        </div>


    </div>


    <script src="./timer.js"></script>


  
    <!-- Content Row -->








    <!-- dashboard content end -->

    <!-- Begin Page Content -->
    <div class="container-fluid mt-md-5">







        <!-- Page Heading -->
        <!-- <h1 class="h3 mb-2 text-gray-800">Tables</h1>
  <p class="mb-4">DataTables is a third party plugin that is used to generate the demo table below.
    For more information about DataTables, please visit the <a target="_blank" href="https://datatables.net">official DataTables documentation</a>.</p> -->

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="row">
                    <div class="col-6">
                        <h6 class="m-0 font-weight-bold text-primary ">Sub Projects</h6>
                    </div>
                    <div class="col-6">
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
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th> ID</th>
                                <th>Project Title</th>
                                <th>customer_id</th>
                                <th hidden>team</th>

                                <th>PM</th>
                                <th>Engineer</th>
                                <th>Checker</th>
                                <th>Hours</th>
                                <th>ECD</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th> ID</th>
                                <th>Project Title</th>
                                <th>customer_id</th>
                                <th hidden>team</th>
                                <th>PM</th>
                                <th>Engineer</th>
                                <th>Checker</th>
                                <th>Hours</th>
                                <th>ECD</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            <!-- ... Your table rows ... -->
                            <?php
                            $sql = "SELECT sp.*, pm.fullname, c.contact_name, c.contact_email, c.contact_phone_number, c.contact_id, c.customer_id
                            FROM subprojects sp
                            LEFT JOIN project_managers pm ON (sp.project_manager = pm.fullname)
                            LEFT JOIN contacts c ON (sp.contact_id = c.contact_id)
                            WHERE 1=1";

                            if ($user_role == 1) {
                                // Admin can see all subprojects, so no additional conditions needed
                            } elseif ($user_role == 2) {
                                // Regular users with role 2 can see subprojects assigned to them
                                $sql .= " AND ((sp.assign_to_id = '$user_id' AND sp.verify_status = '0' OR sp.verify_status IS NULL) OR sp.verify_by = '$user_id') AND sp.assign_to_status = 1";
                            } elseif ($user_role == 3) {
                                // Project managers (user_role 3) can see subprojects where they are project managers
                                $sql .= " AND (sp.project_managers_id = '$user_id' OR sp.verify_by = '$user_id' OR sp.assign_to_id = '$user_id')";
                            } 

                            $sql .= " ORDER BY sp.project_id DESC";

                            $info = $obj_admin->manage_all_info($sql);
                            $serial = 1;
                            $num_row = $info->rowCount();

                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No subprojects were found</td></tr>';
                            }

                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                            ?>
                                <tr>

                                    <td style="background-color: <?php echo $row['urgency']; ?>; text-align: center; width: 5%; border-radius: 5px; color: <?php echo ($row['urgency'] === 'white') ? '#000' : '#fff'; ?>">
                                        <?php echo $row['project_id']; ?>
                                        <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>
                                        <span class="badge badge-pill badge-danger"><?php echo 'S' . $row['subproject_status']; ?></span>

                                    </td>


                                    <td style="width: 30%;">
                                        <div data-toggle="modal" data-target=".bd-progress-modal-sm" href="javascript:void(0);" onclick="updateUrl(<?php echo $row['project_id']; ?>)" class="progress" style="height: 5px;" id="progress_bar">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $row['progress']; ?>%; height:5px;" aria-valuenow="<?php echo $row['progress']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $row['progress']; ?>%</div>
                                        </div>
                                        <script>
                                            function updateUrl(projectId) {
                                                // Parse the current URL's query parameters
                                                const urlParams = new URLSearchParams(window.location.search);

                                                // Set the 'progress_id' parameter with the specified projectId
                                                urlParams.set('table_id', projectId);

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
                                        <script>
                                            function filemanagerUrl(projectId) {
                                                // Parse the current URL's query parameters
                                                const urlParams = new URLSearchParams(window.location.search);

                                                // Replace or add the 'file_project_id' parameter with the given projectId
                                                urlParams.set('file_project_id', projectId);

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

                                        <?php echo $row['subproject_name']; ?>
                                    <td><?php echo $row['customer_id']; ?></td>
                                    <td hidden>
                                        <?php echo $row['p_team']; ?>
                                    </td>

                                    </td>
                                    <td style="width: 8%; <?php echo ($row['project_manager_status'] == 1) ? 'border:  solid orange;' : '';
                                                            echo ($row['project_manager_status'] == 3) ? 'background-color: purple; color: white;' : ''; ?>">

                                        <?php
                                        $projectManager = $row['project_manager'];
                                        $nameParts = explode(' ', $projectManager);

                                        // Ensure we have at least one name part
                                        if (count($nameParts) >= 1) {
                                            $firstName = $nameParts[0];
                                            echo $firstName;
                                        }

                                        // Ensure we have at least two name parts for the last name
                                        if (count($nameParts) >= 2) {
                                            $firstLetterLastName = substr($nameParts[1], 0, 1);
                                            echo " " . $firstLetterLastName . ".";
                                        }
                                        ?>
                                    </td>


                                    <td <?php echo ($row['assign_status'] == 1) ? 'style="border:orange solid; color:white;"' : ''; ?>>
                                        <?php
                                        $assignTo = $row['assign_to'];
                                        $nameParts = explode(' ', $assignTo);

                                        // Ensure we have at least one name part
                                        if (count($nameParts) >= 1) {
                                            $firstName = $nameParts[0];
                                            echo $firstName;
                                        }

                                        // Ensure we have at least two name parts for the last name
                                        if (count($nameParts) >= 2) {
                                            $firstLetterLastName = substr($nameParts[1], 0, 1);
                                            echo " " . $firstLetterLastName . ".";
                                        }
                                        ?>
                                    </td>

                                    <td <?php echo (empty($row['verify_by_name']) || $row['verify_by'] != $user_id) ? '' : 'style="background-color: orange; color:white;"'; ?> <?php echo ($row['checker_status'] == 1) ? 'style="border:orange solid; color:white;"' : ''; ?>>
                                        <?php echo empty($row['verify_by_name']) ? 'N/A' : $row['verify_by_name']; ?>
                                    </td>
                                    <td><?php echo $row['EPT']; ?></td>

                                    <!-- <td style="width: 10%;"><?php echo $row['end_date']; ?></td> -->
                                    <td style="width: 10%;" id="change_date" style="cursor: pointer; " data-toggle="modal" data-target="#change_date" data-project-id="<?php echo $row['project_id']; ?>" onclick="updateUrl(<?php echo $row['project_id']; ?>)">
                                        <?php echo $row['end_date']; ?> </td>

                                    <div class=" modal fade bd-date-modal-sm" id="change_date" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

                                        <div class="modal-dialog modal-sm modal-dialog-centered">

                                            <div class="modal-content">

                                                <div class="form-container mt-2 m-2">

                                                    <form class="" action="" method="post" enctype="multipart/form-data">

                                                        <label class="control-label text-dark" for="">Extend Estimated Date</label>

                                                        <input type="date" name="change_end_date_value" id="change_end_date_value" class="form-control" value="<?php echo date('Y-m-d', strtotime($row['end_date'])); ?>" required>


                                                        <button class="btn btn-primary mt-2" name="change_end_date">Submit</button>

                                                    </form>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    <td style="width: 20%;">
                                        
                                        <a title="Upload DATA's" href="engineeringData_sub_project.php?table_id=<?php echo $row['table_id']; ?>" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-paperclip" viewBox="0 0 16 16">
                                                <path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0V3z" />
                                            </svg></a>&nbsp;&nbsp;

                                        <?php if ($user_role == 1) { ?>
                                            <a title="Update Project Details" href="edit-subproject.php?table_id=<?php echo $row['table_id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                                                    <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001zm-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708l-1.585-1.585z" />
                                                </svg></a>&nbsp;&nbsp;
                                        <?php } ?>

                                        <a title="View" class="view-project" href="task-details_sub_project.php?table_id=<?php echo $row['table_id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                                <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                            </svg>
                                        </a>&nbsp;&nbsp;

                                        <?php if ($user_role == 1 or $user_role == 3) { ?>
                                            <a title="Delete" href="?delete_table_id=<?php echo $row['table_id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                                                </svg></a>&nbsp;&nbsp;
                                        <?php } ?>
                                        <?php if (($row['assign_to_id'] == $user_id) && ($row['verify_by'] != $user_id) || ($row['project_manager_status'] = 0)) { ?>
                                            <a title="Verify" href="process_data-check_sub_project.php?table_id=<?php echo $row['table_id']; ?>" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-square-fill" viewBox="0 0 16 16">
                                                    <path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z" />
                                                </svg>
                                            </a>&nbsp;&nbsp;
                                        <?php } ?>

                                        <?php if (($user_role == 1 || $user_role == 3) && ($row['assign_to_status'] == 3)) { ?>
                                            <a title="Assign" name="assign_to_status" href="" type="button" onclick="updateUrl(<?php echo $row['table_id']; ?>)" data-target="#assign_to_status" data-toggle="modal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                                                    <path d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z" />
                                                </svg>
                                            </a>&nbsp;&nbsp; <?php } ?>

                                        <!-- <?php if ($user_role == 1 or $user_role == 3) { ?>
                        <a title="Deliverable" href="process_data-delivery.php?project_id=<?php echo $row['project_id']; ?>" type="button">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-square-fill" viewBox="0 0 16 16">
                            <path d="M2 16a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2zm6.5-4.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 1 0z" />
                          </svg>
                        </a>
                      <?php  } ?> -->
                                        <?php if (($user_role == 1 || $user_role == 3)) { ?>
                                            <!-- <a title="Deliverable" name="filemanager" type="button" href="" type="button" onclick="filemanagerUrl(<?php echo $row['project_id']; ?>)" data-target="#filemanager" data-toggle="modal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-square-fill" viewBox="0 0 16 16">
                                                    <path d="M2 16a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2zm6.5-4.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 1 0z" />
                                                </svg>
                                            </a> -->
                                        <?php } ?>

                                        <?php if ($user_role == 2 && ($row['checker_status'] == 1)) { ?>
                                            <a title="Remove Checker" href="?table_id=<?php echo $row['table_id']; ?>" name="send_back">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-square-fill" viewBox="0 0 16 16">
                                                    <path d="M16 14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12zm-4.5-6.5H5.707l2.147-2.146a.5.5 0 1 0-.708-.708l-3 3a.5.5 0 0 0 0 .708l3 3a.5.5 0 0 0 .708-.708L5.707 8.5H11.5a.5.5 0 0 0 0-1z" />
                                                </svg>
                                            </a>&nbsp;&nbsp;
                                            <a title="Send to Project Manager" href="send_to_project_manager_sub_project.php?table_id=<?php echo $row['table_id']; ?>" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-square-fill" viewBox="0 0 16 16">
                                                    <path d="M0 14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v12zm4.5-6.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5a.5.5 0 0 1 0-1z" />
                                                </svg>
                                            </a>
                                        <?php  } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>

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

    <!-- Send To Engineer  -->
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
                    <h5 class="modal-title" id="exampleModalLongTitle">Select files for Deliverables</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">

                        <center><button style="text-decoration: none;" href="" type="submit" name="file_manager_deliverables" class="dashed-button p-5 w-100">Open project files</button></center>
                    </div>
                    <div class="modal-footer">
                        <button href="" class="btn btn-primary " name="send_to_deliverables">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- SELECT FILES BEFORE DELIVARABLE -->



    <?php
    include 'conn.php';

    if (isset($_POST["file_manager_deliverables"]) && isset($_GET["file_project_id"])) {
        $project_id = $_GET["file_project_id"]; // Assuming it's in the URL
        header('Location: filemanager.php?file_project_id=' . $project_id);
        exit; // It's a good practice to exit after a header redirect
    }
    
    if (isset($_POST["send_to_deliverables"]) && isset($_GET["file_project_id"])) {
        $project_id = $_GET["file_project_id"]; // Assuming it's in the URL
        header('Location: process_data-delivery.php?project_id=' . $project_id);
        exit; // It's a good practice to exit after a header redirect
    }


    if (isset($_POST["assign_to_status"]) && isset($_GET["table_id"])) {
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
            header('location:'.$_SERVER['PHP_SELF']);
        } else {
            $msg_error = "Error: " . $conn->error;
        }
    }

    //change_end_date update 
    if (isset($_POST["change_end_date"]) && isset($_GET["table_id"])) {
        // Get the progress value from the form
        $change_end_date_value = $_POST['change_end_date_value'];
        $t_end_date = date("Y-m-d", strtotime($change_end_date_value));

        // Get the project ID from the URL parameter or form field
        $table_id = $_GET["table_id"]; // Assuming it's in the URL

        $sql = "UPDATE subprojects SET end_date = ? WHERE table_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $t_end_date, $table_id); // Use "si" for a string and an integer

        if ($stmt->execute()) {
            $msg_success = "Status Updated";
            header('Location: subproject.php');
        } else {
            $msg_error = "Error: " . $stmt->error;
        }
    }

    if (isset($_POST["progress_input_data"]) && isset($_GET["table_id"])) {
        // Get the progress value from the form
        $progress_status = $_POST['progress_data_number'];

        // Get the project ID from the URL parameter or form field
        $table_id = $_GET["table_id"]; // Assuming it's in the URL

        // You can add additional validation and sanitation here

        // SQL query to update the 'progress' column of the 'projects' table for a specific project
        $sql = "UPDATE subprojects SET progress = ? WHERE table_id = ?";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $progress_status, $table_id);

        if ($stmt->execute()) {
            $msg_success = "Status Updated";
            header('location:subproject.php');
        } else {
            $msg_error = "Error: " . $conn->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
    ?>






    <!-- end  -->




    <!-- End of Main Content -->
    <!-- Send back to user if project not verified  -->
    <?php
    include 'conn.php';


    if (isset($_GET['table_id'])) {
        // Get the project ID from the URL parameter
        $table_id = $_GET["table_id"];

        // You can add additional validation and sanitation here

        // Get other data from the URL or from wherever you want
        $status_value = "0";
        $assign_status = 1;
        $checker_status = 0;


        // SQL query to insert data into the table
        $sql = "UPDATE subprojects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL ,assign_status = ?, checker_status = ? WHERE table_id = ?";
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $status_value, $assign_status, $checker_status, $table_id);

        if ($stmt->execute()) {
            $msg_success = "Data Sent  successfully for Rework!";
            header('location:subproject.php');
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
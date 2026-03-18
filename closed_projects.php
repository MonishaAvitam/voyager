



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
<?php include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php'; ?>



<!-- dashboard content  -->

<div class="container-fluid">

  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Closed Project</h1>

    <div class="d-flex align-items-center">

    </div>


  </div>

  <?php


  $branchCode = isset($_SESSION['branch_choice']) ? $_SESSION['branch_choice'] : null;
  ?>




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
          <h6 class="m-0 font-weight-bold text-primary">Closed Projects:

            <?php
            $branchCode = isset($_SESSION['branch_choice']) ? $_SESSION['branch_choice'] : "ALL-BRANCHES";
            if ($branchCode === 'SYD01') {
              echo 'Sydney (SYD01) ';
          

              } else if ($branchCode === 'BRI01') {
                  echo 'Brisbane (BRI01) '; 

                 

                      } else if (!$branchCode || $branchCode === 'ALL-BRANCHES') {
                          echo 'All Branches'; 
  
                          } 
            ?>
          </h6>
        </div>
        <div class="">
          <button type="button" class="btn btn-primary float-right" id="filter-icon" data-toggle="modal" data-target="#filter-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
              <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z" />
            </svg>
            Filter
          </button>
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
<!-- Filter by Project ID -->
            <label for="project-id-filter" class="text-primary">Filter by Project ID:</label>
            <input type="number" id="project-id-filter" class="form-control" placeholder="Enter Project ID">

            <!-- Filter by Engineer -->
            <label for="engineer-filter" class="text-primary mt-3">Filter by Engineer:</label>
            <select id="engineer-filter" class="form-control">
              <option value="">Select Engineer</option>
              <!-- Engineer options will be populated dynamically here -->
              <?php
              // Assuming you have a valid connection to the database
              require './conn.php';

              // Fetch unique engineers from the `projects` table
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
               <!-- Filter by Customer Id -->
            <label for="customer-id-filter" class="text-primary mt-3">Filter by Customer:</label>
            <select id="customer-id-filter" class="form-control">
              <option value="">Select Customer</option>
              <!-- Customer options will be populated dynamically here -->
              <?php
              require './conn.php';

              // Fetch unique contact_ids and customer_names by joining the 'projects' and 'contacts' tables
              $sql_customers = "SELECT DISTINCT p.contact_id, c.customer_name, customer_id 
                      FROM projects p
                      JOIN contacts c ON p.contact_id = c.contact_id
                      WHERE p.contact_id IS NOT NULL";

              $result_customers = $conn->query($sql_customers);

              // Create a dropdown option for each customer
              if ($result_customers->num_rows > 0) {
                while ($row = $result_customers->fetch_assoc()) {
                  echo '<option value="' . $row['customer_id'] . '">' . $row['customer_name'] . '</option>';
                }
              } else {
                echo '<option value="">No Customers Available</option>';
              }
              ?>
            </select>
                  <label for="team-filter" class="text-primary">Filter by Team:</label>
                  <select id="team-filter" class="form-control">
                    <option value="">All</option>
                    <option value="Building">Building Team</option>
                    <option value="Industrial">Industrial Team</option>
                  </select>
                  <!-- <label for="branch-filter" class="text-primary">Filter by Branch Code:</label>
                  <select id="branch-filter" class="form-control">
                    <option value="">All</option> -->
                    
                  </select>
 <label for="state-filter" class="text-primary mt-3">Filter by State</label>
            <select id="state-filter" class="form-control" name="state" required>
              <option value="">Select State</option>
              <Option value="QLD">QLD (Queensland)</Option>
              <Option value="NSW">NSW (New South Wales)</Option>
              <Option value="WA">WA (Western Australia)</Option>
              <Option value="SA">SA (South Australia)</Option>
              <Option value="VICTORIA">VICTORIA</Option>
              <Option value="N/A">Not Applicable</Option>
            </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="apply-filter">Apply Filter</button>
                </div>
              </div>
            </div>
          </div>

          <!-- <script>
            $(document).ready(function() {
              var dataTable = $('#dataTable').DataTable();

              // Apply filters when "Apply Filter" button is clicked
              document.getElementById("apply-filter").addEventListener("click", function() {
                // Get the selected filter values
                var selectedTeamFilter = document.getElementById("team-filter").value; // Fix here
                var selectedBranchFilter = document.getElementById("branch-filter").value;

                // Apply the team filter to the DataTable (assuming team filter is on column 4)
                dataTable.column(4).search(selectedTeamFilter).draw();

                // Apply the branch filter to the DataTable (assuming branch code filter is on column 7)
                dataTable.column(7).search(selectedBranchFilter).draw();

                // Close the filter modal
                $('#filter-modal').modal('hide');
              });
            });
          </script> -->




<!-- Toastify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>


<script>



$(document).ready(function () {
    var dataTable = $('#dataTable').DataTable();

    $("#filter-icon").on("click", function () {
        console.log("Filter button clicked");
        $("#filter-modal").modal("show");
        $("#filter-modal").removeAttr("aria-hidden");
    });

    $("#apply-filter").on("click", function () {
        console.log("Applying filters...");

        if (!$.fn.DataTable.isDataTable("#dataTable")) {
            console.log("DataTable not initialized!");
            return;
        }

        // Get selected filter values
        var projectIdFilter = $("#project-id-filter").val()?.trim() || "";
        var engineerFilter = $("#engineer-filter").val()?.trim() || "";
        var customerFilter = $("#customer-id-filter").val()?.trim() || "";
       
        var selectedBranchFilter = $("#branch-filter").val()?.trim() || "";
        var stateFilter = $("#state-filter").val()?.trim() || "";

        // Debugging logs
        console.log("Project ID:", projectIdFilter);
        console.log("Engineer:", engineerFilter);
        console.log("Customer:", customerFilter);
       
        console.log("Branch:", selectedBranchFilter);
        console.log("State:", stateFilter);

        // Clear all previous filters
        dataTable.search("").columns().search("");

        // Apply project ID filter (column index 0)
        if (projectIdFilter) {
    projectIdFilter = parseInt(projectIdFilter, 10); // Convert to integer
    dataTable.column(0).search(projectIdFilter, false, false);
}
        // Apply customer filter (column index 2)
        if (customerFilter) dataTable.column(2).search(customerFilter, false, false);

        // Apply state filter (column index 3)
        if (stateFilter) dataTable.column(5).search("^" + stateFilter + "$", true, false);

        // Apply engineer filter (column index 6)
        if (engineerFilter) dataTable.column(6).search(engineerFilter, false, false);


        // Apply Branch Code Filter (column index 8)
        if (selectedBranchFilter) {
            console.log("Filtering by Branch:", selectedBranchFilter);
            dataTable.column(8).search("^" + selectedBranchFilter + "$", true, false);
        }

        // Redraw table with applied filters
        setTimeout(() => {
            dataTable.draw();
            console.log("Filters applied.");

      // Show Toastify alert after filtering is successful
      Toastify({
                text: "The filters have been successfully applied!",
                duration: 2000, // Auto-close after 2 seconds
                close: true,
                gravity: "top", // Position: Top
                position: "end", // Align to the end (right side)
                backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)", // Custom styling
                stopOnFocus: true // Prevents dismissing on hover
            }).showToast();

        }, 200); // Ensure data is loaded before filtering

        // Close modal properly
        $("#filter-modal").modal("hide");
    });

    // Fix aria-hidden focus issue
    $("#filter-modal").on("hidden.bs.modal", function () {
        setTimeout(() => {
            $("#filter-icon").focus();
        }, 10);
    });
 
});



</script>








<!-- 


          <script>
$(document).ready(function () {
    var dataTable = $('#dataTable').DataTable();
                // Get the selected filter values
                var selectedTeamFilter = document.getElementById("team-filter").value; // Fix here
                var selectedBranchFilter = document.getElementById("branch-filter").value;

    if ($("#filter-icon").length) {
        $(document).on("click", "#filter-icon", function () {
            console.log("Filter button clicked");
            $("#filter-modal").modal("show");
            $("#filter-modal").removeAttr("aria-hidden");
        });
    }

    if ($("#apply-filter").length) {
        $("#apply-filter").on("click", function () {
            console.log("Applying filters...");

            // Get selected filter values
            var projectIdFilter = $("#project-id-filter").val()?.trim() || "";
            var engineerFilter = $("#engineer-filter").val()?.trim() || "";
            var customerFilter = $("#customer-id-filter").val()?.trim() || "";
            var teamFilter = $("#team-filter").val()?.trim() || "";
            var stateFilter = $("#state-filter").val()?.trim() || "";

            // Debugging logs
            console.log("Project ID:", projectIdFilter);
            console.log("Engineer:", engineerFilter);
            console.log("Customer:", customerFilter);
            console.log("Team:", teamFilter);
            console.log("State:", stateFilter);

            // Clear previous filters
            dataTable.search("").columns().search("").draw();

            // Apply filters based on selected values
            if (projectIdFilter) {
                dataTable.column(0).search("^" + projectIdFilter + "$", true, false);
            }

            if (customerFilter) {
                dataTable.column(2).search(customerFilter, false, false);
            }

            if (stateFilter) {
                dataTable.column(3).search("^" + stateFilter + "$", true, false);
            }

            if (teamFilter === "All") {
                console.log("Showing all teams");
                dataTable.column(7).search("", false, false); // Clear filter
            } else if (teamFilter) {
                console.log("Filtering by Team:", teamFilter);

                  // Apply the team filter to the DataTable (assuming team filter is on column 4)
                  dataTable.column(4).search(selectedTeamFilter).draw();

// Apply the branch filter to the DataTable (assuming branch code filter is on column 7)
dataTable.column(7).search(selectedBranchFilter).draw();

            }

            if (engineerFilter) {
                dataTable.column(6).search(engineerFilter, false, false);
            }

            // Redraw table after applying filters
            dataTable.draw();
            console.log("Filters applied.");

            // Close modal properly
            $("#filter-modal").modal("hide");
        });
    }

    // Ensure focus does not remain on hidden elements
    $("#filter-modal").on("hidden.bs.modal", function () {
        $("#filter-icon").focus();
    });
});
</script>




 -->




















          <!-- <script>
$(document).ready(function () {
    var dataTable = $('#dataTable').DataTable();

    if ($("#filter-icon").length) {
        $(document).on("click", "#filter-icon", function () {
            console.log("Filter button clicked");
            $("#filter-modal").modal("show");
            $("#filter-modal").removeAttr("aria-hidden");
        });
    }

    if ($("#apply-filter").length) {
        $("#apply-filter").on("click", function () {
            console.log("Applying filters...");

            // Get selected filter values
            var projectIdFilter = $("#project-id-filter").val()?.trim() || "";
            var engineerFilter = $("#engineer-filter").val()?.trim() || "";
            var customerFilter = $("#customer-id-filter").val()?.trim() || "";
            var teamFilter = $("#team-filter").val()?.trim() || "";
            var stateFilter = $("#state-filter").val()?.trim() || "";

            // Debugging logs
            console.log("Project ID:", projectIdFilter);
            console.log("Engineer:", engineerFilter);
            console.log("Customer:", customerFilter);
            console.log("Team:", teamFilter);
            console.log("State:", stateFilter);

            // Clear previous filters
            dataTable.search("").columns().search("").draw();

            // Apply filters based on selected values
            if (projectIdFilter) {
                console.log("Filtering by Project ID:", projectIdFilter);
                dataTable.column(0).search("^" + projectIdFilter + "$", true, false);
            }

            if (customerFilter) {
                console.log("Filtering by Customer:", customerFilter);
                dataTable.column(2).search(customerFilter, false, false);
            }

            if (stateFilter) {
                console.log("Filtering by State:", stateFilter);
                dataTable.column(3).search("^" + stateFilter + "$", true, false);
            }

            if (teamFilter === "All") {
                console.log("Showing all teams");
                dataTable.column(4).search("", false, false); // Clear filter
            } else if (teamFilter) {
                console.log("Filtering by Team:", teamFilter);
                dataTable.column(4).search("^" + teamFilter + "$", true, false); // Exact match for team
            }

            if (engineerFilter) {
                console.log("Filtering by Engineer:", engineerFilter);
                dataTable.column(6).search(engineerFilter, false, false);
            }

            // Redraw table after applying filters
            dataTable.draw();
            console.log("Filters applied.");

            // Close modal properly
            $("#filter-modal").modal("hide");
        });
    }

    // Ensure focus does not remain on hidden elements
    $("#filter-modal").on("hidden.bs.modal", function () {
        $("#filter-icon").focus();
    });
});
</script> -->



<!-- <script>


$(document).ready(function () {
    var dataTable = $('#dataTable').DataTable();

    if ($("#filter-icon").length) {
        $(document).on("click", "#filter-icon", function () {
            console.log("Filter button clicked");
            $("#filter-modal").modal("show");
            $("#filter-modal").removeAttr("aria-hidden");
        });
    }

    if ($("#apply-filter").length) {
        $("#apply-filter").on("click", function () {
            console.log("Applying filters...");

            // Get selected filter values
            var projectIdFilter = $("#project-id-filter").val()?.trim() || "";
            var engineerFilter = $("#engineer-filter").val()?.trim() || "";
            var customerFilter = $("#customer-id-filter").val()?.trim() || "";
            var teamFilter = $("#team-filter").val()?.trim() || "";
            var stateFilter = $("#state-filter").val()?.trim() || "";

            // Debugging log
            console.log("Project ID:", projectIdFilter);
            console.log("Engineer:", engineerFilter);
            console.log("Customer:", customerFilter);
            console.log("Team:", teamFilter);
            console.log("State:", stateFilter);

            // Clear previous filters
            dataTable.search("").columns().search("").draw();

            // Apply filters with correct indexes
            if (projectIdFilter) {
                console.log("Filtering by Project ID:", projectIdFilter);
                dataTable.column(0).search("^" + projectIdFilter + "$", true, false);
            }

            if (customerFilter) {
                console.log("Filtering by Customer:", customerFilter);
                dataTable.column(2).search("\\b" + customerFilter + "\\b", true, false);
            }

            if (stateFilter) {
                console.log("Filtering by State:", stateFilter);
                dataTable.column(3).search("^" + stateFilter + "$", true, false);
            }

            if (teamFilter) {
                console.log("Filtering by Team:", teamFilter);
                dataTable.column(5).search("^" + teamFilter + "$", true, false); // Exact match
            }

            if (engineerFilter) {
                console.log("Filtering by Engineer:", engineerFilter);
                dataTable.column(6).search(engineerFilter, false, false);
            }

            // Redraw table after applying filters
            dataTable.draw();
            console.log("Filters applied.");

            // Close modal properly
            $("#filter-modal").modal("hide");
        });
    }

    // Ensure focus does not remain on hidden elements
    $("#filter-modal").on("hidden.bs.modal", function () {
        $("#filter-icon").focus();
    });
});










</script> -->



















          

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


          <script>
            $(document).ready(function() {
              var dataTable = $('#dataTable').DataTable();

              document.getElementById("apply-filter").addEventListener("click", function() {
                // Get the selected filter value
                var selectedFilter = document.getElementById("team-filter").value;

                // Apply the filter to the DataTable
                dataTable.column(4).search(selectedFilter).draw();    // this is used for filter by team .......

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
                <th>State</th>
                <th>Engineer</th>
                <th>EPT</th>
                <th>Branch Code</th>
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
                <th>State</th>
                <th>Engineer</th>
                <th>EPT</th>
                <th>Branch Code</th>
                <th>Action</th>
              </tr>
            </tfoot>
            <tbody>

              <!-- ... Your table rows ... -->
              <?php

              if ($branchCode === "ALL-BRANCHES") {
                $branch_code_new = '';
              } else {
                $branch_code_new = $branchCode;
              }

              $sql = "
               SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team,p.state, p.branch_code, p.project_manager, p.assign_to, p.EPT, p.urgency, p.reopen_status,p.revision_project_id, p.subproject_status, p.contact_id, c.customer_name,p.end_date
               FROM projects p
               LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
               WHERE 
               (p.branch_code = '$branch_code_new' OR '$branch_code_new' = '') AND
               p.urgency = 'purple'
               
            

           
           ";


              //  UNION

              //  SELECT 'Deliverable' AS type, dd.project_id, dd.project_name, dd.project_details, dd.p_team, dd.project_manager, dd.assign_to, dd.EPT, dd.urgency, dd.reopen_status,NULL AS revision_project_id, NULL AS subproject_status, dd.contact_id, c.customer_name,dd.end_date
              //  FROM deliverable_data dd
              //  LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
              //  LEFT JOIN contacts c ON (dd.contact_id = c.contact_id)
              //                 WHERE 
              //                 dd.urgency = 'purple'




              $info = $obj_admin->manage_all_info($sql);
              $serial  = 1;
              $num_row = $info->rowCount();
              if ($num_row == 0) {
                echo '<tr><td colspan="7">No projects were found</td></tr>';
              }
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
              ?>
                <tr>


                  <td style=" background-color: <?php echo $row['urgency']; ?>; text-align: center; width: 5%; border-radius: 5px; color: <?php echo ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff'; ?>">
                    <?php echo !empty($row['revision_project_id']) ? $row['revision_project_id'] : $row['project_id']; ?>
                    <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>
                    <span class="badge badge-pill badge-danger"><?php if ($row['subproject_status'] != NULL) {
                                                                  echo 'S' . $row['subproject_status'];
                                                                } ?></span>

                  </td>

                  <td><?php echo $row['project_name']; ?></td>
                  <td><?php echo $row['customer_name']; ?></td>
                  <td><?php echo $row['project_manager']; ?></td>
                  <td><?php echo $row['p_team']; ?></td>
                  <td><?php echo $row['state']; ?></td>
                  <td><?php echo $row['assign_to']; ?></td>


                  <td><?php echo !empty($row['EPT']) ? $row['EPT'] : 'N/A'; ?></td>
                  
                  <td><?php echo $row['branch_code']; ?></td>

                  <td style="text-align: center;">

                    <a title="View" class="view-project" href="task-details.php?project_id=<?php echo $row['project_id']; ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                        <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                      </svg>
                    </a>&nbsp;&nbsp;

                    <?php if ($user_role == 1 or $user_role == 3 && $row['urgency'] == "purple") {  ?>
                      <!-- <a title="open" class="view-project" href="" onclick="updateUrl(<?php echo $row['project_id']; ?>)" data-target="#status_model" data-toggle="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-opencollective" viewBox="0 0 16 16">
                          <path fill-opacity=".4" d="M12.995 8.195c0 .937-.312 1.912-.78 2.693l1.99 1.99c.976-1.327 1.6-2.966 1.6-4.683 0-1.795-.624-3.434-1.561-4.76l-2.068 2.028c.468.781.78 1.679.78 2.732h.04Z" />
                          <path d="M8 13.151a4.995 4.995 0 1 1 0-9.99c1.015 0 1.951.273 2.732.82l1.95-2.03a7.805 7.805 0 1 0 .04 12.449l-1.951-2.03a5.072 5.072 0 0 1-2.732.781H8Z" />
                        </svg>
                      </a>&nbsp; -->

                    <?php  }  ?>

                    <?php if ($row['urgency'] == "purple") { ?>
                      <?php if ($user_role == 1 or $user_role == 3) { ?>
                        <!-- <a title="Delete" href="?delete_project_id_develirables=<?php echo $row['project_id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                          </svg></a>&nbsp; -->
                      <?php } ?>

                    <?php } else { ?>

                      <?php if ($row['subproject_status'] != NUll) {  ?>


                        <?php if ($user_role == 1 or $user_role == 3) { ?>
                          <!-- <a title="Delete" href="?delete_table_id=<?php echo $row['table_id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                            </svg></a>&nbsp; -->
                        <?php } ?>


                      <?php  } else { ?>

                        <?php if ($user_role == 1 or $user_role == 3 or $user_role == 2) { ?>
                          <!-- <a title="Delete" href="?delete_project_id=<?php echo $row['project_id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                              <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                            </svg></a>&nbsp; -->
                        <?php } ?>


                      <?php  } ?>


                    <?php } ?>

                    <?php if ($user_role == 1 or $user_role == 3) { ?>
                      <a title="Update Project Details" id="update-project-button" href="edit-project.php?project_id=<?php echo $row['project_id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16">
                          <path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001zm-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708l-1.585-1.585z" />
                        </svg></a>&nbsp;&nbsp;
                      <script>
                        document.getElementById("update-project-button").addEventListener("click", function() {
                          sessionStorage.setItem("lastVisitedURL", window.location.href);
                          console.log("Current URL stored:", window.location.href);
                        });
                      </script>

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
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: ../index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

if ($user_role == 4) {
  header('Location:./payslip.php');
}

include './include/sidebar.php';
?>



<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 ">Project Status View</h1>
  </div>

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

  <div class=" bg-white rounded  shadow mt-5 ">
    <!-- Rounded tabs -->
    <ul id="myTab" role="tablist" class="nav nav-tabs nav-pills flex-column flex-sm-row text-center bg-light border-0 rounded-nav">
      <li class="nav-item flex-sm-fill">
        <a id="Graphical-tab" data-toggle="tab" href="#Graphical" role="tab" aria-controls="Graphical" aria-selected="false" class="nav-link border-0 text-uppercase font-weight-bold">Status View</a>
      </li>

    </ul>
    <div id="myTabContent" class="tab-content">

      <div id="Graphical" role="tabpanel" aria-labelledby="Graphical-tab" class="tab-pane fade  py-5 show active ">
        <!-- DataTales Example -->

        <script>
          $(document).ready(function() {
            var dataTable = $('#graphicalTable').DataTable({
              ordering: false // Disable sorting
            });

            document.getElementById("apply-filter").addEventListener("click", function() {
              // Get the selected filter value
              var selectedFilter = document.getElementById("team-filter").value;

              // Apply the filter to the DataTable
              dataTable.column(4).search(selectedFilter).draw();

              // Close the filter modal
              $('#filter-modal').modal('hide');
            });
          });
        </script>

        <div class="table-responsive p-3  ">
          <table id="graphicalTable" class="table  table-sm" style="width:100%">
            <thead>
              <tr>
                <th></th>
              </tr>
            </thead>

            <?php
            $sql = "
                  SELECT type, project_id, start_date, project_manager_status, project_name, urgency, assign_to_status, checker_status
                  FROM (
                      SELECT 'Project' AS type, p.project_id, p.start_date, p.project_manager_status, p.project_name, p.urgency, p.assign_to_status, p.checker_status
                      FROM projects p 
                      UNION
                      SELECT 'Deliverable' AS type, dd.project_id, dd.start_date, dd.project_manager_status, dd.project_name, dd.urgency, dd.assign_to_status, dd.checker_status
                      FROM deliverable_data dd 
                  ) AS combined
                  GROUP BY type, project_id, start_date, project_manager_status, project_name, urgency, assign_to_status, checker_status
                  ORDER BY start_date DESC;
              ";


            $info = $obj_admin->manage_all_info($sql);
            $serial = 1;
            $num_row = $info->rowCount();

            if ($num_row == 0) {
              echo '<tr><td colspan="7">No projects were found</td></tr>';
            } else {
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                $bg_color_p1 = $bg_color_p2 = $bg_color_p3 = $bg_color_p4 = $bg_color_p5 = 'bg-dark';

                if ($row['project_manager_status'] == 1 && $row['urgency'] != 'purple') {
                  $bg_color_p1 = 'bg-success';
                } elseif ($row['assign_to_status'] == 1 && $row['checker_status'] == 0 && $row['urgency'] != 'purple') {
                  $bg_color_p1 = $bg_color_p2 = 'bg-success';
                } elseif ($row['project_manager_status'] == 3 && $row['urgency'] != 'purple') {
                  $bg_color_p1 = $bg_color_p2 = $bg_color_p3 = $bg_color_p4 = 'bg-success';
                } elseif ($row['checker_status'] == 1 && $row['urgency'] != 'purple') {
                  $bg_color_p1 = $bg_color_p2 = $bg_color_p3 = 'bg-success';
                } elseif ($row['urgency'] == 'purple') {
                  $bg_color_p1 = $bg_color_p2 = $bg_color_p3 = $bg_color_p4 = $bg_color_p5  = 'bg-success';
                }
                // Check if the project_id is in deliverable_data table
                $checkDeliverableQuery = "SELECT COUNT(*) AS count FROM deliverable_data WHERE project_id = ?";
                $stmt = $conn->prepare($checkDeliverableQuery);
                $stmt->bind_param('i', $row['project_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $rowCount = $result->fetch_assoc()['count'];

                if ($rowCount > 0) {
                  $bg_color_p1 = $bg_color_p2 = $bg_color_p3 = $bg_color_p4 = $bg_color_p5  = 'bg-success';
                }

                $project_id = $row['project_id'];

                // Default button color (blue)
                $button_color = "btn-primary";
                $invoiceStatus = "Un Invoiced";

                // Check if the project_id is in csa_finance_invoiced
                $sql_invoiced = "SELECT 1 FROM csa_finance_invoiced WHERE project_id = ?";
                $stmt_invoiced = $conn->prepare($sql_invoiced);
                $stmt_invoiced->bind_param("i", $project_id);
                $stmt_invoiced->execute();
                $stmt_invoiced->store_result();

                if ($stmt_invoiced->num_rows > 0) {
                  // If project_id exists in csa_finance_invoiced, set color to green
                  $button_color = "btn-success";
                  $invoiceStatus = "Invoiced";
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
                    $invoiceStatus = "Targeted";
                  }
                  $stmt_ready->close();
                }

            ?>
                <tr>
                  <td>

                    <a class="text-decoration-none" href="task-details.php?project_id=<?php echo $row['project_id']; ?>">
                      <div class="container card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                          <label for="">Project Number <span class="text-primary ml-2">#<?php echo $row['project_id']; ?></span></label>
                          <label for="">Project Created Date : <span class="text-primary ml-2"><?php echo $row['start_date']; ?></span></label>
                          <label for=""><?php echo $row['project_name']; ?></label>
                        </div>
                        <div class="card-body">
                          <div class="d-flex justify-content-around align-items-center">
                            <label class="font-weight-bold <?php echo $bg_color_p1; ?> text-light p-2 rounded">Project Created</label>
                            <img src="https://img.icons8.com/offices/100/000000/double-right.png" width="20" height="20">
                            <label class="font-weight-bold <?php echo $bg_color_p2; ?> text-light p-2 rounded">Project Assigned</label>
                            <img src="https://img.icons8.com/offices/100/000000/double-right.png" width="20" height="20">
                            <label class="font-weight-bold <?php echo $bg_color_p3; ?> text-light p-2 rounded">Checker Assigned</label>
                            <img src="https://img.icons8.com/offices/100/000000/double-right.png" width="20" height="20">
                            <label class="font-weight-bold <?php echo $bg_color_p4; ?> text-light p-2 rounded">Project Ready</label>
                            <img src="https://img.icons8.com/offices/100/000000/double-right.png" width="20" height="20">
                            <label class="font-weight-bold <?php echo $bg_color_p5; ?> text-light p-2 rounded">Project is Closed</label>
                            <img src="https://img.icons8.com/offices/100/000000/double-right.png" width="20" height="20">
                            <label class="font-weight-bold <?php echo $button_color; ?> text-light p-2 rounded"><?php echo $invoiceStatus ?></label>
                          </div>
                        </div>
                      </div>
                    </a>
                  </td>
                </tr>
            <?php
              }
            }
            ?>
          </table>
        </div>
      </div>
      <!-- End rounded tabs -->
    </div>
  </div>


  <?php
  include 'include/footer.php';
  ?>
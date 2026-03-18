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

<head>
  <!-- CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

  <!-- JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>


</head>


<!-- dashboard content  -->

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Accounts Receivable</h1>
  </div>

  <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Filter by Customer Name</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <br>
        <div class="form-group">
          <label for="customer_name">Customer Name:</label>
          <select class="form-control mt-2" id="customer_name" name="customer_name" style="width: 70%">
            <option value="">Select customer</option>
            <?php
            // Assuming $conn is your database connection object
            $sql = "SELECT * FROM contacts";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['customer_name'] . "'>" . $row['customer_name'] . "</option>";
              }
            } else {
              echo "<option value=''>No customers found</option>";
            }
            ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="applyFilter">Apply Filter</button>
        </div>
      </div>
    </div>
  </div>



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
          <select id="team-filter" name="team-filter" class="form-control">
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

  <div class="card mt-5 mb-5 rounded">

    <ul class="nav nav-pills nav-fill ">
      <li class="nav-item">
        <a class="nav-link " aria-current="page" href="unInvoiced.php">Projects</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="unInvoicedSubProjects.php">Sub Projects</a>
      </li>
    </ul>
  </div>


  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Un-Invoiced Sub Projects</h6>
      <div class="d-flex">
        <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#filterModal">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
            <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z" />
          </svg>
        </button>
        <a href="#" class="float-right" id="filter-icon" data-toggle="modal" data-target="#filter-modal">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
          </svg>
        </a>
      </div>
    </div>

    <div class="card-body">
      <div class="table-responsive p-3">
        <table class="table table-striped table-bordered table-sm" id="dataTable" name="dataTable">
          <thead>
            <tr>
              <th>Project Number</th>
              <th>Project Title</th>
              <th>Customer Name</th>
              <th>Team</th>
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>Project Number</th>
              <th>Project Title</th>
              <th>Customer Name</th>
              <th>Team</th>
              <th>Action</th>
            </tr>
          </tfoot>
          <tbody>

            <!-- ... Your table rows ... -->
            <?php


            $sql = "SELECT sp.*, pm.fullname AS subproject_manager_name, c.contact_name, c.contact_email, c.contact_phone_number, c.contact_id, c.customer_id
              FROM subprojects sp
              LEFT JOIN project_managers pm ON (sp.project_manager = pm.fullname)
              LEFT JOIN contacts c ON (sp.contact_id = c.contact_id)
              WHERE  sp.setTarget IS NULL  
              AND NOT EXISTS (
              SELECT 1 
              FROM csa_finance_readytobeinvoiced r
              WHERE r.project_id = sp.project_id 
              AND r.subproject_status = sp.subproject_status ); ";


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
                  <?php echo $row['project_id']; ?>
                  <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>
                  <span class="badge badge-pill badge-danger"><?php if ($row['subproject_status'] != NULL) {
                                                                echo 'S' . $row['subproject_status'];
                                                              } ?></span>

                </td>


                <td>
                  <input type="text" name="project_name" value="<?php if ($row['subproject_status'] != Null) {
                                                                  echo $row['subproject_name'];
                                                                } ?>" style="border: none; background-color:transparent; color:black; outline: none;" readonly>
                </td>
                <td>
                  <input type="text" name="customer_name" value="<?php echo $row['customer_id']; ?>" style="border: none; background-color:transparent; color:black;  outline: none;" readonly>
                </td>

                <td>
                  <input type="text" name="p_team" value="<?php echo $row['p_team']; ?>" style="border: none; background-color: transparent; color: black; outline: none;" readonly>
                </td>







                <td class="d-flex justify-content-around">
                  <form id="readyToBeInvoicedForm" method="post">
                    <input name="project_id" type="text" value="<?php echo $row['project_id']; ?>" hidden>
                    <input name="subproject_status" type="text" value="<?php echo $row['subproject_status']; ?>" hidden>
                    <button type="submit" name="readyToBeInvoiced" class="ml-2 btn btn-outline-primary" onclick="return confirm('Are you sure you want to set this project to Ready to Invoice status?');">Ready To Invoice</button>
                  </form>

                  <button onclick="fetch_project_id(<?php echo $row['project_id']; ?>)" class="btn btn-danger btn-sm ">Set Target</button>
                  <a title="View" class="view-project" href="../task-details.php?project_id=<?php echo $row['project_id']; ?>">
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
  function fetch_project_id(project_id) {
    // Redirect to the URL with the project ID
    const newurl = window.location.pathname + "?project_id=" + project_id;
    history.pushState(null, null, newurl);
    showmodal();

  }

  function showmodal() {
    // Show the Bootstrap modal
    $('#setTargetmodal').modal('show');
  }
</script>

<!-- set target modal  -->

<div class="modal fade " id="setTargetmodal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

  <div class="modal-dialog modal-sm modal-dialog-centered">

    <div class="modal-content">

      <div class="form-container mt-2 m-2">

        <form class="" action="" method="post" enctype="multipart/form-data">
          <label class="control-label text-dark" for="">Target Date</label>
          <input class="form-control" type="date" name="targetdate" id="targetdate">
          <button class="btn btn-primary mt-2" name="setTarget">Submit</button>
        </form>

      </div>

    </div>

  </div>

</div>

<script>
  // Function to filter table based on selected team
  function filterTableByTeam() {
    var selectedTeam = document.getElementById('team-filter').value;
    var table = document.getElementById('dataTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 0; i < rows.length; i++) {
      var teamInput = rows[i].querySelector('input[name="p_team"]');
      if (teamInput) {
        var teamName = teamInput.value;
        if (selectedTeam === '' || teamName === selectedTeam) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    }
  }

  // Event listener for dropdown change
  document.getElementById('team-filter').addEventListener('change', filterTableByTeam);

  // Event listener for the "Apply Filter" button
  document.getElementById('apply-filter').addEventListener('click', filterTableByTeam);
</script>


<script>
  $(document).ready(function() {
    $('#customer_name').select2();
  });
</script>


<script>
  // Function to filter table based on selected customer name
  function filterTableByCustomer() {
    var selectedCustomer = document.getElementById('customer_name').value;
    var table = document.getElementById('dataTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 0; i < rows.length; i++) {
      var customerCell = rows[i].getElementsByTagName('td')[2];
      if (customerCell) {
        var customerName = customerCell.querySelector('input[name="customer_name"]').value;
        if (selectedCustomer === '' || customerName === selectedCustomer) {
          rows[i].style.display = '';
        } else {
          rows[i].style.display = 'none';
        }
      }
    }
  }

  // Event listener for dropdown change
  document.getElementById('customer_name').addEventListener('change', filterTableByCustomer);

  // Event listener for the "Apply Filter" button
  document.getElementById('applyFilter').addEventListener('click', filterTableByCustomer);
</script>



<?php
if (isset($_POST["readyToBeInvoiced"])) {

  $project_id = $_POST['project_id'];
  $subproject_status = $_POST['subproject_status']; // e.g., "S1"
  $currentDateTime = date('Y-m-d H:i:s');

  // Prepare the SQL statement
  $stmt = $conn->prepare("INSERT INTO csa_finance_readytobeinvoiced (project_id, subproject_status, date) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $project_id, $subproject_status, $currentDateTime);

  // Execute the statement
  if ($stmt->execute()) {
      // Redirect on success
      header('Location: readyToBeInvoiced.php');
      exit(); // Exit after redirection to stop further script execution
  } else {
      echo "Error: " . $stmt->error;
  }

  // Close the statement
  $stmt->close();
}




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["setTarget"])) {

  // Ensure proper sanitization of project_id
  $project_id = $_GET['project_id'];
  // Assuming setTarget is a string, assign it directly
  $setTarget = "Active";
  // Assuming targetdate is coming from a form field using POST method
  $setTargetDate = $_POST['targetdate'];

  // Prepare the statement for updating projects
  $stmt1 = $conn->prepare("UPDATE subprojects
    SET setTarget = ?, setTargetDate = ?
    WHERE project_id = ?");

  // Check if statement preparation was successful
  if (!$stmt1) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
  }

  // Bind parameters for updating projects
  $stmt1->bind_param("ssi", $setTarget, $setTargetDate, $project_id);

  // Execute the statement for updating projects
  $stmt1->execute();

  // Close the statement for updating projects
  $stmt1->close();

  // Prepare the statement for updating deliverable_data
  $stmt2 = $conn->prepare("UPDATE deliverable_data
    SET setTarget = ?, setTargetDate = ?
    WHERE project_id = ?");

  // Check if statement preparation was successful
  if (!$stmt2) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
  }

  // Bind parameters for updating deliverable_data
  $stmt2->bind_param("ssi", $setTarget, $setTargetDate, $project_id);

  // Execute the statement for updating deliverable_data
  $stmt2->execute();

  // Close the statement for updating deliverable_data
  $stmt2->close();

  // Redirect after successful update
  header('Location: targetProjects.php');
  exit(); // Make sure to exit after redirection
}





?>


<?php
include './include/footer.php';
?>
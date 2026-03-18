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

include './include/sidebar.php';

?>




<!-- dashboard content  -->

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Targeted Projects</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Targeted Projects</h6>
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
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm" id="dataTable">
                    <thead>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Customer Name</th>
                            <th>Team</th>
                            <th>Target Date</th>

                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Customer Name</th>
                            <th>Team</th>
                            <th>Target Date</th>

                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>

                        <!-- ... Your table rows ... -->
                        <?php
                        $sql = "SELECT 
    dd.project_id,
    dd.urgency,
    dd.reopen_status,
    dd.project_name,
    dd.p_team,
    dd.setTarget,
    dd.setTargetDate,
    c.customer_name 
FROM 
    deliverable_data dd 
LEFT JOIN 
    contacts c ON (dd.contact_id = c.contact_id)

WHERE  dd.setTarget = 'Active' AND  dd.project_id NOT IN (SELECT project_id FROM csa_finance_invoiced) 
  AND dd.project_id NOT IN (SELECT project_id FROM csa_finance_readytobeinvoiced) 


UNION ALL
SELECT 
    p.project_id,
    p.urgency,
    p.reopen_status,
    p.project_name,
    p.p_team,
    p.setTarget,
    p.setTargetDate,
    c.customer_name 
FROM 
    projects p 
LEFT JOIN 
    contacts c ON p.contact_id = c.contact_id
WHERE  
    p.setTarget = 'Active' AND p.project_id NOT IN (SELECT project_id FROM csa_finance_invoiced) 
  AND p.project_id NOT IN (SELECT project_id FROM csa_finance_readytobeinvoiced) ;



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
                                    $backgroundColor = $row['urgency'];
                                }
                                $color = ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff';
                                ?>

                                <td style="background-color: <?php echo $backgroundColor; ?>; text-align: center; width: 5%; border-radius: 5px; color: <?php echo $color; ?>">
                                    <?php echo $row['project_id']; ?>
                                    <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>

                                </td>



                                <td>
                                    <input type="text" name="project_name" value="<?php echo $row['project_name']; ?>" style="border: none; background-color:transparent; outline: none; display:none">
                                    <label for=""><?php echo $row['project_name']; ?></label>
                                </td>
                                <td>
                                    <input type="text" name="customer_name" value="<?php echo $row['customer_name']; ?>" style="border: none; background-color:transparent;   outline: none; display:none">
                                    <label for=""><?php echo $row['customer_name']; ?></label>
                                </td>

                                <td>
                                    <input type="text" name="p_team" value="<?php echo $row['p_team']; ?>" style="border: none; background-color: transparent; color: aliceblue; outline: none;display:none">
                                    <label for=""><?php echo $row['p_team']; ?></label>
                                </td>

                                <td>
                                    <p>
                                        <?php echo $row['setTargetDate']; ?>
                                    </p>
                                </td>






                                <td class="d-flex justify-content-around align-items-center">
                                    <a title="SEND BACK" href="?delete_project_id=<?php echo $row['project_id']; ?>" onclick="return confirm('Are you sure you want to Remove this project from Targeted table ?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-skip-backward-fill" viewBox="0 0 16 16">
                                            <path d="M.5 3.5A.5.5 0 0 0 0 4v8a.5.5 0 0 0 1 0V8.753l6.267 3.636c.54.313 1.233-.066 1.233-.697v-2.94l6.267 3.636c.54.314 1.233-.065 1.233-.696V4.308c0-.63-.693-1.01-1.233-.696L8.5 7.248v-2.94c0-.63-.692-1.01-1.233-.696L1 7.248V4a.5.5 0 0 0-.5-.5" />
                                        </svg></a>&nbsp;&nbsp;

                                    <a onclick="fetch_project_id(<?php echo $row['project_id']; ?>)" class="text-primary  ml-2"><i class="fas fa-calendar-alt"></i></a>
                                    <form id="readyToBeInvoicedForm" method="post">
                                        <input name="project_id" type="text" value="<?php echo $row['project_id']; ?>" hidden>
                                        <button type="submit" name="readyToBeInvoiced" class="ml-2 btn btn-outline-primary">Ready To Invoice</button>
                                    </form>&nbsp;&nbsp;


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
        showmodalTarget();

    }

    function showmodalTarget() {
        // Show the Bootstrap modal
        $('#setTargetmodal').modal('show');
    }


    function showmodalInvoice() {
        // Show the Bootstrap modal
        $('#readyToBeInvoiced').modal('show');
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


<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter by Customer Name</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="form-group">
                <label for="customer_name">Customer Name:</label>
                <select class="form-control mt-2" id="customer_name" style="width: 100%;">
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

            </div>
        </div>
    </div>
</div>

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
            var customerCell = rows[i].getElementsByTagName('td')[2]; // Assuming customer name is in the third column (index 2)
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
                    <option value="Industrial Team">Industrial Team</option>
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
    header('Location:' . $_SERVER['HTTP_REFERER']);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["invoice"])) {

    $invoice_number = $_POST['invoice_number'];
    $comments = $_POST['comments'];
    $price = $_POST['price'];
    $project_name = $_POST['project_name'];
    $project_id = $_POST['project_id'];
    $p_team = $_POST['p_team'];
    $customer_name = $_POST['customer_name'];
    $currentDate = date('d/F/y');


    $stmt = $conn->prepare("INSERT INTO csa_finance_invoiced (project_id,project_title,comments,amount,p_team,customer_name ,invoice_number,month) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $project_id, $project_name, $comments, $price, $p_team, $customer_name, $invoice_number, $currentDate);

    // Execute the statement
    $stmt->execute();

    // Close the statement and connection
    $stmt->close();

    header('Location:dashboard.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["setTarget"])) {

    // Ensure proper sanitization of project_id
    $project_id = $_GET['project_id'];
    // Assuming setTarget is a string, assign it directly
    $setTarget = "Active";
    // Assuming targetdate is coming from a form field using POST method
    $setTargetDate = $_POST['targetdate'];

    // Prepare the statement for updating projects
    $stmt1 = $conn->prepare("UPDATE projects
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
    header('Location:' . $_SERVER['HTTP_REFERER']);
    exit(); // Make sure to exit after redirection
}

if (isset($_GET['delete_project_id'])) {
    $delete_project_id = $_GET['delete_project_id'];

    // SQL query to update the setTarget and setTargetDate columns in projects table
    $sql_projects = "UPDATE projects SET setTarget=null, setTargetDate=null WHERE project_id = $delete_project_id";

    // SQL query to update the setTarget and setTargetDate columns in deliverable_data table
    $sql_deliverable_data = "UPDATE deliverable_data SET setTarget=null, setTargetDate=null WHERE project_id = $delete_project_id";

    // Execute the SQL query for projects table
    if ($conn->query($sql_projects) === TRUE) {
        // Execute the SQL query for deliverable_data table
        if ($conn->query($sql_deliverable_data) === TRUE) {
            // Display a success Toastr notification
            $msg_error = "Project Sent back  Successfully";
            header('Location:' . $_SERVER['HTTP_REFERER']);
        } else {
            // Display an error Toastr notification for deliverable_data table
            $msg_error = "Error deleting the Project: " . $conn->error;
        }
    } else {
        // Display an error Toastr notification for projects table
        $msg_error = "Error deleting the Project: " . $conn->error;
    }
}



?>


<?php
include './include/footer.php';
?>
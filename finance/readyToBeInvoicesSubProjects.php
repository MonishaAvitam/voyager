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
        <h1 class="h3 mb-0 text-gray-800">Ready To Be Invoiced</h1>
    </div>

    <div class="card mt-5 mb-5 rounded">

        <ul class="nav nav-pills nav-fill ">
            <li class="nav-item">
                <a class="nav-link  " aria-current="page" href="readyToBeInvoiced.php">Projects</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="readyToBeInvoicesSubProjects.php">Sub Projects</a>
            </li>
        </ul>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Ready To Be Invoiced Projects</h6>
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



                <table class="table table-striped table-bordered table-sm " id="dataTable">
                    <thead>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Customer Name</th>
                            <th>Team</th>
                            <th>Amount (AUD) </th>
                            <th>Comments</th>
                            <th>Invoice No</th>
                            <th>Last Modified</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Customer Name</th>
                            <th>Team</th>
                            <th>Amount (AUD) </th>
                            <th>Comments</th>
                            <th>Invoice No</th>
                            <th>Last Modified</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>

                        <!-- ... Your table rows ... -->
                        <?php
                        $sql = "SELECT 
    *
FROM (
    
    SELECT 
        sp.project_id,
        sp.urgency,
        sp.reopen_status,
        sp.subproject_status,
        sp.subproject_name,
        sp.p_team,
        sp.setTarget,
        sp.setTargetDate,
        c.customer_name ,
        ri.price,
        ri.comments,
        ri.invoice_number,
        ri.last_modified_date,
        ri.rownumber,
        ri.id
    FROM 
        subprojects sp 
    LEFT JOIN 
        contacts c ON sp.contact_id = c.contact_id
    LEFT JOIN 
       csa_finance_readytobeinvoiced ri ON sp.project_id = ri.project_id
    WHERE 
        NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE sp.project_id = FI.project_id  AND FI.subproject_status IS NOT NULL )
        AND  EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE sp.project_id = RI.project_id)
        AND sp.subproject_status = ri.subproject_status
) AS combined_data
ORDER BY combined_data.id ASC;
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

                                if (empty($row['rownumber'])) {
                                    $project_name = $row['subproject_name'];
                                    $customer_name = $row['customer_name'];
                                    $p_team = $row['p_team'];
                                } else {
                                    $project_name = '';
                                    $customer_name = '';
                                    $p_team = '';
                                }
                                ?>

                                <td style="background-color: <?php echo $row['urgency']; ?>; text-align: center; width: 5%; border-radius: 5px; color: <?php echo ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff'; ?>">
                                    <?php echo $row['project_id']; ?>
                                    <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>
                                    <span class="badge badge-pill badge-danger"><?php if ($row['subproject_status'] != NULL) {
                                                                                    echo 'S' . $row['subproject_status'];
                                                                                } ?></span>

                                </td>



                                <form action="add_invoice.php" method="POST">
                                    <td>
                                        <input type="text" name="project_name" value="<?php echo $project_name ?>" style="border: none; background-color:transparent;  outline: none; display:none">
                                        <label for=""><?php echo $project_name ?></label>

                                    </td>
                                    <td>
                                        <input type="text" name="customer_name" value="<?php echo $customer_name; ?>" style="border: none; background-color:transparent;  outline: none; display:none">
                                        <label for=""><?php echo $customer_name ?></label>

                                    </td>

                                    <td width="5%">
                                        <input type="text" name="p_team" value="<?php echo $p_team; ?>" style="border: none; background-color: transparent;  outline: none;  display:none">
                                        <label for=""><?php echo $p_team ?></label>

                                    </td>

                                    <td width="5%">
                                        <input type="number" name="price" id="price" placeholder="Enter Dollars (AUD)" value="<?php echo $row['price'] ?>" style="width:80px">
                                    </td>

                                    <td>
                                        <textarea name="comments" id="comments" cols="20" rows="1"><?php echo $row['comments'] ?></textarea>
                                    </td>
                                    <td>
                                        <input type="text" name="invoice_number" id="invoice_number" value="<?php echo $row['invoice_number'] ?>" style="width: 100px;">
                                        <input type="text" name="id" id="id" value="<?php echo $row['id'] ?>" readonly hidden>
                                        
                                        <input type="text" name="project_id" id="project_id" value="<?php echo $row['project_id'] ?>" readonly hidden>
                                        <input type="text" name="subproject_status" id="subproject_status" value="<?php echo $row['subproject_status'] ?>" readonly hidden>
                                       
                                    </td>

                                    <td>
                                        <?php echo $row['last_modified_date'] ?>
                                    </td>


                                    <td class="d-flex justify-content-around align-items-center">
                                        <?php
                                        if (empty($row['rownumber'])) {
                                        ?>
                                            <a class="ml-2" title="SEND BACK" href="?delete_project_id=<?php echo $row['project_id']; ?>&subproject_status=<?php echo $row['subproject_status']; ?>" onclick="return confirm('Are you sure you want to send back if u send back it will send back all data related to this project ?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-skip-backward-fill" viewBox="0 0 16 16">
                                                    <path d="M.5 3.5A.5.5 0 0 0 0 4v8a.5.5 0 0 0 1 0V8.753l6.267 3.636c.54.313 1.233-.066 1.233-.697v-2.94l6.267 3.636c.54.314 1.233-.065 1.233-.696V4.308c0-.63-.693-1.01-1.233-.696L8.5 7.248v-2.94c0-.63-.692-1.01-1.233-.696L1 7.248V4a.5.5 0 0 0-.5-.5" />
                                                </svg></a>
                                            <a title="View" class="view-project ml-5 " href="../task-details.php?project_id=<?php echo $row['project_id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                                    <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                                </svg>
                                            </a>
                                            <button type="submit" name="add_row_subproject" onclick="return confirm('Are you sure you want Create new Row?')" class="ml-2 btn " hidden>
                                                <i class="bi bi-plus-circle btn-lg text-primary"></i>
                                            </button>
                                            <button type="submit" name="invoice" onclick="return confirm('Are you sure you want to Invoice this project?')" class=" ml-2 btn btn-primary btn-sm">Invoice</button>

                                        <?php } else { ?>

                                            <button type="submit" name="delete_row" onclick="return confirm('Are you sure you want to delete this row?')" class="ml-2 btn ">
                                                <i class="bi bi-dash-circle btn-lg text-primary"></i>
                                            </button>
                                        <?php } ?>


                                        <button type="submit" name="save" onclick="return confirm('Are you sure you want to save this information?')" class="ml-2 btn btn-primary btn-sm">Save</button>
                                </form>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
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
                    <label class="control-label text-dark" for="">Change Target Date</label>
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


<?php




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["setTarget"])) {


    $project_id = $_GET['project_id'];
    $setTarget = "Active";
    $setTargetDate = $_POST['targetdate'];


    // Prepare the statement
    $stmt = $conn->prepare("UPDATE projects SET setTarget = ?, setTargetDate = ? WHERE project_id = ?");

    // Check if statement preparation was successful
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    // Bind parameters
    $stmt->bind_param("ssi", $setTarget, $setTargetDate, $project_id);

    // Execute the statement
    $stmt->execute();

    // Close the statement
    $stmt->close();


    header('Location:targetProjects.php');
}

if (isset($_GET['delete_project_id'])) {
    $delete_project_id = $_GET['delete_project_id'];
    $subproject_status = $_GET['subproject_status'];

    // SQL query to delete the project
    $sql = "DELETE FROM csa_finance_readytobeinvoiced  WHERE project_id = $delete_project_id AND subproject_status = $subproject_status";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_success = "Project Sent back  Successfully";
        header('Location:' . $_SERVER['HTTP_REFERER']);
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the Project: ' . $conn->error . '";
    }
}


?>


<?php
include './include/footer.php';
?>
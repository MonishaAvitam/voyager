<?php



require '../authentication.php'; // admin authentication check 
require '../conn.php';
include 'include/sidebar.php';
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_team'])) {
    $enquiryId = $_POST['id'];
    $team = $_POST['team'];
    $lastUpdate = date('Y-m-d H:i:s');

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update enquiry_sales table
    $updateEnquirySql = "UPDATE enquiry_under_consideration SET team=?, last_updated=? WHERE id=?";
    $stmt = $conn->prepare($updateEnquirySql);

    // Assuming $purchase_status is a string, adjust the data type if it's an integer
    $stmt->bind_param("ssi",  $team, $lastUpdate, $enquiryId);

    if ($stmt->execute()) {
        $_SESSION['status_success'] = "Team Assigned Successfully";
        header('Location: underconsideration.php');
    } else {
        echo "Error: " . $stmt->error;
        $stmt->close();
        header('Location:' . $_SERVER['HTTP_REFERER']);
    }
    exit();
}

?>


<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Projects Sent To RAE</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Enquiry To Projects</h6>
                <!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_contact">Add Contact</button>-->
            </div>

            <div class="card-body">
                <div class="table-responsive p-3  ">
                    <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Project Status</th>
                                <th>Enquiry Details</th>
                                <th>Last Updated</th>
                                <th>Team</th>
                                <th>RAE Project ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Project Status</th>
                                <th>Enquiry Details</th>
                                <th>Last Updated</th>
                                <th>Team</th>
                                <th>RAE Project ID</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <!-- ... Your table rows ... -->

                            <?php
                            $sql = "SELECT cp.*,c.* FROM csa_sales_converted_projects cp LEFT JOIN contacts c ON cp.customer_id = c.contact_id";
                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {

                                echo '<tr><td colspan="7">No enquries were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <tr>
                                    <td>S<?php echo $row['id'];  ?></td>
                                    <td> <?php 
    echo !empty($row['customer_name']) ? $row['customer_name'] : $row['potential_customer'];  
    ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                    <td><?php echo $row['enquiry_details']; ?></td>
                                    <td><?php echo $row['last_updated'];  ?></td>
                                    <td><?php echo $row['team'];  ?> </td>
                                    <td><?php echo $row['rae_project_id'];  ?> </td>
                                    <td>
                                        &nbsp;
                                        <a title="View" class="view-project" href="../task-details.php?project_id=<?php echo $row['rae_project_id']; ?>">
                                            <i class="fas fa-solid fa-eye" style="color: #7a4edf;"></i>
                                            <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                            </svg>
                                        </a>
                                        &nbsp;
                                        <?php if (empty($row['rae_project_id'])) {  ?>
                                            <a href="Updateenquiry.php?id=<?php echo $row['id'] ?>&rae=true" class="ms-3 btn btn-warning btn-sm">
                                                <?php if ($row['status'] != 'Hold') {  ?>
                                                    Hold Project</a>
                                            &nbsp;
                                        <?php
                                                } else{ ?>
                                                Make Project Live </a>
                                    <?php
                                                }
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
</div>


<?php

include 'include/footer.php';

?>
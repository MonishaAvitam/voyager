<?php

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

// auth check

$user_id = $_SESSION['admin_id'];

$user_name = $_SESSION['name'];

$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {

    header('Location: index.php');
}

// check admin

$user_role = $_SESSION['user_role'];

$task_id = $_GET['project_id'];


?>

<?php include './include/sidebar.php'; ?>

<!--modal for employee add-->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .custom-red {

        background-color: red;

        color: white;

        /* Optional, set text color to contrast with the background */

    }

    .custom-orange {

        background-color: orange;

        color: white;

    }

    .custom-white {

        background-color: white;

        color: black;

    }

    .custom-green {

        background-color: green;

        color: white;

    }

    .custom-purple {

        background-color: purple;

        color: white;

    }
</style>



<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            <div class="well well-custom">

                <div class="row">

                    <div class="col-md-12 col-md-offset-2">

                        <div class="well">

                            <h3 class="text-center bg-primary text-white" style="padding: 7px;">Client Details </h3><br>

                            <div class="row">

                                <div class="col-md-12">

                                    <div class="table-responsive">

                                        <table class="table table-bordered table-single-product">

                                            <tbody>

                                                <?php

                                                // SQL query to retrieve project details based on project_id

                                                // SQL query to retrieve project details and contact information based on project_id
                                                $sql = "
                                                        SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team, p.project_manager, p.assign_to, p.start_date, p.EPT, p.project_details, p.urgency, c.customer_name, c.contact_id, c.customer_id, c.contact_name, c.contact_email, c.contact_phone_number, c.address, c.registration_date, c.comments 
                                                        FROM projects p 
                                                        LEFT JOIN contacts c ON p.contact_id = c.contact_id 
                                                        WHERE p.project_id = '$task_id' 

                                                        UNION 

                                                        SELECT 'Deliverable' AS type, dd.project_id, dd.project_name, dd.p_team, dd.project_manager, dd.assign_to, dd.start_date, dd.EPT, dd.project_details, dd.urgency, c.customer_name, c.contact_id, c.customer_id, c.contact_name, c.contact_email, c.contact_phone_number, c.address, c.registration_date, c.comments 
                                                        FROM deliverable_data dd 
                                                        LEFT JOIN contacts c ON dd.contact_id = c.contact_id 
                                                        WHERE dd.project_id = '$task_id'
                                                        ";


                                                $info = $obj_admin->manage_all_info($sql);
                                                $serial  = 1;
                                                $num_row = $info->rowCount();

                                                if ($num_row == 0) {
                                                    echo '<tr><td colspan="7">No clients were found</td></tr>';
                                                }

                                                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <tr>
                                                        <td>Client Name</td>
                                                        <td><?php echo $row['customer_name']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Project Name</td>

                                                        <td><?php echo $row['project_name']; ?></td>

                                                    </tr>
                                                    <tr>
                                                        <td>Project Description</td>
                                                        <td><?php echo $row['project_details']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Company Code</td> <!-- Added row for Company Code -->
                                                        <td><?php echo $row['customer_id']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Contact Person</td>
                                                        <td><?php echo $row['contact_name']; ?></td>
                                                    </tr>
                                                    <tr>

                                                        <td>Project Manager</td>

                                                        <td><?php echo $row['project_manager']; ?></td>

                                                    </tr>
                                                    <tr>

                                                        <td>Assign To</td>

                                                        <td><?php echo $row['assign_to']; ?></td>

                                                    </tr>
                                                    <tr>
                                                        <td>Contact Email</td>
                                                        <td><?php echo $row['contact_email']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Phone Number</td>
                                                        <td><?php echo $row['contact_phone_number']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Address</td>
                                                        <td><?php echo $row['address']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Comments</td>
                                                        <td><?php echo $row['comments']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="border: none;">
                                                            <a title="Go Back" href="readyToBeInvoiced.php">
                                                                <span class="btn btn-dark btn-xs">Go Back</span>
                                                            </a>

                                                            <!-- Edit Button that redirects to edit_contact.php -->
                                                            <a href="edit_contacts.php?contact_id=<?php echo $row['contact_id']; ?>" class="btn btn-info">Update Client Info</a>



                                                            <a href="edit-project.php?project_id=<?php echo $row['project_id']; ?>" id="update-project" class="btn btn-success">Update Project Info</a>

                                                            <script>
                  document.getElementById("update-project").addEventListener("click", function() {
                    sessionStorage.setItem("lastVisitedURL", window.location.href);
                    console.log("Current URL stored:", window.location.href);
                  });
                </script>



                                                        </td>


                                                    </tr>

                                                <?php
                                                }
                                                ?>

                                            </tbody>

                                        </table>

                                    </div>



                                    <div class="form-group">




                                    </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php


if (isset($_POST["file_manager"]) && isset($_GET["project_id"])) {

    $project_id = $_GET["project_id"]; // Assuming it's in the URL

    header('Location: Gdrive_files.php?file_project_id=' . $project_id);

    exit; // It's a good practice to exit after a header redirect

}

if (isset($_POST["send_to_deliverables"]) && isset($_GET["file_project_id"])) {

    $project_id = $_GET["file_project_id"]; // Assuming it's in the URL

    header('Location: process_data-delivery.php?project_id=' . $project_id);

    exit; // It's a good practice to exit after a header redirect

}

?>

<?php

include("include/footer.php");

?>
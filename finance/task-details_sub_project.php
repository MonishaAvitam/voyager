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

$table_id =  isset($_GET['table_id']) ? htmlspecialchars($_GET['table_id']) : '';


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

<?php

// SQL query to retrieve project details based on project_id

$sql = "SELECT * FROM subprojects WHERE table_id = '$table_id' ";

$info = $obj_admin->manage_all_info($sql);

$serial  = 1;

$num_row = $info->rowCount();

if ($num_row == 0) {

    echo '<tr><td colspan="7">No projects were found</td></tr>';
}

while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
    $project_id =  $row['project_id'];
}

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            <div class="well well-custom">

                <div class="row">

                    <div class="col-md-12 col-md-offset-2">

                        <div class="well">

                            <h3 class="text-center bg-primary text-white" style="padding: 7px;">Project Details </h3><br>

                            <div class="row">

                                <div class="col-md-12">

                                    <div class="table-responsive">

                                        <table class="table table-bordered table-single-product">

                                            <tbody>

                                                <?php

                                                // SQL query to retrieve project details based on project_id

                                                $sql = "SELECT * FROM subprojects WHERE table_id = '$table_id'";

                                                $info = $obj_admin->manage_all_info($sql);

                                                $serial  = 1;

                                                $num_row = $info->rowCount();

                                                if ($num_row == 0) {

                                                    echo '<tr><td colspan="7">No projects were found</td></tr>';
                                                }

                                                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                                ?>

                                                    <tr>
                                                        <td>Project Name</td>

                                                        <td><?php echo $row['subproject_name']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>Description</td>

                                                        <td><?php echo $row['subproject_details']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>Start Time</td>

                                                        <td><?php echo $row['sub_EPT']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>Start Date</td>

                                                        <td><?php echo $row['start_date']; ?></td>

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

                                                        <td>Status</td>

                                                        <td>

                                                            <?php

                                                            $urgency = $row['urgency'];

                                                            $urgencyText = '';

                                                            $urgencyClass = '';

                                                            // Map color values to urgency types

                                                            switch ($urgency) {

                                                                case 'red':

                                                                    $urgencyText = 'Very Urgent';

                                                                    $urgencyClass = 'custom-red';

                                                                    break;

                                                                case 'orange':

                                                                    $urgencyText = 'Urgent';

                                                                    $urgencyClass = 'custom-orange';

                                                                    break;

                                                                case 'white':

                                                                    $urgencyText = "Don't Start the project";

                                                                    $urgencyClass = 'custom-white';

                                                                    break;

                                                                case 'green':

                                                                    $urgencyText = 'Ready to start the Project';

                                                                    $urgencyClass = 'custom-green';

                                                                    break;

                                                                case 'purple':

                                                                    $urgencyText = 'Closed';

                                                                    $urgencyClass = 'custom-purple';

                                                                    break;

                                                                default:

                                                                    // Handle any other color value or error condition

                                                                    $urgencyText = 'Unknown';

                                                                    $urgencyClass = 'custom-unknown';

                                                                    break;
                                                            }

                                                            // Output the urgency type with the appropriate style

                                                            echo "<div class='col-md-2 border p-1 $urgencyClass'>$urgencyText</div>";

                                                            ?>

                                                        </td>

                                                    </tr>

                                                    <tr>
                                                        <td>Project Files</td><?php if ($user_role == 2 or $user_role == 1 or $user_role == 3) {   ?>

                                                            <td>
                                                                <!-- <form method="post">

                                                                    <button type="submit" name="file_manager" class="btn btn-success">Click here to view project files &nbsp;&nbsp;<span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">

                                                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />

                                                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />

                                                                            </svg></span></button>
                                                                            
                                                                </form> -->
                                                                <button class="btn btn-primary " onclick="location.href='https://drive.google.com/drive/u/0/folders/0AMBoRd5BFG7XUk9PVA'">Open Industrial Team Shared Drive </button>
                                                                <button class="btn btn-primary " onclick="location.href='https://drive.google.com/drive/u/0/folders/0AE4knKSOSdZOUk9PVA'">Open Building Team Shared Drive </button>

                                                            </td> <?php  } else { ?>

                                                            <td>

                                                                <form method="post" action="">

                                                                    <?php

                                                                                    if (isset($_POST['requst_verification'])) {

                                                                                        // Extract data from the AJAX request

                                                                                        $user_id = $user_id;

                                                                                        $msg_type = "Requst";

                                                                                        $msg_subject = "Download Request From";

                                                                                        $msg_content = 'File Name: ' . $row['project_name'];

                                                                                        $msg_status = "sent";

                                                                                        $sent_to = "1";

                                                                                        include 'conn.php';

                                                                                        // Insert the data into the notifications table

                                                                                        $sql = "INSERT INTO notifications (user_id, msg_type, msg_subject, msg_content, msg_status, sent_to) VALUES ('$user_id', '$msg_type', '$msg_subject', '$msg_content', '$msg_status', '$sent_to')";

                                                                                        if ($conn->query($sql) === TRUE) {

                                                                                            echo "<script>window.onload = function() {

                                    document.getElementById('request_btn').innerHTML = 'Download Request Sent';

                                    document.getElementById('request_btn').disabled = true;

                                };</script>";
                                                                                        } else {

                                                                                            echo "Error: " . $sql . "<br>" . $conn->error;
                                                                                        }

                                                                                        $conn->close();
                                                                                    } else {;
                                                                                    }

                                                                    ?>

                                                                    <button class="btn btn-primary" type="submit" name="requst_verification" id="request_btn">Request Download &nbsp;&nbsp;<span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">

                                                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />

                                                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />

                                                                            </svg></span></button>

                                                                </form>

                                                            </td>

                                                        <?php        }

                                                        ?>

                                                    </tr>

                                                <?php } ?>

                                            </tbody>

                                        </table>

                                    </div>

                                    <div class="form-group">

                                        <div class="col-sm-3">

                                            <a title="Update Task" href="javascript:history.back()"><span class="btn btn-dark btn-xs">Go Back</span></a>

                                        </div>

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

include 'conn.php';

if (isset($_POST["file_manager"])) {

    header('Location: Gdrive_files.php?file_project_id=' . $project_id);

    exit; // It's a good practice to exit after a header redirect

}

if (isset($_POST["send_to_deliverables"])) {

    header('Location: process_data-delivery.php?project_id=' . $project_id);

    exit; // It's a good practice to exit after a header redirect

}

?>

<?php

include("include/footer.php");

?>


<?php

include("include/footer.php");

?>
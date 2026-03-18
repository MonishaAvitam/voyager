<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require './authentication.php'; // admin authentication check 
require './conn.php';
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
        <h1 class="h3 mb-0 text-gray-800">Agenda</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Projects</h6>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm" id="dataTable">
                    <thead>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Target Date</th>
                            <th>Timestamp</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Target Date</th>
                            <th>Timestamp</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>

                        <!-- ... Your table rows ... -->
                        <?php
                        $sql = "SELECT  * from rae_goals where user_id =$user_id AND status != 'completed'";


                        $info = $obj_admin->manage_all_info($sql);
                        $serial  = 1;
                        $num_row = $info->rowCount();
                        if ($num_row == 0) {
                            echo '<tr>
                            <td colspan="7">No agenda were found</td>
                            <td colspan="7" hidden>No projects were found</td d>
                            <td colspan="7" hidden>No projects were found</td d>
                            <td colspan="7" hidden>No projects were found</td d>
                            <td colspan="7" hidden>No projects were found</td d>
                            
                            
                            </tr>';
                        }
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        ?>


                            <tr>




                                <td style=" text-align: center; width: 5%; border-radius: 5px;">
                                    <?php echo $row['project_id']; ?>

                                </td>



                                <td>
                                    <input type="text" name="project_name" value="<?php echo $row['project_name']; ?>" style="border: none; background-color:transparent; outline: none; display:none">
                                    <label for=""><?php echo $row['project_name']; ?></label>
                                </td>

                                <td>
                                    <p>
                                        <?php echo $row['goalDate']; ?>
                                    </p>
                                </td>
                                <td>
                                    <p>
                                        <?php echo $row['timeStamp']; ?>
                                    </p>
                                </td>






                                <td class="d-flex justify-content-around align-items-center">
                                    <a title="View" class="view-project" href="./task-details.php?project_id=<?php echo $row['project_id']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                            <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                        </svg>
                                    </a>&nbsp;&nbsp;

                                    <a onclick="fetch_project_id(<?php echo $row['project_id']; ?>)" class="text-primary  ml-2"><i class="fas fa-calendar-alt"></i></a>

                                    <form id="updateStatus" method="post">
                                        <input name="project_id" type="text" value="<?php echo $row['project_id']; ?>" hidden>
                                        <button type="submit" name="updateStatus" onclick="return confirm('Are you Sure ?');" class="ml-2 btn btn-outline-success"> Completed</button>
                                    </form>&nbsp;&nbsp;

                                    <a title="Delete Goal" href="?delete_goal_id=<?php echo $row['project_id']; ?>" onclick="return confirm('Are you sure you want to delete this Goal?');">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash text-danger" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                                        </svg></a>&nbsp;&nbsp;






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
        $('#changeDate').modal('show');
    }
</script>


<!-- change goal date modal  -->


<div class="modal fade " id="changeDate" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label text-dark" for="">Change Target Date</label>
                    <input class="form-control" type="date" name="goalDate" id="goalDate">
                    <button class="btn btn-primary mt-2" name="changeDate">Submit</button>
                </form>

            </div>

        </div>

    </div>

</div>







<?php


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["changeDate"])) {

    $project_id = $_GET['project_id'];
    $setGoalDate = $_POST['goalDate'];

    $stmt1 = $conn->prepare("UPDATE rae_goals
    SET goalDate = ?
    WHERE project_id = ?");

    // Bind parameters for updating projects
    $stmt1->bind_param("si", $setGoalDate, $project_id);

    // Execute the statement for updating projects
    $stmt1->execute();

    // Close the statement for updating projects
    $stmt1->close();

    // Redirect after successful update
    header('Location:' . $_SERVER['HTTP_REFERER']);
    exit(); // Make sure to exit after redirection
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateStatus"])) {

    $project_id = $_POST['project_id'];
    $status = 'Completed';

    $stmt1 = $conn->prepare("UPDATE rae_goals
    SET status = ?
    WHERE project_id = ?");

    // Bind parameters for updating projects
    $stmt1->bind_param("si", $status, $project_id);

    // Execute the statement for updating projects
    $stmt1->execute();

    // Close the statement for updating projects
    $stmt1->close();

    // Redirect after successful update
    header('Location:' . $_SERVER['HTTP_REFERER']);
    exit(); // Make sure to exit after redirection
}

if (isset($_GET['delete_goal_id'])) {
    $delete_goal_id = $_GET['delete_goal_id'];

    // SQL query to update the setTarget and setTargetDate columns in projects table
    $sql_projects = "delete from  rae_goals  WHERE project_id = $delete_goal_id";



    // Execute the SQL query for projects table
    // Execute the SQL query for deliverable_data table
    if ($conn->query($sql_projects) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Goal Deleted   Successfully";
        header('Location:' . $_SERVER['HTTP_REFERER']);
    } else {
        // Display an error Toastr notification for deliverable_data table
        $msg_error = "Error deleting the Goal: " . $conn->error;
    }
}




?>


<?php
include './include/footer.php';
?>
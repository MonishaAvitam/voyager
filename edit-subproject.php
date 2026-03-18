<?php
require 'authentication.php'; // admin authentication check 
require 'conn.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit; // Added exit to prevent further execution
}

// check admin
$user_role = $_SESSION['user_role'];
$table_id = $_GET['table_id'];
include 'include/login_header.php';

// Initialize variables to avoid undefined variable warnings
$msg_success = $msg_error = '';

ini_set('display_errors', 1);
error_reporting(E_ALL);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_project"])) {
    // Sanitize user inputs
    $table_id = $_GET['table_id'];
    $new_project_name = htmlspecialchars($_POST['new_project_name']);
    $project_details = htmlspecialchars($_POST['project_details']);
    $start_date = htmlspecialchars($_POST['start_date']);
    $customer_id_name = htmlspecialchars($_POST['customer_id_name']);
    $project_manager_combined = htmlspecialchars($_POST['project_manager']);
    list($project_managers_id, $project_manager) = explode('|', $project_manager_combined);
    $assign_to_combined = htmlspecialchars($_POST['assign_to']);
    list($assign_to_id, $assign_to_username) = explode('|', $assign_to_combined);
    $project_status = htmlspecialchars($_POST['project_status']);
    $p_team = htmlspecialchars($_POST['p_team']);
    $sub_links = trim($_POST['sub_links']); // Remove leading/trailing spaces

    // Ensure each line is properly formatted
    $lines = array_filter(array_map('trim', explode("\n", $sub_links))); // Split by new lines & remove empty entries

    $processedLinks = [];
    foreach ($lines as $line) {
        // Ensure there is no extra comma at the end of each line
        $processedLinks[] = rtrim($line, ',');
    }

    // Join back with a single comma (no extra line breaks)
    $sub_links = implode(",", $processedLinks);

    // Assuming you have received the selected value in days from the form


    $estimated_hours = rtrim($_POST['estimate_hrs'], characters: 'H') . 'H';
    // Create a DateTime object for the current date (today)
    $currentDateTime = new DateTime();

    // Calculate the end date by adding the selected number of days to the current date
    $endDateTime = clone $currentDateTime;

    // Format the end date as a string in the desired format (Y-m-d)
    $t_end_date = $_POST['sub_end_date'] ?? null;

    // Validate date format (you may want to use a more specific validation)
    if (strtotime($start_date) === false) {
        $msg_error = "Invalid start date format.";
    } else {
        // The rest of your code remains the same
        // ...

        // Query to update the project with question mark placeholders
        $sql = "UPDATE subprojects 
                SET subproject_name = ?,
                    subproject_details = ?,
                    project_manager = ?,
                    project_managers_id = ?,
                    start_date = ?,
                    sub_EPT = ?,
                    sub_end_date = ?,
                    sub_links = ?,
                    p_team = ?,
                    assign_to_id = ?,
                    assign_to = ?,
                    urgency = ?,
                    contact_id = ?
                WHERE table_id = ?";

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters using question mark placeholders
            $stmt->bind_param("ssssssssssssss", $new_project_name, $project_details, $project_manager, $project_managers_id, $start_date, $estimated_hours, $t_end_date, $sub_links, $p_team, $assign_to_id, $assign_to_username, $project_status, $customer_id_name, $table_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Project updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating the project.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Error preparing SQL statement.";
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?table_id=" . urlencode($table_id));
        exit();
    }
}
?>

<?php include 'include/sidebar.php'; ?>




<!--modal for employee add-->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Display Notification Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $_SESSION['success_message']; ?>',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo $_SESSION['error_message']; ?>',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

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

    .custom-yellow {

        background-color: yellow;

        color: black;

    }
</style>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            <div class="well well-custom">

                <div class="row">

                    <div class="col-md-12 col-md-offset-2">

                        <div class="well">

                            <h3 class="text-center bg-primary text-white" style="padding: 7px;">Sub Project Details</h3><br>

                            <form action="" method="post">
                                <!-- Replace 'update_project.php' with your update script -->

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-single-product">
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT * FROM subprojects WHERE table_id = '$table_id' ";

                                                    $info = $obj_admin->manage_all_info($sql);
                                                    $serial = 1;
                                                    $num_row = $info->rowCount();
                                                    if ($num_row == 0) {
                                                        echo '<tr><td colspan="7">No projects were found</td></tr>';
                                                    }
                                                    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                                    ?>
                                                        <tr>
                                                            <td>Main Project Id</td>
                                                            <td>
                                                                <label><?php echo $row['project_id'] ?></label>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Project Name</td>
                                                            <td>
                                                                <input type="text" class="form-control" name="new_project_name" value="<?php echo $row['subproject_name'] ?>" required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Description</td>
                                                            <td>
                                                                <textarea name="project_details" class="form-control" style="resize: vertical; height:200px" required><?php echo $row['subproject_details']; ?></textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Customer ID</td>
                                                            <td>
                                                                <?php
                                                                $sql = "SELECT contact_id, customer_id FROM contacts";
                                                                $info_contacts = $obj_admin->manage_all_info($sql);
                                                                ?>

                                                                <select class="form-control" name="customer_id_name" id="customer_id_name" required>
                                                                    <option value="">Select customer_id</option>

                                                                    <?php
                                                                    while ($contacts = $info_contacts->fetch(PDO::FETCH_ASSOC)) {
                                                                        $selected = ($contacts['contact_id'] == $row['contact_id']) ? 'selected' : '';
                                                                        echo '<option value="' . $contacts['contact_id'] . '" ' . $selected . '>' . $contacts['customer_id'] . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>


                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Project links</td>
                                                            <td>
                                                                <textarea name="sub_links" placeholder="Folder Links" class="form-control" id="sub_links" rows="5"><?php
                                                                                                                                                                    echo !empty(trim($row['sub_links'] ?? '')) ? preg_replace('/,/', ",\n", trim($row['sub_links'] ?? '')) . "," : '';
                                                                                                                                                                    ?></textarea>

                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Team</td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div>
                                                                        <select class="form-control" name="p_team" id="p_team" required>
                                                                            <option value="" <?php echo empty($row['p_team']) ? 'selected' : ''; ?>>Select Team</option>
                                                                            <option value="Building Team" <?php echo (!empty($row['p_team']) && strcasecmp($row['p_team'], "Building Team") == 0) ? 'selected' : ''; ?>>Building Team</option>
                                                                            <option value="Industrial Team" <?php echo (!empty($row['p_team']) && (strcasecmp($row['p_team'], "Industrial Team") == 0 || strcasecmp($row['p_team'], "industrial") == 0 || strcasecmp($row['p_team'], "Industrial") == 0)) ? 'selected' : ''; ?>>Industrial Team</option>
                                                                            <option value="count" <?php echo (!empty($row['p_team']) && strcasecmp($row['p_team'], "count") == 0) ? 'selected' : ''; ?>>Quick Project</option>
                                                                            <option value="IT" <?php echo (!empty($row['p_team']) && strcasecmp($row['p_team'], "IT") == 0) ? 'selected' : ''; ?>>IT Project</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Start Date</td>
                                                            <td>
                                                                <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($row['start_date'])); ?>" required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Estimated Date</td>
                                                            <td>
                                                                <input class="form-control" type="date" name="sub_end_date" id="sub_end_date" value="<?php echo $row['sub_end_date'] ?>">
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Estimated hrs</td>
                                                            <td>
                                                                <input class="form-control" type="" name="estimate_hrs" id="estimate_hrs" value="<?php echo $row['sub_EPT'] ?>">
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Project Manager</td>
                                                            <td>
                                                                <?php
                                                                $sql = "SELECT user_id, fullname FROM tbl_admin WHERE  raeAccess = 3 or raeAccess = 1 or raeAccess = 2";
                                                                $info_managers = $obj_admin->manage_all_info($sql);
                                                                ?>
                                                                <select class="form-control" name="project_manager" id="project_manager">
                                                                    <option value="">Not Assigned</option>
                                                                    <?php
                                                                    while ($manager = $info_managers->fetch(PDO::FETCH_ASSOC)) {
                                                                        $selected = ($manager['fullname'] == $row['project_manager']) ? 'selected' : '';
                                                                        echo '<option value="' . $manager['user_id'] . '|' . $manager['fullname'] . '" ' . $selected . '>' . $manager['fullname'] . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Engineer</td>
                                                            <td>
                                                                <?php
                                                                $sql = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess = 2 OR raeAccess = 3 or raeAccess = 1";
                                                                $info_engineers = $obj_admin->manage_all_info($sql);
                                                                ?>
                                                                <select class="form-control" name="assign_to" id="assign_to">
                                                                    <option value="">Not Assigned</option>
                                                                    <?php
                                                                    while ($engineer = $info_engineers->fetch(PDO::FETCH_ASSOC)) {
                                                                        $selected = ($engineer['fullname'] == $row['assign_to']) ? 'selected' : '';
                                                                        echo '<option value="' . $engineer['user_id'] . '|' . $engineer['fullname'] . '" ' . $selected . '>' . $engineer['fullname'] . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Project Status</td>
                                                            <td>

                                                                <select class="form-control" id="project_status" name="project_status" required>
                                                                    <?php if ($row['urgency'] == 'white') : ?>
                                                                        <option value="white" selected>Don't Start the Project</option>
                                                                    <?php else : ?>
                                                                        <option value="white">Don't Start the Project</option>
                                                                    <?php endif; ?>

                                                                    <?php if ($row['urgency'] == 'green') : ?>
                                                                        <option value="green" selected>Ready to Start the Project</option>
                                                                    <?php else : ?>
                                                                        <option value="green">Ready to Start the Project</option>
                                                                    <?php endif; ?>

                                                                    <?php if ($row['urgency'] == 'purple') : ?>
                                                                        <option value="purple" selected>Mark as Completed</option>
                                                                    <?php else : ?>
                                                                        <option value="purple">Mark as Completed</option>
                                                                    <?php endif; ?>

                                                                    <?php if ($row['urgency'] == 'orange') : ?>
                                                                        <option value="orange" selected>Urgent</option>
                                                                    <?php else : ?>
                                                                        <option value="orange">Urgent</option>
                                                                    <?php endif; ?>

                                                                    <?php if ($row['urgency'] == 'red') : ?>
                                                                        <option value="red" selected>Very Urgent</option>
                                                                    <?php else : ?>
                                                                        <option value="red">Very Urgent</option>
                                                                    <?php endif; ?>

                                                                    <?php if ($row['urgency'] == 'yellow') : ?>
                                                                        <option value="yellow" selected>HOLD</option>
                                                                    <?php else : ?>
                                                                        <option value="yellow">HOLD</option>
                                                                    <?php endif; ?>
                                                                    <?php if ($row['urgency'] == 'cancelled') : ?>
                                                                        <option value="cancelled" selected>Cancelled</option>
                                                                    <?php else : ?>
                                                                        <option value="cancelled">Cancelled</option>
                                                                    <?php endif; ?>
                                                                </select>

                                                            </td>
                                                        </tr>
                                                        <!-- Include other project details here -->
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- Include input fields for updating project details here -->
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <a class="btn btn-secondary btn-xs" href="javascript:void(0);" onclick="window.location.href = sessionStorage.getItem('lastVisitedURL') || document.referrer ;">Go Back</a> <button type="submit" class="btn btn-success" name="update_project">Update Project</button>
                                            </div>
                                        </div>
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

<?php

include("include/footer.php");

?>
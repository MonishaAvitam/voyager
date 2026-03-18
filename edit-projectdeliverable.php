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
$task_id = $_GET['project_id'];
include 'include/login_header.php';

// Initialize variables to avoid undefined variable warnings
$msg_success = $msg_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_project"])) {
    // Sanitize user inputs
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

    // Assuming you have received the selected value in days from the form
    $raw_hours = (int)$_POST['t_end_date'];
    $estimated_hours = $raw_hours . "H";


    $number = intval($_POST['t_end_date']); // Ensure it's an integer

    // Set the initial value of $totalHours based on the selected value
    if ($number >= 0 && $number <= 15) {
        $totalHours = 5;
    } elseif ($number >= 16 && $number <= 40) {
        $totalHours = 10;
    } elseif ($number >= 41 && $number <= 80) {
        $totalHours = 20;
    } else {
        $totalHours = 40;
    }
    // Create a DateTime object for the current date (today)
    $currentDateTime = new DateTime();

    // Calculate the end date by adding the selected number of days to the current date
    $endDateTime = clone $currentDateTime;
    $endDateTime->add(new DateInterval("P" . $totalHours . "D"));

    // Format the end date as a string in the desired format (Y-m-d)
    $t_end_date = $endDateTime->format("Y-m-d");


    // Validate date format (you may want to use a more specific validation)
    if (strtotime($start_date) === false) {
        $msg_error = "Invalid start date format.";
    } else {
        // The rest of your code remains the same
        // ...

        // Query to update the project with question mark placeholders
        $sql = "UPDATE deliverable_data
                SET project_name = ?,
                    project_details = ?,
                    project_manager = ?,
                    project_managers_id = ?,
                    start_date = ?,
                    EPT = ?,
                    end_date = ?,
                    p_team = ?,
                    assign_to_id = ?,
                    assign_to = ?,
                    urgency = ?,
                    contact_id = ?
                WHERE project_id = ?";

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters using question mark placeholders
            $stmt->bind_param("sssssssssssss", $new_project_name, $project_details, $project_manager, $project_managers_id, $start_date, $estimated_hours, $t_end_date, $p_team, $assign_to_id, $assign_to_username, $project_status, $customer_id_name, $task_id);

            if ($stmt->execute()) {
                $msg_success = "Project Updated successfully!";
                header('Location: develirables_data.php');
                exit; // Added exit to prevent further execution
            } else {
                // Log the error instead of showing it to the user
                error_log("SQL Error: " . $stmt->error);
                $msg_error = "An error occurred while updating the project.";
            }
        } else {
            $msg_error = "Error in SQL statement preparation.";
        }
    }
}

?>

<?php include 'include/sidebar.php'; ?>




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

                            <h3 class="text-center bg-primary text-white" style="padding: 7px;">Project Details</h3><br>

                            <form action="" method="post">
                                <!-- Replace 'update_project.php' with your update script -->

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-single-product">
                                                <tbody>
                                                    <?php
                                                    // SQL query to retrieve project details based on project_id
                                                    $sql = "SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team, p.project_manager, p.assign_to, p.EPT, p.project_details, p.urgency, p.start_date, p.end_date, p.contact_id
FROM projects p
LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
WHERE p.project_id = '$task_id'
UNION
SELECT 'Deliverable' AS type, dd.project_id, dd.project_name, dd.p_team, dd.project_manager, dd.assign_to, dd.EPT, dd.project_details, dd.urgency, dd.start_date, dd.end_date, dd.contact_id
FROM deliverable_data dd
LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
WHERE dd.project_id = '$task_id'
UNION
SELECT 'Contact' AS type, c.contact_id, c.contact_name ,c.customer_id AS name, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL
FROM contacts c
WHERE c.contact_id = '$task_id';"; // Replace $contact_id with the actual customer_id
                                                    $info = $obj_admin->manage_all_info($sql);
                                                    $serial = 1;
                                                    $num_row = $info->rowCount();
                                                    if ($num_row == 0) {
                                                        echo '<tr><td colspan="7">No projects were found</td></tr>';
                                                    }
                                                    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                                    ?>

                                                        <tr>
                                                            <td>Project Name</td>
                                                            <td>
                                                                <input type="text" class="form-control" name="new_project_name" value="<?php echo $row['project_name'] ?>" required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Description</td>
                                                            <td>
                                                                <textarea name="project_details" class="form-control" style="resize: vertical; height:200px" required><?php echo $row['project_details']; ?></textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Customer ID</td>
                                                            <td>
                                                                <?php
                                                                $sql = "SELECT contact_id, customer_id,customer_name FROM contacts";
                                                                $info_contacts = $obj_admin->manage_all_info($sql);
                                                                ?>

                                                                <select class="form-control" name="customer_id_name" id="customer_id_name" required>
                                                                    <option value="">Select customer_id</option>

                                                                    <?php
                                                                    while ($contacts = $info_contacts->fetch(PDO::FETCH_ASSOC)) {
                                                                        $selected = ($contacts['contact_id'] == $row['contact_id']) ? 'selected' : '';
                                                                        echo '<option value="' . $contacts['contact_id'] . '" ' . $selected . '>' . $contacts['customer_name'] . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>


                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Team</td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div>
                                                                        <select class="form-control" name="p_team" id="p_team" required>
                                                                            <option value="Building Team" <?php echo (strcasecmp($row['p_team'], "Building Team") == 0) ? 'selected' : ''; ?>>Building Team</option>
                                                                            <option value="Industrial Team" <?php echo (strcasecmp($row['p_team'], "Industrial Team") == 0 || strcasecmp($row['p_team'], "industrial") == 0 || strcasecmp($row['p_team'], "Industrial") == 0) ? 'selected' : ''; ?>>Industrial Team</option>
                                                                            <option value="count" <?php echo (strcasecmp($row['p_team'], "count") == 0) ? 'selected' : ''; ?>>Quick Project</option>
                                                                            <option value="IT" <?php echo (strcasecmp($row['p_team'], "IT") == 0) ? 'selected' : ''; ?>>IT Project</option>
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
                                                            <td>Estimated Hours</td>
                                                            <td>
                                                                <input class="form-control" type="number" name="t_end_date" id="t_end_date" min="0" value="<?php echo $row['EPT'] ?>">
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Project Manager</td>
                                                            <td>
                                                                <?php
                                                                $sql = "SELECT user_id, fullname FROM tbl_admin WHERE  raeAccess = 3 or raeAccess = 1 ";
                                                                $info_managers = $obj_admin->manage_all_info($sql);
                                                                ?>
                                                                <select class="form-control" name="project_manager" id="project_manager" required>
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
                                                                $sql = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess = 2 OR raeAccess = 3";
                                                                $info_engineers = $obj_admin->manage_all_info($sql);
                                                                ?>
                                                                <select class="form-control" name="assign_to" id="assign_to" required>
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
                                                                        <option value="purple" selected>Close</option>
                                                                    <?php else : ?>
                                                                        <option value="purple">Close</option>
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
                                                <a onclick="javascript:history.back()" class="btn btn-secondary btn-xs">Go Back</a>
                                                <button type="submit" class="btn btn-success btn-xs" name="update_project">Update Project</button>
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
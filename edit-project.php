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
$task_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

include 'include/login_header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_project"])) {
    $new_project_name = trim($_POST['new_project_name']);
    $project_details = trim($_POST['project_details']);
    $start_date = $_POST['start_date'];
    $customer_id_name = trim($_POST['customer_id_name']);
    $state =$_POST['state'];

    // Handle project manager input
    // Handle project manager input
    if (isset($_POST['project_manager']) && strpos($_POST['project_manager'], '|') !== false) {
        list($project_managers_id, $project_manager) = explode('|', $_POST['project_manager']);
    }


    // Handle assignment input
    if (isset($_POST['assign_to']) && strpos($_POST['assign_to'], '|') !== false) {
        list($assign_to_id, $assign_to_username) = explode('|', $_POST['assign_to']);
    }

    $project_status = trim($_POST['project_status']);
    $p_team = trim($_POST['p_team']);
    $estimated_hours = rtrim($_POST['estimate_EPT'], 'H') . 'H';
    $links = trim($_POST['links']); // Remove leading/trailing spaces

    // Ensure each line is properly formatted
    $lines = array_filter(array_map('trim', explode("\n", $links))); // Split by new lines & remove empty entries

    $processedLinks = [];
    foreach ($lines as $line) {
        // Ensure there is no extra comma at the end of each line
        $processedLinks[] = rtrim($line, ',');
    }

    // Join back with a single comma (no extra line breaks)
    $links = implode(",", $processedLinks);


    // Validate and calculate end_date
    if (!empty($_POST['end_date']) && ctype_digit($_POST['end_date'])) {
        $number = intval($_POST['end_date']);
        $currentDateTime = new DateTime();
        $currentDateTime->modify("+{$number} days");
        $t_end_date = $currentDateTime->format('Y-m-d');
    } else {
        $t_end_date = $_POST['end_date'];
    }

    // Validate start date format
    if (!strtotime($start_date)) {
        $_SESSION['error_message'] = "Invalid start date format.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?project_id=" . $_POST['task_id']);
        exit();
    }

    if (!isset($_SESSION['error_message'])) {
        // Update project query
        $sqlProjects = "UPDATE projects 
                        SET project_name = ?, 
                            project_details = ?, 
                            project_manager = ?, 
                            project_managers_id = ?, 
                            start_date = ?, 
                            EPT = ?, 
                            end_date = ?, 
                            p_team = ?, 
                            state = ?,
                            assign_to_id = ?, 
                            assign_to = ?, 
                            links=?,
                            urgency = ?, 
                            contact_id = ? 
                        WHERE project_id = ?";

        if ($stmtProjects = $conn->prepare($sqlProjects)) {
            $stmtProjects->bind_param(
                "sssssssssssssss",
                $new_project_name,
                $project_details,
                $project_manager,
                $project_managers_id,
                $start_date,
                $estimated_hours,
                $t_end_date,
                $p_team,
                $state,
                $assign_to_id,
                $assign_to_username,
                $links,
                $project_status,
                $customer_id_name,
                $task_id
            );

            if ($stmtProjects->execute()) {
                $_SESSION['success_message'] = "Project updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating the project.";
            }
            $stmtProjects->close();
        } else {
            $_SESSION['error_message'] = "Error preparing SQL statement.";
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?project_id=" . $task_id);
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
                                                    $sql = "SELECT 'Project' AS type, 
                                                    p.project_id, 
                                                    p.project_name, 
                                                    p.p_team, 
                                                    p.project_manager, 
                                                    p.project_managers_id, 
                                                    p.assign_to, 
                                                    p.assign_to_id, 
                                                    p.EPT, 
                                                    p.links,
                                                    p.project_details, 
                                                    p.urgency, 
                                                    p.start_date, 
                                                    p.state,
                                                    p.end_date, 
                                                    p.contact_id
                                             FROM projects p
                                             LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
                                             WHERE p.project_id = '$task_id'
                                             
                                             UNION ALL
                                    
                                             SELECT 'Deliverable' AS type, 
                                                    dd.project_id, 
                                                    dd.project_name, 
                                                    dd.p_team, 
                                                    dd.project_manager, 
                                                    NULL AS project_managers_id,  -- Added NULL to match column count
                                                    dd.assign_to, 
                                                    NULL AS assign_to_id,
                                                    dd.EPT, 
                                                    NULL AS links,
                                                    dd.project_details, 
                                                    dd.urgency, 
                                                    dd.start_date, 
                                                    NULL AS state,
                                                    dd.end_date, 
                                                    dd.contact_id
                                             FROM deliverable_data dd
                                             LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
                                             WHERE dd.project_id = '$task_id'
                                             
                                             UNION ALL
                                    
                                             SELECT 'Contact' AS type, 
                                                    c.contact_id, 
                                                    c.contact_name, 
                                                    NULL AS p_team,  -- Added NULL to match columns
                                                    NULL AS project_manager, 
                                                    NULL AS project_managers_id, 
                                                    NULL AS assign_to, 
                                                    NULL AS assign_to_id,
                                                    NULL AS EPT, 
                                                    NULL AS links,
                                                    NULL AS project_details, 
                                                    NULL AS urgency, 
                                                    NULL AS start_date, 
                                                    NULL AS state,
                                                    NULL AS end_date, 
                                                    c.contact_id
                                             FROM contacts c
                                             WHERE c.contact_id = '$task_id';";
                                                    // Replace $contact_id with the actual customer_id
                                                    $info = $obj_admin->manage_all_info($sql);
                                                    $serial = 1;
                                                    $num_row = $info->rowCount();
                                                    if ($num_row == 0) {
                                                        echo '<tr><td colspan="7">No projects were found</td></tr>';
                                                    }
                                                    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                                    ?>

                                                        <tr>
                                                            <td> Project Id</td>
                                                            <td>
                                                                <label><?php echo $row['project_id'] ?></label>
                                                            </td>
                                                        </tr>

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
                                                                    <option value="">Select Customer ID</option>
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
                                                            <td>State</td>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div>
                                                                        <select class="form-control" name="state" id="state" required>
                                                                            <option value="">Select Region</option>
                       
                      
                                                                            <Option value="SIG" <?php echo (strcasecmp($row['state'], "SG") == 0) ? 'selected' : ''; ?>>SG (Singapore)</Option>
                                                                             <option value="">Select State</option>
                                                                            <Option value="IND" <?php echo (strcasecmp($row['state'], "IND") == 0) ? 'selected' : ''; ?>>IND (India)</Option>
                                                                           
                                                                            <Option value="N/A" <?php echo (strcasecmp($row['state'], "N/A") == 0) ? 'selected' : ''; ?>>Not Applicable</Option>
                                                                        </select>

                                                                    </div>
                                                                </div>  
                                                        </tr>
                                                        <tr>
                                                            <td>Project links</td>
                                                            <td>
                                                                <textarea name="links" placeholder="Folder Links" class="form-control" id="links" rows="5"><?php
                                                                                                                                                            echo !empty(trim($row['links'])) ? preg_replace('/,/', ",\n", trim($row['links'])) . "," : '';
                                                                                                                                                            ?></textarea>

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
                                                                <input class="form-control" type="date" name="end_date" id="end_date" min="0" value="<?php echo $row['end_date'] ?>" required>
                                                            </td>

                                                        </tr>
                                                        <tr>
                                                            <td>Estimated Hours</td>
                                                            <td>
                                                                <input type="text" name="estimate_EPT" id="estimate_EPT" class="form-control" value="<?php echo $row['EPT'] ?>" required>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Project Manager</td>
                                                            <td>
                                                                <?php
                                                                $sql = "SELECT user_id, fullname FROM tbl_admin ";
                                                                $info_managers = $obj_admin->manage_all_info($sql);
                                                                ?>
                                                                <select class="form-control" name="project_manager" id="project_manager">
                                                                    <option value="">Not Assigned</option>

                                                                    <?php
                                                                    while ($manager = $info_managers->fetch(PDO::FETCH_ASSOC)) {
                                                                        $selected = ($manager['user_id'] == $row['project_managers_id']) ? 'selected' : '';
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
                                                                $sql = "SELECT user_id, fullname FROM tbl_admin ";
                                                                $info_engineers = $obj_admin->manage_all_info($sql);
                                                                ?>
                                                                <select class="form-control" name="assign_to" id="assign_to">
                                                                    <option value="">Not Assigned</option>
                                                                    <?php
                                                                    while ($engineer = $info_engineers->fetch(PDO::FETCH_ASSOC)) {
                                                                        $selected = ($engineer['user_id'] == $row['assign_to_id']) ? 'selected' : '';
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
                                                <a class="btn btn-secondary btn-xs" href="javascript:void(0);" onclick="window.location.href = sessionStorage.getItem('lastVisitedURL') || document.referrer ;">Go Back</a>




                                                <button type="submit" class="btn btn-primary btn-xs" name="update_project">Update Project</button>
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
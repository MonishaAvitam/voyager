<?php

require 'authentication.php'; // admin authentication check 

require 'conn.php';

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

include 'include/login_header.php';

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
</style>



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

                                                $sql = "SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team, p.project_manager, p.assign_to, p.start_date, p.end_date, p.EPT, p.project_details, p.urgency, c.customer_name ,p.links,p.state
                                                        FROM projects p 
                                                        LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
                                                        LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
                                                        WHERE p.project_id = '$task_id' 
                                                        UNION 
                                                        SELECT 'Deliverable', dd.project_id, dd.project_name, dd.p_team, dd.project_manager, dd.assign_to, dd.start_date, NULL AS end_date, dd.EPT, dd.project_details, dd.urgency, c.customer_name ,NULL AS links, NULL AS state
                                                        FROM deliverable_data dd 
                                                        LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
                                                        LEFT JOIN contacts c ON (dd.contact_id = c.contact_id)  
                                                        WHERE dd.project_id = '$task_id'
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
                                                        <td>Project Name</td>

                                                        <td><?php echo $row['project_name']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>Description</td>

                                                        <td><?php echo $row['project_details']; ?></td>

                                                    </tr>
                                                    <tr>

                                                        <td>Customer Name</td>

                                                        <td><?php echo $row['customer_name']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>Hours </td>

                                                        <td><?php echo $row['EPT']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>Start Date</td>

                                                        <td><?php echo $row['start_date']; ?></td>

                                                    </tr>

                                                    <tr>

                                                        <td>State</td>

                                                        <td><?php echo $row['state']; ?></td>

                                                    </tr>

                                                    <tr>
                                                        <td>Estimated Completion Date</td>

                                                        <td><?php echo $row['end_date']; ?></td>
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


                                                            echo "<div class='col-md-2 border p-1 $urgencyClass'>$urgencyText</div>";

                                                            ?>

                                                        </td>

                                                    </tr>
                                                    <tr>
                                                        <td>Links</td>
                                                        <td>
                                                            <?php
                                                            if (!empty($row['links'])) {
                                                                // Match valid URLs (starting with http:// or https://)
                                                                preg_match_all('/\bhttps?:\/\/[^\s,]+/', $row['links'], $matches);
                                                                $urls = $matches[0]; // Extract matched URLs

                                                                // Replace URLs in the text with a placeholder to extract remaining text
                                                                $textWithoutUrls = preg_replace('/\bhttps?:\/\/[^\s,]+/', '[URL]', $row['links']);

                                                                // Split text by placeholder, then further split by commas
                                                                $textParts = preg_split('/,/', $textWithoutUrls);

                                                                $urlIndex = 0;
                                                                foreach ($textParts as $text) {
                                                                    $trimmedText = trim($text);

                                                                    if (!empty($trimmedText)) {
                                                                        // Replace [URL] placeholders with actual links
                                                                        $formattedText = str_replace('[URL]', '', $trimmedText);
                                                                        echo htmlspecialchars($formattedText);

                                                                        // If a URL exists at this position, display it as a link
                                                                        if (isset($urls[$urlIndex])) {
                                                                            echo ' <a href="' . htmlspecialchars($urls[$urlIndex]) . '" target="_blank">' . htmlspecialchars($urls[$urlIndex]) .' <i class="fa-solid text-dark fa-square-arrow-up-right"></i></a>';
                                                                            $urlIndex++; // Move to the next URL
                                                                        }
                                                                        echo '<br>'; // Add a line break for readability
                                                                    }
                                                                }
                                                            } else {
                                                                echo '<span class="text-muted">No links available.</span>';
                                                            }
                                                            ?>
                                                        </td>

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
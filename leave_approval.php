<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
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

use Google\Service\AdMob\DateRange;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Days;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
?>

<?php include 'include/sidebar.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php'; ?>


<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Casual/Sick Leave Application</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>


    <!-- Content Row -->

    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"></h6>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_contact">Leave Request</button>
            </div>
            <div class="card-body">
                <div class="table-responsive p-3">
                    <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Employee Name</th>
                                <th>Leave Type</th>
                                <th>Leave Date</th>
                                <th>Hours</th>

                                <th>Approval</th>

                                <?php if ($user_role != 2) { ?>
                                    <th>Actions</th>
                                <?php  } ?>
                            </tr>
                        </thead>


                        <tbody>
                            <?php
                            $sql = "SELECT * from leave_approval ";
                            if ($user_role == 1) {
                                // Admin can see all projects
                                $sql .= " ORDER BY leave_id DESC";
                            } else {
                                $sql .= " WHERE employee_id = $user_id ORDER BY leave_id DESC";
                            }
                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();


                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>

                                <tr>
                                    <form action='' method='POST'>
                                        <td><?php echo $row['leave_id'] ?></td>
                                        <td><?php echo $row['employee_name'] ?></td>
                                        <td><?php echo $row['leave_type'] ?></td>
                                        <?php
                                        $date_from = DateTime::createFromFormat('Y-m-d', $row['leave_from']);
                                        $formatted_date_from = $date_from ? $date_from->format('d-m-Y') : 'Invalid Date';

                                        $date_to = DateTime::createFromFormat('Y-m-d', $row['leave_to']);
                                        $formatted_date_to = $date_to ? $date_to->format('d-m-Y') : 'Invalid Date';
                                        ?>
                                        <td><?php echo $formatted_date_from ?> - <?php echo $formatted_date_to ?></td>

                                        <td><?php echo $row['hours'] ?>H</td>



                                        <td>
                                            <?php
                                            // Ensure keys exist before accessing
                                            $managerApproval = isset($row['approved']) ? $row['approved'] : 'Waiting';
                                            $preManagerApproval = isset($row['cc_manager_status']) ? $row['cc_manager_status'] : 'Waiting';
                                            $managerName = isset($row['manager_name']) ? $row['manager_name'] : 'Unknown';
                                            $preManagerName = isset($row['cc_manager_name']) ? $row['cc_manager_name'] : 'Unknown';

                                            // Format output
                                            $approvalText = [];

                                            if (empty($row['cc_manager_id']) || $row['manager_id'] == $row['cc_manager_id']) {
                                                // Case when both are "Waiting"
                                                if ($managerApproval == 'Waiting') {
                                                    echo "Pending by $managerName";
                                                }
                                                // Case when both Approved
                                                elseif ($managerApproval == 'Approved') {
                                                    echo "Approved by $managerName ";
                                                }
                                                // Case when both Denied
                                                elseif ($managerApproval == 'Denied') {
                                                    echo "Denied by $managerName";
                                                }
                                            } else {
                                                // Case when both are "Waiting"
                                                if ($managerApproval == 'Waiting' && $preManagerApproval == 'Waiting') {
                                                    echo "Pending by $managerName & $preManagerName";
                                                }
                                                // Case when both Approved
                                                elseif ($managerApproval == 'Approved' && $preManagerApproval == 'Approved') {
                                                    echo "Approved by $managerName and $preManagerName";
                                                }
                                                // Case when both Denied
                                                elseif ($managerApproval == 'Denied' && $preManagerApproval == 'Denied') {
                                                    echo "Denied by $managerName and $preManagerName";
                                                }
                                                // Case when one approved and one denied (or mixed statuses)
                                                else {
                                                    if ($managerApproval != 'Waiting') {
                                                        $approvalText[] = ucfirst($managerApproval) . " by " . $managerName;
                                                    } else {
                                                        $approvalText[] = "Pending by " . $managerName;
                                                    }

                                                    if ($preManagerApproval != 'Waiting') {
                                                        $approvalText[] = ucfirst($preManagerApproval) . " by " . $preManagerName;
                                                    } else {
                                                        $approvalText[] = "Pending by " . $preManagerName;
                                                    }

                                                    echo implode(" & ", $approvalText);
                                                }
                                            }

                                            ?>
                                        </td>








                                        <?php if ($user_role == 1 or $user_role == 3) { ?>
                                            <td class=" ">

                                                <div class="d-flex justify-content-center">

                                                    <button type="submit" class="btn btn-success btn-sm <?php echo $row['approved'] === "Approved" ? "disabled" : "";  ?>"
                                                        onclick="updateUrl(<?php echo $row['leave_id']; ?>)"
                                                        name='approve_new_leave'> Approve</button>

                                                    <button class="btn btn-danger btn-sm ml-2 <?php echo $row['approved'] === "Denied" ? "disabled" : "";  ?>" type="submit"
                                                        onclick="updateUrl(<?php echo $row['leave_id']; ?>)"
                                                        name='deny_new_leave'> Deny</button>
                                                </div>
                                            </td>
                                        <?php  } ?>
                                    </form>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="add_contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Leave Form</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="leave_type">Choose Leave</label>
                        <select class="form-control" id="leave_type" name="leave_type" required>
                            <option value="">Select Leave Type</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="manager">Manager</label>
                        <?php
                        $sql = "SELECT user_id, fullname, email FROM tbl_admin WHERE user_role = 3 OR user_role = 1";
                        $info = $obj_admin->manage_all_info($sql);
                        ?>
                        <select class="form-control" id="manager" name="manager" required>
                            <option value="">Select Project Manager</option>
                            <?php
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['user_id'] . '|' . $row['fullname'] . '|' . $row['email'] . '">' . $row['fullname'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cc_manager">CC Manager (Optional) </label>
                        <select class="form-control" name="cc_manager" id="cc_manager">
                            <option value="">-- Select CC-Manager --</option>
                            <?php
                            $query = "SELECT user_id, fullname, email FROM tbl_admin WHERE user_role = 1";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['user_id']}|{$row['fullname']}|{$row['email']}'>{$row['fullname']}</option>";
                            }
                            ?>
                        </select>

                        <div class="form-group">
                            <label for="content">Explanation</label>
                            <textarea class="form-control" id="content" name="content" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="duration">Duration</label>
                            <select class="form-control" id="duration" name="duration" required>
                                <option value="">Select Half Day or Full Day</option>
                                <option value="halfDay">Half Day</option>
                                <option value="fullDay">Full Day</option>
                            </select>
                        </div>

                        <div id="fullDayOptions"></div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                var duration = document.getElementById('duration');
                                var container = document.getElementById('fullDayOptions');
                                duration.addEventListener('change', () => {
                                    // Clear any previous options
                                    while (container.firstChild) {
                                        container.removeChild(container.firstChild);
                                    }

                                    if (duration.value === 'fullDay') {
                                        const startDiv = document.createElement('div');
                                        startDiv.className = 'form-group';
                                        const startDateInput = document.createElement('input');
                                        const startDateLabel = document.createElement('label');

                                        const endDiv = document.createElement('div');
                                        endDiv.className = 'form-group';
                                        const endDateInput = document.createElement('input');
                                        const endDateLabel = document.createElement('label');

                                        startDateLabel.textContent = 'From Date';
                                        startDateInput.type = 'date';
                                        startDateInput.className = 'form-control';
                                        startDateInput.name = 'fromDate';
                                        startDateInput.required = true;

                                        endDateLabel.textContent = 'To Date';
                                        endDateLabel.className = 'mt-3';
                                        endDateInput.type = 'date';
                                        endDateInput.className = 'form-control';
                                        endDateInput.name = 'toDate';
                                        endDateInput.required = true;

                                        startDiv.appendChild(startDateLabel);
                                        startDiv.appendChild(startDateInput);
                                        endDiv.appendChild(endDateLabel);
                                        endDiv.appendChild(endDateInput);
                                        container.appendChild(startDiv);
                                        container.appendChild(endDiv);
                                    } else {
                                        const startDiv = document.createElement('div');
                                        startDiv.className = 'form-group';
                                        const startDateInput = document.createElement('input');
                                        const startDateLabel = document.createElement('label');

                                        startDateLabel.textContent = 'Date';
                                        startDateInput.type = 'date';
                                        startDateInput.className = 'form-control';
                                        startDateInput.name = 'halfLeaveDate';
                                        startDateInput.required = true;

                                        startDiv.appendChild(startDateLabel);
                                        startDiv.appendChild(startDateInput);
                                        container.appendChild(startDiv);
                                    }
                                });
                            });
                        </script>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="leave_request">Leave Request</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateUrl(leaveId) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('leave_id', leaveId);
        const updatedQueryString = urlParams.toString();
        const newUrl = window.location.pathname + '?' + updatedQueryString;
        window.history.pushState({
            leaveId: leaveId
        }, '', newUrl);
    }


    // Function to display loading notification
    function showLoadingNotification() {
        toastr.options = {
            "positionClass": "toast-top-right", // Set the position of the notification
            "timeOut": 0, // Set timeOut to 0 for indefinite duration
            "closeButton": false, // Hide the close button
            "progressBar": true, // Show a progress bar
            "showDuration": "300", // Duration for showing the notification
            "hideDuration": "0" // Duration for hiding the notification (set to 0 for indefinite duration)
        };

        // Show the loading notification
        toastr.info("Sending E-Mail...", "Please wait");
    }

    // Event listener for the form submission
    $('form').on('submit', function() {
        // Show the loading notification when the form is submitted
        showLoadingNotification();
    });
</script>




<?php

include 'conn.php';



if (isset($_POST['leave_request'])) {

    $leave_type = $_POST['leave_type'];
    $selected_manager = $_POST['manager'];
    $selected_cc_manager = $_POST['cc_manager'];

    // Extract manager details
    $manager_parts = explode('|', $selected_manager);
    $manager_id = $manager_parts[0];
    $manager_name = $manager_parts[1];
    $manager_email = $manager_parts[2];

    // Extract CC manager details
    $cc_manager_parts = explode('|', $selected_cc_manager);
    $cc_manager_id = $cc_manager_parts[0];
    $cc_manager_name = $cc_manager_parts[1];
    $cc_manager_email = $cc_manager_parts[2];

    $content = $_POST['content'];
    $duration = $_POST['duration'];

    // Determine leave dates
    $fromDate = isset($_POST['fromDate']) ? new DateTime($_POST['fromDate']) : new DateTime($_POST['halfLeaveDate']);
    $toDate = isset($_POST['toDate']) ? new DateTime($_POST['toDate']) : new DateTime($_POST['halfLeaveDate']);

    // Calculate hours
    $hours = ($duration === 'halfDay') ? 4.62 : 0;

    if ($duration !== 'halfDay') {
        $days = [];
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($fromDate, $interval, (clone $toDate)->modify('+1 day'));

        foreach ($dateRange as $date) {
            if ($date->format('N') < 6) { // Exclude weekends
                array_push($days, $date->format('d-m-Y'));
            }
        }

        $hours = count($days) * 9.24; // Total working hours
    }

    // Format dates correctly
    $fromDateFormatted = $fromDate->format('Y-m-d');
    $toDateFormatted = (clone $toDate)->format('Y-m-d');

    // Insert into database (Removed `manager_status` & `final_status`)
    $sql = "INSERT INTO leave_approval (employee_id, employee_name, leave_type, manager_id, manager_name, manager_email, cc_manager_id, cc_manager_name, cc_manager_email, content, leave_from, leave_to, hours, leave_timestamp, cc_manager_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssssssssssd", $user_id, $user_name, $leave_type, $manager_id, $manager_name, $manager_email, $cc_manager_id, $cc_manager_name, $cc_manager_email, $content, $fromDateFormatted, $toDateFormatted, $hours);

        if ($stmt->execute()) {
            $request_id = $stmt->insert_id;


            if ($manager_id == $cc_manager_id) {

                // send to manager 
                sendEmail($manager_email, $manager_name, $request_id, "Manager", $user_name, $leave_type, $fromDateFormatted, $toDateFormatted, $hours, $content);
            } else {

                // Send emails both
                sendEmail($cc_manager_email, $cc_manager_name, $request_id, "Pre-Manager", $user_name, $leave_type, $fromDateFormatted, $toDateFormatted, $hours, $content);
                sendEmail($manager_email, $manager_name, $request_id, "Manager", $user_name, $leave_type, $fromDateFormatted, $toDateFormatted, $hours, $content);
            }


            header('Location: leave_approval.php');
            exit;
        } else {
            echo "SQL Error: " . $stmt->error;
        }
    } else {
        echo "Error: " . $conn->error;
    }
}


function sendEmail($email, $name, $request_id, $role, $user_name, $leave_type, $fromDateFormatted, $toDateFormatted, $hours, $content)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "engineering@csaengineering.com.au";
        $mail->Password = "kezfduovpirmalcs";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('revanthshiva@csaengineering.com.au', 'Leave System');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = "Leave Request Approval";

        $mail->Body = "
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    width: 100%;
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                }
                .header {
                    background-color: #007bff;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                }
                .header h2 {
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    padding: 20px;
                }
                .content p {
                    line-height: 1.6;
                    color: #333333;
                }
                .button-container {
                    text-align: center;
                    margin-top: 20px;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    background-color: #007bff;
                    color: #ffffff !important;
                    font-size: 16px;
                    text-decoration: none;
                    margin: 0 10px;
                }
                .button:hover {
                    background-color: #0056b3;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    background-color: #f4f4f4;
                    font-size: 12px;
                    color: #666666;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>$leave_type - Leave Request</h2>
                </div>
                <div class='content'>
                    <p>Dear $name,</p>
                    <p>A leave request has been submitted and requires your approval.</p>
                    <p><strong>Employee:</strong> $user_name</p>
                    <p><strong>Leave Type:</strong> $leave_type</p>
                    <p><strong>Duration:</strong> $fromDateFormatted to $toDateFormatted ($hours hours)</p>
                    <p><strong>Reason:</strong> $content</p>
                    <p>Please review the request and take action accordingly.</p>
                    <div class='button-container'>
                        <a href='csaappstore.com/request_leave_sd.php?id=$request_id&role=$role&leave_approval=approve' class='button'>Approve</a>
                        <a href='csaappstore.com/request_leave_sd.php?id=$request_id&role=$role&leave_approval=deny' class='button'>Deny</a>
                    </div>
                    <p>Thank you.</p>
                    <p>Best regards,<br>$user_name</p>
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated notification. Please do not reply to this email.</p>
            </div>
        </body>
        </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Mail Error: {$mail->ErrorInfo}";
    }
}





if (isset($_GET["id"]) && isset($_GET["role"]) && isset($_GET["status"])) {
    $leave_id = intval($_GET["id"]);
    $role = $_GET["role"];
    $status = ($_GET["status"] === 'approve') ? 'Approved' : 'Denied';

    if ($role === 'Pre-Manager') {
        $sql = "UPDATE leave_approval SET cc_manager_status = ? WHERE leave_id = ?";
    } else {
        $sql = "UPDATE leave_approval SET manager_status = ? WHERE leave_id = ?";
    }

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $status, $leave_id);
        $stmt->execute();
        header('Location: leave_approval.php');
    } else {
        echo "Error: " . $conn->error;
    }
}







if (isset($_POST["approve_new_leave"])   && isset($_GET["leave_id"])) {



    $leave_id = intval($_GET["leave_id"]);

    $approveStatus = "Approved";



    if ($leave_id <= 0) {

        die("Invalid leave ID");
    }



    $sql = "UPDATE leave_approval SET approved = ? WHERE leave_id = ?";



    $stmt = $conn->prepare($sql);

    if ($stmt === false) {

        die("Error preparing SQL statement: " . $conn->error);
    }

    if ($stmt->bind_param("si", $approveStatus, $leave_id)) {

        if ($stmt->execute()) {



            $msg_success = "Leave Status Updated";



            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {



            $msg_error = "Error: " . $stmt->error;
        }
    } else {

        die("Error binding parameters: " . $stmt->error);
    }
}







if (isset($_POST["deny_new_leave"])   && isset($_GET["leave_id"])) {



    $leave_id = intval($_GET["leave_id"]);

    $approveStatus = "Denied";



    if ($leave_id <= 0) {

        die("Invalid leave ID");
    }



    $sql = "UPDATE leave_approval SET approved = ? WHERE leave_id = ?";



    $stmt = $conn->prepare($sql);

    if ($stmt === false) {

        die("Error preparing SQL statement: " . $conn->error);
    }

    if ($stmt->bind_param("si", $approveStatus, $leave_id)) {

        if ($stmt->execute()) {



            $msg_success = "Leave Status Updated";



            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {



            $msg_error = "Error: " . $stmt->error;
        }
    } else {

        die("Error binding parameters: " . $stmt->error);
    }
}








echo "<td>";
if ($row['cc_manager_status']) {
    echo "<strong>Pre-Manager:</strong> " . $row['cc_manager_name'] . " (" . $row['cc_manager_status'] . ")<br>";
}
if ($row['manager_status']) {
    echo "<strong>Manager:</strong> " . $row['manager_name'] . " (" . $row['manager_status'] . ")";
}
echo "</td>";











include 'include/footer.php';




?>
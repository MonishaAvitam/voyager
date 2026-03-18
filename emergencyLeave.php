<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';
require './vendor/autoload.php'; // Adjust path to your autoload.php

// auth check

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];



?>

<?php include 'include/sidebar.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php'; ?>


<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Emergency Leave Application</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>


    <!-- Content Row -->

    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"></h6>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_contact">Request Form</button>
            </div>
            <div class="card-body">
                <div class="table-responsive p-3  ">
                    <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Employee Name</th>
                                <th>Date</th>
                                <th>Hours</th>
                                <th>Explanation</th>
                                <th>Manager</th>
                                <th>Approval</th>
                                <?php if ($user_role != 2) { ?>
                                    <th>Actions</th>
                                <?php  } ?>
                            </tr>
                        </thead>


                        <tbody>
                            <?php
                            $sql = "SELECT * from emergency_approval ";
                            if ($user_role == 1) {
                                // Admin can see all projects
                                $sql .= " ORDER BY em_id DESC";
                            } else {
                                $sql .= "WHERE user_id = $user_id ORDER BY em_id DESC";
                            }
                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();


                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>

                                <tr>
                                    <td><?php echo $row['em_id']  ?></td>
                                    <td><?php echo $row['full_name']  ?></td>
                                    <?php
                                    $date = DateTime::createFromFormat('Y-m-d', $row['DOL']);
                                    $formatted_date = $date ? $date->format('d-m-Y') : 'Invalid Date';
                                    ?>
                                    <td><?php echo $formatted_date; ?></td>

                                    <td><?php echo $row['hours']  ?></td>
                                    <td><?php echo $row['explanation']  ?></td>
                                    <td><?php echo $row['pm_name']  ?></td>
                                    <td><?php echo $row['emergency_leave_status']  ?></td>



                                    <?php if ($user_role != 2) { ?>

                                        <td class=" ">
                                            <form action="" method="POST">

                                                <div class="d-flex justify-content-center">

                                                    <button type="submit" class="btn btn-success btn-sm <?php echo $row['emergency_leave_status'] === "Approved" ? "disabled" : "";  ?>"
                                                        onclick="updateUrl(<?php echo $row['em_id']; ?>)" name='approve_emergency_leave'> Approve</button>

                                                    <button class="btn btn-danger btn-sm ml-2 <?php echo $row['emergency_leave_status'] === "Denied" ? "disabled" : "";  ?>" type="submit"
                                                        onclick="updateUrl(<?php echo $row['em_id']; ?>)" name='deny_emergency_leave'> Deny</button>
                                                </div>
                                            </form>
                                        </td>

                                    <?php  } ?>


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
                <h5 class="modal-title" id="exampleModalLabel">Emergency Leave</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="leave_type">Date</label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="manager">Manager</label>
                        <?php
                        $sql = "SELECT user_id, fullname FROM tbl_admin WHERE  raeAccess = 3 or raeAccess = 1";
                        $info = $obj_admin->manage_all_info($sql);
                        ?>
                        <select type="text" class="form-control" id="manager" name="manager" required>
                            <option value="">Select Project Manager</option>
                            <?php
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . $row['user_id'] . '|' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="content">Explanation</label>
                        <textarea class="form-control" id="content" name="content" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="hours">Hours</label>
                        <input type="number" class="form-control" step="0.1" id="hours" name="hours" max="2" placeholder="Enter the number of hours you are unavailable" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="request">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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





    function updateUrl(emergencyLeaveId) {



        const urlParams = new URLSearchParams(window.location.search);



        urlParams.set('emergencyleave_id', emergencyLeaveId);



        const updatedQueryString = urlParams.toString();



        const newUrl = window.location.pathname + '?' + updatedQueryString;



        window.history.pushState({

            emergencyLeaveId: emergencyLeaveId

        }, '', newUrl);



    }
</script>


<?php

include 'conn.php';




// Check if the form is submitted
if (isset($_POST['request'])) {
    // Assuming $conn is your database connection object

    // Extracting form data
    list($pm_id, $pm_name) = explode('|', $_POST['manager']);
    $content = $_POST['content'];
    $hours = $_POST['hours'];
    $date = $_POST['date'];
    $status = "Waiting";
    // Prepare and execute the SQL INSERT statement
    $sql = "INSERT INTO emergency_approval (user_id, pm_id, full_name, emergency_leave_status, DOL, hours, explanation, pm_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // SQL query to retrieve data
    $sql2 = "SELECT email FROM tbl_admin WHERE user_id = $pm_id";
    $result = $conn->query($sql2);

    // Check if there are rows returned
    if (
        $result->num_rows > 0
    ) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $sender_id = $row['email'];
        }
    } else {
        echo "0 results";
    }


    if ($stmt) {
        $stmt->bind_param("iissssss", $user_id, $pm_id, $user_name, $status, $date, $hours, $content, $pm_name);
        $stmt->execute();
        $request_id = $stmt->insert_id;

        // Send email notification to the project manager
        require './PHPMailer/src/Exception.php';
        require './PHPMailer/src/PHPMailer.php';
        require './PHPMailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();

        // SMTP configuration
        $mail->SMTPDebug = 0; // Disable debugging
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "engineering@csaengineering.com.au";
        $mail->Password = "kezfduovpirmalcs";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Sender and recipient
        $mail->setFrom('revanthshiva@csaengineering.com.au', $user_name);
        $mail->addAddress($sender_id); // Assuming $pm_email is correctly fetched

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Temporary Unavailability Notice";
        $mail->Body
            = "
        
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

                        form {
                            margin-top: 20px;
                            text-align: center;
                        }

                        button {
                            padding: 10px 20px;
                            border: none;
                            border-radius: 5px;
                            background-color: #007bff;
                            color: #ffffff;
                            font-size: 16px;
                            margin: 0 10px;
                            cursor: pointer;
                        }

                        button:hover {
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
                            <h2>Emergency Leave Request</h2>
                        </div>
                        <div class='content'>
                            <p>Dear $pm_name,</p>
                            <p>I would like to request Emergency leave from date : $date for $hours hours.</p>
                            <p>The reason for my leave is $content.</p>
                            <p>Please review the request and take action accordingly.</p>
                            <form action='csaappstore.com/request_leave.php' method='get'>
                                <input type='hidden' name='id' value='$request_id'>
                                <button type='submit' name='emergency_approval' value='approve'>Approve</button>
                                <button type='submit' name='emergency_approval' value='deny'>Deny</button>
                            </form>
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

        // Send email
        if (!$mail->send()) {
            echo 'Email could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            header('location:' . $_SERVER['HTTP_REFERER']);
        }
    } else {
        echo "Error: " . $conn->error;
    }
}











if (isset($_POST["approve_emergency_leave"])   && isset($_GET["emergencyleave_id"])) {



    $emergency_leave_id = intval($_GET["emergencyleave_id"]);

    $approveStatus = "Approved";



    if ($emergency_leave_id <= 0) {

        die("Invalid leave ID");
    }



    $sql = "UPDATE emergency_approval SET emergency_leave_status = ? WHERE em_id = ?";



    $stmt = $conn->prepare($sql);

    if ($stmt === false) {

        die("Error preparing SQL statement: " . $conn->error);
    }

    if ($stmt->bind_param("si", $approveStatus, $emergency_leave_id)) {

        if ($stmt->execute()) {



            $msg_success = "Emergency Leave Status Updated";



            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {



            $msg_error = "Error: " . $stmt->error;
        }
    } else {

        die("Error binding parameters: " . $stmt->error);
    }
}







if (isset($_POST["deny_emergency_leave"])   && isset($_GET["emergencyleave_id"])) {



    $emergency_leave_id = intval($_GET["emergencyleave_id"]);

    $approveStatus = "Denied";



    if ($emergency_leave_id <= 0) {

        die("Invalid leave ID");
    }



    $sql = "UPDATE emergency_approval SET emergency_leave_status = ? WHERE em_id = ?";



    $stmt = $conn->prepare($sql);

    if ($stmt === false) {

        die("Error preparing SQL statement: " . $conn->error);
    }

    if ($stmt->bind_param("si", $approveStatus, $emergency_leave_id)) {

        if ($stmt->execute()) {



            $msg_success = "Emergency Leave Status Updated";



            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {



            $msg_error = "Error: " . $stmt->error;
        }
    } else {

        die("Error binding parameters: " . $stmt->error);
    }
}


























?>


<?php
include 'include/footer.php';
?>
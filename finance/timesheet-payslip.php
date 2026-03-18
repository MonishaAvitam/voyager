<?php


require '../authentication.php'; // admin authentication check 

require '../conn.php';

// auth check

$user_id = $_SESSION['admin_id'];

$user_name = $_SESSION['name'];

$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {

    header('Location: ../index.php'); // Ensure correct path
}

// check admin

$user_role = $_SESSION['user_role'];

include 'include/login_header.php';

include 'include/sidebar.php';



// Set the time zone to UTC

date_default_timezone_set('Asia/Kolkata');

// Get the current date


?>


<!-- Modal -->

<head>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <style>
        td {
            text-align: center;
        }

        /* Custom CSS to increase the size of the select field */
        .select2-container--default .select2-selection--single {
            height: 38px;
            /* Adjust the height of the select field */
            width: 270px
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            /* Center the text vertically */
        }
    </style>
</head>

<!-- Performance -->

<div class="container-fluid">




    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selectedDate'])) {
        // Get the selected date value from the form
        $selected_date = $_POST['selectedDate'];

        // Extract the month and year from the selected date
        list($selected_year, $selected_month) = explode('-', $selected_date);

        // Output the selected month and year
        // Echo the variables to display them
        $selected_month;
        $selected_year;
    } else {
        // If the form is not submitted or selectedDate is not set, use current month and year
        $selected_month = date('m');
        $selected_year = date('Y');
    }


    $sql = "
SELECT 
    COALESCE((SELECT SUM(md.meetingHours) FROM meeting_data md WHERE MONTH(md.meeting_date) = $selected_month AND YEAR(md.meeting_date) = $selected_year), 0) AS totalMeetingHours,
    COALESCE((SELECT SUM(l.hours) FROM leave_approval l WHERE MONTH(l.leave_from) = $selected_month AND YEAR(l.leave_from) =  $selected_year), 0) AS totalLeaveHours,
    COALESCE(SUM(CASE WHEN ta.p_team = 'Industrial' THEN ts.working_hours ELSE 0 END), 0) AS totalWorkingHours_Industrial,
        COALESCE(SUM(CASE WHEN ta.p_team = 'Building' THEN ts.working_hours ELSE 0 END), 0) AS totalWorkingHours_Building
    FROM 
        timesheet ts 
    JOIN 
        tbl_admin ta ON ts.user_id = ta.user_id 
    WHERE 
        MONTH(ts.date_value) = $selected_month
        AND YEAR(ts.date_value) = $selected_year
        AND (ta.p_team = 'Industrial' OR ta.p_team = 'Building');
   
";



    $info = $obj_admin->manage_all_info($sql);
    $num_rows = $info->rowCount();

    if ($num_rows === 0) {
        echo '<td>No users Found</td>';
    } else {
        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
            $totalMeetingHours = number_format($row['totalMeetingHours'], 2) . 'H';
            $totalLeaveHours = number_format($row['totalLeaveHours'], 2) . 'H';

            // If user role is 1, set the totalWorkingHours variables accordingly
            $totalWorkingHoursI = number_format($row['totalWorkingHours_Industrial'], 2) . 'H';
            $totalWorkingHoursB = number_format($row['totalWorkingHours_Building'], 2) . 'H';

            // Display or process $totalMeetingHours and $totalWorkingHours as needed
        }
    }


    ?>

    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card  ">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon card-icon-large"><i class="fa fa-users"></i></div>
                    <div class="mb-4 mt-3">
                        <h5 class="card-title mb-0">Total Meeting Hours</h5>
                    </div>
                    <div class="row align-items-center mb-2 d-flex">
                        <div class="col-8">
                            <h2 class="d-flex align-items-center mb-0">
                                <?php echo $totalMeetingHours ?>
                            </h2>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4">
            <div class="card l-bg-green-dark">
                <div class="card-statistic-3 p-3">
                    <div class="card-icon card-icon-large"><i class="fa fa-briefcase"></i></div>
                    <div class="mb-4 mt-3">
                        <h5 class="card-title mb-0">Total Working Industrial Team Hours</h5>
                    </div>
                    <div class="row align-items-center mb-2 d-flex">
                        <div class="col-8">
                            <h2 class="d-flex align-items-center mb-0">
                                <?php echo $totalWorkingHoursI; ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4">
            <div class="card l-bg-green-dark">
                <div class="card-statistic-3 p-3">
                    <div class="card-icon card-icon-large"><i class="fa fa-briefcase"></i></div>
                    <div class="mb-4 mt-3">
                        <h5 class="card-title mb-0">Total Working Building Team Hours</h5>
                    </div>
                    <div class="row align-items-center mb-2 d-flex">
                        <div class="col-8">
                            <h2 class="d-flex align-items-center mb-0">
                                <?php echo $totalWorkingHoursB ?>
                            </h2>
                        </div>
                        <!-- <div class="col-4 text-right">
                            <span>10% <i class="fa fa-arrow-up"></i></span>
                        </div> -->
                    </div>
                    <!-- <div class="progress mt-1 " data-height="8" style="height: 8px;">
                        <div class="progress-bar l-bg-orange" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%;"></div>
                    </div> -->
                </div>
            </div>
        </div>




        <div class="col-xl-3 col-lg-4">
            <div class="card l-bg-green-dark">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon card-icon-large"><i class="fas fa-ticket-alt"></i></div>
                    <div class="mb-4 mt-3">
                        <h5 class="card-title mb-0">Total Leave Hours</h5>
                    </div>
                    <div class="row align-items-center mb-2 d-flex">
                        <div class="col-8">
                            <h2 class="d-flex align-items-center mb-0">
                                <?php echo $totalLeaveHours ?>
                            </h2>
                        </div>
                        <!-- <div class="col-4 text-right">
                            <span>10% <i class="fa fa-arrow-up"></i></span>
                        </div> -->
                    </div>
                    <!-- <div class="progress mt-1 " data-height="8" style="height: 8px;">
                        <div class="progress-bar l-bg-orange" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%;"></div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5 mt-5 d-flex justify-content-center align-items-center">
    <!-- HTML form -->
    <div class="col-9">
        <h3 class="text-primary">Showing Data for <?php echo isset($_POST['selectedDate']) ? date('F Y', strtotime($_POST['selectedDate'])) : date('F Y'); ?></h3>
    </div>
    <div class="col-3">
        <form method="POST" action="">
            <label for="selectedDate">Select Month and Year:</label>
            <div class="d-flex">
                <input type="month" class="form-control" name="selectedDate" id="selectedDate" required value="<?php echo isset($_POST['selectedDate']) ? $_POST['selectedDate'] : date('Y-m'); ?>">
                <button class="btn btn-sm btn-primary ml-2" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($_POST['selectedDate'])): ?>
    <form method="POST" action="timesheet_excel.php">
        <input type="hidden" name="selectedDate" value="<?php echo $_POST['selectedDate']; ?>">
        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" type="submit" style="background-color: #072745; color: #fff;">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Excel
        </button>
    </form>
<?php endif; ?>

    <div class="card mt-2">
        <div class="card-header">
            <div class="col-6 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo "Performance Table   " . '(' . $selected_month_name = date('F', mktime(0, 0, 0,$selected_month, 1)).')' ?></h6>
            </div>
        </div>
        <div class="card-body">

            <div class="table-responsive p-3 ">
                <table class="table table-bordered " id="performance" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Meeting Hours</th>
                            <th>Working Hours</th>
                            <th>Small Job Hours</th>
                            <th>Leave Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $sql = "
                                SELECT 
                                ta.fullname,
                                ta.user_id,
                                IFNULL(ROUND(SUM(md.meetingHours), 2), 0) AS ETMH,  -- Rounded total meeting hours to 2 decimal places
                                IFNULL(ROUND((SELECT SUM(ts.working_hours) FROM timesheet ts WHERE ts.user_id = ta.user_id AND MONTH(ts.date_value) = $selected_month AND YEAR(ts.date_value) = $selected_year), 2), 0) AS ETWH, 
                                IFNULL(ROUND((SELECT SUM(sj.workingHours) FROM smallJobs sj WHERE sj.user_id = ta.user_id AND MONTH(sj.job_date) = $selected_month AND YEAR(sj.job_date) = $selected_year), 2), 0) AS ETSH, 
                                IFNULL(ROUND((SELECT SUM(l.hours) FROM leave_approval l WHERE l.employee_id = ta.user_id AND MONTH(l.leave_from) = $selected_month AND YEAR(l.leave_from) = $selected_year), 2), 0) AS ETLH   
                            FROM 
                                tbl_admin ta 
                            LEFT JOIN 
                                meeting_data md ON md.employee_id = ta.user_id AND MONTH(md.meeting_date) = $selected_month AND YEAR(md.meeting_date) = $selected_year
                            GROUP BY
                                ta.fullname, ta.user_id;                            
                            ";

                        $info = $obj_admin->manage_all_info($sql);
                        $num_rows = $info->rowCount();

                        if ($num_rows === 0) {
                            echo '<td>No users Found</td>';
                        } else {
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                                <tr>
                                    <td><?php echo $row['fullname'] ?></td>
                                    <td><?php echo $row['ETMH'] ?></td>
                                    <td><?php echo $row['ETWH'] ?></td>
                                    <td><?php echo $row['ETSH'] ?></td>
                                    <td><?php echo $row['ETLH'] ?></td>
                                </tr>
                        <?php      }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>







<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>












<!-- Meeting Table -->






















<script>
    function updateUrl(projectId) {
        // Parse the current URL's query parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Set the 'progress_id' parameter with the specified projectId
        urlParams.set('progress_id', projectId);
        // Get the updated query string
        const updatedQueryString = urlParams.toString();
        // Construct the new URL with the updated query string
        const newUrl = window.location.pathname + '?' + updatedQueryString;
        // Use pushState to update the URL without reloading the page
        window.history.pushState({
            projectId: projectId
        }, '', newUrl);
    }

    function confirmDelete(meetingId) {
        var confirmation = confirm("Are you sure you want to delete this meeting?");
        if (confirmation) {
            // If user confirms, update the URL and proceed with form submission
            updateUrlmeeting(meetingId);
            return true;
        } else {
            // If user cancels, prevent form submission
            return false;
        }
    }

    function confirmDeletejobs(job_id) {
        var confirmation = confirm("Are you sure you want to delete this job?");
        if (confirmation) {
            // If user confirms, update the URL and proceed with form submission
            updateUrlsmalljobs(job_id);
            return true;
        } else {
            // If user cancels, prevent form submission
            return false;
        }
    }

    function updateUrlmeeting(meetingId) {
        // Parse the current URL's query parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Set the 'progress_id' parameter with the specified projectId
        urlParams.set('meeting_id', meetingId);
        // Get the updated query string
        const updatedQueryString = urlParams.toString();
        // Construct the new URL with the updated query string
        const newUrl = window.location.pathname + '?' + updatedQueryString;
        // Use pushState to update the URL without reloading the page
        window.history.pushState({
            meetingId: meetingId
        }, '', newUrl);

    }

    function updateUrlsmalljobs(job_id) {
        // Parse the current URL's query parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Set the 'progress_id' parameter with the specified projectId
        urlParams.set('job_id', job_id);
        // Get the updated query string
        const updatedQueryString = urlParams.toString();
        // Construct the new URL with the updated query string
        const newUrl = window.location.pathname + '?' + updatedQueryString;
        // Use pushState to update the URL without reloading the page
        window.history.pushState({
            job_id: job_id
        }, '', newUrl);

    }
</script>

<?php







include 'include/footer.php';
?>

<!-- Add a click event handler to the <td> element -->
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

include 'include/login_header.php';

include 'include/sidebar.php';



// Set the time zone to UTC

date_default_timezone_set('Asia/Kolkata');

// Get the current date

$today = date('Y-m-d');

$yesterday = date('Y-m-d', strtotime('-1 day'));

$day3 = date('Y-m-d', strtotime('-2 day'));

$day4 = date('Y-m-d', strtotime('-3 day'));

$day5 = date('Y-m-d', strtotime('-4 day'));

$day6 = date('Y-m-d', strtotime('-5 day'));

$day7 = date('Y-m-d', strtotime('-6 day'));

?>
<?php
function retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id)
{
    $sql = "SELECT working_hours FROM timesheet WHERE project_id_timesheet = '$project_id' AND date_value = '$dateToRetrieve' AND user_id=$user_id ";

    // Execute the query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Output data, showing the date_value
        while ($row = $result->fetch_assoc()) {
            echo $row["working_hours"] . "" . "<br>"; // Output the working hours
        }
    } else {
        // No data found for the specific date and project
        echo "";
    }
}
function retrieve_leave_data($dateToRetrieve, $conn)
{
    $sql = "SELECT leave_type,employee_name FROM leave_approval WHERE  leave_from = '$dateToRetrieve'";

    // Execute the query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Output data, showing the date_value
        while ($row = $result->fetch_assoc()) {
            // echo $row["leave_type"] .  "<br>"; // Output the working hours
            // echo $row["employee_name"] .  "<br>"; // Output the working hours
            echo '<span type="button"  data-bs-toggle="modal" data-bs-target="#leave-info" class="badge badge-pill badge-danger">Leave info</span>';
        }
    } else {
        // No data found for the specific date and project
        echo "";
    }
}
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
        height: 38px; /* Adjust the height of the select field */
        width: 270px
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px; /* Center the text vertically */
    }

</style>
</head>

<!-- Performance -->

<div class="container-fluid">

    <?php


    if ($user_role == 1) {
        $sql = "
SELECT 
    COALESCE((SELECT SUM(md.meetingHours) FROM meeting_data md WHERE MONTH(md.meeting_date) = MONTH(CURDATE()) AND YEAR(md.meeting_date) = YEAR(CURDATE())), 0) AS totalMeetingHours,
    COALESCE((SELECT SUM(l.hours) FROM leave_data l WHERE MONTH(l.leave_date) = MONTH(CURDATE()) AND YEAR(l.leave_date) = YEAR(CURDATE())), 0) AS totalLeaveHours,
    COALESCE(SUM(CASE WHEN ta.p_team = 'Industrial' THEN ts.working_hours ELSE 0 END), 0) AS totalWorkingHours_Industrial,
        COALESCE(SUM(CASE WHEN ta.p_team = 'Building' THEN ts.working_hours ELSE 0 END), 0) AS totalWorkingHours_Building
    FROM 
        timesheet ts 
    JOIN 
        tbl_admin ta ON ts.user_id = ta.user_id 
    WHERE 
        MONTH(ts.date_value) = MONTH(CURDATE()) 
        AND YEAR(ts.date_value) = YEAR(CURDATE()) 
        AND (ta.p_team = 'Industrial' OR ta.p_team = 'Building');
   
";
    } else {
        $sql = "
SELECT 
    COALESCE((SELECT SUM(md.meetingHours) FROM meeting_data md WHERE md.employee_id = $user_id AND MONTH(md.meeting_date) = MONTH(CURDATE()) AND YEAR(md.meeting_date) = YEAR(CURDATE())), 0) AS totalMeetingHours,
    COALESCE((SELECT SUM(l.hours) FROM leave_data l WHERE l.employee_id = $user_id AND MONTH(l.leave_date) = MONTH(CURDATE()) AND YEAR(l.leave_date) = YEAR(CURDATE())), 0) AS totalLeaveHours,
    COALESCE((SELECT SUM(ts.working_hours) FROM timesheet ts WHERE ts.user_id =$user_id AND MONTH(ts.date_value) = MONTH(CURDATE()) AND YEAR(ts.date_value) = YEAR(CURDATE())), 0) AS totalWorkingHours,
    COALESCE((SELECT SUM(sj.workingHours) FROM smallJobs sj WHERE sj.user_id =$user_id AND MONTH(sj.job_date) = MONTH(CURDATE()) AND YEAR(sj.job_date) = YEAR(CURDATE())), 0) AS totalSmallHours;
";
    }


    $info = $obj_admin->manage_all_info($sql);
    $num_rows = $info->rowCount();

    if ($num_rows === 0) {
        echo '<td>No users Found</td>';
    } else {
        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
            $totalMeetingHours = number_format( $row['totalMeetingHours'] ,2) . 'H';
            $totalLeaveHours = number_format($row['totalLeaveHours'] ,2) . 'H';

            if ($user_role == 1) {
                // If user role is 1, set the totalWorkingHours variables accordingly
                $totalWorkingHoursI = number_format($row['totalWorkingHours_Industrial'],2) . 'H';
                $totalWorkingHoursB = number_format( $row['totalWorkingHours_Building'],2) . 'H';
            } else {
                // Calculate and set totalSmallHours when user role is not 1
                $totalSmallJobHours = number_format($row['totalSmallHours'] ,2). 'H';
                $totalWorkingHours = number_format($row['totalWorkingHours'],2) . 'H';

            }

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

        <?php if ($user_role == 1) { ?>
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
        <?php } ?>

        <?php if ($user_role != 1) { ?>
            <div class="col-xl-3 col-lg-4">
                <div class="card l-bg-green-dark">
                    <div class="card-statistic-3 p-4">
                        <div class="card-icon card-icon-large"><i class="fa fa-briefcase"></i></div>
                        <div class="mb-4 mt-3">
                            <h5 class="card-title mb-0">Total Working Hours</h5>
                        </div>
                        <div class="row align-items-center mb-2 d-flex">
                            <div class="col-8">
                                <h2 class="d-flex align-items-center mb-0">
                                    <?php echo $totalWorkingHours; ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4">
                <div class="card l-bg-green-dark">
                    <div class="card-statistic-3 p-4">
                        <div class="card-icon card-icon-large"><i class="fa fa-briefcase"></i></div>
                        <div class="mb-4 mt-3">
                            <h5 class="card-title mb-0">Total Small Job Hours</h5>
                        </div>
                        <div class="row align-items-center mb-2 d-flex">
                            <div class="col-8">
                                <h2 class="d-flex align-items-center mb-0">
                                    <?php echo $totalSmallJobHours ?>
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
        <?php } ?>


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


    <?php if ($user_role == 1) {  ?>
        <div class="card mt-2">
            <div class="card-header">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo "Performance Table   " . '(' . date('F') . ')' ?></h6>
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

                            if ($user_role == 1) {
                                $sql = "
                                SELECT 
                                ta.fullname,
                                ta.user_id,
                                IFNULL(ROUND(SUM(md.meetingHours), 2), 0) AS ETMH,  -- Rounded total meeting hours to 2 decimal places
                                IFNULL(ROUND((SELECT SUM(ts.working_hours) FROM timesheet ts WHERE ts.user_id = ta.user_id AND MONTH(ts.date_value) = MONTH(CURDATE()) AND YEAR(ts.date_value) = YEAR(CURDATE())), 2), 0) AS ETWH, 
                                IFNULL(ROUND((SELECT SUM(sj.workingHours) FROM smallJobs sj WHERE sj.user_id = ta.user_id AND MONTH(sj.job_date) = MONTH(CURDATE()) AND YEAR(sj.job_date) = YEAR(CURDATE())), 2), 0) AS ETSH, 
                                IFNULL(ROUND((SELECT SUM(l.hours) FROM leave_data l WHERE l.employee_id = ta.user_id AND MONTH(l.leave_date) = MONTH(CURDATE()) AND YEAR(l.leave_date) = YEAR(CURDATE())), 2), 0) AS ETLH   
                            FROM 
                                tbl_admin ta 
                            LEFT JOIN 
                                meeting_data md ON md.employee_id = ta.user_id AND MONTH(md.meeting_date) = MONTH(CURDATE()) AND YEAR(md.meeting_date) = YEAR(CURDATE())
                            GROUP BY
                                ta.fullname, ta.user_id;
                            

                                
                            
                            ";
                            } else {
                                $sql = "
                                SELECT 
                                ta.fullname, 
                                ta.user_id, 
                                ROUND(IFNULL(SUM(md.meetingHours), 0), 2) AS ETMH,  -- Rounded total meeting hours to 2 decimal places
                                ROUND(IFNULL((SELECT SUM(ts.working_hours) FROM timesheet ts WHERE ts.user_id = ta.user_id AND MONTH(ts.date_value) = MONTH(CURDATE()) AND YEAR(ts.date_value) = YEAR(CURDATE())), 0), 2) AS ETWH, 
                                ROUND(IFNULL((SELECT SUM(sj.workingHours) FROM smallJobs sj WHERE sj.user_id = ta.user_id AND MONTH(sj.job_date) = MONTH(CURDATE()) AND YEAR(sj.job_date) = YEAR(CURDATE())), 0), 2) AS ETSH,  
                                ROUND(IFNULL((SELECT SUM(l.hours) FROM leave_data l WHERE l.employee_id = ta.user_id AND MONTH(l.leave_date) = MONTH(CURDATE()) AND YEAR(l.leave_date) = YEAR(CURDATE())), 0), 2) AS ETLH   
                            FROM 
                                tbl_admin ta 
                            LEFT JOIN 
                                meeting_data md ON md.employee_id = ta.user_id AND MONTH(md.meeting_date) = MONTH(CURDATE()) AND YEAR(md.meeting_date) = YEAR(CURDATE())
                            WHERE 
                                ta.user_id = $user_id
                            GROUP BY
                                ta.fullname, ta.user_id;
                            
";
                            }
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
    <?php  }  ?>
</div>



<div class="container-fluid mt-5">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Project Booking (Hrs)</h6>
                </div>
                <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                    <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                    <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
                    <button class="btn btn-primary" data-target="#additional_engineer" data-toggle="modal">Add Project for Additional Engineer</button>
                    <button class="btn btn-warning ml-3" data-target="#remove_additional_engineer" data-toggle="modal">Remove Project for Additional Engineer</button>

                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive p-3">
                <table class="table table-bordered table-sm" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Title</th>
                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                                <!-- <?php
                                        $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                        retrieve_leave_data($dateToRetrieve, $conn);
                                        ?> -->
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Project Title</th>
                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                            </th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, pm.fullname AS project_manager_name, c.contact_name, c.contact_email, c.contact_phone_number, c.contact_id, c.customer_id
                         FROM projects p
                         LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
                         LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
                         WHERE 1=1";

                        $sql2 = "SELECT sp.*, pm.fullname AS subproject_manager_name, c.contact_name, c.contact_email, c.contact_phone_number, c.contact_id, c.customer_id
                         FROM subprojects sp
                         LEFT JOIN project_managers pm ON (sp.project_manager = pm.fullname)
                         LEFT JOIN contacts c ON (sp.contact_id = c.contact_id)
                         WHERE 1=1";

                        // Conditions for projects based on user roles
                        if ($user_role == 1) {
                            // Admin can see all projects, so no additional conditions needed
                        } elseif ($user_role == 2) {

                            $sql .= " AND (
                        (p.assign_to_id = '$user_id' AND (p.verify_status = '0' OR p.verify_status IS NULL)) 
                             OR p.verify_by = '$user_id' 
                         OR (p.assign_to_status = 1 AND JSON_CONTAINS(p.additional_engineers, '\"$user_id\"' ))
                            )";
                        } elseif ($user_role == 3) {
                            $sql .= " AND (p.project_managers_id = '$user_id' OR p.verify_by = '$user_id' OR p.assign_to_id = '$user_id')";
                        } 

                        $sql .= " ORDER BY p.project_id DESC";

                        // Conditions for subprojects based on user roles
                        if ($user_role == 1) {
                            // Admin can see all subprojects, so no additional conditions needed
                        } elseif ($user_role == 2) {
                            $sql2 .= " AND ((sp.assign_to_id = '$user_id' AND (sp.verify_status = '0' OR sp.verify_status IS NULL)) OR sp.verify_by = '$user_id') AND sp.assign_to_status = 1";
                        } elseif ($user_role == 3) {
                            $sql2 .= " AND (sp.project_managers_id = '$user_id' OR sp.verify_by = '$user_id' OR sp.assign_to_id = '$user_id')";
                        } 

                        $sql2 .= " ORDER BY sp.project_id DESC";

                        // Fetching information for projects and subprojects separately
                        $info_projects = $obj_admin->manage_all_info($sql);
                        $info_subprojects = $obj_admin->manage_all_info($sql2);

                        // Combining fetched data from both queries (projects and subprojects)
                        $combined_info = array();
                        while ($row = $info_projects->fetch(PDO::FETCH_ASSOC)) {
                            $combined_info[] = $row;
                        }
                        while ($row = $info_subprojects->fetch(PDO::FETCH_ASSOC)) {
                            $combined_info[] = $row;
                        }

                        // Sorting the combined information array by project_id or subproject_id (adjust the sorting key based on your database structure)
                        usort($combined_info, function ($a, $b) {
                            return $b['project_id'] <=> $a['project_id'];
                        });

                        // Displaying the combined data
                        foreach ($combined_info as $row) {
                            // Process and display the combined data

                        ?>
                            <tr>
                                <td style="background-color: <?php echo $row['urgency']; ?>; text-align: center; width: 5%; border-radius: 5px; color: <?php echo ($row['urgency'] === 'white') ? '#000' : '#fff'; ?>">
                                    <?php echo $row['project_id']; ?>
                                    <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>
                                    <span class="badge badge-pill badge-danger">
                                        <?php if ($row['subproject_status'] != NULL) {
                                            echo 'S' . $row['subproject_status'];
                                        } ?></span>

                                </td>
                                <td style="width: 30%;">

                                    <?php if ($row['subproject_status'] != Null) {
                                        echo $row['subproject_name'];
                                    } else {
                                        echo $row['project_name'];
                                    } ?>
                                </td>

                                <td style="cursor: pointer;" data-toggle="modal" data-target="#day3_modal" data-project-id="<?php echo $row['project_id']; ?>" onclick="updateUrl(<?php if ($row['subproject_status'] != NULL) {
                                                                                                                                                                                        echo $row['table_id'];
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $row['project_id'];
                                                                                                                                                                                    }                                       ?>)">
                                    <?php
                                    if ($row['subproject_status'] != Null) {
                                        $project_id = $row['table_id'];
                                    } else {
                                        $project_id = $row['project_id']; // Replace with the actual project ID

                                    }
                                    $dateToRetrieve = date('Y-m-d', strtotime('-2 day')); // Replace with your desired date

                                    retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id)
                                    ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#yesterday_modal" data-project-id="<?php echo $row['project_id']; ?>" onclick="updateUrl(<?php if ($row['subproject_status'] != NULL) {
                                                                                                                                                                                            echo $row['table_id'];
                                                                                                                                                                                        } else {
                                                                                                                                                                                            echo $row['project_id'];
                                                                                                                                                                                        } ?>)">
                                    <?php
                                    if ($row['subproject_status'] != null) {
                                        $project_id = $row['table_id'];
                                    } else {
                                        $project_id = $row['project_id']; // Replace with the actual project ID
                                    }
                                    $dateToRetrieve = date('Y-m-d', strtotime('-1 day')); // Replace with your desired date

                                    retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id);
                                    ?>
                                </td>

                                <td style="cursor: pointer;" data-toggle="modal" data-target="#today_modal" data-project-id="<?php echo $row['project_id']; ?>" onclick="updateUrl(<?php if ($row['subproject_status'] != NULL) {
                                                                                                                                                                                        echo $row['table_id'];
                                                                                                                                                                                    } else {
                                                                                                                                                                                        echo $row['project_id'];
                                                                                                                                                                                    }                                       ?>)">
                                    <?php
                                    if ($row['subproject_status'] != Null) {
                                        $project_id = $row['table_id'];
                                    } else {
                                        $project_id = $row['project_id']; // Replace with the actual project ID

                                    }

                                    $dateToRetrieve = date('Y-m-d'); // Replace with your desired date



                                    retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id);

                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<div class="modal fade bd-progress-modal-sm" id="today_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">

                    <label class="control-label text-dark" for="">Set Work Hours</label>

                    <input type="text" name="date_value" value="<?php echo $today; ?>" hidden>

                    <input class="form-control" type="number" name="work_hours" max="24" min="0" step="any">

                    <button class="btn btn-primary mt-2" name="timesheet_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade bd-progress-modal-sm" id="yesterday_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">

                    <label class="control-label text-dark" for="">Set Work Hours</label>

                    <input type="text" name="date_value" name="date_value" value="<?php echo $yesterday; ?>" hidden>

                    <input class="form-control" type="number" name="work_hours" max="24" min="0" step="any">

                    <button class="btn btn-primary mt-2" name="timesheet_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade bd-progress-modal-sm" id="day3_modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">

                    <label class="control-label text-dark" for="">Set Work Hours</label>

                    <input type="text" name="date_value" value="<?php echo $day3; ?>" hidden>

                    <input class="form-control" type="number" name="work_hours" max="24" min="0" step="any">

                    <button class="btn btn-primary mt-2" name="timesheet_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>


<!-- Additional Engineer Modal  -->


<div class="modal fade" id="additional_engineer" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select Project</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="project_select" class="control-label text-dark">Select Project</label>
                        <select id="project_select" class="form-control select2" name="project_id">
                            <?php
                            $sql = "SELECT * From projects ";


                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No projects were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <option value="<?php echo $row['project_id'] ?>"><?php echo $row['project_name']  ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="additional_engineer">ADD Project</button>
                    </div>
                </form>



            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
    $('.select2').select2();
});
</script>

<!-- Remove Additional Engineer Modal  -->
<div class="modal fade" id="remove_additional_engineer" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select Project</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="project_select" class="control-label text-dark">Select Project</label>
                        <select id="project_select" class="form-control" name="project_id">
                            <?php
                            $sql = "SELECT * FROM projects WHERE JSON_CONTAINS(additional_engineers, '\"$user_id\"')";


                            $info = $obj_admin->manage_all_info($sql);
                            $serial  = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No projects were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <option value="<?php echo $row['project_id'] ?>"><?php echo $row['project_name']  ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="remove_additional_engineer">Remove Project</button>
                    </div>
                </form>



            </div>
        </div>
    </div>
</div>




<!-- Small Jobs -->

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Small Jobs (Hrs)</h6>
                </div>
                <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                    <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                    <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive ">
                <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>

                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                                <!-- <?php
                                        $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                        retrieve_leave_data($dateToRetrieve, $conn);
                                        ?> -->
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>


                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                            </th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        // SQL query to retrieve project details based on project_id
                        $sql = "SELECT job_date, SUM(workingHours) AS totalJobHours  FROM smallJobs WHERE user_id = $user_id GROUP BY job_date  ";
                        $info = $obj_admin->manage_all_info($sql);
                        $num_rows = $info->rowCount();
                        if ($num_rows > 0) {
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {


                                if ($row['job_date'] == $day3) {
                                    $day3job_hours =
                                        number_format($row['totalJobHours'], 2); // Store the numeric value without concatenating 'H'
                                } elseif ($row['job_date'] == $yesterday) {
                                    $yesterdayjob_hours =
                                        number_format($row['totalJobHours'], 2); // Store the numeric value without concatenating 'H'
                                } elseif ($row['job_date'] == $today) {
                                    $todayjob_hours = number_format($row['totalJobHours'], 2); // Store the numeric value without concatenating 'H'
                                }


                                // Additional check to see if variables are set
                                if (!isset($day3job_hours)) {
                                    $day3job_hours = "No data";
                                }
                                if (!isset($yesterdayjob_hours)) {
                                    $yesterdayjob_hours = "No data";
                                }
                                if (!isset($todayjob_hours)) {
                                    $todayjob_hours = "No data";
                                }
                            }

                        ?>
                            <tr>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#day3JobModal">
                                    <?php echo $day3job_hours; ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#yesterdayJobModal">
                                    <?php echo $yesterdayjob_hours; ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#todayJobModal">
                                    <?php echo $todayjob_hours; ?>
                                </td>
                            </tr>

                        <?php

                        } else { ?>
                            <tr>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#day3JobModal"> No Data
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#yesterdayJobModal"> No Data
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#todayJobModal"> No Data
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>


                </table>
            </div>
            <p class="text-warning"> * Click Cell To Add Leave </p>

        </div>
    </div>
</div>

<!-- Small Jobs Modal -->
<div class="modal fade bd-progress-modal-sm" id="todayJobModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <input type="text" name="date_value" value="<?php echo $today; ?>" hidden>

                    <label class="control-label text-dark" for="">Job Details</label>

                    <input type="text" name="job_name" id="job_name" maxlength="255" class="form-control" placeholder="Job Name">

                    <textarea name="jobDetails" id="jobDetails" cols="10" rows="5" class="form-control mt-2" placeholder="Job Description"></textarea>
                    <label class="control-label text-dark mt-2" for="">Working Hours</label>

                    <input class="form-control " type="number" value="24" name="workingHours" max="24" min="0" step="any">

                    <button class="btn btn-primary mt-2" name="job_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>
<div class="modal fade bd-progress-modal-sm" id="yesterdayJobModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <input type="text" name="date_value" value="<?php echo $yesterday; ?>" hidden>

                    <label class="control-label text-dark" for="">Job Details</label>

                    <input type="text" name="job_name" id="job_name" maxlength="255" class="form-control" placeholder="Job Name">

                    <textarea name="jobDetails" id="jobDetails" cols="10" rows="5" class="form-control mt-2" placeholder="Job Description"></textarea>
                    <label class="control-label text-dark mt-2" for="">Working Hours</label>

                    <input class="form-control " type="number" value="24" name="workingHours" max="24" min="0" step="any">

                    <button class="btn btn-primary mt-2" name="job_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>
<div class="modal fade bd-progress-modal-sm" id="day3JobModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <input type="text" name="date_value" value="<?php echo $day3; ?>" hidden>

                    <label class="control-label text-dark" for="">Job Details</label>

                    <input type="text" name="job_name" id="job_name" maxlength="255" class="form-control" placeholder="Job Name">

                    <textarea name="jobDetails" id="jobDetails" cols="10" rows="5" class="form-control mt-2" placeholder="Job Description"></textarea>
                    <label class="control-label text-dark mt-2" for="">Working Hours</label>

                    <input class="form-control " type="number" value="24" name="workingHours" max="24" min="0" step="any">


                    <button class="btn btn-primary mt-2" name="job_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- leave table -->

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Leave Table (Hrs)</h6>
                </div>
                <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                    <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                    <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered " id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>

                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                                <!-- <?php
                                        $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                        retrieve_leave_data($dateToRetrieve, $conn);
                                        ?> -->
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>


                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                            </th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        // SQL query to retrieve project details based on project_id
                        $sql = "SELECT * FROM leave_data WHERE employee_id = $user_id AND leave_date ";
                        $info = $obj_admin->manage_all_info($sql);
                        $num_rows = $info->rowCount();
                        if ($num_rows > 0) {
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                if ($row['leave_date'] == $day3) {
                                    $day3_hours = $row['leave_type'] . ' ' . $row['hours'] . 'H';
                                } elseif ($row['leave_date'] == $yesterday) {
                                    $yesterday_hours = $row['leave_type'] . ' ' . $row['hours'] . 'H';
                                } elseif ($row['leave_date'] == $today) {
                                    $today_hours = $row['leave_type'] . ' ' . $row['hours'] . 'H';
                                }

                                // Additional check to see if variables are set
                                if (!isset($day3_hours)) {
                                    $day3_hours = "No data";
                                }
                                if (!isset($yesterday_hours)) {
                                    $yesterday_hours = "No data";
                                }
                                if (!isset($today_hours)) {
                                    $today_hours = "No data";
                                }
                            }

                        ?>
                            <tr>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#day3LeaveModal">
                                    <?php echo $day3_hours; ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#yesterdayLeaveModal">
                                    <?php echo $yesterday_hours; ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#todayLeaveModal">
                                    <?php echo $today_hours; ?>
                                </td>
                            </tr>

                        <?php

                        } else { ?>
                            <tr>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#day3LeaveModal">
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#yesterdayLeaveModal">
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#todayLeaveModal">
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>


                </table>
            </div>
            <p class="text-warning"> * Click Cell To Add Leave </p>

        </div>
    </div>
</div>


<!-- Leave Modals  -->

<div class="modal fade bd-progress-modal-sm" id="todayLeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label text-dark" for="">Set Leave Type</label>


                    <input type="text" name="date_value" value="<?php echo $today; ?>" hidden>

                    <select name="leave_type" class="form-control">
                        <option value="">Choose Leave Type</option>
                        <option>Public Holidays</option>
                        <option>Sick Leave</option>
                        <option>Annual Leave</option>
                    </select>

                    <label class="control-label text-dark mt-2" for="">Set Leave Hours</label>

                    <input class="form-control" type="number" value="9.25" name="leave_hours" max="24" min="0" step="0.01">

                    <button class="btn btn-primary mt-2" name="leave_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade bd-progress-modal-sm" id="yesterdayLeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">

                    <label class="control-label text-dark" for="">Set Leave Type</label>

                    <input type="text" name="date_value" value="<?php echo $yesterday; ?>" hidden>

                    <select name="leave_type" class="form-control">
                        <option value="">Choose Leave Type</option>
                        <option>Public Holidays</option>
                        <option>Sick Leave</option>
                        <option>Annual Leave</option>
                    </select>

                    <label class="control-label text-dark mt-2" for="">Set Leave Hours</label>


                    <input class="form-control" type="number" value="9.25" name="leave_hours" max="24" min="0" step="0.01">

                    <button class="btn btn-primary mt-2" name="leave_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade bd-progress-modal-sm" id="day3LeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">

                    <label class="control-label text-dark" for="">Set Leave Type</label>

                    <input type="text" name="date_value" value="<?php echo $day3; ?>" hidden>

                    <select name="leave_type" class="form-control">
                        <option value="">Choose Leave Type</option>
                        <option>Public Holidays</option>
                        <option>Sick Leave</option>
                        <option>Annual Leave</option>
                    </select>
                    <label class="control-label text-dark mt-2" for="">Set Leave Type</label>

                    <input class="form-control " type="number" value="9.25" name="leave_hours" max="24" min="0">

                    <button class="btn btn-primary mt-2" name="leave_data">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>







<!-- Meeting Table -->



<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Meeting Table (Hrs)</h6>
                </div>
                <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                    <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                    <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="" width="100%" cellspacing="0">
                    <thead>
                        <tr>

                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                                <!-- <?php
                                        $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                        retrieve_leave_data($dateToRetrieve, $conn);
                                        ?> -->
                            </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>


                            <th><?php echo $day3; ?></th>
                            <th>Yesterday</th>
                            <th>Today
                            </th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        // SQL query to retrieve project details based on project_id
                        $sql = "SELECT 
                        meeting_date,
                         SUM(meetingHours) AS total_duration
                            FROM 
                          meeting_data 
                            WHERE 
                           employee_id = $user_id
                            GROUP BY 
                          meeting_date;
                            ";
                        $info = $obj_admin->manage_all_info($sql);
                        $num_rows = $info->rowCount();

                        if ($num_rows >= 0) {
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                if ($row['meeting_date'] === $day3) {
                                    $day3TotalDuration = number_format($row['total_duration'], 2);
                                }
                                if ($row['meeting_date'] === $yesterday) {
                                    $yesterdayTotalDuration =
                                        number_format($row['total_duration'], 2);
                                }
                                if ($row['meeting_date'] === $today) {
                                    $todayTotalDuration =
                                        number_format($row['total_duration'], 2);
                                }
                            }



                        ?>
                            <tr>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#day3MeetingModal">
                                    <?php
                                    echo isset($day3TotalDuration) ? $day3TotalDuration : 'No Data';

                                    ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#yesterdayMeetingModal">
                                    <?php
                                    echo isset($yesterdayTotalDuration) ? $yesterdayTotalDuration : 'No Data';

                                    ?>
                                </td>
                                <td style="cursor: pointer;" data-toggle="modal" data-target="#todayMeetingModal">
                                    <?php
                                    echo isset($todayTotalDuration) ? $todayTotalDuration : 'No Data';

                                    ?>
                                </td>
                            </tr>

                        <?php
                        } ?>
                    </tbody>


                </table>
            </div>
            <p class="text-warning"> * Click Cell To Add Meeting </p>

        </div>
    </div>
</div>


<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Meeting Info (Hrs)</h6>
                </div>
                <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                    <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                    <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive p-3 ">
                <table class="table table-bordered table-sm " id="meeting_info" width="100%" cellspacing="0">
                    <thead>
                        <tr>

                            <th>Meeting Id</th>
                            <th>Meeting Date </th>

                            <th>Meeting Total Duration</th>
                            <th>Meeting Purpose</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>


                            <th>Meeting Id</th>
                            <th>Meeting Date </th>
                            <th>Meeting Total Duration</th>
                            <th>Meeting Purpose</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>

                        <?php
                        // SQL query to retrieve project details based on project_id
                        $sql = "SELECT * FROM meeting_data WHERE employee_id = $user_id ORDER BY meeting_id DESC";


                        $info = $obj_admin->manage_all_info($sql);
                        $num_rows = $info->rowCount();


                        if ($num_rows > 0) {
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        ?>


                                <tr>
                                    <td style="width: 5%;">
                                        <?php echo $row['meeting_id']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['meeting_date']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['meetingHours']; ?>

                                    </td>
                                    <td>
                                        <?php echo $row['meeting_purpose']; ?>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <button type="submit" name="delete_meeting" onclick="return confirmDelete(<?php echo $row['meeting_id'] ?>)" class="btn btn-primary">Delete</button>
                                        </form>
                                    </td>


                                </tr>


                        <?php }
                        } ?>


                    </tbody>


                </table>
            </div>
        </div>
    </div>
</div>







<!-- Meeting Modals  -->

<div class="modal fade bd-progress-modal-sm" id="todayMeetingModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <input type="text" name="meetingDate" value="<?php echo $today; ?>" hidden>
                    <label class="control-label text-dark" for="">Meeting Hours</label>
                    <input type="number" name="meetingHours" id="meetingHours" class="form-control" min="0" max="24" step="any">

                    <label class="control-label text-dark mt-2" for="">Meeting Purpose</label>
                    <textarea class="form-control" name="meetingPurpose" placeholder="Enter Meeting Purpose"></textarea>
                    <button class="btn btn-primary mt-2" name="meetingData">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade bd-progress-modal-sm" id="yesterdayMeetingModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <input type="text" name="meetingDate" value="<?php echo $yesterday; ?>" hidden>
                    <label class="control-label text-dark" for="">Meeting Hours</label>
                    <input type="number" name="meetingHours" id="meetingHours" class="form-control" min="0" max="24" step="any">
                    <label class="control-label text-dark mt-2" for="">Meeting Purpose</label>
                    <textarea class="form-control" name="meetingPurpose" placeholder="Enter Meeting Purpose"></textarea>
                    <button class="btn btn-primary mt-2" name="meetingData">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="modal fade bd-progress-modal-sm" id="day3MeetingModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">

        <div class="modal-content">

            <div class="form-container mt-2 m-2">

                <form class="" action="" method="post" enctype="multipart/form-data">
                    <input type="text" name="meetingDate" value="<?php echo $day3; ?>" hidden>
                    <label class="control-label text-dark" for="">Meeting Hours</label>
                    <input type="number" name="meetingHours" id="meetingHours" class="form-control" min="0" max="24" step="any">
                    <label class="control-label text-dark mt-2" for="">Meeting Purpose</label>
                    <textarea class="form-control" name="meetingPurpose" placeholder="Enter Meeting Purpose"></textarea>
                    <button class="btn btn-primary mt-2" name="meetingData">Submit</button>

                </form>

            </div>

        </div>

    </div>

</div>


<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-6 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Small Jobs Info (Hrs)</h6>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive p-3 ">
                <table class="table table-bordered table-sm " id="smalljob_info" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Small Job Id</th>
                            <th>Small Job Date </th>
                            <th>Small Job Duration</th>
                            <th>Small Job Purpose</th>
                           
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Small Job Id</th>
                            <th>Small Job Date </th>
                            <th>Small Job Duration</th>
                            <th>Small Job Purpose</th>
                            
                        </tr>
                    </tfoot>
                    <tbody>

                        <?php
                        // SQL query to retrieve project details based on project_id
                        $sql = "SELECT * FROM smalljobs WHERE user_id = $user_id ORDER BY job_id DESC";


                        $info = $obj_admin->manage_all_info($sql);
                        $num_rows = $info->rowCount();
                        if ($num_rows > 0) {
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                                <tr>
                                    <td style="width: 5%;">
                                        <?php echo $row['job_id']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['job_date']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['workingHours']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['job_name']; ?>
                                    </td>
                                    
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




















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
</script>

<?php



if (isset($_POST["timesheet_data"]) && isset($_GET["progress_id"])) {
    // Get the work hours from the form
    $work_hours = $_POST['work_hours'];

    // Get the project ID from the URL parameter
    $project_id = $_GET["progress_id"];

    // Manually specify the date you want to insert in 'YYYY-MM-DD' format
    $date_value = $_POST['date_value']; // Replace with your desired date

    // You should perform input validation and sanitation to prevent SQL injection
    // SQL query to insert or update a record into the 'timeSheet' table with a manually specified date
    $sql = "INSERT INTO timesheet (date_value, working_hours, project_id_timesheet,user_id) 
            VALUES (?, ?, ?,?) 
            ON DUPLICATE KEY UPDATE working_hours = VALUES(working_hours)";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameters using the correct data types and order
        // In this example, date_value is a string, working_hours is a float, and project_id_timesheet is an integer.
        $stmt->bind_param("ssii", $date_value, $work_hours, $project_id, $user_id);

        if ($stmt->execute()) {
            $msg_success = "Record Inserted or Updated";
            header('location: time-sheet.php');
        } else {
            $msg_error = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        $msg_error = "Error: " . $conn->error;
    }
}


if (isset($_POST["leave_data"])) {
    // Get the work hours from the form
    $leave_hours = $_POST['leave_hours'];
    $leave_type = $_POST['leave_type'];
    $hours = $_POST['leave_hours'];

    // Validate and format the date value
    $date_value = $_POST['date_value'];
    $date = date('Y-m-d', strtotime($date_value));

    // Check if the date is valid
    if ($date === false) {
        // Handle invalid date error
        $msg_error = "Error: Invalid date format";
    } else {
        // Proceed with the SQL query
        $sql = "INSERT INTO leave_data (leave_type, leave_date, employee_id, employee_name,hours) 
            VALUES (?, ?, ?, ?,?) 
        ON DUPLICATE KEY UPDATE 
            leave_date = VALUES(leave_date), 
            hours = VALUES(hours), 
            leave_type = VALUES(leave_type)";


        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters and execute the statement
            $stmt->bind_param("ssiss", $leave_type, $date, $user_id, $user_name, $hours);

            if ($stmt->execute()) {
                $msg_success = "Record Inserted or Updated";
                header('location: time-sheet.php');
            } else {
                $msg_error = "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            $msg_error = "Error: " . $conn->error;
        }
    }
}


if (isset($_POST["meetingData"])) {
    // Get the work hours from the form
    $meetingHours =  $_POST['meetingHours'];
    $meetingPurpose = $_POST['meetingPurpose'];
    $meeting_date = $_POST['meetingDate'];



    // Proceed with the SQL query
    $sql = "INSERT INTO meeting_data (meeting_date, meetingHours, employee_name,meeting_purpose,employee_id) 
            VALUES (?, ?, ?,?,?) 
        ON DUPLICATE KEY UPDATE 
            meeting_date = VALUES(meeting_date), 
            meeting_purpose = VALUES(meeting_purpose), 
            meetingHours = VALUES(meetingHours)";


    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters and execute the statement
        $stmt->bind_param("sssss", $meeting_date, $meetingHours, $user_name, $meetingPurpose, $user_id);

        if ($stmt->execute()) {
            $msg_success = "Record Inserted or Updated";
            header('location: time-sheet.php');
        } else {
            $msg_error = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        $msg_error = "Error: " . $conn->error;
    }
}

if (isset($_POST["job_data"])) {
    // Get the work hours from the form
    $workingHours =  $_POST['workingHours'];
    $job_name = $_POST['job_name'];
    $job_details = $_POST['jobDetails'];
    $job_date = $_POST['date_value'];



    // Proceed with the SQL query
    $sql = "INSERT INTO smallJobs (user_id, workingHours, username,job_name,job_details,job_date) 
            VALUES (?, ?, ?,?,?,?) ";


    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters and execute the statement
        $stmt->bind_param("isssss", $user_id, $workingHours, $user_name, $job_name, $job_details, $job_date);

        if ($stmt->execute()) {
            $msg_success = "Record Inserted or Updated";
            header('location: time-sheet.php');
        } else {
            $msg_error = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        $msg_error = "Error: " . $conn->error;
    }
}


//Additional Engineer 



if (isset($_POST["additional_engineer"])) {
    // Get the project ID and additional engineer IDs from the form
    $project_id = $_POST['project_id'];
    $additional_engineer = $user_id; // New additional engineer ID

    // Fetch the existing additional engineers array from the database
    $sql_select = "SELECT additional_engineers FROM projects WHERE project_id = ?";
    $stmt_select = $conn->prepare($sql_select);

    if ($stmt_select) {
        $stmt_select->bind_param("i", $project_id);
        $stmt_select->execute();
        $stmt_select->bind_result($existing_additional_engineers);
        $stmt_select->fetch();
        $stmt_select->close();

        // Decode the JSON string to an array
        $additional_engineers_array = json_decode($existing_additional_engineers, true);

        // Append the new additional engineer ID to the array
        $additional_engineers_array[] =  "$additional_engineer";

        // Serialize the array back to JSON format
        $new_additional_engineers = json_encode($additional_engineers_array);

        // Update the projects table with the new additional engineers array
        $sql_update = "UPDATE projects SET additional_engineers = ? WHERE project_id = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param("si", $new_additional_engineers, $project_id);

            if ($stmt_update->execute()) {
                $msg_success = "Record Inserted or Updated";
                header('location: time-sheet.php');
                exit; // Exit after redirect
            } else {
                $msg_error = "Error updating record: " . $stmt_update->error;
            }

            $stmt_update->close();
        } else {
            $msg_error = "Error preparing update statement: " . $conn->error;
        }
    } else {
        $msg_error = "Error preparing select statement: " . $conn->error;
    }
}



//Remove Additional Engineer in the Project 

if (isset($_POST["remove_additional_engineer"])) {
    // Get the project ID and additional engineer ID to remove from the form
    $project_id = $_POST['project_id'];
    $additional_engineer_to_remove = $user_id; // User ID to remove

    // Fetch the existing additional engineers array from the database
    $sql_select = "SELECT additional_engineers FROM projects WHERE project_id = ?";
    $stmt_select = $conn->prepare($sql_select);

    if ($stmt_select) {
        $stmt_select->bind_param("i", $project_id);
        $stmt_select->execute();
        $stmt_select->bind_result($existing_additional_engineers);
        $stmt_select->fetch();
        $stmt_select->close();

        // Decode the JSON string to an array
        $additional_engineers_array = json_decode($existing_additional_engineers, true);

        // Remove the specified user ID from the array if it exists
        $index = array_search($additional_engineer_to_remove, $additional_engineers_array);
        if ($index !== false) {
            unset($additional_engineers_array[$index]);
        }

        // Serialize the array back to JSON format
        $new_additional_engineers = json_encode(array_values($additional_engineers_array));

        // Update the projects table with the new additional engineers array
        $sql_update = "UPDATE projects SET additional_engineers = ? WHERE project_id = ?";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param("si", $new_additional_engineers, $project_id);

            if ($stmt_update->execute()) {
                $msg_success = "Record Updated Successfully";
                header('location: time-sheet.php');
                exit; // Exit after redirect
            } else {
                $msg_error = "Error updating record: " . $stmt_update->error;
            }

            $stmt_update->close();
        } else {
            $msg_error = "Error preparing update statement: " . $conn->error;
        }
    } else {
        $msg_error = "Error preparing select statement: " . $conn->error;
    }
}

// Assuming you have a database connection established
// and the meeting_id is sent via POST
if (isset($_POST['delete_meeting'])) {
    $meeting_id = $_GET['meeting_id'];

    // Perform the deletion operation in your database
    // Example:
    $sql = "DELETE FROM meeting_data WHERE meeting_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $meeting_id);
    if ($stmt->execute()) {
        // Deletion successful
        echo json_encode(['success' => true]);
        header('Location:' . $_SERVER['HTTP_REFERER']);
    } else {
        // Failed to delete
        echo json_encode(['success' => false]);
    }
}




include 'include/footer.php';
?>

<!-- Add a click event handler to the <td> element -->
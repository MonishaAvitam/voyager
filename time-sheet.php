<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<?php

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Month;

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

include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';



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
      echo " ";
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
      echo " ";
   }
}
?>
<!-- Modal -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->

<head>
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

      .custom-bg-1 {
         background: linear-gradient(to right, #3ca2a2, #226161, #3ca2a2);
         color: white;
      }

      .icon-container {
         background: white;
         border-radius: 50%;
         width: 50px;
         height: 50px;
         padding: 5px;
      }

      .otp-wrong-gif {
         width: 45px;
         height: 45px;
      }

      header {
         color: black;
      }

      .header {
         color: black;
      }
   </style>
</head>
<!-- Performance -->
<div class="">
   <div class="container">
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



      // Assuming $user_id, $selected_month, and $selected_year are already defined

      $sql = "
SELECT 
    COALESCE((SELECT SUM(md.meetingHours) 
              FROM meeting_data md 
              WHERE md.employee_id = $user_id 
                AND MONTH(md.meeting_date) = $selected_month 
                AND YEAR(md.meeting_date) = $selected_year), 0) AS totalMeetingHours,

    COALESCE((SELECT SUM(l.hours) 
              FROM leave_approval l 
              WHERE l.employee_id = $user_id 
                AND MONTH(l.leave_from) = $selected_month 
                AND YEAR(l.leave_from) = $selected_year 
                AND l.approved = 'Approved'), 0) AS totalLeaveHours,

    COALESCE((SELECT SUM(ts.working_hours) 
              FROM timesheet ts 
              WHERE ts.user_id = $user_id 
                AND MONTH(ts.date_value) = $selected_month 
                AND YEAR(ts.date_value) = $selected_year), 0) AS totalWorkingHours,

    COALESCE((SELECT SUM(sj.workingHours) 
              FROM smallJobs sj 
              WHERE sj.user_id = $user_id 
                AND MONTH(sj.job_date) = $selected_month 
                AND YEAR(sj.job_date) = $selected_year), 0) AS totalSmallHours
";

      $info = $obj_admin->manage_all_info($sql);
      $num_rows = $info->rowCount();

      if ($num_rows === 0) {
         echo '<td>No data found</td>';
      } else {
         while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
            $totalMeetingHours   = number_format((float)($row['totalMeetingHours'] ?? 0), 2) . 'H';
            $totalLeaveHours     = number_format((float)($row['totalLeaveHours'] ?? 0), 2) . 'H';
            $totalWorkingHours   = number_format((float)($row['totalWorkingHours'] ?? 0), 2) . 'H';
            $totalSmallJobHours  = number_format((float)($row['totalSmallHours'] ?? 0), 2) . 'H';
         }
      }
      ?>

      <div class="row " style="">
         <div class="col-xl-3 col-lg-4">
            <div class="card bg-white border-0 shadow-lg" style="height: 13rem;">
               <div class="card-statistic-3 p-4">
                  <div class="icon-container"> <img src="./icons/meeting.webp" width="40" height="40" /> </div>
                  <div class="mb-4 mt-3">
                     <h5 class="card-title mb-0 ">Total Meeting Hours </h5>
                  </div>
                  <div class="row align-items-center mb-2 d-flex">
                     <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0 text-lowercase">
                           <?php echo $totalMeetingHours ?>
                        </h2>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-xl-3 col-lg-4">
            <div class="card bg-white border-0 shadow-lg" style="height: 13rem;">
               <div class="card-statistic-3 p-4">
                  <div class="icon-container"> <img src="./icons/totalworking.webp" width="40" height="40" /> </div>
                  <div class="mb-4 mt-3">
                     <h5 class="card-title mb-0 ">Total Working Hours</h5>
                  </div>
                  <div class="row align-items-center mb-2 d-flex">
                     <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0 ">
                           <?php echo $totalWorkingHours; ?>
                        </h2>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-xl-3 col-lg-4">
            <div class="card bg-white border-0 shadow-lg" style="height: 13rem;">
               <div class="card-statistic-3 p-4">
                  <div class="icon-container"> <img src="./icons/smalljobs.webp" width="40" height="40" /> </div>
                  <div class="mb-4 mt-3">
                     <h5 class="card-title mb-0 ">Total Small Job Hours</h5>
                  </div>
                  <div class="row align-items-center mb-2 d-flex">
                     <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0 text-lowercase">
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
         <div class="col-xl-3 col-lg-4">
            <div class="card bg-white border-0 shadow-lg" style="height: 13rem;">
               <div class="card-statistic-3 p-4">
                  <div class="icon-container"> <img src="./icons/leaves.webp" width="40" height="40" /> </div>
                  <div class="mb-4 mt-3">
                     <h5 class="card-title mb-0 ">Total Leave Hours</h5>
                  </div>
                  <div class="row align-items-center mb-2 d-flex">
                     <div class="col-8">
                        <h2 class="d-flex align-items-center text-lowercase mb-0 ">
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
            <h3>Showing Data for <span class="text-primary fw-bold">
                  <?php $selected_month_name = date('F', mktime(0, 0, 0, $selected_month, 1));
                  echo $selected_month_name; ?>&nbsp<?php echo $selected_year ?> </span>
            </h3>
         </div>
         <div class="col-3 ">
            <form method="POST" action="">
               <label for="selectedDate">Select Month and Year:</label>
               <div class="d-flex">
                  <input type="month" class="form-control" name="selectedDate" id="selectedDate" required>
                  <button class="btn bg-gradient btn-sm btn-success ml-2 " type="submit">Submit</button>
               </div>
            </form>
         </div>
      </div>
      <?php if ($user_role == 1) { ?>
         <div class="card mt-2 border-0 shadow-lg">
            <div class="">
               <div class="col-6 d-flex justify-content-between align-items-center py-4">
                  <h5 class="m-0 font-weight-bold text-primary ">Performance Info</h5>
               </div>
            </div>
            <div class="">
               <div class="table-responsive p-3">
                  <table class="table shadow-lg table-bordered table-sm" id="performance" name="dataTable">
                     <thead style="height:3rem;" class="">
                        <tr class="bg-gradient text-light my-2 " style="background: #5d855b">
                           <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Name</th>
                           <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Meeting
                              Hours
                           </th>
                           <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Working
                              Hours
                           </th>
                           <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Small Job
                              Hours
                           </th>
                           <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Leave Hours
                           </th>
                           <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        if ($user_role == 1) {
                           $sql = "
                                         SELECT 
                                             ta.fullname,
                                             ta.user_id,
                                             IFNULL(ROUND(SUM(md.meetingHours), 2), 0) AS ETMH,  
                                             IFNULL(ROUND(
                                                 (SELECT SUM(ts.working_hours) 
                                                 FROM timesheet ts 
                                                 WHERE ts.user_id = ta.user_id 
                                                 AND MONTH(ts.date_value) = $selected_month 
                                                 AND YEAR(ts.date_value) = $selected_year), 
                                             2), 0) AS ETWH, 
                                             IFNULL(ROUND(
                                                 (SELECT SUM(sj.workingHours) 
                                                 FROM smallJobs sj 
                                                 WHERE sj.user_id = ta.user_id 
                                                 AND MONTH(sj.job_date) = $selected_month 
                                                 AND YEAR(sj.job_date) = $selected_year), 
                                             2), 0) AS ETSH, 
                                             IFNULL(ROUND(
                                                 (SELECT SUM(l.hours) 
                                                 FROM leave_approval l 
                                                 WHERE l.employee_id = ta.user_id 
                                                 AND MONTH(l.leave_from) = $selected_month 
                                                 AND YEAR(l.leave_from) = $selected_year
                                                 AND l.approved = 'Approved'), 
                                             2), 0) AS ETLH   
                                         FROM 
                                             tbl_admin ta 
                                         LEFT JOIN 
                                             meeting_data md ON md.employee_id = ta.user_id 
                                             AND MONTH(md.meeting_date) = $selected_month 
                                             AND YEAR(md.meeting_date) = $selected_year
                                         GROUP BY
                                             ta.fullname, ta.user_id;
                                         ";
                        } else {
                           $sql = "
                                         SELECT 
                                             ta.fullname, 
                                             ta.user_id, 
                                             ROUND(IFNULL(SUM(md.meetingHours), 0), 2) AS ETMH,  
                                             ROUND(IFNULL(
                                                 (SELECT SUM(ts.working_hours) 
                                                 FROM timesheet ts 
                                                 WHERE ts.user_id = ta.user_id 
                                                 AND MONTH(ts.date_value) = $selected_month 
                                                 AND YEAR(ts.date_value) = $selected_year), 
                                             0), 2) AS ETWH, 
                                             ROUND(IFNULL(
                                                 (SELECT SUM(sj.workingHours) 
                                                 FROM smallJobs sj 
                                                 WHERE sj.user_id = ta.user_id 
                                                 AND MONTH(sj.job_date) = $selected_month 
                                                 AND YEAR(sj.job_date) = $selected_year), 
                                             0), 2) AS ETSH,  
                                             ROUND(IFNULL(
                                                 (SELECT SUM(l.hours) 
                                                 FROM leave_approval l 
                                                 WHERE l.employee_id = ta.user_id 
                                                 AND MONTH(l.leave_from) = $selected_month 
                                                 AND YEAR(l.leave_from) = $selected_year
                                                 AND l.approved = 'Approved'), 
                                             0), 2) AS ETLH   
                                         FROM 
                                             tbl_admin ta 
                                         LEFT JOIN 
                                             meeting_data md ON md.employee_id = ta.user_id 
                                             AND MONTH(md.meeting_date) = $selected_month 
                                             AND YEAR(md.meeting_date) = $selected_year
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
                              <tr style="height: 5rem;">
                                 <td class="align-middle"><?php echo $row['fullname'] ?></td>
                                 <td class="align-middle"><?php echo $row['ETMH'] ?></td>
                                 <td class="align-middle"><?php echo $row['ETWH'] ?></td>
                                 <td class="align-middle"><?php echo $row['ETSH'] ?></td>
                                 <td class="align-middle"><?php echo $row['ETLH'] ?></td>
                                 <td class="text-center align-middle">
                                    <button type="button" class="view-btn btn btn-primary"
                                       data-id="<?php echo $row['user_id']; ?>">
                                       view
                                    </button>
                                 </td>
                              </tr>
                        <?php }
                        } ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      <?php } ?>
   </div>
   <div class="container mt-5 ">
      <div class="card mb-4 bg-white border-0 shadow-lg">
         <div class="py-3">
            <div class="row">
               <div class="col-6 d-flex justify-content-between align-items-center">
                  <h5 class="ml-3 font-weight-bold text-primary">Enter Project Hours</h5>
               </div>
               <?php if ($user_role !== 1) { ?>
                  <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                     <button class="btn btn-primary bg-gradient " data-target="#additional_engineer"
                        data-toggle="modal">Add Project</button>
                     <button class="btn btn-danger bg-gradient mx-3" data-target="#remove_additional_engineer"
                        data-toggle="modal">Remove Project</button>
                  </div>
               <?php } ?>
            </div>
         </div>
         <div class="">
            <div class="table-responsive p-3 pt-3">
               <table class="table shadow-lg table-bordered  table-sm pt-3" id="projectsInfo" name="dataTable"
                  style="border:white">
                  <thead style="height:3rem;">
                     <tr class="bg-gradient text-light my-2 " style="background: #355d98">
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">ID</th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Project
                           Title
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">
                           <?php echo $day3; ?>
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Yesterday
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">
                           Today
                           <!-- <?php
                                 $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                 retrieve_leave_data($dateToRetrieve, $conn);
                                 ?> -->
                        </th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $sql = "SELECT p.*, pm.fullname AS project_manager_name, c.contact_name, c.contact_email, c.contact_phone_number, c.contact_id, c.customer_id
                         FROM projects p
                         LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
                         LEFT JOIN contacts c ON (p.contact_id = c.contact_id)
                          where p.urgency != 'purple'";

                     $sql2 = "SELECT sp.*, pm.fullname AS subproject_manager_name, 
                                    c.contact_name, c.contact_email, c.contact_phone_number, 
                                    c.contact_id, c.customer_id
                                    FROM subprojects sp
                                    LEFT JOIN project_managers pm ON sp.project_manager = pm.fullname
                                    LEFT JOIN contacts c ON sp.contact_id = c.contact_id
                                    WHERE sp.urgency != 'purple' 
                                    AND sp.mark_as_completed IS NULL";


                     // Conditions for projects based on user roles
                     if ($user_role == 1) {
                        // Admin can see all projects, so no additional conditions needed
                     } elseif ($user_role == 2) {

                        $sql .= " AND (
                        (p.assign_to_id = '$user_id' AND (p.verify_status = '0' OR p.verify_status IS NULL)) 
                             OR p.verify_by = '$user_id' 
                         OR ( JSON_CONTAINS(p.additional_engineers, '\"$user_id\"' ))
                            )";
                     } elseif ($user_role == 3) {
                        $sql .= " AND (p.project_managers_id = '$user_id' OR p.verify_by = '$user_id' OR p.assign_to_id = '$user_id' OR JSON_CONTAINS(p.additional_engineers, '\"$user_id\"' ))";
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
                        <tr style="height: 5rem;">
                           <td class="">
                              <div class="d-flex  text-center align-items-center justify-content-center pt-3">
                                 <div>
                                    <div class="py-2"
                                       style="width: 4.5rem; background-color: <?php echo $row['urgency']; ?>; text-align: center; border-radius: 5px; color: <?php echo ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff'; ?>">
                                       <?php echo !empty($row['revision_project_id']) ? $row['revision_project_id'] : $row['project_id']; ?>
                                    </div>
                                    <div>
                                       <span
                                          class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>
                                       <span class="badge badge-pill badge-danger"><?php if ($row['subproject_status'] != NULL) {
                                                                                       echo 'S' . $row['subproject_status'];
                                                                                    } ?></span>
                                    </div>
                                 </div>
                              </div>
                           </td>
                           <td style="width: 30%;" class="align-middle">
                              <?php if ($row['subproject_status'] != Null) {
                                 echo $row['subproject_name'];
                              } else {
                                 echo $row['project_name'];
                              } ?>
                           </td>
                           <td class="align-middle" style="cursor: pointer;" data-toggle="modal" data-target="#day3_modal"
                              data-project-id="<?php echo $row['project_id']; ?>"
                              onclick="updateUrl(<?php if ($row['subproject_status'] != NULL) {
                                                      echo $row['table_id'];
                                                   } else {
                                                      echo $row['project_id'];
                                                   } ?>)">
                              <div class="d-flex justify-content-center">
                                 <?php
                                 if ($row['subproject_status'] != Null) {
                                    $project_id = $row['table_id'];
                                 } else {
                                    $project_id = $row['project_id']; // Replace with the actual project ID

                                 }
                                 $dateToRetrieve = date('Y-m-d', strtotime('-2 day')); // Replace with your desired date

                                 retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id)
                                 ?>
                                 <i class="fas fa-solid fa-pencil text-primary ml-3"></i>
                              </div>
                           </td>
                           <td class="align-middle " style="cursor: pointer;" data-toggle="modal"
                              data-target="#yesterday_modal" data-project-id="<?php echo $row['project_id']; ?>"
                              onclick="updateUrl(<?php if ($row['subproject_status'] != NULL) {
                                                      echo $row['table_id'];
                                                   } else {
                                                      echo $row['project_id'];
                                                   } ?>)">
                              <div class="d-flex justify-content-center">
                                 <?php
                                 if ($row['subproject_status'] != null) {
                                    $project_id = $row['table_id'];
                                 } else {
                                    $project_id = $row['project_id']; // Replace with the actual project ID
                                 }
                                 $dateToRetrieve = date('Y-m-d', strtotime('-1 day')); // Replace with your desired date

                                 retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id);
                                 ?>
                                 <i class="fas fa-solid fa-pencil text-primary ml-3"></i>
                              </div>
                           </td>
                           <td class="align-middle " style="cursor: pointer;" data-toggle="modal"
                              data-target="#today_modal" data-project-id="<?php echo $row['project_id']; ?>"
                              onclick="updateUrl(<?php if ($row['subproject_status'] != NULL) {
                                                      echo $row['table_id'];
                                                   } else {
                                                      echo $row['project_id'];
                                                   } ?>)">
                              <div class="d-flex justify-content-center">
                                 <?php
                                 if ($row['subproject_status'] != Null) {
                                    $project_id = $row['table_id'];
                                 } else {
                                    $project_id = $row['project_id']; // Replace with the actual project ID

                                 }

                                 $dateToRetrieve = date('Y-m-d'); // Replace with your desired date



                                 retrieve_working_data($project_id, $dateToRetrieve, $conn, $user_id);

                                 ?>
                                 <i class="fas fa-solid fa-pencil text-primary ml-3"></i>
                              </div>
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
      <div class="">
         <div class="card border-0 py-3">
            <div class="col-12 ">
               <h5 class="m-0 font-weight-bold text-primary">Leave Application</h5>
            </div>
            <div class="card-body border-0 mt-4">
               <div class="row ">
                  <div class="col-6 d-flex justify-content-center"><a href="leave_approval.php"
                        class="btn btn-warning bg-gradient">Casual/Sick Leaves</a></div>
                  <div class="col-6 d-flex justify-content-center"><a href="emergencyLeave.php"
                        class="btn btn-danger bg-gradient">Emergency Leave </a></div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="today_modal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <label class="control-label text-dark" for="">Set Work Hours</label>
                  <input type="text" name="date_value" value="<?php echo $today; ?>" hidden>
                  <input class="form-control" type="number" name="work_hours" max="24" min="0" step="any">
                  <button class="btn bg-gradient btn-success mt-2" name="timesheet_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="yesterday_modal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <label class="control-label text-dark" for="">Set Work Hours</label>
                  <input type="text" name="date_value" name="date_value" value="<?php echo $yesterday; ?>" hidden>
                  <input class="form-control" type="number" name="work_hours" max="24" min="0" step="any">
                  <button class="btn bg-gradient btn-success mt-2" name="timesheet_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="day3_modal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <label class="control-label text-dark" for="">Set Work Hours</label>
                  <input type="text" name="date_value" value="<?php echo $day3; ?>" hidden>
                  <input class="form-control" type="number" name="work_hours" max="24" min="0" step="any">
                  <button class="btn bg-gradient btn-success mt-2" name="timesheet_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <!-- Additional Engineer Modal  -->
   <div class="modal fade" id="additional_engineer" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
      aria-hidden="true">
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
                        $sql = "SELECT * FROM `projects` WHERE `urgency` !='purple'";


                        $info = $obj_admin->manage_all_info($sql);
                        $serial = 1;
                        $num_row = $info->rowCount();
                        if ($num_row == 0) {
                           echo '<tr><td colspan="7">No projects were found</td></tr>';
                        }
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                           <option value="<?php echo $row['project_id'] ?>"><?php echo $row['project_name'] ?></option>
                        <?php } ?>
                     </select>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn bg-gradient btn-secondary" data-dismiss="modal">Close</button>
                     <button type="submit" class="btn bg-gradient btn-primary" name="additional_engineer">Add
                        Project</button>
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
   <div class="modal fade" id="remove_additional_engineer" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
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
                        $serial = 1;
                        $num_row = $info->rowCount();
                        if ($num_row == 0) {
                           echo '<tr><td colspan="7">No projects were found</td></tr>';
                        }
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                           <option value="<?php echo $row['project_id'] ?>"><?php echo $row['project_name'] ?></option>
                        <?php } ?>
                     </select>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn bg-gradient btn-secondary" data-dismiss="modal">Close</button>
                     <button type="submit" class="btn bg-gradient btn-danger" name="remove_additional_engineer">Remove
                        Project</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="container mt-5">
      <div class="card shadow mb-4 border-0 shadow-lg">
         <div class="card-header py-3">
            <div class="row">
               <div class="col-6 d-flex justify-content-between align-items-center">
                  <h5 class="m-0 font-weight-bold text-primary">Enter Small Job Details</h5>
               </div>
               <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                  <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                  <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
               </div>
            </div>
         </div>
         <div class="card-body">
            <div class="table-responsive ">
               <table class="table shadow-lg table-bordered table-sm" name="dataTable" style="border:white">
                  <thead style="height:3rem;">
                     <tr class="bg-gradient text-light my-2 align-middle" style="background: #5d855b">
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">
                           <?php echo $day3; ?>
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Yesterday
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">
                           Today
                           <!-- <?php
                                 $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                 retrieve_leave_data($dateToRetrieve, $conn);
                                 ?> -->
                        </th>
                     </tr>
                  </thead>
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
                        <tr class="pt-4" style="height:4rem; padding-top: 1rem;">
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal" data-target="#day3JobModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#yesterdayJobModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#todayJobModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                        </tr>
                     <?php
                     } else { ?>
                        <tr class="pt-4" style="height:4rem; paddingTop: 1rem;">
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal" data-target="#day3JobModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#yesterdayJobModal"> <button class="btn btn-sm btn-info bg-gradient"> Enter
                                 Details</button>
                           </td>
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#todayJobModal"> <button class="btn btn-sm btn-info bg-gradient"> Enter
                                 Details</button>
                           </td>
                        </tr>
                     <?php
                     }
                     ?>
                  </tbody>
               </table>
               <p class="text-warning"> * Data entered here will be reflected in the Small Jobs Info table.</p>
            </div>
         </div>
      </div>
   </div>
   <div class="container">
      <div class="card shadow mb-4 bg-white border-0 shadow-lg">
         <div class="py-3">
            <div class="row">
               <div class="col-6 d-flex justify-content-between align-items-center">
                  <h5 class="ml-3  font-weight-bold text-primary">Small Jobs Info</h5>
               </div>
            </div>
         </div>
         <div class="">
            <div class="table-responsive p-3 ">
               <table class="table shadow-lg table-bordered  table-sm" id="small-jobs-info-table" name=""
                  style="border:white">
                  <thead style="height:3rem;">
                     <tr class="bg-gradient text-light my-2 align-middle" style="background: #86568c">
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Small Job
                           Id
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Small Job
                           Date
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Small Job
                           Duration
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Small Job
                           Purpose
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Actions
                        </th>
                     </tr>
                  </thead>
                  <tbody style="background: #dddddd70">
                     <?php
                     // SQL query to retrieve project details based on project_id
                     $sql = "SELECT * FROM smallJobs WHERE user_id = $user_id ORDER BY job_id DESC";


                     $info = $obj_admin->manage_all_info($sql);
                     $num_rows = $info->rowCount();
                     if ($num_rows > 0) {
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                     ?>
                           <tr style="height: 5rem;">
                              <td class="align-middle">
                                 <?php echo $row['job_id']; ?>
                              </td>
                              <td class="align-middle">
                                 <?php echo $row['job_date']; ?>
                              </td>
                              <td class="align-middle">
                                 <?php echo $row['workingHours']; ?>
                              </td>
                              <td class="align-middle">
                                 <?php echo $row['job_name']; ?>
                              </td>
                              <td class="align-middle">
                                 <form method="POST">
                                    <button type="submit" name="delete_smallJobs"
                                       onclick="return confirmDeletejobs(<?php echo $row['job_id'] ?>)"
                                       class="btn bg-gradient btn-danger btn-sm">Delete</button>
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
   <div class="container">
      <div class="card shadow mb-4 border-0 shadow-lg">
         <div class="card-header py-3">
            <div class="row">
               <div class="col-6 d-flex justify-content-between align-items-center">
                  <h5 class="m-0 font-weight-bold text-primary">Enter Meeting Details</h5>
               </div>
               <div class="col-6 d-flex  align-items-center justify-content-md-end ">
                  <!-- <button class="btn btn-primary mr-2" data-target="#meeting_box" data-toggle="modal">Meeting</button>
                  <button class="btn btn-primary" data-target="#leave_box" data-toggle="modal">Leave</button> -->
               </div>
            </div>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table shadow-lg table-bordered  table-sm" name="" style="border:white">
                  <thead style="height:3rem;">
                     <tr class="bg-gradient text-light my-2 align-middle" style="background: #5d855b">
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">
                           <?php echo $day3; ?>
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Yesterday
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">
                           Today
                           <!-- <?php
                                 $dateToRetrieve = date('Y-m-d'); // Replace with your desired date

                                 retrieve_leave_data($dateToRetrieve, $conn);
                                 ?> -->
                        </th>
                     </tr>
                  </thead>
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
                        <tr class="pt-4" style="height:4rem;">
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#day3MeetingModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#yesterdayMeetingModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                           <td style="cursor: pointer; padding-top: 1rem;" data-toggle="modal"
                              data-target="#todayMeetingModal">
                              <button class="btn btn-sm btn-info bg-gradient"> Enter Details</button>
                           </td>
                        </tr>
                     <?php
                     } ?>
                  </tbody>
               </table>
               <p class="text-warning"> * Data entered here will be reflected in the Meetings Info Table.</p>
            </div>
         </div>
      </div>
   </div>
   <div class="container">
      <div class="card shadow mb-4 border-0 shadow-lg">
         <div class="py-3">
            <div class="row">
               <div class="col-6 d-flex justify-content-between align-items-center">
                  <h5 class="ml-3  font-weight-bold text-primary">Meeting Info</h5>
               </div>
               <div class="col-6 d-flex  align-items-center justify-content-md-end ">
               </div>
            </div>
         </div>
         <div class="">
            <div class="table-responsive p-3 ">
               <table class="table shadow-lg table-bordered table-sm" id="meetings-info-table" name="dataTable"
                  style="border:white">
                  <thead style="height:3rem;">
                     <tr class="bg-gradient text-light my-2 align-middle" style="background: #86568c">
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Meeting Id
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Meeting
                           Date
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Meeting
                           Total Duration
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Meeting
                           Purpose
                        </th>
                        <th scope="col" class="text-center align-middle" width="6%" style="font-weight:500;">Actions
                        </th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     // SQL query to retrieve project details based on project_id
                     $sql = "SELECT * FROM meeting_data WHERE employee_id = $user_id ORDER BY meeting_id DESC";


                     $info = $obj_admin->manage_all_info($sql);
                     $num_rows = $info->rowCount();


                     if ($num_rows > 0) {
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                     ?>
                           <tr style="height: 5rem;">
                              <td style="width: 5%;" class="align-middle">
                                 <?php echo $row['meeting_id']; ?>
                              </td>
                              <td class="align-middle">
                                 <?php echo $row['meeting_date']; ?>
                              </td>
                              <td class="align-middle">
                                 <?php echo $row['meetingHours']; ?>
                              </td>
                              <td class="align-middle">
                                 <?php echo $row['meeting_purpose']; ?>
                              </td>
                              <td class="align-middle">
                                 <form method="POST">
                                    <button type="submit" name="delete_meeting"
                                       onclick="return confirmDelete(<?php echo $row['meeting_id'] ?>)"
                                       class="btn bg-gradient btn-danger btn-sm">Delete</button>
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
   <!-- leave table -->
   <!-- Small Jobs Modal -->
   <div class="modal fade bd-progress-modal-sm" id="todayJobModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <input type="text" name="date_value" value="<?php echo $today; ?>" hidden>
                  <label class="control-label text-dark" for="">Job Details</label>
                  <input type="text" name="job_name" id="job_name" maxlength="255" class="form-control"
                     placeholder="Job Name">
                  <textarea name="jobDetails" id="jobDetails" cols="10" rows="5" class="form-control mt-2"
                     placeholder="Job Description"></textarea>
                  <label class="control-label text-dark mt-2" for="">Working Hours</label>
                  <input class="form-control " type="number" value="24" name="workingHours" max="24" min="0" step="any">
                  <button class="btn bg-gradient btn-success mt-2" name="job_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="yesterdayJobModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <input type="text" name="date_value" value="<?php echo $yesterday; ?>" hidden>
                  <label class="control-label text-dark" for="">Job Details</label>
                  <input type="text" name="job_name" id="job_name" maxlength="255" class="form-control"
                     placeholder="Job Name">
                  <textarea name="jobDetails" id="jobDetails" cols="10" rows="5" class="form-control mt-2"
                     placeholder="Job Description"></textarea>
                  <label class="control-label text-dark mt-2" for="">Working Hours</label>
                  <input class="form-control " type="number" value="24" name="workingHours" max="24" min="0" step="any">
                  <button class="btn bg-gradient btn-success mt-2" name="job_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="day3JobModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <input type="text" name="date_value" value="<?php echo $day3; ?>" hidden>
                  <label class="control-label text-dark" for="">Job Details</label>
                  <input type="text" name="job_name" id="job_name" maxlength="255" class="form-control"
                     placeholder="Job Name">
                  <textarea name="jobDetails" id="jobDetails" cols="10" rows="5" class="form-control mt-2"
                     placeholder="Job Description"></textarea>
                  <label class="control-label text-dark mt-2" for="">Working Hours</label>
                  <input class="form-control " type="number" value="24" name="workingHours" max="24" min="0" step="any">
                  <button class="btn bg-gradient btn-success mt-2" name="job_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <!-- Leave Modals  -->
   <div class="modal fade bd-progress-modal-sm" id="todayLeaveModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
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
                  <input class="form-control" type="number" value="9.25" name="leave_hours" max="24" min="0"
                     step="0.01">
                  <button class="btn bg-gradient btn-success mt-2" name="leave_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="yesterdayLeaveModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
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
                  <input class="form-control" type="number" value="9.25" name="leave_hours" max="24" min="0"
                     step="0.01">
                  <button class="btn bg-gradient btn-success mt-2" name="leave_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="day3LeaveModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
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
                  <button class="btn bg-gradient btn-success mt-2" name="leave_data">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <!-- Meeting Modals  -->
   <div class="modal fade bd-progress-modal-sm" id="todayMeetingModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <input type="text" name="meetingDate" value="<?php echo $today; ?>" hidden>
                  <label class="control-label text-dark" for="">Meeting Hours</label>
                  <input type="number" name="meetingHours" id="meetingHours" class="form-control" min="0" max="24"
                     step="any">
                  <label class="control-label text-dark mt-2" for="">Meeting Purpose</label>
                  <textarea class="form-control" name="meetingPurpose" placeholder="Enter Meeting Purpose"></textarea>
                  <button class="btn bg-gradient btn-success mt-2" name="meetingData">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="yesterdayMeetingModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <input type="text" name="meetingDate" value="<?php echo $yesterday; ?>" hidden>
                  <label class="control-label text-dark" for="">Meeting Hours</label>
                  <input type="number" name="meetingHours" id="meetingHours" class="form-control" min="0" max="24"
                     step="any">
                  <label class="control-label text-dark mt-2" for="">Meeting Purpose</label>
                  <textarea class="form-control" name="meetingPurpose" placeholder="Enter Meeting Purpose"></textarea>
                  <button class="btn bg-gradient btn-success mt-2" name="meetingData">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade bd-progress-modal-sm" id="day3MeetingModal" tabindex="-1" role="dialog"
      aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
         <div class="modal-content">
            <div class="form-container mt-2 m-2">
               <form class="" action="" method="post" enctype="multipart/form-data">
                  <input type="text" name="meetingDate" value="<?php echo $day3; ?>" hidden>
                  <label class="control-label text-dark" for="">Meeting Hours</label>
                  <input type="number" name="meetingHours" id="meetingHours" class="form-control" min="0" max="24"
                     step="any">
                  <label class="control-label text-dark mt-2" for="">Meeting Purpose</label>
                  <textarea class="form-control" name="meetingPurpose" placeholder="Enter Meeting Purpose"></textarea>
                  <button class="btn bg-gradient btn-success mt-2" name="meetingData">Submit</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="success-msg-popup" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog  wrong-otp-modal-width">
         <div class="modal-content">
            <div class="modal-body text-center">
               <div class="">
                  <img src="https://media.giphy.com/media/3og0IvGtnDyPHCRaYU/giphy.gif" class="otp-wrong-gif" />
               </div>
               <p class="fs-5 fw-bold"> Incorrect</p>
               <p class="">The OTP you have entered is either incorrect or expired.</p>
               <button type="button" class="btn btn-danger create-group-btn mt-2" data-bs-dismiss="modal">Close</button>
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
            $msg_success = "Record Updated Succesfully!";
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
               $msg_success = "Record Updated Succesfully!";
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
      $meetingHours = $_POST['meetingHours'];
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
            $msg_success = "Record Updated Succesfully!";
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
      $workingHours = $_POST['workingHours'];
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
            $msg_success = "Record Updated Succesfully!";
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
         $additional_engineers_array[] = "$additional_engineer";

         // Serialize the array back to JSON format
         $new_additional_engineers = json_encode($additional_engineers_array);

         // Update the projects table with the new additional engineers array
         $sql_update = "UPDATE projects SET additional_engineers = ? WHERE project_id = ?";
         $stmt_update = $conn->prepare($sql_update);

         if ($stmt_update) {
            $stmt_update->bind_param("si", $new_additional_engineers, $project_id);

            if ($stmt_update->execute()) {
               $msg_success = "Record Updated Succesfully!";
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
               $msg_success = "Record Updated Successfully!";
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

   if (isset($_POST['delete_smallJobs'])) {
      $job_id = $_GET['job_id'];

      // Perform the deletion operation in your database
      // Example:
      $sql = "DELETE FROM smallJobs WHERE job_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $job_id);
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
   <script>
      $(document).ready(function() {
         var table = $('#small-jobs-info-table').DataTable({
            paging: true,
            pageLength: 5, // Default number of entries per page
            lengthMenu: [5, 10, 20, 30], // Custom pagination options
            searching: true, // Disable default search box
            ordering: true
         });
         // Custom search box functionality
         $('#customSearchInput').on('keyup', function() {
            table.search(this.value).draw();
         });
      });


      $(document).ready(function() {
         var table = $('#meetings-info-table').DataTable({
            paging: true,
            pageLength: 5, // Default number of entries per page
            lengthMenu: [5, 10, 20, 30], // Custom pagination options
            searching: true, // Disable default search box
            ordering: true
         });
         $('#customSearchInput').on('keyup', function() {
            table.search(this.value).draw();
         });
      });

      $(document).ready(function() {
         var table = $('#projectsInfo').DataTable({
            paging: true,
            pageLength: 5, // Default number of entries per page
            lengthMenu: [5, 10, 20, 30], // Custom pagination options
            searching: true, // Disable default search box
            ordering: true
         });
         $('#customSearchInput').on('keyup', function() {
            table.search(this.value).draw();
         });
      });
   </script>
   <script>
      document.addEventListener("DOMContentLoaded", function() {
         document.querySelectorAll(".view-btn").forEach(function(btn) {
            btn.addEventListener("click", function() {
               let userId = this.dataset.id;
               console.log("Selected User ID:", userId);

               // redirect with id
               window.location.href = "view_timesheet-details.php?id=" + userId;

            });
         });
      });
   </script>
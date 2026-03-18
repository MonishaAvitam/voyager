<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

include 'include/welcomeTopBar.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';
include 'reimbursement_form.php';


include 'conn.php'; // Ensure your database connection is included

$sql01 = "
    SELECT COUNT(*) AS AllActiveProjects
    FROM (
        SELECT 'Project' AS type, 
               p.project_id
               
        FROM projects p
      Where urgency <> 'purple'
        UNION ALL
        SELECT 'subprojects' AS type, 
               ss.project_id
              
        FROM subprojects ss
        Where mark_as_completed  IS NULL
    ) AS combined_data;
";
$result01 = $conn->query($sql01);


if ($result01) {
  $row = $result01->fetch_assoc();
  $AllActiveProjects = $row['AllActiveProjects'];
  $result01->free();
} else {
  echo "Error executing query: " . $conn->error;
}



$sql02 = "
    SELECT COUNT(*) AS Archive
    FROM (
        SELECT 'Project' AS type, p.project_id, p.project_name, p.p_team, p.project_manager, p.assign_to, p.EPT, p.urgency, p.reopen_status,p.revision_project_id, p.subproject_status, p.contact_id, c.customer_name,p.end_date, NULL AS table_id
               FROM projects p
               
               LEFT JOIN project_managers pm ON (p.project_manager = pm.fullname)
               LEFT JOIN contacts c ON (p.contact_id = c.contact_id)

                UNION
                    
                SELECT 'Deliverable' AS type, dd.project_id, dd.project_name, dd.p_team, dd.project_manager, dd.assign_to, dd.EPT, dd.urgency, dd.reopen_status,NULL AS revision_project_id, NULL AS subproject_status, dd.contact_id, c.customer_name,dd.end_date,NULL AS table_id
                FROM deliverable_data dd
                LEFT JOIN project_managers pm ON (dd.project_manager = pm.fullname)
                LEFT JOIN contacts c ON (dd.contact_id = c.contact_id)
                UNION
                    
                SELECT 'subprojects' AS type, ss.project_id, ss.subproject_name, ss.p_team, ss.project_manager, ss.assign_to, ss.sub_EPT, ss.urgency, ss.reopen_status,NULL AS revision_project_id,subproject_status, ss.contact_id, c.customer_name,ss.sub_end_date,ss.table_id
                FROM subprojects ss
                LEFT JOIN project_managers pm ON (ss.project_manager = pm.fullname)
                LEFT JOIN contacts c ON (ss.contact_id = c.contact_id)
    ) AS combined_data;
";
$result02 = $conn->query($sql02);


if ($result02) {
  $row = $result02->fetch_assoc();
  $archive = $row['Archive'];
  $result02->free();
} else {
  echo "Error executing query: " . $conn->error;
}


$sql03 = "
    SELECT COUNT(*) AS myProjects
    FROM (
        SELECT 'Project' AS type, 
               p.project_id,
               p.project_managers_id,
               p.assign_to_id,
               p.verify_by
               
        FROM projects p
      Where  (p.assign_to_id = '$user_id'  OR p.verify_by = '$user_id' OR p.project_managers_id = '$user_id') AND urgency <> 'purple'
        
            
    ) AS combined_data;
";
$result03 = $conn->query($sql03);


if ($result03) {
  $row = $result03->fetch_assoc();
  $myProjects = $row['myProjects'];
  $result03->free();
} else {
  echo "Error executing query: " . $conn->error;
}


$sql04 = "
    SELECT COUNT(*) AS deliverables
    FROM (
        SELECT 'Project' AS type, 
               p.project_id,
               p.project_managers_id,
               p.assign_to_id,
               p.verify_by
               
        FROM projects p
      Where  deliverable_mailDate is  NULL AND urgency = 'purple'
        
            
    ) AS combined_data;
";
$result04 = $conn->query($sql04);


if ($result04) {
  $row = $result04->fetch_assoc();
  $deliverables = $row['deliverables'];
  $result04->free();
} else {
  echo "Error executing query: " . $conn->error;
}







?>


<style>
  /* Welcome Text Styling */
  .welcome-text {
    font-weight: bold;
    font-size: 2.5rem;
    /* Adjust the size as needed */
    color: #333;
    position: relative;
    text-transform: uppercase;
    letter-spacing: 2px;
    /* animation: text-shadow-animation 2s ease-in-out infinite; */
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    /* Initial shadow */
  }

  /* Animation for Shadow */
  @keyframes text-shadow-animation {
    0% {
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
      transform: translateY(0);
    }

    50% {
      text-shadow: 4px 4px 10px rgba(0, 0, 0, 0.5);
      transform: translateY(-10px);
    }

    100% {
      text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
      transform: translateY(0);
    }
  }

  .d-sm-flex {
    padding: 20px;
    border-radius: 10px;
  }
</style>

<script src="./timer.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
</script>

<!-- Intro.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.1.0/introjs.min.css">

<!-- Intro.js JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.1.0/intro.min.js"></script>




<?php
// Initialize upcoming holidays
$currentDate = date('Y-m-d');
$upcomingHolidays = array(
  array('1', '2025-01-01', 'Wednesday', 'New Year Day'),
  array('2', '2025-01-27', 'Monday', 'Australia Day'),
  array('3', '2025-03-14', 'Friday', 'Holi'),
  array('4', '2025-04-18', 'Friday', 'Good Friday'),
  array('5', '2025-04-21', 'Monday', 'Easter Monday'),
  array('6', '2025-04-25', 'Friday', 'Anzac Day'),
  array('7', '2025-05-05', 'Monday', 'Labour Day'),
  array('8', '2025-08-15', 'Friday', 'Independence Day'),
  array('9', '2025-09-05', 'Friday', 'Onam'),
  array('10', '2025-10-06', 'Monday', 'King’s Birthday'),
  array('11', '2025-10-21', 'Tuesday', 'Diwali'),
  array('12', '2025-12-24', 'Wednesday', 'Christmas Eve'),
  array('13', '2025-12-25', 'Thursday', 'Christmas Day'),
  array('14', '2025-12-26', 'Friday', 'Boxing Day'),
);


function sendEmail($recipient, $subject, $message)
{



  $mail = new PHPMailer(true); // Passing `true` enables exceptions 

  try {
    //Server settings
    $mail->SMTPDebug = 0;
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = "engineering@csaengineering.com.au";
    $mail->Password = "kezfduovpirmalcs";
    $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587; // TCP port to connect to

    // Recipients
    $mail->setFrom('revanthshiva@csaengineering.com.au', "CSA Engineering");
    $mail->addAddress($recipient); // Add a recipient

    // Content
    $mail->isHTML(false); // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body = $message;

    $mail->send();
  } catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
}

include 'conn.php'; // Assuming this file contains database connection details

// Check if today is a holiday
foreach ($upcomingHolidays as $holiday) {
  if ($holiday[1] == $currentDate) {
    // Check if an email has already been sent for this holiday
    $stmt = $conn->prepare('SELECT sent_at FROM sent_emails WHERE holiday_date = ?');
    $stmt->bind_param('s', $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $sentEmail = $result->fetch_assoc();

    if (!$sentEmail) {
      // Send the holiday email
      $stmt2 = $conn->prepare('SELECT email_id, Name FROM csa_finance_employee_info');
      $stmt2->execute();
      $result = $stmt2->get_result();
      $employees = $result->fetch_all(MYSQLI_ASSOC);

      $subject = "Holiday Notice: " . $holiday[3];

      foreach ($employees as $employee) {
        $message = "Dear {$employee['Name']},\n\nToday is " . $holiday[3] . " (" . $holiday[2] . "). Enjoy your holiday!\n\nRegards,\nCSA Engineering";
        sendEmail($employee['email_id'], $subject, $message);

        // Update the sent_emails table to mark the email as sent
        $stmt = $conn->prepare('INSERT INTO sent_emails (holiday_date, holiday_name, sent_at) VALUES (?, ?, NOW())');
        $stmt->bind_param('ss', $currentDate, $holiday[3]);
        $stmt->execute();
      }
    }
  }
}




?>





<!-- Modal -->

<div class="modal fade" id="holidayModal" tabindex="-1" role="dialog" aria-labelledby="holidayModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="holidayModalLabel">Holiday List</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
            <tr>
              <th>Holiday</th>
              <th>Day</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($upcomingHolidays as $holiday) {
              echo "<tr>";
              echo "<td>{$holiday[3]}</td>";
              echo "<td>{$holiday[2]}</td>";
              echo "<td>{$holiday[1]}</td>";
              echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Get all elements with class "view-list" (View List buttons)
    var btns = document.querySelectorAll('.view-list');

    // Loop through each button and attach a click event listener
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        // Show the modal
        $('#holidayModal').modal('show');
      });
    });
  });
</script>









<style>
  h1 {
    font-family: 'Poppins', sans-serif;
    /* Google Font */
    font-size: 2.5rem;
    /* Adjust size as per your design */
    font-weight: 700;
    /* Bold */

    line-height: 1.2;
    /* Spacing between lines */
  }

  p {
    font-family: 'Poppins', sans-serif;
    /* Consistent font family */
    font-size: 1rem;
    /* Smaller text */
    /* Light gray for subtext */
    margin-top: -5px;
    /* Adjust spacing for better alignment */
  }


  .search-bar {
    padding: 10px;
    border-radius: 15px;
    /* background-color: #f8f9fa; */
    background-color: rgb(244, 244, 244);
    /* Light background */
  }

  .search-input {
    border: none;
    border-radius: 15px 0 0 15px;
    height: 55px;
    font-size: 1rem;

    outline: none;
    padding: 1rem;
  }

  .search-btn {
    border-radius: 0 15px 15px 0;
    height: 55px;
    background-color: #333;
    /* Dark button */
    color: #fff;
  }

  .search-btn:hover {
    background-color: gray;
  }

  .btn-icon {
    border: none;
    border-radius: 15px;
    background-color: #fff;
    /* White background */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    /* Subtle shadow */
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 10px;
  }

  .btn-icon i {
    font-size: 1.2rem;
    color: #333;
  }

  .btn-icon:hover {
    background-color: #e9ecef;
  }


  .card {
    border: none;

  }



  .officeCard {

    width: 100%;
    /* Ensure consistent maximum width */
    height: 100%;
    /* Fixed height for uniformity */
    border-radius: 15px;
    /* Rounded corners */
    overflow: hidden;
    /* Prevent overflow of content */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    /* Add shadow for a floating effect */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    /* Smooth animation for hover */
    position: relative;
    margin: 0 auto;
    /* Center cards horizontally */
  }

  .officeCard:hover {
    transform: translateY(-15px);
    /* Slight upward movement on hover */
    box-shadow: 0 15px 16px rgba(0, 0, 0, 0.3);
    /* Stronger shadow on hover */
  }


  .holiday-card {
    border-radius: 15px;
    /* background-image: url('https://mildurahouseboats.com.au/wp-content/uploads/2016/06/proc-ico3.png'); */
    background-size: cover;
    background-position: center;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;

  }

  .text-border-custom {
    border-bottom: 2px solid #007bff;
    display: inline-block;
    padding-bottom: 5px;
  }

  .view-list {
    font-size: 0.875rem;
    text-decoration: none;
  }

  .view-list:hover {
    text-decoration: underline;
    color: #0056b3;
  }

  .holiday-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 15px 16px rgba(0, 0, 0, 0.3);
  }


  .card-img {
    width: 100%;
    height: 100%;
    /* Set image height proportion */
    object-fit: cover;
    /* Ensure the image covers the card */
    border-bottom: 2px solid #ddd;
    /* Separator line */
  }



  .card-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 5px;
  }

  .card-subtitle {
    font-size: 1rem;
    margin-bottom: 15px;
  }

  .link button {
    font-size: 0.9rem;
    padding: 8px 12px;
    border-radius: 8px;
    background-color: #007bff;
    /* Bootstrap Primary Blue */
    border: none;
    color: #fff;
    cursor: pointer;
  }

  .link button:hover {
    background-color: #0056b3;
    /* Darker blue for hover effect */
  }




  #searchResults::-webkit-scrollbar {
    display: none;
  }


  .text-border-custom {
    -webkit-text-stroke: 1px orange;
  }


  /* General card styles */
  .stats-card {
    border-radius: 12px;
    /* Rounded corners */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    /* Subtle shadow */
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    /* Smooth hover effect */
    padding: 20px;
    /* Internal spacing */
    color: #fff !important;
    background-color: #0056b3 !important;

  }

  /* Hover effect for cards */
  .stats-card:hover {
    transform: scale(1.05);
    /* Slight zoom on hover */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
    /* Elevated shadow */
  }

  /* Icon styles */
  .stats-icon {
    font-size: 36px;
    /* Large icon size */
    margin-bottom: 10px;
    /* Space between icon and title */
  }

  /* Title styles */
  .stats-title {
    font-size: 18px;
    /* Adjust title font size */
    font-weight: bold;
    /* Make title stand out */
    margin: 0;
    /* Remove default margin */
    text-align: center;
    /* Center-align text */
    text-shadow: rgba(0, 0, 0, 0.5);
    /* Text shadow for better readability */
  }

  /* Gradient backgrounds for each card */
  .actionCard1 {
    background: linear-gradient(45deg, rgb(9, 43, 116), rgb(77, 133, 255));
    /* Fiery Red */
  }

  .actionCard2 {
    background: linear-gradient(45deg, rgb(9, 43, 116), rgb(77, 133, 255));
    /* Sky Blue */
  }

  .actionCard3 {
    background: linear-gradient(45deg, rgb(9, 43, 116), rgb(77, 133, 255));
    /* Lime Green */
  }

  .actionCard4 {
    background: linear-gradient(45deg, rgb(77, 133, 255), rgb(6, 212, 185));
    /* Golden Yellow */

    /* Use dark text for contrast */
  }

  .actionCard5 {
    background: linear-gradient(45deg, rgb(77, 133, 255), rgb(6, 212, 185));
    /* Lavender Purple */
  }

  .actionCard6 {
    background: linear-gradient(45deg, rgb(77, 133, 255), rgb(6, 212, 185));
    /* A warm gradient with shades of orange */
  }



  /* Responsive design for smaller screens */
  @media (max-width: 768px) {
    .stats-card {
      padding: 15px;
    }

    .stats-icon {
      font-size: 28px;
      /* Smaller icons */
    }

    .stats-title {
      font-size: 16px;
      /* Smaller titles */
    }
  }


  .info-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    width: 100%;
    /* Set max width for the card */
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
  }

  /* Hover effect for the card */
  .info-card:hover {
    transform: translateY(-5px);
    /* Lift the card slightly */
  }

  /* Image as overlay */
  .info-card img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* Ensure the image covers the area of the card */
    opacity: 0.3;
    /* Reduce the opacity of the image */
    z-index: 1;
    /* Place the image in the background */
  }

  /* Card text content */
  .info-card .p-4 {
    position: relative;
    z-index: 2;
    text-align: center;
  }

  .info-card .text-border-custom {
    font-size: 24px;
    font-weight: bold;
    color: #ffcc00;
    /* Highlight the date */
  }

  .info-card .font-weight-bold {
    font-size: 18px;
    font-weight: 600;
  }

  .info-card a.view-list {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    background-color: #ff5733;
    padding: 8px 15px;
    border-radius: 25px;
    margin-top: 15px;
    display: inline-block;
  }

  .info-card a.view-list:hover {
    background-color: #ff2f00;
    /* Darker color on hover */
    text-decoration: underline;
  }

  /* Responsive design */
  @media (max-width: 768px) {
    .info-card {
      max-width: 100%;
    }

    .info-card .text-border-custom {
      font-size: 20px;
    }
  }



  .content-container {
    text-align: center;
    /* Center text content */
  }

  /* Optional styling for text */
  .text-border-custom {
    -webkit-text-stroke: 1px orange;
    font-size: 24px;
    font-weight: bold;
    color: #ffcc00;
    /* Highlight the date */
  }

  .view-list {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    background-color: #ff5733;
    padding: 8px 15px;
    border-radius: 25px;
    margin-top: 15px;
    display: inline-block;
  }

  .view-list:hover {
    background-color: #ff2f00;
    /* Darker color on hover */
    text-decoration: underline;
  }

  .list-item {
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .list-item:hover {
    background-color: #e0e0e0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transform: translateY(-5px);
  }

  .list-item .list-icon {
    background-color: #007bff;
    color: #fff;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-right: 15px;
    font-size: 1.5rem;
  }

  .list-item .list-icon:hover {
    background-color: #0056b3;
  }

  /* Text styling for list items */
  .list-item p {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 0;
  }

  /* Spacing adjustments for individual items */
  .list-item .ms-3 {
    margin-left: 15px;
  }



  .green {
    background: #66FF99;
  }

  .circle {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    box-shadow: 0px 0px 1px 1px #0000001a;
  }

  .pulse {
    animation: pulse-animation 2s infinite;
  }

  @keyframes pulse-animation {
    0% {
      box-shadow: 0 0 0 0px rgba(0, 0, 0, 0.2);
    }

    100% {
      box-shadow: 0 0 0 20px rgba(0, 255, 42, 0);
    }
  }

  canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
  }
</style>


<style>
  /* ✅ Google Default Dark Button */
  .google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    gap: 12px;
    padding: 12px;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    font-family: "Roboto", sans-serif;
    border-radius: 8px;
    border: none;
    color: #fff;
    background: #202124;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    text-decoration: none;
  }

  .google-btn:hover {
    background: #292A2D;
    transform: scale(1.05);
    /* Slightly enlarges the button */

  }

  .google-btn img {
    width: 22px;
    height: 22px;
  }

  /* ✅ Google Sync Button */
  .sync-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    gap: 10px;
    background: #0F9D58;
    color: white;
    font-weight: 600;
    padding: 14px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    transition: 0.3s ease-in-out;
    cursor: pointer;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  }

  .sync-btn:hover {
    background: #0C7C43;
  }

  .sync-btn img {
    width: 24px;
    height: 24px;
  }

  .sync-btn .spinner {
    width: 20px;
    height: 20px;
    border: 3px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: none;
  }

  /* ✅ Loading Animation */
  @keyframes spin {
    from {
      transform: rotate(0deg);
    }

    to {
      transform: rotate(360deg);
    }
  }
</style>





<?php
// Initialize an array to hold the projects from all tables
$projects = [];

// Query to get project IDs from the projects table
$sql_projects = "SELECT project_id, project_name ,urgency,branch_code FROM projects WHERE urgency = 'green'";
$result_projects = $conn->query($sql_projects);
if ($result_projects->num_rows > 0) {
  while ($row = $result_projects->fetch_assoc()) {
    $row['table_name'] = 'projects';
    $projects[] = $row;
  }
}

// Query to get project IDs from the deliverable_data table
$sql_Deliverable_projects = "SELECT project_id, project_name, urgency,projectClosed FROM projects WHERE urgency = 'purple'  AND deliverable_mailDate IS NULL";
$result_deliverable = $conn->query($sql_Deliverable_projects);
if ($result_deliverable->num_rows > 0) {
  while ($row = $result_deliverable->fetch_assoc()) {
    $row['table_name'] = 'projects';  // Add the table name
    $projects[] = $row;
  }
}


$sql_Closed_project = "
                      SELECT project_id, project_name, urgency, projectClosed, 'Deliverable Projects' AS project_type
                      FROM projects 
                      WHERE urgency = 'purple' 
                      AND deliverable_mailDate IS NULL 

                      UNION ALL

                      SELECT project_id, project_name, urgency, projectClosed, 'Closed Projects' AS project_type
                      FROM projects 
                      WHERE urgency = 'purple' 
                      AND deliverable_mailDate IS NOT NULL 
                      AND projectClosed IS NOT NULL 


                      ";

$result_Closed = $conn->query($sql_Closed_project);
if ($result_Closed->num_rows > 0) {
  while ($row = $result_Closed->fetch_assoc()) {
    $row['table_name'] = 'projects';  // Add the table name
    $projects[] = $row;  // Store each project in the array
  }
}






// Close the database connection
$conn->close();

$current_date = date("F j, Y");

?>

<script>
  const projects = <?php echo json_encode($projects); ?>;

  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");

    // Function to filter and display projects
    function filterProjects(query) {
      // Filter projects based on the search query
      const filteredProjects = projects.filter(project =>
        project.project_name.toLowerCase().includes(query.toLowerCase()) ||
        project.project_id.toString().includes(query) // Check project ID as string
      );

      // Clear previous results
      searchResults.innerHTML = '';

      // Display matching results
      if (filteredProjects.length > 0) {
        filteredProjects.forEach(project => {
          const resultItem = document.createElement('a');
          resultItem.href = '#';
          resultItem.classList.add('list-group-item', 'list-group-item-action');
          resultItem.textContent = `From: ${project.project_type
            ? project.project_type
            : project.urgency === 'green'
              ? 'Open Projects'
              : 'Closed Projects'
            }, Name: ${project.project_name} (ID: ${project.project_id})`;

          resultItem.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent the default anchor click behavior

            // Populate the search input with the selected project name and project ID
            searchInput.value = `ID: ${project.project_id} - Name: ${project.project_name}`;

            // Clear the search results after selection
            searchResults.innerHTML = '';

            // Trigger search in DataTable (if DataTable is initialized)
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
              $('#dataTable').DataTable().search(searchInput.value).draw();
            }

            // Redirect to the appropriate page based on project_type
            if (project.project_type === 'Deliverable Projects') {
              // Redirect to the Deliverables Data page
              window.location.replace('develirables_data.php?project_id=' + project.project_id);
            } else if (project.project_type === 'Closed Projects' || project.urgency === 'purple' && project.projectClosed !== null) {
              // Redirect to the Closed Projects page
              window.location.replace('closed_projects.php?project_id=' + project.project_id);
            } else if (project.urgency === 'green') {
              // Redirect to the Open Projects page
              window.location.replace('openprojects.php?project_id=' + project.project_id);
            } else {
              // Handle unknown or other cases by redirecting to Closed Projects page
              window.location.replace('closed_projects.php?project_id=' + project.project_id);
            }

          });

          searchResults.appendChild(resultItem);
        });
      } else {
        // If no results, show a "No results found" message
        const noResult = document.createElement('p');
        noResult.classList.add('list-group-item', 'list-group-item-action', 'text-center');
        noResult.textContent = 'No results found';
        searchResults.appendChild(noResult);
      }
    }

    // Event listener for input to search projects live
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim();
      if (query) {
        filterProjects(query); // Filter and display projects
      } else {
        searchResults.innerHTML = ''; // Clear results if input is empty
      }
    });
  });
</script>

<style>
  /***** Default Light Mode Styles *****/
  .introjs-tooltip {
    background-color: #fff !important;
    /* White background */
    color: #333 !important;
    /* Dark text */
    border-radius: 8px !important;
    box-shadow: rgb(0 0 0 / 10%) 0px 4px 10px !important;
    border: 1px solid #ddd !important;
  }

  .introjs-tooltip-title {
    color: #222 !important;
  }

  .introjs-tooltiptext {
    color: #333 !important;
  }

  .introjs-button {
    background-color: #007bff !important;
    /* Blue buttons */
    color: #fff !important;
    border: none;
  }

  .introjs-button:hover {
    background-color: #0056b3 !important;
  }

  /***** Dark Mode Styles *****/
  [data-theme="dark"] .introjs-tooltip {
    background-color: #222 !important;
    /* Dark background */
    color: #ddd !important;
    /* Light text */
    border: 1px solid #444 !important;
    border-radius: 8px !important;
    box-shadow:
      rgb(255 255 255 / 80%) 0px 0px 1px 2px,
      /* White glow */
      rgb(66 55 147 / 50%) 0px 0px 0px 5000px;
    /* Dark overlay */
  }

  [data-theme="dark"] .introjs-tooltip-title {
    color: #f5f5f5 !important;
    /* Light title */
  }

  [data-theme="dark"] .introjs-tooltiptext {
    color: #ccc !important;
    /* Light text */
  }

  [data-theme="dark"] .introjs-button {
    background-color: #444 !important;
    /* Dark buttons */
    color: #fff !important;
    border: 1px solid #555 !important;
  }

  [data-theme="dark"] .introjs-button:hover {
    background-color: #666 !important;
  }

  /***** Default Light Mode *****/
  .introjs-helperLayer {
    background: rgba(255, 255, 255, 0.5) !important;
    /* Light translucent background */
    border-radius: 8px !important;
    box-shadow:
      rgb(0 0 0 / 20%) 0px 0px 10px 4px,
      /* Soft shadow */
      rgba(255, 255, 255, 0.3) 0px 0px 8px 2px;
    /* Subtle glow */
  }

  /***** Dark Mode Styles *****/
  [data-theme="dark"] .introjs-helperLayer {
    background: rgba(255, 255, 255, 0.1) !important;
    /* Slightly visible layer */
    border-radius: 8px !important;
    box-shadow:
      rgb(255 255 255 / 80%) 0px 0px 2px 3px,
      /* White glow effect */
      rgb(66 55 147 / 50%) 0px 0px 0px 5000px;
    /* Deep purple overlay */
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    /* Soft white border */
    transition: all 0.3s ease-in-out;
  }

  /***** Adjusting the Overlay to Match Dark Mode *****/
  [data-theme="dark"] .introjs-overlay {
    background-color: rgba(0, 0, 0, 0.8) !important;
    /* Dark overlay */
  }
</style>

<button onclick="resetIntro()" hidden>Restart Tour</button>




<div class="container-fluid vh-100 ">
  <div class="row h-100 mb-5">
    <div class="col-12 col-md-12  text-white d-flex justify-content-center align-items-center">
      <div class="container-fluid">
        <div class="row  d-flex align-items-top">
          <!-- Left Section -->
          <div class="col-lg-8">
            <div class="row">
              <!-- Header -->
              <div class="col-lg-12 ">
                <div class="header mt-4">
                  <h1 class="text-capitalize">Hello <?php echo $user_name; ?></h1>
                  <h5>Welcome to the Employee Dashboard! Your RAE ID is: <?= $user_id ?></h5>
                </div>
              </div>

              <!-- Search Bar -->
              <div class="col-lg-12 d-flex align-items-center mt-3">
                <div class="search-bar w-100">
                  <div class="input-group">
                    <input type="text" class="search-input form-control" placeholder="Search Project..."
                      id="searchInput">
                    <button class="btn btn-secondary search-btn" type="button">
                      <i class="bi bi-search"></i>
                    </button>
                  </div>
                  <div id="searchResults" class="list-group position-fixed mt-1"
                    style="max-height: 260px; overflow-y: auto; z-index: 1001; ">
                  </div>
                </div>
              </div>
            </div>
          </div>




          <div class="row">

            <div class="<?php echo $user_role !== 2 ? 'col-md-8' : 'col-md-12'; ?>">

              <!-- Cards Section -->
              <?php
              function renderCard($href, $iconClass, $title, $cardClass, $Pulse = '', $count = '', $dataToggle = '')
              {
                $dataAttr = $dataToggle ? "data-bs-toggle='{$dataToggle}'" : "";

                echo "
                      <div class='col-md-4 col-sm-6 mt-4'>
                        <a href='{$href}' {$dataAttr} style='text-decoration:none;'>
                          <div class='card stats-card {$cardClass}'>
                            <div class='card-body text-center d-flex flex-column justify-content-center align-items-center'>
                              <div>
                                <i class='{$iconClass} stats-icon mb-3'></i>
                                <p class='mt-2'>{$count}</p>
                              </div>
                              <p class='stats-title'>{$title}</p>
                            </div>
                          </div>
                        </a>
                      </div>
                    ";
              }
              $AllActiveProjects;
              $myProjects;
              $deliverables;
              $archive;

              $cards = [
                // Cards for role 1
                1 => [
                  ['href' => './openprojects.php', 'iconClass' => 'bi bi-clipboard-data', 'title' => 'Open Projects', 'cardClass' => 'actionCard1', 'Pulse' => 'circle pulse green', 'count' => $AllActiveProjects],
                  ['href' => './develirables_data.php', 'iconClass' => 'bi bi-box-seam', 'title' => 'Completed Projects', 'cardClass' => 'actionCard2', 'Pulse' => '', 'count' => $deliverables],
                  ['href' => './all-projects.php', 'iconClass' => 'bi bi-archive', 'title' => 'All Projects', 'cardClass' => 'actionCard3', 'Pulse' => '', 'count' => $archive],
                  // ['href' => './performance.php', 'iconClass' => 'bi bi-bar-chart-line', 'title' => 'Performance Screen', 'cardClass' => 'actionCard4', 'Pulse' => '', 'count' => ''],
                  ['href' => './closed_projects.php', 'iconClass' => ' fa-solid fa-bars-progress', 'title' => 'Closed Project', 'cardClass' => 'actionCard4', 'Pulse' => '', 'count' => $AllActiveProjects],
                  ['href' => './allApps.php', 'iconClass' => 'bi bi-grid', 'title' => 'Home', 'cardClass' => 'actionCard5', 'Pulse' => '', 'count' => 'Back to all apps']

                ],
                // Cards for other roles
                'default' => [
                  
                  ['href' => './openprojects.php', 'iconClass' => 'bi bi-clipboard-data', 'title' => 'Open Projects', 'cardClass' => 'actionCard1', 'Pulse' => 'circle pulse green', 'count' => $myProjects],
                ],
                // Shared card
                'shared' => [
                  ['href' => 'time-sheet.php', 'iconClass' => 'bi bi-clock-history', 'title' => 'Time Sheet', 'cardClass' => 'actionCard6', 'Pulse' => '', 'count' => 'Timesheet & Leave'],
                  
                ],
              ];
              ?>

              <!-- Usage -->
              <div class="row">
                <?php
                // Render cards based on user role
                // Render role-specific cards
                $roleCards = $cards[$user_role] ?? $cards['default'];
                foreach ($roleCards as $card) {
                  $Pulse = $card['Pulse'] ?? '';
                  $dataToggle = $card['dataToggle'] ?? '';
                  renderCard($card['href'], $card['iconClass'], $card['title'], $card['cardClass'], $Pulse, $card['count'], $dataToggle);
                }

                // Render shared cards (including reimbursement modal)
                foreach ($cards['shared'] as $card) {
                  $Pulse = $card['Pulse'] ?? '';
                  $dataToggle = $card['dataToggle'] ?? '';
                  renderCard($card['href'], $card['iconClass'], $card['title'], $card['cardClass'], $Pulse, $card['count'], $dataToggle);
                }

                ?>
              </div>

            </div>


            <?php
            if ($user_role != 2) {
              ?>
              <!-- List Card -->
              <div class=" col-md-4 col-sm-12 mt-4">

                <!-- Usage -->
                <div class="card stats-card">
                  <div class="card-body">
                    <!-- Title Section -->
                    <div class="mt-4">
                      <h4 class="card-title">Quick Shortcuts</h4>
                    </div>

                    <!-- Shortcuts List -->
                    <div class="row mt-4 mb-3">
                      
                      <!-- Create New Project -->
                      <div class="col-12 mb-3">
                        <div class="list-item d-flex align-items-center justify-content-between">
                          <a data-toggle="modal" data-target="#add_project">
                            <div class="d-flex align-items-center">
                              <div class="list-icon mr-3">
                                <i class="bi bi-file-earmark-plus"></i>
                              </div>
                              <div class="ms-3">
                                <p class="mb-0" style="font-size:1.2rem; color:black;">Create New Project</p>
                              </div>
                            </div>
                          </a>
                        </div>
                      </div>

                      <!-- Create New Subproject -->
                      <div class="col-12 mb-3">
                        <div class="list-item d-flex align-items-center justify-content-between">
                          <a data-toggle="modal" data-target="#add_subproject">
                            <div class="d-flex align-items-center">
                              <div class="list-icon mr-3">
                                <i class="bi bi-folder-plus"></i>
                              </div>
                              <div class="ms-3">
                                <p class="mb-0" style="font-size:1.2rem; color:black;">Create New Subproject</p>
                              </div>
                            </div>
                          </a>
                        </div>
                      </div>

                      <!-- Create New Revision Project -->
                      <div class="col-12">
                        <div class="list-item d-flex align-items-center justify-content-between">
                          <a data-toggle="modal" data-target="#add_revisionproject">
                            <div class="d-flex align-items-center">
                              <div class="list-icon mr-3">
                                <i class="bi bi-pencil-square"></i>
                              </div>
                              <div class="ms-3">
                                <p class="mb-0" style="font-size:1.2rem; color:black;">Create New Revision Project</p>
                              </div>
                            </div>
                          </a>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

              </div>

            </div>

          </div>
        </div>

      </div>

    <?php } ?>

    <?php

    include './conn.php';
    //  year
    $selected_year = isset($_POST['selected_year']) ? $_POST['selected_year'] : date("Y");
    // Initialize an array for monthly working hours
    $monthlyWorkingHours = [];
    // Fetch data for each month
    for ($selected_month = 1; $selected_month <= 12; $selected_month++) {
      $sql = "
                        SELECT 
                            COALESCE((SELECT SUM(md.meetingHours) FROM meeting_data md WHERE md.employee_id = $user_id AND MONTH(md.meeting_date) = $selected_month AND YEAR(md.meeting_date) = $selected_year), 0) AS totalMeetingHours,
                            COALESCE((SELECT SUM(ts.working_hours) FROM timesheet ts WHERE ts.user_id = $user_id AND MONTH(ts.date_value) = $selected_month AND YEAR(ts.date_value) = $selected_year), 0) AS totalWorkingHours,
                            COALESCE((SELECT SUM(sj.workingHours) FROM smallJobs sj WHERE sj.user_id = $user_id AND MONTH(sj.job_date) = $selected_month AND YEAR(sj.job_date) = $selected_year), 0) AS totalSmallHours;
                        ";

      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $monthlyWorkingHours[] =
          $row['totalMeetingHours'] +
          $row['totalWorkingHours'] +
          $row['totalSmallHours']; // Only summing meeting, working, and small hours
      } else {
        $monthlyWorkingHours[] = 0; // Default to 0 if no data found
      }
    }

    ?>


    <div class="col-12 col-md-12  text-white d-flex justify-content-center align-items-center mt-5 ">
      <div class="container-fluid">

        <div class="row ">


          <div class="col-md-12">
            <div class="card officeCard shadow-lg">
              <div class="d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
                <!-- Year Selection Form -->
                <form method="POST" id="yearForm" class="mt-4">
                  <div class="form-group">
                    <select id="selected_year" name="selected_year" class="form-control" onchange="this.form.submit()">
                      <option value="2023" <?php echo $selected_year == '2023' ? 'selected' : ''; ?>>2023</option>
                      <option value="2024" <?php echo $selected_year == '2024' ? 'selected' : ''; ?>>2024</option>
                      <option value="2025" <?php echo $selected_year == '2025' ? 'selected' : ''; ?>>2025</option>
                    </select>
                  </div>
                </form>

                <!-- Bar Graph -->
                <div class="graph-container w-100 p-3">
                  <canvas id="barGraph"></canvas>
                </div>

                <!-- Footer Labels -->
                <div class="graph-footer text-center mt-3 mb-3">
                  <p class="mb-1 text-muted">Performance Chart</p>
                  <p class="mb-0 text-muted">
                    <span id="currentMonth"></span> <span id="currentDate"></span>
                    <span id="currentYear"><?php echo $selected_year; ?></span>
                  </p>
                </div>
              </div>
            </div>
          </div>




         



        </div>


      </div>
    </div>



  </div>
  <div class="vh-100">

  </div>
</div>



</div>


<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Bar Graph Script -->
<script>
  const ctx = document.getElementById('barGraph').getContext('2d');
  const barGraph = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], // Months
      datasets: [{
        label: 'My Working Hours (<?php echo $selected_year; ?>)', // Dynamic year
        data: <?php echo json_encode($monthlyWorkingHours); ?>, // PHP array converted to JavaScript
        backgroundColor: [
          'rgba(54, 162, 235, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 206, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(153, 102, 255, 0.2)',
          'rgba(255, 159, 64, 0.2)',
          'rgba(54, 162, 235, 0.2)',
          'rgba(255, 99, 132, 0.2)',
          'rgba(255, 206, 86, 0.2)',
          'rgba(75, 192, 192, 0.2)',
          'rgba(153, 102, 255, 0.2)',
          'rgba(255, 159, 64, 0.2)'
        ],
        borderColor: [
          'rgba(54, 162, 235, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 99, 132, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>






<div class="modal fade" id="disabledFeature" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Feature disabled</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Creating new project from rae system is currently disabled if you need to create new project use count (finance
        app) "quick project" .
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>




<!-- ✅ Load Google Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">





<?php




if (isset($_POST['branch-syd'])) {
  $_SESSION['branch_choice'] = $_POST['branch-syd'];
  header('Location: openprojects.php'); // Redirect to the next page
  exit;
} else if (isset($_POST['branch-bris'])) {
  $_SESSION['branch_choice'] = $_POST['branch-bris'];
  header('Location: openprojects.php'); // Redirect to the next page
  exit;
} else if (isset($_POST['branch-all'])) {
  $_SESSION['branch_choice'] = $_POST['branch-all'];
  header('Location: openprojects.php'); // Redirect to the next page
  exit;
} else {
  echo '';
}














include 'include/footer.php';
?>
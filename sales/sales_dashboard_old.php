<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);



require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: ../index.php');
}

// check admin
$user_role = $_SESSION['user_role'];

include 'enquiry.php';

include './include/sidebar.php';


if (isset($_GET['delete_user'])) {
  $action_id = $_GET['id'];

  $sql = "DELETE FROM enquiry_sales WHERE id = :id";
  $sent_po = "sales_dashboard.php";
  $obj_admin->delete_data_by_this_method($sql, $action_id, $sent_po);
  $_SESSION['status_success'] = "Enquiry Deleted Successfully";
}

if (isset($_POST['quotationSent'])) {
  $enquiryId = $_POST['enquiryId'];
  $quotation_sent_date = $_POST['quotation_sent_date'];
  $status = '75';
  $status1 = 'Sent';

  // Update the enquiry_status in enquiry_sales
  $sql = "UPDATE enquiry_sales SET enquiry_status = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('si', $status, $enquiryId);
  $stmt->execute();
  $stmt->close();

  // Update the status and quotation_sent_date in quotations
  $sql2 = "UPDATE quotations SET status = ?, quotation_sent_date = ? WHERE enquiry_id = ?";
  $stmt = $conn->prepare($sql2);  // Corrected from $sql1 to $sql2
  $stmt->bind_param('ssi', $status1, $quotation_sent_date, $enquiryId);
  $stmt->execute();
  $stmt->close();
}

if (isset($_POST['createQuotation'])) {
  $enquiryId = $_POST['enquiryId'];
  $quotation_name = $_POST['quotation_name'];
  $quotation_assigned_id = $_POST['quotation_assigned_id'];
  $message = $_POST['message'];

  // Prepare and execute the insertion of the quotation
  $sql = "INSERT INTO quotations (enquiry_id, customer_id, quotation_name, quotation_assigned_id, message)
          SELECT id, contact_id, ?, ?, ? FROM enquiry_sales WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sisi", $quotation_name, $quotation_assigned_id, $message, $enquiryId);

  if ($stmt->execute()) {
    $quotation_no = $stmt->insert_id;

    // Update enquiry_status in enquiry_sales table to 50
    $updateStatusSql = "UPDATE enquiry_sales SET enquiry_status = 50 WHERE id = ?";
    $stmtUpdateStatus = $conn->prepare($updateStatusSql);
    $stmtUpdateStatus->bind_param("i", $enquiryId);

    if (!$stmtUpdateStatus->execute()) {
      echo "Error updating enquiry status: " . $stmtUpdateStatus->error;
    }
    $stmtUpdateStatus->close();
  } else {
    echo "Error moving record: " . $stmt->error;
  }
  $stmt->close();

  // Fetch employee details
  $sqlEmployee = "SELECT fullname, email FROM tbl_admin WHERE user_id = ?";
  $stmt = $conn->prepare($sqlEmployee);
  $stmt->bind_param("i", $quotation_assigned_id);
  $stmt->execute();
  $stmt->bind_result($employeeName, $employeeEmailId);
  $stmt->fetch();
  $stmt->close();

  // Fetch the most recent quotation number
  $sqlQuotation = "SELECT quotation_no, quotation_assigned_id, quotation_name FROM quotations WHERE enquiry_id = ? ORDER BY quotation_no DESC LIMIT 1";
  $stmt = $conn->prepare($sqlQuotation);
  $stmt->bind_param("i", $enquiryId);
  $stmt->execute();
  $stmt->bind_result($latestQuotationNo, $latestQuotationAssignedId, $latestQuotationName);
  $stmt->fetch();
  $stmt->close();

  // Insert notification
  $msg_status = 'unread';
  $software = 'OBI';
  $notificationSql = "INSERT INTO notifications (msg_from, msg_subject, msg_content, msg_status, msg_to, software, quotation_no)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmtNotify = $conn->prepare($notificationSql);
  $stmtNotify->bind_param('isssssi', $user_id, $quotation_name, $message, $msg_status, $quotation_assigned_id, $software, $quotation_no);
  $stmtNotify->execute();
  $stmtNotify->close();

  // Compose email message
  $emailContent = "
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
                  <h2>Quotation</h2>
              </div>
              <div class='content'>
                  <p>Dear $employeeName,</p>
                  <p>Please review the quotation and acknowledge it.</p>
                  <p>You can find the details below:</p>
                  <p>$message</p>
                  <p>Send me the quotation through the RAE system. Please do not reply to this email.</p>
                  <form action='https://csaappstore.com/sales/api.php' method='get'>
                      <input type='hidden' name='quotation_name' value='$latestQuotationName'>
                      <input type='hidden' name='quotation_assigned_id' value='$latestQuotationAssignedId'>
                      <input type='hidden' name='quotation_no' value='$latestQuotationNo'>
                      <button type='submit' name='quotation_acknowledgement' value='Accepted'>Accept Quotation</button>
                  </form>
                  <p><br><br>
                  Please feel free to reach out if you require any additional information or if there are any specific instructions.<br>
                  </p>
                  <p>Best regards,<br>$user_name</p>
              </div>
              <div class='footer'>
                  <p>This is an automated notification. Please do not reply to this email.</p>
              </div>
          </div>
      </body>
      </html>
  ";

  // Send email using PHPMailer
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = "engineering@csaengineering.com.au";
    $mail->Password = "kezfduovpirmalcs";
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('revanthshiva@csaengineering.com.au', $user_name);
    $mail->addAddress($employeeEmailId, $employeeName);

    $mail->isHTML(true);
    $mail->Subject = "Acknowledgment of Quotation Assignment";
    $mail->Body = $emailContent;

    // Attach files if any
    if (isset($_FILES['files'])) {
      foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
          $mail->addAttachment($tmp_name, $_FILES['files']['name'][$key]);
        } else {
          echo "Error with file {$key}: " . $_FILES['files']['error'][$key];
        }
      }
    }

    $mail->send();
    header('Location: ./sales_dashboard.php');
    exit();
  } catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
}


if (isset($_POST['acceptEnquiry'])) {
  $enquiryId = $_POST['enquiryId'];
  $priority = $_POST['priority'];
  $status = '25';
  $sendEmail = $_POST['sendEmail'];

  // Update enquiry_sales table
  $sql = "UPDATE enquiry_sales SET priority = ?, enquiry_status = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ssi', $priority, $status, $enquiryId);
  $stmt->execute();
  $stmt->close();

  // Check if the email should be sent
  if ($sendEmail == 'Yes') {
    // Fetch the contact email based on enquiryId
    $sql = "SELECT c.contact_email 
              FROM enquiry_sales e 
              LEFT JOIN contacts c ON e.contact_id = c.contact_id 
              WHERE e.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $enquiryId);
    $stmt->execute();
    $stmt->bind_result($contact_email);
    $stmt->fetch();
    $stmt->close();

    if ($contact_email) {
      $mail = new PHPMailer(true);

      try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "engineering@csaengineering.com.au";
        $mail->Password = "kezfduovpirmalcs";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('revanthshiva@csaengineering.com.au', $user_name);
        $mail->addAddress($contact_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Acknowledgement of Your Enquiry';
        $mail->Body    = 'Dear Customer,<br><br>Thank you for your enquiry. We acknowledge receipt and will get back to you shortly.<br><br>Best regards,<br>CSA Engineering';

        $mail->send();
        echo 'Email has been sent to ' . htmlspecialchars($contact_email);
      } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
    } else {
      echo 'No email found for the selected contact.';
    }
  }
  header('Location: sales_dashboard.php');
}


if (isset($_POST['rejectEnquiry'])) {
  $enquiryId = $_POST['enquiryId'];
  $status = 'Rejected';
  $sql = "UPDATE enquiry_sales SET enquiry_status = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('si', $status, $enquiryId);
  $stmt->execute();
  $stmt->close();
}
if (isset($_POST['changeacceptEnquiry'])) {
  $enquiryId = $_POST['enquiryId'];
  $priority = $_POST['priority'];
  $sql = "UPDATE enquiry_sales SET priority =? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('si', $priority, $enquiryId);
  $stmt->execute();
  $stmt->close();
}

// Query to get the total number of enquiries per month
$sql = "SELECT YEAR(date) AS year, MONTH(date) AS month, COUNT(*) AS totalenquiry
        FROM enquiry_sales
        GROUP BY YEAR(date), MONTH(date)
        ORDER BY YEAR(date), MONTH(date);"; // Order by year and month

$results = $conn->query($sql);

$months = [];
$totalenquiries = [];

// Month names for chart
$monthNames = [
  1 => 'January',
  2 => 'February',
  3 => 'March',
  4 => 'April',
  5 => 'May',
  6 => 'June',
  7 => 'July',
  8 => 'August',
  9 => 'September',
  10 => 'October',
  11 => 'November',
  12 => 'December'
];

// Check if the query was successful
if ($results) {
  // Create an associative array to store month-year as keys and totals as values
  $data = [];
  while ($row = $results->fetch_assoc()) {
    $key = $row['month'] . '-' . $row['year']; // Key format "month-year"
    $data[$key] = $row['totalenquiry'];
  }
  $results->free();

  // Sort data by month-year key
  ksort($data);

  // Split the sorted data into months and totalenquiries arrays
  foreach ($data as $key => $value) {
    list($month, $year) = explode('-', $key);
    $months[] = $monthNames[(int)$month] . ' ' . $year;
    $totalenquiries[] = $value;
  }
} else {
  echo "Error: " . $conn->error;
}


// Get the current year
$currentYear = date("Y");

// Query to get the total number of enquiries for the current year
$sqlTotalEnquiries = "SELECT COUNT(*) AS totalenquiries 
                      FROM enquiry_sales 
                      WHERE YEAR(date) = $currentYear";
$resultsTotalEnquiries = $conn->query($sqlTotalEnquiries);

// Query to get the total number of rejected enquiries for the current year
$sqlTotalRejected = "SELECT COUNT(*) AS totalrejected 
                     FROM enquiry_sales 
                     WHERE YEAR(date) = $currentYear AND enquiry_status = 'Rejected'";
$resultsTotalRejected = $conn->query($sqlTotalRejected);

// Query to get the total number of converted projects
$sqlConvertedProjects = "SELECT COUNT(*) AS convertedtoprojects 
                         FROM csa_sales_converted_projects";
$resultsConvertedProjects = $conn->query($sqlConvertedProjects);

// Query to get the total number of enquiries with a priority assigned
$sqlPriorityAssigned = "SELECT COUNT(*) AS totalpriorityassigned 
                        FROM enquiry_sales 
                        WHERE YEAR(date) = $currentYear AND priority IS NOT NULL AND priority != ''";
$resultsPriorityAssigned = $conn->query($sqlPriorityAssigned);

// Initialize variables to store the results
$totalEnquiries = 0;
$totalRejected = 0;
$convertedProjects = 0;
$totalPriorityAssigned = 0;

// Fetch and store the total number of enquiries
if ($resultsTotalEnquiries) {
  $rowTotalEnquiries = $resultsTotalEnquiries->fetch_assoc();
  $totalEnquiries = $rowTotalEnquiries['totalenquiries'];
  $resultsTotalEnquiries->free();
} else {
  echo "Error: " . $conn->error;
}

// Fetch and store the total number of rejected enquiries
if ($resultsTotalRejected) {
  $rowTotalRejected = $resultsTotalRejected->fetch_assoc();
  $totalRejected = $rowTotalRejected['totalrejected'];
  $resultsTotalRejected->free();
} else {
  echo "Error: " . $conn->error;
}

// Fetch and store the total number of converted projects
if ($resultsConvertedProjects) {
  $rowConvertedProjects = $resultsConvertedProjects->fetch_assoc();
  $convertedProjects = $rowConvertedProjects['convertedtoprojects'];
  $resultsConvertedProjects->free();
} else {
  echo "Error: " . $conn->error;
}

// Fetch and store the total number of enquiries with a priority assigned
if ($resultsPriorityAssigned) {
  $rowPriorityAssigned = $resultsPriorityAssigned->fetch_assoc();
  $totalPriorityAssigned = $rowPriorityAssigned['totalpriorityassigned'];
  $resultsPriorityAssigned->free();
} else {
  echo "Error: " . $conn->error;
}

// Calculate the total number of completed enquiries and remaining enquiries
$totalCompleted = $totalEnquiries - $totalRejected;
$remainingEnquiries = $totalEnquiries - $convertedProjects;


?>


<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.dataTables.css" />

  <script src="https://cdn.datatables.net/2.1.2/js/dataTables.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0 text-gray-800"> &nbsp;Dashboard</h1>
</div> -->

<div class="row container-fluid d-flex justify-content-center align-items-center">
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Enquiries</div>
            <div class="chart-container">
              <canvas id="enquiryChart" style="width: 100%; height: 350px;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Enquiries</div>
            <div class="chart-container">
              <canvas id="enquiryChartpie" style="width: 100%; height: 200px;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- <div class="col-xl-4 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase mb-1">Enquiries converted to Projects</div>
            <div class="chart-container">
              <canvas id="enquiryconvertChart" style="width: 100%; height: 200px;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>-->

</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('enquiryChart').getContext('2d');

    const config = {
      type: 'bar', // Change the chart type to 'bar'
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
          label: '# of Enquiries',
          data: <?php echo json_encode($totalenquiries); ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar fill color
          borderColor: 'rgba(54, 162, 235, 1)', // Bar border color
          borderWidth: 1, // Border width of bars
          hoverBackgroundColor: 'rgba(54, 162, 235, 0.8)', // Color when hovering over bars
          hoverBorderColor: 'rgba(54, 162, 235, 1)' // Border color when hovering
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top' // Position of the legend
          },
          tooltip: {
            mode: 'index',
            intersect: false // Tooltip shows data for all bars at once
          }
        },
        scales: {
          x: {
            display: true,
            title: {
              display: true,
              text: 'Months' // X-axis title
            }
          },
          y: {
            display: true,
            beginAtZero: true, // Ensures the y-axis starts at 0
            title: {
              display: true,
              text: 'Number of Enquiries' // Y-axis title
            }
          }
        }
      }
    };

    const enquiryChart = new Chart(ctx, config);
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('enquiryChartpie').getContext('2d');
    var enquiryChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: [
          'Closed Enquiries',
          'Total Enquiries',
          'Converted to Projects',
        ],
        datasets: [{
          label: 'Enquiries',
          data: [
            <?php echo $totalPriorityAssigned; ?>,
            <?php echo $totalCompleted; ?>,
            <?php echo $convertedProjects; ?>,
          ],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)'
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
          tooltip: {
            enabled: true
          }
        }
      }
    });
  });
</script>






<!-- Content Row -->
<div class="row">
  <!-- Begin Page Content -->
  <div class="container-fluid  mt-md-5">
    <div class=" container d-flex justify-content-end mb-5">
      <!-- <button class="btn btn-primary  " data-toggle="modal" data-target="#add_enquiry">Create New Enquiry</button> -->
    </div>
    <!-- <img alt="number-1" src="https://th.bing.com/th/id/OIG2.J2IS3rCzxLU1EF0AxQ1c?w=1024&h=1024&rs=1&pid=ImgDetMain" style="width: 1.8rem; height: 1.8rem; border-radius: 50%;"/>

    <img alt="number-2" src="https://th.bing.com/th/id/OIG1.CxojYzn.bYq.JFr0fwV6?w=1024&h=1024&rs=1&pid=ImgDetMain" style="width: 1.8rem; height: 1.8rem; border-radius: 50%;"/>
    <img  alt="number-3" src="https://th.bing.com/th/id/OIG3.tL_GqiOqKJCfQzoQ3DtH?w=1024&h=1024&rs=1&pid=ImgDetMain" style="width: 1.8rem; height: 1.8rem; border-radius: 50%;"/> -->

   
  </div>

  <script>
    $(document).ready(function() {
      ['myTablehome', 'myTablemenu1', 'myTablemenu2', 'myTablemenu3'].forEach(function(id) {
        var table = $('#' + id);
        if (table.length) {
          table.DataTable({
            "ordering": false,
          });
        } else {
          console.error('Table element not found for ID: ' + id);
        }
      });
    });
  </script>

  <style>
    /* Navigation Pills */
    .nav-pills {
      justify-content: center;
    }

    .nav-pills>li>a {
      border-radius: 25px;
      font-size: 16px;
      font-weight: bold;
      color: #333;
      /* Dark grey text */
      padding: 10px 20px;
      margin-right: 10px;
      margin-bottom: 10px;
      background-color: #f8f9fa;
      /* Light grey background */
      border: 1px solid #ddd;
      /* Light grey border */
      transition: background-color 0.3s, color 0.3s, transform 0.3s;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .nav-pills>li.active>a,
    .nav-pills>li>a:hover {
      background-color: #6c757d;
      /* Medium grey for active and hover state */
      color: #fff;
      transform: translateY(-2px);
    }

    .nav-pills>li>a:focus,
    .nav-pills>li>a:active {
      outline: none;
      box-shadow: none;
    }

    /* Tab Content */
    .tab-content>.tab-pane {
      padding: 40px;
      /* Light grey border */
      border-top: none;
      /* background-color: #303336; */
      animation: fadeIn 0.5s ease-in-out;
      /* box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    /* Tab Headings */
    .tab-content h3 {
      font-size: 28px;
      margin-bottom: 20px;
      /* color: #fff; */
      /* Dark grey */
    }

    /* Tab Paragraphs */
    .tab-content p {
      font-size: 18px;
      line-height: 1.8;
      color: #666;
      /* Medium grey */
    }



    #actionButtons {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
      margin-top: 20px;
    }

    #actionButtons a {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 50px;
      width: 100%;
      text-align: center;
      white-space: nowrap;
      padding: 10px;
      font-size: 14px;
      box-sizing: border-box;
    }

    #actionButtons a.btn {
      width: 100%;
      font-weight: 500;
    }

    .modal-body {
      padding: 20px;
    }
  </style>


  <div class="modal fade" id="action" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Action Center</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-around">
            <input type="text" name="id" id="enquiryId" readonly hidden>
            <input type="text" name="id" id="status" readonly hidden>

            <div id="actionButtons">
            </div>
            <script>
              $(document).ready(function() {
                $('#action').on('show.bs.modal', function(event) {
                  var button = $(event.relatedTarget); // Button that triggered the modal
                  var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes
                  var status = button.data('status'); // Extract info from data-* attributes
                  var folderId = button.data('folderid'); // Extract info from data-* attributes

                  // Update the modal's content.
                  var modal = $(this);
                  modal.find('#enquiryId').val(enquiryId);
                  modal.find('#status').val(status);
                  modal.find('#folderId').val(folderId);

                  var actionButtonsDiv = document.getElementById('actionButtons');
                  actionButtonsDiv.innerHTML = '';

                  // Create the reject button
                  var rejectBtn = document.createElement('a');
                  rejectBtn.className = " btn btn-sm btn-danger";
                  rejectBtn.id = 'rejectEnquiryButton';
                  rejectBtn.textContent = "Reject";
                  rejectBtn.setAttribute('data-toggle', 'modal');
                  rejectBtn.setAttribute('data-target', '#rejectEnquiry');
                  rejectBtn.setAttribute('data-dismiss', 'modal');
                  rejectBtn.setAttribute('data-enquiryid', enquiryId);

                  // View/Upload Drive files button
                  var driveBtn = document.createElement('a');
                  driveBtn.className = "btn btn-sm btn-primary";
                  driveBtn.id = 'folderId';
                  driveBtn.textContent = "View/Upload Files";
                  driveBtn.href = 'https://drive.google.com/drive/folders/' + folderId;

                  // Append action buttons based on the status
                  if (status == 0 && !document.getElementById('acceptEnquiryButton')) {
                    var acceptBtn = document.createElement('a');
                    acceptBtn.className = "btn btn-sm btn-success";
                    acceptBtn.id = 'acceptEnquiryButton';
                    acceptBtn.textContent = "Enquiry Acknowledged";
                    acceptBtn.setAttribute('data-toggle', 'modal');
                    acceptBtn.setAttribute('data-target', '#acceptEnquiry');
                    acceptBtn.setAttribute('data-dismiss', 'modal');
                    acceptBtn.setAttribute('data-enquiryid', enquiryId);
                    actionButtonsDiv.appendChild(acceptBtn);
                  } else {
                    var createQuotation = document.createElement('a');
                    createQuotation.className = "btn btn-sm btn-primary";
                    createQuotation.id = 'createQuotation';
                    createQuotation.textContent = "Create New Quotation";
                    createQuotation.setAttribute('data-toggle', 'modal');
                    createQuotation.setAttribute('data-target', '#createQuotationModal');
                    createQuotation.setAttribute('data-dismiss', 'modal');
                    createQuotation.setAttribute('data-enquiryid', enquiryId);
                    actionButtonsDiv.appendChild(createQuotation);

                    var viewQuotation = document.createElement('a');
                    viewQuotation.className = " btn btn-sm btn-info";
                    viewQuotation.id = 'viewQuotationButton';
                    viewQuotation.textContent = "View Quotation";
                    viewQuotation.href = "./viewQuotations.php?enquiryId=" + enquiryId;
                    actionButtonsDiv.appendChild(viewQuotation);

                    var changePriorityBtn = document.createElement('a');
                    changePriorityBtn.className = "btn btn-sm btn-warning";
                    changePriorityBtn.id = 'changePriorityButton';
                    changePriorityBtn.textContent = "Change Priority";
                    changePriorityBtn.setAttribute('data-toggle', 'modal');
                    changePriorityBtn.setAttribute('data-target', '#changePriority');
                    changePriorityBtn.setAttribute('data-dismiss', 'modal');
                    changePriorityBtn.setAttribute('data-enquiryid', enquiryId);
                    actionButtonsDiv.appendChild(changePriorityBtn);

                    var quotationSentBtn = document.createElement('a');
                    quotationSentBtn.className = " btn btn-sm btn-secondary";
                    quotationSentBtn.id = 'quotationSentButton';
                    quotationSentBtn.textContent = "Quotation Sent";
                    quotationSentBtn.setAttribute('data-toggle', 'modal');
                    quotationSentBtn.setAttribute('data-target', '#quotationSentModal');
                    quotationSentBtn.setAttribute('data-dismiss', 'modal');
                    quotationSentBtn.setAttribute('data-enquiryid', enquiryId);
                    actionButtonsDiv.appendChild(quotationSentBtn);

                    var sentToRaeBtn = document.createElement('a');
                    sentToRaeBtn.className = " btn btn-sm btn-dark";
                    sentToRaeBtn.id = 'sentToRaeButton';
                    sentToRaeBtn.textContent = "Sent To RAE";
                    sentToRaeBtn.href = "./send_to_rae.php?enquiry_id=" + enquiryId;
                    actionButtonsDiv.appendChild(sentToRaeBtn);
                  }

                  // Add the reject button and drive button in both cases
                  actionButtonsDiv.appendChild(rejectBtn);
                  actionButtonsDiv.appendChild(driveBtn);
                });
              });
            </script>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="acceptEnquiry" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirm!</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <!-- New Field: How was the enquiry acknowledged? -->
            <div class="form-group">
              <label for="acknowledgementMethod">How was the enquiry acknowledged?</label>
              <select name="acknowledgementMethod" id="acknowledgementMethod" class="form-control" required>
                <option value="">Select Method</option>
                <option value="Phone">Phone</option>
                <option value="Email">Email</option>
                <option value="In-Person">In-Person</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="form-group">
              <label for="sendEmail">Do you want to send an email to the customer to acknowledge the enquiry?</label>
              <select name="sendEmail" id="sendEmail" class="form-control" required>
                <option value="">Select Option</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
              </select>
            </div>

            <!-- Placeholder for real-time response -->
            <div id="emailResponse"></div>


            <!-- Existing Field: Priority -->
            <div class="form-group">
              <label for="priority">Priority</label>
              <select name="priority" id="priority" class="form-control" required>
                <option value="">Select Priority</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="text" name="enquiryId" id="enquiryId" hidden readonly>
            <button class="btn btn-success" type="submit" name="acceptEnquiry">Accept</button>
          </div>
        </form>
      </div>
    </div>
  </div>



  <script>
    $(document).ready(function() {
      $('#acceptEnquiry').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#enquiryId').val(enquiryId);
      });
    });
  </script>

  <script>
    document.getElementById('sendEmail').addEventListener('change', function() {
      var sendEmailValue = this.value;
      var enquiryId = document.getElementById('enquiryId').value;

      // Check if the selected value is "Yes"
      if (sendEmailValue === 'Yes') {
        // Perform AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'acknowledgementEmail.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('emailResponse').innerHTML = xhr.responseText;
          }
        };

        xhr.send('sendEmail=' + encodeURIComponent(sendEmailValue) + '&enquiryId=' + encodeURIComponent(enquiryId));
      } else {
        // Optionally, clear the emailResponse div if "No" is selected
        document.getElementById('emailResponse').innerHTML = '';
      }
    });
  </script>





  <div class="modal fade" id="changePriority" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Change Priority</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST">

          <div class="modal-body">
            <select name="priority" id="priority" class="form-control" required>
              <option value="">Select Priority</option>
              <option value="High">High</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="text" name="enquiryId" id="enquiryId" hidden readonly>
            <button class="btn btn-success" type="submit" name="changeacceptEnquiry">Change</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script>
    $(document).ready(function() {
      $('#changePriority').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#enquiryId').val(enquiryId);
      });
    });
  </script>

  <div class="modal fade" id="rejectEnquiry" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirm !</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to Reject this enquiry?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <form method="POST">
            <input type="text" name="enquiryId" id="enquiryId" hidden readonly>
            <button class="btn btn-danger" type="submit" name="rejectEnquiry" id="confirmDeleteButton">Reject</button>
          </form>
        </div>
      </div>
    </div>
  </div>


  <script>
    $(document).ready(function() {
      $('#rejectEnquiry').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#enquiryId').val(enquiryId);
      });
    });
  </script>



  <div class="modal fade " id="createQuotationModal" tabindex="-1" role="dialog" aria-labelledby="sendToRAELabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sendToRAELabel"> Assign Quotation </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <label for="quotation_name">Quotation Name</label>
            <input type="text" name="quotation_name" class="form-control" id="quotation_name" required>

            <label for="quotation_assigned_id" class="control-label mt-3">Select Engineer or Technical Person</label>
            <select name="quotation_assigned_id" id="quotation_assigned_id" class="form-control" required>
              <?php
              $users = "SELECT * FROM tbl_admin WHERE user_role = 1";
              $result = $conn->query($users);
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<option value='{$row['user_id']}'>{$row['fullname']}, {$row['email']}</option>";
                }
              }
              $result->close();
              ?>
            </select>

            <label for="message" class="mt-3">Message</label>
            <textarea class="form-control" name="message" id="message" required></textarea>

            <label for="enquiryId" class="mt-3">Customer Data (optional)</label>
            <input type="text" id="enquiryId" name="enquiryId" hidden>

            <label for="files" class="mt-3">Attach Files (optional)</label>
            <input type="file" name="files[]" id="files" class="form-control" multiple>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" name="createQuotation" class="btn btn-success" id="confirmSendButton">Assign</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#createQuotationModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#enquiryId').val(enquiryId);
      });
    });
  </script>

  <script>
    jQuery(document).ready(function() {

      var back = jQuery(".prev");
      var next = jQuery(".next");
      var steps = jQuery(".step");

      next.bind("click", function() {
        jQuery.each(steps, function(i) {
          if (!jQuery(steps[i]).hasClass('current') && !jQuery(steps[i]).hasClass('done')) {
            jQuery(steps[i]).addClass('current');
            jQuery(steps[i - 1]).removeClass('current').addClass('done');
            return false;
          }
        })
      });
      back.bind("click", function() {
        jQuery.each(steps, function(i) {
          if (jQuery(steps[i]).hasClass('done') && jQuery(steps[i + 1]).hasClass('current')) {
            jQuery(steps[i + 1]).removeClass('current');
            jQuery(steps[i]).removeClass('done').addClass('current');
            return false;
          }
        })
      });

    })
  </script>


  <div class="modal fade" id="quotationSentModal" tabindex="-1" role="dialog" aria-labelledby="quotationSentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quotationSentModalTitle">Quotation Sent</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Content for the quotation sent modal -->
          <form method="POST">
            <div class="form-group">
              <input type="text" name="enquiryId" id="enquiryId" hidden readonly>
              <label for="quotation_sent_date">Select the date when the quotation was sent:</label>
              <input type="date" class="form-control" id="quotation_sent_date" name="quotation_sent_date" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" name="quotationSent">Submit</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#quotationSentModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#enquiryId').val(enquiryId);
      });
    });
  </script>


  <style>
    .clearfix:after {
      clear: both;
      content: "";
      display: block;
      height: 0;
    }

    .wrapper {
      display: table-cell;
      height: 125px;
      vertical-align: middle;
    }

    .nav {
      margin-top: 40px;
    }

    .pull-right {
      float: right;
    }

    a,
    a:active {
      color: #333;
      text-decoration: none;
    }

    a:hover {
      color: #999;
    }

    .arrow-steps .step {
      font-size: 16px;
      text-align: justify;
      color: #666;
      cursor: default;
      margin: 0 3px;
      padding: 8px 10px 9px 20px;
      min-width: 160px;
      float: left;
      position: relative;
      background-color: #d9e3f7;
      user-select: none;
      transition: background-color 0.2s ease;
    }

    .arrow-steps .step:after,
    .arrow-steps .step:before {
      content: " ";
      position: absolute;
      top: 0;
      right: -20px;
      width: 0;
      height: 0;
      border-top: 20px solid transparent;
      border-bottom: 20px solid transparent;
      border-left: 20px solid #d9e3f7;
      z-index: 2;
      transition: border-color 0.2s ease;
    }

    .arrow-steps .step:before {
      right: auto;
      left: 0;
      border-left: 20px solid #fff;
      z-index: 0;
    }

    .arrow-steps .step:first-child:before {
      border: none;
    }

    .arrow-steps .step.current {
      color: #fff;
      background-color: #23468c;
    }

    .arrow-steps .step.current:after {
      border-left: 20px solid #23468c;
    }

    .arrow-steps .step:first-child {
      border-top-left-radius: 4px;
      border-bottom-left-radius: 4px;
    }

    .arrow-steps .step span {
      position: relative;
    }

    .arrow-steps .step span:before {
      opacity: 0;
      content: "✔";
      position: absolute;
      top: -2px;
      left: -30px;
    }

    .arrow-steps .step.done span:before {
      opacity: 1;
      transition: opacity 0.3s ease 0.5s;
    }

    .arrow-steps .step.accepted {
      background-color: #5cb85c;
      color: #fff;
    }

    .arrow-steps .step.accepted:after {
      border-left: 27px solid #5cb85c;
    }

    .arrow-steps .step.assigned {
      background-color: #f0ad4e;
      color: #fff;
    }

    .arrow-steps .step.assigned:after {
      border-left: 27px solid #f0ad4e;
    }

    .arrow-steps .step.ready {
      background-color: #794fd9;
      color: #fff;
    }

    .arrow-steps .step.ready:after {
      border-left: 27px solid #794fd9;
    }

    .arrow-steps .step.sent {
      background-color: #d9534f;
      color: #fff;
    }

    .arrow-steps .step.sent:after {
      border-left: 27px solid #d9534f;
    }

    .arrow-steps .step.completed {
      background-color: #23468c;
      color: #fff;
    }

    .arrow-steps .step.completed:after {
      border-left: 27px solid #23468c;
    }

    /* Responsive Design */
    @media screen and (max-width: 600px) {
      .arrow-steps .step {
        font-size: 12px;
        padding: 6px 8px;
        min-width: 120px;
      }

      .arrow-steps .step:after,
      .arrow-steps .step:before {
        border-top: 15px solid transparent;
        border-bottom: 15px solid transparent;
        border-left: 15px solid #d9e3f7;
      }

      .arrow-steps .step.current:after {
        border-left: 15px solid #23468c;
      }
    }

    /* Hover States */
    .arrow-steps .step:hover {
      background-color: #b0c4de;
      color: #333;
    }

    .arrow-steps .step.current:hover {
      background-color: #1e3a68;
    }
  </style>


  <?php include './include/footer.php'; ?>
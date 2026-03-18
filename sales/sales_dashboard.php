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
$currentYear = 2024;

$sqlEnquiriesCount = "SELECT COUNT(*) AS total FROM potential_project";
$sqlTotalSentQuotationsAmount = "SELECT SUM(amount) AS total FROM potential_project_sent_quotation";
$sqlTotalCancelledQuotationsAmount = "
SELECT SUM(amount) AS total 
FROM cancelled_quotations 
WHERE sales_id NOT IN (
    SELECT sales_id 
    FROM accepted_quotations
)
";
$sqlTotalAcceptedQuotationsAmount = "
SELECT SUM(amount) AS total 
FROM accepted_quotations
";

$resultEnquiriesCount = $conn->query(query: $sqlEnquiriesCount);
$resultSent = $conn->query(query: $sqlTotalSentQuotationsAmount);
$resultCancelled = $conn->query($sqlTotalCancelledQuotationsAmount);
$resultAccepted = $conn->query($sqlTotalAcceptedQuotationsAmount);

if ($resultEnquiriesCount) {
  $enquiriesCountRow = $resultEnquiriesCount->fetch_assoc();
  $totalEnquiriesCount = $enquiriesCountRow['total'] ?? 0;
} else {
  $totalEnquiriesCount = 0;
}

if ($resultSent) {
  $sentRow = $resultSent->fetch_assoc();
  $totalSentAmount = $sentRow['total'] ?? 0;
} else {
  $totalSentAmount = 0;
}


if ($resultCancelled) {
  $cancelledRow = $resultCancelled->fetch_assoc();
  $totalCancelledAmount = $cancelledRow['total'] ?? 0;
} else {
  $totalCancelledAmount = 0;
}

if ($resultAccepted) {
  $acceptedRow = $resultAccepted->fetch_assoc();
  $totalAcceptedAmount = $acceptedRow['total'] ?? 0;
} else {
  $totalAcceptedAmount = 0;
}




// // Query to get the total number of enquiries for the current year
// $sqlTotalEnquiries = "SELECT COUNT(*) AS totalenquiries 
//                       FROM enquiry_sales 
//                       WHERE YEAR(date) = $currentYear";

// $resultsTotalEnquiries = $conn->query($sqlTotalEnquiries);

// // Query to get the total number of rejected enquiries for the current year
// $sqlTotalRejected = "SELECT COUNT(*) AS totalrejected 
//                      FROM enquiry_sales 
//                      WHERE YEAR(date) = $currentYear AND enquiry_status = 'Rejected'";
// $resultsTotalRejected = $conn->query($sqlTotalRejected);

// // Query to get the total number of converted projects
// $sqlConvertedProjects = "SELECT COUNT(*) AS convertedtoprojects 
//                          FROM csa_sales_converted_projects";
// $resultsConvertedProjects = $conn->query($sqlConvertedProjects);

// // Query to get the total number of enquiries with a priority assigned
// $sqlPriorityAssigned = "SELECT COUNT(*) AS totalpriorityassigned 
//                         FROM enquiry_sales 
//                         WHERE YEAR(date) = $currentYear AND priority IS NOT NULL AND priority != ''";
// $resultsPriorityAssigned = $conn->query($sqlPriorityAssigned);

// // Initialize variables to store the results
// $totalEnquiries = 0;
// $totalRejected = 0;
// $convertedProjects = 0;
// $totalPriorityAssigned = 0;

// // Fetch and store the total number of enquiries
// if ($resultsTotalEnquiries) {
//   $rowTotalEnquiries = $resultsTotalEnquiries->fetch_assoc();
//   $totalEnquiries = $rowTotalEnquiries['totalenquiries'];
//   $resultsTotalEnquiries->free();
// } else {
//   echo "Error: " . $conn->error;
// }

// // Fetch and store the total number of rejected enquiries
// if ($resultsTotalRejected) {
//   $rowTotalRejected = $resultsTotalRejected->fetch_assoc();
//   $totalRejected = $rowTotalRejected['totalrejected'];
//   $resultsTotalRejected->free();
// } else {
//   echo "Error: " . $conn->error;
// }

// // Fetch and store the total number of converted projects
// if ($resultsConvertedProjects) {
//   $rowConvertedProjects = $resultsConvertedProjects->fetch_assoc();
//   $convertedProjects = $rowConvertedProjects['convertedtoprojects'];
//   $resultsConvertedProjects->free();
// } else {
//   echo "Error: " . $conn->error;
// }

// // Fetch and store the total number of enquiries with a priority assigned
// if ($resultsPriorityAssigned) {
//   $rowPriorityAssigned = $resultsPriorityAssigned->fetch_assoc();
//   $totalPriorityAssigned = $rowPriorityAssigned['totalpriorityassigned'];
//   $resultsPriorityAssigned->free();
// } else {
//   echo "Error: " . $conn->error;
// }

// // Calculate the total number of completed enquiries and remaining enquiries
// $totalCompleted = $totalEnquiries - $totalRejected;
// $remainingEnquiries = $totalEnquiries - $convertedProjects;


?>


<head>

  <!-- Chart.js v4 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    .card-4 {
      background: radial-gradient(#62a6e2, #1E88E5)
    }

    .card-3 {
      background: radial-gradient(#d8b55c, goldenrod)
    }



    /* .card-2 {
  background: radial-gradient(#cb7001
  , #FB8C00)

  } */

    .card-2 {
      background: radial-gradient(#fba842, #FB8C00)
    }

    .card-1 {
      background: radial-gradient(#9bbe75, #7CB342)
    }



    .dashboard-card {
      padding: 20px;
      width: 305px;
      height: 180px;

      border-radius: 10px;
      box-shadow: 0px 8px 12px rgba(0, 0, 0, 0.25);
      transition: all 0.2s;
    }

    .dashboard-card:hover {
      box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.4);
      transform: scale(1.04);
    }
  </style>
</head>

<div class="d-sm-flex align-items-center justify-content-between">
  <h1 class="h3 mb-3 px-2 pt-1"> Potential Projects</h1>
</div>


<div class="d-flex justify-content-between mx-auto mb-5 " style="width: 94%">

  <div class="dashboard-card card-4 mt-2">
    <div class='d-flex justify-content-center align-middle mx-auto' style="width: 3rem; height: 3rem; vertical-align: middle; align-items:center; border: 1.5px solid white; border-radius: 50%"> <i class="fa-solid fa-people-arrows text-light " style="font-size: 1.2rem"></i>
    </div>
    <h5 class=" mt-4 text-light">Project Enquiries</h5>
    <h3 class=" mt-1 text-light"><?php echo $totalEnquiriesCount; ?></h3>



  </div>


  <div class="dashboard-card card-3 mt-2">
    <div class='d-flex justify-content-center align-middle mx-auto' style="width: 3rem; height: 3rem; vertical-align: middle; align-items:center; border: 1.5px solid white; border-radius: 50%"> <i class="fa-solid fa-comments-dollar text-light " style="font-size: 1.2rem"></i>
    </div>
    <h5 class=" mt-4 text-light">Quotations Sent</h5>
    <h3 class=" mt-1 text-light"><?php echo $totalSentAmount; ?>$</h3>



  </div>



  <div class="dashboard-card card-2 mt-2">
    <div class='d-flex justify-content-center align-middle mx-auto' style="width: 3rem; height: 3rem; vertical-align: middle; align-items:center; border: 1.5px solid white; border-radius: 50%"> <i class="fa-solid fa-strikethrough text-light " style="font-size: 1.2rem"></i>
    </div>
    <h5 class=" mt-4 mb-0 pb-0 text-light">Cancelled Quotations</h5>
    <h3 class=" mt-1 mb-0 pb-0 text-light"><?php echo $totalCancelledAmount; ?>$</h3>



  </div>

  <div class="dashboard-card card-1 mt-2">
    <div class='d-flex justify-content-center align-middle mx-auto' style="width: 3rem; height: 3rem; vertical-align: middle; align-items:center; border: 1.5px solid white; border-radius: 50%"> <i class="fa-solid fa-sack-dollar text-light " style="font-size: 1.2rem"></i>
    </div>
    <h5 class=" mt-4 mb-0 pb-0 text-light">Accepted Quotations</h5>
    <h3 class=" mt-1 mb-0 pb-0 text-light"><?php echo $totalAcceptedAmount; ?>$</h3>



  </div>


</div>

<div class="row container-fluid d-flex justify-content-center align-items-center">
  <div class="w-100 " style="height: 80vh;">
    <div class="card  shadow pb-2 ">
      <div class="card-body pb-0 pt-3 mb-0">
        <div class="row no-gutters align-items-center">
          <div class="col">
            <div class="text-xs font-weight-bold text-uppercase  px-3 pb-3">Total Enquiries and Quotations</div>
            <div class="chart-container ">
              <canvas id="enquiryChart" class="pb-3 pt-3 mx-auto" style="width: 99.4%; height: 67vh; background: #f6f6f6"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>




</div>

<?php

// dummy data, can use these variables for actual data

// Fetch data for all enquiries per month
$sqlAllEnquiries = "SELECT MONTH(quote_sent_date) AS month, COUNT(*) AS total 
                    FROM potential_project 
                    WHERE YEAR(quote_sent_date) = YEAR(CURDATE()) 
                    GROUP BY MONTH(quote_sent_date) 
                    ORDER BY MONTH(quote_sent_date)";
$resultAllEnquiries = $conn->query($sqlAllEnquiries);

$amountAllEnquiries = array_fill(0, 12, 0); // Initialize array with 12 months
if ($resultAllEnquiries) {
  while ($row = $resultAllEnquiries->fetch_assoc()) {
    $month = (int)$row['month'] - 1; // Convert month to array index (0-11)
    $amountAllEnquiries[$month] = (int)$row['total'];
  }
}

// Fetch data for sent quotations per month
$sqlSentQuotations = "SELECT MONTH(first_enquiry_date) AS month, SUM(amount) AS total 
                      FROM potential_project_sent_quotation 
                      WHERE YEAR(first_enquiry_date) = YEAR(CURDATE()) 
                      GROUP BY MONTH(first_enquiry_date) 
                      ORDER BY MONTH(first_enquiry_date)";
$resultSentQuotations = $conn->query($sqlSentQuotations);

$amountSentQuotations = array_fill(0, 12, 0); // Initialize array with 12 months
if ($resultSentQuotations) {
  while ($row = $resultSentQuotations->fetch_assoc()) {
    $month = (int)$row['month'] - 1; // Convert month to array index (0-11)
    $amountSentQuotations[$month] = (int)$row['total'];
  }
}

// Fetch data for cancelled quotations per month
// Fetch data for cancelled quotations per month
$sqlCancelledQuotations = "
SELECT MONTH(quote_sent_date) AS month, SUM(amount) AS total
FROM cancelled_quotations
WHERE YEAR(quote_sent_date) = YEAR(CURDATE()) 
  AND sales_id NOT IN (SELECT sales_id FROM accepted_quotations WHERE YEAR(accepted_date) = YEAR(CURDATE()))
GROUP BY MONTH(quote_sent_date) 
ORDER BY MONTH(quote_sent_date)";
$resultCancelledQuotations = $conn->query($sqlCancelledQuotations);

$amountTotalRejected = array_fill(0, 12, 0); // Initialize array with 12 months
if ($resultCancelledQuotations) {
  while ($row = $resultCancelledQuotations->fetch_assoc()) {
    $month = (int)$row['month'] - 1; // Convert month to array index (0-11)
    $amountTotalRejected[$month] = (int)$row['total'];
  }
}

// Fetch data for accepted quotations per month
$sqlAcceptedQuotations = "SELECT MONTH(accepted_date) AS month, SUM(amount) AS total 
                          FROM accepted_quotations 
                          WHERE YEAR(accepted_date) = YEAR(CURDATE()) 
                          GROUP BY MONTH(accepted_date) 
                          ORDER BY MONTH(accepted_date)";
$resultAcceptedQuotations = $conn->query($sqlAcceptedQuotations);

$amountAcceptedQuotations = array_fill(0, 12, 0); // Initialize array with 12 months
if ($resultAcceptedQuotations) {
  while ($row = $resultAcceptedQuotations->fetch_assoc()) {
    $month = (int)$row['month'] - 1; // Convert month to array index (0-11)
    $amountAcceptedQuotations[$month] = (int)$row['total'];
  }
}


?>

<script>
  document.addEventListener("DOMContentLoaded", function() {

    const ctx = document.getElementById('enquiryChart').getContext('2d');

    const config = {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]); ?>,
        datasets: [{
            label: 'Project Enquiries',
            data: <?php echo json_encode($amountAllEnquiries); ?>,
            backgroundColor: '#1E88E5',
            borderColor: '#f6f6f6',
            borderWidth: 2,
            hoverBackgroundColor: 'blue',
            hoverBorderColor: 'white',
            barThickness: 18
          },
          {
            label: 'Quotations Sent',
            data: <?php echo json_encode($amountSentQuotations); ?>,
            backgroundColor: '#d7b04e',
            borderColor: '#f6f6f6',
            borderWidth: 2,
            hoverBackgroundColor: 'darkgoldenrod',
            hoverBorderColor: 'white',
            barThickness: 18
          },
          {
            label: 'Quotations Cancelled',
            data: <?php echo json_encode($amountTotalRejected); ?>,
            backgroundColor: '#FB8C00',
            borderColor: '#f6f6f6',
            borderWidth: 2,
            hoverBackgroundColor: '#ff5100',
            hoverBorderColor: 'white',
            barThickness: 18
          },
          {
            label: 'Quotations Accepted',
            data: <?php echo json_encode($amountAcceptedQuotations); ?>,
            backgroundColor: '#59a059',
            borderColor: '#f6f6f6',
            borderWidth: 2,
            hoverBackgroundColor: 'green',
            hoverBorderColor: 'white',
            barThickness: 18
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
              label: function(context) {
                let datasetLabel = context.dataset.label || '';
                let value = context.raw;
                if (
                  datasetLabel === 'Quotations Sent' ||
                  datasetLabel === 'Quotations Cancelled' ||
                  datasetLabel === 'Quotations Accepted'
                ) {
                  return `${datasetLabel}: $${value}`;
                } else {
                  return `${datasetLabel}: ${value}`;
                }
              }
            }
          }
        },
        scales: {
          x: {
            display: true,
            title: {
              display: true,
              text: 'Months'
            },
            grid: {
              display: false
            }
          },
          y: {
            display: false,
            title: {
              display: true,
              text: 'Amount'
            },
            grid: {
              display: false
            }
          }
        }
      }
    };


    const enquiryChart = new Chart(ctx, config);
  });
</script>

<!-- Content Row -->
<div class="row">
  <!-- Begin Page Content -->


  <?php include './include/footer.php'; ?>
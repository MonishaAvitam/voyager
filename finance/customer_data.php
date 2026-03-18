<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: ../index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

if ($user_role == 4) {
  header('Location:./payslip.php');
}

include './include/sidebar.php';

$sql = "
    SELECT
        (SELECT COUNT(*) FROM projects) AS projects_count,
        (SELECT COUNT(*) FROM deliverable_data) AS deliverable_data_count;
";


$result = $conn->query($sql);


if ($result) {
    $row = $result->fetch_assoc();
    $projects = $row['projects_count'];
    $total_deliverable_data = $row['deliverable_data_count'];
    $result->free();
} else {
    echo "Error executing query: " . $conn->error;
}

$total_projects = $projects + $total_deliverable_data;

$sql01 = "
    SELECT
        (SELECT COUNT(*) FROM csa_finance_invoiced) AS invoiced_projects;
";


$result01 = $conn->query($sql01);


if ($result01) {
    $row = $result01->fetch_assoc();
    $invoiced_projects = $row['invoiced_projects'];
    $result01->free();
} else {
    echo "Error executing query: " . $conn->error;
}

$sql02 = "
    SELECT
        (SELECT COUNT(*) FROM csa_finance_readytobeinvoiced) AS readytobeinvoiced_projects;
";


$result02 = $conn->query($sql02);


if ($result02) {
    $row = $result02->fetch_assoc();
    $readytobeinvoiced_projects = $row['readytobeinvoiced_projects'];
    $result02->free();
} else {
    echo "Error executing query: " . $conn->error;
}

$sql03 = "
SELECT
    (
        SELECT COUNT(*) AS target_projects_count
        FROM projects
        LEFT JOIN csa_finance_invoiced ON projects.project_id = csa_finance_invoiced.project_id
        WHERE projects.setTarget = 'Active' 
        AND csa_finance_invoiced.project_id IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM csa_finance_readytobeinvoiced
            WHERE csa_finance_readytobeinvoiced.project_id = projects.project_id
        )
    ) AS project_target_count,
    (
        SELECT COUNT(*) AS target_deliverables_count
        FROM deliverable_data
        LEFT JOIN csa_finance_invoiced ON deliverable_data.project_id = csa_finance_invoiced.project_id
        WHERE deliverable_data.setTarget = 'Active' 
        AND csa_finance_invoiced.project_id IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM csa_finance_readytobeinvoiced
            WHERE csa_finance_readytobeinvoiced.project_id = deliverable_data.project_id
        )
    ) AS deliverable_target_count;
";


$result03 = $conn->query($sql03);

if ($result03) {
    $row = $result03->fetch_assoc();
    $target_projects_count = $row['project_target_count'];
    $target_deliverables_count = $row['deliverable_target_count'];
    $result03->free();
} else {
    echo "Error executing query: " . $conn->error;
}

$total_target_projects_count = $target_projects_count + $target_deliverables_count;


$sql04 = "
SELECT 
    COUNT(*) AS project_count 
FROM (
    SELECT 
        dd.project_id,
        dd.urgency,
        dd.reopen_status,
        dd.project_name,
        dd.p_team,
        dd.setTarget,
        c.customer_name 
    FROM 
        deliverable_data dd 
    LEFT JOIN 
        contacts c ON dd.contact_id = c.contact_id
    WHERE 
        dd.setTarget IS NULL
        AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id)
        AND NOT EXISTS (SELECT 1 FROM csa_finance_readyToBeInvoiced RI WHERE dd.project_id = RI.project_id)

    UNION ALL

    SELECT 
        p.project_id,
        p.urgency,
        p.reopen_status,
        p.project_name,
        p.p_team,
        p.setTarget,
        c.customer_name 
    FROM 
        projects p 
    LEFT JOIN 
        contacts c ON p.contact_id = c.contact_id
    WHERE 
        p.setTarget IS NULL
        AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id)
        AND NOT EXISTS (SELECT 1 FROM csa_finance_readyToBeInvoiced RI WHERE p.project_id = RI.project_id)
) AS project_counts;
";

$result04 = $conn->query($sql04);

if ($result04) {
    $row = $result04->fetch_assoc();
    $project_count = $row['project_count'];
    $result04->free();
} else {
    echo "Error executing query: " . $conn->error;
}

?>


<!-- dashboard content  -->

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customer Data</h1>
  </div>


</div>
<style>
  .order-card {
    color: #fff;
  }

  .bg-c-blue {
    background: linear-gradient(45deg, #4099ff, #73b4ff);
  }

  .bg-c-green {
    background: linear-gradient(45deg, #2ed8b6, #59e0c5);
  }

  .bg-c-yellow {
    background: linear-gradient(45deg, #FFB64D, #ffcb80);
  }

  .bg-c-pink {
    background: linear-gradient(45deg, #FF5370, #ff869a);
  }


  .card {
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
    box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
    border: none;
    margin-bottom: 30px;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
  }

  .card .card-block {
    padding: 25px;
  }

  .order-card i {
    font-size: 26px;
  }

  .f-left {
    float: left;
  }

  .f-right {
    float: right;
  }
</style>

<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<div class="container">
  <div class="row">
    <div class="col-md-4 col-xl-3">
      <div class="card bg-c-blue order-card">
        <div class="card-block">
          <h6 class="m-b-20">Total No. of Projects</h6>
          <h2 class="text-right"><i class="far fa-file-alt f-left"></i><span><?php echo $total_projects ?></span></h2>
        </div>
      </div>
    </div>

    <div class="col-md-4 col-xl-3">
      <div class="card bg-c-green order-card">
        <div class="card-block">
          <h6 class="m-b-20">Total No. of Customers</h6>
          <h2 class="text-right"><i class="fa fa-rocket f-left"></i><span><?php echo $total_target_projects_count ?></span></h2>
        </div>
      </div>
    </div>

    <div class="col-md-4 col-xl-3">
      <div class="card bg-c-yellow order-card">
        <div class="card-block">
          <h6 class="m-b-20">No. of Projects This Month</h6>
          <h2 class="text-right"><i class="fa fa-refresh f-left"></i><span><?php echo $readytobeinvoiced_projects ?></span></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="card_css.css">

<div class="container">
<div class="row">
    <div class="col-lg-4">
        <div class="card card-margin">
            <div class="card-header no-border">
                <h5 class="card-title">MOM</h5>
            </div>
            <div class="card-body pt-0">
                <div class="widget-49">
                    <div class="widget-49-title-wrapper">
                        <div class="widget-49-date-primary">
                            <span class="widget-49-date-day">09</span>
                            <span class="widget-49-date-month">apr</span>
                        </div>
                        <div class="widget-49-meeting-info">
                            <span class="widget-49-pro-title">PRO-08235 DeskOpe. Website</span>
                            <span class="widget-49-meeting-time">12:00 to 13.30 Hrs</span>
                        </div>
                    </div>
                    <ol class="widget-49-meeting-points">
                        <li class="widget-49-meeting-item"><span>Expand module is removed</span></li>
                        <li class="widget-49-meeting-item"><span>Data migration is in scope</span></li>
                        <li class="widget-49-meeting-item"><span>Session timeout increase to 30 minutes</span></li>
                    </ol>
                    <div class="widget-49-meeting-action">
                        <a href="#" class="btn btn-sm btn-flash-border-primary">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-margin">
            <div class="card-header no-border">
                <h5 class="card-title">MOM</h5>
            </div>
            <div class="card-body pt-0">
                <div class="widget-49">
                    <div class="widget-49-title-wrapper">
                        <div class="widget-49-date-warning">
                            <span class="widget-49-date-day">13</span>
                            <span class="widget-49-date-month">apr</span>
                        </div>
                        <div class="widget-49-meeting-info">
                            <span class="widget-49-pro-title">PRO-08235 Lexa Corp.</span>
                            <span class="widget-49-meeting-time">12:00 to 13.30 Hrs</span>
                        </div>
                    </div>
                    <ol class="widget-49-meeting-points">
                        <li class="widget-49-meeting-item"><span>Scheming module is removed</span></li>
                        <li class="widget-49-meeting-item"><span>App design contract confirmed</span></li>
                        <li class="widget-49-meeting-item"><span>Client request to send invoice</span></li>
                    </ol>
                    <div class="widget-49-meeting-action">
                        <a href="#" class="btn btn-sm btn-flash-border-warning">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>



<?php
include './include/footer.php';
?>
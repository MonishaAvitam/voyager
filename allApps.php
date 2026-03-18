<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';

// auth check

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
$user_role = $_SESSION['user_role'];

if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
}



require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

include 'include/welcomeTopBar.php';

$user_is_premium = false; // free user

if ($user_role == 2){
  header('Location: welcome.php');
}
$sql = "SELECT * FROM tbl_admin WHERE user_id = $user_id";
$info = $obj_admin->manage_all_info($sql);
$num_row = $info->rowCount();
while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

  $username = $row['username'];
  $todoAccess = $row['todoAccess'];
  $eventAccess = $row['eventAccess'];  // ✅ add this

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (isset($_POST['obi'])) {
      $_SESSION['csa_login_option'] = 'csa_obi_app';
      if (empty($row['salesAccess'])) {
        $_SESSION['error_message'] = "You do not have permission to access OBI.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
      $_SESSION['user_role'] = $row['salesAccess'];
      header('Location: ./sales/');
      exit();
    }

    if (isset($_POST['rae'])) {
      $_SESSION['csa_login_option'] = 'csa_rae_app';
      if (empty($row['raeAccess'])) {
        $_SESSION['error_message'] = "You do not have permission to access RAE.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
      $_SESSION['user_role'] = $row['raeAccess'];
      header('Location: ./welcome.php');
      exit();
    }

    if (isset($_POST['pv'])) {
      $_SESSION['csa_login_option'] = 'csa_rae_app';
      if (empty($row['raeAccess'] && $row['raeAccess'] !== 2)) {
        $_SESSION['error_message'] = "You do not have permission to access View Port.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
      $_SESSION['user_role'] = $row['raeAccess'];
      header('Location: ./project_VP/viewPort.php');
      exit();
    }

    if (isset($_POST['count'])) {
      $_SESSION['csa_login_option'] = 'csa_finance_app';
      if (empty($row['countAccess'])) {
        $_SESSION['error_message'] = "You do not have permission to access COUNT.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }
      $_SESSION['user_role'] = $row['countAccess'];
      $_SESSION['payslipAccess'] = 'Not Granted';

      header('Location: ./finance/');
      exit();
    }

    if (isset($_POST['payslip'])) {
      $_SESSION['csa_login_option'] = 'csa_finance_app';

      if (empty($row['payslipAccess'])) {
        $_SESSION['error_message'] = "You do not have permission to access Payslip.";
        header(header: 'Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }

      if (!empty($row['payslipAccess'])) {
        $_SESSION['payslipAccess'] = 'Granted';
      }

      // Debugging: Display user role instead of redirecting
      echo $_SESSION['payslipAccess'];
      header('Location: ./finance/');

      exit();
    }

    if (isset($_POST['events'])) {
      $_SESSION['csa_login_option'] = 'csa_events_app';

      if (empty($row['eventAccess']) || $row['eventAccess'] != 1) {
        $_SESSION['error_message'] = "You do not have permission to access Events.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
      }

      $_SESSION['user_role'] = $row['eventAccess'];

      // Get user_role using existing manage_all_info method
      $sqlRole = "SELECT user_role FROM tbl_admin WHERE user_id = $user_id";
      $roleInfo = $obj_admin->manage_all_info($sqlRole);
      $roleRow = $roleInfo->fetch(PDO::FETCH_ASSOC);
      $userRole = $roleRow['user_role'] ?? '';

      // Add random salt
      $salt = bin2hex(random_bytes(4));

      // Encode values as salt|value
      $uidEncoded = base64_encode($salt . "|" . $user_id);
      $unameEncoded = base64_encode($salt . "|" . $user_name);
      $roleEncoded = base64_encode($salt . "|" . $userRole);

      // Redirect to eventsRedirect.php
      header("Location: eventsRedirect.php?uid=$uidEncoded&uname=$unameEncoded&role=$roleEncoded");
      exit();
    }
  }
}



// Default case
$_SESSION['csa_login_option'] = 'allApps';



?>

<?php if (!empty($_SESSION['error_message'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var modal = new bootstrap.Modal(document.getElementById("accessDeniedModal"));
      modal.show();
    });
  </script>
  <?php unset($_SESSION['error_message']); // Clear the session message after displaying 
  ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Apps</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <style>
    .card {
      border-radius: 15px;
      transition: all 0.3s ease-in-out;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      background-color: #f8f9fa;
    }

    .card-body i  {
      color: #007bff;
    }

    .card-title {
      font-weight: bold;
    }

    .locked-card {
      opacity: 0.6;
      cursor: not-allowed;
      position: relative;
    }

    .lock-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      background: rgba(0, 0, 0, 0.6);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
    }
  </style>
</head>

<body>

  <div class="modal fade" id="accessDeniedModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel">Access Denied</h5>
          <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          You do not have permission to access this feature.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


  <div class="container py-5 ">

    <div class="col-lg-12 d-flex justify-content-between align-items-center">
      <!-- Left side -->
      <div class="header mt-4 col-lg-6">
        <h1 class="text-capitalize">Hello <?php echo htmlspecialchars($user_name); ?></h1>
        <h5>All Apps</h5>
      </div>

      <?php
      $sql = "SELECT * FROM tbl_admin WHERE user_id = $user_id";
      $info = $obj_admin->manage_all_info($sql);
      $num_row = $info->rowCount();
      while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

      ?>

        <!-- Right side -->
        <div class="header mt-4 col-lg-6 d-flex justify-content-end">
          <a href="administration.php" class="text-decoration-none text-center" style="color: inherit;">
            <img
              src="<?php echo !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic']) : './icons/user.png'; ?>"
              alt="Profile Picture" class="profile-pic mb-2"
              style="width:80px; height:80px; object-fit:cover; border-radius:50%;">
            <h5 class="mb-0">Profile</h5>
          </a>

        </div>



    </div>

  <?php } ?>

  <div class="mt-5 row g-4 justify-content-center">

    <!-- RAE -->
    <div class="col-lg-4 col-md-6 col-sm-12">
      <form method="POST">
        <button name="rae" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
          <div class="card text-center h-100">
            <div class="card-body py-4">
              <i class="bi bi-robot display-4"></i>
              <h5 class="card-title mt-3">RAE</h5>
              <p class="card-text">Project Management App</p>
            </div>
          </div>
        </button>
      </form>
    </div>

    <!-- View Port -->
  <div class="col-lg-4 col-md-6 col-sm-12">
  <form method="POST" <?php if (!$user_is_premium) echo 'onsubmit="return false;"'; ?>>
    <button name="pv" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
      <div class="card text-center h-100 position-relative">
        <div class="card-body py-4">
          <i class="bi bi-kanban display-4 <?= $user_is_premium ? '' : 'text-muted'; ?>"></i>
          <h5 class="card-title mt-3 <?= $user_is_premium ? '' : 'text-muted'; ?>">View Port</h5>
          <p class="card-text <?= $user_is_premium ? '' : 'text-muted'; ?>">Project Tracking App</p>

          <?php if (!$user_is_premium): ?>
            <div class="lock-overlay">
              <i class="fa-solid fa-lock fa-2x mb-2"></i>
              <p class="mb-0 text-danger fw-bold">Feature locked</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </button>
  </form>
</div>

    <!-- OBI -->
  <div class="col-lg-4 col-md-6 col-sm-12">
  <form method="POST" <?php if (!$user_is_premium) echo 'onsubmit="return false;"'; ?>>
    <button name="obi" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
      <div class="card text-center h-100 position-relative">
        <div class="card-body py-4">
          <i class="bi bi-bar-chart  display-4 <?= $user_is_premium ? '' : 'text-muted'; ?>"></i>
          <h5 class="card-title mt-3 <?= $user_is_premium ? '' : 'text-muted'; ?>">OBI</h5>
          <p class="card-text <?= $user_is_premium ? '' : 'text-muted'; ?>">Sales App</p>

          <?php if (!$user_is_premium): ?>
            <div class="lock-overlay">
              <i class="fa-solid fa-lock fa-2x mb-2"></i>
              <p class="mb-0 text-danger fw-bold">Feature locked</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </button>
  </form>
</div>

    <!-- Binks -->
<div class="col-lg-4 col-md-6 col-sm-12">
  <form method="POST" <?php if (!$user_is_premium) echo 'onsubmit="return false;"'; ?>>
    <button name="payslip" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
      <div class="card text-center h-100 position-relative">
        <div class="card-body py-4">
          <i class="bi bi-cash-coin display-4 <?= $user_is_premium ? '' : 'text-muted'; ?>"></i>
          <h5 class="card-title mt-3 <?= $user_is_premium ? '' : 'text-muted'; ?>">Binks</h5>
          <p class="card-text <?= $user_is_premium ? '' : 'text-muted'; ?>">Payslip App</p>

          <?php if (!$user_is_premium): ?>
            <div class="lock-overlay">
              <i class="fa-solid fa-lock fa-2x mb-2"></i>
              <p class="mb-0 text-danger fw-bold">Feature locked</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </button>
  </form>
</div>


    <!-- COUNT -->
   <div class="col-lg-4 col-md-6 col-sm-12">
  <form method="POST" <?php if (!$user_is_premium) echo 'onsubmit="return false;"'; ?>>
    <button name="count" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
      <div class="card text-center h-100 position-relative">
        <div class="card-body py-4">
          <i class="bi bi-calculator display-4 <?= $user_is_premium ? '' : 'text-muted'; ?>"></i>
          <h5 class="card-title mt-3 <?= $user_is_premium ? '' : 'text-muted'; ?>">COUNT</h5>
          <p class="card-text <?= $user_is_premium ? '' : 'text-muted'; ?>">Finance App</p>

          <?php if (!$user_is_premium): ?>
            <div class="lock-overlay">
              <i class="fa-solid fa-lock fa-2x mb-2"></i>
              <p class="mb-0 text-danger fw-bold">Feature locked</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </button>
  </form>
</div>


    <!-- Skywalker -->
   <div class="col-lg-4 col-md-6 col-sm-12">
  <form method="POST" id="loginForm" <?php if (!$user_is_premium) echo 'onsubmit="return false;"'; ?>>
    <input type="hidden" name="email" value="<?= $username ?>">
    <input type="hidden" name="password" value="<?= $todoAccess ?>">
    <button name="skywalker" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
      <div class="card text-center h-100 position-relative">
        <div class="card-body py-4">
          <i class="bi bi-list-task display-4 <?= $user_is_premium ? '' : 'text-muted'; ?>"></i>
          <h5 class="card-title mt-3 <?= $user_is_premium ? '' : 'text-muted'; ?>">Skywalker</h5>
          <p class="card-text <?= $user_is_premium ? '' : 'text-muted'; ?>">Todo App</p>

          <?php if (!$user_is_premium): ?>
            <div class="lock-overlay">
              <i class="fa-solid fa-lock fa-2x mb-2"></i>
              <p class="mb-0 text-danger fw-bold">Feature locked</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </button>
  </form>

  <?php if ($user_is_premium): ?>
  <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      this.action = 'https://todo.csaappstore.com/custom-login';
    });
  </script>
  <?php endif; ?>
</div>


   <div class="col-lg-4 col-md-6 col-sm-12">
  <form method="POST" <?php if (!$user_is_premium) echo 'onsubmit="return false;"'; ?>>
    <input type="hidden" name="email" value="<?= $username ?>">
    <input type="hidden" name="password" value="<?= $eventAccess ?>">
    <button name="events" class="btn w-100 p-0 border-0 bg-transparent" type="submit">
      <div class="card text-center h-100 position-relative">
        <div class="card-body py-4">
          <i class="fa-regular fa-calendar-days display-4 <?= $user_is_premium ? '' : 'text-muted'; ?>"></i>
          <h5 class="card-title mt-3 <?= $user_is_premium ? '' : 'text-muted'; ?>">Events</h5>
          <p class="card-text <?= $user_is_premium ? '' : 'text-muted'; ?>">Calendar Events</p>

          <?php if (!$user_is_premium): ?>
            <div class="lock-overlay">
              <i class="fa-solid fa-lock fa-2x mb-2"></i>
              <p class="mb-0 text-danger fw-bold">Feature locked</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </button>
  </form>
</div>


  </div>


  <?php


  $sql = "SELECT * FROM tbl_admin WHERE user_id = $user_id";
  $info = $obj_admin->manage_all_info($sql);
  $num_row = $info->rowCount();
  while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
  }
  ?>





  </div>
  </div>




</body>

</html>



<?php
include 'include/footer.php';
?>
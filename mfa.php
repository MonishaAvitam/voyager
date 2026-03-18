<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
  <title>MFA</title>
  <style>
    .form-control {
      width: 55px;
      height: 55px;
      text-align: center;
      font-size: 20px;
      font-weight: 600;
      border: none;
      border-bottom: 2px solid grey;
      background: #fff;
    }

    .form-control:focus {
      outline: none;
      box-shadow: none;
      border-bottom: 2px solid black;
    }

    .submit_btn {
      background-color: #070A19;
      color: white;
    }

    .submit_btn:hover {
      background-color: #0b6efd;
      color: white;
    }

    .pfp-img {
      height: 3rem;
      width: 3rem;
      border-radius: 50%;
      object-fit: cover;
    }

    .logo-img {
      height: 60px;
    }

    .otp-icon {
      height: 90px;
      width: 90px;
    }

    .otp-wrong-gif {
      width: 45px;
      height: 45px;
    }

    .disabled-link {
      pointer-events: none;
      color: grey !important;
      text-decoration: none !important;
      cursor: not-allowed;
    }
  </style>
</head>

<body>
  <?php
  ob_start();
  session_start();
  require 'classes/admin_class.php';
  $obj_admin = new Admin_Class();
  require 'conn.php';

  if (isset($_GET['logout'])) {
    $obj_admin->admin_logout();
    header("Location: index.php");
    exit();
  }

  $user_id = $_SESSION['admin_id'] ?? null;
  $user_name = $_SESSION['name'] ?? null;
  $security_key = $_SESSION['security_key'] ?? null;

  if ($user_id === null || $security_key === null) {
    header("Location: index.php");
    exit();
  }

  if ($_GET['MFA'] === 'true') {
    $sql_select = "SELECT email FROM tbl_admin WHERE user_id = ?";
  } else {
    $obj_admin->admin_logout();
    header("Location: index.php");
    exit();
  }

  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("i", $user_id);
  $stmt_select->execute();
  $stmt_select->bind_result($userMailId);
  $stmt_select->fetch();
  $stmt_select->close();

  if (isset($_SESSION['OTP'])) {
    header("Location: index.php");
    exit();
  }
  ?>

  <!-- Header -->
  <header class="bg-white shadow-sm py-2">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
      <div>
        <img src="./img/logo.png" class="logo-img" alt="Logo">
      </div>
      <div class="d-flex align-items-center">
        <div class="text-center me-3">
          <img src="https://th.bing.com/th/id/OIP.8Ofv96stYwyRFqILvTCFJQAAAA?rs=1&pid=ImgDetMain" class="pfp-img" alt="Profile">
          <div class="fw-bold small text-dark mt-1"><?php echo $user_name ?></div>
        </div>
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <button class="btn btn-danger btn-sm" type="submit" name="logout">Log out</button>
        </form>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="container py-5">
    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto text-center">
      <form method="POST" class="rounded bg-white shadow p-4">
        <h2 class="fw-bolder mb-4">Welcome <span style="color: #0b6efd;"><?php echo $user_name ?>!</span></h2>

        <img src="https://cdn-icons-png.flaticon.com/512/11812/11812806.png" class="otp-icon mb-3" alt="OTP Icon">

        <p class="text-muted">A code has been sent to your registered email</p>
        <p class="fw-bold"><?php echo $userMailId; ?></p>

        <div class="mb-3">
          <p>Enter your 6-digit security code</p>
          <div class="d-flex justify-content-center gap-2">
            <input type="text" class="form-control otp-input" name="v1" id="otp-input-1" maxlength="1">
            <input type="text" class="form-control otp-input" name="v2" id="otp-input-2" maxlength="1">
            <input type="text" class="form-control otp-input" name="v3" id="otp-input-3" maxlength="1">
            <input type="text" class="form-control otp-input" name="v4" id="otp-input-4" maxlength="1">
            <input type="text" class="form-control otp-input" name="v5" id="otp-input-5" maxlength="1">
            <input type="text" class="form-control otp-input" name="v6" id="otp-input-6" maxlength="1">
          </div>
        </div>

        <button class="btn btn-lg submit_btn my-4 w-100" name="otpCheck">Submit</button>

        <div class="text-muted">
          Didn’t get the code?
          <span id="resendLink" onclick="resendOTP()" class="text-primary fw-bold text-decoration-underline ms-2" role="button">Resend</span>
          <span id="timer" class="ms-2 text-secondary"></span>
        </div>
      </form>
    </div>
  </main>

  <!-- Wrong OTP Modal -->
  <div class="modal fade" id="wrong-otp-popup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width:300px">
      <div class="modal-content">
        <div class="modal-body text-center">
          <img src="https://media.giphy.com/media/3og0IvGtnDyPHCRaYU/giphy.gif" class="otp-wrong-gif" />
          <p class="fs-5 fw-bold">Incorrect</p>
          <p>The OTP you have entered is either incorrect or expired.</p>
          <button type="button" class="btn btn-danger mt-2" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'conn.php';
    if ($_GET['MFA'] === "true") {
      $sql = "SELECT OTP, OTP_Timestamp FROM tbl_admin WHERE user_id = $user_id";
    }
    $info = $obj_admin->manage_all_info($sql);
    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
      $generatedOTP = $row['OTP'];
      $otpTimestamp = $row['OTP_Timestamp'];
    }

    $expiry_time = 300;
    $current_time = time();
    $otp_time = strtotime($otpTimestamp);

    if ($current_time - $otp_time > $expiry_time) {
      echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
          var modal = new bootstrap.Modal(document.getElementById('wrong-otp-popup'));
          modal.show();
        });
      </script>";
      exit();
    }

    if (isset($_POST['otpCheck'])) {
      $otp_value = $_POST['v1'] . $_POST['v2'] . $_POST['v3'] . $_POST['v4'] . $_POST['v5'] . $_POST['v6'];

      if ($otp_value == $generatedOTP) {
        $_SESSION['OTP'] = $otp_value;
        if ($_GET['MFA'] === "true") {
          $update_sql = "UPDATE tbl_admin SET OTP = NULL WHERE user_id = $user_id";
        }
        $conn->query($update_sql);
        header('Location: ./allApps.php');
        exit();
      } else {
        echo "<script>
          document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('wrong-otp-popup'));
            modal.show();
          });
        </script>";
      }
    }
  }
  ?>

  <script>
    // Resend OTP popup + timer
    let resendCooldown = false;

    function resendOTP() {
      if (resendCooldown) return;

      Swal.fire({
        icon: 'info',
        title: 'OTP Resent!',
        text: 'A new OTP has been sent to your registered email.',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0b6efd'
      });

      // Disable resend link for 60s
      resendCooldown = true;
      const resendLink = document.getElementById("resendLink");
      const timer = document.getElementById("timer");
      resendLink.classList.add("disabled-link");

      let timeLeft = 60;
      timer.textContent = `(00:${timeLeft})`;

      const countdown = setInterval(() => {
        timeLeft--;
        timer.textContent = `(00:${timeLeft < 10 ? '0' + timeLeft : timeLeft})`;

        if (timeLeft <= 0) {
          clearInterval(countdown);
          resendCooldown = false;
          timer.textContent = "";
          resendLink.classList.remove("disabled-link");
        }
      }, 1000);

      // Trigger actual resend request
      var params = new URLSearchParams(window.location.search);
      var MFAParam = params.get("MFA");
      if (MFAParam === "true") {
        fetch("./mfaMail.php?MFA=true");
      }
    }

    // OTP input behavior
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
      input.addEventListener('input', () => {
        if (input.value.length === 1 && index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      });
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value === '' && index > 0) {
          inputs[index - 1].focus();
        }
      });
      input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text').trim();
        for (let i = 0; i < inputs.length && i < pasteData.length; i++) {
          inputs[i].value = pasteData[i];
        }
      });
    });
  </script>

</body>

</html>

<?php include './include/footer.php'; ?>

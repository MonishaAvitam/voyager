<?php
require 'authentication.php'; // admin authentication check 

// auth check
if (isset($_SESSION['admin_id'])) {
  $user_id = $_SESSION['admin_id'];
  $user_name = $_SESSION['name'];
  $security_key = $_SESSION['security_key'];
}

if (isset($_POST['change_password_btn'])) {
  $info = $obj_admin->change_password_for_employee($_POST);
}

$page_name = "Login";

include("include/login_header.php");


?>

<style>


  .glass {
    /* From https://css.glass */
    /* background: rgba(25, 28, 32, 0.68);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(3.9px);
    -webkit-backdrop-filter: blur(3.9px);
    border: 1px solid rgba(25, 28, 32, 0.3); */
    display: flex;
    width: 100%;
    height: 100vh;
    align-items: center;
    justify-content: center;
  }
</style>

<body class="" style="background-image: url('./img/1567666.png') ; background-repeat:no-repeat; background-size:cover;">

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-4 offset-md-3 d-flex  glass">
        <div class="well" style="width: 100%;">
          <form class="form-horizontal form-custom-login" action="" method="POST">
            <div class="form-heading" style="background: orange;">
              <h2 class="text-center">Please Change your password</h2>
            </div>
            <?php if (isset($info)) { ?>
              <h5 class="alert alert-danger"><?php echo $info; ?></h5>
            <?php } ?>

            <div class="form-group">
              <input type="hidden" class="form-control" name="user_id" value="<?php echo $user_id; ?>" required />
              <input type="password" class="form-control" placeholder="Password" name="password" required />
            </div>
            <div class="form-group">
              <input type="password" class="form-control" placeholder="Retype Password" name="re_password" required />
            </div>
            <button type="submit" name="change_password_btn" class="btn btn-default pull-right" style="color: #fff; border-color:#fff;">Change Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>


  <?php

  include("include/footer.php");

  ?>
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


$admin_id = $_GET['admin_id'];
if (isset($_POST['btn_admin_password'])) {
  $error = $obj_admin->admin_password_change($_POST, $admin_id);
}





?>

<script>
  function validate(admin_new_password, admin_cnew_password) {
    var a = document.getElementById(admin_new_password).value;
    var b = document.getElementById(admin_cnew_password).value;
    if (a != b) {
      alert("Passwords do not match");

    }
    return false;
  }
</script>

<div class="container-fluid text-white">
  <div class="row">
    <div class="col-md-12">
      <div class="well well-custom">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="manage-admin-tab" href="manage-admin.php">Manage Admin</a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link" id="manage-employee-tab" href="admin-manage-user.php">Manage Employee</a>
          </li>
        </ul>
        <div class="gap"></div>
        <div class="row mt-2">
          <div class="col-md-10 col-md-offset-1">
            <div class="well">
            <h3 class="text-center bg-primary " style="padding: 7px;">Admin - Change Password</h3><br>



              <div class="row">
                <div class="col-md-8 col-md-offset-2">

                  <?php

                  if (isset($error)) {
                  ?>
                    <div class="alert alert-danger">
                      <strong>Oopps!!</strong> <?php echo $error; ?>
                    </div>
                  <?php

                  }
                  ?>


                  <form class="form-horizontal" role="form" action="" method="post" autocomplete="off">
                    <div class="form-group">
                      <label class="control-label col-sm-4">Old Password</label>
                      <div class="col-sm-8">
                        <input type="password" placeholder="Enter Old Password" name="admin_old_password" id="admin_old_password" list="expense" class="form-control input-custom" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-4">New Password</label>
                      <div class="col-sm-8">
                        <input type="password" placeholder="Enter New Password" name="admin_new_password" id="admin_new_password" class="form-control input-custom" min="8" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-4">Confirm New Password</label>
                      <div class="col-sm-8">
                        <input type="password" placeholder="Confirm New Password" name="admin_cnew_password" id="admin_cnew_password" list="expense" min="8" class="form-control input-custom" required>
                      </div>
                    </div>

                    <div class="form-group">
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-4">
                      <a href="javascript:history.back()" class="btn btn-danger-custom">Cancel</a>
                        <button type="submit" name="btn_admin_password" class="btn btn-success-custom">Change</button>

                      </div>
                    
                    </div>
                  </form>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<?php

include("include/footer.php");

?>
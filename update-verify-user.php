
<?php

require 'authentication.php'; // admin authentication check 
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
if ($user_role != 1) {
  header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
}

$admin_id = $_GET['admin_id'];

if(isset($_POST['update_current_employee'])){

    $obj_admin->update_user_data($_POST,$admin_id);
}

if(isset($_POST['btn_user_password'])){

    $obj_admin->update_user_password($_POST,$admin_id);
}



$sql = "SELECT * FROM tbl_admin WHERE user_id='$admin_id' ";
$info = $obj_admin->manage_all_info($sql);
$row = $info->fetch(PDO::FETCH_ASSOC);
        
$page_name="Admin";
include("include/sidebar.php");
?>

<div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="well well-custom">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link " id="manage-admin-tab" href="manage-admin.php">Manage Admin</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active " id="manage-admin-tab" href="verify-users.php">Manage Verify Users</a>
              </li>
              <li class="nav-item">
                <a class="nav-link " id="manage-employee-tab" href="admin-manage-user.php">Manage Employee</a>

              </li>
              <li class="nav-item ">
                <a class="nav-link" data-toggle="modal" data-target="#myModal">Add New Employee</a>
              </li>
            </ul>
          <div class="gap"></div>

          <div class="row">
            <div class="col-md-10 col-md-offset-1">
              <div class="well">
                <h3 class="text-center bg-primary" style="padding: 7px;">Edit Employee</h3><br>


                      <div class="row">
                        <div class="col-md-7">
                          <form class="form-horizontal" role="form" action="" method="post" autocomplete="off">
                            <div class="form-group">
                              <label class="control-label col-sm-2">Fullname</label>
                              <div class="col-sm-8">
                                <input type="text" value="<?php echo $row['fullname']; ?>" placeholder="Enter Employee Name" name="em_fullname" list="expense" class="form-control input-custom" id="default" required>
                              </div>
                            </div>
                            
                            <div class="form-group">
                              <label class="control-label col-sm-2">Username</label>
                              <div class="col-sm-8">
                                <input type="text" value="<?php echo $row['username']; ?>" placeholder="Enter Employee Username" name="em_username" class="form-control input-custom" required>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="control-label col-sm-2">Email</label>
                              <div class="col-sm-8">
                                <input type="email" value="<?php echo $row['email']; ?>" placeholder="Enter employee email" name="em_email" class="form-control input-custom" required>
                              </div>
                            </div>
                      
                            <div class="form-group">
                            </div>
                            <div class="form-group">
                              <div class="col-sm-offset-4 col-sm-3">
                                <button type="submit" name="update_current_employee" class="btn btn-success-custom">Update Now</button>
                              </div>
                            </div>
                          </form> 
                        </div>
                        <div class="col-md-5">
                          <button id="emlpoyee_pass_btn" class="btn btn-primary">Change Password</button>
                          <form action="" method="POST" id="employee_pass_cng">
                            <div class="form-group">
                              <label for="admin_password">New Password:</label>
                              <input type="password" name="employee_password" class="form-control input-custom" id="employee_password" min="8" required>
                            </div>
                            <div class="form-group">
                              <button type="submit" name="btn_user_password" class="btn btn-success">Ok</button>

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

<script type="text/javascript">

$('#emlpoyee_pass_btn').click(function(){
    $('#employee_pass_cng').toggle('slow');
});

</script>
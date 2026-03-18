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

$page_name = "Admin";
include("include/sidebar.php");

?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="well">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link " id="manage-admin-tab" href="manage-admin.php">Manage Admin</a>
          </li>
          <li class="nav-item">
                <a class="nav-link active " id="manage-admin-tab" href="verify-users.php">Manage Verify Users</a>
              </li>
          <li class="nav-item">
            <a class="nav-link" id="manage-employee-tab" href="admin-manage-user.php">Manage Employee</a>
          </li>
        </ul>


        <div class="gap"></div>
        <div class="table-responsive">
          <table class="table table-codensed table-custom">
            <thead>
              <tr>
                <th>Serial No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody>

              <?php
              $sql = "SELECT * FROM tbl_admin WHERE user_role = 2";
              $info = $obj_admin->manage_all_info($sql);

              $serial  = 1;
              $total_expense = 0.00;
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
              ?>
                <tr>
                  <td><?php echo $serial;
                      $serial++; ?></td>
                  <td><?php echo $row['fullname']; ?></td>
                  <td><?php echo $row['email']; ?></td>
                  <td><?php echo $row['username']; ?></td>

                  <td><a title="Update Admin" href="update-admin.php?admin_id=<?php echo $row['user_id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                      </svg></a>&nbsp;&nbsp;</td>
                </tr>

              <?php  } ?>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>


</div>




<?php
if (isset($_SESSION['update_user_pass'])) {

  echo '<script>alert("Password updated successfully");</script>';
  unset($_SESSION['update_user_pass']);
}
include("include/footer.php");

?>
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
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';

//Remove ADMIN ACCOUNT

if (isset($_GET['delete_user'])) {
  $user_id = $_GET['delete_user'];



  // Check if the user with the given user_id exists in tbl_admin
  $check_sql = "SELECT COUNT(*) FROM tbl_admin WHERE user_id = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("i", $user_id);
  $check_stmt->execute();
  $check_stmt->bind_result($existing_count);
  $check_stmt->fetch();
  $check_stmt->close();

  if ($existing_count > 0) {

    $update_sql = "UPDATE tbl_admin SET user_role = 2 WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    if ($update_stmt->execute()) {
      $msg_success = "Admin  Removed Successfully";
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
    } else {
      echo "Error updating user role: " . $conn->error;
    }

    $update_stmt->close();
  } else {
    echo "User with user_id $user_id does not exist in tbl_admin.";
  }
}


?>
<?php include 'include/sidebar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="well">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="manage-admin-tab" href="manage-admin.php">Manage Admin</a>
          </li>
         
          <li class="nav-item">
            <a class="nav-link" id="manage-employee-tab" href="admin-manage-user.php">Manage Engineers</a>
          </li>
        </ul>


        <div class="gap"></div>
        <div class="table-responsive mt-5">
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
              $sql = "SELECT * FROM tbl_admin WHERE user_role = 1";
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
                      </svg></a>&nbsp;&nbsp;
                        <a title="Remove" href="?delete_user=<?php echo $row['user_id']; ?>" onclick="return confirm('Are you sure you want to Remove this user?');">

                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                          </svg>
                        </a></td>
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
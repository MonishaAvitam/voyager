<?php

require '../authentication.php'; // admin authentication check 
require '../conn.php';

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


if (isset($_GET['delete_user'])) {
  $action_id = $_GET['admin_id'];

  $task_sql = "DELETE FROM task_info WHERE t_user_id = $action_id";
  $delete_task = $obj_admin->db->prepare($task_sql);
  $delete_task->execute();

  $attendance_sql = "DELETE FROM attendance_info WHERE atn_user_id = $action_id";
  $delete_attendance = $obj_admin->db->prepare($attendance_sql);
  $delete_attendance->execute();

  $sql = "DELETE FROM tbl_admin WHERE user_id = :id";
  $sent_po = "admin-manage-user.php";
  $obj_admin->delete_data_by_this_method($sql, $action_id, $sent_po);
}




if (isset($_POST['add_new_employee'])) {
  $error = $obj_admin->add_new_user($_POST);
}

if (isset($_POST['add_new_team'])) {
  $p_team = $_POST['p_team'];
  $assign_to = $_POST['p_manager'];
  list($user_id, $fullname, $username, $raeAccess, $email) = explode('|', $assign_to);

  // Check if the user with the given user_id exists in tbl_admin
  $check_sql = "SELECT COUNT(*) FROM tbl_admin WHERE user_id = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("i", $user_id);
  $check_stmt->execute();
  $check_stmt->bind_result($existing_count);
  $check_stmt->fetch();
  $check_stmt->close();

  if ($existing_count > 0) {
    // User with user_id already exists in tbl_admin, update raeAccess to 3
    $update_sql = "UPDATE tbl_admin SET p_team = ?, raeAccess = 3 WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $p_team, $user_id);

    if ($update_stmt->execute()) {
      $msg_success = "Project Manager Added successfully";
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



<!--modal for employee add-->
<!-- Modal -->
<div class="container-fluid">
  <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Add Employee Info</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 ">
              <div class="form-container">
                <?php if (isset($error)) { ?>
                  <h5 class="alert alert-danger"><?php echo $error; ?></h5>
                <?php } ?>
                <form role="form" action="" method="post" autocomplete="off">
                  <div class="form-horizontal">

                    <div class="form-group">
                      <label class="control-label col-sm-4">Fullname</label>
                      <div class="col">
                        <input type="text" placeholder="Enter Employee Name" name="em_fullname" list="expense" class="form-control input-custom" id="default" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-4">Username</label>
                      <div class="col">
                        <input type="text" placeholder="Enter Employee username" name="em_username" class="form-control input-custom" required>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-4">Email</label>
                      <div class="col">
                        <input type="email" placeholder="Enter Employee Email" name="em_email" class="form-control input-custom" required>
                      </div>
                    </div>



                    <div class="form-group">
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col">
                        <button type="submit" name="add_new_employee" class="btn btn-success-custom">Add Employee</button>
                      </div>
                      <div class="col-sm-3">
                        <button type="submit" class="btn btn-danger-custom" data-dismiss="modal">Cancel</button>
                      </div>
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


  <!--modal for Team add-->


  <div class="modal fade" id="p_team" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Add New Team </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 ">
              <div class="form-container">
                <?php if (isset($error)) { ?>
                  <h5 class="alert alert-danger"><?php echo $error; ?></h5>
                <?php } ?>
                <form role="form" action="" method="post" autocomplete="off">
                  <div class="form-horizontal">

                    <div class="form-group">
                      <label class="control-label col-sm-4">Team Name</label>
                        <input type="text" placeholder="Enter Team Name" name="p_team" list="expense" class="form-control input-custom" id="default" required>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-sm-4">Project Manager</label>
                      <?php
                      $sql = "SELECT user_id, fullname, username, raeAccess, email FROM tbl_admin WHERE raeAccess = 2";
                      $info = $obj_admin->manage_all_info($sql);
                      ?>
                      <select class="form-control" name="p_manager" id="p_manager" required>
                        <option value="">Select Employee...</option>

                        <?php
                        // Assume you have fetched a list of users with user_id, fullname, username, raeAccess, and email from your database
                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                          $user_info = $row['user_id'] . '|' . $row['fullname'] . '|' . $row['username'] . '|' . $row['raeAccess'] . '|' . $row['email'];
                          echo '<option value="' . $user_info . '">' . $row['fullname'] . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-3 col">
                        <button type="submit" name="add_new_team" class="btn btn-success-custom">Add team</button>
                      </div>
                      <div class="col-sm-3">
                        <button type="submit" class="btn btn-danger-custom" data-dismiss="modal">Cancel</button>
                      </div>
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


  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="row">

          <div class="well well-custom">
            <?php if (isset($error)) { ?>
              <script type="text/javascript">
                $('#myModal').modal('show');
              </script>
            <?php } ?>
            <?php if ($user_role == 1) { ?>
              <!-- <div class="btn-group">
              <button class="btn btn-success btn-menu" data-toggle="modal" data-target="#myModal">Add New Employee</button>
            </div> -->
            <?php } ?>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link " id="manage-admin-tab" href="manage-admin.php">Manage Admin</a>
              </li>

              <li class="nav-item">
                <a class="nav-link " id="manage-admin-tab" href="manage-project-manager.php">Manage Project Manager</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" id="manage-employee-tab" href="admin-manage-user.php">Manage Engineers</a>

              </li>
              <li class="nav-item ">
                <a class="nav-link" data-toggle="modal" data-target="#myModal">Add New Employee</a>
              </li>
              <li class="nav-item ">
                <a class="nav-link" data-toggle="modal" data-target="#p_team">Add Team</a>
              </li>
            </ul>
            <div class="gap"></div>
            <div class="table-responsive mt-5">
              <table class="table table-codensed table-custom">
                <thead>
                  <tr>
                    <th>Serial No.</th>
                    <th>Fullname</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Team</th>
                    <th>Temp Password</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>

                  <?php
                  $sql = "SELECT * FROM tbl_admin WHERE raeAccess = 2 ORDER BY user_id DESC";
                  $info = $obj_admin->manage_all_info($sql);
                  $serial  = 1;
                  $num_row = $info->rowCount();
                  if ($num_row == 0) {
                    echo '<tr><td colspan="7">No Data found</td></tr>';
                  }
                  while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                    <tr>
                      <td><?php echo $serial;
                          $serial++; ?></td>
                      <td><?php echo $row['fullname']; ?></td>
                      <td><?php echo $row['email']; ?></td>
                      <td><?php echo $row['username']; ?></td>
                      <td><?php echo $row['p_team']; ?></td>
                      <td><?php echo $row['temp_password']; ?></td>

                      <td><a title="Update Employee" href="update-employee.php?admin_id=<?php echo $row['user_id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                          </svg></a>&nbsp;&nbsp;
                        <a title="Delete" href="?delete_user=delete_user&admin_id=<?php echo $row['user_id']; ?>" onclick=" return check_delete();"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                          </svg></a>
                      </td>
                    </tr>

                  <?php  } ?>



                </tbody>
              </table>
            </div>
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
  include 'add_project.php';
  include("include/footer.php");

  ?>
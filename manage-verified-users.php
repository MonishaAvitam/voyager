<?php
include 'conn.php';

require 'authentication.php'; // admin authentication check 
include("include/login_header.php");
include 'add_project.php';

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
include 'include/sidebar.php';



if (isset($_GET['delete_user'])) {
  $user_id = $_GET['delete_user']; // Get the user_id from the URL

  // Create a DELETE query to remove the user from the project_managers table
  $delete_sql = "DELETE FROM verified_users WHERE user_id = ?";
  
  // Prepare the DELETE statement
  $delete_stmt = mysqli_prepare($conn, $delete_sql);
  
  if ($delete_stmt === false) {
      die("Error: " . mysqli_error($conn));
  }
  
  // Bind the user_id parameter
  mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
  
  // Execute the DELETE statement
  if (mysqli_stmt_execute($delete_stmt)) {
      // Deletion successful
  
  } else {
      // Deletion failed
      echo "Error deleting user: " . mysqli_error($conn);
  }
  
  // Close the prepared statement
  mysqli_stmt_close($delete_stmt);
}








if (isset($_POST['add_verified_user'])) {
  $assign_to = $_POST['assign_to'];
  list($user_id, $fullname, $username, $user_role, $email) = explode('|', $assign_to);

  $check_sql = "SELECT COUNT(*) FROM verified_users WHERE user_id = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("i", $user_id);
  $check_stmt->execute();
  $check_stmt->bind_result($existing_count);
  $check_stmt->fetch();

  $check_stmt->close();

  if ($existing_count > 0) {
    echo "Verified user  with user_id $user_id already exists.";
  } else {
    $insert_sql = "INSERT INTO verified_users (user_id, fullname, username, user_role, email) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("issss", $user_id, $fullname, $username, $user_role, $email);

    if ($insert_stmt->execute()) {
      // echo "Project manager added successfully";
    } else {
      echo "Error adding project manager: " . $conn->error;
    }

    $insert_stmt->close();
  }
}




// Close the database connection (if needed)
$conn->close();
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


  <!--modal for employee add-->



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
                <a class="nav-link active " id="manage-admin-tab" href="manage-verified-users.php">Manage Verify Users</a>
              </li>
              <li class="nav-item">
                <a class="nav-link " id="manage-admin-tab" href="manage-project-manager.php">Manage Project Manager</a>
              </li>
              <li class="nav-item">
                <a class="nav-link " id="manage-employee-tab" href="admin-manage-user.php">Manage Engineers</a>

              </li>
              <li class="nav-item ">
                <a class="nav-link" data-toggle="modal" data-target="#myModal">Add New Employee</a>
              </li>
            </ul>




            <div class="gap"></div>

            <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#add_manager">
              ADD  Verified User
            </button>
            <div class="table-responsive mt-3">
              <table class="table table-condensed table-custom">
                <thead>
                  <tr>
                    <th>Serial No.</th>
                    <th>Fullname</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Details</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Assuming you have a MySQLi database connection established
                  include 'conn.php';

                  // Select project managers from project_managers table and join with tbl_admin to get additional information
                  $sql = "SELECT ta.fullname, ta.email, ta.username, ta.user_id
        FROM tbl_admin ta
        INNER JOIN verified_users pm ON ta.user_id = pm.user_id
        WHERE ta.user_role = 2;";

                  $result = mysqli_query($conn, $sql);

                  if (!$result) {
                    die("Error: " . mysqli_error($conn));
                  }

                  $serial = 1;

                  if (mysqli_num_rows($result) == 0) {
                    echo '<tr><td colspan="5">No Data found</td></tr>';
                  }

                  while ($row = mysqli_fetch_assoc($result)) {
                  ?>
                    <tr>
                      <td><?php echo $serial;
                          $serial++; ?></td>
                      <td><?php echo $row['fullname']; ?></td>
                      <td><?php echo $row['email']; ?></td>
                      <td><?php echo $row['username']; ?></td>

                      <td>
                        <a title="Delete" href="?delete_user=<?php echo $row['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                          </svg>
                        </a>
                      </td>
                    </tr>
                  <?php
                  }

                  // Close the MySQLi connection when done
                  mysqli_close($conn);
                  ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>


  <div class="modal" id="add_manager">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">ADD Verified User</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <form action="" method="post">
            <div class="form-group">
              <label for="assign_to">Select Verified User</label>
              <?php
              $sql = "SELECT user_id, fullname, username, user_role, email FROM tbl_admin WHERE user_role = 2";
              $info = $obj_admin->manage_all_info($sql);
              ?>
              <select class="form-control" name="assign_to" id="assign_to" required>
                <option value="">Select Employee...</option>

                <?php
                // Assume you have fetched a list of users with user_id, fullname, username, user_role, and email from your database
                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                  $user_info = $row['user_id'] . '|' . $row['fullname'] . '|' . $row['username'] . '|' . $row['user_role'] . '|' . $row['email'];
                  echo '<option value="' . $user_info . '">' . $row['fullname'] . '</option>';
                }
                ?>
              </select>
            </div>

            <!-- Add hidden input fields to store additional information -->
            <input type="hidden" name="user_id" id="user_id">
            <input type="hidden" name="username" id="username">
            <input type="hidden" name="user_role" id="user_role">
            <input type="hidden" name="email" id="email">

            <button type="submit" name="add_verified_user" class="btn btn-primary">Add</button>
          </form>

        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
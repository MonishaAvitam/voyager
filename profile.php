<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
}

$user_role = $_SESSION['user_role'];

include 'include/sidebar.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';

?>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const navbar = document.querySelector(".navbar");

    if (navbar) {
      navbar.classList.remove("mb-4");
    }
  });
</script>

<head>
  <style>
    .cover-photo {
      width: 100%;
      height: 250px;
      background: url('./5123261.jpg') center/cover;

    }

    .profile-pic {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 5px solid white;
      margin-top: -75px;
    }

    .profile-pic-edit {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 5px solid white;
    }
  </style>
</head>



<?php

// SQL query to retrieve project details based on project_id

$sql = "SELECT * FROM tbl_admin WHERE user_id = $user_id
";

$info = $obj_admin->manage_all_info($sql);

$serial  = 1;

$num_row = $info->rowCount();

while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

?>



  <div class="">
    <div class="cover-photo"></div>
    <div class="text-center">
      <img src="<?= $row['profile_picture'] ?>" alt="Profile" class="profile-pic">
      <h3 class="mt-2"> <?php echo $row['fullname']; ?></h3>
      <h3 class="mt-2"> <?php echo $row['email']; ?></h3>

      <p>
        <?php echo $row['tagline'];  ?>
      </p>
    </div>

    <div class="container-fluid vh-100">



      <div class="mt-3">
        <div class="d-flex justify-content-end mb-5 ">


          <!-- Button trigger modal -->
          <div>
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editProfileModal">
              <i class="fa fa-edit" aria-hidden="true"></i>&nbsp; Edit Profile
            </button>
            <button class="btn btn-dark btn-sm" data-toggle="modal" data-target="#changePasswordModal">
              <i class="fa fa-gear" aria-hidden="true"></i>&nbsp; Change Password
            </button>
          </div>

          <script>
            $('#editProfileModal').modal({
              backdrop: 'static',
              keyboard: false
            });
          </script>
          <!-- Edit Profile Modal -->
          <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form method="POST">

                  <div class="modal-body">
                    <div class="form-group text-center">


                      <img src="./passportSize.JPG" alt="Profile" class="profile-pic-edit">
                    </div>


                    <div class="form-group mt-4">
                      <input type="file" name="profileImg" class="form-control" id="new-profile">
                    </div>

                    <div class="form-group mt-4">
                      <label for="fullname" class="col-form-label">Fullname</label>
                      <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Enter Fullname">
                    </div>
                    <div class="form-group">
                      <label for="email" class="col-form-label">Email address</label>
                      <input type="email" name="email" class="form-control" id="email" placeholder="Enter email">
                    </div>
                    <div class="form-group">
                      <label for="tagline" class="col-form-label">Tagline</label>
                      <textarea class="form-control" name="tagline" id="tagline" rows="1"></textarea>
                    </div>
                    <div class="form-group">
                      <label for="about" class="col-form-label">About</label>
                      <textarea class="form-control" name="about" id="about" rows="3"></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                  </div>
                </form>

              </div>
            </div>
          </div>
          <!-- Change Password Modal -->
          <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form method="POST">
                  <div class="modal-body">

                    <div class="form-group">
                      <label for="newpassword">New Password</label>
                      <input type="password" class="form-control" name="password" id="newpassword" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                      <label for="confirmpassword">Confirm Password</label>
                      <input type="password" class="form-control" name="confirmPassword" id="confirmpassword" placeholder="Confirm new password">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="changePassword" class="btn btn-primary">Save changes</button>
                  </div>
                </form>

              </div>
            </div>
          </div>

        </div>
        <div class="card border-0">

          <div class="card-body">
            <h2>
              <span>About</span>
            </h2>
            <p>
              <?php echo $row['about'];  ?>
            </p>
          </div>
          <div class="card-body mt-1 d-flex justify-content-end">

            <p>
              -- RAE User Id : <?php echo $row['user_id'];  ?> --
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>


<?php  } ?>

<?php

if (isset($_POST['update'])) {
  // Ensure user is logged in
  if (!$user_id) {
      echo "<script>
              Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: 'User not logged in!',
              });
            </script>";
      exit;
  }

  // Retrieve and sanitize form data
  $fullname = htmlspecialchars(filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING));
  $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
  $tagline = htmlspecialchars(filter_input(INPUT_POST, 'tagline', FILTER_SANITIZE_STRING));
  $about = htmlspecialchars(filter_input(INPUT_POST, 'about', FILTER_SANITIZE_STRING));

  if (!$email) {
      echo "<script>
              Swal.fire({
                  icon: 'error',
                  title: 'Invalid Email!',
                  text: 'Please enter a valid email address.',
              });
            </script>";
      exit;
  }

  // Upload directory
  $uploadDir = "ProfileUploads/";
  $imagePath = null;

  // Ensure directory exists
  if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
      echo "<script>
              Swal.fire({
                  icon: 'error',
                  title: 'Upload Failed!',
                  text: 'Could not create upload directory!',
              });
            </script>";
      exit;
  }

  // Handle image upload if a file is selected
  if (isset($_FILES['profileImg']) && !empty($_FILES['profileImg']['tmp_name'])) {
      if ($_FILES['profileImg']['error'] !== UPLOAD_ERR_OK) {
          echo "<script>
                  Swal.fire({
                      icon: 'error',
                      title: 'Upload Failed!',
                      text: 'Error: " . htmlspecialchars($_FILES['profileImg']['error']) . "',
                  });
                </script>";
          exit;
      }

      // Get file extension and sanitize
      $imageFileType = strtolower(pathinfo($_FILES['profileImg']['name'], PATHINFO_EXTENSION));
      $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

      if (!in_array($imageFileType, $allowedTypes)) {
          echo "<script>
                  Swal.fire({
                      icon: 'error',
                      title: 'Invalid File Type!',
                      text: 'Only JPG, JPEG, PNG, and GIF files are allowed.',
                  });
                </script>";
          exit;
      }

      // Rename file uniquely using user ID
      $imageName = "user_" . $user_id . "." . $imageFileType;
      $imagePath = $uploadDir . $imageName;

      // Move the uploaded file
      if (!move_uploaded_file($_FILES['profileImg']['tmp_name'], $imagePath)) {
          echo "<script>
                  Swal.fire({
                      icon: 'error',
                      title: 'Upload Failed!',
                      text: 'Error moving the file!',
                  });
                </script>";
          exit;
      }
  }

  // Ensure DB connection is valid
  if (!$conn) {
      echo "<script>
              Swal.fire({
                  icon: 'error',
                  title: 'Database Error!',
                  text: 'Database connection failed!',
              });
            </script>";
      exit;
  }

  // Prepare SQL statement dynamically
  if ($imagePath) {
      $sql = "UPDATE tbl_admin SET fullname = ?, email = ?, tagline = ?, about = ?, profile_picture = ? WHERE user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sssssi", $fullname, $email, $tagline, $about, $imagePath, $user_id);
  } else {
      $sql = "UPDATE tbl_admin SET fullname = ?, email = ?, tagline = ?, about = ? WHERE user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssi", $fullname, $email, $tagline, $about, $user_id);
  }

  // Execute and handle response
  if ($stmt->execute()) {
      $_SESSION['name'] = $fullname; // Update session name

      echo "<script>
            setTimeout(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated!',
                    text: 'Your profile has been updated successfully.',
                }).then(() => {
                    window.location.href = '{$_SERVER['HTTP_REFERER']}';
                });
            }, 100);
        </script>";
  } else {
      echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed!',
                text: 'Something went wrong. Please try again.',
            });
        </script>";
  }

  // Close statement
  $stmt->close();
}










if (isset($_POST['changePassword'])) {
  // Include database connection (if not already included)
  include 'db_connection.php';

  // Retrieve form data
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirmPassword'];

  // Check if passwords match
  if ($password === $confirmPassword) {
    // Password validation (optional but recommended)
    if (strlen($password) < 6) {
      $msg_error = "Password must be at least 6 characters long.";
    } else {
      // Hash the password before storing
      $hashedPassword = md5($password);

      // Prepare SQL statement
      $sql = "UPDATE tbl_admin SET password = ? WHERE user_id = ?";

      // Prepare statement
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("si", $hashedPassword, $user_id);

      // Execute and handle response
      if ($stmt->execute()) {
        // Password updated successfully (Success Alert)
        echo "<script>
                    setTimeout(function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Password updated successfully!',
                        }).then(() => {
                            window.location.href = '{$_SERVER['HTTP_REFERER']}';
                        });
                    }, 100);
                </script>";
      } else {
        // Error in executing query (Error Alert)
        echo "<script>
                    setTimeout(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong. Try again later.',
                        });
                    }, 100);
                </script>";
      }

      // Close statement and connection
      $stmt->close();
      $conn->close();
    }
  } else {
    // Passwords do not match (Error Alert)
    echo "<script>
            setTimeout(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Password and Confirm Password do not match!',
                });
            }, 100);
        </script>";
  }
}
?>

<!-- Include SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>








<?php
include 'include/footer.php';
?>
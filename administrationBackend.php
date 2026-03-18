  <?php


    // Handle profile update
if (isset($_POST['update_profile'])) {
    $fullname     = $_POST['fullname'] ?? '';
    $email        = $_POST['email'] ?? '';
    $password     = $_POST['password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $profilePicPath = null;

    // ✅ If password field is not empty, hash with MD5
if (!empty($password)) {
    // Check if already MD5 (32 hex characters)
    if (preg_match('/^[a-f0-9]{32}$/', $password)) {
        $hashedPassword = $password; // Already hashed, keep it
    } else {
        $hashedPassword = md5($password); // New plain password, hash it
    }
} else {
    // ✅ Keep old password if not changed
    $sql_pass = "SELECT password FROM tbl_admin WHERE user_id = ?";
    $stmt_pass = $conn->prepare($sql_pass);
    $stmt_pass->bind_param("i", $current_user_id);
    $stmt_pass->execute();
    $stmt_pass->bind_result($existingPassword);
    $stmt_pass->fetch();
    $stmt_pass->close();
    $hashedPassword = $existingPassword;
}


    // ✅ Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $targetFilePath = $targetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        if (in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
                $profilePicPath = $targetFilePath;
            }
        }
    }

    // ✅ Update DB
    if ($profilePicPath) {
        $sql = "UPDATE tbl_admin 
                SET fullname=?, email=?, password=?, phone_number=?, profile_pic=? 
                WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $fullname, $email, $hashedPassword, $phone_number, $profilePicPath, $current_user_id);
    } else {
        $sql = "UPDATE tbl_admin 
                SET fullname=?, email=?, password=?, phone_number=? 
                WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $fullname, $email, $hashedPassword, $phone_number, $current_user_id);
    }

    if ($stmt->execute()) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
              Profile updated successfully!
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        echo '<script>setTimeout(function() { window.location.href = "administration.php"; }, 1000);</script>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
              Error updating profile: ' . $stmt->error . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }

    $stmt->close();
}



    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_id"])) {

        $userId = $_POST['user_id'];
        $userName = $_POST['fullname'];
       // $password = md5($_POST['password']);
        $email = $_POST['email'];


        $team = $_POST['team'];
        $userRole = $_POST['user_role'];

        $raeAccess = $_POST['rae_access'] ? 1 : null;
        $countAccess = $_POST['count_access'] ? 5 : null;
        $salesAccess = $_POST['sales_access'] ? 1 : null;
        $payslipAccess = $_POST['payslip_access'] ? 4 : null;


        $todoAccess = $_POST['todo_access'] ? 'Granted' : null;



        $sql = "UPDATE tbl_admin  SET  fullname = ?, email = ?,  p_team = ?, user_role =? , raeAccess = ?, countAccess = ?, salesAccess = ?, payslipAccess = ?, todoAccess = ?  WHERE user_id = ?";


        $stmt1 = $conn->prepare($sql);

        $stmt1->bind_param("sssiiiiisi", $userName, $email,  $team, $userRole, $userRole, $countAccess, $salesAccess, $payslipAccess, $todoAccess, $userId);

        $stmt1->execute();

        $stmt1->close();


        header('Location: administration.php');
        exit();
    }

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

            $update_sql = "UPDATE tbl_admin SET account_status = 0 WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user_id);
            if ($update_stmt->execute()) {
                $msg_success = " Account Suspended Successfully";
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


  if (isset($_GET['toggle_user']) && isset($_GET['status'])) {
    $user_id = intval($_GET['toggle_user']);
    $status = intval($_GET['status']); // 0 = suspend, 1 = activate

    // Check if user exists
    $check_sql = "SELECT COUNT(*) FROM tbl_admin WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($existing_count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($existing_count > 0) {
        $update_sql = "UPDATE tbl_admin SET account_status = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $status, $user_id);

        if ($update_stmt->execute()) {
            if ($status === 1) {
                $msg_success = "Account reactivated successfully.";
            } else {
                $msg_success = "Account suspended successfully.";
            }

            // Redirect back with message
            header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($msg_success));
            exit();
        } else {
            echo "Error updating account status: " . $conn->error;
        }

        $update_stmt->close();
    } else {
        echo "User with user_id $user_id does not exist in tbl_admin.";
    }
}



    ?>
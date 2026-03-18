

   <!-- Topbar -->

   <nav class="navbar navbar-expand navbar-light  topbar mb-4 static-top shadow" style="background-color: #191B25;">

<ul class="navbar-nav ml-auto">

  <!-- Sidebar Toggle (Topbar) -->
  <form class="form-inline">
    <a id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" name="sidebarToggleTop">
      <i class="fa fa-bars"></i>
    </a>

  </form>

  <!-- Topbar Navbar -->
  <ul class="navbar-nav ml-auto">
    <!-- Nav Item - Notifications -->
    <li class="nav-item dropdown no-arrow mx-1">
      <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <!-- Counter - Notifications -->
        <span class="badge badge-danger badge-counter">
          <?php
          // Include your notifications fetching code here

          $sql = "SELECT COUNT(*) as count FROM notifications WHERE sent_to = ?";
          $stmt = $conn->prepare($sql);

          if ($stmt) {
            // Bind the user_id parameter
            $stmt->bind_param("i", $user_id);

            // Execute the query
            $stmt->execute();

            // Bind the result to a variable
            $stmt->bind_result($count);

            // Fetch the result
            $stmt->fetch();

            // Close the statement
            $stmt->close();

            // Display the count
            echo $count;
          } else {
            // Handle error if necessary
            echo "0";
          }

          // Close the database connection
          ?>
        </span>
      </a>
      <!-- Modal -->

      <!-- Dropdown - Notifications -->
      <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
        <h6 class="dropdown-header">
          Notifications Center
        </h6>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


          // Check if the form was submitted
          if (isset($_POST['allow'])) {
            // Handle the form submission here
            // Get the message ID (assuming it's named msg_id) from the POST data
            $msg_id = isset($_POST['msg_id']) ? $_POST['msg_id'] : null;

            if ($msg_id !== null) {
              // Include your database connection script
              include 'conn.php';

              // Prepare and execute the SQL query to update file_access
              $sql = "UPDATE tbl_admin SET file_access = 1 WHERE user_id = ?";
              $stmt = $conn->prepare($sql);

              if ($stmt) {
                // Bind the user_id parameter
                $stmt->bind_param("i", $msg_id);

                // Execute the query
                if ($stmt->execute()) {
                  // Successfully updated file_access, now delete from notifications
                  $deleteSql = "DELETE FROM notifications WHERE user_id = ?";
                  $deleteStmt = $conn->prepare($deleteSql);

                  if ($deleteStmt) {
                    // Bind the user_id parameter for deletion
                    $deleteStmt->bind_param("i", $msg_id);

                    // Execute the deletion query
                    if ($deleteStmt->execute()) {
                      // Redirect to index.php after successful deletion
                      header('location: index.php');
                      exit;
                    } else {
                      echo 'Failed to delete notification.';
                    }

                    // Close the delete statement
                    $deleteStmt->close();
                  } else {
                    echo 'Error preparing delete SQL statement: ' . $conn->error;
                  }
                } else {
                  echo 'Failed to update file access.';
                }

                // Close the update statement
                $stmt->close();
              } else {
                echo 'Error preparing SQL statement: ' . $conn->error;
              }

              // Close the database connection
              $conn->close();
            } else {
              echo 'Message ID is missing or invalid.';
            }
          }


          // if (isset($_POST['delete_notification'])) {
          //   $deleteSql = "DELETE FROM notifications WHERE user_id = ?";
          //   $deleteStmt = $conn->prepare($deleteSql);

          //   if ($deleteStmt) {
          //     // Bind the user_id parameter for deletion
          //     $deleteStmt->bind_param("i", $msg_id);

          //     // Execute the deletion query
          //     if ($deleteStmt->execute()) {
          //       // Redirect to index.php after successful deletion
          //       header('location: index.php');
          //       exit;
          //     } else {
          //       echo 'Failed to delete notification.';
          //     }
          //   }
          // }
        }



        // Fetch notifications
        $sql = "SELECT n.*, a.fullname
  FROM notifications n
  LEFT JOIN tbl_admin a ON n.user_id = a.user_id
  WHERE n.sent_to = ?
  ORDER BY n.created_at DESC
  LIMIT 5";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
          // Bind the user_id parameter
          $stmt->bind_param("i", $user_id);

          // Execute the query
          $stmt->execute();

          // Bind the result to a variable
          $result = $stmt->get_result();

          // Iterate through notifications and display them
          while ($row = $result->fetch_assoc()) {
            echo '<form method="post" action="">'; // Add the form element here
            echo  '<a class="dropdown-item d-flex align-items-center" href="#">';
            echo  '<div class="mr-3">';
            echo   ' <div class="icon-circle preview-icon bg-dark rounded-circle">';
            echo      ' <i class="fas fa-file-alt text-danger"></i>';
            echo  '</div>';
        ?><input type="hidden" name="msg_id" value="<?php echo $row['user_id']; ?>">
        <?php
            echo '</div>';
            echo   ' <div>';
            echo    '<div class="small text-gray-500">' . $row['created_at'] . ' </div>';
            echo      '<span class="font-weight-bold">' . $row['msg_subject'] . '&nbsp' . $row['fullname'] . '&nbsp' . $row['msg_content'] . ' </span><br><br>';
            if ($row['msg_type'] == 'Requst') {
              echo '<button class="btn text-white" style="background-color:#6c7293;"; type="submit" name="allow">Allow</button>&nbsp;&nbsp;&nbsp;&nbsp;
              <button class="btn btn-dark" type="submit" name="delete_notification">Reject</button>';
            }
            echo  ' </div>';
            echo  '</a>';
            echo '</form>'; // Close the form element
          }

          // Close the statement
          $stmt->close();
        } else {
          // Handle error if necessary
          echo '<a class="dropdown-item d-flex align-items-center" href="#">No notifications found</a>';
        }

        ?>


      </div>
      <!-- notification action -->

      <!-- <div class="modal fade" id="notification_action" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content m-5">
        <button class="btn btn-secondary " type="button" data-dismiss="modal">Decline</button>
            <button class="btn btn-primary mt-2">Allow</button> 
        
        </div>
      </div>
    </div> -->
    </li>






    <!-- Nav Item - Messages -->
    <!-- <li class="nav-item dropdown no-arrow mx-1">
      <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-envelope fa-fw"></i>
        <span class="badge badge-danger badge-counter">7</span>
      </a>
      <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
        <h6 class="dropdown-header">
          Message Center
        </h6>
        <a class="dropdown-item d-flex align-items-center" href="#">
          <div class="dropdown-list-image mr-3">
            <img class="rounded-circle" src="img/undraw_profile_1.svg" alt="...">
            <div class="status-indicator bg-success"></div>
          </div>
          <div class="font-weight-bold">
            <div class="text-truncate">Hi there! I am wondering if you can help me with a
              problem I've been having.</div>
            <div class="small text-gray-500">Emily Fowler · 58m</div>
          </div>
        </a>
        <a class="dropdown-item d-flex align-items-center" href="#">
          <div class="dropdown-list-image mr-3">
            <img class="rounded-circle" src="img/undraw_profile_2.svg" alt="...">
            <div class="status-indicator"></div>
          </div>
          <div>
            <div class="text-truncate">I have the photos that you ordered last month, how
              would you like them sent to you?</div>
            <div class="small text-gray-500">Jae Chun · 1d</div>
          </div>
        </a>
        <a class="dropdown-item d-flex align-items-center" href="#">
          <div class="dropdown-list-image mr-3">
            <img class="rounded-circle" src="img/undraw_profile_3.svg" alt="...">
            <div class="status-indicator bg-warning"></div>
          </div>
          <div>
            <div class="text-truncate">Last month's report looks great, I am very happy with
              the progress so far, keep up the good work!</div>
            <div class="small text-gray-500">Morgan Alvarez · 2d</div>
          </div>
        </a>
        <a class="dropdown-item d-flex align-items-center" href="#">
          <div class="dropdown-list-image mr-3">
            <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60" alt="...">
            <div class="status-indicator bg-success"></div>
          </div>
          <div>
            <div class="text-truncate">Am I a good boy? The reason I ask is because someone
              told me that people say this to all dogs, even if they aren't good...</div>
            <div class="small text-gray-500">Chicken the Dog · 2w</div>
          </div>
        </a>
        <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
      </div>
    </li> -->





    <div class="topbar-divider d-none d-sm-block"></div>


    <!-- Nav Item - User Information -->

    <li class="nav-item dropdown no-arrow">
      <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user_name; ?></span>
        <img class="img-profile rounded-circle" src="../../img/undraw_profile.svg">
      </a>

      <!-- Dropdown - User Information -->
      <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

        <a class="dropdown-item" href="#">
          <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
          Profile
        </a>

        <a class="dropdown-item" href="#">
          <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
          Settings
        </a>
        <a class="dropdown-item" href="time-sheet.php">
          <i class="fas fa-list fa-sm fa-fw mr-2 text-green"></i>
          Time sheet
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
          <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
          Logout
        </a>


      </div>
    </li>

  </ul>

</nav>
<link rel="stylesheet" href="./css/darkmodeSwitch.css">

<div id="wrapper">

  <!-- Sidebar -->

  <ul class="navbar-nav  sidebar  accordion shadow" id="accordionSidebar">

    <!-- Sidebar - Brand -->

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
      <div class="sidebar-brand-icon">
        <img src="./img/logo.png" alt="logo" class="logo-img img-fluid" style="max-width: 35px;">
      </div>
      <div class="sidebar-brand-text mx-3">Voyager</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <!-- Nav Item - Dashboard -->

    <li class="nav-item">
      <a class="nav-link" href="welcome.php">
        <i class="fa-solid fa-house-chimney" style="color: goldenrod"></i>
        <span>Go to Dashboard</span></a>
    </li>
    



      <li class="nav-item">
        <!-- <a class="nav-link collapsed" data-toggle="modal" data-target="#add_project">


          <i class="fa-solid fa-circle-plus text-success"></i>
          <span>Add Project</span>

        </a> -->
        <a class="nav-link collapsed" data-toggle="modal" data-target="#yard_project">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Quick Project</span>

        </a>


      </li>

    <li class="nav-item">
      <a class="nav-link" href="openprojects.php">
        <i class="fas fa-fw fa-folder-open" style="color: #4caf50;"></i>
        <span>Open Projects</span></a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
    <!-- Heading -->
    <div class="sidebar-heading">
      MAIN
    </div>
    <!-- Nav Item - Pages Collapse Menu -->
    <?php

    if ($user_role == 3) {

      ?>

      <li class="nav-item">
        <a class="nav-link collapsed" data-toggle="modal" data-target="#add_project">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Add Project</span>

        </a>



      </li>



      <li class="nav-item">
        <a class="nav-link collapsed" data-toggle="modal" data-target="#add_subproject">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Add Sub-Project</span>

        </a>


      </li>



      <li class="nav-item">
        <a class="nav-link collapsed" data-toggle="modal" data-target="#add_revisionproject">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Add Revision Project</span>

        </a>


      </li>


      <li class="nav-item">

        <div id="project" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">

          <div class="bg-white py-2 collapse-inner rounded mt-3">

            <h6 class="collapse-header">DATA</h6>



            <?php if ($user_role == 3 or $user_role == 1) { ?>

              <a class="collapse-item" href="openprojects.php">All Open Projects</a>
              <a class="collapse-item" href="myprojects.php">My Open/Closed Projects</a>


            <?php } ?>



            <!-- <a class="collapse-item" href="subproject.php">Sub Projects</a> -->

            <a class="collapse-item" href="all-projects.php">All Open/Closed Projects</a>
            <a class="collapse-item" href="closed_projects.php">All Closed Projects</a>



          </div>

        </div>

      </li>










    <?php } ?>

    <?php if ($user_role == 1) { ?>
      <li class="nav-item">
        <!-- <a class="nav-link collapsed" data-toggle="modal" data-target="#add_project">


          <i class="fa-solid fa-circle-plus text-success"></i>
          <span>Add Project</span>

        </a> -->
        <a class="nav-link collapsed" data-toggle="modal" data-target="#add_project">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Add Project</span>

        </a>


      </li>




      <li class="nav-item">
        <a class="nav-link collapsed" data-toggle="modal" data-target="#add_subproject">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Add Sub-Project</span>

        </a>


      </li>



      <li class="nav-item">
        <a class="nav-link collapsed" data-toggle="modal" data-target="#add_revisionproject">


          <i class="fa-solid fa-circle-plus text-success"></i>

          <span>Add Revision Project</span>

        </a>


      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="contacts.php">


      <i class="fa-solid fa-user-tie text-secondary"></i>

          <span>Manage Customer</span>

        </a>


      </li>









      <li class="nav-item">








      </li>

      <li class="nav-item">


        <a class="nav-link collapsed" href="closed_projects.php">

          <i class="fas fa-business-time text-primary"></i>

          <span>Closed Projects</span>

        </a>


      </li>

      <?php

    } else if ($user_role == 2) {



      ?>

        <li class="nav-item">

          <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">

            <i class="fas fa-fw fa-wrench text-primary"></i>

            <span>Projects</span>

          </a>

          <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">

            <div class="bg-white py-2 collapse-inner rounded mt-3">

              <h6 class="collapse-header">DATA</h6>

              <a class="collapse-item" href="myprojects.php">My Projects</a>

            </div>
           

          </div>

        </li>

        





      <?php



    }



    ?>




    <!-- Divider -->

    <hr class="sidebar-divider d-none d-md-block">



    <!-- Sidebar Toggler (Sidebar) -->

    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Simulate a click when the page loads
        document.getElementById('sidebarToggle').click();
      });
    </script>




  </ul>



  <!-- End of Sidebar -->



  <!-- Content Wrapper -->

  <div id="content-wrapper" class="d-flex flex-column">



    <!-- Main Content -->



    <!-- Topbar -->

    <nav class="navbar navbar-expand   topbar  mb-4 static-top shadow">

      <ul class="navbar-nav ml-auto">

        <!-- Sidebar Toggle (Topbar) -->
        <form class="form-inline">
          <a id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" name="sidebarToggleTop">
            <i class="fa fa-bars"></i>
          </a>

        </form>

        <!-- Topbar Navbar -->
        <ul class="navbar-nav ml-auto">

          <div class="theme-switch d-flex align-items-center mt-3">

            <label class="bb8-toggle" id="themeSwitchBtn">
              <input class="bb8-toggle__checkbox" id="themeState" type="checkbox">

              <div class="bb8-toggle__container">
                <div class="bb8-toggle__scenery">
                  <div class="bb8-toggle__star"></div>
                  <div class="bb8-toggle__star"></div>
                  <div class="bb8-toggle__star"></div>
                  <div class="bb8-toggle__star"></div>
                  <div class="bb8-toggle__star"></div>
                  <div class="bb8-toggle__star"></div>
                  <div class="bb8-toggle__star"></div>
                  <div class="tatto-1"></div>
                  <div class="tatto-2"></div>
                  <div class="gomrassen"></div>
                  <div class="hermes"></div>
                  <div class="chenini"></div>
                  <div class="bb8-toggle__cloud"></div>
                  <div class="bb8-toggle__cloud"></div>
                  <div class="bb8-toggle__cloud"></div>
                </div>
                <div class="bb8">
                  <div class="bb8__head-container">
                    <div class="bb8__antenna"></div>
                    <div class="bb8__antenna"></div>
                    <div class="bb8__head"></div>
                  </div>
                  <div class="bb8__body"></div>
                </div>
                <div class="artificial__hidden">
                  <div class="bb8__shadow"></div>
                </div>
              </div>
            </label>
            <script>
              document.addEventListener('DOMContentLoaded', () => {
                const themeStateBtn = document.getElementById('themeState');

                // Check the stored theme preference on page load
                const storedTheme = sessionStorage.getItem('theme') || 'light-mode';
                if (storedTheme === 'light-mode') {
                  document.body.classList.remove('dark-mode');
                  document.body.classList.add('light-mode');
                } else {
                  document.body.classList.add('dark-mode');
                  document.body.classList.remove('light-mode');
                  themeStateBtn.checked = true;
                }

                // Add event listener for theme toggle
                themeStateBtn.addEventListener('change', (e) => {
                  if (e.target.checked) {
                    // Apply dark theme
                    sessionStorage.setItem('theme', 'dark-mode');
                    document.body.classList.add('dark-mode');
                    document.body.classList.remove('light-mode');
                  } else {
                    // Apply light theme
                    sessionStorage.setItem('theme', 'light-mode');
                    document.body.classList.add('light-mode');
                    document.body.classList.remove('dark-mode');
                  }
                });

                console.log(themeStateBtn);
              });
            </script>
          </div>

          <div class="topbar-divider d-none d-sm-block"></div>

          <!-- Nav Item - User Information -->

          <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle " id="userDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              <span class="mr-2 d-none d-lg-inline small  "><?php echo $user_name; ?></span>
              <?php

              include './conn.php';
              $sql = "SELECT * FROM tbl_admin WHERE user_id = $user_id";

              $info = $obj_admin->manage_all_info($sql);
              $serial = 1;
              $num_row = $info->rowCount();
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                if ($user_id !== null) {
                  // Fetch the current profile picture from the database
                  $sql = "SELECT profile_pic FROM tbl_admin WHERE user_id = ?";
                  $stmt = $conn->prepare($sql);
                  if ($stmt) {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($profilePicturePath);
                    $stmt->fetch();
                    $stmt->close();
                  } else {
                    echo "Error preparing statement: " . $conn->error;
                  }
                } else {
                  echo "User ID is not set. Please log in.";
                }
                ?>
                <?php
                if ($profilePicturePath != NULL) {
                  ?>
                  <img src="<?php echo $profilePicturePath; ?>" alt="Profile Picture" class="img-profile rounded-circle">
                  <?php
                }
                ?>
                <?php
              }
              ?>
              <?php
              if ($profilePicturePath == NULL) {
                ?>
                <img class="img-profile rounded-circle" src="./img/user.png">
                <?php
              }
              ?>
            </a>

            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

              <a class="dropdown-item" href="administration.php">
                <i class="fas fa-user fa-sm fa-fw mr-2 text-green"></i>
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
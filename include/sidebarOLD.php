<div id="wrapper">

  <!-- Sidebar -->

  <ul class="navbar-nav  sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">

      <div class="sidebar-brand-icon rotate-n-15">

        <i class="fa fa-cog"></i>
      </div>&nbsp;&nbsp;
      <!-- <div class="sidebar-brand-text mx-3">CSA Engineering </div> -->
      <img class="sidebar-brand-text mx-3" src="https://www.csaengineering.com.au/wp-content/uploads/2022/10/White-Logo.png" alt="logo" style="max-width: 100px; height: auto;">
    </a>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
      <a class="nav-link" href="index.php">
        <i class="fas fa-fw fa-tachometer-alt" style="color: #843DCA;"></i>
        <span>Dashboard</span></a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
    <!-- Heading -->
    <div class="sidebar-heading">
      MAIN
    </div>
    <!-- Nav Item - Pages Collapse Menu -->
    <?php

    if ($user_role == 1 or $user_role == 3) {

    ?>

      <li class="nav-item">

        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#project" aria-expanded="true" aria-controls="collapseTwo">

          <i class="fas fa-fw fa-cog text-danger"></i>

          <span>Projects</span>

        </a>





        <div id="project" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">

          <div class="bg-white py-2 collapse-inner rounded mt-3">

            <h6 class="collapse-header">DATA</h6>

            <?php if ($user_role==3 or $user_role==1 ){ ?>
            
            <a class="collapse-item" href="myprojects.php">My Projects</a>

            <?php  }  ?>


            <a class="collapse-item" data-toggle="modal" data-target="#add_project">New Projects</a>
            <a class="collapse-item" data-toggle="modal" data-target="#add_subproject">Add Sub-Project</a>
            <!-- <a class="collapse-item" href="subproject.php">Sub Projects</a> -->

            <a class="collapse-item" href="all-projects.php">ALL Projects</a>



          </div>

        </div>

      </li>





      <li class="nav-item">

        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#Deliverable" aria-expanded="true" aria-controls="collapseTwo">

          <i class="fas fa-fw fa-share text-warning"></i>

          <span>Deliverable</span>

        </a>

        <div id="Deliverable" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">

          <div class="bg-white py-2 collapse-inner rounded mt-3">

            <h6 class="collapse-header">DATA</h6>

            <a class="collapse-item" href="develirables_data.php">Deliverable</a>





          </div>

        </div>

      </li>

    <?php  } ?> <?php if ($user_role == 1) { ?>

     

      <li class="nav-item">

        <a class="nav-link collapsed" href="time-sheet.php">

          <i class="fas fa-fw fa-calendar-week text-success"></i>

          <span>Time Booking</span>

        </a>



      </li>
      <li class="nav-item">

        <a class="nav-link collapsed" href="contacts.php">

          <i class="fas fa-fw fa-address-card " style="color: #FB5C5E;"></i>

          <span>Contacts</span>

        </a>



      </li>

      <li class="nav-item">

        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">

          <i class="fas fa-fw fa-wrench text-info"></i>

          <span>Administration</span>

        </a>

        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">

          <div class="bg-white py-2 collapse-inner rounded mt-3">

            <h6 class="collapse-header">Custom Utilities:</h6>

            <a class="collapse-item" href="manage-admin.php">Manage Admin</a>

            <a class="collapse-item" href="admin-manage-user.php">Manage Employee</a>



          </div>

        </div>

      </li>



    <?php

                } else if ($user_role == 2) {



    ?>

      <li class="nav-item">

        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">

          <i class="fas fa-fw fa-wrench text-primary"></i>

          <span>Projects</span>

        </a>

        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">

          <div class="bg-white py-2 collapse-inner rounded mt-3">

            <h6 class="collapse-header">DATA</h6>

            <a class="collapse-item" href="myprojects.php">My Projects</a>

            <a class="collapse-item" href="all-projects.php">All Projects</a>



          </div>

        </div>

      </li>

      <!-- <li class="nav-item">

        <a class="nav-link collapsed" href="subproject.php">

          <i class="fas fa-fw fa-calendar-week text-success"></i>

          <span>Sub Projects</span>

        </a>



      </li> -->
      <li class="nav-item">

        <a class="nav-link collapsed" href="time-sheet.php">

          <i class="fas fa-fw fa-calendar-week text-success"></i>

          <span>Time Booking</span>

        </a>



      </li>



    <?php



                }



    ?>



    <!-- Nav Item - Utilities Collapse Menu -->





    <!-- Divider -->



    <!-- Divider -->

    <hr class="sidebar-divider d-none d-md-block">



    <!-- Sidebar Toggler (Sidebar) -->

    <div class="text-center d-none d-md-inline">

      <button class="rounded-circle border-0" id="sidebarToggle"></button>

    </div>



  </ul>

  <!-- End of Sidebar -->



  <!-- Content Wrapper -->

  <div id="content-wrapper" class="d-flex flex-column">



    <!-- Main Content -->



    <!-- Topbar -->

    <nav class="navbar navbar-expand navbar-light  topbar mb-4 static-top shadow" style="background-color: #070A19;">

      <ul class="navbar-nav ml-auto">

        <!-- Sidebar Toggle (Topbar) -->
        <form class="form-inline">
          <a id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" name="sidebarToggleTop">
            <i class="fa fa-bars"></i>
          </a>

        </form>

        <!-- Topbar Navbar -->
        <ul class="navbar-nav ml-auto">
        
          <div class="topbar-divider d-none d-sm-block"></div>


          <!-- Nav Item - User Information -->

          <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user_name; ?></span>
              <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
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
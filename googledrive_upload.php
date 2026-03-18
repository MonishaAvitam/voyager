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
include 'include/sidebar.php';




// Make sure the captured data exists

if (isset($_FILES['files']) && !empty($_FILES['files'])) {
  // Validate and sanitize project_id from the URL



}


if (isset($_SESSION['status_success'])) {


  // Unset the success message in the session
  unset($_SESSION['status_success']);
}

?>

<script>
  $(document).ready(function() {
    $("#all_data").modal('show');
  });

  function togglemodals() {
    $(document).ready(function() {
      $("#all_data").modal('hide');
    });
    $(document).ready(function() {
      $("#newFolderModal").modal('show');
    });
  }

  function togglemodalsback() {
    $(document).ready(function() {
      $("#all_data").modal('show');
    });
    $(document).ready(function() {
      $("#newFolderModal").modal('hide');
    });

  }
</script>


<style>
  #progress-container {
    display: none;
    margin-top: 20px;
  }

  #progress-bar {
    width: 0%;
    height: 20px;
    background-color: #4CAF50;
  }
</style>
<div class="modal fade" id="all_data" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Data</h5>

      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-container">
              <?php
              $project_id =  isset($_GET['project_id']) ? htmlspecialchars($_GET['project_id']) : '';
              // SQL query to retrieve project details based on project_id

              $sql = "SELECT * FROM projects WHERE project_id = '$project_id' ";

              $info = $obj_admin->manage_all_info($sql);

              $serial  = 1;

              $num_row = $info->rowCount();

              if ($num_row == 0) {

                echo '<tr><td colspan="7">No projects were found</td></tr>';
              }

              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                $project_name =  $row['project_name'];
                $contact_id = $row['contact_id'];
                // echo $contact_id;
              }
              $sql = "SELECT * FROM contacts WHERE contact_id = '$contact_id' ";

              $info = $obj_admin->manage_all_info($sql);

              $serial  = 1;

              $num_row = $info->rowCount();

              if ($num_row == 0) {

                echo '<tr><td colspan="7">No projects were found</td></tr>';
              }

              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                $customer_id_name =  $row['customer_id'];
                $customer_id =  substr($customer_id_name, 0, 3);
              }

              ?>

              <style>
                input[type="file"] {
                  display: none;
                }
              </style>
              <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
              <script>
                function loading() {
                  Swal.fire({
                    title: "Uploading",
                    width: 600,
                    padding: "3em",
                    customClass: {
                      popup: 'custom-popup-class',
                      backdrop: 'custom-backdrop-class'
                    },
                    showConfirmButton: false,
                    background: "#fff url(./img/trees.png)",
                    backdrop: `
    rgba(0,0,123,0.4)
    url("./img/nyan-cat.gif")
    left top
    no-repeat
  `,
                    html: "<span style='color: #716add;'>Please Wait..</span>",
                    allowOutsideClick: false // Prevent closing when clicking outside

                  });

                }
              </script>
              <div class="d-flex flex-column  justify-content-center text-justify align-items-center">
                <button class="btn btn-primary" onclick="window.open('https://drive.google.com/drive/u/0/folders/0AMBoRd5BFG7XUk9PVA', '_blank')">
                  Upload to Industrial Team Shared Drive
                </button>

                <button class="btn btn-info mt-3" onclick="window.open('https://drive.google.com/drive/u/0/folders/0AMBoRd5BFG7XUk9PVA', '_blank')">
                  Upload to Industrial Team Shared Drive
                </button>
                <label for="notes" class="text-warning mt-2"><span class="text-danger">Important:</span> For file uploads, please use the 'Upload to Shared Drive' option, as other methods are currently unavailable. Only authorized personnel are permitted to upload files. If you do not have permission, please contact your manager via email to request assistance. </label>
              </div>


            </div>
          </div>
        </div>
      </div>



      <div class="modal-footer">
        <!-- <button class="btn btn-secondary" type="button" data-dismiss="modal" onclick="window.location.href='index.php'">Done</button> -->

        <button class="btn btn-secondary" type="button" data-dismiss="modal" onclick="window.location.href='index.php'">Back</button>
      </div>
    </div>
  </div>
</div>

<!-- New Folder Modal -->
<div class="modal fade" id="newFolderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New Folder </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="./google-drive/create_new_folder_drive.php" method="POST">
          <label for="" class="form-label mt-2">Folder Name</label>
          <input name="new_folder_name" type="text" class="form-control" placeholder="Folder Name">

          <label for="">Location</label>

          <?php

          if (empty($projectFolderIds)) {
            echo 'No project folders found.';
          } else {
            echo '<select class="form-control" name="project_child_folder_id" required>';
            echo '<option value="" selected disabled>Select a Folder</option>';

            foreach ($projectFolderIds as $projectFolderId) {
              try {
                $folder = $service->files->get($projectFolderId);
                $folderName = $folder->getName();
                $folderId = $folder->getId(); // Fix: Remove the space

                // Display the main project folder
                echo "<option value='$folderId'>$folderName . ( Main Folder )</option>";



                // Display subfolders within the main project folder
                $subfolders = getSubfoldersAndFiles($service, $projectFolderId, $projectFolderId);
                displaySubfolders($subfolders);
              } catch (Google_Service_Exception $e) {
                // Handle API errors
                echo 'Error: ' . $e->getMessage();
              } catch (Google_Exception $e) {
                // Handle other errors
                echo 'Error: ' . $e->getMessage();
              }
            }

            echo '</select>';
          }


          ?>

      </div>
      <div class="modal-footer">
        <button type="button" onclick="togglemodalsback()" class="btn btn-close">Cancel</button>
        <button type="submit" name="submit" class="btn btn-primary">Create</button>
        </form>
      </div>
    </div>
  </div>
</div>



<?php include 'include/footer.php'  ?>
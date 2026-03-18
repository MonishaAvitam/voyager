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
              $table_id =  isset($_GET['table_id']) ? htmlspecialchars($_GET['table_id']) : '';
              // SQL query to retrieve project details based on project_id

              $sql = "SELECT * FROM subprojects WHERE table_id = '$table_id' ";

              $info = $obj_admin->manage_all_info($sql);

              $serial  = 1;

              $num_row = $info->rowCount();

              if ($num_row == 0) {

                echo '<tr><td colspan="7">No projects were found</td></tr>';
              }

              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                $project_name =  $row['subproject_name'];
                $contact_id = $row['contact_id'];
                $project_id = $row['project_id'];
                $subproject_number = $row['subproject_status'];
                // echo $contact_id;
              }


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
              }

              // Fetching customer_id 
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

                <button class="btn btn-primary " onclick="location.href='https://drive.google.com/drive/u/0/folders/0AMBoRd5BFG7XUk9PVA'">Upload to Industrial Team Shared Drive </button>
                <button class="btn btn-primary mt-2" onclick="location.href='https://drive.google.com/drive/u/0/folders/0AE4knKSOSdZOUk9PVA'">Upload to Building Team Shared Drive </button>
                <label for="notes" class="text-warning mt-2"><span class="text-danger">Important:</span> For file uploads, please use the 'Upload to Shared Drive' option, as other methods are currently unavailable. Only authorized personnel are permitted to upload files. If you do not have permission, please contact your manager via email to request assistance. </label>
              </div>

              <form action="./google-drive/upload.php" method="post" enctype="multipart/form-data">
                <input type="text" name="projectId" id="projectId" value="<?php echo $project_id ?>" hidden>
                <input type="text" name="customer_id" id="customer_id" value="<?php echo $customer_id; ?>" hidden>
                <input type="text" name="subproject_number" id="subproject_number" value="<?php echo $subproject_number; ?>" hidden>

                <!-- File input for multiple file selection -->
                <label for="filesMultiple" class="btn btn-info">Choose Files</label>
                <input class="form-control mt-2 custom-file-upload" type="file" name="files[]" id="filesMultiple" multiple style="display:none;" disabled>

                <!-- Selected files preview -->
                <ul id="fileList"></ul>

                <div id="data_type_div">
                  <label class="form-label mt-2" for="file">Data Type</label>
                  <select class="form-control" name="data_type" id="data_type" onchange="showFileTypeOptions()" disabled>
                    <option value="customer_data">Customer Data</option>
                    <option value="Engineering_data">Engineering Data</option>
                  </select>
                </div>

                <div id="file_type_options" style="display: none;">
                  <label class="form-label mt-2" for="file">Location</label>
                  <?php
                  require __DIR__ . '/vendor/autoload.php';

                  use Google\Client;
                  use Google\Service\Drive;

                  // Your Google API credentials
                  putenv('GOOGLE_APPLICATION_CREDENTIALS=./google-drive/credentials.json');

                  $project_id = isset($_GET['project_id']) ? htmlspecialchars($_GET['project_id']) : '';

                  try {
                    // Initialize Google Drive API client
                    $client = new Client();
                    $client->useApplicationDefaultCredentials();
                    $client->addScope(Drive::DRIVE);

                    // Create a Google Drive service
                    $service = new Drive($client);

                    // Find the folder IDs for the specified project folders
                    $projectFolderIds = getFolderIdsByNumericPrefix($service, $project_id);

                    if (empty($projectFolderIds)) {
                      echo '<option value="Engineering_data" selected>Engineering Data</option>';
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

                          echo '<option value="Engineering_data" selected>Engineering Data</option>';


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
                  } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage();
                  }

                  function getFolderIdsByNumericPrefix($service, $numericPrefix)
                  {
                    $folderIds = [];

                    // List files in the root folder to find the specified project folders
                    $files = $service->files->listFiles([
                      'q' => "mimeType='application/vnd.google-apps.folder'",
                    ]);

                    foreach ($files->getFiles() as $file) {
                      $folderName = $file->getName();
                      // Extract numeric part until the underscore
                      preg_match('/^(\d+)_/', $folderName, $matches);

                      if ($matches && $matches[1] == $numericPrefix) {
                        $folderIds[] = $file->getId();
                      }
                    }

                    return $folderIds;
                  }

                  function getSubfoldersAndFiles($service, $rootFolderId, $parentFolderId)
                  {
                    $subfoldersAndFiles = [];

                    // List files in the specified folder
                    $files = $service->files->listFiles([
                      'q' => "'$parentFolderId' in parents",
                    ]);

                    foreach ($files->getFiles() as $file) {
                      if ($file->getMimeType() == 'application/vnd.google-apps.folder') {
                        // If it's a subfolder, recursively get its contents
                        $subfolderId = $file->getId();
                        $subfolderName = $file->getName();
                        $subfoldersAndFiles[] = [
                          'id' => $subfolderId,
                          'name' => $subfolderName,
                          'contents' => getSubfoldersAndFiles($service, $rootFolderId, $subfolderId),
                        ];
                      }
                    }

                    return $subfoldersAndFiles;
                  }

                  function displaySubfolders($subfolders, $indentation = '')
                  {
                    foreach ($subfolders as $subfolder) {
                      // Display subfolders only
                      echo "<option value='{$subfolder['id']}'>$indentation{$subfolder['name']}</option>";
                      displaySubfolders($subfolder['contents'], $indentation . '&nbsp;&nbsp;&nbsp;&nbsp;'); // Recursively display sub-subfolders
                    }
                  }
                  ?>


                </div>


                <br>
                <button onclick="loading()" class="btn btn-primary mt-2" type="submit" name="submit" disabled>Upload File</button>

              </form>
              <script>
                // JavaScript function to handle file preview
                function handleFileSelect(event) {
                  const fileList = document.getElementById('fileList');
                  fileList.innerHTML = ''; // Clear previous entries

                  const files = event.target.files;
                  if (files.length > 0) {
                    for (const file of files) {
                      const listItem = document.createElement('div'); // Use div instead of li for preview
                      listItem.textContent = file.name;
                      fileList.appendChild(listItem);
                    }
                  }
                }

                // Add event listener to the file input for file selection
                document.getElementById('filesMultiple').addEventListener('change', handleFileSelect);
              </script>

              <script>
                function showFileTypeOptions() {
                  var dataType = document.getElementById('data_type');
                  var fileTypeOptions = document.getElementById('file_type_options');

                  if (dataType.value === 'Engineering_data') {
                    fileTypeOptions.style.display = 'block';
                  } else {
                    fileTypeOptions.style.display = 'none';
                  }
                }
              </script>

              <script>
                function handleFileSelect(event) {
                  const fileList = document.getElementById('fileList');
                  fileList.innerHTML = ''; // Clear previous entries

                  const files = event.target.files;
                  if (files.length > 0) {
                    const commonPrefix = getCommonPrefix(files);
                    for (const file of files) {
                      if (file.isDirectory) {
                        handleDirectory(file, commonPrefix);
                      } else {
                        const listItem = document.createElement('li');
                        listItem.textContent = file.webkitRelativePath || file.name;
                        fileList.appendChild(listItem);
                      }
                    }
                  }
                }

                function handleDirectory(directory, commonPrefix) {
                  const reader = directory.createReader();
                  reader.readEntries((entries) => {
                    entries.forEach((entry) => {
                      const listItem = document.createElement('li');
                      const relativePath = entry.fullPath.substring(commonPrefix.length);
                      listItem.textContent = relativePath;
                      fileList.appendChild(listItem);
                    });
                  });
                }

                function getCommonPrefix(files) {
                  if (files.length === 0) {
                    return '';
                  }

                  const paths = Array.from(files, (file) => file.webkitRelativePath || file.name);
                  const commonPrefix = paths.reduce((prefix, path) => {
                    let i = 0;
                    while (i < prefix.length && i < path.length && prefix[i] === path[i]) {
                      i++;
                    }
                    return prefix.slice(0, i);
                  });

                  return commonPrefix;
                }

                // Add event listeners to both file input elements
                document.getElementById('filesDirectory').addEventListener('change', handleFileSelect);
                document.getElementById('filesMultiple').addEventListener('change', handleFileSelect);
              </script>


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
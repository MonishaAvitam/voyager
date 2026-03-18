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
  $project_id = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

  if ($project_id === false || $project_id === null) {
    $msg_warning = "Invalid project ID.";
  } else {
    // Create the project folder based on the project_id
    $upload_destination = 'DATA/Projects/Project_' . $project_id . '/';

    if (!is_dir($upload_destination) && !mkdir($upload_destination, 0755, true)) {
      $msg_warning = 'Error Creating Project Folder';
    } else {
      // Connect to the database (Assuming you are using PDO)
      $dbHost = 'localhost';
      $dbName = 'csaengin_epms_dev';
      $dbUser = 'csaengin_shiva';
      $dbPassword = 'Revanthshiva3';

      try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check the projects table for reopen_status
        $stmt = $pdo->prepare("SELECT reopen_status FROM projects WHERE project_id = :project_id");
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();

        $reopen_status = $stmt->fetchColumn();

        if (!empty($reopen_status)) {
          // Create a folder with the name of reopen_status inside the project folder
          $reopen_directory = $upload_destination . $reopen_status . '/';

          if (!is_dir($reopen_directory) && !mkdir($reopen_directory, 0755, true)) {
            $msg_warning = 'Error Creating Reopen Status Folder';
          } else {
            // Check the selected value for data type
            $data_type = filter_input(INPUT_POST, 'data-type', FILTER_SANITIZE_STRING);

            if ($data_type === 'engineering_data') {
              // Create the main "engineering_data" folder
              $data_type_directory = $reopen_directory . 'engineering_data/';

              if (!is_dir($data_type_directory) && !mkdir($data_type_directory, 0755, true)) {
                $msg_warning = "Error Creating $data_type Directory";
              } else {
                // Check the selected value for file type
                $fileType = filter_input(INPUT_POST, 'file-type', FILTER_SANITIZE_STRING);

                if (in_array($fileType, ['design', 'analysis', 'drawing'])) {
                  // Create subdirectories for file types
                  $fileTypeDirectory = $data_type_directory . $fileType . '/';

                  if (!is_dir($fileTypeDirectory) && !mkdir($fileTypeDirectory, 0755, true)) {
                    $msg_warning = "Error Creating $fileType Directory";
                  } else {
                    // Loop through and handle file uploads
                    foreach ($_FILES['files']['name'] as $key => $filename) {
                      $file = $_FILES['files']['tmp_name'][$key];
                      $targetFilePath = $fileTypeDirectory . basename($filename);

                      if (move_uploaded_file($file, $targetFilePath)) {
                        $msg_success = "File uploaded successfully: $filename";
                      } else {
                        $msg_warning = "Failed to move the file $filename to the upload directory: $targetFilePath";
                      }
                    }
                  }
                } else {
                  $msg_warning = "Please select a valid file type.";
                }
              }
            } elseif ($data_type === 'customer_id_data') {
              // Create the "customer_id_data" folder if it doesn't exist
              $data_type_directory = $upload_destination . 'customer_id_data/';

              if (!is_dir($data_type_directory) && !mkdir($data_type_directory, 0755, true)) {
                $msg_warning = "Error Creating $data_type Directory";
              } else {
                // Loop through and handle file uploads
                foreach ($_FILES['files']['name'] as $key => $filename) {
                  $file = $_FILES['files']['tmp_name'][$key];
                  $targetFilePath = $data_type_directory . basename($filename);

                  if (move_uploaded_file($file, $targetFilePath)) {
                    $msg_success = "File uploaded successfully: $filename";
                  } else {
                    $msg_warning = "Failed to move the file $filename to the upload directory: $targetFilePath";
                  }
                }
              }
            } else {
              $msg_warning = "Please select a valid data type.";
            }
          }
        } else {
          // $msg_warning = "Reopen Status is empty.";
          if (isset($_FILES['files']) && !empty($_FILES['files'])) {
            // Validate and sanitize project_id from the URL
            $project_id = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);

            if ($project_id === false || $project_id === null) {
                $msg_warning = "Invalid project ID.";
            } else {
                // Create the project folder based on the project_id
                $upload_destination = 'DATA/Projects/Project_' . $project_id . '/';

                if (!is_dir($upload_destination) && !mkdir($upload_destination, 0755, true)) {
                    $msg_warning = 'Error Creating Project Folder';
                } else {
                    // Check the selected value for data type
                    $data_type = filter_input(INPUT_POST, 'data-type', FILTER_SANITIZE_STRING);

                    if (!empty($data_type)) {
                        if ($data_type === 'engineering_data') {
                            // Create a data-type folder (e.g., 'engineering_data')
                            $data_type_directory = $upload_destination . $data_type . '/';

                            if (!is_dir($data_type_directory) && !mkdir($data_type_directory, 0755, true)) {
                                $msg_warning = "Error Creating $data_type Directory";
                            } else {
                                // Check the selected value for file type
                                $fileType = filter_input(INPUT_POST, 'file-type', FILTER_SANITIZE_STRING);

                                if (in_array($fileType, ['design', 'analysis', 'drawing', 'documentation'])) {
                                    // Create a subdirectory for the selected file type
                                    $fileTypeDirectory = $data_type_directory . $fileType . '/';

                                    if (!is_dir($fileTypeDirectory) && !mkdir($fileTypeDirectory, 0755, true)) {
                                        $msg_warning = "Error Creating $fileType Directory";
                                    } else {
                                        // Loop through and handle file uploads
                                        foreach ($_FILES['files']['name'] as $key => $filename) {
                                            $file = $_FILES['files']['tmp_name'][$key];
                                            $targetFilePath = $fileTypeDirectory . basename($filename);

                                            if (move_uploaded_file($file, $targetFilePath)) {
                                                $msg_success = "File uploaded successfully: $filename";
                                            } else {
                                                $msg_warning = "Failed to move the file $filename to the upload directory: $targetFilePath";
                                            }
                                        }
                                    }
                                } else {
                                    $msg_warning = "Please select a valid file type.";
                                }
                            }
                        } elseif ($data_type === 'customer_id_data') {
                            // Create a data-type folder (e.g., 'customer_id_data')
                            $data_type_directory = $upload_destination . $data_type . '/';

                            // Check if the directory exists, and create it if it doesn't
                            if (!is_dir($data_type_directory) && !mkdir($data_type_directory, 0755, true)) {
                                $msg_warning = "Error Creating $data_type Directory";
                            } else {
                                // Upload files directly into the 'customer_id_data' folder
                                foreach ($_FILES['files']['name'] as $key => $filename) {
                                    $file = $_FILES['files']['tmp_name'][$key];
                                    $targetFilePath = $data_type_directory . basename($filename);

                                    if (move_uploaded_file($file, $targetFilePath)) {
                                        $msg_success = "File uploaded successfully: $filename";
                                    } else {
                                        $msg_warning = "Failed to move the file $filename to the upload directory: $targetFilePath";
                                    }
                                }
                            }
                        } else {
                            $msg_warning = "Please select a valid data type.";
                        }
                    } else {
                        $msg_warning = "Data type is empty.";
                    }
                }
            }
        }

        }
      } catch (PDOException $e) {
        $msg_warning = "Database Error: " . $e->getMessage();
      }
    }
  }
} else {
  // Handle the case when $_FILES['files'] is not set or empty
//$msg_warning = "No files were uploaded.";
}




















?>

<script>
  $(document).ready(function() {
    $("#all_data").modal('show');
  });
</script>


<style>
  .upload-form {
    display: flex;
    max-width: 100%;
    padding: 20px;
    flex-flow: column;
    background-color: #fff;
    box-shadow: 0 0 5px 0 rgba(0, 0, 0, 0.15);
  }



  .upload-form label {
    display: flex;
    flex-flow: column;
    justify-content: center;
    align-items: center;
    background-color: #fafbfb;
    border: 1px solid #e6e8ec;
    color: #737476;
    padding: 10px 12px;
    font-weight: 500;
    font-size: 14px;
    margin: 10px 0;
    border-radius: 4px;
    cursor: pointer;
  }

  .upload-form label i {
    margin-right: 10px;
    padding: 5px 0;
    color: #dbdce0;
  }

  .upload-form label span {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    word-break: break-all;
  }

  .upload-form label:hover {
    background-color: #f7f8f9;
    border: 1px solid #e3e5ea;
    color: #68686a;
  }

  .upload-form label:hover i {
    color: #cfd1d4;
  }

  .upload-form input[type="file"] {
    appearance: none;
    visibility: hidden;
    height: 0;
    width: 0;
    padding: 0;
    margin: 0;
  }

  .upload-form .progress {
    height: 20px;
    border-radius: 4px;
    margin: 10px 0;
    background-color: #e6e8ec;
  }

  .upload-form button {
    appearance: none;
    background-color: #072745;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    border: 0;
    padding: 10px 12px;
    margin-top: 10px;
    color: #fff;
    cursor: pointer;
  }

  .upload-form button:hover {
    background-color: #b6563e;
  }

  .upload-form button:disabled {
    background-color: #aca7a5;
  }



  /* Styles for the select element */
  .data-type {
    padding: 10px;
    /* Adjust the padding as needed */
    font-size: 16px;
    /* Adjust the font size as needed */
    width: 100%;
    /* Make the select element 100% width of its container */
    border: 1px solid #ccc;
    /* Add a border */
    border-radius: 5px;
    /* Add border-radius for rounded corners */
    background-color: #fff;
    /* Set background color */
    color: #333;
    /* Set text color */
  }

  /* Styles for the select options */
  .data-type option {
    padding: 10px;
    /* Adjust the padding as needed */
  }

  /* Hover effect for select options */
  .data-type option:hover {
    background-color: #f0f0f0;
    /* Change background color on hover */
  }

  /* Focus styles for select element */
  .data-type:focus {
    outline: none;
    /* Remove the default outline */
    border-color: #3498db;
    /* Change border color on focus */
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.7);
    /* Add a subtle box-shadow on focus */
  }
</style>
<div class="modal fade" id="all_data" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Data</h5>

      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-container">
              <form class="upload-form" action="" method="post" enctype="multipart/form-data">

                <label for="files"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z" />
                    <path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3z" />
                  </svg>Select files ...</label>
                <input id="files" type="file" name="files[]" multiple directory webkitdirectory />

                <select class="data-type" name="data-type" id="data-type">
                  <option value="engineering_data">Engineering Data</option>
                  <option value="customer_id_data">customer_id Data</option>
                </select>
                <select class="data-type mt-2" name="file-type" id="file-type">
                  <option value="design">Design</option>
                  <option value="analysis">Analysis</option>
                  <option value="drawing">Drawing</option>
                  <option value="documentation">Documentation</option>
                </select>
                <script>
                  $(document).ready(function() {
                    // Function to toggle the visibility of the file-type dropdown
                    function toggleFileTypeDropdown() {
                      var selectedDataType = $("#data-type").val();
                      if (selectedDataType === "engineering_data") {
                        $("#file-type").show(); // Show the file-type dropdown
                      } else {
                        $("#file-type").hide(); // Hide the file-type dropdown
                      }
                    }

                    // Initial call to toggleFileTypeDropdown on page load
                    toggleFileTypeDropdown();

                    // Attach an event handler to the data-type dropdown to toggle the file-type dropdown
                    $("#data-type").change(function() {
                      toggleFileTypeDropdown();
                    });
                  });
                </script>

                <div class="progress"></div>

                <button type="submit" name="data-upload">Upload</button><br><br>

              </form>

            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal" onclick="window.location.href='index.php'">Done</button>

        <button class="btn btn-secondary" type="button" data-dismiss="modal" onclick="window.location.href='index.php'">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Declare global variables for easy access 
  const uploadForm = document.querySelector('.upload-form');
  const filesInput = uploadForm.querySelector('#files');

  // Function to initialize the progress bar
  function initializeProgressBar() {
    uploadForm.querySelector('button').innerHTML = 'Upload';
    uploadForm.querySelector('.progress').style.background = 'linear-gradient(to right, #e6e8ec, #e6e8ec)';
    uploadForm.querySelector('button').disabled = false;
  }

  // Attach onchange event handler to the files input element
  filesInput.onchange = () => {
    // Append all the file names to the label
    uploadForm.querySelector('label').innerHTML = '';
    for (let i = 0; i < filesInput.files.length; i++) {
      uploadForm.querySelector('label').innerHTML += '<span><i class="fa-solid fa-file"></i>' + filesInput.files[i].name + '</span>';
    }
  };

  // Attach submit event handler to form
  uploadForm.onsubmit = event => {
    event.preventDefault();
    // Make sure files are selected
    if (!filesInput.files.length) {
      // Handle case when no files are selected
      alert('Please select a file to upload.');
      return;
    }

    // Create a FormData object for the form
    const formData = new FormData(uploadForm);

    // Initiate the AJAX request
    const request = new XMLHttpRequest();
    request.open('POST', uploadForm.action);

    // Attach the progress event handler to the AJAX request
    request.upload.addEventListener('progress', event => {
      if (event.lengthComputable) {
        // Add the current progress to the button
        uploadForm.querySelector('button').innerHTML = 'Uploading... ' + '(' + ((event.loaded / event.total) * 100).toFixed(2) + '%)';
        // Update the progress bar
        uploadForm.querySelector('.progress').style.background = 'linear-gradient(to right, #25b350, #25b350 ' + Math.round((event.loaded / event.total) * 100) + '%, #e6e8ec ' + Math.round((event.loaded / event.total) * 100) + '%)';
        // Disable the submit button
        uploadForm.querySelector('button').disabled = true;
      }
    });

    // The following code will execute when the request is complete
    request.onreadystatechange = () => {
      if (request.readyState == 4) {
        if (request.status == 200) {
          // Handle a successful upload, e.g., display a success message
          $msg_success = 'Upload completed!';


          // Reset the form and clear success message after a brief delay
          setTimeout(() => {
            uploadForm.reset();
            location.reload();

            initializeProgressBar(); // Reinitialize the progress bar for the next upload
          }, 1000); // Adjust the delay time as needed (2 seconds in this example)
        } else {
          // Handle an error, e.g., display an error message
          $msg_error = 'Upload failed. Please try again.';
          // Reset the form and clear error message after a brief delay
          setTimeout(() => {
            uploadForm.reset();
            initializeProgressBar(); // Reinitialize the progress bar for the next upload
          }, 2000); // Adjust the delay time as needed (2 seconds in this example)
        }
      }
    };

    // Execute the request with the FormData
    request.send(formData);
  };

  // Initialize the progress bar when the page loads
  initializeProgressBar();
</script>

<?php include 'include/footer.php'  ?>
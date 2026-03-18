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


// check admin
$user_role = $_SESSION['user_role'];

include 'include/sidebar.php';
include 'add_project.php';



?>



<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="well well-custom">
                <div class="row">
                    <div class="col-md-12 col-md-offset-2">
                        <div class="well">
                            <h3 class="text-center bg-primary " style="padding: 7px; color:aliceblue; ">Project Files</h3><br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-single-product">

                                            <tbody>




                                                <?php

                                                require __DIR__ . '/vendor/autoload.php';

                                                use Google\Client;
                                                use Google\Service\Drive;

                                                $folderId = isset($_GET['folderId']) ? htmlspecialchars($_GET['folderId']) : '';
                                                $projectId = isset($_GET['parentFolderId']) ? htmlspecialchars($_GET['parentFolderId']) : '';

                                                // Your Google API credentials
                                                putenv('GOOGLE_APPLICATION_CREDENTIALS=./google-drive/credentials.json');

                                                try {
                                                    // Initialize Google Drive API client
                                                    $client = new Client();
                                                    $client->useApplicationDefaultCredentials();
                                                    $client->addScope(Drive::DRIVE);

                                                    // Create a Google Drive service
                                                    $service = new Drive($client);

                                                    // Retrieve parent folder name
                                                    $parentFolderName = '';
                                                    if (!empty($folderId)) {
                                                        $parentFolder = $service->files->get($folderId);
                                                        $parentFolderName = $parentFolder->getName();
                                                    }

                                                    // Display parent folder name
                                                    echo '<h2>Folder: ' . $parentFolderName . '</h2>';

                                                    // Retrieve and display files for the specified folder
                                                    $files = $service->files->listFiles([
                                                        'q' => "'$folderId' in parents", // Search for files in the specified folder
                                                    ]);

                                                    echo '<table class="table table-bordered table-single-product">';
                                                    echo '<tr><th >File Name</th><th style="width: 600px;">Actions</th></tr>';

                                                    foreach ($files->getFiles() as $file) {
                                                        echo '<tr>';
                                                        echo '<td>';

                                                        // Check if the file is a folder
                                                        if ($file->getMimeType() == 'application/vnd.google-apps.folder') {
                                                            // If it's a folder, make it clickable
                                                            echo "<a href='view_Gfiles.php?folderId={$file->getId()}&parentFolderId={$projectId}'>{$file->getName()}</a>";
                                                        } else {
                                                            // If it's a regular file, just display the name
                                                            echo $file->getName();
                                                        }

                                                        echo "</td>";
                                                        echo "<td >";

                                                        // Check if the file is a regular file
                                                        if ($file->getMimeType() != 'application/vnd.google-apps.folder') {
                                                            // If it's a regular file, show the delete option
                                                            $fileIdToRemove = $file->getId();
                                                            $fileIdToMove = $file->getId();
                                                            echo "<button class='btn btn-success float-right ml-2' onclick='download_gfiles(\"$fileIdToRemove\")'>Download</button>";
                                                            echo "<button class='btn btn-danger float-right ml-2' onclick='delete_gfiles(\"$fileIdToRemove\")'>Delete</button>";
                                                            if ($parentFolderName !== "Develirables_Data" and $parentFolderName !== "customer_id_data") {
                                                                echo "<button class='btn btn-info ml-2 float-right' onclick='move_Todevelirable(\"$fileIdToMove\", \"$projectId\")'>Move to Deliverables</button>";
                                                            }
                                                            
                                                        }

                                                        echo "</td>";
                                                        echo '</tr>';
                                                    }

                                                    echo '</table>';
                                                } catch (Google_Service_Exception $e) {
                                                    // Handle API errors
                                                    echo 'Error: ' . $e->getMessage();
                                                } catch (Google_Exception $e) {
                                                    // Handle other errors
                                                    echo 'Error: ' . $e->getMessage();
                                                }

                                                ?>








                                                <script>
                                                    function delete_gfiles(fileId) {
                                                        var confirmation = confirm("Are you sure you want to delete this file?");
                                                        if (confirmation) {
                                                            // You can use AJAX or any other method to send the fileId to a PHP script for deletion
                                                            // For simplicity, I'll use window.location to navigate to a delete script with the fileId as a parameter
                                                            window.location.href = 'delete_gfile.php?fileId=' + fileId;
                                                        }
                                                    }
                                                    function download_gfiles(fileId) {
                                                        var confirmation = confirm("Are you sure you want to Download this file?");
                                                        if (confirmation) {
                                                            // You can use AJAX or any other method to send the fileId to a PHP script for deletion
                                                            // For simplicity, I'll use window.location to navigate to a delete script with the fileId as a parameter
                                                            window.location.href = 'download_Gfile.php?fileId=' + fileId;
                                                        }
                                                    }

                                                    function move_Todevelirable(fileId, projectId) {
                                                        var confirmation = confirm("Are you sure you want to move this file to the Deliverables folder?");
                                                        if (confirmation) {
                                                            // Use AJAX or any other method to send the fileId to a PHP script for handling the move
                                                            // For simplicity, I'll use window.location to navigate to a script with the fileId and projectId as parameters
                                                            window.location.href = 'moveTodevelirable_Gfile.php?fileId=' + fileId + '&parentFolderId=' + projectId;
                                                        }
                                                    }
                                                </script>











                                            </tbody>
                                            <button class="btn btn-primary" onclick="javascript:history.back()">Go Back</button>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php


include("include/footer.php");

?>
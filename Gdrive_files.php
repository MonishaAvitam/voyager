<?php



require 'authentication.php'; // admin authentication check 
require './conn.php';
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

                                                // $project_id = "30001";
                                                $project_id = isset($_GET['file_project_id']) ? htmlspecialchars($_GET['file_project_id']) : '';


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
                                                    $customer_id =  $row['customer_id'];
                                                }

                                                echo $ProjecFoldertName =  $project_id . '_' . $customer_id;



                                                // Your Google API credentials
                                                putenv('GOOGLE_APPLICATION_CREDENTIALS=./google-drive/credentials.json');

                                                try {
                                                    // Initialize Google Drive API client
                                                    $client = new Google\Client();
                                                    $client->useApplicationDefaultCredentials();
                                                    $client->addScope(Google\Service\Drive::DRIVE);

                                                    // Create a Google Drive service
                                                    $service = new Google\Service\Drive($client);

                                                    // Specify the project ID you want to filter for
                                                    $projectIdToFilter = $project_id;

                                                    // Find the folder IDs for the specified project folders
                                                    $projectFolderIds = getFolderIdsByNumericPrefix($service, $projectIdToFilter);

                                                    if (empty($projectFolderIds)) {
                                                        echo 'No project folders found.';
                                                    } else {
                                                        // Display files in a table
                                                        echo '<table class="table table-bordered table-single-product">';
                                                        echo '<tr><th>Project Folder</th></tr>';

                                                        foreach ($projectFolderIds as $projectFolderId) {
                                                            try {
                                                                $folder = $service->files->get($projectFolderId);
                                                                $folderName = $folder->getName();

                                                                $files = $service->files->listFiles([
                                                                    'q' => "'$projectFolderId' in parents", // Search for files in the specified folder
                                                                ]);

                                                                if (count($files->getFiles()) == 0) {
                                                                } else {
                                                                    foreach ($files->getFiles() as $file) {
                                                                        echo '<tr>';
                                                                        echo "<td>";

                                                                        // Check if the file is a folder
                                                                        if ($file->getMimeType() == 'application/vnd.google-apps.folder') {
                                                                            // If it's a folder, make it clickable
                                                                            echo "<a href='view_Gfiles.php?folderId={$file->getId()}&parentFolderId={$projectFolderId}'>{$file->getName()}</a>";
                                                                        } else {
                                                                            // If it's a regular file, just display the name
                                                                            echo $file->getName();
                                                                        }

                                                                        echo "</td>";
                                                                        echo '</tr>';
                                                                    }
                                                                }
                                                            } catch (Google_Service_Exception $e) {
                                                                // Handle API errors
                                                                echo 'Error: ' . $e->getMessage();
                                                            } catch (Google_Exception $e) {
                                                                // Handle other errors
                                                                echo 'Error: ' . $e->getMessage();
                                                            }
                                                        }

                                                        echo '</table>';
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
                                                ?>











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
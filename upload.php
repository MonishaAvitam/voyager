<body>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10">


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>




    <?php
    session_start();


    require __DIR__ . '/../vendor/autoload.php';

    use Google\Client;
    use Google\Service\Drive;




    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        // Validate and sanitize inputs
        $project_id = intval($_POST['projectId']);
        $data_type = $_POST['data_type'];
        $file_type = $_POST['file_type'];
        $customer_name_id = $_POST['customer_name_id'];
        $project_name = $_POST['project_name'];

        try {
            // Initialize Google Drive API client
            $client = new Client();
            putenv('GOOGLE_APPLICATION_CREDENTIALS=./apikey.json');
            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);

            // Define the parent folder ID
            $parentFolderId = '1eTrw8rlbfGSPxmnXgL2DMkE3P3gob0zG';
            // $parentFolderId = '1ql_9wK5ZenhJyJKWbsDVcEm_ic_3iCEN';

            // Ensure projects folder exists within the parent folder
            $parentFolderID = createOrGetFolder($driveService, $parentFolderId, 'Engineering_projects');

            // Ensure child folder exists within the projects folder
            $childFolderID = createOrGetFolder($driveService, $parentFolderID, $project_id . '_' . $customer_name_id . '_' . $project_name);

            // If data type is "Engineering_data", create a subfolder within the child folder
            if ($data_type == 'Engineering_data') {
                $engineeringFolderID = createOrGetFolder($driveService, $childFolderID, $data_type);
                $fileFolderID = createOrGetFolder($driveService, $engineeringFolderID, $file_type);
            } elseif ($data_type == 'Customer_data') {
                // Upload files directly into the customer folder
                $customerFolderID = createOrGetFolder($driveService, $childFolderID, $data_type);
                $fileFolderID = $customerFolderID;
            } else {
                // Handle other data types or provide an error message
                echo json_encode(['success' => false, 'message' => 'Invalid data type.']);
                exit;
            }



            // Ensure file type folder (e.g., Drawing File) exists within the engineering folder

            // Loop through each uploaded file
            $totalFiles = count($_FILES['files']['name']);
            $uploadedFiles = 0;

            for ($i = 0; $i < $totalFiles; $i++) {
                $fileTmpName = $_FILES['files']['tmp_name'][$i];
                $fileName = basename($_FILES['files']['name'][$i]);

                // Use finfo for more reliable MIME type detection
                $mimetype = getMimeType($fileTmpName);

                // Upload file to the file type folder
                $fileMetadata = new Drive\DriveFile([
                    'name' => $fileName,
                    'parents' => [$fileFolderID]
                ]);


                uploadFileToDrive($driveService, $fileMetadata, $fileTmpName, $mimetype);

                $uploadedFiles++;
            }

            // Respond with success message after all files are uploaded
            if ($uploadedFiles > 0) {
                $viewLink = "https://drive.google.com/drive/folders/{$childFolderID}";
                $downloadLink = "https://drive.google.com/uc?id={$fileFolderID}";

                $response = [
                    'success' => true,
                    'message' => 'Files uploaded successfully!',
                    'viewLink' => $viewLink,
                    'downloadLink' => $downloadLink
                ];



                // Set success message
                $_SESSION['status_success'] = "File uploaded successfully.";

                // Redirect back to the previous page
                header("Location: {$_SERVER['HTTP_REFERER']}");
                exit; // Ensure that no further code is executed after the redirect


                exit;
            }
        } catch (Google\Service\Exception $e) {
            // Log and respond to Google Service exceptions
            error_log('Google Service Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Google Service Error.']);
        } catch (Exception $e) {
            // Log and respond to generic exceptions
            error_log('Error uploading files: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error uploading files.']);
        }
    } else {
        // Respond with an error message if the request method or submit key is not valid
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }

    // Function to create or get a folder in Google Drive
    function createOrGetFolder($driveService, $parentFolderId, $folderName)
    {
        $existingFolders = $driveService->files->listFiles([
            'q' => "'$parentFolderId' in parents and mimeType='application/vnd.google-apps.folder' and name='$folderName'",
        ]);

        if (count($existingFolders->getFiles()) > 0) {
            // Folder with the same name already exists, use the existing one
            return $existingFolders->getFiles()[0]->id;
        } else {
            // Folder with the desired name does not exist, create a new one
            $folderMetadata = new Drive\DriveFile([
                'name' => $folderName,
                'parents' => [$parentFolderId],
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            $folder = $driveService->files->create($folderMetadata, [
                'fields' => 'id'
            ]);

            return $folder->id;
        }
    }

    // Function to get MIME type using finfo
 // Function to get MIME type using finfo
function getMimeType($fileTmpName)
{
    if (!empty($fileTmpName) && file_exists($fileTmpName)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $fileTmpName);
        finfo_close($finfo);

        return $mimetype;
    } else {
        // Handle the case where $fileTmpName is empty or the file does not exist
        return false;
    }
}

    // Function to upload a file to Google Drive
    // Function to upload a file to Google Drive
function uploadFileToDrive($driveService, $fileMetadata, $fileTmpName, $mimetype)
{
    if (!empty($fileTmpName) && file_exists($fileTmpName)) {
        $content = file_get_contents($fileTmpName);

        $driveService->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimetype,
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);
    } else {
        // Handle the case where $fileTmpName is empty or the file does not exist
        error_log('File path is empty or does not exist: ' . $fileTmpName);
    }
}









    ?>

</body>
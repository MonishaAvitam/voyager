<?php
require '../authentication.php';
include '../conn.php';
require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

function createOrGetFolder($driveService, $parentFolderId, $folderName)
{
    $existingFolders = $driveService->files->listFiles([
        'q' => "'$parentFolderId' in parents and mimeType='application/vnd.google-apps.folder' and name='$folderName'",
    ]);

    if (count($existingFolders->getFiles()) > 0) {
        return $existingFolders->getFiles()[0]->id;
    } else {
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

if (isset($_POST['add_enquiry_sales'])) {
    $user_id = $_POST['user_id'];
    $enquiry_name = nl2br($_POST['enquiry_name']);
    $contact_id = $_POST['contact_id'];
    $potential_customer = $_POST['potential_customer'];
    $comments = $_POST['comments'];
    $current_time = date("H:i:s"); // Format: Hour:Minute:Second
    $temp_voice_note_path = './temp_voice_note/voice_note_latest.wav';
    $enquiry_status = 0;

    $sql = "INSERT INTO enquiry_sales (date, enquiry_name, comments, contact_id, time, folderId, enquiry_status, user_id, potential_customer) VALUES ( NOW(), ?, ?, ?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);

    // Check if $filesharingURL is available, otherwise insert "No Data"
    $fileValue = isset($filesharingURL) ? $filesharingURL : "No Data";

    $stmt->bind_param(
        "sssissis",
        $enquiry_name,     
        $comments,          
        $contact_id,        
        $current_time,     
        $fileValue,         
        $enquiry_status,    
        $user_id,           
        $potential_customer          
    );

    if ($stmt->execute()) {
        // Get the ID of the last inserted row
        $last_inserted_id = $conn->insert_id;
        $_SESSION['status_success'] = "Enquiry added successfully! ID: S". $last_inserted_id;
        echo 'Data inserted successfully! ID: ' . $last_inserted_id;
    } else {
        echo "Error: Failed to insert data.";
    }

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_enquiry_sales'])) {
            $enquiry_name = nl2br($_POST['enquiry_name']);
            $current_time = date("H:i:s"); // Format: Hour:Minute:Second

            // Initialize Google Drive API client with service account credentials
            $client = new Google_Client();
            $client->setAuthConfig('../google-drive/credentials.json');
            $client->setSubject('csa-admin@csa-rae.iam.gserviceaccount.com');
            $client->addScope(Google\Service\Drive::DRIVE);
            $driveService = new Google\Service\Drive($client);

            // Define the parent folder ID
            $parentFolderId = '1hCPZs3ltrKrmUVEl6rht7KbEt0nhFkPk';

            // Create or get the necessary folders
            $parentFolderID = createOrGetFolder($driveService, $parentFolderId, 'Enquiry_data');
            $childFolderID = createOrGetFolder($driveService, $parentFolderID, 'Enquiry_id-' . $last_inserted_id);

            $folderId = $childFolderID; // Use this variable for both attachment files and voice note

            // Check if attachment file is provided
            if (isset($_FILES['attachment_file'])) {
                $numFiles = count($_FILES['attachment_file']['name']);

                // Loop through each uploaded file
                for ($i = 0; $i < $numFiles; $i++) {
                    if ($_FILES['attachment_file']['error'][$i] === UPLOAD_ERR_OK) {
                        // Handle each attachment file
                        $attachmentFilePath = $_FILES['attachment_file']['tmp_name'][$i];
                        $attachmentFileName = $_FILES['attachment_file']['name'][$i];

                        // Define metadata for the attachment file
                        $attachmentMetadata = new Google\Service\Drive\DriveFile([
                            'name' => $attachmentFileName,
                            'parents' => [$folderId],
                        ]);

                        // Upload the attachment file to Google Drive
                        $uploadedFile = $driveService->files->create($attachmentMetadata, [
                            'data' => file_get_contents($attachmentFilePath),
                            'mimeType' => mime_content_type($attachmentFilePath),
                            'uploadType' => 'multipart',
                            'fields' => 'id, webViewLink' // Request webViewLink in addition to ID
                        ]);
                    }
                }
            }

            // Check if the temporary voice note path exists and is not empty
            if (file_exists($temp_voice_note_path)) {
                // Handle voice file
                $voiceFilePath = $temp_voice_note_path;
                $voiceFileName = basename($voiceFilePath);

                // Define metadata for the voice note file
                $voiceNoteMetadata = new Google\Service\Drive\DriveFile([
                    'name' => $voiceFileName,
                    'parents' => [$folderId],
                ]);

                // Upload the voice note file to Google Drive
                $uploadedVoiceFile = $driveService->files->create($voiceNoteMetadata, [
                    'data' => file_get_contents($voiceFilePath),
                    'mimeType' => mime_content_type($voiceFilePath),
                    'uploadType' => 'multipart',
                    'fields' => 'id, webViewLink' // Request webViewLink in addition to ID
                ]);

                // Check if the file upload was successful
                if (isset($uploadedVoiceFile->webViewLink)) {
                    $voiceNotesharingURL = $uploadedVoiceFile->webViewLink;
                    echo "Voice file uploaded successfully! Sharing URL: $voiceNotesharingURL";

                    // Delete the temporary voice note file after upload
                    unlink($temp_voice_note_path);
                } else {
                    echo "Error: Failed to upload voice file.";
                }
            }

            // Update the folderId in the database
            $sql = "UPDATE enquiry_sales SET folderId = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $folderId, $last_inserted_id);

            if ($stmt->execute()) {
                echo 'Data updated successfully! ID: ' . $last_inserted_id;
                header('Location:' . $_SERVER['HTTP_REFERER']);
            } else {
                echo "Error: Failed to update data.";
            }

            $stmt->close();
        } else {
            throw new Exception("Error: Invalid request.");
        }
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
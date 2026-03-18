<?php

// Include the Google API client library
require_once __DIR__ . '/vendor/autoload.php';

// Set up Google client
$client = new Google_Client();
$client->setAuthConfig('./google-drive/credentials.json'); // Path to your client secret JSON file
$client->addScope(Google_Service_Drive::DRIVE);
$client->setAccessType('offline');

// Initialize Google Drive service
$service = new Google_Service_Drive($client);

// Check if a file was uploaded
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Check for errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Set the ID of the folder where you want to upload the file
        $folderId = '1-Kxq3i-NkSHhF7-SmXWldXWX50WZF_4Q'; // Replace with the ID of your folder

        // Create file metadata with parent folder ID
        $uploadFile = new Google_Service_Drive_DriveFile([
            'name' => $file['name'],
            'parents' => [$folderId]
        ]);

        // Upload the file to Google Drive
        $result = $service->files->create(
            $uploadFile,
            [
                'data' => file_get_contents($file['tmp_name']),
                'mimeType' => $file['type']
            ]
        );

        // Check if the upload was successful
        if ($result) {
            echo "File uploaded successfully.";
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Error: " . $file['error'];
    }
}

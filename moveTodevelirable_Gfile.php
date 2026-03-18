<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getParentFolderId($service, $fileId)
{
    try {
        $file = $service->files->get($fileId, ['fields' => 'parents']);
        $parents = $file->getParents();
        if (!empty($parents)) {
            return $parents[0];
        } else {
            return null;  // No parent folder found
        }
    } catch (Exception $e) {
        echo 'An error occurred: ' . $e->getMessage();
        return null;
    }
}

function checkFolderExistence($driveService, $folderName, $parentFolderId)
{
    // List folders in the parent folder
    $response = $driveService->files->listFiles([
        'q' => "mimeType='application/vnd.google-apps.folder' and name='$folderName' and '$parentFolderId' in parents",
    ]);

    // If the folder exists, return its ID
    if (!empty($response->files)) {
        return $response->files[0]->id;
    }

    return null;
}

function createFolder($driveService, $folderName, $parentFolderId)
{
    // Create a new folder within the parent folder
    $folderMetadata = new DriveFile([
        'name'     => $folderName,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents'  => [$parentFolderId],
    ]);

    $folder = $driveService->files->create($folderMetadata, [
        'fields' => 'id',
    ]);

    return $folder->id;
}

if (isset($_GET['fileId'])) {
    $fileId = htmlspecialchars($_GET['fileId']);
    $newParentFolderId = isset($_GET['parentFolderId']) ? htmlspecialchars($_GET['parentFolderId']) : '';

    // Set up the API credentials
    $client = new Google_Client();
    $client->setAuthConfig('./google-drive/credentials.json'); // Path to your service account JSON key file
    $client->addScope(Drive::DRIVE);

    // Create the Google Drive service
    $driveService = new Drive($client);

    // Your Google Drive folder name
    $folderName = 'Develirables_Data';

    // ID of the parent folder (replace with the actual parent folder ID)
    $parentFolderId = $newParentFolderId;

    // Check if the folder already exists within the parent folder
    $folderId = checkFolderExistence($driveService, $folderName, $parentFolderId);

    // If the folder doesn't exist, create it within the parent folder
    if (!$folderId) {
        $folderId = createFolder($driveService, $folderName, $parentFolderId);
    }

    // echo "Folder ID: $folderId\n";

    try {
        // Get the current parent folder ID
        $currentParentFolderId = getParentFolderId($driveService, $fileId);

        if ($currentParentFolderId) {
            echo 'Current Parent folder ID: ' . $currentParentFolderId . '<br>';
        } else {
            echo 'Unable to retrieve current parent folder ID.<br>';
        }

        // ID of the destination folder
        $destinationFolderId = $folderId;

        // Move the file only if it has parents
        if ($currentParentFolderId) {
            // Move the file to the destination folder
            $file = $driveService->files->update(
                $fileId,
                new Google\Service\Drive\DriveFile(),
                [
                    'addParents'    => $destinationFolderId,
                    'removeParents' => $currentParentFolderId,
                    'fields'        => 'id,parents',
                ]
            );

            // Output the moved file details
            // echo 'File ID ' . $file->id . ' moved to folder ' . $destinationFolderId;
            header("Location: {$_SERVER['HTTP_REFERER']}");

        } else {
            echo 'The file has no current parent folder.';
        }
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo 'Missing required parameters.';
}

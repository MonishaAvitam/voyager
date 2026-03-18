<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

// Your Google API credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=./google-drive/apikey.json');

// Initialize Google Drive API client
$client = new Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Drive::DRIVE);

// Create a Google Drive service
$service = new Drive($client);

if (isset($_GET['fileId'])) {
    $fileId = htmlspecialchars($_GET['fileId']);

    try {
        // Retrieve file metadata
        $file = $service->files->get($fileId);

        // Set appropriate headers for file download
        header('Content-Type: ' . $file->getMimeType());
        header('Content-Disposition: attachment; filename="' . $file->getName() . '"');

        // Download the file content
        $response = $service->files->get($fileId, ['alt' => 'media']);
        echo $response->getBody()->getContents();

    } catch (Google_Service_Exception $e) {
        // Handle API errors
        echo 'Google Service Error: ' . $e->getMessage();
    } catch (Google_Exception $e) {
        // Handle other errors
        echo 'Google Error: ' . $e->getMessage();
    }
} else {
    echo "FileId not provided.";
}
?>

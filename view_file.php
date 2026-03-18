<?php
// Check if the 'file' parameter is provided in the URL
if (isset($_GET['file'])) {
    $fileName = $_GET['file'];
    $filePath = 'DATA/Projects/project_' . $_GET['project_id'] . '/' . $fileName;

    // Check if the file exists
    if (file_exists($filePath)) {
        // Read and display the file content
        echo '<pre>';
        echo htmlspecialchars(file_get_contents($filePath));
        echo '</pre>';
    } else {
        echo 'File not found.';
    }
} else {
    echo 'File name not provided in the URL.';
}
?>

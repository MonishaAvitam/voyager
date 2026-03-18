<?php
// Make sure the captured data exists

if (isset($_FILES['files']) && !empty($_FILES['files'])) {
    // Upload destination directory
    $upload_destination = 'DATA/';

    if (!is_dir($upload_destination)) {
        if (mkdir($upload_destination, 0777, true)) {
            echo 'DATA folder Created ';
        } else {
            echo 'Error Creating DATA Folder';
            exit; // Exit the script if folder creation fails
        }
    }

    // Loop through each uploaded file
    foreach ($_FILES['files']['name'] as $key => $filename) {
        $file = $_FILES['files']['tmp_name'][$key];

        // Check the selected value (e.g., 'customer_id_data')
        if ($_POST['data-type'] === 'customer_id_data') {
            $targetDirectory = $upload_destination . 'customer_id_data/';
        } elseif ($_POST['data-type'] === 'engineering_data') {
            $targetDirectory = $upload_destination . 'engineering_data/';
        } else {
            echo "Please select a valid data type.";
            exit;
        }

        // Create the target directory if it doesn't exist
        if (!is_dir($targetDirectory)) {
            if (mkdir($targetDirectory, 0777, true)) {
                echo 'Data folder Created ';
            } else {
                echo 'Error Creating Data Folder';
                exit; // Exit the script if folder creation fails
            }
        }

        $targetFilePath = $targetDirectory . basename($filename);

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file, $targetFilePath)) {
            echo "File uploaded successfully: $filename<br>";
            
        } else {
            echo "Failed to move the file $filename to the upload directory.<br>";
        }
    }
}
?>
<?php
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    echo "Project ID: " . htmlspecialchars($project_id);
  } else {
    echo "No project ID in the URL.";
  }


?>



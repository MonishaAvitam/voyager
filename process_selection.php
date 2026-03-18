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
include 'enquiry.php';


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["selected_items"]) && isset($_POST["file_project_id"])) {
    // Retrieve the project ID and selected items from the form
    $project_id = $_POST["file_project_id"];
    $selected_items = $_POST["selected_items"];

    // Define the destination directory where selected files will be copied
    $destinationDirectory = 'DATA/Projects/Project_' . $project_id . '/deliverable_data/';

    // Ensure the destination directory exists, create it if necessary
    if (!is_dir($destinationDirectory)) {
        if (!mkdir($destinationDirectory, 0755, true)) {
            die("Failed to create destination directory.");
        }
    }

    // Copy each selected file to the destination directory
    foreach ($selected_items as $sourcePath) {
        $fileName = basename($sourcePath);
        $destinationPath = $destinationDirectory . $fileName;

        if (!file_exists($destinationPath)) {
            // Check if the file already exists in the destination folder
            if (copy($sourcePath, $destinationPath)) {
                $msg_success = "File copied successfully: $fileName<br>";
            } else {
                echo "Failed to copy the file: $fileName<br>";
            }
        } else {
            // Handle the case where the file already exists
            $msg_warning= "File '$fileName' already exists in the destination folder.<br>";
        }
    }

    echo '<script>window.history.back();</script>';
} else {
    echo "Invalid request or no files selected.";
}



?>
<?php include 'include/footer.php'?>
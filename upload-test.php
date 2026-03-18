<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if a file is selected
    if (isset($_FILES["files"])) {
        $file = $_FILES["files"];
        $username = $_POST["username"];
        $password = $_POST["password"];

        echo $username;
        echo $password;

        // Check for errors during file upload
        if ($file["error"] === UPLOAD_ERR_OK) {
            // Specify the directory where the file will be saved
            $uploadDir = "uploads/";

            // Create the directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate a unique filename to avoid overwriting existing files
            $uploadFile = $uploadDir . uniqid() . "_" . basename($file["name"]);

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($file["tmp_name"], $uploadFile)) {
                echo "File is valid, and was successfully uploaded.\n";
            } else {
                echo "Failed to move uploaded file.\n";
            }
        } else {
            echo "Error during file upload: " . $file["error"] . "\n";
        }
    } else {
        echo "No file selected.\n";
    }
} else {
    echo "Invalid request method.\n";
}
?>

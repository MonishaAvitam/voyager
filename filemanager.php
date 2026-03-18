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



?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="well well-custom">
                <div class="row">
                    <div class="col-md-12 col-md-offset-2">
                        <div class="well">
                            <h3 class="text-center bg-primary" style="padding: 7px;">Project Files</h3><br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-single-product">

                                            <tbody>
                                                <?php
                                                if (isset($_GET['file_project_id'])) {
                                                    // Retrieve the project ID from the URL
                                                    $project_id = $_GET['file_project_id'];

                                                    // Now, you can use the $project_id variable in your code
                                                    // $msg_warning = "Project ID: " . $project_id;

                                                    // Check if a specific directory path is requested
                                                    if (isset($_GET['path'])) {
                                                        $directory = $_GET['path'];
                                                    } else {
                                                        $directory = 'DATA/Projects/Project_' . $project_id; // Construct the folder path based on the project ID
                                                    }

                                                    if (is_dir($directory)) {
                                                        echo '<form method="post" action="process_selection.php">'; // Add a form for submitting selections
                                                        echo '<input type="hidden" name="file_project_id" value="' . htmlspecialchars($project_id) . '">'; // Add a hidden input for project ID
                                                        echo '<table class="table table-bordered table-single-product">';
                                                        echo '<thead>';
                                                        echo '<tr>';
                                                        echo '<th>Name</th>';
                                                        echo '<th>Type</th>';
                                                        echo '<th>Action</th>';
                                                        echo '</tr>';
                                                        echo '</thead>';
                                                        echo '<tbody>';

                                                        // Function to recursively list files and subfolders with checkboxes
                                                        function listFilesAndSubfolders($dir)
                                                        {
                                                            $contents = scandir($dir);

                                                            foreach ($contents as $item) {
                                                                // Ignore . and .. entries
                                                                if ($item == '.' || $item == '..') {
                                                                    continue;
                                                                }

                                                                $itemPath = $dir . '/' . $item;
                                                                $fileType = is_dir($itemPath) ? 'Directory' : 'File';

                                                                echo '<tr>';
                                                                echo '<td>';
                                                                if (is_dir($itemPath)) {
                                                                    // If it's a directory, create a checkbox to select it
                                                                    echo '<input type="checkbox" name="selected_items[]" value="' . htmlspecialchars($itemPath) . '"> ';
                                                                    echo '<a href="?file_project_id=' . $_GET['file_project_id'] . '&path=' . urlencode($itemPath) . '"><i class="fas fa-folder"></i> ' . $item . '/</a>';
                                                                } else {
                                                                    // If it's a file, create a checkbox to select it
                                                                    echo '<input type="checkbox" name="selected_items[]" value="' . htmlspecialchars($itemPath) . '"> ';
                                                                    echo '<a href="' . $itemPath . '" download>' . $item . '</a>';
                                                                }
                                                                echo '</td>';
                                                                echo '<td>' . $fileType . '</td>';
                                                                echo '<td>';

                                                                // Show the delete button only for files (not folders)
                                                                if (!is_dir($itemPath)) {
                                                                    echo '<a title="Delete" onclick="confirmDelete(\'' . htmlspecialchars($itemPath) . '\');">';
                                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">';
                                                                    echo '<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6a.5.5 0 0 0-1 0Z" />';
                                                                    echo '<path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />';
                                                                    echo '</svg>';
                                                                    echo '</a>';
                                                                }

                                                                echo '</td>';

                                                                echo '</tr>';
                                                            }
                                                        }




                                                        // Display the contents of the selected directory with checkboxes
                                                        listFilesAndSubfolders($directory);

                                                        echo '</tbody>';
                                                        echo '</table>';
                                                        if ($user_role == 1 or $user_role == 3) {
                                                            echo '<input type="submit" class="btn btn-primary" name="download" value="Move to Deliverables">'; // Add a button to submit selected items for download
                                                        }
                                                        echo '</form>';
                                                    } else {
                                                        $msg_error = "NO FILES IN THAT FOLDER";
                                                    }
                                                }
                                                ?>
                                                <script>
                                                    function confirmDelete(itemPath) {
                                                        var fileName = itemPath.split('/').pop();
                                                        var confirmation = confirm('Are you sure you want to delete this file or directory?\nFile Name: ' + fileName);

                                                        if (confirmation) {
                                                            var xhr = new XMLHttpRequest();
                                                            xhr.open('POST', window.location.href, true);
                                                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                                            xhr.onreadystatechange = function() {
                                                                if (xhr.readyState === 4 && xhr.status === 200) {
                                                                    // Reload the page after a successful deletion
                                                                    window.location.reload();
                                                                }
                                                            };
                                                            xhr.send('itemPath=' + encodeURIComponent(itemPath));
                                                        }
                                                    }
                                                </script>

                                                <?php
                                                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itemPath'])) {
                                                    $itemPath = isset($_POST['itemPath']) ? $_POST['itemPath'] : '';
                                                    $itemPath = realpath($itemPath);

                                                    if ($itemPath !== false && is_file($itemPath)) {
                                                        if (unlink($itemPath)) {
                                                            echo 'File deleted successfully.';
                                                        } else {
                                                            echo 'Unable to delete the file.';
                                                        }
                                                    } else {
                                                        echo 'Invalid file or file does not exist.';
                                                    }
                                                    exit; // Terminate the script after handling the AJAX request
                                                }
                                                ?>

                                                <!-- Your HTML content goes here -->







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
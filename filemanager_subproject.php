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
                                                                echo '<td></td>'; // Action column (you can add actions here if needed)
                                                                echo '</tr>';
                                                            }
                                                        }

                                                        // Display the contents of the selected directory with checkboxes
                                                        listFilesAndSubfolders($directory);

                                                        echo '</tbody>';
                                                        echo '</table>';
                                                        if ($user_role ==1 or $user_role == 3){
                                                        echo '<input type="submit" class="btn btn-primary" name="download" value="Move to Deliverables">'; // Add a button to submit selected items for download
                                                        }
                                                        echo '</form>';
                                                    } else {
                                                        $msg_error = "NO FILES IN THAT FOLDER";
                                                    }
                                                }
                                                ?>






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
<?php

require '../authentication.php'; // admin authentication check 
require '../conn.php';
// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

//end
?>

<?php include 'include/login_header.php'; ?>
<?php include 'include/sidebar.php'; ?>
<?php include 'enquiry.php'; ?>



<!-- dashboard content  -->

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Enquiry Data</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>


    <!-- Content Row -->

    <div class="container-fluid">
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Completed Projects</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="DataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>customer_id Name</th>
                                <th>customer_id Number</th>
                                <th>customer_id EmailID</th>
                                <th>Company</th>
                                <th>Enquiry Details</th>
                                <th>Enquiry Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>customer_id Name</th>
                                <th>customer_id Number</th>
                                <th>customer_id EmailID</th>
                                <th>Company</th>
                                <th>Enquiry Details</th>
                                <th>Enquiry Date</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            <!-- ... Your table rows ... -->
                            <?php
                            $sql = "SELECT *
                    FROM enquiry_data 
                    ORDER BY enquiry_id DESC";

                            // Assuming $obj_admin is an instance of your class that interacts with the database
                            $info = $obj_admin->manage_all_info($sql);
                            $serial = 1;

                            if ($info->rowCount() == 0) {
                                echo '<tr><td colspan="3">No Enquiry  were found</td></tr>';
                            } else {
                                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>

                                    <tr>
                                        <td><?php echo $serial;
                                            $serial++; ?></td>

                                        <td><?php echo $row['customer_id_name']; ?></td>
                                        <td><?php echo $row['ph_number']; ?></td>
                                        <td><?php echo $row['email_id']; ?></td>
                                        <td><?php echo $row['company_name']; ?></td>
                                        <td><?php echo $row['enquiry_details']; ?></td>
                                        <td><?php echo $row['enquiry_data'] ?></td>


                                        <td>
                                            <a title="View" class="view-project" name="view_enquiry" href="?enquiry_id=<?php echo $row['enquiry_id']; ?>" data-toggle="modal" data-target=".bd-progress-modal-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                                    <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                                </svg>
                                            </a>&nbsp;&nbsp;


                                            <a title="Mail" class="view-project" href="task-details.php?project_id=<?php echo $row['enquiry_id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
                                                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z" />
                                                </svg>
                                            </a>&nbsp;&nbsp;

                                            <a title="Delete" href="?delete_enquiry_id=<?php echo $row['enquiry_id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                                                </svg></a>&nbsp;&nbsp;

                                            <a title="Download" class="view-project" href="?enquiry_id=<?php echo $row['enquiry_id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                                </svg>
                                            </a>&nbsp;&nbsp;
                                        </td>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>



    <div class="modal fade bd-progress-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Enquiry Details</h3>
                </div>
                <div class="form-container mt-2 m-2">
                    <?php
                    include 'conn.php';

                    if (isset($_GET['view_enquiry'])) {
                        $enquiry_id = $_GET["view_enquiry"];
                        $sql = "SELECT enquiry_details FROM enquiry_data WHERE enquiry_id = ?";

                        $stmt = $conn->prepare($sql);

                        if ($stmt) {
                            // Bind the parameter to the prepared statement
                            $stmt->bind_param("i", $enquiry_id);

                            // Execute the query
                            if ($stmt->execute()) {
                                // Bind the result to a variable
                                $stmt->bind_result($enquiry_details);

                                if ($stmt->fetch()) {
                                    // Display the retrieved data
                                    echo '<p>' . $enquiry_details . '</p>';
                                } else {
                                    echo '<p>No data found for the given enquiry ID.</p>';
                                }

                                // Close the statement
                                $stmt->close();
                            } else {
                                echo '<p>Error: ' . $stmt->error . '</p>';
                            }
                        } else {
                            echo '<p>Error in preparing the statement.</p>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>


    <!-- End of Main Content -->
    <!-- Send back to user if project not verified  -->
    <?php
    include '../conn.php';
    if (isset($_GET['send_back_project_id'])) {
        // Get the project ID from the URL parameter
        $project_id = $_GET["send_back_project_id"];

        // You can add additional validation and sanitation here

        // Get other data from the URL or from wherever you want
        $status_value = "0";


        // SQL query to insert data into the table
        $sql = "UPDATE projects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL WHERE project_id = ?";
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $status_value, $project_id);

        if ($stmt->execute()) {
            $msg_success = "Data inserted successfully!";
        } else {
            $msg_error = "Error: " . $conn->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }

    ?>
    <?php
    include 'include/footer.php';
    ?>
<?php
// Database connection
include('../conn.php');


?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php


// Check if the form is submitted
if (isset($_POST['save_subprojects'])) {




    // Get form values
    $project_id = $_POST['subproject_id'];  // The subproject ID is passed from the form
    $subproject_name = $_POST['subproject_name'];
    $subproject_details = isset($_POST['subproject_details']) && $_POST['subproject_details'] !== 'null' ? $_POST['subproject_details'] : '';  // Ensure it's not null
    $t_start_date = date("Y-m-d");
    $sub_EPT = isset($_POST['sub_EPT']) && is_numeric($_POST['sub_EPT']) ? $_POST['sub_EPT'] . 'H' : 0;
    $urgency = trim($_POST['urgency_status']); // Assign urgency if it's provided
    $comments = isset($_POST['comments']) && $_POST['comments'] !== 'null' ? $_POST['comments'] : '';  // Ensure it's not null
    $outsource = $_POST['outsourced'] ?? 0;
    $t_end_date = $_POST['sub_end_date'];
    $Quoted = isset($_POST['quoted_cost']) && is_numeric($_POST['quoted_cost']) ? $_POST['quoted_cost'] : 0;
    $service_provider_name = $_POST['service_provider_name'] ?? null;
    $project_manager_status = 0;
    $assign_status = 1;
    $assign_to_status = 1;
    $current_date = date("Y-m-d");
    $current_user_id = $_POST['current_user_id'];








    $currentDateTime = new DateTime();

    // Calculate the end date by adding the selected number of days to the current date
    $endDateTime = clone $currentDateTime;
    // $t_end_date = $endDateTime->format("Y-m-d");
    $assign_to_id_for_internal = isset($_POST['assign_to_id_for_internal']) && is_numeric($_POST['assign_to_id_for_internal']) ? $_POST['assign_to_id_for_internal'] : 0;
    $assign_to_id = $_POST['assign_to_id'] ?? 0;
    $subproject_status = $_POST['subproject_status'];

    // Fetch the fullname based on assign_to_id
    $stmt_fullname = $conn->prepare("SELECT fullname FROM tbl_admin WHERE user_id = ?");
    $stmt_fullname->bind_param("s", $assign_to_id_for_internal);
    $stmt_fullname->execute();
    $result_fullname = $stmt_fullname->get_result();
    $fullname = null;

    // Check if the fullname exists
    if ($row = $result_fullname->fetch_assoc()) {
        $fullname = $row['fullname'];
    } else {
        $fullname = ''; // Set as empty if no match
    }
    $stmt_fullname->close();

    // Fetch the p_team based on project_id
    $stmt_team = $conn->prepare("SELECT p_team FROM projects WHERE project_id = ?");
    $stmt_team->bind_param("s", $project_id);
    $stmt_team->execute();
    $result_team = $stmt_team->get_result();
    $p_team = null;

    // Check if the p_team exists
    if ($row = $result_team->fetch_assoc()) {
        $p_team = $row['p_team'];
    } else {
        $p_team = ''; // Set as empty if no match
    }
    $stmt_team->close();

    $stmt_pm = $conn->prepare("SELECT project_manager, project_managers_id FROM projects WHERE project_id = ?");
    $stmt_pm->bind_param("s", $project_id);
    $stmt_pm->execute();
    $result_pm = $stmt_pm->get_result();

    $project_manager = null;
    $project_managers_id = null;

    if ($row = $result_pm->fetch_assoc()) {
        $project_manager = $row['project_manager']; // Assign the fetched project manager name
        $project_managers_id = $row['project_managers_id']; // Assign the fetched project_managers_id
    }

    $stmt_pm->close();





    // Check if project_managers_id is empty
    if (empty($project_managers_id)) {
        echo "<script>
            alert('Project manager not found for this project.\\nUpdate Project Manager.');
            window.history.back();
        </script>";
        exit;
    }

    //comments full name
    $comments_fullname = $conn->prepare("SELECT fullname, user_role FROM tbl_admin WHERE user_id = ?");
    $comments_fullname->bind_param("i", $current_user_id);
    $comments_fullname->execute();
    $result = $comments_fullname->get_result();

    $fullname = null;

    if ($row = $result->fetch_assoc()) {
        $fullname = $row['fullname'];
        $user_role = $row['user_role'];
    }

    $comments_fullname->close();
    
    // Fetch the project_manager from projects based on project_id

    // Fetch the contact_id based on project_id
    $stmt_contact = $conn->prepare("SELECT contact_id FROM projects WHERE project_id = ?");
    $stmt_contact->bind_param("s", $project_id);
    $stmt_contact->execute();
    $result_contact = $stmt_contact->get_result();
    $contact_id = null;

    // Check if the contact_id exists
    if ($row = $result_contact->fetch_assoc()) {
        $contact_id = $row['contact_id'];
    } else {
        $contact_id = ''; // Set as empty if no match
    }

    $stmt_contact->close();


    $query_comments = "SELECT comments FROM subprojects WHERE project_id = ? AND subproject_status=?";
    $stmt_comments = $conn->prepare($query_comments);
    $stmt_comments->bind_param("is", $project_id, $subproject_status);
    $stmt_comments->execute();
    $result_comments = $stmt_comments->get_result();
    
    $existing_comments = "";
    if ($row = $result_comments->fetch_assoc()) {
        $existing_comments = $row['comments'];
    }
    $stmt_comments->close();
    
    // Only update comments if a new comment exists
    if (!empty($comments)) {
        $new_comment = $current_date . " --- " . $comments . " --- " . $fullname . "-" . $user_role . "\n\n";
        
        if (!empty($existing_comments)) {
            $comments = $new_comment . "\n" . $existing_comments;
        } else {
            $comments = $new_comment;
        }
    } else {
        $comments = $existing_comments; // Keep existing comments unchanged
    }
    
    // exit;
    // Ensure $assign_to variable is assigned properly before passing it to bind_param.
    $assign_to = isset($assign_to_id_for_internal) ? $assign_to_id_for_internal : $assign_to_id; // Use one of the two values



    $query = "UPDATE subprojects 
    SET subproject_name = ?, subproject_details = ?, quoted_cost = ?, comments = ?,assign_status = CASE WHEN assign_status IS NULL THEN ? ELSE assign_status END,        project_manager_status = CASE WHEN project_manager_status IS NULL THEN ? ELSE project_manager_status END, 
 sub_EPT = ?, assign_to_id = ?, assign_to = ?, subproject_status = ?, outsourced = ?, 
    start_date = CASE WHEN start_date IS NULL THEN ? ELSE start_date END, 
    sub_end_date = ?, 
    p_team = ?, urgency = ?, project_manager = ?, contact_id = ?,assign_to_status = CASE 
    WHEN assign_to IS NOT NULL AND assign_to <> ? THEN ? 
    ELSE assign_to_status 
    END,project_managers_id=?

    WHERE project_id = ? AND subproject_status = ?";


    // Stop execution to inspect the output before SQL execution



    if ($stmt = $conn->prepare($query)) {
        // Bind parameters (ensure each '?' has a corresponding variable)
        $stmt->bind_param("ssssssssssssssssssssis", $subproject_name, $subproject_details, $Quoted, $comments, $assign_status, $project_manager_status, $sub_EPT, $assign_to, $fullname, $subproject_status, $outsource, $t_start_date, $t_end_date, $p_team, $urgency, $project_manager, $contact_id, $assign_to_status, $assign_to_status, $project_managers_id, $project_id, $subproject_status);

        // Execute the query
        if ($stmt->execute()) {
            echo "<script>window.location.href='viewPort.php?message=success';</script>";
            $stmt->close();
        } else {
            echo "Error updating subproject: " . $stmt->error;
            echo "<script>location.reload();</script>"; // Reload on error

        }
    } else {
        echo "Error preparing the query.";
    }
}


// Check if the delete button is clicked via GET method
if (isset($_GET['delete_project'])) {
    require '../conn.php'; // Ensure the database connection is included

    // ✅ Fetch `project_id` and `subproject_status` safely
    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
    $subproject_status = isset($_GET['subproject_status']) ? trim($_GET['subproject_status']) : '';

    // ✅ Debugging: Check values before executing the query
    if ($project_id === 0 || empty($subproject_status)) {
        die("Error: Invalid project ID or subproject status.");
    }

    // ✅ Debugging: Log values for testing
    error_log("Deleting subproject: Project ID = $project_id, Status = $subproject_status");

    // ✅ Fix: Correct SQL query (Only use `project_id` and `subproject_status`)
    $deleteQuery = "DELETE FROM subprojects WHERE project_id = ? AND subproject_status = ?";

    if ($stmt = $conn->prepare($deleteQuery)) {
        // ✅ Fix: Correct parameter binding
        $stmt->bind_param("is", $project_id, $subproject_status);

        // Execute the query
        if ($stmt->execute()) {
            // ✅ Check if a row was actually deleted
            if ($stmt->affected_rows > 0) {
                // ✅ Success message using SweetAlert
                echo "<script>
                    window.location.href = 'viewPort.php?message=deleted';
                </script>";
            } else {
                echo "Error: No matching subproject found to delete.";
            }
        } else {
            // ✅ Show actual MySQL error
            echo "Error deleting subproject: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Error preparing the delete query: " . $conn->error;
    }

    // Close the DB connection
    $conn->close();
}




// Close the database connection




// Check if the form was submitted
// Include the database connection
include('../conn.php');

// Check if the form is submitted
if (isset($_POST['submitForm'])) {

    // Check if all the required fields are set
    if (isset($_POST['projectId'], $_POST['projectName'], $_POST['projectDetails'], $_POST['urgency'], $_POST['startDate'], $_POST['endDate'], $_POST['comments'])) {

        // Get data from POST request
        $projectId = $_POST['projectId'];
        $projectName = $_POST['projectName'];
        $projectDetails = $_POST['projectDetails'];
        $urgency = $_POST['urgency'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        // $comments = $_POST['comments'];
        $project_manager = $_POST['project_manager_id'];
        $engineer_id = $_POST['engineer_id'];
        $p_team = $_POST['p_team'];
        $current_date = date("Y-m-d");
        $current_user_id = $_POST['user_id'];


        // Fetch engineer's full name based on engineer_id (user_id)
        $engineer_fullname = "";
        $query_engineer = "SELECT fullname FROM tbl_admin WHERE user_id = ?";
        $stmt_engineer = $conn->prepare($query_engineer);
        $stmt_engineer->bind_param("i", $engineer_id);
        $stmt_engineer->execute();
        $result_engineer = $stmt_engineer->get_result();
        if ($row = $result_engineer->fetch_assoc()) {
            $engineer_fullname = $row['fullname'];
        }
        $stmt_engineer->close();

        // Fetch project manager's full name and role
        $project_manager_fullname = "";
        $user_role = ""; // Default empty role
        $query_pm = "SELECT fullname FROM tbl_admin WHERE user_id = ?";
        $stmt_pm = $conn->prepare($query_pm);
        $stmt_pm->bind_param("i", $project_manager); // Fixed: Now binding only one parameter
        $stmt_pm->execute();
        $result_pm = $stmt_pm->get_result();
        if ($row = $result_pm->fetch_assoc()) {
            $project_manager_fullname = $row['fullname'];
        }
        $stmt_pm->close();

        $fullname = "";
        $query_fullname = "SELECT fullname, user_role FROM tbl_admin WHERE user_id = ?";
        $stmt_fullname = $conn->prepare($query_fullname);
        $stmt_fullname->bind_param("i", $current_user_id); // Fixed: Now binding only one parameter
        $stmt_fullname->execute();
        $result_fullname = $stmt_fullname->get_result();
        if ($row = $result_fullname->fetch_assoc()) {  // ✅ Use correct result set
            $fullname = $row['fullname'];
            $user_role = $row['user_role'] ?? "Unknown"; // Handle NULL value safely
        }
        $stmt_fullname->close();

        echo $user_role;
        // exit;    



        // Sanitize the inputs to avoid SQL injection
        $projectId = mysqli_real_escape_string($conn, $projectId);
        $projectName = mysqli_real_escape_string($conn, $projectName);
        $projectDetails = mysqli_real_escape_string($conn, $projectDetails);
        $urgency = mysqli_real_escape_string($conn, $urgency);
        $startDate = mysqli_real_escape_string($conn, $startDate);
        $endDate = mysqli_real_escape_string($conn, $endDate);
        // $comments = mysqli_real_escape_string($conn, $comments);
        $project_manager = mysqli_real_escape_string($conn, $project_manager);
        $engineer = mysqli_real_escape_string($conn, $engineer_id);
        $team = mysqli_real_escape_string($conn, $p_team);

        // Fetch existing comments
        $query_comments = "SELECT comments FROM projects WHERE project_id = ?";
        $stmt_comments = $conn->prepare($query_comments);
        $stmt_comments->bind_param("i", $projectId);
        $stmt_comments->execute();
        $result_comments = $stmt_comments->get_result();

        $existing_comments = "";
        if ($row = $result_comments->fetch_assoc()) {
            $existing_comments = $row['comments'];
        }
        $stmt_comments->close();

        // Prepend new comment to existing comments
        $new_comment = $current_date . " --- " . trim($_POST['comments']) . " --- " . $fullname . "-" . $user_role . "\n\n";
        $comments = $new_comment . $existing_comments; // New comment first, then existing comments

        // ✅ Start a transaction to ensure both queries execute successfully
        mysqli_begin_transaction($conn);


        try {
            // ✅ Update `projects` table
            $query1 = "UPDATE projects
            SET project_name = ?, project_details = ?, urgency = ?, start_date = ?, end_date = ?, project_managers_id=?, assign_to_id=?, assign_to=?, p_team=?, project_manager = ?
            WHERE project_id = ?";

            $stmt1 = $conn->prepare($query1);
            $stmt1->bind_param("ssssssssssi", $projectName, $projectDetails, $urgency, $startDate, $endDate, $project_manager, $engineer_id, $engineer_fullname, $team, $project_manager_fullname, $projectId);
            $stmt1->execute();



            // ✅ If urgency is "purple", update `subprojects` status
            if ($urgency === "purple") {
                $query2 = "UPDATE subprojects SET urgency = 'purple' WHERE project_id = ?";
                $stmt2 = $conn->prepare($query2);
                $stmt2->bind_param("i", $projectId);
                $stmt2->execute();
            }

            // ✅ Commit transaction
            mysqli_commit($conn);

            // Redirect to a success page or show a success message
            echo "<script>alert('Project details updated successfully.'); window.location.href='viewPort.php?message=success';</script>";
        } catch (Exception $e) {
            // ❌ Rollback in case of error
            mysqli_rollback($conn);
            echo "Error updating project: " . $conn->error;
        }
    } else {
        echo "Required fields are missing. Please check the form.";
    }
}


// Check if form is submitted




// Check if form is submitted
if (isset($_POST["assign_engineer"])) {
    // Include database connection
    include('../conn.php');

    // Get values from the form and sanitize input
    $project_id = $_POST['project_id_t'] ?? '';
    $subproject_status = $_POST['subproject_status'] ?? '';

    // Convert project_id to integer
    if (!is_numeric($project_id)) {
        die("<p style='color:red;'>Invalid project ID.</p>");
    }
    $project_id = intval($project_id);

    // Validate input data
    if (!empty($project_id) && !empty($subproject_status)) {
        // 🔹 Debugging: Output received values
        error_log("Received Project ID: $project_id, Subproject Status: $subproject_status");

        // 🔹 Step 1: Check if the row exists **before updating**
        $check_query = "SELECT assign_status FROM subprojects WHERE project_id = ? AND subproject_status = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("is", $project_id, $subproject_status);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            die("<p style='color:red;'>No matching subproject found. Check project ID and status.</p>");
        }

        // Fetch current status
        $row = $result->fetch_assoc();
        $current_status = $row['assign_status'];
        $check_stmt->close();

        // 🔹 Step 2: Ensure update will execute (force update even if values are the same)
        $sql_update = "UPDATE subprojects 
                       SET assign_status = 1, project_manager_status = 0 
                       WHERE project_id = ? AND subproject_status = ?";

        if ($stmt = $conn->prepare($sql_update)) {
            $stmt->bind_param("is", $project_id, $subproject_status);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>
                    alert('Engineer assigned successfully.');
                    window.location.href='viewPort.php';
                </script>";
                exit();
            } else {
                // 🔹 If no rows were updated, check if the values were already set
                if ($current_status == 1) {
                    echo "<p style='color:orange;'>Engineer is already assigned.</p>";
                } else {
                    echo "<p style='color:red;'>Update failed. Please check your input.</p>";
                }
            }

            $stmt->close();
        } else {
            echo "<p style='color:red;'>Error preparing update statement: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Error: Missing project ID or subproject status.</p>";
    }
}



if (isset($_POST["update_followup"])) {
    $project_id = $_POST['project_id'] ?? null;
    $new_comment = trim($_POST['followup_comments'] ?? '');
    $manager_id = $_POST['manager_id'] ?? null;
    $timestamp = date('Y-m-d'); // Current date & time

    // Fetch Manager Full Name and Role
    $query = "SELECT fullname, user_role FROM tbl_admin WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $manager_id);
    $stmt->execute();
    $stmt->bind_result($fullname, $user_role);
    $stmt->fetch();
    $stmt->close();

    if (!empty($project_id) && !empty($new_comment)) {
        // Get existing comments
        $query = "SELECT comments FROM projects WHERE project_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $stmt->bind_result($existing_comments);
        $stmt->fetch();
        $stmt->close();

        // Format new comment
        $formatted_comment = "{$timestamp} --- {$new_comment} --- {$fullname} - {$user_role}\n\n";

        // Append to existing comments
        $updated_comments = !empty($existing_comments) ? $formatted_comment . $existing_comments : $formatted_comment;

        // Update the database
        $update_query = "UPDATE projects SET comments = ? WHERE project_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $updated_comments, $project_id);

        if ($update_stmt->execute()) {
            header("Location: viewPort.php?success=1");
        } else {
            echo "<script>alert('Error updating follow-up.'); history.back();</script>";
        }

        $update_stmt->close();
    } else {
        header("Location: viewPort.php?error=1");
    }

    $conn->close();
}


<?php

require 'authentication.php';

include 'include/login_header.php';

// Check if the 'project_id' is set in the URL

if (isset($_GET['project_id'])) {

    $project_id = $_GET['project_id'];

    // Replace with your database connection code

    include 'conn.php';



    // Get the project data from the 'projects' table

    $sql_select = "SELECT * FROM deliverable_data WHERE project_id = $project_id";

    $result = $conn->query($sql_select);



    if ($result->num_rows > 0) {

        // Fetch the project data

        $row = $result->fetch_assoc();



        // Insert the project data into the 'deliverable_data' table

        // Extract the numeric part of "reopen_status" and increment it

        $numericPart = intval(substr($row['reopen_status'], 1)); // Assuming "reopen_status" is in the format "R2"

        $newNumericPart = $numericPart + 1;



        // Create the new "reopen_status" value with the incremented numeric part

        $newReopenStatus = 'R' . $newNumericPart;



        $sql_insert = "INSERT INTO projects (

    project_id,

    project_name,

    project_details,

    project_manager,

    EPT,

    assign_to_id,

    assign_to,

    urgency,

    verify_by,

    verify_by_name,

    verify_status,

    project_manager_status,

    end_date,

    p_team,

    progress,

    start_date,

    project_managers_id,

    reopen_status,

    contact_id

) VALUES (

    '" . $row['project_id'] . "',

    '" . $row['project_name'] . "',

    '" . $row['project_details'] . "',

    '" . $row['project_manager'] . "',

    '" . $row['EPT'] . "',

    '" . $row['assign_to_id'] . "',

    '" . $row['assign_to'] . "',

    '" . $row['urgency'] . "',

    '" . $row['verify_by'] . "',

    '" . $row['verify_by_name'] . "',

    '" . $row['verify_status'] . "',

    '1',

    '" . $row['end_date'] . "',

    '" . $row['p_team'] . "',

    '" . $row['progress'] . "',

    '" . $row['start_date'] . "',

    '" . $row['project_managers_id'] . "',

    '" . $newReopenStatus . "',

    '" . $row['contact_id'] . "'

)";


        if ($conn->query($sql_insert) === TRUE) {
            // Project data successfully inserted into 'deliverable_data' table
            // Delete the project from the 'projects' table
            $sql_delete = "DELETE FROM deliverable_data WHERE project_id = $project_id";
            if ($conn->query($sql_delete) === TRUE) {

                // Project successfully deleted from 'projects' table

                $msg_success = "Project Sent to Rework ";

                header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;

            } else {

                $msg_error = "Error deleting project from projects table: " . $conn->error;

            }

        } else {

            $msg_error = "Error inserting project data into deliverable_data table: " . $conn->error;

        }

    } else {

        $msg_error = "Project not found in projects table.";

    }



    $conn->close();

} else {

    $msg_error = "No project ID in the URL.";

}

?>

<?php include 'include/footer.php'  ?>
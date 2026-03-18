<?php
session_start();

include 'conn.php';

// Check if the 'project_id' is set in the URL
if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update the project status in the deliverable_data table
        $currentDate = new DateTime();
    
        $completionDate = $currentDate->format('Y-m-d');
        $project_status = 'purple';
        $sql_update = "UPDATE projects SET urgency = ?, projectClosed = ? WHERE project_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $project_status,  $completionDate, $project_id);

        if (!$stmt_update->execute()) {
            throw new Exception("Error updating project status: " . $stmt_update->error);
        }

        

        

        // Commit the transaction if all operations are successful
        $conn->commit();

        // Success message
        $msg_success = "Project moved successfully to Deliverables and closed.";
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=' . urlencode($msg_success));
        exit();

    } catch (Exception $e) {
        // Rollback the transaction if any operation fails
        $conn->rollback();
        $msg_error = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=' . urlencode($msg_error));
        exit();
    }
} else {
    // If 'project_id' is not set, redirect with an error
    $msg_error = "Project ID not specified.";
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=' . urlencode($msg_error));
    exit();
}

$conn->close(); // Close database connection

?>

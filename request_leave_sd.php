<?php
require 'conn.php';

// Debugging: Print GET parameters
if (!isset($_GET['id']) || !isset($_GET['leave_approval']) || !isset($_GET['role'])) {
    echo "Missing parameters.";
    print_r($_GET);
    exit;
}

// Sanitize inputs
$request_id = intval($_GET['id']);
$action = $_GET['leave_approval'];
$role = $_GET['role'];

// Check for valid action
if ($action !== 'approve' && $action !== 'deny') {
    echo "Invalid action.";
    exit;
}

// Set the status based on action
$status = ($action === 'approve') ? 'Approved' : 'Denied';

// Determine which column to update
if ($role === 'Pre-Manager') {
    $sql = "UPDATE leave_approval SET cc_manager_status = ? WHERE leave_id = ?";
} elseif ($role === 'Manager') {
    $sql = "UPDATE leave_approval SET approved = ? WHERE leave_id = ?";
} else {
    echo "Invalid role.";
    exit;
}

// Execute SQL Update
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("si", $status, $request_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo '
        <div style="max-width: 500px; margin: 50px auto; padding: 30px; background: #e6ffed; border: 1px solid #b6f0c0; border-radius: 10px; font-family: Arial, sans-serif; text-align: center;">
            <h3 style="color: #2e7d32;">✅ Leave request updated successfully</h3>
            <p>You can now close this window.</p>
            <button onclick="window.close()" style="padding: 8px 16px; background-color: #2e7d32; color: white; border: none; border-radius: 5px; cursor: pointer;">Close Window</button>
        </div>';
    } else {
        echo '
        <div style="max-width: 500px; margin: 50px auto; padding: 30px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 10px; font-family: Arial, sans-serif; text-align: center;">
            <h3 style="color: #856404;">⚠️ No changes made</h3>
            <p>Maybe the request was already processed.</p>
        </div>';
    }
    
} else {
    echo "SQL Error: " . $conn->error;
}
?>

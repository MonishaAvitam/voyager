<?php
require '../conn.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
        $table = isset($_POST['table']) ? $conn->real_escape_string($_POST['table']) : '';
        $received_date = isset($_POST['received_date']) && !empty($_POST['received_date']) ? 
                        $conn->real_escape_string($_POST['received_date']) : NULL;
        $booked_date = isset($_POST['booked_date']) && !empty($_POST['booked_date']) ? 
                        $conn->real_escape_string($_POST['booked_date']) : NULL;
        
        // Validate data
        if ($record_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
            exit;
        }
        
        // Determine which table to update
        $table_name = '';
        switch ($table) {
            case 'paid':
                $table_name = 'paidinvoices';
                break;
            case 'ready_to_pay':
                $table_name = 'ready_to_pay';
                break;
            case 'unpaid':
                $table_name = 'unpaidinvoices';
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid table name']);
                exit;
        }
        
        // Prepare SQL statement based on which date is being updated
        if ($received_date !== NULL && $booked_date === NULL) {
            $sql = "UPDATE $table_name SET received_date = ? WHERE project_id = ?";
            $params = [$received_date, $record_id];
            $types = "si";
        } elseif ($booked_date !== NULL && $received_date === NULL) {
            $sql = "UPDATE $table_name SET booked_date = ? WHERE project_id = ?";
            $params = [$booked_date, $record_id];
            $types = "si";
        } else {
            echo json_encode(['success' => false, 'message' => 'Please update only one date at a time']);
            exit;
        }
        
        // Execute the query
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            // Bind parameters
            $stmt->bind_param($types, ...$params);
            
            $result = $stmt->execute();
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Date updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update date: ' . $stmt->error]);
            }
            
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>


<!--add_project_action_record>


<?php
    


    require '../conn.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $project_id = $_POST['project_id'];
        $record = $_POST['record'];
    
        if (empty($project_id) || empty($record)) {
            echo "Error: Missing project ID or record.";
            exit;
        }
    
        // Calculate the next record number within this project
        $checkSql = "SELECT COALESCE(MAX(record_number), 0) + 1 AS next_record FROM potential_project_record WHERE project_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $next_record = $result['next_record'];
    
        // Insert the new record with a sequential number
        $insertSql = "INSERT INTO potential_project_record (project_id, record_number, record) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("iis", $project_id, $next_record, $record);
    
        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error: " . $conn->error;
        }
    }











    
    ?>
    
    








?>

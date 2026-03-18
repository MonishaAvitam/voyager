<?php
require '../authentication.php';
require '../conn.php';

// Adding a new row or updating an existing one
if (isset($_POST['add_row'])) {
    $projectId = $_GET['project_id'];
    $date = date('Y-m-d H:i:s');
    $lastModifiedDate = date('Y-m-d H:i:s');
    $mainprojectrowNumber = 0;
    $rowNumber = isset($_GET['saverownumber']) ? $_GET['saverownumber'] : null;

    // Check if project exists in 'uninvoiced' table
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM csa_finance_uninvoiced WHERE project_id = ?");
    $checkStmt->bind_param("s", $projectId);
    $checkStmt->execute();
    $checkStmt->bind_result($projectExists);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($projectExists == 0) {
        // Insert new project if it doesn't exist in the uninvoiced table
        $insertStmt = $conn->prepare("INSERT INTO csa_finance_uninvoiced (project_id, date, last_modified_date, rownumber) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("sssi", $projectId, $date, $lastModifiedDate, $mainprojectrowNumber);

        if ($insertStmt->execute()) {
            echo "New project inserted with row number 0!";
        } else {
            echo "Error: " . $insertStmt->error;
        }
        $insertStmt->close();
    }

    // If rowNumber is provided, update the existing record
    if ($rowNumber !== null && $rowNumber !== '') {

        $updateStmt = $conn->prepare("UPDATE csa_finance_uninvoiced SET last_modified_date = ? WHERE project_id = ? AND rownumber = ?");
        $updateStmt->bind_param("ssi", $lastModifiedDate, $projectId, $rowNumber);

        if ($updateStmt->execute()) {
            echo ($updateStmt->affected_rows > 0) ? "Record updated successfully!" : "No matching record found to update.";
        } else {
            echo "Error: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        $rowNumberStmt = $conn->prepare("
    SELECT MAX(rownumber) 
    FROM (
        SELECT rownumber FROM csa_finance_uninvoiced WHERE project_id = ?
        UNION ALL
        SELECT rownumber FROM csa_finance_readytobeinvoiced WHERE project_id = ?
    ) AS combined_rownumbers
");
        $rowNumberStmt->bind_param("ss", $projectId, $projectId);
        $rowNumberStmt->execute();
        $rowNumberStmt->bind_result($maxRowNumber);
        $rowNumberStmt->fetch();
        $rowNumberStmt->close();

        // Set the new row number to be one higher than the maximum row number found.
        $newRowNumber = ($maxRowNumber === null) ? 1 : (int)$maxRowNumber + 1;

        // Insert the new row with the computed row number into 'csa_finance_uninvoiced'.
        $insertStmt = $conn->prepare("INSERT INTO csa_finance_uninvoiced (project_id, date, last_modified_date, rownumber) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $projectId, $date, $lastModifiedDate, $newRowNumber);

        // Execute the insert statement and check if it was successful.
        if ($insertStmt->execute()) {
            echo "<script>alert('New row inserted with row number!');window.location.href = 'unInvoiced.php';</script>";
            // echo "<script>alert('New row inserted with row number $newRowNumber!');window.location.href = 'unInvoiced.php';</script>";
        } else {
            echo "Error: " . $insertStmt->error;
        }
        $insertStmt->close();
    }
}

if (isset($_POST['selected_projects']) && is_array($_POST['selected_projects'])) {
    $selectedProjects = $_POST['selected_projects'];
    $prices = $_POST['prices'] ?? [];
    $comments = $_POST['comments'] ?? [];
    $service_dates = $_POST['service_dates'] ?? []; // Ensure key matches form input name for service dates
    $due_dates = $_POST['due_dates'] ?? []; // Ensure key matches form input name for due dates

    // echo '<pre>';
    // print_r($_POST); // Debugging: Print all POST data for review
    // echo '</pre>';

    if (count($selectedProjects) > 0) {
        $conn->begin_transaction();
        try {
            // Loop through each selected project
            foreach ($selectedProjects as $index => $projectValue) {
                $projectParts = explode(',', $projectValue);

                if (count($projectParts) === 2) {
                    $projectId = $projectParts[0];
                    $rownumber = $projectParts[1];

                    // Retrieve the price, comment, service_date, and due_date for the current index
                    $price = $prices[$index] ?? 0;
                    $comment = $comments[$index] ?? 'No comments provided';
                    $service_date = $service_dates[$index] ?? '2024-01-01';
                    $due_date = $due_dates[$index] ?? '2024-01-01';

                    // echo "Selected Project ID: " . htmlspecialchars($projectId) . " with Row Number: " . htmlspecialchars($rownumber) .
                    //      " Price: " . htmlspecialchars($price) . " Comments: " . htmlspecialchars($comment) .
                    //      " Service Date: " . htmlspecialchars($service_date) . " Due Date: " . htmlspecialchars($due_date) . "<br>";

                    // Check if the project already exists in `csa_finance_uninvoiced`
                    $checkStmt = $conn->prepare("SELECT project_id, rownumber FROM csa_finance_uninvoiced WHERE project_id = ? AND rownumber = ?");
                    $checkStmt->bind_param('ss', $projectId, $rownumber);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result();

                    // Default project status
                    $project_status = 'MovedToReadyToBeInvoicedTable';

                    if ($result->num_rows > 0) {
                        // Update existing project in `csa_finance_uninvoiced`
                        $updatestmt = $conn->prepare("UPDATE csa_finance_uninvoiced SET project_status = ?, price = ?, comments = ?, service_date = ?, due_date = ? WHERE project_id = ? AND rownumber = ?");
                        $updatestmt->bind_param('sssssss', $project_status, $price, $comment, $service_date, $due_date, $projectId, $rownumber);
                        $updatestmt->execute();

                        // Check and update or insert in `csa_finance_readytobeinvoiced`
                        $checkStmt2 = $conn->prepare("SELECT project_id, rownumber FROM csa_finance_readytobeinvoiced WHERE project_id = ? AND rownumber = ?");
                        $checkStmt2->bind_param('ss', $projectId, $rownumber);
                        $checkStmt2->execute();
                        $result2 = $checkStmt2->get_result();

                        if ($result2->num_rows > 0) {
                            $updatestmt2 = $conn->prepare("UPDATE csa_finance_readytobeinvoiced SET price = ?, comments = ?, service_date = ?, due_date = ? WHERE project_id = ? AND rownumber = ?");
                            $updatestmt2->bind_param('ssssss', $price, $comment, $service_date, $due_date, $projectId, $rownumber);
                            $updatestmt2->execute();
                        } else {
                            // Prevent duplicate inserts
                            $moveStmt = $conn->prepare("
                                            INSERT INTO csa_finance_readytobeinvoiced (project_id, price, date, comments, service_date, due_date, last_modified_date, rownumber)
                                            SELECT project_id, price, date, comments, service_date, due_date, last_modified_date, rownumber
                                            FROM csa_finance_uninvoiced
                                            WHERE project_id = ? AND rownumber = ?
                                            ON DUPLICATE KEY UPDATE
                                            price = VALUES(price), comments = VALUES(comments), service_date = VALUES(service_date), due_date = VALUES(due_date)
                                        ");
                            $moveStmt->bind_param('ss', $projectId, $rownumber);
                            $moveStmt->execute();
                        }
                    } else {
                        // Insert a new project in `csa_finance_uninvoiced`
                        $insertStmt = $conn->prepare("
                            INSERT INTO csa_finance_uninvoiced (project_id, price, comments, service_date, due_date, rownumber, project_status)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insertStmt->bind_param('sssssss', $projectId, $price, $comment, $service_date, $due_date, $rownumber, $project_status);
                        $insertStmt->execute();
                        $insertStmt->close();

                        // Insert into `csa_finance_readytobeinvoiced`
                        $moveStmt = $conn->prepare("
                            INSERT INTO csa_finance_readytobeinvoiced (project_id, price, date, comments, service_date, due_date, last_modified_date, rownumber)
                            SELECT project_id, price, date, comments, service_date, due_date, last_modified_date, rownumber
                            FROM csa_finance_uninvoiced
                            WHERE project_id = ? AND rownumber = ?
                        ");
                        $moveStmt->bind_param('ss', $projectId, $rownumber);
                        $moveStmt->execute();
                    }
                } else {
                    echo "Malformed project data: " . htmlspecialchars($projectValue) . "<br>";
                }
            }

            // Commit transaction
            $conn->commit();
            echo "<script type='text/javascript'>alert('Projects have been successfully processed!'); window.location.href = 'unInvoiced.php';</script>";
        } catch (Exception $e) {
            $conn->rollback(); // Rollback if any error occurs
            echo "Error occurred: " . $e->getMessage();
        }
    } else {
        echo "No projects were selected.";
    }
}




// Saving project data
if (isset($_POST['save'])) {
    $projectId = $_GET['project_id'];
    $rowNumber = isset($_GET['rownumber']) ? (int)$_GET['rownumber'] : 0;
    $price = $_POST['price'] ?? 0;
    $comments = $_POST['comments'];
    $service_date = !empty($_POST['service_date']) ? $_POST['service_date'] : null;
    $lastModifiedDate = date('Y-m-d H:i:s');

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM csa_finance_uninvoiced WHERE project_id = ? AND rownumber = ?");
    $checkStmt->bind_param("si", $projectId, $rowNumber);
    $checkStmt->execute();
    $checkStmt->bind_result($recordCount);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($recordCount > 0) {
        $updateStmt = $conn->prepare("UPDATE csa_finance_uninvoiced SET price = ?, comments = ?,service_date = ?, last_modified_date = ? WHERE project_id = ? AND rownumber = ?");
        $updateStmt->bind_param("dssssi", $price, $comments, $service_date, $lastModifiedDate, $projectId, $rowNumber);
        if ($updateStmt->execute()) {
            echo ($updateStmt->affected_rows > 0) ? "<script>alert('Project updated successfully!'); window.location.href = 'unInvoiced.php';</script>" : "No changes made.";
        } else {
            echo "Error: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        $insertStmt = $conn->prepare("INSERT INTO csa_finance_uninvoiced (project_id, price, comments,service_date, last_modified_date, rownumber) VALUES (?, ?, ?,?, ?, ?)");
        $insertStmt->bind_param("sssssi", $projectId, $price, $comments, $service_date, $lastModifiedDate, $rowNumber);
        if ($insertStmt->execute()) {
            echo "<script>alert(' Project Updated successfully!'); window.location.href = 'unInvoiced.php';</script>";
        } else {
            echo "Error: " . $insertStmt->error;
        }
        $insertStmt->close();
    }
}






// Deleting a row
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_row'])) {
    $projectId = $_GET['project_id'];
    $rowNumber = $_GET['rownumber'];

    $deleteStmt = $conn->prepare("DELETE FROM csa_finance_uninvoiced WHERE project_id = ? AND rownumber = ?");
    $deleteStmt->bind_param("si", $projectId, $rowNumber);

    if ($deleteStmt->execute()) {
        echo ($deleteStmt->affected_rows > 0) ? "<script>alert('Row deleted successfully!'); window.location.href = 'unInvoiced.php';</script>" : "No matching row found to delete.";
        // echo ($deleteStmt->affected_rows > 0) ? "Row successfully deleted!" : "No matching row found to delete.";
    } else {
        echo "Error: " . $deleteStmt->error;
    }

    $deleteStmt->close();
}



$conn->close();
exit();

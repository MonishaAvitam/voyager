<?php
require '../authentication.php'; // admin authentication check 
require '../conn.php';

if (isset($_POST['invoice'])) {
    // Retrieve the selected project IDs and row numbers
    $projectData = $_POST['movetotargets'];

    // Initialize an array to build sanitized conditions for the query
    $conditions = [];
    $types = ""; // For binding parameters
    $params = []; // For the parameter values

    // Initialize an array for form data that was posted (prices, comments, etc.)
    $projectDetails = [];

    // Loop through the selected project data
    foreach ($projectData as $data) {
        list($project_id, $rownumber) = explode('_', $data);
        $conditions[] = "(ri.project_id = ? AND ri.rownumber = ?)";
        $types .= "ii"; // "ii" represents two integers (project_id, rownumber)
        $params[] = (int)$project_id;
        $params[] = (int)$rownumber;

        // Capture additional form data sent for each project (prices, comments, etc.)
        $projectDetails[$data] = [
            'price' => $_POST["price_{$project_id}_{$rownumber}"] ?? 'N/A',
            'comments' => $_POST["comments_{$project_id}_{$rownumber}"] ?? 'N/A',
            'invoice_number' => $_POST["invoiceNumber_{$project_id}_{$rownumber}"] ?? 'N/A',
            'due_date' => $_POST["dueDate_{$project_id}_{$rownumber}"] ?? 'N/A',
            'service_date' => $_POST["serviceDate_{$project_id}_{$rownumber}"] ?? 'N/A',
        ];
    }

    // Create the query condition
    $whereClause = implode(" OR ", $conditions);

    // Prepare the SELECT query
    $query = "
    SELECT 
        ri.project_id,
        ri.price AS amount,
        ri.comments,
        ri.last_modified_date,
        ri.service_date,
        ri.rownumber,
        ri.due_date,
        ri.invoice_number,
        COALESCE(p.project_name, dd.project_name) AS project_name,
        COALESCE(p.p_team, dd.p_team) AS p_team,
        pc.customer_name AS customer_name_from_projects,
        dc.customer_name AS customer_name_from_deliverables
    FROM 
        csa_finance_readytobeinvoiced ri
    LEFT JOIN 
        projects p ON ri.project_id = p.project_id 
    LEFT JOIN 
        deliverable_data dd ON ri.project_id = dd.project_id 
    LEFT JOIN 
        contacts pc ON p.contact_id = pc.contact_id 
    LEFT JOIN 
        contacts dc ON dd.contact_id = dc.contact_id 
    WHERE 
        $whereClause
    ";

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Prepare the SELECT query
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $project_id = (int)$row['project_id'];
                $rownumber = (int)$row['rownumber'];

                // Get the updated data from the form
                $updatedDetails = $projectDetails["{$project_id}_{$rownumber}"];


                // Insert into invoiced table
                $insertQuery = "
                INSERT INTO csa_finance_invoiced (
                    project_id, invoice_number, comments, amount, project_title, customer_name, p_team, service_date, due_date, rownumber
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $insertStmt = $conn->prepare($insertQuery);

                // Store the values in variables before binding
                $invoice_number = $updatedDetails['invoice_number'];
                $comments = $updatedDetails['comments'];
                $amount = $updatedDetails['price'];
                $project_title = $row['project_name'];
                $customer_name = $row['customer_name_from_projects'] ?? $row['customer_name_from_deliverables'];
                $p_team = $row['p_team'];
                $service_date = $updatedDetails['service_date'];
                $due_date = $updatedDetails['due_date'];

                $insertStmt->bind_param(
                    "issdsssssi",
                    $project_id,
                    $invoice_number,
                    $comments,
                    $amount,
                    $project_title,
                    $customer_name,
                    $p_team,
                    $service_date,
                    $due_date,
                    $rownumber
                );
                $insertStmt->execute();


                $project_statusRI = 'Invoiced';
                // Update project status to 'Invoiced'
                $updateQuery = "
                UPDATE csa_finance_readytobeinvoiced
                SET project_status = ? , comments = ?, invoice_number = ?, price = ?, service_date = ?, due_date = ?
                WHERE project_id = ? AND rownumber = ?
            ";
            
            $updateStmt = $conn->prepare($updateQuery);
            
            // Make sure the parameters match the query placeholders
            $updateStmt->bind_param("ssssssii", $project_statusRI, $comments, $invoice_number, $amount, $service_date, $due_date, $project_id, $rownumber);
            $updateStmt->execute();
            
            }
        }

        // Commit the transaction
        $conn->commit();

        echo json_encode(["success" => true]);
        header('location:readyToBeInvoiced.php');
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}




if (isset($_POST['paymentStatus'])) {
   echo  $project_id = $_POST['project_id'];
   echo  $rownumber = isset($_POST['rownumber'])?$_POST['rownumber']:NULL;
   echo $Status = $_POST['Status'];

  

    $query = "UPDATE csa_finance_invoiced 
              SET payment_status = ? 
              WHERE project_id = ? AND rownumber <=> ?"; // Use NULL-safe operator for comparison

    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters. Use "sii" or "siis" depending on NULL
        $stmt->bind_param("sii", $Status, $project_id, $rownumber);

        if ($stmt->execute()) {
            echo "Payment status updated successfully for Project ID: $project_id, Row: " . ($rownumber ?? "NULL") . ".";
        } else {
            echo "Error updating payment status for Project ID: $project_id, Row: " . ($rownumber ?? "NULL") . ".";
        }

        $stmt->close();
        header('Location: invoiced.php');
        exit();
    } else {
        echo "Error preparing the SQL query.";
    }
}





if (isset($_POST['save'])) {
    // Debugging: Check incoming data
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    // Validate and get POST data
    $project_id = isset($_POST['project_id']) && !empty($_POST['project_id']) ? $_POST['project_id'] : null;
    $price = isset($_POST['price']) && !empty($_POST['price']) ? $_POST['price'] : 0;
    $comments = isset($_POST['comments']) && !empty($_POST['comments']) ? $_POST['comments'] : "";
    $service_date = isset($_POST['service_date']) && !empty($_POST['service_date']) ? $_POST['service_date'] : NULL;
    $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
    $rownumber = isset($_POST['rownumber']) && $_POST['rownumber'] !== '' ? $_POST['rownumber'] : NULL;
    $invoice_number = isset($_POST['invoice_number']) && $_POST['invoice_number'] !== '' ? $_POST['invoice_number'] : NULL;

    // Capture the rownumber from the form, ensure it's not empty or set to NULL
    if ($rownumber === null || $rownumber === '') {
        $rownumber = NULL;
    }

    // Current date and time
    $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp);

    // SQL query
    if ($rownumber !== NULL) {
        $sql = "UPDATE csa_finance_readytobeinvoiced SET 
                price = ?, 
                comments = ?, 
                service_date = ?, 
                due_date = ?, 
                invoice_number = ?, 
                last_modified_date = ? 
                WHERE project_id = ? AND rownumber = ?";

        // Prepare the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssssssi", $price, $comments, $service_date, $due_date, $invoice_number, $date, $project_id, $rownumber);
    } else {
        $sql = "UPDATE csa_finance_readytobeinvoiced SET 
                price = ?, 
                comments = ?, 
                service_date = ?, 
                due_date = ?, 
                invoice_number = ?, 
                last_modified_date = ? 
                WHERE project_id = ? AND (rownumber IS NULL OR rownumber = '')";
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssssss", $price, $comments, $service_date, $due_date, $invoice_number, $date, $project_id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Record updated successfully"));
    } else {
        echo json_encode(array("success" => false, "message" => "Error updating record: " . $conn->error));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to the previous page
    header('Location:' . $_SERVER['HTTP_REFERER']);
    exit;
}





if (isset($_POST['save_row'])) {
    // Validate and get POST data
    $project_id = $_POST['project_id'];  // Get the project ID
    $rownumber = $_POST['rownumber'];    // Get the row number to identify the specific row
    $price = $_POST['price'];            // Get the price
    $comments = $_POST['comments'];      // Get the comments
    $service_date = $_POST['service_date'];  // Get the service date
    $invoice_number = $_POST['invoice_number'];  // Get the service date
    $due_date = $_POST['due_date'] ?? NULL;      // Get the due date
    $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp); // Get the current timestamp

    // Debugging: Log the incoming data to ensure they are received correctly
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    // Validate required fields
    if (empty($project_id) || empty($rownumber) || empty($price) || empty($comments) || empty($service_date) || empty($invoice_number) || empty($due_date)) {
        echo json_encode(array("success" => false, "message" => "All fields must be filled out"));
        exit;
    }

    // SQL to update the record using project_id and rownumber to identify the specific row
    $sql = "UPDATE csa_finance_readytobeinvoiced SET 
            price = ?, 
            comments = ?, 
            service_date = ?, 
            invoice_number = ?, 
            due_date = ?, 
            last_modified_date = ? 
            WHERE project_id = ? AND rownumber = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters: 1 float (price), 4 strings (comments, service_date, due_date, last_modified_date), and 2 integers (project_id, rownumber)
        $stmt->bind_param("dsssssii", $price, $comments, $service_date, $invoice_number, $due_date, $date, $project_id, $rownumber);

        // Execute the statement
        if ($stmt->execute()) {
            // Success response
            echo json_encode(array("success" => true, "message" => "Record updated successfully"));
        } else {
            // Error response if execution fails
            echo json_encode(array("success" => false, "message" => "Error updating record: " . $stmt->error));
        }

        // Close the statement
        $stmt->close();
    } else {
        // Error response if preparing the SQL statement fails
        echo json_encode(array("success" => false, "message" => "Error preparing SQL statement: " . $conn->error));
    }

    // Close the connection
    $conn->close();

    // Redirect to the same page (or a different one if required)
    header('Location:' . $_SERVER['HTTP_REFERER']);
    exit;
}




if (isset($_POST['add_row'])) {
    // Set timestamp and date
    $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp);

    // Retrieve project_id and last_rownumber from POST data
    $project_id = $_POST["project_id"];
    $last_rownumber = isset($_POST["last_rownumber"]) ? $_POST["last_rownumber"] : null;

    // Prepare query to find the maximum rownumber from both tables
    $sql = "
                SELECT MAX(rownumber) AS max_row
                FROM (
                    SELECT rownumber FROM csa_finance_readytobeinvoiced WHERE project_id = ?
                    UNION ALL
                    SELECT rownumber FROM csa_finance_uninvoiced WHERE project_id = ?
                ) AS combined
            ";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Error preparing statement: ' . $conn->error);
    }
    $stmt->bind_param("ii", $project_id, $project_id);

    // Execute and fetch the maximum row number
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $max_row = $row['max_row'];

        // Set initial rownumber based on the max_row
        $rownumber = $max_row ? $max_row + 1 : 1;

        // If last_rownumber is provided and greater than max_row, use it
        if ($last_rownumber !== null && $last_rownumber > $max_row) {
            $rownumber = $last_rownumber + 1;
        }

        // Insert the new row into the `csa_finance_readytobeinvoiced` table
        $sql = "INSERT INTO csa_finance_readytobeinvoiced (project_id, rownumber) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Error preparing insert statement: ' . $conn->error);
        }
        $stmt->bind_param("ii", $project_id, $rownumber);

        // Execute insert statement
        if ($stmt->execute()) {
            $_SESSION['message'] = array("success" => true, "text" => "Row added successfully!");
        } else {
            $_SESSION['message'] = array("success" => false, "text" => "Error inserting record: " . $conn->error);
        }
    } else {
        $_SESSION['message'] = array("success" => false, "text" => "Error executing query: " . $conn->error);
    }

    // Close statement and redirect
    $stmt->close();
    $conn->close();
    header('Location:' . $_SERVER['HTTP_REFERER']);
}




if (isset($_POST['add_row_subproject'])) {

    $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp);


    // Prepare an SQL statement to update the record
    $sql = "INSERT INTO csa_finance_readytobeinvoiced 
                (project_id, rownumber, subproject_status, subproject_count)
                VALUES (?, ?, ?, ?)";

    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $project_id, $rownumber, $subproject_status, $subproject_count);

    $project_id = $_POST["project_id"];
    $subproject_status = $_POST["subproject_status"];
    $subproject_count = $_POST["subproject_count"];
    $rownumber = 1;

    // Execute the statement
    if ($stmt->execute()) {
        // Return success message
        echo json_encode(array("success" => true));
    } else {
        // Return error message
        echo json_encode(array("success" => false, "message" => "Error updating record: " . $conn->error));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
    header('Location:' . $_SERVER['HTTP_REFERER']);
}


if (isset($_POST['delete_row'])) {

    $timestamp = time();
    $date = date('Y-m-d H:i:s', $timestamp);

    // Prepare an SQL statement to update the record
    $sql = "DELETE FROM csa_finance_readytobeinvoiced WHERE id =?";


    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);


    // Set parameters from the POST data

    $id = $_POST["id"];


    // Execute the statement
    if ($stmt->execute()) {
        // Return success message
        $_SESSION['message'] = array("success" => true, "text" => "Row added removed successfully!");
    } else {
        // Return error message
        echo json_encode(array("success" => false, "message" => "Error updating record: " . $conn->error));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
    header('Location:' . $_SERVER['HTTP_REFERER']);
}

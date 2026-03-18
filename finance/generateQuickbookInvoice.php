<?php
require '../authentication.php';
require '../conn.php';

// Ensure that the database connection exists
if (!isset($conn)) {
    die("Database connection not established. Please check conn.php.");
}

// Existing code to fetch selected projects and process them
if (isset($_POST['movetotargets']) && !empty($_POST['movetotargets'])) {
    // Retrieve the selected project IDs and row numbers
    $projectData = $_POST['movetotargets'];
    $service_date = $_POST['service_date'];  // Associative array from the form
    $due_date = $_POST['due_date'];
    $invoiceNumber = $_POST['invoiceNumber'] ?? null;

    // Build a sanitized list of `(project_id, rownumber)` pairs for the query
    $conditions = [];
    foreach ($projectData as $data) {
        list($project_id, $rownumber) = explode('_', $data);
        $conditions[] = "(project_id = " . (int)$project_id . " AND rownumber = " . (int)$rownumber . ")";
    }

    // Create the query condition to target only specific `(project_id, rownumber)` pairs
    $whereClause = implode(" OR ", $conditions);

    // Query the database to retrieve details for the selected `(project_id, rownumber)` pairs only
    $query = "
    SELECT 
        project_id,
        price,
        comments,
        last_modified_date,
        rownumber
    FROM 
        csa_finance_readytobeinvoiced
    WHERE 
        $whereClause
";

    // Execute the query
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $invoiceLines = [];
        $totalItemValue = 0;

        while ($row = $result->fetch_assoc()) {
            // Ensure that price is set and is a valid number
            $lineAmount = isset($row['price']) && is_numeric($row['price']) ? (float)$row['price'] : 20.0;  // Default to 20.0 if not valid
            $totalItemValue += $lineAmount;

            // Generate a unique description for each line
            $lineDescription = !empty($row['comments']) ? $row['comments'] : 'No Description';  // Default if no comments provided
            $lineDescription .= " (Row #{$row['rownumber']} in Project #{$row['project_id']})";

            // Fetch the service date for this project and row
            $projectServiceDate = $service_date["{$row['project_id']}_{$row['rownumber']}"] ?? null; // Get the service date for the project

            // Ensure the service date is valid, otherwise set it to NULL
            if ($projectServiceDate && strtotime($projectServiceDate)) {
                $serviceDateFormatted = date('Y-m-d', strtotime($projectServiceDate)); // Format to Y-m-d
            } else {
                $serviceDateFormatted = '1970-01-01';  // Set default date if no valid service date is found
            }

            // Log the values for debugging purposes
            error_log("Line Data: ");
            error_log("Description: $lineDescription");
            error_log("Amount: $lineAmount");
            error_log("Service Date: $serviceDateFormatted");

            // Ensure that each line contains the required fields
            if (!empty($lineDescription) && $lineAmount && !empty($serviceDateFormatted)) {
                // Add the line item with service date
                $invoiceLines[] = [
                    'lineItemName' => "Line for Project #" . $row['project_id'] . " - " . $lineDescription,
                    'lineAmount' => $lineAmount,
                    'lineQty' => 1,  // Assuming quantity is always 1, adjust if needed
                    'lineDescription' => $lineDescription,
                    'serviceDate' => $serviceDateFormatted  // Store the properly formatted service date
                ];
            } else {
                // Log the missing field issue
                error_log("Missing required fields for Line #{$row['rownumber']} in Project #{$row['project_id']}");
                echo "Error: Missing required fields for one of the invoice lines.";
                exit;
            }
        }

        if (empty($invoiceLines)) {
            echo "No invoice lines to process.";
        } else {
            // Prepare the unified invoice
            $invoice = [
                "itemName" => 'Unified Invoice for Selected Projects',
                "itemValue" => $totalItemValue,
                "customerRef" => 68,
                "qty" => 1,
                "description" => "Invoice for Selected Projects",
                "lines" => $invoiceLines,  // Include all invoice lines here
                "service_date" => $service_date,  // Use the dynamic service date
                'dueDate' => $due_date, // Include dueDate in the add-lines data
            ];

            // Convert the invoice to JSON
            $jsonData = json_encode([$invoice]);

            // If an invoice number is provided, add lines to the existing invoice
            if ($invoiceNumber) {
                $addLinesData = [
                    'docNumber' => $invoiceNumber,
                    'serviceDate' => $service_date,
                    'dueDate' => $due_date,
                    'lines' => []  // Prepare to add lines to existing invoice
                ];

                // Add invoice lines to the data
                foreach ($invoiceLines as $line) {
                    // Ensure every line has the necessary data: item name, amount, quantity, and service date
                    if (isset($line['lineItemName'], $line['lineAmount'], $line['lineQty'], $line['serviceDate'])) {
                        $addLinesData['lines'][] = [
                            'lineAmount' => $line['lineAmount'],
                            'lineItemName' => $line['lineItemName'],
                            'lineQty' => $line['lineQty'],
                            'serviceDate' => $line['serviceDate']
                        ];
                    } else {
                        // Log the missing fields issue
                        error_log("Missing required fields for one of the invoice lines.");
                        echo "Error: Missing required fields for one of the invoice lines.";
                        exit;
                    }
                }

                // Convert the add-lines request data to JSON
                $addLinesJsonData = json_encode($addLinesData);

                // Send the request to the API to add the invoice lines
                $addLinesUrl = 'http://localhost:3000/add-lines';
                $chAddLines = curl_init($addLinesUrl);
                curl_setopt($chAddLines, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chAddLines, CURLOPT_POST, true);
                curl_setopt($chAddLines, CURLOPT_POSTFIELDS, $addLinesJsonData);
                curl_setopt($chAddLines, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($addLinesJsonData)
                ]);

                // Execute the cURL request to add lines
                $addLinesResponse = curl_exec($chAddLines);
                curl_close($chAddLines);

                if ($addLinesResponse) {
                    echo "Lines added successfully to invoice #$invoiceNumber!";
                } else {
                    echo "Error adding lines to invoice.";
                }
            } else {
                // If no invoice number is provided, create a new invoice
                $url = 'http://localhost:3000/create-invoice';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ]);

                // Execute the cURL request
                $response = curl_exec($ch);
                curl_close($ch);

                if ($response) {
                    echo "Invoice created successfully!";
                } else {
                    echo "Error creating invoice.";
                }
            }

            // After generating the invoice, update the project statuses in the database
            updateProjectStatus($conn, $projectData, $service_date, $due_date, $invoiceLines);

            header('location:readyToBeInvoiced.php');
        }
    } else {
        echo "No projects found for the selected IDs in the database.";
    }
} else {
    echo "No projects selected.";
}

// Function to update the project status and insert invoiced data into the database
function updateProjectStatus($conn, $projectData, $service_date, $due_date, $invoiceLines)
{
    foreach ($projectData as $data) {
        list($project_id, $rownumber) = explode('_', $data);

        $updateQuery = "
            UPDATE csa_finance_readytobeinvoiced
            SET project_status = 'Invoiced'
            WHERE project_id = " . (int)$project_id . " AND rownumber = " . (int)$rownumber;

        if ($conn->query($updateQuery) === TRUE) {
            echo "Updated project status to 'Invoiced' for Project ID: $project_id, Row: $rownumber.<br>";
        } else {
            echo "Error updating project status: " . $conn->error . "<br>";
        }

        $queryProjectDetails = "
            SELECT project_name, p_team 
            FROM projects 
            WHERE project_id = $project_id
            UNION
            SELECT project_name, p_team 
            FROM deliverable_data 
            WHERE project_id = $project_id
        ";

        $resultProjectDetails = $conn->query($queryProjectDetails);

        if ($resultProjectDetails && $resultProjectDetails->num_rows > 0) {
            $projectDetails = $resultProjectDetails->fetch_assoc();

            // Get the lineAmount and comments from the matched invoice line
            $invoiceLine = array_filter($invoiceLines, function ($line) use ($rownumber) {
                return strpos($line['lineDescription'], "Row #$rownumber") !== false;
            });
            $invoiceLine = reset($invoiceLine); // Get the first match

            $insertQuery = "
                INSERT INTO csa_finance_invoiced (
                    project_id, project_title, comments, amount, p_team, 
                    service_date, due_date, rownumber
                ) 
                VALUES (
                    " . (int)$project_id . ",
                    '" . $conn->real_escape_string($projectDetails['project_name']) . "',
                    '" . $conn->real_escape_string($invoiceLine['lineDescription'] ?? 'No Description') . "',
                    " . (float)($invoiceLine['lineAmount'] ?? 0) . ",
                    '" . $conn->real_escape_string($projectDetails['p_team']) . "',
                    " . (isset($service_date["{$project_id}_{$rownumber}"]) ? "'" . $conn->real_escape_string($service_date["{$project_id}_{$rownumber}"]) . "'" : "NULL") . ",
                    '" . $conn->real_escape_string($due_date) . "',
                    " . (int)$rownumber . "
                )";

            if ($conn->query($insertQuery) === TRUE) {
                echo "Invoice details inserted successfully for Project ID: $project_id, Row: $rownumber.<br>";
            } else {
                echo "Error inserting invoice details: " . $conn->error . "<br>";
            }
        }
    }
}

// Close the connection
$conn->close();

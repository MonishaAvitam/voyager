    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        require '../conn.php'; // Ensure the database connection is established

        // Retrieve values from the form
        $id = $_POST['id'];               // Get the service ID
        $name = $_POST['name'];           // Get the service name
        $vendor_id = $_POST['vendor_id']; // Get the selected vendor ID
        $comments = $_POST['comments'];   // Get the comments

        // Debugging: Log the received vendor_id
        error_log('Received vendor_id: ' . $vendor_id);

        // Check if vendor_id is empty (which should not happen with the proper validation)
        if (empty($vendor_id)) {
            // Handle error if vendor_id is missing
            echo "<script>alert('Vendor ID is required');</script>";
            exit();
        }

        // Prepare and update the service record
        $sql = "UPDATE services SET name = ?, vendor_id = ?, comments = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        // Check if the preparation of the SQL statement was successful
        if ($stmt === false) {
            error_log('Error preparing SQL query: ' . $conn->error);
            echo "<script>alert('There was an error with the update. Please try again.');</script>";
            exit();
        }

        // Bind parameters and execute the query
        $stmt->bind_param("sisi", $name, $vendor_id, $comments, $id);

        // Execute the query and check if successful
        if ($stmt->execute()) {
            // Redirect to the same page with a success parameter
            header("Location: services.php?success=1");
            exit();
        } else {
            // Redirect to the same page with an error parameter
            error_log('Error executing SQL query: ' . $stmt->error);
            header("Location: services.php?error=1");
            
            exit();
        }
        

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
    ?>

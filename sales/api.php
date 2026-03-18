<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Csa</title>
    <style>
        .content {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100vh;
            color: white;
        }
    </style>
</head>

<body style="background-color: grey;">
    <div class="content">
        <?php
        require '../conn.php';

        if (isset($_GET['quotation_acknowledgement'])) {
            $quotation_no = $_GET['quotation_no'];
            $quotation_name = $_GET['quotation_name'];
            $quotation_assigned_id = $_GET['quotation_assigned_id'];

            // Update the quotation status
            $sql = "UPDATE quotations SET status = 'Accepted' WHERE quotation_no = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('i', $quotation_no);
                if ($stmt->execute()) {
                    echo "Request approved successfully. You can now close this window.";
                } else {
                    error_log("Error updating record: " . $stmt->error);
                    echo "Error updating record: " . $stmt->error;
                }
                $stmt->close();
            } else {
                error_log("Error preparing statement: " . $conn->error);
                echo "Error preparing statement: " . $conn->error;
            }



            // $message = 'Quotation has been accepted by ' . $quotation_assigned_id;

            // // RAE Notification
            // $msg_status = 'unread';
            // $software = 'OBI';
            // $notificationSql = "INSERT INTO notifications (msg_from, msg_subject, msg_content, msg_status, software, quotation_no) VALUES (?, ?, ?, ?, ?, ?)";
            // $stmtNotify = $conn->prepare($notificationSql);

            // if ($stmtNotify) {
            //     $stmtNotify->bind_param('issssi', $quotation_assigned_id, $quotation_name, $message, $msg_status, $software, $quotation_no);
            //     if (!$stmtNotify->execute()) {
            //         echo "Error inserting RAE notification: " . $stmtNotify->error;
            //     }
            //     $stmtNotify->close();
            // } else {
            //     echo "Error preparing RAE notification statement: " . $conn->error;
            // }
            
        } else {
            echo "No acknowledgment received.";
        }
        ?>
    </div>
</body>

</html>

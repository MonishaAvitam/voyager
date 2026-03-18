<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Csa</title>
</head>
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

<body style="background-color: black;">
    <div class="content">
        <?php
        require 'conn.php';

        if (isset($_GET['id'])) {
            $request_id = $_GET['id'];

            if (isset($_GET['leave_approval'])) {
                $action = $_GET['leave_approval'];

                switch ($action) {
                    case 'approve':
                        $sql = "UPDATE leave_approval SET approved = 'Approved' WHERE leave_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $request_id);
                        $stmt->execute();
                        // Check for successful update
                        if ($stmt->affected_rows > 0) {
                            echo "Leave request approved successfully.you can now close this window";

                        } else {
                            echo "Failed to approve leave request.you can now close this window";
                        }
                        break;
                    case 'deny':
                        $sql = "UPDATE leave_approval SET approved = 'Denied' WHERE leave_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $request_id);
                        $stmt->execute();
                        // Check for successful update
                        if ($stmt->affected_rows > 0) {
                            echo "Leave request denied successfully. you can now close this window";

                        } else {
                            echo "Failed to deny leave request.you can now close this window";

                        }
                        break;
                    default:
                        echo "Invalid action.";
                        break;
                }
            } else if (isset($_GET['emergency_approval'])) {
                $action = $_GET['emergency_approval'];

                switch ($action) {
                    case 'approve':
                        $sql = "UPDATE emergency_approval SET emergency_leave_status = 'Approved' WHERE em_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $request_id);
                        $stmt->execute();
                        // Check for successful update
                        if ($stmt->affected_rows > 0) {
                            echo "Leave request approved successfully.you can now close this window";

                        } else {
                            echo "Failed to approve leave request.you can now close this window";
                        }
                        break;
                    case 'deny':
                        $sql = "UPDATE emergency_approval SET emergency_leave_status = 'Denied' WHERE em_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $request_id);
                        $stmt->execute();
                        // Check for successful update
                        if ($stmt->affected_rows > 0) {
                            echo "Leave request denied successfully. you can now close this window";
                        } else {
                            echo "Failed to deny leave request.you can now close this window";
                        }
                        break;
                    default:
                        echo "Invalid action.";
                        break;
                }
            }
            
            
            else {
                echo "Action not specified.";
            }
        } else {
            echo "Leave request ID not specified.";
        }


        ?>
    </div>
</body>

</html>
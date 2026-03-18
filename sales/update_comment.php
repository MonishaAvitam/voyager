<?php
require '../conn.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sales_id = $_POST['sales_id'] ?? null;
    $comment = $_POST['comment'] ?? null;
    $revised_qt = $_POST['revised_qt'] ?? null;

    if ($sales_id !== null && $comment !== null) {
        if (!empty($revised_qt)) {
            // Update based on both sales_id and revised_qt
            $stmt = $conn->prepare("UPDATE potential_project_sent_quotation SET comments = ? WHERE sales_id = ? AND revised_qt = ?");
            $stmt->bind_param("sss", $comment, $sales_id, $revised_qt);
        } else {
            // Update based only on sales_id if revised_qt is missing
            $stmt = $conn->prepare("UPDATE potential_project_sent_quotation SET comments = ? WHERE sales_id = ? AND (revised_qt IS NULL OR revised_qt = '')");
            $stmt->bind_param("ss", $comment, $sales_id);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Comment updated successfully.']);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No record updated. Check if the sales_id exists and the revised_qt condition is correct.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $stmt->error
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    }
}

$conn->close();
?>

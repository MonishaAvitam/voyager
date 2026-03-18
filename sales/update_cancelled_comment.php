<?php
require '../conn.php'; // Database connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sales_id = $_POST['sales_id'] ?? null;
    $comment = $_POST['comments'] ?? null; // Fixed key name
    $revised_qt = $_POST['revised_qt'] ?? null;

    if ($sales_id !== null && $comment !== null) {
        if ($revised_qt === 'null' || $revised_qt === NULL || $revised_qt === '') {
            // Update where revised_qt IS NULL
            $stmt = $conn->prepare("UPDATE cancelled_quotations SET comments = ? WHERE sales_id = ? AND (revised_qt IS NULL OR revised_qt = '')");
            $stmt->bind_param("ss", $comment, $sales_id);
        } else {
            // Update where revised_qt is a specific value
            $stmt = $conn->prepare("UPDATE cancelled_quotations SET comments = ? WHERE sales_id = ? AND revised_qt = ?");
            $stmt->bind_param("sss", $comment, $sales_id, $revised_qt);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Comment updated successfully.']);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No record updated. Check if sales_id and revised_qt exist.'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data received.']);
    }
}

$conn->close();
?>

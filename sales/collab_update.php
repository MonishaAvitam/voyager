<?php
header('Content-Type: application/json');
include '../conn.php';

$response = ["success" => false, "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch existing collaborators
    $customerId = intval($_GET['customer_id'] ?? 0);
    if ($customerId > 0) {
        $res = mysqli_query($conn, "SELECT colab_user_ids FROM potential_customer WHERE id=$customerId LIMIT 1");
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            // decode JSON from DB or empty array
            $ids = json_decode($row['colab_user_ids'] ?? '[]', true) ?: [];
            $response = ["success" => true, "data" => $ids];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save collaborators
    $customerId = intval($_POST['customer_id']);
    $collabUserIds = $_POST['collab_user_id'] ?? '';

    // Convert to array if it’s a string like "1,2" or already array
    if (is_string($collabUserIds)) {
        $idsArray = array_filter(explode(',', $collabUserIds));
    } elseif (is_array($collabUserIds)) {
        $idsArray = $collabUserIds;
    } else {
        $idsArray = [];
    }

    // 🔹 Cast to integers before JSON encoding
    $idsJson = json_encode(array_map('intval', array_values($idsArray)));

    $stmt = $conn->prepare("UPDATE potential_customer SET colab_user_ids=? WHERE id=?");
    $stmt->bind_param("si", $idsJson, $customerId);
    $response['success'] = $stmt->execute();

}

echo json_encode($response);
exit;

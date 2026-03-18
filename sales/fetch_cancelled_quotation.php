<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require '../conn.php';

if (!$conn) {
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}

// Ensure sales_id is provided
if (!isset($_GET['sales_id']) || empty($_GET['sales_id'])) {
    echo json_encode(["error" => "sales_id is required"]);
    exit();
}

$sales_id = mysqli_real_escape_string($conn, $_GET['sales_id']);
$revised_qt = isset($_GET['revised_qt']) ? mysqli_real_escape_string($conn, $_GET['revised_qt']) : '';

if (!empty($revised_qt)) {
    // Fetch using both sales_id and revised_qt
    $query = "SELECT * FROM cancelled_quotations WHERE sales_id = '$sales_id' AND revised_qt = '$revised_qt'";
} else {
    // Fetch the latest revised quotation for the given sales_id
    $query = "SELECT * FROM cancelled_quotations WHERE sales_id = '$sales_id' AND revised_qt IS NULL";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . mysqli_error($conn)]);
    exit();
}

// Check if any rows are returned
if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No data found for sales_id: $sales_id"]);
}
?>

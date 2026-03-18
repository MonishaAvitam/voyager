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

if (!isset($_GET['sales_id'])) {
    echo json_encode(["error" => "sales_id is required"]);
    exit();
}

$sales_id = mysqli_real_escape_string($conn, $_GET['sales_id']);

$query = "SELECT * FROM potential_project WHERE sales_id = '$sales_id'";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . mysqli_error($conn)]);
    exit();
}

$data = mysqli_fetch_assoc($result);

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No data found"]);
}
?>

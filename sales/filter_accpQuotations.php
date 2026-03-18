<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require '../conn.php';

// Check connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}

// Default query to fetch all accepted quotations
$query = "SELECT * FROM accepted_quotations";
$params = [];

// Get the selected month and year from the POST request
$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : null;

if ($selectedDate) {
    // Apply filter only if a date is selected
    list($year, $month) = explode('-', $selectedDate);
    $query = "SELECT * FROM accepted_quotations WHERE MONTH(accepted_date) = ? AND YEAR(accepted_date) = ?";
    $params = [$month, $year];
}else{
    list($year, $month) = explode('-', $selectedDate);
    $query = "SELECT * FROM accepted_quotations WHERE accepted_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND accepted_date <= CURDATE()";
    $params = [$month, $year];
}

$stmt = $conn->prepare($query);

// Check if query preparation was successful
if (!$stmt) {
    echo json_encode(["error" => "Query preparation failed: " . $conn->error]);
    exit();
}

// Bind parameters only if filtering
if (!empty($params)) {
    $stmt->bind_param("ss", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(["error" => "Query execution failed: " . $stmt->error]);
    exit();
}

$acceptedQuotations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $acceptedQuotations[] = $row;
}

// Ensure data is always returned in JSON format
echo json_encode(["accepted_quotations" => $acceptedQuotations]);
?>

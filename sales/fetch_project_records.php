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

// Fetch data from potential_project
$query1 = "SELECT * FROM potential_project";
$result1 = mysqli_query($conn, $query1);

$potentialProjects = [];
if ($result1) {
    while ($row = mysqli_fetch_assoc($result1)) {
        $potentialProjects[] = $row;
    }
} else {
    echo json_encode(["error" => "Query 1 failed: " . mysqli_error($conn)]);
    exit();
}

// Fetch data from potential_project_sent_quotation
$query2 = "SELECT * FROM potential_project_sent_quotation";
$result2 = mysqli_query($conn, $query2);

$sentQuotations = [];
if ($result2) {
    while ($row = mysqli_fetch_assoc($result2)) {
        $sentQuotations[] = $row;
    }
} else {
    echo json_encode(["error" => "Query 2 failed: " . mysqli_error($conn)]);
    exit();
}

//cancelled_quotations



// Fetch data from cancelled_quotations
$query3 = "SELECT * FROM cancelled_quotations";
$result3 = mysqli_query($conn, $query3);

$cancelledQuotations = [];

if ($result3) {
    while ($row = mysqli_fetch_assoc($result3)) {
        $cancelledQuotations[] = $row; 
    }
} else {
    echo json_encode(["error" => "Query 3 failed: " . mysqli_error($conn)]);
    exit();
}


// Fetch data from accepted_quotations
// Fetch data from accepted_quotations within the last 2 months from the current date
$query4 = "SELECT * FROM accepted_quotations";
$result4 = mysqli_query($conn, $query4);

$AcceptedQuotations = [];

if ($result4) {
    while ($row = mysqli_fetch_assoc($result4)) {
        $AcceptedQuotations[] = $row;  
    }
} else {
    echo json_encode(["error" => "Query 4 failed: " . mysqli_error($conn)]);
    exit();
}




// Return both datasets in JSON format
$response = [
    "potential_projects" => $potentialProjects,
    "sent_quotations" => $sentQuotations,
    "cancelled_quotations" => $cancelledQuotations,
    "accepted_quotations" => $AcceptedQuotations
];

echo json_encode($response);
?>

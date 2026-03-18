<?php
header('Content-Type: application/json');

// Sample project data
$projects = [
    [
        "project_id" => 31806,
        "revision_project_id" => null,  // Main project
        "project_name" => "Test 3",
        "project_details" => "test 3",
        "start_date" => "2025-02-04",
        "end_date" => "2025-02-10",
        "progress" => 50,
        "contact_name" => "Philip",
        "customer_name" => "ECAR"
    ],
    [
        "project_id" => 31807,
        "revision_project_id" => 31806,  // Subproject of 31806
        "project_name" => "Subproject 1",
        "project_details" => "Subtask of Test 3",
        "start_date" => "2025-02-05",
        "end_date" => "2025-02-08",
        "progress" => 70,
        "contact_name" => "Philip",
        "customer_name" => "ECAR"
    ],
    [
        "project_id" => 31808,
        "revision_project_id" => 31806,  // Another subproject of 31806
        "project_name" => "Subproject 2",
        "project_details" => "Another subtask of Test 3",
        "start_date" => "2025-02-06",
        "end_date" => "2025-02-09",
        "progress" => 30,
        "contact_name" => "Philip",
        "customer_name" => "ECAR"
    ],
    [
        "project_id" => 31805,
        "revision_project_id" => null,  // Another main project
        "project_name" => "Test Project",
        "project_details" => "test ",
        "start_date" => "2025-03-04",
        "end_date" => "2025-03-12",
        "progress" => 80,
        "contact_name" => "Noel Singh",
        "customer_name" => "Composite Structures Australia Engineering"
    ],
    [
        "project_id" => 31809,
        "revision_project_id" => 31805,  // Subproject of 31805
        "project_name" => "Test Subproject",
        "project_details" => "Subtask of Test Project",
        "start_date" => "2025-03-05",
        "end_date" => "2025-03-11",
        "progress" => 50,
        "contact_name" => "Noel Singh",
        "customer_name" => "Composite Structures Australia Engineering"
    ]
];

// Return JSON response
echo json_encode($projects, JSON_PRETTY_PRINT);
?>

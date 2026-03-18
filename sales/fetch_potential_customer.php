<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require '../conn.php';

// Check database connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$office_id_param = isset($_GET['office_id']) ? intval($_GET['office_id']) : 0;
$archived = isset($_GET['archived']) && $_GET['archived'] == 1 ? true : false;

// First get the office_id of the logged-in user
// Use office_id from GET if provided, otherwise fallback to user's office
if (isset($_GET['office_id']) && intval($_GET['office_id']) > 0) {
    $user_office_id = intval($_GET['office_id']);
} else {
    // First get the office_id of the logged-in user
    $office_query = "SELECT office_id FROM tbl_admin WHERE user_id = ?";
    $stmt = $conn->prepare($office_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $office_result = $stmt->get_result();
    $user_office_id = null;

    if ($row = $office_result->fetch_assoc()) {
        $user_office_id = $row['office_id'];
    }
}

// Now fetch customers that match this office_id
$account_manager_id = isset($_GET['account_manager_id']) ? intval($_GET['account_manager_id']) : 0;

if ($account_manager_id > 0) {
    // ✅ Fetch customers by account_manager_id
    $query1 = "SELECT pc.`id`, pc.`date`, pc.`company_name`, pc.`website`, pc.`address`, 
                  pc.`name`, pc.`phone_num`, pc.`email`, pc.`comments`, 
                  ta.`fullname` AS account_manager,
                  pc.`account_manager` AS account_manager_id,
                  pc.`office_id` AS pc_office_id,
                  o.`office_name` AS pc_office_name,
                  ta.`office_id` AS ta_office_id,
                  (
    SELECT MAX(timestamp) 
    FROM potential_customer_action_record 
    WHERE customer_id = pc.id
) AS last_update

           FROM potential_customer pc
           LEFT JOIN tbl_admin ta ON pc.account_manager = ta.user_id
           LEFT JOIN office o ON pc.office_id = o.id
           WHERE (pc.client_status IS NULL OR pc.client_status <> 'converted')
  AND (pc.account_manager = '$account_manager_id'
       OR JSON_CONTAINS(pc.colab_user_ids, '$account_manager_id'))
  AND (pc.status IS NULL OR pc.status <> 'deleted')
           ORDER BY pc.id DESC";


} else {
    // ✅ Default: fetch customers that match this office_id
    $query1 = "SELECT pc.`id`, pc.`date`, pc.`company_name`, pc.`website`, pc.`address`, 
                      pc.`name`, pc.`phone_num`, pc.`email`, pc.`comments`, 
                      ta.`fullname` AS account_manager,
                      pc.`account_manager` AS account_manager_id,
                      pc.`office_id` AS pc_office_id,
                      ta.`office_id` AS ta_office_id,
                      (
    SELECT MAX(timestamp) 
    FROM potential_customer_action_record 
    WHERE customer_id = pc.id
) AS last_update
               FROM potential_customer pc
               LEFT JOIN tbl_admin ta ON pc.account_manager = ta.user_id
              WHERE (pc.client_status <> 'converted' OR pc.client_status IS NULL)
AND pc.office_id = '" . ($office_id_param > 0 ? $office_id_param : $user_office_id) . "'
AND (pc.status IS NULL OR pc.status <> 'deleted')
               ORDER BY pc.id DESC";
}


// Fetch archived customers (status = 'deleted')
$archive_clients = [];
$archive_query = "SELECT pc.`id`, pc.`date`, pc.`company_name`, pc.`website`, pc.`address`, 
                         pc.`name`, pc.`phone_num`, pc.`email`, pc.`comments`, 
                         ta.`fullname` AS account_manager,
                         pc.`account_manager` AS account_manager_id,
                         pc.`office_id` AS pc_office_id,
                         ta.`office_id` AS ta_office_id,
                          (
    SELECT MAX(timestamp) 
    FROM potential_customer_action_record 
    WHERE customer_id = pc.id
) AS last_update
                  FROM potential_customer pc
                  LEFT JOIN tbl_admin ta ON pc.account_manager = ta.user_id
                  WHERE pc.status = 'deleted'
                  ORDER BY pc.id DESC";

$result_archive = mysqli_query($conn, $archive_query);
if ($result_archive) {
    while ($row = mysqli_fetch_assoc($result_archive)) {
        $archive_clients[] = $row;
    }
}

$result1 = mysqli_query($conn, $query1);

if (!$result1) {
    echo json_encode(["error" => "Query 1 failed: " . mysqli_error($conn)]);
    exit();
}

$customers = [];
while ($row = mysqli_fetch_assoc($result1)) {
    $customers[] = $row;
}

// Define an empty array for customer actions
$customer_actions = [];

// Check if a specific customer_id is provided
if (isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];

    // Fetch actions for a specific customer
    $query2 = "SELECT ar.id, ar.customer_id, ar.timestamp, ar.record, ar.record_number, ar.contact_customer_id, ar.user_id, ta.fullname 
           FROM potential_customer_action_record ar
           LEFT JOIN tbl_admin ta ON ar.user_id = ta.user_id
           WHERE ar.customer_id = ? 
           ORDER BY ar.record_number DESC";
    $stmt = $conn->prepare($query2);
    $stmt->bind_param("i", $customerId); // Bind customer_id to the query
    $stmt->execute();
    $result2 = $stmt->get_result();

    if (!$result2) {
        echo json_encode(["error" => "Query 2 failed: " . mysqli_error($conn)]);
        exit();
    }

    while ($row = mysqli_fetch_assoc($result2)) {
        $customer_actions[] = $row;
    }

    // Return only the action records if a customer_id is specified
    echo json_encode(['records' => $customer_actions]);
    exit();
}

// If no specific customer_id is given, return all customer data with an empty action list
$response = [
    "potential_customers" => $customers,
    "customer_actions" => $customer_actions,
    "archive_clients" => $archive_clients, // <-- archived clients added

];

echo json_encode($response);
?>
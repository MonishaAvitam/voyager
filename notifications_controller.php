<?php
// This script will accept the following three commands as
// GET parameters and will act on as follows:
// INSERT_NOTIFICATION - Insert the notification in the [notifications] table
// FETCH_NOTIFICATIONS - Get all notifications for a user from [notifications] table
// DELETE_NOTIFICATIONS - Deletes all notifications for a user from [notifications] table
// SET_IS_VIEWED_FLAG - Call this command from the front-end when the user has viewed the notification

$command = isset($_GET['command']) ? $_GET['command'] : '';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

if (empty($user_id)) {
    echo "<br>Error: user_id must be specified as a GET parameter.";
    exit;
}

if (empty($command)) {
    echo "<br>Sorry, no command specified. Exiting...";
    exit;
}

// Open the MySQL database connection here and select the database in
// which [notifications] table has been created
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etmsh_dev";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($command === "INSERT_NOTIFICATION") {
    $notification_text = isset($_GET['notification_text']) ? $_GET['notification_text'] : '';
    $is_viewed = isset($_GET['is_viewed']) ? $_GET['is_viewed'] : '';

    if (empty($notification_text)) {
        echo "<br>Error: notification_text must be specified as a GET parameter.";
        exit;
    }

    if (empty($is_viewed)) {
        echo "<br>Error: is_viewed must be specified as a GET parameter.";
        exit;
    }

    // Insert notification into the table for the given user
    $sql = "SELECT * FROM notifications";
    $result = $conn->query($sql);
    $id = $result->num_rows + 1;
    $creation_date_time = date("Y-m-d H:i:s");
    $view_date_time = date("Y-m-d H:i:s");
    $sql = "INSERT INTO `notifications`(`id`, `creation_date_time`, `view_date_time`, `user_id`, `notification_text`, `is_viewed`)
            VALUES ('$id', '$creation_date_time', '$view_date_time', '$user_id', '$notification_text', '$is_viewed')";
    $result = $conn->query($sql);
    $conn->close();
    exit;
}

if ($command === "FETCH_NOTIFICATIONS") {
    // Select all notifications for a given user_id and return in JSON format
    $sql = "SELECT * FROM notifications WHERE user_id = '$user_id' AND is_viewed = 'NO'";
    $res = $conn->query($sql);
    $result = array();
    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }
    $conn->close();
    echo json_encode($result);
    exit;
}

if ($command === "DELETE_NOTIFICATIONS") {
    // Delete all notifications for a given user_id
    $sql = "DELETE FROM notifications WHERE user_id = '$user_id'";
    $res = $conn->query($sql);
    $conn->close();
    exit;
}

if ($command === "SET_IS_VIEWED_FLAG") {
    // Update here is_viewed flag to YES for a notification and user_id
    // Implement this part as needed
    exit;
}
?>

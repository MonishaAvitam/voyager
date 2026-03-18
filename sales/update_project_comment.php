<?php
include '../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['sales_id']) && isset($_POST['comments'])) {
        $sales_id = $_POST['sales_id'];
        $comments = $_POST['comments'];

        $stmt = $conn->prepare("UPDATE potential_project SET comments = ? WHERE sales_id = ?");
        $stmt->bind_param("ss", $comments, $sales_id);

        if ($stmt->execute()) {
            header("Location: project_enquiry.php");
            exit();
        } else {
            header("Location: project_enquiry.php");
            exit();
        }
    } else {
        header("Location: project_enquiry.php?message=Invalid request.");
        exit();
    }
}
?>

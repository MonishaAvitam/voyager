

<?php



// Check if the 'project_id' is set in the URL
if (isset($_GET['table_id'])) {
    $table_id = $_GET['table_id'];


    // Replace with your database connection code
    include 'conn.php';

      $status_value = 0;
      $verify_status = 0;
      $assign_status = 0;
      $project_manager_status = 3;
 $checker_status = 0;


      // SQL query to update the 'progress' column of the 'projects' table for a specific project
      $sql = "UPDATE subprojects SET assign_to_status = ? , verify_status = ? , project_manager_status = ? ,checker_status = ? , assign_status = ? WHERE table_id = ?";

      // Prepare and execute the statement
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iissss", $status_value, $verify_status, $project_manager_status, $checker_status, $assign_status, $table_id);

      if ($stmt->execute()) {
        $msg_success = "Sent To Project Manager";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
      } else {
        $msg_error = "Error: " . $conn->error;
      }

   
 

    $conn->close();
} else {
    $msg_error = "No project ID in the URL.";
}
?>


<?php  include 'include/footer.php'  ?>
<?php
require '../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendEmail'])) {
    $sendEmail = $_POST['sendEmail'];
    $enquiryId = $_POST['enquiryId']; 

    // Query to get the contact_id from the enquiry_sales table
    $sql = "SELECT contact_id FROM enquiry_sales WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $enquiryId);
    $stmt->execute();
    $stmt->bind_result($contact_id);
    $stmt->fetch();
    $stmt->close();

    if ($contact_id) {
        // Query to get the contact_email from the contacts table using the contact_id
        $sql = "SELECT contact_email FROM contacts WHERE contact_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $contact_id);
        $stmt->execute();
        $stmt->bind_result($contact_email);
        $stmt->fetch();
        $stmt->close();

        if ($contact_email) {
            echo '<div style="padding: 10px; border: 1px solid #007bff; border-radius: 5px; background-color: #f8f9fa; color: #000000;">
                    <strong>Email ID:</strong> ' . htmlspecialchars($contact_email) . '
                  </div>';
        } else {
            echo '<div style="padding: 10px; border: 1px solid #dc3545; border-radius: 5px; background-color: #f8d7da; color: #dc3545;">
                    <strong>No email found for the selected contact.</strong>
                  </div>';
        }
    } else {
        echo '<div style="padding: 10px; border: 1px solid #ffc107; border-radius: 5px; background-color: #fff3cd; color: #856404;">
            <strong>No contact associated with this enquiry.</strong>
            <input type="email" name="new_contact_email" class="form-control mt-2" placeholder="Enter Email ID">
          </div>';
    }
}
?>


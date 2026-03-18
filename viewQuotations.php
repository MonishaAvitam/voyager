<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';
require  './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

include 'include/sidebar.php';
include 'add_project.php';
include 'add_subproject.php';



use Google\Client;
use Google\Service\Drive;

// Initialize Google Drive API client with service account credentials
$client = new Client();
$client->setAuthConfig('./google-drive/credentials.json');
$client->setSubject('csa-admin@csa-rae.iam.gserviceaccount.com');
$client->addScope(Drive::DRIVE);
$driveService = new Drive($client);

if (isset($_GET['msg_id'])) {
    $msg_id = $_GET['msg_id'];
    $msg_status = 'read';
    $sql = "UPDATE notifications SET msg_status =? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $msg_status, $msg_id);
    $stmt->execute();
    $stmt->close();
    echo '
    <script>
        function clearMsgIdParameter() {
            const url = new URL(window.location);
            url.searchParams.delete("msg_id");
            window.history.replaceState({}, document.title, url);
        }

        clearMsgIdParameter();
        window.location.reload();
    </script>
';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uploadQuotation'])) {
    $quotation_no = $_POST['quotation_no'];
    $enquiry_id = $_POST['enquiry_id'];
    $msg_from = $_POST['msg_from'];

    // Use prepared statement to prevent SQL injection
    $sqlFolderId = "SELECT es.folderId FROM quotations q LEFT JOIN enquiry_sales es ON q.enquiry_id = es.id WHERE q.quotation_no = ?";
    $stmt = $conn->prepare($sqlFolderId);
    $stmt->bind_param('s', $quotation_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $EnquiryFolderId = $row['folderId'];
            $EnquiryFolderId; // For debugging purposes
        }
    } else {
        error_log('No folder ID found for the given quotation number.');
        json_encode(['success' => false, 'message' => 'No folder ID found for the given quotation number.']);
        exit();
    }
    $stmt->close();

    try {
        // Define the parent folder ID
        $parentFolderId = $EnquiryFolderId;
        $uploadedFileIds = []; // Array to store uploaded file IDs

        // Check if attachment file is provided
        if (isset($_FILES['attachment_file'])) {
            $numFiles = count($_FILES['attachment_file']['name']);

            // Loop through each uploaded file
            for ($i = 0; $i < $numFiles; $i++) {
                if ($_FILES['attachment_file']['error'][$i] === UPLOAD_ERR_OK) {
                    // Handle each attachment file
                    $attachmentFilePath = $_FILES['attachment_file']['tmp_name'][$i];
                    $attachmentFileName = $_FILES['attachment_file']['name'][$i];
                    $folderId = $parentFolderId;

                    // Define metadata for the attachment file
                    $attachmentMetadata = new Drive\DriveFile([
                        'name' => $attachmentFileName,
                        'parents' => [$folderId],
                    ]);

                    // Upload the attachment file to Google Drive
                    $uploadedFile = $driveService->files->create($attachmentMetadata, [
                        'data' => file_get_contents($attachmentFilePath),
                        'mimeType' => mime_content_type($attachmentFilePath),
                        'uploadType' => 'multipart',
                        'fields' => 'id, webViewLink' // Request webViewLink in addition to ID
                    ]);

                    // Log the file upload success
                    error_log('Uploaded file: ' . $uploadedFile->id . ', Link: ' . $uploadedFile->webViewLink);

                    // Set file permissions to allow anyone with the link to view the file
                    $permission = new Drive\Permission();
                    $permission->setType('anyone');
                    $permission->setRole('reader');
                    $driveService->permissions->create($uploadedFile->id, $permission);

                    // Add the uploaded file link to the array
                    $uploadedFileIdsLink[] = ['id' => $uploadedFile->id, 'link' => $uploadedFile->webViewLink];
                } else {
                    error_log('Error uploading file: ' . $_FILES['attachment_file']['error'][$i]);
                    echo json_encode(['success' => false, 'message' => 'Error uploading file: ' . $_FILES['attachment_file']['error'][$i]]);
                }
            }

            // Return the uploaded file links
            // echo json_encode(['success' => true, 'files' => $uploadedFileIdsLink]);
        } else {
            error_log('No attachment file provided.');
            echo json_encode(['success' => false, 'message' => 'No attachment file provided.']);
        }
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }





    $uploadedFileIdsJson = json_encode($uploadedFileIds);
    $status = 'Ready';
    $enquiry_status = 60;
    $sql3 = "UPDATE quotations SET quotation_file_id =? ,status = ? WHERE quotation_no = ?";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param('ssi', $uploadedFileIdsJson, $status, $quotation_no);
    $stmt3->execute();
    $stmt3->close();

    $sql4 = "UPDATE enquiry_sales SET enquiry_status =?  WHERE id = ?";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param('si', $enquiry_status, $enquiry_id);
    $stmt4->execute();
    $stmt4->close();


    // Fetch employee details
    $sqlEmployee = "SELECT fullname, email FROM csa_sales_admin WHERE user_id = ?";
    $obiPeople = $conn->prepare($sqlEmployee);
    $obiPeople->bind_param("i", $msg_from);
    $obiPeople->execute();
    $obiPeople->bind_result($employeeName, $employeeEmailId);
    $obiPeople->fetch();
    $obiPeople->close();

    if (!empty($uploadedFileIdsLink)) {
        foreach ($uploadedFileIdsLink as $file) {
            echo "<p><a href='" . htmlspecialchars($file['link']) . "' target='_blank'>" . htmlspecialchars($file['link']) . "</a></p>";
        
    // Compose email message
    $emailContent = "
      <html>
      <head>
          <style>
              body {
                  font-family: Arial, sans-serif;
                  background-color: #f4f4f4;
                  margin: 0;
                  padding: 0;
              }
              .container {
                  width: 100%;
                  max-width: 600px;
                  margin: 20px auto;
                  background-color: #ffffff;
                  border-radius: 8px;
                  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                  overflow: hidden;
              }
              .header {
                  background-color: #007bff;
                  color: #ffffff;
                  padding: 20px;
                  text-align: center;
              }
              .header h2 {
                  margin: 0;
                  font-size: 24px;
              }
              .content {
                  padding: 20px;
              }
              .content p {
                  line-height: 1.6;
                  color: #333333;
              }
              form {
                  margin-top: 20px;
                  text-align: center;
              }
              button {
                  padding: 10px 20px;
                  border: none;
                  border-radius: 5px;
                  background-color: #007bff;
                  color: #ffffff;
                  font-size: 16px;
                  margin: 0 10px;
                  cursor: pointer;
              }
              button:hover {
                  background-color: #0056b3;
              }
              .footer {
                  text-align: center;
                  padding: 20px;
                  background-color: #f4f4f4;
                  font-size: 12px;
                  color: #666666;
              }
          </style>
      </head>
      <body>
          <div class='container'>
              <div class='header'>
                  <h2>Quotation Ready</h2>
              </div>
              <div class='content'>
                  <p>Dear $employeeName,</p>
                  <p>Please review the quotation</p>
                  <p>You can find the quotation below:</p>
                  
                <p><a href='" . htmlspecialchars($file['link']) . "' target='_blank'>" . htmlspecialchars($file['link']) . "</a></p>                  
                  <p><br><br>
                  Please feel free to reach out if you require any additional information or if there are any specific instructions.<br>
                  </p>
                  <p>Best regards,<br>$user_name</p>
              </div>
              <div class='footer'>
                  <p>This is an automated notification. Please do not reply to this email.</p>
              </div>
          </div>
      </body>
      </html>
  ";
        }
    } else {
        echo "<p>No files uploaded.</p>";
    }

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "engineering@csaengineering.com.au";
        $mail->Password = "kezfduovpirmalcs";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('revanthshiva@csaengineering.com.au', $user_name);
        $mail->addAddress($employeeEmailId, $employeeName);

        $mail->isHTML(true);
        $mail->Subject = "Quotation Ready";
        $mail->Body = $emailContent;



        $mail->send();
        header('Location:viewQuotations.php');
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if (isset($_POST['quotationSent'])) {
    $enquiryId = $_POST['enquiryId'];
    $quotation_sent_date = $_POST['quotation_sent_date'];
    $status = '75';
    $status1 = 'Sent';
  
    // Update the enquiry_status in enquiry_sales
    $sql = "UPDATE enquiry_sales SET enquiry_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $enquiryId);
    $stmt->execute();
    $stmt->close();
  
    // Update the status and quotation_sent_date in quotations
    $sql2 = "UPDATE quotations SET status = ?, quotation_sent_date = ? WHERE enquiry_id = ?";
    $stmt = $conn->prepare($sql2);  // Corrected from $sql1 to $sql2
    $stmt->bind_param('ssi', $status1, $quotation_sent_date, $enquiryId);
    $stmt->execute();
    $stmt->close();
  }

?>



<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Assigned Quotations </h1>
        <div class="d-flex align-items-center">
        </div>
    </div>

    <style>
        .card-quotation {

            border: none;
            border-radius: 4px;
        }

        .dots {

            height: 4px;
            width: 4px;
            margin-bottom: 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
        }


        .user-img {

            margin-top: 4px;
        }

        .check-icon {

            font-size: 17px;
            color: #c3bfbf;
            top: 1px;
            position: relative;
            margin-left: 3px;
        }

        .form-check-input {
            margin-top: 6px;
            margin-left: -24px !important;
            cursor: pointer;
        }


        .form-check-input:focus {
            box-shadow: none;
        }


        .icons i {

            margin-left: 8px;
        }

        .reply {

            margin-left: 12px;
        }

        .reply small {

            color: #b7b4b4;

        }


        .reply small:hover {

            color: green;
            cursor: pointer;

        }
    </style>
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="container-fluid mt-5">


        <div class="row d-flex justify-content-center">
    <div class="col-md-12">
        <?php
        // Adjusted SQL query to include `quotation_sent_date`
        $sql = "SELECT n.*, q.status, q.quotation_file_id, q.enquiry_id, q.quotation_sent_date
                FROM notifications n 
                LEFT JOIN quotations q ON n.quotation_no = q.quotation_no  
                WHERE msg_to = ?";

        // Prepare and execute the statement
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('i', $user_id); // Bind user_id as integer
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="card card-quotation p-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="user d-flex flex-row align-items-center">
                                <img src="./sales/img/quotation.png" width="30" class="user-img rounded-circle mr-2">
                                <span>
                                    <small class="font-weight-bold text-primary"><?php echo htmlspecialchars($row['msg_subject']); ?></small> <br>
                                    <small class="font-weight-bold"><?php echo htmlspecialchars($row['msg_content']); ?></small>
                                </span>
                            </div>
                        </div>
                        <div class="action d-flex justify-content-between mt-2 align-items-center">
                            <div class="reply px-4 mt-2">
                                <?php
                                if ($row['status'] == 'Assigned' || $row['status'] == 'Accepted') {
                                    ?>
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="file" name="attachment_file[]" id="attachment_file" multiple>
                                        <input type="hidden" name="quotation_no" value="<?php echo htmlspecialchars($row['quotation_no']); ?>">
                                        <input type="hidden" name="enquiry_id" value="<?php echo htmlspecialchars($row['enquiry_id']); ?>">
                                        <input type="hidden" name="msg_from" value="<?php echo htmlspecialchars($row['msg_from']); ?>">
                                        <button class="btn btn-sm btn-primary" type="submit" name="uploadQuotation">Upload Quotation</button>
                                    </form>
                                    <?php
                                } else {
                                    $dataArray = json_decode($row['quotation_file_id'], true);
                                    if (is_array($dataArray)) {
                                        foreach ($dataArray as $items) {
                                            foreach ($items as $item) {
                                                $id = htmlspecialchars($item['id']);
                                                $link = htmlspecialchars($item['link']);
                                                ?>
                                                <a href="<?php echo $link; ?>" target="_blank">File <?php echo $id; ?></a>
                                                <?php
                                            }
                                        }
                                    }
                                }
                                ?>
        
                            <?php
if (!empty($row['quotation_sent_date'])) {
    ?>
    <div class="mt-2">
        <strong>Quotation Sent Date:</strong> <?php echo htmlspecialchars($row['quotation_sent_date']); ?>
    </div>
    <?php
} else {
    ?>
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#quotationSentModal" data-enquiryId="<?php echo htmlspecialchars($row['enquiry_id']); ?>">Quotation Sent</button>
    <?php
}
?>
                    </div>
                          
                            <div class="icons align-items-center">
                                <small><?php echo htmlspecialchars($row['created_at']); ?></small>
                                
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No notifications found.</p>';
            }
            $stmt->close();
        }
        ?>
    </div>
</div>

<div class="modal fade" id="quotationSentModal" tabindex="-1" role="dialog" aria-labelledby="quotationSentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quotationSentModalTitle">Quotation Sent</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <div class="form-group">
              <input type="text" name="enquiry_id" id="enquiry_id" hidden readonly>
              <label for="quotation_sent_date">Select the date when the quotation was sent:</label>
              <input type="date" class="form-control" id="quotation_sent_date" name="quotation_sent_date" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" name="quotationSent">Submit</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#quotationSentModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var enquiryId = button.data('enquiryid'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#enquiryId').val(enquiryId);
      });
    });
  </script>



<?php

include 'include/footer.php';

?>
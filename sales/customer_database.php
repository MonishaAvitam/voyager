<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

// GOOGLE CALENDAR 

use Google\Client;
use Google\Service\Calendar;

$client = new Client();
$client->setAuthConfig('../google-drive/credentials.json');
$client->addScope(Calendar::CALENDAR);

// Impersonate a user
$client->setSubject('user-email@example.com');

$service = new Calendar($client);



?>

<?php include './include/sidebar.php'; ?>



<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer Database</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-body">
                <div class="table-responsive p-3  ">
                    <table id="dataTable" class="table table-striped table-bordered table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Customer</th>
                                <th>No of Projects</th>
                                <th>Latest Project</th>
                                <th>Contact Name</th>
                                <th>Phone Number</th>
                                <th>Remainder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Select</th>
                                <th>Customer</th>
                                <th>No of Projects</th>
                                <th>Latest Project</th>
                                <th>Contact Name</th>
                                <th>Phone Number</th>
                                <th>Remainder</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php
                            // SQL query
                            $sql = "SELECT 
                                    c.*, 
                                    COALESCE(COUNT(DISTINCT p.project_id), 0) + COALESCE(COUNT(DISTINCT dd.project_id), 0) AS total_projects,
                                    MAX(latest_date) AS last_project_received
                                FROM 
                                    contacts c 
                                LEFT JOIN 
                                    (SELECT contact_id, start_date AS latest_date FROM projects
                                    UNION ALL
                                    SELECT contact_id, start_date AS latest_date FROM deliverable_data) combined_dates
                                ON 
                                    c.contact_id = combined_dates.contact_id
                                LEFT JOIN 
                                    projects p ON c.contact_id = p.contact_id
                                LEFT JOIN 
                                    deliverable_data dd ON c.contact_id = dd.contact_id
                                GROUP BY 
                                    c.contact_id;
                                ";


                            $info = $obj_admin->manage_all_info($sql);
                            $num_row = $info->rowCount();

                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No projects were found</td></tr>';
                            }

                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                // Format the date if it's not null
                                $lastProjectReceived = !empty($row['last_project_received']) ? date("Y-m-d", strtotime($row['last_project_received'])) : 'N/A';
                            ?>
                                <tr>
                                    <td style="text-align:center">
                                        <input type='checkbox' name='rowCheckbox' value="<?php echo $row['contact_id'] ?>" />
                                    </td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['total_projects']); ?></td>
                                    <td><?php echo htmlspecialchars($lastProjectReceived); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_phone_number']); ?></td>
                                    <td></td>
                                    <td>
                                        <a type="button" href="edit_contacts.php?contact_id=<?php echo $row['contact_id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                            </svg>
                                        </a>&nbsp;&nbsp;
                                        <a href="" data-contact-Id="<?php echo $row['contact_id'] ?>" data-toggle="modal" data-target="#phonelogModal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z" />
                                            </svg>
                                        </a>&nbsp;&nbsp;
                                        <a href="" data-contact-Id="<?php echo $row['contact_id'] ?>" data-toggle="modal" data-target="#actions">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                                                <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z" />
                                            </svg>
                                        </a>&nbsp;&nbsp;
                                        <a href="" data-contact-Id="<?php echo $row['contact_id'] ?>" data-toggle="modal" data-target="#remainderModal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-x-fill " viewBox="0 0 16 16">
                                                <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2M6.854 8.146 8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 1 1 .708-.708" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="w-25 d-flex align-items-center justify-content-around mt-2">
                        <button type="button" onclick="toggleSelectAll(this)" class="btn btn-primary mb-3 btn-sm">Select All</button>
                        <button class="btn btn-success mb-3 btn-sm" onclick="send_checked_mail()">Send mail to selected people</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function toggleSelectAll(button) {
        const checkboxes = document.getElementsByName('rowCheckbox');
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        const newCheckedState = !allChecked;

        checkboxes.forEach(checkbox => checkbox.checked = newCheckedState);

        button.innerText = newCheckedState ? 'Deselect All' : 'Select All';
    }

    function send_checked_mail() {
        const checkboxes = document.getElementsByName('rowCheckbox');
        const selectedContactId = Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedContactId.length === 0) {
            alert('No contacts selected');
            return;
        }

        let jsonContactID = JSON.stringify(selectedContactId);

        // Create a form to send JSON data to PHP
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'jsonArray';
        input.value = jsonContactID;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
</script>


<div class="modal fade" id="actions" tabindex="-1" role="dialog" aria-labelledby="actions" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Confirm !</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you certain you want to send an email notification to the client?

            </div>
            <div class="modal-footer">
                <form method="POST">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                    <input type="text" name="contactId" id="contactId" hidden />
                    <button type="submit" class="btn btn-primary" name="send_email">Yes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#actions').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var contactId = button.data('contactId'); // Extract info from data-* attributes

            var modal = $(this); // Get the modal itself
            modal.find('#contactId').val(contactId); // Update the modal's content
        });
    });
</script>


<!-- Remainder box  -->


<div class="modal fade" id="remainderModal" tabindex="-1" role="dialog" aria-labelledby="remainderModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <form method="POST">
                    <label for="" class="control-label">Set Remainder</label>
                    <input class="form-control" type="datetime-local" name="remainder" id="">
                    <button class="btn btn-primary btn-sm mt-2" >Set</button>
                </form>
            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="phonelogModal" tabindex="-1" role="dialog" aria-labelledby="phonelogModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="phonelogModalTitle">Log Phone Call</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="phonelog">Phone Call Details</label>
                        <textarea class="form-control" id="phonelog" name="phonelog" rows="3" required></textarea>
                    </div>
                    <input type="text" name="contactId" id="contactId" hidden readonly>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="savePhonelog">Save Log</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#phonelogModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var contactId = button.data('contact-id');
            var modal = $(this);
            modal.find('#contactId').val(contactId);
        });
    });
</script>


<?php
include '../conn.php';

if (isset($_POST['savePhonelog'])) { 
    $contactId = $_POST['contactId']; 
    $phonelog = $_POST['phonelog'];

    $sql = "UPDATE sales_activities SET phone_log = ?, last_update_phone = NOW() WHERE contact_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param('si', $phonelog, $contactId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Phone log updated successfully!";
        } else {
            echo "No rows affected. Check if the ID exists.";
        }
    } else {
        echo "Execute failed: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}




//mail
if (isset($_POST['send_email']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON string from POST data and decode it to PHP array
    $jsonContactID = $_POST['jsonArray'] ?? '';
    $phpArray = json_decode($jsonContactID, true);

    // If no contact IDs from JSON, fallback to contactId in POST data
    $contactIdList = !empty($phpArray) ? $phpArray : (isset($_POST['contactId']) ? [$_POST['contactId']] : []);

    if (empty($contactIdList)) {
        die('No contact IDs provided.');
    }

    // Set up timezone and email log timestamp
    $timezone = new DateTimeZone('Asia/Kolkata');
    $email_timestamp = new DateTime('now', $timezone);
    $email_log = $email_timestamp->format('Y-m-d H:i:s');

    // Prepare the SQL statements
    $logSql = "INSERT INTO sales_activities (contact_id, email_log) VALUES (?, ?)";
    $customerSql = "SELECT contact_email, customer_name FROM contacts WHERE contact_id = ?";

    $logStmt = $conn->prepare($logSql);
    $customerStmt = $conn->prepare($customerSql);

    if ($logStmt === false || $customerStmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }



    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'engineering@csaengineering.com.au';
        $mail->Password = 'kezfduovpirmalcs';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email Content
        $mail->setFrom('revanthshiva@csaengineering.com.au', 'CSA Engineering');
        $mail->isHTML(true);
        $mail->Subject = "TEST MAIL";
        $mail->Body = "MAIL TEMPLATE";

        foreach ($contactIdList as $contactId) {
            // Log email activity
            $logStmt->bind_param('is', $contactId, $email_log);
            if (!$logStmt->execute()) {
                echo "Log execution failed: " . htmlspecialchars($logStmt->error);
                continue;
            }

            // Fetch contact details
            $customerStmt->bind_param('i', $contactId);
            $customerStmt->execute();
            $result = $customerStmt->get_result(); // Use get_result to fetch data

            if ($row = $result->fetch_assoc()) {
                $to_mail = $row['contact_email'];
                $to_name = $row['customer_name'];

                // Send email
                $mail->clearAddresses(); // Clear previous addresses
                $mail->addAddress($to_mail, $to_name);

                $mail->send();
            } else {
                "Contact ID $contactId not found.<br>";
            }

            // Free result set
            $result->free();
        }
        header('Location:./customer_database.php');

        $customerStmt->close();
        $logStmt->close();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}


?>




<?php

include './include/footer.php';

?>
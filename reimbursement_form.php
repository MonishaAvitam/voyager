<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php

require 'conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];

if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Fetch logged-in user info
$userQuery = $conn->prepare("SELECT fullname, p_team FROM tbl_admin WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();

// Fetch Finance Manager info (user_id = 171)
$fmQuery = $conn->query("SELECT fullname, email FROM tbl_admin WHERE user_id = 171");
$fmData = $fmQuery->fetch_assoc();
$financeManagerName = $fmData['fullname'] ?? '';
$fmEmail = $fmData['email'] ?? '';


// Handle form submission
if (isset($_POST['submit_reimbursement'])) {

    $expenseDate = $_POST['expenseDate'] ?? '';
    $reimbursementDetails = $_POST['reimbursementDetails'] ?? '';
    $team = $userData['p_team'] ?? '';
    $financeManagerId = 171; // fixed

    // Step 1: Insert record first with empty file paths
    $emptyFilePaths = json_encode([]);
    $stmt = $conn->prepare("INSERT INTO reimbursements (user_id, expense_date, reimbursement_details, finance_manager_id, team, file_paths) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $expenseDate, $reimbursementDetails, $financeManagerId, $team, $emptyFilePaths);

    if ($stmt->execute()) {
        $reimbursementId = $stmt->insert_id; // get inserted ID
        $stmt->close();

        $filePaths = [];

        // Step 2: Handle file uploads using reimbursementId
        if (!empty($_FILES['receipt']['name'][0])) {
            $uploadDir = "reimbursement/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['receipt']['tmp_name'] as $index => $tmpName) {
                $originalName = basename($_FILES['receipt']['name'][$index]);
                $targetFile = $uploadDir . "reimbursement{$reimbursementId}_" . pathinfo($originalName, PATHINFO_FILENAME) . "_user{$user_id}." . pathinfo($originalName, PATHINFO_EXTENSION);
                if (move_uploaded_file($tmpName, $targetFile)) {
                    $filePaths[] = $targetFile;
                }
            }
        }

        // Step 3: Update the record with file paths
        $filePathsJson = json_encode($filePaths);
        $updateStmt = $conn->prepare("UPDATE reimbursements SET file_paths = ? WHERE reimbursement_id = ?");
        $updateStmt->bind_param("si", $filePathsJson, $reimbursementId);
        $updateStmt->execute();
        $updateStmt->close();

        echo "<script>alert('Reimbursement submitted successfully!');</script>";

        // Step 4: Send email if finance manager email exists
        if ($fmEmail) {
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration for Gmail
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'chandrue.csa@gmail.com';
                $mail->Password = 'khqm ckij hwxl wiwr'; // Gmail App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Email settings
                $mail->setFrom('chandrue.csa@gmail.com', 'Reimbursement System');
                $mail->addAddress($fmEmail, 'Finance Manager');
                $mail->Subject = "New Reimbursement Claim from {$userData['fullname']}";
                $mail->isHTML(true);

                // Email body 
                $mailBody = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reimbursement Claim</title>
<style>
body {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 600px;
    margin: 20px auto;
    background-color: #ffffff;
    padding: 20px;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}
h2 {
    color: #212529;
    text-align: center;
    margin-bottom: 1rem;
}
p {
    color: #495057;
    line-height: 1.5;
}
.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: collapse;
}
.table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}
.btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    color: #fff !important;
    background-color: #0d6efd;
    border: 1px solid #0d6efd;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: 0.25rem;
    text-decoration: none;
    margin-top: 1rem;
}
.footer {
    text-align: center;
    margin-top: 2rem;
    font-size: 12px;
    color: #6c757d;
}
@media only screen and (max-width: 600px) {
    .table td {
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
}
</style>
</head>
<body>
<div class="container">
<h2>New Reimbursement</h2>
<p>Hello Finance Manager,</p>
<p>A new reimbursement claim has been submitted. Please review the details below:</p>

<table class="table">
<tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($userData['fullname']) . '</td></tr>
<tr><td><strong>Team:</strong></td><td>' . htmlspecialchars($userData['p_team']) . '</td></tr>
<tr><td><strong>Date:</strong></td><td>' . htmlspecialchars($expenseDate) . '</td></tr>
<tr><td><strong>Details:</strong></td><td>' . nl2br(htmlspecialchars($reimbursementDetails)) . '</td></tr>
</table>
';

                if (!empty($filePaths)) {
                    $mailBody .= '<p>You can access all attachments below:</p>';
                    $mailBody .= '<a href="http://localhost/CSA-System/" class="btn" target="_blank">View Details</a>';
                }

                $mailBody .= '
<p>Thanks,<br>Reimbursement System</p>
<div class="footer">&copy; ' . date("Y") . ' CSA Engineering. All rights reserved.</div>
</div>
</body>
</html>';

                $mail->Body = $mailBody;

                // Attach files
                foreach ($filePaths as $file) {
                    $mail->addAttachment($file);
                }

                $mail->send();
                echo "<script>window.location.href='welcome.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Email could not be sent. Mailer Error: " . addslashes($mail->ErrorInfo) . "');</script>";
            }
        }
    } else {
        echo "<script>alert('Error storing reimbursement: " . addslashes($stmt->error) . "');</script>";
        $stmt->close();
    }
}

?>

<!-- Reimbursement Modal -->
<div class="modal fade" id="reimbursementModal" tabindex="-1" aria-labelledby="reimbursementModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reimbursementModalLabel">Claim Reimbursement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reimbursementForm" method="POST" enctype="multipart/form-data">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expenseDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="expenseDate" name="expenseDate" required>
                        </div>
                        <div class="col-md-6">
                            <label for="employeeName" class="form-label">Name</label>
                            <input type="text" class="form-control"
                                value="<?= htmlspecialchars($userData['fullname'] ?? '') ?>" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="team" class="form-label">Team</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($userData['p_team'] ?? '') ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="reimbursementDetails" class="form-label">Reimbursement Details</label>
                        <textarea class="form-control" id="reimbursementDetails" name="reimbursementDetails" rows="3"
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="financeManager" class="form-label">Finance Manager</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($financeManagerName) ?>"
                            disabled>
                    </div>

                    <div class="mb-3">
                        <label for="receipt" class="form-label">Upload Files</label>
                        <input type="file" class="form-control" id="receipt" name="receipt[]"
                            accept=".jpg,.jpeg,.png,.pdf" multiple required>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="submit_reimbursement" form="reimbursementForm"
                    class="btn btn-primary">Submit Claim</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Update file input label with selected file names
        document.getElementById('receipt').addEventListener('change', function (e) {
            var files = Array.from(e.target.files).map(f => f.name).join(', ');
            console.log("Selected files:", files); // Optional: debug
        });

        // Set maximum date as today
        var today = new Date().toISOString().split('T')[0];
        document.getElementById("expenseDate").setAttribute('max', today);
    });
</script>
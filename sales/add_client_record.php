<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include 'include/login_header.php';
include './include/sidebar.php';


// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit();
}

// check admin
$user_role = $_SESSION['user_role'];

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</head>

<body>

    <!-- Add Client Record Modal -->
    <div class="modal fade" id="addClientModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="addClientLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientLabel">Add Potential Client Record</h5>
                    <button type="button" class="btn-close" id="closeModalBtn" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="save_client_record.php" id="addClientForm">
                        <div class="row g-3">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Office</label>
                                    <select class="form-control" name="office" id="state" required>
                                        <option value="">Select Office</option>
                                        <?php
                                        $office_sql = "SELECT `id`, `office_name`, `office_code` FROM `office`";
                                        $office_result = mysqli_query($conn, $office_sql);
                                        if ($office_result && mysqli_num_rows($office_result) > 0) {
                                            while ($office = mysqli_fetch_assoc($office_result)) {
                                                echo '<option value="' . htmlspecialchars($office['id']) . '">'
                                                    . htmlspecialchars($office['office_name']) . ', '
                                                    . htmlspecialchars($office['office_code']) .
                                                    '</option>';
                                            }
                                        }
                                        ?>
                                        <option value="N/A">Not Applicable</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone_num" class="form-control" required>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Company Code</label>
                                    <input type="text" name="company_code" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-12" style="padding:0;">
                            <div class="mb-3">
                                <label class="form-label">Account Manager</label>
                                <select name="account_manager" class="form-control" required>
                                    <option value="">-- Select Account Manager --</option>
                                    <?php
                                    $query = "SELECT `user_id`, `fullname` FROM `tbl_admin` WHERE `salesAccess` = 1";
                                    $result = mysqli_query($conn, $query);
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . $row['user_id'] . '">' . htmlspecialchars($row['fullname']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!-- Full-width Comments -->
                        <div class="mb-3 mt-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="modal-footer p-0 pt-3">
                            <button type="submit" class="btn btn-primary">Save Client</button>
                            <button type="button" class="btn btn-secondary" id="redirectBtn">Close</button>
                        </div>
                    </form>

                    <div id="successMessage" class="alert alert-success mt-3 d-none">
                        Client record has been saved successfully!
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JavaScript for Modal Behavior -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var addClientModal = new bootstrap.Modal(document.getElementById("addClientModal"), {
                backdrop: "static",
                keyboard: false
            });

            addClientModal.show();

            document.getElementById("addClientForm").addEventListener("submit", function (event) {
                event.preventDefault();

                var formData = new FormData(this);

                fetch("save_client_record.php", {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: '✅ Client record has been saved successfully!',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                                background: '#d4edda',
                                color: '#155724'
                            });

                            document.getElementById("addClientForm").reset();

                            // Redirect after 2 seconds
                            setTimeout(() => {
                                window.location.href = "potential_customer.php";
                            }, 2000);
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: data, // will show the error message
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                background: '#f8d7da',
                                color: '#721c24'
                            });
                        }

                    })
                    .catch(error => console.error("Error:", error));
            });

            document.getElementById("closeModalBtn").addEventListener("click", function () {
                addClientModal.hide();
            });

            document.getElementById("redirectBtn").addEventListener("click", function () {
                addClientModal.hide();
                window.location.href = "potential_customer.php"; // Redirect after closing the modal
            });

        });

    </script>


</body>

</html>



<?php include 'include/footer.php'; ?>
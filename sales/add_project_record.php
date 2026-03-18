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
</head>

<body>

    <!-- Add Project Record Modal -->
    <!-- Add Project Record Modal -->
    <!-- Add Project Record Modal -->
    <div class="modal fade" id="addProjectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProjectLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectLabel">Add Potential Project</h5>
                    <button type="button" class="btn-close" id="modalCloseBtn" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProjectForm">
                        <div class="mb-3">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="client_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client Contact Number</label>
                            <input type="text" name="client_contact" class="form-control" pattern="^\d{10}|\d{12}$" title="Please enter a 10 or 12-digit number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" name="client" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project Name</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                     
                        <?php
                        include '../conn.php';
                        $result = $conn->query("SELECT user_id, fullname FROM tbl_admin");
                        ?>

                        <div class="mb-3">
                            <label class="form-label">Sales Manager</label>
                            <select name="engineer" class="form-control" required>
                                <option value="">-- Select Sales Manager --</option>
                                <?php while ($row = $result->fetch_assoc()):
                                    $selected = $row['user_id'] == $user_id ? 'selected' : ''; // Check if this user_id matches the current user's id
                                ?>
                                    <option value="<?php echo $row['user_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($row['fullname']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Enquiry Details</label>
                            <textarea type="text" name="enquiry_details" rows="3" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="save_project" class="btn btn-primary">Save Project</button>
                            <button type="button" class="btn btn-secondary" id="redirectBtn">Close</button>
                        </div>
                        <div id="successMessage" class="alert alert-success mt-3 d-none">
                            Project record has been saved successfully!
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Modal Behavior -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var addProjectModal = new bootstrap.Modal(document.getElementById("addProjectModal"), {
                backdrop: "static",
                keyboard: false
            });
            addProjectModal.show();

            // Handle form submission via AJAX
            document.getElementById("addProjectForm").addEventListener("submit", function(event) {
                event.preventDefault(); // Prevent default form submission

                var formData = new FormData(this);

                fetch("save_project_record.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        document.getElementById("successMessage").classList.remove("d-none"); // Show success message

                        // Redirect to project_enquiry.php after saving
                        setTimeout(function() {
                            window.location.href = "project_enquiry.php";
                        }, 1000); // Redirect after 1.5 seconds to allow message visibility
                    })
                    .catch(error => console.error("Error:", error));
            });

            // Redirect to project_enquiry.php when "Close" button is clicked
            document.getElementById("redirectBtn").addEventListener("click", function() {
                window.location.href = "project_enquiry.php";
            });

            // Redirect to project_enquiry.php when the close (X) button is clicked
            document.getElementById("modalCloseBtn").addEventListener("click", function() {
                window.location.href = "project_enquiry.php";
            });
        });
    </script>



</body>

</html>

<?php include 'include/footer.php'; ?>
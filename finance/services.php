<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}


// check admin
$user_role = $_SESSION['user_role'];




?>

<?php include './include/sidebar.php'; ?>



<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Services</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>
   
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="card shadow mb-4 mt-3">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_new_service">Add Services</button>
            </div>

            <div class="card-body">
                <div class="table-responsive p-3">
                    <table class="table shadow-lg table-bordered table-sm" id="dataTable" name="dataTable" style="border: 1px solid #dddddd70">
                        <thead style="height:4.5rem;">
                            <tr class="bg-primary text-light my-2 text-center">
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Service Name</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Company</th>

                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Comments</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Actions</th>
                            </tr>
                        </thead>

                        <tbody style="background: #dddddd70;">
                            <!-- ... Your table rows ... -->

                            <?php
                            $sql = "SELECT s.* , v.company_name from services s LEFT JOIN vendors v on s.vendor_id = v.id  ";


                            $info = $obj_admin->manage_all_info($sql);

                            $serial  = 1;

                            $num_row = $info->rowCount();

                            if ($num_row == 0) {

                                echo '<tr><td colspan="7">No Services were found</td></tr>';
                            }

                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                            ?>


                                <script>
                                    function updateUrl(contact_id) {
                                        // Parse the current URL's query parameters
                                        const urlParams = new URLSearchParams(window.location.search);

                                        // Set the 'contact_id' parameter with the specified contact_id
                                        urlParams.set('contact_id', contact_id);

                                        // Get the updated query string
                                        const updatedQueryString = urlParams.toString();

                                        // Construct the new URL with the updated query string
                                        const newUrl = window.location.pathname + '?' + updatedQueryString;

                                        // Use pushState to update the URL without reloading the page
                                        window.history.pushState({
                                            contact_id: contact_id
                                        }, '', newUrl);

                                        // Reload the page
                                        // window.location.reload();
                                    }
                                </script>


                                <!-- Table Row -->
                                <tr>
                                    <td class="align-middle"><?php echo $row['name']  ?></td>
                                    <td class="align-middle"><?php echo $row['company_name'] ?? "N/A" ?></td>
                                    <td class="align-middle"><?php echo $row['comments']  ?></td>
                                    <td class="align-middle d-flex justify-content-center">
                                        <div>
                                            <button type="button"
                                                class="btn-sm btn text-info bg-light edit-btn"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo $row['name']; ?>"
                                                data-company="<?php echo $row['company_name']; ?>"
                                                data-comments="<?php echo $row['comments']; ?>"
                                                data-vendor-id="<?php echo $row['vendor_id']; ?>">
                                                Edit
                                            </button>&nbsp;&nbsp;

                                            <!-- Delete Button -->
                                            <a title="Delete"
                                                href="?delete_contact_id=<?php echo $row['id']; ?>"
                                                onclick="return confirm('Are you sure you want to delete this contact?');"
                                                class="btn-sm btn bg-light text-danger">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>


                            <?php } ?>

                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_GET['delete_contact_id'])) {
    $delete_id = $_GET['delete_contact_id']; // Get the ID of the service to delete

    require '../conn.php'; // Ensure the database connection is established

    // Prepare and execute the delete query
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Check if the preparation of the SQL statement was successful
    if ($stmt === false) {
        error_log('Error preparing SQL query for deletion: ' . $conn->error);
        echo "<script>alert('There was an error with the deletion. Please try again.');</script>";
        exit();
    }

    // Bind the ID parameter and execute the query
    $stmt->bind_param("i", $delete_id);

    // Execute the query and check if successful
    if ($stmt->execute()) {
        // Redirect to the same page with a success parameter
        header("Location: services.php?delete_success=1");
        exit();
    } else {
        // Redirect to the same page with an error parameter
        error_log('Error executing SQL query for deletion: ' . $stmt->error);
        header("Location: services.php?delete_error=1");
        exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>



<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="editForm" action="update_service.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Contact</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden input for service ID -->
                    <input type="hidden" name="id" id="contactId">

                    <!-- Hidden input for vendor_id -->
                    <input type="hidden" id="vendor-id" name="vendor_id" required>

                    <!-- Name input field -->
                    <div class="mb-3">
                        <label for="contactName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="contactName" name="name" required>
                    </div>

                    <!-- Vendor Dropdown in the Modal -->
                    <div class="mb-3">
                        <label for="companyName" class="form-label">Select Vendor</label>
                        <select class="form-control" id="companyName" name="vendor_id" required>
                            <option value="" disabled>Select a Vendor</option>
                            <?php
                            require '../conn.php'; // Database connection
                            $result = $conn->query("SELECT id, company_name FROM vendors");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    // Check if this vendor is the selected one when editing
                                    $selected = '';
                                    if (isset($vendor_id) && $vendor_id == $row['id']) {
                                        $selected = 'selected'; // Set the selected attribute
                                    }
                                    echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['company_name']) . "</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No vendors found</option>";
                            }
                            $conn->close(); // Close the connection
                            ?>
                        </select>
                    </div>


                    <!-- Comments input field -->
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const editModal = new bootstrap.Modal(document.getElementById("editModal"));

        editButtons.forEach(button => {
            button.addEventListener("click", function() {
                // Populate modal fields with data attributes
                document.getElementById("contactId").value = this.getAttribute("data-id");
                document.getElementById("contactName").value = this.getAttribute("data-name");

                // Set the vendor dropdown value
                const vendorId = this.getAttribute("data-vendor-id");

                // Set the selected vendor in the dropdown
                const vendorDropdown = document.getElementById("companyName");
                vendorDropdown.value = vendorId; // Set the dropdown selection to the vendor's ID
                document.getElementById("vendor-id").value = vendorId; // Set the hidden vendor ID field

                document.getElementById("comments").value = this.getAttribute("data-comments");

                // Show the modal
                editModal.show();
            });
        });
    });
</script>





<div class="modal fade" id="add_new_service" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New Service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="vendor_id">Select Vendor</label>
                        <select class="form-control" id="companyName" name="vendor_id" required>
                            <option value="">--Select a Vendor--</option>
                            <?php
                            require '../conn.php'; // Database connection
                            $result = $conn->query("SELECT id, company_name FROM vendors");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    // Check if this vendor is the selected one when editing
                                    $selected = '';
                                    if (isset($vendor_id) && $vendor_id == $row['id']) {
                                        $selected = 'selected'; // Set the selected attribute
                                    }
                                    echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['company_name']) . "</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No vendors found</option>";
                            }
                            $conn->close(); // Close the connection
                            ?>
                        </select>
                    </div>

                    <script>
                        // Filter the vendors based on input search
                        function filterVendors() {
                            const input = document.getElementById('vendorSearch');
                            const filter = input.value.toLowerCase();
                            const vendorList = document.getElementById('vendorList');
                            const items = vendorList.getElementsByTagName('li');

                            let hasVisibleItems = false;

                            for (let i = 0; i < items.length; i++) {
                                const txtValue = items[i].textContent || items[i].innerText;
                                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                                    items[i].style.display = "";
                                    hasVisibleItems = true;
                                } else {
                                    items[i].style.display = "none";
                                }
                            }

                            // Hide the list if no items match
                            if (!hasVisibleItems) {
                                vendorList.style.display = 'none';
                            } else {
                                vendorList.style.display = 'block'; // Show list when there are visible items
                            }
                        }

                        // Show all vendors when input is focused
                        function showAllVendors() {
                            const vendorList = document.getElementById('vendorList');
                            const items = vendorList.getElementsByTagName('li');

                            for (let i = 0; i < items.length; i++) {
                                items[i].style.display = ""; // Show all items
                            }
                            vendorList.style.display = 'block'; // Ensure the list is displayed
                        }

                        // Select a vendor from the list
                        function selectVendor(vendorId, vendorName) {
                            const input = document.getElementById('vendorSearch');
                            const databaseInput = document.getElementById('database-input');

                            input.value = vendorName; // Set the input value to the vendor name
                            databaseInput.value = vendorId; // Set the hidden input value to the vendor ID
                            document.getElementById('vendorList').style.display = 'none'; // Hide the vendor list
                        }

                        // Close the vendor list if clicking outside of the dropdown
                        document.addEventListener('click', function(event) {
                            const vendorList = document.getElementById('vendorList');
                            if (!vendorList.contains(event.target) && event.target.id !== 'vendorSearch') {
                                vendorList.style.display = 'none';
                            }
                        });
                    </script>

                    <div class="form-group">
                        <label for="service_name">Service Name</label>
                        <input type="text" class="form-control" id="service_name" name="service_name" required>
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                        <textarea class="form-control" id="comments" name="comments"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_new_service">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<?php
include '../conn.php';

// delete contact

if (isset($_GET['delete_contact_id'])) {
    $delete_contact_id = $_GET['delete_contact_id'];

    // SQL query to delete the project
    $sql = "DELETE FROM contacts WHERE contact_id = $delete_contact_id";

    if ($conn->query($sql) === TRUE) {
        // Display a success Toastr notification
        $msg_error = "Contact Deleted Successfully";
        header('location:contacts.php');
    } else {
        // Display an error Toastr notification with the PHP error message
        $msg_error = "Error deleting the contact: ' . $conn->error . '";
    }
}

if (isset($_POST['add_new_service'])) {
    $vendor_id = $_POST['vendor_id'];
    $service_name = $_POST['service_name'];
    $comments = $_POST['comments'];

    // SQL INSERT query to add the new service
    $sql = "INSERT INTO `services` (`vendor_id`, `name`, `comments`) VALUES (?, ?, ?)";

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $vendor_id, $service_name, $comments); // Bind parameters (all strings in this case)
        if ($stmt->execute()) {
            $msg_success = "Service Added Successfully";
            header('Location: services.php'); // Redirect to contacts.php on success
            exit();
        } else {
            $msg_error = "Error in adding the service: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg_error = "Error preparing the statement: " . $conn->error;
    }
}



?>










<?php

include './include/footer.php';

?>
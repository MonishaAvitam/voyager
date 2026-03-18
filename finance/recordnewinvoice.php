<?php
// init.php

// Start the session at the very beginning

// Include other necessary files
include '../authentication.php'; // Adjust the path as necessary
include '../conn.php'; // Database connection

include './include/login_header.php';

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
    exit; // Make sure to exit after a header redirect
}



// Check admin
$user_role = $_SESSION['user_role'];
if ($user_role == 4) {
    header('Location:./payslip.php');
    exit; // Make sure to exit after a header redirect
}

include './include/sidebar.php';


?>

<style>
    .selected-service {
        display: inline-block;
        padding: 5px 10px;
        margin: 5px;
        background-color: #f1f1f1;
        color: black;
        border: 1px solid #ccc;
        border-radius: 15px;
        position: relative;
    }

    .selected-service span {
        margin-left: 10px;
        cursor: pointer;
        color: red;
    }
</style>

<!-- Modal Structure -->
<div class="modal fade" id="quickProjectModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="quickProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickProjectModalLabel">New Invoice</h5>
            </div>
            <form id="invoiceForm" action="" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="project_id">RAE Id</label>
                        <input type="number" class="form-control" id="project_id" placeholder="Enter RAE Numbers" name="project_id" required>
                    </div>
                    <div class="form-group">
                        <label for="invoice_no">Invoice Number</label>
                        <input type="text" class="form-control" id="invoice_no" placeholder="Enter Invoice Number" name="invoice_no" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Invoice Date</label>
                        <input type="date" class="form-control" id="date" placeholder="Enter Invoice Date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" class="form-control" id="amount" placeholder="Enter Invoice Amount" step="0.01" min="0" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                        <textarea class="form-control" id="comments" rows="3" placeholder="Enter Description" name="comments" ></textarea>
                    </div>

                    <!-- Vendor Dropdown -->
                    <div class="form-group">
                        <label for="vendor">Vendor</label>
                        <select class="form-control" id="vendor" name="vendor" required>
                            <option value="">Select Vendor</option>
                            <?php
                            $stmt = $conn->prepare("SELECT id, company_name FROM vendors");
                            if ($stmt) {
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['id'], ENT_QUOTES) . '">' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '</option>';
                                }
                                $stmt->close();
                            } else {
                                echo '<option value="">Error fetching vendors</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Selected services preview -->

                    <!-- Services Dropdown (Updated for Multiple Selection) -->
                    <div class="form-group">
                        <label for="serviceDropdown">Select Service</label>
                        <div id="selectedServices" class="">
                            <!-- Selected services will appear here -->
                        </div>
                        <select class="form-control mt-3" id="serviceDropdown">
                            <option value="">Select a Service</option>
                            <?php
                            $stmt = $conn->prepare("SELECT id, name FROM services");
                            if ($stmt) {
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['id'], ENT_QUOTES) . '">' . htmlspecialchars($row['name'], ENT_QUOTES) . '</option>';
                                }
                                $stmt->close();
                            } else {
                                echo '<option value="">Error fetching services</option>';
                            }
                            ?>
                        </select>
                    </div>




                    <div class="form-group">
                        <label for="invoice_status">Status</label>
                        <select class="form-control" id="invoice_status" name="invoice_status">
                            <option value="unpaid" selected>Unpaid</option>
                            <option value="readytopay">Ready To Pay</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const serviceDropdown = document.getElementById('serviceDropdown');
    const selectedServicesContainer = document.getElementById('selectedServices');
    const selectedServices = new Set(); // To store unique services

    serviceDropdown.addEventListener('change', () => {
        const selectedOption = serviceDropdown.options[serviceDropdown.selectedIndex];
        const serviceId = selectedOption.value;
        const serviceName = selectedOption.text;

        if (serviceId && !selectedServices.has(serviceId)) {
            // Add service to the set
            selectedServices.add(serviceId);

            // Create service preview element
            const serviceElement = document.createElement('div');
            serviceElement.classList.add('selected-service');
            serviceElement.setAttribute('data-id', serviceId);
            serviceElement.innerHTML = `
                    ${serviceName} <span>&times;</span>
                `;

            // Add event listener to remove service
            serviceElement.querySelector('span').addEventListener('click', () => {
                selectedServices.delete(serviceId); // Remove from the set
                serviceElement.remove(); // Remove from UI
            });

            // Append to the container
            selectedServicesContainer.appendChild(serviceElement);
        }

        // Reset dropdown
        serviceDropdown.value = '';
    });

    document.getElementById('invoiceForm').addEventListener('submit', (e) => {
        const serviceList = Array.from(selectedServices);

        // If no services are selected, show an alert and prevent form submission
        if (serviceList.length === 0) {
            alert('Please select at least one service.');
            e.preventDefault();
            return;
        }

        // Clear any previously added hidden inputs
        document.querySelectorAll('input[name="services[]"]').forEach((input) => input.remove());

        // Add hidden inputs for each service
        serviceList.forEach((serviceId) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'services[]'; // Use array notation
            hiddenInput.value = serviceId;
            document.getElementById('invoiceForm').appendChild(hiddenInput);
        });
    });
});


</script>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Script to Automatically Open the Modal -->
<script>
    $(document).ready(function() {
        // Automatically show the modal
        $('#quickProjectModal').modal('show');

        // Redirect to previous page when the modal is closed
        $('#quickProjectModal').on('hidden.bs.modal', function() {
            window.history.back(); // Go back to the previous page in history
        });
    });
</script>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $project_id = $_POST['project_id'] ?? null;
    $invoice_no = $_POST['invoice_no'] ?? null;
    $comments = $_POST['comments'] ?? null;
    $vendor_id = $_POST['vendor'] ?? null;
    $services = $_POST['services'] ?? null; // This is an array of service IDs
    $invoice_date = $_POST['date'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $invoice_status = $_POST['invoice_status'] ?? 'unpaid';

    // Check if invoice number already exists in any table
    $check_queries = [
        'ready_to_pay' => "SELECT 'found' FROM ready_to_pay WHERE invoice_no = ? AND project_id = ? ",
        'paidinvoices' => "SELECT 'found' FROM paidinvoices WHERE invoice_no = ? AND project_id = ? ",
        'unpaidinvoices' => "SELECT 'found' FROM unpaidinvoices WHERE invoice_no = ? AND project_id = ? "
    ];

    foreach ($check_queries as $table => $query) {
        $check_stmt = $conn->prepare($query);
        if (!$check_stmt) {
            die('Error preparing check statement: ' . $conn->error);
        }

        $check_stmt->bind_param("ss", $invoice_no,$project_id );
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('Invoice number already exists in table: $table. with same RAE Id'); window.history.back();</script>";
            $check_stmt->close();
            exit; // Prevent further execution
        }

        $check_stmt->close();
    }

    // Determine the target table
    if ($invoice_status === 'readytopay') {
        $table = 'ready_to_pay';
    } elseif ($invoice_status === 'paid') {
        $table = 'paidinvoices';
    } else {
        $table = 'unpaidinvoices';
    }

    // Insert invoice details (excluding services)
    $sql = "INSERT INTO $table (project_id, invoice_no, comments, vendor_id, invoice_date, amount) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die('Error preparing statement: ' . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "ssssss",
        $project_id,
        $invoice_no,
        $comments,
        $vendor_id,
        $invoice_date,
        $amount
    );

    // Execute the statement for invoice
    if (!$stmt->execute()) {
        echo '<script>alert("Error saving invoice: ' . $stmt->error . '"); window.history.back();</script>';
        $stmt->close();
        exit;
    }

    // Get the inserted invoice ID for reference (if needed)
    $insert_id = $stmt->insert_id;

    $stmt->close();

    if (isset($services) && is_array($services) && !empty($services)) {
        // Convert the array to JSON format
        $services_json = json_encode($services);

        // Update services linked to the invoice
        $service_sql = "UPDATE $table SET service_id = ? WHERE id = ?";
        $service_stmt = $conn->prepare($service_sql);

        if (!$service_stmt) {
            die('Error preparing service statement: ' . $conn->error);
        }

        // Bind parameters
        $service_stmt->bind_param("si", $services_json, $insert_id);

        if (!$service_stmt->execute()) {
            echo '<script>alert("Error saving service: ' . $service_stmt->error . '"); window.history.back();</script>';
            $service_stmt->close();
            exit;
        }

        $service_stmt->close();
    }

    // Success message
    echo '<script>alert("Invoice and services successfully saved."); window.location.href="unpaid.php";</script>';
}



// Close the database connection
?>

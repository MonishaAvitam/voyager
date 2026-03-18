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

$customers = [];
$sql = "SELECT customer_id, contact_id, contact_name, customer_name FROM contacts"; // Assuming customer_id is needed for the backend
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row; // Store each customer in the array
    }
} else {
    echo "Error fetching customers: " . $conn->error;
}

// Check admin
$user_role = $_SESSION['user_role'];
if ($user_role == 4) {
    header('Location:./payslip.php');
    exit; // Make sure to exit after a header redirect
}

include './include/sidebar.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $project_status = $_POST['project_status'];
        $state = $_POST['state'];
        // Create a new PDO instance
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare data and set defaults to NULL if not provided
        $data = [
            'project_name' => $_POST['projectName'] ?? null,
            'project_details' => $_POST['projectDescription'] ?? null,
            'project_manager' => null, // Adjust if needed
            'start_date' => date('Y-m-d'),
            'assign_to_id' => null,
            'assign_to' => null,
            'urgency' => $project_status ?? null,
            'verify_by' => null,
            'verify_by_name' => null,
            'verify_status' => null,
            'deliverable_status' => null,
            'end_date' => null,
            'EPT' => null,
            'progress' => null,
            'p_team' => 'Count',
            'project_manager_status' => 1,
            'assign_status' => null,
            'checker_status' => null,
            'assign_to_status' => 3,
            'project_managers_id' => null,
            'contact_id' => $_POST['contact_id'] ?? null,
            'reopen_status' => null,
            'file_access' => null,
            'additional_engineers' => null,
            'setTarget' => null,
            'setTargetDate' => null,
            'revision_project_id' => null,
            'quick_project' => 'yes',
            'state' => $state
        ];

        // Insert into the database
        $sql = "INSERT INTO projects (" . implode(', ', array_keys($data)) . ") 
                VALUES (:" . implode(', :', array_keys($data)) . ")";

        // Prepare and execute the statement
        $stmt = $pdo->prepare($sql);

        // Bind parameters dynamically
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();

        // Get the last inserted project ID
        $lastInsertedId = $pdo->lastInsertId();

        // Prepare data for csa_finance_readytobeinvoiced
        // $financeData = [
        //     'project_id' => $lastInsertedId,
        //     'date' => date('Y-m-d'),
        //     'price' => $_POST['price'] ?? null, // Example: Replace with appropriate form data
        //     'comments' => $_POST['comments'] ?? null,
        //     'project_status' => $project_status,
        //     'last_modified_date' => date('Y-m-d H:i:s'),
        //     'rownumber' => null,
        //     'service_date' => $_POST['service_date'] ?? null,
        //     'due_date' => $_POST['due_date'] ?? null,
        // ];

        // $financeSql = "INSERT INTO csa_finance_uninvoiced (" . implode(', ', array_keys($financeData)) . ") 
        //                VALUES (:" . implode(', :', array_keys($financeData)) . ")";

        // $financeStmt = $pdo->prepare($financeSql);

        // foreach ($financeData as $key => $value) {
        //     $financeStmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        // }

        // $financeStmt->execute();

        // Set a session variable for success message
        // $_SESSION['success_message'] = "Project successfully added and copied to finance table!";

        // Redirect to readyToBeInvoiced.php
        // Set success message
        $_SESSION['success_message'] = "Project successfully added! Project ID: $lastInsertedId";

        // Redirect to the success page
        header('Location: unInvoiced.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


<!-- Modal Structure -->
<div class="modal fade" id="quickProjectModal" tabindex="-1" role="dialog" aria-labelledby="quickProjectModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickProjectModalLabel">Quick Project</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickProjectForm" action="" method="POST"> <!-- Action set to empty -->
                <div class="modal-body">
                    <div class="form-group">
                        <label for="state">State</label>
                        <select class="form-control" name="state" id="state" required>
                            <option value="">Select State</option>
                            <Option value="QLD">QLD (Queensland)</Option>
                            <Option value="NSW">NSW (New South Wales)</Option>
                            <Option value="WA">WA (Western Australia)</Option>
                            <Option value="SA">SA (South Australia)</Option>
                            <Option value="VICTORIA">VICTORIA</Option>
                            <Option value="N/A">Not Applicable</Option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="projectName">Project Name</label>
                        <input type="text" class="form-control" id="projectName" placeholder="Enter Project Name" name="projectName" required>
                    </div>
                    <div class="form-group">
                        <label for="projectDescription">Project Description</label>
                        <textarea class="form-control" id="projectDescription" rows="3" placeholder="Enter Project Description" name="projectDescription" required></textarea>
                    </div>
                    <div class="form-group position-relative">
                        <label for="customerSearch">Contact Person</label>
                        <input type="text" class="form-control" id="customerSearch" placeholder="Search Customer" onkeyup="filterCustomers()" onfocus="showAllCustomers() " required>
                        <input type="text" name="contact_id" style="display:none" id="database-input" required />

                        <div id="customerList" class="list-group position-absolute w-100" style="display: none; max-height: 200px; overflow-y: auto;"> <!-- Set max height and enable overflow -->
                            <ul class="list-group">
                                <?php foreach ($customers as $customer): ?>
                                    <li class="list-group-item list-group-item-action"
                                        data-id="<?php echo $customer['contact_id']; ?>"
                                        onclick="selectCustomer('<?php echo $customer['contact_id']; ?>', '<?php echo htmlspecialchars($customer['contact_name']).', '.htmlspecialchars($customer['customer_name']); ?>')">
                                        <?php echo htmlspecialchars($customer['contact_name'] ?$customer['contact_name'] : "Unknown Person"). ", ". htmlspecialchars($customer['customer_name']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <script>
                        function filterCustomers() {
                            const input = document.getElementById('customerSearch');
                            const filter = input.value.toLowerCase();
                            const customerList = document.getElementById('customerList');
                            const items = customerList.getElementsByTagName('li');

                            customerList.style.display = 'block'; // Show list on input

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
                                customerList.style.display = 'none';
                            }
                        }

                        function showAllCustomers() {
                            const customerList = document.getElementById('customerList');
                            const items = customerList.getElementsByTagName('li');

                            // Show all customers when input is focused
                            for (let i = 0; i < items.length; i++) {
                                items[i].style.display = ""; // Show all items
                            }
                            customerList.style.display = 'block'; // Ensure the list is displayed
                        }

                        function selectCustomer(contactId, customerName) {
                            const input = document.getElementById('customerSearch');
                            const dataBaseInput = document.getElementById('database-input')
                            input.value = customerName; // Set input value
                            dataBaseInput.value = contactId;
                            document.getElementById('customerList').style.display = 'none'; // Hide the list
                            // Optionally, set the selected value to a hidden input if needed
                            // document.getElementById('selectedCustomerId').value = contactId;
                        }

                        // Optional: Hide the customer list when clicking outside
                        document.addEventListener('click', function(event) {
                            const customerList = document.getElementById('customerList');
                            if (!customerList.contains(event.target) && event.target.id !== 'customerSearch') {
                                customerList.style.display = 'none';
                            }
                        });
                    </script>

                    <div class="form-group">

                        <label class="control-label" for="project_status">Project Status</label>

                        <div class="">

                            <select class="form-control" id="project_status" name="project_status" required>

                                <option value="white">Don't Start the Project</option>

                                <option value="green">Ready to Start the Project</option>

                                <option value="purple">Closed</option>

                                <option value="orange">Urgent</option>

                                <option value="red">Very Urgent</option>

                                <option value="yellow">HOLD</option>

                            </select>


                        </div>

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
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';
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



<style>
    .fixedheader {
        position: sticky;
        z-index: 1;
        top: -0.2rem;
        background: lightgray;
        font-weight: bold;
        font-size: 1rem;
        color: black;
    }

    .table-container {
        max-height: 95vh;
        overflow-y: auto;
    }

    .table-container::-webkit-scrollbar {
        display: none;
    }


    .info-icon {
        transition: all 0.2s ease-in-out;

    }


    .info-icon:hover {
        transform: scale(1.4);

    }


    .quotation-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: #0d6efd;
        border: 1.5px solid #0d6efd;
        transition: 0.3s ease-out;
    }

    .quotation-btn:hover {
        background: #0d6efd;
        transform: scale(1.2);

        color: white;
    }

    .badge.dark-green {
        background-color: rgb(36, 187, 36);
        color: white;
    }

    .badge.dark-orange {
        background-color: rgb(255, 163, 50);
        color: white;
    }

    .badge.dark-red {
        background-color: rgb(250, 42, 42);
        color: white;
    }

    .badge.badge-lavender {
        background-color: #E6E6FA !important;
        /* Lavender color */
        color: black;
    }
</style>


<!-- add secuirty to prevent this page  -->
<?php
// Assume $conn is your active MySQLi connection
// And $user_id is from the session: $_SESSION['admin_id']

// Prepare the query
$stmt = $conn->prepare("SELECT user_id, obiAdmin FROM tbl_admin WHERE user_id = ?");
$stmt->bind_param("i", $user_id); // "i" = integer
$stmt->execute();

// Get result
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userId = $row['user_id'];
    $obiAdmin = $row['obiAdmin'];

    // Check if obiAdmin is not 1
    if ($obiAdmin != 1) {
        // Redirect back using JavaScript
        echo "<script>
                    alert('You do not have permission to access this page.');
                    window.history.back();
                </script>";
        exit(); // Stop executing the rest of the page
    }
} else {
    echo "<script>
                alert('No admin found.');
                window.history.back();
            </script>";
    exit();
}

// Close statement
$stmt->close();
?>




<div class="mx-auto position-relative" style="width: 97.5%;">
    <div class="d-sm-flex align-items-center justify-content-between">
        <div>
            <h3>Recycle Bin</h3>
        </div>
        <div>
            <a href="potential_customer.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>


<!-- Tabs -->
<ul class="nav nav-tabs justify-content-center mb-4 d-none" id="leadsTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link  px-4 py-2 rounded-pill" id="all-leads-tab" data-bs-toggle="tab" type="button"
            role="tab" style="font-weight: 600; font-size: 1.2rem;">
            All Leads
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active px-4 py-2 rounded-pill" id="my-leads-tab" data-bs-toggle="tab" type="button"
            role="tab" style="font-weight: 600; font-size: 1.2rem;">
            My Leads
        </button>
    </li>
</ul>


<!-- Dropdown above the table -->
<div id="accountManagerFilterWrapper" class="mt-5">
    <label for="accountManagerFilter" class="form-label mr-2">Filter by Account Manager:</label>
    <select id="accountManagerFilter" class="form-select w-auto d-inline-block mr-4">
        <option value="">-- All Account Managers --</option>
        <?php
        $query = "SELECT `fullname`, `user_id` FROM `tbl_admin` WHERE `salesAccess`=1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row['user_id'] . '">' . htmlspecialchars($row['fullname']) . '</option>';
            }
        }
        ?>
    </select>
</div>

<div class="table-container mx-auto" style="max-height: 100vh; overflow-y: auto; width: 98%;">
    <table class="table text-center table-sm table-striped" id="customer-table" style="width:100%;">
        <thead class="fixedheader">
            <tr class="text-light align-center font-weight-normal text-center fixedheader"
                style="height: 3.5rem; background: #133e87;">
                <th width="6%" class="align-middle text-center">Last Update</th>
                <th width="" class="align-middle text-center">Customer ID</th>
                <th width="" class="align-middle text-center">Company Name</th>
                <th width="" class="align-middle text-center">Company Code</th>
                <th width="" class="align-middle text-center">Contact Person</th>
                <th width="6%" class="align-middle text-center">Date</th>
                <th width="" class="align-middle text-center">Address</th>
                <th width="" class="align-middle text-center">Phone</th>
                <th width="" class="align-middle text-center">Account Manager</th>
                <th width="" class="align-middle text-center">Email</th>
                <th width="" class="align-middle text-center">Actions</th>
            </tr>
        </thead>
        <tbody style="background: rgb(245, 245, 245);">
        </tbody>
    </table>
</div>



<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr style="background: #133e87; color: white;">
                            <th class="text-center align-middle">Id</th>
                            <th class="text-center align-middle">Last Updated</th>
                            <th class="text-center align-middle">Records</th>
                        </tr>
                    </thead>
                    <tbody id="recordTableBody">


                    </tbody>
                </table>
                <div style="width: 90%" class="mx-auto mt-2">
                    <label for="recordTextArea" class="form-label">New Record:</label>
                    <textarea class="form-control" id="recordTextArea" rows="3"
                        placeholder="Enter new update..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success bg-gradient btn-sm" id="addRecordBtn"> + Add
                    Record</button>
                <button type="button" class="btn btn-secondary bg-gradient btn-sm"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Account Manager Modal -->
<div class="modal fade" id="accountManagerModal" tabindex="-1" aria-labelledby="accountManagerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="accountManagerModalLabel">Update Account Manager</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="accountManagerForm">
                    <div class="mb-3">
                        <label for="accountManagerDropdown" class="form-label">Select Account Manager</label>
                        <?php
                        // Fetch account managers from database
                        $manager_sql = "SELECT `user_id`, `fullname` FROM `tbl_admin` WHERE `salesAccess`=1";
                        $manager_result = mysqli_query($conn, $manager_sql);

                        $managers = [];
                        if ($manager_result && mysqli_num_rows($manager_result) > 0) {
                            while ($row = mysqli_fetch_assoc($manager_result)) {
                                $managers[] = $row;
                            }
                        }
                        ?>

                        <select class="form-select" id="accountManagerDropdown" required>
                            <option value="">-- Select Manager --</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?= htmlspecialchars($manager['user_id']) ?>">
                                    <?= htmlspecialchars($manager['fullname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Debug output -->

                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAccountManagerBtn">Save</button>
            </div>

        </div>
    </div>
</div>




<!-- Confirmation Modal -->
<div class="modal fade" id="convertModal" tabindex="-1" aria-labelledby="convertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="convertModalLabel">Confirm Conversion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to convert this potential client into a
                <strong>CSA client</strong>?
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary confirm-convert-btn">Yes, Convert</button>
            </div>

        </div>
    </div>
</div>

<style>
    .collaborator-stack img {
        width: 20px;
        height: 20px;
        border: 2px solid white;
        border-radius: 50%;
        position: relative;
        margin-left: -8px;
        /* This creates the overlapping effect */
        transition: all 0.2s ease;
    }

    .collaborator-stack img:first-child {
        margin-left: 0;
    }

    .collaborator-stack img:hover {
        z-index: 10;
        transform: scale(1.2);
    }

    .collaborator-stack .more-count {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #6c757d;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        margin-left: -8px;
        border: 2px solid white;
    }
</style>

<!-- Delete Confirmation Modal -->
<!-- Restore Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to restore this record (ID:
                <strong id="deleteCustomerId">N/A</strong>)?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-success">Yes, Restore</button>
            </div>

        </div>
    </div>
</div>

<script>

    function getDateColorClass(dateValue) {
        if (!dateValue || dateValue === 'N/A') {
            return 'badge-lavender'; // Assign lavender badge if no date is available
        }

        if (dateValue === 'New') {
            return 'badge-new';
        }

        const givenDate = new Date(dateValue); // Convert to Date object
        const today = new Date(); // Get current date

        // Convert both dates to UTC for accuracy
        const givenDateUTC = Date.UTC(givenDate.getFullYear(), givenDate.getMonth(), givenDate.getDate());
        const todayUTC = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());

        // Calculate difference in days
        const diffDays = Math.floor((todayUTC - givenDateUTC) / (1000 * 60 * 60 * 24));

        console.log(`Given Date: ${dateValue}, Days Difference: ${diffDays}`);

        if (diffDays <= 30) {
            return 'dark-green';
        } else if (diffDays > 30 && diffDays <= 45) {
            return 'dark-orange';
        } else {
            return 'dark-red';
        }
    }


    let selectedCustomerId = null;
    const userId = <?php echo $user_id ?>;

    // your existing functions like getDateColorClass() etc...

    // initial fetch (page load)
    const activeTab = document.querySelector('.nav-link.active')?.id || "";
    const urlParams = new URLSearchParams(window.location.search);
    const officeId = urlParams.get('office_id') || <?php echo $office_id ?? 0; ?>; // use PHP office_id if URL param missing
    let apiUrl = `./fetch_potential_customer.php?archived=1`; // Fetch by office only



    console.log("Active Tab:", activeTab);
    console.log("User ID:", userId);
    console.log("API URL:", apiUrl);





    // ⬇️ Add this block at the end ⬇️
    document.querySelectorAll('#leadsTab .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const activeTab = e.target.id;
            let apiUrl = `./fetch_potential_customer.php?archived=1`; // Fetch archived clients only

            console.log("Switched Tab:", activeTab);
            console.log("API URL:", apiUrl);

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Debugging line to check the API response

                    // Extract potential_customers from the response
                    const potentialCustomers = data.archive_clients;

                    // Ensure potentialCustomers is an array and not empty
                    if (!Array.isArray(potentialCustomers) || potentialCustomers.length === 0) {
                        const tableBody = document.querySelector('#customer-table tbody');
                        tableBody.innerHTML = "<tr><td colspan='9'>No data available</td></tr>";
                        return;
                    }

                    // Clear the table before inserting new rows
                    const tableBody = document.querySelector('#customer-table tbody');
                    // ✅ Destroy DataTable before clearing (fix switching tabs)
                    if ($.fn.DataTable.isDataTable('#customer-table')) {
                        $('#customer-table').DataTable().clear().destroy();
                    }
                    tableBody.innerHTML = '';

                    // Loop through potential customers and populate table
                    potentialCustomers.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.setAttribute('data-customer-id', row.id); // Add customer ID as a data attribute
                        const lastUpdateClass = getDateColorClass(row.last_update); // Apply function to last_update column
                        let badgeText = "New";
                        if (lastUpdateClass === "dark-green") {
                            badgeText = "Within Time";
                        } else if (lastUpdateClass === "dark-orange") {
                            badgeText = "Overdue";
                        } else if (lastUpdateClass === "dark-red") {
                            badgeText = "Overdue";
                        }

                        tr.innerHTML = `
                <td class="text-center align-middle">
                            ${row.last_update ? row.last_update.split(' ')[0] : ''}
                            <br>
                            <span class="badge ${lastUpdateClass}">${badgeText}</span>
                        </td>
    
                    <td class="text-center align-middle ">${row.id || 'N/A'}</td>
                    <td class="text-center align-middle px-3">${row.company_name || 'N/A'}</td>
                    <td class="text-center align-middle px-3">${row.company_code || 'N/A'}</td>
                    <td class="text-center align-middle">${row.name || 'N/A'}</td>
                    <td class="text-center align-middle">${row.date || 'N/A'}</td> 
                    
                    <td class="text-center align-middle">${row.address || 'N/A'}</td>  
                
                                    <td class="text-center align-middle">${row.phone_num || 'N/A'}</td>
                        <td class="text-center align-middle account-manager-cell" 
                            data-customer-id="${row.id}" 
                        data-account-manager="${row.account_manager_id || ''}" 
                        role="button">
                        ${row.account_manager || 'N/A'}
                    </td>



                    <td class="text-center align-middle ">${row.email || 'N/A'}</td>
                
                <td class="align-middle">
                <span class="restore-record-btn extra-action-btn" role="button" 
      data-bs-id="${row.id}"  
      data-bs-toggle="modal" data-bs-target="#deleteConfirmModal"
      data-tippy-content="Restore">

    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" 
                            viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" 
                                stroke-width="2" d="m16 10 3-3m0 0-3-3m3 3H5v3m3 4-3 3m0 0 3 3m-3-3h14v-3"/>
                        </svg>
</span>


            </td>








                `;

                        tableBody.appendChild(tr);

                        // In your sidebar JS (run once on page load)
                        tippy.delegate(document.body, {
                            target: '[data-tippy-content]',
                            placement: 'top',
                            animation: 'shift-away',
                            theme: 'light-border',
                            duration: [200, 0],
                        });


                    });

                    // After populating table rows
                    // After populating rows
                    $(document).ready(function () {
                        if ($.fn.DataTable.isDataTable('#customer-table')) {
                            $('#customer-table').DataTable().destroy();
                        }

                        const table = $('#customer-table').DataTable({
                            scrollY: "75vh",
                            scrollX: true,
                            scrollCollapse: true,
                            paging: true,
                            pageLength: 25,
                            lengthMenu: [10, 25, 50, 100],
                            order: [[1, "desc"]],
                            columnDefs: [{ orderable: false, targets: -1 }],
                            fixedHeader: true,
                            language: {
                                emptyTable: "No data available",
                                search: "Search:",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                infoEmpty: "Showing 0 to 0 of 0 entries",
                                paginate: { first: "First", last: "Last", next: "Next", previous: "Prev" },
                            }
                        });
                        $('#accountManagerFilter, label[for="accountManagerFilter"]').prependTo('#customer-table_wrapper .dataTables_filter');


                        // ✅ Add this block for account manager filter
                        $.fn.dataTable.ext.search.push(
                            function (settings, data, dataIndex) {
                                const selectedManagerId = $('#accountManagerFilter').val();
                                if (!selectedManagerId) return true; // no filter, show all

                                // Get the table row
                                const rowNode = $('#customer-table').DataTable().row(dataIndex).node();
                                const rowManagerId = $(rowNode).find('.account-manager-cell').data('account-manager');

                                return rowManagerId == selectedManagerId;
                            }
                        );

                        $('#accountManagerFilter').on('change', function () {
                            $('#customer-table').DataTable().draw();
                        });

                    });



                    document.addEventListener('click', function (event) {
                        if (event.target.classList.contains('view-record-btn') || event.target.classList.contains('add-new-record-btn')) {
                            const row = event.target.closest('tr');
                            selectedCustomerId = row.getAttribute('data-customer-id');
                            console.log("Selected Customer ID:", selectedCustomerId);

                            // Fetch and display customer records in the modal
                            fetchCustomerRecords(selectedCustomerId);
                        }
                    });



                    // Add click event listener for the "View" button
                    document.querySelectorAll('.quotation-btn').forEach(button => {
                        button.addEventListener('click', function () {
                            const row = this.closest('tr');
                            selectedCustomerId = row.getAttribute('data-customer-id');
                            console.log("Selected Customer ID:", selectedCustomerId);

                            // Fetch and display records for the selected customer
                            fetchCustomerRecords(selectedCustomerId);
                        });
                    });
                })
                .catch(error => console.error('Error fetching data:', error));

        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        // 🔥 directly trigger the shown.bs.tab event on page load
        const allLeadsTab = document.querySelector("#all-leads-tab");

        if (allLeadsTab) {
            const event = new Event("shown.bs.tab", { bubbles: true });
            allLeadsTab.dispatchEvent(event);
            console.log("✅ Default All Leads loaded on page load");
        }
    });





    let accountManagerModalInstance = null;

    document.addEventListener('click', function (e) {
        const cell = e.target.closest('.account-manager-cell');
        if (!cell) return;

        selectedCustomerId = cell.getAttribute('data-customer-id');
        const currentManagerId = cell.getAttribute('data-account-manager'); // manager ID
        console.log(currentManagerId)
        const dropdown = document.getElementById('accountManagerDropdown');
        dropdown.value = currentManagerId || "";

        // Update debug span

        // Show modal
        const modalEl = document.getElementById('accountManagerModal');
        accountManagerModalInstance = new bootstrap.Modal(modalEl);
        accountManagerModalInstance.show();
    });

    document.getElementById('saveAccountManagerBtn').addEventListener('click', function () {
        const dropdown = document.getElementById('accountManagerDropdown');
        const managerId = dropdown.value;
        const managerName = dropdown.options[dropdown.selectedIndex].text;

        if (!managerId) {
            alert('Please select an account manager.');
            return;
        }

        const formData = new FormData();
        formData.append('customer_id', selectedCustomerId);
        formData.append('account_manager', managerId); // send ID to PHP

        fetch('update_account_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                console.log('PHP Response:', data);

                if (data.success) {
                    alert('✅ ' + data.message);

                    // Update table cell dynamically
                    const cell = document.querySelector(`.account-manager-cell[data-customer-id='${selectedCustomerId}']`);
                    if (cell) {
                        cell.innerText = managerName; // show name
                        cell.setAttribute('data-account-manager', managerId); // store ID
                    }

                    // Close modal
                    if (accountManagerModalInstance) {
                        accountManagerModalInstance.hide();
                    }

                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('❌ Error connecting to server.');
            });
    });








    // Confirm conversion
    document.querySelector('.confirm-convert-btn').addEventListener('click', function () {
        if (selectedCustomerId) {
            fetch('convert_customer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(selectedCustomerId)
            })
                .then(res => res.json())
                .then(data => {
                    console.log(data);

                    if (data.success) {
                        alert('✅ ' + data.message);
                    } else {
                        alert('❌ ' + data.message);
                    }

                    // Close modal after action (Bootstrap 4 style)
                    $('#convertModal').modal('hide');

                    // Reload page once modal is fully hidden
                    $('#convertModal').on('hidden.bs.modal', function () {
                        window.location.reload();
                    });
                })
                .catch(err => {
                    console.error(err);
                    alert('❌ Error connecting to server');
                });
        }
    });
    document.addEventListener("DOMContentLoaded", () => {
        let deleteCustomerId = null;

        // When trash icon is clicked
        document.addEventListener("click", function (e) {
            const btn = e.target.closest(".delete-record-btn");
            if (btn) {
                deleteCustomerId = btn.getAttribute("data-bs-id");
                document.getElementById("deleteCustomerId").textContent = deleteCustomerId;
            }
        });

        // When confirm delete is clicked
        document.getElementById("confirmDeleteBtn").addEventListener("click", () => {
            if (!deleteCustomerId) return;

            fetch(`./delete_customer.php`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${deleteCustomerId}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Remove row from table
                        const row = document.querySelector(`tr[data-customer-id="${deleteCustomerId}"]`);
                        if (row) row.remove();

                        // Hide modal using Bootstrap 4
                        $('#deleteConfirmModal').modal('hide');

                        // Show success message
                        alert(data.message); // You can replace this with a nicer toast if needed

                    } else {
                        alert(data.message || "Failed to delete record.");
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    alert("An error occurred while deleting the record.");
                });
        });
    });


    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".restore-record-btn");
        if (btn) {
            selectedCustomerId = btn.getAttribute("data-bs-id");

            // Update modal content dynamically
            document.getElementById("deleteConfirmModalLabel").textContent = "Confirm Restore";
            document.getElementById("confirmDeleteBtn").textContent = "Yes, Restore";
            document.getElementById("confirmDeleteBtn").classList.remove("btn-danger");
            document.getElementById("confirmDeleteBtn").classList.add("btn-success");

            document.getElementById("deleteCustomerId").textContent = selectedCustomerId;
        }
    });

    document.getElementById("confirmDeleteBtn").addEventListener("click", () => {
        if (!selectedCustomerId) return;

        fetch(`./restore_customer.php`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${selectedCustomerId}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-customer-id="${selectedCustomerId}"]`);
                    if (row) row.remove();
                    $('#deleteConfirmModal').modal('hide');
                    alert(data.message);
                } else {
                    alert(data.message || "Failed to restore customer.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("An error occurred while restoring the customer.");
            });
    });

    // Fetch customer records and display them in the modal
    function fetchCustomerRecords(customerId) {
        // Fetch customer actions from the backend using the customer ID
        fetch(`./fetch_potential_customer.php?archived=1`)
            .then(response => response.json())
            .then(data => {
                console.log("Fetched Data:", data); // Debugging

                const recordTableBody = document.getElementById('recordTableBody');
                recordTableBody.innerHTML = ''; // Clear previous records

                // Check if customer actions exist
                if (data.records && data.records.length > 0) {
                    let serialNumber = 1; // Initialize serial number

                    data.records.forEach(action => {
                        // Ensure customer_id is compared correctly
                        if (Number(action.customer_id) === Number(customerId)) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                        <td class="text-center align-middle">${serialNumber}</td>
                                        <td class="text-center align-middle">${action.timestamp}</td>
                                        <td class="text-center align-middle">${action.record}</td>
                                    `;
                            recordTableBody.appendChild(tr);
                            serialNumber++; // Increment serial number
                        }
                    });
                } else {
                    // Display message if no records exist
                    recordTableBody.innerHTML = "<tr><td colspan='3' class='text-center'>No actions found</td></tr>";
                }
            })
            .catch(error => console.error('Error fetching records:', error));
    }

    // Use event delegation for dynamic elements
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('quotation-btn')) {
            const row = event.target.closest('tr');
            const customerId = row.getAttribute('data-customer-id');
            console.log("Selected Customer ID:", customerId);

            // Fetch and display customer records in the modal
            fetchCustomerRecords(customerId);
        }
    });



    const currentUserId = <?php echo $_SESSION['admin_id']; ?>;

    // Add a new record for the selected customer
    document.getElementById('addRecordBtn').addEventListener('click', function () {
        const recordText = document.getElementById('recordTextArea').value.trim();

        if (!recordText) {
            alert('Please enter a record before adding.');
            return;
        }

        fetch('./add_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                customer_id: selectedCustomerId,
                record: recordText,
                user_id: currentUserId  // Add this line
            })

        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Record added successfully!');
                    document.getElementById('recordTextArea').value = ''; // Clear textarea
                    fetchCustomerRecords(selectedCustomerId); // Refresh the records in the modal
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>



</html>

<?php
include 'include/footer.php';
?>
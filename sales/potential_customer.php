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
    .delete-record-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 6px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    /* default state → primary blue icon */
    .delete-record-btn .trash-icon path {
        stroke: var(--bs-primary);
        /* Bootstrap primary color */
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* hover state → white icon + blue background */
    .delete-record-btn:hover {
        background-color: var(--bs-primary);
    }

    .delete-record-btn:hover .trash-icon path {
        stroke: #fff;
    }

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



<div class="mx-auto position-relative mb-5" style="width: 97.5%;">
    <div class="d-sm-flex align-items-center justify-content-between">
        <div>
            <h3>My Potential Clients</h3>
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
                <th width="" class="align-middle text-center">Office</th>
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
                            <th class="text-center align-middle">User</th>
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
                Are you sure you want to convert this potential client (ID:
                <strong id="convertCustomerId">N/A</strong>) into a
                <strong>CSA client</strong>?
            </div>



            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary confirm-convert-btn">Yes, Convert</button>
            </div>

        </div>
    </div>
</div>


<!-- Colab Modal -->
<div class="modal fade" id="colabModal" tabindex="-1" aria-labelledby="colabModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="colabModalLabel">Collaborate with a User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="colabForm">
                    <input type="hidden" name="customer_id" id="colabCustomerId">
                    <input type="hidden" name="account_manager_id" id="colabManagerId">

                    <div class="mb-3">
                        <label class="form-label">Select User</label>

                        <div class="dropdown w-100">
                            <input type="text" class="form-control" id="userSearchInput"
                                placeholder="Search or add user...">

                            <ul class="dropdown-menu w-100" id="userDropdownList"></ul>

                            <!-- Hidden input to store selected users (comma separated) -->
                            <input type="hidden" name="collab_user_id" id="selectedUsers">
                        </div>

                        <!-- Selected users will appear here -->
                        <div id="selectedUsersList" class="mt-2"></div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="colabForm" data-bs-dismiss="modal" class="btn btn-primary">Save
                    Collaboration</button>
            </div>

        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmColabModal" tabindex="-1" role="dialog" aria-labelledby="confirmColabModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="confirmColabModalLabel">Confirm Collaboration</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                Are you sure you want to save this collaboration?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSaveBtn" class="btn btn-primary">Yes, Save</button>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete this record (ID:
                <strong id="deleteCustomerId">N/A</strong>)?
                This action cannot be undone.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
            </div>

        </div>
    </div>
</div>

<script>
    const users = <?php
    $query = "SELECT `user_id`, `fullname` FROM `tbl_admin` WHERE `salesAccess`=1;";
    $result = mysqli_query($conn, $query);
    $userArray = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userArray[] = [
                "id" => $row['user_id'],
                "name" => $row['fullname']
            ];
        }
    }
    echo json_encode($userArray);
    ?>;

</script>
<script>
    const loggedInUserId = <?php echo json_encode($_SESSION['admin_id']); ?>;

    const input = document.getElementById("userSearchInput");
    const dropdown = document.getElementById("userDropdownList");
    const selectedUsersList = document.getElementById("selectedUsersList");
    const hiddenInput = document.getElementById("selectedUsers");

    let selected = [];

    // Show matching users as dropdown
    function showDropdown(searchText = "") {
        const search = searchText.toLowerCase();
        dropdown.innerHTML = "";

        const filtered = users.filter(u => u.name.toLowerCase().includes(search) && !selected.some(s => s.id === u.id));

        if (filtered.length === 0 && search !== "") {
            // Option to add new user
            const li = document.createElement("li");
            li.className = "dropdown-item text-primary";
            li.textContent = `Add "${searchText}"`;
            li.onclick = () => addUser({ id: "new-" + Date.now(), name: searchText });
            dropdown.appendChild(li);
        } else {
            filtered.forEach(user => {
                const li = document.createElement("li");
                li.className = "dropdown-item";
                li.textContent = user.name;
                li.onclick = () => addUser(user);
                dropdown.appendChild(li);
            });
        }


        dropdown.classList.add("show");
    }

    // Trigger on typing
    input.addEventListener("input", function () {
        showDropdown(this.value);
    });

    // Trigger on focus (show all users)
    input.addEventListener("focus", function () {
        showDropdown("");
    });


    // Add user to selected list
    function addUser(user) {
        if (selected.some(u => u.id === user.id)) return; // prevent duplicate

        selected.push(user);

        const badge = document.createElement("span");
        badge.className = "badge bg-primary me-1 mb-1";
        badge.id = "badge-" + user.id;

        // ✅ Check if logged in user is the account manager
        const accountManagerId = document.getElementById("colabManagerId").value;

        if (String(loggedInUserId) === String(accountManagerId)) {
            badge.innerHTML = `${user.name} 
            <button type="button" class="btn-close btn-close-white btn-sm ms-1" 
                onclick="removeUser('${user.id}')"></button>`;
        } else {
            badge.innerHTML = `${user.name}`;
        }

        selectedUsersList.appendChild(badge);
        hiddenInput.value = selected.map(u => u.id).join(",");

        input.value = "";
        dropdown.classList.remove("show");
    }


    // Remove user from selected list
    function removeUser(id) {
        selected = selected.filter(u => u.id !== id);
        document.getElementById("badge-" + id)?.remove();
        hiddenInput.value = selected.map(u => u.id).join(",");
    }

    // Hide dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (!dropdown.contains(e.target) && e.target !== input) {
            dropdown.classList.remove("show");
        }
    });
</script>
<!-- More Modal -->
<div class="modal fade" id="moreModal" tabindex="-1" aria-labelledby="moreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moreModalLabel">More Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <!-- Convert Button -->
                <!-- Convert Button -->
                <button type="button" class="btn btn-primary w-100 convert-btn" data-bs-toggle="modal"
                    data-bs-dismiss="modal" data-bs-target="#convertModal">
                    Convert to CSA Client
                </button>

                <!-- Collab Button -->
                <!-- In your More modal -->
                <button type="button" class="btn btn-secondary w-100" data-bs-toggle="modal"
                    data-bs-target="#colabModal" data-bs-dismiss="modal">
                    Collaborate
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
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
    let apiUrl = `./fetch_potential_customer.php?user_id=${userId}`; // ✅ default = ALL leads

    if (activeTab === "my-leads-tab") {
        apiUrl = `./fetch_potential_customer.php?account_manager_id=${userId}`;
    } else if (activeTab === "all-leads-tab") {
        apiUrl = `./fetch_potential_customer.php?user_id=${userId}`;
    }


    console.log("Active Tab:", activeTab);
    console.log("User ID:", userId);
    console.log("API URL:", apiUrl);





    // ⬇️ Add this block at the end ⬇️
    document.querySelectorAll('#leadsTab .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const activeTab = e.target.id;
            let apiUrl = "./fetch_potential_customer.php?";

            if (activeTab === "my-leads-tab") {
                apiUrl += `account_manager_id=${userId}`;
            } else {
                apiUrl += `user_id=${userId}`;
            }

            console.log("Switched Tab:", activeTab);
            console.log("API URL:", apiUrl);

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Debugging line to check the API response

                    // Extract potential_customers from the response
                    const potentialCustomers = data.potential_customers;

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
    ${row.last_update ? row.last_update.split(' ')[0] : (row.date || 'N/A')}
    <br>
    <span class="badge ${lastUpdateClass}">${badgeText}</span> <br/>
   <div class="collaborator-stack" id="collaborators-${row.id}" 
     data-bs-id="${row.id}" data-bs-account-manager="${row.account_manager_id || ''}"
     data-bs-toggle="modal" data-bs-target="#colabModal"
     style="cursor: pointer; display: inline-flex; position: relative; height: 24px; margin-top: 4px;">
    <!-- Collaborator icons will be dynamically inserted here -->
</div>
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
                <td class="text-center align-middle ">${row.pc_office_name || 'N/A'}</td>

              
          <td class="align-middle">
    <div class="d-flex justify-content-center align-items-center gap-3">
        <!-- View -->
        <span class="quotation-btn view-record-btn" role="button" 
              data-bs-toggle="modal" data-bs-target="#recordModal" data-bs-id="${row.id}" 
              data-tippy-content="View Record">
            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" 
                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" 
                 viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="2" 
                      d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/>
                <path stroke="currentColor" stroke-width="2" 
                      d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
            </svg>
        </span>

   <!-- trash -->
<!-- trash -->
<span class="quotation-btn delete-record-btn" role="button" 
      data-bs-id="${row.id}" 
      data-bs-toggle="modal" 
      data-bs-target="#deleteConfirmModal"
      data-tippy-content="Move To Bin">
    <svg class="trash-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" 
         width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/>
    </svg>
</span>







        <!-- Collab -->
        

       <!-- More Icon -->
<span class="quotation-btn more-btn" role="button" 
      data-bs-id="${row.id}" 
      data-bs-account-manager="${row.account_manager_id || ''}"
      data-bs-toggle="modal" 
      data-bs-target="#moreModal"
      data-tippy-content="More Options">
    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" 
         xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" 
         viewBox="0 0 24 24">
        <circle cx="12" cy="5" r="1.5" fill="currentColor"/>
        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
        <circle cx="12" cy="19" r="1.5" fill="currentColor"/>
    </svg>
</span>



    </div>
</td>









            `;

                        tableBody.appendChild(tr);
                        fetchAndDisplayCollaborators(row.id);

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

                    // Add this event listener to your script
                    document.addEventListener('click', function (e) {
                        if (e.target.closest('.convert-btn')) {
                            const btn = e.target.closest('.convert-btn');
                            btn.setAttribute('data-bs-id', selectedCustomerId);
                        }
                    });
                    // Add this event listener to set the selectedCustomerId when More modal is opened
                    document.getElementById('moreModal').addEventListener('show.bs.modal', function (e) {
                        const button = e.relatedTarget; // Button that triggered the modal
                        selectedCustomerId = button.getAttribute('data-bs-id'); // Get the customer ID
                        console.log("Selected Customer ID for More Modal:", selectedCustomerId);

                        // Now set the data-bs-id attribute on the Convert button
                        const convertBtn = this.querySelector('.convert-btn');
                        convertBtn.setAttribute('data-bs-id', selectedCustomerId);
                        convertBtn.setAttribute('data-bs-account-manager', button.getAttribute('data-bs-account-manager')); // ✅ added

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
        const myLeadsTab = document.querySelector("#my-leads-tab");

        if (myLeadsTab) {
            const event = new Event("shown.bs.tab", { bubbles: true });
            myLeadsTab.dispatchEvent(event);
            console.log("✅ Default My Leads loaded on page load");
        }
    });

    // Capture customer ID when opening Colab modal
    document.addEventListener("click", function (e) {
        if (e.target.closest(".teamwork-btn")) {
            const customerId = e.target.closest(".teamwork-btn").getAttribute("data-bs-id");
            document.getElementById("colabCustomerId").value = customerId;
        }
    });

    // When teamwork button is clicked, load collaborators
    // When teamwork modal is opened, load collaborators
    // When teamwork modal is opened, load collaborators
    const colabModal = document.getElementById("colabModal");

    colabModal.addEventListener("show.bs.modal", function (event) {
        // Get the customer ID from the button that triggered the collaboration modal
        const triggerButton = event.relatedTarget;

        // Check if triggered from collaborator stack (icon click)
        if (triggerButton && triggerButton.classList.contains('collaborator-stack')) {
            const customerId = triggerButton.getAttribute('data-bs-id');
            const accountmanagerid = triggerButton.getAttribute('data-bs-account-manager');
            selectedCustomerId = customerId;
            document.getElementById("colabCustomerId").value = customerId;
            document.getElementById("colabManagerId").value = accountmanagerid;
        }
        // Check if triggered from More modal's Collaborate button
        else {
            // Get data from the More modal's convert button which should have the data
            const moreModal = document.getElementById('moreModal');
            const convertBtn = moreModal.querySelector('.convert-btn');
            const customerId = convertBtn?.getAttribute("data-bs-id");
            const accountmanagerid = convertBtn?.getAttribute("data-bs-account-manager");

            if (customerId) {
                selectedCustomerId = customerId;
                document.getElementById("colabCustomerId").value = customerId;
                document.getElementById("colabManagerId").value = accountmanagerid;
            }
        }

        // Reset state before fetching
        selected = [];
        selectedUsersList.innerHTML = "";
        hiddenInput.value = "";

        // Pre-fill the collaborators
        fetch("./collab_update.php?customer_id=" + selectedCustomerId)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                console.log("Collaborators data:", data);
                if (data.success && Array.isArray(data.data)) {
                    // Add existing collaborators
                    data.data.forEach(id => {
                        const user = users.find(u => u.id == id);
                        if (user) addUser(user);
                    });
                } else if (!data.success) {
                    console.error("Server error:", data.message);
                }
            })
            .catch(err => console.error("Fetch error:", err));
    });


    // Save collaborators
    const colabForm = document.getElementById("colabForm");
    const confirmSaveBtn = document.getElementById("confirmSaveBtn");

    colabForm.addEventListener("submit", function (e) {
        e.preventDefault();

        // Show confirmation modal first
        $('#confirmColabModal').modal('show');

        // When user confirms, send request
        confirmSaveBtn.onclick = function () {
            $('#confirmColabModal').modal('hide');

            const formData = new FormData(colabForm);

            fetch("collab_update.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Collaboration updated successfully!");
                        $('#colabModal').modal('hide'); // Close main modal in Bootstrap 4
                        window.location.reload();
                    } else {
                        alert("Failed to update collaboration.");
                    }
                });
        };
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



    // Fetch customer records and display them in the modal
    function fetchCustomerRecords(customerId) {
        // Fetch customer actions from the backend using the customer ID
        fetch(`./fetch_potential_customer.php?customer_id=${customerId}`)
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
                                    <td class="text-center align-middle">${action.fullname}</td>
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
                user_id: loggedInUserId   // ✅ pass user_id
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



    // Update Convert Modal with selected customer ID
    document.getElementById('convertModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // the button that triggered the modal
        const customerId = button.getAttribute('data-bs-id'); // get ID from button
        selectedCustomerId = customerId; // store globally
        document.getElementById('convertCustomerId').textContent = customerId; // set modal text
    });

    // Add event listener for collaboration icon clicks
    document.addEventListener('click', function (e) {
        // Check if the clicked element is a collaborator icon or inside the collaborator stack
        const collaboratorElement = e.target.closest('.collaborator-stack');
        if (collaboratorElement) {
            const customerId = collaboratorElement.getAttribute('data-bs-id');
            const accountmanagerId = collaboratorElement.getAttribute("data-bs-account-manager");
            selectedCustomerId = customerId;
            console.log(accountmanagerId);
            // Set the customer ID in the colab modal
            document.getElementById('colabCustomerId').value = customerId;
            document.getElementById('colabManagerId').value = accountmanagerId;

            // Reset state before fetching
            selected = [];
            selectedUsersList.innerHTML = "";
            hiddenInput.value = "";

            // Pre-fill the collaborators
            fetch("./collab_update.php?customer_id=" + customerId)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    console.log("Collaborators data:", data);
                    if (data.success && Array.isArray(data.data)) {
                        // Add existing collaborators
                        data.data.forEach(id => {
                            const user = users.find(u => u.id == id);
                            if (user) addUser(user);
                        });
                    } else if (!data.success) {
                        console.error("Server error:", data.message);
                    }

                    // Show the colab modal after data is loaded
                    const colabModal = new bootstrap.Modal(document.getElementById('colabModal'));
                    colabModal.show();
                })
                .catch(err => console.error("Fetch error:", err));
        }
    });

    // Function to fetch and display collaborators
    // Function to fetch and display overlapping collaborator icons
    function fetchAndDisplayCollaborators(customerId) {
        fetch(`./collab_update.php?customer_id=${customerId}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById(`collaborators-${customerId}`);
                container.innerHTML = ''; // Clear existing icons

                if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                    const collaborators = data.data;

                    // Show up to 3 icons with a +X indicator if more
                    const maxVisible = 2;
                    const visibleCollaborators = collaborators.slice(0, maxVisible);

                    visibleCollaborators.forEach(userId => {
                        const user = users.find(u => u.id == userId);
                        if (user) {
                            const img = document.createElement('img');
                            img.src = './include/user.png';
                            img.alt = user.name;
                            img.setAttribute('data-tippy-content', user.name);
                            container.appendChild(img);
                        }
                    });

                    // Add +X indicator if there are more collaborators
                    if (collaborators.length > maxVisible) {
                        const moreBadge = document.createElement('span');
                        moreBadge.className = 'more-count';
                        moreBadge.textContent = `+${collaborators.length - maxVisible}`;
                        moreBadge.setAttribute('data-tippy-content', `${collaborators.length - maxVisible} more collaborators`);
                        container.appendChild(moreBadge);
                    }
                } else {
                    // Show single icon if no collaborators
                    const img = document.createElement('img');
                    img.src = './include/user.png';
                    img.alt = 'Add collaborator';
                    img.setAttribute('data-tippy-content', 'Add collaborator');
                    container.appendChild(img);
                }
            })
            .catch(err => console.error("Error fetching collaborators:", err));
    }
</script>



</html>

<?php
include 'include/footer.php';
?>
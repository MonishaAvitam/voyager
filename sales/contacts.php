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


<style>
    .fixedheader {
        position: sticky;
        z-index: 1;
        /* top: -0.3rem; */
        top: -0.2rem;
        /* background: linear-gradient(180deg, lightgray, rgb(193, 193, 193),rgb(193, 193, 193), rgb(193, 193, 193), lightgray) !important; */
        background: rgb(193, 193, 193);
        font-weight: bold;
        letter-spacing: 0.03rem;
        font-size: 1rem;
        color: black;


    }


    .table-container {

        max-height: 67vh;
        overflow-y: auto;


    }

    .table-container::-webkit-scrollbar {
        display: none;
        /* Hides the scrollbar */
    }

    .dataTables_wrapper .bottom {
        position: fixed;
        bottom: 0;
        width: 100%;
        z-index: 999;
        background: white;
        padding: 0.5rem 1rem;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #ddd;
    }

    .dataTables_paginate {
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }





    #customPageInput {
        height: 2rem;
        width: 4rem;

        padding: 0.5rem;
        outline: none;
        border: 1px solid lightgray;
        background: rgb(235, 235, 235);
        border-radius: 4px;

    }

    #customPageInput:hover {
        outline: none;
        border: 2px solid gray;
    }


    .blue-gradient-btn {
        /* background: linear-gradient(rgb(41, 109, 218), rgb(37, 97, 193), rgb(41, 109, 218)); */
        background: #0d6efd;
        color: white;
    }

    .edit-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: 0.3s ease-out;
        color: darkcyan;
        border: 1.5px solid darkcyan;
    }

    .edit-btn:hover {
        background: darkcyan;
        transform: scale(1.2);

        color: white;
    }

    .delete-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: crimson;
        border: 1.5px solid crimson;
        transition: 0.3s ease-out;
    }

    .delete-btn:hover {
        background: crimson;
        transform: scale(1.2);

        color: white;
    }
</style>



<div class="">
    <div class="mx-auto position-relative" style="width: 97.5%;">
        <div class="d-sm-flex align-items-center justify-content-between">
            <div>
                <h3>CSA Clients</h3>
                <h4 class="text-muted">All Office</h4>
            </div>
        </div>
    </div>


    <!-- Content Row -->
    <?php
    $limit = 50; // number of rows per page
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1; // current page
    $offset = ($page - 1) * $limit;

    // Get search keyword from query string
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $sql = "SELECT c.* FROM contacts c WHERE 1=1";

    if (!empty($search)) {
        // Escape % and _ in LIKE so they are treated as literals
        $searchEscaped = "%" . addslashes($search) . "%";
        $sql .= " AND (c.customer_name LIKE '$searchEscaped'
               OR c.contact_name LIKE '$searchEscaped'
               OR c.contact_email LIKE '$searchEscaped'
               OR c.contact_phone_number LIKE '$searchEscaped'
               OR c.comments LIKE '$searchEscaped')";
    }

    // Order & Limit
    $sql .= " ORDER BY c.contact_id DESC LIMIT $offset, $limit";

    // Get results
    $info = $obj_admin->manage_all_info($sql);

    // Count total rows for pagination
    $countSql = "SELECT COUNT(*) as total FROM contacts c WHERE 1=1";
    if (!empty($search)) {
        $countSql .= " AND (c.customer_name LIKE '$searchEscaped'
                     OR c.contact_name LIKE '$searchEscaped'
                     OR c.contact_email LIKE '$searchEscaped'
                     OR c.contact_phone_number LIKE '$searchEscaped'
                     OR c.comments LIKE '$searchEscaped')";
    }

    $totalQuery = $obj_admin->manage_all_info($countSql);
    $totalRows = $totalQuery->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRows / $limit);
    ?>




    <div class=" mx-auto" style="width: 99%">
        <div class="table-container  w-100" style="max-height: 100vh; overflow-y: auto; ">
            <div class="d-flex justify-content-end mb-3">
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" id="globalSearch" class="form-control" placeholder="Search clients..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                            class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 
                         3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 
                         1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z" />
                        </svg>
                    </button>
                </div>
            </div>



            <table class="table text-center table-sm w-100 " id="allprojects-table" style="">


                <thead class="fixedheader">
                    <tr class="text-dark align-middle font-weight-normal text-center " style="height: 3.5rem;">
                        <th width="2%" class="align-middle">Customer ID</th>
                        <th width="13%" class="align-middle">Company Name</th>
                        <th width="13   %" class="align-middle">State</th>
                        <th width="5%" class="align-middle">Code</th>
                        <th width="8%" class="align-middle">Contact Person</th>
                        <th width="8%" class="align-middle">Email</th>
                        <th width="8%" class="align-middle">Phone</th>
                        <th width="12%" class="align-middle">Address</th>
                        <th width="18%" class="align-middle">Comments</th>
                        <th width="8%" class="align-middle">Actions</th>
                    </tr>
                </thead>

                <tbody style="background: rgb(245, 245, 245) ;"> <!-- ... Your table rows ... -->

                    <?php
                    $serial = 1;
                    $num_row = $info->rowCount();

                    if ($num_row == 0) {
                        echo '<tr><td colspan="9">No projects were found</td></tr>';
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


                        <tr>
                            <td class="text-left align-middle px-4"><?php echo $row['contact_id'] ?></td>
                            <td class="text-left align-middle px-4"><?php echo $row['customer_name'] ?></td>
                            <td class="text-left align-middle px-4">
                                <?php
                                $office = mysqli_fetch_assoc(mysqli_query(
                                    $conn,
                                    "SELECT office_name FROM office WHERE id='{$row['office_id']}' LIMIT 1"
                                ));
                                if (!empty($office['office_name']) && preg_match('/\((.*?)\)/', $office['office_name'], $match)) {
                                    echo $match[1]; // text inside brackets
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>

                            <td class="text-left align-middle"><?php echo $row['customer_id'] ?></td>
                            <td class="text-left align-middle"><?php echo $row['contact_name'] ?></td>
                            <td class="text-left align-middle"><?php echo $row['contact_email'] ?></td>
                            <td class="text-left align-middle"><?php echo $row['contact_phone_number'] ?></td>
                            <td class="text-left align-middle"><?php echo $row['address'] ?></td>
                            <td class="text-left align-middle"><?php echo $row['comments'] ?></td>

                            <td class="text-center align-middle my-0 py-0">
                                <div class="d-flex justify-content-center align-items-center gap-2">

                                    <!-- Edit Contact -->
                                    <a href="edit_contacts.php?contact_id=<?php echo $row['contact_id']; ?>"
                                        title="Edit Contact" class="d-flex align-items-center justify-content-center"
                                        style="text-decoration: none; width: 32px; height: 32px;">
                                        <span class="edit-btn" style="cursor: pointer; transition: transform 0.2s;">
                                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                                viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                            </svg>
                                        </span>
                                    </a>

                                    <!-- Delete Contact -->
                                    <a href="?delete_contact_id=<?php echo $row['contact_id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this contact?');"
                                        title="Delete Contact" class="d-flex align-items-center justify-content-center"
                                        style="text-decoration: none; width: 32px; height: 32px;">
                                        <span class="delete-btn" style="cursor: pointer; transition: transform 0.2s;">
                                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                                viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                            </svg>
                                        </span>
                                    </a>

                                    <!-- New Info Icon -->
                                    <a href="javascript:void(0);" title="Potential Records"
                                        class="d-flex align-items-center justify-content-center" data-bs-toggle="modal"
                                        data-bs-target="#recordModal" data-customer-id="<?php echo $row['contact_id']; ?>"
                                        style="text-decoration: none; width: 32px; height: 32px; border: 1px solid currentColor; border-radius: 4px; transition: transform 0.2s;">

                                        <span class="info-btn" style="cursor: pointer; transition: transform 0.2s;">
                                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                                viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 9h6m-6 3h6m-6 3h6M6.996 9h.01m-.01 3h.01m-.01 3h.01M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                                            </svg>
                                        </span>
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
<?php
$startEntry = ($page - 1) * $limit + 1;
$endEntry = min($page * $limit, $totalRows);
?>

<div class="d-flex justify-content-between align-items-center my-3">

    <!-- Custom Info -->
    <div class="text-start mb-3">
        Showing <?php echo $startEntry; ?> to <?php echo $endEntry; ?> of <?php echo $totalRows; ?> entries
    </div>

    <!-- Pagination Buttons -->
    <div class="text-end">
        <!-- Previous Button -->
        <a href="?page=<?php echo max(1, $page - 1); ?>"
            class="btn btn-md mx-2 py-2 px-3 <?php echo ($page == 1) ? 'btn-secondary disabled' : 'btn-primary'; ?>">
            Previous
        </a>

        <?php
        $visiblePages = 3; // number of pages to show around current page
        
        if ($totalPages <= 5) {
            $start = 1;
            $end = $totalPages;
        } else {
            $start = max(1, $page - 1);
            $end = min($totalPages, $page + 1);

            if ($start > 1)
                echo '<a href="?page=1" class="btn btn-md btn-secondary mx-2 py-2 px-3">1</a>';
            if ($start > 2)
                echo '<span class="mx-2 py-2 px-2">...</span>';
        }

        for ($p = $start; $p <= $end; $p++): ?>
            <a href="?page=<?php echo $p; ?>"
                class="btn btn-md mx-2 py-2 px-3 <?php echo $p == $page ? 'btn-dark' : 'btn-secondary'; ?>">
                <?php echo $p; ?>
            </a>
        <?php endfor; ?>

        <?php
        if ($end < $totalPages - 1)
            echo '<span class="mx-2 py-2 px-2">...</span>';
        if ($end < $totalPages)
            echo '<a href="?page=' . $totalPages . '" class="btn btn-md btn-secondary mx-2 py-2 px-3">' . $totalPages . '</a>';
        ?>

        <!-- Next Button -->
        <a href="?page=<?php echo min($totalPages, $page + 1); ?>"
            class="btn btn-md mx-2 py-2 px-3 <?php echo ($page == $totalPages) ? 'btn-secondary disabled' : 'btn-primary'; ?>">
            Next
        </a>
    </div>

</div>





<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Potential Records </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr style="background: #133e87; color: white;">
                            <th class="text-center align-middle">Id</th>
                            <th class="text-center align-middle">Last Updated</th>
                            <th class="text-center align-middle">Users</th>
                            <th class="text-center align-middle">Records</th>
                        </tr>
                    </thead>
                    <tbody id="recordTableBody">


                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary bg-gradient btn-sm"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTable without search
        var table = $('#allprojects-table').DataTable({
            paging: false,
            info: false,
            searching: false, // disable default search
            ordering: false,
            fixedHeader: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: 8 }
            ]
        });

        // Listen for typing in global search
        function runSearch() {
            let query = $('#globalSearch').val();
            window.location.href = "contacts.php?search=" + encodeURIComponent(query);
        }

        // Submit on button click
        $('#searchBtn').on('click', runSearch);

        // Submit on Enter key
        $('#globalSearch').on('keypress', function (e) {
            if (e.which === 13) { // Enter key
                runSearch();
            }
        });


    });
</script>



<!-- ADD CONTACT FORM -->

<div class="modal fade" id="add_contact" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Customer Information </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="customer_id">Company Code</label>
                        <input type="text" class="form-control" id="customer_id" name="customer_id">
                    </div>
                    <div class="form-group">
                        <label for="customer_id">Company Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name">
                    </div>
                    <div class="form-group">
                        <label for="contactName">Contact Person</label>
                        <input type="text" class="form-control" id="contactName" name="contactName">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber">
                    </div>
                    <div class="form-group">
                        <label for="email">Email ID</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea type="text" class="form-control" id="address" name="address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="address">Comments</label>
                        <textarea type="text" class="form-control" id="comments" name="comments"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_contact">Add Contact</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Listen for modal show event
    var recordModal = document.getElementById('recordModal');

    recordModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // clicked button
        var customerId = button.getAttribute('data-customer-id');

        var tbody = document.getElementById('recordTableBody');
        tbody.innerHTML = '<tr><td colspan="3">Loading...</td></tr>';

        // Fetch records via AJAX
        fetch(`fetch_action_records.php?contact_customer_id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = ''; // clear table
                if (data.records.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3">No records found</td></tr>';
                    return;
                }

                data.records.forEach(record => {
                    var tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td class="text-center align-middle">${record.record_number}</td>
                    <td class="text-center align-middle">${record.timestamp}</td>
                    <td class="text-center align-middle">${record.fullname}</td>
                    <td class="text-center align-middle">${record.record}</td>
                `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="3">Error loading records</td></tr>';
                console.error(err);
            });
    });

</script>


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


// ADD NEW CONTACTS
if (isset($_POST['add_contact'])) {
    $customer_id = $_POST['customer_id'];
    $customer_name = $_POST['customer_name'];
    $contactName = $_POST['contactName'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $comments = $_POST['comments'];
    $registration_date = date('y-m-d');

    // Prepare and execute the SQL INSERT statement
    $sql = "INSERT INTO contacts (customer_id,customer_name, contact_name, contact_phone_number, contact_email,address,comments, registration_date) VALUES (?, ?, ?, ?, ?,?,?,?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssssss", $customer_id, $customer_name, $contactName, $phoneNumber, $email, $address, $comments, $registration_date);
        $stmt->execute(); // Execute the prepared statement

        $msg_success = "Contact Added Successfully";
        header('Location: contacts.php');
    } else {
        echo "Error: " . $conn->error;
        $msg_error = "Error in adding the contact: ' . $conn->error . '";
    }
}



?>










<?php

include './include/footer.php';

?>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // Admin authentication check 
require '../conn.php';
include './include/login_header.php';

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
    exit; // Ensure no further code is executed after redirect
}

// Check admin
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';

$connectedToQB = false;

$buttonClass = "";
$buttonText = "";
$authLink = "";


// if (!$connectedToQB) {
//     $buttonText = "Connected to QuickBooks";
//     $buttonClass = "btn btn-success";
//     $authLink = "https://quickbooks.csaappstore.com/auth/";
// }

if (isset($_GET['finance_id'])) {
    $finance_id = $_GET['finance_id'];

    // Step 1: Move the project to csa_finance_readytobeinvoiced
    $sql_move = "SELECT inv.*, 
        (SELECT MAX(rownumber)
        FROM csa_finance_invoiced AS inner_table
        WHERE inner_table.project_id = inv.project_id) AS last_rownumber
        FROM invoices AS inv
        WHERE inv.project_id IN (SELECT DISTINCT project_id FROM invoices)";

    $stmt_move = $conn->prepare($sql_move);
    $stmt_move->bind_param("i", $finance_id);

    if ($stmt_move->execute() === TRUE) {
        // Step 2: Delete the project from csa_finance_invoiced
        $sql_delete = "DELETE FROM csa_finance_invoiced WHERE finance_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $finance_id);

        if ($stmt_delete->execute() === TRUE) {
            // Display a success Toastr notification
            $msg_error = "Project sent back successfully";
            header('Location:' . $_SERVER['HTTP_REFERER']);
            exit; // Ensure no further code is executed after redirect
        } else {
            // Error deleting from csa_finance_invoiced
            $msg_error = "Error deleting the project: " . $conn->error;
        }
    } else {
        // Error moving to csa_finance_readytobeinvoiced
        $msg_error = "Error moving the project: " . $conn->error;
    }
}


// Add pagination parameters
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
$offset = ($page - 1) * $perPage;
$orderColumn = isset($_GET['sort']) ? $_GET['sort'] : 'i.project_id';
$orderDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

// Whitelist allowed columns to prevent SQL injection
$allowedColumns = ['i.project_id', 'i.project_title', 'i.p_team', 'i.invoice_number', 'i.customer_name', 'i.amount', 'i.comments', 'i.service_date', 'i.due_date', 'i.payment_status'];
if (!in_array($orderColumn, $allowedColumns)) {
    $orderColumn = 'i.project_id';
}

// Modify your SQL query to include LIMIT
// Add this near the top of your PHP code (after $perPage is set)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Modify your SQL query to include search conditions
$sql = "SELECT i.*,p.reopen_status,p.revision_project_id,p.urgency,dd.reopen_status,dd.revision_project_id,COALESCE(dd.urgency, p.urgency) AS urgency 
        FROM csa_finance_invoiced i
        LEFT JOIN projects p ON i.project_id = p.project_id
        LEFT JOIN deliverable_data dd ON i.project_id = dd.project_id
        WHERE i.payment_status IS NOT NULL AND i.payment_status != '' AND i.payment_status != 'Void'";

// Add search conditions if search term exists
if (!empty($searchTerm)) {
    // Changed from invoice_id to invoice_number
    $sql .= " AND (i.project_id LIKE '%$searchTerm%' OR 
             i.project_title LIKE '%$searchTerm%' OR 
             i.p_team LIKE '%$searchTerm%' OR 
             i.invoice_number LIKE '%$searchTerm%' OR 
             i.customer_name LIKE '%$searchTerm%' OR 
             i.comments LIKE '%$searchTerm%')";

}
// Add team filter if selected
if (isset($_GET['team']) && !empty($_GET['team'])) {
    $team = $_GET['team'];
    $sql .= " AND i.p_team = '$team'";
}

// Add customer filter if selected
if (isset($_GET['customer']) && !empty($_GET['customer'])) {
    $customer = $_GET['customer'];
    $sql .= " AND i.customer_name = '$customer'";
}

// Add pagination
$sql .= " ORDER BY " . $orderColumn . " $orderDir LIMIT $offset, $perPage";


// Get total count for pagination
// Modify your count query
$countSql = "SELECT COUNT(*) as total FROM csa_finance_invoiced i
             LEFT JOIN projects p ON i.project_id = p.project_id
             LEFT JOIN deliverable_data dd ON i.project_id = dd.project_id
             WHERE i.payment_status IS NOT NULL AND i.payment_status != '' AND i.payment_status != 'Void'";

// Changed from invoice_id to invoice_number below
if (!empty($searchTerm)) {
    // Changed from invoice_id to invoice_number
    $countSql .= " AND (i.project_id LIKE '%$searchTerm%' OR 
                   i.project_title LIKE '%$searchTerm%' OR 
                   i.p_team LIKE '%$searchTerm%' OR 
                   i.invoice_number LIKE '%$searchTerm%' OR 
                   i.customer_name LIKE '%$searchTerm%' OR 
                   i.comments LIKE '%$searchTerm%')";

}
// Add team filter if selected
if (isset($_GET['team']) && !empty($_GET['team'])) {
    $team = $_GET['team'];
    $countSql .= " AND i.p_team = '$team'";
}

// Add customer filter if selected
if (isset($_GET['customer']) && !empty($_GET['customer'])) {
    $customer = $_GET['customer'];
    $countSql .= " AND i.customer_name = '$customer'";
}

$totalResult = $conn->query($countSql);
$totalRow = $totalResult->fetch_assoc();
$totalProjects = $totalRow['total'];
$totalPages = ceil($totalProjects / $perPage);

$startEntry = $offset + 1;
$endEntry = min($offset + $perPage, $totalProjects);


// Fetch invoice data
$info = $obj_admin->manage_all_info($sql);
$num_row = $info->rowCount();

?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get the invoice_no from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const projectId = urlParams.get('project_id');

        if (projectId) {
            // If invoice_no is found in the URL, set it in the DataTable search input
            const dataTableSearchInput = $('#dataTable_filter input'); // This targets the DataTable's search input
            dataTableSearchInput.val(projectId); // Set the value of the DataTable search input

            // Trigger the DataTable search
            const dataTable = $('#dataTable').DataTable(); // Initialize DataTable if not done already
            dataTable.search(projectId).draw(); // Perform search in DataTable with the invoice number
        }
    });

    // Initialize DataTable if not initialized earlier
    // Replace your current DataTable initialization with this:
    $(document).ready(function () {
        $('#dataTable').DataTable({
            "paging": false,
            "searching": false, // Disable DataTable's built-in search
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "sorting": false,
            "dom": 'lrtip' // This removes the DataTable controls

        });

        // Handle form submission
        $('#searchForm').on('submit', function (e) {
            e.preventDefault();
            var searchTerm = $('#searchInput').val();
            window.location.href = '?page=1&perPage=<?php echo $perPage; ?>&search=' + encodeURIComponent(searchTerm);
        });
    });
</script>

<?php
// Function to generate query string with current filters
function getFilterQueryString()
{
    $params = [];
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $params['search'] = $_GET['search'];
    }
    if (isset($_GET['team']) && !empty($_GET['team'])) {
        $params['team'] = $_GET['team'];
    }
    if (isset($_GET['customer']) && !empty($_GET['customer'])) {
        $params['customer'] = $_GET['customer'];
    }
    if (isset($_GET['perPage']) && !empty($_GET['perPage'])) {
        $params['perPage'] = $_GET['perPage'];
    }
    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        $params['sort'] = $_GET['sort'];
    }
    if (isset($_GET['dir']) && !empty($_GET['dir'])) {
        $params['dir'] = $_GET['dir'];
    }
    return http_build_query($params);
}

?>

<!-- dashboard content -->

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Paid Projects</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Paid Projects Tab</h6>
            <div class="d-flex">
                <?php if (!empty($_GET['search']) || !empty($_GET['team']) || !empty($_GET['customer']) || !empty($_GET['perPage']) || !empty($_GET['sort']) || !empty($_GET['dir'])): ?>
                    <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn btn-sm btn-danger ml-2">
                        Clear All Filters
                    </a>
                <?php endif; ?>


                <form id="searchForm" class="form-inline mr-2">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search..."
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <?php if (isset($_GET['team'])): ?>
                        <input type="hidden" name="team" value="<?php echo htmlspecialchars($_GET['team']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['customer'])): ?>
                        <input type="hidden" name="customer" value="<?php echo htmlspecialchars($_GET['customer']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['perPage'])): ?>
                        <input type="hidden" name="perPage" value="<?php echo htmlspecialchars($_GET['perPage']); ?>">
                    <?php endif; ?>
                </form>
                <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#filterModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-funnel" viewBox="0 0 16 16">
                        <path
                            d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z" />
                    </svg>
                </button>
                <a href="#" class="float-right" id="filter-icon" data-toggle="modal" data-target="#filter-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                        class="bi bi-people-fill" viewBox="0 0 16 16">
                        <path
                            d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                    </svg>
                </a>
            </div>
        </div>
        <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">Filter by Customer Name</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" id="customerFilterForm">
                            <input type="hidden" name="page" value="1">
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['team'])): ?>
                                <input type="hidden" name="team" value="<?php echo htmlspecialchars($_GET['team']); ?>">
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="customer_name">Customer Name:</label>
                                <select class="form-control mt-2" id="customer_name" name="customer"
                                    style="width: 100%;" onchange="this.form.submit()">
                                    <option value="">Select customer</option>
                                    <?php
                                    $sql = "SELECT DISTINCT customer_name FROM contacts";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $selected = (isset($_GET['customer']) && $_GET['customer'] == $row['customer_name']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($row['customer_name']) . "' $selected>" . htmlspecialchars($row['customer_name']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="filterTableByCustomer()">Apply
                            Filter</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="filter-modal" tabindex="-1" aria-labelledby="filter-modal-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary" id="filter-modal-label">Filter Options</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" id="teamFilterForm">
                            <input type="hidden" name="page" value="1">
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['customer'])): ?>
                                <input type="hidden" name="customer"
                                    value="<?php echo htmlspecialchars($_GET['customer']); ?>">
                            <?php endif; ?>
                            <label for="team-filter" class="text-primary">Filter by Team:</label>
                            <select id="team-filter" name="team" class="form-control" onchange="this.form.submit()">
                                <option value="">All</option>
                                <option value="Building" <?php if (isset($_GET['team']) && $_GET['team'] == 'Building Team')
                                    echo 'selected'; ?>>Building Team</option>
                                <option value="Industrial" <?php if (isset($_GET['team']) && $_GET['team'] == 'Industrial Team')
                                    echo 'selected'; ?>>Industrial Team</option>
                            </select>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="filterTableByTeam()"
                            id="apply-filter">Apply
                            Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <div class="dataTables_length d-flex align-items-center" id="dataTable_length">
                    <label class="d-flex align-items-center m-0">
                        <span class="mr-2">Show</span>
                        <form method="GET" class="m-0">
                            <select name="perPage" class="custom-select custom-select-sm form-control form-control-sm"
                                onchange="this.form.submit()">
                                <option value="10" <?php if ($perPage == 10)
                                    echo 'selected'; ?>>10</option>
                                <option value="25" <?php if ($perPage == 25)
                                    echo 'selected'; ?>>25</option>
                                <option value="50" <?php if ($perPage == 50)
                                    echo 'selected'; ?>>50</option>
                                <option value="100" <?php if ($perPage == 100)
                                    echo 'selected'; ?>>100</option>
                            </select>
                            <input type="hidden" name="page" value="1">
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['team'])): ?>
                                <input type="hidden" name="team" value="<?php echo htmlspecialchars($_GET['team']); ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['customer'])): ?>
                                <input type="hidden" name="customer"
                                    value="<?php echo htmlspecialchars($_GET['customer']); ?>">
                            <?php endif; ?>
                        </form>
                        <span class="ml-2">entries</span>
                    </label>
                </div>


                <table class="table table-bordered table-sm" id="dataTable">
                    <thead style="height:4.5rem;">
                        <tr style="background-color: #dfa934;" class="text-light my-2 text-center">
                            <th scope="col" class="align-middle" width="8%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=project_id&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'project_id' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Project No.</a>
                            </th>
                            <th scope="col" class="align-middle" width="7%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=project_title&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'project_title' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Project Title</a>
                            </th>
                            <th scope="col" class="align-middle" width="6%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=p_team&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'p_team' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Team</a>
                            </th>
                            <th scope="col" class="align-middle" width="8%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=invoice_number&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'invoice_number' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Invoice No.</a>
                            </th>
                            <th scope="col" class="align-middle" width="8%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=customer_name&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'customer_name' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Customer Name</a>
                            </th>
                            <th scope="col" class="align-middle" width="5%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=amount&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'amount' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Amount</a>
                            </th>
                            <th scope="col" class="align-middle" width="13%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=comments&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'comments' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Comments</a>
                            </th>
                            <th scope="col" class="align-middle" width="7%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=service_date&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'service_date' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Service Date</a>
                            </th>
                            <th scope="col" class="align-middle" width="7%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=due_date&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'due_date' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Due Date</a>
                            </th>
                            <th scope="col" class="align-middle" width="7%" style="font-weight:500;">
                                <a href="?<?php echo getFilterQueryString(); ?>&sort=payment_status&dir=<?php echo (isset($_GET['sort']) && $_GET['sort'] == 'payment_status' && (!isset($_GET['dir']) || $_GET['dir'] == 'asc')) ? 'desc' : 'asc'; ?>"
                                    class="text-light">Payment Status</a>
                            </th>
                            <th scope="col" class="align-middle" width="6%" style="font-weight:500;">Action</th>
                        </tr>

                    </thead>
                    <tbody style="background: #dddddd70;">
                        <?php
                        if ($num_row == 0) {
                            echo '<tr><td colspan="10">No projects were found</td></tr>';
                        }

                        while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td class="pl-4 align-middle">
                                    <div style="display:flex; align-items: center; justify-content: center; height:1.7rem; width:3.8rem; 
                background-color: <?= ($row['urgency'] === 'cancelled') ? '#d3d3c0' : $row['urgency']; ?>; 
                text-align: center; border-radius: 5px; 
                color: <?= ($row['urgency'] === 'cancelled' || $row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff'; ?>;
                text-decoration: <?= ($row['urgency'] === 'cancelled') ? 'line-through' : 'none'; ?>;">
                                        <?= !empty($row['revision_project_id']) ? $row['revision_project_id'] : $row['project_id']; ?>
                                    </div>

                                    <?php if ($row['rownumber'] > 0): ?>
                                        <span class="badge badge-pill badge-danger">V<?= $row['rownumber']; ?></span>
                                    <?php endif; ?>

                                    <span class="badge badge-pill badge-danger"><?= $row['reopen_status']; ?></span>
                                    <span
                                        class="badge badge-pill badge-danger"><?= $row['quick_project'] ? 'Quick Project' : ''; ?></span>
                                </td>



                                <td class="align-middle"><?php echo $row['project_title']; ?></td>
                                <td class="align-middle"><?php echo $row['p_team']; ?></td>
                                <td class="align-middle"><?php echo $row['invoice_id'] ?? $row['invoice_number']; ?></td>
                                <td class="align-middle"><?php echo $row['customer_name'] ?? 'N/A'; ?></td>
                                <td class="align-middle"><?php echo $row['amount']; ?></td>
                                <td class="align-middle"><?php echo $row['comments']; ?></td>
                                <td class="align-middle"><?php echo $row['service_date']; ?></td>
                                <td class="align-middle"><?php echo $row['due_date']; ?></td>


                                <!-- New Payment Status column -->
                                <td class="align-middle">
                                    <a type="button" class="text-decoration-none" data-bs-toggle="modal"
                                        data-bs-target="#changePaymentStatus<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                        data-project-id="<?php echo $row['project_id']; ?>"
                                        data-row-number="<?php echo $row['rownumber']; ?>">
                                        <?php if ($row['payment_status'] == 'paid'): ?>
                                            <span class="badge badge-success"><?php echo $row['payment_status']; ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo $row['payment_status']; ?></span>
                                        <?php endif; ?>

                                        </button>
                                </td>

                                <td class="d-flex justify-content-around align-items-center">
                                    <?php if (empty($row['rownumber'])): ?>


                                        <button type="button" id="more-btn" class="ml-1 btn text-info btn-sm btn-light "
                                            data-bs-toggle="modal"
                                            data-bs-target="#moreModalMain<?php echo $row['project_id']; ?>"
                                            data-project-id="<?php echo $row['project_id']; ?>"
                                            data-row-number="<?php echo $row['rownumber']; ?>">
                                            More
                                        </button>

                                        <!-- Modal content here... -->
                                        <!-- Modal content here... -->
                                        <div class="modal fade" id="moreModalMain<?php echo $row['project_id']; ?>"
                                            tabindex="-1" aria-labelledby="moreModalMain<?php echo $row['project_id']; ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Action Center</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex justify-content-start">
                                                            <!-- View Project Button -->
                                                            <a title="View" class="btn btn-primary mx-1"
                                                                href="./task-details.php?project_id=<?php echo $row['project_id']; ?>">
                                                                View Project
                                                            </a>
                                                            <a class="btn btn-warning mx-1" data-bs-toggle="modal"
                                                                data-bs-target="#updateProject<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                                                data-project-id="<?php echo $row['project_id']; ?>"
                                                                data-row-number="<?php echo $row['rownumber']; ?>">Update
                                                                Project
                                                            </a>

                                                            <!-- Create Variation Button -->
                                                            <form action="add_invoice.php" method="post" class="d-inline-block">
                                                                <input type="hidden" name="project_id"
                                                                    value="<?php echo $row['project_id']; ?>" />
                                                                <input type="hidden" name="rownumber"
                                                                    value="<?php echo $row['rownumber']; ?>" />
                                                                <button type="submit" name="add_row"
                                                                    class="btn btn-success mx-1">
                                                                    Create Variation
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- Example of a dynamically generated modal -->
                                        <div class="modal fade"
                                            id="updateProject<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            tabindex="-1"
                                            aria-labelledby="updateProjectLabel<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="moreModalLabel<?php echo $row['project_id']; ?>">Update
                                                                Project</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <label>Project Id</label>
                                                            <input type="text" name="project_id" class="form-control"
                                                                value="<?php echo $row['project_id']; ?>" readonly />
                                                            <input type="hidden" name="rownumber"
                                                                value="<?php echo $row['rownumber']; ?>" />
                                                            <input type="hidden" name="finance_id"
                                                                value="<?php echo $row['finance_id']; ?>" />
                                                            <label class="mt-3">Amount</label>
                                                            <input type="number" name="amount"
                                                                value="<?php echo $row['amount'] ?>" class="form-control">
                                                            <label class="mt-3">Service Date</label>
                                                            <input type="date" name="service_date"
                                                                value="<?php echo $row['service_date'] ?>"
                                                                class="form-control"><label class="mt-3">Due Date</label>
                                                            <input type="date" name="due_date"
                                                                value="<?php echo $row['due_date'] ?>" class="form-control">
                                                            <label class="mt-3">Comments</label>
                                                            <textarea name="comments"
                                                                class="form-control"><?php echo trim($row['comments']); ?></textarea>


                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="updateProject" class="btn btn-success"
                                                                data-bs-dismiss="modal">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
                                            // Wait for the modal to show
                                            $('#moreModalMain<?php echo $row['project_id']; ?>').on('show.bs.modal', function (event) {
                                                // Get the button that triggered the modal
                                                var button = $(event.relatedTarget);
                                                // Extract project_id and rownumber from data-* attributes
                                                var projectId = button.data('project-id');
                                                var rowNumber = button.data('row-number');

                                                // Find the hidden input inside the modal and set its value to project_id
                                                var modal = $(this);
                                                modal.find('input[name="project_id"]').val(projectId);
                                                modal.find('input[name="rownumber"]').val(rowNumber);

                                                // Optionally, you can update other elements in the modal
                                                modal.find('.modal-title').text('Action Center for Project ID ' + projectId);
                                            });
                                            $('#changePaymentStatus<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>').on('show.bs.modal', function (event) {
                                                // Get the button that triggered the modal
                                                var button = $(event.relatedTarget);

                                                // Extract project_id and rownumber from data-* attributes
                                                var projectId = button.data('project-id');
                                                var rowNumber = button.data('row-number');

                                                // Find the hidden input fields inside the modal and set their values
                                                var modal = $(this);
                                                modal.find('input[name="project_id"]').val(projectId);
                                                modal.find('input[name="rownumber"]').val(rowNumber);
                                            });
                                        </script>

                                    <?php else: ?>
                                        <!-- More and Save Buttons -->

                                        <button type="button" class="ml-2 btn text-info btn-sm btn-light align-middle"
                                            data-bs-toggle="modal"
                                            data-bs-target="#moreModal<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            data-project-id="<?php echo $row['project_id']; ?>"
                                            data-row-number="<?php echo $row['rownumber']; ?>">
                                            More
                                        </button>
                                        <script>
                                            // Wait for the modal to show
                                            $('#moreModal<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>').on('show.bs.modal', function (event) {
                                                // Get the button that triggered the modal
                                                var button = $(event.relatedTarget);

                                                // Extract project_id and rownumber from data-* attributes
                                                var projectId = button.data('project-id');
                                                var rowNumber = button.data('row-number');

                                                // Find the hidden input fields inside the modal and set their values
                                                var modal = $(this);
                                                modal.find('input[name="project_id"]').val(projectId);
                                                modal.find('input[name="rownumber"]').val(rowNumber);
                                            });
                                            $('#changePaymentStatus<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>').on('show.bs.modal', function (event) {
                                                // Get the button that triggered the modal
                                                var button = $(event.relatedTarget);

                                                // Extract project_id and rownumber from data-* attributes
                                                var projectId = button.data('project-id');
                                                var rowNumber = button.data('row-number');

                                                // Find the hidden input fields inside the modal and set their values
                                                var modal = $(this);
                                                modal.find('input[name="project_id"]').val(projectId);
                                                modal.find('input[name="rownumber"]').val(rowNumber);
                                            });
                                        </script>




                                        <!-- Modal for More Options (Delete and View) -->
                                        <div class="modal fade"
                                            id="moreModal<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            tabindex="-1" role="dialog"
                                            aria-labelledby="moreModal<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="moreModalLabel<?php echo $row['project_id']; ?>">Action Center
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- View Project Button -->
                                                        <a title="View" class="btn btn-primary mx-1"
                                                            href="./task-details.php?project_id=<?php echo $row['project_id']; ?>">
                                                            View Project
                                                        </a>

                                                        <a class="btn btn-warning mx-1" data-bs-toggle="modal"
                                                            data-bs-target="#updateProject<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                                            data-project-id="<?php echo $row['project_id']; ?>"
                                                            data-row-number="<?php echo $row['rownumber']; ?>">Update
                                                            Project</a>



                                                        <!-- Hidden Input Fields for project_id and rownumber -->
                                                        <input type="hidden" name="project_id" value="" />
                                                        <input type="hidden" name="rownumber" value="" />

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- Example of a dynamically generated modal -->
                                        <div class="modal fade"
                                            id="updateProject<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            tabindex="-1"
                                            aria-labelledby="updateProjectLabel<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="moreModalLabel<?php echo $row['project_id']; ?>">Update
                                                                Project</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <label>Project Id</label>
                                                            <input type="text" name="project_id" class="form-control"
                                                                value="<?php echo $row['project_id']; ?>" readonly />
                                                            <input type="hidden" name="rownumber"
                                                                value="<?php echo $row['rownumber']; ?>" />
                                                            <input type="hidden" name="finance_id"
                                                                value="<?php echo $row['finance_id']; ?>" />
                                                            <label class="mt-3">Amount</label>
                                                            <input type="number" name="amount"
                                                                value="<?php echo $row['amount'] ?>" class="form-control">
                                                            <label class="mt-3">Service Date</label>
                                                            <input type="date" name="service_date"
                                                                value="<?php echo $row['service_date'] ?>"
                                                                class="form-control"><label class="mt-3">Due Date</label>
                                                            <input type="date" name="due_date"
                                                                value="<?php echo $row['due_date'] ?>" class="form-control">
                                                            <label class="mt-3">Comments</label>
                                                            <textarea name="comments"
                                                                class="form-control"><?php echo trim($row['comments']); ?></textarea>


                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="updateProject" class="btn btn-success"
                                                                data-bs-dismiss="modal">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                    <?php endif; ?>
                                    <div class="modal fade"
                                        id="changePaymentStatus<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                        tabindex="-1" role="dialog"
                                        aria-labelledby="moreModal<?php echo $row['project_id']; ?><?php echo $row['rownumber']; ?>"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="add_invoice.php" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="moreModalLabel<?php echo $row['project_id']; ?>">Payment
                                                            Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">

                                                        <!-- Hidden Input Fields for project_id and rownumber -->
                                                        <input type="hidden" name="project_id"
                                                            value="<?php echo $row['project_id']; ?>" />
                                                        <input type="hidden" name="rownumber"
                                                            value="<?php echo $row['rownumber']; ?>" />

                                                        <select class="form-control" name="Status" id="">
                                                            <option value="paid">Paid</option>
                                                            <option value="partiallyPaid">Partially Paid</option>
                                                            <option value="">Not Paid</option>
                                                            <option value="Void">Void</option>
                                                        </select>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="paymentStatus" class="btn btn-success"
                                                            data-bs-dismiss="modal">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>


                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>

                </table>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="mb-0  text-dark">
                        Showing <?php echo $startEntry; ?> to <?php echo $endEntry; ?> of <?php echo $totalProjects; ?>
                        entries
                        <?php if (!empty($searchTerm)): ?>
                            (filtered from total)
                        <?php endif; ?>
                    </p>

                    <ul class="pagination mb-0">
                        <?php
                        $filterQuery = getFilterQueryString();
                        $querySeparator = empty($filterQuery) ? '' : '&';

                        // Previous
                        if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?page=<?= $page - 1 ?><?= $querySeparator . $filterQuery ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <!-- First Page -->
                        <li class="page-item <?= $page == 1 ? 'active' : '' ?>">
                            <a class="page-link" href="?page=1<?= $querySeparator . $filterQuery ?>">1</a>
                        </li>

                        <!-- Left Dots -->
                        <?php if ($page > 4): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>

                        <!-- Pages Around Current -->
                        <?php for ($i = max(2, $page - 2); $i <= min($totalPages - 1, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $i ?><?= $querySeparator . $filterQuery ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Right Dots -->
                        <?php if ($page < $totalPages - 3): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>

                        <!-- Last Page -->
                        <?php if ($totalPages > 1): ?>
                            <li class="page-item <?= $page == $totalPages ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $totalPages ?><?= $querySeparator . $filterQuery ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Next -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?page=<?= $page + 1 ?><?= $querySeparator . $filterQuery ?>">Next</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?page=<?= $totalPages ?><?= $querySeparator . $filterQuery ?>">Last</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_POST['updateProject'])) {
    include('../conn.php');

    // Get and sanitize input
    echo $amount = (float) $_POST['amount']; // Ensuring it's a valid number
    $comments = trim($_POST['comments']); // Remove unnecessary spaces
    $service_date = $_POST['service_date'];
    $due_date = $_POST['due_date'];
    $finance_id = (int) $_POST['finance_id']; // Ensuring it's an integer

    // Update query
    $sql = "UPDATE csa_finance_invoiced SET amount = ?, comments = ?, service_date = ?, due_date = ? WHERE finance_id = ?";

    // Prepare and bind
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("dsssi", $amount, $comments, $service_date, $due_date, $finance_id);

        // Execute the query
        if ($stmt->execute()) {
            // Close statement before redirection
            $stmt->close();
            $conn->close();

            // Redirect and exit
            header('Location: paid_projects.php');
            exit;
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>



<?php
include './include/footer.php';
?>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
/** @var mysqli $conn */   // <-- add this line

include '../authentication.php'; // admin authentication check 
include '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

if ($user_role == 4) {
    header('Location:./payslip.php');
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
<?php
// Pagination and search setup
$orderColumn = isset($_GET['sort']) ? $_GET['sort'] : 'i.project_id';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
$offset = ($page - 1) * $perPage;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$orderDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';


// ---------- MAIN QUERY ----------
$sql = "SELECT ui.*, 
               v.company_name AS company_name, 
               s.name AS service_name,
               ui.project_id AS project_id
        FROM ready_to_pay ui 
        LEFT JOIN vendors v ON ui.vendor_id = v.id 
        LEFT JOIN services s ON ui.service_id = s.id 
      
        WHERE 1=1";

// ---------- COUNT QUERY ----------
$countSql = "SELECT COUNT(*) as total 
             FROM ready_to_pay ui 
             LEFT JOIN vendors v ON ui.vendor_id = v.id 
             LEFT JOIN services s ON ui.service_id = s.id 
             WHERE 1=1";

// ---------- SEARCH ----------
if (!empty($searchTerm)) {
    $escapedSearchTerm = $conn->real_escape_string($searchTerm);
    $sql .= " AND (
        ui.invoice_no LIKE '%$escapedSearchTerm%' OR 
        v.company_name LIKE '%$escapedSearchTerm%' OR 
        ui.project_id LIKE '%$escapedSearchTerm%' OR 
        EXISTS (
            SELECT 1 
            FROM services sx 
            WHERE ui.service_id LIKE CONCAT('%', sx.id, '%') 
            AND sx.name LIKE '%$escapedSearchTerm%'
        ) OR
        ui.amount LIKE '%$escapedSearchTerm%' OR 
        ui.comments LIKE '%$escapedSearchTerm%'
    )";
}

// ---------- FILTERS ----------

// Vendor filter
if (!empty($_GET['vendor'])) {
    $vendor = $conn->real_escape_string($_GET['vendor']);
    $sql .= " AND ui.vendor_id = '$vendor'";
    $countSql .= " AND ui.vendor_id = '$vendor'";
}

// Service filter (numeric field)
// Service filter (LONGTEXT field)
if (!empty($_GET['service'])) {
    $service = $conn->real_escape_string($_GET['service']); // escape input
    $sql .= " AND ui.service_id IN (
                      SELECT id FROM services WHERE name LIKE '%$service%'
                  )";

}


// Project filter
if (!empty($_GET['project'])) {
    $project = $conn->real_escape_string($_GET['project']);
    $sql .= " AND ui.project_id = '$project'";
    $countSql .= " AND ui.project_id = '$project'";
}


//  $orderDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

// Add sorting and pagination
$allowedColumns = ['ui.project_id', 'ui.invoice_no', 'ui.vendor_id', 'ui.service_id', 'ui.amount', 'ui.comments', 'ui.invoice_date', 'ui.timestamp',];
if (!in_array($orderColumn, $allowedColumns)) {
    $orderColumn = 'ui.project_id';
}
$sql .= " ORDER BY $orderColumn $orderDir LIMIT $offset, $perPage";
$orderColumn = isset($_GET['sort']) && in_array($_GET['sort'], $allowedColumns) ? $_GET['sort'] : 'project_id';
$orderDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';



// ---------- EXECUTE QUERIES ----------
$totalResult = $conn->query($countSql);
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $perPage);

$startEntry = ($totalRecords > 0) ? $offset + 1 : 0;
$endEntry = min($offset + $perPage, $totalRecords);

// Fetch paginated results
$info = $obj_admin->manage_all_info($sql);
$num_row = $info->rowCount();
?>





<!-- dashboard content  -->

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">

    </div>

    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

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
                            <select class="form-control mt-2" id="customer_name" name="customer" style="width: 100%;"
                                onchange="this.form.submit()">
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


            </div>
        </div>
    </div>



    <div class="card shadow mb-4">

        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mt-4">
                <!-- Left Side (Title) -->
                <h1 class="h3 mb-0 text-gray-800">Ready to Pay</h1>

                <!-- Right Side (Search + Filter + People Icon) -->
                <div class="d-flex align-items-center">
                    <?php if (!empty($_GET['search']) || !empty($_GET['team']) || !empty($_GET['customer']) || !empty($_GET['perPage']) || !empty($_GET['sort']) || !empty($_GET['dir'])): ?>
                        <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn btn-sm btn-danger mr-2">
                            Clear All Filters
                        </a>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <form id="searchForm" class="form-inline mr-2" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" id="searchInput" class="form-control form-control-sm"
                                placeholder="Search..."
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary btn-sm" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>


                </div>
            </div>
        </div>









        <div class="card shadow mb-4" style="border:none">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">

            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    // Get the invoice_no from the URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const invoiceNo = urlParams.get('invoice_no');

                    if (invoiceNo) {
                        // If invoice_no is found in the URL, set it in the DataTable search input
                        const dataTableSearchInput = $('#dataTable_filter input'); // This targets the DataTable's search input
                        dataTableSearchInput.val(invoiceNo); // Set the value of the DataTable search input

                        // Trigger the DataTable search
                        const dataTable = $('#dataTable').DataTable(); // Initialize DataTable if not done already
                        dataTable.search(invoiceNo).draw(); // Perform search in DataTable with the invoice number
                    }

                    // if (Services) {
                    //     const dataTableSearchInput = $('#dataTable_filter input');
                    //     dataTableSearchInput.val(Services);
                    //     const dataTable = $('#dataTable').DataTable();
                    //     dataTable.search(Services).draw();

                    // }
                });

                // Initialize DataTable if not initialized earlier
                $(document).ready(function () {
                    $('#dataTable').DataTable({
                        paging: false,      // ❌ remove pagination
                        searching: false,   // ❌ remove search box
                        info: false,
                        sorting: false,      // ❌ remove "Showing 1 to 10 of X entries"
                        lengthChange: false // ❌ remove dropdown for 10, 20, 50 entries
                    });
                });

            </script>

            <div class="card-body">
                <div class="table-responsive p-3">
                    <table class="table shadow-lg table-bordered table-sm" id="dataTable" name="dataTable"
                        style="border: 1px solid #dddddd70">
                        <div class="dataTables_length d-flex align-items-center" id="dataTable_length">
                            <label class="d-flex align-items-center ml-1 mb-4">
                                <span class="mr-2">Show</span>
                                <form method="GET" class="m-0">
                                    <select name="perPage"
                                        class="custom-select custom-select-sm form-control form-control-sm"
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
                                        <input type="hidden" name="search"
                                            value="<?php echo htmlspecialchars($_GET['search']); ?>">
                                    <?php endif; ?>
                                    <?php if (isset($_GET['team'])): ?>
                                        <input type="hidden" name="team"
                                            value="<?php echo htmlspecialchars($_GET['team']); ?>">
                                    <?php endif; ?>
                                    <?php if (isset($_GET['customer'])): ?>
                                        <input type="hidden" name="customer"
                                            value="<?php echo htmlspecialchars($_GET['customer']); ?>">
                                    <?php endif; ?>
                                </form>
                                <span class="ml-2">entries</span>
                            </label>
                        </div>
                        <thead style="height:4.5rem;">
                            <tr class="bg-danger text-light my-2 text-center">
                                <th scope="col" class="align-middle" width="7%" style="font-weight:300;">Select</th>
                                <th scope="col" class="align-middle" width="8%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.project_id&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.project_id') ? 'desc' : 'asc'; ?>">
                                        Project No
                                    </a>
                                </th>
                                <th scope="col" class="align-middle" width="11%" style="font-weight:400">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.invoice_no&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.invoice_no') ? 'desc' : 'asc'; ?>">
                                        Invoice No
                                    </a>
                                </th>
                                <th scope="col" class="align-middle fw-normal" width="13%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.vendor_id&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.vendor_id') ? 'desc' : 'asc'; ?>">
                                        Vendor Name
                                    </a>
                                </th>
                                <th scope="col" class="align-middle fw-normal" hidden width="13%"
                                    style="font-weight:500;">
                                    Vendor id</th>
                                <th scope="col" class="align-middle fw-normal" width="13%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.service_id&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.service_id') ? 'desc' : 'asc'; ?>">
                                        Services
                                    </a>
                                </th>
                                <th scope="col" class="align-middle fw-light" hidden width="10%"
                                    style="font-weight:500;">
                                    service id</th>
                                <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.amount&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.amount') ? 'desc' : 'asc'; ?>">
                                        Amount
                                    </a>
                                </th>
                                <th scope="col" class="align-middle fw-light" hidden width="10%"
                                    style="font-weight:500;">
                                    service id</th>
                                <th scope="col" class="align-middle" width="20%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.comments&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.comments') ? 'desc' : 'asc'; ?>">
                                        Comments
                                    </a>
                                </th>
                                <th scope="col" class="align-middle" width="10%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.invoice_date&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.invoice_date') ? 'desc' : 'asc'; ?>">
                                        Invoice Date
                                    </a>
                                </th>
                                <th scope="col" class="align-middle" width="10%" style="font-weight:500;">
                                    <a class="text-light" style="text-decoration:none;"
                                        href="?<?php echo getFilterQueryString(); ?>&sort=ui.timestamp&dir=<?php echo (isset($_GET['dir']) && $_GET['dir'] == 'asc' && $_GET['sort'] == 'ui.timestamp') ? 'desc' : 'asc'; ?>">
                                        Last Update
                                    </a>
                                </th>
                                <th scope="col" class="align-middle" style="font-weight:500;">Action</th>
                            </tr>
                        </thead>
                        <tbody style="background: #dddddd70;">

                            <?php

                            if ($num_row == 0) {
                                echo '<tr><td class="text-center" colspan="12">No invoices were found</td>
                        </tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {

                                ?>


                                <tr>
                                    <td><input type="checkbox" name="projects[]" class="select-project form-check w-100"
                                            value="<?php echo $row['project_id']; ?>"></td>
                                    <td class="project_id"><?php echo $row['project_id']; ?></td>
                                    <td class="invoice_no"><?php echo $row['invoice_no']; ?></td>
                                    <td class="company_name"><?php echo $row['company_name']; ?></td>
                                    <td class="vendor_id" hidden><?php echo $row['vendor_id']; ?></td>
                                    <td hidden class="service_name"><?php echo $row['service_name']; ?></td>
                                    <td hidden class="service_id"><?php echo $row['service_id']; ?></td>
                                    <td class="service_id_name">
                                        <?php
                                        $service_ids = json_decode($row['service_id'], true);

                                        if (is_array($service_ids)) {
                                            $names = [];

                                            foreach ($service_ids as $id) {
                                                $id = (int) $id; // ensure integer
                                                $result = $conn->query("SELECT name FROM services WHERE id = $id");

                                                if ($result && $row_service = $result->fetch_assoc()) {
                                                    $names[] = $row_service['name'];
                                                } else {
                                                    $names[] = "Unknown (ID $id)";
                                                    error_log("Service ID not found: " . $id);
                                                }
                                            }

                                            echo implode(', ', $names);
                                        } else {
                                            echo 'Invalid service IDs';
                                        }
                                        ?>
                                    </td>
                                    <td class="price"><?php echo $row['amount']; ?></td>
                                   <td class="comments">
    <?php
    $comment = $row['comments'];
    if (strlen($comment) > 50) {
        $short = substr($comment, 0, 50);
        ?>
        <span class="short"><?php echo htmlspecialchars($short); ?>...</span>
        <span class="full" style="display:none;"><?php echo htmlspecialchars($comment); ?></span>
        <a href="javascript:void(0);" class="toggle">View More</a>
        <?php
    } else {
        echo htmlspecialchars($comment);
    }
    ?>
</td>
                                    <td class="invoiceDate"><?php echo $row['invoice_date']; ?></td>
                                    <td class="invoiceDate"><?php echo date('Y-m-d', strtotime($row['timestamp'])); ?></td>
                                    <td>
                                        <div class="d-flex flex-nowrap">
                                            <button class="btn btn-warning btn-sm change-status-btn text-nowrap">Change
                                                Status</button>&nbsp;&nbsp;
                                            <button class="btn btn-info btn-sm edit-btn text-nowrap" data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                data-project_id="<?php echo $row['project_id']; ?>"
                                                data-invoice_no="<?php echo $row['invoice_no']; ?>"
                                                data-invoice_date="<?php echo $row['invoice_date']; ?>"
                                                data-amount="<?php echo $row['amount']; ?>"
                                                data-comments="<?php echo $row['comments']; ?>"
                                                data-vendor="<?php echo $row['vendor_id']; ?>"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-service='<?php echo $row['service_id']; ?>'>
                                                Edit
                                            </button>
                                            <form method="post">
                                                <button type="submit"
                                                    class=" ms-1 btn btn-danger btn-sm danger-status-btn text-nowrap"
                                                    name="delete"
                                                    onclick="return confirm('Are you sure you want to delete this record?');"
                                                    value="<?php echo $row['project_id'] . ',' . $row['invoice_no']; ?>">Delete</button>
                                            </form>
                                        </div>
                                    </td>

                                <?php } ?>
                            </tr>

                        </tbody>
                    </table>


                    <script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("toggle")) {
        let td = e.target.closest("td");
        let shortText = td.querySelector(".short");
        let fullText = td.querySelector(".full");
        
        if (fullText.style.display === "none") {
            fullText.style.display = "inline";
            shortText.style.display = "none";
            e.target.textContent = "View Less";
        } else {
            fullText.style.display = "none";
            shortText.style.display = "inline";
            e.target.textContent = "View More";
        }
    }
});
</script>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0 text-dark">
                            Showing <?php echo $startEntry; ?> to <?php echo $endEntry; ?> of
                            <?php echo $totalRecords; ?> entries
                            <?php if (!empty($searchTerm)): ?>
                                (filtered from total)
                            <?php endif; ?>
                        </p>

                        <ul class="pagination mb-0">
                            <?php
                            // Get current filter parameters
                            $filterParams = [];
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $filterParams['search'] = $_GET['search'];
                            }
                            if (isset($_GET['team']) && !empty($_GET['team'])) {
                                $filterParams['team'] = $_GET['team'];
                            }
                            if (isset($_GET['customer']) && !empty($_GET['customer'])) {
                                $filterParams['customer'] = $_GET['customer'];
                            }
                            if (isset($_GET['perPage']) && !empty($_GET['perPage'])) {
                                $filterParams['perPage'] = $_GET['perPage'];
                            }
                            $filterQuery = http_build_query($filterParams);
                            $querySeparator = empty($filterQuery) ? '' : '&';
                            ?>

                            <!-- Previous -->
                            <?php if ($page > 1): ?>
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


                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit Invoice</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="" method="POST" id="invoiceForm">
                                    <div class="modal-body">
                                        <input type="text" hidden name="id" id="id"></input>
                                        <div class="form-group">
                                            <label for="project_id">RAE Id</label>
                                            <input type="text" class="form-control" name="project_id"
                                                id="edit_project_id" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="invoice_no">Invoice Number</label>
                                            <input type="text" class="form-control" name="invoice_no"
                                                id="edit_invoice_no" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="invoice_date">Invoice Date</label>
                                            <input type="date" class="form-control" name="invoice_date"
                                                id="edit_invoice_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="amount">Amount</label>
                                            <input type="number" class="form-control" name="amount" step="0.01" min="0"
                                                id="edit_amount" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="comments">Comments</label>
                                            <textarea class="form-control" name="comments"
                                                id="edit_comments"></textarea>
                                        </div>
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
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php
                    if (isset($_POST["edit"])) {
                        $project_id = $_POST["project_id"];
                        $vendor_id = $_POST["vendor"];
                        $invoice_no = $_POST["invoice_no"];
                        $comments = $_POST["comments"];
                        $invoice_date = $_POST["invoice_date"];
                        $id = $_POST["id"];
                        $amount = $_POST["amount"];
                        $service_ids = isset($_POST["services"]) ? $_POST["services"] : [];





                        // Convert service IDs array into a JSON format
                        $service_ids_str = json_encode($service_ids);
                        $check_queries = [
                            'paidinvoices' => "SELECT 'found' FROM paidinvoices WHERE invoice_no = ? AND project_id = ? ",
                            'unpaidinvoices' => "SELECT 'found' FROM unpaidinvoices WHERE invoice_no = ? AND project_id = ? "
                        ];

                        foreach ($check_queries as $table => $query) {
                            $check_stmt = $conn->prepare($query);
                            if (!$check_stmt) {
                                die('Error preparing check statement: ' . $conn->error);
                            }
                            $table_name = ($table === 'paidinvoices') ? 'Paid Invoice' : (($table === 'unpaidinvoices') ? 'Unpaid Invoice' : ucfirst(str_replace('_', ' ', $table)));

                            $check_stmt->bind_param("ss", $invoice_no, $project_id);
                            $check_stmt->execute();
                            $check_stmt->store_result();

                            if ($check_stmt->num_rows > 0) {
                                echo "<script>alert('Invoice number already exists in table: $table_name. with same RAE Id'); window.history.back();</script>";
                                $check_stmt->close();
                                exit; // Prevent further execution
                            }

                            $check_stmt->close();
                        }
                        // Check if the invoice_no and project_id already exist in ready_to_pay
                        $check_query = "SELECT id FROM ready_to_pay WHERE invoice_no = ? AND project_id = ? AND id != ?";
                        $check_stmt = $conn->prepare($check_query);
                        $check_stmt->bind_param("ssi", $invoice_no, $project_id, $id);
                        $check_stmt->execute();
                        $check_stmt->store_result();

                        if ($check_stmt->num_rows > 0) {
                            echo "<script>alert('Invoice number already exists with the same RAE Id. Update not allowed.'); window.history.back();</script>";
                            $check_stmt->close();
                            exit;
                        }
                        $check_stmt->close();


                        // Debugging output
                        echo '<pre>';
                        print_r($_POST);
                        echo '</pre>';

                        // Prepare the SQL query
                        $sql = "UPDATE ready_to_pay 
                    SET vendor_id = ?, comments = ?, invoice_date = ?, amount = ?, service_id = ?, project_id = ?, invoice_no = ?,timestamp = NOW()
                    WHERE id = ?";

                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            // Bind parameters in the correct order
                            $stmt->bind_param("issdsssi", $vendor_id, $comments, $invoice_date, $amount, $service_ids_str, $project_id, $invoice_no, $id);

                            // Execute the update
                            if ($stmt->execute()) {
                                echo "<script>alert('Invoice updated successfully!'); window.location.href='readyToPay.php';</script>";
                            } else {
                                echo "<script>alert('Error updating invoice. Please try again.');</script>";
                            }

                            // Close the statement
                            $stmt->close();
                        } else {
                            echo "<script>alert('Database error: Unable to prepare query.');</script>";
                        }

                        // Close the database connection
                        $conn->close();
                    }
                    ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            var editModal = document.getElementById("editModal");
                            var selectedServicesContainer = document.getElementById("selectedServices");
                            var serviceDropdown = document.getElementById("serviceDropdown");
                            var selectedServices = new Set();

                            function addService(serviceId, serviceName) {
                                if (!selectedServices.has(serviceId)) {
                                    selectedServices.add(serviceId);
                                    const serviceElement = document.createElement("div");
                                    serviceElement.classList.add("selected-service");
                                    serviceElement.setAttribute("data-id", serviceId);
                                    serviceElement.innerHTML = `${serviceName} <span>&times;</span>`;

                                    serviceElement.querySelector("span").addEventListener("click", () => {
                                        selectedServices.delete(serviceId);
                                        serviceElement.remove();
                                    });

                                    selectedServicesContainer.appendChild(serviceElement);
                                }
                            }

                            editModal.addEventListener("show.bs.modal", function (event) {
                                var button = event.relatedTarget;

                                document.getElementById("edit_project_id").value = button.getAttribute("data-project_id");
                                document.getElementById("edit_invoice_no").value = button.getAttribute("data-invoice_no");
                                document.getElementById("edit_invoice_date").value = button.getAttribute("data-invoice_date");
                                document.getElementById("edit_amount").value = button.getAttribute("data-amount");
                                document.getElementById("edit_comments").value = button.getAttribute("data-comments");
                                document.getElementById("id").value = button.getAttribute("data-id");

                                var vendorSelect = document.getElementById("vendor");
                                var vendorValue = button.getAttribute("data-vendor");
                                if (vendorSelect) vendorSelect.value = vendorValue;

                                var statusSelect = document.getElementById("invoice_status");
                                var statusValue = button.getAttribute("data-status");
                                if (statusSelect) statusSelect.value = statusValue;

                                // ✅ Clear previous selected services
                                selectedServicesContainer.innerHTML = "";
                                selectedServices.clear();

                                // ✅ Get services from the button attribute
                                var serviceData = button.getAttribute("data-service");

                                if (serviceData) {
                                    try {
                                        let serviceIds = JSON.parse(serviceData);

                                        if (!Array.isArray(serviceIds)) {
                                            serviceIds = [serviceIds]; // Convert single value to an array
                                        }

                                        serviceIds.forEach((serviceId) => {
                                            let serviceOption = serviceDropdown.querySelector(`option[value="${serviceId}"]`);
                                            if (serviceOption) {
                                                addService(serviceId, serviceOption.textContent);
                                            }
                                        });
                                    } catch (e) {
                                        console.error("Error parsing service data: ", e);
                                    }
                                }
                            });

                            // ✅ Handle service selection dropdown
                            serviceDropdown.addEventListener("change", () => {
                                const selectedOption = serviceDropdown.options[serviceDropdown.selectedIndex];
                                const serviceId = selectedOption.value;
                                const serviceName = selectedOption.text;

                                if (serviceId && !selectedServices.has(serviceId)) {
                                    addService(serviceId, serviceName);
                                }

                                serviceDropdown.value = "";
                            });

                            // ✅ Handle form submission to include selected services
                            document.getElementById("invoiceForm").addEventListener("submit", (e) => {
                                const serviceList = Array.from(selectedServices);

                                if (serviceList.length === 0) {
                                    alert("Please select at least one service.");
                                    e.preventDefault();
                                    return;
                                }

                                // Clear previous hidden inputs
                                document.querySelectorAll('input[name="services[]"]').forEach((input) => input.remove());

                                // Add hidden inputs for each selected service
                                serviceList.forEach((serviceId) => {
                                    const hiddenInput = document.createElement("input");
                                    hiddenInput.type = "hidden";
                                    hiddenInput.name = "services[]";
                                    hiddenInput.value = serviceId;
                                    document.getElementById("invoiceForm").appendChild(hiddenInput);
                                });
                            });
                        });
                    </script>
                    <!-- Modal for status change -->
                    <div class="modal fade" id="statusChangeModal" tabindex="-1" role="dialog"
                        aria-labelledby="statusChangeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="statusChangeModalLabel">Change Status</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="" method="POST" id="statusChangeForm">
                                    <div class="modal-body">
                                        <input type="hidden" name="project_id" id="modal_project_id">
                                        <input type="hidden" name="invoice_no" id="modal_invoice_no">
                                        <input type="hidden" name="vendor_id" id="modal_vendor_id">
                                        <input type="hidden" name="comments" id="modal_comments">
                                        <input type="hidden" name="service_id" id="modal_service_id">
                                        <input type="hidden" name="invoice_date" id="modal_invoice_date">
                                        <input type="hidden" name="amount" id="modal_amount">

                                        <div class="form-group">
                                            <label for="statusDropdown">Select Status</label>
                                            <select class="form-control" id="statusDropdown" name="status">
                                                <option disabled value="ready_to_pay">Ready to Pay</option>
                                                <option value="paid">Paid</option>
                                                <option value="unpaid">Unpaid</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="updateStatus" class="btn btn-primary">Save
                                            Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            // Add event listener to all "Change Status" buttons
                            document.querySelectorAll(".change-status-btn").forEach(button => {
                                button.addEventListener("click", function () {
                                    // Get the closest row (<tr>) for the clicked button
                                    const row = this.closest("tr");

                                    // Retrieve the data from the row and check if elements exist before accessing textContent
                                    const projectId = row.querySelector(".project_id") ? row.querySelector(".project_id").textContent.trim() : '';
                                    const invoiceNo = row.querySelector(".invoice_no") ? row.querySelector(".invoice_no").textContent.trim() : '';
                                    const vendorId = row.querySelector(".vendor_id") ? row.querySelector(".vendor_id").textContent.trim() : '';
                                    const comments = row.querySelector(".comments") ? row.querySelector(".comments").textContent.trim() : '';
                                    const serviceId = row.querySelector(".service_id") ? row.querySelector(".service_id").textContent.trim() : '';
                                    const invoiceDate = row.querySelector(".invoiceDate") ? row.querySelector(".invoiceDate").textContent.trim() : '';
                                    const amount = row.querySelector(".price") ? row.querySelector(".price").textContent.trim() : '';

                                    // Set the values in the modal's hidden inputs
                                    document.getElementById("modal_project_id").value = projectId;
                                    document.getElementById("modal_invoice_no").value = invoiceNo;
                                    document.getElementById("modal_vendor_id").value = vendorId;
                                    document.getElementById("modal_comments").value = comments;
                                    document.getElementById("modal_service_id").value = serviceId;
                                    document.getElementById("modal_invoice_date").value = invoiceDate;
                                    document.getElementById("modal_amount").value = amount;

                                    // Show the modal (if not already shown)
                                    $('#statusChangeModal').modal('show');
                                });
                            });
                        });
                    </script>





                    <!-- Modal for displaying selected projects details -->
                    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog"
                        aria-labelledby="confirmationModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Paid</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="" method="POST">

                                    <div class="modal-body">
                                        <!-- Due Date Input Field (Separate) -->


                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>RAE ID</th>
                                                    <th>Invoice No.</th>
                                                    <th>Vendor name</th>
                                                    <th>Service name</th>
                                                    <th>Price</th>
                                                    <th>Comments</th>
                                                    <th>Invoice Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="projectDetailsList">
                                                <!-- Project details will be dynamically inserted here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="markaspaid" class="btn btn-primary"
                                            id="confirmButton">Confirm</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Button to trigger the modal -->
                    <div class="d-flex mt-3">
                        <button type="button" class="btn btn-success" name="markaspaid" onclick="submitAllForms()">Mark
                            As
                            Paid</button>
                    </div>



                    <script>
                        function submitAllForms() {
                            // Get all checked checkboxes with class 'select-project'
                            const selectedProjects = document.querySelectorAll('.select-project:checked');
                            console.log(selectedProjects)

                            // Check if no projects are selected
                            if (selectedProjects.length === 0) {
                                alert('Please select at least one project.');
                                return;
                            }

                            // Get the modal and project details list (tbody)
                            const modal = $('#confirmationModal');
                            const projectDetailsList = document.getElementById('projectDetailsList');
                            projectDetailsList.innerHTML = ''; // Clear previous project details

                            // Loop through selected projects to collect their details
                            selectedProjects.forEach(project => {
                                const projectId = project.value;
                                const row = project.closest('tr');

                                // Gather related data from the row
                                const invoice_no = row.querySelector('.invoice_no').textContent;
                                const price = row.querySelector('.price').textContent;
                                const company_name = row.querySelector('.company_name').textContent;
                                const vendor_id = row.querySelector('.vendor_id').textContent;
                                const service_name = row.querySelector('.service_id_name').textContent.trim();
                                const service_id = row.querySelector('.service_id').textContent;
                                const comments = row.querySelector('.comments').textContent;
                                const invoiceDate = row.querySelector('.invoiceDate').textContent;

                                // Create a new table row for the selected project details
                                const tr = document.createElement('tr');

                                // Add project ID, price, comments, and service date to the row
                                tr.innerHTML = `
                                <td><input type="hidden" name="projects[]" value="${projectId}" />${projectId}</td>
                                <td><input type="hidden" name="invoice_no[${projectId}]" value="${invoice_no}" />${invoice_no}</td>
                                <td><input type="hidden" name="vendor_id[${projectId}]" value="${vendor_id}" />${company_name}</td>
                                <td><input type="hidden" name="service_id[${projectId}]" value='${service_id}' />${service_name}</td>
                                <td><input type="hidden" name="price[${projectId}]" value="${price}" />${price}</td>
                                <td><input type="hidden" name="comments[${projectId}]" value="${comments}" />${comments}</td>
                                <td><input type="hidden" name="invoiceDate[${projectId}]" value="${invoiceDate}" />${invoiceDate}</td>
                            `;

                                // Append the row to the modal table
                                projectDetailsList.appendChild(tr);
                            });

                            // Show the modal for confirmation
                            modal.modal('show');
                        }
                    </script>

                </div>
            </div>





        </div>
    </div>




    <script>
        function fetch_project_id(project_id) {
            // Redirect to the URL with the project ID
            const newurl = window.location.pathname + "?project_id=" + project_id;
            history.pushState(null, null, newurl);
            showmodal();

        }

        function showmodal() {
            // Show the Bootstrap modal
            $('#setTargetmodal').modal('show');
        }
    </script>

    <!-- set target modal  -->

    <div class="modal fade " id="setTargetmodal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-sm modal-dialog-centered">

            <div class="modal-content">

                <div class="form-container mt-2 m-2">

                    <form class="" action="" method="post" enctype="multipart/form-data">
                        <label class="control-label text-dark" for="">Target Date</label>
                        <input class="form-control" type="date" name="targetdate" id="targetdate">
                        <button class="btn btn-primary mt-2" name="setTarget">Submit</button>
                    </form>

                </div>

            </div>

        </div>

    </div>

    <script>
        // Function to filter table based on selected team
        function filterTableByTeam() {
            var selectedTeam = document.getElementById('team-filter').value;
            var table = document.getElementById('dataTable');
            var rows = table.getElementsByTagName('tr');

            for (var i = 0; i < rows.length; i++) {
                var teamInput = rows[i].querySelector('input[name="p_team"]');
                if (teamInput) {
                    var teamName = teamInput.value;
                    if (selectedTeam === '' || teamName === selectedTeam) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }

        // Event listener for dropdown change
        document.getElementById('team-filter').addEventListener('change', filterTableByTeam);

        // Event listener for the "Apply Filter" button
        document.getElementById('apply-filter').addEventListener('click', filterTableByTeam);

        $(document).ready(function () {
            $('#customer_name').select2();
        });

        // Function to filter table based on selected customer name
        function filterTableByCustomer() {
            var selectedCustomer = document.getElementById('customer_name').value;
            var table = document.getElementById('dataTable');
            var rows = table.getElementsByTagName('tr');

            for (var i = 0; i < rows.length; i++) {
                var customerCell = rows[i].getElementsByTagName('td')[2];
                if (customerCell) {
                    var customerName = customerCell.querySelector('input[name="customer_name"]').value;
                    if (selectedCustomer === '' || customerName === selectedCustomer) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }

        // Event listener for dropdown change
        document.getElementById('customer_name').addEventListener('change', filterTableByCustomer);

        // Event listener for the "Apply Filter" button
        document.getElementById('applyFilter').addEventListener('click', filterTableByCustomer);
    </script>



    <?php
    if (isset($_POST['markaspaid'])) {
        $projects = $_POST['projects'] ?? []; // Array of selected project IDs
    
        if (empty($projects)) {
            die('No projects selected.');
        }

        foreach ($projects as $project_id) {
            $invoice_no = $_POST['invoice_no'][$project_id] ?? null;
            $comments = $_POST['comments'][$project_id] ?? null;
            $invoice_date = $_POST['invoiceDate'][$project_id] ?? null;
            $amount = $_POST['price'][$project_id] ?? null;
            $vendor_id = $_POST['vendor_id'][$project_id] ?? null;
            $service_ids = $_POST['service_id'][$project_id] ?? [];

            // ✅ Ensure service_ids is always an array and encode it correctly
            if (!is_array($service_ids)) {
                $service_ids = json_decode($service_ids, true);
                if (!is_array($service_ids)) {
                    $service_ids = [$service_ids]; // Convert single value to array
                }
            }
            $service_ids_str = json_encode($service_ids);

            // ✅ Validate JSON encoding
            if (json_last_error() !== JSON_ERROR_NONE) {
                die('Error encoding service IDs for project ID ' . $project_id . ': ' . json_last_error_msg());
            }
            // ✅ Insert into ready_to_pay table
            $insert_sql = "INSERT INTO paidinvoices (project_id, invoice_no, comments, vendor_id, service_id, invoice_date, amount) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                die('Insert Statement Error: ' . $conn->error);
            }



            // ✅ Correct type binding (use "d" for double amount)
            $insert_stmt->bind_param("isssssd", $project_id, $invoice_no, $comments, $vendor_id, $service_ids_str, $invoice_date, $amount);

            if (!$insert_stmt->execute()) {
                die('Error saving invoice for project ID ' . $project_id . ': ' . $insert_stmt->error);
            }

            $insert_stmt->close();


            // ✅ Delete from unpaidinvoices before inserting
            $delete_sql = "DELETE FROM ready_to_pay WHERE invoice_no = ? AND project_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if (!$delete_stmt) {
                die('Delete Statement Error: ' . $conn->error);
            }
            $delete_stmt->bind_param("si", $invoice_no, $project_id);
            if (!$delete_stmt->execute()) {
                die('Error executing DELETE for project ID ' . $project_id . ': ' . $delete_stmt->error);
            }
            $delete_stmt->close();
        }

        // ✅ Redirect after successful insertion
        header('Location: readyToPay.php');
        exit;
    }


    ?>


    <?php
    if (isset($_POST['updateStatus'])) {
        // Retrieve form data
        $project_id = $_POST['project_id'] ?? null;
        $invoice_no = $_POST['invoice_no'] ?? null;
        $status = $_POST['status'] ?? null;
        $vendor_id = $_POST['vendor_id'] ?? null;        // Assuming you have this data
        $comments = $_POST['comments'] ?? null;           // Assuming you have this data
        $service_id = $_POST['service_id'] ?? null;       // Assuming you have this data
        $invoice_date = $_POST['invoice_date'] ?? null;   // Assuming you have this data
        $amount = $_POST['amount'] ?? null;               // Assuming you have this data
    
        // Get booked_date and received_date from unpaid table
        $dates_sql = "SELECT booked_date, received_date FROM ready_to_pay WHERE project_id = ? AND invoice_no = ?";
        $dates_stmt = $conn->prepare($dates_sql);
        $dates_stmt->bind_param("is", $project_id, $invoice_no);
        $dates_stmt->execute();
        $dates_result = $dates_stmt->get_result();
        $dates_row = $dates_result->fetch_assoc();
        $booked_date = $dates_row['booked_date'];
        $received_date = $dates_row['received_date'];
        $dates_stmt->close();
        // Input validation
        // Check if any required field is empty
        if (empty($project_id) || empty($invoice_no) || empty($status) || empty($vendor_id) || empty($comments) || empty($service_id) || empty($invoice_date) || (!isset($amount) && $amount != 0)) {
            die('<script>alert("All fields are required.")</script>');
        }


        // Determine the table based on the selected status
        $table = '';
        switch ($status) {
            case 'ready_to_pay':
                $table = 'ready_to_pay';
                break;
            case 'paid':
                $table = 'paidinvoices';
                break;
            case 'unpaid':
                $table = 'unpaidinvoices';
                break;
            default:
                die('Invalid status selected.');
        }

        // Start transaction to ensure atomicity (optional but good practice)
        $conn->begin_transaction();

        try {
            // First, delete the record from all status tables
            $delete_sql = "DELETE FROM ready_to_pay WHERE project_id = ? AND invoice_no = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("is", $project_id, $invoice_no);
            if ($delete_stmt->execute()) {
            }
            $delete_stmt->close();

            $delete_sql = "DELETE FROM paidinvoices WHERE project_id = ? AND invoice_no = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("is", $project_id, $invoice_no);
            // if ($delete_stmt->execute()) {
            //     echo "<script>alert('Record deleted successfully from paidinvoices!');</script>";
            // }
            $delete_stmt->close();

            $delete_sql = "DELETE FROM unpaidinvoices WHERE project_id = ? AND invoice_no = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("is", $project_id, $invoice_no);
            if ($delete_stmt->execute()) {
            }
            $delete_stmt->close();

            $insert_sql = "INSERT INTO $table (`project_id`, `vendor_id`, `invoice_no`, `comments`, `service_id`, `invoice_date`, `amount`,`booked_date`,`received_date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            if (!$insert_stmt) {
                throw new Exception("Error preparing insert statement: " . $conn->error);
            }

            // Bind all parameters for the INSERT statement
            $service_id_json = is_array($service_id) ? json_encode($service_id) : $service_id;
            $insert_stmt->bind_param("issssssss", $project_id, $vendor_id, $invoice_no, $comments, $service_id_json, $invoice_date, $amount, $booked_date, $received_date);
            // Execute the insert statement
            if ($insert_stmt->execute()) {
                $conn->commit();
                echo "<script>alert('Project Updated Successfully');</script>";
                echo "<script>window.location.href = 'readyToPay.php';</script>"; // Redirect after alert
                exit;
            } else {
                throw new Exception("Error executing insert statement: " . $insert_stmt->error);
            }


        } catch (Exception $e) {
            // Rollback transaction if an error occurs
            $conn->rollback();
            echo 'Error updating status: ' . $e->getMessage();
        }
        $insert_stmt->close();
    }
    ?>


    <?php
    // Include your database connection code here
    
    // Check if the delete button was clicked by checking the name
    if (isset($_POST['delete'])) {
        // Get the project_id and invoice_no from the form submission
        list($project_id, $invoice_no) = explode(',', $_POST['delete']);

        // Prepare SQL to delete the record based on project_id and invoice_no
        $sql = "DELETE FROM ready_to_pay WHERE project_id = ? AND invoice_no = ?";

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters (project_id and invoice_no) to the prepared statement
            $stmt->bind_param("is", $project_id, $invoice_no);

            // Execute the delete query
            if ($stmt->execute()) {
                // Record deleted successfully, show alert and redirect
                echo "<script>
                    alert('Record deleted successfully!');
                    window.location.href = 'readyToPay.php';
                  </script>";
            } else {
                // Error deleting record, show alert and stay on the same page
                echo "<script>
                    alert('Error deleting record: " . $stmt->error . "');
                  </script>";
            }

            // Close the statement
            $stmt->close();
        } else {
            // Error preparing the query, show alert
            echo "<script>
                alert('Error preparing the query: " . $conn->error . "');
              </script>";
        }
    }
    ?>
    <!-- 
<?php
$projectId = 32371;

$checkSql = "SELECT COUNT(*) as cnt 
             FROM ready_to_pay 
             WHERE project_id = '$projectId'";

$checkResult = $conn->query($checkSql);
$checkRow = $checkResult->fetch_assoc();

echo ($checkRow['cnt'] > 0) ? 1 : 0;
?> -->
    <!-- <?php
    $serviceName = 'Riffpedia'; // service name to check
    
    $checkSql = "SELECT COUNT(*) as cnt 
             FROM ready_to_pay ui
             LEFT JOIN services s ON ui.service_id = s.id
             WHERE s.name = '" . $conn->real_escape_string($serviceName) . "'";

    $checkResult = $conn->query($checkSql);
    $checkRow = $checkResult->fetch_assoc();

    echo ($checkRow['cnt'] > 0) ? 1 : 0;
    ?> -->


    <?php
    include './include/footer.php';
    ?>
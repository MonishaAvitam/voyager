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
    header('Location: ../index.php');
}

$success = isset($_GET['success']) ? $_GET['success'] : 0;


// check admin
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';

?>
<?php

// Pagination and search setup
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
$offset = ($page - 1) * $perPage;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$orderColumn = isset($_GET['sort']) ? $_GET['sort'] : 'i.project_id';
$orderDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

$allowedColumns = [
    'project_id',
    'project_name',
    'p_team',
    'invoice_number',
    'customer_name',
    'price',
    'comments',
    'service_date',
    'due_date',
    'project_status',
    'id'
];

$orderColumn = isset($_GET['sort']) && in_array($_GET['sort'], $allowedColumns) ? $_GET['sort'] : 'id';
$orderDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

// Modify your existing SQL query to add LIMIT for pagination
$sql = "
SELECT *
FROM (
    SELECT 
        dd.project_id,
        dd.urgency,
        dd.reopen_status,
        NULL AS revision_project_id,
        dd.project_name,
        dd.p_team,
        dd.setTarget,
        dd.setTargetDate,
        NULL AS quick_project,
        c.customer_name,
        sp.price,
        sp.comments,
        sp.invoice_number,
        sp.last_modified_date,
        sp.rownumber,
        sp.id,
        sp.service_date,
        sp.due_date,
        sp.project_status
    FROM 
        deliverable_data dd 
    LEFT JOIN 
        contacts c ON dd.contact_id = c.contact_id
    LEFT JOIN 
        csa_finance_readytobeinvoiced sp ON dd.project_id = sp.project_id
    WHERE 
        EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id)
    AND NOT EXISTS (
        SELECT 1 
        FROM csa_finance_invoiced i 
        WHERE i.project_id = sp.project_id 
        AND i.rownumber = sp.rownumber
    )";

// Search and filter conditions for first part
if (!empty($searchTerm)) {
    $sql .= " AND (
        dd.project_id LIKE '%$searchTerm%' OR 
        dd.project_name LIKE '%$searchTerm%' OR 
        dd.p_team LIKE '%$searchTerm%' OR 
        sp.invoice_number LIKE '%$searchTerm%' OR 
        c.customer_name LIKE '%$searchTerm%' OR 
        sp.comments LIKE '%$searchTerm%'
    )";
}

if (!empty($_GET['team'])) {
    $team = $_GET['team'];
    $sql .= " AND dd.p_team = '$team'";
}

if (!empty($_GET['customer'])) {
    $customer = $_GET['customer'];
    $sql .= " AND c.customer_name = '$customer'";
}

$sql .= "
    UNION ALL

    SELECT 
        p.project_id,
        p.urgency,
        p.reopen_status,
        p.revision_project_id,
        p.project_name,
        p.p_team,
        p.setTarget,
        p.setTargetDate,
        p.quick_project,
        c.customer_name,
        sp.price,
        sp.comments,
        sp.invoice_number,
        sp.last_modified_date,
        sp.rownumber,
        sp.id,
        sp.service_date,
        sp.due_date,
        sp.project_status
    FROM 
        projects p 
    LEFT JOIN 
        contacts c ON p.contact_id = c.contact_id
    LEFT JOIN 
        csa_finance_readytobeinvoiced sp ON p.project_id = sp.project_id
    WHERE 
        EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id)";

// Search and filter conditions for second part
if (!empty($searchTerm)) {
    $sql .= " AND (
        p.project_id LIKE '%$searchTerm%' OR 
        p.project_name LIKE '%$searchTerm%' OR 
        p.p_team LIKE '%$searchTerm%' OR 
        sp.invoice_number LIKE '%$searchTerm%' OR 
        c.customer_name LIKE '%$searchTerm%' OR 
        sp.comments LIKE '%$searchTerm%'
    )";
}

if (!empty($_GET['team'])) {
    $sql .= " AND p.p_team = '$team'";
}

if (!empty($_GET['customer'])) {
    $sql .= " AND c.customer_name = '$customer'";
}

$sql .= "
) AS combined_data
WHERE NOT EXISTS (
    SELECT 1 
    FROM csa_finance_invoiced i 
    WHERE i.project_id = combined_data.project_id 
    AND i.rownumber = combined_data.rownumber
)
ORDER BY combined_data.$orderColumn $orderDir
LIMIT $offset, $perPage";

$countSql = "
SELECT COUNT(*) as total 
FROM (
    SELECT 
        dd.project_id,
        sp.rownumber
    FROM 
        deliverable_data dd 
    LEFT JOIN 
        contacts c ON dd.contact_id = c.contact_id
    LEFT JOIN 
        csa_finance_readytobeinvoiced sp ON dd.project_id = sp.project_id
    WHERE 
        EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id)
    AND NOT EXISTS (
        SELECT 1 
        FROM csa_finance_invoiced i 
        WHERE i.project_id = sp.project_id 
        AND i.rownumber = sp.rownumber
    )";

// Filters for first count part
if (!empty($searchTerm)) {
    $countSql .= " AND (
        dd.project_id LIKE '%$searchTerm%' OR 
        dd.project_name LIKE '%$searchTerm%' OR 
        dd.p_team LIKE '%$searchTerm%' OR 
        sp.invoice_number LIKE '%$searchTerm%' OR 
        c.customer_name LIKE '%$searchTerm%' OR 
        sp.comments LIKE '%$searchTerm%'
    )";
}

if (!empty($_GET['team'])) {
    $countSql .= " AND dd.p_team = '$team'";
}

if (!empty($_GET['customer'])) {
    $countSql .= " AND c.customer_name = '$customer'";
}

$countSql .= "
    UNION ALL
    
    SELECT 
        p.project_id,
        sp.rownumber
    FROM 
        projects p 
    LEFT JOIN 
        contacts c ON p.contact_id = c.contact_id
    LEFT JOIN 
        csa_finance_readytobeinvoiced sp ON p.project_id = sp.project_id
    WHERE 
        EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id)";

// Filters for second count part
if (!empty($searchTerm)) {
    $countSql .= " AND (
        p.project_id LIKE '%$searchTerm%' OR 
        p.project_name LIKE '%$searchTerm%' OR 
        p.p_team LIKE '%$searchTerm%' OR 
        sp.invoice_number LIKE '%$searchTerm%' OR 
        c.customer_name LIKE '%$searchTerm%' OR 
        sp.comments LIKE '%$searchTerm%'
    )";
}

if (!empty($_GET['team'])) {
    $countSql .= " AND p.p_team = '$team'";
}

if (!empty($_GET['customer'])) {
    $countSql .= " AND c.customer_name = '$customer'";
}

$countSql .= "
) AS combined_data
WHERE NOT EXISTS (
    SELECT 1 
    FROM csa_finance_invoiced i 
    WHERE i.project_id = combined_data.project_id 
    AND i.rownumber = combined_data.rownumber
)";

$totalResult = $conn->query($countSql);
$totalRow = $totalResult->fetch_assoc();
$totalProjects = $totalRow['total'];
$totalPages = ceil($totalProjects / $perPage);

$startEntry = $offset + 1;
$endEntry = min($offset + $perPage, $totalProjects);
?>


<script>
    <?php if ($success == 1): ?>
        // Show Toast with the success message
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end', // Position the toast at the top right
            showConfirmButton: false, // Hide the confirm button
            timer: 3000, // Auto-dismiss the toast after 3 seconds
            timerProgressBar: true, // Show a progress bar for the timer
            onClose: () => {
                // Redirect to the current page without query parameters
                window.location.href = window.location.pathname;
            }
        });

        Toast.fire({
            icon: 'success',
            title: 'Project successfully added!'
        }).then((result) => {
            // If the toast is clicked, redirect immediately
            if (result.dismiss === Swal.DismissReason.timer || result.isConfirmed) {
                window.location.href = window.location.pathname; // Redirect to the current page
            }
        });

    <?php endif; ?>

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
    $(document).ready(function () {
        $('#dataTable').DataTable(); // Initialize DataTable
    });
</script>


<!-- dashboard content  -->
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
    return http_build_query($params);
}
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ready To Invoice</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Ready To Invoice Tab</h6>
            <div class="d-flex">
                <?php
                if (
                    !empty($_GET['search']) ||
                    !empty($_GET['team']) ||
                    !empty($_GET['customer']) ||
                    !empty($_GET['perPage']) ||
                    !empty($_GET['sort']) ||
                    !empty($_GET['dir'])
                ): ?>
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

                            <!-- Reset to first page when changing perPage -->
                            <input type="hidden" name="page" value="1">

                            <!-- Preserve existing filters -->
                            <?php if (!empty($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>
                            <?php if (!empty($_GET['team'])): ?>
                                <input type="hidden" name="team" value="<?php echo htmlspecialchars($_GET['team']); ?>">
                            <?php endif; ?>
                            <?php if (!empty($_GET['customer'])): ?>
                                <input type="hidden" name="customer"
                                    value="<?php echo htmlspecialchars($_GET['customer']); ?>">
                            <?php endif; ?>

                            <!-- Preserve current sorting -->
                            <?php if (!empty($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                            <?php endif; ?>
                            <?php if (!empty($_GET['dir'])): ?>
                                <input type="hidden" name="dir" value="<?php echo htmlspecialchars($_GET['dir']); ?>">
                            <?php endif; ?>
                        </form>
                        <span class="ml-2">entries</span>
                    </label>
                </div>

                <div class="table-responsive">



                    <table class="table  table-bordered table-sm " id="dataTable">

                        <thead style="height:4.5rem;">
                            <tr style="background: #f25730" class="bg-c-pink text-light my-2 text-center">
                                <th scope="col" class="align-middle" width="7%" style="font-weight:300;">Select</th>

                                <?php
                                // Define columns and display labels
                                $columns = [
                                    'project_id' => ['label' => 'Project No.', 'weight' => 200, 'width' => '8%'],
                                    'project_name' => ['label' => 'Project Title', 'weight' => 400, 'width' => '7%'],
                                    'customer_name' => ['label' => 'Customer Name', 'weight' => 500, 'width' => '8%'],
                                    'p_team' => ['label' => 'Team', 'weight' => 500, 'width' => '6%'],
                                    'comments' => ['label' => 'Comments', 'weight' => 500, 'width' => ''],
                                    'price' => ['label' => 'Amount', 'weight' => 500, 'width' => ''],
                                    'invoice_number' => ['label' => 'Invoice No:', 'weight' => 500, 'width' => ''],
                                    'service_date' => ['label' => 'Service Date', 'weight' => 500, 'width' => ''],
                                    'due_date' => ['label' => 'Due Date', 'weight' => 500, 'width' => ''],
                                    'last_modified_date' => ['label' => 'Last Modified', 'weight' => 500, 'width' => ''],
                                ];

                                foreach ($columns as $col => $props) {
                                    $dir = 'asc';
                                    if (isset($_GET['sort']) && $_GET['sort'] == $col) {
                                        // Toggle sort direction
                                        $dir = (isset($_GET['dir']) && $_GET['dir'] === 'asc') ? 'desc' : 'asc';
                                    }

                                    $filterQuery = getFilterQueryString();
                                    $querySeparator = empty($filterQuery) ? '' : '&';
                                    echo "<th scope='col' class='align-middle' width='{$props['width']}' style='font-weight:{$props['weight']};'>
                    <a href='?sort={$col}&dir={$dir}{$querySeparator}' class='text-light' style='text-decoration:none;'>{$props['label']}</a>
                  </th>";
                                }
                                ?>

                                <th scope="col" class="align-middle" width="10%" style="font-weight:500;">Action</th>
                            </tr>
                        </thead>


                        <tbody style="background: #dddddd70; ">

                            <!-- ... Your table rows ... -->
                            <?php
                            $updateQuery = "UPDATE csa_finance_readytobeinvoiced 
                         SET rownumber = 0 
                         WHERE rownumber IS NULL OR rownumber = ''";

                            // Execute the update query
                            mysqli_query($conn, $updateQuery);







                            $info = $obj_admin->manage_all_info($sql);
                            $serial = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="7">No projects were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {




                                ?>


                                <tr>
                                    <?php

                                    // Determine background color and text color
                                    $backgroundColor = '';
                                    $color = '';
                                    if (isset($row['setTarget']) && $row['setTarget'] == 'Active') {
                                        if (strtotime($row['setTargetDate']) <= strtotime(date('Y-m-d')) || strtotime($row['setTargetDate']) <= strtotime('+5 days')) {
                                            $backgroundColor = "red";
                                        } else {
                                            $backgroundColor = "orange";
                                        }
                                    } else {
                                        $backgroundColor = $row['urgency'];
                                    }
                                    $color = ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff';

                                    $project_name = $row['project_name'];
                                    $customer_name = $row['customer_name'];
                                    $p_team = $row['p_team'];
                                    ?>



                                    <td class="align-middle">
                                        <form class="text-center" id="readyToBeInvoicedForm" method="post">
                                            <!-- Add hidden project_id field -->
                                            <input type="hidden" name="project_id"
                                                value="<?php echo $row['project_id']; ?>" />
                                            <input type="hidden" name="rownumber"
                                                value="<?php echo $row['rownumber']; ?>" />


                                            <input type="checkbox" class="select-project" name="selected_projects[]"
                                                value="<?= $row['project_id']; ?>,<?= $row['rownumber']; ?>"
                                                style="height:1.3rem; width:1.3rem;" />
                                        </form>

                                    </td>

                                    <td class="align-middle">
                                        <div style="
        display:flex; align-items:center; justify-content:center; height:1.7rem; width:3.8rem; 
        background-color: <?= ($row['urgency'] === 'cancelled') ? 'grey' : $row['urgency']; ?>; 
        color: <?= ($row['urgency'] === 'cancelled' || $row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? 'black' : 'white'; ?>; 
        text-align:center; border-radius:5px; <?= ($row['urgency'] === 'cancelled') ? 'text-decoration: line-through;' : ''; ?>
    ">
                                            <?= !empty($row['revision_project_id']) ? $row['revision_project_id'] : $row['project_id']; ?>
                                        </div>

                                        <?php if (!empty($row['rownumber']) && $row['rownumber'] !== '0'): ?>
                                            <span class="badge badge-pill badge-danger">V<?= $row['rownumber']; ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($row['reopen_status'])): ?>
                                            <span class="badge badge-pill badge-danger"><?= $row['reopen_status']; ?></span>
                                        <?php endif; ?>

                                        <?php if ($row['quick_project']): ?>
                                            <span class="badge badge-pill badge-danger">Quick Project</span>
                                        <?php endif; ?>
                                    </td>


                                    <!-- Form for other inputs -->
                                    <form action="add_invoice.php" method="POST">
                                        <!-- Add hidden project_id field -->
                                        <input type="hidden" name="project_id" value="<?php echo $row['project_id']; ?>" />

                                        <td class="align-middle">
                                            <input type="text" name="project_name" value="<?php echo $project_name; ?>"
                                                style="border: none; background-color:transparent; outline: none; display:none">
                                            <label><?php echo $project_name; ?></label>
                                        </td>

                                        <td class="align-middle">
                                            <input type="text" name="customer_name" value="<?php echo $customer_name; ?>"
                                                style="border: none; background-color:transparent; outline: none; display:none">
                                            <label><?php echo $customer_name; ?></label>
                                        </td>

                                        <td class="align-middle">
                                            <input type="text" name="p_team" value="<?php echo $p_team; ?>"
                                                style="border: none; background-color: transparent; outline: none; display:none">
                                            <label><?php echo $p_team; ?></label>
                                        </td>

                                        <td class="align-middle">
                                            <textarea style="outline:none; border: none; width: 250px;" class="Comments"
                                                data-saved-comment="<?php echo $row['comments']; ?>"
                                                name="comments"><?php echo isset($row['comments']) ? $row['comments'] : ''; ?></textarea>

                                        </td>

                                        <td class="align-middle">
                                            <input type="text" data-saved-price="<?php echo $row['price']; ?>" name="price"
                                                class="price" placeholder="" style="outline:none; width:100px; border:none;"
                                                value="<?php echo $row['price']; ?>">
                                        </td>
                                        <td class="align-middle">
                                            <input type="text"
                                                data-saved-invoice_number="<?php echo $row['invoice_number']; ?>"
                                                name="invoice_number" class="invoiceNumber" placeholder=""
                                                style="outline:none; width:100px; border:none;"
                                                value="<?php echo $row['invoice_number']; ?>">
                                        </td>
                                        <td class="align-middle">
                                            <input type="date" data-saved-service-date="<?php echo $row['service_date']; ?>"
                                                name="service_date" class="service_date" placeholder=""
                                                style="outline:none; width:100px; border:none;"
                                                value="<?php echo $row['service_date']; ?>">
                                        </td>
                                        <td class="align-middle">
                                            <input type="date" data-saved-due_date="<?php echo $row['due_date']; ?>"
                                                name="due_date" class="due_date" placeholder=""
                                                style="outline:none; width:100px; border:none;"
                                                value="<?php echo $row['due_date']; ?>">
                                        </td>

                                        <td class="align-middle">
                                            <?php echo $row['last_modified_date']; ?>
                                        </td>

                                        <td class="d-flex justify-content-around align-items-center">
                                            <?php if (empty($row['rownumber'])): ?>

                                                <!-- Save Button -->
                                                <form action="add_invoice.php" method="POST" id="invoiceForm">
                                                    <!-- Fields and buttons here -->
                                                    <input type="hidden" name="project_id"
                                                        value="<?php echo $row['project_id']; ?>" />
                                                    <input type="hidden" name="rownumber"
                                                        value="<?php echo $row['rownumber']; ?>" />
                                                    <button type="submit" name="save"
                                                        onclick="return confirm('Are you sure you want to save this information?');"
                                                        class="ml-2 btn text-info btn-sm btn-light">Save</button>
                                                </form>

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
                                                    tabindex="-1"
                                                    aria-labelledby="moreModalMain<?php echo $row['project_id']; ?>"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLabel">Action Center
                                                                </h5>
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

                                                                    <!-- Send Back Button -->
                                                                    <a class="btn btn-warning mx-1" title="SEND BACK"
                                                                        href="?delete_project_id=<?php echo $row['project_id']; ?>&rownumber=<?php echo $row['rownumber']; ?>&comments=<?php echo $row['comments']; ?>"
                                                                        onclick="return confirm('Are  sure? This action will send the project back into the Uninvoiced Tab.');">
                                                                        Send Back
                                                                    </a>

                                                                    <!-- Create Variation Button -->
                                                                    <form action="add_invoice.php" method="post"
                                                                        class="d-inline-block">
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
                                                </script>

                                            <?php else: ?>
                                                <!-- More and Save Buttons -->
                                                <form action="add_invoice.php" method="POST" id="invoiceForm">
                                                    <!-- Fields and buttons here -->
                                                    <input type="hidden" name="project_id"
                                                        value="<?php echo $row['project_id']; ?>" />
                                                    <input type="hidden" name="rownumber"
                                                        value="<?php echo $row['rownumber']; ?>" />
                                                    <button type="submit" name="save"
                                                        onclick="return confirm('Are you sure you want to save this information?');"
                                                        class="ml-2 btn text-info btn-sm btn-light align-middle">Save</button>
                                                </form>
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
                                                </script>



                                                <!-- Modal for More Options (Delete and View) -->
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
                                                                    id="moreModalLabel<?php echo $row['id']; ?>">Action Center
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

                                                                <!-- Send Back Button -->
                                                                <a class="btn btn-warning mx-1" title="SEND BACK"
                                                                    href="?delete_project_id=<?php echo $row['project_id']; ?>&rownumber=<?php echo $row['rownumber']; ?>"
                                                                    onclick="return confirm('Are you sure you want to send back? This will send back all data related to this project.');">
                                                                    Send Back
                                                                </a>

                                                                <!-- Hidden Input Fields for project_id and rownumber -->
                                                                <input type="hidden" name="project_id" value="" />
                                                                <input type="hidden" name="rownumber" value="" />

                                                                <!-- Delete Row Button -->
                                                                <form action="add_invoice.php" method="POST"
                                                                    class="d-inline-block">
                                                                    <!-- Hidden field to pass the row id -->
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $row['id']; ?>" />
                                                                    <button type="submit" name="delete_row"
                                                                        onclick="return confirm('Are you sure you want to delete this variation project?')"
                                                                        class="btn btn-danger mx-1">
                                                                        Delete Variation
                                                                    </button>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            <?php endif; ?>

                                        </td>
                                    </form>
                                </tr>



                                <?php


                            }

                            ?>


                        </tbody>


                    </table>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0 text-dark">
                            Showing <?php echo $startEntry; ?> to <?php echo $endEntry; ?> of
                            <?php echo $totalProjects; ?> entries
                        </p>

                        <ul class="pagination mb-0">
                            <?php
                            // Get current filter parameters including sorting
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
                            // Add sorting parameters
                            if (isset($_GET['sort']) && !empty($_GET['sort'])) {
                                $filterParams['sort'] = $_GET['sort'];
                            }
                            if (isset($_GET['dir']) && !empty($_GET['dir'])) {
                                $filterParams['dir'] = $_GET['dir'];
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

                    <!-- Modal for displaying selected projects details -->
                    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog"
                        aria-labelledby="confirmationModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Ready to Invoice</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Project ID</th>
                                                <th>Price</th>
                                                <th>Comments</th>
                                                <th>Invoice Number</th>
                                                <th>Due Date</th>
                                                <th>Service Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="projectDetailsList">
                                            <!-- Project details will be dynamically inserted here -->
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="mb-0 text-dark">
                                            Showing <?php echo $startEntry; ?> to <?php echo $endEntry; ?> of
                                            <?php echo $totalProjects; ?> entries
                                        </p>

                                        <ul class="pagination mb-0">
                                            <?php
                                            $searchParam = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
                                            ?>
                                            <!-- Previous -->
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?= $page - 1 ?><?= $searchParam ?>&perPage=<?= $perPage ?>">Previous</a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- First Page -->
                                            <li class="page-item <?= $page == 1 ? 'active' : '' ?>">
                                                <a class="page-link"
                                                    href="?page=1<?= $searchParam ?>&perPage=<?= $perPage ?>">1</a>
                                            </li>

                                            <!-- Left Dots -->
                                            <?php if ($page > 4): ?>
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <?php endif; ?>

                                            <!-- Pages Around Current -->
                                            <?php for ($i = max(2, $page - 2); $i <= min($totalPages - 1, $page + 2); $i++): ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link"
                                                        href="?page=<?= $i ?><?= $searchParam ?>&perPage=<?= $perPage ?>"><?= $i ?></a>
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
                                                        href="?page=<?= $totalPages ?><?= $searchParam ?>&perPage=<?= $perPage ?>"><?= $totalPages ?></a>
                                                </li>
                                            <?php endif; ?>

                                            <!-- Next -->
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?= $page + 1 ?><?= $searchParam ?>&perPage=<?= $perPage ?>">Next</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?= $totalPages ?><?= $searchParam ?>&perPage=<?= $perPage ?>">Last</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Button to trigger the modal -->
                    <button type="button" class="btn btn-success" name="invoice"
                        onclick="submitAllForms()">Invoiced</button>

                    <!-- Hidden form to submit all selected projects -->
                    <form id="invoice-form" method="POST" action="add_invoice.php" style="display:none;">
                        <!-- Hidden inputs will be added here dynamically -->
                    </form>

                    <script>
                        function submitAllForms() {
                            const selectedProjects = document.querySelectorAll('.select-project:checked');

                            if (selectedProjects.length === 0) {
                                alert('Please select at least one project.');
                                return;
                            }

                            const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                            const projectDetailsList = document.getElementById('projectDetailsList');
                            projectDetailsList.innerHTML = ''; // Clear previous project details

                            const projectData = []; // Array to store project data for form submission
                            let isValid = true; // Flag to track if all fields are valid

                            selectedProjects.forEach(project => {
                                const [projectId, rownumber] = project.value.split(',');
                                const row = project.closest('tr');
                                const price = row.querySelector('.price')?.value || 'N/A';
                                const comments = row.querySelector('textarea[name="comments"]')?.value || 'N/A';
                                const invoiceNumber = row.querySelector('.invoiceNumber')?.value || 'N/A';
                                const dueDate = row.querySelector('.due_date')?.value || 'N/A';
                                const serviceDate = row.querySelector('.service_date')?.value || 'N/A';

                                // Validation checks before adding the data to the modal and projectData array
                                if (price === 'N/A' || invoiceNumber === 'N/A' || dueDate === 'N/A' || serviceDate === 'N/A') {
                                    isValid = false;
                                    alert(`Please fill in all fields for project ${projectId}.`);
                                    return; // Prevent further processing for this project
                                }

                                // Add row to the modal table
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
            <td>${projectId}</td>
            <td>${price}</td>
            <td>${comments}</td>
            <td>${invoiceNumber}</td>
            <td>${dueDate}</td>
            <td>${serviceDate}</td>
        `;
                                projectDetailsList.appendChild(tr);

                                // Add data to the projectData array (for submission)
                                projectData.push({
                                    projectId,
                                    rownumber,
                                    price,
                                    comments,
                                    invoiceNumber,
                                    dueDate,
                                    serviceDate
                                });
                            });

                            if (!isValid) {
                                return; // Stop further processing if validation fails
                            }

                            modal.show(); // Show the modal

                            // Handle confirm button click
                            const confirmButton = document.getElementById('confirmButton');
                            confirmButton.disabled = false; // Enable the confirm button initially

                            confirmButton.onclick = function () {
                                confirmButton.disabled = true; // Disable the confirm button to prevent multiple submissions
                                const form = document.getElementById('invoice-form');
                                form.innerHTML = ''; // Clear previous inputs

                                // Add project data as hidden inputs to the form
                                projectData.forEach(data => {
                                    const inputProjectId = document.createElement('input');
                                    inputProjectId.type = 'hidden';
                                    inputProjectId.name = 'movetotargets[]';
                                    inputProjectId.value = `${data.projectId}_${data.rownumber}`;
                                    form.appendChild(inputProjectId);

                                    // Additional hidden inputs for project details
                                    const inputPrice = document.createElement('input');
                                    inputPrice.type = 'hidden';
                                    inputPrice.name = `price_${data.projectId}_${data.rownumber}`;
                                    inputPrice.value = data.price;
                                    form.appendChild(inputPrice);

                                    const inputComments = document.createElement('input');
                                    inputComments.type = 'hidden';
                                    inputComments.name = `comments_${data.projectId}_${data.rownumber}`;
                                    inputComments.value = data.comments;
                                    form.appendChild(inputComments);

                                    const inputInvoiceNumber = document.createElement('input');
                                    inputInvoiceNumber.type = 'hidden';
                                    inputInvoiceNumber.name = `invoiceNumber_${data.projectId}_${data.rownumber}`;
                                    inputInvoiceNumber.value = data.invoiceNumber;
                                    form.appendChild(inputInvoiceNumber);

                                    const inputDueDate = document.createElement('input');
                                    inputDueDate.type = 'hidden';
                                    inputDueDate.name = `dueDate_${data.projectId}_${data.rownumber}`;
                                    inputDueDate.value = data.dueDate;
                                    form.appendChild(inputDueDate);

                                    const inputServiceDate = document.createElement('input');
                                    inputServiceDate.type = 'hidden';
                                    inputServiceDate.name = `serviceDate_${data.projectId}_${data.rownumber}`;
                                    inputServiceDate.value = data.serviceDate;
                                    form.appendChild(inputServiceDate);
                                });

                                // Add hidden input for invoice action
                                const invoiceInput = document.createElement('input');
                                invoiceInput.type = 'hidden';
                                invoiceInput.name = 'invoice';
                                invoiceInput.value = '1';
                                form.appendChild(invoiceInput);

                                // Submit the form using the traditional POST method
                                form.submit();
                            };
                        }
                    </script>



                    <!-- set target modal  -->

                    <div class="modal fade " id="setTargetmodal" tabindex="-1" role="dialog"
                        aria-labelledby="mySmallModalLabel" aria-hidden="true">

                        <div class="modal-dialog modal-sm modal-dialog-centered">

                            <div class="modal-content">

                                <div class="form-container mt-2 m-2">

                                    <form class="" action="" method="post" enctype="multipart/form-data">
                                        <label class="control-label text-dark" for="">Change Target Date</label>
                                        <input class="form-control" type="date" name="targetdate" id="targetdate">
                                        <button class="btn btn-primary mt-2" name="setTarget">Submit</button>
                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog"
                        aria-labelledby="filterModalLabel" aria-hidden="true">
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
                                            <input type="hidden" name="search"
                                                value="<?php echo htmlspecialchars($_GET['search']); ?>">
                                        <?php endif; ?>
                                        <?php if (isset($_GET['team'])): ?>
                                            <input type="hidden" name="team"
                                                value="<?php echo htmlspecialchars($_GET['team']); ?>">
                                        <?php endif; ?>
                                        <?php if (isset($_GET['perPage'])): ?>
                                            <input type="hidden" name="perPage"
                                                value="<?php echo htmlspecialchars($_GET['perPage']); ?>">
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

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="filter-modal" tabindex="-1" aria-labelledby="filter-modal-label"
                        aria-hidden="true">
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
                                            <input type="hidden" name="search"
                                                value="<?php echo htmlspecialchars($_GET['search']); ?>">
                                        <?php endif; ?>

                                        <?php if (isset($_GET['customer'])): ?>
                                            <input type="hidden" name="customer"
                                                value="<?php echo htmlspecialchars($_GET['customer']); ?>">
                                        <?php endif; ?>

                                        <?php if (isset($_GET['perPage'])): ?>
                                            <input type="hidden" name="perPage"
                                                value="<?php echo htmlspecialchars($_GET['perPage']); ?>">
                                        <?php endif; ?>

                                        <label for="team-filter" class="text-primary">Filter by Team:</label>
                                        <select id="team-filter" name="team" class="form-control mb-3"
                                            onchange="this.form.submit()">
                                            <option value="">All</option>
                                            <option value="Building Team" <?php echo (isset($_GET['team']) && $_GET['team'] === 'Building Team') ? 'selected' : ''; ?>>Building Team
                                            </option>
                                            <option value="Industrial Team" <?php echo (isset($_GET['team']) && $_GET['team'] === 'Industrial Team') ? 'selected' : ''; ?>>Industrial Team
                                            </option>
                                        </select>

                                    </form>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="apply-filter">Apply
                                        Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>








                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            // Initialize DataTable with disabled paging and searching
                            $('#dataTable').DataTable({
                                "paging": false,
                                "searching": false, // Disable DataTable's built-in search
                                "ordering": true,
                                "info": false,
                                "autoWidth": false,
                                "sorting": false
                            });

                            // Handle form submission
                            $('#searchForm').on('submit', function (e) {
                                e.preventDefault();
                                var searchTerm = $('#searchInput').val();
                                window.location.href = '?page=1&perPage=<?php echo $perPage; ?>&search=' + encodeURIComponent(searchTerm);
                            });

                            // Get the invoice_no from the URL
                            const urlParams = new URLSearchParams(window.location.search);
                            const projectId = urlParams.get('project_id');

                            if (projectId) {
                                // If invoice_no is found in the URL, set it in the search input
                                document.getElementById('searchInput').value = projectId;
                            }
                        });

                        <?php if ($success == 1): ?>
                            // Show Toast with the success message
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end', // Position the toast at the top right
                                showConfirmButton: false, // Hide the confirm button
                                timer: 3000, // Auto-dismiss the toast after 3 seconds
                                timerProgressBar: true, // Show a progress bar for the timer
                                onClose: () => {
                                    // Redirect to the current page without query parameters
                                    window.location.href = window.location.pathname;
                                }
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Project successfully added!'
                            }).then((result) => {
                                // If the toast is clicked, redirect immediately
                                if (result.dismiss === Swal.DismissReason.timer || result.isConfirmed) {
                                    window.location.href = window.location.pathname; // Redirect to the current page
                                }
                            });
                        <?php endif; ?>
                    </script>
                    <script>
                        $(document).ready(function () {
                            $('#customer_name').select2();
                        });
                    </script>


                    <script>
                        // Function to filter table based on selected customer name
                        function filterTableByCustomer() {
                            var selectedCustomer = document.getElementById('customer_name').value;
                            var table = document.getElementById('dataTable');
                            var rows = table.getElementsByTagName('tr');

                            for (var i = 0; i < rows.length; i++) {
                                var customerCell = rows[i].getElementsByTagName('td')[3]; // Assuming customer name is in the third column (index 2)
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


                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["invoice"])) {
                        $comments = $_POST['comments'];
                        $price = $_POST['price'];
                        $project_name = $_POST['project_name'];
                        $project_id = $_POST['project_id'];
                        $p_team = $_POST['p_team'];
                        $customer_name = $_POST['customer_name'];
                        $currentDate = date('d/F/y'); // Format date for 'month' column
                    
                        // Prepare the SQL statement with seven placeholders
                        $stmt = $conn->prepare("INSERT INTO csa_finance_invoiced (project_id, project_title, comments, amount, p_team, customer_name, month) VALUES (?, ?, ?, ?, ?, ?, ?)");

                        // Bind the parameters with seven variables
                        $stmt->bind_param("sssssss", $project_id, $project_name, $comments, $price, $p_team, $customer_name, $currentDate);

                        // Execute the statement
                        if ($stmt->execute()) {
                            echo "Invoice data successfully added!";
                        } else {
                            echo "Error: " . $stmt->error;
                        }

                        // Close the statement
                        $stmt->close();

                        // Redirect to dashboard
                        header('Location: dashboard.php');
                        exit(); // Always exit after a redirect
                    }


                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["setTarget"])) {


                        $project_id = $_GET['project_id'];
                        $setTarget = "Active";
                        $setTargetDate = $_POST['targetdate'];


                        // Prepare the statement
                        $stmt = $conn->prepare("UPDATE projects SET setTarget = ?, setTargetDate = ? WHERE project_id = ?");

                        // Check if statement preparation was successful
                        if (!$stmt) {
                            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                            exit();
                        }

                        // Bind parameters
                        $stmt->bind_param("ssi", $setTarget, $setTargetDate, $project_id);

                        // Execute the statement
                        $stmt->execute();

                        // Close the statement
                        $stmt->close();


                        header('Location:targetProjects.php');
                    }

                    if (isset($_GET['delete_project_id']) && isset($_GET['rownumber'])) {
                        $delete_project_id = $_GET['delete_project_id'];
                        $rownumber = $_GET['rownumber'];
                        $comments = $_GET['comments'];

                        echo '<pre>';
                        echo print_r($_GET);
                        echo '</pre>';
                        // exit;
                    
                        // Start a transaction to ensure the delete operation is atomic
                        $conn->begin_transaction();

                        try {
                            // Step 1: SQL query to delete the project from csa_finance_readytobeinvoiced based on both project_id and rownumber
                            $deleteSql = "DELETE FROM csa_finance_readytobeinvoiced WHERE project_id = ? AND rownumber = ?";
                            $deleteStmt = $conn->prepare($deleteSql);
                            $deleteStmt->bind_param("ii", $delete_project_id, $rownumber);

                            // Execute the deletion
                            if ($deleteStmt->execute()) {
                                // Step 2: Query to get the project_status and comments from csa_finance_uninvoiced
                                $selectSql = "SELECT project_status, comments FROM csa_finance_uninvoiced WHERE project_id = ? AND rownumber = ?";
                                $selectStmt = $conn->prepare($selectSql);
                                $selectStmt->bind_param("ii", $delete_project_id, $rownumber);

                                $selectStmt->execute();
                                $selectStmt->store_result();

                                if ($selectStmt->num_rows > 0) {
                                    // If the project exists, update the project_status and comments to NULL
                                    $updateSql = "UPDATE csa_finance_uninvoiced SET project_status = NULL, comments = ? WHERE project_id = ? AND rownumber = ?";
                                    $updateStmt = $conn->prepare($updateSql);
                                    $updateStmt->bind_param("sii", $comments, $delete_project_id, $rownumber);


                                    // Execute the update
                                    if ($updateStmt->execute()) {
                                        // Commit the transaction if both delete and update are successful
                                        $conn->commit();
                                        $msg_success = "Project deleted and status updated successfully.";
                                        header('Location:' . $_SERVER['HTTP_REFERER']);
                                        exit();
                                    } else {
                                        // Rollback if the update fails
                                        $conn->rollback();
                                        echo "Error updating the project status: " . $updateStmt->error;
                                    }

                                    // Close the update statement
                                    $updateStmt->close();
                                } else {
                                    // If no matching record is found in csa_finance_uninvoiced, insert a new row
                                    $insertSql = "INSERT INTO csa_finance_uninvoiced (project_id, rownumber, project_status, comments) 
                                              VALUES (?, ?, NULL, NULL)";
                                    $insertStmt = $conn->prepare($insertSql);
                                    $insertStmt->bind_param("ii", $delete_project_id, $rownumber);

                                    // Execute the insert
                                    if ($insertStmt->execute()) {
                                        // Commit the transaction if insertion is successful
                                        $conn->commit();
                                        $msg_success = "Project deleted and inserted into csa_finance_uninvoiced.";
                                        header('Location:' . $_SERVER['HTTP_REFERER']);
                                        exit();
                                    } else {
                                        // Rollback if the insert fails
                                        $conn->rollback();
                                        echo "Error inserting the project into csa_finance_uninvoiced: " . $insertStmt->error;
                                    }

                                    // Close the insert statement
                                    $insertStmt->close();
                                }

                                // Close the select statement
                                $selectStmt->close();
                            } else {
                                // Rollback if the deletion fails
                                $conn->rollback();
                                echo "Error deleting the project: " . $deleteStmt->error;
                            }

                            // Close the deletion statement
                            $deleteStmt->close();
                        } catch (Exception $e) {
                            // Rollback the transaction if an exception occurs
                            $conn->rollback();
                            echo "Transaction failed: " . $e->getMessage();
                        }
                    }







                    ?>
                    <script>
                        // Function to filter table based on selected team
                        function filterTableByTeam() {
                            var selectedTeam = document.getElementById('team-filter').value;
                            var table = document.getElementById('dataTable');
                            var tbody = table.getElementsByTagName('tbody')[0];
                            var rows = tbody.getElementsByTagName('tr');

                            for (var i = 0; i < rows.length; i++) {
                                var teamCell = rows[i].cells[4]; // 5th column (0-based index 4) where team is displayed
                                if (teamCell) {
                                    // Try to get the team name from the hidden input first
                                    var teamInput = teamCell.querySelector('input[name="p_team"]');
                                    var teamName = teamInput ? teamInput.value : teamCell.textContent.trim();

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
                    </script>

                    <?php
                    include './include/footer.php';
                    ?>
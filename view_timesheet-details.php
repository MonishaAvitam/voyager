<?php
// Helper function to get CSS class name
function getUrgencyClass($urgency) {
    return 'urgency-' . strtolower(str_replace(' ', '-', $urgency));
}

// Updated function to get text color based on urgency
function getTextColor($urgency) {
    $colorMapping = getUrgencyColorMapping($urgency);
    return $colorMapping['text'];
}

// Updated function to get background color
function getBackgroundColor($urgency) {
    $colorMapping = getUrgencyColorMapping($urgency);
    return $colorMapping['bg'];
}

// Updated function to get border if needed
function getBorderStyle($urgency) {
    $colorMapping = getUrgencyColorMapping($urgency);
    return isset($colorMapping['border']) ? $colorMapping['border'] : '';
}

// Updated function to check if strikethrough is needed
function hasStrikethrough($urgency) {
    $colorMapping = getUrgencyColorMapping($urgency);
    return isset($colorMapping['strikethrough']) && $colorMapping['strikethrough'];
}

// Enhanced function to generate table rows with proper color handling
function generateTimesheetRow($row) {
    $isSubproject = !empty($row['subproject_status']);
    
    // Determine project ID to show
    $displayProjectId = !empty($row['revision_project_id']) 
                        ? $row['revision_project_id'] 
                        : $row['project_id'];

    // Text color based on urgency
    $bgColor = $row['urgency'];
    $textColor = ($bgColor === 'white' || $bgColor === 'yellow') ? '#000' : '#fff';

    $rowClass = $row['project_type'] === 'sub' ? 'subproject-row' : '';

    $html = "<tr class='{$rowClass}' style='height:5rem;'>";
    
    // Project ID + Badges
    $html .= "<td class='text-center'>
                <div class='d-flex text-center align-items-center justify-content-center pt-3'>
                    <div>
                        <div class='py-2' style='width:4.5rem;background-color:{$bgColor};
                             text-align:center;border-radius:5px;color:{$textColor};'>
                            {$displayProjectId}
                        </div>
                        <div class='mt-1'>";

    // Reopen badge
    if (!empty($row['reopen_status'])) {
        $html .= "<span class='badge badge-pill badge-danger'>{$row['reopen_status']}</span> ";
    }

    // Subproject badge
    if ($isSubproject) {
        $html .= "<span class='badge badge-pill badge-danger'>S{$row['subproject_status']}</span>";
    }

    $html .= "        </div>
                    </div>
                </div>
              </td>";

    // Project Name
    $projectName = $isSubproject && !empty($row['subproject_name'])
        ? $row['subproject_name']
        : ($row['project_name'] ?? '');
    
    $html .= "<td style='width:30%;' class='align-middle'>{$projectName}</td>";

    // Working hours
    $html .= "<td class='align-middle text-center'>{$row['working_hours']}</td>";

    // Date
    $html .= "<td class='align-middle text-center'>{$row['date_value']}</td>";

    $html .= "</tr>";

    return $html;
}

// Test function to verify colors are working
function testColors() {
    $testColors = ['red', 'orange', 'yellow', 'white', 'green', 'blue', 'purple', 'pink', 'black', 'grey'];
    
    echo "<div class='container mt-4 mb-4'>";
    echo "<h5>Color Test Preview</h5>";
    echo "<div class='row'>";
    
    foreach ($testColors as $color) {
        $colorMapping = getUrgencyColorMapping($color);
        $style = "background-color: {$colorMapping['bg']}; color: {$colorMapping['text']};";
        if (isset($colorMapping['border'])) {
            $style .= " border: {$colorMapping['border']};";
        }
        
        echo "<div class='col-md-2 col-sm-3 col-4 mb-2'>";
        echo "<div class='p-2 text-center urgency-badge' style='{$style} font-size: 0.8rem;'>";
        echo ucfirst($color);
        echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "<hr>";
    echo "</div>";
}
?>
<?php
// Updated timesheet display code with entries per page functionality
require 'authentication.php'; 
require 'conn.php';
include 'include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

$user_role = $_SESSION['user_role'];
include 'include/sidebar.php';

if (!isset($_GET['id'])) {
    die("Employee ID missing.");
}

$user_id = intval($_GET['id']);

// Get employee name
$sqlUser = "SELECT fullname FROM tbl_admin WHERE user_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();
$employeeName = $userData['fullname'] ?? "Unknown";

// Check if search parameter is present
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
?>
<div class="container">
<?php
// Determine which month/year to show
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['selectedDate'])) {
    // Format: YYYY-MM
    list($selected_year, $selected_month) = explode('-', $_POST['selectedDate']);
} else if (isset($_GET['selectedDate']) && !empty($_GET['selectedDate'])) {
    // Also check for GET parameter (for search with date filter)
    list($selected_year, $selected_month) = explode('-', $_GET['selectedDate']);
} else {
    $selected_year  = date('Y');
    $selected_month = date('m');
}

// Convert numeric month to full name (e.g. 09 -> September)
$display_month = date('F', mktime(0, 0, 0, (int)$selected_month, 1));

// Enhanced Pagination setup with entries per page
$entries_per_page_options = [5, 10, 25, 50, 100]; // Available options
$rows_per_page = isset($_GET['entries']) && in_array((int)$_GET['entries'], $entries_per_page_options) 
                 ? (int)$_GET['entries'] 
                 : 10; // Default to 10

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $rows_per_page;

// FIXED: Corrected SQL query structure for proper subproject handling
$baseSql = "
    SELECT 
        p.project_id,
        p.project_name,
        p.urgency,
        t.working_hours,
        t.date_value,
        NULL as subproject_status,
        'main' as project_type,
        p.revision_project_id,
        NULL as subproject_name,
        p.reopen_status
    FROM projects p
    JOIN timesheet t ON p.project_id = t.project_id_timesheet
    WHERE t.user_id = ?

    UNION ALL

    SELECT
        sp.project_id,
        p.project_name,
        sp.urgency,
        t.working_hours,
        t.date_value,
        sp.subproject_status,
        'sub' as project_type,
        p.revision_project_id,
        sp.subproject_name,
        sp.reopen_status
    FROM subprojects sp
    JOIN projects p ON sp.project_id = p.project_id
    JOIN timesheet t ON sp.table_id = t.project_id_timesheet
    WHERE t.user_id = ?
";

// Build parameters array step by step
$baseParams = [$user_id, $user_id]; // Base parameters for both parts of UNION
$paramTypes = "ii";

// Get selected date for filtering
$selectedDate = '';
if (!empty($_POST['selectedDate']) || !empty($_GET['selectedDate'])) {
    $selectedDate = !empty($_POST['selectedDate']) ? $_POST['selectedDate'] : $_GET['selectedDate'];
    
    // Add date condition to both parts of the UNION
    $baseSql = str_replace(
        ["WHERE t.user_id = ?"], 
        ["WHERE t.user_id = ? AND DATE_FORMAT(t.date_value, '%Y-%m') = ?"], 
        $baseSql
    );
    
    // Add date parameters for both parts of UNION
    $baseParams = [$user_id, $selectedDate, $user_id, $selectedDate];
    $paramTypes = "isis"; // int, string, int, string
}

// Add search condition if search term is provided
if (!empty($searchTerm)) {
    $searchCondition = " AND (p.project_id LIKE ? OR 
                             p.project_name LIKE ? OR 
                             p.revision_project_id LIKE ? OR
                             COALESCE(sp.subproject_name, '') LIKE ? OR
                             t.working_hours LIKE ? OR
                             t.date_value LIKE ? OR
                             COALESCE(p.reopen_status, '') LIKE ? OR
                             COALESCE(sp.reopen_status, '') LIKE ?)";
    
    $searchPattern = "%$searchTerm%";
    $searchParams = array_fill(0, 8, $searchPattern);
    
    // Add search condition to both parts of the UNION
    if (!empty($selectedDate)) {
        // If we have date filter, the WHERE clause already has date condition
        $baseSql = preg_replace(
            '/WHERE t\.user_id = \? AND DATE_FORMAT\(t\.date_value, \'%Y-%m\'\) = \?/',
            'WHERE t.user_id = ? AND DATE_FORMAT(t.date_value, \'%Y-%m\') = ?' . $searchCondition,
            $baseSql
        );
    } else {
        // If no date filter, just add to the basic WHERE clause
        $baseSql = preg_replace(
            '/WHERE t\.user_id = \?/',
            'WHERE t.user_id = ?' . $searchCondition,
            $baseSql
        );
    }
    
    // Add search parameters for both parts of UNION (8 parameters each)
    $searchParamsForBothUnions = array_merge($searchParams, $searchParams);
    $baseParams = array_merge($baseParams, $searchParamsForBothUnions);
    
    // Update parameter types
    $searchParamTypes = str_repeat("s", 16); // 8 parameters × 2 unions = 16 string parameters
    $paramTypes .= $searchParamTypes;
}

// Count total rows for pagination
$countSql = "SELECT COUNT(*) as total FROM ($baseSql) as combined";

$stmtCount = $conn->prepare($countSql);
if (!$stmtCount) {
    die("SQL Error: " . $conn->error);
}

// Bind parameters for count query
if (!empty($baseParams)) {
    // Create reference array for bind_param
    $bindParams = [$paramTypes];
    foreach ($baseParams as &$value) {
        $bindParams[] = &$value;
    }
    call_user_func_array([$stmtCount, 'bind_param'], $bindParams);
}

$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$total_rows = $resultCount->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);

// Ensure page is within valid range
if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Get the selected date for form preservation
$formSelectedDate = !empty($_POST['selectedDate']) ? $_POST['selectedDate'] : 
                   (!empty($_GET['selectedDate']) ? $_GET['selectedDate'] : '');

// Updated function to build URL with current parameters
function buildUrl($newParams = []) {
    global $user_id, $formSelectedDate, $searchTerm, $rows_per_page;
    
    $params = ['id' => $user_id];
    
    // Only add selectedDate if it's not being cleared
    if (!empty($formSelectedDate) && !isset($newParams['clearDate'])) {
        $params['selectedDate'] = $formSelectedDate;
    }
    
    // Only add search if it's not being cleared
    if (!empty($searchTerm) && !isset($newParams['clearSearch'])) {
        $params['search'] = $searchTerm;
    }
    
    if ($rows_per_page != 10) { // Only add if not default
        $params['entries'] = $rows_per_page;
    }
    
    // Override with new parameters (except clear flags)
    foreach ($newParams as $key => $value) {
        if ($key !== 'clearDate' && $key !== 'clearSearch') {
            if ($value === null) {
                unset($params[$key]);
            } else {
                $params[$key] = $value;
            }
        }
    }
    
    return '?' . http_build_query($params);
}
?>
<!-- Back Button -->
<div class="mb-3">
    <a href="time-sheet.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row mb-5 mt-5 d-flex justify-content-center align-items-center">
    <!-- HTML form -->
    <div class="col-9">
        <h3>
            Showing Data for 
            <span class="text-primary">
            <?php echo $display_month . ' ' . $selected_year; ?>
            </span>
        </h3>
    </div>
    <div class="col-3">
        <form method="POST" action="">
            <label for="selectedDate">Select Month and Year:</label>
            <div class="d-flex">
                <input type="month" class="form-control" name="selectedDate" id="selectedDate" 
                       value="<?php echo $formSelectedDate; ?>" required>
                <button class="btn bg-gradient btn-sm btn-success ml-2" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>
<?php 
// Display color test (remove this line in production)
// testColors(); 
?>
<div class="card mt-2 border-0 shadow-lg">
    <div class="container mt-4">
    
        <div class="mb-3">
            <h4 class="mb-3">
                Timesheet Details of <span class="text-primary"><?php echo $employeeName; ?></span>
            </h4>
            
            <!-- Search and Entries Per Page Row -->
            <div class="d-flex justify-content-between align-items-center">
                <!-- Entries Per Page Dropdown - Left Side -->
                <div class="d-flex align-items-center">
                    <label for="entriesPerPage" class="mr-2 text-nowrap">Show:</label>
                    <select id="entriesPerPage" class="form-control form-control-sm" style="width: auto;" onchange="changeEntriesPerPage()">
                        <?php foreach ($entries_per_page_options as $option): ?>
                            <option value="<?php echo $option; ?>" <?php echo $option == $rows_per_page ? 'selected' : ''; ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="ml-1 text-nowrap">entries per page</span>
                </div>
                
                <!-- Search Form - Right Side with updated Clear functionality -->
                <div class="d-flex align-items-center">
                    <form method="GET" action="" class="d-inline">
                        <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                        <?php if (!empty($formSelectedDate)): ?>
                            <input type="hidden" name="selectedDate" value="<?php echo $formSelectedDate; ?>">
                        <?php endif; ?>
                        <?php if ($rows_per_page != 10): ?>
                            <input type="hidden" name="entries" value="<?php echo $rows_per_page; ?>">
                        <?php endif; ?>
                        <label for="searchInput" class="mr-2">Search:</label>
                        <input type="text" id="searchInput" name="search" class="form-control form-control-sm d-inline-block" 
                               placeholder="Search project ID, name, date..." style="width: 200px;" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </form>
                    
                    <!-- Clear buttons with better functionality -->
                    <div class="ml-2">
                        <?php if (!empty($searchTerm)): ?>
                            <a href="<?php echo buildUrl(['search' => null]); ?>" 
                               class="btn btn-sm btn-outline-secondary" title="Clear search only">
                                Clear Search
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($formSelectedDate)): ?>
                            <a href="<?php echo buildUrl(['selectedDate' => null]); ?>" 
                               class="btn btn-sm btn-outline-danger <?php echo !empty($searchTerm) ? 'ml-1' : ''; ?>" title="Clear date filter only">
                                Clear Date
                            </a>
                        <?php endif; ?>
                        
                        <!-- <?php if (!empty($searchTerm) || !empty($formSelectedDate)): ?>
                            <a href="<?php echo '?id=' . $user_id . ($rows_per_page != 10 ? '&entries=' . $rows_per_page : ''); ?>" 
                               class="btn btn-sm btn-secondary ml-1" title="Reset to current month with no filters">
                                Reset All
                            </a>
                        <?php endif; ?> -->
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0">
            <div class="table-responsive p-3">
                <table class="table table-bordered table-sm" style="border:white">
                    <thead style="height:3rem;">
                        <tr class="bg-gradient text-light my-2" id="projectsInfo" name="dataTable" style="background: #5d855b">
                            <th scope="col" class="text-center align-middle" width="8%">Project ID</th>
                            <th scope="col" class="text-center align-middle" width="25%">Project Title</th>
                            <th scope="col" class="text-center align-middle" width="10%">Working Hours</th>
                            <th scope="col" class="text-center align-middle" width="10%">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sqlProjects = "$baseSql ORDER BY date_value DESC, project_id, project_type LIMIT ?, ?";
                        
                        // Prepare statement
                        $stmtProjects = $conn->prepare($sqlProjects);
                        if (!$stmtProjects) {
                            die("SQL Error: " . $conn->error);
                        }
                        
                        // Add LIMIT parameters to the existing baseParams
                        $finalParams = $baseParams;
                        $finalParams[] = $start;
                        $finalParams[] = $rows_per_page;
                        $finalParamTypes = $paramTypes . "ii"; // Add two integers for LIMIT
                        
                        // Bind parameters for data query
                        if (!empty($finalParams)) {
                            // Create reference array for bind_param
                            $bindParams = [$finalParamTypes];
                            foreach ($finalParams as &$value) {
                                $bindParams[] = &$value;
                            }
                            call_user_func_array([$stmtProjects, 'bind_param'], $bindParams);
                        }
                        
                        $stmtProjects->execute();
                        $resultProjects = $stmtProjects->get_result();
                        
                        // Display current page results
                        if ($resultProjects->num_rows > 0) {
                            while ($row = $resultProjects->fetch_assoc()) {
                                echo generateTimesheetRow($row);
                            }
                        } else {
                            $noRecordsMessage = "No records found";
                            if (!empty($searchTerm) && !empty($formSelectedDate)) {
                                $noRecordsMessage .= " for search term: '" . htmlspecialchars($searchTerm) . "' in " . $display_month . " " . $selected_year;
                            } else if (!empty($searchTerm)) {
                                $noRecordsMessage .= " for search term: '" . htmlspecialchars($searchTerm) . "'";
                            } else if (!empty($formSelectedDate)) {
                                $noRecordsMessage .= " for " . $display_month . " " . $selected_year;
                            }
                            echo "<tr><td colspan='4' class='text-center text-muted'>{$noRecordsMessage}</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                
                <!-- Enhanced Total Records and Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <!-- Total Records and Current Page Info - Left Side -->
                    <div class="text-muted">
                        <?php
                        $showing_start = $total_rows > 0 ? ($start + 1) : 0;
                        $showing_end = min($start + $rows_per_page, $total_rows);
                        ?>
                        Showing <?php echo $showing_start; ?>-<?php echo $showing_end; ?> of <?php echo $total_rows; ?> entries
                        <?php if (!empty($searchTerm) || !empty($formSelectedDate)): ?>
                            <span class="text-info">(filtered results)</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination Controls - Right Side -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <!-- First Page & Previous Page -->
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => 1]); ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => $page - 1]); ?>" aria-label="Previous">
                                        <span aria-hidden="true">‹</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Show page numbers
                            $visible_pages = 5; // Number of page links to show
                            $start_page = max(1, $page - floor($visible_pages / 2));
                            $end_page = min($total_pages, $start_page + $visible_pages - 1);
                            
                            // Adjust if we're at the end
                            if ($end_page - $start_page + 1 < $visible_pages) {
                                $start_page = max(1, $end_page - $visible_pages + 1);
                            }
                            
                            // Show first page if not in visible range
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => 1]); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => $i]); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Show last page if not in visible range -->
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => $total_pages]); ?>"><?php echo $total_pages; ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Next Page & Last Page -->
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => $page + 1]); ?>" aria-label="Next">
                                        <span aria-hidden="true">›</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildUrl(['page' => $total_pages]); ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to change entries per page
function changeEntriesPerPage() {
    const select = document.getElementById('entriesPerPage');
    const selectedValue = select.value;
    
    // Build URL with current parameters
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('entries', selectedValue);
    urlParams.set('page', '1'); // Reset to first page when changing entries per page
    
    // Redirect to new URL
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}

// Debugging: Check if all rows have the same number of columns
$(document).ready(function() {
    var rowCount = $('#projectsInfo tbody tr').length;
    console.log('Total rows: ' + rowCount);
    
    $('#projectsInfo tbody tr').each(function(index) {
        var colCount = $(this).find('td').length;
        console.log('Row ' + (index+1) + ' has ' + colCount + ' columns');
        
        if (colCount !== 4) {
            console.log('ERROR: Row ' + (index+1) + ' has incorrect column count!');
            console.log(this);
        }
    });
});

// Enhanced live search filter for timesheet table (client-side fallback)
document.getElementById("searchInput").addEventListener("keyup", function() {
    let input = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        // Skip any existing no-results messages
        if (row.classList.contains('no-results-message')) {
            return;
        }
        
        let text = row.innerText.toLowerCase();
        if (text.indexOf(input) > -1) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });
    
    // Show message if no results found
    if (visibleCount === 0 && input.length > 0) {
        // Check if there's already a no results message
        if (!document.querySelector('.no-results-message')) {
            let noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-message';
            noResultsRow.innerHTML = '<td colspan="4" class="text-center text-muted">No matching records found</td>';
            document.querySelector('table tbody').appendChild(noResultsRow);
        }
    } else {
        // Remove any existing no results message
        let noResultsMsg = document.querySelector('.no-results-message');
        if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
});
</script>
<?php 
include 'include/footer.php';
?>
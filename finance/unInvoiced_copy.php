<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
  $(document).ready(function () {
    $('#dataTable').DataTable({
      "paging": false,       // Disable pagination
      "searching": false,     // Keep search functionality if needed
      "info": false          // Disable "Showing X of Y entries" info
    });
  });
</script>




<!-- dashboard content  -->

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Accounts Receivable</h1>
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
        <br>
        <div class="form-group ml-4">
          <label for="customer_name">Customer Name:</label>
          <select class="form-control mt-2" id="customer_name" name="customer_name" style="width: 70%">
            <option value="">Select customer</option>
            <?php
            // Assuming $conn is your database connection object
            $sql = "SELECT * FROM contacts";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['customer_name'] . "'>" . $row['customer_name'] . "</option>";
              }
            } else {
              echo "<option value=''>No customers found</option>";
            }
            ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="applyFilter">Apply Filter</button>
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
          <!-- Add your filter options here -->
          <label for="team-filter" class="text-primary">Filter by Team:</label>
          <select id="team-filter" name="team-filter" class="form-control">
            <option value="">All</option>
            <option value="Building">Building Team</option>
            <option value="Industrial">Industrial Team</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="apply-filter">Apply Filter</button>
        </div>
      </div>
    </div>
  </div>


  <div class="card shadow mb-4" style="border:none">
    <div class="card-header py-3 d-flex w-100 justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Uninvoiced Tab</h6>

      <div class="d-flex align-items-center">
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

        <!-- Filter Button -->
        <button class="btn btn-sm btn-primary mr-2" data-toggle="modal" data-target="#filterModal">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel"
            viewBox="0 0 16 16">
            <path
              d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2h-11z" />
          </svg>
        </button>

        <!-- People Icon Button -->
        <a href="#" class="float-right" id="filter-icon" data-toggle="modal" data-target="#filter-modal">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-people-fill"
            viewBox="0 0 16 16">
            <path
              d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
          </svg>
        </a>
      </div>
    </div>


    <div class="card-body">
      <div class="table-responsive p-3">
        <table class="table shadow-lg table-bordered table-sm" id="dataTable" name="dataTable"
          style="border: 1px solid #dddddd70">
          <thead style="height:4.5rem;">
            <tr class="bg-primary text-light my-2 text-center">
              <th scope="col" class="align-middle" width="7%" style="font-weight:300;">Select</th>
              <th scope="col" class="align-middle" width="8%" style="font-weight:200;">Project No.</th>
              <th scope="col" class="align-middle" width="11%" style="font-weight:400">Project Title</th>
              <th scope="col" class="align-middle fw-normal" width="13%" style="font-weight:500;">Customer Name</th>
              <th scope="col" class="align-middle fw-lighter" width="10%" style="font-weight:500;">Team</th>
              <th scope="col" class="align-middle fw-light" width="10%" style="font-weight:500;">Amount</th>
              <th scope="col" class="align-middle" width="20%" style="font-weight:500;">Comments</th>
              <th scope="col" class="align-middle" width="10%" style="font-weight:500;">Service Date</th>
              <th scope="col" class="align-middle" width="10%" style="font-weight:500;">Last Modified</th>
              <th scope="col" class="align-middle" style="font-weight:500;">Action</th>
            </tr>
          </thead>
          <tbody style="background: #dddddd70;">
            <?php


            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
            $offset = ($page - 1) * $perPage;

            // Modify your SQL query to include LIMIT
            // Add this near the top of your PHP code (after $perPage is set)
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';


            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
            $offset = ($page - 1) * $perPage;

            $searchTermSafe = addslashes($searchTerm);

            $sql = "
            SELECT * FROM (
              SELECT 
                dd.project_id,
                dd.urgency,
                dd.reopen_status,
                NULL AS quick_project,
                dd.project_name,
                dd.p_team,
                NULL AS revision_project_id,
                dd.setTarget,
                c.customer_name,
                cu.price,
                cu.comments,
                cu.rownumber,
                cu.last_modified_date,
                cu.service_date
              FROM 
                deliverable_data dd 
              LEFT JOIN 
                contacts c ON dd.contact_id = c.contact_id
              LEFT JOIN
                csa_finance_uninvoiced cu ON dd.project_id = cu.project_id
              WHERE 
                dd.setTarget IS NULL
                AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id AND cu.rownumber = FI.rownumber)
                AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id AND cu.rownumber IS NULL)
                AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id AND cu.rownumber = RI.rownumber)
                AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id AND cu.rownumber IS NULL)

              UNION ALL

              SELECT 
                p.project_id,
                p.urgency,
                p.reopen_status,
                p.quick_project,
                p.project_name,
                p.p_team,
                p.revision_project_id,
                p.setTarget,
                c.customer_name,
                cu.price,
                cu.comments,
                cu.rownumber,
                cu.last_modified_date,
                cu.service_date
              FROM 
                projects p 
              LEFT JOIN 
                contacts c ON p.contact_id = c.contact_id
              LEFT JOIN
                csa_finance_uninvoiced cu ON p.project_id = cu.project_id
              WHERE 
                p.setTarget IS NULL
                AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id AND cu.rownumber = FI.rownumber)
                AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id AND cu.rownumber IS NULL)
                AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id AND cu.rownumber = RI.rownumber)
                AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id AND cu.rownumber IS NULL)
            ) AS combined_results
            ";

            if (!empty($searchTerm)) {
              $sql .= " WHERE (
                project_id LIKE '%$searchTermSafe%' OR 
                project_name LIKE '%$searchTermSafe%' OR 
                p_team LIKE '%$searchTermSafe%' OR 
                customer_name LIKE '%$searchTermSafe%' OR 
                comments LIKE '%$searchTermSafe%'
              )";
            }

            $sql .= " LIMIT $perPage OFFSET $offset";
            $countSql = "
              SELECT COUNT(*) as total FROM (
                SELECT 
                  dd.project_id,
                  dd.project_name,
                  dd.p_team,
                  c.customer_name,
                  cu.comments
                FROM 
                  deliverable_data dd 
                LEFT JOIN 
                  contacts c ON dd.contact_id = c.contact_id
                LEFT JOIN
                  csa_finance_uninvoiced cu ON dd.project_id = cu.project_id
                WHERE 
                  dd.setTarget IS NULL
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id AND cu.rownumber = FI.rownumber)
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id AND cu.rownumber IS NULL)
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id AND cu.rownumber = RI.rownumber)
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id AND cu.rownumber IS NULL)

                UNION ALL

                SELECT 
                  p.project_id,
                  p.project_name,
                  p.p_team,
                  c.customer_name,
                  cu.comments
                FROM 
                  projects p 
                LEFT JOIN 
                  contacts c ON p.contact_id = c.contact_id
                LEFT JOIN
                  csa_finance_uninvoiced cu ON p.project_id = cu.project_id
                WHERE 
                  p.setTarget IS NULL
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id AND cu.rownumber = FI.rownumber)
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id AND cu.rownumber IS NULL)
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id AND cu.rownumber = RI.rownumber)
                  AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id AND cu.rownumber IS NULL)
              ) AS combined_results
              ";


            if (!empty($searchTerm)) {
              $searchTermSafe = addslashes($searchTerm);
              $countSql .= " WHERE (
              project_id LIKE '%$searchTermSafe%' OR 
              project_name LIKE '%$searchTermSafe%' OR 
              p_team LIKE '%$searchTermSafe%' OR 
              customer_name LIKE '%$searchTermSafe%' OR 
              comments LIKE '%$searchTermSafe%'
            )";
            }

            // Get total projects and pages
            $totalResult = $conn->query($countSql);
            $totalRow = $totalResult->fetch_assoc();
            $totalProjects = $totalRow['total'];
            $totalPages = ceil($totalProjects / $perPage);

            $startEntry = $offset + 1;
            $endEntry = min($offset + $perPage, $totalProjects);

            // Fetch invoice data
            $info = $obj_admin->manage_all_info($sql);
            $serial = 1;
            $num_row = $info->rowCount();

            if ($num_row == 0) {
              echo '<tr><td colspan="7">No projects were found</td></tr>';
            }
            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
              ?>
              <tr>
                <form id="invoice-form-<?= $row['project_id']; ?>"
                  action="add_uninvoiced.php?rownumber=<?= $row['rownumber']; ?>&project_id=<?= $row['project_id']; ?>"
                  method="POST" class="project-form" onsubmit="return validateForm(this, event);">

                  <td class="align-middle text-center">
                    <!-- Checkbox for selecting project -->
                    <input type="checkbox" class="select-project" name="selected_projects[]"
                      value="<?= $row['project_id']; ?>,<?= $row['rownumber']; ?>" style="height:1.3rem; width:1.3rem;" />
                  </td>
                  <td class="align-middle text-center">
                    <div
                      style="display:flex; align-items: center; justify-content: center; height:1.7rem; width:3.8rem; background-color: <?= $row['urgency']; ?>; text-align: center; border-radius: 5px; color: <?= ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff'; ?>">
                      <?= !empty($row['revision_project_id']) ? $row['revision_project_id'] : $row['project_id']; ?>
                    </div>
                    <?php if ($row['rownumber'] > 0): ?><span
                        class="badge badge-pill badge-danger">V<?= $row['rownumber']; ?></span><?php endif; ?>
                    <span class="badge badge-pill badge-danger"><?= $row['reopen_status']; ?></span>
                    <span
                      class="badge badge-pill badge-danger"><?= $row['quick_project'] ? 'Quick Project' : ''; ?></span>
                  </td>
                  <td class="align-middle">
                    <input type="text" name="project_name" value="<?= $row['project_name']; ?>"
                      style="border: none; background-color:transparent; display:none">
                    <label><?= $row['project_name']; ?></label>
                  </td>
                  <td class="align-middle">
                    <input type="text" name="customer_name" value="<?= $row['customer_name']; ?>"
                      style="border: none; background-color:transparent; display:none">
                    <label><?= $row['customer_name']; ?></label>
                  </td>
                  <td class="align-middle">
                    <input type="text" name="p_team" value="<?= $row['p_team']; ?>"
                      style="border: none; background-color: transparent; display:none">
                    <label><?= $row['p_team']; ?></label>
                  </td>
                  <td class="align-middle">
                    <input type="text" name="price" class="price" value="<?= $row['price']; ?>"
                      style="outline:none; width:100px; border:none;">
                  </td>
                  <td class="align-middle">
                    <textarea rows="1" name="comments" class="comments" style="outline:none; border: none; width: 250px;"> <?php
                    if (isset($row['comments'])) {
                      echo $row['comments'];
                    } else {
                      echo !empty($row['revision_project_id']) ? $row['revision_project_id'] : $row['project_id'];
                      echo '_' . $row['project_name'];
                      echo '_' . $row['customer_name'];
                    }
                    ?>
                                  </textarea>
                  </td>
                  <td class="align-middle text-center">
                    <input type="date" name="service_date" class="service_date" value="<?= $row['service_date']; ?>"
                      style="border: none;  width: 100%;">
                  </td>

                  <td class="align-middle text-center"><?= $row['last_modified_date']; ?></td>
                  <td class="align-middle d-flex justify-content-around">
                    <div class="my-2 d-flex">
                      <button type="submit" name="save" class="ml-2 mt-2 btn text-info btn-sm btn-light"
                        style="height: 2rem;"
                        onclick="return confirm('Are you sure you want to save this information?')">Save</button>
                      <a href="./task-details.php?project_id=<?= $row['project_id']; ?>"
                        class="ml-2 mt-2 btn text-info btn-sm btn-light" style="height: 2rem;">View</a>
                      <?php if ($row['rownumber'] > 0): ?>
                        <button type="submit" name="delete_row" class="btn btn-lg bi bi-trash text-danger m-auto fa-lg"
                          data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Variation Project"
                          onclick="return confirm('Are you sure you want to delete this variation project?');"></button>
                      <?php else: ?>
                        <button type="submit" name="add_row" class="btn btn-lg bi bi-plus-circle text-info fa-lg"
                          data-bs-toggle="tooltip" data-bs-placement="top" title="Create Variation Project"
                          onclick="return confirm('Are you sure you want to create a new variation project?');"></button>
                      <?php endif; ?>
                    </div>
                  </td>
                </form>
                <script>
                  function validateForm(form, event) {
                    // Get the name of the button clicked
                    var clickedButton = event.submitter ? event.submitter.name : null;

                    // Skip validation for "delete_row" and "add_row" buttons
                    if (clickedButton === "delete_row" || clickedButton === "add_row") {
                      return true; // Allow the form submission without validation
                    }

                    // Get the values of the required fields for validation
                    var projectName = form.project_name.value.trim();
                    var customerName = form.customer_name.value.trim();
                    var price = form.price.value.trim();
                    var serviceDate = form.service_date.value.trim();
                    var comments = form.comments.value.trim(); // Added validation for comments

                    // Check if any required field is empty


                    return true; // Allow form submission
                  }
                </script>
              </tr>

            <?php } ?>
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
            $searchParam = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
            ?>
            <!-- Previous -->
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
              </li>
            <?php endif; ?>

            <!-- First Page -->
            <li class="page-item <?= $page == 1 ? 'active' : '' ?>">
              <a class="page-link" href="?page=1">1</a>
            </li>

            <!-- Left Dots -->
            <?php if ($page > 4): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>

            <!-- Pages Around Current -->
            <?php for ($i = max(2, $page - 2); $i <= min($totalPages - 1, $page + 2); $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <!-- Right Dots -->
            <?php if ($page < $totalPages - 3): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>

            <!-- Last Page -->
            <?php if ($totalPages > 1): ?>
              <li class="page-item <?= $page == $totalPages ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a>
              </li>
            <?php endif; ?>

            <!-- Next -->
            <?php if ($page < $totalPages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
              </li>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $totalPages ?>">Last</a>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <!-- Due Date Input Field (Separate) -->


                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Project ID</th>
                      <th>Price</th>
                      <th>Comments</th>
                      <th>Service Date</th>
                    </tr>
                  </thead>
                  <tbody id="projectDetailsList">
                    <!-- Project details will be dynamically inserted here -->
                  </tbody>
                </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Button to trigger the modal -->
        <div class="d-flex mt-3">
          <button type="button" class="btn btn-success" name="moveToReadyToStart" onclick="submitAllForms()">Ready to
            Invoice</button>
        </div>

        <!-- Hidden form to submit all selected projects -->
        <form id="invoice-form" method="POST" action="add_uninvoiced.php" style="display:none;">
          <!-- Hidden inputs will be added here dynamically -->
        </form>

        <script>
          function submitAllForms() {
            const selectedProjects = document.querySelectorAll('.select-project:checked');

            if (selectedProjects.length === 0) {
              alert('Please select at least one project.');
              return;
            }

            // Get the modal and project details list (tbody)
            const modal = $('#confirmationModal');
            const projectDetailsList = document.getElementById('projectDetailsList');
            projectDetailsList.innerHTML = ''; // Clear previous project details

            // Get the due date value from the input field


            // Loop through all selected projects and collect their details
            selectedProjects.forEach(project => {
              // Split the value into project_id and rownumber
              const [projectId, rownumber] = project.value.split(',');

              // Get the corresponding price, comments, and service_date for this project row
              const row = project.closest('tr');
              const price = row.querySelector('.price').value;
              const comments = row.querySelector('.comments').value;
              const serviceDate = row.querySelector('.service_date').value;

              // Create a table row for each selected project
              const tr = document.createElement('tr');

              // Create and append table cells for project ID, price, comments, and service date
              const tdProjectId = document.createElement('td');
              tdProjectId.textContent = projectId;
              tr.appendChild(tdProjectId);

              const tdPrice = document.createElement('td');
              tdPrice.textContent = price;
              tr.appendChild(tdPrice);

              const tdComments = document.createElement('td');
              tdComments.textContent = comments;
              tr.appendChild(tdComments);

              const tdServiceDate = document.createElement('td');
              tdServiceDate.textContent = serviceDate;
              tr.appendChild(tdServiceDate);

              // Append the row to the table body
              projectDetailsList.appendChild(tr);
            });

            // Show the modal
            modal.modal('show');

            // Attach event listener to confirm button
            document.getElementById('confirmButton').addEventListener('click', function () {
              const form = document.getElementById('invoice-form');
              form.innerHTML = ''; // Clear previous hidden inputs

              // Validate that due date is selected


              let missingPrice = false;
              let missingServiceDate = false;

              // Loop through all selected projects to check if price or service date is missing
              selectedProjects.forEach(project => {
                const [projectId, rownumber] = project.value.split(',');

                // Get the corresponding price, comments, and service_date for this project row
                const row = project.closest('tr');
                const price = row.querySelector('.price').value;
                const comments = row.querySelector('.comments').value;
                const serviceDate = row.querySelector('.service_date').value;

                // Check if price or service date is missing
                if (!price) {
                  missingPrice = true;
                }
                if (!serviceDate) {
                  missingServiceDate = true;
                }

                // Create hidden inputs for each selected project, price, comments, service_date, and the due_date
                const projectInput = document.createElement('input');
                projectInput.type = 'hidden';
                projectInput.name = 'selected_projects[]';
                projectInput.value = `${projectId},${rownumber}`;
                form.appendChild(projectInput);

                const priceInput = document.createElement('input');
                priceInput.type = 'hidden';
                priceInput.name = 'prices[]';
                priceInput.value = price;
                form.appendChild(priceInput);

                const commentsInput = document.createElement('input');
                commentsInput.type = 'hidden';
                commentsInput.name = 'comments[]';
                commentsInput.value = comments;
                form.appendChild(commentsInput);

                const serviceDateInput = document.createElement('input');
                serviceDateInput.type = 'hidden';
                serviceDateInput.name = 'service_dates[]';
                serviceDateInput.value = serviceDate;
                form.appendChild(serviceDateInput);


              });

              // Show alert if price or service date is missing
              if (missingPrice) {
                alert('Please fill in the price for all selected projects.');
                return;
              }

              if (missingServiceDate) {
                alert('Please fill in the service date for all selected projects.');
                return;
              }

              // Submit the form if everything is correct
              form.submit();

              // Close the modal after submission
              modal.modal('hide');
            });
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
      var customerCell = rows[i].getElementsByTagName('td')[3];
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
include './include/footer.php';
?>
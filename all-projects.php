<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';

// auth check

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$user_role = $_SESSION['user_role'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';


include 'include/sidebar.php';


?>

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet"
  integrity="sha512-XMx6nxAO9VAIHvEipUzklxoRQ/BMQE7r3wRI7glOWz2P7RrtfbUzfHygNmA8yGvSOT0EHHGwxOLcEvnOsZK7Xg=="
  crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- jQuery -->


<!-- Bootstrap JS -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>
  table {
    width: 100%;
    border-collapse: collapse;

  }

  th,
  td {
    padding: 8px;
    text-align: left;
  }

  #customSearchInput {
    width: 20rem;
    height: 2rem;

    padding: 0.5rem;
    outline: none;
    border: 1px solid lightgray;
    background: rgb(228, 228, 228);

  }


  #customSearchInput:hover {
    outline: none;
    border: 5px solid lightgray;
  }

  .search-icon-container {
    background: black;
    padding-top: 0.3rem;
    padding-left: 0.8rem;
    height: 2rem;
    width: 2.5rem;
    border-radius: 0.7rem 0 0 0.7rem;
  }

  #customPageInput {
    height: 2rem;
    width: 4rem;

    padding: 0.5rem;
    outline: none;
    border: 1px solid lightgray;
    background: rgb(228, 228, 228);

  }

  #customPageInput:hover {
    outline: none;
    border: 3px solid lightgray;
  }

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

  .fixed-pagination {
    /* position: fixed; */
    bottom: 0;
    width: 100%;
    z-index: 999;
    background: #133E87;
    padding: 0.3rem 1rem;
    /* Reduced padding for a lower height */
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    /* Align pagination to the right */
    align-items: center;
    /* Vertically center the content */
    margin: 0;
  }

  #customSearchInput {
    width: 20rem;
    height: 2rem;

    padding: 0.5rem;
    outline: none;
    border: 1px solid lightgray;
    background: rgb(235, 235, 235);
    border-radius: 0 6px 6px 0;

  }


  #customSearchInput:hover {
    outline: none;
    border: 2px solid gray;
  }

  .search-icon-container {
    background: #0d6efd;
    padding-top: 0.3rem;
    padding-left: 0.7rem;
    height: 2rem;
    width: 2.5rem;
    border-radius: 6px 0 0 6px;
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

  .custom-blue-border {

    border: 1.5px solid #0d6efd;
    /* background: rgb(223, 247, 253);
    box-shadow: 0 7px 7px rgba(0, 0, 0, 0.1); */


  }

  .custom-blue-font {
    color: #0d6efd
  }

  .tippy-box[data-theme~='custom-tooltip'] {
    background-color: #133e87;
    color: white;
    border-radius: 50px;
    padding: 10px 10px;
    border: 1px solid white;

  }


  .tippy-box[data-theme~='custom-tooltip-sidebar'] {
    background-color: black;
    color: white;

    padding: 10px 10px;


  }

  .entries-info {
    white-space: nowrap;
    /* keep text in one line */
    margin: 0;
    /* remove default margins */
    position: relative;
    top: -40px;
    /* move it up slightly */
  }
</style>




<div class=" mx-auto" style="width: 97.5%">

  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">All Open Projects / Closed Projects / Cancelled Projects</h1>

  </div>


  <style>
    td {
      text-align: center;
    }

    /* Custom CSS to increase the size of the select field */
    .select2-container--default .select2-selection--single {
      height: 38px;
      /* Adjust the height of the select field */
      width: 460px
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 38px;
      /* Center the text vertically */
    }
  </style>


  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <style>
    .custom-red {
      background-color: red;
      color: white;
      border-radius: 5px;

      /* Optional, set text color to contrast with the background */
    }

    .custom-orange {
      background-color: orange;
      color: white;
      border-radius: 5px;

    }

    .custom-white {
      background-color: white;
      color: black;
      border-radius: 5px;

    }

    .custom-green {
      background-color: green;
      color: white;
      border-radius: 5px;

    }

    .custom-purple {
      background-color: purple;
      color: white;
      border-radius: 5px;

    }



    tbody textarea {
      height: 1.5rem;
      outline: none;
      border: 1px solid gainsboro;
    }

    tbody textarea:hover {
      border: 1px solid gray;
    }

    .save-btn {
      height: 1.6rem;
      display: flex;
      align-content: center;
      vertical-align: middle;
      border-radius: 4px;
      border: 1.5px solid green;
      color: green;
      transition: 0.3s ease-out;
    }

    .save-btn:hover {
      background: green;

      color: white;
      transform: scale(1.2);
    }

    .assign-btn {
      height: 1.6rem;
      display: flex;
      align-content: center;
      vertical-align: middle;
      border-radius: 4px;
      transition: 0.3s ease-out;
    }

    .assign-btn:hover {
      background: #0d6efd;
      transform: scale(1.2);

      color: white;
    }

    .more-btn {
      height: 1.6rem;
      display: flex;
      align-content: center;
      vertical-align: middle;
      border-radius: 4px;
      color: #484747;
      border: 1.5px solid #484747;
      transition: 0.3s ease-out;
    }

    .more-btn:hover {
      background: #484747;
      transform: scale(1.2);

      color: white;
    }

    #customSearchWrapper button {
      transition: 0.3s ease-out;
    }

    #customSearchWrapper button:hover {
      transform: scale(1.15);
      color: white;
    }
  </style>

  <div class="modal fade" id="filter-modal" tabindex="-1" aria-labelledby="filter-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-primary" id="filter-modal-label">Filter Options</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <l class="modal-body">
          <!-- Filter by Project ID -->
          <label for="project-id-filter" class="text-primary">Filter by Project ID:</label>
          <input type="number" id="project-id-filter" class="form-control" placeholder="Enter Project ID">
          <!-- Filter by Engineer -->
          <label for="engineer-filter" class="text-primary mt-3">Filter by Engineer:</label>
          <select id="engineer-filter" class="form-control">
            <option value="">Select Engineer</option>
            <?php
            // Assuming you have a valid connection to the database
            require './conn.php';

            // Fetch unique engineers from the projects table
            $sql_engineers = "SELECT DISTINCT assign_to FROM projects WHERE assign_to IS NOT NULL order by assign_to";
            $result_engineers = $conn->query($sql_engineers);

            // Create a dropdown option for each engineer
            if ($result_engineers->num_rows > 0) {
              while ($row = $result_engineers->fetch_assoc()) {
                echo '<option value="' . $row['assign_to'] . '">' . $row['assign_to'] . '</option>';
              }
            } else {
              echo '<option value="">No Engineers Available</option>';
            }
            ?>  
          </select>

          <!-- Initialize Select2 -->
          <script>
            $(document).ready(function () {0
              $('#engineer-filter').select2({
                placeholder: "Select Engineer",
                allowClear: true
              });
            });
          </script>

          <!-- Filter by Customer Id -->
          <label for="customer-id-filter" class="text-primary mt-3">Filter by Customer:</label>
          <select id="customer-id-filter" class="form-control">
            <option value="">Select Customer</option>
            <!-- Customer options will be populated dynamically here -->
            <?php
            require './conn.php';

            // Fetch unique contact_ids and customer_names by joining the 'projects' and 'contacts' tables
            $sql_customers = "SELECT DISTINCT p.contact_id, c.customer_name, customer_id 
                    FROM projects p
                    JOIN contacts c ON p.contact_id = c.contact_id
                    WHERE p.contact_id IS NOT NULL order by c.customer_name";

            $result_customers = $conn->query($sql_customers);

            // Create a dropdown option for each customer
            if ($result_customers->num_rows > 0) {
              while ($row = $result_customers->fetch_assoc()) {
                echo '<option value="' . $row['customer_id'] . '">' . $row['customer_name'] . '</option>';
              }
            } else {
              echo '<option value="">No Customers Available</option>';
            }
            ?>
          </select>


          <!-- Filter by Team -->
          <label for="team-filter" class="text-primary mt-3">Filter by Team:</label>
          <select id="team-filter" class="form-control">
            <option value="">Select Team</option>
            <option value="Industrial Team">Industrial</option>
            <option value="Building Team">Building</option>
            <option value="IT">IT</option>
          </select>

          <label for="state-filter" class="text-primary mt-3">Filter by State</label>
          <select id="state-filter" class="form-control" name="state" required>
            <option value="">Select State</option>
            <Option value="QLD">QLD (Queensland)</Option>
            <Option value="NSW">NSW (New South Wales)</Option>
            <Option value="WA">WA (Western Australia)</Option>
            <Option value="SA">SA (South Australia)</Option>
            <Option value="VICTORIA">VICTORIA</Option>
            <Option value="N/A">Not Applicable</Option>
          </select>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" id="apply-filter">Apply Filter</button>
          </div>
      </div>
    </div>
  </div>



  <!-- Modal -->
  <div class="modal fade" id="indicator-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Color References</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-dark">




          <div class="row justify-content-center text-center mt-1" style="font-size: 0.7rem;">
            <div class="col-6 col-md-1 border p-1 custom-red mr-1 " style="width: 7rem;">Very Urgent</div>
            <div class="col-6 col-md-1 border p-1 custom-orange mr-1" style="width: 7rem;">Urgent</div>
            <div class="col-6 col-md-1 border p-1 custom-white mr-1" style="width: 7rem;">Waiting</div>
            <div class="col-6 col-md-1 border p-1 mr-1 bg-secondary text-light"
              style="width: 7rem; border-radius: 5px;">Not Started</div>

            <div class="col-6 col-md-1 border p-1 custom-green mr-1" style="width: 7rem;">In Progress</div>
            <div class="col-6 col-md-1 border p-1 mr-1" style="background: yellow; width: 7rem; border-radius: 5px;">On
              Hold</div>

            <div class="col-6 col-md-1 border p-1 mr-1 text-light"
              style="width: 7rem; border-radius: 5px; background: navy;">Completed</div>
            <div class="col-6 col-md-1 border p-1 custom-purple mr-1" style="width: 7rem;">Closed</div>
          </div>


        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm bg-gradient" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>






  <div id="customSearchWrapper" class="d-flex justify-content-between " style="position:sticky; top: 0; z-index: 1;">


    <div class="d-flex">

      <input type="number" id="customPageInput" min="1" value="20">
      <p class="entries-per-page-text text-secondary mb-2" style="font-size: 0.9rem;">entries per page</p>

    </div>

    <div class="d-flex">

      <div class="d-flex">
        <button class="btn bg-gradient btn-danger text-light btn-sm  mr-2" id="clear-search"
          style="height: 2rem;">Clear</button>

        <div class="search-icon-container"> <i class="fas fa-magnifying-glass text-light" id="search-icon"></i>
        </div>

        <input type="text" style="" id="customSearchInput" placeholder="Search..." />
        <div id="projectResults"></div>
        <script>
          document.addEventListener("DOMContentLoaded", function () {
            const clearSearchBtn = document.getElementById('clear-search');
            const searchInput = document.getElementById('customSearchInput');
            const searchIcon = document.getElementById('search-icon'); // <-- make sure your <i> has id="search-icon"

            // Clear button functionality
            clearSearchBtn.addEventListener('click', function () {
              searchInput.value = ""; // reset search
              offset = 0;
              loadProjects(1); // reload full table
            });

            // Search icon functionality
            searchIcon.addEventListener('click', function () {
              offset = 0; // reset pagination
              loadProjects(1); // reload projects with current search input
            });
          });
        </script>





        </script>

      </div>
      <button class="btn bg-gradient btn-info btn-sm  ml-2" data-bs-toggle="modal" data-bs-target="#indicator-modal"
        style="height: 2rem;"><i class="fa-regular fa-circle-question mr-1"></i>Help </button>
      <button style="height: 2rem;" type="button" data-toggle="modal" data-target="#filter-modal"
        class="btn  btn-sm ml-2 blue-gradient-btn">
        <i class="fa-solid fa-filter text-light"></i> Filter
      </button>




    </div>

  </div>





  <div class="table-container  w-100" style="max-height: 100vh; overflow-y: auto; ">


    <table class="table text-center table-sm w-100 ">

      <thead class="fixedheader">
        <tr class="text-light align-middle font-weight-normal text-center fixedheader" style="height: 3.5rem;">
          <th data-sort="project_id" width="10%"
            class="fixedheader align-middle font-weight-normal text-center sortable">Id </th>
          <th data-sort="project_name" width="17%"
            class=" fixedheader align-middle font-weight-normal text-center sortable">Title </th>
          <th data-sort="customer_name" class=" fixedheader align-middle font-weight-normal text-center sortable">
            Customer </th>
          <th data-sort="state" width="8%" class="fixedheader align-middle font-weight-normal text-center sortable">
            Office </th>
          <th data-sort="p_team" class=" fixedheader align-middle font-weight-normal text-center sortable">Team </th>
          <th data-sort="project_manager" width="6%"
            class=" fixedheader align-middle font-weight-normal text-center sortable">Manager </th>
          <th data-sort="assigned_to" width="6%"
            class=" fixedheader align-middle font-weight-normal text-center sortable">Technician </th>
          <th data-sort="hours" class=" fixedheader align-middle font-weight-normal text-center sortable">Hours </th>
          <th data-sort="end_date" width="6%" class=" fixedheader align-middle font-weight-normal text-center sortable">
            ECD </th>
          <th data-sort="comments" width="23%"
            class=" fixedheader align-middle font-weight-normal text-center sortable">Comments </th>
          <th width="10%" class="fixedheader align-middle font-weight-normal text-center">Actions</th>
        </tr>
      </thead>

      <tbody style="background: rgb(245, 245, 245) ;">

      </tbody>
    </table>
  </div>

  <style>
    #pagination {
      margin-bottom: 80px !important;
      /* increase this value as needed */
    }
  </style>

  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Action Center</h5>
          <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="fw-bold mb-2">
            <span hidden id="modal-project-id" class="text-info fw-normal"></span>
            <span hidden id="modal-table-id" class="text-info fw-normal"></span>
            <span hidden id="modal-subproject-id" class="text-info fw-normal"></span>
            <span hidden id="assign_to_id"></span>
            <span hidden id="verify_by"></span>
            <span hidden id="project_mana"></span>
            <span hidden id="checkerstatus"></span>
          </p>
          <div class="d-flex flex-wrap justify-content-start mt-3">
            <button id="upload-button" type="button" class="btn btn-primary mb-2 mr-2">Upload Data</button>
            <?php if ($user_role == 1 or $user_role == 3) { ?>
              <button id="update-project-button" type="button" class="btn btn-warning mb-2 mr-2">Update Project</button>
              <script>
                document.getElementById("update-project-button").addEventListener("click", function () {
                  sessionStorage.setItem("lastVisitedURL", window.location.href);
                  console.log("Current URL stored:", window.location.href);
                });
              </script>
            <?php } ?>
            <?php if ($user_role == 1 or $user_role == 3) { ?>
              <button id="delete-project-button" type="button" class="btn btn-danger mb-2 mr-2">Delete Project</button>
            <?php } ?>
            <button id="view-project-button" class="view-project btn btn-secondary mb-2 mr-2" type="button">View
              Project</button>
            <?php if ($user_role == 1) { ?>
              <!-- <button type="button" class="btn btn-success mb-2 mr-2" id="verify-button">Assign Checker</button> -->
            <?php } ?>

            <?php if (($user_role == 1 || $user_role == 3)) { ?>
              <!-- <button type="button" id="Close-project" class="btn btn-danger mb-2 mr-2" data-bs-toggle="modal"
                data-bs-target="#status_model"> Mark Complete</button> -->
            <?php } ?>
            <?php if ($user_role == 2) { ?>

              <button id="send_to_pm"
                href="send_to_project_manager_sub_project.php?table_id=<?php echo $row['table_id']; ?>" type="button"
                class="btn  btn-primary mb-2 mr-2	"
                onclick="return confirm('Are you sure you want to send this to the Project Manager?');">
                Send to PM
              </button>
            <?php } ?>
            <?php if (($user_role == 1 || $user_role == 3)) { ?>

              <button id="send_Back" name="send_back" data-toggle="modal" data-target="#yourModalID"
                class="btn btn-warning mb-2 mr-2">Send To Engineer</button>
            <?php } ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="status_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Confirm Project Completion</h5>
          <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <form method="POST" id="status_model_form">
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" name="close_project">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="assign_to_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Are you sure?</h5>
          <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <form id="assign-form" action="" method="POST">
          <div class="modal-body">
            <span id="modal-content-label"></span>
            <h6 id="test"></h6>
            <!-- Hidden fields for project_id and subproject -->
            <input type="hidden" id="assign-modal-table-id" name="table_id" value="">
            <input type="hidden" id="assign-modal-project-id" name="project_id" value="">
            <input type="hidden" id="assign-modal-subproject-id" name="subproject_id" value="">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="assign-submit-button">Send</button>
          </div>
        </form>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const assignModal = document.getElementById("assign_to_status");

      assignModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const projectId = button.getAttribute("data-bs-project-id"); // Get project ID
        const subproject = button.getAttribute("data-bs-subproject-id"); // Get subproject ID
        const tableId = button.getAttribute("data-bs-table-id"); // Get table ID
        const modal = this;
        const form = document.getElementById("assign-form");

        if (subproject && subproject !== "null") {
          modal.querySelector('#modal-content-label').textContent = "Send Subproject to Engineer";
          modal.querySelector('#test').textContent = `Subproject ID: ${subproject}`;
          modal.querySelector('#assign-modal-project-id').value = ""; // Clear project_id
          modal.querySelector('#assign-modal-subproject-id').value = subproject; // Set subproject ID
          modal.querySelector('#assign-modal-table-id').value = tableId; // Set table ID
          form.setAttribute("action", "?action=assign_subproject"); // Update form action
        } else {
          modal.querySelector('#modal-content-label').textContent = "Send Project to Engineer";
          modal.querySelector('#test').textContent = `Project ID: ${projectId}`;
          modal.querySelector('#assign-modal-project-id').value = projectId; // Set project ID
          modal.querySelector('#assign-modal-subproject-id').value = ""; // Clear subproject_id
          modal.querySelector('#assign-modal-table-id').value = ""; // Clear table ID
          form.setAttribute("action", "?action=assign_project"); // Update form action
        }
      });
    });
  </script>



  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const exampleModal = document.getElementById("exampleModal");

      exampleModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const projectId = button.getAttribute("data-bs-project-id");
        const tableId = button.getAttribute("data-bs-table-id");
        const projectName = button.getAttribute("data-bs-name");
        const assignTo = button.getAttribute("data-bs-assign-to");
        const subproject = button.getAttribute("data-bs-subproject-id");
        const assignToId = button.getAttribute("data-bs-assign-to-id");
        const VerifyBy = button.getAttribute("data-bs-verify-by");
        const ProjectManagerStatus = button.getAttribute("data-bs-project-manager-status");
        const CheckerStatus = button.getAttribute("data-bs-checker-status");
        const userId = <?php echo json_encode($user_id); ?>;






        const modal = this; // The modal itself
        modal.querySelector("#checkerstatus").textContent = CheckerStatus;
        modal.querySelector("#modal-project-id").textContent = projectId;
        modal.querySelector("#assign_to_id").textContent = assignToId;
        modal.querySelector("#verify_by").textContent = VerifyBy;
        modal.querySelector("#project_mana").textContent = ProjectManagerStatus;

        const verifyButton = document.getElementById("verify-button");
        if (verifyButton) {
          if (assignToId == <?php echo $user_id; ?> && (VerifyBy == <?php echo $user_id; ?>) || (ProjectManagerStatus == 0) && (CheckerStatus == 1 || CheckerStatus == null)) {
            verifyButton.style.display = "none";
          } else {
            verifyButton.style.display = "block";
          }
          if (ProjectManagerStatus === '3') {
            verifyButton.textContent = "ReAssign";
          } else {
            verifyButton.textContent = "Assign Checker";
          }
        }
        const SendToPm = document.getElementById("send_to_pm");
        if (SendToPm) {
          if (CheckerStatus == 1) {
            SendToPm.style.display = "none";
          } else {
            SendToPm.style.display = "block";

          }

        }

        const sendBackforButton = document.getElementById('send_Back');
        if (sendBackforButton) {
          if (VerifyBy == userId && CheckerStatus == 1) {
            sendBackforButton.style.display = "block";
          } else {
            sendBackforButton.style.display = "none";
          }
          if (ProjectManagerStatus == 3) {
            sendBackforButton.textContent = 'ReWork'
          } else {
            sendBackforButton.textContent = 'Send to Engineer'
          }
        }




        // modal.querySelector("#modal-table-id").textContent = tableId;
        // modal.querySelector("#modal-subproject-id").textContent = subproject;

        const closeProjectButton = modal.querySelector("#Close-project");
        if (closeProjectButton) closeProjectButton.onclick = function () {
          if (subproject === 'null' || subproject === null) {
            // If subproject is null, open the status_model modal
            const statusModal = new bootstrap.Modal(document.getElementById('status_model'));
            statusModal.show();

            // Update the form action with the projectId
            const form = document.getElementById('status_model').querySelector('form');
            form.action = `process_data-delivery.php?project_id=${projectId}`;

            // Update the modal body text with the projectId dynamically
            const modalBody = document.getElementById('status_model').querySelector('.modal-body');
            modalBody.innerHTML = `Are you certain you want to mark the project as completed?`;
          } else {
            // If subproject is not null, handle the subproject completion using the same modal
            const statusModal = new bootstrap.Modal(document.getElementById('status_model'));
            statusModal.show();

            // Ensure the form submits to the same file
            const form = document.getElementById('status_model').querySelector('form');
            form.action = ""; // Ensure the form submits to the same file

            // Set the form method to POST
            form.method = "POST";

            // Add the hidden input for closing the subproject
            const tableIdInput = document.createElement('input');
            tableIdInput.type = 'hidden';
            tableIdInput.name = 'table_id'; // Name of the field should be 'table_id'
            tableIdInput.value = tableId; // Value is the tableId from the button clicked
            form.appendChild(tableIdInput);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'close_subproject'; // This will trigger the PHP handler
            input.value = 'true';
            form.appendChild(input);

            // Update the modal body text for subproject dynamically
            const modalBody = document.getElementById('status_model').querySelector('.modal-body');
            modalBody.innerHTML = `Are you certain you want to mark the subproject as completed?`;
          }


        };


        const sendBack = modal.querySelector("#send_Back");
        if (sendBack) {
          sendBack.onclick = function () {
            window.location.href = subproject === 'null' ?
              `?send_back_project_id=${projectId}` :
              `?send_back_table_id=${tableId}`;
          }
        }


        const SendPm = modal.querySelector("#send_to_pm");
        if (SendPm) {
          SendPm.onclick = function () {
            window.location.href = subproject === 'null' ?
              `send_to_project_manager.php?project_id=${projectId}` :
              `send_to_project_manager_sub_project.php?table_id=${tableId}`;
          }
        };
        // Set additional dynamic button actions
        const uploadButton = modal.querySelector("#upload-button");
        uploadButton.onclick = function () {
          window.location.href = subproject === 'null' ?
            `googledrive_upload.php?project_id=${projectId}` :
            `engineeringData_sub_project.php?table_id=${tableId}`;
        };

        const updateButton = modal.querySelector("#update-project-button") ? modal.querySelector("#update-project-button") : '';
        updateButton.onclick = function () {
          window.location.href = subproject === 'null' ?
            `edit-project.php?project_id=${projectId}` :
            `edit-subproject.php?table_id=${tableId}`;
        };


        const viewButton = modal.querySelector("#view-project-button");
        viewButton.onclick = function () {
          window.location.href = subproject === 'null' ?
            `task-details.php?project_id=${projectId}` :
            `task-details_sub_project.php?table_id=${tableId}`;
        };

        const VerifyButton = modal.querySelector("#verify-button");
        if (VerifyButton) {
          VerifyButton.onclick = function () {
            window.location.href = subproject === 'null' ?
              `process_data-check.php?project_id=${projectId}` :
              `process_data-check_sub_project.php?table_id=${tableId}`;
          }
        };

        const deleteButton = modal.querySelector("#delete-project-button");
        if (deleteButton) {
          deleteButton.onclick = function () {
            // If subproject is not null or 'null', delete the subproject
            if (subproject !== 'null' && subproject !== null) {
              if (confirm("Are you sure you want to delete this subproject?")) {
                window.location.href = `?delete_SubProject_id=${tableId}`;
              }
            } else {
              // If subproject is null, delete the entire project
              if (confirm("Are you sure you want to delete this project?")) {
                window.location.href = `?delete_project_id=${projectId}`;
              }
            }
          }
        };
      });
    });
  </script>

</div>




<div class="modal fade" id="followup-modal" tabindex="-1" aria-labelledby="followupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="followupModalLabel">
          Follow Up: <span id="followup-id"></span>
        </h5>
        <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="" method="post">
          <input type="hidden" id="follow_project_id" name="project_id">
          <input type="hidden" id="follow_table_id" name="table_id">



          <div class="mb-3">
            <label for="followup_comments" class="form-label">Follow Up</label>
            <textarea name="followup_comments" id="followup_comments" class="form-control" rows="4"></textarea>
          </div>
          <div class="mb-3">
            <label for="followup_comments" class="form-label">Signature</label>

            <?php
            $query = "SELECT fullname FROM tbl_admin WHERE user_id = '$user_id'";
            $result = $conn->query($query);
            $row = $result->fetch_assoc();
            $fullname = $row['fullname'] ?? "Not Found"; // Default if not found
            ?>

            <!-- Visible field displaying the Full Name -->
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($fullname); ?>" disabled>

            <!-- Hidden field passing the User ID -->
            <input type="hidden" id="manager_id" name="manager_id" value="<?php echo htmlspecialchars($user_id); ?>">
          </div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary bg-gradient" data-bs-dismiss="modal">Close</button>
        <button type="submit" name="update_followup" class="btn btn-success bg-gradient">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const followUpModal = document.getElementById("followup-modal");


    followUpModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const projectID = button.getAttribute('data-bs-project-id');
      const tableID = button.getAttribute('data-bs-table-id');
      const comments = button.getAttribute('data-bs-comments');

      console.log(projectID);
      console.log(tableID);
      console.log(comments);

      const followupId = document.getElementById("followup-id");
      const followProjectInput = document.getElementById("follow_project_id");
      const followTableInput = document.getElementById("follow_table_id");

      // Assign values to modal fields
      if (followupId) followupId.innerText = projectID || "N/A";
      if (followProjectInput) followProjectInput.value = projectID || "";
      if (followTableInput) followTableInput.value = tableID || "";
    })
  })
</script>
<script>


  // Pagination setup
  let currentSortBy = "project_id";
  let currentSortOrder = "DESC";
  let offset = 0;
  let limit = 20;
  let searchTimeout = null;

  let filterState = {
    projectId: "",
    engineer: "",
    customerId: "",
    team: "",
    state: ""
  };






  const loadProjects = (page = 1) => {
    offset = (page - 1) * limit;
    const searchQuery = document.getElementById('customSearchInput').value.trim();
    // const projectIdFilter = document.getElementById('project-id-filter').value.trim();
    fetch(`allProjectsApi.php?limit=${limit}&offset=${offset}&search=${encodeURIComponent(searchQuery)}&sort_by=${currentSortBy}&sort_order=${currentSortOrder}
&project_id=${encodeURIComponent(filterState.projectId)}
&engineer=${encodeURIComponent(filterState.engineer)}
&customer_id=${encodeURIComponent(filterState.customerId)}
&team=${encodeURIComponent(filterState.team)}
&state=${encodeURIComponent(filterState.state)}`)


      .then(response => response.json())
      .then(data => {
        const tableBody = document.querySelector('tbody');
        const userRole = <?php echo json_encode($user_role); ?>;
        const userId = <?php echo json_encode($user_id); ?>;

        tableBody.innerHTML = ""; // clear old rows

        data.data.forEach(row => {
          // Your row processing code...
          const tr = document.createElement('tr');
          const formattedECD = formatDateToDMY(row.setTargetDate);
          tr.innerHTML = `
                         <td>${row.project_id || row.revision_project_id}</td>
          <td>${row.project_name || row.subproject_name}</td>
          <td>${row.customer_name}</td>
          <td class="align-middle text-center">${formattedECD}</td>
        `;



          tableBody.appendChild(tr);


          const urgencyColor = row.urgency || 'white'; // Default to 'white' if no urgency value
          const textColor = (urgencyColor === 'white' || urgencyColor === 'yellow' || urgencyColor === 'cancelled') ? '#000' : '#fff';
          const pidBorder = (urgencyColor === 'white' || urgencyColor === 'yellow') ? 'border: 1px solid lightgray' : '';
          const bgColor = (urgencyColor.toLowerCase() === "cancelled") ? "#d3d3c0" : urgencyColor; // Light grey for cancelled
          const textDecoration = (urgencyColor.toLowerCase() === "cancelled") ? "text-decoration: line-through;" : ""; // Strike-through text for cancelled

          tr.innerHTML = `
                  <td style="align-content: center; vertical-align: middle; justify-content: center" class="py-0 my-0 ">
                <div class="d-flex" style="align-items: center">
                    <div style="${pidBorder}; display:flex; align-items: center; font-size: 0.9rem; justify-content: center; height:1.5rem; margin-left: 1rem; width:3.2rem; text-align: center; border-radius: 5px; background-color: ${bgColor}; color: ${textColor}; ${textDecoration}">
                ${row.revision_project_id || row.project_id}
                  </div>
                    ${row.reopen_status ? `<span class="badge badge-pill badge-danger"
                      style="height: 1.5rem; margin-left: 2px; border-radius: 50%; font-size: 0.7rem; padding: 0.4rem;">${row.reopen_status}</span>` : ''}
                    ${row.subproject_status ? `<span class="badge badge-pill badge-danger"
                      style="height: 1.5rem; margin-left: 2px; border-radius: 50%; font-size: 0.7rem; padding: 0.4rem;">S${row.subproject_status}</span>` : ''}
                    </div>
                  </td>
                    <td class="align-middle my-0 py-0" >${row.subproject_status !== null ? row.subproject_name : row.project_name}</td>
                
                      <td class="align-middle my-0 py-0" data-bs-toggle='tooltip' data-placement='bottom' data-tippy-content="<b>Customer Name</b>: ${row.customer_name ? row.customer_name : 'N/A'}<br><b>Contact Name</b>: ${row.contact_name ? row.contact_name : 'N/A'}<br><b>Email</b>: ${row.contact_email ? row.contact_email : 'N/A'}<br><b>Phone</b>: ${row.contact_phone_number ? row.contact_phone_number : 'N/A'}"    >${row.customer_id || 'N/A'}</td>
                      <td class="align-middle my-0 py-0">${row.state || row.sub_state || 'N/A'}</td>
                        <td class="align-middle my-0 py-0" >${row.p_team || 'N/A'}</td>
                  <td class="align-middle my-0 py-0"  style="  ${row.project_manager_status === '1' ? 'border: solid orange;' : ''} ${row.project_manager_status === '3' ? 'background-color: purple; color: white;' : ''}">
                    ${formatProjectManagerName(row.project_manager)}
                  </td>
                  
                  <td class="align-middle my-0 py-0"  style="${row.assign_status === '1' ? 'border: 2px solid orange;' : ''}">${row.assign_to || 'N/A'}</td>
                  
                  <td class="align-middle my-0 py-0" >${row.sub_EPT || row.EPT || 'N/A'}</td>
                  
                    <td class="align-middle my-0 py-0">
                  ${row.end_date ? formatDateToDMY(row.end_date) : formatDateToDMY(row.sub_end_date)}
                    </td>

                


                  <td class="align-middle my-0 py-0">
                  <form method="POST" action="">


                      <div class="d-flex">
                      <input type="hidden" value="${row.revision_project_id || row.project_id}" id="project_id_form" name="project_id_form" />
                        <input type="hidden" value="${row.subproject_status}" id="subproject_status_form" name="subproject_status_form" />
                        <input type="hidden" value="${row.reopen_status}" id="reopen_status_form" name="reopen_status_form" />
                    

            
                      <textarea readonly 
                        class="comments-form w-100" 
                        id="${row.revision_project_id || row.project_id}" 
                          oninput="handleInput()" 
                            name="comments_form"
                            rows="4"
                            >
                            ${formatComments(row.comments)}
                              </textarea>

                              </div>

                              </form>
              
                    
                  
                    
                                </td>

                      
                                  

                

                                <td  class=" align-middle text-center">

                                <div class="d-flex justify-content-center align-items-center">




                            
                                              <button 
                                              data-bs-project-id="${row.project_id}" 
                                      data-bs-table-id="${row.table_id}" 
                                      data-bs-comments="${row.comments}" 
                                      data-bs-toggle="modal"
                                      data-bs-target="#followup-modal"
                                      class="custom-blue-border custom-blue-font assign-btn follow-up align-bottom">
                                      <i class="fa-regular fa-comment mt-1"></i>
                                    </button>


                      &nbsp;&nbsp
                    
                    <button 
                      type="button" 
                      class="more-btn" 
                      style=" "

                      data-bs-toggle="modal" 
                      data-bs-target="#exampleModal"
                      data-bs-project-id="${row.project_id}" 
                      data-bs-table-id="${row.table_id}" 
                      data-bs-subproject-id="${row.subproject_status}" 
                      data-bs-assign-to-id="${row.assign_to_id}"
                      data-bs-verify-by="${row.verify_by}"
                      data-bs-project-manager-status="${row.project_manager_status}"
                      data-bs-checker-status="${row.checker_status}"
                      data-bs-name="${row.project_name}" 
                      data-bs-assign_to="${row.assign_to}">
                      More
                    </button>

                    </div>
                  </td>
                  

                  
                  `;
          tableBody.appendChild(tr);
        });

        renderPagination(data.total, page);


        tippy('[data-bs-toggle="tooltip"]', {
          allowHTML: true,
          placement: 'bottom',
          theme: 'custom-tooltip',
        });


function formatComments(comments) {
          if (!comments) return ""; // Handle empty comments

          const separator = "-".repeat(103); // Creates a long line of dashes

          return comments
            .split("\n\n") // Split by double new lines (each entry)
            .map(comment => {
              let parts = comment.split(" --- "); // Split by '---'
              if (parts.length === 3) {
                let authorParts = parts[2].split("-"); // Split by '-'
                let author = authorParts[0].trim(); // Get manager name
                let role = authorParts[1]?.trim() == 1 ? "PM" : authorParts[1]?.trim() == 2 ? "Engineer" : "Unknown";

                return `Date: ${parts[0]}\n${parts[1]}\nWritten by: ${author} (${role})\n${separator}`;
              }
              return comment; // Return as-is if format is unexpected
            })
            .join("\n"); // Use single newline to remove extra gap
        }



       


        $(window).on('load', function () {
          // if ($.fn.dataTable.isDataTable('#open-project-table')) {
          //   dataTable.clear().destroy();
          // }
          //dataTable = initializeDataTable();

          setTimeout(function () {
            // Check if any filter input has values, then show the button
            if (
              $('#customSearchInput').val().trim() ||
              $('#project-id-filter').val().trim() ||
              $('#engineer-filter').val().trim() ||
              $('#customer-id-filter').val().trim() ||
              $('#team-filter').val().trim()
            ) {
              $('#clear-search').fadeIn(300); // Make the button visible
            }
          }, 500);
        });


      })
      .catch(error => console.error('Error fetching data:', error));
  }
  function formatProjectManagerName(projectManager) {
    // Default to 'N/A' if project manager is not set
    const nameParts = projectManager ? projectManager.split(' ') : [];
    let formattedName = 'N/A';
    if (nameParts.length > 0) {
      const firstName = nameParts[0];
      const lastNameInitial = nameParts.length > 1 ? nameParts[1].charAt(0) + '.' : '';
      formattedName = `${firstName} ${lastNameInitial}`;
    }
    return formattedName;
  }

  function editProject(id) {
    alert(`Edit Project ID: ${id}`);
  }

  function deleteProject(id) {
    alert(`Delete Project ID: ${id}`);
  }

  // Function to format date from Y-m-d to d-m-y
  function formatDateToDMY(dateString) {
    if (!dateString) return 'N/A';

    // Check if the date format is valid
    const [year, month, day] = dateString.split('-');

    if (year && month && day) {
      return `${day}-${month}-${year}`;
    }
    return dateString; // Return original if formatting fails
  }

  document.addEventListener("DOMContentLoaded", function () {
    var applyFilterBtn = document.getElementById("apply-filter");
    var clearFilterBtn = document.getElementById("clear-filter");

    if (applyFilterBtn && clearFilterBtn) {
      applyFilterBtn.addEventListener("click", function () {
        // Show the clear filter button when a filter is applied
        clearFilterBtn.style.display = "inline-block";
      });

      clearFilterBtn.addEventListener("click", function () {
        // Reset all filter inputs
        var projectInput = document.getElementById("project-id-filter");
        var engineerInput = document.getElementById("engineer-filter");
        var customerInput = document.getElementById("customer-id-filter");
        var teamInput = document.getElementById("team-filter");

        if (projectInput) projectInput.value = "";
        if (engineerInput) engineerInput.value = "";
        if (customerInput) customerInput.value = "";
        if (teamInput) teamInput.value = "";

        // Hide the clear filter button
        clearFilterBtn.style.display = "none";
      });
    }
  });

  const renderPagination = (total, currentPage) => {
    const totalPages = Math.ceil(total / limit);
    const startEntry = (currentPage - 1) * limit + 1;
    const endEntry = Math.min(currentPage * limit, total);

    // Create wrapper div if not exists
    let wrapperDiv = document.getElementById("pagination-wrapper");
    if (!wrapperDiv) {
      wrapperDiv = document.createElement("div");
      wrapperDiv.id = "pagination-wrapper";
      wrapperDiv.style.display = "flex";
      wrapperDiv.style.justifyContent = "space-between";
      wrapperDiv.style.alignItems = "center";
      wrapperDiv.style.flexWrap = "nowrap"; // ensure everything stays in one row
      wrapperDiv.style.marginTop = "1rem";
      wrapperDiv.style.marginBottom = "80px"; // extra bottom margin
      document.querySelector(".table-container").after(wrapperDiv);
    }

    // Entries info (left side)
    let entriesDiv = document.getElementById("entries-info");
    if (!entriesDiv) {
      entriesDiv = document.createElement("div");
      entriesDiv.id = "entries-info";
      entriesDiv.className = "entries-info"; // apply the CSS class
      wrapperDiv.appendChild(entriesDiv);
    }
    entriesDiv.textContent = `Showing ${startEntry} to ${endEntry} of ${total} entries`;






    // Pagination buttons (right side)
    let paginationDiv = document.getElementById("pagination");
    if (!paginationDiv) {
      paginationDiv = document.createElement("div");
      paginationDiv.id = "pagination";
      paginationDiv.style.display = "flex";
      paginationDiv.style.flexWrap = "nowrap"; // keep buttons in one row
      wrapperDiv.appendChild(paginationDiv);
    }
    paginationDiv.innerHTML = ""; // clear previous pagination

    // Prev button (secondary)
    const prevBtn = document.createElement("button");
    prevBtn.textContent = "Prev";
    prevBtn.className = `btn btn-sm btn-secondary mx-1`;
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => loadProjects(currentPage - 1);
    paginationDiv.appendChild(prevBtn);

    // Page numbers
    let startPage = Math.max(1, currentPage - 1);
    let endPage = Math.min(totalPages, currentPage + 1);

    if (startPage > 1) {
      addPageButton(1, currentPage, paginationDiv);
      if (startPage > 2) addDots(paginationDiv);
    }

    for (let i = startPage; i <= endPage; i++) addPageButton(i, currentPage, paginationDiv);

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) addDots(paginationDiv);
      addPageButton(totalPages, currentPage, paginationDiv);
    }

    // Next button (primary)
    const nextBtn = document.createElement("button");
    nextBtn.textContent = "Next";
    nextBtn.className = `btn btn-sm btn-primary mx-1`;
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => loadProjects(currentPage + 1);
    paginationDiv.appendChild(nextBtn);
  };

  // Helper: Add page button
  const addPageButton = (page, currentPage, container) => {
    const btn = document.createElement("button");
    btn.textContent = page;
    btn.className = `btn btn-sm mx-1 ${page === currentPage ? "btn-primary" : "btn-light"}`;
    btn.onclick = () => loadProjects(page);
    container.appendChild(btn);
  };

  // Helper: Add dots
  const addDots = (container) => {
    const span = document.createElement("span");
    span.textContent = "...";
    span.className = "mx-1";
    container.appendChild(span);
  };


  // Load first page on page load
  // Load first page on page load
  document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('customSearchInput');
    const applyFilterBtn = document.getElementById("apply-filter");
    const clearSearchBtn = document.getElementById('clear-search');
    const customPageInput = document.getElementById("customPageInput");

    const projectFilter = document.getElementById("project-id-filter");
    const engineerFilter = document.getElementById("engineer-filter");
    const customerFilter = document.getElementById("customer-id-filter");
    const teamFilter = document.getElementById("team-filter");
    const stateFilter = document.getElementById("state-filter");

    // ---------------------
    // Search Functionality
    // ---------------------
    searchInput.addEventListener('input', function () {
      offset = 0; // reset pagination
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        loadProjects(1); // reload projects with search
      }, 500);
    });

    // ---------------------
    // Project ID Filter Functionality
    // ---------------------
    applyFilterBtn.addEventListener("click", () => {
      filterState.projectId = projectFilter.value.trim();
      filterState.engineer = engineerFilter.value.trim();
      filterState.customerId = customerFilter.value.trim();
      filterState.team = teamFilter.value.trim();
      filterState.state = stateFilter.value.trim();

      console.log("Filters applied:", filterState);

      // Close modal
      $('#filter-modal').modal('hide');

      // Reset pagination & reload
      offset = 0;
      loadProjects(1);
    });

    clearSearchBtn.addEventListener('click', () => {
      searchInput.value = "";
      projectFilter.value = "";
      engineerFilter.value = "";
      customerFilter.value = "";
      teamFilter.value = "";
      stateFilter.value = "";

      filterState = { projectId: "", engineer: "", customerId: "", team: "", state: "" };
      offset = 0;
      loadProjects(1); // reload full table
    });

    // ---------------------
    // Sorting Functionality
    // ---------------------
    document.querySelectorAll("th.sortable").forEach(th => {
      th.style.cursor = "pointer";
      th.addEventListener("click", function () {
        const sortField = this.getAttribute("data-sort");
        currentSortOrder = currentSortBy === sortField
          ? currentSortOrder === "DESC" ? "ASC" : "DESC"
          : "DESC";
        currentSortBy = sortField;
        loadProjects(1);
      });
    });

    // ---------------------
    // Custom Page Input
    // ---------------------
    if (customPageInput) {
      let pageInputTimeout;

      // Debounce input
      customPageInput.addEventListener("input", () => {
        clearTimeout(pageInputTimeout);
        pageInputTimeout = setTimeout(() => {
          customPageInput.dispatchEvent(new Event("change"));
        }, 500);
      });

      // Handle change
      customPageInput.addEventListener("change", () => {
        let newLimit = parseInt(customPageInput.value, 10);

        // if (isNaN(newLimit) || newLimit <= 0) {
        //   alert("Please enter a valid number greater than 0");
        //   customPageInput.value = limit; // reset to previous value
        //   return;
        // }

        limit = newLimit;
        offset = 0;
        loadProjects(1);
      });
    }

    // Initial load
    loadProjects(1);
  });


</script>



<!-- Toastify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>



<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_followup'])) {
  $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : null;
  $table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : null;
  $comments = isset($_POST['followup_comments']) ? trim($_POST['followup_comments']) : "";
  $current_date = date("Y-m-d"); // Example output: 2025-03-05
  $manager_id = isset($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
  $user_role = null; // Default value

  if ($manager_id) {
    // Fetch manager's full name and user role based on manager_id
    $query = "SELECT fullname, user_role FROM tbl_admin WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $manager_id);
    $stmt->execute();
    $stmt->bind_result($manager_name, $user_role);
    $stmt->fetch();
    $stmt->close();
  } else {
    $manager_name = "Unknown"; // Fallback if manager_id is not provided
    $user_role = "Unknown";
  }




  // Fetch manager full name based on manager_id
  $manager_name = "Unknown";
  if ($manager_id) {
    $query = "SELECT fullname FROM tbl_admin WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $manager_id);
    $stmt->execute();
    $stmt->bind_result($manager_name);
    $stmt->fetch();
    $stmt->close();
  }

  // Format new comment entry with a gap for better readability
  $new_followup_entry = "\n\n" . $current_date . " --- " . $comments . " --- " . $manager_name . "-" . $user_role;

  if ($project_id) {
    if ($table_id) {
      // If both project_id and table_id exist → Append comments in `subprojects`
      $query = "UPDATE subprojects SET comments = CONCAT(? , IFNULL(comments, '')) WHERE project_id = ? AND table_id = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("sii", $new_followup_entry, $project_id, $table_id);
    } else {
      // If only project_id exists → Append comments in `projects`
      $query = "UPDATE projects SET comments = CONCAT(? ,IFNULL(comments, '')) WHERE project_id = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("si", $new_followup_entry, $project_id);
    }

    if ($stmt->execute()) {
      echo "<script>
      alert('Comments updated successfully!');
      window.location.href = 'all-projects.php';
    </script>";

      exit();
    } else {
      echo "<script>alert('Error updating follow-up: " . $stmt->error . "');</script>";
    }
    $stmt->close();
  }
}
?>



<?php
if (isset($_GET['delete_project_id'])) {
  $delete_project_id = $_GET['delete_project_id'];

  // SQL query to delete the project
  $sql = "DELETE FROM projects WHERE project_id = $delete_project_id";

  if ($conn->query($sql) === TRUE) {
    // Display a success Toastr notification
    $msg_error = "Project Deleted Successfully";
    header('Location: ' . $_SERVER['PHP_SELF']);
  } else {
    // Display an error Toastr notification with the PHP error message
    $msg_error = "Error deleting the Project: ' . $conn->error . '";
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comments_form"])) {
  // Include database connection

  // Sanitize inputs
  $commentsForm = trim($_POST["comments_form"]);
  $projectIdForm = isset($_POST["revision_project_id"]) && !empty($_POST["revision_project_id"])
    ? intval($_POST["revision_project_id"])
    : intval($_POST["project_id_form"]);
  $reopenStatusForm = isset($_POST["reopen_status_form"]) ? $_POST["reopen_status_form"] : null;
  $subprojectStatusForm = isset($_POST["subproject_status_form"]) ? intval($_POST["subproject_status_form"]) : null;

  // Debug: Print out received POST values
  echo "commentsForm: " . $commentsForm . "<br>";
  echo "projectIdForm: " . $projectIdForm . "<br>";
  echo "reopenStatusForm: " . $reopenStatusForm . "<br>";
  echo "subprojectStatusForm: " . $subprojectStatusForm . "<br>";
  // exit;


  // Check if subproject_status exists for the given project
  $sqlCheck = "SELECT project_id, comments, subproject_status FROM subprojects WHERE project_id = ?";
  $stmtCheck = $conn->prepare($sqlCheck);

  if ($stmtCheck) {
    $stmtCheck->bind_param('i', $projectIdForm); // ✅ Correct binding
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $row = $resultCheck->fetch_assoc();
    $stmtCheck->close();

    if (!empty($subprojectStatusForm)) { // ✅ Fixed missing closing parenthesis
      // If subproject_status exists, update the subprojects table
      $sqlUpdate = "UPDATE subprojects SET comments = ? WHERE project_id = ? AND subproject_status = ?";
      $stmtUpdate = $conn->prepare($sqlUpdate);

      if ($stmtUpdate) {
        $stmtUpdate->bind_param('sii', $commentsForm, $projectIdForm, $subprojectStatusForm); // ✅ Corrected order

        if ($stmtUpdate->execute()) {
          $stmtUpdate->close();
          $conn->close();
          header('Location: openprojects.php');
          exit();
        } else {
          echo "Error updating subproject: " . $stmtUpdate->error;
        }
      } else {
        echo "SQL Error: " . $conn->error;
      }
    } else {
      // If no subproject_status, update the projects table
      $sqlUpdate = "UPDATE projects SET comments = ? WHERE (revision_project_id = ? AND reopen_status = ?) OR project_id = ?";
      $stmtUpdate = $conn->prepare($sqlUpdate);

      if ($stmtUpdate) {
        $stmtUpdate->bind_param('sisi', $commentsForm, $projectIdForm, $reopenStatusForm, $projectIdForm); // ✅ Fixed incorrect binding

        if ($stmtUpdate->execute()) {
          $stmtUpdate->close();
          $conn->close();
          header('Location: openprojects.php');
          exit();
        } else {
          echo "Error updating project: " . $stmtUpdate->error;
        }
      } else {
        echo "SQL Error: " . $conn->error;
      }
    }
  } else {
    echo "SQL Error: " . $conn->error;
  }

  $conn->close();

  // Redirect only if no output is sent
  if (!headers_sent()) {
    header('Location: all-projects.php');
    exit();
  }
}



if (isset($_GET['delete_SubProject_id'])) {
  $delete_SubProject_id = intval($_GET['delete_SubProject_id']); // Sanitize input

  // Use prepared statements to prevent SQL injection
  $stmt = $conn->prepare("DELETE FROM subprojects WHERE table_id = ?");
  $stmt->bind_param("i", $delete_SubProject_id);

  if ($stmt->execute()) {
    $msg_error = "Subproject Deleted Successfully";
    header('Location: ' . $_SERVER['PHP_SELF']);
  } else {
    $msg_error = "Error deleting the Subproject: " . $stmt->error;
  }
  $stmt->close();
}


if (isset($_POST["close_subproject"])) {
  $table_id = $_POST['table_id'];  // Access the table_id from POST

  // Ensure that the table_id is valid and exists in the database
  if ($table_id) {
    // SQL query to update the subproject status in the database
    $sql = "UPDATE subprojects SET mark_as_completed = 'Completed' WHERE table_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
      $stmt->bind_param("i", $table_id);
      $stmt->execute();

      if ($stmt->affected_rows > 0) {
        $msg_success = "Sub-Project Closed Successfully";
        // Instead of redirecting to the previous page, you might want to display success
        // header('Location: ' . $_SERVER['HTTP_REFERER']);
        // Or show a success message on the current page
        echo "Sub-Project Closed Successfully.";
      } else {
        // If no rows were affected, the subproject might already be closed or other issues
        echo "No changes were made. The subproject may have already been closed.";
      }
    } else {
      echo "Error preparing SQL: " . $conn->error;
    }
  } else {
    echo "Invalid table ID.";
  }
}

if (isset($_GET['send_back_project_id'])) {
  // Get the project ID from the URL parameter
  $project_id = $_GET["send_back_project_id"];

  // You can add additional validation and sanitation here

  // Get other data from the URL or from wherever you want
  $status_value = "0";
  $assign_status = 1;
  $assign_to_status = 1;
  $checker_status = 0;
  $project_manager_status = 0;


  // SQL query to insert data into the table
  // $sql = "UPDATE projects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL ,assign_status = ?, checker_status = ? , project_manager_status =?, assign_to_status = ? WHERE project_id = ?";
  // Prepare and execute the statement
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("isiiis", $status_value, $assign_status, $checker_status, $project_manager_status, $assign_to_status, $project_id);

  if ($stmt->execute()) {
    $msg_success = "Data Sent  successfully for Rework!";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  } else {
    $msg_error = "Error: " . $conn->error;
  }

  // Close the statement and connection
  $stmt->close();
  $conn->close();
}

if (isset($_GET['send_back_table_id'])) {
  // Get the project ID from the URL parameter
  $project_id = $_GET["send_back_table_id"];

  // You can add additional validation and sanitation here

  // Get other data from the URL or from wherever you want
  $status_value = "0";
  $assign_status = 1;
  $assign_to_status = 1;
  $checker_status = 0;
  $project_manager_status = 0;


  // SQL query to insert data into the table
  $sql = "UPDATE subprojects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL ,assign_status = ?, checker_status = ? , project_manager_status =?, assign_to_status = ? WHERE table_id = ?";
  // Prepare and execute the statement
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("isiiis", $status_value, $assign_status, $checker_status, $project_manager_status, $assign_to_status, $project_id);

  if ($stmt->execute()) {
    $msg_success = "Data Sent  successfully for Rework!";
    header('location:index.php');
  } else {
    $msg_error = "Error: " . $conn->error;
  }

  // Close the statement and connection
  $stmt->close();
  $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_GET['action']) && $_GET['action'] === 'assign_project') {
    if (isset($_POST["project_id"])) {
      $project_id = intval($_POST["project_id"]); // Get project_id from POST
      $status_value = 1;
      $verify_status = 0;
      $assign_status = 1;
      $project_manager_status = 0;
      $verify_by = NULL;
      $verify_by_name = NULL;

      $sql = "UPDATE projects SET assign_to_status = ?, verify_status = ?, project_manager_status = ?, verify_by = ?, verify_by_name = ?, assign_status = ? WHERE project_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iisssss", $status_value, $verify_status, $project_manager_status, $verify_by, $verify_by_name, $assign_status, $project_id);

      if ($stmt->execute()) {
        echo "<script>alert('Sent To Engineer successfully!');</script>";
        echo "<script>window.location.href = 'openprojects.php';</script>";
      } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
      }
    }
  } elseif (isset($_GET['action']) && $_GET['action'] === 'assign_subproject') {
    if (isset($_POST["table_id"])) {
      $table_id = intval($_POST["table_id"]); // Get subproject ID from POST
      $status_value = 1;
      $verify_status = 0;
      $assign_status = 1;
      $project_manager_status = 0;
      $verify_by = NULL;
      $verify_by_name = NULL;

      $sql = "UPDATE subprojects SET assign_to_status = ?, verify_status = ?, project_manager_status = ?, verify_by = ?, verify_by_name = ?, assign_status = ? WHERE table_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("iisssss", $status_value, $verify_status, $project_manager_status, $verify_by, $verify_by_name, $assign_status, $table_id);

      if ($stmt->execute()) {
        echo "<script>alert('Sent To Engineer successfully!');</script>";
        echo "<script>window.location.href = 'openprojects.php';</script>";
      } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
      }
    }
  }
}
?>
<?php include './include/footer.php' ?>
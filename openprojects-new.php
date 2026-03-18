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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';


include 'include/sidebar.php';


?>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
  integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
  crossorigin="anonymous"></script>


<script src="https://kit.fontawesome.com/26bcd7cc45.js" crossorigin="anonymous"></script>



<style>
  .custom-red {
    background-color: red;
    color: white;
    border-radius: 5px;
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

  .custom-blue {
    background: navy;
    color: white;
    border-radius: 5px;
  }

  .tr-anime:hover {
    background: lightcyan;
  }

  .subproject-anime {
    transition: all 0.3s ease-in-out;
  }

  .subproject-anime:hover {
    transform: scale(1.12);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  }


  .info-icon {
    transition: all 0.2s ease-in-out;

  }


  .info-icon:hover {
    transform: scale(1.4);

  }



  .project-management {
    font-size: 2.5rem;
    font-weight: bold;
    -webkit-text-stroke: 0.03rem teal;
    font-family: 'Helvetica Neue';

  }


  #customSearchInput {
    width: 20rem;
    height: 2.8rem;

    padding: 0.5rem;
    outline: none;
    border: 1px solid lightgray;
    background: rgb(228, 228, 228);
    border-radius: 0 8px 8px 0;

  }


  #customSearchInput:hover {
    outline: none;
    border: 5px solid lightgray;
  }

  /* .search-icon-container {
        background: rgb(19, 62, 135);
        padding-top: 0.3rem;
        padding-left: 0.8rem;
        height: 2rem;
        width: 2.5rem;
        border-radius: 0.7rem 0 0 0.7rem;
    } */


  .search-icon-container {
    background: #305595cd;

    height: 2.8rem;
    width: 2.8rem;
    padding: 0.6rem;
    border-radius: 8px 0 0 8px;

  }

  #customPageInput {
    height: 2.7rem;
    width: 4.5rem;
    display: flex;
    justify-content: center;

    padding: 0.5rem;
    outline: none;
    border: 1px solid lightgray;
    background: rgb(228, 228, 228);

  }

  #customPageInput:hover {
    outline: none;
    border: 4px solid lightgray;
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







  .fixedheader {
    position: sticky;
    z-index: 1;
    /* top: -0.3rem; */
    top: -0.2rem;
    background: linear-gradient(180deg, lightgray, rgb(193, 193, 193), lightgray) !important;
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

  .bg-teal {
    background: teal;
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


  .search-container-fixed {
    position: fixed;
    top: 1.5rem;
    background: gray;
    border: 3px solid #305595cd;
    border-radius: 12px;
    left: 35%;
    z-index: 10;
    height: 8.7rem;
    width: 30rem;
    background: white;
    padding-top: 1.5rem;
  }


  .pagination-container-custom {
    position: fixed;
    top: 1.5rem;
    background: gray;
    border: 3px solid #305595cd;
    border-radius: 12px;
    left: 40%;
    z-index: 10;
    height: 8.7rem;
    width: 18rem;
    background: white;
    padding-top: 1.5rem;
  }

  /* .info-table-container {
        overflow-y: auto;
        height: 140px;
    }


    .info-table-header {
        position: sticky;
        background:#133E87;
        z-index: 1;
        top: -0.2rem;
        color: white;
    }

    .payments-table-header {
        position: sticky;
        background: #133E87;
        z-index: 1;
        top: -0.2rem;
        color: white;
    } */


  li {
    list-style-type: none;
  }
</style>
<style>
  #project_status option[value="red"] {
    background-color: red;
    color: white;
    /* Ensure text is readable */
  }

  #project_status option[value="orange"] {
    background-color: orange;
    color: black;
  }

  #project_status option[value="white"] {
    background-color: white;
    color: black;
  }

  #project_status option[value="green"] {
    background-color: green;
    color: white;
  }

  #project_status option[value="navy"] {
    background-color: navy;
    color: white;
  }

  #project_status option[value="purple"] {
    background-color: purple;
    color: white;
  }
</style>





<div id="content-wrapper" class="d-flex flex-column">


  <!-- popup search bar custom -->

  <div id="popup-overlay" style="position:fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1">
  </div>

  <div id="project-search" style="">
    <div class="shadow-lg search-container-fixed">
      <div style="display: flex; justify-content: center; ">
        <div class="d-flex">
          <div class="search-icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
              stroke="white" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>

          </div>

          <input type="text" id="customSearchInput" placeholder="Search..." />



        </div>

      </div>
      <div class="d-flex justify-content-end mr-5 mt-1"> <button id="search-close-btn"
          class="btn btn-secondary"> Close</button></div>


    </div>

  </div>

  <div id="pagination-container-fixed">
    <div class="shadow-lg pagination-container-custom">
      <div style="display: flex; justify-content: center; ">
        <div class="d-flex">

          <input type="number" id="customPageInput" min="1" value="20">
          <p class="mb-1 ml-2 mt-3">entries per page</p>

        </div>

      </div>
      <div class="d-flex justify-content-end mt-4 mr-4"> <button id="pagination-close-btn"
          class="btn btn-secondary btn-sm"> Close</button></div>


    </div>

  </div>


  <div class="container-fluid p-0 m-0 vh-100">

    <div class=" mt-0">

      <div class="shadow-lg card border-0 " style="background: gainsboro" ;>

        <div class="table-container " style="max-height: 100vh; overflow-y: auto;">



          <!-- <table id="projectTable" class='m-3 table'> -->
          <table class="table text-center table-sm table-container" id="projectTable" width="100%" cellspacing="0">

            <thead>
              <tr>
                <th class="text-center align-middle"> ID</th>
                <th class="text-center align-middle">Project Title</th>
                <th class="text-center align-middle">%</th>
                <th class="text-center align-middle">Customer Id</th>
                <th class="text-center align-middle">Team</th>
                <th class="text-center align-middle">PM</th>
                <th class="text-center align-middle">Engineer</th>
                <th class="text-center align-middle">Checker</th>
                <th class="text-center align-middle">Hours</th>
                <th class="text-center align-middle">ECD</th>
                <th class="text-center align-middle">Action</th>
              </tr>
            </thead>
            <tbody style="background: rgb(245, 245, 245) ;">
              <!-- Data will load here dynamically -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
              <?php } ?>
              <?php if ($user_role == 1 or $user_role == 3) { ?>
                <button id="delete-project-button" type="button" class="btn btn-danger mb-2 mr-2">Delete Project</button>
              <?php } ?>
              <button id="view-project-button" class="view-project btn btn-secondary mb-2 mr-2" type="button">View Project</button>
              <?php if ($user_role == 1) { ?>
                <button type="button" class="btn btn-success mb-2 mr-2" id="verify-button">Assign Checker</button>
              <?php } ?>

              <?php if (($user_role == 1 || $user_role == 3)) { ?>
                <button type="button" id="Close-project" class="btn btn-danger mb-2 mr-2" data-bs-toggle="modal" data-bs-target="#status_model"> Mark Complete</button>
              <?php } ?>
              <?php if ($user_role == 2) { ?>

                <button id="send_to_pm" href="send_to_project_manager_sub_project.php?table_id=<?php echo $row['table_id']; ?>"
                  type="button" class="btn  btn-primary mb-2 mr-2	"
                  onclick="return confirm('Are you sure you want to send this to the Project Manager?');">
                  Send to PM
                </button>
              <?php } ?>
              <?php if (($user_role == 1 || $user_role == 3)) { ?>

                <button id="send_Back" name="send_back" data-toggle="modal" data-target="#yourModalID" class="btn btn-warning mb-2 mr-2">Send To Engineer</button>
              <?php } ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="status_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
    <div class="modal fade" id="assign_to_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
      document.addEventListener("DOMContentLoaded", function() {
        const assignModal = document.getElementById("assign_to_status");

        assignModal.addEventListener("show.bs.modal", function(event) {
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
      document.addEventListener("DOMContentLoaded", function() {
        const exampleModal = document.getElementById("exampleModal");

        exampleModal.addEventListener("show.bs.modal", function(event) {
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
          if (closeProjectButton) closeProjectButton.onclick = function() {
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
            sendBack.onclick = function() {
              window.location.href = subproject === 'null' ?
                `?send_back_project_id=${projectId}` :
                `?send_back_table_id=${tableId}`;
            }
          }


          const SendPm = modal.querySelector("#send_to_pm");
          if (SendPm) {
            SendPm.onclick = function() {
              window.location.href = subproject === 'null' ?
                `send_to_project_manager.php?project_id=${projectId}` :
                `send_to_project_manager_sub_project.php?table_id=${tableId}`;
            }
          };
          // Set additional dynamic button actions
          const uploadButton = modal.querySelector("#upload-button");
          uploadButton.onclick = function() {
            window.location.href = subproject === 'null' ?
              `googledrive_upload.php?project_id=${projectId}` :
              `engineeringData_sub_project.php?table_id=${tableId}`;
          };

          const updateButton = modal.querySelector("#update-project-button") ? modal.querySelector("#update-project-button") : '';
          updateButton.onclick = function() {
            window.location.href = subproject === 'null' ?
              `edit-project.php?project_id=${projectId}` :
              `edit-subproject.php?table_id=${tableId}`;
          };


          const viewButton = modal.querySelector("#view-project-button");
          viewButton.onclick = function() {
            window.location.href = subproject === 'null' ?
              `task-details.php?project_id=${projectId}` :
              `task-details_sub_project.php?table_id=${tableId}`;
          };

          const VerifyButton = modal.querySelector("#verify-button");
          if (VerifyButton) {
            VerifyButton.onclick = function() {
              window.location.href = subproject === 'null' ?
                `process_data-check.php?project_id=${projectId}` :
                `process_data-check_sub_project.php?table_id=${tableId}`;
            }
          };

          const deleteButton = modal.querySelector("#delete-project-button");
          if (deleteButton) {
            deleteButton.onclick = function() {
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

  <script>
    document.addEventListener("DOMContentLoaded", () => {

      let popupOverlay = document.getElementById("popup-overlay");

      let searchContainer = document.getElementById("project-search");
      let paginationContainer = document.getElementById("pagination-container-fixed");
      popupOverlay.style.display = "none"
      searchContainer.style.display = "none"

      paginationContainer.style.display = "none";











      document.getElementById("enter-search-keyword-btn").addEventListener("click", () => {
        searchContainer.style.display = "block"
        popupOverlay.style.display = "block";


      })


      document.getElementById("search-close-btn").addEventListener("click", () => {
        searchContainer.style.display = "none"


      })

      document.getElementById("pagination-btn").addEventListener("click", () => {
        paginationContainer.style.display = "block";
        popupOverlay.style.display = "block";
      })

      document.getElementById("pagination-close-btn").addEventListener("click", () => {
        paginationContainer.style.display = "none"
      })


      popupOverlay.addEventListener("click", () => {
        searchContainer.style.display = "none";
        paginationContainer.style.display = "none"
        popupOverlay.style.display = "none";



      })



    })















    $(document).ready(function() {
      var table = $('#sub-projects').DataTable({
        paging: true,
        pageLength: 100, // Default number of entries per page
        lengthMenu: [20, 40, 50, 100], // Custom pagination options
        searching: true, // Disable default search box
        ordering: true,
        dom: '<"top">rt<"fixed-pagination"p><"clear">'
      });

      // Search functionality
      $('#customSearchInput').on('keyup', function() {
        table.search(this.value).draw();
      });

      // Change entries per page
      $('#customPageInput').on('change', function() {
        var value = parseInt($(this).val(), 10);
        if (!isNaN(value) && value > 0) {
          table.page.len(value).draw();
        }
      });

      // Set initial input value based on DataTable default
      $('#customPageInput').val(table.page.len());
    });

    function updateUrgencyColor(select) {
      let selectedColor = select.value;
      let textColor = (selectedColor === 'white' || selectedColor === 'yellow') ? '#000' : '#fff';

      select.style.background = selectedColor;
      select.style.color = textColor;
    }
  </script>
  <script>
    // Fetch data from the PHP API asynchronously
    fetch('api.php') // Update with the correct API path
      .then(response => response.json())
      .then(data => {
        const tableBody = document.querySelector('#projectTable tbody');
        const userRole = <?php echo json_encode($user_role); ?>;
        const userId = <?php echo json_encode($user_id); ?>;

        data.forEach(row => {
          // Your row processing code...
          const tr = document.createElement('tr');
          tr.className = "p-0 m-0";

          const formattedECD = formatDateToDMY(row.setTargetDate);
          tr.innerHTML = `
       <td class="align-middle text-center py-0 my-0">${formattedECD}</td>
    `;
          tableBody.appendChild(tr);
          const urgencyColor = row.urgency || 'white'; // Default to 'white' if no urgency value
          const textColor = (urgencyColor === 'white' || urgencyColor === 'yellow') ? '#000' : '#fff';
          tr.innerHTML = `
					<td class="py-0 my-0" width=5% class='align-middle text-center' style="background-color: ${urgencyColor};  color: ${textColor};">
						${row.revision_project_id || row.project_id}
						
					</td>
					<td class="align-middle text-center py-0 my-0" >${row.subproject_status !== null ? row.subproject_name : row.project_name}</td>
					<td class="align-middle text-center py-0 my-0" >
						${row.subproject_status !== null ? `
							<div data-toggle="modal" data-target=".bd-progress-modal-sm_sp" href="javascript:void(0);" onclick="updateUrl_subproject(${row.table_id})">
								<p>${row.sub_progress}%</p>
							</div>
						` : `
							<div data-toggle="modal" data-target=".bd-progress-modal-sm" href="javascript:void(0);" onclick="updateUrl(${row.project_id})">
								<p>${row.progress}%</p>
							</div>
						`}
					</td>
					<td class="align-middle text-center py-0 my-0"  >${row.customer_id || 'N/A'}</td>
					<td class="align-middle text-center py-0 my-0" >${row.p_team || 'N/A'}</td>
					<td class="align-middle text-center py-0 my-0"  style="  ${row.project_manager_status === '1' ? 'border: solid orange;' : ''} ${row.project_manager_status === '3' ? 'background-color: purple; color: white;' : ''}">
						${formatProjectManagerName(row.project_manager)}
					</td>
          
					<td class="align-middle text-center py-0 my-0"  style="${row.assign_status === '1' ? 'border: 2px solid orange;' : ''}">${row.assign_to || 'N/A'}</td>
					<td class="align-middle text-center py-0 my-0"  style="${!row.verify_by_name || row.verify_by != userId ? '' : 'background-color: orange;'} ${row.checker_status == 1 ? 'border: orange solid;' : ''}">
					${row.verify_by_name || 'N/A'}
					</td>
					<td class="align-middle text-center py-0 my-0" >${row.EPT || 'N/A'}</td>
          <td class="align-middle text-center py-0 my-0">${formatDateToDMY(row.setTargetDate)}</td>

					<td width='10%' class="text-center align-middle py-0 my-0">
					
					</td>
				`;
          tableBody.appendChild(tr);
        });

        // Initialize DataTable after the rows are appended
        $(document).ready(function() {
          // Initialize DataTable
          var dataTable = $('#projectTable').DataTable({
            "pageLength": 25,
            "stateSave": true,
            "order": [
              [0, 'desc']
            ] // Orders the table by column 0 (project_id) in descending order
          });
          document.getElementById("apply-filter").addEventListener("click", function() {
            // Get the selected filter values
            var selectedProjectIdFilter = document.getElementById("project-id-filter").value;
            var selectedEngineerFilter = document.getElementById("engineer-filter").value;
            var selectedCustomerIdFilter = document.getElementById("customer-id-filter").value;
            var selectedTeamFilter = document.getElementById("team-filter").value;

            // Apply the project ID filter to the DataTable (assuming project_id is on column 0, change index if needed)
            dataTable.column(0).search(selectedProjectIdFilter).draw();

            // Apply the engineer filter to the DataTable (assuming engineer is on column 6, change index if needed)
            dataTable.column(6).search(selectedEngineerFilter).draw();

            // Apply the customer id filter to the DataTable (assuming customer_id is on column 3, change index if needed)
            dataTable.column(3).search(selectedCustomerIdFilter).draw();

            // Apply the team filter to the DataTable (assuming team is on column 4, change index if needed)
            dataTable.column(4).search(selectedTeamFilter).draw();

            // Close the filter modal
            $('#filter-modal').modal('hide');
          });
          // Search functionality
          $('#customSearchInput').on('keyup', function() {
            table.search(this.value).draw();
          });

          // Change entries per page
          $('#customPageInput').on('change', function() {
            var value = parseInt($(this).val(), 10);
            if (!isNaN(value) && value > 0) {
              table.page.len(value).draw();
            }
          });

          // Set initial input value based on DataTable default
          $('#customPageInput').val(table.page.len());
        });


      })
      .catch(error => console.error('Error fetching data:', error));

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

    document.addEventListener("DOMContentLoaded", function() {
      var applyFilterBtn = document.getElementById("apply-filter");
      var clearFilterBtn = document.getElementById("clear-filter");

      applyFilterBtn.addEventListener("click", function() {
        // Show the clear filter button when a filter is applied
        clearFilterBtn.style.display = "inline-block";
      });

      clearFilterBtn.addEventListener("click", function() {
        // Reset all filter inputs
        document.getElementById("project-id-filter").value = "";
        document.getElementById("engineer-filter").value = "";
        document.getElementById("customer-id-filter").value = "";
        document.getElementById("team-filter").value = "";

        // Clear filters in DataTable
        var dataTable = $('#projectTable').DataTable();
        dataTable.search("").columns().search("").draw();

        // Hide the clear filter button
        clearFilterBtn.style.display = "none";
      });
    });
  </script>
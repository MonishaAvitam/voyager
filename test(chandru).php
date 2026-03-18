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



<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
	rel="stylesheet"
	integrity="sha512-XMx6nxAO9VAIHvEipUzklxoRQ/BMQE7r3wRI7glOWz2P7RrtfbUzfHygNmA8yGvSOT0EHHGwxOLcEvnOsZK7Xg=="
	crossorigin="anonymous"
	referrerpolicy="no-referrer" />

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"
	integrity="sha384-oQOEJrv0D5F9Z0dRYgkVK3lkvB2UJt4h3vJTXkH/PfrxZW4HOxl7JQ5e2SOjHl0+"
	crossorigin="anonymous"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<style>
	table {
		width: 100%;
		border-collapse: collapse;
	}

	th,
	td {
		border: 1px solid #ccc;
		padding: 8px;
		text-align: left;
	}

	
</style>




<div class="container-fluid">
	<h1 class="h3 mb-0 ">Open Projects</h1>
	<div class="container-fluid m-5">
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
		</style>
		<div class="row justify-content-center text-center">
			<div class="col-md-2 border p-1  custom-red">Very Urgent</div>&nbsp;&nbsp;
			<div class="col-md-2 border p-1 custom-orange">Urgent</div>&nbsp;&nbsp;
			<div class="col-md-2 border p-1 custom-white">Don't Start the project</div>&nbsp;&nbsp;
			<div class="col-md-2 border p-1 custom-green">Ready to start the Project</div>&nbsp;&nbsp;
			<div class="col-md-2 border p-1 custom-purple">Closed</div>&nbsp;&nbsp;
		</div>
	</div>
	<!-- <table id="projectTable" class='m-3 table'> -->
	<table id="projectTable" class="table table-striped table-bordered table-sm" style="width:100%" cellspacing="0">

		<thead>
			<tr>
				<th> ID</th>
				<th>Project Title</th>
				<th>%</th>
				<th>Customer Id</th>
				<th>Team</th>
				<th>PM</th>
				<th>Engineer</th>
				<th>Checker</th>
				<th>Hours</th>
				<th>ECD</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			<!-- Data will load here dynamically -->
		</tbody>
	</table>


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
				const urgencyColor = row.urgency || 'white'; // Default to 'white' if no urgency value
				const textColor = (urgencyColor === 'white' || urgencyColor === 'yellow') ? '#000' : '#fff';
				tr.innerHTML = `
					<td class="align-middle" style="background-color: ${urgencyColor}; text-align: center; width: 5%; color: ${textColor};">
						${row.revision_project_id || row.project_id}
						${row.reopen_status ? `<span class="badge badge-pill badge-danger">${row.reopen_status}</span>` : ''}
						${row.subproject_status ? `<span class="badge badge-pill badge-danger">S${row.subproject_status}</span>` : ''}
					</td>
					<td style="width: 20%;" class="align-middle">${row.subproject_status !== null ? row.subproject_name : row.project_name}</td>
					<td class="align-middle text-center">
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
					<td class="align-middle" style="width: 5%;">${row.customer_id || 'N/A'}</td>
					<td class="align-middle">${row.p_team || 'N/A'}</td>
					<td class="align-middle" style="width: 5%; ${row.project_manager_status === '1' ? 'border: solid orange;' : ''} ${row.project_manager_status === '3' ? 'background-color: purple; color: white;' : ''}">
						${formatProjectManagerName(row.project_manager)}
					</td>
					<td class="align-middle" style="${row.assign_status === '1' ? 'border: 2px solid orange;' : ''}">${row.assign_to || 'N/A'}</td>
					<td class="align-middle" style="${!row.verify_by_name || row.verify_by != userId ? '' : 'background-color: orange;'} ${row.checker_status == 1 ? 'border: orange solid;' : ''}">
					${row.verify_by_name || 'N/A'}
					</td>
					<td class="align-middle">${row.EPT || 'N/A'}</td>
					<td class="align-middle">${row.setTargetDate || 'N/A'}</td>
					<td class="text-center align-middle">
					 ${userRole == 1 || userRole == 3 ? (row.assign_to_status == 3 ? `
                        <button 
                            type="button" 
                            title="Assign" 
							class="btn btn-sm btn-primary" 
                            name="assign_to_status" 
                            data-bs-table-id="${row.table_id}"  
                            data-bs-subproject-id="${row.subproject_status}" 
                            data-bs-project-id="${row.project_id}" 
                            class="btn btn-primary btn-sm" 
                            data-bs-target="#assign_to_status" 
                            data-bs-toggle="modal">
                            Assign
                        </button>` : '') : ''}	

						<button 
							type="button" 
							class="btn btn-sm btn-primary" 
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
					</td>
				`;
				tableBody.appendChild(tr);
			});

			// Initialize DataTable after the rows are appended
			$('#projectTable').DataTable({
				pageLength: 10 
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
</script>



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
	$sql = "UPDATE projects SET verify_status = ?, verify_by = NULL, verify_by_name = NULL ,assign_status = ?, checker_status = ? , project_manager_status =?, assign_to_status = ? WHERE project_id = ?";
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
			} else {
				echo "<script>alert('Error: " . $conn->error . "');</script>";
			}
		}
	}
}
?>
<?php include './include/footer.php' ?>
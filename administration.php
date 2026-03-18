<?php

require 'authentication.php'; // admin authentication check 
require 'conn.php';
include 'include/login_header.php';
// include 'include/loginHeaderForAdminPage.php';


// auth check
$current_user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$current_user_role = $_SESSION['user_role'];
$security_key = $_SESSION['security_key'];
if ($current_user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// check admin
$current_user_role = $_SESSION['user_role'];

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';
include 'include/welcomeTopBar.php';
include 'administrationBackend.php';
?>


<html>

<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>

  <style>
    /* Change background and text color for active tab */

    .nav-tabs .nav-link {
      border: none;

    }

    .nav-tabs .nav-link.active {
      border: none;
      border-bottom: 4px solid #0C6DFD;
      /* your color */
      color: black;
      /* text color */
      /* border-color: #4cafef #4cafef #fff;  */
    }

    /* Optional: Change hover for active tab */
    .nav-tabs .nav-link.active:hover {
      background-color: lightgray;
      color: black;
    }

    td {
      text-align: left
    }


    body {
      background-color: #f8f9fa;
    }

    .profile-card {
      /* max-width: 900px; */
      margin: auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
      padding: 20px;
    }

    .profile-pic {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #dee2e6;
    }

    .editable {
      cursor: pointer;
    }

    .editable i {
      margin-left: 5px;
      font-size: 0.9em;
      color: #6c757d;
    }

    .editable:hover i {
      color: #0d6efd;
    }





    /* Optional: nicer action buttons */
    .table-action-btn {
      border: none;
      background: transparent;
      color: #0d6efd;
      cursor: pointer;
      padding: 4px 8px;
    }

    .table-action-btn:hover {
      color: #084298;
    }





    .dataTables_filter {
      display: none !important
    }
  </style>

</head>

<body>



  <div class="container py-5 ">


    <div class="col-lg-12 d-flex justify-content-between align-items-center">
      <!-- Left side -->
      <div class="header mt-4 col-lg-6">
        <h1 class="text-capitalize">Hello <?php echo $user_name; ?></h1>
        <h5>Manage Apps</h5>
      </div>

      <!-- Right side -->
      <div class="col-lg-6 d-flex justify-content-end">
        <div class="mb-0 text-center">
          <button class="btn btn-primary d-flex align-items-center" onclick="window.location.href='allApps.php'">
            <i class="fas fa-arrow-left me-2"></i> Back
          </button>

        </div>
      </div>
    </div>



    <div class="mx-auto mt-3">
      <?php
      $stmt = $conn->prepare("SELECT tbl_admin.*,csa_finance_employee_info.doj from tbl_admin LEFT JOIN csa_finance_employee_info ON tbl_admin.user_id = csa_finance_employee_info.tbl_admin_id  WHERE user_id = ?");
      $stmt->bind_param("i", $current_user_id); // "i" means integer
      $stmt->execute();
      $result = $stmt->get_result();
      $currentUserRow = $result->fetch_assoc();
      ?>
      <div class="container mt-4 " style="width: 100%">
        <div class="profile-card p-4 " style="width: 95%">
          <div class="d-flex  justify-content-between mb-4">

            <div class="d-flex align-items-center justify-content-between mb-4 w-25">
              <img
                src="<?php echo !empty($currentUserRow['profile_pic']) ? $currentUserRow['profile_pic'] : './icons/user.png'; ?>"
                alt="Profile Picture" class="profile-pic me-4"
                style="width:100px; height:100px; object-fit:cover; border-radius:50%;">


         <div>
              <h4 class="mb-0 text-dark">My Profile</h4>
              <p class="text-muted "><?php echo 'RAE', $currentUserRow['user_id'] ?></p>
               <?php
                $office_id = $currentUserRow['office_id'];
                $officeSql = "SELECT office_name FROM office WHERE id = ?";
                $officeStmt = $conn->prepare($officeSql);
                $officeStmt->bind_param("i", $office_id);
                $officeStmt->execute();
                $officeResult = $officeStmt->get_result();

                if ($officeRow = $officeResult->fetch_assoc()) {
                  echo '<p class="text-muted">Office: ' . htmlspecialchars($officeRow['office_name']) . '</p>';
                } else {
                  echo '<p class="text-muted">Office: N/A</p>';
                }
                $officeStmt->close();
                ?>
              </div>
            </div>

            <div>
              <a class="btn btn-primary btn-sm" href="./assets/CSA_policies.pdf" download>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="white" width="20" height="20">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>

                View Company Policies

              </a>
            </div>
          </div>
          <form id="profile-update-form" method="POST" action="" enctype="multipart/form-data">
            <div class="row g-3 text-dark">


              <div class="col-md-6">
                <label class="form-label text-left">Full Name</label>
                <div class="input-group">
                  <input type="text" name="fullname" class="form-control editable editable-input"
                    value="<?php echo $currentUserRow['fullname']; ?>" id="editable-input-fullname">
                  <span class="input-group-text editable" id="edit-fullname"><i class="fas fa-pen"></i></span>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <div class="input-group">
                  <input type="email" name="email" class="form-control editable editable-input"
                    value="<?php echo $currentUserRow['email']; ?>" id="editable-input-email">
                  <span class="input-group-text editable" id="edit-email"><i class="fas fa-pen"></i></span>
                </div>
              </div>


<div class="col-md-6">
  <label class="form-label">Password</label>
  <div class="input-group">
    <input 
      type="password" 
      name="password" 
      class="form-control editable editable-input"
      value="<?php echo $currentUserRow['password']; ?>" 
      id="editable-input-password"
      minlength="6" 
      required
      placeholder="Enter at least 6 characters"
    >
    <span class="input-group-text editable" id="edit-password">
      <i class="fas fa-pen"></i>
    </span>
  </div>
</div>

<script>
const passwordField = document.getElementById("editable-input-password");

// When user types
passwordField.addEventListener("input", function () {
    if (passwordField.value.length > 0 && passwordField.value.length < 6) {
        passwordField.setCustomValidity("Your password must contain at least 6 characters.");
    } else {
        passwordField.setCustomValidity(""); // reset
    }
});
</script>









              <div class="col-md-6 text-left">
                <label class="form-label text-left">Date of Joining</label>
                <input type="text" class="form-control" value="<?php echo $currentUserRow['doj']; ?>" readonly>
              </div>




              <div class="col-md-12">
                <label class="form-label">Access List</label>
                <div class="d-flex gap-3">

                  <?php
                  echo $currentUserRow['raeAccess'] == 1 || $currentUserRow['raeAccess'] == 2 || $currentUserRow['raeAccess'] == 3 ? '<h4 class="badge px-2 font-weight-light bg-primary ">Rae</h4>' : "";
                  echo $currentUserRow['countAccess'] == 5 ? "<h4 class='badge px-2 font-weight-light bg-success'>Count</h4>" : "";


                  echo $currentUserRow['salesAccess'] == 1 ? "<h4 class='badge px-2 font-weight-light bg-info'>Obi</h4>" : "";


                  echo $currentUserRow['payslipAccess'] == 4 ? "<h4 class='badge px-2 font-weight-light bg-warning'>Binks</h4>" : "";


                  echo $currentUserRow['todoAccess'] == "Granted" ? "<h4 class='badge px-2 font-weight-light bg-secondary'>Skywalker</h4>" : "";
                  ?>

                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Team</label>
                <input type="text" class="form-control" value="<?php echo $currentUserRow['p_team']; ?>" readonly>
              </div>



              <div class="col-md-6">
                <label class="form-label">User Role</label>
                <input type="text" class="form-control" value="<?php switch ($currentUserRow['user_role']) {
                                                                  case 1:
                                                                    echo "Manager";
                                                                    break;
                                                                  case 2:
                                                                    echo "Engineer";
                                                                    break;
                                                                  case 3:
                                                                    echo "Team Lead";
                                                                    break;
                                                                  default:
                                                                    echo "<p class='text-danger'>NA</p>";
                                                                } ?>" readonly>
              </div>





              <div class="col-md-6">
                <label class="form-label">Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control">
              </div>

<div class="col-md-6">
    <label class="form-label text-left">Phone Number</label>
    <div class="input-group">
        <input type="text" 
            name="phone_number" 
            class="form-control editable editable-input"
            value="<?php echo htmlspecialchars($currentUserRow['phone_number'] ?? ''); ?>" 
            id="editable-input-phone-number">
        <span class="input-group-text editable" id="edit-phone-number">
            <i class="fas fa-pen"></i>
        </span>
    </div>
</div>




            </div>


            <div id="save-cancel-btn-container" style="display:none" class="justify-content-end mt-4">
              <button type="reset" class="btn btn-secondary me-2" id="cancel-edit-btn">Cancel</button>
              <button type="submit" name="update_profile" class="btn btn-success">Save Updates</button>


            </div>
          </form>

        </div>
      </div>
    </div>
    <?php
    $stmt = $conn->prepare("SELECT user_id,phone_number,  Isadmin FROM tbl_admin WHERE user_id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $userData = $result->fetch_assoc(); // <-- this is the variable with your data 
    $current_Isadmin = $userData['Isadmin']; // store role in variable

    //  


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    // ✅ Validate phone number
    // if (!preg_match("/^[6-9]\d{9}$/", $phone_number)) {
    //     die("Invalid phone number format! Must be 10 digits and start with 6-9.");
    // }

    $sql = "UPDATE tbl_admin SET fullname = ?, phone_number = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $fullname, $phone_number, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "✅ Updated successfully!";
    } else {
        echo "⚠️ No changes made!";
    }
}

    ?>
    <?php if ($current_Isadmin == 1) { ?>
      <div class="container mt-4  text-dark" style="width: 100%">
        <div class="profile-card p-4 " style="width: 95%">
          <h3 class="mb-4 mt-1 text-dark">Manage Users</h3>
          <div class="overflow-x-auto max-h-[70vh] table-responsive" id="table-container">
            <table class="table mt-2 align-middle mb-0" id="manage-users-table">
              <thead>
                <tr>
                  <th>S No</th>
                  <th>Rae ID</th>
                  <th>Fullname</th>
                  <th>Team</th>
                  <th>Email</th>
                  <th>User Role</th>
                  <th>Date of Joining</th>
                  <th>Access</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sql = "SELECT tbl_admin.*,csa_finance_employee_info.doj from tbl_admin LEFT JOIN csa_finance_employee_info ON tbl_admin.user_id = csa_finance_employee_info.tbl_admin_id  ORDER BY account_status DESC";
                $info = $obj_admin->manage_all_info($sql);
                $serial = 1;
                $num_row = $info->rowCount();

                if ($num_row == 0) {
                  echo '<tr><td colspan="10">No users found</td></tr>';
                }

                while ($row = $info->fetch(PDO::FETCH_ASSOC)) { ?>
                  <tr class="text-start">
                    <td><?= $serial++ ?></td>
                    <td><?= $row['user_id'] ?></td>
                    <td>
                      <?= $row['fullname'] ?>
                      <?= $row['account_status'] == 0 ? ' <br> <h4 class="badge mt-1 px-2 font-weight-light bg-danger text-uppercase">Suspended</h4>' : '' ?>
                    </td>
                    <td><?= !empty($row['p_team']) ? $row['p_team'] : "<p class='text-danger'>NA</p>"; ?></td>
                    <td><?= !empty($row['email']) ? $row['email'] : "<p class='text-danger'>NA</p>"; ?></td>
                    <td>
                      <?php
                      switch ($row['user_role']) {
                        case 1:
                          echo "Manager";
                          break;
                        case 2:
                          echo "Engineer";
                          break;
                        case 3:
                          echo "Team Lead";
                          break;
                        default:
                          echo "<p class='text-danger'>NA</p>";
                      }
                      ?>
                    </td>
                    <td><?= $row['doj'] ?></td>
                    <td>
                      <div class="h-4 my-auto d-flex gap-2 text-base">
                        <?php
                        if ($row['account_status'] == 0) {
                          echo "<p class='text-danger'>NA</p>";
                        } else {
                          echo $row['raeAccess']     ? '<h4 class="badge px-2 font-weight-light bg-primary">Rae</h4>' : "";
                          echo $row['countAccess']   ? "<h4 class='badge px-2 font-weight-light bg-success'>Count</h4>" : "";
                          echo $row['salesAccess']   ? "<h4 class='badge px-2 font-weight-light bg-info'>Obi</h4>" : "";
                          echo $row['payslipAccess'] ? "<h4 class='badge px-2 font-weight-light bg-warning'>Binks</h4>" : "";
                          echo $row['todoAccess']    ? "<h4 class='badge px-2 font-weight-light bg-secondary'>Skywalker</h4>" : "";
                        }
                        ?>
                      </div>
                    </td>

                    <td class="text-center">
                      <button type="button" class="btn btn-link p-0 edit-btn" data-bs-toggle="modal"
                        data-bs-target="#edit-user-form"
                        data-userid="<?= $row['user_id']; ?>"
                        data-fullname="<?= htmlspecialchars($row['fullname']); ?>"
                        data-team="<?= $row['p_team']; ?>"
                        data-email="<?= $row['email']; ?>"
                        data-userrole="<?= $row['user_role']; ?>"
                        data-accountstatus="<?= $row['account_status']; ?>"
                        data-raeaccess="<?= $row['raeAccess']; ?>"
                        data-countaccess="<?= $row['countAccess']; ?>"
                        data-obiaccess="<?= $row['salesAccess']; ?>"
                        data-binksaccess="<?= $row['payslipAccess']; ?>"
                        data-todoaccess="<?= $row['todoAccess']; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#017BFE" width="20" height="20">
                          <path
                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" />
                        </svg>
                      </button>

                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php } ?>

    <!-- ✅ One Modal Only -->
    <div class="modal fade" id="edit-user-form" tabindex="-1" role="dialog"
      aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

          <!-- Modal Header -->
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">
              Edit User Details
              <div id="hello-modal"></div>
            </h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Form -->
          <form method="post" id="edit-user-form-element">
            <div class="modal-body">
              <input type="hidden" name="user_id" id="user_id_hidden" />

              <!-- Fullname -->
              <div class="mb-3">
                <label for="fullname" class="form-label">Fullname</label>
                <input type="text" class="form-control" id="fullname" name="fullname">
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email">
              </div>

              <!-- Team -->
              <div class="mb-3">
                <label for="team" class="form-label">Team</label>
                <select class="form-select" id="team" name="team">
                  <option selected>Select Team</option>
                  <option value="Industrial">Industrial</option>
                  <option value="Building">Building</option>
                  <option value="IT">IT</option>
                  <option value="Operations">Operations</option>
                </select>
              </div>

              <!-- Role -->
              <div class="mb-3">
                <label for="role" class="form-label">User Role</label>
                <select class="form-select" id="role" name="user_role">
                  <option selected>Select Role</option>
                  <option value="2">Engineer</option>
                  <option value="3">Team Lead</option>
                  <option value="1">Manager</option>
                </select>
              </div>

              <!-- Access -->
              <div class="mb-3">
                <label class="form-label">Access List</label>
                <div class="d-flex flex-wrap gap-4 mt-3">
                  <div><input type="checkbox" id="rae-access" name="rae_access"> <label for="rae-access">Rae</label></div>
                  <div><input type="checkbox" id="count-access" name="count_access"> <label for="count-access">Count</label></div>
                  <div><input type="checkbox" id="obi-access" name="sales_access"> <label for="obi-access">Obi</label></div>
                  <div><input type="checkbox" id="binks-access" name="payslip_access"> <label for="binks-access">Binks</label></div>
                  <div><input type="checkbox" id="skywalker-access" name="todo_access"> <label for="skywalker-access">Skywalker</label></div>
                </div>
              </div>
            </div>
            <script>
              document.addEventListener("DOMContentLoaded", function() {
                const raeCheckbox = document.getElementById("rae-access");
                const roleSelect = document.getElementById("role");

                // Watch for changes on Rae checkbox
                raeCheckbox.addEventListener("change", function() {
                  if (!raeCheckbox.checked) {
                    roleSelect.value = ""; // reset to default (no role selected)
                  }
                });
              });
            </script>


            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-between">
              <div id="activate-suspend-btn-container"></div>
              <div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Save changes</button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>




  </div>





  </div>




  <script>
    // ===============================
    // Prefill modal + Suspend/Activate
    // ===============================
    document.getElementById('edit-user-form').addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;

      const userId = button.getAttribute('data-userid');
      const suspendStatus = button.getAttribute('data-accountstatus'); // "1" = active, "0" = suspended

      // Prefill form fields
      document.getElementById('user_id_hidden').value = userId;
      document.getElementById('fullname').value = button.getAttribute('data-fullname');
      document.getElementById('email').value = button.getAttribute('data-email');
      document.getElementById('team').value = button.getAttribute('data-team');
      document.getElementById('role').value = button.getAttribute('data-userrole');

      // Access checkboxes (all boolean)
      const raeValue = button.getAttribute('data-raeaccess');
      document.getElementById('rae-access').checked = ["1", "2", "3"].includes(raeValue);
      document.getElementById('count-access').checked = button.getAttribute('data-countaccess') == "5";
      document.getElementById('obi-access').checked = button.getAttribute('data-obiaccess') == "1";
      document.getElementById('binks-access').checked = button.getAttribute('data-binksaccess') == "4";
      document.getElementById('skywalker-access').checked = button.getAttribute('data-todoaccess') == "Granted";

      // Suspend/Activate button
      const container = document.getElementById('activate-suspend-btn-container');
      if (suspendStatus === "1") {
        container.innerHTML = `
      <button type="button" class="btn btn-danger btn-sm" onclick="toggleSuspend('${userId}', 0)">
        Suspend Account
      </button>
    `;
      } else {
        container.innerHTML = `
      <button type="button" class="btn btn-success btn-sm" onclick="toggleSuspend('${userId}', 1)">
        Activate Account
      </button>
    `;
      }
    });

    function toggleSuspend(userId, status) {
      const action = status === 1 ? "activate" : "suspend";
      if (!confirm(`Are you sure you want to ${action} this account?`)) return;
      window.location.href = `?toggle_user=${userId}&status=${status}`;
    }



    // ===============================
    // Save & restore scroll position
    // ===============================
    document.addEventListener("DOMContentLoaded", function() {
      const container = document.getElementById("table-container");
      let scrollKey = "scrollPos";

      if (container) {
        // Restore container scroll
        if (localStorage.getItem(scrollKey)) {
          container.scrollTop = localStorage.getItem(scrollKey);
        }
        window.addEventListener("beforeunload", function() {
          localStorage.setItem(scrollKey, container.scrollTop);
        });
      } else {
        // Restore page scroll
        if (localStorage.getItem("scrollPosition")) {
          window.scrollTo(0, parseInt(localStorage.getItem("scrollPosition")));
        }
        window.addEventListener("beforeunload", function() {
          localStorage.setItem("scrollPosition", window.scrollY);
        });
      }
    });

    // ===============================
    // Initialize DataTables
    // ===============================
    $(document).ready(function() {
      $('#manage-users-table').DataTable({
        stateSave: true,
        pageLength: 10
      });
    });

    // ===============================
    // Inline profile editing
    // ===============================
    document.addEventListener('DOMContentLoaded', function() {
      console.log("DOM loaded");

      const saveCancelContainer = document.getElementById('save-cancel-btn-container');
      const fullname = document.getElementById('editable-input-fullname');
      const email = document.getElementById('editable-input-email');
      const password = document.getElementById('editable-input-password');
      const cancelBtn = document.getElementById('cancel-edit-btn');
      const profilePicInput = document.querySelector('input[name="profile_pic"]'); // ✅ profile picture input

      // Hide initially
      saveCancelContainer.style.display = "none";

      // Store original values
      const originalValues = {
        fullname: fullname.value,
        email: email.value,
        password: password.value,
        phone_number: document.getElementById('editable-input-phone-number').value
      };

      // Enable editing when pencil clicked
      document.querySelectorAll('.editable').forEach(item => {
        item.addEventListener('click', function() {
          const targetId = this.id.replace('edit-', 'editable-input-');
          const inputField = document.getElementById(targetId);
          inputField.removeAttribute('readonly');
          inputField.focus();
          saveCancelContainer.style.display = 'flex';
        });
      });

      // Enable editing when input clicked/changed
      document.querySelectorAll('.editable-input').forEach(input => {
        input.addEventListener('click', function() {
          this.removeAttribute('readonly');
          saveCancelContainer.style.display = 'flex';
        });
        input.addEventListener('input', function() {
          saveCancelContainer.style.display = 'flex';
        });
      });

      // ✅ Show Save button when profile picture is selected
      if (profilePicInput) {
        profilePicInput.addEventListener('change', function() {
          if (this.files.length > 0) {
            saveCancelContainer.style.display = 'flex';
          }
        });
      }

      // Cancel button resets values + clears file input
      cancelBtn.addEventListener('click', function() {
        fullname.value = originalValues.fullname;
        email.value = originalValues.email;
        password.value = originalValues.password;
        document.getElementById('editable-input-phone-number').value = originalValues.phone_number;
        saveCancelContainer.style.display = 'none';
        document.querySelectorAll('.editable-input').forEach(input => {
          input.setAttribute('readonly', 'readonly');
        });
        if (profilePicInput) profilePicInput.value = ""; // ✅ clear selected file
      });

      // Form validation
      const profileForm = document.getElementById('profile-update-form');
      if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
          if (!fullname.value || !email.value || !password.value) {
            e.preventDefault();
            alert('Please fill in all fields');
            return false;
          }
        });
      }
    });
  </script>



  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


</body>





</html>



<?php
include 'include/footer.php';
?>
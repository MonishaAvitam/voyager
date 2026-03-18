<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';
include './include/sidebar.php';


// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
  exit();
}

// check admin
$user_role = $_SESSION['user_role'];

?>



<style>
  .project-enquiry-header {
    position: sticky;
    z-index: 1;
    top: -0.2rem;
    /* background: lightgray; */
    /* background: #1E88E5 !important; */
    font-weight: bold;
    font-size: 1rem;
    /* color: black; */
    color: white;
  }

  .table-container {
    max-height: 95vh;
    overflow-y: auto;
  }

  .table-container::-webkit-scrollbar {
    display: none;
  }


  .quotation-btn {
    height: 1.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    color: green;
    border: 1.5px solid green;
    transition: 0.3s ease-out;
  }



  .quotation-btn:hover {
    background: green;
    transform: scale(1.2);

    color: white;
  }

  .save-btn:hover {
    background: #0d6efd;
    transform: scale(1.2);

    color: white;
  }

  .save-btn {
    height: 1.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    color: #0d6efd;
    border: 1.5px solid #0d6efd;
    transition: 0.3s ease-out;
  }

  .comments-form {
    height: 1.8rem;
    width: 22rem;
    outline: none;
    border: 1px solid gainsboro;
    padding-left: 0.35rem;

  }

  /* .comments-form:hover {

    border: 1px solid slategray;
  } */

  .client-info-btn {
    height: 1.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: 0.3s ease-out;
    color: purple;
    border: 1.5px solid purple;
  }

  .client-info-btn:hover {
    background: purple;
    transform: scale(1.2);
    color: white;
  }

  .view-btn {
    height: 1.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: 0.3s ease-out;
    color: slategray;
    border: 1.5px solid slategray;
  }

  .view-btn:hover {
    background: slategray;
    transform: scale(1.2);
    color: white;
  }

  .custom-input-size {
    width: 65%
  }

  #view-modal textarea {
    height: 5rem;
  }

  .custom-header-font {
    font-family: 'Franklin Gothic Medium', '', Arial, sans-serif
  }

  .action-column svg:hover {
    transform: scale(1.4)
  }

  #customer-table,
  #customer-table th,
  #customer-table td {
    border-top: 1px solid lightgrey !important;
    border-bottom: 1px solid lightgrey !important;
    border-left: none !important;
    border-right: none !important;
  }

  /* .tippy-box[data-theme~='custom-tooltip'] {


border-radius: 10px;
padding: 10px 10px;
border: 1px solid white;
background-color: white;
color: slategray;

} */


  /* .tippy-box[data-theme~='custom-tooltip-sidebar'] {
background-color: black;
color: white;

padding: 10px 10px;


} */
</style>


<!-- Tippy.js CSS -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css">

<!-- Tippy.js JS -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>



<!-- <input type="text" class="customSearchInput" placeholder="Search..." id="customSearchInput" /> -->

<div class=" mx-auto" style="width: 98.5%">

  <div class="d-sm-flex align-items-center justify-content-between ">
    <p style="font-size: 1.7rem;" class="custom-header-font mb-2">Project Enquiry</p>

  </div>
</div>


<div class="table-container mx-auto" style="max-height: 100vh; overflow-y: auto; width: 98.5%; ">

  <table class="table text-center table-sm" id="customer-table">

    <!-- <thead class=" bg-gradient" style="background: teal; font-weight: bold; letter-spacing: 0.03rem; font-size: 1.3rem;"> -->
    <thead class="project-enquiry-header" style="" class="">
      <tr class="text-light align-middle font-weight-normal text-center project-enquiry-header bg-gradient"
        style="height: 3rem; background: #1E88E5;">

        <th width="6%" class="project-enquiry-header align-middle font-weight-normal text-center">
          Q Id </th>


        <th width="10%" style="padding-right: 1.8rem;"
          class="project-enquiry-header  align-middle font-weight-normal text-left">
          Last Followup</th>

        <th width="15%" class="project-enquiry-header  align-middle font-weight-normal text-center">
          Company Name </th>
        <!-- <th width="4%" class=" align-middle font-weight-normal text-center">
          Contact Person</th>


        <th width="5%" class=" align-middle font-weight-normal text-center">
          Client Id</th>

        <th width="5%%" class=" align-middle font-weight-normal text-center">
          Phone</th> -->

        <th width="20%" class="project-enquiry-header  align-middle font-weight-normal text-center">
          Project Name</th>
        <th width="10%" style="padding-right: 1.8rem;"
          class="project-enquiry-header align-middle font-weight-normal text-center">
          Enquiry Date</th>
        <!-- <th width="8%" style="padding-right: 1.8rem;"
                    class=" align-middle font-weight-normal text-center">
                     Enquiry Details</th> -->



        <!-- <th width="9%" style="padding-right: 1.8rem;"
          class=" align-middle font-weight-normal text-center">
          Amount </th> -->

        <th width="20%%" style="padding-right: 1.8rem;"
          class="project-enquiry-header align-middle font-weight-normal text-center">
          Comments</th>

        <th width="16%" style="padding-right: 1.8rem;"
          class="project-enquiry-header  align-middle font-weight-normal text-center">
          Actions</th>
      </tr>


    </thead>
    <tbody style="background: rgb(245, 245, 245) ;">

    </tbody>
  </table>

  <div class="mb-3 mr-4" style="display: inline-block;">
    <label for="comment_user_filter" id="comment_user_filter_wrapper" class="form-label mr-2">Filter :</label>
    <select name="comment_user_filter" id="comment_user_filter" class="form-select w-auto mr-4"
      style="display: inline-block;">
      <option value="">All Enquiries</option>
      <?php
      $sql = "SELECT DISTINCT fullname 
            FROM tbl_admin 
            WHERE user_role IN (1,2,3) 
            AND salesAccess = 1
            ORDER BY fullname";
      $info = $obj_admin->manage_all_info($sql);
      while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8') . '">'
          . htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8') . '</option>';
      }
      ?>
    </select>
  </div>


</div>



<!-- Quotation -->

<div class="modal fade" id="quotation_sent_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Quotation Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="quotationForm">
          <input type="hidden" id="sales_id" name="sales_id">

          <div class="mb-3">
            <label for="quote_sent_date" class="col-form-label">Quotation Sent Date:</label>
            <input type="date" class="form-control" id="quote_sent_date" name="quote_sent_date" required>
          </div>

          <div class="mb-3">
            <label for="quotation_amount" class="col-form-label">Amount:</label>
            <input type="text" class="form-control" id="quotation_amount" name="quotation_amount" required>
          </div>

          <div class="mb-3">
            <label for="comments" class="col-form-label">Comments:</label>
            <textarea readonly class="form-control" id="comments" name="comments" required></textarea>
          </div>

          <div class="mb-3">
            <label for="assign_to" class="col-form-label">Sales Manager:</label>
            <select class="form-control" name="assign_to" id="assign_to" required>
              <option value="">Select Sales Manager...</option>
              <?php
              $sql = "SELECT user_id, fullname FROM tbl_admin WHERE user_role IN ( 1,2,3)";
              $info = $obj_admin->manage_all_info($sql);
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
              }
              ?>
            </select>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Move to Sent Quotations</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    var quotationModal = document.getElementById('quotation_sent_modal');

    quotationModal.addEventListener('show.bs.modal', function (event) {
      var triggerElement = event.relatedTarget; // Element that triggered the modal
      var salesId = triggerElement.getAttribute('data-bs-id'); // Get the sales_id
      var comments = triggerElement.getAttribute('data-bs-comments'); // Get comments
      var date = triggerElement.getAttribute('data-date');


      // Populate modal fields
      document.getElementById('sales_id').value = salesId;
      document.getElementById('comments').value = comments;
      document.getElementById('quote_sent_date').value = date;
    });
  });
</script>

<div class="modal fade" id="client-info-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Client Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="quotationForm" method="post" action="">
          <input type="hidden" name="sales_id" id="sales_id_info">

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Company Name:</label>
            <input type="text" id="company_name" name="company_name" class="form-control custom-input-size"
              placeholder="Company Name">
          </div>

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Contact Name:</label>
            <input type="text" name="client_name" class="form-control custom-input-size" placeholder="Contact Name">
          </div>

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Contact Email:</label>
            <input type="email" id="email_id" name="email_id" class="form-control custom-input-size"
              placeholder="Contact Email">
          </div>

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Client ID:</label>
            <input type="text" name="client_id" class="form-control custom-input-size" placeholder="Client ID">
          </div>

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Company Address:</label>
            <input type="text" id="view_address" name="company_address" class="form-control custom-input-size"
              placeholder="Company Address">
          </div>

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Phone:</label>
            <input type="text" name="client_contact" class="form-control custom-input-size" placeholder="Phone">
          </div>

          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">More Details:</label>
            <textarea name="more_details" id="more_details" class="form-control custom-input-size"></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="info-update" class="btn btn-success">Update Info</button>
          </div>
        </form>


      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("click", function (event) {
    let infoClient = event.target.closest("#client-info-trigger");

    if (infoClient) {
      // Extract data attributes from the clicked element
      let srNum = infoClient.getAttribute("data-sr-num") || "";
      let companyName = infoClient.getAttribute("data-company") || "";
      let contactName = infoClient.getAttribute("data-contact_name") || "";
      let email = infoClient.getAttribute("data-email") || "";
      let clientID = infoClient.getAttribute("data-clientID") || "";
      let address = infoClient.getAttribute("data-address") || "";
      let phone = infoClient.getAttribute("data-phone") || "";
      let details = infoClient.getAttribute("data-details") || "";

      // Populate the modal inputs
      console.log(details);
      document.getElementById("sales_id_info").value = srNum;
      document.getElementById("company_name").value = companyName;
      document.querySelector("input[name='client_name']").value = contactName;
      document.getElementById("email_id").value = email;
      document.querySelector("input[name='client_id']").value = clientID;
      document.getElementById("view_address").value = address;
      document.querySelector("input[name='client_contact']").value = phone;
      document.getElementById("more_details").value = details;
    }
  });


</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    function fixModalBackdrop(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.addEventListener("hidden.bs.modal", function () {
          document.body.classList.remove("modal-open"); // Remove open class
          const modalsBackdrops = document.querySelectorAll(".modal-backdrop");
          modalsBackdrops.forEach((backdrop) => backdrop.remove()); // Remove all modal backdrops
        });
      }
    }

    // Apply the fix to both modals
    fixModalBackdrop("quotation_sent_modal");
    fixModalBackdrop("view-modal");
  });

</script>


<!-- View Modal -->
<div class="modal fade" id="view-modal-info" tabindex="-1" aria-labelledby="followupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Quotation Info - <span id="modal-sales-id"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-3">
        <form id="updateForm" action="" method="POST">
          <input type="hidden" name="sales_id" id="sales_id_info_p">
          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Enquiry Date:</label>
            <input type="date" name="quote_sent_date" id="amount" class="form-control custom-input-size"
              placeholder="Quotation Sent Date">
          </div>
          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Enquiry Details:</label>
            <textarea class="form-control custom-input-size" name="enquiry_details"></textarea>
          </div>
          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Sales Manager:</label>
            <select class="form-control custom-input-size" id="engineer" name="engineer">
              <option value="">Select Sales Manager</option>
              <?php
              require '../conn.php';
              $result = mysqli_query($conn, "SELECT user_id, fullname FROM tbl_admin");
              while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . htmlspecialchars($row['user_id']) . "'>" . htmlspecialchars($row['fullname']) . "</option>";
              }
              ?>
            </select>
          </div>


          <div class="mb-3 d-flex justify-content-between w-100">
            <label class="col-form-label">Comments:</label>
            <textarea class="form-control custom-input-size" readonly id="comments_info" name="comments"></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary bg-gradient btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="submit" name="info-update-p" class="btn btn-success bg-gradient btn-sm">Update Details</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener("click", function (event) {
    if (event.target.closest("#info-client")) {
      let infoClient = event.target.closest("#info-client");

      const srNum = infoClient.getAttribute("data-sr-num") || "N/A";
      const sentDate = infoClient.getAttribute("data-sent_date") || "";
      const enquiry = infoClient.getAttribute("data-enquiry") || "";
      const manager = infoClient.getAttribute("data-manager") || "";
      const comments = infoClient.getAttribute("data-comments") || "";

      console.log(srNum);

      // Populate modal fields
      document.getElementById("sales_id_info_p").value = srNum;
      document.getElementById("amount").value = sentDate;
      document.querySelector("textarea[name='enquiry_details']").value = enquiry;
      document.getElementById("engineer").value = manager;
      document.getElementById('comments_info').value = comments;

      console.log(comments);
      console.log(document.querySelector("textarea[name='comments']"));


      // Update modal title with Sales ID
      document.getElementById("modal-sales-id").innerText = srNum;

      // Show the modal
      const modalElement = document.getElementById("view-modal-info");
      const modal = new bootstrap.Modal(modalElement);
      modal.show();
    }
  });

</script>



<div class="modal fade" id="followup-modal" tabindex="-1" aria-labelledby="followupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="followupModalLabel">
          Follow Up: <span id="followup-id"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="" method="post">
          <input type="hidden" id="follow_sales_id" name="sales_id">
          <input type="hidden" id="follow_revised_qt" name="revised_qt">
          <input type="hidden" id="client_time" name="client_time">

          <div class="mb-3">
            <label for="followup_comments" class="form-label">Follow Up</label>
            <textarea name="followup_comments" id="followup_comments" class="form-control" rows="4"></textarea>
          </div>
          <div class="mb-3">
            <label for="followup_comments" class="form-label">Sales Manager</label>
            <select class="form-control" id="manager_id" name="manager_id">
              <option value="">Select Sales Manager</option>
              <?php
              $result = mysqli_query($conn, "SELECT user_id, fullname FROM tbl_admin");
              while ($row = mysqli_fetch_assoc($result)) {
                $selected = ($row['user_id'] == $user_id) ? "selected" : ""; // Prefill selection
                echo "<option value='" . htmlspecialchars($row['user_id']) . "' $selected>" . htmlspecialchars($row['fullname']) . "</option>";
              }
              ?>
            </select>
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" name="update_followup" class="btn btn-primary">Save Changes</button>
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

      // Extract info from data-* attributes
      const salesId = button.getAttribute("data-id") || "";
      const revisedQt = button.getAttribute("data-revised") || "";
      const comments = button.getAttribute("data-comments") || "";
      const sr_num = button.getAttribute("data-sr_num") || "";

      // Update modal content
      document.getElementById("followup-id").textContent = sr_num;
      document.getElementById("follow_sales_id").value = salesId;
      document.getElementById("follow_revised_qt").value = revisedQt !== 'null' ? revisedQt : "";
      // document.getElementById("followup_comments").value = comments;
      document.getElementById("client_time").value = new Date().toISOString().slice(0, 10);





    });
  });
</script>

<script>


  // Fetch data and populate the table with debugging
  fetch('./fetch_project_records.php')
    .then(response => response.json())
    .then(data => {
      console.log("API Response Data:", data); // Debugging

      if (!data || typeof data !== 'object') {
        console.error("Invalid API response format");
        return;
      }

      const potentialProjects = data.potential_projects || []; // Get potential projects
      const tableBody = document.querySelector('#customer-table tbody');
      tableBody.innerHTML = ''; // Clear table

      if (potentialProjects.length === 0) {
        tableBody.innerHTML = "<tr><td colspan='10'>No data available</td></tr>";
        return;
      }

      potentialProjects.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
                   
                    <td class="text-center align-middle"> <span class=""> ${row.sr_num}</span></td>
                    <td class="text-left align-middle ">${row.quote_sent_date || 'N/A'}</td>

                                           <td class="text-left align-middle">${row.company_name || 'N/A'}</td>

                                                           
                       <td class="text-left align-middle">${row.project_name || 'N/A'}</td>
                    
                    <td class="text-left align-middle ">${row.quote_sent_date || 'N/A'}</td>

                    
                    
                                       

                    <td class=" "> <div class="d-flex align-middle justify-content-center"> <textarea class="comments-form w-100" readonly>${row.comments
            ? row.comments
              .trim() // Remove extra spaces
              .split("\n")
              .filter(comment => comment.trim() !== "") // Remove empty lines
              .map((comment, index) => {
                // Extract date from comment
                const match = comment.match(/^(\d{4}-\d{2}-\d{2}) - (.*?)(?=\s*---|$)/); // Match date and comment before `---`
                const nameMatch = comment.match(/---\s*(.+)/); // Extract name after `---`
                let result = '';
                if (match) {
                  const formattedDate = `Date: ${match[1].split("-").reverse().join("/")}`;
                  const commentText = match[2].trim();
                  result = index === 0
                    ? `${formattedDate}\n\n${commentText}`
                    : `${"-".repeat(103)}\n${formattedDate}\n\n${commentText}`;
                } else {
                  // If no date match, just return the comment as is
                  result = index === 0 ? comment.trim() : `${"-".repeat(103)}\n${comment.trim()}`;
                }

                // If a name match is found, append it after the comment text
                if (nameMatch) {
                  const name = nameMatch[1].trim();
                  result += `\n\n--${name}`; // Append the name with two new lines before it
                }

                return result; // Return the final formatted comment
              })
              .join("\n") // Join all comments into a single string
            : ""
          }</textarea> </div> </td>
                    <td class="align-middle ">
                    <div class="d-flex justify-content-center action-column">
                 <div  data-toggle="modal" id="follow-up" data-id="${row.sales_id}" data-comments="${row.comments}" data-sr_num="${row.sr_num}" data-bs-toggle="modal" data-bs-target="#followup-modal" data-revised="${row.revised_qt}"
                             >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="#0d6efd" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
</svg>
                 </div>

<div  data-bs-toggle="modal" id="info-client" data-bs-target="#view-modal-info" title="Info" data-sr-num="${row.sr_num}" data-sent_date="${row.quote_sent_date}" data-enquiry="${row.enquiry_details}" data-manager="${row.engineer}" data-comments="${row.comments}">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="gray" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
</svg>
</div>


<div data-bs-toggle="modal" id="client-info-trigger" data-bs-target="#client-info-modal" title="Client Info"
 data-sr-num="${row.sr_num}"  data-company="${row.company_name}" data-contact_name="${row.client_name}" data-email="${row.email_id}" data-clientID="${row.client}" data-address="${row.address}" data-phone="${row.client_contact}" data-details="${row.more_detials}">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="purple" width="20" height="20"">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
</svg>
</div>


<div title="Quotation Sent" data-bs-toggle="modal" 
     data-bs-target="#quotation_sent_modal"
                              data-bs-comments="${row.comments}"
                              data-engineer="${row.engineer}"
                              data-date="${row.quote_sent_date}"
                              data-bs-id="${row.sales_id}">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="green" width="20" height="20"">
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v7.5m2.25-6.466a9.016 9.016 0 0 0-3.461-.203c-.536.072-.974.478-1.021 1.017a4.559 4.559 0 0 0-.018.402c0 .464.336.844.775.994l2.95 1.012c.44.15.775.53.775.994 0 .136-.006.27-.018.402-.047.539-.485.945-1.021 1.017a9.077 9.077 0 0 1-3.461-.203M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
</svg>
</div>





                        </div>

                    </td>
                `;

        tableBody.appendChild(tr);
        tippy('[data-bs-toggle="tooltip"]', {
          allowHTML: true,
          placement: 'bottom',
          theme: 'custom-tooltip',
        });



      });
      // Attach event listeners for modals and buttons
      attachQuotationModalEvent();
      attachViewModalEvent();
      attachSaveCommentEvent();
      $(document).ready(function () {
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('client_name'); // Get the value of the 'client_name' parameter

        // Initialize DataTable
        const table = $('#customer-table').DataTable({
          paging: true,        // Enable pagination
          searching: true,     // Enable searching
          ordering: true,      // Enable sorting
          info: true,          // Display information about the table
          lengthChange: false, // Hide the length change option
          autoWidth: false     // Disable auto width for columns (optional)
        });

        // Move both dropdowns before the search box
        $("#customer-table_filter").prepend($("#admin_user"));
        $("#customer-table_filter").prepend($("#comment_user_filter"));
        $("#customer-table_filter").prepend($("#comment_user_filter_wrapper")); // <-- updated


        // Apply search query from URL if present
        if (searchQuery) {
          table.search(searchQuery).draw(); // Apply the search filter
        }

        // Filter table by selected admin
        $('#admin_user').on('change', function () {
          var selectedAdmin = $(this).val();
          if (selectedAdmin) {
            // Assuming admin name/ID is in column 0, adjust index if needed
            table.column(0).search(selectedAdmin).draw();
          } else {
            table.column(0).search('').draw(); // Reset column filter if no selection
          }
        });

        // Filter table by commenter
        $('#comment_user_filter').on('change', function () {
          var selectedCommenter = $(this).val();
          if (selectedCommenter) {
            // Search in the comments column (column 5) for the commenter's name
            table.column(5).search(selectedCommenter).draw();
          } else {
            table.column(5).search('').draw(); // Reset column filter if no selection
          }
        });
      });



    })

    .catch(error => console.error('Error fetching data:', error));




  // Function to handle "Quotation Sent" modal data passing
  function attachQuotationModalEvent() {
    document.querySelectorAll(".quotation-btn").forEach((button) => {
      button.addEventListener("click", function () {
        const raeNum = this.getAttribute("data-bs-id"); // Get RAE Number
        const comments = this.getAttribute('data-bs-comments');
        console.log("Selected RAE Number for Quotation Sent:", raeNum); // Debugging

        const modal = document.getElementById("quotation_sent_modal");
        const raeNumField = modal.querySelector("#sales_id");
        const CommentsField = modal.querySelector('#comments');

        CommentsField.value = comments;

        if (raeNumField) {
          raeNumField.value = raeNum;
        }

        // Open modal programmatically
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
      });
    });
  }

  // Function to handle the "View" button modal data passing
  function attachViewModalEvent() {
    document.querySelectorAll(".view-btn").forEach((button) => {
      button.addEventListener("click", function () {
        const raeNum = this.getAttribute("data-bs-id");
        const enquiryDetails =
          this.getAttribute("data-bs-enquiry") || "No details available.";

        console.log("Selected RAE Number for View:", raeNum);
        console.log("Enquiry Details:", enquiryDetails);

        document.getElementById("view_rae_num").innerText = raeNum;
        document.getElementById("view_enquiry_details").innerText =
          enquiryDetails;

        // Open the modal
        const viewModal = new bootstrap.Modal(
          document.getElementById("quotation_view_modal")
        );
        viewModal.show();
      });
    });
  }

  // Handle form submission for quotation
  document
    .getElementById("quotationForm")
    .addEventListener("submit", function (event) {
      event.preventDefault(); // Prevent default form submission

      const formData = new FormData(this);

      fetch("insert_quotation.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text()) // First, read as text instead of JSON
        .then((text) => {
          console.log("Raw Response:", text); // Debugging
          return JSON.parse(text); // Convert manually to JSON
        })
        .then((data) => {
          alert(data.message);
          if (data.success) {
            location.reload();
          }
        })
        .catch((error) => console.error("Error submitting form:", error));
    });

  // Function to attach save comment event listeners
  function attachSaveCommentEvent() {
    document.querySelectorAll(".save-btn").forEach((button) => {
      button.addEventListener("click", function () {
        const salesId = this.getAttribute("data-id"); // Get sales_id from button
        const row = this.closest("tr"); // Get the row containing the comment
        const commentField = row.querySelector(".comments-form"); // Get textarea

        if (!commentField) {
          console.error("Comment field not found.");
          return;
        }

        const updatedComment = commentField.value; // Get the updated comment

        saveCommentWithoutAjax(salesId, updatedComment);
      });
    });
  }

  function saveCommentWithoutAjax(salesId, comment) {
    // Create a hidden form element
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "update_project_comment.php";

    // Add sales_id field
    const salesIdInput = document.createElement("input");
    salesIdInput.type = "hidden";
    salesIdInput.name = "sales_id";
    salesIdInput.value = salesId;
    form.appendChild(salesIdInput);

    // Add comment field
    const commentInput = document.createElement("input");
    commentInput.type = "hidden";
    commentInput.name = "comments";
    commentInput.value = comment;
    form.appendChild(commentInput);

    // Append form to body and submit
    document.body.appendChild(form);
    form.submit();
  }






  //updation of comment section .
  document.addEventListener("click", function (event) {
    if (event.target.classList.contains("save-btn")) {
      const button = event.target;
      const row = button.closest("tr");
      const salesId = row.querySelector(".view-btn").getAttribute("data-id");
      const commentBox = row.querySelector(".comments-form");
      const newComment = commentBox.value.trim();

      if (newComment === "") {
        alert("Comment cannot be empty!");
        return;
      }
      fetch('update_project_comment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `sales_id=${salesId}&comments=${encodeURIComponent(newComment)}`
      })
        .then(response => response.json())
        .then(data => {
          console.log("Server Response:", data); // Debugging response
          if (data.success) {
            alert("Comment updated successfully!"); // need to fix ( message is not showing ) ,  "SyntaxError: Unexpected token '<', "  //<!DOCTYPE "... is not valid JSON
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            alert("Error updating comment.");
          }
        })
        .catch(error => console.error("Error:", error));

    }
  });








  //view 
  // error that need to fix : sales_id is not showing in the modal . 
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("#customer-table tbody").addEventListener("click", function (event) {
      if (event.target.classList.contains("view-btn")) {
        let salesId = event.target.getAttribute("data-id");

        console.log("Clicked button. Sales ID:", salesId); // Debugging


        if (!salesId) {
          console.error(" Error: Sales ID is missing!");
          return;
        }


        if (salesId) {
          let url = `fetch_enquiry_quotation_view.php?sales_id=${encodeURIComponent(salesId)}`;
          console.log("Fetching URL:", url); // Debugging

          fetch(url)
            .then(response => response.json())
            .then(data => {
              console.log("Fetched Data for Modal:", data); // Debugging

              if (data.error) {
                alert("Error: " + data.error);
                return;
              }

              // Populate modal fields
              document.querySelector("#modal-sales-id").textContent = data.sales_id;
              document.querySelector("#view-modal input#sales_id").value = data.sales_id || '';
              document.querySelector("#company_name").value = data.company_name || '';
              document.querySelector("#view-modal input[placeholder='Contact Name']").value = data.client_name || '';
              document.querySelector("#view-modal input[placeholder='Contact Email']").value = data.contact_email || '';
              document.querySelector("#view-modal input[placeholder='Company Address']").value = data.company_address || '';
              document.querySelector("#view-modal input[placeholder='Phone']").value = data.client_contact || '';
              document.querySelector("#view-modal input[placeholder='Quotation Sent Date']").value = data.quote_sent_date || '';
              document.querySelector("#view-modal textarea[name='enquiry_details']").value = data.enquiry_details || '';
              document.querySelector("#email_id").value = data.email_id || '';
              document.querySelector("#view_address").value = data.address || '';
              let engineerSelect = document.querySelector("#engineer");
              if (data.engineer) {
                let option = engineerSelect.querySelector(`option[value="${data.engineer}"]`);
                if (option) option.selected = true;
              }
              document.querySelector("#view-modal input[placeholder='Client']").value = data.client || '';
              document.querySelector("#view-modal textarea[name='comments']").value = data.comments || '';

              // Show the modal
              new bootstrap.Modal(document.getElementById("view-modal")).show();
            })
            .catch(error => {
              console.error("Error fetching quotation details:", error);
            });
        } else {
          console.error("Sales ID is missing!");
        }
      }
    });


  });



</script>


<?php

if (isset($_POST['info-update'])) {
  $sales_id = $_POST['sales_id'];
  $client_name = $_POST['client_name'];
  $client_contact = $_POST['client_contact'];
  $email_id = $_POST['email_id'];
  $company_name = $_POST['company_name'];
  $company_address = $_POST['company_address'];
  $client_id = $_POST['client_id'];
  $more_details = $_POST['more_details'];

  // Debugging: Print POST data
  // echo '<pre>';
  // print_r($_POST);
  // echo '</pre>';
  // exit; 

  // Prepare the SQL statement
  $query = "UPDATE potential_project 
              SET client_name = ?, client_contact = ?, email_id = ?, company_name = ?, address = ?, client = ?, more_detials = ?
              WHERE sr_num = ?";

  $stmt = mysqli_prepare($conn, $query);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssssss", $client_name, $client_contact, $email_id, $company_name, $company_address, $client_id, $more_details, $sales_id);

    if (mysqli_stmt_execute($stmt)) {
      echo "<script>alert('Details updated successfully!'); window.location.href='project_enquiry.php';</script>";
    } else {
      echo "<script>alert('Error updating details. Please try again.');</script>";
    }

    mysqli_stmt_close($stmt);
  } else {
    echo "<script>alert('Database error. Please try again.');</script>";
  }

  mysqli_close($conn);
}
?>

<?php

if (isset($_POST['info-update-p'])) {
  $sales_id = $_POST['sales_id'];
  $quote_sent_date = $_POST['quote_sent_date'];
  $enquiry_details = $_POST['enquiry_details'];
  $engineer = $_POST['engineer'];
  $comments = $_POST['comments'];

  // echo '<pre>';
  // echo print_r($_POST);
  // echo '</pre>';
  // exit;

  // Prepare the SQL statement
  $query = "UPDATE potential_project SET quote_sent_date = ?, enquiry_details = ?, engineer = ? WHERE sr_num = ?";
  $stmt = mysqli_prepare($conn, $query);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssss", $quote_sent_date, $enquiry_details, $engineer, $sales_id);

    if (mysqli_stmt_execute($stmt)) {
      echo "<script>alert('Details updated successfully!'); window.location.href='project_enquiry.php';</script>";
    } else {
      echo "<script>alert('Error updating details. Please try again.');</script>";
    }

    mysqli_stmt_close($stmt);
  } else {
    echo "<script>alert('Database error. Please try again.');</script>";
  }

  mysqli_close($conn);
}
?>

<?php
if (isset($_POST['update_followup'])) {
  $sales_id = $_POST['sales_id'];
  $revised_qt = $_POST['revised_qt'];
  $comments_followup = trim($_POST['followup_comments']); // Trim to remove unnecessary spaces
  $current_timestamp = isset($_POST['client_time']) ? $_POST['client_time'] : date("Y-m-d");
  $manager_id = $_POST['manager_id'];

  require '../conn.php'; // Ensure DB connection

  // Fetch Manager Name
  $full_name_query = "SELECT fullname FROM tbl_admin WHERE user_id=?";
  $stmt_full_name_query = $conn->prepare($full_name_query);
  $stmt_full_name_query->bind_param("s", $manager_id);
  $stmt_full_name_query->execute();
  $result_fullname = $stmt_full_name_query->get_result();
  $row = $result_fullname->fetch_assoc();
  $manager_name = $row['fullname'] ?? '';
  $stmt_full_name_query->close();

  // Fetch Existing Comments
  $fetch_query = "SELECT comments FROM `potential_project` WHERE `sales_id` = ? ";
  $fetch_stmt = $conn->prepare($fetch_query);
  $fetch_stmt->bind_param("s", $sales_id);
  $fetch_stmt->execute();
  $result = $fetch_stmt->get_result();
  $row = $result->fetch_assoc();
  $existing_comments = $row['comments'] ?? '';
  $fetch_stmt->close();

  // Append new comment if provided
  if (!empty($comments_followup)) {
    $new_comment = "$current_timestamp - $comments_followup---$manager_name";
    $comments = trim("$new_comment\n$existing_comments");
  } else {
    $comments = $existing_comments; // Keep previous comments if no new content
  }

  // Update query based on whether revised_qt exists
  if (!empty($revised_qt)) {
    $update_query = "UPDATE `potential_project` SET `comments` = ? WHERE `sales_id` = ? ";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ss", $comments, $sales_id);
  } else {
    $update_query = "UPDATE `potential_project` SET `comments` = ? WHERE `sales_id` = ? ";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ss", $comments, $sales_id);
  }

  // Execute the update statement
  if ($update_stmt->execute()) {
    echo "<script>
  alert('Follow-up updated successfully!');
  window.location.href = 'project_enquiry.php';
  </script>";
    exit();

  } else {
    echo "<script>alert('Error updating follow-up: " . $update_stmt->error . "');</script>";
  }

  $update_stmt->close();
}
?>


<?php
include 'include/footer.php';
?>
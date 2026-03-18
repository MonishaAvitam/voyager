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



<!DOCTYPE html>
<html lang="en">



<style>
    .accepted-quotations-header {
        position: sticky;
        z-index: 1;
        top: 0.2rem;
        /* background: lightgray; */
        font-weight: bold;
        font-size: 1rem;
        color: white;
    }

    .table-container {
        max-height: 95vh;
        overflow-y: auto;
    }

    .table-container::-webkit-scrollbar {
        display: none;
    }


    .info-icon {
        transition: all 0.2s ease-in-out;

    }


    .info-icon:hover {
        transform: scale(1.4);

    }

    .save-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: 0.3s ease-out;
        color: #0d6efd;
        border: 1.5px solid #0d6efd;
    }

    .save-btn:hover {
        background: #0d6efd;
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

    .custom-input-size {
        width: 65%
    }

    #view-modal textarea {
        height: 5rem;
    }

    .custom-header-font {
        font-family: 'Franklin Gothic Medium', '', Arial, sans-serif
    }

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

    .action-column svg:hover {
        transform: scale(1.4)
    }

    .submit-btn {

        height: 1.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: 0.3s ease-out;
        font-size: 0.8rem;


    }

    .custom-month-form {
        text-align: right;
        border-radius: 6px 0 0 6px;
        outline: none;
        border: 1px solid gainsboro;
        padding: 0 1rem;


    }



    .custom-month-form:hover {
        border: 1.5px solid gray
    }

    #customer-table,
    #customer-table th,
    #customer-table td {
        border-top: 1px solid lightgrey !important;
        border-bottom: 1px solid lightgrey !important;
        border-left: none !important;
        border-right: none !important;
    }
</style>

<div class="mx-auto" style="width: 98.5%">

    <div class="d-sm-flex align-items-center justify-content-between mb-0">
        <p style="font-size: 1.7rem; " class="custom-header-font mb-0">Accepted Quotations</p>
    </div>

    <div class="d-sm-flex align-items-center justify-content-between ">
        <!-- Left Side: Showing Data Text -->
        <div class="">
            <p id="headerText" class="mb-0 fs-5 text-secondary" style="font-size: 0.95rem;">Showing All Data</p>
        </div>

        <!-- Right Side: Filter Form -->
        <div class="col-md-4 text-end ">
            <form id="filterForm" onsubmit="fetchData(event)">
                <label for="selectedDate" class="form-label text-secondary" style="font-size: 0.85rem;">Select Month and
                    Year:</label>
                <div class="input-group d-flex justify-content-end">
                    <input type="month" class="custom-month-form" name="selectedDate" id="selectedDate" style="">
                    <button class="bg-gradient btn-primary btn-sm btn submit-btn" style="" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

</div>



<div class="table-container mx-auto mt-2" style="max-height: 100vh; overflow-y: auto; width: 98.5%; ">
    <table class="table text-center table-sm" id="customer-table">
        <!-- <thead class=" bg-gradient" style="background: teal; font-weight: bold; letter-spacing: 0.03rem; font-size: 1.3rem;"> -->
        <thead class="accepted-quotations-header" style="" class="">
            <tr class="text-light align-middle font-weight-normal text-center accepted-quotations-header bg-gradient"
                style="height: 3rem; background: #7CB342">
                <th width="6%" class="accepted-quotations-header align-middle font-weight-normal text-center">
                    Q Id </th>
                <th width="18%" class="accepted-quotations-header align-middle font-weight-normal text-center">
                    Company Name </th>


                <th width="20%" class="accepted-quotations-header align-middle font-weight-normal text-center">
                    Project Name</th>

                <th width="10%" style="" class="accepted-quotations-header align-middle font-weight-normal text-center">
                    Accepted Date</th>

                <!-- <th width="8%" class="accepted-quotations-header align-middle font-weight-normal text-center">
                    Enquiry Date </th> -->


                <th width="7%" class="accepted-quotations-header align-middle font-weight-normal text-center">Amount
                </th>

                <th width="20%" style="" class="accepted-quotations-header align-middle font-weight-normal text-center">
                    Comments</th>

                <th width="12%" style="" class="accepted-quotations-header align-middle font-weight-normal text-center">
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


    <!-- View Modal -->


    <div class="modal fade" id="view-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Accepted Quotation Info: </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-3">
                    <form action="" method="POST">
                        <input type="hidden" id="sales_id_view" name="sales_id">
                        <input type="hidden" id="revised_qt_view" name="revised_qt">
                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Company Name:</label>
                            <input type="text" id="company_name_info" name="company_name"
                                class="form-control custom-input-size" placeholder="Company Name">
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Quotation Sent Date:</label>
                            <input type="date" id="quote_sent_date" name="quote_sent_date"
                                class="form-control custom-input-size" placeholder="Quotation Sent Date">
                        </div>
                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Quotation Amount:</label>
                            <input type="text" name="amount" class="form-control custom-input-size"
                                placeholder="Quotation Amount">
                        </div>
                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Quotation Accepted Date:</label>
                            <input type="date" id="accepted_date" name="accepted_date"
                                class="form-control custom-input-size">
                        </div>



                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Sales Manager:</label>
                            <select class="form-control custom-input-size" id="engineer" name="engineer">
                                <option value="">Select Sales Manager</option>
                                <?php
                                require '../conn.php';
                                $result = mysqli_query($conn, "SELECT fullname FROM tbl_admin");
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . htmlspecialchars($row['fullname']) . "'>" . htmlspecialchars($row['fullname']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>




                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Comments:</label>
                            <textarea class="form-control custom-input-size" id="comments-view-modal"
                                readonly></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary bg-gradient btn-sm"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="accept-update" class="btn btn-success bg-gradient btn-sm">Update
                        Details</button>
                </div>
                </form>
            </div>


        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.addEventListener("click", function (event) {
                const target = event.target.closest("[data-bs-target='#view-modal']"); // Find the clicked button

                if (!target) return; // Exit if the clicked element is not the expected one

                // Retrieve data attributes from the clicked element
                const salesId = target.getAttribute("data-sales-id") || "";
                const revisedQt = target.getAttribute("data-revised") || "";
                const companyName = target.getAttribute("data-company") || "";
                const quoteSentDate = target.getAttribute("data-sent_date") || "";
                const amount = target.getAttribute("data-amount") || "";
                const manager = target.getAttribute("data-manager") || "";
                const comments = target.getAttribute("data-comments") || "";
                const acceptDate = target.getAttribute("data-accept");

                // Populate modal inputs
                document.getElementById("sales_id_view").value = salesId;
                document.getElementById("revised_qt_view").value = revisedQt !== 'null' ? revisedQt : "";
                document.getElementById("company_name_info").value = companyName;
                document.getElementById("quote_sent_date").value = quoteSentDate;
                document.querySelector("input[name='amount']").value = amount;
                document.getElementById("engineer").value = manager;
                document.getElementById("comments-view-modal").value = comments;
                document.getElementById('accepted_date').value = acceptDate;
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
                    <form action="" method="post">
                        <input type="hidden" id="sales_id_client_ac" name="sales_id">
                        <input type="hidden" id="revised_qt_ac" name="revised_qt">

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Company Name:</label>
                            <input type="text" id="company_name" name="company_name"
                                class="form-control custom-input-size" placeholder="Company Name">
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Client Id:</label>
                            <input type="text" id="client_id" name="client" class="form-control custom-input-size"
                                placeholder="Client">
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Contact Name:</label>
                            <input type="text" id="client_name" name="client_name"
                                class="form-control custom-input-size" placeholder="Contact Name">
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Contact Email:</label>
                            <input type="email" id="email_id" name="contact_email"
                                class="form-control custom-input-size" placeholder="Contact Email">
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Company Address:</label>
                            <textarea id="view_address" name="company_address" class="form-control custom-input-size"
                                placeholder="Company Address"></textarea>
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">Phone:</label>
                            <input type="text" id="client_contact" name="client_contact"
                                class="form-control custom-input-size" placeholder="Phone">
                        </div>

                        <div class="mb-3 d-flex justify-content-between w-100">
                            <label class="col-form-label">More Details:</label>
                            <textarea id="more_details" name="more_details" class="form-control custom-input-size"
                                placeholder="Enter Details..."></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary bg-gradient"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="client_info_ac" class="btn btn-success bg-gradient">Update
                                Info</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <script>
        document.addEventListener("click", function (event) {
            // Check if the clicked element or its parent has the data-bs-target attribute for the client modal
            var button = event.target.closest("[data-bs-target='#client-info-modal']");
            if (!button) return; // If not, exit the function

            // Get the modal
            var modal = document.querySelector("#client-info-modal");

            // Extract data attributes from the closest button
            var salesId = button.getAttribute("data-sales-id") || "";
            var revisedQt = button.getAttribute("data-revised") || "";
            var companyName = button.getAttribute("data-company") || "";
            var clientId = button.getAttribute("data-clientID") || "";
            var clientName = button.getAttribute("data-client-name") || "";
            var email = button.getAttribute("data-email") || "";
            var address = button.getAttribute("data-address") || "";
            var clientContact = button.getAttribute("data-client-contact") || "";
            var moreDetails = button.getAttribute("data-more-details") || "";

            console.log(moreDetails);

            // Fill the modal form fields with the extracted data
            modal.querySelector("#sales_id_client_ac").value = salesId;
            modal.querySelector("#revised_qt_ac").value = revisedQt !== 'null' ? revisedQt : "";
            modal.querySelector("#company_name").value = companyName;
            modal.querySelector("#client_id").value = clientId;
            modal.querySelector("input[name='client_name']").value = clientName;
            modal.querySelector("#email_id").value = email;
            modal.querySelector("#view_address").value = address;
            modal.querySelector("input[name='client_contact']").value = clientContact;
            modal.querySelector("#more_details").value = moreDetails;
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
                            <textarea name="followup_comments" id="followup_comments" class="form-control"
                                rows="4"></textarea>
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
                    <button type="button" class="btn btn-secondary bg-gradient" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_followup" class="btn btn-success bg-gradient">Save
                        Changes</button>
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

                // Update modal content
                document.getElementById("followup-id").textContent = salesId;
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
                console.log("API Response Data:", data);

                if (!data || typeof data !== 'object') {
                    console.error("Invalid API response format");
                    return;
                }

                const potentialProjects = data.accepted_quotations || [];

                const tableBody = document.querySelector('#customer-table tbody');
                tableBody.innerHTML = '';

                if (potentialProjects.length === 0) {
                    tableBody.innerHTML = "<tr><td colspan='10'>No data available</td></tr>";
                    return;
                }

                potentialProjects.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                <td class="text-center align-middle"> 
    <span class=""> ${row.sales_id} </span> 
    </br> 
    ${row.revised_qt ? `<span class="badge badge-danger" >${row.revised_qt}</span>` : ''}
</td>        <td class="text-left align-middle px-3">${row.company_name || 'N/A'}</td>
      
        <td class="text-left align-middle ">${row.project_name || 'N/A'}</td>
        <td class="text-left align-middle ">${row.accepted_date ? row.accepted_date : 'N/A'}</td>
        
        <td class="text-left align-middle ">${row.amount || 'N/A'}</td>
        <td class="align-middle ">
        <div class="d-flex align-middle justify-content-center">
        <textarea class="comments-form" readonly>${row.comments
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
                                            : `${"-".repeat(82)}\n${formattedDate}\n\n${commentText}`;
                                    } else {
                                        // If no date match, just return the comment as is
                                        result = index === 0 ? comment.trim() : `${"-".repeat(82)}\n${comment.trim()}`;
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
                        }</textarea>
                    </div>
                    </td>
        <td class="text-left align-middle action-column">
            <div class="d-flex align-middle justify-content-center">
<div title="Follow Up" id="follow-up" data-id="${row.sales_id}" data-comments="${row.comments}" data-bs-toggle="modal" data-bs-target="#followup-modal" data-revised="${row.revised_qt}">
 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="#0d6efd" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
</svg>
</div>
                <div type="button" title="Quotation Info" data-bs-toggle="modal" data-bs-target="#view-modal" data-id="${row.sales_id}" data-revised="${row.revised_qt}"
                 data-bs-toggle="modal" 
     data-bs-target="#view-modal-info" 
     title="Info" 
     data-sr-num="${row.sr_num}" 
     data-sales-id="${row.sales_id}"
     data-sent_date="${row.quote_sent_date}" 
     data-enquiry="${row.enquiry_details}" 
     data-manager="${row.engineer}" 
     data-accept="${row.accepted_date}"
     data-company="${row.company_name}"
     data-amount="${row.amount}"
     data-revised="${row.revised_qt}"
     data-comments='${row.comments}'>
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="gray" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
</svg>
                </div>

                   <div title="Client Info" data-bs-toggle="modal" data-bs-target="#client-info-modal"
                        data-sales-id="${row.sales_id}"
                         data-revised="${row.revised_qt}"
                         data-company="${row.company_name}"
                         data-clientID = "${row.client}"
                         data-client-name = "${row.client_name}"
                         data-email="${row.email_id}"
                         data-address="${row.address}"
                         data-client-contact="${row.client_contact}"
                         data-more-details="${row.more_detials}">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="purple" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
</svg>
                       </div>
            </div>
        </td>
    `;
                   $(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('sales_id');

    const table = $('#customer-table').DataTable();

    // Add filter elements to the DataTable search area
    $("#customer-table_filter").prepend($("#admin_user"));
    $("#customer-table_filter").prepend($("#comment_user_filter"));
    $("#customer-table_filter").prepend($("#comment_user_filter_wrapper"));

    // If there's a search query in the URL, apply it to the DataTable search
    if (searchQuery) {
        table.search(searchQuery).draw();
        console.log("Applied URL search filter for sales_id:", searchQuery);
    }

    // Print all comments on page load
    table.rows().every(function (rowIdx, tableLoop, rowLoop) {
        const data = this.data();
        const comments = data[5]; // Comments column (index 5)
        console.log(`Row ${rowIdx} comments:\n`, comments);
    });

    // Add event listener for comment user filter
    $('#comment_user_filter').on('change', function () {
        const selectedUser = $(this).val();
        console.log("Filtering comments for user:", selectedUser);

        // Remove any existing custom filters to avoid stacking
        $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(f => !f.isCommentFilter);

        if (selectedUser) {
            const commentFilter = function (settings, data, dataIndex) {
                const comments = data[5]; // Comments column
                console.log(`Checking row ${dataIndex} comments:\n`, comments);

                // Match any number of hyphens before and after username, case-insensitive
                const regex = new RegExp(`-+\\s*${selectedUser.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')}\\s*-*`, 'i');
                const match = regex.test(comments);
                console.log(`Row ${dataIndex} match:`, match);
                return match;
            };
            commentFilter.isCommentFilter = true; // mark it so we can remove it later
            $.fn.dataTable.ext.search.push(commentFilter);
        }

        table.draw();
    });
});



                    tableBody.appendChild(tr);
                });


            })
            .catch(error => console.error('Error fetching data:', error));
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector("#customer-table tbody").addEventListener("click", function (event) {
                if (event.target.classList.contains("save-btn")) {
                    let row = event.target.closest("tr");
                    let salesId = event.target.getAttribute("data-id"); // Get sales_id from button
                    let revisedQt = event.target.getAttribute("data-revised"); // Get revised_qt from button
                    let comment = row.querySelector(".comments-form").value.trim(); // Get comments

                    if (salesId !== "") {
                        console.log("Sending data:", { sales_id: salesId, revised_qt: revisedQt, comment: comment });

                        fetch("update_accepted_comment.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `sales_id=${salesId}&revised_qt=${encodeURIComponent(revisedQt)}&comment=${encodeURIComponent(comment)}`
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log("Response from server:", data);
                                if (data.success) {
                                    alert("Comment updated successfully.");
                                } else {
                                    alert("Error: " + data.message);
                                }
                            })
                            .catch(error => {
                                console.error("Error updating comment:", error);
                            });
                    } else {
                        console.error("Sales ID is empty!");
                    }
                }
            });
        });


        //view 
        //wanna add the sales_id in the view modal ...
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector("#customer-table tbody").addEventListener("click", function (event) {
                if (event.target.classList.contains("view-btn")) {
                    let salesId = event.target.getAttribute("data-id");
                    let revisedID = event.target.getAttribute("data-revised");

                    if (salesId) {
                        // Build the API URL dynamically
                        let fetchUrl = `fetch_quotation_details.php?sales_id=${salesId}`;

                        if (revisedID && revisedID.trim() !== "" && revisedID !== "null") {
                            fetchUrl += `&revised_qt=${revisedID}`;
                        }

                        console.log("Fetching URL:", fetchUrl); // Debugging

                        fetch(fetchUrl)
                            .then(response => response.json())
                            .then(data => {
                                console.log("Fetched Data for Modal:", data);

                                if (data.error) {
                                    alert("Error: " + data.error);
                                    return;
                                }

                                // Populate modal fields
                                document.querySelector("#exampleModalLabel").textContent = `Accepted Quotation Info: ${data.sales_id}`;
                                document.querySelector("#sales_id").value = data.sales_id;
                                document.querySelector("#revised_qt").value = data.revised_qt;
                                document.querySelector("#view-modal input[placeholder='Company Name']").value = data.company_name || '';
                                document.querySelector("#view-modal input[placeholder='Contact Name']").value = data.client_name || '';
                                document.querySelector("#view-modal input[placeholder='Contact Email']").value = data.email_id || '';
                                document.querySelector("#view-modal input[placeholder='Company Address']").value = data.address || '';
                                document.querySelector("#view-modal input[placeholder='Phone']").value = data.client_contact || '';
                                document.querySelector("#view-modal input[placeholder='Quotation Sent Date']").value = data.quote_sent_date || '';
                                document.querySelector("#view-modal input[placeholder='Quotation Amount']").value = data.amount || '';
                                document.querySelector("#view-modal textarea[name='enquiry_details']").value = data.enquiry_details || '';
                                document.querySelector("#view-modal select[name='engineer']").value = data.engineer || '';
                                document.querySelector("#view-modal input[placeholder='Client']").value = data.client || '';
                                document.querySelector("#view-modal textarea[name='comments']").value = data.comments || '';


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


        document.getElementById('selectedDate').addEventListener('change', fetchData);

        //fetching for filter table 

        document.addEventListener("DOMContentLoaded", function () {
            fetchData(); // Fetch all data on page load
        });

        function fetchData(event) {
            if (event) event.preventDefault(); // Prevent default form submission

            const selectedDate = document.getElementById('selectedDate').value; // Get the selected date
            const formData = new FormData();

            if (selectedDate) {
                formData.append('selectedDate', selectedDate);
            }
            const tableBody = document.querySelector('#customer-table tbody');

            // **Clear previous table data before fetching new data**
            tableBody.innerHTML = "<tr><td colspan='12' style='text-align: center;'>Loading...</td></tr>";

            fetch('./filter_accpQuotations.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);

                    if (!data || typeof data !== 'object') {
                        console.error("Invalid API response format");
                        return;
                    }

                    const acceptedQuotations = data.accepted_quotations || [];
                    // const tableBody = document.querySelector('#customer-table tbody');
                    tableBody.innerHTML = ''; // Clear previous content

                    if (acceptedQuotations.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='10'>No data available</td></tr>";
                        updateHeaderText(selectedDate); // Update header even if no data
                        return;
                    }

                    acceptedQuotations.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                <td class="text-center align-middle"> 
    <span class=""> ${row.sales_id} </span> 
    </br> 
    ${row.revised_qt ? `<span class="badge badge-danger" >${row.revised_qt}</span>` : ''}
</td>        
                <td class="text-left align-middle px-3">${row.company_name || 'N/A'}</td>
                <td class="text-center align-middle">${row.project_name || 'N/A'}</td>
                <td class="text-left align-middle">${row.accepted_date ? row.accepted_date : 'N/A'}</td>
                <td class="text-left align-middle">${row.amount || 'N/A'}</td>
                <td class="text-left align-middle">
                    <textarea class="comments-form w-100" readonly>${row.comments
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
                                                : `${"-".repeat(82)}\n${formattedDate}\n\n${commentText}`;
                                        } else {
                                            // If no date match, just return the comment as is
                                            result = index === 0 ? comment.trim() : `${"-".repeat(82)}\n${comment.trim()}`;
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
                            }</textarea>
                </td>
            </td>
        <td class="text-left align-middle action-column">
            <div class="d-flex align-middle justify-content-center">
<div title="Follow Up" id="follow-up" data-id="${row.sales_id}" data-comments="${row.comments}" data-bs-toggle="modal" data-bs-target="#followup-modal" data-revised="${row.revised_qt}">
 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="#0d6efd" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
</svg>
</div>
                <div type="button" title="Quotation Info" data-bs-toggle="modal" data-bs-target="#view-modal" data-id="${row.sales_id}" data-revised="${row.revised_qt}"
                 data-bs-toggle="modal" 
     data-bs-target="#view-modal-info" 
     title="Info" 
     data-sr-num="${row.sr_num}" 
     data-sales-id="${row.sales_id}"
     data-sent_date="${row.quote_sent_date}" 
     data-enquiry="${row.enquiry_details}" 
     data-manager="${row.engineer}" 
     data-accept="${row.accepted_date}"
     data-company="${row.company_name}"
     data-amount="${row.amount}"
     data-revised="${row.revised_qt}"
     data-comments='${row.comments}'>
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="gray" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
</svg>
                </div>

                   <div title="Client Info" data-bs-toggle="modal" data-bs-target="#client-info-modal"
                        data-sales-id="${row.sales_id}"
                         data-revised="${row.revised_qt}"
                         data-company="${row.company_name}"
                         data-clientID = "${row.client}"
                         data-client-name = "${row.client_name}"
                         data-email="${row.email_id}"
                         data-address="${row.address}"
                         data-client-contact="${row.client_contact}"
                         data-more-details="${row.more_details}">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="purple" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
</svg>
                       </div>
            </div>
        </td>
            `;
                        tableBody.appendChild(tr);
                    });
                    // Update the text to show the selected month and year
                    updateHeaderText(selectedDate);
                })
                .catch(error => console.error('Error fetching data:', error));
        }
        // Function to update the header text dynamically
        function updateHeaderText(selectedDate) {
            const headerText = document.getElementById('headerText');
            if (selectedDate) {
                const [year, month] = selectedDate.split("-");
                const date = new Date(year, month - 1);
                const monthName = date.toLocaleString('default', { month: 'long' });
                headerText.innerHTML = `Showing Data for <span class="text-primary">${monthName} ${year}</span>`;
            } else {
                headerText.innerHTML = `Showing All Data`;
            }
        }
        // Load data on page load
        document.addEventListener("DOMContentLoaded", fetchData);
    </script>


    <?php

    if (isset($_POST["client_info_ac"])) {
        $sales_id = $_POST["sales_id"] ?? '';
        $revised_qt = $_POST["revised_qt"] ?? '';
        $company_name = $_POST["company_name"] ?? '';
        $client_name = $_POST["client_name"] ?? '';
        $client_id = $_POST["client"] ?? '';
        $email_id = $_POST["contact_email"] ?? '';
        $address = $_POST["company_address"] ?? '';
        $client_contact = $_POST["client_contact"] ?? '';
        $more_details = $_POST["more_details"] ?? '';

        // Ensure sales_id is provided
        if (empty($sales_id)) {
            echo "<script>alert('Error: Sales ID is required.');</script>";
            exit;
        }

        // echo '<pre>';
        // echo print_r($_POST);
        // echo '</pre>';
        // exit;
    
        // Prepare SQL query and bind parameters correctly
        if (!empty($revised_qt)) {
            $sql = "UPDATE accepted_quotations 
                SET company_name = ?, client_name = ?, client = ?, email_id = ?, address = ?, client_contact = ?, more_detials = ?
                WHERE sales_id = ? AND revised_qt = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssis", $company_name, $client_name, $client_id, $email_id, $address, $client_contact, $more_details, $sales_id, $revised_qt);
        } else {
            $sql = "UPDATE accepted_quotations 
                SET company_name = ?, client_name = ?, client = ?, email_id = ?, address = ?, client_contact = ?, more_detials = ?
                WHERE sales_id = ? AND revised_qt IS NULL";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssis", $company_name, $client_name, $client_id, $email_id, $address, $client_contact, $more_details, $sales_id);
        }

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Quotation details updated successfully.'); window.location.href='converted_projects.php';</script>";
        } else {
            echo "<script>alert('Error updating client info: " . mysqli_error($conn) . "');</script>";
        }

        // Close statement and connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
    ?>



    <?php

    if (isset($_POST['accept-update'])) {
        $sales_id = mysqli_real_escape_string($conn, $_POST['sales_id']);
        $revised_qt = isset($_POST['revised_qt']) ? mysqli_real_escape_string($conn, $_POST['revised_qt']) : "";
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
        $quote_sent_date = mysqli_real_escape_string($conn, $_POST['quote_sent_date']);
        $amount = mysqli_real_escape_string($conn, $_POST['amount']);
        $accepted_date = mysqli_real_escape_string($conn, $_POST['accepted_date']);
        $engineer = mysqli_real_escape_string($conn, $_POST['engineer']);


        // echo '<pre>';
        // echo print_r($_POST);
        // echo '</pre>';
        // // exit;
        // Update query based on whether revised_qt exists
        if (!empty($revised_qt)) {
            $sql = "UPDATE accepted_quotations 
                SET company_name='$company_name', 
                    quote_sent_date='$quote_sent_date', 
                    engineer='$engineer', 
                    amount='$amount', 
                    accepted_date='$accepted_date' 
                WHERE sales_id='$sales_id' AND revised_qt='$revised_qt'";
        } else {
            $sql = "UPDATE accepted_quotations 
                SET company_name='$company_name', 
                    quote_sent_date='$quote_sent_date', 
                    engineer='$engineer', 
                    amount='$amount', 
                    accepted_date='$accepted_date' 
                WHERE sales_id='$sales_id' AND revised_qt IS NULL";
        }

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Quotation details updated successfully.'); window.location.href='converted_projects.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
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

        // Prepare the new comment
        $new_comment = "$current_timestamp - $comments_followup---$manager_name";

        // Determine the comment update logic based on revised_qt presence
        if (!empty($revised_qt)) {
            // Fetch Existing Comments if revised_qt is provided
            $fetch_query = "SELECT comments FROM `accepted_quotations` WHERE `sales_id` = ? AND `revised_qt` = ?";
            $fetch_stmt = $conn->prepare($fetch_query);
            $fetch_stmt->bind_param("ss", $sales_id, $revised_qt);
            $fetch_stmt->execute();
            $result = $fetch_stmt->get_result();
            $row = $result->fetch_assoc();
            $existing_comments = $row['comments'] ?? '';

            // Append new comment to existing comments
            $comments = trim("$new_comment\n$existing_comments");

            // Update the comments in the database
            $update_query = "UPDATE `accepted_quotations` SET `comments` = ? WHERE `sales_id` = ? AND `revised_qt` = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sss", $comments, $sales_id, $revised_qt);

        } else {
            // Fetch Existing Comments if revised_qt is provided
            $fetch_query = "SELECT comments FROM `accepted_quotations` WHERE `sales_id` = ? ";
            $fetch_stmt = $conn->prepare($fetch_query);
            $fetch_stmt->bind_param("s", $sales_id, );
            $fetch_stmt->execute();
            $result = $fetch_stmt->get_result();
            $row = $result->fetch_assoc();
            $existing_comments = $row['comments'] ?? '';

            // Append new comment to existing comments
            $comments = trim("$new_comment\n$existing_comments");

            // Update the comments in the database
            $update_query = "UPDATE `accepted_quotations` SET `comments` = ? WHERE `sales_id` = ? AND `revised_qt` IS NULL";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $comments, $sales_id);
        }

        // Execute the update statement
        if ($update_stmt->execute()) {
            echo "<script>
        alert('Follow-up updated successfully!');
        window.location.href = 'converted_projects.php';
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
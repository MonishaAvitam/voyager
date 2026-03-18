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
    .sent-quotations-header {
        position: sticky;
        z-index: 1;
        top: 0.1rem;
        /* background: lightgray; */
        /* background: goldenrod !important; */

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


    .info-icon {
        transition: all 0.2s ease-in-out;

    }


    .info-icon:hover {
        transform: scale(1.4);

    }

    .accept-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: 0.3s ease-out;
        color: green;
        border: 1.5px solid green;
    }

    .accept-btn:hover {
        background: green;
        transform: scale(1.2);

        color: white;
    }

    .cancel-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: crimson;
        border: 1.5px solid crimson;
        transition: 0.3s ease-out;
    }

    .cancel-btn:hover {
        background: crimson;
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



    .save-btn:hover {
        background: #0d6efd;
        transform: scale(1.2);

        color: white;
    }

    .revise-btn {
        height: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        color: teal;
        border: 1.5px solid teal;
        transition: 0.3s ease-out;
    }



    .revise-btn:hover {
        background: teal;
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

    #customer-table,
    #customer-table th,
    #customer-table td {
        border-top: 1px solid lightgrey !important;
        border-bottom: 1px solid lightgrey !important;
        border-left: none !important;
        border-right: none !important;
    }


    /* .tippy-box[data-theme~='custom-tooltip'] {


border-radius: 50px;
padding: 10px 10px;
border: 1px solid white;
background-color: #133e87;
color: white;

}


.tippy-box[data-theme~='custom-tooltip-sidebar'] {
background-color: black;
color: white;

padding: 10px 10px;


} */
</style>



<!-- <input type="text" class="customSearchInput" placeholder="Search..." id="sent-quotations" /> -->

<div class=" mx-auto" style="width: 98.5% ">

    <div class="d-sm-flex align-items-center justify-content-between ">
        <p style="font-size: 1.7rem; " class="custom-header-font mb-0">Sent Quotations</p>


    </div>
</div>


<div class="table-container mx-auto " style="max-height: 100vh; overflow-y: auto; width: 98.5%; ">
    <table class="table text-center table-sm " id="customer-table">
        <!-- <thead class=" bg-gradient" style="background: teal; font-weight: bold; letter-spacing: 0.03rem; font-size: 1.3rem;"> -->
        <thead class="sent-quotations-header" style="" class="">

            <tr class="text-light bg-gradient align-middle font-weight-normal text-center sent-quotations-header bg-gradient"
                style="height: 3rem; background: goldenrod;">
                <th width="6%" class="sent-quotations-header align-middle font-weight-normal text-center">
                    Q Id </th>

                <th width="10%" style="padding-right: 1.8rem;"
                    class="sent-quotations-header align-middle font-weight-normal text-center">
                    Last Followup</th>

                <th width="15%" class="sent-quotations-header align-middle font-weight-normal text-center">
                    Company Name </th>
                <!-- <th width="6%" class="sent-quotations-header align-middle font-weight-normal text-center">
                    Contact Person</th>


                <th width="3%" class="sent-quotations-header align-middle font-weight-normal text-center">
                    Client Id</th>

                <th width="5%" class="sent-quotations-header align-middle font-weight-normal text-center">
                    Phone</th> -->

                <th width="15%" class="sent-quotations-header align-middle font-weight-normal text-center">
                    Project Name</th>

                <!-- <th width="6%" style="padding-right: 1.8rem;"
                    class="sent-quotations-header align-middle font-weight-normal text-center" title="Quotation Sent Date">
                    Q D</th> -->
                <!-- <th width="6%" style="padding-right: 1.8rem;"
                    class="sent-quotations-header align-middle font-weight-normal text-center" title="First Enquiry Date">
                    E D</th> -->



                <th width="7%" style="padding-right: 1.8rem;"
                    class="sent-quotations-header align-middle font-weight-normal text-center">
                    Amount</th>

                <th width="20%" style="padding-right: 1.8rem;"
                    class="sent-quotations-header align-middle font-weight-normal text-center">
                    Comments</th>

                <th width="20%" style="padding-right: 1.8rem;"
                    class="sent-quotations-header align-middle font-weight-normal text-center">
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


<!-- Revise Quotatation Modal -->

<div class="modal fade" id="revised-quotation-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Revised Quotation Details: SOO2</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="insert_revised_quotation.php" method="POST">
                    <input type="hidden" name="revisedQuotationRequest" value="1">

                    <input type="hidden" name="sales_id" id="hidden-sales-id">

                    <div class="mb-3">
                        <label for="quotation-Sent-Date" class="col-form-label">Revised Quotation Sent Date:</label>
                        <input type="date" class="form-control" name="quote_sent_date" id="quotation-Sent-Date"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="enquiry-details" class="col-form-label">Enquiry Details:</label>
                        <textarea class="form-control" name="enquiry_details" id="enquiry-details"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="quotation_amount" class="col-form-label">Amount:</label>
                        <input type="text" class="form-control" name="quotation_amount" id="quotation_amount" required>
                    </div>

                    <div class="mb-3">
                        <label for="comments1" class="col-form-label">Comments:</label>
                        <textarea class="form-control" readonly id="comments1"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary bg-gradient btn-sm"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success bg-gradient btn-sm">Add Revised Quotation</button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("revised-quotation-modal").addEventListener("show.bs.modal", event => {
            const salesId = event.relatedTarget.getAttribute("data-bs-id");
            const Enquiry = event.relatedTarget.getAttribute("data-bs-enquiry");
            const Amount = event.relatedTarget.getAttribute("data-bs-quotation_amount");
            const Comment = event.relatedTarget.getAttribute("data-bs-comment");
            const QuoteSentDate = event.relatedTarget.getAttribute("data-bs-quote_sent_date");

            console.log(Enquiry, Amount, Comment);
            document.querySelector("#revised-quotation-modal .modal-title").textContent = `Revised Quotation Details: ${salesId}`;
            document.getElementById("hidden-sales-id")?.setAttribute("value", salesId);
            document.getElementById("enquiry-details").value = Enquiry;
            document.getElementById("quotation_amount").value = Amount;
            document.getElementById("comments1").value = Comment;
            document.getElementById("quotation-Sent-Date").value = QuoteSentDate;
        });
    });
</script>



<!-- View Modal -->
<!-- Quotation Info Modal -->
<div class="modal fade" id="view-modal-info" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Quotation Info <span id="modal-sales-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-3">
                <form action="" method="post">
                    <input type="hidden" id="sales_id" name="sales_id">
                    <input type="hidden" id="revised_qt" name="revised_qt">

                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Quotation Sent Date:</label>
                        <input type="date" id="quote_sent_date" name="quote_sent_date"
                            class="form-control custom-input-size">
                    </div>

                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Quotation Amount:</label>
                        <input type="text" id="amount_info" name="quotation_amount"
                            class="form-control custom-input-size" placeholder="Quotation Amount">
                    </div>

                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Enquiry Details:</label>
                        <textarea class="form-control custom-input-size" id="enquiry_details_info"
                            name="enquiry_details"></textarea>
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
                        <textarea class="form-control custom-input-size" readonly id="comments"></textarea>
                    </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary bg-gradient btn-sm"
                    data-bs-dismiss="modal">Close</button>
                <button type="submit" name="quotation_info" class="btn btn-success bg-gradient btn-sm">Update
                    Details</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.body.addEventListener("click", function (event) {
            let target = event.target.closest("#info-client"); // Find closest parent with ID 'info-client'
            if (!target) return; // Exit if click is outside the target elements

            // Fetch data attributes from the clicked element
            let salesId = target.getAttribute("data-sales-id");
            let sentDate = target.getAttribute("data-sent_date");
            let enquiryDetails = target.getAttribute("data-enquiry");
            let manager = target.getAttribute("data-manager");
            let comments = target.getAttribute("data-comments");
            let amount = target.getAttribute("data-amount") || ""; // Ensure amount is not null
            let revised = target.getAttribute("data-revised");

            console.log("rn:", revised);

            // Populate modal fields with the fetched data
            document.getElementById("sales_id").value = salesId;
            document.getElementById("quote_sent_date").value = sentDate;
            document.getElementById("enquiry_details_info").value = enquiryDetails;
            document.getElementById("engineer").value = manager;
            document.getElementById("comments").value = comments;
            document.getElementById("amount_info").value = amount;
            document.getElementById("modal-sales-id").textContent = "(" + salesId + ")";
            document.getElementById("revised_qt").value = revised !== 'null' ? revised : "";
        });
    });

</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Select all div elements that trigger the modal
        document.querySelectorAll("div[data-bs-toggle='modal']").forEach(function (element) {
            element.addEventListener("click", function () {
                // Get the data attributes
                let salesId = element.getAttribute("data-id");
                let revisedQt = element.getAttribute("data-revised");

                // Set the values in the modal form fields
                document.getElementById("sales_id").value = salesId;
                document.getElementById("revised_qt").value = revisedQt;

                // Set sales ID in the modal title
                document.getElementById("modal-sales-id").textContent = `#${salesId}`;
            });
        });
    });

</script>


<div class="modal fade" id="confirmation-modal" tabindex="-1" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="confirmation-form" action="process_action.php" method="post">
                    <input type="hidden" name="action_type" id="action-type-input">
                    <input type="hidden" name="sales_id" id="sales-id-input">
                    <input type="hidden" name="revised_id" id="revised-id-input">

                    <p>Are you sure you want to <span id="modal-action-text" class="fw-bold"></span> this quotation?</p>
                    <p>Sales ID: <span id="sales-id-text" class="fw-bold"></span></p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn bg-gradient" id="confirm-action-btn" form="confirmation-form">Yes,
                    Proceed</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="client-info-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Client Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <input type="hidden" id="sales_id_client" name="sales_id">
                    <input type="hidden" name="revised_qt" id="revised_qt_client">
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Company Name:</label>
                        <input type="text" id="company_name" name="company_name" class="form-control custom-input-size"
                            placeholder="Company Name">
                    </div>
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Client Id:</label>
                        <input type="text" name="client" class="form-control custom-input-size" id="client_id"
                            placeholder="Client">
                    </div>
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Contact Name:</label>
                        <input type="text" name="client_name" class="form-control custom-input-size"
                            placeholder="Contact Name">
                    </div>
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Contact Email:</label>
                        <input type="text" id="email_id" name="contact_email" class="form-control custom-input-size"
                            placeholder="Contact Email">
                    </div>
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Company Address:</label>
                        <textarea type="text" id="view_address" name="company_address"
                            class="form-control custom-input-size" placeholder="Company Address"> </textarea>
                    </div>
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">Phone:</label>
                        <input type="text" name="client_contact" class="form-control custom-input-size"
                            placeholder="Phone">
                    </div>
                    <div class="mb-3 d-flex justify-content-between w-100">
                        <label class="col-form-label">More Details:</label>
                        <textarea name="more_details" id="more_details" class="form-control custom-input-size"
                            placeholder="Enter Details..."></textarea>
                    </div>



                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update-client-info" class="btn btn-success">Update Info</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.body.addEventListener("click", function (event) {
            let target = event.target.closest("div[title='Client Info']"); // Find the closest parent with the specific title
            if (!target) return; // Exit if the click is outside the target elements

            // Fetch data attributes from the clicked element
            let salesId = target.getAttribute("data-sales-id") || "";
            let revised = target.getAttribute("data-revised") || "";
            let company = target.getAttribute("data-company") || "";
            let clientId = target.getAttribute("data-clientid") || "";
            let clientName = target.getAttribute("data-name") || "";
            let email = target.getAttribute("data-email") || "";
            let address = target.getAttribute("data-address") || "";
            let phone = target.getAttribute("data-client") || "";
            let moreDetails = target.getAttribute("data-moredetails") || "";

            console.log(salesId);

            // Populate modal fields with the fetched data
            document.getElementById("sales_id_client").value = salesId;
            document.getElementById("revised_qt_client").value = revised !== 'null' ? revised : "";
            document.getElementById("company_name").value = company;
            document.getElementById("client_id").value = clientId;
            document.querySelector("input[name='client_name']").value = clientName;
            document.getElementById("email_id").value = email;
            document.getElementById("view_address").value = address;
            document.querySelector("input[name='client_contact']").value = phone;
            document.getElementById("more_details").value = moreDetails;
        });
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.addEventListener("click", function (event) {
            const target = event.target.closest("[data-bs-toggle='modal']"); // Ensure closest match
            if (!target) return; // Exit if click is outside modal trigger

            const action = target.getAttribute("data-action");
            const salesId = target.getAttribute("data-bs-id") || "Unknown";
            const revisedID = target.getAttribute("data-bs-revised_qt") || "";

            // Update modal title & action text
            document.getElementById("confirmationModalLabel").textContent = `Confirm ${action === "accept" ? "Acceptance" : "Cancellation"}`;
            document.getElementById("modal-action-text").textContent = action === "accept" ? "accept" : "cancel";
            document.getElementById("sales-id-text").textContent = salesId + (revisedID ? ` - ${revisedID}` : "");

            // Set input values for form submission
            document.getElementById("action-type-input").value = action;
            document.getElementById("sales-id-input").value = salesId;
            document.getElementById("revised-id-input").value = revisedID;

            // Update confirm button styling and text
            const confirmButton = document.getElementById("confirm-action-btn");
            confirmButton.className = `btn ${action === "accept" ? "btn-success" : "btn-danger"}`;
            confirmButton.textContent = `Yes, ${action === "accept" ? "Accept" : "Cancel"}`;
        });
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
            console.log("API Response Data:", data); // Debugging

            if (!data || typeof data !== 'object') {
                console.error("Invalid API response format");
                return;
            }

            const potentialProjects = data.sent_quotations || []; // Get potential projects
            const tableBody = document.querySelector('#customer-table tbody');
            tableBody.innerHTML = ''; // Clear table

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
    ${row.revised_qt ? `<span class="badge badge-danger">${row.revised_qt}</span>` : ''}
</td>

                                                  <td class="text-left align-middle ">${row.quote_sent_date || 'N/A'}</td>

                                                  <td class="text-left align-middle ">${row.company_name || 'N/A'}</td>

                    
                        <td class="text-left align-middle ">${row.project_name || 'N/A'}</td>

                          <td class="text-left align-middle ">${row.amount || 'N/A'}</td>
                        <td class=" "> <div class="d-flex align-middle justify-content-center"> <textarea class="comments-form w-100" readonly>${row.comments
                        ? row.comments
                            .trim() // Remove extra spaces
                            .split("\n")
                            .filter(comment => comment.trim() !== "") // Remove empty lines
                            .map((comment, index) => {
                                // Extract date from comment
                                const match = comment.match(/^(\d{4}-\d{2}-\d{2}) - (.*?)(?=\s*---|$)/); // Match date and comment before `---`
                                const nameMatch = comment.match(/---\s*(.+)/); // Extract name after `---`

                                // Initialize the result string
                                let result = '';

                                // If the date match is found
                                if (match) {
                                    const formattedDate = `Date: ${match[1].split("-").reverse().join("/")}`;
                                    const commentText = match[2].trim();

                                    // Create result string with date and comment
                                    result = index === 0
                                        ? `${formattedDate}\n\n${commentText}`
                                        : `${"-".repeat(108)}\n${formattedDate}\n\n${commentText}`;
                                } else {
                                    // If no date match, just return the comment as is
                                    result = index === 0 ? comment.trim() : `${"-".repeat(108)}\n${comment.trim()}`;
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
                    }</textarea></div> </td>
                    <td class=" align-middle ">
              
 <div class="d-flex justify-content-center align-middle action-column" >

 <div title="Follow Up" data-id="${row.sales_id}" data-revised="${row.revised_qt}" id="follow-up" data-id="${row.sales_id}" data-comments="${row.comments}" data-bs-toggle="modal" data-bs-target="#followup-modal" data-revised="${row.revised_qt}"> 
 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="#0d6efd" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
</svg>

</div>

<div id="info-client" 
     data-bs-toggle="modal" 
     data-bs-target="#view-modal-info" 
     title="Info" 
     data-sr-num="${row.sr_num}" 
     data-sales-id="${row.sales_id}"
     data-sent_date="${row.quote_sent_date}" 
     data-enquiry="${row.enquiry_details}" 
     data-manager="${row.engineer}" 
     data-amount="${row.amount}"
     data-revised="${row.revised_qt}"
     data-comments="${row.comments}">

    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="gray" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
    </svg>
</div>

<div title="Client Info" data-bs-toggle="modal" data-bs-target="#client-info-modal"
data-sales-id="${row.sales_id}"
data-revised="${row.revised_qt}"
data-company="${row.company_name}"
data-clientid="${row.client}"
data-name="${row.client_name}"
data-email="${row.email_id}"
data-address = "${row.address}"
data-client="${row.client_contact}"
data-moredetails= "${row.more_detials}"
>
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="purple" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
</svg>
</div>

<div title="Revise Quotation" data-bs-toggle="modal" data-bs-target="#revised-quotation-modal" data-bs-quote_sent_date="${row.quote_sent_date}"  data-bs-id="${row.sales_id}" data-bs-enquiry="${row.enquiry_details}" data-bs-amount="${row.amount}" data-bs-comment="${row.comments}">

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="teal" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
</svg>
</div>

<div title="Mark Accepted" data-bs-toggle="modal" data-bs-id="${row.sales_id}" data-bs-revised_qt="${row.revised_qt}" data-bs-target="#confirmation-modal" data-action="accept">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" class="ml-3" stroke="green" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
</svg>
</div>

<div title="Mark Cancelled" data-bs-toggle="modal" data-bs-id="${row.sales_id}" data-bs-revised_qt="${row.revised_qt}" data-bs-target="#confirmation-modal" data-action="cancel">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"  stroke-width="2" class="ml-3" stroke="crimson" width="20" height="20">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
</svg>
</div>






                                
 </div>
</td> `;

                tableBody.appendChild(tr);
            });




            $(document).ready(function () {
                // Get URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const searchQuery = urlParams.get('sales_id'); // Get the value of the 'search' parameter

                // Initialize DataTable
                const table = $('#customer-table').DataTable({
                    paging: true
                });

                // Add filter elements to the DataTable search area
                $("#customer-table_filter").prepend($("#admin_user"));
                $("#customer-table_filter").prepend($("#comment_user_filter"));
                $("#customer-table_filter").prepend($("#comment_user_filter_wrapper"));

                // Add event listener for the comment user filter
                $('#comment_user_filter').on('change', function () {
                    const selectedUser = $(this).val();

                    // Search for the selected user in the comments column (column index 5)
                    table.column(5).search(selectedUser).draw();
                });

                // If there's a search query in the URL, apply it to the DataTable search
                if (searchQuery) {
                    table.search(searchQuery).draw();  // Apply the search filter to the DataTable
                }
            });

        })
        .catch(error => console.error('Error fetching data:', error));





    //update
    //updation of comment section .
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('#customer-table tbody').addEventListener('click', function (event) {
            if (event.target.classList.contains('save-btn')) {
                const row = event.target.closest('tr');
                const salesId = event.target.getAttribute("data-id");
                let revisedQt = event.target.getAttribute("data-revised");

                // Ensure revisedQt is treated as an empty string if null or undefined
                if (!revisedQt || revisedQt === "null" || revisedQt === "undefined") {
                    revisedQt = "";
                }

                const comment = row.querySelector('.comments-form').value.trim();

                if (!salesId || comment === "") {
                    alert("Sales ID or comment is missing.");
                    return;
                }

                let formData = `sales_id=${encodeURIComponent(salesId)}&comment=${encodeURIComponent(comment)}`;
                if (revisedQt !== "") {
                    formData += `&revised_qt=${encodeURIComponent(revisedQt)}`;
                }

                console.log("Sending data:", formData); // Debugging

                fetch('./update_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Response:", data); // Debugging
                        if (data.success) {
                            alert('Comment updated successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });






    tippy('[data-bs-toggle="tooltip"]', {
        allowHTML: true,
        placement: 'bottom',
        theme: 'custom-tooltip',
    });






    //view 

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector("#customer-table tbody").addEventListener("click", function (event) {
            if (event.target.classList.contains("view-btn")) {
                let salesId = event.target.getAttribute("data-id");
                let revisedID = event.target.getAttribute("data-revised");

                if (salesId) {
                    // Build the API URL dynamically
                    let fetchUrl = `fetch_sent_quotation.php?sales_id=${salesId}`;

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
                            document.querySelector("#modal-sales-id").textContent = data.sales_id || 'N/A';
                            document.querySelector("#sales_id").value = data.sales_id || '';
                            document.querySelector("#revised_qt").value = data.revised_qt || '';
                            document.querySelector("#view-modal input[placeholder='Company Name']").value = data.company_name || '';
                            document.querySelector("#contact_name").value = data.client_name || '';
                            document.querySelector("#view-modal input[placeholder='Contact Email']").value = data.email_id || '';
                            document.querySelector("#view-modal input[placeholder='Company Address']").value = data.address || '';
                            document.querySelector("#view-modal input[placeholder='Phone']").value = data.client_contact || '';
                            document.querySelector("#view-modal input[placeholder='Quotation Sent Date']").value = data.quote_sent_date || '';
                            document.querySelector("#view-modal input[placeholder='Quotation Amount']").value = data.amount || '';
                            document.querySelector("#view-modal textarea[name='enquiry_details']").value = data.enquiry_details || '';
                            document.querySelector("#view-modal select[name='engineer']").value = data.engineer || '';
                            document.querySelector("#client_id").value = data.client || '';
                            document.querySelector("#comments").value = data.comments || '';
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




    //updation

    //updation 
</script>

<?php

if (isset($_POST['update-client-info'])) {
    // Get form data
    $sales_id = $_POST['sales_id'];
    $revised_qt = isset($_POST['revised_qt']) && $_POST['revised_qt'] !== '' ? $_POST['revised_qt'] : null;
    $company_name = $_POST['company_name'];
    $client_id = $_POST['client'];
    $client_name = $_POST['client_name'];
    $email_id = $_POST['contact_email'];
    $address = $_POST['company_address'];
    $client_contact = $_POST['client_contact'];
    $more_details = $_POST['more_details'];

    // Prepare the update query based on whether revised_qt is provided
    if ($revised_qt !== null) {
        $sql = "UPDATE potential_project_sent_quotation 
                SET company_name = ?, client = ?, client_name = ?, email_id = ?, address = ?, client_contact = ?, more_detials = ?
                WHERE sales_id = ? AND revised_qt = ?";
        $params = [$company_name, $client_id, $client_name, $email_id, $address, $client_contact, $more_details, $sales_id, $revised_qt];
    } else {
        $sql = "UPDATE potential_project_sent_quotation 
                SET company_name = ?, client = ?, client_name = ?, email_id = ?, address = ?, client_contact = ?, more_detials = ?
                WHERE sales_id = ? AND revised_qt IS NULL";
        $params = [$company_name, $client_id, $client_name, $email_id, $address, $client_contact, $more_details, $sales_id];
    }

    // Execute the query
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Client details updated successfully!'); window.location.href='sent_quotations.php';</script>";
        } else {
            echo "<script>alert('Error updating client details. Please try again.');</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('Database error. Please try again.');</script>";
    }

    mysqli_close($conn);
}
?>

<?php

if (isset($_POST['quotation_info'])) {
    // Get form data
    $sales_id = $_POST['sales_id'];
    $revised_qt = $_POST['revised_qt'];
    $quote_sent_date = $_POST['quote_sent_date'];
    $quotation_amount = $_POST['quotation_amount'];
    $enquiry_details = $_POST['enquiry_details'];
    $engineer = $_POST['engineer'];

    // echo '<pre>';
    // echo print_r($_POST);
    // echo '</pre>';
// exit;
    // Check if revised_qt is empty or NULL
    if (empty($revised_qt)) {
        $sql = "UPDATE potential_project_sent_quotation 
        SET quote_sent_date = ?, amount = ?, enquiry_details = ?, engineer = ? 
        WHERE sales_id = ? AND (revised_qt IS NULL OR revised_qt = ?)";


        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssss", $quote_sent_date, $quotation_amount, $enquiry_details, $engineer, $sales_id, $revised_qt);
        }
    } else {
        $sql = "UPDATE potential_project_sent_quotation 
                SET quote_sent_date = ?, amount = ?, enquiry_details = ?, engineer = ? 
                WHERE sales_id = ? AND revised_qt = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssss", $quote_sent_date, $quotation_amount, $enquiry_details, $engineer, $sales_id, $revised_qt);
        }
    }

    // Execute the query
    if (isset($stmt) && mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Quotation details updated successfully!'); window.location.href='sent_quotations.php';</script>";
    } else {
        echo "<script>alert('Error updating quotation details.');</script>";
    }

    // Close resources
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
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

    // Fetch Existing Comments based on revised_qt presence
    if (!empty($revised_qt)) {
        $fetch_query = "SELECT comments FROM potential_project_sent_quotation WHERE sales_id = ? AND revised_qt = ?";
        $fetch_stmt = $conn->prepare($fetch_query);
        $fetch_stmt->bind_param("ss", $sales_id, $revised_qt);
    } else {
        $fetch_query = "SELECT comments FROM potential_project_sent_quotation WHERE sales_id = ? AND revised_qt IS NULL";
        $fetch_stmt = $conn->prepare($fetch_query);
        $fetch_stmt->bind_param("s", $sales_id);
    }

    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $row = $result->fetch_assoc();
    $existing_comments = $row['comments'] ?? '';
    $fetch_stmt->close();

    // Append new comment in REVERSE order (newest first)
    if (!empty($comments_followup)) {
        $new_comment = "$current_timestamp - $comments_followup --- $manager_name";
        $comments = trim("$new_comment\n$existing_comments"); // NEW COMMENT FIRST
    } else {
        $comments = $existing_comments; // Keep previous comments if no new content
    }

    // Update query based on revised_qt presence
    if (!empty($revised_qt)) {
        $update_query = "UPDATE potential_project_sent_quotation SET comments = ? WHERE sales_id = ? AND revised_qt = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sss", $comments, $sales_id, $revised_qt);
    } else {
        $update_query = "UPDATE potential_project_sent_quotation SET comments = ? WHERE sales_id = ? AND revised_qt IS NULL";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $comments, $sales_id);
    }

    // Execute the update statement
    if ($update_stmt->execute()) {
        echo "<script>
            alert('Follow-up updated successfully!');
            window.location.href = 'sent_quotations.php';
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
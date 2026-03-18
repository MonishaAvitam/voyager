<?php
include '../conn.php';
include '../authentication.php';

session_start();

$user_id = $_SESSION['admin_id'];
$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit();
}
// Check access from database
$query = "SELECT raeAccess FROM tbl_admin WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['raeAccess'] != 1 && $row['raeAccess'] != 3) {
        // User does NOT have access → silently go back
        echo "<script>window.history.back();</script>";
        exit();
    }
} else {
    // No user found → silently go back
    echo "<script>window.history.back();</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Project Gantt Chart</title>
    <link rel="stylesheet" href="libs/dhtmlxgantt.css">
    <script src="libs/dhtmlxgantt.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="https://csaappstore.com/img/CSAlogo.ico" type="image/x-icon">

    <link rel="stylesheet" href="style.css">

    <!-- Production -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
</head>
<script>
    let tippyInstance; // global variable

    window.addEventListener("DOMContentLoaded", () => {
        tippyInstance = tippy('#advancedFilterBtn', {
            content: 'Click to apply advanced filters',
            placement: 'bottom',
            arrow: true,
            theme: 'light',
        })[0]; // get the first instance
    });


</script>

<body>

    <div class="container">
        <!-- Header -->
        <div class="app-header">
            <div class="logo-container">
                <img src="https://www.csaengineering.com.au/wp-content/uploads/2022/10/White-Logo.png" alt="Logo">
            </div>

            <button onclick="history.back()" class="btn-secondary">
                ← Go Back
            </button>
        </div>



        <div class="control-group urgency-group" style="margin-bottom: 10px;">
            <div class="project-count" id="projectCount" style="text-align: left; width: 100%;">
                Total Projects: 0
            </div>
            <label>Urgency:</label>
            <div id="colorButtons" class="color-button-group">
                <button class="color-btn red" onclick="applyColorFilter('red')">Very Urgent</button>
                <button class="color-btn navy" onclick="applyColorFilter('navy')">Completed</button>
                <button class="color-btn green" onclick="applyColorFilter('green')">In Progress</button>
                <button class="color-btn orange" onclick="applyColorFilter('orange')">Urgent</button>
                <button class="color-btn yellow" onclick="applyColorFilter('yellow')">On Hold</button>
                <button class="color-btn purple" onclick="applyColorFilter('purple')">Closed</button>
                <button class="color-btn white" onclick="applyColorFilter('white')">Waiting</button>
                <button class="color-btn gray" onclick="applyColorFilter('gray')">Not Started</button>
                <button class="color-btn all" onclick="applyColorFilter('')">All</button>
            </div>
        </div>


        <div style="display: flex; flex-wrap: wrap; justify-content: center; margin-top: 20px;">
            <div class="controls" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">

                <!-- View Select -->
                <div class="control-group">
                    <label for="scaleSelect">View:</label>
                    <select id="scaleSelect" onchange="setScale(this.value)">
                        <option value="week">Week</option>
                        <option value="month" selected>Month</option>
                        <option value="year">Year</option>
                    </select>
                </div>

                <!-- Page Size -->
                <div class="control-group">
                    <label for="pageSize">Show:</label>
                    <select id="pageSize" onchange="changePageSize(this.value)">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Pagination -->
                <div class="control-group page-controls">
                    <button id="prevBtn" onclick="prevPage()">← Previous</button>
                    <span id="pageInfo"></span>
                    <button id="nextBtn" onclick="nextPage()">Next →</button>
                </div>

                <!-- Search -->
                <div class="control-group search-group">
                    <label for="searchBox">Search:</label>
                    <input type="text" id="searchBox" placeholder="Project, ID, PM...">
                    <button onclick="applySearch()">Search</button>
                </div>

                <!-- Filter Button -->
                <div class="filter-btn-wrapper">
                    <button id="advancedFilterBtn" class="btn-secondary" onclick="openFilterModal()">
                        🔍 Advanced Filters
                    </button>
                    <span class="filter-tooltip" id="filterTooltip"></span>
                </div>

                <!-- Reset Button (outside modal) -->
                <button onclick="resetFilters()" class="btn-danger">
                    ♻ Reset Filters
                </button>

                <!-- Modal -->
                <div id="filterModal" class="modal">
                    <div class="modal-content">
                        <!-- Header -->
                        <div class="modal-header">
                            <h2>Advanced Filters</h2>
                            <span class="close"
                                onclick="document.getElementById('filterModal').style.display='none'">&times;</span>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <div class="filter-grid">
                                <!-- Date Filter -->
                                <div class="control-group">
                                    <label for="startDate">Start Date</label>
                                    <input type="date" id="startDate">
                                </div>

                                <div class="control-group">
                                    <label for="endDate">End Date</label>
                                    <input type="date" id="endDate">
                                </div>

                                <!-- Team Filter -->
                                <div class="control-group full-width">
                                    <label for="teamSelect">Team</label>
                                    <select id="teamSelect" onchange="applyTeamFilter(this.value)">
                                        <option value="">All Teams</option>
                                        <option value="Industrial Team">Industrial</option>
                                        <option value="IT">IT</option>
                                        <option value="Building Team">Building</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <button class="btn-secondary"
                                onclick="document.getElementById('filterModal').style.display='none'">Cancel</button>
                            <button class="btn-primary" onclick="applyDateFilter()">Apply</button>
                        </div>
                    </div>
                </div>


            </div>

            <script>
                // Press Enter in search box → apply search
                document.getElementById("searchBox").addEventListener("keydown", function (e) {
                    if (e.key === "Enter") applySearch();
                });
            </script>
        </div>



        <div id="loadingOverlay">
            <div class="spinner"></div>
            Loading projects...
        </div>
        <div id="gantt_here"></div>

        <div id="projectModal">
            <div class="content">
                <div class="modal-header">
                    <h2 id="modalTitle">Project Details</h2>
                    <span class="close"
                        onclick="document.getElementById('projectModal').style.display='none'">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="modalContent"></div>
                </div>
            </div>
        </div>

        <div id="customerTooltip" class="customer-tooltip"></div>
    </div>

    <script>

        // Only the UI has been enhanced
        let currentPage = 1;
        let pageSize = 50;
        let totalItems = 0;
        let searchQuery = "";
        let filterStartDate = "";
        let filterEndDate = "";
        let filterColor = "";
        let filterTeam = "";

        function showLoading() { document.getElementById("loadingOverlay").style.display = "block"; }
        function hideLoading() { document.getElementById("loadingOverlay").style.display = "none"; }

        function openFilterModal() {
            document.getElementById('filterModal').style.display = 'block';
        }
        function updateAdvancedFilterBtn() {
            const btn = document.getElementById('advancedFilterBtn');
            let activeFilters = [];

            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const team = document.getElementById('teamSelect').value;

            if (startDate) activeFilters.push(`Start: ${startDate}`);
            if (endDate) activeFilters.push(`End: ${endDate}`);
            if (team) activeFilters.push(`Team: ${team}`);

            if (activeFilters.length > 0) {
                btn.style.backgroundColor = '#FFA500'; // orange
                btn.style.color = '#000';
            } else {
                btn.style.backgroundColor = '';
                btn.style.color = '';
            }

            // Remove native browser tooltip
            btn.removeAttribute("title");

            // Update Tippy tooltip
            tippyInstance.setContent(activeFilters.length ? activeFilters.join(' | ') : 'Click to apply advanced filters');
        }


        document.getElementById('startDate').addEventListener('change', updateAdvancedFilterBtn);
        document.getElementById('endDate').addEventListener('change', updateAdvancedFilterBtn);
        document.getElementById('teamSelect').addEventListener('change', updateAdvancedFilterBtn);

        function applySearch() {
            searchQuery = document.getElementById("searchBox").value.trim();
            localStorage.setItem("searchQuery", searchQuery);
            currentPage = 1; // reset to first page for new search
            loadData();
        }

        function applyDateFilter() {
            filterStartDate = document.getElementById("startDate").value;
            filterEndDate = document.getElementById("endDate").value;
            localStorage.setItem("filterStartDate", filterStartDate);
            localStorage.setItem("filterEndDate", filterEndDate);
            currentPage = 1;
            loadData();
            updateAdvancedFilterBtn();  // update button color/tooltip

            // Close modal immediately
            document.getElementById("filterModal").style.display = "none";
        }
        function applyColorFilter(color) {
            filterColor = color;   // stays "red" | "green" | etc.
            localStorage.setItem("filterColor", filterColor);
            currentPage = 1;

            // toggle active class
            document.querySelectorAll(".color-btn").forEach(btn => btn.classList.remove("active"));
            if (color !== "") {
                document.querySelector(`.color-btn.${color}`).classList.add("active");
            } else {
                document.querySelector(".color-btn.all").classList.add("active");
            }

            loadData();

        }

        function applyTeamFilter(team) {
            filterTeam = team;
            localStorage.setItem("filterTeam", filterTeam);
            currentPage = 1; // reset to first page
            loadData();       // reload gantt data with team filter
        }
        function resetFilters() {
            // reset variables
            searchQuery = "";
            filterStartDate = "";
            filterEndDate = "";
            filterColor = "";
            filterTeam = "";

            // reset inputs
            document.getElementById("searchBox").value = "";
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";

            // reset color button UI
            document.querySelectorAll(".color-btn").forEach(btn => btn.classList.remove("active"));
            document.querySelector(".color-btn.all")?.classList.add("active");

            // reset team filter
            const teamDropdown = document.getElementById("teamSelect");
            if (teamDropdown) {
                teamDropdown.value = ""; // default "All Teams"
            }

            // clear localStorage
            localStorage.removeItem("searchQuery");
            localStorage.removeItem("filterStartDate");
            localStorage.removeItem("filterEndDate");
            localStorage.removeItem("filterColor");
            localStorage.removeItem("filterTeam");

            // reset pagination
            currentPage = 1;

            // reload data
            loadData();

            // update Advanced Filters button
            updateAdvancedFilterBtn();
        }


        // Prevent built-in alert popups
        gantt.message = function () { return false; };
        gantt.alert = function () { return false; };
        gantt.confirm = function () { return false; };
        gantt.config.details_on_dblclick = false;
        gantt.config.details_on_dblclick = false; // disable lightbox on dblclick
        gantt.config.details_on_create = false;


        function setScale(mode) {
            switch (mode) {
                case "week":
                    gantt.config.scales = [
                        { unit: "week", step: 1, format: "Week %W" },
                        { unit: "day", step: 1, format: "%D %d" }
                    ];
                    break;

                case "month":
                    gantt.config.scales = [
                        { unit: "month", step: 1, format: "%F %Y" },
                        { unit: "week", step: 1, format: "Week %W" }
                    ];
                    break;

                case "year":
                    gantt.config.scales = [
                        { unit: "year", step: 1, format: "%Y" },
                        { unit: "month", step: 1, format: "%M" }
                    ];
                    break;
            }
            gantt.render();
        }

        function formatDateToDDMMYY(dateStr, includeWeekday = false) {
            if (!dateStr) return "N/A";
            const d = new Date(dateStr);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0'); // Month is 0-based
            const year = String(d.getFullYear()).slice(-2); // Last 2 digits

            if (includeWeekday) {
                const weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                const weekdayName = weekdays[d.getDay()];
                return `${weekdayName}/${day}/${month}/${year}`;
            }

            return `${day}/${month}/${year}`;
        }

        const urgencyLabels = {
            red: "Very Urgent",
            navy: "Completed",
            green: "In Progress",
            orange: "Urgent",
            yellow: "On Hold",
            purple: "Closed",
            white: "Waiting",
            gray: "Not Started"
        };

        // Urgency → color mapping
        const urgencyColors = {
            red: "#ff0000",
            navy: "#1A1A55",
            green: "#188918",
            orange: "#FFA500",
            yellow: "#FFFF00",
            purple: "#8B008B",
            white: "#ffffff",
            gray: "#bfbfbf"
        };

        // Configure columns
        gantt.config.columns = [
            {
                name: "projectId",
                tree: true,
                label: "ID",
                align: "left",
                width: 160,
                template: function (task) {
                    let html = task.projectId || "";

                    // Reopen badge
                    if (task.reopen_status) {
                        html += `<span class="badge badge-danger" style="margin-left:5px;">
        ${task.reopen_status}
    </span>`;
                    }

                    // Subproject status badge (red) — only for subprojects / child rows
                    if (task.subproject_status) {
                        html += `<span class="badge badge-danger" style="margin-left:5px;">
        S${task.subproject_status}
    </span>`;
                    }
                    return html;
                },
                header: {
                    template: `<div style="display:flex; flex-direction:column; align-items:center;">
                ID
                <button onclick="toggleColumn('projectId')" style="font-size:10px; margin-top:2px;">⯆</button>
            </div>`
                }
            },
            {
                name: "project_manager",
                label: "PM",
                align: "left",
                width: 100,
                template: function (task) {
                    if (!task.project_manager) return "N/A";
                    return task.project_manager.split(" ")[0]; // shows only first name
                },
                header: {
                    template: `<div style="display:flex; flex-direction:column; align-items:center;">
                PM
                <button onclick="toggleColumn('project_manager')" style="font-size:10px; margin-top:2px;">⯆</button>
            </div>`
                }
            },
            {
                name: "customer_name",
                label: "Customer ID",
                align: "left",
                width: 150,
                template: function (task) {
                    let name = task.customer_name || "N/A";
                    if (name.length > 8) {
                        name = name.substring(0, 8) + "...";
                    }
                    return `<div class="customer-cell" onmouseover="showCustomerTooltip(event, '${task.id}')" onmouseout="hideCustomerTooltip()">
        ${name}
    </div>`;
                },
                header: {
                    template: `<div style="display:flex; flex-direction:column; align-items:center;">
                Customer
                <button onclick="toggleColumn('customer_name')" style="font-size:10px; margin-top:2px;">⯆</button>
            </div>`
                }
            }
        ];


        gantt.config.date_format = "%Y-%m-%d";

        // Color bars based on urgency
        gantt.templates.task_class = function (start, end, task) {
            if (task.urgency && urgencyColors[task.urgency]) {
                return "task_" + task.urgency;
            }
            return "";
        };

        // Custom task bar style
        Object.entries(urgencyColors).forEach(([urgency, color]) => {
            const style = document.createElement("style");
            style.textContent = `.gantt_task_line.task_${urgency} { background-color: ${color} !important; border-color: ${color} !important; }`;
            document.head.appendChild(style);
        });


        gantt.showLightbox = function () {
            return false;
        };

        gantt.init("gantt_here");
        setScale("month");


        let hiddenColumns = new Set();

        function toggleColumn(columnName) {
            if (hiddenColumns.has(columnName)) {
                hiddenColumns.delete(columnName); // Show column
            } else {
                hiddenColumns.add(columnName); // Hide column
            }

            // Reapply columns, keep headers intact
            const baseColumns = gantt.config.columns.map(col => ({
                ...col,
                header: col.header
            }));

            gantt.config.columns = baseColumns.filter(c => !hiddenColumns.has(c.name));
            gantt.render();
        }
        function addBorderToWhiteTasks() {
            document.querySelectorAll(".gantt_task_line.task_white").forEach(el => {
                el.style.border = "1px solid #000"; // black border around white bars
            });
        }


        function changePageSize(size) {
            pageSize = parseInt(size);
            currentPage = 1;
            loadData();
        }

        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                loadData();
            }
        }

        function nextPage() {
            if (currentPage * pageSize < totalItems) {
                currentPage++;
                loadData();
            }
        }
        // Show customer tooltip on hover
        function showCustomerTooltip(event, taskId) {
            const task = gantt.getTask(taskId);

            // If this task doesn't have customer details but has a parent, try the parent
            let customerTask = task;
            if ((!task.customer_details || task.customer_details === "N/A") && task.parent) {
                customerTask = gantt.getTask(task.parent);
            }

            // If still no customer details, try to find the root parent
            if ((!customerTask.customer_details || customerTask.customer_details === "N/A") && customerTask.parent) {
                let rootTask = customerTask;
                while (rootTask.parent) {
                    rootTask = gantt.getTask(rootTask.parent);
                }
                customerTask = rootTask;
            }

            if (!customerTask || !customerTask.customer_details || customerTask.customer_details === "N/A") return;

            const tooltip = document.getElementById("customerTooltip");
            tooltip.innerHTML = customerTask.customer_details;
            tooltip.style.display = "block";
            tooltip.style.left = (event.pageX + 10) + "px";
            tooltip.style.top = (event.pageY + 10) + "px";
        }

        // Hide customer tooltip
        function hideCustomerTooltip() {
            const tooltip = document.getElementById("customerTooltip");
            tooltip.style.display = "none";
        }

        // Ensure tooltip hides when leaving gantt area
        gantt.$container.addEventListener("mouseleave", hideCustomerTooltip);

        // Optional: also hide if user moves too fast and leaves task row
        document.addEventListener("mousemove", (e) => {
            const ganttBox = gantt.$container.getBoundingClientRect();
            if (
                e.clientX < ganttBox.left ||
                e.clientX > ganttBox.right ||
                e.clientY < ganttBox.top ||
                e.clientY > ganttBox.bottom
            ) {
                hideCustomerTooltip();
            }
        });
        // Format contact details for display
        function formatContactDetails(contacts) {
            if (!contacts || contacts.length === 0) return null;

            let seen = new Set();
            let html = "<h4>Customer Details</h4>";

            contacts.forEach(c => {
                if (seen.has(c.customer_name)) return; // skip duplicates
                seen.add(c.customer_name);

                html += `
            <p><strong>${c.customer_name || "N/A"}</strong></p>
            <p>Name: ${c.contact_name || "N/A"}</p>
            <p>Email: ${c.contact_email || "N/A"}</p>
            <p>Phone: ${c.contact_phone_number || "N/A"}</p>
            <p>Address: ${c.address || "N/A"}</p>
            <hr>
        `;
            });

            return html;
        }

        const SECRET_KEY = "mySuperSecretKey123"; // should match backend

        // Fetch backend data
        function loadData() {
            showLoading();
            console.log("DEBUG filterColor being sent:", filterColor);
            fetch(`http://127.0.0.1:8090/gantt-data?limit=${pageSize}&page=${currentPage}&search=${encodeURIComponent(searchQuery)}&start_date=${encodeURIComponent(filterStartDate)}&end_date=${encodeURIComponent(filterEndDate)}&color=${encodeURIComponent(filterColor)}&team=${encodeURIComponent(filterTeam)}&secret_key=${SECRET_KEY}`)
                .then(res => res.json())
                .then(data => {
                    totalItems = data.total;
                    document.getElementById("projectCount").textContent = "Total Projects: " + totalItems;

                    // Update pagination info
                    const totalPages = totalItems > 0 ? Math.ceil(totalItems / pageSize) : 1;
                    document.getElementById("pageInfo").innerText =
                        `Page ${data.page || 1} of ${totalPages}`;
                    document.getElementById("prevBtn").disabled = currentPage === 1;
                    document.getElementById("nextBtn").disabled = currentPage * pageSize >= totalItems;

                    // Reset tasks each load
                    const tasks = { data: [], links: [] };

                    // 🔥 Your full project loop stays here
                    if (data.projects && Array.isArray(data.projects)) {
                        data.projects.forEach(project => {
                            const projectId = project.id;
                            const start = formatDateToDDMMYY(project.start);
                            const end = formatDateToDDMMYY(project.end);


                            // Format customer details for the tooltip
                            const customerDetails = formatContactDetails(project.contacts);

                            // Badge for reopen_status
                            let reopenBadge = "";
                            if (project.reopen_status) {
                                reopenBadge = `<span class="badge badge-danger" style="margin-left:5px;">
        ${project.reopen_status}
    </span>`;
                            }

                            // Project Info with badge
                            let projectInfoHTML = `
<div class="modal-section">
    <h3>Project Information</h3>
    <p><strong>Project ID:</strong> ${project.id || "N/A"} ${reopenBadge}</p>
    <p><strong>Description:</strong> ${project.project_details || project.subproject_details || "N/A"}</p>
    <p><strong>Start Date:</strong> ${formatDateToDDMMYY(project.start, true)}</p>
    <p><strong>Expected End Date:</strong> ${formatDateToDDMMYY(project.end, true)}</p>
    <p><strong>Project Manager:</strong> ${project.project_manager || "N/A"}</p>
    <p><strong>Engineer:</strong> ${project.assign_to || "N/A"}</p>
    <p><strong>Team:</strong> ${project.p_team || "N/A"}</p>
    <p><strong>Status:</strong> ${urgencyLabels[project.urgency] || "N/A"}</p>
    <p><strong>State:</strong> ${project.state || "N/A"}</p>
</div>
`;
                            // Receivable Details
                            let receivableHTML = `
                            <h3>Receivable Details</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Invoice</th><th>Service Date</th><th>Due Date</th><th>Status</th><th>Amount</th><th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                            let receivables = [...(project.invoices ?? []), ...(project.ready_to_invoice ?? []), ...(project.uninvoiced ?? [])];
                            if (receivables.length > 0) {
                                receivables.forEach(inv => {
                                    // Use either invoice_number (for real invoices) or "—" for uninvoiced
                                    const invoiceNo = inv.invoice_number || "—";
                                    // Status: prefer payment_status, else project_status (e.g., "Uninvoiced")
                                    const status = inv.payment_status || inv.project_status || "N/A";
                                    // Amount: prefer amount, else price
                                    const amount = inv.amount ? "$" + inv.amount : inv.price ? "$" + inv.price : "N/A";

                                    receivableHTML += `
        <tr>
            <td>${invoiceNo}</td>
            <td>${inv.service_date || "N/A"}</td>
            <td>${inv.due_date ? new Date(inv.due_date).toLocaleDateString() : "N/A"}</td>
            <td>${status}</td>
            <td style="text-align:right;">${amount}</td>
            <td>${inv.comments || "N/A"}</td>
        </tr>
    `;
                                });
                            } else {
                                receivableHTML += `
                                        <tr>
                                            <td colspan="6" style="text-align:center;">N/A</td>
                                        </tr>
                                `;
                            }
                            receivableHTML += `</tbody></table>`;

                            // Payable Details
                            let payableHTML = `
    <h3>Payable Details</h3>
    <table>
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Invoice Date</th>
                <th>Booked Date</th>
                <th>Received Date</th>
                <th>Amount</th>
                <th>Comments</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
`;
                            const renderPayableRows = (invoices = [], statusLabel = "") => {
                                if (invoices.length === 0) return;
                                invoices.forEach(inv => {
                                    payableHTML += `
            <tr>
                <td>${inv?.invoice_no || "N/A"}</td>
                <td>${inv?.invoice_date || "N/A"}</td>
                <td>${inv?.booked_date || "N/A"}</td>
                <td>${inv?.received_date || "N/A"}</td>
                <td style="text-align:right;">${inv?.amount ? "$" + inv.amount : "N/A"}</td>
                <td>${inv?.comments || "N/A"}</td>
                <td>${statusLabel}</td>
            </tr>
        `;
                                });
                            };

                            // Always call with fallback to empty array
                            renderPayableRows(project?.unpaid_invoices || [], "Unpaid");
                            renderPayableRows(project?.ready_to_pay || [], "Ready to Pay");
                            renderPayableRows(project?.paid_invoices || [], "Paid");

                            // If no rows added, show N/A
                            if (
                                (!project?.unpaid_invoices || project.unpaid_invoices.length === 0) &&
                                (!project?.ready_to_pay || project.ready_to_pay.length === 0) &&
                                (!project?.paid_invoices || project.paid_invoices.length === 0)
                            ) {
                                payableHTML += `
        <tr>
            <td colspan="7" style="text-align:center;">N/A</td>
        </tr>
    `;
                            }
                            payableHTML += `</tbody></table>`;

                            // Merge everything (contact details removed from modal)
                            const fullDetailsHTML = `
                            ${projectInfoHTML}
                            <div class="modal-section">${receivableHTML}</div>
                            <div class="modal-section">${payableHTML}</div>
                        `;

                            // Parent task
                            tasks.data.push({
                                id: projectId,
                                text: project.name,
                                start_date: project.start,
                                end_date: project.end,
                                progress: 0,
                                open: true,
                                projectId: projectId,
                                urgency: project.urgency,
                                details: fullDetailsHTML,
                                project_manager: project.project_manager || "N/A",
                                customer_name: (project.contacts && project.contacts.length > 0)
                                    ? project.contacts[0].customer_name || "N/A"
                                    : "N/A",
                                customer_details: customerDetails, // This will be shown in the tooltip
                                reopen_status: project.reopen_status || null
                            });

                            // Children
                            project.children?.forEach((child, index) => {
                                const childStart = formatDateToDDMMYY(child.start, true);
                                const childEnd = formatDateToDDMMYY(child.end, true);
                                const childId = `${projectId}-${index}`;

                                // Badge for reopen_status
                                let childReopenBadge = "";
                                if (child.reopen_status) {
                                    childReopenBadge = `<span class="badge badge-danger" style="margin-left:5px;">
        ${child.reopen_status}
    </span>`;
                                }

                                const combinedDetails = `
    <p><strong>Parent Project ID:</strong> ${project.id || "N/A"}</p>
    <p><strong>Parent Project Name:</strong> ${project.name || "N/A"}</p>
    <strong>Subproject:</strong> ${child.name || `Subproject ${index + 1}`} ${childReopenBadge}<br>
    Start: ${childStart}<br>
    End: ${childEnd}<br>
    Details: ${child.subproject_details || "N/A"}<br>
    
    Urgency: ${urgencyLabels[child.urgency] || "N/A"}<br>
    <hr>
    ${receivableHTML}
    ${payableHTML}
`;
                                tasks.data.push({
                                    id: childId,
                                    text: child.name || `Subproject ${index + 1}`,
                                    start_date: child.start,
                                    end_date: child.end,
                                    progress: 0,
                                    parent: projectId,
                                    open: true,
                                    projectId: projectId,
                                    urgency: child.urgency,
                                    details: combinedDetails,
                                    project_manager: project.project_manager || "N/A",
                                    customer_name: (project.contacts && project.contacts.length > 0)
                                        ? project.contacts[0].customer_name || "N/A"
                                        : "N/A",
                                    reopen_status: child.reopen_status || null,
                                    customer_details: null,
                                    subproject_status: child.status || null
                                });


                                tasks.links.push({
                                    id: `link-${projectId}-${index}`,
                                    source: projectId,
                                    target: childId,
                                    type: "1"
                                });
                            });

                        });
                    } else {
                        console.warn("No projects found or backend error:", data);
                    }

                    // Clear old tasks and parse new
                    gantt.clearAll();
                    gantt.parse(tasks);
                    addBorderToWhiteTasks();
                })
                .catch(err => console.error("Error loading data:", err))
                .finally(() => hideLoading());
        }
        // Call after tasks are parsed
        function parseTasks(tasks) {
            gantt.clearAll();
            gantt.parse(tasks);
            addBorderToWhiteTasks(); // <-- right after parsing
        }
        // Only open modal when clicking on the bar area (not grid)
        gantt.attachEvent("onTaskClick", function (id, e) {
            const taskArea = e.target.closest(".gantt_task_line");
            if (taskArea) {
                const task = gantt.getTask(id);
                document.getElementById("modalTitle").innerText = "Project ID: " + task.projectId;
                document.getElementById("modalContent").innerHTML = task.details || "No details available.";
                document.getElementById("projectModal").style.display = "block";
                return false; // stop default select
            }
            return true; // let grid clicks pass through
        });
        // When clicking grid (columns) → scroll + highlight bar
        gantt.attachEvent("onGridClick", function (id, e) {
            gantt.showTask(id);   // scroll chart to bar
            highlightTaskBar(id); // highlight effect
            return true;
        });

        function highlightTaskBar(taskId) {
            const taskNode = gantt.getTaskNode(taskId);
            if (taskNode) {
                taskNode.classList.add("highlighted");
                setTimeout(() => taskNode.classList.remove("highlighted"), 2000);
            }
        }
        // Restore filters + search on page load
        window.addEventListener("DOMContentLoaded", () => {
            const savedSearch = localStorage.getItem("searchQuery") || "";
            const savedStartDate = localStorage.getItem("filterStartDate") || "";
            const savedEndDate = localStorage.getItem("filterEndDate") || "";
            const savedTeam = localStorage.getItem("filterTeam") || "";
            filterTeam = savedTeam;
            const savedColor = localStorage.getItem("filterColor") || "";
            filterColor = savedColor;
            if (savedColor !== "") {
                document.querySelector(`.color-btn.${savedColor}`)?.classList.add("active");
            } else {
                document.querySelector(".color-btn.all")?.classList.add("active");
            }

            searchQuery = savedSearch;
            filterStartDate = savedStartDate;
            filterEndDate = savedEndDate;

            document.getElementById("searchBox").value = savedSearch;
            document.getElementById("startDate").value = savedStartDate;
            document.getElementById("endDate").value = savedEndDate;
            document.getElementById("teamSelect").value = savedTeam;

            loadData();
        });
    </script>
    <!-- Tippy.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />

    <!-- Popper.js (dependency) -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>

    <!-- Tippy.js JS -->
    <script src="https://unpkg.com/tippy.js@6"></script>
</body>

</html>
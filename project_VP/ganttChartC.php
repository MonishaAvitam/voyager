<?php

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './login_header.php';

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

include '../include/viewportTopBar.php';
include '../add_project.php';

?>

<!-- DHTMLX Gantt CSS & JS -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* Custom styling (if needed) */
</style>

<body>

<!-- Scale Filter Dropdown -->
<div id="filter-container" style="margin-right: 80px !important;" class="position-fixed top-0 end-0 mt-2 me-5 p-2 shadow-sm rounded z-1 bg-primary text-white">
    <label for="scaleSelect" class="fw-bold me-2">Show by:</label>
    <select id="scaleSelect" class="form-select form-select-sm d-inline-block w-auto">
        <option value="week">Week</option>
        <option value="month" selected>Month</option>
        <option value="year">Year</option>
    </select>
</div>

<!-- Gantt Chart Container -->
<div id="gantt_here"></div>

<!-- Edit Project Form (initially hidden) -->
<div id="editProjectForm" class="modal fade" tabindex="-1" aria-labelledby="editProjectFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProjectFormLabel">Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="projectForm">
                    <div class="mb-3">
                        <label for="projectName" class="form-label">Project Name</label>
                        <input type="text" class="form-control" id="projectName" required>
                    </div>
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <select class="form-select" id="state" required>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Project</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- DHTMLX Gantt JS and other libraries -->
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script>
    let selectedTaskId = null;  // To store selected task ID

    // Initialize Gantt chart with default scale (month)
    function setGanttScale(scale) {
        let scalesConfig = [];

        if (scale === "week") {
            scalesConfig = [
                { unit: "week", step: 1, format: "Week %W" },
                { unit: "day", step: 1, format: "%D, %d" }
            ];
        } else if (scale === "year") {
            scalesConfig = [
                { unit: "year", step: 1, format: "%Y" },
                { unit: "month", step: 1, format: "%M" }
            ];
        } else {
            scalesConfig = [
                { unit: "month", step: 1, format: "%F, %Y" },
                { unit: "day", step: 1, format: "%d" }
            ];
        }

        gantt.config.scales = scalesConfig;
        gantt.render();  // Re-render the chart to apply changes
    }

    // Set initial scale to 'month' (default option)
    setGanttScale("month");

    // Initialize Gantt chart configuration
    gantt.config.columns = [
        { name: "project_id", label: "Project ID", align: "center", width: 100 },
        { name: "text", label: "Project Name", tree: true, width: 200 },
        { name: "state", label: "State", align: "center", width: 50 },
        { name: "start_date", label: "Start Date", align: "center", width: 100 },
        { name: "end_date", label: "End Date", align: "center", width: 100 }
    ];

    gantt.config.open_tree_initially = true;
    gantt.config.autosize = "y";
    gantt.config.show_links = true;
    gantt.config.drag_links = true;
    gantt.config.drag_tasks = true;
    gantt.config.editable = true;
    gantt.config.add_task_button = true;
    gantt.config.add_link_button = true;

    // Fetch Gantt Data from API
    fetch("./apis/gantt.php")
        .then(response => response.json())
        .then(data => {
            let tasks = { data: [], links: [] };

            data.forEach(project => {
                let startDate = project.start_date ? new Date(project.start_date) : new Date();
                let endDate = project.end_date ? new Date(project.end_date) : startDate;

                if (isNaN(startDate.getTime())) {
                    console.error("Invalid start date:", project.start_date);
                    startDate = new Date();
                }
                if (isNaN(endDate.getTime())) {
                    console.error("Invalid end date:", project.end_date);
                    endDate = startDate;
                }

                let formattedStart = gantt.date.date_to_str("%d-%m-%Y")(startDate);
                let formattedEnd = gantt.date.date_to_str("%d-%m-%Y")(endDate);

                tasks.data.push({
                    id: project.project_id,
                    project_id: project.project_id,
                    text: project.project_name,
                    start_date: formattedStart,
                    end_date: formattedEnd,
                    state: project.state,
                    progress: project.progress ? project.progress / 100 : 0,
                    parent: project.revision_project_id || 0,
                    engineer_name: project.engineer_name || "Not Assigned",
                    open: false
                });

                if (project.revision_project_id) {
                    tasks.links.push({
                        id: "link_" + project.project_id,
                        source: project.revision_project_id,
                        target: project.project_id,
                        type: "1"
                    });
                }
            });

            gantt.init("gantt_here");
            gantt.parse(tasks);
        })
        .catch(error => console.error("Error fetching Gantt data:", error));

    // Event Listener for Scale Filter Dropdown
    document.getElementById("scaleSelect").addEventListener("change", function() {
        setGanttScale(this.value);
    });

    // Event listener for task click to open the edit form
    gantt.attachEvent("onTaskClick", function(id) {
        selectedTaskId = id;
        // Fetch task details
        const task = gantt.getTask(id);
        document.getElementById("projectName").value = task.text;
        document.getElementById("startDate").value = gantt.date.str_to_date("%d-%m-%Y")(task.start_date).toISOString().split('T')[0];
        document.getElementById("endDate").value = gantt.date.str_to_date("%d-%m-%Y")(task.end_date).toISOString().split('T')[0];
        document.getElementById("state").value = task.state;

        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editProjectForm'));
        editModal.show();
    });

    // Handle form submission
    document.getElementById("projectForm").addEventListener("submit", function(e) {
        e.preventDefault();
        
        const updatedProject = {
            project_id: selectedTaskId,
            project_name: document.getElementById("projectName").value,
            start_date: document.getElementById("startDate").value,
            end_date: document.getElementById("endDate").value,
            state: document.getElementById("state").value
        };

        // Send updated data to server (API endpoint for updating the project)
        fetch("/apis/update_project.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(updatedProject)
        })
        .then(response => response.json())
        .then(data => {
            // If successful, update the task in the Gantt chart and close the modal
            if (data.success) {
                const task = gantt.getTask(selectedTaskId);
                task.text = updatedProject.project_name;
                task.start_date = updatedProject.start_date;
                task.end_date = updatedProject.end_date;
                task.state = updatedProject.state;

                gantt.updateTask(selectedTaskId);  // Update task on the chart
                bootstrap.Modal.getInstance(document.getElementById('editProjectForm')).hide();  // Hide modal
            } else {
                alert('Failed to update the project');
            }
        })
        .catch(error => {
            console.error('Error updating project:', error);
        });
    });

    // Adjust Gantt Chart Size on Window Resize
    window.addEventListener("resize", function() {
        gantt.setSizes();
    });
</script>

</body>
</html>

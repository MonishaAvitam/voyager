<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gantt Chart</title>

    <!-- DHTMLX Gantt CSS & JS -->
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

    <style>


    </style>
</head>

<body>
    <div id="gantt_here"></div>

    <script>
        gantt.config.scale_unit = "month";
        gantt.config.date_scale = "%F, %Y"; // Month and Year

        gantt.config.columns = [{
                name: "text",
                label: "Project Name",
                tree: true,
                width: 200
            },
            {
                name: "start_date",
                label: "Start Date",
                align: "center",
                width: 100
            },
            {
                name: "end_date",
                label: "End Date",
                align: "center",
                width: 100
            }
        ];

        gantt.config.subscales = [{
            unit: "day",
            step: 1,
            date: "%d"
        }];

        gantt.config.open_tree_initially = true;
        gantt.config.autosize = "y";
        gantt.config.show_links = true;
        gantt.config.drag_links = true;
        gantt.config.drag_tasks = true;
        gantt.config.editable = true;
        gantt.config.add_task_button = true;
        gantt.config.add_link_button = true;

        // Tooltip to show engineer name
        gantt.templates.tooltip_text = function(start, end, task) {
            return `<b>Project:</b> ${task.text}<br><b>Engineer:</b> ${task.engineer_name || "Not Assigned"}`;
        };

        fetch("./apis/gantt.php")
            .then(response => response.json())
            .then(data => {
                let tasks = {
                    data: [],
                    links: []
                };

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
                        text: project.project_name,
                        start_date: formattedStart,
                        end_date: formattedEnd,
                        progress: project.progress ? project.progress / 100 : 0,
                        parent: project.revision_project_id || 0,
                        engineer_name: project.engineer_name || "Not Assigned",
                        open: true
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

        window.addEventListener("resize", function() {
            gantt.setSizes();
        });
    </script>


</body>

</html>
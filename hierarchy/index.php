<?php


require '../authentication.php'; // admin authentication check 
require '../conn.php';


$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];





// check admin
$user_role = $_SESSION['user_role'];


// Handle Add Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!empty($_POST['employee']) && strpos($_POST['employee'], '|') !== false) {
    list($id, $name) = explode('|', $_POST['employee']);
    $id = intval($id); // Ensure ID is an integer
    $name = htmlspecialchars($name);
  }
  $designation = $_POST['designation'] ?? '';
  $department = ($_POST['team'] === 'other' && !empty($_POST['new_team'])) ? $_POST['new_team'] : ($_POST['team'] ?? '');
  $parent = !empty($_POST['parent']) ? $_POST['parent'] : null;

  if (!empty($name) && !empty($designation) && !empty($department)) {
    $stmt = $conn->prepare("INSERT INTO hierarchy (name,user_id, designation, department, parent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sissi", $name, $id, $designation, $department, $parent);
  } else {
    die("Error: Invalid action or missing data.");
  }

  if (!$stmt) {
    die("Error preparing query: " . $conn->error);
  }

  if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
  }

  $stmt->close();
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}


$sql = "SELECT h.*, 
               (SELECT MAX(p.end_date) 
                FROM projects p 
                WHERE p.assign_to_id = h.user_id) AS last_end_date
        FROM hierarchy h
        ORDER BY h.parent ASC";

$result = $conn->query($sql);

$hierarchy = [];
while ($row = $result->fetch_assoc()) {
  $hierarchy[] = [
    'id' => $row['id'],
    'name' => $row['name'],
    'designation' => $row['designation'],
    'department' => $row['department'],
    'parent' => $row['parent'],
    'date' => $row['last_end_date'] // Include last project end date
  ];
}
// Fetch employees from tbl_admin
$sql = "SELECT * FROM tbl_admin ORDER BY fullname ASC";
$result = $conn->query($sql);
$employees = [];

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $employees[] = [
      "id" => intval($row['user_id']),
      "fullname" => htmlspecialchars($row['fullname']),
      "designation" => htmlspecialchars($row['designation'] ?? ''),
      "department" => htmlspecialchars($row['department'] ?? '')
    ];
  }
}


$jsonData = json_encode($hierarchy);
$jsonEmployees = json_encode($employees);
?>


<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSA Employee Time Schedule</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- Google Charts -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

  <style>
  

    /* Center hierarchy tree */
    #chart_div {
      width: 100%;
      display: flex;
      justify-content: center;
      margin-top: 30px;
     
    }

    /* Custom Styling */
    .google-visualization-orgchart-node {
      /* background: linear-gradient(135deg, rgb(68, 18, 102), rgb(221, 11, 98)) !important;
      color: white !important; */
      border-radius: 10px !important;
      text-align: center !important;
      font-size: 14px !important;
      font-weight: bold !important;
      transition: transform 0.3s ease-in-out !important;
      cursor: pointer !important;
      border: none !important;
      width: 200px !important;
    }

    .google-visualization-orgchart-nodesel {
      background: linear-gradient(135deg, rgb(255, 255, 255), rgb(255, 255, 255)) !important;
      color: black !important;
      text-align: center !important;
      font-size: 14px !important;
      font-weight: bold !important;
      transition: transform 0.3s ease-in-out !important;
      cursor: pointer !important;
      border: none !important;
      width: 200px !important;
    }

    .google-visualization-orgchart-linebottom {
    border-bottom: 2px solid rgb(0, 0, 0) !important;
  }

  .google-visualization-orgchart-lineright {
    border-right: 2px solid rgb(0, 0, 0) !important;
  }

  .google-visualization-orgchart-lineleft {
    border-left: 2px solid rgb(0, 0, 0) !important;
  }

  /* Add more dark-mode specific styles here */
  .dark-mode {
    background-color: #121212;
    color: white;
  }



    .google-visualization-orgchart-node:hover {
      transform: scale(1.05) !important;
      box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.3) !important;
    }
  </style>
</head>


  <div class="container-fluid">
    <h2 class="text-center mt-4">CSA Employee Time Schedule</h2>

    <button class="btn btn-success mb-3" onclick="openForm('add')">+ Add New Member</button>
    <script>
      function openForm(action) {
        new bootstrap.Modal(document.getElementById("addModal")).show();
      }
    </script>
    <div class="card card-body">
    <div  id="chart_div"></div>
    </div>
  </div>

  <!-- Popup Modal for Add/Edit -->
  <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <label>Name:</label>
            <select class="form-select" id="employee" name="employee" required>
              <option value="">Select Employee</option>
              <?php foreach ($employees as $employee) : ?>
                <option value="<?= htmlspecialchars($employee['id']) ?>|<?= htmlspecialchars($employee['fullname']) ?>">
                  <?= htmlspecialchars($employee['fullname']) ?>
                </option>
              <?php endforeach; ?>
            </select>


            <label class="">Designation:</label>
            <input type="text" class="form-control" id="designation" name="designation" required>

            <label>Team:</label>
            <select class="form-select" id="team" name="team" onchange="toggleNewTeamInput()">
              <option value="">Select Team</option>
              <?php
              $teams = array_unique(array_column($hierarchy, 'department'));
              foreach ($teams as $team) : ?>
                <option value="<?= htmlspecialchars($team) ?>"><?= htmlspecialchars($team) ?></option>
              <?php endforeach; ?>
              <option value="other">Other (Add New Team)</option>
            </select>

            <input type="text" class="form-control mt-2" id="new_team" name="new_team" placeholder="Enter New Team Name" style="display: none;">

            <label>Manager (Parent Node):</label>
            <select class="form-select" id="parent" name="parent">
              <option value="">None (Top Level)</option>
              <?php foreach ($hierarchy as $member) : ?>
                <option value="<?= htmlspecialchars($member['id']) ?>"><?= htmlspecialchars($member['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </form>

        <script>
          function toggleNewTeamInput() {
            var teamSelect = document.getElementById("team");
            var newTeamInput = document.getElementById("new_team");

            if (teamSelect.value === "other") {
              newTeamInput.style.display = "block";
              newTeamInput.setAttribute("required", "required");
            } else {
              newTeamInput.style.display = "none";
              newTeamInput.removeAttribute("required");
            }
          }
        </script>

      </div>
    </div>
  </div>


  <!-- Bootstrap & JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


  <!-- Google Chart Script -->
  <div id="chart_div"></div>

<script>
  google.charts.load('current', {
    packages: ["orgchart"]
  });
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Name');
    data.addColumn('string', 'Manager');

    var hierarchyData = <?php echo json_encode($hierarchy, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
    const currentDate = new Date();

    hierarchyData.forEach(node => {
      // Handle for multiple nodes for the same person (e.g., different teams)
      // Create multiple "node" entries if needed
      let parentID = node.parent ? node.parent.toString() : '';

      // Convert node date to a JavaScript Date object
      let nodeDate = new Date(node.date);
      let color = nodeDate > currentDate ? '#D70654' : '#2C3930';

      // Add main node (e.g., first role/team)
      data.addRow([{
          v: node.id.toString(),
          f: `<div style="background:${color}; color:white;">
                <strong>${node.name}</strong><br>
                <small>${node.designation} ( ${node.department} )</small><br>
                <small>Busy Till ${node.date}</small>
              </div>`
        },
        parentID
      ]);

      // If the person has multiple teams or roles, create additional nodes for them
      if (node.additionalRoles) {
        node.additionalRoles.forEach(role => {
          // Assign a new manager if the role has a different parent (team/department)
          let newParentID = role.parent ? role.parent.toString() : parentID;

          data.addRow([{
              v: `${node.id}-${role.team}`,  // Adding the team name or role as part of the ID to make it unique
              f: `<div style="background:${color}; color:white;">
                    <strong>${node.name}</strong><br>
                    <small>${role.team} (${node.department})</small><br>
                    <small>Busy Till ${node.date}</small>
                  </div>`
            },
            newParentID
          ]);
        });
      }
    });

    var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
    chart.draw(data, {
      allowHtml: true
    });
  }
</script>




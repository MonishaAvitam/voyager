<?php

use Google\Service\Datastore\Sum;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../authentication.php'; // admin authentication check 
include '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}


// check admin
$user_role = $_SESSION['user_role'];

if ($user_role == 4) {
    header('Location:./payslip.php');
}

include './include/sidebar.php';

$customer_id = $_GET['customerId'];

// Assuming $conn is your database connection object
$sql = "SELECT * FROM contacts WHERE contact_id  = $customer_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customer_name = $row['customer_name'];
    }
} else {
    echo "<option value=''>No customers found</option>";
}


?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Filter By Date</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <label class="control-label">Start Date</label>
                    <input type="date" name="startDate" id="" class="form-control">
                    <label class="control-label mt-3">End Date</label>
                    <input type="date" name="endDate" id="" class="form-control">

            </div>
            <div class="modal-footer">
                <button type="button" onclick="clearFilter()" class="btn btn-danger" data-dismiss="modal">Clear Filter</button>
                <button class="btn btn-primary " type="submit">Apply</button>
                <script>
                    function clearFilter() {
                        var getUrl = window.location.href;
                        var url = new URL(getUrl);

                        // Remove start_date parameter
                        url.searchParams.delete("start_date");

                        // Remove endDate parameter
                        url.searchParams.delete("endDate");

                        // Reload the page with the updated URL
                        window.location.href = url.toString();
                    }
                </script>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- dashboard content  -->

<div class="container-fluid justify-content-center d-flex flex-column align-content-center">
    <div class="d-sm-flex align-items-center  justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer : <?php echo $customer_name  ?></h1>
        <button type="button" class="btn btn-primary mb-5" data-toggle="modal" data-target="#exampleModal">
            Filter By Date
        </button>
    </div>

    <div class=" container-fluid bg-light rounded row d-flex align-items-end mb-5">

        <div class=" col-4 mt-5 mb-5">
            <canvas id="doughnut"></canvas>
        </div>
        <div class=" col-8 mt-5 mb-5">
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Projects Table</h6>

        </div>




        <div class="card-body">
            <div class="table-responsive p-3">
                <table class="table table-striped table-bordered table-sm" id="dataTable" name="dataTable">
                    <thead>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Customer Name</th>
                            <th>Team</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Project Number</th>
                            <th>Project Title</th>
                            <th>Customer Name</th>
                            <th>Team</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>

                        <!-- ... Your table rows ... -->
                        <?php

                        $start_date = isset($_POST['startDate']) ? $_POST['startDate'] : (isset($_GET['start_date']) ? $_GET['start_date'] : '2023-01-01');

                        $end_date =   isset($_POST['endDate'])   ? $_POST['endDate']   : (isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d'));

                        // Assuming $mysqli is your mysqli connection object
                        $sql = "SELECT p.project_id,p.project_name,p.p_team ,p.urgency,p.reopen_status,p.start_date FROM projects p WHERE p.contact_id = ? AND         start_date >= '$start_date' AND start_date <= '$end_date'

                                UNION 
                                SELECT d.project_id,d.project_name,d.p_team,d.urgency,d.reopen_status,d.start_date FROM deliverable_data d WHERE d.contact_id = ? AND    start_date >= '$start_date' AND start_date <= '$end_date'";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ii', $customer_id, $customer_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $dates = array();
                        $liveProjects = array();
                        $closedProjects = array();

                        $serial = 1;
                        $num_row = $result->num_rows;
                        if ($num_row == 0) {
                            echo '<tr><td colspan="7">No projects were found</td></tr>';
                        } else {
                            while ($row = $result->fetch_assoc()) {

                                // Extract the month from start_date
                                $startDate = date('F', strtotime($row['start_date']));

                                // Initialize count for the month if it doesn't exist
                                if (!isset($projectsByMonth[$startDate])) {
                                    $projectsByMonth[$startDate] = 0;
                                }

                                // Increment the count for the month
                                $projectsByMonth[$startDate]++;

                                if ($row['urgency'] != 'purple') {
                                    $liveProjects[] = $row['project_id'];
                                } elseif ($row['urgency'] = 'purple') {
                                    $closedProjects[] = $row['project_id'];
                                }





                        ?>


                                <tr>


                                    <td style="background-color: <?php echo $row['urgency']; ?>; text-align: center; width: 5%; border-radius: 5px; color: <?php echo ($row['urgency'] === 'white' || $row['urgency'] === 'yellow') ? '#000' : '#fff'; ?>">
                                        <?php echo $row['project_id']; ?>
                                        <span class="badge badge-pill badge-danger"><?php echo $row['reopen_status']; ?></span>

                                    </td>

                                    <td>
                                        <input type="text" name="project_name" value="<?php echo $row['project_name']; ?>" style="border: none; background-color:transparent; outline: none; display:none">
                                        <label for=""><?php echo $row['project_name']; ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="customer_name" value="<?php echo $customer_name ?>" style="border: none; background-color:transparent;  outline: none; display:none">
                                        <label for=""><?php echo $customer_name; ?></label>
                                    </td>

                                    <td>
                                        <input type="text" name="p_team" value="<?php echo $row['p_team']; ?>" style="border: none; background-color: transparent; outline: none;display:none">
                                        <label for=""><?php echo $row['p_team']; ?></label>
                                    </td>







                                    <td class="d-flex justify-content-around">

                                        <a title="View" class="mr-2" href="./task-details.php?project_id=<?php echo $row['project_id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                                                <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2zm0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14z" />
                                            </svg>
                                        </a>&nbsp;&nbsp;
                                    </td>


                                </tr>



                        <?php


                            }
                        }

                        $lineDatalabel = json_encode($dates);
                        @$lineData = json_encode($projectsByMonth);

                        ?>

                    </tbody>

                </table>
            </div>
        </div>
    </div>



    <script>
        var url_string = window.location.href;
        var url = new URL(url_string);
        var tp = url.searchParams.get("tp");
        var tlp = url.searchParams.get("top");
        var tcp = url.searchParams.get("tcp");


        const ctx = document.getElementById('doughnut');
        const data1 = {
            labels: [
                'Total projects',
                'Open Projects',
                'Closed Projects'
            ],
            datasets: [{
                labels: [
                    'Total projects',
                    'Open Projects',
                    'Closed Projects'
                ],

                data: [

                    <?php
                    $totalProjects = count($liveProjects) + count($closedProjects);
                    ?>

                    <?php echo $totalProjects ?>,
                    <?php echo count($liveProjects) ?>,
                    <?php echo count($closedProjects) ?>




                ],

                backgroundColor: [
                    'blue',
                    'green',
                    'purple'
                ],
                hoverOffset: 4
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: data1,
        });


        const lineChart = document.getElementById('lineChart');

        <?php
        echo "const data2 = {
                    labels: " . $lineDatalabel . ",
                    datasets: [{
                        label: 'Monthly Project',
                        data:$lineData,
                        borderColor: 'rgb(75, 192, 192)',
                        fill: false,
                        hoverOffset: 4,
                        tension: 0.1
                    }]
                };";

        ?>

        new Chart(lineChart, {
            type: 'line',
            data: data2,
        });
    </script>

</div>





<?php
include './include/footer.php';
?>
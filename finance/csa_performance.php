<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
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



?>


<script src="https://cdn.canvasjs.com/ga/canvasjs.min.js"></script>
<script src="https://cdn.canvasjs.com/ga/canvasjs.stock.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>




<!-- dashboard content  -->

<div class="container-fluid ">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">CSA PERFORMANCES</h1>
        <button type="button" class="btn btn-primary mb-5" data-toggle="modal" data-target="#exampleModal">
            Filter By Date
        </button>
    </div>
    <div class="mb-5 p-5 rounded" style="background-color: white; ">
        <canvas id="doughnut"></canvas>
    </div>


</div>


<style>
    .order-card {
        color: #fff;
    }

    .bg-c-blue {
        background: linear-gradient(45deg, #4099ff, #73b4ff);
    }

    .bg-c-green {
        background: linear-gradient(45deg, #2ed8b6, #59e0c5);
    }

    .bg-c-yellow {
        background: linear-gradient(45deg, #FFB64D, #ffcb80);
    }

    .bg-c-pink {
        background: linear-gradient(45deg, #FF5370, #ff869a);
    }

    .navy {
        background: #124076;
    }


    .card {
        border-radius: 5px;
        -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
        box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
        border: none;
        margin-bottom: 30px;
        -webkit-transition: all 0.3s ease-in-out;
        transition: all 0.3s ease-in-out;
    }

    .card .card-block {
        padding: 25px;
    }

    .order-card i {
        font-size: 26px;
    }

    .f-left {
        float: left;
    }

    .f-right {
        float: right;
    }

    .white-hr {
        border: 1px solid rgb(255, 255, 255);
        background-color: rgb(255, 255, 255);
        display: block;
    }
</style>

<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<div class="container">



    <!-- Modal -->
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary " type="submit">Apply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="row">



        <?php
        $start_date = isset($_POST['startDate']) ? $_POST['startDate'] : date('2023-01-01');
        $end_date = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-d');

        $sql = "SELECT 
    c.customer_name,
    c.contact_id,
    COALESCE(p.live_projects, 0) AS live_projects,
    COALESCE(p.closed_projects, 0) AS closed_projects,
    COALESCE(d.live_deliverable, 0) AS live_deliverable,
    COALESCE(d.closed_deliverable, 0) AS closed_deliverable
FROM contacts c
LEFT JOIN (
    SELECT 
        contact_id,
        SUM(CASE WHEN urgency != 'purple' THEN 1 ELSE 0 END) AS live_projects,
        SUM(CASE WHEN urgency = 'purple' THEN 1 ELSE 0 END) AS closed_projects

    FROM projects
        WHERE start_date >= '$start_date' AND start_date <= '$end_date'
    GROUP BY contact_id
) p ON c.contact_id = p.contact_id
LEFT JOIN (
    SELECT 
        contact_id,
        SUM(CASE WHEN urgency != 'purple' THEN 1 ELSE 0 END) AS live_deliverable,
        SUM(CASE WHEN urgency = 'purple' THEN 1 ELSE 0 END) AS closed_deliverable

    FROM deliverable_data
                WHERE start_date >= '$start_date' AND start_date <= '$end_date'
    GROUP BY contact_id
) d ON c.contact_id = d.contact_id;
";

        $customer_name = array();

        $stmt = $conn->query($sql);
        if ($stmt->num_rows > 0) {

            while ($row = $stmt->fetch_assoc()) {


                $live_projects =  $row['live_deliverable'] + $row['live_projects'];
                $closed_projects =   $row['closed_deliverable'] + $row['closed_projects'];
                $total_projects = $live_projects + $closed_projects;






        ?>


                <div style="cursor: pointer;" class="col-md-4 col-xl-3" onclick="location.href='csa_perfomance_detail.php?customerId=<?php echo $row['contact_id'] ?>&start_date=<?php echo $start_date ?>&endDate=<?php echo $end_date ?>' ">
                    <div class="card 
                    <?php $colour = ['bg-c-pink', 'bg-c-yellow', 'bg-c-green', 'bg-c-blue'];

                    $random_colour = $colour[array_rand($colour)];
                    $live_projects =  $row['live_deliverable'] + $row['live_projects'];
                    $closed_projects =   $row['closed_deliverable'] + $row['closed_projects'];
                    $total_projects = $live_projects + $closed_projects;
                    ?> order-card navy">
                        <div class="card-block">
                            <h6 class="m-b-20 font-weight-bold h6 "><?php echo $row['customer_name'] ?></h6>
                            <hr class="sidebar-divider white-hr ">

                            <p class="m-b-0">Total Projects<span class="f-right"><?php echo $total_projects ?></span></p>
                            <p class="m-b-0">Live Projects<span class="f-right"><?php echo $live_projects ?></span></p>
                            <p class="m-b-0">Closed Projects<span class="f-right"><?php echo $closed_projects ?></span></p>
                        </div>
                    </div>
                </div>

                <?php

                if (!isset($customer_name[$row['customer_name']])) {
                    $customer_name[] = $row['customer_name'];
                    $total_projects_data[] = $total_projects;
                }


                ?>




            <?php

            }
            // print_r($customer_name);
            // print_r($total_projects_data);
            ?>
            <script>
                const ctx = document.getElementById('doughnut');
                <?php
                echo "  const data = {
                    labels:" . json_encode($customer_name) . ",
                    datasets: [{
                        label: 'Total Projects',
                        data: " . json_encode($total_projects_data) . ",
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(75, 192, 192)',
                            'rgb(255, 205, 86)',
                            'rgb(201, 203, 207)',
                            'rgb(54, 162, 235)'
                        ]
                    }]
                }; "

                ?>

                new Chart(ctx, {
                    type: 'line',
                    data: data,
                });
            </script>
        <?php

        } else {

        ?>

            <div class="col-md-4 col-xl-3">
                <div class="card 
                    <?php
                    // $colour = ['bg-c-pink', 'bg-c-yellow', 'bg-c-green', 'bg-c-blue'];
                    // $random_colour = $colour[array_rand($colour)];
                    echo 'bg-c-blue';
                    ?> order-card">
                    <div class="card-block">
                        <h6 class="m-b-20 "><?php echo "No Projects" ?></h6>
                        <hr class="sidebar-divider">

                        <p class="m-b-0">Total Projects<span class="f-right"><?php echo 0 ?></span></p>
                        <p class="m-b-0">Live Projects<span class="f-right"><?php echo 0 ?></span></p>
                        <p class="m-b-0">Closed Projects<span class="f-right"><?php echo 0 ?></span></p>
                    </div>
                </div>
            </div>



        <?php

        }
        include './include/footer.php';
        ?>
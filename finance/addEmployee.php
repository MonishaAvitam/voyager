<div class="modal fade" id="add_employee" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header ">

                <h5 class="modal-title" id="exampleModalLabel">New Employee</h5>

                <button class="close" type="button" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">×</span>

                </button>

            </div>

            <div class="modal-body">

                <div class="row">

                    <div class="col-md-12">

                        <div class="form-container">

                            <form role="form" action="" method="post" autocomplete="off">

                                <div class="form-horizontal">

                                    <div class="form-group">

                                        <label class="control-label">Full Name</label>

                                        <div class="">
                                            <?php
                                            $sql = "SELECT * FROM tbl_admin";
                                            $info = $obj_admin->manage_all_info($sql);
                                            $serial  = 1;
                                            $num_row = $info->rowCount();
                                            if ($num_row == 0) {
                                                echo '<tr><td colspan="7">No Employee were found</td></tr>';
                                            }
                                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                                <select>
                                                    <option value="<?php echo $row['id'] ?>"><?php echo $row['fullname']  ?></option>
                                                </select>
                                            <?php  }  ?>
                                        </div>

                                    </div>

                                    <div class="form-group">

                                        <label class="control-label ">Email ID</label>

                                        <div class="">

                                            <input type="email" placeholder="xxxxx@xxx.xx" id="email_id" name="email_id" list="expense" class="form-control" id="default" required>


                                        </div>

                                    </div>

                                    <div class="form-group">

                                        <label class="control-label">Contact </label>

                                        <div class="">

                                            <input type="number" placeholder="Contact Number" id="contact_number" name="contact_number" list="expense" class="form-control" id="default" required>


                                        </div>

                                    </div>

                                    <div class="form-group">

                                        <label class="control-label">Salary</label>

                                        <div class="">

                                            <input type="number" placeholder="Salary (INR)" id="salary" name="salary" list="expense" class="form-control" id="default" required>


                                        </div>

                                    </div>



                                    <div class="form-group">

                                        <label class="control-label">Other Expense</label>

                                        <div class="">
                                            <input class="form-control" type="number" name="other_expense" id="other_expense" value="0">

                                        </div>

                                    </div>



                                    <div class="modal-footer">

                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>

                                        <!-- <a class="btn btn-primary" href="login.html">Logout</a> -->

                                        <button type="submit" name="new_employee" class="btn btn-secondary">Register</button>

                                    </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_employee"])) {

    // Check if the form was submitted and the "Create Project" button was clicked

    $full_name = $_POST['full_name'];

    $email_id = $_POST['project_details'];

    $contact_number = $_POST['contact_id'];

    $doj = $_POST['p_team'];

    $salary = $_POST['p_team'];
    $expencee = $_POST['p_team'];




    // Assuming you have received the selected value in days from the form
    $raw_hours = (int)$_POST['t_end_date'];
    $estimated_hours = $raw_hours . "H";


    $number = intval($_POST['t_end_date']); // Ensure it's an integer




    // Set the initial value of $totalHours based on the selected value
    if ($number >= 0 && $number <= 15) {
        $totalHours = 5;
    } elseif ($number >= 16 && $number <= 40) {
        $totalHours = 10;
    } elseif ($number >= 41 && $number <= 80) {
        $totalHours = 20;
    } else {
        $totalHours = 40;
    }
    // Create a DateTime object for the current date (today)
    $currentDateTime = new DateTime();

    // Calculate the end date by adding the selected number of days to the current date
    $endDateTime = clone $currentDateTime;
    $endDateTime->add(new DateInterval("P" . $totalHours . "D"));

    // Format the end date as a string in the desired format (Y-m-d)
    $t_end_date = $endDateTime->format("Y-m-d");





    $project_manager_combined = $_POST['project_manager'];

    list($project_managers_id, $project_manager) = explode('|', $project_manager_combined);

    if ($_POST['assign_to'] == "S/P") {
        $assign_to_username = "S/P";
    } elseif ($_POST['assign_to'] == "N/A") {
        $assign_to_username = "N/A";
    } else {
        $assign_to_combined = $_POST['assign_to'];

        list($assign_to_id, $assign_to_username) = explode('|', $assign_to_combined);
    }


    $project_status = $_POST['project_status'];

    include 'conn.php';

    // Use prepared statements to prevent SQL injection

    $sql = "INSERT INTO projects (project_name, project_details, contact_id, p_team, project_manager, project_managers_id, start_date, end_date, EPT, assign_to_id, assign_to, urgency)

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ssssssssssss", $new_project_name, $project_details, $contact_id, $p_team, $project_manager, $project_managers_id, $t_start_date, $t_end_date, $estimated_hours, $assign_to_id, $assign_to_username, $project_status);

    if ($stmt->execute()) {

        // Data was inserted successfully

        $msg_success = "Project created successfully!";

        $stmt->close();

        $conn->close();

        header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
    } else {

        // Error occurred while inserting data

        $msg_error = "Error: " . $conn->error;

        $stmt->close();

        $conn->close();

        header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
    }
}

?>

<!-- end of projects section -->
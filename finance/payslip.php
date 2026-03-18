<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

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


if (isset($_GET['error']) && $_GET['error'] == 'missing_employee') {
    echo "<script>
        Swal.fire({
            title: 'Email Not Sent',
            text: 'Enter the data for Reimbursement',
            icon: 'error',
            confirmButtonText: 'Ok',
            allowOutsideClick: false, // Prevent closing by clicking outside
            allowEscapeKey: false,    // Prevent closing with ESC key
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payslip.php after pressing OK
                window.location.href = 'payslip.php';
            }
        });
    </script>";
}
if (isset($_GET['success']) && $_GET['success'] == 'emails_sent') {
    echo "<script>
        Swal.fire({
            title: 'Email Sent Successfully',
            text: '',
            icon: 'success',
            confirmButtonText: 'Ok',
            allowOutsideClick: false, // Prevent closing by clicking outside
            allowEscapeKey: false,    // Prevent closing with ESC key
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payslip.php after pressing OK
                window.location.href = 'payslip.php';
            }
        });
    </script>";
}
// check admin
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';

?>


<style>
    #dataTable_filter {
        position: relative;
        /* make container relative for absolute positioning */
    }

    #dataTable_filter>button {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }
</style>

<script>
    function updateUrl(Employee_id) {
        // Parse the current URL's query parameters
        const urlParams = new URLSearchParams(window.location.search);

        // Set the 'progress_id' parameter with the specified Employee_id
        urlParams.set('Employee_id', Employee_id);

        // Get the updated query string
        const updatedQueryString = urlParams.toString();

        // Construct the new URL with the updated query string
        const newUrl = window.location.pathname + '?' + updatedQueryString;

        // Use pushState to update the URL without reloading the page
        window.history.pushState({
            Employee_id: Employee_id
        }, '', newUrl);
    }
</script>

<!-- dashboard content  -->

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payslip</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <div>
                <button type="button" class="btn btn-primary" onclick="submitForm('autoLeaveCalcl.php')">Get Leave
                    Data</button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cleardata">Clear
                    Data</button>
                <form method="POST" style="display:inline;"
                    onsubmit="return confirm('Are you sure you want to Auto Fill the Table?');">
                    <input type="hidden" name="set_zero_reimbursements_all" value="1">
                    <button type="submit" class="btn btn-secondary">Auto-Fill</button>
                </form>


            </div>

            <div class="d-flex flex-column">
                <h3 style="text-align: center; cursor: pointer;" class="p-2" id="payPeriodHeader" data-toggle="modal"
                    data-target="#monthModal">
                    Pay Period (
                    <?php
                    $currentDate = new DateTime();

                    // Check if a stored month exists
                    if (isset($_COOKIE['selectedMonth'])) {
                        $selectedMonth = new DateTime($_COOKIE['selectedMonth']);
                    } else {
                        $selectedMonth = $currentDate; // Default to current date
                    }

                    // Calculate the last pay period (15th of last month to 14th of this month)
                    $lastMonth = clone $selectedMonth;
                    $lastMonth->modify('first day of this month')->modify('-1 month')->setDate($lastMonth->format('Y'), $lastMonth->format('m'), 15);

                    $presentMonth = clone $selectedMonth;
                    $presentMonth->modify('first day of this month')->setDate($presentMonth->format('Y'), $presentMonth->format('m'), 14);

                    echo $lastMonth->format('d/m/y') . '  TO  ' . $presentMonth->format('d/m/y');
                    ?> )
                </h3>
                <p class="text-warning text-center m-auto">*The month can be changeable</p>
            </div>
            <!-- Bootstrap Modal -->
            <div class="modal fade" id="monthModal" tabindex="-1" role="dialog" aria-labelledby="monthModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="monthModalLabel">Edit Pay Period</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label for="monthInput">Choose Month:</label>
                            <input type="month" id="monthInput" class="form-control"
                                value="<?php echo $selectedMonth->format('Y-m'); ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <form id="monthForm" method="post">
                                <input type="hidden" name="selectedMonth" id="hiddenMonthInput">
                                <button type="button" class="btn btn-primary" id="saveButton">Save changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <script>
                $(document).ready(function () {
                    const storedMonth = getCookie('selectedMonth');

                    if (storedMonth) {
                        updatePayPeriodHeader(storedMonth);
                        $('#monthInput').val(storedMonth);
                    }

                    $('#saveButton').on('click', function () {
                        const selectedMonth = $('#monthInput').val();

                        // Confirmation before proceeding
                        const confirmChange = confirm(
                            "You are changing the pay period. All existing payslip data will be lost. Do you want to continue?"
                        );

                        if (!confirmChange) {
                            return; // Stop if user clicks "Cancel"
                        }

                        // Set cookie and update header
                        setCookie('selectedMonth', selectedMonth, 30);
                        updatePayPeriodHeader(selectedMonth);
                        $('#monthModal').modal('hide');

                        // Submit hidden form to same page
                        $('#hiddenMonthInput').val(selectedMonth);
                        $('#monthForm').submit();
                    });


                    function updatePayPeriodHeader(selectedMonth) {
                        const date = new Date(selectedMonth + '-01');
                        const lastMonth = new Date(date);
                        lastMonth.setMonth(lastMonth.getMonth() - 1);
                        lastMonth.setDate(15);

                        const presentMonth = new Date(date);
                        presentMonth.setDate(14);

                        const formattedLastMonth =
                            `${lastMonth.getDate().toString().padStart(2, '0')}/${(lastMonth.getMonth() + 1).toString().padStart(2, '0')}/${lastMonth.getFullYear().toString().slice(-2)}`;
                        const formattedPresentMonth =
                            `${presentMonth.getDate().toString().padStart(2, '0')}/${(presentMonth.getMonth() + 1).toString().padStart(2, '0')}/${presentMonth.getFullYear().toString().slice(-2)}`;

                        $('#payPeriodHeader').html(
                            `Pay Period ( ${formattedLastMonth} TO ${formattedPresentMonth} )`);
                    }

                    function setCookie(name, value, days) {
                        const expires = new Date(Date.now() + days * 864e5).toUTCString();
                        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/';
                    }

                    function getCookie(name) {
                        return document.cookie.split('; ').reduce((r, v) => {
                            const parts = v.split('=');
                            return parts[0] === name ? decodeURIComponent(parts[1]) : r;
                        }, '');
                    }
                });
            </script>


            <?php
            require '../conn.php'; // Database connection
            
            if (isset($_POST['selectedMonth'])) {
                $selectedMonth = $_POST['selectedMonth'];

                // Delete all rows from csa_finance_payslip
                $conn->query("DELETE FROM csa_finance_payslip");

                // Delete batch_email rows only if Batch_no is empty or NULL
                $conn->query("DELETE FROM batch_email WHERE Batch_no IS NULL OR Batch_no = ''");

                // Optional: store the selected month in a session/cookie
                setcookie('selectedMonth', $selectedMonth, time() + (30 * 86400), "/");

                // Redirect to the same page to prevent resubmission
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            ?>

            <div class="d-flex justify-align-center align-items-center ">


                <button type="button" class="btn btn-success  float-right mr-2"
                    onclick="submitForm('generateExcel.php')">Export Excel</button>
                <button type="button" class="btn btn-warning  float-right mr-2"
                    onclick="submitForm('ofx_export.php')">Export OFX Template</button>
                <button type="button" class="btn float-right"
                    style="background: linear-gradient(90deg, #FF4D4D, #FF914D); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;"
                    onclick="submitForm('airwllax_export.php')">
                    Airwallex AUD / IND
                </button>

            </div>
        </div>

        <script>
            // Before submitting form, append all selected checkboxes from all pages to the form
            function submitForm(action) {
                if (action !== 'ofx_export.php') {
                    var checkboxes = document.querySelectorAll('#dataTable input[type="checkbox"]:checked');
                    if (checkboxes.length > 10) {
                        alert('You can only select up to 10 employees');
                        return;
                    }
                }

                const table = $('#dataTable').DataTable();
                const form = document.getElementById('myForm');

                // Remove any existing hidden inputs to prevent duplicates
                const oldInputs = document.querySelectorAll('input[name="selected_employees[]"][type="hidden"]');
                oldInputs.forEach(el => el.remove());

                // Get all rows and create hidden inputs for selected checkboxes
                table.rows().every(function () {
                    var row = this.node();
                    var checkbox = $(row).find('input[type="checkbox"]')[0];
                    if (checkbox && checkbox.checked) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'selected_employees[]';
                        hiddenInput.value = checkbox.value;
                        form.appendChild(hiddenInput);
                    }
                });

                // Set form action and submit
                form.action = action;
                form.submit();
            }
        </script>

        <div class="card-body ">
            <div class="mb-3">
                <label for="teamFilter" class="form-label">Select by Team:</label>
                <select id="teamFilter" class="form-select" style="width: 200px; display: inline-block;">
                    <option value="">All Teams</option>
                    <?php
                    // Mapping of team values to display names
                    $teamDisplayMap = [
                        'Industrial' => 'Industrial Team',
                        'Building' => 'Building Team',
                        'IT' => 'Robotics and Software Team',
                        'Accounts' => 'Management'
                    ];

                    // Fetch distinct teams for the dropdown
                    $teamSql = "SELECT DISTINCT a.p_team AS team 
                    FROM csa_finance_employee_info AS ei
                    LEFT JOIN tbl_admin a ON ei.tbl_admin_id = a.user_id";
                    $teamResult = $obj_admin->manage_all_info($teamSql);

                    while ($teamRow = $teamResult->fetch(PDO::FETCH_ASSOC)) {
                        if (!empty($teamRow['team'])) {
                            $teamValue = htmlspecialchars($teamRow['team']);
                            $teamLabel = isset($teamDisplayMap[$teamRow['team']])
                                ? $teamDisplayMap[$teamRow['team']]
                                : $teamRow['team']; // fallback to original name
                            echo '<option value="' . $teamValue . '">' . htmlspecialchars($teamLabel) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>


            <div class="table-responsive ">
                <form id="myForm" method="POST" action="bulk_email.php">
                    <table class="table table-bordered" id="dataTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th hidden>email</th>
                                <th>Bank Name</th>
                                <th>Account No</th>
                                <th>Salary</th>
                                <th>Sick Leave</th>
                                <th>Casual Leave</th>
                                <th>Reimbursement</th>
                                <th>Deductions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th hidden>email</th>
                                <th>Bank Name</th>
                                <th>Account No</th>
                                <th>Salary</th>
                                <th>Sick Leave</th>
                                <th>Casual Leave</th>
                                <th>Reimbursement</th>
                                <th>Deductions</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>

                        <tbody>
                            <?php
                            $sql = "SELECT ei.*, 
                    COALESCE(p.expenses, 'Enter 0 if reimbursement is not applicable.') AS expenses,
                    COALESCE(p.deduction, 0) AS deduction,
                    COALESCE(p.mail_status, 'mail not sent') AS mail_status,
                    a.p_team AS team
                    FROM csa_finance_employee_info AS ei
                    LEFT JOIN csa_finance_payslip p ON ei.employee_id = p.employee_id
                    LEFT JOIN tbl_admin a ON ei.tbl_admin_id = a.user_id";


                            $info = $obj_admin->manage_all_info($sql);
                            $serial = 1; // Initialize serial number
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="12">No Payslip were found</td></tr>';
                            }
                            while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $serial++; ?>
                                        <div style="display: flex; justify-content: center; align-items: center;">
                                            <input class="form-check" style="transform: scale(1.5);" type="checkbox"
                                                name="selected_employees[]" value="<?php echo $row['Employee_id']; ?>">
                                        </div>
                                        <div class="text-center pt-1">
                                            <span
                                                class="badge <?php echo $row['mail_status'] === 'sent' ? 'badge-success' : 'badge-danger'; ?>"
                                                style="font-size: 12px">
                                                <?php echo $row['mail_status']; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo $row['Name']; ?></td>
                                    <?php
                                    // Mapping of team values to display names
                                    $teamDisplayMap = [
                                        'Industrial' => 'Industrial Team',
                                        'Building' => 'Building Team',
                                        'IT' => 'Robotics and Software Team',
                                        'Accounts' => 'Management'
                                    ];
                                    ?>

                                    <td>
                                        <?php
                                        echo htmlspecialchars($teamDisplayMap[$row['team']] ?? $row['team'] ?? 'N/A');
                                        ?>
                                    </td>

                                    <td hidden><input type="email" name="email_id[]"
                                            value="<?php echo $row['email_id']; ?>"></td>
                                    <td><?php echo $row['bank_name']; ?></td>
                                    <td><?php echo "xxxxx" . substr($row['account_no'], -4); ?></td>
                                    <td class="salary" data-salary="<?php echo $row['salary']; ?>">xxxxxx</td>
                                    <td>
                                        <div data-toggle="modal" data-target=".sickLeaveModal" href="javascript:void(0);"
                                            onclick="updateUrl(<?php echo $row['Employee_id']; ?>)">
                                            <p>3/<?php echo (isset($row['SL']) && $row['SL'] != 0 ? ($row['SL'] == floor($row['SL']) ? (int) $row['SL'] : number_format($row['SL'], 1)) : '0.0'); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-toggle="modal" data-target=".casualLeaveModal" href="javascript:void(0);"
                                            onclick="updateUrl(<?php echo $row['Employee_id']; ?>)">
                                            <p>10/<?php echo (isset($row['CL']) && $row['CL'] != 0 ? ($row['CL'] == floor($row['CL']) ? (int) $row['CL'] : number_format($row['CL'], 1)) : '0.0'); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-toggle="modal" data-target=".bd-progress-modal-sm"
                                            href="javascript:void(0);"
                                            onclick="updateUrl(<?php echo $row['Employee_id']; ?>)">
                                            <p><?php echo $row['expenses']; ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <div data-toggle="modal" data-target=".deductionLeaveModal"
                                            href="javascript:void(0);"
                                            onclick="updateUrl(<?php echo $row['Employee_id']; ?>)">
                                            <p><?php echo isset($row['deduction']) ? $row['deduction'] : '0'; ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="btn btn-primary" id="viewFile"
                                            href="print.php?employee_id=<?php echo $row['Employee_id']; ?>">View</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <script>
                        function selectAllCheckboxes() {
                            var table = $('#dataTable').DataTable();
                            var button = document.querySelector('button[onclick="selectAllCheckboxes()"]');
                            var selectedTeam = $('#teamFilter').val(); // get currently selected team from dropdown

                            // Determine if all relevant employees are already checked
                            var allChecked = true;
                            table.rows().every(function () {
                                var row = this.node();
                                var team = $(row).find('td:eq(2)').text(); // column 2 is team
                                var checkbox = $(row).find('input[type="checkbox"]')[0];

                                if (checkbox && (selectedTeam === "" || team === selectedTeam) && !checkbox.checked) {
                                    allChecked = false;
                                    return false; // exit early
                                }
                            });

                            // Toggle checkboxes for relevant employees
                            table.rows().every(function () {
                                var row = this.node();
                                var team = $(row).find('td:eq(2)').text();
                                var checkbox = $(row).find('input[type="checkbox"]')[0];

                                if (checkbox && (selectedTeam === "" || team === selectedTeam)) {
                                    $(checkbox).prop('checked', !allChecked);
                                }
                            });

                            // Update button text
                            button.textContent = allChecked ? 'Select All' : 'Deselect All';
                        }




                    </script>

                    <br>
                    <button type="button" class="btn btn-primary mt-2 align-self-end"
                        onclick="selectAllCheckboxes()">Select All</button>
                    <button type="submit" class="btn btn-primary mt-2 float-right mr-2">Send Mail to
                        Selected Employees</button><br><br>
                </form>


            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {
        var table = $('#dataTable').DataTable({
            "pageLength": 10,
            "responsive": true,
            "dom": '<"top"lf>rt<"bottom"ip>',
            "order": [[1, "asc"]],
            "stateSave": true,
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": [0, 2, 10],
                    "className": "dt-body-center"
                },
                {
                    "targets": '_all',
                    "orderable": true,
                    "className": "dt-body-left"
                }
            ],
            "initComplete": function () {
                $('.dt-body-center').css('width', '50px');
            }
        });

        // Move the team filter after the built-in search box
        $('#teamFilter').parent().appendTo($('#dataTable_filter')).css({
            display: 'inline-block',
            marginLeft: '10px'
        });
        $('#dataTable_filter').prepend('<button id="toggleSalaryBtn" class="btn btn-success mr-2" type="button">Show Salary</button>');

        let salaryVisible = false;
        const tableApi = table || $('#dataTable').DataTable();

        // helper
        function updateSalaryDisplay() {
            $(tableApi.rows().nodes()).each(function () {
                const $salaryTd = $(this).find('td.salary');
                if ($salaryTd.length) {
                    $salaryTd.text(salaryVisible ? $salaryTd.data('salary') : 'xxxxxx');
                }
            });
        }

        // initial mask
        updateSalaryDisplay();

        // click to toggle with color change
        $('#toggleSalaryBtn').on('click', function () {
            salaryVisible = !salaryVisible; // flip state
            updateSalaryDisplay();

            if (salaryVisible) {
                $(this)
                    .removeClass('btn-secondary')
                    .addClass('btn-danger')       // red when visible
                    .text('Hide Salary');
            } else {
                $(this)
                    .removeClass('btn-danger')
                    .addClass('btn-success')      // green when hidden
                    .text('Show Salary');
            }
        });

        // enforce on redraw (pagination, sort, search)
        tableApi.on('draw.dt', function () {
            updateSalaryDisplay();
        });




        // Filter table by team when dropdown changes
        $('#teamFilter').on('change', function () {
            // Mapping of team values to display names (same as PHP)
            const teamDisplayMap = {
                'Industrial': 'Industrial Team',
                'Building': 'Building Team',
                'IT': 'Robotics and Software Team',
                'Accounts': 'Management'
            };

            // Apply filter
            let selectedTeam = this.value;
            let displayName = selectedTeam ? teamDisplayMap[selectedTeam] || selectedTeam : '';
            table.column(2).search(displayName).draw();
        });

        // Handle select all
        $('.select-all').on('click', function () {
            var isChecked = $(this).data('checked') || false;
            $('input[type="checkbox"]').prop('checked', !isChecked);
            $(this).data('checked', !isChecked);
        });
        var state = table.state.loaded();
        if (state && state.columns[2].search.search) {
            $('#teamFilter').val(state.columns[2].search.search);
        }

        $('#myForm').on('submit', function (e) {
            table.$('input[type="checkbox"]').each(function () {
                if (this.checked && !$.contains(document, this)) {
                    $(this).clone().appendTo('#myForm');
                }
            });
        });
    });

</script>
<script>
    function getUrlParam(param) {
        const params = new URLSearchParams(window.location.search);
        return params.get(param);
    }

    function removeUrlParamsAndReload() {
        const url = new URL(window.location.href);
        url.searchParams.delete('mail');
        url.searchParams.delete('count');
        // Use replaceState to avoid adding a new history entry
        window.history.replaceState({}, document.title, url.pathname);
        window.location.reload();
    }

    const mailAdded = getUrlParam('mail');
    const count = getUrlParam('count');

    if (mailAdded === 'added') {
        Swal.fire({
            title: 'Batch Mail Jobs Added Successfully',
            html: `
      ${count} job(s) added.<br><br>
      Employees will get mail one by one. This is a batch process, so please wait patiently.<br><br>
      You may now track the mail status in the Mail Delivery option.<br><br>
      <b>Thank you for your patience!</b>
    `,
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Track Mail Delivery',
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isDismissed) {
                removeUrlParamsAndReload();
                return;
            }
            if (result.isConfirmed) {
                removeUrlParamsAndReload();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = '/mail-delivery'; // adjust URL as needed
            }
        });
    }




    document.getElementById('viewFile').forEach(function (button) {
        button.addEventListener('click', function (event) {
            // Prevent the default behavior (navigation)
            event.preventDefault();

            // Show the loading alert
            Swal.fire({
                title: 'Generating PaySlip...',
                text: 'Please wait until the process is completed.',
                allowOutsideClick: false, // Prevent closing by clicking outside
                didOpen: () => {
                    Swal.showLoading();
                },
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'OK',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Close the alert and proceed with the original link
                    Swal.close();
                    window.location.href = event.target.href; // Navigate to the original link
                }
            });
        });
    });
</script>


<!-- Expense  -->

<div class="modal fade bd-progress-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label" for="progress">Set Expenses </label>
                    <input class="form-control" value="0" type="number" name="expenses_data" required>
                    <button class="btn btn-primary mt-2" name="expenses_data_input">SET</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Sick Leave  -->

<div class="modal fade sickLeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label" for="progress">Annual Sick Leave Taken</label>
                    <input class="form-control" value="0" type="number" name="sick_leave_data_input" step="0.1"
                        required>
                    <button class="btn btn-primary mt-2" name="updateSickLeave">SET</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- casual Leave  -->

<div class="modal fade casualLeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label" for="progress">Annual Casual Leave Taken</label>
                    <input class="form-control" value="0" type="number" name="casual_leave_data_input" step="0.1"
                        required>
                    <button class="btn btn-primary mt-2" name="updateCasualLeave">SET</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- paid Leave  -->

<div class="modal fade paidLeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <label class="control-label" for="progress">Annual Paid Leave Taken</label>
                    <input class="form-control" value="0" type="number" name="paid_leave_data_input" step="0.1"
                        required>
                    <button class="btn btn-primary mt-2" name="updatePaidLeave">SET</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Deductions Leave  -->

<div class="modal fade deductionLeaveModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                    <!-- Make sure this is set -->
                    <label class="control-label" for="progress">Deduction Amount</label>
                    <input class="form-control" value="0" type="number" name="deduction_leave_data_input" required>
                    <button class="btn btn-primary mt-2" name="updateDeductionLeave">SET</button>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Clear DATA -->

<div class="modal fade " id="cleardata" tabindex="-1" role="dialog" aria-labelledby="cleardata" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="form-container mt-2 m-2">
                <form class="progress_data" action="" method="post" enctype="multipart/form-data">
                    <h5>Are you want to delete last month payslip expenses ?</h5>
                    <button class="btn btn-primary mt-2" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary mt-2" name="delete_expenses">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>



<?php


if (isset($_GET['success']) && $_GET['success'] === "true") {
    echo '
    <script>
    Swal.fire({
        title: "Done",
        text: "Leave Data Successfully fetched from time sheet. You can also overwrite data!",
        icon: "success"
    }).then(() => {
        // Remove "success=true" from URL without reloading
        const url = new URL(window.location.href);
        url.searchParams.delete("success");
        window.history.replaceState({}, document.title, url.pathname);
    });
    </script>
    ';
}



if (isset($_POST['delete_expenses'])) {
    // Delete all from csa_finance_payslip
    $sql = "DELETE FROM csa_finance_payslip";
    $stmt1 = $conn->prepare($sql);
    $stmt1->execute();
    $stmt1->close();

    // Delete from batch_email where Batch_no is NULL
    $sql2 = "DELETE FROM batch_email WHERE Batch_no IS NULL";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();
    $stmt2->close();

    $msg_success = "Deleted All Expenses and corresponding batch_email without Batch_no";
    header('location:' . $_SERVER['HTTP_REFERER']);
    exit();
}


if (isset($_POST['set_zero_reimbursements_all'])) {
    // Set expenses to 0 for all employees
    $sql = "SELECT * FROM csa_finance_employee_info";
    $info = $obj_admin->manage_all_info($sql);

    $num_row = $info->rowCount();

    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
        $employee_id = $row['Employee_id'];
        $fetch_Name = $row['Name'];
        $fetch_email = $row['email_id'];
        $fetch_salary = (float) $row['salary'];
        $fetch_bank_name = $row['bank_name'];
        $fetch_account_no = $row['account_no'];
        $fetch_doj = $row['doj'];
        $fetch_designation = $row['designation'];
        $fetch_department = $row['department'];

        $expenses_data = 0;
        $deduction_leave_data = 0;
        $total_pay = $fetch_salary + $expenses_data;

        // Payslip date range
        // Payslip date range - MODIFY THIS SECTION
        $selectedMonth = isset($_COOKIE['selectedMonth']) ? new DateTime($_COOKIE['selectedMonth']) : new DateTime();

        $lastMonth = (clone $selectedMonth)->modify('first day of this month')->modify('-1 month');
        $lastMonth->setDate($lastMonth->format('Y'), $lastMonth->format('m'), 15);

        $presentMonth = clone $selectedMonth;
        $presentMonth->modify('first day of this month')->setDate($presentMonth->format('Y'), $presentMonth->format('m'), 14);

        $payslip_month = $lastMonth->format('d/m/y') . ' TO ' . $presentMonth->format('d/m/y');

        // Insert or update in csa_finance_payslip
        $sql_insert = "INSERT INTO csa_finance_payslip (
            expenses, employee_id, fullName, payslip_month, email_id, salary, deduction,
            bank_name, account_no, total_pay, designation, department, doj, record_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IFNULL(record_date, CURRENT_DATE))
        ON DUPLICATE KEY UPDATE
            expenses = VALUES(expenses),
            fullName = VALUES(fullName),
            payslip_month = VALUES(payslip_month),
            email_id = VALUES(email_id),
            salary = VALUES(salary),
            bank_name = VALUES(bank_name),
            account_no = VALUES(account_no),
            deduction = VALUES(deduction),
            total_pay = VALUES(total_pay),
            designation = VALUES(designation),
            department = VALUES(department),
            doj = VALUES(doj),
            record_date = IFNULL(VALUES(record_date), CURRENT_DATE);";

        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param(
            "iisssdssdsdss",
            $expenses_data,
            $employee_id,
            $fetch_Name,
            $payslip_month,
            $fetch_email,
            $fetch_salary,
            $deduction_leave_data,
            $fetch_bank_name,
            $fetch_account_no,
            $total_pay,
            $fetch_designation,
            $fetch_department,
            $fetch_doj
        );
        $stmt->execute();
        $stmt->close();

        // Also insert into batch_email
        $SL = 0;
        $CL = 0;
        $PL = 0;
        $pan_id = '';
        $Others = 0;
        $mail_status = '';
        $Batch_no = null;

        $sql_batch = "INSERT INTO batch_email (
            employee_id, fullName, email_id, salary, expenses, bank_name, account_no, 
            total_pay, payslip_month, doj, designation, department, record_date, 
            SL, CL, PL, pan_id, Others, deduction, mail_status, Batch_no
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IFNULL(record_date, CURRENT_DATE), ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            fullName = VALUES(fullName),
            email_id = VALUES(email_id),
            salary = VALUES(salary),
            expenses = VALUES(expenses),
            bank_name = VALUES(bank_name),
            account_no = VALUES(account_no),
            total_pay = VALUES(total_pay),
            payslip_month = VALUES(payslip_month),
            doj = VALUES(doj),
            designation = VALUES(designation),
            department = VALUES(department),
            record_date = IFNULL(VALUES(record_date), CURRENT_DATE),
            SL = VALUES(SL),
            CL = VALUES(CL),
            PL = VALUES(PL),
            pan_id = VALUES(pan_id),
            Others = VALUES(Others),
            deduction = VALUES(deduction),
            mail_status = VALUES(mail_status),
            Batch_no = VALUES(Batch_no)";

        $stmt_batch = $conn->prepare($sql_batch);
        $stmt_batch->bind_param(
            "issddssssssssiiisdds",
            $employee_id,
            $fetch_Name,
            $fetch_email,
            $fetch_salary,
            $expenses_data,
            $fetch_bank_name,
            $fetch_account_no,
            $total_pay,
            $payslip_month,
            $fetch_doj,
            $fetch_designation,
            $fetch_department,
            $SL,
            $CL,
            $PL,
            $pan_id,
            $Others,
            $deduction_leave_data,
            $mail_status,
            $Batch_no
        );
        $stmt_batch->execute();
        $stmt_batch->close();
    }

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    $msg = "All reimbursements successfully set to 0.";
    exit;
}




if (isset($_POST["expenses_data_input"]) && isset($_GET["Employee_id"])) {
    // Get the progress value from the form
    $expenses_data = $_POST['expenses_data'];

    $deduction_leave_data = isset($_POST['deduction_leave_data_input']) ? $_POST['deduction_leave_data_input'] : 0;


    // Get the project ID from the URL parameter or form field
    $employee_id = $_GET["Employee_id"]; // Assuming it's in the URL

    $sql = "SELECT * FROM csa_finance_employee_info WHERE employee_id = $employee_id";  // Add a semicolon here
    $info = $obj_admin->manage_all_info($sql);

    $serial = 1;

    $num_row = $info->rowCount();
    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
        $fetch_Name = $row['Name'];
        $fetch_email = $row['email_id'];
        $fetch_salary = (float) $row['salary']; // Cast salary to float
        $fetch_bank_name = $row['bank_name'];
        $fetch_account_no = $row['account_no'];
        $fetch_doj = $row['doj'];
        $fetch_designation = $row['designation'];
        $fetch_department = $row['department'];

        // Ensure expenses_data is also treated as a float
        $expenses_data = (float) $_POST['expenses_data']; // or wherever this is coming from

        // Calculate total pay
        $total_pay = $fetch_salary + $expenses_data;

        // Payslip date range - MODIFY THIS SECTION
        $selectedMonth = isset($_COOKIE['selectedMonth']) ? new DateTime($_COOKIE['selectedMonth']) : new DateTime();

        $lastMonth = (clone $selectedMonth)->modify('first day of this month')->modify('-1 month');
        $lastMonth->setDate($lastMonth->format('Y'), $lastMonth->format('m'), 15);

        $presentMonth = clone $selectedMonth;
        $presentMonth->modify('first day of this month')->setDate($presentMonth->format('Y'), $presentMonth->format('m'), 14);

        $payslip_month = $lastMonth->format('d/m/y') . ' TO ' . $presentMonth->format('d/m/y');
        // Debugging: Uncomment to see formatted dates
        // echo "Payslip Month: " . $payslip_month . "<br>";
    }


    // You can add additional validation and sanitation here

    // Check if this employee already has a payslip
    $check_sql_payslip = "SELECT COUNT(*) as cnt FROM csa_finance_payslip WHERE employee_id = ?";
    $check_stmt_payslip = $conn->prepare($check_sql_payslip);
    $check_stmt_payslip->bind_param("i", $employee_id);
    $check_stmt_payslip->execute();
    $check_result_payslip = $check_stmt_payslip->get_result();
    $row_check_payslip = $check_result_payslip->fetch_assoc();
    $check_stmt_payslip->close();

    if ($row_check_payslip['cnt'] > 0) {
        // Update existing record
        $sql = "UPDATE csa_finance_payslip
            SET expenses = ?, 
                fullName = ?, 
                payslip_month = ?, 
                email_id = ?, 
                salary = ?, 
                deduction = ?, 
                bank_name = ?, 
                account_no = ?, 
                total_pay = ?, 
                designation = ?, 
                department = ?, 
                doj = ?, 
                record_date = IFNULL(record_date, CURRENT_DATE)
            WHERE employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "dsssdsssdsssi",
            $expenses_data,
            $fetch_Name,
            $payslip_month,
            $fetch_email,
            $fetch_salary,
            $deduction_leave_data,
            $fetch_bank_name,
            $fetch_account_no,
            $total_pay,
            $fetch_designation,
            $fetch_department,
            $fetch_doj,
            $employee_id
        );
    } else {
        // Insert new record
        $sql = "INSERT INTO csa_finance_payslip (
        expenses, employee_id, fullName, payslip_month, email_id, salary, deduction,
        bank_name, account_no, total_pay, designation, department, doj, record_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IFNULL(record_date, CURRENT_DATE))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "disssddssdsss",
            $expenses_data,
            $employee_id,
            $fetch_Name,
            $payslip_month,
            $fetch_email,
            $fetch_salary,
            $deduction_leave_data,
            $fetch_bank_name,
            $fetch_account_no,
            $total_pay,
            $fetch_designation,
            $fetch_department,
            $fetch_doj
        );
    }


    // First check if this employee_id already has a non-null Batch_no
    $check_sql = "SELECT COUNT(*) as cnt 
              FROM batch_email 
              WHERE employee_id = ? AND (Batch_no IS NULL OR Batch_no = '')";

    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $employee_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row_check = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($row_check['cnt'] > 0) {
        // Update existing row without Batch_no
        $sql2 = "UPDATE batch_email 
             SET fullName = ?, 
                 email_id = ?, 
                 salary = ?, 
                 expenses = ?, 
                 bank_name = ?, 
                 account_no = ?, 
                 total_pay = ?, 
                 payslip_month = ?, 
                 doj = ?, 
                 designation = ?, 
                 department = ?, 
                 record_date = IFNULL(record_date, CURRENT_DATE),
                 SL = ?, 
                 CL = ?, 
                 PL = ?, 
                 pan_id = ?, 
                 Others = ?, 
                 deduction = ?, 
                 mail_status = ?, 
                 Batch_no = ?
             WHERE employee_id = ? AND (Batch_no IS NULL OR Batch_no = '')";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param(
            "ssddssssssssiiisddsi",
            $fetch_Name,
            $fetch_email,
            $fetch_salary,
            $expenses_data,
            $fetch_bank_name,
            $fetch_account_no,
            $total_pay,
            $payslip_month,
            $fetch_doj,
            $fetch_designation,
            $fetch_department,
            $SL,
            $CL,
            $PL,
            $pan_id,
            $Others,
            $deduction_leave_data,
            $mail_status,
            $Batch_no,
            $employee_id
        );
    } else {
        // Insert new row
        $sql2 = "INSERT INTO batch_email (
        employee_id, fullName, email_id, salary, expenses, bank_name, account_no, 
        total_pay, payslip_month, doj, designation, department, record_date, 
        SL, CL, PL, pan_id, Others, deduction, mail_status, Batch_no
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IFNULL(record_date, CURRENT_DATE), ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param(
            "issddssssssssiiisdds",
            $employee_id,
            $fetch_Name,
            $fetch_email,
            $fetch_salary,
            $expenses_data,
            $fetch_bank_name,
            $fetch_account_no,
            $total_pay,
            $payslip_month,
            $fetch_doj,
            $fetch_designation,
            $fetch_department,
            $SL,
            $CL,
            $PL,
            $pan_id,
            $Others,
            $deduction_leave_data,
            $mail_status,
            $Batch_no
        );
    }



    if (!$stmt2->execute()) {
        $msg_error = "Error inserting into batch_email: " . $conn->error;
    }

    $stmt2->close();


    // Execute the statement
    if ($stmt->execute()) {
        $msg_success = "Status Updated";
        header('location:' . $_SERVER['HTTP_REFERER']);
    } else {
        $msg_error = "Error: " . $conn->error;
    }

    // Close the statement
    $stmt->close();

    // Close the statement and connection

}


if (isset($_POST['updateDeductionLeave']) && isset($_GET["Employee_id"])) {
    $employee_id = (int) $_GET["Employee_id"]; // Ensure integer
    $deduction_leave_data = (float) $_POST["deduction_leave_data_input"]; // Ensure number

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // --- Update in csa_finance_payslip ---
    $updatePayslip = "UPDATE csa_finance_payslip SET deduction = ? WHERE employee_id = ?";
    $stmt1 = $conn->prepare($updatePayslip);
    $stmt1->bind_param("di", $deduction_leave_data, $employee_id);
    $stmt1->execute();
    $stmt1->close();

    // --- Update in batch_email only if Batch_no is empty or NULL ---
    $updateBulk = "UPDATE batch_email 
                   SET deduction = ? 
                   WHERE employee_id = ? 
                     AND (Batch_no IS NULL OR Batch_no = '')";
    $stmt2 = $conn->prepare($updateBulk);
    $stmt2->bind_param("di", $deduction_leave_data, $employee_id);
    $stmt2->execute();
    $stmt2->close();

    // Redirect back
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}




if (isset($_POST['updateSickLeave']) && isset($_GET["Employee_id"])) {
    $employee_id = (int) $_GET["Employee_id"]; // Ensure integer
    $leave_data = $_POST["sick_leave_data_input"];

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate
    if (!is_numeric($leave_data)) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=invalid_input');
        exit();
    }

    $leave_data = floatval($leave_data);

    // --- Update in csa_finance_employee_info ---
    $updateEmployeeLeave = "UPDATE csa_finance_employee_info SET SL = ? WHERE Employee_id = ?";
    $stmt1 = $conn->prepare($updateEmployeeLeave);
    $stmt1->bind_param("di", $leave_data, $employee_id);
    $stmt1->execute();
    $stmt1->close();

    // --- Update in batch_email only if Batch_no is empty or NULL ---
    $updateBulkLeave = "UPDATE batch_email 
                    SET SL = ? 
                    WHERE employee_id = ? 
                      AND (Batch_no IS NULL OR Batch_no = '')";
    $stmt2 = $conn->prepare($updateBulkLeave);
    $stmt2->bind_param("di", $leave_data, $employee_id);
    $stmt2->execute();
    $stmt2->close();

    // --- Update in csa_finance_payslip ---
    $updatePayslipLeave = "UPDATE csa_finance_payslip SET SL = ? WHERE employee_id = ?";
    $stmt3 = $conn->prepare($updatePayslipLeave);
    $stmt3->bind_param("di", $leave_data, $employee_id);
    $stmt3->execute();
    $stmt3->close();

    // Redirect back
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}


if (isset($_POST['updateCasualLeave']) && isset($_GET["Employee_id"])) {
    $employee_id = (int) $_GET["Employee_id"]; // Ensure integer
    $leave_data = $_POST["casual_leave_data_input"];

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate numeric
    if (!is_numeric($leave_data)) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=invalid_input');
        exit();
    }

    $leave_data = floatval($leave_data);

    // --- Update in csa_finance_employee_info ---
    $updateEmployeeLeave = "UPDATE csa_finance_employee_info SET CL = ? WHERE Employee_id = ?";
    $stmt1 = $conn->prepare($updateEmployeeLeave);
    $stmt1->bind_param("di", $leave_data, $employee_id);
    $stmt1->execute();
    $stmt1->close();

    // --- Update in csa_finance_payslip ---
    $updatePayslipLeave = "UPDATE csa_finance_payslip SET CL = ? WHERE employee_id = ?";
    $stmt3 = $conn->prepare($updatePayslipLeave);
    $stmt3->bind_param("di", $leave_data, $employee_id);
    $stmt3->execute();
    $stmt3->close();

    // --- Update in batch_email only if Batch_no is empty or NULL ---
    $updateBulkLeave = "UPDATE batch_email 
                        SET CL = ? 
                        WHERE employee_id = ? 
                          AND (Batch_no IS NULL OR Batch_no = '')";
    $stmt2 = $conn->prepare($updateBulkLeave);
    $stmt2->bind_param("di", $leave_data, $employee_id);
    $stmt2->execute();
    $stmt2->close();

    // Redirect back
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}





include './include/footer.php';
?>
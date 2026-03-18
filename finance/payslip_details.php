<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php';
require '../conn.php';
include './include/login_header.php';

// Auth check
$user_id = $_SESSION['admin_id'] ?? null;
$user_name = $_SESSION['name'] ?? '';
$security_key = $_SESSION['security_key'] ?? '';

if (!$user_id || !$security_key) {
    header('Location: ../index.php');
    exit;
}

$user_role = $_SESSION['user_role'] ?? '';
include './include/sidebar.php';

$employee_id = $_GET['employee_id'] ?? '';

if (empty($employee_id)) {
    header('Location: dashboard.php');
    exit;
}

// Fetch employee name securely
$stmt = $conn->prepare("SELECT `Name` FROM `csa_finance_employee_info` WHERE `Employee_id` = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->bind_result($employee_name);
$stmt->fetch();
$stmt->close();

if (empty($employee_name)) {
    $employee_name = "Unknown";
}
?>

<div class="container-fluid m-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <button class="btn btn-secondary btn-sm me-3" onclick="history.back();">&larr; Back</button>
            <h4 class="h3 mb-0 text-gray-800">
                Payslip Months for: <?php echo htmlspecialchars($employee_name); ?>
            </h4>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong class="m-0 text-primary">Payslip Months</strong>
        </div>

        <div class="card-body">
            <div class="row">
                <?php
                $stmt = $conn->prepare("SELECT DISTINCT `payslip_month` FROM `csa_finance_payslip_records` WHERE `employee_id` = ?");
                $stmt->bind_param("s", $employee_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                            <div class="card text-center h-100 shadow-sm bg-light open-files-modal"
                                style="cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;"
                                data-employee-id="<?php echo htmlspecialchars($employee_id); ?>"
                                data-month="<?php echo htmlspecialchars($row['payslip_month']); ?>">

                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <h6 class="mb-0 ">
                                        <?php
                                        $monthText = $row['payslip_month'];
                                        if (strpos($monthText, 'TO') !== false) {
                                            [$start, $end] = explode('TO', $monthText);
                                            $startDate = DateTime::createFromFormat('d/m/y', trim($start));
                                            echo $startDate ? $startDate->format('M Y') : htmlspecialchars($monthText);
                                        } else {
                                            echo htmlspecialchars($monthText);
                                        }
                                        ?>
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else:
                    echo "<div class='col-12'><p class='text-center'>No payslip records found for this employee.</p></div>";
                endif;

                $stmt->close();
                ?>
            </div>
        </div>
    </div>

</div>

<!-- Modal for File List -->
<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Available Files</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="filesList">
                <p class="text-center">Loading files...</p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts Section -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Card Hover Effect
        const cards = document.querySelectorAll('.card.open-files-modal');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '';
            });

            // Modal Trigger
            card.addEventListener('click', () => {
                const employeeId = card.getAttribute('data-employee-id');
                const month = card.getAttribute('data-month');
                const modal = new bootstrap.Modal(document.getElementById('filesModal'));
                const filesList = document.getElementById('filesList');

                filesList.innerHTML = '<p class="text-center">Loading files...</p>';

                fetch('fetch_files.php?employee_id=' + encodeURIComponent(employeeId) + '&month=' + encodeURIComponent(month))
                    .then(response => response.text())
                    .then(data => {
                        filesList.innerHTML = data;
                    })
                    .catch(() => {
                        filesList.innerHTML = '<p class="text-danger text-center">Failed to load files.</p>';
                    });

                modal.show();
            });
        });
    });
</script>

<?php include './include/footer.php'; ?>
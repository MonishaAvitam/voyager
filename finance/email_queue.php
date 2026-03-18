<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}
$user_role = $_SESSION['user_role'];

include './include/sidebar.php';


// 🔹 Handle Resend action (update status + attempts)
if (isset($_GET['resend_id'])) {
    $resend_id = intval($_GET['resend_id']);
    $update_sql = "UPDATE payslip_queue 
                   SET status = 'pending', attempts = attempts + 1, updated_at = NOW() 
                   WHERE id = $resend_id";
    if (mysqli_query($conn, $update_sql)) {
        $message = "<div class='alert alert-success'>Record #$resend_id marked as pending successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to update record #$resend_id.</div>";
    }
}


// 🔹 JOIN payslip_queue with employee info
$sql = "SELECT pq.id, pq.employee_id, ei.Name AS employee_name, pq.status, pq.attempts, 
               pq.last_error, pq.created_at, pq.updated_at, pq.Batch_no
        FROM payslip_queue pq
        LEFT JOIN csa_finance_employee_info ei 
        ON pq.employee_id = ei.Employee_id";

$result = mysqli_query($conn, $sql);

?>



<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Email Status</h1>
    </div>

    <?php if (!empty($message)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                <?php if (strpos($message, 'success') !== false): ?>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        html: <?= json_encode(strip_tags($message)) ?>,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // ✅ Remove ?resend_id=xx from URL without reload
                        if (window.history.replaceState) {
                            const url = new URL(window.location.href);
                            url.searchParams.delete('resend_id');
                            window.history.replaceState({}, document.title, url.toString());
                        }
                    });
                <?php else: ?>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: <?= json_encode(strip_tags($message)) ?>,
                        showConfirmButton: true
                    }).then(() => {
                        // ✅ Clean URL too, even on error
                        if (window.history.replaceState) {
                            const url = new URL(window.location.href);
                            url.searchParams.delete('resend_id');
                            window.history.replaceState({}, document.title, url.toString());
                        }
                    });
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Email Status</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Last Error</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Batch No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Last Error</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Batch No</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['employee_id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['employee_name'] ?? 'Unknown') ?></td>
                                    <td>
                                        <?php if (($row['status'] ?? '') === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif (($row['status'] ?? '') === 'failed'): ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php elseif (($row['status'] ?? '') === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($row['status'] ?? 'Unknown') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['attempts'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['last_error'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['created_at'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['updated_at'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($row['Batch_no'] ?? '') ?></td>
                                    <td>
                                        <?php if (($row['status'] ?? '') === 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-clock"></i> Pending
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary resend-btn"
                                                data-id="<?= $row['id'] ?>"
                                                data-employee="<?= htmlspecialchars($row['employee_name'] ?? $row['employee_id']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#resendModal">
                                                <i class="fas fa-redo-alt"></i> Resend
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center">No records found</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- 🔹 Bootstrap Resend Confirmation Modal -->
<div class="modal fade" id="resendModal" tabindex="-1" aria-labelledby="resendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resendModalLabel">Confirm Resend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to resend email for <strong><span id="modalEmployee"></span></strong>
                (ID: <span id="modalId"></span>)?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmResend" href="#" class="btn btn-primary">Yes, Resend</a>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        // 🔹 Initialize DataTable
        if ($.fn.DataTable) {
            $('#dataTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']], // sort by ID desc
                responsive: true,
                stateSave: true, // ✅ keep search, paging, etc.
                columnDefs: [
                    { orderable: false, targets: -1 } // disable sorting on Action column
                ]
            });
        } else {
            console.error("❌ DataTables not loaded. Please check script includes.");
        }
    });

    // 🔹 Pass data into modal
    document.querySelectorAll('.resend-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            let id = this.getAttribute('data-id');
            let employee = this.getAttribute('data-employee');
            document.getElementById('modalId').innerText = id;
            document.getElementById('modalEmployee').innerText = employee;
            document.getElementById('confirmResend').setAttribute('href', 'email_queue.php?resend_id=' + id);
        });
    });
</script>
<?php include './include/footer.php';
?>
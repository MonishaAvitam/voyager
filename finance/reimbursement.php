<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php';
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

// 🔹 Fetch reimbursements with employee info
$sql = "SELECT r.reimbursement_id, r.reimbursement_details , r.user_id, a.fullname AS employee_name, r.status, r.file_paths, r.created_at, r.updated_at
        FROM reimbursements r
        JOIN tbl_admin a ON r.user_id = a.user_id
        ORDER BY r.reimbursement_id DESC";

$result = mysqli_query($conn, $sql);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reimbursement Status</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Reimbursements</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th hidden>ID</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th hidden>ID</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td hidden><?= $row['reimbursement_id'] ?></td>
                                <td><?= $row['user_id'] ?></td>
                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                <td><?= htmlspecialchars(strlen($row['reimbursement_details']) > 100 ? substr($row['reimbursement_details'], 0, length: 100) . '...' : $row['reimbursement_details']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm viewBtn"
                                        data-id="<?= $row['reimbursement_id'] ?>"
                                        data-employee="<?= htmlspecialchars($row['employee_name']) ?>"
                                        data-userid="<?= $row['user_id'] ?>"
                                        data-details="<?= htmlspecialchars($row['reimbursement_details']) ?>"
                                        data-status="<?= htmlspecialchars($row['status']) ?>"
                                        data-files='<?= json_encode(json_decode($row['file_paths'])) ?>'
                                        data-bs-toggle="modal" data-bs-target="#reimbursementModal">
                                        View
                                    </button>


                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#dataTable').DataTable({
            "order": [[0, "desc"]]
        });

        // Open modal with data
        $(document).on('click', '.viewBtn', function () {
            let id = $(this).data('id');
            let userid = $(this).data('userid');
            let employee = $(this).data('employee');
            let details = $(this).data('details');
            let status = $(this).data('status');
            let files = $(this).data('files');

            $('#modalReimId').text(id);
            $('#modalUserId').text(userid);
            $('#modalEmployee').text(employee);
            $('#modalDetails').text(details);
            $('#modalStatus').text(status);

            // show files
            let fileHtml = '';
            try {
                let fileArr = Array.isArray(files) ? files : JSON.parse(files);
                if (fileArr.length > 0) {
                    fileHtml = '<ol class="list-group list-group-numbered">';
                    fileArr.forEach(f => {
                        let filename = f.split('/').pop(); // only show filename
                        fileHtml += `<li class="list-group-item p-1">
                    <a href="../${f}" target="_blank">${filename}</a>
                 </li>`;
                    });
                    fileHtml += '</ol>';

                } else {
                    fileHtml = 'No files uploaded';
                }
            } catch (e) {
                fileHtml = 'No files uploaded';
            }
            $('#modalFiles').html(fileHtml);
        });
    });
</script>




<!-- Reimbursement Modal -->
<div class="modal fade" id="reimbursementModal" tabindex="-1" aria-labelledby="reimbursementModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reimbursementModalLabel">Reimbursement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr hidden>
                        <th>ID</th>
                        <td id="modalReimId"></td>
                    </tr>
                    <tr>
                        <th>Employee ID</th>
                        <td id="modalUserId"></td>
                    </tr>
                    <tr>
                        <th>Employee Name</th>
                        <td id="modalEmployee"></td>
                    </tr>
                    <tr>
                        <th>Details</th>
                        <td id="modalDetails"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="modalStatus"></td>
                    </tr>
                    <tr>
                        <th>Files</th>
                        <td id="modalFiles"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>



<?php
include './include/footer.php';
?>
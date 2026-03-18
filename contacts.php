<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require './authentication.php';
require './conn.php';
include './include/login_header.php';

$user_id       = $_SESSION['admin_id'] ?? null;
$user_name     = $_SESSION['name'] ?? null;
$security_key  = $_SESSION['security_key'] ?? null;
$user_role     = $_SESSION['user_role'] ?? null;

if (!$user_id || !$security_key) {
    header('Location: index.php');
    exit;
}

include './include/sidebar.php';
include 'add_project.php';
include 'add_subproject.php';
include 'add_revisionproject.php';
?>

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_contact">
            Add Customer
        </button>
    </div>

    <!-- Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Customer Book</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">




                <table class="table table-bordered table-hover table-sm" id="dataTablecustomer" width="100%">
                    <thead class=" text-white text-center">
                        <tr>
                            <th>Company Name</th>
                            <th>Company Code</th>
                            <th>Contact Person</th>
                            <th>Email ID</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Comments</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $sql = "SELECT * FROM contacts ORDER BY contact_id DESC";
                        $stmt = $conn->query($sql);
                        $rows = $stmt->fetch_all(MYSQLI_ASSOC);

                        if (count($rows) === 0) {
                            echo '<tr>
                                    <td colspan="8" class="text-center text-muted">No customers found</td>

                                  </tr>';
                        }

                        foreach ($rows as $row) {
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['customer_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['contact_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['contact_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['contact_phone_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['comments'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

                                <td class="text-center">
                                    <a href="edit_contacts.php?contact_id=<?= $row['contact_id'] ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>

                                    <a href="?delete_contact_id=<?= $row['contact_id'] ?>"
                                        onclick="return confirm('Are you sure you want to delete this contact?');"
                                        class="btn btn-sm btn-outline-danger ml-1">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
                <script>
                    $(document).ready(function() {
                        $('#dataTablecustomer').DataTable({
                            pageLength: 10,
                            ordering: true,
                            columnDefs: [{
                                orderable: false,
                                targets: 7
                            }]
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>

<!-- ADD CONTACT MODAL -->
<div class="modal fade" id="add_contact" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Company Code</label>
                        <input type="text" name="customer_id" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contactName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phoneNumber" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Comments</label>
                        <textarea name="comments" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" name="add_contact">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php
/* DELETE CONTACT */
if (isset($_GET['delete_contact_id'])) {
    $id = (int) $_GET['delete_contact_id'];
    $conn->query("DELETE FROM contacts WHERE contact_id = $id");
    header("Location: contacts.php");
    exit;
}

/* ADD CONTACT */
if (isset($_POST['add_contact'])) {

    $stmt = $conn->prepare(
        "INSERT INTO contacts 
        (customer_id, customer_name, contact_name, contact_phone_number, contact_email, address, comments, registration_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $date = date('Y-m-d');

    $stmt->bind_param(
        "ssssssss",
        $_POST['customer_id'],
        $_POST['customer_name'],
        $_POST['contactName'],
        $_POST['phoneNumber'],
        $_POST['email'],
        $_POST['address'],
        $_POST['comments'],
        $date
    );

    $stmt->execute();
    header("Location: contacts.php");
    exit;
}

include './include/footer.php';
?>
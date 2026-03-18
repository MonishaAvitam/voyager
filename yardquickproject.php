<script>
document.addEventListener('DOMContentLoaded', function () {
  $('#yard_project').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const enquiryId = button.data('enquiry-id') || '';
    $(this).find('#enquiryIdInput').val(enquiryId);
  });
});
</script>

<div class="modal fade" id="yard_project" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Quick Project</h5>
        <button class="close" data-dismiss="modal">×</button>
      </div>

      <form method="post" autocomplete="off">

        <div class="modal-body">

          <input type="hidden" name="enquiryId" id="enquiryIdInput">

          <!-- Project Name -->
          <div class="form-group">
            <label>Project Name</label>
            <input type="text" name="project_name" class="form-control" required>
          </div>

          <!-- Customer -->
          <div class="form-group">
            <label>Customer</label>
            <select class="form-control" name="contact_id" required>
              <option value="">Select Customer</option>
              <?php
              $sql = "SELECT contact_id, customer_name FROM contacts";
              $info = $obj_admin->manage_all_info($sql);
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['contact_id']}'>{$row['customer_name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Project Manager -->
          <div class="form-group">
            <label>Project Manager</label>
            <select class="form-control" name="project_manager" required>
              <option value="">Select Manager</option>
              <?php
              $sql = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess IN (1,3)";
              $info = $obj_admin->manage_all_info($sql);
              while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['user_id']}|{$row['fullname']}'>{$row['fullname']}</option>";
              }
              ?>
            </select>
          </div>

         

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" name="quick_project" class="btn btn-primary">
            Create
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_project'])) {

  include 'conn.php';

  $project_name = $_POST['project_name'];
  $contact_id   = $_POST['contact_id'];
  $project_status = 'ongoing';
  $branchCode = null;

  $start_date = date('Y-m-d');
  $end_date   = null;
  $estimated_hours = null;

  list($manager_id, $manager_name) = explode('|', $_POST['project_manager']);

  $assign_to_id = null;
  $assign_to = 'N/A';
  $state = null;

  $sql = "INSERT INTO projects
    (branch_code, project_name, contact_id, project_manager, project_managers_id,
     start_date, end_date, EPT, assign_to_id, assign_to, urgency, state)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssssssssssss",
    $branchCode,
    $project_name,
    $contact_id,
    $manager_name,
    $manager_id,
    $start_date,
    $end_date,
    $estimated_hours,
    $assign_to_id,
    $assign_to,
    $project_status,
    $state
  );

  if ($stmt->execute()) {
    $project_id = $stmt->insert_id;

    // Update enquiry if exists
    if (!empty($_POST['enquiryId'])) {
      $q = $conn->prepare(
        "UPDATE csa_sales_converted_projects
         SET enquiry_status='Project Created', rae_project_id=?
         WHERE id=?"
      );
      $q->bind_param("ii", $project_id, $_POST['enquiryId']);
      $q->execute();
      $q->close();
    }

    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit;
  }

  $stmt->close();
  $conn->close();
}
?>

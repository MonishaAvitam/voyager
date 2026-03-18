<?php
include '../conn.php';
require '../authentication.php';
include './include/login_header.php';
$pagename = 'Update Enquiry';

include './include/sidebar.php';
// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}
// check admin
$user_role = $_SESSION['user_role'];

?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('updateModel'));
        myModal.show();
    });
</script>

<div class="modal fade " id="updateModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="exampleModalLabel">Update Enquiry</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-container">
                            <?php

                            $id = $_GET['id'];

                            ?>
                            <form role="form" action="" method="post" autocomplete="off">
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <label for="" class="control-label">Status</label>
                                        <div>
                                            <select class="form-control" name="status" id="status">
                                                <option value="Hold">Hold project in RAE </opion>
                                                <option value="IN_RAE">Make project live in RAE</opion>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="completedDiv" class="form-group"></div>
                                    <div class="d-flex gap-2">
                                        <a class="form-control btn btn-primary" onclick="goBack();">Back</a> &nbsp;
                                        <script>
                                            function goBack() {
                                                window.history.go(-1);
                                            }
                                        </script>
                                        <button type="submit" name="updateenquiry" class="form-control btn btn-primary">Update Enquiry</button>
                                    </div>

                            </form>
                            <!-- <?php   ?> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['updateenquiry'])) {
    $enquiry_status = $_POST['status'];
    $enquiryId =  $id;

    // Update the enquiry_status in enquiry_sales
    $sql = "UPDATE csa_sales_converted_projects SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $enquiry_status, $enquiryId);
    $stmt->execute();
    $stmt->close();
    header('location:converted_to_projects.php');
}
?>




<?php include './include/footer.php'; ?>
<?php

include '../conn.php';

require '../authentication.php';

include './include/login_header.php';

$pagename = 'Edit Purchase';



include './include/sidebar.php';

// auth check

$user_id = $_SESSION['admin_id'];

$user_name = $_SESSION['name'];

$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {

    header('Location: index.php');
}

// check admin

$user_role = $_SESSION['user_role'];

?>

<div class="container bg-white mt-6" style="border-radius: 20px;">
    <div class="row">
        <div class="table-responsive ">
            <table class="table">
                <tbody>
                    <?php
                    $enquiry_id = $_GET['id'];

                    $sql = "SELECT * FROM enquiry_sales

                                    WHERE id = $enquiry_id";

                    $info = $obj_admin->manage_all_info($sql);
                    $serial  = 1;
                    $num_row = $info->rowCount();
                    if ($num_row == 0) {
                        echo '<tr><td colspan="7" class="d-flex justify-content-center align-items-center">No Contacts were found</td></tr>';
                    }
                    while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="mt-6 ">
                            <tr>
                                <td class="fw-bold">Enquiry Id</td>
                                <td><?php echo $row['id']; ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Potential Customer</td>
                                <td><?php echo $row['potential_customer']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Enquiry Status</td>
                                <td><?php if ($row['enquiry_status'] == 0) {
                                        echo 'Enquiry Not Acknowledged';
                                    } elseif ($row['enquiry_status'] == 25) {
                                        echo 'Enquiry  Acknowledged';
                                    } elseif ($row['enquiry_status'] == 50) {
                                        echo 'Quotation Assigned';
                                    } elseif ($row['enquiry_status'] == 60) {
                                        echo 'Quotation ready';
                                    } elseif ($row['enquiry_status'] == 75) {
                                        echo 'Quotation Sent';
                                    } elseif ($row['enquiry_status'] == 100) {
                                        echo 'Projected Created & Sent to Rae';
                                    } ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Last Updated</td>
                                <td><?php echo $row['last_updated']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Comments</td>
                                <td><?php echo $row['comments']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Priority</td>
                                <td><?php echo $row['priority'] ? $row['priority'] : 'Acknowlodge It First ' ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Files:</td>
                                <td>
                                    <a href="<?php echo "https://drive.google.com/drive/u/1/folders/" . $row['folderId']; ?>" class="btn btn-primary">View files</a>
                                </td>
                            </tr>



                        </div>
                    <?php }  ?>
                </tbody>
            </table>
            <a class="btn btn-primary" href="<?php echo $_SERVER['HTTP_REFERER']; ?>">Back</a>
        </div>
    </div>
</div>
</div>
</div>



<!-- end of projects section -->
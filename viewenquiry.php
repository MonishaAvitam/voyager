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
                                <td class="fw-bold">Customer Name: </td>
                                <td><?php echo $row['customer_name']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Customer Contact:</td>
                                <td><?php echo $row['customer_contact']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Customer Email-Id:</td>
                                <td><?php echo $row['customer_emailid']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Customer Address:</td>
                                <td><?php echo $row['customer_address']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Description:</td>
                                <td><?php echo $row['enquiry_details']; ?></td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Files:</td>
                                <td>
                                    <a href="<?php echo "https://drive.google.com/drive/u/1/folders/" . $row['files']; ?>" class="btn btn-primary">View files</a>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">Audio File:</td>
                                <td><a href="<?php echo $row['audio_file']; ?>" class="btn btn-primary">View file</a></td>
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
<?php

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


    // check admin
    $user_role = $_SESSION['user_role'];

    include './include/sidebar.php';
?>




<div class="container-fluid">




</div>














    <?php
    include './include/footer.php';
    ?>
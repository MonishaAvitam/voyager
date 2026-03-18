<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include 'include/sidebar.php';
include './include/login_header.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


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



<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">View Quotations </h1>
        <div class="d-flex align-items-center">
        </div>
    </div>

    <style>
        .card-quotation {

            border: none;
            border-radius: 4px;
        }

        .dots {

            height: 4px;
            width: 4px;
            margin-bottom: 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
        }


        .user-img {

            margin-top: 4px;
        }

        .check-icon {

            font-size: 17px;
            color: #c3bfbf;
            top: 1px;
            position: relative;
            margin-left: 3px;
        }

        .form-check-input {
            margin-top: 6px;
            margin-left: -24px !important;
            cursor: pointer;
        }


        .form-check-input:focus {
            box-shadow: none;
        }


        .icons i {

            margin-left: 8px;
        }

        .reply {

            margin-left: 12px;
        }

        .reply small {

            color: #b7b4b4;

        }


        .reply small:hover {

            color: green;
            cursor: pointer;

        }
    </style>
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="container-fluid mt-5">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary ">Create New Quotation</button>
            </div>

            <div class="row d-flex justify-content-center  ">
                <div class="col-md-12 ">
                    <?php
                    $enquiryId = $_GET['enquiryId'];
                    $sql = "SELECT q.*,c.customer_name FROM quotations q LEFT JOIN contacts c ON q.customer_id = c.contact_id WHERE enquiry_id = $enquiryId  
                          
                            ";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {



                    ?>







                            <div class="card card-quotation p-3 mt-3">

                                <div class="d-flex justify-content-between align-items-center">

                                    <div class="user d-flex flex-row align-items-center">

                                        <img src="./img/quotation.png" width="30" class="user-img rounded-circle mr-2">
                                        <span><small class="font-weight-bold text-primary"><?php echo $row['quotation_name'] ?></small>

                                    </div>
                                    <div>
                                        <small class="font-weight-bold">Quotation <span class="text-success"><?php echo $row['status']  ?></span></small></span>
                                    </div>


                                </div>


                                <div class="action d-flex justify-content-between mt-2 align-items-center">

                                    <div class="reply px-4">

                                        <?php

                                        if ($row['status'] == 'Ready') {


                                            $dataArray = json_decode($row['quotation_file_id'], true);
                                            foreach ($dataArray as $items) {
                                                foreach ($dataArray as $item) {
                                                    $id = $item['id'];
                                                    $link = $item['link'];

                                        ?>
                                                    <a href="<?php echo htmlspecialchars($link); ?>">File <?php echo $fileno  = +1 ?></a>
                                        <?php }
                                            }
                                        }
                                        ?>
                                    </div>

                                    <div class="icons align-items-center">

                                        <small><?php echo $row['last_updated'] ?></small>

                                    </div>

                                </div>



                            </div>


                    <?php

                        }
                    }
                    $result->close();
                    ?>


                </div>


            </div>
        </div>
    </div>
</div>




<?php

include 'include/footer.php';

?>
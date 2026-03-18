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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_team'])) {
}

// if (isset($_POST['send_to_rae'])) {
//     $enquiryId = $_POST['id'];
//     $team = $_POST['team'];
//     $status = "Sent to RAE";


//     $moveToRae = "INSERT INTO csa_sales_converted_projects (id, customer_id , enquiry_details, files, audio_file, date, time,last_updated,team,enquiry_status) 
//                         SELECT id, customer_id, enquiry_details, files, audio_file, date, time,last_updated,?,? FROM enquiry_under_consideration WHERE id=? 
//                         ";
//     $deleteFromSalesSql = "DELETE FROM enquiry_under_consideration WHERE id = ?";

//     $stmtMove = $conn->prepare($moveToRae);
//     $stmtdrop = $conn->prepare($deleteFromSalesSql);

//     if ($stmtMove) {
//         $stmtMove->bind_param("sss", $team, $status, $enquiryId);
//         $stmtdrop->bind_param("s", $enquiryId);
//         $_SESSION['status_success'] = "Enquiry Moved To RAE Successfully";

//         if (!$stmtMove->execute()) {
//             echo "Error moving record: " . $stmtMove->error;
//             // Handle error if moving record fails
//         }

//         // Close the statement
//         if (!$stmtdrop->execute()) {
//             echo "Error moving record: " . $stmtdrop->error;
//             // Handle error if moving record fails
//         }

//         // Close the statement
//         $stmtMove->close();
//     }
// }


?>

<style>
    .ratings i {
        color: green;
    }

    .install span {
        font-size: 12px;
    }

    .col-md-4 {
        margin-top: 27px;
    }
</style>





<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quotations</h1>
        <div class="d-flex align-items-center">
        </div>
    </div>

    <style>
        .bordered-div {
            border: 5px solid red;
            /* Adjust the border width and color as needed */
        }
    </style>
    <!-- Content Row -->
    <div class="container-fluid">
        <div class="container-fluid mt-5">
            <div class="row d-flex justify-content-center  ">

                <?php

                $sql = "SELECT q.*,c.customer_name FROM quotations q LEFT JOIN contacts c ON q.customer_id = c.contact_id  ORDER BY CASE 
            WHEN q.priority = 'high' THEN 1
            WHEN q.priority = 'medium' THEN 2
            WHEN q.priority = 'low' THEN 3
            ELSE 4
        END";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        switch ($row['priority']) {
                            case 'high':
                                $priority  = 'bg-danger';
                                break;
                            case 'medium':
                                $priority  = 'bg-warning';
                                break;
                            case 'low':
                                $priority  = 'bg-info';
                                break;
                        }

                ?>

                        <div class="col-3 m-2  p-2 <?php echo $priority ?> ">
                            <div class="card p-3 ">
                                <div class="d-flex flex-row mb-3">
                                    <img src="./img/projectIcon.png" width="70">
                                    <div class="d-flex flex-column ml-2">
                                        <span class=""><?php echo $row['quotation_name']  ?></span>
                                        <span class=""><?php echo $row['customer_name']  ?></span>
                                    </div>
                                </div>
                                <!-- <h6>Get more context on your users with stripe data inside our platform.</h6> -->
                                <div class="d-flex justify-content-between install mt-3">
                                    <span>Priority is <?php echo $row['priority'] ?></span>
                                    <a href="./viewQuotations.php?">View&nbsp;<i class="fa fa-angle-right"></i></a>
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




<?php

include 'include/footer.php';

?>
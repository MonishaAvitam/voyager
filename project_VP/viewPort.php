<?php

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './login_header.php';

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

include '../include/viewportTopBar.php';
include '../add_project.php';


// Check admin
$user_role = $_SESSION['user_role'];


if ($user_role == 2) {
    header('Location:../welcome.php');
}
$todays_date = date("Y-m-d");

?>
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<?php
if (isset($_GET['message']) && $_GET['message'] == 'success') {
    echo "<script>
        Swal.fire({
            title: 'Success!',
            text: 'Subproject updated successfully!',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'viewPort.php';
        });
    </script>";
}
?>

<?php
if (isset($_GET['message']) && $_GET['message'] == 'deleted') {
    echo "<script>
        Swal.fire({
            title: 'Deleted!',
            text: 'Subproject has been deleted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'

        }).then(() => {
            window.location.href = 'viewPort.php';
        });
    </script>";
}
?>


<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : null;
    $num_subprojects = isset($_POST['num_subprojects']) ? intval($_POST['num_subprojects']) : null;
    $category = isset($_POST['category']) ? intval($_POST['category']) : null;
    $urgency = "white";
    $project_manager_status = null;
    // Determine if outsourced (1 for outsourced, 0 for internal)
    $outsourced = ($category == 0) ? 1 : 0;

    if ($project_id && $num_subprojects > 0) {
        $stmt = $conn->prepare("INSERT INTO subprojects (project_id, subproject_name, subproject_details,project_manager_status, assign_to, subproject_status, outsourced,urgency) VALUES (?, ?, ?, ?, ?,?, ?,?)");

        if ($stmt) {
            for ($i = 1; $i <= $num_subprojects; $i++) {
                $subproject_name = "N/A";
                $assign_to = null;

                $stmt->bind_param("isssssis", $project_id, $subproject_name, $subproject_details, $project_manager_status, $assign_to, $subproject_status, $outsourced, $urgency);
                $stmt->execute();
            }

            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '$num_subprojects subprojects have been created successfully!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'viewPort.php';
                });
            </script>";

            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "<script>alert('No subprojects were created because the number provided is invalid.');</script>";
    }
}

?>





<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>


<script src="https://kit.fontawesome.com/26bcd7cc45.js" crossorigin="anonymous"></script>
<style>
    .custom-red {
        background-color: red;
        color: white;
        border-radius: 5px;
    }


    .custom-orange {
        background-color: orange;
        color: white;
        border-radius: 5px;

    }

    .custom-white {
        background-color: white;
        color: black;
        border-radius: 5px;

    }

    .custom-green {
        background-color: green;
        color: white;
        border-radius: 5px;

    }

    .custom-purple {
        background-color: purple;
        color: white;
        border-radius: 5px;

    }

    .custom-blue {
        background: navy;
        color: white;
        border-radius: 5px;
    }

    .tr-anime:hover {
        background: lightcyan;
    }

    .subproject-anime {
        transition: all 0.3s ease-in-out;
    }

    .subproject-anime:hover {
        transform: scale(1.12);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }


    .info-icon {
        transition: all 0.2s ease-in-out;

    }


    .info-icon:hover {
        transform: scale(1.4);

    }



    .project-management {
        font-size: 2.5rem;
        font-weight: bold;
        -webkit-text-stroke: 0.03rem teal;
        font-family: 'Helvetica Neue';

    }


    #customSearchInput {
        width: 20rem;
        height: 2.8rem;

        padding: 0.5rem;
        outline: none;
        border: 1px solid lightgray;
        background: rgb(228, 228, 228);
        border-radius: 0 8px 8px 0;

    }


    #customSearchInput:hover {
        outline: none;
        border: 5px solid lightgray;
    }

    /* .search-icon-container {
        background: rgb(19, 62, 135);
        padding-top: 0.3rem;
        padding-left: 0.8rem;
        height: 2rem;
        width: 2.5rem;
        border-radius: 0.7rem 0 0 0.7rem;
    } */


    .search-icon-container {
        background: #305595cd;

        height: 2.8rem;
        width: 2.8rem;
        padding: 0.6rem;
        border-radius: 8px 0 0 8px;

    }

    #customPageInput {
        height: 2.7rem;
        width: 4.5rem;
        display: flex;
        justify-content: center;

        padding: 0.5rem;
        outline: none;
        border: 1px solid lightgray;
        background: rgb(228, 228, 228);

    }

    #customPageInput:hover {
        outline: none;
        border: 4px solid lightgray;
    }

    .tippy-box[data-theme~='custom-tooltip'] {
        background-color: #133e87;
        color: white;
        border-radius: 50px;
        padding: 10px 10px;
        border: 1px solid white;
        text-align: left;

    }


    .tippy-box[data-theme~='custom-tooltip-sidebar'] {
        background-color: black;
        color: white;

        padding: 10px 10px;


    }







    .fixedheader {
        position: sticky;
        z-index: 1;
        /* top: -0.3rem; */
        top: -0.2rem;
        background: linear-gradient(180deg, lightgray, rgb(193, 193, 193), lightgray) !important;
        font-weight: bold;
        letter-spacing: 0.03rem;
        font-size: 1rem;
        color: black;


    }

    .fixed {
        position: sticky;
        z-index: 1;
        /* top: -0.3rem; */
        top: -0.2rem;
        font-weight: bold;
        color: black;


    }


    .table-container {

        max-height: 67vh;
        overflow-y: auto;


    }

    .table-container::-webkit-scrollbar {
        display: none;
        /* Hides the scrollbar */
    }

    .bg-teal {
        background: teal;
    }


    .dataTables_wrapper .bottom {
        position: fixed;
        bottom: 0;
        width: 100%;
        z-index: 999;
        background: white;
        padding: 0.5rem 1rem;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #ddd;
    }

    .dataTables_paginate {
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .fixed-pagination {
        /* position: fixed; */
        bottom: 0;
        width: 100%;
        z-index: 999;
        background: #133E87;
        padding: 0.3rem 1rem;
        /* Reduced padding for a lower height */
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: flex-end;
        /* Align pagination to the right */
        align-items: center;
        /* Vertically center the content */
        margin: 0;
    }


    .search-container-fixed {
        position: fixed;
        top: 1.5rem;
        background: gray;
        border: 3px solid #305595cd;
        border-radius: 12px;
        left: 35%;
        z-index: 10;
        height: 8.7rem;
        width: 30rem;
        background: white;
        padding-top: 1.5rem;
    }


    .pagination-container-custom {
        position: fixed;
        top: 1.5rem;
        background: gray;
        border: 3px solid #305595cd;
        border-radius: 12px;
        left: 40%;
        z-index: 10;
        height: 8.7rem;
        width: 18rem;
        background: white;
        padding-top: 1.5rem;
    }

    li {
        list-style-type: none;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Check if success or error message is passed
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('success')) {
            Swal.fire({
                title: "Success!",
                text: "Follow-up updated successfully!",
                icon: "success",
                confirmButtonText: "OK"
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname); // Remove query param
            });
        }

        if (urlParams.has('error')) {
            Swal.fire({
                title: "Error!",
                text: "There was an issue updating the follow-up.",
                icon: "error",
                confirmButtonText: "OK"
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname); // Remove query param
            });
        }
    });
</script>
<div id="wrapper" class="vh-100">




    <ul class="d-flex justify-content-center viewport-sidebar vh-100" id=""
        style="width: 4.5rem; padding: 0; display: flex; justify-content: center; ">






        <div class="mx-auto" style="width: 2rem;">

            <div class="w-full ">
                <li id="enter-search-keyword-btn">
                    <div class="" style="margin-top: 2.5rem; " data-bs-toggle="tooltip" data-placement="right"
                        data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                            stroke="white" className="">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>

                    </div>
                </li>



                <li>
                    <div data-toggle="modal" data-target="#filterModal" class="tooltip-sidebar" data-bs-toggle="tooltip"
                        data-placement="right" data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Filter"
                        style="margin-top: 2.8rem;"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                    </div>
                </li>





                <li>
                    <div data-bs-toggle="tooltip" data-toggle="modal" data-target="#disabledFeature"
                        data-placement="right" data-bs-custom-class="custom-tooltip-sidebar"
                        data-tippy-content="Add New Project" class="w-full " style="margin-top: 2.8rem;"><svg
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                            stroke="white" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                </li>
                <li>
                    <div data-toggle="modal" data-target="#indicator-modal" class="w-full " style="margin-top: 2.8rem;"
                        data-bs-toggle="tooltip" data-placement="right" data-bs-custom-class="custom-tooltip-sidebar"
                        data-tippy-content="Help" data-bs-toggle="modal" data-bs-target="#indicator-modal"><svg
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                            stroke="white" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                </li>


                <li>
                    <div id="pagination-btn" data-bs-toggle="tooltip" data-placement="right"
                        data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Entries per Page"
                        class="w-full " style="margin-top: 2.8rem;"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                        </svg>

                    </div>
                </li>
                <li>
                    <div data-bs-toggle="modal" data-bs-target="#outsourcedPayableModal" id="outsourced-payable-btn"
                        data-bs-toggle="tooltip" data-placement="right" data-bs-custom-class="custom-tooltip-sidebar"
                        data-tippy-content="Outsourced Projects" class="w-full " style="margin-top: 2.8rem;"><svg
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                            stroke="white" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M15 8.25H9m6 3H9m6 3H9m3-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </li>





            </div>

            <div class=" " style="margin-top: 11.2rem;">
                <li>
                    <div class="w-full" style="margin-top: 1.65rem;"><a href="../allApps.php" data-bs-toggle="tooltip"
                            data-placement="right" data-bs-custom-class="custom-tooltip-sidebar"
                            data-tippy-content="Home Page">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                                stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </a>
                    </div>
                </li>
                <li>
                    <div class="w-full " style="margin-top: 1.65rem;"><a href="../time-sheet.php"
                            data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Timesheet"><svg
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                                stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </a>
                    </div>
                </li>
                <li>
                    <div class="w-full " style="margin-top: 1.65rem;"><a href="#" data-toggle="modal"
                            data-target="#logoutModal" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Log out">
                            <!-- <svg
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                                stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg> -->
                            <i class="fa-solid fa-arrow-right-from-bracket text-light ml-2"
                                style="font-size: 1.5rem;"></i>

                        </a>
                    </div>
                </li>
                <li>



            </div>




        </div>



    </ul>
    <!-- End of Sidebar -->



    <!-- Content Wrapper -->


    <div id="content-wrapper" class="d-flex flex-column">


        <div class="modal fade text-dark" id="indicator-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Color and Shape References</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-dark">




                        <div class="row justify-content-center text-center mt-1" style="font-size: 0.7rem;">
                            <div class="col-6 col-md-1 border p-1 custom-red mr-1 " style="width: 7rem;">
                                Very Urgent</div>
                            <div class="col-6 col-md-1 border p-1 custom-orange mr-1" style="width: 7rem;">
                                Urgent</div>
                            <div class="col-6 col-md-1 border p-1 custom-white mr-1" style="width: 7rem;">
                                Waiting</div>
                            <div class="col-6 col-md-1 border p-1 mr-1 bg-secondary text-light"
                                style="width: 7rem; border-radius: 5px;">Not Started</div>

                            <div class="col-6 col-md-1 border p-1 custom-green mr-1" style="width: 7rem;">In
                                Progress</div>
                            <div class="col-6 col-md-1 border p-1 mr-1"
                                style="background: yellow; width: 7rem; border-radius: 5px;">On Hold</div>

                            <div class="col-6 col-md-1 border p-1 custom-blue mr-1" style="width: 7rem;">
                                Completed</div>
                            <div class="col-6 col-md-1 border p-1 custom-purple mr-1" style="width: 7rem;">
                                Closed</div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-center mt-5">
                            <div class="d-flex flex-column align-items-center mx-2">
                                <div
                                    style="border: 3px solid darkgreen; width: 5rem; height: 1.4rem; border-radius: 6px;">
                                </div>
                                <p class="text-center mt-1" style="font-size: 0.8rem;">Within Estimated
                                    Delivery Date</p>
                            </div>

                            <div class="d-flex flex-column align-items-center mx-2">
                                <div
                                    style="border: 3px solid crimson; width: 5rem; height: 1.4rem; border-radius: 6px;">
                                </div>
                                <p class="text-center mt-1" style="font-size: 0.8rem;">Estimated Delivery
                                    Date Passed</p>
                            </div>

                            <div class="d-flex flex-column align-items-center mx-2">
                                <div style="border: 2px solid gray; width: 5rem; height: 1.4rem; border-radius: 50%;">
                                </div>
                                <p class="text-center mt-1" style="font-size: 0.8rem;">Outsourced Subproject
                                </p>
                            </div>

                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm bg-gradient"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- popup search bar custom -->

        <div id="popup-overlay" style="position:fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1">
        </div>



        <div id="project-search">
            <div class="shadow-lg search-container-fixed">
                <div style="display: flex; justify-content: center; ">
                    <div class="d-flex">
                        <div class="search-icon-container">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="white" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>

                        </div>

                        <input type="text" id="customSearchInput" placeholder="Search..." />




                    </div>

                </div>
                <div class="d-flex justify-content-end mr-5 mt-1"> <button id="search-close-btn"
                        class="btn btn-secondary"> Close</button></div>


            </div>

        </div>







        <div id="pagination-container-fixed">
            <div class="shadow-lg pagination-container-custom">
                <div style="display: flex; justify-content: center; ">
                    <div class="d-flex">

                        <input type="number" id="customPageInput" min="1" value="20">
                        <p class="mb-1 ml-2 mt-3">entries per page</p>

                    </div>

                </div>
                <div class="d-flex justify-content-end mt-4 mr-4"> <button id="pagination-close-btn"
                        class="btn btn-secondary btn-sm"> Close</button>
                    <button id="applyFilterBtn" class="btn btn-primary btn-sm ms-2"> Apply</button>
                </div>


            </div>

        </div>

        <script>
            $(document).ready(function () {
                $("#applyFilterBtn").on("click", function () {
                    // Apply filter logic here (if needed)

                    // Close the pagination container
                    $("#pagination-container-fixed").hide();
                });

                $("#pagination-close-btn").on("click", function () {
                    $("#pagination-container-fixed").hide();
                });
            });
        </script>



        <div class="modal fade" id="filterModal" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">Filter Projects</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <!-- Project ID Input -->
                            <div class="form-group">
                                <label for="project_id">Project ID</label>
                                <input type="text" class="form-control" id="project_id" name="project_id" pattern="\d+"
                                    title="Only numbers are allowed">
                            </div>
                            <div class="form-group">
                                <label for="customer_contact">Customer</label>
                                <select class="form-control" id="customer_contact" name="customer_contact">
                                    <option value="">Select Customer</option>
                                    <?php
                                    // Fetch customers from the contacts table
                                    $query_customers = "SELECT contact_id, customer_name FROM contacts";
                                    $result_customers = $conn->query($query_customers);
                                    if ($result_customers->num_rows > 0) {
                                        while ($row = $result_customers->fetch_assoc()) {
                                            echo "<option value='" . $row['customer_name'] . "'>" . $row['customer_name'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No customers found</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="p_team">Filter By Team</label>
                                <select class="form-control" id="team_filter" name="team_filter">
                                    <option value="">Select Team</option>
                                    <?php
                                    // Fetch teams from the database
                                    $team_query = "SELECT DISTINCT p_team FROM subprojects";
                                    $team_result = $conn->query($team_query);
                                    if ($team_result->num_rows > 0) {
                                        while ($team_row = $team_result->fetch_assoc()) {
                                            echo "<option value='" . $team_row['p_team'] . "'>" . $team_row['p_team'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No teams found</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="person_name">Name of the Person</label>
                                <select class="form-control" id="person_name" name="person_name">
                                    <option value="">Select Person</option>
                                    <?php
                                    // Fetch names from database
                                    $query = "SELECT user_id, fullname FROM tbl_admin";
                                    $result = $conn->query($query);
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['fullname'] . "'>" . $row['fullname'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No persons found</option>";
                                    }
                                    ?>
                                </select>

                            </div>
                            <div class="form-group">
                                <label for="state-filter" class="text-primary mt-3">Filter by State</label>
                                <select id="state-filter" class="form-control" name="state">
                                    <option value="">Select State</option>
                                    <Option value="QLD">QLD (Queensland)</Option>
                                    <Option value="NSW">NSW (New South Wales)</Option>
                                    <Option value="WA">WA (Western Australia)</Option>
                                    <Option value="SA">SA (South Australia)</Option>
                                    <Option value="VICTORIA">VICTORIA</Option>
                                    <Option value="N/A">Not Applicable</Option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="filter_submit">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>

        </script>

        <div class="table-container " style="max-height: 100vh; overflow-y: auto;">
            <table class="table text-center table-sm table-container" id="sub-projects">
                <!-- <thead class=" bg-gradient" style="background: teal; font-weight: bold; letter-spacing: 0.03rem; font-size: 1.3rem;"> -->
                <thead class="fixedheader" class="">
                    <tr class="text-light align-middle font-weight-normal text-center fixedheader"
                        style="height: 3.5rem; ">
                        <th width="6%" class="fixedheader align-middle font-weight-normal text-center">
                            Id</th>

                        <th width="5%" class="fixedheader align-middle font-weight-normal text-center">
                            Customer</th>
                        <th class="fixedheader align-middle font-weight-normal text-center">State</th>


                        <th width="6%" class="fixedheader align-middle font-weight-normal text-center">PM
                        </th>

                        <th width="15%" class="fixedheader align-middle font-weight-normal text-center">
                            Title</th>


                        <th width="60%" class="fixedheader align-middle font-weight-normal text-center">
                            Subprojects</th>
                        <th width="4%" style="padding-right: 1.8rem;"
                            class="fixedheader align-middle font-weight-normal text-left">
                            Info</th>
                    </tr>


                </thead>
                <tbody style="background: rgb(245, 245, 245) ;">

                </tbody>
            </table>
        </div>

        <script>
            // Fetch main project data
            fetch('../api.php') // Update with the correct API path
                .then(response => response.json())
                .then(data => {


                    const tableBody = document.querySelector('#sub-projects tbody');
                    const userRole = <?php echo json_encode($user_role); ?>;
                    const userId = <?php echo json_encode($user_id); ?>;

                    data.forEach(row => {
                        // Only process if it's not a subproject
                        if (row.subproject_status == null) {
                            const tr = document.createElement('tr');
                            const urgencyColor = row.urgency || 'white';
                            const textColor = (urgencyColor === 'white' || urgencyColor === 'yellow') ? '#000' : '#fff';

                            tr.innerHTML = `
                        <td style="align-content: center; vertical-align: middle; justify-content: center"class="py-0 my-0 ">
                            <div style="display: flex; justify-content: start; align-items: start;">
                                <div  data-project-id="${row.project_id}" style="display: flex; align-items: center; font-size: 0.9rem; justify-content: center; height: 1.5rem; margin-left: 1rem; width: 2.8rem; background-color: ${urgencyColor}; text-align: center; border-radius: 5px; color: ${textColor}; ">
                                    ${row.revision_project_id ? row.revision_project_id : row.project_id}
                                </div>
                                ${row.reopen_status ? `<span class="badge badge-pill badge-danger" style="height: 1.5rem; margin-left: 2px; font-size: 0.7rem; padding: 0.4rem; ">${row.reopen_status}</span>` : ''}
                            </div>
                        </td>
                        <td class="align-middle  py-0 my-0" data-bs-toggle='tooltip' data-placement='bottom' data-tippy-content="<b>Customer Name</b>:${row.customer_id ? row.customer_id : 'N/A'}<br><b>Contact Name</b>:${row.contact_name ? row.contact_name : 'N/A'}<br><b>Email</b>:${row.contact_email ? row.contact_email : 'N/A'}<br><b>Phone</b>: ${row.contact_phone_number ? row.contact_phone_number : 'N/A'}" style="position:relative; ">${row.customer_id ? row.customer_id.split(' ')[0] : 'N/A'}</td>
                        <td>${row.state || row.sub_state || 'N/A'}</td>
                        <td class="align-middle py-0 my-0" style=" ">${row.project_manager ? row.project_manager : 'Qucik Project'} <p hidden>${row.p_team}</p></td>
                        <td class="align-middle py-0 my-0 text-start" style=" "><p id="p_team" hidden>${row.p_team ? row.p_team : "N/A"}</p>${row.project_name ? row.project_name : "N/A"}</td>
                        <td class="subproject-data py-0 my-0" data-reopen-status=${row.reopen_status} data-revision-project-id="${row.revision_project_id}" data-project-id="${row.project_id}" data-project-name="${row.project_name}" data-projectdetails="${row.project_details}" data-urgency="${row.urgency}" data-start_date="${row.start_date}" data-end_date="${row.end_date}" data-assign-to-id="${row.assign_to_id}" data-comments="${row.comments}" data-team="${row.p_team}" data-project-manager="${row.project_managers_id}">Loading...</td>
                    `;

                            tableBody.appendChild(tr);



                        }

                    });

                    // Fetch subproject data after rendering the table
                    return fetch('../subproject_api.php');
                })
                .then(response => response.json())
                .then(subprojectData => {
                    document.querySelectorAll('.subproject-data').forEach(td => {

                        const projectId = td.getAttribute('data-project-id');
                        const projectName = td.getAttribute('data-project-name');
                        const revisionID = td.getAttribute('data-revision-project-id')
                        const reopenStatus = td.getAttribute('data-reopen-status')
                        const urgency = td.getAttribute('data-urgency')
                        const comments = td.getAttribute('data-comments')
                        const team = td.getAttribute('data-team')
                        const start_date = td.getAttribute('data-start_date')
                        const end_date = td.getAttribute('data-end_date')
                        const projectDetails = td.getAttribute('data-projectdetails')
                        const ProjectIDModal = revisionID ? revisionID : projectId;
                        const subprojects = subprojectData.filter(sub => sub.project_id == projectId);
                        const today = new Date().toISOString().split('T')[0]; // Today's date in YYYY-MM-DD format
                        const assignToID = td.getAttribute('data-assign-to-id');
                        const manager = td.getAttribute('data-project-manager');

                        // Generate the subprojects display HTML
                        let subprojectHTML = subprojects.length > 0 ?
                            `
                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                            ${subprojects.map(sub => `
                            <div 
                            data-project-id="${sub.project_id}" 
                            data-subproject-id="${sub.project_id}"
                            data-subproject-status="${sub.subproject_status}" 
                            data-outsourced=${sub.outsourced}
                            data-subprojectName="${sub.subproject_name}"
                            data-subProjectsDetails="${sub.subproject_details}"
                            data-assign-to-id="${sub.assign_to_id}"
                            data-assign-to="${sub.assign_to}"
                            data-subEPT="${sub.sub_EPT}"
                            data-urgency="${sub.urgency}"
                            data-comments='${sub.comments}'
                            data-subEndDate="${sub.sub_end_date}"
                            data-payable_amount="${sub.quoted_cost}"
                            data-service_provider_name="${sub.service_provider_name}"
                             data-bs-toggle="tooltip" data-placement="bottom" data-tippy-content="<b>${sub.subproject_name}</b></br><b>Assigned to:</b> ${sub.assign_to || sub.assign_to_id}</br><b>ETD: </b>${sub.sub_end_date}</br><b>Project Type: </b>${sub.outsourced == 1 ? 'Outsourced' : 'Internal'}<br/><b>Comments: <b/>${formatComments(sub.comments)}" class=" my-1 subproject-anime card shadow-sm subprojects-card" style="padding: 0.3rem; cursor:pointer; border-left: 5px solid ${sub.urgency === 'cancelled' ? 'grey' : sub.urgency}; width: 7.2rem; height: 1.8rem; font-size: 0.8rem; border-radius: ${sub.outsourced == 1 ? '50%' : '6px'}; text-decoration: ${sub.urgency === 'cancelled' ? 'line-through' : 'none'};"
>

                            <div class="card-body px-2 py-0">
                            <div class="d-flex">
                                ${sub.sub_end_date > today ? `<img src="https://www.emoji.co.uk/files/apple-emojis/symbols-ios/956-large-red-circle.png" style="width: 0.5rem; height: 0.5rem;" class="mt-1 " />` : ''}
                                    <span class="ps-2 d-inline-block" style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    ${sub.subproject_name.length > 10 ? sub.subproject_name.slice(0, 10) + '...' : sub.subproject_name}
                                    </span>                            </div>
                            </div>
                        </div>
                                `).join('')}
                            ` :
                            `
                    <div class="d-flex align-items-center" style="gap: 5px;">
                        <div class="btn btn-sm my-auto" style="width: 8rem; height: 2rem; background: bisque; border: 1px solid orange; margin-top: 5px;" role="alert">No Subprojects</div>
                    `;


                        subprojectHTML += `
                                    <div class="d-flex align-items-center" style="gap: 5px;">

                            <div class="subproject-anim d-flex justify-content-center"
                                style="border-radius: 50%; height: 1.5rem; width: 1.5rem; font-size: 1.1rem; color: white; background: #305595cd; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#addSubprojectModal"
                                 data-revision-id="${ProjectIDModal}"
                                 data-project-id="${projectId}"
                                 data-reopen-status="${reopenStatus}"
                                 ">                                +
                            </div>
                            </div>
                        `;

                        td.innerHTML = subprojectHTML;


                        // ✅ Add a new `<td>` for the info button
                        const infoTd = document.createElement('td');
                        infoTd.classList.add('align-middle', 'text-center'); // Center align the icon
                        infoTd.innerHTML = `
                                <div class="d-flex justify-content-center align-middle info-icon" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#info-modal" 
                                    data-project-id="${projectId}" 
                                    data-revision-id="${ProjectIDModal}" 
                                    data-reopen-status="${reopenStatus}" 
                                    data-project-name="${projectName}" 
                                    data-projectdetails="${projectDetails}" 
                                    data-urgency="${urgency}" 
                                    data-start_date="${start_date}" 
                                    data-end_date="${end_date}" 
                                    data-team="${team}" 
                                    data-comments='${comments}'
                                    data-assignid = "${assignToID}"
                                    data-manager="${manager}"
                                    data-team="${team}"
                                    style="align-items: center; color: #133e87; width: 1.2rem; height: 1.2rem; border: 2px solid #133e87; border-radius: 50%; font-weight: bold; cursor: pointer;">
                                    i
                                </div>
                                `;

                        td.parentElement.appendChild(infoTd);


                        // Update the td content with the subprojects and the plus button

                    });
                    // Initialize DataTable after all rows are populated

                    function formatComments(comments) {
                        if (!comments) return "N/A"; // Handle empty comments

                        const separator = "-".repeat(60); // Shorter separator for tooltip readability

                        return comments
                            .split("\n\n") // Split by double new lines (each entry)
                            .map(comment => {
                                let parts = comment.split(" --- "); // Split by '---'
                                if (parts.length === 3) {
                                    let authorParts = parts[2].split("-"); // Split by '-'
                                    let author = authorParts[0]?.trim(); // Get manager name
                                    let role = authorParts[1]?.trim() == "1" ? "PM" : authorParts[1]?.trim() == "2" ? "Engineer" : "Unknown";

                                    return `<br/>📅 <b>Date:</b> ${parts[0]}<br/>📝 ${parts[1]}<br/>👤 <b>Written by:</b> ${author} (${role})<br/>${separator}`;
                                }
                                return comment; // Return as-is if format is unexpected
                            })
                            .join("<br/>"); // Use <br/> to format inside tooltip
                    }


                    $(document).ready(function () {
                        var table = $('#sub-projects').DataTable({
                            paging: true,
                            pageLength: 100, // Default number of entries per page
                            lengthMenu: [20, 40, 50, 100], // Custom pagination options
                            searching: true, // Enable search functionality
                            ordering: true, // Enable column ordering
                            stateSave: true, // ✅ Corrected stateSave option
                            "order": [
                                [0, "desc"]
                            ], // Order by the first column (index 0) in descending order
                            dom: '<"top">rt<"fixed-pagination"p><"clear">'
                        });
                        // ✅ Function to check and toggle the Clear button
                        function toggleClearButton() {
                            let searchValue = $('#customSearchInput').val().trim();
                            if (searchValue.length > 0) {
                                $('#clearSearch').show();
                            } else {
                                $('#clearSearch').hide();
                            }
                        }
                        // ✅ Search functionality for main input
                        $('#customSearchInput').on('keyup', function () {
                            table.search(this.value).draw();
                            toggleClearButton(); // Check and show/hide button
                        });
                        // ✅ Clear search input and reset DataTable search
                        $('#clearSearch').on('click', function () {
                            $('#customSearchInput').val(''); // Clear input field
                            table.search('').draw(); // Reset DataTable search
                            $(this).hide(); // Hide the clear button
                        });
                        // ✅ Restore search value on reload if stateSave is enabled
                        var state = table.state.loaded();
                        if (state && state.search && state.search.search) {
                            $('#customSearchInput').val(state.search.search); // Restore search value
                            toggleClearButton(); // Ensure the clear button appears if needed
                        }
                        // ✅ Restore search value on reload if stateSave is enabled
                        var state = table.state.loaded();
                        if (state && state.search && state.search.search) {
                            $('#customSearchInput').val(state.search.search); // Restore search value
                            toggleClearButton(); // Ensure the clear button appears if needed
                        }
                        // ✅ Change entries per page
                        $('#customPageInput').on('change', function () {
                            var value = parseInt($(this).val(), 10);
                            if (!isNaN(value) && value > 0) {
                                table.page.len(value).draw();
                            }
                        });



                        // ✅ **Filter Modal Handling**
                        // ✅ Filter Modal Handling - Reset Fields
                        $('#filterModal').on('show.bs.modal', function () {
                            $('#project_id').val('');
                            $('#customer_contact').val('');
                            $('#person_name').val('');
                            $('#team_filter').val('');
                            $('#state-filter').val(''); // ✅ Corrected selector
                            $('#clearSearch').hide(); // ✅ Hide Clear button when resetting filters
                        });

                        $('#clearSearch').on('click', function () {
                            // ✅ Clear search input
                            $('#customSearchInput').val('');

                            // ✅ Clear all column-specific filters
                            table.columns().search("");

                            // ✅ Remove custom search functions
                            $.fn.dataTable.ext.search = [];

                            // ✅ Reset global search and redraw the table
                            table.search("").draw();

                            // ✅ Hide the clear button
                            $(this).hide();

                            // ✅ Reset filter modal fields (optional, if needed)
                            $('#project_id, #customer_contact, #person_name, #team_filter, #state-filter').val('');
                        });

                        // ✅ Restore search value & show clear button on reload if stateSave is enabled
                        var state = table.state.loaded();
                        if (state) {
                            if (state.search && state.search.search) {
                                $('#customSearchInput').val(state.search.search); // Restore search value
                                toggleClearButton(); // Ensure the clear button appears if needed
                            }

                            // ✅ Check if any column filters exist and show Clear button
                            let hasFilters = Object.values(state.columns).some(col => col.search.search);
                            if (hasFilters) {
                                $('#clearSearch').show();
                            }
                        }


                        // ✅ Apply Filter on Form Submission
                        $('#filterModal form').on('submit', function (e) {
                            e.preventDefault(); // Prevent default form submission

                            // ✅ Capture filter values
                            var projectId = $('#project_id').val().trim();
                            var customerContact = $('#customer_contact').val().trim();
                            var personName = $('#person_name').val().trim();
                            var pTeam = $('#team_filter').val().trim();
                            var stateFilter = $('#state-filter').val().trim();

                            // ✅ Debugging: Log values to confirm they are captured correctly
                            console.log("🚀 Filtering - Project ID:", projectId, "Customer Contact:", customerContact, "Person Name:", personName, "Team:", pTeam);

                            // ✅ Ensure the team value is correctly fetched
                            if (!pTeam || pTeam === "Select Team") {
                                console.log("⚠️ No team selected or invalid value!");
                            }

                            // ✅ Reset all previous filters
                            $.fn.dataTable.ext.search = [];

                            // ✅ Apply default column filters
                            table.columns().search("");

                            if (projectId !== "") {
                                table.column(0).search(projectId);
                            }
                            if (pTeam !== "") {
                                table.column(3).search(pTeam);
                            }
                            if (customerContact !== "") {
                                table.column(1).search(customerContact);
                            }
                            if (stateFilter !== "") {
                                table.column(2).search(stateFilter);
                            }
                            if (personName !== "") {
                                table.column(3).search(personName);
                            }

                            // ✅ Apply the team filter
                            if (pTeam !== "") {
                                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                                    var rowNode = table.row(dataIndex).node(); // Get row node
                                    var teamValue = $(rowNode).find('td:eq(3) p').text().trim(); // Extract text inside <p> in column 3

                                    // ✅ Debugging: Log extracted values for each row
                                    console.log("🔍 Checking row:", dataIndex, "| Found Team:", teamValue, "| Selected Filter:", pTeam);

                                    return teamValue === pTeam; // Apply filter
                                });
                            }

                            // ✅ Apply filters and redraw table
                            // ✅ Apply filters and redraw table
                            table.draw();
                            alert("✅ Filter applied successfully!"); // Show success alert
                            setTimeout(function () {
                                $('#filterModal').modal('hide'); // Close the modal after clicking OK
                            }, 100); // Slight delay to ensure alert is processed

                            // ✅ Show/hide the clear button based on applied filters
                            if (projectId !== "" || customerContact !== "" || personName !== "" || pTeam !== "" || stateFilter !== "") {
                                $('#clearSearch').show();
                            } else {
                                $('#clearSearch').hide();
                            }

                        });




                        function initializeTooltips() {
                            tippy('[data-bs-toggle="tooltip"]', {
                                allowHTML: true,
                                placement: 'bottom',
                                theme: 'custom-tooltip',
                                interactive: true
                            });
                        }

                        // ✅ Initialize tooltips on page load
                        initializeTooltips();

                        // ✅ Re-initialize tooltips after table redraw
                        table.on('draw.dt', function () {
                            initializeTooltips();
                        });


                    });


                })
                .catch(error => console.error('Error fetching data:', error));


            // Function to handle viewing project details
            function viewProjectDetails(projectId) {
                window.location.href = `project-details.php?id=${projectId}`;
            }
        </script>

        <div class="modal fade" id="info-modal" tabindex="-1" aria-labelledby="info-modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <form method="POST" action="update_subproject.php" name="submitForm" id="submitForm">
                    <input type="" name="user_id" hidden value="<?php echo $user_id ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="info-modalLabel">Information <span
                                    id="information_projectID"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body mx-auto" style="width:100%;">
                            <span id="hello"></span>
                            <input type="hidden" name="projectId" id="projectId" value="your_project_id_here" />

                            <table class="table table-responsive table-borderless">
                                <tr>
                                    <td class="text-left text-primary" style="width: 11rem;">Name:</td>
                                    <td>
                                        <input type="text" id="projectName" class="form-control" name="projectName"
                                            required value="Existing Project Name" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left text-primary">Description:</td>
                                    <td>
                                        <textarea style="width: 35rem;" class="form-control" name="projectDetails"
                                            required>Existing project description</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left text-primary">Status:</td>
                                    <td>
                                        <select id="urgencySelect" class="form-control" name="urgency"
                                            onchange="updateUrgencyColor(this)">
                                            <option value="red" style="background:red; color:white;">Very Urgent
                                            </option>
                                            <option value="orange" style="background:orange; color:white;">Urgent
                                            </option>
                                            <option value="white" style="background:white; color:black;">Waiting
                                            </option>
                                            <option value="green" style="background:green; color:white;">In Progress
                                            </option>
                                            <option value="navy" style="background:navy; color:white;">Completed
                                            </option>
                                            <option value="purple" style="background:purple; color:white;">Closed
                                            </option>
                                            <option value="yellow" style="background:yellow; color:black;">On Hold
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left text-primary">Start Date:</td>
                                    <td>
                                        <input type="date" class="form-control" name="startDate" value="2025-01-01"
                                            readonly />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left text-primary">Estimated Delivery Date:</td>
                                    <td>
                                        <input type="date" class="form-control" name="endDate" value="2025-12-31" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left text-primary">Comments:</td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2" style="width: 128%;">
                                            <textarea class="form-control" name="comments" rows="2" readonly
                                                required></textarea>
                                            <button type="button" data-bs-dismiss="modal" id="followup_projectid"
                                                class="btn btn-primary" style="width: 25%;" data-bs-toggle="modal"
                                                data-bs-target="#followup-modal">
                                                Follow Up
                                            </button>


                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-left text-primary">Engineer</td>
                                    <td>
                                        <select class="form-control" name="engineer_id" id="engineer_id">
                                            <option value="">Select Engineer</option>
                                            <?php
                                            // Fetch engineers from the database
                                            $query = "SELECT user_id, fullname FROM tbl_admin WHERE 1";
                                            $result = $conn->query($query);

                                            // Check if there are results
                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo '<option value="' . htmlspecialchars($row['user_id']) . '">' . htmlspecialchars($row['fullname']) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="">No engineers available</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-left text-primary">Project Manager</td>
                                    <td>
                                        <select class="form-control" name="project_manager_id" id="manager">
                                            <option value="">Select Project Manager</option>
                                            <?php
                                            // Fetch engineers from the database
                                            $query = "SELECT user_id, fullname FROM tbl_admin WHERE 1";
                                            $result = $conn->query($query);

                                            // Check if there are results
                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo '<option value="' . htmlspecialchars($row['user_id']) . '">' . htmlspecialchars($row['fullname']) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="">No engineers available</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left text-primary">Team:</td>
                                    <td>
                                        <select class="form-select" name="p_team" id="team_info">
                                            <option value="">Select Team</option>
                                            <?php

                                            $query = "SELECT DISTINCT p_team FROM projects";
                                            $result = mysqli_query($conn, $query);

                                            if ($result) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<option value='" . htmlspecialchars($row['p_team']) . "'>" . htmlspecialchars($row['p_team']) . "</option>";
                                                }
                                            } else {
                                                echo "<option>Error: " . mysqli_error($conn) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-left text-primary">Files:</td>
                                    <td>
                                        <div class="d-flex justify-content-start">
                                            <button type="button" class="btn btn-primary btn-sm industrial-btn"
                                                onclick="window.open('https://drive.google.com/drive/u/0/folders/0AMBoRd5BFG7XUk9PVA', '_blank')">
                                                Open Industrial Team Shared Drive
                                            </button>
                                            &nbsp;
                                            &nbsp;
                                            <button type="button" class="btn btn-primary btn-sm building-btn"
                                                onclick="window.open('https://drive.google.com/drive/u/0/folders/0AE4knKSOSdZOUk9PVA', '_blank')">
                                                Open Building Team Shared Drive
                                            </button>
                                        </div>
                                    </td>
                                </tr>


                            </table>

                            <div class="d-flex justify-content-between my-3">
                                <div class="text-left font-weight-bold">Subprojects:</div>
                                <div> Total: <span id="totalSubprojects" class="text-primary font-weight-bold"></span>
                                </div>
                            </div>
                            <div class="info-table-container table-responsive" style="max-height: 200px">
                                <table class="mx-auto mt-3 table" style="width:95%" id="subproject-modal">
                                    <thead class="">
                                        <tr class="fixed" style="background: #133E87; color: white;">
                                            <th class=" fixed text-white text-center">Name</th>
                                            <th class=" fixed text-white text-center">Assigned To</th>
                                            <th class=" fixed text-white text-center">Start Date</th>
                                            <th class=" fixed text-white text-center">End Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table rows will be dynamically inserted here -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-left font-weight-bold">Payment Status:(Receivables) </div>
                            <div class="table-responsive info-table-container mt-3" style="max-height: 200px">
                                <table class="m-auto table" style="width:95%" id="payment_status_recivables">
                                    <thead class="">
                                        <tr class="fixed" style="background: #133E87; color: white;">
                                            <th class=" fixed text-white text-center" width="10%">Invoice No.</th>
                                            <th class=" fixed text-white text-center" width="10%">Service Date </th>
                                            <th class=" fixed text-white text-center" width="10%">Due Date </th>
                                            <th class=" fixed text-white text-center" width="10%">Payment Status</th>
                                            <th class=" fixed text-white text-center" width="10%">Amount</th>
                                            <th class=" fixed text-white text-center" width="35%">Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Payment rows will be dynamically inserted here -->
                                    </tbody>
                                </table>

                            </div>


                        </div>
                        <div class="container mt-4">
  <div class="d-flex flex-wrap gap-3 p-3 border rounded-4 bg-white shadow-sm justify-content-between align-items-center">

    <div class="d-flex flex-column text-center px-3 py-2 bg-body-tertiary rounded-3 border-start border-4 border-primary">
      <small class="text-secondary text-uppercase">Total Receivable</small>
      <div id="totalReceivableAmount" class="fw-semibold text-primary fs-5">$0.00</div>
    </div>

    <div class="d-flex flex-column text-center px-3 py-2 bg-body-tertiary rounded-3 border-start border-4 border-danger">
      <small class="text-secondary text-uppercase">Total Payable</small>
      <div id="totalPayableAmount" class="fw-semibold text-danger fs-5">$0.00</div>
    </div>

    <div class="d-flex flex-column text-center px-3 py-2 bg-body-tertiary rounded-3 border-start border-4 border-success">
      <small class="text-secondary text-uppercase">Profit</small>
      <div id="profitAmount" class="fw-semibold text-success fs-5">$0.00</div>
    </div>

    <div class="d-flex flex-column text-center px-3 py-2 bg-body-tertiary rounded-3 border-start border-4 border-dark">
      <small class="text-secondary text-uppercase">Loss</small>
      <div id="profitLossAmount" class="fw-semibold text-dark fs-5">$0.00</div>
    </div>

  </div>
</div>


                        <!-- Modal Footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="submitForm" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="followup-modal" tabindex="-1" aria-labelledby="followupModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="followupModalLabel">
                            Follow Up: <span id="followup-id"></span>
                        </h5>
                        <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="update_subproject.php" method="post">
                            <input type="hidden" id="follow_project_id" name="project_id">



                            <div class="mb-3">
                                <label for="followup_comments" class="form-label">Follow Up</label>
                                <textarea name="followup_comments" id="followup_comments" class="form-control"
                                    rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="followup_comments" class="form-label">Signature</label>

                                <?php
                                $query = "SELECT fullname FROM tbl_admin WHERE user_id = '$user_id'";
                                $result = $conn->query($query);
                                $row = $result->fetch_assoc();
                                $fullname = $row['fullname'] ?? "Not Found"; // Default if not found
                                ?>

                                <!-- Visible field displaying the Full Name -->
                                <input type="text" class="form-control"
                                    value="<?php echo htmlspecialchars($fullname); ?>" disabled>

                                <!-- Hidden field passing the User ID -->
                                <input type="hidden" id="manager_id" name="manager_id"
                                    value="<?php echo htmlspecialchars($user_id); ?>">
                            </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary bg-gradient"
                            data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_followup" class="btn btn-success bg-gradient">Save
                            Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.addEventListener('click', function (event) {
                    if (event.target.closest('.info-icon')) {
                        const icon = event.target.closest('.info-icon');

                        // Get data attributes
                        const projectName = icon.getAttribute('data-project-name');
                        const projectDetails = icon.getAttribute('data-projectdetails');
                        const urgency = icon.getAttribute('data-urgency');
                        const startDate = icon.getAttribute('data-start_date');
                        const endDate = icon.getAttribute('data-end_date');
                        const team = icon.getAttribute('data-team');
                        const comments = icon.getAttribute('data-comments');
                        const projectId = icon.getAttribute('data-project-id');
                        const revision = icon.getAttribute('data-revision-id');
                        const assignID = icon.getAttribute('data-assignid');
                        const manager = icon.getAttribute('data-manager');

                        console.log(team);

                        // Set values to modal inputs
                        document.getElementById('projectId').value = projectId;
                        document.getElementById('projectName').value = projectName || '';
                        document.getElementById('information_projectID').textContent = revision !== 'null' ? revision : projectId;
                        document.querySelector('#info-modal textarea[name="projectDetails"]').value = projectDetails || '';
                        document.querySelector('#info-modal select#urgencySelect').value = urgency || 'white';
                        document.querySelector('#info-modal input[name="startDate"]').value = startDate || '';
                        document.querySelector('#info-modal input[name="endDate"]').value = endDate || '';
                        document.querySelector('#info-modal textarea[name="comments"]').value = formatComments(comments);
                        document.getElementById('engineer_id').value = assignID;
                        document.getElementById('manager').value = manager;
                        document.getElementById('team_info').value = team;

                        // Set data-project-id attribute to followup_projectid
                        const followupProjectIdElement = document.getElementById('followup_projectid');
                        if (followupProjectIdElement) {
                            followupProjectIdElement.setAttribute('data-project-id', projectId);
                        }

                        // Pass projectId to the Follow-Up Modal
                        const followUpModal = document.getElementById('followup-modal');
                        if (followUpModal) {
                            document.getElementById('follow_project_id').value = projectId; // Hidden input
                            document.getElementById('followup-id').textContent = projectId; // Display project ID in modal title
                        }


                        // Change the background color based on the selected value
                        const urgencySelect = document.querySelector('#info-modal select#urgencySelect');
                        urgencySelect.style.backgroundColor = urgencySelect.value;
                        const industrialButton = document.querySelector('.industrial-btn');
                        const buildingButton = document.querySelector('.building-btn');

                        function formatComments(comments) {
                            if (!comments) return ""; // Handle empty comments

                            const separator = "-".repeat(103); // Creates a long line of dashes

                            return comments
                                .split("\n\n") // Split by double new lines (each entry)
                                .map(comment => {
                                    let parts = comment.split(" --- "); // Split by '---'
                                    if (parts.length === 3) {
                                        let authorParts = parts[2].split("-"); // Split by '-'
                                        let author = authorParts[0].trim(); // Get manager name
                                        let role = authorParts[1]?.trim() == 1 ? "PM" : authorParts[1]?.trim() == 2 ? "Engineer" : "Unknown";

                                        return `Date: ${parts[0]}\n${parts[1]}\nWritten by: ${author} (${role})\n${separator}`;
                                    }
                                    return comment; // Return as-is if format is unexpected
                                })
                                .join("\n"); // Use single newline to remove extra gap
                        }

                        // ✅ Fetch Subprojects and Invoice data that match the selected project ID
                        fetch(`../subproject_api.php?project_id=${projectId}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! Status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(subprojectData => {
                                console.log("All Retrieved Subprojects:", subprojectData);

                                // Filter subprojects that exactly match the selected projectId
                                const matchedSubprojects = subprojectData.filter(sub => sub.project_id == projectId);

                                // Count matched subprojects
                                const subprojectCount = matchedSubprojects.length;

                                // Print in console
                                console.log(`Project ID: ${projectId} has ${subprojectCount} matched subprojects.`);

                                // Display the count in the designated element
                                document.getElementById('totalSubprojects').textContent = subprojectCount;

                                // Clear the table body before inserting new rows
                                const tbody = document.querySelector('#subproject-modal tbody');
                                tbody.innerHTML = ''; // Clear previous rows

                                // Append subproject details to the table
                                matchedSubprojects.forEach(subproject => {
                                    console.log(`Subproject Name: ${subproject.subproject_name}`);
                                    const row = `
                                <tr>
                                    <td class="text-center">${subproject.subproject_name}</td>
                                    <td class="text-center">${subproject.assign_to || 'N/A'}</td>
                                    <td class="text-center">${subproject.start_date || 'N/A'}</td>
                                    <td class="text-center">${subproject.sub_end_date || 'N/A'}</td>
                                </tr>
                            `;
                                    tbody.insertAdjacentHTML('beforeend', row);
                                });
                            })
                            .catch(error => {
                                console.error("Error fetching subprojects:", error);
                            });

                        // Fetch finance data that match the selected project ID
                        // Fetch finance data that match the selected project ID
                        fetch(`../invoice_api.php?project_id=${projectId}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! Status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(financeData => {
                                console.log("All Retrieved Finance Data:", financeData);

                                // Separate Receivables and Payables
                                const receivables = [
                                    ...(financeData.csa_finance_invoiced || []),
                                    ...(financeData.csa_finance_readytobeinvoiced?.filter(invoice => invoice.project_status !== 'Invoiced') || []),
                                    ...(financeData.csa_finance_uninvoiced?.filter(invoice => invoice.project_status !== 'MovedToReadyToBeInvoicedTable') || [])
                                ];



                                // Filter invoices by projectId
                                const matchedReceivables = receivables.filter(invoice => invoice.project_id == projectId);

                                // Update Receivables Table
                                const receivablesTbody = document.querySelector('#payment_status_recivables tbody');
                                receivablesTbody.innerHTML = ''; // Clear previous rows

                                if (matchedReceivables.length === 0) {
                                    receivablesTbody.innerHTML = `
                                        <tr>
                                            <td class="text-center" colspan="6">No receivables found for this project.</td>
                                        </tr>
                                    `;
                                } else {
                                    let totalAmount = 0; // ✅ FIX: Initialize the variable before using it

                                    matchedReceivables.forEach(invoice => {
                                        let paymentStatus = 'Unknown';
                                        let badgeColor = '#6c757d'; // Default grey
                                        const amount = parseFloat(invoice.amount) || 0;
                                        totalAmount += amount;
                                        const formattedAmount = amount ? '$' + amount.toFixed(2) : 'N/A';
                                        if (invoice.source_table === 'csa_finance_uninvoiced') {
                                            paymentStatus = 'Uninvoiced Projects';
                                            badgeColor = '#009FE2'; // Blue
                                        } else if (invoice.source_table === 'csa_finance_readytobeinvoiced') {
                                            paymentStatus = 'Ready to Invoiced';
                                            badgeColor = '#dc3545'; // Red
                                        } else if (invoice.source_table === 'csa_finance_invoiced') {
                                            if (!invoice.payment_status || invoice.payment_status.toLowerCase() === 'null') {
                                                // Handles null, undefined, or "null" as a string
                                                paymentStatus = 'Not Paid';
                                                badgeColor = '#dc3545'; // Red
                                            } else if (invoice.payment_status === 'partiallyPaid') {
                                                paymentStatus = 'Partially Paid';
                                                badgeColor = '#DFA934'; // Yellowish
                                            } else {
                                                paymentStatus = 'Paid Projects';
                                                badgeColor = '#28a745'; // Green
                                            }
                                        }

                                        const row = `
                                                        <tr>
                                                            <td class="text-center">${invoice.invoice_number || invoice.invoice_no || 'N/A'}</td>
                                                            <td class="text-center">${invoice.service_date || 'N/A'}</td>
                                                            <td class="text-center">${invoice.due_date || 'N/A'}</td>
                                                            <td class="text-center">
                                                                <span class="badge badge-pill" style="background-color: ${badgeColor};">
                                                                    ${paymentStatus}
                                                                </span>
                                                            </td>
                                                                            <td class="text-center">${invoice.amount || 'N/A'}</td>

                                                            <td class="text-center">${invoice.comments || 'N/A'}</td>
                                                        </tr>
                                                    `;
                                        receivablesTbody.insertAdjacentHTML('beforeend', row);
                                    });
                                    document.getElementById('totalReceivableAmount').textContent = '$' + totalAmount.toFixed(2);
                                    let totalPayable = 0;
                                    const allPayables = [
                                        ...(financeData.ready_to_pay || []),
                                        ...(financeData.unpaidinvoices || []),
                                        ...(financeData.paidinvoices || [])
                                    ];

                                    // ✅ Filter by projectId before summing
                                    const matchedPayables = allPayables.filter(item => item.project_id == projectId);

                                    matchedPayables.forEach(item => {
                                        const amount = parseFloat(item.amount) || 0;
                                        totalPayable += amount;
                                    });

                                    document.getElementById('totalPayableAmount').textContent = '$' + totalPayable.toFixed(2);

                                    const profit = totalAmount - totalPayable;

                                    // Update profit display
                                    const profitSpan = document.getElementById('profitAmount');
                                    if (profit > 0) {
                                        profitSpan.textContent = '+$' + profit.toFixed(2);
                                        profitSpan.className = 'text-success';
                                    } else {
                                        profitSpan.textContent = '$0.00';
                                        profitSpan.className = 'text-secondary';
                                    }

                                    // Update loss display (new logic)
                                    const lossSpan = document.getElementById('profitLossAmount');
                                    if (profit < 0) {
                                        lossSpan.textContent = '-$' + Math.abs(profit).toFixed(2);
                                        lossSpan.className = 'text-danger';
                                    } else {
                                        lossSpan.textContent = '$0.00';
                                        lossSpan.className = 'text-secondary';
                                    }



                                }


                            })
                            .catch(error => {
                                console.error("Error fetching finance data:", error);

                                // Display error message in both tables
                                document.querySelector('#payment_status_recivables tbody').innerHTML = `
                                        <tr>
                                            <td class="text-center" colspan="6">Error loading receivables data. Please try again.</td>
                                        </tr>
                                    `;


                            });


                    }
                });
            });
        </script>



        <div class="modal fade" id="subprojectModal" tabindex="-1" aria-labelledby="subprojectModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subprojectModalLabel">Subproject Details</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="updateSubprojectForm" action="update_subproject.php" method="POST">

                            <input type="hidden" id="subproject_id" name="subproject_id">
                            <input type="" name="current_user_id" hidden value="<?php echo $user_id; ?>">
                            <label hidden class="control-label " for="outsourced-yes">Project Type:</label>
                            <div>
                                <!-- Internal Project Type -->
                                <div hidden class="form-group text-left">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input mt-1" type="radio" name="outsourced"
                                            id="outsourced-no" value="0">
                                        <label class="form-check-label d-flex" for="outsourced-no">
                                            Internal
                                            <div class="ml-2 mt-1"
                                                style="border: 2px solid gray; width: 3.6rem; height: 1rem;"></div>
                                        </label>
                                    </div>

                                    <!-- Outsourced Project Type -->
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input mt-1" type="radio" name="outsourced"
                                            id="outsourced-yes" value="1">
                                        <label class="form-check-label d-flex" for="outsourced-yes">
                                            Outsourced
                                            <div class="ml-2 mt-1"
                                                style="border: 2px solid gray; width: 3.6rem; height: 1rem; border-radius: 50%;">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="form_submitted" value="true" />

                                <input type="hidden" value="" name="subproject_status" id="subproject_status">
                                <input type="hidden" value="" name="project_id" id="project_id">

                                <!-- Subproject Name Input Field -->
                                <div class="mb-3 text-left">
                                    <label for="subprojectName" class="form-label">Subproject Name</label>
                                    <input type="text" class="form-control fw-bold" id="subprojectName"
                                        name="subproject_name" required>
                                    <select name="subproject_name" class="form-control fw-bold"
                                        id="service_provider_name" style="display: none;">
                                        <option disabled selected value="N/A">Select Service Provider</option>
                                        <?php

                                        $query = "SELECT name FROM services";
                                        $result = mysqli_query($conn, $query);

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3 text-left">
                                    <label for="subprojectDetails" class="form-label">Subproject Details:</label>
                                    <textarea class="form-control fw-bold" id="subprojectDetails" required
                                        name="subproject_details" rows="3"
                                        placeholder="Enter subproject details"></textarea>
                                </div>
                                <div class="mb-3 text-left">
                                    <label for="assign_to" required class="form-label service_provider_label"
                                        id="service_provider_label"></label>
                                    <select class="form-control assign_to fw-bold" name="assign_to_id_for_internal"
                                        required id="assign_to_id">
                                        <option value="">Select Employee</option>
                                        <?php
                                        // Execute the query to fetch employees
                                        $employeeQuery = "SELECT user_id, fullname FROM tbl_admin WHERE raeAccess IN (1, 2, 3)";
                                        $employeeResult = $conn->query($employeeQuery);

                                        // Check if the query returned results
                                        if ($employeeResult && $employeeResult->num_rows > 0) {
                                            while ($employee = $employeeResult->fetch_assoc()) {
                                                $selected = (isset($subproject['assign_to']) && $subproject['assign_to'] == $employee['user_id']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($employee['user_id']) . '" ' . $selected . '>' . htmlspecialchars($employee['fullname']) . '</option>';
                                            }
                                        } else {
                                            echo '<option value="">No employees available</option>';
                                        }
                                        ?>
                                    </select>
                                </div>





                                <div class="mb-3 text-left">
                                    <select name="service_provider_name" class="form-control fw-bold"
                                        id="service_provider_name" style="display: none;">
                                        <option disabled selected value="null">Select Service Provider</option>
                                        <?php

                                        $query = "SELECT name FROM services";
                                        $result = mysqli_query($conn, $query);

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>



                                <div class="mb-3 text-left">
                                    <label for="estimateHours" class="form-label">Estimated Hours:</label>
                                    <input type="text" value="" class="form-control fw-bold" id="estimateHours" required
                                        name="sub_EPT" placeholder="Enter estimated hours">
                                </div>
                                <div class="mb-3 text-left">
                                    <label for="estimateHours" class="form-label">Estimated date:</label>
                                    <input type="date" value="" class="form-control fw-bold" id="sub_end_date" required
                                        name="sub_end_date" placeholder="Enter estimated date">
                                </div>
                                <div class="form-group text-left">
                                    <label class="control-label" required for="project_status">Status:</label>
                                    <div class="form-group">
                                        <select class="form-control fw-bold" id="urgency_value"
                                            style="background-color: red;" name="urgency_status" required>
                                            <option style="background: red; color: white;" value="red">Very Urgent
                                            </option>
                                            <option style="background: orange; color: white;" value="orange">Urgent
                                            </option>
                                            <option style="background: white;" value="white">Waiting </option>
                                            <option style="background: green; color: white;" value="green">In Progress
                                            </option>
                                            <option style="background: navy; color: white;" value="navy">Completed
                                            </option>
                                            <option style="background: purple; color: white;" value="purple">Closed
                                            </option>
                                            <option
                                                style="background: grey; color: black; text-decoration: line-through;"
                                                value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 text-left">
                                    <label for="comments" class="form-label">Comments:</label>
                                    <textarea type="text" class="form-control fw-bold" id="comments"
                                        placeholder="Add Follow Up" name="comments" value=""></textarea>
                                </div>
                                <div class="mb-3 text-left Payable_amount">
                                    <label for="Payable_amount" class="form-label">Enter Payable Amount:</label>
                                    <input type="number" class="form-control fw-bold" id="Payable_amount"
                                        name="quoted_cost" value="">
                                </div>
                            </div>
                            <div class="modal-footer d-flex justify-content-between">
                                <div>
                                    <a href="#" class="delete_subproject" id="delete_subproject"
                                        title="Delete Subproject" onclick="openDeletePopup(this)" data-project-id="123"
                                        data-subproject-id="456" data-subproject-status="Active"
                                        data-subprojectName="Example Subproject">
                                        <i class="fa-solid fa-trash text-danger" style="font-size: 1.1rem;"></i>
                                    </a>
                                </div>

                                <div>
                                    <button type="button" class="btn btn-secondary btn-sm bg-gradient"
                                        data-dismiss="modal">Close</button>
                                    <button type="submit" name="save_subprojects"
                                        class="btn btn-success btn-sm bg-gradient">Save changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <script>
            // Function to open delete confirmation popup
            function openDeletePopup(element) {
                // Extract values from the clicked element
                const projectId = element.getAttribute('data-project-id'); // Main project ID
                const subprojectId = element.getAttribute('data-subproject-id'); // Corrected reference
                const subprojectStatus = element.getAttribute('data-subproject-status');
                const subprojectName = element.getAttribute('data-subproject-name'); // Ensure correct attribute

                // Show confirmation popup
                Swal.fire({
                    title: `Are you sure?`,
                    text: `Do you want to delete the subproject: ${subprojectName}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteSubproject(subprojectId, subprojectStatus); // Pass correct ID
                    }
                });
            }

            // Function to handle the delete action
            function deleteSubproject(subprojectId, subprojectStatus) {
                // Ensure subprojectId is valid before redirecting
                if (!subprojectId) {
                    console.error("Invalid Subproject ID");
                    return;
                }

                // Redirect to delete handler via GET request
                window.location.href = `update_subproject.php?delete_project=true&project_id=${subprojectId}&subproject_status=${subprojectStatus}`;
            }
        </script>

        <script>
            $(document).on('click', '.subproject-anime', function (event) {
                if (event.target.closest('.subproject-anime')) {
                    const card = event.target.closest('.subproject-anime'); // Get the clicked subproject card

                    // Retrieve data attributes
                    const projectId = card.getAttribute('data-project-id') || 'N/A';
                    const subprojectStatus = card.getAttribute('data-subproject-status') || 'N/A';
                    const OutSourced = card.getAttribute('data-outsourced') || '0'; // Default to 0 if null
                    const SubprojectName = card.getAttribute('data-subprojectName');
                    const subprojectDetails = card.getAttribute('data-subProjectsDetails');
                    const subEPT = card.getAttribute('data-subEPT');
                    const urgency = card.getAttribute('data-urgency');
                    const comments = card.getAttribute('data-comments');
                    const subEndDate = card.getAttribute('data-subEndDate');
                    const assignToID = card.getAttribute('data-assign-to-id');
                    const serviceName = card.getAttribute('data-service_provider_name');


                    console.log("Assign to ID:", serviceName);

                    document.getElementById('subprojectName').value = SubprojectName;
                    document.getElementById('subprojectDetails').value = subprojectDetails;
                    document.getElementById('estimateHours').value = subEPT;
                    document.getElementById('sub_end_date').value = subEndDate
                    // document.getElementById('comments').value = formatComments(comments);
                    document.getElementById('sub_end_date').value = subEndDate;
                    document.getElementById('subproject_id').value = card.getAttribute('data-subproject-id');
                    document.getElementById('subproject_status').value = card.getAttribute('data-subproject-status');
                    document.getElementById('project_id').value = card.getAttribute('data-project-id');
                    document.getElementById('delete_subproject').setAttribute('data-subproject-id', card.getAttribute('data-subproject-id'));
                    document.getElementById('delete_subproject').setAttribute('data-subproject-status', card.getAttribute('data-subproject-status'));
                    document.getElementById('delete_subproject').setAttribute('data-subproject-name', card.getAttribute('data-subprojectName'));
                    document.getElementById('project_status').value = card.getAttribute('data-urgency') || '';
                    document.getElementById('service_provider_name').value = SubprojectName;


                    document.getElementById('assign_to_id').value = assignToID && assignToID !== "null" ? assignToID : "";

                    document.getElementById('Payable_amount').value = card.getAttribute('data-Payable_amount');

                    document.getElementById('urgency_value').value = card.getAttribute('data-urgency') || '';
                    console.log("Set urgency value:", document.getElementById('urgency_value').value);
                    const selectElement = document.getElementById('urgency_value');
                    selectElement.style.backgroundColor = selectElement.value === 'red' ? 'red' : selectElement.value === 'orange' ? 'orange' : selectElement.value === 'yellow' ? 'yellow' : selectElement.value === 'green' ? 'green' : '#ffffff';
                    document.getElementById('project_status').value = card.getAttribute('data-urgency') || '';


                    function formatComments(comments) {
                        if (!comments) return ""; // Handle empty comments

                        const separator = "-".repeat(53); // Creates a long line of dashes

                        return comments
                            .split("\n\n") // Split by double new lines (each entry)
                            .map(comment => {
                                let parts = comment.split(" --- "); // Split by '---'
                                if (parts.length === 3) {
                                    let authorParts = parts[2].split("-"); // Split by '-'
                                    let author = authorParts[0].trim(); // Get manager name
                                    let role = authorParts[1]?.trim() == 1 ? "PM" : authorParts[1]?.trim() == 2 ? "Engineer" : "Unknown";

                                    return `Date: ${parts[0]}\n${parts[1]}\nWritten by: ${author} (${role})\n${separator}`;
                                }
                                return comment; // Return as-is if format is unexpected
                            })
                            .join("\n"); // Use single newline to remove extra gap
                    }


                    // Get the input element by its ID
                    // Define assignToElements outside of the condition so it can be used in both cases
                    const assignToElements = document.getElementsByClassName('assign_to');
                    const serviceProviderInput = document.getElementById('service_provider_name');


                    if (OutSourced === '1') {
                        // Hide the original 'assign_to' select element
                        for (let i = 0; i < assignToElements.length; i++) {
                            assignToElements[i].style.display = 'none'; // Hide the select input
                            assignToElements[i].removeAttribute('required'); // Remove 'required' when hidden
                        }
                        // Show the service provider input field
                        serviceProviderInput.style.display = 'block'; // Display the text input for service provider name
                    } else {
                        // Revert back to showing the original 'assign_to' select input and hide the service provider input
                        for (let i = 0; i < assignToElements.length; i++) {
                            assignToElements[i].style.display = 'block'; // Show the select input
                            assignToElements[i].setAttribute('required', 'required'); // Add 'required' when shown
                        }
                        serviceProviderInput.style.display = 'none'; // Hide the service provider input field
                    }



                    // Show/hide the Payable Amount field based on OutSourced value
                    const payableAmountDiv = document.querySelector('.Payable_amount'); // The Payable Amount div

                    if (OutSourced === '1') {
                        // Show the Payable Amount div if the project is outsourced
                        payableAmountDiv.style.display = 'block';
                    } else {
                        // Hide the Payable Amount div if the project is not outsourced
                        payableAmountDiv.style.display = 'none';
                    }

                    // ✅ Update labels dynamically based on OutSourced value
                    if (OutSourced === '1') {
                        // Change label for 'Assign to' to 'Service Provider Name'

                        document.getElementById('service_provider_label').style.display = "none"; // Updated label
                        document.querySelector('label[for="assign_to').setAttribute('name', 'assign_to'); // Change the name attribute 
                        document.querySelector('label[for="subprojectName"]').textContent = "Service Name"; // Change other labels too
                        document.querySelector('label[for="subprojectDetails"]').textContent = "Service Details";

                        // Hide the 'Assign to' select element and show the service provider input
                        document.getElementById('service_provider_name').style.display = 'none';
                        document.getElementById('subprojectName').style.display = 'none';
                        document.getElementById('assign_to').style.display = 'none';
                        document.getElementById('service_provider_name').style.display = 'block'; // Show service provider name field
                    } else {
                        // Revert to normal label for 'Assign to'
                        document.getElementById('service_provider_label').style.display = "block"; // Updated label
                        document.getElementById('service_provider_label').textContent = "Engineer:"; // Reset label
                        document.getElementById('subprojectName').style.display = 'block';
                        document.querySelector('label[for="subprojectName"]').textContent = "Subproject Name"; // Reset other labels
                        document.querySelector('label[for="subprojectDetails"]').textContent = "Subproject Details:";

                        // Show the 'Assign to' select element and hide the service provider input
                        document.getElementById('assign_to').style.display = 'block';
                        document.getElementById('service_provider_name').style.display = 'none'; // Hide service provider input
                    }




                    // ✅ Check the correct radio button for outsourcing
                    document.getElementById('outsourced-yes').checked = OutSourced == 1;
                    document.getElementById('outsourced-no').checked = OutSourced != 1;

                    // ✅ Show modal
                    new bootstrap.Modal(document.getElementById('subprojectModal')).show();
                }
            });
        </script>

        <!-- Modal Template -->
        <div class="modal fade" id="addSubprojectModal" tabindex="-1" role="dialog" aria-labelledby="addSubprojectModal"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Subprojects</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="d-flex justify-content-start mt-2 ml-3">
                        <h6 class="">Project <span class="font-weight-bold" id="modalProjectId"
                                style="color:darkcyan"></span>
                        </h6>
                    </div>
                    <form action="" method="POST">
                        <div class="modal-body">
                            <input type="" hidden name="project_id">
                            <div class="mb-3 text-left">
                                <label for="num_subprojects">Number of Subprojects</label>
                                <input type="number" class="form-control" id="num_subprojects" name="num_subprojects"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="category" id="internal" value="1"
                                        required>
                                    <label class="btn btn-outline-primary w-50" for="internal">Internal Project</label>
                                    &nbsp;&nbsp;
                                    <input type="radio" class="btn-check" name="category" id="outsource" value="0"
                                        required>
                                    <label class="btn btn-outline-primary w-50" for="outsource">Outsource
                                        Project</label>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary bg-gradient"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success bg-gradient">Create Subprojects</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            $('#addSubprojectModal').on('shown.bs.modal', function (event) {
                var button = $(event.relatedTarget);

                // Display revision ID if available, otherwise show project ID
                var displayId = button.data('revision-id') ? button.data('revision-id') : button.data('project-id');

                // Always use project ID for form submission
                var projectId = button.data('project-id');

                // Update modal title with revision ID or project ID for display
                $('#modalProjectId').text(displayId);

                // Set project ID in hidden input field (ensuring correct submission)
                $('input[name="project_id"]').val(projectId);
            });






            document.addEventListener("DOMContentLoaded", () => {

                let popupOverlay = document.getElementById("popup-overlay");

                let searchContainer = document.getElementById("project-search");
                let paginationContainer = document.getElementById("pagination-container-fixed");
                popupOverlay.style.display = "none"
                searchContainer.style.display = "none"

                paginationContainer.style.display = "none";











                document.getElementById("enter-search-keyword-btn").addEventListener("click", () => {
                    searchContainer.style.display = "block"
                    popupOverlay.style.display = "block";


                })


                document.getElementById("search-close-btn").addEventListener("click", () => {
                    searchContainer.style.display = "none"


                })

                document.getElementById("pagination-btn").addEventListener("click", () => {
                    paginationContainer.style.display = "block";
                    popupOverlay.style.display = "block";
                })

                document.getElementById("pagination-close-btn").addEventListener("click", () => {
                    paginationContainer.style.display = "none"
                })


                popupOverlay.addEventListener("click", () => {
                    searchContainer.style.display = "none";
                    paginationContainer.style.display = "none"
                    popupOverlay.style.display = "none";



                })



            })
        </script>


        <div class="modal fade" id="disabledFeature" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Feature disabled</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Creating new project from rae system is currently disabled if you need to create new project use
                        count (finance app) "quick project" .
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>





        <!-- Outsourced project  -->


        <!-- Outsourced Payable Status Modal -->


        <!-- Outsourced Payable Status Modal (View Only) -->
        <!-- Outsourced Payable Status Modal (View Only) -->
        <!-- Outsourced Payable Status Modal -->
        <div class="modal fade" id="outsourcedPayableModal" tabindex="-1" role="dialog"
            aria-labelledby="outsourcedPayableModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="outsourcedPayableModalLabel">Outsourced Projects Payment Status</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <input type="search" class="form-control" id="globalSearch"
                                    placeholder="Search anything..." onkeyup="searchTable()">
                            </div>
                            <div class="col-md-6">
                                <label for="serviceProviderFilter">Service Provider:</label>
                                <select class="form-control" id="serviceProviderFilter">
                                    <option value="">All Service Providers</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="serviceFilter">Service:</label>
                                <select class="form-control" id="serviceFilter">
                                    <option value="">All Services</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="outsourcedPayableTable" class="display">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Project ID</th>
                                        <th>Service Provider</th>
                                        <th>Services</th>
                                        <th>Amount</th>
                                        <th>Payment Status</th>
                                        <th>Invoice #</th>
                                        <th>Invoice Date</th>
                                        <th>Received Date</th>
                                        <th>Booked Date</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="text-center">Loading data...</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                let dataTable;
                let providers = new Set();
                let services = new Set();

                $('#outsourcedPayableModal').on('shown.bs.modal', function () {
                    loadOutsourcedPayableData();
                });

                function searchTable() {
                    const searchText = document.getElementById('globalSearch').value.toLowerCase();
                    const table = document.getElementById('outsourcedPayableTable');
                    const rows = table.getElementsByTagName('tr');

                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        const cells = row.getElementsByTagName('td');
                        let found = false;

                        for (let j = 0; j < cells.length; j++) {
                            const cellText = cells[j].textContent || cells[j].innerText;
                            if (cellText.toLowerCase().indexOf(searchText) > -1) {
                                found = true;
                                break;
                            }
                        }

                        row.style.display = found ? '' : 'none';
                    }
                }

                $('#globalSearch').on('keyup', function () {
                    searchTable();
                });

                function populateFilters(data) {
                    const providerSelect = $('#serviceProviderFilter');
                    const serviceSelect = $('#serviceFilter');

                    providers.clear();
                    services.clear();

                    data.forEach(project => {
                        if (project.company_name) providers.add(project.company_name);
                        if (project.service_name) {
                            project.service_name.split(',').forEach(service => {
                                services.add(service.trim());
                            });
                        }
                    });

                    providerSelect.html('<option value="">All Service Providers</option>');
                    Array.from(providers).sort().forEach(provider => {
                        providerSelect.append(`<option value="${provider}">${provider}</option>`);
                    });

                    serviceSelect.html('<option value="">All Services</option>');
                    Array.from(services).sort().forEach(service => {
                        serviceSelect.append(`<option value="${service}">${service}</option>`);
                    });
                }

                $('#serviceProviderFilter, #serviceFilter').on('change', function () {
                    const providerFilter = $('#serviceProviderFilter').val().toLowerCase();
                    const serviceFilter = $('#serviceFilter').val().toLowerCase();
                    const table = document.getElementById('outsourcedPayableTable');
                    const rows = table.getElementsByTagName('tr');

                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        const providerCell = row.cells[1];
                        const serviceCell = row.cells[2];

                        const providerMatch = !providerFilter || providerCell.textContent.toLowerCase().includes(providerFilter);
                        const serviceMatch = !serviceFilter || serviceCell.textContent.toLowerCase().includes(serviceFilter);

                        row.style.display = (providerMatch && serviceMatch) ? '' : 'none';
                    }
                });

                function loadOutsourcedPayableData() {
                    document.querySelector('#outsourcedPayableTable tbody').innerHTML =
                        '<tr><td colspan="10" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>';

                    fetch('./payable_status_api.php')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                throw new Error(data.error);
                            }

                            const tableBody = document.querySelector('#outsourcedPayableTable tbody');
                            tableBody.innerHTML = '';

                            if (data.length === 0) {
                                tableBody.innerHTML = '<tr><td colspan="10" class="text-center">No outsourced projects found</td></tr>';
                                return;
                            }

                            populateFilters(data);

                            data.forEach(project => {
                                const row = document.createElement('tr');
                                const formattedAmount = project.amount ? parseFloat(project.amount).toFixed(2) : '$0.00';
                                const services = project.service_name ? project.service_name.split(',').map(s => s.trim()).join('<br>') : 'N/A';

                                row.innerHTML = `
                                    <td>${project.display_id}</td>
                                    <td>${project.company_name || 'N/A'}</td>
                                    <td>${services}</td>
                                    <td>${formattedAmount}</td>
                                    <td>
                                        <span class="badge bg-${project.payment_status_color}">
                                            ${project.payment_status_formatted}
                                        </span>
                                    </td>
                                    <td>${project.invoice_no || 'N/A'}</td>
                                    <td>${project.invoice_date_formatted || 'N/A'}</td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm received-date" 
                                            value="${project.received_date || ''}"
                                            data-record-id="${project.project_id}"
                                            data-table="${project.source_table}"
                                            onchange="updateDate(this, 'received_date')">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm booked-date"
                                            value="${project.booked_date || ''}"
                                            data-record-id="${project.project_id}"
                                            data-table="${project.source_table}"
                                            onchange="updateDate(this, 'booked_date')">
                                    </td>
                                    <td>${project.comments || 'N/A'}</td>
                                `;
                                tableBody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching outsourced payable data:', error);
                            document.querySelector('#outsourcedPayableTable tbody').innerHTML =
                                '<tr><td colspan="10" class="text-center text-danger">Error loading data: ' + error.message + '</td></tr>';
                        });
                }

                window.updateDate = function (input, dateType) {
                    const formData = new FormData();
                    formData.append('record_id', input.dataset.recordId);
                    formData.append('table', input.dataset.table);
                    formData.append(dateType, input.value);

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait while we update the date',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: './update_invoice_dates.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Date updated successfully',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to update date',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.close();
                            let errorMessage = 'An unexpected error occurred';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                console.error('Error parsing error response:', e);
                            }
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        </script>

        <?php include '../include/footer.php'; ?>
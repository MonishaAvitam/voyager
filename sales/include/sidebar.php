<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}

include 'topbar_obi.php';
// include 'include/login_header.php';



// check admin
$user_role = $_SESSION['user_role'];


$page_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);





?>



<!-- Tippy.js CSS -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css">

<!-- Tippy.js JS -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>


<style>
    li {
        list-style-type: none;
    }

    .sidebar {
        background: #133E87;
        width: 3rem;
    }

    .customSearchInput {
        width: 20rem;
        height: 2.8rem;

        padding: 0.5rem;
        outline: none;
        border: 1px solid lightgray;
        background: rgb(228, 228, 228);
        border-radius: 0 8px 8px 0;

    }


    .customSearchInput:hover {
        outline: none;
        border: 5px solid lightgray;
    }


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

    .fixed-pagination {
        /* position: fixed; */
        bottom: 0;
        width: 100%;
        z-index: 1000;
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
        z-index: 1000;
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
        z-index: 1000;
        height: 8.7rem;
        width: 18rem;
        background: white;
        padding-top: 1.5rem;
    }


    .tippy-box[data-theme~='custom-tooltip'] {

        /* background-color: black;
        color: white; */
        border-radius: 50px;
        padding: 10px 10px;
        border: 1px solid white;
        background-color: #133e87;
        color: white;

    }


    .tippy-box[data-theme~='custom-tooltip-sidebar'] {
        background-color: black;
        color: white;

        padding: 10px 10px;


    }
</style>


<div id="popup-overlay" style="position:fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1">
</div>


<div id="project-search">
    <div class="shadow-lg search-container-fixed">
        <div style="display: flex; justify-content: center;">
            <div class="d-flex">
                <div class="search-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="white" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>

                <input type="text" class="customSearchInput form-control"
                    placeholder="Search by Sales ID, Project Name, or Table Name" id="sales_search" />
            </div>
        </div>

        <!-- Search results container -->
        <div id="search-results" class="mt-2 p-3 bg-white rounded z-1 shadow-sm"
            style="max-height: 300px; overflow-y: auto; z-index: 2;cursor: pointer;"></div>

        <!-- Close button, initially visible -->
        <div class="d-flex justify-content-end" style="margin-top: -23px; margin-right: 20px;">
            <button id="search-close-btn" class="btn btn-secondary" style="display: block;">Close</button>
        </div>
    </div>
</div>

<script>
    document.getElementById('sales_search').addEventListener('input', function () {
        let query = this.value.trim();
        let closeButton = document.getElementById('search-close-btn');
        let resultsContainer = document.getElementById('search-results');

        // Show or hide the close button based on the input value
        if (query.length > 0) {
            closeButton.style.display = 'none'; // Hide the close button when there's input
        } else {
            closeButton.style.display = 'block'; // Show the close button when there's no input
            resultsContainer.innerHTML = ''; // Clear results when input is empty
        }

        // Fetch search results if query exists
        if (query.length === 0) {
            return;
        }

        // Send the query to the backend to search by Sales ID, Project Name, or Table Name
        fetch('sales_search_api.php?query=' + encodeURIComponent(query))
            .then(response => {
                if (!response.ok) throw new Error('File not found or server error');
                return response.json();
            })
            .then(data => {
                resultsContainer.innerHTML = ''; // Clear previous results

                if (data.length === 0) {
                    resultsContainer.innerHTML = '<p class="text-center text-muted">No results found.</p>';
                    return;
                }

                data.forEach(row => {
                    let div = document.createElement('div');
                    div.className = 'list-group-item border-0 py-2 px-3 mb-2 shadow-sm rounded';

                    // Map the table source to a user-friendly label
                    const sourceMap = {
                        'potential_project': 'Project Enquiry',
                        'potential_project_sent_quotation': 'Sent Quotation',
                        'accepted_quotations': 'Accepted Quotation',
                        'cancelled_quotations': 'Cancelled Quotation'
                    };

                    let sourceLabel = sourceMap[row.source] || row.source; // fallback to actual table name if unknown

                    // Conditional display of sr_num for "Project Enquiry"
                    let srNumHtml = row.source === 'potential_project' ? `<div><strong>Quotation Number: ${row.sr_num}</strong></div>` : '';

                    div.innerHTML = ` 
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block">${row.source === 'potential_project' ? row.sr_num : row.sales_id} <span class="badge bg-danger">${row.revised_qt ? row.revised_qt : ""}</span></strong> <!-- Display sr_num for Project Enquiry, else sales_id -->
                            <small class="text-muted">${row.client_name}</small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">${sourceLabel}</small>
                            <div><strong>${row.project_name}</strong></div>
                            ${srNumHtml} <!-- Display the sr_num here if the source is 'potential_project' -->
                        </div>
                    </div>
                `;

                    // Add a click event to the div to navigate to the appropriate page
                    div.addEventListener('click', function () {
                        // Define the mapping from table source to URL
                        const pageMap = {
                            'potential_project': 'project_enquiry.php?client_name=' + encodeURIComponent(row.company_name),
                            'potential_project_sent_quotation': 'sent_quotations.php?sales_id=' + encodeURIComponent(row.sales_id),
                            'accepted_quotations': 'converted_projects.php?sales_id=' + encodeURIComponent(row.sales_id),
                            'cancelled_quotations': 'cancelled_quotations.php?sales_id=' + encodeURIComponent(row.sales_id)
                        };

                        let pageUrl = pageMap[row.source] || '#'; // Default to '#' if source is not found in the map

                        // Redirect to the corresponding page with the appropriate parameter
                        window.location.href = pageUrl;
                    });

                    resultsContainer.appendChild(div);
                });

            })
            .catch(error => {
                console.error('Error:', error);
                resultsContainer.innerHTML = '<p style="color: red;" class="text-center">Search error. Check console.</p>';
            });
    });

    // Close button functionality
    document.getElementById('search-close-btn').addEventListener('click', function () {
        let inputField = document.getElementById('sales_search');
        inputField.value = ''; // Clear the input field
        document.getElementById('search-results').innerHTML = ''; // Clear the results
        document.getElementById('search-close-btn').style.display = 'block'; // Ensure close button remains visible
    });

</script>





<div id="pagination-container-fixed">
    <div class="shadow-lg pagination-container-custom">
        <div style="display: flex; justify-content: center; ">
            <div class="d-flex">

                <input type="number" id="customPageInput" min="1" value="20">
                <p class="mb-1 ml-2 mt-3">entries per page</p>

            </div>

        </div>
        <div class="d-flex justify-content-end mt-4 mr-4"> <button id="pagination-close-btn"
                class="btn btn-secondary btn-sm"> Close</button></div>


    </div>

</div>

<div id="wrapper">

    <!-- Sidebar -->

    <ul class="   accordion viewport-sidebar  " id="accordionSidebar"
        style="width: 4.5rem; position: fixed; height: 100vh; top: 4rem; left: 0; padding: 0; display: flex; justify-content: center; z-index: 999;">

        <div class="mx-auto"
            style="width: 2rem; display: flex; flex-direction: column; justify-content: space-between; height: 90%;">

            <div class="w-full ">
                <li>
                    <div style="margin-top: 1.5rem; ">
                        <a href="./sales_dashboard.php" data-tippy-content="Home" style="text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1}
                                stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </a>
                    </div>

                </li>

                <li id="enter-search-keyword-btn">
                    <div class="" style="margin-top: 1.6rem; " data-bs-toggle="tooltip" data-placement="right"
                        data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Search">





                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                            stroke="white" className="">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>

                    </div>
                </li>







                <!-- <li>
                    <div data-toggle="modal" data-target="#filterModal" class="tooltip-sidebar" data-bs-toggle="tooltip"
                        data-placement="right" data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Filter"
                        style="margin-top: 2rem;"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                    </div>
                </li> -->














                <li id="project-view">
                    <a href="add_project_record.php?showModal=true" id="plus-btn">
                        <div data-bs-toggle="tooltip" data-toggle="modal" data-target="" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Add Potential Project"
                            class="w-full " style="margin-top: 1.5rem;"><svg xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                    </a>
                </li>

                <li id="client-view">
                    <a href="add_client_record.php?showModal=true" id="plus-btn">
                        <div data-bs-toggle="tooltip" data-toggle="modal" data-target="" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Add Potential Client"
                            class="w-full " style="margin-top: 1.5rem;"><svg xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                    </a>
                </li>


                <li id="project-view">
                    <a href="./potential_customer.php">
                        <div data-bs-toggle="tooltip" data-toggle="modal" data-placement="right"
                            id="switch-to-clients-btn" data-bs-custom-class="custom-tooltip-sidebar"
                            data-tippy-content="Switch to Potential Clients" class="w-full "
                            style="margin-top: 1.5rem;">
                            <!-- <svg viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4 4V9H4.58152M19.9381 11C19.446 7.05369 16.0796 4 12 4C8.64262 4 5.76829 6.06817 4.58152 9M4.58152 9H9M20 20V15H19.4185M19.4185 15C18.2317 17.9318 15.3574 20 12 20C7.92038 20 4.55399 16.9463 4.06189 13M19.4185 15H15"
                                    stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                            </svg> -->
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18 9V12M18 12V15M18 12H21M18 12H15M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7ZM3 20C3 16.6863 5.68629 14 9 14C12.3137 14 15 16.6863 15 20V21H3V20Z"
                                    stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </div>
                    </a>
                </li>

                <li id="client-view">
                    <a href="./sales_dashboard.php">
                        <div data-bs-toggle="tooltip" data-toggle="modal" data-placement="right"
                            id="switch-to-projects-btn" data-bs-custom-class="custom-tooltip-sidebar"
                            data-tippy-content="Switch to Potential Projects" class="w-full "
                            style="margin-top: 1.5rem;"> <svg viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4 4V9H4.58152M19.9381 11C19.446 7.05369 16.0796 4 12 4C8.64262 4 5.76829 6.06817 4.58152 9M4.58152 9H9M20 20V15H19.4185M19.4185 15C18.2317 17.9318 15.3574 20 12 20C7.92038 20 4.55399 16.9463 4.06189 13M19.4185 15H15"
                                    stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </a>
                </li>



                <!-- <li>
        <div data-toggle="modal" data-target="#indicator-modal" class="w-full " style="margin-top: 1.5rem;"
            data-bs-toggle="tooltip" data-placement="right" data-bs-custom-class="custom-tooltip-sidebar"
            data-tippy-content="Help" data-bs-toggle="modal" data-bs-target="#indicator-modal"><svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                stroke="white" className="size-6">
                <path strokeLinecap="round" strokeLinejoin="round"
                    d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
            </svg>
        </div>
    </li> -->


                <!-- <li>
        <div id="pagination-btn" data-bs-toggle="tooltip" data-placement="right"
            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Entries per Page"
            class="w-full " style="margin-top: 1.5rem;"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                <path strokeLinecap="round" strokeLinejoin="round"
                    d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
            </svg>

        </div>
    </li> -->



                <li id="client-view">
                    <a href="./contacts.php">
                        <div data-toggle="modal" class="tooltip-sidebar" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content=" All Clients"
                            style="margin-top: 2rem;"> <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                    </a>
                </li>



                <li id="client-view">
                    <a href="./potential_customer.php">
                        <div data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="My Potential Clients"
                            class="w-full " style="margin-top: 1.5rem;"> <svg viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18 9V12M18 12V15M18 12H21M18 12H15M13 7C13 9.20914 11.2091 11 9 11C6.79086 11 5 9.20914 5 7C5 4.79086 6.79086 3 9 3C11.2091 3 13 4.79086 13 7ZM3 20C3 16.6863 5.68629 14 9 14C12.3137 14 15 16.6863 15 20V21H3V20Z"
                                    stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </div>
                    </a>
                </li>


                <?php
                // Assume $conn is your active MySQLi connection
                // And $user_id is from the session: $_SESSION['admin_id']
                
                // Prepare the query
                $stmt = $conn->prepare("SELECT user_id, obiAdmin FROM tbl_admin WHERE user_id = ?");
                $stmt->bind_param("i", $user_id); // "i" = integer
                $stmt->execute();

                // Get result
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $userId = $row['user_id'];
                        $obiAdmin = $row['obiAdmin'];
                    }
                } else {
                    echo "<script>console.log('No admin found.');</script>";
                }

                // Close statement
                $stmt->close();
                ?>
                <?php if (isset($obiAdmin) && $obiAdmin == 1): ?>

                    <li id="client-view">
                        <a href="./select_office.php">
                            <div data-toggle="modal" class="tooltip-sidebar" data-bs-toggle="tooltip" data-placement="right"
                                data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content=" All Potential Clients"
                                style="margin-top: 2rem;">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="white" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>

                            </div>
                        </a>
                    </li>
                    <li id="client-view">
                        <a href="./sales_archieve.php">
                            <div data-toggle="modal" class="tooltip-sidebar" data-bs-toggle="tooltip" data-placement="right"
                                data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Recycle Bin"
                                style="margin-top: 2rem;">

                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="white">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                </svg>

                            </div>
                        </a>
                    </li>

                <?php endif; ?>


                <!-- ******* -->








                <li id="project-view">
                    <a href="./project_enquiry.php">
                        <div data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Projects Enquiry"
                            class="w-full " style="margin-top: 3.5rem;"> <img alt="number-1"
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTyYRgOpU6mhS6BKgfloaB1I4-yV360-Uorog&s"
                                style="width: 1.4rem; height: 1.4rem; border-radius: 50%;" />




                        </div>
                    </a>
                </li>

                <div id="project-view">
                    <div class="text-light ml-2 " style="height: 4px; line-height: 0.05; margin-top: 0.2rem;">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->


                </div>


                <li id="project-view">
                    <a href="./sent_quotations.php" style="line-height: 1">
                        <div data-toggle="modal" class="tooltip-sidebar" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Sent Quotations"
                            style="margin-top: 0.4rem; ">
                            <img alt="number-2"
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRUa-eUu80NdbgSetVgrZqfmNak5lCE20E1vQ&s"
                                style="width: 1.4rem; height: 1.4rem; border-radius: 50%;" />






                        </div>
                    </a>
                </li>

                <div id="project-view">
                    <div class="text-light ml-2 " style="height: 4px; line-height: 0.05; margin-top: 0.2rem;">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->

                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->

                </div>



                <li id="project-view">
                    <a href="./cancelled_quotations.php">
                        <div data-toggle="modal" class="tooltip-sidebar" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Cancelled Quotations"
                            style="margin-top: 0.4rem ;">
                            <img alt="number-3"
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQJrSLsb3RJxX6CXwNid9mYRCAWA0LhHbsK2A&s"
                                style="width: 1.4rem; height: 1.4rem; border-radius: 50%;" />
                        </div>
                    </a>
                </li>

                <div id="project-view">
                    <div class="text-light ml-2 " style="height: 4px; line-height: 0.05; margin-top: 0.2rem;">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div>
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->
                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->

                    <!-- <div class="text-light ml-2" style="height: 4px; line-height: 0.05">.</div> -->

                </div>



                <li id="project-view">
                    <a href="./converted_projects.php">
                        <div data-toggle="modal" class="tooltip-sidebar" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Accepted Quotations"
                            style="margin-top: 0.4rem ;">
                            <img alt="number-4"
                                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8YfcWJ8GOJAxbTUeNugH2jlPwl-4OxQ-0GFd483vnPUj1hfyc_hyL3ISgjLh3V87V-YE&usqp=CAU"
                                style="width: 1.4rem; height: 1.4rem; border-radius: 50%;" />
                        </div>
                    </a>
                </li>




            </div>









            <div>



                <li>
                    <div class="w-full"><a href="../allApps.php" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Home">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5}
                                stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>


                        </a>
                    </div>
                </li>
                <!-- <li>
                    <div class="w-full " style="margin-top: 1.2rem;"><a href="./archives.php" data-bs-toggle="tooltip"
                            data-placement="right" data-bs-custom-class="custom-tooltip-sidebar"
                            data-tippy-content="Archives"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" strokeWidth={1.5} stroke="white" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>

                        </a>
                    </div>
                </li> -->


                <li>
                    <div class="w-full " style="margin-top: 1.2rem;"><a href="#" data-toggle="modal"
                            data-target="#logoutModal" data-bs-toggle="tooltip" data-placement="right"
                            data-bs-custom-class="custom-tooltip-sidebar" data-tippy-content="Log out">

                            <!-- <i class="fa-solid fa-arrow-right-from-bracket text-light ml-2" style="font-size: 1.5rem;"></i> -->
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17 16L21 12M21 12L17 8M21 12L7 12M13 16V17C13 18.6569 11.6569 20 10 20H6C4.34315 20 3 18.6569 3 17V7C3 5.34315 4.34315 4 6 4H10C11.6569 4 13 5.34315 13 7V8"
                                    stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </a>
                    </div>
                </li>
                <li>



            </div>




        </div>






    </ul>



    <div id="content-wrapper" class="d-flex flex-column" style="margin-top: 5rem; margin-left: 5rem;">


        <script>
            tippy('[data-bs-toggle="tooltip"]', {
                allowHTML: true,
                placement: 'bottom',
                theme: 'custom-tooltip',
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

                // document.getElementById("pagination-btn").addEventListener("click", () => {
                //     paginationContainer.style.display = "block";
                //     popupOverlay.style.display = "block";
                // })

                // document.getElementById("pagination-close-btn").addEventListener("click", () => {
                //     paginationContainer.style.display = "none"
                // })


                popupOverlay.addEventListener("click", () => {
                    searchContainer.style.display = "none";
                    paginationContainer.style.display = "none"
                    popupOverlay.style.display = "none";



                })



            })
        </script>


        <?php

        if ($page_name === "potential_customer" || $page_name === "contacts" || $page_name === "add_client_record" || $page_name === "all_potential_customer" || $page_name === "select_office" || $page_name === "sales_archieve") {
            echo "<script> 
                document.addEventListener('DOMContentLoaded', () => {
                  let projectOptions = document.querySelectorAll('#project-view')
    let clientOptions = document.querySelectorAll('#client-view')

         projectOptions.forEach((options) => {
            options.style.display = 'none'
        })
        clientOptions.forEach((item) => {
        item.style.display = 'block'
    })
         })
    </script>";
        }


        if ($page_name === "project_enquiry" || $page_name === "sent_quotations" || $page_name === "sales_dashboard" || $page_name === "cancelled_quotations" || $page_name === "converted_projects" || $page_name === "add_project_record") {
            echo "<script> 
                                document.addEventListener('DOMContentLoaded', () => {
                                 let projectOptions = document.querySelectorAll('#project-view')
    let clientOptions = document.querySelectorAll('#client-view')

         projectOptions.forEach((option) => {
            option.style.display = 'block'
        })
        clientOptions.forEach((items) => {
        items.style.display = 'none'
    })
         })
    </script>";
        }



        ?>
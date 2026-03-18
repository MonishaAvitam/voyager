<?php



// Suppress warnings about undefined variables
error_reporting(E_ERROR | E_PARSE);

// Set session variables
$_SESSION['status_success'] = $msg_success;
$_SESSION['status_error'] = $msg_error;
$_SESSION['status_info'] = $msg_info;
$_SESSION['status_warning'] = $msg_warning;
?>


<style>
    /* Scroll to top button styles */
#scrollTopBtn {
    position: fixed;       /* Fix at bottom-right */
    bottom: 30px;          /* Distance from bottom */
    right: 30px;           /* Distance from right */
    width: 45px;           /* Button size */
    height: 45px;
    background-color: #070A19;  /* Button background */
    color: white;          /* Icon color */
    text-align: center;
    line-height: 45px;     /* Center icon vertically */
    border-radius: 50%;    /* Circular button */
    z-index: 9999;         /* On top of everything */
    display: none;         /* Hidden initially */
    transition: background-color 0.3s ease, opacity 0.3s ease;
}

#scrollTopBtn:hover {
    background-color: #444;  /* Hover effect */
    color: #fff;
    text-decoration: none;
}

/* Show button when scrolling down */
.show-scroll {
    display: block !important;
}

</style>

<!-- Footer -->
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<!-- Scroll to Top Button-->
<a href="#page-top" class="scroll-to-top rounded" id="scrollTopBtn">
    <i class="fas fa-angle-up"></i>
</a>



<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger" href="?logout=logout">Logout</a>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->


<!-- Bootstrap core JavaScript-->
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<!-- for Tooltip -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>




<!-- jQuery -->
<!-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script> -->
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- DataTables Core JS -->
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.js"></script>
<!-- DataTables Bootstrap 4 JS -->
<script src="https://cdn.datatables.net/2.0.0/js/dataTables.bootstrap4.js"></script>

<script>
    new DataTable('#meeting_info');
    $(document).ready(function() {
        $('#performance').DataTable({
            "pageLength": 5, // Display 1 row initially
            "stateSave": true,
            "lengthMenu": [5, 10, 20, 50, 70], // Menu options for number of rows per page
            "searching": {
                "searchPlaceholder": "Type search here" // Placeholder for search input
            },
            "pagingType": "simple_numbers" // Show only the 'Next' button for pagination
        });
    });
</script>

<script>
    // Initialize DataTable
    new DataTable('#dataTable', {
        lengthMenu: [10, 25, 50, 100, 200], // Set page length options
        pageLength: 50, // Set default page length to 50
        "stateSave": true,
        layout: {
            topEnd: {
                search: {
                    placeholder: 'Type search here'
                }
            },
            bottomEnd: {
                paging: {
                    numbers: 3
                }
            }
        },
        order: [
            [0, 'desc']
        ] // Order by the first column in descending order
    });
    $(document).ready(function() {
        // Example data array (replace with your actual data)

        $('#senttorae').DataTable({
            lengthMenu: [10, 25, 50, 100, 200], // Set page length options
            pageLength: 5, // Set default page length to 5
            "stateSave": true,
            columns: [{
                    title: "Enquiry ID",
                    data: "enquiryId"
                },
                {
                    title: "Customer Name",
                    data: "customerName"
                },
                {
                    title: "Last Updated",
                    data: "lastUpdated"
                },
                {
                    title: "Team",
                    data: "team"
                },
                {
                    title: "Actions",
                    data: "actions"
                }
            ],
            // Example layout configuration
            layout: {
                topEnd: {
                    search: {
                        placeholder: 'Type search here'
                    }
                },
                bottomEnd: {
                    paging: {
                        numbers: 3
                    }
                }
            }
        });
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>



<!-- Footer -->
<footer class=" py-3 mt-auto border-0">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <!-- <div class="mb-2 mb-md-0">
            &copy; <?= date('Y') ?> <a href="https://vathix.com" class=" text-decoration-none">vathix.com</a>. All rights reserved.
        </div> -->
    </div>
</footer>


<!-- bootstrap MD  -->


</body>

</html>
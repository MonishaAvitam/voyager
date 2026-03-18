<!-- Page Wrapper -->
<!-- Button trigger modal -->





<?php



// Suppress warnings about undefined variables
error_reporting(E_ERROR | E_PARSE);

// Set session variables
$_SESSION['status_success'] = $msg_success;
$_SESSION['status_error'] = $msg_error;
$_SESSION['status_info'] = $msg_info;
$_SESSION['status_warning'] = $msg_warning;
?>


<!-- Footer -->
<footer class="sticky-footer ">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Composite Structures Australia Engineering 2023</span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
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
                <a class="btn btn-primary" href="?logout=logout">Logout</a>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<!-- DataTables CSS -->

<!-- DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>   


<!-- Bootstrap core JavaScript-->
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<!-- Core plugin JavaScript-->
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../js/sb-admin-2.min.js"></script>

<!-- Page level plugins -->
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script src="../js/demo/datatables-demo.js"></script>




<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if (isset($_SESSION['message'])): ?>
        const message = <?php echo json_encode($_SESSION['message']); ?>;
        if (message.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message.text,
                confirmButtonText: 'OK'
            }).then(() => {
                // Optionally redirect or do something else here
                window.location.href = '<?php echo $_SERVER['HTTP_REFERER']; ?>';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message.text,
                confirmButtonText: 'OK'
            });
        }
        <?php unset($_SESSION['message']); // Clear message after displaying ?>
    <?php endif; ?>
});
</script>


<script>
// Save scroll position before the page reloads
window.addEventListener('beforeunload', function() {
    sessionStorage.setItem('scrollPosition', window.scrollY);
});

// Restore scroll position on page load
document.addEventListener("DOMContentLoaded", function() {
    const scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem('scrollPosition'); // Clear the position after using it
    }

    <?php if (isset($_SESSION['message'])): ?>
        const message = <?php echo json_encode($_SESSION['message']); ?>;
        if (message.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message.text,
                confirmButtonText: 'OK'
            }).then(() => {
                // Optionally redirect or do something else here
                window.location.href = '<?php echo $_SERVER['HTTP_REFERER']; ?>';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message.text,
                confirmButtonText: 'OK'
            });
        }
        <?php unset($_SESSION['message']); // Clear message after displaying ?>
    <?php endif; ?>
});
</script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#dataTable').DataTable();

        // Check if there is a saved search term in local storage
        var savedSearch = localStorage.getItem('dataTableSearch');
        if (savedSearch) {
            // Set the search term in the DataTable
            table.search(savedSearch).draw();
        }

        // Listen for search event
        $('#dataTable_filter input').on('input', function() {
            // Save the current search value in local storage
            localStorage.setItem('dataTableSearch', $(this).val());
        });

        // Optional: Clear search term and local storage if needed
        $('#clearSearch').on('click', function() {
            table.search('').draw(); // Clear search in DataTable
            localStorage.removeItem('dataTableSearch'); // Remove from local storage
        });
    });
</script>

<!-- bootstrap MD  -->


</body>

</html>
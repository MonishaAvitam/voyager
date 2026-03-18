<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php';
require '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}

$user_role = $_SESSION['user_role'];
include './include/sidebar.php';
?>

<div class="container-fluid m-3">

    <div class="container-fluid px-3">
        <div class="row align-items-center mb-3">
            <div class="col-md-6 col-12">
                <h4 class="h3 mb-0 text-gray-800">Accounts Receivable</h4>
            </div>
            <div class="col-md-6 col-12 mt-2 mt-md-0">
                <input type="text" id="searchInput" class="form-control w-100" placeholder="Search employee...">
            </div>
        </div>
    </div>



    <!-- Big Card Container -->
    <div class="card shadow-sm">
        <div class="card-header">
            <strong class="m-0 font-weight-bold text-primary">Employee List</strong>
        </div>

        <div class="card-body">
            <div class="row">
                <?php
                $sql = "SELECT DISTINCT `employee_id`, `fullName` FROM `csa_finance_payslip_records` ORDER BY `fullName` ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                            <div class="card text-center h-100 shadow-sm bg-light transition"
                                style="cursor: pointer; transition: transform 0.3s, box-shadow 0.3s;"
                                onclick="window.location.href='payslip_details.php?employee_id=<?php echo $row['employee_id']; ?>'">
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($row['fullName']); ?></h6>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='col-12'><p class='text-center'>No employee records found.</p></div>";
                }
                ?>
            </div>
        </div>
    </div>

</div>

<!-- Inline animation script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.card');

        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '';
            });
        });
    });
    // Search filter
    document.getElementById('searchInput').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const cards = document.querySelectorAll('.card-body .card');

        cards.forEach(card => {
            const name = card.textContent.toLowerCase();
            card.parentElement.style.display = name.includes(query) ? 'block' : 'none';
        });
    });

</script>

<?php include './include/footer.php'; ?>
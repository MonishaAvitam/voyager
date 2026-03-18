<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';
include './include/sidebar.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit();
}

// Fetch offices from database
$sql = "SELECT `id`, `office_name`, `office_code`, `created_at`, `updated_at` FROM `office` WHERE 1";
$result = $conn->query($sql);
?>
<style>
    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container mt-5">
    <h2 class="mb-4">Select Office</h2>
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php 
            $delay = 0; // animation delay counter
            while ($office = $result->fetch_assoc()): 
                // Get count of potential customers for this office
                $office_id = $office['id'];
                $count_query = "SELECT COUNT(*) AS total FROM potential_customer WHERE office_id = ?";
                $stmt_count = $conn->prepare($count_query);
                $stmt_count->bind_param("i", $office_id);
                $stmt_count->execute();
                $result_count = $stmt_count->get_result();
                $total_customers = 0;
                if ($row_count = $result_count->fetch_assoc()) {
                    $total_customers = $row_count['total'];
                }
                $stmt_count->close();

                $delay += 0.2; // increase delay for each card (0.2s gap)
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 fade-in" style="animation-delay: <?php echo $delay; ?>s;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($office['office_name']); ?></h5>
                                    <p class="card-text">Code: <?php echo htmlspecialchars($office['office_code']); ?></p>
                                </div>
                                <div class="text-end">
                                    <h1 class="mb-0"><?php echo $total_customers; ?></h1>
                                </div>
                            </div>
                            <p class="card-text"><small class="text-muted">Created:
                                    <?php echo date("d M Y", strtotime($office['created_at'])); ?>
                                </small></p>
                            <a href="all_potential_customer.php?office_id=<?php echo $office['id']; ?>" class="btn btn-primary">
                                View Leads
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">No offices found.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'include/footer.php';
?>

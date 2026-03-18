<?php
$company_name = "CSA Engineering RAE Software";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | <?= $company_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#"><?= $company_name ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="terms-of-service.php">Terms of Service</a></li>
                <li class="nav-item"><a class="nav-link" href="privacy-policy.php">Privacy Policy</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container my-5">
    <h2 class="text-center mb-4">Terms of Service</h2>
    <p>Last updated: <?php echo date("F d, Y"); ?></p>

    <h4>1. Acceptance of Terms</h4>
    <p>By accessing and using <strong><?= $company_name ?></strong>, you agree to be bound by these Terms of Service.</p>

    <h4>2. Use of Our Services</h4>
    <p>You agree to use our services legally and ethically. You must not misuse or engage in unauthorized activities.</p>

    <h4>3. User Accounts</h4>
    <p>To use certain features, you may need to create an account. You are responsible for maintaining the confidentiality of your credentials.</p>

    <h4>4. Prohibited Activities</h4>
    <ul>
        <li>Hacking, data scraping, or unauthorized access.</li>
        <li>Distributing malicious software or harmful content.</li>
        <li>Violating any applicable laws or regulations.</li>
    </ul>

    <h4>5. Intellectual Property</h4>
    <p>All content and trademarks related to <strong><?= $company_name ?></strong> are owned by us and protected by law.</p>

    <h4>6. Limitation of Liability</h4>
    <p>We are not liable for any direct or indirect damages caused by the use of our services.</p>

    <h4>7. Termination</h4>
    <p>We reserve the right to suspend or terminate user accounts if they violate our policies.</p>

    <h4>8. Changes to Terms</h4>
    <p>We may update these Terms from time to time. Continued use of our services after changes means you accept the new terms.</p>

    <h4>9. Contact Information</h4>
    <p>If you have any questions, please contact us at <a href="mailto:info@csaengineering.com">info@csaengineering.com</a>.</p>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    &copy; <?= date("Y") ?> <?= $company_name ?>. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$company_name = "CSA Engineering RAE Software";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | <?= $company_name ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="privacy-policy.php">Privacy Policy</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container my-5">
    <h2 class="text-center mb-4">Privacy Policy</h2>
    <p>Last updated: <?php echo date("F d, Y"); ?></p>

    <p>Welcome to <strong><?= $company_name ?></strong>. Your privacy is important to us. This Privacy Policy explains how we collect, use, and protect your personal information.</p>

    <h4>1. Information We Collect</h4>
    <p>We collect the following types of information:</p>
    <ul>
        <li>Personal details (such as name, email, and phone number) when you register.</li>
        <li>Usage data, such as IP address, browser type, and activity logs.</li>
        <li>Any other information you voluntarily provide through our services.</li>
    </ul>

    <h4>2. How We Use Your Information</h4>
    <p>We use the collected data for the following purposes:</p>
    <ul>
        <li>To provide, maintain, and improve our services.</li>
        <li>To send you updates, security alerts, and customer support messages.</li>
        <li>To comply with legal obligations.</li>
    </ul>

    <h4>3. Sharing Your Information</h4>
    <p>We do not sell or rent your personal data. However, we may share your information with:</p>
    <ul>
        <li>Trusted third-party service providers who assist in delivering our services.</li>
        <li>Legal authorities if required by law.</li>
    </ul>

    <h4>4. Security of Your Data</h4>
    <p>We implement strict security measures to protect your personal data. However, no online platform is 100% secure, and we encourage users to take necessary precautions.</p>

    <h4>5. Your Rights & Choices</h4>
    <p>You have the right to:</p>
    <ul>
        <li>Request access to your personal data.</li>
        <li>Request corrections or deletion of your data.</li>
        <li>Opt-out of marketing communications.</li>
    </ul>

    <h4>6. Changes to This Policy</h4>
    <p>We may update this Privacy Policy from time to time. We encourage users to review this page periodically.</p>

    <h4>7. Contact Us</h4>
    <p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:info@csaengineering.com">info@csaengineering.com</a>.</p>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    &copy; <?= date("Y") ?> <?= $company_name ?>. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

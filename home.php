<?php
$company_name = "CSA Engineering RAE Software";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= $company_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?= $company_name ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="privacy-policy.php">Privacy Policy</a></li>
                <li class="nav-item"><a class="nav-link" href="terms-of-service.php">Terms of Service</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<header class="bg-primary text-white text-center py-5">
    <div class="container">
        <h1>Welcome to <?= $company_name ?></h1>
        <p class="lead">Efficient project management & automation for engineering teams.</p>
        <a href="signup.php" class="btn btn-light btn-lg">Get Started</a>
    </div>
</header>

<!-- Features Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Key Features</h2>
    <div class="row text-center">
        <div class="col-md-4">
            <h4>Task Management</h4>
            <p>Organize and track tasks efficiently with real-time collaboration.</p>
        </div>
        <div class="col-md-4">
            <h4>Data Security</h4>
            <p>Your data is encrypted and securely stored with industry standards.</p>
        </div>
        <div class="col-md-4">
            <h4>Google Integration</h4>
            <p>Sync with Google Tasks, Calendar, and Drive for seamless workflow.</p>
        </div>
    </div>
</div>

<!-- Why We Collect Data Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Why We Collect User Data</h2>
    <p>To improve your experience, we collect limited data such as:</p>
    <ul>
        <li>User account details for login authentication.</li>
        <li>Task data for project tracking and Google integration.</li>
        <li>Usage statistics to enhance features & performance.</li>
    </ul>
    <p>We never sell or misuse your data. <a href="privacy-policy.php">Read our Privacy Policy</a>.</p>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3">
    &copy; <?= date("Y") ?> <?= $company_name ?>. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

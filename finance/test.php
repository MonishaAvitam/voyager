<?php
session_start();

// Initialize session variable if not set
if (!isset($_SESSION['countStatus'])) {
    $_SESSION['countStatus'] = '1'; // Default to '1'
}

// Toggle session variable when the toggle link is clicked
if (isset($_GET['toggle'])) {
    $_SESSION['countStatus'] = $_SESSION['countStatus'] === '2' ? '1' : '2';
}

// Get the current session status
echo $countStatus = $_SESSION['countStatus'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toggle Example</title>
    <!-- Add your preferred CSS here -->
</head>
<body>
    <nav>
        <ul>
            <li class="nav-item">
                <!-- Link to toggle the session variable -->
                <a class="nav-link" href="?toggle=1">
                    <i class="fas fa-random" style="color: white;"></i>
                    <span id="toggleText">
                        <?php echo $countStatus === '1' ? 'Switch to Receivables' : 'Switch to Payable'; ?>
                    </span>
                </a>
            </li>
        </ul>
    </nav>
</body>
</html>

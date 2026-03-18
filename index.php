<!DOCTYPE html>
<html lang="en">

<head>

<link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.4/components/logins/login-9/assets/css/login-9.css">
	

</head>


<?php
ob_start();
session_start();

require 'classes/admin_class.php';
$obj_admin = new Admin_Class();

if (isset($_GET['logout'])) {
	$obj_admin->admin_logout();
}




// auth check
if (isset($_SESSION['admin_id'])) {
	$user_id = $_SESSION['admin_id'];
	$user_name = $_SESSION['admin_name'];
	$security_key = $_SESSION['security_key'];
	if ($user_id != NULL && $security_key != NULL) {
		header('Location: allApps.php');
		exit();
	}
}

// Check if the login button is clicked
if (isset($_POST['login_btn'])) {
	// Check if the user is blocked
	if (isset($_SESSION['block_user']) && $_SESSION['block_user'] >= time()) {
		// Calculate the remaining time until the user can attempt to log in again
		$remaining_time = $_SESSION['block_user'] - time();

		// Display an error message with the remaining time
		$info = "Account is temporarily blocked. Please try again in " . gmdate("i:s", $remaining_time) . " minutes and seconds.";
	} else {
		// Check if the username and password are provided (add your validation logic here)
		if (isset($_POST['username']) && isset($_POST['admin_password'])) {
			// Verify the login credentials (add your verification logic here)
			$info = $obj_admin->admin_login_check($_POST);

			if ($info === "Login successful!") {
				// Clear the login attempt count and block status if login is successful
				unset($_SESSION['login_attempts']);
				unset($_SESSION['block_user']);
				header('Location: welcome.php');
				exit();
			} else {
				// Increment and store login attempt count
				$_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? ($_SESSION['login_attempts'] + 1) : 1;

				// Check if the maximum attempts are reached
				if ($_SESSION['login_attempts'] >= 3) {
					// Check if the user is not already blocked
					if (!isset($_SESSION['block_user'])) {
						// Set the block status and the block duration (e.g., 30 seconds)
						$_SESSION['block_user'] = time() + 30;
						$info .= " Too many unsuccessful attempts. Account is temporarily blocked. Please try again in " . gmdate("i:s", 30) . " minutes and seconds.";
					} else {
						// Display a different error message for blocked users
						$info = "Account is temporarily blocked. Please try again later.";
						// Remove all cookies
						foreach ($_COOKIE as $cookie_name => $cookie_value) {
							setcookie($cookie_name, '', time() - 3600, '/');
						}
					}
				} else {
					// Display a regular error message
					$info .= " Attempt: " . $_SESSION['login_attempts'];
				}
			}
		}
	}
}

$page_name = "Login";
?>





<body>

	

	<!-- Login 9 - Bootstrap Brain Component -->
<section class="bg-primary min-vh-100 d-flex align-items-center py-5">
  <div class="container">
    <div class="row align-items-center gx-5">

      <!-- Left Column: Title, Logo, Company Info -->
	   
      <div class="col-12 col-md-6 text-center text-md-start mb-5 mb-md-0">


        <div class="d-flex justify-content-center justify-content-md-start align-items-center gap-3 flex-wrap mt-2">
          <img src="./img/logo.png" alt="Logo" width="100" height="60" class="img-fluid">

          <!-- Company Name & Slogan -->
          <div class="text-white text-start">

            <h2 class="h3 fw-bold mb-1">Voyager Group Of Companies</h2>
            <p class="mb-0 lead">Your Partner in Marine Solutions</p>
			<p class="mb-0 lead mt-5">RAE SYSTEM </p>

          </div>
        </div>
        <div class="text-center text-md-start mt-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-grip-horizontal text-white" viewBox="0 0 16 16">
            <path d="M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
          </svg>
        </div>
      </div>

      <!-- Right Column: Login Form -->
      <div class="col-12 col-md-6">
        <div class="card rounded-4 shadow-lg border-0">
          <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold mb-3 text-center">Sign in</h3>
            <p class="text-center text-muted mb-4">Don't have an account? <a href="#!">Sign up</a></p>

            <form class="user" method="POST" id="loginForm">
              <div class="mb-3">
                <div class="form-floating">
                  <input type="email" class="form-control" name="username" placeholder="name@example.com" required pattern=".{3,}" title="Username must have at least 3 characters">
                  <label>Email</label>
                </div>
              </div>
              <div class="mb-3">
                <div class="form-floating">
                  <input type="password" class="form-control" name="admin_password" placeholder="Password" pattern=".{6,}" title="Password must have at least 6 characters" required>
                  <label>Password</label>
                </div>
              </div>
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                <label class="form-check-label text-secondary" for="remember_me">Keep me logged in</label>
              </div>
              <div class="d-grid">
                <button type="submit" name="login_btn" class="btn btn-dark btn-lg fw-bold">Log in now</button>
              </div>
            </form>

            <div class="text-center mt-4">
              <a href="#!" class="text-decoration-none text-muted small">Forgot password?</a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<style>

.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.form-control:focus {
  box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
  border-color: #0d6efd;
}
</style>



	

</body>

</html>
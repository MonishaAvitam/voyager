<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!--<meta name="description" content="">-->
	<!--<meta name="author" content="">-->
	<meta property="og:locale" content="en_US" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Login into CSA Engineering" />
	<meta property="og:image" content="https://tse1.mm.bing.net/th?id=OIP.2Lp8Q68DyIN2wld7xTA4ywHaHa&pid=Api&P=0&h=180"> <!-- URL to your image -->

	<meta property="og:description" content="CSA Engineering provides clients with value-for-money engineering services for design, verification, and certification in Gold Coast, QLD. Contact us!!" />
	<meta property="og:url" content="https://app.csaengineering.com.au/" />
	<meta property="og:site_name" content="Composite Structures Australia Engineering Solutions" />
	<meta property="article:modified_time" content="2023-10-12T00:20:43+00:00" />
	<meta name="twitter:card" content="summary_large_image" />
	<meta name="twitter:label1" content="Est. reading time" />
	<meta name="twitter:data1" content="22 minutes" />
	<title>VOYAGER</title>
	<link rel="icon" href="https://www.csaengineering.com.au/wp-content/uploads/2022/06/cropped-fav-icon-32x32.png" type="image/x-icon">

	<!-- Custom fonts for this template-->
	<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<!-- Custom styles for this template-->
	<link href="css/newTheme.css" rel="stylesheet">

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
		header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
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
				header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
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

<style>
	/* Default background styles */
	body {
		background-image: url('./img/bg-hd-1.png');
		background-repeat: no-repeat;
		background-size: contain;
		background-color: #092744;
		background-position: left bottom;


	}

	/* Media query for mobile devices with a maximum width of 767px */
	@media (max-width: 800px) {
		body {
			background-image: url('./img/mobile-bg.png');
			background-repeat: no-repeat;
			background-size: cover;
			background-color: #092744;
		}
	}

	@media(max-height:1020) {

		body {
			background-image: url('./img/bg-hd-1.png');
			background-repeat: no-repeat;
			background-size: contain;
			background-color: #092744;
			background-position: left bottom;
		}

	}

	.glass {
		/* From https://css.glass */
		background: rgba(25, 28, 32, 0.68);
		border-radius: 16px;
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(3.9px);
		-webkit-backdrop-filter: blur(3.9px);
		border: 1px solid rgba(25, 28, 32, 0.3);
	}

	.box {
		/* background-color: wheat; */
		height: 80vh;

	}
</style>



<body>

	<div class="container-fluid mx-auto my-5">

		<div class="row box justify-content-end align-items-center">

			<div class="col-md-4 ">

				<div class="card o-hidden border-0 shadow-lg my-5 glass  ">
					<div class="card-body p-0">
						<!-- Nested Row within Card Body -->
						<div class="row">
							<div class="col-lg-12">
								<div class="p-5">
									<div class="text-center">
										<h1 class="h4  mb-4" style="color: white;">CSA - RAE</h1>
									</div>
									<form class="user" method="POST" id="loginForm">
										<?php if (isset($info)) { ?>
											<h5 class="alert alert-danger"><?php echo $info; ?></h5>
										<?php } ?>
										<div class="form-group">
											<input type="text" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Username" name="username" required pattern=".{3,}" title="Username must have at least 3 characters">
										</div>
										<div class="form-group">
											<input type="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password" name="admin_password" required pattern=".{6,}" title="Password must have at least 6 characters">
										</div>
										<div class="form-group d-flex text-center justify-content-around" style="color: white;">
											<div>
												
												<div class="form-check">
													<input class="form-check-input" type="radio" name="csa_login_option" value="csa_finance_app" id="csa_finance_app">
													<label class="form-check-label" for="csa_finance_app">COUNT</label>
												</div>
											</div>
											
										</div>

										<button name="login_btn" class="btn btn-user btn-block" style="background-color:#EE4B5F; color:aliceblue">
											Login
										</button>
										<hr>
									</form>

									<script>
										document.getElementById('loginForm').addEventListener('submit', function(e) {
											const skyWalkerOption = document.getElementById('skyWalker');
											const usernameField = document.getElementById('exampleInputEmail');
											const passwordField = document.getElementById('exampleInputPassword');

											if (skyWalkerOption.checked) {
												// Change form action to the custom URL for Sky Walker
												this.action = 'http://127.0.0.1:8000/custom-login';

												// Change the name attributes for Sky Walker
												usernameField.name = 'email'; // Change username field to 'email'
												passwordField.name = 'password'; // Change password field to 'password'
											} else {
												// Reset the name attributes to their original values for other options
												usernameField.name = 'username';
												passwordField.name = 'admin_password';
											}
										});
									</script>


								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<!-- Bootstrap core JavaScript-->
	<script src="vendor/jquery/jquery.min.js"></script>
	<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- Core plugin JavaScript-->
	<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

	<!-- Custom scripts for all pages-->
	<script src="js/sb-admin-2.min.js"></script>


</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>VOYAGER</title>
  <link rel="icon" href="../img/favicon.ico" type="image/x-icon">


  <!-- Custom fonts for this template -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">




  <link href="../css/newTheme.css" rel="stylesheet">

  <!-- Include Bootstrap CSS and JS -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template -->

  <!-- Custom styles for this page -->
  <!-- <link rel="stylesheet" href="../css/custom.css"> -->
  

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>


  <!-- toster notification -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

  <!-- Real time Notification -->
  <script src="https://cdn.socket.io/4.5.0/socket.io.min.js" integrity="sha384-7EyYLQZgWBi67fBtVxw60/OWl1kjsfrPFcaU0pp0nAh+i8FD068QogUvg85Ewy1k" crossorigin="anonymous"></script>

  <!-- <script src="https://kit.fontawesome.com/26bcd7cc45.js" crossorigin="anonymous"></script> -->
  <script src="https://kit.fontawesome.com/ffd68f1a05.js" crossorigin="anonymous"></script>

</head>


<body id="page-top" class=""  >
<style>
	/* Overlay styles to cover the entire page */
	#loading-overlay {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(14, 15, 20, 0.92);
		display: flex;
		/* Show overlay by default */
		justify-content: center;
		align-items: center;
		z-index: 1050;
	}


	@-webkit-keyframes rotate-body {
		from {
			-webkit-transform: rotate(0deg);
		}

		to {
			-webkit-transform: rotate(-360deg);
		}
	}

	@-ms-keyframes rotate-body {
		from {
			-ms-transform: rotate(0deg);
		}

		to {
			-ms-transform: rotate(-360deg);
		}
	}

	@keyframes rotate-body {
		from {
			transform: rotate(0deg);
		}

		to {
			transform: rotate(-360deg);
		}
	}

	@-webkit-keyframes head-tilt {
		from {
			-webkit-transform: rotate(0deg);
		}

		25% {
			-webkit-transform: rotate(-0.8deg);
		}

		50% {
			-webkit-transform: rotate(0deg);
		}

		75% {
			-webkit-transform: rotate(0.8deg);
		}

		to {
			-webkit-transform: rotate(0deg);
		}
	}

	@-ms-keyframes head-tilt {
		from {
			-ms-transform: rotate(0deg);
		}

		25% {
			-ms-transform: rotate(-0.8deg);
		}

		50% {
			-ms-transform: rotate(0deg);
		}

		75% {
			-ms-transform: rotate(0.8deg);
		}

		to {
			-ms-transform: rotate(0deg);
		}
	}

	@keyframes head-tilt {
		from {
			transform: rotate(0deg);
		}

		25% {
			transform: rotate(-0.8deg);
		}

		50% {
			transform: rotate(0deg);
		}

		75% {
			transform: rotate(0.8deg);
		}

		to {
			transform: rotate(0deg);
		}
	}

	@-webkit-keyframes move-shadow {
		from {
			-webkit-transform: scaleX(0.97);
		}

		to {
			-webkit-transform: scaleX(1);
		}
	}

	@-ms-keyframes move-shadow {
		from {
			-ms-transform: scaleX(0.97);
		}

		to {
			-ms-transform: scaleX(1);
		}
	}

	@keyframes move-shadow {
		from {
			transform: scaleX(0.97);
		}

		to {
			transform: scaleX(1);
		}
	}

	body {
		background-color: #7c9a9c;
	}

	.bb-8 {
		position: absolute;
		top: 50%;
		left: 50%;
		width: 240px;
		height: 326px;
		margin-bottom: 50px;
		-webkit-transform: translate(-50%, -50%);
		-ms-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
	}

	.bb-8__head {
		z-index: 4;
		position: absolute;
		width: 140px;
		height: 93px;
		-webkit-animation: head-tilt 0.3s 0.5s linear infinite;
		-ms-animation: head-tilt 0.3s 0.5s linear infinite;
		animation: head-tilt 0.3s 0.5s linear infinite;
	}

	.bb-8__head__antenna {
		width: 4px;
		height: 20px;
		background-color: #d3d3d3;
		position: absolute;
		top: -18px;
		left: 120px;
		border-radius: 3px;
	}

	.bb-8__head__antenna--longer {
		width: 4px;
		height: 30px;
		background-color: #d3d3d3;
		position: absolute;
		top: -25px;
		left: 130px;
		border-radius: 3px;
	}

	.bb-8__head__antenna--longer:before {
		content: "";
		width: 100%;
		height: 40%;
		background-color: #bebab5;
		position: absolute;
		bottom: 0;
	}

	.bb-8__head__top {
		overflow: hidden;
		position: absolute;
		width: 140px;
		height: 85px;
		background-color: #fff3e7;
		border-radius: 200px 200px 0 0;
		position: absolute;
		left: 50px;
	}

	.bb-8__head__top__bar--gray {
		width: 100%;
		height: 10px;
		background-color: #d3d3d3;
		position: absolute;
		top: 10px;
	}

	.bb-8__head__top__bar--red {
		width: 100%;
		height: 6px;
		background-color: #ff9649;
		position: absolute;
		top: 30px;
	}

	.bb-8__head__top__bar--red--lower--left {
		width: 18%;
		height: 10px;
		background-color: #ff9649;
		position: absolute;
		left: 0;
		bottom: 15px;
	}

	.bb-8__head__top__bar--red--lower--right {
		width: 28%;
		height: 10px;
		background-color: #ff9649;
		position: absolute;
		right: 0;
		bottom: 15px;
	}

	.bb-8__head__top__bar--red--lower--right:before {
		content: "";
		background-color: #fff3e7;
		width: 6px;
		height: 12px;
		position: absolute;
		left: 10px;
		top: -1px;
	}

	.bb-8__head__top__bar--gray--lower {
		background-color: #d3d3d3;
		width: 100%;
		height: 10px;
		position: absolute;
		bottom: 0px;
	}

	.bb-8__head__top__lens {
		width: 35px;
		height: 35px;
		background-color: #555555;
		border: 3px solid #fff3e7;
		border-radius: 50%;
		position: absolute;
		top: 22px;
		left: 32px;
	}

	.bb-8__head__top__lens:before {
		content: "";
		width: 8px;
		height: 8px;
		background-color: #c7c5c6;
		border-radius: 50%;
		position: absolute;
		right: 5px;
		top: 7px;
		z-index: 1;
	}

	.bb-8__head__top__lens__inner {
		width: 25px;
		height: 25px;
		background-color: #414141;
		border-radius: 50%;
		position: absolute;
		top: 5px;
		left: 5px;
	}

	.bb-8__head__top__lens--secondary {
		width: 12px;
		height: 12px;
		border: 2px solid #414141;
		border-radius: 50%;
		position: absolute;
		right: 50px;
		bottom: 18px;
	}

	.bb-8__head__top__lens--secondary__inner {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background-color: #414141;
		position: absolute;
		top: 2px;
		left: 2px;
	}

	.bb-8__head__joint {
		width: 138px;
		height: 8px;
		position: absolute;
		top: 85px;
		left: 51px;
		background-color: #bebab5;
		z-index: 4;
	}

	.bb-8__head__joint:before {
		content: "";
		width: 0;
		height: 0;
		border-style: solid;
		border-width: 8px 0 0 8px;
		border-color: transparent transparent transparent #7c9a9c;
		position: absolute;
		top: 1px;
		left: 0;
	}

	.bb-8__head__joint:after {
		content: "";
		width: 0;
		height: 0;
		border-style: solid;
		border-width: 0 0 8px 8px;
		border-color: transparent transparent #7c9a9c transparent;
		position: absolute;
		top: 1px;
		right: 0;
	}

	.bb-8__body,
	.bb-8__head-shadow {
		width: 240px;
		height: 240px;
		border-radius: 50%;
		overflow: hidden;
		position: absolute;
		top: 85px;
	}

	.bb-8__body {
		background-color: #fff3e7;
		z-index: 2;
		-webkit-animation: rotate-body 0.9s 0.5s linear infinite;
		-ms-animation: rotate-body 0.9s 0.5s linear infinite;
		animation: rotate-body 0.9s 0.5s linear infinite;
	}

	.bb-8__head-shadow {
		background-color: transparent;
		z-index: 3;
		-webkit-animation: head-tilt 0.3s 0.5s linear infinite;
		-ms-animation: head-tilt 0.3s 0.5s linear infinite;
		animation: head-tilt 0.3s 0.5s linear infinite;
	}

	.bb-8__head-shadow:before {
		content: "";
		width: 100%;
		height: 30px;
		background-color: transparent;
		background-color: rgba(0, 0, 0, 0.2);
		position: absolute;
		top: -8px;
	}

	.bb-8__body__circle {
		border-radius: 50%;
		width: 120px;
		height: 120px;
		border: 20px solid #ff9649;
		position: absolute;
		z-index: 1;
	}

	.bb-8__body__circle__bar {
		width: 35px;
		height: 20px;
		background-color: #ff9649;
		position: absolute;
		top: 50px;
		left: 80px;
	}

	.bb-8__body__circle--one {
		top: -20px;
		left: -40px;
	}

	.bb-8__body__circle--one__bar--one {
		-webkit-transform: rotate(29deg);
		-ms-transform: rotate(29deg);
		transform: rotate(29deg);
		top: 72px;
	}

	.bb-8__body__circle--one__bar--two {
		-webkit-transform: rotate(110deg);
		-ms-transform: rotate(110deg);
		transform: rotate(110deg);
		left: 5px;
		top: 100px;
	}

	.bb-8__body__circle--one__inner-circle {
		width: 70px;
		height: 70px;
		background-color: #d3d3d3;
		position: absolute;
		top: 20px;
		left: 10px;
		border-radius: 50%;
	}

	.bb-8__body__circle--one__inner-circle:before {
		content: "";
		width: 80px;
		height: 12px;
		border-top: 3px solid #fff3e7;
		border-bottom: 3px solid #fff3e7;
		position: absolute;
		top: 10px;
		-webkit-transform: rotate(30deg);
		-ms-transform: rotate(30deg);
		transform: rotate(30deg);
	}

	.bb-8__body__circle--one__inner-circle:after {
		content: "";
		width: 65px;
		height: 12px;
		border-top: 3px solid #fff3e7;
		border-bottom: 3px solid #fff3e7;
		position: absolute;
		bottom: 10px;
		-webkit-transform: rotate(30deg);
		-ms-transform: rotate(30deg);
		transform: rotate(30deg);
	}

	.bb-8__body__circle--one__inner-border {
		width: 70px;
		height: 70px;
		border: 10px solid #d3d3d3;
		border-color: #d3d3d3 transparent transparent transparent;
		position: absolute;
		top: 0px;
		left: 10px;
		border-radius: 50%;
		-webkit-transform: rotate(40deg);
		-ms-transform: rotate(40deg);
		transform: rotate(40deg);
	}

	.bb-8__body__circle--two {
		top: -20px;
		right: -80px;
	}

	.bb-8__body__circle--two__bar--one {
		-webkit-transform: rotate(-30deg);
		-ms-transform: rotate(-30deg);
		transform: rotate(-30deg);
		top: 75px;
		left: -7px;
	}

	.bb-8__body__circle--two__inner-border {
		width: 70px;
		height: 70px;
		border: 15px solid #d3d3d3;
		border-color: transparent transparent transparent #d3d3d3;
		position: absolute;
		top: 8px;
		left: 5px;
		border-radius: 50%;
		-webkit-transform: rotate(35deg);
		-ms-transform: rotate(35deg);
		transform: rotate(35deg);
	}

	.bb-8__body__circle--three {
		bottom: -70px;
		right: 20px;
	}

	.bb-8__body__circle--three__bar--one {
		-webkit-transform: rotate(91deg);
		-ms-transform: rotate(91deg);
		transform: rotate(91deg);
		top: 5px;
		left: 40px;
	}

	.bb-8__body__circle--three__bar--two {
		-webkit-transform: rotate(15deg);
		-ms-transform: rotate(15deg);
		transform: rotate(15deg);
		left: -15px;
	}

	.bb-8__body__circle--three__inner-circle {
		width: 70px;
		height: 70px;
		background-color: #d3d3d3;
		position: absolute;
		top: 38px;
		left: 25px;
		border-radius: 50%;
	}

	.bb-8__body__circle--three__inner-border {
		width: 70px;
		height: 70px;
		border: 25px solid #d3d3d3;
		border-color: #d3d3d3 transparent transparent transparent;
		position: absolute;
		top: 15px;
		left: 0px;
		border-radius: 50%;
		-webkit-transform: rotate(65deg);
		-ms-transform: rotate(65deg);
		transform: rotate(65deg);
	}

	.bb-8__body__line {
		height: 6px;
		position: absolute;
		background-color: #9e9eab;
		border-radius: 90px;
	}

	.bb-8__body__line--one {
		width: 80px;
		top: 46px;
		right: 60px;
		-webkit-transform: rotate(1deg);
		-ms-transform: rotate(1deg);
		transform: rotate(1deg);
	}

	.bb-8__body__line--two {
		width: 70px;
		bottom: 90px;
		right: 5px;
		-webkit-transform: rotate(-60deg);
		-ms-transform: rotate(-60deg);
		transform: rotate(-60deg);
	}

	.bb-8__body__line--three {
		width: 70px;
		bottom: 85px;
		left: 45px;
		-webkit-transform: rotate(60deg);
		-ms-transform: rotate(60deg);
		transform: rotate(60deg);
	}

	.bb-8__body__screw {
		border-radius: 50%;
		width: 10px;
		height: 10px;
		background-color: #9e9eab;
		position: absolute;
	}

	.bb-8__body__screw--one {
		top: 20px;
		left: 131px;
	}

	.bb-8__body__screw--two {
		top: 72px;
		left: 135px;
	}

	.bb-8__body__screw--three {
		bottom: 70px;
		right: 18px;
	}

	.bb-8__body__screw--four {
		bottom: 96px;
		right: 60px;
	}

	.bb-8__body__screw--five {
		bottom: 70px;
		left: 54px;
	}

	.bb-8__body__screw--six {
		bottom: 96px;
		left: 96px;
	}

	.bb-8__body__screw--one:before,
	.bb-8__body__screw--two:before,
	.bb-8__body__screw--three:before,
	.bb-8__body__screw--four:before,
	.bb-8__body__screw--five:before,
	.bb-8__body__screw--six:before {
		content: "";
		width: 4px;
		height: 4px;
		border: 1px solid #d3d3d3;
		border-radius: 50%;
		position: absolute;
		top: 2px;
		left: 2px;
	}

	.bb-8__body-shadow {
		width: 180px;
		height: 25px;
		background-color: transparent;
		background-color: rgba(0, 0, 0, 0.2);
		position: absolute;
		bottom: -15px;
		left: 30px;
		z-index: -1;
		border-radius: 50%;
		-webkit-animation: move-shadow 0.3s 0.5s linear infinite;
		-ms-animation: move-shadow 0.3s 0.5s linear infinite;
		animation: move-shadow 0.3s 0.5s linear infinite;
	}

	.github-link {
		position: absolute;
		bottom: 0;
		padding: 15px;
	}

	.github-link a {
		font-size: 20px;
		text-decoration: none;
		letter-spacing: 2px;
		color: #fff3e7 !important;
	}
</style>

<!-- Loading Spinner with overlay -->
<div id="loading-overlay">
	<div id="" class=" d-flex justify-content-center align-items-center" role="status">
		<div class="bb-8">
			<div class="bb-8__head">
				<div class="bb-8__head__antenna"></div>
				<div class="bb-8__head__antenna--longer"></div>
				<div class="bb-8__head__top">
					<div class="bb-8__head__top__bar--gray"></div>
					<div class="bb-8__head__top__bar--red"></div>
					<div class="bb-8__head__top__lens">
						<div class="bb-8__head__top__lens__inner"></div>
					</div>
					<div class="bb-8__head__top__lens--secondary">
						<div class="bb-8__head__top__lens--secondary__inner"></div>
					</div>
					<div class="bb-8__head__top__bar--red--lower--left"></div>
					<div class="bb-8__head__top__bar--red--lower--right"></div>
					<div class="bb-8__head__top__bar--gray--lower"></div>
				</div>
				<div class="bb-8__head__joint"></div>
			</div>
			<div class="bb-8__head-shadow"></div>
			<div class="bb-8__body">
				<div class="bb-8__body__circle bb-8__body__circle--one">
					<div class="bb-8__body__circle__bar bb-8__body__circle--one__bar--one"></div>
					<div class="bb-8__body__circle__bar bb-8__body__circle--one__bar--two"></div>
					<div class="bb-8__body__circle--one__inner-circle"></div>
					<div class="bb-8__body__circle--one__inner-border"></div>
				</div>
				<div class="bb-8__body__circle bb-8__body__circle--two">
					<div class="bb-8__body__circle__bar bb-8__body__circle--two__bar--one"></div>
					<div class="bb-8__body__circle--two__inner-border"></div>
				</div>
				<div class="bb-8__body__circle bb-8__body__circle--three">
					<div class="bb-8__body__circle__bar bb-8__body__circle--three__bar--one"></div>
					<div class="bb-8__body__circle__bar bb-8__body__circle--three__bar--two"></div>
					<div class="bb-8__body__circle--three__inner-circle"></div>
					<div class="bb-8__body__circle--three__inner-border"></div>
				</div>
				<div class="bb-8__body__line bb-8__body__line--one"></div>
				<div class="bb-8__body__line bb-8__body__line--two"></div>
				<div class="bb-8__body__line bb-8__body__line--three"></div>
				<div class="bb-8__body__screw bb-8__body__screw--one"></div>
				<div class="bb-8__body__screw bb-8__body__screw--two"></div>
				<div class="bb-8__body__screw bb-8__body__screw--three"></div>
				<div class="bb-8__body__screw bb-8__body__screw--four"></div>
				<div class="bb-8__body__screw bb-8__body__screw--five"></div>
				<div class="bb-8__body__screw bb-8__body__screw--six"></div>
			</div>
			<div class="bb-8__body-shadow"></div>
		</div>
		<div class="github-link">
			<a target="_blank">Loading....</a>
		</div>
	</div>
</div>

<script>
	// Show the loading overlay immediately when the page starts loading
	document.getElementById('loading-overlay').style.display = 'flex';

	// Listen for the 'load' event on the window to ensure the page is fully loaded
	window.addEventListener('load', function() {
		console.log('Page is fully loaded!');
		// Hide the overlay once the page is loaded
		document.getElementById('loading-overlay').style.display = 'none';
	});

	// Optionally, use a small timeout to ensure the spinner is hidden after page load
	setTimeout(function() {
		if (document.readyState === 'complete') {
			document.getElementById('loading-overlay').style.display = 'none';
		}
	}, 500); // 500ms delay to ensure proper visibility
</script>


  <?php
  if (isset($_SESSION['status_success']) || isset($_SESSION['status_error']) || isset($_SESSION['status_info'])  || isset($_SESSION['status_warning'])) {
    // Get the status messages from the session
    $status_success = isset($_SESSION['status_success']) ? $_SESSION['status_success'] : '';
    $status_error = isset($_SESSION['status_error']) ? $_SESSION['status_error'] : '';
    $status_info = isset($_SESSION['status_info']) ? $_SESSION['status_info'] : '';
    $status_warning = isset($_SESSION['status_warning']) ? $_SESSION['status_warning'] : '';

    // Unset the session variables to remove them after displaying
    unset($_SESSION['status_success']);
    unset($_SESSION['status_error']);
    unset($_SESSION['status_info']);
    unset($_SESSION['status_warning']);
  ?>

    <script>
      // Display the Toastr notifications using JavaScript
      <?php if (!empty($status_success)) : ?>
        toastr.success("<?php echo $status_success; ?>");
      <?php endif; ?>

      <?php if (!empty($status_error)) : ?>
        toastr.error("<?php echo $status_error; ?>");
      <?php endif; ?>

      <?php if (!empty($status_info)) : ?>
        toastr.info("<?php echo $status_info; ?>");
      <?php endif; ?>

      <?php if (!empty($status_warning)) : ?>
        toastr.warning("<?php echo $status_warning; ?>");
      <?php endif; ?>
    </script>
  <?php
  }


  $user_id = $_SESSION['admin_id'];


  $sql = "SELECT * FROM tbl_admin WHERE user_id = $user_id";
  $info = $obj_admin->manage_all_info($sql);
  $num_row = $info->rowCount();
  while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
	  $_SESSION['user_role'] = $row['raeAccess'];
  }
  
  ?>
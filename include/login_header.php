<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>VOYAGER	</title>
	<link rel="icon" href="../img/favicon.ico" type="image/x-icon">


	<!-- Custom fonts for this template -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">



	<!-- Custom styles for this template -->
	<link href="css/newTheme.css" rel="stylesheet">

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


<body id="page-top" class="">




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
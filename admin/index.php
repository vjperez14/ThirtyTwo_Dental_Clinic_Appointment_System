<?php
	session_start();
	if(!$_SESSION['logedin']) {
		echo "<script>alert('Please login first');</script>";
		header('Location: login.php');
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	
	<title>Administrator Access - Thirty-two Dental Care Center</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" href="../img/logo.jpg">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

	<link href="https://fonts.googleapis.com/css?family=Work+Sans:300,400,,500,600,700" rel="stylesheet">

	<link rel="stylesheet" href="assets/css/open-iconic-bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/animate.css">

	<link rel="stylesheet" href="assets/css/owl.carousel.min.css">
	<link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
	<link rel="stylesheet" href="assets/css/magnific-popup.css">

	<link rel="stylesheet" href="assets/css/aos.css">

	<link rel="stylesheet" href="assets/css/ionicons.min.css">

	<link rel="stylesheet" href="assets/css/bootstrap-datepicker.css">
	<link rel="stylesheet" href="assets/css/jquery.timepicker.css">

	<link rel="stylesheet" href="assets/css/flaticon.css">
	<link rel="stylesheet" href="assets/css/icomoon.css">
	<link rel="stylesheet" href="assets/css/style.css">
	<link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
	<nav class="navbar  navbar-expand-lg  navbar-light bg-light">
		<div class="container">
			<a class="navbar-brand" href="index.php"><img src="../assets/img/logos.png" width="200" height="100"></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
				aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="oi oi-menu"></span> Menu
			</button>
			<div class="collapse navbar-collapse" id="ftco-nav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item active"><a href="index.php" class="nav-link">Pending Appointments</a></li>
					<li class="nav-item"><a href="approved.php" class="nav-link">Approved Appointments</a></li>
					<li class="nav-item"><a href="completed.php" class="nav-link">Completed Appointments</a></li>
					<!-- <li class="nav-item"><a href="cancelled.php" class="nav-link">Rescheduled Appointments</a></li> -->
					<li class="nav-item"><a href="declined.php" class="nav-link">Declined Appointments</a></li>
				</ul>
			</div>
			<div class="collapse navbar-collapse" id="ftco-nav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item cta"><a href="logout.php" class="nav-link"><span>Log Out</span></a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<!-- END nav -->
	<div class="pending-body">
		<table id="appttable" class="display compact cell-border" >
			<thead>
				<tr>
					<th>Ticket</th>
					<th>Full Name</th>
					<th>Phone Number</th>
					<th>Email Address</th>
					<th>Service</th>
					<th>Date</th>
					<th>Time</th>
					<th>Message</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
					include('assets/php/config.php');
					$sql = "SELECT * FROM appointments WHERE `status` = 'pending'";
					$res = mysqli_query($con, $sql );
					$count = 0;
					while($row=mysqli_fetch_array($res)){
						$time = $row['time'];
						$time =  date('g:i A', strtotime($time));
						
						echo "<tr>";
							echo "<td>";echo $row['ticket']; echo "</td>";
							echo "<td>";echo $row["name"]; echo "</td>";
							echo "<td>";echo .0.$row["phone"];  echo "</td>";
							echo "<td>";echo $row["email"];  echo "</td>";
							echo "<td>";echo $row["service"];  echo "</td>";
							echo "<td>";echo $row["date"];  echo "</td>";
							echo "<td>";echo $time;  echo "</td>";
							echo "<td style='width: 50px;'><p class='break-line'>";echo $row["issue"];  echo "</p></td>";
							echo "<td style='text-align: center; width: 50px;'><button name='approve".$count."' id='approve".$count."' type='submit' class='btn form-group btn-success' value=".$row['ticket'].">Approve</button>";
							echo "<button name='cancel".$count."' id='cancel".$count."' type='submit' class='btn form-group btn-warning' value=".$row['ticket'].">Reschedule</button>";
							echo "<button name='decline".$count."' id='decline".$count."' type='submit' class='btn  btn-danger' value=".$row['ticket'].">Decline</button></td>";
						echo "</tr>";
						$count++;
					}
				?>
		</table>
	</div>
	<footer class="ftco-footer ftco-bg-dark ftco-section">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<p>
						<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
						Copyright &copy;
						<script>document.write(new Date().getFullYear());</script> All rights reserved | This website is
						made with <i class="icon-heart" aria-hidden="true"></i> by <a href="https://colorlib.com"
							target="_blank">CompuPartners</a>
						<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
					</p>
				</div>
			</div>
		</div>
	</footer>
	<!-- loader -->
	<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
		<circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
		<circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
			stroke="#F96D00" /></svg>
	</div>
	<script src="assets/js/timepicki.js"></script>
	<script>
		$('#timepicker1').timepicki();
	</script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/jquery-migrate-3.0.1.min.js"></script>
	<script src="assets/js/popper.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/jquery.easing.1.3.js"></script>
	<script src="assets/js/jquery.waypoints.min.js"></script>
	<script src="assets/js/jquery.stellar.min.js"></script>
	<script src="assets/js/owl.carousel.min.js"></script>
	<script src="assets/js/jquery.magnific-popup.min.js"></script>
	<script src="assets/js/aos.js"></script>
	<script src="assets/js/jquery.animateNumber.min.js"></script>
	<script src="assets/js/bootstrap-datepicker.js"></script>
	<script src="assets/js/jquery.timepicker.min.js"></script>
	<script src="assets/js/scrollax.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
	<script src="assets/js/google-map.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="assets/js/admin.js"></script>
	<script src="assets/js/search.js"></script>
	<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#appttable').DataTable();
        } );
    </script>
</body>
</html>
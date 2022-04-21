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

	<link href="https://fonts.googleapis.com/css?family=Work+Sans:300,400,,500,600,700" rel="stylesheet">

	<link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
	<link rel="stylesheet" href="css/animate.css">

	<link rel="stylesheet" href="css/owl.carousel.min.css">
	<link rel="stylesheet" href="css/owl.theme.default.min.css">
	<link rel="stylesheet" href="css/magnific-popup.css">

	<link rel="stylesheet" href="css/aos.css">

	<link rel="stylesheet" href="css/ionicons.min.css">

	<link rel="stylesheet" href="css/bootstrap-datepicker.css">
	<link rel="stylesheet" href="css/jquery.timepicker.css">


	<link rel="stylesheet" href="css/flaticon.css">
	<link rel="stylesheet" href="css/icomoon.css">
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
		<div class="container">
			<a class="navbar-brand" href="index.php"><img src="../img/logos.png" width="125" height="120"></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
				aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="oi oi-menu"></span> Menu
			</button>
			<div class="collapse navbar-collapse" id="ftco-nav">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item "><a href="index.php" class="nav-link">Pending Appointments</a></li>
					<li class="nav-item"><a href="forpayment.php" class="nav-link">For Payment Appointments</a></li>
					<li class="nav-item"><a href="approved.php" class="nav-link">Approved Appointments</a></li>
					<li class="nav-item"><a href="completed.php" class="nav-link">Completed Appointments</a></li>
					<li class="nav-item active"><a href="rescheduled.php" class="nav-link">Rescheduled Appointments</a></li>
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
	<section class="ftco-section bg-light" id="company">
		<div class="container">
			<div class="row justify-content-center mb-5 pb-3 ftco-animate">
				<div class="col-md-12 text-center heading-section ftco-animate">
					<br><br><br><br><br>
					<span class="subheading1">Thirty-two Dental Care Center</span>
					<h2>Rescheduled Appointments</h2>
					<div class="col-lg-12">
					<input type="text" id="myTicket" onkeyup="myFunction()" placeholder="Search for Ticket..." title="Type in a reference ID" style="font-size: 20px;">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>Ticket</th>
									<th>Full Name</th>
									<th>Phone Number</th>
									<th>Email Address</th>
									<th>Home Address</th>
									<th>Service</th>
									<th>Date</th>
									<th>Time</th>
									<th>Message</th>
								</tr>
							</thead>
							<tbody>
								<?php
									include('../php/config.php');
									$sql = "SELECT * FROM appointments WHERE `status` = 'rescheduled'";
									$res = mysqli_query($con, $sql );
									while($row=mysqli_fetch_array($res)){
										$time = $row['time'];
                           				$time =  date('g:i A', strtotime($time));
										echo "<tr>";
											echo "<td>";echo $row['ticket']; echo "</td>";
											echo "<td>";echo $row["name"]; echo "</td>";
											echo "<td>";echo $row["phone"];  echo "</td>";
											echo "<td>";echo $row["email"];  echo "</td>";
											echo "<td>";echo $row["address"];  echo "</td>";
											echo "<td>";echo $row["service"];  echo "</td>";
											echo "<td>";echo $row["date"];  echo "</td>";
											echo "<td>";echo $time;  echo "</td>";
											echo "<td>";echo $row["issue"];  echo "</td>";
										echo "</tr>";
									}
								?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<br><br>
	</section>
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
				stroke="#F96D00" /></svg></div>

	<script src="js/timepicki.js"></script>
	<script>
		$('#timepicker1').timepicki();
	</script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-migrate-3.0.1.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.easing.1.3.js"></script>
	<script src="js/jquery.waypoints.min.js"></script>
	<script src="js/jquery.stellar.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/jquery.magnific-popup.min.js"></script>
	<script src="js/aos.js"></script>
	<script src="js/jquery.animateNumber.min.js"></script>
	<script src="js/bootstrap-datepicker.js"></script>
	<script src="js/jquery.timepicker.min.js"></script>
	<script src="js/scrollax.min.js"></script>
	<script
		src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
	<script src="js/google-map.js"></script>
	<script src="js/main.js"></script>
	<script src="js/admin.js"></script>
	<script src="js/search.js"></script>

</body>
<?php
	if(isset($_POST["approve"])){
		$res=mysqli_query($link, "select * from appointments where status='pending' limit 1");
		while($row=mysqli_fetch_array($res)){
			$appno=$row['appno'];
			mysqli_query($link, "update appointments set status='approved' where appno=$appno limit 1");
		}
		?>
<script type="text/javascript">
	window.location.href = "admin.php";
</script>
<?php
	}
?>
</html>
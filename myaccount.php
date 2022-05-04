<?php
  session_start();
  $isActive = isset($_SESSION['email']);
  if($isActive){
    $user = $_SESSION['email'];
  } else {
    header("Location: index.php");
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Thirty-two Dental Care Center</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="img/logo.jpg">

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
  <link rel="stylesheet" href="assets/css/myaccount.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <nav class="navbar  navbar-expand-lg  navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="index.php"><img src="assets/img/logos.png" width="200" height="100"></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
        aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="oi oi-menu"></span> Menu
      </button>
      <div class="collapse navbar-collapse" id="ftco-nav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a href="index" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="index#services" class="nav-link">Services</a></li>
          <li class="nav-item"><a href="index#company" class="nav-link">About</a></li>
          <li class="nav-item"><a href="shedule" class="nav-link">Shedule</a></li>
          <li class="nav-item"><a href="contact" class="nav-link">Contact</a></li>
          <?php
              switch ($isActive) {
                  case 'value':{
                      ?>
                        <li class='nav-item cta active'><a href='myaccount' class='nav-link'><span>My Account</span></a></li>
                        <li class='nav-item cta'><a href='assets/php/logoutprocess.php' class='nav-link'><span>Logout</span></a></li>
                      <?php
                  break;
                  }
                  default:{
                      ?>
                        <li class="nav-item cta"><a href="login" class="nav-link"><span>Log In</span></a></li>
                        <li class="nav-item cta"><a href="register" class="nav-link"><span>Register</span></a></li>
                      <?php
                  break;
                  } 
              }
          ?>
        </ul>
      </div>
    </div>
  </nav>
  <!-- END nav -->
  <section class="ftco-section">
    <div class="container">
      <img src="assets/images/user2.png" class="img-fluid" alt="" style="float:left;width:170px;height:170px;">
      <div class="row justify-content-left mb-5 pb-5">
        <div class="col-md-7 text-left heading-section ftco-animate">
          <span class="subheading1">Hi there!</span>
          <?php
            include('assets/php/config.php');
            $sql = "SELECT * FROM registered_accounts WHERE email = '" .$_SESSION['email']. "'";
            $res = mysqli_query($con, $sql );
            if(! $res ) {
              die('Could not get data: ' . mysql_error());
            }
            while($row=mysqli_fetch_array($res)){
              $firstname = $row["firstname"];
              $lastname = $row["lastname"];
              echo "<h2 class='mb-4'>$firstname $lastname</h2>";
            }
          ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 nav-link-wrap mb-5 pb-md-5 pb-sm-1 ftco-animate">
          <div class="nav ftco-animate nav-pills justify-content-left text-center" id="v-pills-tab" role="tablist"
            aria-orientation="vertical">
            <a class="nav-link active" id="v-pills-nextgen-tab" data-toggle="pill" href="#v-pills-nextgen" role="tab"
              aria-controls="v-pills-nextgen" aria-selected="true" style="color: black">Dashboard <img
                src="assets/images/dashboard.png" style="width:30px;height:30px;"></a>

            <a class="nav-link" id="v-pills-performance-tab" data-toggle="pill" href="#v-pills-performance" role="tab"
              aria-controls="v-pills-performance" aria-selected="false" style="color: black">Edit Profile <img
                src="assets/images/user1.png" style="width:30px;height:30px;"></a>

            <a class="nav-link" id="v-pills-effect-tab" data-toggle="pill" href="#v-pills-effect" role="tab"
              aria-controls="v-pills-effect" aria-selected="false" style="color:black">Privacy Policies <img
                src="assets/images/login.png" style="width:30px;height:30px;"></a>
          </div>
        </div>
        <div class="col-md-12 align-items-center ftco-animate">
          <br><br>
          <div class="tab-content ftco-animate" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-nextgen" role="tabpanel"
              aria-labelledby="v-pills-nextgen-tab">
              <div class="d-md-flex">
                <div class="order-last align-self-center">
                </div>
                <div class="order-first" style="margin-left: -20%;">
                  <form method="POST" id="dashboard-data" enctype="multipart/form-data">
                    <table>
                      <tr>
                        <th>Appointment</th>
                        <th>Appointment Ticket</th>
                        <th>&nbsp &nbsp Status</th>
                        <th>Actions</th>
                      </tr>
                      <?php
                        include('assets/php/config.php');
                        $sql = "SELECT * FROM appointments WHERE email = '" .$_SESSION['email']. "' ORDER BY date ASC";
                        $res = mysqli_query($con, $sql );
                        if(! $res ) {
                          echo "There is no data to show";
                          die('Could not get data: ' . mysql_error());
                        }
                        if (mysqli_num_rows($res) != 0) {
                          while($row=mysqli_fetch_array($res)){
                            $time = $row['time'];
                            $time =  date('g:i A', strtotime($time));
                            
                            if ($row['status'] == 'completed') {
                              $status = "<button type='button' class='btn btn-success' data-title='Your appointment is now secured. Our technician will be contacting you on the day of the appointment'>Completed</button>";
                              $action = "<i class='fa fa-file-text-o' aria-hidden='true'></i> Evaluate the Technician";
                            } else if ($row['status'] == 'servicing') {
                              $status = "<button type='button' class='btn btn-secondary' data-title='Your appointment is now secured. Our technician will be contacting you on the day of the appointment.'>For Servicing</button>";
                              $action = "<i class='fa fa-eye' aria-hidden='true'></i> View Details";
                            } else if ($row['status'] == "approved") {
                              $status = "<button type='button' class='btn btn-info' data-title='Your appointment has been approved.'>Approved</button>";
                              $action = "<a href=#><i style='font-size:20px' class='fa'>&#xf00d;</i> Cancel Appointment</a>";
                              // $action = "<i class='fa fa-money' aria-hidden='true'></i> Proceed to Payment";
                              // $action = "<button type='button' name='paynow' id='paynow' class='btn btn-info' value=".$row['ticket']." data-title='Your appointment has been approved. Kindly settle your payment now.'></button>";
                              // $action = "<div id='paypal-button'></div>";
                            } else if ($row['status'] == 'pending') {
                              $status = "<button type='button' name='paypal-button' class='btn btn-warning' data-title='Your appointment is still being reviewed. Kindly wait for our confirmation email.'>For Approval</button>";
                              $action = "<i style='font-size:20px' class='fa'>&#xf00d;</i> Cancel Appointment";
                            } else if ($row['status'] == "declined") {
                              $status = "<button type='button' class='btn btn-danger' data-title='Your appointment has been declined. Click on 'View Details' to know more.'>Declined</button>";
                              $action = "<i class='fa fa-eye' aria-hidden='true'></i> View Details";
                            } else if ($row['status'] == 'cancelled'){
                              $status = "<button type='button' class='btn btn-danger' data-title='You have cancelled this appointment. Click on 'View Details' to know more.'>Cancelled</button>";
                              $action = "<i class='fa fa-eye' aria-hidden='true'></i> View Details";
                            }
                            echo "<tr>
                                    <td><b>" .$row['service']. "</b><br>Date: ". $row['date'] ."<br>Time: ". $time ."</td>".
                                    "<td><p>".$row['ticket']."</p></td>".
                                    "<td>".$status."</td>".
                                    "<td>".$action."</td>
                                  </tr>";
                          }
                        } else {
                          echo "<tr><td> There is no data to show.</td></tr>";
                        }
                      ?>
                    </table>
                  </form>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="v-pills-performance" role="tabpanel"
              aria-labelledby="v-pills-performance-tab">
              <div class="d-md-flex">
                <div class="one-forth align-self-center">

                </div>
                <div class="one-half order-first mr-md-5 align-self-center">
                  <form name="form1" method="post" enctype="multipart/form-data">
                    <div class="row1">
                      <div class="column1">
                        <div class="form-group">
                          <input type="text" name="firstname" id="firstname" class="form-control" required>First Name*
                        </div>
                      </div>
                      <div class="column1">
                        <div class="form-group">
                          <input type="text" name="lastname" id="lastname" class="form-control" required>Last Name*
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <input type="text" name="email" id="email" class="form-control"
                        pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                        title="Kindly follow the format (example@email.com)" required>Email Address*
                    </div>
                    <div class="form-group">
                      <input type="text" name="phone" onkeypress="validate(event)" pattern=".{10,}"
                        title="Valid phone number format: XXX-XXX-XXXX" id="phone" class="form-control" required>Phone
                      Number*
                    </div>
                    <script>
                      function validate(evt) {
                        var theEvent = evt || window.event;
                        if (theEvent.type === 'paste') {
                          key = event.clipboardData.getData('text/plain');
                        } else {
                          var key = theEvent.keyCode || theEvent.which;
                          key = String.fromCharCode(key);
                        }
                        var regex = /[0-9]|\./;
                        if (!regex.test(key)) {
                          theEvent.returnValue = false;
                          if (theEvent.preventDefault) theEvent.preventDefault();
                        }
                      }
                    </script>
                    <div class="form-group">
                      <input type="password" name="pass" id="pass" class="form-control" pattern=".{8,}"
                        title="Your password must contain eight (8) or more characters." required>Password*
                    </div>
                  </form>
                  <br>
                  <p><a href="#" class="btn btn-secondary py-3">Save Changes</a></p>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="v-pills-effect" role="tabpanel" aria-labelledby="v-pills-effect-tab">
              <div class="d-md-flex">
                <div class="align-self-center">

                </div>
                <div class="order-first align-self-center" style="margin-left: -20%;">
                  <h3 class="heading">Use of Site</h3>
                  <p></p>
                  <br>
                  <p></p>
                  <br>
                  <h3 class="heading">Amendments</h3>
                  <p></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <br><br><br>
  <footer class="ftco-footer ftco-bg-dark ftco-section1">
    <div class="container">
      <div class="row mb-5 pb-5 align-items-center d-flex">
        <div class="col-md-6">
          <div class="heading-section heading-section-white ftco-animate">
            <span class="subheading">Let us help you</span>
            <h2 style="font-size: 30px;">We are ready to cater your dental concerns</h2>
          </div>
        </div>
        <div class="col-md-3 ftco-animate">
          <div class="price">

          </div>
        </div>
        <div class="col-md-3 ftco-animate">
          <p class="mb-0"><a href="appointment.php" class="btn btn-secondary py-3 px-4">Set An Appointment</a>
          </p>
        </div>
      </div>
      <br>
      <hr style="color: white;">
      <div class="row mb-5">
        <div class="col-md">
          <div class="ftco-footer-widget mb-4 bg-primary p-4">
            <h2 class="ftco-heading-2">Thirty-two Dental Care Center</h2>
            <p>We Are A Certified Dental Clinic You Can Trust</p>
            <ul class="ftco-footer-social list-unstyled mb-0">
              <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
              <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
            </ul>
          </div>
        </div>
        <div class="col-md">
          <div class="ftco-footer-widget mb-4 ml-md-5">
            <h2 class="ftco-heading-2">Our Services</h2>
            <ul class="list-unstyled">
              <li><a href="services/service1.php" class="py-2 d-block">ORAL PROPHYLAXYS OR CLEANING</a></li>
              <li><a href="services/service3.php" class="py-2 d-block">RESTORATION OR PASTA</a></li>
              <li><a href="services/service4.php" class="py-2 d-block">DENTURES</a></li>
            </ul>
          </div>
        </div>
        <div class="col-md">
          <div class="ftco-footer-widget mb-4">
            <h2 class="ftco-heading-2"></h2>
            <ul class="list-unstyled">
              <br>
              <li><a href="services/service5.php" class="py-2 d-block">TOOTH EXTRACTION</a></li>
              <li><a href="services/service6.php" class="py-2 d-block">JACKET CROWN OR FIXED BRIDGE</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-md">
          <div class="ftco-footer-widget mb-4">
            <h2 class="ftco-heading-2">Office</h2>
            <div class="block-23 mb-3">
              <ul>
                <li><span class="icon icon-map-marker"></span><span class="text">7 L. Sianghio St,
                    Quezon City, 1103 Metro Manila Philippines</span></li>
                <li><span class="icon icon-phone"></span><span class="text">+63926 400 4227</span></li>

                <!-- loader -->
                <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
                    <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
                    <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
                      stroke="#F96D00" /></svg></div>
                <script src="assets/js/jquery.min.js"></script>
                <script src="assets/js/jquery-migrate-3.0.1.min.js"></script>
                <script src="assets/js/popper.min.js"></script>
                <script src="assets/js/bootstrap/bootstrap.min.js"></script>
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
                <script
                  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false">
                </script>
                <script src="assets/js/google-map.js"></script>
                <script src="assets/js/main.js"></script>
                <script src="assets/js/payment.js"></script>
                <script src="https://www.paypalobjects.com/api/checkout.js"></script>
                <script src="assets/js/paypal.js"></script>

</body>

</html>
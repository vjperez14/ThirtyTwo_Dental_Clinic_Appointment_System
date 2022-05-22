<!--Change this area po. Kung ano man need ayusin like name nung database and so on-->
<?php
  // session_start();
  include("assets/php/info.php");
  $isActive = isset($_SESSION['email']);
  if($isActive){
    $user = $_SESSION['email'];
  }else {
    header("Location: index.php");
  }
?>
<!--Up until here po hihi-->
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
  <link href="assets/css/timepicki.css" rel="stylesheet">


  <link rel="stylesheet" href="assets/css/flaticon.css">
  <link rel="stylesheet" href="assets/css/icomoon.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
    $(function () {
      $("#datepicker").datepicker({
        showOtherMonths: true,
        selectOtherMonths: true
      });
    });
  </script>
</head>

<body>
  <!-- Messenger Chat Plugin Code -->
  <div id="fb-root"></div>

  <!-- Your Chat Plugin code -->
  <div id="fb-customer-chat" class="fb-customerchat">
  </div>

  <script>
      var chatbox = document.getElementById('fb-customer-chat');
      chatbox.setAttribute("page_id", "115139194518992");
      chatbox.setAttribute("attribution", "biz_inbox");
  </script>

  <!-- Your SDK code -->
  <script>
      window.fbAsyncInit = function () {
          FB.init({
              xfbml: true,
              version: 'v13.0'
          });
      };

      (function (d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s);
          js.id = id;
          js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
          fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
  </script>
  <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
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
          <!-- <li class="nav-item"><a href="shedule.php" class="nav-link">Shedule</a></li> -->
          <li class="nav-item"><a href="contact" class="nav-link">Contact</a></li>
          <?php
            switch ($isActive) {
                case 'value':{
                    ?>
                      <li class='nav-item '><a href='myaccount' class='nav-link'><span>My Account</span></a></li>
                      <li class='nav-item '><a href='assets/php/logoutprocess.php' class='nav-link'><span>Logout</span></a></li>
                    <?php
                break;
                }
                default:{
                    ?>
                      <li class="nav-item "><a href="login" class="nav-link"><span>Log In</span></a></li>
                      <li class="nav-item "><a href="register" class="nav-link"><span>Register</span></a></li>
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
  <div class="hero-wrap js-fullheight">
    <div class="overlay"></div>
    <div class="container-fluid px-0">
      <div class="row d-md-flex no-gutters slider-text align-items-center js-fullheight justify-content-end">
        <img class="one-third js-fullheight align-self-end order-md-last img-fluid" src="assets/img/bg-imagedental.png"
          alt="">
        <div class="one-forth d-flex align-items-center ftco-animate js-fullheight">
          <div class="text mt-5">
            <span class="subheading">LOOKING FORWARD TO SEE YOU</span>
            <h1 class="mb-3" style="font-size: 30px;"><span>Appointment Summary!</span></h1>
            <!--Kindly check this area din po if tama yung nasa loob nung POST-->
            <?php
              include('assets/php/config.php');
                $sql = "SELECT * FROM appointments WHERE email = '" .$_SESSION['email']. "' ORDER BY apt_id DESC LIMIT 1";
                $retval = mysqli_query($con, $sql );
                if(! $retval ) {
                  die('Could not get data: ' . mysql_error());
                }
                while($row = $retval->fetch_assoc()) {
                  $time = $row['time'];
                  $time =  date('g:i A', strtotime($time));
                  echo "Dear "; echo $row['name']; echo",<br>";
                  echo "Your appointment is now being processed.<br>Here is the summary of your appointment:<br><br>";
                  echo "<b>Full Name: "; echo $row['name']; echo "<br>";
                  echo "Contact Number: "; echo "0".$row['phone']; echo "<br>"; 
                  echo "Email Address: "; echo $row['email']; echo "<br>"; 
                  echo "Service Availed: "; echo $row['service']; echo "<br>"; 
                  echo "Date and Time: "; echo $row['date']; echo " at "; echo $time; echo "<br>";
                  echo "Status: Pending"; echo "</b><br><br>";
                  echo "Thank you for trusting us. <br>";
                  echo "";
                }
            ?>
            <!--Up until here po hihi-->
            <br><br>
            <p><a href="index.php" class="btn btn-secondary px-4 py-3" style="margin-top: -5%">Go back to homepage</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
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
      </div>
      <br>
      <hr style="color: white;">
      <div class="row mb-5">
        <div class="col-md">
          <div class="ftco-footer-widget mb-4 bg-primary p-4">
            <h2 class="ftco-heading-2">Thirty-two Dental Care Center</h2>
            <p>We Are A Certified Dental Clinic You Can Trust</p>
            <ul class="ftco-footer-social list-unstyled mb-0">
              <li class="ftco-animate"><a href="https://www.facebook.com/thirtytwodentalcarecenter"><span class="icon-facebook"></span></a></li>
              <li class="ftco-animate"><a href="https://www.instagram.com/thirtytwodentalcarecenter/"><span class="icon-instagram"></span></a></li>
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
                <li><span class="icon icon-envelope"></span><span
                    class="text">thirtytwodentalcarecenter@gmail.com</span></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <br><br>
      <div class="row">
        <div class="col-md-12 text-center">
          <p>
            <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
            Copyright &copy;
            <script>
              document.write(new Date().getFullYear());
            </script> All rights reserved | This website is made with
            <i class="icon-heart" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">IReserve</a>
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
  <script src="assets/js/timepicki.js"></script>
  <script>
    $('#timepicker1').timepicki();
  </script>
  <script src="assets/js/bootstrap/bootstrap.min.js"></script>
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
  <!-- <script src="assets/js/jquery.timepicker.min.js"></script> -->
  <script src="assets/js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false">
  </script>
  <script src="assets/js/google-map.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>
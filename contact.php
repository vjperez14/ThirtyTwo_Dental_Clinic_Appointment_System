<?php
  session_start();
  $isActive = isset($_SESSION['email']);
  if($isActive){
    $user = $_SESSION['email'];
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Thirty-two Dental Care Center</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" href="assets/img/logos.png">

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
  <link rel="stylesheet" href="assets/css/timepicki.css">


  <link rel="stylesheet" href="assets/css/flaticon.css">
  <link rel="stylesheet" href="assets/css/icomoon.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
  <nav class="navbar  navbar-expand-lg  navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="index.php"><img src="assets/img/logos.png" width="200" height="100"></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
        aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="oi oi-menu"></span> Menu
      </button>
      <div class="collapse navbar-collapse" id="ftco-nav">
        <ul class="navbar-nav ml-auto">
          
          <li class="nav-item "><a href="index" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="index#services" class="nav-link">Services</a></li>
          <li class="nav-item"><a href="index#company" class="nav-link">About</a></li>
          <!-- <li class="nav-item"><a href="shedule.php" class="nav-link">Shedule</a></li> -->
          <li class="nav-item active"><a href="contact" class="nav-link">Contact</a></li>
          <?php
                    switch ($isActive) {
                        case 'value':{
                            ?>
                              <li class='nav-item cta'><a href='myaccount' class='nav-link'><span>My Account</span></a></li>
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
  <div class="bd-example">
    <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
        <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
      </ol>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="assets/img/slides/slides1.png" class="d-block w-100" alt="...">
          <div class="carousel-caption">
            <h5 class="h5-carousel-caption animated slideInDown">Welcome to Thirty-two Dental Care Center</h5>
            <h1 class="h1-carousel-caption animated zoomIn">Take The Best <br> Quality Dental <br> Treatment</h1>
            <a href="appointment" class="btn btn-lg btn-primary animated slideInUp">Set an appointment now!</a>
          </div>
        </div>
        <div class="carousel-item">
          <img src="assets/img/slides/slides3.png" class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5 class="h5-carousel-caption">Keep Your Teeth Healthy</h5>
            <h1 class="h1-carousel-caption ">Take The Best <br> Quality Dental <br> Treatment</h1>
            <a href="appointment" class="btn btn-lg btn-primary animated slideInUp">Set an appointment now!</a>

          </div>
        </div>
      </div>
      <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carouselExampleCaptions" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </div>
  <section class="ftco-section contact-section ftco-degree-bg">
    <div class="container">
      <div class="row d-flex mb-5 contact-info">
        <div class="col-md-11 mb-4">
          <h2 class="h4"><b>Contact Information</b></h2>
        </div>
        <div class="col-md-3">
          <p style="margin-left: 2%; color: black;"><img src="assets/images/icon-location.png" width="20" height="25"><b>&nbsp
              &nbsp Office Address </b></p>
          <p style="margin-left: 1%; color: black; font-size: 14px;">7 L. Sianghio St, Quezon City, 1103 Metro Manila
            Philippines</b></p>
        </div>
        <div class="col-md-3">
          <p style="margin-left: 2%; color: black;"><img src="assets/images/icon-phone.png" width="20" height="25"><b>&nbsp
              &nbsp Call Us</b></p>
          <p style="margin-left: 1%; color: black; font-size: 14px;">+63926 400 4227</b></p>
        </div>
        <div class="col-md-3">
          <p style="margin-left: 2%; color: black;"><img src="assets/images/icon-email.png" width="30" height="25"><b>&nbsp
              &nbsp Email </b></p>
          <p style="margin-left: 1%; color: black; font-size: 14px;">thirtytwodentalcarecenter@gmail.com</b></p>
        </div>
      </div>
      <br><br><br><br>
      <h2 class="h4"><b>Inquire Now</b></h2><br>
      <h2 style="font-size: 16px; margin-top: -3%;">We are more than glad to meet and assist you!</h2><br>
      <div class="row block-9">

        <div class="col-md-6 pr-md-5">
            <div class="form-group">
              <input type="text" id="name" class="form-control" placeholder=" Full Name" name="name" required>
            </div>
            <div class="form-group">
              <input type="text" id="email" class="form-control" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                title="Kindly follow the format (example@email.com)" placeholder=" Email Address" name="email" required>
            </div>
            <div class="form-group">
              <textarea id="message" cols="50" rows="15" class="form-control" placeholder="Message"
                name="message"></textarea>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-secondary py-3 px-5" id="send">Send</button>
            </div>
        </div>
        <div class="col-md-6 wow slideInUp" data-wow-delay="0.6s">
          <iframe width="100%" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
            src=https://www.google.com/maps/embed?pb=!3m2!1sen!2sph!4v1634364401011!5m2!1sen!2sph!6m8!1m7!1sdtjyP1SM1uoBdItrL9DqeQ!2m2!1d14.62597054874523!2d121.0351932666682!3f217.9693369269088!4f1.2216075788398228!5f0.7820865974627469"
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </section>
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
                            <li><a href="services/service1.php" class="py-2 d-block">ORAL PROPHYLAXYS OR CLEANING</a>
                            </li>
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
                        <i class="icon-heart" aria-hidden="true"></i> by <a href="https://colorlib.com"
                            target="_blank">IReserve</a>
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

  <!-- jquery -->
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.4.0/jquery-migrate.min.js" integrity="sha512-QDsjSX1mStBIAnNXx31dyvw4wVdHjonOwrkaIhpiIlzqGUCdsI62MwQtHpJF+Npy2SmSlGSROoNWQCOFpqbsOg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js" integrity="sha512-0QbL0ph8Tc8g5bLhfVzSqxe9GERORsKhIn1IrpxDAgUsbBGz/V7iSav2zzW325XGd1OMLdL4UiqRJj702IeqnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/3.0.0/jquery.waypoints.js" integrity="sha512-kAKC+JNXXE28EycATN2UdxoTwR/5B4GpZnGPkzc7Z0QXGnH+BbFuhYDM7/va6ouR0NfGydfVN2alYnyfU/UCgw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/stellar.js/0.6.1/jquery.stellar.min.js" integrity="sha512-MZiEKWRoqHmTsaur2/0bTQaOT5DqmnMDWuXoYXgNwFzCq+J2rQRIa4uVOmkL7SnIIhis6V4IcPnhqKxgOt8zDg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js" integrity="sha512-IsNh5E3eYy3tr/JiX2Yx4vsCujtkhwl7SLqgnwLNgf04Hrt9BT9SXlLlZlWx+OK4ndzAoALhsMNcCmkggjZB1w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-animateNumber/0.0.14/jquery.animateNumber.min.js" integrity="sha512-WY7Piz2TwYjkLlgxw9DONwf5ixUOBnL3Go+FSdqRxhKlOqx9F+ee/JsablX84YBPLQzUPJsZvV88s8YOJ4S/UA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- ------ -->

  <!-- popper -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js" integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- ------ -->
  
  <!-- bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- --------- -->
  
  <!-- carousel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- -------- -->

  <!-- aos -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- --- -->
  <script src="assets/js/scrollax.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false">
  </script>
  <script src="assets/js/google-map.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="assets/js/validateinquiry.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
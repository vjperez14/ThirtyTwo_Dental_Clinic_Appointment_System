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
    <link rel="icon" href="assets/img/logo.png">

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
            <a class="navbar-brand" href="index.php"><img src="assets/img/logos.png" width="200" height="100"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
                aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="#services" class="nav-link">Services</a></li>
                    <li class="nav-item"><a href="#company" class="nav-link">About</a></li>
                    <!-- <li class="nav-item"><a href="shedule.php" class="nav-link">Shedule</a></li> -->
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                    <?php
                        switch ($isActive) {
                            case 'value':{
                                ?>
                                    <li class='nav-item cta'><a href='myaccount.php' class='nav-link'><span>My Account</span></a></li>
                                    <li class='nav-item cta'><a href='assets/php/logoutprocess.php'
                                        class='nav-link'><span>Logout</span></a></li>
                                <?php
                            break;
                            }
                            default:{
                                ?>
                                    <li class="nav-item cta"><a href="login.php" class="nav-link"><span>Log In</span></a></li>
                                    <li class="nav-item cta"><a href="register.php" class="nav-link"><span>Register</span></a></li>
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
    <div class="container-fluid p-0">
        <div id="header-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="w-100" src="assets/img/slides/slides2.png" alt="Image">
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3" style="max-width: 900px;">
                            <h5 class="text-white text-uppercase mb-3 animated slideInDown">Welcome to Thirty-two Dental
                                Care Center</h5>
                            <h1 class="display-1 text-white mb-md-4 animated zoomIn">Take The Best Quality Dental
                                Treatment</h1>
                            <a href="appointment.php"
                                class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Appointment</a>
                            <a href="" class="btn btn-secondary py-md-3 px-md-5 animated slideInRight">Contact Us</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img class="w-100" src="assets/img/slides/slides1.png" alt="Image">
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3" style="max-width: 900px;">
                            <h5 class="text-white text-uppercase mb-3 animated slideInDown">Keep Your Teeth Healthy</h5>
                            <h1 class="display-1 text-white mb-md-4 animated zoomIn">Take The Best Quality Dental
                                Treatment</h1>
                            <a href="appointment.php" class="btn btn-secondary px-4 py-3">Set An Appointment</a>
                            <a href="" class="btn btn-secondary py-md-3 px-md-5 animated slideInRight">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev bg-transparent" type="button" data-bs-target="#header-carousel"
                data-bs-slide="prev" style="padding: 0 !important;margin: 0 !important;border: 0 !important;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next bg-transparent" type="button" data-bs-target="#header-carousel"
                data-bs-slide="next" style="padding: 0 !important;margin: 0 !important;border: 0 !important;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
    <section class="ftco-section services-section bg-light" id="services">
        <div class="row justify-content-center mb-5 pb-3">
            <div class=" text-center heading-section ftco-animate">
                <div class="row">
                    <div class="col-sm">
                        <img src="assets/img/2.jpg" width="450px" height="400px" alt="">
                    </div>
                    <div class="col-sm">
                        <img src="assets/img/1.jpg" width="450px" height="400px" alt="">
                    </div>
                    <div class="col-sm">
                        <img src="assets/img/3.jpg" width="450px" height="400px" alt="">
                    </div>
                </div><br>
                <h2 class="mb-4">Our Services</h2>
                <p>Keep Your Teeth Healthy</p><br>
            </div>
        </div>
        <div class="container">
            <br><br>
            <div class="row">
                <div class="col-md-6 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center">
                            <img src="assets/img/incisor.png" width="50px" height="50px" alt="">
                        </div>
                        <div class="media-body pl-4">
                            <a href="services/service1.php">
                                <h3 class="heading">ORAL PROPHYLAXYS OR CLEANING</h3>
                            </a>
                            <p class="mb-0">Prices Light calcular deposits ₱800</p>
                            <p class="mb-0">Moderate 1,200pesos</p>
                            <p class="mb-0">Heavy 1,500 and UP</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center order-md-last">
                            <img src="assets/img/clean.png" width="50px" height="50px" alt="">
                        </div>
                        <div class="media-body pl-4 pl-md-0 pr-md-4 text-md-right">
                            <a href="services/service3.php">
                                <h3 class="heading">RESTORATION OR PASTA</h3>
                            </a>
                            <p class="mb-0">Surface/area of each tooth ₱800</p>
                            <p class="mb-0">Additional surface/area of each tooth ₱300</p>
                            <p class="mb-0">Price is depends how big the aree of the tooth will restore</p>
                            <p class="mb-0">**additional charges for infection control and check up**</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center">
                            <img src="assets/img/teeth.png" width="50px" height="50px" alt="">
                        </div>
                        <div class="media-body pl-4">
                            <a href="services/service4.php">
                                <h3 class="heading">DENTURES</h3>
                            </a>
                            <p class="mb-0"><i>1-8 teeth per Arch ₱800</i></p>
                            <p class="mb-0"><i>Complet Denture/ almost complete ₱2,000</i></p>
                            <p class="mb-0"><i>Impacted tooth or surgery pocedure ₱7,500 and Up Per Month</i></p>
                            <p class="mb-0"><i>Materials: US Plastic pontic or ipin on acrylic plastic base</i></p>
                            <p class="mb-0"><i>Additional reinforcement for denture will charge and discuss on
                                    chairside</i></p>
                            <p class="mb-0"><i>**additional charges for infection control and check up**</i></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center order-md-last">
                            <img src="assets/img/enamel.png" width="50px" height="50px" alt="">
                        </div>
                        <div class="media-body pl-4 pl-md-0 pr-md-4 text-md-right">
                            <a href="services/service5.php">
                                <h3 class="heading">TOOTH EXTRACTION</h3>
                            </a>
                            <p class="mb-0"><i>Third molar/wisdom tooth ₱2000</i></p>
                            <p class="mb-0"><i>Impacted tooth or surgery pocedure ₱7,500 up</i></p>
                            <p class="mb-0"><i>**additional charges for infection control and check up**</i></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services d-flex align-items-center">
                        <div class="icon d-flex align-items-center justify-content-center">
                            <img src="assets/img/tooth-whitening.png" width="50px" height="50px" alt="">
                        </div>
                        <div class="media-body pl-4">
                            <a href="services/service6.php">
                                <h3 class="heading">JACKET CROWN OR FIXED BRIDGE</h3>
                            </a>
                            <p class="mb-0"><i>Plastic ₱3,500</i></p>
                            <p class="mb-0"><i>Porcelain with metal ₱3,500</i></p>
                            <p class="mb-0"><i>Pure porcelain starts ₱ 9,000 depend on class of porcelain used Per
                                    Month</i></p>
                            <p class="mb-0"><i>**additional charges for infection control and check up**</i></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="ftco-section testimony-section">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 text-center heading-section ftco-animate">
                    <h2 class="mb-4">Doctor</h2>
                </div>
            </div>
            <div class="row ftco-animate">
                <div class="col-md-12">
                    <div class="carousel-testimony owl-carousel ftco-owl">
                        <div class="item">
                            <div class="testimony-wrap p-4 text-center">
                                <div class="user-img mb-4" style="background-image: url(assets/img/doc.png)">
                                    <span class="quote d-flex align-items-center justify-content-center">
                                        <i class="icon-quote-left"></i>
                                    </span>
                                </div>
                                <div class="text">
                                    <p class="mb-4"></p>
                                    <p class="name">Dr. Mark Teejay A. Aguila</p>
                                    <span class="position">Dentist/Orthodontist</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>
    </section>
    <section class="ftco-section bg-light" id="company">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3 ftco-animate">
                <div class="col-md-12 text-center heading-section ftco-animate">
                    <span class="subheading1"></span>
                    <h2>About</h2><br>
                    <p>
                        Thirty Two Dental Clinic Center is a Dental clinic in #5 Luis Shanghio street
                        kamuning That specialize oral care. The main dental doctor in the clinic is Dr. Mark Tee Jay
                        Aguila.
                        The opening and closing time of Thirty Two Dental Clinic Center are: Mon to Sat: 9:00 AM-7:00
                        PM.
                        Some of the services provided by the clinic are: ORAL PROPHYLAXYS or CLEANING, RESTORATION OR
                        PASTA, TOOTH EXTRACTION, DENTURES and Jacket Crown or Fixed Bridge
                </div>
                </p>
            </div>
        </div>
        <br><br>
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false">
    </script>
    <script src="assets/js/google-map.js"></script>
    <script src="assets/js/main.js"></script>
    <!-- BEGIN PHP Live! HTML Code [V3] -->
    <span
        style="color: #0000FF; text-decoration: underline; line-height: 0px !important; cursor: pointer; position: fixed; bottom: 0px; right: 20px; z-index: 20000000;"
        id="phplive_btn_1600915952"></span>
    <script data-cfasync="false" type="text/javascript">
        (function () {
            var phplive_e_1600915952 = document.createElement("script");
            phplive_e_1600915952.type = "text/javascript";
            phplive_e_1600915952.async = true;
            phplive_e_1600915952.src = "//pojects.com/phplive/js/phplive_v2.js.php?v=0%7C1600915952%7C0%7C&";
            document.getElementById("phplive_btn_1600915952").appendChild(phplive_e_1600915952);
            if ([].filter) {
                document.getElementById("phplive_btn_1600915952").addEventListener("click", function () {
                    phplive_launch_chat_0()
                });
            } else {
                document.getElementById("phplive_btn_1600915952").attachEvent("onclick", function () {
                    phplive_launch_chat_0()
                });
            }
        })();
    </script>
    <!-- END PHP Live! HTML Code [V3] -->
</body>

</html>
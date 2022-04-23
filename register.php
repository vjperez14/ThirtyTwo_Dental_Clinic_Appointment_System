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
    <link rel="stylesheet" href="assets/css/formcontrol.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php"><img src="assets/img/logos.png" width="200" height="100"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
                aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="index.php#services" class="nav-link">Services</a></li>
                    <li class="nav-item"><a href="index.php#company" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                    <li class="nav-item"><a href="login.php" class="nav-link"><span>Log In</span></a></li>
                    <li class="nav-item active"><a href="register.php" class="nav-link"><span>Register</span></a></li>
                </ul>
            </div>
        </div>
        </div>
    </nav>
    <!-- END nav -->
    <br><br><br><br>
    <div class="overlay"></div>
    <div class="container-fluid px-0">
        <div class="d-md-flex">
            <div class="row d-md-flex no-gutters slider-text align-items-center js-fullheight justify-content-end">
                <img class="one-half js-fullheight align-self-end order-md-last img-fluid" src="assets/img/teeth1.png"
                    alt="">
            </div>
            <div class="one-forth ml-md-5 align-self-center">
                <br>
                <div class="text mt-5">
                    <span class="subheading1">YOUR JOURNEY WITH US STARTS HERE</span>
                    <h1 class="mb-3" style="font-size: 30px;"><b><span>Create your Thirty-two Dental Account</span></b>
                    </h1>
                    <form name="form2" id="RegForm" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" name="firstname" id="firstname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" name="lastname" id="lastname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="registeremail">Email Address</label>
                            <input type="email" name="registeremail" id="registeremail" class="form-control"
                                title="Kindly follow the format (example@email.com)" required>
                        </div>
                        <div class="form-group">
                            <label for="phonenumber">Phone Number</label>
                            <input type="text" name="phonenumber" id="phonenumber"
                                onkeypress="return onlyNumberKey(event)"
                                oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                                type="number" maxlength="11" maxlenght="11"
                                title="Valid phone number format: XXX-XXX-XXXX" class="form-control" required>
                            <script>
                                function onlyNumberKey(evt) {
                                    // Only ASCII charactar in that range allowed 
                                    var ASCIICode = (evt.which) ? evt.which : evt.keyCode
                                    if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
                                        return false;
                                    return true;
                                }
                            </script>
                        </div>
                        <div class="form-group">
                            <label for="confirmpassword">Password</label>
                            <input type="password" name="registerpassword" id="registerpassword" class="form-control"
                                pattern=".{8,}" title="Your password must contain eight (8) or more characters."
                                required>
                        </div>
                        <div class="form-group">
                            <label for="confirmpassword">Confirm Password</label>
                            <input type="password" name="confirmpassword" id="confirmpassword" class="form-control"
                                pattern=".{8,}" required>
                        </div>
                        <div class="form-group">
                            <!-- <input type="submit" name="setapp" value="Sign Up" class="btn btn-secondary py-3 px-5"> -->
                            <button type="button" id="savebtn" class="btn btn-secondary py-3 px-5"> Sign Up </button>
                        </div>

                    </form>
                    <br>
                    <p> Already a Thirty-two Dental member? Log In <a href="login.php">here</a>.</p>
                </div><br><br>
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
                    <div class="col-md-3 ftco-animate">
                        <p class="mb-0"><a href="appointment.php" class="btn btn-secondary py-3 px-4">Set An
                                Appointment</a></p>
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
                                <li><a href="service1.php" class="py-2 d-block">ORAL PROPHYLAXYS OR CLEANING</a></li>
                                <li><a href="service3.php" class="py-2 d-block">RESTORATION OR PASTA</a></li>
                                <li><a href="service4.php" class="py-2 d-block">DENTURES</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="ftco-footer-widget mb-4">
                            <h2 class="ftco-heading-2"></h2>
                            <ul class="list-unstyled">
                                <br>
                                <li><a href="service5.php" class="py-2 d-block">JACKET CROWN OR FIXED BRIDGE</a></li>
                                <li><a href="service6.php" class="py-2 d-block">JACKET CROWN OR FIXED BRIDGE</a>
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
                                    <li><span class="icon icon-phone"></span><span class="text">+63926 400 4227</span>
                                    </li>
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
                    stroke="#F96D00" />
            </svg></div>
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
        <!-- <script src="assets/js/jquery.timepicker.min.js"></script> -->
        <script src="assets/js/scrollax.min.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false">
        </script>
        <script src="assets/js/google-map.js"></script>
        <script src="assets/js/main.js"></script>
        <!-- bootstrap -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
            integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
        </script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
            integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
        </script>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"> </script>
        <!-- custom script -->
        <script src="assets/js/validate.js"></script>
</body>

</html>
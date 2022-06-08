<?php
    require_once 'emailverificationmail.php';
    require('config.php');    
    // check if email is already taken
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email_check']) && $_POST['email_check'] == 1) {
        // validate email
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $sqlcheck = "SELECT email FROM registered_accounts WHERE email = '$email' ";
        $checkResult = $con->query($sqlcheck);

        // check if email already taken
        if($checkResult->num_rows > 0) {
            echo "Sorry! email has already registered.";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {// save records into the database
        $fname = $_POST['firstname'];
        $minitial = $_POST['middleinitial'];
        $lname = $_POST['lastname'];
        $email = $_POST['registeremail'];
        $password = $_POST['registerpassword'];
        $phone = $_POST['phonenumber'];
        $save = $_POST['save'];
        $password   =        md5($password);
        $token = bin2hex(random_bytes(50));

        // $emailcheck = mysqli_real_escape_string($con, $_POST['email']);
        $sqlcheck = "SELECT email FROM registered_accounts WHERE email = '$email' ";
        $checkResult = mysqli_query($con, $sqlcheck);

        if (mysqli_num_rows($checkResult) > 0) {
            echo "taken";
        } else {
            // insert into table
            $sql = "INSERT INTO registered_accounts (firstname, middle, lastname, email, password, token, phone) VALUES ('$fname', '$minitial', '$lname', '$email', '$password', '$token', '$phone') ";
            $result = $con->query($sql);
            
            if($result) {
                sendVerificationEmail($email, $token); 
                // header("Location: ../../registersuccess.php");
                echo "Please verify your email: ".$email;
            } else {
                echo $con->error;
            }
        }
    }   

?>
<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailerMaster/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailerMaster/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailerMaster/PHPMailer-master/src/SMTP.php';



function sendVerificationEmail($userEmail, $token) {
    
    $mail = new PHPMailer();
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->Host       = "thirtytwodentalclinic.com"; // SMTP server
    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                        // 1 = errors and messages
                                        // 2 = messages only
    $mail->SMTPAuth   = "true";                  // enable SMTP authentication
    $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
    $mail->Host       = "thirtytwodentalclinic.com";      // sets GMAIL as the SMTP server
    $mail->Port       = 465; 
    $mail->Username   = "no-reply@thirtytwodentalclinic.com";
    $mail->Password   = "Dontreply.1";

    $mail->IsHTML(true);
    $mail->setFrom('no-reply@thirtytwodentalclinic.com', 'ThirtyTwoDentalClinic');
    $mail->AddAddress($userEmail);
    $mail->Subject = "Email Verification";
    $content =  '<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Test mail</title>
        <style>
            .wrapper {
            padding: 20px;
            color: #444;
            font-size: 1.3em;
            }
            a {
            background: #592f80;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            color: #fff;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <p>Thank you for signing up on our site. Please click on the link below to verify your account:.</p>
            <a href="https://thirtytwodentalclinic.com/assets/php/verify_email.php?token=' . $token . '">Verify Email!</a>
        </div>
    </body>
    </html>';

    $mail->MsgHTML($content); 
    if(!$mail->Send()) {
    echo "Error while sending Email.";
    var_dump($mail);
    } else {

    }



}

// https://thirtytwodentalclinic.com/assets/php/verify_email.php
// http://localhost/thirtytwo/assets/php/verify_email.php
?>
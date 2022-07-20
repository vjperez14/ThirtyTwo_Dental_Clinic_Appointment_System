<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailerMaster/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailerMaster/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailerMaster/PHPMailer-master/src/SMTP.php';



function sendVerificationEmail($userEmail, $token) {
    
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";

    $mail->SMTPDebug  = 0;  
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    $mail->Host       = "smtp.gmail.com";
    $mail->Username   = "ugereyes14@gmail.com";
    $mail->Password   = "wcckvozjqdffprew";

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
            <a href="http://localhost/thirtytwo/assets/php/verify_email.php?token=' . $token . '">Verify Email!</a>
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
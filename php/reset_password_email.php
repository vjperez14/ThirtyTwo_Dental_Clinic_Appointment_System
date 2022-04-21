<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailerMaster/PHPMailer-master/src/Exception.php';
require '../vendor/PHPMailerMaster/PHPMailer-master/src/PHPMailer.php';
require '../vendor/PHPMailerMaster/PHPMailer-master/src/SMTP.php';

function sendResetPasswordEmail($userEmail, $token) {
    
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";

    $mail->SMTPDebug  = 1;  
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    $mail->Host       = "smtp.gmail.com";
    $mail->Username   = "thirtytwodentalclinic32@gmail.com";
    $mail->Password   = "dummyaccount";

    $mail->IsHTML(true);
    $mail->setFrom('thirtytwodentalclinic32@gmail.com', 'Thirtytwo Dental Clinic');
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
            <p>Hi there, click on this <a href="http://localhost/thirtytwo/new_password_form.php?token=' . $token . '">link</a> to reset your password on our site</p>
        </div>
    </body>
    </html>';

    $mail->MsgHTML($content); 
    if(!$mail->Send()) {
    echo "Error while sending Email.";
    var_dump($mail);
    } else {
        echo "<script type='text/javascript'>";
        echo "window.location = '../login.php'";
        echo "</script>";
    }
}

?>
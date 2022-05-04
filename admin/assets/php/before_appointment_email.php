<?php 
    require('config.php');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../vendor/PHPMailerMaster/PHPMailer-master/src/Exception.php';
    require '../vendor/PHPMailerMaster/PHPMailer-master/src/PHPMailer.php';
    require '../vendor/PHPMailerMaster/PHPMailer-master/src/SMTP.php';

    
    $sql = "SELECT email, date, time FROM appointments";
    $results = mysqli_query($con, $sql );
    if(! $results ) {
        die('Could not get data: ' . mysql_error());
    }
    while($row = $results->fetch_assoc()) {
        $email = $row['email'];
        $time = $row['time'];
        $date = $row['date'];
        $time =  date('\\TH:i', strtotime($time));
        $date = date('Y.m.d', strtotime($date));
        $timestamp = $date.$time;

        $today = new DateTime("today"); // This object represents current date/time with time set to midnight

        $match_date = DateTime::createFromFormat( "Y.m.d\\TH:i", $timestamp );
        $match_date->setTime( 0, 0, 0 ); // set time part to midnight, in order to prevent partial comparison

        $diff = $today->diff( $match_date );
        $diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

        switch( $diffDays ) {
            case 0:
                sendReminderTodayAppointment($email);
                echo "//Today";
                break;
            case -1:
                echo "//Yesterday";
                break;
            case +1:
                sendReminderBeforeAppointment($email);
                echo "//Tomorrow";
                break;
            default:
                echo "//Sometime";
        }
    }


    function sendReminderBeforeAppointment($useremail) {
    
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
        $mail->AddAddress($useremail);
        $mail->Subject = "A reminder of your appointment";
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
                <p>Your Appointment is Tomorrow</p>
            </div>
        </body>
        </html>';
    
        $mail->MsgHTML($content); 
        if(!$mail->Send()) {
        echo "Error while sending Email.";
        var_dump($mail);
        } else {
        echo "Email sent successfully";
        }
    }

    function sendReminderTodayAppointment($useremail) {
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
        $mail->AddAddress($useremail);
        $mail->Subject = "A reminder of your appointment";
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
                <p>Today is your Appointment with us.</p>
            </div>
        </body>
        </html>';
    
        $mail->MsgHTML($content); 
        if(!$mail->Send()) {
        echo "Error while sending Email.";
        var_dump($mail);
        } else {
        echo "Email sent successfully";
        }
    }
?>
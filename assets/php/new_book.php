<?php
    require('config.php');
    session_start();
    date_default_timezone_set('Asia/Manila');
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
        $getIdQuery = "SELECT id FROM registered_accounts WHERE email = '".$_POST['email']."'";
        $getIdResult = mysqli_query($con, $getIdQuery);
        $id = mysqli_fetch_assoc($getIdResult);
        $fk_id = $id['id'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $service = $_POST['service'];
        $date = $_POST['date'];
        $date = date("Y-m-d", strtotime($date));
        $time = $_POST['time'];
        // $time =  date('g:i A', strtotime($time));
        $issue = $_POST['message'];
        $ticket = base64_encode(random_bytes(10));

        $selectedTime = mysqli_real_escape_string($con, $_POST['time']);
        $time_24hour  = date("H:i", strtotime($selectedTime));
        $curtime = date('H:i');
        
        $sqlCheck = "SELECT date, time FROM appointments WHERE time = '$selectedTime' AND date = '$date'";
        $sqlCheckResult = mysqli_query($con, $sqlCheck);
        $checkRow = mysqli_num_rows($sqlCheckResult);
        if ($checkRow > 0) {
            echo "taken";
        } elseif ($time_24hour < $curtime && date("Y-m-d") == $date ) {
            echo "late";
        } else {
            $sql = "INSERT INTO appointments (requestee, name, phone, email, service, date, time, issue, ticket, status, notif_status) VALUES ($fk_id,'$name', $phone, '$email', '$service', '$date', '$time', '$issue', '$ticket', 'pending', 1) ";
            $result = mysqli_query($con, $sql);
            if ($result) {
                echo "Your appointment has been sent.";
            } else {
                echo $con->error;
            }
        }
    }
    
?>
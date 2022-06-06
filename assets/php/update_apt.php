<?php
include('config.php');
date_default_timezone_set('Asia/Manila');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
    $id = $_POST['id'];
    $date = $_POST['date'];
    $date = date("Y-m-d", strtotime($date));
    $time = $_POST['time'];

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
        $sql = "UPDATE appointments SET date = '$date', time = '$time', status = 'pending' WHERE apt_id = '$id'";
        $result = mysqli_query($con, $sql);
        if ($result) {
            echo "Your request for reschedule will be reviewed by the admin.";
        } else {
            echo $con->error;
        }
    }
}
?>
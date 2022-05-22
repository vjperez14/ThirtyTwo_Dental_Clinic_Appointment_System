<?php
date_default_timezone_set('Asia/Manila'); 
// date_default_timezone_set('America/Los_Angeles');   
include('config.php');
$timeSlots = array("8:00 AM", "9:00 AM", "10:00 AM", "11:00 AM", "1:00 PM", "2:00 PM", "3:00 PM", "4:00 PM");



if (isset($_POST['view'])) {
    $beginDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime($beginDate.'+1 month'));
    $begin = new DateTime( $beginDate );
    $end = new DateTime( $endDate );
    $end = $end->modify( '+1 day' );

    $currentTime = new DateTime();

    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($begin, $interval ,$end);

    $nextAvailable = '';

    foreach($daterange as $date2) {
        $checkDate = date_format($date2, 'Y-m-d');
        foreach($timeSlots as $timeSlot) {
            $newDate = date_create($checkDate.' '.$timeSlot);
            if ($currentTime > $newDate or $timeSlot == "5:00 PM") {
                break;
            }
            $sql = "SELECT * FROM appointments WHERE time='$timeSlot' AND date='$checkDate'";
            $result = mysqli_query($con, $sql);
            $resultCheck = mysqli_num_rows($result);

            $nextAvailable = date_create($timeSlot.' '.$checkDate);
            $nextAvailable = date_format($nextAvailable, 'F d Y, h:i A');
            
            if ($resultCheck == 0 && $nextAvailable != '') {
                break 2;
            }
        }
        
    }
    echo "$nextAvailable<br>";
}
?>
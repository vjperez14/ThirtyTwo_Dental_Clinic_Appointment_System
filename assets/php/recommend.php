<?php
date_default_timezone_set('Asia/Manila');
include('config.php');



if (isset($_POST['view'])) {
    $time = date('h:i A');

    $curtime = date('H:i');
    
    $query8a = "SELECT * FROM appointments WHERE time = '8:00 AM'";
    $result8a = mysqli_query($con,$query8a);
    $count8a = mysqli_num_rows($result8a);

    $query9a = "SELECT * FROM appointments WHERE time = '9:00 AM'";
    $result9a = mysqli_query($con,$query9a);
    $count9a = mysqli_num_rows($result9a);

    $query10a = "SELECT * FROM appointments WHERE time = '10:00 AM'";
    $result10a = mysqli_query($con,$query10a);
    $count10a = mysqli_num_rows($result10a);

    $query11a = "SELECT * FROM appointments WHERE time = '11:00 AM'";
    $result11a = mysqli_query($con,$query11a);
    $count11a = mysqli_num_rows($result11a);

    $query1p = "SELECT * FROM appointments WHERE time = '1:00 PM'";
    $result1p = mysqli_query($con,$query1p);
    $count1p = mysqli_num_rows($result1p);

    $query2p = "SELECT * FROM appointments WHERE time = '2:00 PM'";
    $result2p = mysqli_query($con,$query2p);
    $count2p = mysqli_num_rows($result2p);

    $query3p = "SELECT * FROM appointments WHERE time = '3:00 PM'";
    $result3p = mysqli_query($con,$query3p);
    $count3p = mysqli_num_rows($result3p);

    $query4p = "SELECT * FROM appointments WHERE time = '4:00 PM'";
    $result4p = mysqli_query($con,$query4p);
    $count4p = mysqli_num_rows($result4p);

    $query5p = "SELECT * FROM appointments WHERE time = '5:00 PM'";
    $result5p = mysqli_query($con,$query5p);
    $count5p = mysqli_num_rows($result5p);

    $query6p = "SELECT * FROM appointments WHERE time = '6:00 PM'";
    $result6p = mysqli_query($con,$query6p);
    $count6p = mysqli_num_rows($result6p);

    if ( $count8a < 0 || $count8a < 3 && time() < strtotime("8:00 AM") ) {
        echo json_encode("8:00 AM");
    } elseif ( $count9a < 0 || $count9a < 3 && time() < strtotime("9:00 AM"))  {
        echo json_encode("9:00 AM");
    } elseif ( $count10a < 0 || $count10a < 3 && time() < strtotime("10:00 AM"))  {
        echo json_encode("10:00 AM");
    } elseif ( $count11a < 0 || $count11a < 3 && time() < strtotime("11:00 AM"))  {
        echo json_encode("11:00 AM");
    } elseif ( $count1p < 0 || $count1p < 3 && time() < strtotime("1:00 PM"))  {
        echo json_encode("1:00 PM");
    } elseif ( $count2p < 0 || $count2p < 3 && time() < strtotime("2:00 PM"))  {
        echo json_encode("2:00 PM");
    }elseif ( $count3p < 0 || $count3p < 3 && time() < strtotime("3:00 PM"))  {
        echo json_encode("3:00 PM");
    } elseif ( $count4p < 0 || $count4p < 3 && time() < strtotime("4:00 PM"))  {
        echo json_encode("4:00 PM");
    } elseif ( $count5p < 0 || $count5p < 3 && time() < strtotime("5:00 PM"))  {
        echo json_encode("5:00 PM");
    } elseif ( $count6p < 0 || $count6p < 3 && time() < strtotime("6:00 PM"))  {
        echo json_encode("6:00 PM");
    } else {
        echo json_encode("Tomorrow at 8:00 AM");
    }

    // echo json_encode( $count2p < 0 || $count2p < 3 && time() < strtotime("2:00 PM"));
}



?>
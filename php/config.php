<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fastech";

    // crearte connection
    $con = new Mysqli($servername, $username, $password, $dbname);

    // check connection
    if($con->connect_error) {
    die("Connection Failed : " . $connect->error);
    } else {
    // echo "Successfully Connected";	
    }
?>
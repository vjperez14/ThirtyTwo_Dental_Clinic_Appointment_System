<?php
    require('config.php'); 
    session_start();
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $fk_id = getId();
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $service = $_POST['service'];
        $date = $_POST['date'];
        $date = date("Y-m-d", strtotime($date));
        $time = $_POST['time'];
        $time =  date('g:i A', strtotime($time));
        $issue = $_POST['message'];
        $ticket = base64_encode(random_bytes(10));

        $sql = "INSERT INTO appointments (requestee, name, phone, email, address, service, date, time, issue, ticket, status) VALUES ($fk_id,'$name', $phone, '$email', '$address', '$service', '$date', '$time', '$issue', '$ticket', 'pending') ";
        $result = $con->query($sql);


                
        if($result) {
            echo "<div class='alert alert-success alert-dismissible'> 
                <button class='close' type='button' data-dismiss='alert'>&times;</button>
                Registration has completed successfully. </div>";
            // sleep(3);
                header("Location:../summary.php");
                // echo "<script>alert('". $_SESSION['email'] ."'); </script>";
        } 
        else {
            echo $con->error;
           echo "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'> &times; </button>
            Whoops! some error encountered. Please try again.";
        }
    }


    function getId(){
        $ids = 0;
        if(isset($_SESSION['email'])){
            $link=mysqli_connect("localhost","root", "") or die(mysqli_error($link));
            mysqli_select_db($link, "fastech") or die(mysqli_error($link));
            $res=mysqli_query($link, "select id from registered_accounts 
            where email = '" .$_SESSION['email']. "'");
            while($row=mysqli_fetch_array($res)){
            $ids = $row["id"];
            }
            
        }
        return $ids;
    }    

    
    
    // $save = $_POST['save'];

    // check if email is already taken
    // if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email_check']) && $_POST['email_check'] == 1) {
    //     // validate email
    //     $email = mysqli_real_escape_string($con, $_POST['email']);
    //     $sqlcheck = "SELECT email FROM registered_accounts WHERE email = '$email' ";
    //     $checkResult = $con->query($sqlcheck);

    //     // check if email already taken
    //     if($checkResult->num_rows > 0) {
    //         echo "Valid email lmao";
    //     }
    // }
    // if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 1) {
    //     echo "<script>alert('trut');</script>";
    // }   
    // else{
    //     echo "<script>alert('pols');</script>";
    // }
    // if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {// save records into the database
        // $name = $_POST['firstname'];
        // $phone = $_POST['phone'];
        // $email = $_POST['email'];
        // $address = $_POST['address'];
        // $service = $_POST['service'];
        // $date = $_POST['date'];
        // $time = $_POST['time'];
        // $issue = $_POST['issue'];
        // $save = $_POST['save'];

        // insert into table
        // $sql = "INSERT INTO appointments (name, phone, email, address, service, date, time, issue, service-ref) VALUES ('$name', '$phone', '$email', '$address', '$service', '$date', '$time', '$issue', 'putangnapre') ";
        // $result = $con->query($sql);
        
    //    // echo $con->error;
    //     if($result) {
            
    //         // echo "<div class='alert alert-success alert-dismissible'> 
    //         //     <button class='close' type='button' data-dismiss='alert'>&times;</button>
    //         //     Registration has completed successfully.
    //         // </div>";
    //     } else {
    //         echo $con->error;
	   //  //    echo "<div class='alert alert-danger alert-dismissible'>
    //     //     <button type='button' class='close' data-dismiss='alert'> &times; </button>
    //     //     Whoops! some error encountered. Please try again.";
    //     }
    // }	
    // // else{
    // //     echo "<script>alert ('putang ina pre');</script>";
    // // }
?>
<?php
    include('config.php');
    session_start();
    $error = "Incorrect username or password please try again.";
    $firstname = "";
    $lastname ="";
    if(isset($_SESSION['email'])){
      mysqli_select_db($con, "thirtytwo") or die(mysqli_error($con));
      $res=mysqli_query($con, "select firstname, middle, lastname, phone from registered_accounts where email = '" .$_SESSION['email']. "'");
      while($row=mysqli_fetch_array($res)){
        $firstname = $row["firstname"];
        $middleinitial = $row['middle'];
        $lastname = $row["lastname"];
        $phone = $row['phone'];
      }
    }
    else{
      echo "<script>alert('Register or Log-in First to book an Appointment')</script>";
    }
    

?> 
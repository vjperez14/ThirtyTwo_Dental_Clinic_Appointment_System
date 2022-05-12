<?php
    include('config.php');
    session_start();
    $error = "Incorrect username or password please try again.";
    $firstname = "";
    $lastname ="";
    if(isset($_SESSION['email'])){
      $link=mysqli_connect("localhost","root", "") or die(mysqli_error($link));
      mysqli_select_db($link, "thirtytwo") or die(mysqli_error($link));
      $res=mysqli_query($link, "select firstname from registered_accounts where email = '" .$_SESSION['email']. "'");
      while($row=mysqli_fetch_array($res)){
        $firstname = $row["firstname"];
      }
      $res=mysqli_query($link, "select lastname from registered_accounts where email = '" .$_SESSION['email']. "'");
      while($row=mysqli_fetch_array($res)){
        $lastname = $row["lastname"];
      }
    }
    else{
      echo "<script>alert('Register or Log-in First to book an Appointment')</script>";
    }
    

?> 
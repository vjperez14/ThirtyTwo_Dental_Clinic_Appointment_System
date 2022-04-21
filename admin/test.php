<?php
    include('php/config.php');
    $error = "Incorrect username or password please try again.";
    // session_start();
    // If form submitted, insert values into the database.
   //  if (isset($_POST['email'])){
        
   //     //  $email = stripslashes($_REQUEST['email']); // removes backslashes
   //     //  $email = mysqli_real_escape_string($con,$email); //escapes special characters in a string


   //     //  $password = stripslashes($_REQUEST['password']);
   //     //  $password = mysqli_real_escape_string($con,$password);
        
   //     // $password = md5($password);
   // //Checking is user existing in the database or not
   //      $query = "SELECT * FROM `registered_accounts` WHERE email='$email' and password='$password'";

   //      $result = mysqli_query($con,$query) or die(mysqli_error($con));


   //      $rows = mysqli_num_rows($result);
   //      if($rows==1){
   //         $_SESSION['email'] = $email;
   //         $_SESSION['firstname'] = $firstname;
   //      // $_SESSION['logedin'] = true;
           
   //         header("Location: ../index.php"); // Redirect user to index.php
   //      } else {
   //          $_SESSION["error"] = $error;
   //          // header("Location: ../login.php");
   //      }

   // }
    $firstname = "";
    $lastname ="";
    if(isset($_SESSION['user'])){
      $link=mysqli_connect("localhost","root", "") or die(mysqli_error($link));
      mysqli_select_db($link, "fastech") or die(mysqli_error($link));
      $res=mysqli_query($link, "select firstname from admin_account 
      where email = '" .$_SESSION['user']. "'");
      while($row=mysqli_fetch_array($res)){
        $firstname = $row["firstname"];
      }
      $res=mysqli_query($link, "select lastname from registered_accounts 
      where email = '" .$_SESSION['email']. "'");
      while($row=mysqli_fetch_array($res)){
        $lastname = $row["lastname"];
      }
    }
    else{
      echo "<script>alert('Register or Log-in First to book an Appointment')</script>";
    }
    

?> 
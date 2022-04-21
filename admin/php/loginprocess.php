<?php
    include('config.php');
    $error = "Incorrect username or password please try again.";
    session_start();
    // If form submitted, insert values into the database.
    if (isset($_POST['user'])){
        
        $user = stripslashes($_REQUEST['user']); // removes backslashes
        $user = mysqli_real_escape_string($con,$user); //escapes special characters in a string


        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con,$password);
   //Checking is user existing in the database or not
        $query = "SELECT * FROM `admin_account` WHERE user='$user' and password='$password'";

        $result = mysqli_query($con,$query) or die(mysqli_error($con));


        $rows = mysqli_num_rows($result);
        if($rows==1){
          $_SESSION['email'] = $email;
          $_SESSION['firstname'] = $firstname;
          $_SESSION['logedin'] = true;
           header("Location: ../index.php"); // Redirect user to index.php
        } else {
            $_SESSION["error"] = $error;
            header("Location: ../login.php");
        }
   }
?> 
<?php
require('config.php');  
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $sql = "SELECT * FROM registered_accounts WHERE token='$token' LIMIT 1";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $query = "UPDATE registered_accounts SET verified=1 WHERE token='$token'";

        if (mysqli_query($con, $query)) {
            $_SESSION['id'] = $user['id'];
            // $_SESSION['username'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verified'] = true;
            $_SESSION['message'] = "Your email address has been verified successfully";
            $_SESSION['type'] = 'alert-success';
            header('location: ../../login.php');
            exit(0);
        }
    } else {
        echo "User not found!";
    }
} else {
    echo "No token provided!";
}
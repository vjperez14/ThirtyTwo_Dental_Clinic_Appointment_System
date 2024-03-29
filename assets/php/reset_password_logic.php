<?php
include('reset_password_email.php');
session_start();
$errors = [];
try {
    $db = mysqli_connect('localhost', 'thirtytwo', 'denatalclinic', 'thirtytwo');
} catch (Exception $e) {
    $db = mysqli_connect('localhost', 'root', '', 'thirtytwo');
}

/*
    Accept email of user whose password is to be reset
    Send email to user to reset their password
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password']) && $_POST['reset_password'] == 1) {
    $email = mysqli_real_escape_string($db, $_POST['email']);
    // ensure that the user exists on our system
    $query = "SELECT email FROM registered_accounts WHERE email='$email'";
    $results = mysqli_query($db, $query);

    if (empty($email)) {
        array_push($errors, "Your email is required");
        echo "empty";
    }else if(mysqli_num_rows($results) <= 0) {
        array_push($errors, "Sorry, no user exists on our system with that email");
        echo "zero";
    }
    // generate a unique random token of length 100
    $token = bin2hex(random_bytes(50));

    if (count($errors) == 0) {
        $_SESSION['token'] = $token;
        // store token in the password-reset database table against the user's email
        $sql = "INSERT INTO password_resets (email, token) VALUES ('$email', '$token')";
        $results = mysqli_query($db, $sql);

        // Send email to user with the token in a link they can click on
        sendResetPasswordEmail($email, $token);
        echo "Request for reset password has been sent.";
    }
}

// ENTER A NEW PASSWORD
if (isset($_POST['new_password'])) {
    $new_pass = mysqli_real_escape_string($db, $_POST['new_pass']);
    $new_pass_c = mysqli_real_escape_string($db, $_POST['new_pass_c']);
    // Grab to token that came from the email link
    $token = $_SESSION['token'];
    if (empty($new_pass) || empty($new_pass_c)) {
        array_push($errors, "Password is required");
        $_SESSION['new_password_error'] = "Password Required";
        header("Location: https://thirtytwodentalclinic.com/new_password_form.php?token='.$token.'");
    }
    if ($new_pass !== $new_pass_c) {
        array_push($errors, "Password do not match");
        $_SESSION['new_password_error'] = "Password do not patch";
        header("Location: https://thirtytwodentalclinic.com/new_password_form.php?token='.$token.'");
    } 
    if (count($errors) == 0) {
        // select email address of user from the password_reset table 
        $sql = "SELECT email FROM password_resets WHERE token='$token' LIMIT 1";
        $results = mysqli_query($db, $sql);
        $email = mysqli_fetch_assoc($results)['email'];
        if ($email) {
            $new_pass = md5($new_pass);
            $sql = "UPDATE registered_accounts SET password='$new_pass' WHERE email='$email'";
            $results = mysqli_query($db, $sql);
            echo '<script>alert("Change Password Success")
            window.location.replace("../../login.php");
            </script>';
        }
    }
}
?>
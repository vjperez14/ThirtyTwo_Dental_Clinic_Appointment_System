<?php 
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
    $id = $_POST['id'];
    $fname = $_POST['firstname'];
    $minitial = $_POST['middleinitial'];
    $lname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    $updatequery = mysqli_query($con, "UPDATE registered_accounts SET firstname = '$fname', middle = '$minitial', lastname = '$lname', email = '$email', phone = '$phone' WHERE `id` = '$id'");
    if($updatequery) {
        echo "Your profile has been updated.";
    } else {
        echo $con->error;
    }
}


?>
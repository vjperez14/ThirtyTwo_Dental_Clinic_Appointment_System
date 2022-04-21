<?php
    require('config.php');    
    // check if email is already taken
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email_check']) && $_POST['email_check'] == 1) {
        // validate email
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $sqlcheck = "SELECT email FROM registered_accounts WHERE email = '$email' ";
        $checkResult = $con->query($sqlcheck);

        // check if email already taken
        if($checkResult->num_rows > 0) {
            echo "Sorry! email has already registered.";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {// save records into the database
        $fname = $_POST['firstname'];
        $lname = $_POST['lastname'];
        $email = $_POST['registeremail'];
        $password = $_POST['registerpassword'];
        $phone = $_POST['phonenumber'];
        $save = $_POST['save'];
        $password   =        md5($password);

        // insert into table
        $sql = "INSERT INTO registered_accounts (firstname, lastname, email, password, phone) VALUES ('$fname', '$lname', '$email', '$password', '$phone') ";
        $result = $con->query($sql);
        header("Location: ../registersuccess.php");
        if($result) {
            
            // echo "<div class='alert alert-success alert-dismissible'> 
            //     <button class='close' type='button' data-dismiss='alert'>&times;</button>
            //     Registration has completed successfully.
            // </div>";
        } else {
            echo $con->error;
        //    echo "<div class='alert alert-danger alert-dismissible'>
        //     <button type='button' class='close' data-dismiss='alert'> &times; </button>
        //     Whoops! some error encountered. Please try again.";
        }

    }   

?>
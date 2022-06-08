<style>
    html {
        font-family: 'Poppins', sans-serif;
    }
</style>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"> </script>
<?php
    include('config.php');
    $error = "Incorrect username or password please try again.";
    session_start();
    $verified = 0;
    // If form submitted, insert values into the database.
    if (isset($_POST['email'])){
        
        $email = stripslashes($_REQUEST['email']); // removes backslashes
        $email = mysqli_real_escape_string($con,$email); //escapes special characters in a string


        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($con,$password);
        
        $password = md5($password);
        //Checking is user existing in the database or not
        $query = "SELECT * FROM `registered_accounts` WHERE email='$email' and password='$password'";

        

        $result = mysqli_query($con,$query) or die(mysqli_error($con));

        while($row=mysqli_fetch_array($result)) {
            $id = $row['id'];
            $verified = $row['verified'];
            if ($row['middle'] != '') {
                $name = $row['firstname']." ".$row['middle']." ".$row['lastname'];
            } else {
                $name = $row['firstname']." ".$row['lastname'];
            }
            
        }
        $rows = mysqli_num_rows($result);
        if($rows==1){
            if(!$verified) {
                echo '<script type="text/javascript">';
                echo 'setTimeout(function () { swal.fire("Opps...","Verify your Email First: '.$email.'","error").then(function () {
                    window.location = "../../login.php";
                });';
                echo '}, 500);</script>';
                // header('location: ');
            } else {
                $_SESSION['id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['verified'] = $verified;
                header("Location: ../../index.php"); // Redirect user to index.php
            }
        } else {
            echo '<script type="text/javascript">';
            echo 'setTimeout(function () { swal.fire("Opps...","Incorrect username or password please try again.","error").then(function () {
                window.location = "../../login.php";
            });';
            echo '}, 500);</script>';
        }
   }
?> 

<?php
    require('config.php');
    require('send_email.php');


    if(isset($_GET['apt_id']) && $_GET['event'] == 'approve') {
        $id = $_GET['apt_id'];
        $decline = mysqli_query($con, "UPDATE appointments SET `status` = 'approved', `notif_status` = 0 WHERE `apt_id` = '$id'");
        $sqlGetEmail = "SELECT email FROM appointments WHERE apt_id = '$id'";
        $res = $con->query($sqlGetEmail);
        while($row=mysqli_fetch_array($res)) {
            sendVerificationEmail($row['email']);
        }
        header('location: ../../index.php');
    }

    if(isset($_GET['apt_id']) && $_GET['event'] == 'complete') {
        $id = $_GET['apt_id'];
        $decline = mysqli_query($con, "UPDATE appointments SET `status` = 'completed' WHERE `apt_id` = '$id'");
        header('location: ../../approved.php');
    }

    if(isset($_GET['apt_id']) && $_GET['event'] == 'decline') {
        $id = $_GET['apt_id'];
        $decline = mysqli_query($con, "UPDATE appointments SET `status` = 'declined' WHERE `apt_id` = '$id'");
        header('location: ../../index.php');
    }

    
?>
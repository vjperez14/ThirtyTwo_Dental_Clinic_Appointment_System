<?php
    require('config.php');
    if(isset($_GET['apt_id']) && $_GET['event'] == 'cancel') {
        $id = $_GET['apt_id'];
        $cancel = mysqli_query($con, "UPDATE appointments SET status = 'Cancelled' WHERE apt_id = '$id'");
        header('location: ../../myaccount.php');
    }
?>
<?php
    include('config.php');
    include('send_email.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
        echo "testing";
        $ref = $_POST['appticket'];
        $string = strval($ref);

        $sql = "UPDATE appointments SET `status` = 'approved' WHERE ticket = '$ref'";
        $result = $con->query($sql);

        $sqlGetEmail = "SELECT email FROM appointments WHERE ticket = '$ref'";
        $res = $con->query($sqlGetEmail);
        while($row=mysqli_fetch_array($res)) {
            sendVerificationEmail($row['email']);
        }
        

        
        header('Location: ../myaccount.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
        echo "testing";
        $ref = $_POST['canticket'];
        $string = strval($ref);

        $sql = "UPDATE appointments SET `status` = 'cancelled' WHERE ticket = '$ref'";
        $result = $con->query($sql);

        header('Location: ../myaccount.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
        echo "testing";
        $ref = $_POST['decticket'];
        $string = strval($ref);

        $sql = "UPDATE appointments SET `status` = 'declined' WHERE ticket = '$ref'";
        $result = $con->query($sql);

        header('Location: ../myaccount.php');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
        echo "testing";
        $ref = $_POST['comticket'];
        $string = strval($ref);

        $sql = "UPDATE appointments SET `status` = 'completed' WHERE ticket = '$ref'";
        $result = $con->query($sql);

        header('Location: ../myaccount.php');
    }
?>
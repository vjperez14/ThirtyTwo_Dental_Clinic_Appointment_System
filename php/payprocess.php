<?php
    include('config.php');
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $_POST['save'] == 1) {
        echo "testing";
        $ref = $_POST['ticket'];
        $string = strval($ref);

        $sql = "UPDATE appointments SET `status` = 'servicing' WHERE ticket = '$ref'";
        $result = $con->query($sql);

        header('Location: ../myaccount.php');
    }
?>
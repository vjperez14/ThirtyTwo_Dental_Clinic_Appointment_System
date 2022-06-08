<?php
    $servername = "localhost";
    $username = "thirtytwo";
    $password = "denatalclinic";
    $dbname = "thirtytwo";

    // crearte connection
    try {
        $con = new Mysqli($servername, $username, $password, $dbname);
    } catch (Exception $e) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "thirtytwo";

        // crearte connection
        $con = new Mysqli($servername, $username, $password, $dbname);
    }
?>
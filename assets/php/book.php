<style>
    body{
        font-family: "Work Sans", Arial, sans-serif;
    }
</style>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"> </script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
    require('config.php'); 
    session_start();
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $fk_id = getId();
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $service = $_POST['service'];
        $date = $_POST['date'];
        $date = date("Y-m-d", strtotime($date));
        $time = $_POST['time'];
        // $time =  date('g:i A', strtotime($time));
        $issue = $_POST['message'];
        $ticket = base64_encode(random_bytes(10));

        $sql = "INSERT INTO appointments (requestee, name, phone, email, address, service, date, time, issue, ticket, status) VALUES ($fk_id,'$name', $phone, '$email', '$address', '$service', '$date', '$time', '$issue', '$ticket', 'pending') ";
        $result = $con->query($sql);

        if($result) {
            ?>  
                <body>
                    <script type="text/javascript">
                        setTimeout(() => {
                            swal.fire({
                                icon: "success",
                                title: "Success!",
                                text: "You have successfully booked an appointment",
                            }).then(function () {
                                window.location = "../../summary.php";
                            });
                        }, 500);
                    </script>
                </body>
            <?php
            // echo '<script type="text/javascript">';
            // echo 'setTimeout(function () { swal.fire("Success","You have successfully booked an appointment","success").then(function () {
            //     window.location = "../../summary.php";
            // });';
            // echo '}, 500);</script>';
        } else {
            echo $con->error;
            echo "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'> &times; </button>
            Whoops! some error encountered. Please try again.";
        }
    }


    function getId(){
        $ids = 0;
        if(isset($_SESSION['email'])){
            $link=mysqli_connect("localhost","root", "") or die(mysqli_error($link));
            mysqli_select_db($link, "fastech") or die(mysqli_error($link));
            $res=mysqli_query($link, "select id from registered_accounts 
            where email = '" .$_SESSION['email']. "'");
            while($row=mysqli_fetch_array($res)){
            $ids = $row["id"];
            }
            
        }
        return $ids;
    }   
?>
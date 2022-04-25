<style>
    body{
        font-family: "Work Sans", Arial, sans-serif;
    }
</style>
<!-- this is needed for the sweetalert for this page -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"> </script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
    require('config.php'); 
    session_start();
    date_default_timezone_set('Asia/Manila');
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['time_check']) && $_POST['time_check'] == 1) {
        // time validation check if the slots for specific time is full or not
        $time = mysqli_real_escape_string($con, $_POST['time']);
        $date = mysqli_real_escape_string($con, $_POST['date']);
        $time_24hour  = date("H:i", strtotime($time));
        $curtime = date('H:i');
        $newDate = date("F j, Y", strtotime($date));

        $sqlcheck = "SELECT date, time, COUNT(*) as count FROM appointments WHERE time = '$time'";
        $checkResult = mysqli_query($con,$sqlcheck);
        $datetime = mysqli_fetch_assoc($checkResult);

        // check if the time slot is full on the same date as the user wants
        if($datetime['count'] == 3 && $datetime['date'] == $date) {
            echo "Sorry there are no time slot availabe on: ".$newDate;
            ?>  
                <body>
                    <script type="text/javascript"> // this is the sweetalert plugin after succes insert this will show as an alert
                        setTimeout(() => {
                            swal.fire({
                                icon: "error",
                                title: "Opps...!",
                                text: "Please check your chosen time",
                            })
                        }, 500);
                    </script>
                </body>
            <?php
        } 
        if($time_24hour < $curtime && date("Y-m-d") == $date  ) {
            echo "Sorry I'ts already: ".date("g:i A", strtotime($curtime));
            ?>  
                <body>
                    <script type="text/javascript"> // this is the sweetalert plugin after succes insert this will show as an alert
                        setTimeout(() => {
                            swal.fire({
                                icon: "error",
                                title: "Opps...!",
                                text: "Please check your chosen time",
                            })
                        }, 500);
                    </script>
                </body>
            <?php
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST'){
        // inserting the appointment data in the database
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
                    <script type="text/javascript"> // this is the sweetalert plugin after succes insert this will show as an alert
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
        } else {
            echo $con->error;
            echo "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'> &times; </button>
            Whoops! some error encountered. Please try again.";
        }
    }

    // this is just getting the unique id of the user
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
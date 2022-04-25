<?php
    require('config.php');
    if(isset($_POST['view'])) {
        if($_POST["view"] != ''){
            $update_query = "UPDATE appointments SET notif_status = 1 WHERE notif_status = 0";
            mysqli_query($con, $update_query);
        }
    
        $sql = "SELECT * FROM appointments ORDER BY apt_id DESC LIMIT 5";
        $results = mysqli_query($con, $sql);
        $output = '';
        if(mysqli_num_rows($results) > 0) {
            while($row = mysqli_fetch_array($results)) {
                $output .= '
                <li>
                <strong><a href="http://localhost/thirtytwo/myaccount.php" style="text-decoration:none;padding:0px;margin:0px;">Your Appointment has been approved.</a></strong>
                <p> Date of appointment: '.$row["date"].'</p>
                <hr>
                </li>
                ';
            }
        } else {
            $output .= '<li><a href="#" class="text-bold text-italic">No Notification Found</a></li>';
        }

        $query = "SELECT * FROM appointments WHERE notif_status=0";
        $result = mysqli_query($con,$query);
        $count = mysqli_num_rows($result);
        $data = array(
            'notification' => $output,
            'unseen_notification' => $count
        );

        // Encoding array in JSON format
        echo json_encode($data);
    }
?>
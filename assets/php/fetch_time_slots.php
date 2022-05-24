<?php
include "config.php";
date_default_timezone_set('Asia/Manila');
$date = $_POST['date'];
$selectedDate = date('Y-m-d', strtotime($date));
$timeSlots = array("8:00 AM", "9:00 AM", "10:00 AM", "11:00 AM", "1:00 PM", "2:00 PM", "3:00 PM", "4:00 PM", "5:00 PM");

$output = '<table width="75%">
<tr>
    <td class="text-left" colspan=2><h3>'.date("F j, Y", strtotime($date)).'</h3></td>
</tr>';
for ($i = 0; $i < 8; $i++) {
    $time_24hour  = date('H:i', strtotime($timeSlots[$i]));
    $curtime = date('H:i');

    $sql = "SELECT * FROM appointments WHERE time='$timeSlots[$i]' AND date='$selectedDate'";
    $result = mysqli_query($con, $sql);
    $resultCheck = mysqli_num_rows($result);
    if ($resultCheck > 0) {
        $output .= '
            
            <tr>
                <td style="text-align: center;">'.$timeSlots[$i].' - '.$timeSlots[$i+1].'</td>
                <td style="color: red; text-align: right;">Taken</td>
            </tr>
        ';
    } elseif($time_24hour < $curtime && $selectedDate == date('Y-m-d')) {
        $output .= '
            <tr>
                <td style="text-align: center;">'.$timeSlots[$i].' - '.$timeSlots[$i+1].'</td>
                <td style="color: red; text-align: right;">Its already: '.date('g:i A', strtotime($curtime)).'</td>
            </tr>
        ';
    } else {
        $output .= '
            <tr>
                <td style="text-align: center;">'.$timeSlots[$i]." - ".$timeSlots[$i+1].'</td>
                <td style="color: green; text-align: right;">Available</td>
            </tr>
        ';
    }
}
$output .='</table>';
echo $output;
?>
<?php
	$cal_year = date( "Y", time() ) ;
	$c_start = ( isset( $y_start ) ) ? $y_start : 2010 ;
?>
<script data-cfasync="false" type="text/javascript">
<!--
	function select_day( thescript )
	{
		var month = $('#day_month').val() ;
		var year = $('#day_year').val() ;
		var opid = $('#cal_opid').val() ;
		
		location.href = thescript+"?opid="+opid+"&m="+month+"&y="+year+"&"+unixtime() ;
	}
//-->
</script>
<?php $path = explode( "/", $_SERVER['PHP_SELF'] ) ; $total = count( $path ) ; $script = $path[$total-1] ; ?>
<form><input type="hidden" name="cal_opid" id="cal_opid" value="<?php echo ( isset( $opid ) ) ? $opid : "" ; ?>"><select id="day_month"><?php for( $c = 1; $c <= 12; ++$c ){ $selected = ( $c == $m ) ? "selected" : "" ; print "<option value=\"$c\" $selected>".date("F", mktime( 0, 0, 1, $c, 1, 2010 ))."</option>" ; } ?></select> <select id="day_year"><?php for( $c = $c_start; $c <= $cal_year; ++$c ){ $selected = ( $c == $y ) ? "selected" : "" ; print "<option value=\"$c\" $selected>$c</option>" ; } ?></select> &nbsp; <button type="button" onClick="select_day('<?php echo $script ?>');" class="btn" id="btn_submit_cal">submit</button></form>
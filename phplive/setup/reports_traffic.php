<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	// STANDARD header for Setup
	if ( !is_file( "../web/config.php" ) ){ HEADER("location: install.php") ; exit ; }
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	$error = "" ;

	include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/get_ext.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$statu = Util_Format_Sanatize( Util_Format_GetVar( "statu" ), "n" ) ;
	$jump = ( Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ) ? Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) : "main" ;

	if ( !isset( $CONF["foot_log"] ) ) { $CONF["foot_log"] = "on" ; }
	if ( !isset( $CONF["icon_check"] ) ) { $CONF["icon_check"] = "on" ; }

	$footprint_off = ( $CONF["foot_log"] == "off" ) ? "checked" : "" ;
	$footprint_on = ( $footprint_off == "checked" ) ? "" : "checked" ;
	$icon_check_off = ( $CONF["icon_check"] == "off" ) ? "checked" : "" ;
	$icon_check_on = ( $icon_check_off == "checked" ) ? "" : "checked" ;
	$ping = ( isset( $VALS["ping"] ) && $VALS["ping"] ) ? unserialize( $VALS["ping"] ) : Array() ;
	if ( isset( $ping["foot"] ) ) { $VARS_JS_FOOTPRINT_CHECK = $ping["foot"] ; }
	if ( isset( $ping["status"] ) ) { $VARS_JS_CHATICON_CHECK = $ping["status"] ; }

	if ( !$statu ) { $statu = time() ; }
	$m = date( "m", $statu ) ;
	$d = date( "j", $statu ) ;
	$y = date( "Y", $statu ) ;

	$today = mktime( 0, 0, 1, $m, $d, $y ) ;
	$stat_start = mktime( 0, 0, 1, $m, 1, $y ) ;
	$stat_end = mktime( 23, 59, 59, $m, date('t', $stat_start), $y ) ;
	$stat_end_day = date( "j", $stat_end ) ;

	if ( $action === "reset" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/remove.php" ) ;

		Footprints_remove_ClearFootprints( $dbh ) ;
		database_mysql_close( $dbh ) ;
		HEADER( "location: reports_traffic.php?action=success&" ) ; exit ;
	}

	$footprints_timespan = Array() ;
	if ( $CONF["foot_log"] == "on" )
		$footprints_timespan = Footprints_get_ext_FootprintsRangeHash( $dbh, $stat_start, $stat_end ) ;

	$month_max = $month_total_footprints = 0 ;
	$month_max_expand = "" ;
	foreach ( $footprints_timespan as $key => $value )
	{
		if ( $value["total"] > $month_max )
		{
			$month_max = $value["total"] ;
			$month_max_expand = date( "D, M j, Y", $key ) ;
		}
		$month_total_footprints += $value["total"] ;
	}
	$month_ave = floor( $month_total_footprints/$stat_end_day ) ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var global_foot_log = "<?php echo $CONF["foot_log"] ?>" ;
	var global_icon_check = "<?php echo $CONF["icon_check"] ?>" ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "rtraffic" ) ;

		show_div( "<?php echo $jump ?>" ) ;

		<?php if ( $action && !$error ): ?>do_alert(1, "Update Success") ;<?php endif ; ?>

		$('#stat_day_expand').html( "Select a day from above to expand" ) ;

		<?php if ( !isset( $CONF_EXTEND ) ): ?>
		init_ping_select( "foot", global_foot_log, <?php echo $VARS_JS_FOOTPRINT_CHECK ?> ) ;
		init_ping_select( "status", global_icon_check, <?php echo $VARS_JS_CHATICON_CHECK ?> ) ;
		<?php endif ; ?>
	});

	function init_ping_select( thediv, thevalue, theping )
	{
		if ( thevalue == "on" )
			$('#div_'+thediv+'_ping').show() ;
		else
			$('#div_'+thediv+'_ping').hide() ;

		if ( theping )
			$('#span_'+thediv).html( theping ) ;
	}

	function show_div( thediv )
	{
		$('#div_alert').hide() ;
	
		var divs = Array( "main", "settings" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#foot_'+divs[c]).hide() ;
			$('#menu_foot_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('#foot_'+thediv).show() ;
		$('#menu_foot_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
	}

	function select_date( theunix, thedayexpand, thetotal )
	{
		var json_data = new Object ;

		<?php if ( $CONF["foot_log"] == "on" ): ?>
		$('#stat_day_expand').html( thedayexpand+" <span class=\"info_box\" style=\"font-size: 14px; font-weight: normal;\">Total Page Views (Footprints): "+thetotal+"</span>" ) ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_reports.php",
			data: "action=footprints&sdate="+theunix+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					var footprints_string = "<table cellspacing=0 cellpadding=0 border=0 width=\"100%\">" ;
					for ( var c = 0; c < json_data.footprints.length; ++c )
					{
						total = json_data.footprints[c].total ;
						url_snap = json_data.footprints[c].url_snap ;
						url_raw = json_data.footprints[c].url_raw ;

						var td1 = "td_dept_td" ;
						var bg_color = ( c % 2 ) ? "FFFFFF" : "EDEDED" ;

						if ( url_raw == "livechatimagelink" )
						{
							url_raw = "JavaScript:void(0)" ;
							url_snap = "Live Chat Image Link" ;
						}
						footprints_string += "<tr style=\"background: #"+bg_color+"\"><td class=\""+td1+"\" width=\"16\">"+total+"</td><td class=\""+td1+"\" width=\"100%\"><a href=\""+url_raw+"\" target=\"_blank\" style=\"text-decoration: none;\">"+url_snap+"</a></td></tr>" ;
					}
					if ( !c )
						footprints_string += "<tr><td class=\"td_dept_td\" colspan=2>Blank results.</td></tr>" ;

					footprints_string += "</table>" ;
					$('#dynamic_footprints').html( footprints_string ) ;
				}
				else { do_alert( 0, json_data.error ) ; }
			}
		});
		<?php endif ; ?>
	}

	function update_foot_log( theflag )
	{
		if ( global_foot_log != theflag )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_foot_settings&option=foot_settings&value="+theflag+"&"+unixtime(),
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
					{
						global_foot_log = theflag ;
						do_alert( 1, "Update Success" ) ;
						init_ping_select( "foot", global_foot_log, 0 ) ;
					}
					else { do_alert( 0, json_data.error ) ; }
				}
			});
		}
	}

	function update_icon_check( theflag )
	{
		if ( global_icon_check != theflag )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_foot_settings&option=foot_icon&value="+theflag+"&"+unixtime(),
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
					{
						global_icon_check = theflag ;
						do_alert( 1, "Update Success" ) ;
						init_ping_select( "status", global_icon_check, 0 ) ;
					}
					else { do_alert( 0, json_data.error ) ; }
				}
			});
		}
	}

	function update_ping_interval( thediv, thevalue )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_.php",
			data: "action=update_ping&option="+thediv+"&value="+thevalue+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					do_alert( 1, "Update Success" ) ;
					if ( thediv == "foot" )
						init_ping_select( thediv, global_foot_log, thevalue ) ;
					else
						init_ping_select( thediv, global_icon_check, thevalue ) ;
				}
				else { do_alert( 0, json_data.error ) ; }
			}
		});
	}

	function do_reset_footprints()
	{
		if ( confirm( "Really clear footprints data?" ) )
			location.href = "reports_traffic.php?action=reset" ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["traffic"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu_focus" style="margin-left: 0px;" id="menu_foot_main" onClick="show_div('main')">Footprints</div>
			<div class="op_submenu" onClick="location.href='reports_refer.php'">Refer URLs</div>
			<div class="op_submenu" id="menu_foot_settings" onClick="show_div('settings')">Settings</div>
			<div style="clear: both"></div>
		</div>

		<div id="foot_main" style="margin-top: 25px;">
			
			<div style="text-shadow: none; text-align: justify;">
				On pages that has the <a href="code.php">Standard HTML Code</a>, the system will store the visitor's footprint data as they navigate from page to page.  To conserve server resources and to optimize system response, the footprint data is stored for maximum of <b><?php echo $VARS_FOOTPRINT_STATS_EXPIRE ?> days</b>.  Reports greater than <b><?php echo $VARS_FOOTPRINT_STATS_EXPIRE ?> days</b> will be automatically cleared.  It is recommended to utilize a traffic statistics tool such as <a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a> for a detailed website traffic information.
				<div style="margin-top: 10px;">To further conserve server resources, <a href="reports_traffic.php?jump=settings">footprint logging can be switched off</a>.</div>
			</div>

			<table cellspacing=0 cellpadding=0 border=0 width="100%" style="margin-top: 25px;">
			<tr>
				<td><div class="td_dept_header">Timeline</div></td>
				<td width="80"><div class="td_dept_header">Total</div></td>
			</tr>
			<tr>
				<td class="td_dept_td">
					<select onChange="location.href='reports_traffic.php?statu='+this.value">
					<?php
						$now_expire = $now - (60*60*24*$VARS_FOOTPRINT_STATS_EXPIRE) ;

						$start_month = date( "n", $now_expire ) ;
						$start_year = date( "Y", $now_expire ) ;

						$end_month = date( "n", $now ) ;						
						$end_year = date( "Y", $now ) ;

						$stat_start = mktime( 0, 0, 1, $start_month, 1, $start_year ) ;
						$stat_end = mktime( 0, 0, 1, $end_month, 1, $end_year ) ;

						if ( $stat_start == $stat_end ) { $stat_end += 1 ; }

						$c = 0 ;
						while( $stat_start < $stat_end )
						{
							$stat_unixtime = mktime( 0, 0, 1, date( "m", $stat_start )+$c, 1, date( "Y", $stat_start ) ) ;
							$this_month = date( "m", $stat_unixtime ) ;
							$stat_expand = date( "F Y", $stat_unixtime ) ;

							$selected = ( $this_month == $m ) ? "selected" : "" ;
							print "<option value=\"$stat_unixtime\" $selected>$stat_expand</option>" ;

							$c = 1 ;
							$stat_start = $stat_unixtime ;
						}
					?>
					</select>
				</td>
				<td class="td_dept_td"><?php echo $month_total_footprints ?></td>
			</tr>
			</table>

			<div style="margin-top: 25px; width: 100%;">
				<table cellspacing=0 cellpadding=0 border=0 style="height: 100px; width: 100%;">
				<tr>
					<?php
						$tooltips = Array() ;
						for ( $c = 1; $c <= $stat_end_day; ++$c )
						{
							$stat_day = mktime( 0, 0, 1, $m, $c, $y ) ;
							$stat_day_expand = date( "l, M j, Y", $stat_day ) ;

							$total = 0 ;
							$h1 = "0px" ; $meter = "meter_v_blue.gif" ;
							$tooltip = "$stat_day_expand" ;
							$tooltips[$stat_day] = $tooltip ;
							$tooltip_display = "" ;

							$meter_shadow = " box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2); " ;
							if ( isset( $footprints_timespan[$stat_day] ) )
							{
								$total = $footprints_timespan[$stat_day]["total"] ;
								$tooltip_display = "$stat_day_expand (Total: $total)" ;
								if ( $month_max )
									$h1 = round( ( $footprints_timespan[$stat_day]["total"]/$month_max ) * 100 ) . "px" ;
							}
							else if ( ( $c == $stat_end_day ) && ( !$month_max ) )
							{
								$h1 = "100px" ;
								$meter = "meter_v_clear.gif" ;
								$meter_shadow = "" ;
							}

							print "
								<td valign=\"bottom\" width=\"2%\"><div id=\"bar_v_requests_$c\" title=\"$tooltip_display\" alt=\"$tooltip_display\" style=\"height: $h1; background: url( ../pics/meters/$meter ) repeat-y; border-top-left-radius: 5px 5px; border-top-right-radius: 5px 5px; cursor: pointer; $meter_shadow\" OnMouseOver=\"\" OnClick=\"select_date( $stat_day, '$stat_day_expand', $total );\"></div></td>
								<td><img src=\"../pics/space.gif\" width=\"5\" height=1></td>
							" ;
						}
					?>
				</tr>
				<tr>
					<?php
						for ( $c = 1; $c <= $stat_end_day; ++$c )
						{
							$stat_day = mktime( 0, 0, 1, $m, $c, $y ) ;
							$stat_day_expand = date( "l, M j, Y", $stat_day ) ;
							$total = 0 ;
							if ( isset( $footprints_timespan[$stat_day] ) ) { $total = $footprints_timespan[$stat_day]["total"] ; }
							print "
								<td align=\"center\"><div id=\"requests_bg_day\" OnMouseOver=\"\" OnClick=\"select_date( $stat_day, '$stat_day_expand', $total );\" class=\"page_report\" style=\"margin: 0px; font-size: 10px; font-weight: bold;\" title=\"$tooltips[$stat_day]\" id=\"$tooltips[$stat_day]\">$c</div></td>
								<td><img src=\"../pics/space.gif\" width=\"5\" height=1></td>
							" ;
						}
					?>
				</tr>
				</table>
			</div>

			<div id="overview_day_chart" style="margin-top: 50px;">
				<div id="overview_date_title"><div id="stat_day_expand"></div></div>
				<div id="overview_data_container">
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td><div class="td_dept_header">Top 100 Footprints</div></td>
					</tr>
					<tr>
						<td><div style="height: 300px; overflow: auto;">
						<div id="dynamic_footprints">
							<?php if ( $CONF["foot_log"] != "on" ): ?>
							<div class="td_dept_td">Footprints logging is switched <a href="reports_traffic.php?jump=settings">Off</a>.</div>
							<?php endif ; ?>
						</div>
						</div></td>
					</tr>
					</table>
				</div>

				<div style="margin-top: 45px;" class="info_info">
					<div><img src="../pics/icons/reset.png" width="16" height="16" border="0" alt=""> <b>Clear Footprints data</b></div>
					<div style="margin-top: 5px;">Clear the footprints data from the database.  This action cannot be reversed.</div>

					<div style="margin-top: 15px;"><button type="button" onClick="do_reset_footprints()">Clear Data</button></div>
				</div>
			</div>
		</div>

		<div id="foot_settings" style="margin-top: 25px; text-align: justify;">
			<div style="float: left; min-height: 470px; width: 45%" class="info_info">
				<div style="font-size: 14px; font-weight: bold;"><a name="footprints">Visitor Footprint Logging</a></div>
				
				<div style="margin-top: 15px;">
					(default is On) For pages that has the <a href="code.php">Standard HTML Code</a>, the system will ping the server every <span id="span_foot"><?php echo $VARS_JS_FOOTPRINT_CHECK ?></span> seconds to update the visitor's footprint data.  The resource usage of the communication is very minimal and takes milliseconds to process.  However, to reduce server resource usage, switch the setting to "Off".
					
					<div class="info_neutral" style="margin-top: 10px;">
						<li style="padding: 8px; list-style: none;"> <span class=""><b>Off</b></span> will communicate to the server only once, at time of page load
						<li style="margin-top: 5px; padding: 8px; list-style: none;"> <span class=""><b>Off</b></span> will pause all storing of visitor footprint data
						<li style="margin-top: 5px; padding: 8px; list-style: none;"> <span class=""><b>Off</b></span> will hide all footprint instances throughout the operator console
					</div>
				</div>

				<div style="margin-top: 25px;">
					<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#r_foot_settings_on').prop('checked', true);update_foot_log('on');"><input type="radio" name="r_foot_settings" id="r_foot_settings_on" value="on" <?php echo $footprint_on ?>> On</div>
					<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#r_foot_settings_off').prop('checked', true);update_foot_log('off');"><input type="radio" name="r_foot_settings" id="r_foot_settings_off" value="off" <?php echo $footprint_off ?>> Off</div>
					<div style="clear: both;"></div>
				</div>
				<div style="display: none; margin-top: 25px;" id="div_foot_ping">
					Ping the server every 
					<select id="foot_ping" onChange="update_ping_interval('foot', this.value)">
					<?php
						for ( $c = 20; $c <= 60; ++$c )
						{
							$selected = ( $VARS_JS_FOOTPRINT_CHECK == $c ) ? "selected" : "" ;
							if ( $c % 5 == 0 )
								print "<option value='$c' $selected>$c</option>" ;
						}
					?>
					</select> seconds (default is 60 seconds).
					<div style="margin-top: 5px;">The greater the seconds, less server resources will be used.  But it will take longer to detect the visitor has left the website (traffic data on the operator console traffic monitor).</div>
				</div>
			</div>
			<div style="float: left; margin-left: 2px; min-height: 470px; width: 45%;" class="info_info">
				<div style="font-size: 14px; font-weight: bold;">Chat Icon Status Check</div>

				<div style="margin-top: 15px;">
					(default is On) For pages that has the <a href="code.php">Standard HTML Code</a>, while the visitor is viewing the page, the system will ping the server every <span id="span_status"><?php echo $VARS_JS_CHATICON_CHECK ?></span> seconds to update and gather various information.  The resource usage is very minimal and takes milliseconds to process.  However, to reduce server resource usage, switch the setting to "Off".
					
					<div class="info_neutral" style="margin-top: 10px;">
						<li style="padding: 8px; list-style: none;"> <span class=""><b>Off</b></span> will communicate to the server only once, at time of page load
						<li style="margin-top: 5px; padding: 8px; list-style: none;"> <span class=""><b>Off</b></span> will switch off the automatic chat icon online/offline status update while the visitor is on the same page.
						<li style="margin-top: 5px; padding: 8px; list-style: none;"> <span class=""><b>Off</b></span> will switch off the ability for operators to send a chat invite to the visitor ("chat invite" feature on the operator console traffic monitor).
					</div>
				</div>

				<div style="margin-top: 25px;">
					<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#r_foot_icon_on').prop('checked', true);update_icon_check('on');"><input type="radio" name="r_foot_icon" id="r_foot_icon_on" value="on" <?php echo $icon_check_on ?>> On</div>
					<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#r_foot_icon_off').prop('checked', true);update_icon_check('off');"><input type="radio" name="r_foot_icon" id="r_foot_icon_off" value="off" <?php echo $icon_check_off ?>> Off</div>
					<div style="clear: both;"></div>
				</div>
				<div style="display: none; margin-top: 25px;" id="div_status_ping">
					Ping the server every 
					<select id="status_ping" onChange="update_ping_interval('status', this.value)">
					<?php
						for ( $c = 10; $c <= 60; ++$c )
						{
							$selected = ( $VARS_JS_CHATICON_CHECK == $c ) ? "selected" : "" ;
							if ( $c % 5 == 0 )
								print "<option value='$c' $selected>$c</option>" ;
						}
					?>
					</select> seconds (default is 25 seconds).
					<div style="margin-top: 5px;">The greater the seconds, less server resources will be used.  But it will take longer for the <b>operator initiated chat invites</b> to display to the visitor and it will take longer for the chat icon to automatically switch status (online to offline or offline to online) while the visitor is on the same page.</div>
				</div>
			</div>
			<div style="clear: both;"></div>

		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>

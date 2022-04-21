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
	if ( !is_file( "../../web/config.php" ) ){ HEADER("location: ../../setup/install.php") ; exit ; }
	include_once( "../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/Util_Proaction.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }

	$PROACTION_VERSION = "1.0" ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/VERSION.php" ) )
		include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/VERSION.php" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$depts_hash = Array( 0 => "All Departments" ) ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$depts_hash[$department["deptID"]] = $department["name"] ;
	}
	$proactions = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["proaction"] ) && $VALS_ADDONS["proaction"] ) ? unserialize( base64_decode( $VALS_ADDONS["proaction"] ) ) : Array() ;
	$proactions_priority_array = Array() ;
	foreach ( $proactions as $thisproid => $proaction_array )
	{
		$priority = $proactions[$thisproid]["priority"] ;
		$proactions_priority_array[$priority] = $thisproid ;
	} krsort( $proactions_priority_array ) ;
	$dept_groups = Depts_get_AllDeptGroups( $dbh ) ; $dept_groups_hash = Array() ;
	for ( $c = 0; $c < count( $dept_groups ); ++$c )
	{
		$dept_group = $dept_groups[$c] ;
		$dept_groups_hash[$dept_group["groupID"]] = $dept_group["name"] ;
	}
?>
<?php include_once( "../../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "../../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("html").css({'background': '#F4F6F8'}) ; $("body").css({'background': '#F4F6F8'}) ;
	});

	function reset_stats( theproid )
	{
		if ( confirm( "Reset stats and set values to zero?" ) )
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			$.ajax({
			type: "POST",
			url: "./ajax/setup_actions.php",
			data: "action=reset&proid="+theproid+"&"+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					$('#img_reset_'+theproid).hide() ;
					$('#td_click_total_'+theproid).html("0") ;
					$('#td_click_accepted_'+theproid).html("0 (0%)") ;
					$('#td_click_declined_'+theproid).html("0 (0%)") ;
					parent.do_alert( 1, "Update Success" ) ;
				}
				else
				{
					parent.do_alert( 0, json_data.error ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				parent.do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
			} });
		}
	}

	var proids_status = new Object ;
	function update_status( theproid, thestatus )
	{
		if ( proids_status[theproid] != thestatus )
		{
			$('#status_'+theproid+'_'+thestatus).prop('checked', true) ;
			var unique = unixtime() ;
			var json_data = new Object ;

			$.ajax({
			type: "POST",
			url: "./ajax/setup_actions.php",
			data: "action=pause&proid="+theproid+"&status="+thestatus+"&"+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					proids_status[theproid] = thestatus ;
					parent.do_alert( 1, "Update Success" ) ;
				}
				else
				{
					parent.do_alert( 0, json_data.error ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				parent.do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
			} });
		}
		else
			parent.do_alert( 1, "Update Success" ) ;
	}
//-->
</script>
</head>
<body>
	<div id="div_proaction" style="">
		<table cellspacing=0 cellpadding=2 border=0 width="100%" id="table_proaction">
		<tr>
			<td><div class="td_dept_header"></div></td>
			<td><div class="td_dept_header">Priority</div></td>
			<td><div class="td_dept_header">Message</div></td>
			<td><div class="td_dept_header" style="text-align: center;">Status</div></td>
			<td><div class="td_dept_header" style="text-align: center;">Icon</div></td>
			<td><div class="td_dept_header">Criteria</div></td>
			<td width="100"><div class="td_dept_header">Device</div></td>
			<td><div class="td_dept_header">Reset</div></td>
		</tr>
		<tbody>
		<?php
			$array_index = 0 ;
			foreach ( $proactions_priority_array as $null => $thisproid )
			{
				$proaction_array = $proactions[$thisproid] ;
				// [deptid] => 0 [position] => 1 [duration] => 5 [andor] => 1 [footprints] => 1 [reset] => 1 [profile] => 1 [exin] => include [exclude] => [message] =>
				if ( isset( $depts_hash[$proaction_array["deptid"]] ) || isset( $dept_groups_hash[$proaction_array["deptid"]] ) )
				{
					$department = isset( $dept_groups_hash[$proaction_array["deptid"]] ) ? $dept_groups_hash[$proaction_array["deptid"]] : $depts_hash[$proaction_array["deptid"]] ;
					$priority = $proaction_array["priority"] ;
					$position = $proaction_array["position"] ;
					$duration = Util_Format_Duration( $proaction_array["duration"], 1 ) ;
					$andor = ( $proaction_array["andor"] == 2 ) ? '<span class="info_blue">and</span>' : '<span class="info_slate">or</span>' ;
					$footprints = $proaction_array["footprints"] ;
					$reset = $proaction_array["reset"] ;
					if ( !isset( $proaction_array["device"] ) ) { $proaction_array["device"] = 1 ; }
					$device = $proaction_array["device"] ;

					if ( $reset === "" ) { $reset = "3 hours" ; }
					else if ( $reset < 0 ) { $reset = "never";  }
					else if ( $reset === 0 ) { $reset = "35 seconds";  }
					else { $reset = "$reset hours" ; }

					$status_on_checked = ( !isset( $proaction_array["paused"] ) ) ? "checked" : "" ;
					$status_off_checked = ( $status_on_checked == "" ) ? "checked" : "" ;

					$profile = "Hide" ; $profile_image = "None" ;
					if ( $proaction_array["profile"] == 1 ) { $profile = "Default" ; $profile_image = "<div alt=\"Display the Global Default profile picture.\" title=\"Display the Global Default profile picture.\" style=\"cursor: help;\"><img src=\"".Util_Upload_GetLogo( "profile", 0 )."\" width=\"50\" height=\"50\" border=0><br>default</div>" ; }
					else if ( $proaction_array["profile"] == 2 ) { $profile = "Random" ; $profile_image = "<div alt=\"Display random online Department Operator&lsquo;s profile picture.\" title=\"Display random online Department Operator&lsquo;s profile picture.\"><img src=\"../../themes/initiate/profile_random.png\" width=\"50\" height=\"50\" border=0 style=\"cursor: help;\" class=\"round\"></div>" ; }

					$device_string = "All Devices" ;
					if ( $device == 2 ) { $device_string = "Only Desktop Computers" ; }
					else if ( $device == 3 ) { $device_string = "Only Mobile" ; }

					$exin = ( $proaction_array["exin"] == "exclude" ) ? "exclude: " : "include: " ;
					$exclude = $proaction_array["exclude"] ;
					if ( !$exclude ) { $exin = "" ; }
					else { $exclude = preg_replace( "/[\(\)]/", "", preg_replace( "/\|/", ",", $exclude ) ) ; }
					$exclude_string = ( $exclude ) ? "<div style=\"margin-top: 5px;\"><img src=\"pics/include.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"$exin: $exclude\" title=\"$exin: $exclude\" style=\"cursor: help;\"></div>" : "" ;

					$priority_string = ( $array_index ) ? '<a href="JavaScript:void(0)" onClick="parent.move_to_top(\''.$thisproid.'\', \''.$array_index.'\')"><img src="../../pics/icons/top.png" width="16" height="16" border="0" alt=""><br>move to top</a>' : '' ;

					$onoff = "<span class='info_good' style='text-shadow: none; cursor: help' title='display only when chat is online' alt='display only when chat is online'>Online</span><div style=\"margin-top: 10px; margin-bottom: 10px;\">or</div><span class='info_error' style='text-shadow: none; cursor: help;' title='display only when chat is offline' alt='display only when chat is offline'>Offline</span>" ;
					if ( isset( $proaction_array["onoff"] ) )
					{
						if ( $proaction_array["onoff"] == 1 ) { $onoff = "<span class='info_good' style='text-shadow: none; cursor: help;' title='display only when chat is online' alt='display only when chat is online'>Online</span>" ; }
						else if ( $proaction_array["onoff"] == 0 ) { $onoff = "<span class='info_error' style='text-shadow: none; cursor: help;' title='display only when chat is offline' alt='display only when chat is offline'>Offline</span>" ; }
					} else { $proaction_array["onoff"] = 1 ; }
					$click = isset( $proaction_array["click"] ) ? $proaction_array["click"] : "chat" ;
					$click_behavior = '<img src="../../pics/icons/chat_blue.png" width="16" height="16" border="0" alt="open the chat request window" title="open the chat request window">' ;
					if ( preg_match( "/^http/", $click ) )
						$click_behavior = "<a href=\"$click\" target=\"_blank\"><img src=\"../../pics/icons/link.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"open url $click\" title=\"open url $click\"></a>" ;
					else if ( $click == "" )
						$click_behavior = "<img src=\"../../pics/icons/delete_sm.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"do nothing\" title=\"do nothing\">" ;
					$message = rawurldecode( $proaction_array["message"] ) ;

					$tr_color = ( ( $array_index + 1 ) % 2 ) ? "FFFFFF" : "EDEDED" ;
					$click_stats = Util_Proaction_GetClickStats( $dbh, $thisproid ) ;
					$click_accept_percent = ( $click_stats["total_views"] ) ? round( $click_stats["total_accepted"]/$click_stats["total_views"], 2 ) * 100 : 0 ;
					$click_declined_percent = ( $click_stats["total_views"] ) ? round( $click_stats["total_declined"]/$click_stats["total_views"], 2 ) * 100 : 0 ;
					
					$reset_string = ( $click_stats["total_views"] ) ? "<img src=\"../../pics/icons/reset.png\" width=\"14\" height=\"14\" border=\"0\" style=\"cursor: pointer;\" onClick=\"reset_stats('$thisproid')\" alt=\"reset stats\" title=\"reset stats\" id=\"img_reset_$thisproid\">" : "" ;

					$edit_delete = "<div style=\"cursor: pointer;\" onClick=\"parent.do_edit( '$thisproid', $proaction_array[deptid], $proaction_array[position], $proaction_array[duration], $proaction_array[andor], $proaction_array[footprints], $proaction_array[reset], $proaction_array[profile], '$proaction_array[exin]', '$exclude', $proaction_array[onoff], $proaction_array[device], '$proaction_array[click]', '".rawurlencode( $proaction_array["message"] )."' )\"><img src=\"../../pics/btn_edit.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></div><div onClick=\"parent.do_delete( '$thisproid' )\" style=\"margin-top: 10px; cursor: pointer;\"><img src=\"../../pics/btn_delete.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></div>" ;

					print '<tr id="tr_'.$array_index.'" bgColor="'.$tr_color.'">
						<td class="td_dept_td" valign="top">'.$edit_delete.'<div style="margin-top: 5px;">
							<div class="info_good" style="width: 60px; padding: 3px; cursor: pointer; text-shadow: none;" onclick="update_status(\''.$thisproid.'\', 1)"><input type="radio" name="status_'.$thisproid.'" id="status_'.$thisproid.'_1" value="1" '.$status_on_checked.'> Active</div>
							<div class="info_error" style="margin-top: 5px; width: 60px; padding: 3px; cursor: pointer; text-shadow: none;" onclick="update_status(\''.$thisproid.'\', 0)"><input type="radio" name="status_'.$thisproid.'" id="status_'.$thisproid.'_0" value="0" '.$status_off_checked.'> Pause</div>
						</div></td>
						<td class="td_dept_td" valign="top">'.$priority_string.'</td>
						<td style="" class="td_dept_td" valign="top">
							<div style="text-shadow: none;" class="info_neutral" alt="display this ProAction for HTML Code: '.$department.'" title="display this ProAction for HTML Code: '.$department.'">'.$department.'</div>
							<table cellspacing=0 cellpadding=0 border=0><tr>
								<td><div id="div_message_'.$thisproid.'" style="position: relative; background: #FFFFFF; padding: 10px; border-radius: 5px; box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2); width: 150px; height: 75px; overflow: hidden;"><div>'.$message.'</div><div style="position: absolute; top: 10px; left: 0px; width: 170px; height: 85px; background: transparent; cursor: pointer;" onClick="parent.view_demo(\''.$thisproid.'\', '.$proaction_array["position"].', '.$proaction_array["profile"].', \''.rawurlencode( $proaction_array["message"] ).'\')" title="view invite preview" alt="view invite preview"></div></div><div style="margin-top: 5px; font-size: 10px; color: #A8A8A8;">ID: '.$thisproid.'</div></td>
								<td style="padding-left: 15px;">
									<div style="padding-bottom: 5px; border-bottom: 1px solid #888787;">stats '.$reset_string.'</div>
									<div style="margin-top: 5px;">
										<table cellspacing=0 cellpadding=2 border=0>
										<tr>
											<td><img src="pics/visitors.png" width="16" height="16" border="0" alt="views" title="views"></td>
											<td><div id="td_click_total_'.$thisproid.'">'.$click_stats["total_views"].'</div></td>
										</tr>
										<tr>
											<td><img src="pics/accepted.png" width="16" height="16" border="0" alt="accepted" title="accepted"></td>
											<td><div id="td_click_accepted_'.$thisproid.'">'.$click_stats["total_accepted"].' ('.$click_accept_percent.'%)</div></td>
										</tr>
										<tr>
											<td><img src="pics/declined.png" width="16" height="16" border="0" alt="declined" title="declined"></td>
											<td><div id="td_click_declined_'.$thisproid.'">'.$click_stats["total_declined"].' ('.$click_declined_percent.'%)</div></td>
										</tr>
										</table>
									</div>
								</td>
							</tr></table>
						</td>
						<td class="td_dept_td" align="center">'.$onoff.'</td>
						<td class="td_dept_td" align="center">'.$profile_image.'</td>
						<td class="td_dept_td">on same page '.$duration.' <div style="margin-top: 10px; text-shadow: none;">'.$andor.' '.$footprints.' footprints</div>
							<div style="margin-top: 15px;" class="info_neutral">click behavior: '.$click_behavior.'</div>'.$exclude_string.'</td>
						<td class="td_dept_td" align="center">'.$device_string.'</td>
						<td class="td_dept_td">'.$reset.'</td>
					</tr>
					' ; ++$array_index ;
				}
				else
				{
					// ProAction invite department ID no longer exists (deleted)
				}
			}
		?>
		</tbody>
		</table>
	</div>
</body>
</html>
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

	if ( !isset( $CONF['SQLTYPE'] ) ) { $CONF['SQLTYPE'] = "SQL.php" ; }
	else if ( $CONF['SQLTYPE'] == "mysql" ) { $CONF['SQLTYPE'] = "SQL.php" ; }

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/
	/* AUTO PATCH */
	if ( !is_file( "$CONF[CONF_ROOT]/patches/$patch_v" ) )
	{
		$query = isset( $_SERVER["QUERY_STRING"] ) ? Util_Format_Sanatize( $_SERVER["QUERY_STRING"], "query" ) : "" ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER( "location: ../patch.php?from=setup&".$query."&" ) ; exit ;
	}

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/get_ext.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$init = Util_Format_Sanatize( Util_Format_GetVar( "init" ), "n" ) ;
	if ( $admininfo["status"] == -1 ) { $init = 0 ; }
	$error = "" ;
	$theme = "default" ;

	Ops_update_itr_IdleOps( $dbh ) ;
	// double check safe measure
	$vars = Util_Format_Get_Vars( $dbh ) ;
	if ( !isset( $vars["code"] ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Vars/update.php" ) ;
		Vars_update_Var( $dbh, "code", 0 ) ;
	}

	$departments = Depts_get_AllDepts( $dbh ) ;
	$operators = Ops_get_AllOps( $dbh, 1 ) ;
	$t_transcripts = Chat_ext_get_TotalTranscript( $dbh ) ;
	$query = "SELECT SUM(rateit) AS rateit, SUM(ratings) AS ratings FROM p_rstats_depts" ;
	database_mysql_query( $dbh, $query ) ;
	$data = database_mysql_fetchrow( $dbh ) ;
	$t_rating = ( isset( $data["rateit"] ) && $data["rateit"] ) ? round( $data["ratings"]/$data["rateit"] ) : 0 ;
	$t_rating = Util_Functions_Stars( "..", $t_rating ) ;
	$t_requests = Chat_ext_get_AllRequests( $dbh, 0 ) ;
	$is_assigned = Ops_get_TotalOpsAssigned( $dbh ) ;
	$global_default_logo = Util_Upload_GetLogo( "logo", 0 ) ;
	$timezone_array = explode( "/", $CONF["TIMEZONE"] ) ; 
	$timezone = end( $timezone_array ) ;

	$message = Util_Format_Sanatize( Util_Format_GetVar( "message" ), "ln" ) ;

	$pr = Util_Format_Sanatize( Util_Format_GetVar( "pr" ), "n" ) ;
	if ( $pr ) { database_mysql_close( $dbh ) ; HEADER( "location: settings.php?jump=profile&pr=$pr&init=$init" ) ; exit ; }

	$total_operators = 0 ;
	$operators_hash = Array() ;
	for ( $c = 0; $c < count( $operators ); ++$c )
	{
		$operator = $operators[$c] ;
		if ( $operator["login"] != "phplivebot" )
		{
			$operators_hash[$operator["opID"]] = $operator["name"] ;
			++$total_operators ;
		}
	}
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		Chat_remove_ExpiredTranscript( $dbh, $department["deptID"], $department["texpire"] ) ;
	}

	$ips = isset( $VALS['CHAT_SPAM_IPS'] ) ? explode( "-", $VALS['CHAT_SPAM_IPS'] ) : Array() ; $ips_spam = 0 ;
	for ( $c = 0; $c < count( $ips ); ++$c )
	{
		if ( $ips[$c] ) { ++$ips_spam ; }
	}

	$created = date( "M j, Y", $admininfo["created"] ) ;
	$diff = time() - $admininfo["created"] ; $days_running = round( $diff/(60*60*24) ) ;

	$m = date( "m", $now ) ;
	$d = date( "j", $now ) ;
	$y = date( "Y", $now ) ;
	$stat_end = mktime( 0, 0, 1, $m, $d, $y ) ;
	$stat_end_day = date( "j", $stat_end ) ;

	$now_start = $now - (60*60*24*15) ;
	$m = date( "m", $now_start ) ;
	$d = date( "j", $now_start ) ;
	$y = date( "Y", $now_start ) ;
	$stat_start = mktime( 0, 0, 1, $m, $d, $y ) ;
	$stat_start_day = date( "j", $stat_start ) ;

	$requests_timespan = Chat_get_ext_RequestsRangeHash( $dbh, $stat_start, $stat_end, $operators, 0 ) ;

	$month_stats = Array() ;
	$month_total_requests = $month_total_taken = $month_total_declined = $month_total_message = $month_total_initiated = 0 ;
	$month_max_chat = 0 ;
	foreach ( $requests_timespan as $sdate => $deptop )
	{
		// todo: filter for invalid dates (should be fixed with timezone reset)
		if ( isset( $deptop["depts"] ) )
		{
			foreach ( $deptop["depts"] as $key => $value )
			{
				if ( !isset( $month_stats[$sdate] ) )
				{
					$month_stats[$sdate] = Array() ;
					$month_stats[$sdate]["requests"] = $month_stats[$sdate]["taken"] = $month_stats[$sdate]["declined"] = $month_stats[$sdate]["message"] = $month_stats[$sdate]["initiated"] = 0 ;
				}

				$month_stats[$sdate]["requests"] += $value["requests"] ;
				$month_stats[$sdate]["taken"] += $value["taken"] ;
				$month_stats[$sdate]["declined"] += $value["declined"] ;
				$month_stats[$sdate]["message"] += $value["message"] ;
				$month_stats[$sdate]["initiated"] += $value["initiated"] ;

				if ( $sdate )
				{
					$month_total_requests += $value["requests"] ;
					$month_total_taken += $value["taken"] ;
					$month_total_declined += $value["declined"] ;
					$month_total_initiated += $value["initiated"] ;
					$month_total_message += $value["message"] ;
				}

				$rating = ( $value["rateit"] ) ? round( $value["ratings"]/$value["rateit"] ) : 0 ;
			}
		}
		if ( isset( $deptop["ops"] ) )
		{
			foreach ( $deptop["ops"] as $key => $value )
			{
				$rating = ( $value["rateit"] ) ? round( $value["ratings"]/$value["rateit"] ) : 0 ;
			}
		}

		if ( isset( $month_stats[$sdate]["requests"] ) && ( $month_stats[$sdate]["requests"] > $month_max_chat ) && $sdate )
			$month_max_chat = $month_stats[$sdate]["requests"] ;
	}

	if ( !isset( $CONF['API_KEY'] ) ) { $CONF['API_KEY'] = Util_Format_RandomString( 10 ) ; Util_Vals_WriteToConfFile( "API_KEY", $CONF['API_KEY'] ) ; }
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
	var st_rd ;
	var global_c_chat ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;

		init_menu() ;
		toggle_menu_setup( "home" ) ;

		$('#div_greeting').show() ;

		<?php if ( $action === "success" ): ?>do_alert( 1, "<?php echo ( $message ) ? $message : 'Success' ; ?>" ) ;<?php endif ; ?>

		<?php if ( $init ): ?>
		$('#div_body_wrapper').hide() ;
		$('body').css({'overflow':'hidden'}) ;
		$('#div_init').fadeIn("slow") ;
		<?php endif ; ?>

	});

	function launch_tools_op_status()
	{
		var url = "tools_op_status.php" ;

		if ( <?php echo $total_operators ?> > 0 )
			External_lib_PopupCenter( url, "Status", 650, 550, "scrollbars=yes,menubar=no,resizable=1,location=no,width=650,height=550,status=0" ) ;
		else
		{
			if ( confirm( "Operator account does not exist.  Add an operator?" ) )
				location.href = "ops.php" ;
		}
	}

	function remote_disconnect( theopid, thelogin )
	{
		if ( typeof( st_rd ) != "undefined" ) { do_alert( 0, "Another disconnect in progress." ) ; return false ; }

		if ( confirm( "Remote disconnect operator console ("+thelogin+")?" ) )
		{
			var json_data = new Object ;

			$('#op_login').html( thelogin ) ;
			$('#remote_disconnect_notice').center().show() ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=remote_disconnect&opid="+theopid+"&"+unixtime(),
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
						check_op_status( theopid ) ;
					else
					{
						$('#remote_disconnect_notice').hide() ;
						do_alert( 0, "Could not remote disconnect console.  Please try again." ) ;
					}
				}
			});
		}
	}

	function check_op_status( theopid )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( typeof( st_rd ) != "undefined" ) { clearTimeout( st_rd ) ; }

		$.ajax({
		type: "POST",
		url: "../wapis/status_op.php",
		data: "opid="+theopid+"&jkey=<?php echo md5( $CONF['API_KEY'] ) ?>&"+unique,
		success: function(data){
			eval( data ) ;

			if ( !parseInt( json_data.status ) )
				location.href = 'index.php?action=success&'+unique ;
			else
				st_rd = setTimeout( function(){ check_op_status( theopid ) ; }, 2000 ) ;
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Lost connection to server.  Please refresh the page and try again." ) ;
		} });
	}

	function show_div( thediv )
	{
		var divs = Array( "operator", "setup" ) ;

		for ( var c = 0; c < divs.length; ++c )
		{
			$('#login_'+divs[c]).hide() ;
			$('#menu_url_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('#login_'+thediv).show() ;
		$('#menu_url_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
	}

	function select_date_chat( theunix, thedayexpand, thetotal, thec, theincro )
	{
		$('#tr_requests').find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf("bar_v_requests_") != -1 )
				$(this).css({'border': '1px solid #4FD25B'}) ;
		} );

		if ( typeof( thetotal ) == "undefined" ) { var thetotal = 0 ; }

		if ( global_c_chat == thec )
		{
			global_c_chat = undeefined ;
			$('#stat_day_expand_chat').html( "" ) ;
		}
		else
		{
			global_c_chat = thec ;
			$('#stat_day_expand_chat').html( "<span class=\"info_neutral\" style=\"font-weight: bold;\">"+thedayexpand+"</span> &nbsp; Chat Requests: "+thetotal ) ;
			if ( typeof( thec ) != "undefined" ) { $('#bar_v_requests_'+thec).css({'border': '1px solid #235D28'}) ; }
		}
	}

	function update_timezone()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		var timezone = $('#timezone').val() ;
		var format = $('#timeformat_12').prop('checked') ? 12 : 24 ;

		if ( timezone )
		{
			if ( confirm( "Update timezone to "+timezone+"?" ) )
			{
				$.ajax({
				type: "POST",
				url: "../ajax/setup_actions_.php",
				data: "action=update_timezone&timezone="+timezone+"&format="+format+"&"+unique,
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
					{
						location.href = "index.php?action=success&message=Timezone+Updated" ;
					}
					else
					{
						do_alert( 0, json_data.error ) ;
					}
				},
				error:function (xhr, ajaxOptions, thrownError){
					do_alert( 0, "Lost connection to server.  Please refresh the page and try again." ) ;
				} });
			}
		}
		else
			do_alert( 0, "Timezone must be selected." ) ;
	}
//-->
</script>
</head>

<?php include_once( "./inc_header.php" ) ; ?>

		<div style="" id="div_body_wrapper">	
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td valign="top">

					<div class="home_box info_neutral" style="width: 210px; margin-left: 0px;">
						<div style="padding: 10px; padding-top: 0px;"><a href="interface.php?jump=logo"><img src="<?php echo $global_default_logo ?>" style="max-width: 100%; max-height: 150px;" border=0 class="round"></a></div>
						<hr>
						<div style="padding: 10px;">
							<table cellspacing=0 cellpadding=0 border=0>
							<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["trans"] ) ) ) ? "display: none;" : "" ; ?>">
								<td style="padding-top: 10px; padding-bottom: 10px;">Total Transcripts</td>
								<td style="padding-left: 5px; padding-top: 10px; padding-bottom: 10px;"><span class="info_neutral" onClick="location.href='transcripts.php'" style="cursor: pointer;"><b><?php echo $t_transcripts ?></b></span></td>
							</tr>
							<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ) ? "display: none;" : "" ; ?>">
								<td style="padding-top: 10px; padding-bottom: 10px;">Overall Rating</td>
								<td style="padding-left: 5px; padding-top: 10px; padding-bottom: 10px;"><a href="reports_chat.php"><?php echo $t_rating ?></a></td>
							</tr>
							<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ) ? "display: none;" : "" ; ?>">
								<td style="padding-top: 10px;">Timezone</td>
								<td style="padding-left: 5px; padding-top: 10px;"><a href="interface.php?jump=time"><?php echo $timezone ?></a></td>
							</tr>
							</table>
						</div>
						<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 10px;"></div>

						<div style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["depts"] ) ) ) ? "display: none;" : "" ; ?> padding: 10px; padding-bottom: 5px;"><img src="../pics/icons/depts.png" width="16" height="16" border="0" alt=""> <a href="depts.php">Chat Departments (<?php echo count( $departments ) ?>)</a></div>

						<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 10px;"></div>

						<div style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>">
							<div style="padding: 10px; padding-bottom: 0px;" class="title"><img src="../pics/icons/ops.png" width="16" height="16" border="0" alt=""> <a href="ops.php">Chat Operators (<?php echo $total_operators ?>)</a></div>
							<div style="padding: 10px; max-height: 200px; overflow: auto;">
								<?php
									for ( $c = 0; $c < count( $operators ); ++$c )
									{
										$operator = $operators[$c] ;
										$login = $operator["login"] ;
										if ( $operator["login"] != "phplivebot" )
										{
											$name = ( strlen( $operator["name"] ) < 18 ) ? $operator["name"] : substr( $operator["name"], 0, 18 )."..." ;
											$name_display = "<span alt='login: $login' title='login: $login'>$name</span>" ;
											$mapp_online_icon = ( $operator["mapp"] ) ? " <img src=\"../pics/icons/mobile.png\" width=\"10\" height=\"10\" border=\"0\" alt=\"logged in on mobile\" title=\"logged in on mobile\" style=\"cursor: help;\">" : "" ;
											$status_img = ( $operator["status"] ) ? "<img src=\"../pics/icons/bulb.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"online\" title=\"online\">$mapp_online_icon" : "<img src=\"../pics/icons/bulb_off.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"offline\" title=\"offline\">" ;

											print "<div style=\"margin-bottom: 5px;\">$status_img $name_display</div>" ;
										}
									}
								?>
							</div>
						</div>

						<div style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["settings"] ) ) ) ? "display: none;" : "" ; ?> margin-top: 15px;">
							<div style="padding: 10px;" class="info_neutral">
								<a href="system.php">Software Version</a>
								<div style="margin-top: 5px;">v.<?php echo $VERSION ?></div>
							</div>

							<div style="margin-top: 15px; padding: 10px; text-align: justify;">
								For helpful information, how-to and answers to common issues, please visit the <a href="https://www.phplivesupport.com/r.php?r=help" target="_blank">Knowledge Base</a>.
							</div>
						</div>
					</div>

				</td>
				<td valign="top" width="100%">
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td width="300" valign="top">

							<div id="home_box_start" class="home_box" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && ( !isset( $admininfo["access"]["depts"] ) && !isset( $admininfo["access"]["ops"] ) && !isset( $admininfo["access"]["code"] ) ) ) ) ? "display: none;" : "" ; ?> width: 280px;">
								<div class="edit_title round_top" style="margin-right: 0px; margin-bottom: 0px; padding: 15px; padding-left: 20px; background: #FFFFFF; border-bottom: 0px; text-shadow: none; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);">
									<div style="">Getting Started</div>
								</div>
								<div style="background: url( ../pics/bg_setup.png ) no-repeat #5CAED6; background-position: bottom right; padding: 15px; box-shadow: -2px 0 16px 1px rgba(0,0,0,.1);" class="round_bottom">
									<table cellspacing=0 cellpadding=7 border=0>
									<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["depts"] ) ) ) ? "display: none;" : "" ; ?>">
										<td><img src="../pics/icons/arrow_right.png" width="16" height="15" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
										<td><a href="depts.php" style="color: #FFFFFF;">Add Chat Department</a></td>
									</tr>
									<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>">
										<td><img src="../pics/icons/arrow_right.png" width="16" height="15" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
										<td><a href="ops.php" style="color: #FFFFFF;">Add Chat Operator</a></td>
									</tr>
									<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>">
										<td><img src="../pics/icons/arrow_right.png" width="16" height="15" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
										<td><a href="ops.php?jump=assign" style="color: #FFFFFF;">Assign Operator to Department</a></td>
									</tr>
									<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["code"] ) ) ) ? "display: none;" : "" ; ?>">
										<td><img src="../pics/icons/arrow_right.png" width="16" height="15" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
										<td><a href="code.php" style="color: #FFFFFF;">Copy HTML Code</a></td>
									</tr>
									<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>">
										<td><img src="../pics/icons/arrow_right.png" width="16" height="15" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
										<td><a href="ops.php?jump=online" style="color: #FFFFFF;">Go <span style="font-weight: bold;">ONLINE!</span></a></td>
									</tr>
									</table>
								</div>
							</div>

						</td>
						<td valign="top">
							
							<div class="home_box">
								<table cellspacing=0 cellpadding=0 border=0>
								<tr>
									<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ) ? "display: none;" : "" ; ?>">
										<div class="op_submenu" style="width: 100px; box-shadow: 0 3px 2px -2px #D5D8D9;" onClick="location.href='interface.php?jump=logo'">Interface</div>
										<div style="clear: both;"></div>
										<div style="padding: 10px; margin-top: 5px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="interface.php">Logo</a></div>
										<div style="padding: 10px; margin-top: 5px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="interface_themes.php">Theme</a></div>
										<div style="padding: 10px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="interface_custom.php">Form Fields</a></div>
										<div style="padding: 10px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="interface_lang.php">Update Texts</a></div>
									</td>
									<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 25px;">
										<div class="op_submenu" style="width: 100px; box-shadow: 0 3px 2px -2px #D5D8D9;" onClick="location.href='reports_chat.php'">Reports</div>
										<div style="clear: both;"></div>
										<div style="padding: 10px; margin-top: 5px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="reports_chat.php">Chat Reports</a></div>
										<div style="padding: 10px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="reports_chat_active.php">Active Chats (<?php echo count( $t_requests ) ?>)</a></div>
										<div style="padding: 10px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="reports_chat_missed.php">Missed Chats</a></div>
										<div style="padding: 10px;"><img src="../pics/icons/arrow_grey.png" width="16" height="16" border="0" alt=""> <a href="reports_chat_msg.php">Offline Messages</a></div>
									</td>
								</tr>
								<tr style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>">
									<td colspan=2 style="padding-top: 15px;">
										<div class="info_neutral" style="padding: 15px; text-align: center;"><a href="JavaScript:void(0)" onClick="launch_tools_op_status()">Operator Status Monitor</a></div>
									</td>
								</tr>
								</table>
							</div>

						</td>
					</tr>
					<tr>
						<td valign="top" colspan=2 style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ) ? "display: none;" : "" ; ?> padding-top: 25px;">

							<div class="home_box" style="border-top: 1px dashed #F4F6F8;">
								<div style="padding-top: 15px; font-size: 14px;"><img src="../pics/icons/calendar.png" width="16" height="16" border="0" alt=""> Recent 15 Day Chat Stat [ <a href="reports_chat.php">view full reports</a> ] &nbsp; <span id="stat_day_expand_chat"></span></div>
								<div style="margin-top: 15px;">
									<table cellspacing=0 cellpadding=0 border=0 style="height: 100px;" width="100%">
									<tr id="tr_requests">
										<?php
											$tooltips = Array() ; $stat_day_totals = Array() ; $incro = 1 ;
											while ( $incro <= 15 )
											{
												$m = date( "m", $stat_start ) ;
												$d = date( "j", $stat_start ) ;
												$y = date( "Y", $stat_start ) ;
												$stat_day = mktime( 0, 0, 1, $m, $d+$incro, $y ) ;

												$stat_day_expand = date( "l, M j, Y", $stat_day ) ;
												$c = date( "j", $stat_day ) ;

												$h1 = "0px" ; $meter = "meter_v_green.gif?$VERSION" ;
												$tooltip = "$stat_day_expand" ;
												$tooltips[$stat_day] = "$tooltip (Total: 0)" ;

												$meter_shadow = " box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2); " ;
												if ( isset( $month_stats[$stat_day] ) )
												{
													$stat_day_totals[$c] = $month_stats[$stat_day]["requests"] ;
													if ( $month_max_chat )
														$h1 = round( ( $month_stats[$stat_day]["requests"]/$month_max_chat ) * 100 ) . "px" ;
													$tooltips[$stat_day] = "$tooltip (Total: ".$stat_day_totals[$c].")" ;
												}
												else
													$stat_day_totals[$c] = 0 ;

												print "
													<td valign=\"bottom\" style=\"width: 30px; height: 100px;\"><div id=\"bar_v_requests_$c\" title=\"".$tooltips[$stat_day]."\" alt=\"".$tooltips[$stat_day]."\" style=\"height: $h1; background: url( ../pics/meters/$meter ) repeat-y; border: 1px solid #4FD25B; border-top-left-radius: 5px 5px; border-top-right-radius: 5px 5px; cursor: pointer; $meter_shadow\" OnClick=\"select_date_chat( $stat_day, '$stat_day_expand', '".$stat_day_totals[$c]."', $c, $incro );\">&nbsp;</div></td>
													<td><img src=\"../pics/space.gif\" width=\"3\" height=1 border=0></td>
												" ;
												++$incro ;
											}
										?>
									</tr>
									<tr>
										<?php
											$incro = 1 ;
											while ( $incro <= 15 )
											{
												$m = date( "m", $stat_start ) ;
												$d = date( "j", $stat_start ) ;
												$y = date( "Y", $stat_start ) ;
												$stat_day = mktime( 0, 0, 1, $m, $d+$incro, $y ) ;

												$stat_day_expand = date( "l, M j, Y", $stat_day ) ;
												$c = date( "j", $stat_day ) ;
												print "
													<td align=\"center\"><div id=\"requests_bg_day\" class=\"page_report\" style=\"width: 30px; margin: 0px; font-size: 10px; font-weight: bold;\" title=\"$tooltips[$stat_day]\" id=\"$tooltips[$stat_day]\" OnClick=\"select_date_chat( $stat_day, '$stat_day_expand', '".$stat_day_totals[$c]."', $c, $incro );\">$c</div></td>
													<td><img src=\"../pics/space.gif\" width=\"3\" height=1 border=0></td>
												" ;
												++$incro ;
											}
										?>
									</tr>
									<tr>
										<?php
											$incro = 1 ;
											while ( $incro <= 15 )
											{
												$m = date( "m", $stat_start ) ;
												$d = date( "j", $stat_start ) ;
												$y = date( "Y", $stat_start ) ;
												$stat_day = mktime( 0, 0, 1, $m, $d+$incro, $y ) ;

												$stat_day_expand = date( "l, M j, Y", $stat_day ) ;
												$c = date( "j", $stat_day ) ;
												$total = $stat_day_totals[$c] ;

												print "
													<td align=\"center\"><div id=\"requests_bg_total_$c\" class=\"info_clear\" style=\"margin: 0px; font-size: 10px; font-weight: bold;\">$total</div></td>
													<td><img src=\"../pics/space.gif\" width=\"3\" height=1 border=0></td>
												" ;
												++$incro ;
											}
										?>
									</tr>
									</table>
								</div>

								<?php if ( $ips_spam ): ?>
								<div class="info_warning" style="margin-top: 25px;"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../pics/icons/bullet_red.png" width="12" height="12" border="0" alt=""></td><td style="padding-left: 5px;"> There are <b><a href="settings.php?jump=sips"><?php echo $ips_spam ?> blocked IPs</a></b> from chat access.  Blocked IPs will always see an offline chat icon.</td></tr></table></div>
								<?php endif ; ?>

							</div>

						</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>
		</div>

		<div id="remote_disconnect_notice" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background: url( ../pics/bg_trans_white.png ) repeat; overflow: hidden; z-index: 20;">
			<div style="padding-top: 300px; text-align: center;"><span class="info_error" style="">Disconnecting console [ <span id="op_login"></span> ].  Just a moment... <img src="../pics/loading_fb.gif" width="16" height="11" border="0" alt=""></span></div>
		</div>

<?php
	if ( $init ):
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Hash.php" ) ;
	$timezones = Util_Hash_Timezones() ;
?>
<div id="div_init" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; padding-top: 80px; z-index: 50; background: url(../themes/initiate/bg_trans_darker.png) repeat;">
	<div class="info_info" style="width: 500px; height: 350px; margin: 0 auto; padding: 10px; text-shadow: 1px 1px #FFFFFF;">

		<div class="td_dept_td noshadow">
			<div class="edit_title">Update Timezone</div>
			<div style="margin-top: 5px; text-align: justify;">
				Before continuing, update the timezone for your system.
				<div style="margin-top: 5px;" class="info_warning">
					<table cellspacing=0 cellpadding=0 border-0>
					<tr>
						<td><img src="../pics/icons/alert.png" width="16" height="16" border="0" alt=""></td>
						<td style="padding-left: 5px;">Please double check the timezone selection.  Updating the timezone again will reset the chat stats data (total accepted, total declined, total chats missed, etc).</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		
		<div>
			<table cellspacing=0 cellpadding=0 border=0>
			<tr> 
				<td class="td_dept_td noshadow" width="120">Timezone</td> 
				<td class="td_dept_td noshadow">
					<select id="timezone">
					<option value=""></option>
					<?php
						for ( $c = 0; $c < count( $timezones ); ++$c )
						{
							print "<option value=\"$timezones[$c]\">$timezones[$c]</option>" ;
						}
					?>
					</select>
				</td> 
			</tr>
			<tr>
				<td class="td_dept_td noshadow">Time Format</td>
				<td class="td_dept_td noshadow">
					<div style="">
						<span class="info_neutral" style="margin-left: 5px; cursor: pointer;" onclick="$('#timeformat_12').prop('checked', true);"><input type="radio" id="timeformat_12" name="timeformat_12" value="24" <?php echo ( !$VARS_24H ) ? "checked" : "" ; ?>> 12h</span>
						<span class="info_neutral" style="margin-left: 5px; cursor: pointer;" onclick="$('#timeformat_24').prop('checked', true);"><input type="radio" id="timeformat_24" name="timeformat_12" value="24" <?php echo ( $VARS_24H ) ? "checked" : "" ; ?>> 24h</span>
					</div>
				</td>
			</tr>
			<tr>
				<td class="td_dept_td noshadow">&nbsp;</td>
				<td class="td_dept_td noshadow">
					<input type="hidden" class="input" size="35" id="password" value="1">
					<button type="button" onClick="update_timezone()" class="btn">Update Timezone</button>
				</td>
			</tr>
			</table>
		</div>

	</div>
</div>
<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ; ?>
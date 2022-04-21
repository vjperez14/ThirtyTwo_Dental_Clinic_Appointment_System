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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$page = Util_Format_Sanatize( Util_Format_GetVar( "page" ), "n" ) ;
	$index = Util_Format_Sanatize( Util_Format_GetVar( "index" ), "n" ) ;
	$m = Util_Format_Sanatize( Util_Format_GetVar( "m" ), "n" ) ;
	$d = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
	$y = Util_Format_Sanatize( Util_Format_GetVar( "y" ), "n" ) ;
	$created_timestamp = Util_Format_TableFirstCreated( $dbh, "p_req_log" ) ;
	$y_start = date( "Y", $created_timestamp ) ;
	$cal_year = date( "Y", time() ) ;
	$c_start = ( isset( $y_start ) ) ? $y_start : 2010 ;

	$year = $y ; // used for the page generation function Util_Functions_Page

	$departments = Depts_get_AllDepts( $dbh ) ;
	$operators = Ops_get_AllOps( $dbh ) ;
	$t_requests = Chat_ext_get_AllRequests( $dbh, 0 ) ;
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
	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#E4EBF3'}) ;
		init_menu() ;
		toggle_menu_setup( "rchats" ) ;
	});

	function select_day()
	{
		$('#div_freev').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
	}

	function switch_dept()
	{
		$('#div_freev').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='reports_chat.php'">Chat Reports</div>
			<div class="op_submenu" onClick="location.href='reports_chat_active.php'">Active Chats (<?php echo count( $t_requests ) ?>)</div>
			<div class="op_submenu_focus">Missed Chats</div>
			<div class="op_submenu" onClick="location.href='reports_chat_msg.php'">Offline Messages</div>
			<!-- <div class="op_submenu" onClick="location.href='reports_chat_queue.php'">Waiting Queue</div> -->
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<form method="POST" action="reports_chat_active.php" id="form_theform">
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td>
					<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this )">
					<option value="0">All Departments</option>
					<?php
						for ( $c = 0; $c < count( $departments ); ++$c )
						{
							$department = $departments[$c] ;

							if ( $department["name"] != "Archive" )
							{
								$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
								print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
							}
						}
					?>
					</select>
				</td>
				<td align="right">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td style="padding-left: 15px;">
						<select class="select_calendar" id="day_month">
							<option value="0"></option>
							<?php
								for( $c = 1; $c <= 12; ++$c )
								{
									$selected = ( $c == $m ) ? "selected" : "" ;
									print "<option value=\"$c\" $selected>".date("F", mktime( 0, 0, 1, $c, 1, 2010 ))."</option>" ;
								}
							?>
						</select>
						<select class="select_calendar" id="day_year">
							<option value="0"></option>
							<?php
								for( $c = $c_start; $c <= $cal_year; ++$c )
								{
									$selected = ( $c == $y ) ? "selected" : "" ;
									print "<option value=\"$c\" $selected>$c</option>" ;
								}
							?>
						</select> <button type="button" onClick="select_day();" id="btn_submit_cal" class="btn">submit</button>
					</td>
					</tr>
					</table>
				</td>
			</tr>
			</table>

			<div style="padding-top: 15px;">
				<div class="edit_title">Missed Chats Icon Descriptions</div>
				<div style=""><img src="../pics/icons/bullet_red.png" width="16" height="16" border="0" alt=""> Chat request was abandoned (dropped) by the visitor or the internet disconnected during the routing process.  The request did not complete the normal routing cycle.</div>
				<div style="margin-top: 5px;"><img src="../pics/icons/bullet_orange.png" width="16" height="16" border="0" alt=""> The chat request was cancelled by the visitor during the routing cycle.  The request routed to the leave a message.</div>
				<div style="margin-top: 5px;"><img src="../pics/icons/bullet_blue.png" width="16" height="16" border="0" alt=""> Chat request completed the routing cycle to all department operators but the request was declined by all department operators.  The request routed to the leave a message.</div>
				<div style="margin-top: 5px;"><img src="../pics/icons/bullet_purple.png" width="16" height="16" border="0" alt=""> "Operator Initiated Chat Invite".  The operator initiated a chat invite to the visitor but the invite was declined by the visitor or the visitor did not take action.</div>
				<div style="margin-top: 5px;"><img src="../pics/icons/email.png" width="16" height="16" border="0" alt=""> The visitor <a href="reports_chat_msg.php">left a message</a>.</div>

				<div style="margin-top: 25px;" id="div_freev"><?php include_once( "./inc_freev.php" ) ; ?></div>
			</div>

<?php include_once( "./inc_footer.php" ) ?>

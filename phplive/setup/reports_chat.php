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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;

	$error = "" ; $now = time() ;
	$theme = "default" ;
	$deptinfo = $opinfo = Array() ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$ftab = Util_Format_Sanatize( Util_Format_GetVar( "ftab" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
	$m = Util_Format_Sanatize( Util_Format_GetVar( "m" ), "n" ) ;
	$d = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
	$y = Util_Format_Sanatize( Util_Format_GetVar( "y" ), "n" ) ;
	if ( !$m ) { $m = date( "m", $now ) ; }
	if ( !$d ) { $d = date( "j", $now ) ; }
	if ( !$y ) { $y = date( "Y", $now ) ; }
	$y_start = date( "Y", Util_Format_TableFirstCreated( $dbh, "p_req_log" ) ) ;

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
	"use strict"

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#E4EBF3'}) ;
		init_menu() ;
		toggle_menu_setup( "rchats" ) ;

		<?php if ( $action && !$error ): ?>do_alert(1, "Success") ;<?php endif ; ?>
	});
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu_focus" style="margin-left: 0px;">Chat Reports</div>
			<div class="op_submenu" onClick="location.href='reports_chat_active.php'">Active Chats (<?php echo count( $t_requests ) ?>)</div>
			<div class="op_submenu" onClick="location.href='reports_chat_missed.php'">Missed Chats</div>
			<div class="op_submenu" onClick="location.href='reports_chat_msg.php'">Offline Messages</div>

			<div style="float: right">
				<div class="info_white" style="display: inline-block;"><img src="../pics/icons/clock.png" width="16" height="16" border="0" alt=""> <a href="interface.php?jump=time" target="_parent">current system time</a> is <span id="span_system_time" style="color: #3A89D1;  font-weight: bold;"><?php echo date( "M j ($VARS_TIMEFORMAT)", time() ) ; ?></span></div>
			</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			View total chat requests and the accepted, declined and chat request timeline activity for each month, day and hour.  The chat report is available for each operator and department.  Average chat session duration and average time to accept a chat request is also available for each operator and department.
		</div>

		<div style="margin-top: 25px;"><?php include_once( "./inc_freev.php" ) ; ?></div>

<?php include_once( "./inc_footer.php" ) ?>


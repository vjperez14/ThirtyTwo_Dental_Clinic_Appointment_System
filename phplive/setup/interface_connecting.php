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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;
	$error = "" ;

	$departments = Depts_get_AllDepts( $dbh ) ;

	// set the $deptid based on visible or not visible availability
	if ( !$deptid )
	{
		if ( count( $departments ) ) { $deptid = $departments[0]["deptID"] ; }
	}

	if ( $action === "update" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

		$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;
		$message = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "message" ), "" ) ) ;
		$message_busy = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "message_busy" ), "" ) ) ;

		$table_name = "msg_greet" ;

		if ( !$message )
			$error = "Blank input is invalid.  Message has been reset." ;
		else
		{
			if ( $copy_all )
			{
				for( $c = 0; $c < count( $departments ); ++$c )
				{
					Depts_update_DeptValue( $dbh, $departments[$c]["deptID"], $table_name, $message ) ;
				}
			}
			else
			{
				Depts_update_DeptValue( $dbh, $deptid, $table_name, $message ) ;
			}
		}
	}

	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
	$deptname = "" ; $message = "" ;
	if ( isset( $deptinfo["deptID"] ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($deptinfo["lang"], "ln").".php" ) ;
		$deptname = $deptinfo["name"] ;

		$message = $deptinfo["msg_greet"] ;
	}
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
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "interface" ) ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>
	});

	function switch_dept( theobject )
	{
		var unique = unixtime() ;
		location.href = "interface_connecting.php?deptid="+theobject.value ;
	}

	function do_submit()
	{
		var message = $('#message').val().trim() ;

		if ( !message )
			do_alert( 0, "Blank message is invalid." ) ;
		else
			$('#theform').submit() ;
	}

	function do_reset()
	{
		$('#theform').trigger("reset") ;
		$('#btn_reset').hide() ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" onClick="location.href='interface.php?jump=logo'" style="margin-left: 0px;">Logo</div>
			<div class="op_submenu" onClick="location.href='interface_themes.php'" id="menu_themes">Theme</div>
			<div class="op_submenu" onClick="location.href='interface_custom.php'" id="menu_custom">Form Fields</div>
			<div class="op_submenu_focus" id="menu_lang">Update Texts</div>
			<div class="op_submenu" onClick="location.href='code_autostart.php'" id="menu_auto">Automatic Start Chat</div>
			<div class="op_submenu" onClick="location.href='interface_gdpr.php'" id="menu_gdpr">Consent Checkbox</div>
			<div class="op_submenu" onClick="location.href='interface_chat_msg.php'">Chat End Msg</div>
			<?php if ( phpversion() >= "5.1.0" ): ?><div class="op_submenu" onClick="location.href='interface.php?jump=time'">Timezone</div><?php endif; ?>
			<div class="op_submenu" onClick="location.href='code_settings.php'">Settings</div>
			<div style="clear: both"></div>
		</div>

		<?php if ( !count( $departments ) ): ?>
		<div style="padding-top: 25px;">
			<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span>
		</div>
		<?php else: ?>

		<div style="margin-top: 25px;">
			<div class="op_submenu3" style="margin-left: 0px;" onClick="location.href='interface_lang.php'">Chat Window Texts</div>
			<div class="op_submenu_focus">Connecting Text</div>
			<div class="op_submenu3" onClick="location.href='interface_offline.php'">Offline Texts</div>
			<div class="op_submenu3" onClick="location.href='interface_offline.php?jump=template'">"Leave a message" Email Template</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/marquee/marquee.php" ) ): ?><div class="op_submenu3" onClick="location.href='../addons/marquee/marquee.php'">Marquee Text</div><?php endif ; ?>
			<div style="clear: both"></div>
		</div>

		<form action="interface_connecting.php" id="theform" method="POST" accept-charset="<?php echo $LANG["CHARSET"] ?>">
		<input type="hidden" name="action" value="update">
		<div style="margin-top: 25px;">
			<div style="<?php echo ( count( $departments ) == 1 ) ? "display: none;" : "" ; ?>">
				<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this )">
				<?php
					for ( $c = 0; $c < count( $departments ); ++$c )
					{
						$department = $departments[$c] ;
						$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
						print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
					}
				?>
				</select>
			</div>

			<?php if ( $deptid ): ?>
				<div style="margin-top: 25px; text-align: justify;">
					When the visitor requests a chat session, display the following message to the visitor while the chat is connecting to an operator.
				</div>
				<div style="margin-top: 25px;"><input type="text" class="input" style="width: 95%" id="message" name="message" maxlength="455" value="<?php echo preg_replace( "/\"/", "&quot;", $message ) ?>" placeholder="<?php echo ( isset( $LANG ) && isset( $LANG["CHAT_NOTIFY_LOOKING_FOR_OP"] ) ) ? $LANG["CHAT_NOTIFY_LOOKING_FOR_OP"] : "ddAn agent will be with you shortly. Thank you for your patience." ; ?>" onKeyDown="$('#btn_reset').show()"></div>
				<div style="margin-top: 5px;">Pre-populated variables: <b>%%visitor%%</b> = visitor's name</div>

				<?php if ( count( $departments ) > 1 ) : ?>
				<div style="margin-top: 15px;"><input type="checkbox" id="copy_all" name="copy_all" value=1> copy this update to all departments</div>
				<?php endif ; ?>

				<div style="margin-top: 15px;"><input type="button" class="btn" onClick="do_submit()" value="Update"> &nbsp; <input type="button" style="display: none;" id="btn_reset" onClick="do_reset()" class="btn" value="Reset"></div>
			<?php endif ; ?>
		</div>
		</form>

		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>

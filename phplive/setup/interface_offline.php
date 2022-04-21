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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$error = Util_Format_Sanatize( Util_Format_GetVar( "error" ), "ln" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ; if ( !$jump ) { $jump = "offline" ; }
	$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	// set the $deptid based on visible or not visible availability
	if ( !$deptid )
	{
		if ( count( $departments ) ) { $deptid = $departments[0]["deptID"] ; }
	}
	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	if ( isset( $deptinfo["deptID"] ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($deptinfo["lang"], "ln").".php" ) ;
	}
	else
		include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($CONF['lang'], "ln").".php" ) ;

	if ( $action === "update" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/put.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

		$message = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "message" ), "" ) ) ;
		$message_busy = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "message_busy" ), "" ) ) ;
		$offline_form = Util_Format_Sanatize( Util_Format_GetVar( "offline_form" ), "n" ) ;
		$emailm_cc = Util_Format_Sanatize( Util_Format_GetVar( "emailm_cc" ), "e" ) ;
		if ( $emailm_cc == $deptinfo["email"] ) { $emailm_cc = "" ; }

		$template_subject = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "template_subject" ), "" ) ) ;
		$template_body = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "template_body" ), "" ) ) ;
		$offline_template = "$template_subject-_-$template_body" ;

		$MSG_LEAVE_MESSAGE = Util_Format_Sanatize( Util_Format_GetVar( "TXT_MSG_LEAVE_MESSAGE" ), "noscripts" ) ;

		$table_name = "msg_offline" ;

		if ( !$message )
		{
			$error = urlencode( "Blank input is invalid.  Message has been reset." ) ;
			$action = "" ;
		}
		else
		{
			if ( $copy_all )
			{
				for( $c = 0; $c < count( $departments ); ++$c )
				{
					if ( $jump == "offline" )
					{
						$lang_db_dept = Lang_get_Lang( $dbh, $departments[$c]["deptID"] ) ;
						$lang_vars = ( isset( $lang_db_dept["lang_vars"] ) && $lang_db_dept["lang_vars"] ) ? unserialize( $lang_db_dept["lang_vars"] ) : Array() ;
						if ( ( isset( $LANG["MSG_LEAVE_MESSAGE"] ) && ( $LANG["MSG_LEAVE_MESSAGE"] != $MSG_LEAVE_MESSAGE ) ) || ( isset( $lang_vars["MSG_LEAVE_MESSAGE"] ) && ( isset( $lang_db_dept["deptID"] ) && ( $lang_vars["MSG_LEAVE_MESSAGE"] != $MSG_LEAVE_MESSAGE ) ) ) )
						{
							$lang_vars["MSG_LEAVE_MESSAGE"] = $MSG_LEAVE_MESSAGE ;
							Lang_put_Lang( $dbh, $departments[$c]["deptID"], serialize( $lang_vars ) ) ;
						}
						Depts_update_DeptValues( $dbh, $departments[$c]["deptID"], Array( "emailm_cc"=>$emailm_cc, "msg_busy"=>$message_busy, $table_name=>$message ) ) ; usleep( 10000 ) ;
						Depts_update_DeptVarsValue( $dbh, $departments[$c]["deptID"], "offline_form", $offline_form ) ;
					}
					else
						Depts_update_DeptVarsValue( $dbh, $departments[$c]["deptID"], "offline_msg_template", $offline_template ) ;
				}
			}
			else
			{
				if ( $jump == "offline" )
				{
					$lang_db_dept = Lang_get_Lang( $dbh, $deptid ) ;
					$lang_vars = ( isset( $lang_db_dept["lang_vars"] ) && $lang_db_dept["lang_vars"] ) ? unserialize( $lang_db_dept["lang_vars"] ) : Array() ;
					if ( ( isset( $LANG["MSG_LEAVE_MESSAGE"] ) && ( $LANG["MSG_LEAVE_MESSAGE"] != $MSG_LEAVE_MESSAGE ) ) || ( isset( $lang_vars["MSG_LEAVE_MESSAGE"] ) && ( isset( $lang_db_dept["deptID"] ) && ( $lang_vars["MSG_LEAVE_MESSAGE"] != $MSG_LEAVE_MESSAGE ) ) ) )
					{
						$lang_vars["MSG_LEAVE_MESSAGE"] = $MSG_LEAVE_MESSAGE ;
						Lang_put_Lang( $dbh, $deptid, serialize( $lang_vars ) ) ;
					}
					Depts_update_DeptValues( $dbh, $deptid, Array( "emailm_cc"=>$emailm_cc, "msg_busy"=>$message_busy, $table_name=>$message ) ) ; usleep( 10000 ) ;
					Depts_update_DeptVarsValue( $dbh, $deptid, "offline_form", $offline_form ) ;
				}
				else
					Depts_update_DeptVarsValue( $dbh, $deptid, "offline_msg_template", $offline_template ) ;
			}
			$action = "success" ;
		}
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER( "location: interface_offline.php?action=$action&deptid=$deptid&jump=$jump&error=$error" ) ;
		exit ;
	}

	$deptname = $deptinfo["name"] ;
	$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;

	$lang_db = Lang_get_Lang( $dbh, $deptid ) ;
	if ( isset( $lang_db["deptID"] ) && $lang_db["deptID"] )
	{
		$db_lang_hash = unserialize( $lang_db["lang_vars"] ) ;
		$LANG = array_merge( $LANG, $db_lang_hash ) ;
	}

	$message = $deptinfo["msg_offline"] ;
	$offline_form = ( isset( $deptvars["offline_form"] ) ) ? $deptvars["offline_form"] : 1 ;

	include_once( "$CONF[DOCUMENT_ROOT]/examples/inc_default_vars.php" ) ;
	$template_subject = $DEFAULT_VAR_OFFLINE_TEMPLATE_SUBJECT ;
	$template_body = $DEFAULT_VAR_OFFLINE_TEMPLATE_BODY ;
	if ( isset( $deptvars["offline_msg_template"] ) && preg_match( "/-_-/", $deptvars["offline_msg_template"] ) )
	{	
		LIST( $template_subject, $template_body ) = explode( "-_-", $deptvars["offline_msg_template"] ) ;
	}
	$offline = ( isset( $VALS['OFFLINE'] ) && $VALS['OFFLINE'] ) ? unserialize( $VALS['OFFLINE'] ) : Array( ) ;
	if ( !isset( $offline[0] ) ) { $offline[0] = "embed" ; }
	if ( !isset( $offline[$deptid] ) ) { $offline[$deptid] = $offline[0] ; }
	$redirect_url = ( isset( $offline[$deptid] ) && !preg_match( "/^(icon|hide|embed|tab)$/", $offline[$deptid] ) ) ? $offline[$deptid] : "" ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=<?php echo $LANG["CHARSET"] ?>">
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

		show_div( "<?php echo $jump ?>" ) ;
	});

	function show_div( thediv )
	{
		$('.div_offline').hide() ;
		//$('#div_submenu2_wrapper').parent('div[class*=menu_offline]').removeClass("op_submenu_focus") ;

		$('#div_'+thediv).show() ;
		$('.menu_offline_'+thediv).removeClass("op_submenu3").addClass("op_submenu_focus") ;
	}

	function switch_dept( theobject )
	{
		var unique = unixtime() ;
		location.href = "interface_offline.php?deptid="+theobject.value+"&jump=<?php echo $jump ?>" ;
	}

	function do_submit_settings()
	{
		var emailm_cc = $('#emailm_cc').val().replace(/\s/g,'') ;
		$('#emailm_cc').val(emailm_cc) ;

		if ( emailm_cc && !check_email( emailm_cc ) )
			do_alert( 0, "Email format is invalid. (example: you@domain.com)" ) ;
		else if ( emailm_cc && ( "<?php echo $deptinfo["email"] ?>" == emailm_cc ) )
			do_alert( 0, "Email address must be different then the department email." ) ;
		else
			$('#theform').submit() ;
	}

	function do_reset()
	{
		$('#theform').trigger("reset") ;
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

		<div style="margin-top: 25px;" id="div_submenu2_wrapper">
			<div class="op_submenu3" style="margin-left: 0px;" onClick="location.href='interface_lang.php'">Chat Window Texts</div>
			<div class="op_submenu3" onClick="location.href='interface_connecting.php'">Connecting Text</div>
			<div class="op_submenu3 menu_offline_offline" onClick="location.href='interface_offline.php?jump=offline'">Offline Texts</div>
			<div class="op_submenu3 menu_offline_template" onClick="location.href='interface_offline.php?jump=template'">"Leave a message" Email Template</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/marquee/marquee.php" ) ): ?><div class="op_submenu3" onClick="location.href='../addons/marquee/marquee.php'">Marquee Text</div><?php endif ; ?>
			<div style="clear: both"></div>
		</div>

		<form action="interface_offline.php" id="theform" method="POST" accept-charset="<?php echo $LANG["CHARSET"] ?>">
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="deptid" value="<?php echo $deptid ?>">
		<input type="hidden" name="jump" id="jump" value="<?php echo $jump ?>">
		<div style="<?php echo ( count( $departments ) == 1 ) ? "display: none;" : "" ; ?> margin-top: 25px;">
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
		<div style="margin-top: 25px;">
			<?php if ( $jump == "offline" ): ?>
			When the department is offline, display the following offline message on the chat request window.
			<?php else: ?>
			The following template will be used to format the offline email message that is sent to the department email address when the visitor sends an offline message.
			<?php endif ; ?>
		</div>
		<?php if ( $redirect_url ): ?>
			<div style="margin-top: 25px;" class="info_warning">The "Leave a message" form will not be displayed because the <a href="icons.php?deptid=<?php echo $deptid ?>&jump=settings">Chat Icon offline setting</a> for this department is set to a URL.  Instead, the URL will be displayed.</div>
		<?php else: ?>
			<div id="div_offline" class="div_offline" style="display: none; margin-top: 25px;">
				<div>
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td valign="top" style="padding-right: 25px;">
							<div><span style="font-weight: bold;"><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> <big><big>Offline Header</big></big></span></div>
							<div style="margin-top: 5px;"><input type="text" class="input" style="width: 90%;" maxlength="165" name="TXT_MSG_LEAVE_MESSAGE" id="TXT_MSG_LEAVE_MESSAGE" value="<?php echo Util_Format_ConvertQuotes( rawurldecode( $LANG["MSG_LEAVE_MESSAGE"] ) ) ?>" placeholder="Please leave a message."></div>
						</td>
						<td valign="top" width="70%">
							<div>
								<div><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> <span style="font-weight: bold;">Subtext (default):</span></div>
								<div style="margin-top: 5px; padding-bottom: 15px;"><input type="text" class="input" style="width: 95%" id="message" name="message" maxlength="955" value="<?php echo preg_replace( "/\"/", "&quot;", $message ) ?>"></div>
							</div>

							<div>
								<div><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> <span style="font-weight: bold;">Subtext ("busy"):</span> When department operators are online but the chat request was not accepted.</div>
								<div style="margin-top: 5px; padding-bottom: 15px;"><input type="text" class="input" style="width: 95%" id="message_busy" name="message_busy" maxlength="955" value="<?php echo preg_replace( "/\"/", "&quot;", $deptinfo["msg_busy"] ) ?>"></div>
							</div>
						</td>
					</tr>
					</table>
				</div>
				<div style="margin-top: 15px;">
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td>
							<div class="info_neutral">
								<div class="info_white">
									<table cellspacing=0 cellpadding=2 border=0>
									<tr>
										<td><input type="radio" name="offline_form" id="offline_form_0" value=0 <?php echo ( !$offline_form ) ? "checked" : "" ; ?>></td>
										<td onClick="$('#offline_form_0').prop('checked', true);" style="cursor: pointer;">When Offline or Busy: <span class="info_misc"><b>Do not display</b> the "leave a message" email form.</span>  Only display the above Offline Header and Subtext messages.</td>
									</tr>
									</table>
								</div>
								<div class="info_white" style="margin-top: 15px;">
									<table cellspacing=0 cellpadding=4 border=0>
									<tr>
										<td><input type="radio" name="offline_form" id="offline_form_1" value=1 <?php echo ( $offline_form ) ? "checked" : "" ; ?>></td>
										<td>
											<div onClick="$('#offline_form_1').prop('checked', true);" style="cursor: pointer;">When Offline or Busy: <span class="info_misc"><b>Display</b> the "leave a message" email form</span> and allow visitors to send an email message to the department.</div>
											<div style="margin-top: 5px;">The message will be automatically sent to the <a href="depts.php?ftab=email">department email address</a>.  Also, send a copy to: <input type="text" class="input" size="20" maxlength="160" style="padding: 5px;" name="emailm_cc" id="emailm_cc" value="<?php echo $deptinfo["emailm_cc"] ?>"></div>
										</td>
									</tr>
									</table>
								</div>
							</div>
						</td>
					</tr>
					</table>
				</div>
			</div>
			<div id="div_template" class="div_offline" style="display: none; margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%" style="margin-top: 15px;">
				<tr>
					<td valign="top" nowrap>
						Subject: <input type="text" class="input" id="template_subject" name="template_subject" size="35" maxlength="255" value="<?php echo $template_subject ?>">
						<div style="margin-top: 5px;">
							<textarea type="text" cols="50" rows="7" id="template_body" name="template_body" style="resize: vertical;"><?php echo $template_body ?></textarea>
						</div>
					</td>
					<td valign="top" width="100%" style="padding-left: 15px;">
						Dynamically populated variables:
						<ul style="margin-top: 5px;">
							<li><b>%%visitor_subject%%</b> = subject provided by the visitor</li>
							<li style="margin-top: 3px;"><b>%%visitor_message%%</b> = message provided by the visitor</li>
							<li style="margin-top: 3px;"><b>%%department_name%%</b> = department name</li>
							<li style="margin-top: 3px;"><b>%%custom_variables%%</b> = <a href="interface_custom.php?deptid=<?php echo $deptid ?>" target="_parent">custom form fields</a> (if provided)</li>
							<li style="margin-top: 3px;"><b>%%visitor%%</b> = visitor's name</li>
							<li style="margin-top: 3px;"><b>%%visitor_email%%</b> = visitor's email</li>
							<li style="margin-top: 3px;"><b>%%stat_total_footprints%%</b> = visitor's total footprints (number)</li>
							<li style="margin-top: 3px;"><b>%%stat_ip%%</b> = visitor's IP address</li>
							<li style="margin-top: 3px;"><b>%%stat_visitor_id%%</b> = unique ID assigned to the visitor</li>
							<li style="margin-top: 3px;"><b>%%stat_onpage_url%%</b> = URL the chat icon was clicked</li>
						</ul>
					</td>
				</tr>
				</table>
			</div>
			<?php if ( count( $departments ) > 1 ) : ?>
			<div style="margin-top: 15px;"><input type="checkbox" id="copy_all" name="copy_all" value=1> copy this update to all departments</div>
			<?php endif ; ?>

			<div style="margin-top: 25px;">
				<input type="button" value="Update" class="btn" onClick="do_submit_settings()"> &nbsp; <input type="button" id="btn_reset" onClick="do_reset()" class="btn" value="Reset">
			</div>
		<?php endif ; ?>
		</form>

		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
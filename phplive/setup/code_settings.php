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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

	if ( !isset( $VALS["POPOUT"] ) ) { $VALS["POPOUT"] = "on" ; }
	if ( !isset( $VALS["EMBED_OPINVITE_AUTO"] ) ) { $VALS["EMBED_OPINVITE_AUTO"] = "off" ; }
	if ( !isset( $VALS["PRINTER_ICON"] ) ) { $VALS["PRINTER_ICON"] = "on" ; }
	if ( !isset( $VALS["EMBED_ANIMATE"] ) ) { $VALS["EMBED_ANIMATE"] = "on" ; }

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;
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
	var global_popout = "<?php echo $VALS["POPOUT"] ; ?>" ;
	var global_opauto = "<?php echo $VALS["EMBED_OPINVITE_AUTO"] ; ?>" ;
	var global_printer_icon = "<?php echo $VALS["PRINTER_ICON"] ; ?>" ;
	var global_embed_animate = "<?php echo $VALS["EMBED_ANIMATE"] ; ?>" ;
	var global_padding_bottom = <?php echo $VARS_CHAT_PADDING_WIDGET_BOTTOM ?> ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "interface" ) ;
		if ( typeof( show_div ) == "function" )
			show_div( "code_settings" ) ;
	});

	function confirm_popout( thepopout )
	{
		if ( global_popout != thepopout )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_popout&value="+thepopout+"&"+unixtime(),
				success: function(data){
					global_popout = thepopout ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
		else
			do_alert( 1, "Update Success" ) ;
	}

	function confirm_opauto( theopauto )
	{
		if ( global_opauto != theopauto )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_opauto&value="+theopauto+"&"+unixtime(),
				success: function(data){
					global_opauto = theopauto ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
		else
			do_alert( 1, "Update Success" ) ;
	}

	function confirm_printer_icon( the_printer_icon )
	{
		if ( global_printer_icon != the_printer_icon )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_printer_icon&value="+the_printer_icon+"&"+unixtime(),
				success: function(data){
					global_printer_icon = the_printer_icon ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
		else
			do_alert( 1, "Update Success" ) ;
	}

	function confirm_embed_animate( the_embed_animate )
	{
		if ( global_embed_animate != the_embed_animate )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_embed_animate&value="+the_embed_animate+"&"+unixtime(),
				success: function(data){
					global_embed_animate = the_embed_animate ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
		else
			do_alert( 1, "Update Success" ) ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" onClick="location.href='interface.php?jump=logo'" style="margin-left: 0px;">Logo</div>
			<div class="op_submenu" onClick="location.href='interface_themes.php'" id="menu_themes">Theme</div>
			<div class="op_submenu" onClick="location.href='interface_custom.php'">Form Fields</div>
			<div class="op_submenu" onClick="location.href='interface_lang.php'">Update Texts</div>
			<div class="op_submenu" onClick="location.href='code_autostart.php'">Automatic Start Chat</div>
			<div class="op_submenu" onClick="location.href='interface_gdpr.php'">Consent Checkbox</div>
			<div class="op_submenu" onClick="location.href='interface_chat_msg.php'">Chat End Msg</div>
			<?php if ( phpversion() >= "5.1.0" ): ?><div class="op_submenu" onClick="location.href='interface.php?jump=time'">Timezone</div><?php endif; ?>
			<div class="op_submenu_focus">Settings</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<form>
			<div style="text-align: justify;" id="settings_misc_settings">
				<div style="float: left; height: 300px; width: 45%" class="info_info">
					<div style="display: none;">
						<div style="font-size: 14px; font-weight: bold;"><img src="../pics/icons/win_pop.png" width="16" height="16" border="0" alt=""> Embed Chat Popout Display</div>
						<div style="margin-top: 5px;">(default is On) If <a href="icons.php?jump=settings">embed chat</a> is enabled, the popout feature allows the visitor to open the embed chat in a new window when clicking the popout icon <img src="../themes/whiteout/win_pop.png" width="16" height="16" border="0" alt="">.  By switching "Off" the embed chat popout, the popout icon <img src="../themes/whiteout/win_pop.png" width="16" height="16" border="0" alt=""> will not be visible.</div>
						<div style="margin-top: 5px;"><img src="../pics/icons/mobile.png" width="16" height="16" border="0" alt=""> Mobile devices (including tablets), the popout option is always Off.</div>
						<div style="margin-top: 5px;">
							<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#popout_on').prop('checked', true);confirm_popout('on');"><input type="radio" name="popout" id="popout_on" value="on" <?php echo ( $VALS["POPOUT"] != "off" ) ? "checked" : "" ?>> On</div>
							<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#popout_off').prop('checked', true);confirm_popout('off');"><input type="radio" name="popout" id="popout_off" value="off" <?php echo ( $VALS["POPOUT"] == "off" ) ? "checked" : "" ?>> Off</div>
							<div style="clear: both;"></div>
						</div>
						<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 10px;"></div>
					</div>

					<div style="font-size: 14px; font-weight: bold;"><img src="../themes/initiate/printer.png" width="16" height="16" border="0" alt=""> Printer Icon Display</div>
					<div style="margin-top: 5px;">(default is On) For the visitor chat window, set the system to display or not display the printer icon <img src="../themes/initiate/printer.png" width="16" height="16" border="0" alt=""> during a chat session.  The printer icon allows visitors to open the chat transcript in a new window for viewing and printing the chat transcript during a chat session.</div>
					<div style="margin-top: 5px;"><img src="../pics/icons/mobile.png" width="16" height="16" border="0" alt=""> Mobile devices (including tablets), the printer icon is always Off.</div>
					<div style="margin-top: 5px;">
						<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#printer_icon_on').prop('checked', true);confirm_printer_icon('on');"><input type="radio" name="printer_icon" id="printer_icon_on" value="on" <?php echo ( $VALS["PRINTER_ICON"] != "off" ) ? "checked" : "" ?>> On</div>
						<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#printer_icon_off').prop('checked', true);confirm_printer_icon('off');"><input type="radio" name="printer_icon" id="printer_icon_off" value="off" <?php echo ( $VALS["PRINTER_ICON"] == "off" ) ? "checked" : "" ?>> Off</div>
						<div style="clear: both;"></div>
					</div>
				</div>
				<div style="float: left; margin-left: 2px; height: 300px; width: 45%;" class="info_info">
					<div style="font-size: 14px; font-weight: bold;">Open the Embed Chat for Operator Initiated Chat Invites</div>
					<div style="margin-top: 5px;">(default is On) For operator initiated chat invites ("chat invite" feature on the operator console traffic monitor), automatically open the embed chat window and start the chat session.  "Off" will display the <a href="code_invite.php">chat invite image</a> only.</div>
					<div style="margin-top: 5px;">
						<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#opauto_on').prop('checked', true);confirm_opauto('on');"><input type="radio" name="opauto" id="opauto_on" value="on" <?php echo ( $VALS["EMBED_OPINVITE_AUTO"] != "off" ) ? "checked" : "" ?>> On</div>
						<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#opauto_off').prop('checked', true);confirm_opauto('off');"><input type="radio" name="opauto" id="opauto_off" value="off" <?php echo ( $VALS["EMBED_OPINVITE_AUTO"] == "off" ) ? "checked" : "" ?>> Off</div>
						<div style="clear: both;"></div>
					</div>
					<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 10px;"></div>

					<div style="font-size: 14px; font-weight: bold; margin-top: 10px;">Embed Window Animate</div>
					<div style="margin-top: 5px;">(default is Off) If "On", the embed chat window will load from bottom to top, animating the window height.  If set to "Off", the embed chat window will display immediately, without animation.  The <a href="code.php">HTML Code</a> area will show how the embed chat window will load.</div>
					<div style="margin-top: 5px;">
						<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#embed_animate_on').prop('checked', true);confirm_embed_animate('on');"><input type="radio" name="embed_animate" id="embed_animate_on" value="on" <?php echo ( $VALS["EMBED_ANIMATE"] != "off" ) ? "checked" : "" ?>> On</div>
						<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#embed_animate_off').prop('checked', true);confirm_embed_animate('off');"><input type="radio" name="embed_animate" id="embed_animate_off" value="off" <?php echo ( $VALS["EMBED_ANIMATE"] == "off" ) ? "checked" : "" ?>> Off</div>
						<div style="clear: both;"></div>
					</div>
				</div>
				<div style="clear: both;"></div>
			</div>
			</form>
		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>

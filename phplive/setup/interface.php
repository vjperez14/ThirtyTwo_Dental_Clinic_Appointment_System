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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Hash.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_File.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$https = "" ;
	if ( isset( $_SERVER["HTTP_CF_VISITOR"] ) && preg_match( "/(https)/i", $_SERVER["HTTP_CF_VISITOR"] ) ) { $https = "s" ; }
	else if ( isset( $_SERVER["HTTP_X_FORWARDED_PROTO"] ) && preg_match( "/(https)/i", $_SERVER["HTTP_X_FORWARDED_PROTO"] ) ) { $https = "s" ; }
	else if ( isset( $_SERVER["HTTPS"] ) && preg_match( "/(on)/i", $_SERVER["HTTPS"] ) ) { $https = "s" ; }

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$error = Util_Format_Sanatize( Util_Format_GetVar( "error" ), "ln" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ; if ( !$jump ) { $jump = "logo" ; }
	$jump2 = Util_Format_Sanatize( Util_Format_GetVar( "jump2" ), "ln" ) ; if ( !$jump2 ) { $jump2 = "upload" ; }
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$lang = Util_Format_Sanatize( Util_Format_GetVar( "lang" ), "ln" ) ;

	if ( !isset( $CONF["THEME"] ) ) { $CONF["THEME"] = "default" ; }
	if ( !isset( $CONF["lang"] ) ) { $CONF["lang"] = "english" ; } if ( !$lang ) { $lang = $CONF["lang"] ; }
	$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
	if ( isset( $dept_themes[0] ) ) { $CONF["THEME"] = $dept_themes[0] ; }
	$win_style = ( isset( $VALS["STYLE"] ) && $VALS["STYLE"] ) ? $VALS["STYLE"] : "modern" ;

	$deptinfo = Array() ;
	if ( $deptid )
		$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	LIST( $your_ip, $null ) = Util_IP_GetIP( "" ) ;

	if ( $action === "update" )
	{
		if ( $jump == "logo" )
		{
			LIST( $error, $filename ) = Util_Upload_File( "logo", $deptid ) ;
			if ( !$error )
				$url = "interface.php?deptid=$deptid&action=success" ;
			else
				$url = "interface.php?deptid=$deptid&action=error&error=".urlencode( $error ) ;
			if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
			HEADER( "location: $url" ) ;
			exit ;
		}
		else if ( $jump == "time" )
		{
			$timezone = Util_Format_Sanatize( Util_Format_GetVar( "timezone" ), "timezone" ) ;

			if ( $timezone != $CONF["TIMEZONE"] )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
				Chat_remove_ResetReports( $dbh ) ;

				$error = ( Util_Vals_WriteToConfFile( "TIMEZONE", $timezone ) ) ? "" : "Could not write to config file." ;
				if ( phpversion() >= "5.1.0" ){ date_default_timezone_set( $timezone ) ; }
			}
		}
	}
	else if ( ( $action === "clear" ) && $deptid )
	{
		$dir_files = glob( $CONF["CONF_ROOT"]."/logo_$deptid.*", GLOB_NOSORT ) ;
		$total_dir_files = count( $dir_files ) ;
		if ( $total_dir_files )
		{
			for ( $c = 0; $c < $total_dir_files; ++$c )
			{
				if ( $dir_files[$c] && is_file( $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
			}
		}
	}
	else if ( $action == "format" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		$timeformat = ( $value != 24 ) ? 12 : 24 ;
		if ( Util_Vals_WriteToFile( "TIMEFORMAT", $timeformat ) )
		{
			$VARS_24H = ( $value != 24 ) ? 0 : 1 ;
			$VARS_TIMEFORMAT = ( !$VARS_24H ) ? "g:i:s a" : "G:i:s" ;
			$action = "success" ;
		}
		else
			$error = "Error updating time format." ;
	}

	$departments = Depts_get_AllDepts( $dbh ) ;
	$timezones = Util_Hash_Timezones() ;
	$emlogos_hash = ( isset( $VALS["EMLOGOS"] ) && $VALS["EMLOGOS"] ) ? unserialize( $VALS["EMLOGOS"] ) : Array() ;

	$login_url = $CONF['BASE_URL'] ;
	if ( !preg_match( "/\/\//", $login_url ) ) { $login_url = "//$PHPLIVE_HOST$login_url" ; }
	$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;

	$global_default_logo = Util_Upload_GetLogo( "logo", 0 ) ;
	$logo = $global_default_logo ;
	if ( $deptid )
	{
		$logo = Util_Upload_GetLogo( "logo", $deptid ) ;
	}
	$is_using_global_default_logo = ( $logo == $global_default_logo ) ? 1 : 0 ;
	$embed_win_sizes = ( isset( $VALS["embed_win_sizes"] ) && $VALS["embed_win_sizes"] ) ? unserialize( $VALS["embed_win_sizes"] ) : Array() ;
	if ( isset( $embed_win_sizes[0] ) )
	{
		$VARS_CHAT_WIDTH_WIDGET = $embed_win_sizes[0]["width"] ;
		$VARS_CHAT_HEIGHT_WIDGET = $embed_win_sizes[0]["height"] ;
	}
	if ( isset( $VALS["padding_bottom"] ) && is_numeric( $VALS["padding_bottom"] ) ) { $VARS_CHAT_PADDING_WIDGET_BOTTOM = $VALS["padding_bottom"] ; }
	if ( isset( $VALS["border_radius"] ) && is_numeric( $VALS["border_radius"] ) ) { $VARS_CHAT_PADDING_WIDGET_RADIUS = $VALS["border_radius"] ; }
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
	var global_div_sub = "" ;
	var global_timeformat = <?php echo ( !isset( $VALS["TIMEFORMAT"] ) || ( isset( $VALS["TIMEFORMAT"] ) && ( $VALS["TIMEFORMAT"] != 24 ) ) ) ? 12 : 24 ; ?> ;
	var global_emlogo = <?php echo ( !isset( $emlogos_hash[$deptid] ) || ( isset( $emlogos_hash[$deptid] ) && $emlogos_hash[$deptid] ) ) ? 1 : 0 ; ?> ;
	var global_padding_bottom = <?php echo $VARS_CHAT_PADDING_WIDGET_BOTTOM ?> ;
	var global_border_radius = <?php echo $VARS_CHAT_PADDING_WIDGET_RADIUS ?> ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "interface" ) ;
		show_div( "<?php echo $jump ?>" ) ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>
		<?php if ( $action && $error ): ?>do_alert_div( "..", 0, "<?php echo $error ?>" ) ;<?php endif ; ?>

		check_image_dim() ;
	});

	function show_div( thediv )
	{
		$('#div_alert').hide() ;
	
		var divs = Array( "logo", "charset", "time", "lang", "props" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#settings_'+divs[c]).hide() ;
			$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('input#jump').val( thediv ) ;
		$('#settings_'+thediv).show() ;
		$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;

		if ( thediv == "logo" )
			show_div_logo( "<?php echo $jump2 ?>" ) ;
	}

	function show_div_logo( thediv )
	{
		$('#div_notice_ref').hide() ;

		var divs = Array( "upload", "display", "settings", "padding", "radius", "modern" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#settings_logo_'+divs[c]).hide() ;
			$('#menu2_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu3') ;
		}

		global_div_sub = thediv ;
		phplive_style = undeefined ; // so it loads system set style

		// cancel unchanged embed window size
		view_preview_cancel(0);

		// cancel unchanged embed window padding bottom
		$('#padding_bottom').val( global_padding_bottom ) ;
		$('#border_radius').val( global_border_radius ) ;

		if ( thediv == "settings" )
		{
			var width = $('#embed_width').val().trim() ; $('#embed_width').val( width ) ;
			var height = $('#embed_height').val().trim() ; $('#embed_height').val( height ) ;

			preview_theme_embed( width, height ) ;
		}

		if ( ( thediv == "padding" ) || ( thediv == "radius" ) || ( thediv == "modern" ) )
			$('#div_department_select').hide() ;
		else
			$('#div_department_select').show() ;

		if ( thediv != "upload" )
			$('#div_notice_ref').show() ;

		$('#settings_logo_'+thediv).show() ;
		$('#menu2_'+thediv).removeClass('op_submenu3').addClass('op_submenu_focus') ;
	}

	function switch_dept( theobject )
	{
		location.href = "interface.php?deptid="+theobject.value+"&jump2="+global_div_sub ;
	}

	function update_timezone()
	{
		var timezone = $('#timezone').val() ;

		if ( timezone != "<?php echo $CONF["TIMEZONE"] ?>" )
		{
			if ( confirm( "This action will reset the chat reports data.  Are you sure?" ) )
				location.href = "interface.php?action=update&jump=time&timezone="+timezone ;
			else
				$('#timezone').val( "<?php echo $CONF["TIMEZONE"] ?>" ) ;
		}
		else
			do_alert( 1, "System timezone is already "+timezone+"." ) ;
	}

	function check_image_dim()
	{
		var img = new Image() ;
		img.onload = get_img_dim ;
		img.src = '<?php print Util_Upload_GetLogo( "logo", $deptid ) ?>' ;
	}

	function get_img_dim()
	{
		var img_width = this.width ;
		var img_height = this.height ;

		//$('#div_logo').css({'width': img_width, 'height': img_height}) ;
	}

	function confirm_clear()
	{
		if ( confirm( "Really remove this department logo and use Global Default?" ) )
		{
			location.href = "interface.php?action=clear&deptid=<?php echo $deptid ?>" ;
		}
	}

	function toggle_emlogo( thevalue )
	{
		if ( global_emlogo != thevalue )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_emlogo&deptid=<?php echo $deptid ?>&value="+thevalue,
				success: function(data){
					global_emlogo = thevalue ;

					do_alert( 1, "Update Success" ) ;
					if ( global_emlogo )
					{
						var text_orig = $( "#deptid option:selected" ).text() ;
						$('#deptid').find('option[value="<?php echo $deptid ?>"]').text(text_orig + " (display logo)" ) ;
					}
					else
					{
						var text_orig = $( "#deptid option:selected" ).text() ;
						$('#deptid').find('option[value="<?php echo $deptid ?>"]').text(text_orig.replace( "(display logo)", "") ) ;
					}
				}
			});
		}
	}

	function confirm_change( theformat )
	{
		if ( parseInt( global_timeformat ) != parseInt( theformat ) )
			location.href = "interface.php?action=format&jump=time&value="+theformat+"&"+unixtime() ;
	}

	function view_preview( theupdateflag )
	{
		var width = $('#embed_width').val().trim() ; $('#embed_width').val( width ) ;
		var height = $('#embed_height').val().trim() ; $('#embed_height').val( height ) ;

		if ( !parseInt( width ) || !parseInt( height ) )
			do_alert( 0, "Value must be a number." ) ;
		else if ( width < 320 )
			do_alert( 0, "Width must be greater than 320 pixels." ) ;
		else if ( width > 800 )
			do_alert( 0, "Width must be less than 800 pixels." ) ;
		else if ( height < 400 )
			do_alert( 0, "Height must be greater than 400 pixels." ) ;
		else if ( height > 800 )
			do_alert( 0, "Height must be less than 800 pixels." ) ;
		else
		{
			var changed = 0 ;

			if ( width != <?php echo ( isset( $embed_win_sizes[$deptid] ) ) ? $embed_win_sizes[$deptid]["width"] : $VARS_CHAT_WIDTH_WIDGET ; ?> )
				changed = 1 ;
			if ( height != <?php echo ( isset( $embed_win_sizes[$deptid] ) ) ? $embed_win_sizes[$deptid]["height"] : $VARS_CHAT_HEIGHT_WIDGET ; ?> )
				changed = 1 ;

			if ( changed )
			{
				if ( !theupdateflag ) { $('#span_cancel').show() ; preview_theme_embed( width, height ) ; }
				else
				{
					$.ajax({
						type: "POST",
						url: "../ajax/setup_actions_.php",
						data: "action=embed_win&width="+width+"&height="+height+"&d=<?php echo $deptid ?>",
						success: function(data){
							location.href = "interface.php?jump2=settings&deptid=<?php echo $deptid ?>&action=success" ;
						}
					});
				}
			}
			else
			{
				if ( theupdateflag )
				{
					do_alert( 1, "Update Success" ) ;
				}
				else
				{
					preview_theme_embed( width, height ) ;
				}
			}
		}
	}

	function view_preview_cancel( theupdateflag )
	{
		var width ; var height ;

		if ( theupdateflag )
		{
			width = <?php echo $VARS_CHAT_WIDTH_WIDGET ; ?> ;
			height = <?php echo $VARS_CHAT_HEIGHT_WIDGET ; ?> ;
		}
		else
		{
			width = <?php echo ( isset( $embed_win_sizes[$deptid] ) ) ? $embed_win_sizes[$deptid]["width"] : $VARS_CHAT_WIDTH_WIDGET ; ?> ;
			height = <?php echo ( isset( $embed_win_sizes[$deptid] ) ) ? $embed_win_sizes[$deptid]["height"] : $VARS_CHAT_HEIGHT_WIDGET ; ?> ;
		}

		$('#embed_width').val( width ) ;
		$('#embed_height').val( height ) ;

		// for logo menu where JS has not fully loaded
		if ( typeof( phplive_embed_window_close ) != "undefined" )
			phplive_embed_window_close( ) ;

		$('#span_cancel').hide() ;

		if ( theupdateflag )
		{
			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions_.php",
				data: "action=embed_win&&d=<?php echo $deptid ?>",
				success: function(data){
					location.href = "interface.php?jump2=settings&deptid=<?php echo $deptid ?>&action=success" ;
				}
			});
		}
	}

	function update_padding()
	{
		var padding_bottom = parseInt( $('#padding_bottom').val().trim() ) ;

		if ( ( global_padding_bottom != padding_bottom ) && !isNaN( padding_bottom ) )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions_.php",
				data: "action=update_padding&value="+padding_bottom,
				success: function(data){
					global_padding_bottom = padding_bottom ;
					$('#padding_bottom').val( global_padding_bottom ) ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
		else if ( isNaN( padding_bottom ) )
		{
			$('#padding_bottom').val( global_padding_bottom ) ;
			do_alert( 0, "Padding value must be a number." ) ;
		}
		else
			do_alert( 1, "Update Success" ) ;
	}

	function update_border_radius()
	{
		var border_radius = parseInt( $('#border_radius').val().trim() ) ;

		if ( ( global_border_radius != border_radius ) && !isNaN( border_radius ) )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions_.php",
				data: "action=update_radius&value="+border_radius,
				success: function(data){
					global_border_radius = border_radius ;
					$('#border_radius').val( global_border_radius ) ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
		else if ( isNaN( border_radius ) )
		{
			$('#border_radius').val( global_border_radius ) ;
			do_alert( 0, "Radius value must be a number." ) ;
		}
		else
			do_alert( 1, "Update Success" ) ;
	}

	function confirm_style( thestyle )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_.php",
			data: "action=update_style&value="+thestyle,
			success: function(data){
				do_alert( 1, "Update Success" ) ;
				phplive_style = thestyle ;
			}
		});
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
			<div class="op_submenu" onClick="show_div('logo')" id="menu_logo" style="margin-left: 0px;">Logo</div>
			<div class="op_submenu" onClick="location.href='interface_themes.php'" id="menu_themes">Theme</div>
			<div class="op_submenu" onClick="location.href='interface_custom.php'" id="menu_custom">Form Fields</div>
			<div class="op_submenu" onClick="location.href='interface_lang.php'">Update Texts</div>
			<div class="op_submenu" onClick="location.href='code_autostart.php'" id="menu_auto">Automatic Start Chat</div>
			<div class="op_submenu" onClick="location.href='interface_gdpr.php'" id="menu_gdpr">Consent Checkbox</div>
			<div class="op_submenu" onClick="location.href='interface_chat_msg.php'">Chat End Msg</div>
			<?php if ( phpversion() >= "5.1.0" ): ?><div class="op_submenu" onClick="show_div('time')" id="menu_time">Timezone</div><?php endif; ?>
			<div class="op_submenu" onClick="location.href='code_settings.php'">Settings</div>
			<div style="clear: both"></div>
		</div>

		<?php if ( !count( $departments ) ): ?>
		<div style="margin-top: 25px;">
			<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span>
		</div>
		<?php else: ?>

		<form method="POST" action="interface.php" enctype="multipart/form-data" autocomplete="off">
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="jump" id="jump" value="">
		<input type="hidden" name="MAX_FILE_SIZE" value="3000000">

		<div style="display: none; margin-top: 25px;" id="settings_logo">
			<div id="op_submenu_wrapper_logo">
				<div class="op_submenu3" style="margin-left: 0px;" onClick="show_div_logo('upload')" id="menu2_upload">Upload Logo</div>
				<div class="op_submenu3" onClick="show_div_logo('display')" id="menu2_display">Display Logo</div>
				<div class="op_submenu3" onClick="show_div_logo('settings')" id="menu2_settings">Embed Window Size</div>
				<div class="op_submenu3" onClick="show_div_logo('padding')" id="menu2_padding">Embed Window Padding Bottom</div>
				<div class="op_submenu3" onClick="show_div_logo('radius')" id="menu2_radius">Embed Window Border Radius</div>
				<div class="op_submenu3" onClick="show_div_logo('modern')" id="menu2_modern">Modern Look</div>
				<div style="clear: both"></div>
			</div>

			<div style="margin-top: 25px;" id="div_department_select">
				<?php if ( count( $departments ) > 1 ): ?>
				<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this )">
					<option value="0">All Departments <?php echo ( !isset( $emlogos_hash[0] ) || ( isset( $emlogos_hash[0] ) && $emlogos_hash[0] ) ) ? "(display logo)" : "" ; ?></option>
					<?php
						for ( $c = 0; $c < count( $departments ); ++$c )
						{
							$department = $departments[$c] ;
							if ( $department["name"] != "Archive" )
							{
								$display_text = ( !isset( $emlogos_hash[$department["deptID"]] ) || ( isset( $emlogos_hash[$department["deptID"]] ) && $emlogos_hash[$department["deptID"]] ) ) ? "(display logo)" : "" ;
								$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
								print "<option value=\"$department[deptID]\" $selected>$department[name] $display_text</option>" ;
							}
						}
						if ( count( $dept_groups ) )
						{
							for ( $c = 0; $c < count( $dept_groups ); ++$c )
							{
								$dept_group = $dept_groups[$c] ;
								$display_text = ( !isset( $emlogos_hash[$dept_group["groupID"]] ) || ( isset( $emlogos_hash[$dept_group["groupID"]] ) && $emlogos_hash[$dept_group["groupID"]] ) ) ? "(display logo)" : "" ;
								$selected = ( $deptid == $dept_group["groupID"] ) ? "selected" : "" ;
								print "<option value=\"$dept_group[groupID]\" $selected>$dept_group[name] [Department Group] $display_text</option>" ;
							}
						}
					?>
				</select>
				<div id="div_notice_ref" style="display: none; margin-top: 5px;" class="info_dept"><b>IMPORTANT:</b> Display logo and embed window size settings are based on the <a href="code.php?deptid=<?php echo $deptid ?>">chat icon HTML Code</a> that references the above selected department.</div>

				<?php else: ?>
				<input type="hidden" name="deptid" id="deptid" value="0">
				<?php endif ; ?>
			</div>

			<div id="settings_logo_upload" style="display: none; margin-top: 25px;">
				For proper display of the logo, <span class="info_warning" style="padding: 2px;"><b>maximum</b> logo size should be <b><?php echo $VARS_CHAT_WIDTH_WIDGET - 50 ?>px (width)</b> and <b>135px (height)</b></span>.  If the logo is bigger then the recommended dimensions, it will be automatically resized to fit the window.

				<table cellspacing=0 cellpadding=0 border=0 width="100%" class="edit_wrapper" style="margin-top: 15px;">
				<tr>
					<td valign="top">
						<div id="div_alert" style="display: none; margin-bottom: 25px;"></div>

						<?php if ( ( count( $departments ) == 1 ) && isset( $deptinfo["deptID"] ) ): ?>
						<div class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Because only one department is available, choose the <a href="interface.php" style="color: #FFFFFF;">"All Departments"</a> to upload your logo.</div>

						<?php else: ?>
							<div id="div_logo"><img src="<?php print $logo ?>" style="max-width: 520px; max-height: 150px; border: 0px;"></div>

							<?php if ( $deptid && !$is_using_global_default_logo ): ?>
								<div style="margin-top: 15px;"><img src="../pics/icons/reset.png" width="16" height="16" border="0" alt=""> <a href="JavaScript:void(0)" onClick="confirm_clear()">clear this logo and use All Departments logo</a></div>
							<?php elseif ( $deptid ): ?>
								<div style="margin-top: 15px;">&bull; currently using <a href="interface.php">All Departments logo</a></div>
							<?php endif ; ?>

							<div style="margin-top: 25px;">
								<div><input type="file" name="logo" size="30"></div>
								<div style="margin-top: 5px;"><input type="submit" value="Upload Logo" style="margin-top: 10px;" class="btn"></div>
							</div>
						<?php endif ; ?>
					</td>
				</tr>
				</table>
			</div>
			<div id="settings_logo_display" style="display: none; margin-top: 25px;">
				<div>
					Display the logo on the chat window?
				</div>
				<div style="margin-top: 15px;">
					<div class="info_good" style="float: left; padding: 3px; cursor: pointer;" onclick="$('#emlogo_on').prop('checked', true);toggle_emlogo(1);"><input type="radio" name="emlogo" id="emlogo_on" value="1" <?php echo ( !isset( $emlogos_hash[$deptid] ) || ( isset( $emlogos_hash[$deptid] ) && $emlogos_hash[$deptid] ) ) ? "checked" : "" ; ?>> Display Logo</div>
					<div class="info_error" style="float: left; margin-left: 10px; padding: 3px; cursor: pointer;" onclick="$('#emlogo_off').prop('checked', true);toggle_emlogo(0);"><input type="radio" name="emlogo" id="emlogo_off" value="0" <?php echo ( !isset( $emlogos_hash[$deptid] ) || ( isset( $emlogos_hash[$deptid] ) && $emlogos_hash[$deptid] ) ) ? "" : "checked" ; ?>> Do Not Display Logo</div>
					<div style="clear: both;"></div>
				</div>

				<div style="margin-top: 45px;"><span class="info_menu_focus" style="padding: 6px;"><a href="JavaScript:void(0)" onClick="preview_theme('<?php echo ( isset( $dept_themes[$deptid] ) ) ? $dept_themes[$deptid] : $CONF["THEME"] ; ?>', <?php echo $VARS_CHAT_WIDTH ?>, <?php echo $VARS_CHAT_HEIGHT ?>, <?php echo $deptid ?> )">view how it looks (popup)</a></span></div>
				<div style="margin-top: 25px;"><span class="info_menu_focus" style="padding: 6px;"><a href="JavaScript:void(0)" onClick="show_div_logo('settings')">view how it looks (embed)</a></span></div>
			</div>
			<div id="settings_logo_settings" style="display: none; margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td valign="top" width="400">
						<div class="info_neutral">
							Adjust the embed chat window <span class="info_white"><big><b>Width and Height</b></big></span>.
							<div style="margin-top: 15px;" id="div_win_size">
								<table cellspacing=0 cellpadding=0 border=0>
								<tr>
									<td>Width</td>
									<td style="padding-left: 5px;">
										<select name="embed_width" id="embed_width">
										<?php
											$width = isset( $embed_win_sizes[$deptid] ) ? $embed_win_sizes[$deptid]["width"] : $VARS_CHAT_WIDTH_WIDGET ;
											for ( $c = 320; $c <= 800; ++$c )
											{
												if ( ( $c % 5 ) == 0 )
												{
													$selected = ( $c == $width ) ? "selected" : "" ;
													print "<option value=\"$c\" $selected>$c</option>" ;
												}
											}
										?>
										</select> (default: 415)
									</td>
								</tr>
								<tr><td><img src="../pics/space.gif" width="1" height="5" border=0></td></tr>
								<tr>
									<td>Height</td>
									<td style="padding-left: 5px;">
										<select name="embed_height" id="embed_height">
										<?php
											$height = isset( $embed_win_sizes[$deptid] ) ? $embed_win_sizes[$deptid]["height"] : $VARS_CHAT_HEIGHT_WIDGET ;
											for ( $c = 400; $c <= 800; ++$c )
											{
												if ( ( $c % 5 ) == 0 )
												{
													$selected = ( $c == $height ) ? "selected" : "" ;
													print "<option value=\"$c\" $selected>$c</option>" ;
												}
											}
										?>
										</select> (default: 600)
									</td>
								</tr>
								<tr><td><img src="../pics/space.gif" width="1" height="5" border=0></td></tr>
								<tr>
									<td>&nbsp;</td>
									<td>
										<div style="padding-top: 10px;"><span class="info_menu_focus" style="padding: 6px;"><a href="JavaScript:void(0)" onClick="view_preview(0)">view how it will look</a></span> &nbsp;&nbsp; <span style="display: none;" id="span_cancel"><a href="JavaScript:void(0)" onClick="view_preview_cancel(0)">cancel</a></span></div>
										<div style="margin-top: 25px;">
											<button type="button" class="btn" onClick="view_preview(1)">Update</button> &nbsp;
											<?php if ( isset( $embed_win_sizes[$deptid] ) && $deptid ): ?>
											<span id="span_embed_win_reset" style="">&bull; reset to use <a href="JavaScript:void(0)" onClick="view_preview_cancel(1)">Global Default</a></span>
											<?php elseif ( isset( $embed_win_sizes[$deptid] ) ): ?>
											<span id="span_embed_win_reset" style="">&bull; reset to use <a href="JavaScript:void(0)" onClick="view_preview_cancel(1)">original size</a></span>
											<?php endif ; ?>
										</div>
									</td>
								</tr>
								</table>
							</div>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<div id="settings_logo_padding" style="display: none; margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td valign="top" width="400">
						<div class="info_neutral">
							<div>Adjust the embed chat window <span class="info_white"><big><b>Padding Bottom</b></big></span> (default: 20)</div>
							<div style="margin-top: 15px;">
								<select id="padding_bottom">
									<?php
										for ( $c = 0; $c <= 50; ++$c )
										{
											if ( !( $c % 5 ) )
											{
												$selected = ( $c == $VARS_CHAT_PADDING_WIDGET_BOTTOM ) ? "selected" : "" ;
												print "<option value=\"$c\" $selected>$c</option>" ;
											}
										}
									?>
								</select>
							</div>
							<div style="margin-top: 25px;"><span class="info_menu_focus" style="padding: 6px;"><a href="JavaScript:void(0)" onClick="view_preview(0)">view how it will look</a></span></div>
							<div style="margin-top: 25px;">
								<button type="button" class="btn" onClick="update_padding()">Update</button>
							</div>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<div id="settings_logo_radius" style="display: none; margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td valign="top" width="400">
						<div class="info_neutral">
							<div>Adjust the embed chat window <span class="info_white"><big><b>Border Radius</b></big></span> (default: 10)</div>
							<div style="margin-top: 15px;">
								<select id="border_radius">
									<?php
										for ( $c = 0; $c <= 60; ++$c )
										{
											if ( !( $c % 5 ) )
											{
												$selected = ( $c == $VARS_CHAT_PADDING_WIDGET_RADIUS ) ? "selected" : "" ;
												print "<option value=\"$c\" $selected>$c</option>" ;
											}
										}
									?>
								</select>
							</div>
							<div style="margin-top: 25px;"><span class="info_menu_focus" style="padding: 6px;"><a href="JavaScript:void(0)" onClick="view_preview(0)">view how it will look</a></span></div>
							<div style="margin-top: 25px;">
								<button type="button" class="btn" onClick="update_border_radius()">Update</button>
							</div>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<div id="settings_logo_modern" style="display: none; margin-top: 25px;">
				"Modern look" will override few <a href="interface_themes.php">theme</a> style values (font-size and line-height) to a more modern sizes.  This will provide a more "modern look" to the visitor chat window.

				<div style="margin-top: 25px;">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td>
							<div class="info_neutral">
								<input type="radio" name="win_look" id="win_modern" value="modern" <?php echo ( $win_style == "modern" ) ? "checked" : "" ; ?> onClick="confirm_style('modern')"><span onclick="$('#win_modern').prop('checked', true);confirm_style('modern');" style="cursor: pointer;"> Modern Look</span>
								<div class="info_menu_focus" style="margin-top: 25px; padding: 10px;"><a href="JavaScript:void(0)" onClick="phplive_style='modern';view_preview(0)">view how it looks</a></div>
							</div>
						</td>
						<td style="padding-left: 15px;">
							<div class="info_neutral">
								<input type="radio" name="win_look" id="win_classic" value="classic" <?php echo ( $win_style == "classic" ) ? "checked" : "" ; ?> onClick="confirm_style('classic')"><span onclick="$('#win_classic').prop('checked', true);confirm_style('classic');" style="cursor: pointer;">  Classic look</span>
								<div class="info_menu_focus" style="margin-top: 25px; padding: 10px;"><a href="JavaScript:void(0)" onClick="phplive_style='classic';view_preview(0)">view how it looks</a></div>
							</div>
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>

		<?php if ( phpversion() >= "5.1.0" ): ?>
		<div style="display: none; margin-top: 25px;" id="settings_time">

			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td valign="top">

					<div style="">
						<select id="timezone">
						<?php
							for ( $c = 0; $c < count( $timezones ); ++$c )
							{
								$selected = "" ;
								if ( $timezones[$c] == date_default_timezone_get() )
									$selected = "selected" ;

								print "<option value=\"$timezones[$c]\" $selected>$timezones[$c]</option>" ;
							}
						?>
						</select>
					</div>
					<div style="margin-top: 15px;">
						<div style="text-align: justify;" class="info_error">
							<table cellspacing=0 cellpadding=0 border=0>
							<tr>
								<td><img src="../pics/icons/alert.png" width="16" height="16" border="0" alt=""></td>
								<td style="padding-left: 5px;">Updating the timezone will reset (clear) the <a href="reports_chat.php" style="color: #FFFFFF;">chat reports data</a>.</td>
							</tr>
							</table>

							<div style="margin-top: 5px;">The reset is necessary because the past data timezone will conflict with the new timezone, resulting in invalid data and possible errors.  Be sure to print out the current reports before continuing.  The <a href="transcripts.php" style="color: #FFFFFF;">chat transcripts</a> will not be deleted but the created timestamp may be different from the original because of the timezone change.</div>
						</div>
					</div>

					<div style="margin-top: 25px;"><button type="button" onClick="update_timezone()" class="btn">Update Timezone</button></div>
				</td>
				<td width="25"><img src="../pics/space.gif" width="25" height="1" border=0></td>
				<td valign="top" width="500" class="info_info">
					<div>
						<div>System Time:</div>
						<div style="display: inline-block; margin-top: 15px; font-size: 18px; font-weight: bold; font-family: sans-serif;"><?php echo $CONF['TIMEZONE'] ?></div>
						<div style="margin-top: 15px; font-size: 32px; font-weight: bold; color: #3A89D1; font-family: sans-serif;"><?php echo date( "M j, Y ($VARS_TIMEFORMAT)", time() ) ; ?></div>
					</div>

					<div style="margin-top: 25px;">Updating the hour format will not reset any data.  It simply formats the hour display to 12 or 24.</div>
					<div style="margin-top: 15px;">
						<span class="info_neutral" style="margin-left: 5px; cursor: pointer;" onclick="$('#timeformat_12').prop('checked', true);confirm_change(12);"><input type="radio" id="timeformat_12" name="timeformat_12" value="12" <?php echo ( !$VARS_24H ) ? "checked" : "" ; ?>> 12h</span>
						<span class="info_neutral" style="margin-left: 5px; cursor: pointer;" onclick="$('#timeformat_24').prop('checked', true);confirm_change(24);"><input type="radio" id="timeformat_24" name="timeformat_12" value="24" <?php echo ( $VARS_24H ) ? "checked" : "" ; ?>> 24h</span>
					</div>
				</td>
			</tr>
			</table>

		</div>
		<?php endif; ?>

		</form>

		<?php endif ; ?>

		<?php endif ; ?>

<span style="color: #0000FF; text-decoration: underline; line-height: 0px !important; cursor: pointer; position: fixed; bottom: 0px; right: 15px; z-index: 20000000;" id="phplive_btn_615" onclick="phplive_launch_chat_<?php echo $deptid ?>()"></span>
<script data-cfasync="false" type="text/javascript">

var st_embed_launch ;
var si_loaded ;
var phplive_stop_chat_icon = 1 ;
var phplive_padding_bottom ;
var phplive_border_radius ;
var phplive_style ;

function preview_theme_embed( thewidth, theheight )
{
	if ( $('#phplive_iframe_chat_embed_wrapper').is(":visible") )
	{
		phplive_embed_window_close( ) ;
		if ( typeof( st_embed_launch ) != "undefined" ) { clearTimeout( st_embed_launch ) ; }
		st_embed_launch = setTimeout( function(){
			if ( thewidth )
				phplive_depts[<?php echo $deptid ?>]["embed_width"] = thewidth ;
			if ( theheight )
				phplive_depts[<?php echo $deptid ?>]["embed_height"] = theheight ;

			phplive_padding_bottom = parseInt( $('#padding_bottom').val().trim() ) ;
			phplive_border_radius = parseInt( $('#border_radius').val().trim() ) ;
			phplive_launch_chat_<?php echo $deptid ?>() ;
		}, 1200 ) ;
	}
	else
	{
		si_loaded = setInterval(function( ){
			if ( ( typeof( phplive_depts ) != "undefined" ) && ( typeof( phplive_depts[<?php echo $deptid ?>] ) != "undefined" ) && ( typeof( phplive_depts[<?php echo $deptid ?>]["embed_width"] ) != "undefined" ) && ( typeof( phplive_launch_chat ) != "undefined" ) )
			{
				clearInterval( si_loaded ) ;

				if ( thewidth )
					phplive_depts[<?php echo $deptid ?>]["embed_width"] = thewidth ;
				if ( theheight )
					phplive_depts[<?php echo $deptid ?>]["embed_height"] = theheight ;

				phplive_padding_bottom = parseInt( $('#padding_bottom').val().trim() ) ;
				phplive_border_radius = parseInt( $('#border_radius').val().trim() ) ;
				phplive_launch_chat_<?php echo $deptid ?>() ;
			}
		}, 200 ) ;
	}
}

(function() {
var phplive_e_615 = document.createElement("script") ;
phplive_e_615.type = "text/javascript" ;
phplive_e_615.async = true ;
phplive_e_615.src = "<?php echo $CONF["BASE_URL"] ?>/js/phplive_v2.js.php?v=<?php echo $deptid ?>%7C615%7C0%7C&" ;
document.getElementById("phplive_btn_615").appendChild( phplive_e_615 ) ;
})() ;

</script>

<?php include_once( "./inc_footer.php" ) ?>
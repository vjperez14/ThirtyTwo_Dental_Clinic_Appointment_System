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
	if ( !is_file( "../web/config.php" ) ){ HEADER("location: ../setup/install.php") ; exit ; }
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$opinfo = Util_Security_AuthOp( $dbh ) ){ ErrorHandler( 602, "Invalid operator session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$console = Util_Format_Sanatize( Util_Format_GetVar( "console" ), "n" ) ;
	$auto = Util_Format_Sanatize( Util_Format_GetVar( "auto" ), "n" ) ;
	$menu = "notifications" ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ; if ( !$jump ) { $jump = "sounds" ; }
	$error = "" ;

	$opvars = Ops_get_OpVars( $dbh, $opinfo["opID"] ) ;
	$op_sounds = ( isset( $VALS["op_sounds"] ) && $VALS["op_sounds"] ) ? unserialize( $VALS["op_sounds"] ) : Array() ;
	if ( isset( $op_sounds[$opinfo["opID"]] ) ) { $op_sounds_vals = $op_sounds[$opinfo["opID"]] ; $opinfo["sound1"] = $op_sounds_vals[0] ; $opinfo["sound2"] = $op_sounds_vals[1] ; } else { $opinfo["sound1"] = "default" ; $opinfo["sound2"] = "default" ; }

	$sound_on = ( !isset( $opvars["sound"] ) || ( isset( $opvars["sound"] ) && $opvars["sound"] ) ) ? "checked" : "" ;
	$sound_off = ( $sound_on == "checked" ) ? "" : "checked" ;
	$blink_on = ( !isset( $opvars["blink"] ) || ( isset( $opvars["blink"] ) && !$opvars["blink"] ) ) ? "" : "checked" ;
	$blink_off = ( $blink_on == "checked" ) ? "" : "checked" ;
	$blink_r_on = ( !isset( $opvars["blink_r"] ) || ( isset( $opvars["blink_r"] ) && !$opvars["blink_r"] ) ) ? "" : "checked" ;
	$blink_r_off = ( $blink_r_on == "checked" ) ? "" : "checked" ;
	$dn_always_on = ( !isset( $opvars["dn_always"] ) || ( isset( $opvars["dn_always"] ) && $opvars["dn_always"] ) ) ? "checked" : "" ;
	$dn_always_off = ( $dn_always_on == "checked" ) ? "" : "checked" ;

	if ( $action === "update_sound" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$sound1 = Util_Format_Sanatize( Util_Format_GetVar( "sound1" ), "ln" ) ;
		$sound2 = Util_Format_Sanatize( Util_Format_GetVar( "sound2" ), "ln" ) ;

		$op_sounds[$opinfo["opID"]] = Array( $sound1, $sound2 ) ;
		Util_Vals_WriteToFile( "op_sounds", serialize( $op_sounds ) ) ;
		$opinfo["sound1"] = $sound1 ; $opinfo["sound2"] = $sound2 ;
		
		$jump = "sounds" ;
	}
	else if ( $action === "success" )
	{
		// success action is an indicator to show the success alert as well
		// as bypass the refreshing of the operator console
	}
	else
		$error = "invalid action" ;

	$mapp_enabled = 0 ;
	$mapp_opid = $opinfo["opID"] ;
	$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
	if ( $mapp_opid && isset( $mapp_array[$mapp_opid] ) && isset( $mapp_array[$mapp_opid]["a"] ) ) { $mapp_enabled = 1 ; }
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> Operator </title>

<meta name="description" content="v.<?php echo $VERSION ?>">
<meta name="keywords" content="<?php echo md5( $KEY ) ?>">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/dn.js?<?php echo filemtime ( "../js/dn.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/modernizr.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var opwin ;
	var base_url = ".." ; // needed for function play_sound()
	var focused = 1 ; // needed for function play_sound() and dn_show()
	var dn = dn_check() ;
	var dn_always = 1 ; // always show since demo notification
	var dn_enabled_response = <?php echo ( isset( $opvars["dn_response"] ) ) ? $opvars["dn_response"] : 0 ; ?> ;
	var dn_enabled_request = <?php echo ( isset( $opvars["dn_request"] ) ) ? $opvars["dn_request"] : 0 ; ?> ;
	var dn_counter = 0 ;
	var st_sound ;
	var sound_volume = ( typeof( parent.isop ) != "undefined" ) ? parent.sound_volume : 1 ;
	var embed = 0 ;
	var mobile = 0 ;
	var mapp = parent.mapp ;
	var wp = ( ( typeof( window.external ) != "undefined" ) && ('wp_total_visitors' in window.external) ) ? 1 : 0 ;
	var proto = location.protocol ;
	var dn_browser ;

	var global_sound = <?php echo ( $sound_on ) ? 1 : 0 ; ?> ;
	var global_blink = <?php echo ( $blink_on ) ? 1 : 0 ; ?> ;
	var global_blink_r = <?php echo ( $blink_r_on ) ? 1 : 0 ; ?> ;
	var global_dn_always = <?php echo ( $dn_always_on ) ? 1 : 0 ; ?> ;
	var global_mobile_push = <?php echo ( $opinfo["sms"] == 1 ) ? 1 : 0 ; ?>

	var audio_supported = HTML5_audio_support() ;
	var mp3_support = ( typeof( audio_supported["mp3"] ) != "undefined" ) ? 1 : 0 ;
	var phplive_session_support = ( typeof( Storage ) != "undefined" ) ? 1 : 0 ;

	var jump = "<?php echo $jump ?>" ; // need to use js var jump due to notification check in .ready

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;

		if ( ( typeof( parent.isop ) != "undefined" ) && ( parent.dn_status == 1 ) )
		{
			//parent.dn_status = undeefined ; // need to unset so it redirect once
			//jump = "dn" ;
		}

		init_menu_op() ;
		toggle_menu_op( "notifications" ) ;
		show_div( jump ) ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>

		<?php if ( $action && !$error && ( $action != "update_password" ) ): ?>
		if ( ( typeof( parent.isop ) != "undefined" ) && ( "<?php echo $action ?>" != "success" ) )
		{
			if ( "<?php echo $action ?>" == "update_sound" )
			{
				parent.sound_new_request = $('#sound1').val() ;
				parent.sound_new_text = $('#sound2').val() ;
			}
			else
				parent.refresh_console(0) ;
		}
		<?php endif ; ?>

		if ( dn_enabled_response ) { $('#dn_enabled_response_off').hide() ; $('#dn_enabled_response_on').show() ; }
		if ( audio_supported ) { $("input[name=volume][value='"+sound_volume+"']").prop("checked", true) ; $('#tr_sound_volume').show() ; }

		dn_browser = dn_check_browser() ;
		if ( ( dn_browser == "null" ) && !wp )
			$('#dn_unavailable').show() ;
		else
		{
			var dn = dn_check() ;

			if ( ( dn == -1 ) && ( dn_browser == "firefox" ) ) { $('#dn_firefox').show() ; }
			else
			{
				if ( wp ) { $('#dn_winapp').show() ; }
				else if ( !dn )
				{
					$('#dn_enabled').show() ;

					if ( dn_enabled_request ) { $('#dn_enabled_on').show() ; }
					else { $('#dn_enabled_off').show() ; }
				}
				else if ( ( dn == 2 ) && ( dn_browser == "chrome" ) )
				{
					if ( proto == "http:" )
						$('#dn_chrome').show() ;
					else
						$('#dn_disabled').show() ;
				}
				else if ( dn == 2 ) { $('#dn_disabled').show() ; }
				else { $('#dn_request').show() ; }
			}
		}

		if ( typeof( parent.isop ) != "undefined" ) { parent.init_extra_loaded() ; }
		if ( wp ) { $('#chat_text_powered').hide() ; }

		if ( !mp3_support )
		{
			$('#div_sound_alert_onoff').hide() ;
			$('#td_sound').hide() ;
			do_alert( 0, "Browser does not support MP3 sound files.  Sound settings are not available." ) ;
		} else { toggle_td_sound() ; }

		get_https_url() ;
	});

	function get_https_url()
	{
		var url = window.location.href ;
		if ( !url.match( /^https:/i ) )
		{
			url = url.replace( /(.*?)\/\//, "HTTPS://" ) ;
			url = url.replace( /\/ops(.*)$/, "" ) ;
		}
		$('#span_https_url').html( url ) ;
	}

	function show_div( thediv )
	{
		var divs = Array( "sounds", "dn", "sms" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#op_'+divs[c]).hide() ;
			$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('#op_'+thediv).show() ;
		$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
	}

	function demo_sound1( theflag )
	{
		var sound = $('#sound1').val() ;

		clear_sound('new_request') ;
		if ( theflag )
			play_sound(1, 'new_request', 'new_request_'+sound) ;
	}

	function demo_sound2()
	{
		var sound = $('#sound2').val() ;

		clear_sound('new_request') ;
		play_sound(0, 'new_text', 'new_text_'+sound) ;
	}

	function update_sound()
	{
		var sound1 = $('#sound1').val() ;
		var sound2 = $('#sound2').val() ;

		location.href = "notifications.php?wp="+wp+"&auto=<?php echo $auto ?>&console=<?php echo $console ?>&action=update_sound&sound1="+sound1+"&sound2="+sound2 ;
	}

	function dn_toggle( theflag )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "../ajax/chat_actions_op_ext.php",
			data: "wp="+wp+"&auto=<?php echo $auto ?>&console=<?php echo $console ?>&action=dn_toggle&dn="+theflag+"&"+unixtime(),
			success: function(data){
				eval(data) ;

				if ( json_data.status )
				{
					if ( theflag == 1 ){ $('#dn_enabled_off').hide() ; $('#dn_enabled_on').show() ; }
					else { $('#dn_enabled_on').hide() ; $('#dn_enabled_off').show() ; }

					if ( typeof( parent.dn_enabled_request ) != "undefined" )
						parent.dn_enabled_request = theflag ;
				}
				else
					do_alert( 0, "Error: Could not update DN value." ) ;
			}
		});
	}

	function dn_toggle_response( theflag )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "../ajax/chat_actions_op_ext.php",
			data: "wp="+wp+"&auto=<?php echo $auto ?>&console=<?php echo $console ?>&action=dn_toggle_response&dn="+theflag+"&"+unixtime(),
			success: function(data){
				eval(data) ;

				if ( json_data.status )
				{
					if ( theflag == 1 ){ $('#dn_enabled_response_off').hide() ; $('#dn_enabled_response_on').show() ; }
					else { $('#dn_enabled_response_on').hide() ; $('#dn_enabled_response_off').show() ; }

					if ( typeof( parent.dn_enabled_response ) != "undefined" )
						parent.dn_enabled_response = theflag ;
				}
				else
					do_alert( 0, "Error: Could not update DN response value." ) ;
			}
		});
	}

	function toggle_console_sound( thevalue )
	{
		if ( parseInt( global_sound ) != parseInt( thevalue ) )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/chat_actions_op_ext.php",
				data: "action=console_sound&value="+thevalue+"&"+unixtime(),
				success: function(data){
					eval(data) ;

					if ( json_data.status )
					{
						global_sound = thevalue ;
						if ( typeof( parent.chat_sound ) != "undefined" )
						{
							parent.chat_sound = global_sound ;
							parent.print_chat_sound_image( parent.theme ) ;
							parent.clear_sound( "new_request" ) ;
						}

						toggle_td_sound() ;

						if ( !parseInt( global_sound ) && !parseInt( global_blink ) ) { toggle_console_blink( 1, 0 ) ; $('#console_blink_on').prop('checked', true) ; }
						else if ( parseInt( global_sound ) ) { $('#div_console_blink_alert').hide() ; }
						do_alert( 1, "Update Success" ) ;
					}
					else
						do_alert( 0, "Error: Could not update value.  Please try again." ) ;
				}
			});
		}
	}

	function toggle_td_sound()
	{
		if ( $('#console_sound_on').is(':checked') )
			$('#td_sound').show() ;
		else
			$('#td_sound').hide() ;
	}

	function toggle_console_blink( thevalue, thealert )
	{
		if ( parseInt( global_blink ) != parseInt( thevalue ) )
		{
			if ( ( !parseInt( global_sound ) && !parseInt( thevalue ) ) || !thealert )
			{
				$('#div_console_blink_alert').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
				$('#console_blink_on').prop('checked', true) ;
			}

			if ( !parseInt( global_sound ) && !thevalue ) {}
			else
			{
				var json_data = new Object ;

				$.ajax({
					type: "POST",
					url: "../ajax/chat_actions_op_ext.php",
					data: "action=console_blink&value="+thevalue+"&"+unixtime(),
					success: function(data){
						eval(data) ;

						if ( json_data.status )
						{
							global_blink = thevalue ;
							if ( typeof( parent.isop ) != "undefined" ) { parent.console_blink = thevalue ; }
							if ( thealert ) { do_alert( 1, "Update Success" ) ; }
						}
						else
							do_alert( 0, "Error: Could not update value.  Please try again." ) ;
					}
				});
			}
		}
	}

	function toggle_console_blink_r( thevalue, thealert )
	{
		if ( parseInt( global_blink_r ) != parseInt( thevalue ) )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/chat_actions_op_ext.php",
				data: "action=console_blink_r&value="+thevalue+"&"+unixtime(),
				success: function(data){
					eval(data) ;

					if ( json_data.status )
					{
						global_blink_r = thevalue ;
						if ( typeof( parent.isop ) != "undefined" ) { parent.console_blink_r = thevalue ; }
						if ( thealert ) { do_alert( 1, "Update Success" ) ; }
					}
					else
						do_alert( 0, "Error: Could not update value.  Please try again." ) ;
				}
			});
		}
	}

	function toggle_dn_always( thevalue )
	{
		if ( parseInt( global_dn_always ) != parseInt( thevalue ) )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/chat_actions_op_ext.php",
				data: "action=dn_always&value="+thevalue+"&"+unixtime(),
				success: function(data){
					eval(data) ;

					if ( json_data.status )
					{
						global_dn_always = thevalue ;
						if ( typeof( parent.isop ) != "undefined" ) { parent.dn_always = thevalue ; }
						do_alert_div( base_url, 1, "Update Success" ) ;
						setTimeout( function(){ $('#div_alert').fadeOut("fast") }, 1500 ) ;
					}
					else
						do_alert( 0, "Error: Could not update value.  Please try again." ) ;
				}
			});
		}
		else
			do_alert( 1, "Update Success" ) ;
	}

	function update_volume( thevalue )
	{
		var sound = $('#sound1').val() ;

		sound_volume = thevalue ;
		if ( typeof( parent.isop ) != "undefined" ) { parent.sound_volume = sound_volume ; }

		$("input[name=volume][value='"+thevalue+"']").prop("checked", true) ;

		clear_sound('new_request') ; parent.clear_sound('new_request') ;
		play_sound(0, 'new_request', 'new_request_'+sound) ;
		if ( phplive_session_support ) { try { localStorage.setItem( "volume_newrequest", sound_volume ) ; } catch (error) {} }

		do_alert( 1, "Update Success" ) ;
	}

	function update_mobile_push( thevalue )
	{
		<?php if ( $mapp_enabled ): ?>
		if ( global_mobile_push != thevalue )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/chat_actions_op_ext.php",
				data: "action=update_mobile_push&value="+thevalue+"&"+unixtime(),
				success: function(data){
					eval(data) ;

					if ( json_data.status )
					{
						global_mobile_push = thevalue ;
						do_alert( 1, "Update Success" ) ;
					}
					else
						do_alert( 0, "Error: Could not update value.  Please try again. [e33]" ) ;
				}
			});
		}
		else
			do_alert( 1, "Update Success" ) ;
		<?php else: ?>
		$('#mobile_push_off').prop('checked', true) ;
		$('#div_mobileapp_enable').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		<?php endif ; ?>
	}
	
	function scroll_to_site_id()
	{
		var pos = $("#div_mobileapp_siteid").position() ;

		$('html').animate({
			scrollTop: pos.top
		}, 1000, function() {
			$("#div_mobileapp_siteid").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		});
	}

	function do_reset()
	{
		$('#btn_reset').hide() ;
		$('#sound1').val(parent.sound_new_request) ;
		$('#sound2').val(parent.sound_new_text) ;

		demo_sound1(0) ;
	}

	var st_blink ;
	function example_blink()
	{
		parent.flash_console(0) ;

		if ( typeof( st_blink ) != "undefined" )
			clearTimeout( st_blink ) ;
		st_blink = setTimeout( function(){ parent.clear_flash_console() ; st_blink = undeefined ; }, 5000 ) ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ); ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="show_div('sounds')" id="menu_sounds">Sound Alerts</div>
			<div class="op_submenu" onClick="show_div('dn')" id="menu_dn">Desktop Notification</div>
			<?php if ( isset( $CONF['MAPP_KEY'] ) && $opinfo["mapper"] ): ?><div class="op_submenu" onClick="show_div('sms')" id="menu_sms">Mobile App Alert</div><?php endif ; ?>
			<div style="clear: both"></div>
		</div>

		<div id="op_sounds" style="display: none; margin-top: 25px;">
			<?php if ( !$console ): ?><div style="margin-bottom: 25px;">If sound settings are updated and the <a href="index.php?jump=online">operator chat console</a> is open, you will need to close the operator console and launch the operator console again for the updated changes to take effect on the operator console.</div><?php endif ; ?>

			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td valign="top" width="275">
					<div class="info_info" id="div_sound_alert_onoff">
						<div style=""><img src="../pics/icons/bell_start.png" width="16" height="16" border="0" alt=""> Chat request and response sound alert</div>
						<div style="margin-top: 10px;">
							<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#console_sound_on').prop('checked', true);toggle_console_sound(1);"><input type="radio" name="console_sound" id="console_sound_on" value=1 <?php echo $sound_on ?>> On</div>
							<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#console_sound_off').prop('checked', true);toggle_console_sound(0);"><input type="radio" name="console_sound" id="console_sound_off" value=0 <?php echo $sound_off ?>> Off</div>
							<div style="clear: both;"></div>
						</div>
					</div>
					<div style="margin-top: 15px;" class="info_info">
						<div>
							<div id="div_console_blink_alert" class="info_box" style="display: none; margin-bottom: 15px;">Console automatically set to "blink" if sound alert is off.</div>
							<div>
								<table cellspacing=0 cellpadding=0 border=0>
								<tr>
									<td>&bull;</td>
									<td style="padding-left: 5px;">Blink the operator console window for new <b>chat requests</b>. (<a href="JavaScript:void(0)" onClick="example_blink()">show example</a>)</td>
								</tr>
								</table>
							</div>
							<div style="margin-top: 10px;">
								<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#console_blink_on').prop('checked', true);toggle_console_blink(1, 1);"><input type="radio" name="console_blink" id="console_blink_on" value=1  <?php echo $blink_on ?>> On</div>
								<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#console_blink_off').prop('checked', true);toggle_console_blink(0, 1);"><input type="radio" name="console_blink" id="console_blink_off" value=0 <?php echo $blink_off ?>> Off</div>
								<div style="clear: both;"></div>
							</div>
						</div>

						<div style="margin-top: 25px;">
							<div>
								<table cellspacing=0 cellpadding=0 border=0>
								<tr>
									<td>&bull;</td>
									<td style="padding-left: 5px;">Blink the operator console window for new <b>chat responses</b>.</td>
								</tr>
								</table>
							</div>
							<div style="margin-top: 10px;">
								<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#console_blink_r_on').prop('checked', true);toggle_console_blink_r(1, 1);"><input type="radio" name="console_blink_r" id="console_blink_r_on" value=1  <?php echo $blink_r_on ?>> On</div>
								<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#console_blink_r_off').prop('checked', true);toggle_console_blink_r(0, 1);"><input type="radio" name="console_blink_r" id="console_blink_r_off" value=0 <?php echo $blink_r_off ?>> Off</div>
								<div style="clear: both;"></div>
							</div>
						</div>
					</div>
				</td>
				<td valign="top" style="padding-left: 50px;" id="td_sound">
					<form id="theform">
					<table cellspacing=0 cellpadding=2 border=0>
					<tr>
						<td class="td_dept_td">New chat request: </td>
						<td class="td_dept_td"><div style="margin-left: 15px;"><select name="sound1" id="sound1" onChange="demo_sound1(0);$('#btn_reset').show();">
							<?php
								$dir_sounds = opendir( "$CONF[DOCUMENT_ROOT]/media/" ) ;

								$sounds = $sounds_filter = Array() ;
								while ( $sound = readdir( $dir_sounds ) )
									$sounds[] = $sound ;
								closedir( $dir_sounds ) ;
								
								sort( $sounds, SORT_STRING ) ;
								for ( $c = 0; $c < count( $sounds ); ++$c )
								{
									$sound = $sounds[$c] ;

									if ( preg_match( "/[a-z]/i", $sound ) && preg_match( "/^new_request_/i", $sound ) )
									{
										$sound_temp = preg_replace( "/(new_request_)|(.swf)|(.mp3)/", "", $sound ) ;
										if ( !isset( $sounds_filter[$sound_temp] ) )
										{
											$sounds_filter[$sound_temp] = 1 ;
											$sound_display = ucwords( preg_replace( "/_/", " ", $sound_temp ) ) ;
											$selected = "" ;
											if ( $opinfo["sound1"] == $sound_temp )
												$selected = "selected" ;

											print "<option value=\"$sound_temp\" $selected>$sound_display</option>" ;
										}
									}
								}
							?>
							</select></div>
						</td>
						<td class="td_dept_td"><span style="cursor: pointer;" onClick="demo_sound1(1)" class="info_neutral">play sound</span></td>
						<td class="td_dept_td"><span style="cursor: pointer;" onClick="demo_sound1(0)" class="info_neutral">stop sound</span></td>
					</tr>
					<tr>
						<td class="td_dept_td"><div style="padding-top: 5px;">New chat response: </div></td>
						<td class="td_dept_td"><div style="padding-top: 5px; margin-left: 15px;"><select name="sound2" id="sound2" onChange="$('#btn_reset').show()">
							<?php
								$dir_sounds = opendir( "$CONF[DOCUMENT_ROOT]/media/" ) ;

								$sounds = $sounds_filter = Array() ;
								while ( $sound = readdir( $dir_sounds ) )
									$sounds[] = $sound ;
								closedir( $dir_sounds ) ;

								sort( $sounds, SORT_STRING ) ;
								for ( $c = 0; $c < count( $sounds ); ++$c )
								{
									$sound = $sounds[$c] ;

									if ( preg_match( "/[a-z]/i", $sound ) && preg_match( "/^new_text_/i", $sound ) )
									{
										$sound_temp = preg_replace( "/(new_text_)|(.swf)|(.mp3)/", "", $sound ) ;
										if ( !isset( $sounds_filter[$sound_temp] ) && ( $sound_temp != "default" ) && ( $sound_temp != "return" ) && ( $sound_temp != "sound_check" ) )
										{
											$sounds_filter[$sound_temp] = 1 ;
											// new sound for Android increased volume the default as of v.4.7.9.9.9.4
											if ( $sound_temp == "return_android" )
												$sound_display = "Return" ;
											else
												$sound_display = ucwords( preg_replace( "/_/", " ", $sound_temp ) ) ;

											$selected = "" ;
											if ( $opinfo["sound2"] == $sound_temp )
												$selected = "selected" ;

											print "<option value=\"$sound_temp\" $selected>$sound_display</option>" ;
										}
									}
								}
							?>
							</select></div>
						</td>
						<td class="td_dept_td"><span style="cursor: pointer;" onClick="demo_sound2()" class="info_neutral">play sound</span></td>
						<td class="td_dept_td">&nbsp;</td>
					</tr>
					<tr>
						<td></td>
						<td colspan=3 class="td_dept_td" style="border-bottom: 0px;">
							<div style="margin-left: 15px; padding-top: 15px;">
								<input type="button" value="Update Sound Alerts" onClick="update_sound()" class="btn">
								&nbsp; &nbsp; <button type="button" class="btn" onClick="do_reset()" id="btn_reset" style="display: none;">Reset</button>
							</div>
						</td>
					</tr>
					</table>
					</form>

					<div style="margin-top: 25px;">
						<table cellspacing=0 cellpadding=2 border=0>
						<tr>
							<td colspan="4">
								<div style="border-top: 1px solid #C9CFD6;">
									<table cellspacing=0 cellpadding=0 border=0>
									<tr id="tr_sound_volume" style="display: none;">
										<td class="td_dept_td_blank" nowrap>Sound Alert Volume: </td>
										<td class="td_dept_td_blank" nowrap>
											<div>
												<div class="li_op round" style="cursor: pointer;" onclick="$('#vol_1').prop('checked', true);update_volume(1);"><input type="radio" name="volume" id="vol_1" value="1"> 100%</div>
												<div class="li_op round" style="cursor: pointer;" onclick="$('#vol_75').prop('checked', true);update_volume(0.09);"><input type="radio" name="volume" id="vol_75" value="0.09"> 75%</div>
												<div class="li_op round" style="cursor: pointer;" onclick="$('#vol_50').prop('checked', true);update_volume(0.06);"><input type="radio" name="volume" id="vol_50" value="0.06"> 50%</div>
												<div class="li_op round" style="cursor: pointer;" onclick="$('#vol_25').prop('checked', true);update_volume(0.03);"><input type="radio" name="volume" id="vol_25" value="0.03"> 25%</div>
												<div style="clear: both;"></div>
											</div>
										</td>
									</tr>
									</table>
								</div>
							</td>
						</tr>
						</table>
					</div>
				</td>
			</tr>
			</table>
		</div>

		<div id="op_sms" style="display: none; margin-top: 25px;">
			<div class="edit_title"><img src="../pics/icons/mobile_alert_big.png" width="48" height="48" border="0" alt=""> Desktop Computer to Mobile App Push Alert</div>
			
			<div style="margin-top: 5px;">Utilize the Mobile App to receive new chat request push notification on your mobile device when logged in from a desktop computer.  This feature is only a new chat request push notification alert for situations you are away from the computer but have your mobile device with you.  You will need to return to the computer to service the new chat request.</div>

			<?php if ( isset( $CONF['MAPP_KEY'] ) && $CONF['MAPP_KEY'] ): ?>
				<div style="<?php echo ( $mapp_enabled ) ? "display: none;" : "" ; ?> margin-top: 10px;" class="info_warning" id="div_mobileapp_enable">
					<div class="edit_title">To activate this feature:</div>
					<ol style="">
						<li style="margin-top: 5px;"> Download and install the <a href="https://www.phplivesupport.com/r.php?r=mapp" target="_blank">Mobile App</a> on your mobile device.</li>
						<li style="margin-top: 5px;"> Activate the Mobile App with your <a href="JavaScript:void(0)" onClick='scroll_to_site_id()'>Site ID</a>.</li>
						<li style="margin-top: 5px;"> Log in from the Mobile App with your operator account login credentials.
							<div style="margin-top: 5px;"><b>NOTE:</b> The login process from the Mobile App will enable the ability to update the <a href="JavaScript:void(0)" onClick='$("#div_mobileapp_alert_onoff").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast");'>On/Off</a> setting on this page.</div></li>
						<li style="margin-top: 5px;"> Your operator account is now linked with the Mobile App. Logout from the Mobile App and close the Mobile App on your mobile device (place the app in background).</li>
						<li style="margin-top: 5px;">Log back in from a desktop computer.  You will now be able to update the <a href="JavaScript:void(0)" onClick='$("#div_mobileapp_alert_onoff").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast");'>On/Off</a> setting on this page.</li> 
					</ol>
				</div>

				<div style="margin-top: 10px;" class="info_neutral">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td width="16"><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""></td>
						<td style="padding-left: 5px;">This feature is a new chat request one time push notification alert to your mobile device when logged in from a desktop computer.</td>
					</tr>
					</table>
				</div>

				<div style="margin-top: 15px;" id="div_mobileapp_alert_onoff">
					<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#mobile_push_on').prop('checked', true);update_mobile_push(1);"><input type="radio" name="mobile_push" id="mobile_push_on" value=1 <?php echo ( $opinfo["sms"] == 1 ) ? "checked" : "" ?> > On</div>
					<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#mobile_push_off').prop('checked', true);update_mobile_push(0);"><input type="radio" name="mobile_push" id="mobile_push_off" value=0 <?php echo ( $opinfo["sms"] != 1 ) ? "checked" : "" ?>> Off</div>
					<div style="clear: both;"></div>
				</div>

				<?php
					if ( $opinfo["mapper"] && isset( $CONF['MAPP_KEY'] ) && $CONF['MAPP_KEY'] ):
					$mapp_key = isset( $CONF['MAPP_KEY'] ) ? $CONF['MAPP_KEY'] : "" ;
					$kpr = substr( $mapp_key, 0, 5 ) ; $kpo = substr( $mapp_key, 5, strlen( $mapp_key ) ) ;
				?>
				<div style="margin-top: 55px;" id="div_mobileapp_siteid">
					<div>To activate the Mobile App on your mobile device, the <b>Site ID</b> is:</div>
					<div style="margin-top: 15px; font-size: 18px; font-weight: bold;">
						<b>Mobile App Site ID:</b> <span id="div_kpr" class="info_box"><?php echo $kpr ?></span> - <span id="div_kpo" class="info_box"><?php echo $kpo ?></span>
					</div>
				</div>
				<?php endif ; ?>
			<?php else: ?>
				<div style="margin-top: 10px;" class="info_error">
					<span><img src="../pics/icons/warning.png" width="16" height="16" border="0" alt=""> Mobile App access has not been enabled.</span>  Please contact the <b>Setup Admin</b> to generate the <b>Mobile App Site ID</b> at:
					<div style="margin-top: 15px;"><code>Setup Admin &gt; Settings &gt; Mobile App</code></div>
					<div style="margin-top: 15px;">After the Site ID has been generated, <a href="JavaScript:void(0)" onClick="location.href='notifications.php?jump=sms&console=<?php echo $console ?>'" style="color: #FFFFFF;">refresh this page</a> to view this area.</div>
				</div>
			<?php endif ; ?>

		</div>

		<div id="op_dn" style="display: none; margin-top: 25px;">
			Display a new chat request/response notification on the desktop.  Desktop notification is currently available for <a href="https://www.google.com/chrome" target="_blank">Google Chrome</a>, <a href="http://www.firefox.com" target="_blank">Firefox</a> and <a href="https://www.microsoft.com/en-us/edge" target="_blank">IE Edge</a>.

			<form>
			<table cellspacing=0 cellpadding=2 border=0 width="100%" style="margin-top: 25px;">
			<tr>
				<td >
					<div id="dn_unavailable" style="display: none;"><img src="../pics/icons/warning.png" width="16" height="16" border="0" alt=""> Desktop Notification is not supported for this browser type.</div>
					<div id="dn_request" style="display: none;">
						<div class="info_good"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""> Good news!  Desktop notification is supported for this browser.</div>

						<div style="margin-top: 15px;">To enable the new chat request desktop notification, click on the "Request Notification" button.  When alerted to "Allow" or "Deny" the request, click the "Allow" option.</div>

						<div style="margin-top: 15px;"><input type="button" onClick="dn_pre_request()" value="Request Notification" class="btn"></div>
					</div>
					<div id="dn_firefox" style="display: none;">
						<div class="info_good"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""> Good news!  Desktop notification is supported for this browser.</div>

						<div style="margin-top: 15px;">However, the system has detected an outdated Firefox version.  An upgrade of the browser is needed.  After the browser upgrade, visit this area again to enable the feature. <a href="http://www.firefox.com" target="_blank">Firefox.com</a></div>
					</div>
					<div id="dn_winapp" style="display: none;" class="info_misc">
						For WinApp, the Desktop Notification feature can be accessed at the WinApp "Menu" &gt; "Settings" &gt; "Desktop Notification"
					</div>
					<div id="dn_enabled" style="display: none;">
						<table cellspacing=0 cellpadding=0 border=0 width="100%">
						<tr>
							<td valign="top" style="width: 250px;">
								<div style="" class="info_info">
									<div id="div_alert" style="display: none; margin-bottom: 15px;"></div>
									<div class="info_neutral">
										<table cellspacing=0 cellpadding=2 border=0>
										<tr>
											<td><input type="radio" name="dn_always" id="dn_always_on" value=1 <?php echo $dn_always_on ?> onClick="toggle_dn_always(1, 1)"></td>
											<td>Always display the Desktop Notification</td>
										</tr>
										</table>
									</div>
									<div class="info_neutral" style="margin-top: 15px;">
										<table cellspacing=0 cellpadding=2 border=0>
										<tr>
											<td><input type="radio" name="dn_always" id="dn_always_off" value=0 <?php echo $dn_always_off ?> onClick="toggle_dn_always(0, 1)"></td>
											<td>Only display the Desktop Notification if the operator console window is out of focus</td>
										</tr>
										</table>
									</div>
								</div>
							</td>
							<td valign="top" style="padding-left: 50px;">
								<div class="info_good edit_title" id="dn_enabled_on" style="display: none; text-align: center;"><span class="info_white">New Chat Request</span> desktop notification alert is on. <button type="button" onClick="dn_toggle(0)" class="btn">Switch Off</button></div>

								<div class="info_error edit_title" id="dn_enabled_off" style="display: none; text-align: center;"><span class="info_white">New Chat Request</span> desktop notification alert is off.  <button type="button" onClick="dn_toggle(1)" class="btn">Switch On</button></div>

								<div style="margin-top: 25px;">
									<div class="info_good edit_title" id="dn_enabled_response_on" style="display: none; text-align: center;"><span class="info_white">Chat Response</span> desktop notification alert is on. <button type="button" onClick="dn_toggle_response(0)" class="btn">Switch Off</button></div>
									<div class="info_error edit_title" id="dn_enabled_response_off" style="text-align: center;"><span class="info_white">Chat Response</span> desktop notification alert is off. <button type="button" onClick="dn_toggle_response(1)" class="btn">Switch On</button></div>
								</div>

								<div style="margin-top: 25px; text-align: center;">
									<span class="info_misc"><a href="JavaScript:void(0)" onClick="dn_show( 'new_chat', '<?php echo time() ?>', 'Demo Visitor', 'This is a demo chat request question.', 45000 )">Click here to display a demo desktop notification alert.</a></span>
								</div>
							</td>
						</tr>
						</table>
					</div>
					<div id="dn_chrome" style="display: none;">
						<span class="info_error">Desktop notification is not available on non-secure URL for this browser.</span>

						<div style="margin-top: 25px;">For Google Chrome and Edge Chromium browsers, the desktop notification will only function on HTTPS secure URLs.</div>
						<div style="margin-top: 5px;">Please access your system on an HTTPS URL,  example: <code><span id="span_https_url"></span></code></div>
						
						<div style="margin-top: 25px;">If your server does not have HTTPS capability, try using <a href="http://www.firefox.com" target="_blank">Firefox</a> browser.</div>

						<div style="margin-top: 25px;"><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> If you feel this error message is incorrect, try to <a href="https://www.phplivesupport.com/help_desk.php?docid=18" target="_blank">Reset Desktop Notification Settings</a> and <a href="./notifications.php?jump=dn&console=<?php echo $console ?>&<?php echo time() ?>">reload this page</a> to re-request permission.</div>
					</div>
					<div id="dn_disabled" style="display: none;">
						<?php
							// use include due to iframe scenario for demo for custom message
							include_once( "./inc_dn.php" ) ;
						?>
					</div>
					<div id="dn_insecure" style="display: none;">
						<span class="info_error">Desktop notification manual enable required.</span>

						<div style="margin-top: 25px;">The system has detected an insecure HTTP URL.  However, desktop notification may still be available for this browser.  You will need to manually enable the desktop notification.  Please visit the <a href="https://www.phplivesupport.com/knowledge_base.php?docid=337" target="_blank">Enable Desktop Notification Manually</a> documentation.</div>
					</div>
				</td>
			</tr>
			</table>
			</form>
		</div>

<?php include_once( "./inc_footer.php" ); ?>
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

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$console = Util_Format_Sanatize( Util_Format_GetVar( "console" ), "n" ) ;
	$wp = Util_Format_Sanatize( Util_Format_GetVar( "wp" ), "n" ) ;
	$pr = Util_Format_Sanatize( Util_Format_GetVar( "pr" ), "n" ) ;
	$auto = Util_Format_Sanatize( Util_Format_GetVar( "auto" ), "n" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ; if ( !$jump ) { $jump = "pic" ; }
	$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
	$menu = "settings" ; $error = "" ;
	// for image cropper, ses = opid for security check
	$ses = isset( $_COOKIE["cS"] ) ? Util_Format_Sanatize( $_COOKIE["cS"], "ln" ) : "" ;

	if ( $action === "update" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_File.php" ) ;

		LIST( $error, $filename ) = Util_Upload_File( "profile", $opinfo["opID"] ) ;
	}
	else if ( $action === "update_password" )
	{
		$password = Util_Format_Sanatize( Util_Format_GetVar( "password" ), "ln" ) ;
		$npassword = Util_Format_Sanatize( Util_Format_GetVar( "npassword" ), "ln" ) ;
		$vpassword = Util_Format_Sanatize( Util_Format_GetVar( "vpassword" ), "ln" ) ;
		$md5_password = Util_Format_Sanatize( Util_Format_GetVar( "md5_password" ), "ln" ) ;
		$md5_cookie = md5( $_COOKIE["cS"] ) ;

		// phplive_pr = password reset flag
		if ( ( $password == md5( "c4ca4238a0b923820dcc509a6f75849b".$md5_cookie ) ) && ( isset( $_COOKIE["phplive_pr"] ) && ( $_COOKIE["phplive_pr"] == md5( "phplive".substr( md5( $CONF['SALT'].$opinfo["password"] ), 6, 12 ) ) ) ) ) { $password = md5( $opinfo["password"].$md5_cookie ) ; }
		if ( $md5_password == md5( $npassword.$vpassword.$md5_cookie ) )
		{
			if ( $password != md5( $opinfo["password"].$md5_cookie ) )
				$json_data = "json_data = { \"status\": 0, \"error\": \"Current Password is invalid.\" };" ;
			else if ( $vpassword != md5( $npassword.$md5_cookie ) )
				$json_data = "json_data = { \"status\": 0, \"error\": \"New and Verify Password does not match.\" };" ;
			else if ( $password == $vpassword )
				$json_data = "json_data = { \"status\": 0, \"error\": \"New Password must be different then the Current Password.\" };" ;
			else
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
				Ops_update_OpValue( $dbh, $opinfo["opID"], "password", $npassword ) ;
				Util_Format_SetCookie( "phplive_pr", "", -1, "/", "", $PHPLIVE_SECURE ) ;
				$json_data = "json_data = { \"status\": 1 };" ;
			}
		}
		else { $json_data = "json_data = { \"status\": 0, \"error\": \"Could not update password.  Please try again.\" };" ; }

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	else if ( $action === "update_nsleep" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;
		Ops_update_OpVarValue( $dbh, $opinfo["opID"], "nsleep", $value ) ;
		$json_data = "json_data = { \"status\": 1 };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	else if ( $action === "update_shorts" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;
		Ops_update_OpVarValue( $dbh, $opinfo["opID"], "shorts", $value ) ;
		$json_data = "json_data = { \"status\": 1 };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	else if ( $action === "success" )
	{
		// sucess action is an indicator to show the success alert as well
		// as bypass the refreshing of the operator console
	}
	else { $error = "invalid action" ; }

	$opvars = Ops_get_OpVars( $dbh, $opinfo["opID"] ) ;
	$auto_login_enabled = ( isset( $_COOKIE["cAT"] ) && $_COOKIE["cAT"] ) ? 1 : 0 ;
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
<script data-cfasync="false" type="text/javascript" src="../js/dn.js?<?php echo $VERSION ?>"></script>

<link rel="stylesheet" href="../addons/cropper/css/cropper.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../addons/cropper/js/bootstrap.bundle.min.js?<?php echo $VERSION ?>" crossorigin="anonymous"></script>
<script data-cfasync="false" type="text/javascript" src="../addons/cropper/js/cropper.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../addons/cropper/js/init.js?<?php echo filemtime( "../addons/cropper/js/init.js" ) ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../addons/cropper/js/canvas-to-blob.min.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var opwin ;
	var menu ;
	var dn = dn_check() ;
	var embed = 0 ; var file_check = 0 ;
	var pie = <?php echo ( preg_match( "/(MSIE 6)|(MSIE 7)|(MSIE 8)/i", $agent ) ) ? 1 : 0 ; ?> ;
	var wp = ( ( typeof( window.external ) != "undefined" ) && ('wp_total_visitors' in window.external) ) ? 1 : 0 ;
	var opid = "<?php echo $ses ?>" ;
	var is_console = <?php echo $console ?> ; var auto = <?php echo $auto ?> ; // cropper dependant

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu_op() ;
		toggle_menu_op( "<?php echo $menu ?>" ) ;
		show_div( "<?php echo $jump ?>" ) ;

		<?php if ( $action && $error ): ?>
		do_alert_div( "..", 0, "<?php echo $error ?>" ) ;
		<?php elseif ( $action ): ?>
		do_alert(1, "Update Success" ) ;
		<?php endif ; ?>
		
		if ( typeof( parent.isop ) != "undefined" )
		{
			if ( parent.$('#img_profile_pic').attr('src') != $('#img_profile_pic').attr('src') )
			{
				parent.profile_pic_url = $('#img_profile_pic').attr('src') ;
				parent.$('#img_profile_pic').attr('src', $('#img_profile_pic').attr('src') ) ;
			}
		}

		$('#op_sleep_browser').show() ;
		if ( wp || !pie ) { $('#div_sleep_lock_onoff').show() ; }
		else { $('#div_sleep_lock_nope').show() ; }

		if ( ( typeof( parent.isop ) != "undefined" ) && ( ( "<?php echo $action ?>" == "update" ) || ( "<?php echo $action ?>" == "clear" ) ) && ( "<?php echo $error ?>" == "" ) )
		{
			if ( "<?php echo $action ?>" == "update" )
			{
				//
			}
			else
				parent.refresh_console(0) ;
		}
		else if ( typeof( parent.isop ) != "undefined" ) { parent.init_extra_loaded() ; }
		if ( wp ) { $('#chat_text_powered').hide() ; }

		<?php
			$pr_process = 0 ;
			if ( $pr && ( isset( $_COOKIE["phplive_pr"] ) && ( $_COOKIE["phplive_pr"] == md5( "phplive".substr( md5( $CONF['SALT'].$opinfo["password"] ), 6, 12 ) ) ) ) ):
			$pr_process = 1 ;
		?>
		$('body').css({'overflow':'hidden'}) ;
		$('#div_update_password').show() ;
		<?php endif ; ?>

		if ( browser_filter && !wp )
			init_crop() ;
		else
			$('#div_browser').show() ;
	});

	function show_div( thediv )
	{
		var divs = Array( "pic", "shorts", "auto", "password", "sleep" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#op_'+divs[c]).hide() ;
			$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('#op_'+thediv).show() ;
		$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
	}

	function update_password()
	{
		if ( $('#password').val() == "" )
			do_alert( 0, "Please provide the Current Password." ) ;
		else if ( $('#npassword').val() == "" )
			do_alert( 0, "Please provide the New Password." ) ;
		else if ( $('#npassword').val() != $('#vpassword').val() )
			do_alert( 0, "New and Verify Password does not match." ) ;
		else if ( $('#npassword').val().length < 6 )
			do_alert( 0, "New Password must be at least 6 characters." ) ;
		else
		{
			var json_data = new Object ;
			var unique = unixtime() ;

			var password = phplive_md5( phplive_md5( $('#password').val() )+"<?php echo md5( $_COOKIE["cS"] ) ?>" ) ;
			var npassword = phplive_md5( $('#npassword').val() ) ;
			var vpassword = phplive_md5( phplive_md5( $('#npassword').val() )+"<?php echo md5( $_COOKIE["cS"] ) ?>" ) ;
			var md5_password = phplive_md5( npassword+vpassword+"<?php echo md5( $_COOKIE["cS"] ) ?>" ) ;

			$.ajax({
			type: "POST",
			url: "settings.php",
			data: "action=update_password&password="+password+"&npassword="+npassword+"&vpassword="+vpassword+"&md5_password="+md5_password+"&"+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					$('#password').val('') ;
					$('#npassword').val('') ;
					$('#vpassword').val('') ;

					<?php if ( $pr_process ): ?>
					$('#div_update_password_password').hide() ;
					$('#div_update_password_success').show() ;
					<?php else: ?>
					do_alert( 1, "Password Updated" ) ;
					<?php endif ; ?>
				}
				else
					do_alert( 0, json_data.error ) ;

			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
			} });
		}
	}

	function update_auto_login( thevalue )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "../index.php",
			data: "action=update_auto_login&value="+thevalue+"&"+unixtime(),
			success: function(data){
				eval(data) ;

				if ( json_data.status )
					do_alert( 1, "Update Success" ) ;
				else
					do_alert( 0, "Error processing request.  Please try again." ) ;
			}
		});
	}

	function update_nsleep( thevalue )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "settings.php",
			data: "action=update_nsleep&value="+thevalue+"&"+unixtime(),
			success: function(data){
				eval(data) ;

				if ( json_data.status )
				{
					if ( typeof( parent.isop ) != "undefined" )
						parent.refresh_console(0) ;
					else
						do_alert( 1, "Update Success" ) ;
				}
				else
					do_alert( 0, "Error processing request.  Please try again." ) ;
			}
		});
	}

	function update_shorts( thevalue )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "settings.php",
			data: "action=update_shorts&value="+thevalue+"&"+unixtime(),
			success: function(data){
				eval(data) ;

				if ( json_data.status )
				{
					do_alert( 1, "Update Success" ) ;
					if ( typeof( parent.isop ) != "undefined" )
						parent.shortcut_enabled = thevalue ;
				}
				else
					do_alert( 0, "Error processing request.  Please try again." ) ;
			}
		});
	}

	function init_file_upload()
	{
		// to-do (true method to suppress warning unreachable code)
		if ( true )
			return true ;
		else
		{
			var input, file ;
			if ( !window.FileReader ) { file_check = 1 ; return false ; } input = document.getElementById('profile') ;
			if ( !input ) { do_alert_div( "..", 0, "Could not find the file destination." ) ; }
			else if ( !input.files ) { file_check = 1 ; return false ; }
			else if ( !input.files[0] ) { do_alert_div( "..", 0, "Nothing to upload." ) ; }
			else
			{
				file = input.files[0] ;
				do_alert_div( "..", 0, "File " + file.name + " is " + file.size + " bytes in size" ) ;
			}
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ); ?>
		<form method="POST" action="settings.php" enctype="multipart/form-data">
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="MAX_FILE_SIZE" value="3000000">

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="show_div('pic')" id="menu_pic">Profile Picture</div>
			<div class="op_submenu" onClick="show_div('shorts')" id="menu_shorts">Chat Session Shortcuts</div>
			<div class="op_submenu" onClick="show_div('auto')" id="menu_auto">Automatic Login</div>
			<div class="op_submenu" onClick="show_div('sleep')" id="menu_sleep">Computer Sleep Lock</div>
			<div class="op_submenu" onClick="show_div('password')" id="menu_password"><img src="../pics/icons/key.png" width="12" height="12" border="0" alt=""> Password</div>
			<div style="clear: both"></div>
		</div>

		<div id="op_pic" style="display: none; margin-top: 25px;">

			<div style="margin-top: 15px;">
				<div class="info_white" style="padding: 15px;">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td><img src="<?php print Util_Upload_GetLogo( "profile", $opinfo["opID"] ) ?>" width="55" height="55" border=0 style="border: 1px solid #DFDFDF; border-radius: 50%;" id="img_profile_pic"></td>
						<td style="padding-left: 15px;">
							Chat Operator
							<div style="margin-top: 5px;" class="edit_title">
								<big><?php echo $opinfo["name"] ?></big> <span style="font-size: 12px; font-weight: normal;">&lt;<?php echo $opinfo["email"] ?>&gt;</span>
							</div>
						</td>
					</tr>
					</table>
				</div>
			</div>
			<div style="margin-top: 25px;">
				<?php if ( $opinfo["pic"] ): ?>
				<div><span class="info_good">Your profile picture will be displayed to the visitor during a chat session.</span></div>
				<?php else: ?>
				<div><span class="info_error">Your profile picture is not visible to the visitor.  To update the visible setting, please contact the Setup Admin.</span></div>
				<?php endif ; ?>

				<div id="div_alert" style="display: none; margin-top: 15px; margin-bottom: 25px;"></div>
				<?php if ( isset( $opvars["pic_edit"] ) && $opvars["pic_edit"] ): ?>
				<div style="margin-top: 35px;">
					<div id="div_alert" style="display: none; margin-top: 15px; margin-bottom: 25px;"></div>
					<div style="margin-top: 25px;">
						<div><input type="file" id="input_profile" name="profile" size="30" onChange="init_file_upload()"></div>
						<div id="div_cropper_loading" style="display: none; margin-top: 15px;">loading...</div>
						<div id="div_browser" style="display: none; margin-top: 15px; text-align: justify;">
							<input type="submit" value="Upload Image" style="margin-top: 10px;" class="btn">
						</div>
					</div>
				</div>
				<?php else: ?>
				<div style="margin-top: 25px;"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> To change your profile picture, please contact the Setup Admin.</div>
				<div style="display: none;"><input type="file" id="input_profile" name="profile" size="30" onChange="init_file_upload()"></div>
				<?php endif ; ?>
			</div>
		</div>

		<div id="op_shorts" style="display: none; margin-top: 25px;">

			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td valign="top" width="250">
						<div style="text-align: justify;">Shortcut commands can be entered at the operator console textarea during a chat session.  Shortcut commands begin with a forward slash (/) character.</div>
						<div style="margin-top: 10px;">
							<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#shorts_on').prop('checked', true);update_shorts(1);"><input type="radio" name="shorts" id="shorts_on" value=1 <?php echo ( isset( $opvars["shorts"] ) && $opvars["shorts"] ) ? "checked" : "" ?> > On</div>
							<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#shorts_off').prop('checked', true);update_shorts(0);"><input type="radio" name="shorts" id="shorts_off" value=0 <?php echo ( !isset( $opvars["shorts"] ) || !$opvars["shorts"] ) ? "checked" : "" ?>> Off</div>
							<div style="clear: both;"></div>
						</div>
					</td>
					<td valign="top" style="padding-left: 50px;">
						<div><span class="info_neutral round_bottom_none">Current list of shortcut commands:</span></div>
						<div style="margin-top: 10px;">
							<table cellspacing=1 cellpadding=5 border=0>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /accept </td>
								<td style="background: #EFF0F1;">accept the chat request</td>
							</tr>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /decline </td>
								<td style="background: #EFF0F1;">decline the chat request</td>
							</tr>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /close </td>
								<td style="background: #EFF0F1;">close (disconnect) the current chat session</td>
							</tr>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /exit </td>
								<td style="background: #EFF0F1;">close (disconnect) the current chat session</td>
							</tr>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /n </td>
								<td style="background: #EFF0F1;">toggle to the next chat session</td>
							</tr>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /t </td>
								<td style="background: #EFF0F1;">toggle to the next chat session</td>
							</tr>
							<tr>
								<td style="background: #DEDEDE; font-weight: bold;"> /nolink </td>
								<td style="background: #EFF0F1;">do not autolink URLs</td>
							</tr>
							</table>
						</div>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div id="op_auto" style="display: none; margin-top: 25px;">

			<div style="margin-top: 15px;">
				Automatically login without providing the credentials.

				<div style="margin-top: 10px;" class="info_neutral">
					<b>Keep in mind:</b> The "Automatic Login" (Remember me) is browser and computer specific.
					<ul style="margin-top: 10px;">For example:
						<li style="margin-top: 5px;"> If you enable the "Automatic Login" on a computer using Google Chrome browser, it will only function on that computer using Google Chrome browser.  Firefox or other browsers will require the login credentials.  If you login using Firefox browser, it will clear the "Automatic Login" on Google Chrome browser.</li>
						<li style="margin-top: 5px;"> For security, if you enabled the "Automatic Login" on <b>computer A</b> and then you login from <b>computer B</b>, it will clear the "Automatic Login" at computer A and all other computers that may have the "Automatic Login" enabled.</li>
					</ul>
				</div>

				<div style="margin-top: 15px;">
					<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#auto_login_on').prop('checked', true);update_auto_login(1);"><input type="radio" name="auto_login" id="auto_login_on" value=1 <?php echo ( $auto_login_enabled ) ? "checked" : "" ?> > On</div>
					<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#auto_login_off').prop('checked', true);update_auto_login(0);"><input type="radio" name="auto_login" id="auto_login_off" value=0 <?php echo ( !$auto_login_enabled ) ? "checked" : "" ?>> Off</div>
					<div style="clear: both;"></div>
				</div>
			</div>
		</div>

		<div id="op_sleep" style="display: none; margin-top: 25px;">

			<div id="op_sleep_browser" style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td valign="top" width="250">
						<div style="text-align: justify;">Prevent the computer from powering down to "system sleep".  For most modern computers, "system sleep" mode may pause network connections and web browser processes.  This will cause the operator console to go offline and unable to receive new chat requests.  It is recommended to keep this setting to "On".</div>

						<div id="div_sleep_lock_onoff" style="display: none; margin-top: 10px;">
							<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#nsleep_on').prop('checked', true);update_nsleep(1);"><input type="radio" name="nsleep" id="nsleep_on" value=1 <?php echo ( $opvars["nsleep"] ) ? "checked" : "" ?> > On</div>
							<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#nsleep_off').prop('checked', true);update_nsleep(0);"><input type="radio" name="nsleep" id="nsleep_off" value=0 <?php echo ( !$opvars["nsleep"] ) ? "checked" : "" ?>> Off</div>
							<div style="clear: both;"></div>
						</div>
						<div id="div_sleep_lock_nope" style="display: none; margin-top: 10px;"><span class="info_error">Sleep Lock is not supported for this browser.</span></div>

					</td>
					<td valign="top" style="padding-left: 50px;">
						<div><span class="info_neutral round_bottom_none"><i>Sleep Lock</i> will have the following lock/prevent behaviors depending on the brower type:</span></div>
						<div style="margin-top: 10px;">
							<table cellspacing=1 cellpadding=5 border=0 width="100%">
							<tr>
								<td bgColor="#DEDEDE" align="center"><b>Browser</b></td>
								<td bgColor="#DEDEDE" align="center">Screen Saver</td>
								<td bgColor="#DEDEDE" align="center">Screen Shutdown</td>
								<td bgColor="#DEDEDE" align="center">Sleep Power Down</td>
							</tr>
							<tr>
								<td bgColor="#EFF0F1"><img src="../themes/default/browsers/Chrome.png" width="16" height="16" border="0" alt="Chrome" title="Chrome"> Chrome</td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
								<td bgColor="#EFF0F1" align="center">&nbsp;</td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
							</tr>
							<tr>
								<td bgColor="#EFF0F1"><img src="../themes/default/browsers/Firefox.png" width="16" height="16" border="0" alt="Firefox" title="Firefox"> Firefox</td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
							</tr>
							<tr>
								<td bgColor="#EFF0F1"><img src="../themes/default/browsers/IE.png" width="16" height="16" border="0" alt="IE (all)" title="IE (all)"> IE (9+)</td>
								<td bgColor="#EFF0F1" align="center">&nbsp;</td>
								<td bgColor="#EFF0F1" align="center">&nbsp;</td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
							</tr>
							<tr>
								<td bgColor="#EFF0F1"><img src="../themes/default/browsers/IE.png" width="16" height="16" border="0" alt="IE (all)" title="IE (all)"> WinApp</td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
							</tr>
							<tr>
								<td bgColor="#EFF0F1"><img src="../themes/default/browsers/Safari.png" width="16" height="16" border="0" alt="Safari" title="Safari"> Safari</td>
								<td bgColor="#EFF0F1" align="center">&nbsp;</td>
								<td bgColor="#EFF0F1" align="center">&nbsp;</td>
								<td bgColor="#EFF0F1" align="center"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""></td>
							</tr>
							</table>
						</div>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<?php if ( !$pr_process ): ?>
		<div id="op_password" style="display: none; margin-top: 25px;">
			<div style="margin-top: 15px;">Current Password</div>
			<div><input type="password" class="input" name="password" id="password" size="30" autocomplete="off"></div>

			<div style="margin-top: 25px;">
				<div style="background: url( ../pics/dotted_line.png ) repeat-x; height: 25px;"></div>

				<div style="font-size: 14px; font-weight: bold;">Update Password</div>
				<div style="margin-top: 5px;">Password must be at least 6 characters and can be a combination of letters, numbers and any special characters.</div>
				<div style="margin-top: 15px;">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td valign="bottom">
							<div>New Password</div>
							<div><input type="password" class="input" name="npassword" id="npassword" size="30" onKeyPress="return noquotes(event)" autocomplete="off"></div>
						</td>
						<td valign="bottom" style="padding-left: 25px;">
							<div>Verify New Password</div>
							<div><input type="password" class="input" name="vpassword" id="vpassword" size="30" onKeyPress="return noquotes(event)" autocomplete="off"></div>
						</td>
					</tr>
					</table>
				</div>
				<div style="margin-top: 25px; background: url( ../pics/dotted_line.png ) repeat-x; height: 25px;"></div>
			</div>

			<div style="margin-top: 5px;"><input type="button" value="Update Password" onClick="update_password()" class="btn"></div>
		</div>
		<?php endif ; ?>

<?php if ( $pr_process ): ?>
<div id="div_update_password" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; padding-top: 80px; z-index: 50; background: url(../themes/initiate/bg_trans_darker.png) repeat;">
	<div class="info_info" style="width: 500px; height: 350px; margin: 0 auto; padding: 10px;">

		<div id="div_update_password_password">
			<div class="td_dept_td">
				<div class="edit_title">Update Password</div>
				<div style="margin-top: 5px; text-align: justify;">Before continuing, update your password.  Password must be at least 6 characters and can be a combination of letters, numbers and any special characters.</div>
			</div>
			
			<div>
				<table cellspacing=0 cellpadding=0 border=0>
				<tr> 
					<td class="td_dept_td" width="120">New Password</td> 
					<td class="td_dept_td"><input type="password" class="input" size="35" id="npassword"></td> 
				</tr>
				<tr>
					<td class="td_dept_td" width="120" nowrap>Verify New Password</td> 
					<td class="td_dept_td"><input type="password" class="input" size="35" id="vpassword"></td> 
				</tr>
				<tr>
					<td class="td_dept_td">&nbsp;</td>
					<td class="td_dept_td">
						<input type="hidden" class="input" size="35" id="password" value="1">
						<button type="button" onClick="update_password()" class="btn">Update Password</button> &nbsp; or &nbsp; <a href="../logout.php?action=logout">return to the login page</a>
					</td>
				</tr>
				</table>
			</div>
		</div>
		<div id="div_update_password_success" style="display: none;" class="td_dept_td">
			<div class="info_good title" style="text-shadow: none;"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""> Password has been updated.</div>
			<div style="margin-top: 25px;">

				<a href="index.php">Close window</a>

			</div>
		</div>

	</div>
</div>
<?php endif ; ?>

<?php include_once( "../addons/cropper/inc_crop.php" ) ; ?>
</form>
<?php include_once( "./inc_footer.php" ); ?>

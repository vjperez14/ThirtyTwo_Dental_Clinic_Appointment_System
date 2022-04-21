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

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$url = Util_Format_Sanatize( Util_Format_GetVar( "url" ), "url" ) ;

	if ( ( $action === "pre_register" ) && $url )
	{
		if ( function_exists( "curl_init" ) && function_exists( "curl_exec" ) )
		{
			$url = urlencode( $url ) ;
			$request = curl_init( "https://www.phplivesupport.com/mapp/Util/system_validate.php" ) ;
			curl_setopt( $request, CURLOPT_RETURNTRANSFER, true ) ;
			curl_setopt( $request, CURLOPT_CUSTOMREQUEST, "POST") ;
			curl_setopt( $request, CURLOPT_POSTFIELDS, Array( "u"=>"$url" ) ) ;
			if ( !isset( $VARS_SET_VERIFYPEER ) || ( $VARS_SET_VERIFYPEER == 1 ) )
			{
				curl_setopt( $request, CURLOPT_SSL_VERIFYPEER, true ) ;
				curl_setopt( $request, CURLOPT_CAINFO, "$CONF[DOCUMENT_ROOT]/mapp/API/cacert.pem" ) ;
			}
			else { curl_setopt( $request, CURLOPT_SSL_VERIFYPEER, false ) ; }
			$response = preg_replace( "/\n/", "", curl_exec( $request ) ) ;
			$curl_errno = curl_errno( $request ) ;
			$status = curl_getinfo( $request, CURLINFO_HTTP_CODE ) ;
			curl_close( $request ) ;

			if ( ( $response == 0 ) && ( $status != "404" ) && !$curl_errno ) { $error = "X-Frame-Options detected.  For more information <a href='http://www.phplivesupport.com/r.php?r=xframe' target='_blank' style='color: #FFFFFF;'>CLICK HERE to view the X-Frame-Options documentation</a>." ; $json_data = "json_data = { \"status\": 0, \"error\": \"$error\" };" ; }
			else if ( $curl_errno == 35 ) { $json_data = "json_data = { \"status\": 0, \"error\": \"OpenSSL upgrade is required.  <a href='https://www.openssl.org' target='_blank' style='color: #FFFFFF;'>Open SSL</a> must be v.0.9.8o or greater.\" };" ; }
			else if ( $response == 1 ) { $json_data = "json_data = { \"status\": 1 };" ; }
			else if ( $curl_errno ) { $json_data = "json_data = { \"status\": 0, \"error\": \"CURL error: $curl_errno\" };" ; }
			else if ( $status == "404" ) { $json_data = "json_data = { \"status\": 0, \"error\": \"Registeration server is temporarily down.  Please try again at a later time.\" };" ; }
			else { $json_data = "json_data = { \"status\": 0, \"error\": \"Invalid URL.\" };" ; }
		}
		else
		{
			$json_data = "json_data = { \"status\": 0, \"error\": \"Server PHP does not support <a href='http://php.net/manual/en/book.curl.php' target='_blank' style='color: #FFFFFF;'>cURL</a>.  Contact your server admin to enable PHP cURL support to utilize the Mobile App feature.  Also check the 'curl_exec' function is not disabled in the php.ini file.\" };" ;
		}

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	else if ( $action === "update_idle_hours" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$hours = Util_Format_Sanatize( Util_Format_GetVar( "hours" ), "n" ) ; if ( !$hours ) { $hours = 10 ; }

		Util_Vals_WriteToFile( "MOBILE_EXPIRED_OPS", $hours ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
		
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	} $rkey = Util_Format_Sanatize( Util_Format_GetVar( "rkey" ), "ln" ) ;
	if ( $rkey && ( $rkey == md5($KEY.md5($CONF['MAPP_KEY'])) ) && !isset( $VALS["IOSW"] ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ; Util_Vals_WriteToFile( "IOSW", $rkey ) ; }

	$mapp_key = isset( $CONF['MAPP_KEY'] ) ? $CONF['MAPP_KEY'] : "" ;
	$kpr = substr( $mapp_key, 0, 5 ) ; $kpo = substr( $mapp_key, 5, strlen( $mapp_key ) ) ;
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
		toggle_menu_setup( "settings" ) ;

		var url = location.href ;
		url = url.replace( /\/mapp\/settings(.*)$/, "" ) ;
		url = url.replace( /\/$/, "" ) ;

		$('#url').val( url ) ;
		$('#phplive_url').html( url ) ;

		var kpo = phplive_md5(url).substring(10, 15) ;
		if ( ( '<?php echo $mapp_key ?>' == '' ) || ( '<?php echo $kpo ?>' != kpo ) )
			$('#div_register').show() ;
		else
		{
			$('#div_kpr').html( '<?php echo $kpr ?>' ) ;
			$('#div_kpo').html( '<?php echo $kpo ?>' ) ;

			$('#div_register').hide() ;
			$('#div_activated').show() ;
		}
	});

	function init_register()
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		var url = $('#url').val() ;

		$('#div_alert').hide() ;
		$('#btn_register').attr( "disabled", true ) ;

		$.ajax({
		type: "POST",
		url: "./settings.php",
		data: "action=pre_register&url="+encodeURIComponent(url)+"&"+unique,
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
				do_register() ;
			else
			{
				$('#btn_register').attr( "disabled", false ) ;
				do_alert_div( "..", 0, json_data.error ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			$('#btn_register').attr( "disabled", false ) ;
			do_alert_div( "..", 0, "Could not establish connection to begin the Mobile App registration.  Please try again." ) ;
		} });
	}

	function do_register()
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		var url = $('#url').val() ;

		$.ajax({
		type: "POST",
		url: "https://chat.phplivesupport.com/mapp/Util/system_register.php",
		data: "url="+encodeURIComponent(url)+"&key=<?php echo $KEY ?>&"+unique,
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
				do_save_code( json_data.kpr, json_data.kpo ) ;
			else
			{
				$('#btn_register').attr( "disabled", false ) ;
				do_alert_div( "..", 0, json_data.error ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			$('#btn_register').attr( "disabled", false ) ;
			do_alert_div( "..", 0, "Could not establish connection to the Registration server.  The server may be in the process of being updated.  Please try again." ) ;
		} });
	}

	function do_register_ios()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$('#btn_register_ios').attr( "disabled", true ) ;
		$('#div_ios_whitelist_status').html( '<span class="info_box"><img src="../pics/loading_ci.gif" width="16" height="16" border="0" alt=""></span>' ) ;

		var div_kpr = $('#div_kpr').html() ;
		var div_kpo = $('#div_kpo').html() ;
		var mkey = phplive_md5( div_kpr+div_kpo ) ;

		$.ajax({
		type: "POST",
		url: "https://chat.phplivesupport.com/mapp/Util/system_register_ios.php",
		data: "key=<?php echo $KEY ?>&mkey="+mkey+"&"+unique,
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				location.href = "settings.php?rkey="+json_data.rkey+"&"+unique ;
			}
			else
			{
				$('#div_ios_whitelist_status').html(json_data.error).show() ;
				setTimeout( function()
				{
					$('#btn_register_ios').attr( "disabled", false ) ;
				}, 5000 ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			$('#btn_register').attr( "disabled", false ) ;
			do_alert_div( "..", 0, "Could not establish connection to the iOS Whitelist server.  The server may be in the process of being updated.  Please try again." ) ;
		} });
	}

	function do_save_code( thekpr, thekpo )
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		var thismkey = thekpr+thekpo ;

		$('#btn_register').attr( "disabled", true ) ;

		$.ajax({
		type: "POST",
		url: "../ajax/setup_actions_.php",
		data: "action=save_mapp_key&mkey="+thismkey+"&"+unique,
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				$('#div_kpr').html( thekpr ) ;
				$('#div_kpo').html( thekpo ) ;

				$('#div_register').hide() ;
				$('#div_activated').show() ;

				if ( therkey != "" )
					location.href = "settings.php?"+unique ;
			}
			else
			{
				$('#btn_register').attr( "disabled", false ) ;
				do_alert_div( "..", 0, json_data.error ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			$('#btn_register').attr( "disabled", false ) ;
			do_alert_div( "..", 0, "Could not save Activation Code.  Please try again." ) ;
		} });
	}

	function update_idle_hours()
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		var hours = $('#idle_hours').val() ;

		$('#btn_update').attr( "disabled", true ) ;

		$.ajax({
		type: "POST",
		url: "./settings.php",
		data: "action=update_idle_hours&hours="+hours+"&"+unique,
		success: function(data){
			eval( data ) ;

			$('#btn_update').attr( "disabled", false ) ;
			if ( json_data.status )
			{
				do_alert( 1, "Update Success" ) ;
			}
			else
			{
				do_alert( 0, "Could not save settings.  Please try again." ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			$('#btn_register').attr( "disabled", false ) ;
			do_alert( 0, "Could not process request.  Please try again." ) ;
		} });
	}
//-->
</script>
</head>
<?php include_once( "../setup/inc_header.php" ) ?>

			<div class="op_submenu_wrapper">
				<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='../setup/settings.php?jump=eips'" id="menu_eips">Excluded IPs</div>
				<div class="op_submenu" onClick="location.href='../setup/settings.php?jump=sips'" id="menu_sips">Blocked IPs</div>
				<div class="op_submenu" onClick="location.href='../setup/settings.php?jump=props'" id="menu_props">Autocorrect & Charset</div>
				<div class="op_submenu" onClick="location.href='../setup/settings.php?jump=cookie'" id="menu_cookie">Cookies</div>
				<div class="op_submenu" onClick="location.href='../setup/settings.php?jump=upload'" id="menu_upload">File Upload</div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/ldap/ldap.php" ) ): ?><div class="op_submenu" onClick="location.href='../addons/ldap/ldap.php'" id="menu_ldap">LDAP</div><?php endif ; ?>
				<div class="op_submenu_focus" id="menu_system"><img src="../pics/icons/mobile.png" width="12" height="12" border="0" alt=""> Mobile App</div>
				<?php if ( $admininfo["adminID"] == 1 ): ?><div class="op_submenu" onClick="location.href='../setup/settings.php?jump=profile'" id="menu_profile"><img src="../pics/icons/key.png" width="12" height="12" border="0" alt=""> Password</div><?php endif ; ?>
				<div class="op_submenu" onClick="location.href='../setup/system.php'" id="menu_cookie">System</div>
				<div style="clear: both"></div>
			</div>

			<div id="div_register" style="display: none; margin-top: 25px;">
				To utilize the <a href="http://www.phplivesupport.com/r.php?r=mapp" target="_blank">Mobile Application</a> for Android and iOS, a registration of your PHP Live! system is required.  Registration is required to verify the software License Key and also to generate your unique Mobile App <b>Site ID</b>.

				<div style="margin-top: 25px;" class="info_info">
					<div style="">Your PHP Live! URL:</div>
					<div style="margin-top: 5px; font-size: 32px; font-weight: bold; color: #1DA1F2; text-shadow: 1px 1px #FFFFFF;"><span id="phplive_url"></span></div>

					<div style="display: none; margin-top: 25px;" class="info_error" id="div_alert"></div>

					<div style="margin-top: 25px;">Software License Key:</div>
					<div style="margin-top: 15px;"><span class="info_white"><?php echo $KEY ?></span></div>
					<div id="div_btn_register" style="margin-top: 25px;">
						<div><img src="../pics/icons/info.png" width="14" height="14" border="0" alt=""> The domain <big><b><?php echo $_SERVER["HTTP_HOST"] ?></b></big> must be accessible on the internet.  It cannot be an internal IP address or internal private domain.</div>
						<form>
						<input type="hidden" name="url" id="url" value="">
						<div style="margin-top: 25px;"><button type="button" onClick="init_register()" id="btn_register" class="btn">Generate Mobile App Site ID</button></div>
						</form>
					</div>

				</div>
			</div>

			<div id="div_activated" style="display: none; margin-top: 25px;">
				<span class="info_good"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""> Your PHP Live! system has been registered for Mobile App.</span>

				<div style="margin-top: 25px; padding: 25px;"  class="info_misc">
					Provide the following <b>Mobile App Site ID</b> to your operators.  The <b>Site ID</b> will need to be entered on the <a href="https://www.phplivesupport.com/r.php?r=mapp" target="_blank" style="color: #FFFFFF;">Mobile App</a> for Android and iOS.

					<div style="margin-top: 25px;">
						<div style="font-size: 18px; font-weight: bold;"><img src="../pics/icons/mobile_big.png" width="48" height="48" border="0" alt=""><b>Mobile App Site ID:</b> <span id="div_kpr" class="info_blue_dark"></span> - <span id="div_kpo" class="info_blue_dark"></span></div>

						<div style="margin-top: 35px;" class="info_info">
							<div style=""><span class="info_good"><b>Android Devices:</b></span> Operators using Android devices should be able to utilize the Mobile App immediately.</div>
							<?php if ( isset( $VALS["IOSW"] ) ): ?>
							<div style="margin-top: 25px;"><span class="info_good"><b>iOS Devices:</b></span> Operators using iOS devices should be able to utilize the Mobile App.  <b>iOS whitelist processing has been <span class="info_good">completed</span>.</b></div>
							<?php else: ?>
							<div style="margin-top: 15px;"><span class="info_error"><b>iOS Devices:</b></span> For iOS devices, domain whitelist access is required due to Apple security policies. <button type="button" class="btn" id="btn_register_ios" onClick="do_register_ios()">Request iOS Whitelist Access</button></div>
							<div style="display: none; margin-top: 15px;" id="div_ios_whitelist_status"></div>
							<?php endif ; ?>
						</div>
					</div>

					<div style="margin-top: 25px;"><span class="info_neutral"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> <span class="title">Reminder:</span></span> <a href="../setup/ops.php" style="color: #FFFFFF;">Enable the "Mobile App Access" for each operator</a> to allow the operator to login from the Mobile App.</div>
				
					<div style="display: none; margin-top: 45px; padding-top: 45px; border-top: 1px solid #E5E5E5;">
						<div class="edit_title">Mobile App Automatic Idle Offline</div>
						<div style="margin-top: 5px;">Automatically set mobile <i>Online</i> operators to <span style="font-weight: bold; color: #D6453D;">Offline</span> status if the operator has <span style="font-weight: bold; color: #D6453D;">not accessed (opened) the mobile application</span> greater than
							<select name="idle_hours" id="idle_hours">
							<?php
								$VARS_MOBILE_EXPIRED_OPS = ( isset( $VALS["MOBILE_EXPIRED_OPS"] ) ) ? $VALS["MOBILE_EXPIRED_OPS"] : 10 ;
								for( $c = 10; $c <= 48; ++$c )
								{
									$selected = "" ;
									if ( $VARS_MOBILE_EXPIRED_OPS == $c )
										$selected = "selected" ;
									print "<option value=\"$c\" $selected>$c</option>" ;
								}
							?><option value="96" <?php echo ( $VARS_MOBILE_EXPIRED_OPS == 96 ) ? "selected" : "" ?>>96</option><option value="720" <?php echo ( $VARS_MOBILE_EXPIRED_OPS == 720 ) ? "selected" : "" ?>>720</option></select> hours.
							<div><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> Most Android devices continue to communicate with the server when the Mobile App is placed in the background and is not considered idle state.  This setting is primarily for iOS devices because all network activity is paused when the Mobile App is placed in the background on iOS.</div>
							<div style="margin-top: 15px;"><button type="button" onClick="update_idle_hours()" class="btn" id="btn_update">Update</button></div>
						</div>
					</div>

				</div>
			</div>

<?php include_once( "../setup/inc_footer.php" ) ?>
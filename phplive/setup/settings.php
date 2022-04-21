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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;

	$error = "" ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$jump = ( Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ) ? Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) : "eips" ;
	$pr = Util_Format_Sanatize( Util_Format_GetVar( "pr" ), "n" ) ;
	$init = Util_Format_Sanatize( Util_Format_GetVar( "init" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$vars = Util_Format_Get_Vars( $dbh ) ;

	if ( !isset( $CONF["cookie"] ) ) { $CONF["cookie"] = "on" ; }
	$cookie_off = ( $CONF["cookie"] == "off" ) ? "checked" : "" ;
	$cookie_on = ( $cookie_off == "checked" ) ? "" : "checked" ;
	$charset = ( isset( $vars["char_set"] ) && $vars["char_set"] ) ? unserialize( $vars["char_set"] ) : Array(0=>"UTF-8") ;

	LIST( $your_ip, $null ) = Util_IP_GetIP( "" ) ;

	$max_days = 365 ; $max_bytes_ = 0 ; $fname = "same" ;
	$upload_max_filesize = ini_get( "upload_max_filesize" ) ;
	$upload_max_post = ( ini_get( "post_max_size" ) ) ? ini_get( "post_max_size" ) : $upload_max_filesize ;

	if ( $upload_max_filesize && preg_match( "/k/i", $upload_max_filesize ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
		$max_bytes = $temp * 1000 ;
		$max_bytes_ = $max_bytes ;
	}
	else if ( $upload_max_filesize && preg_match( "/m/i", $upload_max_filesize ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
		$max_bytes = $temp * 1000000 ;
		$max_bytes_ = $max_bytes ;
	}
	else if ( $upload_max_filesize && preg_match( "/g/i", $upload_max_filesize ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
		$max_bytes = $temp * 1000000000 ;
		$max_bytes_ = $max_bytes ;
	}
	else { $max_bytes = 500000 ; }

	if ( $upload_max_post && preg_match( "/k/i", $upload_max_post ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_post, "n" ) ;
		$max_post_bytes = $temp * 1000 ;
		$max_post_bytes_ = $max_post_bytes ;
	}
	else if ( $upload_max_post && preg_match( "/m/i", $upload_max_post ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_post, "n" ) ;
		$max_post_bytes = $temp * 1000000 ;
		$max_post_bytes_ = $max_post_bytes ;
	}
	else if ( $upload_max_post && preg_match( "/g/i", $upload_max_post ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_post, "n" ) ;
		$max_post_bytes = $temp * 1000000000 ;
		$max_post_bytes_ = $max_post_bytes ;
	}
	else if ( $upload_max_post ) { $max_post_bytes = $upload_max_post ; $max_post_bytes_ = "$max_post_bytes bytes" ; }

	if ( isset( $VALS["UPLOAD_MAX"] ) && $VALS["UPLOAD_MAX"] )
	{
		$upmax_array = unserialize( $VALS["UPLOAD_MAX"] ) ;
		$max_days = $upmax_array["days"] ;
		$max_bytes = $upmax_array["bytes"] ;
		$fname = ( isset( $upmax_array["fname"] ) && ( $upmax_array["fname"] == "random" ) ) ? "random" : "same" ;
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
	"use strict" ;

	var global_cookie = "<?php echo $CONF["cookie"] ?>" ;
	var global_charset = "<?php echo $charset[0] ?>" ;
	var global_autocorrect_v = <?php echo ( !isset( $VALS["AUTOCORRECT_V"] ) || ( isset( $VALS["AUTOCORRECT_V"] ) && $VALS["AUTOCORRECT_V"] ) ) ? 1 : 0 ; ?> ;
	var global_autocorrect_o = <?php echo ( !isset( $VALS["AUTOCORRECT_O"] ) || ( isset( $VALS["AUTOCORRECT_O"] ) && $VALS["AUTOCORRECT_O"] ) ) ? 1 : 0 ; ?> ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "settings" ) ;

		fetch_eips() ;
		fetch_sips() ;
		show_div( "<?php echo $jump ?>" ) ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>
		<?php if ( $action && $error ): ?>do_alert_div( "..", 0, "<?php echo $error ?>" ) ;<?php endif ; ?>

		<?php
			$pr_process = 0 ;
			if ( $pr && ( isset( $_COOKIE["phplive_pr"] ) && ( $_COOKIE["phplive_pr"] == md5( "phplive".substr( md5( $CONF['SALT'].$admininfo["password"] ), 6, 12 ) ) ) ) ):
			$pr_process = 1 ;
		?>
		$('body').css({'overflow':'hidden'}) ;
		$('#div_update_password').show() ;
		<?php endif ; ?>
	});

	function fetch_eips()
	{
		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=eips&"+unixtime(),
			success: function(data){
				print_eips( data ) ;
			}
		});
	}

	function fetch_sips()
	{
		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=sips&"+unixtime(),
			success: function(data){
				print_sips( data ) ;
			}
		});
	}

	function print_eips( thedata )
	{
		var json_data = new Object ;

		eval( thedata ) ;
		if ( typeof( json_data.ips ) != "undefined" )
		{
			var ip_string = "<table cellspacing=0 cellpadding=0 border=0 width=\"100%\">" ;
			for ( var c = 0; c < json_data.ips.length; ++c )
			{
				var ip = json_data.ips[c]["ip"] ;
				var ip_ = ip.replace( /\./g, "" ) ;

				ip_string += "<tr id=\"tr_eip_"+ip_+"\"><td class=\"td_dept_td\" width=\"14\"><div id=\"eip_"+ip_+"\"><a href=\"JavaScript:void(0)\" onClick=\"remove_eip( '"+ip+"' )\"><img src=\"../pics/icons/delete.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\"></a></div></td><td class=\"td_dept_td\">"+ip+"</td></tr>" ;
			}
			if ( !c )
				ip_string += "<tr><td class=\"td_dept_td\">Blank results.</td></tr>" ;
		}
		ip_string += "</table>" ;
		$('#eips').html( ip_string ) ;
	}

	function print_sips( thedata )
	{
		var json_data = new Object ;

		eval( thedata ) ;
		if ( typeof( json_data.ips ) != "undefined" )
		{
			var ip_string = "<table cellspacing=0 cellpadding=0 border=0 width=\"100%\">" ;
			for ( var c = 0; c < json_data.ips.length; ++c )
			{
				var ip = json_data.ips[c]["ip"] ;
				var ip_ = ip.replace( /\./g, "" ) ;

				ip_string += "<tr id=\"tr_sip_"+ip_+"\"><td class=\"td_dept_td\" width=\"14\"><div id=\"sip_"+ip_+"\"><a href=\"JavaScript:void(0)\" onClick=\"remove_sip( '"+ip+"' )\"><img src=\"../pics/icons/delete.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\"></a></div></td><td class=\"td_dept_td\">"+ip+"</td></tr>" ;
			}
			if ( !c )
				ip_string += "<tr><td class=\"td_dept_td\">Blank results.</td></tr>" ;
		}
		ip_string += "</table>" ;
		$('#sips').html( ip_string ) ;
	}

	function add_eip()
	{
		var ip = $('#ip_exclude').val().replace( /[^a-zA-Z0-9:.*]/g, "" ) ;
		$('#ip_exclude').val( ip ) ;

		if ( !ip )
			do_alert( 0, "Blank IP field is invalid." ) ;
		else
		{
			var json_data = new Object ;
			$('#btn_eip').attr( "disabled", true ) ;
			$('#img_loading_eip').show() ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=add_eip&ip="+ip+"&"+unixtime(),
				success: function(data){
					eval(data) ;
					if ( json_data.status )
					{
						setTimeout( function(){
							$('#img_loading_eip').hide() ;
							$('#btn_eip').attr( "disabled", false ) ;
							fetch_eips() ;
							do_alert( 1, "Update Success" ) ;
						}, 500 ) ;
					}
					else
					{
						$('#img_loading_eip').hide() ;
						$('#btn_eip').attr( "disabled", false ) ;
						do_alert( 0, "IP ("+ip+") already excluded." ) ;
					}
					$('#ip_exclude').val('') ;
				}
			});
		}
	}

	function remove_eip( theip )
	{
		$('#eips').hide() ;
		$('#img_loading_eip').show() ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=remove_eip&ip="+theip+"&"+unixtime(),
			success: function(data){
				setTimeout( function(){
					location.href = "settings.php?action=success" ;
				}, 500 ) ;
			}
		});
	}

	function add_sip()
	{
		var ip = $('#ip_spam').val().replace( /[^a-zA-Z0-9:.]/g, "" ) ;
		$('#ip_spam').val( ip ) ;

		if ( !ip )
			do_alert( 0, "Blank IP field is invalid." ) ;
		else
		{
			var json_data = new Object ;
			$('#btn_sip').attr( "disabled", true ) ;
			$('#img_loading_sip').show() ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=add_sip&ip="+ip+"&"+unixtime(),
				success: function(data){
					eval(data) ;
					if ( json_data.status )
					{
						setTimeout( function(){
							$('#img_loading_sip').hide() ;
							$('#btn_sip').attr( "disabled", false ) ;
							fetch_sips() ;
							do_alert( 1, "Update Success" ) ;
						}, 500 ) ;
					}
					else
					{
						$('#img_loading_sip').hide() ;
						$('#btn_sip').attr( "disabled", false ) ;
						do_alert( 0, "IP ("+ip+") already reported as spam." ) ;
					}
					$('#ip_spam').val('') ;
				}
			});
		}
	}

	function remove_sip( theip )
	{
		var theip_ = theip.replace( /\./g, "" ) ;
		$('#sips').hide() ;
		$('#img_loading_sip').show() ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=remove_sip&ip="+theip+"&"+unixtime(),
			success: function(data){
				setTimeout( function(){
					location.href = "settings.php?action=success&jump=sips" ;
				}, 500 ) ;
			}
		});
	}

	function show_div( thediv )
	{
		var orig_div = thediv ;
		if ( thediv == "charset" )
			thediv = "props" ;

		var divs = Array( "eips", "sips", "cookie", "props", "upload", "profile" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#settings_'+divs[c]).hide() ;
			$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('input#jump').val( thediv ) ;
		$('#settings_'+thediv).show() ;
		$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;

		if ( orig_div == "props" )
			show_div_props( "autocorrect" ) ;
		else if ( orig_div == "charset" )
			show_div_props( "charset" ) ;
	}

	function switch_dept( theobject )
	{
		location.href = "settings.php?deptid="+theobject.value ;
	}

	function update_profile()
	{
		var process = 1 ;
		var inputs = Array( "email", "login" ) ;

		<?php if ( $admininfo["status"] != -1 ): ?>
		if ( !check_email( $('#email').val() ) ) { do_alert( 0, "Email format is invalid. (example: you@domain.com)" ) ; process = 0 ; }
		<?php endif ; ?>

		if ( !$('#password').val() )
		{
			do_alert( 0, "Current Password must be provided." ) ; process = 0 ;
		}
		<?php if ( $pr_process ): ?>else if ( !$('#npassword').val() ) { do_alert( 0, "New Password must be provided.") ; process = 0 ; }<?php endif ; ?>
		else if ( $('#npassword').val() || $('#vpassword').val() )
		{
			if ( $('#npassword').val() != $('#vpassword').val() )
			{
				do_alert( 0, "New Password and Verify Password does not match." ) ; process = 0 ;
			}
			else if ( $('#npassword').val().length < 6 )
			{
				do_alert( 0, "New Password must be at least 6 characters." ) ; process = 0 ;
			}
		}

		if ( process ){ update_profile_doit() ; } ;
	}

	function update_profile_doit()
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		var email = $('#email').val() ;
		var login = $('#login').val() ;
		var password = phplive_md5( phplive_md5( $('#password').val() )+"<?php echo md5( $_COOKIE["phpliveadminSES"] ) ?>" ) ;
		var npassword = phplive_md5( $('#npassword').val() ) ;
		var vpassword = phplive_md5( phplive_md5( $('#npassword').val() )+"<?php echo md5( $_COOKIE["phpliveadminSES"] ) ?>" ) ;
		var md5_password = phplive_md5( npassword+vpassword+"<?php echo md5( $_COOKIE["phpliveadminSES"] ) ?>" ) ;

		$.ajax({
		type: "POST",
		url: "../ajax/setup_actions.php",
		data: "action=update_profile&email="+email+"&login="+login+"&password="+password+"&npassword="+npassword+"&vpassword="+vpassword+"&md5_password="+md5_password+"&"+unique,
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
				if ( parseInt( json_data.status ) == 2 )
					do_alert( 1, "\"Powered by\" has been removed successfully." ) ;
				else
					do_alert( 1, "Update Success" ) ;
				<?php endif ; ?>

			}
			else
				do_alert( 0, json_data.error ) ;

		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
		} });
	}

	function confirm_change( theflag )
	{
		if ( global_cookie != theflag )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_cookie&value="+theflag+"&"+unixtime(),
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
					{
						global_cookie = theflag ;
						do_alert( 1, "Update Success" ) ;
					}
					else
					{
						do_alert( 0, json_data.error ) ;
					}
				}
			});
		}
	}

	function update_upmax()
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		var upmax_days = parseInt( $('#upmax_days').val() ) ;
		var upmax_bytes = parseInt( $('#upmax_bytes').val().replace(/\,/g,"") ) ;

		var fname = ( $('#fname_random').is(':checked') ) ? "random" : "same" ;

		if ( !upmax_days )
		{
			$('#upmax_days').val(365) ;
			do_alert( 0, "Days value must be a number." ) ;
			return false ;
		}
		else if ( !upmax_bytes )
		{
			$('#upmax_bytes').val(<?php echo $max_bytes ?>) ;
			do_alert( 0, "Bytes value must be a number." ) ;
			return false ;
		}
		else if ( <?php echo $max_bytes_ ?> && ( upmax_bytes > <?php echo $max_bytes_ ?> ) )
		{
			$('#upmax_bytes').val( <?php echo $max_bytes_ ?> ) ;
			upmax_bytes = <?php echo $max_bytes_ ?> ;
		}

		if ( <?php echo $max_post_bytes ?> && ( upmax_bytes > <?php echo $max_post_bytes ?> ) )
		{
			$('#upmax_bytes').val( <?php echo $max_post_bytes ?> ) ;
			upmax_bytes = <?php echo $max_post_bytes ?> ;
		}

		$('#btn_update_upmax').attr("disabled", true) ;

		$.ajax({
		type: "POST",
		url: "../ajax/setup_actions_.php",
		data: "action=update_upmax&days="+upmax_days+"&bytes="+upmax_bytes+"&fname="+fname+"&"+unique,
		success: function(data){
			eval( data ) ;

			$('#btn_update_upmax').attr("disabled", false) ;
			if ( json_data.status )
			{
				do_alert( 1, "Update Success" ) ;
			}
			else
				do_alert( 0, json_data.error ) ;

		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
		} });
	}

	function show_div_props( thediv )
	{
		var divs = Array( "autocorrect", "charset" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#props_'+divs[c]).hide() ;
			$('#menu2_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu3') ;
		}

		$('#props_'+thediv).show() ;
		$('#menu2_'+thediv).removeClass('op_submenu3').addClass('op_submenu_focus') ;
	}

	function confirm_charset( thecharset )
	{
		if ( global_charset != thecharset )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_vars&varname=char_set&value="+thecharset+"&"+unixtime(),
				success: function(data){
					global_charset = thecharset ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
	}

	function toggle_autocorrect( the_vo, thevalue )
	{
		if ( ( ( the_vo == "v" ) && ( global_autocorrect_v != thevalue ) ) || ( ( the_vo == "o" ) && ( global_autocorrect_o != thevalue ) ) )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_autocorrect&vo="+the_vo+"&value="+thevalue+"&"+unixtime(),
				success: function(data){
					if ( the_vo == "v" ) { global_autocorrect_v = thevalue ; }
					else { global_autocorrect_o = thevalue ; }
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["settings"] ) ) && !$pr ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="show_div('eips')" id="menu_eips">Excluded IPs</div>
			<div class="op_submenu" onClick="show_div('sips')" id="menu_sips">Blocked IPs</div>
			<div class="op_submenu" onClick="show_div('props')" id="menu_props">Autocorrect & Charset</div>
			<div class="op_submenu" onClick="show_div('cookie')" id="menu_cookie">Cookies</div>
			<div class="op_submenu" onClick="show_div('upload')" id="menu_upload">File Upload</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/ldap/ldap.php" ) ): ?><div class="op_submenu" onClick="location.href='../addons/ldap/ldap.php'" id="menu_ldap">LDAP</div><?php endif ; ?>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/mapp/settings.php" ) ): ?><div class="op_submenu" onClick="location.href='../mapp/settings.php'" id="menu_system"><img src="../pics/icons/mobile.png" width="12" height="12" border="0" alt=""> Mobile App</div><?php endif ; ?>
			<?php if ( $admininfo["adminID"] == 1 ): ?>
			<div class="op_submenu" onClick="show_div('profile')" id="menu_profile"><img src="../pics/icons/key.png" width="12" height="12" border="0" alt=""> Password</div>
			<?php endif ; ?>
			<div class="op_submenu" onClick="location.href='system.php'" id="menu_system">System</div>
			<div style="clear: both"></div>
		</div>

		<form method="POST" action="settings.php" enctype="multipart/form-data">
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="jump" id="jump" value="">

		<div style="display: none; margin-top: 25px; text-align: justify;" id="settings_eips">
			To avoid misleading page views when developing a website, exclude internal or company IP from being counted towards the overall footprint report.  Excluded IPs will not be visible on the traffic monitor. Footprints of Excluded IPs will not be stored in the database and will not count towards the overall <a href="reports_traffic.php">footprint report</a>.

			<div style="margin-top: 15px;">
				<ul>
					<b>Wildcard</b> (*) is ok.  Example:
					<li style="margin-top: 5px;"> IP exclude of 123* will exclude all IPs that begin with 123</li>
					<li style="margin-top: 5px;"> IP exclude of *123* will exclude all IPs that contain 123</li>
				</ul>
			</div>

			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td valign="top" nowrap style="padding-right: 25px;">
						<div class="info_info">
							<div>Your IP: <b><?php echo $your_ip ?></b></div>
							<div style="margin-top: 15px;"><input type="text" class="input" name="ip_exclude" id="ip_exclude" size="20" maxlength="45" onKeyPress="return justips(event)" autocomplete="off"></div>
							<div style="margin-top: 25px;"><input type="button" onClick="add_eip()" value="Add Exclude IP" class="btn" id="btn_eip"></div>
						</div>
					</td>
					<td valign="top" width="100%">
						<div><div class="td_dept_header">Current Excluded IPs: &nbsp; <img src="../pics/loading_ci.gif" width="14" height="14" border="0" alt="" id="img_loading_eip" style="display: none;" class="info_white"></div></div>
						<div id="eips" style=""></div>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div style="display: none; margin-top: 25px;" id="settings_sips">
			Blocked IPs will always see an OFFLINE live chat status.  Operators can specify an IP to block during a chat session or you can provide an IP address here.  Blocked IPs will still display on the operator traffic monitor.  It is suggested to periodically clear out the blocked IPs.

			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td valign="top" nowrap style="padding-right: 25px;">
						<div class="info_info">
							<div>Example: <b>123.456.789.101</b></div>
							<div style="margin-top: 15px;"><input type="text" class="input" name="ip_spam" id="ip_spam" size="20" maxlength="45" onKeyPress="return justips(event)" autocomplete="off"></div>
							<div style="margin-top: 25px;"><input type="button" onClick="add_sip()" value="Add IP to Block" class="btn" id="btn_sip"></div>
						</div>
					</td>
					<td valign="top" width="100%">
						<div><div class="td_dept_header">Current Blocked IPs: &nbsp; <img src="../pics/loading_ci.gif" width="14" height="14" border="0" alt="" id="img_loading_sip" style="display: none;" class="info_white"></div></div>
						<div id="sips" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;"></div>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div style="display: none; margin-top: 25px;" id="settings_upload">
			File upload is available during a chat session for both the visitor and the operator.  File upload On/Off for website visitors can be set for each department at the <a href="depts.php">Departments</a> area.  File upload On/Off for the operator can be set for each operator at the <a href="ops.php">Operators</a> area.
			<div style="margin-top: 15px;">Update the duration the uploaded files are stored on the server and also the max file size that can be uploaded.</div>

			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=10 border=0>
				<tr>
					<td align="right">Store uploaded files on the server for </td>
					<td><input type="text" size="6" maxlength="6" class="input" name="upmax_days" id="upmax_days" value="<?php echo $max_days ?>" onKeyPress="return numbersonly(event)"> days</td>
				</tr>
				<tr>
					<td align="right">Upload max file size </td>
					<td>
						<input type="text" size="11" maxlength="11" class="input" name="upmax_bytes" id="upmax_bytes" value="<?php echo $max_bytes ?>" onKeyPress="return numbersonly(event)"> bytes (1K = 1,000 bytes, 1M = 1,000,000 bytes)
					</td>
				</tr>
				<?php if ( $max_bytes_ ): ?>
				<tr>
					<td colspan=2>
						<div style=""><img src="../pics/icons/info.png" width="14" height="14" border="0" alt=""> This web server has a pre-configured upload max file size capability of <span class="info_blue_dark" style="cursor: pointer;"><a href="http://php.net/manual/en/ini.core.php#ini.upload-max-filesize" target="_blank"><?php echo preg_replace( "/M/i", "MB", $upload_max_filesize ) ?></a></span>. ('<a href="http://php.net/manual/en/ini.core.php#ini.upload-max-filesize" target="_blank">upload_max_filesize</a>' directive)</div>

						<?php if ( ini_get( "post_max_size" ) && $upload_max_post ): ?>
						<div style="margin-top: 25px;"><img src="../pics/icons/info.png" width="14" height="14" border="0" alt=""> This web server has a pre-configured POST max file size capability of <span class="info_blue_dark" style="cursor: pointer;"><a href="http://php.net/manual/en/ini.core.php#ini.post-max-size" target="_blank"><?php echo preg_match( "/bytes/", $max_post_bytes_ ) ? $max_post_bytes_ : preg_replace( "/M/i", "MB", $upload_max_post ) ; ?></a></span>. ('<a href="http://php.net/manual/en/ini.core.php#ini.post-max-size" target="_blank">post_max_size</a>' directive)  File upload is performed as POST method.</div>
						<?php endif ; ?>
					</td>
				</tr>
				<?php endif ; ?>
				<tr>
					<td align="right">File Names </td>
					<td>
						<div style="margin-top: 5px;">
							<span class="info_neutral" style="cursor: pointer;" onclick="$('#fname_same').prop('checked', true);"><input type="radio" name="fname" id="fname_same" value="same" <?php echo ( $fname != "random" ) ? "checked" : "" ; ?>> keep original file names</span> &nbsp; &nbsp;
							<span class="info_neutral" style="cursor: pointer;" onclick="$('#fname_random').prop('checked', true);"><input type="radio" name="fname" id="fname_random" value="0" <?php echo ( $fname == "random" ) ? "checked" : "" ; ?>> randomize file names (example: <code>picture.png</code> might look something like <code>3zk5tsn9rq.png</code>)</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="padding-top: 25px;">
						<?php if ( $VARS_INI_UPLOAD ): ?><button type="button" class="btn" onClick="update_upmax()" id="btn_update_upmax">Update</button>
						<?php else: ?><img src="../pics/icons/alert.png" width="16" height="16" border="0" alt=""> File upload is not enabled for this server ('<a href="http://php.net/manual/en/ini.core.php#ini.file-uploads" target="_blank">file_uploads</a>' directive).  Please contact the server admin for more information.
						<?php endif ; ?>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div style="display: none; margin-top: 25px;" id="settings_cookie">
			Switch On/Off the use of cookies on the visitor chat window.  The cookies provide convenience for the visitor and does not affect the actual chat functions.
			<div style="margin-top: 25px;" class="info_info">
				Cookies set by the system on the visitor chat request window:
				<li style="margin-top: 5px;"> <code>phplivevname</code> - The visitor's name
				<li> <code>phplivevemail</code> - The visitor's email address
				<li> <code>phplivevid</code> - Visitor identification ID
			</div>
			<div style="margin-top: 15px;" class="info_warning"><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> It is recommended to keep the setting at <b>"Set cookies"</b> to greatly improve visitor identification and system performance.</div>

			<div style="margin-top: 25px;">
				<span class="info_good" style="cursor: pointer;" onclick="$('#cookie_on').prop('checked', true);confirm_change('on');"><input type="radio" name="cookie" id="cookie_on" value="on" <?php echo $cookie_on ?>> Set cookies</span>
				<span class="info_neutral" style="cursor: pointer;" onclick="$('#cookie_off').prop('checked', true);confirm_change('off');"><input type="radio" name="cookie" id="cookie_off" value="off" <?php echo $cookie_off ?>> Do not set cookies</span>
			</div>
		</div>

		<div style="display: none; margin-top: 25px;" id="settings_props">
			<div id="op_submenu_wrapper_logo">
				<div class="op_submenu3" style="margin-left: 0px;" onClick="show_div_props('autocorrect')" id="menu2_autocorrect">Autocorrect</div>
				<div class="op_submenu3" style="" onClick="show_div_props('charset')" id="menu2_charset">Character Set</div>
				<div style="clear: both"></div>
			</div>

			<div style="margin-top: 25px;" id="props_autocorrect">
				<div><span style="color: #0078D7; font-weight: bold;">Autocorrect</span> is a web browser feature that sometimes automatically corrects misspelled words during a chat session.</div>
				
				<div style="margin-top: 5px;"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> It is recommended to keep the setting to "On" unless it is causing some issues (example: international language being autocorrected to English words).</div>
				<div style="margin-top: 15px;"><span class="info_warning"><img src="../pics/icons/warning.gif" width="16" height="16" border="0" alt=""> [ Caution ] Switching "Off" the autocorrect might also disable the automatic spellcheck on some browsers or devices.</span></div>
				<div style="margin-top: 25px;">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td>
							Visitor chat autocorrect:
						</td>
						<td style="padding-left: 15px;">
							<div style="">
								<div class="info_good" style="float: left; padding: 3px; width: 50px; cursor: pointer;" onclick="$('#autocorrect_v_1').prop('checked', true);toggle_autocorrect('v', 1);"><input type="radio" name="autocorrect_v" id="autocorrect_v_1" value="1" <?php echo ( !isset( $VALS["AUTOCORRECT_V"] ) || ( isset( $VALS["AUTOCORRECT_V"] ) && $VALS["AUTOCORRECT_V"] ) ) ? "checked" : "" ; ?>> On</div>
								<div class="info_error" style="float: left; margin-left: 10px; padding: 3px; width: 50px; cursor: pointer;" onclick="$('#autocorrect_v_0').prop('checked', true);toggle_autocorrect('v', 0);"><input type="radio" name="autocorrect_v" id="autocorrect_v_0" value="0" <?php echo ( isset( $VALS["AUTOCORRECT_V"] ) && !$VALS["AUTOCORRECT_V"] ) ? "checked" : "" ; ?>> Off</div>
								<div style="clear: both;"></div>
							</div>
						</td>
					</tr>
					<tr><td colspan=2>&nbsp;</td></tr>
					<tr>
						<td>
							Operator chat autocorrect:
						</td>
						<td style="padding-left: 15px;">
							<div style="">
								<div class="info_good" style="float: left; padding: 3px; width: 50px; cursor: pointer;" onclick="$('#autocorrect_o_1').prop('checked', true);toggle_autocorrect('o', 1);"><input type="radio" name="autocorrect_o" id="autocorrect_o_1" value="1" <?php echo ( !isset( $VALS["AUTOCORRECT_O"] ) || ( isset( $VALS["AUTOCORRECT_O"] ) && $VALS["AUTOCORRECT_O"] ) ) ? "checked" : "" ; ?>> On</div>
								<div class="info_error" style="float: left; margin-left: 10px; padding: 3px; width: 50px; cursor: pointer;" onclick="$('#autocorrect_o_0').prop('checked', true);toggle_autocorrect('o', 0);"><input type="radio" name="autocorrect_o" id="autocorrect_o_0" value="0" <?php echo ( isset( $VALS["AUTOCORRECT_O"] ) && !$VALS["AUTOCORRECT_O"] ) ? "checked" : "" ; ?>> Off</div>
								<div style="clear: both;"></div>
							</div>
						</td>
					</tr>
					</table>
				</div>
			</div>
			<div style="display: none; margin-top: 25px;" id="props_charset">
				If multi-language characters are not rendering properly on the operator chat window or while viewing transcripts, try updating the UTF value.  UTF-8 is suggested.

				<div style="margin-top: 25px;">
					<div class="li_op round" style="cursor: pointer;" onclick="$('#charset_UTF-8').prop('checked', true); confirm_charset('UTF-8');"><input type="radio" name="charset" id="charset_UTF-8" value="UTF-8" <?php echo ( $charset[0] == "UTF-8" ) ? "checked" : "" ?>> UTF-8</div>
					<div class="li_op round" style="cursor: pointer;" onclick="$('#charset_ISO-8859-1').prop('checked', true); confirm_charset('ISO-8859-1');"><input type="radio" name="charset" id="charset_ISO-8859-1" value="ISO-8859-1" <?php echo ( $charset[0] == "ISO-8859-1" ) ? "checked" : "" ?>> ISO-8859-1</div>
					<div style="clear: both;"></div>
				</div>
			</div>
		</div>

		<?php if ( !$pr_process ): ?>
		<div style="display: none; margin-top: 25px;" id="settings_profile">
			Update the Setup Admin contact email address and the password.  The Setup Admin email is used for the Setup Admin "forgot password" recovery and other system related features.

			<div style="margin-top: 25px;">
				<input type="hidden" name="login" id="login" value="<?php echo $admininfo["login"] ?>">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="td_dept_td" width="120">Setup Admin Email</td>
					<td class="td_dept_td" width="770"><input type="text" class="input" size="35" maxlength="160" name="email" id="email" value="<?php echo $admininfo["email"] ?>" onKeyPress="return justemails(event)" value=""></td>
				</tr>
				<tr>
					<td class="td_dept_td" width="120">Current Password</td>
					<td class="td_dept_td" width="770"><input type="password" class="input" size="35" id="password" autocomplete="off"></td>
				</tr>
				<tr>
					<td colspan="4" style="padding-top: 5px;">
						<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 15px;"></div>
						<div style="">
							<table cellspacing=0 cellpadding=4 border=0>
							<tr>
								<td class="td_dept_td" width="120">&nbsp;</td>
								<td class="td_dept_td">
									<div style="font-size: 14px; font-weight: bold;">Update Password (optional)</div>
									<div style="margin-top: 5px;">Password must be at least 6 characters and can be a combination of letters, numbers and any special characters.</div>
								</td>
							</tr>
							<tr> 
								<td class="td_dept_td" width="120">New Password</td> 
								<td class="td_dept_td"><input type="password" class="input" size="35" id="npassword" autocomplete="off"></td> 
							</tr>
							<tr>
								<td class="td_dept_td" width="120" nowrap>Verify New Password</td> 
								<td class="td_dept_td"><input type="password" class="input" size="35" id="vpassword" autocomplete="off"></td> 
							</tr>
							</table>
						</div>
						<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 15px;"></div>
					</td>
				</tr>
				<tr> 
					<td></td> 
					<td class="td_dept_td"><input type="button" value="Update Profile" id="btn_submit" onClick="update_profile()" class="btn"></td> 
				</tr> 
				</table>
			</div>
		</div>
		<?php else: ?>
		<input type="hidden" name="login" id="login" value="<?php echo $admininfo["login"] ?>">
		<input type="hidden" name="email" id="email" value="<?php echo $admininfo["email"] ?>">
		<?php endif ; ?>

		</form>
		<?php endif ; ?>

<?php if ( $pr_process ): ?>
<div id="div_update_password" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; padding-top: 80px; z-index: 50; background: url(../themes/initiate/bg_trans_darker.png) repeat;">
	<div class="info_info" style="width: 500px; height: 350px; margin: 0 auto; padding: 10px;">

		<div id="div_update_password_password">
			<div class="td_dept_td noshadow">
				<div class="edit_title">Update Password</div>
				<div style="margin-top: 5px; text-align: justify;">Before continuing, update the password to this Setup Admin area.  Password must be at least 6 characters and can be a combination of letters, numbers and any special characters.</div>
			</div>
			
			<div>
				<table cellspacing=0 cellpadding=0 border=0>
				<tr> 
					<td class="td_dept_td noshadow" width="120">New Password</td> 
					<td class="td_dept_td noshadow"><input type="password" class="input" size="35" id="npassword"></td> 
				</tr>
				<tr>
					<td class="td_dept_td noshadow" width="120" nowrap>Verify New Password</td> 
					<td class="td_dept_td noshadow"><input type="password" class="input" size="35" id="vpassword"></td> 
				</tr>
				<tr>
					<td class="td_dept_td noshadow">&nbsp;</td>
					<td class="td_dept_td noshadow">
						<input type="hidden" class="input" size="35" id="password" value="1">
						<button type="button" onClick="update_profile()" class="btn">Update Password</button>
					</td>
				</tr>
				</table>
			</div>
		</div>
		<div id="div_update_password_success" style="display: none;" class="td_dept_td noshadow">
			<div class="info_good title" style="text-shadow: none;"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""> Password has been updated.</div>
			<div style="margin-top: 25px;">

				<?php if ( $init ): ?>

				<a href="index.php?init=<?php echo $init ?>">Update Timezone</a>

				<?php else:  ?>

					<?php if ( !count( $departments ) ): ?>
					<div style="color: #1B3C5D; text-shadow: 1px 1px #EBF2FA;" class="edit_title">Getting started.  Things to do:</div>
					<div style="margin-top: 25px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td><img src="../pics/icons/numbers/1.png" width="16" height="16" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
							<td style="padding-left: 5px;"><a href="depts.php">Add Chat Department</a></td>
						</tr>
						<tr>
							<td style="padding-top: 10px;"><img src="../pics/icons/numbers/2.png" width="16" height="16" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
							<td style="padding-left: 5px; padding-top: 10px;">Add Chat Operator</td>
						</tr>
						<tr>
							<td style="padding-top: 10px;"><img src="../pics/icons/numbers/3.png" width="16" height="16" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
							<td style="padding-left: 5px; padding-top: 10px;">Assign Operator to Department</td>
						</tr>
						<tr>
							<td style="padding-top: 10px;"><img src="../pics/icons/numbers/4.png" width="16" height="16" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
							<td style="padding-left: 5px; padding-top: 10px;">Copy HTML Code</td>
						</tr>
						<tr>
							<td style="padding-top: 10px;"><img src="../pics/icons/numbers/5.png" width="16" height="16" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></td>
							<td style="padding-left: 5px; padding-top: 10px;">Go <span style="font-weight: bold;">ONLINE!</span></td>
						</tr>
						</table>
					</div>
					<?php else: ?>
					<a href="index.php">Close window</a>
					<?php endif ; ?>

				<?php endif ; ?>

			</div>
		</div>

	</div>
</div>
<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_DB.php" ) ;

	// permission checking
	$perm_web = is_writable( "$CONF[CONF_ROOT]" ) ;
	$perm_conf = is_writeable( "$CONF[CONF_ROOT]/config.php" ) ;
	$perm_chats = is_writeable( $CONF["CHAT_IO_DIR"] ) ;
	$perm_initiate = is_writeable( $CONF["TYPE_IO_DIR"] ) ;
	$perm_patches = is_writeable( "$CONF[CONF_ROOT]/patches" ) ;
	$disabled_functions = ini_get( "disable_functions" ) ;
	$ini_open_basedir = ini_get("open_basedir") ;
	$ini_safe_mode = ini_get("safe_mode") ;
	$safe_mode = preg_match( "/on/i", $ini_safe_mode ) ? 1 : 0 ;

	$function_exif_imagetype = ( function_exists( "exif_imagetype" ) ) ? 1 : 0 ;

	$pv = phpversion() ;
	$version_build = 1 ; // build of the version

	$last_upgraded = ( is_file( "$CONF[CONF_ROOT]/patches/$patch_v" ) ) ? date( "M j, Y ($VARS_TIMEFORMAT)", filemtime ( "$CONF[CONF_ROOT]/patches/$patch_v" ) ) : 0 ;

	$query = "SELECT created FROM p_admins WHERE adminID = 1 LIMIT 1" ;
	database_mysql_query( $dbh, $query ) ;
	$super_admin = database_mysql_fetchrow( $dbh ) ;

	$created = date( "M j, Y", $super_admin["created"] ) ;
	$diff = time() - $super_admin["created"] ;
	$days_running = round( $diff/(60*60*24) ) ;

	$tables = Util_DB_GetTableNames( $dbh ) ; $db_error = 0 ;
	for( $c = 0; $c < count( $tables ); ++$c )
	{
		$analyze = Util_DB_AnalyzeTable( $dbh, $tables[$c] ) ;
		$stats = Util_DB_TableStats( $dbh, $tables[$c] ) ;

		$name = $stats["Name"] ;
		$type = $analyze["Msg_type"] ;
		if ( preg_match( "/^p_/", $name ) )
		{
			if ( isset( $analyze["Msg_text"] ) && !preg_match( "/(Table is already up to date)|(ok)/i", $analyze["Msg_text"] ) )
			{
				$db_error = 1 ; break 1 ;
			}
		}
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

<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var global_adminid = 0 ;
	var access_hash = new Object ;
	access_hash["depts"] = "Departments" ;
	access_hash["ops"] = "Operators" ;
	access_hash["interface"] = "Interface" ;
	access_hash["icons"] = "Chat Icons" ;
	access_hash["code"] = "HTML Code" ;
	access_hash["trans"] = "Transcripts" ;
	access_hash["reports"] = "Reports" ;
	access_hash["traffic"] = "Traffic" ;
	access_hash["extras"] = "Extras" ;
	access_hash["settings"] = "Settings" ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "settings" ) ;

		<?php if ( $admininfo["status"] != -1 ): ?>fetch_admins() ;<?php endif ; ?>

	});

	<?php if ( $admininfo["status"] != -1 ): ?>
	function generate_admin()
	{
		var login = $('#login').val().trim() ;
		var reset_password = $( "#password" ).prop( "checked" ) ? 1 : 0 ;
		var query_access = "" ;
		var flag = 0 ;

		$('#div_admins_wrapper').find(':checkbox').each( function () {
			var div_name = this.id ;
			if ( div_name.indexOf( "access_" ) == 0 )
			{
				if ( $(this).prop( 'checked' ) )
				{
					query_access += "&access[]="+$(this).val() ;
					flag = 1 ;
				}
			}
		}) ;

		if ( !login )
			do_alert( 0, "Please provide the login." ) ;
		else if ( !flag )
			do_alert( 0, "At least one access must be checked." ) ;
		else
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			$('#btn_generate').attr( "disabled", true ) ;

			$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=generate_setup_admin&adminid="+global_adminid+"&reset="+reset_password+"&login="+login+query_access+"&"+unique,
			success: function(data){
				eval( data ) ;

				$('#btn_generate').attr( "disabled", false ) ;
				if ( json_data.status )
				{
					do_alert( 1, "Success" ) ;
					reset_access() ;
					fetch_admins() ;
				}
				else
					do_alert( 0, json_data.error ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				$('#btn_generate').attr( "disabled", false ) ;
				do_alert( 0, "Could not connect to server.  Try refreshing this page." ) ;
			} });
		}
	}

	function reset_access()
	{
		$('#login').val("") ;
		$('#div_password').hide() ;
		$('#password').prop( 'checked', false ) ;
		$('#span_cancel').hide() ;

		$('#div_admins_wrapper').find(':checkbox').each( function () {
			var div_name = this.id ;
			if ( div_name.indexOf( "access_" ) == 0 )
			{
				$(this).prop( 'checked', false ) ;
			}
		}) ; global_adminid = 0 ;
	}

	function fetch_admins()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$.ajax({
		type: "POST",
		url: "../ajax/setup_actions.php",
		data: "action=fetch_setup_admins&"+unique,
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				var admin_string = "<table cellspacing=1 cellpadding=0 border=0 width='100%'>"+
					"<tr>"+
						"<td width=\"14\" class=\"td_dept_td\">&nbsp;</td>"+
						"<td class=\"td_dept_td\"><b>Login Info</b></td>"+
						"<td class=\"td_dept_td\"><b>&nbsp;</b></td>"+
						"<td class=\"td_dept_td\"><b>Access</b></td>"+
					"</tr>" ;
				for ( var c = 0; c < json_data.admins.length; ++c )
				{
					var admin = json_data.admins[c] ;
					var password = admin["password"] ;

					var access_string = admin["access"] ;
					if ( !access_string )
					{
						for ( var index in access_hash )
							access_string += index+"," ;
					}
					var access_string_array = access_string.split( "," ) ;
					var access_string_display = "" ;
					for ( var c2 = 0; c2 < access_string_array.length; ++c2 )
					{
						if ( access_string_array[c2] )
							access_string_display += "<div class='info_neutral' style='float: left; margin-right: 5px; margin-bottom: 5px;'>"+access_hash[access_string_array[c2]]+"</div>" ;
					}
					access_string_display += "<div class='clear: both'></div>" ;

					var edit_string = "<div><a href=\"JavaScript:void(0)\" onClick=\"do_edit("+admin["adminid"]+", '"+admin["login"]+"', '"+access_string+"')\"><img src=\"../pics/btn_edit.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;
					var delete_string = "<div style=\"margin-top: 10px;\"><a href=\"JavaScript:void(0)\" onClick=\"delete_admin("+admin["adminid"]+")\"><img src=\"../pics/btn_delete.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;

					admin_string += "<tr style='background: #FFFFFF;'><td class=\"td_dept_td\">"+edit_string+delete_string+"</td><td class=\"td_dept_td\" nowrap><b>Login:</b><div style=\"margin-top: 5px;\">"+admin["login"]+"</div><div style=\"margin-top: 15px;\"><b>Password:</b><div style=\"margin-top: 5px;\">"+password+"</div></div></td><td class=\"td_dept_td\" nowrap>Created<div style=\"margin-top: 5px;\">"+admin["created"]+"</div><div style=\"margin-top: 15px;\">Last Login<br><div style=\"margin-top: 5px;\">"+admin["lastactive"]+"</div></div></td><td class=\"td_dept_td\" nowrap>"+access_string_display+"</td></tr>" ;
				}
				admin_string += "</table>" ;
				
				$('#div_admins').html( admin_string ) ;
			}
			else
			{
				//
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Could not connect to server.  Try refreshing this page." ) ;
		} });
	}

	function do_edit( theadminid, thelogin, theaccess )
	{
		reset_access() ;

		global_adminid = theadminid ;

		var access_string_array = theaccess.split( "," ) ;
		for ( var c = 0; c < access_string_array.length; ++c )
			$('#access_'+access_string_array[c]).prop( 'checked', true ) ;

		$('#login').val(thelogin) ;
		$('#div_password').show() ;
		$('#span_cancel').show() ;
	}

	function delete_admin( theadminid )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( confirm( "Really delete this Admin?" ) )
		{
			$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=delete_setup_admin&adminid="+theadminid+"&"+unique,
			success: function(data){
				eval( data ) ;

				$('#btn_generate').attr( "disabled", false ) ;
				if ( json_data.status )
				{
					do_alert( 1, "Account Deleted" ) ;
					fetch_admins() ;
				}
				else
					do_alert( 0, json_data.error ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Could not connect to server.  Try refreshing this page." ) ;
			} });
		}
	}
	<?php endif ; ?>

	function load_file_check()
	{
		$('#div_file_link').html( '<img src="../pics/loading_ci.gif" width="16" height="16" border="0" alt="" style="background: #FFFFFF;" class="round">' ) ;
		location.href = "settings_snapshot.php" ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["settings"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='settings.php?jump=eips'" id="menu_eips">Excluded IPs</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=sips'" id="menu_sips">Blocked IPs</div>
			<div class="op_submenu" onClick="location.href='../setup/settings.php?jump=props'" id="menu_props">Autocorrect & Charset</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=cookie'" id="menu_cookie">Cookies</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=upload'" id="menu_upload">File Upload</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/ldap/ldap.php" ) ): ?><div class="op_submenu" onClick="location.href='../addons/ldap/ldap.php'" id="menu_ldap">LDAP</div><?php endif ; ?>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/mapp/settings.php" ) ): ?><div class="op_submenu" onClick="location.href='../mapp/settings.php'" id="menu_system"><img src="../pics/icons/mobile.png" width="12" height="12" border="0" alt=""> Mobile App</div><?php endif ; ?>
			<?php if ( $admininfo["adminID"] == 1 ): ?>
			<div class="op_submenu" onClick="location.href='settings.php?jump=profile'" id="menu_profile"><img src="../pics/icons/key.png" width="12" height="12" border="0" alt=""> Password</div>
			<?php endif ; ?>
			<div class="op_submenu_focus" id="menu_system">System</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/account/index.php" ) ): ?><div class="op_submenu" onClick="location.href='../addons/account/index.php'" id="menu_account">Account</div><?php endif ; ?>
			<div style="clear: both"></div>
		</div>

		<?php if ( $db_error ): ?>
			<div class="info_error" style="margin-top: 25px;"><img src="../pics/icons/warning.png" width="16" height="16" border="0" alt=""> Database table has errors.  This will effect your system and some areas will not function properly.  <a href="db.php" style="color: #FFFFFF;">Please review the database informaion to fix the issue.</a></div>
		<?php endif ; ?>

		<div style="margin-top: 25px;">
			<form>
			<div style="float: left; width: 750px;">

				<div class="info_info">
					<table cellspacing=0 cellpadding=5 border=0 width="100%">
					<tr>
						<td nowrap><b>Software License Key:</b> <span class="info_white"><?php echo $KEY ?></span></td>
						<td width="100%" align="right" style="padding-left: 25px;"></td>
					</tr>
					<tr>
						<td colspan=2 style="padding-top: 25px;">
							<div class="info_blue" style="box-shadow: 0 8px 12px 0 rgba(0,0,0,.24),0 20px 40px 0 rgba(0,0,0,.24);">
								<table cellspacing=0 cellpadding=2 border=0>
								<tr>
									<td style="padding-right: 15px">PHP Live! <span class="info_white">v.<?php echo $VERSION ?> <span style="opacity: .4;">(build: <?php echo $version_build ?>)</span></span></td><td><img src="../pics/icons/disc.png" width="16" height="16" border="0" alt=""> <a href="https://www.phplivesupport.com/r.php?plk=pi-24-ysj-m&r=vcheck&v=<?php echo base64_encode( $VERSION ) ?>&k=<?php echo base64_encode( $KEY ) ?>&b=<?php echo $version_build ?>" target="new" style="color: #FFFFFF;">Click Here to check for new software version</a></td>
								</tr>
								</table>
							</div>
							<?php
								if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/geo_data/VERSION.php" ) ):
								include_once( "$CONF[DOCUMENT_ROOT]/addons/geo_data/VERSION.php" ) ;
							?>
							<div class="info_neutral" style="margin-top: 15px;">
								<table cellspacing=0 cellpadding=2 border=0>
								<tr>
									<td style="padding-right: 15px"><a href="extras_geo.php">GeoIP addon</a> <span class="info_white">v.<?php echo $VERSION_GEO ?></span></td><td><img src="../pics/icons/disc.png" width="16" height="16" border="0" alt=""> <a href="http://www.phplivesupport.com/r.php?r=vcheck_geo&v=<?php echo base64_encode( $VERSION_GEO ) ?>" target="new">check for new version</a></td>
								</tr>
								</table>
							</div>
							<?php endif ; ?>
							<?php
								if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/API/VERSION.php" ) ):
								include_once( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/API/VERSION.php" ) ;
							?>
							<div class="info_neutral" style="margin-top: 15px;">
								<table cellspacing=0 cellpadding=2 border=0>
								<tr>
									<td style="padding-right: 15px"><a href="../addons/phplivebot/phplivebot.php">Bot addon</a> <span class="info_white">v.<?php echo $PHPLIVEBOT_VERSION ?></span></td><td><img src="../pics/icons/disc.png" width="16" height="16" border="0" alt=""> <a href="http://www.phplivesupport.com/r.php?r=vcheck_bot&v=<?php echo base64_encode( $PHPLIVEBOT_VERSION ) ?>" target="new">check for new version</a></td>
								</tr>
								</table>
							</div>
							<?php endif ; ?>
						</td>
					</tr>
					</table>
				</div>

				<?php if ( $admininfo["status"] != -1 ): ?>
				<div style="margin-top: 25px; text-align: justify;" class="info_info" id="div_admins_wrapper">
					<div class="edit_title">Additional Setup Admin Accounts</div>
					<div style="margin-top: 5px;" class="info_white">Account passwords are automatically generated.  They will be asked to change the password when they first log in.  If at anytime they forget their password, simply edit the account and check the "reset the password" checkbox.</div>
					<div style="margin-top: 25px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>
								Create Login<br>
								<input type="text" class="input" size="12" maxlength="15" style="" id="login" autocomplete="off" onKeyPress="return logins(event)">
								<div style="display: none; margin-top: 10px;" id="div_password"><input type="checkbox" id="password"> <label for="password">reset password</label></div>
								<div style="margin-top: 20px;">
									<button type="button" class="btn" onClick="generate_admin()" id="btn_generate">Submit</button> &nbsp; &nbsp; <span id="span_cancel" style="display: none;"><a href="JavaScript:void(0)" onClick="reset_access()">cancel</a></span>
								</div>
							</td>
							<td style="padding-left: 15px;">
								<div class="info_white" style="padding: 20px; padding-top: 25px; padding-bottom: 25px;">
									<div>Areas of access:</div>
									<div style="margin-top: 15px;">
										<span class="info_neutral"><input type="checkbox" name="access" id="access_depts" value="depts"> <label for="access_depts" style="cursor: pointer">Departments</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_ops" value="ops"> <label for="access_ops" style="cursor: pointer">Operators</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_interface" value="interface"> <label for="access_interface" style="cursor: pointer">Interface</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_icons" value="icons"> <label for="access_icons" style="cursor: pointer">Chat Icons</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_code" value="code"> <label for="access_code" style="cursor: pointer">HTML Code</label></span>
									</div>
									<div style="margin-top: 25px;">
										<span class="info_neutral"><input type="checkbox" name="access" id="access_trans" value="trans"> <label for="access_trans" style="cursor: pointer">Transcripts</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_reports" value="reports"> <label for="access_reports" style="cursor: pointer">Reports</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_traffic" value="traffic"> <label for="access_traffic" style="cursor: pointer">Traffic</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_extras" value="extras"> <label for="access_extras" style="cursor: pointer">Extras</label></span> &nbsp;
										<span class="info_neutral"><input type="checkbox" name="access" id="access_settings" value="settings"> <label for="access_settings" style="cursor: pointer">Settings</label></span> &nbsp;
									</div>
								</div>
							</td>
						</tr>
						</table>
					</div>
					<div style="margin-top: 25px; min-height: 145px; max-height: 245px; overflow-x: hidden; overflow-y: auto;" id="div_admins"></div>
				</div>
				<?php endif ; ?>

			</div>
			<div style="float: left; border: 0px solid transparent; margin-left: 25px; text-align: right;">
				System Installed on:
				<div style="margin-top: 5px; margin-bottom: 20px; font-size: 16px;">
					<?php echo $created ?>
					<div style="margin-top: 5px; font-size: 12px;">(<?php echo ( $days_running ) ? number_format( $days_running ) : 1 ; ?> days)</div>
					<div style="margin-top: 15px; font-size: 12px;">
						System last upgraded:
						<div style="margin-top: 5px;"><?php echo $last_upgraded ?></div>
					</div>
				</div>

				<div style="" class="info_neutral"><a href="db.php"><img src="../pics/icons/db.png" width="16" height="16" border="0" alt=""> View Database Stats</a></div>
				<div style="margin-top: 15px;" class="info_neutral" id="div_file_link"><a href="JavaScript:void(0)" onClick="load_file_check()"><img src="../pics/icons/view.png" width="16" height="16" border="0" alt=""> File Check</a> <?php echo ( $opcache ) ? '<img src="../pics/icons/bolt_yes.png" width="16" height="16" border="0" alt="OpCache enabled" title="OpCache enabled">' : '' ;  ?></div>

				<div style="margin-top: 15px; width: 170px; overflow: auto;" class="info_neutral">
					Server <a href="http://www.php.net/" target="_blank">PHP version</a>: <?php echo $pv ?>
					<div style="margin-top: 15px;">
						<a href="https://www.php.net/manual/en/book.opcache.php" target="_blank">OpCache</a>: <?php echo ( $opcache ) ? 'Enabled <img src="../pics/icons/bolt_yes.png" width="16" height="16" border="0" alt="OpCache enabled" title="OpCache enabled">' : 'Disabled <img src="../pics/icons/bolt_no.png" width="16" height="16" border="0"  alt="OpCache not enabled" title="OpCache not enabled">' ; ?>
					</div>
					<div style="margin-top: 15px;">
						<a href="http://php.net/manual/en/reserved.constants.php#constant.php-int-max" target="_blank">PHP_INT_MAX</a>
						<div style="font-weight: bold; margin-top: 10px;"><?php echo PHP_INT_MAX; ?></div>
					</div>

					<?php if ( !$function_exif_imagetype ): ?>
					<div style="margin-top: 15px;" class="info_error">
						Function <a href="https://www.php.net/manual/en/function.exif-imagetype.php" target="_blank">exif_imagetype</a> is not enabled.  Please enable this function to view uploaded files.
					</div>
					<?php endif ; ?>
				</div>
			</div>
			<div style="clear:both;"></div>
			</form>

		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
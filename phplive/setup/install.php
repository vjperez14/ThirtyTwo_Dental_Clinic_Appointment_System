<?php
	/***************************************/
	//
	// (c) PHP Live!
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	error_reporting(1) ;
	$pv = phpversion() ; if ( $pv >= "5.1.0" ){ date_default_timezone_set( "America/New_York" ) ; }
	$upgrade_php = ( $pv < 5.4 ) ? 1 : 0 ;

	include_once( "../API/Util_Format.php" ) ;
	include_once( "../API/Util_Vals.php" ) ;
	include_once( "../API/Util_Hash.php" ) ;

	$PHPLIVE_VERSION_START = "4" ;
	$now = time() ;
	$live = 1 ;

	$query = isset( $_SERVER["QUERY_STRING"] ) ? Util_Format_Sanatize( $_SERVER["QUERY_STRING"], "query" ) : "" ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$base_url = Util_Format_Sanatize( Util_Format_GetVar( "base_url" ), "base_url" ) ;
	$e = Util_Format_Sanatize( Util_Format_GetVar( "e" ), "ln" ) ;
	$q = Util_Format_Sanatize( Util_Format_GetVar( "q" ), "b64" ) ;

	if ( $live )
	{
		if ( is_file( "../web/config.php" ) )
		{ HEADER( "location: ../index.php?menu=sa" ) ; exit ; }
	}

	/***** PRE INSTALL CHECK OF PHP SETTINGS *****/
	// gather ini settings
	$ini_open_basedir = ini_get("open_basedir") ;
	$ini_safe_mode = ini_get("safe_mode") ;
	$safe_mode = preg_match( "/on/i", $ini_safe_mode ) ? 1 : 0 ;

	if ( function_exists( "mysql_get_client_info" ) ) { $mysql_version = mysql_get_client_info() ; }
	else if ( function_exists( "mysqli_get_client_info" ) ) { $mysql_version = mysqli_get_client_info() ; }
	else { $mysql_version = false ; }

	$php_version = PHP_VERSION ;
	/***** PRE INSTALL CHECK OF PHP SETTINGS *****/

	if ( $action === "create_dirs" )
	{
		if ( !is_dir( "../web/chat_initiate" ) )
			mkdir( "../web/chat_initiate", 0777 ) ;
		if ( !is_dir( "../web/chat_sessions" ) )
			mkdir( "../web/chat_sessions", 0777 ) ;
		if ( !is_dir( "../web/patches" ) )
			mkdir( "../web/patches", 0777 ) ;

		$headers = ( function_exists( "get_headers" ) ) ? get_headers( $base_url, 1 ) : Array() ; $proxy_cache = 0 ;
		if ( isset( $headers["X-Proxy-Cache"] ) || isset( $headers["x-proxy-cache"] ) )
		{
			$x_proxy_cache = isset( $headers["X-Proxy-Cache"] ) ? $headers["X-Proxy-Cache"] : $headers["x-proxy-cache"] ;
			if ( is_array( $x_proxy_cache ) )
			{
				for ( $c = 0; $c < count( $x_proxy_cache ); ++$c )
				{
					if ( strtoupper( $x_proxy_cache[$c] ) != "BYPASS" )
						$proxy_cache = 1 ;
				}
			} else if ( strtoupper( $x_proxy_cache ) != "BYPASS" )
				$proxy_cache = 1 ;
		}
		if ( $proxy_cache )
			$json_data = "json_data = { \"status\": 0, \"error\": \"proxy_cache\" };" ;
		else if ( ( $safe_mode || ( $ini_safe_mode == 1 ) ) && ( !is_dir( "../web/chat_sessions" ) || !is_writable( "../web/chat_sessions" ) ) )
			$json_data = "json_data = { \"status\": 0, \"error\": \"prep\" };" ;
		else if ( !is_dir( "../web/chat_sessions" ) || !is_dir( "../web/chat_initiate" ) || !is_dir( "../web/patches" ) )
			$json_data = "json_data = { \"status\": 0, \"error\": \"mkdir\" };" ;
		else if ( !is_writable( "../web/chat_sessions" ) || !is_dir( "../web/chat_initiate" ) || !is_dir( "../web/patches" ) )
			$json_data = "json_data = { \"status\": 0, \"error\": \"permissions\" };" ;
		else if ( !$mysql_version )
			$json_data = "json_data = { \"status\": 0, \"error\": \"mysql\" };" ;
		else
		{
			if ( !is_file( "../web/vals.php" ) )
			{
				$vals_string = "< php \$VALS = Array() ; \$VALS['CHAT_SPAM_IPS'] = \"\" ; \$VALS['TRAFFIC_EXCLUDE_IPS'] = \"\" ; ?>" ;
				$vals_string = preg_replace( "/< php/", "<?php", $vals_string ) ;

				$fp = fopen ("../web/vals.php", "w") ;
				fwrite( $fp, $vals_string, strlen( $vals_string ) ) ;
				fclose( $fp ) ;
			}
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	else if ( $action === md5( $q ) )
	{
		$query = parse_str( urldecode( base64_decode( $q ) ), $query_array ) ;
		$email = Util_Format_Sanatize( $query_array["email"], "e" ) ;
		$login = Util_Format_Sanatize( $query_array["login"], "ln" ) ;
		$password = Util_Format_Sanatize( $query_array["password"], "ln" ) ;
		$vpassword = Util_Format_Sanatize( $query_array["vpassword"], "ln" ) ;
		$document_root = Util_Format_Sanatize( $query_array["document_root"], "base_url" ) ;
		$base_url = Util_Format_Sanatize( $query_array["base_url"], "base_url" ) ;
		$db_type = Util_Format_Sanatize( $query_array["db_type"], "ln" ) ;
		$db_host = Util_Format_Sanatize( $query_array["db_host"], "ln" ) ;
		$db_name = Util_Format_Sanatize( $query_array["db_name"], "" ) ;
		$db_login = base64_decode( Util_Format_Sanatize( $query_array["db_login"], "" ) ) ;
		$db_password = base64_decode( Util_Format_Sanatize( $query_array["db_password"], "" ) ) ;
		$timezone = Util_Format_Sanatize($query_array["timezone"], "timezone" ) ;

		$db_port = 0 ;
		if ( preg_match( "/:/", $db_host ) )
		{
			LIST( $db_host, $port_temp ) = explode( ":", $db_host ) ;
			$port_temp = trim( $port_temp ) ;
			$db_port = is_numeric( $port_temp ) ? $port_temp : 0 ;
		}

		$str_len = strlen( $base_url ) ;
		$last = ( $str_len ) ? $base_url[$str_len-1] : "" ;
		if ( $last == "/" ) { $base_url = substr( $base_url, 0, $str_len - 1 ) ; }
		$base_url = preg_replace( "/^(http:)/i", "", $base_url ) ;

		$str_len = strlen( $document_root ) ; $last = ( $str_len ) ? $document_root[$str_len-1] : "" ;
		if ( $last == "/" ) { $document_root = substr( $document_root, 0, $str_len - 1 ) ; }
		else if ( $last == "\\" ) { $document_root = substr( $document_root, 0, $str_len - 1 ) ; }
		$str_len = strlen( $document_root ) ; $last = ( $str_len ) ? $document_root[$str_len-1] : "" ;
		if ( $last == "/" ) { $document_root = substr( $document_root, 0, $str_len - 1 ) ; } // safety check
		else if ( $last == "\\" ) { $document_root = substr( $document_root, 0, $str_len - 1 ) ; } // safety check

		$error = "" ;
		if ( !is_file( "$document_root/phplive.php" ) )
			$error = "Document Root is invalid." ;
		else if ( !$db_host || !$db_name || !$db_login )
			$error = "Blank DB value is invalid." ;
		else if ( $vpassword != md5($password) )
			$error = "Setup Password and Verify Password does not match." ;
		else if ( $db_type == "mysql" )
		{
			if ( function_exists('mysqli_connect') )
				$db_type = "mysqli" ;
			else if ( !function_exists('mysql_connect') )
				$error = "PHP MySQL extension is not enabled.  Try MySQLi or PDO." ;
		}
		else if ( ( $db_type == "mysqli" ) && !function_exists('mysqli_connect') )
			$error = "PHP MySQLi extension is not enabled." ;
		else if ( ( $db_type == "pdo" ) && !extension_loaded('pdo_mysql') )
			$error = "PHP PDO extension is not enabled." ;

		if ( !$error )
		{
			$CONF = Array() ;
			$CONF["SQLHOST"] = $db_host ;
			$CONF["SQLLOGIN"] = $db_login ;
			$CONF["SQLPASS"] = $db_password ;
			$CONF["DATABASE"] = $db_name ;
			$CONF["SQLPORT"] = $db_port ;

			if ( $db_type == "mysql" )
			{
				$sql_type = "SQL.php" ;
				$connection = mysql_connect( $CONF["SQLHOST"], $CONF["SQLLOGIN"], stripslashes( $CONF["SQLPASS"] ) ) ;
				if ( mysql_errno() ) { $error = "MySQL Host or login information is invalid." ; }
				else{
					mysql_select_db( $CONF["DATABASE"] ) ;
					if ( $result = mysql_query( "SHOW TABLES", $connection ) ){
						mysql_close( $connection ) ; unset( $connection ) ;
						include_once( "../API/SQL.php" ) ;
					}
					else{
						mysql_close( $connection ) ;
						$error = "MySQL database ($db_name) not found." ;
					}
				}
			}
			else if ( $db_type == "mysqli" )
			{
				$error = "MySQL Host or login information is invalid." ;
				$sql_type = "SQLi.php" ;
				$temp_host = $CONF["SQLHOST"] ;
				if ( isset( $CONF["SQLPORT"] ) && $CONF["SQLPORT"] ) { $temp_host = $CONF["SQLHOST"].":".$CONF["SQLPORT"] ; }
				$connection = new mysqli( $temp_host, $CONF["SQLLOGIN"], stripslashes( $CONF["SQLPASS"] ) ) ;
				if ( $connection->connect_errno && is_numeric( $connection->connect_errno ) ) { $error = "MySQL Host or login information is invalid. (MySQL error: ".$connection->connect_errno.")" ; }
				else{
					$connection->select_db( $CONF["DATABASE"] ) ;
					if ( $result = $connection->query("SHOW TABLES") ){
						$error = "" ;
						mysqli_close( $connection ) ; unset( $connection ) ;
						include_once( "../API/SQLi.php" ) ;
					}
					else{
						mysqli_close($connection);
						$error = "MySQL database ($db_name) not found." ;
					}
				}
			}
			else
			{
				$sql_type = "PDO.php" ;
				try {
					$port_string = ( $CONF["SQLPORT"] ) ? "port=$CONF[SQLPORT];" : "" ;
					$connection = new PDO( "mysql:host=$CONF[SQLHOST];{$port_string}dbname=$CONF[DATABASE];", $CONF["SQLLOGIN"], $CONF["SQLPASS"] ) ;
					$connection = null ; unset( $connection ) ;
					include_once( "../API/PDO.php" ) ;
				} catch ( PDOException $e ) {
					$error = "MySQL host, login, password or database name is invalid." ;
				}
			}

			if ( !$error )
			{
				include_once( "./KEY.php" ) ;

				$query_array = get_db_query() ;
				$errors = "" ;
				for ( $c = 0; $c < count( $query_array ); ++$c )
				{
					if ( $query_array[$c] )
					{
						database_mysql_query( $dbh, $query_array[$c] ) ;
						if ( !$dbh['ok'] )
							$errors .= $dbh['error'] ;
					}
				}

				if ( $errors )
					$error = $errors ;
				else
				{
					$document_root = addslashes( $document_root ) ;
					$conf_vars = "\$CONF = Array() ;\n" ;
					$conf_vars .= "\$CONF['DOCUMENT_ROOT'] = addslashes( '$document_root' ) ;\n" ;
					$conf_vars .= "\$CONF['BASE_URL'] = '$base_url' ;\n" ;
					$conf_vars .= "\$CONF['SQLTYPE'] = '$sql_type' ;\n" ;
					$conf_vars .= "\$CONF['SQLHOST'] = '$db_host' ;\n" ;
					$conf_vars .= "\$CONF['SQLPORT'] = '$db_port' ;\n" ;
					$conf_vars .= "\$CONF['SQLLOGIN'] = '$db_login' ;\n" ;
					$conf_vars .= "\$CONF['SQLPASS'] = '$db_password' ;\n" ;
					$conf_vars .= "\$CONF['DATABASE'] = '$db_name' ;\n" ;
					$conf_vars .= "\$CONF['THEME'] = 'default' ;\n" ;
					$conf_vars .= "\$CONF['TIMEZONE'] = '$timezone' ;\n" ;
					$conf_vars .= "\$CONF['icon_online'] = '' ;\n" ;
					$conf_vars .= "\$CONF['icon_offline'] = '' ;\n" ;
					$conf_vars .= "\$CONF['lang'] = 'english' ;\n" ;
					$conf_vars .= "\$CONF['logo'] = '' ;\n" ;

					$conf_string = "< php\n	$conf_vars" ;
					$conf_string .= "	if ( phpversion() >= '5.1.0' ){ date_default_timezone_set( \$CONF['TIMEZONE'] ) ; }\n" ;
					$conf_string .= "	include_once( \"\$CONF[DOCUMENT_ROOT]/API/Util_Vars.php\" ) ;\n?>" ;
					$conf_string = preg_replace( "/< php/", "<?php", $conf_string ) ;

					$fp = fopen ("../web/config.php", "w") ;
					fwrite( $fp, $conf_string, strlen( $conf_string ) ) ;
					fclose( $fp ) ;

					if ( is_file( "../web/config.php" ) )
					{
						$now = time() ;
						LIST( $login, $password ) = database_mysql_quote( $dbh, $login, $password ) ;

						$query = "INSERT INTO p_admins VALUES(NULL, $now, 0, 0, '', '$login', '$password', '$email')" ;
						database_mysql_query( $dbh, $query ) ;

						$version_string = "< php \$VERSION = \"$PHPLIVE_VERSION_START\" ; ?>" ;
						$version_string = preg_replace( "/< php/", "<?php", $version_string ) ;
						$fp = fopen ("../web/VERSION.php", "w") ;
						fwrite( $fp, $version_string, strlen( $version_string ) ) ;
						fclose( $fp ) ;

						$base_url = urlencode( preg_replace( "/http/i", "hphp", $base_url ) ) ;
						$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
						$CONF = Array() ; $CONF["DOCUMENT_ROOT"] = $document_root ;
						LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
					}
					else { $error = "Could not create configuration file." ; }
				}
			}
		}

		if ( $error )
		{
			$error = rawurlencode( $error ) ;
			$json_data = "json_data = { \"status\": 0, \"error\": \"$error\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 1 };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$document_root = preg_replace( "/setup(.*?)/i", "", dirname(__FILE__) ) ; include_once( "./KEY.php" ) ; $timezones = Util_Hash_Timezones() ;
	$VERSION = "_install_$PHPLIVE_VERSION_START" ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support Installation </title>

<meta name="description" content="powered by: PHP Live!  www.phplivesupport.com">
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
	var phplive_proto = ( location.href.indexOf("https") == 0 ) ? 1 : 0 ;
	var execute ;
	var inputs = Array( "email", "login", "password", "vpassword", "base_url", "document_root", "db_host", "db_name", "db_login", "db_password" ) ;
	var inputs_test = Array( "db_host", "db_name", "db_login", "db_password" ) ;

	$(document).ready(function()
	{
		$("html, body").css({'background': '#485C73'}) ;
		$('#base_url').val( location.toString().replace( "setup/install.php", "" ) ) ;

		init_menu() ;
		create_dirs() ;

		<?php if ( $e ): ?>
		if ( phplive_proto ) { $('#error_cert').show() ; }
		<?php endif ; ?>

		if ( !phplive_proto )
			$('#div_insecure').show() ;
	});

	function install()
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		var email = $('#email').val().trim() ;
		var login = $('#login').val().trim() ;
		var password = phplive_md5( $('#password').val().trim() ) ;
		var vpassword = phplive_md5( password ) ;
		var base_url = $('#base_url').val().trim().replace("http", "hphp") ;
		var document_root = $('#document_root').val().trim() ;
		var db_type = $('#db_type').val().trim() ;
		var db_host = $('#db_host').val().trim() ;
		var db_name = $('#db_name').val().trim() ;
		var db_login = $('#db_login').val().trim() ;
		var db_password = $('#db_password').val().trim() ;
		var timezone = $('#timezone').val().trim() ;
		var license_agree = $('#license_agree').prop( "checked" ) ;
		base_url = base_url.replace( /\?(.*?)$/, "" ) ;

		if ( !timezone )
			do_alert( 0, "Timezone must be selected." ) ;
		else if ( !login || ( password == "d41d8cd98f00b204e9800998ecf8427e" ) || !db_host || !db_name || !db_login )
			do_alert( 0, "All input values must be provided." ) ;
		else if ( !check_email( $('#email').val() ) )
			do_alert( 0, "Email format is invalid. (example: you@domain.com)" ) ;
		else if ( $('#password').val() != $('#vpassword').val() )
			do_alert( 0, "Setup Password and Verify Password does not match." ) ;
		else if ( !license_agree )
		{
			do_alert( 0, "You must agree with the PHP Live! Software License Agreement." ) ;
			$('#div_license_agree').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		}
		else
		{
			$('#div_alert').hide() ;
			$('#btn_install').html( "Installing..." ) ;
			$('#btn_install').attr('disabled', true) ;
			input_disable() ;

			var query = new Object ;
			query["email"] = email ;
			query["login"] = login ;
			query["password"] = password ;
			query["vpassword"] = vpassword ;
			query["base_url"] = base_url ;
			query["document_root"] = document_root ;
			query["db_type"] = db_type ;
			query["db_host"] = db_host ;
			query["db_name"] = db_name ;
			query["db_login"] = phplive_base64.encode( db_login ) ;
			query["db_password"] = phplive_base64.encode( db_password ) ;
			query["timezone"] = timezone ;
			query_serialized = phplive_base64.encode( $.param( query ) ) ;
			var action = phplive_md5( query_serialized ) ;

			$.ajax({
			type: "POST",
			url: "install.php",
			data: "action="+action+"&q="+query_serialized+"&"+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					$('#btn_install').html( "Install Success" ) ;
					do_alert( 1, "Install Success" ) ;
					setTimeout( function(){ location.href = "../patch.php?menu=sa" ; }, 5000 ) ;
				}
				else
				{
					input_enable() ;
					$('#btn_install').html( "Click to Install" ) ;
					$('#btn_install').attr('disabled', false) ;
					do_alert_div( "..", 0, decodeURIComponent( json_data.error ) ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				location.href = "install.php?e="+xhr.responseText+"&"+unique ;
			} });
		}
	}

	function create_dirs()
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		var base_url = encodeURIComponent( $('#base_url').val().replace("http", "hphp") ) ;

		$.ajax({
		type: "POST",
		url: "install.php",
		data: "action=create_dirs&base_url="+base_url+"&"+unique,
		success: function(data){
			eval( data ) ;

			if ( !parseInt( json_data.status ) )
			{
				$('#pre_check').hide() ;
				$('#pre_install').show() ;

				if ( json_data.error == "proxy_cache" )
					$('#pre_proxy_cache').show() ;
				else if ( json_data.error == "prep" )
					location.href = "../README/PREP.html" ;
				else if ( json_data.error == "mysql" )
					$('#pre_errormysql').show() ;
				else if ( json_data.error == "mkdir" )
					$('#pre_mkdir').show() ;
				else
					$('#pre_errorbox').show() ;
			}
			else
				setTimeout( function(){ $('#pre_check').hide() ; next_step() ; }, 2000 ) ;
		},
		error:function (xhr, ajaxOptions, thrownError){
			location.href = "install.php?e=2&"+unique ;
		} });
	}

	function next_step()
	{
		<?php if ( $upgrade_php ): ?>
		$('#form_install').hide() ;
		$('#pre_install').show() ;
		$('#pre_errorupgradephp').show() ;
		<?php else: ?>
		$('#pre_install').hide() ;
		$('#form_install').show() ;
		<?php endif ; ?>
	}

	function input_disable()
	{
		$( '*', '#form_install' ).each( function () {
			var div_name = this.id ;
			if ( $(this).is("input") )
				$(this).attr( "disabled", true ) ;
		}) ;
	}

	function input_enable()
	{
		$( '*', '#form_install' ).each( function () {
			var div_name = this.id ;
			if ( $(this).is("input") )
				$(this).attr( "disabled", false ) ;
		}) ;
	}

	function http_redirect()
	{
		var base_url = location.toString( ).replace("https:", "http:") ;
		base_url = base_url.replace( /\?(.*?)$/, "" ) ;
			
		location.href = base_url ;
	}

	function https_redirect()
	{
		var base_url = location.toString( ).replace("http:", "https:") ;
		base_url = base_url.replace( /\?(.*?)$/, "" ) ;

		location.href = base_url ;
	}
//-->
</script>
</head>
<body>

<div id="body" style="width: 970px; margin: 0 auto; margin-top: 15px;">
	<div id="pre_check" style="margin-top: 25px;" class="info_info">
		Checking directory permissions... <img src="../pics/loading_ci.gif" width="16" height="16" border="0" alt="" class="info_white">
	</div>

	<div id="pre_install" style="display: none;">
		<div id="pre_errorbox" style="display: none;" class="info_neutral">
			<div class="edit_title info_error" style="text-shadow: none;">Error: Directory permissions.</div>
			<div style="margin-top: 25px;">Directory permission error.  Please refer to the <a href="http://www.phplivesupport.com/r.php?r=perm" target="_blank">directory permission documentation</a> to correct the issue.  After completed, refresh this page to continue.</div>
		</div>
		<div id="pre_proxy_cache" style="display: none;" class="info_neutral">
			<div class="edit_title info_error" style="text-shadow: none;">Error: Proxy Cache Detected.</div>
			<div style="margin-top: 25px;">
				Server proxy cache may cache the result of a PHP script output, resulting in unexpected software behaviors.
				<div style="margin-top: 15px; margin-bottom: 15px; font-weight: bold; font-size: 16px;">Please disable (or bypass) the proxy cache for the <span class="info_box">/phplive</span> directory.</div>
				For <b>cPanel</b> users, the proxy cache setting can be accessed at the <b>Cache Manager</b> area.  For more information about proxy cache, please visit the <a href="https://www.nginx.com/blog/nginx-caching-guide/" target=="_blank">Nginx Caching Guide</a>.  After completed, refresh this page to continue.
			</div>
			<div style="margin-top: 25px;">* <a href="https://www.phplivesupport.com/r.php?r=tech" target="_blank">Contact Tech Support</a> for further assistance</div>
		</div>
		<div id="pre_mkdir" style="display: none;" class="info_neutral">
			<div class="edit_title info_error" style="text-shadow: none;">Error: Could not create system directories.</div>
			<div style="margin-top: 25px;">The required directories could not be created.  Please refer to the <a href="https://www.phplivesupport.com/r.php?r=selinux" target="_blank">Installation troubleshooting tips</a> to correct the issue.  After completed, refresh this page to continue.</div>
		</div>
		<div id="pre_errormysql" style="display: none;" class="info_neutral">
			<div class="edit_title info_error" style="text-shadow: none;">Error: MySQL support was not detected.</div>
			<div style="margin-top: 25px;">MySQL support was not detected.  Contact your server admin to enable MySQL support for PHP or perhaps check if the MySQL server is running. After completed, refresh this page to continue.</div>
			<div style="margin-top: 25px;">* <a href="https://www.phplivesupport.com/r.php?r=tech" target="_blank">Contact Tech Support</a> for further assistance</div>
		</div>
		<div id="pre_errorupgradephp" style="display: none;" class="info_neutral">
			<div class="edit_title info_error" style="text-shadow: none;">Error: Server PHP upgrade required.</div>
			<div style="margin-top: 25px;">Your server PHP version is version <big><b><?php echo $pv ?></b></big>.  However, the PHP Live! software <big><b>requires PHP >= 5.4 or PHP 7+</b></big>.  Please upgrade your server PHP to PHP 5.4 or greater.  After your server PHP has been upgraded, refresh this page to continue.</div>
			<div style="margin-top: 25px;">&bull; <a href="http://www.php.net/downloads.php" target="_blank">Visit PHP.net to download the latest PHP version</a></div>
			<div style="margin-top: 25px;">* <a href="https://www.phplivesupport.com/r.php?r=tech" target="_blank">Contact Tech Support</a> for further assistance</div>
		</div>
	</div>

	<form id="form_install" style="display: none; margin-top: 25px;">
	<input type="hidden" name="base_url" id="base_url" value="">
	<input type="hidden" id="timezone" value="America/New_York">
	<div style="padding: 20px;" class="info_misc round_bottom_none">
		<table cellspacing=0 cellpadding=0 border=0>
		<tr>
			<td style="font-size: 20px;"><div class="info_white" style="padding: 10px; border: 1px solid #34ABAE;"><img src="../pics/logo.png" width="120" height="25" border="0" alt=""></div></td>
			<td style="padding-left: 25px;">
				<div style="font-size: 20px;">Installation</div>
				<div style="display: none; margin-top: 5px;" class="info_warning" id="div_insecure">
					<img src="../pics/icons/warning.gif" width="16" height="16" border="0" alt=""> <b>Non-secure HTTP Installation Detected</b>
					<div style="margin-top: 5px;"><b>NOTE:</b> You are attempting to install PHP Live! on a non-secure HTTP URL.  Some aspects of the software may not function properly.  To limit potential issues, consider installing on a secure HTTPS URL by changing the URL to HTTPS (or <a href="JavaScript:void(0)" onClick="https_redirect()" style="color: #101010;">click here</a> to attempt to load this page in secure HTTPS URL).  For more information, please visit the <a href="https://www.phplivesupport.com/r.php?r=nonsecure" target="_blank" style="color: #D8372C;">Non-secure HTTP Installation Notes</a> documentation.</div>
				</div>
				<div style="display: none; margin-top: 5px;" class="info_error" id="error_cert">Possible HTTPS cert error.  Try installing over <a href="JavaScript:void(0)" style="color: #FFFFFF;" onClick="http_redirect()">HTTP</a> protocol.</div>
			</td>
		</tr>
		</table>
	</div>
	<div style="box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);" class="info_info round_top_none">
		<table cellspacing=0 cellpadding=0 border=0 width="100%">
		<tr>
			<td valign="top" width="45%" style="">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td colspan=2>
						<span class="edit_title">Create Your Setup Admin Account</span>
						<div style="margin-top: 15px; text-align: justify;">The Setup Admin has access to all the setup features such as creating chat departments, creating chat operators, update chat icons, view chat reports, access to all the administrative configuration options.</div>
					</td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap><div style="cursor: default;" class="info_blue_dark round">Setup Admin Login</div></td>
					<td class="td_dept_td_blank noshadow"><input type="text" class="input" size="25" maxlength="15" name="login" id="login" onKeyPress="return nospecials(event)" value=""><div style="font-size: 10px;">* letters and numbers only</div></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap><div style="cursor: default;" class="info_blue_dark round">Setup Admin Password</div></td>
					<td class="td_dept_td_blank noshadow"><input type="password" class="input" size="25" name="password" id="password" value=""></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Verify Setup Password</td>
					<td class="td_dept_td_blank noshadow"><input type="password" class="input" size="25" name="vpassword" id="vpassword" value=""></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Setup Admin Email</td>
					<td class="td_dept_td_blank noshadow"><input type="text" class="input" size="25" maxlength="160" name="email" id="email" onKeyPress="return justemails(event)" value=""><div style="margin-top: 5px; font-size: 10px;"><span class="info_misc" style="padding: 2px;">* used for password recovery and system alerts</span></div></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Document Root</td>
					<td class="td_dept_td_blank noshadow"><input type="text" class="input" size="25" maxlength="255" name="document_root" id="document_root" value="<?php echo $document_root ?>"><div style="margin-top: 5px; font-size: 10px;"><span class="info_neutral">* do not modify the above value</span></div></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Software License Key</td>
					<td class="td_dept_td_blank noshadow"><code><?php echo $KEY ?></code></td>
				</tr>
				</table>
			</td>
			<td width="10%">&nbsp;</td>
			<td valign="top" width="45%" style="">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td colspan=2>
						<span class="edit_title">Database Settings</span>
						<div style="margin-top: 15px;">Contact your website admin to create a database for your PHP Live! system and provide the MySQL database information below.</div>
					</td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Connection Type</td>
					<td class="td_dept_td_blank noshadow">
						<select id="db_type" name="db_type" class="select">
							<option value="mysqli" selected>MySQLi</option>
							<option value="pdo">PDO</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Database Name</td>
					<td class="td_dept_td_blank noshadow"><input type="text" class="input" size="25" maxlength="85" name="db_name" id="db_name" value=""></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" colspan="2">
						<div><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> The MySQL user (<b>Database Login/Username</b>) should have the following privileges granted to the above Database: SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER</div>
					</td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Database Host</td>
					<td class="td_dept_td_blank noshadow">
						<input type="text" class="input" size="25" maxlength="85" name="db_host" id="db_host" value="">
					</td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Database Login/Username</td>
					<td class="td_dept_td_blank noshadow"><input type="text" class="input" size="25" maxlength="85" name="db_login" id="db_login" value=""></td>
				</tr>
				<tr>
					<td class="td_dept_td_blank noshadow" nowrap>Database Password</td>
					<td class="td_dept_td_blank noshadow"><input type="password" class="input" size="25" maxlength="300" name="db_password" id="db_password" value=""></td>
				</tr>
				<tr>
					<td style="padding-top: 15px; padding-left: 15px;" colspan=2>
						<div class="info_white" style="padding: 15px;">
							<div style="margin-bottom: 15px;" id="div_license_agree"><input type="checkbox" id="license_agree"> I agree with the <a href="https://www.phplivesupport.com/copyright.php" target="license">PHP Live! Software License Agreement</a></div>
							<div id="div_alert" style="display: none; margin-bottom: 15px;"></div>
							<div style=""><button type="button" id="btn_install" class="btn" onClick="install()">Click to Install</button></div>
						</div>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		<div style="margin-top: 55px; padding-top: 15px; border-top: 1px solid #BFC6CC; text-align: right;">For installation assistance, please visit the <a href="https://www.phplivesupport.com/r.php?r=install&plk=pi-23-78m-m&key=<?php echo $KEY ?>" target="_blank">knowledge base</a>. &copy; PHP Live LLC</div>
	</div>
	</form>
</div>

</body>
</html>

<?php
	function get_db_query()
	{
		$query = "DROP TABLE IF EXISTS p_admins; CREATE TABLE IF NOT EXISTS p_admins ( adminID int(10) unsigned NOT NULL AUTO_INCREMENT, created int(10) unsigned NOT NULL, lastactive int(10) unsigned NOT NULL, status tinyint(4) NOT NULL, ses varchar(32) NOT NULL, login varchar(15) NOT NULL, password varchar(32) NOT NULL, email varchar(160) NOT NULL, PRIMARY KEY (adminID), KEY ses (ses) ); DROP TABLE IF EXISTS p_canned; CREATE TABLE IF NOT EXISTS p_canned ( canID int(10) unsigned NOT NULL AUTO_INCREMENT, opID int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, title varchar(35) NOT NULL, message mediumtext NOT NULL, PRIMARY KEY (canID), KEY opID (opID), KEY deptID (deptID) ); DROP TABLE IF EXISTS p_departments; CREATE TABLE IF NOT EXISTS p_departments ( deptID int(10) unsigned NOT NULL AUTO_INCREMENT, visible tinyint(4) NOT NULL, queue tinyint(4) NOT NULL, tshare tinyint(4) NOT NULL, texpire int(10) unsigned NOT NULL, rtype tinyint(4) NOT NULL, rtime int(10) unsigned NOT NULL, img_offline varchar(50) NOT NULL, img_online varchar(50) NOT NULL, name varchar(40) NOT NULL, email varchar(160) NOT NULL, msg_greet text NOT NULL, msg_offline text NOT NULL, msg_email text NOT NULL, PRIMARY KEY (deptID) ); DROP TABLE IF EXISTS p_dept_ops; CREATE TABLE IF NOT EXISTS p_dept_ops ( deptID int(10) unsigned NOT NULL, opID int(10) unsigned NOT NULL, display tinyint(4) NOT NULL, visible tinyint(4) NOT NULL, PRIMARY KEY (deptID,opID) ); DROP TABLE IF EXISTS p_external; CREATE TABLE IF NOT EXISTS p_external ( extID int(10) unsigned NOT NULL AUTO_INCREMENT, name varchar(40) NOT NULL, url varchar(255) NOT NULL, PRIMARY KEY (extID) ); DROP TABLE IF EXISTS p_ext_ops; CREATE TABLE IF NOT EXISTS p_ext_ops ( extID int(10) NOT NULL, opID int(10) NOT NULL, UNIQUE KEY extID (extID,opID) ); DROP TABLE IF EXISTS p_footprints; CREATE TABLE IF NOT EXISTS p_footprints ( created int(10) unsigned NOT NULL, ip varchar(25) NOT NULL, os tinyint(1) NOT NULL, browser tinyint(1) NOT NULL, mdfive varchar(32) NOT NULL, onpage varchar(255) NOT NULL, title varchar(150) NOT NULL, KEY ip (ip), KEY created (created) ); DROP TABLE IF EXISTS p_footprints_u; CREATE TABLE IF NOT EXISTS p_footprints_u ( created int(10) unsigned NOT NULL, updated int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, marketID int(10) unsigned NOT NULL, os tinyint(1) NOT NULL, browser tinyint(1) NOT NULL, resolution varchar(15) NOT NULL, ip varchar(25) NOT NULL, hostname varchar(150) NOT NULL, onpage varchar(255) NOT NULL, title varchar(150) NOT NULL, refer varchar(255) NOT NULL, UNIQUE KEY ip (ip), KEY updated (updated) ); DROP TABLE IF EXISTS p_footstats; CREATE TABLE IF NOT EXISTS p_footstats ( sdate int(10) unsigned NOT NULL, total int(10) unsigned NOT NULL, onpage varchar(255) NOT NULL, KEY sdate (sdate) ); DROP TABLE IF EXISTS p_ips; CREATE TABLE IF NOT EXISTS p_ips ( ip varchar(25) NOT NULL, created int(10) unsigned NOT NULL, t_footprints int(10) unsigned NOT NULL, t_requests int(10) unsigned NOT NULL, t_initiate int(11) NOT NULL, PRIMARY KEY (ip) ); DROP TABLE IF EXISTS p_marketing; CREATE TABLE IF NOT EXISTS p_marketing ( marketID int(10) unsigned NOT NULL AUTO_INCREMENT, skey varchar(4) NOT NULL, name varchar(40) NOT NULL, color varchar(6) NOT NULL, PRIMARY KEY (marketID), KEY skey (skey) ); DROP TABLE IF EXISTS p_market_c; CREATE TABLE IF NOT EXISTS p_market_c ( sdate int(10) unsigned NOT NULL, marketID int(10) unsigned NOT NULL, clicks mediumint(8) unsigned NOT NULL, PRIMARY KEY (sdate,marketID) ); DROP TABLE IF EXISTS p_operators; CREATE TABLE IF NOT EXISTS p_operators ( opID int(10) unsigned NOT NULL AUTO_INCREMENT, lastactive int(10) unsigned NOT NULL, lastrequest int(11) unsigned NOT NULL, status tinyint(4) NOT NULL, signall tinyint(4) NOT NULL, rate tinyint(4) NOT NULL, op2op tinyint(4) NOT NULL, traffic tinyint(4) NOT NULL, ses varchar(32) NOT NULL, login varchar(15) NOT NULL, password varchar(32) NOT NULL, name varchar(40) NOT NULL, email varchar(160) NOT NULL, pic varchar(50) NOT NULL, theme varchar(15) NOT NULL, PRIMARY KEY (opID), KEY ses (ses), KEY lastactive (lastactive,status) ); DROP TABLE IF EXISTS p_opstatus_log; CREATE TABLE IF NOT EXISTS p_opstatus_log ( created int(11) NOT NULL, opID int(11) NOT NULL, status tinyint(4) NOT NULL, KEY created (created) ); DROP TABLE IF EXISTS p_refer; CREATE TABLE IF NOT EXISTS p_refer ( ip varchar(25) NOT NULL, created int(10) unsigned NOT NULL, marketID int(10) unsigned NOT NULL, mdfive varchar(32) NOT NULL, refer varchar(255) NOT NULL, KEY mdfive (mdfive), KEY ip (ip) ); DROP TABLE IF EXISTS p_referstats; CREATE TABLE IF NOT EXISTS p_referstats ( sdate int(10) unsigned NOT NULL, total int(10) unsigned NOT NULL, refer varchar(255) NOT NULL, KEY sdate (sdate) ); DROP TABLE IF EXISTS p_reqstats; CREATE TABLE IF NOT EXISTS p_reqstats ( sdate int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, opID int(10) unsigned NOT NULL, requests int(10) NOT NULL, taken smallint(5) unsigned NOT NULL, declined smallint(5) unsigned NOT NULL, message smallint(5) unsigned NOT NULL, initiated smallint(5) unsigned NOT NULL, PRIMARY KEY (sdate,deptID,opID) ); DROP TABLE IF EXISTS p_requests; CREATE TABLE IF NOT EXISTS p_requests ( requestID int(10) unsigned NOT NULL AUTO_INCREMENT, created int(10) unsigned NOT NULL, updated int(10) unsigned NOT NULL, vupdated int(10) unsigned NOT NULL, status tinyint(1) NOT NULL, deptID int(11) unsigned NOT NULL, opID int(11) unsigned NOT NULL, op2op int(10) unsigned NOT NULL, marketID int(10) NOT NULL, os tinyint(1) NOT NULL, browser tinyint(1) NOT NULL, requests int(10) unsigned NOT NULL, ces varchar(32) NOT NULL, resolution varchar(15) NOT NULL, vname varchar(40) NOT NULL, vemail varchar(160) NOT NULL, ip varchar(25) NOT NULL, hostname varchar(150) NOT NULL, agent varchar(200) NOT NULL, onpage varchar(255) NOT NULL, title varchar(150) NOT NULL, rstring varchar(255) NOT NULL, refer varchar(255) NOT NULL, question text NOT NULL, PRIMARY KEY (requestID), UNIQUE KEY ces (ces), KEY opID (opID), KEY op2op (op2op), KEY updated (updated), KEY status (status) ); DROP TABLE IF EXISTS p_req_log; CREATE TABLE IF NOT EXISTS p_req_log ( ces varchar(32) NOT NULL, created int(10) unsigned NOT NULL, ended int(10) unsigned NOT NULL, status tinyint(1) NOT NULL, deptID int(11) unsigned NOT NULL, opID int(11) unsigned NOT NULL, op2op int(11) NOT NULL, marketID int(10) NOT NULL, os tinyint(1) NOT NULL, browser tinyint(1) NOT NULL, resolution varchar(15) NOT NULL, vname varchar(40) NOT NULL, vemail varchar(160) NOT NULL, ip varchar(25) NOT NULL, hostname varchar(150) NOT NULL, agent varchar(200) NOT NULL, onpage varchar(255) NOT NULL, title varchar(150) NOT NULL, question text NOT NULL, PRIMARY KEY (ces), KEY opID (opID), KEY ip (ip) ); DROP TABLE IF EXISTS p_transcripts; CREATE TABLE IF NOT EXISTS p_transcripts ( ces varchar(32) NOT NULL, created int(11) unsigned NOT NULL, ended int(10) unsigned NOT NULL, deptID int(11) unsigned NOT NULL, opID int(11) unsigned NOT NULL, op2op tinyint(4) NOT NULL, rating tinyint(1) NOT NULL, fsize mediumint(9) NOT NULL, vname varchar(40) NOT NULL, vemail varchar(160) NOT NULL, ip varchar(25) NOT NULL, question text NOT NULL, formatted text NOT NULL, plain text NOT NULL, PRIMARY KEY (ces), KEY ip (ip), KEY created (created), KEY rating (rating), KEY opID (opID) ); DROP TABLE IF EXISTS p_vars; CREATE TABLE IF NOT EXISTS p_vars ( code varchar(10) NOT NULL ); DROP TABLE IF EXISTS p_rstats_depts; DROP TABLE IF EXISTS p_rstats_ops; DROP TABLE IF EXISTS p_dept_vars; DROP TABLE IF EXISTS p_op_vars;" ;
		$query_array = explode( ";", $query ) ;
		return $query_array ;
	}
?>
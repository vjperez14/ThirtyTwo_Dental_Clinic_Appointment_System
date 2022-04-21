<?php
	/************** DO NOT MODIFY BELOW */
	$now = time() ; $var_microtime = ( function_exists( "gettimeofday" ) ) ? 1 : 0 ; $var_process_start = ( $var_microtime ) ? microtime(true) : $now ;
	$var_mem_start = ( function_exists( "memory_get_usage" ) ) ? memory_get_usage() : 0 ;
	if ( defined( 'API_Util_Vars' ) ) { return ; } define( 'API_Util_Vars', true ) ;
	$PHPLIVE_HOST = isset( $_SERVER["HTTP_HOST"] ) ? $_SERVER["HTTP_HOST"] : "unknown_host" ;
	if ( ( $PHPLIVE_HOST == "unknown_host" ) && isset( $_SERVER["SERVER_NAME"] ) ) { $PHPLIVE_HOST = $_SERVER["SERVER_NAME"] ; }
	$PHPLIVE_URI = isset( $_SERVER["REQUEST_URI"] ) ? $_SERVER["REQUEST_URI"] : "unknown_uri" ;
	$PHPLIVE_FULLURL = "$PHPLIVE_HOST/$PHPLIVE_URI" ;
	$PHPLIVE_SECURE = ( ( isset( $_SERVER["HTTPS"] ) && ( preg_match( "/on/i", $_SERVER["HTTPS"] ) ) ) || ( isset( $_SERVER['HTTP_X_FORWARDED_PORT'] ) && ( $_SERVER['HTTP_X_FORWARDED_PORT'] == 443 ) ) || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && ( preg_match( "/https/i", $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) ) ) ? true : false ;
	if ( isset( $CONF_EXTEND ) ) { $CONF['CONF_ROOT'] = "$CONF[DOCUMENT_ROOT]/web/$CONF_EXTEND" ; }
	else { $CONF['CONF_ROOT'] = "$CONF[DOCUMENT_ROOT]/web" ; }
	$CONF["UPLOAD_HTTP"] = "$CONF[BASE_URL]/web" ; $CONF["UPLOAD_DIR"] = $CONF['CONF_ROOT'] ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ; }
	include_once( "$CONF[CONF_ROOT]/vals.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/setup/KEY.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/setup/KEY.php" ) ; } else { print "Error: Missing KEY file." ; exit ; } $opcache = 0 ; $opcache_config = Array() ; if ( function_exists( "opcache_get_status" ) && function_exists( "opcache_invalidate" ) ) { $opcache_config = opcache_get_configuration() ; if ( isset( $opcache_config["directives"] ) && isset( $opcache_config["directives"]["opcache.enable"] ) && $opcache_config["directives"]["opcache.enable"] ) { $opcache = 1 ; } }
	if ( preg_match( "/patch\.php/", $PHPLIVE_URI ) ) { $VERSION = "PATCH_$now" ; } else { include_once( "$CONF[CONF_ROOT]/VERSION.php" ) ; }
	$VARS_INI_UPLOAD = ini_get( "file_uploads" ) ; $VARS_FREEV = ( $KEY == "87d68c42c1a66fdff9481b5b17fb3924" ) ? 1 : 0 ;
	$VARS_BROWSER = Array( 1=>"IE", 2=>"Firefox", 3=>"Chrome", 4=>"Safari", 5=>"Opera", 6=>"Other" ) ;
	$VARS_OS = Array( 1=>"Windows", 2=>"Mac", 3=>"Unix", 4=>"Other", 5=>"Mobile" ) ; $VARS_GID_MIN = 1000000000 ;
	$VARS_IP_CAPTURE = Array( "X_FORWARDED_FOR", "HTTP_X_FORWARDED_FOR", "CF-Connecting-IP", "HTTP_CF_CONNECTING_IP", "REMOTE_ADDR", "HTTP_X_SUCURI_CLIENTIP" ) ;
	$VARS_MISC_MOBILE_MAX_QUIRK = 1 ; // required for proper mobile input no zoom (always 1)
	$VARS_24H = ( !isset( $VALS["TIMEFORMAT"] ) || ( isset( $VALS["TIMEFORMAT"] ) && ( $VALS["TIMEFORMAT"] != 24 ) ) ) ? 0 : 1 ;
	$VARS_TIMEFORMAT = ( !$VARS_24H ) ? "g:i:s a" : "G:i:s" ; $THEMES_EXCLUDE = Array( "combat"=>1, "covert"=>1, "raindrops"=>1, "reload"=>1, "safari"=>1, "soldiers"=>1, "spooky"=>1, "very_pastel"=>1, "strawberry"=>1, "milk"=>1, "winterland"=>1, "hearts"=>1, "notblue"=>1, "leaves"=>1 ) ;
	$geoip = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/geo_data/GeoLite2-City.mmdb" ) ) ? 1 : 0 ;
	$geomap = ( isset( $CONF["geomap"] ) && ( strlen( $CONF["geomap"] ) == 39 ) ) ? 1 : 0 ; $geokey = ( $geomap ) ? $CONF["geomap"] : "" ;
	$VARS_JS_AUTOLINK_FILE = "min" ; $patch_v = 252 ; $FAST_PATCH = 0 ; $VARS_DB_ERROR_LOG_FILE = "" ; $VARS_ADDON_EMARKET_ENABLED = 1 ;
	$NO_CACHE = 1 ; if ( PHP_SAPI !== 'cli' ) { include_once( "$CONF[DOCUMENT_ROOT]/inc_cache.php" ) ; }
	$VARS_IFRAME_BACKGROUND = "initiate/iframe_bg.gif" ;
	/************** DO NOT MODIFY ABOVE */
	//
	// To change any of the below variables, create a new file API/Util_Extra.php (detailed at the end of this file)
	// Variable infomation located at: README/VARS.txt
	//
	// An example Util_Extra.php file is located at phplive/examples/Util_Extra.php
	//
	$CONF["CHAT_IO_DIR"] = "$CONF[CONF_ROOT]/chat_sessions" ;
	$CONF["TYPE_IO_DIR"] = "$CONF[CONF_ROOT]/chat_initiate" ;
	$CONF["ATTACH_DIR"] = "$CONF[CONF_ROOT]/file_attach" ;
	$CONF["TEMP_DIR"] = "$CONF[CONF_ROOT]/file_temp" ;
	$CONF["EXPORT_DIR"] = "$CONF[CONF_ROOT]/exported_files" ;

	$VAR_DEBUG_OUT = 0 ;
	$VARS_SET_VERIFYPEER = 1 ;
	$VARS_E_ALL = 0 ;

	$VARS_JS_REQUESTING = 3 ;
	$VARS_JS_FOOTPRINT_CHECK = 60 ;
	$VARS_JS_CHATICON_CHECK = 25 ;
	$VARS_JS_FOOTPRINT_MAX_CYCLE = 45 ;
	$VARS_JS_RATING_FETCH = 25 ;
	$VARS_FOOTPRINT_U_EXPIRE = $VARS_JS_FOOTPRINT_CHECK * 3 ;
	$VARS_IP_LOG_EXPIRE = 14 ;
	$VARS_FOOTPRINT_STATS_EXPIRE = 14 ;
	$VARS_JS_OP_CONSOLE_TIMEOUT = 45 ;
	$VARS_CYCLE_VUPDATE = 10 ;
	$VARS_CYCLE_CLEAN = $VARS_JS_REQUESTING + 6 ;
	$VARS_CYCLE_CLEAN_Q = 4 ;
	$VARS_EXPIRED_OPS = $VARS_CYCLE_CLEAN * 34 ;
	$VARS_EXPIRED_REQS = 1800 ;
	$VARS_EXPIRED_ACTIVE_REQS = 330 ;
	$VARS_EXPIRED_OP2OP = $VARS_EXPIRED_REQS ;
	$VARS_EXPIRED_QUEUE_IDLE = 30 ;
	$VARS_PEER_REQUEST_EXPIRE = 60 ;
	$VARS_MOBILE_CHAT_BUFFER = 300 ;
	$VARS_MAIL_SEND_BUFFER = .25 ;
	$VARS_CHAT_WIDTH = 540 ;
	$VARS_CHAT_HEIGHT = 580 ;
	$VARS_CHAT_WIDTH_WIDGET = 415 ;
	$VARS_CHAT_HEIGHT_WIDGET = 600 ;
	$VARS_CHAT_PADDING_WIDGET = 20 ;
	$VARS_CHAT_PADDING_SVG_ICON = 20 ;
	$VARS_CHAT_PADDING_WIDGET_BOTTOM = 20 ;
	$VARS_CHAT_PADDING_WIDGET_RADIUS = 10 ;
	$VARS_MYSQL_FREE_RESULTS = 0 ;
	$VARS_MAX_CHAT_FILESIZE = 5000000 ;
	$VARS_SETUP_IDLE_LOGOUT = 30 ;
	$VARS_MAX_MBOARD_MESSAGES = 500000 ;
	$VARS_REFRESH_ACTIVE_CHATS = 25 ;
	$VARS_REFRESH_TRAFFIC_MONITOR = 60 ;
	$VARS_REFRESH_OP_MONITOR = 25 ;
	$VARS_MAX_DEPT_GROUP_DEPTS = 5 ;

	/*****************************************************************************/
	/* To change a variable from above, create a new file Util_Extra.php within the directory phplive/API/ and place the variable changes there.
	// --- phplive/API/Util_Extra.php ---
	//
	// example:
	//	to change the variable $VARS_IP_LOG_EXPIRE, place the variable in the API/Util_Extra.php
	//	with your new value.  if new variables or values are introduced, your changes will not revert
	//	to the default values. */
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Extra.php" ) ; }
?>
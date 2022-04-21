<?php
	if ( defined( 'API_Util_Error' ) ) { return ; }	
	define( 'API_Util_Error', true ) ;

	$display_errors = isset( $VARS_E_ALL ) ? $VARS_E_ALL : 0 ;

	if ( isset( $_GET["e_all"] ) ) { error_reporting(E_ALL) ; }
	else if ( isset( $VARS_E_ALL ) && $VARS_E_ALL ) { error_reporting(E_ALL) ; }
	else if ( $display_errors ) { error_reporting(E_ALL) ; }
	else { error_reporting(0) ; }

	FUNCTION ErrorHandler( $errno, $errmsg, $filename, $linenum, $vars ) 
	{
		global $CONF ; global $VERSION ; global $PHPLIVE_HOST ; global $KEY ; global $dbh ; global $embed ; global $page_origin ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		$VERSION = ( isset( $VERSION ) ) ? $VERSION : "invalid" ;
		$ckey = ( isset( $KEY ) ) ? $KEY : "error-no-license" ;
		$query = isset( $_SERVER["QUERY_STRING"] ) ? Util_Format_Sanatize( $_SERVER["QUERY_STRING"], "query" ) : "" ;
		if ( !isset( $page_origin ) ) { $page_origin = "" ; }
		if ( ( phpversion() >= "5.1.0" ) && !isset( $CONF["TIMEZONE"] ) || !$CONF["TIMEZONE"] ){ date_default_timezone_set( "America/New_York" ) ; }

		// 600-699 is custom error reserved for PHP Live!
		$errortype = array (
			1		=>  "Error",
			2		=>  "Warning",
			4		=>  "Parsing Error",
			8		=>  "Notice",
			16		=>  "Core Error",
			32		=>  "Core Warning",
			64		=>  "Compile Error",
			128		=>  "Compile Warning",
			256		=>  "User Error",
			512		=>  "User Warning",
			1024	=>  "User Notice",
			600		=>	"PHP Live! DB Connection Failed",
			601		=>	"PHP Live! Configuration Missing",
			602		=>	"PHP Live! Operator Session Expired",
			603		=>	"PHP Live! Chat Request Not Created",
			604		=>	"PHP Live! DB Data Error",
			605		=>	"PHP Live! Error",
			606		=>	"PHP Live! Patch Loop Error",
			607		=>	"PHP Live! version not compatible with WinApp",
			608		=>	"PHP Live! Setup Session Expired",
			609		=>	"PHP Live! Directory or file permission denied.",
			610		=>	"PHP Live! Directory not found.",
			611		=>	"PHP Live! Update your language pack.",
			612		=>	"PHP Live! DOCUMENT_ROOT is invalid.",
			613		=>	"PHP Live! MySQL PDO Extension Not Enabled",
			614		=>	"PHP Live! MySQLi Extension Not Enabled",
			615		=>	"PHP Live! Could not create system file: Addon ProAction"
		) ;
		if ( $errno == 602 ) { HEADER( "location: $CONF[BASE_URL]/logout.php?$query&errno=$errno&action=logout" ) ; exit ; }
		else if ( $errno == 608 ) { HEADER( "location: $CONF[BASE_URL]/logout.php?$query&menu=sa&action=logout" ) ; exit ; }
		if ( $errno )
		{
			if ( preg_match( "/gethostbyaddr/", $errmsg ) ) { return true ; }
			else {
				$errmsg = strip_tags( $errmsg ) ;
				if ( preg_match( "/Connection timed out/i", $errmsg ) && preg_match( "/phplivesupport/i", $PHPLIVE_HOST ) ) { $errmsg = "<span style='font-size: 16px; font-weight: bold;'>System is currently being updated.  Thank you for your patience.</span>" ; }
				$errmsg_query = urlencode( $errmsg ) ;

				$admin_email = ( isset( $_SERVER['SERVER_ADMIN'] ) ) ? " <a href=\"mailto:$_SERVER[SERVER_ADMIN]\">$_SERVER[SERVER_ADMIN]</a>" : "" ;
				$script = ( isset( $_SERVER['SCRIPT_NAME'] ) ) ? $_SERVER['SCRIPT_NAME'] : $filename ;
				$path_array = explode( "/", $script ) ;
				$path_total = count( $path_array ) ;

				if ( $path_total )
				{
					if ( $path_array[$path_total-2] == "ops" )
						$script_path = "ops/".$path_array[$path_total-1] ;
					else if ( $path_array[$path_total-2] == "setup" )
						$script_path = "setup/".$path_array[$path_total-1] ;
					else
						$script_path = $path_array[$path_total-1] ;
				}
				$script_encoded = urlencode( $script_path ) ;
				$solution_url = "https://www.phplivesupport.com/help_desk.php?errornum=$errno&error=$errmsg_query&script=$script_encoded&line=$linenum&key=$ckey" ;
				$output = file_get_contents( "$CONF[DOCUMENT_ROOT]/files/error_notice.php" ) ;
				$output = preg_replace( "/%file%/", $script_path, $output ) ;
				$output = preg_replace( "/%line%/", $linenum, $output ) ;
				$output = preg_replace( "/%error%/", $errmsg, $output ) ;
				$output = preg_replace( "/%base_url%/", $CONF["BASE_URL"], $output ) ;
				$output = preg_replace( "/%solution%/", $solution_url, $output ) ;
				$output = preg_replace( "/%admin%/", $admin_email, $output ) ;
				$output = preg_replace( "/%version%/", "PHP Live! v.$VERSION", $output ) ;
				$output = preg_replace( "/%page_origin%/", $page_origin, $output ) ;
				$embed_close = ( isset( $embed ) && $embed ) ? "<div style=\"margin-top: 55px;\"><center><button type=\"button\" onClick=\"parent_send_message( 'close', 0 )\" style=\"padding: 10px; width: 120px;\">Close</button></center></div>" : "" ;
				$output = preg_replace( "/%embed_close%/", $embed_close, $output ) ;
				print $output ;
				error_log( "[ PHP Live! ] $errmsg in $script_path on line $linenum", 0 ) ;
				exit ;
			}
		}
	} set_error_handler( "ErrorHandler" ) ;
?>
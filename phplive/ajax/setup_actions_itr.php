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
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$setupinfo = Util_Security_AuthSetup( $dbh ) )
	{
		$json_data = "json_data = { \"status\": 0, \"error\": \"Authentication error.\" };" ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	// STANDARD header end
	/****************************************/

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;

	if ( $action === "systime" )
	{
		$systime = date( "M j $VARS_TIMEFORMAT", time() ) ;
		$json_data = "json_data = { \"status\": 1, \"systime\": \"$systime\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action.\" };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
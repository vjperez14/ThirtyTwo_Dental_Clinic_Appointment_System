<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	include_once( "../../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;

	$status = 0 ;
	$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "ln" ) ;
	if ( strlen( $opid ) == 32 )
	{
		$accountinfo = Util_Security_AuthOp( $dbh ) ;
		if ( isset( $accountinfo["lastactive"] ) )
		{
			$status = 1 ;
			$opid = $accountinfo["opID"] ;
		}
	}
	else
	{
		$accountinfo = Util_Security_AuthSetup( $dbh ) ;
		if ( isset( $accountinfo["lastactive"] ) )
		{
			$status = 2 ;
			if ( !$opid ) { $opid = 0 ; }
		}
	}

	if ( !$status )
		$json_data = "json_data = { \"status\": $status, \"error\": \"Authentication error.\" };" ;
	else
	{
		$filename = "profile_$opid" ;

		if ( is_file( "$CONF[CONF_ROOT]/$filename.PNG" ) )
			@unlink( "$CONF[CONF_ROOT]/$filename.PNG" ) ;
		else if ( is_file( "$CONF[CONF_ROOT]/$filename.JPEG" ) )
			@unlink( "$CONF[CONF_ROOT]/$filename.JPEG" ) ;
		else if ( is_file( "$CONF[CONF_ROOT]/$filename.GIF" ) )
			@unlink( "$CONF[CONF_ROOT]/$filename.GIF" ) ;

		$filename = "profile_{$opid}.JPEG" ;
		$file_to_upload = $_FILES['avatar']['tmp_name'] ;
		move_uploaded_file( $file_to_upload, "$CONF[CONF_ROOT]/$filename" ) ;

		$json_data = "json_data = { \"status\": $status };" ;
	}

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
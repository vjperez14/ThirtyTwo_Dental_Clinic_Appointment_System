<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	error_reporting(0) ;
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;
	$filename = Util_Format_Sanatize( Util_Format_GetVar( "filename" ), "ln" ) ;

	$error_string = "Method Not Allowed" ; $error_code = 405 ;
	$protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ;
	if ( $ces && is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) )
	{
		$data = file_get_contents( "php://input" ) ;
		if ( $data && preg_match( "/\.((png)|(gif)|(jpg)|(jpeg))$/", $filename ) )
		{
			$fileinfo = getimagesize( $data ) ;
			file_put_contents( "$CONF[ATTACH_DIR]/$filename", $data ) ;
		} else { HEADER( $protocol . " $error_code $error_string" ) ; }
	} else { HEADER( $protocol . " $error_code $error_string" ) ; }
?>
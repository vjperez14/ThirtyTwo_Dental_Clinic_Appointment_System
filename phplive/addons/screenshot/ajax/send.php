<?php
	include_once( "../../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$isop = Util_Format_Sanatize( Util_Format_GetVar( "isop" ), "n" ) ;
	$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;
	$salt = Util_Format_Sanatize( Util_Format_GetVar( "salt" ), "ln" ) ;
	$filename = Util_Format_Sanatize( Util_Format_GetVar( "filename" ), "lnss" ) ;
	$image_data = Util_Format_Sanatize( Util_Format_GetVar( "image" ), "" ) ;

	if ( ( md5( md5( $CONF["SALT"] ).$ces ) == $salt ) && is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;

		if ( preg_match( "/^data:image/i", $image_data ) )
		{
			if ( !is_dir( "$CONF[ATTACH_DIR]" ) ) { mkdir( "$CONF[ATTACH_DIR]", 0777 ) ; usleep( 250000 ) ; }
			if ( !is_dir( "$CONF[ATTACH_DIR]/screenshots" ) )
			{
				mkdir( "$CONF[ATTACH_DIR]/screenshots", 0777 ) ; usleep( 250000 ) ;
				$index_file = "$CONF[DOCUMENT_ROOT]/files/index.php" ;
				if ( is_dir( "$CONF[ATTACH_DIR]/screenshots" ) && !is_file( "$CONF[ATTACH_DIR]/screenshots/index.php" ) ) { @copy( $index_file, "$CONF[ATTACH_DIR]/screenshots/index.php" ) ; }
			}

			$image_parts = explode( ";base64,", $image_data ) ;
			$image_base64 = base64_decode( $image_parts[1] ) ;

			if ( !$filename || is_file( "$CONF[ATTACH_DIR]/screenshots/{$filename}.PNG" ) )
				$filename = "screenshot_{$ces}_".Util_Format_RandomString(6) ;

			$file_path = "$CONF[ATTACH_DIR]/screenshots/{$filename}.PNG" ;

			if ( file_put_contents( $file_path, $image_base64 ) )
			{
				$default_path = ( preg_match( "/\/web\/file_attach/", $CONF["ATTACH_DIR"] ) ) ? 1 : 0 ;
				$json_data = "json_data = { \"status\": 1, \"default_path\": $default_path };" ;
			}
			else
				$json_data = "json_data = { \"status\": 0, \"error\": \"Error uploading screenshot.\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid image type.\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action.\" };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
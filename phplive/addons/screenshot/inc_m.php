<?php
	$scr_data = Util_Format_Sanatize( Util_Format_GetVar( "scr_data" ), "" ) ;
	$attach_file = Util_Format_Sanatize( Util_Format_GetVar( "file" ), "b64" ) ;

	$file_path_http = "" ;
	if ( !is_dir( "$CONF[ATTACH_DIR]" ) ) { mkdir( "$CONF[ATTACH_DIR]", 0777 ) ; usleep( 250000 ) ; }
	if ( !is_dir( "$CONF[ATTACH_DIR]/screenshots" ) )
	{
		mkdir( "$CONF[ATTACH_DIR]/screenshots", 0777 ) ; usleep( 250000 ) ;
		$index_file = "$CONF[DOCUMENT_ROOT]/files/index.php" ;
		if ( is_dir( "$CONF[ATTACH_DIR]/screenshots" ) && !is_file( "$CONF[ATTACH_DIR]/screenshots/index.php" ) ) { @copy( $index_file, "$CONF[ATTACH_DIR]/screenshots/index.php" ) ; }
	}
	if ( isset( $custom_vars ) && preg_match( "/^data:image/i", $scr_data ) )
	{
		$scr_data = str_replace( 'data:image/png;base64,', '', $scr_data ) ;
		$scr_data = str_replace( ' ', '+', $scr_data ) ;
		$image_base64 = base64_decode( $scr_data ) ;

		$filename = "screenshot_vis_".Util_Format_RandomString(6) ;

		$file_path = "$CONF[ATTACH_DIR]/screenshots/{$filename}.PNG" ;

		if ( file_put_contents( $file_path, $image_base64 ) )
		{
			$file_path_http = "$CONF[BASE_URL]/view.php?file={$filename}.PNG" ;
			$custom_vars .= "-cus-Screenshot URL-_-{$file_path_http}" ;
		}
	}
	if ( $attach_file )
	{
		$attach_file = base64_decode( $attach_file ) ;
		if ( is_file( "$CONF[ATTACH_DIR]/$attach_file" ) )
		{
			$file_path_http = "$CONF[BASE_URL]/view.php?file={$attach_file}" ;
			$custom_vars .= "-cus-Attachment URL-_-{$file_path_http}" ;
		}
	}
?>
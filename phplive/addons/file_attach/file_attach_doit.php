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
	include_once( "../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/addons/file_attach/API/Util_Upload_File.php" ) ;

	$error = $filename = $filename_ = "" ; $fname = "same" ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$ces = Util_Format_Sanatize( Util_Format_GetVar( "attach_ces" ), "ln" ) ;
	$token = Util_Format_Sanatize( Util_Format_GetVar( "attach_token" ), "ln" ) ; $ces_token = "" ;
	if ( $token )
	{
		$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
		LIST( $ip, $vis_token ) = Util_IP_GetIP( $token ) ;
		$ces_token = md5( $agent.$ip.$vis_token ) ;
	}

	if ( !is_dir( "$CONF[ATTACH_DIR]" ) ) { mkdir( "$CONF[ATTACH_DIR]", 0777 ) ; usleep( 250000 ) ; }
	if ( !is_dir( "$CONF[ATTACH_DIR]/screenshots" ) )
	{
		mkdir( "$CONF[ATTACH_DIR]/screenshots", 0777 ) ; usleep( 250000 ) ;
		$index_file = "$CONF[DOCUMENT_ROOT]/files/index.php" ;
		if ( is_dir( "$CONF[ATTACH_DIR]/screenshots" ) && !is_file( "$CONF[ATTACH_DIR]/screenshots/index.php" ) ) { @copy( $index_file, "$CONF[ATTACH_DIR]/screenshots/index.php" ) ; }
	}

	if ( $action == "delete" )
	{
		$filename = Util_Format_Sanatize( Util_Format_GetVar( "file" ), "ln" ) ;
		if ( $ces == $ces_token )
		{
			if ( is_file( "$CONF[ATTACH_DIR]/$filename" ) ) { @unlink( "$CONF[ATTACH_DIR]/$filename" ) ; }
			$json_data = "json_data = { \"status\": 1 }" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error deleting file.\" }" ;
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$status = Util_Format_Sanatize( Util_Format_GetVar( "attach_status" ), "n" ) ;
	$upload_formats_string = base64_decode( Util_Format_Sanatize( Util_Format_GetVar( "attach_uf" ), "b64" ) ) ;
	$disconnected = Util_Format_Sanatize( Util_Format_GetVar( "attach_dis" ), "n" ) ;

	$upload_max_filesize = ini_get( "upload_max_filesize" ) ;
	$upload_max_post = ini_get( "post_max_size" ) ;

	if ( $upload_max_filesize && preg_match( "/k/i", $upload_max_filesize ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
		$max_bytes = $temp * 1000 ;
	}
	else if ( $upload_max_filesize && preg_match( "/m/i", $upload_max_filesize ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
		$max_bytes = $temp * 1000000 ;
	}
	else if ( $upload_max_filesize && preg_match( "/g/i", $upload_max_filesize ) )
	{
		$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
		$max_bytes = $temp * 1000000000 ;
	}
	else { $max_bytes = 500000 ; }

	if ( isset( $VALS["UPLOAD_MAX"] ) && $VALS["UPLOAD_MAX"] )
	{
		$upmax_array = unserialize( $VALS["UPLOAD_MAX"] ) ;
		$max_bytes = $upmax_array["bytes"] ;
		$fname = ( isset( $upmax_array["fname"] ) && ( $upmax_array["fname"] == "random" ) ) ? "random" : "same" ;
	}

	if ( $action === "upload" )
	{
		if ( $VARS_INI_UPLOAD && $ces && $status && !$disconnected && $upload_formats_string && ( is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) || ( $ces == $ces_token ) ) )
		{
			LIST( $error, $filename ) = Util_Upload_File( $upload_formats_string ) ;
			$filename_ = $filename ;
			if ( !$error )
			{
				if ( is_file( "$CONF[DOCUMENT_ROOT]/web/file_attach/$filename" ) )
					$filename = "$CONF[BASE_URL]/web/file_attach/$filename" ;
				else if ( isset( $CONF_EXTEND ) && is_file( "$CONF[DOCUMENT_ROOT]/web/$CONF_EXTEND/file_attach/$filename" ) )
					$filename = "$CONF[BASE_URL]/web/$CONF_EXTEND/file_attach/$filename" ;
				else
				{
					if ( is_file( "$CONF[ATTACH_DIR]/$filename" ) )
					{
						$image_path = "$CONF[ATTACH_DIR]/$filename" ;
						if ( preg_match( "/\.pdf$/i", $filename ) )
							$image_type = 20 ;
						else if ( preg_match( "/((\.txt)|(\.text))$/i", $filename ) )
							$image_type = 21 ;
						else if ( preg_match( "/\.conf$/i", $filename ) )
							$image_type = 22 ;
						else if ( preg_match( "/\.zip$/i", $filename ) )
							$image_type = 23 ;
						else if ( preg_match( "/\.tar$/i", $filename ) )
							$image_type = 24 ;
						else
							$image_type = exif_imagetype( $image_path ) ;
						$filename = Util_Upload_Output( $image_path, $image_type ) ;
						if ( !$filename )
							$error = "File Upload Formats:<br>$upload_formats_string" ;
					}
					else
						$error = "Could not locate uploaded file." ;
				}
			}
		}
		else
			$error = "A chat session must be active." ;
	}
?>
<?php include_once( "$CONF[DOCUMENT_ROOT]/inc_doctype.php" ) ?>
<head>
<title> Upload </title>
<meta name="author" content="osicodesinc">
<meta name="mapp" content="active">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "$CONF[DOCUMENT_ROOT]/inc_meta_dev.php" ) ; ?>
<script data-cfasync="false" type="text/javascript">
<!--

	<?php if ( $action && !$error ): ?>
		parent.upload_success( '<?php echo $filename ?>', '<?php echo $filename_ ?>' ) ;
	<?php else: ?>
		parent.upload_error( '<?php echo $error ?>' ) ;
	<?php endif ; ?>

//-->
</script>
</head>
<body style="background: transparent;"></body>
</html>
<?php
	if ( defined( 'API_Util_Upload_File' ) ) { return ; }	
	define( 'API_Util_Upload_File', true ) ;

	FUNCTION Util_Upload_File( $icon, $deptid )
	{
		global $CONF ;

		$upload_max_filesize = ini_get( "upload_max_filesize" ) ;
		$upload_max_post = ( ini_get( "post_max_size" ) ) ? ini_get( "post_max_size" ) : $upload_max_filesize ;

		$now = time() ;
		$extension = $error = $filename = "" ;

		if ( !defined( 'API_Util_Vals' ) )
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

		if ( isset( $_SERVER['CONTENT_LENGTH'] ) && ( $_SERVER['CONTENT_LENGTH'] > 3000000 ) )
			$error = "File size must be 3M or less." ;
		else if ( $_FILES[$icon]['name'] && !$_FILES[$icon]['tmp_name'] )
			$error = "File size must be $upload_max_post or less." ;
		else if ( !$_FILES[$icon]['tmp_name'] )
			$error = "Nothing to upload. [e1]" ;
		else if ( isset( $_FILES[$icon]['size'] ) )
		{
			$filename = basename( $_FILES[$icon]['name'] ) ;
			$fileinfo = getimagesize( $_FILES[$icon]['tmp_name'] ) ;
			$filetype = $_FILES[$icon]['type'] ;
			$errorno = $_FILES[$icon]['error'] ;
			$filesize = $_FILES[$icon]['size'] ;
			$filename_parts = explode( ".", $filename ) ;

			if ( $errorno == UPLOAD_ERR_NO_FILE )
				$error = "Nothing to upload. [e2]" ;
			else if ( !is_uploaded_file( $_FILES[$icon]['tmp_name'] ) )
				$error = "Invalid file." ;
			else if ( !$fileinfo )
				$error = "Please upload a GIF, PNG, or JPG file." ;
			else if ( count( $filename_parts ) == 1 )
				$error = "Could not detect the file type extension." ;
			else if ( count( $filename_parts ) > 2 )
				$error = "File name should contain only one dot. (example: image.jpg)" ;
			else if ( !preg_match( "/(gif)|(jpeg)|(jpg)|(png)/i", $filename_parts[1] ) )
				$error = "Please upload a GIF, PNG, or JPG file." ;
			else if ( $errorno == UPLOAD_ERR_OK )
			{
				if ( preg_match( "/gif/i", $filetype ) )
					$extension = "GIF" ;
				else if ( preg_match( "/(jpeg)|(jpg)/i", $filetype ) )
					$extension = "JPEG" ;
				else if ( preg_match( "/png/i", $filetype ) )
					$extension = "PNG" ;

				if ( $extension )
				{
					if ( preg_match( "/(online)|(offline)|(initiate)|(logo)|(profile)/", $icon ) )
					{
						$filename = $icon."_$deptid" ;

						if ( is_file( "$CONF[CONF_ROOT]/$filename.PNG" ) )
							@unlink( "$CONF[CONF_ROOT]/$filename.PNG" ) ;
						else if ( is_file( "$CONF[CONF_ROOT]/$filename.JPEG" ) )
							@unlink( "$CONF[CONF_ROOT]/$filename.JPEG" ) ;
						else if ( is_file( "$CONF[CONF_ROOT]/$filename.GIF" ) )
							@unlink( "$CONF[CONF_ROOT]/$filename.GIF" ) ;

						$filename = $icon."_$deptid.$extension" ;
					}
					else
						$filename = "$icon.$extension" ;

					if( move_uploaded_file( $_FILES[$icon]['tmp_name'], "$CONF[CONF_ROOT]/$filename" ) )
					{
						if ( preg_match( "/(logo)|(initiate)/", $icon ) && !$deptid )
							$error = ( Util_Vals_WriteToConfFile( $icon, $filename ) ) ? "" : "Could not write to config file." ;
					}
					else
						$error = "Could not process uploading of files" ;
				}
				else
					$error = "Please upload a GIF, PNG, or JPG file." ;
			}
			else if ( $errorno == UPLOAD_ERR_NO_TMP_DIR )
				$error = "Upload temp dir \"upload_tmp_dir\" not set or not writeable." ;
			else if ( $errorno == UPLOAD_ERR_FORM_SIZE )
				$error = "File size must be 200KB or less." ;
			else if ( $errorno == UPLOAD_ERR_INI_SIZE )
				$error = "File size must be upload_max_filesize directive or less." ;
			else if ( $errorno )
				$error = "Error in uploading. [errorno: $errorno]" ;
			else
				$error = "Error in uploading." ;
		}
		else
			$error = "Nothing to upload. [e3]" ;
		return Array( $error, $filename ) ;
	}
?>

<?php
	if ( defined( 'API_Chat_Util' ) ) { return ; }
	define( 'API_Chat_Util', true ) ;

	FUNCTION UtilChat_AppendToChatfile( $chatfile,
							$string )
	{
		if ( ( $chatfile == "" ) || ( $string == "" ) )
			return false ;
		global $CONF ; global $VARS_MAX_CHAT_FILESIZE ;
		$string .= "<>" ; // add new line marker

		$filesize = is_file( "$CONF[CHAT_IO_DIR]/$chatfile" ) ? filesize( "$CONF[CHAT_IO_DIR]/$chatfile" ) : 0 ;
		if ( $filesize < $VARS_MAX_CHAT_FILESIZE )
		{
			if ( $filesize > ( $VARS_MAX_CHAT_FILESIZE - 10000 ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;

				$max_size = Util_Functions_Bytes( $VARS_MAX_CHAT_FILESIZE ) ;
				$string .= base64_encode( "<div class='info_error' style='margin-bottom: 5px;'>Chat size is approaching max limit ($max_size).  Please end the chat soon and start a new chat session.</div>" )."<>" ;
			}
			file_put_contents( "$CONF[CHAT_IO_DIR]/$chatfile", $string, FILE_APPEND | LOCK_EX ) ;
			return true ;
		}
		else
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;

			$max_size = Util_Functions_Bytes( $VARS_MAX_CHAT_FILESIZE ) ;
			$string = base64_encode( "<div class='info_error' style='margin-bottom: 5px;'>Max chat size limit reached ($max_size).  Message was not delivered.  Please end the chat and start a new chat session.</div>" )."<>" ;
			file_put_contents( "$CONF[CHAT_IO_DIR]/$chatfile", $string, FILE_APPEND | LOCK_EX ) ;
			return false ;
		}
	}

	FUNCTION UtilChat_ExportChat( $chatfile )
	{
		if ( $chatfile == "" )
			return false ;
		global $CONF ;

		$output = Array() ; $fmsize = 0 ;
		if ( is_file( "$CONF[CHAT_IO_DIR]/$chatfile" ) )
		{
			$fmsize = filesize( "$CONF[CHAT_IO_DIR]/$chatfile" ) ;
			$trans_raw = file_get_contents( "$CONF[CHAT_IO_DIR]/$chatfile" ) ;
			$trans_raw_array = explode( "<>", $trans_raw ) ;
			$trans_raw_output = "" ;
			for( $c = 0; $c < count( $trans_raw_array ); ++$c )
			{
				$text = base64_decode( $trans_raw_array[$c] ) ;
				// <!--vc_init_ contains large data string.  no need to save
				if ( !preg_match( "/<!--vc_init_/", $text ) )
				{
					$trans_raw_output .= $text . "<>" ;
				}
			}
			$output[] = $trans_raw_output ;
			$output[] = preg_replace( "/<(.*?)>/", "", preg_replace( "/<>/", "\r\n", preg_replace( "/<a href='(.*?)'(.*?)a>/i", "$1", $trans_raw_output ) ) ) ;
		} return Array( $fmsize, $output ) ;
	}

	FUNCTION UtilChat_WriteIsWriting( $theces, $theflag, $theisop, $theisop_, $theisop__ )
	{
		if ( $theces == "" )
			return false ;
		global $CONF ;

		$iid = $theisop ;
		$typing_file = "{$theces}{$iid}.txt" ;
		if ( is_file( "$CONF[CHAT_IO_DIR]/{$theces}.txt" ) )
		{
			if ( $theflag )
			{
				if ( !is_file( "$CONF[TYPE_IO_DIR]/$typing_file" ) )
					touch( "$CONF[TYPE_IO_DIR]/$typing_file" ) ;
			}
			else
			{
				if ( is_file( "$CONF[TYPE_IO_DIR]/$typing_file" ) )
					@unlink( "$CONF[TYPE_IO_DIR]/$typing_file" ) ;
			} return true ;
		}
		else
			return false ;
	}
?>
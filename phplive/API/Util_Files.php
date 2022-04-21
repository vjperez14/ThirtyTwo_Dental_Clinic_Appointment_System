<?php
	if ( defined( 'API_Util_Files' ) ) { return ; }	
	define( 'API_Util_Files', true ) ;

	FUNCTION Util_Files_CleanExportDir()
	{
		global $CONF ;
		global $now ;

		$expired = $now - (60*5) ;

		if ( is_dir( $CONF["EXPORT_DIR"] ) )
		{
			$dir_files = glob( "$CONF[EXPORT_DIR]/transcripts_export_*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
					{
						$modtime = filemtime( $dir_files[$c] ) ;
						if ( $modtime < $expired )
						{
							if ( $dir_files[$c] && is_file( $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
						}
					}
				}
			}
		}
	}

	FUNCTION Util_Files_CleanUploadDir()
	{
		global $CONF ;
		global $VALS ;
		global $now ; $max_days = 365 ;

		if ( isset( $VALS["UPLOAD_MAX"] ) && $VALS["UPLOAD_MAX"] )
		{
			$upmax_array = unserialize( $VALS["UPLOAD_MAX"] ) ;
			$max_days = $upmax_array["days"] ;
		}
		$dir_files = glob( $CONF["ATTACH_DIR"]."/*.*", GLOB_NOSORT ) ;
		$total_dir_files = count( $dir_files ) ;
		if ( $total_dir_files )
		{
			for ( $c = 0; $c < $total_dir_files; ++$c )
			{
				$file = $dir_files[$c] ;
				$modtime = filemtime( $file ) ;
				if ( $modtime < ( $now - (60*60*24*$max_days) ) )
				{
					if ( is_file( $file ) && !preg_match( "/index\.php$/", $file ) ) { @unlink( $file ) ; }
				}
			}
		}
		$dir_files = glob( $CONF["ATTACH_DIR"]."/screenshots/*.*", GLOB_NOSORT ) ;
		$total_dir_files = count( $dir_files ) ;
		if ( $total_dir_files )
		{
			for ( $c = 0; $c < $total_dir_files; ++$c )
			{
				$file = $dir_files[$c] ;
				$modtime = filemtime( $file ) ;
				if ( $modtime < ( $now - (60*60*24*$max_days) ) )
				{
					if ( is_file( $file ) && !preg_match( "/index\.php$/", $file ) ) { @unlink( $file ) ; }
				}
			}
		}
	}
?>
<?php
	if ( defined( 'API_Chat_remove_itr' ) ) { return ; }
	define( 'API_Chat_remove_itr', true ) ;

	FUNCTION Chat_remove_itr_OldRequests( &$dbh )
	{
		global $CONF ;
		global $VALS ;
		global $VARS_EXPIRED_REQS ; global $VARS_EXPIRED_ACTIVE_REQS ;
		$now = time() ;
		$expired = $now - $VARS_EXPIRED_REQS ;
		$expired_active = $now - $VARS_EXPIRED_ACTIVE_REQS ;

		$expired_requests = $expired_requests_hash = $file_ces = Array() ;
		$dir_files = glob( $CONF["CHAT_IO_DIR"].'/*.txt', GLOB_NOSORT ) ;
		$total_dir_files = count( $dir_files ) ;
		if ( $total_dir_files )
		{
			for ( $c = 0; $c < $total_dir_files; ++$c )
			{
				$ces = str_replace( "$CONF[CHAT_IO_DIR]", "", $dir_files[$c] ) ;
				$ces = preg_replace( "/[\\/]|(.txt)/", "", $ces ) ;
				if ( $ces ) { $file_ces[$ces] = 1 ; }
			}
		}

		$query = "SELECT * FROM p_requests WHERE ( ( updated < $expired OR ( vupdated < $expired AND vupdated <> 615 ) ) OR ( status = 1 AND ( updated < $expired_active OR vupdated < $expired_active ) ) ) AND op2op = 0 AND md5_vis_ <> 'grc'" ;
		database_mysql_query( $dbh, $query ) ;
		if ( $dbh[ 'ok' ] )
		{
			if ( database_mysql_nresults( $dbh ) || $total_dir_files )
			{
				while( $data = database_mysql_fetchrow( $dbh ) )
				{
					$expired_requests[] = $data ;
					$expired_requests_hash[$data["ces"]] = 1 ;
				}

				if ( !defined( 'API_Chat_get' ) )
					include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
				if ( !defined( 'API_Chat_get_itr' ) )
					include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_itr.php" ) ;

				foreach ( $file_ces as $this_ces => $the_flag )
				{
					if ( !isset( $expired_requests_hash[$this_ces] ) )
					{
						$requestinfo = Chat_get_itr_RequestCesInfo( $dbh, $this_ces ) ;
						if ( !isset( $requestinfo["ces"] ) )
						{ $expired_requests[] = Chat_get_RequestHistCesInfo( $dbh, $this_ces ) ; }
					}
				}
				if ( count( $expired_requests ) )
				{
					if ( !defined( 'API_Chat_Util' ) )
					include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
					if ( !defined( 'API_Chat_put' ) )
						include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put_itr.php" ) ;
					if ( !defined( 'API_Chat_remove_itr' ) )
						include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove_itr.php" ) ;
					if ( !defined( 'API_Depts_get' ) )
						include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

					$lang = $CONF["lang"] ; $deptinfo = Array() ; $deptvars = Array() ; $prev_deptid = 1111111111 ; // start things off
					for ( $c = 0; $c < count( $expired_requests ); ++$c )
					{
						$request = $expired_requests[$c] ;
						if ( isset( $request["ces"] ) )
						{
							$ces = $request["ces"] ; $ip = $request["ip"] ; $deptid = $request["deptID"] ;
							$vis_token = isset( $request["md5_vis_"] ) ? $request["md5_vis_"] : $request["md5_vis"] ;
							$trans_file = "{$ces}.txt" ;

							if ( $prev_deptid != $deptid )
							{
								$prev_deptid = $deptid ;
								$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
								$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
								if ( $deptinfo["lang"] ) { $lang = $deptinfo["lang"] ; }
								include( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($lang, "ln").".php" ) ;
							}

							$query = "UPDATE p_footprints_u SET chatting = 0 WHERE md5_vis = '$vis_token'" ;
							database_mysql_query( $dbh, $query ) ;

							if ( is_file( "$CONF[CHAT_IO_DIR]/$trans_file" ) )
							{
								$query = "SELECT * FROM p_transcripts WHERE ces = '$ces' LIMIT 1" ;
								database_mysql_query( $dbh, $query ) ;
								$transcript = database_mysql_fetchrow( $dbh ) ;

								if ( !isset( $transcript["ces"] ) )
								{
									$ended = filemtime( "$CONF[CHAT_IO_DIR]/$trans_file" ) ;
									$string_disconnect = "<div class='cl'><disconnected><d6>".$LANG["CHAT_NOTIFY_DISCONNECT"]."</div>" ;
									UtilChat_AppendToChatfile( $trans_file, base64_encode( $string_disconnect ) ) ;

									$output = UtilChat_ExportChat( $trans_file ) ;
									if ( is_array( $output ) && isset( $output[1][0] ) )
									{
										$formatted = $output[1][0] ; $plain = $output[1][1] ;

										$fsize = strlen( $formatted ) ;
										$requestinfo = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;

										if ( $requestinfo["status"] )
										{
											$custom_string = "" ;
											$customs = explode( "-cus-", rawurldecode( $requestinfo["custom"] ) ) ;
											for ( $c3 = 0; $c3 < count( $customs ); ++$c3 )
											{
												$custom_var = $customs[$c3] ;
												if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
												{
													LIST( $cus_name, $cus_var ) = explode( "-_-", $custom_var ) ;
													if ( $cus_var ) { $custom_string .= $cus_name.": ".$cus_var."\r\n" ; }
												}
											}
											if ( Chat_put_itr_Transcript( $dbh, $ces, $requestinfo["status"], $requestinfo["created"], $ended, $requestinfo["deptID"], $requestinfo["opID"], $requestinfo["initiated"], $requestinfo["op2op"], 0, $fsize, $requestinfo["vname"], $requestinfo["vemail"], $requestinfo["ip"], $vis_token, $custom_string, $requestinfo["question"], $formatted, $plain, $deptinfo, $deptvars ) )
											{
												LIST( $ces ) = database_mysql_quote( $dbh, $requestinfo["ces"] ) ;
												$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
												database_mysql_query( $dbh, $query ) ;
											}
										}
									}
								}
								if ( is_file( "$CONF[CHAT_IO_DIR]/$trans_file" ) ) { @unlink( "$CONF[CHAT_IO_DIR]/$trans_file" ) ; }
							}
						}
					}
					$query = "DELETE FROM p_requests WHERE ( ( updated < $expired OR ( vupdated < $expired AND vupdated <> 615 ) ) OR ( status = 1 AND ( updated < $expired_active OR vupdated < $expired_active ) ) ) AND op2op = 0 AND md5_vis_ <> 'grc'" ;
					database_mysql_query( $dbh, $query ) ;
				}
			}

			$dir_files = glob( $CONF["CHAT_IO_DIR"].'/*.text', GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
					{
						$modtime = filemtime( $dir_files[$c] ) ;
						if ( $modtime < ( $now - (60*15) ) )
						{
							if ( $dir_files[$c] && is_file( $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
						}
					}
				}
			}
		} return true ;
	}

	FUNCTION Chat_remove_itr_ExpiredOp2OpRequests( &$dbh )
	{
		global $VARS_EXPIRED_OP2OP ;
		$expired_op2op = time() - $VARS_EXPIRED_OP2OP ;

		$query = "DELETE FROM p_requests WHERE updated < $expired_op2op AND vupdated < $expired_op2op AND op2op <> 0" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}
?>
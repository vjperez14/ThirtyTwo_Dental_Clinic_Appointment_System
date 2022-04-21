<?php
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$opid = isset( $_COOKIE["cO"] ) ? Util_Format_Sanatize( $_COOKIE["cO"], "n" ) : "" ;
	$ses = isset( $_COOKIE["cS"] ) ? Util_Format_Sanatize( $_COOKIE["cS"], "ln" ) : "" ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "a" ), "ln" ) ;

	if ( !$opid || !is_file( "$CONF[TYPE_IO_DIR]/$opid"."_ses_{$ses}.ses" ) )
		$json_data = "json_data = { \"status\": -1 };" ;
	else if ( $action === "fr" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;

		$flag = Util_Format_Sanatize( Util_Format_GetVar( "f" ), "n" ) ;
		$status = Util_Format_Sanatize( Util_Format_GetVar( "st" ), "n" ) ;
		$mapp = Util_Format_Sanatize( Util_Format_GetVar( "m" ), "n" ) ;
		$deptids = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "a" ) ;
		$total_processed = 0 ; $auto_offline_string = "" ;

		$query = "SELECT ses, signall, rating, ces, status FROM p_operators WHERE opID = '$opid' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;
		$opinfo = database_mysql_fetchrow( $dbh ) ;

		if ( isset( $opinfo["ses"] ) && ( $opinfo["ses"] == $ses ) )
		{
			$m = date( "m", $now ) ; $d = date( "j", $now ) ; $y = date( "Y", $now ) ; $hour_now = date( "G", $now ) ;
			$stat_start = mktime( 0, 0, 0, $m, $d, $y ) ; $stat_end = mktime( 23, 59, 59, $m, $d, $y ) ;

			if ( $flag && !$mapp )
			{
				$query = "SELECT SUM(rateit) AS rateit, SUM(ratings) AS ratings FROM p_rstats_ops WHERE opID = '$opid'" ;
				database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
				$overall = ( isset( $data["rateit"] ) && $data["rateit"] ) ? round( $data["ratings"]/$data["rateit"] ) : 0 ;

				$query = "SELECT SUM(taken) AS total FROM p_rstats_ops WHERE opID = '$opid'" ;
				database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
				$chats_overall = ( isset( $data["total"] ) ) ? $data["total"] : 0 ;

				$query = "SELECT SUM(taken) AS total FROM p_rstats_ops WHERE sdate >= $stat_start AND sdate <= $stat_end AND opID = '$opid'" ;
				database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
				$chats_today = ( isset( $data["total"] ) ) ? $data["total"] : 0 ;
			}
			else { $overall = 0 ; $chats_overall = $chats_today = 0 ; }

			$signal = $opinfo["signall"] ;
			if ( $signal )
			{
				//
			}
			else if ( is_array( $deptids ) && ( count( $deptids ) > 0 ) )
			{
				$auto_offline = ( isset( $VALS["AUTO_OFFLINE"] ) && $VALS["AUTO_OFFLINE"] ) ? unserialize( $VALS["AUTO_OFFLINE"] ) : Array() ;
				for ( $c = 0; $c < count( $deptids ); ++$c )
				{
					$deptid = Util_Format_Sanatize( $deptids[$c], "n" ) ;
					if ( isset( $auto_offline[$deptid] ) )
					{
						$query = "SELECT * FROM p_dept_ops WHERE opID = '$opid'" ;
						database_mysql_query( $dbh, $query ) ;
						if ( $dbh[ 'ok' ] )
						{
							$op_depts_status_hash = Array() ;
							while ( $data = database_mysql_fetchrow( $dbh ) )
							{
								$op_depts_status_hash[$data["deptID"]] = Array( $data["status"], $data["dept_offline"] ) ;
							}
							LIST( $offline_hour, $offline_min, $offline_duration, $offline_rewind ) = explode( ",", $auto_offline[$deptid] ) ;
							if ( $hour_now && ( $hour_now <= $offline_rewind ) ) { $offline_time_start = mktime( $offline_hour, $offline_min, 0, $m, $d-1, $y ) ; }
							else { $offline_time_start = mktime( $offline_hour, $offline_min, 0, $m, $d, $y ) ; }
							$offline_time_end = $offline_time_start + ( 60*60*$offline_duration ) ;
							if ( isset( $op_depts_status_hash[$deptid] ) )
							{
								if ( ( $now >= $offline_time_start ) && ( $now <= $offline_time_end ) )
								{
									++$total_processed ;
									$auto_offline_string .= "-$deptid-" ;
									if ( $op_depts_status_hash[$deptid][0] )
									{
										$query = "UPDATE p_dept_ops SET status = 0 WHERE opID = '$opid' AND deptID = '$deptid' AND status = 1" ;
										database_mysql_query( $dbh, $query ) ;
										Util_Format_CleanDeptOnline( $deptid, $opid ) ;
									}
								}
							}
						}
					}
					else if ( $status )
					{
						$query = "UPDATE p_dept_ops SET status = 1 WHERE opID = $opid AND deptID = $deptid AND status = 0 AND dept_offline = 0" ;
						database_mysql_query( $dbh, $query ) ;
						$nresults = database_mysql_nresults( $dbh ) ;

						$flag_file = "online_{$deptid}_{$opid}.info" ;
						if ( !is_file( "$CONF[CHAT_IO_DIR]/$flag_file" ) ) { touch( "$CONF[CHAT_IO_DIR]/$flag_file" ) ; }
					}
				} $signal = 2 ; if ( $total_processed != count( $deptids ) ) { $signal = 0 ; }
			}
			$restart_requesting = 0 ;
			if ( $status )
			{
				$query = "UPDATE p_operators SET status = 1 WHERE opID = $opid AND status = 0" ;
				database_mysql_query( $dbh, $query ) ; $updated = database_mysql_nresults( $dbh ) ;
				if ( $updated )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
					Ops_update_PutOpStatus( $dbh, $opid, 1, $mapp ) ;
					$restart_requesting = 1 ;
				}
			}

			// signall override
			if ( is_file( "$CONF[TYPE_IO_DIR]/$opid.locked" ) )
				$signal = 1 ;

			$json_data = "json_data = { \"status\": 1, \"rt_o\": \"$overall\", \"rt_r\": \"$opinfo[rating]\", \"ces\": \"$opinfo[ces]\", \"c_t\": $chats_today, \"c_o\": $chats_overall, \"signal\": $signal, \"rst\": \"$restart_requesting\", \"v\": \"$VERSION\", \"aof\": \"$auto_offline_string\" }; " ;
		}
		else if ( $opinfo["ses"] == "mapp_idle" )
			$json_data = "json_data = { \"status\": 0, \"rt_o\": \"\", \"rt_r\": \"\", \"ces\": \"\", \"signal\": 4 }; " ;
		else if ( $opinfo["ses"] != $ses )
			$json_data = "json_data = { \"status\": 0, \"rt_o\": \"\", \"rt_r\": \"\", \"ces\": \"\", \"signal\": 3 }; " ;
		else
			$json_data = "json_data = { \"status\": 0, \"rt_o\": \"\", \"rt_r\": \"\", \"ces\": \"\", \"signal\": 1 }; " ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
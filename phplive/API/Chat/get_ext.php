<?php
	if ( defined( 'API_Chat_get_ext' ) ) { return ; }
	define( 'API_Chat_get_ext', true ) ;

	FUNCTION Chat_get_ext_RequestsRangeHash( &$dbh,
								$stat_start,
								$stat_end,
								$operators,
								$opid )
	{
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		$ops_query = "" ;
		for ( $c = 0; $c < count( $operators ); ++$c )
		{
			$operator = $operators[$c] ;
			$ops_query .= " AND opID <> $operator[opID] " ;
		}
		if ( count( $operators ) && $ops_query )
		{
			$query = "DELETE FROM p_rstats_ops WHERE (opID <> 0 $ops_query)" ;
			database_mysql_query( $dbh, $query ) ;
		}

		LIST( $stat_start, $stat_end ) = database_mysql_quote( $dbh, $stat_start, $stat_end ) ;

		$output = Array() ;

		if ( !$opid )
		{
			$stats = Array() ;
			$query = "SELECT * FROM p_rstats_depts WHERE sdate >= $stat_start AND sdate <= $stat_end" ;
			database_mysql_query( $dbh, $query ) ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$stats[] = $data ;

			for ( $c = 0; $c < count( $stats ); ++$c )
			{
				$data = $stats[$c] ;
				$sdate = $data["sdate"] ;
				$deptid = $data["deptID"] ;

				if ( !isset( $output[$sdate] ) )
				{
					$output[$sdate] = Array() ;
					$output[$sdate]["depts"] = Array() ;
				}

				if ( !isset( $output[$sdate]["depts"][$deptid] ) )
				{
					$stat_start_day = mktime( 0, 0, 1, date( "n", $sdate ), date("j", $sdate), date( "Y", $sdate ) ) ;
					$stat_end_day = mktime( 23, 59, 59, date( "n", $sdate ), date("j", $sdate), date( "Y", $sdate ) ) ;

					$output[$sdate]["depts"][$deptid] = Array() ;
					$output[$sdate]["depts"][$deptid]["requests"] = 0 ;
					$output[$sdate]["depts"][$deptid]["taken"] = 0 ;
					$output[$sdate]["depts"][$deptid]["declined"] = 0 ;
					$output[$sdate]["depts"][$deptid]["transfer"] = 0 ;
					$output[$sdate]["depts"][$deptid]["transfer_a"] = 0 ;
					$output[$sdate]["depts"][$deptid]["message"] = Chat_ext_get_Total_Chats_Missed( $dbh, $deptid, $stat_start_day, $stat_end_day ) ;
					$output[$sdate]["depts"][$deptid]["initiated"] = 0 ;
					$output[$sdate]["depts"][$deptid]["rateit"] = 0 ;
					$output[$sdate]["depts"][$deptid]["ratings"] = 0 ;
				}

				$output[$sdate]["depts"][$deptid]["requests"] += $data["requests"] ;
				$output[$sdate]["depts"][$deptid]["taken"] += $data["taken"] ;
				$output[$sdate]["depts"][$deptid]["declined"] += $data["declined"] ;
				$output[$sdate]["depts"][$deptid]["transfer"] = $data["transfer"] ;
				$output[$sdate]["depts"][$deptid]["transfer_a"] = $data["transfer_a"] ;
				$output[$sdate]["depts"][$deptid]["initiated"] += $data["initiated"] ;
				$output[$sdate]["depts"][$deptid]["rateit"] += $data["rateit"] ;
				$output[$sdate]["depts"][$deptid]["ratings"] += $data["ratings"] ;
			}
		}

		$ops_string = "" ;
		if ( $opid )
		{
			LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;
			$ops_string = " AND opID = $opid" ;
		}

		$stats = Array() ;
		$query = "SELECT * FROM p_rstats_ops WHERE sdate >= $stat_start AND sdate <= $stat_end $ops_string" ;
		database_mysql_query( $dbh, $query ) ;
		while ( $data = database_mysql_fetchrow( $dbh ) )
			$stats[] = $data ;

		for ( $c = 0; $c < count( $stats ); ++$c )
		{
			$data = $stats[$c] ;
			$sdate = $data["sdate"] ;
			$opid = $data["opID"] ;

			if ( !isset( $output[$sdate] ) ) { $output[$sdate] = Array() ; }

			if ( !isset( $output[$sdate]["ops"] ) )
				$output[$sdate]["ops"] = Array() ;

			if ( !isset( $output[$sdate]["ops"][$opid] ) )
			{
				$output[$sdate]["ops"][$opid] = Array() ;
				$output[$sdate]["ops"][$opid]["requests"] = 0 ;
				$output[$sdate]["ops"][$opid]["taken"] = 0 ;
				$output[$sdate]["ops"][$opid]["declined"] = 0 ;
				$output[$sdate]["ops"][$opid]["transfer"] = 0 ;
				$output[$sdate]["ops"][$opid]["transfer_a"] = 0 ;
				$output[$sdate]["ops"][$opid]["message"] = 0 ;
				$output[$sdate]["ops"][$opid]["initiated"] = 0 ;
				$output[$sdate]["ops"][$opid]["rateit"] = 0 ;
				$output[$sdate]["ops"][$opid]["ratings"] = 0 ;
			}

			$output[$sdate]["ops"][$opid]["requests"] += $data["requests"] ;
			$output[$sdate]["ops"][$opid]["taken"] += $data["taken"] ;
			$output[$sdate]["ops"][$opid]["declined"] += $data["declined"] ;
			$output[$sdate]["ops"][$opid]["transfer"] = $data["transfer"] ;
			$output[$sdate]["ops"][$opid]["transfer_a"] = $data["transfer_a"] ;
			$output[$sdate]["ops"][$opid]["message"] += $data["message"] ;
			$output[$sdate]["ops"][$opid]["initiated"] += $data["initiated"] ;
			$output[$sdate]["ops"][$opid]["rateit"] += $data["rateit"] ;
			$output[$sdate]["ops"][$opid]["ratings"] += $data["ratings"] ;
		}

		return $output ;
	}

	FUNCTION Chat_get_ext_AcceptedDeptsHash( &$dbh,
								$deptid,
								$stat_start,
								$stat_end )
	{
		global $VARS_JS_REQUESTING ;
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		LIST( $deptid, $stat_start, $stat_end ) = database_mysql_quote( $dbh, $deptid, $stat_start, $stat_end ) ;

		if ( !$deptid )
			$query = "SELECT deptID, AVG(accepted) AS accepted, count(*) AS total FROM p_req_log WHERE created >= $stat_start AND created <= $stat_end AND accepted <> 0 AND transferred = 0 GROUP BY deptID" ;
		else
			$query = "SELECT deptID, AVG(accepted) AS accepted, count(*) AS total FROM p_req_log WHERE deptID = $deptid AND created >= $stat_start AND created <= $stat_end AND accepted <> 0 AND transferred = 0" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ; $stats = Array() ; $total = 0 ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$stats[] = $data ;

			for ( $c = 0; $c < count( $stats ); ++$c )
			{
				$data = $stats[$c] ;
				$deptid = $data["deptID"] ;

				$total_count = $data["total"] ;
				$accepted = floor( $data["accepted"] ) ;
				
				// subtract the network 1-$VARS_JS_REQUESTING seconds to
				// average it out due to 1-$VARS_JS_REQUESTING seconds network communication
				for ( $c2 = $VARS_JS_REQUESTING; $c2 > 0; --$c2 )
				{
					$network_buffer = $c2 * $total_count ;
					$diff = $accepted - $network_buffer ;

					if ( $diff > 0 )
					{
						$accepted = $accepted - round($network_buffer/2) ;
						break ;
					}
				}

				$output[$deptid] = $accepted ;
				$total += $accepted ;
			}
			$output[0] = ( count( $stats ) ) ? floor( $total/count( $stats ) ) : 0 ;
		}
		return $output ;
	}

	FUNCTION Chat_get_ext_AcceptedOpsHash( &$dbh,
								$opid,
								$stat_start,
								$stat_end )
	{
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		LIST( $opid, $stat_start, $stat_end ) = database_mysql_quote( $dbh, $opid, $stat_start, $stat_end ) ;

		if ( !$opid )
			$query = "SELECT opID, AVG(accepted_op) AS accepted FROM p_req_log WHERE created >= $stat_start AND created <= $stat_end AND accepted_op <> 0 AND transferred = 0 GROUP BY opID" ;
		else
			$query = "SELECT opID, AVG(accepted_op) AS accepted FROM p_req_log WHERE opID = $opid AND created >= $stat_start AND created <= $stat_end AND accepted_op <> 0 AND transferred = 0" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ; $stats = Array() ; $total = 0 ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$stats[] = $data ;

			for ( $c = 0; $c < count( $stats ); ++$c )
			{
				$data = $stats[$c] ;
				$opid = $data["opID"] ;
				$output[$opid] = floor( $data["accepted"] ) ;
				$total += $output[$opid] ;
			}
			$output[0] = ( count( $stats ) ) ? floor( $total/count( $stats ) ) : 0 ;
		}
		return $output ;
	}

	FUNCTION Chat_get_ext_TransDurDeptsHash( &$dbh,
								$deptid,
								$stat_start,
								$stat_end )
	{
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		LIST( $deptid, $stat_start, $stat_end ) = database_mysql_quote( $dbh, $deptid, $stat_start, $stat_end ) ;

		if ( !$deptid )
			$query = "SELECT deptID, AVG(duration) AS duration FROM p_req_log WHERE created >= $stat_start AND created <= $stat_end AND accepted <> 0 AND duration <> 0 AND transferred = 0 GROUP BY deptID" ;
		else
			$query = "SELECT deptID, AVG(duration) AS duration FROM p_req_log WHERE deptID = $deptid AND created >= $stat_start AND accepted <> 0 AND created <= $stat_end AND duration <> 0 AND transferred = 0" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ; $stats = Array() ; $total = 0 ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$stats[] = $data ;

			for ( $c = 0; $c < count( $stats ); ++$c )
			{
				$data = $stats[$c] ;
				$deptid = $data["deptID"] ;
				$output[$deptid] = floor( $data["duration"] ) ;
				$total += $output[$deptid] ;
			}
			$output[0] = ( count( $stats ) ) ? floor( $total/count( $stats ) ) : 0 ;
		}
		return $output ;
	}

	FUNCTION Chat_get_ext_TransDurOpsHash( &$dbh,
								$opid,
								$stat_start,
								$stat_end )
	{
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		LIST( $opid, $stat_start, $stat_end ) = database_mysql_quote( $dbh, $opid, $stat_start, $stat_end ) ;

		if ( !$opid )
			$query = "SELECT opID, AVG(duration) AS duration FROM p_req_log WHERE created >= $stat_start AND created <= $stat_end AND duration <> 0 AND transferred = 0 GROUP BY opID" ;
		else
			$query = "SELECT opID, AVG(duration) AS duration FROM p_req_log WHERE opID = $opid AND created >= $stat_start AND created <= $stat_end AND duration <> 0 AND transferred = 0" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ; $stats = Array() ; $total = 0 ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$stats[] = $data ;

			for ( $c = 0; $c < count( $stats ); ++$c )
			{
				$data = $stats[$c] ;
				$opid = $data["opID"] ;
				$output[$opid] = floor( $data["duration"] ) ;
				$total += $output[$opid] ;
			}
			$output[0] = ( count( $stats ) ) ? floor( $total/count( $stats ) ) : 0 ;
		}
		return $output ;
	}

	FUNCTION Chat_ext_get_VisTranscripts( &$dbh,
						$vis_token )
	{
		if ( $vis_token == "" )
			return false ;

		LIST( $vis_token ) = database_mysql_quote( $dbh, $vis_token ) ;

		$query = "SELECT * FROM p_transcripts WHERE md5_vis = '$vis_token' ORDER BY created DESC LIMIT 100" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
		}
		return $output ;
	}

	FUNCTION Chat_ext_get_RefinedTranscripts( &$dbh,
								$deptid,
								$opid,
								$tid,
								$s_as,
								$text,
								$year,
								$stat_start,
								$stat_end,
								$page,
								$limit )
	{
		if ( $limit == "" )
			return false ;

		LIST( $deptid, $opid, $tid, $text, $stat_start, $stat_end, $page, $limit ) = database_mysql_quote( $dbh, $deptid, $opid, $tid, $text, $stat_start, $stat_end, $page, $limit ) ;
		$start = ( $page * $limit ) ;

		$search_string = ( $year ) ? " AND created >= $stat_start AND created <= $stat_end " : "" ;
		if ( $s_as == "ces" ) { $search_string .= " AND ( ces = '$text' ) " ; }
		else if ( $s_as == "vid" ) { $search_string .= " AND ( md5_vis = '$text' ) " ; }
		else if ( preg_match( "/^cus_/i", $s_as ) ) { $search_string .= " AND ( custom LIKE '%$text%' ) " ; }
		else if ( $text ) { $search_string .= " AND ( plain LIKE '%$text%' ) " ; }

		if ( $tid ) { $search_string .= " AND ( tag = $tid ) " ; }
		if ( $deptid && $opid )
		{
			$query = "SELECT * FROM p_transcripts WHERE deptID = $deptid AND opID = $opid $search_string ORDER BY created DESC LIMIT $start, $limit" ;
			$query2 = "SELECT count(*) AS total FROM p_transcripts WHERE deptID = $deptid AND opID = $opid $search_string" ;
		}
		else if ( $deptid )
		{
			$query = "SELECT * FROM p_transcripts WHERE deptID = $deptid $search_string ORDER BY created DESC LIMIT $start, $limit" ;
			$query2 = "SELECT count(*) AS total FROM p_transcripts WHERE deptID = $deptid $search_string" ;
		}
		else if ( $opid )
		{
			$query = "SELECT * FROM p_transcripts WHERE opID = $opid $search_string ORDER BY created DESC LIMIT $start, $limit" ;
			$query2 = "SELECT count(*) AS total FROM p_transcripts WHERE opID = $opid $search_string" ;
		}
		else
		{
			$query = "SELECT * FROM p_transcripts WHERE created > 0 $search_string ORDER BY created DESC LIMIT $start, $limit" ;
			$query2 = "SELECT count(*) AS total FROM p_transcripts WHERE created > 0 $search_string" ;
		}
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
		}

		database_mysql_query( $dbh, $query2 ) ;
		$data = database_mysql_fetchrow( $dbh ) ;
		$output[] = $data["total"] ;
		return $output ;
	}

	FUNCTION Chat_ext_get_Transcript( &$dbh,
								$ces )
	{
		if ( $ces == "" )
			return false ;

		LIST( $ces ) = database_mysql_quote( $dbh, $ces ) ;

		$query = "SELECT * FROM p_transcripts WHERE ces = '$ces' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_TotalTranscript( &$dbh )
	{
		$query = "SELECT count(*) AS total FROM p_transcripts" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_OpDeptTrans( &$dbh,
								$opid,
								$s_as,
								$text,
								$year,
								$stat_start,
								$stat_end,
								$tid,
								$page,
								$limit )
	{
		if ( ( $opid == "" ) || ( $limit == "" ) )
			return false ;

		global $CONF ;
		if ( !defined( 'API_Depts_get' ) )
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

		LIST( $opid, $text, $stat_start, $stat_end, $tid, $page, $limit ) = database_mysql_quote( $dbh, $opid, $text, $stat_start, $stat_end, $tid, $page, $limit ) ;
		$start = ( $page * $limit ) ;

		$departments = Depts_get_OpDepts( $dbh, $opid ) ;
		$dept_string = " ( opID = $opid OR op2op = $opid " ;
		for ( $c = 0; $c < count( $departments ); ++$c )
		{
			if ( $departments[$c]["tshare"] )
				$dept_string .= " OR deptID = " . $departments[$c]["deptID"] ;
		}
		$dept_string .= " ) " ;

		$search_string = ( $year ) ? " AND created >= $stat_start AND created <= $stat_end " : "" ;
		if ( is_numeric( $tid ) && $tid ) { $search_string .= " AND ( tag = $tid ) " ; }
		else if ( $s_as == "ces" ) { $search_string .= " AND ( ces = '$text' ) " ; }
		else if ( $s_as == "vid" ) { $search_string .= " AND ( md5_vis = '$text' ) " ; }
		else if ( preg_match( "/^cus_/i", $s_as ) ) { $search_string .= " AND ( custom LIKE '%$text%' ) " ; }
		else if ( $s_as && $text ) { $search_string .= " AND ( plain LIKE '%$text%' ) " ; }
		$query = "SELECT * FROM p_transcripts WHERE $dept_string $search_string ORDER BY created DESC LIMIT $start, $limit" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
		}
		$query = "SELECT count(*) AS total FROM p_transcripts WHERE $dept_string $search_string" ;
		database_mysql_query( $dbh, $query ) ;
		$data = database_mysql_fetchrow( $dbh ) ;
		$output[] = $data["total"] ;

		return $output ;
	}

	FUNCTION Chat_ext_get_AllRequests( &$dbh,
					$deptid )
	{
		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$dept_string = "" ;
		if ( $deptid )
			$dept_string = " AND deptID = $deptid " ;

		// created > 0 flag is placeholder for the AND condition above
		$query = "SELECT * FROM p_requests WHERE created > 0 $dept_string ORDER BY created ASC" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_OpAllRequests( &$dbh,
					$deptid )
	{
		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$dept_string = "" ;
		if ( $deptid )
			$dept_string = " AND deptID = $deptid " ;

		// created > 0 flag is placeholder for the AND condition above
		$query = "SELECT * FROM p_requests WHERE created > 0 AND ( status = 1 OR status = 2 ) AND ( op2op = 0 OR ( op2op <> 0 AND status = 2 ) ) AND md5_vis_ <> 'grc' $dept_string ORDER BY created ASC" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_OpAllRequestsTotal( &$dbh,
					$theflag,
					$departments )
	{
		if ( !is_numeric( $theflag ) || !$theflag || !is_array( $departments ) || !count( $departments ) )
			return 0 ;

		$dept_string = "" ;
		if ( $theflag == 2 )
		{
			for ( $c = 0; $c < count( $departments ); ++$c )
			{
				$dept_string .= " deptID = ".$departments[$c]["deptID"]." OR " ;
			}
			if ( $dept_string )
			{
				if ( $dept_string ) { $dept_string = substr_replace( $dept_string, "", -3 ) ; }
				$dept_string = "AND ( $dept_string )" ;
			}
		}

		$query = "SELECT opID FROM p_operators WHERE login = 'phplivebot' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;
		$phplivebotinfo = database_mysql_fetchrow( $dbh ) ;
		$bot_query = ( isset( $phplivebotinfo["opID"] ) ) ? "opID <> $phplivebotinfo[opID] AND " : "" ;

		// created > 0 flag is placeholder for the AND condition above
		$query = "SELECT count(*) AS total FROM p_requests WHERE created <> 0 AND $bot_query ( status = 1 OR status = 2 ) AND ( op2op = 0 OR ( op2op <> 0 AND status = 2 ) ) AND md5_vis_ <> 'grc' $dept_string" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return 0 ;
	}

	FUNCTION Chat_ext_get_OpRequestLogTags( &$dbh,
					$opid,
					$stat_start,
					$stat_end )
	{
		if ( !$opid || !$stat_start || !$stat_end )
			return Array() ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT count(*) AS total, tag FROM p_req_log WHERE opID = $opid AND tag <> 0 AND created >= $stat_start AND created <= $stat_end GROUP BY tag" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return $output ;
	}

	FUNCTION Chat_ext_get_OpStatusLog( &$dbh,
								$opid,
								$stat_start,
								$stat_end )
	{
		if ( !$opid || !$stat_start || !$stat_end )
			return Array( Array(), null ) ;

		LIST( $opid, $stat_start, $stat_end ) = database_mysql_quote( $dbh, $opid, $stat_start, $stat_end ) ;

		$query = "SELECT * FROM p_opstatus_log WHERE opID = $opid AND created >= $stat_start AND created <= $stat_end ORDER BY created ASC" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;

			// fetch previous status
			$query = "SELECT * FROM p_opstatus_log WHERE opID = $opid AND created < $stat_start ORDER BY created DESC LIMIT 1" ;
			database_mysql_query( $dbh, $query ) ;
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $data["created"] ) )
				return Array( $output, $data ) ;
		}
		return Array( $output, null ) ;
	}

	FUNCTION Chat_ext_get_Chats_Missed( &$dbh,
								$deptid,
								$stat_start,
								$stat_end,
								$page,
								$limit )
	{
		if ( $limit == "" )
			return false ;

		LIST( $deptid, $page, $limit ) = database_mysql_quote( $dbh, $deptid, $page, $limit ) ;
		$start = ( $page * $limit ) ;

		// 1429689962 is the unixtime the new feature was added.  prior data has invalid "ended" value that is
		// not compatible with the new feature of "Missed Chats"
		if ( $deptid )
			$query = "SELECT * FROM p_req_log WHERE status = 0 AND deptID = $deptid AND ended = 0 AND created > 1429689962 AND op2op = 0 AND archive = 0 AND created >= $stat_start AND created <= $stat_end ORDER BY created DESC LIMIT $start, $limit" ;
		else
			$query = "SELECT * FROM p_req_log WHERE status = 0 AND ended = 0 AND created > 1429689962 AND op2op = 0 AND archive = 0 AND created >= $stat_start AND created <= $stat_end ORDER BY created DESC LIMIT $start, $limit" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
		}
		return $output ;
	}

	FUNCTION Chat_ext_get_Chats_MissedStatus( &$dbh,
								$deptid,
								$status,
								$stat_start,
								$stat_end )
	{
		if ( !is_numeric( $deptid ) || !is_numeric( $status ) || !is_numeric( $stat_start ) || !is_numeric( $stat_end ) )
			return false ;

		LIST( $deptid, $status ) = database_mysql_quote( $dbh, $deptid, $status ) ;

		$dept_string = ( $deptid ) ? " AND deptID = $deptid " : "" ;
		if ( $status == 10 )
			$query = "SELECT count(*) AS total FROM p_req_log WHERE status = 0 AND status_msg = 0 $dept_string AND archive = 0 AND created >= $stat_start AND created <= $stat_end AND initiated = 0" ;
		else if ( $status == 11 )
			$query = "SELECT count(*) AS total FROM p_req_log WHERE status = 0 AND initiated = 1 AND ended = 0 $dept_string AND archive = 0 AND created >= $stat_start AND created <= $stat_end" ;
		else
			$query = "SELECT count(*) AS total FROM p_req_log WHERE status_msg = $status $dept_string AND archive = 0 AND created >= $stat_start AND created <= $stat_end AND initiated = 0" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_Total_Chats_Missed( &$dbh,
								$deptid,
								$stat_start,
								$stat_end )
	{
		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		if ( $deptid )
			$query = "SELECT count(*) AS total FROM p_req_log WHERE status = 0 AND deptID = $deptid AND ended = 0 AND created > 1429689962 AND op2op = 0  AND created >= $stat_start AND created <= $stat_end AND initiated = 0" ;
		else
			$query = "SELECT count(*) AS total FROM p_req_log WHERE status = 0 AND ended = 0 AND created > 1429689962 AND op2op = 0 AND created >= $stat_start AND created <= $stat_end AND initiated = 0" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_RequestURLs( &$dbh,
								$stat_start,
								$stat_end )
	{
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		LIST( $stat_start, $stat_end ) = database_mysql_quote( $dbh, $stat_start, $stat_end ) ;

		$query = "SELECT onpage, count(*) AS total FROM p_req_log WHERE created >= $stat_start AND created <= $stat_end AND op2op = 0 AND initiated = 0 AND archive = 0 GROUP BY onpage ORDER BY total DESC" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$output = Array() ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;

			return $output ;
		}
		return false ;
	}

	FUNCTION Chat_ext_get_RequestTimeline( &$dbh,
								$deptid,
								$opid,
								$stat_start,
								$stat_end )
	{
		if ( ( $stat_start == "" ) || ( $stat_end == "" ) )
			return false ;

		LIST( $deptid, $opid, $stat_start, $stat_end ) = database_mysql_quote( $dbh, $deptid, $opid, $stat_start, $stat_end ) ;

		$dept_string = ( $deptid ) ? "AND deptID = $deptid" : "" ;
		$op_string = ( $opid ) ? "AND opID = $opid" : "" ;

		$query = "SELECT created, SUM(status) AS status FROM p_rstats_log WHERE created >= $stat_start AND created <= $stat_end $op_string $dept_string GROUP BY ces ORDER BY created ASC" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$output = Array() ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return false ;
	}
?>
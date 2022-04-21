<?php
	if ( defined( 'API_Chat_get' ) ) { return ; }
	define( 'API_Chat_get', true ) ;

	FUNCTION Chat_get_RequestHistCesInfo( &$dbh,
					$ces )
	{
		if ( $ces == "" )
			return false ;

		LIST( $ces ) = database_mysql_quote( $dbh, $ces ) ;

		$query = "SELECT * FROM p_req_log WHERE ces = '$ces' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		}
		return false ;
	}

	FUNCTION Chat_get_OpTotalRequests( &$dbh,
						$opid )
	{
		if ( $opid == "" )
			return false ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT count(*) AS total FROM p_requests WHERE ( opID = $opid OR op2op = $opid OR opID = 1111111111 ) AND ( status = 1 OR status = 2 )" ;
		database_mysql_query( $dbh, $query ) ;

		$requests = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return 0 ;
	}

	FUNCTION Chat_get_IPTotalRequests( &$dbh,
						$value,
						$table )
	{
		if ( ( $value == "" ) || ( $table == "" ) )
			return false ;

		LIST( $value ) = database_mysql_quote( $dbh, $value ) ;

		if ( $table == "req_log" )
			$query = "SELECT count(*) AS total FROM p_req_log WHERE md5_vis = '$value'" ;
		else if ( $table == "requests" )
			$query = "SELECT count(*) AS total FROM p_requests WHERE ip = '$value'" ;
		else { return false ; }
		database_mysql_query( $dbh, $query ) ;

		$requests = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return 0 ;
	}

	FUNCTION Chat_get_TotalIPTranscripts( &$dbh,
								$vis_token )
	{
		if ( $vis_token == "" )
			return false ;

		LIST( $vis_token ) = database_mysql_quote( $dbh, $vis_token ) ;

		$query = "SELECT count(*) AS total FROM p_transcripts WHERE md5_vis = '$vis_token'" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
				return $data["total"] ;
		}
		return 0 ;
	}

	FUNCTION Chat_get_OpDeclined( &$dbh,
					$opid,
					$max = 24 )
	{
		if ( $opid == "" )
			return false ;

		$stat_begin = time() - (60*60*24) ;
		LIST( $opid, $max ) = database_mysql_quote( $dbh, $opid, $stat_begin ) ;

		$query = "SELECT p_req_log.* FROM p_req_log INNER JOIN p_rstats_log ON p_req_log.ces = p_rstats_log.ces AND (p_rstats_log.opID = '$opid' AND p_rstats_log.status = 0 AND p_rstats_log.created > $stat_begin ) ORDER BY p_rstats_log.created DESC" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
		}
		return $output ;
	}
?>
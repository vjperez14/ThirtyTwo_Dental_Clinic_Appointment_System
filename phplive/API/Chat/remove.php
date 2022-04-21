<?php
	if ( defined( 'API_Chat_remove' ) ) { return ; }
	define( 'API_Chat_remove', true ) ;

	FUNCTION Chat_remove_Request( &$dbh,
						$requestid )
	{
		if ( !is_numeric( $requestid ) || !$requestid )
			return false ;

		LIST( $requestid ) = database_mysql_quote( $dbh, $requestid ) ;

		$query = "DELETE FROM p_requests WHERE requestID = $requestid" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}

	FUNCTION Chat_remove_Transcript( &$dbh,
						$ces )
	{
		if ( $ces == "" )
			return false ;

		LIST( $ces ) = database_mysql_quote( $dbh, $ces ) ;

		$query = "DELETE FROM p_transcripts WHERE ces = '$ces'" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
		{
			$query = "DELETE FROM p_req_log WHERE ces = '$ces'" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DELETE FROM p_notes WHERE ces = '$ces'" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DELETE FROM p_rstats_log WHERE ces = '$ces'" ;
			database_mysql_query( $dbh, $query ) ;
			return true ;
		}
		else
			return false ;
	}

	FUNCTION Chat_remove_ExpiredTranscript( &$dbh,
								$deptid,
								$texpire )
	{
		if ( !is_numeric( $deptid ) || !$deptid || !is_numeric( $texpire ) || !$texpire )
			return false ;

		LIST( $deptid, $texpire ) = database_mysql_quote( $dbh, $deptid, $texpire ) ;
		$expired = time() - (60*$texpire) ;

		$query = "DELETE FROM p_transcripts WHERE deptID = $deptid AND created < $expired" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_req_log WHERE deptID = $deptid AND created < $expired" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_notes WHERE deptID = $deptid AND created < $expired" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_rstats_log WHERE deptID = $deptid AND created < $expired" ;
		database_mysql_query( $dbh, $query ) ;
		// p_rstats_ops and p_rstats_depts stored data by sdate (day). data is cumulative and not individual
		return true ;
	}

	FUNCTION Chat_remove_ResetReports( &$dbh )
	{
		$query = "TRUNCATE TABLE p_rstats_depts" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "TRUNCATE TABLE p_rstats_ops" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "TRUNCATE TABLE p_rstats_log" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_req_log WHERE status = 0" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "UPDATE p_req_log SET accepted = 0, accepted_op = 0, duration = 0, status_msg = 0, archive = 1 WHERE archive = 0" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}
?>
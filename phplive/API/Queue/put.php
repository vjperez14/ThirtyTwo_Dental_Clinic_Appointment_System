<?php
	if ( defined( 'API_Queue_put' ) ) { return ; }
	define( 'API_Queue_put', true ) ;

	FUNCTION Queue_put_Queue( &$dbh,
					$deptid,
					$embed,
					$ces,
					$vis_token,
					$rstring )
	{
		if ( ( $deptid == "" ) || ( $ces == "" ) || ( $vis_token == "" ) )
			return false ;

		$now = time() ;
		LIST( $deptid, $embed, $ces, $vis_token, $rstring ) = database_mysql_quote( $dbh, $deptid, $embed, $ces, $vis_token, $rstring ) ;

		$query = "INSERT INTO p_queue VALUES ( NULL, $now, $now, $deptid, $embed, '$ces', '$vis_token', '$rstring' )" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$sdate = mktime( 0, 0, 1, date("m"), date("j"), date("Y") ) ;

			$query = "INSERT INTO p_queue_log VALUES( NULL, $now, 0, $sdate, 0, $deptid, '$ces' )" ;
			database_mysql_query( $dbh, $query ) ;
			return true ;
		}
		return false ;
	}
?>
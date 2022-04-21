<?php
	if ( defined( 'API_Notes_put' ) ) { return ; }
	define( 'API_Notes_put', true ) ;

	FUNCTION Notes_put_Note( &$dbh,
					$opid,
					$deptid,
					$isnote,
					$ces,
					$message )
	{
		if ( ( $ces == "" ) || ( $message == "" ) || !is_numeric( $isnote ) )
			return false ;

		$now = time() ;
		LIST( $opid, $deptid, $isnote, $ces, $message ) = database_mysql_quote( $dbh, $opid, $deptid, $isnote, $ces, $message ) ;

		$query = "INSERT INTO p_notes VALUES ( NULL, $now, $opid, $deptid, $isnote, '$ces', '$message' )" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$id = database_mysql_insertid( $dbh ) ;
			return $id ;
		}
		return false ;
	}
?>
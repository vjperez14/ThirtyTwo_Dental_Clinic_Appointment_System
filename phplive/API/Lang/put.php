<?php
	if ( defined( 'API_Lang_put' ) ) { return ; }
	define( 'API_Lang_put', true ) ;

	FUNCTION Lang_put_Lang( &$dbh,
					$deptid,
					$value )
	{
		if ( !is_numeric( $deptid ) || ( $value == "" ) )
			return false ;

		LIST( $deptid, $value ) = database_mysql_quote( $dbh, $deptid, $value ) ;

		$query = "REPLACE INTO p_lang_packs VALUES ( $deptid, '$value' )" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}
?>
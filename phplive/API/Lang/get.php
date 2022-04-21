<?php
	if ( defined( 'API_Lang_get' ) ) { return ; }
	define( 'API_Lang_get', true ) ;

	FUNCTION Lang_get_Lang( &$dbh,
						$deptid )
	{
		if ( !is_numeric( $deptid ) )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$query = "SELECT * FROM p_lang_packs WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		} return false ;
	}

	FUNCTION Lang_get_AllLangs( &$dbh )
	{
		$query = "SELECT * FROM p_lang_packs" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
			{
				$deptid = $data["deptID"] ;
				$output[$deptid] = ( $data["lang_vars"] ) ? unserialize( $data["lang_vars"] ) : Array() ;
			}
		} return $output ;
	}
?>
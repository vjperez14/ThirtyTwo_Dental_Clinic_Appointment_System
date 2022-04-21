<?php
	if ( defined( 'API_Lang_remove' ) ) { return ; }
	define( 'API_Lang_remove', true ) ;

	FUNCTION Lang_remove_Lang( &$dbh,
						$deptid )
	{
		if ( !is_numeric( $deptid ) )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$query = "DELETE FROM p_lang_packs WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}
?>
<?php
	if ( defined( 'API_Lang_update' ) ) { return ; }
	define( 'API_Lang_update', true ) ;

	FUNCTION Lang_update_LangValue( &$dbh,
					  $deptid,
					  $value )
	{
		if ( ( $deptid == "" ) || ( $value == "" ) )
			return false ;
		
		LIST( $deptid, $value ) = database_mysql_quote( $dbh, $deptid, $value ) ;

		$query = "UPDATE p_lang_packs SET lang_vars = '$value' WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}
?>
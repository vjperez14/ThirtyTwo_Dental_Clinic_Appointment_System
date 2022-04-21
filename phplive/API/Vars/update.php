<?php
	if ( defined( 'API_Vars_update' ) ) { return ; }
	define( 'API_Vars_update', true ) ;

	FUNCTION Vars_update_Var( &$dbh,
					$tbl_name,
					$value )
	{
		if ( $tbl_name == "" )
			return false ;
		
		LIST( $tbl_name, $value ) = database_mysql_quote( $dbh, $tbl_name, $value ) ;

		$query = "SELECT * FROM p_vars LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;
		$total = database_mysql_nresults( $dbh ) ;

		if ( !$total )
		{
			$char_set = 'a:1:{i:0;s:5:"UTF-8";}' ;
			$query = "INSERT INTO p_vars VALUES(NULL, 0, 1, 0, 0, 0, '$char_set', 0)" ;
			database_mysql_query( $dbh, $query ) ;
		}

		$query = "UPDATE p_vars SET $tbl_name = '$value' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}
?>

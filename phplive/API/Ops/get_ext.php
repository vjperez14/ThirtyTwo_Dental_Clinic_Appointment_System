<?php
	if ( defined( 'API_Ops_get_ext' ) ) { return ; }
	define( 'API_Ops_get_ext', true ) ;

	FUNCTION Ops_get_ext_OpInfoByLogin( &$dbh,
					$login )
	{
		if ( $login == "" ) { return false ; }

		LIST( $login ) = database_mysql_quote( $dbh, $login ) ;

		$query = "SELECT * FROM p_operators WHERE login = '$login' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		}
		return false ;
	}

	FUNCTION Ops_get_ext_OpdDeptStats( &$dbh )
	{
		$query = "SELECT count(*) AS total FROM p_departments" ;
		database_mysql_query( $dbh, $query ) ;
		$data = database_mysql_fetchrow( $dbh ) ;
		$total_depts = isset( $data["total"] ) ? $data["total"] : 0 ;

		$query = "SELECT count(*) AS total FROM p_operators" ;
		database_mysql_query( $dbh, $query ) ;
		$data = database_mysql_fetchrow( $dbh ) ;
		$total_ops = isset( $data["total"] ) ? $data["total"] : 0 ;

		$query = "SELECT count(*) AS total FROM p_dept_ops" ;
		database_mysql_query( $dbh, $query ) ;
		$data = database_mysql_fetchrow( $dbh ) ;
		$total_assigned = isset( $data["total"] ) ? $data["total"] : 0 ;

		$output = Array() ;
		$output["total_depts"] = $total_depts ;
		$output["total_ops"] = $total_ops ;
		$output["total_assigned"] = $total_assigned ;

		return $output ;
	} $ERROR_OPS = "Max allowed operators have been reached." ; $ERROR_OPS_FREE = "Max allowed operators have been reached for the free version." ;
?>
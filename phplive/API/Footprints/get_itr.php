<?php
	if ( defined( 'API_Footprints_get_itr' ) ) { return ; }
	define( 'API_Footprints_get_itr', true ) ;

	FUNCTION Footprints_get_itr_TotalFootprints_U( &$dbh )
	{
		// WHERE os <> 5 AND footprints >= 2
		$query = "SELECT count(*) AS total FROM p_footprints_u" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return 0 ;
	}

	FUNCTION Footprints_get_itr_IPFootprints( &$dbh,
					$vis_token,
					$limit )
	{
		if ( ( $vis_token == "" ) || ( $limit == "" ) )
			return false ;

		LIST( $vis_token, $limit ) = database_mysql_quote( $dbh, $vis_token, $limit ) ;

		$query = "SELECT count(*) AS total, md5_page, onpage, title FROM p_footprints WHERE md5_vis = '$vis_token' GROUP BY md5_page ORDER BY total DESC LIMIT $limit" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$output = Array() ;
			while( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return false ;
	}
?>
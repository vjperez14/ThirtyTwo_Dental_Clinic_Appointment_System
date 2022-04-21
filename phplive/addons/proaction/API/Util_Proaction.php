<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	if ( defined( 'API_Util_Proaction' ) ) { return ; }	
	define( 'API_Util_Proaction', true ) ;

	FUNCTION Util_Proaction_SaveClickStatus( &$dbh,
					  $proactionid,
					  $status )
	{
		if ( ( $proactionid == "" ) || !is_numeric( $status ) )
			return false ;
	
		LIST( $proactionid ) = database_mysql_quote( $dbh, $proactionid ) ;
		$sdate = mktime( 0, 0, 1, date("m"), date("j"), date("Y") ) ;

		$query = "INSERT INTO p_proaction_c VALUES ('$proactionid', $sdate, 1, 0, 0) ON DUPLICATE KEY UPDATE views = views+1" ;
		if ( $status == 1 )
			$query = "UPDATE p_proaction_c SET taken = taken+1 WHERE proactionID = '$proactionid' AND sdate = $sdate" ;
		else if ( $status == 0 )
			$query = "UPDATE p_proaction_c SET declined = declined+1 WHERE proactionID = '$proactionid' AND sdate = $sdate" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	FUNCTION Util_Proaction_GetClickStats( &$dbh,
					  $proactionid )
	{
		if ( $proactionid == "" )
			return false ;
	
		LIST( $proactionid ) = database_mysql_quote( $dbh, $proactionid ) ;

		$query = "SELECT SUM(views) as total_views, SUM(taken) as total_accepted, SUM(declined) as total_declined FROM p_proaction_c WHERE proactionID = $proactionid" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $data["total_views"] ) )
				return $data ;
		}
		return Array( "total_views"=>0, "total_accepted"=>0, "total_declined"=>0 ) ;
	}

	FUNCTION Util_Proaction_DeleteStats( &$dbh,
					  $proactionid )
	{
		if ( $proactionid == "" )
			return false ;
	
		LIST( $proactionid ) = database_mysql_quote( $dbh, $proactionid ) ;

		$query = "DELETE FROM p_proaction_c WHERE proactionID = '$proactionid'" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}
?>
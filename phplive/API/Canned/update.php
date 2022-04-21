<?php
	if ( defined( 'API_Canned_update' ) ) { return ; }
	define( 'API_Canned_update', true ) ;

	FUNCTION Canned_update_CanValue( &$dbh,
					  $canid,
					  $tbl_name,
					  $value )
	{
		if ( !is_numeric( $canid ) || !$canid || ( $tbl_name == "" ) )
			return false ;
		
		LIST( $canid, $tbl_name, $value ) = database_mysql_quote( $dbh, $canid, $tbl_name, $value ) ;

		$query = "UPDATE p_canned SET $tbl_name = '$value' WHERE canID = $canid" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	FUNCTION Canned_update_ResetCat( &$dbh )
	{
		$query = "UPDATE p_canned SET catID = -1, cats_extra = ''" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	FUNCTION Canned_update_CanOwner( &$dbh,
					  $canid,
					  $opid_prev,
					  $opid_new )
	{
		if ( !is_numeric( $canid ) || !$canid || !$opid_prev || !$opid_new )
			return false ;
		
		LIST( $canid, $opid_prev, $opid_new ) = database_mysql_quote( $dbh, $canid, $opid_prev, $opid_new ) ;

		$query = "UPDATE p_canned SET opID = $opid_new, catID = -1, cats_extra = '' WHERE canID = $canid AND opID = $opid_prev" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}
?>
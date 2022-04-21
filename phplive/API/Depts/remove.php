<?php
	if ( defined( 'API_Depts_remove' ) ) { return ; }
	define( 'API_Depts_remove', true ) ;

	FUNCTION Depts_remove_Dept( &$dbh,
						$deptid )
	{
		global $CONF ; global $VALS ;
		if ( !$deptid || !is_numeric( $deptid ) )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$query = "DELETE FROM p_canned WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_dept_ops WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_rstats_depts WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_rstats_log WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_req_log WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_transcripts WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_notes WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_messages WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_departments WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_dept_vars WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_lang_packs WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;

		$query = "UPDATE p_dept_groups SET deptids = REPLACE( deptids, ',$deptid,', '' )" ;
		database_mysql_query( $dbh, $query ) ;
		$query = "DELETE FROM p_dept_groups WHERE deptids = ''" ;
		database_mysql_query( $dbh, $query ) ;

		Util_Format_CleanDeptOnline( $deptid, "" ) ;
		Util_Format_CleanIcons( $deptid ) ;

		Depts_remove_AddonRefs( $deptid ) ;

		return true ;
	}

	FUNCTION Depts_remove_DeptGroup( &$dbh,
						$groupid )
	{
		global $CONF ; global $VALS ;
		if ( !$groupid || !is_numeric( $groupid ) )
			return false ;

		LIST( $groupid ) = database_mysql_quote( $dbh, $groupid ) ;

		$query = "DELETE FROM p_dept_groups WHERE groupID = $groupid" ;
		database_mysql_query( $dbh, $query ) ;

		Depts_remove_AddonRefs( $groupid ) ;

		return true ;
	}

	FUNCTION Depts_remove_AddonRefs( $deptid )
	{
		global $CONF ; global $VALS ;
		if ( !$deptid || !is_numeric( $deptid ) )
			return false ;

		include_once( "$CONF[DOCUMENT_ROOT]/addons/API/Util_Addons.php" ) ;

		$auto_connect_array = ( isset( $VALS["auto_connect"] ) && $VALS["auto_connect"] ) ? unserialize( $VALS["auto_connect"] ) : Array() ;
		if ( isset( $auto_connect_array[$deptid] ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
			unset( $auto_connect_array[$deptid] ) ;
			Util_Vals_WriteToFile( "auto_connect", serialize( $auto_connect_array ) ) ;
		}

		if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
		$code_maps = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["code_maps"] ) && $VALS_ADDONS["code_maps"] ) ? unserialize( base64_decode( $VALS_ADDONS["code_maps"] ) ) : Array() ;
		$code_maps_updated = 0 ;
		foreach ( $code_maps as $mapid => $data )
		{
			LIST( $thisdeptid, $map_string ) = explode( ",", $data ) ;
			if ( $thisdeptid == $deptid )
			{
				$code_maps_updated = 1 ;
				unset( $code_maps[$mapid] ) ;
			}
		}
		if ( $code_maps_updated )
		{
			Util_Addons_WriteToFile( "code_maps", base64_encode( serialize( $code_maps ) ) ) ;
		}
	}
?>
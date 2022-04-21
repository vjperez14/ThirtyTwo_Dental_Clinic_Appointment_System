<?php
	if ( defined( 'API_Ops_update' ) ) { return ; }
	define( 'API_Ops_update', true ) ;

	function Ops_update_OpValue( &$dbh,
					  $opid,
					  $tbl_name,
					  $value )
	{
		if ( !is_numeric( $opid ) || !$opid || ( $tbl_name == "" ) )
			return false ;
		
		LIST( $opid, $tbl_name, $value ) = database_mysql_quote( $dbh, $opid, $tbl_name, $value ) ;

		$query = "UPDATE p_operators SET $tbl_name = '$value' WHERE opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_OpValues( &$dbh,
					  $opid,
					  $tbl_name,
					  $value,
					  $tbl_name2,
					  $value2 )
	{
		if ( !is_numeric( $opid ) || !$opid || ( $tbl_name == "" ) || ( $tbl_name2 == "" ) )
			return false ;
		
		LIST( $opid, $tbl_name, $value, $tbl_name2, $value2 ) = database_mysql_quote( $dbh, $opid, $tbl_name, $value, $tbl_name2, $value2 ) ;

		$query = "UPDATE p_operators SET $tbl_name = '$value', $tbl_name2 = '$value2' WHERE opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_OpVarValue( &$dbh,
					  $opid,
					  $tbl_name,
					  $value )
	{
		if ( !is_numeric( $opid ) || !$opid || ( $tbl_name == "" ) )
			return false ;
		
		LIST( $opid, $tbl_name, $value ) = database_mysql_quote( $dbh, $opid, $tbl_name, $value ) ;

		$query = "UPDATE p_op_vars SET $tbl_name = '$value' WHERE opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;
		
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_OpDeptVisible( &$dbh,
					  $deptid,
					  $visible )
	{
		if ( !is_numeric( $deptid ) || !$deptid || ( $visible == "" ) )
			return false ;
		
		LIST( $deptid, $visible ) = database_mysql_quote( $dbh, $deptid, $visible ) ;

		$query = "UPDATE p_dept_ops SET visible = '$visible' WHERE deptID = '$deptid'" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_OpDeptDisplay( &$dbh,
						$deptid,
						$opid,
						$display )
	{
		if ( !is_numeric( $deptid ) || !$deptid || !is_numeric( $opid ) || !$opid || !is_numeric( $display ) )
			return false ;
		
		LIST( $deptid, $opid, $display ) = database_mysql_quote( $dbh, $deptid, $opid, $display ) ;

		$query = "UPDATE p_dept_ops SET display = '$display' WHERE deptID = $deptid AND opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_PutOpStatus( &$dbh,
						$opid,
						$status,
						$mapp )
	{
		if ( !is_numeric( $opid ) || !$opid || !is_numeric( $status ) )
			return false ;
		global $CONF ;

		if ( !$status ) { $mapp = 0 ; }
		else if ( $mapp ) { $mapp = 1 ; }
		LIST( $opid, $status, $mapp ) = database_mysql_quote( $dbh, $opid, $status, $mapp ) ;

		$now = time() ;
		$query = "INSERT IGNORE INTO p_opstatus_log VALUES( $now, $opid, $status, $mapp )" ;
		database_mysql_query( $dbh, $query ) ;

		$dept_offline_string = ( $status ) ? "AND dept_offline = 0" : "" ;

		$query = "UPDATE p_dept_ops SET status = $status $dept_offline_string WHERE opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;
		return true ;
	}

	function Ops_update_OpResetOffStatus( &$dbh,
					  $opid )
	{
		if ( !is_numeric( $opid ) || !$opid )
			return false ;
		
		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "UPDATE p_dept_ops SET dept_offline = 0 WHERE opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_OpDeptOffStatus( &$dbh,
					  $opid,
					  $deptid,
					  $status )
	{
		if ( !is_numeric( $opid ) || !$opid || !is_numeric( $deptid ) || !$deptid || !is_numeric( $status ) )
			return false ;
		
		LIST( $opid, $deptid, $status ) = database_mysql_quote( $dbh, $opid, $deptid, $status ) ;

		if ( $status )
		{
			$query = "UPDATE p_operators SET status = 1 WHERE opID = $opid AND status = 0" ;
			database_mysql_query( $dbh, $query ) ;

			// check to see if any dept_offline is set to only go online for that department because
			// this is department status specific
			$query = "SELECT COUNT(*) AS total FROM p_dept_ops WHERE opID = $opid AND deptID <> $deptid AND dept_offline = 1" ;
			database_mysql_query( $dbh, $query ) ;
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( !$data["total"] )
			{
				$query = "UPDATE p_dept_ops SET dept_offline = 1 WHERE opID = $opid AND deptID <> $deptid AND status = 0" ;
				database_mysql_query( $dbh, $query ) ;

				$query = "UPDATE p_dept_ops SET status = 1, dept_offline = 0 WHERE opID = $opid AND ( dept_offline = 0 OR deptID = $deptid )" ;
				database_mysql_query( $dbh, $query ) ;
			}
			else
			{
				$query = "UPDATE p_dept_ops SET status = 1, dept_offline = 0 WHERE opID = $opid AND deptID = $deptid" ;
				database_mysql_query( $dbh, $query ) ;
			}
		}
		else
		{
			$query = "UPDATE p_dept_ops SET status = 0, dept_offline = 1 WHERE opID = $opid AND deptID = $deptid" ;
			database_mysql_query( $dbh, $query ) ;

			// if there are no op departments online, set main status to offline
			$query = "SELECT COUNT(*) AS total FROM p_dept_ops WHERE opID = $opid AND status = 1" ;
			database_mysql_query( $dbh, $query ) ;
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( !$data["total"] )
			{
				$query = "UPDATE p_operators SET status = 0 WHERE opID = $opid AND status = 1" ;
				database_mysql_query( $dbh, $query ) ;
			}
		}

		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	function Ops_update_AllFileUploadSettings( &$dbh,
						$vupload_val )
	{
		LIST( $vupload_val ) = database_mysql_quote( $dbh, $vupload_val ) ;

		$query = "UPDATE p_operators SET upload = '$vupload_val' WHERE upload <> '$vupload_val'" ;
		database_mysql_query( $dbh, $query ) ;
		return true ;
	}
?>
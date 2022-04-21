<?php
	if ( defined( 'API_Ops_put_itr' ) ) { return ; }
	define( 'API_Ops_put_itr', true ) ;

	FUNCTION Ops_put_itr_OpReqStat( &$dbh,
					$deptid,
					$opid,
					$tbl_name,
					$incro,
					$skip_dept = 0 )
	{
		if ( !is_numeric( $deptid ) || !is_numeric( $opid ) || !is_numeric( $incro ) )
			return false ;

		LIST( $deptid, $opid, $tbl_name, $incro ) = database_mysql_quote( $dbh, $deptid, $opid, $tbl_name, $incro ) ;
		$sdate = mktime( 0, 0, 1, date("m"), date("j"), date("Y") ) ;

		$vars = Array() ;
		$vars["requests"] = $vars["taken"] = $vars["declined"] = $vars["transfer"] = $vars["transfer_a"] = $vars["message"] = $vars["initiated"] = $vars["rateit"] = $vars["ratings"] = 0 ;
		$vars[$tbl_name] += $incro ;

		// only time it would skip dept logging is first initial routing when no opID is set because
		// logging already done via chat_actions_create.php
		if ( $deptid && !$skip_dept ){ $query = "INSERT INTO p_rstats_depts VALUES ( $sdate, $deptid, $vars[requests], $vars[taken], $vars[declined], $vars[transfer], $vars[transfer_a], $vars[message], $vars[initiated], $vars[rateit], $vars[ratings] ) ON DUPLICATE KEY UPDATE $tbl_name = $tbl_name + $incro" ; database_mysql_query( $dbh, $query ) ; }
		if ( $opid ){ $query = "INSERT INTO p_rstats_ops VALUES ( $sdate, $opid, $vars[requests], $vars[taken], $vars[declined], $vars[transfer], $vars[transfer_a], $vars[message], $vars[initiated], $vars[rateit], $vars[ratings] ) ON DUPLICATE KEY UPDATE $tbl_name = $tbl_name + $incro" ; database_mysql_query( $dbh, $query ) ; }

		return true ;
	}
?>
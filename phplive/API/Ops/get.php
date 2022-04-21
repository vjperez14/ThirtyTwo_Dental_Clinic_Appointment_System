<?php
	if ( defined( 'API_Ops_get' ) ) { return ; }
	define( 'API_Ops_get', true ) ;

	function Ops_get_AllOps( &$dbh, $online_flag = 0 )
	{
		$order_by = ( $online_flag ) ? "status DESC, name ASC" : "name ASC" ;
		$query = "SELECT * FROM p_operators ORDER BY $order_by" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$output = Array() ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return false ;
	}

	function Ops_get_TotalOps( &$dbh )
	{
		$query = "SELECT count(*) AS total FROM p_operators WHERE login <> 'phplivebot'" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return false ;
	}

	function Ops_get_TotalOpsAssigned( &$dbh )
	{
		$query = "SELECT count(*) AS total FROM p_dept_ops" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data["total"] ;
		}
		return 0 ;
	}

	function Ops_get_IsOpInDept( &$dbh,
					$opid,
					$deptid )
	{
		if ( !$opid || !$deptid )
			return false ;

		LIST( $opid, $deptid ) = database_mysql_quote( $dbh, $opid, $deptid ) ;

		$query = "SELECT * FROM p_dept_ops WHERE deptID = $deptid AND opID = $opid LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			if ( database_mysql_nresults( $dbh ) ) { return true ; }
		}
		return false ;
	}

	function Ops_get_IsOpOnline( &$dbh,
					$opid )
	{
		if ( !is_numeric( $opid ) || !$opid )
			return false ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT * FROM p_dept_ops WHERE opID = $opid AND status = 1 LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			if ( database_mysql_nresults( $dbh ) ) { return true ; }
		}
		return false ;
	}

	function Ops_get_OpInfoByID( &$dbh,
					$opid )
	{
		if ( !is_numeric( $opid ) || !$opid )
			return false ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT * FROM p_operators WHERE opID = $opid LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		}
		return false ;
	}

	function Ops_get_OpInfoByToken( &$dbh,
					$token )
	{
		if ( $token == "" )
			return false ;

		LIST( $token ) = database_mysql_quote( $dbh, $token ) ;

		$query = "SELECT * FROM p_operators WHERE md5( CONCAT( login, password ) ) = '$token' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		}
		return false ;
	}

	function Ops_get_AllOpsField( &$dbh,
					$table_name )
	{
		if ( $table_name == "" )
			return false ;

		LIST( $table_name ) = database_mysql_quote( $dbh, $table_name ) ;

		$query = "SELECT opID, $table_name FROM p_operators" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		}
		return false ;
	}

	function Ops_get_OpVars( &$dbh,
					$opid )
	{
		if ( !$opid )
			return false ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT * FROM p_op_vars WHERE opID = $opid LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		}
		return false ;
	}

	function Ops_get_NextRequestOp( &$dbh,
					$opid,
					$deptid,
					$rtype,
					$rstring,
					$dept_trans )
	{
		if ( !$deptid || ( $rtype == "" ) )
			return false ;

		global $VARS_EXPIRED_OPS ;
		$lastactive = time() - $VARS_EXPIRED_OPS - 10 ; // extra 10 seconds buffer
		$opid_direct_exists = 0 ; // flag to signal $opid is available

		if ( $rtype == 1 )
			$order_by = "ORDER BY p_dept_ops.display ASC" ;
		else if ( $rtype == 2 )
			$order_by = "ORDER BY p_operators.lastrequest ASC" ;
		else { return false ; }
		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$rstring_query = "" ;
		if ( $rstring )
		{
			$rstring_array = explode( ",", $rstring ) ;
			for ( $c = 0; $c < count( $rstring_array ); ++$c )
			{
				if ( is_numeric( $rstring_array[$c] ) && $rstring_array[$c] )
				{ $rstring_query .= " AND p_operators.opID <> ".$rstring_array[$c]." " ; }
			}
		}

		$query = "SELECT p_operators.opID AS opID, p_operators.lastactive AS lastactive, p_operators.maxc AS maxc, p_operators.mapp AS mapp, p_operators.ses AS ses, p_operators.rate AS rate, p_operators.sms AS sms, p_operators.smsnum AS smsnum, p_operators.name AS name, p_operators.email AS email FROM p_operators INNER JOIN p_dept_ops ON p_operators.opID = p_dept_ops.opID WHERE p_dept_ops.status = 1 AND ( ( p_operators.lastactive > $lastactive ) OR ( p_operators.mapp <> 0 ) ) AND p_dept_ops.deptID = $deptid $rstring_query $order_by" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$operators = Array() ; $ops_string = "" ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
			{
				$operators[] = $data ;
				$ops_string .= "$data[opID]," ;
				if ( $opid && ( $opid == $data["opID"] ) ) { $opid_direct_exists = $opid ; }
			}

			$op_chats = Array() ;
			// $query = "SELECT opID, count(*) AS total FROM p_requests WHERE op2op = 0 AND ended = 0 AND initiated = 0 GROUP BY opID" ;
			$query = "SELECT opID, count(*) AS total FROM p_requests WHERE op2op = 0 AND ended = 0 AND md5_vis_ <> 'grc' GROUP BY opID" ;
			database_mysql_query( $dbh, $query ) ;

			if ( $dbh[ 'ok' ] )
			{
				// $dept_trans == 2 indicates transfer to department. skip all queue and operator limit check.
				while ( $data = database_mysql_fetchrow( $dbh ) )
					$op_chats[$data["opID"]] = ( $dept_trans == 2 ) ? 0 : $data["total"] ;
			}

			$total_ops_online = count( $operators ) ;
			for ( $c = 0; $c < $total_ops_online; ++$c )
			{
				$operator = $operators[$c] ;
				if ( ( $opid_direct_exists == $operator["opID"] ) && ( !isset( $op_chats[$operator["opID"]] ) || ( $operator["maxc"] == -1 ) || ( $op_chats[$operator["opID"]] < $operator["maxc"] ) ) )
				{
					$operator["q_ops"] = $ops_string ;
					return $operator ;
				}
			}
			// standard check
			for ( $c = 0; $c < $total_ops_online; ++$c )
			{
				$operator = $operators[$c] ;
				if ( !isset( $op_chats[$operator["opID"]] ) || ( $operator["maxc"] == -1 ) || ( $op_chats[$operator["opID"]] < $operator["maxc"] ) )
				{
					$operator["q_ops"] = $ops_string ;
					return $operator ;
				}
			}
			return Array( "q_ops" => $ops_string ) ;
		}
		return false ;
	}

	function Ops_get_OpDepts( &$dbh,
						$opid )
	{
		if ( !$opid )
			return Array() ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT p_dept_ops.deptID AS deptID, p_dept_ops.status AS status, p_departments.name AS name, p_departments.tshare AS tshare FROM p_dept_ops, p_departments WHERE p_dept_ops.opID = $opid AND p_dept_ops.deptID = p_departments.deptID" ;
		database_mysql_query( $dbh, $query ) ;

		$depts = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$depts[] = $data ;
			return $depts ;
		}
		return $depts ;
	}
?>
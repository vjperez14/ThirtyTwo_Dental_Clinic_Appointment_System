<?php
	if ( defined( 'API_Depts_get' ) ) { return ; }
	define( 'API_Depts_get', true ) ;

	FUNCTION Depts_get_AllDepts( &$dbh, $order_by = "name ASC" )
	{
		$query = "SELECT * FROM p_departments ORDER BY $order_by" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$output = Array() ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		} return false ;
	}

	FUNCTION Depts_get_DeptOps( &$dbh,
						$deptid,
						$status = 0,
						$order_by = "p_dept_ops.display ASC" )
	{
		if ( !$deptid )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		if ( $deptid > 100000000 )
		{
			$dept_string = "" ;

			$query = "SELECT * FROM p_dept_groups WHERE groupID = $deptid" ;
			database_mysql_query( $dbh, $query ) ;
			$dept_group = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $dept_group["deptids"] ) )
			{
				$dept_group_deptids = explode( ",", $dept_group["deptids"] ) ;
				for ( $c = 0; $c < count( $dept_group_deptids ); ++$c )
				{
					$this_deptid = $dept_group_deptids[$c] ;
					if ( $this_deptid )
						$dept_string .= "p_dept_ops.deptID = $this_deptid OR " ;
				}
			}
			if ( $dept_string )
				$dept_string = "AND ( " . substr_replace( $dept_string, "", -3 ) . ")" ;
			else
				$dept_string = "AND p_dept_ops.deptID = -1" ; // default to no output because invalid $deptid
		}
		else
			$dept_string = ( $deptid ) ? "AND p_dept_ops.deptID = $deptid" : "" ;

		$status_string = ( $status ) ? "AND p_dept_ops.status = $status" : "" ;
		$query = "SELECT * FROM p_operators, p_dept_ops WHERE p_operators.opID = p_dept_ops.opID $dept_string $status_string GROUP BY p_dept_ops.opID ORDER BY $order_by" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$output = Array() ;
			while( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
			return $output ;
		} return false ;
	}

	FUNCTION Depts_get_DeptOps_OpsOnlinePic( &$dbh,
						$deptid,
						$status = 0 )
	{
		if ( !is_numeric( $deptid ) || !is_numeric( $status ) )
			return Array() ;

		LIST( $deptid, $status ) = database_mysql_quote( $dbh, $deptid, $status ) ;

		if ( $deptid > 100000000 )
		{
			$dept_string = "" ;
			$visible_string = "" ;

			$query = "SELECT * FROM p_dept_groups WHERE groupID = $deptid" ;
			database_mysql_query( $dbh, $query ) ;
			$dept_group = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $dept_group["deptids"] ) )
			{
				$dept_group_deptids = explode( ",", $dept_group["deptids"] ) ;
				for ( $c = 0; $c < count( $dept_group_deptids ); ++$c )
				{
					$this_deptid = $dept_group_deptids[$c] ;
					if ( $this_deptid )
						$dept_string .= "p_dept_ops.deptID = $this_deptid OR " ;
				}
			}
			if ( $dept_string )
				$dept_string = "AND ( " . substr_replace( $dept_string, "", -3 ) . ")" ;
			else
				$dept_string = "AND p_dept_ops.deptID = -1" ; // default to no output because invalid $deptid
		}
		else
		{
			$dept_string = ( $deptid ) ? "AND p_dept_ops.deptID = $deptid" : "" ;
			$visible_string = ( $deptid ) ? "" : "AND p_dept_ops.visible = 1" ;
		}

		$status_string = ( $status ) ? "AND p_dept_ops.status = $status" : "" ;
		$query = "SELECT p_operators.opID, p_operators.name FROM p_operators, p_dept_ops WHERE p_operators.opID = p_dept_ops.opID AND p_operators.pic = 1 AND p_operators.pic_form_display = 1 $dept_string $visible_string $status_string" ;
		database_mysql_query( $dbh, $query ) ;

		$ops = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while( $data = database_mysql_fetchrow( $dbh ) )
				$ops[] = $data ;
		} return $ops ;
	}

	FUNCTION Depts_get_OpDepts( &$dbh,
						$opid,
						$order_by = "p_departments.name ASC" )
	{
		if ( !$opid )
			return false ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "SELECT * FROM p_departments, p_dept_ops WHERE p_departments.deptID = p_dept_ops.deptID AND p_dept_ops.opID = $opid ORDER BY $order_by" ;
		database_mysql_query( $dbh, $query ) ;

		$depts = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while( $data = database_mysql_fetchrow( $dbh ) )
				$depts[] = $data ;
			return $depts ;
		} return false ;
	}

	FUNCTION Depts_get_DeptInfo( &$dbh,
						$deptid )
	{
		if ( !$deptid )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$query = "SELECT * FROM p_departments WHERE deptID = $deptid LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		} return false ;
	}

	FUNCTION Depts_get_DeptInfoByName( &$dbh,
						$name )
	{
		if ( $name == "" )
			return false ;

		LIST( $name ) = database_mysql_quote( $dbh, $name ) ;

		$query = "SELECT * FROM p_departments WHERE name = '$name' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		} return false ;
	}

	FUNCTION Depts_get_AllDeptsVars( &$dbh )
	{
		$query = "SELECT * FROM p_dept_vars" ;
		database_mysql_query( $dbh, $query ) ;

		$vars = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
			{
				$deptid = $data["deptID"] ;
				$vars[$deptid] = $data ;
			}
		} return $vars ;
	}

	FUNCTION Depts_get_DeptVars( &$dbh,
						$deptid )
	{
		if ( !$deptid )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$query = "SELECT * FROM p_dept_vars WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		} return false ;
	}

	FUNCTION Depts_get_AllDeptGroups( &$dbh )
	{
		$query = "SELECT * FROM p_dept_groups ORDER BY name ASC" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while ( $data = database_mysql_fetchrow( $dbh ) )
				$output[] = $data ;
		} return $output ;
	}

	FUNCTION Depts_get_DeptGroup( &$dbh,
						$groupid )
	{
		if ( !$groupid )
			return false ;

		LIST( $groupid ) = database_mysql_quote( $dbh, $groupid ) ;

		$query = "SELECT * FROM p_dept_groups WHERE groupID = $groupid" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			return $data ;
		} return false ;
	}

	FUNCTION Depts_get_IsDeptInGroup( &$dbh,
						$deptid )
	{
		if ( !$deptid )
			return false ;

		LIST( $deptid ) = database_mysql_quote( $dbh, $deptid ) ;

		$query = "SELECT * FROM p_dept_groups WHERE deptids LIKE '%,$deptid,%' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $data["groupID"] ) )
				return true ;
		} return false ;
	}

	FUNCTION Depts_get_SMTPByMd5( &$dbh,
						$smtp_md5 )
	{
		if ( $smtp_md5 == "" )
			return false ;

		LIST( $smtp_md5 ) = database_mysql_quote( $dbh, $smtp_md5 ) ;

		$query = "SELECT smtp FROM p_departments WHERE MD5( smtp ) = '$smtp_md5' LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $data["smtp"] ) )
				return $data["smtp"] ;
		} return false ;
	}
?>
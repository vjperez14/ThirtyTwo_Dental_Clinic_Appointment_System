<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$opid = isset( $_COOKIE["cO"] ) ? Util_Format_Sanatize( $_COOKIE["cO"], "n" ) : "" ;
	$ses = isset( $_COOKIE["cS"] ) ? Util_Format_Sanatize( $_COOKIE["cS"], "ln" ) : "" ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;

	if ( !$opid || !is_file( "$CONF[TYPE_IO_DIR]/$opid"."_ses_{$ses}.ses" ) )
		$json_data = "json_data = { \"status\": -1 };" ;
	else if ( $action === "deptops" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;

		$departments = Depts_get_AllDepts( $dbh ) ;
		$json_data = "json_data = { \"status\": 1, \"departments\": [  " ;
		for ( $c = 0; $c < count( $departments ); ++$c )
		{
			$department = $departments[$c] ;
			$dept_ops = Depts_get_DeptOps( $dbh, $department["deptID"], 0, "p_dept_ops.status DESC, p_operators.name ASC" ) ;

			$total_ops_online = 0 ;
			$json_data .= "{ \"deptid\": $department[deptID], \"name\": \"$department[name]\", \"rtype\": $department[rtype], \"operators\": [  " ;
			for ( $c2 = 0; $c2 < count( $dept_ops ); ++$c2 )
			{
				$operator = $dept_ops[$c2] ;
				$requests = Chat_get_OpTotalRequests( $dbh, $operator["opID"] ) ;

				if ( $operator["status"] )
					++$total_ops_online ;

				$json_data .= "{ \"opid\": $operator[opID], \"status\": $operator[status], \"name\": \"$operator[name]\", \"email\": \"$operator[email]\", \"requests\": $requests }," ;
			}
			$json_data = substr_replace( $json_data, "", -1 ) ;
			$json_data .= "	], \"online\": $total_ops_online }," ;
		}
		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
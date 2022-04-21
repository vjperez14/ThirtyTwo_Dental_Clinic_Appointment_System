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
	else if ( $action === "status" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;
	
		$status = Util_Format_Sanatize( Util_Format_GetVar( "status" ), "n" ) ;
		$mapp = Util_Format_Sanatize( Util_Format_GetVar( "mapp" ), "n" ) ;

		Ops_update_OpValue( $dbh, $opid, "lastactive", time() ) ;

		$total_depts_online = $total_dept_offline_set = 0 ;
		if ( !$status && !Ops_get_itr_AnyOpsOnline( $dbh, 0 ) )
		{
			$dir_files = glob( $CONF["TYPE_IO_DIR"]."/*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) && !preg_match( "/\.ses$/", $dir_files[$c] ) && !preg_match( "/\.mapp$/", $dir_files[$c] ) && !preg_match( "/index\.php$/", $dir_files[$c] ) && !preg_match( "/\.locked$/", $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
				}
			}
			Util_Format_CleanDeptOnline( "", "" ) ;
		}
		else if ( $status )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

			$op_depts = Depts_get_OpDepts( $dbh, $opid ) ;
			$total_op_depts = count( $op_depts ) ;
			for ( $c = 0; $c < $total_op_depts; ++$c )
			{
				$department = $op_depts[$c] ;

				// if only 1 department online, process the online just in case department got deleted and only
				// one department exists in the ops/op_op2op.php area and the individual status update option is not visible
				if ( !$department["dept_offline"] || ( $total_op_depts == 1 ) )
				{
					++$total_depts_online ;
					$flag_file = "online_".$department["deptID"]."_".$opid.".info" ;
					if ( !is_file( "$CONF[CHAT_IO_DIR]/$flag_file" ) ) { touch( "$CONF[CHAT_IO_DIR]/$flag_file" ) ; }
				}

				if ( $department["dept_offline"] ) { ++$total_dept_offline_set ; }
			}
		}
		else
		{
			Util_Format_CleanDeptOnline( "", $opid ) ;
		}

		if ( $status )
		{
			$total_depts_offline = ( $total_op_depts != $total_depts_online ) ? $total_op_depts - $total_depts_online : 0 ;
			if ( $total_dept_offline_set && ( $total_op_depts == 1 ) )
				Ops_update_OpResetOffStatus( $dbh, $opid ) ;

			if ( $total_depts_online )
			{
				Ops_update_PutOpStatus( $dbh, $opid, 1, $mapp ) ;
				Ops_update_OpValue( $dbh, $opid, "status", 1 ) ;
				$json_data = "json_data = { \"status\": 1, \"offline\": $total_depts_offline }; " ;
			}
			else
			{
				// there are no op departments online.  status remains offline
				$json_data = "json_data = { \"status\": 0 }; " ;
			}
		}
		else
		{
			// if offline, just set it offline.  dept_offline does not matter
			Ops_update_PutOpStatus( $dbh, $opid, 0, $mapp ) ;
			Ops_update_OpValue( $dbh, $opid, "status", 0 ) ;
			$json_data = "json_data = { \"status\": 1 }; " ;
		}
	}
	else if ( $action == "update_dept_offline" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;

		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$status = Util_Format_Sanatize( Util_Format_GetVar( "status" ), "n" ) ;

		$flag_file = "online_".$deptid."_".$opid.".info" ;
		if ( $status )
		{
			if ( !is_file( "$CONF[CHAT_IO_DIR]/$flag_file" ) ) { touch( "$CONF[CHAT_IO_DIR]/$flag_file" ) ; }
		}
		else
		{
			if ( is_file( "$CONF[CHAT_IO_DIR]/$flag_file" ) ) { @unlink( "$CONF[CHAT_IO_DIR]/$flag_file" ) ; }
		}
		
		if ( Ops_update_OpDeptOffStatus( $dbh, $opid, $deptid, $status ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error updating department offline status.  Please try again.\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
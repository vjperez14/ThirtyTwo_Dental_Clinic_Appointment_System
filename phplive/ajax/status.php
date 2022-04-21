<?php
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$akey = Util_Format_Sanatize( Util_Format_GetVar( "akey" ), "ln" ) ; $pst = Util_Format_Sanatize( Util_Format_GetVar( "pst" ), "n" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$token = Util_Format_Sanatize( Util_Format_GetVar( "token" ), "ln" ) ;
	$image_dir = realpath( "$CONF[DOCUMENT_ROOT]/pics/icons/pixels" ) ;
	$auto_connect_array = ( isset( $VALS["auto_connect"] ) && $VALS["auto_connect"] ) ? unserialize( $VALS["auto_connect"] ) : Array() ;
	$bot_online = 0 ;
	if ( $deptid )
		$bot_online = ( isset( $auto_connect_array[$deptid] ) && ( $auto_connect_array[$deptid]["auto_connect"] == "bot" ) ) ? 1 : 0 ;
	else
	{
		foreach ( $auto_connect_array as $this_deptid => $value )
		{
			if ( $auto_connect_array[$this_deptid]["auto_connect"] == "bot" )
				$bot_online = 1 ;
		}
	}
	if ( $bot_online && !is_file( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/phplivebot.php" ) ) { $bot_online = 0 ; }

	if ( $pst || ( isset( $CONF["API_KEY"] ) && ( md5( $akey ) == md5( $CONF["API_KEY"] ) ) ) )
	{
		if ( !isset( $CONF['SQLTYPE'] ) ) { $CONF['SQLTYPE'] = "SQLi.php" ; }
		else if ( $CONF['SQLTYPE'] == "mysql" ) { $CONF['SQLTYPE'] = "SQLi.php" ; }

		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

		Ops_update_itr_IdleOps( $dbh ) ;
		$total_ops_hash = Array() ;

		/*********************************************************/
		// department groups will always be greater than 100000000
		/*********************************************************/
		if ( $deptid > 100000000 )
		{
			$dept_group = Depts_get_DeptGroup( $dbh, $deptid ) ;
			if ( isset( $dept_group["deptids"] ) )
			{
				$dept_group_deptids = explode( ",", $dept_group["deptids"] ) ;
				for ( $c = 0; $c < count( $dept_group_deptids ); ++$c )
				{
					if ( $dept_group_deptids[$c] )
					{
						$total_ops = Ops_get_itr_AnyOpsOnline( $dbh, $dept_group_deptids[$c] ) ;
						$total_ops_hash[$dept_group_deptids[$c]] = $total_ops ;
					}
				}
			}
			else
			{
				$deptid = 0 ;
				goto standard ;
			}
		}
		else
		{
			$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
			if ( !isset( $deptinfo["deptID"] ) ) { $deptid = 0 ; }

			standard:
			$total_ops_hash[$deptid] = Ops_get_itr_AnyOpsOnline( $dbh, $deptid ) ;
		}

		$total_ops = ( $bot_online ) ? 1 : 0 ;
		foreach ( $total_ops_hash as $deptid => $total_ops_temp )
		{
			$file_name = "online_{$deptid}.info" ;
			if ( $total_ops_temp )
			{
				if ( !is_file( "$CONF[CHAT_IO_DIR]/$file_name" ) )
				{
					touch( "$CONF[CHAT_IO_DIR]/$file_name" ) ;
				}
			}
			else
			{
				if ( is_file( "$CONF[CHAT_IO_DIR]/$file_name" ) )
				{
					@unlink( "$CONF[CHAT_IO_DIR]/$file_name" ) ;
				}
				Util_Format_CleanDeptOnline( $deptid, "" ) ;
			}
			if ( !$total_ops && $total_ops_temp ) { $total_ops = $total_ops_temp ; }
		}

		if ( $action === "js" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_itr.php" ) ;

			LIST( $ip, $vis_token ) = Util_IP_GetIP( $token ) ;

			if ( $ip && preg_match( "/$ip/", $VALS["CHAT_SPAM_IPS"] ) ) { $image_path = "$image_dir/3x3.gif" ; }
			else if ( $total_ops ) { $image_path = "$image_dir/1x1.gif" ; }
			else { $image_path = "$image_dir/2x2.gif" ; }

			$op_invite = ( is_file( "$CONF[TYPE_IO_DIR]/{$vis_token}.txt" ) ) ? 1 : 0 ;

			$query = "SELECT ces FROM p_queue WHERE md5_vis = '$vis_token' AND embed = 1 LIMIT 1" ;
			database_mysql_query( $dbh, $query ) ; $queueinfo = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $queueinfo["ces"] ) && $total_ops ) { $image_path = "$image_dir/8x8.gif" ; }
			else if ( isset( $queueinfo["ces"] ) ) { $image_path = "$image_dir/12x12.gif" ; }
			else
			{
				$query = "SELECT ces, status, initiated FROM p_requests WHERE md5_vis = '$vis_token' AND ended = 0 LIMIT 1" ;
				database_mysql_query( $dbh, $query ) ; $requestinfo = database_mysql_fetchrow( $dbh ) ;
				if ( isset( $requestinfo["ces"] ) && $total_ops )
				{
					if ( $requestinfo["status"] ) { $image_path = "$image_dir/8x8.gif" ; }
					else if ( !$requestinfo["initiated"] ) { $image_path = "$image_dir/10x10.gif" ; }
					else { $image_path = "$image_dir/4x4.gif" ; }
				}
				else if ( isset( $requestinfo["ces"] ) )
				{
					if ( $requestinfo["status"] ) { $image_path = "$image_dir/9x9.gif" ; }
					else if ( !$requestinfo["initiated"] ) { $image_path = "$image_dir/11x11.gif" ; }
					else { $image_path = "$image_dir/5x5.gif" ; }
				}
				else if ( $op_invite && $total_ops ) { $image_path = "$image_dir/6x6.gif" ; }
				else if ( $op_invite ) { $image_path = "$image_dir/7x7.gif" ; }
			}

			Header( "Content-type: image/GIF" ) ;
			Header( "Content-Transfer-Encoding: binary" ) ;
			if ( !isset( $VALS['OB_CLEAN'] ) || ( $VALS['OB_CLEAN'] == 'on' ) ) { ob_clean(); flush(); }
			readfile( $image_path ) ;
		}
		else
		{
			HEADER('Content-Type: text/plain; charset=utf-8') ;
			if ( $total_ops ) { print 1 ; }
			else { print 0 ; }
		}
		database_mysql_close( $dbh ) ;
	}
	else
	{
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print "Invalid request" ;
	}
	exit ;
?>

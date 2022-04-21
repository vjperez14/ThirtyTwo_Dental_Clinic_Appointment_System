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
	if ( !isset( $CONF['SQLTYPE'] ) ) { $CONF['SQLTYPE'] = "SQL.php" ; }
	else if ( $CONF['SQLTYPE'] == "mysql" ) { $CONF['SQLTYPE'] = "SQL.php" ; }

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update_itr.php" ) ;

	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
	$image_dir = $CONF['CONF_ROOT'] ; $image_path = $image_type = "" ;

	LIST( $ip, $null ) = Util_IP_GetIP( "" ) ;
	$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
	LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
	$mobile = ( $os == 5 ) ? 1 : 0 ;

	Ops_update_itr_IdleOps( $dbh ) ;
	$total_ops_hash = Array() ;

	if ( $ip && preg_match( "/$ip/", $VALS["CHAT_SPAM_IPS"] ) )
		$total_ops = 0 ;
	else
	{
		if ( $deptid > 100000000 )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
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
			standard:
			$total_ops_hash[$deptid] = Ops_get_itr_AnyOpsOnline( $dbh, $deptid ) ;
		}
	}

	///////////////////////////
	// auto cleaning of DB
	// unlike footprints.php, this gets called once per page load.  frequent cleaning ok
	$vars = Util_Format_Get_Vars( $dbh ) ;
	if ( $vars["ts_clear"] <= ( $now - ( $VARS_CYCLE_CLEAN * 2 ) ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/remove_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/remove.php" ) ;
		Util_Format_Update_TimeStamp( $dbh, "clear", $now ) ;
		Footprints_remove_itr_Expired_U( $dbh ) ;
		IPs_remove_Expired_IPs( $dbh ) ;
	}
	///////////////////////////

	database_mysql_close( $dbh ) ;

	$prefix = "" ;
	$total_ops = 0 ;
	foreach ( $total_ops_hash as $deptid => $total_ops_temp )
	{
		$file_name = "online_{$deptid}.info" ;
		if ( $total_ops_temp )
		{
			$prefix = "icon_online" ;
			if ( !is_file( "$CONF[CHAT_IO_DIR]/$file_name" ) )
			{
				touch( "$CONF[CHAT_IO_DIR]/$file_name" ) ;
			}
		}
		else
		{
			$prefix = "icon_offline" ;
			if ( is_file( "$CONF[CHAT_IO_DIR]/$file_name" ) )
			{
				@unlink( "$CONF[CHAT_IO_DIR]/$file_name" ) ;
			}
			Util_Format_CleanDeptOnline( $deptid, "" ) ;
		}
		if ( !$total_ops && $total_ops_temp ) { $total_ops = $total_ops_temp ; }
	}

	$offline = ( isset( $VALS['OFFLINE'] ) && $VALS['OFFLINE'] ) ? unserialize( $VALS['OFFLINE'] ) : Array() ;
	if ( !$total_ops && isset( $offline[$deptid] ) && ( $offline[$deptid] == "hide" ) )
	{
		$image_type = "GIF" ;
		$image_path = "$CONF[DOCUMENT_ROOT]/pics/space.gif" ;
	}
	else if ( $prefix && is_file( realpath( "$image_dir/$prefix"."_{$deptid}.GIF" ) ) )
	{
		$image_type = "GIF" ;
		$image_path = "$image_dir/$prefix"."_{$deptid}.GIF" ;
	}
	else if ( $prefix && is_file( realpath( "$image_dir/$prefix"."_{$deptid}.JPEG" ) ) )
	{
		$image_type = "JPEG" ;
		$image_path = "$image_dir/$prefix"."_{$deptid}.JPEG";
	}
	else if ( $prefix && is_file( realpath( "$image_dir/$prefix"."_{$deptid}.PNG" ) ) )
	{
		$image_type = "PNG" ;
		$image_path = "$image_dir/$prefix"."_{$deptid}.PNG" ;
	}
	else if ( $prefix && is_file( realpath( "$image_dir/$CONF[$prefix]" ) ) && isset( $CONF[$prefix] ) && $CONF[$prefix] )
	{
		$image_type = preg_replace( "/(.*?)./", "", $CONF[$prefix] ) ;
		$image_path = "$image_dir/$CONF[$prefix]" ;
	}
	else
	{
		if ( !$prefix ) { $prefix = "icon_offline" ; }
		$image_type = "GIF" ;
		$image_path = "$CONF[DOCUMENT_ROOT]/pics/icons/$prefix.gif" ;
	}

	Header( "Content-type: image/$image_type" ) ;
	Header( "Content-Transfer-Encoding: binary" ) ;
	if ( !isset( $VALS['OB_CLEAN'] ) || ( $VALS['OB_CLEAN'] == 'on' ) ) { ob_clean(); flush(); }
	readfile( $image_path ) ;
?>
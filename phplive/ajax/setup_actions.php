<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	// STANDARD header for Setup
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) )
	{
		$json_data = "json_data = { \"status\": 0, \"error\": \"Authentication error.\" };" ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;

	if ( $action === "moveup" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

		if ( Ops_get_IsOpInDept( $dbh, $opid, $deptid ) || !$opid )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;

			$dept_ops = Depts_get_DeptOps( $dbh, $deptid ) ;

			$json_data = "json_data = { \"status\": 1, \"ops\": [ " ;
			for ( $c = 0; $c < count( $dept_ops ); ++$c )
			{
				$dept_op = $dept_ops[$c] ;
				$status = $dept_op["status"] ;
				
				$json_data .= "{ \"name\": \"$dept_op[name]\", \"opid\": $dept_op[opID], \"login\": \"$dept_op[login]\", \"status\": $status, \"display\": $dept_op[display] }," ;
			}

			$json_data = substr_replace( $json_data, "", -1 ) ;
			$json_data .= "	] };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0 };" ;
	}
	else if ( $action === "op_dept_remove" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/remove.php" ) ;

		Ops_remove_OpDept( $dbh, $opid, $deptid ) ;
		$total_assigned = Ops_get_TotalOpsAssigned( $dbh ) ;

		$json_data = "json_data = { \"status\": 1, \"total\": \"$total_assigned\" };" ;
	}
	else if ( $action === "add_eip" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;

		$ip = trim( Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "ip" ) ) ;

		// reserved word "agent", store the user agent for situations IP changes a lot
		// in a modest traffic website only for dev purposes
		if ( strtolower( $ip ) == "agent" )
		{
			$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
			$agent_lang = isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "&nbsp;" ;
			$agent_md5 = md5( "$agent$agent_lang" ) ;
			$ip = $agent_md5 ;
		}

		if ( !Util_IP_IsIPExcluded( $ip, 1 ) )
		{
			$val = preg_replace( "/ +/", "", $VALS["TRAFFIC_EXCLUDE_IPS"] ) . "-$ip" ;
			$val = preg_replace( "/--/", "-", $val ) ;
			Util_Vals_WriteToFile( "TRAFFIC_EXCLUDE_IPS", Util_Format_Trim( $val ) ) ;
			$json_data = "json_data = { \"status\": 1 }; " ;
		}
		else
			$json_data = "json_data = { \"status\": 0 }; " ;
	}
	else if ( $action === "add_sip" )
	{
		$ip = trim( Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "lns" ) ) ;

		if ( !preg_match( "/$ip/", $VALS["CHAT_SPAM_IPS"] ) )
		{
			$val = preg_replace( "/ +/", "", $VALS["CHAT_SPAM_IPS"] ) . "-$ip" ;
			$val = preg_replace( "/--/", "-", $val ) ;
			Util_Vals_WriteToFile( "CHAT_SPAM_IPS", Util_Format_Trim( $val ) ) ;
			$json_data = "json_data = { \"status\": 1 }; " ;
		}
		else
			$json_data = "json_data = { \"status\": 0 }; " ;
	}
	else if ( $action === "add_tag" )
	{
		$tid = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "tid" ), "n" ) ) ;
		$tag = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "tag" ), "ln" ) ) ;
		$color = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "color" ), "ln" ) ) ;

		$error = 0 ;
		$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;
		foreach ( $tags as $index => $value )
		{
			if ( $index != "c" )
			{
				LIST( $status, $thiscolor, $thistag ) = explode( ",", $value ) ;
				$thistag = rawurldecode( $thistag ) ;
				if ( $status && ( $tag == $thistag ) ) { $error = 1 ; }
				else if ( !$status && ( $tag == $thistag ) ) { $tags[$index] = "1,$color,".rawurlencode($tag) ; $error = 2 ; }
				else if ( $index == $tid ) { $tags[$index] = "1,$color,".rawurlencode($tag) ; $error = 2 ; }
			}
		}

		if ( $error == 1 )
			$json_data = "json_data = { \"status\": 0, \"error\": \"Tag exists.\" }; " ;
		else
		{
			if ( !$error )
			{
				$index = isset( $tags["c"] ) ? $tags["c"]+1 : 1 ;
				$tag = rawurlencode( $tag ) ;
				$tags[$index] = "1,$color,$tag" ;
				$tags["c"] = $index ;
			}
			$serialized = serialize( $tags ) ;

			Util_Vals_WriteToFile( "TAGS", $serialized ) ;
			$json_data = "json_data = { \"status\": 1 }; " ;
		}
	}
	else if ( $action === "eips" )
	{
		$ips = explode( "-", Util_Format_Sanatize( $VALS['TRAFFIC_EXCLUDE_IPS'], "ip" ) ) ;

		$json_data = "json_data = { \"status\": 1, \"ips\": [ " ;
		for ( $c = 0; $c < count( $ips ); ++$c )
		{
			if ( $ips[$c] )
				$json_data .= "{ \"ip\": \"$ips[$c]\" }," ;
		}

		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action === "tags" )
	{
		$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;

		$json_data = "json_data = { \"status\": 1, \"tags\": [ " ;
		foreach ( $tags as $index => $value )
		{
			if ( $index != "c" )
			{
				LIST( $status, $color, $tag ) = explode( ",", $value ) ;
				if ( $status ) { $json_data .= "{ \"id\": $index, \"tag\": \"$tag\", \"color\": \"$color\" }," ; }
			}
		}

		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action === "remove_eip" )
	{
		$ip = Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "ip" ) ;

		$pattern = '/'.quotemeta( $ip ).'/i' ;
		$val = preg_replace( $pattern, "", preg_replace( "/ +/", "", Util_Format_Sanatize( $VALS["TRAFFIC_EXCLUDE_IPS"], "ip" ) ) ) ;
		Util_Vals_WriteToFile( "TRAFFIC_EXCLUDE_IPS", Util_Format_Trim( $val ) ) ;

		$ips = explode( "-", $val ) ;
		$json_data = "json_data = { \"status\": 1, \"ips\": [ " ;
		for ( $c = 0; $c < count( $ips ); ++$c )
		{
			if ( preg_match( "/\d+/", $ips[$c] ) )
				$json_data .= "{ \"ip\": \"$ips[$c]\" }," ;
		}

		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action === "remove_sip" )
	{
		$ip = Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "lns" ) ;

		$val = preg_replace( "/$ip/", "", preg_replace( "/ +/", "", Util_Format_Sanatize( $VALS["CHAT_SPAM_IPS"], "lns" ) ) ) ;
		Util_Vals_WriteToFile( "CHAT_SPAM_IPS", Util_Format_Trim( $val ) ) ;

		$ips = explode( "-", $val ) ;
		$json_data = "json_data = { \"status\": 1, \"ips\": [ " ;
		for ( $c = 0; $c < count( $ips ); ++$c )
		{
			if ( preg_match( "/\d+/", $ips[$c] ) )
				$json_data .= "{ \"ip\": \"$ips[$c]\" }," ;
		}

		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action === "remove_tag" )
	{
		$id = Util_Format_Sanatize( Util_Format_GetVar( "id" ), "n" ) ;

		$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;
		if ( isset( $tags[$id] ) && ( $id != "c" ) )
		{
			LIST( $status, $color, $tag ) = explode( ",", $tags[$id] ) ;
			$tags[$id] = "0,$color,$tag" ;
			$serialized = serialize( $tags ) ;

			Util_Vals_WriteToFile( "TAGS", $serialized ) ;
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "sips" )
	{
		$ips = explode( "-", Util_Format_Sanatize( $VALS['CHAT_SPAM_IPS'], "lns" ) ) ;

		$json_data = "json_data = { \"status\": 1, \"ips\": [ " ;
		for ( $c = 0; $c < count( $ips ); ++$c )
		{
			if ( $ips[$c] )
				$json_data .= "{ \"ip\": \"$ips[$c]\" }," ;
		}

		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action === "update_foot_settings" )
	{
		$option = Util_Format_Sanatize( Util_Format_GetVar( "option" ), "ln" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;
		$conf_name = "" ;
		if ( $option == "foot_settings" ) { $conf_name = "foot_log" ; }
		else if ( $option == "foot_icon" ) { $conf_name = "icon_check" ; }

		if ( $conf_name )
		{
			if ( $value && Util_Vals_WriteToConfFile( $conf_name, $value ) )
				$json_data = "json_data = { \"status\": 1 };" ;
			else
				$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to conf file [$value].\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action.\" };" ;
	}
	else if ( $action === "update_cookie" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToConfFile( "cookie", $value ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to conf file [$value] [e0].\" };" ;
	}
	else if ( $action === "update_popout" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "POPOUT", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [$value] [e0].\" };" ;
	}
	else if ( $action === "update_opauto" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "EMBED_OPINVITE_AUTO", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [$value] [e1].\" };" ;
	}
	else if ( $action === "update_embed_pos" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "EMBED_POS", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [$value] [e2].\" };" ;
	}
	else if ( $action === "update_dept_name_vis" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "DEPT_NAME_VIS", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [$value] [e3].\" };" ;
	}
	else if ( $action === "update_printer_icon" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "PRINTER_ICON", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [$value] [e4].\" };" ;
	}
	else if ( $action === "update_embed_animate" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "EMBED_ANIMATE", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [$value] [e5].\" };" ;
	}
	else if ( $action === "update_vars" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Vars/update.php" ) ;
		$varname = Util_Format_Sanatize( Util_Format_GetVar( "varname" ), "ln" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $varname == "char_set" ) { $value = serialize( Array(0=>"$value") ) ; }
		if ( Vars_update_Var( $dbh, $varname, $value ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0 };" ;
	}
	else if ( $action === "update_emlogo" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		$departments = Depts_get_AllDepts( $dbh ) ;

		$emlogos_hash = ( isset( $VALS["EMLOGOS"] ) && $VALS["EMLOGOS"] ) ? unserialize( $VALS["EMLOGOS"] ) : Array() ;
		$emlogos_hash[$deptid] = $value ;
		if ( !$deptid && ( count( $departments ) == 1 ) )
		{
			// copy setting to department for proper display on chat window
			$deptid = $departments[0]["deptID"] ;
			$emlogos_hash[$deptid] = $value ;
		}
		if ( Util_Vals_WriteToFile( "EMLOGOS", serialize( $emlogos_hash ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0 };" ;
	}
	else if ( $action === "update_autocorrect" )
	{
		$vo = Util_Format_Sanatize( Util_Format_GetVar( "vo" ), "ln" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		$process = 0 ;
		if ( $vo == "v" ) { $process = "AUTOCORRECT_V" ; }
		else if ( $vo == "o" ) { $process = "AUTOCORRECT_O" ; }

		if ( $process && Util_Vals_WriteToFile( $process, $value ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0 };" ;
	}
	else if ( $action === "update_profile" )
	{
		$email = Util_Format_Sanatize( Util_Format_GetVar( "email" ), "e" ) ;
		$login = Util_Format_Sanatize( Util_Format_GetVar( "login" ), "ln" ) ;
		$password = Util_Format_Sanatize( Util_Format_GetVar( "password" ), "ln" ) ;
		$npassword = Util_Format_Sanatize( Util_Format_GetVar( "npassword" ), "ln" ) ;
		$vpassword = Util_Format_Sanatize( Util_Format_GetVar( "vpassword" ), "ln" ) ;
		$md5_password = Util_Format_Sanatize( Util_Format_GetVar( "md5_password" ), "ln" ) ;
		$md5_cookie = md5( $_COOKIE["phpliveadminSES"] ) ;

		if ( $md5_password == md5( $npassword.$vpassword.$md5_cookie ) )
		{
			if ( $password == md5( md5( md5($KEY."_key_phplive") ).$md5_cookie ) )
			{
				$error = ( Util_Vals_WriteToConfFile( "KEY", md5($KEY."_key_phplive") ) ) ? "" : "Could not write to config file." ;
				if ( !$error )
					$json_data = "json_data = { \"status\": 2 };" ;
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"$error\" };" ;
			}
			else
			{
				// phplive_pr = password reset flag
				if ( ( $password == md5( "c4ca4238a0b923820dcc509a6f75849b".$md5_cookie ) ) && ( isset( $_COOKIE["phplive_pr"] ) && ( $_COOKIE["phplive_pr"] == md5( "phplive".substr( md5( $CONF['SALT'].$admininfo["password"] ), 6, 12 ) ) ) ) ) { $password = md5( $admininfo["password"].$md5_cookie ) ; }
				if ( $password == md5( $admininfo["password"].$md5_cookie ) )
				{
					if ( $npassword && ( $password == $vpassword ) )
						$json_data = "json_data = { \"status\": 0, \"error\": \"New Password must be different then the Current Password.\" };" ;
					else
					{
						LIST( $email, $login ) = database_mysql_quote( $dbh, $email, $login ) ;

						$password_query = "" ;
						if ( $npassword && ( $npassword != "d41d8cd98f00b204e9800998ecf8427e" ) ) { $password_query = " , password = '$npassword' " ; }

						$query = "UPDATE p_admins SET login = '$login', email = '$email' $password_query WHERE adminID = $admininfo[adminID]" ;
						database_mysql_query( $dbh, $query ) ;

						if ( $dbh[ 'ok' ] )
						{
							Util_Format_SetCookie( "phplive_pr", "", -1, "/", "", $PHPLIVE_SECURE ) ;
							$json_data = "json_data = { \"status\": 1 };" ;
						}
						else
							$json_data = "json_data = { \"status\": 0, \"error\": \"DB Error: $dbh[error]\" };" ;
					}
				}
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"Current Password is invalid.\" };" ;
			}
		}
		else { $json_data = "json_data = { \"status\": 0, \"error\": \"Could not update password.  Please try again.\" };" ; }
	}
	else if ( $action === "remote_disconnect" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$opinfo = Ops_get_OpInfoByID( $dbh, $opid ) ;

		if ( isset( $opinfo["opID"] ) && $opid && ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) || $opinfo["mapp"] ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;

			Ops_update_OpValue( $dbh, $opid, "signall", 1 ) ;
			Ops_update_OpValue( $dbh, $opid, "status", 0 ) ;
			Ops_update_PutOpStatus( $dbh, $opid, 0, 0 ) ;

			$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
			if ( isset( $mapp_array[$opid] ) ) { $arn = $mapp_array[$opid]["a"] ; $platform = $mapp_array[$opid]["p"] ; }
			if ( isset( $arn ) && $arn )
			{
				Ops_update_OpValue( $dbh, $opid, "mapp", 0 ) ;
				Util_MAPP_Publish( $opid, "new_request", $platform, $arn, "Remote Disconnect by Admin. You are Offline." ) ;
			}

			if ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) )
			{
				@unlink( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) ;
			}
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( isset( $opinfo["opID"] ) && $opid && Ops_update_OpValue( $dbh, $opid, "signall", 1 ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error processing remote disconnect.\" };" ;
	}
	else if ( $action === "delete_message" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Messages/remove.php" ) ;

		$messageid = Util_Format_Sanatize( Util_Format_GetVar( "messageid" ), "n" ) ;
		Messages_remove_Messages( $dbh, $messageid ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "update_savem" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$savem = Util_Format_Sanatize( Util_Format_GetVar( "savem" ), "n" ) ;

		Depts_update_DeptValue( $dbh, $deptid, "savem", $savem ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "generate_setup_admin" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Setup/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Setup/put.php" ) ;

		if ( $admininfo["status"] != -1 )
		{
			$login = Util_Format_Sanatize( Util_Format_GetVar( "login" ), "ln" ) ;
			$adminid = Util_Format_Sanatize( Util_Format_GetVar( "adminid" ), "n" ) ;
			$reset = Util_Format_Sanatize( Util_Format_GetVar( "reset" ), "n" ) ;
			$access_array = Util_Format_Sanatize( Util_Format_GetVar( "access" ), "a" ) ;
			$error = "" ;

			$temp = Util_Format_RandomString( 10 ) ;
			$email = md5( "$now$temp" ) ;
			$password = md5( "phplive".substr( $email, -6, 6 ) ) ;

			$access_hash = Array() ;
			for ( $c = 0; $c < count( $access_array ); ++$c )
			{
				$access = Util_Format_Sanatize( $access_array[$c], "lns" ) ;
				$access_hash[$access] = 1 ;
			}

			$admininfo_ = Setup_get_InfoByLogin( $dbh, $login ) ;
			if ( ( !$adminid && isset( $admininfo_["login"] ) ) || ( $adminid && isset( $admininfo_["login"] ) && ( $login != $admininfo_["login"] ) ) )
				$error = "Login ($login) is already in use" ;
			else
			{
				if ( $adminid && !$reset )
				{
					$admininfo_ = Setup_get_InfoByID( $dbh, $adminid ) ;
					$password = $admininfo_["password"] ;
				}

				if ( Setup_put_Account( $dbh, $adminid, $now, $login, $password, $email, serialize( $access_hash ) ) )
					$json_data = "json_data = { \"status\": 1 };" ;
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"DB Error: $dbh[error]\" };" ;
			}
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Action not available for this account.\" };" ;
	}
	else if ( $action === "fetch_setup_admins" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Setup/get.php" ) ;

		$admins = Setup_get_AllAccounts( $dbh ) ;

		$json_data = "json_data = { \"status\": 1, \"admins\": [ " ;
		if ( $admininfo["status"] != -1 )
		{
			for ( $c = 0; $c < count( $admins ); ++$c )
			{
				$admin = $admins[$c] ;
				if ( $admin["status"] == -1 )
				{
					$created = date( "M j, Y ($VARS_TIMEFORMAT)", $admin["created"] ) ;
					$lastactive = ( $admin["lastactive"] ) ? date( "M j, Y ($VARS_TIMEFORMAT)", $admin["lastactive"] ) : "<i>not yet logged in</i>" ;
					$password = "phplive".substr( $admin["email"], -6, 6 ) ;
					$access_array = ( $admin["access"] ) ? unserialize( $admin["access"] ) : Array() ;
					$access_string = implode( ",", array_keys( $access_array ) ) ;

					// password was changed.  can't display because password unknown
					if ( $admin["password"] != md5( $password ) ) { $password = "******" ; }

					$json_data .= "{ \"adminid\": \"$admin[adminID]\", \"created\": \"$created\", \"lastactive\": \"$lastactive\", \"status\": $admin[status], \"login\": \"$admin[login]\", \"password\": \"$password\", \"access\": \"$access_string\" }," ;
				}
			}
		}

		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action === "delete_setup_admin" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Setup/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Setup/remove.php" ) ;

		$adminid = Util_Format_Sanatize( Util_Format_GetVar( "adminid" ), "n" ) ;

		if ( $admininfo["status"] != -1 )
		{
			$admininfo_ = Setup_get_InfoByID( $dbh, $adminid ) ;
			if ( isset( $admininfo_["adminID"] ) && ( $admininfo_["status"] == -1 ) )
			{
				Setup_remove_Admin( $dbh, $adminid ) ;
				$json_data = "json_data = { \"status\": 1 };" ;
			}
			else
				$json_data = "json_data = { \"status\": 0, \"error\": \"Account cannot be deleted.\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Action not available for this account.\" };" ;
	}
	else if ( $action === "update_mobile_newwin" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		if ( Util_Vals_WriteToFile( "MOBILE_NEWWIN", Util_Format_Trim( $value ) ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to vals file [e11].\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action. [a1]\" };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
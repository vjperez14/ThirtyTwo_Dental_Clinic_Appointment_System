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
	if ( !$setupinfo = Util_Security_AuthSetup( $dbh ) )
	{
		$json_data = "json_data = { \"status\": 0, \"error\": \"Authentication error.\" };" ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	// STANDARD header end
	/****************************************/

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	if ( $action === "update_profile_pic_onoff" )
	{
		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		if ( $opid )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
			Ops_update_OpValue( $dbh, $opid, "pic", $value ) ;
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "update_pic_edit" )
	{
		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;
		$flag = Util_Format_Sanatize( Util_Format_GetVar( "flag" ), "n" ) ;

		if ( $opid )
		{
			if ( $flag )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
				Ops_update_OpVarValue( $dbh, $opid, "pic_edit", $value ) ;
			}
			else
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
				Ops_put_OpVars( $dbh, $opid ) ;
				Ops_update_OpVarValue( $dbh, $opid, "pic_edit", $value ) ;
			}
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "update_pic_form_display" )
	{
		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		if ( $opid )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
			Ops_update_OpValue( $dbh, $opid, "pic_form_display", $value ) ;
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "save_mapp_key" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$mkey = Util_Format_Sanatize( Util_Format_GetVar( "mkey" ), "ln" ) ;

		Util_Vals_WriteToConfFile( "MAPP_KEY", $mkey ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "tag" )
	{
		$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;
		$tagid = Util_Format_Sanatize( Util_Format_GetVar( "tagid" ), "n" ) ;

		$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;
		if ( $ces && ( isset( $tags[$tagid] ) || !$tagid ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;

			Chat_update_RequestLogValue( $dbh, $ces, "tag", $tagid ) ;
			Chat_update_TranscriptValue( $dbh, $ces, "tag", $tagid ) ;
			$json_data = "json_data = { \"status\": 1 }; " ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"A chat session must be active.\" }; " ;
	}
	else if ( $action == "update_upmax" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$upmax_days = Util_Format_Sanatize( Util_Format_GetVar( "days" ), "n" ) ;
		$upmax_bytes = Util_Format_Sanatize( Util_Format_GetVar( "bytes" ), "n" ) ;
		$fname = Util_Format_Sanatize( Util_Format_GetVar( "fname" ), "ln" ) ;
		if ( $fname != "random" ) { $fname = "same" ; }

		$upload_max_filesize = ini_get( "upload_max_filesize" ) ;
		$upload_max_post = ini_get( "post_max_size" ) ;

		if ( $upload_max_filesize && preg_match( "/k/i", $upload_max_filesize ) )
		{
			$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
			$max_bytes = $temp * 1000 ;
		}
		else if ( $upload_max_filesize && preg_match( "/m/i", $upload_max_filesize ) )
		{
			$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
			$max_bytes = $temp * 1000000 ;
		}
		else if ( $upload_max_filesize && preg_match( "/g/i", $upload_max_filesize ) )
		{
			$temp = Util_Format_Sanatize( $upload_max_filesize, "n" ) ;
			$max_bytes = $temp * 1000000000 ;
		}
		else { $max_bytes = 500000 ; }

		if ( $upload_max_post && preg_match( "/k/i", $upload_max_post ) )
		{
			$temp = Util_Format_Sanatize( $upload_max_post, "n" ) ;
			$max_post_bytes = $temp * 1000 ;
		}
		else if ( $upload_max_post && preg_match( "/m/i", $upload_max_post ) )
		{
			$temp = Util_Format_Sanatize( $upload_max_post, "n" ) ;
			$max_post_bytes = $temp * 1000000 ;
		}
		else if ( $upload_max_post && preg_match( "/g/i", $upload_max_post ) )
		{
			$temp = Util_Format_Sanatize( $upload_max_post, "n" ) ;
			$max_post_bytes = $temp * 1000000000 ;
		}
		else if ( $upload_max_post ) { $max_post_bytes = $upload_max_post ; }

		$upmax_array = Array() ;
		$upmax_array['days'] = $upmax_days ;
		$temp = ( $upmax_bytes > $max_bytes ) ? $max_bytes : $upmax_bytes ;
		$upmax_array['bytes'] = ( $upload_max_post && ( $temp > $max_post_bytes ) ) ? $max_post_bytes : $temp ;
		$upmax_array['fname'] = $fname ;

		Util_Vals_WriteToFile( "UPLOAD_MAX", serialize( $upmax_array ) ) ;
		$json_data = "json_data = { \"status\": 1 }; " ;
	}
	else if ( $action == "send_test_email" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Email.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
		if ( isset( $deptinfo["smtp"] ) )
		{
			if ( $deptinfo["smtp"] )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;
				$smtp_array = unserialize( Util_Functions_itr_Decrypt( $CONF["SALT"], $deptinfo["smtp"] ) ) ;
			}
			$from_name = $to_name = $deptinfo["name"] ;
			$from_email = $to_email = $deptinfo["email"] ;
			$vsubject = "Live Chat Test Email [SUCCESS]" ;
			$message = "Good news!  Emails are sending successfully." ;

			$error = Util_Email_SendEmail( $from_name, $from_email, $to_name, $to_email, $vsubject, $message, "" ) ;
			if ( $error )
			{
				$error = Util_Format_ConvertQuotes( $error ) ;
				$json_data = "json_data = { \"status\": 0, \"error\": \"$error\" };" ;
			}
			else
				$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Department not found.\" };" ;
	}
	else if ( $action == "delete_policy" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		if ( Depts_update_DeptVarsValue( $dbh, $deptid, "gdpr_msg", "" ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error deleting Department policy.\" };" ;
	}
	else if ( $action == "delete_endmsg" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		if ( Depts_update_DeptVarsValue( $dbh, $deptid, "end_chat_msg", "" ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error deleting Chat End Message.\" };" ;
	}
	else if ( $action == "update_dept_group" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/put.php" ) ;
		$groupid = Util_Format_Sanatize( Util_Format_GetVar( "gid" ), "n" ) ;
		$name = Util_Format_Sanatize( Util_Format_GetVar( "name" ), "ln" ) ;
		$lang = Util_Format_Sanatize( Util_Format_GetVar( "lang" ), "ln" ) ;
		$deptids = Util_Format_Sanatize( Util_Format_GetVar( "deptids" ), "ln" ) ;

		if ( !$groupid ) { $groupid = $now ; }
		if ( Depts_put_DeptGroups( $dbh, $groupid, $name, $lang, $deptids ) )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error saving Department Groups.\" };" ;
	}
	else if ( $action == "update_timezone" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$timezone = Util_Format_Sanatize( Util_Format_GetVar( "timezone" ), "timezone" ) ;
		$format = Util_Format_Sanatize( Util_Format_GetVar( "format" ), "n" ) ;
		if ( $format != 24 ) { $format = 12 ; }

		$error = "" ;
		if ( $timezone != $CONF["TIMEZONE"] )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
			Chat_remove_ResetReports( $dbh ) ;

			$error = ( Util_Vals_WriteToConfFile( "TIMEZONE", $timezone ) ) ? "" : "Could not write to config file." ;
		}
		if ( !Util_Vals_WriteToFile( "TIMEFORMAT", $format ) )
			$error = "Error updating time format." ;

		if ( !$error )
			$json_data = "json_data = { \"status\": 1 };" ;
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"$error\" };" ;
	}
	else if ( $action === "fetch_deptops" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		if ( $deptid )
			$operators = Depts_get_DeptOps( $dbh, $deptid ) ;
		else
			$operators = Ops_get_AllOps( $dbh ) ;

		$json_data = "json_data = { \"status\": 1, \"operators\": [  " ;
		for ( $c = 0; $c < count( $operators ); ++$c )
		{
			$operator = $operators[$c] ;
			if ( $operator["name"] && ( $operator["login"] != "phplivebot" ) )
			{
				$name = rawurlencode( $operator["name"] ) ;
				$json_data .= "{ \"opid\": \"$operator[opID]\", \"name\": \"$name\" }," ;
			}
		}
		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else if ( $action == "embed_win" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
		$width = Util_Format_Sanatize( Util_Format_GetVar( "width" ), "n" ) ;
		$height = Util_Format_Sanatize( Util_Format_GetVar( "height" ), "n" ) ;

		$embed_win_sizes = ( isset( $VALS["embed_win_sizes"] ) && $VALS["embed_win_sizes"] ) ? unserialize( $VALS["embed_win_sizes"] ) : Array() ;
		$embed_win_sizes[$deptid] = Array() ;
		if ( $width && $height )
		{
			$embed_win_sizes[$deptid]["width"] = $width ;
			$embed_win_sizes[$deptid]["height"] = $height ;
		}
		else
		{
			if ( isset( $embed_win_sizes[$deptid] ) ) { unset( $embed_win_sizes[$deptid] ) ; }
		}
		Util_Vals_WriteToFile( "embed_win_sizes", serialize( $embed_win_sizes ) ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action == "update_svg" ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/svg/ajax/actions.php" ) ; }
	else if ( $action == "update_alttext" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$reset = Util_Format_Sanatize( Util_Format_GetVar( "reset" ), "n" ) ;
		$alt_online = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_online" ), "notags" ) ) ;
		$alt_offline = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_offline" ), "notags" ) ) ;
		$alt_invite = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_invite" ), "notags" ) ) ;
		$alt_close = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_close" ), "notags" ) ) ;
		$alt_emminimize = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_emminimize" ), "notags" ) ) ;
		$alt_empopout = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_empopout" ), "notags" ) ) ;
		$alt_emclose = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_emclose" ), "notags" ) ) ;
		$alt_emmaximize = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "alt_emmaximize" ), "notags" ) ) ;

		$alttext_array = ( isset( $VALS["alttext"] ) && $VALS["alttext"] ) ? unserialize( $VALS["alttext"] ) : Array() ;
		if ( !$reset )
		{
			if ( !isset( $alttext_array[$deptid] ) )
				$alttext_array[$deptid] = Array() ;
			$alttext_array[$deptid]["online"] = base64_encode( $alt_online ) ;
			$alttext_array[$deptid]["offline"] = base64_encode( $alt_offline ) ;
			$alttext_array[$deptid]["invite"] = base64_encode( $alt_invite ) ;
			$alttext_array[$deptid]["close"] = base64_encode( $alt_close ) ;
			$alttext_array[$deptid]["emminimize"] = base64_encode( $alt_emminimize ) ;
			$alttext_array[$deptid]["empopout"] = base64_encode( $alt_empopout ) ;
			$alttext_array[$deptid]["emclose"] = base64_encode( $alt_emclose ) ;
			$alttext_array[$deptid]["emmaximize"] = base64_encode( $alt_emmaximize ) ;
		}
		else
		{
			if ( isset( $alttext_array[$deptid] ) ) { unset( $alttext_array[$deptid] ) ; }
		}
		Util_Vals_WriteToFile( "alttext", serialize( $alttext_array ) ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action == "clear_status_activity" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/remove.php" ) ;
		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;

		$operator = Ops_get_OpInfoByID( $dbh, $opid ) ;
		if ( isset( $operator["opID"] ) )
		{
			Ops_remove_OpOnlineOfflineLog( $dbh, $opid ) ;

			if ( $operator["status"] )
				Ops_update_PutOpStatus( $dbh, $opid, 1, $operator["mapp"] ) ;
		}

		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action == "update_ping" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$option = Util_Format_Sanatize( Util_Format_GetVar( "option" ), "ln" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		if ( ( $option == "foot" ) || ( $option == "status" ) )
		{
			$ping = ( isset( $VALS["ping"] ) && $VALS["ping"] ) ? unserialize( $VALS["ping"] ) : Array() ;
			$ping[$option] = ( $value >= 10 ) ? $value : 10 ;
			Util_Vals_WriteToFile( "ping", serialize( $ping ) ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action. [p1]\" };" ;
	}
	else if ( $action == "update_padding" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		if ( is_numeric( $value ) )
		{
			Util_Vals_WriteToFile( "padding_bottom", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid padding value.\" };" ;
	}
	else if ( $action == "update_radius" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

		if ( is_numeric( $value ) )
		{
			Util_Vals_WriteToFile( "border_radius", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid radius value.\" };" ;
	}
	else if ( $action == "update_style" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "lns" ) ;

		if ( ( $value == "classic" ) || ( $value == "modern" ) )
		{
			Util_Vals_WriteToFile( "STYLE", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid style value.\" };" ;
	}
	else if ( $action == "display_order" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$opids = Util_Format_Sanatize( Util_Format_GetVar( "o" ), "a" ) ;
		$displays = Util_Format_Sanatize( Util_Format_GetVar( "ds" ), "a" ) ;

		for ( $c = 0; $c < count( $opids ); ++$c )
		{
			$opid = Util_Format_Sanatize( $opids[$c], "n" ) ;
			$display = isset( $displays[$c] ) ? Util_Format_Sanatize( $displays[$c], "n" ) : -1 ;

			if ( $opid && ( $display != -1 ) )
			{
				Ops_update_OpDeptDisplay( $dbh, $deptid, $opid, $display ) ;
			}
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action == "update_dept_noicon" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;
		$no_chat_icons = ( isset( $VALS['NO_CHAT_ICONS'] ) && $VALS['NO_CHAT_ICONS'] ) ? unserialize( $VALS['NO_CHAT_ICONS'] ) : Array() ;

		if ( !$value && isset( $no_chat_icons[$deptid] ) ) { unset( $no_chat_icons[$deptid] ) ; }
		else if ( $value ) { $no_chat_icons[$deptid] = 1 ; }

		Util_Vals_WriteToFile( "NO_CHAT_ICONS", serialize( $no_chat_icons ) ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action == "can_cats" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/ajax/inc_cats.php" ) ;;
	}
	else
		$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action. [a2]\" };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
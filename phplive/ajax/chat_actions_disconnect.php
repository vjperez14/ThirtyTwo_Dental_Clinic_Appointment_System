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

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$ip = Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "ln" ) ;

	if ( $action === "disconnect" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/update.php" ) ;

		$isop = Util_Format_Sanatize( Util_Format_GetVar( "isop" ), "n" ) ;
		$isop_ = Util_Format_Sanatize( Util_Format_GetVar( "isop_" ), "n" ) ;
		$isop__ = Util_Format_Sanatize( Util_Format_GetVar( "isop__" ), "n" ) ;
		$bid = Util_Format_Sanatize( Util_Format_GetVar( "b" ), "n" ) ;
		$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "lns" ) ;
		$token = Util_Format_Sanatize( Util_Format_GetVar( "token" ), "ln" ) ;
		$vis_token = Util_Format_Sanatize( Util_Format_GetVar( "vis_token" ), "lns" ) ;
		$vclick = Util_Format_Sanatize( Util_Format_GetVar( "vclick" ), "n" ) ;

		if ( !$vis_token ) { LIST( $ip, $vis_token ) = Util_IP_GetIP( $token ) ; }

		$requestinfo = Chat_get_itr_RequestCesInfo( $dbh, $ces ) ;
		if ( isset( $requestinfo["requestID"] ) && ( $requestinfo["status"] || $requestinfo["initiated"] ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put_itr.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/get.php" ) ;

			$lang = $CONF["lang"] ;
			$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo["deptID"] ) ;
			$deptvars = Depts_get_DeptVars( $dbh, $requestinfo["deptID"] ) ;
			$group_chat = ( $requestinfo["md5_vis_"] == "grc" ) ? 1 : 0 ; $group_chat_md5_vis = "" ;
			if ( $deptinfo["lang"] ) { $lang = $deptinfo["lang"] ; }
			include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($lang, "ln").".php" ) ;
			$LANG_DB = Lang_get_Lang( $dbh, $requestinfo["deptID"] ) ;
			if ( isset( $LANG_DB["deptID"] ) && $LANG_DB["deptID"] )
			{
				$db_lang_hash = unserialize( $LANG_DB["lang_vars"] ) ;
				$LANG = array_merge( $LANG, $db_lang_hash ) ;
			}

			if ( isset( $deptinfo["smtp"] ) && $deptinfo["smtp"] )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;
				$smtp_array = unserialize( Util_Functions_itr_Decrypt( $CONF["SALT"], $deptinfo["smtp"] ) ) ;
			}

			if ( $isop )
			{
				if ( $group_chat )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

					$opinfo = Ops_get_OpInfoByID( $dbh, $isop ) ;
					$op_name = isset( $opinfo["name"] ) ? $opinfo["name"] : "Operator ???" ;
					$text = "<div class='cl'>$op_name has left the chat.</div>" ;
					$group_chat_md5_vis = trim( preg_replace( "/-{$isop}-/", "", $requestinfo["md5_vis"] ) ) ;

					// if the group chat starter ends the chat, group chat ends (fixes possible situations as well)
					if ( $requestinfo["opID"] == $isop )
					{
						$group_chat_md5_vis = "" ;
						$text = "<div class='cl'><disconnected><d1>".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_ODISCONNECT"] ) )."</div>" ;
					}
					Chat_update_RequestValue( $dbh, $requestinfo["requestID"], "md5_vis", $group_chat_md5_vis ) ;
				}
				else
				{
					$text = "<div class='cl'><disconnected><d1>".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_ODISCONNECT"] ) )."</div>" ;
					Chat_update_RequestLogValue( $dbh, $ces, "disc", 1 ) ;
				}

				UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;
			}
			else
			{
				$text = "<div class='cl'><disconnected><d3>".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_VDISCONNECT"] ) )."</div>" ;
				UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;
				Chat_update_RequestLogValue( $dbh, $ces, "disc", 2 ) ;
			}

			if ( !$requestinfo["initiated"] || ( $requestinfo["initiated"] && $requestinfo["status"] ) )
			{
				$output = UtilChat_ExportChat( "{$ces}.txt" ) ;
				if ( is_array( $output ) && isset( $output[1][0] ) )
				{
					$formatted = $output[1][0] ; $plain = $output[1][1] ;
					$fsize = strlen( $formatted ) ;
					$vis_token = ( $requestinfo["md5_vis"] ) ? $requestinfo["md5_vis"] : $vis_token ;
					
					$custom_string = "" ;
					$customs = explode( "-cus-", rawurldecode( $requestinfo["custom"] ) ) ;
					for ( $c = 0; $c < count( $customs ); ++$c )
					{
						$custom_var = $customs[$c] ;
						if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
						{
							LIST( $cus_name, $cus_var ) = explode( "-_-", $custom_var ) ;
							if ( $cus_var ) { $custom_string .= $cus_name.": ".$cus_var."\r\n" ; }
						}
					}
					if ( !$group_chat || ( $group_chat && !$group_chat_md5_vis ) )
					{
						if ( Chat_put_itr_Transcript( $dbh, $ces, $requestinfo["status"], $requestinfo["created"], $now, $requestinfo["deptID"], $requestinfo["opID"], $requestinfo["initiated"], $requestinfo["op2op"], 0, $fsize, $requestinfo["vname"], $requestinfo["vemail"], $requestinfo["ip"], $vis_token, $custom_string, $requestinfo["question"], $formatted, $plain, $deptinfo, $deptvars ) )
						{
							Chat_remove_Request( $dbh, $requestinfo["requestID"] ) ;
							Chat_update_RecentChat( $dbh, $requestinfo["opID"], $ces, 0 ) ;
						}
					}
				}
			}
			else if ( $requestinfo["initiated"] || $requestinfo["status"] )
				Chat_remove_Request( $dbh, $requestinfo["requestID"] ) ;
		}
		else if ( isset( $requestinfo["requestID"] ) && !$requestinfo["status"] )
		{
			if ( $isop && ( $requestinfo["opID"] != $isop ) )
			{
				if ( $requestinfo["op2op"] )
				{
					LIST( $ces ) = database_mysql_quote( $dbh, $requestinfo["ces"] ) ;
					$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
					database_mysql_query( $dbh, $query ) ;
				}
			}
			else
			{
				LIST( $ces ) = database_mysql_quote( $dbh, $requestinfo["ces"] ) ;
				$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
				database_mysql_query( $dbh, $query ) ;
			}
		}
		else
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/get.php" ) ;

			$queueinfo = Queue_get_InfoByCes( $dbh, $ces ) ;
			if ( isset( $queueinfo["ces"] ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/remove.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;
				Queue_remove_Queue( $dbh, $queueinfo["ces"] ) ;
			}
		}

		if ( $ces ) { clear_istyping( $ces ) ; }
		Footprints_update_FootprintUniqueValue( $dbh, $vis_token, "chatting", 0 ) ;

		$json_data = "json_data = { \"status\": 1, \"ces\": \"$ces\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;

	function clear_istyping( $ces )
	{
		global $CONF ;
		if ( $ces )
		{
			$dir_files = glob( $CONF["TYPE_IO_DIR"]."/$ces"."*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
				}
			}
		}
	}
?>
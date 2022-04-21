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
	else if ( $action === "accept" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_itr.php" ) ;

		$requestid = Util_Format_Sanatize( Util_Format_GetVar( "requestid" ), "n" ) ;
		$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "lns" ) ;
		$op_now = Util_Format_Sanatize( Util_Format_GetVar( "now" ), "n" ) ;
		$tooslow = 0 ;

		// unlink to make sure because accept always has Mobile App opened
		if ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) )
		{
			@unlink( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) ;
		}

		$requestinfo = Chat_get_itr_RequestCesInfo( $dbh, $ces ) ;
		if ( !isset( $requestinfo["status"] ) || ( $requestinfo["vupdated"] == 1 ) || ( ( $requestinfo["vupdated"] < ( $now - $VARS_EXPIRED_REQS ) ) && !$requestinfo["op2op"] ) || ( $requestinfo["status"] && ( $requestinfo["opID"] != $opid ) ) )
			$tooslow = 1 ;
		else if ( ( $requestinfo["status"] != 2 ) && ( $opid != $requestinfo["opID"] ) && ( $requestinfo["opID"] != 1111111111 ) )
			$tooslow = 1 ;
		else
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/get.php" ) ;

			$opinfo = ( $requestinfo["opID"] != 1111111111 ) ? Ops_get_OpInfoByID( $dbh, $requestinfo["opID"] ) : Ops_get_OpInfoByID( $dbh, $opid ) ;
			if ( Chat_update_AcceptChat( $dbh, $requestinfo["requestID"], $opid, $requestinfo["status"], $requestinfo["op2op"] ) )
			{
				if ( $requestinfo["md5_vis"] == "op2op" )
					Chat_update_RequestLogValue( $dbh, $ces, "status", 1 ) ;
				else
				{
					// function Chat_update_itr_RouteChat updates the log opID during routing
					// but double set to ensure it gets updated here (because direct op transfer does not route)
					Chat_update_RequestLogValues( $dbh, $ces, "status", 1, "opID", $opid ) ;
				}

				$lang = $CONF["lang"] ;
				$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo["deptID"] ) ;
				if ( $deptinfo["lang"] ) { $lang = $deptinfo["lang"] ; }
				include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($lang, "ln").".php" ) ;

				$LANG_DB = Lang_get_Lang( $dbh, $requestinfo["deptID"] ) ;
				if ( isset( $LANG_DB["lang_vars"] ) && $LANG_DB["lang_vars"] )
				{
					$db_lang_hash = unserialize( $LANG_DB["lang_vars"] ) ;
					$LANG = array_merge( $LANG, $db_lang_hash ) ;
				}

				// delete the transfer flag file (created at ops/inc_chat_transfer.php)
				$filename_declined = $ces."-de.text" ;
				if ( is_file( "$CONF[CHAT_IO_DIR]/$filename_declined" ) )
					@unlink( "$CONF[CHAT_IO_DIR]/$filename_declined" ) ;

				// if transferred to operator, keep the same created time (status 2 is transferred) and skip stat incro
				if ( ( $requestinfo["status"] != 2 ) && ( $requestinfo["tupdated"] != 2 ) && ( $requestinfo["md5_vis"] != "op2op" ) )
				{
					// only new requests affect the lastrequest.  transferred chats should not break the round-robin
					Ops_update_OpValue( $dbh, $opid, "lastrequest", $now ) ;

					// the requst log table p_req_log will contain the exact time the chat was created
					// p_requests needs to use time of accept to calculate duration and for the chat timer
					Chat_update_RequestValue( $dbh, $requestid, "created", $now ) ; // update request created for chat timer

					if ( !$requestinfo["initiated"] )
					{
						Chat_update_RstatsLogValue( $dbh, $ces, $opid, "status", 1 ) ;
						Ops_put_itr_OpReqStat( $dbh, $requestinfo["deptID"], $opid, "taken", 1 ) ;
					}
					if ( $requestinfo["opID"] == 1111111111 )
					{
						Chat_update_RequestValue( $dbh, $requestid, "opID", $opid ) ;
					}

					if ( !$requestinfo["initiated"] )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
						$requestinfo_log = Chat_get_RequestHistCesInfo( $dbh, $ces ) ; // due to queue need to fetch log value
						if ( isset( $requestinfo_log["created"] ) )
						{
							$accepted_dept = $now-$requestinfo_log["created"] ; if ( $accepted_dept <= 0 ) { $accepted_dept = 1 ; }
							$accepted_op = $now-$op_now ; if ( $accepted_op <= 0 ) { $accepted_op = 1 ; }
							Chat_update_RequestLogValues( $dbh, $ces, "accepted", $accepted_dept, "accepted_op", $accepted_op ) ;
						}
					}

					$text = "<div class='ca'><b>$opinfo[name]</b> ".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_JOINED"] ) )."</div>" ;
					UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;

					// ajax/chat_actions_op_transfer.php to indicate transfer to dept for "joined" text due to non-refresh of chat
					$flag_file = $ces."-0_trans.text" ;
					if ( is_file( "$CONF[CHAT_IO_DIR]/$flag_file" ) )
					{
						@unlink( "$CONF[CHAT_IO_DIR]/$flag_file" ) ;
					}
					else if ( $requestinfo["op2op"] && ( $requestinfo["status"] != 2 ) )
					{
						//
					}
					else if ( $requestinfo["opID"] && ( $requestinfo["opID"] != 1111111111 ) )
					{
						// when the chat transcript was transferred back to original operator
					}

					include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/update.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/remove.php" ) ;
					Queue_update_QueueLogValueByCes( $dbh, $ces, "status", 1 ) ;
					Queue_remove_Queue( $dbh, $ces ) ;
				}
				else
				{
					// reset the op2op as it was used for the original opID for transfer back (if not op2op chat)
					if ( $requestinfo["md5_vis"] != "op2op" )
					{
						Chat_update_RequestValue( $dbh, $requestid, "op2op", 0 ) ;
						Ops_put_itr_OpReqStat( $dbh, $requestinfo["deptID"], $opid, "transfer_a", 1 ) ;
					}

					$text = "<div class='ca'><b>$opinfo[name]</b> ".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_JOINED"] ) )."</div>" ;
					UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;
				}
			}
			else
				$tooslow = 1 ;
		}
		if ( $tooslow ) { $json_data = "json_data = { \"status\": 1, \"tooslow\": 1 };" ; }
		else { $json_data = "json_data = { \"status\": 1, \"tooslow\": 0 };" ; }
	}
	else { $json_data = "json_data = { \"status\": 0 };" ; }

	if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
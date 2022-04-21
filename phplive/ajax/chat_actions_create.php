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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;
	$token = Util_Format_Sanatize( Util_Format_GetVar( "token" ), "ln" ) ;
	$token_ces = Util_Format_Sanatize( Util_Format_GetVar( "token_ces" ), "ln" ) ;

	if ( $action == "create" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;

		LIST( $ip, $vis_token ) = Util_IP_GetIP( $token ) ;
		$token_ces_ = md5( "$ces$CONF[SALT]" ) ;
		
		if ( $token_ces == $token_ces_ )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

			$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
			$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "lns" ) ;
			$auto_pop = Util_Format_Sanatize( Util_Format_GetVar( "auto_pop" ), "n" ) ;
			$vname = Util_Format_Sanatize( Util_Format_GetVar( "vname" ), "ln" ) ; $vname_orig = $vname ;
			$vemail = Util_Format_Sanatize( Util_Format_GetVar( "vemail" ), "e" ) ; $vemail = ( !$vemail ) ? "null" : $vemail ;
			$vsubject = rawurldecode( Util_Format_Sanatize( Util_Format_GetVar( "vsubject" ), "htmltags" ) ) ;
			$question = Util_Format_Sanatize( Util_Format_GetVar( "vquestion" ), "htmltags" ) ; $question = ( $question ) ? $question : "" ; // for number 0 situations
			$onpage = rawurldecode( Util_Format_Sanatize( Util_Format_GetVar( "onpage" ), "url" ) ) ; $onpage = ( $onpage ) ? $onpage : "" ;
			$refer = rawurldecode( Util_Format_Sanatize( Util_Format_GetVar( "refer" ), "url" ) ) ; $refer = ( $refer ) ? $refer : "" ;
			$title = Util_Format_Sanatize( Util_Format_GetVar( "title" ), "title" ) ; $title = ( $title ) ? $title : "" ;
			$resolution = Util_Format_Sanatize( Util_Format_GetVar( "win_dim" ), "ln" ) ;
			$rtype = Util_Format_Sanatize( Util_Format_GetVar( "rtype" ), "n" ) ;
			$rstring = Util_Format_Sanatize( Util_Format_GetVar( "rstring" ), "ln" ) ;
			$embed = Util_Format_Sanatize( Util_Format_GetVar( "embed" ), "n" ) ;
			$marketid = Util_Format_Sanatize( Util_Format_GetVar( "marketid" ), "n" ) ;
			$proto = Util_Format_Sanatize( Util_Format_GetVar( "proto" ), "n" ) ;
			$custom = Util_Format_Sanatize( Util_Format_GetVar( "custom" ), "htmltags" ) ;
			$peer_support = Util_Format_Sanatize( Util_Format_GetVar( "prs" ), "n" ) ;
			$bid = Util_Format_Sanatize( Util_Format_GetVar( "b" ), "n" ) ;
			$sim_ops = "" ; $vis_token_embed = ( $embed ) ? $vis_token : "" ;
			$status = 0 ; // initital status of chat request
			$was_inqueue = 0 ;
			$bname = "" ; // bot name

			$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
			LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
			$mobile = ( $os == 5 ) ? 1 : 0 ;

			$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
			if ( isset( $deptinfo["deptID"] ) &&  ( $vname != "null" ) )
			{
				$opid = 0 ;
				if ( $rtype < 3 )
				{
					// just creat chat session
					// let routing script (ajax/chat_routing.php) do the processings
					include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/get.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/update.php" ) ;
					Queue_update_QueueValueByCes( $dbh, $ces, "updated", $now+($VARS_JS_REQUESTING*$VARS_CYCLE_CLEAN_Q) ) ; // extra buffer to ensure it stays in queue
					$queuelog_info = Queue_get_QueueInfoLog( $dbh, $ces ) ;
					if ( isset( $queuelog_info["ces"] ) )
					{
						$was_inqueue = ( !$queuelog_info["ended"] ) ? Queue_update_QueueLogValueByCes( $dbh, $ces, "ended", time() ) : 1 ;
					}
					if ( $was_inqueue )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
						$requestinfo = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;
						if ( isset( $requestinfo["ces"] ) )
						{
							$onpage = $requestinfo["onpage"] ;
							$title = $requestinfo["title"] ;
							$custom = $requestinfo["custom"] ;
							$resolution = $requestinfo["resolution"] ;
						}
					}
					if ( $bid ) { $opid = $bid ; $status = 1 ; }
				}
				else
				{
					if ( $deptinfo["lang"] ) { $CONF["lang"] = $deptinfo["lang"] ; }
					if ( $bid ) { $opid = $bid ; $status = 1 ; }
					else
					{
						$opid = 1111111111 ;
						$opinfo_next = Array( "rate" => 0, "sms" => 0 ) ;
						$sim_operators = Depts_get_DeptOps( $dbh, $deptid, 1 ) ;
						$total_sim_ops = count( $sim_operators ) ;
						for ( $c = 0; $c < $total_sim_ops; ++$c )
						{
							$operator = $sim_operators[$c] ;
							$sim_opid = $operator["opID"] ;
							$sim_ops .= "$sim_opid-" ;
						}
					}
				}

				// need to create it before DB entry just in case requesting queries it fast before actual appending happens
				if ( !is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ) { touch( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ; }
				LIST( $requestid, $request_country ) = Chat_put_Request( $dbh, $deptid, $opid, $status, 0, 0, $os, $browser, $ces, $resolution, $vname, $vemail, $ip, $vis_token_embed, $vis_token, $onpage, $title, $question, $peer_support, $marketid, $refer, $rstring, $custom, $auto_pop, $sim_ops ) ;
				if ( isset( $requestid ) && is_numeric( $requestid ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/put.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/update.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;

					/*******************************************/
					// make an entry just in case they close chat before routing or internet situations
					if ( !$bid ) { Chat_put_RstatsLog( $dbh, $ces, 0, $deptid, 0 ) ; }
					if ( !$bid && !$was_inqueue ) { Ops_put_itr_OpReqStat( $dbh, $deptid, 0, "requests", 1 ) ; }
					/*******************************************/

					Chat_put_ReqLog( $dbh, $requestid ) ;

					IPs_put_IP( $dbh, $ip, $vis_token, $deptid, 0, 1, 0, 1, 0, 0, true ) ;
					Footprints_update_FootprintUniqueValues( $dbh, $vis_token, "requests", "+1", "chatting", 1 ) ;
					$op_inactive = $now - 90 ;
					$op_inactive_mapp_processed = 0 ;
					$json_extra = "" ;

					if ( $sim_ops )
					{
						for ( $c = 0; $c < $total_sim_ops; ++$c )
						{
							$operator = $sim_operators[$c] ;
							$sim_opid = $operator["opID"] ;

							if ( !$bid ) { Chat_put_RstatsLog( $dbh, $ces, 0, $deptid, $operator["opID"] ) ; }
							if ( !$bid ) { Ops_put_itr_OpReqStat( $dbh, 0, $operator["opID"], "requests", 1 ) ; }

							if ( $operator["mapp"] && ( $operator["lastactive"] < $op_inactive ) && !is_file( "$CONF[TYPE_IO_DIR]/$sim_opid.mapp" ) )
							{
								$op_inactive_mapp_processed = 1 ;
								touch( "$CONF[TYPE_IO_DIR]/$sim_opid.mapp" ) ;
							}
							if ( $op_inactive_mapp_processed || ( is_file( "$CONF[TYPE_IO_DIR]/$sim_opid.mapp" ) || ( !$operator["mapp"] && ( $operator["sms"] == 1 ) ) ) )
							{
								$inc_question = $question ;
								$mapp_opid = $sim_opid ;
								$sms_oname = $operator["name"] ; $sms_oemail = $operator["email"] ;
								$sms_vname = rawurlencode( $vname ) ; $sms_num = $operator["smsnum"] ;
								include( "$CONF[DOCUMENT_ROOT]/ajax/inc_request_sms.php" ) ;
							}
						}
					}
					else
					{
						if ( $bid )
						{
							include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_ext.php" ) ;
							$opinfo_next = Ops_get_ext_OpInfoByLogin( $dbh, "phplivebot" ) ; $profile_src = "" ;
							$bname = $opinfo_next["name"] ;
							if ( $opinfo_next["pic"] )
							{
								if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
								else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
								$profile_src = Util_Upload_GetLogo( "profile", $opinfo_next["opID"] ) ;
							}
							$json_extra = ", \"opid\": $bid, \"status_request\": $status, \"name\": \"$opinfo_next[name]\", \"mapp\": 0, \"rate\": $opinfo_next[rate], \"profile\": \"$profile_src\" " ;
						}
						else
						{
							include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
							$opinfo_next = Ops_get_NextRequestOp( $dbh, 0, $deptid, $rtype, $rstring, 0 ) ;
						}

						if ( isset( $opinfo_next ) && isset( $opinfo_next["mapp"] ) && $opinfo_next["mapp"] )
						{
							$mapp_opid = $opinfo_next["opID"] ;
							if ( ( $opinfo_next["lastactive"] < $op_inactive ) && !is_file( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) )
								touch( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) ;
						}
					}

					$text = ( $question ) ? "<div class='ca'><i>".$question."</i></div>" : "<div></div>" ;

					if ( $bid && is_file( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/phplivebot.php" ) )
					{
						if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
						include_once( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/inc_chat_create.php" ) ;
					}

					// need to check file exists and size due to queue process may not have deleted the file.  limits double question for few situations
					if ( !is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) || ( filesize( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) == 0 ) )
						UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;

					$json_data = "json_data = { \"status\": 1, \"requestid\": $requestid $json_extra };" ;
				}
				else
				{
					$json_data = "json_data = { \"status\": 0, \"error\": \"System error [".Util_Format_StripQuotes( $dbh["error"] )."].  Please close the chat window and try again.\" };" ;
				}
			}
			else { $json_data = "json_data = { \"status\": 0, \"error\": \"Invalid request [d].  Please close the chat window and try again.\" };" ; }
		}
		else { $json_data = "json_data = { \"status\": 0, \"error\": \"Invalid request [s].  Please close the chat window and try again.\" };" ; }
	}
	else { $json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action.  Please close the chat window and try again.\" };" ; }

	if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
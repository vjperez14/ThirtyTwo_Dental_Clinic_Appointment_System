<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	/*
	// status json route: -1 no request, 0 same op route, 1 request accepted, 2 new op route, 10 leave a message
	*/
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "a" ), "ln" ) ;

	if ( $action === "routing" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/get.php" ) ;

		$ces = Util_Format_Sanatize( Util_Format_GetVar( "c" ), "ln" ) ;
		$opid_direct = Util_Format_Sanatize( Util_Format_GetVar( "o" ), "n" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
		$c_routing = Util_Format_Sanatize( Util_Format_GetVar( "cr" ), "n" ) ;
		$rtype = Util_Format_Sanatize( Util_Format_GetVar( "r" ), "n" ) ;
		$rtime = Util_Format_Sanatize( Util_Format_GetVar( "rt" ), "n" ) ;
		$lang = Util_Format_Sanatize( Util_Format_GetVar( "lg" ), "ln" ) ;
		$queue = Util_Format_Sanatize( Util_Format_GetVar( "q" ), "n" ) ;
		$proto = Util_Format_Sanatize( Util_Format_GetVar( "pr" ), "n" ) ;
		$q_ops_online = "" ;

		$queueinfo = Queue_get_InfoByCes( $dbh, $ces ) ;
		$inqueue = isset( $queueinfo["ces"] ) ? 1 : 0 ;
		$requestinfo = Chat_get_itr_RequestCesInfo( $dbh, $ces ) ;
		if ( !isset( $requestinfo["requestID"] ) )
		{
			if ( $inqueue )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;
				// get all the ops online for comparison
				$q_ops_array = Ops_get_itr_OpsOnlineIDs( $dbh, $deptid ) ;
				$q_ops_online = ( is_array( $q_ops_array ) ) ? preg_replace( "/ /", "", implode( ",", $q_ops_array ) ) : "" ;
			}
			$json_data = "json_data = { \"status\": 10, \"q_ops\": \"$q_ops_online\" };" ;
		}
		else
		{
			if ( ( ( $requestinfo["status"] == 2 ) && !$requestinfo["tupdated"] ) || $requestinfo["tloop"] )
			{
				if ( !$requestinfo["tloop"] )
				{
					// transfer operator was not available, route to original operator
					include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

					$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo["deptID"] ) ;
					$rtime = $now+$deptinfo["rtime"] ;
					$query = "UPDATE p_requests SET tupdated = $rtime, status = 2, vupdated = $now, opID = $requestinfo[op2op], op2op = $requestinfo[opID], tloop = 1 WHERE ces = '$requestinfo[ces]'" ;
					database_mysql_query( $dbh, $query ) ;
					$json_data = "json_data = { \"status\": 2, \"opid\": $requestinfo[op2op], \"rtime\": $deptinfo[rtime] };" ;
				}
				else if ( $requestinfo["tloop"] && ( $requestinfo["tupdated"] > $now ) )
				{
					$json_data = "json_data = { \"status\": 2, \"opid\": $requestinfo[op2op] };" ;
				}
				else
				{
					$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
					database_mysql_query( $dbh, $query ) ;
					$json_data = "json_data = { \"status\": 11 };" ;
				}
			}
			else if ( $requestinfo["status"] && ( $requestinfo["opID"] != 1111111111 ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
				$opinfo = Ops_get_OpInfoByID( $dbh, $requestinfo["opID"] ) ; $profile_src = "" ;
				if ( isset( $opinfo["opID"] ) )
				{
					if ( $opinfo["pic"] )
					{
						if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
						else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
						$profile_src = Util_Upload_GetLogo( "profile", $opinfo["opID"] ) ;
					}
					$bid = ( $opinfo["login"] == "phplivebot" ) ? $opinfo["opID"] : 0 ;
					$json_data = "json_data = { \"status\": 1, \"status_request\": $requestinfo[status], \"requestid\": $requestinfo[requestID], \"initiated\": $requestinfo[initiated], \"name\": \"$opinfo[name]\", \"rate\": $opinfo[rate], \"deptid\": $deptid, \"opid\": $opinfo[opID], \"email\": \"$opinfo[email]\", \"profile\": \"$profile_src\", \"bid\": $bid, \"mapp\": \"$opinfo[mapp]\" };" ;
				}
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid operator ID.\" }" ;
			}
			else
			{
				if ( $inqueue )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/update.php" ) ;
					Queue_update_QueueValueByCes( $dbh, $ces, "updated", $now ) ;
				}

				// vupdated is used for routing UNTIL chat is accepted then it is used
				// for visitor's callback updated time
				if ( !$requestinfo["opID"] )
					$rupdated = $requestinfo["vupdated"] - ($rtime * 2) ; // new chat start routing immediately
				else
					$rupdated = $requestinfo["vupdated"] + $rtime ;

				if ( $now <= $rupdated )
				{
					$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
					$mapp_opid = $requestinfo["opID"] ;
					if ( $mapp_opid && ( ( isset( $mapp_array[$mapp_opid] ) && isset( $mapp_array[$mapp_opid]["r"] ) && $mapp_array[$mapp_opid]["r"] && is_file( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) ) || ( $mapp_opid == 1111111111 ) ) )
					{
						if ( $mapp_opid == 1111111111 ) { $c_routing += 1 ; } // add one so it does not process duplicate push
						$process = ( $c_routing % 4 ) ? 0 : 1 ;
						if ( !$inqueue && $process )
						{
							$mapp_route_updated = 1 ; // start off with 1 to process
							if ( is_file( "$CONF[TYPE_IO_DIR]/mapp_{$ces}.route" ) ) { $mapp_route_updated = filemtime( "$CONF[TYPE_IO_DIR]/mapp_{$ces}.route" ) ; }
							if ( $mapp_route_updated < ( $now - ( $VARS_JS_REQUESTING * 3 ) ) )
							{
								$sms_oname = $sms_oemail = $sms_vname = $sms_num = "" ;
								$inc_question = "\xF0\x9F\x94\x81 ".$requestinfo["question"] ;
								include_once( "$CONF[DOCUMENT_ROOT]/ajax/inc_request_sms.php" ) ;
								touch( "$CONF[TYPE_IO_DIR]/mapp_{$ces}.route" ) ;
							}
						}
					}
					$json_data = "json_data = { \"status\": 0 };" ;
				}
				else
				{
					if ( $requestinfo["opID"] == 1111111111 )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;

						$sim_ops = Util_Format_ExplodeString( "-", $requestinfo["sim_ops"] ) ;
						$sim_ops_ = Util_Format_ExplodeString( "-", $requestinfo["sim_ops_"] ) ;
						for ( $c = 0; $c < count( $sim_ops ); ++$c )
						{
							$found = 0 ;
							for ( $c2 = 0; $c2 < count( $sim_ops_ ); ++$c2 )
							{
								if ( $sim_ops[$c] == $sim_ops_[$c2] )
									$found = 1 ;
							}
							if ( !$found )
							{
								Ops_put_itr_OpReqStat( $dbh, $requestinfo["deptID"], $sim_ops[$c], "declined", 1 ) ;
							}
						}

						// leave a message
						LIST( $ces ) = database_mysql_quote( $dbh, $requestinfo["ces"] ) ;
						$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
						database_mysql_query( $dbh, $query ) ;
						$json_data = "json_data = { \"status\": 12 };" ;
					}
					else
					{
						include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update_itr.php" ) ;
						include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
						include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;

						if ( ( $requestinfo["tupdated"] != 2 ) && ( $requestinfo["status"] != 2 ) && !$requestinfo["op2op"] && $requestinfo["opID"] )
						{
							if ( !$inqueue ) { Ops_put_itr_OpReqStat( $dbh, $deptid, $requestinfo["opID"], "declined", 1 ) ; }
							else if ( isset( $queueinfo["ces"] ) )
							{
								$ops_d = trim( $queueinfo["ops_d"] ) ;
								$temp = $requestinfo["opID"]."," ;
								if ( !preg_match( "/(^|,)$temp/", $ops_d ) )
								{
									Ops_put_itr_OpReqStat( $dbh, $deptid, $requestinfo["opID"], "declined", 1 ) ;
								}
							}
						}

						$opinfo_next = Ops_get_NextRequestOp( $dbh, $opid_direct, $deptid, $rtype, $requestinfo["rstring"], $requestinfo["tupdated"] ) ;
						if ( isset( $opinfo_next["opID"] ) )
						{
							$opid = $opinfo_next["opID"] ;
							Chat_update_itr_RouteChat( $dbh, $requestinfo["requestID"], $requestinfo["ces"], $opinfo_next["opID"], $opinfo_next["sms"],  "$opinfo_next[opID],$requestinfo[rstring]" ) ;

							if ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) || ( !$opinfo_next["mapp"] && ( $opinfo_next["sms"] == 1 ) ) )
							{
								$inc_question = $requestinfo["question"] ;
								$mapp_opid = $opid ;
								$sms_oname = $opinfo_next["name"] ; $sms_oemail = $opinfo_next["email"] ;
								$sms_vname = $requestinfo["vname"] ; $sms_num = $opinfo_next["smsnum"] ;
								include_once( "$CONF[DOCUMENT_ROOT]/ajax/inc_request_sms.php" ) ;
							}

							// don't log trasfer chats on total stats of requests
							if ( ( $requestinfo["tupdated"] != 2 ) && ( $requestinfo["status"] != 2 ) && !$requestinfo["op2op"] )
							{
								include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put.php" ) ;
								if ( !$inqueue )
								{
									if ( !$c_routing )
									{
										// skip dept logging if start of routing (opID is zero) because dept logging happens
										// at time of chat creation (chat_actions_create.php).  if logged again, it will
										// cause duplicate logging (2 requests total for 1 chat request)
										$skip_dept = ( !$requestinfo["opID"] ) ? 1 : 0 ;
										Ops_put_itr_OpReqStat( $dbh, $deptid, $opinfo_next["opID"], "requests", 1, $skip_dept ) ;
									}
									else
									{
										Ops_put_itr_OpReqStat( $dbh, 0, $opinfo_next["opID"], "requests", 1 ) ;
									}
									Chat_put_RstatsLog( $dbh, $requestinfo["ces"], 0, $deptid, $opinfo_next["opID"] ) ;
								}
								else if ( isset( $queueinfo["ces"] ) )
								{
									$ops_d = trim( $queueinfo["ops_d"] ) ;
									$temp = $opinfo_next["opID"]."," ;
									if ( !preg_match( "/(^|,)$temp/", $ops_d ) )
									{
										Ops_put_itr_OpReqStat( $dbh, 0, $opinfo_next["opID"], "requests", 1 ) ;
										Chat_put_RstatsLog( $dbh, $requestinfo["ces"], 0, $deptid, $opinfo_next["opID"] ) ;
									}
								}
							}
							if ( ( $requestinfo["tupdated"] == 2 ) && !$requestinfo["op2op"] )
							{
								Ops_put_itr_OpReqStat( $dbh, 0, $opinfo_next["opID"], "transfer", 1 ) ;
							} $json_data = "json_data = { \"status\": 2, \"opid\": $opinfo_next[opID], \"q_ops\": \"$opinfo_next[q_ops]\" };" ;
						}
						else
						{
							include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put.php" ) ;
							include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/update.php" ) ;
							include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;

							// get all the ops online for comparison
							$q_ops_array = Ops_get_itr_OpsOnlineIDs( $dbh, $deptid ) ;
							$q_ops_online = ( is_array( $q_ops_array ) ) ? preg_replace( "/ /", "", implode( ",", $q_ops_array ) ) : "" ;

							if ( $requestinfo["tupdated"] == 2 )
							{
								include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
								$text = "<x-nod><restart_router><div class='ca'>Department was not available to accept the chat transfer.</div>" ;
								UtilChat_AppendToChatfile( $requestinfo["ces"].".txt", base64_encode( $text ) ) ; usleep( 250000 ) ;
							}

							LIST( $ces ) = database_mysql_quote( $dbh, $requestinfo["ces"] ) ;
							$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
							database_mysql_query( $dbh, $query ) ;
							if ( $requestinfo["opID"] ) { Queue_update_OpDeclined( $dbh, $requestinfo["ces"], $requestinfo["opID"] ) ; }
							$json_data = "json_data = { \"status\": 13, \"q_ops\": \"$q_ops_online\" };" ;
						}
					}
				}
			}
		}
	}

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	error_reporting(0) ;
	/* status DB request: -1 ended by action taken, 0 waiting pick-up, 1 picked up, 2 transfer */
	$microtime = ( function_exists( "gettimeofday" ) ) ? 1 : 0 ;
	$process_start = ( $microtime ) ? microtime(true) : time() ;
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "a" ), "ln" ) ;
	$proto = Util_Format_Sanatize( Util_Format_GetVar( "pr" ), "n" ) ;
	$q_cces = Util_Format_Sanatize( Util_Format_GetVar( "qcc" ), "a" ) ;
	$co = Util_Format_Sanatize( Util_Format_GetVar( "co" ), "ln" ) ;
	$realtime = Util_Format_Sanatize( Util_Format_GetVar( "r" ), "n" ) ;
	$mapp = Util_Format_Sanatize( Util_Format_GetVar( "m" ), "n" ) ;
	$messageid = Util_Format_Sanatize( Util_Format_GetVar( "mid" ), "n" ) ;
	if ( !isset( $CONF['foot_log'] ) ) { $CONF['foot_log'] = "on" ; } if ( !isset( $CONF['icon_check'] ) ) { $CONF['icon_check'] = "on" ; }
	$json_status = 0 ; $json_request = $json_chatting = $json_error = "" ;
	if ( $action === "rq" )
	{
		$opid = isset( $_COOKIE["cO"] ) ? Util_Format_Sanatize( $_COOKIE["cO"], "n" ) : 0 ;
		$ses = isset( $_COOKIE["cS"] ) ? Util_Format_Sanatize( $_COOKIE["cS"], "ln" ) : "invalid_ses" ;
		$ses_console = isset( $_COOKIE["cSC"] ) ? Util_Format_Sanatize( $_COOKIE["cSC"], "ln" ) : "invalid_ses" ;
		$cookie_cs = substr( $ses, 0, 3 ) ; $cs = Util_Format_Sanatize( Util_Format_GetVar( "cs" ), "ln" ) ;

		if ( !$opid || ( $cookie_cs != $cs ) || !is_file( "$CONF[TYPE_IO_DIR]/$opid"."_ses_{$ses}.ses" ) )
			$json_status = -1 ;
		else if ( !$mapp && ( $co != $ses_console ) )
			$json_status = -2 ;
		else
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/get_itr.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

			$console_status = Util_Format_Sanatize( Util_Format_GetVar( "st" ), "n" ) ;
			$c_requesting = Util_Format_Sanatize( Util_Format_GetVar( "cr" ), "n" ) ;
			$traffic = Util_Format_Sanatize( Util_Format_GetVar( "tr" ), "n" ) ;
			$op2op_enabled = Util_Format_Sanatize( Util_Format_GetVar( "oo" ), "n" ) ;
			$q_ces = Util_Format_Sanatize( Util_Format_GetVar( "qc" ), "a" ) ;
			$q_ces_hash = Array() ;

			for ( $c = 0; $c < count( $q_ces ); ++$c ) { $ces = $q_ces[$c] ; $q_ces_hash[$ces] = 1 ; }
			if ( !( $c_requesting % $VARS_CYCLE_CLEAN ) )
			{
				$vars = Util_Format_Get_Vars( $dbh ) ;
				if ( $vars["ts_clean"] <= ( $now - ( $VARS_CYCLE_CLEAN * 2 ) ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove_itr.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/remove_itr.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update_itr.php" ) ;

					Util_Format_Update_TimeStamp( $dbh, "clean", $now ) ;
					Footprints_remove_itr_Expired_U( $dbh ) ;
					Chat_remove_itr_ExpiredOp2OpRequests( $dbh ) ;
					Chat_remove_itr_OldRequests( $dbh ) ;
					Ops_update_itr_IdleOps( $dbh ) ;
				}
			}
			else if ( !( $c_requesting % ($VARS_CYCLE_CLEAN-1) ) )
			{
				$query = "UPDATE p_operators SET lastactive = $now WHERE opID = $opid" ;
				database_mysql_query( $dbh, $query ) ;
				if ( !database_mysql_nresults( $dbh ) && 0 ) { Util_Format_SetCookie( "cS", "invalid", -1, "/", "", $PHPLIVE_SECURE ) ; }
				else
				{
					// 615 indicates request declined and awaiting next operator
					// don't update the updated value.  it will time out and be deleted automatically
					$query = "UPDATE p_requests SET updated = $now WHERE ( opID = $opid OR op2op = $opid OR opID = 1111111111 ) AND ( status = 0 OR status = 1 OR status = 2 ) and vupdated <> 615" ;
					database_mysql_query( $dbh, $query ) ;
				}
			}

			if ( !( $c_requesting % $VARS_CYCLE_CLEAN_Q ) )
			{
				if ( !isset( $vars ) ) { $vars = Util_Format_Get_Vars( $dbh ) ; }
				if ( $vars["ts_queue"] <= ( $now - ( $VARS_JS_REQUESTING * 3 ) ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/remove.php" ) ;

					Util_Format_Update_TimeStamp( $dbh, "queue", $now ) ;
					Queue_remove_ExpiredQueues( $dbh ) ;
				}
			}

			$messenger_string = "" ;
			if ( $op2op_enabled )
			{
				$query = "SELECT messageID FROM p_mboard WHERE messageID > $messageid AND opID <> $opid ORDER BY messageID DESC LIMIT 1" ;
				database_mysql_query( $dbh, $query ) ;
				$data = database_mysql_fetchrow( $dbh ) ;
				if ( isset( $data["messageID"] ) ) { $messenger_string = "\"mgb\": $data[messageID], " ; }
			}

			$total_traffics = ( $traffic ) ? Footprints_get_itr_TotalFootprints_U( $dbh ) : 0 ;
			$query = "SELECT * FROM p_requests WHERE ( opID = $opid OR op2op = $opid OR opID = 1111111111 OR md5_vis LIKE '%-{$opid}-%' ) AND ( status = 0 OR status = 1 OR status = 2 ) ORDER BY created ASC" ;
			database_mysql_query( $dbh, $query ) ;

			$requests_temp = Array() ;
			if ( $dbh[ 'ok' ] )
			{
				while ( $data = database_mysql_fetchrow( $dbh ) )
				{
					$deptid = $data["deptID"] ;
					$online_file = "$CONF[CHAT_IO_DIR]/online_{$deptid}_{$opid}.info" ;

					$process = 1 ;
					if ( ( $data["opID"] == 1111111111 ) && !$data["status"] )
					{
						// for sim routing, need to check req status and if op is online (it will show on their console if not checked)
						// - it will only arrive here chat win was open when online but op went offline before chat start
						// - vis routing may finish department routing duration in this case
						if ( !is_file( $online_file ) ) { $process = 0 ; }
					}
					if ( $process ) { $requests_temp[] = $data ; }
				}
			} $requests = Array() ;
			for ( $c = 0; $c < count( $requests_temp ); ++$c )
			{
				$requestinfo = $requests_temp[$c] ;
				if ( ( $requestinfo["status"] == 2 ) && ( $requestinfo["op2op"] == $opid ) )
				{
					if ( $requestinfo["tupdated"] && ( $requestinfo["tupdated"] < $now ) )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/ops/inc_chat_transfer.php" ) ;
					}
				}
				else if ( ( $requestinfo["status"] == 2 ) && !$requestinfo["tupdated"] && ( $requestinfo["opID"] == $opid ) )
				{
					// visitor must have abanded chat... don't show again on the operator console
					// visitor routing process does the connecting to original operator.  chat will timeout if they don't return
				}
				else
				{
					// sim ops filter for declined
					if ( !preg_match( "/(^|-)($opid-)/", $requestinfo["sim_ops_"] ) ) { $requests[] = $requestinfo ; }
				}
			}
			$json_status = 1 ;
			$json_request = "\"t\": $total_traffics, \"r\": [  " ;
			for ( $c = 0; $c < count( $requests ); ++$c )
			{
				$req = $requests[$c] ;
				$os = $VARS_OS[$req["os"]] ;
				$browser = $VARS_BROWSER[$req["browser"]] ;
				$title = rawurlencode( preg_replace( "/(\")|(%22)/", "&quot;", $req["title"] ) ) ;
				$question = preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", preg_replace( "/(\")|(%22)/", "&quot;", $req["question"] ) ) ;
				$onpage = rawurlencode( preg_replace( "/hphp/i", "http", $req["onpage"] ) ) ;

				$str_snap = ( $mapp ) ? 35 : 40 ;
				$refer_raw = ( function_exists( "mb_check_encoding" ) && mb_check_encoding( $req["refer"], "UTF-8" ) ) ? preg_replace( "/hphp/i", "http", preg_replace( "/(\")|(%22)/", "&quot;", $req["refer"] ) ) : "" ;
				if ( !preg_match( "/^http/i", $refer_raw ) ) { $refer_raw = "" ; }
				$refer_snap = ( strlen( $refer_raw ) > $str_snap ) ? substr( $refer_raw, 0, ($str_snap-5) ) . "..." : $refer_raw ;
				$refer_snap = rawurlencode( preg_replace( "/^((http)|(https)):\/\/(www.)/", "", $refer_snap ) ) ;

				$custom = rawurlencode( $req["custom"] ) ;

				// if status is 2 then it's a transfer chat... keep original visitor name
				if ( ( $req["status"] != 2 ) && $req["op2op"] )
				{
					// query needed to correctly display operator names at both sides (during invite and page reload situations)
					if ( $opid == $req["op2op"] ) { $opinfo = Ops_get_OpInfoByID( $dbh, $req["opID"] ) ; }
					else { $opinfo = Ops_get_OpInfoByID( $dbh, $req["op2op"] ) ; }
					$vname = $opinfo["name"] ; $vemail = $opinfo["email"] ;
				}
				else { $vname = $req["vname"] ; $vemail = $req["vemail"] ; }

				if ( ( $req["status"] == 1 ) && ( $req["opID"] == 1111111111 ) )
				{
					$req["status"] = 0 ;
					$query = "UPDATE p_requests SET status = 0 WHERE requestID = $req[requestID]" ;
					database_mysql_query( $dbh, $query ) ;
				}

				if ( isset( $q_ces_hash[$req["ces"]] ) )
					$json_request .= "{ \"rid\": $req[requestID], \"ces\": \"$req[ces]\", \"did\": $req[deptID], \"status\": $req[status], \"vup\": \"$req[vupdated]\" }," ;
				else
				{
					if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
					$gravatar = "" ;
					$dept_gravatars = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["gravatars"] ) && $VALS_ADDONS["gravatars"] ) ? unserialize( $VALS_ADDONS["gravatars"] ) : Array() ;
					if ( ( !isset( $dept_gravatars[$req["deptID"]] ) || $dept_gravatars[$req["deptID"]] ) && is_file( "$CONF[DOCUMENT_ROOT]/addons/gravatar/API/Util_Gravatar.php" ) )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/addons/gravatar/API/Util_Gravatar.php" ) ;
						$gravatar = ( $vemail && ( $vemail != "null" ) ) ? Util_Gravatar( $vemail, 25 ) : "" ;
					}
					$country = strtolower( $req["country"] ) ;
					$json_request .= "{ \"rid\": $req[requestID], \"ces\": \"$req[ces]\", \"created\": \"$req[created]\", \"now\": \"$now\", \"tupdated\": $req[tupdated], \"did\": $req[deptID], \"opid\": $req[opID], \"op2op\": $req[op2op], \"vname\": \"$vname\", \"status\": $req[status], \"auto_pop\": $req[auto_pop], \"initiated\": $req[initiated], \"os\": \"$os\", \"browser\": \"$browser\", \"requests\": \"$req[requests]\", \"resolution\": \"$req[resolution]\", \"vemail\": \"$vemail\", \"peer\": $req[peer], \"ip\": \"$req[ip]\", \"vis_token\": \"$req[md5_vis_]\", \"onpage\": \"$onpage\", \"title\": \"$title\", \"question\": \"$question\", \"marketid\": \"$req[marketID]\", \"refer_raw\": \"$refer_raw\", \"refer_snap\": \"$refer_snap\", \"custom\": \"$custom\", \"vup\": \"$req[vupdated]\", \"country\": \"$country\", \"gravatar\": \"$gravatar\" }," ;
				}
			} $json_request = substr_replace( $json_request, "", -1 ) ;
			$json_request .= "	] " ;
		}
	}
	if ( count( $q_cces ) )
	{
		$ces = Util_Format_Sanatize( Util_Format_GetVar( "c" ), "ln" ) ;
		$isop = Util_Format_Sanatize( Util_Format_GetVar( "o" ), "n" ) ;
		$isop_ = Util_Format_Sanatize( Util_Format_GetVar( "o_" ), "n" ) ;
		$isop__ = Util_Format_Sanatize( Util_Format_GetVar( "o__" ), "n" ) ;
		$bid = Util_Format_Sanatize( Util_Format_GetVar( "b" ), "n" ) ;
		$isopr = Util_Format_Sanatize( Util_Format_GetVar( "isopr" ), "n" ) ;
		if ( !$isopr ) { $isopr = $realtime ; } // for realtime chat, realtime is always an operator
		$c_chatting = Util_Format_Sanatize( Util_Format_GetVar( "ch" ), "n" ) ;
		$q_chattings = Util_Format_Sanatize( Util_Format_GetVar( "qch" ), "a" ) ;
		$q_isop_ = Util_Format_Sanatize( Util_Format_GetVar( "qo_" ), "a" ) ;
		$q_isop__ = Util_Format_Sanatize( Util_Format_GetVar( "qo__" ), "a" ) ;
		$fmindexes = Util_Format_Sanatize( Util_Format_GetVar( "fi" ), "a" ) ;
		$fmsizes = Util_Format_Sanatize( Util_Format_GetVar( "fs" ), "a" ) ;
		$mapp = Util_Format_Sanatize( Util_Format_GetVar( "mp" ), "n" ) ;
		$fline = Util_Format_Sanatize( Util_Format_GetVar( "f" ), "n" ) ;

		if ( ( $isop && $isop_ ) && ( $isop == $isop_ ) ) { $iid = $isop__ ; }
		else if ( $isop && $isop_ ) { $iid = $isop_ ; }
		else { $iid = $isop_ ; }
		$filename = $ces.$iid ;
		$istyping = ( is_file( "$CONF[TYPE_IO_DIR]/{$filename}.txt" ) && !$realtime ) ? 1 : 0 ;
		$json_status = ( $json_status < 0 ) ? $json_status : 1 ;
		$json_chatting = " \"i\": $istyping, \"c\": [  " ;

		for ( $c = 0; $c < count( $q_cces ); ++$c )
		{
			$ces = Util_Format_Sanatize( $q_cces[$c], "lns" ) ;
			$chatting = Util_Format_Sanatize( $q_chattings[$c], "n" ) ;

			$text = "" ;
			$chat_file = "$CONF[CHAT_IO_DIR]/{$ces}.txt" ;
			if ( is_file( $chat_file ) )
			{
				$this_fmsize = filesize( $chat_file ) ;
				$this_fmindex = 0 ;
				if ( is_numeric( $fmindexes[$c] ) && is_numeric( $fmsizes[$c] ) && ( $fmsizes[$c] < $this_fmsize ) )
				{
					$trans_raw = file( $chat_file ) ;
					$trans = explode( "<>", implode( "", $trans_raw ) ) ;
					$this_fmindex = count( $trans ) - 1 ;
					if ( $fmindexes[$c] < $this_fmindex )
					{
						$trans = array_slice( $trans, $fmindexes[$c] ) ;
						if ( !$isopr )
						{
							$trans_out = Array() ;
							$total_index = count( $trans ) ;
							for ( $c2 = 0; $c2 < $total_index; ++$c2 )
							{
								$chat_line = base64_decode( $trans[$c2] ) ;
								if ( preg_match( "/<div class='co cw'/i", $chat_line ) )
								{
									// x-nod = no display or alert to the visitor
									$trans_out[] = base64_encode( "<x-nod>" ) ;
								}
								else
									$trans_out[] = base64_encode( $chat_line ) ;
							} $trans = $trans_out ;
						}
						$text = addslashes( preg_replace( "/\"/", "&quot;", implode( "<>", $trans ) ) ) ;
						$text = preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $text ) ;
					}
				}
				$json_chatting .= "{ \"ces\": \"$ces\", \"fmindex\": $this_fmindex, \"fmsize\": $this_fmsize, \"text\": \"$text\" }," ;
			}
			else
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/get.php" ) ;

				LIST( $ces ) = database_mysql_quote( $dbh, $ces ) ;
				$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
				database_mysql_query( $dbh, $query ) ;

				$lang = $CONF["lang"] ;
				$requestinfo_log = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;
				$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo_log["deptID"] ) ;
				if ( $deptinfo["lang"] ) { $lang = $deptinfo["lang"] ; }
				include( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($lang, "ln").".php" ) ;
				$LANG_DB = Lang_get_Lang( $dbh, $requestinfo_log["deptID"] ) ;
				if ( isset( $LANG_DB["deptID"] ) && $LANG_DB["deptID"] )
				{
					$db_lang_hash = unserialize( $LANG_DB["lang_vars"] ) ;
					$LANG = array_merge( $LANG, $db_lang_hash ) ;
				}

				if ( isset( $requestinfo_log["ces"] ) )
				{
					if ( $requestinfo_log["disc"] == 1 )
						$disconnect_text = "<div class='cl'><disconnected><d4>".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_ODISCONNECT"] ) )."</div>" ;
					else if ( $requestinfo_log["disc"] == 2 )
						$disconnect_text = "<div class='cl'><disconnected><d5>".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_VDISCONNECT"] ) )."</div>" ;
					else
						$disconnect_text = "<div class='cl'><disconnected><d8>".Util_Format_ConvertQuotes( urldecode( $LANG["CHAT_NOTIFY_DISCONNECT"] ) )."</div>" ;
				}
				else
					$disconnect_text = "<div class='cl'><disconnected><d9>".urldecode( $LANG["CHAT_NOTIFY_DISCONNECT"] )."</div>" ;
				$text = base64_encode( $disconnect_text ) ;
				$json_chatting .= "{ \"ces\": \"$ces\", \"text\": \"$text\" }," ;
			}

			if ( !$isop && ( !( $c_chatting % $VARS_CYCLE_VUPDATE ) ) && !$realtime )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;
				$requestid = Util_Format_Sanatize( Util_Format_GetVar( "rq" ), "n" ) ;
				$mobile = Util_Format_Sanatize( Util_Format_GetVar( "mo" ), "n" ) ;

				$vupdated = ( $mobile ) ? $now + $VARS_MOBILE_CHAT_BUFFER : $now ;
				if ( $mapp && is_file( "$CONF[TYPE_IO_DIR]/$isop_".".mapp" ) )
				{
					Chat_update_RequestValues( $dbh, $requestid, "vupdated", $vupdated, "updated", $vupdated ) ;
				}
				else
				{
					// always future time if $bid because the ping duration is much slower ($VARS_JS_REQUESTING * 20)
					if ( $bid )
					{
						$vupdated += ( $VARS_JS_REQUESTING * 60 ) * $VARS_CYCLE_VUPDATE ;
						Chat_update_RequestValues( $dbh, $requestid, "vupdated", $vupdated, "updated", $vupdated ) ;
					}
					else
						Chat_update_RequestValueByCes( $dbh, $ces, "vupdated", $vupdated ) ;
				}
			}
			else if ( !$isop && ( !( $c_chatting % $VARS_CYCLE_CLEAN ) ) && !$realtime )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;

				if ( !isset( $vars ) ) { $vars = Util_Format_Get_Vars( $dbh ) ; }
				if ( $vars["ts_clean"] <= ( $now - ( $VARS_CYCLE_CLEAN * 2 ) ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove_itr.php" ) ;
					Util_Format_Update_TimeStamp( $dbh, "clean", $now ) ;
					Chat_remove_itr_OldRequests( $dbh ) ;
				}
			}
		}
		$json_chatting = substr_replace( $json_chatting, "", -1 ) ; $json_chatting .= "	] " ;
	}
	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }

	$process_end = ( $microtime ) ? microtime(true) : time() ;
	$pd = $process_end - $process_start ; if ( !$pd ) { $pd = 0.001 ; }
	$pd = str_replace( ",", ".", $pd ) ; if ( is_numeric( $pd ) ) { $pd = number_format( $pd, 5 ) ; }

	if ( $json_request ) { $json_request .= ", " ; }
	if ( $json_chatting ) { $json_chatting .= ", " ; }
	$json_data = "json_data = { \"s\": $json_status, $json_request $json_chatting pd: $pd, $messenger_string \"e\": \"$json_error\" };" ;
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
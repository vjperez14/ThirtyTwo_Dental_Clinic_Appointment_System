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
	else if ( $action === "decline" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;

		$requestid = Util_Format_Sanatize( Util_Format_GetVar( "requestid" ), "n" ) ;
		$isop_ = Util_Format_Sanatize( Util_Format_GetVar( "isop_" ), "n" ) ;
		$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "lns" ) ;
		$op2op = Util_Format_Sanatize( Util_Format_GetVar( "op2op" ), "n" ) ;
		$status = Util_Format_Sanatize( Util_Format_GetVar( "status" ), "n" ) ;

		// unlink to make sure because decline always has Mobile App opened
		if ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) )
		{
			@unlink( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) ;
		}

		$requestinfo = Chat_get_itr_RequestCesInfo( $dbh, $ces ) ;
		if ( isset( $requestinfo["opID"] ) )
		{
			if ( ( $op2op || ( $status == 2 ) ) && ( ( $requestinfo["opID"] == $opid ) || ( $requestinfo["op2op"] == $opid ) ) && ( $status == $requestinfo["status"] ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;

				if ( !$status )
				{
					$text = "<c615><disconnected><div class='cl'>Operator was not available for chat.  Chat session has ended.</div></c615>" ;
					UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;
					if ( $opid != $isop_ )
					{
						// delay processing so it registers the declined message before deleting request too quickly
						sleep( $VARS_JS_REQUESTING+2 ) ;
					}
					Chat_remove_Request( $dbh, $requestinfo["requestID"] ) ;
				}
				else
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
					include_once( "$CONF[DOCUMENT_ROOT]/ops/inc_chat_transfer.php" ) ;
				}
			}
			else if ( $requestinfo["opID"] == $opid )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/update.php" ) ;

				Chat_update_RequestValues( $dbh, $requestid, "vupdated", 615, "opID", 0 ) ;
				// not a transfer, a standard request
				if ( ( $requestinfo["tupdated"] != 2 ) && ( $requestinfo["status"] != 2 ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;
					Ops_put_itr_OpReqStat( $dbh, $requestinfo["deptID"], $opid, "declined", 1 ) ;
				}

				// if in queue, this matters  always try to update.
				Queue_update_OpDeclined( $dbh, $ces, $opid ) ;
			}
			else if ( $requestinfo["opID"] == 1111111111 )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Queue/update.php" ) ;

				$sim_ops = Util_Format_ExplodeString( "-", $requestinfo["sim_ops"] ) ;
				Ops_put_itr_OpReqStat( $dbh, $requestinfo["deptID"], $opid, "declined", 1 ) ;

				// if in queue, this matters.  always try to update.
				Queue_update_OpDeclined( $dbh, $ces, $opid ) ;

				$sim_string = "{$opid}-" . $requestinfo["sim_ops_"] ;
				Chat_update_RequestValue( $dbh, $requestid, "sim_ops_", $sim_string ) ;
				$sim_ops_ = Util_Format_ExplodeString( "-", $sim_string ) ;

				$sim_ops_hash = Array() ;
				for ( $c = 0; $c < count( $sim_ops ); ++$c )
				{
					$this_opid = trim( $sim_ops[$c] ) ;
					if ( $this_opid ) { $sim_ops_hash[$this_opid] = 1 ; }
				}
				$sim_ops_new = 0 ; // make sure no new operators came online during sim routing and declined
				for ( $c = 0; $c < count( $sim_ops_ ); ++$c )
				{
					$this_opid = trim( $sim_ops_[$c] ) ;
					if ( !isset( $sim_ops_hash[$this_opid] ) ) { ++$sim_ops_new ; }
				}

				// only indicate 615 if valid to limit premature request delete in chat_routing.php if ( $now <= $rupdated )
				if ( !$sim_ops_new && ( count( $sim_ops ) == count( $sim_ops_ ) ) && isset( $sim_ops_hash[$opid] ) )
				{
					Chat_update_RequestValue( $dbh, $requestid, "vupdated", 615 ) ;
				}
			}
			$json_data = "json_data = { \"status\": 1, \"ces\": \"$ces\" };" ;
		}
		else { $json_data = "json_data = { \"status\": 1, \"ces\": \"$ces\" };" ; } // output success, chat doesn't exist anyway
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
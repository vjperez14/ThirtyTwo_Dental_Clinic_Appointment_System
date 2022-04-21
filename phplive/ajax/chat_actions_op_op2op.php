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
	else if ( $action === "op2op" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_ext.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put.php" ) ;

		$opid_cookie = $opid ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$resolution = Util_Format_Sanatize( Util_Format_GetVar( "win_dim" ), "ln" ) ;
		$peer_support = Util_Format_Sanatize( Util_Format_GetVar( "peer" ), "n" ) ;

		$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
		LIST( $ip, $null ) = Util_IP_GetIP( "" ) ;
		LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
		$mobile = ( $os == 5 ) ? 1 : 0 ;
		$ces = Util_Functions_ext_GenerateCes( $dbh ) ;
		$opinfo = Ops_get_OpInfoByID( $dbh, $opid_cookie ) ;
		$opinfo_ = Ops_get_OpInfoByID( $dbh, $opid ) ;

		if ( isset( $opinfo["opID"] ) )
		{
			$opinfo_next = $opinfo_ ; // set it to a variable that is recognized for SMS buffer
			LIST( $requestid, $request_country ) = Chat_put_Request( $dbh, $deptid, $opid, 0, 0, $opid_cookie, $os, $browser, $ces, $resolution, $opinfo["name"], $opinfo["email"], $ip, "op2op", "op2op", "op2op", "", "Operator to Operator Chat Request", $peer_support, 0, "", "", "" ) ;
			if ( isset( $requestid ) )
			{
				// create empty file to signal a chat session
				touch( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ;

				$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
				if ( isset( $mapp_array[$opid] ) && ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) || ( !$opinfo_["mapp"] && ( $opinfo_["sms"] == 1 ) ) ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;
					$arn = $mapp_array[$opid]["a"] ; $platform = $mapp_array[$opid]["p"] ;
					Util_MAPP_Publish( $opid, "new_request", $platform, $arn, "[ Operator to Operator Chat Request ]" ) ;
				}
				Chat_put_ReqLog( $dbh, $requestid ) ;
				$json_data = "json_data = { \"status\": 1, \"ces\": \"$ces\" };" ;
			}
			else
				$json_data = "json_data = { \"status\": -1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0 };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
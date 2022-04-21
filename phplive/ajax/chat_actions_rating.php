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
	$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;
	$token = Util_Format_Sanatize( Util_Format_GetVar( "token" ), "ln" ) ;

	if ( $action === "rating" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;

		$rating = Util_Format_Sanatize( Util_Format_GetVar( "rating" ), "n" ) ;

		$req_log = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;
		if ( isset( $req_log["ces"] ) )
		{
			$opid = $req_log["opID"] ;
			$deptid = $req_log["deptID"] ;
			Chat_update_TranscriptValue( $dbh, $ces, "rating", $rating ) ;
			Chat_update_RecentChat( $dbh, $opid, $ces, $rating ) ;
			Ops_put_itr_OpReqStat( $dbh, $deptid, $opid, "rateit", 1 ) ;
			Ops_put_itr_OpReqStat( $dbh, $deptid, $opid, "ratings", $rating ) ;
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "comment" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Notes/put.php" ) ;

		$message = Util_Format_Sanatize( Util_Format_GetVar( "message" ), "htmltags" ) ;

		$req_log = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;
		if ( isset( $req_log["ces"] ) )
		{
			$noteid = Notes_put_Note( $dbh, $req_log["opID"], $req_log["deptID"], 0, $ces, $message ) ;
			if ( $noteid ) { Chat_update_TranscriptValue( $dbh, $ces, "noteID", $noteid ) ; }
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Error processing request [n2].\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
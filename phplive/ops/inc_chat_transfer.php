<?php
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;

	// tupdated
	// 1 = chat was accepted
	// 2 = transfer chat to department (op is status = 2)
	// 3 = bot transfer chat to op
	// timestamp = timestamp of time chat was transferred for normal chat session

	$requestinfo_log = Chat_get_RequestHistCesInfo( $dbh, $requestinfo["ces"] ) ;
	$filename_declined = $requestinfo["ces"]."-de" ; // flag to limit duplicate transfer timeout message

	if ( !is_file( "$CONF[CHAT_IO_DIR]/{$filename_declined}.text" ) )
	{
		// for processing only one time
		UtilChat_AppendToChatfile( "{$filename_declined}.text", $ces ) ;
		$opinfo = Ops_get_OpInfoByID( $dbh, $requestinfo["op2op"] ) ; $profile_src = "" ;

		$lang = $CONF["lang"] ;
		$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo["deptID"] ) ;
		if ( $deptinfo["lang"] ) { $lang = $deptinfo["lang"] ; }
		$lang = Util_Format_Sanatize($lang, "ln") ;
		if ( is_file( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) ;

			$text = "<c615><restart_router><div class='ca'>".$LANG["CHAT_TRANSFER_TIMEOUT"]." $LANG[CHAT_TRANSFER] <b>$opinfo[name]</b> ($deptinfo[name]).</div></c615>" ;
		} else { $text = "<c615><restart_router><div class='ca'>Transfer chat not available at this time.  Connecting to the previous operator... Transferring chat to <b>$opinfo[name]</b> ($deptinfo[name]).</div></c615>" ; }

		if ( $opinfo["pic"] )
		{
			if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
			else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
			$profile_src = Util_Upload_GetLogo( "profile", $opinfo["opID"] ) ;
		}

		$text .= "<top><!--opid:$opinfo[opID]--><!--mapp:$opinfo[mapp]--><!--name:$opinfo[name]--><!--profile_pic:$profile_src--><!--department:$deptinfo[name]--></top>" ;

		/***************************************/
		// as of v.4.7.99.9.8, route to leave a message rather then routing back to previous op
		$text = "<x-nod><restart_router><div class='ca'>Operator was not available to accept the chat transfer.</div>" ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
		Chat_remove_Request( $dbh, $requestinfo["requestID"] ) ; usleep( 250000 ) ;
		/***************************************/

		UtilChat_AppendToChatfile( $requestinfo["ces"].".txt", base64_encode( $text ) ) ;

		/*
		$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
		$mapp_opid = $opinfo["opID"] ;
		if ( $opinfo["mapp"] && is_file( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;
			if ( isset( $mapp_array[$mapp_opid] ) ) { $arn = $mapp_array[$mapp_opid]["a"] ; $platform = $mapp_array[$mapp_opid]["p"] ; }
			if ( isset( $arn ) && $arn ) { Util_MAPP_Publish( $mapp_opid, "new_request", $platform, $arn, "[ Transfer Chat ]" ) ; }
		}
		*/
	}
	/*
	$now_v = time() ;
	$query = "UPDATE p_requests SET deptID = $requestinfo_log[deptID], tupdated = 0, status = 2, vupdated = $now_v WHERE ces = '$requestinfo[ces]'" ;
	database_mysql_query( $dbh, $query ) ;
	*/
?>
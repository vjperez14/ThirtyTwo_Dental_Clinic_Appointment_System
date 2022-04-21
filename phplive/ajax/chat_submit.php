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

	$isop = Util_Format_Sanatize( Util_Format_GetVar( "isop" ), "n" ) ;
	$isop_ = Util_Format_Sanatize( Util_Format_GetVar( "isop_" ), "n" ) ;
	$isop__ = Util_Format_Sanatize( Util_Format_GetVar( "isop__" ), "n" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
	$bid = Util_Format_Sanatize( Util_Format_GetVar( "b" ), "n" ) ;
	$op2op = Util_Format_Sanatize( Util_Format_GetVar( "op2op" ), "n" ) ;
	$requestid = Util_Format_Sanatize( Util_Format_GetVar( "requestid" ), "n" ) ;
	$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "lns" ) ;
	$salt = Util_Format_Sanatize( Util_Format_GetVar( "salt" ), "ln" ) ;
	$mapp = Util_Format_Sanatize( Util_Format_GetVar( "mp" ), "n" ) ;
	$text = preg_replace( "/( p_br )/", "<br>", Util_Format_Sanatize( base64_decode( Util_Format_GetVar( "text" ) ), "noscripts" ) ) ; $text_out = "" ;
	$cookie_opid = isset( $_COOKIE["cO"] ) ? $_COOKIE["cO"] : "" ;
	$cookie_ses = isset( $_COOKIE["cS"] ) ? $_COOKIE["cS"] : "" ;
	if ( ( ( md5( md5( $CONF["SALT"] ).$ces ) == $salt ) || ( md5( md5( $CONF["SALT"] ).$cookie_opid.$cookie_ses ) == $salt ) ) && is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;

		if ( ( $isop && $isop_ ) && ( $isop == $isop_ ) ) { $wid = $isop_ ; }
		else if ( $isop && $isop_ ) { $wid = $isop__ ; }
		else { $wid = $isop_ ; }

		// override javascript timestamp
		$text = preg_replace( "/<timestamp_(\d+)_((co)|(cv))>/", "<timestamp_".$now."_$2>", $text ) ;
		$text = preg_replace( "/▒~@▒/", "", $text ) ; $text = Util_Format_NOJS( $text ) ;

		if ( is_file( "$CONF[DOCUMENT_ROOT]/custom_code/chat_submit.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/custom_code/chat_submit.php" ) ; }
		UtilChat_AppendToChatfile( "{$ces}.txt", base64_encode( $text ) ) ;
		usleep( 100000 ) ; // 10th of second

		if ( $isop )
		{
			if ( $op2op )
			{
				$wid = ( $isop == $isop__ ) ? $isop_ : $isop__ ;
				if ( $wid && is_file( "$CONF[TYPE_IO_DIR]/{$wid}.mapp" ) )
				{
					$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
					if ( isset( $mapp_array[$wid] ) ) { $arn = $mapp_array[$wid]["a"] ; $platform = $mapp_array[$wid]["p"] ; }
					if ( isset( $arn ) && $arn )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;
						$text_plain = strip_tags( $text ) ;
						Util_MAPP_Publish( $wid, "new_text", $platform, $arn, $text_plain ) ;
					}
				}
			}
			else
			{
				//
			}
		} else if ( $bid && is_file( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/phplivebot.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/inc_chat_submit.php" ) ; }
		else
		{
			if ( $wid && ( $wid == $mapp ) && !is_file( "$CONF[TYPE_IO_DIR]/{$mapp}.mapp" ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
				$op_inactive = $now - 60 ;

				// check for mapp file just in case it does not exist or was deleted
				$opinfo = Ops_get_OpInfoByID( $dbh, $mapp ) ;
				if ( $opinfo["lastactive"] < $op_inactive )
					touch( "$CONF[TYPE_IO_DIR]/{$mapp}.mapp" ) ;
			}

			if ( $wid && is_file( "$CONF[TYPE_IO_DIR]/{$wid}.mapp" ) )
			{
				$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
				if ( isset( $mapp_array[$wid] ) ) { $arn = $mapp_array[$wid]["a"] ; $platform = $mapp_array[$wid]["p"] ; }
				if ( isset( $arn ) && $arn )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;
					$text_plain = strip_tags( $text ) ;
					Util_MAPP_Publish( $wid, "new_text", $platform, $arn, $text_plain ) ;
				}
			}
		}
		UtilChat_WriteIsWriting( $ces, 0, $isop, $isop_, $isop__ ) ;

		// need to use "text" because update_ces() relies on it
		$json_data = "json_data = { \"status\": 1, \"ces\": \"$ces\", \"text\": \"$text_out\" };" ;
	}
	else
		$json_data = "json_data = { \"status\": -1 };" ;
	
	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
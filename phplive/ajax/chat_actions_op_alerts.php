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
	else if ( $action === "alerts" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;

		$declines = Chat_get_OpDeclined( $dbh, $opid ) ;
		$json_data = "json_data = { \"status\": 1, \"declines\": [  " ;
		for ( $c = 0; $c < count( $declines ); ++$c )
		{
			$declineinfo = $declines[$c] ;
			$ces = Util_Format_ConvertQuotes( $declineinfo["ces"] ) ;
			$created = date( "M j ($VARS_TIMEFORMAT)", $declineinfo["created"] ) ;
			if ( function_exists( "mb_convert_encoding" ) )
				$vname = rawurlencode( Util_Format_ConvertQuotes( mb_convert_encoding( $declineinfo["vname"], 'UTF-8', 'UTF-8' ) ) ) ;
			else
				$vname = rawurlencode( Util_Format_ConvertQuotes( $declineinfo["vname"] ) ) ;
			$vemail = Util_Format_ConvertQuotes( $declineinfo["vemail"] ) ;
			$marketid = $declineinfo["marketID"] ;
			$ip = Util_Format_ConvertQuotes( $declineinfo["ip"] ) ;
			$custom = Util_Format_ConvertQuotes( $declineinfo["custom"] ) ;
			$onpage_url = rawurlencode( Util_Format_ConvertQuotes( $declineinfo["onpage"] ) ) ;
			$onpage_title = rawurlencode( Util_Format_ConvertQuotes( $declineinfo["title"] ) ) ;
			$question = rawurlencode( Util_Format_ConvertQuotes( preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $declineinfo["question"] ) ) ) ;
			$custom_vars_string = "" ;

			$status = $declineinfo["status"] ;

			if ( $declineinfo["custom"] )
			{
				$custom_vars_string = "" ;
				$customs = explode( "-cus-", $declineinfo["custom"] ) ;
				for ( $c2 = 0; $c2 < count( $customs ); ++$c2 )
				{
					$custom_var = $customs[$c2] ;
					if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
					{
						LIST( $cus_name, $cus_val ) = explode( "-_-", rawurldecode( $custom_var ) ) ;
						if ( $cus_val )
						{
							if ( preg_match( "/^((http)|(www))/", $cus_val ) )
							{
								if ( preg_match( "/^(www)/", $cus_val ) ) { $cus_val = "http://$cus_val" ; }
								$cus_val_snap = ( strlen( $cus_val ) > 40 ) ? substr( $cus_val, 0, 15 ) . "..." . substr( $cus_val, -15, strlen( $cus_val ) ) : $cus_val ;
								$custom_vars_string .= "<div style=\"padding: 2px;\"><b>$cus_name:</b> <a href=\"$cus_val\" target=_blank>$cus_val_snap</a></div>" ;
							}
							else
							{
								$custom_vars_string .= "<div style=\"padding: 2px;\"><b>$cus_name:</b> $cus_val</div>" ;
							}
						}
					}
				}
				$custom_vars_string = ( $custom_vars_string ) ? "<div style=\"margin-top: 15px; max-height: 65px; overflow: auto;\">$custom_vars_string</div>" : "" ;
				$custom_vars_string = rawurlencode( $custom_vars_string ) ;
			}
			$json_data .= "{ \"ces\": \"$ces\", \"created\": \"$created\", \"status\": \"$status\", \"status_msg\": \"$declineinfo[status_msg]\", \"deptid\": \"$declineinfo[deptID]\", \"vname\": \"$vname\", \"vemail\": \"$vemail\", \"marketid\": $marketid, \"ip\": \"$ip\", \"question\": \"$question\", \"onpage_url\": \"$onpage_url\", \"onpage_title\": \"$onpage_title\", \"custom_vars\": \"$custom_vars_string\" }," ;
		}
		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
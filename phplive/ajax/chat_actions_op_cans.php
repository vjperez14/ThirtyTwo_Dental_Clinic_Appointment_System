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
	else if ( $action === "cans" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Canned/get.php" ) ;

		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$global_can_exists = 0 ; // 1111111111 indication for <select> prints

		$cans = Canned_get_OpCanned( $dbh, $opid, $deptid ) ;
		$json_data = "json_data = { \"status\": 1, \"cans\": [  " ;
		for ( $c = 0; $c < count( $cans ); ++$c )
		{
			$caninfo = $cans[$c] ;
			$cats_extra = ( $caninfo["cats_extra"] && Util_Functions_itr_is_serialized( $caninfo["cats_extra"] ) ) ? unserialize( $caninfo["cats_extra"] ) : Array() ;
			$title = Util_Format_ConvertQuotes( $caninfo["title"] ) ;
			$message = Util_Format_ConvertQuotes( preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $caninfo["message"] ) ) ;
			$message = preg_replace( "/▒~@▒/", "", $message ) ;

			$deptid = $caninfo["deptID"] ; $catid = $caninfo["catID"] ;
			if ( isset( $cats_extra[$opid] ) )
			{
				LIST( $deptid, $catid ) = explode( ",", $cats_extra[$opid] ) ;
			}

			if ( $caninfo["deptID"] == 1111111111 ) { $global_can_exists = 1 ; }

			$json_data .= "{ \"canid\": $caninfo[canID], \"deptid\": $deptid, \"catid\": \"$catid\", \"title\": \"$title\", \"message\": \"$message\" }," ;
		}
		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	], \"global\": $global_can_exists };" ;
	}
	else if ( $action === "auto_canned" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;

		$canid = Util_Format_Sanatize( Util_Format_GetVar( "canid" ), "n" ) ;
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;
		$opid = Util_Format_Sanatize( $_COOKIE["cO"], "n" ) ;

		if ( $value ) { Ops_update_OpVarValue( $dbh, $opid, "canID", $canid ) ; }
		else { Ops_update_OpVarValue( $dbh, $opid, "canID", 0 ) ; }
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else if ( $action === "vis_idle" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;

		$canid_1 = Util_Format_Sanatize( Util_Format_GetVar( "canid_1" ), "n" ) ;
		$canid_2 = Util_Format_Sanatize( Util_Format_GetVar( "canid_2" ), "n" ) ;
		$idle = Util_Format_Sanatize( Util_Format_GetVar( "idle" ), "n" ) ;

		$vis_idle_canned = Array( "canid_1" => $canid_1, "canid_2" => $canid_2, "idle" => $idle ) ;

		if ( $canid_1 && $canid_1 )
			Ops_update_OpVarValue( $dbh, $opid, "vis_idle_canned", serialize( $vis_idle_canned ) ) ;
		else
			Ops_update_OpVarValue( $dbh, $opid, "vis_idle_canned", "" ) ;
		$json_data = "json_data = { \"status\": 1 };" ;
	}
	else
		$json_data = "json_data = { \"status\": 0 };" ;

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
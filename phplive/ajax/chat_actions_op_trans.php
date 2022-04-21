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
	else if ( $action === "transcripts" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
		$vis_token = Util_Format_Sanatize( Util_Format_GetVar( "vis_token" ), "lns" ) ;

		$operators = Ops_get_AllOps( $dbh ) ;
		$operators_hash = Array() ;
		for ( $c = 0; $c < count( $operators ); ++$c )
		{
			$operator = $operators[$c] ;
			$operators_hash[$operator["opID"]] = $operator["name"] ;
		}

		$transcripts = Chat_ext_get_VisTranscripts( $dbh, $vis_token ) ;
		$json_data = "json_data = { \"status\": 1, \"transcripts\": [  " ;
		for ( $c = 0; $c < count( $transcripts ); ++$c )
		{
			$transcript = $transcripts[$c] ;

			if ( $transcript["opID"] )
			{
				// intercept nulled operator accounts that have been deleted
				if ( !isset( $operators_hash[$transcript["op2op"]] ) ) { $operators_hash[$transcript["op2op"]] = "&nbsp;" ; }
				if ( !isset( $operators_hash[$transcript["opID"]] ) ) { $operators_hash[$transcript["opID"]] = "&nbsp;" ; }

				$operator = ( $transcript["op2op"] ) ? $operators_hash[$transcript["op2op"]] : $operators_hash[$transcript["opID"]] ;
				$created = date( "M j ($VARS_TIMEFORMAT)", $transcript["created"] ) ;
				$duration = $transcript["ended"] - $transcript["created"] ;
				$duration = Util_Format_Duration( $duration ) ;
				$question = Util_Format_ConvertQuotes( preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $transcript["question"] ) ) ;
				$vname = ( $transcript["op2op"] ) ? $operators_hash[$transcript["opID"]] : $transcript["vname"] ;

				$json_data .= "{ \"ces\": \"$transcript[ces]\", \"created\": \"$created\", \"operator\": \"$operator\", \"rating\": \"$transcript[rating]\", \"duration\": \"$duration\", \"initiated\": $transcript[initiated], \"op2op\": $transcript[op2op], \"vname\": \"$vname\", \"question\": \"$question\" }," ;
			}
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
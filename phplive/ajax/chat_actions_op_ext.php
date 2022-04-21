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
		$json_data = "json_data = { \"status\": -1, \"error\": \"Invalid [eop 1].\" };" ;
	else
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		if ( $action === "dn_toggle" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$dn = Util_Format_Sanatize( Util_Format_GetVar( "dn" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "dn_request", $dn ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "dn_toggle_response" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$dn = Util_Format_Sanatize( Util_Format_GetVar( "dn" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "dn_response", $dn ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "console_sound" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "sound", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "console_sound_mapp" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "sound", $value ) ;
			if ( !$value ) { Ops_update_OpVarValue( $dbh, $opid, "blink", 1 ) ; }
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "push_repeat_mapp" )
		{
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;
			$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
			if ( isset( $mapp_array[$opid] ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
				$mapp_array[$opid]["r"] = $value ; 
				Util_Vals_WriteToFile( "MAPP", serialize( $mapp_array ) ) ;
			}
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "console_blink" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "blink", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "console_blink_r" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "blink_r", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action === "dn_always" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

			Ops_update_OpVarValue( $dbh, $opid, "dn_always", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else if ( $action == "can_cats" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/ajax/inc_cats.php" ) ;;
		}
		else if ( $action == "update_mobile_push" )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		
			$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;
			Ops_update_OpValue( $dbh, $opid, "sms", $value ) ;
			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid action [eop 2].\" };" ;
	}

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
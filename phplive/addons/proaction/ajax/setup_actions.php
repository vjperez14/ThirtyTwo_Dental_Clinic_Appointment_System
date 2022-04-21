<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	include_once( "../../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$proid = Util_Format_Sanatize( Util_Format_GetVar( "proid" ), "ln" ) ;

	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/Util_Proaction.php" ) ;
		include_once( "$CONF[CONF_ROOT]/addons.php" ) ;

		if ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["proaction"] ) && $VALS_ADDONS["proaction"] )
		{
			$proactions = unserialize( base64_decode( $VALS_ADDONS["proaction"] ) ) ;
			if ( $action == "moveup" )
			{
				if ( isset( $proactions[$proid] ) )
				{
					$update = 0 ;
					foreach ( $proactions as $thisproid => $proaction_array )
					{
						if ( $proid == $thisproid )
						{
							$proactions[$proid]["priority"] = $now ;
							$update = 1 ;
						}
					}
					if ( $update )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/addons/API/Util_Addons.php" ) ;
						Util_Addons_WriteToFile( "proaction", base64_encode( serialize($proactions) ) ) ;
					}
					$json_data = "json_data = { \"status\": 1 };" ;
					HEADER('Content-Type: text/plain; charset=utf-8') ;
					print $json_data ;
				}
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid ProAction ID.  Please try again.\" };" ;
			}
			else if ( $action == "reset" )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/Util_Proaction.php" ) ;

				Util_Proaction_DeleteStats( $dbh, $proid ) ;
				$json_data = "json_data = { \"status\": 1 };" ;
			}
			else if ( $action == "pause" )
			{
				if ( isset( $proactions[$proid] ) )
				{
					$status = Util_Format_Sanatize( Util_Format_GetVar( "status" ), "n" ) ;

					if ( $status && isset( $proactions[$proid]["paused"] ) ) { unset( $proactions[$proid]["paused"] ) ; }
					else if ( !$status ) { $proactions[$proid]["paused"] = 1 ; }
					include_once( "$CONF[DOCUMENT_ROOT]/addons/API/Util_Addons.php" ) ;
					Util_Addons_WriteToFile( "proaction", base64_encode( serialize($proactions) ) ) ;

					$json_data = "json_data = { \"status\": 1 };" ;
				}
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid ProAction ID.  Please try again. [e2]\" };" ;
			}
			else
				$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid request [pr].  Please try again.\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"ProAction Invite has not been created.\" };" ;
	}

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
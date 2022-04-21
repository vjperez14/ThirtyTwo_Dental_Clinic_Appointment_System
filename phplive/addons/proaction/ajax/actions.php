<?php
	include_once( "../../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$proid = Util_Format_Sanatize( Util_Format_GetVar( "proid" ), "ln" ) ;
	$status = Util_Format_Sanatize( Util_Format_GetVar( "status" ), "n" ) ;
	$image_dir = realpath( "$CONF[DOCUMENT_ROOT]/pics/icons/pixels" ) ;

	$image_path = "$image_dir/1x1.gif" ;
	if ( $action == "status" )
	{
		LIST( $ip, $null ) = Util_IP_GetIP( "" ) ;
		if ( !isset( $VALS["TRAFFIC_EXCLUDE_IPS"] ) ) { $VALS["TRAFFIC_EXCLUDE_IPS"] = "" ; }
		$exclude_traffic_ips = explode( "-", preg_replace( "/ +/", "", $VALS['TRAFFIC_EXCLUDE_IPS'] ) ) ; $exclude_traffic_ips_exist = 0 ;
		if ( $ip ) { for ( $c = 0; $c < count( $exclude_traffic_ips ); ++$c ) { if ( $exclude_traffic_ips[$c] && preg_match( "/".$exclude_traffic_ips[$c]."/", $ip ) ) { ++$exclude_traffic_ips_exist ; break ; } } }
		if ( !$exclude_traffic_ips_exist )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/Util_Proaction.php" ) ;
			if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) )
				include_once( "$CONF[CONF_ROOT]/addons.php" ) ;

			if ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["proaction"] ) && $VALS_ADDONS["proaction"] )
			{
				$proactions = unserialize( base64_decode( $VALS_ADDONS["proaction"] ) ) ;
				if ( isset( $proactions[$proid] ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;

					Util_Proaction_SaveClickStatus( $dbh, $proid, $status ) ;
					database_mysql_close( $dbh ) ;
				}
			}
		} $image_path = "$image_dir/6x6.gif" ;
	}

	Header( "Content-type: image/GIF" ) ;
	Header( "Content-Transfer-Encoding: binary" ) ;
	if ( !isset( $VALS['OB_CLEAN'] ) || ( $VALS['OB_CLEAN'] == 'on' ) ) { ob_clean(); flush(); }
	readfile( $image_path ) ;
?>
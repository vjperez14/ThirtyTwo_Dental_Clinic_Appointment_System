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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	use GeoIp2\Database\Reader ;

	$onpage = Util_Format_Sanatize( base64_decode( Util_Format_GetVar( "pg" ) ), "url" ) ;
	$title = Util_Format_Sanatize( base64_decode( Util_Format_GetVar( "tl" ) ), "title" ) ;
	$refer = Util_Format_Sanatize( base64_decode( Util_Format_GetVar( "r" ) ), "url" ) ;
	$resolution = Util_Format_Sanatize( Util_Format_GetVar( "resolution" ), "ln" ) ;
	$token = Util_Format_Sanatize( Util_Format_GetVar( "token" ), "ln" ) ;
	$c = Util_Format_Sanatize( Util_Format_GetVar( "c" ), "n" ) ;
	$image_dir = realpath( "$CONF[DOCUMENT_ROOT]/pics/icons/pixels" ) ; $image_path = "$image_dir/4x4.gif" ;
	$refer = ( $refer && !preg_match( "/$PHPLIVE_HOST/i", $refer ) ) ? $refer : "" ;

	LIST( $ip, $vis_token ) = Util_IP_GetIP( $token ) ;
	$pi = $marketid = $skey = $excluded = 0 ;

	preg_match( "/plk(=|%3D)(.*)-m/", $onpage, $matches ) ;
	if ( isset( $matches[2] ) ) { LIST( $pi, $marketid, $skey ) = explode( "-", $matches[2] ) ; }
	if ( !isset( $CONF["foot_log"] ) ) { $CONF["foot_log"] = "on" ; }

	$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
	$agent_lang = isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "&nbsp;" ;
	$agent_md5 = md5( "$agent$agent_lang$ip" ) ;
	if ( $agent_md5 && Util_IP_IsIPExcluded( $agent_md5, 1 ) ) { $excluded = 1 ; }
	if ( !$excluded && Util_IP_IsIPExcluded( $ip, 0 ) ) { $excluded = 1 ; }

	LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
	$agent = substr( $agent, 0, 255 ) ;
	$mobile = ( $os == 5 ) ? 1 : 0 ;

	if ( preg_match( "/(statichtmlapp.com)/", $onpage ) && !$title ) { $title = "Facebook Page" ; }
	else if ( !$title ) { $title = "- no title -" ; }

	if ( $excluded ) { $image_path = "$image_dir/5x5.gif" ; }
	else
	{
		if ( !isset( $CONF['SQLTYPE'] ) ) { $CONF['SQLTYPE'] = "SQL.php" ; }
		else if ( $CONF['SQLTYPE'] == "mysql" ) { $CONF['SQLTYPE'] = "SQL.php" ; }
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/put.php" ) ;

		$geo_country_code = $geo_region = $geo_city = "" ; $geo_lat = $geo_long = 0 ;
		if ( $geoip && !$c )
		{
			if ( ( phpversion() >= 5.4 ) && is_file( "$CONF[DOCUMENT_ROOT]/addons/geo_data/vendor/autoload.php" ) )
			{
				require "$CONF[DOCUMENT_ROOT]/addons/geo_data/vendor/autoload.php" ;
				$reader = new Reader( "$CONF[DOCUMENT_ROOT]/addons/geo_data/GeoLite2-City.mmdb" ) ;
				try {
					$record = $reader->city( $ip ) ;
					$geo_country_code = ( isset( $record->country->isoCode ) ) ? $record->country->isoCode : "" ;
					$geo_country = ( $geo_country_code != "" ) ? $record->country->name : "" ;
					$geo_region = ( isset( $record->mostSpecificSubdivision->name ) ) ? $record->mostSpecificSubdivision->name : "" ;
					$geo_city = ( isset( $record->city->name ) ) ? $record->city->name : "" ;
					$geo_lat = ( isset( $record->location->latitude ) ) ? $record->location->latitude : 28.613459424004414 ;
					$geo_long = ( isset( $record->location->longitude ) ) ? $record->location->longitude : -40.4296875 ;
				} catch (Exception $e) {
					$geo_country_code = $geo_region = $geo_city = "" ; $geo_lat = $geo_long = 0 ;
				}
			}
		}
		if ( $onpage && !$c )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/get.php" ) ;
			$ipinfo = IPs_get_IPInfo( $dbh, $vis_token, $ip ) ;
			$footprints = isset( $ipinfo["t_footprints"] ) ? $ipinfo["t_footprints"]+1 : 1 ;
			$requests = isset( $ipinfo["t_requests"] ) ? $ipinfo["t_requests"] : 0 ;
			$initiates = isset( $ipinfo["t_initiate"] ) ? $ipinfo["t_initiate"] : 0 ;
			$query = "SELECT * FROM p_refer WHERE md5_vis = '$vis_token' LIMIT 1" ;
			database_mysql_query( $dbh, $query ) ; $refer_data = database_mysql_fetchrow( $dbh ) ;
			$refer = ( isset( $refer_data["refer"] ) ) ? $refer_data["refer"] : $refer ;

			if ( $pi && is_numeric( $marketid ) && $marketid && $skey )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/get_itr.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/update.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/put.php" ) ;

				$marketinfo = Marketing_get_itr_MarketingByID( $dbh, $marketid ) ;
				if ( $marketinfo["skey"] == $skey )
				{
					$clickinfo = Marketing_get_itr_ClickInfo( $dbh, $marketid ) ;
					if ( isset( $clickinfo["marketID"] ) ) { Marketing_update_MarketClickValue( $dbh, $marketid, "clicks", $clickinfo["clicks"]+1 ) ; }
					else { Marketing_put_Click( $dbh, $marketid, 1 ) ; }
				}
			}
		}
		else { $footprints = 1 ; $requests = $initiates = 0 ; }
		$nresults = Footprints_put_Print_U( $dbh, $c, $vis_token, 0, $os, $browser, $footprints, $requests, $initiates, $resolution, $ip, $onpage, $title, $marketid, $refer, $geo_country_code, $geo_region, $geo_city, $geo_lat, $geo_long ) ;
		if ( $c > $VARS_JS_FOOTPRINT_MAX_CYCLE ) { $image_path = "$image_dir/4x4.gif" ; }
		else if ( !$c )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/put.php" ) ;
	
			if ( $CONF["foot_log"] == "on" )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/put_itr.php" ) ;
				Footprints_put_itr_Print( $dbh, 0, $os, $browser, $ip, $vis_token, $onpage, $title ) ;
			}
			if ( $refer || ( !$refer && $marketid ) ) { Footprints_put_Refer( $dbh, $vis_token, $marketid, $refer ) ; }
			$result = IPs_put_IP( $dbh, $ip, $vis_token, 0, 1, 0, 0, 0, 0, 1, false, $onpage ) ;

			$vars = Util_Format_Get_Vars( $dbh ) ;
			if ( $vars["ts_clear"] <= ( $now - ( $VARS_CYCLE_CLEAN * 2 ) ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/remove_itr.php" ) ;
				include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/remove.php" ) ;
				Util_Format_Update_TimeStamp( $dbh, "clear", $now ) ;
				Footprints_remove_itr_Expired_U( $dbh ) ;
				IPs_remove_Expired_IPs( $dbh ) ;
			}
			if ( is_numeric( $result ) )
			{
				if ( $result == 1 ) { $image_path = "$image_dir/2x2.gif" ; }
				else { $image_path = "$image_dir/3x3.gif" ; }
			}
			else { $image_path = "$image_dir/1x1.gif" ; }
		}
		else
		{
			// repeat
			include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/put.php" ) ;
			$result = IPs_put_IP( $dbh, $ip, $vis_token, 0, 0, 0, 0, 0, 0, 0, false, $onpage ) ;
			if ( is_numeric( $result ) )
			{
				// 1, process, else don't process and skip the duration timer flag
				if ( $result == 1 ) { $image_path = "$image_dir/2x2.gif" ; }
				else { $image_path = "$image_dir/3x3.gif" ; }
			}
			else { $image_path = "$image_dir/1x1.gif" ; }
		}
	}

	if ( isset( $dbh ) && isset( $dbh['con'] ) )
		database_mysql_close( $dbh ) ;

	Header( "Content-type: image/GIF" ) ;
	Header( "Content-Transfer-Encoding: binary" ) ;
	if ( !isset( $VALS['OB_CLEAN'] ) || ( $VALS['OB_CLEAN'] == 'on' ) ) { ob_clean(); flush(); }
	readfile( $image_path ) ;
?>
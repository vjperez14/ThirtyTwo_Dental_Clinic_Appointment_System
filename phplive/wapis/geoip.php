<?php
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	use GeoIp2\Database\Reader ;

	$akey = Util_Format_Sanatize( Util_Format_GetVar( "akey" ), "ln" ) ;
	$format = Util_Format_Sanatize( Util_Format_GetVar( "f" ), "ln" ) ;
	$ip = Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "ln" ) ;

	$output = "" ;
	if ( $akey && isset( $CONF["API_KEY"] ) && ( $akey == $CONF["API_KEY"] ) )
	{
		if ( $geoip )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Hash.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/Util.php" ) ;

			if ( phpversion() >= 5.4 )
			{
				require "$CONF[DOCUMENT_ROOT]/addons/geo_data/vendor/autoload.php" ;

				$reader = new Reader( "$CONF[DOCUMENT_ROOT]/addons/geo_data/GeoLite2-City.mmdb" ) ;
				try {
					$record = $reader->city( $ip ) ;
					$geo_country_code = ( isset( $record->country->isoCode ) ) ? $record->country->isoCode : "unknown" ;
					$geo_country = ( $geo_country_code != "unknown" ) ? $record->country->name : "Unknown" ;
					$geo_region = ( isset( $record->mostSpecificSubdivision->name ) ) ? $record->mostSpecificSubdivision->name : "unknown" ;
					$geo_city = ( isset( $record->city->name ) ) ? $record->city->name : "unknown" ;
					$geo_lat = ( isset( $record->location->latitude ) ) ? $record->location->latitude : 28.613459424004414 ;
					$geo_long = ( isset( $record->location->longitude ) ) ? $record->location->longitude : -40.4296875 ;
				} catch (Exception $e) {
					$geo_country_code = "Geo location not found or IP format is invalid." ; $geo_country = $geo_region = $geo_city = $geo_lat = $geo_long = "" ;
				}

				$output = "" ;
				if ( $format == "csv" )
					$output = "$geo_country_code,$geo_country,$geo_region,$geo_city,$geo_lat,$geo_long" ;
				else if ( $format == "json" )
					$output = "{ \"country\": \"$geo_country_code\", \"country_name\": \"$geo_country\", \"region\": \"$geo_region\", \"city\": \"$geo_city\", \"latitude\": $geo_lat, \"longitude\": $geo_long }" ;
				else
					$output = "Invalid Format" ;
			}
			else
				$output = "GeoIP Addon requires PHP >= 5.4.  Please <a href='http://php.net/downloads.php' target='_blank'>upgrade your PHP</a> to utilize the GeoIP Addon." ;
		}
		else
			$output = "GeoIP Not Enabled" ;
	}
	else
		$output = "Invalid API Key" ;
	
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $output ; exit ;
?>
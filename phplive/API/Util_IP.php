<?php
	if ( defined( 'API_Util_IP' ) ) { return ; }	
	define( 'API_Util_IP', true ) ;

	FUNCTION Util_IP_GetIP( $token )
	{
		global $CONF ; global $PHPLIVE_HOST ; global $PHPLIVE_SECURE ; global $VARS_IP_CAPTURE ;
		if ( !defined( 'API_Util_Format' ) )
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;

		$ip = "0.0.0.0" ;
		$headers = function_exists( "apache_request_headers" ) ? apache_request_headers() : Array() ;
		for ( $c = 0; $c < count( $VARS_IP_CAPTURE ); ++$c )
		{
			$env_var = $VARS_IP_CAPTURE[$c] ;
			if ( isset( $_SERVER[$env_var] ) && $_SERVER[$env_var] ) {
				$ip = $_SERVER[$env_var] ;
				if ( preg_match( "/,/", $ip ) ) { LIST( $ip, $ip_ ) = explode( ",", preg_replace( "/ +/", "", $ip ) ) ; }
				break 1 ;
			} else if ( isset( $headers[$env_var] ) && $headers[$env_var] ) { $ip = $headers[$env_var] ; break 1 ; }
		}

		$cookie_fallback = md5( $token.$ip ) ;
		if ( !isset( $PHPLIVE_HOST ) ) { $PHPLIVE_HOST = "unknown_host" ; }
		if ( ( !isset( $CONF['cookie'] ) || ( isset( $CONF['cookie'] ) && ( $CONF['cookie'] == "on" ) ) ) )
		{
			if ( !isset( $_COOKIE["phplivevid"] ) )
			{
				// only set cookie if $token is passed, otherwise, it's IP fetching only
				if ( $token )
				{
					$microtime = microtime(true) ;
					$cookie_vid = md5( $microtime.rand( 100000000, 999999999 ).Util_Format_RandomString(10) ) ;
					Util_Format_SetCookie( "phplivevid", $cookie_vid, time()+(60*60*24*3650), "/", "", $PHPLIVE_SECURE ) ;
				}
				else { $cookie_vid = $cookie_fallback ; }
			} else { $cookie_vid = $_COOKIE["phplivevid"] ; }
		} else { $cookie_vid = $cookie_fallback ; }
		return Array( $ip, Util_Format_Sanatize( $cookie_vid, "lns" ) ) ;
	}

	FUNCTION Util_IP_IsIPExcluded( $ip, $theforce )
	{
		global $VALS ;
		if ( $ip && isset( $VALS['TRAFFIC_EXCLUDE_IPS'] ) )
		{
			$ips = explode( "-", Util_Format_Sanatize( $VALS['TRAFFIC_EXCLUDE_IPS'], "ip" ) ) ;

			for ( $c = 0; $c < count( $ips ); ++$c )
			{
				if ( $ips[$c] )
				{
					if ( preg_match( '/^\*/', $ips[$c] ) && preg_match( '/\*$/', $ips[$c] ) && !$theforce )
					{
						$temp_ip = preg_replace( '/\*/', "", $ips[$c] ) ;
						$pattern = '/'.quotemeta( $temp_ip ).'/i' ;
						if ( preg_match( $pattern, $ip ) )
							return true ;
					}
					else if ( preg_match( '/^\*/', $ips[$c] ) && !preg_match( '/\*$/', $ips[$c] ) && !$theforce )
					{
						$temp_ip = preg_replace( '/\*/', "", $ips[$c] ) ;
						$pattern = '/'.quotemeta( $temp_ip ).'$/i' ;
						if ( preg_match( $pattern, $ip ) )
							return true ;
					}
					else if ( !preg_match( '/^\*/', $ips[$c] ) && preg_match( '/\*$/', $ips[$c] ) && !$theforce )
					{
						$temp_ip = preg_replace( '/\*/', "", $ips[$c] ) ;
						$pattern = '/^'.quotemeta( $temp_ip ).'/i' ;
						if ( preg_match( $pattern, $ip ) )
							return true ;
					}
					else
					{
						if ( $ips[$c] == $ip )
							return true ;
					}
				}
			}
		}
		return false ;
	}
?>
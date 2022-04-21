<?php
	if ( defined( 'API_Util_Format' ) ) { return ; }	
	define( 'API_Util_Format', true ) ;

	FUNCTION Util_Format_Sanatize( $string, $flag )
	{
		if ( !is_array( $string ) ) { $string = trim( preg_replace( "/â€/", "", $string ), "\x00" ) ; }
		switch ( $flag )
		{
			case ( "a" ):
				return ( is_array( $string ) ) ? $string : Array() ; break ;
			case ( "n" ):
				$varout = preg_replace( "/[^0-9.-]/i", "", $string ) ; if ( !$varout ) { $varout = 0 ; }
				return $varout ;
				break ;
			case ( "ln" ):
				$temp = preg_replace( "/[`\$*%=<>\(\)\[\]\|\{\}\/\\\]/i", "", $string ) ;
				$varout = ( $temp == "0" ) ? "" : $temp ; return $varout ; break ;
			case ( "lns" ):
				return preg_replace( "/[^a-z0-9.:\-]/i", "", $string ) ; break ;
			case ( "lnss" ):
				return preg_replace( "/[^a-z0-9_\-]/i", "", $string ) ; break ;
			case ( "ip" ):
				return preg_replace( "/[^a-z0-9.:\-*]/i", "", $string ) ; break ;
			case ( "b64" ):
				return preg_replace( "/[^a-z0-9.+\/=\-_]/i", "", $string ) ; break ;
			case ( "eln" ):
				return preg_replace( "/[^a-z0-9+_.\-@]/i", "", Util_Format_Trim( $string ) ) ; break ;
			case ( "e" ):
				return strip_tags( Util_Format_Trim( $string ) ) ; break ;
			case ( "v" ):
				return preg_replace( "/(%20)|(%00)|(%3Cv%3E)|(<v>)/", "", Util_Format_Trim( $string ) ) ; break ;
			case ( "base_url" ):
				return preg_replace( "/[\$\!`\"<>';]/i", "", Util_Format_Trim( preg_replace( "/^hphp/i", "http", $string ) ) ) ; break ;
			case ( "url" ):
				return preg_replace( "/[\$\!`\"<>'\(\); ]/i", "", Util_Format_Trim( preg_replace( "/^hphp/i", "http", $string ) ) ) ; break ;
			case ( "title" ):
				return preg_replace( "/[`\$=\!<>]/i", "", Util_Format_Trim( preg_replace( "/^hphp/i", "http", $string ) ) ) ; break ;
			case ( "htmltags" ):
				return Util_Format_ConvertTags( $string ) ; break ;
			case ( "timezone" ):
				return preg_replace( "/['`?\$*%=<>\(\)\[\]\|\{\}\\\]/i", "", $string ) ; break ;
			case ( "notags" ):
				return strip_tags( $string ) ; break ;
			case ( "noscripts" ): {
				$string = preg_replace( "/<script(.*?)\/script>/i", "", $string ) ;
				return preg_replace( "/<svg(.*?)>/i", "&lt;svg$1&gt;", $string ) ; }
			case ( "query" ):
				return $string ; break ;
			default:
				return $string ;
		}
	}

	FUNCTION Util_Format_URL( $string )
	{
		return preg_replace( "/^http/i", "hphp", $string ) ;
	}

	FUNCTION Util_Format_Trim( $string )
	{
		return preg_replace( "/(\r\n)|(\r)|(\n)/", "", $string ) ;
	}

	FUNCTION Util_Format_ConvertTags( $string )
	{
		$string = preg_replace( "/>/", "&gt;", $string ) ;
		return preg_replace( "/</", "&lt;", $string ) ;
	}

	FUNCTION Util_Format_ConvertQuotes( $string )
	{
		$string = preg_replace( "/'/", "&#39;", $string ) ;
		return preg_replace( "/(\")|(%22)/", "&#34;", $string ) ;
	}

	FUNCTION Util_Format_StripQuotes( $string )
	{
		return preg_replace( "/[\"']/", "", $string ) ;
	}

	FUNCTION Util_Format_Duration( $duration, $min_sec = 0 )
	{
		$string = "" ;
		$seconds = floor( $duration ) ;

		$minutes = floor( $seconds/60 ) ;
		$hours = floor( $minutes/60 ) ;
		if ( $hours )
		{
			$minutes = floor( ( $duration - (60*60*$hours) )/60 ) ;
			$string = "$hours hr $minutes min" ;
		}
		else if ( $minutes )
		{
			$seconds = floor( $duration - ($minutes*60) ) ;
			if ( $min_sec && $seconds )
				$string = "$minutes min $seconds sec" ;
			else
				$string = "$minutes min" ; // simplified for narrow rows
		}
		else if ( $seconds ) { $string = "$seconds sec" ; }
		return $string ;
	}

	FUNCTION Util_Format_GetVar( $varname, $method = "" )
	{
		$varout = 0 ;
		if ( isset( $_POST[$varname] ) )
			$varout = $_POST[$varname] ;
		else if ( isset( $_GET[$varname] ) )
			$varout = $_GET[$varname] ;
		if ( !is_array( $varout ) )
			$varout = stripslashes( $varout ) ;
		return $varout ;
	}

	FUNCTION Util_Format_GetOS( $agent, $ckpad = false )
	{
		global $CONF ;
		if ( !defined( 'API_Util_Mobile' ) )
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Mobile_Detect.php" ) ;

		$mobile_detect = new Mobile_Detect ;
		// tablets are considered mobile devices
		if ( $mobile_detect->isMobile() )
		{
			$os = 5 ;
			if ( $ckpad && $mobile_detect->isTablet() )
			{
				if ( $mobile_detect->isiPad() )
					$os = 3 ;
			}
		}
		else if ( preg_match( "/Windows/i", $agent ) ) { $os = 1 ; }
		else if ( preg_match( "/Mac/i", $agent ) ) { $os = 2 ; }
		else { $os = 4 ; }

		if ( preg_match( "/MSIE/i", $agent ) ) { $browser = 1 ; }
		else if ( preg_match( "/(Edge)|(Edg)/i", $agent ) ) { $browser = 1 ; }
		else if ( preg_match( "/Firefox/i", $agent ) ) { $browser = 2 ; }
		else if ( preg_match( "/Chrome/i", $agent ) ) { $browser = 3 ; }
		else if ( preg_match( "/Safari/i", $agent ) ) { $browser = 4 ; }
		else if ( preg_match( "/Trident/i", $agent ) ) { $browser = 1 ; }
		else { $browser = 6 ; } return Array( $os, $browser ) ;
	}

	FUNCTION Util_Format_RandomString( $length = 5, $chars = '23456789abcdeghjkmnpqrstuvwxyz')
	{
		$charLength = strlen($chars)-1 ;

		$random_string = "" ;
		for ( $c = 0 ; $c < $length ; $c++ )
			$random_string .= $chars[mt_rand(0,$charLength)] ;
		return $random_string ;
	}

	FUNCTION Util_Format_DEBUG( $string, $thefile = "" )
	{
		global $CONF ; $now = time() ;
		$script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? $_SERVER['SCRIPT_NAME'] : "" ;
		if ( $thefile ) { $thefile = Util_Format_Sanatize( $thefile, "ln" ) ; $log_file = "$CONF[CONF_ROOT]/$thefile" ; }
		else { $log_file = "$CONF[CONF_ROOT]/debug.txt" ; }
		if ( is_writeable( $CONF["CONF_ROOT"] ) ) { file_put_contents( $log_file, $now." -> ".$script_name." -> ".$string."\n", FILE_APPEND ) ; }

		/*******************************************/
		/*
		$debug_backtrace = debug_backtrace() ; $debug_caller = array_shift( $debug_backtrace ) ;
		$path_parts = pathinfo( $debug_caller['file'] ) ;
		$debug_file = $path_parts['basename'] ;
		$debug_line = $debug_caller['line'] ;
		$debug_backtrace_string = " { $debug_file , $debug_line } " ;
		*/
		/*******************************************/
	}

	FUNCTION Util_Format_DEBUG_DBQUERIES( &$dbh )
	{
		$query_string = "" ;
		if ( isset( $dbh['query_his'] ) ) { for ( $c = 0; $c < count( $dbh['query_his'] ); ++$c ) { $query_string .= $dbh['query_his'][$c]."\r\n" ; } }
		return $query_string ;
	}

	FUNCTION Util_Format_Get_Vars( &$dbh )
	{
		$query = "SELECT * FROM p_vars LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ; return $data ;
		} return false ;
	}

	FUNCTION Util_Format_TableFirstCreated( &$dbh,
								$table )
	{
		if ( $table == "" )
			return time() ;

		$sdates = Array() ;
		$sdates["p_market_c"] = true ;

		LIST( $table ) = database_mysql_quote( $dbh, $table ) ;
		$field = ( isset( $sdates[$table] ) ) ? "sdate" : "created" ;

		$query = "SELECT $field FROM $table ORDER BY $field ASC LIMIT 1" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $data[$field] ) )
				return $data[$field] ;
		}
		return time() ;
	}

	FUNCTION Util_Format_Update_TimeStamp( &$dbh, $ts_table, $now )
	{
		if ( !preg_match( "/^(clean)|(clear)|(queue)$/", $ts_table ) ) { return false ; }
		LIST( $now ) = database_mysql_quote( $dbh, $now ) ;

		$query = "UPDATE p_vars SET ts_$ts_table = $now" ;
		database_mysql_query( $dbh, $query ) ;
		if ( $dbh[ 'ok' ] )
			return true ;
		return false ;
	}

	FUNCTION Util_Format_ExplodeString( $delim, $string )
	{
		$output = explode( $delim, $string ) ;
		for ( $c = 0; $c < count( $output ); ++$c )
		{
			if ( !$output[$c] ) { unset( $output[$c] ) ; }
		} return $output ;
	}

	FUNCTION Util_Format_SetCookie( $name, $value, $expire, $path, $domain, $secure, $samesite = "None" )
	{
		global $CONF ; global $CONF_EXTEND ;
		if ( isset( $CONF_EXTEND ) && $CONF_EXTEND ) { $path = "{$path}{$CONF_EXTEND}/" ; }
		$script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? $_SERVER['SCRIPT_NAME'] : "" ;
		$external = ( preg_match( "/footprints.php/i", $script_name ) || preg_match( "/status.php/i", $script_name ) ) ? 1 : 0 ;
		if ( !preg_match( "/^https/i", $CONF['BASE_URL'] ) )
			setcookie( $name, $value, $expire, $path, $domain, false ) ;
		else
		{
			// internal cookies SameSite not required
			// HEADER cookie method causes issues on Mobile App
			if ( !$external )
			{
				// no secure flag (false) to allow cookies to be passed if website is HTTP but PHP Live! is HTTPS
				setcookie( $name, $value, $expire, $path, $domain, false ) ;
			}
			else
			{
				$header = 'Set-Cookie:' ;
				$header .= rawurlencode($name) . '=' . rawurlencode($value) . ';' ;
				if ( $expire != -1 )
					$header .= 'expires=' . gmdate('D, d-M-Y H:i:s T', $expire) . ';' ;
				$header .= 'path=' . $path . ';' ;
				if ( $domain )
					$header .= 'domain=' . rawurlencode($domain) . ';' ;
				$header .= 'secure;' ; // required here because of SameSite flag
				$header .= 'httponly;' ;
				$header .= 'SameSite=' . $samesite . ';' ;
				HEADER( $header, false ) ;
			}
		}
	}

	FUNCTION Util_Format_CleanDeptOnline( $deptid, $opid )
	{
		global $CONF ;
		if ( is_dir( $CONF["CHAT_IO_DIR"] ) )
		{
			if ( is_numeric( $deptid ) && is_numeric( $opid ) )
			{
				$dir_files = glob( "$CONF[CHAT_IO_DIR]/online_".$deptid."_*", GLOB_NOSORT ) ;
				$total_dir_files = count( $dir_files ) ;
				if ( $total_dir_files )
				{
					for ( $c = 0; $c < $total_dir_files; ++$c )
					{
						if ( $dir_files[$c] && is_file( $dir_files[$c] ) && preg_match( "/online_{$deptid}_{$opid}.info/i", $dir_files[$c] ) )
							@unlink( $dir_files[$c] ) ;
					}
				}
			}
			if ( is_numeric( $deptid ) )
			{
				$dir_files = glob( "$CONF[CHAT_IO_DIR]/online_".$deptid."_*", GLOB_NOSORT ) ;
				$total_dir_files = count( $dir_files ) ;
				if ( $total_dir_files )
				{
					for ( $c = 0; $c < $total_dir_files; ++$c )
					{
						if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
							@unlink( $dir_files[$c] ) ;
					}
				}
			}
			else if ( is_numeric( $opid ) )
			{
				$dir_files = glob( "$CONF[CHAT_IO_DIR]/online_*", GLOB_NOSORT ) ;
				$total_dir_files = count( $dir_files ) ;
				if ( $total_dir_files )
				{
					for ( $c = 0; $c < $total_dir_files; ++$c )
					{
						if ( $dir_files[$c] && is_file( $dir_files[$c] ) && preg_match( "/online_(.*?)_{$opid}.info/i", $dir_files[$c] ) )
							@unlink( $dir_files[$c] ) ;
					}
				}
			}
			else
			{
				$dir_files = glob( "$CONF[CHAT_IO_DIR]/online*", GLOB_NOSORT ) ;
				$total_dir_files = count( $dir_files ) ;
				if ( $total_dir_files )
				{
					for ( $c = 0; $c < $total_dir_files; ++$c )
					{
						if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
							@unlink( $dir_files[$c] ) ;
					}
				}
			}
		}
	}

	FUNCTION Util_Format_CleanIcons( $deptid )
	{
		global $CONF ;
		if ( is_dir( $CONF["CONF_ROOT"] ) && is_numeric( $deptid ) )
		{
			$dir_files = glob( "$CONF[CONF_ROOT]/icon_online_".$deptid.".*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
						@unlink( $dir_files[$c] ) ;
				}
			}
			$dir_files = glob( "$CONF[CONF_ROOT]/icon_offline_".$deptid.".*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
						@unlink( $dir_files[$c] ) ;
				}
			}
			$dir_files = glob( "$CONF[CONF_ROOT]/logo_".$deptid.".*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
						@unlink( $dir_files[$c] ) ;
				}
			}
		}
	}

	FUNCTION Util_Format_IsIPExcluded( $ip, $theforce )
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

	FUNCTION Util_Format_NOJS( $thestring )
	{
		$js_words = Array( "onabort", "onafterprint", "onanimationend", "onanimationiteration", "onanimationstart", "onbeforeprint", "onbeforeunload", "onblur", "oncanplay", "oncanplaythrough", "onchange", "onclick", "oncontextmenu", "oncopy", "oncut", "ondblclick", "ondrag", "ondragend", "ondragenter", "ondragleave", "ondragover", "ondragstart", "ondrop", "ondurationchange", "onended", "onerror", "onfocus", "onfocusin", "onfocusout", "onfullscreenchange", "onfullscreenerror", "onhashchange", "oninput", "oninvalid", "onkeydown", "onkeypress", "onkeyup", "onload", "onloadeddata", "onloadedmetadata", "onloadstart", "onmessage", "onmousedown", "onmouseenter", "onmouseleave", "onmousemove", "onmouseover", "onmouseout", "onmouseup", "onmousewheel", "onoffline", "ononline", "onopen", "onpagehide", "onpageshow", "onpaste", "onpause", "onplay", "onplaying", "onpopstate", "onprogress", "onratechange", "onresize", "onreset", "onscroll", "onsearch", "onseeked", "onseeking", "onselect", "onshow", "onstalled", "onstorage", "onsubmit", "onsuspend", "ontimeupdate", "ontoggle", "ontouchcancel", "ontouchend", "ontouchmove", "ontouchstart", "ontransitionend", "onunload", "onvolumechange", "onwaiting", "onwheel" ) ;
		for ( $c = 0; $c < count( $js_words ); ++$c )
		{
			$js_word = substr( $js_words[$c], 1 ) ;
			$thestring = preg_replace( "/$js_words[$c]/i", "&#111;$js_word", $thestring ) ;
		} $thestring = preg_replace( "/<script/i", "<&#115;cript", $thestring ) ;
		$thestring = preg_replace( "/javascript/i", "&#107;avascript", $thestring ) ; return $thestring ;
	}
	FUNCTION Util_Format_base64_decode_array( &$value, $index ) { $value = base64_decode( $value ) ; }
?>
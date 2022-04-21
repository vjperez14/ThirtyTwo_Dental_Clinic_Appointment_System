<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	// STANDARD header for Setup
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	/****************************************/

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	if ( $action === "fetch_policy" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
		$gdpr_message = $deptvars["gdpr_msg"] ;
		if ( preg_match( "/-_-/", $gdpr_message ) ) { LIST( $text_checkbox, $gdpr_message ) = explode( "-_-", $gdpr_message ) ; }

		if ( isset( $dbh ) && isset( $dbh['con'] ) )
		database_mysql_close( $dbh ) ;

		HEADER( "Content-Type: text/plain" ) ;
		print $gdpr_message ; exit ;
	}
	else if ( $action === "fetch_online_pics" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
		else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
		$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
		if ( isset( $dept_themes[$deptid] ) && $deptid ) { $theme = $dept_themes[$deptid] ; }
		else { $theme = $CONF["THEME"] ; }

		$dir_files = glob( $CONF["CONF_ROOT"]."/profile_*", GLOB_NOSORT ) ; $dir_files_hash = Array() ;
		$total_dir_files = count( $dir_files ) ;
		if ( $total_dir_files )
		{
			for ( $c = 0; $c < $total_dir_files; ++$c )
			{
				if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
				{
					$opid = preg_replace( "/(.*?)profile_/", "", $dir_files[$c] ) ;
					$opid = preg_replace( "/\.(.*?)$/", "", $opid ) ;
					$profile_src = $CONF["UPLOAD_HTTP"] . "/profile_" . preg_replace( "/(.*?)profile_/", "", $dir_files[$c] ) . "?" . filemtime( $dir_files[$c] ) ;
					$dir_files_hash[$opid] = $profile_src ;
				}
			}
		}

		$online_operators = Depts_get_DeptOps_OpsOnlinePic( $dbh, $deptid, 1 ) ; $online_operators_hash = Array() ;
		shuffle( $online_operators ) ;
		for ( $c = 0; $c < count( $online_operators ); ++$c )
		{
			$opinfo = $online_operators[$c] ;
			$opid = $opinfo["opID"] ;
			$online_operators_hash[$opid] = $opinfo ;
		}
		$total = 0 ;
		$json_data = "json_data = { \"status\": 1, \"profile_pics\": [  " ;
		foreach ( $online_operators_hash as $opid => $opinfo )
		{
			$profile_src = ( isset( $dir_files_hash[$opid] ) ) ? rawurlencode( $dir_files_hash[$opid] ) : Util_Upload_GetLogo( "profile", $opid ) ;
			$name = rawurlencode( $opinfo["name"] ) ;
			$json_data .= "{ \"opid\": \"$opid\", \"name\": \"$name\", \"pic\": \"$profile_src\" }," ;
			++$total ;
			if ( $total >= 3 ) { break 1 ; }
		}
		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;
	}

	if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
	$json_data = Util_Format_Trim( $json_data ) ;
	$json_data = preg_replace( "/\t/", "", $json_data ) ;
	HEADER('Content-Type: text/plain; charset=utf-8') ;
	print $json_data ; exit ;
?>
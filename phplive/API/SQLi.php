<?php
	/////////////////////////////////////////
	// PHP Live! Database API
	/////////////////////////////////////////
	if ( defined( 'API_Util_SQL' ) ) { return ; }	
	define( 'API_Util_SQL', true ) ; if ( !isset( $dbh ) ) { $dbh = Array() ; $dbh['query_his'] = Array() ; $dbh['free'] = ( isset( $VARS_MYSQL_FREE_RESULTS ) && is_numeric( $VARS_MYSQL_FREE_RESULTS ) && $VARS_MYSQL_FREE_RESULTS ) ? 1 : 0 ; }

	if ( !isset( $connection ) )
	{
		if ( !function_exists('mysqli_connect') )
		{
			if ( !defined( 'API_Util_Error' ) )
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
			ErrorHandler( 614, "MySQLi Extension Not Enabled", $PHPLIVE_FULLURL, 0, Array() ) ; exit ;
		}
		$temp_host = $CONF["SQLHOST"] ;
		if ( isset( $CONF["SQLPORT"] ) && $CONF["SQLPORT"] ) { $temp_host = $CONF["SQLHOST"].":".$CONF["SQLPORT"] ; }
		$connection = new mysqli( $temp_host, $CONF["SQLLOGIN"], stripslashes( $CONF["SQLPASS"] ) ) ;
		if ( $connection->select_db( $CONF["DATABASE"] ) ) { $dbh['con'] = $connection ; $dbh['qc'] = 0 ; $dbh['pc'] = 0 ; }
		else
		{
			if ( !defined( 'API_Util_Error' ) )
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
			ErrorHandler( 600, "Could not connect to database or database does not exist.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ;
		}
	}

	FUNCTION database_mysql_query( &$dbh, $query )
	{
		global $PHPLIVE_URI ; global $VARS_DB_ERROR_LOG_FILE ; global $VARS_MYSQL_THROTTLE_PAUSE ; global $CONF_EXTEND ;
		$dbh['ok'] = 0 ; $dbh['result'] = 0 ; $dbh['error'] = "None" ;  $dbh['query'] = $query ; ++$dbh['qc'] ; ++$dbh['pc'] ; $dbh['query_his'][] = $query ;
		$result = $dbh['con']->query( $query ) ;
		if ( $result ) { $dbh['result'] = $result ; $dbh['ok'] = 1 ; $dbh['error'] = "None" ; }
		else {
			$dbh['result'] = 0 ; $dbh['ok'] = 0 ; $dbh['error'] = $dbh['con']->error ;
			$log_file = ( isset( $VARS_DB_ERROR_LOG_FILE ) && $VARS_DB_ERROR_LOG_FILE ) ? $VARS_DB_ERROR_LOG_FILE : "" ;
			if ( $log_file && function_exists( "Util_Format_DEBUG" ) ) { Util_Format_DEBUG( "[".time()."] $PHPLIVE_URI -> ".$query." -> ".$dbh['error']."\r\n", $log_file  ) ; }
		} if ( isset( $VARS_MYSQL_THROTTLE_PAUSE ) && is_numeric( $VARS_MYSQL_THROTTLE_PAUSE ) && ( $dbh['pc'] >= $VARS_MYSQL_THROTTLE_PAUSE ) ) { sleep( 2 ) ; $dbh['pc'] = 0 ; }
	}

	FUNCTION database_mysql_fetchrow( &$dbh )
	{
		$result = $dbh['result']->fetch_array( MYSQLI_ASSOC ) ;
		return $result ;
	}

	FUNCTION database_mysql_fetchrowa( &$dbh )
	{
		$result = $dbh['result']->fetch_assoc() ;
		return $result ;
	}

	FUNCTION database_mysql_insertid( &$dbh )
	{
		$id = $dbh['con']->insert_id ;
		return $id ;
	}

	FUNCTION database_mysql_nresults( &$dbh )
	{
		$total = $dbh['con']->affected_rows ;
		return $total ;
	}

	FUNCTION database_mysql_quote( &$dbh )
	{
		$output = Array() ;
		for ( $i = 1; $i < func_num_args(); $i++ )
			$output[] = $dbh['con']->real_escape_string( stripslashes( func_get_arg( $i ) ) ) ;
		return $output ;
	}

	FUNCTION database_mysql_close( &$dbh )
	{
		// Util_Format_DEBUG( memory_get_usage()/1024/1024 ) ;
		if ( isset( $dbh['con'] ) )
		{
			if ( $dbh['free'] && is_object( $dbh['result'] ) ) { $dbh['result']->free() ; }
			$dbh['con']->close() ;
			return true ;
		}
		return false ;
	}

	FUNCTION database_mysql_version( &$dbh )
	{
		database_mysql_query( $dbh, "SELECT version() AS version" ) ;
		$output = database_mysql_fetchrow( $dbh ) ;
		return $output["version"] ;
	}

	FUNCTION database_mysql_old( &$dbh )
	{
		return preg_match( "/^(4.0|3)/", database_mysql_version( $dbh ) ) ? 1 : 0 ;
	}

	if ( isset( $CONF['UTF_DB'] ) )
	{
		//$query = "SET NAMES 'utf8'" ;
		//database_mysql_query( $dbh, $query ) ;
	}

?>
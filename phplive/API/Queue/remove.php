<?php
	if ( defined( 'API_Queue_remove' ) ) { return ; }
	define( 'API_Queue_remove', true ) ;

	FUNCTION Queue_remove_Queue( &$dbh,
			$ces )
	{
		if ( $ces == "" )
			return false ;

		LIST( $ces ) = database_mysql_quote( $dbh, $ces ) ;

		$query = "DELETE FROM p_queue WHERE ces = '$ces'" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}

	FUNCTION Queue_remove_ExpiredQueues( &$dbh )
	{
		global $VARS_JS_REQUESTING ;
		$expired = time() - ( $VARS_JS_REQUESTING * 3 ) ;

		$query = "DELETE FROM p_queue WHERE updated < $expired" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}

	FUNCTION Queue_remove_ExpiredQueueLog( &$dbh )
	{
		$VARS_QUEUE_LOG_EXPIRE = 90 ;
		$expired = time() - ( 86400 * $VARS_QUEUE_LOG_EXPIRE ) ;

		$query = "DELETE FROM p_queue_log WHERE created < $expired" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}
?>
<?php
	if ( defined( 'API_Setup_put' ) ) { return ; }
	define( 'API_Setup_put', true ) ;

	FUNCTION Setup_put_Account( &$dbh,
					$adminid,
					$created,
					$login,
					$password,
					$email,
					$access )
	{
		if ( !is_numeric( $adminid ) || ( $created == "" ) || ( $login == "" ) || ( $password == "" )
			|| ( $email == "" ) )
			return false ;

		LIST( $adminid, $login, $password, $email, $access ) = database_mysql_quote( $dbh, $adminid, $login, $password, $email, $access ) ;

		if ( $adminid )
			$query = "UPDATE p_admins SET login = '$login', password = '$password', email = '$email', access = '$access' WHERE adminID = $adminid" ;
		else
			$query = "INSERT INTO p_admins VALUES ( NULL, $created, 0, -1, '', '$login', '$password', '$email', '$access' )" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
			return true ;

		return false ;
	}

?>

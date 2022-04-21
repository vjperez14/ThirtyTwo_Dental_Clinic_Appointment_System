<?php
	if ( defined( 'API_Ops_put' ) ) { return ; }
	define( 'API_Ops_put', true ) ;

	FUNCTION Ops_put_Op( &$dbh,
					$opid,
					$status,
					$mapper,
					$rate,
					$sms,
					$op2op,
					$traffic,
					$viewip,
					$nchats,
					$maxc,
					$maxco,
					$login,
					$password,
					$name,
					$email,
					$tag,
					$peer,
					$upload,
					$view_chats,
					$dept_offline )
	{
		if ( ( $login == "" ) || !is_numeric( $status ) || ( $name == "" ) || ( $email == "" ) )
			return "Blank input is invalid." ;

		global $CONF ; global $VALS ;
		LIST( $login, $opid ) = database_mysql_quote( $dbh, $login, $opid ) ;

		$query = "SELECT * FROM p_operators WHERE opID = $opid" ;
		database_mysql_query( $dbh, $query ) ;
		$operator = database_mysql_fetchrow( $dbh ) ;

		$operator_ = Ops_get_ext_OpInfoByLogin( $dbh, $login ) ;
		if ( isset( $operator_["login"] ) )
		{
			if ( isset( $operator["opID"] ) && ( $operator["opID"] == $operator_["opID"] ) )
			{
				// it's ok to match
			} else { return "Operator login ($login) is already in use." ; }
		}

		if ( isset( $operator["opID"] ) )
		{
			// placeholder password masked "php-live-support" indication of password has not changed.  keep original password
			if ( $password == "1655648fa5d34211f0232944dfe7a2d3" )
				$password = $operator["password"] ;

			if ( $sms && !$operator["sms"] )
				$sms = time()-60 ;
			else if ( $sms )
				$sms = $operator["sms"] ;
		}
		else
			$sms = ( $sms ) ? time()-60 : 0 ;

		LIST( $opid, $status, $mapper, $rate, $sms, $op2op, $traffic, $viewip, $nchats, $maxc, $maxco, $password, $name, $email, $tag, $peer, $view_chats, $dept_offline, $upload ) = database_mysql_quote( $dbh, $opid, $status, $mapper, $rate, $sms, $op2op, $traffic, $viewip, $nchats, $maxc, $maxco, $password, $name, $email, $tag, $peer, $view_chats, $dept_offline, $upload ) ;

		// NOTE to self: bot direct entry at code_autostart.php
		if ( isset( $operator["opID"] ) )
			$query = "UPDATE p_operators SET mapper = $mapper, rate = $rate, op2op = $op2op, traffic = $traffic, viewip = $viewip, nchats = $nchats, maxc = $maxc, maxco = $maxco, tag = $tag, peer = $peer, upload = '$upload', sms = $sms, view_chats = $view_chats, dept_offline = $dept_offline, login = '$login', password = '$password', name = '$name', email = '$email' WHERE opID = $opid" ;
		else
		{
			if ( !$opid ) { $opid = "NULL" ; }
			$query = "INSERT INTO p_operators VALUES ( $opid, 0, 0, 0, $mapper, 0, 0, $rate, $op2op, $traffic, $viewip, $nchats, $maxc, $maxco, 0, $tag, $peer, '$upload', '', '', 0, $sms, '', '$login', '$password', '$name', '$email', 0, 0, $view_chats, $dept_offline, 'default' )" ;
		}

		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			if ( $opid == "NULL" )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

				$opid = database_mysql_insertid( $dbh ) ;
				$op_sounds = ( isset( $VALS["op_sounds"] ) && $VALS["op_sounds"] ) ? unserialize( $VALS["op_sounds"] ) : Array() ;
				$op_sounds[$opid] = Array( "default", "default" ) ;
				Util_Vals_WriteToFile( "op_sounds", serialize( $op_sounds ) ) ;
			}

			if ( $status && is_file( "$CONF[TYPE_IO_DIR]/$opid.locked" ) )
			{ @unlink( "$CONF[TYPE_IO_DIR]/$opid.locked" ) ; }
			else if ( !$status && !is_file( "$CONF[TYPE_IO_DIR]/$opid.locked" ) )
			{ touch( "$CONF[TYPE_IO_DIR]/$opid.locked" ) ; }
			return $opid ;
		}
		else
			return "DB Error: $dbh[error]" ;
	}

	FUNCTION Ops_put_OpDept( &$dbh,
					$opid,
					$deptid,
					$visible,
					$status )
	{
		if ( ( $opid == "" ) || ( $deptid == "" ) )
			return false ;

		LIST( $opid, $deptid, $visible, $status ) = database_mysql_quote( $dbh, $opid, $deptid, $visible, $status ) ;

		$query = "SELECT count(*) AS total FROM p_dept_ops WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$data = database_mysql_fetchrow( $dbh ) ;
		$display = $data["total"] + 1 ; // add 1 because it starts at ZERO

		$query = "INSERT IGNORE INTO p_dept_ops VALUES ( $deptid, $opid, $display, $visible, $status, 0 )" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}

	FUNCTION Ops_put_OpVars( &$dbh,
					$opid )
	{
		if ( $opid == "" )
			return false ;

		LIST( $opid ) = database_mysql_quote( $dbh, $opid ) ;

		$query = "INSERT INTO p_op_vars VALUES( $opid, 0, 1, 0, 0, 1, 1, 0, 1, 0, 0, 0, 0, '', '', '' )" ;
		database_mysql_query( $dbh, $query ) ;

		return true ;
	}
?>
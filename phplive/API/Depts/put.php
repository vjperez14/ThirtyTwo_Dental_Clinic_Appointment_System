<?php
	if ( defined( 'API_Depts_put' ) ) { return ; }
	define( 'API_Depts_put', true ) ;

	FUNCTION Depts_put_Department( &$dbh,
					$deptid,
					$name,
					$email,
					$visible,
					$queue,
					$rtype,
					$rtime,
					$savem,
					$vupload,
					$ctimer,
					$smtp,
					$tshare,
					$texpire,
					$lang )
	{
		if ( ( $name == "" ) || ( $email == "" )  || ( $rtime == "" ) || ( $lang == "" ) || !is_numeric( $deptid ) )
			return false ;
		global $CONF ;
		global $LANG ;

		LIST( $name, $deptid ) = database_mysql_quote( $dbh, $name, $deptid ) ;

		$query = "SELECT * FROM p_departments WHERE deptID = $deptid" ;
		database_mysql_query( $dbh, $query ) ;
		$department = database_mysql_fetchrow( $dbh ) ;

		if ( isset( $department["deptID"] ) )
		{
			$this_smtp = $department["smtp"] ;
			$smtp_string = ( $this_smtp != $smtp ) ? "smtp = '$smtp'," : "" ;

			$msg_greet = $department["msg_greet"] ;
			$msg_offline = $department["msg_offline"] ;
			$msg_busy = $department["msg_busy"] ;

			if ( $department["lang"] != $lang )
			{
				// include the previous language pack to see if values updates
				$LANG_BACKUP = $LANG ;
				include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".$department["lang"].".php" ) ;

				if ( $department["msg_greet"] == $LANG["CHAT_NOTIFY_LOOKING_FOR_OP"] )
				{
					$msg_greet = $LANG_BACKUP["CHAT_NOTIFY_LOOKING_FOR_OP"] ;
				}
				if ( $department["msg_offline"] == $LANG["CHAT_NOTIFY_OP_NOT_FOUND"] )
				{
					$msg_offline = $LANG_BACKUP["CHAT_NOTIFY_OP_NOT_FOUND"] ;
				}
				if ( $department["msg_busy"] == $LANG["CHAT_NOTIFY_OP_NOT_FOUND"] )
				{
					$msg_busy = $LANG_BACKUP["CHAT_NOTIFY_OP_NOT_FOUND"] ;
				}
			}

			$msg_transcript = $department["msg_email"] ;
		}
		else
		{
			$deptid = "NULL" ;
			$smtp_string = "" ;

			$msg_greet = $LANG["CHAT_NOTIFY_LOOKING_FOR_OP"] ;
			$msg_offline = $msg_busy = $LANG["CHAT_NOTIFY_OP_NOT_FOUND"] ;
			$msg_transcript = "Hi %%visitor%%,\r\n\r\nHere is the complete chat transcript for your reference:\r\n\r\n%%transcript%%\r\n\r\n==========\r\n\r\n%%operator%%\r\n%%op_email%%\r\n" ;
		}

		LIST( $deptid, $email, $visible, $queue, $rtype, $rtime, $savem, $vupload, $ctimer, $smtp, $tshare, $texpire, $lang, $msg_greet, $msg_offline, $msg_busy, $transcript ) = database_mysql_quote( $dbh, $deptid, $email, $visible, $queue, $rtype, $rtime, $savem, $vupload, $ctimer, $smtp, $tshare, $texpire, $lang, $msg_greet, $msg_offline, $msg_busy, $msg_transcript ) ;

		$query = "INSERT INTO p_departments VALUES ( $deptid, $visible, 255, $queue, $tshare, $texpire, 1, 1, 1, 1, 0, 0, $rtype, $rtime, $savem, '$vupload', $ctimer, '', '$smtp', '$lang', '$name', '$email', '', '', 0, '$msg_greet', '$msg_offline', '$msg_busy', '$transcript' ) ON DUPLICATE KEY UPDATE name = '$name', email = '$email', visible = $visible, queue = $queue, rtype = $rtype, rtime = $rtime, savem = $savem, vupload = '$vupload', ctimer = $ctimer, tshare = $tshare, texpire = $texpire, $smtp_string lang = '$lang', msg_greet = '$msg_greet', msg_offline = '$msg_offline', msg_busy = '$msg_busy'" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$id = ( $deptid && ( $deptid != "NULL" ) ) ? $deptid : database_mysql_insertid( $dbh ) ;
			$query = "UPDATE p_dept_ops SET visible = '$visible' WHERE deptID = $id" ;
			database_mysql_query( $dbh, $query ) ;
			return $id ;
		}

		return false ;
	}

	FUNCTION Depts_put_DeptGroups( &$dbh,
					$groupid,
					$name,
					$lang,
					$deptids )
	{
		if ( !is_numeric( $groupid ) || ( $name == "" ) || ( $lang == "" ) || ( $deptids == "" ) )
			return false ;

		LIST( $groupid, $name, $lang, $deptids ) = database_mysql_quote( $dbh, $groupid, $name, $lang, $deptids ) ;

		$query = "REPLACE INTO p_dept_groups VALUES ( $groupid, '$name', '$lang', '$deptids' )" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			return true ;
		}

		return false ;
	}
?>
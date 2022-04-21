<?php
	if ( defined( 'API_Chat_put_itr' ) ) { return ; }
	define( 'API_Chat_put_itr', true ) ;

	FUNCTION Chat_put_itr_Transcript( &$dbh,
					$ces,
					$status,
					$created,
					$ended,
					$deptid,
					$opid,
					$initiated,
					$op2op,
					$rating,
					$fsize,
					$vname,
					$vemail,
					$ip,
					$vis_token,
					$custom,
					$question,
					$formatted,
					$plain,
					$deptinfo,
					$deptvars )
	{
		if ( ( $ces == "" ) || ( $deptid == "" ) || ( $opid == "" ) || ( $fsize == "" )
			|| ( $ended == "" ) || ( $vname == "" ) || ( $ip == "" )
			|| ( $vis_token == "" ) || ( $formatted == "" ) || ( $plain == "" ) )
			return false ;

		global $CONF ; global $VALS ; global $VARS_OS ; global $VARS_BROWSER ;
		global $VARS_EXPIRED_TFOOT ; global $smtp_array ; global $action ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Email.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;

		$email_error = "" ;
		LIST( $ces, $status, $created, $ended, $deptid, $opid, $initiated, $op2op, $rating, $fsize, $vname, $vemail, $ip, $vis_token, $custom, $question, $formatted, $plain ) = database_mysql_quote( $dbh, $ces, $status, $created, $ended, $deptid, $opid, $initiated, $op2op, $rating, $fsize, $vname, $vemail, $ip, $vis_token, $custom, $question, $formatted, $plain ) ;

		// get initiated value from log because during transfer it resets the initiate flag
		$requestinfo_log = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;

		$transcript = Chat_ext_get_Transcript( $dbh, $ces ) ;
		$trans_exists = 1 ;
		if ( isset( $requestinfo_log["ces"] ) && !isset( $transcript["ces"] ) && ( $created != "null" ) )
		{
			$trans_exists = 0 ;
			$initiated = ( isset( $requestinfo_log["initiated"] ) ) ? $requestinfo_log["initiated"] : $initiated ;
			$tag = ( isset( $requestinfo_log["tag"] ) ) ? $requestinfo_log["tag"] : 0 ;
			$accepted_op = ( isset( $requestinfo_log["accepted_op"] ) ) ? $requestinfo_log["accepted_op"] : 0 ;
			$duration = $ended - $created ;

			if ( $requestinfo_log["md5_vis"] == "grc" ) { $vis_token = "grc" ; }

			$query = "UPDATE p_requests SET ended = $ended WHERE ces = '$ces'" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "UPDATE p_req_log SET duration = $duration, ended = $ended WHERE ces = '$ces'" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "INSERT INTO p_transcripts VALUES ( '$ces', $created, $ended, $deptid, $opid, $accepted_op, $initiated, $op2op, $rating, 0, $fsize, 0, $requestinfo_log[marketID], $tag, '$vname', '$vemail', '$ip', '$vis_token', '$requestinfo_log[custom]', '$question', '$formatted', '$plain' )" ;
			database_mysql_query( $dbh, $query ) ; $nresults = database_mysql_nresults( $dbh ) ;
			if ( $dbh[ 'ok' ] && $nresults && is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ) { @unlink( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ; }

			// clear istyping files
			$dir_files = glob( $CONF["TYPE_IO_DIR"]."/$ces"."*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
				}
			}
		} else if ( isset( $requestinfo_log["ces"] ) && isset( $transcript["ces"] ) && ( $created != "null" ) && is_file( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ) { @unlink( "$CONF[CHAT_IO_DIR]/{$ces}.txt" ) ; }

		/***** unregister autoloaders for SMTP autoloader *****/
		if ( function_exists( "spl_autoload_functions" ) )
		{
			$autoloader_functions = spl_autoload_functions() ;
			if ( is_array( $autoloader_functions ) )
			{
				foreach( $autoloader_functions as $autoloader_function ) { spl_autoload_unregister( $autoloader_function ) ; }
			}
		}
		/******************************************************/

		if ( $formatted && isset( $deptinfo["temail"] ) && isset( $requestinfo_log["custom"] ) )
		{
			if ( $status )
			{
				if ( !isset( $smtp_array ) && $deptinfo["smtp"] )
					$smtp_array = unserialize( Util_Functions_itr_Decrypt( $CONF["SALT"], $deptinfo["smtp"] ) ) ;

				$vemail_display = $vemail ;
				if ( $vemail == "null" ) { $vemail = "" ; $vemail_display = "" ; }

				if ( !isset( $opinfo ) )
				{
					$query = "SELECT * FROM p_operators WHERE opID = '$opid' LIMIT 1" ;
					database_mysql_query( $dbh, $query ) ;
					$opinfo = database_mysql_fetchrow( $dbh ) ;
				}

				$lang = $CONF["lang"] ;
				if ( $deptinfo["lang"] ) { $lang = $deptinfo["lang"] ; }
				include( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($lang, "ln").".php" ) ;

				$subject_visitor = $LANG["TRANSCRIPT_SUBJECT"]." $opinfo[name]" ;
				$subject_department = $LANG["TRANSCRIPT_SUBJECT"]." $vname" ;

				// override for emailing transcript
				if ( isset( $deptvars["trans_f_dept"] ) && $deptvars["trans_f_dept"] )
				{
					$from_name = $deptinfo["name"] ;
					$from_email = $deptinfo["email"] ;
				}
				else
				{
					$from_name = $opinfo["name"] ;
					$from_email = $opinfo["email"] ;
				}

				// stripslashes needed for \' to correctly detect certain patterns without the slash
				$message_trans = Util_Email_FormatTranscript( $ces, $deptinfo["msg_email"], $deptinfo["name"], $deptinfo["email"], $vname, $vemail, $opinfo["name"], $opinfo["email"], $requestinfo_log["custom"], stripslashes( $formatted ) ) ;
				if ( ( $created == "null" ) && $vemail )
				{
					$email_error = Util_Email_SendEmail( $from_name, $from_email, $vname, $vemail, $subject_visitor, $message_trans, "trans" ) ;
					$query = "UPDATE p_req_log SET vemail = '$vemail' WHERE ces = '$ces' AND vemail = 'null'" ;
					database_mysql_query( $dbh, $query ) ;
					$query = "UPDATE p_transcripts SET vemail = '$vemail' WHERE ces = '$ces' AND vemail = 'null'" ;
					database_mysql_query( $dbh, $query ) ;
				}
				if ( !$trans_exists )
				{
					$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
					if ( $opinfo["mapp"] && is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) )
					{
						include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;
						if ( isset( $mapp_array[$opid] ) ) { $arn = $mapp_array[$opid]["a"] ; $platform = $mapp_array[$opid]["p"] ; }
						if ( isset( $arn ) && $arn ) { Util_MAPP_Publish( $opid, "new_text", $platform, $arn, "$vname [".$LANG["TXT_DISCONNECT"]."]" ) ; }
					}
					// send transcript to visitor automatically if transferred chat resulted in leave a message
					if ( $requestinfo_log["status"] && ( $requestinfo_log["status_msg"] == 1 ) && preg_match( "/@/", $vemail ) )
					{
						$email_error = Util_Email_SendEmail( $from_name, $from_email, $vname, $vemail, $subject_visitor, $message_trans, "trans" ) ;
					}

					if ( $deptinfo["emailt"] && $deptinfo["emailt_bcc"] )
					{
						if ( !$vemail ) { $vname = $from_name ; $vemail = $from_email ; }
						$email_error = Util_Email_SendEmail( $from_name, $from_email, $vname, $vemail, $subject_visitor, $message_trans, "trans", Array($deptinfo["emailt"]) ) ;
					}
					else if ( $deptinfo["emailt"] && preg_match( "/@/", $deptinfo["emailt"] ) )
					{
						// additional copy of chat transcript to specified email address (department option)
						// use department name as the to name because only email address is available
						$email_error = Util_Email_SendEmail( $from_name, $from_email, $deptinfo["name"], $deptinfo["emailt"], $subject_department, $message_trans, "trans" ) ;
					}

					if ( $deptinfo["temaild"] )
					{
						// send copy to department email address
						$email_error = Util_Email_SendEmail( $from_name, $from_email, $deptinfo["name"], $deptinfo["email"], $subject_department, $message_trans, "trans" ) ;
					}

					if ( $deptinfo["aemail"] && preg_match( "/@/", $vemail ) )
					{
						// automatic sending of chat transcript to the visitor
						$email_error = Util_Email_SendEmail( $from_name, $from_email, $vname, $vemail, $subject_visitor, $message_trans, "trans" ) ;
					}
					if ( is_file( "$CONF[DOCUMENT_ROOT]/custom_code/new_chat_transcript.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/custom_code/new_chat_transcript.php" ) ; }
				}
			}
			if ( isset( $action ) && ( $action == "send_email_trans" ) ) { return $email_error ; }
			else { return true ; }
		}
		else if ( $trans_exists ) { return true ; }
		return false ;
	}
?>
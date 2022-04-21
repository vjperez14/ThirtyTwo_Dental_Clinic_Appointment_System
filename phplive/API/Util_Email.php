<?php
	if ( defined( 'API_Util_Email' ) ) { return ; }	
	define( 'API_Util_Email', true ) ;

	$ERROR_EMAIL = "" ;
	FUNCTION eMailErrorHandler($errno, $errstr, $errfile, $errline) { global $ERROR_EMAIL ; $ERROR_EMAIL = $errstr ; }
	FUNCTION Util_Email_SendEmail( $from_name, $from_email, $to_name, $to_email, $subject, $message, $extra, $bcc = Array() )
	{
		global $CONF ;
		global $smtp_array ;
		global $ERROR_EMAIL ;
		global $DMARC_DOMAINS ;

		$subject = stripslashes( preg_replace( "/-dollar-/", "\$", $subject ) ) ;
		$message = stripslashes( preg_replace( "/-dollar-/", "\$", $message ) ) ;
		$attachment_file = ( $extra && is_file( "$CONF[ATTACH_DIR]/$extra") ) ? $extra : "" ;

		LIST( $null, $domain ) = explode( "@", $from_email ) ;
		$dmarc = 0 ;

		if ( $extra == "offline" )
		{
			if ( function_exists( "dns_get_record" ) )
			{
				set_error_handler( function() { /* ignore errors */ } ) ;
				$dns_record = dns_get_record( "_dmarc.$domain", DNS_TXT ) ;
				restore_error_handler() ;
				if ( isset( $dns_record[0] ) && isset( $dns_record[0]["txt"] ) )
				{
					$dmarc_record = $dns_record[0]["txt"] ;
					if ( !$dmarc_record || preg_match( "/=none/i", $dmarc_record ) )
						$dmarc = 0 ;
					else
						$dmarc = 1 ;
				}
			}
			else
			{
				$DMARC_DOMAINS_DEFAULT = Array( "yahoo"=>1,"hotmail"=>1,"aol"=>1,"nist.gov"=>1,"haskell.com"=>1,"nvent.com"=>1,"martin-eng.com"=>1,"matrixit.net"=>1, "none.com"=>1,"getcruise.com"=>1,"otcbrunei.com"=>1,"upcmail.nl"=>1,"verizon.net"=>1,"amazon.com"=>1,"baml.com"=>1,"bankofamerica.com"=>1,"ml.com"=>1,"discovercard.com"=>1,"dropbox.com"=>1,"fidelity.com"=>1,"kickstarter.com"=>1,"southwest.com"=>1,"square.com"=>1,"uber.com"=>1,"visa.com"=>1,"wal-mart.com"=>1,"walmart.com"=>1,"wix.com"=>1,"adp.com"=>1,"aetna.com"=>1,"airbnb.com"=>1,"americanexpress.com"=>1,"aexp.com"=>1,"americangreetings.com"=>1,"applemusic.com"=>1,"box.com"=>1,"britishairways.com"=>1,"chase.com"=>1,"jpmchase.com"=>1,"citibank.com"=>1,"dhl.com"=>1,"evernote.com"=>1,"facebook.com"=>1,"fedex.com"=>1,"gap.com"=>1,"groupon.com"=>1,"instagram.com"=>1,"linkedin.com"=>1,"oldnavy.com"=>1,"paypal.com"=>1,"pinterest.com"=>1,"pch.com"=>1,"mail.rollingstone.com"=>1,"squarespace.com"=>1,"twitter.com"=>1,"ups.com"=>1,"ftc.gov"=>1,"senate.gov"=>1,"usps.gov"=>1,"usaa.com"=>1,"wachovia.com"=>1,"wellsfargo.com"=>1,"whatsapp.com"=>1 ) ;
				if ( !isset( $DMARC_DOMAINS ) ) { $DMARC_DOMAINS = $DMARC_DOMAINS_DEFAULT ; }
				else{ $DMARC_DOMAINS = array_merge( $DMARC_DOMAINS, $DMARC_DOMAINS_DEFAULT ) ; }
				if ( is_array( $DMARC_DOMAINS ) )
				{
					foreach( $DMARC_DOMAINS as $thisdomain => $null ) { if ( preg_match( "/^$thisdomain/i", $domain ) ) { $dmarc = 1 ; break ; } }
				}
			}
		}

		$subject_new = '=?UTF-8?B?'.base64_encode( $subject ).'?=' ;
		// SMTP
		//ini_set( SMTP, "localhost" ) ;

		if ( $to_email )
		{
			if ( isset( $smtp_array ) && isset( $smtp_array["host"] ) )
			{
				$CONF["SMTP_HOST"] = $smtp_array["host"] ;
				$CONF["SMTP_LOGIN"] = $smtp_array["login"] ;
				$CONF["SMTP_PASS"] = $smtp_array["pass"] ;
				$CONF["SMTP_PORT"] = $smtp_array["port"] ;
				$CONF["SMTP_CRYPT"] = ( isset( $smtp_array["crypt"] ) ) ? $smtp_array["crypt"] : "" ;
				$CONF["SMTP_API"] = isset( $smtp_array["api"] ) ? $smtp_array["api"] : "" ;
				$CONF["SMTP_DOMAIN"] = isset( $smtp_array["domain"] ) ? $smtp_array["domain"] : "" ;
			}

			set_error_handler('eMailErrorHandler') ;
			if ( !isset( $CONF["SMTP_PASS"] ) && is_file( "$CONF[DOCUMENT_ROOT]/addons/smtp/API/Util_Extra.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/smtp/API/Util_Extra.php" ) ; }
			if ( isset( $CONF["SMTP_PASS"] ) )
			{
				if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/smtp/API/Util_Email_SMTP.php" ) )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/addons/smtp/API/Util_Email_SMTP.php" ) ;

					$error = Util_Email_SMTP_SwiftMailer( $to_email, $to_name, $from_email, $from_name, $subject_new, $message, $dmarc, $attachment_file, $bcc ) ;
					if ( defined( 'API_Util_Error' ) ) { set_error_handler( "ErrorHandler" ) ; }

					if ( $error == "NONE" ) { return false ; }
					else { return $error ; }
				}
				else
					return "SMTP addon not found or addon upgrade is needed. [e1]" ;
			}
			else
			{
				$headers = "Reply-to: $from_name <$from_email>" . "\r\n" ;
				if ( $dmarc )
				{
					$headers .= "From: ".'=?UTF-8?B?'.base64_encode( $to_name ).'?='." <$to_email>" . "\r\n" ;
				} else { $headers .= "From: ".'=?UTF-8?B?'.base64_encode( $from_name ).'?='." <$from_email>" . "\r\n" ; }
				$headers .= "MIME-Version: 1.0" . "\n" ;
				$headers .= "Content-type: text/plain; charset=UTF-8" . "\r\n" ;
				for ( $c = 0; $c < count( $bcc ); ++$c ) { $headers .= "Bcc: $bcc[$c]\r\n" ; }

				if ( mail( $to_email, $subject_new, $message, $headers ) ) { if ( defined( 'API_Util_Error' ) ) { set_error_handler( "ErrorHandler" ) ; } return false ; }
				else
				{
					if ( defined( 'API_Util_Error' ) ) { set_error_handler( "ErrorHandler" ) ; }

					if ( preg_match( "/failed to connect/i", $ERROR_EMAIL ) )
						return "Could not connect to local mail server or mail server is not installed." ;
					else if ( $ERROR_EMAIL )
						return "Email error: Could not send email ($ERROR_EMAIL)" ;
					else
						return "Email error: Please contact your server admin to make sure the server can send emails." ;
				}
			}
		}
		else
			return "Recipient is invalid." ;
	}

	function Util_Email_FormatTranscript( $ces, $transcript_template, $dept_name, $dept_email, $vis_name, $vis_email, $op_name, $op_email, $custom_vars, $transcript_formatted )
	{
		$custom_vars_string = "" ;
		$customs = explode( "-cus-", $custom_vars ) ;
		for ( $c = 0; $c < count( $customs ); ++$c )
		{
			$custom_var = $customs[$c] ;
			if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
			{
				LIST( $cus_name, $cus_val ) = explode( "-_-", preg_replace( "/%20/", " ", $custom_var ) ) ;
				$cus_name = preg_replace( "/\\$/", "-dollar-", $cus_name ) ;
				$cus_val = preg_replace( "/\\$/", "-dollar-", $cus_val ) ;
				if ( $cus_val && ( $cus_name != "ProAction ID" ) )
				{
					$custom_vars_string .= "$cus_name: $cus_val\r\n" ;
				}
			}
		}
		$trans = explode( "<>", $transcript_formatted ) ;
		$trans_out = Array() ;
		$total_index = count( $trans ) ;
		for ( $c2 = 0; $c2 < $total_index; ++$c2 )
		{
			$chat_line = $trans[$c2] ;
			if ( preg_match( "/<div class='co cw'/i", $chat_line ) )
			{
				// x-nod = no display or alert to the visitor
				//$trans_out[] = base64_encode( "<x-nod>" ) ;
			}
			else
				$trans_out[] = $chat_line ;
		} $transcript_formatted = implode( "<>", $trans_out ) ;
		if ( $vis_email == "null" ) { $vis_email = "" ; }
		$message = preg_replace( "/<div id='chat_survey_content'(.*)/", "", $transcript_formatted ) ;
		$message = preg_replace( "/&nbsp;/", " ", $message ) ;
		$message = preg_replace( "/<x-vis>(.*?)<\/x-vis>/", "", $message ) ;
		$message = preg_replace( "/<x-voice_chat_button>(.*?)<\/x-voice_chat_button>/", "", $message ) ;
		$message = preg_replace( "/<!--(.*?)-->/", "", $message ) ;
		$message = preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $message ) ;
		// bot related
		$message = preg_replace( "/<button (.*?)%%>(.*?)<\/button>/", " ( $2 ) ", $message ) ;
		//
		$message = preg_replace( "/<>/", "\r\n", stripslashes( preg_replace( "/(\r\n)|(\n)|(\r)/", "", $message ) ) ) ;
		/***********************/
		// raw HTML ajax format check
		/***********************/
		$message = preg_replace( "/&#107;/", "j", $message ) ;
		$message = preg_replace( "/&#115;/", "s", $message ) ;
		$message = preg_replace( "/&#111;/", "o", $message ) ;

		$message = preg_replace( "/<div style='margin-top: 15px; height: 1px(.*?)<\/div>/i", "", $message ) ;
		$message = preg_replace( "/<div class='ctitle cl' onclick='toggle_rating(.*?)<\/div>/i", "", $message ) ;
		/***********************/
		$message = preg_replace( "/<a tag='image' href='(.*?)'(.*?)a>/i", "$1", $message ) ;
		$message = preg_replace( "/<a tag='link' href='(.*?)'(.*?)a>/i", "$1", $message ) ;
		$message = preg_replace( "/<a href='(.*?)'(.*?)a>/i", "$1", $message ) ;
		$message = preg_replace( "/\\$/", "-dollar-", $message ) ; // to limit variable confusion.  will revert before sending
		$message = preg_replace( "/<div class='ca'><i>(.*?)<\/i><\/div>/i", "-------------------------------------\r\n$vis_name $vis_email\r\n$custom_vars_string\r\n$1\r\n-------------------------------------", $message ) ;
		$message = preg_replace( "/<div class='ca'>(.*?)<\/div>/i", "-------------------------------------\r\n$1\r\n-------------------------------------", $message ) ;
		$message = preg_replace( "/<disconnected><d(\d)>(.*?)<\/div>/i", "\r\n-------------------------------------\r\n$2\r\n-------------------------------------\r\n", $message ) ;
		$message = preg_replace( "/<div class='btn_op_hide'>(.*?)<\/div><\/div>/", "", $message ) ;
		/***********************/
		// raw HTML ajax format check
		/***********************/
		$message = preg_replace( "/<div class='cl' style='display: block;'>(.*?)<\/div>/i", "\r\n-------------------------------------\r\n$1\r\n-------------------------------------\r\n", $message ) ;
		/***********************/
		$message = preg_replace( "/===\r\n===/", "-------------------------------------", $message ) ;
		$message = preg_replace( "/<div class='co'><b>(.*?)<timestamp_(\d+)_co>:<\/b> /i", "\r\n$1: ", $message ) ;
		$message = preg_replace( "/<v>/", "", $message ) ; // old chat transcript format clean
		$message = preg_replace( "/<div class='cv'><b>(.*?)<timestamp_(\d+)_cv>:<\/b> /i", "\r\n$1: ", $message ) ;
		$message = preg_replace( "/<iframe (.*?) src='(.*?)' (.*?)><\/iframe>/i", "$2", $message ) ;
		$message = preg_replace( "/mailto:/i", "", $message ) ;
		$message = preg_replace( "/[\n]+/", "\r\n", $message ) ; $message = preg_replace( "/[\r]+/", "\r\n", $message ) ;
		$message = preg_replace( "/[\r\n]+/", "\r\n\r\n", $message ) ;
		// need to put <br> check here to limit double spacing from above line
		$message = preg_replace( "/<br>/", "\r\n", $message ) ;
		$message = preg_replace( "/<(.*?)>/", "", strip_tags( $message ) ) ; // double check
		$message = preg_replace( "/\r\n\r\n/", "\r\n", $message ) ; // double triple check
		$message = preg_replace( "/\r\n\r\n/", "\r\n", $message ) ; $message = preg_replace( "/\r\n\r\n/", "\r\n", $message ) ;
		$message = preg_replace( "/\r\n\r\n/", "\r\n", $message ) ; $message = preg_replace( "/\r\n/", "\r\n\r\n", $message ) ;
		$message = preg_replace( "/\r\n-------------------------------------\r\n\r\n-------------------------------------\r\n/", "\r\n-------------------------------------\r\n", $message ) ;

		$message = preg_replace( "/%%transcript%%/i", "$message", $transcript_template ) ;
		$message = preg_replace( "/%%visitor%%/i", $vis_name, $message ) ;
		$message = preg_replace( "/%%operator%%/i", $op_name, $message ) ;
		$message = preg_replace( "/%%op_email%%/i", $op_email, $message ) ;
		$message = preg_replace( "/%%department%%/i", $dept_name, $message ) ;
		$message = preg_replace( "/%%dept_email%%/i", $dept_email, $message ) ;
		$message = preg_replace( "/%%chatid%%/i", $ces, $message ) ;
		$message = preg_replace( "/DO_NOT_DELETE@THIS_SYSTEM.BOT/i", "", $message ) ; // don't display system bot email
		return $message ;
	}
?>
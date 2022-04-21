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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	$auth = Util_Format_Sanatize( Util_Format_GetVar( "auth" ), "ln" ) ;
	$id = Util_Format_Sanatize( Util_Format_GetVar( "id" ), "n" ) ;
	$wp = Util_Format_Sanatize( Util_Format_GetVar( "wp" ), "n" ) ;
	$opid = 0 ;
	if ( $auth == "setup" )
	{
		$ses = isset( $_COOKIE["phpliveadminSES"] ) ? Util_Format_Sanatize( $_COOKIE["phpliveadminSES"], "ln" ) : "" ;
		if ( !$admininfo = Util_Security_AuthSetup( $dbh, $ses, $id ) ){ ErrorHandler( 602, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
		$theme = $CONF["THEME"] ;
	}
	else
	{
		$ses = isset( $_COOKIE["cS"] ) ? Util_Format_Sanatize( $_COOKIE["cS"], "ln" ) : "" ;
		if ( !$opinfo = Util_Security_AuthOp( $dbh, $id, $wp ) ){ ErrorHandler( 602, "Invalid operator session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
		$theme = $opinfo["theme"] ;
		$opid = $opinfo["opID"] ;
	}
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Notes/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;
	$back = Util_Format_Sanatize( Util_Format_GetVar( "back" ), "n" ) ;
	$mapp = Util_Format_Sanatize( Util_Format_GetVar( "mapp" ), "n" ) ;
	$realtime = Util_Format_Sanatize( Util_Format_GetVar( "realtime" ), "n" ) ;
	$text = Util_Format_Sanatize( Util_Format_GetVar( "text" ), "" ) ;
	$theme_override = Util_Format_Sanatize( Util_Format_GetVar( "theme" ), "ln" ) ;
	if ( $theme_override ) { $theme = $theme_override ; }
	if ( !is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/style.css" ) || isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }
	$addon_whisper = is_file( "$CONF[DOCUMENT_ROOT]/addons/whisper/inc_whisper.php" ) ? 1 : 0 ;

	if ( ( $action == "remove_lines" ) && isset( $admininfo ) )
	{
		$ids = Util_Format_Sanatize( Util_Format_GetVar( "ids" ), "a" ) ;

		$transcript = Chat_ext_get_Transcript( $dbh, $ces ) ;
		if ( isset( $transcript["ces"] ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/update.php" ) ;

			$formatted = $transcript["formatted"] ;
			for ( $c = 0; $c < count( $ids ); ++$c )
			{
				$id = $ids[$c] ;

				$formatted = preg_replace( "/<!--begin:$id-->(.*?)<timestamp_(.*?)>:<\/b><\/span>(.*?)<\/div><!--end:$id-->/i", "<!--begin:$id-->$1<timestamp_$2>:<\/b><\/span> *********<!--deleted--><\/div><!--end:$id-->", $formatted ) ;
			}

			$plain = preg_replace( "/<(.*?)>/", "", preg_replace( "/<>/", "\r\n", preg_replace( "/<a href='(.*?)'(.*?)a>/i", "$1", $formatted ) ) ) ;
			Chat_update_TranscriptValues( $dbh, $ces, "formatted", $formatted, "plain", $plain ) ;

			$json_data = "json_data = { \"status\": 1 };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid transcript ID.\" };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		$json_data = Util_Format_Trim( $json_data ) ; $json_data = preg_replace( "/\t/", "", $json_data ) ;
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
	LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
	$mobile = ( $os == 5 ) ? 1 : 0 ;
	$error = "" ;
	$noteinfo = Array() ; $rating_stars = "" ;

	$transcript = Chat_ext_get_Transcript( $dbh, $ces ) ;
	$requestinfo = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;
	$requestinfo_log = isset( $requestinfo["created"] ) ? $requestinfo : Array() ;
	if ( !isset( $requestinfo["ces"] ) && isset( $transcript["ces"] ) )
	{
		$requestinfo = Array() ;
		$requestinfo["ces"] = $transcript["ces"] ;
		$requestinfo["created"] = $transcript["created"] ;
		$requestinfo["ended"] = $transcript["ended"] ;
		$requestinfo["status"] = 1 ;
		$requestinfo["initiated"] = $transcript["initiated"] ;
		$requestinfo["deptID"] = $transcript["deptID"] ;
		$requestinfo["opID"] = $transcript["opID"] ;
		$requestinfo["accepted_op"] = $transcript["accepted_op"] ;
		$requestinfo["op2op"] = $transcript["op2op"] ;
		$requestinfo["marketID"] = $transcript["marketID"] ;
		$requestinfo["os"] = 4 ;
		$requestinfo["browser"] = 6 ;
		$requestinfo["resolution"] = "&nbsp;" ;
		$requestinfo["vname"] = $transcript["vname"] ;
		$requestinfo["vemail"] = $transcript["vemail"] ;
		$requestinfo["ip"] = $transcript["ip"] ;
		$requestinfo["sim_ops"] = "" ;
		$requestinfo["agent"] = "&nbsp;" ;
		$requestinfo["onpage"] = "" ;
		$requestinfo["title"] = "" ;
		$requestinfo["custom"] = "" ;
		$requestinfo["md5_vis"] = $transcript["md5_vis"] ;
		$requestinfo["question"] = $transcript["question"] ;
		$requestinfo["tag"] = $transcript["tag"] ;
		$noteinfo = ( $transcript["noteID"] ) ? Notes_get_NoteInfo( $dbh, $transcript["noteID"] ) : Array() ;
		$rating_stars = Util_Functions_Stars( "..", $transcript["rating"] ) ;
	}
	else if ( !isset( $requestinfo["ces"] ) && !isset( $transcript["ces"] ) )
	{
		$requestinfo = Array() ;
		$requestinfo["ces"] = "invalid" ;
		$requestinfo["created"] = 0 ;
		$requestinfo["ended"] = 0 ;
		$requestinfo["status"] = 1 ;
		$requestinfo["initiated"] = 0 ;
		$requestinfo["deptID"] = "invalid" ;
		$requestinfo["opID"] = "invalid" ;
		$requestinfo["accepted_op"] = 0 ;
		$requestinfo["op2op"] = 0 ;
		$requestinfo["marketID"] = 0 ;
		$requestinfo["os"] = 4 ;
		$requestinfo["browser"] = 6 ;
		$requestinfo["resolution"] = "&nbsp;" ;
		$requestinfo["vname"] = "invalid" ;
		$requestinfo["vemail"] = "invalid" ;
		$requestinfo["ip"] = "invalid" ;
		$requestinfo["sim_ops"] = "" ;
		$requestinfo["agent"] = "&nbsp;" ;
		$requestinfo["onpage"] = "" ;
		$requestinfo["title"] = "" ;
		$requestinfo["custom"] = "" ;
		$requestinfo["md5_vis"] = "" ;
		$requestinfo["question"] = "invalid" ;
		$transcript["formatted"] = "" ;
		$transcript["opID"] = 0 ;
		$transcript["deptID"] = 0 ;
		$transcript["created"] = 0 ;
		$transcript["ended"] = 0 ;
		$transcript["tag"] = 0 ;
	}
	else if ( isset( $transcript["ces"] ) )
	{
		$noteinfo = ( $transcript["noteID"] ) ? Notes_get_NoteInfo( $dbh, $transcript["noteID"] ) : Array() ;
		$rating_stars = Util_Functions_Stars( "..", $transcript["rating"] ) ;
	}

	if ( !$realtime || $requestinfo["ended"] )
	{
		$realtime = 0 ; // set it to zero since it ended, not realtime anymore
		$formatted = preg_replace( "/(\r\n)|(\r)|(\n)/", "<br>", preg_replace( "/\"/", "&quot;", $transcript["formatted"] ) ) ;
		//$formatted = preg_replace( "/\p{L}*?".preg_quote($text)."\p{L}*/ui", "<span style='background: #FFFF00; padding: 2px; text-shadow: none; color: #000000;'>$0</span>", $formatted ) ;
		$requestinfo["vname"] = Util_Format_Sanatize( $requestinfo["vname"], "v" ) ;

		$opinfo_ = Ops_get_OpInfoByID( $dbh, $transcript["opID"] ) ;
		if ( !isset( $opinfo_["opID"] ) )
			$opinfo_ = Array( "opID"=>0, "login"=>"Invalid", "name"=>"Invalid", "email"=>"Invalid" ) ;

		$deptinfo = Depts_get_DeptInfo( $dbh, $transcript["deptID"] ) ;
		if ( !isset( $deptinfo["deptID"] ) )
			$deptinfo = Array( "name"=>"Invalid", "lang"=>"english", "msg_email"=>"Invalid" ) ;

		$duration = $transcript["ended"] - $transcript["created"] ;
		if ( $duration < 60 )
			$duration = 60 ;
		$duration = Util_Format_Duration( $duration ) ;
	}
	else if ( isset( $requestinfo["opID"] ) )
	{
		$opinfo_ = Ops_get_OpInfoByID( $dbh, $requestinfo["opID"] ) ;
		if ( !isset( $opinfo_["opID"] ) )
			$opinfo_ = Array( "opID"=>0, "login"=>"Invalid", "name"=>"Invalid", "email"=>"Invalid" ) ;

		$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo["deptID"] ) ;
		if ( !isset( $deptinfo["deptID"] ) )
			$deptinfo = Array( "name"=>"Invalid", "lang"=>"english", "msg_email"=>"Invalid" ) ;

		$formatted = "<div class='ca' style='font-size: 14px; font-weight: bold;'>Real-time Chat Session View</div>" ;
		$duration = "" ;
	}

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	$profile_pic_url = Util_Upload_GetLogo( "profile", $opinfo_["opID"] ) ;
	$profile_pic = "<div style='margin-bottom: 5px;'><table cellspacing=0 cellpadding=0 border=0><tr><td valign='top' width='55'><div id='chat_profile_pic' style='border: 0px;'><img src='$profile_pic_url' width='55' height='55' border=0 class='profile_pic_img' style='border-radius: 50%;'></div></td><td valign='top' style='padding: 10px;'><div style='font-weight: bold; font-size: 14px;' id='chat_profile_name'>$opinfo_[name]<div style='margin-top: 4px; font-weight: normal;'>$deptinfo[name]</div><div style='margin-top: 4px; font-weight: normal; font-size: 12px;'>Chat ID: $ces</div></div></div></td></tr></table></div>" ;

	if ( $mapp && isset( $requestinfo["custom"] ) )
	{
		$custom_string_mapp = "" ;
		$customs = explode( "-cus-", $requestinfo["custom"] ) ;
		for ( $c = 0; $c < count( $customs ); ++$c )
		{
			$custom_var = $customs[$c] ;
			if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
			{
				LIST( $cus_name, $cus_val ) = explode( "-_-", rawurldecode( $custom_var ) ) ;
				if ( $cus_val )
				{
					if ( preg_match( "/^((http)|(www))/", $cus_val ) )
					{
						if ( preg_match( "/^(www)/", $cus_val ) ) { $cus_val = "http://$cus_val" ; }
						$cus_val_snap = ( strlen( $cus_val ) > 40 ) ? substr( $cus_val, 0, 15 ) . "..." . substr( $cus_val, -15, strlen( $cus_val ) ) : $cus_val ;
						$custom_string_mapp .= "<tr><td>$cus_name: </td><td><a href='$cus_val' target='_blank'>$cus_val_snap</a></td></tr>" ;
					}
					else
						$custom_string_mapp .= "<tr><td>$cus_name: </td><td>$cus_val</td></tr>" ;
				}
			}
		}
	}

	$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;
	$tags_options = $tag_selected = "" ; $hastag = 0 ;
	foreach ( $tags as $index => $value )
	{
		if ( $index != "c" )
		{
			LIST( $status, $color, $tag ) = explode( ",", $value ) ;
			$tag = rawurldecode( $tag ) ;

			$selected = "" ;
			if ( isset( $requestinfo["tag"] ) && ( $index == $requestinfo["tag"] ) )
			{
				$selected = "selected" ;
				$tag_selected = $tag ;
			}
			if ( $status )
			{
				$tags_options .= "<option value='$index' $selected>$tag</option>" ;
				++$hastag ;
			}
		}
	}
	if ( !$hastag ) { $tags_options = "" ; }

	$emarketing_string = "" ;
	if ( isset( $admininfo ) )
	{
		if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
		$emarketings = Array() ; $emarketinginfo = Array( "id"=>0, "message"=>"", "val_1"=>"", "val_0"=>"", "isreq"=>1, "status"=>0 ) ;
		if ( $VARS_ADDON_EMARKET_ENABLED && is_file( "$CONF[DOCUMENT_ROOT]/addons/emarketing/emarketing.php" ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/addons/emarketing/API/Util_Emarketing.php" ) ;
			// make sure lib is current
			if ( function_exists( "Util_Emarketing_VisInfo" ) )
			{
				$emarketinfo = Util_Emarketing_VisInfo( $dbh, $requestinfo["md5_vis"] ) ;
				if ( isset( $emarketinfo["md5_vis"] ) )
				{
					$emarketings = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["emarketing"] ) && $VALS_ADDONS["emarketing"] ) ? unserialize( base64_decode( $VALS_ADDONS["emarketing"] ) ) : Array() ;
					if ( count( $emarketings ) )
					{
						$emarketinginfo = current( (Array)$emarketings ) ;
						if ( $emarketinfo["thevalue"] == 1 ) { $value_string = $emarketinginfo["val_1"] ; }
						else if ( $emarketinfo["thevalue"] == 0 ) { $value_string = $emarketinginfo["val_0"] ; }
						else { $value_string = "no response" ; }
						$emarketing_string = "<tr><td nowrap class='chat_info_td_h'>Opt-in </td><td class='chat_info_td'>$emarketinginfo[message] <span style='font-weight: bold;'>$value_string</span></td></tr>" ;
					}
				}
			}
		}
	}

	$visitor_id = $requestinfo["md5_vis"] ;
	if ( $requestinfo["md5_vis"] == "op2op" )
		$visitor_id = "<img src='../themes/initiate/agent.png' width='16' height='16' border='0' title='Operator to Operator Chat' alt='Operator to Operator Chat'> <big><b>Operator 2 Operator Chat</b></big>" ;
	else if ( $requestinfo["md5_vis"] == "grc" )
		$visitor_id ="<img src='../themes/initiate/group.png' width='16' height='16' border='0' title='Group Chat' alt='Group Chat'> <big><b>Group Chat</b></big>" ;

	$created = isset( $requestinfo_log["created"] ) ? date( "M j, Y, $VARS_TIMEFORMAT", $requestinfo_log["created"] ) : date( "M j, Y, $VARS_TIMEFORMAT", $requestinfo["created"] ) ;
	$created_string = "<tr><td nowrap class='chat_info_td_h'>Created</td><td class='chat_info_td'>$created</td></tr>" ;
	$average_accept_string = ( isset( $requestinfo['accepted_op'] ) && isset( $admininfo ) ) ? "<tr><td nowrap class='chat_info_td_h'>Accepted</td><td class='chat_info_td'>".Util_Format_Duration( $requestinfo['accepted_op'] )." (chat accept time)</td></tr>" : "" ;
	$visitorid_string = ( !$mapp || ( $requestinfo["md5_vis"] == "op2op" ) ) ? "<tr><td nowrap class='chat_info_td_h'>Visitor ID </td><td class='chat_info_td'>$visitor_id</td></tr>" : $custom_string_mapp ;
	$ip_string = ( ( ( isset( $opinfo ) && $opinfo["viewip"] ) && !$requestinfo["op2op"] ) || isset( $admininfo ) ) ? "<tr><td nowrap class='chat_info_td_h'>IP Address </td><td class='chat_info_td'>$requestinfo[ip]</td></tr>" : "" ;
	$tags_string = ( $tags_options && isset( $admininfo ) ) ? "<tr><td nowrap class='chat_info_td_h'>Tag </td><td class='chat_info_td'><form><select id='tagid' style='font-size: 12px; padding: 4px;' onChange='update_tag(this.value)'><option value='0'></option>$tags_options</select> &nbsp; <span id='req_tag_saved' class='info_good' style='display: none; padding: 4px !important;'>saved</span></form></td></tr>" : "<tr><td nowrap class='chat_info_td_h'>Tag </td><td class='chat_info_td'>$tag_selected</td></tr>" ;
	$comments_string = ( isset( $noteinfo["message"] ) ) ? "<tr><td nowrap class='chat_info_td_h'><table cellspacing=0 cellpadding=0 border=0><tr><td><img src='../pics/icons/info_flag.png' width='10' height='10' border='0' alt=''> &nbsp; </td><td>Comment </td></tr></table></td><td class='chat_info_td'><i>".Util_Format_ConvertQuotes( preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $noteinfo["message"] ) )."</i></td></tr>" : "" ;
	$rating_string = ( $rating_stars ) ? "<tr><td nowrap class='chat_info_td_h'>Rating </td><td class='chat_info_td'>$rating_stars</td></tr>" : "" ;
	$market = "" ;
	if ( isset( $requestinfo["marketID"] ) && $requestinfo["marketID"] )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/get_itr.php" ) ;
		$marketinfo = Marketing_get_itr_MarketingByID( $dbh, $requestinfo["marketID"] ) ;
		if ( isset( $marketinfo["marketID"] ) ) { $market = "<tr><td nowrap>Campaign </td><td><span style='padding: 2px; background: #$marketinfo[color]; border-radius: 5px;'>$marketinfo[name]</span></td></tr>" ; }
	}
	$profile_pic .= "<div class='ca'><table cellspacing=0 cellpadding=2 border=0>$created_string$average_accept_string$visitorid_string$ip_string$market$tags_string$rating_string$comments_string$emarketing_string</table></div>" ;

	if ( $deptinfo["lang"] ) { $CONF["lang"] = $deptinfo["lang"] ; }
	include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($CONF["lang"], "ln").".php" ) ;

	if ( isset( $requestinfo["os"] ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/get_ext.php" ) ;

		$os = $VARS_OS[$requestinfo["os"]] ;
		$browser = $VARS_BROWSER[$requestinfo["browser"]] ;

		$onpage_title = preg_replace( "/\"/", "&quot;", $requestinfo["title"] ) ;
		$onpage_title_raw = $onpage_title ;
		$onpage_title_snap = ( strlen( $onpage_title_raw ) > 20 ) ? substr( $onpage_title_raw, 0, 40 ) . "..." : $onpage_title_raw ;
		$onpage_raw = preg_replace( "/hphp/i", "http", $requestinfo["onpage"] ) ;
		$onpage_snap = ( strlen( $onpage_raw ) > 20 ) ? substr( $onpage_raw, 0, 40 ) . "..." : $onpage_raw ;

		$referinfo = Footprints_get_IPRefer( $dbh, $requestinfo["md5_vis"] ) ;
		$refer_raw = ( isset( $referinfo["refer"] ) && $referinfo["refer"] ) ? preg_replace( "/hphp/i", "http", $referinfo["refer"] ) : "" ;
		if ( !preg_match( "/^http/i", $refer_raw ) ) { $refer_raw = "" ; }
		$refer_snap = ( strlen( $refer_raw ) > 20 ) ? substr( $refer_raw, 0, 40 ) . "..." : $refer_raw ;
		$refer_snap = preg_replace( "/^((http)|(https)):\/\/(www.)/", "", $refer_snap ) ;

		if ( $requestinfo["marketID"] )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/get_itr.php" ) ;
			$marketinfo = Marketing_get_itr_MarketingByID( $dbh, $requestinfo["marketID"] ) ;
		}
	}

	$deptid = $requestinfo["deptID"] ;
	$dept_emo = ( isset( $VALS["EMOS"] ) && $VALS["EMOS"] ) ? unserialize( $VALS["EMOS"] ) : Array() ;
	$addon_emo = 0 ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/emoticons/emoticons.php" ) )
	{
		if ( !isset( $dept_emo[$deptid] ) || ( isset( $dept_emo[$deptid] ) && $dept_emo[$deptid] ) ) { $addon_emo = 1 ; }
		else if ( isset( $dept_emo[$deptid] ) && !$dept_emo[$deptid] ) { $addon_emo = 0 ; }
		else if ( !isset( $dept_emo[0] ) || ( isset( $dept_emo[0] ) && $dept_emo[0] ) ) { $addon_emo = 1 ; }
	}
	$message_body = preg_replace( "/%%visitor%%/i", "$requestinfo[vname]", $deptinfo["msg_email"] ) ;
	$message_body = preg_replace( "/%%operator%%/i", "$opinfo_[name]", $message_body ) ;
	$message_body = preg_replace( "/%%op_email%%/i", "$opinfo_[email]", $message_body ) ;
	$message_body = preg_replace( "/%%chatid%%/i", $ces, $message_body ) ;

	$autolinker_js_file = ( isset( $VARS_JS_AUTOLINK_FILE ) && ( ( $VARS_JS_AUTOLINK_FILE == "min" ) || ( $VARS_JS_AUTOLINK_FILE == "src" ) ) ) ? "autolinker_$VARS_JS_AUTOLINK_FILE.js" : "autolinker_min.js" ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> <?php echo ( $realtime ) ? "Chat Session" : "Transcript" ; ?> </title>

<meta name="description" content="v.<?php echo $VERSION ?>">
<meta name="keywords" content="<?php echo md5( $KEY ) ?>">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=<?php echo $LANG["CHARSET"] ?>"> 
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../themes/<?php echo $theme ?>/style.css?<?php echo $VERSION ?>">

</head>
<body>

<div id="chat_canvas" style="min-height: 100%; width: 100%;"></div>
<div id="chat_canvas_content" style="position: absolute; top: 0px; left: 0px; width: 100%; z-Index: 2; overflow: auto;">
	<div style="padding: 10px;">
		<div id="chat_body" style="overflow: auto; padding: 10px; word-break: break-word; word-wrap: break-word;">
			<div id="span_loading" style="" class="round">
				<img src="../themes/<?php echo $theme ?>/loading_chat.gif" width="16" height="16" border="0" alt="" title="loading..." alt="loading..." class="<?php echo preg_match( "/^((cloud)|(home)|(slate_basic)|(island)|(leaves)|(very_pastel))/i", $theme ) ? "info_box" : "info_neutral" ; ?>">
			</div>
		</div>
		<div id="chat_input" style="margin-top: 2px; padding: 5px; border-radius: 5px;">

			<?php if ( isset( $requestinfo["ces"] ) && ( $requestinfo["ces"] != "invalid" ) && !$mapp ): ?>
			<table cellspacing=0 cellpadding=0 border=0 width="100%" id="table_info">
			<tr>
				<td width="200" valign="top">
					<div class="chat_info_td_traffic" style="font-weight: bold;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td style="padding-right: 5px;"><span id="chat_vtimer"></span></td>
							<td><span id="req_rating"><?php echo $rating_stars ?></span> <img src="../pics/loading_pulse.gif" width="12" height="12" border="0" alt="real-time view" title="real-time view" style="display: none; background: #FFFFFF; padding: 2px; border-radius: 2px;" id="img_spinner_real_time"></td>
						</tr>
						</table>
					</div>
					<div class="chat_info_td_traffic"><b><?php echo ( $requestinfo["resolution"] ) ? $requestinfo["resolution"] : "" ; ?></b> &nbsp; <img src="../themes/<?php echo $theme ?>/os/<?php echo $os ?>.png" width="14" height="14" border="0"  title="<?php echo $os ?>" alt="<?php echo $os ?>" style="cursor: help;"> &nbsp; <img src="../themes/<?php echo $theme ?>/browsers/<?php echo $browser ?>.png" width="14" height="14" border="0" title="<?php echo $browser ?>" alt="<?php echo $browser ?>" style="cursor: help;"></div>

					<div style="max-height: 85px; margin-right: 5px; overflow: auto;">
					<?php
						if ( $requestinfo["custom"] )
						{
							$customs = explode( "-cus-", $requestinfo["custom"] ) ;
							for ( $c = 0; $c < count( $customs ); ++$c )
							{
								$custom_var = $customs[$c] ;
								if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
								{
									LIST( $cus_name, $cus_val ) = explode( "-_-", rawurldecode( $custom_var ) ) ;
									if ( $cus_val )
									{
										if ( preg_match( "/^((http)|(www))/", $cus_val ) )
										{
											if ( preg_match( "/^(www)/", $cus_val ) ) { $cus_val = "http://$cus_val" ; }
											$cus_val_snap = ( strlen( $cus_val ) > 40 ) ? substr( $cus_val, 0, 15 ) . "..." . substr( $cus_val, -15, strlen( $cus_val ) ) : $cus_val ;
											print "<div class=\"chat_info_td_blank\" style=\"font-weight: bold;\">$cus_name</div><div style=\"padding-top: 0px;\" class=\"chat_info_td\" title=\"$cus_val\" alt=\"$cus_val\"><a href=\"$cus_val\" target=_blank>$cus_val_snap</a></div>" ;
										}
										else
										{
											print "<div class=\"chat_info_td_blank\" style=\"font-weight: bold;\">$cus_name</div><div style=\"padding-top: 0px;\" class=\"chat_info_td\">$cus_val</div>" ;
										}
									}
								}
							}
						}
					?>
					</div>
				</td>
				<td valign="top">
					<?php
						$mailto = "" ;
						if ( $requestinfo["vemail"] && ( ( $requestinfo["vemail"] != "null" ) && ( $requestinfo["vemail"] != "invalid" ) ) )
						{
							$mailto = "mailto:$requestinfo[vemail]" ;
						}
					?>
					<table cellspacing=0 cellpadding=0 border=0>
					<?php if ( $requestinfo["md5_vis"] != "grc" ): ?>
					<tr>
						<td width="50" nowrap class='chat_info_td_h'>Visitor</td>
						<td><div class="chat_info_td_traffic"><b><?php echo ( $requestinfo["vname"] && ( ( $requestinfo["vname"] != "null" ) && ( $requestinfo["vname"] != "invalid" ) ) ) ? $requestinfo["vname"] : "" ; ?></b> <?php echo ( $mailto ) ? "<a href='$mailto' target='_blank'>$requestinfo[vemail]</a>" : "" ; ?></div></td>
					</tr>
					<?php endif ; ?>
					<tr>
						<td class='chat_info_td_h'>Operator</td>
						<td><div class="chat_info_td_traffic"><?php echo ( $requestinfo["initiated"] ) ? "<img src=\"../themes/$CONF[THEME]/info_initiate.gif\" width=\"10\" height=\"10\" border=\"0\" alt=\"\" title=\"Operator Initiated Chat Invite\" alt=\"Operator Initiated Chat Invite\"> " : "" ; ?><b><?php echo $opinfo_["name"] ; ?></b> <?php echo ( $opinfo_["login"] != "phplivebot" ) ? "<a href='mailto:$opinfo_[email]' target='new'>$opinfo_[email]</a>" : "" ; ?></div></td>
					</tr>
					<tr>
						<td class='chat_info_td_h'>Department</td>
						<td><div class="chat_info_td_traffic"><b><?php echo $deptinfo["name"] ; ?></b></div></td>
					</tr>
					<tr>
						<td class='chat_info_td_h'>On Page</td>
						<td>
							<div class="chat_info_td_traffic" title="<?php echo rawurldecode( $onpage_raw ) ?>" alt="<?php echo rawurldecode( $onpage_raw ) ?>"><?php if ( $onpage_raw != "livechatimagelink" ): ?><b><a href="<?php echo rawurldecode( $onpage_raw ) ; ?>" target="_blank"><?php echo rawurldecode( $onpage_title_snap ) ?></a></b><?php else: ?><?php echo rawurldecode( $onpage_title_snap ) ?><?php endif ; ?></div>
						</td>
					</tr>
					<tr>
						<td class='chat_info_td_h'>Refer</td>
						<td><div class="chat_info_td_traffic" title="<?php echo rawurldecode( $refer_raw ) ?>" alt="<?php echo rawurldecode( $refer_raw ) ?>"><b><a href="<?php echo rawurldecode( $refer_raw ) ?>" target="_blank"><?php echo rawurldecode( $refer_snap ) ?></a></b></div></td>
					</tr>
					</table>
				</td>
			</tr>
			</table>

			<?php elseif ( !$mapp ): ?>
			<div class="info_box">Transcript does not exist or is no longer available.</div>
			<?php endif ; ?>
		</div>
	</div>
</div>
<div style="position: fixed; width: 100%; bottom: 0px; left: 0px; border-radius: 0px; z-Index: 10;" id="div_options" class="info_neutral">
	<div style="padding: 10px;">
		<?php if ( !$realtime ): ?>
			<div style="float: left; cursor: pointer;" onClick="toggle_email()"><span class="info_box" style="box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);"><img src="../themes/<?php echo $theme ?>/email.png" width="16" height="16" border="0" alt="email transcript" title="email transcript"> Email Transcript</span></div>

			<?php if ( !$back ): ?>
			<div style="float: left; margin-left: 15px; cursor: pointer;" onClick="do_print('<?php echo $ces ?>', <?php echo $requestinfo["deptID"] ?>, <?php echo $requestinfo["opID"] ?>, <?php echo $VARS_CHAT_WIDTH+100 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?> )"><span class="info_box" style="box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);"><img src="../themes/<?php echo $theme ?>/printer.png" width="16" height="16" border="0" alt="print" title="print"> Print Transcript</span></div>
			<?php endif ; ?>

			<?php if ( isset( $admininfo ) ): ?>
			<div style="float: left; margin-left: 15px; cursor: pointer;" onClick="toggle_remove()"><span class="info_box" style="box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);"><img src="../themes/<?php echo $theme ?>/typing.png" width="15" height="16" border="0" alt=""> Delete Lines</span></div>
			<?php endif ; ?>
			<div style="clear: both;"></div>
		<?php elseif ( $addon_whisper && isset( $opinfo ) && $opinfo["view_chats"] ): ?>
			<div><span class="info_box" style="box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);"><img src="../themes/<?php echo $theme ?>/chats.png" width="16" height="16" border="0" alt=""> <a href="JavaScript:void(0)" onClick="toggle_chat()">Participate in chat</a></span></div>
		<?php else: ?><img src="../pics/space.gif" width="1" height="16" border=0><?php endif ; ?>
	</div>
</div>

<div id="table_email" style="display: none; position: fixed; width: 100%; bottom: 0px; border-radius: 0px; z-Index: 11;" class="info_content">
	<div style="padding: 15px;">
		<form>
		<input type="hidden" name="deptid" id="deptid" value="<?php echo $requestinfo["deptID"] ?>">
		<table cellspacing=0 cellpadding=0 border=0 width="100%">
		<tr>
			<td style="">To Email:<br><input type="text" class="input_text" name="vmail" id="vemail" size="38" maxlength="160" style="width: 95%;" value="<?php echo ( $requestinfo["vemail"] != "null" ) ? $requestinfo["vemail"] : "" ; ?>" onKeyPress="return justemails(event)"></td>
		</tr>
		<tr><td style="height: 5px;"></td></tr>
		<tr>
			<td colspan=2>Message:<br><textarea class="input_text" rows="7" style="width: 95%; resize: none;" wrap="virtual" id="message" spellcheck="true"><?php echo $message_body ?>

</textarea></td>
		</tr>
		<tr><td style="height: 15px;"></td></tr>
		<tr><td align="center"><input type="button" id="btn_email" value="Email Transcript" onClick="send_email()" class="input_op_button"> &nbsp; &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="toggle_info(0)">cancel</a></td></tr>
		</table>
		</form>
	</div>
</div>
<?php if ( $addon_whisper ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/whisper/inc_whisper.php" ) ; } ?>
<div id="div_remove_notice" style="display: none; position: fixed; bottom: 0px; left: 0px; text-align: center; width: 100%; z-Index: 11;" class="info_box">
	<div style="padding: 10px;">
		<div id="div_remove_info">Lines that are <b>checked</b> will be <b>deleted</b>. &nbsp; <button type="button" onClick="save_remove()">delete checked lines</button> &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="toggle_remove()">cancel</a></div>
		<div id="div_remove_confirm" style="display: none;"><span class="info_error">Are you sure?  This action cannot be reversed. &nbsp; <button type="button" id="btn_remove" onClick="save_remove_doit()">yes, delete checked lines</button></span> &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="toggle_remove()">cancel</a></div>
	</div>
</div>

<?php if ( $back && !$mapp ): ?>
<div class="info_disconnect" style="position: absolute; top: 0px; right: 0px; z-Index: 101;" onClick="history.go(-1)"><img src="../themes/<?php echo $theme ?>/close_extra.png" width="14" height="14" border="0" alt=""> back to transcript list</div>
<?php elseif ( $mapp ): ?>
<div class="info_disconnect" style="position: absolute; top: 0px; right: 0px; z-Index: 101;" onClick="parent.close_transcript('<?php echo $ces ?>')"><img src="../themes/<?php echo $theme ?>/close_extra.png" width="14" height="14" border="0" alt=""> close transcript &nbsp;</div>
<?php endif ; ?>

<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo filemtime ( "../js/global.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/global_ajax.js?<?php echo filemtime ( "../js/global_ajax.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/youtube-vimeo-url-parser.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/winapp.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/<?php echo $autolinker_js_file ?>?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var version = "<?php echo $VERSION ?>" ;
	var view = 1 ; // flag used in global.js for minor formatting of divs
	var base_url = ".." ;  var base_url_full = "<?php echo $CONF["BASE_URL"] ?>" ;
	var phplive_proto = ( location.href.indexOf("https") == 0 ) ? 1 : 0 ; // to avoid JS proto error, use page proto for areas needing to access the JS objects
	if ( !phplive_proto && ( base_url_full.match( /http/i ) == null ) ) { base_url_full = "http:"+base_url_full ; }
	else if ( phplive_proto && ( base_url_full.match( /https/i ) == null ) ) { base_url_full = "https:"+base_url_full ; }
	var proto = phplive_proto ;
	var isop = 0 ; var isop_ = 0 ; var isop__ = 0 ;
	var debug = 0 ; // write various logs in console.log
	var ses_console = "" ; // used by operator console only to limit multiple console windows
	var ces = "<?php echo $ces ?>" ;
	var st_typing, st_flash_console ;
	var wp = <?php echo $wp ?> ;
	var realtime = <?php echo ( $realtime ) ? $realtime : 0 ; ?> ;
	var mobile = <?php echo $mobile ?> ; var mapp = <?php echo $mapp ?> ;
	var theme = "<?php echo $theme ?>" ;
	var salt = "<?php echo md5( md5( $CONF["SALT"] ).$ces ) ?>" ;
	var phplive_mobile = 0 ; var phplive_ios = 0 ;
	var phplive_userAgent = navigator.userAgent || navigator.vendor || window.opera ;
	if ( phplive_userAgent.match( /iPad/i ) || phplive_userAgent.match( /iPhone/i ) || phplive_userAgent.match( /iPod/i ) )
	{
		phplive_ios = 1 ;
		if ( phplive_userAgent.match( /iPad/i ) ) { phplive_mobile = 0 ; }
		else { phplive_mobile = 1 ; }
	}
	else if ( phplive_userAgent.match( /Android/i ) ) { phplive_mobile = 2 ; }
	var profile_pic_enabled = 1 ;
	var time_h24 = <?php echo $VARS_24H ?> ;
	var embed = 0 ;
	var time_format = <?php echo ( !isset( $VALS['TIMEFORMAT'] ) || ( $VALS['TIMEFORMAT'] != 24 ) ) ? 12 : 24 ; ?> ;
	var timestamp = 1 ;
	var transcript ;
	var autolinker = new Autolinker( { newWindow: true, stripPrefix: false } ) ;
	var cname = "<?php echo ( isset( $opinfo ) && isset( $opinfo["opID"] ) ) ? $opinfo["name"] : "invalid" ; ?>" ; var cemail = "<?php echo ( isset( $opinfo ) && isset( $opinfo["opID"] ) ) ? $opinfo["email"] : "invalid" ; ?>" ;

	// addons related
	var addon_emo = <?php echo $addon_emo ?> ;
	var addon_voice_chat = 0 ;

	var st_realtime ;
	var c_chatting = 0 ;
	var chats = new Object ;
	chats[ces] = new Object ;
	chats[ces]["requestid"] = 0 ;
	chats[ces]["status"] = <?php echo ( isset( $requestinfo["os"] ) ) ? $requestinfo["status"] : 0 ; ?> ;
	chats[ces]["op2op"] = <?php echo ( isset( $requestinfo["os"] ) ) ? $requestinfo["op2op"] : 0 ; ?> ;
	chats[ces]["initiated"] = <?php echo ( isset( $requestinfo["os"] ) ) ? $requestinfo["initiated"] : 0 ; ?> ;
	chats[ces]["disconnected"] = 0 ;
	chats[ces]["mapp"] = 0 ;
	chats[ces]["trans"] = "" ;
	chats[ces]["vemail"] = "<?php echo ( isset( $requestinfo["vemail"] ) && ( $requestinfo["vemail"] != "null" ) ) ? $requestinfo["vemail"] : "" ; ?>" ;
	chats[ces]["question"] = "<?php echo ( isset( $requestinfo["question"] ) ) ? preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", preg_replace( "/\"/", "&quot;", $requestinfo["question"] ) ) : "" ; ?>" ;
	chats[ces]["timer"] = <?php echo ( isset( $requestinfo["os"] ) ) ? $requestinfo["created"] : 0 ; ?> ;
	chats[ces]["tag"] = <?php echo ( isset( $requestinfo["tag"] ) ) ? $requestinfo["tag"] : 0 ; ?> ;
	chats[ces]["fmindex"] = 0 ; chats[ces]["fmsize"] = 0 ; chats[ces]["fmlid"] = "" ;

	$(document).ready(function()
	{
		init_divs(0) ;

		transcript = "<?php echo preg_replace( "/<\/script>/i", "&lt;/script&gt;", preg_replace( "/▒~V~R~@▒~V~R/", "", preg_replace( "/▒~@▒/", "", $formatted ) ) ) ?>" ;
		$('#chat_body').html( "<?php echo $profile_pic ?>"+init_timestamps( transcript.emos().extract_youtube().replace( /class='btn_op_hide'/g, "style='display: none;'" ) ) ) ;
		$('#chat_body :button').prop('disabled', true) ;

		$('#btn_email').attr( "disabled", false ) ; // reset it... firefox caches disabled
		init_req() ;

		if ( realtime )
		{
			init_timer() ;
			chatting() ;
		}

		$('window').focus() ;
		<?php if ( $action == "success" ): ?>do_alert( 1, "Success" ) ;<?php endif ; ?>

		//$('#table_info tr:nth-child(1n)').addClass('chat_info_tr_traffic_row') ;

		if ( mapp ) { init_external_url() ; $('#div_options').hide() ; }
		else if ( !<?php echo $requestinfo["created"] ?> ) { $('#div_options').hide() ; }
	});

	if ( !mapp )
	{
		// iOS resizes on various events, even CSS resize (skip $mapp)
		$(window).resize(function() { init_divs(1) ; });
	}

	function init_external_url()
	{
		$("a").click(function(){
			var temp_url = $(this).attr( "href" ) ;
			if ( !temp_url.match( /javascript/i ) )
			{
				parent.parent.external_url = temp_url ;
				return false ;
			}
		});
	}

	function init_chat_body_height( thewidth, theheight )
	{
		var chat_body_width = thewidth - 15 ;
		var chat_body_height = theheight - 25 ;

		$('#chat_input').hide() ;
		$('#chat_body').css({'overflow-x': 'hidden', 'width': chat_body_width, 'height': chat_body_height }) ;
	}

	function init_req()
	{
		if ( realtime )
			$('#img_spinner_real_time').show() ;
		else
		{
			$('#img_spinner_real_time').hide() ;
			$('#chat_vtimer').html( "<img src='../pics/icons/clock3.png' width='16' height='16' border='0' alt='chat duration' title='chat duration' style='cursor: help;'> <?php echo $duration ; ?>" ) ;
		}
	}

	function toggle_info( theforce )
	{
		if ( theforce )
		{
			if ( $('#table_email').is(':visible') ) { toggle_info(0) ; }
		}
		else
		{
			$('#table_email').hide() ;
			$('#at_bill_1').prop( "checked", true ) ;

			$('#table_info').fadeIn( "fast" ) ;
		}
	}

	function toggle_email()
	{
		$('#table_info').hide() ;
		$('#table_email').fadeIn( "fast" ) ;
		$('#btn_email').attr( "disabled", false ) ;
	}

	function send_email()
	{
		if ( !$('#vemail').val() )
			do_alert( 0, "Blank Email is invalid." ) ;
		else if ( !$('#message').val() )
			do_alert( 0, "Blank Message is invalid." ) ;
		else
		{
			$('#btn_email').attr( "disabled", true ) ;

			var json_data = new Object ;
			var unique = unixtime() ;
			var deptid = $('#deptid').val() ;
			var vname = "<?php echo ( isset( $requestinfo["os"] ) ) ? $requestinfo["vname"] : "" ; ?>" ;
			var vemail = $('#vemail').val() ;
			var subject = encodeURIComponent( "Chat Transcript: "+vname ) ;
			var message =  encodeURIComponent( $('#message').val() ) ;

			$('#chat_button_start').blur() ;

			$.ajax({
			type: "POST",
			url: "../phplive_m.php",
			data: "action=send_email&ces=<?php echo $ces ?>&trans=1&opid=<?php echo isset( $opinfo_["opID"] ) ? $opinfo_["opID"] : 0 ; ?>&deptid="+deptid+"&cp=1&vname="+vname+"&vemail="+vemail+"&vsubject="+subject+"&vquestion="+message+"&unique="+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					toggle_info(0) ;
					do_alert( 1, "Transcript emailed to: "+vemail ) ;
				}
				else
				{
					do_alert( 0, json_data.error ) ;
					$('#btn_email').attr( "disabled", false ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Error sending transcript.  Please refresh the page and try again." ) ;
			} });
		}
	}

	function chatting()
	{
		var json_data = new Object ;
		var start = 0 ;
		var q_ces = "" ;
		var q_chattings = "" ;
		var q_cids = "" ;
		var q_fmindex = "" ;
		var q_fmsize = "" ;

		q_ces += "qcc[]="+ces+"&" ;
		q_chattings += "qch[]=0&" ;
		q_fmindex += "fi[]="+chats[ces]["fmindex"]+"&" ;
		q_fmsize += "fs[]="+chats[ces]["fmsize"]+"&" ;

		if ( typeof( st_chatting ) != "undefined" )
		{
			clearTimeout( st_chatting ) ;
			st_chatting = undeefined ;
		}

		if ( !chats[ces]["disconnected"] )
		{
			var unique = unixtime() ;
			$.ajax({
			type: "GET",
			url: "../ajax/chat_op_requesting.php",
			data: "c="+ces+"&ch="+c_chatting+"&pr="+proto+"&r="+realtime+"&"+q_ces+q_chattings+q_fmindex+q_fmsize+"&"+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.s )
				{
					for ( var c = 0; c < json_data.c.length; ++c )
						realtime_update_ces( json_data.c[c] ) ;
				}
				else { do_alert( 0, json_data.e ) ; }

				if ( typeof( st_chatting ) == "undefined" )
					st_realtime = setTimeout(function(){ chatting() ; }, <?php echo ( ( $VARS_JS_REQUESTING + 1 ) * 1000 ) ?>) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
			} });
			++c_chatting ;
		}
		else
		{
			if ( typeof( st_chatting ) != "undefined" )
			{
				clearTimeout( st_chatting ) ;
				st_chatting = undeefined ;
			}
		}
	}

	function update_tag( thetagid )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$('#tagid').attr('disabled', true) ;

		if ( typeof( ces ) != "undefined" )
		{
			if ( chats[ces]["tag"] != thetagid )
			{
				var ajax_script = ( <?php echo isset( $admininfo ) ? 1 : 0 ; ?> ) ? "setup_actions_.php" : "chat_actions_op_tag.php" ;
				$.ajax({
				type: "POST",
				url: "../ajax/"+ajax_script,
				data: "action=tag&ces="+ces+"&tagid="+thetagid+"&unique="+unique+"&",
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
					{
						chats[ces]["tag"] = thetagid ;
						$('#req_tag_saved').fadeIn("fast").delay(3000).fadeOut( "fast", function() {
							$('#tagid').attr('disabled', false) ;
						});
					}
					else
					{
						do_alert( 0, json_data.error ) ;
						$('#tagid').attr('disabled', false).val(0) ;
					}
				},
				error:function (xhr, ajaxOptions, thrownError){
					do_alert( 0, "Error processing tag.  Please refresh the console and try again." ) ;
				} });
			}
			else
			{
				$('#req_tag_saved').fadeIn("fast").delay(3000).fadeOut( "fast", function() {
					$('#tagid').attr('disabled', false) ;
				});
			}
		}
		else
		{
			$('#tagid').attr('disabled', false).val(0) ;
			do_alert( 0, "A chat session must be active." ) ;
		}
	}

	var remove_lines ;
	function toggle_remove()
	{
		<?php if ( isset( $admininfo ) ): ?>

		if ( $('#chat_profile_pic').length )
		{
			var transcript_orig = transcript ;
			var lines = transcript_orig.replace( /class='btn_op_hide'/g, "style='display: none;'" ).split( "<>" ) ;

			for ( var c = 0; c < lines.length; ++c )
			{
				if ( lines[c].match( /<!--begin/ ) )
				{
					lines[c] = lines[c].remove_add_checkbox() ;
				}
			}

			var transcript_out = lines.join('') ;

			$('#div_remove_notice').show() ;
			$('#td_remove_save').show() ;
			$('#chat_body').fadeTo("fast", 0).html( transcript_out ).fadeTo("fast", 1) ;
			$('#chat_body').scrollTop(0) ;
		}
		else
		{
			$('#div_remove_notice').hide() ;
			$('#td_remove_save').hide() ;
			$('#chat_body').html( "<?php echo $profile_pic ?>"+init_timestamps( transcript.replace( /class='btn_op_hide'/g, "style='display: none;'" ).emos().extract_youtube() ) ) ;
			$('#chat_body').scrollTop(0) ;

			$('#div_remove_info').show() ;
			$('#div_remove_confirm').hide() ;

			$('#btn_remove').attr( "disabled", false ) ;
		}
		
		<?php endif ; ?>
	}

	<?php if ( isset( $admininfo ) ): ?>

	function save_remove()
	{
		remove_lines = new Array() ;

		$('#chat_body input:checked').each(function() {
			remove_lines.push( $(this).attr('id') ) ;
		});

		if ( remove_lines.length )
		{
			$('#div_remove_info').hide() ;
			$('#div_remove_confirm').show() ;
		}
		else
			do_alert( 0, "Nothing has been checked." ) ;
	}

	function save_remove_doit()
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		var remove_query = "" ;
		for ( var c = 0; c < remove_lines.length; ++c )
		{
			var id = remove_lines[c].replace( /remove_/i, "" ) ;
			remove_query += "ids[]="+id+"&" ;
		}

		$('#btn_remove').attr('disabled', true) ;

		$.ajax({
		type: "POST",
		url: "./op_trans_view.php",
		data: "action=remove_lines&ces=<?php echo $ces ?>&id=<?php echo $admininfo["adminID"] ?>&auth=setup&"+remove_query+unique,
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				var url = location.href.replace( /&action=success/i, "" ) ;
				location.href = url+"&action=success" ;
			}
			else
			{
				do_alert( 0, json_data.error ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Error processing request.  Please refresh the page and try again." ) ;
		} });
	}

	String.prototype.remove_add_checkbox = function(){
		var string = this ;
		if ( !string.match( /<!--deleted-->/i ) && !string.match( /<div class='ca'>/i ) ) { string = string.replace( /<\/div><!--end:(.*?)-->$/i, function( $0, $1 ) { return " <input type=\"checkbox\" id=\"remove_"+$1+"\"></div><!--end:"+$1+"-->" } ) ; }
		return string ;
	} ;

	<?php endif ; ?>
//-->
</script>

</body>
</html>
<?php database_mysql_close( $dbh ) ; ?>
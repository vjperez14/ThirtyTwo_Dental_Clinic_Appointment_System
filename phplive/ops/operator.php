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
	if ( !is_file( "../web/config.php" ) ){ HEADER("location: ../setup/install.php") ; exit ; }
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$opinfo = Util_Security_AuthOp( $dbh ) ){ ErrorHandler( 602, "Invalid operator session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Hash.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/External/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;

	/***** [ BEGIN ] BASIC CLEANUP *****/
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Footprints/remove_itr.php" ) ;

	Footprints_remove_itr_Expired_U( $dbh ) ;
	Chat_remove_itr_ExpiredOp2OpRequests( $dbh ) ;
	Chat_remove_itr_OldRequests( $dbh ) ;
	Util_Functions_SystemClean( $dbh ) ;
	/***** [ END ] BASIC CLEANUP *****/

	$wp = Util_Format_Sanatize( Util_Format_GetVar( "wp" ), "n" ) ;
	$auto = Util_Format_Sanatize( Util_Format_GetVar( "auto" ), "n" ) ;
	$ins = Util_Format_Sanatize( Util_Format_GetVar( "ins" ), "n" ) ;
	$mapp = Util_Format_Sanatize( Util_Format_GetVar( "mapp" ), "n" ) ;
	$mapp_reload = Util_Format_Sanatize( Util_Format_GetVar( "mapp_reload" ), "n" ) ;
	$nalert = Util_Format_Sanatize( Util_Format_GetVar( "nalert" ), "n" ) ;
	$salert = Util_Format_Sanatize( Util_Format_GetVar( "salert" ), "n" ) ; // 0 = show sound check div, 1 = hide
	$pop = Util_Format_Sanatize( Util_Format_GetVar( "pop" ), "n" ) ;
	$reload = Util_Format_Sanatize( Util_Format_GetVar( "reload" ), "n" ) ;
	$arn = Util_Format_Sanatize( Util_Format_GetVar( "arn" ), "url" ) ;
	$platform = Util_Format_Sanatize( Util_Format_GetVar( "platform" ), "n" ) ;
	$open_status = Util_Format_Sanatize( Util_Format_GetVar( "open_status" ), "n" ) ; if ( $open_status != 1 ) { $open_status = 0 ; }
	$ip = $token = $vis_token = "" ; // needed for file attachment due to new method in offline form
	$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
	LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ;
	$mobile = ( $os == 5 ) ? 1 : 0 ;
	$theme = $opinfo["theme"] ;
	if ( !is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/style.css" ) || isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }
	if ( !isset( $CONF['foot_log'] ) ) { $CONF['foot_log'] = "on" ; }
	if ( !isset( $CONF['icon_check'] ) ) { $CONF['icon_check'] = "on" ; }

	$opid = $opinfo["opID"] ;
	if ( $mapp && $arn && $platform )
	{
		// double check mobile app arn is same as stored value
		$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
		if ( isset( $mapp_array[$opid] ) )
		{
			$arn_stored = $mapp_array[$opid]["a"] ;
			if ( $arn_stored != $arn )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
				$mapp_array[$opid]["a"] = $arn ;
				$mapp_array[$opid]["p"] = $platform ;
				Util_Vals_WriteToFile( "MAPP", serialize( $mapp_array ) ) ;
			}
		}
	}

	$operators = Ops_get_AllOps( $dbh ) ;
	$departments = Depts_get_AllDepts( $dbh ) ;
	$departments_vars = Depts_get_AllDeptsVars( $dbh ) ;
	$op_depts = Depts_get_OpDepts( $dbh, $opid ) ;
	$externals = External_get_OpExternals( $dbh, $opid ) ;
	$vars = Util_Format_Get_Vars( $dbh ) ;
	$opvars = Ops_get_OpVars( $dbh, $opid ) ;

	$can_cats_admin = ( isset( $VALS["can_cats"] ) && $VALS["can_cats"] ) ? base64_decode( $VALS["can_cats"] ) : "" ;
	if ( $can_cats_admin ) { $opvars["can_cats"] = $can_cats_admin ; }

	$vis_idle_canned = Array() ; $vis_idle_canned_js = "" ;
	if ( isset( $opvars["vis_idle_canned"] ) && $opvars["vis_idle_canned"] )
	{
		$vis_idle_canned = unserialize( $opvars["vis_idle_canned"] ) ;
		$vis_idle_canned_js .= "vis_idle_canned['canid_1'] = '$vis_idle_canned[canid_1]' ; " ;
		$vis_idle_canned_js .= "vis_idle_canned['canid_2'] = '$vis_idle_canned[canid_2]' ; " ;
		$vis_idle_canned_js .= "vis_idle_canned['idle'] = '$vis_idle_canned[idle]' ; " ;
	}

	$op_sounds = ( isset( $VALS["op_sounds"] ) && $VALS["op_sounds"] ) ? unserialize( $VALS["op_sounds"] ) : Array() ;
	if ( isset( $op_sounds[$opid] ) ) { $op_sounds_vals = $op_sounds[$opid] ; $opinfo["sound1"] = $op_sounds_vals[0] ; $opinfo["sound2"] = $op_sounds_vals[1] ; } else { $opinfo["sound1"] = "default" ; $opinfo["sound2"] = "default" ; }

	$console_sound = ( !isset( $opvars["sound"] ) || ( isset( $opvars["sound"] ) && $opvars["sound"] ) ) ? 1 : 0 ;
	$console_blink = ( !isset( $opvars["blink"] ) || ( isset( $opvars["blink"] ) && !$opvars["blink"] ) ) ? 0 : 1 ;
	$console_blink_r = ( !isset( $opvars["blink_r"] ) || ( isset( $opvars["blink_r"] ) && !$opvars["blink_r"] ) ) ? 0 : 1 ;
	$charset = ( isset( $vars["char_set"] ) && $vars["char_set"] ) ? unserialize( $vars["char_set"] ) : Array(0=>"UTF-8") ;
	$cookie_cs = substr( $_COOKIE["cS"], 0, 3 ) ;

	$depts_hash = "depts_hash[1111111111] = 'All Departments' ;" ;
	$depts_rtime_hash = "" ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$depts_hash .= "depts_hash[".$department["deptID"]."] = '$department[name]' ;" ;
		$depts_rtime_hash .= "depts_rtime_hash[".$department["deptID"]."] = '$department[rtime]' ;" ;
	} if ( !$reload ) { Util_Format_CleanDeptOnline( "", $opid ) ; sleep(1) ; }

	$op_depts_hash = $deptids = "" ;
	for ( $c = 0; $c < count( $op_depts ); ++$c )
	{
		$department = $op_depts[$c] ;
		$op_depts_hash .= "op_depts_hash[".$department["deptID"]."] = '$department[name]' ;" ;
		$deptids .= "&d[]=".$department["deptID"] ;
		$flag_file = "online_".$department["deptID"]."_".$opid.".info" ;
		if ( !is_file( "$CONF[CHAT_IO_DIR]/$flag_file" ) ) { touch( "$CONF[CHAT_IO_DIR]/$flag_file" ) ; }
		Chat_remove_ExpiredTranscript( $dbh, $department["deptID"], $department["texpire"] ) ;
	}

	// few lang vars shared with visitor JS functions
	$LANG = Array() ;
	$LANG["CHAT_NOTIFY_DISCONNECT"] = "The party has left.  Chat has ended." ;
	$LANG["CHAT_JS_BLANK_EMAIL"] = "Please provide an email address." ;
	$LANG["CHAT_JS_INVALID_EMAIL"] = "Email format is invalid.  (example: name@domain.com)" ;
	$LANG["CHAT_JS_EMAIL_SENT"] = "Email Sent" ;

	$dept_emo = ( isset( $VALS["EMOS"] ) && $VALS["EMOS"] ) ? unserialize( $VALS["EMOS"] ) : Array() ;
	$dept_emos = "" ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$deptid = $department["deptID"] ;
		if ( isset( $dept_emo[$deptid] ) )
			$dept_emos .= "dept_emos[$deptid] = $dept_emo[$deptid] ;" ;
		else
			$dept_emos .= "dept_emos[$deptid] = 1 ;" ;
	}

	$dept_ctimer = "" ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$deptid = $department["deptID"] ;
		$dept_ctimer .= "dept_ctimer[$deptid] = $department[ctimer] ;" ;
	}

	$profile_pic_url = Util_Upload_GetLogo( "profile", $opid ) ;
	if ( is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) )
	{
		@unlink( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) ;
	}
	$autolinker_js_file = ( isset( $VARS_JS_AUTOLINK_FILE ) && ( ( $VARS_JS_AUTOLINK_FILE == "min" ) || ( $VARS_JS_AUTOLINK_FILE == "src" ) ) ) ? "autolinker_$VARS_JS_AUTOLINK_FILE.js" : "autolinker_min.js" ;

	$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;
	$tags_options = "" ;
	foreach( $tags as $index => $value )
	{
		if ( $index != "c" )
		{
			LIST( $status, $color, $tag ) = explode( ",", $value ) ;
			$tag = rawurldecode( $tag ) ;
			if ( $status )
				$tags_options .= "<option value=\"$index\">$tag</option>" ;
		}
	}

	$attach_icon = "$CONF[DOCUMENT_ROOT]/themes/$theme/attach.png" ;
	if ( is_file( $attach_icon ) ) { $attach_icon = "$CONF[BASE_URL]/themes/$theme/attach.png" ; }
	else { $attach_icon = "$CONF[BASE_URL]/pics/icons/attach.png" ; }
	$typing_icon = "$CONF[DOCUMENT_ROOT]/themes/$theme/typing.png" ;
	if ( is_file( $typing_icon ) ) { $typing_icon = "$CONF[BASE_URL]/themes/$theme/typing.png" ; }
	else { $typing_icon = "$CONF[BASE_URL]/pics/icons/typing.png" ; }
	$can_upload = ( $opinfo["upload"] ) ? 1 : 0 ;

	$VALS_ADDONS = Array() ; if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
	$proactions_string = "" ;
	if ( isset( $VALS_ADDONS["proaction"] ) && $VALS_ADDONS["proaction"] )
	{
		$proactions = unserialize( base64_decode( $VALS_ADDONS["proaction"] ) ) ;
		foreach ( $proactions as $proid => $proaction_array )
		{
			if ( $proid && isset( $proaction_array["message"] ) )
				$proactions_string .= "addon_proactions['$proid'] = \"".rawurlencode( $proaction_array["message"] ) ."\" ; " ;
		}
	}
	$salt = md5( md5( $CONF["SALT"] ).$opinfo["opID"].$opinfo["ses"] ) ;
	$messageboard_id = 0 ;
	$query = "SELECT MAX(messageID) AS messageID FROM p_mboard" ;
	database_mysql_query( $dbh, $query ) ;
	if ( $dbh[ 'ok' ] ) { $data = database_mysql_fetchrow( $dbh ) ; if ( isset( $data["messageID"] ) ) { $messageboard_id = $data["messageID"] ; } }
	$addon_whisper = is_file( "$CONF[DOCUMENT_ROOT]/addons/whisper/inc_whisper.php" ) ? 1 : 0 ;
	$addon_voice_chat = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/voice_chat/inc_op.php" ) ) ? 1 : 0 ;
	$vc_stuns = ( $addon_voice_chat && isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["voice_chat"] ) && $VALS_ADDONS["voice_chat"] ) ? unserialize( rawurldecode( base64_decode( $VALS_ADDONS["voice_chat"] ) ) ) : Array() ;
	try{
		$vc_stuns = ( $vc_stuns ) ? json_encode( $vc_stuns ) : "{}" ;
	} catch (Exception $e) {
		$vc_stuns = "{}" ;
	}
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> Operator Console: <?php echo $opinfo["name"] ?> </title>

<meta name="description" content="v.<?php echo $VERSION ?>">
<meta name="keywords" content="<?php echo md5( $KEY ) ?>">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=<?php echo $charset[0] ?>">
<?php include_once( "../inc_meta_dev.php" ) ; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

<link rel="Stylesheet" href="../themes/<?php echo $theme ?>/style.css?<?php echo filemtime ( "../themes/$theme/style.css" ) ; ?>" />
<?php if ( $mapp ): ?>
<link rel="Stylesheet" href="../mapp/css/mapp.css?<?php echo filemtime ( "../mapp/css/mapp.css" ) ; ?>" id="stylesheet" />
<script data-cfasync="false" type="text/javascript" src="../mapp/js/mapp.js?<?php echo filemtime ( "../mapp/js/mapp.js" ) ; ?>"></script>
<?php else: ?>
<script data-cfasync="false" type="text/javascript" src="../js/winapp.js?<?php echo filemtime ( "../js/winapp.js" ) ; ?>"></script>
<?php if ( ( !preg_match( "/(MSIE 6)|(MSIE 7)|(MSIE 8)/i", $agent ) && !$mapp ) || $wp ): ?><script data-cfasync="false" type="text/javascript" src="../js/sleep.js?<?php echo $VERSION ?>"></script><?php endif ; ?>
<?php endif ; ?>
<script data-cfasync="false" type="text/javascript" src="../js/dn.js?<?php echo filemtime ( "../js/dn.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo filemtime ( "../js/global.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/global_ajax.js?<?php echo filemtime ( "../js/global_ajax.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/youtube-vimeo-url-parser.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/variables.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/paste_upload.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/modernizr.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/<?php echo $autolinker_js_file ?>?<?php echo $VERSION ?>"></script>
<style>
.img_message_board_icon_pulse{
	box-shadow: 0 0 0 0 rgba(232, 76, 61, 0.7);
	border-radius: 50%;
	background-color: #e84c3d;
	-webkit-animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
	-moz-animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
	-ms-animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
	animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
}
@-webkit-keyframes pulse {to {box-shadow: 0 0 0 45px rgba(232, 76, 61, 0);}}
@-moz-keyframes pulse {to {box-shadow: 0 0 0 45px rgba(232, 76, 61, 0);}}
@-ms-keyframes pulse {to {box-shadow: 0 0 0 45px rgba(232, 76, 61, 0);}}
@keyframes pulse {to {box-shadow: 0 0 0 45px rgba(232, 76, 61, 0);}}
.img_voice_chat_icon_pulse{
	box-shadow: 0 0 0 0 rgba(0, 230, 140, 0.7);
	border-radius: 50%;
	background-color: #00E68C;
	-webkit-animation: pulse_vc 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
	-moz-animation: pulse_vc 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
	-ms-animation: pulse_vc 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
	animation: pulse_vc 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
}
@-webkit-keyframes pulse_vc {to {box-shadow: 0 0 0 45px rgba(0, 230, 140, 0);}}
@-moz-keyframes pulse_vc {to {box-shadow: 0 0 0 45px rgba(0, 230, 140, 0);}}
@-ms-keyframes pulse_vc {to {box-shadow: 0 0 0 45px rgba(0, 230, 140, 0);}}
@keyframes pulse_vc {to {box-shadow: 0 0 0 45px rgba(0, 230, 140, 0);}}
</style>
<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var version = "<?php echo $VERSION ?>" ;
	var base_url = ".." ;
	var base_url_full = "<?php echo $CONF["BASE_URL"] ?>" ; var conf_extend = "<?php echo ( isset( $CONF_EXTEND ) && $CONF_EXTEND ) ? $CONF_EXTEND : "" ; ?>" ;
	var phplive_proto = ( location.href.indexOf("https") == 0 ) ? 1 : 0 ; // to avoid JS proto error, use page proto for areas needing to access the JS objects
	if ( !phplive_proto && ( base_url_full.match( /http/i ) == null ) ) { base_url_full = "http:"+base_url_full ; }
	else if ( phplive_proto && ( base_url_full.match( /https/i ) == null ) ) { base_url_full = "https:"+base_url_full ; }
	var debug = 0 ; // write various logs in console.log
	var sound_volume = 1 ;
	var salt = "<?php echo $salt ?>" ;
	var proto = phplive_proto ;
	var ses_console = "<?php echo Util_Format_RandomString( 3 ) ?>" ;
	var audio_supported = HTML5_audio_support() ;
	var messageboard_id = <?php echo $messageboard_id ?> ; var messageboard_id_mgb = <?php echo $messageboard_id ?> ; var messageboard_emoji = "" ; var messageboard_all_loaded = 0 ;
	var mp3_support = ( typeof( audio_supported["mp3"] ) != "undefined" ) ? 1 : 0 ;
	var phplive_session_support = ( typeof( Storage ) != "undefined" ) ? 1 : 0 ;
	if ( phplive_session_support )
	{
		try {
			// initial check
			var temp_sound_volume = localStorage.getItem("volume_newrequest") ;
			if ( temp_sound_volume ) { sound_volume = temp_sound_volume ; }
			var temp_messageboard_id_mgb = localStorage.getItem("messageboard_id_mgb") ;
			// for alert purposes when they log back in
			if ( temp_messageboard_id_mgb ) { messageboard_id = temp_messageboard_id_mgb ; messageboard_id_mgb = temp_messageboard_id_mgb ; }
		} catch (error) {}
	}

	var isop = <?php echo $opid ?> ; var isop_ = 0 ; var isop__ = 0 ;
	var viewip = <?php echo $opinfo["viewip"] ?> ;
	var nchats = <?php echo $opinfo["nchats"] ?> ;
	var cname = "<?php echo $opinfo["name"] ?>" ; var cemail = "<?php echo $opinfo["email"] ?>" ;
	var ces, ces_trans, info, extra ;
	var ck_his = new Array(), ex_his = new Array(), bk_his = new Array(), markets = new Array() ;
	var st_resize, st_typing, st_flash_console, st_logout ;
	var si_offline, si_title, si_typing, si_rating, si_automatic_offline, si_istyping_clear ;
	var si_his = new Object, maps_his = new Object, maps_his_ = new Object, iframe_his = new Object, cl_his = new Object, rtimer_his = new Object, missed_his = new Object ;
	var tim_offline ; var dept_offline = <?php echo $opinfo["dept_offline"] ?> ;
	var prev_status = -1 ; var current_status = 0 ;
	var rupdated ; // flag to tell if chats were removed in DB
	var traffic = <?php echo $opinfo["traffic"] ?> ; var op2op_enabled = <?php echo $opinfo["op2op"] ?> ;
	var dn_status ;
	var dn_enabled_response = <?php echo ( isset( $opvars["dn_response"] ) ) ? $opvars["dn_response"] : 0 ; ?> ;
	var dn_enabled_request = <?php echo ( isset( $opvars["dn_request"] ) ) ? $opvars["dn_request"] : 0 ; ?> ;
	var dn_always = <?php echo ( isset( $opvars["dn_always"] ) ) ? $opvars["dn_always"] : 0 ; ?> ;
	var wp = ( ( typeof( window.external ) != "undefined" ) && ('wp_total_visitors' in window.external) ) ? 1 : 0 ;
	if ( wp ) { dn_enabled_response = 1 ; dn_enabled_request = 1 ; dn_always = 1 ; wp_init() ; }
	var dn_counter = 0 ; // keeps track of all the dn notices for a session, continous counter
	var prev_traffic = 0 ;
	var mapp = <?php echo $mapp ?> ; var mapp_c = <?php echo ( isset( $opvars["mapp_c"] ) ) ? $opvars["mapp_c"] : 0 ; ?> ;
	if ( !mapp ) { set_cookie( "cSC", ses_console, 60*60*24*30 ) ; }
	var mapp_obj = new Object ; var external_url = "" ;
	var mapp_build = 5 ; // exists-5 = Mobile App 5.0, 6 = ...
	var mobile = ( <?php echo $mobile ?> ) ? is_mobile() : 0 ; var is_chrome = Is_Chrome() ;
	var phplive_mobile = 0 ;
	var phplive_userAgent = navigator.userAgent || navigator.vendor || window.opera ;
	if ( phplive_userAgent.match( /iPad/i ) || phplive_userAgent.match( /iPhone/i ) || phplive_userAgent.match( /iPod/i ) )
	{
		if ( phplive_userAgent.match( /iPad/i ) ) { phplive_mobile = 0 ; }
		else { phplive_mobile = 1 ; }
	}
	else if ( phplive_userAgent.match( /Android/i ) ) { phplive_mobile = 2 ; }
	var peer = ( wp ) ? 0 : <?php echo ( $addon_voice_chat ) ? $opinfo["peer"] : 0 ; ?> ;
	var phplive_peer_support = 0 ; var webrtc_supported = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || window.RTCPeerConnection ; if ( !mapp && peer && phplive_proto && navigator.mediaDevices && webrtc_supported && ( window.navigator.userAgent.indexOf("Edge") < 0 ) ) { phplive_peer_support = 1 ; } if ( phplive_peer_support ) { var script_peer = document.createElement('script') ; script_peer.type = "text/javascript" ; script_peer.async = true ; script_peer.onload = script_peer.onreadystatechange = function( ) { if ( ( typeof( this.readyState ) == "undefined" ) || ( this.readyState == "loaded" || this.readyState == "complete" ) ) { } } ; script_peer.src = "../addons/voice_chat/js/simplepeer.min.js?<?php echo ( is_file( "../addons/voice_chat/js/simplepeer.min.js" ) ) ? filemtime ( "../addons/voice_chat/js/simplepeer.min.js" ) : $VERSION ; ?>" ; var script_peer_s = document.getElementsByTagName('script')[0] ; script_peer_s.parentNode.insertBefore(script_peer, script_peer_s) ; }
	var peer_request_expire = <?php echo $VARS_PEER_REQUEST_EXPIRE ?> ;
	var total_new_requests = 0 ; var requestid ;
	var traffic_sound = 0 ; var message_board_sound = 1 ; var message_board_pulse = 1 ;
	var chat_sound = <?php echo $console_sound ?> ; <?php if ( $mapp ): ?>chat_sound_mapp = chat_sound ;<?php endif ; ?>
	var console_blink = ( mapp ) ? 0 : <?php echo $console_blink ?> ;
	var console_blink_r = ( mapp ) ? 0 : <?php echo $console_blink_r ?> ;
	var sound_new_request = "<?php echo ( $opinfo["sound1"] ) ? $opinfo["sound1"] : "default" ; ?>" ;
	var sound_new_text = "<?php echo ( $opinfo["sound2"] ) ? $opinfo["sound2"] : "default" ; ?>" ;
	var theme = "<?php echo $theme ?>" ; var theme_timestamp = <?php echo filemtime ( "../themes/$theme/style.css" ) ; ?> ;
	var title_orig = document.title ;
	var si_counter = 0 ;
	var si_global_counter ; var global_counter = 0 ;
	var logout_timer = 90 ;
	var focused = 1 ;
	var fetch_rating_flag = 0 ;
	var reconnect_counter = 0 ; // reconnection flag so it runs once
	var network_counter = 0 ; var ping_counter_rating = 0 ; var ping_counter_req = 0 ; var ping_total_bytes_received = 0 ;
	var embed = 0 ;
	var time_format = <?php echo ( !isset( $VALS['TIMEFORMAT'] ) || ( $VALS['TIMEFORMAT'] != 24 ) ) ? 12 : 24 ; ?> ;
	var total_op_depts = <?php echo count( $op_depts ) ; ?> ;
	var divs_info = Array( "info", "footprints", "transcripts", "transfer", "maps", "spam" ) ;
	var total_markets = 0 ;
	var cans = new Object, vis_idle_canned = new Object ;
	var auto_canid = <?php echo ( isset( $opvars["canID"] ) ) ? $opvars["canID"] : 0 ; ?> ;
	var vclick = 0 ;
	var shortcut_enabled = <?php echo ( isset( $opvars["shorts"] ) && !$mapp ) ? $opvars["shorts"] : 0 ; ?> ;
	var profile_pic_enabled = <?php echo ( $profile_pic_url && !$mapp ) ? 1 : 0 ; ?> ; var profile_pic_url = "<?php echo $profile_pic_url ?>" ;
	var nsleep = ( <?php echo ( isset( $opvars["nsleep"] ) && !$mapp ) ? $opvars["nsleep"] : 0 ; ?> && ( typeof( sleep ) != "undefined" ) && !mapp && ( typeof( audio_supported["audio"] ) != "undefined" ) ) ? 1 : 0 ;
	var maxc = <?php echo ( $opinfo["maxc"] == -1 ) ? 10 : $opinfo["maxc"] ?> ; var maxco = <?php echo $opinfo["maxco"] ?> ;
	var cache_v = "<?php echo $VERSION ?>" ;
	var tag = <?php echo ( $opinfo["tag"] && $tags_options ) ? 1 : 0 ; ?> ;
	var ins = <?php echo $ins ?> ;
	var timestamp = 1 ;
	var auto_offline_string = "" ;
	var arrow_symbol = ( mapp ) ? "&#707;" : "&#11166;" ; var arrow_down_symbol = ( mapp ) ? "&#709;" : "&#11167;" ;

	var status_update_flag = 0 ; // to indiate status is updating for timing of fetch rating
	var addon_voice_chat = <?php echo $addon_voice_chat ?> ;
	var addon_whisper = ( <?php echo $addon_whisper ?> ) ? <?php echo $opid ?> : 0 ;
	var addon_emo = 0 ;
	var dept_emos = new Object ;
	<?php echo $dept_emos ?>
	var addon_vc = new Object ; addon_vc = <?php echo $vc_stuns ?> ;
	var dept_ctimer = new Object ;
	<?php echo $dept_ctimer ?>

	var cans_string ; // global so op_traffic.php can reference
	var initiate_canid = 0 ; // global op_traffic.php
	var initiate_deptid = 0 ; // global op_traffic.php
	var height_chat_body ; // global for toggle_input_text()
	var prev_network_string ;
	var extra_wrapper_height ; // global for reference
	var global_ces_array = new Object ; // global to track switchboard

	var newwin, newwin_print ;

	var vis_token ; // for op_traffic.php
	var automatic_offline_active = 0 ;
	var ao = 0 ; // flag: automatic offline
	var rd = 0 ; // flag: remote disconnect
	var dup = 0 ; // flag: duplicate login
	var mi = 0 ; // flag: mapp idle offline
	var wid = 0 ; // flag: winapp idle offline

	var chats = new Object ;
	var depts_hash = new Object ;
	var depts_rtime_hash = new Object ;
	var op_depts_hash = new Object ;
	var traffic_data = new Object ;
	var messageboard = new Object ;

	// addons related
	var addon_proactions = new Object ; <?php echo $proactions_string ?>

	var stars = new Object ;
	stars[5] = "<?php echo Util_Functions_Stars( "..", 5 ) ; ?>" ;
	stars[4] = "<?php echo Util_Functions_Stars( "..", 4 ) ; ?>" ;
	stars[3] = "<?php echo Util_Functions_Stars( "..", 3 ) ; ?>" ;
	stars[2] = "<?php echo Util_Functions_Stars( "..", 2 ) ; ?>" ;
	stars[1] = "<?php echo Util_Functions_Stars( "..", 1 ) ; ?>" ;
	stars[0] = "<?php echo Util_Functions_Stars( "..", 0 ) ; ?>" ;

	var console_divs = new Array() ;

	var autolinker = new Autolinker( { newWindow: true, stripPrefix: false } ) ;
	var phplive_orientation_isportrait ;

	// p_engine migration
	var stopped = 0 ;
	var reconnect = 0 ;
	var chatting_err_915, chatting_err_815 ;
	var dc_c_queueing = 0 ;
	var c_routing = 0, c_chatting = 0, c_requesting = 0, c_queueing = 0 ;
	var st_routing, st_chatting, st_requesting, st_network, st_connect, st_reconnect, st_queueing ;
	var VARS_JS_REQUESTING = <?php echo $VARS_JS_REQUESTING ?> ;
	var VARS_EXPIRED_QUEUE_IDLE = <?php echo $VARS_EXPIRED_QUEUE_IDLE ?> ;
	var VARS_JS_OP_CONSOLE_TIMEOUT = <?php echo $VARS_JS_OP_CONSOLE_TIMEOUT ?> ;
	var CHAT_ERROR_DC = "<?php echo isset( $LANG["CHAT_ERROR_DC"] ) ? Util_Format_ConvertQuotes( $LANG["CHAT_ERROR_DC"] ) : "Connection error.  Please refresh the page and try again." ; ?>" ;
	var cs = "<?php echo $cookie_cs ?>" ;

	$(document).ready(function()
	{
		$.ajaxSetup({ cache: false }) ;
		/*
		$( document ).ajaxStart(function() { }) ;
		$( document ).ajaxStop(function() { }) ;
		*/
		$("body").fadeIn("slow") ;
		init_divs(0) ;
		init_disconnect() ;

		init_typing() ;
		textarea_listen() ;
		print_chat_sound_image( theme ) ;

		<?php echo $depts_hash ?>

		<?php echo $depts_rtime_hash ?>
		
		<?php echo $op_depts_hash ?>

		<?php echo $vis_idle_canned_js ?>

		<?php if ( $opinfo["traffic"] ): ?>update_traffic_counter( pad( prev_traffic, 2 ) ) ;<?php endif ; ?>

		$('#offline_timer').html( seconds_to_hhmmss( (60)*logout_timer ) ) ;
		$('#reconnect_notice').center() ;

		<?php if ( $reload && !$nalert && !$salert ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>

		timestamp_st_requesting = <?php echo $now ?> ; // initialize
		update_ratings() ;
		si_rating = setInterval( function(){
			if ( !stopped )
				update_ratings() ;
		}, <?php echo $VARS_JS_RATING_FETCH ?> * 1000 ) ;
		if ( mapp ) { init_mapp_console() ; }
		if ( tag ) { $('#tr_tag').show() ; }

		prep_init() ;
		setTimeout( function(){ fetch_markets() ; }, 300 ) ;
		setTimeout( function(){ populate_cans(0) ; }, 800 ) ;

		if ( !total_op_depts )
		{
			logout_timer = 5 ;
			toggle_status(1) ;
			setTimeout(function(){
				clearInterval( si_rating ) ; check_network( 615, undeefined, undeefined ) ; $('#chat_panel').hide() ;
				$('#chat_body').html( '<div id="no_dept" class="info_error" style=\"padding: 10px;\">You are OFFLINE.  Account is not assigned to a department.<div style="margin-top: 15px;"><img src="../themes/'+theme+'/alert.png" width="16" height="16" border="0" alt=""> Please contact the Setup Admin to assign your account to a department.  Once assigned, <a href="#" onClick="refresh_console_online(1)" style="color: #FFFFFF;">refresh this page</a> to go online.</div></div>' ).children(':last').hide().fadeIn(500) ;
			}, 1000) ;
		}
		else
		{
			// delay to limit few potential file create/delete issues during duplicate login situations
			setTimeout( function(){ init_console() ; }, 500 ) ;
		}
	});
	$(window).resize(function() {
		// some devices triggers resize on various events, even at full screen
		if ( !mobile && !mapp )
		{
			init_divs(1) ;
			$('#icons_slider').attr( 'src', '../themes/'+theme+'/slider_right.png' ) ;
			$('#chat_info_wrapper_network').hide() ;
			$('#chat_info_wrapper_info').show() ;
			$('#chat_info_container').show() ;
		}
		else if ( mobile === 3 )
		{
			var chat_body_padding_diff = ( typeof( $('#chat_body').css('padding-left') ) != "undefined" ) ? 20 - ( $('#chat_body').css('padding-left').replace( /px/, "" ) * 2 ) : 0 ;
			var browser_width = $(window).width() ;
			var body_width = browser_width - 42 ;
			var chat_body_width = body_width + chat_body_padding_diff ;
			$('#chat_body').css({'width': chat_body_width}) ;
		}
		init_scrolling() ;
		init_maps_iframes() ;
	});

	if ( !wp && !mapp && !ins ) { window.onbeforeunload = function() { pre_logout() ; return "You are about to exit the operator console." ; } }

	$(window).focus(function() {
		input_focus() ;
	});
	$(window).blur(function() {
		focused = 0 ;
	});

	function prep_init()
	{
		if ( !mapp )
		{
			dn_status = dn_check() ;
			if ( dn_status == 1 )
			{
				$('#div_notification').fadeIn( "fast" ) ;
				setTimeout(function(){
					if ( $('#div_notification').is(':visible') ) {
						dn_status = undeefined ;
						$('#div_notification').fadeOut("fast") ;
					}
				}, 300000) ;
			}
			else if ( dn_status == 2 ) {  }
			$('#canned_select_btn').dblclick(function() { add_text_prepare(1) ; }) ;

			// IE edge bug fix of div not displaying (seems to be IE edge bug)
			if ( /Edge\/\d./i.test(navigator.userAgent) )
			{
				open_network_status() ; // first trigger display
				setTimeout(function(){ open_network_status() ; }, 50) ; // set it back after display
			}

			if ( typeof( si_global_counter ) != "undefined" ) { clearInterval( si_global_counter ) ; }
			si_global_counter = setInterval( "global_counter += 1", 1000 ) ;

			if ( !wp && !<?php echo $salert ?> && mp3_support )
			{
				$('#div_sound_interaction').show() ;
				if ( !chat_sound )
				{
					$('#div_sound_interaction_message_off').show() ;
				}
				$('#div_sound_interaction_message').center().show() ;
			}
			else if ( !mp3_support )
				$('#options_sound').hide() ;

			$('#options_expand').show() ;
			$('#options_expand_v').show() ;
			init_info() ;
		}
		else
		{
			dn_enabled_response = dn_enabled_request = dn_always = 0 ;
			init_external_url() ;
		}
		if ( profile_pic_enabled ) { var name = cname.replace( / (.*)/, "" ) ; $('#div_profile_pic').html('<img src="<?php echo $profile_pic_url ?>" width="55" height="55" border=0 class="profile_pic_img" id="img_profile_pic" style="border-radius: 50%;"><div style="margin-top: 5px; font-size: 10px;" alt="view profile" title="view profile">'+name+'</div>').fadeIn( "fast" ) ; }
		<?php if ( $VARS_INI_UPLOAD && $can_upload && is_file( "$CONF[DOCUMENT_ROOT]/addons/file_attach/file_attach.php" ) ): ?>
			try {
				document.getElementById("input_text").addEventListener("paste", paste_upload_init) ;
			} catch(err) { }
		<?php endif ; ?>
		if ( typeof( si_istyping_clear ) != "undefined" ) { clearInterval( si_istyping_clear ) ; }
		si_istyping_clear = setInterval(function() { init_clear_istyping_check() ; }, 1000) ;
		check_network(.0, 1, 0) ;
	}

	function init_console()
	{
		if ( !<?php echo $open_status ?> ) { toggle_status(0) ; }
		else { toggle_status(1) ; }

		setTimeout( function(){
			// p_engine migration
			if ( typeof( st_requesting ) != "undefined" ) { clearTimeout( st_requesting ) ; }
			st_requesting = setTimeout( "requesting()", 1000 ) ; // slight delay (not really needed)
			// p_engine migration end
		}, 800 ) ;
	}

	function init_nsleep()
	{
		if ( !mapp && nsleep && sleep.ispaused() )
		{
			sleep.prevent() ;
			nsleep = 2 ;
		}
	}

	var global_maxc_flag = 0 ; // indication of automatic maxc offline triggered
	function init_maxc()
	{
		var total_active_chats = 0 ;
		for ( var thisces in chats )
		{
			if ( ( parseInt( chats[thisces]["status"] ) == 1 ) && !chats[thisces]["disconnected"] )
				++total_active_chats ;
		}

		if ( maxco && total_active_chats && ( total_active_chats >= maxc ) )
		{
			global_maxc_flag = 1 ;

			if ( !$('#chat_status_offline').is(':visible') )
				toggle_status(1) ;
		}
		else if ( maxco && ( total_active_chats < maxc ) && global_maxc_flag )
		{
			global_maxc_flag = 0 ;

			if ( ( prev_status == 1 ) && $('#chat_status_offline').is(':visible') )
				toggle_status(0) ;
		}
	}

	function init_external_url()
	{
		$("a").click(function(){
			var temp_url = $(this).attr( "href" ) ;
			if ( !temp_url.match( /javascript/i ) )
			{
				external_url = temp_url ;
				return false ;
			}
		});
	}

	function pre_logout()
	{
		toggle_status(1) ;
	}

	function toggle_input_text()
	{
		var height_input_text = $("textarea#input_text").height() ;
		if ( height_input_text == 69 )
		{
			height_chat_body = $('#chat_body').height() ;

			var height_new = height_chat_body - (250-69) ;
			$('#chat_body').animate({
				height: height_new
			}, 300, function() {
				$("#chat_input").css({'bottom': -50}) ; 
				$("textarea#input_text").animate({
					height: 250
				}, 300, function() {
					init_scrolling() ;
					$('textarea#input_text').focus() ;
					// place cursor at end
					var temp = $('textarea#input_text').val() ; $('textarea#input_text').val(temp) ;
				});
				if ( $('#div_notification').is(':visible') ) { $('#div_notification').fadeOut("fast") ; }
			});
		}
		else
		{
			$("#chat_input").css({'bottom': "auto"}) ;
			$("textarea#input_text").animate({
				height: 69
			}, 300, function() {
				$('#chat_body').animate({
					height: height_chat_body
				}, 300, function() {
					init_scrolling() ;
					$('textarea#input_text').focus() ;
					// place cursor at end
					var temp = $('textarea#input_text').val() ; $('textarea#input_text').val(temp) ;
				});
			});
		}
	}

	function init_maps_iframes()
	{
		var height = $('#chat_body').height() - 70 ;
		for ( var thisces in maps_his )
			$('#iframe_maps_'+thisces).css({'height': height}) ;
		
		for ( var thisdiv in ex_his )
		{
			$( '*', '#'+thisdiv ).each( function(){
				var div_name = $( this ).attr('id') ;
				init_iframe( div_name ) ;
			} );
		}
	}

	function menu_blink( thecolor, theces )
	{
		if ( typeof( si_his[theces] ) == "undefined" )
		{
			if ( typeof( bk_his[theces] ) == "undefined" )
				bk_his[theces] = 1 ;

			if ( mapp && !$('#div_mapp_chat_bubble_red').is(':visible') )
			{
				$('#div_mapp_chat_bubble_red').fadeIn( "fast" ) ;
			}
			si_his[theces] = setInterval(function(){ menu_blink_doit( thecolor, theces ) ; }, 1000) ;
		}
	}

	function menu_blink_doit( thecolor, theces )
	{
		var offcolor ;
		if ( thecolor == "red" )
			offcolor = "green" ;
		else
			offcolor = "red" ;

		if ( !( bk_his[theces] % 2 ) )
			$('#menu_'+theces).removeClass('chat_switchboard_cell_bl_'+thecolor).removeClass('chat_switchboard_cell_bl_'+offcolor).addClass('chat_switchboard_cell') ;
		else
			$('#menu_'+theces).removeClass('chat_switchboard_cell').addClass('chat_switchboard_cell_bl_'+thecolor) ;

		bk_his[theces] += 1 ;
	}

	function new_chat( thejson_data, theflag )
	{
		var thisces = thejson_data["ces"] ;
		var is_in_his = is_ces_in_his( thisces ) ;
		rupdated = theflag ;

		// if 615 flag, visitor has improperly closed chat and the status is stuck on previous decline... bypass
		if ( thejson_data["vup"] == 615 ) { return true ; }

		// fixes random UI quirk
		if ( !mobile ) { $(window).scrollTop(0) ; }

		if ( typeof( chats[thisces] ) == "undefined" )
		{
			if ( !is_in_his && ( typeof( cl_his[thisces] ) == "undefined" ) )
			{
				// parseInt() not needed in some areas but same measure
				chats[thisces] = new Object ;
				chats[thisces]["requestid"] = parseInt( thejson_data["rid"] ) ;
				chats[thisces]["tupdated"] = parseInt( thejson_data["tupdated"] ) ;
				chats[thisces]["deptid"] = parseInt( thejson_data["did"] ) ;
				chats[thisces]["opid"] = parseInt( thejson_data["opid"] ) ;
				chats[thisces]["op2op"] = ( thejson_data["status"] != 2 ) ? parseInt( thejson_data["op2op"] ) : 0 ;
				chats[thisces]["opid_orig"] = <?php echo $opid ?> ;
				chats[thisces]["mapp"] = <?php echo $opinfo["mapp"] ?> ;
				chats[thisces]["status"] = parseInt( thejson_data["status"] ) ;
				chats[thisces]["initiated"] = parseInt( thejson_data["initiated"] ) ;
				chats[thisces]["auto_pop"] = thejson_data["auto_pop"] ;
				chats[thisces]["disconnected"] = 0 ;
				chats[thisces]["closed"] = 0 ;
				chats[thisces]["tooslow"] = 0 ;
				chats[thisces]["vname"] = thejson_data["vname"] ;
				chats[thisces]["os"] = thejson_data["os"] ;
				chats[thisces]["browser"] = thejson_data["browser"] ;
				chats[thisces]["resolution"] = thejson_data["resolution"] ;
				chats[thisces]["vemail"] = ( thejson_data["vemail"] != "null" ) ? thejson_data["vemail"] : "" ;
				chats[thisces]["requests"] = thejson_data["requests"] ;
				chats[thisces]["ip"] = thejson_data["ip"] ;
				chats[thisces]["peer"] = thejson_data["peer"] ;
				chats[thisces]["country"] = thejson_data["country"] ;
				chats[thisces]["vis_token"] = thejson_data["vis_token"] ;
				chats[thisces]["onpage"] = decodeURIComponent( thejson_data["onpage"] ) ;
				chats[thisces]["marketid"] = parseInt( thejson_data["marketid"] ) ;
				try{
					chats[thisces]["title"] = decodeURIComponent( thejson_data["title"] ) ;
				} catch(e){
					chats[thisces]["title"] = "?????" ;
				}
				try{
					chats[thisces]["refer_raw"] = decodeURIComponent( thejson_data["refer_raw"] ) ;
				} catch(e){
					chats[thisces]["refer_raw"] = thejson_data["refer_raw"] ;
				}
				chats[thisces]["refer_snap"] = decodeURIComponent( thejson_data["refer_snap"] ) ;
				chats[thisces]["custom"] = decodeURIComponent( thejson_data["custom"] ) ;
				chats[thisces]["question"] = thejson_data["question"] ;
				chats[thisces]["footprints"] = 0 ;
				chats[thisces]["transcripts"] = 0 ;
				chats[thisces]["now"] = parseInt( thejson_data["now"] ) ;
				chats[thisces]["timer"] = parseInt( thejson_data["created"] ) ;
				chats[thisces]["rtimer"] = pad( 0, 2 ) ;
				chats[thisces]["istyping"] = 0 ;
				chats[thisces]["istyping_counter"] = 0 ;
				chats[thisces]["input"] = "" ;
				chats[thisces]["recent_res"] = unixtime() ;
				chats[thisces]["tag"] = 0 ;
				chats[thisces]["op2op_"] = ( chats[thisces]["vis_token"] == "op2op" ) ? parseInt( thejson_data["op2op"] ) : 0 
				chats[thisces]["group_chat"] = ( chats[thisces]["vis_token"] == "grc" ) ? 1 : 0 ;
				chats[thisces]["gravatar"] = ( thejson_data["gravatar"] != "" ) ? "<span id=\"img_gravatar\"><img src=\""+thejson_data["gravatar"]+"\" border=0 class=\"round\" alt=\"Gravatar\" title=\"Gravatar\" style=\"border-radius: 50%;\"></span>" : "" ;
				chats[thisces]["processed"] = unixtime() ;
				chats[thisces]["fmindex"] = 0 ; chats[thisces]["fmsize"] = 0 ; chats[thisces]["fmlid"] = "" ;

				if ( chats[thisces]["initiated"] )
				{
					input_focus() ;
					chats[thisces]["timer"] = unixtime() ;
					chats[thisces]["trans"] = "<div class=\"ca\"><div class=\"ctitle\">Chat Invite.  <span id=\"trans_title\">Connecting</span>...</div></div>" ;
				}
				else if ( chats[thisces]["status"] == 1 )
				{
					chats[thisces]["trans"] = "" ;
				}
				else if ( chats[thisces]["op2op"] == isop )
				{
					// set status as picked up so the operator can send messages of important note prior to
					// receiving operator accept/decline
					chats[thisces]["status"] = 1 ;
					chats[thisces]["timer"] = unixtime() ;
					// <op2op> flag to indicate remove when picked up
					chats[thisces]["trans"] = "<c615><op2op><div class=\"ca\">Requesting Operator to Operator Chat. Connecting...</div></op2op></c615>" ;
				}
				else if ( chats[thisces]["op2op"] && ( chats[thisces]["status"] != 2 ) )
				{
					chats[thisces]["trans"] = "<c615><div class=\"ca\">Operator to Operator Chat Request from <b>"+chats[thisces]["vname"]+"</b></div><div class=\"ca\"><button type=\"button\" class=\"input_op_button\" style=\"padding: 10px;\" onClick=\"$(this).attr('disabled', 'true');chat_accept();\">accept</button> &nbsp; or &nbsp;  <span onClick=\"chat_decline()\" style=\"text-decoration: underline; cursor: pointer;\">decline</span></div></c615>" ;
				}
				else if ( chats[thisces]["status"] == 2 )
				{
					chats[thisces]["timer"] = unixtime() ;
					chats[thisces]["trans"] = "<c615><div class=\"ca\"><div class=\"info_box\"><i>"+thejson_data["question"]+"</i></div> <div style=\"margin-top: 15px;\"><div class=\"info_content ctitle\">Transferred Chat</div><div style=\"margin-top: 10px;\"><button type=\"button\" class=\"input_op_button\" style=\"padding: 10px;\" onClick=\"$(this).attr('disabled', 'true');chat_accept();\">accept</button> &nbsp; or &nbsp; <span onClick=\"chat_decline()\" style=\"text-decoration: underline; cursor: pointer;\">decline</span></div></div></c615>" ;

					var timer_start = ( typeof( depts_rtime_hash[chats[thisces]["deptid"]] ) != "undefined" ) ? parseInt( depts_rtime_hash[chats[thisces]["deptid"]] ) : 0 ;
					timer_start -= <?php echo $VARS_JS_REQUESTING ?> ;
					init_rtimer( chats[thisces]["deptid"], thisces, timer_start ) ;
				}
				else
				{
					var transferred_string = "" ;
					if ( chats[thisces]["tupdated"] == 2 )
						transferred_string = "<div style=\"margin-top: 15px;\"><div class=\"info_content ctitle\">Transferred Chat</div>" ;
					else if ( chats[thisces]["tupdated"] == 3 )
						transferred_string = "<div style=\"margin-top: 15px;\"><div class=\"info_content ctitle\">Bot Transferred Chat</div>"

					chats[thisces]["trans"] = "<c615><div class=\"ca\">"+
						"<div class=\"info_box\">"+
							"<i>"+thejson_data["question"]+"</i>"+
						"</div>"+transferred_string+
						"<div style=\"margin-top: 10px;\">"+
							"<div style=\"margin-top: 10px;\">"+
								"<button type=\"button\" style=\"padding: 10px;\" class=\"input_op_button\" onClick=\"$(this).attr('disabled', 'true');chat_accept();\">accept</button> &nbsp; or &nbsp; <span onClick=\"chat_decline()\" style=\"text-decoration: underline; cursor: pointer;\">decline</span>"+
							"</div>"+
						"</div></c615>" ;

					var timer_start = 0 ;
					if ( typeof( depts_rtime_hash[chats[thisces]["deptid"]] ) != "undefined" )
					{
						// future update space
						timer_start = depts_rtime_hash[chats[thisces]["deptid"]] ;
					}
					timer_start -= Math.round( parseInt( <?php echo $VARS_JS_REQUESTING ?> )/2 ) ; // few seconds for delay
					init_rtimer( chats[thisces]["deptid"], thisces, timer_start ) ;
				}

				chats[thisces]["chatting"] = 0 ;
				cl_his[thisces] = true ;

				if ( !chats[thisces]["initiated"] && ( chats[thisces]["op2op"] != isop ) )
				{
					if ( chat_sound && ( !chats[thisces]["status"] || ( chats[thisces]["status"] == 2 ) ) )
						play_sound( 1, "new_request", "new_request_"+sound_new_request ) ;
					if ( console_blink && ( !chats[thisces]["status"] || ( chats[thisces]["status"] == 2 ) ) )
						flash_console(0) ;

					title_blink_init() ;
				}

				// if console was refreshed
				if ( chats[thisces]["status"] != 1 )
				{
					if ( !chats[thisces]["initiated"] && ( chats[thisces]["op2op"] != isop ) )
					{
						if ( dn_enabled_request )
							dn_show( 'new_chat', thisces, chats[thisces]["vname"], thejson_data["question"].replace( /<br>/g, ' ' ), 900000 ) ;
					}
				}
				if ( dept_ctimer[chats[thisces]["deptid"]] ) { $('#chat_vtimer_wrapper').show() ; }
				else { $('#chat_vtimer_wrapper').hide() ; }
			}
		}
		else if ( ( chats[thisces]["status"] == 3 ) && ( !thejson_data["status"] || ( thejson_data["status"] == 2 ) ) )
		{
			// transferred chat is transferred BACK to the original operator
			chats[thisces]["trans"] = "<c615><div class=\"ca\"><div class=\"info_box\"><i>"+chats[thisces]["question"]+"</i></div> <div style=\"margin-top: 10px;\"><div class=\"ctitle\">Transferred Chat</div><div style=\"margin-top: 10px;\"><button type=\"button\" class=\"input_op_button\" style=\"\" onClick=\"$(this).attr('disabled', 'true');chat_accept();\">accept</button> &nbsp; or &nbsp; <span onClick=\"chat_decline()\" style=\"text-decoration: underline; cursor: pointer;\">decline</span></div></div></c615>" ;
			if ( thisces == ces )
			{
				var transcript = chats[thisces]["trans"] ;
				$('#chat_body').html( transcript.emos() ).children(':last').hide().fadeIn(500) ;
				init_scrolling() ;
			}
			else
				menu_blink( "red", thisces ) ;

			// set status to transfer so it doesn't repeat the above message
			chats[thisces]["status"] = 2 ;
			chats[thisces]["chatting"] = 0 ;
			chats[thisces]["disconnected"] = 0 ;

			if ( chat_sound )
				play_sound( 1, "new_request", "new_request_"+sound_new_request ) ;
			else
				flash_console(0) ;

			title_blink_init() ;
		}
		else
			chats[thisces]["status"] = thejson_data["status"] ;

		if ( typeof( chats[thisces] ) != "undefined" )
		{
			chats[thisces]["rupdated"] = rupdated ;
		}
	}

	function init_rtimer( thedeptid, theces, thetimer_start )
	{
		if ( typeof( rtimer_his[theces] ) == "undefined" )
		{
			rtimer_his[theces] = setInterval(function(){
				chats[theces]["rtimer"] = pad( thetimer_start, 2 ) ;
				if ( thetimer_start >= 0 )
				{
					// on mobile app don't display timer countdown because the JavaScript process starts when app is opened
					if ( !mapp )
						$('#rtimer_'+theces).html( " <span style=\"font-weight: normal;\">("+chats[theces]["rtimer"]+")</span>" ).show() ;
				}
				else
				{
					clearInterval( rtimer_his[theces] ) ; rtimer_his[theces] = undeefined ;
					setTimeout( function(){ auto_decline_chat( theces ) ; }, 10000 ) ;
				}
				thetimer_start -= 1 ;
			}, 1000) ;
		}
		else { clearInterval( rtimer_his[theces] ) ; rtimer_his[theces] = undeefined ; }
	}

	function auto_decline_chat( theces )
	{
		// chat was most likely abandoned by the visitor, causing the routing process to stop
		// decline it since operator did not take action
		chat_decline( theces ) ;
	}

	function init_chat_list( theflag )
	{
		var thisclass, thisces, thisces_temp, thisimage ;
		var list_string = "" ; var refresh_list_string = 0 ;
		var ces_array = new Array() ;

		clean_chats( theflag ) ;

		var t_chats = total_chats() ;

		for ( var thisces in chats )
		{
			if ( typeof( global_ces_array[thisces] ) == "undefined" ) { global_ces_array[thisces] = 1 ; refresh_list_string = 1 ; }
			ces_array.push( thisces ) ;
		}
		for ( var thisces in global_ces_array )
		{
			if ( typeof( chats[thisces] ) == "undefined" ) { delete global_ces_array[thisces] ; refresh_list_string = 1 ; }
		}

		ces_array.sort() ;

		var obj_length = ces_array.length ;
		for ( var c = 0; c < obj_length; ++c )
		{
			var thisces = ces_array[c] ;

			if ( thisces == ces )
			{
				thisclass = "chat_switchboard_cell_focus" ;
				thisimage = "online_green.png" ;
			}
			else
			{
				thisclass = "chat_switchboard_cell" ;
				thisimage = "online_grey.png" ;
			}

			var icon_op2op = "" ;
			if  ( ( chats[thisces]["status"] != 2 ) && chats[thisces]["op2op"] )
				icon_op2op = " <img src=\"../themes/initiate/agent.png\" width=\"12\" height=\"12\" border=\"0\" title=\"Operator-to-Operator chat\" alt=\"Operator-to-Operator chat\">" ;
			else if ( chats[thisces]["group_chat"] )
				icon_op2op = " <img src=\"../themes/initiate/group.png\" width=\"12\" height=\"12\" border=\"0\" title=\"Group Chat\" alt=\"Group Chat\">" ;

			var rtimer_style_display = ( ( typeof( rtimer_his[thisces] ) == "undefined" ) || mapp ) ? "display: none;" : "" ;
			list_string += "<div id=\"menu_"+thisces+"\" class=\""+thisclass+"\" style=\"float: left;\" onClick=\"activate_chat('"+thisces+"')\"><img src=\"../themes/"+theme+"/"+thisimage+"\" border=\"0\" id=\"menu_img_"+thisces+"\"> "+chats[thisces]["vname"]+icon_op2op+" <span id=\"rtimer_"+thisces+"\" style=\"font-weight: normal; "+rtimer_style_display+"\"> ("+chats[thisces]["rtimer"]+")</span></div>" ;
		}

		if ( t_chats )
		{
			list_string += "<div style=\"clear:both\"></div>" ;
			$('#options_print').fadeIn( "fast" ) ;

			if ( !total_new_requests )
			{
				clear_sound( "new_request" ) ;
			}
		}
		else
		{
			ces = undeefined ;
			reset_canvas() ;
			disconnect_showhide() ;
			if ( $('#options_print').is(":visible") )
				$('#options_print').hide() ;

			title_blink( 0, title_orig, "reset" ) ;

			clear_sound( "new_request" ) ;
			if ( focused ) { clear_flash_console() ; }
			past_upload_processing = 0 ; // reset - not needed but just in case it gets stuck
		}

		if ( refresh_list_string ) { $('#chat_switchboard').html( list_string ) ; }
		if ( ( t_chats == 1 ) && ( $('#req_ces').html() != thisces ) )
		{
			if ( ces != thisces )
			{
				activate_chat( thisces ) ;
			}
		}
		else if ( t_chats && ( typeof( ces ) == "undefined" ) )
		{
			activate_chat( get_chat_prev() ) ;
		}

		for ( var thisces in chats )
		{
			if ( ces != thisces )
			{
				if ( !chats[thisces]["status"] && !chats[thisces]["initiated"] )
				{
					menu_blink( "red", thisces ) ;
				}
				else if ( chats[thisces]["status"] == 2 )
				{
					menu_blink( "red", thisces ) ;
				}
			}
		}
		if ( isop && !mapp ) { init_maxc() ; }

		// process additional actions after first initial requesting()
		if ( !mapp && !$('#chat_panel').is(":visible") )
		{
			$('#chat_panel').css({'bottom': -100}) ;
			$('#chat_panel').show().animate({
				bottom: 55
			}, 800, function() {
				//
			});
		}
	}

	function clean_chats( theflag )
	{
		rupdated = ( theflag ) ? theflag : rupdated ;
		for ( var thisces in chats )
		{
			if ( !chats[thisces]["initiated"] && ( chats[thisces]["rupdated"] != rupdated ) )
			{
				if ( ( !chats[thisces]["disconnected"] && !chats[thisces]["status"] && !chats[thisces]["op2op"] ) || ( chats[thisces]["status"] == 2 ) )
				{
					// missed chat
					// missed_his[thisces] = chats[thisces] ;
					$('#options_alert').css({ 'opacity': '1' }) ;
					delete_chat_session( thisces ) ;
				}
				else if ( chats[thisces]["op2op"] && !chats[thisces]["status"] )
				{
					// 2 situations
					// A - operator to operator chat
					// B - transferred chat with op2op being the original operator ID
					//
					// only automatically clean the chat if expired transfer chat
					if ( !chats[thisces]["disconnected"] )
					{
						delete_chat_session( thisces ) ;
					}
				}
			}
		}	
	}

	function init_iframe( theiframe )
	{
		extra_wrapper_height = $('#chat_extra_wrapper').outerHeight() - $('#chat_footer').outerHeight() - 5 ;

		// add bottom padding so it shows entire website
		if ( theiframe.indexOf( "iframe_ext_" ) == 0 )
			extra_wrapper_height -= 30 ;
		else if ( theiframe == "iframe_settings" )
			extra_wrapper_height += 8 ;

		$('#'+theiframe).css({'height': extra_wrapper_height}) ;
	}

	function init_extra()
	{
		$('#chat_extra_wrapper').show() ;
	}

	function init_extra_loaded()
	{
		//
	}

	function init_chats()
	{
		for ( var thisces in chats )
		{
			if ( typeof( chats[thisces] ) != "undefined" )
				chats[thisces]["processed"] = unixtime() ;
		}
	}

	function total_chats()
	{
		var total = 0 ;
		total_new_requests = 0 ;

		for ( var thisces in chats )
		{
			++total ;
			// transferred chats are considered new chats
			if ( ( !chats[thisces]["status"] || ( chats[thisces]["status"] == 2 ) ) && !chats[thisces]["initiated"] && ( chats[thisces]["op2op"] != isop ) )
				++total_new_requests ;
		}
		return total ;
	}

	function activate_chat( theces )
	{
		if ( past_upload_processing )
		{
			do_alert( 0, "File upload in progress..." ) ;
			return false ;
		}
		if ( mapp )
		{
			close_extra( extra ) ;
			if ( $('#div_mapp_chat_bubble_red').is(':visible') ) { $('#div_mapp_chat_bubble_red').hide() ; }
		}
		if ( theces == ces ) { return true ; }
		else { if ( typeof( toggle_file_attach ) == "function" ) { toggle_file_attach(1) ; } }

		// store text to memory to place back when focused
		if ( typeof( chats[ces] ) != "undefined" )
		{
			chats[ces]["input"] = $( "textarea#input_text" ).val() ;
		}
		toggle_last_response(1) ;
		ces = theces ;

		$('#req_tag_saved').hide() ;

		if ( typeof( chats[ces] ) != "undefined" )
		{
			requestid = chats[ces]["requestid"] ;
			isop_ = chats[ces]["op2op"] ;
			isop__ = chats[ces]["opid"] ;

			if ( typeof( si_his[ces] ) != "undefined" ) { clearInterval( si_his[ces] ) ; delete bk_his[ces] ; delete si_his[ces] ; }
			
			if ( ( typeof( dept_emos[chats[ces]["deptid"]] ) != "undefined" ) && dept_emos[chats[ces]["deptid"]] && <?php echo ( is_file( "$CONF[DOCUMENT_ROOT]/addons/emoticons/emoticons.php" ) ) ? 1 : 0 ; ?> )
			{ $('#span_emoticons').fadeIn( "fast" ) ; addon_emo = 1 ; } else { $('#span_emoticons').hide() ; addon_emo = 0 ; }

			var transcript = chats[ces]["trans"] ;
			$('#chat_body').html( init_timestamps( transcript.emos().extract_youtube() ) ) ;

			$('textarea#input_text').val( chats[ces]["input"] ) ;
			if ( chats[ces]["input"] )
				toggle_input_btn_enable( false ) ;
			else
				toggle_input_btn_enable( true ) ;

			for ( var thisces in chats )
			{
				if ( thisces == ces )
					$('#menu_img_'+ces).attr( "src", "../themes/"+theme+"/online_green.png" ) ;
				else
					$('#menu_img_'+thisces).attr( "src", "../themes/"+theme+"/online_grey.png" ) ;
			}

			if ( typeof( "set_attach_vars" ) == "function" ) { set_attach_vars() ; }
			init_scrolling() ;
			ck_his.push( ces ) ;

			reset_chat_list_style() ;
			init_textarea() ;
			$('#menu_'+ces).removeClass('chat_switchboard_cell_bl_red').removeClass('chat_switchboard_cell_bl_green').removeClass('chat_switchboard_cell_bl_red').removeClass('chat_switchboard_cell').addClass('chat_switchboard_cell_focus') ;
			$('#chat_vname').html( chats[ces]["vname"] ) ;

			var peer_support = "" ;
			if ( phplive_peer_support && peer && !wp && !mapp && !chats[ces]["group_chat"] )
			{
				if ( phplive_peer_support && chats[ces]["peer"] )
				{
					// for op2op, only allow requester to request audio chat to limit issues siginal confusion
					if ( !chats[ces]["op2op_"] || ( chats[ces]["op2op_"] && ( chats[ces]["op2op"] == isop ) ) )
						peer_support = " <span style=\"cursor: pointer;\" onClick=\"vc_toggle(1, 0)\"><img src=\"../themes/initiate/mic_yes.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Voice Chat\" title=\"Voice Chat\"></a>" ;
				}
				else
					peer_support = " <span style=\"cursor: pointer;\" onClick=\"vc_toggle(1, 0)\"><img src=\"../themes/initiate/mic_no.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Voice Chat\" title=\"Voice Chat\"></a>" ;
			}

			// populate info section
			if ( !chats[ces]["op2op"] || ( chats[ces]["op2op"] && ( chats[ces]["status"] == 2 ) ) )
			{
				var mailto = "mailto:"+chats[ces]["vemail"] ;
				var req_auto_pop = ( chats[ces]["auto_pop"] && chats[ces]["vemail"] ) ? "<img src=\"../pics/icons/info_flag.png\" width=\"10\" height=\"10\" border=\"0\" alt=\"pre-populated visitor information\" title=\"pre-populated visitor information\"> " : "" ;
				var req_email = ( chats[ces]["initiated"] && !chats[ces]["auto_pop"] ) ? "<i>operator chat invite - email not available</i>" : "<a href=\""+mailto+"\" class=\"nounder\" target=\""+phplive_link_target+"\"><span class=\"chat_info_link\">"+chats[ces]["vemail"]+"</span></a>"+"&nbsp;"+req_auto_pop ;
				var marketing = ( ( typeof( markets[chats[ces]["marketid"]] ) != "undefined" ) && ( typeof( markets[chats[ces]["marketid"]]["name"] ) != "undefined" ) ) ? markets[chats[ces]["marketid"]]["name"] : "" ;

				parse_custom_vars() ;

				var mapicon = ( typeof( map_icons[chats[ces]["country"]] ) != "undefined" ) ? chats[ces]["country"] : "unknown" ;
				var geomap_string = ( chats[ces]["country"] ) ? " &nbsp; <img src=\"../pics/maps/"+mapicon+".gif\" width=\"18\" height=\"12\" border=\"0\" alt=\""+chats[ces]["country"]+"\" title=\""+chats[ces]["country"]+"\">" : "" ;
				var ip_string = ( chats[ces]["ip"].length > 16 ) ? "<small>"+chats[ces]["ip"]+"</small>" : chats[ces]["ip"] ;
				var geomap_string_display = ( mapp ) ? ip_string+geomap_string : "<a href=\"#\" onClick=\"toggle_info_maps()\">"+ip_string+geomap_string+"</a>" ;

				$('#req_dept').html( depts_hash[chats[ces]["deptid"]]+"&nbsp;" ) ; 
				if ( chats[ces]["group_chat"] )
				{
					var group_chat_string = "<img src=\"../themes/initiate/group.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"\"> Group Chat" ;
					if ( chats[ces]["opid"] == isop )
						$('#req_email').html( group_chat_string+"<div style='margin-top: 5px;' class='info_box'><b>Note:</b> You are the creator of this group chat. If you close the chat, the group chat will end.</div>" ) ;
					else
						$('#req_email').html( group_chat_string ) ;

					if ( chats[ces]["group_chat"] == 1 )
					{
						chats[ces]["group_chat"] = 2 ; // indicates joined text was sent
						http_text( "<div class='ca'><img src='"+profile_pic_url+"' width='25' height='25' style='border-radius: 50%;' alt=''> "+cname+" has joined the chat.</div>" ) ;
					}
					$('#req_request').html("") ;
				}
				else
				{
					$('#req_email').html( chats[thisces]["gravatar"]+" "+req_email ) ;
					$('#req_request').html( chats[ces]["requests"] + " time(s)"+"&nbsp;" ) ;
				}

				var onpage = ( chats[ces]["onpage"] != "livechatimagelink" ) ? "<div title=\""+chats[ces]["onpage"]+"\"><a href=\""+chats[ces]["onpage"]+"\" target=\""+phplive_link_target+"\" alt=\""+chats[ces]["onpage"]+"\" title=\""+chats[ces]["onpage"]+"\">"+chats[ces]["title"]+"</a></div>" : chats[ces]["title"] ;
				$('#req_onpage').html( onpage ) ;
				$('#req_refer').html( "<a href=\""+chats[ces]["refer_raw"]+"\" target=\""+phplive_link_target+"\" alt=\""+chats[ces]["refer_raw"]+"\" title=\""+chats[ces]["refer_raw"]+"\">"+chats[ces]["refer_snap"]+"</a>"+"&nbsp;" ) ;
				$('#req_market').html( marketing+"&nbsp;" ) ;
				if ( chats[ces]["group_chat"] )
				{
					$('#req_resolution').html("") ;
					$('#req_ip').html("") ;
				}
				else
				{
					$('#req_resolution').html( chats[ces]["resolution"]+" &nbsp; <img src=\"../themes/"+theme+"/os/"+chats[ces]["os"]+".png\" border=0 alt=\""+chats[ces]["os"]+"\" title=\""+chats[ces]["os"]+"\" alt=\""+chats[ces]["os"]+"\" width=\"16\" height=\"16\"> &nbsp; <img src=\"../themes/"+theme+"/browsers/"+chats[ces]["browser"]+".png\" border=0 alt=\""+chats[ces]["browser"]+"\" title=\""+chats[ces]["browser"]+"\" alt=\""+chats[ces]["browser"]+"\" width=\"16\" height=\"16\">"+peer_support ) ;
					$('#req_ip').html( geomap_string_display ) ;
				}
				$('#req_vid').html( chats[ces]["vis_token"] ) ;
				$('#req_ces').html( ces ) ;
				$('#tagid').val(chats[ces]["tag"]) ;
			}
			else
			{
				$('#req_dept').html( depts_hash[chats[ces]["deptid"]] ) ;
				$('#req_email').html( "Operator 2 Operator Chat" ) ;
				$('#req_request').empty() ;
				$('#req_onpage').empty() ;
				$('#req_refer').empty() ;
				$('#req_market').empty() ;
				$('#req_resolution').html( peer_support ) ;
				$('#req_ip').empty() ;
				$('#req_custom').empty().hide() ;
				$('#tr_files').hide() ; $('#req_files').empty() ;
				$('#req_vid').empty() ;
				$('#req_ces').html( ces ) ;
				$('#tagid').val(0) ;
			}

			if ( !mapp && !mobile ) { $('#input_text').focus() ; }

			toggle_info("info") ;
			
			if ( dept_ctimer[chats[ces]["deptid"]] ) { $('#chat_vtimer_wrapper').show() ; }
			else { $('#chat_vtimer_wrapper').hide() ; }
			
			init_timer() ;
		}
		disconnect_showhide() ;
		if ( typeof( vc_check_toggle ) == "function" ) { vc_check_toggle( ces ) ; }
	}

	function init_info()
	{
		$('#chat_info_menu_list').find('*').each( function(){
			var div_name = $( this ).attr('id') ;
			var class_name = $( this ).attr('class') ;
			if ( div_name && ( class_name == "chat_info_menu" ) )
			{
				$(this).hover(
					function () {
						$(this).removeClass('chat_info_menu').addClass('chat_info_menu_hover') ;
					},
					function () {
						$(this).removeClass('chat_info_menu_hover').addClass('chat_info_menu') ;
					}
				);
			}
		} );
		toggle_info("info") ;

		$('#div_menu_op_inner').find('*').each( function(){
			var div_name = $( this ).attr('id') ;
			if ( div_name && ( div_name.indexOf( "menu_op_" ) != -1 ) )
			{
				$(this).hover(
					function () {
						$(this).css({'opacity': 0.5}) ;
					},
					function () {
						$(this).css({'opacity': 1}) ;
					}
				);
			}
		} );
	}

	function parse_custom_vars()
	{
		var custom_display = 0 ;
		var custom_orig = chats[ces]["custom"] ;
		var custom_raw = custom_orig.split("-cus-") ;
		var custom_string = "<table cellspacing=0 cellpadding=0 border=0 width=\"100%\">" ;

		$('#tr_files').hide() ; $('#req_files').empty() ;

		var obj_length = custom_raw.length ;
		for ( var c = 0; c < obj_length; ++c )
		{
			if ( custom_raw[c] != 0 )
			{
				var custom_val = custom_raw[c].split("-_-") ;
				if ( custom_val[1] )
				{
					var custom_value = custom_val[1] ;

					// check for attachments and file upload
					if ( custom_val[0] == "Screenshot URL" )
					{
						$('#req_files').html( "<a href=\""+custom_val[1]+"\" target=\""+phplive_link_target+"\"><img src=\"../themes/initiate/screenshot.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"Screenshot\" title=\"Screenshot\" class=\"info_box\"></a>" ) ;
						$('#tr_files').show() ;
					}
					else if ( custom_value.match( /^((http)|(www))/ ) )
					{
						if ( custom_value.match( /^(www)/ ) ) { custom_value = "http://"+custom_value ; }
						var custom_value_snap = ( custom_value.length > 30 ) ? custom_value.substring( 0, 20 ) + "..." + custom_value.substring( custom_value.length-20, custom_value.length ) : custom_value ;
						custom_string += "<tr><td><div class=\"chat_info_td_blank\"><span style=\"font-weight: bold;\">"+custom_val[0]+": </span> <span title=\""+custom_value+"\" alt=\""+custom_value+"\"><a href=\""+custom_value+"\" target=\""+phplive_link_target+"\">"+custom_value_snap+"</a></span></div></td></tr>" ;
					}
					else if ( custom_val[0].match( /proaction id/i ) && ( typeof( addon_proactions[custom_value] ) != "undefined" ) )
					{
						if ( mapp )
							custom_string += "<tr><td><div class=\"chat_info_td_blank\"><span style=\"font-weight: bold;\">"+custom_val[0]+": </span> <span>"+custom_val[1]+"</span></div></td></tr>" ;
						else
							custom_string += "<tr><td><div class=\"chat_info_td_blank\"><span style=\"font-weight: bold;\">"+custom_val[0]+": </span> <span><a href=\"#\" onClick=\"toggle_proaction_box(0, '"+custom_val[1]+"')\" id=\"a_proaction_id\">"+custom_val[1]+"</a></span></div></td></tr>" ;
					}
					else
					{
						custom_string += "<tr><td><div class=\"chat_info_td_blank\"><span style=\"font-weight: bold;\">"+custom_val[0]+": </span> <span>"+custom_val[1]+"</span></div></td></tr>" ;
					}
				}
				custom_display = 1 ;
			}
		}
		custom_string += "</table>" ;
		if ( custom_display ) { $('#req_custom').html(custom_string).fadeIn( "fast" ) ; } else { $('#req_custom').html( "&nbsp;" ).fadeIn( "fast" ) ; }
	}

	function chat_accept()
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		var wname = encodeURIComponent( cname ) ;

		clear_flash_console() ; $('#tagid').val(0) ;
		if ( ( typeof( ces ) != "undefined" ) && ( typeof( chats[ces] ) != "undefined" ) )
		{
			if ( typeof( rtimer_his[ces] ) != "undefined" ) { clearInterval( rtimer_his[ces] ) ; rtimer_his[ces] = undeefined ; }
			$('#rtimer_'+ces).hide() ;

			$.ajax({
			type: "POST",
			url: "../ajax/chat_actions_op_accept.php",
			data: "action=accept&requestid="+chats[ces]["requestid"]+"&ces="+ces+"&now="+chats[ces]["now"]+"&unique="+unique+"&",
			success: function(data){
				try {
					eval(data) ;
				} catch(err) {
					do_alert(0, "Error processing chat accept.  Please refresh the console and try again.") ;
				}

				if ( json_data.status )
				{
					fetch_rating_flag = 1 ;
					input_focus() ;
					dn_close( ces ) ;

					total_chats() ;
					if ( !total_new_requests && ( mobile == 2 ) )
					{
						clear_sound( "new_request" ) ;
						if ( focused ) { clear_flash_console() ; }
					}

					// if transferred, keep the same created time
					if ( chats[ces]["status"] != 2 )
						chats[ces]["timer"] = unixtime() ;

					chats[ces]["disconnected"] = 0 ; // reset it here as safe measure
					if ( json_data.tooslow )
					{
						chats[ces]["status"] = 1 ;
						chats[ces]["disconnected"] = 1 ;
						chats[ces]["tooslow"] = 1 ;
						chats[ces]["trans"] = "<div class='cl'><div class=\"ctitle\">Chat request has expired or is no longer available.</div><div style='margin-top: 15px;'><button onClick='cleanup_disconnect(ces)' style='padding: 10px;' class='input_op_button'>close</button></div></div>" ;
						$('#chat_body').html( chats[ces]["trans"] ).children(':last').hide().fadeIn(500) ;
					}
					else
					{
						if ( chats[ces]["status"] == 2 )
						{
							$('#chat_body').empty() ;
							var string = chats[ces]["trans"] ;
							chats[ces]["trans"] = string.c615() ;
						}
						else { chats[ces]["trans"] = "" ; }

						// set status to picked up always
						chats[ces]["status"] = 1 ;
						var transcript = chats[ces]["trans"] ;

						$('#chat_body').html( transcript.emos() ).children(':last').hide().fadeIn(500) ;
						if ( typeof( "set_attach_vars" ) == "function" ) { set_attach_vars() ; }
						init_scrolling() ;
						init_textarea() ;
						init_chat_list(0) ;
						toggle_info("info") ;
						disconnect_showhide() ;
						init_timer() ;

						if ( auto_canid && ( chats[ces]["status"] != 2 ) && !chats[ces]["op2op"] )
						{
							var can_exists = 0 ;
							for ( var thistitle in cans )
							{
								if ( cans[thistitle] === auto_canid ) { can_exists = 1 ; }
							}
							if ( can_exists )
							{
								chats[ces]["auto_canid"] = auto_canid ;
							}
						}
					}
				}
				else { do_alert( 0, "Error accepting chat.  Please refresh the console and try again." ) ; }
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Error accepting chat.  Please refresh the console and try again." ) ;
			} });
		}
	}

	function chat_decline( theces_force )
	{
		var unique = unixtime() ;
		var theces = ( typeof( theces_force ) != "undefined" ) ? theces_force : ces ;
		var json_data = new Object ;
		var wname = encodeURIComponent( cname ) ;

		clear_flash_console() ;
		if ( typeof( chats[theces] ) == "undefined" )
			return false ;
		else if ( chats[theces]["tooslow"] )
			cleanup_disconnect( theces ) ;
		else if ( !chats[theces]["status"] || chats[theces]["disconnected"] || ( chats[theces]["status"] == 2 ) )
		{
			var requestid = chats[theces]["requestid"] ;
			var op2op = chats[theces]["op2op"] ;
			var status = chats[theces]["status"] ;
			cleanup_disconnect( theces ) ;

			$.ajax({
			type: "POST",
			url: "../ajax/chat_actions_op_decline.php",
			data: "action=decline&requestid="+requestid+"&isop_="+isop_+"&isop__="+isop__+"&ces="+theces+"&op2op="+op2op+"&status="+status+"&unique="+unique+"&",
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					toggle_info('info') ;
					setTimeout( function() { if ( typeof( cl_his[theces] ) != "undefined" ) { delete cl_his[theces] ; } }, <?php echo ( $VARS_JS_REQUESTING * 3 ) ?> * 1000 ) ;
				}
				else
					do_alert( 0, "Error declining chat.  Please refresh the console and try again." ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Error declining chat.  Please refresh the console and try again." ) ;
			} });
		}
	}

	var dept_cat_cans = new Object ; var global_can_exists = 0 ;
	var cats = ( <?php echo ( isset( $opvars["can_cats"] ) && $opvars["can_cats"] ) ? 1 : 0 ; ?> && ( typeof( JSON ) != "undefined" ) ) ? JSON.parse( decodeURIComponent( "<?php echo ( isset( $opvars["can_cats"] ) && $opvars["can_cats"] ) ? rawurlencode( $opvars["can_cats"] ) : "" ; ?>" ) ) : {} ;
	function populate_cans( thedeptid )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$('#canned_select').empty() ;
		dept_cat_cans = new Object ;

		global_can_exists = 0 ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_cans.php",
		data: "action=cans&opid="+isop+"&deptid="+thedeptid+"&"+unique+"&",
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				cans = new Object ;
				cans_string = "" ;

				var json_length = json_data.cans.length ;

				for ( var c = 0; c < json_length; ++c )
				{
					var caninfo = json_data.cans[c] ;

					var canid = caninfo["canid"] ;
					var deptid = caninfo["deptid"] ;
					var catid = caninfo["catid"] ;
					var title = caninfo["title"] ;
					var message = caninfo["message"] ;

					if ( typeof( JSON ) == "undefined" ) { catid = -1 ; }

					var tr_id = "tr_dept_"+deptid+"_"+catid+"_"+canid+"_" ;
					dept_cat_cans[tr_id] = caninfo ;
					cans[title] = canid ;

					cans_string += "<option value=\""+message+"\">"+title+"</option>" ;
				}

				$('#canned_select').append('<option value="">-- select canned response --</option>') ;
				<?php
					for ( $c = 0; $c < count( $op_depts ); ++$c )
					{
						$department = $op_depts[$c] ;
						print "\$('#canned_select').append('<optgroup label=\"&bull; ".$department["name"]." (department)\">') ; print_dept_cats_select( $department[deptID] ) ; $('#canned_select').append('</optgroup>') ; " ;
					}
					print "\$('#canned_select').append('<optgroup label=\"All Departments\">') ; print_dept_cats_select( 1111111111 ) ; $('#canned_select').append('</optgroup>') ; global_can_exists = 1 ; " ;
				?>
			}
			else
				do_alert( 0, "Could not load canned responses. Please refresh the console and try again." ) ;
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Lost connection to server.  Please refresh the console and try again. [Error: 1031]" ) ;
		} });
	}

	function print_dept_cats_select( thedeptid )
	{
		var cats_array = cats[thedeptid] ;

		print_dept_cat_cans( thedeptid, -1 ) ;
		if ( typeof( cats_array ) != "undefined" )
		{
			for ( var c = 0; c < cats_array.length; ++c )
			{
				$('#canned_select').append('<optgroup label=" &nbsp; &nbsp; &nbsp; &bull; '+cats_array[c]+' (category)">') ;
				print_dept_cat_cans( thedeptid, c ) ;
			}
		}
	}

	function print_dept_cat_cans( thedeptid, thecatid )
	{
		var tr_id = "tr_dept_"+thedeptid+"_"+thecatid+"_" ;
		var tr_id_regex = new RegExp( tr_id, "i" ) ;

		for ( var this_tr_id in dept_cat_cans )
		{
			if ( this_tr_id.match( tr_id_regex ) )
			{
				var caninfo = dept_cat_cans[this_tr_id] ;

				var canid = caninfo["canid"] ;
				var title = caninfo["title"] ;
				var message = caninfo["message"] ;

				var spaces = ( parseInt( thecatid ) == -1 ) ? " &nbsp; &nbsp; &nbsp; " : " &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " ;
				var options_string = "<option value=\""+message+"\">"+spaces+title+"</option>" ;
				$('#canned_select').append(options_string) ;
			}
		}
	}

	function get_chat_prev( theces )
	{
		var thisces ;
		var obj_length = ck_his.length-1 ;
		for ( var c = obj_length; c >= 0; --c )
		{
			if ( ck_his[c] != "undefined" )
			{
				thisces = ck_his[c] ;
				if ( typeof( chats[thisces] ) != "undefined" )
				{
					if ( typeof( theces ) != "undefined" )
					{
						if ( theces != thisces ) { return thisces ; }
					}
					else { return thisces ; }
				}
			}
		}

		// otherwise activate the first chat request
		for ( var thisces in chats )
		{
			if ( theces != thisces ) { return thisces ; }
		}
	}

	function get_chat_next( theces )
	{
		var the_index = 0, ces_index = 0, next_index = 0 ;
		var chats_array = new Array() ;
		for ( var thisces in chats )
		{
			if ( thisces == theces ) { ces_index = the_index ; }
			chats_array.push( thisces ) ;
			++the_index ;
		}
		var chats_index_length = ( chats_array.length ) ? chats_array.length - 1 : 0 ;
		next_index = ces_index + 1 ;
		if ( typeof( chats_array[next_index] ) == "undefined" )
			return chats_array[0] ;
		else
			return chats_array[next_index] ;
	}

	function is_ces_in_his( theces )
	{
		var temp_ces ;
		var obj_length = ck_his.length-1 ;
		for ( var c = obj_length; c >= 0; --c )
		{
			temp_ces = ck_his[c] ;
			if ( temp_ces == theces )
				return true ;
		}
		return false ;
	}

	function reset_chat_list_style()
	{
		for ( var thisces in chats )
			$('#menu_'+thisces).removeClass('chat_switchboard_cell_focus').addClass('chat_switchboard_cell') ;
	}

	function reset_canvas()
	{
		var chat_body_content = $('#chat_body').html() ;

		if ( typeof( ces ) == "undefined" )
		{
			if ( !prev_status )
			{
				if ( !chat_body_content.match( /notice_online_tag/i ) )
				{
					$('#chat_body').html("<div class='notice_online_tag' style=\"padding: 10px;\">You are <span class=\"info_good\">ONLINE</span>.<div style='margin-top: 20px;'>To receive visitor chat requests, keep this window open or minimized.</div></div>").children(':last').hide().fadeIn(500) ;
				}
			}
			else
			{
				if ( automatic_offline_active && !chat_body_content.match( /notice_automatic_offline/i ) )
				{
					$('#chat_body').html("<div class='info_error notice_automatic_offline'>You are OFFLINE.</div><div class='info_error' style='margin-top: 15px;' id='div_automatic_offline'><img src='../themes/"+theme+"/alert.png' width='16' height='16' border='0' alt=''> <span style='font-size: 16px; font-weight: bold;'>Offline Hours</span><div style='margin-top: 10px;'>It is past regular chat support hours.  The system will automatically logout shortly and will be available again during regular chat support hours.  <div style='margin-top: 15px;'><button type='button' class='input_button' style='padding: 10px;' onClick='ao=0;rd=0;dup=0;mi=0;toggle_status(3)'>Click to logout now.</button></div></div></div>").children(':last').hide().fadeIn(500) ;
				}
				else if ( !chat_body_content.match( /offline/i ) )
				{
					$('#chat_body').html("<div class='info_error'>You are OFFLINE.</div>").children(':last').hide().fadeIn(500) ;
				}
			}
		}

		if ( $('#req_dept').html().length )
		{
			$('#chat_vname').empty() ;
			$('#chat_vtimer').empty() ;
			$('#info_info').find('*').each( function(){
				var div_name = this.id ;
				if ( div_name.indexOf( "req_" ) == 0 )
				{
					if ( div_name == "req_tag" ) { $('#tagid').val(0) ; }
					else if ( div_name == "req_tag_saved" ) { }
					else { $(this).empty() ; }

					if ( div_name == "req_files" ) { $('#tr_files').hide() ; }
				}
			} );
		}
	}

	function pre_disconnect()
	{
		if ( typeof( chats[ces] ) != "undefined" )
		{
			toggle_info("info") ;
			if ( chats[ces]["tooslow"] )
				cleanup_disconnect( ces ) ;
			else if ( ( chats[ces]["status"] != 1 ) && !chats[ces]["initiated"] )
				chat_decline() ;
			else
			{
				chats[ces]["closed"] = 1 ;
				disconnect( 1, ces ) ;
			}
		}
	}

	function cleanup_disconnect( theces )
	{
		delete_chat_session( theces ) ;

		$('#chat_vname').empty() ; $('#chat_vtimer').empty() ;
		init_chat_list(0) ;
		init_textarea() ;
		activate_chat( get_chat_prev() ) ;
		close_extra( extra ) ;

		var total_chat_sessions = total_chats() ;
		if ( !total_new_requests )
		{
			clear_sound( "new_request" ) ;
			if ( focused ) { clear_flash_console() ; }
		}
		if ( !total_chat_sessions ) { if ( typeof( toggle_file_attach ) == "function" ) { toggle_file_attach(1) ; } }
	}

	function delete_chat_session( theces )
	{
		dn_close( theces ) ;

		if ( typeof( chats[theces] ) != "undefined" )
		{
			// if transferred chat or too slow, delete from history in case it is routed back
			if ( ( chats[theces]["status"] == 3 ) || ( chats[theces]["status"] == 2 ) || chats[theces]["tooslow"] )
			{
				if ( chats[theces]["status"] == 2 )
				{
					// slight delay to limit display again
					setTimeout( function(){ delete cl_his[theces] ; }, <?php echo ( $VARS_JS_REQUESTING * 2 ) ?> * 1000 ) ;
				}
				else { delete cl_his[theces] ; }
			}
			else if ( !chats[theces]["status"] )
			{
				// add some buffer so it does not pop up immediately if loop
				setTimeout( function() { if ( typeof( cl_his[theces] ) != "undefined" ) { delete cl_his[theces] ; } }, <?php echo ( $VARS_JS_REQUESTING * 2 ) ?> * 1000 ) ;
			}

			delete chats[theces] ;
			delete maps_his[theces] ;
			$('#iframe_maps_'+theces).remove() ;

			if ( typeof( rtimer_his[theces] ) != "undefined" ) { clearInterval( rtimer_his[theces] ) ; rtimer_his[theces] = undeefined ; }
			delete rtimer_his[theces] ;

			delete_from_his_ck( theces ) ;

			// if chat was focused, set it to undefined so the list resets
			if ( ces == theces ) { ces = undeefined ; }
		}
	}

	function delete_from_his_ck( theces )
	{
		var temp_ces ;
		var obj_length = ck_his.length-1 ;
		for ( var c = obj_length; c >= 0; --c )
		{
			temp_ces = ck_his[c] ;
			if ( temp_ces == theces )
			{
				ck_his[c] = undeefined ;
			}
		}
	}

	function toggle_info( thediv )
	{
		var obj_length = divs_info.length ;
		for ( var c = 0; c < obj_length; ++c )
		{
			if ( divs_info[c] != thediv )
			{
				$('#info_menu_'+divs_info[c]).removeClass('chat_info_menu_focus').addClass('chat_info_menu') ;
				$('#info_'+divs_info[c]).hide() ;
				if ( divs_info[c] == "transcripts" )
					$('#info_'+divs_info[c]).removeClass('info_transcripts') ;

				if ( ( divs_info[c] != "info" ) && ( divs_info[c] != "maps" ) )
					$('#info_'+divs_info[c]).html( "<img src=\"../themes/"+theme+"/loading_fb.gif\" border=\"0\" alt=\"\">" ) ;
			}
		}

		info = thediv ;

		$('#info_menu_'+thediv).removeClass('chat_info_menu').addClass('chat_info_menu_focus') ;
		$('#info_'+thediv).fadeIn( "fast" ) ;
		$('#chat_info_body').css({'overflow': 'auto'}) ;

		if ( thediv == "maps" )
		{
			for ( var thisces in maps_his )
				$('#iframe_maps_'+thisces).hide() ;
			populate_maps() ;
		}
		else if ( thediv == "transfer" )
		{
			if ( ( typeof( ces ) != "undefined" ) && ( typeof( chats[ces]["status"] ) != "undefined" ) && ( !chats[ces]["status"] || chats[ces]["op2op"] || chats[ces]["group_chat"] || chats[ces]["disconnected"] ) )
				$('#info_transfer').html( "<div class=\"info_box\">Chat transfer is not available for this session.<ul style=\"margin-top: 5px;\"><b>Possible Reasons:</b> <li style=\"margin-top: 5px;\">Chat has not been accepted.</li><li>This is an operator to operator chat.</li><li>This is a group chat.</li><li>Chat session has ended.</li></ul></div>" ) ;
			else
				populate_ops(0) ;
		}
		else if ( thediv == "footprints" )
			populate_footprints() ;
		else if ( thediv == "transcripts" )
			populate_transcripts(mapp) ;
		else if ( thediv == "spam" )
			populate_spam() ;

		disconnect_showhide() ;
	}

	var st_info_maps ;
	function toggle_info_maps()
	{
		if ( typeof( st_info_maps ) != "undefined" ) { clearTimeout( st_info_maps ) ; }
		st_info_maps = setTimeout(function(){ toggle_info('maps') ; }, 300) ;
	}

	function disconnect_showhide()
	{
		if ( ( typeof( ces ) != "undefined" ) && ( typeof( chats[ces] ) != "undefined" ) )
		{
			if ( !chats[ces]["closed"] )
			{
				$('#info_disconnect').css({"padding": "3px"}).html( "<img src=\"../themes/"+theme+"/close_extra.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\"> end chat with <b>"+chats[ces]["vname"]+"</b>" ).show() ;
				if ( mapp ) { $('#info_disconnect_mapp').html( "<img src=\"../themes/"+theme+"/close_extra.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\"> end chat with <b>"+chats[ces]["vname"]+"</b>" ).fadeIn( "fast" ) ; $('#options_mapp_vinfo').fadeIn( "fast" ) ; }
			}
		}
		else if ( parseInt( $('#info_disconnect').css('padding-left') ) )
		{
			$('#info_disconnect').css({"padding": "0px"}).empty().hide() ;
			if ( mapp ) { $('#info_disconnect_mapp').empty().hide() ; $('#options_mapp_vinfo').hide() ; }
		}
	}

	function populate_ops( themapp )
	{
		if ( ( typeof( ces ) == "undefined" ) || ( typeof( chats[ces] ) == "undefined" ) )
			display_info_error('transfer') ;
		else
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			$.ajax({
			type: "POST",
			url: "../ajax/chat_actions_op_deptops.php",
			data: "action=deptops&unique="+unique+"&",
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					if ( themapp ) { mapp_obj["ops"] = new Object ; mapp_obj["ops"] = json_data.departments ; populate_mapp_ops() ; }
					var ops_string = "" ;
					var json_length = json_data.departments.length ;
					for ( var c = 0; c < json_length; ++c )
					{
						var total_dept_ops = json_data.departments[c].operators.length ;
						var this_deptid = json_data.departments[c]["deptid"] ;
						var dept_status = "offline" ;
						var dept_status_bullet = "online_grey.png" ;

						if ( ( json_data.departments[c]["online"] ) )
						{
							dept_status = "online" ;
							dept_status_bullet = "online_green.png" ;
						}

						var btn_transfer_department = ( total_dept_ops ) ? "&nbsp;<button type=\"button\" onClick=\"transfer_chat( this, "+this_deptid+",'"+json_data.departments[c]["name"]+"', "+json_data.departments[c]["rtype"]+", 0, 0, 0);$(this).attr('disabled', 'true');\">transfer</button>" : "" ;
						ops_string += "<div class=\"chat_info_td_h\" style=\"padding: 10px;\"><span style=\"cursor: pointer;\" onClick=\"toggle_dept_select("+this_deptid+")\"><img src=\"../themes/"+theme+"/"+dept_status_bullet+"\" width=\"12\" height=\"12\" border=\"0\" alt=\""+dept_status+"\" title=\""+dept_status+"\"> <span id=\"span_transfer_expand_"+this_deptid+"\">"+arrow_symbol+"</span> <b>"+json_data.departments[c]["name"]+"</b></span> %%btn_transfer_department%%</div><div id=\"div_transfer_ops_"+this_deptid+"\" style=\"display: none; max-height: 200px; overflow: auto;\">" ;

						var transfer_op_indept = 0 ; var transfer_ops_online = 0 ;
						for ( var c2 = 0; c2 < total_dept_ops; ++c2 )
						{
							var status = "offline" ;
							var status_bullet = "online_grey.png" ;
							var chatting_with = ( nchats ) ? " - chatting with "+json_data.departments[c].operators[c2]["requests"]+" visitors" : "" ;
							var btn_transfer = "" ;
							var opacity = " opacity: 0.6; filter: alpha(opacity=60);" ;

							if ( json_data.departments[c].operators[c2]["status"] )
							{
								status = "online" ;
								opacity = "" ;

								status_bullet= "online_green.png" ;
								btn_transfer = "<button type=\"button\" onClick=\"transfer_chat( this, "+this_deptid+",'"+json_data.departments[c]["name"]+"', "+json_data.departments[c]["rtype"]+", "+json_data.departments[c].operators[c2]["opid"]+",'"+json_data.departments[c].operators[c2]["name"]+"', 0);$(this).attr('disabled', 'true');\">transfer</button>" ;
							}

							if ( json_data.departments[c].operators[c2]["opid"] == isop )
							{
								transfer_op_indept = 1 ; // check to see if operator is in department
								ops_string += "<div class=\"chat_info_td\" style=\"padding: 10px; padding-left: 25px;"+opacity+"\"><img src=\"../themes/"+theme+"/"+status_bullet+"\" width=\"12\" height=\"12\" border=\"0\" alt=\""+status+"\" title=\""+status+"\"> "+json_data.departments[c].operators[c2]["name"]+" (You) "+chatting_with+"</div>" ;
							}
							else
							{
								if ( status == "online" ) { ++transfer_ops_online ; }
								ops_string += "<div class=\"chat_info_td\" style=\"padding: 10px; padding-left: 25px;"+opacity+"\"><img src=\"../themes/"+theme+"/"+status_bullet+"\" width=\"12\" height=\"12\" border=\"0\" alt=\""+status+"\" title=\""+status+"\"> "+btn_transfer+" "+json_data.departments[c].operators[c2]["name"]+" "+chatting_with+"</div>" ;
							}
						}
						if ( !total_dept_ops ) { ops_string += "<div class=\"chat_info_td\" style=\"padding: 10px; padding-left: 25px;\">Blank results.</div>"  ; }
						ops_string += "</div>" ; // close department ops list

						if ( transfer_ops_online > 1 )
							ops_string = ops_string.replace( /%%btn_transfer_department%%/, btn_transfer_department ) ;
						else
							ops_string = ops_string.replace( /%%btn_transfer_department%%/, "" ) ;
					}
					ops_string += "<div class=\"chat_info_end\"></div>" ;
					$('#info_transfer').html( ops_string ) ;

					if ( json_data.departments.length == 1 )
						setTimeout( function() { toggle_dept_select(json_data.departments[0]["deptid"]) ; }, 500 ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Error loading requested page.  Please refresh the console and try again." ) ;
			} });
		}
	}

	function toggle_dept_select( thedeptid )
	{
		var deptid_was_visible = 0 ;

		$('#info_transfer').find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf( "div_transfer_ops_" ) != -1 )
			{
				var matches = div_name.match( /div_transfer_ops_(\d+)$/ ) ;
				var this_deptid = ( typeof( matches[1] ) != "undefined" ) ? matches[1] : 0 ;
				if ( this_deptid && $(this).is(":visible") )
				{
					deptid_was_visible = this_deptid ;
					$('#span_transfer_expand_'+this_deptid).html( arrow_symbol ) ;
					$(this).hide() ;
				}
			}
		} );

		if ( deptid_was_visible != thedeptid )
		{
			$('#span_transfer_expand_'+thedeptid).html( arrow_down_symbol ) ;
			$('#div_transfer_ops_'+thedeptid).fadeIn( "fast", function() {
				$('#div_transfer_ops_'+thedeptid).scrollTop(0) ;
				/*
				// need to fine tune it more
				var scrollto = $('#info_transfer').scrollTop() + $('#div_transfer_ops_'+thedeptid).position().top - $('#info_transfer').height()/2 + $('#div_transfer_ops_'+thedeptid).height()/2 ;
				if ( scrollto > 50 )
				{
					$('#info_transfer').animate({
						scrollTop: scrollto
					}, 400) ;
				}
				*/
			});
		}
	}

	function populate_footprints()
	{
		if ( ( typeof( ces ) == "undefined" ) || ( typeof( chats[ces] ) == "undefined" ) )
			display_info_error('footprints') ;
		else
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			if ( chats[ces]["op2op"] || chats[ces]["group_chat"] ) { $('#info_footprints').html( "<div class=\"info_box\">Footprints not available for this session.</div>" ) ; }
			else
			{
				if ( chats[ces]["footprints"] == 0 )
				{
					$.ajax({
					type: "POST",
					url: "../ajax/chat_actions_op_footprints.php",
					data: "action=footprints&vis_token="+chats[ces]["vis_token"]+"&unique="+unique+"&",
					success: function(data){
						eval( data ) ;

						if ( json_data.status )
						{
							var footprints_string = "<table cellspacing=0 cellpadding=0 border=0>" ;
							var json_length = json_data.footprints.length ;
							for ( var c = 0; c < json_length; ++c )
							{
								var url_raw = json_data.footprints[c]["onpage"] ;
								if ( url_raw == "livechatimagelink" )
									url_raw = "JavaScript:void(0)" ;
								footprints_string += "<tr><td width=\"30\" style=\"text-align: center\" class=\"chat_info_td_h\"><b>"+json_data.footprints[c]["total"]+"</b></td><td width=\"100%\" class=\"chat_info_td\"><div title=\""+json_data.footprints[c]["onpage"]+"\" alt=\""+json_data.footprints[c]["onpage"]+"\"><a href=\""+url_raw+"\" target=\""+phplive_link_target+"\">"+json_data.footprints[c]["title"]+"</a></div></tr>" ;
							}
							footprints_string += "<tr><td colspan=2 class=\"chat_info_end\"></td></tr></table>" ;

							if ( json_data.footprints.length == 0 )
								footprints_string = "<div class=\"chat_info_td\">Visitor has no footprint record.</div>" ;

							chats[ces]["footprints"] = footprints_string ;
							$('#info_footprints').html( chats[ces]["footprints"] ) ;
						}
					},
					error:function (xhr, ajaxOptions, thrownError){
						do_alert( 0, "Error loading footprints.  Please refresh the console and try again." ) ;
					} });
				}
				else
				{
					$('#info_footprints').html( chats[ces]["footprints"] ) ;
				}
			}
		}
	}

	function populate_maps()
	{
		if ( typeof( maps_his[ces] ) == "undefined" )
		{
			var iframe_map ;

			if ( ( typeof( ces ) != "undefined" ) && ( typeof( chats[ces]["status"] ) != "undefined" ) && ( chats[ces]["op2op"] || chats[ces]["group_chat"] ) )
			{
				iframe_map = "<div id=\"iframe_maps_"+ces+"\" style=\"display: none;\"><div class=\"info_box\">GeoIP not available for this session.</div></div>" ;
			}
			else
			{
				var info_maps_width = $('#info_maps').width() - 18 ;
				var unique = unixtime() ;

				iframe_map = document.createElement( "iframe" ) ;
				var mapp_href = ( ( typeof( ces ) != "undefined" ) && ( typeof( chats[ces] ) != "undefined" ) ) ? "./maps.php?ip="+chats[ces]["ip"]+"&vis_token="+chats[ces]["vis_token"]+"&viewip="+viewip+"&skip="+chats[ces]["op2op"]+"&"+unique+"&" : "maps.php?ip=null" ;
				iframe_map.setAttribute( "src", mapp_href ) ; 
				iframe_map.style.display = "none" ;
				iframe_map.border = 0 ;
				iframe_map.frameBorder = 0 ;
				iframe_map.setAttribute( "id", "iframe_maps_"+ces ) ;
			}
			maps_his[ces] = iframe_map ;

			$('#info_maps').append( iframe_map ) ;
			$("#iframe_maps_"+ces).css({"border": "5px", "width": info_maps_width, "overflow": "hidden"}) ;
			init_maps_iframes() ;
			$('#iframe_maps_'+ces).fadeIn( "fast" ) ;
		}
		else
		{
			init_maps_iframes() ;
			$('#iframe_maps_'+ces).fadeIn( "fast" ) ;
		}
	}

	function display_info_error( thediv )
	{
		$('#info_'+thediv).html( "<div class='info_error'>A chat session must be active.</div>" ) ;
	}

	function populate_transcripts( themapp )
	{
		if ( ( typeof( ces ) == "undefined" ) || ( typeof( chats[ces] ) == "undefined" ) )
			display_info_error('transcripts') ;
		else
		{
			var unique = unixtime() ;
			var json_data = new Object ;
			mapp_obj["transcripts"] = new Object ;

			if ( chats[ces]["op2op"] || chats[ces]["group_chat"] )
			{
				if ( mapp ) { populate_mapp_trans_vinfo() ; }
				else { $('#info_transcripts').html( "<div class=\"info_box\">Transcripts not available for this session.</div>" ) ; }
			}
			else
			{
				if ( chats[ces]["transcripts"] == 0 )
				{
					$.ajax({
					type: "POST",
					url: "../ajax/chat_actions_op_trans.php",
					data: "action=transcripts&vis_token="+chats[ces]["vis_token"]+"&unique="+unique+"&",
					success: function(data){
						eval( data ) ;

						if ( json_data.status )
						{
							if ( themapp ) { mapp_obj["transcripts"] = json_data.transcripts ; populate_mapp_trans_vinfo() ; }
							else
							{
								var transcripts_string = "<table cellspacing=0 cellpadding=0 border=0 width=\"100%\">" ;
								var json_length = json_data.transcripts.length ;
								for ( var c = 0; c < json_length; ++c )
								{
									var initiated = ( json_data.transcripts[c]["initiated"] ) ?  "<img src=\"../pics/icons/info_initiate.gif\" width=\"10\" height=\"10\" border=\"0\" alt=\"\" title=\"Operator Initiated Chat Invite\" alt=\"Operator Initiated Chat Invite\"> " : "" ;

									transcripts_string += "<tr><td width=\"16\" class=\"chat_info_td_h\" title=\"view transcript\" alt=\"view transcript\" onClick=\"open_transcript('"+json_data.transcripts[c]["ces"]+"')\" id=\"transcript_"+json_data.transcripts[c]["ces"]+"\" style=\"width: 16px; cursor: pointer;\"><img src=\"../themes/"+theme+"/view.png\" width=\"16\" height=\"16\"></td><td class=\"chat_info_td\">"+initiated+json_data.transcripts[c]["operator"]+"</td><td class=\"chat_info_td\">"+json_data.transcripts[c]["duration"]+"</td><td width=\"130\" class=\"chat_info_td\">"+json_data.transcripts[c]["created"]+"</td></tr>" ;
								}
								transcripts_string += "</table>" ;

								if ( json_data.transcripts.length == 0 )
									transcripts_string = "<table cellspacing=0 cellpadding=0 border=0 width=\"100%\"><tr><td class=\"chat_info_td\">Blank results.</td></tr></table>" ;

								chats[ces]["transcripts"] = transcripts_string ;
								$('#info_transcripts').html( chats[ces]["transcripts"] ) ;
							}
						}
					},
					error:function (xhr, ajaxOptions, thrownError){
						do_alert( 0, "Error loading transcripts.  Please refresh the console and try again." ) ;
					} });
				}
				else
				{
					$('#info_transcripts').html( chats[ces]["transcripts"] ) ;
				}
			}
		}
	}

	function populate_spam()
	{
		if ( ( typeof( ces ) == "undefined" ) || ( typeof( chats[ces] ) == "undefined" ) )
			display_info_error('spam') ;
		else
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			if ( chats[ces]["op2op"] || chats[ces]["group_chat"] ) { $('#info_spam').html( "<div class=\"info_box\">Block not available for this session.</div>" ) ; }
			else
			{
				$.ajax({
				type: "POST",
				url: "../ajax/chat_actions_op_spam.php",
				data: "action=spam_check&ip="+chats[ces]["ip"]+"&unique="+unique+"&",
				success: function(data){
					eval( data ) ;

					if ( json_data.status )
					{
						var spam_string = "<div class=\"chat_info_td round\" style=\"margin-bottom: 10px;\"><ul style=\"padding-left: 15px;\"><li> Block the visitor's IP address from future chat requests.</li><li> Blocked visitors will always see an offline status.</li></ul></div>" ;

						if ( json_data.exist == 0 )
							spam_string += "<div id=\"info_spam_action\"><button type=\"button\" class=\"input_op_button\" onClick=\"spam_block(1, '"+chats[ces]["ip"]+"')\">Block</button></div>" ;
						else
							spam_string += "<div id=\"info_spam_action\" class=\"chat_info_link\" onClick=\"spam_block(0, '"+chats[ces]["ip"]+"')\">Visitor has been blocked.  Click to unblock visitor.</div>" ;

						$('#info_spam').html( spam_string ) ;
					}
				},
				error:function (xhr, ajaxOptions, thrownError){
					do_alert( 0, "Error loading requested page.  Please refresh the console and try again." ) ;
				} });
			}
		}
	}

	function spam_block( theflag, theip )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$('#info_spam_action').html( "<img src=\"../themes/"+theme+"/loading_fb.gif\" border=\"0\" alt=\"\">" ) ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_spam.php",
		data: "action=spam_block&flag="+theflag+"&ip="+theip+"&unique="+unique+"&",
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				var spam_string = "" ;
				if ( !theflag )
					spam_string = "<div id=\"info_spam_action\"><button type=\"button\" class=\"input_op_button\" onClick=\"spam_block(1, '"+chats[ces]["ip"]+"')\">Block</button></div>" ;
				else
					spam_string = "<div id=\"info_spam_action\" class=\"chat_info_link\" onClick=\"spam_block(0, '"+chats[ces]["ip"]+"')\">Visitor has been blocked.  Click to unblock visitor.</div>" ;

				$('#info_spam_action').html( spam_string ) ;
			}
			else
			{
				do_alert( 0, json_data.error ) ;
				$('#info_spam_action').html("") ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Error processing block.  Please refresh the console and try again." ) ;
		} });
	}

	function transfer_chat( thebtn, thedeptid, thedeptname, thertype, theopid, theopname, themapp )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( ( thertype == 3 ) && ( theopid == 0 ) )
		{
			do_alert( 0, "Please choose an operator.  Transfer not is available for simultaneous routing." ) ;
			if ( !$('#div_transfer_ops_'+thedeptid).is(':visible') )
				toggle_dept_select( thedeptid ) ;
			return false ;
		}
		else if ( addon_voice_chat && ( typeof( peer_object ) != "undefined" ) && ( typeof( peer_object["status"] ) != "undefined" ) && ( peer_object["ces"] == ces ) )
		{
			do_alert( 0, "Voice Chat is open for this chat session.  Transfer is not available." ) ;
			$('#div_voice_chat').fadeOut('fast').fadeIn('fast') ;
			
			// button is disabled AFTER the transfer call.  need a timeout to correctly re-enable
			setTimeout( function(){ $(thebtn).attr( "disabled", false ) ; }, 1000 ) ;
			return false ;
		}

		// only transfer if chat has not been transferred already
		if ( chats[ces]["status"] != 3 )
		{
			$.ajax({
			type: "POST",
			url: "../ajax/chat_actions_op_transfer.php",
			data: "action=transfer&requestid="+chats[ces]["requestid"]+"&ces="+ces+"&deptid="+thedeptid+"&opid="+theopid+"&opname="+theopname+"&proto="+proto+"&unique="+unique+"&",
			success: function(data){
				eval( data ) ;

				if ( json_data.status == 1 )
				{
					isop_ = 0 ; isop__ = 0 ;
					chats[ces]["initiated"] = 0 ; // reset to register it as a normal chat

					// delete chat AND remove it from history list so it can repopulate if transferred back to same op
					chats[ces]["status"] = 3 ; // set it to 3, for AFTER transfer (used only here)
					chats[ces]["disconnected"] = unixtime() ;
					chats[ces]["fmindex"] = 0 ;
					chats[ces]["fmsize"] = 0 ;
					chats[ces]["fmlid"] = "" ;

					var trans_to = ( theopname ) ? theopname : thedeptname ;
					var trans_string = "<c615><div class='cl'>Chat has been transferred to <b>"+trans_to+"</b>. Chat has ended.<div style='margin-top: 10px;'><button onClick='pre_disconnect()' style='padding: 10px;' class='input_op_button'>close chat</button></div></div></c615>" ;
					chats[ces]["trans"] += trans_string  ;
					$('#chat_body').append( trans_string ).children(':last').hide().fadeIn(500) ; if ( isop && mapp ) { init_external_url() ; }
					init_scrolling() ;
					init_textarea() ;

					if ( themapp )
						setTimeout( function(){ close_extra( extra ) ; }, 1000 ) ;
				}
				else if ( typeof( json_data.error ) != "undefined" ) { do_alert( 0, json_data.error ) ; }
				else { do_alert( 0, "Transfer error.  Please refresh the console and try again. [e2]" ) ; }
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Transfer error.  Please refresh the console and try again." ) ;
			} });
		}
		else
		{
			do_alert( 0, "Chat has already been transferred." ) ;
		}
	}

	function fetch_markets()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_campaigns.php",
		data: "action=campaigns&unique="+unique+"&",
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				total_markets = json_data.markets.length ;
				for ( var c = 0; c < total_markets; ++c )
				{
					var marketid = parseInt( json_data.markets[c].marketid ) ;
					var name = json_data.markets[c].name ;
					var color = json_data.markets[c].color ;

					markets[marketid] = new Object ;
					markets[marketid]["name"] = name ;
					markets[marketid]["color"] = color ;
				}
				// add the dummy ZERO
				markets[0] = new Object ;

				if ( parseInt( total_markets ) ) { $('#tr_market').show() ; }
			}
			else { do_alert( 0, "Error fetching campaigns.  Please refresh the console and try again." ) ; }
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Error fetching campaigns.  Please refresh the console and try again." ) ;
		} });
	}

	function populate_ops_op2op()
	{
		$('#iframe_op2op').attr('src', "op_op2op.php?"+unixtime()+"&" ).ready(function() {
			init_iframe( 'iframe_op2op' ) ;
		});
		$('#chat_extra_body_op2op').show() ;
	}

	function populate_ops_message_board()
	{
		$('#iframe_message_board').attr('src', "../addons/message_board/inc_iframe.php?"+unixtime()+"&" ).ready(function() {
			init_iframe( 'iframe_message_board' ) ;
		});
		$('#chat_extra_body_message_board').show() ;
	}

	function populate_traffic()
	{
		$('#iframe_traffic').attr('src', "op_traffic.php?mapp="+mapp+"&"+unixtime()+"&" ).ready(function() {
			init_iframe( 'iframe_traffic' ) ;
		});
		$('#chat_extra_body_traffic').show() ;
	}

	function populate_canned( theflag )
	{
		$('#iframe_canned').attr('src', "op_canned.php?flag="+theflag+"&"+unixtime()+"&" ).ready(function() {
			init_iframe( 'iframe_canned' ) ;
		});
		$('#chat_extra_body_canned').show() ;
	}

	function populate_trans()
	{
		$('#iframe_trans').attr('src', "op_trans.php?"+unixtime()+"&" ).ready(function() {
			init_iframe( 'iframe_trans' ) ;
		});
		$('#chat_extra_body_trans').show() ;
	}

	function populate_settings( themenu )
	{
		var theurl = "about:blank" ;
		var query_append = "&auto=<?php echo $auto ?>&wp=1&console=1&"+unixtime() ;

		if ( themenu == "themes" )
			theurl = "./index.php?menu="+themenu+query_append ;
		else if ( themenu == "sound" )
			theurl = "./notifications.php?"+query_append ;
		else if ( themenu == "dn" )
			theurl = "./notifications.php?jump=dn"+query_append ;
		else if ( themenu == "trans" )
			theurl = "./transcripts.php?"+query_append ;
		else if ( themenu == "reports" )
			theurl = "./reports.php?"+query_append ;
		else if ( themenu == "chats" )
			theurl = "./reports_chat_active.php?"+query_append ;
		else if ( themenu == "activity" )
			theurl = "./activity.php?"+query_append ;
		else if ( themenu == "settings" )
			theurl = "./settings.php?menu=themes"+query_append ;
		else if ( themenu == "profile" )
			theurl = "./index.php?"+query_append ;

		$('#iframe_settings').attr('src', theurl ).ready(function() {
			init_iframe( 'iframe_settings' ) ;
		});

		if ( typeof( iframe_his["settings"] ) == "undefined" )
		{
			$('#chat_extra_body_settings').show() ;
			iframe_his["settings"] = 1 ;

			//if ( $('#div_menu_op').is(':visible') ) { toggle_menu_op() ; }
		}
		else { $('#chat_extra_body_settings').show() ; }
	}

	function populate_ext( thediv, theurl, theskip )
	{
		var temp = "chat_extra_body_ext_"+thediv ;

		if ( theskip ) { ex_his[temp] = undeefined ; }
		if ( typeof( ex_his[temp] ) == "undefined" )
		{
			$('#'+temp).html( "<iframe id=\"iframe_ext_"+thediv+"\" name=\"iframe_ext_"+thediv+"\" style=\"width: 100%; border: 0px;\" src=\"../blank_.php?div="+thediv+"&skip="+theskip+"&theme=<?php echo $opinfo["theme"] ?>&url="+encodeURIComponent(theurl)+"&proto="+proto+"\" scrolling=\"auto\" border=0 frameborder=0 onLoad=\"init_iframe_errors( '"+thediv+"','XFrame', '"+theurl+"'); init_iframe( 'iframe_ext_"+thediv+"' );\"></iframe>" ).show() ;
			ex_his[temp] = 1 ;
		}
		else
			$('#'+temp).show() ;
	}

	function init_reload_traffic()
	{
		document.getElementById('iframe_traffic').contentWindow.close_footprint_info(1) ;
		refresh_traffic("iframe_traffic") ;
	}

	function init_iframe_errors( thediv, theerror, theurl )
	{
		if ( ( typeof( document.getElementById('iframe_ext_'+thediv).contentWindow.xframe ) != "undefined" ) && document.getElementById('iframe_ext_'+thediv).contentWindow.xframe )
		{
			document.getElementById('iframe_ext_'+thediv).contentWindow.display_error( theerror, theurl ) ;
		}
		else if ( ( typeof( document.getElementById('iframe_ext_'+thediv).contentWindow.xframe ) != "undefined" ) && !document.getElementById('iframe_ext_'+thediv).contentWindow.xframe )
		{
			setTimeout( function(){ $('#iframe_ext_'+thediv).attr('src', theurl ) ; }, 800 ) ;
		}
	}

	function toggle_extra( thediv, theflag, theurl, thename, themenu )
	{
		if ( !total_op_depts && ( thediv != "mapp_power" ) )
		{
			$('#no_dept').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
			return false ;
		}

		// the flag is used for various triggers for the div
		reset_footer() ;
		toggle_last_response(1) ;
		total_chats() ;

		// mapp pre actions
		if ( mapp ) { $('#chat_switchboard').css({'top': -1000}) ; }

		if ( ( thediv != "settings" ) && ( themenu != "sounds" ) ) { dn_status = undeefined ; }
		if ( $('#div_notification').is(':visible') ) { $('#div_notification').hide() ; }

		if ( extra == thediv )
		{
			close_extra( thediv ) ;
			if ( !mapp && !mobile ) { $( "textarea#input_text" ).focus() ; }
		}
		else
		{
			extra = thediv ;

			if ( typeof( thediv ) == "number" )
				$('#chat_footer_cell_ext_'+thediv).removeClass('chat_footer_cell').addClass('chat_footer_cell_focus') ;
			else
				$('#chat_footer_cell_'+thediv).removeClass('chat_footer_cell').addClass('chat_footer_cell_focus') ;

			$('#chat_extra_title').html( "<span onClick=\"close_extra( '"+extra+"' )\" class=\"info_error\" style=\"font-weight: normal; cursor: pointer;\">close</span> "+thename ) ;
			if ( thediv == "op2op" )
			{
				var group_chat_string = " &nbsp; <button type=\"button\" id=\"btn_group_chat\" style=\"display: none; font-size: 12px;\" onClick=\"toggle_group_chat()\"><img src=\"../themes/initiate/group.png\" width=\"10\" height=\"10\" alt=\"\" border=0> create group chat</button>" ;
				$('#chat_extra_title').append( group_chat_string ) ;
				hide_extra( "chat_extra_body_"+thediv ) ;
				setTimeout( function(){ populate_ops_op2op() ; }, 300 ) ; // add a little delay to fix bug
			}
			else if ( thediv == "message_board" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				setTimeout( function(){ populate_ops_message_board() ; }, 300 ) ; // add a little delay to fix bug
			}
			else if ( thediv == "traffic" )
			{
				// todo: REFINE onClick= event
				var traffic_sound_onoff = ( mp3_support ) ? "<span id=\"div_traffic_sound\" class=\"sound_box_on\" style=\"margin-left: 20px; font-weight: normal; font-size: 10px; cursor: pointer;\" onClick=\"toggle_traffic_sound()\"></span>" : "" ;
				$('#chat_extra_title').append( traffic_sound_onoff+" &nbsp; <span style=\"font-size: 10px; font-weight: normal;\">Traffic monitor will refresh every <?php echo $VARS_REFRESH_TRAFFIC_MONITOR ?> seconds or when there is change in traffic. <a href=\"JavaScript:void(0)\" onClick='init_reload_traffic(\"iframe_traffic\")'>refresh now</button></a></span>" ) ;
				print_traffic_sound_text() ;
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_traffic() ;
			}
			else if ( thediv == "canned" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_canned( theflag ) ;
			}
			else if ( thediv == "trans" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_settings( themenu ) ;
			}
			else if ( thediv == "settings" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_settings( themenu ) ;
			}
			else if ( thediv == "themes" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_settings( themenu ) ;
			}
			else if ( thediv == "mapp_chats" )
			{
				if ( !total_new_requests && $('#div_mapp_chat_bubble_red').is(':visible') ) { $('#div_mapp_chat_bubble_red').hide() ; }
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_chats() ;
				toggle_mapp_icon( thediv, 1 ) ;
			}
			else if ( thediv == "mapp_traffic" )
			{
				$('#chat_extra_title').append( "&nbsp; <span style=\"float: right; margin-right: 10px; font-size: 10px; font-weight: normal;\"><button type='button' style='' onClick='init_reload_mapp_traffic()'>refresh</button></span><span style=\"clear: both;\"></span>" ) ;
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_traffic() ;
				toggle_mapp_icon( thediv, 1 ) ;
			}
			else if ( thediv == "mapp_themes" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_themes() ;
			}
			else if ( thediv == "mapp_prefs" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_prefs() ;
			}
			else if ( thediv == "mapp_sounds" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_sounds() ;
			}
			else if ( thediv == "mapp_operators" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_operators() ;
			}
			else if ( thediv == "mapp_message_board" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_message_board() ;
			}
			else if ( thediv == "mapp_trans" )
			{
				$('#chat_extra_title').append( "&nbsp; <span style=\"float: right; margin-right: 10px; font-size: 10px; font-weight: normal;\"><button type='button' style='' onClick='init_reload_mapp_trans()'>refresh</button></span><span style=\"clear: both;\"></span>" ) ;
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_trans() ;
			}
			else if ( thediv == "mapp_power" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_power() ;
				toggle_mapp_icon( thediv, 1 ) ;
			}
			else if ( thediv == "mapp_vinfo" )
			{
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_vinfo( ces ) ;
			}
			else if ( thediv == "mapp_cans" )
			{
				$('#chat_extra_title').append( "&nbsp; <span style=\"float: right; margin-right: 10px; font-size: 10px; font-weight: normal;\"><button type='button' style='' onClick='init_reload_mapp_cans()'>refresh</button></span><span style=\"clear: both;\"></span>" ) ;
				hide_extra( "chat_extra_body_"+thediv ) ;
				populate_mapp_cans() ;
				toggle_mapp_icon( thediv, 1 ) ;
			}
			else
			{
				hide_extra( "chat_extra_body_ext_"+thediv ) ;
				populate_ext( thediv, theurl, 0 ) ;
			}

			init_extra() ;
		}
	}

	function toggle_group_chat()
	{
		if ( <?php echo $VARS_FREEV ?> )
			do_alert( 0, "Group chat not available for the free version." ) ;
		else
			document.getElementById('iframe_op2op').contentWindow.toggle_group_chat() ;
	}

	function reset_footer()
	{
		var divs_footer = Array( "op2op", "message_board", "traffic", "canned", "trans", "extras" ) ;
		var obj_length = divs_footer.length ;
		for ( var c = 0; c < obj_length; ++c )
			$('#chat_footer_cell_'+divs_footer[c]).removeClass('chat_footer_cell_focus').addClass('chat_footer_cell') ;

		$('div').filter(function() {
			return this.id.match(/chat_footer_cell_ext_\d/) ;
		}).removeClass('chat_footer_cell_focus').addClass('chat_footer_cell') ;

		$('#div_geomap').hide() ;
	}

	function close_extra( thediv )
	{
		if ( typeof( extra ) == "undefined" )
		{
			// nothing for now
		}
		else
		{
			if ( ( thediv == "settings" ) && ( typeof( document.getElementById('iframe_'+thediv).contentWindow ) != "undefined" ) )
			{
				if ( typeof( document.getElementById('iframe_'+thediv).contentWindow.mp3_support ) != "undefined" )
					document.getElementById('iframe_'+thediv).contentWindow.clear_sound('new_request') ;
			}
			else if ( thediv == "mapp_chats" ) { $('#chat_switchboard').css({'top': -1000}) ; }
			else if ( ( thediv == "mapp_sounds" ) && ( typeof( document.getElementById('iframe_mapp_sounds').contentWindow.demo_sound1 ) != "undefined" ) ) { document.getElementById('iframe_mapp_sounds').contentWindow.demo_sound1(0) ; }

			if ( mapp ) { $('#chat_extra_wrapper').hide() ; toggle_mapp_icon(thediv, 0) ; close_extra_doit( thediv ) ; }
			else { $('#chat_extra_wrapper').fadeOut('fast', function() { close_extra_doit( thediv ) ; }) ; }

			if ( thediv == "settings" )
			{
				$('#chat_footer').fadeIn( "fast" ) ;
			}
		}
	}

	function close_extra_doit( thediv )
	{
		if ( typeof( thediv ) != "number" )
		{
			if ( ( thediv == "op2op" ) || ( thediv == "message_board" ) || ( thediv == "canned" ) || ( thediv == "trans" ) || ( thediv == "traffic" ) || ( thediv == "settings" ) || ( thediv == "mapp_themes" ) || ( thediv == "mapp_prefs" ) || ( thediv == "mapp_sounds" ) || ( thediv == "mapp_cans" ) || ( thediv == "mapp_trans" ) || ( thediv == "mapp_operators" ) || ( thediv == "mapp_message_board" ) || ( thediv == "mapp_power" ) || ( thediv == "mapp_traffic" ) ) { iframe_blank( thediv ) ; }
		}
		extra = undeefined ;
		reset_footer() ;
	}

	function hide_extra( thediv )
	{
		var divs_extra = Array( "chat_extra_body_op2op", "chat_extra_body_message_board", "chat_extra_body_traffic", "chat_extra_body_canned", "chat_extra_body_trans", "chat_extra_body_at", "chat_extra_body_extras", "chat_extra_body_settings", "chat_extra_body_mapp_chats", "chat_extra_body_mapp_traffic", "chat_extra_body_mapp_themes", "chat_extra_body_mapp_prefs", "chat_extra_body_mapp_sounds", "chat_extra_body_mapp_operators", "chat_extra_body_mapp_message_board", "chat_extra_body_mapp_trans", "chat_extra_body_mapp_power", "chat_extra_body_mapp_vinfo", "chat_extra_body_mapp_cans" ) ;

		var obj_length = divs_extra.length ;
		for ( var c = 0; c < obj_length; ++c )
		{
			if ( divs_extra[c] != thediv )
			{
				var regp = /chat_extra_body_(.*)/gi ;
				var regm = regp.exec( divs_extra[c] ) ;
				var thisdiv = regm[1] ;

				if ( ( thisdiv == "op2op" ) || ( thisdiv == "message_board" ) || ( thisdiv == "canned" ) || ( thisdiv == "trans" ) || ( thisdiv == "traffic" ) || ( thisdiv == "settings" ) || ( thisdiv == "mapp_themes" ) || ( thisdiv == "mapp_prefs" ) || ( thisdiv == "mapp_sounds" ) || ( thisdiv == "mapp_cans" ) || ( thisdiv == "mapp_trans" ) || ( thisdiv == "mapp_operators" ) || ( thisdiv == "mapp_message_board" ) || ( thisdiv == "mapp_power" ) || ( thisdiv == "mapp_traffic" ) ) { iframe_blank( thisdiv ) ; }
				if ( mapp && ( thisdiv == "mapp_sounds" ) && ( typeof( document.getElementById('iframe_mapp_sounds').contentWindow.demo_sound1 ) != "undefined" ) ) { document.getElementById('iframe_mapp_sounds').contentWindow.demo_sound1(0) ; }
				if ( mapp && thisdiv.match(/mapp_/) ) { toggle_mapp_icon(thisdiv, 0) ; }

				$('#'+divs_extra[c]).hide() ; 
			}
		}

		$('div').filter(function() {
			return this.id.match(/chat_extra_body_ext_\d/) ;
		}).hide() ;
	}

	function iframe_blank( thediv )
	{
		if ( ( $('#iframe_'+thediv).length != 0 ) && ( typeof( document.getElementById('iframe_'+thediv).contentWindow ) != "undefined" ) )
		{
			// mobile = 1/3 (iOS) about:blank does not process - bug fix
			if ( mapp && ( ( mobile == 1 ) || ( mobile == 3 ) ) )
			{
				var href = document.getElementById('iframe_'+thediv).contentWindow.location.href ;
				if ( !href.match( /blank/ ) ) { $('#iframe_'+thediv).attr( 'src', "../blank.php" ) ; } // about:blank doesn't work on iOS mapp
			}
			else { $('#iframe_'+thediv).attr( 'src', "about:blank" ) ; }
		}
	}

	function select_canned_pre( theauto_canid )
	{
		input_focus() ;
		close_extra( extra ) ;

		var thetitle ;
		if ( typeof( theauto_canid ) == "number" )
		{
			for ( var thistitle in cans )
			{
				if ( cans[thistitle] == theauto_canid ) { thetitle = thistitle ; break ; }
			}
		}
		else { thetitle = theauto_canid ; }

		if ( typeof( thetitle ) != "undefined" )
		{
			$('#canned_select option').filter(function () {
				var temp_title = $(this).html().replace( / &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; /, "" ) ;
				temp_title = temp_title.replace( / &nbsp; &nbsp; &nbsp; /, "" ) ;
				temp_title = temp_title.replace( /&nbsp; &nbsp; &nbsp; /, "" ) ; // IE double check
				temp_title = $('<textarea />').html(temp_title).text() ;
				if ( temp_title == $('<textarea />').html(thetitle.replace( /&-#39;/g, "'" )).text() )
				{
					this.selected = true ;
				}
			}) ;
			select_canned() ;
		}
	}

	function select_canned()
	{
		var response_text = $('#canned_select').val() ;

		if ( typeof( extra ) != "undefined" ) { close_extra( extra ) ; }
		if ( response_text && total_chats() && ( typeof( ces ) != "undefined" ) && chats[ces]["status"] && !chats[ces]["disconnected"] )
		{
			$( "textarea#input_text" ).val( response_text.replace( /<br>/g, "\r" ) ) ;
			if ( !mapp && !mobile ) { $( "textarea#input_text" ).focus() ; }
			toggle_input_btn_enable( false ) ;

			// check for vis idle canned
			if ( typeof( vis_idle_canned['canid_1'] ) != "undefined" )
			{
				var thetitle ;
				var response_title = $('#canned_select').find(":selected").text() ;
				for ( var thistitle in cans )
				{
					if ( cans[thistitle] === parseInt( vis_idle_canned['canid_1'] ) ) { thetitle = thistitle ; break ; }
				}
				if ( thetitle && ( thetitle == response_title ) )
				{
					// visitor idle canned response feature
				}
			}
		}
		else if ( !response_text )
		{
			do_alert( 0, "A canned response must be selected." ) ;
			$('#canned_select').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		}
		else { do_alert( 0, "A chat session must be active." ) ; }
	}

	function check_network( thetotal, theunixtime, theserver )
	{
		if ( typeof( theunixtime ) != "undefined" )
		{
			var network_duration = thetotal - theserver ;
			if ( network_duration < 0 ) { network_duration = 0.001 ; }
			update_network( thetotal.toFixed(3), network_duration.toFixed(3), theserver.toFixed(3) ) ;
		}
		else
		{
			if ( typeof( theserver ) == "undefined" ) { theserver = "-" ; }
			update_network( "[error] "+thetotal, "-", theserver ) ;
		}
	}

	var total_network_display = 50 ;
	var global_network_max = 0 ; var global_prev_network = 0 ; var global_prev_server = 0 ;
	var diff_network_string = "" ; var diff_server_string = "" ; var network_string = "" ; var server_string = "" ;
	function update_network( thetotal, thenetwork, theserver )
	{
		var meter_length = 0 ;

		if ( !thetotal.match( /error/i ) ) { thetotal = parseFloat( thetotal ).toFixed(3) ; }
		if ( thetotal && !global_network_max )
		{
			// init the max
			global_network_max = thetotal*4 ;
		}

		if ( global_network_max && !mapp )
		{
			meter_length = Math.round( ( thetotal/global_network_max ) * 100 ) ;
			if ( meter_length > 100 ) { meter_length = 100 ; }
		}

		var temp_network = parseFloat( thenetwork ).toFixed(3) ;
		var temp_server = parseFloat( theserver ).toFixed(3) ;
		
		var diff_network = parseFloat( temp_network - global_prev_network ).toFixed(3) ;
		var diff_server = parseFloat( temp_server - global_prev_server ).toFixed(3) ;

		var diff_network_display = ( diff_network < 0 ) ? diff_network * -1 : diff_network ;
		var diff_server_display = ( diff_server < 0 ) ? diff_server * -1 : diff_server ;

		//diff_network_string = ( ( diff_network > 0 ) ) ? " <span class='info_error' style='padding: 2px;'>-"+parseFloat( diff_network_display ).toFixed(3)+'</span>' : "<span class='info_good' style='padding: 2px;'>+"+parseFloat( diff_network_display ).toFixed(3)+"</span>" ;

		//diff_server_string = ( ( diff_server > 0 ) ) ? " <span class='info_error' style='padding: 2px;'>-"+parseFloat( diff_server_display ).toFixed(3)+'</span>' : "<span class='info_good' style='padding: 2px;'>+"+parseFloat( diff_server_display ).toFixed(3)+"</span>" ;

		if ( diff_network == 0 ) { diff_network_string = "" ; }
		if ( diff_server == 0 ) { diff_server_string = "" ; }

		network_string = thenetwork ;
		server_string = theserver ;

		update_network_log( "<tr id='div_network_his_"+network_counter+"' style='display: none'><td class='chat_info_td'>"+network_string+"</td><td class='chat_info_td' title=''>"+server_string+"</td><td class='chat_info_td'>"+thetotal+" <div style=\"width: "+meter_length+"px; background: #3399FF; height: 2px;\">&nbsp;</div></td></tr>" ) ;

		var total_pings = ping_counter_rating + ping_counter_req ;
		var ping_hours = Math.floor( global_counter/3600 ) ;
		var ping_minutes = Math.floor( ( global_counter - ( ping_hours * 3600 ) )/60 );
		$('#ping_counter_req').html(ping_counter_req+":"+total_pings+" ["+ping_hours+":"+pad( ping_minutes, 2 )+"]" ) ;
		$('#ping_total_bytes_received').html(ping_total_bytes_received) ;

		global_prev_network = temp_network ;
		global_prev_server = temp_server ;

		if ( thetotal <= 0.3 )
		{
			if ( mapp ) { update_mapp_network( 5 ) ; }
			else { $('#chat_network_img').css({'background-position': '0px bottom'}) ; }
		}
		else if ( thetotal <= 0.7 )
		{
			if ( mapp ) { update_mapp_network( 4 ) ; }
			else { $('#chat_network_img').css({'background-position': '0px -152px'}) ; }
		}
		else if ( thetotal <= 1.4 )
		{
			if ( mapp ) { update_mapp_network( 3 ) ; }
			else { $('#chat_network_img').css({'background-position': '0px -114px'}) ; }
		}
		else if ( thetotal <= 2 )
		{
			if ( mapp ) { update_mapp_network( 2 ) ; }
			else { $('#chat_network_img').css({'background-position': '0px -76px'}) ; }
		}
		else if ( thetotal <= 50 )
		{
			if ( mapp ) { update_mapp_network( 1 ) ; }
			else { $('#chat_network_img').css({'background-position': '0px -38px'}) ; }
		}
		else
		{
			if ( mapp ) { update_mapp_network( 0 ) ; }
			else { $('#chat_network_img').css({'background-position': '0px 0px'}) ; }

			// disconnected by calling p_engine.php stop() function (attempt to reconnect)
			if ( total_op_depts ) { reconnect_doit() ; }
		}
	}

	function update_network_log( thestring )
	{
		var now_string = thestring.replace(/his_\d+'/g, "") ;

		if ( prev_network_string != now_string )
		{
			if ( mapp ) { update_mapp_network_log( network_counter, thestring ) ; }
			else
			{
				$('#chat_info_network_info tbody tr:nth-child(2)').after( thestring ) ;
				$('#div_network_his_'+network_counter).fadeIn("fast") ;
			}

			if ( thestring.match( /(\[error\])|(disco)/gi ) )
			{
				var localtime = new Date() ;
				var tempstring = thestring.replace( /id='(.*?)' style=/g, "id='log_$1' style=" ) ;
				tempstring = tempstring.replace( /<td class='chat_info_td'>&nbsp;<\/td>/, "" ) ;
				tempstring = tempstring.replace( /none'><td class='chat_info_td'>(.*?)</g, "none'><td class='chat_info_td'>"+localtime.toLocaleString()+"<" ) ;

				if ( mapp ) { }
				else
				{
					tempstring = tempstring.replace( /<td class='chat_info_td' title=''>-<\/td>/, "" ) ;
					tempstring = tempstring.replace( /<span class=(.*?)<\/span>/g, "" ) ;
					$('#chat_info_network_log tbody tr:nth-child(2)').after( tempstring ) ;
					$('#log_div_network_his_'+network_counter).fadeIn( "fast" ) ;
				}
			}
			++network_counter ;
			prev_network_string = now_string ;

			if ( network_counter > total_network_display )
			{
				var div_delete = network_counter - total_network_display - 1 ;
				$('#div_network_his_'+div_delete).fadeOut( "slow", function() {
					$('#div_network_his_'+div_delete).remove() ;
				});
			}
		}
	}

	function toggle_status( thestatus )
	{
		var unique = unixtime() ;
		var this_logout_timer = logout_timer ;

		if ( !$('#div_dept_offline_all_notice').is(":visible") || ( thestatus >= 2 ) )
		{
			// make it active if status is online to hide logout div
			if ( prev_status != thestatus )
			{
				if ( typeof( st_logout ) != "undefined" )
				{
					clearTimeout( st_logout ) ;
					st_logout = undeefined ;
				}

				$('#chat_status_logout').hide() ;

				if ( ( automatic_offline_active || global_maxc_flag ) && ( thestatus == 0 ) )
				{
					if ( !$('#status_1').is(':checked') ) { $('#status_1').prop( "checked", true ) ; }
					if ( !$('#chat_status_offline').is(':visible') )
					{
						if ( typeof( si_offline ) != "undefined" ) { clearInterval( si_offline ); si_offline = undeefined ; $('#offline_timer').html( this_logout_timer+":00" ) ; }
						start_offline_timer( this_logout_timer*60 ) ;
						$('#chat_status_offline').fadeIn( "fast" ) ;
					}
					else if ( global_maxc_flag )
						toggle_offline_timer_showhide(1) ;

					if ( $('#div_automatic_offline').is(':visible') )
						$('#div_automatic_offline').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
					else
						$('#chat_status_offline').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
				}
				else
				{
					if ( typeof( si_offline ) != "undefined" ) { clearInterval( si_offline ); si_offline = undeefined ; $('#offline_timer').html( this_logout_timer+":00" ) ; }
					if ( thestatus == 0 )
					{
						$('#chat_status_offline_radio').css({'background': ''}) ; $('#chat_status_offline').hide() ;
						if ( !$('#status_0').is(':checked') ) { $('#status_0').prop( "checked", true ) ; }

						if ( typeof( prev_status ) != "undefined" ) { prev_status = thestatus ; update_status(1) ; } // do not log cancel logout revert
						else { prev_status = thestatus ; }
					}
					else if ( thestatus == 1 )
					{
						toggle_offline_timer_showhide( global_maxc_flag ) ;
						if ( !$('#status_1').is(':checked') ) { $('#status_1').prop( "checked", true ) ; }
						if ( !$('#chat_status_offline').is(':visible') ) { $('#chat_status_offline').fadeIn( "fast" ) ; }

						var color = $('#chat_status_offline').css("background-color") ;
						$('#chat_status_offline_radio').css({'background-color': color}) ;

						if ( $('#div_dept_offline_some_notice').is(":visible") )
							$('#div_dept_offline_some_notice').hide() ;

						if ( $('#div_automatic_offline').is(":visible") )
							$('#div_automatic_offline').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;

						start_offline_timer( this_logout_timer*60 ) ;
						if ( typeof( prev_status ) != "undefined" ) { prev_status = thestatus ; update_status(0) ; }
						else { prev_status = thestatus ; }
					}
					else if ( thestatus == 2 )
					{
						$('#chat_status_logout').fadeIn( "fast" ) ;
						$('#chat_status_offline_radio').css({'background': ''}) ; $('#chat_status_offline').hide() ;
						if ( !$('#status_2').is(':checked') ) { $('#status_2').prop( "checked", true ) ; }

						// another layer of check for auto logout
						st_logout = setTimeout( function(){ toggle_status( 3 ) ; }, 300000 ) ;
					}
					else if ( ( thestatus == 3 ) || ( thestatus == 4 ) || ( thestatus == 5 ) )
					{
						window.onbeforeunload = null ;
						var wpl = ( thestatus == 5 ) ? 1 : 0 ; // winapp logout indication
						location.href = base_url+"/logout.php?action=logout&ao="+ao+"&rd="+rd+"&dup="+dup+"&mi="+mi+"&auto=1&wp="+wp+"&wpl="+wpl+"&wid="+wid+"&pop=<?php echo $pop ?>&mapp="+mapp+"&"+unique+"&" ;
					}
					else
					{
						$('#status_'+prev_status).prop( "checked", true ) ;
						$('#chat_status_logout').hide() ;

						// 1000 status indicates closing of the logout cancel button
						if ( ( thestatus == 1000 ) && ( prev_status == 1 ) )
						{
							if ( !$('#div_dept_offline_all_notice').is(":visible") )
								prev_status = undeefined ;

							toggle_status( 1 ) ;
						}
						else { var temp = prev_status ; prev_status = undeefined ; toggle_status( temp ) ; }
					}
				}
			}
		}
		else
		{
			if ( !$('#chat_status_offline').is(":visible") )
			{
				toggle_offline_timer_showhide( global_maxc_flag ) ;
				if ( typeof( si_offline ) != "undefined" ) { clearInterval( si_offline ); si_offline = undeefined ; $('#offline_timer').html( this_logout_timer+":00" ) ; }
				start_offline_timer( this_logout_timer*60 ) ;
				$('#chat_status_offline').fadeIn( "fast" ) ;
			}

			$('#status_1').prop( "checked", true ) ;
			$('#div_dept_offline_all_notice').fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		}
	}

	function toggle_offline_timer_showhide( theflag )
	{
		if ( theflag )
		{
			$('#chat_status_offline_text').html( "You are OFFLINE" ) ;
			$('#offline_timer_description').html( "Max concurrent chat limit reached.  System will automatically place you back online when your concurrent chat total is below "+maxc+" chat session(s)." ) ;
			$('#offline_timer').hide() ;
			$('#offline_timer_btn').hide() ;
		}
		else
		{
			var extra_string = (wid) ? " Computer is idle." : "" ;
			$('#chat_status_offline_text').html( "You are OFFLINE."+extra_string ) ;
			$('#offline_timer_description').html( "Automatically log out in: &nbsp; " ) ;
			$('#offline_timer').fadeIn( "fast" ) ;
			$('#offline_timer_btn').fadeIn( "fast" ) ;
		}
	}

	function update_status( thestatus )
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		status_update_flag = 1 ;

		current_status = thestatus ;

		$('#chat_status_logout').hide() ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_status.php",
		data: "action=status&status="+thestatus+"&mapp="+mapp+"&unique="+unique+"&",
		success: function(data){
			try {
				eval(data) ;

				status_update_flag = 0 ;
				if ( json_data.status )
				{
					if ( dept_offline && json_data.offline && !$('#div_dept_offline_some_notice').is(':visible') )
						$('#div_dept_offline_some_notice').show() ;
					else if ( !json_data.offline && $('#div_dept_offline_some_notice').is(':visible') )
						$('#div_dept_offline_some_notice').hide() ;
				}
				else
				{
					toggle_status( 1 ) ; // set to offline
					$('#div_dept_offline_all_notice').fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
				}
			} catch(err) {
				do_alert( 0, "Error updating status.  Please refresh the console and try again. [e2]" ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Error updating status.  Please refresh the console and try again. [e3]" ) ;
		} });
	}

	function update_ratings()
	{
		if ( status_update_flag )
		{
			var si_status_update = setInterval(function(){
				if ( !status_update_flag )
				{
					clearInterval( si_status_update ) ;
					update_ratings_doit() ;
				}
			}, 200) ;
		}
		else { update_ratings_doit() ; }
	}

	function update_ratings_doit()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( !total_chats() )
			++fetch_rating_flag ;
		else
			fetch_rating_flag = 1 ;

		// if no chats, save query resources
		var rating_flag = ( ( fetch_rating_flag >= 10 ) && $('#rating_recent').length ) ? 0 : 1 ;
		var of = ( rd || mi || dup || ao ) ? 1 : 0 ; // for future feature
		if ( !mapp && !stopped && !reconnect && ( parseInt( timestamp_st_requesting ) < ( unique - ( VARS_JS_REQUESTING * 6 ) ) ) )
		{
			write_debug( "Idle requesting process restarted.", "" ) ;
			restart_requesting("restart") ;
		}
		$.ajax({
		type: "GET",
		url: "../ajax/chat_actions_op_itr_ratings.php",
		data: "a=fr&st="+current_status+"&f="+rating_flag+"&m="+mapp+"&"+unique+"<?php echo $deptids ?>",
		success: function(data, textstatus, request){
			try {
				eval(data) ;
				++ping_counter_rating ;
				$('#ping_counter_rating').html(ping_counter_rating) ;
			} catch(err) {
				// suppress error, system checks in intervals
				return false ;
			}
			if ( typeof( request.responseText.length ) != "undefined" )
			{
				ping_total_bytes_received += parseInt( request.responseText.length ) ;
				$('#ping_total_bytes_received').html(ping_total_bytes_received) ;
			}

			if ( json_data.status )
			{
				if ( ( json_data.v.indexOf( "." ) > 0 ) && ( version != json_data.v ) )
				{
					// refresh console to ensure latest JS libs are loaded
					var t_chats = total_chats() ;
					if ( !t_chats ) { refresh_console(0, 1) ; }
				}
				else
				{
					auto_offline_string = json_data.aof ;
					if ( rating_flag && ( typeof( json_data.ces ) != "undefined" ) && ( typeof( json_data.rt_r ) != "undefined" ) && json_data.ces )
					{
						var stars_string = ( json_data.ces ) ? "<span style=\"cursor: pointer;\">"+stars[json_data.rt_r]+"</span>" : stars[json_data.rt_r] ; 
						$('#rating_recent').html( stars_string ).off('click').on('click', function() {
							if ( json_data.ces )
							{
								open_transcript( json_data.ces ) ;
							}
						});
						$('#rating_overall').html( stars[json_data.rt_o] ) ;
						$('#chats_today').html( json_data.c_t+" accepted" ) ;
						$('#chats_overall').html( json_data.c_o+" accepted" ) ;
					}
					else
					{
						if ( !$('#rating_overall').html() )
						{
							$('#rating_recent').html( stars[0] ) ;
							$('#rating_overall').html( stars[0] ) ;
							$('#chats_today').html( "0 accepted" ) ;
							$('#chats_overall').html( "0 accepted" ) ;
						}
					}
				}
			}

			if ( parseInt( json_data.rst ) == 1 )
			{
				// restart requesting process (most likely comp went to idle/sleep mode)
				restart_requesting("rst") ;
			}

			if ( parseInt( json_data.signal ) == 1 )
			{
				rd = 1 ; // admin disconnect
				toggle_status(3) ;
			}
			else if ( parseInt( json_data.signal ) == 4 )
			{
				mi = 1 ; // mobile idle
				toggle_status(3) ;
			}
			else if ( parseInt( json_data.signal ) == 3 )
			{
				dup = 1 ; // duplicate login
				toggle_status(3) ;
			}
			else if ( parseInt( json_data.signal ) == 2 )
			{
				// automatic offline
				if ( typeof( si_automatic_offline ) != "undefined" ) { clearInterval( si_automatic_offline ) ; }
				si_automatic_offline = setInterval(function(){
					if ( !total_chats() )
					{
						clearInterval( si_automatic_offline ) ; si_automatic_offline = undeefined ;
						logout_timer = 5 ; automatic_offline_active = 1 ; ao = 1 ;
						toggle_status(1) ;
					}
				}, 1000) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			// suppress error, system checks in intervals
		} });
	}

	function start_offline_timer( thestart )
	{
		tim_offline = thestart ;
		si_offline = setInterval( "offline_timer()", 1000 ) ;
	}

	function reset_offline_timer()
	{
		if ( typeof( si_offline ) != "undefined" )
		{
			clearInterval( si_offline ) ; si_offline = undeefined ;
			start_offline_timer( logout_timer*60 ) ;
		}
	}

	function offline_timer()
	{
		if ( tim_offline )
		{
			/*
			var mins = Math.floor( tim_offline/60 ) ;
			var secs = pad( tim_offline - ( mins * 60 ), 2 ) ;
			var display = mins+":"+secs ;
			*/

			var t_chats = total_chats() ;

			if ( t_chats )
			{
				if ( $('#offline_timer').html() != "paused" )
				{
					$('#offline_timer').html( "paused" ) ;
					$('#offline_timer_paused').show() ;
					// hide on Mapp because it covers portion of the chat
					if ( mapp )
						$('#chat_status_offline').hide() ;
				}
			}
			else
			{
				var display = seconds_to_hhmmss( tim_offline ) ;
				$('#offline_timer').html( display ) ;
				--tim_offline ;

				if ( $('#offline_timer_paused').is(":visible") ) ;
					$('#offline_timer_paused').hide() ;
			}
		}
		else if ( global_maxc_flag )
		{
			clearInterval( si_offline ) ; si_offline = undeefined ;
		}
		else if ( typeof ( si_offline ) != "undefined" )
		{
			if ( automatic_offline_active ) { ao = 1 ; }
			clearInterval( si_offline ) ; si_offline = undeefined ;
			toggle_status(3) ;
		}
	}

	function launch_settings( themenu )
	{
		if ( !total_op_depts )
		{
			$('#no_dept').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
			return false ;
		}

		if ( mapp )
		{
			toggle_mapp_menu_prefs(1) ;
			toggle_extra( 'mapp_sounds', '', '', '' ) ;
		}
		else
		{
			$('#chat_footer').fadeOut("fast").promise( ).done(function( ) {
				toggle_extra( "settings", "", "", "", themenu ) ;
			}) ;
		}
	}

	function launch_alerts()
	{
		var div_width = ( mapp ) ? "100%" : "550px" ;
		var div_height = ( mapp ) ? "100%" : "455px" ;

		$('#div_alerts_wrapper').css({'width': div_width, 'height': div_height}) ;

		var div_height_inner = $('#div_alerts_wrapper').height() - 150 ;
		$('#div_alerts').css({'height': div_height_inner}) ;

		if ( !$('#div_alerts_wrapper').is(':visible') )
		{
			populate_alerts() ;
		}
		else
		{
			$('#div_alerts_wrapper').hide() ;
			$('#div_alerts').html( "<img src=\"../themes/"+theme+"/loading_fb.gif\" border=\"0\" alt=\"\">" ) ;
		}
	}

	function populate_alerts()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		// populate missed chats (alerts)
		$('#options_alert').css({ 'opacity': '0.1' }) ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_alerts.php",
		data: "action=alerts&opid="+isop+"&unique="+unique+"&",
		success: function(data){
			eval( data ) ;

			if ( json_data.status )
			{
				var declines_string = '<table cellspacing=0 cellpadding=0 border=0 width="100%">' ;

				var total_declines = 0 ;
				var json_length = json_data.declines.length ;
				for ( var c = 0; c < json_length; ++c )
				{
					var declineinfo = json_data.declines[c] ;
					var thisces = declineinfo["ces"] ;

					var is_in_his = is_ces_in_his( thisces ) ;
					if ( !is_in_his )
					{
						var created = declineinfo["created"] ;
						var vname = decodeURIComponent( declineinfo["vname"] ) ;
						var vemail = ( declineinfo["vemail"] != "null" ) ? " &lt;<a href=\"mailto:"+declineinfo["vemail"]+"\" target=\"_blank\">"+declineinfo["vemail"]+"</a>&gt;" : "" ;
						var department = ( typeof( depts_hash[declineinfo["deptid"]] ) != "undefined" ) ? depts_hash[declineinfo["deptid"]] : "&nbsp;" ;
						var ip = declineinfo["ip"] ;
						var marketing = ( ( typeof( markets[declineinfo["marketid"]] ) != "undefined" ) && ( typeof( markets[declineinfo["marketid"]]["name"] ) != "undefined" ) ) ? "<tr><td class=\"chat_info_td_h\" width=\"90\">Marketing</td><td class=\"chat_info_td\">"+markets[declineinfo["marketid"]]["name"]+"</td></tr>" : "" ;

						var status = parseInt( declineinfo["status"] ) ;
						var status_icon = ( status ) ? "<img src=\"../themes/"+theme+"/online_green.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"chat request was accepted by another operator\" title=\"chat request was accepted by another operator\">" : "" ;
						status_icon = ( parseInt( declineinfo["status_msg"] ) == 3 ) ? " &nbsp; <img src=\"../pics/icons/bullet_orange.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"chat request was cancelled by the visitor\" title=\"chat request was cancelled by the visitor\"> &nbsp; " : status_icon ;
						status_icon = ( parseInt( declineinfo["status_msg"] ) == 1 ) ? " &nbsp; <img src=\"../themes/"+theme+"/online_grey.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"chat request was declined by all online operators\" title=\"chat request was declined by all online operators\"> &nbsp; " : status_icon ;
						status_icon = ( !status && !parseInt( declineinfo["status_msg"] ) ) ? "&nbsp; <img src=\"../pics/icons/bullet_red.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"chat request was abandoned (dropped) by the visitor\" title=\"chat request was abandoned (dropped) by the visitor\"> &nbsp; " : status_icon ; 

						var status_msg_icon = ( parseInt( declineinfo["status_msg"] ) == 2 ) ? " &nbsp; <img src=\"../themes/"+theme+"/email.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"visitor sent an offline message\" title=\"visitor sent an offline message\"> &nbsp; " : "" ;

						var onpage_url = decodeURIComponent( declineinfo["onpage_url"] ) ;
						var onpage_title = decodeURIComponent( declineinfo["onpage_title"] ) ;
						var onpage = "" ;
						if ( onpage_url && ( onpage_url != "livechatimagelink" ) )
							onpage = "<a href=\""+onpage_url+"\" target=\"win_onpage\">"+onpage_title+"</a>" ;
						var ip_string = ( viewip ) ? "<tr><td class=\"chat_info_td_h\">IP</td><td class=\"chat_info_td\">"+ip+"</td></tr>" : "" ;

						var question = decodeURIComponent( declineinfo["question"] ) ;

						var custom_vars_string = decodeURIComponent( declineinfo["custom_vars"] ) ;

						var bg_color = ( (c+1) % 2 ) ? "" : "chat_info_tr_traffic_row" ;
						++total_declines ;

						declines_string += "<tr><td><div class=\"chat_info_td_t\"><span style=\"cursor: help;\">"+status_icon+status_msg_icon+"</span>"+created+"</div></td></tr><tr><td><table cellspacing=0 cellpadding=2 border=0 width=\"100%\">"+
							"<tr><td class=\"chat_info_td_h\" width=\"90\">Request Time</td><td class=\"chat_info_td\">"+created+"</td></tr>"+
							"<tr><td class=\"chat_info_td_h\">Department</td><td class=\"chat_info_td\">"+department+"</td></tr>"+
							"<tr><td class=\"chat_info_td_h\">Visitor Name</td><td class=\"chat_info_td\">"+vname+vemail+"</td></tr>"+ip_string+
							"<tr><td class=\"chat_info_td_h\">Clicked From</td><td class=\"chat_info_td\">"+onpage+"</td></tr>"+marketing+
							"<tr><td class=\"chat_info_td_h\" valign=\"top\">Question</td><td class=\"chat_info_td\"><div style=\"word-break: break-word; word-wrap: break-word;\">"+question+custom_vars_string+"</div></td></tr></table><div style=\"padding: 5px;\">&nbsp;</div></td></tr>" ;
					}
				}
				if ( !total_declines ) { declines_string += "<tr><td class=\"chat_info_td\">Blank results.</td></tr>" ; }
				declines_string += "</table>" ;
				$('#div_alerts').html( declines_string ) ;
				$('#div_alerts_wrapper').show() ;
			}
			else{ do_alert( 0, "Error fetching alerts.  Please refresh the console and try again." ) ; }
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Error fetching alerts.  Please refresh the console to reset the status. [e1]" ) ;
		} });
	}

	function open_transcript( theces )
	{
		var url = base_url_full+"/ops/op_trans_view.php?ces="+theces+"&id="+isop+"&wp="+wp+"&auth=op&"+unixtime()+"&" ;

		if ( wp )
			wp_new_win( url, "Transcript_"+theces, <?php echo $VARS_CHAT_WIDTH+120 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?> ) ;	
		else
			External_lib_PopupCenter( url, "Transcript_"+theces, <?php echo $VARS_CHAT_WIDTH+120 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?>, "scrollbars=yes,menubar=no,resizable=1,location=no,width=<?php echo $VARS_CHAT_WIDTH+100 ?>,height=<?php echo $VARS_CHAT_HEIGHT+85 ?>,status=0" ) ;

		if ( extra == "traffic" )
			document.getElementById('iframe_'+extra).contentWindow.set_trans_img( theces ) ;
	}

	function update_traffic_counter( thecounter )
	{
		var addon_chrome_extension_id = $('#addon_chrome_extension_id').val() ;

		if ( !mapp && ( prev_traffic != thecounter ) && ( extra == "traffic" ) && ( typeof( document.getElementById('iframe_traffic').contentWindow.loaded ) != "undefined" ) ) { refresh_traffic("iframe_traffic") ; }
		else if ( mapp && ( prev_traffic != thecounter ) && ( extra == "mapp_traffic" ) && ( typeof( document.getElementById('iframe_mapp_traffic').contentWindow.loaded ) != "undefined" ) ) { refresh_traffic("iframe_mapp_traffic") ; }

		if ( prev_traffic != thecounter )
		{
			$('#chat_footer_traffic_counter').html( thecounter ) ;
			if ( mapp ) { $('#chat_footer_traffic_counter_mapp').html( thecounter ) ; }
			if ( thecounter && traffic_sound )
				play_sound( 0, "new_traffic", "new_traffic" ) ;
		}

		if ( wp )
			wp_total_visitors( thecounter ) ;
		if ( addon_chrome_extension_id && ( typeof( chrome ) != "undefined" ) && ( typeof( chrome.runtime ) != "undefined" ) )
		{
			var counter_temp = ( traffic && current_status ) ? thecounter : "00" ;
			chrome.runtime.sendMessage(addon_chrome_extension_id, { total_traffic: counter_temp, status: current_status, url_href: window.location.href }, function ( reply ) { }) ;
		}

		prev_traffic = thecounter ;
	}

	function refresh_traffic( theiframe )
	{
		document.getElementById(theiframe).contentWindow.populate_traffic() ;
	}

	function toggle_traffic_sound()
	{
		if ( traffic_sound )
		{
			traffic_sound = 0 ;
			clear_sound( "new_traffic" ) ;
		}
		else
		{
			traffic_sound = 1 ;
			play_sound( 0, "new_traffic", "new_traffic" ) ;
		}

		print_traffic_sound_text() ;
	}

	function print_traffic_sound_text()
	{
		if ( traffic_sound )
			$('#div_traffic_sound').html( "traffic sound is ON" ).removeClass('sound_box_off').addClass('sound_box_on') ;
		else
			$('#div_traffic_sound').html( "traffic sound is OFF" ).removeClass('sound_box_on').addClass('sound_box_off') ;
	}

	function refresh_console( theflag, thesalert )
	{
		var unique = unixtime() ;
		var open_status = ( parseInt( current_status ) ) ? 0 : 1 ;
		if ( typeof( thesalert ) == "undefined" ) { thesalert = 0 ; }

		window.onbeforeunload = null ;
		location.href = base_url+"/ops/operator.php?reload=1&wp=<?php echo $wp ?>&open_status="+open_status+"&auto=<?php echo $auto ?>&mapp="+mapp+"&nalert="+theflag+"&salert="+thesalert+"&ins="+ins+"&"+unique+"&" ;
	}

	function refresh_console_online( theflag )
	{
		var unique = unixtime() ;

		window.onbeforeunload = null ;
		location.href = base_url+"/ops/operator.php?reload=1&wp=<?php echo $wp ?>&open_status=0&auto=<?php echo $auto ?>&mapp="+mapp+"&nalert="+theflag+"&ins="+ins+"&"+unique+"&" ;
	}

	function reconnect_doit()
	{
		++reconnect_counter ;

		if ( reconnect_counter == 1 ) { open_network_status(1) ; }

		// ~5 minutes to try to reconnect = 60 cycles (1 cycle =~ 3-4 seconds)
		// Mobile App (mapp) continuous reconnect (2000 cycles =~ 2 hours)
		var reconnect_cycles = ( mapp ) ? 5000 : 60 ;
		if ( reconnect_counter > reconnect_cycles )
		{
			stopit(0) ; $('#div_mapp_spinner').hide() ;

			var a_message = ( wp ) ? "Please exit WinApp and try again." : "Please close the operator console and login again." ;
			a_message = ( mapp ) ? "<button type=\"button\" class=\"\" onClick=\"reconnect_mapp()\" id=\"btn_reconnect\">Try to Reconnect</button>" : a_message ;
			$('#reconnect_status').html("<img src=\"../themes/"+theme+"/alert.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\"> Could not reconnect.  "+a_message) ;

			if ( chat_sound )
				play_sound( 1, "new_request", "new_request_"+sound_new_request ) ;

			if ( dn_enabled_request )
				dn_show( 'new_chat', "null", "System Alert", "Operator console could not establish a connection to the server.", 900000 ) ;

			flash_console(0) ;
			title_blink_init() ;
			if ( !mapp )
			{
				open_network_status() ;
				toggle_network_infolog() ;
			}
			write_debug( "Max reconnect attempt ("+reconnect_cycles+") reached.", "" ) ;
		}
		else
		{
			if ( reconnect_counter == 1 ) { update_network_log( "<tr id='div_network_his_"+network_counter+"' style='display: none'><td class='chat_info_td'>disconnected</td><td class='chat_info_td'>&nbsp;</td><td class='chat_info_td'>disconnected</td></tr>" ) ; }

			stopit(1) ;

			if ( mapp && ( reconnect_counter < 2 ) )
			{
				// for Mobile Apps, some devices pauses network at pause/resume.  add some buffer so the disconnect message is only
				// displayed on actual network disconnect
			}
			else
			{
				$('#reconnect_notice').fadeIn( "fast" ) ;
				if ( reconnect_counter == 1 ) { $('#div_debug').show() ; }
			}
			$('#reconnect_attempt').html( "&bull; reconnect attempt: "+reconnect_counter ) ;

			// only need to call requesting() as the function restarts chatting()
			restart_requesting("reconnect") ;
		}
	}

	function reconnect_success()
	{
		reconnect_counter = 0 ;
		if ( $('#reconnect_notice').is(':visible') )
		{
			var localtime = new Date() ; var reconnect_string ;
			toggle_status( prev_status ) ;

			$('#reconnect_notice').hide() ;
			$('#div_debug').hide() ;

			if ( mapp )
			{
				// skip - mobile devices has non-sequential network status output during reconnect
			}
			else
			{
				reconnect_string = "<tr id='div_network_his_"+network_counter+"' style='display: none'><td class='chat_info_td' colspan=2>"+localtime.toLocaleString()+"</td><td class='chat_info_td'><img src='../themes/"+theme+"/online_green.png' width='10' height='10' border=0> reconnected</td></tr>" ;
				$('#chat_info_network_info tbody tr:nth-child(2)').after( reconnect_string ) ;
				$('#div_network_his_'+network_counter).fadeIn("fast") ;
			}
			reconnect_string = "<tr><td class='chat_info_td'>"+localtime.toLocaleString()+"</td><td class='chat_info_td'><img src='../themes/"+theme+"/online_green.png' width='10' height='10' border=0> reconnected</td></tr>" ;
			$('#chat_info_network_log tbody tr:nth-child(2)').after( reconnect_string ) ;
			++network_counter ;

			if ( network_counter > total_network_display )
			{
				var div_delete = network_counter - total_network_display - 1 ;
				$('#div_network_his_'+div_delete).fadeOut( "slow", function() {
					$('#div_network_his_'+div_delete).remove() ;
				});
			}
		}
	}

	function toggle_network_infolog()
	{
		if ( $('#chat_info_wrapper_network_info').is(':visible') )
		{
			$('#chat_info_wrapper_network_info').hide() ;
			$('#chat_info_wrapper_network_log').fadeIn( "fast" ) ;
		}
		else
		{
			$('#div_debug').hide() ;
			$('#chat_info_wrapper_network_log').hide() ;
			$('#chat_info_wrapper_network_info').fadeIn( "fast" ) ;
		}
	}

	function expand_map( theleft, themd5, theip )
	{
		for ( var thismd5 in maps_his_ )
			$('#info_maps_footprint_'+thismd5).hide() ;

		if ( !<?php echo $geoip ?> )
			return true ;

		var unique ; // indication of first load of the map
		if ( typeof( maps_his_[themd5] ) == "undefined" )
		{
			unique = unixtime() ;
			var iframe_map = document.createElement( "iframe" ); 
			iframe_map.setAttribute( "src", "./maps.php?ip="+theip+"&vis_token="+themd5+"&viewip="+viewip+"&"+unique+"&" ) ; 
			iframe_map.style.display = "none" ;
			iframe_map.border = 0 ;
			iframe_map.frameBorder = 0 ;
			iframe_map.setAttribute( "id", "info_maps_footprint_"+themd5 ) ;
			maps_his_[themd5] = iframe_map ;

			$('#info_maps_').append( iframe_map ) ;
			$("#info_maps_footprint_"+themd5).css({"width": "100%", "overflow": "hidden"}).fadeIn(500) ;
		}
		else
			$('#info_maps_footprint_'+themd5).fadeIn(500) ;

		var pos = $('#chat_extra_wrapper').position() ;
		var top = pos.top + 30 ;
		var width = parseInt( $('#chat_extra_wrapper').width() ) - theleft - 20 ;
		var height = parseInt( $('#chat_extra_wrapper').height() ) - 89 ;
		width = ( width > 815 ) ? 815 : width ;
		$('#div_geomap').css({'top': top, 'left': theleft, 'width': width, 'height': height}).fadeIn( "fast" ) ;

		var height_ = height - 25 ;
		$("#info_maps_footprint_"+themd5).css({"height": height_}) ;

		if ( typeof( unique ) == "undefined" )
			document.getElementById('info_maps_footprint_'+themd5).contentWindow.adjust_height() ;
	}

	function delete_object( thetype, themd5 )
	{
		if ( thetype == "map" )
		{
			if ( typeof( maps_his_[themd5] ) != "undefined" )
			{
				delete maps_his_[themd5] ;
				$('#info_maps_footprint_'+themd5).remove() ;
			}
		}
		else if ( thetype == "traffic" )
		{
			if ( typeof( traffic_data[themd5] ) != "undefined" )
				delete traffic_data[themd5] ;
		}
	}

	function open_network_status( theforce )
	{
		if ( $('#chat_info_wrapper_network').is(':visible') && !theforce )
		{
			$('#div_debug').hide() ;
			$('#chat_info_wrapper_network').hide() ;
			$('#chat_info_wrapper_info').fadeIn( "fast" ) ;
		}
		else if ( !mapp )
		{
			$('#chat_info_wrapper_info').hide() ;
			$('#chat_info_wrapper_network').fadeIn( "fast" ) ;

			if ( $('#chat_info_wrapper_network_log').is(':visible') ) { toggle_network_infolog() ; }
			if ( !$('#chat_info_container').is(':visible') ) { toggle_slider(0) ; }
		}
	}

	function toggle_debug()
	{
		if ( $('#div_debug').is(':visible') )
			$('#div_debug').hide() ;
		else
		{
			var div_debug_width = ( mapp ) ? "300px" : "400px" ;
			$('#div_debug').css({width: div_debug_width}).show() ;
		}
	}

	function toggle_slider( theforce )
	{
		if ( $('#div_voice_chat').length && $('#div_voice_chat').is(':visible') )
		{
			$('#div_voice_chat').fadeOut('fast').fadeIn('fast') ;
		}
		else
		{
			var browser_width = $(window).width() ;

			if ( $('#chat_info_container').is(':visible') || theforce )
			{
				var chat_body_width = browser_width - ( 35 + ( parseInt( $('#chat_body').css('padding-left').replace( /px/, "" ) ) * 2 ) ) ;
				var chat_info_container = ( !$('#chat_info_wrapper_network').is(':visible') ) ? "chat_info_container" : "chat_info_wrapper_network" ;
				var chat_info_container_ = ( chat_info_container == "chat_info_container" ) ? "chat_info_wrapper_network" : "chat_info_container" ;

				$('#'+chat_info_container).fadeOut( "fast", function() {
					$('#chat_body').animate({
						width: chat_body_width
					}, 400, function() {
						$('#'+chat_info_container_).fadeOut( "fast", function() {
							//
						});
						init_scrolling() ;
						$('#icons_slider').attr( 'src', '../themes/'+theme+'/slider_left.png' ) ;
						$('#chat_data').hide() ;
					});
				}) ;
			}
			else
			{
				var chat_body_padding = $('#chat_body').css('padding-left') ;
				var chat_body_padding_diff = ( typeof( chat_body_padding ) != "undefined" ) ? 20 - ( chat_body_padding.replace( /px/, "" ) * 2 ) : 0 ;
				var body_width = browser_width - 450 ;
				var chat_body_width = body_width + chat_body_padding_diff ;

				$('#chat_body').animate({
					width: chat_body_width
				}, 400, function() {
					$('#chat_info_container').fadeIn( "fast", function() {
						if ( !$('#chat_info_wrapper_info').is(':visible') )
							$('#chat_info_wrapper_network').fadeIn( "fast" ) ;
					}) ;
					init_scrolling() ;
					$('#icons_slider').attr( 'src', '../themes/'+theme+'/slider_right.png' ) ;
					$('#chat_data').show() ;
				});
			}
		}
	}

	function process_shortcuts( thetext )
	{
		if ( ( thetext == "/t" ) || ( thetext == "/n" ) ) { $('textarea#input_text').val( "" ) ; activate_chat( get_chat_next( ces ) ) ; }
		else if ( ( thetext == "/exit" ) || ( thetext == "/close" ) ) { pre_disconnect() ; }
		else if ( ( thetext == "/accept" ) && ( ( typeof( ces ) != "undefined" ) && ( !chats[ces]["status"] || ( chats[ces]["status"] == 2 ) ) ) ) { chat_accept() ; }
		else if ( ( thetext == "/decline" ) && ( ( typeof( ces ) != "undefined" ) && ( !chats[ces]["status"] || ( chats[ces]["status"] == 2 ) ) ) ) { chat_decline() ; }
		$('textarea#input_text').val( "" ) ;
	}

	function toggle_last_response( theforce_close )
	{
		if ( $('#div_last_response').is(':visible') || theforce_close )
		{
			$('#div_last_response').hide() ;
		}
		else
		{
			if ( typeof( chats[ces] ) != "undefined" )
			{
				if ( chats[ces]["disconnected"] )
				{
					do_alert( 0, "Chat has ended." ) ;
				}
				else
				{
					var diff = unixtime() - chats[ces]["recent_res"] ;
					var mins =  Math.floor( diff/60 ) ; if ( !mins ) { mins = 1 ; }

					var timer_pos = $("#chat_vtimer").position() ;
					var height_timer_box = parseInt( $('#div_last_response').outerHeight() ) ;
					var timer_top = timer_pos.top - height_timer_box - 10 ;
					var timer_left = timer_pos.left ;

					$('#last_response_timer_vname').html( chats[ces]["vname"] ) ;
					$('#last_response_timer').html( mins ) ;
					$('#div_last_response').css({'top': timer_top, 'left': timer_left}).fadeIn( "fast" ) ;
				}
			}
		}
	}

	function update_tag( thetagid )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		$('#tagid').attr('disabled', true) ;

		if ( ( typeof( ces ) != "undefined" ) && chats[ces]["status"] )
		{
			if ( chats[ces]["tag"] != thetagid )
			{
				$.ajax({
				type: "POST",
				url: "../ajax/chat_actions_op_tag.php",
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

	function write_debug( thedata, theerr )
	{
		var time_string = new Date() ;
		time_string = time_string.toString() ;
		time_string = time_string.replace( / GMT(.*?) /i, "" ) ;
		time_string = time_string.replace( /\((.*?)\)$/i, "" ) ;

		var error_message = "\r\n----- ERR : "+time_string+" -----\r\n"+thedata+"\r\n"+theerr ;
		var orig = $('#error_message').val() ;
		$('#error_message').val( orig+error_message ) ;
	}

	function close_sound_interaction()
	{
		$('#div_sound_interaction_message').animate({'top': '-500px'}, {
			duration: 300,
			complete: function() {
				init_nsleep() ;
				$('#div_sound_interaction').fadeOut("fast") ;
				$('#div_sound_interaction_message').hide() ;
				do_alert( 1, "Sound Enabled" ) ;
			}
		});
	}

	function update_theme( thetheme, thetheme_timestamp )
	{
		status_update_flag = 1 ; // disable until finish

		$('html, body').animate({
			opacity: 0
		}, 1000, function() {
			$('link[href="../themes/'+theme+'/style.css?'+theme_timestamp+'"]').remove() ;
			$('head').append( '<link rel="Stylesheet" href="../themes/'+thetheme+'/style.css?'+thetheme_timestamp+'" />') ;

			var regex_match = new RegExp( "themes/"+theme+"/", "gi" ) ;
			$('img').each(function() {
				var image_src = $(this).attr('src') ;

				if ( image_src.indexOf( "themes" ) > 0 )
				{
					var image_src_new = image_src.replace( regex_match, "themes/"+thetheme+"/" ) ;
					$(this).attr('src', image_src_new) ;
				}
			}) ;

			regex_match = new RegExp( "themes/"+theme+"/stars.png", "gi" ) ;
			$('.class_star').each(function() {
				var image_src = $(this).css('background-image') ;
				
				var image_src_new = image_src.replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;

				$(this).css('background-image', image_src_new ) ;
			}) ;

			stars[5] = stars[5].replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;
			stars[4] = stars[4].replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;
			stars[3] = stars[3].replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;
			stars[2] = stars[2].replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;
			stars[1] = stars[1].replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;
			stars[0] = stars[0].replace( regex_match, "themes/"+thetheme+"/stars.png" ) ;

			theme = thetheme ;
			theme_timestamp = thetheme_timestamp ;

			status_update_flag = 0 ;

			for ( var thisces in maps_his )
			{
				delete maps_his[thisces] ;
				$('#iframe_maps_'+thisces).remove() ;
			}

			// revert to info tab due to iframe maps reset
			toggle_info('info') ;

			$('html, body').animate({
			opacity: 1
			}, 1000, function() {
				// need to do the check here because color is not registered if opacity is 0
				if ( $('#status_1').is(':checked') )
				{
					var color = $('#chat_status_offline').css("background-color") ;
					$('#chat_status_offline_radio').css({'background-color': color}) ;
				}
			});
		});
	}

	function toggle_menu_op()
	{
		if ( $('#div_menu_op').is(':visible') )
		{
			$('#div_menu_op').animate({'left': -300}, {
				duration: 500,
				complete: function() {
					$('#div_menu_op').hide() ;
				}
			});
		}
		else
		{
			if ( $('#div_notification').is(':visible') ) { $('#div_notification').fadeOut( "fast" ) ; }
			$('#div_menu_op').show() ;

			var pos = $("#options_menu_op").position() ;
			var height_box = parseInt( $('#div_menu_op').outerHeight() ) ;
			var top = pos.top - height_box - 10 ;
			var left = pos.left - 25 ;

			$('#div_menu_op').animate({'left': left}, {
				duration: 500,
				complete: function() {
					//
				}
			});
		}
	}
//-->
</script>
</head>
<body style="display: none; overflow: hidden; -webkit-text-size-adjust: 100%;" onClick="clear_flash_console();">

<div id="chat_canvas" style="min-height: 100%; width: 100%;" class="chat_canvas_op">
	<div id="chat_switchboard" style="position: relative; height: 19px; padding-left: 10px; z-Index: 500;" onClick="input_focus();dn_close();"></div>
</div>
<div id="chat_body_wrapper" style="position: absolute; top: 20px; padding: 10px; z-Index: 2;" onClick="input_focus();dn_close();<?php if ( !$mapp ): ?>close_extra( extra );<?php endif ; ?>">
	<div id="chat_body" style="overflow: auto; <?php echo ( $mobile || $mapp ) ? " padding: 5px;": "" ; ?> word-break: break-word; word-wrap: break-word;" onClick="close_misc('all')"><div class="info_neutral" style="display: inline-block; *display: inline; zoom: 1; margin-top: 5px; padding: 25px;"><img src="../pics/spinner.gif" width="16" height="16" border="0" alt="" class="round" title="connecting..." alt="connecting..."> loading</div></div>
	<div id="chat_options" style="padding: 5px;">
		<div style="height: 16px;">
			<?php if ( !$mapp ): ?>
			<div id="options_menu_op" style="float: left; padding-right: 15px; cursor: pointer;" onClick="toggle_menu_op()"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../themes/<?php echo $theme ?>/vcard.png" width="16" height="16" border="0"></td><td>&nbsp;Menu</td></tr></table></div>
			<div id="options_menu_trans" style="float: left; padding-right: 15px; cursor: pointer;" onClick="launch_settings('trans')"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../themes/<?php echo $theme ?>/view.png" width="16" height="16" border="0"></td><td>&nbsp;Transcripts</td></tr></table></div>
			<?php endif ; ?>
			<div id="options_sound" style="float: left; padding-right: 15px; cursor: pointer;" onClick="launch_settings('sound')"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../themes/<?php echo $theme ?>/sound_on.png" width="16" height="16" border="0" alt="" id="chat_sound"></td><?php if ( !$mapp ): ?><td>&nbsp;Sound</td><?php endif ; ?></table></div>
			<div id="options_expand_v" style="display: none; float: left; padding-right: 15px;"><img src="../themes/<?php echo $theme ?>/slider_v.png" width="16" height="16" border="0" alt="" onClick="toggle_input_text()" id="chat_expand" title="expand input" alt="expand input" style="cursor: pointer;"></div>
			<div id="options_print" style="display: none; float: left;">
				<span style="display: none; padding-right: 15px;" id="span_emoticons"><img src="<?php echo $CONF["BASE_URL"] ?>/addons/emoticons/smile.png" width="16" height="16" border="0" title="emoji" alt="emoji" style="cursor: pointer;" id="chat_emoticons" onClick="toggle_emo_box(0);close_misc('attach');close_misc('trans');"></span>
				<?php if ( $VARS_INI_UPLOAD && $can_upload && is_file( "$CONF[DOCUMENT_ROOT]/addons/file_attach/file_attach.php" ) ): ?><span style="padding-right: 15px;" id="chat_file_attach"><img src="<?php echo $attach_icon ?>" width="16" height="16" border="0" onClick="toggle_file_attach(0);close_misc('emo');close_misc('trans');" title="file attachment" alt="file attachment" style="cursor: pointer;"></span><?php endif ; ?>
				<span style="padding-right: 15px;" id="chat_email"><img src="../themes/<?php echo $theme ?>/email.png" width="16" height="16" border="0" onClick="toggle_send_trans(0);close_misc('emo');close_misc('attach');" alt="send transcript" title="send transcript" style="cursor: pointer;"></span>
				<span style="padding-right: 15px;" id="chat_printer"><img src="../themes/<?php echo $theme ?>/printer.png" width="16" height="16" border="0" alt="" onClick="do_print(ces, 0, isop, <?php echo $VARS_CHAT_WIDTH+100 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?>)" title="print transcript" alt="print transcript" style="cursor: pointer;"></span>
				<span id="chat_vtimer_wrapper" style="display: none; padding-right: 15px; cursor: pointer;" onClick="toggle_last_response(0)"><span style="font-weight: normal; display: inline-block; padding: 2px;" id="chat_vtimer">00:00</span></span>
				<span id="options_mapp_vinfo" style="display: none; padding-right: 5px; cursor: pointer;" onClick="toggle_extra( 'mapp_vinfo', '', '', 'Visitor Info' )"><img src="../themes/<?php echo $theme ?>/profile_vis.png" width="16" height="16" border="0" alt=""> visitor info</span>
				<span id="chat_processing" style="display: none; padding-right: 15px;"><img src="../themes/<?php echo $theme ?>/loading_chat.gif" width="16" height="16" border="0" alt="loading..." title="loading..."></span>
				<span id="chat_vname" style="padding: 2px; padding-right: 15px;"></span>
				<span id="chat_vistyping_wrapper" style="display: none; padding-right: 15px;"><span id="chat_vistyping" style="display: none; padding-right: 15px;"><img src="<?php echo $typing_icon ?>" width="16" height="16" border="0" alt="is typing..." title="is typing..."></span></span>
			</div>
			<div id="options_expand" style="float: right;"><span style="cursor: pointer;"><img src="../themes/<?php echo $theme ?>/slider_right.png" width="16" height="16" border="0" onClick="toggle_slider(0)" id="icons_slider" name="icons_slider"></span></div>
			<div id="options_alert" style="float: right; padding-right: 15px; opacity: 0.1; filter: alpha(opacity=10);"><span style="cursor: pointer;" onClick="launch_alerts()"><img src="../themes/initiate/notice.png" width="16" height="16" border="0" alt="Alert" title="Alert"></span></div>
			<div style="clear: both;"></div>
		</div>
	</div>
	<div id="chat_input" style="margin-top: 8px;" onClick="dn_close();">
		<div style="display: none; float: left; width: 105px; cursor: pointer; text-align: center;" id="div_profile_pic" onClick="toggle_menu_op()"></div>
		<div style="float: left;" id="div_input_text"><textarea id="input_text" rows="3" style="height: 75px; resize: none;" wrap="virtual" onKeyup="input_text_listen(event);" onKeydown="input_text_typing(event);clear_flash_console();" spellcheck="true" <?php echo ( isset( $VALS["AUTOCORRECT_O"] ) && !$VALS["AUTOCORRECT_O"] ) ? "autocomplete=\"off\" autocorrect=\"off\"" : "" ; ?>></textarea></div>
		<div style="clear:both;"></div>
	</div>
</div>
<div id="chat_data" style="position: absolute; overflow: hidden; z-Index: 3;">
	<div class="chat_info_wrapper" style="margin-right: 8px;">

		<div id="chat_info_container">
			<div id="chat_info_header" style="margin-bottom: 5px;">
				<?php if ( $opinfo["rate"] ): ?>
				<div style="float: left; margin-right: 25px; ">
					<div class="rating_title">recent rating:</div>
					<div id="rating_recent"></div>
				</div>
				<div style="float: left; margin-right: 25px;">
					<div class="rating_title">overall rating:</div>
					<div id="rating_overall"></div>
				</div>
				<?php endif ; ?>
				<div style="float: left; margin-right: 10px;">
					<div class="rating_title">chats today:<div id="chats_today" style=""></div></div>
				</div>
				<div style="float: left;">
					<div class="rating_title">chats overall:<div id="chats_overall" style=""></div></div>
				</div>
				<div style="clear: both;"></div>
			</div>
			<div id="chat_info_wrapper_info" style="margin-top: 15px;">
				<div id="chat_info_menu_list">
					<div id="info_menu_info" class="chat_info_menu" onClick="toggle_info('info')">Visitor Info</div>
					<?php if ( $CONF["foot_log"] == "on" ): ?><div id="info_menu_footprints" class="chat_info_menu" onClick="toggle_info('footprints')">Footprints</div><?php endif ; ?>
					<div id="info_menu_transcripts" class="chat_info_menu" onClick="toggle_info('transcripts')">Transcripts</div>
					<div id="info_menu_transfer" class="chat_info_menu" onClick="toggle_info('transfer')">Transfer Chat</div>
					<div id="info_menu_maps" class="chat_info_menu" onClick="toggle_info('maps')">Geo</div>
					<div id="info_menu_spam" class="chat_info_menu" onClick="toggle_info('spam')" style="margin-right: 0px;">Block</div>
					<div style="clear: both"></div>
				</div>
				<div id="chat_info_body" style="overflow: auto;">
					<div id="info_info" style="display: none;">
						<div id="info_info_body" style="">
							<table cellspacing=0 cellpadding=0 border=0>
							<tr><td class="chat_info_td_h" style="padding-top: 10px; padding-bottom: 10px;">Department</td><td width="100%" class="chat_info_td"> <span id="req_dept" style="font-weight: bold;"></span></td></tr>
							<tr><td class="chat_info_td_h" nowrap style="padding-top: 10px; padding-bottom: 10px;">Visitor Email</td><td class="chat_info_td"> <span id="req_email"></span></td></tr>
							<tr><td class="chat_info_td_h" nowrap style="padding-top: 10px; padding-bottom: 10px;">Chat Request</td><td class="chat_info_td"> <span id="req_request"></span></td></tr>
							<tr><td class="chat_info_td_h" nowrap style="padding-top: 10px; padding-bottom: 10px;">Clicked From</td><td class="chat_info_td"> <span id="req_onpage"></span></td></tr>
							<tr><td class="chat_info_td_h" style="padding-top: 10px; padding-bottom: 10px;">Refer URL</td><td class="chat_info_td"> <span id="req_refer"></span></td></tr>
							<tr id="tr_market" style="display: none;"><td class="chat_info_td_h">Campaign</td><td class="chat_info_td"> <span id="req_market"></span></td></tr>
							<tr><td nowrap class="chat_info_td_h" style="padding-top: 10px; padding-bottom: 10px;">Resolution</td><td class="chat_info_td"> <span id="req_resolution"></span></td></tr>
							<?php if ( $opinfo["viewip"] ): ?><tr><td nowrap class="chat_info_td_h">IP Address</td><td class="chat_info_td"> <span id="req_ip"></span></td></tr><?php endif ; ?>
							<tr><td nowrap class="chat_info_td_h" style="padding-top: 10px; padding-bottom: 10px;" nowrap>Custom Fields</td><td class="chat_info_td"><div id="req_custom" style="max-height: 80px; overflow-y: auto; overflow-x: hidden;"></div></td></tr>
							<tr id="tr_files" style="display: none;"><td nowrap class="chat_info_td_h" nowrap style="padding-top: 10px; padding-bottom: 10px;">Files</td><td class="chat_info_td"><div id="req_files" style="max-height: 50px; overflow-y: auto;"></div></td></tr>
							<tr id="tr_tag" style="display: none;"><td nowrap class="chat_info_td_h" style="padding-top: 10px; padding-bottom: 10px;">Tag</td><td class="chat_info_td"> <span id="req_tag">
								<select id="tagid" style="font-size: 14px; padding: 4px;" onChange="update_tag(this.value)"><option value="0"></option><?php echo $tags_options ?></select></span> &nbsp; <span id="req_tag_saved" class="info_good" style="display: none; padding: 4px !important;">saved</span>
							</td></tr>
							<tr style="display: none;"><td class="chat_info_td_h" nowrap>Visitor ID</td><td class="chat_info_td"> <span id="req_vid" style=""></span></td></tr>
							<tr><td class="chat_info_td_h" style="padding-top: 10px; padding-bottom: 10px;">Chat ID</td><td class="chat_info_td"> <span id="req_ces"></span></td></tr>
							</table>
						</div>
					</div>
					<div id="info_maps" style="display: none; padding: 10px;"></div>
					<div id="info_footprints" style="display: none; padding: 10px;"><img src="../themes/<?php echo $theme ?>/loading_fb.gif" border="0" alt=""></div>
					<div id="info_transcripts" style="display: none; padding: 10px; overflow-x: hidden;"><img src="../themes/<?php echo $theme ?>/loading_fb.gif" border="0" alt=""></div>
					<div id="info_transfer" style="display: none; padding: 10px;"><img src="../themes/<?php echo $theme ?>/loading_fb.gif" border="0" alt=""></div>
					<div id="info_spam" style="display: none; padding: 10px;"></div>
				</div>
			</div>
		</div>

		<div id="chat_info_wrapper_network" style="display: none; overflow: auto; overflow-x: hidden;" class="info_content">
			<div id="chat_info_wrapper_network_info">
				<table cellspacing=0 cellpadding=2 border=0 width="100%" id="chat_info_network_info">
				<tbody>
				<tr><td colspan=3><div class="info_box">
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td width="100%">Status | <span onClick="toggle_network_infolog()" style="text-decoration: underline; cursor: pointer;">Log</span> <img src="../themes/<?php echo $theme ?>/view.png" width="16" height="16" border="0" alt="detailed log" title="detailed log" style="cursor: pointer;" onClick="toggle_debug()"> &nbsp; <span class="info_box" style="font-size: 10px; cursor: help" title="ratings ping counter : chats ping counter : ping total [duration hours:duration minutes]" alt="ratings ping counter : chats ping counter : ping total duration hours:duration minutes"> <span id="ping_counter_rating"></span>:<span id="ping_counter_req"></span></span> &nbsp; <span class="info_box" style="font-size: 10px; cursor: help;" title="total data received (bytes)" alt="total data received (bytes)" id="ping_total_bytes_received"></span></td>
						<td align="right"><button type="button" style="" onClick="open_network_status()">close</button></td>
					</tr>
					</table>
				</div></td></tr>
				<tr>
					<td class="chat_info_td_h" width="37%" nowrap><b>Network Speed</b><div style="font-size: 10px;">(seconds)</div></td>
					<td class="chat_info_td_h" width="37%" nowrap><b>Server Response</b><div style="font-size: 10px;">(seconds)</div></td>
					<td class="chat_info_td_h" width="24%" nowrap><b>Total</b><div style="font-size: 10px;">(seconds)</div></td>
				</tr>
				</tbody>
				</table>
			</div>
			<div id="chat_info_wrapper_network_log" style="display: none;">
				<table cellspacing=0 cellpadding=2 border=0 width="100%" id="chat_info_network_log">
				<tbody>
				<tr><td colspan=3 class="chat_info_td"><div class="info_box">
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td width="100%"><span onClick="toggle_network_infolog()" style="text-decoration: underline; cursor: pointer;">Status</span> | <span>Log</span> <img src="../themes/<?php echo $theme ?>/view.png" width="16" height="16" border="0" alt="detailed log" title="detailed log" style="cursor: pointer;" onClick="toggle_debug()"></td>
						<td align="right"><button type="button" style="" onClick="open_network_status()">close</button></td>
					</tr>
					</table>
					<div style="display: none; padding: 5px; margin-top: 5px; background: #FFFFFF; color: #000000; height: 100px; font-family: Courier New;"><div>---------- OUTPUT LOG ----------</div><div id="chat_info_wrapper_network_log_output"></div></div>
				</div></td></tr>
				<tr>
					<td class="chat_info_td_h" width="37%"><b>Time</b></td>
					<td class="chat_info_td_h" width="24%"><b>Error Code</b></td>
				</tr>
				</tbody>
				</table>
			</div>
		</div>
		<div id="sounds" style="width: 1px; height: 1px; overflow: hidden; opacity:0.0; filter:alpha(opacity=0);">
			<span id="div_sounds_new_request"></span>
			<span id="div_sounds_new_text"></span>
			<span id="div_sounds_new_traffic"></span>
			<span id="div_sounds_new_liner"></span>
			<span id="div_sounds_message_board"></span>
			<audio id='div_sounds_audio_new_request'></audio>
			<audio id='div_sounds_audio_new_text'></audio>
			<audio id='div_sounds_audio_new_traffic'></audio>
			<audio id='div_sounds_audio_new_liner'></audio>
			<audio id='div_sounds_audio_message_board'></audio>
		</div>
	</div>
</div>
<div id="chat_btn" style="position: absolute; padding-right: 10px; z-Index: 10">
	<button id="input_btn" type="button" OnClick="add_text_prepare(1)">Submit</button>
	<div style="margin-top: 5px; font-size: 12px;" id="chat_text_login"></div>
</div>

<div id="chat_panel" style="display: none; position: absolute; z-Index: 10">
	<div id="chat_status" style="float: left; height: 75px; padding-left: 10px;">
		Status
		<div id="chat_status_status" style="margin-top: 5px;">
			<form>
			<table cellspacing=0 cellpadding=0 border=0>
			<tr>
				<td><div style="padding: 4px; cursor: pointer;" onClick="toggle_status(0);"><input type="radio" name="status" id="status_0" value=0 checked> online </div></td>
				<td style="padding-left: 5px;"><div style="padding: 4px; cursor: pointer;" onClick="toggle_status(1);" id="chat_status_offline_radio" class="round"><input type="radio" name="status" id="status_1" value=1> offline </div></td>
				<td style="padding-left: 5px;"><div style="padding: 4px; cursor: pointer;" onClick="toggle_status(2);"><input type="radio" name="status" id="status_2" value=2> logout </div></td>
			</tr>
			</table>
			</form>
		</div>
		<div style="display: none; margin-top: 2px; margin-bottom: 50px;" class="info_error" id="div_dept_offline_all_notice">You have set all departments <br>to offline.  <button type="button" onClick="toggle_extra( 'op2op', '', '', 'Operators' )" class="input_op_button">set to online</button></div>
		<div style="display: none; margin-top: 2px;" class="info_error" id="div_dept_offline_some_notice">Some departments are offline.<br><button type="button" onClick="toggle_extra( 'op2op', '', '', 'Operators' )" class="input_op_button">set to online</button></div>
	</div>
	<div id="chat_network" style="float: left; height: 75px; padding-left: 15px;">
		Network
		<div id="chat_network_img" style="margin-top: 5px; width: 50px; height: 38px; cursor: pointer;" title="network status" alt="network status" onClick="open_network_status()"></div>
	</div>
	<div style="clear: both;"></div>
</div>
<div id="chat_status_offline" style="position: absolute; display: none; padding: 5px; width: 255px; height: 80px; z-Index: 104;" onClick="close_extra( extra );">
	<div id="chat_status_offline_text" style="padding: 2px; font-weight: bold;" class="round">OFFLINE</div>
	<div style="margin-top: 5px;">
		<table cellspacing=0 cellpadding=0 border=0 width="100%">
		<tr>
			<td><div id="offline_timer_description" style="text-align: justify;">Automatically log out in: </div></td>
			<td width="50" id="offline_timer" style="font-family: Impact, Serif; font-size: 18px;"></td>
			<td style="padding-left: 5px;" align="right"><div id="offline_timer_btn"><form><button type="button" style="font-size: 10px;" onClick="reset_offline_timer();">Reset</button></form></div></td>
		</tr>
		</table>
		<div id="offline_timer_paused" style="display: none; margin-top: 5px;">You are in a chat session.  Timer paused.</div>
	</div>
</div>

<div id="chat_footer" style="position: absolute; width: 100%; bottom: 0px; height: 34px; overflow: hidden; z-Index: 106;">

	<?php if ( $opinfo["op2op"] ): ?>
	<div class="chat_footer_cell_noclick"><img src="../themes/<?php echo $theme ?>/divider.png" border="0" alt=""></div>
	<div id="chat_footer_cell_op2op" class="chat_footer_cell" onClick="toggle_extra( 'op2op', '', '', 'Operators' )">Operators</div>
	<?php endif ; ?>

	<div class="chat_footer_cell_noclick"><img src="../themes/<?php echo $theme ?>/divider.png" border="0" alt=""></div>
	<div id="chat_footer_cell_canned" class="chat_footer_cell" onClick="toggle_extra( 'canned', '', '', 'Canned Responses' )">Canned Responses</div>
	<div class="chat_footer_cell_noclick" style="padding-top: 1px;"><select id="canned_select" style="width: 220px;" onChange="$(this).blur();"></select> <button type="button" id="canned_select_btn" onClick="select_canned()" class="input_op_button">select</button></div>

	<?php if ( $opinfo["traffic"] ): ?>
	<div class="chat_footer_cell_noclick"><img src="../themes/<?php echo $theme ?>/divider.png" border="0" alt=""></div>
	<div id="chat_footer_cell_traffic" class="chat_footer_cell" onClick="toggle_extra( 'traffic', '', '', 'Traffic Monitor' )">Traffic Monitor <span id="chat_footer_traffic_counter">00</span></div>
	<?php endif; ?>

	<?php if ( $opinfo["op2op"] ): ?>
	<div class="chat_footer_cell_noclick"><img src="../themes/<?php echo $theme ?>/divider.png" border="0" alt=""></div>
	<div id="chat_footer_cell_message_board" class="chat_footer_cell" onClick="toggle_extra( 'message_board', '', '', 'Message Board' )">
		<table cellspacing=0 cellpadding=0 border=0>
		<tr>
			<td>Message Board</td>
			<td style="padding-left: 5px; opacity: 0.3; filter: alpha(opacity=30);" id="td_message_board_icon"><img src="../themes/initiate/notice.png" width="12" height="12" border="0" alt="" id="img_message_board_icon"></td>
		</tr>
		</table>
	</div>
	<?php endif ; ?>

	<?php
		for ( $c = 0; $c < count( $externals ); ++$c )
		{
			$external = $externals[$c] ;
			$name = preg_replace( "/\"/", "&ldquo;", preg_replace( "/'/", "&lsquo;", $external["name"] ) ) ;

			print "
				<div class=\"chat_footer_cell_noclick\"><img src=\"../themes/$theme/divider.png\" border=\"0\" alt=\"\"></div>
				<div id=\"chat_footer_cell_ext_$external[extID]\" class=\"chat_footer_cell\" onClick=\"toggle_extra( $external[extID], '', '$external[url]', '$name' )\">$name</div>
			" ;
		}
	?>

	<div class="chat_footer_cell_noclick"><img src="../themes/<?php echo $theme ?>/divider.png" border="0" alt=""></div>
	<div style="clear: both;"></div>
</div>
<div id="chat_extra_wrapper" style="position: absolute; display: none; bottom: 0px; width: 100%; overflow: hidden; z-Index: 105;">
	<div id="chat_extra_title" style="font-size: 16px; font-weight: bold; padding: 6px; padding-left: 10px;"></div>
	<div id="chat_extra_body_op2op" style="display: none;"><iframe id="iframe_op2op" name="iframe_op2op" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<div id="chat_extra_body_message_board" style="display: none;"><iframe id="iframe_message_board" name="iframe_message_board" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<div id="chat_extra_body_traffic" style="display: none;"><iframe id="iframe_traffic" name="iframe_traffic" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<div id="chat_extra_body_canned" style="display: none;"><iframe id="iframe_canned" name="iframe_canned" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<div id="chat_extra_body_trans" style="display: none;"><iframe id="iframe_trans" name="iframe_trans" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<div id="chat_extra_body_at" style="display: none;"><iframe id="iframe_at" name="iframe_at" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<div id="chat_extra_body_extras" style="display: none;"><iframe id="iframe_extras" name="iframe_extras" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<?php
		for ( $c = 0; $c < count( $externals ); ++$c )
		{
			$external = $externals[$c] ;
			print "<div id=\"chat_extra_body_ext_$external[extID]\" style=\"display: none;\"></div>" ;
		}
	?>
	<div id="chat_extra_body_settings" style="display: none;"><iframe id="iframe_settings" name="iframe_settings" style="width: 100%; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0></iframe></div>
	<?php if ( $mapp ) { include_once( "../mapp/mapp_iframes.php" ) ; } ?>
</div>
<div id="info_disconnect" class="info_disconnect" style="position: absolute; top: 1px; right: 0px; text-align: right; z-Index: <?php echo ( $mapp ) ? 104 : 601 ; ?>;" onClick="pre_disconnect();"></div>
<div id="chat_status_logout" style="position: absolute; display: none; width: 100%; bottom: 0px; height: 78px; z-Index: 600;">
	<div id="chat_status_logout_confirm" style="position: absolute; bottom: 0px; right: 0px; padding-bottom: 15px; padding-right: 20px; ">
		<form>
		<table cellspacing=0 cellpadding=5 border=0>
		<tr>
			<td nowrap><div class="info_error"><img src="../themes/<?php echo $theme ?>/alert.png" width="16" height="16" border="0" alt=""> Really logout and go offline?  <button type="button" onClick="toggle_status(3)">Yes, Log Out.</button></div></td>
			<td><div style="padding-left: 15px;"> <button type="button" onClick="toggle_status(1000)">Cancel</button></div></td>
		</tr>
		</table>
		</form>
	</div>
</div>
<?php
	include_once( "../inc_email_trans.php" ) ;
	if ( $mapp )
		include_once( "../mapp/mapp_footer_menu.php" ) ;
?>

<div id="div_notification" style="display: none; position: absolute; text-align: justify; bottom: 165px; left: 35px; width: 390px; z-Index: 1000;">
	<div class="info_box">Access the Menu to configure the <span class="info_good">Desktop Notification</span> alert.</div>
	<div style="margin-top: 5px;"><img src="../pics/icons/arrow_down_curve.png" width="32" height="32" border="0" alt=""></div>
</div>
<div id="reconnect_notice" class="info_warning" style="display: none; position: absolute; z-Index: 1000; padding: 25px; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);">
	<div id="reconnect_status">Operator console disconnected.  Reconnecting... <img src="../themes/<?php echo $theme ?>/loading_fb.gif" border="0" alt="" style="padding: 2px; background: #FFFFFF;" class="round"></div>
	<div id="reconnect_attempt" style="margin-top: 15px;">&nbsp;</div>
</div>

<div id="console_notice" class="info_error" style="display: none; position: absolute; padding: 25px; z-Index: 1000; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);">
	<div>Operator console is opened at another browser.  For security, this session has ended.</div>
</div>

<div id="div_geomap" class="info_content" style="display: none; position: absolute; z-Index: 1000;">
	<div class="info_error" style="text-align: center; cursor: pointer;" onClick="$('#div_geomap').hide();">close</div>
	<div id="info_maps_"></div>
</div>

<div style="display: none; position: absolute; top: 0px; left: 0px; padding: 45px; z-Index: 102; cursor: pointer; box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);" class="info_box" id="div_last_response" onClick="toggle_last_response(0)">
	<div style="font-weight: bold; font-size: 14px;" id="last_response_timer_vname"></div>
	<div style="margin-top: 5px;">Last response sent <span id="last_response_timer"></span>&nbsp; minutes ago</div>
</div>

<div id="div_debug" style="display: none; position: absolute; bottom: 0px; right: 0px; width: 400px; height: 200px; overflow: auto; z-Index: 1000; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);" class="info_content">
	<form>
	<div style="display: inline-block;">Error Message Log Details &nbsp; <button type="button" style="font-size: 10px;" onClick="$('#div_debug').hide();">close</button></div>
	<div style="margin-top: 5px;"><textarea style="width: 96%; height: 150px; font-size: 10px; padding: 2px; border: 0px; resize: none;" id="error_message" readonly></textarea></div>
	</form>
</div>
<?php include_once( "$CONF[DOCUMENT_ROOT]/addons/emoticons/emoticons.php" ) ; ?>
<?php if ( $VARS_INI_UPLOAD && $can_upload && is_file( "$CONF[DOCUMENT_ROOT]/addons/file_attach/file_attach.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/file_attach/file_attach.php" ) ; } ?>
<?php if ( $proactions_string ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/inc_operator.php" ) ; } ?>
<?php if ( is_file( "./inc_extra.php" ) && !$mapp ) { include_once( "./inc_extra.php" ) ; } ?>

<div id="div_mapp_spinner" style="display: none; position: absolute; top: 0px; left: 0px; z-Index: 1000;" onClick="spinner_check()"><span class="info_neutral" style="padding: 25px; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);"><img src="../pics/spinner.gif" width="16" height="16" border="0" alt="" class="round" title="connecting..." alt="connecting..." id="img_mapp_spinner"> connecting</span></div>
<div id="div_alerts_wrapper" style="display: none; position: absolute; bottom: 0px; right: 0px; width: 550px; height: 515px; z-Index: 601; border-bottom-left-radius: 0px 0px; border-bottom-right-radius: 0px 0px; border-top-right-radius: 0px 0px;" class="info_content">
	<div style="padding: 15px;">
		<div><span class="info_error" onClick="launch_alerts()" style="text-align: center; cursor: pointer;"><img src="../themes/<?php echo $theme ?>/close_extra.png" width="16" height="16" border="0"> Close</span></div>
		<div style="margin-top: 25px;"><img src="../themes/initiate/notice.png" width="18" height="18" border="0" alt="Alert" title="Alert"> Your declined (and missed) chats in recent 24 hours:</div>
	</div>
	<div id="div_alerts" style="padding: 15px; padding-top: 0px; height: 200px; overflow: auto; word-break: break-word; word-wrap: break-word;"><img src="../themes/<?php echo $theme ?>/loading_fb.gif" border="0" alt=""></div>
</div><input type="hidden" name="addon_chrome_extension_id" id="addon_chrome_extension_id" value="">
<div id="div_sound_interaction" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background: url( ../themes/initiate/bg_trans_white.png ) repeat; z-Index: 1000;"></div>
<div id="div_sound_interaction_message" class="info_content" style="display: none; position: absolute; top: 0px; left: 0px; width: 310px; padding: 25px; text-align: center; z-Index: 1001;">
	<div><img src="../themes/initiate/sound.png" width="64" height="64" border="0" alt="" style=""></div>
	<div style="margin-top: 15px;">
		<big><b>Sound Check</b></big> - Some web browsers require user interaction on the page to play sound. Click the 'Ok' button to ensure sound is enabled for this session.
	</div>
	<div style="display: none; margin-top: 15px;" id="div_sound_interaction_message_off" class="info_box"><img src="../themes/<?php echo $theme ?>/sound_off.png" width="16" height="16" border="0" alt=""> This is only an interaction confirmation.  Your operator chat console sound setting will remain Off.</div>
	<div style="margin-top: 15px;"><button type="button" class="input_button" style="padding: 10px;" onClick="play_sound( 0, 'new_text', 'new_text_sound_check' );close_sound_interaction();" id="div_sound_interaction_message_btn">Ok</button></div>
</div>
<?php if ( $addon_voice_chat ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/voice_chat/inc_op.php" ) ; } ?><video width="1" height="1" id="video" style="display: none;"></video>
<div id="div_prevent_click" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background: url( ../themes/initiate/bg_trans_white.png ) repeat; z-Index: 1000;"></div>
<div id="div_menu_op" style="display: none; position: absolute; bottom: 55px; left: -250px; width: 200px; z-index: 104;" class="info_content">
	<div id="div_menu_op_inner" style="padding-left: 25px;">
		<div style="padding-top: 5px; padding-bottom: 15px;"><span class="info_error" style="cursor: pointer;" onClick="toggle_menu_op()">Close</span></div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('profile')" id="menu_op_profile">Profile</div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('themes')" id="menu_op_themes">Themes</div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('sound')" id="menu_op_sound">Sound</div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('dn')" id="menu_op_dn">Desktop Notification</div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('trans')" id="menu_op_trans">Transcripts</div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('reports')" id="menu_op_reports">Reports</div>
		<?php if ( $opinfo["view_chats"] ): ?><div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('chats')" id="menu_op_chats">Active Chats</div><?php endif ; ?>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('activity')" id="menu_op_activity">Online Activity</div>
		<div class="chat_info_td round" style="margin-bottom: 5px; cursor: pointer;" onClick="launch_settings('settings')" id="menu_op_settings">Settings</div>
	</div>
</div>
<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/custom_code/operator_console.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/custom_code/operator_console.php" ) ; } ?>
</body>
</html>
<?php database_mysql_close( $dbh ) ; ?>
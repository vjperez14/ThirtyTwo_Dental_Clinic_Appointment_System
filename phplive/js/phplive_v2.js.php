<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	include_once( "../web/config.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;
	$datauri = ( isset( $VARS_CHATICON_DATAURI ) && $VARS_CHATICON_DATAURI ) ? 1 : 0 ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) || $datauri ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; $datauri = 1 ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	$query = Util_Format_Sanatize( Util_Format_GetVar( "q" ), "" ) ;
	if ( !$query ) { $query = Util_Format_Sanatize( Util_Format_GetVar( "v" ), "" ) ; }
	$lang = Util_Format_Sanatize( Util_Format_GetVar( "lang" ), "ln" ) ;
	$agent = isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : "&nbsp;" ;
	LIST( $os, $browser ) = Util_Format_GetOS( $agent ) ; LIST( $ip, $null ) = Util_IP_GetIP("") ;
	$mobile = ( $os == 5 ) ? 1 : 0 ;
	$params = Array( ) ; $params = explode( "|", $query ) ;
	$deptid = ( isset( $params[0] ) && $params[0] ) ? Util_Format_Sanatize( $params[0], "n" ) : 0 ;
	$btn = ( isset( $params[1] ) && $params[1] ) ? Util_Format_Sanatize( $params[1], "n" ) : 0 ;
	$placeholder = ( isset( $params[2] ) && $params[2] ) ? Util_Format_Sanatize( $params[2], "n" ) : 0 ;
	$text = ( isset( $params[3] ) && $params[3] ) ? Util_Format_Sanatize( rawurldecode( $params[3] ), "ln" ) : "" ;
	$placeholder2 = ( isset( $params[4] ) && $params[4] ) ? Util_Format_Sanatize( $params[4], "ln" ) : "" ;
	$base_url = $CONF["BASE_URL"] ; $preview = ( $btn == 615 ) ? 1 : 0 ;
	if ( !isset( $CONF['foot_log'] ) ) { $CONF['foot_log'] = "on" ; }
	if ( !isset( $CONF['icon_check'] ) ) { $CONF['icon_check'] = "on" ; }
	if ( !isset( $VARS_ADA_TXT ) ) { $VARS_ADA_TXT = "" ; }
	if ( isset( $VALS["padding_bottom"] ) && is_numeric( $VALS["padding_bottom"] ) ) { $VARS_CHAT_PADDING_WIDGET_BOTTOM = $VALS["padding_bottom"] ; }
	if ( isset( $VALS["border_radius"] ) && is_numeric( $VALS["border_radius"] ) ) { $VARS_CHAT_PADDING_WIDGET_RADIUS = $VALS["border_radius"] ; }
	$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
	$ping = ( isset( $VALS["ping"] ) && $VALS["ping"] ) ? unserialize( $VALS["ping"] ) : Array() ;
	if ( isset( $dept_themes[$deptid] ) ) { $theme = $dept_themes[$deptid] ; } else { $theme = $CONF["THEME"] ; }
	if ( isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }
	if ( !$preview && is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/iframe_bg.gif" ) ) { $VARS_IFRAME_BACKGROUND = "$theme/iframe_bg.gif" ; }
	/***************************************/
	// HTML Code Mapper Addon
	$code_map_orig_deptid = $deptid ;
	if ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["code_maps"] ) && is_file( "$CONF[DOCUMENT_ROOT]/addons/code_mapper/code_mapper.php" ) )
	{ include_once( "$CONF[DOCUMENT_ROOT]/addons/code_mapper/inc_code_mapper.php" ) ; }
	/***************************************/
	$initiate = ( isset( $VALS["auto_initiate"] ) && $VALS["auto_initiate"] ) ? unserialize( html_entity_decode( $VALS["auto_initiate"] ) ) : Array( ) ;
	$embed_win_sizes = ( isset( $VALS["embed_win_sizes"] ) && $VALS["embed_win_sizes"] ) ? unserialize( $VALS["embed_win_sizes"] ) : Array() ;
	if ( isset( $embed_win_sizes[0] ) ) { $VARS_CHAT_WIDTH_WIDGET = $embed_win_sizes[0]["width"] ; $VARS_CHAT_HEIGHT_WIDGET = $embed_win_sizes[0]["height"] ; }
	$automatic_invite_pos = ( isset( $initiate["pos"] ) ) ? $initiate["pos"] : 1 ;
	if ( !is_numeric( $automatic_invite_pos ) || ( $automatic_invite_pos == 1 ) || ( $automatic_invite_pos > 4 ) ) { $automatic_invite_show = "left,3em" ; $automatic_invite_start = "top: 40%; left: -800px;" ; }
	else if ( $automatic_invite_pos == 2 ) { $automatic_invite_show = "right,3em" ; $automatic_invite_start = "top: 40%; right: -800px;" ; }
	else if ( $automatic_invite_pos == 3 ) { $automatic_invite_show = "bottom,3em" ; $automatic_invite_start = "bottom: -800px; left: 3em;" ; }
	else { $automatic_invite_show = "bottom,3em" ; $automatic_invite_start = "bottom: -800px; right: 3em;" ; }
	$online = ( isset( $VALS['ONLINE'] ) && $VALS['ONLINE'] ) ? unserialize( $VALS['ONLINE'] ) : Array( ) ;
	if ( !isset( $online[0] ) ) { $online[0] = "embed" ; }
	if ( !isset( $online[$deptid] ) ) { $online[$deptid] = $online[0] ; }
	$offline = ( isset( $VALS['OFFLINE'] ) && $VALS['OFFLINE'] ) ? unserialize( $VALS['OFFLINE'] ) : Array( ) ;
	if ( !isset( $offline[0] ) ) { $offline[0] = "embed" ; }
	if ( !isset( $offline[$deptid] ) ) { $offline[$deptid] = $offline[0] ; }
	$redirect_url = ( isset( $offline[$deptid] ) && !preg_match( "/^(icon|hide|embed|tab)$/", $offline[$deptid] ) ) ? $offline[$deptid] : "" ;
	$icon_hide = ( isset( $offline[$deptid] ) && preg_match( "/^(hide)$/", $offline[$deptid] ) ) ? 1 : 0 ;
	$embed_online = ( isset( $online[$deptid] ) && preg_match( "/^(embed)$/", $online[$deptid] ) ) ? 1 : 0 ;
	$embed_offline = ( isset( $offline[$deptid] ) && preg_match( "/^(embed)$/", $offline[$deptid] ) ) ? 1 : 0 ;
	$tabbed_online = ( isset( $online[$deptid] ) && preg_match( "/^(tab)$/", $online[$deptid] ) ) ? 1 : 0 ;
	$tabbed_offline = ( isset( $online[$deptid] ) && preg_match( "/^(tab)$/", $offline[$deptid] ) ) ? 1 : 0 ;
	$mobile_newwin = ( isset( $VALS["MOBILE_NEWWIN"] ) && is_numeric( $VALS["MOBILE_NEWWIN"] ) ) ? intval( $VALS["MOBILE_NEWWIN"] ) : 0 ;
	$embed_pos = ( !isset( $VALS["EMBED_POS"] ) || ( isset( $VALS["EMBED_POS"] ) && ( $VALS["EMBED_POS"] != "left" ) ) ) ? "right" : "left" ;
	if ( !isset( $VALS["EXCLUDE"] ) ) { $VALS["EXCLUDE"] = "" ; }
	$exclude_array = explode( ",", $VALS["EXCLUDE"] ) ; $exclude_process = 0 ; $exclude_string = "" ;
	for ( $c = 0; $c < count( $exclude_array ); ++$c ) { if ( $exclude_array[$c] ) { $exclude_string .= "($exclude_array[$c])|" ; } }
	if ( $exclude_string ) { $exclude_process = 1 ; $exclude_string = substr_replace( $exclude_string, "", -1 ) ; }
	else { $exclude_string = "place-holder_text" ; }
	/***************************************/
	// ProAction Addon
	$addon_proaction_js_init = "" ; $addon_proaction_js_priority = "" ; $addon_proaction_js_settings = "" ; $addon_proaction_js_pics = "\"".Util_Upload_GetLogo( "profile", 0 )."\"," ;
	if ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["proaction"] ) && is_file( "$CONF[DOCUMENT_ROOT]/addons/proaction/proaction.php" ) )
	{ include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/inc_proaction.php" ) ; } $addon_proaction_js_pics = rtrim( $addon_proaction_js_pics, ',' ) ;
	/***************************************/
	$screenshots = ( isset( $VALS["SCREENSHOTS"] ) && $VALS["SCREENSHOTS"] ) ? unserialize( $VALS["SCREENSHOTS"] ) : Array() ;
	$screenshot_found = 0 ;
	foreach ( $screenshots as $this_deptid => $value ) { if ( $value ) { $screenshot_found = 1 ; } }
	$addon_screenshot = ( $screenshot_found && is_file( "$CONF[DOCUMENT_ROOT]/addons/screenshot/js/html2canvas.min.js" ) ) ? 1 : 0 ;
	$initiate_array = ( isset( $VALS["auto_initiate"] ) && $VALS["auto_initiate"] ) ? unserialize( html_entity_decode( $VALS["auto_initiate"] ) ) : Array( ) ;
	$initiate_duration = ( isset( $initiate_array["duration"] ) && $initiate_array["duration"] ) ? $initiate_array["duration"] : 0 ;
	$exclude_string_invite = "" ;
	if ( isset( $initiate_array["exclude"] ) && $initiate_array["exclude"] )
	{
		$exclude_array = explode( ",", $initiate_array["exclude"] ) ;
		for ( $c = 0; $c < count( $exclude_array ); ++$c ) { $exclude_string_invite .= "($exclude_array[$c])|" ; }
		if ( $exclude_string_invite ) { $exclude_string_invite = substr_replace( $exclude_string_invite, "", -1 ) ; }
	}
	$initiate_doit = ( !$initiate_duration || !isset( $initiate_array["andor"] ) || !is_numeric( $initiate_array["andor"] ) || ( isset( $initiate_array["andor"] ) > 2 ) ) ? 0 : 1 ;
	$svg_icons = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["svg_icons"] ) && $VALS_ADDONS["svg_icons"] ) ? unserialize( base64_decode( $VALS_ADDONS["svg_icons"] ) ) : Array() ;
	/***************************************/
	$svg_icons_online = Array() ;
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["online"] ) )
	{
		if ( isset( $svg_icons[$deptid]["online"][5] ) && preg_match( "/^<svg /i", $svg_icons[$deptid]["online"][5] ) )
			$svg_icons_online = $svg_icons[$deptid]["online"] ;
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && preg_match( "/^<svg /i", $svg_icons[0]["online"][5] ) )
		$svg_icons_online = $svg_icons[0]["online"] ;

	$svg_icons_offline = Array() ;
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["offline"] ) )
	{
		if ( isset( $svg_icons[$deptid]["offline"][5] ) && preg_match( "/^<svg /i", $svg_icons[$deptid]["offline"][5] ) )
			$svg_icons_offline = $svg_icons[$deptid]["offline"] ;
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && preg_match( "/^<svg /i", $svg_icons[0]["offline"][5] ) )
		$svg_icons_offline = $svg_icons[0]["offline"] ;
	/***************************************/

	/***************************************/
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["online"] ) )
	{
		if ( isset( $svg_icons[$deptid]["online"][5] ) && preg_match( "/^<span /i", $svg_icons[$deptid]["online"][5] ) )
			$svg_icons_online = $svg_icons[$deptid]["online"] ;
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && preg_match( "/^<span /i", $svg_icons[0]["online"][5] ) )
		$svg_icons_online = $svg_icons[0]["online"] ;

	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["offline"] ) )
	{
		if ( isset( $svg_icons[$deptid]["offline"][5] ) && preg_match( "/^<span /i", $svg_icons[$deptid]["offline"][5] ) )
			$svg_icons_offline = $svg_icons[$deptid]["offline"] ;
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && preg_match( "/^<span /i", $svg_icons[0]["offline"][5] ) )
		$svg_icons_offline = $svg_icons[0]["offline"] ;
	/***************************************/
	if ( $datauri )
	{
		$widget_close_image = Util_Upload_Output(0, 0, "$CONF[DOCUMENT_ROOT]/themes/initiate/close_box.png", "image/png" ) ;
		$widget_bg_image = Util_Upload_Output(0, 0, "$CONF[DOCUMENT_ROOT]/themes/initiate/bg_trans.png", "image/png" ) ;
		$blank_space_image = Util_Upload_Output(0, 0, "$CONF[DOCUMENT_ROOT]/pics/space.gif", "image/gif" ) ;
		$embed_loading_image = Util_Upload_Output(0, 0, "$CONF[DOCUMENT_ROOT]/themes/initiate/loading_embed.gif", "image/png" ) ;
		$VARS_IFRAME_BACKGROUND = Util_Upload_Output(0, 0, "$CONF[DOCUMENT_ROOT]/themes/$VARS_IFRAME_BACKGROUND", "image/gif" ) ;
	}
	else
	{
		$widget_close_image = "$base_url/themes/initiate/close_box.png" ;
		$widget_bg_image = "$base_url/themes/initiate/bg_trans.png" ;
		$blank_space_image = "$base_url/pics/space.png" ;
		$embed_loading_image = "$base_url/themes/initiate/loading_embed.gif" ;
		$VARS_IFRAME_BACKGROUND = "$base_url/themes/$VARS_IFRAME_BACKGROUND" ;
	} $initiate_chat_image = Util_Upload_GetInitiate( 0 ) ; $VARS_IFRAME_BACKGROUND = "background: url( $VARS_IFRAME_BACKGROUND ) repeat-x;" ;
	if ( $text ) { $icon_online_image = $icon_offline_image = rawurlencode( $text ) ; $icon_online_svg_image = $icon_offline_svg_image = "" ; }
	else
	{
		$icon_online_image = Util_Upload_GetChatIcon( "icon_online", $deptid ) ;
		$icon_offline_image = Util_Upload_GetChatIcon( "icon_offline", $deptid ) ;
		$icon_online_svg_image = ( isset( $svg_icons_online[0] ) ) ? preg_replace( "/\"/", "'", $svg_icons_online[5] ) : "" ;
		$icon_offline_svg_image = ( isset( $svg_icons_offline[0] ) ) ? preg_replace( "/\"/", "'", $svg_icons_offline[5] ) : "" ;
	} $icon_online_svg_on = ( $icon_online_svg_image ) ? 1 : 0 ; $icon_offline_svg_on = ( $icon_offline_svg_image ) ? 1 : 0 ;
	$alttext_array = ( isset( $VALS["alttext"] ) && $VALS["alttext"] ) ? unserialize( $VALS["alttext"] ) : Array() ;
	$alttext_array_dept = Array() ; $alttext_using_global = 0 ;
	if ( isset( $alttext_array[$deptid] ) )
		$alttext_array_dept = $alttext_array[$deptid] ;
	else if ( $deptid && isset( $alttext_array[0] ) )
	{
		$alttext_using_global = 1 ;
		$alttext_array_dept = $alttext_array[0] ;
	} array_walk( $alttext_array_dept, "Util_Format_base64_decode_array" ) ;
	Header( "Content-type: application/javascript" ) ;
?>
var __cfRLUnblockHandlers = 1 ;
if ( typeof( phplive_base_url ) == "undefined" ) { if ( typeof( phplive_utf8_encode ) == "undefined" ){ function phplive_utf8_encode(r){if(null===r||"undefined"==typeof r)return"";var e,n,t=r+"",a="",o=0;e=n=0,o=t.length;for(var f=0;o>f;f++){var i=t.charCodeAt(f),l=null;if(128>i)n++;else if(i>127&&2048>i)l=String.fromCharCode(i>>6|192,63&i|128);else if(55296!=(63488&i))l=String.fromCharCode(i>>12|224,i>>6&63|128,63&i|128);else{if(55296!=(64512&i))throw new RangeError("Unmatched trail surrogate at "+f);var d=t.charCodeAt(++f);if(56320!=(64512&d))throw new RangeError("Unmatched lead surrogate at "+(f-1));i=((1023&i)<<10)+(1023&d)+65536,l=String.fromCharCode(i>>18|240,i>>12&63|128,i>>6&63|128,63&i|128)}null!==l&&(n>e&&(a+=t.slice(e,n)),a+=l,e=n=f+1)}return n>e&&(a+=t.slice(e,o)),a} function phplive_md5(n){var r,t,u,e,o,f,c,i,a,h,v=function(n,r){return n<<r|n>>>32-r},g=function(n,r){var t,u,e,o,f;return e=2147483648&n,o=2147483648&r,t=1073741824&n,u=1073741824&r,f=(1073741823&n)+(1073741823&r),t&u?2147483648^f^e^o:t|u?1073741824&f?3221225472^f^e^o:1073741824^f^e^o:f^e^o},s=function(n,r,t){return n&r|~n&t},d=function(n,r,t){return n&t|r&~t},l=function(n,r,t){return n^r^t},w=function(n,r,t){return r^(n|~t)},A=function(n,r,t,u,e,o,f){return n=g(n,g(g(s(r,t,u),e),f)),g(v(n,o),r)},C=function(n,r,t,u,e,o,f){return n=g(n,g(g(d(r,t,u),e),f)),g(v(n,o),r)},b=function(n,r,t,u,e,o,f){return n=g(n,g(g(l(r,t,u),e),f)),g(v(n,o),r)},m=function(n,r,t,u,e,o,f){return n=g(n,g(g(w(r,t,u),e),f)),g(v(n,o),r)},y=function(n){for(var r,t=n.length,u=t+8,e=(u-u%64)/64,o=16*(e+1),f=new Array(o-1),c=0,i=0;t>i;)r=(i-i%4)/4,c=i%4*8,f[r]=f[r]|n.charCodeAt(i)<<c,i++;return r=(i-i%4)/4,c=i%4*8,f[r]=f[r]|128<<c,f[o-2]=t<<3,f[o-1]=t>>>29,f},L=function(n){var r,t,u="",e="";for(t=0;3>=t;t++)r=n>>>8*t&255,e="0"+r.toString(16),u+=e.substr(e.length-2,2);return u},S=[],_=7,j=12,k=17,p=22,q=5,x=9,z=14,B=20,D=4,E=11,F=16,G=23,H=6,I=10,J=15,K=21;for(n=this.phplive_utf8_encode(n),S=y(n),c=1732584193,i=4023233417,a=2562383102,h=271733878,r=S.length,t=0;r>t;t+=16)u=c,e=i,o=a,f=h,c=A(c,i,a,h,S[t+0],_,3614090360),h=A(h,c,i,a,S[t+1],j,3905402710),a=A(a,h,c,i,S[t+2],k,606105819),i=A(i,a,h,c,S[t+3],p,3250441966),c=A(c,i,a,h,S[t+4],_,4118548399),h=A(h,c,i,a,S[t+5],j,1200080426),a=A(a,h,c,i,S[t+6],k,2821735955),i=A(i,a,h,c,S[t+7],p,4249261313),c=A(c,i,a,h,S[t+8],_,1770035416),h=A(h,c,i,a,S[t+9],j,2336552879),a=A(a,h,c,i,S[t+10],k,4294925233),i=A(i,a,h,c,S[t+11],p,2304563134),c=A(c,i,a,h,S[t+12],_,1804603682),h=A(h,c,i,a,S[t+13],j,4254626195),a=A(a,h,c,i,S[t+14],k,2792965006),i=A(i,a,h,c,S[t+15],p,1236535329),c=C(c,i,a,h,S[t+1],q,4129170786),h=C(h,c,i,a,S[t+6],x,3225465664),a=C(a,h,c,i,S[t+11],z,643717713),i=C(i,a,h,c,S[t+0],B,3921069994),c=C(c,i,a,h,S[t+5],q,3593408605),h=C(h,c,i,a,S[t+10],x,38016083),a=C(a,h,c,i,S[t+15],z,3634488961),i=C(i,a,h,c,S[t+4],B,3889429448),c=C(c,i,a,h,S[t+9],q,568446438),h=C(h,c,i,a,S[t+14],x,3275163606),a=C(a,h,c,i,S[t+3],z,4107603335),i=C(i,a,h,c,S[t+8],B,1163531501),c=C(c,i,a,h,S[t+13],q,2850285829),h=C(h,c,i,a,S[t+2],x,4243563512),a=C(a,h,c,i,S[t+7],z,1735328473),i=C(i,a,h,c,S[t+12],B,2368359562),c=b(c,i,a,h,S[t+5],D,4294588738),h=b(h,c,i,a,S[t+8],E,2272392833),a=b(a,h,c,i,S[t+11],F,1839030562),i=b(i,a,h,c,S[t+14],G,4259657740),c=b(c,i,a,h,S[t+1],D,2763975236),h=b(h,c,i,a,S[t+4],E,1272893353),a=b(a,h,c,i,S[t+7],F,4139469664),i=b(i,a,h,c,S[t+10],G,3200236656),c=b(c,i,a,h,S[t+13],D,681279174),h=b(h,c,i,a,S[t+0],E,3936430074),a=b(a,h,c,i,S[t+3],F,3572445317),i=b(i,a,h,c,S[t+6],G,76029189),c=b(c,i,a,h,S[t+9],D,3654602809),h=b(h,c,i,a,S[t+12],E,3873151461),a=b(a,h,c,i,S[t+15],F,530742520),i=b(i,a,h,c,S[t+2],G,3299628645),c=m(c,i,a,h,S[t+0],H,4096336452),h=m(h,c,i,a,S[t+7],I,1126891415),a=m(a,h,c,i,S[t+14],J,2878612391),i=m(i,a,h,c,S[t+5],K,4237533241),c=m(c,i,a,h,S[t+12],H,1700485571),h=m(h,c,i,a,S[t+3],I,2399980690),a=m(a,h,c,i,S[t+10],J,4293915773),i=m(i,a,h,c,S[t+1],K,2240044497),c=m(c,i,a,h,S[t+8],H,1873313359),h=m(h,c,i,a,S[t+15],I,4264355552),a=m(a,h,c,i,S[t+6],J,2734768916),i=m(i,a,h,c,S[t+13],K,1309151649),c=m(c,i,a,h,S[t+4],H,4149444226),h=m(h,c,i,a,S[t+11],I,3174756917),a=m(a,h,c,i,S[t+2],J,718787259),i=m(i,a,h,c,S[t+9],K,3951481745),c=g(c,u),i=g(i,e),a=g(a,o),h=g(h,f);var M=L(c)+L(i)+L(a)+L(h);return M.toLowerCase( )} }
var phplive_base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(a){var d="",c=0;for(a=phplive_base64._utf8_encode(a);c<a.length;){var b=a.charCodeAt(c++);var e=a.charCodeAt(c++);var f=a.charCodeAt(c++);var g=b>>2;b=(b&3)<<4|e>>4;var h=(e&15)<<2|f>>6;var k=f&63;isNaN(e)?h=k=64:isNaN(f)&&(k=64);d=d+this._keyStr.charAt(g)+this._keyStr.charAt(b)+this._keyStr.charAt(h)+this._keyStr.charAt(k)}return d},decode:function(a){var d="",c=0;for(a=a.replace(/[^A-Za-z0-9\+\/\=]/g,"");c<a.length;){var b=this._keyStr.indexOf(a.charAt(c++));var e=this._keyStr.indexOf(a.charAt(c++));var f=this._keyStr.indexOf(a.charAt(c++));var g=this._keyStr.indexOf(a.charAt(c++));b=b<<2|e>>4;e=(e&15)<<4|f>>2;var h=(f&3)<<6|g;d+=String.fromCharCode(b);64!=f&&(d+=String.fromCharCode(e));64!=g&&(d+=String.fromCharCode(h))}return d=phplive_base64._utf8_decode(d)},_utf8_encode:function(a){a=a.replace(/\r\n/g,"\n");for(var d="",c=0;c<a.length;c++){var b=a.charCodeAt(c);128>b?d+=String.fromCharCode(b):(127<b&&2048>b?d+=String.fromCharCode(b>>6|192):(d+=String.fromCharCode(b>>12|224),d+=String.fromCharCode(b>>6&63|128)),d+=String.fromCharCode(b&63|128))}return d},_utf8_decode:function(a){var d="",c=0;for(c1=c2=0;c<a.length;){var b=a.charCodeAt(c);128>b?(d+=String.fromCharCode(b),c++):191<b&&224>b?(c2=a.charCodeAt(c+1),d+=String.fromCharCode((b&31)<<6|c2&63),c+=2):(c2=a.charCodeAt(c+1),c3=a.charCodeAt(c+2),d+=String.fromCharCode((b&15)<<12|(c2&63)<<6|c3&63),c+=3)}return d}};
var phplive_base_url_orig = "<?php echo $base_url ?>" ;
var phplive_base_url = phplive_base_url_orig ;
var phplive_proto = ( location.href.indexOf("https") == 0 ) ? 1 : 0 ; // to avoid JS proto error, use page proto for areas needing to access the JS objects
if ( !phplive_proto && ( phplive_base_url.match( /http/i ) == null ) ) { phplive_base_url = "http:"+phplive_base_url_orig ; }
else if ( phplive_proto && ( phplive_base_url.match( /https/i ) == null ) ) { phplive_base_url = "https:"+phplive_base_url_orig ; }
var phplive_proto_full = window.location.protocol ;
var phplive_origin_page = phplive_proto_full + "//" + window.location.hostname ;
var phplive_origin_port = location.port ; if ( phplive_origin_port ) { phplive_origin_page = phplive_origin_page+":"+phplive_origin_port ; }
phplive_origin_page = encodeURIComponent( phplive_origin_page.replace("http", "hphp") ) ;
var phplive_regex_replace = new RegExp( phplive_base_url_orig, "g" ) ; var undeefined ;
var phplive_browser = navigator.appVersion ; var phplive_mime_types = "" ;
var phplive_display_width = screen.availWidth ; var phplive_display_height = screen.availHeight ; var phplive_display_color = screen.colorDepth ; var phplive_timezone = new Date( ).getTimezoneOffset( ) ;
if ( navigator.mimeTypes.length > 0 ) { for (var x=0; x < navigator.mimeTypes.length; x++) { phplive_mime_types += navigator.mimeTypes[x].description ; } }
var phplive_browser_gl = ( typeof( document.createElement("canvas").getContext ) != "undefined" ) ? document.createElement("canvas").getContext("webgl") : new Object ; var phplive_browser_gl_string = "" ; for ( var phplive_browser_gl in phplive_browser_gl ) { phplive_browser_gl_string += phplive_browser_gl+phplive_browser_gl[phplive_browser_gl] ; }
var phplive_browser_token = phplive_md5( phplive_display_width+phplive_display_height+phplive_display_color+phplive_timezone+phplive_browser+phplive_mime_types+phplive_browser_gl_string ) ;
var phplive_mobile = 0 ; var phplive_userAgent = navigator.userAgent || navigator.vendor || window.opera ; var phplive_ipad = 0 ;
// remove mobile indication for iPad for consistency because some iPad web browsers does not have iPad string
if ( phplive_userAgent.match( /iPad/i ) || phplive_userAgent.match( /iPhone/i ) || phplive_userAgent.match( /iPod/i ) )
{ if ( phplive_userAgent.match( /iPad/i ) ) { phplive_mobile = 0 ; phplive_ipad = 0 ; } else { phplive_mobile = 1 ; } }
else if ( phplive_userAgent.match( /Android/i ) ) { phplive_mobile = 2 ; }
var phplive_peer_support = 0 ; var webrtc_supported = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || window.RTCPeerConnection ; if ( phplive_proto && !phplive_mobile && navigator.mediaDevices && webrtc_supported && ( window.navigator.userAgent.indexOf("Edge") < 0 ) ) { phplive_peer_support = 1 ; }
//console.info( phplive_proto, !phplive_mobile, navigator.mediaDevices, webrtc_supported, ( window.navigator.userAgent.indexOf("Edge") < 0 ) ) ;
//console.info( phplive_proto && !phplive_mobile && navigator.mediaDevices && webrtc_supported && ( window.navigator.userAgent.indexOf("Edge") < 0 ) ) ;
var phplive_stat_refer = phplive_base64.encode( document.referrer.replace("http", "hphp") ) ;
var phplive_resolution = encodeURI( screen.width + " x " + screen.height ) ;
var phplive_query_extra = "&r="+phplive_stat_refer+"&resolution="+phplive_resolution ;
var phplive_fetch_status_url = phplive_base_url+"/ajax/status.php?action=js&token="+phplive_browser_token+"&deptid={deptid}&pst=1" ;
var phplive_fetch_footprints_url = phplive_base_url+"/ajax/footprints.php" ;
var phplive_preview_query = ( <?php echo $preview ?> ) ? "&preview=1" : "" ;
var phplive_request_url_query = "token="+phplive_browser_token+"&pgo="+phplive_origin_page+phplive_preview_query ;
var phplive_request_url = phplive_base_url+"/phplive.php?d={deptid}{onpage}&prs="+phplive_peer_support+"&"+phplive_request_url_query ;
var phplive_si_phplive_fetch_status = parseInt( <?php echo ( isset( $ping["status"] ) && is_numeric( $ping["status"] ) && ( $ping["status"] >= 10 ) ) ? $ping["status"] : $VARS_JS_CHATICON_CHECK ?> ) ;
var phplive_si_phplive_fetch_footprints = parseInt( <?php echo ( isset( $ping["foot"] ) && is_numeric( $ping["foot"] ) && ( $ping["foot"] >= 20 ) ) ? $ping["foot"] : $VARS_JS_FOOTPRINT_CHECK ?> ) ;
var phplive_si_fetch_status = new Object ; var phplive_st_fetch_footprints ;
var phplive_depts = new Object ; var phplive_btns = new Object ; var phplive_chat_icons = new Object ; var phplive_globals = new Object ;
var phplive_session_support = ( typeof( Storage ) !== "undefined" ) ? 1 : 0 ; var phplive_compat = ( [].filter ) ? 1 : 0 ;
phplive_globals["icon_initiate"] = "<?php echo $initiate_chat_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
phplive_globals["icon_initiate_close"] = "<?php echo $widget_close_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
phplive_globals["icon_initiate_bg"] = "<?php echo $widget_bg_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
phplive_globals["icon_embed_loading"] = "<?php echo $embed_loading_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
phplive_globals["icon_space"] = "<?php echo $blank_space_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
phplive_globals["embedinvite"] = <?php echo ( isset( $VALS["EMBED_OPINVITE_AUTO"] ) && ( $VALS["EMBED_OPINVITE_AUTO"] == "on" ) ) ? 1 : 0 ?> ;
phplive_globals["iframe_sandbox_attributes"] = "<?php echo ( isset( $VARS_MISC_IFRAME_SANDBOX_ATTRIBUTES ) && $VARS_MISC_IFRAME_SANDBOX_ATTRIBUTES ) ? "sandbox='$VARS_MISC_IFRAME_SANDBOX_ATTRIBUTES'" : "" ?>" ;
phplive_globals["ajax_key"] = "<?php echo md5( $agent.$CONF["SALT"] ) ; ?>" ;
phplive_globals["exclude_process"] = <?php echo $exclude_process ?> ;
phplive_globals["exclude_string"] = "<?php echo $exclude_string ?>" ;
phplive_globals["exclude_string_invite"] = "<?php echo $exclude_string_invite ?>" ;
phplive_globals["initiate_duration"] = <?php echo $initiate_duration ?> ;
phplive_globals["newwin_width"] = <?php echo $VARS_CHAT_WIDTH ?> ;
phplive_globals["newwin_height"] = <?php echo $VARS_CHAT_HEIGHT ?> ;
phplive_globals["mobile_newwin"] = <?php echo $mobile_newwin ?> ;
phplive_globals["embed_load_speed"] = 400 ; // milliseconds
phplive_globals["embed_pos"] = "<?php echo $embed_pos ?>" ;
phplive_globals["embed_padding"] = ( phplive_mobile ) ? "0px" : "<?php echo $VARS_CHAT_PADDING_WIDGET ?>px" ;
phplive_globals["embed_padding_svg"] = "<?php echo $VARS_CHAT_PADDING_SVG_ICON ?>px" ;
phplive_globals["embed_padding_bottom"] = ( phplive_mobile ) ? "0px" : "<?php echo $VARS_CHAT_PADDING_WIDGET_BOTTOM ?>px" ;
phplive_globals["embed_padding_radius"] = ( phplive_mobile ) ? "0px" : "<?php echo $VARS_CHAT_PADDING_WIDGET_RADIUS ?>px" ;
phplive_globals["embed_box_shadow"] = "box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.3);" ; phplive_globals["embed_loaded"] = false ;
phplive_globals["invite_pos"] = <?php echo $automatic_invite_pos ?> ;
phplive_globals["invite_start"] = "<?php echo $automatic_invite_start ?>" ;
phplive_globals["invite_show"] = "<?php echo $automatic_invite_show ?>" ;
phplive_globals["invite_dur"] = <?php echo $initiate_duration ?> ;
phplive_globals["invite_exin"] = "<?php echo ( isset( $initiate_array["exin"] ) ) ? $initiate_array["exin"] : "" ; ?>" ;
phplive_globals["invite_andor"] = <?php echo ( isset( $initiate_array["andor"] ) && is_numeric( $initiate_array["andor"] ) ) ? $initiate_array["andor"] : 0 ; ?> ;
phplive_globals["invite_exin_pages"] = "<?php echo $exclude_string_invite ; ?>" ;
phplive_globals["invite_doit"] = <?php echo $initiate_doit ?> ;
phplive_globals["foot_log"] = "<?php echo $CONF["foot_log"] ?>" ;
phplive_globals["icon_check"] = "<?php echo $CONF['icon_check'] ?>" ;
phplive_globals["alt_online"] = "<?php echo isset( $alttext_array_dept["online"] ) ? $alttext_array_dept["online"] : "" ; ?>" ;
phplive_globals["alt_offline"] = "<?php echo isset( $alttext_array_dept["offline"] ) ? $alttext_array_dept["offline"] : "" ; ?>" ;
phplive_globals["alt_invite"] = "<?php echo isset( $alttext_array_dept["invite"] ) ? $alttext_array_dept["invite"] : "" ; ?>" ;
phplive_globals["alt_close"] = "<?php echo isset( $alttext_array_dept["close"] ) ? $alttext_array_dept["close"] : "" ; ?>" ;
phplive_globals["alt_emminimize"] = "<?php echo isset( $alttext_array_dept["emminimize"] ) ? $alttext_array_dept["emminimize"] : "" ; ?>" ;
phplive_globals["alt_emclose"] = "<?php echo isset( $alttext_array_dept["emclose"] ) ? $alttext_array_dept["emclose"] : "" ; ?>" ;
phplive_globals["alt_emmaximize"] = "<?php echo isset( $alttext_array_dept["emmaximize"] ) ? $alttext_array_dept["emmaximize"] : "" ; ?>" ;
phplive_globals["embed_animate"] = <?php echo ( !isset( $VALS["EMBED_ANIMATE"] ) || ( $VALS["EMBED_ANIMATE"] == "on" ) ) ? 600 : 0 ; ?> ;
phplive_globals["phplive_misc_01"] = <?php echo ( isset( $VARS_MISC_MOBILE_MAX_QUIRK_NOFIX ) && $VARS_MISC_MOBILE_MAX_QUIRK_NOFIX ) ? 1 : 0 ?> ;
phplive_globals["phplive_misc_02"] = <?php echo ( isset( $VARS_MISC_CSS_OVERRIDE ) && $VARS_MISC_CSS_OVERRIDE ) ? 1 : 0 ; ?> ;
phplive_globals["addon_screenshot"] = ( typeof( Promise ) != "undefined" ) ? <?php echo $addon_screenshot ?> : 0 ;
phplive_globals["addon_proactionid"] = 0 ; var phplive_proaction_localstorage = new Object ; var phplive_proactions_processed = new Object ; var phplive_addon_proaction_priority = new Array() ; var phplive_addon_proaction = new Object ; var phplive_addon_proaction_pics = new Array( <?php echo $addon_proaction_js_pics ?> ) ; <?php echo $addon_proaction_js_init ?> <?php echo $addon_proaction_js_settings ?> <?php echo $addon_proaction_js_priority ?> var phplive_proaction_duration_counter = 0 ;
phplive_globals["processes"] = 0 ; phplive_globals["deptid"] ;
var phplive_js_lib = document.createElement("script") ;
phplive_js_lib.type = "text/javascript" ;
phplive_js_lib.async = true ;
phplive_js_lib.src = phplive_base_url+"/js/phplive.js?<?php echo filemtime ( "$CONF[DOCUMENT_ROOT]/js/phplive.js" ) ; ?>" ;
document.getElementsByTagName("head")[0].appendChild( phplive_js_lib ) ; }
if ( typeof( phplive_depts[<?php echo $deptid ?>] ) == "undefined" ) {
phplive_depts[<?php echo $deptid ?>] = new Object ;
phplive_depts[<?php echo $deptid ?>]["redirect_url"] = "<?php echo $redirect_url ?>" ;
phplive_depts[<?php echo $deptid ?>]["icon_hide"] = <?php echo $icon_hide ?> ;
phplive_depts[<?php echo $deptid ?>]["embed_online"] = <?php echo $embed_online ?> ;
phplive_depts[<?php echo $deptid ?>]["embed_offline"] = <?php echo $embed_offline ?> ;
phplive_depts[<?php echo $deptid ?>]["tabbed_offline"] = <?php echo $tabbed_offline ?> ;
phplive_depts[<?php echo $deptid ?>]["tabbed_online"] = <?php echo $tabbed_online ?> ;
phplive_depts[<?php echo $deptid ?>]["isonline"] = -1 ;
phplive_depts[<?php echo $deptid ?>]["redirect"] = "<?php echo $redirect_url ?>" ;
phplive_depts[<?php echo $deptid ?>]["loaded"] = 0 ;
phplive_depts[<?php echo $deptid ?>]["iframe_bg"] = "<?php echo $VARS_IFRAME_BACKGROUND ?>" ;
phplive_depts[<?php echo $deptid ?>]["embed_width"] = "<?php echo isset( $embed_win_sizes[$deptid] ) ? $embed_win_sizes[$deptid]["width"] : $VARS_CHAT_WIDTH_WIDGET ?>" ;
phplive_depts[<?php echo $deptid ?>]["embed_height"] = "<?php echo isset( $embed_win_sizes[$deptid] ) ? $embed_win_sizes[$deptid]["height"] : $VARS_CHAT_HEIGHT_WIDGET ?>" ;
if ( typeof( phplive_embed_win_width ) != "undefined" ) { phplive_depts[<?php echo $deptid ?>]["embed_width"] = phplive_embed_win_width+"" ; }
if ( typeof( phplive_embed_win_height ) != "undefined" ) { phplive_depts[<?php echo $deptid ?>]["embed_height"] = phplive_embed_win_height+"" ; }
phplive_depts[<?php echo $deptid ?>]["embed_width"] = ( !phplive_depts[<?php echo $deptid ?>]["embed_width"].match( /%/ ) ) ? phplive_depts[<?php echo $deptid ?>]["embed_width"] + "px" : phplive_depts[<?php echo $deptid ?>]["embed_width"] ;
phplive_depts[<?php echo $deptid ?>]["embed_height"] = ( !phplive_depts[<?php echo $deptid ?>]["embed_height"].match( /%/ ) ) ? phplive_depts[<?php echo $deptid ?>]["embed_height"] + "px" : phplive_depts[<?php echo $deptid ?>]["embed_height"] ;
if ( phplive_mobile ) { phplive_depts[<?php echo $deptid ?>]["embed_width"] = "100%" ; phplive_depts[<?php echo $deptid ?>]["embed_height"] = "100%" ; }
var phplive_si_check_jquery_<?php echo $deptid ?> = setInterval(function( ){
if ( typeof( phplive_jquery ) != "undefined" ) {
clearInterval( phplive_si_check_jquery_<?php echo $deptid ?> ) ;
var fetch_url = phplive_fetch_status_url.replace( /{deptid}/, <?php echo $deptid ?> ) ;
if ( ( typeof( phplive_stop_chat_icon ) == "undefined" ) || !parseInt( phplive_stop_chat_icon ) ) {
phplive_fetch_status( <?php echo $deptid ?>, fetch_url ) ;
phplive_si_fetch_status[<?php echo $deptid ?>] = setInterval(function( ){
	phplive_fetch_status( <?php echo $deptid ?>, fetch_url ) ;
}, phplive_si_phplive_fetch_status * 1000 ) ; } }
}, 100 ) ; window.phplive_launch_chat_<?php echo $deptid ?> = function( ) { phplive_launch_chat( <?php echo $deptid ?>, 0 ) ; } ; }
if ( typeof( phplive_link_function ) == "undefined" ) { var phplive_link_function = window.phplive_launch_chat_<?php echo $deptid ?> ; }
if ( typeof( phplive_btns[<?php echo $btn ?>] ) == "undefined" ) {
phplive_btns[<?php echo $btn ?>] = new Object ;
phplive_btns[<?php echo $btn ?>]["deptid"] = <?php echo $deptid ?> ;
phplive_btns[<?php echo $btn ?>]["isonline"] = -1 ;
<?php if ( $datauri && !$text ): ?>
phplive_btns[<?php echo $btn ?>]["datauri"] = 1 ;
phplive_btns[<?php echo $btn ?>]["icon_online"] = ( <?php echo $icon_online_svg_on ?> && phplive_compat ) ? "<?php echo $icon_online_svg_image ?>" : "<?php echo $icon_online_image ?>" ;
phplive_btns[<?php echo $btn ?>]["icon_offline"] = ( <?php echo $icon_offline_svg_on ?> && phplive_compat ) ? "<?php echo $icon_offline_svg_image ?>" : "<?php echo $icon_offline_image ?>" ;
<?php else: ?>
phplive_btns[<?php echo $btn ?>]["datauri"] = 0 ;
phplive_btns[<?php echo $btn ?>]["icon_online"] = ( <?php echo $icon_online_svg_on ?> && phplive_compat ) ? "<?php echo $icon_online_svg_image ?>" : "<?php echo $icon_online_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
phplive_btns[<?php echo $btn ?>]["icon_offline"] = ( <?php echo $icon_offline_svg_on ?> && phplive_compat ) ? "<?php echo $icon_offline_svg_image ?>" : "<?php echo $icon_offline_image ?>".replace( phplive_regex_replace, phplive_base_url ) ;
<?php endif ; ?>
} <?php if ( $code_map_orig_deptid != $deptid ): ?>window.phplive_launch_chat_<?php echo $code_map_orig_deptid ?> = function( ) { phplive_launch_chat( <?php echo $deptid ?>, 0 ) ; } ;<?php endif ; ?>
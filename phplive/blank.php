<?php
	include_once( "./web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$url = base64_decode( Util_Format_Sanatize( Util_Format_GetVar( "url" ), "b64" ) ) ;
	$onpage = base64_decode( Util_Format_Sanatize( Util_Format_GetVar( "onpage" ), "b64" ) ) ;
	$title = base64_decode( Util_Format_Sanatize( Util_Format_GetVar( "title" ), "b64" ) ) ;
	$page_origin = Util_Format_Sanatize( rawurldecode( Util_Format_GetVar( "pgo" ) ), "url" ) ;
	$background = Util_Format_Sanatize( Util_Format_GetVar( "bg" ), "ln" ) ;
?>
<?php include_once( "./inc_doctype.php" ) ?>
<head>
<title> blank page </title>
<meta name="author" content="osicodesinc">
<meta name="mapp" content="active">
<meta name="description" content="phplive_c615">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "./inc_meta_dev.php" ) ; ?>
<script data-cfasync="false" type="text/javascript" src="./js/jquery_md5.js?<?php echo $VERSION ?>"></script>
</head>
<body style="background: <?php echo ( $background ) ? "#$background" : "transparent" ; ?>;">
<div id="div_embed_error" style="display: none; margin-top: 55px; text-align: center; background: #D9534F; border: 1px solid #D43F3A; padding: 8px; color: #FFFFFF; text-shadow: none; font-family: arial; border-radius: 5px;">Error loading chat window.  Malformed data.<br><br><center><button type="button" onClick="close_window()" id="btn_close" style="display: none;">Close</button></center></div>

<script data-cfasync="false" type="text/javascript">
<!--
	// this script is to fetch the JavaScript visitor token
	var loaded = 1 ;

	<?php
		if ( $url && preg_match( "/api_key/", $url ) && preg_match( "/^phplive_/", $url ) ):
		usleep( 250000 ) ; // to limit throttle
	?>
		var phplive_browser = navigator.appVersion ; var phplive_mime_types = "" ;
		var phplive_display_width = screen.availWidth ; var phplive_display_height = screen.availHeight ; var phplive_display_color = screen.colorDepth ; var phplive_timezone = new Date().getTimezoneOffset() ;
		if ( navigator.mimeTypes.length > 0 ) { for (var x=0; x < navigator.mimeTypes.length; x++) { phplive_mime_types += navigator.mimeTypes[x].description ; } }
		var phplive_browser_gl = ( typeof( document.createElement("canvas").getContext ) != "undefined" ) ? document.createElement("canvas").getContext("webgl") : new Object ; var phplive_browser_gl_string = "" ; for ( var phplive_browser_gl in phplive_browser_gl ) { phplive_browser_gl_string += phplive_browser_gl+phplive_browser_gl[phplive_browser_gl] ; }
		var phplive_browser_token = phplive_md5( phplive_display_width+phplive_display_height+phplive_display_color+phplive_timezone+phplive_browser+phplive_mime_types+phplive_browser_gl_string ) ;

		var win_width = screen.width ;
		var win_height = screen.height ;
		var win_dim = encodeURIComponent( win_width + " x " + win_height ) ;
		var url = "<?php echo $url ?>&deptid=<?php echo $deptid ?>&onpage=<?php echo $onpage ?>&pgo=<?php echo rawurlencode( Util_Format_URL( $page_origin ) ) ?>&title=<?php echo $title ?>&token="+phplive_browser_token+"&win_dim="+win_dim+"&<?php echo $now ?>" ; location.href = url ;
	<?php elseif ( preg_match( "/^phplive_/", $url ) ) : ?>
		document.getElementById('div_embed_error').style.display = 'block' ;
		<?php if ( preg_match( "/embed=1/", $url ) ): ?>
		document.getElementById('btn_close').style.display = 'block' ;
		<?php endif ; ?>
	<?php endif ; ?>

	function close_window()
	{
		var json_message = '{ "phplive_message": "close", "phplive_deptid": <?php echo $deptid ?> }' ;
		parent.postMessage( json_message,  "<?php echo $page_origin ?>" ) ;
	}
//-->
</script>

</body>
</html>
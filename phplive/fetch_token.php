<?php
	include_once( "./web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	$query = isset( $_SERVER["QUERY_STRING"] ) ? Util_Format_Sanatize( $_SERVER["QUERY_STRING"], "query" ) : time() ;
	$query = preg_replace( "/token=(.*?)($|&)/", "$2", $query ) ; // possible redirect loop issue fix
?>
<!doctype html>
<html><head>
<title> </title>
<meta name="description" content="phplive_c615">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "./inc_meta_dev.php" ) ; ?>
<script data-cfasync="false" type="text/javascript" src="./js/jquery_md5.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript">
<!--
	var phplive_browser = navigator.appVersion ; var phplive_mime_types = "" ;
	var phplive_display_width = screen.availWidth ; var phplive_display_height = screen.availHeight ; var phplive_display_color = screen.colorDepth ; var phplive_timezone = new Date().getTimezoneOffset() ;
	if ( navigator.mimeTypes.length > 0 ) { for (var x=0; x < navigator.mimeTypes.length; x++) { phplive_mime_types += navigator.mimeTypes[x].description ; } }
	var phplive_browser_gl = ( typeof( document.createElement("canvas").getContext ) != "undefined" ) ? document.createElement("canvas").getContext("webgl") : new Object ; var phplive_browser_gl_string = "" ; for ( var phplive_browser_gl in phplive_browser_gl ) { phplive_browser_gl_string += phplive_browser_gl+phplive_browser_gl[phplive_browser_gl] ; }
	var phplive_browser_token = phplive_md5( phplive_display_width+phplive_display_height+phplive_display_color+phplive_timezone+phplive_browser+phplive_mime_types+phplive_browser_gl_string ) ;
	location.href = "./phplive.php?<?php echo preg_replace( "/&&/", "&", preg_replace( "/&$/", "", $query ) ) ?>&token="+phplive_browser_token ;
//-->
</script>
</head>
<body style="background: transparent;"></body>
</html>
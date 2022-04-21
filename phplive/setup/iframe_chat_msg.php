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
	if ( !is_file( "../web/config.php" ) ){ HEADER("location: install.php") ; exit ; }
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/english.php" ) ;

	$error = "" ; $theme = $CONF["THEME"] ; $lang = "english" ;
	$chat_end_message = "" ;

	$preview = Util_Format_Sanatize( Util_Format_GetVar( "preview" ), "n" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$text = Util_Format_Sanatize( Util_Format_GetVar( "text" ), "noscripts" ) ; if ( !$text ) { $text = "" ; }
	$text = preg_replace( "/\"/", "'", $text ) ;
	$text = preg_replace( "/<html(.*?)>/i", "'", $text ) ;
	$text = preg_replace( "/<body(.*?)>/i", "'", $text ) ;
	$chat_end_message = $text ;

	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	if ( isset( $deptinfo["deptID"] ) )
	{
		$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
		if ( isset( $dept_themes[$deptid] ) && $dept_themes[$deptid] ) { $theme = $dept_themes[$deptid] ; }
		else if ( $theme && !is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/style.css" ) || isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = $CONF["THEME"] ; }

		if ( !$preview )
		{
			$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
			$chat_end_message = ( isset( $deptvars["end_chat_msg"] ) && $deptvars["end_chat_msg"] ) ? $deptvars["end_chat_msg"] : "" ;
		}
	}
	if ( isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }
	if ( $preview && !$chat_end_message )
	{
		$chat_end_message = "<div class=\"info_error\" style=\"margin-top: 15px;\">Please provide the message.</div>" ;
	}

	if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../themes/<?php echo $theme ?>/style.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("#div_chat_end_message a").click(function( event ){
			event.preventDefault() ;
			window.open( $(this).attr('href'), '_blank' ) ;
		});
	});
//-->
</script>
</head>
<body>

<div id="request_body" style="height: 100%; padding-top: 15px;">
	<div id="chat_body" style="padding: 0px; border: 0px;">
		<?php if ( $deptid && $chat_end_message ): ?>
		<div class="cv"><b>Visitor:</b> Ok great.  Thank you for the information.</div>
		<div class="co"><b>Operator:</b> You are welcome.  Have a great day.</div>
		<div class='cl'>The party has left.  Chat has ended.</div>
		<?php echo "<div class='ca' id='div_chat_end_message'>".$chat_end_message."</div>" ?>
		<?php endif ; ?>
	</div>
</div>

</body>
</html>

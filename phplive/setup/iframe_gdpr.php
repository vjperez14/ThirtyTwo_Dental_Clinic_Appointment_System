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
	$gdpr_message = "" ;

	$preview = Util_Format_Sanatize( Util_Format_GetVar( "preview" ), "n" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$text = Util_Format_Sanatize( Util_Format_GetVar( "text" ), "noscripts" ) ; if ( !$text ) { $text = "" ; }
	$text = preg_replace( "/\"/", "'", $text ) ;
	$text = preg_replace( "/<html(.*?)>/i", "'", $text ) ; $text = preg_replace( "/<body(.*?)>/i", "'", $text ) ;
	$gdpr_message = $text ;
	$text_checkbox = Util_Format_Sanatize( Util_Format_GetVar( "text_checkbox" ), "notags" ) ;

	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	if ( isset( $deptinfo["deptID"] ) )
	{
		$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
		if ( isset( $dept_themes[$deptid] ) && $dept_themes[$deptid] ) { $theme = $dept_themes[$deptid] ; }
		else if ( $theme && !is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/style.css" ) || isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = $CONF["THEME"] ; }

		if ( !$preview )
		{
			$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
			$gdpr_message = ( isset( $deptvars["gdpr_msg"] ) && $deptvars["gdpr_msg"] ) ? $deptvars["gdpr_msg"] : "" ;
		}
	}
	if ( isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }
	if ( preg_match( "/-_-/", $gdpr_message ) ) { LIST( $text_checkbox, $gdpr_message ) = explode( "-_-", $gdpr_message ) ; }
	$text_checkbox = preg_replace( "/\[link\](.*?)\[\/link\]/", "<a href='JavaScript:void(0)' onClick='toggle_policy( $deptid )'>$1</a>", $text_checkbox ) ;

	if ( $preview && !$gdpr_message )
	{
		$error = "Please provide the policy text." ;
		$text_checkbox = "" ;
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
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo filemtime ( "../js/global.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		if ( <?php echo ( $gdpr_message ) ? 1 : 0 ; ?> )
		{
			toggle_policy() ;
		}
		else if ( <?php echo ( $error ) ? 1 : 0 ; ?> )
		{
			$('#error').show() ;
		}

		$("#div_policy a").click(function( event ){
			event.preventDefault() ;
			window.open( $(this).attr('href'), '_blank' ) ;
		});
	});

	function toggle_policy( thedeptid )
	{
		// have to use z-index method for IE7 quirk freeze using hide or fadeOut
		if ( parseInt( $('#div_policy_wrapper').css('top') ) > 0 )
		{
			$('#div_policy_wrapper').css('top', "-500px") ;
		}
		else
		{
			$('#div_policy_wrapper').center() ;
		}
	}

	function start_chat()
	{
		if ( !$('#checkbox_data_policy').is(':checked') )
		{
			$('#checkbox_data_policy_arrow').show() ;
			$('#div_notice_data_policy').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		}
		else
		{
			do_alert( 0, "Chat is not available for interface preview." ) ;
		}
	}
//-->
</script>
</head>
<body style="overflow: hidden;">

<div id="request_body" style="height: 100%; padding-top: 15px;">
	<?php if ( $text_checkbox ): ?>
	<div id="chat_submit_btn" style="margin-top: 25px; padding-bottom: 25px;">
		<div id="div_notice_data_policy"><input type="checkbox" id="checkbox_data_policy" style="-webkit-transform:scale(1.2,1.2); -moz-transform:scale(1.2,1.2); -ms-transform:scale(1.2,1.2); transform:scale(1.2,1.2);"> &nbsp; <span id="checkbox_data_policy_arrow" style="display: none;">&larr;</span> &nbsp; <?php echo $text_checkbox ?></div>
		<div style="margin-top: 35px;">
			<button id="chat_button_start" class="input_button" type="button" style="width: 160px; height: 45px; font-size: 14px; font-weight: bold; padding: 6px;" onClick="start_chat()"><span id="LANG_CHAT_BTN_START_CHAT">Start Chat</span></button>
		</div>
	</div>
	<?php endif ; ?>

	<div id="error" class="info_error" style="display: none; margin-top: 25px; padding-bottom: 25px;"><?php echo $error ?></div>
</div>

<div id="div_policy_wrapper" style="position: fixed; top: -500px; left: 0px; width: 90%; padding: 2px; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2); z-Index: 10;" class="info_content">
	<div style="text-align: center; cursor: pointer;" class="info_error" onClick="toggle_policy()">Close</div>
	<div id="div_policy" style="margin-top: 5px; padding: 5px; height: 180px; overflow: auto;"><?php echo $gdpr_message ?></div>
</div>
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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	$error = "" ;

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$page = Util_Format_Sanatize( Util_Format_GetVar( "page" ), "n" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$option = Util_Format_Sanatize( Util_Format_GetVar( "option" ), "n" ) ;
	$sub = Util_Format_Sanatize( Util_Format_GetVar( "sub" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$bgcolor = Util_Format_Sanatize( Util_Format_GetVar( "bgcolor" ), "ln" ) ;

	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($deptinfo["lang"], "ln").".php" ) ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=<?php echo $LANG["CHARSET"] ?>">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var winname = unixtime() ;
	var option = <?php echo $option ?> ; // used to communicate with depts.php to toggle iframe

	$(document).ready(function()
	{
		$.ajaxSetup({ cache: false }) ;
		$("body, html").css({'background-color': '#<?php echo $bgcolor ?>'}) ;

		<?php if ( ( $action === "update" ) && !$error ): ?>
		do_alert( 1, "Update Success" ) ;
		<?php elseif ( $error ): ?>
		do_alert( 0, "<?php echo $error ?>" ) ;
		<?php endif ; ?>
	});

	function send_complete( thejson_data )
	{
		var json_data = new Object ;

		$('#btn_send_email').attr( "disabled", false ) ;
		try {
			eval(thejson_data) ;
		} catch(err) {
			do_alert( 0, "Email did not send. [Error: "+err+"]" ) ;
			return false ;
		}

		if ( json_data.status )
		{
			$('#btn_send_email').attr( "disabled", true ) ;
			do_alert_div( "../", 1, "<img src=\"../pics/icons/check.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\"> Success!  Test email sent to <b><?php echo $deptinfo["email"] ?></b>.  If the email does not arrive within 5-10 minutes, try a different email as the department email or check the email server mail log file for more information." ) ;
		}
		else
		{
			var error = json_data.error ;

			if ( error.match( /mail server is not installed/i ) )
				error = error + " Contact your server admin to fix the email issue or consider using an <a href=\"../addons/smtp/smtp.php?deptid=<?php echo $deptid ?>\" target=\"_parent\" style=\"color: #FFFFFF;\">SMTP</a> provider.  All operations that require sending of emails (email transcripts, email offline messages, etc) will produce this error until fixed." ;
			do_alert_div( "../", 0, error ) ;
		}
	}

	function disable_button()
	{
		$('#btn_send_email').attr( 'disabled', true ) ;
	}
//-->
</script>
</head>
<body style="overflow: hidden;">

<div id="iframe_body" style="height: 440px; padding: 10px; overflow: hidden; <?php echo ( $bgcolor ) ? "background: #$bgcolor;" : "" ?>">
	<div class="title">Is your server able to send emails?</div>
	<div style="margin-top: 15px;">As a default, the system will utilize the standard <a href="https://www.php.net/manual/en/function.mail.php" target="php_mailfunction">PHP mail()</a> function using the server mail settings to send out emails (transcripts, offline messages, etc).  However, if the department SMTP values are provided, emails will be sent using the SMTP values.</div>

	<div style="margin-top: 15px;">
		<div class="info_misc"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt="enabled"> SMTP settings can be updated at the <a href="../addons/smtp/smtp.php?deptid=<?php echo $deptid ?>" target="_parent">SMTP</a> area.</div>
	</div>

	<div style="margin-top: 15px;">
		<div id="div_alert" style="display: none; margin-bottom: 15px; text-shadow: none;"></div>
		<div><b>Check if your server can send out emails</b> &rarr; <button type="button" class="btn" onClick="parent.send_test_email(<?php echo $deptid ?>, '<?php echo $deptinfo["email"] ?>');" id="btn_send_email">Send Test Email to <?php echo $deptinfo["email"] ?></button></div>
	</div>
</div>

</body>
</html>
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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$error = Util_Format_Sanatize( Util_Format_GetVar( "error" ), "ln" ) ;
	$page = Util_Format_Sanatize( Util_Format_GetVar( "page" ), "n" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$option = Util_Format_Sanatize( Util_Format_GetVar( "option" ), "n" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$bgcolor = Util_Format_Sanatize( Util_Format_GetVar( "bgcolor" ), "ln" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ;
	$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($deptinfo["lang"], "ln").".php" ) ;
	$addon_auto_respond = is_file( "$CONF[DOCUMENT_ROOT]/addons/auto_reply/inc_iframe.php" ) ? 1 : 0 ;

	if ( $addon_auto_respond && ( $action === "update" ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/addons/auto_reply/submit.php" ) ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER( "location: iframe_edit_2.php?action=$action&bgcolor=$bgcolor&option=$option&deptid=$deptid&copy_all=$copy_all&error=$error" ) ;
		exit ;
	}

	$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
	$offline_form = ( isset( $deptvars["offline_form"] ) ) ? $deptvars["offline_form"] : 1 ;

	$lang_db = Lang_get_Lang( $dbh, $deptid ) ;
	if ( isset( $lang_db["deptID"] ) && $lang_db["deptID"] )
	{
		$db_lang_hash = unserialize( $lang_db["lang_vars"] ) ;
		$LANG = array_merge( $LANG, $db_lang_hash ) ;
	}

	$auto_reply_onoff = 0 ; 
	$auto_reply_subject = $auto_reply_body = $auto_reply_from = "" ;
	if ( isset( $deptvars["offline_auto_reply"] ) && preg_match( "/-_-/", $deptvars["offline_auto_reply"] ) )
	{
		$auto_reply_array = explode( "-_-", $deptvars["offline_auto_reply"] ) ;
		if ( isset( $auto_reply_array[0] ) && isset( $auto_reply_array[1] ) && isset( $auto_reply_array[2] ) && isset( $auto_reply_array[3] ) )
		{
			$auto_reply_onoff = $auto_reply_array[0] ;
			$auto_reply_from = $auto_reply_array[1] ;
			$auto_reply_subject = $auto_reply_array[2] ;
			$auto_reply_body = $auto_reply_array[3] ;
		}
	}
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
	var option = <?php echo $option ?> ; // used to communicate with depts.php to toggle iframe

	$(document).ready(function()
	{
		$.ajaxSetup({ cache: false }) ;
		$("body, html").css({'background-color': '#<?php echo $bgcolor ?>'}) ;

		<?php if ( $action === "success" ): ?>
		do_alert( 1, "Update Success" ) ;

		<?php if ( $auto_reply_onoff ): ?>
			<?php if ( $copy_all ): ?>
			$('*[id*=span_auto_]', parent.document).each(function() {
				$(this).html( "<span class=\"info_good\" style=\"padding: 2px;\">On</span>" ) ;
			}) ;
			<?php else: ?>
			$('#span_auto_<?php echo $deptid ?>', parent.document).html( "<span class=\"info_good\" style=\"padding: 2px;\">On</span>" ) ;
			<?php endif ; ?>
		<?php else: ?>
			<?php if ( $copy_all ): ?>
			$('*[id*=span_auto_]', parent.document).each(function() {
				$(this).html( "<span class=\"info_error\" style=\"padding: 2px;\">Off</span>" ) ;
			}) ;
			<?php else: ?>
			$('#span_auto_<?php echo $deptid ?>', parent.document).html( "<span class=\"info_error\" style=\"padding: 2px;\">Off</span>" ) ;
			<?php endif ; ?>
		<?php endif ; ?>

		<?php elseif ( $error ): ?>
		do_alert( 0, "<?php echo $error ?>" ) ;
		<?php endif ; ?>
	});

	function do_submit_settings()
	{
		var receipt_from = $('#receipt_from').val().replace(/\s/g,'') ;
		$('#receipt_from').val(receipt_from) ;

		if ( receipt_from && !check_email( receipt_from ) )
			do_alert( 0, "Email format is invalid. (example: you@domain.com)" ) ;
		else
			$('#form_settings').submit() ;
	}
//-->
</script>
</head>
<body>

<div id="iframe_body" style="height: 440px; padding: 10px; <?php echo ( $bgcolor ) ? "background: #$bgcolor;" : "" ?>">
	<form action="iframe_edit_2.php" id="form_settings" method="POST" accept-charset="<?php echo $LANG["CHARSET"] ?>">
	<input type="hidden" name="action" value="update">
	<input type="hidden" name="deptid" value="<?php echo $deptid ?>">
	<input type="hidden" name="option" value="<?php echo $option ?>">
	<input type="hidden" name="bgcolor" value="<?php echo $bgcolor ?>">
	<input type="hidden" name="jump" id="jump" value="">

	<div class="info_info">
	<?php if ( $addon_auto_respond ) { include_once( "$CONF[DOCUMENT_ROOT]/addons/auto_reply/inc_iframe.php" ) ; } ?>
	</div>

	<?php if ( count( $departments ) > 1 ) : ?>
	<div style="margin-top: 15px;"><input type="checkbox" id="copy_all" name="copy_all" value=1> copy this update to all departments</div>
	<?php endif ; ?>

	<div style="margin-top: 25px;"><input type="button" value="Update" class="btn" onClick="do_submit_settings()"> &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="parent.do_options( <?php echo $option ?>, <?php echo $deptid ?> );">cancel</a></div>

	</form>
</div>

</body>
</html>
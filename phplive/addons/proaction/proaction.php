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
	if ( !is_file( "../../web/config.php" ) ){ HEADER("location: ../../setup/install.php") ; exit ; }
	include_once( "../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/Util_Proaction.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/addons/API/Util_Addons.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }

	$PROACTION_VERSION = "1.0" ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/VERSION.php" ) )
		include_once( "$CONF[DOCUMENT_ROOT]/addons/proaction/API/VERSION.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$jump = ( Util_Format_GetVar( "jump" ) ) ? Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) : "image" ;
	$proid = Util_Format_Sanatize( Util_Format_GetVar( "proid" ), "ln" ) ; if ( !$proid ) { $proid = $now ; }
	$error = "" ; $display = 0 ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$depts_hash = Array( 0 => "All Departments" ) ;
?>
<?php include_once( "../../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "../../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../../js/framework.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../../js/jquery_md5.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#E4EBF3'}) ;

		init_menu() ;
		toggle_menu_setup( "html" ) ;
		show_div( "code_proaction" ) ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Success" ) ;
		<?php elseif ( $action && $error ): ?>do_alert( 0, "<?php echo $error ?>" ) ;<?php endif ; ?>
		$('#btn_submit').bind('click', function( ) {
			do_submit( ) ;
		}) ;
	});
//-->
</script>
</head>
<?php include_once( "../../setup/inc_header.php" ) ?>

		<?php if ( !count( $departments ) ): ?>
		<span class="info_error"><img src="../../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="../../setup/depts.php" style="color: #FFFFFF;">Department</a> to continue.</span>
		<?php
			else:
			$display = 1 ;
		?>
		<?php endif ; ?>

		<?php
			if ( $display ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_menu_code.php" ) ;
		?>

		<div style="margin-top: 25px;">
			<div>On webpages containing the <a href="../../setup/code.php">Standard HTML Code</a>, automatically display a ProAction Invite to the visitor when certain criterias are met.  ProAction Invite can be a custom message to convey call to action to chat, display promotional information or direct a visitor to a specific URL.</div>

			<div style="margin-top: 25px;"><?php include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_freev.php" ) ; ?></div>
		</div>
		<?php endif ; ?>

<?php include_once( "../../setup/inc_footer.php" ) ?>

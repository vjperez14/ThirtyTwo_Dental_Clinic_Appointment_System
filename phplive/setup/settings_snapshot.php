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

	$snapshot_file = "$CONF[DOCUMENT_ROOT]/examples/snapshot.txt" ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#E4EBF3'}) ;
		init_menu() ;
		toggle_menu_setup( "settings" ) ;
	});
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='settings.php?jump=eips'" id="menu_eips">Excluded IPs</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=sips'" id="menu_sips">Blocked IPs</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=props'" id="menu_props">Autocorrect & Charset</div>
			<?php if ( $admininfo["adminID"] == 1 ): ?>
			<div class="op_submenu" onClick="location.href='settings.php?jump=cookie'" id="menu_cookie">Cookies</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=upload'" id="menu_upload">File Upload</div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/mapp/settings.php" ) ): ?><div class="op_submenu" onClick="location.href='../mapp/settings.php'" id="menu_system"><img src="../pics/icons/mobile.png" width="12" height="12" border="0" alt=""> Mobile App</div><?php endif ; ?>
			<div class="op_submenu" onClick="location.href='settings.php?jump=profile'" id="menu_profile"><img src="../pics/icons/key.png" width="12" height="12" border="0" alt=""> Password</div>
			<?php endif ; ?>
			<div class="op_submenu_focus" id="menu_system">System</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<div style="margin-bottom: 25px;"><span class="info_neutral"><img src="../pics/icons/arrow_left.png" width="16" height="15" border="0" alt=""> <a href="system.php">back</a></span></div>
			<div id="div_alert"></div>
			<div style="matgin-top: 25px;">Check the integrity of the software files to ensure they are original code.</div>
			<div style="margin-top: 15px;"><?php include_once( "./inc_freev.php" ) ; ?></div>
		</div>

<?php include_once( "./inc_footer.php" ) ?>


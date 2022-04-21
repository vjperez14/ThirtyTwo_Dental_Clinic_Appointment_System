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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;

	$operators = Ops_get_AllOps( $dbh ) ;
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

<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;

	$(document).ready(function()
	{
		$.ajaxSetup({ cache: false }) ;
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#E4EBF3'}) ;

		init_menu() ;
	});
//-->
</script>
</head>
<?php include_once( "../../setup/inc_header.php" ) ?>

		<div id="canned_container">

			<?php if ( !count( $operators) ):  ?>
			<span class="info_error"><img src="../../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add an <a href="../../setup/ops.php" style="color: #FFFFFF;">Operator</a> to continue.</span>

			<?php else: ?>
			<img src="./pics/mboard.png" width="16" height="16" border="0" alt=""> <big><b>Message Board:</b></big> Post a message that can be viewed by all operators on their operator console "Message Board".
			<div style="margin-top: 25px;">
				<?php include_once( "../../setup/inc_freev.php" ) ; ?>
			</div>
			<?php endif ; ?>

		</div>
		<!-- dummy divs for operator console duplicate -->
		<div style="display: none;">
			<div id="chat_body"></div>
		</div>

<?php include_once( "../../setup/inc_footer.php" ) ?>

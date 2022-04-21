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
	include_once( "../../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$opinfo = Util_Security_AuthOp( $dbh ) ){ ErrorHandler( 602, "Invalid operator session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$error = "" ;

	$operators = Ops_get_AllOps( $dbh ) ;
	$theme = $opinfo["theme"] ;
	if ( !is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/style.css" ) ) { $theme = "default" ; }
?>
<?php include_once( "../../inc_doctype.php" ) ?>
<head>
<title> operators </title>

<meta name="description" content="v.<?php echo $VERSION ?>">
<meta name="keywords" content="<?php echo md5( $KEY ) ?>">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../../themes/<?php echo $theme ?>/style.css?<?php echo filemtime ( "../../themes/$theme/style.css" ) ; ?>">
<script data-cfasync="false" type="text/javascript" src="../../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var si_op2op ;

	$(document).ready(function()
	{
		$.ajaxSetup({ cache: false }) ;

		var div_height = parent.extra_wrapper_height - 45 ;
		setTimeout(function(){
			$('#canned_container').css({'min-height': div_height}).fadeIn("slow") ;
		}, 100) ;

		var div_height_inner = div_height - 55 ;
		$('#canned_body').css({'height': div_height_inner}) ;
		$('#div_operators').css({'height': div_height_inner}) ;

		parent.init_extra_loaded() ;
	});
//-->
</script>
</head>
<body>

<div id="canned_container" style="display: none; padding: 15px; height: 200px; overflow: auto;">
	<div style="">
		Post a message on a real-time message board that can be viewed by all operators.

		<div style="margin-top: 25px;"><?php include_once( "../../setup/inc_freev.php" ) ; ?></div>
	</div>
</div>

</body>
</html>
<?php database_mysql_close( $dbh ) ; ?>

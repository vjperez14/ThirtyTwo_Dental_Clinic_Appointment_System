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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$gid = Util_Format_Sanatize( Util_Format_GetVar( "gid" ), "n" ) ;

	$departments_all = Depts_get_AllDepts( $dbh ) ;
	$departments = Array() ; $depts_hash = Array() ;
	for ( $c = 0; $c < count( $departments_all ); ++$c )
	{
		$department = $departments_all[$c] ;
		$depts_hash[$department["deptID"]] = $department["name"] ;

		if ( ( $department["name"] != "Archive" ) && $department["visible"] )
			$departments[] = $department ;
	}

	$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;
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
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var max_depts = 5 ; // maximum  departments in a group.  greater then 5 may reduce system performance (loading the chat icon)
	var global_group_deptid = 0 ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#E4EBF3'}) ;
		init_menu() ;
		toggle_menu_setup( "depts" ) ;
	});
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='depts.php'">Chat Departments</div>
			<div class="op_submenu" onClick="location.href='dept_display.php'">Department Select Display Order</div>
			<div class="op_submenu_focus">Department Groups</div>
			<div class="op_submenu" onClick="location.href='dept_canned_cats.php'">Canned Response Categories</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<form>
			<input type="hidden" name="gid" id="gid" value=0>
			<div>Create department groups for customized department selection on the chat request window. Max 5 departments can be assigned to a group to maintain optimal system performance.</div>

			<div style="margin-top: 25px;"><?php include_once( "./inc_freev.php" ) ; ?></div>
		</div>

<?php include_once( "./inc_footer.php" ) ?>

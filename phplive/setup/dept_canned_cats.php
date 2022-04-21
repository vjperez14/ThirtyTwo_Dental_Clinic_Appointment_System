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
	$error = "" ;

	$departments = Depts_get_AllDepts( $dbh, "display ASC, name ASC" ) ;

	$can_cats_admin = ( isset( $VALS["can_cats"] ) && $VALS["can_cats"] ) ? $VALS["can_cats"] : "" ;

	// make hash for quick refrence
	$can_cats_prefill = "{ \"1111111111\":[], " ;
	$dept_hash = Array() ;
	$dept_hash[1111111111] = "All Departments" ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$dept_hash[$department["deptID"]] = $department["name"] ;

		$can_cats_prefill .= " \"$department[deptID]\":[], " ;
	} $can_cats_prefill = preg_replace( "/, $/", "", $can_cats_prefill ) ;
	$can_cats_prefill .= " }" ;
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
<link rel="Stylesheet" href="../js/jquery-ui.min.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
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

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["depts"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='depts.php'">Chat Departments</div>
			<div class="op_submenu" onClick="location.href='dept_display.php'">Department Select Display Order</div>
			<div class="op_submenu" onClick="location.href='dept_groups.php'">Department Groups</div>
			<div class="op_submenu_focus">Canned Response Categories</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<div>The canned response categories will be available when adding/editing a canned response at the <a href="depts.php?ftab=cans">Department Canned Responses</a> area.</div>
			<div style="margin-top: 15px;">Categories can be created by <a href="ops.php">chat operators</a> at the <a href="ops.php?jump=online">operator area</a>, or the categories can be created at this area.  If categories are created here, all the operators will automatically use these categories and the ability to create/edit their own categories will not be available to the operators.  If there are no categories in this area, the system will automatically allow operators to create their own canned categories.</div>
			<div style="margin-top: 25px;"><?php include_once( "./inc_freev.php" ) ; ?></div>
		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>

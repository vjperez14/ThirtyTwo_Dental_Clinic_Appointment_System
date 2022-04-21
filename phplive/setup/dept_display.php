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

	if ( $action == "display_order" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;
		$deptids = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "a" ) ;
		$displays = Util_Format_Sanatize( Util_Format_GetVar( "ds" ), "a" ) ;

		for ( $c = 0; $c < count( $deptids ); ++$c )
		{
			$deptid = Util_Format_Sanatize( $deptids[$c], "n" ) ;
			$display = isset( $displays[$c] ) ? Util_Format_Sanatize( $displays[$c], "n" ) : -1 ;

			if ( $deptid && ( $display != -1 ) )
			{
				Depts_update_DeptValue( $dbh, $deptid, "display", $display ) ;
			}
		}
		$json_data = "json_data = { \"status\": 1 };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$departments = Depts_get_AllDepts( $dbh, "display ASC, name ASC" ) ;
	$embed_win_sizes = ( isset( $VALS["embed_win_sizes"] ) && $VALS["embed_win_sizes"] ) ? unserialize( $VALS["embed_win_sizes"] ) : Array() ;
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
<script data-cfasync="false" type="text/javascript" src="../js/jquery-ui.min.js?<?php echo $VERSION ?>"></script>

<style>
ul {
	list-style: none;
	padding-left: 0;
}​
</style>
<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "depts" ) ;

		$( "#div_sortable" ).sortable() ;

		$( "#div_sortable" ).sortable() ;
		$( "#div_sortable" ).on('sortupdate', function() { do_sort() ; } ) ;

		var cursor_grab = ( browser_filter ) ? "grab" : "move" ;
		var cursor_grabbing = ( browser_filter ) ? "grabbing" : "move" ;

		$( ".li_cursor" ).css('cursor', cursor_grab) ;
		$( ".li_cursor" ).mousedown(function() {
			$(this).css('cursor', cursor_grabbing) ;
		});
		$( ".li_cursor" ).mouseup(function() {
			$(this).css('cursor', cursor_grab) ;
		});
	});

	function do_sort()
	{
		var sort_order = $( "#div_sortable" ).sortable("toArray") ;
		var query_string = "" ;

		for ( var c = 0; c < sort_order.length; ++c )
		{
			var matches = sort_order[c].match( /^dept_(.*?)_(.*?)$/ ) ;
			if ( typeof( matches[1] ) != "undefined" )
			{
				var display = c ;
				var deptid = matches[2] ;
				query_string += "d[]="+deptid+"&ds[]="+display+"&" ;
			}
		}
		
		if ( query_string )
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			$.ajax({
			type: "POST",
			url: "dept_display.php",
			data: "action=display_order&"+query_string+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
					do_alert( 1, "Update Success" ) ;
				else
					do_alert( 0, json_data.error ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Lost connection to server.  Please refresh the page and try again." ) ;
			} });
		}
	}
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
			<div class="op_submenu_focus">Department Select Display Order</div>
			<div class="op_submenu" onClick="location.href='dept_groups.php'">Department Groups</div>
			<div class="op_submenu" onClick="location.href='dept_canned_cats.php'">Canned Response Categories</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			For the <a href="code.php">All Departments HTML Code</a>, update the department selection display order.  Mouse grab and move the department to update the order (top being the first on the list).

			<?php if ( count( $departments ) < 2 ): ?>
			<div style="margin-top: 15px;">
				<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> This feature is available when there are at least 2 <a href="depts.php" style="color: #FFFFFF;">Departments</a> created.</span>
			</div>
			<?php else: ?>
			<div id="div_list" style="margin-top: 15px; padding-right: 15px; width: 300px; max-height: 400px; overflow: auto;">
				<ul id="div_sortable">
				<?php
					for ( $c = 0; $c < count( $departments ); ++$c )
					{
						$department = $departments[$c] ;

						$visible = ( $department["visible"] ) ? "" : "<img src=\"../pics/icons/privacy_on.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"not visible for selection\" title=\"not visible for selection\"> &nbsp; " ;

						print "<li id=\"dept_{$c}_{$department['deptID']}\" style=\"margin-bottom: 15px;\" class=\"info_neutral li_cursor\">$visible$department[name]</li>" ;
					}
				?>
				</ul>
			</div>
			<div style="margin-top: 15px;"><span class="info_menu_focus" style="padding: 6px;"><a href="JavaScript:void(0)" onClick="preview_embed()">view how it will look</a></span></div>
			<?php endif ; ?>

		</div>
		<?php endif ; ?>

<span style="color: #0000FF; text-decoration: underline; line-height: 0px !important; cursor: pointer; position: fixed; bottom: 0px; right: 15px; z-index: 20000000;" id="phplive_btn_615" onclick="phplive_launch_chat_0()"></span>
<script data-cfasync="false" type="text/javascript">

var st_embed_launch ;
var phplive_stop_chat_icon = 1 ;
var phplive_theme = "" ;
var phplive_embed_win_width = "<?php echo ( isset( $embed_win_sizes[0] ) ) ? $embed_win_sizes[0]["width"] : $VARS_CHAT_WIDTH_WIDGET ; ?>" ;
var phplive_embed_win_height = "<?php echo ( isset( $embed_win_sizes[0] ) ) ? $embed_win_sizes[0]["height"] : $VARS_CHAT_HEIGHT_WIDGET ; ?>" ;

function preview_embed()
{
	if ( $('#phplive_iframe_chat_embed_wrapper').is(":visible") )
	{
		phplive_embed_window_close( ) ;
		if ( typeof( st_embed_launch ) != "undefined" ) { clearTimeout( st_embed_launch ) ; }
		st_embed_launch = setTimeout( function(){ phplive_launch_chat_0() ; }, 1200 ) ;
	}
	else { phplive_launch_chat_0() ; }
}

(function() {
var phplive_e_615 = document.createElement("script") ;
phplive_e_615.type = "text/javascript" ;
phplive_e_615.async = true ;
phplive_e_615.src = "<?php echo $CONF["BASE_URL"] ?>/js/phplive_v2.js.php?v=0%7C615%7C0%7C&" ;
document.getElementById("phplive_btn_615").appendChild( phplive_e_615 ) ;
})() ;

</script>

<?php include_once( "./inc_footer.php" ) ?>
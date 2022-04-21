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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$prev_theme = Util_Format_Sanatize( Util_Format_GetVar( "prev_theme" ), "ln" ) ;
	$theme = Util_Format_Sanatize( Util_Format_GetVar( "theme" ), "ln" ) ;  if ( !$theme ) { $theme = $CONF["THEME"] ; }
	$error = "" ;

	$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
	$departments = Depts_get_AllDepts( $dbh ) ;
	$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;

	if ( $action == "update_theme" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

		if ( is_dir( "$CONF[DOCUMENT_ROOT]/themes/$theme/" ) )
		{
			if ( !$deptid )
			{
				$error = ( Util_Vals_WriteToConfFile( "THEME", $theme ) ) ? "" : "Could not write to config file." ;
				if ( !$error )
				{
					$CONF["THEME"] = $theme ;

					$update_vals = 0 ;
					foreach ( $dept_themes as $the_deptid => $theme )
					{
						if ( $theme == $prev_theme ) { unset( $dept_themes[$the_deptid] ) ; $update_vals = 1 ; }
					}
					if ( count( $dept_themes ) || $update_vals ) { $error = ( Util_Vals_WriteToFile( "THEMES", serialize( $dept_themes ) ) ) ? "" : "Could not write to vals file. [e1]" ; }
				}
			}
			else
			{
				if ( ( $deptid && isset( $dept_themes[$deptid] ) && ( $dept_themes[$deptid] == $theme ) ) || ( isset( $CONF["THEME"] ) && ( $CONF["THEME"] == $theme ) ) ) {
					if ( isset( $dept_themes[$deptid] ) ) { unset( $dept_themes[$deptid] ) ; }
				}
				else { $dept_themes[$deptid] = $theme ; }
				$error = ( Util_Vals_WriteToFile( "THEMES", serialize( $dept_themes ) ) ) ? "" : "Could not write to vals file. [e2]" ;
			}
		}
		else { $error = "Invalid theme." ; }
	}

	$themes_js = "" ;
	foreach ( $dept_themes as $key => $value )
		$themes_js .= "themes[$key] = '$value' ; " ;
	$embed_win_sizes = ( isset( $VALS["embed_win_sizes"] ) ) ? unserialize( $VALS["embed_win_sizes"] ) : Array() ;
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
	"use strict" ;
	var deptid = <?php echo $deptid ?> ;
	var primary_theme = "<?php echo $CONF["THEME"] ?>" ;
	var global_theme = "<?php echo $theme ?>" ;
	var themes = new Object ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "interface" ) ;

		<?php if ( $themes_js ): ?>
		eval( "<?php echo $themes_js ?>" ) ;
		<?php endif ; ?>

		switch_dept( deptid ) ;
		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>

	});

	function switch_dept( thedeptid )
	{
		var theme ;
		var dept_name = $("#deptid option:selected").text() ;

		if ( deptid != thedeptid )
		{
			$('#td_loading').show() ;
			location.href = "interface_themes.php?deptid="+thedeptid ;
		}
		else
		{
			deptid = thedeptid ;

			if ( typeof( themes[deptid] ) != "undefined" ) { theme = themes[deptid] ; }
			else { theme = primary_theme ; }
			global_theme = theme ;

			$('#div_themes').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "span_" ) == 0 )
					$('#'+div_name).removeClass('info_misc').addClass('info_white') ;
			}) ;
			$('#span_'+theme).removeClass('info_white').addClass('info_misc') ;
			$('#theme_'+theme).prop('checked', true) ;
			$('#div_thumb_'+theme).fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;

			if ( dept_name == "Primary Theme" )
				$('#div_information').html( "<span style='font-size: 16px; font-weight: bold;'>Primary</span> chat window theme for <a href='./code.php?deptid="+deptid+"'>All Departments HTML Code</a>" ) ;
			else
				$('#div_information').html( "<span style='font-size: 16px; font-weight: bold;'>"+dept_name+"</span> chat window theme for <a href='./code.php?deptid="+deptid+"'>Department Specific HTML Code</a>" ) ;
		}
	}

	function confirm_theme( thetheme, thethumb )
	{
		if ( global_theme != thetheme )
		{
			var height = $(document).height() ;

			$('#theme_'+thetheme).prop('checked', true) ;
			$('#div_theme_thumb').html( "<div style=\"background: url( "+thethumb+" ); background-position: top left; width: 155px; height: 105px; border-radius: 5px;\">&nbsp;</div>") ;

			$('body').css({'overflow': 'hidden'}) ;
			$('#div_confirm').css({'height': height+'px'}).show() ;
			$('#div_confirm_body').center().show() ;
		}
	}

	function update_theme( thetheme )
	{
		location.href = "interface_themes.php?action=update_theme&deptid="+deptid+"&prev_theme="+global_theme+"&theme="+thetheme ;
	}

	function update_theme_pre( theflag )
	{
		if ( theflag )
		{
			var theme = $('input:radio[name=theme]:checked').val() ;
			update_theme( theme ) ;
		}
		else
		{
			$('#theme_'+global_theme).prop('checked', true) ;

			$('#div_confirm').hide() ;
			$('#div_confirm_body').hide() ;
			$('body').css({'overflow': 'visible'}) ;
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" onClick="location.href='interface.php?jump=logo'" style="margin-left: 0px;">Logo</div>
			<div class="op_submenu_focus" id="menu_themes">Theme</div>
			<div class="op_submenu" onClick="location.href='interface_custom.php'" id="menu_custom">Form Fields</div>
			<div class="op_submenu" onClick="location.href='interface_lang.php'">Update Texts</div>
			<div class="op_submenu" onClick="location.href='code_autostart.php'" id="menu_auto">Automatic Start Chat</div>
			<div class="op_submenu" onClick="location.href='interface_gdpr.php'" id="menu_gdpr">Consent Checkbox</div>
			<div class="op_submenu" onClick="location.href='interface_chat_msg.php'">Chat End Msg</div>
			<?php if ( phpversion() >= "5.1.0" ): ?><div class="op_submenu" onClick="location.href='interface.php?jump=time'">Timezone</div><?php endif; ?>
			<div class="op_submenu" onClick="location.href='code_settings.php'">Settings</div>
			<div style="clear: both"></div>
		</div>

		<?php if ( !count( $departments ) ): ?>
		<div style="margin-top: 25px;">
			<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span>
		</div>
		<?php else: ?>

		<div style="margin-top: 25px;">Set the theme for the visitor chat window.</div>
		<div style="margin-top: 15px;" class="info_info">
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td valign="top" width="50%">
					<div style="text-shadow: none;">
						<form>
						<?php if ( count( $departments ) > 1 ): ?>
						<div class="info_misc">
							<div>When updating the <b>Primary Theme</b>, it will also update the themes for departments that have the same theme set as the Primary Theme.</div>
							<div style="margin-top: 5px;">
								<table cellspacing=0 cellpadding=0 border=0>
								<tr>
									<td>
										<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this.value )">
										<option value="0">Primary Theme</option>
										<?php
											for ( $c = 0; $c < count( $departments ); ++$c )
											{
												$department = $departments[$c] ;
												$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
												if ( $department["name"] != "Archive" )
													print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
											}
											if ( count( $dept_groups ) )
											{
												for ( $c = 0; $c < count( $dept_groups ); ++$c )
												{
													$dept_group = $dept_groups[$c] ;
													$selected = ( $deptid == $dept_group["groupID"] ) ? "selected" : "" ;
													print "<option value=\"$dept_group[groupID]\" $selected>$dept_group[name] [Department Group]</option>" ;
												}
											}
										?>
										</select>
									</td>
									<td style="display: none; padding-left: 5px;" id="td_loading"><img src="../pics/loading_ci.gif" width="16" height="16" border="0" alt="" class="info_white"></td>
								</tr>
								</table>
								<div id="div_information" style="margin-top: 5px;"></div>
							</div>
						</div>
						<?php else: ?>
						<input type="hidden" name="deptid" id="deptid" value="0">
						<?php endif ; ?>

						<div id="div_themes" style="margin-top: 5px;">
							<table cellspacing=0 cellpadding=2 border=0 width="100%" style="margin-top: 25px;">
							<tr>
								<td>
									<?php
										$dir_themes = opendir( "$CONF[DOCUMENT_ROOT]/themes/" ) ;

										$themes = Array() ;
										while ( $theme = readdir( $dir_themes ) )
											$themes[] = $theme ;
										closedir( $dir_themes ) ;

										sort( $themes, SORT_STRING ) ;
										for ( $c = 0; $c < count( $themes ); ++$c )
										{
											$theme = $themes[$c] ;
											$path_thumb = ( is_file( "../themes/$theme/thumb.png" ) ) ? "../themes/$theme/thumb.png" : "../pics/screens/thumb_theme_blank.png" ;

											if ( preg_match( "/[a-z]/i", $theme ) && !preg_match( "/^\./", $theme ) && ( $theme != "initiate" ) && !isset( $THEMES_EXCLUDE[$theme] ) )
											{
												if ( !isset( $CONF_EXTEND ) || !isset( $CONF_EXTEND_THEMES ) || !isset( $CONF_EXTEND_THEMES[$theme] ) || ( isset( $CONF_EXTEND ) && isset( $CONF_EXTEND_THEMES[$theme] ) && ( $CONF_EXTEND_THEMES[$theme] == $CONF_EXTEND ) ) )
													print "<div class=\"li_op round\" style=\"padding: 5px; width: 110px; margin-bottom: 15px;\"><div id=\"div_thumb_$theme\" style=\"background: url( $path_thumb ); background-position: top left; height: 90px;\" class=\"round_top\"><span style=\"padding: 4px; cursor: pointer;\" onClick=\"confirm_theme('$theme', '$path_thumb')\" id=\"span_$theme\"><input type=\"radio\" name=\"theme\" id=\"theme_$theme\" value=\"$theme\"> $theme</span></div><div style=\"text-align: center; cursor: pointer; \" class=\"info_action round_top_none\" onClick=\"preview_theme_embed('$theme', $deptid)\">click to preview</div></div>" ;
											}
										}
									?>
									<div style="clear: both;"></div>
								</td>
							</tr>
							</table>
						</div>
						</form>
					</div>
				</td>
			</tr>
			</table>
		</div>

		<?php endif ; ?>

<div id="div_confirm" style="display: none; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background: url( ../themes/initiate/bg_trans_dark.png ) repeat; overflow: hidden; z-index: 20;">&nbsp;</div>
<div id="div_confirm_body" class="info_neutral" style="display: none; position: absolute; padding: 25px; width: 350px; margin: 0 auto; top: 100px; box-shadow: -2px 0 16px 1px rgba(0,0,0,.1); z-index: 21;">
	<table cellspacing=0 cellpadding=0 border=0>
	<tr>
		<td><div id="div_theme_thumb" class="li_mapp round" style="border: 1px solid #DDDEDF; width: 155px; height: 105px;"></div><div class="clear:both;"></div></td>
		<td style="padding-left: 15px;">
			<div id="confirm_title">Select this theme?</div>
			<div style="margin-top: 15px;"><button type="button" onClick="update_theme_pre(1)" class="btn">Yes</button> &nbsp; &nbsp; <span style="text-decoration: underline; cursor: pointer;" onClick="update_theme_pre(0)">cancel</span></div>
		</td>
	</tr>
	</table>
</div>

<span style="color: #0000FF; text-decoration: underline; line-height: 0px !important; cursor: pointer; position: fixed; bottom: 0px; right: 15px; z-index: 20000000;" id="phplive_btn_615" onclick="phplive_launch_chat_0()"></span>
<script data-cfasync="false" type="text/javascript">

var phplive_v = new Object ;
var st_embed_launch ;
var phplive_stop_chat_icon = 1 ;
var phplive_theme = "" ;
var phplive_embed_win_width = "<?php echo $VARS_CHAT_WIDTH_WIDGET ; ?>" ;
var phplive_embed_win_height = "<?php echo $VARS_CHAT_HEIGHT_WIDGET ; ?>" ;

function preview_theme_embed( thetheme, thedeptid )
{
	phplive_v["deptid"] = thedeptid ;
	phplive_theme = thetheme ;

	if ( $('#phplive_iframe_chat_embed_wrapper').is(":visible") )
	{
		phplive_embed_window_close( ) ;
		if ( typeof( st_embed_launch ) != "undefined" ) { clearTimeout( st_embed_launch ) ; }
		st_embed_launch = setTimeout( function(){ phplive_launch_chat_0() ; }, 1000 ) ;
	}
	else { phplive_launch_chat_0() ; }
}

(function() {
var phplive_href = encodeURIComponent( location.href ) ;
var phplive_e_615 = document.createElement("script") ;
phplive_e_615.type = "text/javascript" ;
phplive_e_615.async = true ;
phplive_e_615.src = "<?php echo $CONF["BASE_URL"] ?>/js/phplive_v2.js.php?v=0|615|0|&r="+phplive_href ;
document.getElementById("phplive_btn_615").appendChild( phplive_e_615 ) ;
})() ;

</script>

<?php include_once( "./inc_footer.php" ) ?>
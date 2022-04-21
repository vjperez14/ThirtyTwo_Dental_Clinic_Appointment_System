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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/get.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
	$emarketing_addon_enabled = ( $VARS_ADDON_EMARKET_ENABLED && is_file( "../addons/emarketing/emarketing.php" ) ) ? 1 : 0 ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;

	if ( $action === "submit" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/put.php" ) ;

		$marketid = Util_Format_Sanatize( Util_Format_GetVar( "marketid" ), "n" ) ;
		$skey = Util_Format_Sanatize( Util_Format_GetVar( "skey" ), "ln" ) ;
		$name = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "name" ), "ln" ) ) ;
		$color = Util_Format_Sanatize( Util_Format_GetVar( "color" ), "ln" ) ;

		if ( !$skey )
			$skey = Util_Format_RandomString(3) ;

		if ( !Marketing_put_Marketing( $dbh, $marketid, $skey, $name, $color ) )
			$error = "Name ($name) is already in use." ;
	}
	else if ( $action === "delete" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/remove.php" ) ;

		$marketid = Util_Format_Sanatize( Util_Format_GetVar( "marketid" ), "n" ) ;
		Marketing_remove_Marketing( $dbh, $marketid ) ;
	}
	$marketings = Marketing_get_AllMarketing( $dbh ) ;
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
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "extras" ) ;
		if ( typeof( show_div ) == "function" )
			show_div( "marketing" ) ;

		<?php if ( $error ): ?>do_alert( 0, "<?php echo $error ?>" ) ;<?php endif ; ?>

	});

	function tcolor_focus( thediv )
	{
		$('#theform').find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf( "tcolor_li_" ) != -1 )
				$(this).css( { "border": "1px solid #C2C2C2" } ) ;
		} );

		if ( thediv != undefined )
		{
			$( "#color" ).val( thediv ) ;
			$( "#tcolor_li_"+thediv ).css( { "border": "1px solid #444444" } ) ;
		}
	}

	function do_edit( themarketid, theskey, thename, thecolor )
	{
		$( "input#marketid" ).val( themarketid ) ;
		$( "input#skey" ).val( theskey ) ;
		$( "input#name" ).val( thename ) ;
		tcolor_focus( thecolor ) ;
		location.href = "#a_edit" ;
	}

	function do_delete( themarketid )
	{
		if ( confirm( "Delete this campaign?" ) )
			location.href = "marketing_click.php?action=delete&marketid="+themarketid ;
	}

	function do_submit()
	{
		var name = $( "#name" ).val() ;
		var color = $( "#color" ).val() ;

		if ( name == "" )
			do_alert( 0, "Please provide the Campaign Name." ) ;
		else if ( color == "" )
			do_alert( 0, "Please select the Indication Color." ) ;
		else
			$('#theform').submit() ;
	}

//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["extras"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<?php include_once( "./inc_menu.php" ) ; ?>

		<div style="margin-top: 25px;">
			<div class="op_submenu_focus" style="margin-left: 0px;">Campaign Tracking</div>
			<div class="op_submenu3" onClick="location.href='reports_marketing.php'">Campaign Clicks</div>
			<?php if ( $emarketing_addon_enabled ): ?>
			<div class="op_submenu3" onClick="location.href='../addons/emarketing/emarketing.php'">Email Marketing</div>
			<div class="op_submenu3" onClick="location.href='../addons/emarketing/emarketing_export.php'">Email Marketing Stats</div>
			<?php endif ?>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			Campaign Tracking will track your marketing campaign click-through rates.  Simply append the generated query key to your campaign URL.  <b>IMPORTANT:</b> The landing page must have the <a href="code.php">chat icon HTML Code</a> to capture the query key for tracking the click-through rates.
		</div>

		<div style="margin-top: 25px;">
			<form>
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td width="200"><div class="td_dept_header">Name</div></td>
				<td><div class="td_dept_header">Query key to append to URL</div></td>
			</tr>
			<?php
				for ( $c = 0; $c < count( $marketings ); ++$c )
				{
					$marketing = $marketings[$c] ;
					$td1 = "td_dept_td" ;

					$edit_delete = "<a href=\"JavaScript:void(0)\" onClick=\"do_edit( $marketing[marketID], '$marketing[skey]', '$marketing[name]', '$marketing[color]' );\"><img src=\"../pics/btn_edit.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a> &nbsp; <a href=\"JavaScript:void(0)\" onClick=\"do_delete($marketing[marketID])\"><img src=\"../pics/btn_delete.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a>" ;

					print "
						<tr>
							<td class=\"$td1\" nowrap style=\"background: #$marketing[color];\">
								<div style=\"font-weight: bold; text-shadow: none;\">$marketing[name]</div>
								<div style=\"margin-top: 5px;\">$edit_delete<br><span style=\"font-size: 10px; opacity: 0.5; filter: alpha(opacity=50);\">ID: $marketing[marketID]</span></div>
							</td>
							<td class=\"$td1\">
								<input type=\"text\" style=\"background: transparent; border: 1px solid transparent; font-weight: bold; color: #6E6E6E; width: 100%;\" size=\"80\" value=\"&plk=pi-$marketing[marketID]-$marketing[skey]-m\" readonly>
								<div class=\"info_neutral\">example: https://www.your-website.com/?<span class=\"txt_blue\">&plk=pi-$marketing[marketID]-$marketing[skey]-m</span></div>
							</td>
						</tr>
					" ;
				}
				if ( $c == 0 )
					print "<tr><td colspan=7 class=\"td_dept_td\">Blank results.</td></tr>" ;
			?>
			</table>
			</form>
		</div>

		<div class="edit_wrapper" style="padding: 5px; margin-top: 55px;">
			<a name="a_edit"></a><div class="edit_title">Create/Edit Marketing Click Tracking</div>
			<div style="margin-top: 10px;">
				<form method="POST" action="marketing_click.php" id="theform">
				<input type="hidden" name="action" value="submit">
				<input type="hidden" name="marketid" id="marketid" value="0">
				<input type="hidden" name="skey" id="skey" value="">
				<input type="hidden" name="color" id="color" value="">
				<table cellspacing=0 cellpadding=5 border=0>
				<tr>
					<td>
						<b>Campaign name</b> (example: <i>Google PPC</i>)
						<div style="margin-top: 5px;"><input type="text" class="input" name="name" id="name" size="50" maxlength="40" value="" onKeyPress="return nospecials(event)"></div>
					</td>
				</tr>
				<tr>
					<td style="padding-top: 10px;">
						<b>Indication Color</b> Select the campaign reference color.
						<div style="margin-top: 5px;">
							<div id="tcolor_li_DDFFEE" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #DDFFEE;" OnClick="tcolor_focus( 'DDFFEE' )"></div>
							<div id="tcolor_li_FFE07B" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #FFE07B;" OnClick="tcolor_focus( 'FFE07B' )"></div>
							<div id="tcolor_li_A4C3E3" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #A4C3E3;" OnClick="tcolor_focus( 'A4C3E3' )"></div>
							<div id="tcolor_li_FADADB" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #FADADB;" OnClick="tcolor_focus( 'FADADB' )"></div>
							<div id="tcolor_li_FABEFF" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #FABEFF;" OnClick="tcolor_focus( 'FABEFF' )"></div>
							<div id="tcolor_li_ABE3FA" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #ABE3FA;" OnClick="tcolor_focus( 'ABE3FA' )"></div>
							<div id="tcolor_li_F9FABE" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #F9FABE;" OnClick="tcolor_focus( 'F9FABE' )"></div>
							<div id="tcolor_li_BDBEF9" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #BDBEF9;" OnClick="tcolor_focus( 'BDBEF9' )"></div>
							<div id="tcolor_li_DAB195" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #DAB195;" OnClick="tcolor_focus( 'DAB195' )"></div>
							<div id="tcolor_li_C1ADD0" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #C1ADD0;" OnClick="tcolor_focus( 'C1ADD0' )"></div>
							<div id="tcolor_li_B7E3A3" style="float: left; cursor: pointer; width: 15px; height: 15px; margin-right: 3px; border: 1px solid #C2C2C2; background: #B7E3A3;" OnClick="tcolor_focus( 'B7E3A3' )"></div>
							<div style="clear: both"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td style="padding-top: 15px;">
						<div id="div_op_online" class="info_warning"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../pics/icons/warning.gif" width="16" height="16" border="0" alt=""></td><td style="padding-left: 5px;">If an operator is online, they must logout and login again to see the new Campaigns on their operator console (during a chat session, traffic monitor, etc).</td></tr></table></div>
						<div style="padding-top: 15px;"><input type="button" value="Submit" onClick="do_submit()" class="btn"> &nbsp; &nbsp; <input type="reset" value="Reset" onClick="$( 'input#marketid' ).val(0)" class="btn"></div>
					</td>
				</tr>
				</table>
				</form>
			</div>
		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
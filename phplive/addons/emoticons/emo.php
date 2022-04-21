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
	if ( !is_file( "../../web/config.php" ) ){ HEADER("location: install.php") ; exit ; }
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

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$dept_emo = ( isset( $VALS["EMOS"] ) && $VALS["EMOS"] ) ? unserialize( $VALS["EMOS"] ) : Array() ; $addon_emo = 1 ;

	if ( $action === "update_dept_emo" )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value == "on" )
		{
			if ( !$deptid )
			{
				for ( $c = 0; $c < count( $departments ); ++$c )
				{
					$department = $departments[$c] ;
					$deptid = $department["deptID"] ;
					$dept_emo[$deptid] = 1 ;
				}
			}
			else { $dept_emo[$deptid] = 1 ; }
			Util_Vals_WriteToFile( "EMOS", serialize( $dept_emo ) ) ;
		}
		else if ( $value == "off" )
		{
			if ( !$deptid )
			{
				for ( $c = 0; $c < count( $departments ); ++$c )
				{
					$department = $departments[$c] ;
					$deptid = $department["deptID"] ;
					$dept_emo[$deptid] = 0 ;
				}
			}
			else { $dept_emo[$deptid] = 0 ; }
			Util_Vals_WriteToFile( "EMOS", serialize( $dept_emo ) ) ;
		}
		$json_data = "json_data = { \"status\": 1, \"error\": \"\" };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		$json_data = Util_Format_Trim( $json_data ) ;
		$json_data = preg_replace( "/\t/", "", $json_data ) ;
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$dept_emos_string = "" ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$deptid = $department["deptID"] ;
		if ( isset( $dept_emo[$deptid] ) )
			$dept_emos_string .= "dept_emos[$deptid] = $dept_emo[$deptid] ;" ;
		else
			$dept_emos_string .= "dept_emos[$deptid] = 1 ;" ;
	}
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
	var dept_emos = new Object ;
	<?php echo $dept_emos_string ?>

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;

		init_menu() ;
		toggle_menu_setup( "extras" ) ;
		if ( typeof( show_div ) == "function" )
			show_div( "emoticons" ) ;

		<?php if ( ( $action === "submit" ) && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>
	});

	function confirm_emo_onoff( thedeptid, thevalue )
	{
		var string_onoff = thedeptid+","+thevalue ;

		update_dept_emo( thedeptid, thevalue ) ;
		if ( thedeptid )
			dept_emos[thedeptid] = thevalue ;
		else
			$('#dept_emo_'+thedeptid+"_"+thevalue).prop('checked', false) ;
	}

	function update_dept_emo( thedeptid, thevalue )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "./emo.php",
			data: "action=update_dept_emo&deptid="+thedeptid+"&value="+thevalue+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					if ( !thedeptid )
					{
						if ( thevalue == "on" ) {
						<?php
							for ( $c = 0; $c < count( $departments ); ++$c )
							{
								$department = $departments[$c] ;
								print "\$('#dept_emo_$department[deptID]_on').prop('checked', true) ; " ;
							}
						?> }
						else {
						<?php
							for ( $c = 0; $c < count( $departments ); ++$c )
							{
								$department = $departments[$c] ;
								print "\$('#dept_emo_$department[deptID]_off').prop('checked', true) ; " ;
							}
						?> }
					}
					do_alert( 1, "Update Success" ) ;
				}
				else
					do_alert( 0, "Error [emo]. Please refresh the page and try again.") ;
			}
		});
	}
//-->
</script>
<?php include_once( "../../setup/inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["extras"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<?php include_once( "../../setup/inc_menu.php" ) ; ?>

		<div style="margin-top: 25px;">

			<div style="margin-top: 15px;">Add additional expressiveness with emoticons.  If enabled, emoticons selection will be visible during a chat session for both the visitor and the operator.</div>

			<?php if ( count( $departments ) > 1 ): ?>
			<div style="margin-top: 15px;" class="info_info">
				<div class="info_good" style="float: left; cursor: pointer;">
					<button onclick="confirm_emo_onoff(0, 'on');" class="btn"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="smile.png" width="20" height="20" border="0" alt=""></td><td style="padding-left: 5px;">Enable Emoticons for ALL Departments</td></tr></table></button>
				</div>
				<div class="info_error" style="float: left; margin-left: 10px; cursor: pointer;"><button onclick="confirm_emo_onoff(0, 'off');" class="btn">Disable Emoticons for ALL Departments</button></div>
				<div style="clear: both;"></div>
			</div>
			<?php endif ; ?>

			<?php if ( !count( $departments ) ): ?>
			<div style="margin-top: 15px;"><span class="info_error"><img src="../../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="../../setup/depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span></div>
			<?php else: ?>
			<div style="margin-top: 5px;">
				<div class="edit_title td_dept_td">Departments</div>
				<table cellspacing=0 cellpadding=0 border=0>
				<?php
					for ( $c = 0; $c < count( $departments ); ++$c )
					{
						$department = $departments[$c] ;
						$deptid = $department["deptID"] ;
						$td1 = "td_dept_td" ;

						if ( $department["name"] != "Archive" )
						{
							$checked_on = "" ;
							if ( !isset( $dept_emo[$deptid] ) || ( isset( $dept_emo[$deptid] ) && $dept_emo[$deptid] ) ) { $checked_on = "checked" ; }
							else if ( isset( $dept_emo[$deptid] ) && !$dept_emo[$deptid] ) { $checked_on = "" ; }
							else if ( !isset( $dept_emo[0] ) || ( isset( $dept_emo[0] ) && $dept_emo[0] ) ) { $checked_on = "checked" ; }
							$checked_off = ( !$checked_on ) ? "checked" : "" ;

							$div_onoff = "<div style=\"margin-top: 15px;\"><div class=\"info_good\" style=\"float: left; width: 60px; text-shadow: none; cursor: pointer;\" onclick=\"$('#dept_emo_$department[deptID]_on').prop('checked', true);confirm_emo_onoff($department[deptID], 'on')\"><input type=\"radio\" name=\"dept_emo_$department[deptID]\" id=\"dept_emo_$department[deptID]_on\" value=\"on\" $checked_on> On</div><div class=\"info_error\" style=\"float: left; margin-left: 10px; width: 60px; text-shadow: none; cursor: pointer;\" onclick=\"$('#dept_emo_$department[deptID]_off').prop('checked', true);confirm_emo_onoff($department[deptID], 'off')\"><input type=\"radio\" name=\"dept_emo_$department[deptID]\" id=\"dept_emo_$department[deptID]_off\" value=\"off\" $checked_off> Off</div><div style=\"clear: both;\"></div></div>" ;

							print "
							<tr>
								<td class=\"$td1\" nowrap>
									<div style=\"\">$department[name]</div>
								</td>
								<td class=\"$td1\">$div_onoff</td>
							</tr>
							" ;
						}
					}
				?>
				</table>
			</div>
			<?php endif ; ?>

		</div>
		<?php endif ; ?>

<?php include_once( "../../setup/inc_footer.php" ) ?>

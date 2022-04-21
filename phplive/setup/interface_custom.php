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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;
	$error = "" ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	if ( !$deptid && count( $departments ) )
		$deptid = $departments[0]["deptID"] ;

	if ( $action === "update" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

		$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;

		$rname = Util_Format_Sanatize( Util_Format_GetVar( "rname" ), "n" ) ;
		$remail = Util_Format_Sanatize( Util_Format_GetVar( "remail" ), "n" ) ;
		$rquestion = Util_Format_Sanatize( Util_Format_GetVar( "rquestion" ), "n" ) ;
		$custom_field = preg_replace( "/[',]/", "", preg_replace( "/\"/", "", Util_Format_Sanatize( Util_Format_GetVar( "custom_field" ), "notags" ) ) ) ;
		$custom_field_required = Util_Format_Sanatize( Util_Format_GetVar( "custom_field_required" ), "n" ) ;
		$custom_field2 = preg_replace( "/[',]/", "", preg_replace( "/\"/", "", Util_Format_Sanatize( Util_Format_GetVar( "custom_field2" ), "notags" ) ) ) ;
		$custom_field2_required = Util_Format_Sanatize( Util_Format_GetVar( "custom_field2_required" ), "n" ) ;
		$custom_field3 = preg_replace( "/[',]/", "", preg_replace( "/\"/", "", Util_Format_Sanatize( Util_Format_GetVar( "custom_field3" ), "notags" ) ) ) ;
		$custom_field3_required = Util_Format_Sanatize( Util_Format_GetVar( "custom_field3_required" ), "n" ) ;
		$custom_field4 = preg_replace( "/[',]/", "", preg_replace( "/\"/", "", Util_Format_Sanatize( Util_Format_GetVar( "custom_field4" ), "notags" ) ) ) ;
		$custom_field4_required = Util_Format_Sanatize( Util_Format_GetVar( "custom_field4_required" ), "n" ) ;
		$custom_field4_values = preg_replace( "/'/", "", preg_replace( "/\"/", "", Util_Format_Sanatize( Util_Format_GetVar( "custom_field4_values" ), "notags" ) ) ) ;
		if ( $custom_field4 ) { $custom_field4 = $custom_field4.",".$custom_field4_values ; }
		$prechat = Util_Format_Sanatize( Util_Format_GetVar( "prechat" ), "n" ) ;
		$custom_fields = ( $custom_field || $custom_field2 || $custom_field3 || $custom_field4 ) ? serialize( Array( "$custom_field", $custom_field_required, "$custom_field2", $custom_field2_required, "$custom_field3", $custom_field3_required, "$custom_field4", $custom_field4_required ) ) : serialize( Array() ) ;

		if ( $copy_all )
		{
			for( $c = 0; $c < count( $departments ); ++$c )
			{
				Depts_update_DeptValues( $dbh, $departments[$c]["deptID"], Array( "remail"=>$remail, "rquestion"=>$rquestion, "rname"=>$rname, "custom"=>$custom_fields ) ) ;
				Depts_update_DeptVarsValue( $dbh, $departments[$c]["deptID"], "prechat_form", $prechat ) ;
			}
		}
		else
		{
			Depts_update_DeptValues( $dbh, $deptid, Array( "remail"=>$remail, "rquestion"=>$rquestion, "rname"=>$rname, "custom"=>$custom_fields ) ) ;
			Depts_update_DeptVarsValue( $dbh, $deptid, "prechat_form", $prechat ) ;
		}
	}

	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
	$pre_chat_form = ( !isset( $deptvars['prechat_form'] ) || $deptvars['prechat_form'] ) ? 1 : 0 ;
	$custom_fields = ( isset( $deptinfo["custom"] ) && $deptinfo["custom"] ) ? unserialize( $deptinfo["custom"] ) : Array() ;
	$custom_fields_dropdown_values = "" ;
	if ( isset( $custom_fields[6] ) && preg_match( "/,/", $custom_fields[6] ) )
	{
		$custom_fields6_array = explode( ",", $custom_fields[6] ) ;
		$custom_fields[6] = $custom_fields6_array[0] ; array_shift($custom_fields6_array) ;
		$custom_fields_dropdown_values = implode(  ",",  $custom_fields6_array ) ;
	}
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
	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "interface" ) ;
		set_radio() ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;<?php endif ; ?>
	});

	function toggle_prechat( theflag )
	{
		if ( theflag )
		{
			$('#div_prechat_skip').hide() ;
			$('#div_prechat').show() ;

			$('#menu_prechat_1').removeClass("op_submenu").addClass("op_submenu_focus") ;
			$('#menu_prechat_0').removeClass("op_submenu_focus").addClass("op_submenu") ;
		}
		else
		{
			$('#div_prechat').hide() ;
			$('#div_prechat_skip').show() ;

			$('#menu_prechat_0').removeClass("op_submenu").addClass("op_submenu_focus") ;
			$('#menu_prechat_1').removeClass("op_submenu_focus").addClass("op_submenu") ;
		}
	}

	function switch_dept( theobject )
	{
		var unique = unixtime() ;
		location.href = "interface_custom.php?deptid="+theobject.value ;
	}

	function close_view() { } // dummy function needed for preview close
	function view_preview( theflag )
	{
		if ( theflag )
		{
			//
		}
		else
		{
			var custom_object = new Object ;
			custom_object["prechat"] = ( $( "#prechat_1" ).prop( "checked" ) ) ? 1 : 0 ;
			custom_object["rname"] = ( $( "#rname_1" ).prop( "checked" ) ) ? 1 : 0 ;
			custom_object["remail"] = ( $( "#remail_1" ).prop( "checked" ) ) ? 1 : 0 ;
			custom_object["rquestion"] = ( $( "#rquestion_1" ).prop( "checked" ) ) ? 1 : 0 ;
			custom_object["custom1"] = $('#custom_field').val().trim() ;
			custom_object["custom2"] = $('#custom_field2').val().trim() ;
			custom_object["custom3"] = $('#custom_field3').val().trim() ;
			custom_object["custom4"] = $('#custom_field4').val().trim() ;
			if ( $('#custom_field4_values').val().trim() )
				custom_object["custom4"] += ","+$('#custom_field4_values').val().trim() ;

			custom_object["custom1_req"] = $('#custom_field_required').val() ;
			custom_object["custom2_req"] = $('#custom_field2_required').val() ;
			custom_object["custom3_req"] = $('#custom_field3_required').val() ;
			custom_object["custom4_req"] = $('#custom_field4_required').val() ;

			document.getElementById('iframe_widget_embed').contentWindow.preview_custom( custom_object ) ;
			$('#phplive_widget_embed_iframe').fadeOut("fast").fadeIn("fast") ;
		}
	}

	function do_update()
	{
		$('#form_custom').submit() ;
	}

	function do_reset()
	{
		$('#form_custom').trigger("reset") ;
		set_radio() ;

		view_preview(0) ;
		view_preview(1) ;
	}

	function set_radio()
	{
		toggle_prechat( <?php echo isset( $deptvars['prechat_form'] ) ? 1 : 1 ; ?> ) ;
		toggle_prechat( <?php echo ( $pre_chat_form ) ? 1 : 0 ?> ) ;

		<?php if ( $deptid ): ?>
		$( "input#rname_"+<?php echo $deptinfo["rname"] ?> ).prop( "checked", true ) ;
		$( "input#remail_"+<?php echo $deptinfo["remail"] ?> ).prop( "checked", true ) ;
		$( "input#rquestion_"+<?php echo $deptinfo["rquestion"] ?> ).prop( "checked", true ) ;
		<?php endif ; ?>

	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" onClick="location.href='interface.php?jump=logo'" style="margin-left: 0px;">Logo</div>
			<div class="op_submenu" onClick="location.href='interface_themes.php'" id="menu_custom">Theme</div>
			<div class="op_submenu_focus" id="menu_custom">Form Fields</div>
			<div class="op_submenu" onClick="location.href='interface_lang.php'">Update Texts</div>
			<div class="op_submenu" onClick="location.href='code_autostart.php'" id="menu_auto">Automatic Start Chat</div>
			<div class="op_submenu" onClick="location.href='interface_gdpr.php'" id="menu_gdpr">Consent Checkbox</div>
			<div class="op_submenu" onClick="location.href='interface_chat_msg.php'">Chat End Msg</div>
			<?php if ( phpversion() >= "5.1.0" ): ?><div class="op_submenu" onClick="location.href='interface.php?jump=time'">Timezone</div><?php endif; ?>
			<div class="op_submenu" onClick="location.href='code_settings.php'">Settings</div>
			<div style="clear: both"></div>
		</div>

		<?php if ( !count( $departments ) ): ?>
		<div style="padding-top: 25px;">
			<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span>
		</div>
		<?php else: ?>
		<form method="POST" action="interface_custom.php" enctype="multipart/form-data" id="form_custom" autocomplete="off">
		<input type="hidden" name="action" value="update">
		<div style="margin-top: 25px;">
			<table cellspacing=0 cellpadding=2 border=0 width="100%">
			<tr>
				<td valign="top" width="<?php echo $VARS_CHAT_WIDTH_WIDGET ?>">
					<div id='phplive_widget_embed_iframe' style='width: <?php echo $VARS_CHAT_WIDTH_WIDGET ?>px; height: 550px; border-radius: 5px; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);'>
						<iframe id='iframe_widget_embed' name='iframe_widget_embed' style='width: 100%; height: 100%; border-radius: 5px; border: 0px;' src='<?php echo ( $deptid ) ? "../phplive.php?preview=2&embed=1&d=$deptid" : "../blank.php?bg=FFFFFF" ; ?>' scrolling='no' border=0 frameborder=0></iframe>
					</div>

					<div style="margin-top: 45px;" class="info_neutral"><b>Documentation:</b> Pre-populate the name and email, and include custom variables using JavaScript.  For more information, please visit the <a href="https://www.phplivesupport.com/knowledge_base.php?docid=42" target="_blank">Pre-populate visitor name and include custom variables documentation</a>.</div>
				</td>
				<td valign="top" width="100%" style="padding-left: 25px;" id="td_text_values">
					<?php if ( count( $departments ) > 1 ): ?>
					<div style="margin-bottom: 15px;">
						<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this )">
						<?php
							for ( $c = 0; $c < count( $departments ); ++$c )
							{
								$department = $departments[$c] ;

								if ( $department["name"] != "Archive" )
								{
									$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
									print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
								}
							}
						?>
						</select>
					</div>
					<?php else: ?>
					<input type="hidden" name="deptid" value="<?php echo $deptid ?>">
					<?php endif ; ?>

					<?php if ( $deptid ): ?>
					<div style="margin-bottom: 15px;" class="info_neutral"><big><b>&larr;</b></big> For this area, the interface preview is automatically set to <span class="info_good">Online</span> status.</div>
					<div>
						<div style="">
							<div class="<?php echo ( $pre_chat_form ) ? "op_submenu_focus" : "op_submenu" ; ?>" onclick="$('#prechat_1').prop('checked', true);toggle_prechat(1);" id="menu_prechat_1"><input type="radio" name="prechat" id="prechat_1" value="1" onClick="toggle_prechat(1)" <?php echo ( $pre_chat_form ) ? "checked" : "" ; ?> > Display the Pre-Chat Form</div>
							<div class="<?php echo ( !$pre_chat_form ) ? "op_submenu_focus" : "op_submenu" ; ?>" onclick="$('#prechat_0').prop('checked', true);toggle_prechat(0);" id="menu_prechat_0"><input type="radio" name="prechat" id="prechat_0" value="0" onClick="toggle_prechat(0)" <?php echo ( !$pre_chat_form ) ? "checked" : "" ; ?> > Hide the Pre-Chat Form</div>
							<div style="clear: both;"></div>
						</div>
						<div class="info_info round_top_none">
							<div id="div_prechat" style="display: none;">
								<div class="info_white">
									<div><span class="txt_blue" style="font-weight: bold;">Name</span>: Select "Yes" for required.  Select "No" for optional.</div>
									<div style="margin-top: 5px;">
										<div class="li_op round" style="cursor: pointer;" onclick="$('#rname_1').prop('checked', true);"><input type="radio" name="rname" id="rname_1" value="1"> Yes, required.</div>
										<div class="li_op round" style="cursor: pointer;" onclick="$('#rname_0').prop('checked', true);"><input type="radio" name="rname" id="rname_0" value="0"> Optional</div>
										<div style="clear: both;"></div>
									</div>
								</div>

								<div class="info_white" style="margin-top: 10px;">
									<div><span class="txt_blue" style="font-weight: bold;">Email</span>: Select "Yes" for required.  Select "No" to <b>hide the email field</b>.</div>
									<div style="margin-top: 5px;">
										<div class="li_op round" style="cursor: pointer;" onclick="$('#remail_1').prop('checked', true);"><input type="radio" name="remail" id="remail_1" value="1"> Yes, required.</div>
										<div class="li_op round" style="cursor: pointer;" onclick="$('#remail_0').prop('checked', true);"><input type="radio" name="remail" id="remail_0" value="0"> Hide field.</div>
										<div style="clear: both;"></div>
									</div>
								</div>

								<div class="info_white" style="margin-top: 10px;">
									<div><span class="txt_blue" style="font-weight: bold;">Question</span>:  Select "Yes" for required.  Select "No" to <b>hide the question field</b></div>
									<div style="margin-top: 5px;">
										<div class="li_op round" style="cursor: pointer;" onclick="$('#rquestion_1').prop('checked', true);"><input type="radio" name="rquestion" id="rquestion_1" value="1"> Yes, required.</div>
										<div class="li_op round" style="cursor: pointer;" onclick="$('#rquestion_0').prop('checked', true);"><input type="radio" name="rquestion" id="rquestion_0" value="0"> Hide field.</div>
										<div style="clear: both;"></div>
									</div>
								</div>

								<div style="margin-top: 15px; text-shadow: none;">Addtional Form Fields (example: Phone, Ticket ID, Account Number)</div>
								<div style="margin-top: 10px;">
									<table cellspacing=0 cellpadding=2 border=0>
									<tr>
										<td><input type="text" class="input" size="28" maxlength="70" id="custom_field" name="custom_field" value="<?php echo isset( $custom_fields[0] ) ? $custom_fields[0] : "" ; ?>" onKeyPress="return noquotestags(event)"></td>
										<td style="padding-left: 10px;"><select name="custom_field_required" id="custom_field_required" class="select"><option value=1>required to chat</option><option value=0 <?php echo ( isset( $custom_fields[1] ) && !$custom_fields[1] ) ? "selected" : "" ; ?>>optional</option></select></td>
									</tr>
									<tr>
										<td><input type="text" class="input" size="28" maxlength="70" id="custom_field2" name="custom_field2" value="<?php echo isset( $custom_fields[2] ) ? $custom_fields[2] : "" ; ?>" onKeyPress="return noquotestags(event)"></td>
										<td style="padding-left: 10px;"><select name="custom_field2_required" id="custom_field2_required" class="select"><option value=1>required to chat</option><option value=0 <?php echo ( isset( $custom_fields[3] ) && !$custom_fields[3] ) ? "selected" : "" ; ?>>optional</option></select></td>
									</tr>
									<tr>
										<td><input type="text" class="input" size="28" maxlength="70" id="custom_field3" name="custom_field3" value="<?php echo isset( $custom_fields[4] ) ? $custom_fields[4] : "" ; ?>" onKeyPress="return noquotestags(event)"></td>
										<td style="padding-left: 10px;"><select name="custom_field3_required" id="custom_field3_required" class="select"><option value=1>required to chat</option><option value=0 <?php echo ( isset( $custom_fields[5] ) && !$custom_fields[5] ) ? "selected" : "" ; ?>>optional</option></select></td>
									</tr>
									</table>
									<div style="margin-top: 10px;" class="info_neutral">
										Dropdown Menu (example: Are you an existing customer?)
										<div style="margin-top: 10px;">
											<table cellspacing=0 cellpadding=2 border=0>
											<tr>
												<td valign="bottom">
													<div><input type="text" class="input" size="34" maxlength="70" id="custom_field4" name="custom_field4" value="<?php echo isset( $custom_fields[6] ) ? $custom_fields[6] : "" ; ?>" onKeyPress="return noquotestags(event)"></div>
												</td>
												<td style="padding-left: 10px;" valign="bottom"><select name="custom_field4_required" id="custom_field4_required" class="select"><option value=1>required to chat</option><option value=0 <?php echo ( isset( $custom_fields[7] ) && !$custom_fields[7] ) ? "selected" : "" ; ?>>optional</option></select></td>
											</tr>
											<tr>
												<td colspan=2 style="padding-top: 5px;">
													<img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> Separate each menu option with a comma (example: Yes, No).<br>
													<div style="margin-top: 5px;"><input type="text" class="input" size="34" maxlength="400" id="custom_field4_values" name="custom_field4_values" value="<?php echo $custom_fields_dropdown_values ?>" onKeyPress="return noquotestags(event)"></div>
												</td>
											</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
							<div id="div_prechat_skip" style="display: none;">
								Do not display the pre-chat form. To request a chat session, the visitor only needs to click the "Start Chat" button.
								<div style="margin-top: 5px;" class="info_warning">
									<b>NOTE:</b> The <a href="interface_lang.php" target="_parent">Welcome Greeting message and the Sub Text</a> will be displayed prior to starting a chat session.  To skip the pre-chat form entirely and start the chat session automatically, the feature is located at the <a href="code_autostart.php" target="_parent">Automatic Start Chat</a> area.
								</div>
							</div>
							<div style="margin-top: 15px;">
								<span class="info_menu_focus" style="padding: 6px;">&#8592; <a href="JavaScript:void(0)" onClick="view_preview(0)">view how it will look</a></span>
							</div>
						</div>
					</div>

					<div style="margin-top: 25px;">
						<?php if ( count( $departments ) > 1 ) : ?>
						<div style="margin-top: 25px;"><input type="checkbox" id="copy_all" name="copy_all" value=1> copy this update to all departments</div>
						<?php endif ; ?>
						<div style="margin-top: 25px;">
							<div style=""><button type="button" class="btn" onClick="do_update()">Update Changes</button> &nbsp; &nbsp; <button type="button" class="btn" onClick="do_reset()">Reset</button></div>
						</div>
					</div>

					<?php endif ; ?>

				</td>
			</tr>
			</table>
		</div>
		</form>

		<?php endif ; ?>

		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
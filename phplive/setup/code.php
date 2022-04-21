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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$jump = ( Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ) ? Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) : "main" ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$proto = Util_Format_Sanatize( Util_Format_GetVar( "proto" ), "n" ) ;
	$position = Util_Format_Sanatize( Util_Format_GetVar( "position" ), "n" ) ;
	$error = "" ; $display = 0 ;

	if ( !isset( $VALS["OB_CLEAN"] ) ) { $VALS["OB_CLEAN"] = "on" ; }
	if ( !isset( $VALS["EMBED_POS"] ) ) { $VALS["EMBED_POS"] = "right" ; }

	$code_maps = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["code_maps"] ) && $VALS_ADDONS["code_maps"] ) ? unserialize( base64_decode( $VALS_ADDONS["code_maps"] ) ) : Array() ;
	/***************************************/
	/* a check to make sure code.php is not in condition to limit confusion */
	$code_mapper_coexists = 0 ;
	foreach ( $code_maps as $mapid => $data )
	{
		LIST( $thisdeptid, $map_string ) = explode( ",", $data ) ;
		$map_string_array = explode( "%2C", $map_string ) ;
		for( $c = 0; $c < count( $map_string_array ); ++$c )
		{
			$map_string_match = $map_string_array[$c] ;
			if ( $map_string_match && preg_match( "/$map_string_match/", "code.php" ) )
			{
				$code_mapper_coexists = 1 ;
				break 2 ;
			}
		}
	}
	/***************************************/

	$departments = Depts_get_AllDepts( $dbh ) ;
	$ops_assigned = 0 ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$ops = Depts_get_DeptOps( $dbh, $department["deptID"] ) ;
		if ( count( $ops ) )
			$ops_assigned = 1 ;
	}
	$deptinfo = Array() ;
	if ( $deptid )
		$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;

	$total_ops = Ops_get_TotalOps( $dbh ) ;
	$total_ops_online = Ops_get_itr_AnyOpsOnline( $dbh, $deptid ) ;

	$dept_query = $deptid ;
	if ( $action === "update_proto" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Vars/update.php" ) ;

		$vars = Util_Format_Get_Vars( $dbh ) ;
		if ( ( $proto != $vars["code"] ) || !is_numeric( $vars["code"] ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

			$error = "" ;
			if ( !$proto )
			{
				$error = ( Util_Vals_WriteToConfFile( "BASE_URL", preg_replace( "/^(http:\/\/)|(https:\/\/)/i", "//", $CONF["BASE_URL"] ) ) ) ? "" : "Could not write to config file." ;
			}
			else if ( $proto == 1 )
				$error = ( Util_Vals_WriteToConfFile( "BASE_URL", preg_replace( "/^(https:\/\/)|(http:\/\/)|(\/\/)/i", "http://", $CONF["BASE_URL"] ) ) ) ? "" : "Could not write to config file." ;
			else if ( $proto == 2 )
			{
				$error = ( Util_Vals_WriteToConfFile( "BASE_URL", preg_replace( "/^(https:\/\/)|(http:\/\/)|(\/\/)/i", "https://", $CONF["BASE_URL"] ) ) ) ? "" : "Could not write to config file." ;
			}
			if ( !$error ) { Vars_update_Var( $dbh, "code", $proto ) ; }
		}
		Vars_update_Var( $dbh, "position", $position ) ;
	}
	else if ( $action === "add_extra_departments" )
	{
		$deptids = Util_Format_Sanatize( Util_Format_GetVar( "deptids" ), "a" ) ;

		$dept_query = "" ;
		for ( $c = 0; $c < count( $deptids ); ++$c )
			$dept_query .= $deptids[$c]."010" ;
	}
	else if ( $action === "proto_error" )
	{
		$error = "Could not detect HTTPS (SSL) support on this server." ;
	}
	else if ( $action === "update_ob_clean" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "ln" ) ;

		if ( $value && Util_Vals_WriteToFile( "OB_CLEAN", Util_Format_Trim( $value ) ) )
			$VALS["OB_CLEAN"] = $value ;
		else
			$error = "Could not write to vals file [OB: $value]." ;
	}

	/*************/
	/* HTML Code */
	$position_css = "" ;
	$vars = Util_Format_Get_Vars( $dbh ) ;
	if ( isset( $vars["code"] ) )
	{
		$proto = $vars["code"] ;
		if ( !is_numeric( $proto ) ) { $proto = 0 ; }
		switch ( $vars["position"] )
		{
			case 2:
				$position_css = " position: fixed; bottom: 0px; right: $VARS_CHAT_PADDING_WIDGET"."px; z-index: 20000000;" ;
				break ;
			case 3:
				$position_css = " position: fixed; bottom: 0px; left: $VARS_CHAT_PADDING_WIDGET"."px; z-index: 20000000;" ;
				break ;
			case 4:
				$position_css = " position: fixed; top: 0px; right: $VARS_CHAT_PADDING_WIDGET"."px; z-index: 20000000;" ;
				break ;
			case 5:
				$position_css = " position: fixed; top: 0px; left: $VARS_CHAT_PADDING_WIDGET"."px; z-index: 20000000;" ;
				break ;
			case 6:
				$position_css = " position: fixed; top: 50%; left: 0px; z-index: 20000000;" ;
				break ;
			case 7:
				$position_css = " position: fixed; top: 50%; right: 0px; z-index: 20000000;" ;
				break ;
			default:
				$position_css = "" ;
		}

		// automatic fix for toggle
		if ( !$proto && preg_match( "/^http:/", $CONF["BASE_URL"] ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Vars/update.php" ) ;
			$error = ( Util_Vals_WriteToConfFile( "BASE_URL", preg_replace( "/^(http:)/i", "", $CONF["BASE_URL"] ) ) ) ? "" : "Could not write to config file." ;
		}
	}

	$base_url = $CONF["BASE_URL"] ;
	$source_url = "$base_url/js/phplive_v2.js.php?v={$deptid}%7C{$now}%7C{$proto}%7C" ;
	$code = "&lt;!-- BEGIN PHP Live! HTML Code [V3] --&gt;-nl-&lt;span style=\"color: #0000FF; text-decoration: underline; line-height: 0px !important; cursor: pointer;$position_css\" id=\"phplive_btn_$now\"&gt;&lt;/span&gt;-nl-&lt;script data-cfasync=\"false\" type=\"text/javascript\"&gt;-nl--nl-(function() {-nl-var phplive_e_$now = document.createElement(\"script\") ;-nl-phplive_e_$now.type = \"text/javascript\" ;-nl-phplive_e_$now.async = true ;-nl-phplive_e_$now.src = \"$source_url%%text_string%%&\" ;-nl-document.getElementById(\"phplive_btn_$now\").appendChild( phplive_e_$now ) ;-nl-if ( [].filter ) { document.getElementById(\"phplive_btn_$now\").addEventListener( \"click\", function(){ phplive_launch_chat_$deptid() } ) ; } else { document.getElementById(\"phplive_btn_$now\").attachEvent( \"onclick\", function(){ phplive_launch_chat_$deptid() } ) ; }-nl-})() ;-nl-//phplive_auto-nl-&lt;/script&gt;-nl-&lt;!-- END PHP Live! HTML Code [V3] --&gt;" ;

	if ( $proto == 1 ) { $base_url = preg_replace( "/(http:)|(https:)/", "http:", $base_url ) ; }
	else if ( $proto == 2 ) { $base_url = preg_replace( "/(http:)|(https:)/", "https:", $base_url ) ; }
	else { $base_url = preg_replace( "/(http:)|(https:)/", "", $base_url ) ; }

	$thecode = preg_replace( "/%%base_url%%/", $base_url, $code ) ;
	$code_html = preg_replace( "/&lt;/", "<", $thecode ) ;
	$code_html = preg_replace( "/&gt;/", ">", $code_html ) ;
	$code_html = preg_replace( "/-nl-/", "\r\n", $code_html ) ;
	/* HTML Code */
	/*************/
	
	$online = ( isset( $VALS['ONLINE'] ) && $VALS['ONLINE'] ) ? unserialize( $VALS['ONLINE'] ) : Array( ) ;
	$offline = ( isset( $VALS['OFFLINE'] ) && $VALS['OFFLINE'] ) ? unserialize( $VALS['OFFLINE'] ) : Array() ;
	$offline_option = "icon" ;
	if ( isset( $offline[$deptid] ) )
	{
		if ( !preg_match( "/^(icon|hide)$/", $offline[$deptid] ) ) { $offline_option = "redirect" ; }
		else{ $offline_option = $offline[$deptid] ; }
	}
	else
	{
		if ( isset( $offline[0] ) )
		{
			if ( !preg_match( "/^(icon|hide)$/", $offline[0] ) ) { $offline_option = "redirect" ; }
			else{ $offline_option = $offline[0] ; }
		}
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var global_ob_clean = "<?php echo $VALS["OB_CLEAN"] ?>" ;
	var global_embed_pos = "<?php echo $VALS["EMBED_POS"] ; ?>" ;
	var global_proto = "<?php echo $proto ?>" ;
	var global_position = <?php echo isset( $vars["position"] ) ? $vars["position"] : 1 ?> ;
	var phplive_html_code_global_div ; // to not show the chat icon for plain text embed close

	var st_proto_verify ;
	var thecode = '<?php echo $thecode ?>' ;
	thecode = thecode.replace( /-nl-/g, "\r\n" ) ;
	thecode = thecode.replace( /&lt;/g, "<" ) ;
	thecode = thecode.replace( /&gt;/g, ">" ) ;
	var phplive_browser = navigator.appVersion ; var phplive_mime_types = "" ;
	var phplive_display_width = screen.availWidth ; var phplive_display_height = screen.availHeight ; var phplive_display_color = screen.colorDepth ; var phplive_timezone = new Date().getTimezoneOffset() ;
	if ( navigator.mimeTypes.length > 0 ) { for (var x=0; x < navigator.mimeTypes.length; x++) { phplive_mime_types += navigator.mimeTypes[x].description ; } }
	var phplive_browser_gl = ( typeof( document.createElement("canvas").getContext ) != "undefined" ) ? document.createElement("canvas").getContext("webgl") : new Object ; var phplive_browser_gl_string = "" ; for ( var phplive_browser_gl in phplive_browser_gl ) { phplive_browser_gl_string += phplive_browser_gl+phplive_browser_gl[phplive_browser_gl] ; }
	var phplive_browser_token = phplive_md5( phplive_display_width+phplive_display_height+phplive_display_color+phplive_timezone+phplive_browser+phplive_mime_types+phplive_browser_gl_string ) ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		check_protocol() ;
		init_menu() ;
		toggle_menu_setup( "html" ) ;

		populate_code( "standard" ) ;
		if ( typeof( show_div ) == "function" )
			show_div( "code_main" ) ;

		<?php if ( ( ( $action === "switch" ) || ( $action === "update_proto" ) ) && !$error ): ?>
			$('#div_new_code').delay(200).fadeIn("slow") ; do_alert(1, "New HTML Code Generated", 3) ;
		<?php elseif ( ( $action === "update_proto" ) && $error ): ?>
			do_alert(0, "<?php echo $error ?>") ;
		<?php elseif ( ( $action === "proto_error" ) && $error ): ?>
			do_alert( 0, "<?php echo $error ?>" ) ;
		<?php elseif ( $action && !$error ): ?>
			do_alert( 1, "Update Success" ) ;
		<?php endif ; ?>
	});

	function switch_dept( theobject )
	{
		location.href = "code.php?deptid="+theobject.value+"&action=switch&proto=<?php echo $proto ?>&"+unixtime() ;
	}

	function populate_code( thetextarea )
	{
		var thiscode = thecode.replace( /\/\/phplive_auto/, "" ) ;
		if ( thetextarea == "standard" )
		{
			var code = thiscode.replace( /%%text_string%%/g, "" ) ;
			$('#textarea_code_'+thetextarea).val( code ) ;
		}
		else if ( thetextarea == "text" )
		{
			var text = encodeURI( $('#code_text').val() ) ;
			var code = thiscode.replace( /%%text_string%%/g, text ) ;
			code = code.replace( / line-height: (.*?);/, "" ) ;

			if ( text == "" )
				do_alert( 0, "Please provide the text." ) ;
			else
			{
				$('#code_text_code').show() ;

				<?php if ( !$total_ops_online && ( $offline_option == "hide" ) ): ?>
				$('#html_code_text_output').html("<span onClick=\"$('#div_text_link_offline').fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast')\" style=\"cursor: pointer;\">"+$('#code_text').val()+"</span>") ;
				<?php else: ?>
				$('#html_code_text_output').html("<span onClick=\"phplive_launch_chat_<?php echo $deptid ?>(0)\" style=\"cursor: pointer;\">"+$('#code_text').val()+"</span>") ;
				<?php endif ; ?>

				$('#textarea_code_'+thetextarea).val( code ) ;
				$('#html_code_text_output_tip').show() ;
				do_alert(1, "New HTML Code Generated", 3) ;
			}
		}
		$('#div_textarea_text_wrapper').show() ;
	}

	function input_text_listen_text( e )
	{
		var key = -1 ;
		var shift ;

		key = e.keyCode ;
		shift = e.shiftKey ;

		if ( !shift && ( ( key == 13 ) || ( key == 10 ) ) )
			$('#btn_generate').click() ;
	}

	function toggle_code( theproto )
	{
		var unique = unixtime() ;
		var proto = $('input[name=proto]:checked', '#form_proto').val() ;
		var position = $('#position').val() ;

		if ( ( global_proto != proto ) || ( global_position != position ) )
		{
			var url = "<?php echo $CONF["BASE_URL"] ?>" ;
			var url_https = ( proto == 2 ) ? url.replace( /^(http:\/\/)|(\/\/)/i, "https://" ) : url ;

			$('#proto_verify').show() ;
			$('#iframe_proto_verify').attr('src', url_https+"/blank.php").ready(function() {
				toggle_code_doit() ;
			});
			if ( typeof( st_proto_verify ) != "undefined" ) { clearTimeout( st_proto_verify ) ; }
			st_proto_verify = setTimeout( function(){ location.href = "./code.php?action=proto_error" }, 10000 ) ;
		}
	}

	function toggle_code_doit()
	{
		var unique = unixtime() ;
		var proto = $('input[name=proto]:checked', '#form_proto').val() ;
		var position = $('#position').val() ;

		var url = "<?php echo $CONF["BASE_URL"] ?>" ;
		var url_https = ( proto == 2 ) ? url.replace( /^((http:\/\/)|(\/\/))/i, "https://" ) : url ;

		location.href = url_https+"/setup/code.php?action=update_proto&deptid=<?php echo $deptid ?>&proto="+proto+"&position="+position+"&"+unique ;
	}

	function show_html_code()
	{
		$('#btn_show').hide() ;
		$('#div_html_code').show() ;
	}

	function open_departments( thedeptid )
	{
		var pos = $('#department_add_btn').position() ;
		var top = pos.top - 45 ;
		var left = pos.left + $('#department_add_btn').outerWidth() + 25 ;

		$('#div_departments').css({'top': top, 'left': left}).fadeIn("fast") ;
	}

	function check_protocol()
	{
		var url = window.location.href ;
		var url_https = ( url.match( /^https:/i ) ) ? 1 : 0 ;

		// check for situations where the server always redirects http to https
		if ( url_https && ( <?php echo $proto ?> == 1 ) )
		{
			$('#div_always_https').show() ;
			$('#proto_radio_https').prop("checked", true) ;

			setTimeout( function(){ toggle_code() ; }, 3000 ) ;
		} return true ;
	}

	function do_redirect()
	{
		location.href = "code_invite.php?token="+phplive_browser_token ;
	}

	function toggle_html_code( thediv )
	{
		phplive_html_code_global_div = thediv ;
		var divs = Array( "standard", "text", "noj", "direct" ) ;

		for ( var c = 0; c < divs.length; ++c )
		{
			$('#menu_html_code_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu3') ;
			$('#div_code_'+divs[c]).hide() ;
		}

		if ( thediv == "standard" )
		{
			$('#phplive_btn_<?php echo $now ?>').show() ;
			$('#phplive_btn_<?php echo $now ?>_clone').show() ;
		}
		else
		{
			$('#phplive_btn_<?php echo $now ?>').hide() ;
			$('#phplive_btn_<?php echo $now ?>_clone').hide() ;
		}

		$('#menu_html_code_'+thediv).removeClass('op_submenu3').addClass('op_submenu_focus') ;
		$('#div_code_'+thediv).show() ;
	}

	function toggle_ob()
	{
		if ( $('#settings_ob').is(':visible') )
			$('#settings_ob').hide() ;
		else
		{
			$('#settings_ob').show() ;
			$("html, body").animate( { scrollTop: $(document).height() }, "slow" ) ;
		}
	}

	function confirm_ob_clean( theob_clean )
	{
		if ( global_ob_clean != theob_clean )
		{
			location.href = "code.php?action=update_ob_clean&value="+theob_clean+"&deptid=<?php echo $deptid ?>&proto=<?php echo $proto ?>&position=<?php echo isset( $vars["position"] ) ? $vars["position"] : 1 ; ?>&"+unixtime() ;
		}
	}

	function confirm_embed_pos( theembed_pos )
	{
		if ( global_embed_pos != theembed_pos )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_embed_pos&value="+theembed_pos+"&"+unixtime(),
				success: function(data){
					location.href = "code.php?deptid=<?php echo $deptid ?>&action=embed_pos" ;
				}
			});
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["code"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<?php if ( !count( $departments ) ): ?>
		<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> You must first add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to generate the chat icon HTML Code.</span>
		<?php elseif ( !$total_ops ): ?>
		<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> You must first add an <a href="ops.php" style="color: #FFFFFF;">Operator</a> to generate the chat icon HTML Code.</span>
		<?php elseif ( !$ops_assigned ): ?>
		<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> You must first <a href="ops.php?jump=assign" style="color: #FFFFFF;">assign an operator to a department</a> to generate the chat icon HTML Code.</span>
		<?php
			else:
			$display = 1 ; $select_depts = 1 ;
			if ( count( $departments ) == 1 )
			{
				$department = $departments[0] ;
				if ( $department["visible"] )
					$select_depts = 0 ;
			}
		?>
		<?php endif ; ?>

		<?php
			if ( $display ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_menu_code.php" ) ;
		?>

		<div style="margin-top: 25px;">

			<div id="div_code_options">
				<form method="POST" action="" id="form_theform">
				<?php if ( $select_depts ): ?>
				<div>
					<span class="info_menu_focus_shadow"><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> Department</span>
				</div>
				<div style="margin-top: 5px;">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td><select name="deptid" id="deptid" style="font-size: 16px;" OnChange="switch_dept( this )">
							<option value="0">All Departments</option>
							<?php
								if ( $select_depts )
								{
									for ( $c = 0; $c < count( $departments ); ++$c )
									{
										$department = $departments[$c] ;

										if ( $department["name"] != "Archive" )
										{
											$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
											print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
										}
									}
									$deptgroupinfo = Array() ;
									for ( $c = 0; $c < count( $dept_groups ); ++$c )
									{
										$dept_group = $dept_groups[$c] ;
										$selected = ( $deptid == $dept_group["groupID"] ) ? "selected" : "" ;
										if ( $selected ) { $deptgroupinfo["deptID"] = $deptid ; $deptgroupinfo["name"] = $dept_group["name"] ; }
										print "<option value=\"$dept_group[groupID]\" $selected>$dept_group[name] (Department Group)</option>" ;
									}
								}
							?>
							</select>
							<?php if ( count( $departments ) && !$deptid ): ?>
							&nbsp; <span style="font-size: 18px; font-weight: bold;">&larr;</span> (optional) Select a department to generate a <b>Department Specific HTML Code</b>.
							<?php endif ; ?>
						</td>
						<?php if ( $deptid && ( isset( $deptinfo["deptID"] ) || isset( $deptgroupinfo["deptID"] ) ) ): ?>
						<td style="padding-left: 15px;">
							<div><span class="info_box" style="padding: 2px;">This is a Department Specific HTML Code</span> because a department has been selected.</div>
							<div style="margin-top: 10px;">Visitors will be automatically routed to the <b><?php echo isset( $deptgroupinfo["name"] ) ? $deptgroupinfo["name"] : $deptinfo["name"] ; ?></b> department when the chat icon is clicked.</div>
						</td>
						<?php endif ; ?>
					</tr>
					</table>
				</div>
				<?php endif ; ?>
				</form>
			</div>
			<div style="margin-top: 25px;">
				<form id="proto_pos">
				<div>
					<span class="info_menu_focus_shadow"><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> Chat Icon Position</span>
				</div>
				<div style="margin-top: 5px;">
					<select name="position" id="position" onChange="toggle_code()" style="font-size: 16px;">
						<option value="1" <?php echo ( $vars["position"] == 1 ) ? "selected" : "" ; ?>>Display the chat icon where the HTML code is placed on the page.</option>
						<option value="2" <?php echo ( $vars["position"] == 2 ) ? "selected" : "" ; ?>>Bottom Right</option>
						<option value="3" <?php echo ( $vars["position"] == 3 ) ? "selected" : "" ; ?>>Bottom Left</option>
						<option value="4" <?php echo ( $vars["position"] == 4 ) ? "selected" : "" ; ?>>Top Right</option>
						<option value="5" <?php echo ( $vars["position"] == 5 ) ? "selected" : "" ; ?>>Top Left</option>
						<option value="6" <?php echo ( $vars["position"] == 6 ) ? "selected" : "" ; ?>>Center Left</option>
						<option value="7" <?php echo ( $vars["position"] == 7 ) ? "selected" : "" ; ?>>Center Right</option>
					</select>
				</div>
				</form>
			</div>
			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td width="48%" valign="top">
						<form id="form_proto">
						<div>
							<span class="info_menu_focus_shadow"><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> HTTP or HTTPS</span>
						</div>
						<div class="info_neutral round_top_none" style="margin-top: 10px;">
							<div style="display: none; margin-top: 5px;" class="info_error" id="div_always_https">System has detected an Always HTTPS environment.</div>
							<div style="margin-top: 5px;"><input type="radio" name="proto" value="0" <?php echo ( !isset( $vars["code"] ) || !$vars["code"] ) ? "checked" : "" ?> onClick="toggle_code()"> Toggle <b><i>HTTP and HTTPS</i></b> based on the webpage URL</div>
							<div style="margin-top: 5px;"><input type="radio" id="proto_radio_https" name="proto" value="2" <?php echo ( $vars["code"] == 2 ) ? "checked" : "" ?> onClick="toggle_code()"> Always <b><i>HTTPS</i></b> <img src="../pics/icons/lock.png" width="16" height="16" border="0" alt=""> secure chats (SSL enabled servers)</div>
						</div>
						</form>
					</td>
					<td width="4%">&nbsp;</td>
					<td width="48%" valign="top">
						<div>
							<span class="info_menu_focus_shadow"><img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""> Embed Chat Window Position</span>
						</div>
						<div class="info_neutral round_top_none" style="margin-top: 10px;">
							<div style="margin-top: 5px;">When the chat icon is clicked, open the embed chat window at the bottom right or the bottom left of the page.  This setting only applies if the <a href="icons.php?jump=settings">Chat Icon Window Setting</a> is set to <b>embed</b>.</div>
							<div style="margin-top: 5px;">
								<div class="info_clear" style="float: left; cursor: pointer;" onclick="$('#embed_pos_left').prop('checked', true);confirm_embed_pos('left');"><input type="radio" name="embed_pos" id="embed_pos_left" value="left" <?php echo ( $VALS["EMBED_POS"] == "left" ) ? "checked" : "" ?>> Bottom Left</div>
								<div class="info_clear" style="float: left; margin-left: 10px; cursor: pointer;" onclick="$('#embed_pos_right').prop('checked', true);confirm_embed_pos('right');"><input type="radio" name="embed_pos" id="embed_pos_right" value="right" <?php echo ( $VALS["EMBED_POS"] != "left" ) ? "checked" : "" ?>> Bottom Right</div>
								<div style="clear: both;"></div>
							</div>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">

				<div style="display: none; margin-bottom: 5px; margin-bottom: 15px; padding: 15px; text-align: center; border: 2px solid #75AE7B;" class="info_good" id="div_new_code">
					<div class="edit_title">New HTML Code Generated.</div>
					<div style="margin-top: 5px;">To use these new settings, copy the new HTML Code (displayed below) and paste or replace the code on your webpages.</div>
				</div>

				<?php if ( $code_mapper_coexists ): ?>
				<div style="margin-top: 25px; margin-bottom: 25px;"><span class="info_error"><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> <b>Note:</b></span> If the chat icon and the chat request window is not displaying the selected department, the <a href="../addons/code_mapper/code_mapper.php">HTML Code Mapper addon</a> contains <code>code.php</code> as a mapped condition.</div>
				<?php endif ; ?>

				<div style="margin-bottom: 5px;">
					<div style="margin-left: 0px; margin-top: 5px; padding: 8px; font-size: 12px; font-weight: normal; cursor: pointer;" class="op_submenu_focus" onClick="toggle_html_code('standard')" id="menu_html_code_standard">Standard HTML Code (recommended)</div>
					<div style="margin-top: 5px; padding: 8px; font-size: 12px; font-weight: normal; cursor: pointer;" class="op_submenu3" onClick="toggle_html_code('noj')" id="menu_html_code_noj">No JavaScript</div>
					<div style="margin-top: 5px; padding: 8px; font-size: 12px; font-weight: normal; cursor: pointer;" class="op_submenu3" onClick="toggle_html_code('direct')" id="menu_html_code_direct">URL Link</div>
					<div style="margin-top: 5px; padding: 8px; font-size: 12px; font-weight: normal; cursor: pointer;" class="op_submenu3" onClick="toggle_html_code('text')" id="menu_html_code_text">Plain Text Link</div>
					<div style="clear: both;"></div>
				</div>

				<div style="padding: 10px;" class="info_misc">
					<div style="font-size: 26px; font-weight: bold;"><img src="../pics/icons/code.png" width="16" height="16" border="0" alt=""> Copy/paste the below HTML Code to your webpages.</div>
					<div style="margin-bottom: 5px;">For best results, it is recommended to paste the HTML Code onto all of your webpages, anywhere after the &lt;body&gt; tag and before the closing &lt;/body&gt; tag.  For multiple chat icons on the same page, please reference the <a href="http://www.phplivesupport.com/r.php?r=multi" target="new" style="color: #FFFFFF;"><img src="../pics/icons/view.png" width="16" height="16" border="0" alt=""> documentation</a>.</div>
					<div id="div_code_standard">
						<div><textarea wrap="virtual" id="textarea_code_standard" style="font-size: 10px; padding: 20px; width: 860px; height: 240px; resize: none;" onMouseDown="setTimeout(function(){ $('#textarea_code_standard').select(); }, 200);" readonly></textarea></div>

						<?php if ( isset( $deptinfo["deptID"] ) && !$deptinfo["visible"] ): ?>
						<div class="info_error" style="margin-top: 15px;"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> This department is <a href="depts.php?ftab=vis" style="color: #FFFFFF;">not visible for selection</a> but visitors can still reach this department with the above HTML Code.</div>
						<?php endif ; ?>

						<div style="margin-top: 10px;">The above HTML Code will produce the following status icon.</div>

						<?php if ( !$total_ops_online && ( $offline_option == "hide" ) ): ?>
						<div class="info_error" style="margin-top: 5px;">Reminder: Offline chat icon is not displayed based on the current <a href="icons.php?deptid=<?php echo $deptid ?>&jump=settings" style="color: #FFFFFF;">offline setting</a>.</div>
						<?php endif; ?>
						<div style="margin-top: 5px;" id="output_code"><?php echo preg_replace( "/%%text_string%%/", "", $code_html ) ?></div>
					</div>

					<div id="div_code_text" style="display: none;">
						<div id="div_textarea_text_wrapper" style="display: none;"><textarea wrap="virtual" id="textarea_code_text" style="font-size: 10px; padding: 20px; width: 860px; height: 200px; resize: none;" onMouseDown="setTimeout(function(){ $('#textarea_code_text').select(); }, 200);" readonly></textarea></div>

						<div style="margin-top: 10px;">
							<div class="info_box" style="display: none; margin-bottom: 15px; width: 70%;" id="html_code_text_output_tip">
								<img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> To achieve design consistency with your website, modify the &lt;span&gt; style portion of the above code.
							</div>
							<div><input type="text" class="input" size="25" maxlength="155" id="code_text" onKeydown="input_text_listen_text(event);" value="Click for Live Chat"> <input type="button" value="Generate" onClick="populate_code('text')" id="btn_generate" class="btn"></div>
							<div style="margin-top: 10px;">
								Example: <i>"Click for Live Chat"</i>
								<div id="code_text_code" style="display: none; margin-top: 10px;">The above code will produce the following text link.</div>
								<div id="html_code_text_output" style="margin-top: 5px; color: #0000FF; text-decoration: underline;"></div>
								<?php if ( !$total_ops_online && ( $offline_option == "hide" ) ): ?>
								<div class="info_error" style="margin-top: 15px;" id="div_text_link_offline">Reminder: When chat is offline, the text link will not be displayed based on the current <a href="icons.php?deptid=<?php echo $deptid ?>&jump=settings" style="color: #FFFFFF;">offline setting</a>.</div>
								<?php endif ; ?>
							</div>
							<div style="margin-top: 25px; width: 70%;" class="info_blue">
								<div class="title"><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> Alternative Method: <a href="icons.php?deptid=<?php echo $deptid ?>">Chat Icon With Text</a></div>
							</div>
						</div>
					</div>

					<div id="div_code_noj" style="display: none;">
						<textarea wrap="virtual" id="textarea_code_plain" style="font-size: 10px; padding: 20px; width: 860px; height: 85px; resize: none;" onMouseDown="setTimeout(function(){ $('#textarea_code_plain').select(); }, 200);" readonly>&lt;!-- BEGIN PHP Live! HTML Code --&gt;
<a href="<?php echo $base_url ?>/phplive.php?d=<?php echo $dept_query ?>&onpage=livechatimagelink&title=Live+Chat+Image+Link" target="_blank"><img src="<?php echo $base_url ?>/ajax/image.php?d=<?php echo $dept_query ?>" border=0 alt="Live Chat" title="Live Chat"></a>
&lt;!-- END PHP Live! HTML Code --&gt;</textarea>

						<div style="margin-top: 10px;">
							<img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Automatic chat invite, operator chat invite and real-time visitor footprint monitor/tracking will not be available for this code option due to the lack of JavaScript.
						</div>
						<div style="margin-top: 15px;">The above HTML Code will produce the following status icon.</div>
						<div style="margin-top: 5px;"><a href="<?php echo $base_url ?>/phplive.php?d=<?php echo $dept_query ?>&onpage=livechatimagelink&title=Live+Chat+Image+Link" target="_blank"><img src="<?php echo $base_url ?>/ajax/image.php?d=<?php echo $dept_query ?>" border=0 alt="Live Chat" title="Live Chat"></a></div>
					</div>

					<div id="div_code_direct" style="display: none;">
						<div style=""><textarea wrap="virtual" id="textarea_code_direct" style="padding: 20px; width: 860px; height: 55px; resize: none;" onMouseDown="setTimeout(function(){ $('#textarea_code_direct').select(); }, 200);" readonly><?php echo $base_url ?>/phplive.php?d=<?php echo $dept_query ?>&onpage=livechatimagelink&title=Live+Chat+Direct+Link</textarea></div>

						<div style="margin-top: 10px;">URL link to request a chat.</div>
					</div>
				</div>

				<div style="margin-top: 15px;"><span class="info_warning"><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""> <b>Note:</b></span> If the chat icon is not displaying, try updating the <a href="JavaScript:void(0)" onClick="toggle_ob()" style="">Image Output OB Clean Setting</a></div>

				<div style="display: none; margin-top: 15px;" class="info_info" id="settings_ob">
					<div style="font-size: 14px; font-weight: bold;">Image Output "OB Clean"</div>

					<div style="margin-top: 15px;">(default is On) If the chat icon is not displaying, it may be due to server settings.  In these situations, simply switch the <b>OB Clean</b> setting to <b>Off</b> to correct the issue.</div>
					<div style="margin-top: 15px;">
						<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#ob_clean_on').prop('checked', true);confirm_ob_clean('on');"><input type="radio" name="ob_clean" id="ob_clean_on" value="on" <?php echo ( $VALS["OB_CLEAN"] != "off" ) ? "checked" : "" ?>> On</div>
						<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#ob_clean_off').prop('checked', true);confirm_ob_clean('off');"><input type="radio" name="ob_clean" id="ob_clean_off" value="off" <?php echo ( $VALS["OB_CLEAN"] == "off" ) ? "checked" : "" ?>> Off</div>
						<div style="clear: both;"></div>
					</div>
				</div>

			</div>

		</div>
		<?php endif ; ?>

		<?php endif ; ?>

		<div style="display: none;"><iframe id='iframe_proto_verify' src='about:blank' scrolling='no' border=0 frameborder=0></iframe></div>
<?php include_once( "./inc_footer.php" ) ?>

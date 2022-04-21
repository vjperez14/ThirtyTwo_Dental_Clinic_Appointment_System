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

	$vars_rtype = Array( 1=>"Defined Order", 2=>"Round-robin", 3=>"Simultaneous" ) ;
	$search_ops_limit = 20 ; // greater than x will display search icon

	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }

	$https = "" ; $error = "" ;
	if ( isset( $_SERVER["HTTP_CF_VISITOR"] ) && preg_match( "/(https)/i", $_SERVER["HTTP_CF_VISITOR"] ) ) { $https = "s" ; }
	else if ( isset( $_SERVER["HTTP_X_FORWARDED_PROTO"] ) && preg_match( "/(https)/i", $_SERVER["HTTP_X_FORWARDED_PROTO"] ) ) { $https = "s" ; }
	else if ( isset( $_SERVER["HTTPS"] ) && preg_match( "/(on)/i", $_SERVER["HTTPS"] ) ) { $https = "s" ; }

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ; if ( !$jump ) { $jump = "main" ; }
	$ftab = Util_Format_Sanatize( Util_Format_GetVar( "ftab" ), "ln" ) ;
	if ( !isset( $CONF['icon_check'] ) ) { $CONF['icon_check'] = "on" ; }
	if ( !isset( $CONF["screen"] ) ) { $CONF["screen"] = "same" ; }
	$ldap_array = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["LDAP"] ) && $VALS_ADDONS["LDAP"] ) ? unserialize( base64_decode( $VALS_ADDONS["LDAP"] ) ) : Array() ;

	$addon_voice_chat = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/voice_chat/inc_op.php" ) ) ? 1 : 0 ;
	$addon_ldap = ( isset( $ldap_array["server"] ) && is_file( "$CONF[DOCUMENT_ROOT]/addons/ldap/ldap.php" ) ) ? 1 : 0 ;
	$addon_whisper = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/whisper/inc_whisper.php" ) ) ? 1 : 0 ;

	if ( $action === "submit" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_ext.php" ) ;
		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$status = Util_Format_Sanatize( Util_Format_GetVar( "status" ), "n" ) ;
		$rate = Util_Format_Sanatize( Util_Format_GetVar( "rate" ), "n" ) ;
		$sms = Util_Format_Sanatize( Util_Format_GetVar( "sms" ), "n" ) ;
		$op2op = Util_Format_Sanatize( Util_Format_GetVar( "op2op" ), "n" ) ;
		$traffic = Util_Format_Sanatize( Util_Format_GetVar( "traffic" ), "n" ) ;
		$viewip = Util_Format_Sanatize( Util_Format_GetVar( "viewip" ), "n" ) ;
		$maxc = Util_Format_Sanatize( Util_Format_GetVar( "maxc" ), "n" ) ;
		$maxco = Util_Format_Sanatize( Util_Format_GetVar( "maxco" ), "n" ) ;
		$login = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "login" ), "ln" ) ) ;
		$password = Util_Format_Sanatize( Util_Format_GetVar( "password" ), "ln" ) ;
		$name = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "name" ), "ln" ) ) ;
		$email = Util_Format_Sanatize( Util_Format_GetVar( "email" ), "e" ) ;
		$mapper = Util_Format_Sanatize( Util_Format_GetVar( "mapper" ), "n" ) ;
		$nchats = Util_Format_Sanatize( Util_Format_GetVar( "nchats" ), "n" ) ;
		$tag = Util_Format_Sanatize( Util_Format_GetVar( "tag" ), "n" ) ;
		$peer = Util_Format_Sanatize( Util_Format_GetVar( "peer" ), "n" ) ;
		$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;
		$vupload = Util_Format_Sanatize( Util_Format_GetVar( "vupload" ), "a" ) ;
		$view_chats = Util_Format_Sanatize( Util_Format_GetVar( "view_chats" ), "n" ) ;
		$dept_offline = Util_Format_Sanatize( Util_Format_GetVar( "dept_offline" ), "n" ) ;
		$total_ops = Ops_get_TotalOps( $dbh ) ; if ( !isset( $VARS_MAX_OPS ) && $VARS_FREEV ) { $VARS_MAX_OPS = 2 ; } else if ( !isset( $VARS_MAX_OPS ) && ( !isset( $KEY_OPS ) || !is_numeric( base64_decode( $KEY_OPS ) ) ) ) { $VARS_MAX_OPS = 1 ; } else if ( !isset( $VARS_MAX_OPS ) && isset( $KEY_OPS ) && is_numeric( base64_decode( $KEY_OPS ) ) ) { $VARS_MAX_OPS = base64_decode( $KEY_OPS ) ; } if ( !is_numeric( $VARS_MAX_OPS ) ) { $VARS_MAX_OPS = 1 ; } if ( isset( $VARS_MAX_OPS ) && ( $total_ops >= $VARS_MAX_OPS ) && !$opid ) { $error = $ERROR_OPS ; if ( $VARS_FREEV ) { $error = $ERROR_OPS_FREE ; } }
		else
		{
			$vupload_val = "" ;
			if ( !count( $vupload ) ) { $vupload_val = "0," ; }
			else
			{
				for ( $c = 0; $c < count( $vupload ); ++$c )
				{
					if ( $vupload[$c] == 1 ) { $vupload_val = "1," ; break ; }
					$vupload_val .= $vupload[$c]."," ;
				}
			} if ( $vupload_val ) { $vupload_val = substr_replace( $vupload_val, "", -1 ) ; }
			$error = Ops_put_Op( $dbh, $opid, $status, $mapper, $rate, $sms, $op2op, $traffic, $viewip, $nchats, $maxc, $maxco, $login, $password, $name, $email, $tag, $peer, strtoupper( $vupload_val ), $view_chats, $dept_offline ) ;
			if ( is_numeric( $error ) )
			{
				if ( $copy_all )
				{
					include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
					Ops_update_AllFileUploadSettings( $dbh, strtoupper( $vupload_val ) ) ;
				}
				database_mysql_close( $dbh ) ;
				HEADER( "location: ops.php?action=success" ) ;
				exit ;
			}
		}
	}
	else if ( $action === "delete" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/remove.php" ) ;

		$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
		$opinfo = Ops_get_OpInfoByID( $dbh, $opid ) ;

		if ( isset( $opinfo["opID"] ) && ( $opinfo["login"] != "phplivebot" ) )
		{
			$mapp_array = ( isset( $VALS["MAPP"] ) && $VALS["MAPP"] ) ? unserialize( $VALS["MAPP"] ) : Array() ;
			if ( $opinfo["mapp"] && is_file( "$CONF[TYPE_IO_DIR]/{$opid}.mapp" ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/mapp/API/Util_MAPP.php" ) ;
				if ( isset( $mapp_array[$opid] ) ) { $arn = $mapp_array[$opid]["a"] ; $platform = $mapp_array[$opid]["p"] ; }
				if ( isset( $arn ) && $arn ) { Util_MAPP_Publish( $opid, "new_request", $platform, $arn, "Account not found.  You are Offline." ) ; }
			}
			Ops_remove_Op( $dbh, $opid ) ;
		}
	}
	else if ( $action === "submit_assign" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/put.php" ) ;

		$opids = Util_Format_GetVar( "opids" ) ;
		$deptids = Util_Format_GetVar( "deptids" ) ;

		for ( $c = 0; $c < count( $opids ); ++$c )
		{
			$opid = Util_Format_Sanatize( $opids[$c], "n" ) ;
			for ( $c2 = 0; $c2 < count( $deptids ); ++$c2 )
			{
				$deptid = Util_Format_Sanatize( $deptids[$c2], "n" ) ;
				$opinfo = Ops_get_OpInfoByID( $dbh, $opid ) ;
				$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
				Ops_put_OpDept( $dbh, $opid, $deptid, $deptinfo["visible"], $opinfo["status"] ) ;
			}
		}
	}
	else if ( $action === "screen" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$screen = Util_Format_Sanatize( Util_Format_GetVar( "screen" ), "ln" ) ;

		if ( $CONF["screen"] != $screen )
		{
			$error = ( Util_Vals_WriteToConfFile( "screen", $screen ) ) ? "" : "Could not write to config file." ;
			$CONF["screen"] = $screen ;
		}
		$jump = "online" ;
	}

	$screen_same = ( $CONF["screen"] == "same" ) ? "checked" : "" ;
	$screen_separate = ( $screen_same == "checked" ) ? "" : "checked" ;

	Ops_update_itr_IdleOps( $dbh ) ;
	$operators = Ops_get_AllOps( $dbh ) ;
	$departments = Depts_get_AllDepts( $dbh ) ;

	$total_operators = 0 ;
	for ( $c = 0; $c < count( $operators ); ++$c )
	{
		$operator = $operators[$c] ;
		if ( $operator["login"] != "phplivebot" )
			++$total_operators ;
	}

	$login_url = $CONF['BASE_URL'] ;
	if ( !preg_match( "/\/\//", $login_url ) ) { $login_url = "//$PHPLIVE_HOST$login_url" ; }
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
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/jquery-ui.min.js?<?php echo $VERSION ?>"></script>
<?php if ( $addon_ldap ): ?><script data-cfasync="false" type="text/javascript" src="../addons/ldap/js/ldap.js?<?php echo filemtime ( "../addons/ldap/js/ldap.js" ) ; ?>"></script><?php endif ; ?>

<style>
ul {
	list-style: none;
	padding-left: 0;
}
</style>
<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var global_div_list_height ;
	var global_div_form_height ;
	var global_opid ;
	var st_rd ;
	var global_search_field ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		$("body").show() ;

		init_menu() ;
		toggle_menu_setup( "ops" ) ;
		init_divs() ;
		init_op_dept_list() ;

		show_div( "<?php echo $jump ?>" ) ;

		<?php if ( $action && !$error ): ?>
		do_alert( 1, "Update Success" ) ;
		<?php elseif ( $error ): ?>
		do_alert( 0, "<?php echo $error ?>" ) ;
		<?php endif ; ?>

		$('#login').on("paste",function(e) {
			e.preventDefault() ;
		});

		<?php if ( $ftab == "max" ): ?>
			$('.div_class_max').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		<?php endif ; ?>

		$('#urls_<?php echo $CONF["screen"] ?>').show() ;
	});

	function init_divs()
	{
		global_div_list_height = $('#div_list').outerHeight() ;
		global_div_form_height = $('#div_form').outerHeight() ;
	}

	function init_op_dept_list()
	{
		<?php
			for ( $c = 0; $c < count( $departments ); ++$c )
			{
				$department = $departments[$c] ;
				if ( $department["name"] != "Archive" )
				{
					$timeout = $c*100 ;
					print "setTimeout( function(){ op_dept_moveup( $department[deptID], 0 ) ; }, $timeout ) ;" ;
				}
			}
		?>
	}

	function do_edit( theopid, thestatus_access, thename, theemail, thelogin, therate, theop2op, thetraffic, theviewip, themaxc, themaxco, themapper, thenchats, thetag, thepeer, theupload, theview_chats, thedept_offline )
	{
		show_form() ;
		$( "input#opid" ).val( theopid ) ;
		$( "input#name" ).val( thename ) ;
		$( "input#email" ).val( theemail ) ;
		$( "input#login" ).val( thelogin ) ;
		$( "input#password_temp" ).val( "php-live-support" ) ;
		$( "select#maxc" ).val( themaxc ) ;
		$( "input#maxco_"+themaxco ).prop( "checked", true ) ;
		$( "input#rate_"+therate ).prop( "checked", true ) ;
		$( "input#op2op_"+theop2op ).prop( "checked", true ) ;
		$( "input#traffic_"+thetraffic ).prop( "checked", true ) ;
		$( "input#viewip_"+theviewip ).prop( "checked", true ) ;
		$( "input#mapper_"+themapper ).prop( "checked", true ) ;
		$( "input#nchats_"+thenchats ).prop( "checked", true ) ;
		$( "input#tag_"+thetag ).prop( "checked", true ) ;
		$( "input#peer_"+thepeer ).prop( "checked", true ) ;
		$( "input#view_chats_"+theview_chats ).prop( "checked", true ) ;
		$( "input#dept_offline_"+thedept_offline ).prop( "checked", true ) ;

		if ( thelogin == "phplivebot" )
		{
			$('.div_setting').hide() ;
			$('.div_setting_vc').hide() ;
		}

		toggle_status_error( thestatus_access ) ;
		do_upload_checked( theupload ) ;
		$('#div_op_online').show() ;
	}

	function toggle_status_error( thestatus_access )
	{
		if ( thestatus_access )
		{
			$('#span_inactive').fadeOut("fast") ;
		}
		else
		{
			$('#span_inactive').fadeIn("fast") ;
		}
		$( "input#status_"+thestatus_access ).prop( "checked", true ) ;
	}

	function do_notice( thediv, theopid, thelogin )
	{
		if ( ( thediv == "disconnect" ) && ( typeof( st_rd ) != "undefined" ) ) { do_alert( 0, "Another disconnect in progress." ) ; return false ; }

		var pos = $('#div_tr_'+theopid).position() ;
		var width = $('#div_tr_'+theopid).outerWidth() - 18 ;
		var height = $('#div_tr_'+theopid).outerHeight() - 8 ;

		global_opid = theopid ;

		if ( $('#div_notice_'+thediv).is(':visible') )
			$('#div_notice_'+thediv).fadeOut( "fast", function() { show_div_notice(thediv, thelogin, pos, width, height) ; }) ;
		else
			show_div_notice(thediv, thelogin, pos, width, height) ;
	}

	function do_delete_doit()
	{
		location.href = "ops.php?action=delete&opid="+global_opid ;
	}

	function show_div_notice( thediv, thelogin, thepos, thewidth, theheight )
	{
		$('#span_login_'+thediv).html( thelogin ) ;
		$('#div_notice_'+thediv).css({'top': thepos.top, 'left': thepos.left, 'width': thewidth, 'height': theheight}).fadeIn("fast") ;
	}

	function do_submit()
	{
		var name = encodeURIComponent( $( "input#name" ).val().trim() ) ;
		var email = $( "input#email" ).val().trim() ;
		var login = encodeURIComponent( $( "input#login" ).val().trim() ) ;
		var password = $( "input#password_temp" ).val().trim() ;
		var ldap_verified = ( <?php echo $addon_ldap ?> && $('#ldap_verified').length ) ? $('#ldap_verified').val() : 1 ;

		if ( name == "" )
			do_alert( 0, "Please provide a name." ) ;
		else if ( !check_email( email ) )
			do_alert( 0, "Please provide a valid email address." ) ;
		else if ( login == "" )
			do_alert( 0, "Please provide a login." ) ;
		else if ( password == "" )
			do_alert( 0, "Please provide a password." ) ;
		else if ( login == "<?php echo $admininfo["login"] ?>" )
			do_alert( 0, "Operator login must be different then the Setup Admin login." ) ;
		else if ( <?php echo $addon_ldap ?> && !parseInt( ldap_verified ) )
		{
			$('html, body').animate({
				scrollTop: 0
			}, 'slow', function() {
				do_alert( 0, "Please verify the LDAP user login." ) ;
				$('#btn_ldap_search').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			});
		}
		else
		{
			email = encodeURIComponent( email ) ;
			$("input#password").val( phplive_md5( password ) ) ;
			$('#btn_submit').attr('disabled', true) ;
			$('#theform').submit() ;
		}
	}

	function show_div( thediv )
	{
		var divs = Array( "main", "assign", "report", "monitor", "online", "resources" ) ;

		if ( $('#div_form').is(':visible') )
			do_reset() ;

		for ( var c = 0; c < divs.length; ++c )
		{
			$('#edit').hide() ;

			$('#ops_'+divs[c]).hide() ;
			$('#menu_ops_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		if ( thediv == "main" )
			$('#edit').show() ;

		$('input#jump').val( thediv ) ;
		$('#ops_'+thediv).show() ;
		$('#menu_ops_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;

		if ( thediv == "assign" )
		{
			$('#img_getting_started_assign').show() ;
			$('#img_getting_started_ops').hide() ;
			$('#img_getting_started_online').hide() ;
		}
		else if ( thediv == "main" )
		{
			$('#img_getting_started_ops').show() ;
			$('#img_getting_started_assign').hide() ;
			$('#img_getting_started_online').hide() ;
		}
	}

	function op_dept_moveup( thedeptid, theopid )
	{
		var json_data = new Object ;
		$('#dept_ops_'+thedeptid).fadeOut("fast") ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=moveup&deptid="+thedeptid+"&opid="+theopid+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				var total_ops = 0 ;
				if ( json_data.ops != undefined )
				{
					var cursor = ( browser_filter ) ? "grab" : "move" ;
					var ops_string = "<ul id=\"div_sortable_"+thedeptid+"\">" ;
					for ( var c = 0; c < json_data.ops.length; ++c )
					{
						++total_ops ;
						var name = json_data.ops[c]["name"] ;
						var opid = json_data.ops[c]["opid"] ;
						var login = json_data.ops[c]["login"] ;
						var status = parseInt( json_data.ops[c]["status"] ) ? "<img src=\"../pics/icons/bulb.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"is online\" title=\"is online\">" : "<img src=\"../pics/icons/bulb_off.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"is offline\" title=\"is offline\">" ;

						ops_string += "<li id=\"op_"+c+"_"+opid+"\" style=\"margin-bottom: 15px; cursor: "+cursor+";\" class=\"info_neutral li_cursor_"+thedeptid+"\"><a href=\"JavaScript:void(0)\" onClick=\"op_dept_remove( "+thedeptid+", "+opid+" )\"><img src=\"../pics/icons/delete.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"remove from department\" title=\"remove from department\"></a> &nbsp; "+status+" "+name+"</li>" ;
					}
					if ( !total_ops )
						ops_string += "<div class=\"info_neutral\">Blank results.</div>" ;
				}
				ops_string += "</ul>" ;
				$('#dept_ops_'+thedeptid).html( ops_string ).fadeIn("fast") ;

				if ( theopid ) { do_alert( 1, "Update Success" ) ; }

				if ( total_ops > 1 )
				{
					var cursor_grab = ( browser_filter ) ? "grab" : "move" ;
					var cursor_grabbing = ( browser_filter ) ? "grabbing" : "move" ;

					$( "#div_sortable_"+thedeptid ).sortable() ;
					$( "#div_sortable_"+thedeptid ).on('sortupdate', function() { op_dept_sort( thedeptid ) ; } ) ;

					$( ".li_cursor_"+thedeptid ).css('cursor', cursor_grab) ;
					$( ".li_cursor_"+thedeptid ).mousedown(function() {
						$(this).css('cursor', cursor_grabbing) ;
					});
					$( ".li_cursor_"+thedeptid ).mouseup(function() {
						$(this).css('cursor', cursor_grab) ;
					});
					$('#div_defined_'+thedeptid).show() ;
				}
				else
				{
					$( ".li_cursor_"+thedeptid ).css('cursor', 'default') ;
					$('#div_defined_'+thedeptid).hide() ;
				}
			}
		});
	}

	function op_dept_sort( thedeptid )
	{
		var sort_order = $( "#div_sortable_"+thedeptid ).sortable("toArray") ;
		var query_string = "" ;

		for ( var c = 0; c < sort_order.length; ++c )
		{
			var matches = sort_order[c].match( /^op_(.*?)_(.*?)$/ ) ;
			if ( typeof( matches[1] ) != "undefined" )
			{
				var display = c ;
				var opid = matches[2] ;
				query_string += "o[]="+opid+"&ds[]="+display+"&" ;
			}
		}

		if ( query_string )
		{
			var unique = unixtime() ;
			var json_data = new Object ;

			$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_.php",
			data: "action=display_order&deptid="+thedeptid+"&"+query_string+unique,
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

	function op_dept_remove( thedeptid, theopid )
	{
		var json_data = new Object ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=op_dept_remove&deptid="+thedeptid+"&opid="+theopid+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					op_dept_moveup( thedeptid, 0 ) ;
				}
				do_alert( 1, "Update Success" ) ;
			}
		});
	}

	function remote_disconnect()
	{
		var json_data = new Object ;
		$('#remote_disconnect_button').hide() ;
		$('#remote_disconnect_notice').show() ;

		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=remote_disconnect&opid="+global_opid+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
					check_op_status( global_opid ) ;
				else
				{
					$('#remote_disconnect_notice').hide() ;
					do_alert( 0, "Could not remote disconnect console.  Please try again." ) ;
				}
			}
		});
	}

	function check_op_status( theopid )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( typeof( st_rd ) != "undefined" ) { clearTimeout( st_rd ) ; }

		$.ajax({
		type: "POST",
		url: "../wapis/status_op.php",
		data: "opid="+theopid+"&jkey=<?php echo md5( $CONF['API_KEY'] ) ?>&"+unique,
		success: function(data){
			eval( data ) ;

			if ( !parseInt( json_data.status ) )
				location.href = 'ops.php?action=success' ;
			else
				st_rd = setTimeout( function(){ check_op_status( theopid ) ; }, 2000 ) ;
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Lost connection to server.  Please refresh the page and try again." ) ;
		} });
	}

	function launch_tools_op_status()
	{
		var url = "tools_op_status.php" ;

		if ( <?php echo $total_operators ?> > 0 )
			External_lib_PopupCenter( url, "Status", 650, 550, "scrollbars=yes,menubar=no,resizable=1,location=no,width=650,height=550,status=0" ) ;
		else
		{
			if ( confirm( "Operator account does not exist.  Create an operator?" ) )
				location.href = "ops.php" ;
		}
	}

	function check_all_ops( theobject )
	{
		if ( ( typeof( theobject ) != "undefined" ) && ( theobject.checked ) )
		{
			$('#div_list_ops').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "ck_op_" ) == 0 )
					this.checked = true ;
			}) ;
		}
		else
		{
			$('#div_list_ops').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "ck_op_" ) == 0 )
					this.checked = false ;
			}) ;
		}
	}

	function check_all_depts( theobject )
	{
		if ( ( typeof( theobject ) != "undefined" ) && ( theobject.checked ) )
		{
			$('#div_list_depts').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "ck_dept_" ) == 0 )
					this.checked = true ;
			}) ;
		}
		else
		{
			$('#div_list_depts').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "ck_dept_" ) == 0 )
					this.checked = false ;
			}) ;
		}
	}

	function do_assign()
	{
		var ok_ops = 0 ;
		var ok_depts = 0 ;

		$('#div_list_ops').find('*').each( function () {
			var div_name = this.id ;
			if ( ( div_name.indexOf( "ck_op_" ) == 0 ) && this.checked )
				ok_ops = 1 ;
		}) ;
		$('#div_list_depts').find('*').each( function () {
			var div_name = this.id ;
			if ( ( div_name.indexOf( "ck_dept_" ) == 0 ) && this.checked )
				ok_depts = 1 ;
		}) ;

		if ( !ok_ops )
			do_alert( 0, "An operator must be selected." ) ;
		else if ( !ok_depts )
			do_alert( 0, "A department must be selected." ) ;
		else
		{
			$('#btn_submit_assign').attr('disabled', true) ;
			$('#form_assign').submit() ;
		}
	}

	function show_form()
	{
		$(window).scrollTop(0) ;
		$('#div_list').hide() ;
		$('#div_btn_add').hide() ;
		$('#div_form').show() ;
	}

	function do_reset()
	{
		$('#opid').val(0) ;
		$('#theform').each(function(){
			this.reset();
		});

		$('#div_form').hide() ;
		$('#div_btn_add').show() ;
		$('#div_list').show() ;
		$('#div_op_online').hide() ;
		$('#div_copy_all').hide() ;
		$('#div_dept_offline_error').hide() ;
		$('#btn_submit').attr('disabled', false) ;

		$('#ldap_verified').val(0) ;
		$('.span_ldap_found').empty().hide() ;

		$('.div_setting').show() ;
		if ( <?php echo $addon_voice_chat ?> ) { $('.div_setting_vc').show() ; }

		toggle_status_error(1) ;
		$(window).scrollTop(0) ;
	}

	function toggle_upload( thevalue )
	{
		var total_checked = 0 ;
		$('#theform').find('*').each( function () {
			var div_name = this.id ;
			if ( div_name.indexOf( "upload_" ) == 0 )
			{
				if ( this.checked ) { ++total_checked ; }
			}
		}) ;

		if ( $('#upload_'+thevalue).is(':checked') )
		{
			$('#upload_'+thevalue).prop('checked', false) ;
			if ( thevalue != 1 ) { $('#upload_1').prop('checked', false) ; }
			else if ( thevalue == 1 ) { check_all(0) ; }
		}
		else
		{
			++total_checked ;
			$('#upload_'+thevalue).prop('checked', true) ;
			if ( thevalue == 0 ) { check_all(0) ; }
			else if ( thevalue == 1 ) { check_all(1) ; }
			else if ( total_checked == 8 ) { $('#upload_1').prop('checked', true) ; }
			else { $('#upload_0').prop('checked', false) ; }
		}
	}

	function check_all( theflag )
	{
		if ( theflag )
		{
			$('#theform').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "upload_" ) == 0 )
				{
					if ( div_name == "upload_0" )
						this.checked = false ;
					else
						this.checked = true ;
				}
			}) ;
		}
		else
		{
			$('#theform').find('*').each( function () {
				var div_name = this.id ;
				if ( div_name.indexOf( "upload_" ) == 0 )
				{
					if ( div_name != "upload_0" )
						this.checked = false ;
				}
			}) ;
		}
	}

	function do_upload_checked( thevalue )
	{
		var uploads = thevalue.split( "," ) ;

		if ( uploads.length >= 8 ) { check_all(1) ; }
		else
		{
			for ( var c = 0; c < uploads.length; ++c )
			{
				var value = uploads[c] ;

				if ( value )
				{
					if ( value == 1 ) { check_all(1) ; break ; }
					else { $('#upload_'+value).prop('checked', true) ; }
				}
			}
		}
	}

	function check_uncheck( theselection, theobj )
	{
		if ( $('#ck_'+theselection+'_all').prop('checked') && !theobj.checked )
		{
			$('#ck_'+theselection+'_all').prop('checked', false) ;
		}
	}

	function check_op2op( theflag )
	{
		var op2op = $('#op2op_1').prop('checked') ? 1 : 0 ;

		$('#div_dept_offline_error').hide() ;

		if ( theflag )
		{
			if ( !op2op )
			{
				$('#div_dept_offline_error').fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
				$('#dept_offline_0').prop('checked', true) ;
			}
			else
				$('#dept_offline_1').prop('checked', true) ;
		}
		else
		{
			$('#dept_offline_0').prop('checked', true) ;
		}
	}

	function toggle_search( theobj, thefield )
	{
		if ( ( global_search_field == thefield ) || ( thefield == "close" ) )
		{
			global_search_field = undeefined ;

			$('#div_search_box').fadeOut("fast") ;
			$('#search_string').val('') ;

			$('.tr_op_assign').show() ;
		}
		else
		{
			var pos = $(theobj).position() ;
			var top = pos.top - 20 ;
			var left = pos.left + 20 ;

			if ( global_search_field )
			{
				$('#search_string').val('') ;
				$('.tr_op_assign').show() ;
			}

			global_search_field = thefield ;

			$('#div_search_box').css({'top': top, 'left': left}).fadeIn("fast") ;
			$('#search_string').focus() ;
		}
	}

	function do_search(e)
	{
		var search_string = $('#search_string').val() ;

		$('#tbody_ops_assign').find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf( "td_op_"+global_search_field+"_" ) != -1 )
			{
				var matches = div_name.match( /_(\d+)$/ ) ;
				var this_opid = ( typeof( matches[1] ) != "undefined" ) ? matches[1] : 0 ;

				var this_value = $(this).html() ;
				var pattern1 = search_string ;

				if ( this_value.match(new RegExp( pattern1, 'gi' ) ) )
				{
					$('#tr_op_assign_'+this_opid).show() ;
				}
				else
				{
					$('#tr_op_assign_'+this_opid).hide() ;
				}
			}
		} );
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="show_div('main')" id="menu_ops_main">Chat Operators</div>
			<div class="op_submenu" onClick="show_div('assign')" id="menu_ops_assign">Assign Operator to Department</div>
			<div class="op_submenu" onClick="location.href='interface_op_pics.php'">Profile Picture</div>
			<div class="op_submenu" onClick="location.href='ops_reports.php'" id="menu_ops_report">Online/Offline Activity</div>
			<div class="op_submenu" onClick="show_div('monitor')" id="menu_ops_monitor">Status Monitor</div>
			<div class="op_submenu" onClick="location.href='ops.php?jump=online'" id="menu_ops_online"><img src="../pics/icons/bulb.png" width="12" height="12" border="0" alt=""> Go ONLINE!</div>
			<div style="clear: both"></div>
		</div>

		<div id="ops_main" style="display: none;">
			<table cellspacing=0 cellpadding=0 border=0 width="100%" id="div_btn_add">
			<tr>
				<td width="180"><div class="edit_focus" style="margin-top: 25px;" onClick="show_form()"><img src="../pics/icons/add.png" width="16" height="16" border="0" alt=""> Add Chat Operator</div></td>
				<td style="" align="right" valign="bottom">&nbsp;</td>
			</tr>
			</table>

			<div id="div_list" style="margin-top: 15px; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);">

				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<?php
					$image_empty = "<img src=\"../pics/space.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"\">" ;
					$image_checked = "<img src=\"../pics/icons/check.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\">";
					for ( $c = 0; $c < count( $operators ); ++$c )
					{
						$operator = $operators[$c] ;

						$opid = $operator["opID"] ;
						$login = $operator["login"] ;
						$email = $operator["email"] ;
						$maxc = ( $operator["maxc"] != -1 ) ? $operator["maxc"] : "&nbsp;" ;
						$maxc = ( $operator["maxco"] ) ? "<span class=\"info_error\" style=\"padding: 2px;\" alt=\"set to offline when max is reached\" title=\"set to offline when max is reached\">$maxc</span>" : $maxc ;
						$rate = ( $operator["rate"] ) ? $image_checked : $image_empty ;
						$sms = ( $operator["sms"] ) ? $image_checked : $image_empty ;
						$op2op = ( $operator["op2op"] ) ? $image_checked : $image_empty ;
						$traffic = ( $operator["traffic"] ) ? $image_checked : $image_empty ;
						$viewip = ( $operator["viewip"] ) ? $image_checked : $image_empty ;
						$status = ( $operator["status"] ) ? "<b>Operator is Online</b><br>Click to disconnect console." : "Offline" ;
						$style = ( $operator["status"] ) ? "cursor: pointer" : "" ;
						$td_style = ( $operator["status"] ) ? "info_online" : "info_clear" ;
						$js = ( $operator["status"] ) ? "onClick=\"do_notice('disconnect', $opid, '$login')\"" : "" ;
						$profile_image = Util_Upload_GetLogo( "profile", $opid ) ;

						$mapp_icon = ( $operator["mapper"] ) ? " <img src=\"../pics/icons/mobile.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"can login from the Mobile App\" title=\"can login from the Mobile App\" style=\"cursor: help;\"> " : "" ;
						$mapp_online_icon = ( $operator["mapp"] ) ? " &nbsp; <img src=\"../pics/icons/mobile.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"logged in on mobile\" title=\"logged in on mobile\" style=\"cursor: help;\">" : "" ;
						$tag_icon = ( $operator["tag"] ) ? " <img src=\"../pics/icons/pin.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"can tag chats\" title=\"can tag chats\" style=\"cursor: pointer;\" onClick=\"location.href='transcripts_tags.php'\"> " : "" ;
						$voice_icon = ( $addon_voice_chat && $operator["peer"] ) ? " <img src=\"../themes/initiate/mic_yes.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"can send Voice Chat requests\" title=\"can send Voice Chat requests\" style=\"cursor: help;\"> " : "" ;
						$upload_icon = ( $VARS_INI_UPLOAD && $operator["upload"] ) ? "<img src=\"../pics/icons/attach.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"can upload files during chat\" title=\"can upload files during chat\" style=\"cursor: help;\" onClick=\"location.href='settings.php?jump=upload'\">" : "" ;

						$status_access = ( !is_file( "$CONF[TYPE_IO_DIR]/$opid.locked" ) ) ? 1 : 0 ;
						$status_access_string = ( $status_access ) ? "" : "<div class=\"info_error\" style=\"text-shadow: none; cursor: help;\" alt=\"Account inactive.  Edit to activate.\" title=\"Account inactive.  Edit to activate.\">inactive</div>" ;

						$bg_color = ( ($c+1) % 2 ) ? "FFFFFF" : "EDEDED" ;

						$status_img = ( $operator["status"] ) ? "<span style=\"cursor: pointer;\"><img src=\"../pics/icons/bulb.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"online\" title=\"online\" class=\"info_good\">$mapp_online_icon<br>logout</span>" : "<img src=\"../pics/icons/bulb_off.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"offline\" title=\"offline\">" ;

						$edit_string = "<div><a href=\"JavaScript:void(0)\" onClick=\"do_edit( $opid, $status_access, '$operator[name]', '$operator[email]', '$operator[login]', $operator[rate], $operator[op2op], $operator[traffic], $operator[viewip], $operator[maxc], $operator[maxco], $operator[mapper], $operator[nchats], $operator[tag], $operator[peer], '$operator[upload]', $operator[view_chats], $operator[dept_offline] )\"><img src=\"../pics/btn_edit.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;
						$delete_string = "<div style=\"margin-top: 10px;\"><a href=\"JavaScript:void(0)\" onClick=\"do_notice('delete', $opid, '$login')\"><img src=\"../pics/btn_delete.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;

						if ( $login == "phplivebot" )
						{
							$login = $email = "" ;
							$maxc = $sms = $op2op = $traffic = $viewip = $image_empty ;
							$mapp_icon = "" ;
							$tag_icon = "" ;
							$upload_icon = "" ;
							$status_img = $image_empty ;
							$style = "" ;
							$td_style ="info_clear" ;
							$js = "" ;
							$delete_string = "" ;
						}
						if ( $login ) { $login = "<div class=\"info_login\">$login</div>" ; }

						$td1 = "td_dept_td" ;

						print "
						<tr id=\"div_tr_$opid\" style=\"background: #$bg_color\">
							<td class=\"$td1\" nowrap>$edit_string$delete_string</td>
							<td class=\"$td1\">
								<table cellspacing=0 cellpadding=2 border=0>
								<tr>
									<td align=\"center\" style=\"\">
										<a href=\"interface_op_pics.php?opid=$opid\"><img src=\"$profile_image\" width=\"55\" height=\"55\" border=\"0\" alt=\"\" style=\"border: 1px solid #DFDFDF; border-radius: 50%;\"></a>
										<div style=\"margin-top: 5px; text-align: left;\" class=\"txt_grey\">ID: $operator[opID]</div>
									</td>
									<td>$operator[name]<div style=\"padding-top: 5px;\">$mapp_icon $tag_icon $upload_icon $voice_icon</div></td>
								</tr>
								</table>
							</td>
							<td class=\"$td1\" nowrap><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Login</div>$login$status_access_string</td>
							<td class=\"$td1\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Email</div>$email</td>
							<td class=\"$td1 div_class_max\" align=\"center\" alt=\"max concurrent chats\" title=\"max concurrent chats\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Max Concurrent<br>Chats</div>$maxc</td>
							<td class=\"$td1\" align=\"center\" alt=\"chat rating survey\" title=\"chat rating survey\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Rate</div>$rate</td>
							<td class=\"$td1\" align=\"center\" alt=\"operator to operator chat\" title=\"operator to operator chat\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Op2Op</div>$op2op</td>
							<td class=\"$td1\" align=\"center\" alt=\"traffic monitor\" title=\"traffic monitor\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Traffic</div>$traffic</td>
							<td class=\"$td1\" align=\"center\" alt=\"view visitor IP\" title=\"view visitor IP\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">View IP</div>$viewip</td>
							<td class=\"$td1\" align=\"center\" $js nowrap><div class=\"txt_grey\" style=\"margin-bottom: 5px;\" alt=\"online/offline status\" title=\"online/offline status\">Status</div>$status_img</td>
						</tr>
						" ;
					}
					if ( $c == 0 )
						print "<tr><td colspan=11 class=\"td_dept_td\">Blank results.</td></tr>" ;
				?>
				</table>

			</div>
		</div>

		<div style="display: none;" id="ops_assign">

			<div style="margin-top: 25px;">
				<?php if ( !count( $departments ) ): ?>
				<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span>
				<?php elseif ( !$total_operators ):  ?>
				<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add an <a href="ops.php" style="color: #FFFFFF;">Operator</a> to view this area.</span>

				<?php else: ?>
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td valign="top">
						<form method="POST" action="ops.php" id="form_assign">
						<input type="hidden" name="action" value="submit_assign">
						<input type="hidden" name="jump" value="assign">
						
						<div style="max-height: 320px; overflow: auto;" id="div_list_ops">
							<table cellspacing=0 cellpadding=0 border=0 width="100%">
							<tr>
								<td width="20"><div class="td_dept_header"><input type="checkbox" onClick="check_all_ops(this)" id="ck_op_all"></div></td>
								<td width="10"><div class="td_dept_header">ID</div></td>
								<td><div class="td_dept_header">Operator Name <?php if ( count( $operators ) > $search_ops_limit ): ?><img src="../pics/icons/search.png" width="12" height="12" border="0" alt="search" title="search" style="cursor: pointer;" onClick="toggle_search(this, 'name')"><?php endif ; ?></div></td>
								<!-- <td width="70" nowrap><div class="td_dept_header">Login <?php if ( count( $operators ) > $search_ops_limit ): ?><img src="../pics/icons/search.png" width="12" height="12" border="0" alt="search" title="search" style="cursor: pointer;" onClick="toggle_search(this, 'login')"><?php endif ; ?></div></td> -->
								<td width="50"><div class="td_dept_header">Status</div></td>
								<td><div class="td_dept_header">Email <?php if ( count( $operators ) > $search_ops_limit ): ?><img src="../pics/icons/search.png" width="12" height="12" border="0" alt="search" title="search" style="cursor: pointer;" onClick="toggle_search(this, 'email')"><?php endif ; ?></div></td>
							</tr>
							<tbody id="tbody_ops_assign">
							<?php
								$td_index = 0 ;
								for ( $c = 0; $c < count( $operators ); ++$c )
								{
									$operator = $operators[$c] ;
									$opid = $operator["opID"] ;

									$td1 = "td_dept_td" ;
									$status_text = ( $operator["status"] ) ? "online" : "offline" ;
									$status_img = ( $operator["status"] ) ? "<img src=\"../pics/icons/bulb.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"$status_text\" title=\"$status_text\" class=\"info_good\">" : "<img src=\"../pics/icons/bulb_off.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"$status_text\" title=\"$status_text\">" ;

									if ( $operator["login"] != "phplivebot" )
									{
										$bg_color = ( ( $td_index + 1 ) % 2 ) ? "FFFFFF" : "EDEDED" ;
										++$td_index ;

										print "
										<tr style=\"background: #$bg_color\" id=\"tr_op_assign_$opid\" class=\"tr_op_assign\">
											<td class=\"$td1\"><input type=\"checkbox\" id=\"ck_op_$opid\" name=\"opids[]\" value=\"$opid\" onClick=\"check_uncheck('op', this)\"></td>
											<td class=\"$td1\" style=\"opacity: 0.4; filter: alpha(opacity=40);\">$opid</td>
											<td class=\"$td1\" id=\"td_op_name_$opid\">$operator[name]</td>
											<!-- <td class=\"$td1\" nowrap id=\"td_op_login_$opid\"><div class=\"info_login\">$operator[login]</div></td> -->
											<td class=\"$td1\">$status_img</td>
											<td class=\"$td1\" id=\"td_op_email_$opid\">$operator[email]</td>
										</tr>
										" ;
									}
								}
							?>
							</tbody>
							</table>
						</div>
						<div style="margin-top: 15px;">
							<div class="info_neutral round_bottom_none" style="border-bottom: 0px;"><img src="../pics/icons/arrow_top.png" width="15" height="16" border="0" alt=""> Assign the above checked operators to departments: <img src="../pics/icons/arrow_down.png" width="15" height="16" border="0" alt=""></div>
							<div style="max-height: 320px; overflow: auto;" id="div_list_depts" class="info_neutral round_top_none">
								<table cellspacing=0 cellpadding=0 border=0 width="100%">
								<tr>
									<td width="20"><div class="td_dept_header"><input type="checkbox" onClick="check_all_depts(this)" id="ck_dept_all"></div></td>
									<td width="10"><div class="td_dept_header">ID</div></td>
									<td><div class="td_dept_header">Department Name</div></td>
								</tr>
								<?php
									$ops_assigned = 0 ;
									for ( $c = 0; $c < count( $departments ); ++$c )
									{
										$department = $departments[$c] ;
										$ops = Depts_get_DeptOps( $dbh, $department["deptID"] ) ;
										if ( count( $ops ) )
											$ops_assigned = 1 ;

										$td1 = "td_dept_td" ;
										$bg_color = ( ( $c + 1 ) % 2 ) ? "FFFFFF" : "EDEDED" ;

										if ( $department["name"] != "Archive" )
										{
											print "
											<tr style=\"background: #$bg_color\">
												<td class=\"$td1\"><input type=\"checkbox\" id=\"ck_dept_$department[deptID]\" name=\"deptids[]\" value=\"$department[deptID]\" onClick=\"check_uncheck('dept', this)\"></td>
												<td class=\"$td1\" style=\"opacity: 0.4; filter: alpha(opacity=40);\">$department[deptID]</td>
												<td class=\"$td1\" nowrap>
													<div style=\"\">$department[name]</div>
												</td>
											</tr>
											" ;
										}
									}
								?>
								</table>
							</div>

							<div style="margin-top: 15px;" class="info_warning"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../pics/icons/warning.gif" width="16" height="16" border="0" alt=""></td><td style="padding-left: 5px;">If the operator is online, and is assigned to a new department, they must logout and login again to receive chat request of the new assigned department.</td></tr></table></div>

							<div style="margin-top: 15px;">
								<button type="button" style="padding: 10px;" onClick="do_assign()" id="btn_submit_assign">Click to Assign</button>
							</div>
						</div>
						</form>
					</td>
					<td valign="top" style="padding-left: 25px;" width="350">
						<div style="margin-top: 15px;" class="title">
							<img src="../pics/icons/depts.png" width="16" height="16" border="0" alt=""> Operator to Department Assignment
						</div>
						<div style="margin-top: 15px; max-height: 1000px; overflow: auto;">
							<?php
								for ( $c = 0; $c < count( $departments ); ++$c )
								{
									$department = $departments[$c] ;
									$visible = ( $department["visible"] ) ? "<img src=\"../pics/icons/check.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"visible for selection\" title=\"visible for selection\">" : "<img src=\"../pics/icons/privacy_on.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"not visible for selection\" title=\"not visible for selection\">" ;
									$rtype = $vars_rtype[$department["rtype"]] ;

									if ( $department["rtype"] == 1 )
									{
										$rdescription = "The order that is displayed here is the defined order (top being always the first to receive the new chat request).<div id=\"div_defined_$department[deptID]\" style=\"display: none; margin-top: 5px; padding: 2px;\" class=\"info_box\">Mouse grab the name and move to update the order.</div>" ;
									}
									else if ( $department["rtype"] == 2 )
									{
										$rdescription = "The operator that has not accepted a chat in the longest time will be the first to receive the chat request." ;
									}
									else if ( $department["rtype"] == 3 )
										$rdescription = "All operators will receive the chat request at the same time." ;
									$rdescription = "<div style='margin-top: 5px;'>$rdescription</div>" ;

									if ( $department["name"] != "Archive" )
									{
										print "
											<div class=\"info_info\" style=\"margin-bottom: 5px;\">
												<div class=\"info_white round_bottom_none\"><a href=\"depts.php?ftab=vis\">$visible</a> <big><b>$department[name]</b></big> <span style=\"font-weight: normal;\">(<a href=\"depts.php?ftab=route\">$rtype</a>)</span>$rdescription</div>
												<div id=\"dept_ops_$department[deptID]\" style=\"min-height: 25px; max-height: 500px; overflow: auto;\"></div>
											</div>
										" ;
									}
								}
							?>
						</div>
					</td>
				</tr>
				</table>
				<?php endif ; ?>
			</div>
		</div>


		<div style="display: none;" id="ops_monitor">
			<div style="margin-top: 25px;">
				View operator online/offline status in a widget window.
			</div>

			<div style="margin-top: 25px;">
				<?php if ( !$total_operators ): ?>
				<div style="margin-top: 25px;"><span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add an <a href="ops.php" style="color: #FFFFFF;">Operator</a> to view this area.</span></div>
				<?php else: ?>
				<button type="button" onClick="launch_tools_op_status()" class="btn">Open Operator Status Monitor</button>
				<?php endif ; ?>
			</div>
		</div>

		<div style="display: none;" id="ops_online">
			<div style="margin-top: 25px;">
				<?php if ( !count( $departments ) ): ?>
				<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add a <a href="depts.php" style="color: #FFFFFF;">Department</a> to view this area.</span>
				<?php elseif ( !$total_operators ): ?>
				<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Add an <a href="ops.php" style="color: #FFFFFF;">Operator</a> to view this area.</span>
				<?php elseif ( !$ops_assigned ): ?>
				<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> <a href="ops.php?jump=assign" style="color: #FFFFFF;">Assign an operator to a department</a> to view this area.</span>
				<?php else: ?>

				<div style="margin-top: 5px;">
					<div class="li_op round" style="cursor: pointer;" onclick="$('#screen_one').prop('checked', true); location.href='ops.php?action=screen&screen=same';"><input type="radio" name="screen" id="screen_one" value="same" <?php echo $screen_same ?>> Same URL</div>
					<div class="li_op round" style="cursor: pointer;" onclick="$('#screen_two').prop('checked', true); location.href='ops.php?action=screen&screen=separate';"><input type="radio" name="screen" id="screen_two" value="separate" <?php echo $screen_separate ?>> Separate URLs</div>
					<div style="clear: both;"></div>
				</div>

				<div style="margin-top: 25px;">
					<div id="urls_same" style="display: none; padding: 25px; box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);" class="info_white">
						<div style="font-size: 14px; font-weight: bold;"><img src="../pics/icons/user_chat_big.png" width="24" height="24" border="0" alt=""> <img src="../pics/icons/settings_big.png" width="24" height="24" border="0" alt=""> Operator and Setup Admin Login URL</div>

						<div style="margin-top: 15px;">Provide the following URL to your <a href="ops.php?jump=">Chat Operators</a>.  The chat operators will need to login at this URL to go online and to receive visitor chat requests.</div>

						<div style="margin-top: 15px; font-size: 32px; font-weight: bold;"><a href="<?php echo ( !preg_match( "/^(http)/", $CONF["BASE_URL"] ) ) ? "http$https:$login_url" : $login_url ; ?>" target="new" style="color: #1DA1F2;" class="nounder"><?php echo ( !preg_match( "/^(http)/", $login_url ) ) ? "http$https:$login_url" : $login_url ; ?></a></div>
					</div>
					<div id="urls_separate" style="display: none;">
						<div class="info_white" style="padding: 25px; box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);">
							<div style="font-size: 14px; font-weight: bold;"><img src="../pics/icons/user_chat_big.png" width="24" height="24" border="0" alt=""> Operator Login URL</div>

							<div style="margin-top: 15px;">Provide the following URL to your <a href="ops.php?jump=">Chat Operators</a>.  The chat operators will need to login at this URL to go online and to receive visitor chat requests.</div>

							<div style="margin-top: 15px; font-size: 32px; font-weight: bold;"><a href="<?php echo ( !preg_match( "/^(http)/", $login_url ) ) ? "http$https:$login_url" : $login_url ; ?>" target="new" style="color: #1DA1F2;" class="nounder"><?php echo ( !preg_match( "/^(http)/", $login_url ) ) ? "http$https:$login_url" : $login_url ; ?></a></div>
						</div>

						<div class="info_white" style="margin-top: 25px; padding: 25px; box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);">
							<div style="font-size: 14px; font-weight: bold;"><img src="../pics/icons/settings_big.png" width="24" height="24" border="0" alt=""> Setup Admin Login URL</div>
							<div style="margin-top: 15px; font-size: 32px; font-weight: bold;"><a href="<?php echo ( !preg_match( "/^(http)/", $login_url ) ) ? "http$https:$login_url" : $login_url ; ?>/setup" target="new" style="color: #1DA1F2;" class="nounder"><?php echo ( !preg_match( "/^(http)/", $login_url ) ) ? "http$https:$login_url" : $login_url ; ?>/setup</a></div>
						</div>
					</div>
				</div>

				<div style="margin-top: 25px;" class="info_misc"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> If you have not done so already, copy/paste the chat icon <a href="./code.php">HTML Code</a> onto your webpages.</div>
				<?php endif ; ?>
			</div>
		</div>

		<div id="div_form" style="display: none; margin-top: 25px;" id="a_edit">
			<form method="POST" action="ops.php" id="theform">
			<input type="hidden" name="action" value="submit">
			<input type="hidden" name="jump" id="jump" value="">
			<input type="hidden" name="opid" id="opid" value="0">
			<input type="hidden" name="password" id="password" value="">

			<div style="">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td><div class="tab_form_title" style="background: #F4F6F8; border: 0px; text-align: left; font-weight: normal; text-shadow: none;"><span class="info_misc"><img src="../pics/icons/arrow_left.png" width="16" height="15" border="0" alt=""> <a href="JavaScript:void(0)" onClick="do_reset()">back</a></span></div></td>
					<td style="padding-left: 10px;">
						&nbsp;
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td nowrap class="tab_form_title">Status</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td><span class="info_good" style="cursor: pointer;" onClick="toggle_status_error(1);"><input type="radio" name="status" id="status_1" value="1" checked> Active</span></td>
							<td style="padding-left: 5px;"><span class="info_error" style="cursor: pointer;" onClick="toggle_status_error(0);"><input type="radio" name="status" id="status_0" value="0"> Inactive</span></td>
							<td style="padding-left: 5px;"><span id="span_inactive" style="display: none;" class="info_error">Inactive accounts will not be able to log in to the operator area.</span></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td nowrap class="tab_form_title">Operator Name</td>
					<td style="padding-left: 10px;"><input type="text" class="input" name="name" id="name" size="30" maxlength="40" value="" onKeyPress="return noquotes(event)" autocomplete="off"> <span class="span_ldap_found" style="display: none;"></span></td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Operator Email</td>
					<td style="padding-left: 10px;"><input type="text" class="input" name="email" id="email" size="30" maxlength="160" value="" onKeyPress="return justemails(event)"> <span class="span_ldap_found" style="display: none;"></span></td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Login<div style="font-size: 10px; font-weight: normal;">* letters and numbers only</div></td>
					<td style="padding-left: 10px;">
						<?php if ( $addon_ldap ): ?>
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td><img src="../addons/ldap/pics/ldap.png" width="34" height="34" border="0" alt="LDAP" title="LDAP"></td>
							<td style="padding-left: 5px;">
								<input type="hidden" id="ldap_verified" value="0">
								<a href="../addons/ldap/ldap.php">LDAP is enabled.</a>  Search for user login <input type="text" class="input" name="login" id="login" size="30" maxlength="60" onKeyPress="return justemails(event)" value="" autocomplete="off"> &nbsp; <button type="button" style="padding: 5px;" id="btn_ldap_search" onClick="ldap_do_search('..', $('#login').val())">Click to verify LDAP user login</button> <span class="span_ldap_found" style="display: none;"></span>
							</td>
						</tr>
						</table>
						<?php else: ?><input type="text" class="input" name="login" id="login" size="30" maxlength="60" value="" onKeyPress="return justemails(event)" autocomplete="off"><?php endif ; ?>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Password</td>
					<td style="padding-left: 10px;">
						<?php if ( $addon_ldap ): ?>
						<input type="hidden" name="password_temp" id="password_temp" value="<?php echo Util_Format_RandomString(45) ?>">
						<table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../addons/ldap/pics/ldap.png" width="34" height="34" border="0" alt="LDAP" title="LDAP"></td><td style="padding-left: 5px;"><a href="../addons/ldap/ldap.php">LDAP is enabled.</a>  System will use the LDAP credentials.</td></tr></table>
						<?php else: ?><input type="password" name="password_temp" id="password_temp" class="input" size="30" value="" autocomplete="off"><?php endif ; ?>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Max Concurrent Chats</td>
					<td style="padding-left: 10px;">
						<div style="margin-top: 5px;" class="info_warning">
							<img src="../pics/icons/info.png" width="14" height="14" border="0" alt=""> Max concurrent chat limit does not apply to department <a href="./depts.php?ftab=route"><b>simultaneous routing type</b></a>.  Simultaneous routing will not check the max limit.
							<div style="margin-top: 5px;"><img src="../pics/icons/info.png" width="14" height="14" border="0" alt=""> Max concurrent chat limit does not apply to <b>transferred chats</b>.  Transferred chats will not check the max limit.</div>
						</div>
						<div style="margin-top: 5px;">
							Maximum number of chat sessions this operator can have active at the same time:
							<select id="maxc" name="maxc">
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10" selected>10</option>
							</select>
						</div>
						<div style="margin-top: 5px;">
							When the operator reaches max concurrent chat sessions:
							<div style="margin-top: 10px;">
								<div><input type="radio" name="maxco" id="maxco_0" value=0 checked> (recommended) Skip the operator for all new chat requests until the operator's total concurrent chats is below the max.  If no other operators are online or are available, the chat request will be routed to the leave a message form.  Enable the <a href="depts.php?ftab=queue">"Waiting Queue"</a> if you prefer visitors to wait until the operator is available rather than being routed to the leave a message form.</div>
								<div style="margin-top: 10px;">
									<table cellspacing=0 cellpadding=0 border=0>
									<tr>
										<td valign="top"><input type="radio" name="maxco" id="maxco_1" value=1></td>
										<td style="padding-left: 3px;">
											Automatically switch the operator to OFFLINE status and automatically switch their status back to ONLINE when their total concurrent chats is below the max.
											<div style="margin-top: 5px;"><b>NOTE:</b> This will effect the operator's overall <a href="ops_reports.php">online activity duration</a> because their status will be offline.</div>
											<div style="margin-top: 5px;"><b>NOTE:</b> If the department "<a href="depts.php?ftab=queue">Waiting Queue</a> is enabled, this option is <b>not recommended</b> because the waiting queue requires online status.</div>
											<div style="margin-top: 5px;"><b>NOTE:</b> This method is not supported on the Mobile App.  Mobile App will always use the "Skip" method and the status will remain online.</div>
										</td>
									</tr>
									</table>
								</div>
							</div>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Chat Rating Survey</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>When a chat session ends, allow visitors to submit a rating for this operator (1-5 stars and optional comment).</td>
							<td style="padding-left: 5px;"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#rate_1').prop('checked', true);"><input type="radio" name="rate" id="rate_1" value="1" checked> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#rate_0').prop('checked', true);"><input type="radio" name="rate" id="rate_0" value="0"> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting" id="div_op2op">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Operator to Operator Chat<br>and Group Chat<br>and Message Board</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Enable the operator to chat with other online operators, be able to start a group chat with other operators and have access to the Operator Message Board.  If this setting is "Off", this operator will not be able to see other operators or their online/offline status because the "Operators" and "Message Board" menu options will not be visible on their operator console.</td>
							<td style="padding-left: 5px;" width="120"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#op2op_1').prop('checked', true);"><input type="radio" name="op2op" id="op2op_1" value="1" checked> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#op2op_0').prop('checked', true);$('#dept_offline_0').prop('checked', true);"><input type="radio" name="op2op" id="op2op_0" value="0"> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Specific Department Offline</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>
								If this operator is assigned to multiple departments, enable the ability for this operator to go online/offline for a specific department.  For example, if an operator is assigned to two departments, "Department A" and "Department B", they can go offline for "Department A" and be online for "Department B".  If set to "Off", the operator can either be online for all departments or offline for all departments.
								<div class="info_error" style="display: none; margin-top: 5px;" id="div_dept_offline_error">This feature requires <a href="JavaScript:void(0)" onClick="$('#div_op2op').fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast').fadeOut('fast').fadeIn('fast')">Operator-to-Operator Chat</a> to be enabled because the feature is located in that area.</div>
							</td>
							<td style="padding-left: 5px;" width="120"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="check_op2op(1)"><input type="radio" name="dept_offline" id="dept_offline_1" value="1"> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="check_op2op(0)"><input type="radio" name="dept_offline" id="dept_offline_0" value="0" checked> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Traffic Monitor</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Allow this operator to view the website traffic data and the ability to send a chat invite to the visitor.</td>
							<td style="padding-left: 5px;"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#traffic_1').prop('checked', true);"><input type="radio" name="traffic" id="traffic_1" value="1" checked> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#traffic_0').prop('checked', true);"><input type="radio" name="traffic" id="traffic_0" value="0"> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">View IP</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Allow this operator to view visitor's IP address and the <a href="extras_geo.php">GeoIP</a> information on the Traffic Monitor and during a chat session.</td>
							<td style="padding-left: 5px;"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#viewip_1').prop('checked', true);"><input type="radio" name="viewip" id="viewip_1" value="1"> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#viewip_0').prop('checked', true);"><input type="radio" name="viewip" id="viewip_0" value="0" checked> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Tag Chats</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Can "<a href="transcripts_tags.php">tag</a>" a chat during a chat session.</td>
							<td style="padding-left: 5px;"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#tag_1').prop('checked', true);"><input type="radio" name="tag" id="tag_1" value="1" checked> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#tag_0').prop('checked', true);"><input type="radio" name="tag" id="tag_0" value="0"> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="<?php echo ( !$addon_voice_chat ) ? "display: none;" : "" ; ?> margin-top: 15px;" class="div_setting_vc">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title"><img src="../themes/initiate/mic_yes.png" width="16" height="16" border="0" alt=""> Voice Chats</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>
								Can send Voice Chat requests to the visitor during a chat session.
								<div class="info_warning" style="margin-top: 5px;"><b>Important:</b> VPN, Firewall or other port blocking services may affect connectivity and the ability to connect. Voice chat connection is not always guaranteed.  Provide a <a href="../addons/voice_chat/voice_chat_admin.php">TURN server</a> to improve connectivity.</div>
							</td>
							<td style="padding-left: 5px;" width="130"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#peer_1').prop('checked', true);"><input type="radio" name="peer" id="peer_1" value="1"> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#peer_0').prop('checked', true);"><input type="radio" name="peer" id="peer_0" value="0" checked> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title"><img src="../pics/icons/attach.png" width="16" height="16" border="0" alt=""> File Upload
					<td style="padding-left: 10px;">
						<?php if ( $VARS_INI_UPLOAD ): ?>

						Allow this operator to upload files during a chat session.
						<div style="margin-top: 10px;">
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload(0)"><input type="checkbox" name="vupload[]" value="0" id="upload_0" onclick="toggle_upload(0)"> Off</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload(1)"><input type="checkbox" name="vupload[]" value="1" id="upload_1" onclick="toggle_upload(1)"> All</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('GIF')"><input type="checkbox" name="vupload[]" value="GIF" id="upload_GIF" onclick="toggle_upload('GIF')"> GIF</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('PNG')"><input type="checkbox" name="vupload[]" value="PNG" id="upload_PNG" onclick="toggle_upload('PNG')"> PNG</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('JPG')"><input type="checkbox" name="vupload[]" value="JPG" id="upload_JPG" onclick="toggle_upload('JPG')"> JPG, JPEG</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('PDF')"><input type="checkbox" name="vupload[]" value="PDF" id="upload_PDF" onclick="toggle_upload('PDF')"> PDF</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('ZIP')"><input type="checkbox" name="vupload[]" value="ZIP" id="upload_ZIP" onclick="toggle_upload('ZIP')"> ZIP</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('TAR')"><input type="checkbox" name="vupload[]" value="TAR" id="upload_TAR" onclick="toggle_upload('TAR')"> TAR</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('TXT')"><input type="checkbox" name="vupload[]" value="TXT" id="upload_TXT" onclick="toggle_upload('TXT')"> TXT</span>
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload('CONF')"><input type="checkbox" name="vupload[]" value="CONF" id="upload_CONF" onclick="toggle_upload('CONF')"> CONF</span>
						</div>

						<?php
							// feature not active at this time
							if ( count( $operators ) > 1 ) :
						?>
						<div id="div_copy_all" style="display: none; margin-top: 25px;">
							<span class="info_neutral"><input type="checkbox" id="copy_all" name="copy_all" value=1> copy this File Upload <img src="../pics/icons/attach.png" width="16" height="16" border="0" alt=""> setting to all operators</span>
						</div>
						<?php endif ; ?>

						<?php else: ?>
						<img src="../pics/icons/alert.png" width="16" height="16" border="0" alt=""> File upload is not enabled for this server ('<a href="http://php.net/manual/en/ini.core.php#ini.file-uploads" target="_blank">file_uploads</a>' directive).  Please contact the server admin for more information.
						<?php endif ; ?>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">View Chatting Number</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Allow this operator to view the total number of active chats of other operators. (example: John Doe - <b><i>chatting with 2 visitors</i></b>)</td>
							<td style="padding-left: 5px;"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#nchats_1').prop('checked', true);"><input type="radio" name="nchats" id="nchats_1" value="1"> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#nchats_0').prop('checked', true);"><input type="radio" name="nchats" id="nchats_0" value="0" checked> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">View Active Chats<br>and Whisper</td>
					<td style="padding-left: 10px;">
						Allow this operator to view active chat sessions.
						<div style="margin-top: 5px;"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> After logging in as an operator, the active chat sessions list (if enabled) is located at the "Reports" area.</div>
						<?php if ( $addon_whisper ): ?>
						<div class="info_warning" style="margin-top: 5px;"><b>Note:</b> Enabling this setting will also allow the operator to send whispers or participate in the chat when viewing the active chat session.</div>
						<?php else: ?>
						<div class="info_warning" style="margin-top: 5px;"><b>Note:</b> Whisper feature is not available for this system.  Whisper feature is only available for <a href="https://www.phplivesupport.com/r.php?r=pre_whisper" target="_blank">Enterprise Download</a> and On Demand clients.</div>
						<?php endif ; ?>
						<div style="margin-top: 5px;">
							<div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#view_chats_1').prop('checked', true);"><input type="radio" name="view_chats" id="view_chats_1" value="1"> View all active chats</div>
							<div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#view_chats_2').prop('checked', true);"><input type="radio" name="view_chats" id="view_chats_2" value="2"> View only their assigned department active chats</div>
							<div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#view_chats_0').prop('checked', true);"><input type="radio" name="view_chats" id="view_chats_0" value="0" checked> Off</div><div style="clear:both;"></div>
						</div>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" class="div_setting">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title"><img src="../pics/icons/mobile.png" width="16" height="16" border="0" alt=""> <a href="../mapp/settings.php">Mobile App Access</a></td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Allow this operator to login from the Mobile App.</td>
							<td style="padding-left: 5px;"><div class="li_op round" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#mapper_1').prop('checked', true);"><input type="radio" name="mapper" id="mapper_1" value="1"> On</div><div class="li_op round" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#mapper_0').prop('checked', true);"><input type="radio" name="mapper" id="mapper_0" value="0" checked> Off</div><div style="clear:both;"></div></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td><div class="tab_form_title" style="background: #F4F6F8; border: 0px; text-align: left; font-weight: normal; text-shadow: none;"><span class="info_misc"><img src="../pics/icons/arrow_left.png" width="16" height="15" border="0" alt=""> <a href="JavaScript:void(0)" onClick="do_reset()">back</a></span></div></td>
					<td style="padding-left: 10px;">
						<div id="div_op_online" style="display: none;" class="info_warning"><table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../pics/icons/warning.gif" width="16" height="16" border="0" alt=""></td><td style="padding-left: 5px;">If the operator is online, they must logout and login again for the changes to take effect on their operator console.</td></tr></table></div>

						<div style="margin-top: 25px;"><button type="button" onClick="do_submit()" class="btn" id="btn_submit">Submit</button> &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="do_reset()">cancel</a></div>
					</td>
				</tr>
				</table>
			</div>

			</form>
		</div>

		<div id="div_notice_disconnect" style="display: none; position: absolute; text-align: right;" class="info_error">
			<div style="padding: 10px;">
				<div class="edit_title">Operator <span id="span_login_disconnect"></span> is <span class="info_good">Online</span>.  Remote log out and set operator to offline?</div>

				<div style="margin-top: 15px;" id="remote_disconnect_button"><button type="button" class="btn" onClick="remote_disconnect()">Yes. Log out.</button> &nbsp; &nbsp; &nbsp; <a href="JavaScript:void(0)" style="color: #FFFFFF" onClick="$('#div_notice_disconnect').fadeOut('fast')">cancel</a></div>
				<div id="remote_disconnect_notice" style="display: none; margin-top: 15px;">Just a moment... <img src="../pics/loading_fb.gif" width="16" height="11" border="0" alt=""></div>
			</div>
		</div>

		<div id="div_notice_delete" style="display: none; position: absolute;" class="info_error">
			<div style="padding: 10px;">
				<div class="edit_title">Really delete operator account (<span id="span_login_delete"></span>)?</div>
				<div style="margin-top: 5px;">Deleting the operator account will also delete the operator's chat transcripts. &nbsp; &nbsp; <button type="button" onClick="$(this).attr('disabled', true);do_delete_doit();" class="btn">Yes. Delete.</button> &nbsp; &nbsp; &nbsp; <a href="JavaScript:void(0)" style="color: #FFFFFF" onClick="$('#div_notice_delete').fadeOut('fast')">cancel</a></div>
			</div>
		</div>
		<?php endif ; ?>

<div id="div_search_box" style="display: none; position: absolute; top: 0px; left: 0px; width: 220px; z-Index: 10; box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2);" class="info_neutral">
	<input type="text" class="input" id="search_string" size="15" maxlength="60" onKeyUp="return do_search(event)" autocomplete="off"> &nbsp; <a href="JavaScript:void(0)" onClick="toggle_search(this, 'close')">close</a>
</div>
<?php include_once( "./inc_footer.php" ) ?>
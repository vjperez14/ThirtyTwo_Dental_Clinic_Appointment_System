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

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_File.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/addons/API/Util_Addons.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }

	$deptinfo = Array() ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$option = Util_Format_Sanatize( Util_Format_GetVar( "option" ), "ln" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ;
	$error = Util_Format_Sanatize( Util_Format_GetVar( "error" ), "ln" ) ;

	$online = ( isset( $VALS['ONLINE'] ) && $VALS['ONLINE'] ) ? unserialize( $VALS['ONLINE'] ) : Array() ;
	$offline = ( isset( $VALS['OFFLINE'] ) && $VALS['OFFLINE'] ) ? unserialize( $VALS['OFFLINE'] ) : Array() ;
	$no_chat_icons = ( isset( $VALS['NO_CHAT_ICONS'] ) && $VALS['NO_CHAT_ICONS'] ) ? unserialize( $VALS['NO_CHAT_ICONS'] ) : Array() ;

	$online_is_default_svg = 0 ; $offline_is_default_svg = 0 ; $online_default_svg_active = 0 ; $offline_default_svg_active = 0 ;
	$svg_icons = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["svg_icons"] ) && $VALS_ADDONS["svg_icons"] ) ? unserialize( base64_decode( $VALS_ADDONS["svg_icons"] ) ) : Array() ;
	/***************************************/
	$svg_icons_online = Array() ;
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["online"] ) )
	{
		if ( isset( $svg_icons[$deptid]["online"][5] ) && preg_match( "/^<svg /i", $svg_icons[$deptid]["online"][5] ) )
			$svg_icons_online = $svg_icons[$deptid]["online"] ;
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && preg_match( "/^<svg /i", $svg_icons[0]["online"][5] ) )
	{
		$online_is_default_svg = 1 ;
		$svg_icons_online = $svg_icons[0]["online"] ;
	} if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && preg_match( "/^<svg /i", $svg_icons[0]["online"][5] ) )
	{
		$online_default_svg_active = 1 ;
	}

	$svg_icons_offline = Array() ;
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["offline"] ) )
	{
		if ( isset( $svg_icons[$deptid]["offline"][5] ) && preg_match( "/^<svg /i", $svg_icons[$deptid]["offline"][5] ) )
			$svg_icons_offline = $svg_icons[$deptid]["offline"] ;
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && preg_match( "/^<svg /i", $svg_icons[0]["offline"][5] ) )
	{
		$offline_is_default_svg = 1 ;
		$svg_icons_offline = $svg_icons[0]["offline"] ;
	} if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && preg_match( "/^<svg /i", $svg_icons[0]["offline"][5] ) )
	{
		$offline_default_svg_active = 1 ;
	}
	/***************************************/

	$online_is_default_text = 0 ; $offline_is_default_text = 0 ; $online_default_text_active = 0 ; $offline_default_text_active = 0 ;
	/***************************************/
	$text_icons_online = Array() ;
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["online"] ) )
	{
		if ( isset( $svg_icons[$deptid]["online"][5] ) && preg_match( "/^<span /i", $svg_icons[$deptid]["online"][5] ) )
		{
			$online_is_default_svg = 0 ;
			$svg_icons_online = Array() ;
			$text_icons_online = $svg_icons[$deptid]["online"] ;
		}
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && preg_match( "/^<span /i", $svg_icons[0]["online"][5] ) )
	{
		$online_is_default_text = 1 ;
		$text_icons_online = $svg_icons[0]["online"] ;
	} if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && preg_match( "/^<span /i", $svg_icons[0]["online"][5] ) )
	{
		$online_default_text_active = 1 ;
	}

	$text_icons_offline = Array() ;
	if ( $deptid && isset( $svg_icons[$deptid] ) && isset( $svg_icons[$deptid]["offline"] ) )
	{
		if ( isset( $svg_icons[$deptid]["offline"][5] ) && preg_match( "/^<span /i", $svg_icons[$deptid]["offline"][5] ) )
		{
			$offline_is_default_svg = 0 ;
			$svg_icons_offline = Array() ;
			$text_icons_offline = $svg_icons[$deptid]["offline"] ;
		}
	}
	else if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && preg_match( "/^<span /i", $svg_icons[0]["offline"][5] ) )
	{
		$offline_is_default_text = 1 ;
		$text_icons_offline = $svg_icons[0]["offline"] ;
	} if ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && preg_match( "/^<span /i", $svg_icons[0]["offline"][5] ) )
	{
		$offline_default_text_active = 1 ;
	}
	/***************************************/

	if ( $action === "upload" )
	{
		$icon = isset( $_FILES['icon_online']['name'] ) ? "icon_online" : "icon_offline" ;
		LIST( $error, $filename ) = Util_Upload_File( $icon, $deptid ) ;
		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER( "location: icons.php?action=success&deptid=$deptid&error=$error" ) ;
		exit ;
	}
	else if ( $action === "update_offline" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$url = Util_Format_Sanatize( Util_Format_GetVar( "url" ), "url" ) ;

		$departments = Depts_get_AllDepts( $dbh ) ;
		$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;

		$dept_hash = Array() ;
		for ( $c = 0; $c < count( $departments ); ++$c )
		{
			$department = $departments[$c] ;
			$dept_hash[$department["deptID"]] = 1 ; 
		}
		for ( $c = 0; $c < count( $dept_groups ); ++$c )
		{
			$department = $dept_groups[$c] ;
			$dept_hash[$department["groupID"]] = 1 ; 
		}

		foreach ( $offline as $key => $value )
		{
			if ( $key && !isset( $dept_hash[$key] ) )
				unset( $offline[$key] ) ;
		}
		$offline[$deptid] = ( $option == "redirect" ) ? $url : $option ;
		Util_Vals_WriteToFile( "OFFLINE", serialize( $offline ) ) ;
		$jump = "settings" ;
	}
	else if ( $action === "update_online" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

		$departments = Depts_get_AllDepts( $dbh ) ;
		$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;

		$dept_hash = Array() ;
		for ( $c = 0; $c < count( $departments ); ++$c )
		{
			$department = $departments[$c] ;
			$dept_hash[$department["deptID"]] = 1 ; 
		}
		for ( $c = 0; $c < count( $dept_groups ); ++$c )
		{
			$department = $dept_groups[$c] ;
			$dept_hash[$department["groupID"]] = 1 ; 
		}

		foreach ( $online as $key => $value )
		{
			if ( $key && !isset( $dept_hash[$key] ) )
				unset( $online[$key] ) ;
		}
		$online[$deptid] = ( $option == "redirect" ) ? $url : $option ;
		Util_Vals_WriteToFile( "ONLINE", serialize( $online ) ) ;
		$jump = "settings" ;
	}
	else if ( $action === "reset" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

		if ( $option == "online" )
		{
			foreach ( $online as $key => $value )
			{
				if ( $key && ( $key == $deptid ) )
					unset( $online[$key] ) ;
			} Util_Vals_WriteToFile( "ONLINE", serialize( $online ) ) ;
		}
		else if ( $option == "offline" )
		{
			foreach ( $offline as $key => $value )
			{
				if ( $key && ( $key == $deptid ) )
					unset( $offline[$key] ) ;
			} Util_Vals_WriteToFile( "OFFLINE", serialize( $offline ) ) ;
		}
		else if ( ( $option == "icon_online" ) || ( $option == "icon_offline" ) )
		{
			if ( $deptid )
			{
				$dir_files = glob( $CONF["CONF_ROOT"]."/$option"."_$deptid.*", GLOB_NOSORT ) ;
				$total_dir_files = count( $dir_files ) ;
				if ( $total_dir_files )
				{
					for ( $c = 0; $c < $total_dir_files; ++$c )
					{
						if ( $dir_files[$c] && is_file( $dir_files[$c] ) ) { @unlink( $dir_files[$c] ) ; }
					}
				}
				if ( isset( $svg_icons[$deptid] ) )
				{
					unset( $svg_icons[$deptid] ) ;
					Util_Addons_WriteToFile( "svg_icons", base64_encode( serialize( $svg_icons ) ) ) ;
				}
				if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
				HEADER( "location: icons.php?action=success&deptid=$deptid" ) ;
				exit ;
			}
		}
	}

	if ( !isset( $departments ) ) { $departments = Depts_get_AllDepts( $dbh ) ; }
	if ( $deptid ) { $deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ; }

	$online_option = "embed" ;
	if ( isset( $online[$deptid] ) ) { $online_option = $online[$deptid] ; }
	else
	{
		if ( isset( $online[0] ) ) { $online_option = $online[0] ; }
	}

	$offline_option = "embed" ; $redirect_url = "" ;
	if ( isset( $offline[$deptid] ) )
	{
		if ( !preg_match( "/^(icon|hide|embed|tab)$/", $offline[$deptid] ) ) { $offline_option = "redirect" ; $redirect_url = $offline[$deptid] ; }
		else{ $offline_option = $offline[$deptid] ; }
	}
	else
	{
		if ( isset( $offline[0] ) )
		{
			if ( !preg_match( "/^(icon|hide|embed|tab)$/", $offline[0] ) ) { $offline_option = "redirect" ; $redirect_url = $offline[0] ; }
			else{ $offline_option = $offline[0] ; }
		}
	}
	$mobile_newwin = ( isset( $VALS["MOBILE_NEWWIN"] ) && is_numeric( $VALS["MOBILE_NEWWIN"] ) ) ? intval( $VALS["MOBILE_NEWWIN"] ) : 0 ;
	if ( !isset( $dept_groups ) ) { $dept_groups = Depts_get_AllDeptGroups( $dbh ) ; }
	$dept_groups_hash = Array() ;
	for ( $c = 0; $c < count( $dept_groups ); ++$c )
	{
		$dept_group = $dept_groups[$c] ;
		$dept_groups_hash[$dept_group["groupID"]] = $dept_group["name"] ;
	}

	$global_default_online = Util_Upload_GetChatIcon( "icon_online", 0 ) ;
	$global_default_offline = Util_Upload_GetChatIcon( "icon_offline", 0 ) ;

	$online_is_default_image = 0 ; $offline_is_default_image = 0 ;
	if ( $deptid )
	{
		$online_image = Util_Upload_GetChatIcon( "icon_online", $deptid ) ;
		$offline_image = Util_Upload_GetChatIcon( "icon_offline", $deptid ) ;

		if ( !$online_is_default_svg && !$online_is_default_text && ( ( !isset( $svg_icons[0] ) && !isset( $svg_icons[0]["online"] ) ) || ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["online"] ) && $svg_icons[0]["online"][0] ) ) && ( $online_image == $global_default_online ) )
			$online_is_default_image = 1 ;
		if ( !$offline_is_default_svg && !$offline_is_default_text && ( ( !isset( $svg_icons[0] ) && !isset( $svg_icons[0]["offline"] ) ) || ( isset( $svg_icons[0] ) && isset( $svg_icons[0]["offline"] ) && $svg_icons[0]["offline"][0] ) ) && ( $offline_image == $global_default_offline ) )
			$offline_is_default_image = 1 ;
	}
	else
	{
		$online_image = $global_default_online ;
		$offline_image = $global_default_offline ;
	}

	$alttext_array = ( isset( $VALS["alttext"] ) && $VALS["alttext"] ) ? unserialize( $VALS["alttext"] ) : Array() ;
	$alttext_array_dept = Array() ; $alttext_using_global = 1 ;
	if ( isset( $alttext_array[$deptid] ) )
	{
		$alttext_using_global = 0 ;
		$alttext_array_dept = $alttext_array[$deptid] ;
	}
	else if ( $deptid && isset( $alttext_array[0] ) )
	{
		$alttext_array_dept = $alttext_array[0] ;
	} array_walk( $alttext_array_dept, "Util_Format_base64_decode_array" ) ;
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
<script data-cfasync="false" type="text/javascript" src="../addons/svg/js/spectrum.js?<?php echo filemtime ( "../addons/svg/js/spectrum.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../addons/svg/js/svg.js?<?php echo filemtime ( "../addons/svg/js/svg.js" ) ; ?>"></script>
<link rel="Stylesheet" href="../addons/svg/css/spectrum.css?<?php echo filemtime ( "../addons/svg/css/spectrum.css" ) ; ?>">

<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var deptid = <?php echo $deptid ?> ;
	var global_mobilenewwin = <?php echo $mobile_newwin ?> ;
	var global_jump = "<?php echo $jump ?>" ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "icons" ) ;

		show_div('<?php echo $jump ?>') ;

		if ( $('#div_chaticons').length )
		{
			<?php if ( $action === "ob" ): ?>
				toggle_ob() ;
			<?php elseif ( $action && !$error ): ?>
				do_alert( 1, "Update Success" ) ;
				if ( "<?php echo $action ?>" == "update_ob_clean" ) { toggle_ob() ; }
			<?php endif ; ?>

			<?php if ( $action && $error ): ?>do_alert( 0, "<?php echo $error ?>" ) ;<?php endif ; ?>
			<?php if ( $deptid ): ?>$('#div_notice_html').show() ;<?php endif ; ?>
			if ( [].filter ) { svg_init() ; }

			<?php if ( ( $action != "upload" ) ): ?>
				<?php if ( isset( $svg_icons_online[0] ) ): ?>
					toggle_type('svg', 'online') ;
					toggle_status('svg', 'online') ;
					toggle_active( 'svg', 'online' ) ;
					$('#div_default_image_online').hide() ;
					$('#div_default_text_online').hide() ;
				<?php elseif ( isset( $text_icons_online[0] ) ): ?>
					toggle_type('text', 'online') ;
					toggle_status('text', 'online') ;
					toggle_active( 'text', 'online' ) ;
					$('#div_default_image_online').hide() ;
					$('#div_default_svg_online').hide() ;
				<?php else: ?>
					toggle_type('image', 'online') ;
					toggle_status('image', 'online') ;
					toggle_active( 'image', 'online' ) ;
					$('#div_default_svg_online').hide() ;
					$('#div_default_text_online').hide() ;
				<?php endif ; ?>

				<?php if ( isset( $svg_icons_offline[0] ) ): ?>
					toggle_type('svg', 'offline') ;
					toggle_status('svg', 'offline') ;
					toggle_active( 'svg', 'offline' ) ;
					$('#div_default_image_offline').hide() ;
					$('#div_default_text_offline').hide() ;
				<?php elseif ( isset( $text_icons_offline[0] ) ): ?>
					toggle_type('text', 'offline') ;
					toggle_status('text', 'offline') ;
					toggle_active( 'text', 'offline' ) ;
					$('#div_default_image_offline').hide() ;
					$('#div_default_svg_offline').hide() ;
				<?php else: ?>
					toggle_type('image', 'offline') ;
					toggle_status('image', 'offline') ;
					toggle_active( 'image', 'offline' ) ;
					$('#div_default_svg_offline').hide() ;
					$('#div_default_text_offline').hide() ;
				<?php endif ; ?>
			<?php endif ; ?>

			<?php if ( !isset( $text_icons_online[0] ) && !isset( $svg_icons_online[0] ) ): ?>
				$('#image_online_status_image').prop( "checked", true ) ;
			<?php elseif ( isset( $text_icons_online[0] ) ): ?>
				$('#image_online_status_text').prop( "checked", true ) ;
			<?php elseif ( isset( $svg_icons_online[0] ) ): ?>
				$('#image_online_status_svg').prop( "checked", true ) ;
			<?php endif ; ?>

			<?php if ( !isset( $text_icons_offline[0] ) && !isset( $svg_icons_offline[0] ) ): ?>
				$('#image_offline_status_image').prop( "checked", true ) ;
			<?php elseif ( isset( $text_icons_offline[0] ) ): ?>
				$('#image_offline_status_text').prop( "checked", true ) ;
			<?php elseif ( isset( $svg_icons_offline[0] ) ): ?>
				$('#image_offline_status_svg').prop( "checked", true ) ;
			<?php endif ; ?>

			<?php if ( $jump == "settings" ): ?>
			show_div_behavior( 'online', 'options' ) ; show_div_behavior( 'offline', 'options' ) ;
			<?php else: ?>
			show_div_behavior( 'online', 'icon' ) ; show_div_behavior( 'offline', 'icon' ) ;
			<?php endif ; ?>
		}
	});

	function switch_dept( theobject )
	{
		location.href = "icons.php?deptid="+theobject.value+"&jump="+global_jump+"&"+unixtime() ;
	}

	function switch_dept_alttext( theobject )
	{
		location.href = "icons.php?deptid="+theobject.value+"&jump=alttext&"+unixtime() ;
	}

	function show_div( thediv )
	{
		$('#div_alert').hide() ;

		// for situations the department is changed when viewing settings
		if ( thediv == "settings" ) { thediv = "chaticons" ; }
		if ( thediv )
		{
			var divs = Array( "chaticons", "noicon", "alttext", "iconsettings" ) ;
			for ( var c = 0; c < divs.length; ++c )
			{
				$('#div_'+divs[c]).hide() ;
				$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
			}

			$('#div_'+thediv).show() ;
			$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
		}
	}

	function show_div_behavior( theicon, thediv )
	{
		$('#div_alert').hide() ;

		var divs = Array( "icon", "options" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#'+theicon+'_'+divs[c]).hide() ;
			$('#menu_'+theicon+'_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu3') ;
		}

		$('#'+theicon+'_'+thediv).show() ;
		$('#menu_'+theicon+'_'+thediv).removeClass('op_submenu3').addClass('op_submenu_focus') ;

		if ( thediv == "options" )
		{
			global_jump = "settings" ;
			$('#'+theicon+'_svg_radio').hide() ;
			$('#div_'+theicon+'_icon_image').hide() ;
			$('#div_'+theicon+'_icon_svg').hide() ;
			$('#div_'+theicon+'_icon_text').hide() ;
		}
		else
		{
			global_jump = "" ;
			$('#'+theicon+'_svg_radio').show() ;

			if ( $('#'+theicon+'_icon_type_image').is(':checked') )
				toggle_type( 'image', theicon ) ;
			else if ( $('#'+theicon+'_icon_type_svg').is(':checked') )
				toggle_type( 'svg', theicon ) ;
			else
				toggle_type( 'text', theicon ) ;
		}
	}

	function check_url()
	{
		var url = $('#offline_url').val().trim() ;
		var url_ok = ( url.match( /(http:\/\/)|(https:\/\/)/i ) ) ? 1 : 0 ;

		if ( !url )
			return "Please provide the webpage URL." ;
		else if ( !url_ok )
			return "URL should begin with http:// or https:// protocol." ;
		else
			return false ;
	}

	function open_url()
	{
		var unique = unixtime() ;
		var url = $('#offline_url').val().trim() ;
		var error = check_url() ;

		if ( error )
			do_alert( 0, error ) ;
		else
			window.open(url, unique, 'scrollbars=yes,menubar=yes,resizable=1,location=yes,toolbar=yes,status=1') ;
	}

	function update_online()
	{
		var unique = unixtime() ;
		var option = $("input[name='online_option']:checked").val() ;

		location.href = "./icons.php?action=update_online&deptid=<?php echo $deptid ?>&option="+option+"&"+unique ;
	}

	function update_offline()
	{
		var unique = unixtime() ;
		var option = $("input[name='offline_option']:checked").val() ;
		var error = check_url() ;

		if ( error && ( option == "redirect" ) )
			do_alert( 0, error ) ;
		else
		{
			var url = encodeURIComponent( $('#offline_url').val().trim().replace( /http/ig, "hphp" ) ) ;
			location.href = "./icons.php?action=update_offline&deptid=<?php echo $deptid ?>&option="+option+"&url="+url+"&"+unique ;
		}
	}

	function reset_doit( theicon, thedeptid )
	{
		if ( confirm( "Reset to Global Default "+theicon+" window settings?" ) )
			location.href = "./icons.php?action=reset&jump=settings&option="+theicon+"&deptid="+thedeptid ;
	}

	function reset_icon( theicon, thedeptid )
	{
		if ( confirm( "Reset to use Global Default icon?  This will reset both online AND offline icons to use the Global Default." ) )
			location.href = "./icons.php?action=reset&option="+theicon+"&deptid="+thedeptid ;
	}

	function confirm_change( theflag )
	{
		if ( global_mobilenewwin != theflag )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_mobile_newwin&value="+theflag+"&"+unixtime(),
				success: function(data){
					global_mobilenewwin = theflag ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
	}

	function toggle_type( thetype, theicon )
	{
		if ( !$('#'+theicon+'_icon_type_'+thetype).prop('checked') )
			$('#'+theicon+'_icon_type_'+thetype).prop('checked', true) ;

		if ( theicon == "online" )
		{
			$("#online_svg_radio").find('*').each( function(){
				var div_name = this.id ;
				if ( div_name.indexOf("menu_icons_") != -1 )
					$(this).removeClass('info_misc').addClass('info_neutral') ;
			} );
			$('#menu_icons_'+thetype+'_'+theicon).removeClass('info_neutral').addClass('info_misc') ;
		}
		else
		{
			$("#offline_svg_radio").find('*').each( function(){
				var div_name = this.id ;
				if ( div_name.indexOf("menu_icons_") != -1 )
					$(this).removeClass('info_misc').addClass('info_neutral') ;
			} );
			$('#menu_icons_'+thetype+'_'+theicon).removeClass('info_neutral').addClass('info_misc') ;
		}

		if ( thetype == "image" )
		{
			$('#div_'+theicon+'_icon_svg').hide() ;
			$('#div_'+theicon+'_icon_text').hide() ;
			$('#div_'+theicon+'_icon_image').show() ;
		}
		else if ( thetype == "svg" )
		{
			if ( [].filter )
			{
				$('#div_'+theicon+'_icon_image').hide() ;
				$('#div_'+theicon+'_icon_text').hide() ;
				$('#div_'+theicon+'_icon_svg').show() ;
			}
			else
			{
				do_alert( 0, "This browser does not support SVG images.  Please use a modern browser." ) ;
			}
		}
		else
		{
			$('#div_'+theicon+'_icon_image').hide() ;
			$('#div_'+theicon+'_icon_svg').hide() ;
			$('#div_'+theicon+'_icon_text').show() ;
		}
	}

	function toggle_active( thetype, theicon )
	{
		$("#"+theicon+"_svg_radio").find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf("span_icons_") != -1 )
				$(this).html( $(this).html().replace( / \(active\)/i, "" ) ) ;
		} );
		$('#span_icons_'+thetype+'_'+theicon).html( $('#span_icons_'+thetype+'_'+theicon).html()+" <span class='info_good round_top_none round_bottom_none'>(active)</span>" ) ;
	}

	function toggle_status( thetype, theicon )
	{
		$('#svg_'+theicon+'_status_'+thetype).prop('checked', true) ;
		$('#text_'+theicon+'_status_'+thetype).prop('checked', true) ;
	}

	function update_alttext( thereset )
	{
		var json_data = new Object ;
		var unique = unixtime( ) ;

		var alt_query = "" ;
		if ( !thereset )
		{
			$("#table_alttext").find('*').each( function(){
				var div_name = this.id ;
				if ( div_name.indexOf("alt_") == 0 )
					alt_query += div_name+"="+encodeURIComponent( $(this).val().trim() )+"&" ;
			} );
		}
		else
		{
			if ( !confirm( "Reset to Global Default values?" ) )
				return false ;
			alt_query = "reset=1" ;
		}

		$('#btn_alttext').attr( "disabled", true ) ;

		$.ajax({
		type: "POST",
		url: "../ajax/setup_actions_.php",
		data: "action=update_alttext&deptid=<?php echo $deptid ?>&"+alt_query+"&"+unique,
		success: function(data){
			eval( data ) ;
			$('#btn_alttext').attr( "disabled", false ) ;

			if ( json_data.status )
			{
				location.href = "icons.php?action=success&deptid=<?php echo $deptid ?>&jump=alttext" ;
			}
			else
				do_alert( 0, json_data.error ) ;

		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
		} });
	}

	function display_text(e, theicon )
	{
		if ( noquotes(e) )
		{
			var text = $('#input_text_'+theicon).val().trim() ;
			$('#span_text_text_'+theicon).html( text ) ;

			if ( !$('#span_'+theicon+'_text_cancel').is(":visible") )
				$('#span_'+theicon+'_text_cancel').show() ;
		}
	}

	var confirm_noicon_processing = 0 ;
	function confirm_noicon( thedeptid, thevalue )
	{
		do_alert( 0, "Feature is not yet available.  Sorry about that." ) ;
		return false ;

		if ( !confirm_noicon_processing )
		{
			var json_data = new Object ;

			confirm_noicon_processing = 1 ;

			$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_.php",
			data: "action=update_dept_noicon&deptid="+thedeptid+"&value="+thevalue+"&"+unixtime(),
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					location.href = "icons.php?jump=noicon&action=success" ;
				}
				else
				{
					confirm_noicon_processing = 0 ;
					do_alert( 0, "Error processing request. Please refresh the page and try again.") ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
			} });
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["icons"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu_focus" style="margin-left: 0px;" onClick="show_div('chaticons')" id="menu_chaticons">Chat Icons</div>
			<!-- <div class="op_submenu" onClick="show_div('noicon')" id="menu_noicon">No Chat Icon</div> -->
			<div class="op_submenu" onClick="show_div('alttext')" id="menu_alttext">Alt Text</div>
			<div class="op_submenu" onClick="show_div('iconsettings')" id="menu_iconsettings">Mobile Behavior</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;" id="div_chaticons">
			<?php if ( count( $departments ) > 1 ): ?>
			<div>
				<form method="POST" action="" id="form_theform">
				<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this )">
				<option value="0">Global Default</option>
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
				</form>
			</div>
			<div id="div_notice_html" style="display: none; margin-top: 15px;"><span class="info_dept"> You must use the <a href="code.php?deptid=<?php echo $deptid ?>">Department Specific HTML Code</a> to display this department chat icon.</span></div>
			<?php endif ; ?>

			<div id="div_alert" style="display: none; margin-top: 25px;"></div>

			<div style="margin-top: 25px;">
			
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td>
						<div style="padding-bottom: 15px;">
							<div class="op_submenu" onClick="show_div_behavior('online', 'icon')" id="menu_online_icon" style="margin-left: 0px;">Online Icon</div>
							<div class="op_submenu3" onClick="show_div_behavior('online', 'options')" id="menu_online_options">Online Setting</div>
							<div style="clear: both"></div>
						</div>
					</td>
					<td>
						<div style="padding-bottom: 15px;">
							<div class="op_submenu" onClick="show_div_behavior('offline', 'icon')" id="menu_offline_icon" >Offline Icon</div>
							<div class="op_submenu3" onClick="show_div_behavior('offline', 'options')" id="menu_offline_options">Offline Setting</div>
							<div style="clear: both"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%" style="padding-right: 10px; padding-top: 25px;">
						<form method="POST" action="icons.php" enctype="multipart/form-data" id="form_online" name="form_online">
						<input type="hidden" name="action" value="upload">
						<input type="hidden" name="deptid" value="<?php echo $deptid ?>">
						<input type="hidden" name="MAX_FILE_SIZE" value="200000">
						<div class="edit_title">
							<?php
								if ( isset( $deptinfo["name"] ) )
									print $deptinfo["name"] ;
								else if ( isset( $dept_groups_hash[$deptid] ) )
									print $dept_groups_hash[$deptid] ;
								else
									print "Global Default" ;
							?>
							<span class="info_good">ONLINE</span> Chat Icon
						</div>
						<div id="online_svg_radio" style="margin-top: 25px;">
							<span class="info_neutral" id="menu_icons_image_online" style="cursor: pointer;" onclick="toggle_type('image', 'online')"><input type="radio" id="online_icon_type_image" name="online_icon_type" value="image" checked> <span id="span_icons_image_online">Image</span></span>
							<span class="info_neutral" id="menu_icons_svg_online" style="margin-left: 5px; cursor: pointer;" onclick="toggle_type('svg', 'online')"><input type="radio" id="online_icon_type_svg" name="online_icon_type" value="svg" > <span id="span_icons_svg_online">SVG</span></span>
							<span class="info_neutral" id="menu_icons_text_online" style="margin-left: 5px; cursor: pointer;" onclick="toggle_type('text', 'online')"><input type="radio" id="online_icon_type_text" name="online_icon_type" value="text" > <span id="span_icons_text_online">Text</span></span>
						</div>
						<div id="div_online_icon_image" style="display: none; margin-top: 25px;" class="info_neutral">
							<div style="text-shadow: none;">
								<div class="info_good" style="float: left; cursor: pointer; text-shadow: none; padding: 3px; margin-right: 10px;" onclick="svg_submit('online', 'image', 1)"><input type="radio" name="image_online_status" id="image_online_status_image" value="1"> Use Image</div>
								<div class="info_neutral" style="float: left; cursor: pointer; text-shadow: none; padding: 3px; margin-right: 10px;" onclick="svg_submit('online', 'svg', 1)"><input type="radio" name="image_online_status" id="image_online_status_svg" value="0"> Use SVG</div>
								<div class="info_neutral" style="float: left; cursor: pointer; text-shadow: none; padding: 3px; margin-right: 10px;" onclick="svg_submit('online', 'text', 1)"><input type="radio" name="image_online_status" id="image_online_status_text" value="0"> Use Text</div>
								<div style="clear: both;"></div>
							</div>
							<div style="margin-top: 15px;">
								<div><input type="file" name="icon_online" size="30"></div>
								<div style="margin-top: 5px;"><input type="submit" value="Upload Image" style="margin-top: 10px;" class="btn"></div>
							</div>

							<div style="margin-top: 15px;"><img src="<?php print $online_image ?>" border="0" alt=""></div>
							<?php if ( $deptid ): ?>
							<div style="margin-top: 25px;" id="div_default_image_online">
								<?php if ( $online_is_default_image && !$online_default_svg_active && !$online_default_text_active ): ?>
								&bull; currently using <a href="icons.php">Global Default</a>
								<?php else: ?>
								<img src="../pics/icons/reset.png" width="16" height="16" border="0" alt=""> reset to use <a href="JavaScript:void(0)" onClick="reset_icon( 'icon_online', <?php echo $deptid ?> )">Global Default</a>
								<?php endif ; ?>
							</div>
							<?php endif ; ?>

							<div style="margin-top: 45px;">Browse available chat icons at the <a href="https://www.phplivesupport.com/r.php?r=icons" target="_blank">chat icons download page</a>.</div>
						</div>
						<?php $icon_svg = "online" ; include( "../addons/svg/inc_icons_svg.php" ) ; ?>
						<?php $icon_text = "online" ; include( "../addons/svg/inc_icons_text.php" ) ; ?>
						<div id="online_options" style="display: none; margin-top: 25px; line-height: 160%;" class="info_info">
							<table cellspacing=1 cellpadding=5 border=0 width="100%">
							<tr>
								<td colspan=2><div style="font-size: 14px; font-weight: bold;">When the <span class="info_good">ONLINE</span> chat icon is clicked:</div></td>
							</tr>
							<tr>
								<td width="25" align="center" style="padding-top: 10px;"><input type="radio" name="online_option" value="popup" <?php echo ( $online_option == "popup" ) ? "checked" : "" ; ?>></td>
								<td style="padding-top: 10px;">Open the chat in a new <span class="info_box" style="padding: 2px;">popup</span> window.</td>
							</tr>
							<tr>
								<td width="25" align="center" style="padding-top: 10px;"><input type="radio" name="online_option" value="tab" <?php echo ( $online_option == "tab" ) ? "checked" : "" ; ?>></td>
								<td style="padding-top: 10px;">Open the chat in a new <span class="info_box" style="padding: 2px;">tabbed</span> window.</td>
							</tr>
							<tr>
								<td width="25" align="center" style="padding-top: 10px;"><input type="radio" name="online_option" value="embed" <?php echo ( $online_option == "embed" ) ? "checked" : "" ; ?>></td>
								<td style="padding-top: 10px;">Open the chat in an <span class="info_box" style="padding: 2px;">embed</span> window on the webpage.</td>
							</tr>
							<tr>
								<td></td>
								<td><div style="padding-top: 5px;"><button type="button" onClick="update_online()" class="btn">Update</button>
								&nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="$('#form_online').get(0).reset(); show_div_behavior('online', 'icon');">cancel</a> &nbsp; 
								<?php
									if ( $deptid && !isset( $online[$deptid] ) ):
										print " &bull; currently using Global Default settings" ;
									elseif ( $deptid ):
										print " &nbsp; <img src=\"../pics/icons/reset.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\"> reset to use <a href=\"JavaScript:void(0)\" onClick=\"reset_doit( 'online', $deptid )\">Global Default</a>" ;
									endif ;
								?>
								</div></td>
							</tr>
							</table>
						</div>
						</form>
					</td>
					<td valign="top" width="50%" style="padding-left: 10px; padding-top: 25px;">
						<form method="POST" action="icons.php" enctype="multipart/form-data" id="form_offline" name="form_offline">
						<input type="hidden" name="action" value="upload">
						<input type="hidden" name="deptid" value="<?php echo $deptid ?>">
						<input type="hidden" name="MAX_FILE_SIZE" value="200000">
						<div class="edit_title">
							<?php
								if ( isset( $deptinfo["name"] ) )
									print $deptinfo["name"] ;
								else if ( isset( $dept_groups_hash[$deptid] ) )
									print $dept_groups_hash[$deptid] ;
								else
									print "Global Default" ;
							?>
							<span class="info_error">OFFLINE</span> Chat Icon
						</div>
						<div id="offline_svg_radio" style="margin-top: 25px;">
							<span class="info_neutral" id="menu_icons_image_offline" style="cursor: pointer;" onclick="toggle_type('image', 'offline')"><input type="radio" id="offline_icon_type_image" name="offline_icon_type" value="image" checked> <span id="span_icons_image_offline">Image</span></span>
							<span class="info_neutral" id="menu_icons_svg_offline" style="margin-left: 5px; cursor: pointer;" onclick="toggle_type('svg', 'offline')"><input type="radio" id="offline_icon_type_svg" name="offline_icon_type" value="svg" > <span id="span_icons_svg_offline">SVG</span></span>
							<span class="info_neutral" id="menu_icons_text_offline" style="margin-left: 5px; cursor: pointer;" onclick="toggle_type('text', 'offline')"><input type="radio" id="offline_icon_type_text" name="offline_icon_type" value="text" > <span id="span_icons_text_offline">Text</span></span>
						</div>
						<div id="div_offline_icon_image" style="display: none; margin-top: 25px;" class="info_neutral">
							<div style="text-shadow: none;">
								<div class="info_good" style="float: left; cursor: pointer; text-shadow: none; padding: 3px; margin-right: 10px;" onclick="svg_submit('offline', 'image', 1)"><input type="radio" name="image_offline_status" id="image_offline_status_image" value="1"> Use Image</div>
								<div class="info_neutral" style="float: left; cursor: pointer; text-shadow: none; padding: 3px; margin-right: 10px;" onclick="svg_submit('offline', 'svg', 1)"><input type="radio" name="image_offline_status" id="image_offline_status_svg" value="0"> Use SVG</div>
								<div class="info_neutral" style="float: left; cursor: pointer; text-shadow: none; padding: 3px; margin-right: 10px;" onclick="svg_submit('offline', 'text', 1)"><input type="radio" name="image_offline_status" id="image_offline_status_text" value="0"> Use Text</div>
								<div style="clear: both;"></div>
							</div>
							<div style="margin-top: 15px;">
								<div><input type="file" name="icon_offline" size="30"></div>
								<div style="margin-top: 5px;"><input type="submit" value="Upload Image" style="margin-top: 10px;" class="btn"></div>
							</div>

							<div style="margin-top: 15px;"><img src="<?php print $offline_image ?>" border="0" alt=""></div>
							<?php if ( $deptid ): ?>
							<div style="margin-top: 25px;" id="div_default_image_offline">
								<?php if ( $offline_is_default_image && !$offline_default_svg_active && !$offline_default_text_active ): ?>
								&bull; currently using <a href="icons.php">Global Default</a>
								<?php else: ?>
								<img src="../pics/icons/reset.png" width="16" height="16" border="0" alt=""> reset to use <a href="JavaScript:void(0)" onClick="reset_icon( 'icon_offline', <?php echo $deptid ?> )">Global Default</a>
								<?php endif ; ?>
							</div>
							<?php endif ; ?>

							<div style="margin-top: 45px;">Browse available chat icons at the <a href="https://www.phplivesupport.com/r.php?r=icons" target="_blank">chat icons download page</a>.</div>
						</div>
						<?php $icon_svg = "offline" ; include( "../addons/svg/inc_icons_svg.php" ) ; ?>
						<?php $icon_text = "offline" ; include( "../addons/svg/inc_icons_text.php" ) ; ?>
						<div id="offline_options" style="display: none; margin-top: 25px; line-height: 160%;" class="info_info">
							<table cellspacing=1 cellpadding=5 border=0 width="100%">
							<tr>
								<td colspan=2><div style="font-size: 14px; font-weight: bold;">When the <span class="info_error">OFFLINE</span> chat icon is clicked:</div></td>
							</tr>
							<tr>
								<td width="25" align="center" style="padding-top: 10px;"><input type="radio" name="offline_option" value="icon" <?php echo ( $offline_option == "icon" ) ? "checked" : "" ; ?>></td>
								<td style="padding-top: 10px;">Display the offline chat icon and open the leave a message in a new <span class="info_box" style="padding: 2px;">popup</span> window.</td>
							</tr>
							<tr>
								<td width="25" align="center"><input type="radio" name="offline_option" value="tab" <?php echo ( $offline_option == "tab" ) ? "checked" : "" ; ?>></td>
								<td>Display the offline chat icon and open the leave a message in a new <span class="info_box" style="padding: 2px;">tabbed</span> window.</td>
							</tr>
							<tr>
								<td width="25" align="center"><input type="radio" name="offline_option" value="embed" <?php echo ( $offline_option == "embed" ) ? "checked" : "" ; ?>></td>
								<td>Display the offline chat icon and <span class="info_box" style="padding: 2px;">embed</span> the leave a message window on the webpage.</td>
							</tr>
							<tr>
								<td width="25" align="center"><input type="radio" name="offline_option" id="option_redirect" value="redirect" <?php echo ( $offline_option == "redirect" ) ? "checked" : "" ; ?> onClick="$('#offline_url').focus()"></td>
								<td>
									Display the offline chat icon and redirect the visitor to a webpage. Provide the redirect URL below:
									<div style="margin-top: 5px;">
										<input type="text" class="input" style="width: 80%;" maxlength="255" name="offline_url" id="offline_url" value="<?php echo $redirect_url ?>" onFocus="$('#option_redirect').prop('checked', true)" onKeyPress="return noquotestags(event)"> &nbsp; <span style="">&middot; <a href="JavaScript:void(0)" onClick="open_url()">visit</a></span>
									</div>
								</td>
							</tr>
							<tr>
								<td width="25" align="center"><input type="radio" name="offline_option" value="hide" <?php echo ( $offline_option == "hide" ) ? "checked" : "" ; ?>></td>
								<td>
									Do not display the offline chat icon.
								</td>
							</tr>
							<tr>
								<td></td>
								<td><div style="padding-top: 5px;"><button type="button" onClick="update_offline()" class="btn">Update</button>
								&nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="$('#form_offline').get(0).reset(); show_div_behavior('offline', 'icon');">cancel</a> &nbsp; 
								<?php
									if ( $deptid && !isset( $offline[$deptid] ) ):
										print " &bull; currently using Global Default settings" ;
									elseif ( $deptid ):
										print " &nbsp; <img src=\"../pics/icons/reset.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\"> reset to use <a href=\"JavaScript:void(0)\" onClick=\"reset_doit( 'offline', $deptid )\">Global Default</a>" ;
									endif ;
								?>
								</div></td>
							</tr>
							</table>
						</div>
						</form>
					</td>
				</tr>
				</table>

			</div>
		</div>

		<div style="display: none; margin-top: 25px;" id="div_noicon">
			<div><b>NOTE:</b> Because the chat window will be loaded automatically on every page load, this method is only suggested if your server has enough resource.</div>
			<div style="margin-top: 15px;" class="edit_title td_dept_td">Departments</div>
			<table cellspacing=0 cellpadding=0 border=0 id="table_dept">
			<?php
				$output = "" ;
				for ( $c = 0; $c < count( $departments ); ++$c )
				{
					$department = $departments[$c] ;
					$this_deptid = $department["deptID"] ;

					if ( $department["name"] != "Archive" )
					{
						$td1 = "td_dept_td" ;
						$checked_on = "" ;
						if ( !isset( $no_chat_icons[$this_deptid] ) ) { $checked_on = "checked" ; }
						$checked_off = ( !$checked_on ) ? "checked" : "" ;

						$div_onoff = "<div style=\"margin-top: 15px;\">
							<div class=\"info_good\" style=\"float: left; width: 125px; text-shadow: none; cursor: pointer;\" onclick=\"$('#dept_noicon_{$this_deptid}_0').prop('checked', true);confirm_noicon($this_deptid, 0)\"><input type=\"radio\" name=\"dept_noicon_{$this_deptid}\" id=\"dept_noicon_{$this_deptid}_0\" value=\"0\" $checked_on> Display Chat Icon</div>
							<div class=\"info_neutral\" style=\"float: left; margin-left: 10px; width: 215px; text-shadow: none; cursor: pointer;\" onclick=\"$('#dept_noicon_{$this_deptid}_1').prop('checked', true);confirm_noicon($this_deptid, 1)\"><input type=\"radio\" name=\"dept_noicon_{$this_deptid}\" id=\"dept_noicon_{$this_deptid}_1\" value=\"1\" $checked_off> Use Embed Window Minimized</div><div style=\"clear: both;\"></div>
						</div>" ;

						$output .= "<tr>
							<td class=\"$td1\" nowrap>
								$department[name]
							</td>
							<td class=\"$td1\">$div_onoff</td>
						</tr>" ;
					}
				}
				if ( count( $dept_groups ) )
				{
					for ( $c = 0; $c < count( $dept_groups ); ++$c )
					{
						$dept_group = $dept_groups[$c] ;
						$this_deptid = $dept_group["groupID"] ;

						$td1 = "td_dept_td" ;
						$output .= "<tr><td class=\"$td1\" nowrap>$dept_group[name] [Department Group]</td>" ;
					}
				}
				print $output ;
			?>
			</table>
		</div>

		<div style="display: none; margin-top: 25px;" id="div_alttext">
			Alt texts are text that is displayed when the image (chat icons, chat invite, embed window actions) cannot be loaded or when the mouse is over the image.

			<?php if ( count( $departments ) > 1 ): ?>
			<div style="margin-top: 15px;">
				<form method="POST" action="" id="form_alttext">
				<select name="deptid_alttext" id="deptid_alttext" style="font-size: 16px;" onChange="switch_dept_alttext( this )">
				<option value="0">Global Default</option>
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
				</form>
			</div>
			<?php endif ; ?>

			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=5 border=0 width="100%" id="table_alttext">
				<tr>
					<td valign="bottom" width="25%">
						<div><span class="info_good">Online</span> Online chat icon</div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_online" maxlength="100" value="<?php echo isset( $alttext_array_dept["online"] ) ? $alttext_array_dept["online"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
					<td valign="bottom" width="25%">
						<div><span class="info_error">Offline</span> Offline chat icon</div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_offline" maxlength="100" value="<?php echo isset( $alttext_array_dept["offline"] ) ? $alttext_array_dept["offline"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
					<td valign="bottom" width="25%">
						<div><a href="code_invite.php">Automatic Chat Invite</a> image</div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_invite" maxlength="100" value="<?php echo isset( $alttext_array_dept["invite"] ) ? $alttext_array_dept["invite"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
					<td valign="bottom" width="25%">
						<div><a href="code_invite.php">Automatic</a> and <a href="../addons/proaction/proaction.php">ProAction</a> invite close <img src="../themes/initiate/close_box.png" width="14" height="14" border="0" alt=""></div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_close" maxlength="100" value="<?php echo isset( $alttext_array_dept["close"] ) ? $alttext_array_dept["close"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
				</tr>
				<tr>
					<td valign="bottom" width="25%">
						<div><img src="../themes/initiate/win_min.png" width="16" height="16" border="0" alt=""> embed chat minimize</div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_emminimize" maxlength="100" value="<?php echo isset( $alttext_array_dept["emminimize"] ) ? $alttext_array_dept["emminimize"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
					<td valign="bottom" width="25%">
						<div><img src="../themes/initiate/win_max.png" width="16" height="16" border="0" alt=""> embed chat maximize</div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_emmaximize" maxlength="100" value="<?php echo isset( $alttext_array_dept["emmaximize"] ) ? $alttext_array_dept["emmaximize"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
					<td valign="bottom" width="25%">
						<div><img src="../themes/initiate/win_close.png" width="16" height="16" border="0" alt=""> embed chat close</div>
						<div style="margin-top: 5px;"><input type="text" class="input" style="width: 50%" id="alt_emclose" maxlength="100" value="<?php echo isset( $alttext_array_dept["emclose"] ) ? $alttext_array_dept["emclose"] : "" ; ?>" onKeyPress="return noquotestagscomma(event)" autocomplete="off"></div>
					</td>
				</tr>
				</table>
			</div>

			<div style="padding-top: 25px;">
				<button type="button" onClick="update_alttext(0)" class="btn" id="btn_alttext">Update</button> &nbsp;
				<?php if ( $deptid && $alttext_using_global ): ?>
					&bull; currently using <a href="icons.php?jump=alttext">Global Default</a> values
				<?php elseif ( $deptid ): ?>
					<img src="../pics/icons/reset.png" width="16" height="16" border="0" alt=""> reset to use <a href="JavaScript:void(0)" onClick="update_alttext(1)">Global Default</a> values
				<?php endif ; ?>
			</div>
		</div>

		<div style="display: none; margin-top: 25px;" id="div_iconsettings">
			<b>Mobile behavior:</b> This setting overrides the <a href="JavaScript:void(0)" onClick="show_div_behavior( 'online', 'options' ) ; show_div_behavior( 'offline', 'options' ) ; show_div('chaticons');">Online/Offline Setting</a>.
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0><tr><td><img src="../pics/icons/info.png" width="16" height="16" border="0" alt=""></td><td style="padding-left: 5px;"> If the webpage does not contain a <a href="https://www.phplivesupport.com/r.php?r=viewport" target="_blank">viewport</a>, the chat window will automatically open in a new window on mobile devices.  The automatic behavior is to ensure the chat window is displayed correctly based on the mobile device screen size.</td></tr></table>
			</div>

			<div style="margin-top: 25px;">
				<div class="info_neutral" style="cursor: pointer;" onclick="$('#mobile_newwin_0').prop('checked', true);confirm_change(0);"><input type="radio" name="mobile_newwin" id="mobile_newwin_0" value="0" <?php echo ( $mobile_newwin === 0 ) ? "checked" : "" ; ?>> Use <a href="JavaScript:void(0)" onClick="show_div_behavior( 'online', 'options' ) ; show_div_behavior( 'offline', 'options' ) ; show_div('chaticons');">Online/Offline Setting</a></div>
				<div class="info_neutral" style="margin-top: 15px; cursor: pointer;" onclick="$('#mobile_newwin_2').prop('checked', true);confirm_change(2);"><input type="radio" name="mobile_newwin" id="mobile_newwin_2" value="2" <?php echo ( $mobile_newwin === 2 ) ? "checked" : "" ; ?>> Always open the chat request in a new window for mobile visitors (not including iPad visitors).</div>
			</div>
		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
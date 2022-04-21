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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/addons/API/Util_Addons.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$ao = Util_Format_Sanatize( Util_Format_GetVar( "ao" ), "n" ) ;
	$ftab = Util_Format_Sanatize( Util_Format_GetVar( "ftab" ), "ln" ) ;
	$dept_themes = ( isset( $VALS["THEMES"] ) && $VALS["THEMES"] ) ? unserialize( $VALS["THEMES"] ) : Array() ;
	$error = "" ;
	if ( isset( $THEMES_EXCLUDE[$CONF["THEME"]] ) ) { $CONF["THEME"] = "default" ; }

	if ( $action === "submit" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/put.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
		$name = Util_Format_StripQuotes( Util_Format_Sanatize( Util_Format_GetVar( "name" ), "ln" ) ) ;
		$email = Util_Format_Sanatize( Util_Format_GetVar( "email" ), "e" ) ;
		$visible = Util_Format_Sanatize( Util_Format_GetVar( "visible" ), "n" ) ;
		$rtype = Util_Format_Sanatize( Util_Format_GetVar( "rtype" ), "n" ) ;
		$rtime = Util_Format_Sanatize( Util_Format_GetVar( "rtime" ), "n" ) ;
		$vupload = Util_Format_Sanatize( Util_Format_GetVar( "vupload" ), "a" ) ;
		$screenshot = Util_Format_Sanatize( Util_Format_GetVar( "screenshot" ), "n" ) ;
		$ctimer = Util_Format_Sanatize( Util_Format_GetVar( "ctimer" ), "n" ) ;
		$smtp_md5 = Util_Format_Sanatize( Util_Format_GetVar( "smtp" ), "ln" ) ;
		$tshare = Util_Format_Sanatize( Util_Format_GetVar( "tshare" ), "n" ) ;
		$traffic = Util_Format_Sanatize( Util_Format_GetVar( "traffic" ), "n" ) ;
		$texpire = Util_Format_Sanatize( Util_Format_GetVar( "texpire" ), "n" ) ;
		$lang = Util_Format_Sanatize( Util_Format_GetVar( "lang" ), "ln" ) ;
		$theme = Util_Format_Sanatize( Util_Format_GetVar( "theme" ), "ln" ) ;

		if ( !is_file( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) )
			$lang = "english" ;
		include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) ;

		$department_pre = Depts_get_DeptInfoByName( $dbh, $name ) ;
		if ( ( isset( $department_pre["deptID"] ) && !$deptid ) || ( isset( $department_pre["deptID"] ) && ( $department_pre["deptID"] != $deptid ) ) ) { $error = "Department name ($name) is already in use." ; }
		else
		{
			$smtp_temp = Depts_get_SMTPByMd5( $dbh, $smtp_md5 ) ;
			$smtp = ( $smtp_temp ) ? $smtp_temp : "" ;

			if ( $name != "Archive" )
			{
				$queue = 0 ;
				if ( isset( $department_pre["deptID"] ) )
					$queue = $department_pre["queue"] ;

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
				if ( !$deptid = Depts_put_Department( $dbh, $deptid, $name, $email, $visible, $queue, $rtype, $rtime, 6, strtoupper( $vupload_val ), $ctimer, $smtp, $tshare, $texpire, $lang ) ) { $error = "DB Error: $dbh[error]" ; }
			}
			else { $error = "Department name (Archive) is used by the system." ; }

			if ( !$error )
			{
				$departments = Depts_get_AllDepts( $dbh ) ;
				if ( count( $departments ) == 1 )
				{
					if ( !isset( $CONF["lang"] ) || ( isset( $CONF["lang"] ) && ( $CONF["lang"] != $lang ) ) ) { $error = ( Util_Vals_WriteToConfFile( "lang", $lang ) ) ? "" : "Could not write to config file. [e1]" ; }
					if ( !$error && ( !isset( $CONF["THEME"] ) || ( isset( $CONF["THEME"] ) && ( $CONF["THEME"] != $theme ) ) ) ) { $error = ( Util_Vals_WriteToConfFile( "THEME", $theme ) ) ? "" : "Could not write to vals file. [e1]" ; }
				}
				else if ( count( $departments ) == 2 )
				{
					$auto_connect_array = ( isset( $VALS["auto_connect"] ) && $VALS["auto_connect"] ) ? unserialize( $VALS["auto_connect"] ) : Array() ;

					if ( isset( $auto_connect_array[0] ) && ( $auto_connect_array[0]["auto_connect"] != "op" ) )
					{
						$thisdeptid = ( $departments[0]["deptID"] == $deptid ) ? $departments[1]["deptID"] : $departments[0]["deptID"] ;

						$auto_connect_array[$thisdeptid] = $auto_connect_array[0] ;
						unset( $auto_connect_array[0] ) ;
						Util_Vals_WriteToFile( "auto_connect", serialize( $auto_connect_array ) ) ; usleep( 250000 ) ;
					}
				}

				$marquee_array = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["marquees"] ) && $VALS_ADDONS["marquees"] ) ? unserialize( base64_decode( $VALS_ADDONS["marquees"] ) ) : Array() ;
				if ( isset( $marquee_array[0] ) )
				{
					$marquee_array[$deptid] = $marquee_array[0] ;
					Util_Addons_WriteToFile( "marquees", base64_encode( serialize($marquee_array) ) ) ; usleep( 250000 ) ;
				}

				if ( $theme )
				{
					if ( ( $deptid && isset( $dept_themes[$deptid] ) && ( $dept_themes[$deptid] == $theme ) ) || ( isset( $CONF["THEME"] ) && ( $CONF["THEME"] == $theme ) ) ) {
						if ( isset( $dept_themes[$deptid] ) ) { unset( $dept_themes[$deptid] ) ; }
					}
					else { $dept_themes[$deptid] = $theme ; }
					$error = ( Util_Vals_WriteToFile( "THEMES", serialize( $dept_themes ) ) ) ? "" : "Could not write to vals file. [e2]" ; usleep( 250000 ) ;
				}

				if ( $screenshot && ( !count( $vupload ) || ( count( $vupload ) && !$vupload[0] ) ) )
					$screenshot = 0 ;

				$screenshots = ( isset( $VALS["SCREENSHOTS"] ) && $VALS["SCREENSHOTS"] ) ? unserialize( $VALS["SCREENSHOTS"] ) : Array() ;

				$process = 0 ;
				if ( isset( $screenshots[$deptid] ) && !$screenshot ) { unset( $screenshots[$deptid] ) ; $process = 1 ; }
				else if ( $screenshot ) { $screenshots[$deptid] = 1 ; $process = 1 ; }
				if ( $process )
				{
					$error = ( Util_Vals_WriteToFile( "SCREENSHOTS", serialize( $screenshots ) ) ) ? "" : "Could not write to vals file. [e3]" ; usleep( 250000 ) ;
				}

				if ( !$error )
				{
					if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
					HEADER( "location: depts.php?action=success" ) ;
					exit ;
				}
			}
		}
	}
	else if ( $action === "delete" )
	{
		$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;

		if ( $deptid && !Depts_get_IsDeptInGroup( $dbh, $deptid ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/remove.php" ) ;
			include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

			Depts_remove_Dept( $dbh, $deptid ) ;

			$update_vals = 0 ;
			if ( isset( $dept_themes[$deptid] ) ) { unset( $dept_themes[$deptid] ) ; $update_vals = 1 ; }
			if ( count( $dept_themes ) || $update_vals ) { $error = ( Util_Vals_WriteToFile( "THEMES", serialize( $dept_themes ) ) ) ? "" : "Could not write to vals file. [e3]" ; }

			$departments = Depts_get_AllDepts( $dbh ) ;
			if ( count( $departments ) == 1 )
			{
				$auto_connect_array = ( isset( $VALS["auto_connect"] ) && $VALS["auto_connect"] ) ? unserialize( $VALS["auto_connect"] ) : Array() ;
				$department = $departments[0] ;
				if ( isset( $department["lang"] ) && $department["lang"] && ( $CONF["lang"] != $department["lang"] ) )
				{
					$lang = $department["lang"] ;
					$error = ( Util_Vals_WriteToConfFile( "lang", $lang ) ) ? "" : "Could not write to config file. [e2]" ;
					usleep( 10000 ) ;
					$CONF["lang"] = $lang ;
				}
				if ( isset( $dept_themes[$department["deptID"]] ) && ( $dept_themes[$department["deptID"]] != $CONF["THEME"] ) )
				{
					$error = ( Util_Vals_WriteToConfFile( "THEME", $dept_themes[$department["deptID"]] ) ) ? "" : "Could not write to vals file. [e5]" ;
					usleep( 10000 ) ;
					$CONF["THEME"] = $dept_themes[$department["deptID"]] ;
				}
				if ( isset( $auto_connect_array[$deptid] ) )
				{
					unset( $auto_connect_array[$deptid] ) ;
					$this_deptid = $departments[0]["deptID"] ;

					if ( isset( $auto_connect_array[$this_deptid] ) )
					{
						// copy the 1 department auto connect setting to 0 so that phplive.php processes it correctly for 0 ID
						$auto_connect_array[0] = $auto_connect_array[$this_deptid] ;
						$auto_connect_array[0]["deptid"] = 0 ;
						Util_Vals_WriteToFile( "auto_connect", serialize( $auto_connect_array ) ) ;
						usleep( 10000 ) ;
					}
				}
				$emlogos_hash = ( isset( $VALS["EMLOGOS"] ) && $VALS["EMLOGOS"] ) ? unserialize( $VALS["EMLOGOS"] ) : Array() ;
				if ( isset( $emlogos_hash[$department["deptID"]] ) )
				{
					$emlogos_hash[0] = $emlogos_hash[$department["deptID"]] ;
					Util_Vals_WriteToFile( "EMLOGOS", serialize( $emlogos_hash ) ) ;
				}
				else if ( count( $emlogos_hash ) )
				{
					$emlogos_hash = Array() ;
					Util_Vals_WriteToFile( "EMLOGOS", serialize( $emlogos_hash ) ) ;
				}
				$marquee_array = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["marquees"] ) && $VALS_ADDONS["marquees"] ) ? unserialize( base64_decode( $VALS_ADDONS["marquees"] ) ) : Array() ;
				if ( isset( $marquee_array[$department["deptID"]] ) )
				{
					$marquee_array[0] = $marquee_array[$department["deptID"]] ;
					Util_Addons_WriteToFile( "marquees", base64_encode( serialize($marquee_array) ) ) ;
				}
				$svg_update = 0 ;
				$svg_icons = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["svg_icons"] ) && $VALS_ADDONS["svg_icons"] ) ? unserialize( base64_decode( $VALS_ADDONS["svg_icons"] ) ) : Array() ;
				if ( isset( $svg_icons[$deptid] ) )
				{
					$svg_update = 1 ;
					unset( $svg_icons[$deptid] ) ;
				}
				if ( isset( $svg_icons[$department["deptID"]] ) )
				{
					$svg_update = 1 ;
					$svg_icons[0] = $svg_icons[$department["deptID"]] ;
					unset( $svg_icons[$department["deptID"]] ) ;
				}
				if ( $svg_update )
					Util_Addons_WriteToFile( "svg_icons", base64_encode( serialize( $svg_icons ) ) ) ;
			}
			else if ( !count( $departments ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Lang/remove.php" ) ;
				Lang_remove_Lang( $dbh, 0 ) ;
			}
			else
			{
				$marquee_array = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["marquees"] ) && $VALS_ADDONS["marquees"] ) ? unserialize( base64_decode( $VALS_ADDONS["marquees"] ) ) : Array() ;
				if ( isset( $marquee_array[$deptid] ) )
				{
					if ( isset( $marquee_array[0] ) && ( $marquee_array[0] == $marquee_array[$deptid] ) )
						unset( $marquee_array[0] ) ;
					unset( $marquee_array[$deptid] ) ;
					Util_Addons_WriteToFile( "marquees", base64_encode( serialize($marquee_array) ) ) ;
				}
			}

			$dir_files = glob( $CONF["CONF_ROOT"]."/logo_*", GLOB_NOSORT ) ;
			$total_dir_files = count( $dir_files ) ;
			if ( $total_dir_files )
			{
				for ( $c = 0; $c < $total_dir_files; ++$c )
				{
					if ( $dir_files[$c] && is_file( $dir_files[$c] ) && preg_match( "/logo_$deptid\./", $dir_files[$c] ) )
					{
						@unlink( $dir_files[$c] ) ;
					}
				}
			}

			// need to fetch again to get remaining departments
			$departments = Depts_get_AllDepts( $dbh ) ;
			if ( count( $departments ) == 1 )
			{
				$department = $departments[0] ;
				$temp_deptid = $department["deptID"] ;

				$default_logo = $dept_logo = "" ;
				if ( $total_dir_files )
				{
					for ( $c = 0; $c < $total_dir_files; ++$c )
					{
						if ( $dir_files[$c] && is_file( $dir_files[$c] ) )
						{
							if ( preg_match( "/logo_0\./", $dir_files[$c] ) )
								$default_logo = $dir_files[$c] ;
							else if ( preg_match( "/logo_$temp_deptid\./", $dir_files[$c] ) )
								$dept_logo = $dir_files[$c] ;
						}
					}
				}
				if ( $dept_logo )
				{
					if ( $default_logo ) { @unlink( $default_logo ) ; }
				}
			}
		}
		else
			$error = "Department is assigned to a Department Group.  Department cannot be deleted." ;
	}
	else if ( $action === "update_lang" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		$prev_lang = Util_Format_Sanatize( Util_Format_GetVar( "prev_lang" ), "ln" ) ;
		$lang = Util_Format_Sanatize( Util_Format_GetVar( "lang" ), "ln" ) ;

		if ( is_file( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) )
		{
			$error = ( Util_Vals_WriteToConfFile( "lang", $lang ) ) ? "" : "Could not write to config file. [e3]" ;
			if ( !$error )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

				$CONF["lang"] = $lang ;
				Depts_update_DeptLangs( $dbh, $prev_lang, $lang ) ;
			}
		}
		else { $error = "Invalid language." ; }
	}

	if ( !isset( $departments ) )
		$departments = Depts_get_AllDepts( $dbh ) ;

	// filter for departments with SMTP
	$departments_smtp = $smtp_temp = Array() ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		if ( $department["smtp"] && !isset( $smtp_temp[$department["smtp"]] ) )
		{
			$departments_smtp[$department["deptID"]] = $department["smtp"] ;
			$smtp_temp[$department["smtp"]] = true ;
		}
	}

	$addon_auto_respond = is_file( "$CONF[DOCUMENT_ROOT]/addons/auto_reply/inc_iframe.php" ) ? 1 : 0 ;
	$dept_vars = Depts_get_AllDeptsVars( $dbh ) ;

	$auto_offline = ( isset( $VALS["AUTO_OFFLINE"] ) && $VALS["AUTO_OFFLINE"] ) ? unserialize( $VALS["AUTO_OFFLINE"] ) : Array() ;
	$screenshots = ( isset( $VALS["SCREENSHOTS"] ) && $VALS["SCREENSHOTS"] ) ? unserialize( $VALS["SCREENSHOTS"] ) : Array() ;
	$themes_js = "" ;
	foreach ( $dept_themes as $key => $value )
	{
		if ( isset( $THEMES_EXCLUDE[$value] ) ) { $value = "default" ; }
		$themes_js .= "themes[$key] = '$value' ; " ;
	}

	$dept_groups_hash = Array() ; $dept_groups_js = "" ;
	$dept_groups = Depts_get_AllDeptGroups( $dbh ) ;
	for ( $c = 0; $c < count( $dept_groups ); ++$c )
	{
		$dept_group = $dept_groups[$c] ;
		$deptids = explode( ",", $dept_group["deptids"] ) ;
		for ( $c2 = 0; $c2 < count( $deptids ); ++$c2 )
		{
			$deptid_temp = $deptids[$c2] ;
			if ( $deptid_temp && !isset( $dept_groups_hash[$deptid_temp] ) ) { $dept_groups_hash[$deptid_temp] = 1 ; $dept_groups_js .= "dept_groups[$deptid_temp] = 1 ; " ; }
		}
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

<script data-cfasync="false" type="text/javascript">
<!--
	var global_deptid ;
	var global_option ;
	var global_div_list_height ;
	var global_div_form_height ;
	var themes = new Object ;
	var max_menus = 8 ;

	var dept_groups = new Object ; <?php echo $dept_groups_js ?>

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;

		init_menu() ;
		toggle_menu_setup( "depts" ) ;

		init_divs() ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;
		<?php elseif ( $action && $error ): ?>do_alert( 0, "<?php echo $error ?>" ) ;
		<?php endif ; ?>

		eval( "<?php echo $themes_js ?>" ) ;

		<?php if ( $ao ): ?>
		$('*[id*=menu_8_]').each(function() {
			$(this).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		}) ;
		<?php endif ; ?>

		<?php if ( $ftab == "vis" ): ?>
			$(".div_class_visible").fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		<?php elseif ( $ftab == "connect" ): ?>
			$('*[id*=menu_1_]').each(function() {
				$(this).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			}) ;
		<?php elseif ( $ftab == "msg" ): ?>
			$('*[id*=menu_2_]').each(function() {
				$(this).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			}) ;
		<?php elseif ( $ftab == "cans" ): ?>
			$('*[id*=menu_3_]').each(function() {
				$(this).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			}) ;
		<?php elseif ( $ftab == "queue" ): ?>
			$('*[id*=menu_5_]').each(function() {
				$(this).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			}) ;
		<?php elseif ( $ftab == "route" ): ?>
			$('.div_class_route').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		<?php elseif ( $ftab == "email" ): ?>
			$('.div_class_email').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		<?php elseif ( $ftab == "req" ): ?>
			$('*[id*=menu_6_]').each(function() {
				$(this).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			}) ;
		<?php elseif ( $ftab == "lang" ): ?>
			$('.div_class_lang').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
			$('#div_tab_lang_primary').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		<?php endif ; ?>

	});
	$(window).resize(function() { });

	function init_divs()
	{
		global_div_list_height = $('#div_list').outerHeight() ;
		global_div_form_height = $('#div_form').outerHeight() ;
	}

	function do_submit()
	{
		var name = $( "input#name" ).val() ;
		var email = $( "input#email" ).val() ;

		if ( name == "" )
			do_alert( 0, "Please provide the department name." ) ;
		else if ( !check_email( email ) )
			do_alert( 0, "Please provide a valid email address." ) ;
		else if ( !check_visible() )
		{
			do_alert( 0, "The one available department must be \"Visible for Selection\"." ) ;
			setTimeout( function(){ $('#td_option_visible').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ; }, 3500 ) ;
		}
		else
		{
			$('#btn_submit').attr('disabled', true) ;
			$('#theform').submit() ;
		}
	}

	function do_options( theoption, thedeptid, thebgcolor )
	{
		var unique = unixtime() ;
		global_option = theoption ;
		global_deptid = thedeptid ;

		for ( var c = 1; c <= max_menus; ++c )
		{
			if ( c != theoption )
				$('#menu_'+c+"_"+thedeptid).removeClass('menu_dept_focus').addClass('menu_dept') ;
		}

		if ( $('#iframe_edit_'+thedeptid).is(':visible') && ( document.getElementById('iframe_edit_'+thedeptid).contentWindow.option == theoption ) )
		{
			$('#iframe_'+thedeptid).fadeOut("fast") ;

			$('#menu_'+theoption+"_"+thedeptid).removeClass('menu_dept_focus').addClass('menu_dept') ;
		}
		else
		{
			$('#iframe_edit_'+thedeptid).attr('src', 'iframe_edit_'+theoption+'.php?bgcolor='+thebgcolor+'&option='+theoption+'&deptid='+thedeptid+'&'+unique ) ;

			$('#iframe_'+thedeptid).fadeIn("fast") ;
			$('#menu_'+theoption+"_"+thedeptid).removeClass('menu_dept').addClass('menu_dept_focus') ;
		}
	}

	function do_edit( thedeptid, thename, theemail, thertype, thertime, thevupload, thescreenshot, thectimer, thetexpire, thevisible, thequeue, thetshare, thelang, thesmtp_md5 )
	{
		global_deptid = thedeptid ;

		$( "input#deptid" ).val( thedeptid ) ;
		$( "input#name" ).val( thename ) ;
		$( "input#email" ).val( theemail ) ;
		$( "select#rtime" ).val( thertime ) ;
		$( "select#texpire" ).val( thetexpire ) ;
		$( "select#smtp" ).val( thesmtp_md5 ) ;
		$( "input#rtype_"+thertype ).prop( "checked", true ) ;
		$( '#ctimer_'+thectimer ).prop('checked', true) ;
		$( "input#visible_"+thevisible ).prop( "checked", true ) ;
		$( "input#tshare_"+thetshare ).prop( "checked", true ) ;
		$( "input#screenshot_"+thescreenshot ).prop( "checked", true ) ;

		if ( thelang ) { $( "select#lang" ).val( thelang ) ; }
		else { $( "select#lang" ).val( "<?php echo $CONF["lang"] ?>" ) ; }
		if ( typeof( themes[thedeptid] ) != "undefined" ) { $( "select#theme" ).val( themes[thedeptid] ) ; }

		toggle_td_visible( parseInt( thevisible ) ) ;
		show_form( thedeptid ) ;

		do_upload_checked( thevupload ) ;
		$('#div_dept_online').show() ;
	}

	function toggle_td_visible( thevisible )
	{
		if ( thevisible ) { $('#td_option_visible').removeClass('info_error').addClass('info_good') ; }
		else { $('#td_option_visible').removeClass('info_good').addClass('info_error') ; }
	}

	function check_visible()
	{
		var deptid = parseInt( $( "input#deptid" ).val() ) ;
		var visible = $('input:radio[name=visible]:checked').val() ;
		var total_depts = <?php echo count( $departments ) ; ?> ;
 
		if ( !parseInt( visible ) )
		{
			if ( ( deptid && ( total_depts == 1 ) ) || ( !deptid && !total_depts ) )
				return false ;
		} return true ;
	}

	function do_delete( thedeptid, thename )
	{
		var pos = $('#div_tr_'+thedeptid).position() ;
		var width = $('#div_tr_'+thedeptid).outerWidth() - 18 ;
		var height = $('#div_tr_'+thedeptid).outerHeight() + 75 ;

		global_deptid = thedeptid ;

		if ( $('#div_notice_delete').is(':visible') )
			$('#div_notice_delete').fadeOut( "fast", function() { show_div_delete(thename, pos, width, height) ; }) ;
		else
			show_div_delete(thename, pos, width, height) ;
	}

	function do_delete_doit()
	{
		if ( confirm( "Are you sure?  All department data will be permanently deleted." ) )
			location.href = "depts.php?action=delete&deptid="+global_deptid ;
	}

	function show_div_delete( thename, thepos, thewidth, theheight )
	{
		$('#span_name').html( thename ) ; 
		$('#div_notice_delete').css({'top': thepos.top, 'left': thepos.left, 'width': thewidth, 'height': theheight}).fadeIn("fast") ;

		if ( typeof( dept_groups[global_deptid] ) != "undefined" )
		{
			$('#div_button_confirm_delete').hide() ;
			$('#div_button_error_dept_group_assigned').show() ;
		}
		else
		{
			$('#div_button_error_dept_group_assigned').hide() ;
			$('#div_button_confirm_delete').show() ;
		}
	}

	function update_lang( thelang )
	{
		location.href = 'depts.php?action=update_lang&deptid=0&prev_lang=<?php echo isset( $CONF["lang"] ) ? $CONF["lang"] : "" ; ?>&lang='+thelang ;
	}

	function show_form( thedeptid )
	{
		if ( typeof( global_option ) != "undefined" )
		{
			if ( $('#iframe_edit_'+global_deptid).is(':visible') && ( document.getElementById('iframe_edit_'+global_deptid).contentWindow.option == global_option ) )
			do_options( global_option, global_deptid, "" ) ;
		}

		$(window).scrollTop(0) ;
		if ( !thedeptid )
		{
			$('#span_link_html_code').html( '<a href="code.php">department specific HTML Code</a>' ) ;
		}
		else
		{
			$('#span_link_html_code').html( '<a href="code.php?deptid='+thedeptid+'">department specific HTML Code</a>' ) ;
		}

		$('#div_smtps').show() ;

		$('#div_error_dept_group_assigned').hide() ;
		$('#div_btn_add').hide() ;
		$('#div_list').hide() ;
		$('#div_form').show() ;
		$('#div_theme_preview_wrapper').hide() ;
	}

	function do_reset()
	{
		global_deptid = 0 ;
		$('#deptid').val(0) ;
		$('#lang').val('<?php echo $CONF["lang"] ?>') ;
		$('#theform').each(function(){
			this.reset();
		});

		$(window).scrollTop(0) ;
		$('#div_form').hide() ;
		$('#div_btn_add').show() ;
		$('#div_list').show() ;
		$('#div_dept_online').hide() ;
		$('#btn_submit').attr('disabled', false) ;

		if ( <?php echo count( $departments ) ?> )
			$('#div_theme_preview_wrapper').show() ;
	}

	function iframe_scroll( thedeptid, thescrollto )
	{
		document.getElementById('iframe_edit_'+thedeptid).contentWindow.scrollTo( 0, thescrollto ) ;
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
			if ( thevalue != 1 )
			{
				$('#upload_1').prop('checked', false) ;
				if ( total_checked == 1 ) { $('#screenshot_0').prop('checked', true) ; }
			}
			else if ( thevalue == 1 ) { check_all(0) ; $('#screenshot_0').prop('checked', true) ; }
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

	function send_test_email( thedeptid, theemail )
	{
		var unique = unixtime() ;

		if ( confirm( "Send a test email to "+theemail+"?" ) )
		{
			document.getElementById('iframe_edit_'+thedeptid).contentWindow.disable_button() ;

			$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_.php",
			data: "action=send_test_email&deptid="+thedeptid+"&unique="+unique,
			success: function(data){
				document.getElementById('iframe_edit_'+thedeptid).contentWindow.send_complete( data ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Could not connect to server.  Please refresh the page and try again." ) ;
			} });
		}
	}

	function check_dept_group_assigned()
	{
		if ( typeof( dept_groups[global_deptid] ) != "undefined" )
		{
			setTimeout( function(){ $('#visible_1').prop('checked', true) ; toggle_td_visible(1) ; }, 200 ) ;
			$('#div_error_dept_group_assigned').show().fadeTo('fast', .1).fadeTo('fast', 1).fadeTo('fast', .1).fadeTo('fast', 1).fadeTo('fast', .1).fadeTo('fast', 1) ;
		}
	}

	function mimic_edit()
	{
		$('#div_notice_delete').hide() ;
		$('#dept_edit_'+global_deptid).trigger( "click" ) ;
	}

	function check_screenshot()
	{
		var process = 0 ;
		$.each($("input[name='vupload[]']:checked"), function(){
			var this_val = $(this).val() ;
			if ( this_val && ( parseInt( this_val ) || ( this_val != "0" ) ) ) { process = 1 }
		});
		
		if ( !process )
		{
			$('#screenshot_0').prop('checked', true) ;
			do_alert( 0, "At least one Visitor File Upload format must be enabled." ) ;
			$('#div_setting_file_upload').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
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
			<div class="op_submenu_focus" style="margin-left: 0px;">Chat Departments</div>
			<div class="op_submenu" onClick="location.href='dept_display.php'">Department Select Display Order</div>
			<div class="op_submenu" onClick="location.href='dept_groups.php'">Department Groups</div>
			<div class="op_submenu" onClick="location.href='dept_canned_cats.php'">Canned Response Categories</div>
			<div style="clear: both"></div>
		</div>

		<div id="div_btn_add" style="margin-top: 25px;">
			<table cellspacing=0 cellpadding=0 border=0>
			<tr>
				<td><div class="edit_focus" onClick="toggle_td_visible(1);show_form(0);"><img src="../pics/icons/add.png" width="16" height="16" border="0" alt=""> Add Chat Department</div></td>
				<td style="padding-left: 55px;">
					<?php if ( count( $departments ) > 1 ): ?>
						<div style="text-shadow: none;">
							<div id="div_tab_lang_primary" class="edit_title">Primary Language</div>
							<div>Primary language for <a href="code.php">All Departments HTML Code</a>:</div>
							<div style="margin-top: 5px;">
								<div id="primary_lang_select" class="info_neutral">
									<select name="lang_pr" id="lang_pr">
									<?php
										$dir_langs = opendir( "$CONF[DOCUMENT_ROOT]/lang_packs/" ) ;

										$langs = Array() ;
										while ( $this_lang = readdir( $dir_langs ) )
											$langs[] = $this_lang ;
										closedir( $dir_langs ) ;

										sort( $langs, SORT_STRING ) ;
										for ( $c = 0; $c < count( $langs ); ++$c )
										{
											$this_lang = preg_replace( "/.php/", "", $langs[$c] ) ;

											$selected = $selected_string = "" ;
											if ( $CONF["lang"] == $this_lang )
											{
												$selected = "selected" ;
												$selected_string = " (primary)" ;
											}

											if ( preg_match( "/[a-z]/i", $this_lang ) && !preg_match( "/index/i", $this_lang ) )
												print "<option value=\"$this_lang\" $selected>".ucfirst( $this_lang )."$selected_string</option>" ;
										}
									?>
									</select> &nbsp; &nbsp;
									<button type="button" onClick="update_lang($('#lang_pr').val())" class="btn">Update</button>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</td>
			</tr>
			</table>
		</div>
		<div style="display: none; margin-top: 15px; text-align: right;"><span class="info_neutral" style="cursor: pointer;"><img src="../themes/initiate/group.png" width="16" height="16" border="0" alt=""> configure fallback department</span></div>
		<div id="div_list" style="margin-top: 25px;">
			<table cellspacing=0 cellpadding=0 border=0 width="100%" id="table_departments" style="box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2);">
			<?php
				for ( $c = 0; $c < count( $departments ); ++$c )
				{
					$department = $departments[$c] ;

					$name = $department["name"] ;
					$rtype = $vars_rtype[$department["rtype"]] ;
					$rtime = "$department[rtime] sec" ;
					$visible = ( $department["visible"] ) ? "<img src=\"../pics/icons/check.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"visible for selection\" title=\"visible for selection\">" : "<img src=\"../pics/icons/privacy_on.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"not visible for selection\" title=\"not visible for selection\">" ;
					$screenshot = ( isset( $screenshots[$department["deptID"]] ) && $screenshots[$department["deptID"]] ) ? 1 : 0 ;

					$queue_string = ( $department["queue"] && ( $department["rtype"] != 3 ) ) ? "<span class=\"info_good\" style=\"padding: 2px; text-shadow: none;\">On</span>" : "<span class=\"info_error\" style=\"padding: 2px; text-shadow: none;\">Off</span>" ;
					$vupload_icon = ( $VARS_INI_UPLOAD && $department["vupload"] ) ? " <img src=\"../pics/icons/attach.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"visitors can upload files during chat\" title=\"visitors can upload files during chat\" style=\"cursor: help;\"> " : "" ;
					$screenshot_icon = ( $screenshot ) ? " <img src=\"../themes/initiate/screenshot.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"visitors can send a webpage screenshot\" title=\"visitors can send a webpage screenshot\" style=\"cursor: help;\"> " : "" ;

					$lang = ucfirst( $department["lang"] ) ;
					$theme = ( isset( $dept_themes[$department["deptID"]] ) ) ? $dept_themes[$department["deptID"]] : $CONF["THEME"] ;
					if ( isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }
					$span_class = ( isset( $auto_offline[$department["deptID"]] ) ) ? "info_good" : "info_error" ;
					$auto_off_string = ( isset( $auto_offline[$department["deptID"]] ) ) ? "<span class=\"info_good\" style=\"padding: 2px; text-shadow: none;\">On</span>" : "<span class=\"info_error\" style=\"padding: 2px; text-shadow: none;\">Off</span>" ;
					$auto_reply_onoff = 0 ;
					if ( $addon_auto_respond && isset( $dept_vars[$department["deptID"]] ) )
					{
						$this_dept_vars = $dept_vars[$department["deptID"]] ;
						if ( preg_match( "/-_-/", $this_dept_vars["offline_auto_reply"] ) )
						{
							$auto_reply_array = explode( "-_-", $this_dept_vars["offline_auto_reply"] ) ;
							if ( isset( $auto_reply_array[0] ) && isset( $auto_reply_array[1] ) && isset( $auto_reply_array[2] ) && isset( $auto_reply_array[3] ) )
							{
								$auto_reply_onoff = $auto_reply_array[0] ;
								$auto_reply_from = $auto_reply_array[1] ;
								$auto_reply_subject = $auto_reply_array[2] ;
								$auto_reply_body = $auto_reply_array[3] ;
							}
						}
					}

					$deptid_string = ( 1 ) ? "<div class=\"txt_grey\" style=\"margin-top: 15px; text-align: left; font-size: 12px;\">ID: $department[deptID]</div>" : "" ;

					$smtp_md5 = ( $department["smtp"] ) ? md5( $department["smtp"] ) : 0 ;

					$bg_color = ( ($c+1) % 2 ) ? "FFFFFF" : "EDEDED" ;

					$auto_reply_off_string = ( $auto_reply_onoff ) ? "<span class=\"info_good\" style=\"padding: 2px; text-shadow: none;\">On</span>" : "<span class=\"info_error\" style=\"padding: 2px; text-shadow: none;\">Off</span>" ;
					$addon_auto_respond_tab = ( $addon_auto_respond ) ? "<div class=\"menu_dept\" id=\"menu_2_$department[deptID]\" onClick=\"do_options( 2, $department[deptID], '$bg_color' );\">Offline Automatic Reply <span id=\"span_auto_$department[deptID]\" style=\"text-shadow: none;\">$auto_reply_off_string</span></div>" : "" ;

					$edit_delete = "<div id=\"dept_edit_$department[deptID]\"><a href=\"JavaScript:void(0)\" onClick=\"do_edit( $department[deptID], '$name', '$department[email]', '$department[rtype]', '$department[rtime]', '$department[vupload]', $screenshot, '$department[ctimer]', '$department[texpire]', '$department[visible]', '$department[queue]', '$department[tshare]', '$department[lang]', '$smtp_md5' )\"><img src=\"../pics/btn_edit.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div><div style=\"margin-top: 10px;\"><a href=\"JavaScript:void(0)\" onClick=\"do_delete($department[deptID], '$name')\"><img src=\"../pics/btn_delete.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;
					$options = "
						<div class=\"menu_dept\" id=\"menu_3_$department[deptID]\" onClick=\"do_options( 3, $department[deptID], '$bg_color' );\">Canned Responses</div>
						<div class=\"menu_dept\" id=\"menu_5_$department[deptID]\" onClick=\"do_options( 5, $department[deptID], '$bg_color' );\">Queue <span id=\"span_queue_$department[deptID]\" style=\"text-shadow: none;\">$queue_string</span></div>
						<div class=\"menu_dept\" id=\"menu_1_$department[deptID]\" onClick=\"do_options( 1, $department[deptID], '$bg_color' );\">Timestamp</div>
						<div class=\"menu_dept\" id=\"menu_4_$department[deptID]\" onClick=\"do_options( 4, $department[deptID], '$bg_color' );\">Visitor Email Transcript</div>
						$addon_auto_respond_tab
						<div class=\"menu_dept\" id=\"menu_8_$department[deptID]\" onClick=\"do_options( 8, $department[deptID], '$bg_color' );\">Offline Hours <span id=\"span_class_$department[deptID]\" style=\"text-shadow: none;\">$auto_off_string</span></div>
						<div class=\"menu_dept\" id=\"menu_7_$department[deptID]\" style=\"margin: 0px;\" onClick=\"do_options( 7, $department[deptID], '$bg_color' );\">SMTP</div>
						<div style=\"clear: both;\"></div>
					" ;

					$td1 = "td_dept_td_blank" ;
					$td2 = "td_dept_td" ;

					print "
					<tr id=\"div_tr_$department[deptID]\" style=\"background: #$bg_color;\">
						<td class=\"$td1\" nowrap>$edit_delete</td>
						<td class=\"$td1\">
							<div><b>$name</b></div>
							<div style=\"margin-top: 5px;\">$vupload_icon $screenshot_icon</div>
						</td>
						<td class=\"$td1 div_class_email\"><div id=\"div_td_email_$department[deptID]\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Department Email</div>$department[email]</div></td>
						<td class=\"$td1 div_class_route\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Routing Type</div>$rtype</td>
						<td class=\"$td1 div_class_rtype\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Routing Time</div>$rtime</td>
						<td class=\"$td1 div_class_visible\" align=\"center\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Visible</div>$visible</td>
						<td class=\"$td1 div_class_lang\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Language</div>$lang</td>
						<td class=\"$td1\"><div class=\"txt_grey\" style=\"margin-bottom: 5px;\">Theme</div><a href=\"interface_themes.php\">$theme</a></td>
					</tr>
					<tr style=\"background: #$bg_color;\">
						<td class=\"$td2\" valign=\"top\" align=\"right\" style=\"padding-top: 0px;\">
							$deptid_string
						</td>
						<td class=\"$td2\" colspan=\"8\" valign=\"top\" style=\"padding-top: 0px;\">
							<div style=\"padding-top: 15px; padding-bottom: 0px; border-bottom: 0px;\" class=\"info_neutral\">
								Department options for <span class=\"info_white\">$name</span>
								<div style=\"margin-top: 15px;\">$options</div>
							</div>
							<div id=\"iframe_$department[deptID]\" style=\"display: none; width: 100%\"><iframe id=\"iframe_edit_$department[deptID]\" name=\"iframe_edit_$department[deptID]\" style=\"width: 100%; height: 460px; border: 0px; margin-top: 15px;\" src=\"\" scrolling=\"auto\" frameBorder=\"0\"></iframe></div>
						</td>
					</tr>
					" ;
				}
				if ( $c == 0 )
					print "<tr><td colspan=9 class=\"td_dept_td\">Blank results.</td></tr>" ;
			?>
			</table>
		</div>

		<div id="div_form" style="display: none;" id="a_edit">
			<form method="POST" action="depts.php" id="theform">
			<input type="hidden" name="action" value="submit">
			<input type="hidden" name="deptid" id="deptid" value="0">
			<input type="hidden" name="tshare" value="">
			<input type="hidden" id="texpire" name="texpire" value="0">
			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td colspan=2 style="padding-bottom: 25px;" align="left"><span class="info_misc"><img src="../pics/icons/arrow_left.png" width="16" height="15" border="0" alt=""> <a href="JavaScript:void(0)" onClick="do_reset()">back</a></span></td>
				</tr>
				<tr>
					<td nowrap class="tab_form_title">Department Name</td>
					<td style="padding-left: 10px;"><input type="text" class="input" name="name" id="name" size="30" maxlength="40" value="" onKeyPress="return noquotes(event)"> &nbsp; * example: Customer Support, Sales, Billing Department</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Department Email</td>
					<td style="padding-left: 10px;"><input type="text" class="input" name="email" id="email" size="30" maxlength="160" value="" onKeyPress="return justemails(event)"> &nbsp; * if the visitor leaves a message on the offline form, the message is sent to this email address</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title" id="td_option_visible" style="text-shadow: none;">Visible for Selection</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>
								When the visitor requests chat, choose whether to display this department on the department selection dropdown menu. If "Not visible", the only way to reach this department is if an operator transfers the chat session to this department or by using the <span id="span_link_html_code"></span>.
								<div class="info_error" style="display: none; margin-top: 5px;" id="div_error_dept_group_assigned">Department is assigned to a <a href="dept_groups.php" style="color: #FFFFFF;">Department Group</a>.  Department must be visible.</div>
							</td>
							<td style="padding-left: 15px;" nowrap>
								<span class="info_good" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#visible_1').prop('checked', true);$('#div_error_dept_group_assigned').hide();toggle_td_visible(1);"><input type="radio" name="visible" id="visible_1" value="1" checked> Visible</span>
								<span class="info_error" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#visible_0').prop('checked', true);check_dept_group_assigned();toggle_td_visible(0);"><input type="radio" name="visible" id="visible_0" value="0"> Not visible</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Chat Routing Type</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=5 border=0>
						<tr>
							<td nowrap><div class="info_neutral" onClick="$('#rtype_1').prop('checked', true);" style="cursor: pointer;"><input type="radio" name="rtype" id="rtype_1" value="1"> <span style="font-weight: bold; color: #5D5D5D;">Defined Order:</span></div></td>
							<td>The chat request is routed to each operator based on the defined order set at <a href="ops.php?jump=assign">Assign Operator to Department</a> area.</td>
						</tr>
						<tr>
							<td nowrap><div class="info_neutral" onClick="$('#rtype_2').prop('checked', true);" style="cursor: pointer;"><input type="radio" name="rtype" id="rtype_2" value="2" checked> <span style="font-weight: bold; color: #5D5D5D;">Round-Robin:</span></div></td>
							<td>The operator that has not accepted a chat in the longest time will be the first to receive the chat request.</td>
						</tr>
						<tr>
							<td><div class="info_neutral" onClick="$('#rtype_3').prop('checked', true);" style="cursor: pointer;"><input type="radio" name="rtype" id="rtype_3" value="3"> <span style="font-weight: bold; color: #5D5D5D;">Simultaneous:</span></div></td>
							<td>All operators will receive the chat request at the same time.</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Chat Routing Time</td>
					<td style="padding-left: 10px;">
						<div>If an operator does not accept the chat request within <select name="rtime" id="rtime" ><option value="15">15 seconds</option><option value="30">30 seconds</option><option value="45" selected>45 seconds</option><option value="60">1 minute</option><option value="90">1 min 30 sec</option><option value="120">2 minutes</option><option value="150">2 min 30 sec</option><option value="180">3 minutes</option><option value="240">4 minutes</option><option value="300">5 minutes</option></select>, route the chat request to the next available online operator.</div>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Share Transcripts</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>
								Should the chat transcripts be shared between all operators that are assigned to this department?  If set to "No, keep private", the chat transcripts will only be visible to the chat operator that accepted the chat.
							</td>
							<td style="padding-left: 15px;" nowrap>
								<span class="info_good" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#tshare_1').prop('checked', true);"><input type="radio" name="tshare" id="tshare_1" value="1"> Yes, share</span>
								<span class="info_error" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#tshare_0').prop('checked', true);"><input type="radio" name="tshare" id="tshare_0" value="0" checked> No, keep private</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Chat Timer Display</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>During a chat session (for both the visitor and the operator), display a clock like incrementing timer that shows the duration of the chat session.  The timer will be in the format of <b>mm:ss</b> on the chat window.</td>
							<td style="padding-left: 15px;" nowrap>
								<span class="info_good" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF; cursor: pointer;" onclick="$('#ctimer_1').prop('checked', true);"><input type="radio" name="ctimer" id="ctimer_1" value="1" checked> Display</span>
								<span class="info_error" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onclick="$('#ctimer_0').prop('checked', true);"><input type="radio" name="ctimer" id="ctimer_0" value="0"> Don't display</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;" id="div_setting_file_upload">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title"><img src="../pics/icons/attach.png" width="16" height="16" border="0" alt=""> Visitor File Upload</td>
					<td style="padding-left: 10px;">
						<?php if ( $VARS_INI_UPLOAD ): ?>
						Allow visitors to upload files during a chat session?
						<div style="margin-top: 10px;" id="div_file_extensions">
							<span class="info_neutral" style="cursor: pointer;" onclick="toggle_upload(0);$('#screenshot_0').prop('checked', true);"><input type="checkbox" name="vupload[]" value="0" id="upload_0" onclick="toggle_upload(0)"> No</span>
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

						<div style="margin-top: 20px;"><span class="info_warning"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> The file upload setting for the chat operators can be set for each operator at the <a href="ops.php">Operators</a> area.</span></div>
						<?php else: ?>
						<img src="../pics/icons/alert.png" width="16" height="16" border="0" alt=""> File upload is not enabled for this server ('<a href="http://php.net/manual/en/ini.core.php#ini.file-uploads" target="_blank">file_uploads</a>' directive).  Please contact the server admin for more information.
						<?php endif ; ?>
					</td>
				</tr>
				</table>
			</div>
			<?php if ( $VARS_INI_UPLOAD ): ?>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title"><img src="../themes/initiate/screenshot.png" width="16" height="16" border="0" alt=""> Webpage Screenshot</td>
					<td style="padding-left: 10px;">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>
								Allow visitors to send a screenshot of the webpage during a chat session and for the "Leave a message" offline form?
								<div style="margin-top: 5px;"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> Screenshot is only available for visitors using modern browsers on desktop computers.</div>
							</td>
							<td style="padding-left: 15px;" nowrap>
								<span class="info_good" style="background: #7BD869; border: 1px solid #65CB55; color: #FFFFFF;" onClick="check_screenshot()"><input type="radio" name="screenshot" id="screenshot_1" value="1"><label for="screenshot_1" style="cursor: pointer;"> Yes</label></span>
								<span class="info_error" style="background: #FD7D7F; border: 1px solid #E16F71; color: #FFFFFF; cursor: pointer;" onClick="$('#screenshot_0').prop('checked', true);"><input type="radio" name="screenshot" id="screenshot_0" value="0" checked> No</span>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<?php endif ; ?>
			<?php if ( count( $departments_smtp ) > 0 ): ?>
			<div id="div_smtps" style="display: none; margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title"><a href="../addons/smtp/smtp.php">SMTP Settings</a></td>
					<td style="padding-left: 10px;">
						Use SMTP setting: 
						<select name="smtp" id="smtp">
							<option value="0"></option>
							<?php
								foreach ( $departments_smtp as $deptid => $smtp )
								{
									$smtp_array = unserialize( Util_Functions_itr_Decrypt( $CONF["SALT"], $smtp ) ) ;
									if ( $smtp_array )
									{
										$smtp_md5 = md5( $smtp ) ;
										if ( isset( $smtp_array["api"] ) && $smtp_array["api"] )
											print "<option value=\"$smtp_md5\">API: $smtp_array[api] ($smtp_array[login]$smtp_array[domain])</option>" ;
										else
											print "<option value=\"$smtp_md5\">$smtp_array[host] (login: $smtp_array[login])</option>" ;
									}
								}
							?>
						</select>
					</td>
				</tr>
				</table>
			</div>
			<?php endif ; ?>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Language</td>
					<td style="padding-left: 10px;">
						<select name="lang" id="lang">
						<?php
							$dir_langs = opendir( "$CONF[DOCUMENT_ROOT]/lang_packs/" ) ;

							$langs = Array() ;
							while ( $this_lang = readdir( $dir_langs ) )
								$langs[] = $this_lang ;
							closedir( $dir_langs ) ;

							sort( $langs, SORT_STRING ) ;
							for ( $c = 0; $c < count( $langs ); ++$c )
							{
								$this_lang = preg_replace( "/.php/", "", $langs[$c] ) ;

								$selected = "" ;
								if ( $CONF["lang"] == $this_lang )
									$selected = "selected" ;

								if ( preg_match( "/[a-z]/i", $this_lang ) && !preg_match( "/index/i", $this_lang ) )
									print "<option value=\"$this_lang\" $selected> ".ucfirst( $this_lang )."</option>" ;
							}
						?>
						</select> Visitor chat window language ("Start Chat", "Name", "Email", "Question", "Email Transcript", "Select Department", etc) 
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 15px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td class="tab_form_title">Theme</td>
					<td style="padding-left: 10px;">
						<select name="theme" id="theme">
						<?php
							$dir_themes = opendir( "$CONF[DOCUMENT_ROOT]/themes/" ) ;

							$themes = Array() ;
							while ( $this_theme = readdir( $dir_themes ) )
								$themes[] = $this_theme ;
							closedir( $dir_themes ) ;

							sort( $themes, SORT_STRING ) ;
							for ( $c = 0; $c < count( $themes ); ++$c )
							{
								$this_theme = $themes[$c] ;

								$selected = "" ;
								if ( $CONF["THEME"] == $this_theme )
									$selected = "selected" ;

								if ( preg_match( "/[a-z]/i", $this_theme ) && ( $this_theme != "initiate" ) && !isset( $THEMES_EXCLUDE[$this_theme] ) )
									print "<option value=\"$this_theme\" $selected>$this_theme</option>" ;
							}
						?>
						</select>
					</td>
				</tr>
				</table>
			</div>
			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td><div class="tab_form_title" style="background: #F4F6F8; border: 0px; padding-left: 0px; text-align: left; font-weight: normal; text-shadow: none;"><span class="info_misc"><img src="../pics/icons/arrow_left.png" width="16" height="15" border="0" alt=""> <a href="JavaScript:void(0)" onClick="do_reset()">back</a></span></div></td>
					<td style="padding-left: 10px;">
						<div id="div_dept_online" style="display: none;" class="info_warning">
							<table cellspacing=0 cellpadding=0 border=0>
							<tr>
								<td><img src="../pics/icons/warning.gif" width="16" height="16" border="0" alt=""></td>
								<td style="padding-left: 5px;">If an <a href="ops.php">operator</a> is Online for this department, they must logout and login again for some of the changes to take effect on their operator console.</td>
							</tr>
							</table>
						</div>
						<div style="margin-top: 25px;"><button type="button" onClick="do_submit()" class="btn" id="btn_submit">Submit</button> &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="do_reset()">cancel</a></div>
					</td>
				</tr>
				</table>
			</div>

			</form>
		</div>

		<div id="div_notice_delete" style="display: none; position: absolute; text-align: justify;" class="info_error">
			<div style="padding: 10px;">
				<div class="edit_title">Really delete this department (<span id="span_name"></span>)?</div>
				<div style="margin-top: 5px;">To retain the department chat transcripts and the chat reports, it is recommended to <a href="JavaScript:void(0)" onClick="mimic_edit()" style="color: #FFFFFF;">edit the department</a> and set the "Visible for Selection" to "No" rather then permanently deleting the department and the data.</div>

				<div style="display: none; margin-top: 15px;" id="div_button_confirm_delete"><button type="button" onClick="do_delete_doit()" class="btn">Delete</button> &nbsp; &nbsp; &nbsp; <a href="JavaScript:void(0)" style="color: #FFFFFF" onClick="$('#div_notice_delete').fadeOut('fast')">cancel</a></div>
				<div style="display: none; margin-top: 15px;" id="div_button_error_dept_group_assigned">
					<div class="info_box"><img src="../pics/icons/alert.png" width="16" height="16" border="0" alt=""> Department is assigned to a <a href="dept_groups.php">Department Group</a>.  Department cannot be deleted.</div>
					<div style="margin-top: 15px;"><a href="JavaScript:void(0)" style="color: #FFFFFF" onClick="$('#div_notice_delete').fadeOut('fast')">cancel</a></div>
				</div>
			</div>
		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
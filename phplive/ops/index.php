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
	if ( !is_file( "../web/config.php" ) ){ HEADER("location: ../setup/install.php") ; exit ; }
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$opinfo = Util_Security_AuthOp( $dbh ) ){ ErrorHandler( 602, "Invalid operator session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;

	/***** [ BEGIN ] BASIC CLEANUP *****/
	$dir_files = glob( $CONF["CHAT_IO_DIR"].'/*.txt*', GLOB_NOSORT ) ;
	$total_dir_files = count( $dir_files ) ;
	if ( $total_dir_files )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/Util.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/put_itr.php" ) ;

		for ( $c = 0; $c < $total_dir_files; ++$c )
		{
			$file = $dir_files[$c] ;
			$modtime = filemtime( $file ) ;
			if ( $modtime && ( $modtime < ( $now - (60*60*24*5) ) ) )
			{
				if ( is_file( $file ) )
				{
					$ces = str_replace( "$CONF[CHAT_IO_DIR]", "", $file ) ;
					$ces = preg_replace( "/[\\/]|(.txt)/", "", $ces ) ;
					$requestinfo = Chat_get_RequestHistCesInfo( $dbh, $ces ) ;
					if ( isset( $requestinfo["ces"] ) && !$requestinfo["ended"] )
					{
						$deptinfo = Depts_get_DeptInfo( $dbh, $requestinfo["deptID"] ) ;
						$deptvars = Depts_get_DeptVars( $dbh, $requestinfo["deptID"] ) ;

						LIST( $ces ) = database_mysql_quote( $dbh, $requestinfo["ces"] ) ;

						$CONF["lang"] = ( isset( $CONF["lang"] ) && $CONF["lang"] ) ? $CONF["lang"] : "english" ;
						include( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($CONF["lang"], "ln").".php" ) ;
						$string_disconnect = "<div class='cl'><disconnected><d7>".$LANG["CHAT_NOTIFY_DISCONNECT"]."</div>" ;
						UtilChat_AppendToChatfile( $ces.".txt", base64_encode( $string_disconnect ) ) ;

						$output = UtilChat_ExportChat( $ces.".txt" ) ;
						if ( is_array( $output ) && isset( $output[1][0] ) )
						{
							$formatted = $output[1][0] ; $plain = $output[1][1] ;
							$fsize = strlen( $formatted ) ;
							if ( $requestinfo["status"] )
							{
								$custom_string = "" ;
								$customs = explode( "-cus-", rawurldecode( $requestinfo["custom"] ) ) ;
								for ( $c = 0; $c < count( $customs ); ++$c )
								{
									$custom_var = $customs[$c] ;
									if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
									{
										LIST( $cus_name, $cus_var ) = explode( "-_-", $custom_var ) ;
										if ( $cus_var ) { $custom_string .= $cus_name.": ".$cus_var."\r\n" ; }
									}
								}
								$query = "DELETE FROM p_requests WHERE ces = '$ces'" ;
								database_mysql_query( $dbh, $query ) ;
								Chat_put_itr_Transcript( $dbh, $ces, $requestinfo["status"], $requestinfo["created"], $modtime, $requestinfo["deptID"], $requestinfo["opID"], $requestinfo["initiated"], $requestinfo["op2op"], 0, $fsize, $requestinfo["vname"], $requestinfo["vemail"], $requestinfo["ip"], $requestinfo["md5_vis"], $custom_string, $requestinfo["question"], $formatted, $plain, $deptinfo, $deptvars ) ;
							}
						}
					}
					else
					{
						$transcript = Chat_ext_get_Transcript( $dbh, $ces ) ;
						if ( isset( $transcript["ces"] ) && is_file( $file ) )
						{
							@unlink( $file ) ;
						}
					}
				} } } }
	/***** [ END ] BASIC CLEANUP *****/

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ;
	$console = Util_Format_Sanatize( Util_Format_GetVar( "console" ), "n" ) ;
	$menu = Util_Format_Sanatize( Util_Format_GetVar( "menu" ), "ln" ) ;
	$wp = Util_Format_Sanatize( Util_Format_GetVar( "wp" ), "n" ) ;
	$auto = Util_Format_Sanatize( Util_Format_GetVar( "auto" ), "n" ) ;
	$menu = ( $menu ) ? $menu : "go" ;
	$error = "" ;
	$theme = "default" ; // for the rating stars
	if ( !is_file( "$CONF[DOCUMENT_ROOT]/themes/$opinfo[theme]/style.css" ) || isset( $THEMES_EXCLUDE[$opinfo["theme"]] ) ) { $opinfo["theme"] = $theme ; }

	// password reset flag to update the password
	$pr = Util_Format_Sanatize( Util_Format_GetVar( "pr" ), "n" ) ;
	if ( $pr ) { database_mysql_close( $dbh ) ; HEADER( "location: settings.php?auto=$auto&console=$console&jump=password&pr=$pr" ) ; exit ; }

	$op_depts = Ops_get_OpDepts( $dbh, $opinfo["opID"] ) ;
	$opvars = Ops_get_OpVars( $dbh, $opinfo["opID"] ) ;

	if ( $action === "update_theme" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;
		$theme = Util_Format_Sanatize( Util_Format_GetVar( "theme" ), "ln" ) ;

		if ( !Ops_update_OpValue( $dbh, $opinfo["opID"], "theme", $theme ) )
			$error = "Error in updating theme." ;
		else
			$opinfo["theme"] = $theme ;
		
		$menu = "themes" ;
	}
	else if ( $action === "success" )
	{
		// sucess action is an indicator to show the success alert as well
		// as bypass the refreshing of the operator console
	}
	else
		$error = "invalid action" ;

	$query = "SELECT SUM(rateit) AS rateit, SUM(ratings) AS ratings FROM p_rstats_ops WHERE opID = '$opinfo[opID]'" ;
	database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
	$overall = ( isset( $data["rateit"] ) && $data["rateit"] ) ? round( $data["ratings"]/$data["rateit"] ) : 0 ;

	$query = "SELECT SUM(taken) AS total FROM p_rstats_ops WHERE opID = '$opinfo[opID]'" ;
	database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
	$chats_accepted = ( isset( $data["total"] ) ) ? $data["total"] : 0 ;

	$dept_string = " ( opID = $opinfo[opID] OR op2op = $opinfo[opID] " ;
	for ( $c = 0; $c < count( $op_depts ); ++$c )
	{
		if ( $op_depts[$c]["tshare"] )
			$dept_string .= " OR deptID = " . $op_depts[$c]["deptID"] ;
	}
	$dept_string .= " ) " ;

	$query = "SELECT count(*) AS total FROM p_transcripts WHERE $dept_string" ;
	database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
	$chat_transcripts = ( isset( $data["total"] ) ) ? $data["total"] : 0 ;

	$auto_login_enabled = ( isset( $_COOKIE["cAT"] ) && $_COOKIE["cAT"] ) ? 1 : 0 ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> Operator Home </title>

<meta name="description" content="v.<?php echo $VERSION ?>">
<meta name="keywords" content="<?php echo md5( $KEY ) ?>">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo filemtime ( "../css/setup.css" ) ; ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/jquery_md5.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/dn.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/modernizr.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var opwin ;
	var menu ;
	var theme = "<?php echo $opinfo["theme"] ?>" ;
	var base_url = ".." ; // needed for function play_sound()
	var embed = 0 ;
	var wp = ( ( typeof( window.external ) != "undefined" ) && ('wp_total_visitors' in window.external) ) ? 1 : 0 ;
	var console_type = 1 ;

	var audio_supported = HTML5_audio_support() ;
	var mp3_support = ( typeof( audio_supported["mp3"] ) != "undefined" ) ? 1 : 0 ;
	var phplive_session_support = ( typeof( Storage ) != "undefined" ) ? 1 : 0 ;
	if ( phplive_session_support )
	{
		try {
			var temp = localStorage.getItem("console_type") ;
			if ( temp == 0 ) { console_type = 0 ; }
		} catch (error) {}
	}

	/*
	$.preload_images = function() {
		for (var i = 0; i < arguments.length; i++)
		{
			$("<img />").attr( "src", arguments[i] ) ;
		}
	} ; $.preload_images( "../pics/bg_btn.gif", "../pics/bg_btn_focus.gif" ) ;
	*/

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;

		$('#op_launch_btn_popup').on('mouseover mouseout', function(event) {
			$('#op_launch_btn_popup').toggleClass('op_launch_btn_focus') ;
		});
		$('#op_launch_btn_tab').on('mouseover mouseout', function(event) {
			$('#op_launch_btn_tab').toggleClass('op_launch_btn_focus') ;
		});

		init_menu_op() ;
		init_div_confirm() ;
		toggle_menu_op( "<?php echo $menu ?>" ) ;

		if ( !<?php echo count( $op_depts ) ?> ) { $('#no_dept').show() ; }

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ; setTimeout( function(){ $('#div_alert_wrapper').fadeOut("slow") ; }, 3000 ) ;<?php endif ; ?>

		if ( "<?php echo $jump ?>" == "online" )
		{
			$('#op_launch_btn_popup').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		}
		else
			$('#div_thumb_'+theme).fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;

		if ( ( typeof( parent.isop ) != "undefined" ) && ( "<?php echo $action ?>" == "update_theme" ) )
		{
			parent.update_theme( "<?php echo $opinfo["theme"] ?>", <?php echo filemtime ( "../themes/$opinfo[theme]/style.css" ) ; ?> ) ;
			parent.close_extra( parent.extra ) ;
			parent.do_alert( 1, "Update Success" ) ;
		}

		if ( typeof( parent.isop ) != "undefined" )
		{
			if ( parent.$('#img_profile_pic').attr('src') != $('#img_profile_pic').attr('src') )
			{
				parent.profile_pic_url = $('#img_profile_pic').attr('src') ;
				parent.$('#img_profile_pic').attr('src', $('#img_profile_pic').attr('src') ) ;
			}
		}

		toggle_status(0) ;
		if ( typeof( parent.isop ) != "undefined" ) { parent.init_extra_loaded() ; }
		if ( wp ) { $('#chat_text_powered').hide() ; }
		if ( console_type == 0 )
		{
			$('#popup_tab_tab').prop('checked', true) ;
			toggle_tab_popup( "tab" ) ;
		}
		else
			toggle_tab_popup( "popup" ) ;
	});

	function init_div_confirm()
	{
	}

	function launchit()
	{
		var open_status = $('#open_status').val() ;
		var open_win_popup = ( $('#popup_tab_popup').is(':checked') ) ? 1 : 0 ;
		var screen_width = screen.width ;
		var screen_height = screen.height ;
		var url = "operator.php?wp="+wp+"&auto=<?php echo $auto ?>&console=<?php echo $console ?>&open_status="+open_status+"&"+unixtime() ;

		var console_width ;
		if ( screen_width > 1200 ) { console_width = 1200 }
		else if ( screen_width > 1000 ) { console_width = 1000 ; }
		else if ( screen_width > 800 ) { console_width = 940 ; }
		else { console_width = 700 ; }
		var console_height = ( screen_height > 1000 ) ? 760 : 660 ;

		if ( !<?php echo count( $op_depts ) ?> )
			$('#no_dept').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		else
		{
			if ( phplive_session_support ) { try { localStorage.setItem( "console_type", open_win_popup ) ; } catch (error) {} }
			if ( typeof( opwin ) == "undefined" )
			{
				if ( open_win_popup )
					opwin = window.open( url, "operator_console", "scrollbars=yes,menubar=no,resizable=1,location=no,width="+console_width+",height="+console_height+",status=0" ) ;
				else
					location.href = url ;
			}
			else if ( opwin.closed )
			{
				if ( open_win_popup )
					opwin = window.open( url, "operator_console", "scrollbars=yes,menubar=no,resizable=1,location=no,width="+console_width+",height="+console_height+",status=0" ) ;
				else
					location.href = url ;
			}
			else if ( opwin && !open_win_popup )
			{
				url = "" ; // unset URL to stop focus
				do_alert( 0, "You must close the existing operator console window." ) ;
				$('#popup_tab_popup').prop('checked', true) ;
			}

			if ( opwin && url )
			{
				setTimeout( function(){ opwin.focus() ; }, 300 ) ;
			}
		}
		return true ;
	}

	function confirm_theme( thetheme, thethumb )
	{
		if ( theme != thetheme )
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
		location.href = 'index.php?console=<?php echo $console ?>&wp='+wp+'&auto=<?php echo $auto ?>&action=update_theme&theme='+thetheme ;
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
			$('#theme_<?php echo $opinfo["theme"] ?>').prop('checked', true) ;

			$('#div_confirm').hide() ;
			$('#div_confirm_body').hide() ;
			$('body').css({'overflow': 'visible'}) ;
		}
	}

	function toggle_status( thestatus )
	{
		$('#open_status').val( thestatus ) ;
	}

	function toggle_tab_popup( thediv )
	{
		if ( thediv == "popup" )
		{
			$('#popup_tab_popup').prop('checked', true) ;
			$('#div_tab_popup_popup').removeClass('info_neutral_lite').addClass('info_neutral') ;
			$('#div_tab_popup_tab').removeClass('info_neutral').addClass('info_neutral_lite') ;
		}
		else
		{
			$('#popup_tab_tab').prop('checked', true) ;
			$('#div_tab_popup_tab').removeClass('info_neutral_lite').addClass('info_neutral') ;
			$('#div_tab_popup_popup').removeClass('info_neutral').addClass('info_neutral_lite') ;
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ); ?>

		<div id="op_go" style="display: none; padding: 15px;" class="info_white">
			<div id="no_dept" class="info_error" style="display: none; margin-bottom: 15px;"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Please contact the Setup Admin to assign your account to a department.  Once assigned, <a href="./index.php?<?php echo time() ?>" style="color: #FFFFFF;">refresh</a> this page to continue.</div>

			<div>
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td width="55"><a href="settings.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>"><img src="<?php print Util_Upload_GetLogo( "profile", $opinfo["opID"] ) ?>" id="img_profile_pic" width="55" height="55" border=0 style="border: 1px solid #DFDFDF; border-radius: 50%;" class="round"></a></td>
					<td style="padding-left: 15px;">
						<div>Chat Operator <span class="info_neutral"><?php echo $opinfo["login"] ?></span></div>
						<div style="margin-top: 15px;" class="edit_title">
							<big><?php echo $opinfo["name"] ?></big> <span style="font-size: 12px; font-weight: normal;">&lt;<?php echo $opinfo["email"] ?>&gt;</span>
						</div>
					</td>
				</tr>
				</table>

				<div style="margin-top: 15px;">
					<table cellspacing=0 cellpadding=0 border=0>
					<tr>
						<td nowrap>
							<div style="display: inline-block; padding: 10px;">
								&bull; &nbsp; <a href="settings.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&jump=auto&<?php echo $cache_bypass ?>">Automatic Login (Remember me)</a>: <?php echo ( $auto_login_enabled ) ? "<span class=\"info_good\" style=\"padding: 2px;\">On</span>" : "Off" ; ?>
							</div>
						</td>
						<td style="padding-left: 15px;" nowrap>
							<div style="padding: 10px;">
								&bull; &nbsp; <a href="transcripts.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>">Total Chats Accepted</a>: <?php echo $chats_accepted ?>
							</div>
						</td>
						<td style="padding-left: 15px;" nowrap>
							<div style="padding: 10px;">
								<table cellspacing=0 cellpadding=3 border=0>
								<tr>
									<td nowrap>&bull; &nbsp; <a href="transcripts.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>">Overall Rating</a>:</td>
									<td nowrap><?php echo Util_Functions_Stars( "..", $overall ) ; ?></td>
								</tr>
								</table>
							</div>
						</td>
					</tr>
					</table>
				</div>
			</div>

			<?php if ( !$console ): ?>
			<div style="margin-top: 15px; border-top: 1px solid #F2F2F2; height: 25px;"></div>

			<table cellspacing=0 cellpadding=0 border=0 width="100%" style="margin-top: 5px;">
			<tr>
				<td valign="bottom">
					<div style="text-align: center;">
						<center>
						<table cellspacing=5 cellpadding=0 border=0>
						<tr>
							<td>
								<div id="div_tab_popup_popup" class="info_neutral" style="width: 280px; cursor: pointer;" onclick="toggle_tab_popup('popup')">
									<table cellspacing=0 cellpadding=4 border=0>
									<tr>
										<td><input type="radio" name="popup_tab" id="popup_tab_popup" value="popup" checked></td>
										<td align="left"><big><b>Pop-up</b></big><div style="margin-top: 5px;">Open the chat console in a popup window.</div></td>
									</tr>
									</table>
								</div>
							</td>
							<td><img src="../pics/space.gif" width="5" height=1 border=0></td>
							<td>
								<div id="div_tab_popup_tab" class="info_neutral" style="width: 280px; cursor: pointer;" onclick="toggle_tab_popup('tab')">
									<table cellspacing=0 cellpadding=4 border=0>
									<tr>
										<td><input type="radio" name="popup_tab" id="popup_tab_tab" value="tab"></td>
										<td align="left"><big><b>Tabbed</b></big><div style="margin-top: 5px;">Open the chat console in this tab.</div></td>
									</tr>
									</table>
								</div>
							</td>
						</tr>
						</table>
						</center>
					</div>
				</td>
				<!-- <td valign="bottom">
					Launch console with status
					<div style="margin-top: 5px;">
						<div class="info_good" style="float: left; width: 60px; cursor: pointer;" onclick="$('#status_0').prop('checked', true);toggle_status(0);"><input type="radio" name="status" id="status_0" value=0 checked> Online</div>
						<div class="info_error" style="float: left; margin-left: 10px; width: 60px; cursor: pointer;" onclick="$('#status_1').prop('checked', true);toggle_status(1);"><input type="radio" name="status" id="status_1" value=1> Offline</div>
						<div style="clear: both;"></div>
					</div>
				</td> -->
			</tr>
			<tr>
				<td colspan=2 style="padding-top: 30px;">
					<div id="div_status" style="text-shadow: none;">
						<div id="op_launch_btn_popup" style="border: 1px solid #04A1E1; padding: 15px; font-size: 18px; font-weight: bold; color: #FFFFFF; text-shadow: -1px -1px #008CFB; text-align: center; cursor: pointer; box-shadow: 3px 3px 10px rgba(0, 0, 0, 0.2);" onClick="launchit()" class="op_launch_btn round"><img src="../pics/icons/pointer.png" width="16" height="16" border="0" alt=""> Click here to open the operator chat console and to go online.</div>
					</div>
				</td>
			</tr>
			</table>
			<?php endif ; ?>
		</div>

		<div id="op_themes" style="display: none;">
			<?php if ( !$console ): ?><div style="margin-bottom: 25px;">If the operator chat console is open, you will need to refresh the console to see the changes.</div><?php endif ; ?>
			<div style="margin-bottom: 25px;">Operator console themes.</div>

			<div id="div_alert_wrapper" style=""><span id="div_alert"></span></div>
			<form>
			<input type="hidden" name="open_status" id="open_status" value="0">
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
							$checked = ( $opinfo["theme"] == $theme ) ? "checked" : "" ;
							$class = ( $checked ) ? "info_misc" : "info_white" ;
							$path_thumb = ( is_file( "../themes/$theme/thumb.png" ) ) ? "../themes/$theme/thumb.png" : "../pics/screens/thumb_theme_blank.png" ;

							if ( preg_match( "/[a-z]/i", $theme ) && !preg_match( "/^\./", $theme ) && ( $theme != "initiate" ) && !isset( $THEMES_EXCLUDE[$theme] ) )
							{
								if ( !isset( $CONF_EXTEND ) || !isset( $CONF_EXTEND_THEMES ) || !isset( $CONF_EXTEND_THEMES[$theme] ) || ( isset( $CONF_EXTEND ) && isset( $CONF_EXTEND_THEMES[$theme] ) && ( $CONF_EXTEND_THEMES[$theme] == $CONF_EXTEND ) ) )
									print "<div class=\"li_op round\" style=\"padding: 5px; width: 115px; margin-bottom: 15px;\"><div id=\"div_thumb_$theme\" style=\"background: url( $path_thumb ); background-position: top left; height: 105px; border-radius: 5px;\"><span class=\"$class\" style=\"padding: 4px; border: 1px solid #A6ACB1; cursor: pointer;\" onClick=\"confirm_theme('$theme', '$path_thumb')\" id=\"span_$theme\"><input type=\"radio\" name=\"theme\" id=\"theme_$theme\" value=\"$theme\" $checked> $theme</span></div></div>" ;
							}
						}
					?>
					<div style="clear: both;"></div>
				</td>
			</tr>
			</table>
			</form>
		</div>

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

<!-- preload -->
<img src="../pics/bg_btn.gif" style="display: none;">
<img src="../pics/bg_btn_focus.gif" style="display: none;">

<?php include_once( "./inc_footer.php" ); ?>
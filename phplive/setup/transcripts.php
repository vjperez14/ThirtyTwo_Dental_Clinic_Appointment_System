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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/get.php" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
	$addon_gravatar = 0 ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/gravatar/API/Util_Gravatar.php" ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/addons/gravatar/API/Util_Gravatar.php" ) ;
		$addon_gravatar = 1 ;
	} $dept_gravatars = ( isset( $VALS_ADDONS ) && isset( $VALS_ADDONS["gravatars"] ) && $VALS_ADDONS["gravatars"] ) ? unserialize( $VALS_ADDONS["gravatars"] ) : Array() ;

	$error = "" ;
	$theme = "default" ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
	$page = Util_Format_Sanatize( Util_Format_GetVar( "page" ), "n" ) ;
	$index = Util_Format_Sanatize( Util_Format_GetVar( "index" ), "n" ) ;
	$tid = Util_Format_Sanatize( Util_Format_GetVar( "tid" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ; $deptinfo = Array() ;
	$operators = Ops_get_AllOps( $dbh ) ;
	$markets = Marketing_get_AllMarketing( $dbh ) ;

	// make hash for quick refrence
	$operators_hash = Array() ;
	for ( $c = 0; $c < count( $operators ); ++$c )
	{
		$operator = $operators[$c] ;
		$profile_pic_url = Util_Upload_GetLogo( "profile", $operator["opID"] ) ;
		$operators_hash[$operator["opID"]] = Array( $operator["login"], $operator["name"], $profile_pic_url ) ;
	}

	$dept_hash = Array() ; $dept_customs = Array() ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$dept_hash[$department["deptID"]] = $department["name"] ;
		if ( $department["custom"] )
		{
			$custom = unserialize( $department["custom"] ) ;
			$dept_customs = array_merge( $dept_customs, $custom ) ;
		}
		Chat_remove_ExpiredTranscript( $dbh, $department["deptID"], $department["texpire"] ) ;
		if ( $deptid && ( $department["deptID"] == $deptid ) )
			$deptinfo = $departments[$c] ;
	}

	$markets_hash = Array() ;
	for ( $c = 0; $c < count( $markets ); ++$c )
	{
		$market = $markets[$c] ;
		$marketid = $market["marketID"] ;
		$markets_hash[$marketid] = Array() ;
		$markets_hash[$marketid]["name"] = $market["name"] ;
		$markets_hash[$marketid]["color"] = $market["color"] ;
	}
	Chat_remove_itr_OldRequests( $dbh ) ;

	$custom_search_options = "" ;
	$custom_search_field_hash = Array() ; // used for page function Util_Functions_Page

	for ( $c = 0; $c < count( $dept_customs ); ++$c )
	{
		$custom = $dept_customs[$c] ;
		if ( $custom && !isset( $custom_search_field_hash[$custom] ) && !preg_match( "/,/", $custom ) )
		{
			$custom_search_field_hash[$custom] = 1 ;
			$custom_search_options .= "<option value='cus_$c'>$custom</option>" ;
		}
		++$c ;
	}

	if ( $action === "delete" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove.php" ) ;
		$ces = Util_Format_Sanatize( Util_Format_GetVar( "ces" ), "ln" ) ;

		if ( !Chat_remove_Transcript( $dbh, $ces ) )
			$error = "Transcript delete error: $dbh[error]" ;
	}

	$query = "SELECT created FROM p_admins WHERE adminID = 1 LIMIT 1" ;
	database_mysql_query( $dbh, $query ) ;
	$super_admin = database_mysql_fetchrow( $dbh ) ;
	if ( isset( $super_admin["created"] ) ) { $y_start = date( "Y", $super_admin["created"] ) ; }
	else { $y_start = 2011 ; }

	$text = Util_Format_Sanatize( Util_Format_GetVar( "text" ), "" ) ; $text = ( $text ) ? $text : "" ; $text_query = urlencode( $text ) ;
	$s_as = Util_Format_Sanatize( Util_Format_GetVar( "s_as" ), "ln" ) ;
	$month = Util_Format_Sanatize( Util_Format_GetVar( "m" ), "n" ) ;
	$year = Util_Format_Sanatize( Util_Format_GetVar( "y" ), "n" ) ;
	if ( $month && $year )
	{
		$stat_start = mktime( 0, 0, 1, $month, 1, $year ) ;
		$stat_end = mktime( 23, 59, 59, $month, date( "t", $stat_start ), $year ) ;
	}
	else
	{
		$stat_start = mktime( 0, 0, 1, 1, 1, $year ) ;
		$stat_end = mktime( 23, 59, 59, 12, date( "t", mktime( 23, 59, 59, 12, 1, $year ) ), $year ) ;
	}
	$transcripts = Chat_ext_get_RefinedTranscripts( $dbh, $deptid, $opid, $tid, $s_as, $text, $year, $stat_start, $stat_end, $page, 15 ) ;

	$total_index = count($transcripts) - 1 ;
	$pages = Util_Functions_Page( $page, $index, 15, $transcripts[$total_index], "transcripts.php", "s_as=$s_as&text=$text_query&deptid=$deptid&opid=$opid&tid=$tid" ) ;

	$tags = ( isset( $VALS['TAGS'] ) && $VALS['TAGS'] ) ? unserialize( $VALS['TAGS'] ) : Array() ;
	$has_tags = 0 ;
	$tags_hash = Array() ;
	foreach ( $tags as $tag_index => $value )
	{
		if ( $tag_index != "c" )
		{
			LIST( $status, $color, $tag ) = explode( ",", $value ) ;
			$tag = rawurldecode( $tag ) ;
			$tags_hash[$tag_index] = $tag ;
			if ( $status )
				$has_tags = 1 ;
		}
	}
	$search_reset = ( $deptid || $opid || $year || $text || $s_as || $tid ) ? 1 : 0 ;
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
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo filemtime ( "../js/global.js" ) ; ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var global_ces ; var global_created ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "trans" ) ;

		show_div( "list" ) ;

		$('#form_search').on("submit", function() { return false ; }) ;

		<?php if ( $action && ( $action == "success" ) && !$error ): ?>do_alert( 1, "Update Success" ) ;
		<?php elseif ( $action && ( $action != "search" ) && !$error ): ?>do_alert( 1, "Delete Success" ) ;
		<?php elseif ( $error ): ?>do_alert( 0, "<?php echo $error ?>" ) ;
		<?php endif ; ?>

		<?php if ( $search_reset ): ?>toggle_search_trans(0) ;<?php endif ; ?>

	});

	function open_transcript( theces, theopname )
	{
		var url = "../ops/op_trans_view.php?ces="+theces+"&id=<?php echo $admininfo["adminID"] ?>&text=<?php echo urlencode( $text ) ?>&auth=setup&"+unixtime() ;

		$('#transcripts_list').find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf("img_") != -1 )
				$(this).css({ 'opacity': 1 }) ;
		} );

		$('#img_'+theces).css({ 'opacity': '0.4' }) ;

		External_lib_PopupCenter( url, theces, <?php echo $VARS_CHAT_WIDTH+100 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?>, "scrollbars=yes,menubar=no,resizable=1,location=no,width=<?php echo $VARS_CHAT_WIDTH+100 ?>,height=<?php echo $VARS_CHAT_HEIGHT+85 ?>,status=0" ) ;
	}

	function input_text_listen_search( e )
	{
		var key = -1 ;
		var shift ;

		key = e.keyCode ;
		shift = e.shiftKey ;

		if ( !shift && ( ( key == 13 ) || ( key == 10 ) ) )
			e.preventDefault() ;
	}

	function show_div( thediv )
	{
		var divs = Array( "list", "tags", "encr", "export" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#transcripts_'+divs[c]).hide() ;
			$('#menu_trans_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('#transcripts_'+thediv).show() ;
		$('#menu_trans_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
	}

	function do_notice( thediv, theces, thecreated )
	{
		var pos = $('#tr_'+theces).position() ;
		var width = $('#tr_'+theces).outerWidth() ;
		var height = $('#tr_'+theces).outerHeight() - 15 ;

		global_ces = theces ; global_created = thecreated ;
		show_div_notice(thediv, pos, width, height) ;
	}

	function do_delete_doit()
	{
		if ( global_ces && global_created )
			location.href = "transcripts.php?tid=<?php echo $tid ?>&ces="+global_ces+"&created="+global_created+"&y=<?php echo $year ?>&action=delete&index=<?php echo $index ?>&page=<?php echo $page ?>&deptid=<?php echo $deptid ?>&opid=<?php echo $opid ?>" ;
	}

	function show_div_notice( thediv, thepos, thewidth, theheight )
	{
		$('#div_notice_'+thediv).css({'top': thepos.top, 'left': thepos.left, 'width': thewidth, 'height': theheight}).fadeIn("fast") ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["trans"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='transcripts.php'" id="menu_trans_list">Transcripts</div>
			<div class="op_submenu" onClick="location.href='transcripts_tags.php'" id="menu_trans_tags">Tags</div>
			<!-- <div class="op_submenu" onClick="show_div('encr')" id="menu_trans_encr">Encryption</div> -->
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<div class="op_submenu_focus" style="margin-left: 0px;">View Transcripts</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/export_transcripts/export_transcripts.php" ) ): ?><div class="op_submenu3" onClick="location.href='../addons/export_transcripts/export_transcripts.php'">Export Transcripts</div><?php endif ; ?>
			<div class="op_submenu3" onClick="location.href='transcripts_settings.php'">Automatic Delete Transcripts</div>
			<div style="clear: both"></div>
		</div>

		<div id="transcripts_list" style="margin-top: 25px;">

			<div style="">
				<?php
					$trans_script = "/setup/transcripts.php" ;
				?>
				<div style=""><span class="info_neutral" style="cursor: pointer;" onClick="toggle_search_trans(0)"><img src="../pics/icons/search.png" width="16" height="16" border="0" alt=""> search transcripts</span></div>
				<div class="info_neutral" id="div_trans_search" style="display: none; padding-top: 15px;">
					<form method="POST" action="" id="form_trans_search" style="">
					<div style="">
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td colspan=6 style="padding-bottom: 15px;">
								<table cellspacing=0 cellpadding=0 border=0>
								<tr>
									<td style="padding-right: 15px;">
										Department<br>
										<select name="deptid" id="deptid" style="font-size: 16px;">
										<option value="0"></option>
										<?php
											for ( $c = 0; $c < count( $departments ); ++$c )
											{
												$department = $departments[$c] ;
												$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
												print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
											}
										?>
										</select>
									</td>
									<td style="padding-right: 15px;">
										Operator<br>
										<select name="opid" id="opid" style="font-size: 16px;">
										<option value="0"></option>
										<?php
											for ( $c = 0; $c < count( $operators ); ++$c )
											{
												$operator = $operators[$c] ;
												$selected = ( $opid == $operator["opID"] ) ? "selected" : "" ;
												print "<option value=\"$operator[opID]\" $selected>$operator[name]</option>" ;
											}
										?>
										</select>
									</td>
								</tr>
								</table>
							</td>
						</tr>
						<tr id="tr_search_criteria">
							<?php if ( $has_tags ): ?>
							<td style="padding-right: 15px;">
								Tag<br>
								<select id="tid" style="font-size: 16px;"><option value="0"></option>
								<?php
									foreach ( $tags as $index => $value )
									{
										if ( $index != "c" )
										{
											LIST( $status, $color, $tag ) = explode( ",", $value ) ;
											$tag = rawurldecode( $tag ) ;
											if ( $status )
											{
												$selected = ( $index == $tid ) ? "selected" : "" ;
												print "<option value=\"$index\" $selected>$tag</option>" ;
											}
										}
									}
								?>
								</select>
							</td>
							<?php else: ?>
							<td><input type="hidden" id="tid" name="tid" value=0></td>
							<?php endif; ?>
							<td style="padding-right: 15px;">
								Month<br>
								<select name="month" id="month">
								<?php
									for ( $c = 0; $c <= 12; ++$c )
									{
										$selected = ( $month == $c ) ? "selected" : "" ;
										$month_expanded = ( $c ) ? date( "F", mktime( 0, 0, 1, $c, 1, 2010 ) ) : "" ;
										print "<option value='$c' $selected>$month_expanded</option>" ;
									}
								?>
								</select>
							</td>
							<td style="padding-right: 15px;">
								Year<br>
								<?php
									$y = date( "Y", time() ) ;
									$year_string = "<select name='year' id='year' style='font-size: 16px;'>" ;
									$year_string .= ( isset( $page ) ) ? "<option value='0'></option>" : "" ;
									for ( $c = $y; $c >= $y_start; --$c ) { $selected = "" ; if ( $year == $c ) { $selected = "selected" ; } $year_string .= "<option value=$c $selected>$c</option>" ; } $year_string .= "</select>" ;
									print $year_string ;
								?>
							</td>
							<td style="padding-right: 15px;">
								Search Text<br>
								<input type="text" class="input_text_search input" size="15" maxlength="55" style="" id="input_search" value="<?php echo $text ?>" onKeydown="input_text_listen_search(event);" autocomplete="off">
							</td>
							<td style="padding-right: 15px;">
								Search Field<br>
								<select name="s_as" id="s_as" style="">
									<option value=""></option>
									<option value="text">text</option>
									<option value="ces">chat ID</option>
									<option value="vid">visitor ID</option> <?php echo $custom_search_options ?>
								</select>
							</td>
							<td>
								&nbsp;<br>
								<div>
									<input type="button" id="btn_page_search" style="" class="btn" value="search" onClick="do_search('<?php echo $CONF["BASE_URL"].$trans_script ?>')">
									<?php if ( $search_reset ): ?>
									&nbsp; &nbsp; &nbsp; <span class="info_blue"><a href="transcripts.php">reset</a></span>
									<?php endif ; ?>
								</div>
								<script data-cfasync="false" type="text/javascript">
									$('#s_as').val('<?php echo ( $s_as ) ? $s_as : "" ; ?>') ;
								</script>
							</td>
						</tr>
						</table>
					</div>
					</form>
				</div>
			</div>

			<div style="margin-top: 25px;"><img src="../pics/icons/flag_blue.png" width="14" height="14" border="0" alt=""> Flag icon indicates the transcript includes the visitor's comment.</div>

			<div style="margin-top: 25px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%" id="table_transcripts">
				<tr><td colspan="10"><?php echo $pages ?></td></tr>
				<tr>
					<td width="20" nowrap><div class="td_dept_header"></div></td>
					<td width="120"><div class="td_dept_header">Operator</div></td>
					<td><div class="td_dept_header">Visitor</div></td>
					<td><div class="td_dept_header">Created</div></td>
					<td width="90"><div class="td_dept_header">Duration</div></td>
					<td width="100%"><div class="td_dept_header">Question</div></td>
				</tr>
				<?php
					for ( $c = 0; $c < count( $transcripts )-1; ++$c )
					{
						$transcript = $transcripts[$c] ;
						if ( $transcript["opID"] )
						{
							// intercept nulled operator accounts that have been deleted
							if ( !isset( $operators_hash[$transcript["op2op"]] ) ) { $operators_hash[$transcript["op2op"]] = Array( 0, "&nbsp;", "" ) ; }
							if ( !isset( $operators_hash[$transcript["opID"]] ) ) { $operators_hash[$transcript["opID"]] = Array( 0, "&nbsp;", "" ) ; }

							$operator = ( $transcript["op2op"] ) ? $operators_hash[$transcript["op2op"]][1] : $operators_hash[$transcript["opID"]][1] ;
							$profile_pic_url = "../pics/profile.png" ;
							if ( $operators_hash[$transcript["opID"]][2] )
								$profile_pic_url = $operators_hash[$transcript["opID"]][2] ;

							$created_date = date( "M j, Y", $transcript["created"] ) ;
							$created_time = date( "$VARS_TIMEFORMAT", $transcript["created"] ) ;
							$duration = Util_Format_Duration( $transcript["ended"] - $transcript["created"] ) ;
							$fsize = Util_Functions_Bytes( $transcript["fsize"] ) ;
							$question = preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $transcript["question"] ) ;
							if ( $question == "null" ) { $question = "" ; }
							$vname = ( $transcript["op2op"] ) ? $operators_hash[$transcript["opID"]][1] : Util_Format_Sanatize( $transcript["vname"], "v" ) ;
							$email = ( $transcript["vemail"] && ( $transcript["vemail"] != "null" ) ) ? "<div style=\"margin-top: 5px; margin-bottom: 5px;\">$transcript[vemail]</div>" : "" ;
							$rating = ( $transcript["rating"] ) ? Util_Functions_Stars( "..", $transcript["rating"] ) : "" ;
							$initiated = ( $transcript["initiated"] ) ?  "<img src=\"../pics/icons/info_initiate.gif\" width=\"10\" height=\"10\" border=\"0\" alt=\"\" title=\"Operator Initiated Chat Invite\" alt=\"Operator Initiated Chat Invite\" class=\"info_misc\" style=\"padding: 4px;\"> " : "" ;
							$market = ( $transcript["marketID"] && isset( $markets_hash[$transcript["marketID"]] ) ) ? "<div style=\"margin-top: 15px;\">Campaign: <span class=\"info_clear\" style=\"padding: 2px; background: #".$markets_hash[$transcript["marketID"]]["color"]."; cursor: pointer;\" onClick=\"location.href='marketing_click.php'\">".$markets_hash[$transcript["marketID"]]["name"]."</span></div>" : "" ;

							$rating_note_seperator = ( $rating ) ? "&nbsp;" : "" ;
							$note = ( $transcript["noteID"] ) ?  "$rating_note_seperator<img src=\"../pics/icons/flag_blue.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\" title=\"includes visitor comment\" alt=\"includes visitor comment\" style=\"cursor: pointer;\" onClick=\"open_transcript('$transcript[ces]', '$operator')\"> &nbsp;" : "" ;
							$tag_string = "" ;
							if ( isset( $tags[$transcript["tag"]] ) )
							{
								LIST( $sthistatus, $thiscolor, $thistag ) = explode( ",", $tags[$transcript["tag"]] ) ;
								$tag_string = "<span class=\"info_neutral\" style=\"padding: 2px; background: #$thiscolor; border: 1px solid #C2C2C2; color: #474747; text-shadow: none;\">".rawurldecode( preg_replace( "/(.*?),/", "", $tags[$transcript["tag"]] ) )."</span> " ;
							}
							$accepted_op = Util_Format_Duration( $transcript["accepted_op"] ) ;
							$accepted_op_string = ( !$initiated ) ? "<div style=\"margin-top: 5px; cursor: help;\" class=\"info_neutral\" alt=\"chat accept time\" title=\"chat accept time\"><img src=\"../pics/icons/clock2.png\" width=\"16\" height=\"16\" border=\"0\"> <span>$accepted_op</span></div>" : "" ;	

							if ( $transcript["op2op"] )
								$question = " <img src=\"../themes/initiate/agent.png\" width=\"16\" height=\"16\" border=\"0\" title=\"Operator to Operator Chat\" alt=\"Operator to Operator Chat\" style=\"cursor: help;\" class=\"info_neutral\"> " ;
							else if ( $transcript["md5_vis"] == "grc" )
								$question = " <img src=\"../themes/initiate/group.png\" width=\"16\" height=\"16\" border=\"0\" title=\"Group Chat\" alt=\"Group Chat\" style=\"cursor: help;\" class=\"info_neutral\"> " ;

							$btn_view = "<div id=\"img_$transcript[ces]\"><a href=\"JavaScript:void(0)\" onClick=\"open_transcript('$transcript[ces]', '$operator')\"><img src=\"../pics/btn_view.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;
							$btn_delete = "<div style=\"margin-top: 10px;\"><a href=\"JavaScript:void(0)\" onClick=\"do_notice('delete', '$transcript[ces]', $transcript[created]);\"><img src=\"../pics/btn_delete.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;

							$gravatar = "" ;
							if ( $addon_gravatar && ( !isset( $dept_gravatars[$transcript["deptID"]] ) || $dept_gravatars[$transcript["deptID"]] ) )
							{
								$gravatar = ( $transcript["vemail"] && ( $transcript["vemail"] != "null" ) ) ? Util_Gravatar( $transcript["vemail"], 25 ) : "" ;
								if ( $gravatar )
									$gravatar = "<img src=\"$gravatar\" border=0 alt=\"Gravatar\" title=\"Gravatar\" style=\"border-radius: 50%;\">" ;
							}

							$bg_color = ( ($c+1) % 2 ) ? "FFFFFF" : "EDEDED" ;
							$td1 = "td_dept_td" ;

							$custom_vars_string = "" ;
							if ( $transcript["custom"] )
							{
								$custom_vars_string = "" ;
								$customs = explode( "-cus-", $transcript["custom"] ) ;
								for ( $c2 = 0; $c2 < count( $customs ); ++$c2 )
								{
									$custom_var = $customs[$c2] ;
									if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
									{
										LIST( $cus_name, $cus_val ) = explode( "-_-", rawurldecode( $custom_var ) ) ;
										if ( $cus_val )
										{
											if ( preg_match( "/^((http)|(www))/", $cus_val ) )
											{
												if ( preg_match( "/^(www)/", $cus_val ) ) { $cus_val = "http://$cus_val" ; }
												$cus_val_snap = ( strlen( $cus_val ) > 40 ) ? substr( $cus_val, 0, 15 ) . "..." . substr( $cus_val, -15, strlen( $cus_val ) ) : $cus_val ;
												$custom_vars_string .= "<div style=\"padding: 2px;\"><b>$cus_name:</b> <a href=\"$cus_val\" target=_blank>$cus_val_snap</a></div>" ;
											}
											else
											{
												$custom_vars_string .= "<div style=\"padding: 2px;\"><b>$cus_name:</b> $cus_val</div>" ;
											}
										}
									}
								}
								$custom_vars_string = ( $custom_vars_string ) ? "<div style=\"margin-top: 15px;\" class=\"info_custom\"><img src=\"../pics/icons/pin_note.png\" width=\"16\" height=\"16\" border=\"0\" alt=\"\"> Custom Fields<div style=\"margin-top: 5px; max-height: 65px; overflow: auto;\">$custom_vars_string</div></div>" : "" ;
							}

							print "<tr id=\"tr_$transcript[ces]\" style=\"background: #$bg_color\">
								<td class=\"$td1\">$btn_view$btn_delete</td>
								<td class=\"$td1\" nowrap>
									<div id=\"transcript_$transcript[ces]\">$initiated <img src='$profile_pic_url' width='25' height='25' border=0 style=\"border-radius: 50%;\"><div style=\"margin-top: 5px;\">$operator</div></div>
								</td>
								<td class=\"$td1\" nowrap>
									$gravatar $vname$email
									<table cellspacing=0 cellpadding=0 border=0><tr><td>$rating</td><td>$note</td></tr></table>
								</td>
								<td class=\"$td1\" nowrap>
									$created_date
									<div style=\"font-size: 10px; margin-top: 3px;\">($created_time)</div>
									<div style=\"margin-top: 8px;\"><span class=\"info_neutral\" style=\"padding: 3px; opacity: 0.5; filter: alpha(opacity=50);\">chat ID: $transcript[ces]</span></div>
								</td>
								<td class=\"$td1\" nowrap>
									<div style=\"cursor: help;\" alt=\"chat duration\" title=\"chat duration\"><img src=\"../pics/icons/clock3.png\" width=\"16\" height=\"16\" border=\"0\"> <span>$duration</span></div>
									<div style=\"margin-top: 5px; font-size: 10px; margin-top: 3px;\">($fsize)</div>
								</td>
								<td class=\"$td1\" style=\"word-break: break-word; word-wrap: break-word;\">
									<div class=\"info_neutral\">$tag_string $question</div>
									$market
									$custom_vars_string
								</td>
							</tr>" ;
						}
					}
					if ( $c == 0 )
						print "<tr><td colspan=9 class=\"td_dept_td\">Blank results.</td></tr>" ;
				?>
				<tr><td colspan="10"><?php echo $pages ?></td></tr>
				</table>
			</div>

		</div>

		<div id="transcripts_encr" style="display: none; margin-top: 25px;">
		</div>

		<div id="div_notice_delete" style="display: none; position: absolute;" class="info_error">
			<div style="padding: 10px;">
				<div class="edit_title">Delete this transcript permanently?</div>

				<div style="margin-top: 5px;"><button type="button" onClick="$(this).attr('disabled', true);do_delete_doit();">Delete</button> &nbsp; &nbsp; &nbsp; <a href="JavaScript:void(0)" style="color: #FFFFFF" onClick="$('#div_notice_delete').fadeOut('fast')">cancel</a></div>
			</div>
		</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
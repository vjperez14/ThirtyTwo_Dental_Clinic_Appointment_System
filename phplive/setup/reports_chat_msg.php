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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Messages/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;

	$error = "" ;

	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$page = Util_Format_Sanatize( Util_Format_GetVar( "page" ), "n" ) ;
	$index = Util_Format_Sanatize( Util_Format_GetVar( "index" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;
	$deptinfo = Array() ;
	if ( $deptid ) { $deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ; }
	$operators = Ops_get_AllOps( $dbh ) ;

	// make hash for quick refrence
	$dept_hash = Array() ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$dept_hash[$department["deptID"]] = $department["name"] ;
	}

	$messages = Messages_get_Messages( $dbh, $deptid, $page, 15 ) ;
	$total = Messages_get_TotalMessages( $dbh, $deptid ) ;
	$pages = Util_Functions_Page( $page, $index, 15, $total, "reports_chat_msg.php", "deptid=$deptid" ) ;

	$t_requests = Chat_ext_get_AllRequests( $dbh, 0 ) ;
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
	var global_messageid ;
	var global_savem = <?php echo isset( $deptinfo["deptID"] ) ? $deptinfo["savem"] : 0 ; ?> ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "rchats" ) ;
	});

	function open_message( themessageid )
	{
		var screen_width = screen.width ;
		var screen_height = screen.height ;
		var window_width = 720 ;
		var window_height = 550 ;

		global_messageid = themessageid ;

		$('#messages').find('*').each( function(){
			var div_name = this.id ;
			if ( div_name.indexOf("img_") != -1 )
				$(this).css({ 'opacity': 1 }) ;
		} );

		$('#img_'+themessageid).css({ 'opacity': '0.4' }) ;

		var url = "reports_msg_view.php?messageid="+themessageid+"&"+unixtime() ;
		var newwin = window.open( url, "message_"+themessageid, "scrollbars=yes,menubar=no,resizable=1,location=no,width="+window_width+",height="+window_height+",status=0" ) ;
		setTimeout( function(){ newwin.focus() ; }, 300 ) ;
	}

	function delete_message()
	{
		setTimeout( function() { $('#tr_'+global_messageid).remove() ; do_alert( 1, "Delete Success" ) ; }, 500 ) ;
	}

	function switch_dept( theobject )
	{
		location.href = "reports_chat_msg.php?deptid="+theobject.value+"&"+unixtime() ;
	}

	function do_savem( thevalue )
	{
		if ( global_savem != thevalue )
		{
			var json_data = new Object ;

			$.ajax({
				type: "POST",
				url: "../ajax/setup_actions.php",
				data: "action=update_savem&deptid=<?php echo $deptid ?>&savem="+thevalue+"&"+unixtime(),
				success: function(data){
					global_savem = thevalue ;
					do_alert( 1, "Update Success" ) ;
				}
			});
		}
	}

//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='reports_chat.php'">Chat Reports</div>
			<div class="op_submenu" onClick="location.href='reports_chat_active.php'">Active Chats (<?php echo count( $t_requests ) ?>)</div>
			<div class="op_submenu" onClick="location.href='reports_chat_missed.php'">Missed Chats</div>
			<div class="op_submenu_focus">Offline Messages</div>
			<!-- <div class="op_submenu" onClick="location.href='reports_chat_queue.php'">Waiting Queue</div> -->
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<div class="op_submenu_focus" style="margin-left: 0px;">Offline Messages</div>
			<div class="op_submenu3" onClick="location.href='reports_chat_msg_urls.php'">Message URLs</div>
			<div style="clear: both"></div>
		</div>

		<?php if ( count( $departments ) ): ?>
		<div style="margin-top: 25px;">
			<form method="POST" action="reports_chat_active.php" id="form_theform">
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td>
					<select name="deptid" id="deptid" style="font-size: 16px;" onChange="switch_dept( this )">
					<option value="0">All Departments</option>
					<?php
						$ops_assigned = 0 ;
						for ( $c = 0; $c < count( $departments ); ++$c )
						{
							$department = $departments[$c] ;
							$ops = Depts_get_DeptOps( $dbh, $department["deptID"] ) ;
							if ( count( $ops ) )
								$ops_assigned = 1 ;

							if ( $department["name"] != "Archive" )
							{
								$selected = ( $deptid == $department["deptID"] ) ? "selected" : "" ;
								print "<option value=\"$department[deptID]\" $selected>$department[name]</option>" ;
							}
						}
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan=2 style="padding-top: 5px;">
					<div class="info_neutral" style="text-shadow: none;">
						<?php
							if ( isset( $deptinfo["deptID"] ) ):
						?>
						<table cellspacing=0 cellpadding=0 border=0>
						<tr>
							<td>Automatically delete department saved offline messages created over &nbsp; </td>
							<td>
								<select id="savem" name="savem" onChange="do_savem(this.value)">
								<option value="0">do not delete</option>
								<?php
									for( $c = 1; $c <= 12; ++$c )
									{
										$selected = ( $deptinfo["savem"] == $c ) ? "selected" : "" ;
										print "<option value=\"$c\" $selected>$c</option>" ;
									}
								?>
								</select> months ago
							</td>
							<td></td>
						</tr>
						</table>
						<?php else: ?>
						<img src="../pics/icons/arrow_top.png" width="15" height="16" border="0" alt=""> Select a department above to update the message delete setting.
						<?php endif ; ?>
					</div>
					<div style="margin-top: 15px;"><img src="../pics/icons/info.png" width="12" height="12" border="0" alt=""> When a visitor leaves an offline message, the message is emailed to the <a href="depts.php?ftab=email">department</a> email address.  These are the saved copies of the messages.</div>
				</td>
			</tr>
			</table>
			</form>
		</div>
		<?php endif ; ?>

		<table cellspacing=0 cellpadding=0 border=0 width="100%" style="margin-top: 25px;" id="messages">
		<tr><td colspan="10"><?php echo $pages ?></td></tr>
		<tr>
			<td width="20" nowrap><div class="td_dept_header">&nbsp;</div></td>
			<td width="140"><div class="td_dept_header">Created</div></td>
			<td width="80" nowrap><div class="td_dept_header">Name</div></td>
			<td width="80" nowrap><div class="td_dept_header">Department</div></td>
			<td width="80"><div class="td_dept_header">Footprints</div></td>
			<td><div class="td_dept_header">Subject</div></td>
		</tr>
		<?php
			for ( $c = 0; $c < count( $messages ); ++$c )
			{
				$message = $messages[$c] ;

				$visitor = $message["vname"] ;
				$department = isset( $dept_hash[$message["deptID"]] ) ? $dept_hash[$message["deptID"]] : "&nbsp;" ;
				$created_date = date( "M j, Y", $message["created"] ) ;
				$created_time = date( "$VARS_TIMEFORMAT", $message["created"] ) ;
				$ip = $message["ip"] ;
				$subject = htmlentities( $message["subject"] ) ;

				$ces = ( $message["ces"] ) ? "<div style=\"margin-top: 8px;\"><span class=\"info_neutral\" style=\"padding: 3px; opacity: 0.5; filter: alpha(opacity=50);\">chat ID: $message[ces]</span></div>" : "" ;

				$bg_color = ( ($c+1) % 2 ) ? "FFFFFF" : "EDEDED" ;
				$td1 = "td_dept_td" ;

				$chat = ( $message["chat"] ) ? "chat.png" : "space.gif" ;
				$custom_vars_string = "" ;
				if ( $message["custom"] )
				{
					$custom_vars_string = "" ;
					$customs = explode( "-cus-", $message["custom"] ) ;
					for ( $c2 = 0; $c2 < count( $customs ); ++$c2 )
					{
						$custom_var = $customs[$c2] ;
						if ( $custom_var && preg_match( "/-_-/", $custom_var ) )
						{
							LIST( $cus_name, $cus_val ) = explode( "-_-", rawurldecode( $custom_var ) ) ;
							if ( $cus_val )
							{
								if ( preg_match( "/^Attachment URL/i", $cus_name ) )
									$cus_name = "<img src=\"../themes/initiate/attach.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"\"> $cus_name" ;

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

				$btn_view = "<div id=\"img_$message[messageID]\"><a href=\"JavaScript:void(0)\" onClick=\"open_message('$message[messageID]')\"><img src=\"../pics/btn_view.png\" width=\"64\" height=\"23\" border=\"0\" alt=\"\"></a></div>" ;

				print "<tr id=\"tr_$message[messageID]\" style=\"background: #$bg_color;\">
					<td class=\"$td1\">$btn_view</td>
					<td class=\"$td1\" nowrap>
						$created_date
						<div style=\"font-size: 10px; margin-top: 3px;\">($created_time)</div>
						$ces
					</td>
					<td class=\"$td1\">
						$visitor
						<div style=\"margin-top: 5px;\"><a href=\"mailto:$message[vemail]\">$message[vemail]</a></div>
					</td>
					<td class=\"$td1\">$department</td>
					<td class=\"$td1\" nowrap>$message[footprints]</td>
					<td class=\"$td1\">
						<div id=\"div_$message[messageID]\" style=\"word-break: break-word; word-wrap: break-word;\">$subject$custom_vars_string</div>
					</td>
				</tr>" ;
			}
			if ( $c == 0 )
				print "<tr><td colspan=8 class=\"td_dept_td\">Blank results.</td></tr>" ;
		?>
		<tr><td colspan="10"><?php echo $pages ?></td></tr>
		</table>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>
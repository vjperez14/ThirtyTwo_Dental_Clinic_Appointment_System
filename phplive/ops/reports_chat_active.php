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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get_ext.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/get_ext.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Chat/remove_itr.php" ) ;

	Chat_remove_itr_OldRequests( $dbh ) ;

	$console = Util_Format_Sanatize( Util_Format_GetVar( "console" ), "n" ) ;
	$menu = Util_Format_Sanatize( Util_Format_GetVar( "menu" ), "ln" ) ;
	$wp = Util_Format_Sanatize( Util_Format_GetVar( "wp" ), "n" ) ;
	$auto = Util_Format_Sanatize( Util_Format_GetVar( "auto" ), "n" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$menu = ( $menu ) ? $menu : "reports" ;
	$error = "" ;

	$departments = ( $opinfo["view_chats"] == 1 ) ? Depts_get_AllDepts( $dbh ) : Depts_get_OpDepts( $dbh, $opinfo["opID"] ) ;
	$depts_hash = "" ; $depts_hash_array = Array() ;
	for ( $c = 0; $c < count( $departments ); ++$c )
	{
		$department = $departments[$c] ;
		$depts_hash .= "depts_hash[".$department["deptID"]."] = '$department[name]' ;" ;
		$depts_hash_array[$department["deptID"]] = $department["name"] ;
	}

	if ( ( $action == "fetch" ) && $opinfo["view_chats"] )
	{
		$phplivebotinfo = Ops_get_ext_OpInfoByLogin( $dbh, "phplivebot" ) ;
		$bid = ( isset( $phplivebotinfo["opID"] ) ) ? $phplivebotinfo["opID"] : 0 ;
		$active_requests = Chat_ext_get_OpAllRequests( $dbh, 0 ) ;

		$json_data = "json_data = { \"status\": 1, \"requests\": [  " ;
		for ( $c = 0; $c < count( $active_requests ); ++$c )
		{
			$requestinfo = $active_requests[$c] ;
			$ces = $requestinfo["ces"] ;
			$opid = $requestinfo["opID"] ;
			$deptid = $requestinfo["deptID"] ;
			$vname = rawurlencode( Util_Format_Sanatize( $requestinfo["vname"], "v" ) ) ;
			$vemail = rawurlencode( $requestinfo["vemail"] ) ;
			$created = date( "M j ($VARS_TIMEFORMAT)", $requestinfo["created"] ) ;
			$duration = Util_Format_Duration( $now - $requestinfo["created"] ) ;
			$ip = $requestinfo["ip"] ;
			$question = rawurlencode( preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", $requestinfo["question"] ) ) ;
			$status = $requestinfo["status"] ;
			$op2op = $requestinfo["op2op"] ;
			$initiated = $requestinfo["initiated"] ;

			if ( ( $requestinfo["md5_vis_"] != "op2op" ) && ( $requestinfo["md5_vis_"] != "grc" ) && ( !$bid || ( $bid && ( $opid != $bid ) ) ) )
			{
				if ( ( $opinfo["view_chats"] == 1 ) || isset( $depts_hash_array[$deptid] ) )
					$json_data .= "{ \"opid\": $opid, \"deptid\": $deptid, \"vname\": \"$vname\", \"vemail\": \"$vemail\", \"created\": \"$created\", \"duration\": \"$duration\", \"ip\": \"$ip\", \"question\": \"$question\", \"status\": $status, \"ces\": \"$ces\", \"op2op\": $op2op, \"initiated\": $initiated }," ;
			}
		}
		$json_data = substr_replace( $json_data, "", -1 ) ;
		$json_data .= "	] };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		$json_data = Util_Format_Trim( $json_data ) ; $json_data = preg_replace( "/\t/", "", $json_data ) ;
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$total_requests = 0 ;
	$operators = Ops_get_AllOps( $dbh ) ;

	$ops_hash = "" ;
	for ( $c = 0; $c < count( $operators ); ++$c )
	{
		$operator = $operators[$c] ;
		$ops_hash .= "ops_hash[".$operator["opID"]."] = '$operator[name]' ;" ;
	}
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> Operator </title>

<meta name="description" content="v.<?php echo $VERSION ?>">
<meta name="keywords" content="<?php echo md5( $KEY ) ?>">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8"> 
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	"use strict" ;
	var wp = ( typeof( parent.wp ) != "undefined" ) ? parent.wp : 0 ;
	var base_url_full = ( typeof( parent.base_url_full ) != "undefined" ) ? parent.base_url_full : ".." ;
	var refresh_counter = 30 ;
	var si_refresh ;

	var ops_hash = new Object ;
	var depts_hash = new Object ;

	<?php echo $ops_hash ?>
	<?php echo $depts_hash ?>

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;

		init_menu() ;
		toggle_menu_op( "reports" ) ;

		$('#refresh_counter').html( pad( refresh_counter, 1 ) ) ;

		fetch_chats(0) ;
		init_timer_chat_active() ;
	});

	function open_chat( theces, theopid, theop2op )
	{
		if ( ( theopid == <?php echo $opinfo["opID"] ?> ) || ( theop2op == <?php echo $opinfo["opID"] ?> ) )
		{
			do_alert( 0, "You are the operator in this chat." ) ;
		}
		else
		{
			var url = base_url_full+"/ops/op_trans_view.php?ces="+theces+"&id=<?php echo $opinfo["opID"] ?>&auth=op&realtime=1&"+unixtime() ;

			if ( wp )
				parent.wp_new_win( url, "Chat_"+theces, <?php echo $VARS_CHAT_WIDTH+120 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?> ) ;
			else
				External_lib_PopupCenter( url, theces, <?php echo $VARS_CHAT_WIDTH+120 ?>, <?php echo $VARS_CHAT_HEIGHT+85 ?>, "scrollbars=yes,menubar=no,resizable=1,location=no,width=<?php echo $VARS_CHAT_WIDTH+100 ?>,height=<?php echo $VARS_CHAT_HEIGHT+85 ?>,status=0" ) ;
		}
	}

	function fetch_chats( theflag )
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		$.ajax({
		type: "POST",
		url: "./reports_chat_active.php",
		data: "action=fetch&"+unique,
		success: function(data){
			try{
				eval( data ) ;
			} catch(e){
				do_alert( 0, "System sent an invalid response.  Please try again." ) ;
				return false ;
			}

			if ( json_data.status )
			{
				var output_string = "" ;
				for ( var c = 0; c < json_data.requests.length; ++c )
				{
					var requestinfo = json_data.requests[c] ;

					var operator = ( typeof( ops_hash[requestinfo["opid"]] ) != "undefined" ) ? ops_hash[requestinfo["opid"]] : "&nbsp;" ;
					var visitor = decodeURIComponent( requestinfo["vname"] ) ;
					var email = ( requestinfo["vemail"] && ( requestinfo["vemail"] != "null" ) ) ? "<div style=\"margin-top: 5px;\">"+decodeURIComponent( requestinfo["vemail"] )+"</div>" : "" ;
					var department = ( typeof( depts_hash[requestinfo["deptid"]] ) != "undefined" ) ? depts_hash[requestinfo["deptid"]] : "" ;
					var created = requestinfo["created"] ;
					var duration = requestinfo["duration"] ;
					var ip = requestinfo["ip"] ;
					var question = decodeURIComponent( requestinfo["question"] ) ;

					var icon_link = ( requestinfo["status"] ) ? "<img src=\"../pics/icons/chats.png\" style=\"cursor: pointer;\" onClick=\"open_chat('"+requestinfo["ces"]+"', "+requestinfo["opid"]+", "+requestinfo["op2op"]+")\" id=\"img_"+requestinfo["ces"]+"\" title=\"view chat session\" alt=\"view chat session\">" : "" ;

					var routing_string = "" ;
					if ( requestinfo["initiated"] && !requestinfo["status"] )
						routing_string = "<div class=\"info_warning\" style=\"margin-bottom: 5px;\"><img src=\"../pics/icons/info_initiate.gif\" width=\"10\" height=\"10\" border=\"0\" alt=\"\" title=\"Operator Initiated Chat Invite\" alt=\"Operator Initiated Chat Invite\" class=\"info_misc\"> Waiting for Visitor Action</div>" ;
					else if ( !requestinfo["status"] )
					{
						routing_string = "<div class=\"info_warning\" style=\"margin-bottom: 5px;\" title=\"Chat request is routing to operators.\" alt=\"Chat request is routing to operators.\">Routing to Operators</div>" ;
						if ( requestinfo["created"] < ( unixtime() - (60*10) ) )
							routing_string = "<div class=\"info_warning\" style=\"margin-bottom: 5px;\" title=\"Visitor has abandoned the chat. Request will timeout in 30 minutes.\" alt=\"Visitor has abandoned the chat. Request will timeout in 30 minutes.\">Idle Request</div>" ;
					}

					var bg_color = ( (c+1) % 2 ) ? "FFFFFF" : "EDEDED" ;
					var td1 = "td_dept_td" ;

					output_string += "<tr id=\"tr_"+requestinfo["ces"]+"\" style=\"background: #"+bg_color+";\">"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\" nowrap>"+icon_link+routing_string+"</td>"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\" nowrap><b><div id=\"chat_"+requestinfo["ces"]+"\">"+operator+"</div></b></td>"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\" nowrap>"+visitor+email+"</td>"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\">"+department+"</td>"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\" nowrap>"+created+"</td>"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\" nowrap>"+duration+"</td>"+
						"<td class=\""+td1+"\" style=\"padding: 15px;\">"+question+"</td>"+
					"</tr>" ;
				}
				if ( !json_data.requests.length )
					output_string = "<tr><td colspan=8 class=\"td_dept_td\">Blank results.</td></tr>" ;

				if ( theflag ) { $('#span_refresh_success').show() ; }

				$('#span_total_requests').html( json_data.requests.length ) ;
				$('#tbody_requests').fadeOut("fast").html( output_string ).fadeIn("fast") ;
			}
			else
				do_alert( 0, json_data.error ) ;
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Connection error.  Please refresh the page to try again." ) ;
		} });
	}

	function init_timer_chat_active()
	{
		if ( typeof( si_refresh ) != "undefined" ) { clearTimeout( si_refresh ) ; }

		var refresh_counter_temp = refresh_counter ;
		si_refresh = setInterval(function(){
			if ( refresh_counter_temp <= 0 )
			{
				fetch_chats(1) ;
				$('#refresh_counter').html( 0 ) ;
				$('#btn_refresh').attr('disabled', true) ;
				refresh_counter_temp = refresh_counter ;
			}
			else
			{
				if ( ( refresh_counter_temp == ( refresh_counter - 5 ) ) && $('#btn_refresh').prop('disabled') )
				{
					$('#span_refresh_success').hide() ;
					$('#btn_refresh').attr('disabled', false) ;
				}

				$('#refresh_counter').html( pad( refresh_counter_temp, 1 ) ) ;
				--refresh_counter_temp ;
			}
		}, 1000) ;
	}

	function do_refresh()
	{
		fetch_chats(1) ;
		init_timer_chat_active() ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ); ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='reports.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'">Chat Reports</div>
			<div class="op_submenu_focus">Active Chats (<span id="span_total_requests"><?php echo $total_requests ?></span>)</div>
			<div style="clear: both"></div>
		</div>

		<?php if ( $opinfo["view_chats"] ): ?>
		<div style="margin-top: 25px;">View <?php echo ( $opinfo["view_chats"] == 2 ) ? "department" : "all" ; ?> active chat sessions in real-time.  This page will automatically refresh in <span id="refresh_counter" class="info_neutral"></span> seconds.  <button type="button" id="btn_refresh" class="btn" onClick="$(this).attr('disabled',true);do_refresh();" disabled>refresh now</button> &nbsp; <span class="info_good" style="display: none;" id="span_refresh_success">refresh success</span></div>

		<table cellspacing=0 cellpadding=0 border=0 width="100%" style="margin-top: 25px;">
		<tr>
			<td width="40"><div class="td_dept_header">&nbsp;</div></td>
			<td width="80"><div class="td_dept_header">Operator</div></td>
			<td width="80"><div class="td_dept_header">Visitor</div></td>
			<td width="140"><div class="td_dept_header">Department</div></td>
			<td width="140"><div class="td_dept_header">Created</div></td>
			<td width="140"><div class="td_dept_header">Duration</div></td>
			<td><div class="td_dept_header">Question</div></td>
		</tr>
		<tbody id="tbody_requests">
		</tbody>
		</table>
		<?php else: ?>
		<div style="margin-top: 25px;" class="info_error">Account does not have access to this area.</div>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ); ?>
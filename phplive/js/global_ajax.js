function http_text( thetext )
{
	var json_data = new Object ;
	var unique = unixtime() ; var millisec = (new Date).getTime() ;
	var orig_text = thetext ;
	thetext = "<!--begin:"+millisec+"-->"+thetext+"<!--end:"+millisec+"-->" ;

	var sendtext = encodeURIComponent( phplive_base64.encode( thetext ) ) ;
	var thesalt = ( typeof( salt ) != "undefined" ) ? salt : "nosalt" ;
	var this_mapp = ( !isop ) ? chats[ces]["mapp"] : mapp ;
	var bid_query = "" ;
	if ( ( typeof( bid ) != "undefined" ) && bid )
	{
		bid_query = bot_get_query() ;
		$('#input_text').attr("disabled", true) ;
		$('#chat_processing').show() ;
	}

	if ( typeof( chats[ces] ) != "undefined" )
	{
		chats[ces]["fmlid"] = chats[ces]["fmlid"]+","+millisec ;

		$.ajax({
		type: "POST",
		url: base_url+"/ajax/chat_submit.php",
		data: "requestid="+chats[ces]["requestid"]+"&d="+chats[ces]["deptid"]+"&isop="+isop+"&isop_="+isop_+"&isop__="+isop__+"&op2op="+chats[ces]["op2op"]+"&ces="+ces+"&mp="+this_mapp+"&text="+sendtext+"&salt="+thesalt+bid_query+"&unique="+unique+"&",
		success: function(data){
			try {
				eval(data) ;
				if ( chat_http_error )
				{
					st_http_text = undeefined ;
					if ( !chats[ces]["disconnected"] )
					{
						do_alert( 1, "Reconnect success!" ) ;
						chat_http_error = 0 ; st_http_backlog_responses = "" ;
					} else { $('#chat_processing').hide() ; }
				}
			} catch(err) {
				if ( !chats[ces]["disconnected"] )
				{
					if ( st_http_backlog_responses.indexOf( orig_text ) == -1 )
					{
						st_http_backlog_responses += thetext + "<>" ;
					}
					do_alert( 0, "Error sending response.  Retrying..." ) ; chat_http_error = 1 ;
					if ( typeof( st_http_text ) != "undefined" ) { clearTimeout( st_http_text ) ; }
					st_http_text = setTimeout( function(){ http_text( st_http_backlog_responses  ) ; }, 6000 ) ;
				} else { $('#chat_processing').hide() ; }
				return false ;
			}

			if ( json_data.status )
			{
				if ( typeof( st_http ) != "undefined" ) { clearTimeout( st_http ) ; st_http = undeefined ; }
				clearTimeout( st_typing ) ; st_typing = undeefined ;

				update_ces( json_data ) ;

				if ( ( typeof( bid ) != "undefined" ) && bid )
				{
					// chatting() function will enable the textarea and hide the loading icon
					// for bot, need to do it in chatting(), after text is fetched
				}
				else
					$('#chat_processing').hide() ;
			}
			else { do_alert( 0, "Error sending message.  Please refresh the page and try again." ) ; }
		},
		error:function (xhr, ajaxOptions, thrownError){
			if ( !chats[ces]["disconnected"] )
			{
				if ( st_http_backlog_responses.indexOf( orig_text ) == -1 )
				{
					st_http_backlog_responses += thetext + "<>" ;
				}
				do_alert( 0, "Error contacting server.  Retrying..." ) ; chat_http_error = 1 ;
				if ( typeof( st_http_text ) != "undefined" ) { clearTimeout( st_http_text ) ; }
				st_http_text = setTimeout( function(){ http_text( st_http_backlog_responses  ) ; }, 6000 ) ;
			} else { $('#chat_processing').hide() ; }
		} });
	}
}

function send_istyping()
{
	if ( ( typeof( bid ) != "undefined" ) && bid )
		return true ;
	else
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		if ( typeof( chats[ces] ) != "undefined" )
		{
			$.ajax({
			type: "GET",
			url: base_url+"/ajax/chat_actions_istyping.php",
			data: "a=t&isop="+isop+"&isop_="+isop_+"&c="+ces+"&f=1&"+unique+"&",
			success: function(data){
				try {
					eval(data) ;
				} catch(err) {
					do_alert( 0, err ) ;
					return false ;
				}

				if ( json_data.status ) {
					return true ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				// suppress error to limit confusion... if error here, there will be error reporting in more crucial areas
			} });
		}
	}
}

function clear_istyping( theforce )
{
	var json_data = new Object ;
	var unique = unixtime() ;

	if ( typeof( theforce ) == "undefined" ) { theforce = 0 ; }
	if ( typeof( chats[ces] ) != "undefined" )
	{
		$.ajax({
		type: "GET",
		url: base_url+"/ajax/chat_actions_istyping.php",
		data: "a=t&isop="+isop+"&isop_="+isop_+"&c="+ces+"&f="+theforce+"&"+unique+"&",
		success: function(data){
			try {
				eval(data) ;
			} catch(err) {
				do_alert( 0, err ) ;
				return false ;
			}

			if ( json_data.status ) {
				clearTimeout( st_typing ) ;
				st_typing = undeefined ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			// suppress error to limit confusion... if error here, there will be error reporting in more crucial areas
		} });
	}
}

function disconnect( theclick, theces, thevclick )
{
	if ( typeof( theces ) == "undefined" ) { theces = ces ; }
	if ( typeof( thevclick ) == "undefined" ) { thevclick = 0 ; }
	vclick = thevclick ;
	
	if ( theclick )
	{
		document.getElementById('info_disconnect')._onclick = document.getElementById('info_disconnect').onclick ;
		$('#info_disconnect').prop( "onclick", null ).html('<img src="'+base_url+'/themes/'+theme+'/loading_fb.gif" width="16" height="11" border="0" alt="">') ;
		if ( mapp ) { $('#info_disconnect_mapp').prop( "onclick", null ).html('<img src="'+base_url+'/themes/'+theme+'/loading_fb.gif" width="16" height="11" border="0" alt="">') ; }
	}

	else if ( theces == ces ) { $('#chat_vistyping_wrapper').hide() ; $('#chat_vistyping').hide() ; }

	if ( ( ( typeof( theces ) != "undefined" ) && ( typeof( chats[theces] ) != "undefined" ) ) )
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		// limit multiple clicks during internet lag
		if ( !chats[theces]["disconnect_click"] )
		{
			chats[theces]["disconnect_click"] = theclick ;

			var bid_query = "" ;
			if ( ( typeof( bid ) != "undefined" ) && bid )
			{
				bid_query = "&b="+bid ;
			}

			$.ajax({
			type: "POST",
			url: base_url+"/ajax/chat_actions_disconnect.php",
			data: "action=disconnect&isop="+isop+"&isop_="+isop_+"&isop__="+isop__+"&ces="+theces+"&vis_token="+chats[ces]["vis_token"]+"&ip="+chats[theces]["ip"]+"&vclick="+thevclick+bid_query+"&unique="+unique+"&",
			success: function(data){
				try {
					eval(data) ;
				} catch(err) {
					chats[theces]["disconnect_click"] = 0 ;
					if ( theclick )
					{
						document.getElementById('info_disconnect').onclick = document.getElementById('info_disconnect')._onclick ;
						if ( mapp ) { document.getElementById('info_disconnect_mapp').onclick = document.getElementById('info_disconnect')._onclick ; }
					}
					do_alert( 0, "Error processing disconnect.  Please refresh the page and try again. [e1]" ) ;
					return false ;
				}

				if ( theclick )
				{
					document.getElementById('info_disconnect').onclick = document.getElementById('info_disconnect')._onclick ;
					if ( mapp ) { document.getElementById('info_disconnect_mapp').onclick = document.getElementById('info_disconnect')._onclick ; }
				}
				if ( json_data.status )
				{
					if ( parseInt( isop ) && !theclick )
					{
						chats[theces]["disconnect_click"] = 0 ;
						chats[theces]["disconnected"] = unixtime() ;
						if ( !$('textarea#input_text').is(':disabled') ) { $('textarea#input_text').val( "" ).attr("disabled", true) ; }
					}
					else
						cleanup_disconnect( json_data.ces ) ;

					if ( isop && !mapp ) { init_maxc() ; }
				}
				else
				{
					chats[theces]["disconnect_click"] = 0 ;
					if ( theclick )
					{
						document.getElementById('info_disconnect').onclick = document.getElementById('info_disconnect')._onclick ;
						if ( mapp ) { document.getElementById('info_disconnect_mapp').onclick = document.getElementById('info_disconnect')._onclick ; }
					}
					do_alert( 0, "Error processing disconnect.  Please refresh the page and try again. [e2]" ) ;
				}
			},
			statusCode: {
				500: function() {
					chats[theces]["disconnect_click"] = 0 ;
					if ( theclick )
					{
						document.getElementById('info_disconnect').onclick = document.getElementById('info_disconnect')._onclick ;
						if ( mapp ) { document.getElementById('info_disconnect_mapp').onclick = document.getElementById('info_disconnect')._onclick ; }
					}
					do_alert( 0, "Error processing disconnect.  Please refresh the page and try again. [e4]" ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				chats[theces]["disconnect_click"] = 0 ;
				if ( theclick )
				{
					document.getElementById('info_disconnect').onclick = document.getElementById('info_disconnect')._onclick ;
					if ( mapp ) { document.getElementById('info_disconnect_mapp').onclick = document.getElementById('info_disconnect')._onclick ; }
				}
				do_alert( 0, "Error processing disconnect.  Please refresh the page and try again. [e3 - "+xhr.status+"]" ) ;
			} });
		}
	}
}

function submit_survey( thevalue, thetexts )
{
	var json_data = new Object ;
	var unique = unixtime() ;

	// isop_ holds the recent opID for transfer chats
	// but stats will show 0 chats with rating showing... confusing.  keep original op (chats[ces]["opid"]) for rating

	if ( parseInt( thevalue ) )
	{
		$.ajax({
		type: "POST",
		url: base_url+"/ajax/chat_actions_rating.php",
		data: "action=rating&token="+phplive_md5( chats[ces]["ip"] )+"&ces="+ces+"&rating="+thevalue+"&unique="+unique+"&",
		success: function(data){
			try {
				eval(data) ;
			} catch(err) {
				do_alert( 0, err ) ;
				return false ;
			}

			if ( json_data.status )
			{
				chats[ces]["survey"] = 2 ;
				//do_alert( 1, thetexts[0] ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			// suppress error to limit confusion... if error here, there will be error reporting in more crucial areas
		} });
	}
}

function queueing()
{
	var unique = unixtime() ;
	var json_data = new Object ;

	var this_rstring = "" ;
	for ( var this_opid in chats[ces]["q_opids"] )
	{
		if ( this_opid ) { this_rstring = this_opid+","+this_rstring ; }
	} rstring = this_rstring ;

	var minutes = Math.floor( ( c_queueing * parseInt( VARS_JS_REQUESTING ) )/60 ) ;
	if ( minutes >= parseInt( VARS_EXPIRED_QUEUE_IDLE ) )
	{
		leave_a_mesg(0, "") ;
		return false ;
	}

	$.ajax({
	type: "GET",
	url: base_url+"/ajax/chat_queueing.php",
	data: "&a=queueing&e="+embed+"&c="+ces+"&q="+queue+"&ql="+qlimit+"&d="+deptid+"&t="+phplive_browser_token+"&cq="+c_queueing+"&r="+rtype+"&rs="+rstring+"&"+unique,
	success: function(data){
		try {
			eval(data) ;
		} catch(err) {
			// suppress
		}

		if ( typeof( st_queueing ) != "undefined" )
		{
			clearTimeout( st_queueing ) ;
			st_queueing = undeefined ;
		}

		if ( json_data.status == 1 )
		{
			var total_ops_online = ( typeof( json_data.t_ops ) != "undefined" ) ? parseInt( json_data.t_ops ) : -1 ;

			if ( ( typeof( json_data.accepted ) != "undefined" ) && json_data.accepted )
			{
				// chat accepted reload page to process (for multiple sessions situations)
				window.location.href = window.location.href ;
			}
			else if ( ( ( total_ops_online == -1 ) || total_ops_online ) && ( json_data.created != 615 ) )
			{
				process_queue( false, parseInt( json_data.qpos ), parseInt( json_data.est ), parseInt( json_data.created ) ) ;
				++c_queueing ;
				st_queueing = setTimeout( "queueing()" , VARS_JS_REQUESTING * 1000 ) ;
			}
			else
			{
				leave_a_mesg(0, "") ;
			}
		}
		else if ( json_data.status == 2 )
		{
			// operator is available
			if ( ces == json_data.ces ) { process_queue( json_data.ces, parseInt( json_data.qpos ), 0, parseInt( json_data.created ) ) ; }
			else
			{
				process_queue( false, parseInt( json_data.qpos ), 0, parseInt( json_data.created ) ) ;
				++c_queueing ;
				st_queueing = setTimeout( "queueing()" , VARS_JS_REQUESTING * 1000 ) ;
			}
		}
		else { do_alert( 0, json_data.error ) ; stopit(0) ; }
	},
	error:function (xhr, ajaxOptions, thrownError){
		if ( typeof( st_queueing ) != "undefined" )
		{
			clearTimeout( st_queueing ) ;
			st_queueing = undeefined ;
		}
		st_queueing = setTimeout( "queueing()" , VARS_JS_REQUESTING * 1000 ) ;
		++dc_c_queueing ;
		if ( dc_c_queueing > 1 ) { do_alert( 0, CHAT_ERROR_DC ) ; }
	} });
}

function routing( theopid )
{
	var unique = unixtime() ;
	var json_data = new Object ;

	// routing and chatting should never happen at the same time
	if ( typeof( st_chatting ) != "undefined" ) { clearTimeout( st_chatting ) ; st_chatting = undeefined ; }
	toggle_show_disconnect(1) ;

	$.ajax({
	type: "GET",
	url: base_url+"/ajax/chat_routing.php",
	data: "&a=routing&c="+ces+"&d="+deptid+"&r="+rtype+"&rt="+rtime+"&cr="+c_routing+"&lg="+lang+"&q="+queue+"&o="+theopid+"&pr="+proto+"&b="+bid+"&"+unique,
	success: function(data){
		try {
			eval(data) ;
		} catch(err) {
			// suppress
		}

		if ( typeof( st_routing ) != "undefined" ) { clearTimeout( st_routing ) ; st_routing = undeefined ; }
		if ( json_data.status == 1 )
			init_connect( json_data ) ;
		else if ( json_data.status == 2 )
		{
			// routed to new operator
			var opid = parseInt( json_data.opid ) ;

			if ( typeof( json_data.rtime ) != "undefined" )
			{
				rtime = parseInt( json_data.rtime ) ;
			}
			++c_routing ;

			if ( opid ) { chats[ces]["q_opids"][opid] = 1 ; }
			st_routing = setTimeout( "routing(0)" , VARS_JS_REQUESTING * 1000 ) ;

			// page reload situation of when chat transferred, transferred op not available and
			// the chat is transferred back to original op
			if ( !chats[ces]["opid"] && chats[ces]["chatting"] )
			{
				init_connect( json_data ) ;
			}
		}
		else if ( json_data.status == 10 )
		{
			stopit(0) ;

			var q_ops = ( typeof( json_data.q_ops ) != "undefined" ) ? json_data.q_ops : "" ;
			leave_a_mesg(1, q_ops) ;
		}
		else if ( json_data.status == 12 )
		{
			stopit(0) ;

			leave_a_mesg(1, "") ;
		}
		else if ( json_data.status == 13 )
		{
			stopit(0) ;

			var q_ops = ( typeof( json_data.q_ops ) != "undefined" ) ? json_data.q_ops : "" ;
			leave_a_mesg(1, q_ops) ;
		}
		else if ( json_data.status == 11 )
		{
			stopit(0) ;

			var q_ops = ( typeof( json_data.q_ops ) != "undefined" ) ? json_data.q_ops : "" ;
			vclick = 2 ; // 2=flag not to store stats
			leave_a_mesg(1, q_ops) ;
		}
		else if ( json_data.status == 0 )
		{
			++c_routing ;
			st_routing = setTimeout( "routing(0)" , VARS_JS_REQUESTING * 1000 ) ;
		}
	},
	error:function (xhr, ajaxOptions, thrownError){
		if ( typeof( st_routing ) != "undefined" )
		{
			clearTimeout( st_routing ) ;
			st_routing = undeefined ;
		}
		st_routing = setTimeout( "routing(0)" , VARS_JS_REQUESTING * 1000 ) ;
	} });
}

function requesting()
{
	var start = microtime( true ) ;
	var unique = unixtime() ;
	var json_data = new Object ;
	var chatting_query = get_chatting_query() ; if ( chatting_query ) { chatting_query = "&"+chatting_query ; }
	var isopr = ( ( typeof( addon_whisper ) != "undefined" ) && addon_whisper ) ? addon_whisper : 0 ;
	var q_ces = "" ;
	++c_requesting ; c_chatting = c_requesting ;

	for ( var ces in chats )
	{
		q_ces += "qc[]="+ces+"&" ;
	}

	if ( typeof( st_network ) != "undefined" ) { clearTimeout( st_network ) ; st_network = undeefined ; }
	if ( typeof( st_requesting ) != "undefined" ) { clearTimeout( st_requesting ) ; st_requesting = undeefined ; }

	if ( !reconnect )
	{ st_network = setTimeout( function(){ stopit(0) ; check_network( 715, undeefined, undeefined ) ; }, parseInt( VARS_JS_OP_CONSOLE_TIMEOUT ) * 1000 ) ; }
	else
	{ st_network = setTimeout( function(){ stopit(0) ; check_network( 717, undeefined, undeefined ) ; }, parseInt( VARS_JS_REQUESTING ) * 1000 ) ; }

	$.ajax({
	type: "GET",
	url: base_url+"/ajax/chat_op_requesting.php",
	data: "cs="+cs+"&m="+mapp+"&a=rq&st="+current_status+"&oo="+op2op_enabled+"&pr="+proto+"&tr="+traffic+"&cr="+c_requesting+"&"+q_ces+chatting_query+"&co="+ses_console+"&mid="+messageboard_id+"&isopr="+isopr+"&"+unique,
	success: function(data, textstatus, request){
		if ( typeof( st_network ) != "undefined" ) { clearTimeout( st_network ) ; st_network = undeefined ; }
		try {
			eval(data) ;
			++ping_counter_req ;
		} catch(err) {
			// most likely internet disconnect or server response error will cause console to disconnect automatically
			// suppress error and let the console reconnect
			if ( !reconnect )
			{
				check_network( 719, undeefined, err ) ;
				write_debug( "719: "+data, err ) ;
			}
			else
			{
				stopit(0) ;
				st_reconnect = setTimeout(function(){ check_network( 716, undeefined, undeefined ) ; }, 3000) ;
			} return false ;
		}

		if ( typeof( request.responseText.length ) != "undefined" )
			ping_total_bytes_received += parseInt( request.responseText.length ) ;

		// hide mapp spinner for visual indication of request process connected
		if ( $('#div_mapp_spinner').is(":visible") )
			$('#div_mapp_spinner').hide() ;

		chatting_err_815 = undeefined ;
		if ( !stopped || ( stopped && reconnect ) )
		{
			stopped = 0 ; // reset it for disconnect situation
			reconnect = 0 ;
			reconnect_success() ;

			// reset it here for network status
			unique = unixtime() ;
			timestamp_st_requesting = unique ;

			if ( json_data.s == -1 )
			{
				dup = 1 ; // most likely another login at another location
				toggle_status( 3 ) ;
			}
			else if ( json_data.s == -2 )
			{
				dup = 1 ; // duplicate operator console. logout for security
				toggle_status(3) ;
			}
			else if ( json_data.s )
			{
				var json_length = ( typeof( json_data.r ) != "undefined" ) ? json_data.r.length : 0 ;
				for ( var c = 0; c < json_length; ++c )
				{
					var thisces = json_data.r[c]["ces"] ;
					var thisdeptid = json_data.r[c]["did"] ;
					var vis_token = ( typeof( json_data.r[c]["vis_token"] ) != "undefined" ) ? json_data.r[c]["vis_token"] : "" ;

					//var rupdated = ( typeof( depts_rtime_hash[thisdeptid] ) != "undefined" ) ? parseInt( json_data.r[c]["vup"] ) + parseInt( depts_rtime_hash[thisdeptid] ) : unique ;
					// ( unique <= rupdated ) - need to plan further

					if ( json_data.r[c]["op2op"] || ( typeof( op_depts_hash[thisdeptid] ) != "undefined" ) || ( vis_token == "grc" ) )
					{
						new_chat( json_data.r[c], unique ) ;
					}
				}

				init_chat_list( unique ) ;
				update_traffic_counter( pad( json_data.t, 2 ) ) ;

				if ( typeof( st_requesting ) != "undefined" ) { clearTimeout( st_requesting ) ; }
				st_requesting = setTimeout( "requesting()" , VARS_JS_REQUESTING * 1000 ) ;

				var end = microtime( true ) ;
				var diff = end - start ;

				check_network( diff, unique, json_data.pd ) ;

				// process chats, same as in chatting() function
				process_chat_messages( json_data.c, json_data.i ) ;
			}

			if ( $('#chat_footer_cell_message_board').length )
			{
				var iframe = ( mapp ) ? "iframe_mapp_message_board" : "iframe_message_board" ;
				if ( ( typeof( json_data.mgb ) != "undefined" ) && parseInt( json_data.mgb ) )
				{
					var message_board_is_open = ( document.getElementById(iframe).contentWindow.isop ) ? 1 : 0 ;
					if ( messageboard_id_mgb != parseInt( json_data.mgb ) )
					{
						messageboard_id_mgb = parseInt( json_data.mgb ) ;
						if ( message_board_pulse )
						{
							if ( !$('#img_message_board_icon').hasClass("img_message_board_icon_pulse") )
							{
								if ( !message_board_is_open || ( message_board_is_open && ( !document.getElementById(iframe).contentWindow.focused || !document.getElementById(iframe).contentWindow.input_is_focused() ) ) )
								{
									$('#td_message_board_icon').css({'opacity': 1}) ;
									$('#img_message_board_icon').addClass("img_message_board_icon_pulse") ;
								}
							}

							if ( !message_board_is_open || ( message_board_is_open && !document.getElementById(iframe).contentWindow.focused ) )
							{
								// DN for future version... need on/off ability
								// need a timeout or the DN will not show
								//setTimeout( function(){ dn_show( 'message_board', "message_board", "Message Board", "New Message", 900000 ) ; }, 1000 ) ;
							}
						}
						else
						{
							$('#td_message_board_icon').css({'opacity': 0.2}) ;
						}

						if ( message_board_sound )
							play_sound( 0, "message_board", "message_board_beep" ) ;
					}
					if ( message_board_is_open )
					{
						document.getElementById(iframe).contentWindow.fetch_messages(1) ;
					}
					else
					{
						//
					}
				}
				else if ( ( parseFloat( $('#td_message_board_icon').css("opacity") ) == 1 ) || ( parseFloat( $('#td_message_board_icon').css("opacity") ) == 0.2 ) )
				{
					// reset only happens if Message Board is opened and user interaction
				}
			}
		}
	},
	error:function (xhr, ajaxOptions, thrownError){
		$('#img_mapp_spinner').addClass('info_error') ; // visual indication network error

		if ( mapp && !$('#div_mapp_spinner').is(':visible') )
			$('#div_mapp_spinner').show().center() ;

		if ( typeof( chatting_err_815 ) == "undefined" )
		{
			chatting_err_815 = 1 ;
			update_network_log( "<tr id='div_network_his_"+network_counter+"' style='display: none'><td class='chat_info_td' colspan='3'>xhr: 815: "+xhr.status+"</td></tr>" ) ;
			setTimeout(function(){ requesting() ; }, 3000) ;
			write_debug( "815: "+xhr.status, "thrownError: "+xhr.responseText ) ;
		}
		else
		{
			// for Mobile Apps, some devices pauses network at pause/resume.  add some buffer so the disconnect message is only
			// displayed on actual network disconnect
			if ( mapp && chatting_err_815 && ( chatting_err_815 < 3 ) ) { ++chatting_err_815 ; }
			else
			{
				stopit(0) ;
				st_reconnect = setTimeout(function(){ check_network( 815+":"+xhr.status, undeefined, undeefined ) ; }, 3000) ;
			}
		}
	} });
}

function chatting()
{
	var json_data = new Object ;
	var chatting_query = get_chatting_query() ;

	if ( typeof( st_chatting ) != "undefined" ) { clearTimeout( st_chatting ) ; st_chatting = undeefined ; }

	if ( chatting_query )
	{
		var unique = unixtime() ;

		$.ajax({
		type: "GET",
		url: base_url+"/ajax/chat_op_requesting.php",
		data: chatting_query+"&pr="+proto+"&"+unique+"&",
		success: function(data){
			try {
				eval(data) ;
			} catch(err) {
				// if operator, the console will attempt to reconnect
				// if visitor, keep trying to send the data
				if ( !isop ) { visitor_reconnect() ; }
			}

			chatting_err_915 = undeefined ;
			if ( !stopped || ( stopped && reconnect ) )
			{
				stopped = 0 ; // reset it for disconnect situation
				reconnect = 0 ;

				if ( json_data.s )
				{
					// process chats
					process_chat_messages( json_data.c, json_data.i ) ;

					// only apply to visitor... for operator requesting() calls it for disconnection detection
					if ( !isop )
					{
						if ( typeof( st_chatting ) == "undefined" )
							st_chatting = setTimeout( "chatting()" , VARS_JS_REQUESTING * 1000 ) ;

						if ( ( $('#chat_processing').is(":visible") || $('#input_text').is(":disabled") ) && ( typeof( bid ) != "undefined" ) && bid )
						{
							$('#chat_processing').hide() ;
							$('#input_text').attr("disabled", false) ;
							if ( !mobile ) { $('#input_text').focus() ; }
						}
					}
				}
				else
				{
					// supress.  should never reach here.
					// chatting will trigger again at start_timer() function
				}
			}
			else
			{
				clearTimeout( st_chatting ) ; st_chatting = undeefined ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			if ( isop )
			{
				if ( typeof( chatting_err_915 ) == "undefined" )
				{
					chatting_err_915 = 1 ;
					update_network_log( "<tr id='div_network_his_"+network_counter+"' style='display: none'><td class='chat_info_td' colspan='3'>xhr: 915: "+xhr.status+"</td></tr>" ) ;
					setTimeout(function(){ chatting() ; }, 3000) ;
				}
				else
				{
					stopit(0) ;
					st_reconnect = setTimeout(function(){ check_network( 915+":"+xhr.status, undeefined, undeefined ) ; }, 1000) ;
				}
			}
			else { visitor_reconnect() ; }
		} });
		++c_chatting ;
	}
	else
	{
		if ( !isop ) { st_chatting = setTimeout( "chatting()" , VARS_JS_REQUESTING * 1000 ) ; }
	}
}
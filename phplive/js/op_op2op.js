function init_timer_op2op()
{
	var refresh_counter_temp = refresh_counter ;
	if ( typeof( si_refresh ) != "undefined" ) { clearInterval( si_refresh ) ; }
	si_refresh = setInterval(function(){
		if ( refresh_counter_temp <= 0 )
		{
			if ( !mapp_global_deptid && !$('#div_group_chat').is(":visible") )
			{
				// if mapp_global_deptid is set, don't reload because a logout confirm div is displayed.
				if ( typeof( si_refresh ) != "undefined" ) { clearInterval( si_refresh ) ; }
				do_refresh() ;
			}
		}
		else
		{
			if ( ( refresh_counter_temp == ( refresh_counter - 5 ) ) && $('#btn_refresh').prop('disabled') )
			{
				$('#btn_refresh').attr('disabled', false) ;
				$('#span_refresh_success').fadeOut("fast") ;
			}
			else if ( refresh_counter_temp <= 10 )
				$('#refresh_counter').fadeOut("fast").fadeIn("fast") ;

			$('#refresh_counter').html( pad( refresh_counter_temp, 1 ) ) ;
			--refresh_counter_temp ;
		}
	}, 1000) ;
}

function request_op2op( thedeptid, theopid )
{
	$('#btn_'+thedeptid+"_"+theopid).html( "requesting..." ).attr("disabled", "true") ;
	request_op2op_doit( thedeptid, theopid ) ;
}

function request_op2op_doit( thedeptid, theopid )
{
	if ( parent.total_op_depts )
	{
		var win_width = screen.width ;
		var win_height = screen.height ;
		var win_dim = win_width + " x " + win_height ;
		var json_data = new Object ;
		var unique = unixtime() ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_op2op.php",
		data: "action=op2op&deptid="+thedeptid+"&opid="+theopid+"&resolution="+win_dim+"&proto="+parent.phplive_proto+"&peer="+parent.phplive_peer_support,
		success: function(data){
			eval( data ) ;

			if ( json_data.status ) { setTimeout( function(){ parent.input_focus() ; parent.close_extra( parent.extra ) ; }, 3000 ) ; }
			else { do_alert( 0, "Error requesting operator chat.  Please refresh the console and try again." ) ; }
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Error requesting operator chat.  Please refresh the console and try again." ) ;
		} });
	}
	else
	{
		parent.close_extra( parent.extra ) ;
		parent.$('#no_dept').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
	}
}

function mapp_cancel_logout()
{
	$('#dept_'+mapp_global_deptid+'_status_1').prop('checked', true) ;
	$('#div_mapp_logout').hide() ;
	$('#div_all_departments').show() ;

	mapp_global_deptid = undeefined ;
}

function mapp_logout()
{
	parent.location.href = "../logout.php?action=logout&auto=1&mapp=1&menu=operator&"+unixtime() ;
}

function toggle_group_chat()
{
	if ( !$('#div_group_chat').is(":visible") )
	{
		var height = $(document).height() ;
		var group_chat_found = 0 ;

		for ( var thisces in parent.chats )
		{
			if ( parent.chats[thisces]["group_chat"] )
				group_chat_found = 1 ;
		}

		if ( group_chat_found )
		{
			do_alert( 0, "You are in a group chat.  Creating a new group chat is not available." ) ;
		}
		else
		{
			var max_height = height - 400 ;
			$('#div_group_chat_ops_list').css({'height': max_height+'px', 'overflow': 'auto'}) ;

			$('body').css({'overflow': 'hidden'}) ;
			$('#div_group_chat').show() ;
		}
	}
	else
	{
		$('#btn_create').attr( "disabled", false ).html( "Create Group Chat" ) ;
		$('#form_group_chat').trigger("reset") ;

		$('#div_group_chat').hide() ;
		$('body').css({'overflow': 'visible'}) ;

		$('#div_group_chat_ops').show() ;
		$('#div_group_chat_confirm').hide() ;
	}
}

function create_group_chat()
{
	var group_name = $('#group_name').val().trim() ;
	var deptid = parseInt( $('#deptid').val() ) ;
	var opids_total = 0 ;
	group_chat_opids = "" ; group_chat_op_names = "<ul>" ;

	$("#div_group_chat_body").find('*').each( function(){
		var div_name = this.id ;
		if ( div_name.indexOf("group_opid_") != -1 )
		{
			if ( $(this).prop('checked') )
			{
				++opids_total ;
				var opid = $(this).val() ;

				group_chat_opids += "opids[]="+opid+"&" ;
				group_chat_op_names += " <li> <b>"+$('#span_group_chat_op_name_'+opid).html()+"</b></li>" ;
			}
		}
	} ); group_chat_op_names += "</ul>" ;

	if ( !group_name )
	{
		$('#group_name').focus() ;
		do_alert( 0, "Group Name must be provided." ) ;
	}
	else if ( !deptid )
	{
		$('#deptid').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		do_alert( 0, "Please select a department." ) ;
	}
	else if ( opids_total < 2 )
	{
		if ( $('#div_no_ops').length )
			$('#div_no_ops').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;

		do_alert( 0, "At least 2 operators must be selected to start a group chat." ) ;
	}
	else if ( opids_total > group_chat_max_ops )
		do_alert( 0, "Max "+group_chat_max_ops+" operators can be in a group chat." ) ;
	else if ( group_chat_opids )
	{
		$('#div_group_chat_ul').html( group_chat_op_names ) ;

		$('#div_group_chat_ops').hide() ;
		$('#div_group_chat_confirm').show() ;
	}
}

function op2op_with_you( theopid )
{
	var is_in_op2op = 0 ;
	for ( var thisces in parent.chats )
	{
		if ( parent.chats[thisces]["op2op"] && ( ( parent.chats[thisces]["opid"] == theopid ) || ( parent.chats[thisces]["op2op"] == theopid ) ) && !parent.chats[thisces]["disconnected"] )
			is_in_op2op = 1 ;
	}
	return is_in_op2op ;
}

function populate_ops_op2op()
{
	var json_data = new Object ;
	var unique = unixtime() ;

	$.ajax({
	type: "POST",
	url: "../ajax/chat_actions_op_deptops.php",
	data: "action=deptops&"+unique,
	success: function(data){
		eval( data ) ;

		if ( json_data.status )
		{
			var ops_string = "" ;
			$('#deptid').empty().append( "<option value='0'>- select department -</option>" ) ;
			for ( var c = 0; c < json_data.departments.length; ++c )
			{
				var total_dept_ops = json_data.departments[c].operators.length ;
				var this_deptid = json_data.departments[c]["deptid"] ;
				var auto_offline_display = ( parent.auto_offline_string.indexOf( "-"+this_deptid+"-" ) != -1 ) ? "" : "display: none;" ;
				var dept_status = "offline" ;
				var dept_status_bullet = "online_grey.png" ;

				if ( ( json_data.departments[c]["online"] ) )
				{
					dept_status = "online" ;
					dept_status_bullet = "online_green.png" ;
				}

				ops_string += "<div class=\"chat_info_td_t\" style=\"margin-bottom: 1px; padding: 10px;\"><span style=\"cursor: pointer;\" onClick=\"toggle_dept_select("+this_deptid+")\"><img src=\"../themes/"+theme+"/"+dept_status_bullet+"\" width=\"12\" height=\"12\" border=\"0\" alt=\""+dept_status+"\" title=\""+dept_status+"\"> <span id=\"span_transfer_expand_"+this_deptid+"\">"+parent.arrow_symbol+"</span> "+json_data.departments[c]["name"]+"</span><div style=\""+auto_offline_display+" margin-top: 3px;\" class=\"info_error\" id=\"dept_"+this_deptid+"_auto_offline\">It is past regular chat support hours for this department.  Department is offline.</div></div><div id=\"div_transfer_ops_"+this_deptid+"\" style=\"display: none; margin-right: 15px; max-height: 250px; overflow: auto;\">" ;
				$('#deptid').append( "<option value='"+this_deptid+"'>"+json_data.departments[c]["name"]+"</option>" ) ;
				for ( var c2 = 0; c2 < total_dept_ops; ++c2 )
				{
					var id, btn_id ;
					var this_opid = json_data.departments[c].operators[c2]["opid"] ;
					var deptid = this_deptid ;
					var isonline = json_data.departments[c].operators[c2]["status"] ;
					var status = "offline" ;
					var status_js = "JavaScript:void(0)" ;
					var status_bullet = "online_grey.png" ; var bullet_class = "info_clear" ;
					var td_div = "chat_info_td_blank" ;
					var button = "" ;
					var chatting_with = ( nchats ) ? " - chatting with "+json_data.departments[c].operators[c2]["requests"]+" visitors" : "" ;
					var dept_offline_online_checked = "" ;
					var dept_status_string = this_opid+"_"+deptid ; global_op_dept_status[dept_status_string] = isonline ;
					var opacity = " opacity: 0.6; filter: alpha(opacity=60);" ;

					if ( isonline )
					{
						id = "op2op_"+this_opid ;
						btn_id = "btn_"+deptid+"_"+this_opid ;
						status = "online" ;
						status_js = "request_op2op("+deptid+","+this_opid+")" ;
						status_bullet = "online_green.png" ; bullet_class = "info_good" ;
						dept_offline_online_checked = "checked" ;
						opacity = "" ;
						
						++total_depts_online ;
						if ( this_opid == opid )
							++total_depts_online_op ;

						var is_in_op2op_with_you = op2op_with_you( this_opid ) ;
						if ( ( this_opid != parent.isop ) && !is_in_op2op_with_you )
							button = "<button type=\"button\" id=\""+btn_id+"\" onClick=\""+status_js+"\">request chat</button> " ;
						else if ( ( this_opid != parent.isop ) && is_in_op2op_with_you )
							button = "<button type=\"button\" disabled>request sent</button> " ;
					}

					var dept_offline_offline_checked = ( !dept_offline_online_checked || !auto_offline_display ) ? "checked" : "" ;
					var profile_image = "<img src=\""+$("#img_profile_"+this_opid).attr('src')+"\" width=\"32\" height=\"32\" border=0 class=\"profile_pic_img\" style=\"border-radius: 50%;\">" ;

					var op_name = ( this_opid == parent.isop ) ? json_data.departments[c].operators[c2]["name"]+" (You)" : json_data.departments[c].operators[c2]["name"] ;
					var dept_offline_btn = "" ;
					if ( ( this_opid == parent.isop ) && parent.dept_offline && ( parent.total_op_depts > 1 ) )
					{
						dept_offline_btn = "<div class=\""+td_div+" chat_info_td_traffic\" style=\"margin-top: 5px;\">"+
								"<span class=\"info_good\" onClick=\"toggle_dept_status("+this_opid+","+deptid+", 1)\" style=\"cursor: pointer;\"><input type=\"radio\" name=\"dept_"+deptid+"_status\" id=\"dept_"+deptid+"_status_1\" "+dept_offline_online_checked+"> online</span>"+
								" &nbsp; "+
								"<span class=\"info_error\" onClick=\"toggle_dept_status("+this_opid+","+deptid+", 0)\" style=\"cursor: pointer;\"><input type=\"radio\" name=\"dept_"+deptid+"_status\" id=\"dept_"+deptid+"_status_0\" "+dept_offline_offline_checked+"> offline</span>"+
							"</div>" ;
					}

					ops_string += "<div class=\""+td_div+" chat_info_td_traffic\" style=\"padding-left: 15px;"+opacity+"\">"+
						"<table cellspacing=0 cellpadding=5 border=0>"+
						"<tr>"+
							"<td width=\"12\"><img src=\"../themes/"+theme+"/"+status_bullet+"\" width=\"12\" height=\"12\" border=\"0\" class=\""+bullet_class+"\" alt=\""+status+"\" title=\""+status+"\"></td>"+
							"<td width=\"32\">"+profile_image+"</td>"+
							"<td>"+button+"<span id=\""+id+"\"> "+op_name+chatting_with+"</span>"+dept_offline_btn+"</td>"+
						"</tr>"+
						"</table>"+
					"</div>" ;
				}
				if ( !total_dept_ops ) { ops_string += "<div class=\"chat_info_td_blank chat_info_td_traffic\" style=\"padding: 10px; padding-left: 25px;\">Blank results.</div>"  ; }
				ops_string += "</div>" ; // close department ops list

				ops_string += "<div style=\"padding-top: 15px;\"></div>" ;
			}
			$('#canned_body').html( ops_string ) ;

			if ( total_depts_online_op && ( parent.$('#div_dept_offline_all_notice').is(":visible") || parent.$('#chat_status_offline').is(":visible") ) )
			{
				// need to hide div first so it processes the status.  there is div visible check in function
				// toggle_status to make sure it does not duplicate process causing multiple DB entries
				parent.$('#div_dept_offline_all_notice').hide() ;

				var t_chats = parent.total_chats() ;
				if ( !t_chats )
					parent.toggle_status(0) ;
			}
			else if ( !total_depts_online_op && !parent.$('#div_dept_offline_all_notice').is(":visible") )
				parent.toggle_status(1) ;
			else if ( ( total_depts_online_op == parent.total_op_depts ) && parent.$('#div_dept_offline_some_notice').is(":visible") )
				parent.$('#div_dept_offline_some_notice').hide() ;
			else if ( parent.dept_offline && ( total_depts_online_op != parent.total_op_depts ) && !parent.$('#div_dept_offline_some_notice').is(":visible") )
			{
				parent.$('#div_dept_offline_some_notice').show() ;
			}

			if ( global_deptid || ( json_data.departments.length == 1 ) )
			{
				if ( json_data.departments.length == 1 )
					global_deptid = json_data.departments[0]["deptid"] ;

				setTimeout( function() { toggle_dept_select(global_deptid) ; }, 1000 ) ;
			}
		}
	},
	error:function (xhr, ajaxOptions, thrownError){
		do_alert( 0, "Error retrieving operator list.  Please refresh the console and try again." ) ;
	} });
}

var global_transfer_expand_top ;
function toggle_dept_select( thedeptid )
{
	global_deptid = thedeptid ;
	var deptid_was_visible = 0 ;

	$('#canned_body').find('*').each( function(){
		var div_name = this.id ;
		if ( div_name.indexOf( "div_transfer_ops_" ) != -1 )
		{
			var matches = div_name.match( /div_transfer_ops_(\d+)$/ ) ;
			var this_deptid = ( typeof( matches[1] ) != "undefined" ) ? matches[1] : 0 ;
			if ( this_deptid && $(this).is(":visible") )
			{
				deptid_was_visible = this_deptid ;
				$('#span_transfer_expand_'+this_deptid).html( parent.arrow_symbol ) ;
				$(this).hide() ;
			}
		}
	} );

	if ( deptid_was_visible != thedeptid )
	{
		$('#span_transfer_expand_'+thedeptid).html( parent.arrow_down_symbol ) ;
		$('#div_transfer_ops_'+thedeptid).fadeIn( "fast", function() {
			$('#div_transfer_ops_'+thedeptid).scrollTop(0) ;

			var scrollto = $('#canned_body').scrollTop() + $('#div_transfer_ops_'+thedeptid).position().top - $('#canned_body').height()/2 + $('#div_transfer_ops_'+thedeptid).height()/2 ;
			if ( scrollto > 50 )
			{
				$('#canned_body').animate({
					scrollTop: scrollto
				}, 400) ;
			}
		});
	}
	else { global_deptid = 0 ; }
}

function toggle_dept_status( theopid, thedeptid, thestatus )
{
	var dept_status_string = theopid+"_"+thedeptid ;
	var mapp_total_depts_online_op_prep = total_depts_online_op - 1 ;
	mapp_global_deptid = thedeptid ;

	if ( thestatus == global_op_dept_status[dept_status_string] )
		return false ;
	else if ( !thestatus && mapp && ( mapp_total_depts_online_op_prep <= 0 ) )
	{
		$('#div_all_departments').hide() ;
		$('#div_mapp_logout').show() ;
		return false ;
	}
	else if ( thestatus && parent.$('#div_automatic_offline').is(':visible') )
	{
		$('#dept_'+thedeptid+'_status_0').prop('checked', true) ;
		parent.toggle_status( 1000 ) ;
		parent.toggle_extra( 'op2op', '', '', 'Operators' ) ;
		return false ;
	}
	else if ( parent.auto_offline_string.indexOf( "-"+thedeptid+"-" ) != -1 )
	{
		// automatic offline is active
		$('#dept_'+thedeptid+'_status_0').prop('checked', true) ;
		$('#dept_'+thedeptid+'_auto_offline').fadeIn("fast").fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ;
		return false ;
	}
	else
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		global_op_dept_status[dept_status_string] = thestatus ;

		if ( !$('#dept_'+thedeptid+'_status_'+thestatus).prop('checked') )
			$('#dept_'+thedeptid+'_status_'+thestatus).prop('checked', true) ;

		$.ajax({
		type: "POST",
		url: "../ajax/chat_actions_op_status.php",
		data: "action=update_dept_offline&deptid="+thedeptid+"&status="+thestatus+"&"+unique,
		success: function(data){
			eval( data ) ;
			if ( json_data.status )
			{
				if ( !thestatus )
				{
					--total_depts_online_op ;
					if ( total_depts_online_op <= 0 )
					{
						//alert( total_depts_online_op ) ;
						parent.toggle_status(1) ;
					}
				}

				setTimeout( function(){ location.href = "op_op2op.php?action=reload&mapp="+mapp ; }, 100 ) ;
			}
			else
				do_alert( 0, json_data.error ) ;
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Connection error.  Please refresh the console and try again." ) ;
		} });
	}
}

function create_group_chat_doit( theopids )
{
	var group_name = $('#group_name').val().trim() ;
	var deptid = parseInt( $('#deptid').val() ) ;

	if ( !deptid )
	{
		$('#deptid').fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1).fadeTo("fast", .1).fadeTo("fast", 1) ;
		do_alert( 0, "Please select a department." ) ;
	}
	else
	{
		var json_data = new Object ;
		var unique = unixtime() ;

		$('#btn_create').attr( "disabled", true ).html( "Creating..." ) ;

		//parent.group_chat_opids = theopids ;

		$.ajax({
		type: "POST",
		url: "./op_op2op.php",
		data: "action=create_group_chat&deptid="+deptid+"&u="+unique+"&group_name="+encodeURIComponent( group_name )+"&"+theopids,
		success: function(data){
			eval( data ) ;
			if ( json_data.status )
			{
				parent.do_alert( 1, "Group Chat Created" ) ;
				toggle_group_chat() ;
				setTimeout( function(){ parent.input_focus() ; parent.close_extra( parent.extra ) ; }, 3000 ) ;
			}
			else
			{
				do_alert( 0, json_data.error ) ;
			}
		},
		error:function (xhr, ajaxOptions, thrownError){
			do_alert( 0, "Connection error.  Please refresh the console and try again." ) ;
		} });
	}
}
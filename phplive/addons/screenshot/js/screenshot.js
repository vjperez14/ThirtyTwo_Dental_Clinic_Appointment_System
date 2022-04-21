function screenshot_preview( thedata )
{
	if ( typeof( thedata ) == "undefined" )
	{
		do_alert( 0, "Website screenshot not available." ) ;
		screenshot_delete() ;
	}
	else if ( thedata == "x_require" )
	{
		do_alert( 0, "Screenshot not available." ) ;
		screenshot_delete() ;
	}
	else if ( thedata == "x_nonsupport" )
	{
		do_alert( 0, "Screenshot not available.  Please try again." ) ;
		screenshot_delete() ;
	}
	else if ( thedata == "x_timedout" )
	{
		do_alert( 0, "Screenshot has timed out." ) ;
		screenshot_delete() ;
	}
	else
	{
		$('#scr_data').val( thedata ) ;

		$('#div_screenshot_loading').hide() ;
		var div_content = '<div class="info_box" style="text-align: center; border-bottom-left-radius: 0px 0px; border-bottom-right-radius: 0px 0px;">Screenshot</div><div style="width: 100px; max-height: 200px; overflow-y: hidden;"><img src="'+thedata+'" id="img_screenshot" style="max-width: 100px; border-top-left-radius: 0px 0px; border-top-right-radius: 0px 0px;" border=0></div><div style="text-align: right;"><img src="'+base_url_full+'/themes/initiate/trashcan.png" width="16" height="16" border="0" alt="" style="cursor: pointer;" onClick="screenshot_delete()" id="img_screenshot_delete"></div>' ;
		$('#div_screenshot_image').html( div_content ).show( "fast", function() {
			$("#request_body").animate({ scrollTop: $(document).height() }, "slow") ;
		});
	}
}

function toggle_screenshot( theforce_close )
{
	if ( $('#div_screenshot_confirm').is(':visible') || theforce_close )
		$('#div_screenshot_confirm').fadeOut("fast") ;
	else
	{
		if ( !browser_promise )
		{
			$('#div_screenshot_btn').hide() ;
			$('#div_screenshot_nosupport').show() ;
		}

		if ( ( typeof( ces ) != "undefined" ) && !chats[ces]["disconnected"] )
		{
			if ( !isop || mapp )
				$('#div_screenshot_confirm').center() ;
			else
				position_send_trans() ;

			var top = parseInt( $('#div_screenshot_confirm').css('top') ) ;
			var top_start = top + 100 ;

			$('#div_screenshot_confirm').css({'top': top_start}) ;

			$('#div_screenshot_confirm').fadeIn({queue: false, duration: 'fast'}) ;
			$('#div_screenshot_confirm').animate({ top: top }, 'fast') ;
		}
		else
			do_alert( 0, CHAT_NOTIFY_DISCONNECT ) ;
	}
}

function screenshot_take()
{
	if ( embed )
	{
		if ( ( typeof( preview ) != "undefined" ) && preview )
		{
			do_alert( 0, "Screenshot is not available for interface preview." ) ;
			return false ;
		}

		$('#div_screenshot_btn').hide() ;
		$('#div_screenshot_loading').show() ;
		parent_send_message( "screenshot", deptid ) ;
	}
}

function screenshot_delete()
{
	// can only happen on chat request window
	if ( typeof( isop ) == "undefined" )
	{
		$('#scr_data').val( "" ) ;
		$('#div_screenshot_btn').html( '<img src="'+base_url_full+'/themes/initiate/screenshot.png" width="16" height="16" border="0">' ).show() ;
		$('#div_screenshot_image').empty() ;
		$('#div_screenshot_loading').hide() ;
	}
}

function screenshot_send( thedata, theonpage )
{
	if ( thedata == "x_require" )
	{
		do_alert( 0, "Screenshot not available." ) ;
		$('#div_screenshot_loading').hide() ;
		$('#div_screenshot_btn').show() ;
		toggle_screenshot(1) ;
	}
	else
	{
		var json_data = new Object ;
		var unique = unixtime() ;
		var filename = "screenshot_"+ces+"_"+randomstring(6) ;
		if ( theonpage ) { theonpage = phplive_base64.decode( theonpage ).replace( /^hphp/, "http" ) ; }

		var data = new FormData() ;
		data.append( "image", thedata ) ;
		data.append( "ces", ces ) ;
		data.append( "isop", isop ) ;
		data.append( "salt", salt ) ;
		data.append( "filename", filename ) ;

		// AJAX request
		$.ajax({
			type: "POST",
			url: base_url_full+"/addons/screenshot/ajax/send.php",
			contentType: false,
			processData: false,
			data: data,
			success: function(data){

				$('#div_screenshot_loading').hide() ;
				$('#div_screenshot_btn').show() ;

				try {
					eval(data) ;
				} catch(err) {
					do_alert( 0, err ) ;
					return false ;
				}

				if ( json_data.status )
				{
					filename += ".PNG" ;

					var screenshot_path = ( json_data.default_path ) ? base_url_full+"/web/file_attach/screenshots/"+filename : base_url_full+"/pics/space.gif" ;
					add_text_prepare( 1, "screenshot:"+screenshot_path+":name:"+filename+":url:"+theonpage ) ;
				}
				else
				{
					do_alert( 0, json_data.error ) ;
				}
				toggle_screenshot(1) ;
			}
		});
	}
}
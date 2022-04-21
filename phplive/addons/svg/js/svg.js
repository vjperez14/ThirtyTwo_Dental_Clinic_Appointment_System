var icon_online_svg_outer ;
var icon_online_svg_inner ;
var icon_online_svg_dots ;

var icon_offline_svg_outer ;
var icon_offline_svg_inner ;
var icon_offline_svg_dots ;

var icon_online_text_outer ;
var icon_online_text_inner ;
var icon_online_text_dots ;
var icon_online_text ;

var icon_offline_text_outer ;
var icon_offline_text_inner ;
var icon_offline_text_dots ;
var icon_offline_text ;

function svg_init()
{
	svg_update( 'online' ) ;
	svg_update_text( 'online' ) ;
	$('.svg_online_outer').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			//color.toHexString() ;
			$('#span_online_cancel').show() ;
			svg_update( 'online' ) ;
		}
	});
	$('.svg_online_inner').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_online_cancel').show() ;
			svg_update( 'online' ) ;
		}
	});
	$('.svg_online_dots').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_online_cancel').show() ;
			svg_update( 'online' ) ;
		}
	});
	icon_online_svg_outer = $('.svg_online_outer').val() ;
	icon_online_svg_inner = $('.svg_online_inner').val() ;
	icon_online_svg_dots = $('.svg_online_dots').val() ;

	svg_update( 'offline' ) ;
	svg_update_text( 'offline' )
	$('.svg_offline_outer').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_offline_cancel').show() ;
			svg_update( 'offline' ) ;
		}
	});
	$('.svg_offline_inner').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_offline_cancel').show() ;
			svg_update( 'offline' ) ;
		}
	});
	$('.svg_offline_dots').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_offline_cancel').show() ;
			svg_update( 'offline' ) ;
		}
	});
	icon_offline_svg_outer = $('.svg_offline_outer').val() ;
	icon_offline_svg_inner = $('.svg_offline_inner').val() ;
	icon_offline_svg_dots = $('.svg_offline_dots').val() ;

	$('.text_online_outer').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_online_text_cancel').show() ;
			svg_update_text( 'online' ) ;
		}
	});
	$('.text_online_inner').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_online_text_cancel').show() ;
			svg_update_text( 'online' ) ;
		}
	});
	$('.text_online_dots').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_online_text_cancel').show() ;
			svg_update_text( 'online' ) ;
		}
	});
	icon_online_text_outer = $('.text_online_outer').val() ;
	icon_online_text_inner = $('.text_online_inner').val() ;
	icon_online_text_dots = $('.text_online_dots').val() ;
	icon_online_text = $('#input_text_online').val().trim() ;

	$('.text_offline_outer').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_offline_text_cancel').show() ;
			svg_update_text( 'offline' ) ;
		}
	});
	$('.text_offline_inner').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_offline_text_cancel').show() ;
			svg_update_text( 'offline' ) ;
		}
	});
	$('.text_offline_dots').spectrum({
		preferredFormat: "hex",
		showInput: true,
		showInitial: true,
		replacerClassName: 'info_neutral',
		change: function(color) {
			$('#span_offline_text_cancel').show() ;
			svg_update_text( 'offline' ) ;
		}
	});
	icon_offline_text_outer = $('.text_offline_outer').val() ;
	icon_offline_text_inner = $('.text_offline_inner').val() ;
	icon_offline_text_dots = $('.text_offline_dots').val() ;
	icon_offline_text = $('#input_text_offline').val().trim() ;
}

function svg_update( theicon )
{
	var outer = $('.svg_'+theicon+'_outer').val() ;
	var inner = $('.svg_'+theicon+'_inner').val() ;
	var dots = $('.svg_'+theicon+'_dots').val() ;

	var image_online = '<svg id="svg_online_" style="filter: drop-shadow(2px 2px 5px rgba(0,0,0,0.2));" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 640 640" width="60" height="60"><defs><path style="fill: '+outer+' !important;" d="M628.58 320C628.58 490.64 490.31 629.18 320 629.18C149.69 629.18 11.42 490.64 11.42 320C11.42 149.36 149.69 10.82 320 10.82C490.31 10.82 628.58 149.36 628.58 320Z" id="i8VVhIQhAy"></path><path style="fill: '+inner+' !important;" d="M154.39 327.31C145.47 242.82 213.28 166.39 305.86 156.61C398.44 146.83 480.72 207.38 489.65 291.88C498.58 376.38 430.78 452.8 338.2 462.58C315.71 464.96 293.83 463.18 273.46 457.94C269.61 461.08 250.36 476.82 215.72 505.14L217.78 432.34C178.58 391.28 157.45 356.27 154.39 327.31Z" id="c2VPbzTLxw"></path><path style="fill: '+dots+' !important;" d="M259.47 304.13C259.47 316.88 249.12 327.24 236.37 327.24C223.62 327.24 213.27 316.88 213.27 304.13C213.27 291.38 223.62 281.03 236.37 281.03C249.12 281.03 259.47 291.38 259.47 304.13Z" id="bpomRosYj"></path><path style="fill: '+dots+' !important;" d="M345.13 304.13C345.13 316.88 334.77 327.24 322.02 327.24C309.27 327.24 298.92 316.88 298.92 304.13C298.92 291.38 309.27 281.03 322.02 281.03C334.77 281.03 345.13 291.38 345.13 304.13Z" id="cS831ETxc"></path><path style="fill: '+dots+' !important;" d="M431.94 304.13C431.94 316.88 421.59 327.24 408.84 327.24C396.09 327.24 385.73 316.88 385.73 304.13C385.73 291.38 396.09 281.03 408.84 281.03C421.59 281.03 431.94 291.38 431.94 304.13Z" id="a1W8EpizTc"></path></defs><g><g><g><use xlink:href="#i8VVhIQhAy" opacity="1" fill="'+outer+'" fill-opacity="1"></use></g><g><use xlink:href="#c2VPbzTLxw" opacity="1" fill="'+inner+'" fill-opacity="1"></use></g><g><use xlink:href="#bpomRosYj" opacity="1" fill="'+dots+'" fill-opacity="1"></use></g><g><use xlink:href="#cS831ETxc" opacity="1" fill="'+dots+'" fill-opacity="1"></use></g><g><use xlink:href="#a1W8EpizTc" opacity="1" fill="'+dots+'" fill-opacity="1"></use></g></g></g></svg>' ;

	var image_offline = '<svg id="svg_offline_" style="filter: drop-shadow(2px 2px 5px rgba(0,0,0,0.2));" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 640 640" width="60" height="60"><defs><path style="fill: '+outer+' !important;" d="M628.58 320C628.58 490.64 490.31 629.18 320 629.18C149.69 629.18 11.42 490.64 11.42 320C11.42 149.36 149.69 10.82 320 10.82C490.31 10.82 628.58 149.36 628.58 320Z" id="akAxA96t8"></path><path style="fill: '+inner+' !important;" d="M154.39 327.31C145.47 242.82 213.28 166.39 305.86 156.61C398.44 146.83 480.72 207.38 489.65 291.88C498.58 376.38 430.78 452.8 338.2 462.58C315.71 464.96 293.83 463.18 273.46 457.94C269.61 461.08 250.36 476.82 215.72 505.14L217.78 432.34C178.58 391.28 157.45 356.27 154.39 327.31Z" id="b84wtcrNnc"></path><path style="fill: '+dots+' !important;" d="M247.91 360.53L247.91 250.94L398.14 250.94L398.14 360.53L247.91 360.53ZM322.07 324L262 279.02L262 345.92L384.05 345.92L384.05 277.3L322.26 323.57L322.07 324ZM321.88 302.77L371.58 265.55L272.18 265.55L321.88 302.77Z" id="a4ikl1BvW"></path></defs><g><g><g><use xlink:href="#akAxA96t8" opacity="1" fill="'+outer+'" fill-opacity="1"></use></g><g><use xlink:href="#b84wtcrNnc" opacity="1" fill="'+inner+'" fill-opacity="1"></use></g><g><use xlink:href="#a4ikl1BvW" opacity="1" fill="'+dots+'" fill-opacity="1"></use></g></g></g></svg>' ;

	if ( theicon == "online" )
		$('#svg_'+theicon+'_image').html( image_online ) ;
	else
		$('#svg_'+theicon+'_image').html( image_offline ) ;
}

function svg_update_text( theicon )
{
	var outer = $('.text_'+theicon+'_outer').val() ;
	var inner = $('.text_'+theicon+'_inner').val() ;
	var dots = $('.text_'+theicon+'_dots').val() ;
	var text = $('#input_text_'+theicon).val().trim() ;

	var image_online = '<span style="padding: 10px !important; text-decoration: none !important; display: inline-block !important; background: '+inner+' !important; color: '+dots+' !important; border: 1px solid '+outer+' !important; border-radius: 10px !important; -webkit-box-shadow: 2px 2px 5px rgba(0,0,0,0.2) !important; -moz-box-shadow: 2px 2px 5px rgba(0,0,0,0.2) !important; box-shadow: 2px 2px 5px rgba(0,0,0,0.2) !important;" id="phplive_text"><table style="border: 0px !important; padding: 0px !important; margin: 0px !important;"><tr><td style="border: 0px !important; padding-right: 0px !important; padding-left: 0px !important; padding-top: 0px !important; padding-bottom: 0px !important; color: '+dots+' !important;"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 640 640" width="20" height="20"><defs><path style="fill: '+dots+' !important;" d="M51.72 294.49C51.72 159.02 172.06 49.19 320.51 49.19C468.95 49.19 589.29 159 589.29 294.49C589.29 429.98 468.97 539.81 320.52 539.81C284.46 539.81 250.05 533.32 218.63 521.59C212 525.93 178.85 547.66 119.16 586.77L134.62 471.67C79.35 399.99 51.72 340.93 51.72 294.49Z" id="biq1Fbxfy"></path></defs><g><g><g><g><filter id="shadow11189699" x="42.72" y="40.19" width="556.58" height="556.58" filterUnits="userSpaceOnUse" primitiveUnits="userSpaceOnUse"><feFlood></feFlood><feComposite in2="SourceAlpha" operator="in"></feComposite></filter><path style="fill: '+dots+' !important;" d="M51.72 294.49C51.72 159.02 172.06 49.19 320.51 49.19C468.95 49.19 589.29 159 589.29 294.49C589.29 429.98 468.97 539.81 320.52 539.81C284.46 539.81 250.05 533.32 218.63 521.59C212 525.93 178.85 547.66 119.16 586.77L134.62 471.67C79.35 399.99 51.72 340.93 51.72 294.49Z" id="b17d8ou18z" fill="white" fill-opacity="1" filter="url(#shadow11189699)"></path></g><use xlink:href="#biq1Fbxfy" opacity="1" fill="'+dots+'" fill-opacity="1"></use></g></g></g></svg></td><td style="border: 0px !important; padding-right: 0px !important; padding-left: 3px !important; padding-top: 0px !important; padding-bottom: 0px !important; color: '+dots+' !important;" id="span_text_text_online">'+text+'</td></tr></table></span>' ;

	var image_offline = '<span style="padding: 10px !important; text-decoration: none !important; display: inline-block !important; background: '+inner+' !important; color: '+dots+' !important; border: 1px solid '+outer+' !important; border-radius: 10px !important; -webkit-box-shadow: 2px 2px 5px rgba(0,0,0,0.2) !important; -moz-box-shadow: 2px 2px 5px rgba(0,0,0,0.2) !important; box-shadow: 2px 2px 5px rgba(0,0,0,0.2) !important;" id="phplive_text"><table style="border: 0px !important; padding: 0px !important; margin: 0px !important;"><tr><td style="border: 0px !important; padding-right: 0px !important; padding-left: 0px !important; padding-top: 0px !important; padding-bottom: 0px !important; color: '+dots+' !important;"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid meet" viewBox="0 0 640 640" width="20" height="20"><defs><path style="fill: '+dots+' !important;" d="M51.21 554.44L51.21 71.41L588.79 71.41L588.79 554.44L51.21 554.44ZM316.58 393.43L101.61 195.17L101.61 490.04L538.39 490.04L538.39 187.61L317.27 391.55L316.58 393.43ZM315.9 299.85L493.76 135.82L138.04 135.82L315.9 299.85Z" id="c13HOhMVvH"></path></defs><g><g><g><g><filter id="shadow2371485" x="42.21" y="62.41" width="556.58" height="502.03" filterUnits="userSpaceOnUse" primitiveUnits="userSpaceOnUse"><feFlood></feFlood><feComposite in2="SourceAlpha" operator="in"></feComposite></filter><path style="fill: '+dots+' !important;" d="M51.21 554.44L51.21 71.41L588.79 71.41L588.79 554.44L51.21 554.44ZM316.58 393.43L101.61 195.17L101.61 490.04L538.39 490.04L538.39 187.61L317.27 391.55L316.58 393.43ZM315.9 299.85L493.76 135.82L138.04 135.82L315.9 299.85Z" id="aQ6F24gjr" fill="white" fill-opacity="1" filter="url(#shadow2371485)"></path></g><use xlink:href="#c13HOhMVvH" opacity="1" fill="'+dots+'" fill-opacity="1"></use></g></g></g></svg></td><td style="border: 0px !important; padding-right: 0px !important; padding-left: 3px !important; padding-top: 0px !important; padding-bottom: 0px !important; color: '+dots+' !important;" id="span_text_text_offline">'+text+'</td></tr></table></span>' ;

	if ( theicon == "online" )
		$('#text_'+theicon+'_image').html( image_online ) ;
	else
		$('#text_'+theicon+'_image').html( image_offline ) ;
}

var svg_submit_status = false ;
function svg_submit( theicon, thetype )
{
	var json_data = new Object ;
	var unique = unixtime() ;

	var text = "" ;
	var outer = "" ; var inner = "" ; var dots = "" ;

	if ( thetype == "svg" )
	{
		outer = $('.svg_'+theicon+'_outer').val() ;
		inner = $('.svg_'+theicon+'_inner').val() ;
		dots = $('.svg_'+theicon+'_dots').val() ;
	}
	else if ( thetype == "text" )
	{
		text = encodeURIComponent( $('#input_text_'+theicon).val().trim() ) ;
		outer = $('.text_'+theicon+'_outer').val() ;
		inner = $('.text_'+theicon+'_inner').val() ;
		dots = $('.text_'+theicon+'_dots').val() ;
	}

	$("input[type=radio]").attr('disabled', true) ;

	svg_color_picker_disable( theicon, true ) ;
	$('#btn_'+thetype+'_'+theicon).attr( "disabled", true ) ;

	if ( !svg_submit_status )
	{
		svg_submit_status = true ;
		$.ajax({
			type: "POST",
			url: "../ajax/setup_actions_.php",
			data: "action=update_svg&deptid="+deptid+"&icon="+theicon+"&t="+thetype+"&o="+outer+"&i="+inner+"&d="+dots+"&text="+text+"&"+unique,
			success: function(data){
				$("input[type=radio]").attr('disabled', false) ;
				try {
					eval(data) ;
				} catch(err) {
					json_data.status = 0 ;
					json_data.error = "Server sent an unexpected response.  Please try again." ;
				}

				svg_submit_status = false ;
				svg_color_picker_disable( theicon, false ) ;

				$('#btn_'+thetype+'_'+theicon).attr( "disabled", false ) ;
				$('#div_loading_'+theicon).hide() ;

				if ( json_data.status )
				{
					location.href = "icons.php?action=success&deptid="+deptid+"&"+unique ;
				}
				else
				{
					do_alert( 0, json_data.error ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				svg_submit_status = false ;
				svg_color_picker_disable( theicon, false ) ;
				do_alert( 0, "Connection error.  Please refresh the page and try again." ) ;
			}
		});
	}
}

function svg_cancel( theicon )
{
	$('#span_'+theicon+'_cancel').hide() ;

	$('.svg_'+theicon+'_outer').val( eval("icon_"+theicon+"_svg_outer") ) ;
	$('.svg_'+theicon+'_inner').val( eval("icon_"+theicon+"_svg_inner") ) ;
	$('.svg_'+theicon+'_dots').val( eval("icon_"+theicon+"_svg_dots") ) ;

	$('.svg_'+theicon+'_outer').spectrum("set", eval("icon_"+theicon+"_svg_outer")) ;
	$('.svg_'+theicon+'_inner').spectrum("set", eval("icon_"+theicon+"_svg_inner")) ;
	$('.svg_'+theicon+'_dots').spectrum("set", eval("icon_"+theicon+"_svg_dots")) ;

	svg_update( theicon ) ;
}

function text_cancel( theicon )
{
	$('#span_'+theicon+'_text_cancel').hide() ;

	$('.text_'+theicon+'_outer').val( eval("icon_"+theicon+"_text_outer") ) ;
	$('.text_'+theicon+'_inner').val( eval("icon_"+theicon+"_text_inner") ) ;
	$('.text_'+theicon+'_dots').val( eval("icon_"+theicon+"_text_dots") ) ;

	$('.text_'+theicon+'_outer').spectrum("set", eval("icon_"+theicon+"_text_outer")) ;
	$('.text_'+theicon+'_inner').spectrum("set", eval("icon_"+theicon+"_text_inner")) ;
	$('.text_'+theicon+'_dots').spectrum("set", eval("icon_"+theicon+"_text_dots")) ;
	$('#input_text_'+theicon).val( eval("icon_"+theicon+"_text") ) ;

	svg_update_text( theicon ) ;
}

function svg_color_picker_disable( theicon, theflag )
{
	if ( theflag )
	{
		$('.svg_'+theicon+'_outer').spectrum("disable") ;
		$('.svg_'+theicon+'_inner').spectrum("disable") ;
		$('.svg_'+theicon+'_dots').spectrum("disable") ;
	}
	else
	{
		$('.svg_'+theicon+'_outer').spectrum("enable") ;
		$('.svg_'+theicon+'_inner').spectrum("enable") ;
		$('.svg_'+theicon+'_dots').spectrum("enable") ;
	}
}

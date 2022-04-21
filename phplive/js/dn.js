/***************************************/
//
//
// PHP Live! Support
//
// https://www.phplivesupport.com
//
/***************************************/

var dn_si ;
var dn_his = new Object ;
function dn_pre_request()
{
	dn_request() ;

	if ( typeof( dn_si ) != "undefined" ) ;
		clearInterval( dn_si ) ;

	dn_si = setInterval(function(){ dn_check_si() }, 300) ;
}

function dn_check_browser()
{
	if ( navigator.userAgent.toLowerCase().indexOf('chrome') > -1 )
		return "chrome" ;
	else if ( navigator.userAgent.toLowerCase().indexOf('firefox') > -1 )
		return "firefox" ;
	else
		return "null" ;
}

function dn_check_si()
{
	var dn = dn_check() ;
	if ( parseInt( dn ) == 2 )
	{
		if ( typeof( dn_si ) != "undefined" )
			clearInterval( dn_si ) ;

		$('#dn_request').hide() ;
		$('#dn_disabled').show() ;
	}
	else if ( !parseInt( dn ) && ( parseInt( dn ) != -1 ) )
	{
		if ( typeof( dn_si ) != "undefined" )
			clearInterval( dn_si ) ;

		$('#dn_request').hide() ;
		$('#dn_enabled').show() ;
		if ( dn_enabled )
			$('#dn_enabled_on').show() ;
		else
			$('#dn_enabled_off').show() ;
	}
}

function dn_request()
{
	var dn = dn_check() ;
	if ( parseInt( dn ) == 2 )
	{
		$('#dn_request').hide() ;
		$('#dn_disabled').show() ;
	}
	else if ( !parseInt( dn ) && ( parseInt( dn ) != -1 ) )
		do_alert( 1, "Desktop notification already enabled." ) ;
	else if ( ( "Notification" in window ) )
	{
		Notification.requestPermission(function (permission) {
			if( !( 'permission' in Notification ) )
			{
				Notification.permission = permission ;
			}

			if ( ( typeof( Notification.permission ) == "undefined" ) || ( Notification.permission != "granted" ) )
			{
				if ( ( dn_browser == "firefox" ) && !proto.match( /https/i ) )
				{
					$('#dn_request').hide() ;
					$('#dn_insecure').show() ;
				}
				else
				{
					$('#dn_request').hide() ;
					$('#dn_disabled').show() ;
				}
			}
		});
	}
}

function dn_check()
{
	if ( ( typeof( wp ) != "undefined" ) && wp ) { return 0 ; }
	// -1 - not supported, 0 - allowed, 1 - not allowed, 2 - denied (took action)
	if ( "Notification" in window )
	{
		var permission ;
		if ( typeof( Notification.permission ) != "undefined" )
			permission = Notification.permission ;
		else { permission = "denied" ; }

		if ( permission == "default" ) { return 1 ; }
		else if ( permission === "granted" ) { return 0 ; }
		else if ( permission == "denied" ) { return 2 ; }
		else { alert( "Notification Error: "+permission ) ; } // report unknown error
	}
	else
		return -1 ;
}

function dn_show( theflag, theces, thename, thequestion, theduration )
{
	var dn = dn_check() ;
	if ( wp )
	{
		window.external.wp_incoming_chat( theces, thename, thequestion.replace( /<br>/g, ' ' ), theduration ) ;
	}
	else if ( !parseInt( dn ) && ( parseInt( dn ) != -1 ) )
	{
		if ( dn_always || ( !dn_always && !focused ) )
		{
			dn_show_doit( theflag, theces, thename, thequestion, theduration ) ;
		}
	}
}

function dn_show_doit( theflag, theces, thename, thequestion, theduration )
{
	var cache_var = "v6" ;
	var iconurl = "../pics/icons/dn_notify.png?"+cache_var ;
	if ( theflag == "new_response" ) { iconurl = "../pics/icons/dn_notify.png?"+cache_var ; }
	else if ( theflag == "logout" ) { iconurl = "./pics/icons/dn_notify.png?"+cache_var ; }

	++dn_counter ;
	var dn_counter_temp = dn_counter ;
	if ( typeof( dn_his[theces] ) == "undefined" ) { dn_his[theces] = new Object ; }
	if ( typeof( dn_his[theces][dn_counter_temp] ) == "undefined" )
	{
		dn_his[theces][dn_counter_temp] = new Object ;
		dn_his[theces][dn_counter_temp]["dn"] = new Notification( 
			thename, { 
				icon: iconurl, 
				body: thequestion,
				requireInteraction: 1
			}
		) ;
		dn_his[theces][dn_counter_temp]["dn"].onclick = function(){
			window.focus() ;

			if ( typeof( activate_chat ) != "undefined" )
			{
				if ( typeof( chats[theces] ) != "undefined" )
				{
					activate_chat( theces ) ;
					$('#input_text').focus() ;
				}
			}

			dn_close( theces, dn_counter_temp ) ;
		} ;
	}

	//dn_his[theces][dn_counter_temp].onshow = function() { }
	dn_his[theces][dn_counter_temp]["dn"].onshow = function()
	{ 
		try{
			dn_his[theces][dn_counter_temp]["st"] = setTimeout( function() { dn_close( theces, dn_counter_temp ) ; }, theduration ) ;
		} catch(e){
			//
		}
	}
}

function dn_close( theces, thisdn_counter )
{
	if ( wp ) { wp_hide_tray( theces ) ; }
	else if ( typeof( theces ) == "undefined" )
	{
		for ( var thisces in dn_his )
			dn_close( thisces ) ;
	}
	else if ( typeof( dn_his[theces] ) != "undefined" )
	{
		if ( typeof( dn_his[theces][thisdn_counter] ) != "undefined" )
		{
			try {
				dn_his[theces][thisdn_counter]["dn"].close() ;
				clearTimeout( dn_his[theces][thisdn_counter]["st"] ) ;
				dn_his[theces][thisdn_counter] = undeefined ;
			} catch(err) { }
		}
		else
		{
			for ( var thisdn_counter in dn_his[theces] )
			{
				if ( ( typeof( dn_his[theces][thisdn_counter] ) != "undefined" ) && ( typeof( dn_his[theces][thisdn_counter]["dn"] ) != "undefined" ) )
				{
					try {
						dn_his[theces][thisdn_counter]["dn"].close() ;
						clearTimeout( dn_his[theces][thisdn_counter]["st"] ) ;
						dn_his[theces][thisdn_counter] = undeefined ;
					} catch(err) { }
				}
			}
			dn_his[theces] = undeefined ;
		}
	}
}
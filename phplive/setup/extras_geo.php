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
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_IP.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$admininfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid setup session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
	
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$apikey = Util_Format_Sanatize( Util_Format_GetVar( "apikey" ), "ln" ) ;
	$jump = ( Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ) ? Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) : "geoip" ;
	$success = Util_Format_Sanatize( Util_Format_GetVar( "success" ), "n" ) ;

	$error = "" ; $dev = 0 ;
	$VERSION_GEO = 0 ;


	/***************************************/
	// location of the addons/geo_data directory
	$geo_dir = "$CONF[DOCUMENT_ROOT]/addons/geo_data" ;
	/***************************************/


	if ( is_dir( $geo_dir ) && is_file( "$geo_dir/VERSION.php" ) )
		include_once( "$geo_dir/VERSION.php" ) ;

	LIST( $your_ip, $null ) = Util_IP_GetIP( "" ) ;

	if ( $action === "update_api" )
	{
		if ( strlen( $apikey ) == 39 )
		{
			$error = ( Util_Vals_WriteToConfFile( "geomap", "$apikey" ) ) ? "" : "Could not write to config file." ;
			if ( !$error )
				$json_data = "json_data = { \"status\": 1 };" ;
			else
				$json_data = "json_data = { \"status\": 0, \"error\": \"Could not write to conf file.\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"API Key format is invalid.\" };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}
	else if ( $action === "clear_api" )
	{
		Util_Vals_WriteToConfFile( "geomap", "" ) ;
		HEADER( "location: ./extras_geo.php?jump=geomap&action=cleared_api" ) ; exit ;
	}
	else if ( $action == "activate" )
	{
		if ( !is_file( "$CONF[DOCUMENT_ROOT]/addons/geo_data/GeoLite2-City.mmdb" ) )
			$error = "Could not process the GeoIP addon.  Double check the <code>geo_data/</code> directory is inside the <code>addons/</code> folder (<code>phplive/addons/geo_data</code>).  If the directory exists and you are seeing this error, make sure the <code>geo_data/</code> directory has read permissions." ;
	}
	
	$addon_emo = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/emoticons/emo.php" ) ) ? 1 : 0 ;
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
	var st_geo_import ;
	var st_geo_import_cycle = 7 ; // seconds
	var global_ip ;

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "extras" ) ;
		show_div_geo( "<?php echo $jump ?>" ) ;

		<?php if ( !$geomap ): ?>
		$('#div_maps_steps').show() ;
		<?php else: ?>
		$('#div_maps_update').show() ;
		<?php endif; ?>

		if ( "<?php echo $error ?>" )
			do_alert_div( "..", 0, "<?php echo $error ?>" ) ;
		else if ( ( "<?php echo $action ?>" == "update" ) || <?php echo $success ?> )
			do_alert( 1, "Update Success" ) ;
		else if ( "<?php echo $action ?>" == "cleared" )
			do_alert( 1, "GeoIP addon has been reset." ) ;
		else if ( "<?php echo $action ?>" == "cleared_api" )
			do_alert( 1, "Google Maps API Key has been cleared." ) ;
	});

	function confirm_activate()
	{
		location.href = "extras_geo.php?action=activate" ;
	}

	function submit_key()
	{
		var unique = unixtime() ;
		var json_data = new Object ;
		var apikey = $('#apikey').val() ; apikey = apikey.replace( / /g, "" ) ;
		$('#apikey').val( apikey ) ;

		if ( !apikey )
			do_alert( 0, "Blank API Key is invalid." ) ;
		else if ( apikey.length != 39 )
			do_alert( 0, "API Key format is invalid." ) ;
		else if ( "<?php echo $geokey ?>" == apikey )
		{
			do_cancel() ;
			do_alert( 1, "Update Success" ) ;
		}
		else
		{
			$.ajax({
			type: "POST",
			url: "./extras_geo.php",
			data: "action=update_api&apikey="+apikey+"&unique="+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
					location.href = "./extras_geo.php?jump=geomap&success=1" ;
				else
				{
					do_alert( 0, jaon_data.error ) ;
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Error processing API Key.  Please refresh the page and try again." ) ;
			} });
		}
	}

	function clear_key()
	{
		if ( confirm( "Clear API Key and deactivate Google Maps?" ) )
		{
			location.href = "./extras_geo.php?action=clear_api&jump=<?php echo $jump ?>" ;
		}
	}

	function do_cancel()
	{
		$('#div_maps_steps').hide() ;
		$('#div_maps_update').show() ;
		$(window).scrollTop(0) ;
	}

	function show_div_geo( thediv )
	{
		var divs = Array( "geoip", "geomap" ) ;
		for ( var c = 0; c < divs.length; ++c )
		{
			$('#div_'+divs[c]).hide() ;
			$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
		}

		$('#div_'+thediv).show() ;
		$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
	}

	function lookup_ip( theip )
	{
		var unique = unixtime() ;

		$('#geoip_output').html('<img src="../pics/loading_fb.gif" width="16" height="11" border="0" alt="">') ;

		if ( !theip )
		{
			$('#geoip_output').html("") ;
			do_alert( 0, "Blank IP is invalid." ) ;
		}
		else
		{
			if ( global_ip != theip )
			{
				global_ip = theip ;
				$('#btn_lookup').attr("disabled", true) ;
				setTimeout( function(){ $('#btn_lookup').attr("disabled", false) ; }, 2000 ) ;

				$('#iframe_map').attr('src', "../ops/maps.php?ip="+theip+"&vis_token=Quick+IP+Lookup&viewip=1&"+unique) ;
			}
			else { $('#iframe_map').fadeOut("fast").fadeIn("fast").fadeOut("fast").fadeIn("fast") ; }
		}
	}

//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php
			if ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["extras"] ) ) ):
			include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_access.php" ) ; else:
		?>
		<?php include_once( "./inc_menu.php" ) ; ?>

		<form method="POST" action="extras_geo.php" enctype="multipart/form-data">
		<input type="hidden" name="action" value="update">

		<div style="text-align: justify; margin-top: 25px;">
			<div id="div_geoip">
				<div>GeoIP is the identification of the real-world geographic location of an IP address. Enabling this feature will display a country flag, the region and city of an IP address throughout various areas of the system.  To enable this feature, complete the following steps:</div>

				<?php if ( !is_file( "$CONF[DOCUMENT_ROOT]/addons/geo_data/GeoLite2-City.mmdb" ) ): ?>
				<div style="margin-top: 15px;" class="edit_title">Activate the GeoIP addon:</div>
				<div style="display: none; margin-top: 15px;" class="info_error" id="div_alert"></div>
				<div style="margin-top: 15px;">
					<table cellspacing=0 cellpadding=0 border=0 width="100%">
					<tr>
						<td width="33%" valign="top" style="padding-right: 5px;">
							<div style="height: 210px;" class="info_info">
								<div style="font-size: 14px; font-weight: bold;"><span style="color: #F38725;">Step 1:</span> Download</div>
								<div style="margin-top: 5px;">
									Login at the PHP Live! client area and download the compressed GeoIP Addon file.
									<div style="margin-top: 15px;">(~24 Megs compressed)</div>

									<div style="margin-top: 15px;"><a href="http://www.phplivesupport.com/r.php?r=login" target="_blank">Login and Download the GeoIP Addon</a></div>
								</div>
							</div>
						</td>
						<td width="33%" valign="top" style="padding-right: 5px;">
							<div style="height: 210px;" class="info_info">
								<div style="font-size: 14px; font-weight: bold;"><span style="color: #F38725;">Step 2:</span> Extract</div>
								<div style="margin-top: 5px;">
									Extract the downloaded file to produce the <code>"geo_data/"</code> folder containing the addon files.

									<div style="margin-top: 15px;">File decompression software such as <a href="http://www.winzip.com" target="_blank">WinZip</a> or <a href="http://www.win-rar.com/" target="_blank">WinRar</a> is needed to decompress the file.</div>
								</div>
							</div>
						</td>
						<td width="33%" valign="top" style="">
							<div style="height: 210px;" class="info_info">
								<div style="font-size: 14px; font-weight: bold;"><span style="color: #F38725;">Step 3:</span> FTP</div>
								<div style="margin-top: 5px;">
									FTP the entire <code>"geo_data/"</code> folder to your server and place it inside the <code>addons/</code> directory of your PHP Live! system.
									
									<div style="margin-top: 15px;"><code>phplive/addons/</code></div>

									<div style="margin-top: 15px;">After the folder is uploaded, click the button below to activate the GeoIP addon.</div>
								</div>

								<div style="margin-top: 15px;"><button type="button" onClick="confirm_activate()" class="btn">Folder is uploaded.  Activate the GeoIP.</button></div>
							</div>
						</td>
					</tr>
					</table>
				</div>

				<?php else: ?>
				<div style="margin-top: 15px;">
					<div class="edit_title"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt=""> GeoIP Is Enabled</div>

					<div id="div_geoip_lookup" style="margin-top: 15px;">
						<div class="op_submenu_focus" style="background: #F3F3F3; border: 1px solid #DDDCD7; border-bottom: 0px; border-bottom-left-radius: 0px 0px; border-bottom-right-radius: 0px 0px;">Quick IP Lookup</div>
						<div style="clear: both;"></div>
						<div class="info_info">
							IP Address (your current IP: <span class="txt_orange"><?php echo $your_ip ?></span>)<br>
							<span style="font-size: 10px;">* support for IPv6 will be available in the near future</span>

							<div style="margin-top: 10px;">
								<table cellspacing=0 cellpadding=0 border=0 width="100%">
								<tr>
									<td valign="top" nowrap><input type="text" class="input" name="ip_addy" id="ip_addy" size="20" maxlength="45" onKeyPress="return numbersonly(event)"> &nbsp; <input type="button" onClick="lookup_ip($('#ip_addy').val().trim())" value="Lookup" class="btn" id="btn_lookup"></td>
									<td style="padding-left: 25px; width: 100%;" valign="top"><iframe id="iframe_map" name="iframe_map" style="width: 100%; height: 250px; border: 0px;" src="about:blank" scrolling="auto" border=0 frameborder=0 class="round"></iframe></td>
								</tr>
								</table>
							</div>
						</div>
						<div style="margin-top: 5px; font-size: 10px; text-align: right;">* to disable the GeoIP addon, delete the <code>geo_data/</code> folder from the addons directory</div>
					</div>

					<div style="margin-top: 25px; text-align: right;">GeoIP Data v.<?php echo $VERSION_GEO ?>  <img src="../pics/icons/disc.png" width="16" height="16" border="0" alt=""> <a href="system.php">Check for updates.</a></div>
				</div>

				<?php endif ; ?>
			</div>

			<div id="div_geomap" style="display: none; margin-top: 25px;">
				<div>Expand the GeoIP feature with Google Maps.  Google Maps will display the approximate location of an IP address on Google Maps.  Due to the <a href="https://developers.google.com/maps/terms" target="_blank">Google Maps API Terms of Service</a>, you'll want to signup for a Google API Key so that the integration is linked to your account.  API Key also enables the reporting features of the Google Maps requests.</div>

				<?php if ( !$geoip ): ?>
					<div style="margin-top: 25px;">
						<span class="info_error"><img src="../pics/icons/warning.png" width="12" height="12" border="0" alt=""> Enable the <a href="JavaScript:void(0)" onClick="show_div_geo('geoip')" style="color: #FFFFFF;">GeoIP Addon</a> before activating Google Maps.</span>
					</div>
				<?php else: ?>
					<div style="margin-top: 25px;">
						<?php if ( $geomap ): ?>
						<div class="edit_title" style="padding-bottom: 15px;"><img src="../pics/icons/check.png" width="16" height="16" border="0" alt="">  Google Maps Is Enabled <span style="font-size: 12px; font-weight: normal;"> &bull; <a href="JavaScript:void(0)" onClick="show_div_geo('geoip')">try it</a></span></div>
						<?php endif ; ?>
						<div id="div_maps_steps" style="display: none;">
							<div style="font-size: 14px; font-weight: bold;"><span style="color: #F38725;">Step 1:</span> Enable the "Google Maps JavaScript API"</div>
							<div style="margin-top: 5px;">
								<ul>
									<li> Login to the <a href="https://console.developers.google.com/apis" target="_blank">Google Code Console</a> and select a project (or create a new project).</li>
									<li> After selecting a project, click the left menu "<code>Library</code>".</li>
									<li> On the "<code>Library</code>" page, there should be "Google Maps APIs" section with a list of APIs.  Click the link "<span style="font-weight: bold; color: #3B78E7;">Google Maps JavaScript API</span>" and proceed to "<span style="font-weight: bold; color: #3B78E7;">ENABLE</span>" the API.</li>
								</ul>
							</div>

							<div style="margin-top: 25px;">
								<div style="font-size: 14px; font-weight: bold;"><span style="color: #F38725;">Step 2:</span> Credentials</div>
								<div style="margin-top: 5px;">After enabling the "Google Maps JavaScript API", click the blue button "<span style="font-weight: bold; color: #3B78E7;">Manage</span>" and then to the "<span style="font-weight: bold; color: #3B78E7;">Credentials</span>" menu on the left.  Proceed to "<span style="font-weight: bold; color: #3B78E7;">Create credentials</span>" (drop down menu) and select "API Key".</div>
							</div>

							<div style="margin-top: 25px;">
								<div style="font-size: 14px; font-weight: bold;"><span style="color: #F38725;">Step 3:</span> Provide the API Key</div>
								<div style="margin-top: 5px;">Copy the generated "Key" and provide the key below to activate Google Maps:</div>

								<div style="margin-top: 15px;"><input type="text" id="apikey" name="apikey" class="input" size="50" maxlength="55" value="<?php echo ( $geokey ) ? $geokey : "" ; ?>"></div>

								<div style="margin-top: 15px;"><img src="../pics/icons/info.png" width="14" height="14" border="0" alt=""> <b>NOTE:</b> it may take 15-45 minutes for Google to process the new API Key.  During processing, there may be an API error when trying to lookup an IP address.</div>
								<div style="margin-top: 15px;">
									<input type="button" value="Update Google API Key" onClick="submit_key()" class="btn"> &nbsp; 
									<span style="display: none;" id="text_cancel"> <input type="button" value="Clear Key" onClick="clear_key()" class="btn"> &nbsp; <a href="JavaScript:void(0)" onClick="do_cancel()">cancel</a></span>
								</div>
							</div>
						</div>
						<div id="div_maps_update" style="display: none;">
							<div class="info_info">Google API Key: <input type="text" class="input" size="50" maxlength="55" value="<?php echo ( $geokey ) ? $geokey : "" ; ?>" disabled="disabled"></div>
							<div style="margin-top: 15px;"><img src="../pics/icons/key.png" width="16" height="16" border="0" alt=""> <a href="JavaScript:void(0)" onClick="$('#div_maps_steps').show(); $('#div_maps_update').hide(); $('#text_cancel').show();">Update Google API Key</a></div>
						</div>
						
					</div>
				<?php endif ; ?>

			</div>

		</div>
		</form>
		<?php endif ; ?>

<?php include_once( "./inc_footer.php" ) ?>

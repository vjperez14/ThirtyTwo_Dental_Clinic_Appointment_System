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
	include_once( "../web/config.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Security.php" ) ;
	if ( !$opinfo = Util_Security_AuthOp( $dbh ) )
	{
		if ( !$opinfo = Util_Security_AuthSetup( $dbh ) ){ ErrorHandler( 608, "Invalid session or session has expired.", $PHPLIVE_FULLURL, 0, Array() ) ; exit ; }
		$opinfo["theme"] = "default" ;
	}
	// STANDARD header end
	/****************************************/

	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Hash.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/IPs/Util.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/GeoIP/get.php" ) ;
	use GeoIp2\Database\Reader ;

	$theme = $opinfo["theme"] ;
	if ( !is_file( "$CONF[DOCUMENT_ROOT]/themes/$theme/style.css" ) || isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }

	$ip = Util_Format_Sanatize( Util_Format_GetVar( "ip" ), "ln" ) ;
	$vis_token = Util_Format_Sanatize( Util_Format_GetVar( "vis_token" ), "lns" ) ;
	$viewip = Util_Format_Sanatize( Util_Format_GetVar( "viewip" ), "n" ) ;
	$skip = Util_Format_Sanatize( Util_Format_GetVar( "skip" ), "n" ) ;
	$geo_country_code = "unknown" ;

	if ( $skip ) { $geoip = 0 ; $geo_country = "Location Unknown" ; $geo_region = "-" ; $geo_city = "-" ; $geo_lat = 28.613459424004414 ; $geo_long = -40.4296875 ; }
	else
	{
		if ( ( phpversion() >= 5.4 ) && $geoip )
		{
			require "$CONF[DOCUMENT_ROOT]/addons/geo_data/vendor/autoload.php" ;

			$reader = new Reader( "$CONF[DOCUMENT_ROOT]/addons/geo_data/GeoLite2-City.mmdb" ) ;
			try {
				$record = $reader->city( $ip ) ;
				$geo_country_code = ( isset( $record->country->isoCode ) ) ? $record->country->isoCode : "unknown" ;
				$geo_country = ( $geo_country_code != "unknown" ) ? $record->country->name : "Unknown" ;
				$geo_region = ( isset( $record->mostSpecificSubdivision->name ) ) ? $record->mostSpecificSubdivision->name : "unknown" ;
				$geo_city = ( isset( $record->city->name ) ) ? $record->city->name : "unknown" ;
				$geo_lat = ( isset( $record->location->latitude ) ) ? $record->location->latitude : 28.613459424004414 ;
				$geo_long = ( isset( $record->location->longitude ) ) ? $record->location->longitude : -40.4296875 ;
			} catch (Exception $e) {
				$geo_country = "Location Unknown" ; $geo_region = "-" ; $geo_city = "-" ; $geo_lat = 28.613459424004414 ; $geo_long = -40.4296875 ;
			}
		}
		else if ( phpversion() < 5.4 )
		{
			$geomap = 0 ; // bypass map to display message
			$geo_country_code = "unknown" ;
			$geo_country = "Unknown" ;
			$geo_region = "unknown" ;
			$geo_city = "unknown" ;
			$geo_lat = 28.613459424004414 ;
			$geo_long = -40.4296875 ;
		}
		else
		{
			$geo_country_code = "unknown" ;
			$geo_country = "Unknown" ;
			$geo_region = "unknown" ;
			$geo_city = "unknown" ;
			$geo_lat = 28.613459424004414 ;
			$geo_long = -40.4296875 ;
		}

		if ( $geo_country_code == "unknown" ) { $geomap = 0 ; $geokey = "" ; }
	}
	$zoom = 3 ;
	if ( $geo_city != "unknown" ) { $zoom = 4 ; }
?>
<?php include_once( "$CONF[DOCUMENT_ROOT]/inc_doctype.php" ) ?>
<head>
<title> v.<?php echo $VERSION ?> </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../themes/<?php echo $theme ?>/style.css?<?php echo $VERSION ?>" id="stylesheet">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>
<?php if ( $geoip && $geomap ): ?>
<script data-cfasync="false" type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $geokey ?>"></script>
<?php endif; ?>
<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		<?php if ( $geoip && $geomap ): ?>
		setTimeout( function(){ draw_map() ; }, 500 ) ;
		<?php else: ?>
		$('#map_default').show() ;
		<?php endif ; ?>

	});

	function draw_map()
	{
		var info_content = "<div class=\"info_box\"><b>Country:</b> <?php echo $geo_country ?> (<?php echo $geo_country_code ?>)<br><b>Region:</b> <?php echo $geo_region ?><br><b>City:</b> <?php echo $geo_city ?></div>" ;
		var infowindow = new google.maps.InfoWindow({
			content: info_content
		});

		var latlng = new google.maps.LatLng( <?php echo $geo_lat ?>, <?php echo $geo_long ?> ) ;
		var myOptions = {
			zoom: <?php echo $zoom ?>,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		} ;
		var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions) ;
		var marker = new google.maps.Marker({
			animation: google.maps.Animation.DROP,
			position: latlng,
			title: "Country: <?php echo $geo_country ?>, Region: <?php echo $geo_region ?>, City: <?php echo $geo_city ?>"
		}) ;
		marker.setMap(map) ;
		infowindow.open(map, marker) ;
		//marker.addListener('click', function() {
		//	infowindow.open(map, marker) ;
		//});
 
		adjust_height() ;
	}

	function adjust_height()
	{
		var canvas_height = $(window).height() ;
		<?php if ( $geoip && $geomap ): ?>$('#map_canvas').css({'height': canvas_height}).show() ;<?php endif ; ?>
	}
//-->
</script>
</head>
<body class="info_content" style="margin: 0px; border: 0px; padding: 0px;">
	<div id="map_canvas" style="display: none; height: 100%;"></div>
	<div id="map_default" style="display: none; height: 100%;">
		<?php if ( $skip ): ?>
		<div class="info_box" style="padding: 10px;">Location not available for this session.</div>

		<?php else: ?>

			<?php if ( ( phpversion() >= 5.4 ) && $geoip && !$geomap ): ?>
			<div style="padding: 10px;">
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td width="80" class="chat_info_td_h">Visitor ID</td>
					<td class="chat_info_td"><?php echo ( $vis_token ) ? $vis_token : "" ; ?></td>
				</tr>
				<?php if ( $viewip ): ?>
				<tr>
					<td width="80" class="chat_info_td_h">IP</td>
					<td class="chat_info_td"><?php echo $ip ?></td>
				</tr>
				<?php endif ; ?>
				<tr>
					<td width="80" class="chat_info_td_h">Country</td>
					<td class="chat_info_td"><img src="../pics/maps/<?php echo strtolower( $geo_country_code ) ?>.gif" width="18" height="12" border="0" alt="<?php echo $geo_country ?>" title="<?php echo $geo_country ?>"> <?php echo $geo_country ?></div></td>
				</tr>
				<tr>
					<td width="80" class="chat_info_td_h">Region</td>
					<td class="chat_info_td"><?php echo $geo_region ?></td>
				</tr>
				<tr>
					<td width="80" class="chat_info_td_h">City</td>
					<td class="chat_info_td"><?php echo $geo_city ?></td>
				</tr>
				</table>
			</div>

			<?php elseif ( phpversion() < 5.4 ): ?>
			<div class="chat_info_td" style="padding: 10px; text-align: justify;"><a href="http://php.net/downloads.php" target="_blank">PHP >= 5.4</a> is required to utilize the GeoIP Addon.  Your PHP version is <?php echo phpversion() ?>.</div>

			<?php else: ?>
			<div class="chat_info_td" style="padding: 10px; text-align: justify;">
				To enable Geo location, please contact the Setup Admin.  The Setup Admin will need to login to the Setup Admin area and click the top menu "Extras" and access the "GeoIP" tab.
				<div style="margin-top: 15px;"><code>Setup Admin &gt; Extras &gt; GeoIP</code></div>
			</div>
			<?php endif ; ?>

		<?php endif ;?>
	</div>
</body>
</html>
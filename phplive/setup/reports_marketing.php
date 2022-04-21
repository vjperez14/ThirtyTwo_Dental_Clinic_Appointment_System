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

	$error = "" ;

	include_once( "$CONF[DOCUMENT_ROOT]/API/Marketing/get.php" ) ;
	if ( is_file( "$CONF[CONF_ROOT]/addons.php" ) ) { include_once( "$CONF[CONF_ROOT]/addons.php" ) ; }
	$emarketing_addon_enabled = ( $VARS_ADDON_EMARKET_ENABLED && is_file( "../addons/emarketing/emarketing.php" ) ) ? 1 : 0 ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$m = Util_Format_Sanatize( Util_Format_GetVar( "m" ), "n" ) ;
	$d = Util_Format_Sanatize( Util_Format_GetVar( "d" ), "n" ) ;
	$y = Util_Format_Sanatize( Util_Format_GetVar( "y" ), "n" ) ;
	$y_start = date( "Y", Util_Format_TableFirstCreated( $dbh, "p_market_c" ) ) ;

	if ( !$m )
		$m = date( "m", time() ) ;
	if ( !$d )
		$d = date( "j", time() ) ;
	if ( !$y )
		$y = date( "Y", time() ) ;

	$today = mktime( 0, 0, 1, $m, $d, $y ) ;
	$stat_start = mktime( 0, 0, 1, $m, 1, $y ) ;
	$stat_end = mktime( 23, 59, 59, $m, date('t', $stat_start), $y ) ;
	$stat_end_day = date( "j", $stat_end ) ;

	$marketings = Marketing_get_AllMarketing( $dbh ) ;

	$clicks_timespan = Marketing_get_ClicksRangeHash( $dbh, $stat_start, $stat_end ) ;
	$month_total_clicks = 0 ;
	$month_max = $js_stats = "" ;
	foreach ( $clicks_timespan as $sdate => $marketids )
	{
		foreach ( $marketids as $key => $value )
		{
			if ( $sdate && ( $key == "clicks" ) )
				$month_total_clicks += $value ;
			if ( $key != "clicks" )
				$js_stats .= "stats[$sdate][$key]['clicks'] = $value[clicks] ; " ;
		}

		if ( isset( $clicks_timespan[$sdate]["clicks"] ) && ( $clicks_timespan[$sdate]["clicks"] > $month_max ) && $sdate )
			$month_max = $clicks_timespan[$sdate]["clicks"] ;
	}
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
	var stats = new Object ; stats[0] = new Object ;

	<?php
		for ( $c = 1; $c <= $stat_end_day; ++$c )
		{
			$stat_day = mktime( 0, 0, 1, $m, $c, $y ) ;
			print "stats[$stat_day] = new Object; " ;

			for ( $c2 = 0; $c2 < count( $marketings ); ++$c2 )
			{
				$marketing = $marketings[$c2] ;
				print "stats[$stat_day][$marketing[marketID]] = new Object; " ;
				print "stats[0][$marketing[marketID]] = new Object; " ;
			}
		}
	?>

	<?php echo $js_stats ?>

	$(document).ready(function()
	{
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "extras" ) ;
		if ( typeof( show_div ) == "function" )
			show_div( "marketing" ) ;

		reset_date() ;
	});

	function reset_date()
	{
		select_date( 0, "<?php echo date( "M j, Y", $stat_start ) ?> - <?php echo date( "M j, Y", $stat_end ) ?>" ) ;
	}

	function select_date( theunix, thedayexpand )
	{
		var total = 0 ;
		$('#stat_day_expand').html( thedayexpand ) ;
		for ( var marketid in stats[theunix] )
		{
			if ( typeof( stats[theunix][marketid]["clicks"] ) != "undefined" )
			{
				$('#stat_clicks_'+marketid).html( stats[theunix][marketid]["clicks"] ) ;
				total += stats[theunix][marketid]["clicks"] ;
			}
			else
			{
				$('#stat_clicks_'+marketid).html( 0 ) ;
			}
		}
		$('#stat_total_requests').html( total ) ;
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<?php include_once( "./inc_menu.php" ) ; ?>

		<div style="margin-top: 25px;">
			<div class="op_submenu3" style="margin-left: 0px;" onClick="location.href='marketing_click.php'">Campaign Tracking</div>
			<div class="op_submenu_focus">Campaign Clicks</div>
			<?php if ( $emarketing_addon_enabled ): ?>
			<div class="op_submenu3" onClick="location.href='../addons/emarketing/emarketing.php'">Email Marketing</div>
			<div class="op_submenu3" onClick="location.href='../addons/emarketing/emarketing_export.php'">Email Marketing Stats</div>
			<?php endif ?>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			The click activity report for the <a href="marketing_click.php">Campaign Tracking</a> feature.
		</div>

		<table cellspacing=0 cellpadding=0 border=0 width="100%" style="margin-top: 25px;">
		<tr>
			<td><div class="td_dept_header">Timeline</div></td>
			<td><div class="td_dept_header">Clicks</div></td>
		</tr>
		<tr>
			<td class="td_dept_td"><?php include_once( "./inc_select_cal.php" ) ; ?></td>
			<td class="td_dept_td"><?php echo $month_total_clicks ?></td>
		</tr>
		</table>

		<div style="margin-top: 25px; width: 100%;">
			<table cellspacing=0 cellpadding=0 border=0 style="height: 100px; width: 100%;">
			<tr>
				<?php
					$tooltips = Array() ;
					for ( $c = 1; $c <= $stat_end_day; ++$c )
					{
						$stat_day = mktime( 0, 0, 1, $m, $c, $y ) ;
						$stat_day_expand = date( "l, M j, Y", $stat_day ) ;

						$h1 = "0px" ; $meter = "meter_v_blue.gif" ;
						$tooltip = "$stat_day_expand" ;
						$tooltips[$stat_day] = $tooltip ;
						$tooltip_display = "" ;

						$meter_shadow = " box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.2); " ;
						if ( isset( $clicks_timespan[$stat_day] ) )
						{
							$tooltip_display = "$stat_day_expand" ;
							if ( $month_max )
								$h1 = round( ( $clicks_timespan[$stat_day]["clicks"]/$month_max ) * 100 ) . "px" ;
						}
						else if ( ( $c == $stat_end_day ) && ( !$month_max ) )
						{
							$h1 = "100px" ;
							$meter = "meter_v_clear.gif" ;
							$meter_shadow = "" ;
						}

						print "
							<td valign=\"bottom\" width=\"2%\"><div id=\"bar_v_requests_$c\" title=\"$tooltip_display\" alt=\"$tooltip_display\" style=\"height: $h1; background: url( ../pics/meters/$meter ) repeat-y; border-top-left-radius: 5px 5px; border-top-right-radius: 5px 5px; cursor: pointer; $meter_shadow\" OnMouseOver=\"\" OnClick=\"select_date( $stat_day, '$stat_day_expand' );\"></div></td>
							<td><img src=\"../pics/space.gif\" width=\"5\" height=1 border=0></td>
						" ;
					}
				?>
			</tr>
			<tr>
				<?php
					for ( $c = 1; $c <= $stat_end_day; ++$c )
					{
						$stat_day = mktime( 0, 0, 1, $m, $c, $y ) ;
						$stat_day_expand = date( "l, M j, Y", $stat_day ) ;
						print "
							<td align=\"center\"><div id=\"requests_bg_day\" class=\"page_report\" style=\"margin: 0px; font-size: 10px; font-weight: bold;\" title=\"$tooltips[$stat_day]\" alt=\"$tooltips[$stat_day]\" OnMouseOver=\"\" OnClick=\"select_date( $stat_day, '$stat_day_expand' );\">$c</div></td>
							<td><img src=\"../pics/space.gif\" width=\"5\" height=1 border=0></td>
						" ;
					}
				?>
			</tr>
			</table>
		</div>

		<div id="overview_day_chart" style="margin-top: 50px;">
			<div id="overview_date_title"><div id="stat_day_expand"></div></div>
			<div id="overview_data_container">
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td width="200"><div class="td_dept_header">Campaigns</div></td>
					<td><div class="td_dept_header">Clicks</div></td>
				</tr>
				<?php
					for ( $c = 0; $c < count( $marketings ); ++$c )
					{
						$marketing = $marketings[$c] ;
						$color = $marketing["color"] ;
						$td1 = "td_dept_td" ;

						print "
							<tr>
								<td class=\"$td1\" style=\"background: #$color; text-shadow: none;\" nowrap>
									<b>$marketing[name]</b>
									<div style='margin-top: 5px; font-size: 10px;'>&plk=pi-$marketing[marketID]-$marketing[skey]-m</div>
								</td>
								<td class=\"$td1\"><div id=\"stat_clicks_$marketing[marketID]\">0</div></td>
							</tr>
						" ;
					}
				?>
				<tr>
					<td class="td_dept_td"><b>Total</b></td>
					<td class="td_dept_td"><div id="stat_total_requests" style="font-weight: bold;"></div></td>
				</tr>
				</table>
			</div>
		</div>

<?php include_once( "./inc_footer.php" ) ?>
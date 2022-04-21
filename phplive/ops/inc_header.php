<?php
	$cache_bypass = "" ;
?>
<body style="">

<script data-cfasync="false" type="text/javascript">
<!--
	$(init_inview) ;
	$(window).scroll( init_inview ) ;

	function check_inview( theobject )
	{
		var scroll_top = $(window).scrollTop() ;
		var scroll_view = scroll_top + $(window).height() ;

		var pos_top = $(theobject).offset().top ;
		var pos_bottom = pos_top + $(theobject).height() ;

		return ((pos_bottom <= scroll_view) && (pos_top >= scroll_top) ) ;
	}

	function init_inview() {
		if ( check_inview( $('#menu_wrapper') ) )
			$('#div_scrolltop').fadeOut("fast") ;
		else
			$('#div_scrolltop').fadeIn("fast") ;
	}

	function scroll_top()
	{
		$('html, body').animate({
			scrollTop: 0
		}, 200);
	}

//-->
</script>

<div id="div_scrolltop" style="display: none; position: fixed; top: 25%; right: 0px; z-index: 1000;">
	<div style="padding: 5px; background: #DFDFDF; border: 1px solid #B9B9B9; border-right: 0px; text-shadow: 1px 1px #FFFFFF; border-top-left-radius: 5px 5px; border-bottom-left-radius: 5px 5px; cursor: pointer;" onClick="scroll_top()"><img src="../pics/icons/arrow_top.png" width="15" height="16" border="0" alt=""> top</div>
</div>

<div id="header_wrapper" style="background: url( <?php echo $CONF["BASE_URL"] ?>/pics/bg_header_shadow.png ) repeat-x #49586C; background-position: bottom;">
	<div style="">
		<div style="width: 970px; margin: 0 auto;">
			<div id="menu_wrapper" style="padding-top: <?php echo ( $console ) ? 15 : 35 ; ?>px; padding-bottom: <?php echo ( $console ) ? 15 : 35 ; ?>px;">
				<div id="menu_go" class="menu" onClick="<?php echo ( preg_match( "/(cans)|(notifications)|(transcript)|(activity)|(report)|(settings)/", $menu ) ) ? "location.href='./index.php?console=$console&auto=$auto&$cache_bypass'" : "toggle_menu_op('go')" ; ?>"><?php echo ( $console ) ? '<img src="../pics/icons/vcard.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round">  Profile' : '<img src="../pics/icons/bulb.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Go ONLINE!' ; ?></div>
				<?php if ( $console ): ?><div id="menu_themes" class="menu" onClick="<?php echo ( preg_match( "/(cans)|(notifications)|(transcript)|(activity)|(report)|(settings)/", $menu ) ) ? "location.href='./index.php?menu=themes&console=$console&auto=$auto&$cache_bypass'" : "toggle_menu_op('themes')" ; ?>"><img src="../pics/icons/menu_icons.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Themes</div>
				<div id="menu_notifications" class="menu" onClick="location.href='./notifications.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'"><img src="../pics/icons/menu_sound.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Sound Alerts & Desktop Notification</div>
				<?php else: ?>
				<!-- <div id="menu_cans" class="menu" onClick="location.href='./cans.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'"><img src="../pics/icons/menu_cans.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Canned Responses</div> -->
				<?php endif ; ?>
				<div id="menu_trans" class="menu" onClick="location.href='transcripts.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'"><img src="../pics/icons/menu_trans.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Transcripts</div>
				<div id="menu_reports" class="menu" onClick="location.href='./reports.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'"><img src="../pics/icons/menu_calendar.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Reports</div>
				<div id="menu_activity" class="menu" onClick="location.href='./activity.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'"><img src="../pics/icons/menu_calendar.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Online/Offline Activity</div>
				<div id="menu_settings" class="menu" onClick="location.href='./settings.php?console=<?php echo $console ?>&auto=<?php echo $auto ?>&<?php echo $cache_bypass ?>'"><img src="../pics/icons/menu_settings.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Settings</div>
				<div style="clear: both;"></div>
			</div>
		</div>
	</div>
</div>

<div style="width: 100%; padding-top: <?php echo ( $console ) ? 5 : 25 ; ?>px;" id="div_body_container">
	<div style="width: 970px; margin: 0 auto; text-align: right;">
		<table cellspacing=0 cellpadding=0 border=0 width="100%">
		<tr>
			<td width="100%">&nbsp;</td>
			<?php if ( !$console ): ?>
			<td width="160" nowrap>
				<div style="padding: 10px; text-align: center; background: #8BCF92; border: 1px solid #82C289;" class="round_top"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/bulb_big.png" width="12" height="12" border="0" alt="" id="img_bulb"> <a href="index.php?jump=online&<?php echo $cache_bypass ?>" style="color: #FFFFFF;">Go <span style="">ONLINE!</span></a></div>
			</td>
			<?php endif ; ?>
			<td align="right" style="padding-left: 15px;" nowrap>
				<div style="padding: 10px; text-align: center;" class="round_top">Chat Operator: <span style="font-size: 16px; font-weight: bold;"><?php echo $opinfo["login"] ?></span> <?php if ( !$console ): ?>&nbsp; [ <a href="JavaScript:void(0)" onClick="logout_op()">log out</a> ]<?php endif ; ?></div>
			</td>
		</tr>
		</table>
	</div>
	<div style="width: 970px; margin: 0 auto; padding: 25px; padding-bottom: 100px;" class="round">
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

	function toggle_navigation()
	{
		if ( $('#div_menu_expand').is(':visible') )
		{
			$('#div_menu_expand').show().animate({'top': -390}, {
				duration: 300,
				complete: function() {
					$('#div_menu_expand').hide() ;
				}
			});
		}
		else
		{
			$('#div_menu_expand').show().animate({'top': 0}, {
				duration: 300,
				complete: function() {
					scroll_top() ;
				}
			});
		}
	}

	function close_navigation()
	{
		if ( $('#div_menu_expand').is(':visible') )
			toggle_navigation() ;
	}

	function close_getting_started()
	{
		var name = "setup_getting_started" ;
		var value = 1 ;
		var expire = unixtime() + (60*60*24*3650) ;
		var cookie_string = name + "=" + value + "; path=/;" ;

		document.cookie = cookie_string ;

		$('#div_getting_started').fadeOut( 1000, function() {
			//
		}) ;
	}
//-->
</script>

<div id="div_scrolltop" style="display: none; position: fixed; top: 25%; right: 0px; z-Index: 1000;">
	<div style="padding: 5px; background: #DFDFDF; border: 1px solid #B9B9B9; border-right: 0px; text-shadow: 1px 1px #FFFFFF; border-top-left-radius: 5px 5px; border-bottom-left-radius: 5px 5px; cursor: pointer;" onClick="scroll_top()"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/arrow_top.png" width="15" height="16" border="0" alt=""> top</div>
</div>

<div id="header_wrapper" style="background: #49586C;">
	<div style="background: url( <?php echo $CONF["BASE_URL"] ?>/pics/bg_header_shadow.png ) repeat-x; background-position: bottom; border-bottom: 1px solid #2D3B4A;">
		<div style="width: 970px; margin: 0 auto; padding-top: 35px; padding-bottom: 35px;">
			<div id="menu_wrapper" style="">
				<div id="menu_home" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/'"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_home.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Home</div>
				<div id="menu_depts" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/depts.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["depts"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_depts.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Departments</div>
				<div id="menu_ops" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/ops.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_ops.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Operators</div>
				<div id="menu_interface" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/interface.php?jump=logo'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_icons.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Interface</div>
				<div id="menu_icons" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/icons.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["icons"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_icons.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Chat Icons</div>
				<div id="menu_html" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/code.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["code"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_code.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> HTML Code</div>
				<div id="menu_trans" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/transcripts.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["trans"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_trans.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Transcripts</div>
				<div id="menu_rchats" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/reports_chat.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_chats.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Reports</div>
				<div id="menu_rtraffic" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/reports_traffic.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["traffic"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_marketing.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Traffic</div>
				<div id="menu_extras" class="menu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/marketing_click.php'" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["extras"] ) ) ) ? "display: none;" : "" ; ?>"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_extras.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Extras</div>
				<div id="menu_settings" class="menu" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["settings"] ) ) ) ? "display: none;" : "" ; ?> margin-right: 0px;" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/settings.php'"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_settings.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"> Settings</div>
				<div style="clear: both;"></div>
			</div>
		</div>
	</div>
</div>
<div style="">
	<?php include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_header_extra.php" ) ; ?>
	<div style="width: 970px; margin: 0 auto; padding-top: 25px;">
		<?php if ( $admininfo["isadmin"] || ( !$admininfo["isadmin"] && ( !count( $admininfo["access"] ) || ( count( $admininfo["access"] ) > 1 ) || ( ( count( $admininfo["access"] ) == 1 ) && !isset( $admininfo["access"]["mboard"] ) ) ) ) ): ?>
		<div id="menu_expand" onClick="toggle_navigation();" class="info_blue" style="display: inline-block; *display: inline; zoom: 1; cursor: pointer;" class="round"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/expand_menu.png" width="16" height="16" border="0" alt=""> expand navigation menu</div>
		<?php endif ; ?>
		<?php if ( $admininfo["isadmin"] || ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && isset( $admininfo["access"]["mboard"] ) ) ) ): ?>
		<div onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/message_board/message_board.php'" class="<?php echo ( preg_match( "/message_board.php/i", $_SERVER['REQUEST_URI'] ) ) ? "info_white" : "info_clear" ; ?>" style="display: inline-block; *display: inline; zoom: 1; cursor: pointer;" class="round"><img src="<?php echo $CONF["BASE_URL"] ?>/addons/message_board/pics/mboard.png" width="16" height="16" border="0" alt=""> Message Board</div>
		<?php endif ; ?>
	</div>
</div>

<div id="div_menu_expand" style="display: none; position: absolute; top: -390px; left: 0px; padding-bottom: 55px; width: 100%; background: url( <?php echo $CONF["BASE_URL"] ?>/pics/bg_header_shadow.png ) repeat-x #49586C; background-position: bottom; border-bottom: 1px solid #2D3B4A; color: #FFFFFF;  text-shadow: none; z-Index: 10;">
	<div style="width: 970px; margin: 0 auto; padding-top: 40px;">
		<table cellspacing=0 cellpadding=2 border=0>
		<tr>
			<td style=""><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_home.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["depts"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_depts.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_ops.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_icons.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["icons"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_icons.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["code"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_code.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["trans"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_chats.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_trans.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["traffic"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_marketing.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["extras"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_extras.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
			<td style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["settings"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/menu_settings.png" width="12" height="12" border="0" alt="" style="padding: 2px; background: #49586C;" class="round"></td>
		</tr>
		<tr>
			<td valign="top"><a href="<?php echo $CONF["BASE_URL"] ?>/setup/" style="font-weight: bold; color: #FFFFFF;">Home</a></td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["depts"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;">
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/depts.php" style="font-weight: bold; color: #FFFFFF;">Departments</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/dept_display.php" style="color: #FFFFFF;">Department Select Display Order</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/dept_groups.php" style="color: #FFFFFF;">Dept Groups</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/dept_canned_cats.php" style="color: #FFFFFF;">Canned Response Categories</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/ops.php" style="font-weight: bold; color: #FFFFFF;">Operators</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/ops.php?jump=assign" style="color: #FFFFFF;">Assign Operator</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface_op_pics.php" style="color: #FFFFFF;">Profile Picture</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/ops_reports.php" style="color: #FFFFFF;">Online/Offline Log</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/ops.php?jump=monitor" style="color: #FFFFFF;">Status Monitor</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/ops.php?jump=online" style="color: #FFFFFF;">Go ONLINE</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["interface"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface.php?jump=logo" style="font-weight: bold; color: #FFFFFF;">Interface</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface.php?jump=logo" style="color: #FFFFFF;">Logo & Win Size</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface_themes.php" style="color: #FFFFFF;">Theme</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface_custom.php" style="color: #FFFFFF;">Form Fields</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface_lang.php" style="color: #FFFFFF;">Update Texts</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/code_autostart.php" style="color: #FFFFFF;">Automatic Start Chat</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface_gdpr.php" style="color: #FFFFFF;">Consent Checkbox</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface_chat_msg.php" style="color: #FFFFFF;">Chat End Msg</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/interface.php?jump=time" style="color: #FFFFFF;">Timezone</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/code_settings.php" style="color: #FFFFFF;">Settings</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["icons"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/icons.php" style="font-weight: bold; color: #FFFFFF;">Chat Icons</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/icons.php?jump=alttext" style="color: #FFFFFF;">Alt Text</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/icons.php?jump=iconsettings" style="color: #FFFFFF;">Mobile Behavior</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["code"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/code.php" style="font-weight: bold; color: #FFFFFF;">HTML Code</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/code_invite.php" style="color: #FFFFFF;">Automatic Invite</a></div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/proaction/proaction.php" ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/proaction/proaction.php" style="color: #FFFFFF;">ProAction Invite</a></div><?php endif ; ?>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/code_mapper/code_mapper.php" ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/code_mapper/code_mapper.php" style="color: #FFFFFF;">Code Mapper</a></div><?php endif ; ?>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["trans"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/transcripts.php" style="font-weight: bold; color: #FFFFFF;">Transcripts</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/transcripts_tags.php" style="color: #FFFFFF;">Tags</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["reports"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_chat.php" style="font-weight: bold; color: #FFFFFF;">Reports</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_chat_active.php" style="color: #FFFFFF;">Active Chats</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_chat_missed.php" style="color: #FFFFFF;">Missed Chats</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_chat_msg.php" style="color: #FFFFFF;">Offline Msg</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["traffic"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_traffic.php" style="font-weight: bold; color: #FFFFFF;">Traffic</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_traffic.php" style="color: #FFFFFF;">Footprints</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_refer.php" style="color: #FFFFFF;">Refer URLs</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/reports_traffic.php?jump=settings" style="color: #FFFFFF;">Settings</a></div>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["extras"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/marketing_click.php" style="font-weight: bold; color: #FFFFFF;">Extras</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/marketing_click.php" style="color: #FFFFFF;">Marketing</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/extras.php?jump=external" style="color: #FFFFFF;">External URLs</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/extras_geo.php" style="color: #FFFFFF;">GeoIP</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/extras_geo.php?jump=geomap" style="color: #FFFFFF;">Google Maps</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/emoticons/emo.php" style="color: #FFFFFF;">Emoticons</a></div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/gravatar/gravatar.php" ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/gravatar/gravatar.php" style="color: #FFFFFF;">Gravatar</a></div><?php endif ; ?>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/extras.php?jump=apis" style="color: #FFFFFF;">Dev APIs</a></div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/smtp/smtp.php" ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/smtp/smtp.php" style="color: #FFFFFF;">SMTP</a></div><?php endif ; ?>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/phplivebot.php" ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/phplivebot/phplivebot.php" style="color: #FFFFFF;">Chat Bot</a></div><?php endif ; ?>
			</td>
			<td valign="top" style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["settings"] ) ) ) ? "display: none;" : "" ; ?> padding-left: 10px;" nowrap>
				<a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php" style="font-weight: bold; color: #FFFFFF;">Settings</a>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php" style="color: #FFFFFF;">Exclude IPs</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=sips" style="color: #FFFFFF;">Blocked IPs</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=props" style="color: #FFFFFF;">Autocorrect</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=charset" style="color: #FFFFFF;">Charset</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=cookie" style="color: #FFFFFF;">Cookies</a></div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/ldap/ldap.php" ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/addons/ldap/ldap.php" style="color: #FFFFFF;">LDAP</a></div><?php endif ; ?>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=upload" style="color: #FFFFFF;">File Upload</a></div>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/mapp/settings.php" style="color: #FFFFFF;">Mobile App</a></div>
				<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/mapp/settings.php" ) && ( $admininfo["adminID"] == 1 ) ): ?><div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=profile" style="color: #FFFFFF;">Password</a></div><?php endif ; ?>
				<div style="margin-top: 15px;"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/system.php" style="color: #FFFFFF;">System</a></div>
			</td>
		</tr>
		<tr>
			<td colspan=3 style="padding-top: 25px;">
				<div style="display: inline-block; *display: inline; zoom: 1;">
					<span class="info_error" onClick="toggle_navigation();" style="cursor: pointer;">close</span>
				</div>
			</td>
			<td colspan=8 align="right"><?php if ( is_file( "$CONF[DOCUMENT_ROOT]/setup/inc_menu_extra.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_menu_extra.php" ) ; } else { print "&nbsp;" ; } ?></td>
		</tr>
		</table>
	</div>
</div>

<div style="width: 100%; padding-top: 25px;" onClick="close_navigation()">
	<div style="width: 970px; margin: 0 auto;">
		<table cellspacing=0 cellpadding=0 border=0 width="100%">
		<tr>
			<td width="100%" style="padding-left: 310px;">
				<div id="div_greeting" style="display: none; font-size: 20px; color: #485C73; text-shadow: 1px 1px #ECF4FC;">
					<?php echo ( $admininfo["isadmin"] ) ? "Setup Admin" : "&nbsp;" ; ?>
				</div>
			</td>
			<td align="right">
				<table cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td width="160" nowrap style="<?php echo ( !$admininfo["isadmin"] && ( count( $admininfo["access"] ) && !isset( $admininfo["access"]["ops"] ) ) ) ? "display: none;" : "" ; ?>">
						<div style="padding: 10px; text-align: center; background: #8BCF92; border: 1px solid #82C289;" class="round_top"><img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/bulb_big.png" width="16" height="16" border="0" alt="" id="img_bulb"> <a href="<?php echo $CONF["BASE_URL"] ?>/setup/ops.php?jump=online" style="color: #FFFFFF;">Go <span style="">ONLINE!</span></a></div>
					</td>
					<td align="right" valign="bottom" style="padding-left: 10px;" nowrap>
						<div style="padding: 10px; text-align: center;" class="round_top">
							<span class="edit_title">
								<img src="<?php echo $CONF["BASE_URL"] ?>/pics/icons/settings.png" width="16" height="16" border="0" alt="">
								<?php if ( $admininfo["status"] != -1 ): ?>
								<a href="<?php echo $CONF["BASE_URL"] ?>/setup/settings.php?jump=profile"><?php echo $admininfo["login"] ?></a>
								<?php else: echo $admininfo["login"] ; endif ; ?>
							</span> &nbsp; &nbsp; [ <a href="<?php echo $CONF["BASE_URL"] ?>/logout.php?action=logout&menu=sa">logout</a> ]
						</div>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
	</div>
	<div style="width: 970px; margin: 0 auto; padding: 25px; padding-bottom: 100px;" class="round" id="div_body_main">
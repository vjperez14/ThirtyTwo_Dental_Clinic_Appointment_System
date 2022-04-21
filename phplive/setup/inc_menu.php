		<script data-cfasync="false" type="text/javascript">
		<!--
		function show_div( thediv )
		{
			var divs = Array( "marketing", "external", "apis", "smtp", "emoticons", "phplivebot", "gravatar", "voice_chat" ) ;
			for ( var c = 0; c < divs.length; ++c )
			{
				$('#extras_'+divs[c]).hide() ;
				$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
			}

			$('input#jump').val( thediv ) ;
			$('#extras_'+thediv).show() ;
			$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
		}
		//-->
		</script>
		<?php
			$addon_smtp = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/smtp/smtp.php" ) ) ? 1 : 0 ;
			$addon_phplivebot = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/phplivebot/phplivebot.php" ) ) ? 1 : 0 ;
			$addon_gravatar = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/gravatar/API/Util_Gravatar.php" ) ) ? 1 : 0 ;
			$addon_voice_chat = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/voice_chat/voice_chat.php" ) ) ? 1 : 0 ;
		?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/marketing_click.php'" id="menu_marketing" style="margin-left: 0px;">Marketing</div>
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/extras.php?jump=external'" id="menu_external">External URLs</div>
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/extras_geo.php'" id="menu_geoip">GeoIP</div>
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/extras_geo.php?jump=geomap'" id="menu_geomap">Google Maps</div>
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/emoticons/emo.php'" id="menu_emoticons">Emoticons</div>
			<?php if ( $addon_gravatar ): ?><div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/gravatar/gravatar.php'" id="menu_gravatar">Gravatar</div><?php endif ; ?>
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/extras.php?jump=apis'" id="menu_apis">Dev APIs</div>
			<?php if ( $addon_smtp ): ?><div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/smtp/smtp.php'" id="menu_smtp">SMTP</div><?php endif ; ?>
			<?php if ( $addon_voice_chat ): ?><div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/voice_chat/voice_chat_admin.php'" id="menu_voice_chat">Voice Chat</div><?php endif ; ?>
			<?php if ( $addon_phplivebot ): ?><div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/phplivebot/phplivebot.php'" id="menu_phplivebot">Chat Bot</div><?php endif ; ?>
			<div style="clear: both"></div>
		</div>
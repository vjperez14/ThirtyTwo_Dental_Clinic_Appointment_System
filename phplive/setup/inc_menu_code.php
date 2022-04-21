		<script data-cfasync="false" type="text/javascript">
		<!--
		function show_div( thediv )
		{
			var divs = Array( "code_main", "code_autostart", "code_settings", "code_invite", "code_proaction", "code_mapper" ) ;
			for ( var c = 0; c < divs.length; ++c )
			{
				$('#menu_'+divs[c]).removeClass('op_submenu_focus').addClass('op_submenu') ;
			}

			$('#menu_'+thediv).removeClass('op_submenu').addClass('op_submenu_focus') ;
		}
		//-->
		</script>
		<?php $addon_proaction = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/proaction/proaction.php" ) ) ? 1 : 0 ; ?>
		<?php $addon_code_mapper = ( is_file( "$CONF[DOCUMENT_ROOT]/addons/code_mapper/code_mapper.php" ) ) ? 1 : 0 ; ?>
		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/code.php'" id="menu_code_main">HTML Code</div>
			<div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/setup/code_invite.php'" id="menu_code_invite">Automatic Chat Invite</div>
			<?php if ( $addon_proaction ): ?><div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/proaction/proaction.php'" id="menu_code_proaction">ProAction Invite</div><?php endif ; ?>
			<?php if ( $addon_code_mapper ): ?><div class="op_submenu" onClick="location.href='<?php echo $CONF["BASE_URL"] ?>/addons/code_mapper/code_mapper.php'" id="menu_code_mapper">HTML Code Mapper</div><?php endif ; ?>
			<div style="clear: both"></div>
		</div>
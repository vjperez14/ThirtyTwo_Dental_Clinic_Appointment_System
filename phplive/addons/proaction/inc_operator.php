<div style="display: none; position: absolute; top: 0px; right: 0px; background: #FFFFFF; color: #2F3535; padding: 15px; box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2); width: 255px; cursor: pointer; z-Index: 1000; border-radius: 5px;" id="proaction_box" onClick="toggle_proaction_box(0, '')">
	<div style="text-align: center; cursor: pointer;" class="info_error"><img src="<?php echo $CONF["BASE_URL"] ?>/themes/<?php echo $theme ?>/close_extra.png" width="16" height="16" border="0"> <?php echo ( isset( $LANG["CHAT_CLOSE"] ) ) ? $LANG["CHAT_CLOSE"] : "Close" ; ?></div>
	<div style="margin-top: 5px;"><img src="../addons/proaction/pics/proaction.png" width="16" height="16" border="0" alt=""> Clicked from ProAction Invite</div>
	<div style="margin-top: 15px;" id="div_proaction_content"></div>
</div>

<script data-cfasync="false" type="text/javascript">
<!--
	function toggle_proaction_box( theforce_close, theproid )
	{
		if ( $('#proaction_box').is(':visible') || theforce_close )
		{
			$('#proaction_box').fadeOut("fast") ;
		}
		else
		{
			if ( typeof( addon_proactions[theproid] ) != "undefined" )
			{
				$('#div_proaction_content').html( decodeURIComponent( addon_proactions[theproid] ) ) ;
				$("#proaction_box").center().fadeIn("fast") ;
			}
			else
				do_alert( 0, "Proaction information not found." ) ;
		}
	}
//-->
</script>
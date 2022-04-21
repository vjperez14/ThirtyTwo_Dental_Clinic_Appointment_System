<div style="display: none; position: absolute; top: 0px; left: 0px; padding: 2px; width: 245px; height: 195px; overflow: auto; box-shadow: 0px 0px 25px rgba(0, 0, 0, 0.2); z-Index: 1000;" class="info_content" id="div_screenshot_confirm">
	<div style="text-align: center; cursor: pointer;" class="info_error" onClick="close_misc('all')"><img src="<?php echo $CONF["BASE_URL"] ?>/themes/<?php echo $theme ?>/close_extra.png" width="16" height="16" border="0"> <?php echo ( isset( $LANG["CHAT_CLOSE"] ) ) ? $LANG["CHAT_CLOSE"] : "Close" ; ?></div>
	<div style="padding: 10px;">
		Take a website screenshot and send?
		<div style="margin-top: 15px;" id="div_screenshot_btn"><input type="submit" value="Send Screenshot" style="margin-top: 10px; padding: 10px;" id="btn_send_screenshot" class="input_op_button" onClick="screenshot_take()"></div>
		<div style="display: none; margin-top: 15px;" id="div_screenshot_loading"><img src="./themes/<?php echo $theme ?>/loading_chat.gif" width="16" height="16" border="0" alt="" class="round"></div>
		<div style="display: none; margin-top: 15px;" id="div_screenshot_nosupport" class="info_error">Screenshot is not available for this browser.  Please consider using a modern browser (example: Chrome).</div>
	</div>
</div>
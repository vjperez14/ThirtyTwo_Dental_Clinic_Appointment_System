<div id="div_crop_wrapper" style="display: none; position: absolute; top: 0px; left: 0px; width: 10px; height: 10px; background: url( ../themes/initiate/bg_trans_darker.png ) repeat; overflow: hidden; z-Index: 1001;"></div>
<div id="div_crop_body" class="info_white" style="display: none; position: absolute; width: 1000px; z-Index: 1002;">
	<div style="text-align: center; cursor: pointer;" onClick="close_crop()" class="info_error"><img src="../pics/icons/close.png" width="16" height="16" border="0" alt=""> close</div>

	<div id="div_crop_canvas" style="margin-top: 35px;">
		<table cellspacing=0 cellpadding=0 border=0 width="100%">
		<tr>
			<td valign="top">
				<div style="padding: 25px; height: 400px; overflow: auto;">
					<div class="modal-body">
						<img id="image" src="../pics/profile.png" style="width: 100%; height: auto;" border=0>
					</div>
				</div>
			</td>
			<td valign="top" style="padding-left: 25px;">
				<!-- placeholder profile_original required for upload process -->
				<div style="display: none; width: 50px; height: 50px;"><img class="rounded" id="profile_original" src="../pics/profile.png" style="width: 100%;"></div>

				<div class="info_neutral">
					Cropped Image Preview
					<div class="preview" style="margin-top: 10px; width: 55px; height: 55px; overflow: hidden; border: 1px solid #DFDFDF; border-radius: 50%; box-shadow: 0 0 5px 5px #E6E6E6;"><img src="../pics/profile.png" style="width: 100%;" border=0></div>

					<div id="div_buttons_crop" style="margin-top: 15px;">
						<button type="button" class="btn" id="btn_crop">Upload Image</button>
						<div style="margin-top: 15px;">or &nbsp; <a href="JavaSCript:void(0)" onClick="close_crop()">cancel</a></div>
					</div>
				</div>

				<div style="display: none; margin-top: 15px;" class="alert"></div>

				<div id="div_optional" style="margin-top: 55px; text-align: justify;">
					<b>Optional:</b> If you prefer to upload the original image without cropping, click the button below.  This method is suggested for smaller sized images to retain image sharpness.

					<div style="margin-top: 5px;"><input type="submit" value="Upload Original" style="margin-top: 10px;" class="btn"></div>
				</div>

				<div id="div_browser_crop" style="display: none; margin-top: 45px; text-align: justify;" class="info_error">
					This browser does not support image cropping.  Please consider using a modern browsers (example: Google Chrome).  Click the button to upload the original image.

					<div style="margin-top: 5px;"><input type="submit" value="Upload Original" style="margin-top: 10px;" class="btn" id="btn_upload_original"></div>
				</div>
			</td>
		</tr>
		</table>
	</div>
</div>
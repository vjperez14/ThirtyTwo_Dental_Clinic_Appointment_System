<?php
	HEADER( "Content-type: text/css" ) ;
	$theme = isset( $_GET["theme"] ) ? $_GET["theme"] : "" ;
	$theme = preg_replace( "/[^a-z_]/i", "", $theme ) ;
	if ( $theme ):
?>
	<?php if ( $theme == "clouds" ): ?>
		.cb{ background: #CDF1FF; color: #486899; }
	<?php elseif ( $theme == "hearts" ): ?>
		.cb{ background: #9C090A; color: #FFE7E1; }
	<?php elseif ( $theme == "home" ): ?>
		.cb{ background: #C6F8FF; color: #3399FF; }
	<?php elseif ( $theme == "island" ): ?>
		.cb{ background: #C6F8FF; color: #3399FF; }
	<?php elseif ( $theme == "leaves" ): ?>
		.cb{ background: #E3D8B7; border: 1px solid #DCD1B1; }
	<?php elseif ( $theme == "notblue" ): ?>
		.cb{ background: #F72FAC; }
	<?php elseif ( $theme == "safari" ): ?>
		.cb{ background: url( ../themes/safari/bg_co.png ) repeat; padding: 15px; color: #0C0B06; }
	<?php elseif ( $theme == "very_pastel" ): ?>
		.cb{ background: #FFFFCF; }
	<?php elseif ( $theme == "whiteout" ): ?>
		.cb{ background: #EFEFF1; border: 1px solid #E5E5E7; }
	<?php elseif ( $theme == "winterland" ): ?>
		.cb{ background: #DEE9FE; color: #486899; }
	<?php endif ; ?>
<?php endif ; ?>
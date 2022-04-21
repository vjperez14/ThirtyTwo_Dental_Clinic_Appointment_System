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

	if ( is_file( "$CONF[DOCUMENT_ROOT]/API/Util_Extra_Pre.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload_.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Upload.php" ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$error = "" ; $embed = 1 ; $theme = $CONF["THEME"] ;

	$opid = Util_Format_Sanatize( Util_Format_GetVar( "opid" ), "n" ) ;
	$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "n" ) ;

	$departments_pre = Depts_get_AllDepts( $dbh, "display ASC, name ASC" ) ;
	$departments_visible = Array() ;
	for ( $c = 0; $c < count( $departments_pre ); ++$c )
	{
		$department_temp = $departments_pre[$c] ;
		if ( $department_temp["visible"] ) { $departments_visible[] = $department_temp ; }
	}
	$profile_url = Util_Upload_GetLogo( "profile", $opid ) ;

	if ( isset( $THEMES_EXCLUDE[$theme] ) ) { $theme = "default" ; }

	if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../themes/<?php echo $theme ?>/style.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		if ( <?php echo $value ?> )
			$('#div_online_pics').show() ;
	});
//-->
</script>
</head>
<body style="overflow: hidden;">

<div id="chat_canvas" style="min-height: 100%; width: 100%;">
	<div id="chat_body" style="padding: 10px; padding-top: 25px; height: 500px;">

		<div style="display: none; padding-top: 15px; margin-bottom: 15px;" class="info_content" id="div_online_pics">
			<center>
			<table cellspacing=0 cellpadding=2 border=0>
			<tr>
				<td style="padding-left: 4px; padding-right: 4px;" id="td_pic_1"><img src="<?php echo $profile_url ?>" width="55" height="55" border="0" alt="" style="border-radius: 50%;"></td>
			</tr>
			<tr><td colspan=3><div class="info_good" style="text-align: center;">Online</div></td></tr>
			</table>
			</center>
		</div>

		<div id="chat_text_header" style="margin-bottom: 5px;"><span id="LANG_CHAT_WELCOME">Welcome to our Live Chat</span></div>
		<div id="chat_text_header_sub" style=""><span id="LANG_CHAT_WELCOME_SUBTEXT">To better assist you, please provide the following information.</span></div>
		<div style="margin-top: 15px;"><span id="chat_text_department"><span id="LANG_TXT_DEPARTMENT">Department</span></span></div>
		<select id="vdeptid" style="width: 100%; -webkit-appearance: none;"><option value=0>- select department -</option>
		<?php
			$selected = "" ;
			for ( $c = 0; $c < count( $departments_visible ); ++$c )
			{
				$department = $departments_visible[$c] ;
				print "<option value=\"$department[deptID]\">$department[name]</option>" ;
			}
		?>
		</select>

	</div>
</div>

</body>
</html>

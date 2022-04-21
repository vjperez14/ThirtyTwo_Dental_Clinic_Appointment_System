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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$page = Util_Format_Sanatize( Util_Format_GetVar( "page" ), "n" ) ;
	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$option = Util_Format_Sanatize( Util_Format_GetVar( "option" ), "n" ) ;
	$deptid = Util_Format_Sanatize( Util_Format_GetVar( "deptid" ), "n" ) ;
	$bgcolor = Util_Format_Sanatize( Util_Format_GetVar( "bgcolor" ), "ln" ) ;
	$jump = Util_Format_Sanatize( Util_Format_GetVar( "jump" ), "ln" ) ;

	$copy_all = Util_Format_Sanatize( Util_Format_GetVar( "copy_all" ), "n" ) ;

	$departments = Depts_get_AllDepts( $dbh ) ;

	if ( $action === "update_email" )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/update.php" ) ;

		$temail = Util_Format_Sanatize( Util_Format_GetVar( "temail" ), "n" ) ;
		$aemail = Util_Format_Sanatize( Util_Format_GetVar( "aemail" ), "n" ) ;
		$temaild = Util_Format_Sanatize( Util_Format_GetVar( "temaild" ), "n" ) ;
		$emailt = Util_Format_Sanatize( Util_Format_GetVar( "emailt" ), "e" ) ;
		$emailt_bcc = Util_Format_Sanatize( Util_Format_GetVar( "emailt_bcc" ), "n" ) ;
		$femail = Util_Format_Sanatize( Util_Format_GetVar( "femail" ), "n" ) ;
		$message = preg_replace( "/<script(.*?)<\/script>/i", "", Util_Format_Sanatize( Util_Format_GetVar( "message" ), "" ) ) ;

		$table_name = "msg_email" ;

		if ( $copy_all )
		{
			for( $c = 0; $c < count( $departments ); ++$c )
			{
				Depts_update_DeptValues( $dbh, $departments[$c]["deptID"], Array( "temail"=>$temail, "aemail"=>$aemail, "temaild"=>$temaild, "emailt"=>$emailt, "emailt_bcc"=>$emailt_bcc, $table_name=>$message ) ) ;
				Depts_update_DeptVarsValue( $dbh, $departments[$c]["deptID"], "trans_f_dept", $femail ) ;
			}
		}
		else
		{
			Depts_update_DeptValues( $dbh, $deptid, Array( "temail"=>$temail, "aemail"=>$aemail, "temaild"=>$temaild, "emailt"=>$emailt, "emailt_bcc"=>$emailt_bcc, $table_name=>$message ) ) ;
			Depts_update_DeptVarsValue( $dbh, $deptid, "trans_f_dept", $femail ) ;
		}
	}

	$deptinfo = Depts_get_DeptInfo( $dbh, $deptid ) ;
	$deptvars = Depts_get_DeptVars( $dbh, $deptid ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/".Util_Format_Sanatize($deptinfo["lang"], "ln").".php" ) ;
	$deptname = $deptinfo["name"] ;

	$message = $deptinfo["msg_email"] ;
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=<?php echo $LANG["CHARSET"] ?>">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	var winname = unixtime() ;
	var option = <?php echo $option ?> ; // used to communicate with depts.php to toggle iframe

	$(document).ready(function()
	{
		$.ajaxSetup({ cache: false }) ;
		$("body, html").css({'background-color': '#<?php echo $bgcolor ?>'}) ;

		<?php if ( ( $action === "update_email" ) && !$error ): ?>
		do_alert( 1, "Update Success" ) ;
		<?php elseif ( $error ): ?>
		do_alert( 0, "<?php echo $error ?>" ) ;
		<?php endif ; ?>

		show_div( "<?php echo ( $jump == "template" ) ? "template" : "email" ; ?>" ) ;
	});

	function do_submit_settings()
	{
		var emailt = $('#emailt').val().replace(/\s/g,'') ;
		$('#emailt').val(emailt) ;

		if ( emailt && !check_email( emailt ) )
			do_alert( 0, "Email format is invalid. (example: you@domain.com)" ) ;
		else if ( emailt && ( "<?php echo $deptinfo["email"] ?>" == emailt ) )
			do_alert( 0, "Email address must be different then the department email." ) ;
		else
			$('#form_settings').submit() ;
	}

	function show_div( thediv )
	{
		$('#jump').val( thediv ) ;
		if ( thediv == "template" )
		{
			$('#menu2_trans_email').removeClass("op_submenu_focus").addClass("op_submenu") ;
			$('#menu2_trans_template').removeClass("op_submenu").addClass("op_submenu_focus") ;
			$('#div_email').hide() ; $('#div_template').show() ;
		}
		else
		{
			$('#menu2_trans_template').removeClass("op_submenu_focus").addClass("op_submenu") ;
			$('#menu2_trans_email').removeClass("op_submenu").addClass("op_submenu_focus") ;
			$('#div_template').hide() ; $('#div_email').show() ;
		}
	}
//-->
</script>
</head>
<body>

<div id="iframe_body" style="height: 440px; padding: 10px; <?php echo ( $bgcolor ) ? "background: #$bgcolor;" : "" ?>">
	<form action="iframe_edit_4.php" id="form_settings" method="POST" accept-charset="<?php echo $LANG["CHARSET"] ?>">
	<input type="hidden" name="action" id="action" value="update_email">
	<input type="hidden" name="deptid" value="<?php echo $deptid ?>">
	<input type="hidden" name="option" value="<?php echo $option ?>">
	<input type="hidden" name="bgcolor" value="<?php echo $bgcolor ?>">
	<input type="hidden" name="jump" id="jump" value="">
	<div id="">
		<div class="op_submenu"  style="margin-left: 0px; padding: 8px;" onClick="show_div('email')" id="menu2_trans_email">Visitor Email Transcript</div>
		<div class="op_submenu_focus" style="padding: 8px;" onClick="show_div('template')" id="menu2_trans_template">Transcript Message Template</div>
		<div style="clear: both"></div>
	</div>

	<div id="div_email" style="display: none;">
		<div style="padding-bottom: 15px; text-align: justify;">
			<div style="float: left; height: 250px; width: 360px" class="info_info round_top_none">
				<div style="font-weight: bold; font-size: 14px;">Visitor Email Transcript</div>
				<div style="margin-top: 5px;">Provide visitors an option to send the chat transcript to their email addresss during a chat session?  Selecting "No" will hide the email transcript icon <img src="../themes/default/email.png" width="16" height="16" border="0" alt=""> during a chat session.</div>
				<div style="margin-top: 10px;">
					<div class="li_op round" style="cursor: pointer;" onclick="$('#temail_1').prop('checked', true)"><input type="radio" name="temail" id="temail_1" value="1" checked> Yes </div>
					<div class="li_op round" style="cursor: pointer;" onclick="$('#temail_0').prop('checked', true)"><input type="radio" name="temail" id="temail_0" value="0"> No</div>
					<div style="clear: both;"></div>
				</div>
				<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 10px;"></div>

				<div style="margin-top: 10px; font-weight: bold; font-size: 14px;">Automatically Email Transcript to the Visitor</div>
				<div style="margin-top: 5px;">Automatically email the chat transcript to the visitor when the chat session ends?</div>
				<?php if ( $deptinfo["remail"] ): ?>
				<div style="margin-top: 10px;">
					<div class="li_op round" style="cursor: pointer;" onclick="$('#aemail_1').prop('checked', true)"><input type="radio" name="aemail" id="aemail_1" value="1" checked> Yes </div>
					<div class="li_op round" style="cursor: pointer;" onclick="$('#aemail_0').prop('checked', true)"><input type="radio" name="aemail" id="aemail_0" value="0"> No</div>
					<div style="clear: both;"></div>
				</div>
				<?php else: ?>
				<div style="margin-top: 10px;" class="info_error">To enable the Automatic Email Transcript feature, the "Email" field must be set to "required" <a href="interface_custom.php?deptid=<?php echo $deptid ?>" target="_parent">on the chat request form</a>.</div>
				<?php endif ; ?>
			</div>
			<div style="float: left; margin-left: 2px; height: 250px; width: 360px;" class="info_info round_top_none">
				<div style="font-weight: bold; font-size: 14px;">Department Copy</div>
				<div style="margin-top: 5px;">Automatically send a copy of the chat transcript to the <a href="JavaScript:void(0)" onClick="parent.blink_td_email(<?php echo $deptid ?>)">department email address</a> when the chat session ends?</div>
				<div style="margin-top: 10px;">
					<div class="li_op round" style="cursor: pointer;" onclick="$('#temaild_1').prop('checked', true)"><input type="radio" name="temaild" id="temaild_1" value="1" checked> Yes</div>
					<div class="li_op round" style="cursor: pointer;" onclick="$('#temaild_0').prop('checked', true)"><input type="radio" name="temaild" id="temaild_0" value="0"> No</div>
					<div style="clear: both;"></div>
				</div>
				<div style="margin-top: 15px; background: url( ../pics/dotted_line.png ) repeat-x; height: 10px;"></div>

				<div style="margin-top: 15px; font-weight: bold; font-size: 14px;">Additional Copy</div>
				<div style="margin-top: 5px;">Send a copy of the chat transcript to the following email address when the chat session ends? (leave blank to inactivate)</div>
				<div style="margin-top: 10px;">
					<input type="text" class="input" style="width: 50%" id="emailt" name="emailt" maxlength="160" value="<?php echo $deptinfo["emailt"] ?>" onKeyPress="return justemails(event)">
				</div>
				<div style="display: none; margin-top: 10px;">
					<input type="checkbox" name="emailt_bcc" id="emailt_bcc" value=1 class="select" <?php echo ( $deptinfo["emailt_bcc"] ) ? "checked" : "" ; ?>> send as BCC
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		<script data-cfasync="false" type="text/javascript">
		<!--
			$( "input#temail_"+<?php echo $deptinfo["temail"] ?> ).prop( "checked", true ) ;
			$( "input#temaild_"+<?php echo $deptinfo["temaild"] ?> ).prop( "checked", true ) ;
			$( "input#aemail_"+<?php echo $deptinfo["aemail"] ?> ).prop( "checked", true ) ;
		//-->
		</script>
	</div>
	<div id="div_template" style="display: none; padding-bottom: 15px;" class="info_info round_top_none">
		The following template will be used to format the transcript email message.
		<div style="margin-top: 25px;">
			From: 
			<span class="info_neutral" style="cursor: pointer;" onclick="$('#femail_op').prop('checked', true)"><input type="radio" name="femail" id="femail_op" value="0" <?php echo ( !isset( $deptvars["trans_f_dept"] ) || !$deptvars["trans_f_dept"] ) ? "checked" : "" ; ?>> Operator Email Address</span>
			<span class="info_neutral" style="margin-left: 5px; cursor: pointer;" onclick="$('#femail_dept').prop('checked', true)"><input type="radio" name="femail" id="femail_dept" value="1" <?php echo ( isset( $deptvars["trans_f_dept"] ) && $deptvars["trans_f_dept"] ) ? "checked" : "" ; ?>> Department Email Address</span>
		</div>
		<div style="margin-top: 15px;">
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td valign="top" width="300" nowrap>
					<textarea type="text" cols="50" rows="8" id="message" name="message" style="resize: vertical;"><?php echo preg_replace( "/\"/", "&quot;", $message ) ?></textarea>
				</td>
				<td valign="top" width="100%" style="padding-left: 15px;">
					Variables that will be pre-populated:
					<div style="margin-top: 5px;">
						<div><span style="font-weight: bold; color: #427EEC;">%%transcript%%</span> = the chat transcript</div>
						<div style="margin-top: 3px;"><span style="font-weight: bold; color: #427EEC;">%%visitor%%</span> = visitor's name</div>
						<div style="margin-top: 3px;"><span style="font-weight: bold; color: #427EEC;">%%operator%%</span> = operator name</div>
						<div style="margin-top: 3px;"><span style="font-weight: bold; color: #427EEC;">%%op_email%%</span> = operator email</div>
						<div style="margin-top: 3px;"><span style="font-weight: bold; color: #427EEC;">%%department%%</span> = department name</div>
						<div style="margin-top: 3px;"><span style="font-weight: bold; color: #427EEC;">%%dept_email%%</span> = department email</div>
						<div style="margin-top: 3px;"><span style="font-weight: bold; color: #427EEC;">%%chatid%%</span> = chat ID of the chat session</div>
					</div>
				</td>
			</tr>
			</table>
		</div>
	</div>

	<?php if ( count( $departments ) > 1 ) : ?>
	<div style="margin-top: 15px;"><input type="checkbox" id="copy_all" name="copy_all" value=1> copy this update (<b>Visitor Email Transcript</b> and <b>Transcript Message Template</b>) to all departments</div>
	<?php endif ; ?>

	<div style="margin-top: 15px;"><input type="button" value="Update" class="btn" onClick="do_submit_settings()"> &nbsp; &nbsp; <a href="JavaScript:void(0)" onClick="parent.do_options( <?php echo $option ?>, <?php echo $deptid ?> );">cancel</a></div>
	</form>
</div>

</body>
</html>
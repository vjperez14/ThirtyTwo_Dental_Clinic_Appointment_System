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

	include_once( "$CONF[DOCUMENT_ROOT]/API/Messages/get.php" ) ;

	$messageid = Util_Format_Sanatize( Util_Format_GetVar( "messageid" ), "n" ) ;

	$message = Messages_get_MessageByID( $dbh, $messageid ) ; $subject = "" ;
	if ( isset( $message["messageID"] ) )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Messages/update.php" ) ;

		$deptinfo = Depts_get_DeptInfo( $dbh, $message["deptID"] ) ;

		$to = "$deptinfo[name] &lt;$deptinfo[email]&gt;" ;
		$subject = htmlentities( $message["subject"] ) ;
		$created = date( "M j, Y ($VARS_TIMEFORMAT)", $message["created"] ) ;
		if ( preg_match( "/^http/", $message["onpage"] ) )
		{
			$onpage_snap = ( strlen( $message["onpage"] ) > 80 ) ? substr( $message["onpage"], 0, 40 ) . "..." . substr( $message["onpage"], -40, strlen( $message["onpage"] ) ) : $message["onpage"] ;
			$onpage = "<a href=\"$message[onpage]\" target=_blank alt=\"$message[onpage]\" title=\"$message[onpage]\">$onpage_snap</a>" ;
		}
		else
			$onpage = $message["onpage"] ;
		$message_body = preg_replace( "/(\r\n)|(\n)|(\r)/", "<br>", htmlentities( $message["message"] ) ) ;
	}
	else
	{
		if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
		print "Invalid message ID." ;
	}
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> View Offline Message </title>

<meta name="description" content="PHP Live! Support <?php echo $VERSION ?>">
<meta name="keywords" content="powered by: PHP Live!  www.phplivesupport.com">
<meta name="robots" content="all,index,follow">
<meta http-equiv="content-type" content="text/html; CHARSET=utf-8">
<?php include_once( "../inc_meta_dev.php" ) ; ?>

<link rel="Stylesheet" href="../css/setup.css?<?php echo $VERSION ?>">
<script data-cfasync="false" type="text/javascript" src="../js/global.js?<?php echo $VERSION ?>"></script>
<script data-cfasync="false" type="text/javascript" src="../js/framework.js?<?php echo $VERSION ?>"></script>

<script data-cfasync="false" type="text/javascript">
<!--
	$(document).ready(function()
	{
		$("html, body").css({'background': '#FAFAFA'}) ;

		var custom_found = 0 ;
		var custom_raw = "<?php echo $message["custom"] ?>" ;
		var custom_array = custom_raw.split("-cus-") ;
		var custom_string = "<img src=\"../pics/icons/pin_note.png\" width=\"14\" height=\"14\" border=\"0\" alt=\"\"> Custom Fields<div style='margin-top: 5px; max-height: 100px; overflow: auto;'>" ;
		for ( var c = 0; c < custom_array.length; ++c )
		{
			if ( custom_array[c] != 0 )
			{
				var custom_val = custom_array[c].split("-_-") ;
				if ( custom_val[1] )
				{
					custom_found += 1 ;
					var custom_value = decodeURIComponent( custom_val[1] ) ;
					if ( custom_value.match( /^http/ ) )
					{
						var custom_value_snap = ( custom_value.length > 60 ) ? custom_value.substring( 0, 30 ) + "..." + custom_value.substring( custom_value.length-30, custom_value.length ) : custom_value ;
						custom_string += "<div style=\"padding: 2px;\"><b>"+decodeURIComponent( custom_val[0] )+"</b> <a href=\""+custom_value+"\" target=_blank>"+custom_value_snap+"</a></div>" ;
					}
					else
						custom_string += "<div style=\"padding: 2px;\"><b>"+decodeURIComponent( custom_val[0] )+":</b> "+decodeURIComponent( custom_val[1] )+"</div>" ;
				}
			}
		}
		custom_string += "</div>" ;
		if ( custom_found ) { $('#custom_variables').html( custom_string ).show() ; }
	});

	function delete_message()
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( confirm( "Really delete this message?" ) )
		{
			$.ajax({
			type: "POST",
			url: "../ajax/setup_actions.php",
			data: "action=delete_message&messageid=<?php echo $messageid ?>&unique="+unique,
			success: function(data){
				eval( data ) ;

				if ( json_data.status )
				{
					window.opener.delete_message() ;
					window.close() ;
				}
				else
					do_alert( "Message could not be deleted." ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				do_alert( 0, "Error deleting message.  Please refresh the page and try again." ) ;
			} });
		}
	}
//-->
</script>
</head>
<body style="">
	<div style="padding: 25px; background: #FAFAFA;">
		<div id="div_message_body" class="round">
			<table cellspacing=0 cellpadding=2 border=0 width="100%">
			<tr>
				<td align="right"><div class="td_dept_td_blank round" style="font-weight: bold;">Subject</div></td>
				<td width="100%"><div class="td_dept_td_blank info_neutral" style="padding: 10px; text-shadow: none;"><?php echo $subject ?></div></td>
			</tr>
			<tr>
				<td align="right"><div class="td_dept_td_blank round" style="font-weight: bold;">From</div></td>
				<td><div class="td_dept_td_blank" style="padding: 2px; text-shadow: none;"><span id="msg_from_name"><?php echo $message["vname"] ?></span> &lt;<span id="msg_from_email"><a href="mailto:<?php echo $message["vemail"] ?>" target="_blank"><?php echo $message["vemail"] ?></a></span>&gt;</div></td>
			</tr>
			<tr>
				<td align="right"><div class="td_dept_td_blank round" style="font-weight: bold;">To</div></td>
				<td><div class="td_dept_td_blank" id="msg_to" style="padding: 2px; text-shadow: none;"><?php echo $to ?></div></td>
			</tr>
			<tr>
				<td align="right" nowrap><div class="td_dept_td_blank round" style="font-weight: bold;">On Page</div></td>
				<td><div class="td_dept_td_blank" id="msg_to" style="padding: 2px; text-shadow: none;"><?php echo $onpage ?></div></td>
			</tr>
			<tr>
				<td align="right"><div class="td_dept_td_blank round" style="font-weight: bold;">Sent</div></td>
				<td><div class="td_dept_td_blank" id="msg_created" style="padding: 2px; text-shadow: none;"><?php echo $created ?> from IP: <?php echo $message["ip"] ?></div></td>
			</tr>
			<tr>
				<td align="right" valign="top">
					<div class="td_dept_td_blank round" style="font-weight: bold;">Message</div>
				</td>
				<td valign="top">
					<div class="td_dept_td_blank" style="padding: 2px; text-shadow: none;">
						<div id="msg_message" class="info_white" style="padding: 15px; height: 100px; overflow: auto; word-break: break-word; word-wrap: break-word;" class="round"><?php echo $message_body ?></div>
					</div>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<div style="display: none;" id="custom_variables"></div>
				</td>
			</tr>
			</table>
		</div>
	</div>

	<div style="position: fixed; bottom: 0px; width: 100%;">
		<div style="padding-left: 25px; padding-right: 25px;">
			<table cellspacing=0 cellpadding=0 border=0 width="100%">
			<tr>
				<td nowrap><span onClick="delete_message()" style="cursor: pointer;"><img src="../pics/btn_delete.png" width="64" height="23" border="0" alt=""></span></td>
				<td width="100%" align="right" style="padding-top: 10px; padding-bottom: 10px;">
					<button type="button" class="btn" onClick="window.close()">close</button>
				</td>
			</tr>
			</table>
		</div>
	</div>

</body>
</html>
<?php if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; } ?>
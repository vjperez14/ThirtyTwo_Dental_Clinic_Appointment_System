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





	/*********************************************/
	// Set this to true to output various MySQL variables
	/*********************************************/
	//
	//

	$mysql_vars = false ;

	//
	//
	/*********************************************/





	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_DB.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/examples/table_schema.php" ) ;

	$action = Util_Format_Sanatize( Util_Format_GetVar( "action" ), "ln" ) ;
	$table = Util_Format_Sanatize( Util_Format_GetVar( "table" ), "ln" ) ;
	$token = Util_Format_Sanatize( Util_Format_GetVar( "token" ), "ln" ) ;
	$error = "" ;

	$table_schemas = unserialize( $table_schemas ) ;

	if ( $action == "repair" )
	{
		if ( $token == md5($CONF['SALT'].$CONF['DOCUMENT_ROOT']) )
		{
			if ( preg_match( "/^p_/", $table ) )
			{
				$query = "REPAIR TABLE $table" ;
				database_mysql_query( $dbh, $query ) ;
				if ( $dbh[ 'ok' ] )
				{
					$query = "SELECT 1 FROM $table LIMIT 1" ;
					database_mysql_query( $dbh, $query ) ;
					if ( !$dbh[ 'ok' ] )
					{
						$error = "
							Could not repair table <code><big><b>$table</b></big></code>.  Here are few other things you can do:
							<ul style=\"margin-top: 5px;\">
								<li> (recommended) Contact your server admin for alternative repair methods or restore the <code><big><b>$table</b></big></code> table from a backup.
								<li> Or, delete the existing <code><big><b>$table</b></big></code> table that has the errors and create a new <code><big><b>$table</b></big></code> table.  The new table will contain zero data.<br><button type=\"button\" onClick=\"recreate_table()\">Delete and create a new $table table.</button>
							</ul>
						" ; $error = Util_Format_Trim( $error ) ;
					}
				}
			}
		}
		else
			$error = "Invalid access token." ;
	}
	else if ( $action == "recreate_table" )
	{
		if ( $token == md5($CONF['SALT'].$CONF['DOCUMENT_ROOT']) )
		{
			if ( isset( $table_schemas[$table] ) )
			{
				$query = "DROP TABLE $table" ;
				database_mysql_query( $dbh, $query ) ;

				$query = $table_schemas[$table] ;
				database_mysql_query( $dbh, $query ) ;

				if ( !$dbh[ 'ok' ] )
				{
					$error = $dbh["error"] ;
				}

				if ( $error )
				{
					$error = "
						Could not recreate table <code><big><b>$table</b></big></code>.  Here are few other things you can try:
						<ul style=\"margin-top: 5px;\">
							<li> (recommended) Contact your server admin for alternative repair methods or restore the <code><big><b>$table</b></big></code> table from a backup.
							<li> Or, <a href=\"https://www.phplivesupport.com/r.php?r=uninstall\" target=\"newwin_uninstall\" style=\"color: #FFFFFF;\">uninstall the system</a> and perform a new install.
						</ul>
					" ; $error = Util_Format_Trim( $error ) ;
				}
			}
		}
		else
			$error = "Invalid access token." ;
	}
	else if ( $action == "repair_structure" )
	{
		if ( $token == md5($CONF['SALT'].$CONF['DOCUMENT_ROOT']) )
		{
			if ( isset( $table_schemas[$table] ) )
			{
				$result = Util_DB_CheckTableStructure( $dbh, $table, 1 ) ;

				if ( $result == "" )
					$json_data = "json_data = { \"status\": 1 };" ;
				else
					$json_data = "json_data = { \"status\": 0, \"error\": \"Could not repair table.  Possible primary key conflict or first field situation.  Please contact <a href='mailto:tech@phplivesupport.com' target='_blank'>tech@phplivesupport.com</a> for assistance.\" };" ;
			}
			else
				$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid table name or table does not exist.\" };" ;
		}
		else
			$json_data = "json_data = { \"status\": 0, \"error\": \"Invalid access token.\" };" ;

		if ( isset( $dbh ) && isset( $dbh['con'] ) ) { database_mysql_close( $dbh ) ; }
		HEADER('Content-Type: text/plain; charset=utf-8') ;
		print $json_data ; exit ;
	}

	$tables = Util_DB_GetTableNames( $dbh ) ;

	$mysql_vars_string = "" ;
	$query = "SHOW VARIABLES" ;
	database_mysql_query( $dbh, $query ) ;
	if ( $dbh[ 'ok' ] )
	{
		while( $data = database_mysql_fetchrow( $dbh ) )
		{
			if ( isset( $data["Variable_name"] ) && isset( $data["Value"] ) )
			{
				if ( $data["Variable_name"] == "innodb_buffer_pool_size" )
					$mysql_vars_string .= "innodb_buffer_pool_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "sort_buffer_size" )
					$mysql_vars_string .= "sort_buffer_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "read_buffer_size" )
					$mysql_vars_string .= "read_buffer_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "join_buffer_size" )
					$mysql_vars_string .= "join_buffer_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "tmp_table_size" )
					$mysql_vars_string .= "tmp_table_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "table_open_cache" )
					$mysql_vars_string .= "table_open_cache = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "max_connections" )
					$mysql_vars_string .= "max_connections = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "thread_cache_size" )
					$mysql_vars_string .= "thread_cache_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "query_cache_size" )
					$mysql_vars_string .= "query_cache_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "query_cache_type" )
					$mysql_vars_string .= "query_cache_type = $data[Value]<br>" ;
				else if ( $data["Variable_name"] == "innodb_buffer_pool_instances" )
					$mysql_vars_string .= "innodb_buffer_pool_instances = $data[Value]<br>" ;
				else if ( $data["Variable_name"] == "innodb_log_file_size" )
					$mysql_vars_string .= "innodb_log_file_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "innodb_log_buffer_size" )
					$mysql_vars_string .= "innodb_log_buffer_size = $data[Value] (" . Util_Functions_Bytes( $data["Value"] ) . ")<br>" ;
				else if ( $data["Variable_name"] == "innodb_file_per_table" )
					$mysql_vars_string .= "innodb_file_per_table = $data[Value]<br>" ;
				else if ( $data["Variable_name"] == "default_storage_engine" )
					$mysql_vars_string .= "default_storage_engine = $data[Value]<br>" ;
			}
		}
	}
?>
<?php include_once( "../inc_doctype.php" ) ?>
<head>
<title> PHP Live! Support </title>

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
		$("html").css({'background': '#272727'}) ; $("body").css({'background': '#F4F6F8'}) ;
		init_menu() ;
		toggle_menu_setup( "settings" ) ;

		<?php if ( $action && !$error ): ?>do_alert( 1, "Update Success" ) ;
		<?php elseif ( $action && $error ): ?>do_alert_div( "..", 0, '<?php echo $error ?>' ) ;
		<?php endif ; ?>
	});

	function recreate_table( thetable )
	{
		var table = ( typeof( thetable ) != "undefined" ) ? thetable : "<?php echo $table ?>" ;

		if ( confirm( "Are you sure?" ) )
		{
			location.href = "db.php?action=recreate_table&table="+table+"&token=<?php echo md5($CONF['SALT'].$CONF['DOCUMENT_ROOT']) ?>" ;
		}
	}

	function repair_structure( thetable )
	{
		var unique = unixtime() ;
		var json_data = new Object ;

		if ( confirm( "Attempt to repair the table structure for "+thetable+"?" ) )
		{
			$(':button').prop('disabled', true) ;

			$.ajax({
			type: "POST",
			url: "db.php",
			data: "action=repair_structure&table="+thetable+"&token=<?php echo md5($CONF['SALT'].$CONF['DOCUMENT_ROOT']) ?>&"+unique,
			success: function(data){
				eval( data ) ;

				$(':button').prop('disabled', false) ;
				if ( json_data.status )
				{
					location.href = "db.php?action=success" ;
				}
				else
					do_alert_div( "..", 0, json_data.error ) ;
			},
			error:function (xhr, ajaxOptions, thrownError){
				$(':button').prop('disabled', false) ;
				do_alert( 0, "Could not connect to server.  Please try again." ) ;
			} });
		}
	}
//-->
</script>
</head>
<?php include_once( "./inc_header.php" ) ?>

		<div class="op_submenu_wrapper">
			<div class="op_submenu" style="margin-left: 0px;" onClick="location.href='settings.php?jump=eips'" id="menu_eips">Excluded IPs</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=sips'" id="menu_sips">Blocked IPs</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=props'" id="menu_props">Autocorrect & Charset</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=cookie'" id="menu_cookie">Cookies</div>
			<div class="op_submenu" onClick="location.href='settings.php?jump=upload'" id="menu_upload">File Upload</div>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/addons/ldap/ldap.php" ) ): ?><div class="op_submenu" onClick="location.href='../addons/ldap/ldap.php'" id="menu_ldap">LDAP</div><?php endif ; ?>
			<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/mapp/settings.php" ) ): ?><div class="op_submenu" onClick="location.href='../mapp/settings.php'" id="menu_system"><img src="../pics/icons/mobile.png" width="12" height="12" border="0" alt=""> Mobile App</div><?php endif ; ?>
			<?php if ( $admininfo["adminID"] == 1 ): ?>
			<div class="op_submenu" onClick="location.href='settings.php?jump=profile'" id="menu_profile"><img src="../pics/icons/key.png" width="12" height="12" border="0" alt=""> Password</div>
			<?php endif ; ?>
			<div class="op_submenu_focus" id="menu_system">System</div>
			<div style="clear: both"></div>
		</div>

		<div style="margin-top: 25px;">
			<div style="margin-bottom: 25px;"><span class="info_misc"><img src="../pics/icons/arrow_left.png" width="16" height="15" border="0" alt=""> <a href="system.php">back</a></span></div>
			<div id="div_alert"></div>
			<div>
				<table cellspacing=0 cellpadding=0 border=0 width="100%">
				<tr>
					<td width="130"><div class="td_dept_header">Table Name</div></td>
					<td width="100"><div class="td_dept_header">Rows</div></td>
					<td width="100"><div class="td_dept_header">Size</div></td>
					<td width="100"><div class="td_dept_header">Type</div></td>
					<td width="60"><div class="td_dept_header">Status</div></td>
					<td><div class="td_dept_header">Structure</div></td>
				</tr>
				<?php
					$approx_disc_use = 0 ;
					$tables_hash = Array() ;
					for( $c = 0; $c < count( $tables ); ++$c )
					{
						$tables_hash[$tables[$c]] = $tables[$c] ;

						$analyze = Util_DB_AnalyzeTable( $dbh, $tables[$c] ) ;
						$stats = Util_DB_TableStats( $dbh, $tables[$c] ) ;
						$fields_not_found_string = Util_DB_CheckTableStructure( $dbh, $tables[$c], 0 ) ;

						if ( $fields_not_found_string == "" )
							$fields_not_found_string = "<span class=\"info_good\" style=\"padding: 2px;\">Ok</span>" ;
						else if ( $fields_not_found_string == "notexist" )
							$fields_not_found_string = "<span class=\"info_error\" style=\"padding: 2px;\">Invalid Table</span>" ;
						else
							$fields_not_found_string = "<span class=\"info_error\" style=\"padding: 2px;\">$fields_not_found_string</span>" ;

						$name = $stats["Name"] ;
						$type = $analyze["Msg_type"] ;
						$status = "<span class=\"info_error\" style=\"padding: 2px;\">".$analyze["Msg_text"]." <button type=\"button\" onClick=\"location.href='db.php?action=repair&table=$name&token=".md5($CONF['SALT'].$CONF['DOCUMENT_ROOT'])."'\">repair</button></span>" ;

						if ( preg_match( "/^p_/", $name ) )
						{
							if ( preg_match( "/(Table is already up to date)|(ok)/i", $status ) )
								$status = "<span class=\"info_good\" style=\"padding: 2px;\">OK</span>" ;

							$rows = $stats["Rows"] ;
							$ave_row_size = $stats["Data_length"] ;
							$ave_disk = $ave_row_size + $stats["Index_length"] ;
							$ave_size = Util_Functions_Bytes( $ave_disk ) ;

							$bg_color = ( ($c+1) % 2 ) ? "FFFFFF" : "EDEDED" ;

							$db_engine_innodb = Util_DB_IsInnoDB( $dbh, $name ) ;
							if ( is_bool( $db_engine_innodb ) )
								$innodb = ( $db_engine_innodb ) ? "InnoDB" : "MyISAM" ;
							else
								$innodb = "<span class='info_error'>".$db_engine_innodb."</span>" ;

							$approx_disc_use += $ave_disk ;

							print "<tr style=\"background: #$bg_color\">
								<td style=\"padding: 5px;\"><div class=\"td_dept_td info_neutral\" style=\"padding: 4px;\">$name</div></td>
								<td><div class=\"td_dept_td\">$rows</div></td>
								<td><div class=\"td_dept_td\">$ave_size</div></td>
								<td><div class=\"td_dept_td\">$innodb</div></td>
								<td><div class=\"td_dept_td\">$status</div></td>
								<td><div class=\"td_dept_td\">$fields_not_found_string</div></td>
							</tr>" ;
						}
					}

					foreach ( $table_schemas as $name => $structure )
					{
						if ( !isset( $tables_hash[$name] ) )
						{
							print "<tr><td colspan=\"7\"><div class=\"info_error\">Error: Missing Table <button type=\"button\" onClick=\"recreate_table('$name')\">Create table $name.</button></div></td></tr>" ;
						}
					}

					$approx_disc_use = Util_Functions_Bytes( $approx_disc_use ) ;
				?>
				<tr>
					<td colspan=6>
						<div class="info_neutral">
							<table cellspacing=0 cellpadding=0 border=0>
							<tr>
								<td valign="top">
									<table cellspacing=3 cellpadding=3 border=0>
									<tr>
										<td nowrap>disk space usage</td>
										<td class="info_misc" nowrap>~<?php echo $approx_disc_use ?></td>
									</tr>
									<tr>
										<td nowrap>connection type</td>
										<td class="info_misc" nowrap> <?php echo preg_replace( "/.php/", "", $CONF["SQLTYPE"] ) ?></td>
									</tr>
									</table>
								</td>
								<td valign="top" style="padding-left: 25px;">
									<table cellspacing=3 cellpadding=3 border=0>
									<tr>
										<td>
											<?php echo ( $mysql_vars ) ? $mysql_vars_string : "" ; ?> &nbsp;
										</td>
									</tr>
									</table>
								</td>
							</tr>
							</table>
						</div>
					</td>
				</tr>
				</table>
			</div>
		</div>

<?php include_once( "./inc_footer.php" ) ?>
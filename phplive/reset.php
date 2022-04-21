<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	// Setup Admin Credential Reset
	/***************************************/

	// STEP 1.
	//**************************************
	// variable: $live
	//		[ 1, 0 ]
	//		1 - will process the crential update
	//		0 - will not process the update
	//
	// * set to 0 at all times unless updating the credentials

	$live = 0 ;
	/***************************************/



	// STEP 2. (optional)
	//**************************************
	// variable: $setup_admin_login
	//		the Setup Admin login (letters and numbers only)
	//
	// Provide the $setup_admin_login if you would like to change the login.
	// Leave blank if you do not want to change it.
	//

	$setup_admin_login = '' ;
	/***************************************/



	// STEP 3. (required)
	//**************************************
	// variable: $setup_admin_password
	//		the Setup Admin password (no quotes ' or ")
	//
	// IMPORTANT: the password MUST be enclosed in single quotes '
	//
	// Example:
	// $setup_admin_password = 'new_password' ; // correct way
	// $setup_admin_password = "new_password" ; // incorrect way (produces invalid hash in some situations)
	//

	$setup_admin_password = '' ;
	/***************************************/


	// STEP 4. Final Step
	//**************************************
	//
	// Access this script from a URL:
	//
	// example: www.your-website.com/phplive/reset.php
	//
	// There will be a success message displayed if the credentials have been
	// updated.  After the update, be sure to set the above $live variable
	// back to 0 to lock the script from further updates.
	//
	/***************************************/



























	/****************************************/
	/****************************************/
	/****************************************/
	/****************************************/
	/****************************************/
	/****************************************/
	// DO NOT MODIFY BELOW
	/****************************************/
	/****************************************/
	/****************************************/
	/****************************************/
	/****************************************/
	/****************************************/






































































	/****************************************/
	/****************************************/
	// DO NOT MODIFY
	/****************************************/
	/****************************************/
	if ( !is_file( "./web/config.php" ) ){ HEADER("location: setup/install.php") ; exit ; }
	include_once( "./web/config.php" ) ;
	if ( $live === 1 ) {
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Format.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Error.php" ) ;
		include_once( "$CONF[DOCUMENT_ROOT]/API/".Util_Format_Sanatize($CONF["SQLTYPE"], "ln") ) ;
		if ( $setup_admin_password )
		{
			$password = md5( $setup_admin_password ) ; LIST( $login, $password ) = database_mysql_quote( $dbh, $setup_admin_login, $password ) ;
			$query = "SELECT * FROM p_admins WHERE adminID = 1" ; database_mysql_query( $dbh, $query ) ; $data = database_mysql_fetchrow( $dbh ) ;
			if ( isset( $data["adminID"] ) ) {
				if ( $login )
					$query = "UPDATE p_admins SET login = '$login', password = '$password' WHERE adminID = 1" ;
				else
					$query = "UPDATE p_admins SET password = '$password' WHERE adminID = 1" ;
				database_mysql_query( $dbh, $query ) ;
				print "<!doctype html><html><body><div style='padding: 25px; font-family: Arial; font-size: 12px;'>
					<div style='background: #FBF7B4; border: 1px solid #F1E5A3; padding: 5px; color: #806732; font-size: 18px; text-shadow: 1px 1px #FBF9DB; border-radius: 10px;'>Reset Success!</div>
					<div style='margin-top: 15px;'><img src=\"./pics/icons/warning.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"\"> Don't forget to set the <b><big>\$live</big></b> variable back to 0.  (<code>\$live = 0</code>)</div>
					<div style='margin-top: 15px;'><a href='./index.php?menu=sa'>Login to Setup Admin</a></div>
					</div></body></html>" ;
			} else { print "<!doctype html><html><body><div style='padding: 25px; font-family: Arial; font-size: 12px;'><div style='background: #FD7D7F; border: 1px solid #E16F71; padding: 5px; color: #FFFFFF; border-radius: 10px;'>Error: Could not locate the master Setup Admin account to reset.</div></div></body></html>" ;
			} if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
		}
		else { print "<!doctype html><html><body><div style='padding: 25px; font-family: Arial; font-size: 12px;'><div style='background: #FD7D7F; border: 1px solid #E16F71; padding: 5px; color: #FFFFFF; border-radius: 10px;'>Error: The variable <b><big>\$setup_admin_password</big></b> must be provided.</div></div></body></html>" ; }
	}
	else {
		print "<!doctype html><html><body><div style='padding: 25px; font-family: Arial; font-size: 12px;'>
		<div style='font-size: 18px;'>Request did not process.</div>
		<div style='margin-top: 5px;'>Be sure to set the <b><big>\$live</big></b> variable to 1 to process the reset.  (<code>\$live = 1</code>)</div>
		<div style='margin-top: 5px;'>After it has been set, refresh the page to try again.</div></div></body></html>" ;
	}
?>
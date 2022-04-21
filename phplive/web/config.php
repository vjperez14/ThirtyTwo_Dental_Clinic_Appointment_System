<?php
	$CONF = Array() ;
$CONF['DOCUMENT_ROOT'] = addslashes( '/var/www/html/phplive' ) ;
$CONF['BASE_URL'] = '//pojects.com/phplive' ;
$CONF['SQLTYPE'] = 'SQLi.php' ;
$CONF['SQLHOST'] = 'localhost' ;
$CONF['SQLPORT'] = '0' ;
$CONF['SQLLOGIN'] = 'debian-sys-maint' ;
$CONF['SQLPASS'] = 'l6pdxQO6O3wIFWZs' ;
$CONF['DATABASE'] = 'phplivechat' ;
$CONF['THEME'] = 'default' ;
$CONF['TIMEZONE'] = 'Asia/Manila' ;
$CONF['icon_online'] = '' ;
$CONF['icon_offline'] = '' ;
$CONF['lang'] = 'english' ;
$CONF['logo'] = '' ;
$CONF['CONF_ROOT'] = '/var/www/html/phplive/web' ;
$CONF['UPLOAD_HTTP'] = '//pojects.com/phplive/web' ;
$CONF['UPLOAD_DIR'] = '/var/www/html/phplive/web' ;
$CONF['ATTACH_DIR'] = '/var/www/html/phplive/web/file_attach' ;
$CONF['TEMP_DIR'] = '/var/www/html/phplive/web/file_temp' ;
$CONF['EXPORT_DIR'] = '/var/www/html/phplive/web/exported_files' ;
$CONF['geo'] = '' ;
$CONF['SALT'] = 'gcwwdazn2mv4n5rpqkyxestpgp4aybr3' ;
$CONF['API_KEY'] = 'r3n2jahpsp' ;
	if ( phpversion() >= '5.1.0' ){ date_default_timezone_set( $CONF['TIMEZONE'] ) ; }
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vars.php" ) ;
?>
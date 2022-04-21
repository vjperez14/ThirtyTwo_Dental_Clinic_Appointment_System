<?php
	/***************************************/
	//
	//
	// PHP Live! Support
	//
	// https://www.phplivesupport.com
	//
	/***************************************/
	if ( defined( 'API_Util_Addons' ) ) { return ; }	
	define( 'API_Util_Addons', true ) ;

	FUNCTION Util_Addons_WriteToFile( $valname, $val )
	{
		global $CONF ;
		global $VALS_ADDONS ;
		global $opcache ;
		if ( !isset( $VALS_ADDONS ) ) { $VALS_ADDONS = Array() ; }
		
		$val = preg_replace( "/'/", "", $val ) ;

		if ( !isset( $VALS_ADDONS[$valname] ) ) { $VALS_ADDONS[$valname] = "" ; }

		$conf_vars = "\$VALS_ADDONS = Array() ; " ;
		foreach( $VALS_ADDONS as $key => $value )
		{
			if ( $key == $valname ) { $VALS_ADDONS[$key] = $val ; }
			$conf_vars .= " \$VALS_ADDONS['$key'] = '".$VALS_ADDONS[$key]."' ; " ;
		} $conf_vars = preg_replace( "/`/", "", $conf_vars ) ;

		$conf_string = "< php $conf_vars ?>" ;
		$conf_string = preg_replace( "/< php/", "<?php", preg_replace( "/  +/", " ", $conf_string ) ) ;

		if ( is_writeable( "$CONF[CONF_ROOT]" ) )
		{
			file_put_contents( "$CONF[CONF_ROOT]/addons.php", $conf_string, LOCK_EX ) ;
			if ( $opcache ) { opcache_invalidate( "$CONF[CONF_ROOT]/addons.php" ) ; }
			return true ;
		}
		else { return false ; }
	}
?>
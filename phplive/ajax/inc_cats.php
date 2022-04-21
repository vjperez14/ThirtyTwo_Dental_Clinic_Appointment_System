<?php
	if ( isset( $CONF ) )
	{
		$value = Util_Format_Sanatize( Util_Format_GetVar( "value" ), "" ) ;
		$can_cats_admin = ( isset( $VALS["can_cats"] ) && $VALS["can_cats"] ) ? base64_decode( $VALS["can_cats"] ) : "" ;

		if ( isset( $setupinfo ) )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

			$has_cat = 0 ;

			$cats_hash = json_decode( $value ) ;
			foreach ( $cats_hash as $deptid => $cats_array )
			{
				if ( count( $cats_array ) ) { $has_cat += 1 ; }
			}

			if ( ( $can_cats_admin && !$has_cat ) || ( !$can_cats_admin && $has_cat ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Canned/update.php" ) ;
				Canned_update_ResetCat( $dbh ) ;
			}

			if ( !$has_cat )
				Util_Vals_WriteToFile( "can_cats", "" ) ;
			else
				Util_Vals_WriteToFile( "can_cats", base64_encode( $value ) ) ;
		}
		else
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/update.php" ) ;

			if ( !$can_cats_admin )
			{
				$cats_hash = json_decode( $value ) ;
				foreach ( $cats_hash as $deptid => $cats_array )
				{
					$query_string = "UPDATE p_canned SET catID = -1 WHERE ( deptID = $deptid AND ( catID != -1 ) " ;
					for ( $c = 0; $c < count( $cats_array ); ++$c )
					{
						$query_string .= "AND ( catID != $c ) " ;
					}
					$query_string .= " ) " ;
					database_mysql_query( $dbh, $query_string ) ;
				}
				Ops_update_OpVarValue( $dbh, $opid, "can_cats", $value ) ;
			}
		}
		$json_data = "json_data = { \"status\": 1 };" ;
	}
?>
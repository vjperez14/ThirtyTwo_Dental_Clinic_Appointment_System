<?php
	if ( defined( 'API_Util_DB' ) ) { return ; }	
	define( 'API_Util_DB', true ) ;

	FUNCTION Util_DB_GetTableNames( &$dbh )
	{
		$query = "SHOW TABLES" ;
		database_mysql_query( $dbh, $query ) ;

		$output = Array() ;
		if ( $dbh[ 'ok' ] )
		{
			while( $data = database_mysql_fetchrowa( $dbh ) )
			{
				foreach( $data as $db => $table )
					$output[] = $table ;
			}
		}

		return $output ;
	}

	FUNCTION Util_DB_AnalyzeTable( &$dbh,
					$table )
	{
		if ( $table == "" )
			return false ;
	
		LIST( $table ) = database_mysql_quote( $dbh, $table ) ;

		$query = "ANALYZE TABLE $table" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrowa( $dbh ) ;
			return $data ;
		}

		return false ;
	}

	FUNCTION Util_DB_TableStats( &$dbh,
					$table )
	{
		if ( $table == "" )
			return false ;
	
		LIST( $table ) = database_mysql_quote( $dbh, $table ) ;

		$query = "SHOW TABLE STATUS LIKE '$table'" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrowa( $dbh ) ;
			return $data ;
		}

		return false ;
	}

	FUNCTION Util_DB_IsInnoDB( &$dbh,
					$table )
	{
		if ( $table == "" )
			return false ;

		$query = "SHOW CREATE TABLE p_admins;" ;
		database_mysql_query( $dbh, $query ) ;

		if ( $dbh[ 'ok' ] )
		{
			$data = database_mysql_fetchrow( $dbh ) ;
			if ( preg_match( "/InnoDB/", $data["Create Table"] ) )
				return true ;
			else
				return false ;
		}
		return $dbh["error"] ;
	}

	FUNCTION Util_DB_CheckTableStructure( &$dbh,
					$table,
					$repair )
	{
		if ( ( $table == "" ) || !is_numeric( $repair ) )
			return false ;
		global $table_schemas ;
		$charset_string = ( database_mysql_old( $dbh ) ) ? "" : "CHARACTER SET utf8 COLLATE utf8_general_ci" ;

		if ( isset( $table_schemas[$table] ) )
		{
			$schema = preg_replace( "/^create(.*?)\(/i", "", $table_schemas[$table] ) ;
			$fields_array = explode( "  ", $schema ) ;

			$fields_order_name = Array() ;
			$fields_order_index = Array() ;
			$field_names_schema = Array() ; $field_keys_schema = Array() ;
			for ( $c = 0; $c < count( $fields_array ); ++$c )
			{
				$field_string = trim( $fields_array[$c] ) ;
				if ( $field_string && preg_match( "/ /i", $field_string ) )
				{
					$parts = explode( " ", $field_string ) ;
					$field_name = trim( $parts[0] ) ;
					if ( !preg_match( "/^(primary|key|unique)/i", $field_name ) )
					{
						$field_names_schema[$field_name] = preg_replace( "/,$/", "", $field_string ) ;

						$fields_order_name[$field_name] = $c ;
						$fields_order_index[$c] = $field_name ;
					}
					else
					{
						preg_match( "/\((.*?)\)/", $field_string, $matches ) ;
						if ( isset( $matches[1] ) )
						{
							$field_name = $matches[1] ;

							if ( preg_match( "/^(primary)/i", $field_string ) )
								$field_string = preg_replace( "/primary/i", "ADD PRIMARY", $field_string ) ;
							else if ( preg_match( "/^(unique)/i", $field_string ) )
								$field_string = preg_replace( "/unique/i", "ADD UNIQUE", $field_string ) ;
							else
								$field_string = preg_replace( "/key/i", "ADD INDEX", $field_string ) ;

							$field_keys_schema[$field_name] = preg_replace( "/(\),)|(\) \))/", ")", $field_string ) ;
						}
					}
				}
			}

			$field_names_db = Array() ;
			$query = "DESCRIBE $table" ;
			database_mysql_query( $dbh, $query ) ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
			{
				$field_name = $data["Field"] ;
				$field_names_db[$field_name] = true ;
			}
			
			$db_missing_fields_string = "" ;
			foreach ( $field_names_schema AS $this_field_name => $null )
			{
				if ( !isset( $field_names_db[$this_field_name] ) )
				{
					if ( $repair )
					{
						$index = $fields_order_name[$this_field_name] - 1 ;
						if ( isset( $fields_order_index[$index] ) )
						{
							$query_add = $field_names_schema[$this_field_name] ;
							if ( preg_match( "/varchar/i", $field_names_schema[$this_field_name] ) )
								$query_add = preg_replace( "/NOT NULL/i", "$charset_string NOT NULL", $field_names_schema[$this_field_name] ) ;
							else if ( preg_match( "/TEXT NOT NULL/i", $field_names_schema[$this_field_name] ) )
								$query_add = preg_replace( "/TEXT NOT NULL/i", "TEXT $charset_string NOT NULL", $field_names_schema[$this_field_name] ) ;

							$query_add = preg_replace( "/DEFAULT english/i", "DEFAULT 'english'", $query_add ) ;
							$query_key = ( isset( $field_keys_schema[$this_field_name] ) ) ? ", ".$field_keys_schema[$this_field_name] : "" ;

							$after_string = " AFTER ".$fields_order_index[$index] ;
	
							$query = "ALTER TABLE $table ADD " . $query_add . $after_string . $query_key  ;
							database_mysql_query( $dbh, $query ) ;

							if ( !$dbh['ok'] )
								$db_missing_fields_string .= "[ Could not repair: $this_field_name query error ] " ;
						}
						else
							$db_missing_fields_string .= " [ Could not repair: 1st item potential primary ] " ;
					}
					else
						$db_missing_fields_string .= " [ missing: $this_field_name <button type=\"button\" onClick=\"repair_structure('$table')\">repair</button> ] " ;
				}
			}

			foreach ( $field_names_db AS $this_field_name => $null )
			{
				if ( !isset( $field_names_schema[$this_field_name] ) )
				{
					if ( $repair )
					{
						$query = "ALTER TABLE $table DROP $this_field_name" ;
						database_mysql_query( $dbh, $query ) ;
					}
					else
						$db_missing_fields_string .= " [ invalid: $this_field_name <button type=\"button\" onClick=\"repair_structure('$table')\">repair</button> ] " ;
				}
			}
			return $db_missing_fields_string ;
		}
		else
			return "notexist" ;
	}
?>
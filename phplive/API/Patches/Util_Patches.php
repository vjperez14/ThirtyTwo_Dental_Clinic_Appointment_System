<?php
	$patch_processed = 0 ; // new var as of v.4.7.9.9.8.7
	if ( !is_file( "$CONF[CONF_ROOT]/patches/1" ) )
	{ $patched = 1 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.1" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/2" ) )
	{ $patched = 2 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD etrans TINYINT( 1 ) NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log ADD etrans TINYINT( 1 ) NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.1.2" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/3" ) )
	{ $patched = 3 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD tupdated INT NOT NULL AFTER created" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.1.3" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/4" ) )
	{ $patched = 4 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_vars ADD sm_fb TEXT NOT NULL, ADD sm_tw TEXT NOT NULL, ADD sm_yt TEXT NOT NULL, ADD sm_li TEXT NOT NULL " ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.1.4" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/5" ) )
	{ $patched = 5 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD initiated TINYINT( 1 ) NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log ADD initiated TINYINT( 1 ) NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD chatting TINYINT( 1 ) NOT NULL AFTER marketID" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_transcripts ADD initiated TINYINT( 1 ) NOT NULL AFTER opID" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD agent VARCHAR( 200 ) NOT NULL AFTER hostname" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.1.54" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/6" ) )
	{ $patched = 6 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			// attempt to drop table to reset
			$query = "DROP TABLE IF EXISTS p_sm" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "CREATE TABLE IF NOT EXISTS p_sm ( deptID INT( 10 ) UNSIGNED NOT NULL , sm LONGTEXT NOT NULL , PRIMARY KEY ( deptID ) )" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "SELECT * FROM p_vars LIMIT 1" ;
			database_mysql_query( $dbh, $query ) ;
			$data = database_mysql_fetchrow( $dbh ) ;

			if ( isset( $data["sm_fb"] ) || isset( $data["sm_tw"] ) || isset( $data["sm_yt"] ) || isset( $data["sm_li"] ) )
			{
				$sm_fb = ( isset( $data["sm_fb"] ) ) ? $data["sm_fb"] : "" ;
				$sm_tw = ( isset( $data["sm_tw"] ) ) ? $data["sm_tw"] : "" ;
				$sm_yt = ( isset( $data["sm_yt"] ) ) ? $data["sm_yt"] : "" ;
				$sm_li = ( isset( $data["sm_li"] ) ) ? $data["sm_li"] : "" ;

				$sm_string = "$sm_fb-sm-$sm_tw-sm-$sm_yt-sm-$sm_li" ;
				//$query = "INSERT INTO p_sm VALUES( 0, '$sm_string' )" ;
				//database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_vars DROP sm_fb, DROP sm_tw, DROP sm_yt, DROP sm_li" ;
			database_mysql_query( $dbh, $query ) ;

			// patching from very beginning to now needs to include lang var
			if ( !isset( $CONF["lang"] ) )
			{
				$CONF["lang"] = "english" ;
				Util_Vals_WriteToConfFile( "lang",  "english" ) ;
			}

			Util_Vals_WriteVersion( "4.1.55" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/7" ) )
	{ $patched = 7 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.56" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/8" ) )
	{ $patched = 8 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.57" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/9" ) )
	{ $patched = 9 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD lang VARCHAR( 15 ) NOT NULL AFTER rtime" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "UPDATE p_departments SET lang = '$CONF[lang]'" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.1.58" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/10" ) )
	{ $patched = 10 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.59" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/11" ) )
	{ $patched = 11 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.60" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/12" ) )
	{ $patched = 12 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.61" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/13" ) )
	{ $patched = 13 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.62" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/14" ) )
	{ $patched = 14 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD temail TINYINT NOT NULL AFTER texpire" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_reqstats ADD rateit SMALLINT UNSIGNED NOT NULL , ADD ratings SMALLINT UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators ADD ces VARCHAR( 32 ) NOT NULL AFTER ses, ADD rating TINYINT NOT NULL AFTER ces" ;
			database_mysql_query( $dbh, $query ) ;

			if ( $dbh["error"] == "None" )
			{
				$query = "UPDATE p_departments SET temail = 1" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "UPDATE p_departments SET msg_email = 'Hi %%visitor%%,\r\n\r\nHere is the complete chat transcript for your reference:\r\n\r\n%%transcript%%\r\n\r\n==========\r\n\r\n%%operator%%\r\n%%op_email%%\r\n'" ;
				database_mysql_query( $dbh, $query ) ;

				$dates = Array() ;
				$query = "SELECT * FROM p_reqstats ORDER BY sdate ASC" ;
				database_mysql_query( $dbh, $query ) ;
				while ( $data = database_mysql_fetchrow( $dbh ) )
					$dates[] = $data ;

				for( $c = 0; $c < count( $dates ); ++$c )
				{
					$date = $dates[$c] ;
					$deptid = $date["deptID"] ;
					$opid = $date["opID"] ;

					$stat_start = mktime( 0, 0, 1, date( "m", $date["sdate"] ), date( "j", $date["sdate"] ), date( "Y", $date["sdate"] ) ) ;
					$stat_end = mktime( 0, 0, 1, date( "m", $date["sdate"] ), date( "j", $date["sdate"] )+1, date( "Y", $date["sdate"] ) ) ;

					$ratings = Array() ;
					$query = "SELECT count(*) AS rateit, SUM(rating) AS ratings FROM p_transcripts WHERE deptID = $deptid AND opID = $opid AND opID <> 0 AND created >= $stat_start AND created < $stat_end AND rating <> 0" ;
					database_mysql_query( $dbh, $query ) ;
					while ( $data = database_mysql_fetchrow( $dbh ) )
						$ratings[] = $data ;

					for ( $c2 = 0; $c2 < count( $ratings ); ++$c2 )
					{
						$rating = $ratings[$c2] ;
						$query = "UPDATE p_reqstats SET rateit = $rating[rateit], ratings = $rating[ratings] WHERE sdate = $date[sdate] AND deptID = $deptid AND opID = $opid" ;
						database_mysql_query( $dbh, $query ) ;
					}
				}
			}

			Util_Vals_WriteVersion( "4.1.7" ) ;
			if ( is_file( "$CONF[CHAT_IO_DIR]/TIMESTAMP" ) )
				@unlink( "$CONF[CHAT_IO_DIR]/TIMESTAMP" ) ;

			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/15" ) )
	{ $patched = 15 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.72" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/16" ) )
	{ $patched = 16 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_footprints_u DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD INDEX ( created , deptID )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints DROP INDEX mdfive" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints ADD INDEX ( mdfive )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments ADD temaild TINYINT NOT NULL AFTER temail" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips ADD INDEX ( created )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips ADD i_footprints INT UNSIGNED NOT NULL AFTER t_initiate , ADD i_timestamp INT UNSIGNED NOT NULL AFTER i_footprints , ADD i_initiate INT UNSIGNED NOT NULL AFTER i_timestamp" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_ops DROP INDEX display" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_ops ADD INDEX ( display, visible )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_ops ADD status TINYINT NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_footstats" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footstats DROP INDEX sdate" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footstats ADD mdfive VARCHAR( 32 ) NOT NULL AFTER sdate" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footstats ADD PRIMARY KEY ( sdate , mdfive )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_refer DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_refer ADD INDEX ( created )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_referstats" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_referstats DROP INDEX sdate" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_referstats ADD mdfive VARCHAR( 32 ) NOT NULL AFTER sdate" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_referstats ADD PRIMARY KEY ( sdate , mdfive )" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.1.8" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/17" ) )
	{ $patched = 17 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.81" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/18" ) )
	{ $patched = 18 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.82" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/19" ) )
	{ $patched = 19 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.83" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/20" ) )
	{ $patched = 20 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.1.84" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/21" ) )
	{ $patched = 21 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests CHANGE agent agent VARCHAR( 255 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_geo_bloc" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_geo_bloc ( startIpNum int(10) unsigned NOT NULL, endIpNum int(10) unsigned NOT NULL, locId int(10) unsigned NOT NULL, network mediumint(6) unsigned NOT NULL, PRIMARY KEY (endIpNum), KEY network (network) )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_geo_loc" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_geo_loc ( locId int(10) unsigned NOT NULL, country char(2) $charset_string NOT NULL, region char(42) $charset_string NOT NULL, city varchar(50) $charset_string DEFAULT NULL, latitude float DEFAULT NULL, longitude float DEFAULT NULL, PRIMARY KEY (locId) )" ;
			database_mysql_query( $dbh, $query ) ;

			if ( !isset( $CONF["geo"] ) ) { Util_Vals_WriteToConfFile( "geo",  "" ) ; }

			$query = "ALTER TABLE p_footprints_u ADD country CHAR( 2 ) NOT NULL, ADD region CHAR( 42 ) NOT NULL, ADD city CHAR( 50 ) NOT NULL, ADD latitude FLOAT NOT NULL, ADD longitude FLOAT NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/22" ) )
	{ $patched = 22 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/23" ) )
	{ $patched = 23 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/24" ) )
	{ $patched = 24 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/25" ) )
	{ $patched = 25 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/26" ) )
	{ $patched = 26 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/27" ) )
	{ $patched = 27 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/28" ) )
	{ $patched = 28 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/29" ) )
	{ $patched = 29 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/30" ) )
	{ $patched = 30 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/31" ) )
	{ $patched = 31 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.11" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/32" ) )
	{ $patched = 32 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.12" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/33" ) )
	{ $patched = 33 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.13" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/34" ) )
	{ $patched = 34 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.14" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/35" ) )
	{ $patched = 35 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_vars ADD position TINYINT( 1 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.15" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/36" ) )
	{ $patched = 36 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.16" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/37" ) )
	{ $patched = 37 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.17" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/38" ) )
	{ $patched = 38 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.18" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/39" ) )
	{ $patched = 39 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.19" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/40" ) )
	{ $patched = 40 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.91" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/41" ) )
	{ $patched = 41 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.92" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/42" ) )
	{ $patched = 42 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.93" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/43" ) )
	{ $patched = 43 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.94" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/44" ) )
	{ $patched = 44 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.95" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/45" ) )
	{ $patched = 45 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.96" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/46" ) )
	{ $patched = 46 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.97" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/47" ) )
	{ $patched = 47 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SELECT * FROM p_sm" ;
			database_mysql_query( $dbh, $query ) ;
			if ( isset( $dbh["result"] ) && $dbh["result"] )
			{
				$socials = Array() ;
				while ( $data = database_mysql_fetchrow( $dbh ) )
					$socials[] = $data ;

				$query = "CREATE TABLE IF NOT EXISTS p_socials ( deptID int(10) unsigned NOT NULL, status tinyint(1) NOT NULL, social varchar(15) $charset_string NOT NULL, tooltip varchar(55) $charset_string NOT NULL, url varchar(255) $charset_string NOT NULL, UNIQUE KEY deptID (deptID,social) )" ;
				database_mysql_query( $dbh, $query ) ;

				/*
				for ( $c = 0; $c < count( $socials ); ++$c )
				{
					$sm = $socials[$c] ;

					$deptid = $sm["deptID"] ;
					$sm_fb_array = $sm_tw_array = $sm_yt_array = $sm_li_array = Array() ;
					LIST( $sm_fb, $sm_tw, $sm_yt, $sm_li ) = explode( "-sm-", $sm["sm"] ) ;
					$sm_fb_array = unserialize( $sm_fb ) ;
					$sm_tw_array = unserialize( $sm_tw ) ;
					$sm_yt_array = unserialize( $sm_yt ) ;
					$sm_li_array = unserialize( $sm_li ) ;

					LIST( $tooltip, $url ) = database_mysql_quote( $dbh, $sm_fb_array["tooltip"], $sm_fb_array["url"] ) ;
					if ( $url )
					{
						$query = "INSERT INTO p_socials VALUES( $deptid, $sm_fb_array[status], 'facebook', '$tooltip', '$url')" ;
						database_mysql_query( $dbh, $query ) ;
					}

					LIST( $tooltip, $url ) = database_mysql_quote( $dbh, $sm_tw_array["tooltip"], $sm_tw_array["url"] ) ;
					if ( $url )
					{
						$query = "INSERT INTO p_socials VALUES( $deptid, $sm_tw_array[status], 'twitter', '$tooltip', '$url')" ;
						database_mysql_query( $dbh, $query ) ;
					}

					LIST( $tooltip, $url ) = database_mysql_quote( $dbh, $sm_yt_array["tooltip"], $sm_yt_array["url"] ) ;
					if ( $url )
					{
						$query = "INSERT INTO p_socials VALUES( $deptid, $sm_yt_array[status], 'youtube', '$tooltip', '$url')" ;
						database_mysql_query( $dbh, $query ) ;
					}

					LIST( $tooltip, $url ) = database_mysql_quote( $dbh, $sm_li_array["tooltip"], $sm_li_array["url"] ) ;
					if ( $url )
					{
						$query = "INSERT INTO p_socials VALUES( $deptid, $sm_li_array[status], 'linkedin', '$tooltip', '$url')" ;
						database_mysql_query( $dbh, $query ) ;
					}
				}
				*/

				$query = "DROP TABLE IF EXISTS p_sm" ;
				database_mysql_query( $dbh, $query ) ;
			}

			Util_Vals_WriteVersion( "4.2.98" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/48" ) )
	{ $patched = 48 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.99" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/49" ) )
	{ $patched = 49 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.99-1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/50" ) )
	{ $patched = 50 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD sound VARCHAR( 15 ) NOT NULL AFTER theme" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh["error"] == "None" )
			{
				$query = "UPDATE p_operators SET sound = 'default'" ;
				database_mysql_query( $dbh, $query ) ;
			}

			Util_Vals_WriteVersion( "4.2.99-2" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/51" ) )
	{ $patched = 51 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log CHANGE agent agent VARCHAR( 255 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_canned CHANGE title title VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_canned CHANGE message message MEDIUMTEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments CHANGE name name VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments CHANGE msg_greet msg_greet TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments CHANGE msg_offline msg_offline TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments CHANGE msg_email msg_email TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_external CHANGE name name VARCHAR( 40 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints CHANGE title title VARCHAR( 150 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints_u CHANGE title title VARCHAR( 150 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_marketing CHANGE name name VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators CHANGE name name VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests CHANGE vname vname VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests CHANGE title title VARCHAR( 150 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests CHANGE question question TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log CHANGE vname vname VARCHAR( 40 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log CHANGE title title VARCHAR( 150 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log CHANGE question question TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_socials CHANGE tooltip tooltip VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_transcripts CHANGE vname vname VARCHAR( 80 ) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts CHANGE question question TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts CHANGE formatted formatted TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts CHANGE plain plain TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.99-3" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/52" ) )
	{ $patched = 52 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.99-4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/53" ) )
	{ $patched = 53 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD auto_pop TINYINT NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_footprints CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_footprints_u CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_ips CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_refer CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_requests CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_req_log CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_transcripts CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_footprints_u CHANGE agent agent VARCHAR( 255 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators ADD viewip TINYINT NOT NULL AFTER traffic" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh['error'] == "None" )
			{
				$query = "UPDATE p_operators SET viewip = 1" ;
				database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_departments ADD remail TINYINT NOT NULL AFTER texpire" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh['error'] == "None" )
			{
				$query = "UPDATE p_departments SET remail = 1" ;
				database_mysql_query( $dbh, $query ) ;
			}

			Util_Vals_WriteVersion( "4.2.99-5" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/54" ) )
	{ $patched = 54 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.99-6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/55" ) )
	{ $patched = 55 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.99-7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/56" ) )
	{ $patched = 56 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD sms INT UNSIGNED NOT NULL AFTER rating, ADD smsnum VARCHAR( 65 ) NOT NULL AFTER sms" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.99-8" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/57" ) )
	{ $patched = 57 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.99-9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/58" ) )
	{ $patched = 58 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.100" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/59" ) )
	{ $patched = 59 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			if ( !isset( $CONF["SALT"] ) ) { Util_Vals_WriteToConfFile( "SALT", Util_Format_RandomString( 32 ) ) ; }

			$query = "ALTER TABLE p_operators DROP INDEX lastactive" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators DROP INDEX status" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators ADD INDEX ( status )" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_requests DROP INDEX updated" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators ADD dn TINYINT( 1 ) UNSIGNED NOT NULL AFTER viewip" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh['error'] == "None" )
			{
				$query = "UPDATE p_operators SET dn = 1" ;
				database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_operators CHANGE viewip viewip TINYINT( 1 ) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators CHANGE traffic traffic TINYINT( 1 ) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators CHANGE op2op op2op TINYINT( 1 ) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_operators CHANGE rate rate TINYINT( 1 ) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.101" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/60" ) )
	{ $patched = 60 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_req_log DROP INDEX deptID" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_req_log ADD INDEX ( created, deptID )" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.102" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/61" ) )
	{ $patched = 61 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.103" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/62" ) )
	{ $patched = 62 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.104" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/63" ) )
	{ $patched = 63 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD sound2 VARCHAR( 15 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh["error"] == "None" )
			{
				$query = "UPDATE p_operators SET sound2 = 'default'" ;
				database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_operators CHANGE sound sound1 VARCHAR( 15 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			// double check and drop sound to fix issue if system was repatched
			$query = "ALTER TABLE p_operators DROP sound" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "TRUNCATE TABLE p_reqstats" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_reqstats ADD requests_ INT( 10 ) UNSIGNED NOT NULL AFTER requests" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.105" ) ;

			if ( is_file( "$CONF[CONF_ROOT]/patches/VERSION.php" ) )
				@unlink( "$CONF[CONF_ROOT]/patches/VERSION.php" ) ;

			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/64" ) )
	{ $patched = 64 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.106" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/65" ) )
	{ $patched = 65 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.107" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/66" ) )
	{ $patched = 66 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.108" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/67" ) )
	{ $patched = 67 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD custom VARCHAR( 255 ) $charset_string NOT NULL AFTER refer" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_req_log ADD custom VARCHAR( 255 ) $charset_string NOT NULL AFTER title" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_departments DROP img_offline, DROP img_online" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.109" ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/68" ) )
	{ $patched = 68 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.110" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/69" ) )
	{ $patched = 69 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.111" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/70" ) )
	{ $patched = 70 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.112" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/71" ) )
	{ $patched = 71 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SELECT * FROM p_vars LIMIT 1" ;
			database_mysql_query( $dbh, $query ) ;
			$data = database_mysql_fetchrow( $dbh ) ;

			/*
			$temp = "" ;
			$query = "DESCRIBE p_vars" ;
			database_mysql_query( $dbh, $query ) ;
			while ( $data = database_mysql_fetchrow( $dbh ) )
			{
				$temp .= "{$data['Field']} = {$data['Type']} ---- ";
			}
			*/

			if ( !isset( $data["code"] ) )
			{
				// code, position
				$query = "INSERT INTO p_vars VALUES( 0, 1 )" ;
				database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_vars ADD ts_clean INT UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.2.113" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/72" ) )
	{ $patched = 72 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.114" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/73" ) )
	{ $patched = 73 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.115" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/74" ) )
	{ $patched = 74 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.2.116" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/75" ) )
	{ $patched = 75 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

			if ( !isset( $VALS['OFFLINE'] ) ) { $VALS['OFFLINE'] = "" ; }
			Util_Vals_WriteToFile( "TRAFFIC_EXCLUDE_IPS", "" ) ;
			$VALS['TRAFFIC_EXCLUDE_IPS'] = "" ;

			$query = "ALTER TABLE p_requests ADD sim_ops VARCHAR( 155 ) NOT NULL AFTER hostname, ADD sim_ops_ VARCHAR( 155 ) NOT NULL AFTER sim_ops" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_req_log ADD sim_ops VARCHAR( 155 ) NOT NULL AFTER hostname" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_departments ADD rloop TINYINT UNSIGNED NOT NULL AFTER rtime, ADD savem TINYINT UNSIGNED NOT NULL AFTER rloop" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh["error"] == "None" )
			{
				$query = "UPDATE p_departments SET rloop = 1" ;
				database_mysql_query( $dbh, $query ) ;

				$query = "UPDATE p_departments SET savem = 1" ;
				database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_departments ADD custom VARCHAR( 255 ) $charset_string NOT NULL AFTER savem" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_vars ADD char_set VARCHAR( 155 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_admins DROP INDEX login" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_admins ADD UNIQUE (login)" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "DROP TABLE IF EXISTS p_messages" ;
			database_mysql_query( $dbh, $query ) ;
			
			$query = "CREATE TABLE IF NOT EXISTS p_messages ( messageID int(10) unsigned NOT NULL AUTO_INCREMENT, created int(10) unsigned NOT NULL, status tinyint(4) NOT NULL, chat tinyint(3) unsigned NOT NULL, locked int(11) NOT NULL, deptID int(10) unsigned NOT NULL, footprints int(10) unsigned NOT NULL, ip varchar(45) NOT NULL, vname varchar(80) NOT NULL, vemail varchar(160) NOT NULL, subject varchar(155) NOT NULL, agent varchar(255) NOT NULL, onpage varchar(255) NOT NULL, refer varchar(255) NOT NULL, message text $charset_string NOT NULL, PRIMARY KEY (messageID) )" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_departments ADD smtp VARCHAR( 255 ) NOT NULL AFTER custom" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteToFile( "CHAT_SPAM_IPS", "" ) ;
			Util_Vals_WriteVersion( "4.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/76" ) )
	{ $patched = 76 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "UPDATE p_departments SET smtp = ''" ;
			database_mysql_query( $dbh, $query ) ;

			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/77" ) )
	{ $patched = 77 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/78" ) )
	{ $patched = 78 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "DROP TABLE IF EXISTS p_reqstats" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "CREATE TABLE IF NOT EXISTS p_rstats_depts ( sdate int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, requests int(10) NOT NULL, taken smallint(5) unsigned NOT NULL, declined smallint(5) unsigned NOT NULL, message smallint(5) unsigned NOT NULL, initiated smallint(5) unsigned NOT NULL, rateit smallint(5) unsigned NOT NULL, ratings smallint(5) unsigned NOT NULL, PRIMARY KEY (sdate,deptID) )" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "CREATE TABLE IF NOT EXISTS p_rstats_ops ( sdate int(10) unsigned NOT NULL, opID int(10) unsigned NOT NULL, requests int(10) NOT NULL, taken smallint(5) unsigned NOT NULL, declined smallint(5) unsigned NOT NULL, message smallint(5) unsigned NOT NULL, initiated smallint(5) unsigned NOT NULL, rateit smallint(5) unsigned NOT NULL, ratings smallint(5) unsigned NOT NULL, PRIMARY KEY (sdate,opID) )" ;
			database_mysql_query( $dbh, $query ) ;

			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/79" ) )
	{ $patched = 79 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log DROP etrans" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_requests DROP etrans" ;
			database_mysql_query( $dbh, $query ) ;

			// remove expired to limit query time on drop index
			$expired = time() - (60*60*24*$VARS_IP_LOG_EXPIRE) ;
			$query = "DELETE FROM p_ips WHERE created < $expired" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_ips DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/80" ) )
	{ $patched = 80 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			if ( !isset( $CONF["API_KEY"] ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
				Util_Vals_WriteToConfFile( "API_KEY", Util_Format_RandomString( 10 ) ) ;
			}

			$query = "ALTER TABLE p_departments CHANGE smtp smtp TEXT NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.3.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/81" ) )
	{ $patched = 81 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_footprints_u ADD footprints INT UNSIGNED NOT NULL AFTER browser" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.3.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/82" ) )
	{ $patched = 82 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.3.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/83" ) )
	{ $patched = 83 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD msg_busy TEXT $charset_string NOT NULL AFTER msg_offline" ;
			database_mysql_query( $dbh, $query ) ;

			Util_Vals_WriteVersion( "4.3.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/84" ) )
	{ $patched = 84 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_footprints_u ADD requests INT UNSIGNED NOT NULL AFTER footprints, ADD initiates INT UNSIGNED NOT NULL AFTER requests" ;
			database_mysql_query( $dbh, $query ) ;

			// reset the Ips table for optimization
			$query = "DROP TABLE IF EXISTS p_ips" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "CREATE TABLE IF NOT EXISTS p_ips ( ip varchar(32) NOT NULL, created int(10) unsigned NOT NULL, t_footprints int(10) unsigned NOT NULL, t_requests int(10) unsigned NOT NULL, t_initiate int(10) unsigned NOT NULL, i_footprints int(10) unsigned NOT NULL, i_timestamp int(10) unsigned NOT NULL, i_initiate int(10) unsigned NOT NULL, PRIMARY KEY (ip), KEY created (created) )" ;
			database_mysql_query( $dbh, $query ) ;
			// end reset

			Util_Vals_WriteVersion( "4.3.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/85" ) )
	{ $patched = 85 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD t_vses TINYINT( 2 ) UNSIGNED NOT NULL AFTER op2op" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_requests ADD agent_md5 VARCHAR( 32 ) NOT NULL AFTER agent, ADD INDEX ( agent_md5 )" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "CREATE TABLE IF NOT EXISTS p_canned_auto ( opID int(10) unsigned NOT NULL, canID int(10) unsigned NOT NULL, PRIMARY KEY (opID) )" ;
			database_mysql_query( $dbh, $query ) ;

			$query = "ALTER TABLE p_messages ADD custom VARCHAR( 255 ) $charset_string NOT NULL AFTER refer" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;

			$query = "ALTER TABLE p_operators ADD curc TINYINT( 1 ) UNSIGNED NOT NULL AFTER dn , ADD maxc TINYINT( 1 ) NOT NULL AFTER curc" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh["error"] == "None" )
			{
				$query = "UPDATE p_operators SET maxc = -1" ;
				database_mysql_query( $dbh, $query ) ;
			}

			if ( isset( $CONF["SQLTYPE"] ) && !preg_match( "/(SQLi)|(PDO)/", $CONF["SQLTYPE"] ) ) { Util_Vals_WriteToConfFile( "SQLTYPE", "SQL.php" ) ; }
			if ( !isset( $CONF["SQLTYPE"] ) ) { Util_Vals_WriteToConfFile( "SQLTYPE", "SQL.php" ) ; }
			Util_Vals_WriteVersion( "4.3.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/86" ) )
	{ $patched = 86 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.3.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/87" ) )
	{ $patched = 87 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.3.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/88" ) )
	{ $patched = 88 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.3.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/89" ) )
	{ $patched = 89 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD rquestion TINYINT NOT NULL AFTER texpire" ;
			database_mysql_query( $dbh, $query ) ;
			if ( $dbh["error"] == "None" )
			{
				$query = "UPDATE p_departments SET rquestion = 1" ;
				database_mysql_query( $dbh, $query ) ;
			}

			$query = "ALTER TABLE p_vars ADD ts_clear INT UNSIGNED NOT NULL AFTER ts_clean" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_footstats" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footstats CHANGE mdfive md5_page VARCHAR( 32 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_referstats" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_referstats CHANGE mdfive md5_page VARCHAR( 32 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_footprints" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints ADD md5_vis VARCHAR( 32 ) NOT NULL AFTER browser, ADD INDEX ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints DROP INDEX mdfive" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints CHANGE mdfive md5_page VARCHAR( 32 )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints ADD INDEX ( md5_page )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "TRUNCATE TABLE p_refer" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_refer DROP ip" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_refer ADD md5_vis VARCHAR( 32 ) NOT NULL FIRST, ADD PRIMARY KEY ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_refer CHANGE mdfive md5_page VARCHAR( 32 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_refer DROP INDEX mdfive, ADD INDEX md5_page ( md5_page )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_footprints_u" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u DROP INDEX ip" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u DROP hostname" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u DROP agent" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD INDEX ( created )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD md5_vis VARCHAR( 32 ) NOT NULL FIRST, ADD PRIMARY KEY ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "TRUNCATE TABLE p_ips" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_ips DROP INDEX ip" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips DROP PRIMARY KEY" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips CHANGE ip ip VARCHAR( 45 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips ADD INDEX ( ip )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_ips ADD md5_vis VARCHAR( 32 ) NOT NULL FIRST, ADD PRIMARY KEY ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests DROP agent_md5" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests DROP hostname" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests DROP agent" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests ADD md5_vis VARCHAR( 32 ) NOT NULL AFTER ip, ADD INDEX ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests ADD md5_vis_ VARCHAR( 32 ) NOT NULL AFTER md5_vis" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log DROP hostname" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log DROP agent" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log DROP INDEX ip" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log ADD md5_vis VARCHAR( 32 ) NOT NULL AFTER ip, ADD INDEX ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts DROP INDEX ip" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts ADD md5_vis VARCHAR( 32 ) NOT NULL AFTER ip, ADD INDEX ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_messages DROP agent" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/90" ) )
	{ $patched = 90 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/91" ) )
	{ $patched = 91 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/92" ) )
	{ $patched = 92 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/93" ) )
	{ $patched = 93 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_footstats DROP mdfive" ; // safety check
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_referstats DROP mdfive" ; // safety check
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts CHANGE question question MEDIUMTEXT $charset_string NOT NULL , CHANGE formatted formatted MEDIUMTEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_operators CHANGE smsnum smsnum VARCHAR( 155 ) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/94" ) )
	{ $patched = 94 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/95" ) )
	{ $patched = 95 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/96" ) )
	{ $patched = 96 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/97" ) )
	{ $patched = 97 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "CREATE TABLE IF NOT EXISTS p_dept_vars ( deptID int(11) NOT NULL, greeting_title varchar(255) $charset_string NOT NULL, greeting_body varchar(255) $charset_string NOT NULL, PRIMARY KEY (deptID) )" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/98" ) )
	{ $patched = 98 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators DROP curc" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u DROP PRIMARY KEY" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD footprintID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u DROP INDEX md5_vis" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_messages ADD md5_vis VARCHAR(32) NOT NULL AFTER vemail, ADD INDEX ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints_u ADD UNIQUE ( md5_vis )" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/99" ) )
	{ $patched = 99 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_transcripts DROP INDEX deptID" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts DROP INDEX op2op" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts ADD INDEX( deptID )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts ADD INDEX( op2op )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints_u DROP footprintID" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.4.91" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/100" ) )
	{ $patched = 100 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log DROP INDEX created" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log ADD status_msg TINYINT(1) NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_messages ADD ces VARCHAR(32) NOT NULL AFTER vemail" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log ADD INDEX( created )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.4.92" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/101" ) )
	{ $patched = 101 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.93" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/102" ) )
	{ $patched = 102 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.94" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/103" ) )
	{ $patched = 103 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD emailt VARCHAR(160) NOT NULL AFTER email" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.95" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/104" ) )
	{ $patched = 104 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD canID TINYINT NOT NULL AFTER maxc" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.96" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/105" ) )
	{ $patched = 105 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD emailt_bcc TINYINT(1) NOT NULL AFTER emailt" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.97" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/106" ) )
	{ $patched = 106 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.98" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/107" ) )
	{ $patched = 107 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/108" ) )
	{ $patched = 108 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_canned ADD auto_select TINYINT(1) NOT NULL AFTER deptID" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/109" ) )
	{ $patched = 109 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/110" ) )
	{ $patched = 110 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/111" ) )
	{ $patched = 111 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log DROP INDEX archive" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_refer DROP INDEX archive" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints DROP INDEX archive" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log ADD archive TINYINT(1) NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_req_log ADD INDEX ( archive )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_refer ADD archive TINYINT(1) NOT NULL AFTER created" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_refer ADD INDEX ( archive )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints ADD archive TINYINT(1) NOT NULL AFTER created" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints ADD INDEX ( archive )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "TRUNCATE TABLE p_footprints_u" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u ADD footID INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (footID)" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_vars ADD idle_o TINYINT UNSIGNED NOT NULL AFTER deptID, ADD idle_v TINYINT UNSIGNED NOT NULL AFTER idle_o" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log ADD idle_disconnect TINYINT NOT NULL AFTER browser" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_op_vars (opID int(10) unsigned NOT NULL, sound tinyint(1) NOT NULL, blink tinyint(1) NOT NULL, blink_r tinyint(1) NOT NULL, dn_response tinyint(1) NOT NULL, dn_always tinyint(1) NOT NULL, PRIMARY KEY (opID))" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log CHANGE custom custom TEXT NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_requests CHANGE custom custom TEXT NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/112" ) )
	{ $patched = 112 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/113" ) )
	{ $patched = 113 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "CREATE TABLE IF NOT EXISTS p_lang_packs ( lang varchar(15) NOT NULL, lang_vars TEXT $charset_string NOT NULL, UNIQUE KEY lang (lang) )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_messages CHANGE custom custom TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.4.99.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/114" ) )
	{ $patched = 114 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/115" ) )
	{ $patched = 115 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/116" ) )
	{ $patched = 116 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_transcripts CHANGE plain plain MEDIUMTEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/117" ) )
	{ $patched = 117 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_vars ADD trans_f_dept TINYINT UNSIGNED NOT NULL AFTER idle_v" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests ADD ended INT UNSIGNED NOT NULL AFTER created" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.91" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/118" ) )
	{ $patched = 118 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.92" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/119" ) )
	{ $patched = 119 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.93" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/120" ) )
	{ $patched = 120 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators CHANGE pic pic TINYINT(1) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_vars ADD profile_pic TINYINT(1) UNSIGNED NOT NULL AFTER char_set" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_op_vars ADD nsleep TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER dn_always" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.94" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/121" ) )
	{ $patched = 121 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.95" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/122" ) )
	{ $patched = 122 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.96" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/123" ) )
	{ $patched = 123 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_op_vars ADD shorts TINYINT(1) UNSIGNED NOT NULL AFTER nsleep" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_canned DROP auto_select" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_op_vars ADD canID INT UNSIGNED NOT NULL AFTER opID" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.97" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/124" ) )
	{ $patched = 124 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			if ( !isset( $CONF["SALT"] ) ) { Util_Vals_WriteToConfFile( "SALT", Util_Format_RandomString( 32 ) ) ; } // check again
			$query = "ALTER TABLE p_operators ADD mapper TINYINT(1) UNSIGNED NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators ADD mapp TINYINT(1) UNSIGNED NOT NULL AFTER mapper" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_opstatus_log ADD mapp TINYINT(1) UNSIGNED NOT NULL AFTER status" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "TRUNCATE TABLE p_ips" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			if ( !isset( $VALS["op_sounds"] ) )
			{
				include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;
		
				$query = "SELECT * FROM p_operators" ;
				database_mysql_query( $dbh, $query ) ;

				$op_sounds = Array() ; $update_vals = 0 ;
				while ( $data = database_mysql_fetchrow( $dbh ) )
				{
					if ( isset( $data["sound1"] ) )
					{
						$opid = $data["opID"] ;
						$sound1 = $data["sound1"] ;
						$sound2 = $data["sound2"] ;
						$op_sounds[$opid] = Array( $sound1, $sound2 ) ;
						$update_vals = 1 ;
					}
				}

				Util_Vals_WriteToFile( "op_sounds", serialize( $op_sounds ) ) ;
			}
			$query = "ALTER TABLE p_operators DROP sound1" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators DROP sound2" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.98" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/125" ) )
	{ $patched = 125 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.99" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/126" ) )
	{ $patched = 126 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_op_vars ADD mapp_c TINYINT(1) UNSIGNED NOT NULL AFTER shorts" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.4.99.99.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/127" ) )
	{ $patched = 127 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.99.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/128" ) )
	{ $patched = 128 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.4.99.99.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/129" ) )
	{ $patched = 129 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/130" ) )
	{ $patched = 130 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_vars ADD prechat_form TINYINT(1) NOT NULL DEFAULT 1 AFTER trans_f_dept" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/131" ) )
	{ $patched = 131 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD nchats TINYINT(1) NOT NULL DEFAULT 1 AFTER viewip" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_op_vars ADD dn_request TINYINT(1) NOT NULL AFTER dn_response" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators DROP dn" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/132" ) )
	{ $patched = 132 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_transcripts ADD encr TINYINT(1) NOT NULL AFTER rating" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_messages DROP status" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_messages DROP locked" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts DROP INDEX encr" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts ADD INDEX ( encr )" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.5.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/133" ) )
	{ $patched = 133 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/134" ) )
	{ $patched = 134 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/135" ) )
	{ $patched = 135 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD country VARCHAR(3) NOT NULL AFTER ip" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u CHANGE country country VARCHAR(2) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u CHANGE region region VARCHAR(42) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u CHANGE city city VARCHAR(50) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/136" ) )
	{ $patched = 136 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/137" ) )
	{ $patched = 137 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD maxco TINYINT(1) NOT NULL AFTER maxc" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_notes ( noteID int(10) unsigned NOT NULL AUTO_INCREMENT, created int(10) unsigned NOT NULL, opID int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, ces varchar(32) NOT NULL, message tinytext $charset_string NOT NULL, PRIMARY KEY (noteID), KEY created (created), KEY opID (opID), KEY deptID (deptID), KEY ces (ces) )" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/138" ) )
	{ $patched = 138 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_transcripts ADD noteID INT(10) UNSIGNED NOT NULL AFTER fsize, ADD INDEX (noteID)" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.5.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/139" ) )
	{ $patched = 139 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_vars ADD varID TINYINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (varID)" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.9.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/140" ) )
	{ $patched = 140 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_op_vars ADD pic_edit TINYINT(1) UNSIGNED NOT NULL AFTER mapp_c" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_rstats_log ( ces varchar(32) NOT NULL, created int(10) unsigned NOT NULL, status tinyint(1) NOT NULL, opID int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, PRIMARY KEY (ces,opID), KEY created (created), KEY opID (opID), KEY deptID (deptID), KEY status (status) )" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/141" ) )
	{ $patched = 141 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			// duplicate check needed for patch 3 bug on previous v.4.5.9.2 not having the query (fixed since)
			$query = "CREATE TABLE IF NOT EXISTS p_rstats_log ( ces varchar(32) NOT NULL, created int(10) unsigned NOT NULL, status tinyint(1) NOT NULL, opID int(10) unsigned NOT NULL, deptID int(10) unsigned NOT NULL, PRIMARY KEY (ces,opID), KEY created (created), KEY opID (opID), KEY deptID (deptID), KEY status (status) )" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/142" ) )
	{ $patched = 142 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/143" ) )
	{ $patched = 143 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SHOW INDEX FROM p_opstatus_log" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "opID" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_opstatus_log ADD PRIMARY KEY (created, opID)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			Util_Vals_WriteVersion( "4.5.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/144" ) )
	{ $patched = 144 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/145" ) )
	{ $patched = 145 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/146" ) )
	{ $patched = 146 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/147" ) )
	{ $patched = 147 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests CHANGE custom custom TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_req_log CHANGE custom custom TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_departments CHANGE custom custom TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_transcripts ADD custom TEXT $charset_string NOT NULL AFTER md5_vis" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_dept_vars ADD offline_form TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER prechat_form" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.5.9.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/148" ) )
	{ $patched = 148 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.9-1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/149" ) )
	{ $patched = 149 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/150" ) )
	{ $patched = 150 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/151" ) )
	{ $patched = 151 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			if ( isset( $CONF['SALT'] ) && function_exists( "mcrypt_decrypt" ) )
			{
				$query = "SELECT * FROM p_departments" ;
				database_mysql_query( $dbh, $query ) ;
				$departments = Array() ;
				while ( $data = database_mysql_fetchrow( $dbh ) )
					$departments[] = $data ;

				for ( $c = 0; $c < count( $departments ); ++$c )
				{
					$department = $departments[$c] ;
					if ( phpversion() < 7.1 )
					{
						$serialized = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5($CONF['SALT']), base64_decode($department["smtp"]), MCRYPT_MODE_CBC, md5(md5($CONF['SALT'])) ), "\0" ) ;
						if ( Util_Functions_itr_is_serialized( $serialized ) )
						{
							$smtp_encoded = base64_encode( $serialized ) ;
							$query = "UPDATE p_departments SET smtp = '$smtp_encoded' WHERE deptID = $department[deptID]" ;
							database_mysql_query( $dbh, $query ) ;
						}
					} else { $query = "UPDATE p_departments SET smtp = '' WHERE deptID = $department[deptID]" ; database_mysql_query( $dbh, $query ) ; }
				}
			}
			Util_Vals_WriteVersion( "4.5.9.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/152" ) )
	{ $patched = 152 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/153" ) )
	{ $patched = 153 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SHOW INDEX FROM p_operators" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "name" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_operators ADD INDEX(name)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			$query = "ALTER TABLE p_ips DROP i_timestamp" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_ips DROP i_initiate" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints DROP INDEX md5_page_2" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.5.9.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/154" ) )
	{ $patched = 154 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.5.9.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/155" ) )
	{ $patched = 155 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log ADD tag TINYINT UNSIGNED NOT NULL AFTER idle_disconnect, ADD INDEX tag (tag)" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_transcripts ADD tag TINYINT UNSIGNED NOT NULL AFTER noteID, ADD INDEX tag (tag)" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_operators ADD tag TINYINT(1) NOT NULL DEFAULT 1 AFTER canID" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/156" ) )
	{ $patched = 156 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "UPDATE p_operators SET maxc = 5 WHERE maxc = -1" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_vars ADD ts_queue INT UNSIGNED NOT NULL AFTER ts_clear" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "SHOW INDEX FROM p_operators" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "maxc" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_operators ADD INDEX(maxc)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_operators ADD INDEX(mapp)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_operators ADD INDEX(rate)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_operators ADD INDEX(sms)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_operators ADD INDEX(smsnum)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_operators ADD INDEX(email)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			$query = "CREATE TABLE IF NOT EXISTS p_queue ( queueID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, created int(11) NOT NULL, updated int(10) UNSIGNED NOT NULL, deptID int(10) UNSIGNED NOT NULL, ces varchar(32) NOT NULL, md5_vis varchar(32) NOT NULL, ops_d varchar(200) $charset_string NOT NULL, PRIMARY KEY (queueID), KEY ces (ces), KEY md5_vis (md5_vis), KEY deptID (deptID), KEY ops_d (ops_d) )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_queue_log ( logID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, created int(10) UNSIGNED NOT NULL, ended int(10) UNSIGNED NOT NULL, sdate int(10) UNSIGNED NOT NULL, status tinyint(1) NOT NULL, deptID int(10) UNSIGNED NOT NULL, ces varchar(32) NOT NULL, PRIMARY KEY (logID), KEY created (created), KEY ended (ended), KEY status (status), KEY ces (ces), KEY deptID (deptID), KEY sdate (sdate) )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_vars ADD qest TINYINT(1) UNSIGNED NOT NULL AFTER offline_form, ADD qpos TINYINT(1) UNSIGNED NOT NULL AFTER qest, ADD qlimit TINYINT(2) UNSIGNED NOT NULL DEFAULT 5 AFTER qpos, ADD qtexts VARCHAR(255) $charset_string NOT NULL AFTER qlimit" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.6.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/157" ) )
	{ $patched = 157 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_queue ADD embed TINYINT(1) UNSIGNED NOT NULL AFTER deptID, ADD INDEX (embed)" ;
			database_mysql_query( $dbh, $query ) ;
			touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/158" ) )
	{ $patched = 158 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/159" ) )
	{ $patched = 159 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_op_vars ADD vis_idle_canned VARCHAR(90) NOT NULL AFTER pic_edit" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.6.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/160" ) )
	{ $patched = 160 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/161" ) )
	{ $patched = 161 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/162" ) )
	{ $patched = 162 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/163" ) )
	{ $patched = 163 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/164" ) )
	{ $patched = 164 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/165" ) )
	{ $patched = 165 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD vupload TINYINT(1) NOT NULL AFTER savem" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators ADD upload TINYINT(1) NOT NULL AFTER tag" ;
			database_mysql_query( $dbh, $query ) ;
			// reset the debug.txt file if it exists
			if ( is_file( "$CONF[CONF_ROOT]/debug.txt" ) ) { @unlink( "$CONF[CONF_ROOT]/debug.txt" ) ; }
			Util_Vals_WriteVersion( "4.6.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/166" ) )
	{ $patched = 166 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/167" ) )
	{ $patched = 167 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SHOW INDEX FROM p_req_log" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "ended" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_req_log ADD INDEX(ended)" ;
				database_mysql_query( $dbh, $query ) ;
				usleep( 250000 ) ;
				$query = "ALTER TABLE p_req_log ADD INDEX(deptID)" ;
				database_mysql_query( $dbh, $query ) ;
				usleep( 250000 ) ;
				$query = "ALTER TABLE p_req_log ADD INDEX(initiated)" ;
				database_mysql_query( $dbh, $query ) ;
				usleep( 250000 ) ;
				$query = "ALTER TABLE p_req_log ADD INDEX(op2op)" ;
				database_mysql_query( $dbh, $query ) ;
				usleep( 250000 ) ;
			}
			$query = "ALTER TABLE p_req_log ADD accepted INT UNSIGNED NOT NULL AFTER created, ADD accepted_op INT UNSIGNED NOT NULL AFTER accepted, ADD duration MEDIUMINT UNSIGNED NOT NULL AFTER accepted_op, ADD INDEX (accepted), ADD INDEX (accepted_op), ADD INDEX (duration)" ;
			usleep( 250000 ) ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_transcripts ADD accepted_op INT UNSIGNED NOT NULL AFTER opID, ADD INDEX (accepted_op)" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_departments ADD emailm_cc VARCHAR(160) NOT NULL AFTER email" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments ADD ctimer TINYINT(1) NOT NULL DEFAULT 1 AFTER vupload" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_vars ADD end_chat_msg TEXT $charset_string NOT NULL AFTER greeting_body" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_op_vars ADD upload_ses INT UNSIGNED NOT NULL AFTER pic_edit" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_marquees" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_rstats_depts DROP initiated_" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_rstats_ops DROP initiated_" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_requests ADD rloop TINYINT UNSIGNED NOT NULL AFTER requests" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.6.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched"."_" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched"."__" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/168" ) )
	{ $patched = 168 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/169" ) )
	{ $patched = 169 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/170" ) )
	{ $patched = 170 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/171" ) )
	{ $patched = 171 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/172" ) )
	{ $patched = 172 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/173" ) )
	{ $patched = 173 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments CHANGE vupload vupload VARCHAR(45) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators CHANGE upload upload VARCHAR(45) NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.6.9.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/174" ) )
	{ $patched = 174 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.6.9.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/175" ) )
	{ $patched = 175 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/176" ) )
	{ $patched = 176 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD tloop TINYINT NOT NULL AFTER rloop" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/177" ) )
	{ $patched = 177 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/178" ) )
	{ $patched = 178 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_transcripts ADD atID INT UNSIGNED NOT NULL AFTER noteID, ADD INDEX (atID)" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_requests ADD slack TINYINT(1) UNSIGNED NOT NULL AFTER marketID" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/179" ) )
	{ $patched = 179 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/180" ) )
	{ $patched = 180 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/181" ) )
	{ $patched = 181 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_transcripts CHANGE op2op op2op INT(10) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/182" ) )
	{ $patched = 182 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SHOW INDEX FROM p_lang_packs" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "lang" ) { $found = 1 ; } }
			if ( $found )
			{
				$query = "SELECT * FROM p_departments" ;
				database_mysql_query( $dbh, $query ) ;
				$departments = Array() ;
				while ( $data = database_mysql_fetchrow( $dbh ) )
					$departments[] = $data ;

				$query = "SELECT * FROM p_lang_packs" ;
				database_mysql_query( $dbh, $query ) ;
				$lang_packs = Array() ;
				while ( $data = database_mysql_fetchrow( $dbh ) )
				{
					$lang = $data["lang"] ;
					$lang_string = $data["lang_vars"] ;
					$lang_packs[$lang] = $lang_string ;
				}

				$query = "TRUNCATE TABLE p_lang_packs" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_lang_packs DROP lang" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_lang_packs ADD deptID INT UNSIGNED NOT NULL FIRST, ADD UNIQUE (deptID)" ;
				database_mysql_query( $dbh, $query ) ;

				for ( $c = 0; $c < count( $departments ); ++$c )
				{
					$deptinfo = $departments[$c] ;
					$deptid = $deptinfo["deptID"] ;
					$lang = $deptinfo["lang"] ;
					if ( $lang && isset( $lang_packs[$lang] ) )
					{
						$query = "INSERT INTO p_lang_packs VALUES( $deptid, '$lang_packs[$lang]' )" ;
						database_mysql_query( $dbh, $query ) ;
					}
				}
			}
			Util_Vals_WriteVersion( "4.7.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/183" ) )
	{ $patched = 183 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/184" ) )
	{ $patched = 184 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "CREATE TABLE IF NOT EXISTS p_proaction_c ( proactionID int(11) NOT NULL, sdate int(10) UNSIGNED NOT NULL, views mediumint(8) UNSIGNED NOT NULL, taken mediumint(8) UNSIGNED NOT NULL, declined mediumint(8) UNSIGNED NOT NULL, PRIMARY KEY (proactionID,sdate), KEY views (views,taken,declined) )" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "SHOW INDEX FROM p_operators" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "lastactive" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_operators ADD INDEX(lastactive)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			$query = "ALTER TABLE p_dept_vars ADD gdpr_msg TEXT $charset_string NOT NULL AFTER end_chat_msg" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_gdpr" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/185" ) )
	{ $patched = 185 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_op_vars ADD can_cats TEXT $charset_string NOT NULL AFTER vis_idle_canned" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_canned ADD catID SMALLINT NOT NULL DEFAULT -1 AFTER deptID, ADD cats_extra TEXT NOT NULL AFTER catID" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/186" ) )
	{ $patched = 186 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/187" ) )
	{ $patched = 187 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/188" ) )
	{ $patched = 188 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_departments ADD rname TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER texpire" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_emarketing ( emarketID INT UNSIGNED NOT NULL AUTO_INCREMENT, created INT UNSIGNED NOT NULL, thevalue TINYINT(1) UNSIGNED NOT NULL, email VARCHAR(160) $charset_string NOT NULL, PRIMARY KEY (emarketID), INDEX (created), INDEX (thevalue), UNIQUE (email) )" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/189" ) )
	{ $patched = 189 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_vars ADD emarketID INT UNSIGNED NOT NULL AFTER qlimit" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_emarketing CHANGE emarketID emarketID INT(10) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "SHOW INDEX FROM p_emarketing" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "sdate" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_emarketing DROP PRIMARY KEY, ADD INDEX (emarketID), DROP INDEX email, ADD INDEX email (email)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_emarketing ADD md5_vis VARCHAR(32) NOT NULL AFTER email, ADD sdate INT UNSIGNED NOT NULL AFTER md5_vis, ADD ces VARCHAR(32) NOT NULL AFTER sdate, ADD INDEX (md5_vis), ADD INDEX (sdate), ADD INDEX (ces)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_emarketing ADD statID INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (statID)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_emarketing DROP INDEX emarketID" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_emarketing ADD UNIQUE( emarketID, md5_vis)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_emarketing CHANGE thevalue thevalue TINYINT(1) NOT NULL" ;
				database_mysql_query( $dbh, $query ) ;
			}
			Util_Vals_WriteVersion( "4.7.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/190" ) )
	{ $patched = 190 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/191" ) )
	{ $patched = 191 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/192" ) )
	{ $patched = 192 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/193" ) )
	{ $patched = 193 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/194" ) )
	{ $patched = 194 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/195" ) )
	{ $patched = 195 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/196" ) )
	{ $patched = 196 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/197" ) )
	{ $patched = 197 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/198" ) )
	{ $patched = 198 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "UPDATE p_operators SET sms = 0, smsnum = ''" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE p_dept_groups ( groupID INT UNSIGNED NOT NULL, name VARCHAR(45) $charset_string NOT NULL, deptids VARCHAR(45) NOT NULL, PRIMARY KEY (groupID))" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_dept_vars ADD timestamp TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER qlimit" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators ADD pic_form_display TINYINT(1) UNSIGNED NOT NULL AFTER pic" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "SHOW INDEX FROM p_req_log" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "status_msg" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "CREATE INDEX status_msg ON p_req_log ( status, status_msg )" ;
				database_mysql_query( $dbh, $query ) ;
				usleep( 250000 ) ;
			}
			$query = "ALTER TABLE p_op_vars ADD lang_vars TEXT $charset_string NOT NULL AFTER can_cats" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/199" ) )
	{ $patched = 199 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_vars ADD offline_msg_template TEXT $charset_string NOT NULL AFTER gdpr_msg" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/200" ) )
	{ $patched = 200 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/201" ) )
	{ $patched = 201 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/202" ) )
	{ $patched = 202 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/203" ) )
	{ $patched = 203 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/204" ) )
	{ $patched = 204 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/205" ) )
	{ $patched = 205 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/206" ) )
	{ $patched = 206 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	if ( !is_file( "$CONF[CONF_ROOT]/patches/207" ) )
	{ $patched = 207 ;
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
		}
	}
	$patched = 208 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 209 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 210 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.8.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 211 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 212 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.9.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 213 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_groups ADD lang VARCHAR(15) NOT NULL DEFAULT 'english' AFTER name" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9.9.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 214 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 215 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "CREATE TABLE IF NOT EXISTS p_live_qa ( dataID int(10) UNSIGNED NOT NULL AUTO_INCREMENT, qaID int(10) UNSIGNED NOT NULL, created int(10) UNSIGNED NOT NULL, status tinyint(4) NOT NULL, deptID int(10) UNSIGNED NOT NULL, opID int(10) UNSIGNED NOT NULL, vis_token varchar(32) NOT NULL, ip varchar(45) NOT NULL, email varchar(160) NOT NULL, message text CHARACTER SET utf8 NOT NULL, PRIMARY KEY ( dataID ), KEY vis_token (vis_token), KEY qaID (qaID,created,status,deptID,opID))" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE IF NOT EXISTS p_live_qa_log ( dataID int(10) UNSIGNED NOT NULL, qaID int(10) UNSIGNED NOT NULL, created int(10) UNSIGNED NOT NULL, status tinyint(4) NOT NULL, deptID int(10) UNSIGNED NOT NULL, opID int(10) UNSIGNED NOT NULL, vis_token varchar(32) NOT NULL, ip varchar(45) NOT NULL, email varchar(160) NOT NULL, message text CHARACTER SET utf8 NOT NULL, PRIMARY KEY ( dataID ), KEY vis_token (vis_token), KEY qaID (qaID,created,status,deptID,opID))" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_refer CHANGE refer refer TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_footprints_u CHANGE refer refer TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_requests CHANGE refer refer TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_messages CHANGE refer refer TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			$query = "ALTER TABLE p_referstats CHANGE refer refer TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			usleep( 250000 ) ;
			Util_Vals_WriteVersion( "4.7.9.9.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 216 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 217 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 218 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_live_qa CHANGE dataID dataID INT(10) UNSIGNED NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.9.9.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 219 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.9.9.9.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 220 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_ops ADD dept_offline TINYINT(1) UNSIGNED NOT NULL AFTER status, ADD INDEX (dept_offline)" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators ADD dept_offline TINYINT(1) NOT NULL AFTER pic_form_display" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "SHOW INDEX FROM p_requests" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "updated" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_requests ADD INDEX(updated)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_requests ADD INDEX(vupdated)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_requests ADD INDEX(ended)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			Util_Vals_WriteVersion( "4.7.9.9.9.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 221 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.91" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 222 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "UPDATE p_departments SET rloop = 1" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.92" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 223 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_notes ADD isnote TINYINT UNSIGNED NOT NULL AFTER deptID, ADD INDEX (isnote)" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.93" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 224 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "DROP TABLE IF EXISTS p_live_qa" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_live_qa_log" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_geo_bloc" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_geo_loc" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "DROP TABLE IF EXISTS p_socials" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.94" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 225 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.95" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 226 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.96" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 227 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.97" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 228 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.98" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 229 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 230 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_dept_vars ADD offline_auto_reply TEXT $charset_string NOT NULL AFTER offline_msg_template" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_requests DROP t_vses" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments DROP rloop" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.99.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 231 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 232 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_req_log ADD disc TINYINT UNSIGNED NOT NULL AFTER tag" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments ADD display TINYINT UNSIGNED NOT NULL AFTER visible" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_admins ADD access TEXT $charset_string NOT NULL AFTER email" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_departments ADD aemail TINYINT NOT NULL AFTER temail" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.99.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 233 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 234 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 235 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_notes CHANGE message message TEXT $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "UPDATE p_departments SET texpire = 0" ; // reset due to new minutes method
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.99.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 236 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 237 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests CHANGE slack bses_id VARCHAR(32) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "CREATE TABLE p_mboard ( messageID INT UNSIGNED NOT NULL AUTO_INCREMENT, created INT UNSIGNED NOT NULL, status TINYINT UNSIGNED NOT NULL, opID INT UNSIGNED NOT NULL, message TEXT $charset_string NOT NULL, PRIMARY KEY (messageID), INDEX (created), INDEX (status), INDEX (opID), INDEX (messageID, opID))" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.99.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 238 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SHOW INDEX FROM p_footprints_u" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "ip" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_footprints_u ADD INDEX(ip)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			Util_Vals_WriteVersion( "4.7.99.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 239 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators ADD view_chats TINYINT(1) UNSIGNED NOT NULL AFTER pic_form_display" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.99.9.1" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 240 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_requests ADD peer INT UNSIGNED NOT NULL AFTER tloop" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_operators ADD peer TINYINT(1) UNSIGNED NOT NULL AFTER tag" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_footprints_u CHANGE title title VARCHAR(255) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "SHOW INDEX FROM p_requests" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "md5_vis_" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "UPDATE p_transcripts SET atID = 0 WHERE atID <> 0" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_transcripts DROP INDEX atID" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_transcripts CHANGE atID marketID TINYINT UNSIGNED NOT NULL" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_requests ADD INDEX(md5_vis_)" ;
				database_mysql_query( $dbh, $query ) ;
				$query = "ALTER TABLE p_transcripts ADD INDEX(marketID)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			Util_Vals_WriteVersion( "4.7.99.9.2" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 241 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.9.3" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 242 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.9.4" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 243 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.9.5" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 244 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			if ( is_file( "$CONF[CONF_ROOT]/debug.txt" ) ) { @unlink( "$CONF[CONF_ROOT]/debug.txt" ) ; }
			Util_Vals_WriteVersion( "4.7.99.9.6" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 245 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.99.9.7" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 246 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Vals.php" ) ;

			$query = "UPDATE p_dept_vars SET timestamp = 0" ;
			database_mysql_query( $dbh, $query ) ;

			// 1595123820 when modern style was available
			$created = is_file( "$CONF[CONF_ROOT]/patches/1" ) ? filemtime ( "$CONF[CONF_ROOT]/patches/1" ) : $now ;
			if ( !isset( $VALS["STYLE"] ) && ( $created < 1595123820 ) ) { Util_Vals_WriteToFile( "STYLE", "classic" ) ; }

			Util_Vals_WriteVersion( "4.7.99.9.8" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 247 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_operators CHANGE login login VARCHAR(60) $charset_string NOT NULL" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.99.9.9" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 248 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "SHOW INDEX FROM p_req_log" ;
			database_mysql_query( $dbh, $query ) ;
			$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "transferred" ) { $found = 1 ; } }
			if ( !$found )
			{
				$query = "ALTER TABLE p_req_log CHANGE idle_disconnect transferred TINYINT(1) UNSIGNED NOT NULL" ;
				database_mysql_query( $dbh, $query ) ; usleep( 250000 ) ; // to limit issues on some server environments
				$query = "ALTER TABLE p_req_log ADD INDEX(transferred)" ;
				database_mysql_query( $dbh, $query ) ;
			}
			Util_Vals_WriteVersion( "4.7.101" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 249 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.102" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 250 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			$query = "ALTER TABLE p_rstats_depts ADD transfer INT UNSIGNED NOT NULL AFTER declined, ADD transfer_a INT UNSIGNED NOT NULL AFTER transfer" ;
			database_mysql_query( $dbh, $query ) ;
			$query = "ALTER TABLE p_rstats_ops ADD transfer INT UNSIGNED NOT NULL AFTER declined, ADD transfer_a INT UNSIGNED NOT NULL AFTER transfer" ;
			database_mysql_query( $dbh, $query ) ;
			Util_Vals_WriteVersion( "4.7.103" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 251 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.104" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	$patched = 252 ; if ( !is_file( "$CONF[CONF_ROOT]/patches/$patched" ) )
	{
		if ( $patch_init ) { $patches_array[$patched] = false ; }
		else if ( $patches_array[$patched] == true )
		{
			Util_Vals_WriteVersion( "4.7.105" ) ; touch( "$CONF[CONF_ROOT]/patches/$patched" ) ;
			$patch_processed = 1 ;
		}
	}
	if ( $patch_processed && ( $patch_v == $patched ) )
	{
		/*******************************************/
		// Always the closing queries due to possible errors that may stop the patch and
		// always process with each patch just in case MySQL upgraded from previous non support
		$query = "SHOW INDEX FROM p_transcripts" ;
		database_mysql_query( $dbh, $query ) ;
		$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "plain" ) { $found = 1 ; } }
		if ( !$found )
		{
			$query = "ALTER TABLE p_transcripts ADD FULLTEXT plain (plain)" ;
			database_mysql_query( $dbh, $query ) ;
		}
		$query = "SHOW INDEX FROM p_transcripts" ;
		database_mysql_query( $dbh, $query ) ;
		$found = 0 ; while ( $data = database_mysql_fetchrow( $dbh ) ) { if ( $data["Column_name"] == "custom" ) { $found = 1 ; } }
		if ( !$found )
		{
			$query = "ALTER TABLE p_transcripts ADD FULLTEXT custom (custom)" ;
			database_mysql_query( $dbh, $query ) ;
		}
		/*******************************************/
	}
?>
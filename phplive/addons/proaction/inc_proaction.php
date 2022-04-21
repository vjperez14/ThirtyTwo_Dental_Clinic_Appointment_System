<?php
	$temp = base64_decode( $VALS_ADDONS["proaction"] ) ;
	if ( Util_Functions_itr_is_serialized( $temp ) )
	{
		$this_proaction = unserialize( $temp ) ; $this_proaction_final = Array() ;
		foreach ( $this_proaction as $this_proactionid => $this_value )
		{
			$this_proaction_array = $this_value ;
			foreach ( $this_proaction_array as $this_key => $this_value )
			{
				if ( $this_key == "deptid" )
				{
					if ( ( $this_value == $deptid ) || !$deptid )
					{
						if ( !isset( $this_proaction_array["paused"] ) )
							$this_proaction_final[$this_proactionid] = $this_proaction_array ;
					}
				}
			}
		}

		$proactions_priority_array = Array() ;
		foreach ( $this_proaction_final as $thisproid => $proaction_array )
		{
			$priority = $this_proaction_final[$thisproid]["priority"] ;
			$proactions_priority_array[$priority] = $thisproid ;
		} krsort( $proactions_priority_array ) ;

		$proaction_pics_final = Array() ;
		foreach ( $proactions_priority_array as $null => $this_proactionid )
		{
			$this_proaction_array = $this_proaction_final[$this_proactionid] ;
			$addon_proaction_js_init .= "phplive_addon_proaction['$this_proactionid'] = new Object ; " ;
			$addon_proaction_js_priority .= "phplive_addon_proaction_priority.push('$this_proactionid') ; " ;

			foreach ( $this_proaction_array as $this_key => $this_value )
			{
				$this_value = rawurlencode( $this_value ) ;
				$addon_proaction_js_settings .= "phplive_addon_proaction['$this_proactionid'][\"$this_key\"] = \"$this_value\" ; " ;
				if ( ( $this_key == "profile" ) && $this_value )
				{
					$this_deptid = $this_proaction_array["deptid"] ;
					if ( $this_deptid )
					{
						$dir_files = glob( "$CONF[CHAT_IO_DIR]/online_".$this_deptid."_*", GLOB_NOSORT ) ;
						$total_dir_files = count( $dir_files ) ;
						if ( $total_dir_files )
						{
							for ( $c = 0; $c < $total_dir_files; ++$c )
							{
								preg_match( "/online_".$this_deptid."_(\d+)\.info/i", $dir_files[$c], $matches ) ;
								if ( isset( $matches[1] ) && is_numeric( $matches[1] ) && !isset( $proaction_pics_final[$matches[1]] ) )
								{ $proaction_pics_final[$matches[1]] = Util_Upload_GetLogo( "profile", $matches[1] ) ; }
							}
						}
					}
					else
					{
						$dir_files = glob( "$CONF[CHAT_IO_DIR]/online_*", GLOB_NOSORT ) ;
						$total_dir_files = count( $dir_files ) ;
						if ( $total_dir_files )
						{
							for ( $c = 0; $c < $total_dir_files; ++$c )
							{
								preg_match( "/online_(\d+)_(\d+)\.info/i", $dir_files[$c], $matches ) ;
								if ( isset( $matches[2] ) && is_numeric( $matches[2] ) && !isset( $proaction_pics_final[$matches[2]] ) )
								{ $proaction_pics_final[$matches[2]] = Util_Upload_GetLogo( "profile", $matches[2] ) ; }
							}
						}
					}
				}
			}
		}
		foreach ( $proaction_pics_final as $this_opid => $this_value ) { $addon_proaction_js_pics .= "\"".$this_value."\"," ; }
		$addon_proaction_js_pics = substr_replace( $addon_proaction_js_pics, "", -1 ) ;
	}
?>
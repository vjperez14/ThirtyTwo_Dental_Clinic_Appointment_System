<?php
	if ( defined( 'API_Util_Functions' ) ) { return ; }	
	define( 'API_Util_Functions', true ) ;

	FUNCTION Util_Functions_Sort_Compare($a, $b){ return strnatcmp($b['total'], $a['total']) ; }

	FUNCTION Util_Functions_Bytes( $bytes, $precision = 1 )
	{
		$units = Array( 'bytes', 'KB', 'MB', 'GB', 'TB' ) ; 
		$bytes = max( $bytes, 0 ) ; 
		$pow = floor( ($bytes ? log($bytes) : 0) / log(1000) ) ; 
		$pow = min( $pow, count($units) - 1 ) ; 

		$bytes /= pow( 1000, $pow ) ;
		// $bytes /= ( 1 << (10 * $pow) ) ; 
		return round( $bytes, $precision ).' '.$units[$pow] ; 
	}

	FUNCTION Util_Functions_Page( $page, $index, $page_per, $total, $url, $query )
	{
		global $console ;
		global $month ;
		global $year ;
		if ( !isset( $console ) ) { $console = 0 ; }
		if ( !isset( $month ) ) { $month = date( "m", time() ) ; }
		if ( !isset( $year ) ) { $year = date( "Y", time() ) ; }

		$string = "<div style='margin-top: 15px;'>" ;

		$pages = $remainder = 0 ;

		$remainder = ( $total % $page_per ) ;
		$pages = floor( $total/$page_per ) ;
		$pages = ( $remainder ) ? $pages + 1 : $pages ;

		$span = 10 ;
		$remainder = ( $pages % $span ) ;
		$groups = floor( $pages/$span ) ;
		$groups = ( $remainder ) ? $groups + 1 : $groups ;
		$start = ( $index * $span ) ;
		$end = $start + $span ;

		$group_prev = "" ;
		if ( $index > 0 )
		{
			$c = $start - $span ;
			$new_index = $index - 1 ;
			$group_prev = "<div class=\"page\" onClick=\"location.href='$url?page=$c&m=$month&y=$year&index=$new_index&console=$console&$query'\">...prev</div>" ;
		}

		$group_next = "" ;
		if ( $index < ( $groups - 1 ) )
		{
			$c = $end ;
			$new_index = $index + 1 ;
			$group_next = "<div class=\"page\" onClick=\"location.href='$url?page=$c&m=$month&y=$year&index=$new_index&console=$console&$query'\">next...</div>" ;
		}

		$string .= $group_prev ;
		for ( $c = $start; $c < $end; ++$c )
		{
			if ( $c < $pages )
			{
				$this_page = $c + 1 ;

				if ( $c == $page )
					$string .= "<div class=\"page_focus\">$this_page</div>" ;
				else
					$string .= "<div class=\"page\" onClick=\"location.href='$url?page=$c&m=$month&y=$year&index=$index&console=$console&$query'\">$this_page</div>" ;
			}
		}
		$string .= $group_next . "<div style=\"clear: both;\"></div></div>" ;

		return ( $pages > 1 ) ? $string : "" ;
	}

	FUNCTION Util_Functions_Stars( $directory, $rating )
	{
		global $theme ;
		$star_img = "$directory/themes/$theme/stars.png" ;

		$output = "<div style='width: 60px;'>" ;
		for ( $c = 1; $c <= $rating; ++$c )
			$output .= "<div style='float: left; width: 12px; height: 12px; background: url( $star_img ) no-repeat; background-position: 0px -12px;' class='class_star'></div>" ;
		for ( $c2 = $c; $c2 <= 5; ++$c2 )
			$output .= "<div style='float: left; width: 12px; height: 12px; background: url( $star_img ) no-repeat;' class='class_star'></div>" ;
		$output .= "<div style='clear: both;'></div></div>" ;
		
		return $output ;
	}

	FUNCTION Util_Functions_SystemClean( $dbh )
	{
		global $CONF ;
		global $now ;

		$message_board_expired = $now - 94670856 ; // 3 years
		$query = "DELETE FROM p_mboard WHERE created < $message_board_expired" ;
		database_mysql_query( $dbh, $query ) ;
	}
?>
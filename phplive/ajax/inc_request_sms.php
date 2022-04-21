<?php
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Email.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Util_Functions_itr.php" ) ;
	include_once( "$CONF[DOCUMENT_ROOT]/API/Depts/get.php" ) ;

	$lang = Util_Format_Sanatize( $CONF["lang"], "ln" ) ;
	if ( is_file( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/$lang.php" ) ; }
	else { include_once( "$CONF[DOCUMENT_ROOT]/lang_packs/english.php" ) ; }
	$question_mapp = ( $inc_question ) ? $inc_question : "[ $LANG[TXT_LIVECHAT] ]" ;

	if ( $mapp_opid == 1111111111 )
	{
		include_once( "$CONF[DOCUMENT_ROOT]/API/Ops/get.php" ) ;
		$op_inactive = $now - 60 ;
		$op_inactive_mapp_processed = 0 ;
		$sim_ops = Util_Format_ExplodeString( "-", $requestinfo["sim_ops"] ) ;
		for ( $c = 0; $c < count( $sim_ops ); ++$c )
		{
			$mapp_opid = $sim_ops[$c] ;
			$opinfo = Ops_get_OpInfoByID( $dbh, $mapp_opid ) ;
			if ( isset( $opinfo["mapp"] ) )
			{
				if ( $opinfo["mapp"] && ( $opinfo["lastactive"] < $op_inactive ) && !is_file( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) )
				{
					$op_inactive_mapp_processed = 1 ;
					touch( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) ;
				}
				if ( $op_inactive_mapp_processed || is_file( "$CONF[TYPE_IO_DIR]/$mapp_opid.mapp" ) )
					Util_Functions_itr_Do_Mobile_Push() ;
			}
		}
	} else { Util_Functions_itr_Do_Mobile_Push() ; }
?>
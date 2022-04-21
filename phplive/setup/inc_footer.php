	<?php if ( is_file( "$CONF[DOCUMENT_ROOT]/setup/inc_footer_extra.php" ) ) { include_once( "$CONF[DOCUMENT_ROOT]/setup/inc_footer_extra.php" ) ; } ?>
	</div>

</div>

<div style="margin-top: 25px; padding-top: 85px; padding-bottom: 85px; background: url(<?php echo $CONF["BASE_URL"] ?>/pics/bg_fade_lite.png) no-repeat #272727; background-position: top center; border-top: 1px solid #272727; color: #686868;">
	<div style="width: 970px; margin: 0 auto; text-align: right;">
		<a href="<?php echo $CONF["BASE_URL"] ?>/setup/system.php">PHP Live! v.<?php echo $VERSION ?></a>

		<div id="div_debug_console_footer" style="<?php echo ( isset( $VAR_DEBUG_OUT ) && $VAR_DEBUG_OUT ) ? "" : "display: none;" ?> margin-top: 15px; text-align: right;">
			<span>
			<?php
				$var_process_end = ( $var_microtime ) ? microtime(true) : time() ;
				$pd = $var_process_end - $var_process_start ; if ( !$pd ) { $pd = 0.001 ; }
				$pd = str_replace( ",", ".", $pd ) ;
				$var_mem_peak = ( function_exists( "memory_get_peak_usage" ) ) ? round( memory_get_peak_usage( false )/1000 ) : 0 ;
				print "DB queries: " . $dbh['qc'] . " &bull; Process Duration: ".number_format( $pd, 3 )." &bull; Max Mem Usage: ".$var_mem_peak."kb" ;
			?>
			</span>
		</div>
	</div>
</div>

</body>
</html>
<?php
	if ( isset( $dbh ) && $dbh['con'] ) { database_mysql_close( $dbh ) ; }
?>
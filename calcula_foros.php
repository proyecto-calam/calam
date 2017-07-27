<?php
/*se configura la pagina */
	define('CLI_SCRIPT', true);

	require_once(dirname(__FILE__) . '/../config.php');
	require_once('lib.php');
	
	calculate_unam_stats_forum_for_instalation();
?>

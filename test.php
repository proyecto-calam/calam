<?php		
	require_once(dirname(__FILE__) . '/../../config.php');	
	require_once('lib.php');
/*
	$start_date = 1433116800;
	$end_date = 1498003200;		
	$userid = 74;

	/*Ajuste de las fechas para adecuar al formato GMT*/
	//$offset = date("Z");
	//$offset *= 1;
	//$start_date = strtotime($start_date);
	//$end_date = strtotime($end_date);
	/*$start_date += $offset;
	$end_date += $offset;

	$output ="";
			
	$string_output = "time_platform";
	$output = get_time_platform_user($start_date, $end_date, $userid);

	$output["opcion"] = array ("opcion"=>1);

	imprime_pre($output);

	echo json_encode($output);*/

	$now = new DateTime("now", core_date::get_server_timezone_object());
	$year = $now->format('Y');
	$month = $now->format('m');

	$now->setDate($year, $month, 1);
	$dayofweek = $now->format('N');
	echo $dayofweek;
?>
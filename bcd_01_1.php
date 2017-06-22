<?php		
	require_once(dirname(__FILE__) . '/../../config.php');	
	require_once('lib.php');

	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];		
	$userid = $_POST['userid'];
	
	$offset = date("Z");
	$offset *= 1;
	$start_date = strtotime($start_date);
	$end_date = strtotime($end_date);
	$start_date += $offset;
	$end_date += $offset;

	$output ="";			
	$string_output = "time_platform";
	$output = get_time_platform_user($start_date, $end_date, $userid);
	$output["opcion"] = array ("opcion"=>1);
	echo json_encode($output);
?>
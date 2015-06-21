<?php

require_once('../includes/general_functions.php');
require_once('../includes/constants.php');

print_r($_POST);

if($_POST['action'] == 'add'){
	// ADD THE FLIGHT
	if(isset($_POST['data']['type'])){
		if($_POST['data']['type']=='arr'){
			$type = AIR_TYPE_ARR;
		}
		else{
			$type = AIR_TYPE_DEP;
		}
	}
	else $type = 0;
	
	$sql = "INSERT INTO `air_flight` (`flight_no`,`airline`,`date`,`time`,`type`,`shifts`) VALUES ('{$_POST['data']['flight_no']}','{$_POST['data']['airline']}','{$_POST['data']['date']}','{$_POST['data']['time']}','{$type}','{$_POST['data']['shifts']}')";
	$query = new rQuery($con,$sql);
	$query->run();
	
	// GET THE ID
	$sql = "SELECT `flight_id` FROM `air_flight` ORDER BY `flight_id` DESC LIMIT 1";
	$query = new rQuery($con,$sql,false,false,true);
	$result = $query->run();
	$flight_id = $result[0]['flight_id'];
	
	// ADD THE DRIVER/PASSENGERS
	if(isset($_POST['data']['driver']) && is_array($_POST['data']['driver']) && count($_POST['data']['driver']) > 0)
	foreach($_POST['data']['driver'] as $d){
		$sql = "INSERT INTO `air_staff` (`flight_id`,`staff_id`,`type`) VALUES ('{$flight_id}','{$d}','".AIR_TYPE_DRIVER."')";
		$query = new rQuery($con,$sql);
		$query->run();
	}
	
	if(isset($_POST['data']['passenger']) && is_array($_POST['data']['passenger']) && count($_POST['data']['passenger']) > 0)
	foreach($_POST['data']['passenger'] as $d){
		$sql = "INSERT INTO `air_staff` (`flight_id`,`staff_id`,`type`) VALUES ('{$flight_id}','{$d}','".AIR_TYPE_PASSENGER."')";
		$query = new rQuery($con,$sql);
		$query->run();
	}
}
?>
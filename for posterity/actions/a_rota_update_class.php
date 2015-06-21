<?php
	require_once('../includes/rota_classes.php');
	
	$rc = new RotaCheck($con, $_POST['start_date'], $_POST['end_date'], $_POST['group_id']);
	
	$s = $rc->query_s(); // Staff. Tested and working
	$sd = $rc->query_sd(); // Staff Date. Tested and working
	$sp = $rc->query_sp(); // Staff Position. Tested and working
	$dhp = $rc->query_dhp();
	$sdh = $rc->query_sdh(); // Need to add general fixed
	$sdhp = $rc->query_sdhp(); // Need to add position fixed
		
	$data = array();
	
	if(count($s)>0)
	foreach($s as $x)
		array_push($data, $x);
	foreach($sd as $x)
		array_push($data, $x);
	foreach($sp as $x)
		array_push($data, $x);
	foreach($dhp as $x)
		array_push($data, $x);
	foreach($sdh as $x)
		array_push($data, $x);
	if($_POST['action'] == 'json')
		echo json_encode($data);
	else{
		echo "<h2>Data Check</h2>";
		foreach($data as $d){
			echo "sdhp:{$d['staff_id']},{$d['date']},{$d['shift_id']},{$d['pos_id']} class:{$d['class']} message:{$d['message']}<br/>";
		}
		echo "<br/>Testing complete.<br/>";
	}

	/* 
	OUTPUT DATA FORMAT:
	array[i][staff_id/date/shift_id/pos_id/class/message]
	*/
	
?>
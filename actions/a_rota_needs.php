<?php

require_once('../includes/rota_classes.php');

if(isset($_POST['cal'])){
	// DELETE ALL
	$sql = "DELETE FROM `rota_needs_sched` WHERE `end_date` >= ?";
	$query = new rQuery($con, $sql, 's',datestr_cur());
	$query->run();
	
	// INSERT
	if(count($_POST['cal']) > 0)
	foreach($_POST['cal'] as $c){
		$n = new rotaNeedSched($c['name'],$c['start_date'],$c['end_date']);
		$n->insert($con);
	}
}

if(isset($_POST['temp'])){
	// DELETE ALL
	db_delete($con,'templates_rota_needs');
	
	// INSERT
	if(count($_POST['temp']) > 0)
	foreach($_POST['temp'] as $t){
		if(isset($t['inst']))
		foreach($t['inst'] as $i){
			$n = new rotaNeed($t['name'], null, $i['day'], $i['shift_id'], $i['pos_id'], $i['value']);
			$n->insert($con);
		}
	}
}

if(isset($_POST['ex'])){
	// DELETE ALL
	$sql = "DELETE FROM `rota_needs_ex` WHERE `date` >= ?";
	$query = new rQuery($con, $sql, 's',datestr_cur());
	$query->run();
	
	// INSERT
	if(count($_POST['ex']) > 0)
	foreach($_POST['ex'] as $e){
		if(isset($e['inst']))
		foreach($e['inst'] as $i){
			$n = new rotaNeed(null, $e['date'], null, $i['shift_id'], $i['pos_id'], $i['value']);
			$n->insert($con);
		}
	}
}

	echo "Saved: " . date("D jS, G:i") . "<br/><br/>";
?>
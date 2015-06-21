<?php
	require_once('../includes/staff_classes.php');
		
	$group = new dGroup($con,$_POST['group']);
	$shifts = implode(',',$group->shift);
	$staff = implode(',',$_POST['staff']);
	$start_date = $_POST['start_date'];
	$end_date = $_POST['end_date'];
	
	$sql = "DELETE FROM `rota` WHERE `sheet`='".ROTA_SHEET_IP."' AND `date`<='{$end_date}' AND `date`>='{$start_date}' AND `shift_id` IN({$shifts}) AND `staff_id` IN({$staff})";
	$query = new rQuery($con,$sql);
	$query->run();
	
	if(isset($_POST['record']) && is_array($_POST['record']) && count($_POST['record'])>0){
		$table = 'rota';
		$field = array('sheet','date','staff_id','shift_id','pos_id');
		foreach($_POST['record'] as $r){
			$value = array(ROTA_SHEET_IP,$r['date'],$r['staff_id'],$r['shift_id'],$r['pos_id']);
			db_insert($con,$table,$field,$value,$select_id = false, $debug = false);
		}
		echo "Saved " . count($_POST['record']) . " records in the rota: "  . date("D jS, G:i:s") . "<Br/>";
	}
?>
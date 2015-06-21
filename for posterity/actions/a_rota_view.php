<?php
	require_once('../includes/staff_classes.php');
	
	// DELETE ALL THE OLD FOR THAT SHIFT
	$sql = "DELETE FROM `rota` WHERE `date`='{$_POST['date']}' AND `shift_id`='{$_POST['shift_id']}' AND `pos_id`='{$_POST['pos_id']}' AND `sheet`='" . ROTA_SHEET_IP . "'";
	$query = new rQuery($con, $sql, false,false, true);
	$query->run();
	// PUT IN THE NEW
	if(isset($_POST['staff_id']) && is_array($_POST['staff_id']))
		$count = count($_POST['staff_id']);
	else
		$count = 0;
	if($count){
		foreach($_POST['staff_id'] as $s){
			$sql = "INSERT INTO `rota` (`sheet`,`date`,`staff_id`,`pos_id`,`shift_id`) VALUES ('".ROTA_SHEET_IP."','{$_POST['date']}','{$s}','{$_POST['pos_id']}','{$_POST['shift_id']}')";
			$query = new rQuery($con, $sql, false,false, true);
			$query->run();
		}
	}
	echo $count . " staff members scheduled for " . $_POST['info'];
?>
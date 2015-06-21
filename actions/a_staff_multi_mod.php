<?php

require_once('../includes/general_functions.php');

// PREP
$staff_type = false;
if(isset($_POST['staff_type']) && $_POST['staff_type'] != 0) $staff_type = $_POST['staff_type'];
else $staff_type = false;

if(isset($_POST['staff']) && is_array($_POST['staff']) && count($_POST['staff']) > 0) $staff_id = implode(',',$_POST['staff']);
else $staff_id = false;

if(isset($_POST['action']) && is_array($_POST['action']) && count($_POST['action'])) $action = $_POST['action'];
else $action = false;

if(!$staff_type && !$staff_id){
	echo "Please select at least one staff type for which to perform these actions.";
	exit();
}
if(!$action){
	echo "Please define at least one action to perform on the staff members/type.";
	exit();
}

// Get all the staff you're doing the action to:

$sql = "SELECT `staff_id` FROM `staff` WHERE";
if($staff_type) $sql .= " `type` = '{$staff_type}'";
if($staff_type && $staff_id) $sql .= " OR";
if($staff_id) $sql .= "`staff_id` IN({$staff_id})";

$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
$result = $query->run();
$staff = array();
foreach($result as $r)
	array_push($staff, $r['staff_id']);
$staff_id = implode(',',$staff);

// foreach action


foreach($action as $i=>$a){
	$sql = '';
	$runit = true;
	if($a['type'] == 'pos'){
		// WARNINGS
		if(!$a['action']){echo "Error with action number:" . $i + 1 . " - no action defined.<br/>"; exit();}
		if(!$a['name']){echo "Error with action number:" . $i + 1 . " - no position defined.<br/>"; exit();}
		
		$pos_id = $a['name'];
		
		if($a['action'] == 'del')
			$sql = "DELETE FROM `staff_pos` WHERE `staff_id` IN({$staff_id}) AND `pos_id`='{$pos_id}'";
		elseif($a['action'] == 'add'){
			$sql = "DELETE FROM `staff_pos` WHERE `staff_id` IN({$staff_id}) AND `pos_id`='{$pos_id}'";
			
			$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
			$result = $query->run();
			
			$sql = '';
			
			foreach($staff as $s){
				$sql = "INSERT INTO `staff_pos` (`staff_id`,`pos_id`,`min`,`max`,`pref`) VALUES ({$s},{$pos_id},{$a['details']['min']},{$a['details']['max']},{$a['details']['pref']})";
				$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
				$result = $query->run();
			}
			$runit = false;
		}
		elseif($a['action'] == 'mod'){
			$sql = "SELECT `staff_id` FROM `staff_pos` WHERE `staff_id` IN({$staff_id}) AND `pos_id`='{$pos_id}'";
			$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
			$result = $query->run();
			$temp_staff = array();
			if(is_array($result) && count($result > 0)){
				foreach($result as $r)
					array_push($temp_staff, $r['staff_id']);
				$temp_staff_id = implode(',',$temp_staff);
				$sql = '';
				$sql = "UPDATE `staff_pos` SET `min`='{$a['details']['min']}',`max`='{$a['details']['max']}',`pref`='{$a['details']['pref']}' WHERE `pos_id`='{$pos_id}' AND `staff_id` IN({$temp_staff_id})";
			}
		}
	}
	if($a['type'] == 'lang'){
		// WARNINGS
		if(!$a['action']){echo "Error with action number:" . $i + 1 . " - no action defined.<br/>"; exit();}
		if(!$a['name']){echo "Error with action number:" . $i + 1 . " - no language defined.<br/>"; exit();}
		
		$lang_id = $a['name'];
		
		if($a['action'] == 'del')
			$sql = "DELETE FROM `staff_lang` WHERE `staff_id` IN({$staff_id}) AND `lang_id`='{$lang_id}'";
		elseif($a['action'] == 'add'){
			$sql = "DELETE FROM `staff_lang` WHERE `staff_id` IN({$staff_id}) AND `lang_id`='{$lang_id}';
			";
			
			$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
			$result = $query->run();
			
			$sql = '';
			
			foreach($staff as $s){
				$sql = "INSERT INTO `staff_lang` (`staff_id`,`lang_id`) VALUES ({$s},{$lang_id})";
				$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
				$result = $query->run();
			}
			$runit = false;
		}
	}
	if($a['type'] == 'contract'){
		$sql = "UPDATE `staff_contract` SET `min`='{$a['details']['min']}',`max`='{$a['details']['max']}',`pref`='{$a['details']['pref']}' WHERE `staff_id` IN({$staff_id})";
	}
	if($runit){
		$query = new rQuery($con, $sql, $param_type = false, $param = false,$force_array = true);
		$result = $query->run();
	}
	
	echo "Hope it worked! Messages about success are not available yet.";
}
?>
<?php
require_once('../includes/server_info.php');
require_once('../includes/staff_classes.php');

if(isset($_POST['action']) && $_POST['action'] == 'fetch'){
	$staff = new staffM($con);
	$sql = "SELECT `staff_id` FROM `staff` WHERE `first_name` LIKE '%{$_POST['search']}%' OR `last_name` LIKE '%{$_POST['search']}%' OR CONCAT(`first_name`,' ',`last_name`) LIKE '%{$_POST['search']}%' ORDER BY `first_name` {$_POST['sort']},`last_name` {$_POST['sort']}";
	$query = new rQuery($con,$sql,false,false,true);
	$list = $query->run();
	if(isset($list) && is_array($list) && count($list)>0)
	foreach($list as $l)
		echo "<div class='staff_selector_member' data-staff_id='{$l['staff_id']}'>{$staff->staff[$l['staff_id']]->name}</div>
		";
}
// NEW FOR AIRPORT RUNS
elseif(isset($_POST['action']) && $_POST['action'] == 'simple_predict'){
	$sql = "SELECT `staff_id`,`first_name`,`last_name` FROM `staff` WHERE `first_name` LIKE '%{$_POST['search']}%' OR `last_name` LIKE '%{$_POST['search']}%' OR CONCAT(`first_name`,' ',`last_name`) LIKE '%{$_POST['search']}%' OR CONCAT(`last_name`,' ',`first_name`) LIKE '%{$_POST['search']}%'";
	$query = new rQuery($con,$sql,false,false,true);
	$list = $query->run(false,3);
	if(isset($list) && is_array($list) && count($list)>0){
		foreach($list as $l){
			echo "<div class='staff_selector_member' data-staff_id='{$l['staff_id']}'>{$l['first_name']}";
			if($l['last_name']) echo " {$l['last_name']}";
			echo "</div>";
		}
	}
	else{
		echo "No matches.";
	}
}
//
else
echo "
<div class='staff_selector' data-selected=''>
	<div class='search'>
		Staff:<input type='text' class='staff_name' name='staff_name'/><br/>
		<input type='radio' name='sort' value='ASC'/>Asc <input type='radio' name='sort' value='DESC'/>Desc
	</div>
	<div class='findings'></div>
</div>";

?>
<?php
require_once('../includes/server_info.php');
require_once('../includes/staff_classes.php');
$staff = new staffM($con);

if(isset($_POST['action']) && $_POST['action'] == 'fetch'){
	$sql = "SELECT `staff_id` FROM `staff` WHERE `first_name` LIKE '%{$_POST['search']}%' OR `last_name` LIKE '%{$_POST['search']}%' OR CONCAT(`first_name`,' ',`last_name`) LIKE '%{$_POST['search']}%' ORDER BY `first_name` {$_POST['sort']},`last_name` {$_POST['sort']}";
	$query = new rQuery($con,$sql,false,false,true);
	$list = $query->run();
	foreach($list as $l)
		echo "<div class='staff_selector_member' data-staff_id='{$l['staff_id']}'>{$staff->staff[$l['staff_id']]->name}</div>
		";
}
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
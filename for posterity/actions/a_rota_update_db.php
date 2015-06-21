<?php
require_once('../includes/rota_classes.php');

/*NOTE: AS of 6-26, sheet is still hard-coded, not variable yet. Hard coded in the js portion.*/
$group = new dGroup($con, $_POST['group_id']);
$pos = new dPosLib($con, true, $group);
$pos_id_array = array();
foreach($pos->pos as $p)
	array_push($pos_id_array, $p->pos_id);
array_push($pos_id_array,'0');
$pos_id_str = "'" . implode("', '", $pos_id_array) . "'";

if(isset($_POST['data']))
foreach($_POST['data'] as $d){
	// DELETE ALL FIRST
	/*
	$field = array('sheet','date','shift_id','staff_id');
	$value = array($_POST['sheet'],$d['date'],$d['shift_id'],$d['staff_id']);
	db_delete($con,'rota', $field,$value);
	*/
	$sql = "DELETE FROM `rota` WHERE `sheet`='{$_POST['sheet']}' AND `date`='{$d['date']}' AND `shift_id` = '{$d['shift_id']}' AND `staff_id` = '{$d['staff_id']}' AND `pos_id` IN({$pos_id_str})";
	$query = new rQuery($con, $sql, false,false,true);
	$result = $query->run();
	
	if($d['pos_id']){ // INSERT IT
		$field = array('sheet','date','shift_id','staff_id','pos_id');
		$value = array($_POST['sheet'],$d['date'],$d['shift_id'],$d['staff_id'],$d['pos_id']);
		db_insert($con,'rota',$field,$value,false);
	}
}
if(isset($_POST['data']) && $c = count($_POST['data']))
	echo "Updated {$c} records : " . date("D jS, G:i");
else
	echo "No changes made - nothing updated : " . date("D jS, G:i");
?>
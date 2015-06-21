<?php
require_once('../includes/staff_classes.php');
//Array ( [data] => Array ( [0] => Array ( [action] => yes [lang_id] => new [order] => 3 [name] => [abbr] => [alt1] => [alt2] => ) ) [action] => lang )

$data = $_POST['data'];

if($_POST['action'] == 'lang'){
	foreach($data as $d){
		$lang = new dLang();
		$lang->lang_id = $d['lang_id'];
		$lang->order = $d['order'];
		$lang->name = $d['name'];
		$lang->abbr = $d['abbr'];
		$lang->alt1 = $d['alt1'];
		$lang->alt2 = $d['alt2'];
		if($d['action'] == 'yes'){ // update/insert
			if($lang->lang_id == 'new') // insert
				$lang->insert($con);
			else // update
				$lang->update($con);
		}
		elseif($d['action'] == 'del') // delete
			$lang->delete($con);
	}
	
	echo "Saved: " . date("D jS, G:i") . "<br/><br/>";
	$lang = new dLangLib($con);
	$lang->order_fix($con);
	$lang->form();
	echo "<button onclick=lang_save();>Save Languages</button>";
}
elseif($_POST['action'] == 'pos'){
	foreach($data as $d){
		$pos = new dpos();
		$pos->pos_id = $d['pos_id'];
		$pos->order = $d['order'];
		$pos->name = $d['name'];
		$pos->abbr = $d['abbr'];
		$pos->alt1 = $d['alt1'];
		$pos->alt2 = $d['alt2'];
		$pos->pref_type = $d['pref_type'];
		$pos->pref_strength = $d['pref_strength'];
		$pos->pos_type = $d['pos_type'];
		if($d['action'] == 'yes'){ // update/insert
			if($pos->pos_id == 'new') // insert
				$pos->insert($con);
			else // update
				$pos->update($con);
		}
		elseif($d['action'] == 'del') // delete
			$pos->delete($con);
	}
	
	echo "Saved: " . date("D jS, G:i") . "<br/><br/>";
	$pos = new dposLib($con);
	$pos->order_fix($con);
	$pos->form();
	echo "<button onclick=pos_save();>Save Positions</button>";
}
elseif($_POST['action'] == 'shift'){
	$shift_array = array();
	if(isset($_POST['par']) && count($_POST['par']) > 0)
	foreach($_POST['par'] as $p){
		$shift = new dShift();
		$shift->shift_id = $p['shift_id'];
		$shift->order = $p['order'];
		$shift->name = $p['name'];
		$shift->abbr = $p['abbr'];
		$shift->alt1 = $p['alt1'];
		$shift->alt2 = $p['alt2'];
		
		if($p['action'] == 'yes'){ // update/insert
			if($shift->shift_id == 'new') // insert
				$shift->insert($con);
			else // update
				$shift->update($con);
		}
		elseif($p['action'] == 'del') // delete
			$shift->delete($con);
	}
	if(isset($_POST['inst']) && count($_POST['inst']) > 0)
	foreach($_POST['inst'] as $i){
		$shift = new dShift();
		$shift->shift_id = $i['shift_id'];
		$shift->instance_id = $i['instance_id'];
		$shift->name = $i['name'];
		$shift->abbr = $i['abbr'];
		$shift->start_date = $i['start_date'];
		$shift->end_date = $i['end_date'];
		$shift->start_time = $i['start_time'];
		$shift->end_time = $i['end_time'];
		
		if($i['action'] == 'yes'){ // update/insert
			if($shift->instance_id == 'new') // insert
				$shift->insert($con);
			else // update
				$shift->update($con);
		}
		elseif($i['action'] == 'del') // delete
			$shift->delete($con);
	}
	
	echo "Saved: " . date("D jS, G:i") . "<br/><br/>";
	$shift = new dshiftLib($con);
	$shift->order_fix($con);
	$shift->form();
	echo "<button onclick=shift_save();>Save Shifts</button>";
}
elseif($_POST['action'] == 'group'){
	foreach($data as $d){
		$group = new dgroup();
		$group->group_id = $d['group_id'];
		$group->order = $d['order'];
		$group->name = $d['name'];
		$group->abbr = $d['abbr'];
		$group->alt1 = $d['alt1'];
		$group->alt2 = $d['alt2'];
		$group->data = $d['data'];
		if($d['action'] == 'yes'){ // update/insert
			if($group->group_id == 'new') // insert
				$group->insert($con);
			else // update
				$group->update($con);
		}
		elseif($d['action'] == 'del') // delete
			$group->delete($con);
	}
	
	echo "Saved: " . date("D jS, G:i") . "<br/><br/>";
	$group = new dgroupLib($con);
	$group->order_fix($con);
	$group->form();
	echo "<button onclick=group_save();>Save Groups</button>";
}
?>
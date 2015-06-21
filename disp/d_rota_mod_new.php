<?php
	require_once('../includes/calendar.php');
	require_once('../includes/staff_classes.php');
	
	$config = new conFig();
	
	switch($_POST['action']){
		case "calendar":
			calendarStage($_POST);
			break;
		case "fetchInfo":
			fetchInitialInfo($_POST);
			break;
		default:
			echo "Posted action type is invalid.<br/>";
			break;
	
	}


function fetchInitialInfo($dataIn){
	global $con;
	global $config;
	
	$dataOut = array();
	$dataOut['message']="Info Fetched.<br/>";
	
	$start_date = start_of_week($dataIn['date']);
	$end_date = datestr_shift($start_date,7);
	$sheet = ROTA_SHEET_IP;
	
	// STAFF, SHIFT, and POS LISTS
	$group = new dGroup($con,$dataIn['group']);
	$staff = implode(",",$dataIn['staff']);
	$shift = implode(",",$group->shift);
	$pos = implode(",",$group->pos);
	
	// RAW POSITION INFO
	$dataOut['raw_pos'] = array();
	$posLib = new dPosLib($con);
	foreach($posLib->pos as $p){
		$g = false;
		foreach($group->pos as $gp)
			if($gp == $p->pos_id)
				$g = true;
		array_push($dataOut['raw_pos'],array(
			'pos_id'=>$p->pos_id,
			'name'=>$p->name,
			'abbr'=>$p->abbr,
			'group'=>$g
		));
	}
	
	// STAFF TYPE
	$sql = "SELECT `staff_id`,`type` FROM `staff` WHERE `staff_id` IN({$staff})";
	$query = new rQuery($con, $sql, false, false, true);
	$dataOut['staff_type'] = $query->run(false,2);
	
	// TOTAL CONTRACTS
	$sql = "SELECT * FROM `staff_contract` WHERE `staff_id` IN({$staff})";
	$query = new rQuery($con,$sql,false,false,true);
	$dataOut['contracts'] = $query->run(false,3);
	
	//POSITIONS & POS_CONTRACTS
	$sql = "SELECT `staff_id`,`pos_id`,`pref`,`min`,`max` FROM `staff_pos` WHERE `staff_id` IN({$staff}) ORDER BY `staff_id` ASC";
	$query = new rQuery($con,$sql,false,false,true);
	$dataOut['positions'] = $query->run(false,4);
	
	// AVAILABILITY
	$dataOut['availability'] = array();
	foreach($dataIn['staff'] as $staff_id){/*
			So array in the form of $availability['staff_id']['date']['shift_id']['pref'|'fixed'|'pos_id'].
			UNTESTED - I think that this function is giving us the proper AV information, but it should be double checked.
		*/
		//$s = new staffS($con, $staff_id,false); // false here to not auto-load: no need to call up info twice just for the make_av_raw function
		$s = new staffS($con, $staff_id);
//		$s->make_av_raw($con,$start_date,$end_date);
//		$dataOut['av_old'][$staff_id] = $s->av_raw;
		
		//echo "<h4>".$s->name." : ".$staff_id."</h4>";
		//print_r($dataOut['av_old'][$staff_id]);
		$dataOut['availability'][$staff_id] = $s->set_avn($con,$start_date,$end_date,true,true);
	}
	// SHIFTS
	// SCHEDULED
	$sql = "SELECT `staff_id`,`date`,`pos_id`,`shift_id` FROM `rota` WHERE `sheet`='{$sheet}' AND `date` <='{$end_date}' AND `date`>='{$start_date}' AND `staff_id` IN({$staff}) AND `shift_id` IN({$shift}) ORDER BY `date`,`shift_id` ASC";
	$query = new rQuery($con,$sql,false,false,true);
	$dataOut['scheduled'] = $query->run(false,3);
	
	// NEEDS
	$dataOut['needs'] = array();
	// ARRAY OF DATES
	$dates = datestr_array_with_config($config,$start_date, $end_date);
	// GET NEEDS OF EACH DAY
	foreach($dates as $d){
		$day = datestr_format($d,'w') + 1;
		// GET TEMPLATE NAME
		$sql = "SELECT `name` FROM `rota_needs_sched` WHERE `start_date` <='{$d}' AND `end_date` >= '{$d}'";
		$query = new rQuery($con,$sql);
		$results = $query->run();
		if(isset($results['name']))
			$template_name = $results['name'];
		elseif(isset($results[0]['name']))
			$template_name = $results[0]['name'];
		
		if(!$template_name){
			echo "Error: No rota needs template assigned for date:{$d}!<br/>";
			exit();
		}
		// GET TEMPLATE DATA
		$sql = "SELECT `shift_id`,`pos_id`,`number` FROM `templates_rota_needs` WHERE `name`='{$template_name}' AND `day`='{$day}'";
		$query = new rQuery($con, $sql, false, false, true);
		$results = $query->run(false,2);
		
		// PUT IT IN THE NEEDS ARRAY
		$dataOut['needs'][$d] = array();
		foreach($results as $r)
			array_push($dataOut['needs'][$d],$r);
		// IGNORING EXCEPTIONS FOR NOW!
	}
	
	echo json_encode($dataOut);
	/* DATA FORMATS ON RETURN
	contracts: [i][staff_id|min|max|pref]
	positions: [i][staff_id|pos_id|pref|min|max]
	availability: [staff_id][date][shift_id][pref|fixed|pos_id]
	scheduled: [i][staff_id|date|pos_id|shift_id]
	needs: [date][shift_id|pos_id|number]
	*/
}

function calendarStage($data){
	global $con;
	
	$cal = new Calendar($con);
		
	if(isset($data['date']))
		$cal->datestr = $data['date'];
	else
		$cal->datestr = standard_date_val();
	
	if(isset($data['group']))
		$group = new dGroup($con,$data['group']);
	else
		$group = null;
		
	if($group){
		$cal->group = $group;
		$cal->set_staff_name_type = NAME_FIRST_L;
	}
		
	$cal->title = "Rotas: {$group->name} - Week of " . datestr_format($data['date']);
	$cal->row1_type = CAL_STAFF;
	$cal->col1_type = CAL_DATE;
	$cal->col2_type = CAL_SHIFT;
	$cal->col2_format = 'abbr';
	//$cal->default_content = "<div class='hidden rota_data'></div>";
	$cal->table_class = 'new_rota_table';
	
	$cal->date_select_buttons = false;
	$cal->date_select_input = false;
	$cal->group_select = false;
	$cal->init();
}

?>
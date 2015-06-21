<?php
	//session_start();
	require_once('../includes/calendar.php');
	require_once('../includes/rota_classes.php');
	
	$data = $_POST['data'];
	if(isset($data['datestr']))
		session_update('date',$data['datestr']);

	
	if(isset($_POST['staff_id']))
		$staff = new staffS($con, $_POST['staff_id'], $auto_load = true, $date = $data['datestr']);
	
	$posLib = new dPosLib($con);
	if($data['title'] == 'Shift Templates' || $data['title'] == 'Availability Exceptions'){
		$calendar = new Calendar($con, $datestr = $data['datestr'], $type = $data['type'], $id = $data['id'], $class = $data['clas'], $title = $data['title'], $row1_type = $data['row1_type'], $row2_type = $data['row2_type'], $col1_type = $data['col1_type'], $col2_type = $data['col2_type'], $title_suffix = false, $datestr_format = "D jS", $content = null, $default_content = null);
		$calendar->row1_format = $data['row1_format'];
		$calendar->col1_format = $data['col1_format'];
		$calendar->row2_format = $data['row2_format'];
		$calendar->col2_format = $data['col2_format'];
		
		if($calendar->title == 'Shift Templates'){
			$calendar->content = $staff->form_av_temp_content($posLib);
			$calendar->default_content = $staff->form_av_temp_default($posLib);
		}
		elseif($calendar->title == 'Availability Exceptions'){
			$calendar->content = $staff->form_av_inst_content($posLib);
			$calendar->default_content = $staff->form_av_inst_default($posLib);
		}
		
		$calendar->init();
	}
	elseif($data['title'] == 'Rotas' || $data['title'] == 'Rotas - Staff View'){
		$rota = new Rota($con);
		$rota->group = new dGroup($con,$data['group_id']);
		$rota->datestr = $data['datestr'];
		if($data['title'] == 'Rotas') $rota->type = ROTA_STD;
		if($data['title'] == 'Rotas - Staff View') $rota->type = ROTA_STAFF;
		$rota->init();
		$rota->show();
	}
?>
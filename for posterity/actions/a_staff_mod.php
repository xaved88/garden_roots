<?php
	require_once('../includes/server_info.php');
	require_once('../includes/staff_classes.php');
	/*
	Data passing method: all arrays, . as the next level:
	staff_id
	details.xxx => all details
	lang.i => all languages, just the id in an array
	pos.i.xxx=> all positions, an array(i simply incremented) with the named values in the next array.
	here.i.xxx=> all here, here[i][date|type]
	*/
	
	
	if(isset($_POST['action']) && $_POST['action']=='remove_from_schedule'){
		$sql = "DELETE FROM `rota` WHERE `staff_id`='{$_POST['staff_id']}' AND `date`<='{$_POST['date_end']}' AND `date`>='{$_POST['date_start']}'";
		$query = new rQuery($con,$sql);
		if($query->run())	
			echo "Updated:" . date("D jS, G:i") . "<br/>
				Removed from all shifts in rota from {$_POST['date_start']} til {$_POST['date_end']}.<br/>";
		else
			echo "Error: Shift Scheduled in rota not removed - " . date("D jS, G:i") . "<br/><br/>";
	}
	else{	
		$config = new conFig();
		
		$staff = new staffS();
		$staff->staff_id = $_POST['staff_id'];
		$staff->first_name = $_POST['details']['first_name'];
		$staff->last_name = $_POST['details']['last_name'];
		$staff->type = $_POST['details']['type'];
		$staff->partner = $_POST['details']['partner'];
		$staff->gender = $_POST['details']['gender'];
		$staff->email = $_POST['details']['email'];
		$staff->email2 = $_POST['details']['email2'];
		$staff->phone = $_POST['details']['phone'];
		$staff->phone2 = $_POST['details']['phone2'];
		$staff->mailing_address = $_POST['details']['mailing_address'];
		$staff->birthday = $_POST['details']['birthday'];
		
		if(isset($_POST['lang']))
		foreach($_POST['lang'] as $l){
			$staff->lang[$l] = $l;
		}
		
		if(isset($_POST['pos']))
		foreach($_POST['pos'] as $p){
			$staff->pos[$p['pos_id']] = array(
				'staff_id' => $_POST['staff_id'],
				'pos_id' => $p['pos_id'],
				'skill' => $p['skill'],
				'pref' => $p['pref'],
				'min' => $p['min'],
				'max' => $p['max'],
				'training_hours' => $p['training_hours'],
				'training_start_date' => $p['training_start_date'],
				'combo' => $p['combo']
			);
		}
		
		if(isset($_POST['contract'])){
			$staff->contract['min'] = $_POST['contract']['min'];
			$staff->contract['max'] = $_POST['contract']['max'];
			$staff->contract['pref'] = $_POST['contract']['pref'];
		}
		
		if(isset($_POST['here']))
		foreach($_POST['here'] as $h){
			array_push($staff->here, array(
				'start_date' => datestr_shift($h['start_date'],$config->xml->arr_buffer),
				'end_date' => datestr_shift($h['end_date'],-$config->xml->dep_buffer),
				'type' => AWAY_ARRDEP
			));
		}
		
		if(isset($_POST['away']))
		foreach($_POST['away'] as $a){
			array_push($staff->away, array(
				'start_date' => $a['start_date'],
				'end_date' => $a['end_date'],
				'type' => $a['type']
			));
		}
		
		if(isset($_POST['av_temp']))
		foreach($_POST['av_temp'] as $av){
			$staff->add_av_temp($av);
		}
		
		if(isset($_POST['av_inst']))
		foreach($_POST['av_inst'] as $av){
			$staff->add_av_inst($av);
		}
		
		$staff->update($con);
		$staff->update_lang($con);
		$staff->update_pos($con);
		$staff->update_contract($con);
		$staff->update_here($con);
		$staff->update_away($con);
		$staff->update_av($con, $_POST['start_date'],$_POST['end_date']);
		
		echo "Saved: " . date("D jS, G:i") . "<br/><br/>";
	}
	
?>
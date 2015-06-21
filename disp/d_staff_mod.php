<?php
	require_once('../includes/server_info.php');
	require_once('../includes/staff_classes.php');
	if(!isset($_POST['staff_id']) || !isset($_POST['action'])){
		echo "Error: Staff_id or action not posted by the ajax call. Terminating page.<br/>";
		exit();
	}
	
	$config = new conFig();
	$staff_id = $_POST['staff_id'];
	$action = $_POST['action'];
	
	$staff = new staffS($con,$staff_id);
	switch ($action){
		case 'lib';
			$staff->data_av_new($con);
			break;
		case 'details':
			$staff->form_details($con);
			break;
		case 'pos':
			$staff->form_pos($con);
			break;
		case 'lang':
			$staff->form_lang($con);
			break;
		
		case 'av': // OUTDATED, TO BE REMOVED SOON
			$staff->form_av($con);
			break;
		case 'hereaway': // OUTDATED TO BE REMOVED SOON
			$staff->form_hereaway($con);
			break;
			
		case 'sched':
			echo "<div id='sched_accordion'>
			<h3>Availability:</h3>
			<div id='av_acc'>";
			$staff->form_av_new($con);
			echo "</div>
			<h3>Arrival/Departure:</h3>
			<div>";
			$staff->form_here($con,$config->xml->arr_buffer,$config->xml->dep_buffer);
			echo "</div>
			<h3>Vacation:</h3>
			<div>";
			$staff->form_away($con);
			echo "</div>";
			echo "<h3>Schedule</h3>
				<div>";
			$staff->form_sched($con);
			echo "</div></div>";
			break;
		
		case 'avn':
			$date = $_POST['date'];
			if($_POST['push'])
			$date = datestr_shift($date,$_POST['push']);
			$staff->form_av_new($con,$date);
			break;
			
		case 'name-only':
			echo $staff->name;
			break;
		case 'details_view':
			$staff->view_details($con);
			break;
		case 'pos_view':
			$staff->view_pos($con);
			break;
		case 'lang_view':
			$staff->view_lang($con);
			break;
		case 'av_view':
			$staff->view_av($con);
			break;
		case 'hereaway_view':
			$staff->view_hereaway($con);
			break;
		default:
			break;
	}
?>
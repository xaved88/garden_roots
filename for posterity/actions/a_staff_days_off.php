<?php
require_once('../includes/staff_classes.php');

switch($_POST['action']){
	case 'update_days_off':
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$data = $_POST['data'];
		$away_type = AWAY_DAY_OFF;

		$sql = "SELECT `staff_id` FROM `staff` WHERE `type`='{$_POST['type']}'";
		$query = new rQuery($con,$sql,false,false,true);
		$result = $query->run();
		$staff = array();
		if(isset($result) && is_array($result) && count($result)>0)
		foreach($result as $r)
			array_push($staff, $r['staff_id']);
		$staff = implode(',',$staff);

		$sql = "DELETE FROM `staff_away` WHERE `start_date` >= '{$start_date}' AND `end_date` <= '{$end_date}' AND `type`='{$away_type}' AND `staff_id` IN({$staff})";
		$query = new rQuery($con,$sql,false,false,true);
		$result = $query->run();


		$field = array('staff_id','start_date','end_date','type');
		foreach($data as $d){
			if(count($d['date'])>0)
			foreach($d['date'] as $date){
				if($date != NULL_DATE){
					$value = array((string)$d['staff_id'], (string)$date, (string)$date, (string)AWAY_DAY_OFF);
					db_insert($con,'staff_away',$field,$value);
				}
			}
		}
		echo "Saved: " . date("D jS, G:i:s") . "<br/><br/>";
		break;
	case 'update_print_options':
		$config = new conFig();
		$o = $config->xml->days_off_options;
		$o->inline_css = $_POST['inline_css'];
		$o->devotions_text = $_POST['devotions_text'];
		$o->devotions_css = $_POST['devotions_css'];
		$o->header_text = $_POST['header_text'];
		$o->header_css = $_POST['header_css'];
		$o->footer_text = $_POST['footer_text'];
		$o->footer_css = $_POST['footer_css'];
		if($config->update())
			echo "Options updated successfully: "  . date("D jS, G:i:s") . "<Br/>";
		else
			echo "Error updating options: "  . date("D jS, G:i:s") . "<br/>";
		break;
		
	default:
		echo "Error - no function defined for a_staff_days_off.php:" . date("D jS, G:i:s") . " <br/>";
		break;
}
?>
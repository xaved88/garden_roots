<?php
require_once('../includes/staff_classes.php');

error_reporting(E_ALL);

$name = TEMP_DEFAULT; // THIS  WILL NEED TO CHANGE ONCE YOU HAVE NAMES CHANGING AROUND.

if($_POST['action'] == 'staff_av'){
	// DELETE ALL THE OLD ONES
	$where_field = array('staff_id','name');
	$where_value = array(0,$name);
	db_delete($con,'templates_av', $where_field, $where_value, $debug = false);
	foreach($_POST['data'] as $d){
		if(isset($d['temp'])){
			$type = $d['type'];
			foreach($d['temp'] as $t){
				$field =array('staff_id','type','name','day','shift_id','pos_id','pref','fixed');
				$value = array(0,$type,$name, $t['day'], $t['shift_id'], $t['pos_id'], $t['pref'],false);
				db_insert($con,'templates_av',$field,$value,$select_id = false, $debug = false);
			}
		}
	}
}
elseif($_POST['action'] == 'other'){
	$config = new conFig();
	update_arrdep_buffers($con,$_POST['data'],$config);
	
	$config->xml->email->header = $_POST['data']['email_header'];
	$config->xml->email->footer = $_POST['data']['email_footer'];
	//$config->xml->email->css = $_POST['data']['email_css'];
	$config->xml->email->subject = $_POST['data']['email_subject'];
	if( isset($_POST['data']['email_cc_sender'] ) )
		$config->xml->email->cc_sender = ' checked';
	else
		$config->xml->email->cc_sender = false;
		
	if( isset($_POST['data']['company_web']) && is_array($_POST['data']['company_web']) && count($_POST['data']['company_web']) > 0 ){
		foreach($_POST['data']['company_web'] as $i=>$cw){
			$name = 'company_web_' . $i;
			$config->xml->$name = $cw;
		}
	}
	else{
		$config->company_web_0 = false;
		$config->company_web_1 = false;
		$config->company_web_2 = false;
	}
	$config->update();
	/*
	$config->xml->arr_buffer = $_POST['data']['arr_buffer'];
	$config->xml->dep_buffer = $_POST['data']['dep_buffer'];
	if(!$config->update())
		echo "Error. Could not update master config file.<br/>";
		*/
}

echo "Saved: " . date("D jS, G:i:s") . "<br/><br/>";


function update_arrdep_buffers($con,$data,$config){
	$arr_dif = (int)$data['arr_buffer'] - (int)$config->xml->arr_buffer;
	$dep_dif = (int)$data['dep_buffer'] - (int)$config->xml->dep_buffer;
	$config->xml->arr_buffer = $data['arr_buffer'];
	$config->xml->dep_buffer = $data['dep_buffer'];
	if($config->update())
		echo "Configuration updated.<br/>";
	else
		echo "Error. Could not update master config file.<br/>";
	
	if($arr_dif){
		$sql = "UPDATE `staff_away` SET `start_date`=DATE_ADD(`start_date`, INTERVAL {$arr_dif} DAY) WHERE `type`='".AWAY_ARRDEP."'";
		$query = new rQuery($con, $sql);
		$query->run();
	}
	if($dep_dif){
		$sql = "UPDATE `staff_away` SET `end_date`=DATE_SUB(`end_date`, INTERVAL {$dep_dif} DAY) WHERE `type`='".AWAY_ARRDEP."'";
		$query = new rQuery($con, $sql);
		$query->run();
	}
}
?>
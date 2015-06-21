<?php
	require_once('../includes/general_functions.php');
	require_once('../includes/staff_classes.php');
	//print_r($_POST);
	//echo "<br/>";
	error_reporting(E_ALL);
	/*
		1) Get complete staff list
		2) Prep Messages
		3) Go through every staff member:
			3.a) Fill header/footer
			3.b) Get their schedule for the time period
			3.c) Compose Message
			3.d) Send the email to their emails.
	*/
	
	{// 1 - GET COMPLETE STAFF LIST
	
	$staff = array();
	$staff_details = array();
	
	// If employees selected, fill them in the staff
	if(isset($_POST['data']['employees']) && $_POST['data']['employees']){
		$sql = "SELECT `staff_id`,`first_name`,`last_name`,`email`,`email2` FROM `staff` WHERE `type`='2'";
		$query = new rQuery($con,$sql,false,false,true);
		$results = $query->run(false,5);
		if($results && count($results)>0){
			foreach($results as $r){
				array_push($staff,$r['staff_id']);
				$staff_details[$r['staff_id']] = $r;
			}
		}
	}
	// If volunteers selected, fill them
	if(isset($_POST['data']['volunteers']) && $_POST['data']['volunteers']){
		$sql = "SELECT `staff_id`,`first_name`,`last_name`,`email`,`email2` FROM `staff` WHERE `type`='1'";
		$query = new rQuery($con,$sql,false,false,true);
		$results = $query->run(false,5);
		if($results && count($results)>0){
			foreach($results as $r){
				array_push($staff,$r['staff_id']);
				$staff_details[$r['staff_id']] = $r;
			}
		}
	}
	// Individuals add:
	if(isset($_POST['data']['individuals']) && is_array($_POST['data']['individuals']) && count($_POST['data']['individuals'])>0)
	foreach($_POST['data']['individuals'] as $s){
		if(!in_array($s,$staff)){
			$sql = "SELECT `staff_id`,`first_name`,`last_name`,`email`,`email2` FROM `staff` WHERE `staff_id`='{$s}'";
			$query = new rQuery($con,$sql,false,false,true);
			$results = $query->run(false,5);
			if($results && count($results)>0){
				array_push($staff,$results[0]['staff_id']);
				$staff_details[$results[0]['staff_id']] = $results[0];
			}
		}
	}
	// Remove Excepts:
	if(isset($_POST['data']['except']) && is_array($_POST['data']['except']) && count($_POST['data']['except'])>0)
	foreach($_POST['data']['except'] as $e){
		if(in_array($e,$staff)){
			foreach($staff as &$s)
				if($s == $e)
					$s = false;
		}
	}
	}
	{// 2 - PREP MESSAGES
	$header = fill_text($_POST['data']['header'], $_POST['data']);
	$footer = fill_text($_POST['data']['footer'], $_POST['data']);
	if( isset($_POST['data']['cc_sender']) && $_POST['data']['cc_sender'] )
		$cc_sender = TRUE;
	else
		$cc_sender = FALSE;
	//echo "<h3>Header</h3>{$header}<h3>Footer</h3>{$footer}";
	}
	{// 3 - EVERY STAFF MEMBER
	
	$pos = new dPosLib($con);
	$shift = new dShiftLib($con);
	$config = new conFig();
	
	
	echo "<table class='return_info'><tr><th>Staff Member</th><th class='status'>Status</th><th>Notes</th></tr>";
	if($staff && is_array($staff) && count($staff)>0)
	foreach($staff as $s)
	if($s){
		$not_sent = false;
		{// 3.a - FILL HEADER/FOOTER
		$body_header = fill_name($header, $staff_details[$s]);
		$body_footer = fill_name($footer, $staff_details[$s]);
		}
		{// 3.b - GET SCHEDULE FOR THE TIME PERIOD
			if($_POST['data']['date_type']=='range'){
				$start_date = $_POST['data']['start_date'];
				$end_date = $_POST['data']['end_date'];
			}
			elseif($_POST['data']['date_type'] == 'month'){
				$start_date = start_of_month($_POST['data']['start_date']);
				$end_date = end_of_month($_POST['data']['start_date']);
			}
			elseif($_POST['data']['date_type'] == 'week'){
				$start_date = start_of_week($_POST['data']['start_date']);
				$end_date = end_of_week($_POST['data']['start_date']);
			}
			if(!$end_date || !$start_date){
				echo "Error: Dates not set. Contact your system administrator.<Br/>";
				return;
			}
			
			$sql = "SELECT `date`,`pos_id`,`rota`.`shift_id` FROM `rota` INNER JOIN `data_shift` ON `rota`.`shift_id` = `data_shift`.`shift_id` WHERE `sheet`='".ROTA_SHEET_IP."' AND `date`<='{$end_date}' AND `date`>='{$start_date}' AND `staff_id`='{$s}' ORDER BY `date` ASC, `data_shift`.`order` ASC";
			$query = new rQuery($con,$sql,false,false,true);
			$sched = $query->run(false,3);
		}	
			if($sched && count($sched>0)){
			{// 3.c - COMPOSE MESSAGE
				$td_style =  " style='border-bottom: 1px solid black; padding: 2px 25px;";
				$td_big_style = $td_style . " font-size:120%'";
				$td_style .= "'";
				
				$body = "<table style='border-bottom:1px solid black; border-collapse:collapse'>
				<tr><th{$td_style}>Date</th><th{$td_style}>Day</th><th{$td_style}>Shift</th><th{$td_style}>Position</th></tr>";
				foreach($sched as $sc){
					$body .= "<tr><td{$td_style}>".datestr_format($sc['date'],'M j')."</td><td{$td_style}>".datestr_format($sc['date'],'l')."</td><td{$td_style}>{$shift->par[$sc['shift_id']]->name}</td><td{$td_big_style}>{$pos->pos[$sc['pos_id']]->name}</td></tr>";
				}
				$body .= "</table>";
				if($config->xml->email->css)
					$style = "<style>" . $config->xml->email->css . "</style>";
				else
					$style = '';
				$body = $style . $body_header . "<br/>" . $body . "<br/>" . $body_footer;
			}
			{// 3.d - SEND MESSAGE
				//echo "<h3>Sending:</h3>{$body}<br/>";
				
				// send only if emails are on file
				if($staff_details[$s]['email'] || $staff_details[$s]['email2']){
					if($staff_details[$s]['email'] && $staff_details[$s]['email2'])
						$to = array($staff_details[$s]['email'],$staff_details[$s]['email2']);
					elseif($staff_details[$s]['email2'])
						$to = $staff_details[$s]['email2'];
					else
						$to = $staff_details[$s]['email'];
					//echo "Email1: {$staff_details[$s]['email']} Email2: {$staff_details[$s]['email2']} To:{$to}<br/>";
					$subject = fill_text($_POST['data']['subject'],$_POST['data'],false);
					$subject = fill_name($subject,$staff_details[$s]);
					
					// Sending
					if(gt_mail($to,$subject,$body,$cc_sender)){
						// Success, show that in message
						$sending_details = "Successfully sent to ";
						if(is_array($to)) 
							$sending_details .= implode(",",$to);
						else
							$sending_details .= $to;
					}
					else{
						$not_sent = true;
						$sending_details = "Error in gt_mail() function.";
						// Failed to send, show in message
					}
				}
				// otherwise, toggle not_sent
				else{
					$not_sent = true;
					$sending_details = "No email address on file.";
				}
			}
			}
			else{
				$not_sent = true;
				$sending_details = "No scheduled shifts";
			}
			//$message = "{$staff_details[$s]['first_name']} {$staff_details[$s]['last_name']} - " . $sending_details;
			//echo $message . "<br/>";
			echo "<tr><td>{$staff_details[$s]['first_name']} {$staff_details[$s]['last_name']}</td>";
			if($not_sent) echo "<td>Not Sent</td>";
			else	echo "<td>Sent</td>";
			echo "<td>{$sending_details}</td>";
			// echo $message = "Not Sent: {$staff_details['s']['first_name']} {$staff_details['s']['last_name']} - No scheduled shifts.";
			
	}
	}
	echo "</table>";
	echo "<br/>Completed: " . date("D jS, G:i:s") . "<br/>";

	
	// SUPPORTING FUNCTIONS
	function html_prep($string){
		$string = str_replace("\r",'',$string);
		$string = str_replace("\n",'<br/>',$string);
		$string = str_replace(" ","&nbsp;",$string);
		$string = str_replace("\t","&#09;",$string);
		return $string;
	}
	
	function fill_text($string,$data,$whitespace = true){
		if($whitespace)
			$string = html_prep($string);
		// [when|month|year|start_date|end_date]
		$month = datestr_format($data['start_date'],'F');
		$year = datestr_format($data['start_date'],'Y');
		$day = datestr_format($data['start_date'],'jS');
		if($data['end_date']){
			$end_month = datestr_format($data['end_date'],'F');
			$end_day = datestr_format($data['end_date'],'jS');
		}
		else{
			$end_month = '';
			$end_day = '';
		}
		
		$start_date = "{$month} {$day}";
		$end_date = "{$end_month} {$end_day}";
		$when = "the ";
		if($data['date_type'] == 'month'){
			$when .= " month of {$month}, {$year}";			
		}
		elseif($data['date_type'] == 'week'){
			$when .= " week of {$month} {$day}, {$year}";
		}
		elseif($data['date_type'] == 'range'){
			$when = "{$month} {$day} thru";
			if($month != $end_month)
				$when .= " {$end_month}";
			$when .= " {$end_day}";
		}
		
		$string = str_replace('[when]',$when,$string);
		$string = str_replace('[month]',$month,$string);
		$string = str_replace('[year]',$year,$string);
		$string = str_replace('[day]',$day,$string);
		$string = str_replace('[start_date]',$start_date,$string);
		$string = str_replace('[end_date]',$end_date,$string);
		
		return $string;
	}

	function fill_name($string,$details){ // $details = array[staff_id|first_name|last_name]
		$string = str_replace('[name]',$details['first_name'] . " " . $details['last_name'], $string);
		$string = str_replace('[first_name]',$details['first_name'], $string);
		$string = str_replace('[last_name]',$details['last_name'], $string);
		return $string;
	}

?>
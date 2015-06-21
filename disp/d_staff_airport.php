<?php

require_once('../includes/general_functions.php');
require_once('../includes/constants.php');

class Airport{
	
	function __construct(){
	
	}
	
	public function form($flight_id = false){
		global $con;
		$sql = "SELECT `shift_id`,`abbr` FROM `data_shift` ORDER BY `order` ASC";
		$query = new rQuery($con,$sql,false,false,true);
		$results = $query->run(false,2);
		$shifts = array();
		if($results && is_array($results) && count($results)>0)
		foreach($results as $r){
			array_push($shifts,$r);
		} 
		if(!$flight_id){
			echo"
			<button class='add_run'>Save</button>
			<p>Flight no:<input class='flight_no' type='text'/> Airline:<input class='flight_airline' type='text'/></p>
			<p>Arrival:<input class='flight_type' type='radio' name='flight_type' value='arr'/> Departure:<input class='flight_type' type='radio' name='flight_type' value='dep'/></p>
			<p>Date:<input class='flight_date' type='date' value='<?php echo standard_date_val();?>'/> Time:<input class='flight_time' type='time'/></p>
			<p>Shifts:<div class='flight_shifts'></div><div class='flight_shift_box invis'>";
			if(count($shifts)>0)
			foreach($shifts as $s){
				echo "<input class='shift' type='checkbox' value='{$s['shift_id']}' data-abbr='{$s['abbr']}'/>{$s['abbr']}<br/>";
			}
			echo "</div></p>
			<div class='transport' data-type='driver'>
				<div class='left'>Driver:<div class='driver_list'></div></div>
				<div><input class='flight_driver search_staff' type='text' data-selector='div.driver_search_box'/></div>
				<div class='driver_search_box search_box left invis'></div>
			</div>
			<div class='transport' data-type='passenger'>
				<div class='left'>Passensgers:<div class='passenger_list'></div></div>
				<div class='left'><input class='flight_passenger search_staff' type='text' data-selector='div.passenger_search_box'/></div>
				<div class='passenger_search_box search_box left invis'></div>
			</div>
			";
		}
		else
			echo "<p>Flight Id:${flight_id}</p>";
	}
	
	public function report($start_date = null, $end_date = "9999-99-99"){
		global $con;
		if(!$start_date)
			$start_date = standard_date_val();
		if(!$end_date)
			$end_date = "9999-99-99";
		$sql = "SELECT `flight_id`,`flight_no`,`airline`,`date`,`time`,`shifts` FROM `air_flight` WHERE `date`>='{$start_date}' AND `date`<='{$end_date}' ORDER BY `date` ASC";
		$query = new rQuery($con,$sql,false,false,true);
		$result = $query->run(false,6);
		if(is_array($result) && count($result)>0)
			$flights = $result;
		else
			$flights = false;
		
		echo "
		<div class='flight_search'>
			Start Date: <input class='start_date' type='date' value='{$start_date}'><br/>
			End Date: <input class='end_date' type='date' value='{$end_date}'><br/>
			<button data-range='input'>Search</button><button data-range='all'>Get All</button><button data-range='future'>All after today</button>
		</div>
		<table>
			<thead>
				<tr><th>Title</th></tr>
				<tr><th>Date</th><th>Flight No.</th><th>Airline</th><th>Time</th><th>Driver</th><th>Passengers</th><th>Shifts</th></tr>
			</thead>
			<tbody>";
		if($flights)foreach($flights as $f){
			$sql = "SELECT `air_staff`.`staff_id`,`air_staff`.`type`,`first_name`,`last_name` FROM `air_staff` INNER JOIN `staff` ON `air_staff`.`staff_id` = `staff`.`staff_id` WHERE `flight_id`='{$f['flight_id']}'";
			$query = new rQuery($con,$sql,false,false,true);
			$result = $query->run(false,2);
			$driver = false;
			$passenger = false;
			if(is_array($result) && count($result) > 0){
				foreach($result as $r){
					if($r['type'] == AIR_TYPE_DRIVER){
						if(!is_array($driver))
							$driver = array();
						array_push($driver, $r);
					}
					elseif($r['type'] == AIR_TYPE_PASSENGER){
						if(!is_array($passenger))
							$passenger = array();
						array_push($passenger, $r);
					}
				}
			}
			echo "<tr class='flight' data-flight_id='{$f['flight_id']}'><td>{$f['date']}</td><td>{$f['flight_no']}</td><td>{$f['airline']}</td><td>{$f['time']}</td><td>";
			if($driver)foreach($driver as $i=>$d){
				if($i > 0)
					echo ", ";
				echo $d['first_name'];
				if($d['last_name'] && $c = substr($d['last_name'],0,1))
					echo " {$c}.";
			}
			echo "</td><td>";
			if($passenger)foreach($passenger as $i=>$p){
				if($i > 0)
					echo ", ";
				echo $p['first_name'];
				if($p['last_name'] && $c = substr($p['last_name'],0,1))
					echo " {$c}.";
			}
			echo "</td><td>{$f['shifts']}</td></tr>";
		}
		echo "		
			</tbody>
		</table>";
	}
}


$Airport = new Airport();

if($_POST['action'] == 'add_form'){
	$Airport->form();
}
elseif($_POST['action'] == 'rep_form'){
	if(!isset($_POST['data']) || (!is_array($_POST['data']) && $_POST['data'] == 'future'))
		$Airport->report();
	elseif(!is_array($_POST['data']) && $_POST['data'] == 'all')
		$Airport->report('0000-00-00');
	elseif(is_array($_POST['data']) && isset($_POST['data']['start_date']) && isset($_POST['data']['end_date'])){
		$Airport->report($_POST['data']['start_date'],$_POST['data']['end_date']);
	}
	else{
		echo "Unknown option. Please contact your administrator for further assistance.<br/>";
	}
}
else{
	echo "<p>No defined function for this action:{$_POST['action']}.</p>";
}
?>
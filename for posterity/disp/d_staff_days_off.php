<?php
require_once('../includes/staff_classes.php');

/*
	since this is such an exception, I actually think it will work better without the calendar
*/

switch($_POST['action']){
	case 'load':
		{// LOAD DAYS OFF
		$type = $_POST['type'];
		$date = $_POST['date'];
		$config = new conFig();
		$sql = " WHERE `type` = '{$type}'";
		$staff = new staffM($con, $sql);
		
		if(!$date)
			$date = session_get('date');
		if(!$date)
			$date = datestr_cur();
		
		session_update('date',$date);
			
			/*
		$start_month = start_of_month($date);
		$end_month = end_of_month($date);
		$start_date = datestr_shift($start_month, 1-datestr_format($start_month, 'j'));
		$end_date = datestr_shift($end_month, 6-datestr_format($end_month,'N'));
			*/

		$weeks = week_month_array_accurate($date);
		$start_date = $weeks[0][0];
		$end_date = $weeks[count($weeks)-1][6];
		$col_count = 1 + count($weeks);

		// PRINTING IT
		echo "<button onclick=save();>Save</button>
		<br/>
		<button onclick=push_date('".datestr_shift($start_date,-1)."');><-</button><input class='change_date' type='date' value='" . $date . "'><button onclick=push_date('".datestr_shift($end_date,1)."');>-></button>
		<table id='days_off' data-start_date='{$start_date}' data-end_date='{$end_date}'>
		<tr><th colspan={$col_count}>Days Off</th></tr>
		<tr><th></th>";
		foreach($weeks as $w)
			echo "<th>" . datestr_format($w[0],"M jS") . "</th>";
		echo "</tr>";

		foreach($staff->staff as $s){
			if($s->is_here($start_date,$end_date)){
				echo "<tr><th class='days_off_staff' data-staff_id='{$s->staff_id}'>{$s->name}</th>";
				for($i=0; $i<$col_count-1; $i++){
					$day_off = $s->get_day_off($weeks[$i][0]);
					echo "<td><select class='days_off_date'><option value='".NULL_DATE."'></option>";
					foreach($config->day as $cd){
						$selected = '';	if($weeks[$i][$cd['id']-1] == $day_off) $selected = ' selected ';
						echo "<option value={$weeks[$i][$cd['id']-1]}{$selected}>{$cd['name']}</option>";
					}			
					echo "</td>";
				}
				echo "</tr>";
			}
		}

		echo "</table>";
		}
		break;
		
	case 'print':
		{// PRINT DAYS OFF
		// $_POST['date'], $_POST['type'], $_POST['options']:array();
		
		
		session_update('date',$_POST['date']);
		$weeks = week_month_array_accurate($_POST['date']);
		
		$month = datestr_format($_POST['date'],'M');
		
		$staff = new staffM($con);
		
		$title = '';
		$config = new conFig();
		$options = $_POST['options'];
		$title .= $config->staff_type($_POST['type']) . " Day Off/Vacation Schedule: ";
		$title .= datestr_format($_POST['date'],'F Y');
		
		$devotions_div = '';
		if($options['devotions_text'] || $options['devotions_css']){
			$devotions_div = "<div style='{$options['devotions_css']}'>{$options['devotions_text']}</div>";
		}
		if($options['inline_css'])
			echo "<style>" . $options['inline_css'] . "</style>";
		echo "
		<div class='header' style='{$options['header_css']}'>{$options['header_text']}</div>
		<div class='title'>{$title}</div>
		<table style='border-collapse:collapse;'><thead>
		<tr><th class='my_table'>Sunday</th><th class='my_table'>Monday</th><th class='my_table'>Tuesday</th><th class='my_table'>Wednesday</th><th class='my_table'>Thursday</th><th class='my_table'>Friday</th><th class='my_table'>Saturday</th></tr>
		</thead><tbody>";
		foreach($weeks as $w){
			echo "<tr class='my_table'>";
			foreach($w as $d){
				$md = datestr_format($d,'j');
				$m = datestr_format($d,'M');
				$day_div = "<div class='day_div";
				if($m != $month) $day_div .= " nonmonth";
				$day_div .= "'>{$md}</div>";
				
				$staff_div = "<div class='staff_div'>";
				
				$sql = "SELECT `staff_away`.`staff_id` FROM `staff_away` INNER JOIN `staff` ON `staff_away`.`staff_id` = `staff`.`staff_id` WHERE `staff_away`.`type` IN(" . AWAY_VAC . "," . AWAY_DAY_OFF . ") AND `staff_away`.`start_date`<='{$d}' AND `staff_away`.`end_date`>='{$d}' AND `staff`.`type`='{$_POST['type']}'";
				$query = new rQuery($con, $sql, false, false, true);
				$staff_list = $query->run();
				
				$already = array();
				if(isset($staff_list) && is_array($staff_list) && count($staff_list)>0)
				foreach($staff_list as $s){
					if(!isset($already[$s['staff_id']])){
						$already[$s['staff_id']] = true;
						$staff->staff[$s['staff_id']]->set_name(NAME_FIRST_L);
						$staff_div .= $staff->staff[$s['staff_id']]->name . "<br/>";
					}
				}
				$staff_div .= "</div>";
				echo "<td class='my_table'>{$day_div}{$devotions_div}{$staff_div}</td>";
			}
			echo "</tr>";
		}
		echo "</tbody></table>
		<div class='footer' style='{$options['footer_css']}'>{$options['footer_text']}</div>";
		}
		break;
}
?>
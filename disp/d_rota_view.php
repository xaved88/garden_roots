<?php
	require_once('../includes/staff_classes.php');
	/* DATA
	Array ( [action] => fill_dialog [date] => 2014-07-23 [shift_id] => 11 [pos_id] => 10 )
	*/
	
	class shortStaff{
		public $staff_id = false;
		public $name = false; 
		public $av = 0;
		public $conflict = false;
		public $scheduled = false;
		public $class = '';
		
		public function add_class(){
			if($this->scheduled)
				$this->class .= ' scheduled';
			$this->class .= " av_{$this->av}";
			if($this->conflict)
				$this->class .= ' conflict';
			
		}
	}
	
	// DATA PREP
	$staff_prep = array();
	$pos = new dPos($con,$_POST['pos_id']);
	$shift = new dShift($con,SHIFT_PAR,$_POST['shift_id']);
	$day_format = datestr_format($_POST['date'], DATESTR_FORMAT_DJS);
	$title = $day_format . " " . $shift->name . " " . $pos->name;
	
	
	
	// GET ALL AVAILABLE
	$sql = " INNER JOIN `staff_pos` ON `staff`.`staff_id` = `staff_pos`.`staff_id` WHERE `pos_id`='{$_POST['pos_id']}'";
	$staffLib = new staffM( $con, $sql);
	
	foreach($staffLib->staff as $s){
		$avn = $s->set_avn($con, $_POST['date'],$_POST['date']);
		if(isset($avn[$_POST['date']][$_POST['shift_id']]) && $avn[$_POST['date']][$_POST['shift_id']]['pref']!=0){
			$ss = new shortStaff();
			$ss->staff_id = $s->staff_id;
			$ss->name = $s->name;
			$ss->av = $avn[$_POST['date']][$_POST['shift_id']]['pref'];
			$staff_prep[$s->staff_id] = $ss;
		}
	}
	// GET ALL SCHEDULED
	$sql = "SELECT `staff_id` FROM `rota` WHERE `sheet`='".ROTA_SHEET_IP."' AND `date`='{$_POST['date']}' AND `shift_id`='{$_POST['shift_id']}' AND `pos_id`='{$_POST['pos_id']}'";
	$query = new rQuery($con, $sql, false,false,true);
	$results = $query->run();
	
	if($results && is_array($results) && count($results)>0)
	foreach($results as $r){
		if(isset($staff_prep[$r['staff_id']])){
			$staff_prep[$r['staff_id']]->scheduled = true;
		}
		else{
			$s = new staffS($con, $r['staff_id']);
			$ss = new shortStaff();
			$ss->staff_id = $s->staff_id;
			$ss->name = $s->name;
			$ss->scheduled = true;
			$staff_prep[$r['staff_id']] = $ss;
		}
	}
	
	// ORDER
	$staff = array();
	
	$step = 4;
	while(count($staff_prep) > 0 && $step > 0){
		if($step == 4)
		foreach($staff_prep as $i=>$s){
			if($s->scheduled){
				array_push($staff, $s);
				unset($staff_prep[$i]);
			}
		}
		if($step < 4){
			foreach($staff_prep as $i=>$s){
				if($s->av == $step){
					array_push($staff,$s);
					unset($staff_prep[$i]);
				}
			}
		}
		$step --;
	}
	// ADD CLASS
	foreach($staff as $s){
		$s->add_class();
	}
	
	// PRINT IT
	// HEADER
	echo "<div class='staff_dialog' data-date='{$_POST['date']}' data-shift_id='{$_POST['shift_id']}' data-pos_id='{$_POST['pos_id']}'>";
		echo "<div class='top_bar'><span class='text'>{$title}</span><button class='close'>X</button><button class='save'>S</button></div>";
		echo "<div class='staff_list'>";
		foreach($staff as $s){
			echo "<div class='staff{$s->class}' data-staff_id='{$s->staff_id}'><button class='toggle'>";
			if($s->scheduled)
				echo "X";
			else echo "+";
			echo "</button><span class='text'>{$s->name}</span></div>";
		}
		echo "</div>";
	echo "</div>";
?>
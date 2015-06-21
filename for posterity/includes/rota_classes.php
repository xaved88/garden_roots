<?php
require_once('general_functions.php');
require_once('constants.php');
require_once('calendar.php');
require_once('staff_classes.php');


class rotaNeed{
	public $name, $date, $day, $shift_id, $pos_id, $number;
	
	function __construct($name = null, $date = null, $day = null, $shift_id = null, $pos_id = null, $number = null){
		$this->name = $name;
		$this->date = $date;
		$this->day = $day;
		$this->shift_id = $shift_id;
		$this->pos_id = $pos_id;
		$this->number = $number;
	}

	public function insert($con){
		$field = array('shift_id','pos_id','number');
		$value = array($this->shift_id, $this->pos_id, $this->number);
		if($this->day){
			$table = 'templates_rota_needs';
			array_push($field, 'name');
			array_push($field, 'day');
			array_push($value, $this->name);
			array_push($value, $this->day);
		}
		elseif($this->date){
			$table = 'rota_needs_ex';
			array_push($field, 'date');
			array_push($value, $this->date);
		}
		return db_insert($con,$table,$field,$value);
	}
}
class rotaNeedSched{
	public $name, $start_date, $end_date;
	
	function __construct($name = null, $start_date = null, $end_date = null){
		$this->name = $name;
		$this->start_date = $start_date;
		$this->end_date = $end_date;
	}
	
	public function insert($con){
		$field = array('name','start_date','end_date');
		$value = array($this->name, $this->start_date, $this->end_date);
		$table = 'rota_needs_sched';
		return db_insert($con,$table,$field,$value);
	}
}
class rotaNeeds{
	public $needs_ex = array(); //rotaNeed
	public $needs_temp = array(); // rotaNeed
	public $needs_sched = array(); // rotaNeedSched
	public $name, $date;
	public $con;
	
	function __construct($con = null, $name = null, $date = null){ // start & end dates do nothing now, just for later.
		$this->con = $con;
		$this->name = $name;
		if($date == null)
			$date = datestr_cur();
		$this->date = $date;
		if($this->con)
			$this->init();
	}
	
	public function init(){
		if($this->name){
			$field = 'name';
			$value = $this->name;
		}
		else{
			$field = false;
			$value = false;
		}
		$needs_temp = db_select_all($this->con, 'templates_rota_needs',$field,$value, $force_array = true, $limit = false,false);
		foreach($needs_temp as $n){
			if(!isset($this->needs_temp[$n['name']]))
				$this->needs_temp[$n['name']] = array();
			array_push($this->needs_temp[$n['name']], new rotaNeed($n['name'], null, $n['day'], $n['shift_id'], $n['pos_id'], $n['number']));
		}
		/*
		$needs_ex = db_select_all($this->con, 'rota_needs_ex',false,false,true,false,false);
		foreach($needs_ex as $n){
			array_push($this->needs_ex, new rotaNeed(null, $n['date'], null, $n['shift_id'], $n['pos_id'], $n['number']));
		}
		*/
		$sql = "SELECT * FROM `rota_needs_ex` WHERE `date` >= '{$this->date}' ORDER BY `date` ASC";
		$query = new rQuery($this->con, $sql, false,false,$force_array = true);
		$needs_ex = $query->run();
		if(is_array($needs_ex) && count($needs_ex) > 0)
		foreach($needs_ex as $n){
			if(!isset($this->needs_ex[$n['date']]) || !is_array($this->needs_ex[$n['date']]))
				$this->needs_ex[$n['date']] = array();
			array_push($this->needs_ex[$n['date']], new rotaNeed(null, $n['date'], null, $n['shift_id'], $n['pos_id'], $n['number']));
		}
		
		$sql = "SELECT * FROM `rota_needs_sched` WHERE `end_date` >= '{$this->date}' ORDER BY `start_date` ASC";
		$query = new rQuery($this->con, $sql, false,false,$force_array = true);
		$needs_sched = $query->run();
		if(is_array($needs_sched) && count($needs_sched)>0)
		foreach($needs_sched as $n){
			array_push($this->needs_sched, new rotaNeedSched($name = $n['name'], $start_date = $n['start_date'], $end_date = $n['end_date']));
		}
		
	}

	public function temp_names(){
		$names = array();
		foreach($this->needs_temp as $i=>$n){
			array_push($names, $i);
		}
		return $names;
	}
	
	public function form_temp_select($value = ''){
		$sel = "<select class='needs_cal_temp'>";	
		foreach($this->temp_names() as $n){
			$sel .= "<option value='{$n}'";
			if($value == $n) $sel .= " selected";
			$sel .= ">{$n}</option>";
		}
		$sel .= "</select>";
		return $sel;
	}
	
	
	public function add_ex($n){
		if(is_object($n))
			array_push($this->needs_ex, $n);
		elseif(is_array($n) && count($n)>0){
			(isset($n['name'])) ? $name=$n['name']:$name=null;
			(isset($n['date'])) ? $date=$n['date']:$date=null;
			(isset($n['shift_id'])) ? $shift_id=$n['shift_id']:$shift_id=null;
			(isset($n['day'])) ? $day=$n['day']:$day=null;
			(isset($n['pos_id'])) ? $pos_id=$n['pos_id']:$pos_id=null;
			(isset($n['number'])) ? $number=$n['number']:$number=null;
			array_push($this->needs_ex, new rotaNeed($name, $date, $day, $shift_id, $pos_id, $number));
		}
	}
	public function add_temp($n){
		if(is_object($n))
			array_push($this->needs_temp, $n);
		elseif(is_array($n) && count($n)>0){
			(isset($n['name'])) ? $name=$n['name']:$name=null;
			(isset($n['date'])) ? $date=$n['date']:$date=null;
			(isset($n['shift_id'])) ? $shift_id=$n['shift_id']:$shift_id=null;
			(isset($n['day'])) ? $day=$n['day']:$day=null;
			(isset($n['pos_id'])) ? $pos_id=$n['pos_id']:$pos_id=null;
			(isset($n['number'])) ? $number=$n['number']:$number=null;
			array_push($this->needs_temp, new rotaNeed($name, $date, $day, $shift_id, $pos_id, $number));
		//	array_push($this->needs_temp, new rotaNeed($name = $n['name'], $date = null, $day = $n['day'], $shift_id = $n['shift_id'], $pos_id = $n['pos_id'], $number = $n['number']));
		}
	
	}
	public function add_cal($n){
		if(is_object($n))
			array_push($this->needs_sched, $n);
		elseif(is_array($n) && count($n)>0)
			array_push($this->needs_sched, new rotaNeedSched($name = $n['name'], $start_date = $n['start_date'], $end_date = $n['end_date']));
	}
	
	public function form_cal(){
		echo "<table id='rota_needs_cal'>
			<tr><th colspan=3>Schedule for Rota Needs</th><th><button onclick=add_needs_cal();>Add New</button></tr>
			<tr><th>Template</th><th>Start</th><th>End</th><th>Delete</th></tr>
			<tr class='hidden' data-save='1'><td>" . $this->form_temp_select() . "</td><td><input type='date' class='needs_cal_start'></td><td><input type='date' class='needs_cal_end'></td><td><button onclick=remove_needs_cal(this);>X</button></td></tr>";
		foreach($this->needs_sched as $n){
			echo "<tr class='needs_cal_inst' data-save='1'><td>" . $this->form_temp_select($n->name) . "</td><td><input type='date' class='needs_cal_start' value='{$n->start_date}'></td><td><input type='date' class='needs_cal_end' value='{$n->end_date}'></td><td><button onclick=remove_needs_cal(this);>X</button></td></tr>";
		}
		echo "</table>";
	}
	public function form_ex(){
		// Select a date,then a shift table.
		$cal = new Calendar($this->con, false, CAL_TYPE_WEEK, $id = 'calendar_needs_ex', $class = 'calendar_alternating', $title = "Date: <input class='needs_ex_date' type='date'/>", $row1_type = CAL_POS, $row2_type = null, $col1_type = CAL_SHIFT, $col2_type = null, false, $datestr_format = "D jS");
		$cal->date_select_buttons = false;
		$cal->date_select_input = false;
		$cal->init(false);
		$cal->default_content = "<input type='number'>";
		echo "<h3>New Exception</h3>";
		echo "<div class='needs_ex' data-save='0' data-date='new'><button onclick=remove_needs(this);>Set</button>";
		$cal->print_it();
		echo "</div>";
		foreach($this->needs_ex as $i=>$d){
			$cL = new CalContentLib;
			foreach($d as $x){
				$cL->add_content($data = "<input type='number' value='{$x->number}'>", $x->pos_id, $x->shift_id);
			}
			echo "<h3>{$i}</h3>
				<div class='needs_ex' data-save='1' data-date='{$i}'><button onclick=remove_needs(this);>Delete</button>";
			$cal->title = "Date: <input class='needs_ex_date' type='date' value='{$i}'/>";
			$cal->content = $cL;
			$cal->print_it();
			echo "</div>";
		}
	}
	public function form_temp(){
		$cal = new Calendar($this->con, false, CAL_TYPE_WEEK, $id = 'calendar_needs_temp', $class = 'calendar_alternating', $title = "Name: <input class='needs_temp_name' type='text'/>", $row1_type = CAL_POS, $row2_type = CAL_SHIFT, $col1_type = CAL_DAY, $col2_type = null, false, $datestr_format = "D jS");
		$cal->date_select_buttons = false;
		$cal->date_select_input = false;
		$cal->init(false);
		$cal->default_content = "<input type='number'>";
		echo "<h3>New Template</h3>";
		echo "<div class='needs_temp' data-save='0' data-name='new'><button onclick=remove_needs(this);>Set</button>";
		$cal->print_it();
		echo "</div>";
		
		$temp_names = $this->temp_names();
		foreach($temp_names as $t){
			$cL = new CalContentLib;
			foreach($this->needs_temp[$t] as $n){
				$cL->add_content($data = "<input type='number' value='{$n->number}'>", $row1 = $n->pos_id, $col1 = $n->day, $row2 = $n->shift_id);
				//<input type='number' value='{$n->number}'>
			}
			echo "<h3>{$t}</h3>";
			echo "<div class='needs_temp' data-save='1' data-name'{$t}'><button onclick=remove_needs(this);>Delete</button>";
			$cal->title = "Name: <input class='needs_temp_name' type='text' value='{$t}'/>";
			$cal->content = $cL;
			$cal->print_it();
			echo "</div>";
			
		}
	}
	
	public function insert_all(){
		//SCHED
		echo "Needs Sched Count: " . count($this->needs_sched) . "<br/>";
		if(count($this->needs_sched) > 0)
		foreach($this->needs_sched as $n){
			$n->insert($this->con);		
		}
	}
	public function delete_all(){
		//EX
		foreach($this->needs_ex as $n){
			$where_field = 'date';
			$where_value = $n->date;
			db_delete($con,'rota_needs_ex', $where_field, $where_value);
		}
		//TEMP
		foreach($this->needs_temp as $n){
			$where_field = 'name';
			$where_value = $n->name;
			db_delete($con,'templates_rota_needs', $where_field, $where_value);
		}
		//SCHED
		foreach($this->needs_sched as $n){
			$where_field = array('name','start_date','end_date');
			$where_value = array($n->name, $n->start_date, $n->end_date);
			db_delete($con,'rota_needs_sched', $where_field, $where_value);			
		}
	}
	public function delete_sched($con){
		db_delete($con,'rota_needs_sched', $where_field, $where_value);		
	}
}

class Rota{
	public $cal, $type, $sheet;
	public $con;
	public $staff,$pos;
	public $group = null;
	public $start_date, $end_date;
	public $datestr = false;
	public $scheduled = array();
	
	function __construct($con){
		$this->con = $con;
		$this->type = ROTA_STD;
	}
	
	public function init(){
		$this->pos = new dPosLib($this->con);
		
		if(!$this->datestr)
			$this->datestr = session_get('date');
		$cal = new Calendar($this->con, $this->datestr, $type = CAL_TYPE_WEEK, $id = 'rota_cal', $class = 'calendar_rota', $title = "Rotas", $row1_type = CAL_POS, $row2_type = null, $col1_type = CAL_DATE, $col2_type = CAL_SHIFT, $title_suffix = false, $datestr_format = "D jS", $content = null, $default_content = null);
		
		$cal->group = $this->group;
		$cal->group_select = true;
		$cal->col2_format = 'abbr';
		
		if($this->type == ROTA_STD){
		}
		elseif($this->type == ROTA_STAFF){
			$cal->row1_type = CAL_STAFF;
			$cal->title = "Rotas - Staff View";
		}
		$this->cal = $cal;
		$this->cal->init(false);
		
		$this->start_date = $this->cal->start_date;
		$this->end_date = $this->cal->end_date;
		
		// RETURNING AN ERROR IF A STAFF IS SCHEDULED BUT AWAY. OOPS.
		//$this->staff = new staffM($this->con, $sql = false, $param_type=false, $param=false, $group = null, array('start_date'=>$this->start_date, 'end_date'=>$this->end_date));
		
		$this->staff = new staffM($this->con, $sql = false, $param_type=false, $param=false, $group = null);
		
		$this->get_scheduled();
		$this->set_content();
		
	}
	
	public function get_scheduled($sheet = ROTA_SHEET_IP){ // STILL NEEDS TO CHANGE: I MEAN, TYPE, SHEET? ETC.
		$sql = "SELECT * FROM `rota` WHERE `date` >= ? AND `date` <= ? AND `sheet` = ?";
		$param = array((string)$this->start_date, (string)$this->end_date, (string)$sheet);
		$query = new rQuery($this->con, $sql, 'sss', $param, true);
		$this->scheduled = $query->run();	
	}
	
	public function check_scheduled($sheet,$date,$staff_id,$shift_id,$pos_id){
		$array = $this->scheduled;
		if($array){
			$terms = array('sheet','date','staff_id','shift_id','pos_id');
			foreach($terms as $t){
			if(count($array) > 0)
				foreach($array as $i=>$a){
					if($a[$t] != $$t)
						unset($array[$i]);
				}
			}
		
			if(count($array) > 0) // CONSIDER REMOVING THESE FROM THE $this->scheduled, as they won't need to be checked again. Not necessary now, but in larger databases it could be.
				return true;
		}
		return false;
	}
	public function set_content(){
		$this->content = new CalContentLib();
		if($this->type == ROTA_STAFF){
			// SETTING THE INDIVIDUAL CONTENT
			foreach($this->cal->staff->staff as $staff){
				foreach($this->cal->shift->par as $shift){
					foreach($this->cal->date as $date){
						$content = "<div class='td_div'><div class='rota_staff_buttons'>";
						foreach($this->group->pos as $i=>$pos){
							$capability_class = ' incapable';
							if($staff->has_pos($i))
								$capability_class = ' capable';
							$sel_class = ' unselected';
							$clicked = 0;
							if($this->check_scheduled(ROTA_SHEET_IP,$date,$staff->staff_id,$shift->shift_id, $i)){
								$sel_class = ' selected';
								$clicked = 1;
							}
							$content .= "<div class='button_div'><button class='pos_select radio_toggle{$sel_class}{$capability_class}' data-clicked='{$clicked}' value='{$i}'>{$this->pos->pos[$i]->abbr}</button></div>";
							
						}
						$content .= "</div></div>";
						
						$this->content->add_content($content, $row1 = $staff->staff_id, $col1 = $date, $row2 = null, $col2 = $shift->shift_id, $reset = true);
					}
				}
			}
			// SETTING THE DEFAULT CONTENT. IN THEORY, THIS SHOULD NEVER GET USED.
			$default_content = "<div class='td_div'><div class='rota_staff_buttons'>";
			foreach($this->group->pos as $i=>$p){
				$default_content .= "<div class='button_div'><button class='pos_select radio_toggle unselected' data-clicked='0' value='{$i}'>{$this->pos->pos[$i]->abbr}</button></div>";
			}
			$default_content .= "</div></div>";
		}
		elseif($this->type == ROTA_STD){
			if(is_array($this->scheduled) && count($this->scheduled)>0)
			foreach($this->scheduled as $s){
				if(isset($this->staff->staff[$s['staff_id']])){
					$data = "<div class='staff_member'>{$this->staff->staff[$s['staff_id']]->name}</div>";
					$this->content->add_content($data, $row1 = $s['pos_id'], $col1 = $s['date'], $row2 = null, $col2 = $s['shift_id'], $reset = true);
				}
			}
			$default_content = "";
		}
		$this->cal->content = $this->content;
		$this->cal->default_content = $default_content;
	}
	
	public function show(){
		$this->cal->print_it();
	}
}

class RotaCheck{ //($con, $start_date = null, $end_date = null, $group = null)
	public $con;
	public $start_date, $end_date, $group_id;
	public $group = null;
	public $staff, $date, $shift, $pos;
	
	public $staff_id_str,$staff_id_str_zeroless, $pos_id_str, $shift_id_str;
	public $shift_count = array();
	public $dhp_count = array(); // array[date][pos_id][shift_id/'total']
	
	function __construct($con, $start_date = null, $end_date = null, $group_id = null){
		$this->con = $con;
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		$this->group_id = $group_id;
		
		$this->init();
	}
	
	public function init(){
		if($this->group_id)
			$this->group = new dGroup($this->con,$this->group_id);
		$this->staff = new staffM($this->con, false,false,false, $this->group);
		$this->date =  datestr_array_start_end($this->start_date, $this->end_date);
		$this->shift = new dShiftLib($this->con,false,true,$this->group);
		$this->pos = new dPosLib($this->con, true, $this->group);	
		
		$this->set_id_arrays();
		$this->count_shifts();
	}
	
	public function set_id_arrays(){
		$pos_id_array = array();
		foreach($this->pos->pos as $p)
			array_push($pos_id_array, $p->pos_id);
		array_push($pos_id_array,'0');
		$this->pos_id_str = "'" . implode("', '", $pos_id_array) . "'";
		
		$staff_id_array = array();
		foreach($this->staff->staff as $s)
			array_push($staff_id_array, $s->staff_id);
		$this->staff_id_str_zeroless = "'" . implode("', '", $staff_id_array ) . "'";
		array_push($staff_id_array,'0');
		$this->staff_id_str = "'" . implode("', '", $staff_id_array ) . "'";
		$shift_id_array = array();
		foreach($this->shift->par as $s)
			array_push($shift_id_array, $s->shift_id);
		array_push($shift_id_array,'0');
		$this->shift_id_str = "'" . implode("', '", $shift_id_array ) . "'";
	}
	
	public function query_s(){ // STAFF: Contract
		$staff = array();
		
		foreach($this->staff->staff as $s){
			$total = $this->shift_count[$s->staff_id]['total_censored'];
			if($total < $s->contract['min']) // UNDER CONTRACT
				array_push($staff,array(
					'date' => 0,
					'staff_id' => $s->staff_id,
					'shift_id' => 0,
					'pos_id' => 0,
					'class'=>'rq_contract_under',
					'message' => "Staff member under minimum contract shifts, at {$total} of {$s->contract['min']}"
				));
			else if($total > $s->contract['max']) // OVER CONTRACT
				array_push($staff,array(
					'date' => 0,
					'staff_id' => $s->staff_id,
					'shift_id' => 0,
					'pos_id' => 0,
					'class'=>'rq_contract_over',
					'message' => "Staff member over maximum contract shifts, at {$total} of {$s->contract['max']}"
				));
		}
		return $staff;
	}
	public function query_sd(){ // STAFF DATE: AWAY QUERIES: Vacation, day off,  arr/dep, other. //TODO: Spouse
		$sd = array(); // staff/date
		
		// AWAY MINUS AWAY_ARRDEP
		$sql = "SELECT * FROM `staff_away` WHERE `staff_id` IN({$this->staff_id_str}) AND `start_date` <= '{$this->end_date}' AND `end_date` >= '{$this->start_date}' AND `type` != '" .AWAY_ARRDEP."'";
		$query = new rQuery($this->con, $sql, false,false,true);
		$result = $query->run();
		
		if(is_array($result) && count($result)>0)
		foreach($result as $r)
			foreach($this->date as $d)
				if($d <= $r['end_date'] && $d >= $r['start_date']){
					array_push($sd,array(
						'date' => $d,
						'staff_id' => $r['staff_id'],
						'shift_id' => 0,
						'pos_id' => 0,
						'class'=>'rq_away',
						'message' => "Staff is away of type:{$r['type']} from {$r['start_date']} til {$r['end_date']}."
					));
				}
		
		// ARRDEP
		foreach($this->staff->staff as $s){
			foreach($this->date as $d){
				if(!$s->is_here($d,$d)){
					array_push($sd,array(
						'date' => $d,
						'staff_id' => $s->staff_id,
						'shift_id' => 0,
						'pos_id' => 0,
						'class' => 'rq_away',
						'message' => 'Staff has not arrived.'
					));
				}
			}
		}
		return $sd;
		
		// SPOUSE DAY OFF
		
	}
	public function query_sp(){ // STAFF POSITION: Position Preference, Min, & Max
		$sp = array(); // staff/pos
		
		// LOAD THEIR LIMITS
		$sql = "SELECT * FROM `staff_pos` WHERE `staff_id` IN({$this->staff_id_str}) AND `pos_id` IN({$this->pos_id_str})";
		$query = new rQuery($this->con, $sql, false,false,true);
		$result = $query->run();
		
		if($result && count($result) > 0)
		foreach($result as $r){
			if(isset($this->shift_count[$r['staff_id']]) && isset($this->shift_count[$r['staff_id']][$r['pos_id']]))
			{	// OVER CONTRACT
				if($this->shift_count[$r['staff_id']][$r['pos_id']] > $r['max'])
					array_push($sp,array(
						'date' => 0,
					'staff_id' => $r['staff_id'],
					'shift_id' => 0,
					'pos_id' => $r['pos_id'],
					'class' => 'rq_pos_over',
					'message' => "Over contracted shifts for position {$r['pos_id']}: at {$this->shift_count[$r['staff_id']][$r['pos_id']]} of {$r['max']}"
					));
				// UNDER CONTRACT
				elseif($this->shift_count[$r['staff_id']][$r['pos_id']] < $r['min'])
					array_push($sp,array(
						'date' => 0,
					'staff_id' => $r['staff_id'],
					'shift_id' => 0,
					'pos_id' => $r['pos_id'],
					'class' => 'rq_pos_under',
					'message' => "Under contracted shifts for position {$r['pos_id']}: at {$this->shift_count[$r['staff_id']][$r['pos_id']]} of {$r['min']}"
					));
				
			}
			// SOMETHING WRONG AND CANT FIND POSITION INFO
			else{
				array_push($sp,array(
					'date' => 0,
					'staff_id' => $r['staff_id'],
					'shift_id' => 0,
					'pos_id' => $r['pos_id'],
					'class' => 'rq_severe',
					'message' => 'Staff does not have position set.'
				));
			}
		}
		
		return $sp;
	}
	public function query_dhp(){ // DATE SHIFT POSITION: # Working compared to rota needs
		$dhp = array();
		
		// LOAD ROTA NEEDS
		// LOAD EXCEPTIONS
		$sql = "SELECT * FROM `rota_needs_ex` WHERE `date` <= '{$this->end_date}' AND `date` >= '{$this->start_date}' AND `pos_id` IN({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str})";
		$query = new rQuery($this->con, $sql, false,false,true);
		$exceptions = $query->run();
		// LOAD EXCEPTIONS 0
		$sql = "SELECT DISTINCT(date) AS `date` FROM `rota_needs_ex` WHERE `date` <= '{$this->end_date}' AND `date` >= '{$this->start_date}' AND `pos_id` IN({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str})";
		$query = new rQuery($this->con, $sql, false,false,true);
		$exceptions0 = $query->run();
		// LOAD THE TEMPLATES
		$sql = "SELECT rota_needs_sched.`name`,`day`,`shift_id`,`pos_id`,`number` FROM `rota_needs_sched` INNER JOIN `templates_rota_needs` ON rota_needs_sched.name = templates_rota_needs.name WHERE rota_needs_sched.start_date <= '{$this->end_date}' AND rota_needs_sched.end_date >= '{$this->start_date}' AND `pos_id` IN({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str})";
		
		$query = new rQuery($this->con, $sql, false,false,true);
		$templates = $query->run();
		
		// BUILD THE ARRAY:
		$needs = array();
		foreach($this->date as $d){
			$needs[$d] = array();
			foreach($this->shift->par as $s){
				$needs[$d][$s->shift_id] = array();
				foreach($this->pos->pos as $p){
					$needs[$d][$s->shift_id][$p->pos_id]['name'] = '';
					$needs[$d][$s->shift_id][$p->pos_id]['number'] = 0;
				}
			}
		}
		// APPLY TEMPLATES
		if(is_array($templates) && count($templates)>0)
		foreach($templates as $t){
			$date = $this->date[$t['day']-1];
			$needs[$date][$t['shift_id']][$t['pos_id']]['number'] = $t['number'];
			$needs[$date][$t['shift_id']][$t['pos_id']]['name'] = $t['name'];
		}
		// APPLY EXCEPTIONS 0
		if(is_array($exceptions0) && count($exceptions0)>0)
		foreach($exceptions0 as $e){
			foreach($this->shift->par as $s){
				foreach($this->pos->pos as $p){
					$needs[$e['date']][$s->shift_id][$p->pos_id]['name'] = "{$e['date']} Exception";
					$needs[$e['date']][$s->shift_id][$p->pos_id]['number'] = 0;
				}
			}
		}
		// APPLY EXCEPTIONS
		if(is_array($exceptions) && count($exceptions) > 0)
		foreach($exceptions as $e){
			$needs[$e['date']][$e['shift_id']][$e['pos_id']]['name'] = "{$e['date']} Exception";
			$needs[$e['date']][$e['shift_id']][$e['pos_id']]['number'] = $e['number'];
		}
		
		// SET IT ALL TO THE ROTA CLASSES STANDARD & RETURN
		foreach($needs as $d=>$n1)
			foreach($n1 as $s=>$n2)
				foreach($n2 as $p=>$n){
					// OVER AMOUNT
					$message = "rota needs for this position: {$this->dhp_count[$d][$p][$s]} of {$n['number']} - ";;
					if($n['name']) $message .= "From: '{{$n['name']}}'";
					else $message .= "No rota needs template or exception assigned.";
					if($this->dhp_count[$d][$p][$s] > $n['number']){
						$message = "Over " . $message;
						array_push($dhp,array(
							'date' => $d,
							'staff_id' => 0,
							'shift_id' => $s,
							'pos_id' => $p,
							'class' => 'rq_needs_over',
							'message' => $message
						));
					}
					// UNDER AMOUNT
					elseif($this->dhp_count[$d][$p][$s] < $n['number']){
						$message = "Under " . $message;
						array_push($dhp,array(
							'date' => $d,
							'staff_id' => 0,
							'shift_id' => $s,
							'pos_id' => $p,
							'class' => 'rq_needs_under',
							'message' => $message
						));
					}
				}
		return $dhp;
	}
	public function query_sdh(){ // STAFF DATE SHIFT: Checks Staff Availability, Already Working
		$sdh = array();
		{// STAFF AV
		// LOAD STAFF TYPE TEMPLATE
		$sql = "SELECT * FROM `templates_av` WHERE `staff_id` = 0 AND `type` != 0 AND `pos_id` IN({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str})";
		$query = new rQuery($this->con, $sql, false,false,true);
		$type_template = $query->run();
		
		// LOAD STAFF PERSONAL TEMPLATES
		$sql = "SELECT * FROM `templates_av` WHERE `staff_id` IN({$this->staff_id_str_zeroless}) AND `pos_id` IN({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str})";
		$query = new rQuery($this->con, $sql, false, false, true);
		$personal_template = $query->run();
		
		// LOAD STAFF PERSONAL EXCEPTIONS
		$sql = "SELECT * FROM `staff_av` WHERE `staff_id` IN({$this->staff_id_str_zeroless}) AND `pos_id` IN({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str}) AND `date` <= '{$this->end_date}' AND `date` >= '{$this->start_date}' AND `raw` = '0'";
		$query = new rQuery($this->con, $sql, false, false, true);
		$exception = $query->run();
		
		// PREP AV CONTAINER
		$av = array();
		foreach($this->staff->staff as $s){
			$av[$s->staff_id] = array();
			foreach($this->date as $d){
				$av[$s->staff_id][$d] = array();
				foreach($this->shift->par as $h){
					$av[$s->staff_id][$d][$h->shift_id]['pref'] = 0;
					$av[$s->staff_id][$d][$h->shift_id]['message'] = '';
				}
			}
		}
		
		// COMBINE ALL RAW DATA INTO THE AV CONTAINER
		if(is_array($type_template) && count($type_template) > 0)
		foreach($type_template as $t){
			foreach($this->staff->staff as $s){
				if($t['type'] == $s->type){
					$av[$s->staff_id][$this->date[$t['day']-1]][$t['shift_id']]['pref'] = $t['pref'];
					$av[$s->staff_id][$this->date[$t['day']-1]][$t['shift_id']]['message'] = 'staff type av template';
				}
			}
		}
		if(is_array($personal_template) && count($personal_template) > 0)
		foreach($personal_template as $t){
			$av[$t['staff_id']][$this->date[$t['day']-1]][$t['shift_id']]['pref'] = $t['pref'];
			$av[$t['staff_id']][$this->date[$t['day']-1]][$t['shift_id']]['message'] = 'staff member\'s personal av template';
		}
		if(is_array($exception) && count($exception) > 0)
		foreach($exception as $t){
			$av[$t['staff_id']][$t['date']][$t['shift_id']]['pref'] = $t['pref'];
			$av[$t['staff_id']][$t['date']][$t['shift_id']]['message'] = 'staff member\'s av exceptions';
		}
		
		// PROCESS
		foreach($av as $s=>$a1)
			foreach($a1 as $d=>$a2)
				foreach($a2 as $h=>$a){
					if($a['pref'] == 0 || $a['pref'] == -1)
						$message = "Staff unavailable";
					else
						$message = "Available for this shift: from {$a['message']}";
					array_push($sdh,array(
						'date' => $d,
						'staff_id' => $s,
						'shift_id' => $h,
						'pos_id' => 0,
						'class' => "rq_av{$a['pref']}",
						'message' => $message
					));
					
				}
		}
		
		{// CONFLICT: ALREADY WORKING
		$sql = "SELECT * FROM `rota` WHERE `sheet`=".ROTA_SHEET_IP." AND `date`<='{$this->end_date}' AND `date`>='{$this->start_date}'";
		$query = new rQuery($this->con, $sql, false,false,true);
		$result = $query->run();
		
		$conflict = array();
		if($result && count($result) > 0)
		foreach($result as $i=>$r1){
			foreach($result as $j=>$r2){
				if($i!=$j && $r1['date'] == $r2['date'] && $r1['staff_id'] == $r2['staff_id'] && $r1['shift_id'] == $r2['shift_id']){
					array_push($conflict, $r1);
				}
			}
			unset($result[$i]);
		}
		foreach($conflict as $c){
			array_push($sdh,array(
				'date' => $c['date'],
				'staff_id' => $c['staff_id'],
				'shift_id' => $c['shift_id'],
				'pos_id' => 0,
				'class' => "rq_conflict",
				'message' => "Conflict: Already Scheduled."
			));
		}
		}
		return $sdh;
	}
	public function query_sdhp(){ // STAFF DATE SHIFT POS: Av_Pos
		$sdhp = array();
		{// AV POS
		// TEMPLATES
		$sql = "SELECT * FROM `templates_av` WHERE `type`=0 AND `pos_id`!=0 AND `staff_id` IN({$this->staff_id_str}) AND `pos_id` IN ({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str})";
		$query = new rQuery($this->con, $sql, false,false,true);
		$av_template = $query->run();
		
		// EXCEPTIONS
		$sql = "SELECT * FROM `staff_av` WHERE `pos_id`!=0 AND `staff_id` IN({$this->staff_id_str}) AND `pos_id` IN ({$this->pos_id_str}) AND `shift_id` IN ({$this->shift_id_str}) AND `date`>='{$this->start_date}' AND `date`<='{$this->end_date}' AND `raw`='0'";
		$query = new rQuery($this->con, $sql, false,false,true);
		$av_exceptions = $query->run();
		
		// PREP AV CONTAINER
		$av = array();
		foreach($this->staff->staff as $s){
			$av[$s->staff_id] = array();
			foreach($this->date as $d){
				$av[$s->staff_id][$d] = array();
				foreach($this->shift->par as $h){
					$av[$s->staff_id][$d][$h->shift_id]['pos'] = 0;
					$av[$s->staff_id][$d][$h->shift_id]['message'] = '';
				}
			}
		}
		if(is_array($av_template) && count($av_template) > 0)
		foreach($av_template as $a){
			$av[$a['staff_id']][$this->date[$a['day']-1]][$a['shift_id']]['pos'] = $a['pos_id'];
			$av[$a['staff_id']][$this->date[$a['day']-1]][$a['shift_id']]['message'] = "personal template";
		}
		if(is_array($av_exceptions) && count($av_exceptions) > 0)
		foreach($av_exceptions as $a){
			$av[$a['staff_id']][$a['date']][$a['shift_id']]['pos'] = $a['pos_id'];
			$av[$a['staff_id']][$a['date']][$a['shift_id']]['message'] = "av exceptions";
		}
		
		foreach($av as $s=>$a1){
			foreach($a1 as $d=>$a2){
				foreach($a2 as $h=>$a){
					if($h['pos'] != 0)
					array_push($sdhp,array(
						'date' => $d,
						'staff_id' => $s,
						'shift_id' => $h,
						'pos_id' => $a['pos'],
						'class' => "rq_pos_fixed",
						'message' => "AV Pos from the {$a['message']}."
					));
				}
			}
		}
		}
		
		return $sdhp;
	}
	
	public function count_shifts(){
		// THIS QUERY WILL GET ALL SHIFTS, REGARDLESS OF POSITION TYPES
		$sql = "SELECT `staff_id`,`pos_id`,`date`,`shift_id` FROM `rota` WHERE `staff_id` IN({$this->staff_id_str_zeroless}) AND `pos_id` IN({$this->pos_id_str}) AND `date` <= '{$this->end_date}' AND `date` >= '{$this->start_date}' AND `sheet` = '".ROTA_SHEET_IP."'";
		$query = new rQuery($this->con, $sql, false,false,true);
		$result = $query->run();
		
		$this->shift_count = array(); // [$staff_id]['total',$pos_id][count]
		
		// PREPARES ALL THE ARRAYS AND COUNTS
		foreach($this->staff->staff as $s){
			$this->shift_count[$s->staff_id] = array();
			$this->shift_count[$s->staff_id]['total'] = 0;
			$this->shift_count[$s->staff_id]['total_censored'] = 0;
			foreach($this->pos->pos as $p){
				$this->shift_count[$s->staff_id][$p->pos_id] = 0;
			}
		}
		// COUNTS UNCENSORED
		if($result && count($result)>0)
		foreach($result as $r){
			$this->shift_count[$r['staff_id']]['total']++;
			if(isset($this->shift_count[$r['staff_id']][$r['pos_id']]))
				$this->shift_count[$r['staff_id']][$r['pos_id']]++;
			else
				$this->shift_count[$r['staff_id']][$r['pos_id']] = 1;
		}
		
		// THIS QUERY WILL GET ALL THE SHIFTS SCHEDULED TAKING INTO ACCOUNT THE POSITION TYPES
		$sql = "SELECT `rota`.`staff_id` FROM `rota` INNER JOIN `data_pos` ON `rota`.`pos_id` = `data_pos`.`pos_id` WHERE `rota`.`staff_id` IN({$this->staff_id_str_zeroless}) AND `rota`.`pos_id` IN({$this->pos_id_str}) AND `data_pos`.`pos_type` = 0 AND `rota`.`date` <= '{$this->end_date}' AND `rota`.`date` >= '{$this->start_date}' AND `rota`.`sheet` = '".ROTA_SHEET_IP."'";
		
		$query = new rQuery($this->con, $sql, false,false,true);
		$censored = $query->run();
		
		// COUNTS CENSORED
		if($censored && count($censored)>0)
		foreach($censored as $c)
			$this->shift_count[$c['staff_id']]['total_censored']++;
		
		// PREPARE THE DHP
		$this->dhp_count = array();
		foreach($this->date as $d){
			$this->dhp_count[$d] = array();
			foreach($this->pos->pos as $p){
				$this->dhp_count[$d][$p->pos_id] = array();
				$this->dhp_count[$d][$p->pos_id]['total'] = 0;
				foreach($this->shift->par as $s)
					$this->dhp_count[$d][$p->pos_id][$s->shift_id] = 0;
			}
		}
		
		if($result && count($result)>0)
		foreach($result as $r){
			$this->dhp_count[$r['date']][$r['pos_id']]['total'] ++;
			$this->dhp_count[$r['date']][$r['pos_id']][$r['shift_id']]++;
		}
	}

}
?>
<?php
require_once('general_functions.php');
require_once('constants.php');

class staffS{//($con = null, $staff_id = null, $auto_load = true, $date = null)
	public $staff_id;
	public $first_name,$last_name,$name;
	public $type,$partner,$gender;
	public $email,$email2,$phone,$phone2,$mailing_address,$birthday;
	public $name_type = NAME_STANDARD;
	
	public $lang = array();
	public $pos = array();
	public $contract = array();
	public $av = array();
	public $av_temp = array();
	public $av_raw = array();
	public $away = array();
	public $here = array();
	
	public $avn = array();  // types of type/temp/ex
	public $avn_pre_ex = array();
	public $avn_ex = array();
	public $avn_temp = array();
	public $avn_type = array();
	
	function __construct($con = null, $staff_id = null, $auto_load = true, $date = null){
		$this->staff_id = $staff_id;
		if($auto_load && $con)
			$this->load_all($con,$staff_id, $date);
	}
	
	// LOAD FUNCTIONS
	public function load_all($con, $staff_id = false, $date = null){
		if($this->load($con, $staff_id)){
			$this->load_lang($con);
			$this->load_pos($con);
			$this->load_contract($con);
			$this->load_av($con,$date);
			$this->load_away($con);
			$this->load_here($con);
			return true;
		}
		else
			return false;
	}
	public function load($con, $staff_id = false){ // loads everything from the staff table
		if($staff_id !== false)
			$this->staff_id = $staff_id;
		if($this->staff_id){
			$data = db_select_all($con, 'staff', 'staff_id',$this->staff_id, false, $limit = 1);
			if($data){
				$this->first_name = $data['first_name'];
				$this->last_name = $data['last_name'];
				$this->type = $data['type'];
				$this->partner = $data['partner'];
				$this->gender = $data['gender'];
				$this->email = $data['email'];
				$this->email2 = $data['email2'];
				$this->phone = $data['phone'];
				$this->phone2 = $data['phone2'];
				$this->mailing_address = $data['mailing_address'];
				$this->birthday = $data['birthday'];
				
				$this->set_name();
			}
			return true;
		}
		else
			return false;
	}
	public function load_lang($con){
		if($this->staff_id){
			$data = db_select($con, 'staff_lang', 'lang_id', 'staff_id',$this->staff_id, true);
			$this->lang = array();
			if($data) foreach($data as $d)
				$this->lang[$d] = $d;
		}
	}
	public function load_pos($con){
		if($this->staff_id){
			$data = db_select_all($con, 'staff_pos', 'staff_id',$this->staff_id, true);
			$this->pos = array();
			if($data) foreach($data as $d){
				$this->pos[$d['pos_id']] = $d;
			}
		}
	}
	public function load_contract($con){
		if($this->staff_id){
			$data = db_select_all($con, 'staff_contract','staff_id',$this->staff_id);
			if($data){
				$this->contract['min'] = $data['min'];
				$this->contract['max'] = $data['max'];
				$this->contract['pref'] = $data['pref'];
			}
			else{
				$this->contract['min'] = 0;
				$this->contract['max'] = 0;
				$this->contract['pref'] = 0;
			}
		}
	}
	public function load_av($con, $date = null){
		// Load instances ($this->av)
		$field = array('staff_id','raw');
		$value = array($this->staff_id,'0');
		$avLib = db_select_all($con,'staff_av',$field,$value, true, $limit = false, $debug = false);
		$this->av = array();
		foreach($avLib as $av){
			$a = new staffAv($av['date'],null,$this->staff_id,$av['shift_id'],$av['pos_id'],$av['pref'],$av['fixed'],$av['raw']);
			array_push($this->av, $a);
		}
		// Load Templates ($this->av_temp)
		$this->av_temp = new dTemplateAv($con,  $action = TEMP_AV_STAFF, $id = $this->staff_id, $name = TEMP_DEFAULT);
	}
	
	public function load_avn($con){ // loads $avn_ex, avn_temp, and avn_type
		$sql = "SELECT * FROM `av` WHERE `staff_id`='{$this->staff_id}' OR `staff_type`='{$this->type}' ORDER BY `end_date`";
		$query = new rQuery($con,$sql,false,false,true);
		$av = $query->run(false,8);
		
		$this->avn_ex = array();
		$this->avn_temp = array();
		$this->avn_type = array();
		if($av && is_array($av) && count($av)>0)
		foreach($av as $a){
			if(!$a['day'])
				array_push($this->avn_ex, $a);
			else if(!$a['staff_type'])
				array_push($this->avn_temp,$a);
			else
				array_push($this->avn_type,$a);
		}
	}
	public function set_avn($con, $start_date, $end_date, $load=true, $check_here=false){ // sets avn_ex,temp, and type into the avn.
		if($load)
			$this->load_avn($con);
			
		$date = datestr_array_start_end($start_date, $end_date);
		foreach($date as &$d){
			$date[$d] = (int)datestr_format($d,"w") + 1;
		}
		$this->avn = array();
		
		
		// SET TYPE DEFAULT
		if($this->avn_type && is_array($this->avn_type) && count($this->avn_type)>0)
		foreach($this->avn_type as $a){
			if($a['start_date'] == NULL_DATE && $a['end_date'] == NULL_DATE){
				foreach($date as $i=>$d){
					if($d == $a['day']){
						$this->avn[$i][$a['shift_id']]['pref'] = $a['pref'];
						$this->avn[$i][$a['shift_id']]['pos_id'] = $a['pos_id'];
						$this->avn[$i][$a['shift_id']]['type'] = 'type';
					}
				}
			}
		}
		// SET TYPE SEGMENTS
		if($this->avn_type && is_array($this->avn_type) && count($this->avn_type)>0)
		foreach($this->avn_type as $a){
			if($a['start_date'] <= $end_date && $a['end_date'] >= $start_date){
				foreach($date as $i=>$d){
					if($d == $a['day'] && $i <= $end_date && $i <= $a['end_date'] && $i >= $start_date && $i >= $a['start_date']){
						$this->avn[$i][$a['shift_id']]['pref'] = $a['pref'];
						$this->avn[$i][$a['shift_id']]['pos_id'] = $a['pos_id'];
						$this->avn[$i][$a['shift_id']]['type'] = 'type';
					}
				}
			}
		}
		
		// SET TEMP DEFAULT
		if($this->avn_temp  && is_array($this->avn_temp) && count($this->avn_temp)>0)
		foreach($this->avn_temp as $a){
			if($a['start_date'] == NULL_DATE && $a['end_date'] == NULL_DATE){
				foreach($date as $i=>$d){
					if($d == $a['day']){
						$this->avn[$i][$a['shift_id']]['pref'] = $a['pref'];
						$this->avn[$i][$a['shift_id']]['pos_id'] = $a['pos_id'];
						$this->avn[$i][$a['shift_id']]['type'] = 'temp';
					}
				}
			}
		}
		
		// SET TEMP SEGMENTS
		if($this->avn_temp  && is_array($this->avn_temp) && count($this->avn_temp)>0)
		foreach($this->avn_temp as $a){
			if($a['start_date'] <= $end_date && $a['end_date'] >= $start_date){
				foreach($date as $i=>$d){
					if($d == $a['day'] && $i <= $end_date && $i <= $a['end_date'] && $i >= $start_date && $i >= $a['start_date']){
						$this->avn[$i][$a['shift_id']]['pref'] = $a['pref'];
						$this->avn[$i][$a['shift_id']]['pos_id'] = $a['pos_id'];
						$this->avn[$i][$a['shift_id']]['type'] = 'temp';
					}
				}
			}
		}
		
		$this->avn_pre_ex = $this->avn;
		// SET EXCEPTIONS
		if($this->avn_ex && is_array($this->avn_ex) && count($this->avn_ex)>0)
		foreach($this->avn_ex as $a){
			if($a['end_date'] >= $start_date && $a['end_date'] <= $end_date){
				$this->avn[$a['end_date']][$a['shift_id']]['pref'] = $a['pref'];
				$this->avn[$a['end_date']][$a['shift_id']]['pos_id'] = $a['pos_id'];
				$this->avn[$a['end_date']][$a['shift_id']]['type'] = 'ex';
			}
		}
		if(!$check_here)
			return $this->avn;
		
		// HERE SUTFF (Arrival/departure, vacation, days off)
		//print_r($this->avn);
		// TEST: false for nothing, # for a specific staff number.
		$CHECK_STAFF = false;
		// CLEAN AVN
		/* I don't know why these things snuck in. Array keys 0-6 ish it seems, like there's a week value being put in. seemed to be all copies of just a single date. This is a fix for the moment, but must be an error up the line.*/
		foreach($this->avn as $i=>$a){
			if($i<100)
				unset($this->avn[$i]);
		}
		// GET ARRIVAL DEPARTURE
		$sql = "SELECT `start_date`,`end_date` FROM `staff_away` WHERE `staff_id`='{$this->staff_id}' AND `type`='".AWAY_ARRDEP."' AND `start_date`<='{$end_date}' AND `end_date` >= '{$start_date}'";
		$query = new rQuery($con,$sql,false,false,true);
		$arrdep = $query->run(false,2);
		if(is_array($arrdep) && count($arrdep)>0)
			$arrdep = $arrdep[0];
		else
			$arrdep = false;
			
		// GET VAC + DAY OFF
		$sql = "SELECT `start_date`,`end_date` FROM `staff_away` WHERE `staff_id`='{$this->staff_id}' AND ( `type`='".AWAY_VAC."' OR `type`='".AWAY_DAY_OFF."' ) AND `start_date`<='{$end_date}' AND `end_date` >= '{$start_date}'";
		$query = new rQuery($con,$sql,false,false,true);
		$vac = $query->run(false,2);
		if(!is_array($vac) || count($vac)<=0)
			$vac = false;
		
		if($CHECK_STAFF && $CHECK_STAFF==$this->staff_id){
			echo "<h3>ArrDep:</h3>"; print_r($arrdep);
			echo "<h3>Vac:</h3>"; print_r($vac);
			echo "<h3>AVN Before:</h3>"; print_r($this->avn);
		}
		// PROCESS EACH:
		// CLEAN:
		
		// ARRDEP
		if($arrdep){
			$s = $arrdep['start_date'];
			$e = $arrdep['end_date'];
			
			foreach($this->avn as $i=>&$a){
				if($i >= $e || $i <= $s){
					if($CHECK_STAFF && $CHECK_STAFF==$this->staff_id)echo "<h3>In ArrDEP check: {$s} : {$i} : {$e}</h3>";
					foreach($a as &$shift){
						$shift['pref'] = 0;
						$shift['pos_id'] = 0;
						$shift['type'] = 'not_here';
					}
				}
			}
		}
		// VAC+DAY_OFF
		if($vac){
			foreach($vac as $v){
				$s = $v['start_date'];
				$e = $v['end_date'];
				if($s!=$e){ // cycle through them all
					foreach($this->avn as $i=>&$a){
						if($i <= $e && $i >= $s){
							if($CHECK_STAFF && $CHECK_STAFF==$this->staff_id)echo "<h3>In Vac check: {$s} : {$i} : {$e}</h3>";
							foreach($a as &$shift){
								$shift['pref'] = 0;
								$shift['pos_id'] = 0;
								$shift['type'] = 'away';
							}
						}
					}
				}
				else{ // just disable that one day
					if($CHECK_STAFF && $CHECK_STAFF==$this->staff_id)echo "<h3>In DayOFF check: {$s} : {$i} : {$e}</h3>";
					foreach($this->avn[$s] as &$shift){
						$shift['pref'] = 0;
						$shift['pos_id'] = 0;
						$shift['type'] = 'away';
					}
				}
			}
		}
		if($CHECK_STAFF && $CHECK_STAFF==$this->staff_id){
			echo "<h3>AVN After:</h3>"; print_r($this->avn);
		}
		return $this->avn;
		/*
		echo "<h3>arrdep</h3>";print_r($arrdep);
		echo "<h3>vac</h3>";print_r($vac);
		*/
		/*
		define("AWAY_ARRDEP",1);
		define("AWAY_VAC",2);
		define("AWAY_DAY_OFF",3);
		*/
	}
	public function load_away($con){
		if($this->staff_id){
			$sql = "SELECT * FROM staff_away WHERE `staff_id` = '{$this->staff_id}' AND `type`!='".AWAY_ARRDEP."' ORDER BY `end_date` DESC";
			$q = new rQuery($con, $sql, false,false, true);
			$data = $q->run();
			$this->away = array();
			if($data) foreach($data as $d){
				array_push($this->away, array('start_date' => $d['start_date'], 'end_date' => $d['end_date'], 'type' => $d['type']));
			}
		/*	I DONT KNOW WHY THIS BROKE: AN ISSUE WITH THE STAFF ID AND ITS TYPE? MAYBE READ THEM AS AN INT INSTEAD. NOPE, INT DOESN'T WORK EITHER
			$sql = "SELECT * FROM staff_away WHERE `staff_id` = ? AND `type`!='".AWAY_ARRDEP."' ORDER BY `end_date` DESC";
			$q = new rQuery($con, $sql, "s", (string)$this->staff_id, true);
			echo "<h2>load_away</h2>";
			$data = $q->run(DEBUG);
			$this->away = array();
			if($data) foreach($data as $d){
				array_push($this->away, array('start_date' => $d['start_date'], 'end_date' => $d['end_date'], 'type' => $d['type']));
			}
			*/
		}
	}
	public function load_here($con){
		if($this->staff_id){
			$sql = "SELECT * FROM `staff_away` WHERE `staff_id` = '{$this->staff_id}' AND `type` = '".AWAY_ARRDEP."' ORDER BY `end_date` DESC";
			$q = new rQuery($con, $sql, false,false, true);
			$data = $q->run();
			$this->here = array();
			if($data) foreach($data as $d){
				array_push($this->here, array('start_date' => $d['start_date'], 'end_date' => $d['end_date'], 'type' => $d['type']));
			}
			/*
			$sql = "SELECT * FROM `staff_away` WHERE `staff_id` = ? AND `type` = '".AWAY_ARRDEP."' ORDER BY `end_date` DESC";
			$q = new rQuery($con, $sql, "i", (int)$this->staff_id, true);
			echo "<h2>load_here: {$this->staff_id}</h2>";
			$data = $q->run(DEBUG);
			$this->here = array();
			if($data) foreach($data as $d){
				array_push($this->here, array('start_date' => $d['start_date'], 'end_date' => $d['end_date'], 'type' => $d['type']));
			}
			*/
		}
	}
	
	public function make_av_raw($con,$start_date,$end_date){
		// LOAD STAFF TYPE TEMPLATE
		$sql = "SELECT * FROM `templates_av` WHERE `staff_id` = 0 AND `type` = '{$this->type}'";
		$query = new rQuery($con, $sql, false,false,true);
		$type_template = $query->run();
		
		// LOAD STAFF PERSONAL TEMPLATES
		$sql = "SELECT * FROM `templates_av` WHERE `staff_id` = '{$this->staff_id}'";
		$query = new rQuery($con, $sql, false, false, true);
		$personal_template = $query->run();
		
		// LOAD STAFF PERSONAL EXCEPTIONS
		$sql = "SELECT * FROM `staff_av` WHERE `staff_id` = '{$this->staff_id}' AND `date` <= '{$end_date}' AND `date` >= '{$start_date}' AND `raw` = '0'";
		$query = new rQuery($con, $sql, false, false, true);
		$exception = $query->run();
		
		// USE THE DATES TO GET THEM ALL IN THE SAME FORMAT:
		$date = datestr_array_start_end($start_date, $end_date);
		
		$raw_av = array();
		
		// date, raw, staff_id, shift_id, pos_id, pref, fixed
		if(is_array($type_template) && count($type_template) > 0)
		foreach($type_template as $t){
			foreach($date as $d){
				if(datestr_format($d,'w') == $t['day']-1){
					if(!isset($raw_av[$d])) $raw_av[$d] = array();
					if(!isset($raw_av[$d][$t['shift_id']])) $raw_av[$d][$t['shift_id']] = array();
					$raw_av[$d][$t['shift_id']]['pref'] = $t['pref'];
					$raw_av[$d][$t['shift_id']]['fixed'] = $t['fixed'];
					$raw_av[$d][$t['shift_id']]['pos_id'] =  $t['pos_id'];
				}
			}
		}
		if(is_array($personal_template) && count($personal_template) > 0)
		foreach($personal_template as $t){
			foreach($date as $d){
				if(datestr_format($d,'w') == $t['day']-1){
					if(!isset($raw_av[$d])) $raw_av[$d] = array();
					if(!isset($raw_av[$d][$t['shift_id']])) $raw_av[$d][$t['shift_id']] = array();
					$raw_av[$d][$t['shift_id']]['pref'] = $t['pref'];
					$raw_av[$d][$t['shift_id']]['fixed'] = $t['fixed'];
					$raw_av[$d][$t['shift_id']]['pos_id'] =  $t['pos_id'];
				}
			}
		}
		
		if(is_array($exception) && count($exception) > 0)
		foreach($exception as $t){
			if(!isset($raw_av[$t['date']])) $raw_av[$t['date']] = array();
			if(!isset($raw_av[$t['date']][$t['shift_id']])) $raw_av[$t['date']][$t['shift_id']] = array();
			$raw_av[$t['date']][$t['shift_id']]['pref'] = $t['pref'];
			$raw_av[$t['date']][$t['shift_id']]['fixed'] = $t['fixed'];
			$raw_av[$t['date']][$t['shift_id']]['pos_id'] =  $t['pos_id'];
		}
		
		// GET ALL THE DAYS OFF/AWAY/ARR/DEP, and Remove
		if(count($this->away)<=0)
			$this->load_away($con);
		if(count($this->here)<=0)
			$this->load_here($con);
		$is_here = $this->is_here_v2($start_date,$end_date);
		foreach($date as $d){
			if(isset($is_here[$d]) && !$is_here[$d] && isset($raw_av[$d])){
				foreach($raw_av[$d] as &$rav)
					$rav['pref'] = 0;
			}
		}
		$this->av_raw = $raw_av;
	}
	// SETTINGS/TEST FUNCTIONS
	public function set_name($name_type = false){
		if($name_type !== false)
			$this->name_type = $name_type;
		switch($this->name_type){
			case NAME_STANDARD:
				$this->name = $this->first_name . ' ' . $this->last_name;
				break;
			case NAME_INITIALS:
				$this->name = "";
				if($this->first_name && strlen($this->first_name) > 0)
				$this->name = $this->first_name[0] . ". ";
				if($this->last_name && strlen($this->last_name) > 0)
				$this->name .= $this->last_name[0] . ".";
				break;
			case NAME_FIRST_L:
				$this->name = $this->first_name;
				if($this->last_name && strlen($this->last_name) > 0)
				$this->name .= ' ' . $this->last_name[0] . ".";
				break;
			case NAME_F_LAST:
				$this->name = "";
				if($this->first_name && strlen($this->first_name) > 0)
				$this->name = $this->first_name[0] . ". " . $this->last_name;
				break;
			case NAME_FIRST:
				$this->name = $this->first_name;
				break;
			case NAME_LAST:
				$this->name = $this->last_name;
				break;
			default:
				$this->name = "default";
				break;
		}
	}
	public function show(){ // print_r's the whole class
		print_r($this);
		echo "<br/>";
	}

	// CHECKING FUNCTIONS
	public function is_here($start_date, $end_date){
		if(count($this->here) == 0)
			return true;
		foreach($this->here as $h){
			if(($h['start_date']==NULL_DATE || $end_date >= $h['start_date'])&&($h['end_date']==NULL_DATE || $start_date <= $h['end_date']))
				return true;
		}
		return false; // was $here. Any reason for that?
	}
	public function is_here_not_away($start_date, $end_date){
		if(!$this->is_here($start_date, $end_date))
			return false;
		else{
			foreach($this->away as $a)
				if($a['start_date'] <= $start_date && $a['end_date'] >= $end_date)
					return false;
		}
		return true;
	}
	public function is_here_v2($start_date, $end_date){ // returns array [date]=true/false checks both here & away
		$date = datestr_array_start_end($start_date, $end_date);
		$return = array();
		foreach($date as $d){
			$return[$d] = true;
			foreach($this->here as $h){
				if($h['start_date'] > $d || $h['end_date'] < $d)
					$return[$d] = false;
			}
			foreach($this->away as $a){
				if($a['start_date'] <= $d && $a['end_date'] >= $d)
					$return[$d] = false;
			}
		}
		return $return;
	}
	public function get_day_off($date){ //gets day off in the given week
		$week_start = start_of_week($date);
		$week_end = datestr_shift($week_start, 6);
		foreach($this->away as $a){
			if($a['type'] == AWAY_DAY_OFF && $a['start_date'] <= $week_end && $a['end_date'] >= $week_start)
				return $a['start_date'];
		}
		return NULL_DATE;
	}
	public function has_pos($pos_id){
		if(!is_array($pos_id)){
			foreach($this->pos as $p)
				if($p['pos_id'] == $pos_id)
					return true;
		}
		else{
			foreach($pos_id as $p){
				if($this->has_pos($p))
					return true;
			}
		}
		return false;
	}
	public function is_av($con,$date,$shift_id){
		//
		$sql = "SELECT `pref` FROM `staff_av` WHERE `staff_id`='{$this->staff_id}' AND `date`='{$date}' AND `shift_id`='{$shift_id}'";
		$query = new rQuery($con, $sql, false,false, true);
		$results = $query->run();
		if($results && is_array($results) && count($results) > 0)
			return $results[0]['pref'];
		else{
			$day = datestr_format($date, DATESTR_FORMAT_DAY) + 1;
			$sql = "SELECT `pref` FROM `templates_av` WHERE `staff_id`='{$this->staff_id}' AND `day`='{$day}' AND `shift_id`='{$shift_id}'";
			$query = new rQuery($con, $sql, false,false, true);
			$results = $query->run();
			if($results && is_array($results) && count($results) > 0)
				return $results[0]['pref'];
			else{
				$sql = "SELECT `pref` FROM `templates_av` WHERE `type`='{$this->type}' AND `day`='{$day}' AND `shift_id`='{$shift_id}'";
				$query = new rQuery($con, $sql, false,false, true);
				$results = $query->run();
				if($results && is_array($results) && count($results) > 0)
					return $results[0]['pref'];
			}
		}
		
		return false;
	}
	
	// ADD FUNCTIONS
	public function add_lang($lang_id){
		array_push($this->lang, $lang_id);
	}
	public function add_pos($pos_id){
		$pos = array('staff_id' => false, 'pos_id' => $pos_id);
		array_push($this->pos, $pos);
		// [pos] => Array ( [2] => Array ( [staff_id] => 1 [pos_id] => 2 [skill] => 5 [pref] => 5 [min] => 4 [max] => 8 [training_hours] => 0 [training_start_date] => 0000-00-00 [combo] => 0 ) )
	}
	public function add_av_temp($av){
		if(is_object($this->av_temp)){ // can be input as either an array OR as a staffAV object
			$this->av_temp = array();
		}
		if(!is_object($av)){ // it's an array, so parse it
			$av_man = new dTemplateAv(null);
			$av = $av_man->make_av($av);
		}
		array_push($this->av_temp, $av);
	}
	public function add_av_inst($av){
		if(is_object($this->av)){ // can be input as either an array OR as a staffAV object
			$this->av = array();
		}
		if(!is_object($av)){ // it's an array, so parse it
			$av_man = new dTemplateAv(null);
			$av = $av_man->make_av($av);
		}
		array_push($this->av, $av);
	}
	
	// DATABASE FUNCTIONS
	public function update($con){
		$field = array('first_name','last_name','type','partner','gender','email','email2','phone','phone2','mailing_address','birthday');
		$value = array($this->first_name, $this->last_name, $this->type, $this->partner, $this->gender, $this->email, $this->email2, $this->phone, $this->phone2, $this->mailing_address, $this->birthday);
		return db_update($con,'staff',$field,$value,'staff_id',$this->staff_id);
	}
	public function insert($con){
		$field = array('first_name','last_name','type','partner','gender','email','email2','phone','phone2','mailing_address','birthday');
		$value = array($this->first_name, $this->last_name, $this->type, $this->partner, $this->gender, $this->email, $this->email2, $this->phone, $this->phone2, $this->mailing_address, $this->birthday);
		if(!db_insert($con,'staff',$field,$value))
			return false;
		$sql = "SELECT `staff_id` FROM staff WHERE first_name = ? AND last_name = ? and type = ? AND gender = ? ORDER BY staff_id DESC LIMIT 1";
		$q = new rQuery($con, $sql, "ssss", array($this->first_name, $this->last_name, $this->type, $this->gender));
		$id = $q->run();
		$this->staff_id = $id['staff_id'];
		return $this->staff_id;
	}
	public function delete($con){
		return db_delete($con,'staff', 'staff_id', $this->staff_id);
	}
	
	public function update_lang($con){
		db_delete($con, 'staff_lang','staff_id',$this->staff_id);
		$i = 0;
		foreach($this->lang as $l){
			$field = array('staff_id','lang_id');
			$value = array($this->staff_id, $l);
			if(db_insert($con,'staff_lang',$field,$value))
				$i++;
		}
		if($i == count($this->lang))
			return true;
		else
			return false;
	}
	public function update_pos($con){
	// [pos] => Array ( [2] => Array ( [staff_id] => 1 [pos_id] => 2 [skill] => 5 [pref] => 5 [min] => 4 [max] => 8 [training_hours] => 0 [training_start_date] => 0000-00-00 [combo] => 0 ) )
		db_delete($con, 'staff_pos','staff_id',$this->staff_id);
		$i = 0;
		foreach($this->pos as $p){
			$field = array('staff_id','pos_id','skill','pref','min','max','training_hours','training_start_date','combo');
			$value = array();
			foreach($field as $f){
				if (isset($p[$f]) && $f != 'staff_id'){
					array_push($value, $p[$f]);
				}
				elseif($f == 'staff_id'){
					if($p[$f])
						array_push($value,$p[$f]);
					else
						array_push($value, $this->staff_id);
				}
				else
					array_push($value, '');
			}
			if(db_insert($con,'staff_pos',$field,$value))
				$i++;
		}
		if($i == count($this->pos))
			return true;
		else
			return false;
	}
	public function update_contract($con){
		db_delete($con, 'staff_contract','staff_id',$this->staff_id);
		$field = array('staff_id','min','max','pref');
		$value = array($this->staff_id, $this->contract['min'], $this->contract['max'], $this->contract['pref']);
		db_insert($con, 'staff_contract',$field,$value);
	}
	public function update_here($con){
		$field = array('staff_id', 'type');
		$value = array($this->staff_id,AWAY_ARRDEP);
		db_delete($con, 'staff_away',$field,$value);
		$i=0;
		foreach($this->here as $h){
			$field = array('staff_id','start_date','end_date','type');
			$value = array($this->staff_id, $h['start_date'], $h['end_date'], $h['type']);
			if(db_insert($con,'staff_away',$field,$value))
				$i++;
		}
		if($i == count($this->here))
			return true;
		else
			return false;
	}
	public function update_away($con){ // DOESN'T UPDATE DAYS OFF, THAT'S DONE MANUALLY
	//	db_delete($con, 'staff_away','staff_id',$this->staff_id);
		$sql = "DELETE FROM `staff_away` WHERE `staff_id` = ? AND `type` != ? AND `type` != ?";
		$query = new rQuery($con, $sql, "sss", array((string)$this->staff_id, (string)AWAY_ARRDEP, (string)AWAY_DAY_OFF));
		$query->run();
		$i=0;
		foreach($this->away as $a){
			$field = array('staff_id','start_date','end_date','type');
			$value = array($this->staff_id, $a['start_date'], $a['end_date'], $a['type']);
			if(db_insert($con,'staff_away',$field,$value))
				$i++;
		}
		if($i == count($this->away))
			return true;
		else
			return false;
	}
	public function update_av($con, $start_date = null, $end_date = null){
		// AV_TEMPLATES
		if(!is_object($this->av_temp)){
			// DELETE all the others from the table
			$where_field = array('staff_id','name');
			$where_value = array($this->staff_id, TEMP_DEFAULT);
			db_delete($con,'templates_av', $where_field, $where_value, $debug = false);
			// ADD THE SAVED ONES
			if(count($this->av_temp) > 0)
			foreach($this->av_temp as $av){
				$field = array('staff_id','type','name','day','shift_id','pos_id','pref','fixed');
				$value = array($this->staff_id, 0, TEMP_DEFAULT, $av->day, $av->shift_id, $av->pos_id, $av->pref, $av->fixed);
				db_delete($con, 'templates_av',$field,$value); // To eliminate any instances of multiple entry
				db_insert($con,'templates_av',$field,$value);
			}
		}
		if(is_array($this->av) && $start_date && $end_date){
			// DELETE ALL THE OLD ONES IN THE TABLE
			$sql = "DELETE FROM `staff_av` WHERE `staff_id` = '{$this->staff_id}' AND `date` >= '{$start_date}' AND `date` <= '{$end_date}' AND `raw`='0'";
			$query = new rQuery($con, $sql, false,false, true);
			$query->run();
			
			// ADD THE SAVED ONES 
			if(count($this->av) > 0)
			foreach($this->av as $av){
				$field = array('date','staff_id','shift_id','pos_id','pref','fixed');
				$value = array($av->date, $this->staff_id, $av->shift_id, $av->pos_id, $av->pref, $av->fixed);
				db_delete($con,'staff_av',$field,$value); // To eliminate any instances of multiple entry
				db_insert($con,'staff_av',$field,$value);
			}
		}
	}
	
	// FORM FUNCTIONS

	public function form_av_inst_default($posLib){
		$ord = $posLib->ord;
		$default_content = "
		<div class='av_sched_inst' data-save=0>
			<div class='av_pref_slider' data-value='0'></div>
			<select class='av_pos'><option value='0'></option>";
		foreach($ord as $o){
			if(isset($this->pos[$o['pos_id']]))
				$default_content .= "<option value='{$o['pos_id']}'>{$posLib->pos[$o['pos_id']]->abbr}</option>";
		}
		$default_content .= "</select>
			<div class='av_fixed_buttons'>
				<button class='noselect' data-value=1>Fix</button>
				<!--<button class='select' data-value=0>Flex</button>-->
			</div>
		</div>";
		return $default_content;
	}
	public function form_av_inst_content($posLib){
		$ord = $posLib->ord;
		$content = new CalContentLib();
		foreach($this->av as $a){
			//<button class='toggle_av'>X</button>
			$data =	"<div class='av_sched_inst' data-save=1>
				<div class='av_pref_slider' data-value='{$a->pref}'></div>
				<select class='av_pos'><option value='0'></option>";
			foreach($ord as $o){
				if(isset($this->pos[$o['pos_id']])){
					$selected = '';
					if($a->pos_id == $o['pos_id']) $selected = ' selected';
					$data .= "<option value='{$o['pos_id']}'$selected>{$posLib->pos[$o['pos_id']]->abbr}</option>";
				}
			}
			$data .= "</select>
				<div class='av_fixed_buttons'>";
			$fix_select = 'noselect';
			$flex_select = 'noselect';
			if($a->fixed) $fix_select = 'select';
			else $flex_select = 'select';
			$data .= "<button class='{$fix_select}' data-value=1>Fix</button>
					<!--<button class='{$flex_select}' data-value=0>Flex</button>-->
				</div>
			</div>";
			$content->add_content($data, $a->shift_id,$a->date);
		}
		return $content;
	}
	public function form_av_temp_default($posLib){ // currently just calling the inst_default.
		return $this->form_av_inst_default($posLib);
	}
	public function form_av_temp_content($posLib){
		$ord = $posLib->ord;
		$content = new CalContentLib();
		foreach($this->av_temp->av as $a){
			//<button class='toggle_av'>X</button>
			$data =	"<div class='av_sched_inst' data-save=1>
				<div class='av_pref_slider' data-value='{$a->pref}'></div>
				<select class='av_pos'><option value='0'></option>";
			foreach($ord as $o){
				if(isset($this->pos[$o['pos_id']])){
					$selected = '';
					if($a->pos_id == $o['pos_id']) $selected = ' selected';
					$data .= "<option value='{$o['pos_id']}'$selected>{$posLib->pos[$o['pos_id']]->abbr}</option>";
				}
			}
			$data .= "</select>
				<div class='av_fixed_buttons'>";
			$fix_select = 'noselect';
			$flex_select = 'noselect';
			if($a->fixed) $fix_select = 'select';
			else $flex_select = 'select';
			$data .= "<button class='{$fix_select}' data-value=1>Fix</button>
					<!--<button class='{$flex_select}' data-value=0>Flex</button>-->
				</div>
			</div>";
			$content->add_content($data, $a->shift_id,$a->day);
		}
		return $content;
	}	
	
	public function form_details($con){
		$type_vol = '';
		$type_emp = '';
		if($this->type == 1) $type_vol = ' checked';
		elseif ($this->type == 2) $type_emp = ' checked';
		
		$gender_male = '';
		$gender_female = '';
		if($this->gender == MALE) $gender_male = ' checked';
		if($this->gender == FEMALE) $gender_female = ' checked';
		
		echo "
			First Name: <input type='text' class='input_staff_first_name' value='{$this->first_name}'><br/>
			Last Name: <input type='text' class='input_staff_last_name' value='{$this->last_name}'><br/>
			Type:
			<input type='radio' class='input_staff_type' name='input_staff_type' value='1'$type_vol>Volunteer 
			<input type='radio' class='input_staff_type' name='input_staff_type' value='2'$type_emp>Employee
			<br/>
			Partner:<select class='input_staff_partner'>";
			show_partner_select($con, $this->partner);
		echo "</select><br/>
			Gender:
			<input type='radio' class='input_staff_gender' name='input_staff_gender' value='1'$gender_male>Male 
			<input type='radio' class='input_staff_gender' name='input_staff_gender' value='2'$gender_female>Female
			<br/>
			Email: <input type='text' class='input_staff_email' value='{$this->email}'><br/>
			Email2: <input type='text' class='input_staff_email2' value='{$this->email2}'><br/>
			Phone: <input type='text' class='input_staff_phone' value='{$this->phone}'><br/>
			Phone2: <input type='text' class='input_staff_phone2' value='{$this->phone2}'><br/>
			Mailing Address: <input type='text' class='input_staff_mailing_address' value='{$this->mailing_address}'><br/>
			Birthday: <input type='date' class='input_staff_birthday' value='{$this->birthday}'><br/>";
	}
	public function form_av($con){ 
		require_once('../includes/calendar.php');
		$posLib = new dPosLib($con);
		$ord = $posLib->ord;
		
		{// AV OVERVIEW -- UNFINISHED!
		$av_start = '2014-01-01';
		$av_end = '2014-12-31';
		$this->make_av_raw($con,'2014-01-01', '2014-12-31');
		$ov = '';
		}
		{// AV INSTANCES
		$content = $this->form_av_inst_content($posLib);
		$cal_inst = new Calendar($con, $datestr = false, $type = CAL_TYPE_WEEK, $id = 'calendar_av_inst', $class = 'calendar_standard', $title = "Availability Exceptions", $row1_type = CAL_SHIFT, $row2_type = null, $col1_type = CAL_DATE, $col2_type = null, $title_suffix = false, $datestr_format = "D jS", $content);
		$cal_inst->default_content = $this->form_av_inst_default($posLib);
		}
		{// AV TEMPLATES
		$content = $this->form_av_temp_content($posLib);
		
		$cal_temp = new Calendar($con, $datestr = false, $type = CAL_TYPE_WEEK, $id = 'calendar_av_temp', $class = 'calendar_standard', $title = "Shift Templates", $row1_type = CAL_SHIFT, $row2_type = null, $col1_type = CAL_DAY, $col2_type = null, $title_suffix = false, $datestr_format = "D jS", $content);
		$cal_temp->date_select_buttons = false;
		$cal_temp->date_select_input = false;
		$cal_temp->default_content =  $this->form_av_temp_default($posLib); 
		}
		
		// PRINTING IT
		echo "<ul>
			<li><a href='#tabs-av_ov'>AV Overview</a></li>
			<li><a href='#tabs-av_temp'>AV Regular</a></li>
			<li><a href='#tabs-av_inst'>AV Exceptions</a></li>

		</ul>
		<div id='tabs-av_ov'>";
		//print_r($this->av_raw);
		echo "Still in progress.";
		echo "</div>";
		echo "<div id='tabs-av_temp'>";
		$cal_temp->init();
		echo "</div>";
		echo "<div id='tabs-av_inst'>";
		$cal_inst->init();
		echo "</div>";
		
	}
	public function form_av_new($con,$date = null){
		echo "
		<button id='save_avn'>Save</button>
		<ul>
			<li><a href='#tabs-avn_ov'>Overview</a></li>
			<li><a href='#tabs-avn_temp'>Templates</a></li>
		</ul>";
		
		echo "<div id='tabs-avn_ov'>";
		$this->form_avn_ov($con,$date);
		echo "</div>";
		echo "<div id='tabs-avn_temp'>";
		$this->form_avn_temp($con);
		echo "</div>";
	}
	
	public function data_av_new($con,$date = null){
		$data = array();
		
		// WHAT ALL DO WE NEED FROM HERE ANYWAYS? POSITION NAMES... SHIFT NAMES & ABBR....
		// Pos
		$sql = "SELECT `pos_id`,`name`,`abbr` FROM `data_pos` ORDER BY `order` ASC";
		$query = new rQuery($con,$sql,false,false,true);
		$data['pos'] = $query->run(false,3);
		
		// Shifts
		$sql = "SELECT `shift_id`,`name`,`abbr` FROM `data_shift` ORDER BY `order` ASC";
		$query = new rQuery($con,$sql,false,false,true);
		$data['shift'] = $query->run(false,3);
		
		// OTHER
		$data['pref'] = array(
			0 => "Unavailable",
			1 => "Unpreferred",
			2 => "Normal",
			3 => "Preferred"
		);
		
		
		echo json_encode($data);
	}
	
	public function form_here($con, $arr_buffer = 0, $dep_buffer = 0){
		echo "<table class='staff_here'>
			<tr><th colspan=3>Arrivals/Departures</th></tr>
			<tr><th>Arrival</th><th>Departure</th><th>Remove</th></tr>";
		
		foreach($this->here as $h){
			$start_date = datestr_shift($h['start_date'],-$arr_buffer);
			$end_date = datestr_shift($h['end_date'],$dep_buffer);
			echo "<tr class='here_inst' data-save='1'>
				<td><input class='here_start_date' type='date' value='{$start_date}'></td>
				<td><input class='here_end_date' type='date' value='{$end_date}'></td>
				<td class='table_button remove' onclick=here_del(this);>X</td>
				</tr>";
		}
		echo "</table><button onclick='here_add(this)'>Add an Arr/Dep</button>";
	}
	public function form_away($con){ 
		echo "<table class='staff_away'>
			<tr><th colspan=4>Time Off & Vacations</th></tr>
			<tr><th>Dep Date</th><th>Return Date</th><th>Type</th><th>Remove</th></tr>";
		foreach($this->away as $a){
			if($a['type'] != AWAY_DAY_OFF && $a['type'] != AWAY_ARRDEP){
				$sel_vac = ''; $sel_other = '';
				if($a['type'] == AWAY_VAC) $sel_vac = ' selected ';
				elseif($a['type'] == AWAY_OTHER) $sel_other = ' selected ';
				echo "<tr class='away_inst' data-save='1'>
					<td><input class='away_start_date' type='date' value='{$a['start_date']}'></td>
					<td><input class='away_end_date' type='date' value='{$a['end_date']}'></td>
					<td><select class='away_type'>
						<option value='" . AWAY_VAC . "'{$sel_vac}>Vacation</option>
						<option value='" . AWAY_OTHER . "'{$sel_other}>Other</option>
					</select></td>
					<td class='table_button remove' onclick=away_del(this);>X</td>
					</tr>";
			}
		}
		echo "</table><button onclick='away_add(this)'>Schedule Time Off</button>";
	}
	public function form_hereaway($con){// UNSTARTED& being phazed out.
		echo "BEING PHAZED OUT! staff_classes.php function form_hereaway($con)!<br/>";
	}
	public function form_sched($con){
		echo "<p><i>This will have more information in the future, however for the moment it only has the kill from schedule button.</i></p>";
		echo "<p>Start Date: <input id='remove_shifts_from' type='date' value='".datestr_cur()."'></p>
			<p>End Date: <input id='remove_shifts_to' type='date'value='".datestr_cur()."'/></p><br/>
			<button onclick=remove_all_shifts();>Remove From All Shifts</button>";
	}
	public function form_lang($con, $lang_lib=null){
		if(!$lang_lib)
			$lang_lib = new dLangLib($con);
		echo "<table class='staff_lang'>
		<tr><th colspan='2'>Languages</th></tr>";
		foreach($this->lang as $l){
			if(isset($lang_lib->lang[$l]))
				echo "<tr><td class='lang_inst' data-lang_id='{$l}' data-save='1'>{$lang_lib->lang[$l]->name}</td><td class='table_button remove' onclick=lang_del(this);>X</td></tr>";
		}
		
		echo "<tr class='divider'><th colspan='2'>Add a Language</th></tr>
		<tr><td class='lang_add'><select><option value=''></option>";
		foreach($lang_lib->lang as $l){
			$found = false;
			foreach($this->lang as $t){
				if($t == $l->lang_id)
					$found = true;
			}
			if(!$found)
				echo "<option value='{$l->lang_id}'>{$l->name}</option>";
		}
		echo "</select></td><td class='table_button add' onclick=lang_add(this);>+</td></tr>
		</table>";
	}
	public function form_pos($con, $pos_lib=null){// Finished, but untested with the object controller
		if(!$pos_lib)
			$pos_lib = new dPosLib($con);
		
		echo "<table class='staff_pos'>
		<div class='pos_con'>
		<h4>Weekly Contract</h4>
		Min:<input class='pos_con_min' type='number' value={$this->contract['min']}>
		Max:<input class='pos_con_max' type='number' value={$this->contract['max']}>
		Pref:<input class='pos_con_pref' type='number' value={$this->contract['pref']}><br/>
		</div>
		<tr><th>Position</th><th>Pref</th><th>Min</th><th>Max</th><th>Train. Hours</th><th>Train. Start Date</th><th>Skill</th><th></th></tr>";
		foreach($this->pos as $i=>$p){
			if(isset($pos_lib->pos[$i]))
				echo "<tr><td class='pos_inst' data-pos_id='{$i}' data-save='1'>{$pos_lib->pos[$i]->name}</td>
					<td><input class='pos_pref' type='number' value='{$p['pref']}'></td>
					<td><input class='pos_min' type='number' value='{$p['min']}'></td>
					<td><input class='pos_max' type='number' value='{$p['max']}'></td>
					<td><input class='pos_training_hours' type='number' value='{$p['training_hours']}'></td>
					<td><input class='pos_training_start_date' type='date' value='{$p['training_start_date']}'></td>
					<td><input class='pos_skill' type='number' value='{$p['skill']}'></td>
					<td class='table_button remove' onclick=pos_del(this);>X</td></tr>";
		}
		
		echo "<tr class='divider'><th colspan='2'>Add a Position:</th></tr>
		<tr><td class='pos_add'><select><option value=''></option>";
		foreach($pos_lib->pos as $p){
			$found = false;
			foreach($this->pos as $i=>$t){
				if($i == $p->pos_id)
					$found = true;
			}
			if(!$found)
				echo "<option value='{$p->pos_id}'>{$p->name}</option>";
		}
		echo "</select></td><td class='table_button add' onclick=pos_add(this);>+</td></tr>
		</table>";
	/*	This was here before, not sure why. Don't delete until you've checked it with the object controller again.
		
		if(!$pos_lib)
			$pos_lib = new dPosLib($con);
		
		echo "<div class='oc_inside acc'>";
		$tab_index = 0;
		foreach($this->pos as $p){
			echo "<h3>{$pos_lib->pos[$p['pos_id']]->name}</h3>";
			echo "<div id='oc_staff_pos_{$tab_index}'>";
			echo "{$pos_lib->pos[$p['pos_id']]->name}:<br/>";
			echo "Skill:<input type='number' class='input_number' value='{$p['skill']}'><br/>
			Pref:<input type='number' class='input_number' value='{$p['pref']}'>
			Min:<input type='number' class='input_number' value='{$p['min']}'> 
			Max:<input type='number' class='input_number' value='{$p['max']}'><br/>";
			echo "Training Hours:<input type='number' class='input_number' value='{$p['training_hours']}'><br/>
			Train. Start:<input type='date' value='{$p['training_start_date']}><br/>";
			echo "Combo:<input type='number' class='input_number' value='{$p['combo']}'>";
			echo "</div>";
		}
		echo "<h3>Add Position</h3>
		<div id='oc_staff_pos_add'>This will be updated someday.</div>
		</div>";
	*/
	}

	
	// Sub Forms:
	public function form_avn_ov($con,$date = null){
		if(!$date)
			$date = standard_date_val();
		$week = week_month_array_accurate($date);
		$this->set_avn($con,$week[0][0],$week[count($week)-1][6]);
		
		//print_r($this->avn);
		
		$config = new conFig();
		$config->day_init();
		$active_day = $config->day_active_array();
		
		$shifts = array();
		$sql = "SELECT `shift_id`,`abbr` FROM `data_shift`";
		$query = new rQuery($con,$sql,false,false,true);
		$shifts = $query->run(false, 2);
		
		$this->av_editor($con);
		
		echo "<table class='av_overview' data-start_date='".$week[0][0]."' data-end_date='".$week[count($week)-1][6]."' data-month_start='".start_of_month($date)."' data-month_end='".end_of_month($date)."'><thead><tr>";
		echo "<th colspan=".count($active_day).">";
		echo "<button class='float_left push_date' data-amount=-".datestr_format($date,'t')."> <-- </button>";
		echo "<button class='float_right push_date' data-amount=".datestr_format($date,'t')."> --> </button>";
		echo "<input type='date' class='float_right push_date' value='{$date}'/>";
		echo datestr_format($date,"F Y");
		echo "</th></tr><tr>";
		foreach($active_day as $ad)
			echo "<th>{$ad['name']}</th>";
		echo "</tr>";
		echo "<tbody>";
		foreach($week as $w){
			echo "<tr>";
			foreach($active_day as $ad){
				$day = $w[$ad['id']-1];
				echo "<td data-date='{$day}'><div class='day_div'>" . datestr_format($day,"d") . "</div>";
				foreach($shifts as $s){
					$pref = 0;
					$pos = 0;
					$type = 'type';
					$orig_pos = '';
					$orig_pref = '';
					$orig_type = '';
					if(isset($this->avn[$day]) && isset($this->avn[$day][$s['shift_id']])){
						(isset($this->avn[$day][$s['shift_id']]['pref']))?$pref = $this->avn[$day][$s['shift_id']]['pref']:$pref=0;
						(isset($this->avn[$day][$s['shift_id']]['pos_id']))?$pos = $this->avn[$day][$s['shift_id']]['pos_id']:$pos=0;
						(isset($this->avn[$day][$s['shift_id']]['type']))?$type = $this->avn[$day][$s['shift_id']]['type']:$type='type';
						if($type=='ex'){
							$o_pos = 0;
							$o_pref = 0;
							$o_type = 'type';
							if(isset($this->avn_pre_ex[$day]) && isset($this->avn_pre_ex[$day][$s['shift_id']])){
								(isset($this->avn_pre_ex[$day][$s['shift_id']]['pref']))?$o_pref = $this->avn_pre_ex[$day][$s['shift_id']]['pref']:$o_pref=0;
								(isset($this->avn_pre_ex[$day][$s['shift_id']]['pos_id']))?$o_pos = $this->avn_pre_ex[$day][$s['shift_id']]['pos_id']:$o_pos=0;
								(isset($this->avn_pre_ex[$day][$s['shift_id']]['type']))?$o_type = $this->avn_pre_ex[$day][$s['shift_id']]['type']:$o_type='type';
							}
							$orig_pos = " data-pos_id-orig='{$o_pos}'";
							$orig_pref = " data-pref-orig='{$o_pref}'";
							$orig_type = " data-type-orig='{$o_type}'";
						}
					}
					$class = "av" . $pref;
					echo "<div class='shift_av {$class}' data-shift_id='{$s['shift_id']}' data-type='{$type}' data-pref='{$pref}'{$orig_pref}{$orig_type}><span class='abbr'>{$s['abbr']}:</span><span class='pos_disp' data-pos_id={$pos}{$orig_pos}>$pos</span></div>";
				}
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</tbody></table>";
	}
	public function form_avn_temp($con,$refresh = false){
		if((count($this->avn_temp)==0 && count($this->avn_temp)==0)|| $refresh){
			$this->load_avn($con);
		}
		
		$config = new conFig();
		$config->day_init();
		$active_day = $config->day_active_array();
		
		$sql = "SELECT `shift_id`,`abbr` FROM `data_shift`";
		$query = new rQuery($con,$sql,false,false,true);
		$shift = $query->run(false, 2);
		
		// DEFAULT
		$default = array();
		if(count($this->avn_temp) > 0)
		foreach($this->avn_temp as $a){
			if($a['start_date'] == NULL_DATE && $a['end_date'] == NULL_DATE)
				array_push($default,$a);
		}
		// SEGMENTS
		$segment = array();
		if(count($this->avn_temp) > 0)
		foreach($this->avn_temp as $a){
			if($a['end_date'] != NULL_DATE){
				if(isset($segment[$a['end_date']]))
					array_push($segment[$a['end_date']],$a);
				else
					$segment[$a['end_date']] = array($a);
			}
		}
		
		// DEFAULT, BREAK UP BY DATE TEMPLATES, NEW.
		echo"<select id='av_temp_select'>
			<option value='default'>Default</option>
			<option value='new'>New</option>";
		if(count($segment)>0) 
		foreach($segment as $s){
			echo "<option value='{$s[0]['end_date']}' data-start_date='{$s[0]['start_date']}' data-end_date='{$s[0]['end_date']}'>{$s[0]['start_date']} til {$s[0]['end_date']}</option>";
		}
		echo "</select>
		<div class='controls'>
		Start Date:<input type='date' id='av_temp_start_date'/> &nbsp; End Date:<input type='date' id='av_temp_end_date'/> &nbsp; <button id='delete_av_temp'>Delete</button>
		</div>
		<table class='av_temp_calendar'><tr>";
		foreach($active_day as $d){
			echo "<td day='{$d['id']}'><div class='day_name' day='{$d['id']}'>{$d['abbr']}</div>";
			foreach($shift as $s){
				"<div class='shift_av'></div>";
				echo "<div class='shift_av' data-shift_id='{$s['shift_id']}'><span class='abbr'>{$s['abbr']}:</span><span class='pos_disp' data-pos_id=0></span></div>";
			}
			echo "</td>";
		}
		echo "</tr></table>";
		
		$this->av_editor($con,false);
		
		echo "<div id='av_temp_lib'>";
		if(count($default)>0)
		foreach($default as $d){
			echo "<div class='av_temp_inst' data-day='{$d['day']}' data-shift_id='{$d['shift_id']}' data-pos_id='{$d['pos_id']}'  data-date='default' data-pref='{$d['pref']}'></div>";
		}
		if(count($segment)>0)
		foreach($segment as $s){
			foreach($s as $d){
				echo "<div class='av_temp_inst' data-day='{$d['day']}' data-shift_id='{$d['shift_id']}' data-pos_id='{$d['pos_id']}'  data-date='{$d['end_date']}' data-pref='{$d['pref']}' data-start_date='{$d['start_date']}' data-end_date='{$d['end_date']}'></div>";
			}
		}
		foreach($this->avn_type as $a){
			echo "<div class='av_temp_inst' data-day='{$a['day']}' data-shift_id='{$a['shift_id']}' data-pos_id='{$a['pos_id']}'  data-date='type' data-pref='{$a['pref']}' data-start_date='{$a['start_date']}' data-end_date='{$a['end_date']}'></div>";
		}
		echo "</div>";
	}
	public function form_avn_ex($con,$refresh = false){
			if((count($this->avn_ex)==0 && count($this->avn)==0)|| $refresh){
			$this->load_avn($con);
		}
		
		echo "<table id='av_ex'><tr><th>Date</th><th>Preference</th><th>Fixed Position</th><th>Remove</th></tr>";
		foreach($this->avn_ex as $e){
			echo "<tr><td>{$e['end_date']}</td>";
			switch($e['pref']){
				case 0: echo "<td class='av0'>Unavailable</td>";break;
				case 1: echo "<td class='av1'>Unpreferred</td>";break;
				case 2: echo "<td class='av2'>Normal</td>";break;
				case 3: echo "<td class='av3'>Preferred</td>";break;
			}
			echo "</td><td>{$e['pos_id']}</td><td><button onclick=removeAvEx(this);>X</button></td></tr>";
		}
		echo "</table>";
	}
	
	public function av_editor($con,$exception = true, $classes = true){
		$posLib = new dPosLib($con, $auto_load = true, $group = null);
		//print_r($this->pos);
		
		echo "<div class='av_editor'>";
		if($classes){
			$shifts = new dShiftLib($con);
			echo "<div class='shift_selector'>";
			foreach($shifts->par as $s){
				echo "<input class='av_shift_toggle' type='checkbox' value={$s->shift_id} checked/>{$s->abbr}";
			}
			echo "</div>";
			
		}
		echo "<div class='pref'>Preference: <select class='av_pref_select'>
			<option class='av0' value='0'>Unavailable</option>
			<option class='av1' value='1'>Unpreferred</option>
			<option class='av2' value='2'>Normal</option>
			<option class='av3' value='3'>Preferred</option>
		</select></div>";
		echo "<div class='pos'>Position: <select class='av_pos_select'><option value='0'>None</option>";
		if(count($this->pos)>0)
		foreach($this->pos as $p){
			echo "<option value='{$p['pos_id']}'>{$posLib->pos[$p['pos_id']]->name}</option>";;
		}
		echo "</select></div>";
		
		
		if($exception)
			echo "<div><button class='remove_ex hidden'>Remove Exception</button></div>";
		else
			echo "<div><button class='remove_temp hidden'>Revert</button></div>";
		
		echo "</div>";
	}
	
	// VIEW FUNCTIONS
	public function view_details($con){
		$type_name = '';
		if($this->type == 1) $type_name="Volunteer";
		elseif($this->type == 2) $type_name="Employee";
		$partner_name = '';
		if($this->partner){
			$part = new staffS($con,$this->partner);
			$partner_name = $part->name;
		}
		$gender_name = '';
		if($this->gender == 1) $gender_name="Male";
		elseif($this->gender == 2) $gender_name="Female";
		echo"
			First Name: {$this->first_name}<br/>
			Last Name: {$this->last_name}<br/>
			Type: {$type_name}<br/>
			Partner: {$partner_name}<br/>
			Gender: {$gender_name}<br/>
			Email: {$this->email}<br/>
			Email2: {$this->email2}<br/>
			Phone: {$this->phone}<br/>
			Phone2: {$this->phone2}<br/>
			Mailing Address: {$this->mailing_address}<br/>
			Birthday: {$this->birthday}<br/>";
	}
	public function view_av($con){ // UNFINISHED
		echo "View AV FROM THE STAFF CLASS!<br/>";
	}
	public function view_hereaway($con){// UNFINISHED
		echo "View AWAY FROM THE STAFF CLASS!<br/>";
	}
	public function view_lang($con, $lang_lib=null){
		if(!$lang_lib)
			$lang_lib = new dLangLib($con);
			
		echo "<ul>";
		foreach($this->lang as $l)
			echo "<li>{$lang_lib->lang[$l]->name}</li>";
		echo "</ul>";
	}
	public function view_pos($con, $pos_lib=null){
		if(!$pos_lib)
			$pos_lib = new dPosLib($con);
		
		echo "<table class='staff_pos'>
		<tr><th colspan='7'>Positions:</th></tr>
		<tr><th>Position</th><th>Pref</th><th>Min</th><th>Max</th><th>Train. Hours</th><th>Train. Start Date</th><th>Skill</th><th></tr>";
		foreach($this->pos as $i=>$p){
			echo "<tr><td>{$pos_lib->pos[$i]->name}</td>
				<td>{$p['pref']}</td>
				<td>{$p['min']}</td>
				<td>{$p['max']}</td>
				<td>{$p['training_hours']}</td>
				<td>{$p['training_start_date']}</td>
				<td>{$p['skill']}</td></tr>";
		}
		
		echo "</table>";
	}

}
class staffM{//($con, $sql=false, $param_type=false, $param=false, $group = null, $available = false) $available = array('start_date'=>$start_date, 'end_date'=>$end_date)
	// NEED AN array of staff members.... plus what? A query for defining how we got them? Or should it just be settings, and then the query locally built?
	public $staff, $con; // $staff=array(new staffS);
	
	
	function __construct($con, $sql=false, $param_type=false, $param=false, $group = null, $available = false){
		$this->con = $con;
		$query = "SELECT `staff`.`staff_id` FROM `staff`";
		$sql = $query . $sql;
		$query = new rQuery($con, $sql, $param_type, $param, true);
		$results = $query->run();
		
		/*
		if($available && is_array($available) && isset($available['start']) && isset($available['end']) && $results && count($results) > 0){
			$staff = array();
			foreach($results as $r)
				array_push($staff,$r['staff_id']);
			$staff_id = implode(',',$staff);
		}
		*/
		$this->staff = array();
		if($results && count($results) > 0)
		foreach($results as $r){
//			array_push($this->staff, new staffS($this->con, $r['staff_id']));
			$st = new staffS($this->con, $r['staff_id']);
			if(!$group || !is_object($group) || $st->has_pos($group->pos))
			if(!$available)
				$this->staff[$r['staff_id']] = $st;
			elseif($available && is_array($available) && isset($available['start_date']) && isset($available['end_date']) && $st->is_here_not_away($available['start_date'], $available['end_date'])){
				$this->staff[$r['staff_id']] = $st;
			}
				
		}
	}
	
	
	public function set_name($name_type = false){
		if($name_type){
			foreach($this->staff as $s){
				$s->set_name($name_type);
			}
			return true;
		}
	}
	public function show(){
		foreach($this->staff as $s){
			print_r($s);
			echo "<br/>";
		}
	}
}

class dGroup{//($con = null, $group_id = null)
	public $group_id;
	public $name,$abbr,$alt1,$alt2;
	public $order,$data; // SHIFT first, POS 2nd - for data
	public $shift = array();
	public $pos = array(); // both arrays containing all of the shifts & positions in it. $array[id] = id is the format.
	
	function __construct($con = null, $group_id = null){
		if($con != null && $group_id != null)
			$this->load($con,$group_id);
		else
			$this->group_id = $group_id;
	}
	
	public function load($con, $group_id = null){
		if($group_id !== null)
			$this->group_id = $group_id;
		if($this->group_id){
			$data = db_select_all($con, 'data_group', 'group_id',$this->group_id, false, $limit = 1);
			if($data){
				$this->group_id = $data['group_id'];
				$this->name = $data['name'];
				$this->abbr = $data['abbr'];
				$this->alt1 = $data['alt1'];
				$this->alt2 = $data['alt2'];
				$this->order = $data['order'];
				$this->data = $data['data'];
				$this->parse();
			}
		}
	}
	public function parse($debug = false){
		$this->shift = array();
		$this->pos = array();
		$data = explode(':',$this->data);
		if(isset($data[0]) && isset($data[1])){
			if($debug) print_r($data);
			$shift = explode(',',$data[0]);
			foreach($shift as $s)
				$this->shift[$s] = $s;			
			
			$pos = explode(',',$data[1]);
			foreach($pos as $s)
				$this->pos[$s] = $s;		
		}
		else{
		
		}
	}
	public function show(){
		echo "Pos:<br/>";
		print_r($this->pos);
		echo "<br/><br/>Shifts:<br/>";
		print_r($this->shift);
		echo "<br/><br/>";
	}

	public function has_shift($shift_id){
		if(count($this->shift) == 0)
			return false;
		foreach($this->shift as $s)
			if($shift_id == $s)
				return true;
		return false;
	}
	public function has_pos($pos_id){
		if(count($this->pos) == 0)
			return false;
		foreach($this->pos as $s)
			if($pos_id == $s)
				return true;
		return false;
	}
	// DATABASE FUNCTIONS
	public function update($con){
		$field = array('name','abbr','alt1','alt2','order','data');
		$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order, $this->data);
		return db_update($con,'data_group',$field,$value,'group_id',$this->group_id);
	}
	public function insert($con){
		$field = array('name','abbr','alt1','alt2','order','data');
		$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order, $this->data);
		$this->group_id = db_insert($con,'data_group',$field,$value,'group_id');
		return $this->group_id;
	}
	
	public function delete($con){
		return db_delete($con,'data_group', 'group_id', $this->group_id);
	}
}
class dGroupLib{//($con = null, $auto_load = true)
	public $Group = array();
	public $ord = array();
	public $con;
	
	function __construct($con = null, $auto_load = true){
		$this->con = $con;
		if($con && $auto_load)
			$this->load();
	}
	function load($con = null, $auto_order = true){
		if($con)
			$this->con = $con;
		if($this->con){
			$this->Group = array();
			$data = db_select($this->con,'data_group','group_id',false,false,true,false,false);
			if($data)
			foreach($data as $d){
				$Group = new dGroup($this->con, $d);
				$this->Group[$Group->group_id] = $Group;
			}
			if($auto_order)
				$this->order();
			return $this->Group;
		}
		else
			return false;
	}
	function order($asc = true){ // false for descending
		if($this->con){
			$this->ord = array();
			$sql = "SELECT group_id FROM data_group ORDER BY `order`";
			($asc) ? $sql .= " ASC": $sql .= " DESC";
			$ord = new rQuery($this->con, $sql, false, false,true);
			$this->ord = $ord->run();
			return $this->ord;
		}
		else
			return false;
	}
	function show($by_order = false){
		if(!$by_order)
			$show = $this->Group;
		else
			$show = $this->get_ordered();
		
		echo "Groups:<br/>";
		foreach($show as $s){
			print_r($s);
			echo "<br/>";
		}
	}
	function get_ordered($con = null){
		if(count($this->ord) <1)
			$this->order($con);
		
		$ret = array();
		
		if($this->ord)
		foreach($this->ord as $o){
			if(isset($this->Group[$o['group_id']]))
				array_push($ret, $this->Group[$o['group_id']]);
			else
				echo "Error: index '$o' not set.";
		}
		
		return $ret;
	}
	
	function order_fix(){
		$this->order();
		$i = 1;
		if($this->ord)
		foreach($this->ord as $o){
			$this->Group[$o['group_id']]->order = $i;
			$this->Group[$o['group_id']]->update($this->con);
			$i++;
		}
	}
	function form(){
		$shift = new dShiftLib($this->con);
		$pos = new dPosLib($this->con);
		
		echo "<table class='info_group'>
			<tr><th colspan=7>Group Form</th></tr>
			<tr><th>Order</th><th>Group</th><th>Abbr</th><th>Alt1</th><th>Alt2</th><th>Data</th><th>Remove</th></tr>";
		if($this->ord)
		foreach($this->ord as $o){
			$g = $this->Group[$o['group_id']];
			echo "<tr class='group_inst' data-group_id='{$g->group_id}' data-changed='no'>
				<td class='table_button order' data-order='{$g->order}' onclick=order_change(this);>{$g->order}</td>
				<td><input type='text' class='input_group_name' value='{$g->name}'></td>
				<td><input type='text' class='input_group_abbr' value='{$g->abbr}'></td>
				<td><input type='text' class='input_group_alt1' value='{$g->alt1}'></td>
				<td><input type='text' class='input_group_alt2' value='{$g->alt2}'></td>";
			// Group Data
			echo "<td class='input_group_data'>
			<button class='group_data_button_shift'>Shifts</button>
			<button class='group_data_button_pos'>Positions</button>
			<div class='group_data_content_shift hidden' data-shown='no'>"; // Shifts
			foreach($shift->ord as $o2){
				$checked = '';
				$s = $shift->par[$o2['shift_id']];
				if($g->has_shift($s->shift_id))
					$checked = ' checked ';
				echo "<input type='checkbox' name='input_group_shift' value='{$s->shift_id}'{$checked}>{$s->name}<br/>";
			}
			echo "</div>
			<div class='group_data_content_pos hidden' data-shown='no'>"; // Position
			foreach($pos->ord as $o2){
				$checked = '';
				$p = $pos->pos[$o2['pos_id']];
				if($g->has_pos($p->pos_id))
					$checked = ' checked ';
				echo "<input type='checkbox' name='input_group_pos' value='{$p->pos_id}'{$checked}>{$p->name}<br/>";
			}
			echo "</div></td>";
			// Remove button
			echo "<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td>
			</tr>";
		}
		echo "</table>";
		echo "<button onclick=group_add()>Add New Group</button>";
	}
}

class dLang{//($con = null, $lang_id = null)
	public $lang_id;
	public $name,$abbr,$alt1,$alt2;
	public $order;
	
	function __construct($con = null, $lang_id = null){
		if($con != null && $lang_id != null)
			$this->load($con,$lang_id);
		else
			$this->lang_id = $lang_id;
	}
	public function load($con, $lang_id = null){
		if($lang_id !== null)
			$this->lang_id = $lang_id;
		if($this->lang_id){
			$data = db_select_all($con, 'data_lang', 'lang_id',$this->lang_id, false, $limit = 1);
			if($data){
				$this->lang_id = $data['lang_id'];
				$this->name = $data['name'];
				$this->abbr = $data['abbr'];
				$this->alt1 = $data['alt1'];
				$this->alt2 = $data['alt2'];
				$this->order = $data['order'];
			}
		}
	}

		// DATABASE FUNCTIONS
	public function update($con){
		$field = array('name','abbr','alt1','alt2','order');
		$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order);
		return db_update($con,'data_lang',$field,$value,'lang_id',$this->lang_id);
	}
	public function insert($con){
		$field = array('name','abbr','alt1','alt2','order');
		$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order);
		$this->lang_id = db_insert($con,'data_lang',$field,$value,'lang_id');
		return $this->lang_id;
	}
	public function delete($con){
		return db_delete($con,'data_lang', 'lang_id', $this->lang_id);
	}
}
class dLangLib{//($con = null, $auto_load = true)
	public $lang = array();
	public $ord = array();
	public $con;
	
	function __construct($con = null, $auto_load = true){
		$this->con = $con;
		if($con && $auto_load)
			$this->load();
	}
	function load($con = null, $auto_order = true){
		if($con)
			$this->con = $con;
		if($this->con){
			$this->lang = array();
			$data = db_select($this->con,'data_lang','lang_id',false,false,true,false,false);
			if($data)
			foreach($data as $d){
				$lang = new dlang($this->con, $d);
				$this->lang[$lang->lang_id] = $lang;
			}
			if($auto_order)
				$this->order();
			return $this->lang;
		}
		else
			return false;
	}
	function order($asc = true){ // false for descending
		if($this->con){
			$this->ord = array();
			$sql = "SELECT lang_id FROM data_lang ORDER BY `order`";
			($asc) ? $sql .= " ASC": $sql .= " DESC";
			$ord = new rQuery($this->con, $sql, false, false,true);
			$this->ord = $ord->run();
			return $this->ord;
		}
		else
			return false;
	}
	function show($by_order = false){
		if(!$by_order)
			$show = $this->lang;
		else
			$show = $this->get_ordered();
		
		echo "langs:<br/>";
		foreach($show as $s){
			print_r($s);
			echo "<br/>";
		}
	}
	function get_ordered($con = null){
		if(count($this->ord) <1)
			$this->order($con);
		
		$ret = array();
		
		if($this->ord)
		foreach($this->ord as $o){
			if(isset($this->lang[$o['lang_id']]))
				array_push($ret, $this->lang[$o['lang_id']]);
			else
				echo "Error: index '$o' not set.";
		}
		
		return $ret;
	}

	function order_fix(){ // need to set this correctly!
		// cycle through the orders
		$this->order();
		$i = 1;
		if($this->ord)
		foreach($this->ord as $o){
			$this->lang[$o['lang_id']]->order = $i;
			$this->lang[$o['lang_id']]->update($this->con);
			$i++;
		}
	}
	function form(){
		echo "<table class='info_lang'>
			<tr><th colspan=6>Language Form</th></tr>
			<tr><th>Order</th><th>Language</th><th>Abbr</th><th>Alt1</th><th>Alt2</th><th>Remove</th></tr>";
		if($this->ord)
		foreach($this->ord as $o){
			$l = $this->lang[$o['lang_id']];
			echo "<tr class='lang_inst' data-lang_id='{$l->lang_id}' data-changed='no'>
				<td class='table_button order' data-order='{$l->order}' onclick=order_change(this);>{$l->order}</td>
				<td><input type='text' class='input_lang_name' value='{$l->name}'></td>
				<td><input type='text' class='input_lang_abbr' value='{$l->abbr}'></td>
				<td><input type='text' class='input_lang_alt1' value='{$l->alt1}'></td>
				<td><input type='text' class='input_lang_alt2' value='{$l->alt2}'></td>
				<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td>
			</tr>";
		}
		echo "</table>";
		echo "<button onclick=lang_add()>Add New Language</button>";
	}
}

class dPos{//($con = null, $pos_id = null)
	public $pos_id;
	public $name,$abbr,$alt1,$alt2;
	public $order,$pref_type,$pref_strength,$pos_type;
	
	function __construct($con = null, $pos_id = null){
		if($con != null && $pos_id != null)
			$this->load($con,$pos_id);
		else
			$this->pos_id = $pos_id;
	}
	
	public function load($con, $pos_id = null){
		if($pos_id !== null)
			$this->pos_id = $pos_id;
		if($this->pos_id){
			$data = db_select_all($con, 'data_pos', 'pos_id',$this->pos_id, false, $limit = 1);
			if($data){
				$this->pos_id = $data['pos_id'];
				$this->name = $data['name'];
				$this->abbr = $data['abbr'];
				$this->alt1 = $data['alt1'];
				$this->alt2 = $data['alt2'];
				$this->order = $data['order'];
				$this->pref_type = $data['pref_type'];
				$this->pref_strength = $data['pref_strength'];
				$this->pos_type = $data['pos_type'];
			}
		}
	}
	
	// DATABASE FUNCTIONS
	public function update($con){
		$field = array('name','abbr','alt1','alt2','order','pref_type','pref_strength','pos_type');
		$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order,$this->pref_type, $this->pref_strength, $this->pos_type);
		return db_update($con,'data_pos',$field,$value,'pos_id',$this->pos_id);
	}
	public function insert($con){
		$field = array('name','abbr','alt1','alt2','order','pref_type','pref_strength','pos_type');
		$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order,$this->pref_type, $this->pref_strength, $this->pos_type);
		$this->pos_id = db_insert($con,'data_pos',$field,$value,'pos_id');
		return $this->pos_id;
	}
	
	public function delete($con){
		return db_delete($con,'data_pos', 'pos_id', $this->pos_id);
	}
}
class dPosLib{//($con = null, $auto_load = true, $group = null)
	public $pos = array();
	public $ord = array();
	public $con;
	
	function __construct($con = null, $auto_load = true, $group = null){
		$this->con = $con;
		if($con && $auto_load)
			$this->load($this->con, true, $group);
	}
	function load($con = null, $auto_order = true, $group = null){
		if($con)
			$this->con = $con;
		if(!$this->con)
			return false;
			
		$this->pos = array();
		$data = db_select($this->con,'data_pos','pos_id',false,false,true);
		if($data)
		foreach($data as $d){
			if(!$group || !is_object($group) || $group->has_pos($d)){
				$pos = new dPos($this->con, $d);
				$this->pos[$pos->pos_id] = $pos;
			}
		}
		if($auto_order)
			$this->order();
		return $this->pos;
	}
	function order($asc = true){ // false for descending
		if($this->con){
			$this->ord = array();
			$sql = "SELECT pos_id FROM data_pos ORDER BY `order`";
			($asc) ? $sql .= " ASC": $sql .= " DESC";
			$ord = new rQuery($this->con, $sql, false, false,true);
			$or = $ord->run();
			$x = array();
			foreach($or as $o){
				if(isset($this->pos[$o['pos_id']])){
					array_push($x, $o);
				}
			}
			$this->ord = $x;
			return $this->ord;
		}
		else
			return false;
	}
	function show($by_order = false){
		if(!$by_order)
			$show = $this->pos;
		else
			$show = $this->get_ordered();
		
		echo "Positions:<br/>";
		foreach($show as $s){
			print_r($s);
			echo "<br/>";
		}
	}
	function get_ordered($con = null){
		if(count($this->ord) <1)
			$this->order($con);
		
		$ret = array();
		
		
		if($this->ord)
		foreach($this->ord as $o){
			if(isset($this->pos[$o['pos_id']]))
				array_push($ret, $this->pos[$o['pos_id']]);
			else
				echo "Error: index '$o' not set.";
		}
		
		return $ret;
	}

	
	function order_fix(){ // need to set this correctly!
		// cycle through the orders
		$this->order();
		$i = 1;
		if($this->ord)
		foreach($this->ord as $o){
			$this->pos[$o['pos_id']]->order = $i;
			$this->pos[$o['pos_id']]->update($this->con);
			$i++;
		}
	}
	function form(){
		echo "<table class='info_pos'>
			<tr><th colspan=8>Position Form</th></tr>
			<tr><th>Order</th><th>Position</th><th>Abbr</th><th>Alt1</th><th>Alt2</th><th>Pref Type</th><th>Pref Strength</th><th>Type</th><th>Remove</th></tr>";
		if($this->ord)
		foreach($this->ord as $o){
			$p = $this->pos[$o['pos_id']];
			echo "<tr class='pos_inst' data-pos_id='{$p->pos_id}' data-changed='no'>
				<td class='table_button order' data-order='{$p->order}' onclick=order_change(this);>{$p->order}</td>
				<td><input type='text' class='input_pos_name' value='{$p->name}'></td>
				<td><input type='text' class='input_pos_abbr' value='{$p->abbr}'></td>
				<td><input type='text' class='input_pos_alt1' value='{$p->alt1}'></td>
				<td><input type='text' class='input_pos_alt2' value='{$p->alt2}'></td>";
			// SELECT for Pref Type
			echo "<td><select class='input_pos_pref_type'><option value=''></option>";
			$config = new conFig();
			$st = $config->staff_type_array();
			foreach($st as $t){
				$selected = '';
				if($p->pref_type == $t['id'])
					$selected = ' selected ';
				echo "<option value='{$t['id']}'{$selected}>{$t['name']}</option>";
			}
			echo "</select>";
			// SLIDER for Pref Strength
			echo "<td><div class='input_pos_pref_strength' data-value='{$p->pref_strength}'></div></td>
				<td><select class='input_pos_type'><option value='0'";
			if($p->pos_type == 0) echo " selected";	
			echo ">Norm</option><option value='1'";
			if($p->pos_type == 1) echo " selected";			
			echo ">NoContract</option></select></td>";
			echo "<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td>
			</tr>";
		}
		echo "</table>";
		echo "<button onclick=pos_add()>Add New Position</button>";
	}
}

class dShift{//($con = null, $type = SHIFT_PAR, $id = null)
	public $shift_id = null;
	public $instance_id = null; // if it has an instance_id, then it is an instance. If instance_id = false, then it's a parent
	public $name,$abbr,$alt1,$alt2;
	public $order;
	public $start_date, $end_date, $start_time, $end_time;
	
	function __construct($con = null, $type = SHIFT_PAR, $id = null){
		if($type == SHIFT_PAR)
			$this->shift_id = $id;
		else
			$this->instance_id = $id;
		if($con && ($this->instance_id !== null || $this->shift_id !== null))
			$this->load($con);
	}
	
	public function load($con){
		if($this->instance_id !== null){
			$data = db_select_all($con, 'data_shift_instance', 'instance_id', $this->instance_id, false, $limit = 1);
			if($data){ // preparing info specific to instances
				$this->instance_id = $data['instance_id'];
			$this->start_date = $data['start_date'];
			$this->end_date = $data['end_date'];
			$this->start_time = $data['start_time'];
			$this->end_time = $data['end_time'];
			}
		}
		elseif($this->shift_id !== null){
			$data = db_select_all($con, 'data_shift', 'shift_id', $this->shift_id, false, $limit = 1);
			if($data){ // preparing info specific to parent shifts
				$this->alt1 = $data['alt1'];
				$this->alt2 = $data['alt2'];
				$this->order = $data['order'];
			}
		}
		if($data){ // info for the both of them
			$this->shift_id = $data['shift_id'];
			$this->name = $data['name'];
			$this->abbr = $data['abbr'];
		}
	}

	// DATABASE FUNCTIONS - SOME OUTDATED!
	public function update($con){
		if($this->instance_id){
			$field = array('shift_id','name','abbr','start_date','end_date','start_time','end_time');
			$value = array($this->shift_id, $this->name, $this->abbr, $this->start_date, $this->end_date, $this->start_time, $this->end_time);
			return db_update($con,'data_shift_instance',$field,$value,'instance_id',$this->instance_id);
		}
		else{
			$field = array('name','abbr','alt1','alt2','order');
			$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order);
			return db_update($con,'data_shift',$field,$value,'shift_id',$this->shift_id);
		}
	}
	public function insert($con){ // OUTDATED
		if($this->instance_id){
			$field = array('shift_id','name','abbr','start_date','end_date','start_time','end_time');
			$value = array($this->shift_id, $this->name, $this->abbr, $this->start_date, $this->end_date, $this->start_time, $this->end_time);
			$this->instance_id = db_insert($con,'data_shift_instance',$field,$value,'instance_id');
			return $this->instance_id;
		}
		else{
			$field = array('name','abbr','alt1','alt2','order');
			$value = array($this->name, $this->abbr, $this->alt1, $this->alt2, $this->order);
			$this->shift_id = db_insert($con,'data_shift',$field,$value,'shift_id');
			return $this->shift_id;
		}
	}
	public function delete($con){ // OUTDATED
		if($this->instance_id){
			return db_delete($con,'data_shift_instance', 'instance_id', $this->instance_id);
		}
		else{
			db_delete($con,'data_shift_instance', 'shift_id', $this->shift_id);
			return db_delete($con,'data_shift', 'shift_id', $this->shift_id);
		}
	}	
}
class dShiftLib{ //($con = null, $date = false, $auto_load = true, $group = null)
	public $par = array(); // Parent Shifts
	public $shift = array(); // Current Instance Shifts. Sorted by parent. $dShiftLib->shift[shift_id]: getting only based on date
	public $inst = array(); // Instance Shifts, an array of them for getting all shifts, even not in the date range. $dShiftLib->inst[shift_id][i]
	public $ord = array();
	public $search_date;
	public $con;
	
	function __construct($con = null, $date = false, $auto_load = true, $group = null){
		$this->con = $con;
		if(!$date)
			$this->search_date = datestr_cur();
		else
			$this->search_date = $date;
		if($con && $auto_load)
		$this->load($this->con, $this->search_date, true, $group);
	}
	
	function load($con = null, $date = null, $auto_order = true, $group = null){
		if($con)
			$this->con = $con;
		if($date)
			$this->search_date = $date;
		
		if($this->con){
			// Get the parents
			$par = db_select($this->con, 'data_shift','shift_id',$where_field = false,$where_value = false, $force_array = true, $limit = false, $debug = false);
			if($par && is_array($par) && count($par) > 0)
			foreach($par as $p){
				if(!$group || !is_object($group) || $group->has_shift($p))
					$this->par[$p] = new dShift($this->con, SHIFT_PAR, $p);
			}
			
			/**** WARNING ****
			THE BELOW TWO ARE NOT HOOKED UP TO GROUPS YET.
			***** /WARNING ***/
			
			// Get the instances (library) - look at making this an rQuery and adding an order by start date/end date. I think that could be helpful.
			$inst = db_select($this->con, 'data_shift_instance','instance_id',$where_field = false, $where_value = false, $force_array = true, $limit = false, $debug = false);
			if($inst && is_array($inst) && count($inst)>0)
			foreach($inst as $i){
				$x = new dShift($this->con, SHIFT_INST,$i);
				if(!isset($this->inst[$x->shift_id]))
					$this->inst[$x->shift_id] = array();
				array_push($this->inst[$x->shift_id], $x);
			}
			
			// Get the shifts (single active instances)
			$sql = "SELECT `instance_id` FROM `data_shift_instance` WHERE (`start_date` <= ?  OR `start_date` = '" . NULL_DATE . "') AND (`end_date` >= ? OR `end_date` = '" . NULL_DATE . "')";
			$query = new rQuery($this->con, $sql, "ss", array($this->search_date, $this->search_date),true);
			$shifts = $query->run();
			if($shifts && count($shifts) > 0){
				foreach($shifts as $s){
					$x = new dShift($this->con, SHIFT_INST,$i);
					$this->shift[$x->shift_id] = $x;
				}
			}
			
			$this->order();
		}
		else
			return false;
	}

	function order($asc = true){ // false for descending. So $dShiftLib->ord[i]['shift_id']
		if($this->con){
			$this->ord = array();
			$sql =  "SELECT `shift_id` FROM `data_shift` ORDER BY `order`";
			($asc) ? $sql .= " ASC": $sql .= " DESC";
			$ord = new rQuery($this->con, $sql, false, false, true);
			$or = $ord->run();
			$x = array();
			foreach($or as $o){
				if(isset($this->par[$o['shift_id']]))
					array_push($x,$o);
			}
			$this->ord = $x;
			return $this->ord;
		}
		else
			return false;
	}
	
	function order_fix(){ // need to set this correctly!
		// cycle through the orders
		$this->order();
		$i = 1;
		if($this->ord)
		foreach($this->ord as $o){
			$this->par[$o['shift_id']]->order = $i;
			$this->par[$o['shift_id']]->update($this->con);
			$i++;
		}
	}
	
	function form(){
	
		// Opening tags for the table
		echo "<table class='info_shift'>
			<tr><th colspan=7>Shift Form</th></tr>";
		// Foreach parent: Order Shift Abbr Alt1 Alt2 Add Remove
		
		if($this->ord) foreach($this->ord as $o){
			$par = $this->par[$o['shift_id']];
			echo "<tr><th>Order</th><th>Shift</th><th>Abbr</th><th>Alt1</th><th>Alt2</th><th>Add</th><th>Remove</th></tr>";
			echo "<tr class='shift_par' data-shift_id='{$par->shift_id}' data-changed='no'>
				<td class='table_button order' data-order='{$par->order}' onclick=order_change(this);>{$par->order}</td>
				<td><input type='text' class='input_shift_name' value='{$par->name}'></td>
				<td><input type='text' class='input_shift_abbr' value='{$par->abbr}'></td>
				<td><input type='text' class='input_shift_alt1' value='{$par->alt1}'></td>
				<td><input type='text' class='input_shift_alt2' value='{$par->alt2}'></td>
				<td class='table_button add_inst data-action='add' onclick=shift_inst_add(this);>+</td>
				<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td></tr>";
		// Foreach Child: Shift Abbr Start Time End Time Start Date End Date Remove
			if(isset($this->inst[$par->shift_id]) && count($this->inst[$par->shift_id] > 0)){
				echo "<tr><td>-</td><td colspan=6>
					<table class='shift_inst' data-shift_id='{$par->shift_id}'>
					<tr><th>Shift</th><th>Abbr</th><th>S.Time</th><th>E.Time</th><th>S.Date</th><th>E.Date</th><th>Remove</th></tr>";
				foreach($this->inst[$par->shift_id] as $i){
					echo "<tr class='shift_inst' data-shift_id='{$i->shift_id}' data-instance_id='{$i->instance_id}' data-changed='no'>
						<td><input type='text' class='input_shift_inst_name' value='{$i->name}'></td>
						<td><input type='text' class='input_shift_inst_abbr' value='{$i->abbr}'></td>
						<td><input type='time' class='input_shift_inst_start_time' value='{$i->start_time}'></td>
						<td><input type='time' class='input_shift_inst_end_time' value='{$i->end_time}'></td>
						<td><input type='date' class='input_shift_inst_start_date' value='{$i->start_date}'></td>
						<td><input type='date' class='input_shift_inst_end_date' value='{$i->end_date}'></td>
						<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td></tr>";
				}
				echo "</table></tr>";
			}
		}
		// Closing:
		echo "</table>
		<button onclick=shift_add()>Add New Parent Shift</button>";
		
		/*
				echo "<td><input type='date' class='input_shift_start_date' value='{$s->start_date}'></td>
					<td><input type='date' class='input_shift_end_date' value='{$s->end_date}'></td>
					<td><input type='time' class='input_shift_start_time' value='{$s->start_time}'></td>
					<td><input type='time' class='input_shift_end_time' value='{$s->end_time}'></td>
					<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td>
				</tr>";
			}
		}
		echo "</table>";
		echo "<button onclick=shift_add()>Add New Shift</button>";
		*/
	}
	/*
	function form(){
		echo "<table class='info_shift'>
			<tr><th colspan=12>Shift Form</th></tr>
			<tr><th>Order</th><th>Shift</th><th>Abbr</th><th>Alt1</th><th>Alt2</th><th>Parent</th><th>Start Date</th><th>End Date</th><th>Start Time</th><th>End Time</th><th>Remove</th></tr>";
		if($this->ord){
			$par_select = array();
			foreach($this->ord as $o2){
				// id, instance, name, parentable?
				$s2 = $this->shift[$o2['shift_id']];
				array_push($par_select, array(
					'shift_id'=> $s2->shift_id,
					'name' => $s2->name,
					'instance' => $s2->instance,
					'child' => false, // you'll need to improve this one, don't show if it has a parent. Will require updating the class though to have that in its info
				));
				/*
				$s2 = $this->shift[$o2['shift_id']];
				array_push($par_select,"<option value='{$s2->shift_id}'>{$s2->name}</option>");
				
			}
			foreach($this->ord as $o){
				$s = $this->shift[$o['shift_id']];
				echo "<tr class='shift_inst' data-instance='{$s->instance}' data-changed='no'>
					<td class='table_button order' data-order='{$s->order}' onclick=order_change(this);>{$s->order}</td>
					<td><input type='text' class='input_shift_name' value='{$s->name}'></td>
					<td><input type='text' class='input_shift_abbr' value='{$s->abbr}'></td>
					<td><input type='text' class='input_shift_alt1' value='{$s->alt1}'></td>
					<td><input type='text' class='input_shift_alt2' value='{$s->alt2}'></td>";
				// OLD: echo"	<td><input type='text' class='input_shift_instance' value='{$s->instance}'></td>";
				// select of parents (shift_id)
				echo "<td><select class='input_shift_id'><option></option>";
				foreach($par_select as $p){
				// shift_id, name, instance,child
					if($s->instance != $p['instance'] && !$p['child']){
						$selected = '';
						if($s->shift_id == $p['shift_id'])
							$selected = ' selected ';
						echo "<option value='{$p['shift_id']}'{$selected}>{$p['name']}</option>";
					}
				}
				echo "</select></td>";
				// AND the rest of the data
				echo "<td><input type='date' class='input_shift_start_date' value='{$s->start_date}'></td>
					<td><input type='date' class='input_shift_end_date' value='{$s->end_date}'></td>
					<td><input type='time' class='input_shift_start_time' value='{$s->start_time}'></td>
					<td><input type='time' class='input_shift_end_time' value='{$s->end_time}'></td>
					<td class='table_button remove' data-action='del' onclick=info_del(this);>X</td>
				</tr>";
			}
		}
		echo "</table>";
		echo "<button onclick=shift_add()>Add New Shift</button>";
	}
	*/
}

class staffAv{//($date = null, $day = null, $staff_id = null, $shift_id = null, $pos_id = null, $pref = null, $fixed = null, $raw = null)
	public $date, $day;
	public $shift_id, $staff_id, $pos_id;
	public $pref, $fixed, $raw;
	
	function __construct($date = null, $day = null, $staff_id = null, $shift_id = null, $pos_id = null, $pref = null, $fixed = null, $raw = null){
		$this->date = $date;
		$this->day = $day;
		$this->staff_id = $staff_id;
		$this->shift_id = $shift_id;
		$this->pos_id = $pos_id;
		$this->pref = $pref;
		$this->fixed = $fixed;
		$this->raw = $raw;
	}
}
class dTemplateAv{//($con = null,  $action = TEMP_AV_STAFF, $id = null, $name = TEMP_DEFAULT)
	public $av = array(); // will load to here if for a non-null id
	public $staff = array(); // will load to here if you had a null staff_id
	public $type = array(); // will load to here if you had a null type
	public $action, $id; // $id = either staff_id or type, depending on the request
	public $name;
	
	function __construct($con = null,  $action = TEMP_AV_STAFF, $id = null, $name = TEMP_DEFAULT){
		$this->id = $id;
		$this->action = $action;
		$this->name = $name;
		if($con){
			$this->init($con);
		}
	}
	
	public function init($con){
		if($this->action == TEMP_AV_STAFF){
			$where_field = array('type');
			$where_value = array('0');
			if($this->name){
				array_push($where_field,'name');
				array_push($where_value,$this->name);
			}
			if($this->id){
				array_push($where_field,'staff_id');
				array_push($where_value,$this->id);
			}
		}
		elseif($this->action == TEMP_AV_TYPE){
			$where_field = array('staff_id');
			$where_value = array('0');
			if($this->name){
				array_push($where_field,'name');
				array_push($where_value,$this->name);
			}
			if($this->id){
				array_push($where_field,'type');
				array_push($where_value,$this->id);
			}
		}
		$result = db_select_all($con, 'templates_av', $where_field,$where_value, $force_array = true, $limit = false, $debug = false);
		if($this->id){
			foreach($result as $r){
				array_push($this->av, $this->make_av($r));
			}
		}
		elseif($this->action == TEMP_AV_STAFF){
			foreach($result as $r){
				if(!isset($this->staff[$r['staff_id']]))
					$this->staff[$r['staff_id']] = array();
				array_push($this->staff[$r['staff_id']], $this->make_av($r));
			}
		}
		elseif($this->action == TEMP_AV_TYPE){
			foreach($result as $r){
				if(!isset($this->type[$r['type']]))
					$this->type[$r['type']] = array();
				array_push($this->type[$r['type']], $this->make_av($r));
			}
		}
	}
	
	function make_av($array){
		$a = new staffAv();
		if(isset($array['staff_id']))
			$a->staff_id = $array['staff_id'];
		else $a->staff_id = 0;
		if(isset($array['type']))
			$a->type = $array['type'];
		else $a->type = 0;
		if(isset($array['day']))
			$a->day = $array['day'];
		else $a->day = 0;
		if(isset($array['date']))
			$a->date = $array['date'];
		else $a->date = null;
		if(isset($array['shift_id']))
			$a->shift_id = $array['shift_id'];
		else $a->shift_id = 0;
		if(isset($array['pos_id']))
			$a->pos_id = $array['pos_id'];
		else $a->pos_id = 0;
		if(isset($array['pref']))
			$a->pref = $array['pref'];
		else $a->pref = 0;
		if(isset($array['fixed']))
			$a->fixed = $array['fixed'];
		else $a->fixed = 0;
		return $a;
	}
}

function show_partner_select($con,$partner_id = null){ // this doesn't include the select tags around it, so that you have the ability to determine its id and whatever.
	$staffM = new staffM($con);
	echo "<option>";
	echo "</option>";
//	echo "<option value = ''>None</option>";
	foreach($staffM->staff as $s)
		if($partner_id == $s->staff_id)
			echo "<option value='{$s->staff_id}' selected>{$s->name}</option>";
		else
			echo "<option value='{$s->staff_id}'>{$s->name}</option>";
	return true;
}
?>
<?php
require_once('general_functions.php');
require_once('staff_classes.php');
require_once('constants.php');

//if(!isset($_SESSION)){session_start();}

class CalCel{
	public $value, $disp, $abbr;
	
	function __construct($value = null,$disp = null,$abbr = null){
		$this->value = $value;
		$this->disp = $disp;
		$this->abbr = $abbr;
	}
}

class CalContent{
	public $row1,$row2,$col1,$col2;
	public $data;
	
	function __construct($data = null,$row1 = null, $col1 = null, $row2 = null, $col2 = null){
		$this->row1 = $row1;
		$this->row2 = $row2;
		$this->col1 = $col1;
		$this->col2 = $col2;
		$this->data = $data;
	}
}
class CalContentLib{
	public $content = array();
	public $content_cycle = array();
	
	function __construct($content = null){
		if($content){
			$this->content = $content;
			$this->content_cycle = $this->content;
		}
	}
	
	public function add_content($data = null, $row1 = null, $col1 = null, $row2 = null, $col2 = null, $reset = true){
		$content = new CalContent($data,$row1,$col1,$row2,$col2);
		array_push($this->content, $content);
		if($reset) $this->reset();
	}
	public function add_content_obj($content, $reset = true){
		array_push($this->content, $content);
		if($reset) $this->reset();
	}
	
	public function reset(){
		$this->content_cycle = $this->content;
	}
	/*
	public function get_data($row1 = null, $col1 = null, $row2 = null, $col2 = null, $single_use = true){
		foreach($this->content_cycle as $i=>$c){
			if($row1 == $c->row1 && $row2 == $c->row2 && $col1 == $c->col1 && $col2 == $c->col2){
				$data = $c->data;
				if($single_use) unset($this->content_cycle[$i]);
				return $data;
			}
		}
	}
	*/
	
	public function get_data($row1 = null, $col1 = null, $row2 = null, $col2 = null, $single_use = true){
		$data = '';
		foreach($this->content_cycle as $i=>$c){
			if($row1 == $c->row1 && $row2 == $c->row2 && $col1 == $c->col1 && $col2 == $c->col2){
				if($data != '')
					$data .= "<br/>";
				$data .= $c->data;
				if($single_use) unset($this->content_cycle[$i]);
				//return $data;
			}
		}
		if($data != ''); return $data;
	}
	public function mod_data($new_data, $row1 = null, $col1 = null, $row2 = null, $col2 = null){
		foreach($this->content as &$c){
			if($row1 == $c->row1 && $row2 == $c->row2 && $col1 == $c->col1 && $col2 == $c->col2)
				$c->data = $new_data;
		}
	}
	
}
class Calendar{

	public $title, $title_addition, $type, $datestr, $table_id, $table_class;
	public $row1, $row2, $col1, $col2;
	public $row1_type, $row2_type, $col1_type, $col2_type;
	public $row1_data, $row2_data, $col1_data, $col2_data;
	public $row1_format, $row2_format, $col1_format, $col2_format;
	public $start_date = '';
	public $end_date = '';
	public $date_select_buttons = true;
	public $date_select_input = true;
	public $group_select = false;
	public $datestr_format;
	public $config;
	public $content, $default_content;
	public $con;
	public $staff = null; 
	public $group = null;
	public $date = null;
	public $shift = null;
	public $pos = null;
	public $set_staff_name_type = null;
	
	function __construct($con, $datestr = false, $type = CAL_TYPE_WEEK, $id = 'calendar', $class = 'calendar_standard', $title = "Calendar", $row1_type = CAL_POS, $row2_type = null, $col1_type = CAL_DATE, $col2_type = null, $title_suffix = false, $datestr_format = "D jS", $content = null, $default_content = null){
		$this->con = $con;
		
		$this->title = $title;
		($datestr) ? $this->datestr = $datestr : $this->datestr = datestr_cur();
		$this->title_suffix = $title_suffix;
		$this->type = $type;
		$this->table_id = $id;
		$this->table_class = $class;
		$this->row1_type = $row1_type;
		$this->row2_type = $row2_type;
		$this->col1_type = $col1_type;
		$this->col2_type = $col2_type;
		$this->row1_format = 'disp';
		$this->row2_format = 'disp';
		$this->col1_format = 'disp';
		$this->col2_format = 'disp';
		$this->datestr_format = $datestr_format;
		$this->config = new conFig();
		$this->content = $content;
		$this->default_content = $default_content;
	}
	
	public function init($print = true){
		$this->build();
		if($print)
			$this->print_it();
	}
	
	public function build(){
		if( $this->row1_type == CAL_DATE || $this->row2_type == CAL_DATE || $this->col1_type == CAL_DATE || $this->col2_type == CAL_DATE){
			if($this->type == CAL_TYPE_WEEK)
				$type = DATESTR_ARRAY_WEEK;
			elseif($this->type == CAL_TYPE_MONTH)
				$type = DATESTR_ARRAY_MONTH;
				
			$complete_week = datestr_array($this->datestr,$type);
			$processed_week = array();
			$active_days = $this->config->day_active_array();
			foreach($complete_week as $c){
				$active = false;
				foreach($active_days as $i=>$a){
					if(datestr_format($c,'w') == $i-1){
						$active = true;
					}
				}
				if($active){
					array_push($processed_week, $c);
				}
			}
			$this->date = $processed_week;
			$this->start_date = $complete_week[0];
			$this->end_date = $complete_week[count($complete_week)-1];//$this->date[count($this->date)-1];
		}
		else
			$this->date = null;
		// POS
		if( $this->row1_type == CAL_POS || $this->row2_type == CAL_POS || $this->col1_type == CAL_POS || $this->col2_type == CAL_POS){
			if(!is_object($this->group)){
				$this->pos = new dPosLib($this->con);
			}
			else{
				$this->pos = new dPosLib($this->con,true,$this->group);
			}
		//	(!is_object($this->group)) ? $pos = new dPosLib($this->con): $pos = new dPosLib($this->con,true,$this->group);
		}
		else
			$this->pos = null;
		// SHIFT
		if( $this->row1_type == CAL_SHIFT || $this->row2_type == CAL_SHIFT || $this->col1_type == CAL_SHIFT || $this->col2_type == CAL_SHIFT){
			if(!is_object($this->group)){
				$this->shift = new dShiftLib($this->con);
			}
			else{
				$this->shift = new dShiftLib($this->con, false, true, $this->group);
			}
		}
		else
			$this->shift = null;
		// STAFF
		// THIS ONE: will call for all staff members, unless you have given it a staffM first
		if( $this->row1_type == CAL_STAFF || $this->row2_type == CAL_STAFF || $this->col1_type == CAL_STAFF || $this->col2_type == CAL_STAFF){
			if($this->staff && is_object($this->staff))
				$this->staff = $this->staff;
			else{
				if(!$this->date || !$this->start_date || !$this->end_date){
					$this->staff = new staffM($this->con,false,false,false,$this->group);		
				}
				else{
					$this->staff = new staffM($this->con,false,false,false,$this->group, array('start_date'=>$this->start_date, 'end_date'=>$this->end_date));
				}
			}
		}
		else
			$this->staff = null;
		
		if($this->set_staff_name_type){
			$this->staff->set_name($this->set_staff_name_type);
		}
		
		if($this->row1_type == CAL_DAY || $this->row2_type == CAL_DAY || $this->col1_type == CAL_DAY || $this->col2_type == CAL_DAY){
			$this->day = $this->config->day_active_array();
		}
		// DO THE GET'S ON ALL THE APPROPRIATE ROWS
		switch($this->col1_type){
			case CAL_DATE:
				$this->col1_data = 'date';
				$this->col1 = $this->get_date($this->date);
				break;
			case CAL_POS:
				$this->col1_data = 'pos';
				$this->col1 = $this->get_pos($this->pos);
				break;
			case CAL_SHIFT:
				$this->col1_data = 'shift';
				$this->col1 = $this->get_shift($this->shift);
				break;
			case CAL_STAFF:
				$this->col1_data = 'staff';
				$this->col1 = $this->get_staff($this->staff);
				break;
			case CAL_DAY:
				$this->col1_data = 'day';
				$this->col1 = $this->get_day($this->day);
			default:
				break;
		}
		switch($this->col2_type){
			case CAL_DATE:
				$this->col2_data = 'date';
				$this->col2 = $this->get_date($this->date);
				break;
			case CAL_POS:
				$this->col2_data = 'pos';
				$this->col2 = $this->get_pos($this->pos);
				break;
			case CAL_SHIFT:
				$this->col2_data = 'shift';
				$this->col2 = $this->get_shift($this->shift);
				break;
			case CAL_STAFF:
				$this->col2_data = 'staff';
				$this->col2 = $this->get_staff($this->staff);
				break;
			case CAL_DAY:
				$this->col2_data = 'day';
				$this->col2 = $this->get_day($this->day);
			default:
				break;
		}
		switch($this->row1_type){
			case CAL_DATE:
				$this->row1_data = 'date';
				$this->row1 = $this->get_date($this->date);
				break;
			case CAL_POS:
				$this->row1_data = 'pos';
				$this->row1 = $this->get_pos($this->pos);
				break;
			case CAL_SHIFT:
				$this->row1_data = 'shift';
				$this->row1 = $this->get_shift($this->shift);
				break;
			case CAL_STAFF:
				$this->row1_data = 'staff';
				$this->row1 = $this->get_staff($this->staff);
				break;
			case CAL_DAY:
				$this->row1_data = 'day';
				$this->row1 = $this->get_day($this->day);
			default:
				break;
		}
		switch($this->row2_type){
			case CAL_DATE:
				$this->row2_data = 'date';
				$this->row2 = $this->get_date($this->date);
				break;
			case CAL_POS:
				$this->row2_data = 'pos';
				$this->row2 = $this->get_pos($this->pos);
				break;
			case CAL_SHIFT:
				$this->row2_data = 'shift';
				$this->row2 = $this->get_shift($this->shift);
				break;
			case CAL_STAFF:
				$this->row2_data = 'staff';
				$this->row2 = $this->get_staff($this->staff);
				break;
			case CAL_DAY:
				$this->row2_data = 'day';
				$this->row2 = $this->get_day($this->day);
			default:
				break;
		}
	}
	
	public function get_date($date){
		$ret = array();
		foreach($date as $d){
			$cc = new CalCel($d, datestr_format($d,$this->datestr_format));
			array_push($ret,$cc);
		}
		return $ret;
	}
	public function get_pos($pos){ // THIS IS WORKING, BUT NOT CONFIGURED YET TO THE POS_GROUPS!
		$ret = array();
		foreach($pos->ord as $p){
			$cc = new CalCel($pos->pos[$p['pos_id']]->pos_id, $pos->pos[$p['pos_id']]->name, $pos->pos[$p['pos_id']]->abbr);
			array_push($ret, $cc);
		}
		return $ret;
		
	}
	public function get_shift($shift){ // WORKING, BUT NO POS_GROUPS STUFF
		$ret = array();
		foreach($shift->ord as $s){
			$cc = new CalCel($shift->par[$s['shift_id']]->shift_id, $shift->par[$s['shift_id']]->name, $shift->par[$s['shift_id']]->abbr);
			array_push($ret, $cc);
		}
		return $ret;
	}
	public function get_staff($staff){
		$ret = array();
		foreach($staff->staff as $s){
			$cc = new CalCel($s->staff_id, $s->name, $s->last_name);
			array_push($ret, $cc);
		}
		return $ret;
	}
	public function get_day($day){
		$ret = array();
		foreach($day as $d){
			$cc = new CalCel($d['id'], $d['name'], $d['abbr']);
			array_push($ret, $cc);
		}
		return $ret;
	}
	
	
	public function print_it(){
	{// DATA PREP
	($this->col2) ? $col1_colspan = count($this->col2): $col1_colspan = 1;
	($this->row2) ? $row1_rowspan = count($this->row2): $row1_rowspan = 1;
	
	($this->col2) ? $empty_rowspan = 2 : $empty_rowspan = 1;
	($this->row2) ? $empty_colspan = 2 : $empty_colspan = 1;
	
	$row_count = count($this->row1) * $row1_rowspan;
	if($this->col2) $row_count ++; // +1 for second columns row
	$row_count += 2; // 1 for title bar, and 1 for col1.
	
	$col_count = count($this->col1) * $col1_colspan;
	if($this->row2) $col_count ++;
	$col_count += 1;
	
	$cell_col_count = $col_count - 1;
	if($this->row2) $cell_col_count --;
	
	}
	// ACTUALLY PRINTING IT
	echo 
	"<table id='{$this->table_id}' class='{$this->table_class}' data-type='{$this->type}' data-row1_type='{$this->row1_type}' data-row2_type='{$this->row2_type}' data-col1_type='{$this->col1_type}' data-col2_type='{$this->col2_type}' data-start_date='{$this->start_date}' data-end_date='{$this->end_date}' data-row1_format='{$this->row1_format}' data-row2_format='{$this->row2_format}' data-col1_format='{$this->col1_format}' data-col2_format='{$this->col2_format}'";
	if(is_object($this->group)) echo "data-group_id='{$this->group->group_id}'";
	echo ">
	<thead><tr><th class='cal_title' colspan=$col_count>{$this->title}{$this->title_suffix}</th></tr>";
	// INTERFACE ROW
	if($this->date_select_buttons || $this->date_select_input){
		echo "<tr><th colspan=$col_count>";
		if($this->date_select_buttons)
			echo "<button onclick=push_date(this); data-value='" . datestr_shift($this->datestr, -7). "'><-</button>";
		if($this->date_select_input)
			echo "<input type='date' onchange=push_date(this); value='" . $this->datestr . "'>";
		if($this->date_select_buttons)
			echo "<button onclick=push_date(this); data-value='" . datestr_shift($this->datestr, 7). "';>-></button>";
		if($this->group_select){
			$groupLib = new dGroupLib($this->con);
			
			echo "<div id='cal_group_radio'>";
			$i = 1;
			foreach($groupLib->Group as $g){
				$checked = '';
				if(is_object($this->group) && $this->group->group_id == $g->group_id) $checked = ' checked';
				echo "<input type='radio' id='cgr_{$i}' name='group' value='{$g->group_id}' onchange=change_group(this);{$checked}><label for='cgr_{$i}'>{$g->name}</label>";
				$i++;
			}
			echo "</div>";
		}
		echo "</th></tr>";
	}
	// COL 1 ROW
	echo "<tr><th colspan=$empty_colspan rowspan=$empty_rowspan>-</th>";
	foreach($this->col1 as $c)
		echo "<th colspan=$col1_colspan data-{$this->col1_data}='{$c->value}'>{$c->{$this->col1_format}}</th>";
	echo "</tr>";
	// COL 2 ROW
	if($this->col2){
		echo "<tr>";
		foreach($this->col1 as $c1)
			foreach($this->col2 as $c)
				echo "<th data-{$this->col1_data}='{$c1->value}' data-{$this->col2_data}='{$c->value}'>{$c->{$this->col2_format}}</th>";
		echo "</tr>";
	}
	echo "</thead>";
	// BODY
	echo "<tbody>";
	foreach($this->row1 as $r1){
		echo "<tr><th rowspan=$row1_rowspan data-{$this->row1_data}='{$r1->value}'>{$r1->{$this->row1_format}}</th>";
		if($this->row2)
		foreach($this->row2 as $index=>$r2){
			if($index != 0) echo "<tr>";
			echo "<th data-{$this->row1_data}='{$r1->value}' data-{$this->row2_data}='{$r2->value}'>{$r2->{$this->row2_format}}</th>";
			for($i=0; $i<$cell_col_count; $i++){
				$c1 = $this->col1[$i/$col1_colspan];
				if($this->col2) $c2 = $this->col2[$i%$col1_colspan];
				$data = "data-{$this->col1_data}='{$c1->value}'";
				if($this->col2) $data .= " data-{$this->col2_data}='{$c2->value}'";
				$data .= " data-{$this->row1_data}='{$r1->value}'";
				if($this->row2) $data .= " data-{$this->row2_data}='{$r2->value}'";
				
				echo "<td $data>";
				if($this->content || $this->default_content){
					$r1v = null; $r2v = null; $c1v = null; $c2v = null;
					if($this->row1 && $r1 && $r1->value)
						$r1v = $r1->value;
					if($this->row2 && $r2 && $r2->value)
						$r2v = $r2->value;
					if($this->col1 && $c1 && $c1->value)
						$c1v = $c1->value;
					if($this->col2 && $c2 && $c2->value)
						$c2v = $c2->value;
					//echo "r1:$r1v c1:$c1v r2:$r2v c2:$c2v<br/>";
					if(is_object($this->content) && $c = $this->content->get_data($r1v, $c1v, $r2v, $c2v))
						echo $c;
					else
						echo $this->default_content; 
				}
				echo "</td>";
			}
			echo "</tr>";
		}
		else{
			for($i=0; $i<$cell_col_count; $i++){
			$c1 = $this->col1[$i/$col1_colspan];
			if($this->col2) $c2 = $this->col2[$i%$col1_colspan];
			$data = "data-{$this->col1_data}='{$c1->value}'";
			if($this->col2) $data .= " data-{$this->col2_data}='{$c2->value}'";
			$data .= " data-{$this->row1_data}='{$r1->value}'";
			echo "<td $data>";
			if($this->content || $this->default_content){
				$r1v = null; $r2v = null; $c1v = null; $c2v = null;
				if($this->row1 && $r1 && $r1->value)
					$r1v = $r1->value;
				if($this->row2 && $r2 && $r2->value)
					$r2v = $r2->value;
				if($this->col1 && $c1 && $c1->value)
					$c1v = $c1->value;
				if($this->col2 && $c2 && $c2->value)
					$c2v = $c2->value;
				if(is_object($this->content) && $c = $this->content->get_data($r1v, $c1v, $r2v, $c2v))
					echo $c;
				else
					echo $this->default_content;
			}
			echo "</td>";
			}
		
		}
		echo "</tr>";
	}
	echo "</tbody></table>";
	}
	
	public function show(){
		print_r($this);
		echo "<br/>";
	}

}
?>
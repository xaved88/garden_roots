<?php
$action = $_POST['action'];
switch($action){	
	case 'generate':
	{// PRINT TEMPLATES
	require_once('../includes/staff_classes.php');
	$data = array();
	$data['message'] = "Process incomplete. Refer to error calls to determine the problem.<br/>";
	
	if(!isset($_POST['date']) || !isset($_POST['template'])){
		$data['message'] = "Error: Either date or template information does not exist - cannot load.";
		echo json_encode($data);
		return;
	}
	
	session_update('date',$_POST['date']);
	{//1) Load the file
	$file = '../config/rota_templates.xml';
	$xml = new SimpleXMLElement($file, null, true);
	$xtemplate = false;
	foreach($xml->template as $t){
		foreach($t->attributes() as $a => $b){
			if($b == $_POST['template'])
				$xtemplate = $t;
		}
	}
	if(!$xtemplate){
		$data['message'] = "Error. Could not find template with the given name.<br/>";
		echo json_encode($data);
		return;
	}
	}
	{//1.5) CLASSES && FUNCTIONS
	
	function get_record($array, $search, $value){
		if(!is_array($search)){
			$search = array($search);
		}
		if(!is_array($value)){
			$value = array($value);
		}
		if(count($search) != count($value)){
			echo "Error: search & value terms don't match.<br/>";
			return false;
		}
		
		$ret = array();
		
		if(isset($array) && is_array($array) && count($array > 0))
		foreach($array as $a){
			$count = 0;
			foreach($search as $i=>$s){
				if(isset($a[$s]) && $a[$s] == $value[$i])
					$count ++;
			}
			if($count == count($search))
				array_push($ret, $a);
		}
		return $ret;
	}
	
	function info_in_cell($cell, $name){
		foreach($cell->table->tr as $tr)
			foreach($tr->td as $td)
				foreach($td->content as $c)
					if((string)$c == (string)$name)
						return true;
						
		return false;
	}
	function make_unique($array, $no_val = null){
		$erase_values = array();
		if($no_val !== null)
		if(is_array($no_val)){
			$erase_values = array_merge($erase_values,$no_val);
		}
		else
			array_push($erase_values, $no_val);
		
		foreach($array as $i=>$a){
			$erase = false;
			foreach($erase_values as $e){
				if($a == $e)
					$erase = true;
			}
			if(!$erase) array_push($erase_values, $a);
			else
				unset($array[$i]);
		}
		
		return $array;
	}
		
	function find_in_array($array, $value){
		if(count($array) <= 0)
			return false;
		foreach($array as $a)
			if($a == $value)
				return true;
		return false;
		
	}
		
	class tCell{
		public $date, $shift, $css, $data;
		
		public function __construct($date = null, $shift = null, $css = null, $data = null){
			$this->date = $date;
			$this->shift = $shift;
			$this->css = $css;
			$this->data = $data;
		}
		public function check($date,$shift){
			if($this->date == $date && $this->shift == $shift)
				return true;
			else
				return false;
		}
		
		public function fill_data($records, $cell, $info, $pos, $staff){
		//Array ( [staff_id] => 38 [date] => 2014-08-18 [pos_id] => 8 [shift_id] => 10 )
			$html = "<table";
			if(!$cell){
				$html .= "></table>";
				$this->data = $html;
				return true;
			}
			if($cell->css)
				$html .= " style=\"{$cell->css}\"";
			$html .= ">";
			//if(isset($cell->table->tr) && is_array($cell->table->tr) && count($cell->table->tr)>0)
			if(isset($cell->table->tr))
			foreach($cell->table->tr as $tr){
				$html .= "<tr>";
				foreach($tr as $td){
					$html .= "<td colspan='{$td->colspan}' rowspan='{$td->rowspan}'";
					{// GET THE TD STYLING
					$style = array();
					$empty_style = array();
					$count_all = 0;
					foreach($td->content as $c){
						$content = false;
						foreach($info->inst as $i){
							if((string)$i->name == (string)$c)
								$content = $i;
						}
						$count = 0;
						foreach($records as $r){
							if($r['pos_id'] == (int)$content->pos_id){
								$count ++;
								$count_all ++;
							}
						}
						if($count == 0 && $content->empty_css)
							array_push($empty_style, $content->empty_css);
						else if($count > 0 && $content->div_css)
							array_push($style, $content->div_css);
					}
					if($count_all > 0){
						if(count($style)>0)
							$style = "style=\"". implode(" ",$style) . "\"";
						else
							$style = "";
					}
					elseif($count_all == 0){
						if(count($empty_style)>0)
							$style = "style=\"". implode(" ",$empty_style) . "\"";
						else
							$style = "";
					}
					$html .= $style . ">";
					}
					{// GET THE REST OF THE CONTENT DATA
					foreach($td->content as $c){
						$content = false;
						foreach($info->inst as $i){
							if((string)$i->name == (string)$c)
								$content = $i;
						}
						if($content){
							// POSITION PREP
							$pos_text = '';
							if(($pos_display = (string)$content->pos_disp)!='none'){
								if((string)$content->pos_delim == 'yes') $tag = 'div';
								else $tag = 'span';
								if(isset($pos->pos[(int)$content->pos_id]->$pos_display)){
									$pos_text = "<{$tag}";
									if($content->pos_css)
										$pos_text .= " style=\"{$content->pos_css}\"";
									$pos_text .= ">{$pos->pos[(int)$content->pos_id]->$pos_display}</{$tag}>";
								}
							}
							// STAFF PREP
							$staff_text = '';
							$staff_names = array();
							$count = 0;
							$staff_id_used = array();
							foreach($records as $r){
								$shifts = array();
								if($content->shift_overflow == '') array_push($shifts, $this->shift);
								else $shifts = array_merge($shifts, explode(',',$content->shift_overflow));
								
								if(find_in_array($shifts, $r['shift_id']))
								if($r['pos_id'] == (int)$content->pos_id && !find_in_array($staff_id_used, (int)$r['staff_id'])){
									$count ++;
									array_push($staff_id_used, $r['staff_id']);
									//$r['staff_id']
									$staff_temp = $staff->staff[(int)$r['staff_id']];
									switch((string)$content->name_disp){
										case "first":
											$staff->staff[(int)$r['staff_id']]->set_name(NAME_FIRST);
											break;
										case "last":
											$staff->staff[(int)$r['staff_id']]->set_name(NAME_LAST);
											break;
										case "initials":
											$staff->staff[(int)$r['staff_id']]->set_name(NAME_INITIALS);
											break;
										case "first+":
											$staff->staff[(int)$r['staff_id']]->set_name(NAME_FIRST_L);
											break;
										case "last+":
											$staff->staff[(int)$r['staff_id']]->set_name(NAME_F_LAST);
											break;
										case "full":
										default:
											$staff->staff[(int)$r['staff_id']]->set_name(NAME_STANDARD);
											break;
									}
									$sn = $staff->staff[(int)$r['staff_id']]->name;
									if((string)$content->pos_replicate == 'yes' && (string)$content->pos_loc == 'after')
										$sn .= $pos_text;
									elseif((string)$content->pos_replicate == 'yes' && (string)$content->pos_loc == 'before')
										$sn = $pos_text . $sn;
									array_push($staff_names, $sn);
								}
							}
							if((string)$content->pos_delim == 'yes') $tag = 'div';
								else $tag = 'span';
							if((string)$content->name_delim == 'yes'){
								$staff_pre_text = implode('<br/>',$staff_names);
							}
							else
								$staff_pre_text = implode((string)$content->name_delim_string,$staff_names);
							$staff_text = "<{$tag}";
							if($content->name_css)
								$staff_text .= " style=\"{$content->name_css}\"";
							$staff_text .= ">{$staff_pre_text}</{$tag}>";
							
							if($content->empty_hide != 'yes' || $count > 0){
								if((string)$content->pos_replicate == 'yes')
									$html .= "<div>{$staff_text}</div>";
								elseif((string)$content->pos_loc == 'before')
									$html .= "<div>" . $pos_text . $staff_text . "</div>";
								else
									$html .= "<div>" . $staff_text . $pos_text . "</div>";
							}
						}
					}
					}
					$html .= "</td>";
				}
				$html .= "</tr>";
			}
			
			$html .= "</table>";
			$this->data = $html;
			return true;
		}
		public function print_it($pos,$staff){
			$html = $this->data;
			return $html;
		}
	}
	class tTable{
		public $cell = array();
		public $start_date, $end_date;
		public $date = array();
		public $datestr_format = 'D jS';
		public $shift_format = 'abbr';
		public $col, $col_type, $row, $row_type;
		public $sched;
		public $title = false;
		public $title_text, $show_title, $title_date_format, $title_date_position;
		
		public function add_cell($cell){
			array_push($this->cell, $cell);
		}
		
		public function make_title(){
			if(!$this->show_title){
				$this->title = false;
				return;
			}
			if($this->title_text){
				$this->title = $this->title_text;
			}
			if($this->title_date_format && $this->title_date_format != 'none'){
				$date = array();
				foreach($this->date as $d){
					array_push($date, $d);
				}
				$date_first = $date[0];
				$date_last = $date[count($date)-1];
				switch($this->title_date_format){
					case 'startDjS':
						$datestr = datestr_format($date_first,"D jS");
						break;
					case 'startMjS':
						$datestr = datestr_format($date_first,"M jS");
						break;
					case 'startMjSY':
						$datestr = datestr_format($date_first,"M jS Y");
						break;
					case 'rangeDjS':
						$datestr = datestr_format($date_first,"D jS") . " thru " . datestr_format($date_last, "D jS");
						break;
					case 'rangeMjS':
						$datestr = datestr_format($date_first,"M jS") . " thru " . datestr_format($date_last, "M jS");
						break;
					case 'rangeMjSY':
						$datestr = datestr_format($date_first,"M jS Y") . " thru " . datestr_format($date_last, "M jS Y");
						break;
					default:
						$datestr = '';
						break;
				}
				if($this->title_date_position && $this->title_date_position == 'before')
					$this->title = $datestr . $this->title;
				else
					$this->title .= $datestr;
			}
			
		}
		public function print_it($shift, $pos, $staff){
			$html = "<table><thead>";
			$this->make_title();
			if($this->title){
				$colcount = 1 + count($this->col);
				$html .= "<tr><th colspan='{$colcount}'>{$this->title}</th></tr>";
			}
			$html .= "<tr><th></th>";
			if($this->col_type == 'shift')
				foreach($this->col as $c){
					if(isset($shift->par[$c]->{$this->shift_format}))
						$html .= "<th>" . $shift->par[$c]->{$this->shift_format} . "</th>";
					else
						$html .= "<th>" . $shift->par[$c]->abbr . "</th>";
				}
			else
				foreach($this->col as $c){
					if($this->datestr_format)
						$html .= "<th>" . datestr_format($c, $this->datestr_format) . "</th>";
					else
						$html .= "<th>" . $c . "</th>";
				}
					
			$html .= "</tr></thead>";
			$html .= "<tbody>";
			foreach($this->row as $r){
				$html .= "<tr>";
				if($this->row_type == 'shift')
					$html .= "<th>" . $shift->par[$r]->name . "</th>";
				else{
					if($this->datestr_format)
						$html .= "<th>" . datestr_format($r, $this->datestr_format) . "</th>";
					else
						$html .= "<th>" . $r . "</th>";
				}
				foreach($this->col as $c){
					if($this->col_type == 'shift'){
						$date = $r;
						$shift = $c;
					}
					else{
						$date = $c;
						$shift = $r;
					}
					$html .= "<td data-{$this->col_type}='{$c}' data-{$this->row_type}='{$r}'>";
					foreach($this->cell as $c)
						if($c->check($date,$shift))
							$html .= $c->print_it($pos,$staff);
					$html .= "</td>";
				}
				$html .= "</tr>";
			}
			$html .= "</tbody></table>";
			return $html;
		}
		
		public function check_cells($date,$shift){
			foreach($cell as $c){
				if($c->check($date,$shift))
					return $c;
			}
			return false;
		}
	}
	}
	{//2) Get the date range, & rota info
	$table= new tTable();
	if($xtemplate->table->date_format)
		$table->datestr_format = $xtemplate->table->date_format;
	if($xtemplate->table->shift_format)
		$table->shift_format = $xtemplate->table->shift_format;
		
	if($xtemplate->table->title_text)
		$table->title_text = $xtemplate->table->title_text;
	if($xtemplate->table->show_title)
		$table->show_title = $xtemplate->table->show_title;
	if($xtemplate->table->title_date_format)
		$table->title_date_format = $xtemplate->table->title_date_format;
	if($xtemplate->table->title_date_position)
		$table->title_date_position = $xtemplate->table->title_date_position;
	/*
		public $title_text, $show_title, $title_date_format, $title_date_position;
		*/
		
	$date = $_POST['date'];
	if(isset($_POST['push']) && $_POST['push'])
		$date = datestr_shift($date, $_POST['push']);
	$table->start_date = start_of_week($date);
	$table->end_date = datestr_shift($table->start_date, 7);
	
	$table->date = array();
	if(count($xtemplate->table->days->day) > 0)
	foreach($xtemplate->table->days->day as $d){
		$table->date[(int)$d] = datestr_shift($table->start_date, (int)$d - 1);
	}
	
	$Dstaff = new staffM($con);
	$Dpos = new dPosLib($con);
	$Dshift = new dShiftLib($con);
	
	$shift = array();
	if(count($xtemplate->table->shifts->shift) > 0)
	foreach($xtemplate->table->shifts->shift as $s){
		if((int)$s)
			$shift[(int)$s] = (int)$s;
	}
	
	$pos = array();
	if(count($xtemplate->info->inst) > 0)
	foreach($xtemplate->info->inst as $i){
		if((int)$i->pos_id)
			$pos[(int)$i->pos_id] = (int)$i->pos_id;
	}
	
	
	if((string)$xtemplate->table->layout == 'shift'){
		$table->col = $shift;
		$table->col_type = 'shift';
		$table->row = $table->date;
		$table->row_type = 'date';
	}
	elseif((string)$xtemplate->table->layout == 'day'){
		$table->row = $shift;
		$table->row_type = 'shift';
		$table->col = $table->date;
		$table->col_type = 'date';
	}
	
	$sql = "SELECT `staff_id`,`date`,`pos_id`,`shift_id` FROM `rota` WHERE `shift_id` IN(" . implode(',',$shift) . ") AND `pos_id` IN(" . implode(',',$pos) . ") AND `date`<='{$table->end_date}' AND `date`>='{$table->start_date}'";
	$query = new rQuery($con, $sql, false, false, true);
	$table->sched = $query->run();
	//print_r($table->sched);
	if(isset($table->sched) && $table->sched && is_array($table->sched) && count($table->sched)>0)
	foreach($table->sched as &$s){
		unset($s[0]);
		unset($s[1]);
		unset($s[2]);
		unset($s[3]);
	}
	
	/*
	[0] => Array ( [staff_id] => 33 [date] => 2014-08-06 [pos_id] => 8 [shift_id] => 10 ) 
	[1] => Array ( [staff_id] => 33 [date] => 2014-08-07 [pos_id] => 8 [shift_id] => 11 ) 
	[2] => Array ( [staff_id] => 33 [date] => 2014-08-09 [pos_id] => 8 [shift_id] => 10 ) 
	[3] => Array ( [staff_id] => 35 [date] => 2014-08-04 [pos_id] => 8 [shift_id] => 10 ) 
	[4] => Array ( [staff_id] => 35 [date] => 2014-08-06 [pos_id] => 8 [shift_id] => 11 )
	*/
	
	}
	{//3) SET CELLS
	$xInfo = $xtemplate->info;
	foreach($xtemplate->table->cells->cell as $c){
		$xCell = false;
		
		foreach($xtemplate->cells->cell as $cc){
			if((string)$cc->name == (string)$c->id){
				$xCell = $cc;
			}
		}
		$cell = new tCell($table->date[(int)$c->day], (int)$c->shift);
		$overflow = array();
		foreach($xInfo->inst as $i){
			if((string)$i->shift_overflow != '' && info_in_cell($xCell, $i->name)){
				$o = explode(",",$i->shift_overflow);
				$overflow = array_merge($overflow, $o);
			}
		}
		make_unique($overflow, (int)$c->shift);
		$records = get_record($table->sched, array('date','shift_id'), array((string)$table->date[(int)$c->day], (int)$c->shift));
		
		if(count($overflow) > 0)
		foreach($overflow as $o){
			$overflow_records = get_record($table->sched, array('date','shift_id'), array((string)$table->date[(int)$c->day], (int)$o));
			if(count($overflow_records) > 0)
				$records = array_merge($records, $overflow_records);
		}
		
		$cell->fill_data($records, $xCell, $xInfo, $Dpos, $Dstaff);
		$table->add_cell($cell);
	}
	}
	// 4) Print & Encode
	
	$data['rota'] = '';
	if($style = $xtemplate->table->inline_css)
		$data['rota'] .= "<div class='print_rota_instance'><style>{$style}</style>";
		
	if($xtemplate->table->header_content || $xtemplate->table->header_style){
		$data['rota'] .= "<div class='template_header' style='{$xtemplate->table->header_style}'>{$xtemplate->table->header_content}</div>";
	}
	$data['rota'] .= $table->print_it($Dshift,$Dpos,$Dstaff);
	if($xtemplate->table->footer_content || $xtemplate->table->footer_style){
		$data['rota'] .= "<div class='template_footer' style='{$xtemplate->table->footer_style}'>{$xtemplate->table->footer_content}</div>";
	}
	
	$data['rota'] .= "</div>";
	
	$data['message'] = "\"{$_POST['template']}\" rota template generated successfully: " . date("H:i:s") . "<br/>";;
	echo json_encode($data);
	break;
	}

	case 'load':
	{// LOAD TEMPLATES
	$data = array();
	
	$file = '../config/rota_templates.xml';
	$xml = new SimpleXMLElement($file, null, true);
	$xtemplate = false;
	foreach($xml->template as $t){
		foreach($t->attributes() as $a => $b){
			if($b == $_POST['name'])
				$xtemplate = $t;
		}
	}
	
	if(!$xtemplate){
		$data['message'] = "Error. Could not find template with the given name.<br/>";
		echo json_encode($data);
		return;
	}
	
	
	$data['name'] = $_POST['name'];
	// TABLE
	$data['table'] = array();
	$data['table']['layout'] = (string) $xtemplate->table->layout;
	$data['table']['css'] = (string) $xtemplate->table->css;
	$data['table']['inline_css'] = (string) $xtemplate->table->inline_css;
	$data['table']['date_format'] = (string) $xtemplate->table->date_format;
	$data['table']['shift_format'] = (string) $xtemplate->table->shift_format;
	$data['table']['show_title'] = (string) $xtemplate->table->show_title;
	$data['table']['title_text'] = (string) $xtemplate->table->title_text;
	$data['table']['title_date_format'] = (string) $xtemplate->table->title_date_format;
	$data['table']['title_date_position'] = (string) $xtemplate->table->title_date_position;
	$data['table']['header_content'] = (string) $xtemplate->table->header_content;
	$data['table']['header_style'] = (string) $xtemplate->table->header_style;
	$data['table']['footer_content'] = (string) $xtemplate->table->footer_content;
	$data['table']['footer_style'] = (string) $xtemplate->table->footer_style;
	$data['table']['shifts'] = array();
	if(count($xtemplate->table->shifts->shift)>0)
	foreach($xtemplate->table->shifts->shift as $s)
		array_push($data['table']['shifts'], (string)$s);
	$data['table']['days'] = array();
	if(count($xtemplate->table->days->day)>0)
	foreach($xtemplate->table->days->day as $d)
		array_push($data['table']['days'], (string)$d);
	$data['table']['cells'] = array();
	if(count($xtemplate->table->cells->cell)>0)
	foreach($xtemplate->table->cells->cell as $c){
		$x = array();
		$x['shift'] = (string)$c->shift;
		$x['day'] = (string)$c->day;
		$x['id'] = (string)$c->id;
		array_push($data['table']['cells'], $x);
	}
	
	// CELLS
	$data['cells'] = array();
	if(count($xtemplate->cells->cel)>0);
	foreach($xtemplate->cells->cell as $c){
		$cell = array();
		$cell['name'] = (string)$c->name;
		$cell['color'] = (string)$c->color;
		$cell['css'] = (string)$c->css;
		$cell['table'] = array();
		if(count($c->table->tr) > 0)
		foreach($c->table->tr as $tr){
			$x = array();
			if(count($tr->td)>0)
			foreach($tr->td as $td){
				$y = array();
				$y['colspan'] = (string)$td->colspan;
				$y['rowspan'] = (string)$td->rowspan;
				$y['content'] = array();
				foreach($td->content as $content){
					array_push($y['content'],(string)$content);
				}
				array_push($x, $y);
			}
			array_push($cell['table'],$x);
		}
		array_push($data['cells'], $cell);
	}
	
	// INFO
	$data['info'] = array();
	if(count($xtemplate->info->inst)>0)
	foreach($xtemplate->info->inst as $i){
		$info = array();
		$info['name'] = (string)$i->name;
		$info['color'] = (string)$i->color;
		$info['pos_id'] = (string)$i->pos_id;
		$info['name_disp'] = (string)$i->name_disp;
		$info['pos_disp'] = (string)$i->pos_disp;
		$info['name_delim'] = (string)$i->name_delim;
		$info['pos_delim'] = (string)$i->pos_delim;
		$info['pos_loc'] = (string)$i->pos_loc;
		$info['empty_hide'] = (string)$i->empty_hide;
		$info['pos_css'] = (string)$i->pos_css;
		$info['name_css'] = (string)$i->name_css;
		$info['div_css'] = (string)$i->div_css;
		$info['empty_css'] = (string)$i->empty_css;
		$info['shift_overflow'] = (string)$i->shift_overflow;
		$info['name_delim_string'] = (string)$i->name_delim_string;
		$info['pos_replicate'] = (string)$i->pos_replicate;
		array_push($data['info'],$info);
	}
	
	$data['message'] = "\"{$_POST['name']}\" loaded successfully: " . date("H:i:s") . "<br/>";;
	
	echo json_encode($data);
	}
	break;

	case 'save':
	{// SAVE TEMPLATE
	$data = $_POST['data'];
	// LOADS XML FILE
	$file = '../config/rota_templates.xml';
	$xml = new SimpleXMLElement($file, null, true);
	
	// ELIMINATES TEMPLATES WITH IDENTICAL NAMES
	foreach($xml->template as $t){
		foreach($t->attributes() as $a => $b){
			if($a == 'name' && $b == $data['name']){
				unset($t[0]);
				break 2;
			}
		}
	}
	
	// CREATES NEW TEMPLATE NODE
	$xtemplate = $xml->addChild('template');
	$xtemplate->addAttribute('name',$data['name']);
	
	if(isset($data['table'])){// TABLE
		$dtable = $data['table'];
		$xtable = $xtemplate->addChild('table');
		
		if(isset($dtable['layout']))
			$xtable->addChild('layout',$dtable['layout']);
		if(isset($dtable['css']))
			$xtable->addChild('css',$dtable['css']);
		if(isset($dtable['inline_css']))
			$xtable->addChild('inline_css',$dtable['inline_css']);
		if(isset($dtable['date_format']))
			$xtable->addChild('date_format',$dtable['date_format']);
		if(isset($dtable['shift_format']))
			$xtable->addChild('shift_format',$dtable['shift_format']);
		if(isset($dtable['show_title']))
			$xtable->addChild('show_title',$dtable['show_title']);
		if(isset($dtable['title_text']))
			$xtable->addChild('title_text',$dtable['title_text']);
		if(isset($dtable['title_date_format']))
			$xtable->addChild('title_date_format',$dtable['title_date_format']);
		if(isset($dtable['title_date_position']))
			$xtable->addChild('title_date_position',$dtable['title_date_position']);
		if(isset($dtable['header_content']))
			$xtable->addChild('header_content',$dtable['header_content']);
		if(isset($dtable['header_style']))
			$xtable->addChild('header_style',$dtable['header_style']);
		if(isset($dtable['footer_content']))
			$xtable->addChild('footer_content',$dtable['footer_content']);
		if(isset($dtable['footer_style']))
			$xtable->addChild('footer_style',$dtable['footer_style']);
		$xshifts = $xtable->addChild('shifts');
		if(isset($dtable['shifts']) && is_array($dtable['shifts']) && count($dtable['shifts']) > 0)
			foreach($dtable['shifts'] as $s)
				$xshift = $xshifts->addChild('shift',$s);
		
		$xdays = $xtable->addChild('days');
		if(isset($dtable['days']) && is_array($dtable['days']) && count($dtable['days']) > 0)
			foreach($dtable['days'] as $s)
				$xday = $xdays->addChild('day',$s);
		
		$xcells = $xtable->addChild('cells');
		if(isset($dtable['cells']) && is_array($dtable['cells']) && count($dtable['cells']))
			foreach($dtable['cells'] as $c){
				$xcell = $xcells->addChild('cell');
				$xcell->addChild('shift',$c['shift']);
				$xcell->addChild('day',$c['day']);
				$xcell->addChild('id',$c['id']);
			}
	}
	if(isset($data['cells'])){// CELL
		$dcells = $data['cells'];
		$xcells = $xtemplate->addChild('cells');
		if(is_array($dcells) && count($dcells)>0)
		foreach($dcells as $dcell){
			$xcell = $xcells->addChild('cell');
			if(isset($dcell['name']))
				$xcell->addChild('name',$dcell['name']);
			if(isset($dcell['color']))
				$xcell->addChild('color',$dcell['color']);
			if(isset($dcell['css']))
				$xcell->addChild('css',$dcell['css']);
			
			if(isset($dcell['table']) && is_array($dcell['table']) && count($dcell['table'])>0){
				$xcell_table = $xcell->addChild('table');
				foreach($dcell['table'] as $tr){
					$xtr = $xcell_table->addChild('tr');
					if(isset($tr) && is_array($tr) && count($tr)>0)
					foreach($tr as $td){
						$xtd = $xtr->addChild('td');
						if(isset($td['colspan']))
							$xtd->addChild('colspan',$td['colspan']);
						if(isset($td['rowspan']))
							$xtd->addChild('rowspan',$td['rowspan']);
						if(isset($td['content']) && is_array($td['content']) && count($td['content'])>0)
						foreach($td['content'] as $c)
							$xtd->addChild('content',$c);
					}
				}
			}
		}
	}
	if(isset($data['info'])){// INFO
		$dinfo = $data['info'];
		$xinfo = $xtemplate->addChild('info');
		if(is_array($dinfo) && count($dinfo)>0)
		foreach($dinfo as $dinst){
			$xinst = $xinfo->addChild('inst');
			if(isset($dinst['name']))
				$xinst->addChild('name',$dinst['name']);
			if(isset($dinst['color']))
				$xinst->addChild('color',$dinst['color']);
			if(isset($dinst['pos_id']))
				$xinst->addChild('pos_id',$dinst['pos_id']);
			if(isset($dinst['name_disp']))
				$xinst->addChild('name_disp',$dinst['name_disp']);
			if(isset($dinst['pos_disp']))
				$xinst->addChild('pos_disp',$dinst['pos_disp']);
			if(isset($dinst['name_delim']))
				$xinst->addChild('name_delim',$dinst['name_delim']);
			if(isset($dinst['pos_delim']))
				$xinst->addChild('pos_delim',$dinst['pos_delim']);
			if(isset($dinst['pos_loc']))
				$xinst->addChild('pos_loc',$dinst['pos_loc']);
			if(isset($dinst['empty_hide']))
				$xinst->addChild('empty_hide',$dinst['empty_hide']);
			if(isset($dinst['pos_css']))
				$xinst->addChild('pos_css',$dinst['pos_css']);
			if(isset($dinst['name_css']))
				$xinst->addChild('name_css',$dinst['name_css']);
			if(isset($dinst['div_css']))
				$xinst->addChild('div_css',$dinst['div_css']);
			if(isset($dinst['empty_css']))
				$xinst->addChild('empty_css',$dinst['empty_css']);
			if(isset($dinst['shift_overflow']))
				$xinst->addChild('shift_overflow',$dinst['shift_overflow']);
			if(isset($dinst['name_delim_string']))
				$xinst->addChild('name_delim_string',$dinst['name_delim_string']);
			if(isset($dinst['pos_replicate']))
				$xinst->addChild('pos_replicate',$dinst['pos_replicate']);
		}
	}
	
	// SAVE IT
	if($xml->asXML($file))
		echo "Saved \"{$data['name']}\" successfully: " . date("H:i:s") . "<br/>";
	else
		echo "Error saving \"{$data['name']}\": " . date("H:i:s") . "<br/>";
	}
	break;
}

?>
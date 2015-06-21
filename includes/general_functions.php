<?php
/*
This document contains basic functions regarding the MYSQL database and data gathering. Used for most everything.
*/

require_once('server_info.php');

class conFig{

	public $xml;
	// useable outside...
	public $datestr,$file;
	public $day = array();
	public $pref = array();
	
	function __construct($depth = 1, $file = 'config/master_config.xml'){
		if($depth > 0)
		for($i=0; $i<$depth; $i++)
			$file = '../' . $file;
		$this->file = $file;
		$this -> init($this->file);
	}
	public function init($file = 'config/master_config.xml'){
		$this->xml = simplexml_load_file($file);
		$this->datestr = $this->xml->datestr;
		$this->day_init();
		$this->pref_init();
	}
	public function show(){
		echo $this->xml->asXML();
		echo "<br/>";
		print_r($this->xml);
	}
	public function update(){
		if($this->xml->asXML($this->file))
			return true;
		else
			return false;
	}
	
	// FUNCTIONS FOR PREPARING COMMON INFO
	public function day_init(){
		foreach($this->xml->days->day as $d){
			$day = array(
				'id' => (int)$d->id,
				'name' => (string)$d->name,
				'abbr' => (string)$d->abbr,
				'active' => ((string)$d->active == 'true')? true:false
			);
			$this->day[(int)$d->id] = $day;
		}
	}
	
	public function day_active_array(){
		$day = array();
		foreach($this->day as $d){
			if($d['active'])
				$day[$d['id']] = $d;
		}
		return $day;
	}
	public function pref_init(){
		foreach($this->xml->prefs->pref as $p){
			$pref = array(
				'id' => (int)$p->id,
				'name' => (string) $p->name,
				'mod' => (int)$p->mod
			);
			$this->pref[(int)$p->id] = $pref;
		}
	}
	
	// FUNCTIONS FOR GETTING INFORMATION WITHOUT NAVIGATING THE XML FILE
	public function datestr_format(){
		return $this->xml->datestr->format;
	}
	
	public function staff_type($i){
		foreach($this->xml->staffTypes->type as $t)
			if($t->id == $i)
				return $t->name;
	}
	public function staff_type_count(){
		return count($this->xml->staffTypes->type);
	}
	public function staff_type_array(){ // returns md array. [i][id|name]
		$ret = array();
		foreach($this->xml->staffTypes->type as $t){
			array_push($ret, array('id' => $t->id, 'name' => $t->name));
		}
		return $ret;	
	}
	public function settings_staff_object(){ // return format: $ret[i]->type|day[val]->id|shift[val]
		$ret = array();
		foreach($this->xml->staffAv as $s){
			$obj = new stdClass();
			$obj->type = (int)$s->type;
			$obj->day = array();
			foreach($s->day as $d){
				$o = new stdClass();
				$o->id = (int)$d->id;
				$o->shift = array();
				foreach($d->shift as $s){
					$o->shift[(int)$s] = (int)$s;
				}
				$obj->day[$o->id] = $o;
			}
			$ret[$obj->type] = $obj;
		}
		return $ret;
	}
}

class rqFields{ // THIS ACTS AS THE METHOD OF PUTTING IN DATA FOR DB FUNCTIONS BELOW.
	public $field = false;
	public $value = false;
	public $where_field = false;
	public $where_value = false;
	
	function __construct($f=false,$v=false,$wf=false,$wv=false){
		$this->init($f,$v,$wf,$wv);
	}
	
	public function init($f,$v,$wf,$wv){
		$this->field = $f;
		$this->value = $v;
		$this->where_field = $wf;
		$this->where_value = $wv;
	}
}
class rQuery{//($con, $sql = false, $param_type = false, $param = false,$force_array = false)
	public $con = false;
	public $sql = false; //query
	public $param = false; // parameters for bind_param, array
	public $param_type = false;
	public $force_array = false;
	public $result = null;
	
	function __construct($con, $sql = false, $param_type = false, $param = false,$force_array = false){
		$this->init($con, $sql,$param_type,$param,$force_array);
	}
	public function init($con, $sql = false,$param_type = false,$param = false,$force_array = false){
		$this->con = $con;
		$this->sql = $sql;
		$this->param_type = $param_type;
		$this->param = $param;
		$this->force_array = $force_array;
	}
	
	public function run($debug = false, $clean_max_id=false){
		// Prepares the statment
		$stmt = $this->con->prepare($this->sql);
		if($debug) echo "SQL Raw: {$this->sql}<br/>";
		// Binds
		if($this->param_type && $this->param){
			$bind_names[] = $this->param_type;
			for ($i=0; $i<count($this->param);$i++) 
			{	$bind_name = 'bind' . $i;
				$$bind_name = $this->param[$i];
				$bind_names[] = &$$bind_name;
			}
			call_user_func_array(array($stmt,'bind_param'),$bind_names);
		}
		// Executes and gets the results
		$this->result = $stmt->execute();
		if(strpos($this->sql, "SELECT") !== false){
			$result = $stmt->get_result();
			$this->result = null;
			$this->result = array();
			if($result)
			while($row = $result->fetch_array())
				array_push($this->result, $row);
			
			// Sets to array or just value
//			if(count($this->result) == 1 || !$this->force_array)
			if(count($this->result) == 1 && !$this->force_array)
				$this->result = $this->result[0];
			elseif(count($this->result) <1)
				$this->result = false;
			
			if($clean_max_id){
				if($this->result && is_array($this->result) && count($this->result)>0)
				foreach($this->result as $i=>$r){
					$this->result[$i] = array_clean($r, $clean_max_id);
				}
			}
			if($debug)
				$this->show();
			return $this->result;
		}
		else{
			if($debug)
				$this->show();
			return $this->result;
		}
	}
	
	public function show(){
		echo "SQL: {$this->sql} <br/>";
		print_r($this->result);
		echo "<br/>";
	}
}

{// DATABASE FUNCTIONS
// SINGLES
function db_update($con,$table,$field,$value,$where_field = false,$where_value = false, $debug = false){
	// ESCAPE THE BAD THINGS:
	if(is_array($value)) foreach($value as &$v) $v = $con->real_escape_string($v);
	else $value = $con->real_escape_string($value);
	if(is_array($where_value)) foreach($where_value as &$v) $v = $con->real_escape_string($v);
	else $where_value = $con->real_escape_string($where_value);
	
	// PREPARE SQL QUERY
	$sql = "UPDATE $table SET ";
	if(is_array($field) && is_array($value)){
		foreach($field as $i=>$f) 
			$sql .= "`$f`='{$value[$i]}',";
		$sql = substr($sql, 0, -1);
	}
	else $sql .= "`$field`='$value'";
	if($where_field && $where_value){
			$sql .= ' WHERE ';
		if(is_array($where_field) && is_array($where_value)){
			foreach($where_field as $i=>$f)
				$sql .= "`$f`='{$where_value[$i]}' AND ";
			$sql = substr($sql, 0, -5);	
		}
		else $sql .= "`$where_field`='$where_value'";
	}
	if($debug) echo "db_update: $sql <br/>";
	
	// EXECUTE THE QUERY
	$stmt = $con->prepare($sql);
	$ret = $stmt->execute();
	$stmt->close();
	return $ret;
}
function db_insert($con,$table,$field,$value,$select_id = false, $debug = false){
	// ESCAPE THE BAD THINGS:
	if(is_array($value)) foreach($value as &$v)	$v = $con->real_escape_string($v);
	else $value = $con->real_escape_string($value);
	if($debug)
		print_r($value);
	
	// PREPARE SQL QUERY
	
	$sql = "INSERT INTO $table (";
	if(is_array($field)){
		foreach($field as $f) 
			$sql .= "`$f`,";
		$sql = substr($sql, 0, -1);
	}
	
	else $sql .= "`$field`";
	$sql .= ") VALUES (";
	if(is_array($value)){
		foreach($value as &$v) 
			$sql .= "'$v',";
		$sql = substr($sql, 0, -1);
	}
	else $sql .= "'$value'";
	$sql .= ")";
	if($debug)	echo "db_insert: $sql <br/>";
	// EXECUTE THE QUERY
	$stmt = $con->prepare($sql);
	$ret = $stmt->execute();
	$stmt->close();
	if(!$select_id)
		return $ret;
	else{ // NOTE: THIS ONLY WORKS IF THE AUTO INCREMENT IS HIGHER THAN ANY OTHER RECORDS. YOU COULD SCREW THIS UP BY MESSING WITH THE AI & NOT DELETING RECORDS WITH A HIGHER ID, BUT WORRY ABOUT THAT LATER.
		$sql = "SELECT `$select_id` FROM $table ORDER BY `$select_id` DESC LIMIT 1";
		if($debug) echo "db_insert_get_id:$sql<br/>";
		$stmt = $con->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		$stmt->close();
		return $id;
	}
}
function db_delete($con,$table, $where_field = false, $where_value = false, $debug = false){
	//ESCAPE THE BAD THINGS
	if(is_array($where_value)) foreach($where_value as &$v) $v = $con->real_escape_string($v);
	else $where_value = $con->real_escape_string($where_value);
	
	//PREPARE THE SQL
	$sql = "DELETE FROM $table";	
	if($where_field && $where_value){
			$sql .= ' WHERE ';
		if(is_array($where_field) && is_array($where_value)){
			foreach($where_field as $i=>$f)
				$sql .= "$f='{$where_value[$i]}' AND ";
			$sql = substr($sql, 0, -5);	
		}
		else $sql .= "$where_field='$where_value'";
	}
	if($debug) echo "db_delete: $sql <br/>";
// EXECUTE THE QUERY
	$stmt = $con->prepare($sql);
	$ret = $stmt->execute();
	$stmt->close();
	return $ret;
}
function db_select($con,$table,$field,$where_field = false,$where_value = false, $force_array = false, $limit = false, $debug = false){
	// ESCAPE THE BAD THINGS
	if(is_array($where_value)) foreach($where_value as &$v) $v = $con->real_escape_string($v);
	elseif($where_value) $where_value = $con->real_escape_string($where_value);
	// PREPARE THE QUERY
	
	/*
	$sql = "SELECT $field FROM $table";
	*/
	$sql = "SELECT ";
	if(is_array($field)){
		foreach($field as $f) 
			$sql .= "$f,";
		$sql = substr($sql, 0, -1);
	}
	else $sql .= "$field";
	$sql .= " FROM $table";
	if($where_field && $where_value){
			$sql .= ' WHERE ';
		if(is_array($where_field) && is_array($where_value)){
			foreach($where_field as $i=>$f)
				$sql .= "$f='{$where_value[$i]}' AND ";
			$sql = substr($sql, 0, -5);	
		}
		else $sql .= "$where_field='$where_value'";
	}
	if($limit !== false)
		$sql .= " LIMIT $limit";
	if($debug) echo "db_select:$sql<br/>";
	
	// EXECUTE THE QUERY
	$stmt = $con->prepare($sql);
	$stmt->execute();
	// SET THE BINDS CORRECTLY, BASED 
	if(is_array($field)){
		$ret = array();
		if(count($field) == 1)
			$stmt->bind_result($ret[$field[0]]);
		elseif(count($field == 2))
			$stmt->bind_result($ret[$field[0]], $ret[$field[1]]);
		elseif(count($field == 3))
			$stmt->bind_result($ret[$field[0]], $ret[$field[1]], $ret[$field[2]]);
		elseif(count($field == 4))
			$stmt->bind_result($ret[$field[0]], $ret[$field[1]], $ret[$field[2]], $ret[$field[3]]);
		elseif(count($field == 5))
			$stmt->bind_result($ret[$field[0]], $ret[$field[1]], $ret[$field[2]], $ret[$field[3]], $ret[$field[4]]);
	}
	else
	$stmt->bind_result($ret);
	$ret_array = array();
	while($stmt->fetch())
		array_push($ret_array, $ret);
	$stmt->close();
	if(count($ret_array)<1)
		return false;
	if($force_array || count($ret_array) > 1)
		return $ret_array;
	elseif(count($ret_array) == 1)
		return $ret_array[0];
}
function db_select_all($con, $table, $where_field = false,$where_value = false, $force_array = false, $limit = false, $debug = false){
	// ESCAPE THE BAD THINGS
	if(is_array($where_value)) foreach($where_value as &$v) $v = $con->real_escape_string($v);
	else $where_value = $con->real_escape_string($where_value);
	
	// PREPARE THE QUERY
	$sql = "SELECT * FROM $table";
	if($where_field && $where_value){
		$sql .= ' WHERE ';
		if(is_array($where_field) && is_array($where_value)){
			foreach($where_field as $i=>$f)
				$sql .= "$f='{$where_value[$i]}' AND ";
			$sql = substr($sql, 0, -5);	
		}
		else $sql .= "$where_field='$where_value'";
	}
	if($limit !== false)
		$sql .= " LIMIT $limit";
	if($debug) echo "db_select_all: $sql <br/>";
	
	// EXECUTE THE QUERY AND RETURN THE INFO
	$ret = array();
	$stmt = $con->prepare($sql);
	$stmt->execute();
	
	$result = $stmt->get_result();
	while($row = $result->fetch_array(MYSQLI_ASSOC))
		array_push($ret, $row);
	
	
	if($force_array || count($ret) > 1)
		return $ret;
	elseif(count($ret) == 1)
		return $ret[0];
	elseif(count($ret) < 1)
		return false;
	
}

// MULTI
// $rQuery - array of rQuery class
function db_update_multi($con, $table, $rqFields_array, $debug = false){ 
	foreach($$rqFields_array as $q)
		db_update($con,$table,$q->field,$q->value,$q->where_field,$q->where_value,$debug);
}
function db_insert_multi($con,$table,$rqFields_array,$debug=false){
	foreach($rqFields_array as $q)
		db_insert($con,$table,$q->field,$q->value,$debug);
}
function db_delete_multi($con,$table,$rqFields_array,$debug=false){
	foreach($rqFields_array as $q)
		db_delete($con,$table,$q->where_field,$q->where_value, $debug);
}
}

{// DATE FUNCTIONS
	function start_of_week($datestr){
		return datestr_shift($datestr, -datestr_format($datestr, 'w'));
	}
	function end_of_week($datestr){
		return datestr_shift($datestr, 7-datestr_format($datestr, 'w'));
	}
	function start_of_month($datestr){
		return datestr_shift($datestr, 1-datestr_format($datestr, 'd'));
	}
	function end_of_month($datestr){
		return datestr_shift($datestr, datestr_format($datestr, 't') - datestr_format($datestr, 'd'));
	}
	
	function week_month_array($datestr){
		$start_month = start_of_month($datestr);
		$end_month = end_of_month($datestr);
		$start_date = datestr_shift($start_month, 1-datestr_format($start_month, 'j'));
		$date = $start_date;
		$weeks = array();
		$week_count = 4;
		$days = datestr_format($start_month, 'j') + 1-datestr_format($start_month, 'd');
		if($days > 28)
			$week_count ++;
		if($days > 35)
			$week_count ++;
		for($i=0; $i<$week_count; $i++){
			array_push($weeks, datestr_array($date));
			$date = datestr_shift($date, 7);
		}
		return $weeks;
	}
	
	function week_month_array_accurate($datestr){
		$start_month = start_of_month($datestr);
		$end_month = end_of_month($datestr);
		$start_date = datestr_shift($start_month, -1-datestr_format($start_month, 'w'));
		$end_date = datestr_shift($end_month, 5-datestr_format($end_month,'w'));
		
		$weeks = array();
		$date = $start_date;
		while($date < $end_date ){
			$week = array();
			for($i=0; $i<7; $i++){
				$date = datestr_shift($date,1);
				array_push($week, $date);
			}
			array_push($weeks, $week);
		}
		
		return $weeks;
	}
	
	function datestr_cur(){
		return date(DATESTR_FORMAT_STD);
	}
	function datestr_shift($datestr, $shift = 0){
		if($shift > 0)
			$string = "+$shift day";
		elseif($shift <0)
			$string = "$shift day";
		else
			return $datestr;
		return date(DATESTR_FORMAT_STD, strtotime($string . " " . $datestr));
	}
	function datestr_format($datestr, $format = false){
		if(!$format)
			$format = DATESTR_FORMAT_STD;
		return date($format,strtotime($datestr));
	}
	function datestr_array($datestr, $type = false){ // DOESN'T INCLUDE THE TURNING DAYS ON/OFF, BUT STILL WORKS
		if(!$type)
			$type = DATESTR_ARRAY_WEEK;
		
		$start_datestr = $datestr;
		$count = 0;
		switch($type){
			case DATESTR_ARRAY_WEEK:
			case DATESTR_ARRAY_WEEK_INCLUDE:
			case DATESTR_ARRAY_WEEK_EXACT:
			case DATESTR_ARRAY_WEEK_EXCLUDE:
				$start_datestr = start_of_week($datestr);//datestr_shift($datestr, -datestr_format($datestr, 'w'));
				$count = 7;
				break;
			case DATESTR_ARRAY_MONTH:
			case DATESTR_ARRAY_MONTH_INCLUDE:
				$start_month = start_of_month($datestr);
				echo "start_month:$start_month<br/>";
				$start_datestr = start_of_week($start_month); // datestr_shift($datestr, -datestr_format($datestr, 'w'));
				echo "start_datestr:$start_datestr<br/>";
				if(datestr_format($start_datestr, 'md') == '0201')
					$count = 28;
				else
					$count = 35;
				break;
			case DATESTR_ARRAY_MONTH_EXACT:
			case DATESTR_ARRAY_MONTH_EXCLUDE:
				$start_datestr = start_of_month($datestr);
				$count = datestr_format($start_datestr, 't');
				break;
			default:
				return false;
		}
		$ret = array();
		for($i=0; $i<$count; $i++){
			array_push($ret, datestr_shift($start_datestr, $i));
			
		}
		
		return $ret;
	}
	function datestr_array_start_end($start_date, $end_date){
		$datestr = $start_date;
		$array = array();
		while($datestr <= $end_date){
			array_push($array, $datestr);
			$datestr = datestr_shift($datestr,1);
		}
		return $array;
	}
	function datestr_array_with_config($config,$start_date, $end_date){
		$day_active_array = $config->day_active_array();
		$datestr = $start_date;
		$array = array();
		while($datestr <= $end_date){
			$day = datestr_format($datestr, 'w')+1;
			foreach($day_active_array as $d){
				if($d['id'] == $day)
					array_push($array, $datestr);
			}
			$datestr = datestr_shift($datestr,1);
		}
		return $array;
	}
}

{// EMAIL FUNCTIONS

function gt_mail($to,$subject,$body,$cc_sender = TRUE,$alt_body = null){ // UNVERIFIED

/* THINGS TO VERIFY:
	1) Timezone works uncommented
	DONE - 2) DEBUG is off
	3) multiple emails work
*/
//	date_default_timezone_set('Etc/UTC');
	require_once('../includes/phpmailer/PHPMailerAutoload.php');
	$sender_address = 'rota@gardentomb.com';
	$sender_name = 'GT Rotas';
	$mail = new PHPMailer;
	
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	$mail->SMTPDebug = 0; // 0 - off, 1 - client messages, 2 - client and server messages
	
	$mail->Host = "172.16.160.230";
	$mail->Port = 25;
	$mail->SMTPAuth = true;
	$mail->Username = "Rota";
	$mail->Password = "Passw0rd";
	$mail->setFrom($sender_address, $sender_name);
	//$mail->setFrom('rota@gardentomb.com', 'GT Rotas');
	//Set an alternative reply-to address
	//$mail->addReplyTo('replyto@example.com', 'First Last');
	
	if(!is_array($to)){
		//echo "To String: {$to}<br/>";
		$mail->addAddress($to);
	}
	elseif(count($to)>0){
		//echo "Array to:"; print_r($to); echo "<br/>";
		foreach($to as $t){
			$mail->addAddress($t);
		}
	}
	if($cc_sender){
		$mail->AddCC($sender_address,$sender_name);
	}
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->IsHTML(true);
	if($alt_body){
		$mail->AltBody = $alt_body; // plain text body if html cannot be read by mail client
	}
	
	//send the message, check for errors
	if (!$mail->send()) {
		return false; //array('success'=>false, 'message'=>$mail->ErrorInfo);
	} else {
		return true;
	}
}

}
{// SESSION FUNCTIONS
	function session_update($var, $val){
		if (session_status() == PHP_SESSION_NONE)
			session_start();
		$_SESSION[$var] = $val;
	}
	function session_get($var){
		if (session_status() == PHP_SESSION_NONE)
			session_start();
		if(isset($_SESSION[$var]))
			return $_SESSION[$var];
		else
			return false;
	}
}
{// MISC FUNCTIONS
	function standard_date_val(){
		if($x=session_get('date'))
			return $x; 
		else 
			return datestr_cur();
	}
	function option_color_list(){
		return "<option value='none'>None</option>
			<option style='background-color:AliceBlue' value='AliceBlue'>AliceBlue</option>
			<option style='background-color:AntiqueWhite' value='AntiqueWhite'>AntiqueWhite</option>
			<option style='background-color:Aqua' value='Aqua'>Aqua</option>
			<option style='background-color:Aquamarine' value='Aquamarine'>Aquamarine</option>
			<option style='background-color:Azure' value='Azure'>Azure</option>
			<option style='background-color:Beige' value='Beige'>Beige</option>
			<option style='background-color:Bisque' value='Bisque'>Bisque</option>
			<option style='background-color:Black' value='Black'>Black</option>
			<option style='background-color:BlanchedAlmond' value='BlanchedAlmond'>BlanchedAlmond</option>
			<option style='background-color:Blue' value='Blue'>Blue</option>
			<option style='background-color:BlueViolet' value='BlueViolet'>BlueViolet</option>
			<option style='background-color:Brown' value='Brown'>Brown</option>
			<option style='background-color:BurlyWood' value='BurlyWood'>BurlyWood</option>
			<option style='background-color:CadetBlue' value='CadetBlue'>CadetBlue</option>
			<option style='background-color:Chartreuse' value='Chartreuse'>Chartreuse</option>
			<option style='background-color:Chocolate' value='Chocolate'>Chocolate</option>
			<option style='background-color:Coral' value='Coral'>Coral</option>
			<option style='background-color:CornflowerBlue' value='CornflowerBlue'>CornflowerBlue</option>
			<option style='background-color:Cornsilk' value='Cornsilk'>Cornsilk</option>
			<option style='background-color:Crimson' value='Crimson'>Crimson</option>
			<option style='background-color:Cyan' value='Cyan'>Cyan</option>
			<option style='background-color:DarkBlue' value='DarkBlue'>DarkBlue</option>
			<option style='background-color:DarkCyan' value='DarkCyan'>DarkCyan</option>
			<option style='background-color:DarkGoldenRod' value='DarkGoldenRod'>DarkGoldenRod</option>
			<option style='background-color:DarkGray' value='DarkGray'>DarkGray</option>
			<option style='background-color:DarkGreen' value='DarkGreen'>DarkGreen</option>
			<option style='background-color:DarkKhaki' value='DarkKhaki'>DarkKhaki</option>
			<option style='background-color:DarkMagenta' value='DarkMagenta'>DarkMagenta</option>
			<option style='background-color:DarkOliveGreen' value='DarkOliveGreen'>DarkOliveGreen</option>
			<option style='background-color:DarkOrange' value='DarkOrange'>DarkOrange</option>
			<option style='background-color:DarkOrchid' value='DarkOrchid'>DarkOrchid</option>
			<option style='background-color:DarkRed' value='DarkRed'>DarkRed</option>
			<option style='background-color:DarkSalmon' value='DarkSalmon'>DarkSalmon</option>
			<option style='background-color:DarkSeaGreen' value='DarkSeaGreen'>DarkSeaGreen</option>
			<option style='background-color:DarkSlateBlue' value='DarkSlateBlue'>DarkSlateBlue</option>
			<option style='background-color:DarkSlateGray' value='DarkSlateGray'>DarkSlateGray</option>
			<option style='background-color:DarkTurquoise' value='DarkTurquoise'>DarkTurquoise</option>
			<option style='background-color:DarkViolet' value='DarkViolet'>DarkViolet</option>
			<option style='background-color:DeepPink' value='DeepPink'>DeepPink</option>
			<option style='background-color:DeepSkyBlue' value='DeepSkyBlue'>DeepSkyBlue</option>
			<option style='background-color:DimGray' value='DimGray'>DimGray</option>
			<option style='background-color:DodgerBlue' value='DodgerBlue'>DodgerBlue</option>
			<option style='background-color:FireBrick' value='FireBrick'>FireBrick</option>
			<option style='background-color:FloralWhite' value='FloralWhite'>FloralWhite</option>
			<option style='background-color:ForestGreen' value='ForestGreen'>ForestGreen</option>
			<option style='background-color:Fuchsia' value='Fuchsia'>Fuchsia</option>
			<option style='background-color:Gainsboro' value='Gainsboro'>Gainsboro</option>
			<option style='background-color:GhostWhite' value='GhostWhite'>GhostWhite</option>
			<option style='background-color:Gold' value='Gold'>Gold</option>
			<option style='background-color:GoldenRod' value='GoldenRod'>GoldenRod</option>
			<option style='background-color:Gray' value='Gray'>Gray</option>
			<option style='background-color:Green' value='Green'>Green</option>
			<option style='background-color:GreenYellow' value='GreenYellow'>GreenYellow</option>
			<option style='background-color:HoneyDew' value='HoneyDew'>HoneyDew</option>
			<option style='background-color:HotPink' value='HotPink'>HotPink</option>
			<option style='background-color:IndianRed' value='IndianRed'>IndianRed</option>
			<option style='background-color:Indigo' value='Indigo'>Indigo</option>
			<option style='background-color:Ivory' value='Ivory'>Ivory</option>
			<option style='background-color:Khaki' value='Khaki'>Khaki</option>
			<option style='background-color:Lavender' value='Lavender'>Lavender</option>
			<option style='background-color:LavenderBlush' value='LavenderBlush'>LavenderBlush</option>
			<option style='background-color:LawnGreen' value='LawnGreen'>LawnGreen</option>
			<option style='background-color:LemonChiffon' value='LemonChiffon'>LemonChiffon</option>
			<option style='background-color:LightBlue' value='LightBlue'>LightBlue</option>
			<option style='background-color:LightCoral' value='LightCoral'>LightCoral</option>
			<option style='background-color:LightCyan' value='LightCyan'>LightCyan</option>
			<option style='background-color:LightGoldenRodYellow' value='LightGoldenRodYellow'>LightGoldenRodYellow</option>
			<option style='background-color:LightGray' value='LightGray'>LightGray</option>
			<option style='background-color:LightGreen' value='LightGreen'>LightGreen</option>
			<option style='background-color:LightPink' value='LightPink'>LightPink</option>
			<option style='background-color:LightSalmon' value='LightSalmon'>LightSalmon</option>
			<option style='background-color:LightSeaGreen' value='LightSeaGreen'>LightSeaGreen</option>
			<option style='background-color:LightSkyBlue' value='LightSkyBlue'>LightSkyBlue</option>
			<option style='background-color:LightSlateGray' value='LightSlateGray'>LightSlateGray</option>
			<option style='background-color:LightSteelBlue' value='LightSteelBlue'>LightSteelBlue</option>
			<option style='background-color:LightYellow' value='LightYellow'>LightYellow</option>
			<option style='background-color:Lime' value='Lime'>Lime</option>
			<option style='background-color:LimeGreen' value='LimeGreen'>LimeGreen</option>
			<option style='background-color:Linen' value='Linen'>Linen</option>
			<option style='background-color:Magenta' value='Magenta'>Magenta</option>
			<option style='background-color:Maroon' value='Maroon'>Maroon</option>
			<option style='background-color:MediumAquaMarine' value='MediumAquaMarine'>MediumAquaMarine</option>
			<option style='background-color:MediumBlue' value='MediumBlue'>MediumBlue</option>
			<option style='background-color:MediumOrchid' value='MediumOrchid'>MediumOrchid</option>
			<option style='background-color:MediumPurple' value='MediumPurple'>MediumPurple</option>
			<option style='background-color:MediumSeaGreen' value='MediumSeaGreen'>MediumSeaGreen</option>
			<option style='background-color:MediumSlateBlue' value='MediumSlateBlue'>MediumSlateBlue</option>
			<option style='background-color:MediumSpringGreen' value='MediumSpringGreen'>MediumSpringGreen</option>
			<option style='background-color:MediumTurquoise' value='MediumTurquoise'>MediumTurquoise</option>
			<option style='background-color:MediumVioletRed' value='MediumVioletRed'>MediumVioletRed</option>
			<option style='background-color:MidnightBlue' value='MidnightBlue'>MidnightBlue</option>
			<option style='background-color:MintCream' value='MintCream'>MintCream</option>
			<option style='background-color:MistyRose' value='MistyRose'>MistyRose</option>
			<option style='background-color:Moccasin' value='Moccasin'>Moccasin</option>
			<option style='background-color:NavajoWhite' value='NavajoWhite'>NavajoWhite</option>
			<option style='background-color:Navy' value='Navy'>Navy</option>
			<option style='background-color:OldLace' value='OldLace'>OldLace</option>
			<option style='background-color:Olive' value='Olive'>Olive</option>
			<option style='background-color:OliveDrab' value='OliveDrab'>OliveDrab</option>
			<option style='background-color:Orange' value='Orange'>Orange</option>
			<option style='background-color:OrangeRed' value='OrangeRed'>OrangeRed</option>
			<option style='background-color:Orchid' value='Orchid'>Orchid</option>
			<option style='background-color:PaleGoldenRod' value='PaleGoldenRod'>PaleGoldenRod</option>
			<option style='background-color:PaleGreen' value='PaleGreen'>PaleGreen</option>
			<option style='background-color:PaleTurquoise' value='PaleTurquoise'>PaleTurquoise</option>
			<option style='background-color:PaleVioletRed' value='PaleVioletRed'>PaleVioletRed</option>
			<option style='background-color:PapayaWhip' value='PapayaWhip'>PapayaWhip</option>
			<option style='background-color:PeachPuff' value='PeachPuff'>PeachPuff</option>
			<option style='background-color:Peru' value='Peru'>Peru</option>
			<option style='background-color:Pink' value='Pink'>Pink</option>
			<option style='background-color:Plum' value='Plum'>Plum</option>
			<option style='background-color:PowderBlue' value='PowderBlue'>PowderBlue</option>
			<option style='background-color:Purple' value='Purple'>Purple</option>
			<option style='background-color:Red' value='Red'>Red</option>
			<option style='background-color:RosyBrown' value='RosyBrown'>RosyBrown</option>
			<option style='background-color:RoyalBlue' value='RoyalBlue'>RoyalBlue</option>
			<option style='background-color:SaddleBrown' value='SaddleBrown'>SaddleBrown</option>
			<option style='background-color:Salmon' value='Salmon'>Salmon</option>
			<option style='background-color:SandyBrown' value='SandyBrown'>SandyBrown</option>
			<option style='background-color:SeaGreen' value='SeaGreen'>SeaGreen</option>
			<option style='background-color:SeaShell' value='SeaShell'>SeaShell</option>
			<option style='background-color:Sienna' value='Sienna'>Sienna</option>
			<option style='background-color:Silver' value='Silver'>Silver</option>
			<option style='background-color:SkyBlue' value='SkyBlue'>SkyBlue</option>
			<option style='background-color:SlateBlue' value='SlateBlue'>SlateBlue</option>
			<option style='background-color:SlateGray' value='SlateGray'>SlateGray</option>
			<option style='background-color:Snow' value='Snow'>Snow</option>
			<option style='background-color:SpringGreen' value='SpringGreen'>SpringGreen</option>
			<option style='background-color:SteelBlue' value='SteelBlue'>SteelBlue</option>
			<option style='background-color:Tan' value='Tan'>Tan</option>
			<option style='background-color:Teal' value='Teal'>Teal</option>
			<option style='background-color:Thistle' value='Thistle'>Thistle</option>
			<option style='background-color:Tomato' value='Tomato'>Tomato</option>
			<option style='background-color:Turquoise' value='Turquoise'>Turquoise</option>
			<option style='background-color:Violet' value='Violet'>Violet</option>
			<option style='background-color:Wheat' value='Wheat'>Wheat</option>
			<option style='background-color:White' value='White'>White</option>
			<option style='background-color:WhiteSmoke' value='WhiteSmoke'>WhiteSmoke</option>
			<option style='background-color:Yellow' value='Yellow'>Yellow</option>
			<option style='background-color:YellowGreen' value='YellowGreen'>YellowGreen</option>";
	}
	function array_clean($array, $max_id=0){ // max_id can also be an array of values, whether strings or whatever
		if(is_array($max_id)){
			if(count($max_id) > 0)
				foreach($max_id as $id)
					if(isset($array[$id]))
						unset($array[$id]);
		}
		else{
			for($i=0; $i<=$max_id; $i++)
				if(isset($array[$i]))
					unset($array[$i]);
		}
		return $array;
	}
	function array_multi_unique($array){ // USE THIS ONE - it seems to be a bit more efficient.
        foreach ($array as $k=>$na)
            $new[$k] = serialize($na);
        $uniq = array_unique($new);
        foreach($uniq as $k=>$ser)
            $new1[$k] = unserialize($ser);
        return ($new1);
    }
}
?>
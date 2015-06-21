<?php
require_once('staff_classes.php');
/*	
class OC{
	public $con;
	public $staff,$data,$cal,$notes;
	public $title;

	function __construct(){
		$this->init();
	}
	
	public function init(){
		$this->staff = new OC_Staff();
		$this->data = new OC_Data();
		$this->cal = new OC_Cal();
		$this->notes = new OC_Notes();
	}
	
	public function show(){
		$title = array();
		$content = array();
		$ref = 'oc';
		
		if($this->staff){
			array_push($title, 'Staff');
			array_push($content, $this->staff->get_html());
		}
		if($this->data){
			array_push($title, 'Data');
			array_push($content, $this->data->get_html());
		}
		if($this->cal){
			array_push($title, 'Calendar');
			array_push($content, $this->cal->get_html());
		}
		if($this->notes){
			array_push($title, 'Notes');
			array_push($content, $this->notes->get_html());
		}
		
		
		echo"
		<div id='oc' class='oc_standard'>
			<div class='oc_title'>{$this->title}</div>";
		
		echo generate_tab($ref, $title,$content);
		
		echo"
		</div>";
	}
}

class OC_Staff{
	public $details, $av, $away, $here, $lang, $pos;
	
	function __construct(){
		$this->init();
	}
	public function init(){
	
	}
	public function get_html(){
		$title = array('Details','Av','Away','Here','Lang','Pos');
		$content = array('Detail info','Av info','Away info','Here info','Lang info','Pos info');
		$content_id = array('oc_staff_details','oc_staff_av','oc_staff_away','oc_staff_here','oc_staff_lang','oc_staff_pos');
		return generate_acc($title, $content, $content_id);
	}
	public function show(){
	}
}
class OC_Data{
	public $group, $lang, $pos,$shift; //see name
	function __construct(){
		$this->init();
	}
	
	public function init(){
	
	}
	
	public function get_html(){
		$title = array('Group','Lang','Pos','Shift');
		$content = array('Group Info','Lang Info','Pos Info','Shift Info');
		$content_id = array('oc_data_group','oc_data_lang','oc_data_pos','oc_data_shift');
		return generate_acc($title, $content,$content_id);
	}
	
	public function show(){
	}
}
class OC_Cal{
	public $settings;
	
	function __construct(){
		$this->init();
	}
	public function init(){
	
	}
	public function get_html(){
		$title = array('Settings');
		$content = array('Settings Info');
		$content_id = array('oc_cal_settings');
		return generate_acc($title, $content,$content_id);
	}
	public function show(){
	}
}
class OC_Notes{

	function __construct(){
		$this->init();
	}
	public function init(){
	
	}
	public function get_html(){
		return "<div id='oc_notes'> NOTES ABOUT EVERYTHING GO HERE.</div>";
	}
	public function show(){
	}
}
function generate_tab($ref = null, $title = null, $content = null){
	if(!is_array($title))
		$title = array($title);
	if(!is_array($content))
		$content = array($content);
	
	if($ref != null)
		$ref .= '-';
	$ret = '';
	$ret .= "<ul>";
	
	foreach($title as $i=>$t)
		$ret .= "<li><a href='#tabs-{$ref}{$i}'>{$t}</a></li>";
		
	$ret .= "</ul>";
	
	foreach($content as $i=>$c)
		$ret .= "<div id='tabs-{$ref}{$i}'>{$c}</div>";
		
	return $ret;
}
function generate_acc($title = null, $content = null, $content_id = null){
	if(!is_array($title))
		$title = array($title);
	if(!is_array($content))
		$content = array($content);
	
	if(count($title) != count($content))
		return false;
	
	$ret = '';
	$ret .= "<div id='test_id' class='acc'>";
	
	foreach($title as $i=>$t){
		$ret .= "<h3>$t</h3><div";
		if($content_id && isset($content_id[$i]))
			$ret .=" id='{$content_id[$i]}'";
		$ret .= ">{$content[$i]}</div>";
	}
		
	$ret .= "</div>";
	
	return $ret;
}
*/

if(!isset($_POST['div_id']) || !isset($_POST['id']))
	exit();
	
$id = $_POST['id'];

$staff = new StaffS($con, $id);
switch($_POST['div_id']){
	case 'oc_staff_details':
		$staff->form_details($con);
		break;
	case 'oc_staff_av':
		$staff->form_av($con);
		break;
	case 'oc_staff_away':
		$staff->form_away($con);
		break;
	case 'oc_staff_here':
		$staff->form_here($con);
		break;
	case 'oc_staff_lang':
		$staff->form_lang($con);
		break;
	case 'oc_staff_pos':
		$staff->form_pos($con);
		break;
}
?>
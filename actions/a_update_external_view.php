<?php

// START MONITORING OUTPUT, FOR WRITING LATER
//ob_start();
// unneeded, as we actually are just iframing the page rather than saving it.

// GET THE STUFF HERE
/*
	from: a_rota_templates
	$_POST[]:
		action: generate
		date: current date probably
		
*/

require_once('../includes/general_functions.php');
$config = new conFig();


$template = array();
if(isset($config->xml->company_web_0) && $config->xml->company_web_0)
	array_push($template, trim($config->xml->company_web_0));
if(isset($config->xml->company_web_1) && $config->xml->company_web_1)
	array_push($template, trim($config->xml->company_web_1));
if(isset($config->xml->company_web_2) && $config->xml->company_web_2)
	array_push($template, trim($config->xml->company_web_2));
/*
	$template = array(
	'shop - no lunch',
	'reception and guides - no lunch'
);
*/
$_POST['action'] = 'generate';
$_POST['date'] = date('Y-m-d');

if(is_array($template) && count($template) > 0)
foreach($template as $t){
	$_POST['template'] = $t;
	$dont_encode = true;
	require('a_rota_templates.php');
	echo $data['rota'] . "<br/><br/>";
}

/*
$_POST['action'] = 'generate';
$_POST['date'] = date('Y-m-d');
$_POST['template'] = 'shop - no lunch';
$dont_encode = true;
require('a_rota_templates.php');
echo $data['rota'];

include('a_rota_templates.php');
echo $data['rota'];
*/
// THE OUTPUT - see above comments on the start monitoring output
// print_r( ob_get_contents() );
?>
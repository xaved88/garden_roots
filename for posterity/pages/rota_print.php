<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Rota Mod</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/calendar.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/calendar.css" rel="stylesheet">

<style>

	button.pressed{border-type:inset; background-color:aliceblue;}
	.invis{display:none;}
	
	table,tr,th,td{border: 1px solid black;}
	
	input#week_count{width:50px;}
</style>
<script>

function hide_all_but_this(obj){
	$(obj).siblings().hide();
	par = $(obj).parent();
	if(!$(par).is('body'))
		hide_all_but_this(par);
}
function show_all_from_this(obj){
	$(obj).siblings().show();
	par = $(obj).parent();
	if(!$(par).is('body'))
		show_all_from_this(par);
}
function print_rota(){
	hide_all_but_this('div#rota');
	window.print();
	show_all_from_this('div#rota');
}

function generate_templates(){
	template = $('select#select_template').val();
	if(!template){
		alert("Please select a template.");
		return;
	}
	date = $('input#template_date').val();
	if(!date){
		alert("Please select a date.");
		return;
	}
	action = 'generate';
	week_count = $('input#week_count').val();
	
	page_break = false;
	if($('input#page_break').prop('checked'))
		page_break = true;
	
	$('#action_response').html('');
	$('#rota').html('');
	
	generate_template(action,template,date,week_count,0,page_break);
}

function generate_template(action,template,date,number,push,page_break){
	if(!push)
		push = 0;
	$.ajax({
		type: 'POST',
		url: '../actions/a_rota_templates.php',
		dataType:'json',
		data:{
			action: action,
			template: template,
			date: date,
			push: push
		},
		success:function(data){
			$('#action_response').append(data['message']);
			$('#rota').append(data['rota']);
			if(number > 1){
				$('#rota').find('div.template_footer').remove();
				generate_template(action,template,date,number-1,push+7,page_break)
			}
			else if(page_break){
				$('div.print_rota_instance:not(:last-child)').each(function(){
					$(this).css('page-break-after','always');
				});
			}
		},
		error: function(data){
			$('#action_response').html('Error loading file: AJAX call failed. Logging to console.<br/>');
			$('#action_response').append(data.responseText);
			console.log(data);
		}
	});
}
function listener(){
	$("button,select,div").off();
}

$(function() {
	listener();
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
require_once('../includes/general_functions.php');
?>


<div id='action_response'></div>
<div id='print_options'>
<select id='select_template'><option value=''></option>
<?php
	$file = '../config/rota_templates.xml';
	$xml = new SimpleXMLElement($file, null, true);
	foreach($xml->template as $t){
		foreach($t->attributes() as $a => $b){
			echo "<option value='{$b}'>{$b}</option>";
		}
	}
?>
</select>
<input id='template_date' type='date' value='<?php echo standard_date_val();?>'/>
Weeks to Load:<input id='week_count' type='number' value=1 /> 
Page break each week? <input id='page_break' type='checkbox' checked /> 
<button onclick=generate_templates();>Generate Template</button>
<button onclick=print_rota();>Print</button>
</div>
<div id='rota'></div>


</body>

</html>
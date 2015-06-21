<html>
<head>
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

function generate_template(){
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
	$.ajax({
		type: 'POST',
		url: '../actions/a_rota_templates.php',
		dataType:'json',
		data:{
			action: action,
			template: template,
			date: date
		},
		success:function(data){
			
			$('#action_response').html(data['message']);
			$('#rota').html(data['rota']);
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

<div id='action_response'></div>
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
<input id='template_date' type='date'/>
<button onclick=generate_template();>Generate Template</button>
<button onclick=print_rota();>Print</button>
<div id='rota'></div>


</body>
</html>
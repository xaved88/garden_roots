<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Rota Needs</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/calendar.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/calendar.css" rel="stylesheet">

<script>
// Calendar actions
function after_push_date(){
	sched_after_ss();
}

// Add/Remove Functions:

function add_needs_cal(){
	hidden_row = $('table#rota_needs_cal tr.hidden');//.html(); // oops!
	hidden_row.wrap('<p>');
	hidden_row_text = hidden_row.parent().html();
	hidden_row.unwrap();
	$('table#rota_needs_cal tr.needs_cal_inst:first').before(hidden_row_text);
	new_row = $('table#rota_needs_cal tr.hidden:last');
	new_row.removeClass('hidden');
	new_row.addClass('needs_cal_inst');
}
function remove_needs_cal(obj){
	td = $(obj).parent();
	tr = td.parent();
	if(tr.attr('data-save') == '1'){ // Is active, so remove
		tr.attr('data-save','0');
		tr.addClass('rem');
		td.html('+');
	}
	else{ //Inactive, so reactivate
		tr.attr('data-save','1');
		tr.removeClass('rem');
		td.html('X');
	}
}

function remove_needs(obj){ // Both ex && temp
	div = $(obj).parent();
	if(div.attr('data-save') == '0'){
		div.attr('data-save','1');
		div.removeClass('rem');
		$(obj).html('Delete');
	}
	else{
		div.attr('data-save','0');
		div.addClass('rem');
		$(obj).html('Set');
	}
//	alert('removing template!');
}


// Save Function
function save(){
	// Cal
	// Looking good for basics, need to test with adding and all
	var cal = new Array();
	$('table#rota_needs_cal tr.needs_cal_inst[data-save=1]').each(function(){
		data = {};
		data.name = $(this).find('select.needs_cal_temp').val();
		data.start_date = $(this).find('input.needs_cal_start').val();
		data.end_date = $(this).find('input.needs_cal_end').val();
		cal.push(data);
	});
	// Temp
	var temp = new Array();
	$('div#tabs-needs_temp div.needs_temp[data-save=1]').each(function(){
		data = {}
		data.name = $(this).find('input.needs_temp_name').val();
		data.inst = new Array();
		$(this).find('td').each(function(){
			inst = {};
			inst.day = $(this).attr('data-day');
			inst.pos_id = $(this).attr('data-pos');
			inst.shift_id = $(this).attr('data-shift');
			inst.value = $(this).find('input[type=number]').val();
			
			if(inst.day && inst.pos_id && inst.shift_id && inst.value && inst.value != 0)
				data.inst.push(inst);
		});
		temp.push(data);
	});
	/*
	var temp_del = new Array();
	$('div#tabs-needs_temp div.needs_temp[data-save=0]').each(function(){
		data = {}; // Need to layer the class because of the action page layout.
		if($(this).attr('data-name') != 'new'){
			data.name = $(this).find('input.needs_temp_name').val();
		}
		temp_del.push(data);
	});
	*/
	// Ex
	var ex = new Array();
	$('div#tabs-needs_ex div.needs_ex[data-save=1]').each(function(){
		data = {}
		data.date = $(this).find('input.needs_ex_date').val();
		data.inst = new Array();
		$(this).find('td').each(function(){
			inst = {};
			inst.pos_id = $(this).attr('data-pos');
			inst.shift_id = $(this).attr('data-shift');
			inst.value = $(this).find('input[type=number]').val();
			
			if(inst.pos_id && inst.shift_id && inst.value && inst.value != 0){
				data.inst.push(inst);
			}
		});
		ex.push(data);
	});
	/*
	var ex_del = new Array(); // NOT SURE WHY THIS ONE ISN'T WORKING, SEEMS THAT IT SHOULD.
	$('div#tabs-needs_ex div.needs_ex[data-save=0]').each(function(){
		data = {}; // Need to layer the class because of the action page layout.
		if($(this).attr('data-date') != 'new'){
			data.date = $(this).find('input.needs_ex_date').val();
		}
		ex_del.push(data);
	});h
	*/
	// AJAX
	$.ajax({
		type:'POST',
		url:'../actions/a_rota_needs.php',
		data:{
			cal: cal,
			temp: temp,
			//temp_del: temp_del,
			ex: ex,
			//ex_del: ex_del
		},
		success:function(data){
			$("#action_response").html(data);
		}
	});
}

// Page Start functions
$(function() {
	$("#rota_needs").tabs();
	$("#tabs-needs_temp").accordion({
		heightStyle: "content",
		collapsible: true,
		active: false
	});
	$("#tabs-needs_ex").accordion({
		heightStyle: "content",
		collapsible: true,
		active: false
	});
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
include('../includes/rota_classes.php');
$needs = new rotaNeeds($con);
?>

<div id='action_response'></div>
<div id='rota_needs'>
	<button onclick=save()>Save</button>
	<ul>
		<li><a href="#tabs-needs_cal">Needs Calendar</a></li>
		<li><a href="#tabs-needs_temp">Needs Templates</a></li>
		<li><a href="#tabs-needs_ex">Exceptions</a></li>
	</ul>
	<div id="tabs-needs_cal">
		<?php
		$needs->form_cal();
		?>
	</div>	
	<div id="tabs-needs_temp">
		<?php
		$needs->form_temp();
		?>
	</div>
	<div id="tabs-needs_ex">
		<?php
		$needs->form_ex();
		?>
	</div>
</div>
</body>

</html>
<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Airport Runs</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<style>

div.search_box{margin:5px 5px 5px 20px; padding:3px; border: 1px solid black;}
.left{float:left;}
.invis{display:none;}

div.flight_shifts{width:150px; height: 20px; border: 1px solid black; padding 3px; margin: 0px 5px; text-align:center;}
</style>
<script>

function add_run(){
	data = {};
	data.flight_no = $('input.flight_no').val();
	data.airline = $('input.flight_airline').val();
	data.type = $('input.flight_type:checked').val();
	data.date = $('input.flight_date').val();
	data.time = $('input.flight_time').val();
	data.shifts = new Array();
	$('div.flight_shift_box input:checked').each(function(){
		console.log($(this).val());
		data.shifts.push($(this).val());
	});
	console.log(data.shifts);
	if(!data.shifts.length)
		data.shifts = 0;
	else
		data.shifts = data.shifts.join(',');
	console.log(data.shifts);
	data.driver = new Array();
	$('div.driver_list div.driver').each(function(){
		data.driver.push($(this).attr('data-staff_id'));
	});
	data.passenger = new Array();
	$('div.passenger_list div.passenger').each(function(){
		data.passenger.push($(this).attr('data-staff_id'));
	});
	console.log(data);
	$.ajax({
		type:'POST',
		url:'../actions/a_staff_airport.php',
		data:{
			action:'add',
			data:data
		},
		success:function(data){
			$("div#action_response").html(data);
			page_listeners();
		},
		error:function(data){
			$("div#action_response").html("Error. Logged to console.");
			console.log(data);
		}
	});
	
}

function add_form(){
	action = 'add';
	fetch_form(action);
}
function mod_form(){
	action = 'mod';
	fetch_form(action);
}
function del_form(){
	action = 'del';
	fetch_form(action);
}
function rep_form(){
	action = 'rep';
	fetch_form(action);
}

function fetch_form(action,data){
	action = action + "_form"
	$.ajax({
		type:'POST',
		url:'../disp/d_staff_airport.php',
		data:{
			action: action,
			data: data
		},
		success:function(data){
			$("div#airport_display").html(data);
			page_listeners();
		},
		error:function(data){
			$("div#action_response").html("Error. Logged to console.");
			console.log(data);
		}
	});
}
	
function search_staff(obj){ // obj = input
	o = $(obj);
	search = o.val();
	selector = o.attr('data-selector');
	$.ajax({
		type:'POST',
		url:'../includes/staff_selector.php',
		data:{
			action:'simple_predict',
			search:search
		},
		success:function(data){
			$(selector).html(data);
			search_listeners();
			$(selector).removeClass('invis');
		},
		error:function(data){
			$("div#action_response").html("Error. Logged to console.");
			console.log(data);
		}
	});
}
function staff_selected(obj){ // obj = html div
	o = $(obj);
	staff_id = o.attr('data-staff_id');
	name = o.html();
	p = o.closest('div.transport');
	type = p.attr('data-type');
	// ADD IT TO THE LIST
	list = p.find('div.' + type + "_list");
	x = list.find('div[data-staff_id='+staff_id+']');
	if(x.length){ // IF IT"S ALREADY THERE
		alert("Staff Member already set");
	}
	else{
		html = "<div class='"+type+"' data-staff_id='"+staff_id+"'>"+name+"<button class='remove_name'>X</button></div>";
		console.log(html);
		list.append(html);
	}
	// CLOSE THE SEARCH
	o.closest('div.search_box').html('').addClass('invis');
	// CLEAR THE SEARCH
	p.find('input.search_staff').val('');
	page_listeners();
}
function remove_name(obj){	// obj = html button
	o = $(obj);
	o.parent().remove();
}
function mod_shift(obj){ // html input checkbox
	o = $(obj);
	p = o.closest('div.flight_shift_box');
	shifts = new Array();
	p.find('input:checked').each(function(){
		shifts.push($(this).attr('data-abbr'));
	});
	shift_string = shifts.join(',');
	$('div.flight_shifts').html(shift_string);
}

function prep_flight_search(str){ // str = identifying range string, 'input|all|future'
	action = 'rep';
	if(str == 'future' || str == 'all')
		data = str;
	else if(str == 'input'){
		console.log(str);
		data = {};
		data.start_date = $('div.flight_search input.start_date').val();
		data.end_date = $('div.flight_search input.end_date').val();
	}
	console.log(data);
	fetch_form(action,data);
}
function page_listeners(){
	$('button').off();
	$('input').off();
	$('div').off();
	
	
	$('button#add').click(function(){	add_form();	});
	$('button#mod').click(function(){	mod_form();	});
	$('button#del').click(function(){	del_form();	});
	$('button#rep').click(function(){	rep_form();	});
	$('button.remove_name').click(function(){	remove_name(this);	});
	$('div.flight_shifts').click(function(){	$('div.flight_shift_box').toggleClass('invis'); });
	$('input.shift').change(function(){	mod_shift(this);	});
	$('button.add_run').click(function(){	add_run();	});
	$('div.flight_search button').click(function(){	prep_flight_search($(this).attr('data-range'));	});
	$('input.search_staff').change(function(){	search_staff(this);	});
	
	search_listeners();
}

function search_listeners(){
	$('div.staff_selector_member').off();
	
	$('div.staff_selector_member').click(function(){	staff_selected(this);	});
}

$(function() {
	page_listeners();
	//$( "#add_staff" ).tabs();
});
</script>
</head>


<body>
<?php 
require_once('../includes/general_functions.php');
include('../includes/nav_bar.php');
?>
<div id='action_response'></div>
<div id='airport_menu'>
	<button id='add'>Add</button>
	<button id='mod'>Modify</button>
	<button id='del'>Delete</button>
	<button id='rep'>Report</button>
</div>

<div id='airport_display'>

</div>

<div id='airport_search'>
</div>

<div id='airport_report'>
</div>

</body>

</html>
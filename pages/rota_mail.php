<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Emailer</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<style>
	button.pressed{	background-color:azure; border-style:inset;	}
	.invis{	display:none; }
	
	#who,#when,#search,#staff,#individuals,#except,#submit{	float:left;	}
	div.bordered{
		height: 60px;
		border: 1px solid darkgray;
		padding: 10px;
		margin-top: 5px;
	}
	
	div.column{
		padding: 5px 20px;
	}
	div.controls{
		margin-top:20px;
	}
	
	button.remove_staff{
		font-size: 50%;
		margin: 3px 12px auto 5px;
		padding: 0px;
		vertical-align: middle;
		float:right;
	}
	div.underline{	border-bottom: 1px solid grey; }
	div#individuals,div#except{	min-width: 140px;}
	
	button.except{	float:left; margin-right:10px;	}
	div#content textarea{width:400px;}
	
	button#submit.submitable{}
	button#submit.unclickable{border:inset; background-color:beige;}
	
	table.return_info, table.return_info tr{border:1px solid black; border-collapse:collapse;}
	table.return_info th.status{padding-right:40px;}
</style>
<script>

function send_it(){
	// Set the button to be unclickable while processing, break if it is already processing.
	button = $('button#submit');
	
	if(!button.hasClass('submitable')){
		alert("Still working on your past requests. Please wait.");
		return;
	}
	button.html("Processing...");
	button.removeClass("submitable");
	button.addClass("unclickable");
	
	data = {};
	data.individuals = new Array();
	$('div#individuals div.staff_mail').each(function(){
		data.individuals.push($(this).attr('data-staff_id'));
		console.log("adding staff:" + $(this).attr('data-staff_id'));
	});
	data.except = new Array();
	$('div#except div.staff_mail').each(function(){
		data.except.push($(this).attr('data-staff_id'));
		console.log("removing staff:" + $(this).attr('data-staff_id'));
	});
	if($('div#staff_buttons button.employees').hasClass('pressed'))
		data.employees = true;
	else
		data.employees = 0;
	if($('div#staff_buttons button.volunteers').hasClass('pressed'))
		data.volunteers = true;
	else
		data.volunteers = 0;
	data.date_type = false;
	if($('div#when button.month').hasClass('pressed'))
		data.date_type = 'month';
	else if($('div#when button.week').hasClass('pressed'))
		data.date_type = 'week';
	else if($('div#when button.range').hasClass('pressed'))
		data.date_type = 'range';
	else{
		alert("Error, please select a date type");
		return;
	}
	data.start_date = false;
	data.end_date = false;
	if(data.date_type == 'month')
		data.start_date = $('input#month_date').val();
	else if(data.date_type == 'week')
		data.start_date = $('input#week_date').val();
	else if(data.date_type == 'range'){
		data.start_date = $('input#start_date').val();
		data.end_date = $('input#end_date').val();
	}
	data.header = $('textarea#email_header').val();
	data.subject = $('textarea#email_subject').val();
	data.footer = $('textarea#email_footer').val();
	if($('input#email_cc_sender:checked').length)
		data.cc_sender = true;
		
	$.ajax({
		url:'../actions/a_rota_mail.php',
		type:'POST',
		data:{
			data:data
		},
		success:function(data){
			$('#action_response').html(data);
			button.html("Send It");
			button.addClass("submitable");
			button.removeClass("unclickable");
		},
		failure:function(data){
			$('#action_response').html('AJAX Error, logging data to console.');
			console.log(data);
		}
	});
}

function clear_search(){
	$('input.search_staff').val('');
	$('div.staff_select').html('');
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
	staff_name = o.text();
	
	//console.log(staff_id + " : " + staff_name);
	
	if($('div#except button').hasClass('pressed')){
		add_staff(staff_id,staff_name,true);
	}
	else{
		add_staff(staff_id,staff_name,false);
	}
}
function add_staff(staff_id, staff_name, except){
	console.log('in add_staff()');
	if(except){
		div = $('div#except');
	}
	else{
		div = $('div#individuals');
	}
	x = div.find('div.staff_mail[data-staff_id='+staff_id+']'); 
	if( x.length ){
		console.log('Removing');
		x.remove();
	}
	else{
		console.log('Appending');
		html = "<div class='staff_mail underline' data-staff_id='"+staff_id+"'>"+staff_name+"<button class='remove_staff'>X</button></div>";
		div.append(html);
	}
	staff_listeners();
}
function remove_staff(obj){ // obj = html button object
	o = $(obj);
	o.closest('div.staff_mail').remove();
}

function button_toggle(obj){//obj = html button object
	o = $(obj);
	if(o.hasClass('pressed')){
		o.removeClass('pressed');
	}
	else{
		o.addClass('pressed');
		if(o.hasClass('toggle_group')){
			o.siblings('button.toggleable.toggle_group').removeClass('pressed');
		}
	}
}

function date_input_show(str, obj){// str = string: month|week|range. obj = html button
	o = $(obj);
	$('div#month').addClass('invis');
	$('div#week').addClass('invis');
	$('div#range').addClass('invis');
	if(o.hasClass('pressed')){
		$('div#'+str).removeClass('invis');
	}
}
function page_listeners(){
	$('button').off();
	$('input').off();
	
	$('button.toggleable').click(function(){	button_toggle(this);	});
	$('button#clear_search').click(function(){	clear_search();	});
	// keep these three below toggleables
	$('div#when button.month').click(function(){	date_input_show('month',this);	});
	$('div#when button.week').click(function(){	date_input_show('week',this);	});
	$('div#when button.range').click(function(){	date_input_show('range',this);	});
	$('button#submit').click(function(){	send_it();	});
	
	
	$('input.search_staff').change(function(){	search_staff(this);	});
	
	search_listeners();
	staff_listeners();
}

function search_listeners(){
	$('div.staff_selector_member').off();
	
	$('div.staff_selector_member').click(function(){	staff_selected(this);	});
}

function staff_listeners(){
	$('button.remove_staff').off();
	
	$('button.remove_staff').click(function(){	remove_staff(this);	});
}
$(function() {
	page_listeners();
});
</script>
</head>


<body>
<?php 
require_once('../includes/general_functions.php');
include('../includes/nav_bar.php');

$date = standard_date_val();
$config = new conFig();
?>
<div id='action_response'>
</div>
	
<div id='who'>
	<div id='search' class='column'>
		<label for='search_staff'>Search Staff:</label><input class='search_staff' data-selector='div.staff_select'/><button id='clear_search'>Clear</button><br/>
		<div class='staff_select bordered'></div>
	</div>
	<div id='staff' class='column'>
		<div id='staff_buttons'>
			<button class='toggleable employees'>All Employees</button>
			<button class='toggleable volunteers'>All Volunteers</button>
		</div>
		<div id='individuals' class='bordered'><div class='underline'>Individuals:</div></div>
		<div id='except' class='bordered'><button class='toggleable except'>---</button><div class='underline'>Except:</div></div>
	</div>
</div>

<div id='when' class='column'>
	<button class='toggleable toggle_group month'>Month</button>
	<button class='toggleable toggle_group week'>Week</button>
	<button class='toggleable toggle_group range'>Range</button>
	<div id='month' class='invis'>
		<label for="month_date">Month of:</label>
		<input name="month_date" id="month_date" type='date' value='<?php echo $date; ?>' />
	</div>
	<div id='week' class='invis'>
		<label for="week_date">Week of:</label>
		<input name="week_date" id="week_date" type='date' value='<?php echo $date; ?>' />
	</div>
	<div id='range' class='invis'>
		<label for="start_date">Start Date:</label>
		<input name="start_date" id="start_date" type='date' value='<?php echo $date; ?>' /><br/>
		<label for="end_date">End Date:</label>
		<input name="end_date" id="end_date" type='date' value='<?php echo $date; ?>' />
	</div>
	<div id='content'>
		<label for='email_header'>Header:</label>
		<textarea name='email_header' type='text' id='email_header' rows=5><?php echo $config->xml->email->header;?></textarea>
		<br/><label for='email_subject'>Subject:</label>
		<textarea name='email_subject' type='text' id='email_subject' rows=1><?php echo $config->xml->email->subject;?></textarea>
		<br/><label for='email_footer'>Footer:</label>
		<textarea name='email_footer' type='text' id='email_footer' rows=5><?php echo $config->xml->email->footer;?></textarea>
	</div>
	<div class='controls'>
		<label for='email_cc_sender'>CC Sender:</label>
		<input id='email_cc_sender' name='email_cc_sender' type='checkbox' <?php echo $config->xml->email->cc_sender?>/><br/>
		<button id='submit' class='submitable'>Send It</button>
	</div>
</div>
</body>

</html>
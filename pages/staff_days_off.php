<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Staff Days Off</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/staff_selector.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<style>
	div.day_div{
		height:20px; 
		width:20px; 
		border:1px solid black;
		text-align:center;
		margin: 0px 0px 70px 0px;
		font-size:85%;
		float:left;
	}
	div.day_div.nonmonth{font-style:italic;}
	
	div.staff_div{margin-left:28px; font-size:90%;}
	
	div.title{text-align:center; font-size: 110%; border: 1px solid black;}
	
	div#pdo_cal table td{border:1px solid black; height:95px; width: 125px; font-size:90%}
	
	.my_table{border:1px solid black;}
	
	button.pressed{border-type:inset; background-color:aliceblue;}
	.invis{display:none;}
	
	div#print_days_options{border: 1px solid black;padding: 5px 20px;}
	
	.blank_class{
		background: none;
		border: none;
	}
	
	
	button#toggle_partner_link{float: right;}
</style>
<script>
function save(){
	data = new Array();
	$('table#days_off th.days_off_staff').each(function(){
		d = {};
		d.staff_id = $(this).attr('data-staff_id');//$(this).parent().parent().find('th.days_off_staff').attr('data-staff_id');
		d.date = new Array();
		$(this).parent().find('select.days_off_date').each(function(){
			d.date.push($(this).val());
		});
		data.push(d);
	});
	
	start_date = $('table#days_off').attr('data-start_date');
	end_date = $('table#days_off').attr('data-end_date');
	type = $('select#select_type').val();
	
	$.ajax({
		type: 'POST',
		url: '../actions/a_staff_days_off.php',
		data:{
			action: 'update_days_off',
			start_date: start_date,
			end_date: end_date,
			type: type,
			data: data
		},
		success:function(data){
			$('#action_response').html(data);
		}
	});
}

function load(){
	action = 'load';
	type = $('select#select_type').val();
	date = '';
	$.ajax({
		type: 'POST',
		url: '../disp/d_staff_days_off.php',
		data:{
			action: action,
			type: type,
			date: date
		},
		success:function(data){
			$('#days_off').html(data);
			listener();
		}
	});
}

function load_print_cal(){
	action = 'print';
	type = $('select#select_type').val();
	date = $('input#pdo_date').val();
	
	options = {};
	options.inline_css = $('textarea#inline_css').val();
	options.devotions_text = $('textarea#devotions_text').val();
	options.devotions_css = $('textarea#devotions_css').val();
	options.header_text = $('textarea#header_text').val();
	options.header_css = $('textarea#header_css').val();
	options.footer_text = $('textarea#footer_text').val();
	options.footer_css = $('textarea#footer_css').val();
	
	$.ajax({
		type: 'POST',
		url: '../disp/d_staff_days_off.php',
		data:{
			action: action,
			type:type,
			date:date,
			options: options
		},
		success:function(data){
			$('div#pdo_cal').html(data);
		},
		error:function(data){
			$('#action_response').html("Error in AJAX calls. Logging data to console.<br/>");
			console.log(data);
		}
	});
}


function hide_all_but_this(obj){
	$(obj).addClass('blank_class');
	$(obj).siblings().hide();
	par = $(obj).parent();
	if(!$(par).is('body'))
		hide_all_but_this(par);
}
function show_all_from_this(obj){
	$(obj).siblings().show();
	$(obj).removeClass('blank_class');
	par = $(obj).parent();
	if(!$(par).is('body'))
		show_all_from_this(par);
}
function print_days_off(){
	obj = $('div#pdo_cal');
	hide_all_but_this(obj);
	window.print();
	show_all_from_this(obj);
	
}

function toggle_button(obj, solo){
	if($(obj).hasClass('pressed'))
		$(obj).removeClass('pressed');
	else{
		if(solo) $(obj).siblings().removeClass('pressed');
		$(obj).addClass('pressed');
	}
}
function toggle_pdo_options(){
	pdo = $('div#print_days_options');
	if(pdo.hasClass('invis'))
		pdo.removeClass('invis');
	else
		pdo.addClass('invis');
}
function save_pdo_options(){
	action = 'update_print_options';
	inline_css = $('textarea#inline_css').val();
	devotions_text = $('textarea#devotions_text').val();
	devotions_css = $('textarea#devotions_css').val();
	header_text = $('textarea#header_text').val();
	header_css = $('textarea#header_css').val();
	footer_text = $('textarea#footer_text').val();
	footer_css = $('textarea#footer_css').val();
	
		
	$.ajax({
		type: 'POST',
		url: '../actions/a_staff_days_off.php',
		data:{
			action: action,
			inline_css: inline_css,
			devotions_text: devotions_text,
			devotions_css: devotions_css,
			header_text: header_text,
			header_css: header_css,
			footer_text: footer_text,
			footer_css: footer_css
		},
		success:function(data){
			$('#pdo_action_response').html(data);
		}
	});
}

function listener(){
	$("button,select,div").off();
	$("button.toggleable").click(function(){	toggle_button(this);	});
	$("button#button_pdo_options").click(function(){	toggle_pdo_options();	});
	$("button#save_pdo_options").click(function(){	save_pdo_options();	});
	$('select#select_type').change(function(){	load();	});
	$('input.change_date').change(function(){	push_date($(this).val());	});
	$('button#generate_days_off').click(function(){	load_print_cal($(this).val());	});
	$('button#print_days_off').click(function(){	print_days_off();	});
	
	$('select.days_off_date').change(function(){	partner_link(this);	});
	
}

function push_date(date){
	type = $('select#select_type').val();
	$.ajax({
		type: 'POST',
		url: '../disp/d_staff_days_off.php',
		data:{
			action: 'load',
			type: type,
			date: date
		},
		success:function(data){
			$('#days_off').html(data);
		}
	});
}

// PARTNER SELECT FUNCTIONS
/*<button class='toggle_partner_link clickable'>Partner Link</button> */
function partner_link(obj){ // obj = html select
	toggle = $('button#toggle_partner_link');
	if(!toggle.hasClass('pressed'))
		return;
	
	o = $(obj);
	th = o.closest('tr').find('th');
	staff_id = th.attr('data-staff_id');
	partner_id = th.attr('data-partner_id');
	date = o.find('option:selected').val();
	week = o.attr('data-week');
	
	if(!partner_id)
		return;
	
	partner_tr = $('table#days_off th[data-staff_id='+partner_id+']').closest('tr');
	partner_select = partner_tr.find('select[data-week='+week+']');
	partner_select.val(date);
	/* WORKING EXCEPT FOR BLANKS
	partner_tr = $('table#days_off th[data-staff_id='+partner_id+']').closest('tr');
	partner_option = partner_tr.find('option[value='+date+']');
	partner_option.closest('select').val(date);
	*/
	
	
}

$(function() {
	$('div#days_off_accordion').accordion({
		heightStyle: 'content',
		active: false,
		collapsible: true
	});
	listener();
	$('select#select_type').val('1');
	load();
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
require_once('../includes/general_functions.php');
?>
<div id='action_response'></div>
<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
<div>
Choose a type: <select id='select_type'><option value='0'></option>
<?php
	require_once('../includes/staff_classes.php');
	$config = new conFig();
	$type = $config->staff_type_array();
	
	foreach($type as $t){
		echo "<option value='{$t['id']}'>{$t['name']}</option>";
	}
?>
</select>
</div>
<div id='days_off_accordion'>
	<h3>Select</h3>
	<div id='days_off'></div>
	<h3>Print</h3>
	<div id='print_days_off'>
		<div id='pdo_action_response'></div>
		<div>Choose a date: <input id='pdo_date' type='date' value='<?php echo standard_date_val();  ?>'> 
		<button id='generate_days_off'>Generate</button>
		<button id='print_days_off'>Print</button></div>
		<button id='button_pdo_options' class='toggleable'>Options</button>
		<div id='print_days_options' class='invis'>
			<button id='save_pdo_options'>Save Options</button>
			<?php $config = new conFig(); $option = $config->xml->days_off_options;?>
			Custom Inline CSS: <textarea id='inline_css' col='120' rows='3'><?php echo $option->inline_css;?></textarea> <i>div#pdo_cal - all, div#pdo_cal table - table</i><br/>
			Devotions Text:	<textarea id='devotions_text' col='60' rows='2'><?php echo $option->devotions_text;?></textarea>
			Devotions Style: <textarea id='devotions_css' col='60' rows='2'><?php echo $option->devotions_css;?></textarea><br/>
			Header Text: <textarea id='header_text' col='60' rows='2'><?php echo $option->header_text;?></textarea>
			Header Style: <textarea id='header_css' col='60' rows='2'><?php echo $option->header_css;?></textarea><br/>
			Footer Text: <textarea id='footer_text' col='60' rows='2'><?php echo $option->footer_text;?></textarea>
			Footer Style: <textarea id='footer_css' col='60' rows='2'><?php echo $option->footer_css;?></textarea>
		</div>
		<div id='pdo_cal'>
		</div>
	</div>
</div>
</div>
</body>
</html>
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
<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/calendar.css" rel="stylesheet">
<link href="../css/rota_style.css" rel="stylesheet">


<style>
	div#hide_menu{
		border: 1px solid black;
		display: none;
	}
</style>
<script>

// FOR THE CALENDAR
function after_push_date(){
	radio_toggle_listener();
	prep_cal_slider($('table#rota_cal'));
}

// Updating:
function update_rota_db(obj){ // Save it all to the current sheet, basically
	data = new Array();
	$('table#rota_cal td[data-save=1]').each(function(){
		cell = {};
		cell.pos_id = $(this).find('div.rota_staff_buttons button[data-clicked=1]').val();	
		if(!cell.pos_id || cell.pos_id == 'undefined') cell.pos_id = 0;
		cell.date = $(this).attr('data-date');
		cell.shift_id = $(this).attr('data-shift');
		cell.staff_id = $(this).attr('data-staff');
		data.push(cell);
		$(this).attr('data-save','0');
	});
	
	sheet = 1; // HARD CODE - this will need to be variable, after you give a sheet capability to the rota
	group_id = $('table#rota_cal').attr('data-group_id');
	$.ajax({
		type:'POST',
		url:'../actions/a_rota_update_db.php',
		data:{
			data:data,
			sheet:sheet,
			group_id:group_id
		},
		success:function(data){
			$("#action_response").html(data);
			clear_rota_class();
			update_rota_class(obj);
		}
	});
}
function update_rota_class(obj){ // Run the queries & apply the proper classes
	action = $(obj).attr('data-action');
	table = $('table#rota_cal');
	group_id = table.attr('data-group_id');
	start_date = table.attr('data-start_date');
	end_date = table.attr('data-end_date');
	if(action == 'json')
	$.ajax({
		dataType: "json",
		type:'POST',
		url:'../actions/a_rota_update_class.php',
		data:{
			group_id:group_id,
			start_date:start_date,
			end_date:end_date,
			action: action
		},
		success:function(data){
			$(data).each(function(){
				update_class_instance(this);
			});
		}
	});
	else
	$.ajax({
		type:'POST',
		url:'../actions/a_rota_update_class.php',
		data:{
			group_id:group_id,
			start_date:start_date,
			end_date:end_date,
			action: action
		},
		success:function(data){
			$('#action_response').append("<br/>" + data);
		}
	});
	
}
function update_class_instance(data){
	// Get the td parent object/s
	td_call = '';
	if(data['staff_id']!='0') td_call += '[data-staff=' + data['staff_id'] + ']';
	if(data['date']!='0') td_call += '[data-date=' + data['date'] + ']';
	if(data['shift_id']!='0') td_call += '[data-shift=' + data['shift_id'] + ']';
	td = $('table#rota_cal td' + td_call);
	
	// Choose which places to apply the class
	obj = false;
	obj2 = false; // does nothing yet
	if(data['pos_id'] != '0')
		button = td.find('button.pos_select[value=' + data['pos_id'] + ']');
	else
		button = td.find('button.pos_select');
		
	switch(data['class']){
		case 'rq_severe':
			break;
		case 'rq_away':
			obj = td;
			break;
		case 'rq_conflict':
			break;
		case 'rq_av-1':
			obj = td;
			break;
		case 'rq_av0':
			obj = td;
			break;	
		case 'rq_av1':
			obj = td;
			break;
		case 'rq_av2':
			obj = td;
			break;
		case 'rq_av3':
			obj = td;
			break;
		case 'rq_fixed':
			obj = td.find('div.td_div');
			break;
		case 'rq_pos_fixed':
			obj = button.parent();
			break;
			
		case 'rq_needs_under':
			obj = button.parent();
			break;
		case 'rq_needs_over':
			obj = button.parent();
			break;
		case 'rq_pos_under':
			obj = button.parent();
			break;
		case 'rq_pos_over':
			obj = button.parent();
			break;
		case 'rq_contract_under':
			obj = td.find('div.td_div');
			break;
		case 'rq_contract_over':
			obj = td.find('div.td_div');
			break;
	}
	// Apply it
	if(obj)
		obj.addClass(data['class']);
		
	// Messages
	button.each(function(){
		if($(this).attr('title'))
			$(this).attr('title', $(this).attr('title') + '\n' + data['message']);
		else $(this).attr('title',data['message']);
	})
}
function clear_rota_class(){ 
	class_string = "rq_away rq_severe rq_conflict rq_av1 rq_av2 rq_av3 rq_fixed rq_pos_fixed rq_needs_under rq_needs_over rq_pos_under rq_pos_over rq_contract_under rq_contract_over";
	
	$('table#rota_cal td').removeClass(class_string);
	$('table#rota_cal td div.td_div').removeClass(class_string);
	$('table#rota_cal td div.button_div').removeClass(class_string);
	$('table#rota_cal td button').removeClass(class_string);
	$('table#rota_cal td button').removeAttr('title');
}

// Radio Buttons - My Custom
function radio_toggle(obj){
	if($(obj).hasClass('incapable') || $(obj).hasClass('rq_away') || $(obj).closest('td').hasClass('rq_away') || $(obj).parent().parent().hasClass('rq_av0') )
		return;
	$(obj).closest('td').attr('data-save','1');
	if($(obj).attr('data-clicked') == '1'){
		$(obj).attr('data-clicked','0');
		$(obj).removeClass('selected');
		$(obj).addClass('unselected');
	}
	else{
		$(obj).closest('td').find('button.radio_toggle[data-clicked=1]').each(function(){
			$(this).attr('data-clicked','0');
			$(this).removeClass('selected');
			$(this).addClass('unselected');
		});
		$(obj).attr('data-clicked','1');
		$(obj).addClass('selected');
		$(obj).removeClass('unselected');
	}
}
function radio_hover(){
	$('button.radio_toggle').hover(
	function(){ //Handler in, mouseenter
		td = $(this).closest('td');
		staff_id = td.attr('data-staff');
		date = td.attr('data-date');
		shift_id = td.attr('data-shift');
		$('table#rota_cal th[data-staff='+staff_id+']').addClass('highlight');
		$('table#rota_cal th[data-date='+date+']:first').addClass('highlight');
		$('table#rota_cal th[data-date='+date+'][data-shift='+shift_id+']').addClass('highlight');
	},
	function(){ // Handler out, mouseleave
		td = $(this).closest('td');
		staff_id = td.attr('data-staff');
		date = td.attr('data-date');
		shift_id = td.attr('data-shift');
		$('table#rota_cal th[data-staff='+staff_id+']').removeClass('highlight');
		$('table#rota_cal th[data-date='+date+']:first').removeClass('highlight');
		$('table#rota_cal th[data-date='+date+'][data-shift='+shift_id+']').removeClass('highlight');
	});
}
function radio_toggle_listener(){
	$('button.radio_toggle').off();
	$('button.radio_toggle').click(function(){
		radio_toggle(this);
	});
	radio_hover();
}

// ToolTips, Scroll Bar, etc
function toggle_tips(obj){
	if($(obj).attr('data-tips') == '1'){
		$(obj).attr('data-tips','0');
		$(document).tooltip( "destroy" );
	}
	else{
		$(obj).attr('data-tips','1');
		$( document ).tooltip();
	}
}
function prep_cal_slider(cal){
	//prep_cal_slider($('table#rota_cal'));
	div_height = $('div#scroll_content').height();
	th_height = cal.find('thead').height();
	tr_height = cal.find('tbody tr:first').height();
	
	count = Math.floor((div_height - th_height) / tr_height) - 1;
	
	
	cal.attr('data-rows_to_show', count);
}
function show_tr(percent, cal){
	i = 1;
	tr = cal.find('tbody tr');
	total = tr.size();
	count = cal.attr('data-rows_to_show');
	hide = (-percent/100) * (total-count);
	
	//alert('hide:' + hide + ' count:' + count + ' size:' + total);
	tr.each(function(){
		if(i > hide)
			$(this).removeClass('scroll_hidden');
		else
			$(this).addClass('scroll_hidden');
		i++;
	});
}
function push_slider(value){

}
$(function(){
	radio_toggle_listener();
	
	//SLIDER
   $( "div#scroll_bar" ).slider({
		orientation: "vertical",
		range: "min",
		min: -100,
		max: 0,
		value: 0,
		change: function( event, ui ) {
			show_tr(ui.value, $('table#rota_cal'));
		}
	});
	prep_cal_slider($('table#rota_cal'));
});



function toggle_hide_menu(){
	hide_menu = $('div#hide_menu');
	if(hide_menu.attr('data-hidden') == '0'){
		hide_menu.hide();
		hide_menu.attr('data-hidden','1');
		return;
	}
	
	hide_menu.show();
	hide_menu.attr('data-hidden','0');
	
	if(hide_menu.html() != 'unfilled')
		return;
	
	data = {};
	data.shift =  new Array();
	data.staff = new Array();
	data.date = new Array();
	$('table#rota_cal th').each(function(){
		if($(this).attr('data-staff') && !$(this).attr('data-date') && !$(this).attr('data-shift')){
			i = {};
			i.id = $(this).attr('data-staff');
			i.name = $(this).html();
			data.staff.push(i);
		}
		else if(!$(this).attr('data-staff') && $(this).attr('data-date') && !$(this).attr('data-shift')){
			i = {};
			i.id = $(this).attr('data-date');
			i.name = $(this).html();
			data.date.push(i);
		}
		else if(!$(this).attr('data-staff') && $(this).attr('data-date') && $(this).attr('data-shift')){
			i = {};
			i.id = $(this).attr('data-shift');
			i.date = $(this).attr('data-date');
			i.date_name = $("table#rota_cal th[data-date='" + i.date + "']").first().html();
			i.name = $(this).html();
			data.shift.push(i);
		}
	});
	
	html = "<h3>Staff</h3><div class='hide_staff'>";
	$(data.staff).each(function(){
		html += "<input type='checkbox' value='" + this.id + "'>" + this.name + "<br/>";
	});
	/* Hide Shifts - not working currently, but something to consider.
	html += "</div><h3>Shifts</h3><div class='hide_shift'>";
	$(data.shift).each(function(){
		html += "<input type='checkbox' value='" + this.id + "' data-date='" + this.date + "'>" + this.date_name + ": " + this.name + "<br/>";
	});
	*/
	html += "</div><h3>Dates</h3><div class='hide_date'>";
	$(data.date).each(function(){
		html += "<input type='checkbox' value='" + this.id + "'>" + this.name + "<br/>";
	});
	html += "</div>";
	hide_menu.html(html);
	hide_menu.accordion({
		collapsible: true,
		active:false,
		heightStyle:"content"
	});
	
	hide_check_listener();
}
function toggle_hide_item(obj){
	hide = false;
	if($(obj).prop('checked'))
		hide = true;
	
	id = $(obj).val();
	div = $(obj).closest('div[class^=hide]');
	if(div.hasClass('hide_staff'))
		type = 'staff';
	else if(div.hasClass('hide_shift')){
		type = 'shift';
		date = $(obj).attr('data-date');
		/*	this is not working!
		th = $("table#rota_cal th[data-shift='" + id + "']").first();
		colspan = parseInt(th.attr('colspan')) - 1;
		th.attr('colspan',colspan.toString());
		*/
	}
	else if(div.hasClass('hide_date'))
		type = 'date';
	
	$("table#rota_cal th[data-" + type + "='" + id + "'], table#rota_cal td[data-" + type + "='" + id + "']").each(function(){
		if(hide) $(this).hide();
		else $(this).show();
	});
}
function hide_check_listener(){
	$('div#hide_menu input:checkbox').off();
	$('div#hide_menu input:checkbox').change(function(){
		toggle_hide_item(this);
	});
}
function reset_hide_box(){
	$('div#hide_menu').html('unfilled');
	$('div#hide_menu').hide();
	$('div#hide_menu').attr('data-hidden','1');
}

</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
?>

<div id='action_response'></div>
<button onclick=update_rota_db(this); data-action='json'>Update</button>
<button onclick=update_rota_db(this); data-action='test'>Update Test</button>
<!--
<button onclick=clear_rota_class();>Clear Classes</button>
<button onclick=toggle_tips(this); data-tips='0'>Toggle Tips</button>
-->
<button onclick=toggle_hide_menu();>Hide things</button>
<div id='hide_menu' data-hidden='1'>unfilled</div>

<div id='scroll_bar' style='margin-top:75px;'></div>
<div id='scroll_content' style='height:502px;'>
<?php
require_once('../includes/rota_classes.php');

$rota = new Rota($con);
$rota->group = new dGroup($con,2);
$rota->type = ROTA_STAFF;
$rota->init();
$rota->show();
?>
<div>
</div>
</div>
</body>

</html>
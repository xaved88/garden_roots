<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Mod Staff</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/staff_selector.js"></script>
<script src="../js/calendar.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<style>
	div.day_div{	height:20px; border-bottom:1px solid black;text-align:center;	}
	div.day_name{		font-weight: bold; height:20px; border-bottom:1px solid black;text-align:center;	}
	div.day_div.nonmonth{	font-style:italic;	}
	
	table.av_overview, table.av_overview tr, table.av_overview th, table.av_overview td{border: 2px solid black;}
	table.av_overview{border-collapse:collapse;}
	table.av_overview th{width: 100px;}
	table.av_overview td{padding: 0px}
	
	table.av_temp_calendar, table.av_temp_calendar tr, table.av_temp_calendar td{border: 1px solid black;}
	table.av_temp_calendar{border-collapse:collapse;}
	table.av_temp_calendar td{width: 100px; height: 100px;}
	
	.float_left{ float:left;}
	.float_right{ float:right;}
	
	div.shift_av{ 
		cursor: pointer;
		-webkit-user-select: none;  /* Chrome all / Safari all */
		-moz-user-select: none;     /* Firefox all */
		-ms-user-select: none;   
	}
	div.shift_av.selected{border: 2px solid darkorange;}
	.av0{ background-color:#666;}
	.av1{ background-color:#ccc; }
	.av2{ background-color:#fff; }
	.av3{ background-color:violet; }
	
	div.shift_av.ex, div.shift_av.temp{ font-style:italic; text-decoration:underline;}
	
	div.av_editor{ margin-top:10px;}
	div.av_editor div{ float:left;}
	div.av_editor div.shift_selector{float:none; margin-bottom:10px; text-align:right;}
	div.av_editor input[type='checkbox']{ margin-left:15px;}
	
	.hidden{ display:none; }
	
</style>
<script>
// Saving it all
function save_staff(){ // position-combo currently blank. needs to be added here in the jquery, and in the actual staff class form. the action page will handle it just fine.
	saveSched();
	// data harvesting
	// Details data
	staff_id = $('.staff_selector').attr('data-selected');
	details = {};
	details.staff_id = staff_id;
	details.first_name = $('#tabs-details input.input_staff_first_name').val();
	details.last_name = $('#tabs-details input.input_staff_last_name').val();
	details.type = $('#tabs-details input.input_staff_type:checked').val();
	details.partner = $('#tabs-details select.input_staff_partner').val();
	details.gender = $('#tabs-details input.input_staff_gender:checked').val();
	details.email = $('#tabs-details input.input_staff_email').val();
	details.email2 = $('#tabs-details input.input_staff_email2').val();
	details.phone = $('#tabs-details input.input_staff_phone').val();
	details.phone2 = $('#tabs-details input.input_staff_phone2').val();
	details.mailing_address = $('#tabs-details input.input_staff_mailing_address').val();
	details.birthday = $('#tabs-details input.input_staff_birthday').val();
	// Lang data
	lang = new Array();
	$('#tabs-lang td.lang_inst[data-save=1]').each(function(){
		lang.push($(this).attr('data-lang_id'));
	});
	// Contract data
	contract = {};
	contract.min = $('#tabs-pos div.pos_con input.pos_con_min').val();
	contract.max = $('#tabs-pos div.pos_con input.pos_con_max').val();
	contract.pref = $('#tabs-pos div.pos_con input.pos_con_pref').val();
	
	// Pos data
	pos = new Array();
	$('#tabs-pos td.pos_inst[data-save=1]').each(function(){
		var tr = $(this).parent();
		var p = {};
		p.pos_id = $(this).attr('data-pos_id');
		p.skill = tr.find('input.pos_skill').val();
		p.pref = tr.find('input.pos_pref').val();
		p.min =  tr.find('input.pos_min').val();
		p.max = tr.find('input.pos_max').val();
		p.training_hours = tr.find('input.pos_training_hours').val();
		p.training_start_date = tr.find('input.pos_training_start_date').val();
		p.combo = '';
		pos.push(p);
	});
	// Here data
	here = new Array();
	$('#tabs-sched tr.here_inst[data-save=1]').each(function(){
		var h = {};
		h.start_date = $(this).find('input.here_start_date').val();
		h.end_date = $(this).find('input.here_end_date').val();
		here.push(h);
	});
	// Away data
	away = new Array();
	$('#tabs-sched tr.away_inst[data-save=1]').each(function(){
		var a = {};
		a.start_date = $(this).find('input.away_start_date').val();
		a.end_date = $(this).find('input.away_end_date').val();
		a.type = $(this).find('select.away_type').val();
		away.push(a);
	});
	/*
	// AV - Template Data
	av_temp = new Array();
	$('#tabs-sched table#calendar_av_temp div.av_sched_inst[data-save=1]').each(function(){
		td = $(this).parent();
		var a = {};
		a.day = td.attr('data-day');
		a.shift_id = td.attr('data-shift');
		a.pos_id = td.find('select.av_pos').val();
		a.pref = td.find('div.av_pref_slider').slider('option','value');
		a.fixed = td.find('div.av_fixed_buttons button.select').attr('data-value');
		av_temp.push(a);
	});
	// AV - Instance Data
	av_inst = new Array();
	start_date = $('#tabs-sched table#calendar_av_inst').attr('data-start_date');
	end_date = $('#tabs-sched table#calendar_av_inst').attr('data-end_date');
	$('#tabs-sched div#tabs-av_inst table#calendar_av_inst div.av_sched_inst[data-save=1]').each(function(){
		td = $(this).parent();
		var a = {};
		a.date = td.attr('data-date');
		a.shift_id = td.attr('data-shift');
		a.pos_id = td.find('select.av_pos').val();
		a.pref = td.find('div.av_pref_slider').slider('option','value');
		a.fixed = td.find('div.av_fixed_buttons button.select').attr('data-value');
		if(a.fixed != '1') a.fixed = '0';
		av_inst.push(a);
	});
	*/
	// Posting it
	$.ajax({
		type:'POST',
		url:'../actions/a_staff_mod.php',
		data:{
			staff_id: staff_id,
			details: details,
			lang: lang,
			pos: pos,
			contract: contract,
			here: here,
			away: away,
			start_date: start_date,
			end_date: end_date
		},
		success:function(data){
			$("#action_response").html(data);

		}
	});
}

// Lang, Pos, page specific functions:
function lang_add(obj){
// data prep
	var tr = $(obj).parent(); //
	var divider = $(tr).parent().find('tr.divider');
	var td = $(tr).find('td.lang_add');
	var sel = $(td).find('select');
// make sure it has a value
	if(sel.val() == 0)
		alert("Please select a language to add.");
	else{ // add it
		id = sel.val();
		name = '';
		sel.find('option').each(function(){
			if($(this).attr('value') == id){
				name = $(this).html();
				$(this).remove();
			}
		});
		
		divider.before("<tr><td class='lang_inst add' data-lang_id='" + id + "' data-save='1'>" + name + "</td><td class='table_button remove' onclick=lang_del(this);>X</td></tr>");
	}
}
function lang_del(obj){
	var par = $(obj).parent();
	var td = $(par).find('td.lang_inst');
	if(td.attr('data-save') == 1){
		td.attr('data-save',0);
		td.addClass('del');
		$(obj).html('+');
	}
	else{
		td.attr('data-save',1);
		td.removeClass('del');
		$(obj).html('X');
	}
}
function pos_add(obj){
// data prep
	var tr = $(obj).parent(); //
	var divider = $(tr).parent().find('tr.divider');
	var td = $(tr).find('td.pos_add');
	var sel = $(td).find('select');
// make sure it has a value
	if(sel.val() == 0)
		alert("Please select a position to add.");
	else{ // add it
		id = sel.val();
		name = '';
		sel.find('option').each(function(){
			if($(this).attr('value') == id){
				name = $(this).html();
				$(this).remove();
			}
		});
		
		divider.before("<tr><td class='pos_inst add' data-pos_id='" + id + "' data-save='1'>" + name + "</td><td><input class='pos_pref' type='number' value='0'></td><td><input class='pos_min' type='number' value='0'></td><td><input class='pos_max' type='number' value='0'></td><td><input class='pos_training_hours' type='number' value='0'></td><td><input class='pos_training_start_date' type='date' value='0'></td><td><input class='pos_skill' type='number' value='0'></td><td class='table_button remove' onclick=pos_del(this);>X</td></tr>");
	}
}
function pos_del(obj){
	var par = $(obj).parent();
	var td = $(par).find('td.pos_inst');
	if(td.attr('data-save') == 1){
		td.attr('data-save',0);
		td.addClass('del');
		$(obj).html('+');
	}
	else{
		td.attr('data-save',1);
		td.removeClass('del');
		$(obj).html('X');
	}
}
function here_add(obj){
	new_tr = "<tr class='here_inst' data-save='1'><td><input class='here_start_date' type='date'></td><td><input class='here_end_date' type='date'></td><td class='table_button remove' onclick='here_del(this);'>X</td></tr>";
	//new_tr = "<tr class='here_inst' data-save='1'><td><input class='here_date' type='date'></td><td><select class='here_type'><option value='1'>Arrival</option><option value='2'>Departure</option></select></td><td class='table_button remove' onclick=here_del(this);>X</td></tr>";
	
	$('table.staff_here').append(new_tr);
}
function here_del(obj){
	var tr = $(obj).parent();
	if(tr.attr('data-save') == 1){
		tr.attr('data-save',0);
		tr.addClass('del');
		$(obj).html('+');
	}
	else{
		tr.attr('data-save',1);
		tr.removeClass('del');
		$(obj).html('X');
	}
}
function away_add(obj){
	new_tr = "<tr class='away_inst' data-save='1'><td><input class='away_start_date' type='date'></td><td><input class='away_end_date' type='date'></td><td><select class='away_type'><option value='2'>Vacation</option><option value='4'>Other</option></select></td><td class='table_button remove' onclick=away_del(this);>X</td></tr>";
	$('table.staff_away').append(new_tr);
}
function away_del(obj){ // just uses the here_del() function
	here_del(obj);
}

// Special Internal Functions
function remove_all_shifts(){
	date_start = $('input#remove_shifts_from').val();
	date_end = $('input#remove_shifts_to').val();
	staff_id = $('.staff_selector').attr('data-selected');
	staff_name = $('div.staff_selector_member[data-staff_id=' + staff_id + ']').text();
	if(confirm("Really remove all shifts for " + staff_name + " from all rotas from " + date_start + " til " + date_end + "?\n\nThis action cannot be undone and can really mess you up."))
	if(confirm("Like, for real for real?")){
		$.ajax({
		type:'POST',
		url:'../actions/a_staff_mod.php',
		data:{
			staff_id: staff_id,
			date_start: date_start,
			date_end: date_end,
			action: 'remove_from_schedule'
		},
		success:function(data){
			$("#action_response").html(data);

		}
		});
	}
}

// AV Functions (PRETTY MUCH ALL NEW v0.4_d_3)
function sched_lib_fetch(){
	$.ajax({
		dataType:'json',
		type:'POST',
		url:'../disp/d_staff_mod.php',
		data:{ 
			action: 'lib',
			staff_id: false
		},
		success:function(data){
			setLibrary(data);
			sched_after_ss();
		},
		error:function(data){
			console.log(data);
			$('#action_response').html('Error loading file: AJAX call failed. Logging to console.<br/>');
			$('#action_response').append(data.responseText);	
			sched_after_ss();
		}
		
	});
}
function setLibrary(data){
	lib = $('div#library');
	$.each(data['pos'], function(){
		lib.append("<div class='pos' data-pos_id='"+this['pos_id']+"' data-name='"+this['name']+"' data-abbr='"+this['abbr']+"'></div>");
	});
	$.each(data['shift'], function(){
		lib.append("<div class='shift' data-shift_id='"+this['shift_id']+"' data-name='"+this['name']+"' data-abbr='"+this['abbr']+"'></div>");
	});
	$.each(data['pref'], function(index,value){
		lib.append("<div class='pref' data-pref_id='"+index+"' data-name='"+value+"'></div>");
	});
}
function fetchLibrary(type,id,element){
	lib = $('div#library');
	
	selector = 'div.'+type+'[data-'+type+'_id='+id+']';
	attr = 'data-'+element;
	data = lib.find(selector).attr(attr);
	return data;
}
function updateSched(){
	table = $('table.av_overview');
	
	table.find('div.shift_av').each(function(){
		span = $(this).find('span.pos_disp');
		pos_id = span.attr('data-pos_id');
		if(pos_id != 0){
			data = fetchLibrary('pos',pos_id,'name');
			span.html(data);
		}
		else
			span.html("");
		if($(this).attr('data-type') == 'ex')
			$(this).addClass('ex');
		else
			$(this).removeClass('ex');
	});
	
	table = $('table.av_temp_calendar');
	
	table.find('div.shift_av').each(function(){
		span = $(this).find('span.pos_disp');
		pos_id = span.attr('data-pos_id');
		if(pos_id != 0){
			data = fetchLibrary('pos',pos_id,'name');
			span.html(data);
		}
		else
			span.html("");
		if($(this).attr('data-type') == 'temp')
			$(this).addClass('temp');
		else
			$(this).removeClass('temp');
	});
}

function saveSched(){
	// GET THE DATA
	date = $("table.av_overview input.push_date[type=date]").val();
	staff_id = $('.staff_selector').attr('data-selected');
	
		// EXCEPTIONS FROM THE OVERVIEW
	ex = [];
	$('table.av_overview div.shift_av[data-type=ex]').each(function(){
		t = $(this);
		info = {};
		info.pos_id = t.find('span.pos_disp').attr('data-pos_id');
		info.shift_id = t.attr('data-shift_id');
		info.date =	t.closest('td').attr('data-date');
		info.pref = t.attr('data-pref');
		
		ex.push(info);
	});
		// TEMPLATES:
	updateAvTemp();
	temp = [];
	$('div#av_temp_lib div.av_temp_inst[data-date!=type]').each(function(){
		t = $(this);
		info = {};
		info.day = t.attr('data-day');
		info.shift_id = t.attr('data-shift_id');
		info.pos_id = t.attr('data-pos_id');
		info.pref = t.attr('data-pref');
		info.start_date = t.attr('data-start_date');
		info.end_date = t.attr('data-end_date');
		info.type = t.attr('data-date');
		
		temp.push(info);
	});
	
	
	start_date = $("table.av_overview").attr('data-start_date');
	end_date = $("table.av_overview").attr('data-end_date');
	
	console.log("Start:" + start_date + " End:" + end_date);
	// SEND IT
	$.ajax({
		type:'POST',
		url:'../actions/a_staff_mod.php',
		data:{
			action:'save_avn',
			staff_id: staff_id,
			start_date: start_date,
			end_date: end_date,
			temp:temp,
			ex:ex
		},
		success:function(data){
			$("#action_response").html(data);
			// RELOAD IT
			reloadSched(date,0);
		}
	});
}
function pushSched(date,push){
	table = $('table.av_overview');
	if(date && date <= table.attr('data-month_end') && date >= table.attr('data-month_start'))
		return;
	else{
		date = $('div#av_acc input.push_date').val();
		reloadSched(date,push);
	}
}
function reloadSched(date,push){
	if(!date)
		date = false;
	if(!push)
		push = 0;
	staff_id = $('div.staff_selector').attr('data-selected');
	$.ajax({
		type:'POST',
		url:'../disp/d_staff_mod.php',
		data:{
			staff_id: staff_id,
			action: 'avn',
			date: date,
			push: push
		},
		success:function(data){
			$("div#av_acc").html(data);
			avn_after_ss();
			
		}
	});
	
}

function updateAvTemp(){
	table = $('table.av_temp_calendar');
	value = $('select#av_temp_select').val();
	value_pre = table.attr('data-current_date');
	lib = $('div#av_temp_lib');
	start_date = $('input#av_temp_start_date').val();
	end_date = $('input#av_temp_end_date').val();
	
	dateStuff = "";
	if(start_date && end_date)
		dateStuff = " data-start_date='"+start_date+"' data-end_date='"+end_date+"'";

	$('select#av_temp_select option[value='+value_pre+']').attr('data-start_date',start_date).attr('data-end_date',end_date);
	
	if(value_pre){
		// DELETE ALL THE CURRENT ONES TO THAT TYPE
		lib.find('div.av_temp_inst[data-date='+value_pre+']').remove();
		
		// ADD IT
		table.find('div.shift_av.temp').each(function(){
			day = $(this).closest('td').attr('day');
			shift_id = $(this).attr('data-shift_id');
			pref = $(this).attr('data-pref');
			pos_id = $(this).find('span.pos_disp').attr('data-pos_id');
			lib.append("<div class='av_temp_inst' data-day='"+day+"' data-shift_id='"+shift_id+"' data-pos_id='"+pos_id+"'  data-date='"+value_pre+"' data-pref='"+pref+"'"+dateStuff+"></div>");
		});
	}
	
	table.attr('data-current_date',value);
	
	// CLEAR PREVIOUS STUFF AND SET BLANK TYPE TEMPLATE
	table.find('div.shift_av').each(function(){
		$(this).removeClass('ex temp av1 av2 av3').addClass('av0').attr('data-pos_id',0).attr('data-pref',0)
		$(this).attr('data-type','type').attr('data-type-orig','type').attr('data-pref-orig',0);
		$(this).find('span.pos_disp').attr('data-pos_id',0).attr('data-pos_id-orig',0);
	});
	
	// SET THE SET TYPE TEMPLATES
	$('div#av_temp_lib div.av_temp_inst[data-date=type]').each(function(){
		t = $(this);
		if((t.attr('data-start_date')<=value && t.attr('data-end_date')>=value) || ((value=='default'||value=='new') && t.attr('data-start_date')=="0000-00-00" && t.attr('data-end_date')=="0000-00-00"))
			table.find('td[day='+t.attr('data-day')+'] div.shift_av[data-shift_id='+t.attr('data-shift_id')+']').attr('data-pref',t.attr('data-pref')).addClass('av'+t.attr('data-pref')).attr('data-type','type').attr('data-type-orig','type').find('span.pos_disp').attr('data-pos_id',t.attr('data-pos_id')).attr('data-pos_id-orig',t.attr('data-pos_id'));
	});
	
	// GET THE NEW ONES
	$('div#av_temp_lib div.av_temp_inst[data-date='+value+']').each(function(){
		t = $(this);
		table.find('td[day='+t.attr('data-day')+'] div.shift_av[data-shift_id='+t.attr('data-shift_id')+']').attr('data-pref',t.attr('data-pref')).addClass('av'+t.attr('data-pref')).addClass('temp').attr('data-type','temp').find('span.pos_disp').attr('data-pos_id',t.attr('data-pos_id'));
	});
	
	$('input#av_temp_start_date').val($('select#av_temp_select option:selected').attr('data-start_date'));
	$('input#av_temp_end_date').val($('select#av_temp_select option:selected').attr('data-end_date'));
	
	updateSched();
}
function updateAvInstance(obj){//obj = html element "select"
	fix_select_class(obj);
	
	parent = $(obj).closest('div[id^=tabs-avn]');
	
	pos_select = parent.find('div.av_editor select.av_pos_select');
	pref_select = parent.find('div.av_editor select.av_pref_select');
	button = parent.find('div.av_editor button[class^=remove]');
	
	pos_id = pos_select.val();
	pref = pref_select.val();
	
	c = 'av' + pref;
	
	selected = parent.find('div.shift_av.selected');
	
	if(parent.is('[id=tabs-avn_ov'))
		dataType = 'ex';
	else
		dataType = 'temp';
	selected.each(function(){
		t =$(this);
		if(t.attr('data-type')!=dataType){
			s = t.find('span.pos_disp');
			t.attr('data-type-orig',t.attr('data-type'));
			t.attr('data-pref-orig',t.attr('data-pref'));
			s.attr('data-pos_id-orig',s.attr('data-pos_id'));
		}
		button.removeClass('hidden');
	});
	
	selected.attr('data-type',dataType).attr('data-pref',pref).removeClass('av0 av1 av2 av3').addClass(c).find('span.pos_disp').attr('data-pos_id',pos_id);
	
	selected.each(function(){
		t =$(this);
		s = t.find('span.pos_disp');
		if(t.attr('data-pref-orig') == t.attr('data-pref') && s.attr('data-pos_id-orig') == s.attr('data-pos_id')){
			revertAvInstance(this,true);
		}
	});
	
	updateSched();
}
function selectAvInstance(event,obj){//obj = html element div.shift_av

	o = $(obj);
	parent = o.closest('div[id^=tabs-avn]');
	
	if(o.hasClass('selected'))
		selected = true;
	else
		selected = false;
	table = o.closest("table");
	
	button = parent.find('div.av_editor button[class^=remove]');
	
	if(!event.shiftKey)
	table.find('div.shift_av').each(function(){
		$(this).removeClass('selected');
	});
	
	if(selected){
		button.addClass('hidden');
		o.removeClass('selected');
		return false;
	}
		
	o.addClass('selected');
	
	pos_id = o.find('span.pos_disp').attr('data-pos_id');
	pref = o.attr('data-pref');
	
	pos_select = parent.find('div.av_editor select.av_pos_select');
	pref_select = parent.find('div.av_editor select.av_pref_select');
	
	pos_select.val(pos_id);
	pref_select.val(pref);
	//if(o.attr('data-type') == 'ex')
	
	console.log("Data Type:"+o.attr('data-type') + "  Orig:"+o.attr('data-type-orig'));
	if(o.attr('data-type-orig') && (o.attr('data-type') != o.attr('data-type-orig')))
		button.removeClass('hidden');
	else
		button.addClass('hidden');
	
	//if(o.hasClass('av0'))
		c = 'av0';
	//else if(o.hasClass('av1'))
	if(o.hasClass('av1'))
		c = 'av1';
	else if(o.hasClass('av2'))
		c = 'av2';
	else if(o.hasClass('av3'))
		c = 'av3';	
	fix_select_class(pref_select, c);
}
function revertAvInstance(obj,skip_update){//obj = optional div.shift_av
	if(obj)
		selected = $(obj);
	else
		selected = $('div.shift_av.selected');
		
	span = selected.find('span.pos_disp');
	if(selected.attr('data-pref-orig') && span.attr('data-pos_id-orig')){
		if(selected.attr('data-typ-orig'))
			selected.attr('data-type', selected.attr('data-type-orig'));
		else
			selected.removeAttr('data-type');
		selected.attr('data-pref', selected.attr('data-pref-orig'));
		
		selected.removeClass('av0 av1 av2 av3');
		selected.addClass('av'+selected.attr('data-pref'));
		
		span.attr('data-pos_id', span.attr('data-pos_id-orig'));
	}
	selected.removeClass('ex temp');
	button.addClass('hidden');
	
	if(!skip_update)
		updateSched();
}
function fix_select_class(obj, class_to_give){ // select element
	o = $(obj);
	o.removeClass('av0 av1 av2 av3');
	s = o.find('option[value='+o.val()+']');
	if(!class_to_give){
		if(s.hasClass('av0')) class_to_give = 'av0';
		else if(s.hasClass('av1'))  class_to_give = 'av1';
		else if(s.hasClass('av2')) class_to_give = 'av2';
		else if(s.hasClass('av3')) class_to_give = 'av3';
	}
	
	o.addClass(class_to_give);
	
	
}
function toggleAvShift(obj){ // obj = html element input[type=checkbox]
	o = $(obj);
	shift_id = o.val();
	if(o.is(':checked'))
		checked = true;
	else
		checked = false;
	
	parent = o.closest('div[id^=tabs-avn]');
	divs = parent.find('div.shift_av[data-shift_id='+shift_id+']');
	if(checked)
		divs.removeClass('hidden');
	else
		divs.addClass('hidden');
}

function newSchedListeners(){
	$('select#av_temp_select').off();
	$('div.av_editor select.av_pos_select').off();
	$('div.av_editor select.av_pref_select').off();
	$('div.av_editor button').off();
	$('div.av_editor input').off();
	$('div.shift_av').off();
	$('button#save_avn').off();
	$('div#av_acc .push_date').off();
	
	$('select#av_temp_select').change(function(){updateAvTemp()});
	$('div.av_editor select.av_pos_select,div.av_editor select.av_pref_select').change(function(){updateAvInstance(this);});
	$('div.shift_av').click(function(){selectAvInstance(event,this);});
	$('div.av_editor button[class^=remove]').click(function(){revertAvInstance();});
	$('div.av_editor input.av_shift_toggle').change(function(){toggleAvShift(this);});
	$('button#save_avn').click(function(){saveSched();});
	$('div#av_acc button.push_date').click(function(){pushSched(false,$(this).attr('data-amount'));});
	$('div#av_acc input.push_date').change(function(){pushSched($(this).val(),0);});
}

// Calendar actions
function after_push_date(){
	sched_after_ss();
}

// Staff Selector actions
function staff_selector_selected(staff_id){
	staff_selector_fetch(staff_id,"details");
	staff_selector_fetch(staff_id,"lang");
	staff_selector_fetch(staff_id,"pos");
	staff_selector_fetch(staff_id,"sched");
	$("#save_staff").show();
}
function staff_selector_fetch(staff_id, div){
	$.ajax({
		type:'POST',
		url:'../disp/d_staff_mod.php',
		data:{
			staff_id: staff_id,
			action: div
		},
		success:function(data){
			$("#tabs-" + div).html(data);
			if(div == 'sched'){
				sched_lib_fetch();
			}
		}
	});
}
function sched_after_ss(){
	updateSched();
	$("#sched_accordion").accordion({
		collapsible: true,
		heightStyle: "content",
		active: false
	});
	$("#av_acc").tabs();
	$("div.sched_accordian_av").tabs();
	$(".av_pref_slider").each(function(){
		val = $(this).attr('data-value');
		$(this).slider({
			value:val,
			min: -1,
			max: 3,
			step: 1,
			create: function( event, ui){
				par = $(this).parent();
				td = par.parent();
				td.removeClass('av_pref_slider_-1');
				td.removeClass('av_pref_slider_1');
				td.removeClass('av_pref_slider_2');
				td.removeClass('av_pref_slider_3');
				td.removeClass('av_pref_slider_0');
				td.addClass('av_pref_slider_' + $(this).slider('option','value'));
				if($(this).slider('option','value') == 0)
					par.attr('data-save',0);
				else
					par.attr('data-save',1);
			},
			stop: function( event, ui ) {
			//	td = $(this).parent().parent();
				par = $(this).parent();
				td = par.parent();
				td.removeClass('av_pref_slider_-1');
				td.removeClass('av_pref_slider_1');
				td.removeClass('av_pref_slider_2');
				td.removeClass('av_pref_slider_3');
				td.removeClass('av_pref_slider_0');
				td.addClass('av_pref_slider_' + $(this).slider('option','value'));
				if($(this).slider('option','value') == 0)
					par.attr('data-save',0);
				else
					par.attr('data-save',1);
			}
		});
	});
	$("div.av_fixed_buttons button").off();
	$("div.av_fixed_buttons button").click(function(){
		if($(this).hasClass('select')){
			$(this).removeClass('select');
			$(this).addClass('noselect');
		}
		else{
			$(this).removeClass('noselect');
			$(this).addClass('select');
		}
		/*
		par = $(this).parent();
		// Get This one set
		if($(this).hasClass('noselect'))
			add_it = true;
		else
			add_it = false;
	
		// Reset both
		par.find('button').each(function(){
			$(this).removeClass('select');
			$(this).addClass('noselect');
		});
		
		if(add_it){
			$(this).removeClass('noselect');
			$(this).addClass('select');
		}
		*/
		/*
		par = $(this).parent();
		par.find('button').each(function(){
			if($(this).hasClass('select')){
				$(this).removeClass('select');
				$(this).addClass('noselect');
			}
			else{
				$(this).removeClass('noselect');
				$(this).addClass('select');
			}
		})
		*/
	});
	$("button.toggle_av").click(function(){
		av = $(this).parent().find('div.av_sched_temp');
		if(av.hasClass('hidden')){
			av.removeClass('hidden');
			av.attr('data-save',1);
			$(this).html('X');
			$(av).parent().removeClass('no_bg');
		}
		else{
			av.addClass('hidden');
			av.attr('data-save',0);
			$(this).html('+');
			$(av).parent().addClass('no_bg');		}
		
	});
	newSchedListeners();
	updateAvTemp();
}
function avn_after_ss(){
	$("#av_acc").tabs('destroy');
	$("#av_acc").tabs();
	updateSched();
	newSchedListeners();
	updateAvTemp();
}
$(function() {
	$( "#mod_staff" ).tabs();
	$( "#save_staff").hide();
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
include('../includes/staff_selector.php');
?>
<div id='action_response'></div>
<button id='save_staff' onclick=save_staff();>Save</button>
<div id='mod_staff'>
	<ul>
		<li><a href="#tabs-details">Staff Details</a></li>
		<li><a href="#tabs-lang">Languages</a></li>
		<li><a href="#tabs-pos">Positions</a></li>
		<li><a href="#tabs-sched">Schedule</a></li>
	</ul>
	<div id="tabs-details"></div>
	<div id="tabs-lang"></div>
	<div id="tabs-pos"></div>
	<div id="tabs-sched"></div>
</div>
<div id='library'></div>
</body>

</html>
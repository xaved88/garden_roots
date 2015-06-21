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

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<script>
// Saving it all
function save_staff(){ // position-combo currently blank. needs to be added here in the jquery, and in the actual staff class form. the action page will handle it just fine.
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
			av_temp: av_temp,
			av_inst: av_inst,
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
			if(div == 'sched')
				sched_after_ss();
		}
	});
}
function sched_after_ss(){
	$("#sched_accordion").accordion({
		collapsible: true,
		heightStyle: "content",
		active: false
	});
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
	/*
	$("#calendar_av_temp td").click(function(){
		av = $(this).find('div.av_sched_temp');
		if(av.hasClass('hidden')){
			av.removeClass('hidden');
			av.attr('data-save','yes');
		}
	});
	$("#calendar_av_temp td button.hide_av").off();
	$("#calendar_av_temp td button.hide_av").click(function(event){
		if(event.shiftKey)
		av = $(this).parent();
		av.addClass('hidden');
		av.attr('data-save','no');
	});
	*/
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

</body>

</html>
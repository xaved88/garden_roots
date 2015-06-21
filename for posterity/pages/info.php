<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Info</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<script>
// Saving functions
function lang_save(){
	action = 'lang';
	data = new Array();
	// harvest data
	$('table.info_lang tr.lang_inst').each(function(){
		if($(this).attr('data-changed') != 'no'){
			row = {};
			row.action = $(this).attr('data-changed');
			row.lang_id = $(this).attr('data-lang_id');
			row.order = $(this).find('td.order').attr('data-order');
			row.name = $(this).find('input.input_lang_name').val();
			row.abbr = $(this).find('input.input_lang_abbr').val();
			row.alt1 = $(this).find('input.input_lang_alt1').val();
			row.alt2 = $(this).find('input.input_lang_alt2').val();
			data.push(row);
		}
	});
	// ajax it to the a_ page
	if(data.length > 0)
	$.ajax({
		type:'POST',
		url:'../actions/a_info.php',
		data:{
			data:data,
			action:action
		},
		success:function(data){ // get the return function and update?
			$("#tabs-lang").html(data);
		}
	});
	else
		alert('no changed data for the languages!');
}
function pos_save(){
	action = 'pos';
	data = new Array();
	// harvest data
	$('table.info_pos tr.pos_inst').each(function(){
		if($(this).attr('data-changed') != 'no'){
			$(this).find('.input_pos_pref_strength').slider();
			row = {};
			row.action = $(this).attr('data-changed');
			row.pos_id = $(this).attr('data-pos_id');
			row.order = $(this).find('td.order').attr('data-order');
			row.name = $(this).find('input.input_pos_name').val();
			row.abbr = $(this).find('input.input_pos_abbr').val();
			row.alt1 = $(this).find('input.input_pos_alt1').val();
			row.alt2 = $(this).find('input.input_pos_alt2').val();
			//row.pref_type = $(this).find('input.input_pos_pref_type').val();
			row.pref_type = $(this).find('select.input_pos_pref_type option:checked').val(); //untested
			row.pref_strength = $(this).find('.input_pos_pref_strength').slider("option","value");
			row.pos_type = $(this).find('select.input_pos_type').val();
			//$(this).find('input.input_pos_pref_strength').val();
			//// getter
			//var value = $( ".selector" ).slider( "option", "value" );
			data.push(row);
		}
	});
	// ajax it to the a_ page
	if(data.length > 0)
	$.ajax({
		type:'POST',
		url:'../actions/a_info.php',
		data:{
			data:data,
			action:action
		},
		success:function(data){ // get the return function and update?
			$("#tabs-pos").html(data);
			page_prep();
		}
	});
	else
		alert('no changed data for the positions!');
}
function shift_save(){
	action = 'shift';
	// harvest parent rows
	par = new Array();
	$('table.info_shift tr.shift_par').each(function(){
		if($(this).attr('data-changed') != 'no'){
			row = {};
			row.action = $(this).attr('data-changed');
			row.shift_id = $(this).attr('data-shift_id');
			row.order = $(this).find('td.order').attr('data-order');
			row.name = $(this).find('input.input_shift_name').val();
			row.abbr = $(this).find('input.input_shift_abbr').val();
			row.alt1 = $(this).find('input.input_shift_alt1').val();
			row.alt2 = $(this).find('input.input_shift_alt2').val();
			par.push(row);
		}
	});
	// harvest the instance rows
	inst = new Array();
	$('table.info_shift tr.shift_inst').each(function(){
		if($(this).attr('data-changed') != 'no'){
			row = {};
			row.action = $(this).attr('data-changed');
			row.instance_id = $(this).attr('data-instance_id');
			row.shift_id = $(this).attr('data-shift_id');
			row.name = $(this).find('input.input_shift_inst_name').val();
			row.abbr = $(this).find('input.input_shift_inst_abbr').val();
			row.start_time = $(this).find('input.input_shift_inst_start_time').val();
			row.end_time = $(this).find('input.input_shift_inst_end_time').val();
			row.start_date = $(this).find('input.input_shift_inst_start_date').val();
			row.end_date = $(this).find('input.input_shift_inst_end_date').val();
			inst.push(row);
		}
	});
	// ajax it to the a_ page
	if(inst.length > 0 || par.length > 0)
	$.ajax({
		type:'POST',
		url:'../actions/a_info.php',
		data:{
			data:'',
			par:par,
			inst:inst,
			action:action
		},
		success:function(data){ // get the return function and update?
			$("#tabs-shift").html(data);
		}
	});
	else
		alert('no changed data for the Shifts!');
}
function group_save(){
	action = 'group';
	data = new Array();
	// harvest data
	$('table.info_group tr.group_inst').each(function(){
		if($(this).attr('data-changed') != 'no'){
			row = {};
			row.action = $(this).attr('data-changed');
			row.group_id = $(this).attr('data-group_id');
			row.order = $(this).find('td.order').attr('data-order');
			row.name = $(this).find('input.input_group_name').val();
			row.abbr = $(this).find('input.input_group_abbr').val();
			row.alt1 = $(this).find('input.input_group_alt1').val();
			row.alt2 = $(this).find('input.input_group_alt2').val();
			//row.data = $(this).find('input.input_group_data').val();
			row.data = '';
			first = true;
			$(this).find('input[name=input_group_shift]:checked').each(function(){
				if(!first)
					row.data += ',';
				else
					first = false;
				row.data += $(this).val();
			});
			row.data += ':';
			first = true;
			$(this).find('input[name=input_group_pos]:checked').each(function(){
				if(!first)
					row.data += ',';
				else
					first = false;
				row.data += $(this).val();
			});
			
			data.push(row);
		}
	});
	// ajax it to the a_ page
	if(data.length > 0)
	$.ajax({
		type:'POST',
		url:'../actions/a_info.php',
		data:{
			data:data,
			action:action
		},
		success:function(data){ // get the return function and update?
			$("#tabs-group").html(data);
			group_buttons_listener();
		}
	});
	else
		alert('no changed data for the Groups!');
}

// Order Functions
function info_del(obj){
	tr = $(obj).parent();
	if($(obj).attr('data-action') == 'del'){
		tr.addClass('del');
		tr.attr("data-changed",'del');
		$(obj).attr('data-action','add');
		$(obj).html('+');
	}
	else if($(obj).attr('data-action') == 'add'){
		tr.removeClass('del');
		if(tr.hasClass('changed'))
			tr.attr("data-changed",'yes');
		else
			tr.attr("data-changed",'no');
		$(obj).attr('data-action','del');
		$(obj).html('X');
	}
}
function order_change(obj){
	// find out if there is an active one
	active_td = false;
	active = false;
	clas = $(obj).parent().parent().parent().attr('class');
	$('div#mod_info table.' + clas + ' tr td.table_button.order').each(function(){
		if($(this).hasClass('active')){
			active_td = this;
			active = $(this).attr('data-order');
		}
	});
	// if not, activate this one
	if(!active)
		$(obj).addClass('active');
	// if yes, and it's itself, deactiveate
	else if(active == $(obj).attr('data-order'))
		$(obj).removeClass('active');
	// if no, switch.
	else{
		// select everything current
		a = obj;
		b = active_td;
		a_tr = $(a).parent();
		b_tr = $(b).parent();
		
		// class management
		$(active_td).removeClass('active');
		$(a_tr).addClass('changed');
		$(a_tr).removeClass('del');
		$(a_tr).attr('data-changed','yes');
		$(b_tr).addClass('changed');
		$(b_tr).removeClass('del');
		$(b_tr).attr('data-changed','yes');
		
		//replace the <td> order buttons
		a_text = $(a).wrap('<p/>').parent().html(); $(a).unwrap();
		b_text = $(b).wrap('<p/>').parent().html(); $(b).unwrap();
		$(a).before(b_text); $(a).remove();
		$(b).before(a_text); $(b).remove();
		
		// replace the <tr>'s
		a_tr_text = $(a_tr).wrap('<p/>').parent().html(); $(a_tr).unwrap();
		b_tr_text = $(b_tr).wrap('<p/>').parent().html(); $(b_tr).unwrap();
		$(a_tr).before(b_tr_text); $(a_tr).remove();
		$(b_tr).before(a_tr_text); $(b_tr).remove();
	}
}

// Row Adding Functions
function lang_add(){
	var high_order = 0;
	$('table.info_lang td.order').each(function(){
		if(high_order < $(this).attr('data-order'))
			high_order = $(this).attr('data-order');
	});
	
	var order = parseInt(high_order) + 1;
	$('table.info_lang tr:last').after("<tr class='lang_inst changed' data-lang_id='new' data-changed='yes'><td class='table_button order' data-order='" + order + "' onclick=order_change(this);>" + order + "</td><td><input type='text' class='input_lang_name'></td><td><input type='text' class='input_lang_abbr'></td><td><input type='text' class='input_lang_alt1'></td><td><input type='text' class='input_lang_alt2'></td><td class='table_button remove' onclick=info_del(this); data-action='del'>X</td>");
	
	info_change_listener();
}
function pos_add(){ 
	var high_order = 0;
	$('table.info_pos td.order').each(function(){
		if(high_order < $(this).attr('data-order'))
			high_order = $(this).attr('data-order');
	});
	var order = parseInt(high_order) + 1;

	// Copying and reseting the values to add a new line
	var text = $('table.info_pos tr:last').wrap('<p/>').parent().html(); $('table.info_pos tr:last').unwrap();
	$('table.info_pos tr:last').after(text); // need it to update the id, order, and clear all the values...
	nr = $('table.info_pos tr:last');
	$(nr).attr('data-pos_id','new');
	$(nr).find('td.order').attr('data-order',order);
	$(nr).find('td.order').html(order);
	$(nr).find('input').each(function(){
		$(this).val('');
		$(this).html('');
	});
	$(nr).find('select option:selected').removeAttr('selected');
	$('table.info_pos tr:last').find(".input_pos_pref_strength").each(function(){
		$(this).slider({
			value: 0,
			min: -100,
			max: 100,
			step: 20,
			slide: function( event, ui ) {
				$( "#amount" ).val( "$" + ui.value );
			}
		});
	});
	info_change_listener();
}
function shift_add(){
	var high_order = 0;
	$('table.info_shift td.order').each(function(){
		if(high_order < $(this).attr('data-order'))
			high_order = $(this).attr('data-order');
	});
	var order = parseInt(high_order) + 1;
	
	new_tr = "<tr><th>Order</th><th>Shift</th><th>Abbr</th><th>Alt1</th><th>Alt2</th><th>Add</th><th>Remove</th></tr><tr class='shift_par' data-shift_id='new' data-changed='no'><td class='table_button order' data-order='" + parseInt(order) + "' onclick=order_change(this);>" + parseInt(order) + "</td><td><input type='text' class='input_shift_name'></td><td><input type='text' class='input_shift_abbr'></td><td><input type='text' class='input_shift_alt1'></td><td><input type='text' class='input_shift_alt2'></td><td class='table_button add_inst data-action='add' onclick=shift_inst_add(this);>+</td><td class='table_button remove' data-action='del' onclick=info_del(this);>X</td></tr>";
	$('table.info_shift').append(new_tr);
	info_change_listener();
}
function shift_inst_add(obj){
	tr = $(obj).parent();
	shift_id = tr.attr('data-shift_id');
	
	new_tr = "<tr class='shift_inst' data-shift_id='" + shift_id + "' data-instance_id='new' data-changed='no'><td><input type='text' class='input_shift_inst_name'></td><td><input type='text' class='input_shift_inst_abbr'></td><td><input type='time' class='input_shift_inst_start_time'></td><td><input type='time' class='input_shift_inst_end_time'></td><td><input type='date' class='input_shift_inst_start_date'></td><td><input type='date' class='input_shift_inst_end_date'></td><td class='table_button remove' data-action='del' onclick=info_del(this);>X</td></tr>";
	
	new_table_close = "</table></tr>";
	new_table_open = "<tr><td>-</td><td colspan=6><table class='shift_inst' data-shift_id='" + shift_id + "'><tr><th>Shift</th><th>Abbr</th><th>S.Time</th><th>E.Time</th><th>S.Date</th><th>E.Date</th><th>Remove</th></tr>";
	
	// Find the table of inst if it exists:
	table = false;
	tr.parent().find('table.info_shift_inst').each(function(){
		if($(this).attr('data-shift_id') == shift_id)
			table = this;
	});
	
	// And add the new rows
	if(table)
		$(table).append(new_tr);
	else
		tr.after(new_table_open + new_tr + new_table_close);
	
	info_change_listener();
}
function group_add(){
	
	var high_order = 0;
	$('table.info_group td.order').each(function(){
		if(high_order < $(this).attr('data-order'))
			high_order = $(this).attr('data-order');
	});
	var order = parseInt(high_order) + 1;

	// Copying and reseting the values to add a new line
	var text = $('table.info_group tr:last').wrap('<p/>').parent().html(); $('table.info_group tr:last').unwrap();
	$('table.info_group tr:last').after(text); // need it to update the id, order, and clear all the values...
	nr = $('table.info_group tr:last');
	$(nr).attr('data-group_id','new');
	$(nr).find('td.order').attr('data-order',order);
	$(nr).find('td.order').html(order);
	$(nr).find('input[type=text]').each(function(){
		$(this).val('');
		$(this).html('');
	});
	$(nr).find('input[type=checkbox]:checked').removeAttr('checked');
	info_change_listener();
	group_buttons_listener();
}

// LISTENERS
function page_prep(){
	// FOR POSITIONS
	$( ".input_pos_pref_strength" ).each(function(){
		val = parseInt($(this).attr('data-value'));
		//alert(val);
		//val = -50;
		$(this).slider({
			value:val,
			min: -100,
			max: 100,
			step: 20,
			slide: function( event, ui ) {
				$( "#amount" ).val( "$" + ui.value );
			}
		});
	});
	// FOR GROUPS
	group_buttons_listener();

	// FOR ALL
	info_change_listener();
}
function info_change_listener(){
	$('table[class^=info] input').off();
	$('table[class^=info] input').change(function(){
		tr = $(this).parent().parent();
		tr.addClass('changed');
		if(tr.hasClass('del'))
			tr.removeClass('del');
		tr.attr('data-changed','yes');
	});
}
function group_buttons_listener(){
	$('button[class^=group_data_button]').off();
	$('button[class^=group_data_button]').click(function(){
		shift = $(this).parent().find('.group_data_content_shift');
		pos = $(this).parent().find('.group_data_content_pos');
		if($(this).hasClass('group_data_button_shift')){
			if(shift.attr('data-shown') == 'yes'){
				shift.attr('data-shown','no');
				shift.hide();
			}
			else{
				shift.attr('data-shown','yes');
				shift.show();
			}
			pos.attr('data-shown','no');
			pos.hide();
		}
		else if($(this).hasClass('group_data_button_pos')){
			if(pos.attr('data-shown') == 'yes'){
				pos.attr('data-shown','no');
				pos.hide();
			}
			else{
				pos.attr('data-shown','yes');
				pos.show();
			}
			shift.attr('data-shown','no');
			shift.hide();
		}
	});
}

$(function() {
	$("#mod_info").tabs();
	page_prep();
	
});
</script>
</head>

<body>
<?php 
require_once('../includes/server_info.php');
require_once('../includes/staff_classes.php');
include('../includes/nav_bar.php');
?>
<div id='action_response'></div>
<div id='mod_info'>
	<ul>
		<li><a href="#tabs-lang">Languages</a></li>
		<li><a href="#tabs-pos">Positions</a></li>
		<li><a href="#tabs-shift">Shifts</a></li>
		<li><a href="#tabs-group">Groups</a></li>
	</ul>
	<div id="tabs-lang"><?php
		$lang = new dLangLib($con);
		$lang->form();
	?><button onclick=lang_save();>Save Languages</button></div>
	<div id="tabs-pos"><?php
		$pos = new dPosLib($con);
		$pos->form();
	?><button onclick=pos_save();>Save Positions</button>
	</div>
	<div id="tabs-shift"><?php
		$shift = new dShiftLib($con);
		$shift->form();
	?><button onclick=shift_save();>Save Shifts</button>
	</div>
	<div id="tabs-group"><?php
		$group = new dGroupLib($con);
		$group->form();
	?><button onclick=group_save();>Save Groups</button>
	</div>
</div>
</body>
</html>
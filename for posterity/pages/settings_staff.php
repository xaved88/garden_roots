<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Staff Settings</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<script>
// FROM THE XML VERSION
function staff_av_day_add(obj){
	/* this is the right idea, but not working yet. Anyways, putting it on pause since for right now we can still edit it via the xml document, and that's all that we need.
	alert("Adding a day!");
	tr = $(obj).parent();
	type = tr.find("td.av_inst_type");
	type.attr('rowspan', parseInt(type.attr('rowspan')) + 1);
	add = tr.find("td.table_button.add_day");
	add.attr('rowspan', parseInt(add.attr('rowspan')) + 1);
	
	td
	
	td = new Array();
	
	// staff_av_day: <td class='av_inst_day bottom_border' data-type='new'>
	td.push("<td class='av_inst_day bottom_border' data-type='new'>" + $('.hidden_data.staff_av_day').html() + "</td>");
	// staff_av_shift: <td class='av_inst_shift bottom_border' data-type='new'>";
	td.push("<td class='av_inst_shift bottom_border' data-type='new'>" + $('.hidden_data.staff_av_shift').html() + "</td>");
	// staff_av_remove:
	td.push("<td class='table_button remove_day>X</td>");
	
	$(td).each(function(){
		tr.append(this);
	});
	*/
}
function staff_av_day_remove(obj){
	alert("Removing a day!");
}
function staff_av_listener(){
	$('td.table_button.add_day').off();
	$('td.table_button.add_day').click(function(){
		staff_av_day_add(this);
	});
	
	$('td.table_button.remove_day').off();
	$('td.table_button.remove_day').click(function(){
		staff_av_day_remove(this);
	});
}

// FOR THE MYSQL VERSION
function add_tempav(obj){
	// get the stuff to copy
	tr = $('div#settings_staff_av table.hidden_data.add_row tbody').html();
	// find the closest table and append to it
	$(obj).parent().find('table tbody').append(tr);
}
function save_av(){
	data = new Array();
	$('div#av_types_acc table').each(function(){
		type = {};
		type.type = $(this).attr('data-type');
		type.temp = new Array();
		$(this).find("tr.temp_inst[data-save=yes]").each(function(){
			temp = {};
			temp.day = $(this).find('select.temp_day').val();
			temp.shift_id = $(this).find('select.temp_shift').val();
			temp.pos_id = $(this).find('select.temp_pos').val();
			temp.pref = $(this).find('select.temp_pref').val();
			type.temp.push(temp);
		});
		data.push(type);
	});
	
	$.ajax({
		type:'POST',
		url:'../actions/a_settings.php',
		data:{
			action: 'staff_av',
			data: data
		},
		success:function(data){
			$("#action_response").html(data);
		}
	});
}
function save_other(){
	data = {};
	data.arr_buffer = $('input#arr_buffer').val();
	data.dep_buffer = $('input#dep_buffer').val();
	
	
	$.ajax({
		type:'POST',
		url:'../actions/a_settings.php',
		data:{
			action: 'other',
			data: data
		},
		success:function(data){
			$("#action_response").html(data);
		}
	});
}
function remove_row(obj){
	tr = $(obj).parent();
	if(tr.attr('data-save') == 'yes'){
		tr.attr('data-save','no');
		tr.addClass('del');
		$(obj).html('+');
	}
	else{
		tr.attr('data-save','yes');
		tr.removeClass('del');
		$(obj).html('X');
	}
}
function tempav_listener(){
	$('td.table_button.remove').off();
	$('td.table_button.remove').click(function(){
		remove_row(this);
	});
}
$(function() {
	tempav_listener();
	staff_av_listener();
	$("#settings_staff").accordion({
		collapsible: true,
		heightStyle: "content",
		active: false
	});
	$("#av_types_acc").accordion({
		collapsible: true,
		heightStyle: "content"
	});
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
?>
<div id='action_response'></div>
<div id='settings_staff'>
	<h3>Availability - Staff Type Templates</h3>
	<div id='settings_staff_av'>
	<button onclick=save_av();>Save</button><br/>
	<?php
		require_once('../includes/staff_classes.php');
		$config = new conFig();
		
		$av = new dTemplateAv($con, $action = TEMP_AV_TYPE, $id = null, $name = TEMP_DEFAULT);
		$st = $config->staff_type_array(); //st[i][id|name]
		$shift = new dShiftLib($con, $date = false, $auto_load = true);
		$pos = new dPosLib($con);
		
		echo "<div id='av_types_acc'>";
		foreach($st as $t){
			echo "<h3>{$t['name']}</h3>
			<div>
			<table data-type='{$t['id']}'>
			<tr><th colspan=5>Availability Templates</th></tr>
			<tr><th>Day</th><th>Shift</th><th>Position</th><th>Pref</th><th>Remove</th></tr>";
			if(isset($av->type[(int)$t['id']])){
				$type = $av->type[(int)$t['id']];
				foreach($type as $t){
					echo "<tr class='temp_inst' data-save='yes'><td><select class='temp_day'>";
					foreach($config->day as $cd){
						$selected = '';	if($cd['id'] == $t->day) $selected = ' selected ';
						echo "<option value={$cd['id']}{$selected}>{$cd['name']}</option>";
					}
					echo "</select></td><td><select class='temp_shift'>";
					foreach($shift->par as $s){
						$selected = '';	if($s->shift_id == $t->shift_id) $selected = ' selected ';
						echo "<option value={$s->shift_id}{$selected}>{$s->name}</option>";
					}
					echo "</select></td><td><select class='temp_pos'><option value='0'></option>";
					foreach($pos->ord as $o){
						$p = $pos->pos[$o['pos_id']];
						$selected = '';	if($p->pos_id == $t->pos_id) $selected = ' selected ';
						echo "<option value={$p->pos_id}{$selected}>{$p->name}</option>";
					}
					echo "</select></td><td><select class='temp_pref'><option value='0'></option>";
					foreach($config->pref as $p){
						$selected = '';	if($p['id'] == $t->pref) $selected = ' selected ';
						echo "<option value={$p['id']}{$selected}>{$p['name']}</option>";
						
					}
					echo "</select></td>
					<td class='table_button remove'>X</td></tr>";
				}
			}
			echo "</table><button onclick=add_tempav(this);>Add to template</button>
			</div>";
		}
		echo "</div>";
		
		
		// Stuff for the fill!
		echo "<table class='hidden_data add_row'>";
		echo "<tr class='temp_inst' data-save='yes'><td><select class='temp_day'>";
		foreach($config->day as $cd)
			echo "<option value={$cd['id']}>{$cd['name']}</option>";
		echo "</select></td><td><select class='temp_shift'>";
		foreach($shift->par as $s)
			echo "<option value={$s->shift_id}>{$s->name}</option>";
		echo "</select></td><td><select class='temp_pos'><option value='0' selected></option>";
		foreach($pos->ord as $o){
			$p = $pos->pos[$o['pos_id']];
			echo "<option value={$p->pos_id}>{$p->name}</option>";
		}
		echo "</select></td><td><select class='temp_pref'><option value='0' selected></option>";
		foreach($config->pref as $p)
			echo "<option value={$p['id']}>{$p['name']}</option>";
		echo "</select></td>
		<td class='table_button remove'>X</td></tr>";
		echo "</table>";
	?>
	</div>
	
	<h3>Other</h3>
	<div>
		<button onclick=save_other();>Save</button><br/>
		Arrival Buffer: <input id='arr_buffer' type='number' value='<?php echo $config->xml->arr_buffer;?>'><br/>
		Departure Buffer: <input id='dep_buffer' type='number' value='<?php echo $config->xml->dep_buffer;?>'>
	</div>
</div>


</body>

</html>
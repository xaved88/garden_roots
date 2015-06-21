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
	div#staff_and_type_selection{float:left; padding: 3px 10px; border:solid 1px black;}
	table#multi_staff_action_table{float:left;}
	table#multi_staff_action_table, table#multi_staff_action_table tr, table#multi_staff_action_table th, table#multi_staff_action_table td{border: solid 1px black;}
	
	tr.tr_template{display:none};
</style>
<script>

function staff_selector_selected(staff_id){
	staff_selector_fetch(staff_id,"name-only");
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
			if($("div.staff_list div.staff_member[data-staff_id='" + staff_id + "']").size() < 1 ){
				staff_member_div = "<div class='staff_member' data-staff_id='" + staff_id + "'><button onclick=remove_member(this);>X</button> " + data + "</div>"
				$("div.staff_list").append(staff_member_div);
				
			}
		}
	});
}

function save(){
	// get staff type if there is
	staff_type = $('select#select_type').val();
	// get staff list
	staff = new Array();
	$('div.staff_member').each(function(){
		staff.push($(this).attr('data-staff_id'));
	});
	// all actions
	action = new Array();
	$('tr.multi_staff_action_instance').each(function(){
		details = $(this).find('td.details');
		data = {};
		data.type = $(this).find('td.type select').val();
		data.action = $(this).find('td.action select').val();
		data.name = $(this).find('td.name select').val();
		data.details = {}
		data.details.min = details.find('input.' + data.type + '_min').val();
		data.details.max = details.find('input.' + data.type + '_max').val();
		data.details.pref = details.find('input.' + data.type + '_pref').val();
		action.push(data);
	});
	
	$.ajax({
		type:'POST',
		url:'../actions/a_staff_multi_mod.php',
		data:{
			staff_type: staff_type,
			staff: staff,
			action: action
		},
		success: function(data){
			$('div#action_response').html(data);
		}
	});
}

function add_table_action(){
	type = $("tr.tr_template td.type").html();
	remove = $("tr.tr_template td.remove").html();
	tr = "<tr class='multi_staff_action_instance'><td class='type'>" + type + "<td class='action'></td><td class='name'></td><td class='details'></td><td class='remove'>" + remove + "</td></tr>";
	
	$('table#multi_staff_action_table').append(tr);
	
	listeners();
}
function remove_table_action(obj){
	$(obj).closest('tr').remove();
	listeners();
}
function remove_member(button){
	$(button).parent().remove();
}

function select_type_change(select){
	tr = $(select).closest('tr');
	value = $(select).val();
	if(value == ''){
		remove_table_action(select);
		return;
	}
	template = $('table#multi_staff_action_table tr.tr_template');
	tr.find('td.action').html(template.find('td.'+value+'_action').html());
	tr.find('td.name').html(template.find('td.'+value+'_name').html());
	tr.find('td.details').html(template.find('td.'+value+'_details').html());
}
function listeners(){
	$('select').off();
	$('td.type select').change(function(){ select_type_change(this); });
}
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
include('../includes/staff_selector.php');
?>

<div id='action_response'></div>
<div id='staff_and_type_selection'>
	Staff Type: <select id='select_type'><option value='0'></option>
	<?php
		require_once('../includes/staff_classes.php');
		$config = new conFig();
		$type = $config->staff_type_array();
		
		foreach($type as $t){
			echo "<option value='{$t['id']}'>{$t['name']}</option>";
		}
	?>
	</select>
	<div class='staff_list'>
	Individual Members:<br/>
	</div>
</div>

<table id='multi_staff_action_table'>
	<thead>
		<tr><th>Type:</th><th>Action</th><th>Name</th><th>Details</th><th>Remove</th></tr>
	</thead>
	<tbody>
		<tr class='tr_template'>
			<td class='type'>
				<select>
					<option value=''></option>
					<option value='pos'>Position</option>
					<option value='lang'>Language</option>
					<option value='contract'>Contract</option>
				</select>
			</td>
			<td class='pos_action'>
				<select>
					<option value=''></option>
					<option value='add'>Add</option>
					<option value='mod'>Modify</option>
					<option value='del'>Delete</option>
				</select>
			</td>
			<td class='lang_action'>
				<select>
					<option value=''></option>
					<option value='add'>Add</option>
					<option value='del'>Delete</option>
				</select>
			</td>
			<td class='contract_action'></td>
			<td class='pos_name'>
				<select>
					<option value=''></option>
					<?php
						require_once('../includes/staff_classes.php');
						$pos = new dPosLib($con);
						foreach($pos->ord as $o){
							$p = $pos->pos[$o['pos_id']];
							echo "<option value='{$p->pos_id}'>{$p->name}</option>";
						}
					?>
				</select>
			</td>
			<td class='lang_name'>
				<select>
					<option value=''></option>
					<?php
						require_once('../includes/staff_classes.php');
						$lang = new dLangLib($con);
						foreach($lang->ord as $o){
							$l = $lang->lang[$o['lang_id']];
							echo "<option value='{$l->lang_id}'>{$l->name}</option>";
						}
					?>
				</select>
			</td>
			<td class='contract_name'></td>
			<td class='pos_details'>
				Min:<input class='pos_min' type='number'> 
				Max:<input class='pos_max'  type='number'> 
				Pref:<input class='pos_pref'  type='number'> 
			</td>
			<td class='lang_details'></td>
			<td class='contract_details'>
				Min:<input class='contract_min' type='number'> 
				Max:<input class='contract_max'  type='number'> 
				Pref:<input class='contract_pref'  type='number'> 
			</td>
			<td class='remove'>
				<button onclick=remove_table_action(this);>X</button>
			</td>
		</tr>
	</tbody>
</table>
<button onclick=add_table_action();>Add Action</button>
<button onclick=save();>Save it!</button>

<!--
	1) Add the employee type selection (simple select)
	2) Table for actions: TR = new action, TD = definition of it, so:
	
	<tr><td>Type(pos/lang for now)</td><td>Action(add/mod/delete)</td><td>DB Select(pos_name, lang_name, etc)</td><td>Details</td></tr>
	
	3) Make the relative action page to deal with it.
-->
<!--
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
-->
</body>

</html>
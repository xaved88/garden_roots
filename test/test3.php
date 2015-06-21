<html>
<head>
<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/calendar.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/calendar.css" rel="stylesheet">

<style>
	td{height:50px; width:50px; border: 1px solid black;}
	
	button.pressed{border-type:inset; background-color:aliceblue;}
	
	div#mp_shifts, div#mp_days, div#mp_css{display:none; padding 3px 8px; border: 1px solid black;}
	
	.invis{display:none;}
	
	div.td_button{border: 5px outset silver; height:22px; width: 38px; padding:8px 0px; text-align:center; overflow:hidden; float:left;}
	div.td_button.pressed{border: 5px inset yellow;}

	/*div#cell_management{display:inline-block;}*/
	div#cell_management table#cell_seen{float:left;}
	div#cell_table_buttons{float:left; border:1px solid black; padding:10px; margin: 0px 100px 0px 50px;}
	
</style>
<script>

function test(){
	$( ".drag_box:not(.button)" ).draggable(/*{
		snap: "td", 
		snapMode: "outer"
	}*/);
}

function save(){
	action = 'save';
	
	data = {};
	data.name = $("input#template_name").val();
	
	{//TABLE
	table = {};
	table.layout = $("select#mp_layout_select").val();
	table.css = $("div#mp_layout_options input#table_css").val();
	table.inline_css = $("div#mp_layout_options textarea#inline_css").val();
	table.date_format = $("div#mp_layout_options input#date_format").val();
	table.shift_format = $("div#mp_layout_options select#shift_format").val();
	table.shifts = new Array();
	table.days = new Array();
	table.cells = new Array();
	$("div#mp_shifts div.shift_checkbox input[type=checkbox]:checked").each(function(){
		table.shifts.push($(this).val());
	});
	$("div#mp_days div.day_checkbox input[type=checkbox]:checked").each(function(){
		table.days.push($(this).val());
	});
	$("table#mp_template_table td").each(function(){
		x = {};
		x.shift = $(this).attr('data-shift');
		x.day = $(this).attr('data-day');
		x.id = $(this).attr('data-id');
		table.cells.push(x);
	});
	data.table = table;
	}
	{//CELLS
	cells = new Array();
	$("div#cell_define div.cell_inst").each(function(){
		cell = {};
		cell.name = $(this).find('span.name').html();
		cell.color = $(this).find('span.color').html();
		cell.css = $(this).find('span.css').html();
		cell.table = new Array();
		$(this).find('table tr').each(function(){
			row = new Array();
			 $(this).find('td').each(function(){
				td = {};
				td.colspan = $(this).attr('colspan');
				if(!td.colspan) td.colspan = 1;
				td.rowspan = $(this).attr('rowspan');
				if(!td.rowspan) td.rowspan = 1;
				td.content = new Array();
				$(this).find('div.cell_info_inst').each(function(){
					td.content.push($(this).attr('data-id'));
				});
				row.push(td);
			});
			cell.table.push(row);
		});
		cells.push(cell);
	});
	data.cells = cells;
	}
	{//INFO
	info = new Array();
	$("div#info_define div.info_inst").each(function(){
		inst = {};
		inst.name = $(this).find('span.name').html();
		inst.color = $(this).find('span.color').html();
		inst.pos_id = $(this).find('span.pos_id').html();
		inst.name_disp = $(this).find('span.name_disp').html();
		inst.name_delim = $(this).find('span.name_delim').html();
		inst.pos_disp = $(this).find('span.pos_disp').html();
		inst.pos_loc = $(this).find('span.pos_loc').html();
		inst.pos_delim = $(this).find('span.pos_delim').html();
		inst.empty_hide = $(this).find('span.empty_hide').html();
		inst.pos_css = $(this).find('span.pos_css').html();
		inst.name_css = $(this).find('span.name_css').html();
		inst.div_css = $(this).find('span.div_css').html();
		inst.empty_css = $(this).find('span.empty_css').html();
		info.push(inst);
	});
	data.info = info;
	}
	
	// SAVING THROUGH PHP TO XML FILE
	$.ajax({
		type: 'POST',
		url: '../actions/a_rota_templates.php',
		data:{
			action: action,
			data: data
		},
		success:function(data){
			$('#action_response').html(data);
		},
		error:function(data){
			$('#action_response').html(data);
		}
	});
}

function load(){
	name = $('select#load_template').val();
	action = 'load';
	$.ajax({
		type: 'POST',
		url: '../actions/a_rota_templates.php',
		dataType:'json',
		data:{
			action: action,
			name: name
		},
		success:function(data){
			$('#action_response').html(data['message']);
			// CLEAR EVERYTHING
			// CLEAR INFO
			$('select#info_select option[value!=0]').remove();
			$('div.info_inst').remove();
			$("div.info_button[data-id!='none']").remove();
			// CLEAR CELL
			$('select#cell_select option[value!=0]').remove();
			$('div.cell_inst').remove();
			$("div.cell_button[data-id!='none']").remove();
			// CLEAR TABLE
			$("div#mp_shifts div.shift_checkbox input").prop('checked',false);
			$("div#mp_days div.day_checkbox input").prop('checked',false);
			
			// INFO DATA
			$(data['info']).each(function(){
				html = "<div class='info_inst invis' data-name='" + this['name'] + "'>";
				html += "<span class='name'>" + this['name'] + "</span>";
				html += "<span class='color'>" + this['color']+ "</span>";
				html += "<span class='pos_id'>" + this['pos_id'] + "</span>";
				html += "<span class='name_disp'>" + this['name_disp'] + "</span>";
				html += "<span class='name_delim'>" + this['name_delim'] + "</span>";
				html += "<span class='pos_disp'>" + this['pos_disp'] + "</span>";
				html += "<span class='pos_loc'>" + this['pos_loc'] + "</span>";
				html += "<span class='pos_delim'>" + this['pos_delim'] + "</span>";
				html += "<span class='empty_hide'>" + this['empty_hide'] + "</span>";
				html += "<span class='pos_css'>" + this['pos_css'] + "</span>";
				html += "<span class='name_css'>" + this['name_css'] + "</span>";
				html += "<span class='div_css'>" + this['div_css'] + "</span>";
				html += "<span class='empty_css'>" + this['empty_css'] + "</span>";
				html += "</div>";
				$('div#info_seen').after(html);
				$('select#info_select').append("<option style='background-color:"+this['color']+";' value='" + this['name'] + "'>" + this['name'] + "</option>");
				html = "<div class='info_button td_button' style='background-color:"+ this['color'] +";' data-id='"+ this['name'] +"'>"+ this['name'] +"</div>";
				$('div#cell_table_buttons').append(html);
			});
			info_select();
			
			// CELL DATA
			define = $('div#cell_define');
			$(data['cells']).each(function(){	
				html = "<div class='cell_inst invis' data-name='"+this['name']+"'>";
				html += "<span class='name'>" + this['name'] + "</span>";
				html += "<span class='color'>" + this['color'] + "</span>";
				html += "<span class='css'>" + this['css'] + "</span>";
				html += "<table><thead></thead><tbody>";
				$(this['table']).each(function(){
					html += "<tr>";
					$(this).each(function(){
						html += "<td colspan='" + this['colspan'] + "' rowspan='" + this['rowspan'] + "'>";
						$(this['content']).each(function(){
							html += "<div class='cell_info_inst' data-id='" + this + "'>" + this + "</div>";
						});
						html += "</td>";
					});
					html += "</tr>";
				});
				html += "</tbody></table></div>";
				define.append(html);
				$('select#cell_select').append("<option style='background-color:"+this['color']+";' value='" + this['name'] + "'>" + this['name'] + "</option>");
				html = "<div class='cell_button td_button' style='background-color:"+this['color']+";' data-id='"+this['name']+"'>"+this['name']+"</div>";
				$('div#table_controls').append(html);
			});
			select_cell();
				
			// TABLE DATA
			$('select#mp_layout_select').val(data['table']['layout']);
			$('input#table_css').val(data['table']['css']);
			$('textarea#inline_css').val(data['table']['inline_css']);
			$('input#date_format').val(data['table']['date_format']);
			$('select#shift_format').val(data['table']['shift_format']);
			$(data['table']['shifts']).each(function(){
				$("div#mp_shifts div.shift_checkbox input[value='"+this+"']").prop('checked',true);
			});
			$(data['table']['days']).each(function(){
				$("div#mp_days div.day_checkbox input[value='"+this+"']").prop('checked',true);
			});
			generate_table();
			$(data['table']['cells']).each(function(){
				td = $("table#mp_template_table td[data-day='"+this['day']+"'][data-shift='"+this['shift']+"']");
				td.attr('data-id', this['id']);
				td.html(this['id']);
			});
			// FINAL
			$('input#template_name').val(data['name']);
			refresh_colors();
			listener();
			
		},
		error:function(data){
			$('#action_response').html('Error loading file: AJAX call failed. Logging to console.<br/>');
			$('#action_response').append(data.responseText);
			console.log(data);
		}
	});
}

// MAIN TABLE FUNCTIONS
function generate_table(){
	layout = $("select#mp_layout_select").val();
	if(layout == ''){
		alert("Please select a layout type.");
		return;
	}
	else if(layout = 'shift'){
		col_type = 'shift';
		row_type = 'day';
	}
	else if(layout = 'day'){
		col_type = 'day';
		row_type = 'shift';
	}
	
	col = new Array();
	row = new Array();
	
	$("div#mp_shifts div.shift_checkbox").each(function(){
		if($(this).find('input[type=checkbox]').is(':checked')){
			data = {}
			data.name = $(this).find('span').html();
			data.abbr = $(this).find('span').attr('data-abbr');
			data.id = $(this).find('input').val();
			if(layout == 'shift')
				col.push(data);
			else if(layout == 'day')
				row.push(data);
		}
	});
	
	$("div#mp_days div.day_checkbox").each(function(){
		if($(this).find('input[type=checkbox]').is(':checked')){
			data = {}
			data.name = $(this).find('span').html();
			data.abbr = $(this).find('span').attr('data-abbr');
			data.id = $(this).find('input').val();
			if(layout == 'day')
				col.push(data);
			else if(layout == 'shift')
				row.push(data);
		}
	});
	
	table = $('table#mp_template_table');
	thead = table.find('thead');
	tbody = table.find('tbody');
	html = "<tr><th></th>";
	$(col).each(function(){
		html += "<th data-" + col_type + "='" + this.id + "'>" + this.abbr + "</th>";
	});
	html += "</tr>";
	thead.html(html);
	html = "";
	$(row).each(function(){
		row_id = this.id;
		html += "<tr><th data-" + row_type + "='" + row_id + "'>" + this.abbr + "</th>";
		$(col).each(function(){
			// 	<div class='cell_button td_button' style='background-color:lightgray;' data-id='default'>default</div>
			html += "<td style='background-color:lightgray' data-" + row_type + "='" + row_id + "' data-" + col_type + "='" + this.id + "' data-id='default'>default</td>";
		});
		html += "</tr>";
	});
	tbody.html(html);
	$("div#table_controls").removeClass('invis');
}
function toggle_layout(selector){
	obj = $("div#mp_" + selector);
	if(obj.is(':visible'))
		obj.hide();
	else
		obj.show();
}
function place_cell(obj, cell){
	//obj = the td where it's going
	//cell = the button/cell that it is applying to that td
	if(!cell){
		cell = $('div.cell_button.pressed').get(0);
	}
	if(!cell){
		return;
	}
	
	$(obj).attr("data-id",$(cell).attr("data-id"));
	$(obj).html($(cell).html());
	$(obj).css("background-color", $(cell).css("background-color"));
	
	if(!window.event.shiftKey){
		toggle_button(cell);
	}
}

// CELL FUNCTIONS
function generate_cell_table(){
	//temp = $('div#cell_define input#cell_name').val();
	tbody = $('table#cell_seen tbody');
	row_count = $('input#cell_row').val();
	col_count = $('input#cell_col').val();
	
	html = '';
	for(i=0; i<row_count; i++){
		html += "<tr>";
		for(j=0; j<col_count; j++)
			html += "<td style='background-color:white;'></td>";
		html += "</tr>";
	}
	
	tbody.html(html);
	
	listener();
}
function select_cell(){
	name= $('select#cell_select').val();
	define = $('div#cell_define');
	temp = $("div.cell_inst[data-name='"+name+"']");
	if(name != 0){
		name = temp.find('span.name').html();
		color = temp.find('span.color').html();
		css = temp.find('span.css').html();
		table = temp.find('table').html();
	}
	else{
		row = 0;
		col = 0;
		color = 'none';
		name = '';
		css = '';
		table = '<thead></thead><tbody></tbody>';
	}
	
	define.find('input#cell_name').val(name);
	define.find('select#cell_color').val(color);
	define.find('input#cell_css').val(css);
	define.find('table#cell_seen').html(table);
	
	select_match_selected();
	listener();
}
function save_cell(){
	// GET CURRENT INFO
	define = $('div#cell_define');
	seen = $('table#cell_seen');
	name = define.find('input#cell_name').val();
	color = define.find('select#cell_color').val();
	
	if(name == '' || name == 'none'){
		alert("Error: Must chose a valid name");
		return;
	}
	
	// GENERATE THE NEW
	html = "<span class='name'>" + name + "</span>";
	html += "<span class='color'>" + $('select#cell_color').val() + "</span>";
	html += "<span class='css'>" + define.find('input#cell_css').val() + "</span>";
	html += "<table>";
	html += seen.html();
	html += "</table>";
	
	// PUT IT IN
	if(define.find("div.cell_inst[data-name='"+name+"']").get(0)){
		define.find("div.cell_inst[data-name='"+name+"']").html(html);
		$("select#cell_select option[value='" + name + "']").css('background-color',color);
		$("div.cell_button[data-id='"+name+"']").css('background-color',color);
		$("table#mp_template_table td[data-id='"+name+"']").each(function(){
			$(this).attr("data-id",name);
			$(this).html(name);
			$(this).css("background-color", color);
		});
		
	}
	else{
		html = "<div class='cell_inst invis' data-name='" + name + "'>" + html + "</div>";
		define.append(html);
		$('select#cell_select').append("<option style='background-color:"+color+";' value='" + name + "'>" + name + "</option>");
		
		html = "<div class='cell_button td_button' style='background-color:"+color+";' data-id='"+name+"'>"+name+"</div>";
		$('div#table_controls').append(html);
	}
	
	listener();
	$('select#cell_select').val(name);
	select_cell();

}
function remove_cell(){
	define = $('div#cell_define');
	name = $('select#cell_select').val();
	if(name != '0' && name != 'none' && name != 'default'){
		define.find("div.cell_inst[data-name='"+name+"']").remove();
		$("div#table_controls div.cell_button[data-id='"+name+"']").remove();
		$("table#mp_template_table td[data-id='"+name+"']").each(function(){
			$(this).attr("data-id",'none');
			$(this).html('none');
			$(this).css("background-color", 'white');
		});
		$("select#cell_select option[value='"+name+"']").remove();
		listener();
		select_cell();
	}
	else
		alert("Error: Cannot delete this instance - please select a valid one.");
}
function table_cell_manage(obj){
	// CHECK THE TABLE ACTION BUTTONS
	pressed = $('div#cell_table_buttons .pressed').get(0);
	if($(pressed).is('button')){
		action = $(pressed).attr('data-action');
		
		if(action == 'remove'){
			$(obj).remove();
			return;
		}
		else if(action == 'add'){
			$(obj).after("<td></td>");
			listener();
			return;
		}
		if(!$(obj).attr('colspan'))
			colspan = 1;
		else colspan = parseInt($(obj).attr('colspan'));
		if(!$(obj).attr('rowspan'))
			rowspan = 1;
		else rowspan = parseInt($(obj).attr('rowspan'));
		
		if(action == 'col+'){
			colspan++;
		}
		else if(action == 'col-'){
			if(colspan > 1)
			colspan --;
		}
		$(obj).attr('colspan',colspan);
		$(obj).attr('colspan',colspan);
		if(action == 'row+'){
			rowspan++;
		}
		else if(action == 'row-'){
			if(rowspan > 1)
			rowspan --;
		}
		$(obj).attr('rowspan',rowspan);
		$(obj).attr('rowspan',rowspan);
	}
	else if($(pressed).is('div.info_button')){
	
		if($(pressed).attr('data-id')!='none'){
			html = "<div class='cell_info_inst' style='background-color:"+$(pressed).css('background-color')+";' data-id='"+$(pressed).attr("data-id")+"'>"+$(pressed).html()+"</div>";
			
			/*
			$(obj).attr("data-id",$(pressed).attr("data-id"));
			$(obj).html($(pressed).html());
			$(obj).css("background-color", $(pressed).css("background-color"));
			*/
			$(obj).find("div.cell_info_inst[data-id='"+$(pressed).attr('data-id')+"']").remove();
			$(obj).append(html);
			$(obj).css("background-color", $(pressed).css("background-color"));
			
			if(!window.event.shiftKey){
				toggle_button(pressed);
			}
		}
		else{
			$(obj).find("div.cell_info_inst").remove();
			$(obj).css("background-color", $(pressed).css("background-color"));
		}
	}
}

// INFO FUNCTIONS
function info_select(){
	name = $('select#info_select').val();
	seen = $('div#info_seen');
	if(name != '0'){
		inst = $("div.info_inst[data-name='"+name+"']");
		$('input#info_name_input').val(inst.find('span.name').html());
		$('select#info_color').val(inst.find('span.color').html());
		seen.find('select.pos_id').val(inst.find('span.pos_id').html());
		seen.find('select.name_disp').val(inst.find('span.name_disp').html());
		if(inst.find('span.name_delim').html() == 'yes')
			seen.find('input.name_delim').prop('checked',true);
		else
			seen.find('input.name_delim').prop('checked',false);
		seen.find('select.pos_disp').val(inst.find('span.pos_disp').html());
		if(inst.find('span.pos_loc').html() == 'before'){
			seen.find('input.pos_loc[value=before]').prop('checked',true);
			seen.find('input.pos_loc[value=after]').prop('checked',false);
		}
		else{
			seen.find('input.pos_loc[value=after]').prop('checked',true);
			seen.find('input.pos_loc[value=before]').prop('checked',false);
		}
		if(inst.find('span.pos_delim').html() == 'yes')
			seen.find('input.pos_delim').prop('checked',true);
		else
			seen.find('input.pos_delim').prop('checked',false);
		if(inst.find('span.empty_hide').html() == 'yes')
			seen.find('input.empty_hide').prop('checked',true);
		else
			seen.find('input.empty_hide').prop('checked',false);
		
		seen.find('textarea.div_css').val(inst.find('span.div_css').html());
		seen.find('textarea.pos_css').val(inst.find('span.pos_css').html());
		seen.find('textarea.name_css').val(inst.find('span.name_css').html());
		seen.find('textarea.empty_css').val(inst.find('span.empty_css').html());
		select_match_selected();
	}
	else{
		$('input#info_name_input').val('new');
		$('select#info_color').val('none');
		seen.find('select.pos_id').val('1');
		seen.find('select.name_disp').val('full');
		seen.find('select.pos_disp').val('name');
		seen.find('input.name_delim').prop('checked',false);
		seen.find('input.pos_loc[value=before]').prop('checked',true);
		seen.find('input.pos_loc[value=after]').prop('checked',false);
		seen.find('input.pos_delim').prop('checked',false);
		seen.find('textarea.div_css').val('');
		seen.find('textarea.pos_css').val('');
		seen.find('textarea.name_css').val('');
		seen.find('textarea.empty_css').val('');
		select_match_selected();
	}
}
function save_info(){
	// GET CURRENT INFO
	define = $('div#info_define');
	seen = $('div#info_seen');
	name = define.find('input#info_name_input').val();
	
	if(name == '' || name == 'none'){
		alert("error: not a valid name");
		return;
	}
	
	color = $('select#info_color').val();
	// GENERATE NEW DIV
//	html = "<div class='info_inst invis' data-name='" + name + "'>";
	html = "<span class='name'>" + name + "</span>";
	html += "<span class='color'>" + color + "</span>";
	html += "<span class='pos_id'>" + seen.find('select.pos_id').val() + "</span>";
	html += "<span class='name_disp'>" + seen.find('select.name_disp').val() + "</span>";
	html += "<span class='name_delim'>" + seen.find('input.name_delim:checked').val() + "</span>";
	html += "<span class='pos_disp'>" + seen.find('select.pos_disp').val() + "</span>";
	html += "<span class='pos_loc'>" + seen.find('input.pos_loc:checked').val() + "</span>";
	html += "<span class='pos_delim'>" + seen.find('input.pos_delim:checked').val() + "</span>";
	html += "<span class='empty_hide'>" + seen.find('input.empty_hide:checked').val() + "</span>";
	html += "<span class='pos_css'>" + seen.find('textarea.pos_css').val() + "</span>";
	html += "<span class='name_css'>" + seen.find('textarea.name_css').val() + "</span>";
	html += "<span class='div_css'>" + seen.find('textarea.div_css').val() + "</span>";
	html += "<span class='empty_css'>" + seen.find('textarea.empty_css').val() + "</span>";
	
//	html += "</div>";
	
	// PUT IT IN
	if(define.find("div.info_inst[data-name='"+name+"']").get(0)){
		define.find("div.info_inst[data-name='"+name+"']").html(html);
		
		$("select#info_select option[value='" + name + "']").css('background-color',color);
		
		
		$("div.info_button[data-id='"+name+"']").css('background-color',color);
		$("table#cell_seen td[data-id='"+name+"'], div.cell_inst table td[data-id='"+name+"']").each(function(){
			$(this).attr("data-id",name);
			$(this).html(name);
			$(this).css("background-color", color);
		});
	}
	else{
		html = "<div class='info_inst invis' data-name='" + name + "'>" + html + "</div>";
		seen.after(html);
		$('select#info_select').append("<option style='background-color:"+color+";' value='" + name + "'>" + name + "</option>");
		html = "<div class='info_button td_button' style='background-color:"+color+";' data-id='"+name+"'>"+name+"</div>";
		$('div#cell_table_buttons').append(html);
	}
	
	listener();
	$('select#info_select').val(name);
	info_select();
}
function remove_info(){
	name = $('select#info_select').val();
	
	if(name != '0' && name != 'none' && name != ''){
		
		$("div.info_inst[data-name='"+name+"']").remove();
		$("select#info_select option[value='"+name+"']").remove();
	
		$("div#cell_table_buttons div.info_button[data-id='"+name+"']").remove();
		$("table#cell_seen td[data-id='"+name+"'], div.cell_inst table td[data-id='"+name+"']").each(function(){
			$(this).attr("data-id",'none');
			$(this).html('');
			$(this).css("background-color", 'white');
		});
		listener();
		info_select();
	}
	else
		alert("Error: Cannot delete this instance - please select a valid one.");
}

// GENERAL FUNCTIONS
function toggle_button(obj, solo){
	if($(obj).hasClass('pressed'))
		$(obj).removeClass('pressed');
	else{
		if(solo) $(obj).siblings().removeClass('pressed');
		$(obj).addClass('pressed');
	}
}

function refresh_colors(){
	// CELL REFRESH:
	$("div#cell_define td div.cell_info_inst").each(function(){
		id = $(this).attr('data-id');
		if(id == 'none')
			bg = 'white';
		else bg = $("div.info_inst[data-name='" + id + "'] span.color").html();
		$(this).css('background-color',bg);
		$(this).closest('td').css('background-color',bg);
	});
	// TABLE REFRESH
	$("table#mp_template_table td").each(function(){
		id = $(this).attr('data-id');
		if(id == 'none')
			bg = 'white';
		else bg = $("div.cell_inst[data-name='" + id + "'] span.color").html();
		$(this).css('background-color',bg);
	});
}
function select_match_selected(obj){
	if(!obj)
		$('select.match_selected').each(function(){
			select_match_selected(this);
		});
	else
		$(obj).css('background-color',$(obj).find('option:selected').css('background-color'));
}

function listener(){
	$("button,select,div").off();
	
	// MAIN TABLE FUNCTIONS
	$("button.toggleable").click(function(){	toggle_button(this);	});
	$("button.toggleable_solo").click(function(){	toggle_button(this,true);	});
	$("button#mp_shifts_button").click(function(){	toggle_layout("shifts");	});
	$("button#mp_days_button").click(function(){	toggle_layout("days");	});
	$("button#mp_css_button").click(function(){	toggle_layout("css");	});
	$("button#generate_table").click(function(){	generate_table();	});
	$("table#mp_template_table td").click(function(){	place_cell(this);	});
	
	// INFO FUNCTIONS
	$("button#save_info").click(function(){	save_info();	});
	$("button#remove_info").click(function(){	remove_info();	});
	$('select#info_select').change(function(){	info_select();	});
	
	// CELL FUNCTIONS
	$("button#generate_cell_table").click(function(){ generate_cell_table();	});
	$("button#save_cell").click(function(){	save_cell();	});
	$("button#remove_cell").click(function(){	remove_cell();	});
	$("select#cell_select").change(function(){	select_cell();	});
	$("table#cell_seen td").click(function(){	table_cell_manage(this);	});
	
	// GENERAL FUNCTIONS
	$("div.td_button").click(function(){	toggle_button(this, true);	});
	$("select.match_selected").change(function(){	select_match_selected(this);	});
}

$(function() {
	
	$("div#accordion").accordion({
		collapsible:true,
		heightStyle:'content',
		active:false
	});
	listener();
});
</script>
</head>
<body>

<div id='action_response'></div>
Name:<input type='text' id='template_name'><br/>
<button onclick=test();>Test</button>
<button onclick=save();>Save</button>

<select id='load_template'>
<?php
	$file = '../config/rota_templates.xml';
	$xml = new SimpleXMLElement($file, null, true);
	foreach($xml->template as $t){
		foreach($t->attributes() as $a => $b){
			echo "<option value='{$b}'>{$b}</option>";
		}
	}
?>
</select>
<button onclick=load();>Load</button>
	

<div id='accordion'>
	<h3>Table Management</h3>
	<div id='table_management'>
		Layout: <select id='mp_layout_select'>
			<option value=''></option>
			<option value='shift'>Col-Shifts : Row-Days</option>
			<option value='day'>Col-Days : Row-Shifts</option>
		</select>

		<div id='mp_layout_options'>
			<button id='mp_shifts_button' class='toggleable'>Shifts</button>
			<button id='mp_days_button' class='toggleable'>Days</button>
			<button id='mp_css_button' class='toggleable'>Styling</button>
			<div id='mp_shifts'>
			<?php
				require_once('../includes/staff_classes.php');
				
				$shifts = new dShiftLib($con);
				foreach($shifts->ord as $o){
					$s = $shifts->par[$o['shift_id']];
					echo "<div class='shift_checkbox'><input type='checkbox' value='{$s->shift_id}'><span data-abbr='{$s->abbr}'>{$s->name}</span></div>";
				}
			?>
			</div>
			<div id='mp_days'>
			<?php
				require_once('../includes/general_functions.php');
				$config = new conFig();
				foreach($config->day as $d){
					echo "<div class='day_checkbox'><input type='checkbox' value='{$d['id']}'";
					if($d['active']) echo " checked";
					echo "><span data-abbr='{$d['abbr']}'>{$d['name']}</span></div>";
				}	
			?>
			</div>
			<div id='mp_css'>
				Table CSS: <input id='table_css' type='text'>
				Inline Style Sheet: <textarea rows="4" cols="70" id='inline_css'></textarea>
				Date Format: <input id='date_format' type='text' value='D jS'>
				Shift Format: <select id='shift_format'>
					<option value='name'>Full</option>
					<option value='abbr'>Abbreviation</option>
					<option value='alt1'>Alternate Display 1</option>
					<option value='alt2'>Alternate Display 2</option>
				</select>
			</div>
		</div>
		<button id='generate_table'>Generate the Table</button>

		<table id='mp_template_table'>
			<thead>
			</thead>
			<tbody>
			</tbody>
		</table>
		<div id='table_controls' class='invis'>
		<div class='cell_button td_button' style='background-color:white;' data-id='none'>none</div>
		<div class='cell_button td_button' style='background-color:lightgray;' data-id='default'>default</div>
		</div>
	</div>
	<h3>Cell Management</h3>
	<div id='cell_management'>
		<select id='cell_select' class='match_selected'>
			<option value='0'>New</option>
			<option value='default'>default</option>
		</select>
		<div id='cell_define'>
			Cell Name: <input id='cell_name' type='text'> 
			CSS: <input id='cell_css' type='text'><br/>
			Color: <select id='cell_color' class='match_selected'>
			<?php
				require_once('../includes/general_functions.php');
				echo option_color_list();
			?>
			</select>
			Cell Size: Col:<input id='cell_col' type='number' value='0'> Row:<input id='cell_row' type='number' value='0'>
			<button id='generate_cell_table'>Generate Cell</button><br/>
			<button id='save_cell'>Save</button><button id='remove_cell'>Remove</button>
			<table id='cell_seen'>
				<thead></thead>
				<tbody></tbody>
			</table>
			
			<div id='cell_table_buttons'>
			Colspan: <button data-action='col+' class='toggleable_solo'>+</button> <button data-action='col-' class='toggleable_solo'>-</button>
			Rowspan: <button data-action='row+' class='toggleable_solo'>+</button> <button data-action='row-' class='toggleable_solo'>-</button><br/>
			Cell: <button data-action='remove' class='toggleable_solo'>Remove</button>
			<button data-action='add' class='toggleable_solo'>Add</button>
			<br/><br/>
			<div class="info_button td_button" style="background-color: white;" data-id="none"></div>
			</div>
			
			<div class="cell_inst invis" data-name="default"><span class="name">default</span><span class="color">LightGray</span><span class="row">undefined</span><span class="col">undefined</span><table>
				<thead></thead>
				<tbody></tbody>
			</table></div>
		</div>
	</div>
	<h3>Info Management</h3>
	<div id='info_management'>
	<select id='info_select' class='match_selected'>
		<option value='0'>New</option>
	</select>
	<div id='info_define'>
		Info Name: <input id='info_name_input' type='text'><button id='save_info'>Save</button><button id='remove_info'>Remove</button><br/>
		Color: <select id='info_color' class='match_selected'>
			<?php
				require_once('../includes/general_functions.php');
				echo option_color_list();
			?>
		</select>
		<div id='info_seen'>
			Position: <select class='pos_id'>
				<?php
					require_once('../includes/staff_classes.php');
					
					$pos = new dPosLib($con);
					foreach($pos->ord as $o){
						$p = $pos->pos[$o['pos_id']];
						echo "<option value='{$p->pos_id}'>{$p->name}</option>";
					}
				?>
				</select><br/>
			Name Display: <select class='name_disp'>
					<option value='full'>Full Name: Bill Gates</option>
					<option value='first'>First: Bill</option>
					<option value='last'>Last: Gates</option>
					<option value='initials'>Initials: B.G.</option>
					<option value='first+'>First +: Bill G.</option>
					<option value='last+'>Last +: B.Gates</option>
					<option value='none'>None</option>
				</select><br/>
			Newline after Names?: <input type='checkbox' class='name_delim' value='yes'><br/>
			Position Display: <select class='pos_disp'>
					<option value='name'>Full Display</option>
					<option value='abbr'>Abbreviation</option>
					<option value='alt1'>Alt Display 1</option>
					<option value='alt2'>Alt Display 2</option>
					<option value='none'>None</option>
				</select><br/>
			Position Location: <input type='checkbox' class='pos_loc' value='before' checked>Before <input type='checkbox' class='pos_loc' value='after'>After<br/>
			Break between Pos & Names? <input type='checkbox' class='pos_delim' value='yes'><br/>
			Hide if empty? <input type='checkbox' class='empty_hide' value='yes' checked><br/>
			Pos CSS: <textarea class='pos_css' cols='60' rows='3'></textarea><br/>
			Name CSS: <textarea class='name_css' cols='60' rows='3'></textarea><br/>
			Instance CSS: <textarea class='div_css' cols='60' rows='3'></textarea><br/>
			Instance CSS (when empty): <textarea class='empty_css' cols='60' rows='3'></textarea>
		</div>
	</div>
</div>
</div>

</body>
</html>
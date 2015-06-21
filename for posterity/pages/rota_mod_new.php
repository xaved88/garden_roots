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
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<link href="../css/calendar.css" rel="stylesheet">
<link href="../css/rota_style.css" rel="stylesheet">
<link href="../css/new_rota_style.css" rel="stylesheet">


<script>

// ROTA FUNCTIONS - BROAD SCALE
function saveRota(){
	cal = $('table#calendar');
	staff = new Array();
	cal.find('tbody th').each(function(){
		staff.push($(this).attr('data-staff'));
	});
	start_date = cal.attr('data-start_date');
	end_date = cal.attr('data-end_date');
	
	group = cal.attr('data-group_id');
	
	record = new Array();
	cal.find('td div.sched_pos_button').each(function(){
		r = {};
		t = $(this);
		td = t.closest('td');
		r.date = td.attr('data-date');
		r.staff_id = td.attr('data-staff');
		r.shift_id = td.attr('data-shift');
		r.pos_id = t.attr('data-pos_id');
		record.push(r);
	});
	
	$.ajax({
		type:'POST',
		url:'../actions/a_rota_mod_new.php',
		data:{
			staff:staff,
			start_date:start_date,
			end_date:end_date,
			group:group,
			record: record
		},
		success:function(data){
			$("#action_response").html(data);
		},
		error:function(data){
			console.log(data);
			$("#action_response").html("Error. Returned data logged to console.<Br/>");
			$("#action_status").html("Error. Process stopped.");
		}
	});
}
function clearRota(){
	if(confirm("Really wipe the rota clean?")){
		$('table#calendar td').removeAttr('data-scheduled');
	}
	processInfoAll();
}
function autoFixed(){
	$('table#calendar td').each(function(){
		t = $(this);
		if(!t.attr('data-scheduled') || t.attr('data-scheduled') == "" || t.attr('data-scheduled') == "0")
		if(t.attr('data-fixed') && t.attr('data-fixed') != 0)
			t.attr('data-scheduled', t.attr('data-fixed'));
	});
	processInfoAll();
}

// CALENDAR & LOAD FUNCTIONS
function refreshCalendar(group,date){
	$("#action_status").html("Loading Calendar...");
	if(!group)
		group = $("#controls select.group").val();
	if(!date)
		date = $("#controls input.date").val();
	$.ajax({
		type:'POST',
		url:'../disp/d_rota_mod_new.php',
		data:{
			group:group,
			date:date,
			action: "calendar"
		},
		success:function(data){
			$("#content").html(data);
			$("#action_status").html("Fetching Info. Calendar Loaded Successfully.");
			prepCalendar();
			fetchInitialInfo();
		},
		error:function(data){
			console.log(data);
			$("#action_response").html("Error. Returned data logged to console.<Br/>");
			$("#action_status").html("Error. Process stopped.");
		}
	});
}
function prepCalendar(){
	//get last shift:
	last_shift = $('table#calendar thead th').last().attr('data-shift');
	$('table#calendar th[data-shift='+last_shift+'],table#calendar td[data-shift='+last_shift+'], table#calendar tbody th, table#calendar thead th:not([data-shift])').addClass('strong-right-border');
	
	//put in the th alerts
	$('table#calendar thead th[data-shift]').append("<div class='shift_alerts'></div>");
}
function fetchInitialInfo(){

	staff = new Array();
	$('table.new_rota_table th[data-staff]').each(function(){
		if($(this).attr('data-staff'))
			staff.push($(this).attr('data-staff'));
	});
	
	date = $("#controls input.date").val();
		group = $("#controls select.group").val();
	
	$.ajax({
		dataType:'json',
		type:'POST',
		url:'../disp/d_rota_mod_new.php',
		data:{
			staff:  staff,
			date: date,
			group: group,
			action:"fetchInfo"
		},
		success:function(data){
			$('#action_response').html(data['message']);
			$('#action_status').html("Applying Info. Info Fetched Successfully.");
			applyInfo(data);
		},
		error:function(data){
			console.log(data);
			$('#action_response').html('Error loading file: AJAX call failed. Logging to console.<br/>');
			$('#action_response').append(data.responseText);
			$("#action_status").html("Error. Process stopped.");		
		}
		
	});
}
function applyInfo(data){

	info_home = $('#hidden_info');
	/* DATA FORMATS ON RETURN
	contracts: [i][staff_id|min|max|pref]
	positions: [i][staff_id|pos_id|pref|min|max]
	availability: [staff_id][date][shift_id][pref|fixed|pos_id]
	scheduled: [i][staff_id|date|pos_id|shift_id]
	needs: [date][shift_id|pos_id|number]
	*/
	
	// FORMAT NAMES WITH THE CORRECT SPAN ACCROSS THEM
	$('table#calendar tbody th').each(function(){
		html = "<div class='staff_name'>" + $(this).html() + "</div>";
		$(this).html(html);
		
	});
	
	// APPLY RAW POSITION DATA
	raw_position_code = "<div class='raw_position'></div>";
	$(data['raw_pos']).each(function(){
		info_home.prepend(raw_position_code);
		rpc = $(info_home).find('.raw_position:first-of-type');
		rpc.attr('data-pos_id',this['pos_id']);
		rpc.attr('data-name',this['name']);
		rpc.attr('data-abbr',this['abbr']);
		rpc.attr('data-group',this['group']);
	});
	
	// APPLY CONTRACT DATA
	staff_contract_code = "<span class='staff_contract'></span>";
	$(data['contracts']).each(function(){
		staff = $("th[data-staff="+this['staff_id']+"]");
		staff.prepend(staff_contract_code);
		scc = $(staff).find('.staff_contract');
		scc.attr('data-staff_id',this['staff_id']);
		scc.attr('data-pref',this['pref']);
		scc.attr('data-min',this['min']);
		scc.attr('data-max',this['max']);
	});
	
	// APPLY STAFF POSITION DATA
	staff_position_code = "<div class='staff_position'></div>";
	$(data['positions']).each(function(){
		group = info_home.find('.raw_position[data-pos_id='+this['pos_id']+']').attr('data-group');
		staff = $("th[data-staff="+this['staff_id']+"]");
		if(group == 'true'){
			staff.prepend(staff_position_code);
			spc = $(staff).find('.staff_position:first-of-type');
		}
		else if(group == 'false'){
			staff.append(staff_position_code);
			spc = $(staff).find('.staff_position:last-of-type');
		}
		spc.attr('data-staff_id',this['staff_id']);
		spc.attr('data-pos_id',this['pos_id']);
		spc.attr('data-pref',this['pref']);
		spc.attr('data-min',this['min']);
		spc.attr('data-max',this['max']);
		spc.attr('data-group',group);
	});
	
	// Add Staff TH header things (lights,smileys,counter)
	shiftCounterCode = "<div class='staff_shift_counter'></div>";
	alertsCode = "<div class='staff_alerts'></div>";
	contentCode = "<div class='staff_contract_perfect'>&#9786;</div>";
	$('table#calendar tbody th').each(function(){
		$(this).prepend(shiftCounterCode + alertsCode + contentCode);
	});
	
	// APPLY AVAILABILITY DATA: 	availability: [staff_id][date][shift_id][pref|fixed|pos_id]
	$("table#calendar td").each(function(){
		date = $(this).attr('data-date');
		shift = $(this).attr('data-shift');
		staff = $(this).attr('data-staff');
		
		//d = data['availability'][staff][date][shift];
		d= data['availability'];
		if(d){ d = d[staff]; } else { d = false;}
		if(d){ d = d[date]; } else { d = false;}
		if(d){ d = d[shift]; } else { d = false;}
		if(d){
			$(this).attr('data-pref',d['pref']);
			if(d['fixed'] && d['fixed'] != '0')
				$(this).attr('data-fixed',d['pos_id']);
			else
				$(this).attr('data-fixed','0');
			$(this).attr('data-pos_id',d['pos_id']);
		}
		else{
			$(this).attr('data-pref',0);
			$(this).attr('data-fixed',0);
			$(this).attr('data-pos_id',0);		
		}
	});

	// APPLY SCHEDULED DATA:	scheduled: [i][staff_id|date|pos_id|shift_id]
	$(data['scheduled']).each(function(){
		td = $("td[data-staff="+this['staff_id']+"][data-date="+this['date']+"][data-shift="+this['shift_id']+"]");
		if(x = td.attr('data-scheduled')){
			td.attr('data-scheduled',x + ',' + this['pos_id']);
		}
		else{
			td.attr('data-scheduled',this['pos_id']);
		}
	});

	// APPLY NEEDS DATA: [date][shift_id|pos_id|number]
	needs_position_code = "<div class='needs_position'></div>";
	$.each(data['needs'],function(i,x){
		nDate = i;
		$(x).each(function(){
			shift = $("th[data-date='"+nDate+"'][data-shift='"+this['shift_id']);
			shift.prepend(needs_position_code);
			spc = $(shift).find('.needs_position:first-of-type');
			spc.attr('data-pos_id',this['pos_id']);
			spc.attr('data-number',this['number']);
		});
	});
	
	$('#action_status').html("Now putting it all together. Applied Info Successfully.");
	processInfoAll();
}
function processInfoAll(){
	// INITIAL TD LOAD
	$("table#calendar tbody td").each(function(){
		updateCell(this);
	});
	// ROW CHECKS (update numbers & styles)
	$("table#calendar tbody tr").each(function(){
		updateRowCounts(this);
		updateRowStyle(this);
	});
	// COLUMN CHECKS (update numbers & styles)
	$("table#calendar thead div.needs_position").each(function(){
		updateColCounts(this);
	});
	$("table#calendar thead th[data-shift]").each(function(){
		updateColStyle(this);
	});
	// LAST TD LOAD
	$("table#calendar td").each(function(){
		updateCellStyle(this);
		updateTdTitles(this);
	});
	
	// LISTENERS & UPDATE MESSAGE
	$("#action_status").html("Finished. You may edit now.");
	scrollMan();
	listeners();
}

// CHECKS & UPDATE FUNCTIONS
function updateCell(obj){
	info_home = $('#hidden_info');
	o = $(obj);
	o.html("<div class='fixed_pos'>*</div>");
	tag_start = "<div class='sched_pos_button' data-pos_id='";
	tag_end = "</div>";
	if(s = o.attr('data-scheduled')){
		s = s.split(',');
		o.html();
		$(s).each(function(){
			abbr = info_home.find(".raw_position[data-pos_id='" + this + "']").attr('data-abbr');
			o.append(tag_start + this + "'>" + abbr + tag_end);
		});
	}
}
function updateRowCounts(obj){// obj = tr
	tr = $(obj);
	th = tr.find('th');
	th.find('div.staff_position').each(function(){
		count = tr.find("div.sched_pos_button[data-pos_id='" + $(this).attr('data-pos_id') + "']").length;
		$(this).attr('data-count', count);
	});
	total_count = 0;
	tr.find('td').each(function(){
		if($(this).attr('data-scheduled'))
			total_count ++;
	});
	th.find('span.staff_contract').attr('data-count',total_count);
	th.find('div.staff_shift_counter').html(total_count);
}
function updateAllColCounts(){
	/*
	Dear future self,
	
	I don't know why we are needing to use this. For some reason, the original setup of only searching for the individual is not working. I THINK it may be the trying to make a jquery object into another jquery object, and for some reason it is switching over to the related row th (it was doing that with the updateColStyle() function til I added the shift,date method.
	
	Probably this has to do with the thead selector being a bit weird? Anyways, I think you could fix it by adding in the shift/date arguments into the update ColCounts function, but I'm too lazy to do it right now. Performance is fine at the moment, but if it drops down some, this would be a good place to look. Updating 125 position each change instead of the necessary shift (8 normally?)	
	
	*/
	$("table#calendar thead div.needs_position").each(function(){
		updateColCounts(this);
	});
}
function updateColCounts(obj){// obj = div.needs_position in the th's
	//alert("here");
	o = $(obj);
	th = o.closest('th');
	tbody = th.closest('table').find('tbody');
	pos = o.attr('data-pos_id');
	shift = th.attr('data-shift');
	date = th.attr('data-date');
	
	count = tbody.find("td[data-shift='"+shift+"'][data-date='"+date+"'] div.sched_pos_button[data-pos_id='"+pos+"']").length;
	o.attr('data-count',count);
}
function updateTdTitles(obj){ // td;
	info_home = $('#hidden_info');
	o = $(obj);
	
	s = o.find('div.sched_pos_button');
	if( s.length > 0){
		s.each(function(){
			th = $(this).closest('tr').find('th');
			pos_id_match = "[data-pos_id='" + $(this).attr('data-pos_id') + "']";
			div_pos = th.find('div.staff_position' + pos_id_match);
			div_shift = $("thead th[data-date='"+o.attr('data-date')+"'][data-shift='"+o.attr('data-shift')+"'] div.needs_position[data-pos_id='"+$(this).attr('data-pos_id')+"']");
			
			// Position Name
			pos_name = info_home.find(".raw_position"+pos_id_match).attr('data-name');
			
			// Personal: X: Min/Pref/Max
			personal = "Personal: ";
			personal += div_pos.attr('data-count') + " : " + div_pos.attr('data-min') + "/" + div_pos.attr('data-pref') + "/" + div_pos.attr('data-max');
			// Shift: X/Need
			shift = "Shift: ";
			shift += div_shift.attr('data-count') + " Need: " + div_shift.attr('data-number');
			
			title = pos_name + "\n" + personal + "\n" + shift;
			$(this).prop('title',title);
		});
	}
}

function updateAllStyle(obj){// obj = td
	td = $(obj);
	tr = td.closest('tr');
	th = $('table#calendar thead th[data-shift='+td.attr('data-shift')+'][data-date='+td.attr('data-date')+']');
	updateRowStyle(tr);
	updateColStyle(false,td.attr('data-shift'),td.attr('data-date'));
	updateCellStyle(td);
}
function updateRowStyle(obj){// obj = tr
	tr = $(obj);
	th = tr.find('th');
	counter = th.find('div.staff_shift_counter');
	alerts = th.find('div.staff_alerts');
	
	// Total Count
	staff_contract = th.find('span.staff_contract');
	total_count = parseInt(staff_contract.attr('data-count'));
	total_min = parseInt(staff_contract.attr('data-min'));
	total_max = parseInt(staff_contract.attr('data-max'));
	total_pref = parseInt(staff_contract.attr('data-pref'));
	
	th.removeClass('rq-contract-over rq-contract-under rq-contract-perfect');
	
	if(total_count > total_max)
		th.addClass('rq-contract-over');
	if(total_count < total_min)
		th.addClass('rq-contract-under');
	if(total_count == total_pref)
		th.addClass('rq-contract-perfect');
	
	// Total Count - Title Hover
	total_title = "Currently: "+total_count+"\nMin: "+total_min+"\nPref: "+total_pref+"\nMax: "+total_max;
	counter.prop('title',total_title);
	
	// Position Counts
	th.removeClass('rq-pos-over rq-pos-under');
	alerts_title = "";
	th.find('div.staff_position[data-group=true]').each(function(){
		total_count = parseInt($(this).attr('data-count'));
		if(!total_count) total_count = 0;
		total_min = parseInt($(this).attr('data-min'));
		total_max = parseInt($(this).attr('data-max'));
		pos_name = getPosInfo($(this).attr('data-pos_id'),'data-name');
		alerts_title_count = total_count + " : " + pos_name + " [" + total_min + "/" + total_max + "]";;
		
		if(total_count > total_max){
			th.addClass('rq-pos-over');
			alerts_title = alerts_title_count + " (Over)\n" + alerts_title;
		}
		if(total_count < total_min){
			th.addClass('rq-pos-under');
			alerts_title = alerts_title_count + " (Under)\n" + alerts_title;
		
		}
		if(total_count <= total_max && total_count >= total_min){
			alerts_title += "\n" + alerts_title_count + " (Good)";
		}
	});
	
	alerts.prop('title',alerts_title);

}
function updateColStyle(obj, shift, date){// obj = shift th in thead
	if(shift && date)
		th = $('table#calendar thead th[data-shift='+shift+'][data-date='+date+']');
	else
		th = $(obj);
	
	alerts = th.find('div.shift_alerts');
	over = false;
	under = false;
	alerts_title = "";
	th.find('div.needs_position').each(function(){
		t = $(this);
		pos_name = getPosInfo(t.attr('data-pos_id'),'data-name');
		alerts_title_count = t.attr('data-count') + "/" + t.attr('data-number') + " : " + pos_name;
		if(t.attr('data-count') > t.attr('data-number')){
			over = true;
			alerts_title += alerts_title_count + " (Over)\n";
		}
		else if(t.attr('data-count') < t.attr('data-number')){
			under = true;
			alerts_title += alerts_title_count + " (Under)\n";
		}
	});
	th.removeClass('rq-shift-pos-over rq-shift-pos-under');
	if(over)
		th.addClass('rq-shift-pos-over');
	if(under)
		th.addClass('rq-shift-pos-under');
	alerts.prop('title',alerts_title);
}
function updateCellStyle(obj){// obj = td
	td = $(obj);
	
	// Prep
	td.removeClass('rq-av0 rq-av1 rq-av2 rq-av3 rq-fixed rq-fixed-correct rq-fixed-incorrect');
	pref = td.attr('data-pref');
	if(td.attr('data-fixed') && td.attr('data-fixed')!="0")
		fixed = td.attr('data-fixed');
	else
		fixed = false;
	
	// Availability
	td.addClass('rq-av' + pref);
	if(fixed){
		if(td.is(":not([data-scheduled])"))
			td.addClass('rq-fixed');
		else if(td.attr('data-scheduled') == fixed)
			td.addClass('rq-fixed-correct');
		else if(td.attr('data-scheduled') != fixed)
			td.addClass('rq-fixed-incorrect');
	}
	
	
	// Conflict (with working double positions, or scheduled on a day not-off, etc.

}

// DIALOG BOX FUNCTIONS
function openPosDialogBox(event, td){
	td = $(td);
	box = $('div#pos_dialog_box');
	
	if(box.attr('data-staff') == td.attr('data-staff') && box.attr('data-shift') == td.attr('data-shift') && box.attr('data-date') == td.attr('data-date')){
		closePosDialogBox(td);
	}
	else{
		closePosDialogBox(td);
		th = $("table#calendar th[data-staff='"+td.attr('data-staff')+"']");
		box.attr('data-staff',td.attr('data-staff'));
		box.attr('data-shift',td.attr('data-shift'));
		box.attr('data-date',td.attr('data-date'));
		
		if(td.attr('data-scheduled'))
			scheduled = td.attr('data-scheduled').split(',');
		else
			scheduled = new Array('0');
			
		if(td.attr('data-fixed'))
			fixed = td.attr('data-fixed');
		else
			fixed = false;
			
		// Make HTML
		html = "";
		html_first = "<div class='pos_group'>";
		html_second = "<div class='pos_nogroup'>";
		
		th.find('div.staff_position').each(function(){
			t = $(this);
			pos_id = $(this).attr('data-pos_id');
			title = "Scheduled: " + t.attr('data-count') + "\nMin: " + t.attr('data-min') + "\nPref: " + t.attr('data-pref') + "\nMax: " + t.attr('data-max');
			if($('div#pos_dialog_box input.abbr').prop('checked'))
				pos_format = 'data-abbr';
			else
				pos_format = 'data-name';
			inst_html = "<div class='pos_button";
			if(pos_format == 'data-abbr') inst_html += ' abbr';
			if(pos_id == fixed){
				inst_html += ' fixed';
			}
			if($(this).attr('data-group') == 'false') inst_html += ' nogroup';
			$(scheduled).each(function(){
				if(this == pos_id) inst_html += ' selected';
			});
			inst_html += "' title='"+title+"' data-pos_id='"+pos_id+"'>"+getPosInfo(pos_id,pos_format)+"</div>";
			
			if($(this).attr('data-group')=='true')
				html_first += inst_html;
			else
				html_second += inst_html;
			//div#pos_dialog_box input.abbr

		});
		html_first += "</div>";
		html_second += "</div>";
		html = html_first + html_second;
		// Place It
		tdPos = td.offset();
		tdWidth = td.width();
		tdHeight = td.height();
		left = tdPos.left + tdWidth;
		summit = tdPos.top + tdHeight;
		
		
		// Put it all in the box
		box.find('div.options').html(html);
		box.find('div.name').html(th.find('div.staff_name').text());
		//box.css('left',left);
		//box.css('top',summit);
		box.show();
		$('td.selected').removeClass('selected');
		td.addClass('selected');
		
		
		mouseX = event.screenX;
		mouseY = event.screenY;
		winWidth = $(window).width();
		winHeight = $(window.top).height();
		boxWidth = box.outerWidth(true);
		boxHeight = box.outerHeight(true);
		
		if(mouseX > winWidth/2)
			left -= tdWidth + boxWidth;
		if(mouseY > winHeight/2)
			summit -= tdHeight + boxHeight;
			
		box.css('left',left);
		box.css('top',summit);
		
		listeners();
	}
}
function closePosDialogBox(){
	$('td.selected').removeClass('selected');
	
	save_button = $('div#pos_dialog_box button.save');
	save_button.addClass('invis');
	box = $('div#pos_dialog_box');
	box.removeAttr('data-staff');
	box.removeAttr('data-shift');
	box.removeAttr('data-date');
	box.hide();
	
}
function changePosSelection(obj,autoSave){
	o = $(obj);
	
	if(o.hasClass('selected'))
		o.removeClass('selected');
	else{
		if(!doubleBookEnabled())
			$('div#pos_dialog_box div.options div.pos_button.selected').removeClass('selected');
		o.addClass('selected');
	}
	
	if(!autoSave){
		save_button = $('div#pos_dialog_box button.save');
		if(save_button.hasClass('invis'))
			save_button.removeClass('invis');
	}
	
	else{
		saveAndClose();
	}
}
function toggleDoubleBook(forceOff){
	button = $('div#pos_dialog_box button.double');
	if(button.hasClass('pressed') || forceOff)
		button.removeClass('pressed');
	else
		button.addClass('pressed');
}
function saveAndClose(){
	toggleDoubleBook(true);
	box = $('div#pos_dialog_box');
	pos = box.find('div.options div.pos_button.selected');
	pos_id = new Array();
	pos.each(function(){
		pos_id.push($(this).attr('data-pos_id'));
	});
	staff_id = box.attr('data-staff');
	shift_id = box.attr('data-shift');
	date = box.attr('data-date');
	
	td = $('table#calendar td[data-staff='+staff_id+'][data-date='+date+'][data-shift='+shift_id+']');
	tr = td.closest('tr');
	ch = $("table#calendar thead th[data-date="+date+"][data-shift="+shift_id+"] div.needs_position");
	td.attr('data-scheduled',pos_id.join(','));
	// Update Info
	updateCell(td);
	updateRowCounts(tr);
//	ch.each(function(){updateColCounts(ch)});
	updateAllColCounts(); /* NO CLUE WHY THE ABOVE IS NOT WORKING AND WHY THIS MUST BE DONE. LOOK INTO IT, IT'S INEFFICIENT. MORE NOTES AT THE FUNCTION */
	updateTdTitles(td);
	
	// Update Style
	updateAllStyle(td);
	
	closePosDialogBox();
}

// INFO FUNCTIONS
function setInfo(obj, type){ // object = th, type='shift'|'staff'
	th = $(obj);
	if(th.hasClass('selected'))
		removeInfo(obj,type);
	else{
		if(type=='shift')
			showShiftInfo(obj);
		else if(type=='staff')
			showStaffInfo(obj);
	}
	
}
function showShiftInfo(obj){
	th = $(obj);
	
	$('table#calendar thead th.selected').removeClass('selected');
	th.addClass('selected');
	shift_info = $('div#disp_info div#disp_shift_info');
	
	// Positions In Group
	pg_html = "Positions: <br/> <table><tr><th>Name</th><th>Set</th><th>Require</th></tr>";
	pg = th.find('div.needs_position[data-group=true]');
	pg.each(function(){
		t = $(this);
		pg_html += "<tr><td>"+getPosInfo(t.attr('data-pos_id'),'data-name')+"</td>";
		pg_html += "<td>"+t.attr('data-count')+"</td>";
		pg_html += "<td>"+t.attr('data-number')+"</td></tr>";
	});
	pg_html += "</table>";
	
	// Positions Not In Group
	pn_html = "Positions: <br/> <table><tr><th>Name</th><th>Set</th><th>Require</th></tr>";
	pn = th.find('div.needs_position[data-group!=true]');
	pn.each(function(){
		t = $(this);
		pn_html += "<tr><td>"+getPosInfo(t.attr('data-pos_id'),'data-name')+"</td>";
		pn_html += "<td>"+t.attr('data-count')+"</td>";
		pn_html += "<td>"+t.attr('data-number')+"</td></tr>";
	});
	pn_html += "</table>";
	
	html = pg_html + "<br/>" + pn_html;
	shift_info.html(html);
}
function showStaffInfo(obj){
	th = $(obj);
	$('table#calendar tbody th.selected').removeClass('selected');
	th.addClass('selected');
	staff_info = $('div#disp_info div#disp_staff_info');
	html = 'testing staff';
	
	
	html = '';
	// Staff Name:
	name_html = th.find('div.staff_name').text();
	// Contract: 
	contract = th.find('span.staff_contract');
	contract_html = "Shifts Scheduled: " + contract.attr('data-count') + "<br/>";;
	contract_html += "Contract: ";
	contract_html += " Min:" + contract.attr('data-min');
	contract_html += " Pref:" + contract.attr('data-pref');
	contract_html += " Max:" + contract.attr('data-max');
	// Positions In Group: Pos_name: Current, Max
	pg_html = "Positions: <br/> <table><tr><th>Name</th><th>Set</th><th>Min</th><th>Pref</th><th>Max</th></tr>";
	pg = th.find('div.staff_position[data-group=true]');
	pg.each(function(){
		t = $(this);
		pg_html += "<tr><td>"+getPosInfo(t.attr('data-pos_id'),'data-name')+"</td>";
		pg_html += "<td>"+t.attr('data-count')+"</td>";
		pg_html += "<td>"+t.attr('data-min')+"</td>";
		pg_html += "<td>"+t.attr('data-pref')+"</td>";
		pg_html += "<td>"+t.attr('data-max')+"</td></tr>";
	});
	pg_html += "</table>";
	
	// Position Not In Group:
	pn_html = "Positions (not relevant to current rota): <br/> <table><tr><th>Name</th><th>Set</th><th>Min</th><th>Pref</th><th>Max</th></tr>";
	pn = th.find('div.staff_position[data-group!=true]');
	pn.each(function(){
		t = $(this);
		pn_html += "<tr><td>"+getPosInfo(t.attr('data-pos_id'),'data-name')+"</td>";
		pn_html += "<td>"+t.attr('data-count')+"</td>";
		pn_html += "<td>"+t.attr('data-min')+"</td>";
		pn_html += "<td>"+t.attr('data-pref')+"</td>";
		pn_html += "<td>"+t.attr('data-max')+"</td></tr>";
	});
	pn_html += "</table>";
	
	html = name_html + "<br/>" + contract_html + "<br/>" + pg_html + "<br/>" + pn_html;
	staff_info.html(html);
}
function removeInfo(obj,type){ // object = th, type='shift'|'staff'
	th = $(obj);
	staff_info = $('div#disp_info div#disp_staff_info');
	shift_info = $('div#disp_info div#disp_shift_info');
	
	th.removeClass('selected');
	if(type=='shift')
		shift_info.html('');
	else if(type=='staff')
		staff_info.html('');
}

// OTHER FUNCTIONS
function quickClearPos(td){
	td = $(td);
	if(td.attr('data-scheduled')){
		td.removeAttr('data-scheduled');
		updateCell(td);
		updateRowCounts(td.closest('tr'));
		updateAllColCounts();
		updateTdTitles(td);
		updateAllStyle(td);
	}
}
function doubleBookEnabled(){
	button = $('div#pos_dialog_box button.double');
	if(button.hasClass('pressed'))
		return true;
	else
		return false;
}

function mouseHover(td, m){// obj = td;
	if(m=='off')
		$('th.mouseHover').removeClass('mouseHover');
	else if(m=='on'){
		td = $(td);
		$("th[data-staff='"+td.attr('data-staff')+"']").addClass('mouseHover');
		$("th[data-date='"+td.attr('data-date')+"'][data-shift='"+td.attr('data-shift')+"']").addClass('mouseHover');
	}
}

function toggleStyle(button){
	b = $(button);
	styles_home = $('div#styles');
	style = styles_home.find('div.'+b.attr('data-div_class'));
	if(b.hasClass('pressed')){
		html = style.html();
		html = '<!--' + html + '-->';
		style.html(html);
		b.removeClass('pressed');
	}
	else{
		html = style.html();
		html = html.substr(4);
		html = html.substr(0,html.length-3);
		style.html(html);
		b.addClass('pressed');
	}
}

// LIBRARY FUNCTIONS
function getPosInfo(pos_id,attribute){
	home = $("div#hidden_info");
	pos = home.find("div.raw_position[data-pos_id="+pos_id+"]");
	return pos.attr(attribute);
}

// BASE FUNCTIONS
function listeners(){
	$('input').off();
	$('select').off();
	$('button').off();
	$('td').off();
	$('th').off();
	$("div#scroll_content").off();
	
	$('#controls input.date').change(function(){	refreshCalendar()	});
	$('#controls select.group').change(function(){	refreshCalendar()	});
	$('#controls button#save_rota').click(function(){	saveRota()	});
	$('#controls button#clear_rota').click(function(){	clearRota()	});
	$('#controls button#auto_fixed').click(function(){	autoFixed()	});
	
	$('#controls button.style_test2').click(function(){	toggleStyle(this); });
	
	$('table#calendar td').mouseover(function(){ 	mouseHover(this,'on');});
	$('table#calendar td').mouseout(function(){ 	mouseHover(this,'off');});
	$('table#calendar td').click(function(){	openPosDialogBox(event, this);	});
	$('table#calendar td').dblclick(function(){	quickClearPos(this);	});
	$('table#calendar thead th').click(function(){ setInfo(this,'shift')	});
	$('table#calendar tbody th').click(function(){ setInfo(this,'staff')	});
	
	$('div#pos_dialog_box button.close').click(function(){	closePosDialogBox();});
	$('div#pos_dialog_box button.double').click(function(){	toggleDoubleBook();});
	$('div#pos_dialog_box button.save').click(function(){	saveAndClose();});
	$('div#pos_dialog_box div.pos_button').click(function(){	changePosSelection(this);	});
	$('div#pos_dialog_box div.pos_button').dblclick(function(){	changePosSelection(this,true);	});
	
}


// SCROLL & SLIDER STUFF
function scrollMan(){
	cal = $("table#calendar");
	cal_height = $("div#scroll_content").height();
	vis_count = 0;
	cal.find('tbody tr').each(function(){
		if(this.offsetTop + this.offsetHeight < cal_height)
			vis_count ++;
	});
	tot_count = cal.find('tbody tr').length;
	min = vis_count - tot_count;
	console.log('min:'+min);
	$( "div#scroll_bar" ).slider({
		orientation: "vertical",
		range: "min",
		min: min,
		max: 0,
		value: 0,
		change: function( event, ui ) {
			show_tr(ui.value);
		}
	});
	
	$("div#scroll_bar").attr('data-visible_rows',vis_count);
	$("div#scroll_bar").attr('data-total_rows',tot_count);
}
function show_tr(tr_to_hide){
	cal =  $('table#calendar');
	scroll_bar = $("div#scroll_bar");
	tr_to_hide = -tr_to_hide;
	total_rows = parseInt(scroll_bar.attr('data-total_rows'));
	vis_rows = parseInt(scroll_bar.attr('data-visible_rows'));
	
	show_start = tr_to_hide + 1;
	show_end = show_start + vis_rows;
	
	i=1;
	cal.find('tbody tr').each(function(){
		if(i < show_start || i > show_end)
			$(this).addClass('scroll_hidden');
		else
			$(this).removeClass('scroll_hidden');
		i++;
	});
}
/*
function scrollMan(){
	cal = $("table#calendar");
	cal_height = $("div#scroll_content").height();
	vis_count = 0;
	cal.find('tbody tr').each(function(){
		if(this.offsetTop + this.offsetHeight < cal_height)
			vis_count ++;
	});
	tot_count = cal.find('tbody tr').length;
	
	$( "div#scroll_bar" ).slider({
		orientation: "vertical",
		range: "min",
		min: -100,
		max: 0,
		value: 0,
		change: function( event, ui ) {
			show_tr(ui.value);
		}
	});
	
	$("div#scroll_bar").attr('data-visible_rows',vis_count);
	$("div#scroll_bar").attr('data-total_rows',tot_count);
}
function prep_cal_slider(cal){
	//prep_cal_slider($('table#rota_cal'));
	div_height = $('div#scroll_content').height();
	th_height = cal.find('thead').height();
	tr_height = cal.find('tbody tr:first').height();
	count = Math.floor((div_height - th_height) / tr_height) - 1;
	
	cal.attr('data-rows_to_show', count);
}
function show_tr(neg_percent){
	cal =  $('table#calendar');
	scroll_bar = $("div#scroll_bar");
	percent = -neg_percent/100;
	
	total_rows = parseInt(scroll_bar.attr('data-total_rows'));
	vis_rows = parseInt(scroll_bar.attr('data-visible_rows'));
	
	if(percent > 0.95)
		vis_rows --;
	show_start = Math.ceil((total_rows - vis_rows) * percent);
	show_end = show_start + vis_rows;
	
	i=1;
	cal.find('tbody tr').each(function(){
		if(i < show_start || i > show_end)
			$(this).addClass('scroll_hidden');
		else
			$(this).removeClass('scroll_hidden');
		i++;
	});
}
*/

$(function(){
	listeners();
	refreshCalendar();
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
?>

<div id='styles'>
	<div class='test2'><!--
		<style>
		body{color:green;}
		</style>
	--></div>
</div>
<div id='action_status'></div>
<div id='action_response'></div>
<div id='controls'>
	<select class='group'><?php
		require_once('../includes/general_functions.php');
		$sql = "SELECT `group_id`,`name` FROM `data_group` ORDER BY `order` ASC";
		$query = new rQuery($con,$sql,false,false,true);
		$results = $query->run();
		
		if($results && is_array($results) && count($results)>0)
		foreach($results as $r)
			echo "<option value='{$r['group_id']}'>{$r['name']}</option>";
	?></select>
	<input type='date' class='date' value="<?php echo standard_date_val();?>"/>
	<button id='save_rota'>Save</button>
	<button id='auto_fixed'>Auto-Fixed</button>
	<button id='clear_rota'>Clear Rota</button>
	<button id='style_test'>Style Test</button>
	<button class='style_test2' data-div_class='test2'>Style Test 2</button>
</div>
<div id='pos_dialog_box'>
	<div class='header_bar'>
		<input class='abbr' title='Checked:Position Abbreviations\nUnchecked: Full Position Names' type='checkbox' value='abbr' checked />
		<button class='double'>dbl</button>
		<button class='close'>X</button>
		<button class='save invis'>S</button>
	</div>
	<div class='name'></div>
	<div class='options'></div>
</div>

<div id='scroll_bar' style='margin-top:75px;'></div>
<div id='scroll_content'>
	<div id='content'>
	</div>
</div>
<div id='disp_info'>
	<div>
		<h3>Staff Info:</h3>
		<div id='disp_staff_info'></div>
	</div>
	<hr/>
	<div>
		<h3>Shift Info:</h3>
		<div id='disp_shift_info'></div>
	</div>
</div>

<div id='hidden_info'>
</div>
</body>

</html>
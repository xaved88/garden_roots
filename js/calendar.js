// after_push_date(); - the function to call things after the date has been pushed.

function change_group(obj){
	table = $(obj).parent().parent().parent().parent().parent();
	par = table.parent();
	
	data = {};
	data.datestr = table.find('thead input[type=date]').val();
	if(!data.datestr)
		data.datestr = '';
	data.type = table.attr('data-type');
	data.id = table.attr('id');
	data.clas = table.attr('class');
	data.title = table.find('th.cal_title').html();
	data.row1_type = table.attr('data-row1_type');
	data.row2_type = table.attr('data-row2_type');
	data.col1_type = table.attr('data-col1_type');
	data.col2_type = table.attr('data-col2_type');
	data.row1_format = table.attr('data-row1_format');
	data.row2_format = table.attr('data-row2_format');
	data.col1_format = table.attr('data-col1_format');
	data.col2_format = table.attr('data-col2_format');
	data.group_id = $('div#cal_group_radio input[type=radio]:checked').val();
	
	staff_id = $('div.staff_selector').attr('data-selected');
	
	$.ajax({
		type:'POST',
		url:'../disp/d_calendar.php',
		data:{
			data:data,
			staff_id: staff_id
		},
		success:function(data){
			$(par).html(data);
			try{
				$( "#cal_group_radio" ).buttonset();
				after_push_date();
			}
			catch(err){
			
			}
		}
	});
}
function push_date(obj){
	table = $(obj).parent().parent().parent().parent();
	par = table.parent();
	
	data = {};
	data.datestr = $(obj).val();
	if(!data.datestr)
		data.datestr = $(obj).attr('data-value');
	data.type = table.attr('data-type');
	data.id = table.attr('id');
	data.clas = table.attr('class');
	data.title = table.find('th.cal_title').html();
	data.row1_type = table.attr('data-row1_type');
	data.row2_type = table.attr('data-row2_type');
	data.col1_type = table.attr('data-col1_type');
	data.col2_type = table.attr('data-col2_type');
	data.row1_format = table.attr('data-row1_format');
	data.row2_format = table.attr('data-row2_format');
	data.col1_format = table.attr('data-col1_format');
	data.col2_format = table.attr('data-col2_format');
	data.group_id = $('div#cal_group_radio input[type=radio]:checked').val();
	
	staff_id = $('div.staff_selector').attr('data-selected');
	$.ajax({
		type:'POST',
		url:'../disp/d_calendar.php',
		data:{
			data:data,
			staff_id: staff_id
		},
		success:function(data){
			$(par).html(data);
			try{
				$( "#cal_group_radio" ).buttonset();
				after_push_date();
			}
			catch(err){
			
			}
		}
	});
}

$(function(){
	$( "#cal_group_radio" ).buttonset();
	/*
	$(".rota_staff_radio").each(function(){
		$(this).buttonset();
	});
	*/
});

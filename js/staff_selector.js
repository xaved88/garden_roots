/*
Include whatever you want to do with the selected staff member by the function below on the page:
function staff_selector_selected(staff_id){}
*/
function staff_selector_name_listener(){
	$(".staff_selector .findings .staff_selector_member").off();
	$(".staff_selector .findings .staff_selector_member").click(function(){
		var staff_id = $(this).attr('data-staff_id');
		$(".staff_selector").attr('data-selected', staff_id);
		
		try {	staff_selector_selected(staff_id);
			staff_selector_name_class_update();
		}
		catch(err){
			alert("There was an error with the staff selection. Do you have staff_selector_selected(id) defined in your doc?");
		}
	});
}

function staff_selector_name_class_update(){
	var sel = $(".staff_selector").attr('data-selected');
	sel = sel.split(',');
	$(".staff_selector .findings .staff_selector_member").each(function(){
		var selected = false;
		for(i=0; i<sel.length; i++)
			if(sel[i] == $(this).attr('data-staff_id'))
				selected = true;
		if(selected)
			$(this).addClass('ss_selected');
		else
			$(this).removeClass('ss_selected');		
	});
}

function staff_selector_update(){
	var search = $(".staff_selector .search input[name=staff_name]").val();
	var sort = 'ASC'
	$(".staff_selector .search input[name=sort]").each(function(){
		if($(this).is(':checked'))
			sort = $(this).val();
	});
	
	$.ajax({
			type:'POST',
			url:'../includes/staff_selector.php',
			data:{
				action: 'fetch',
				search: search,
				sort: sort
			},
			success:function(data){
				$(".staff_selector .findings").html(data);
				staff_selector_name_listener();
				staff_selector_name_class_update();
			}
		});
}

function staff_selector_listener(){
	$(".staff_selector .search input[name=staff_name]").change(function(){
		staff_selector_update();
	});
	$(".staff_selector .search input[name=sort]").change(function(){
		staff_selector_update();
	});
}

$(function() {
	staff_selector_listener();
});
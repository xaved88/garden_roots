$(function() {
	$( "#tabs" ).tabs();
	$("#oc").tabs();
	
	$( "#oc .acc").each(function(){
		$(this).accordion();
	});
});
function oc_fill_section(div_id, id){
	if($("#" + div_id)){
		$.ajax({
			type:'POST',
			url:'includes/oc_fill.php',
			data:{
				div_id: div_id,
				id: id
			},
			success:function(data){
				$("#" + div_id).html(data);
				oc_inner_accordian(div_id);
			}
		});
	}
}
function oc_fill(staff_id){
	oc_fill_section('oc_staff_details',staff_id);
	oc_fill_section('oc_staff_av',staff_id);
	oc_fill_section('oc_staff_away',staff_id);
	oc_fill_section('oc_staff_here',staff_id);
	oc_fill_section('oc_staff_lang',staff_id);
	oc_fill_section('oc_staff_pos',staff_id);
}
function oc_inner_accordian(id){
	$('#' + id).find('.oc_inside.acc').each(function(){
		$(this).accordion();
	});
}
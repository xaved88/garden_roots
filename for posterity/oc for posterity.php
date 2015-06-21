<html>
<head>
<link rel="stylesheet" type="text/css" href="css/oc.css">
<link href="css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.10.4.custom.js"></script>
<script src="js/oc.js"></script>

<!--
<script>

// ALL OF THIS CAN ESSENTIALLY GO INTO AN INCLUDE.
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

</script>
-->	
</head>
<body>
<button onclick=oc_fill(1);>Fill it (staff:1)</button><br/>
<button onclick=oc_test();>Test</button>
<?php 
require_once('includes/oc.php');

$oc = new OC();
$oc->title="Mastermind";
$oc->show();
?>
</body>
</html>
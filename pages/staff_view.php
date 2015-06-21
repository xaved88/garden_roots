<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - View Staff</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/staff_selector.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<script>
// Staff Selector actions
function staff_selector_selected(staff_id){
	staff_selector_fetch(staff_id,"details");
	staff_selector_fetch(staff_id,"lang");
	staff_selector_fetch(staff_id,"pos");
	staff_selector_fetch(staff_id,"av");
	staff_selector_fetch(staff_id,"hereaway");
}
function staff_selector_fetch(staff_id, div){
	$.ajax({
		type:'POST',
		url:'../disp/d_staff_mod.php',
		data:{
			staff_id: staff_id,
			action: div + '_view'
		},
		success:function(data){
			$("#tabs-" + div).html(data);
		}
	});
}
$(function() {
	$( "#mod_staff" ).tabs();
});
</script>
</head>

<body>
<?php 
include('../includes/nav_bar.php');
include('../includes/staff_selector.php');
?>
<div id='action_response'></div>
<div id='mod_staff'>
	<ul>
		<li><a href="#tabs-details">Staff Details</a></li>
		<li><a href="#tabs-lang">Languages</a></li>
		<li><a href="#tabs-pos">Positions</a></li>
		<li><a href="#tabs-av">Availability</a></li>
		<li><a href="#tabs-hereaway">Vacation</a></li>
	</ul>
	<div id="tabs-details"></div>
	<div id="tabs-lang"></div>
	<div id="tabs-pos"></div>
	<div id="tabs-av"></div>
	<div id="tabs-hereaway"></div>
</div>

</body>
</html>
<?php
require_once('../includes/authentication.php');
?>
<html>
<head>
<title>Garden Roots - Home</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>
<script src="../js/staff_selector.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link rel="shortcut icon" href="../images/favicon.ico">
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
?>
<div id='action_response'></div>
<i>Current Version/Patch: v 0.4_e_01</i><br/>
Welcome to Garden Roots! Select an option from above.
<ul>
	<li><a href='../test/test.php'>Test Page 1</a></li>
	<li><a href='../test/test2.php'>Test Page 2</a></li>
	<li><a href='../test/test3.php'>Test Page 3</a></li>
</ul>
Scripts:
<ul>
	<li><a href='../scripts/update_av_v04d3.php'>AV Table Update</a></li>
</ul>
</body>
</html>
<?php
	require_once('../includes/staff_classes.php');
	
	$staff = new staffS($con);
	
	$staff->first_name = $_POST['first_name'];
	$staff->last_name = $_POST['last_name'];
	$staff->type = $_POST['type'];
	$staff->gender = $_POST['gender'];
	$staff->partner = $_POST['partner'];
	$staff->birthday = $_POST['birthday'];
	$staff->mailing_address = $_POST['mailing_address'];
	$staff->phone = $_POST['phone'];
	$staff->phone2 = $_POST['phone2'];
	$staff->email = $_POST['email'];
	$staff->email2 = $_POST['email2'];
	
	$success = false;
	$staff->set_name();
	if($staff->insert($con))
		$success = true;
		
	for($i=0; $i<$_POST['lang_max']; $i++){
		if(isset($_POST['lang_'.$i]))
			$staff->add_lang($_POST['lang_'.$i]);
	}
	for($i=0; $i<$_POST['pos_max']; $i++){
		if(isset($_POST['pos_'.$i]))
			$staff->add_pos($_POST['pos_'.$i]);
	}
	
	$staff->update_lang($con);
	$staff->update_pos($con);
?>

<html>
<head>
<title>Garden Roots - Add Staff</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>

<link href="../css/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<link href="../css/nav_bar.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

<script>
$(function() {
	$( "#add_staff" ).tabs();
});
</script>
</head>


<body>
<?php include('../includes/nav_bar.php');

echo "<br/><br/>";
echo ($success) ? "{$staff->name} has been successfully added!" : "Error: {$staff->name} was not added.";?>


</body>

</html>
<html>
<head>
<title>Garden Roots - Add Staff</title>

<script src="../js/jquery-1.10.2.js"></script>
<script src="../js/jquery-ui-1.10.4.custom.js"></script>

<link rel="shortcut icon" href="../images/favicon.ico">
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
<?php include('../includes/nav_bar.php');?>
<h1> Welcome to the add staff page!</h1>


<form action='../forms/f_staff_add.php' method='POST'>
<div id="add_staff">
	<ul>
		<li><a href="#tabs-1">Staff Details</a></li>
		<li><a href="#tabs-2">Languages</a></li>
		<li><a href="#tabs-3">Positions</a></li>
	</ul>
	<div id="tabs-1">
	<i>Required</i><br/>
	First Name: <input type='text' name='first_name'><br/>
	Last Name:	<input type='text' name='last_name'><br/>
	Staff Type:	<select name='type'>
	<?php require_once('../includes/general_functions.php');
		$config = new conFig();
		$st = $config->staff_type_array();
		foreach($st as $t)
			echo "<option value='{$t['id']}'>{$t['name']}</option>";
	?>
	</select><br/>
	<i>Optional</i><br/>
	Gender: <select name='gender'>
	<?php require_once('../includes/constants.php');
		echo "<option value='0'></option>
			<option value='" . MALE . "'>Male</option>
			<option value='" . FEMALE . "'>Female</option>";
	?>	
	</select><br/>
	Partner: <select name='partner'>
	<option value=''>            </option>
	<?php require_once('../includes/staff_classes.php');
		$staff = new staffM($con);
		foreach($staff->staff as $s){
			echo "<option value='{$s->staff_id}'>{$s->name}</option>";
		}
	?>
	</select><br/>
	Birthday: <input type='date' name='birthday'><br/>
	Mailing Address: <input type='text' name='mailing_address'><br/>
	Phone: <input type='text' name='phone'><br/>
	Phone (Secondary): <input type='text' name='phone2'><br/>
	Email: <input type='text' name='email'><br/>
	Email (Secondary): <input type='text' name='email2'><br/>
	</div>
	<div id="tabs-2">
	Language: <br/>
	<?php require_once('../includes/staff_classes.php');
		$lang = new dLangLib($con);
		$i=1;
		foreach($lang->ord as $o){
			$l = $lang->lang[$o['lang_id']];
			echo "<input type='checkbox' name='lang_$i' value='{$l->lang_id}'> {$l->name} <br/>";
			$i++;
		}
		echo "<input type='hidden' name='lang_max' value='$i'>";
	?>
	</div>
	<div id="tabs-3">
	Position: <br/> <!-- need dynamic -->
	<?php require_once('../includes/staff_classes.php');
		$pos = new dPosLib($con);
		$i=1;
		foreach($pos->ord as $o){
			$p = $pos->pos[$o['pos_id']];
			echo "<input type='checkbox' name='pos_$i' value='{$p->pos_id}'> {$p->name} <br/>";
			$i++;
		}
		echo "<input type='hidden' name='pos_max' value='$i'>";
	?>
	</div>
</div>
<input type='submit' name='submit' value="Add Staff">
</form>

</body>

</html>
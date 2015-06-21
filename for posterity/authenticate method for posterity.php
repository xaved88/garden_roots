<?php 
require_once('includes/authentication.php');

$access = array(AUTH_ADMIN, AUTH_ADMIN, AUTH_GENERAL);
$auth = new Authenticate($con, $access);
if(!$auth -> authorize()){
	include('access_denied.php');
	exit();
}
?>
<html>
<head>
</head>
<body>
	<a href='index.php'>Home</a>
	<form action='login.php' method='post'>
		<input type='hidden' name='atype' value='logout'>
		<input type='hidden' name='page' value='http://localhost/garden_roots_new/index.php'>
		<input type='submit' value='Logout'>
	<?php
	require_once('includes/calendar.php');
	$calendar = new Calender($con);
	$calendar->title = "Shalom Kupo!";
	$calendar->row2_type = CAL_SHIFT;
	$calendar->col2_type = CAL_SHIFT;
	$calendar->init();
	$con->close();
	?>
</body>
</html>
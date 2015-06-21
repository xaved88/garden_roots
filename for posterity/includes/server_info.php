<?php
$server_ip = "127.0.0.1";
$server_user = "root";
$server_pass = "";
$server_db = "garden_roots_new";

$con=mysqli_connect($server_ip,$server_user,$server_pass,$server_db);
if (mysqli_connect_errno($con))
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
?>
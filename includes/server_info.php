<?php
$server_ip = "localhost";
$server_user = "root";
$server_pass = "";
$server_db = "garden_roots";

$con=mysqli_connect($server_ip,$server_user,$server_pass,$server_db);
if (mysqli_connect_errno($con))
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
?>
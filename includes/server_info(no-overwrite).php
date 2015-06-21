<?php
/* USE THIS SOMEDAY, BUT FOR NOW IT'S STILL ON THE WHACKY STUFF.
$server_ip = "127.0.0.1";
$server_user = "root";
$server_pass = "";
$server_db = "garden_roots_new";
*/

$server_ip = "127.0.0.1";
$server_user = "rotaMan";
$server_pass = "GTr0ta5";
$server_db = "garden_roots_v04-20140701-2";

$con=mysqli_connect($server_ip,$server_user,$server_pass,$server_db);
if (mysqli_connect_errno($con))
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
?>
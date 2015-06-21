<?php


require_once('../includes/server_info.php');
require_once('../includes/general_functions.php');

print_r($con);echo "<Br/><br/>";

$sql = "SELECT * FROM `staff`";
$query = new rQuery($con,$sql);
$results = $query->run();
print_r($results);
?>
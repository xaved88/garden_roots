<?php
	require_once('../includes/general_functions.php');
	
	// GET LIST OF FIELDS FROM AV
	$fields = array();
	$sql = "SHOW COLUMNS FROM `templates_av`";
	$result = $con->query($sql);
	while ($row = $result->fetch_assoc()) {
		array_push($fields, $row['Field']);
	}
	// ADD IF IT DOESN'T EXIST
	$exists = false;
	foreach($fields as $f){
		if($f=='transfered_up')
			$exists = true;
	}
	if(!$exists){
		$sql = "ALTER TABLE  `templates_av` ADD  `transfered_up` BOOLEAN NOT NULL DEFAULT FALSE";
		$con->query($sql);
	}
	
	// GET ALL OF THE OLD DATA
	$sql = "SELECT `instance_id`,`staff_id`,`day`,`shift_id`,`pos_id`,`pref` FROM `templates_av` WHERE `type`=0 AND `transfered_up`=0 AND `staff_id`!=0";
	$query = new rQuery($con,$sql,false,false,true);
	$records = $query->run(false,6);
	
	// PUT IT IN THE NEW DATABASE
	// IF TABLE DOESN'T EXIST, CREATE IT
	$sql = "SHOW TABLES";
	$tables = $con->query($sql);
	$exists = false;
	while($t = mysqli_fetch_row($tables)){
		if($t[0] == 'av')
			$exists = true;
	}
	
	if(!$exists){
		$sql = "CREATE TABLE `av` (`start_date` DATE,`end_date` DATE,`day` SMALLINT,`staff_type` SMALLINT,`staff_id` SMALLINT,`shift_id` SMALLINT,`pref` SMALLINT,`pos_id` SMALLINT);";
		echo "<h4>Adding Table</h4>" .$sql . "<br/>";
		$con->query($sql);
	}
	
	// ON SUCCESS, MODIFY THE OLD DATA TO SHOW PROCESSED
	$success = array();
	$failure = array();
	if($records && is_array($records) && count($records)>0)
	foreach($records as $r){
		$sql = "INSERT INTO `av` (`start_date`,`end_date`,`day`,`staff_type`,`staff_id`,`shift_id`,`pref`,`pos_id`) VALUES ('0000-00-00','0000-00-00',{$r['day']},0,{$r['staff_id']},{$r['shift_id']},{$r['pref']},{$r['pos_id']}) ";
		if($con->query($sql)){
			$sql2 = "UPDATE `templates_av` SET `transfered_up`=1 WHERE `instance_id`={$r['instance_id']}";
			if($con->query($sql2))
				array_push($success,$r['instance_id']);
			else
				array_push($failure,$r['instance_id']);
		}
	}
	
	
	// ARRANGING FOR THE VOLUNTEER TEMPLATE
	$sql = "DELETE FROM `av` WHERE `type`=1 AND `start_date`='0000-00-00' AND `end_date`='0000-00-00'";
	$con->query($sql);
	
	$type = 1;
	$pref = 2;
	$day = array(2,3,4,5,6,7);
	$shift_id = array(10,11,12,13);
	
	foreach($day as $d)
	foreach($shift_id as $s){
		$sql = "INSERT INTO `av`(`start_date`,`end_date`,`day`,`staff_type`,`staff_id`,`shift_id`,`pref`,`pos_id`) VALUES ('0000-00-00','0000-00-00',{$d},{$type},0,{$s},{$pref},0)";
		$con->query($sql);
	}
?>

Process completed.<br/>
<h3>Status:</h3>
<dl>
	<dt>Records Processed:</dt>
	<dd><?php echo count($records);?></dd>
	<dt>Success Rate:</dt>
	<dd><?php echo round(count($success)*100/count($records)) . "%";?></dd>
	<dt># Success:</dt>
	<dd><?php echo count($success);?></dd>
	<dt># Failures:</dt>
	<dd><?php echo count($failure);?></dd>
<dl>
<div class='successes' style="display:none">
	<ul>
<?php
	foreach($success as $s)
		echo "<li>" . $s . "</li>";
?>
	</ul>
</div>
<div class='failures' style="display:none">
	<ul>
<?php
	foreach($failure as $f)
		echo "<li>" . $f . "</li>";
?>
	</ul>
</div>

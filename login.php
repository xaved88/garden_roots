<?php
	require_once('includes/authentication.php');

	if($_POST['atype'] == 'login')
		login($con, $_POST['username'], $_POST['pass']);
	elseif($_POST['atype'] == 'logout'){
		logout($con);
		session_unset();
		session_destroy();
	}
	if(isset($_POST['page']))
		header( 'Location: ' . $_POST['page'] );
?>
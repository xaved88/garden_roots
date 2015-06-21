<?php
require_once('includes/authentication.php');
?>
<html>
<head>
</head>
<body>
	<a href='http://localhost/garden_roots_new/index.php'>Home</a><br/>
	Bad doughnut! You don't have the proper credentials to view the page. Login here.
	<form action='login.php' method='post'>
		Username: <input type='text' name='username'><br/>
		Password: <input type='password' name='pass'><br/>
		<input type='hidden' name='page' value='
		<?php
			echo getURL();
		?>'>
		<input type='hidden' name='atype' value='login'>
		<input type='submit'>
	</form>
</body>
</html>
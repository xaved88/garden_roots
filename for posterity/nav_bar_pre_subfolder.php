<?php
require_once('includes/authentication.php');
?>
<div class='roots_logo'>
</div>
<div class='nav_bar'>
	<nav>
	<ul>
		<li><a href="#">Home</a></li>
		<li><a href="#">Staff</a>
			<ul>
			<li><a href="#">New Staff</a></li>
			<li><a href="#">View Staff</a></li>
			<li><a href="#">Modify Staff</a></li>
			</ul>
		</li>
		<li><a href="#">Rotas</a>
			<ul>
			<li><a href="#">View Rotas</a></li>
			<li><a href="#">Edit Rotas</a></li>
			<li><a href="#">Print Rotas</a></li>
			</ul>
		</li>
		<li><a href="#">Info</a></li>
		<li><a href="#">Settings</a></li>
	</ul>
	</nav>
</div>
<div class='user_box'>
	<?php 
		echo "<form action='login.php' method='POST'>";
	
		if(isset($_SESSION['uid'])) echo "
			Welcome {$_SESSION['uid']}.<br/><input type='submit' value='Logout'>
			<input type='hidden' name='atype' value='logout'>
			<input type='hidden' name='page' value='index.php'>";
			
		else echo "
			<input type='hidden' name='atype' value='login'>
			<input type='hidden' name='page' value='index.php'>
			User:<input type='text' name='username'> Pass:<input type='password' name='pass'><br/>
			<input type='submit' value='Login'>";
		echo "</form>";
	?>
</div>
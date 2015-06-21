<?php
require_once('../includes/authentication.php');
?>
<div class='page_header'>
<div class='roots_logo'>
</div>
<div class='nav_bar'>
	<nav>
	<ul>
		<li><a href="../pages/index.php">Home</a></li>
		<li><a href="../pages/staff_mod.php">Staff</a>
			<ul>
			<li><a href="../pages/staff_add.php">New Staff</a></li>
			<li><a href="../pages/staff_mod.php">Modify Staff</a></li>
			<li><a href="../pages/staff_multi_mod.php">Edit Multiple</a></li>
			<li><a href="../pages/staff_days_off.php">Days Off</a></li>
			<!--<li><a href="../pages/staff_airport.php">Airport Runs</a></li>-->
			</ul>
		</li>
		<li><a href="../pages/rota_mod.php">Rotas</a>
			<ul>
			<li><a href="../pages/rota_view.php">View Rotas</a></li>
			<!--
			<li><a href="../pages/rota_mod.php">Edit Rotas</a></li>
			<li><a href="../pages/rota_mod_new.php">Edit Rotas<sup style="text-transform:uppercase;">NEW!</sup></a></li>
			-->
			<li><a href="../pages/rota_mod_new.php">Edit Rotas</a></li>
			<li><a href="../pages/rota_needs.php">Rota Needs</a></li>
			<li><a href="../pages/rota_templates.php">Rota Templates</a></li>
			<li><a href="../pages/rota_print.php">Print Rotas</a></li>
			<li><a href="../pages/rota_mail.php">Email Rotas</a></li>
			<!--<li><a href="#">Export Rotas</a></li>-->
			</ul>
		</li>
		<li><a href="../pages/info.php">Info</a></li>
		<li><a href="../pages/settings_staff.php">Settings</a>
			<ul>
			<li><a href="../pages/settings_staff.php">Staff</a></li>
			</ul>
		</li>
	</ul>
	</nav>
</div>
<div class='user_box'>
	<?php 
		echo "<form action='../login.php' method='POST'>";
	
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
</div>
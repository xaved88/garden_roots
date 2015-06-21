<?php
session_start();

require_once('server_info.php');
require_once('constants.php');

class Authenticate{
	public $access;
	public $con;
	
	function __construct($con, $access){
		$this->con = $con;
		$this->access = $access;
	}
	function authorize(){
		if(isset($_SESSION['uid']) && isset($_SESSION['hash'])){
			$sql = "SELECT access FROM user INNER JOIN user_logins ON user.user_id=user_logins.user_id WHERE user_logins.user_id=? AND user_logins.hash=?";
			$stmt = $this->con->prepare($sql);
			//$stmt->bind_param('is', $this->user_id, $this->hash);
			$stmt->bind_param('is', $_SESSION['uid'], $_SESSION['hash']);
			$stmt->execute();
			$stmt->bind_result($access);
			$stmt->fetch();
			$stmt->close();
			
			echo "<b>Access:$access</b><br/>";
			$authorize = false;
			foreach($this->access as $a)
				if($a == $access)
					$authorize = true;
					
			return $authorize;
		}
		else
			return false;
	}
}

function login($con, $username, $pass){
	$pass_enc = md5($pass);
	$sql = "SELECT user_id FROM user WHERE username=? AND passenc=?";
	$stmt = $con->prepare($sql);
	$stmt->bind_param('ss', $username, $pass_enc);
	$stmt->execute();
	$stmt->bind_result($user_id);
	if($stmt->fetch())
		$uid = $user_id;
	else
		$uid = false;
	$stmt->close();
	
	if($uid){
		// DELETE OLD USER LOGINS
		$sql = "DELETE FROM user_logins WHERE user_id =?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param('i',$uid);
		$stmt->execute();
		$stmt->close();
		
		// MAKE NEW USER LOGINS
		$hash = md5($username . $pass . time());
		$sql = "INSERT INTO user_logins (user_id, hash) VALUES (?,?)";
		$stmt = $con->prepare($sql);
		$stmt->bind_param('is', $uid,$hash);
		$stmt->execute();
		$stmt->close();
		
		$_SESSION['uid'] = $uid;
		$_SESSION['hash'] = $hash;
	}
	return $uid;
}

function logout($con){
	if(isset($_SESSION['uid'])){
		$sql = "DELETE FROM user_logins WHERE user_id =?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param('i',$_SESSION['uid']);
		$stmt->execute();
		$stmt->close();
	}
}
function getUrl() {
  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
  $url .= $_SERVER["REQUEST_URI"];
  return $url;
}

?>
<?php
require_once("../functions.php");

try {
	if(isset($_POST['check_email'])){
		$con = connectPDO();
		$email=filter_input(INPUT_POST,"check_email", FILTER_SANITIZE_EMAIL);
		$query = $con->query("SELECT * FROM user WHERE email = '$email'");
		//$result = $query->fetchAll(PDO::FETCH_ASSOC);
		if($query->rowCount()>0){
			print "<span style='color:#f55;font-size:80%;'>This email is taken &nbsp;&#x2718;</span>";
		}else{
			print "<span style='color:#3b3;font-size:80%;'>This email is aviable &nbsp;&#x2714;</span>";
		}
		$con = $email = $query = null;
	}
	if(isset($_POST['check_user'])){
		$con = connectPDO();
		$user=filter_input(INPUT_POST,"check_user", FILTER_SANITIZE_STRING);
		$query = $con->query("SELECT * FROM user WHERE username = '$user'");
		//$result = $query->fetchAll(PDO::FETCH_ASSOC);
		if($query->rowCount()>0){
			print "<span style='color:#f55;font-size:80%;'>This username is taken &nbsp;&#x2718;</span>";
		}else{
			print "<span style='color:#3b3;font-size:80%;'>This username is aviable &nbsp;&#x2714;</span>";
		}
		$con = $user = $query = null;
	}
} catch(PDOException $e) {
	  echo 'ERROR: ' . $e->getMessage();
} // end try
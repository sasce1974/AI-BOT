<?php
require_once("../functions.php");
if (!isset($_POST['submit'])) {
    die(header("Location: ../login/"));
}
//try{
$_SESSION["formAttempt"] = true;
if (isset($_SESSION["error"])){
    unset($_SESSION["error"]);
}
$_SESSION["error"]= array();
$required= array("username", "password");
foreach ($required as $requiredField) {
    if (!isset($_POST[$requiredField]) || $_POST[$requiredField] == "") {
        $_SESSION["error"][] = $requiredField . " needed.";
    }
}

// the next code retrieve login attempt with ip
$con = connectPDO();
$username= filter_input(INPUT_POST,"username", FILTER_SANITIZE_STRING);
$pass=filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
$ip=$_SERVER['REMOTE_ADDR'];
$query = $con->prepare("INSERT INTO loginatempt (id, username, ip, time) VALUES 
(NULL , ?, ?, NOW())");
$result = $query->execute(array($username, $ip));
$con= $query = null;
unset($con, $query);

//redirection...
if (count($_SESSION["error"]) > 0) {
    die(header("Location: ../login/index.php?user={$username}"));
} else {
	$user = new User;
	if ($user->authenticate($_POST['username'], $_POST['password'])) {
		unset($_SESSION['formAttempt']);
		die(header("Location: ../")); //to HAI page
	} else {
		$_SESSION['error'][] = "Wrong username or password.";
			die(header("Location: ../login/index.php?user={$username}"));
	}
}
//} catch(PDOException $e){
//    error_message("There was a problem with the login process, please try again later.",
//        "Error with the login" . $e->getMessage() . " on line " . $e->getLine());
//}
?>
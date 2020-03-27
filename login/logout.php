<?php 
require_once("../functions.php");
$user = new User;
$user->logout();
die(header("Location: ../login/"));
?>
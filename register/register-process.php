<?php
require_once("../functions.php");
$user = new User();
if (!isset($_POST['submit'])) {
    die(header("Location:" . BASE_URL . "/register/"));
}


/** Check if submitted  data is valid */

$_SESSION["formAttempt"] = true;
if (isset($_SESSION["error"])){
    unset($_SESSION["error"]);
} 
$_SESSION["error"]= array ();
$required= array("email", "password1", "password2", "username");
foreach ($required as $requiredField) {
    if (!isset($_POST[$requiredField]) || $_POST[$requiredField] == "") {
        $_SESSION["error"][] = $requiredField . " needed.";
    }
}
if (!preg_match("/^[A-Za-z_\d]{5,}$/", $_POST["username"])) {
    $_SESSION["error"][] = "Username need to be at least 5 characters long with only letters and numbers.";
}
if (!preg_match("/.{5,}$/", $_POST["password1"])) {
    $_SESSION["error"][] = "Password need to be Minimum 5 characters.";
}


if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    $_SESSION["error"][] = "Invalid email!"; }
    
if ($_POST["password1"] != $_POST["password2"]) {
    $_SESSION["error"][] = "Passwords don't match!"; }


    /** If submitted data is valid, register the user */

if (count($_SESSION["error"]) > 0) {
    die(header("Location:" . BASE_URL . "/register/index.php?user={$_POST['username']}&email={$_POST['email']}&name={$_POST['name']}"));
} else {
    if($user->registerUser($_POST)) {
        unset($_SESSION["formAttempt"]);
        header("Location:" . BASE_URL);
        exit ();

    } else {
        error_log("Problem registering user: {$_POST['email']}");
        $_SESSION["error"][] = "There is a problem with the registration process.";
        die(header("Location:" . BASE_URL . "/register/index.php?user={$_POST['username']}&email={$_POST['email']}&name={$_POST['name']}"));
    }
}

?>
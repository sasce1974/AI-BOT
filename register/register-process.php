<?php
require_once("../functions.php");
$user = new User();
if (!isset($_POST['submit'])) {
    die(header("Location:" . BASE_URL . "/register/"));
}

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
  
/*function registerUser($userData) {
    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
    if ($mysqli->connect_errno) {
        error_log("Cannot connect to MySQL: " . $mysqli->connect_error);
        return false;
    }
    $username = $mysqli->real_escape_string($_POST["username"]);
    
//check for an existing username
    $findUser = "SELECT id FROM user WHERE username = '{$username}'";
    $findResult = $mysqli->query($findUser);
    $findRow = $findResult->fetch_assoc();
    if (isset($findRow['id']) && $findRow['id'] != "") {
        $_SESSION["error"][] = "Account with the same username already exist.";
        return false;
    }
    $findUser = $findResult = $findRow = null;
    $email = $mysqli->real_escape_string($_POST["email"]);
//check for an existing email
    $findUser = "SELECT id FROM user WHERE email = '{$email}'";
    $findResult = $mysqli->query($findUser);
    $findRow = $findResult->fetch_assoc();
    if (isset($findRow['id']) && $findRow['id'] != "") {
        $_SESSION["error"][] = "Account with the same e-mail already exist.";
        return false;
    }
    $findUser = $findResult = $findRow = null;
    if (isset($_POST["name"])) {
        $name = $mysqli->real_escape_string($_POST["name"]);
    } else {
        $name = "Anonimous_user no: " . rand(1000000, 9999999);
    }
    
    //check for an existing name
    $findUser = "SELECT id FROM user WHERE name = '{$name}'";
    $findResult = $mysqli->query($findUser);
    $findRow = $findResult->fetch_assoc();
    if (isset($findRow['id']) && $findRow['id'] != "") {
        $_SESSION["error"][] = "Account with the same full name already exist.";
        return false;
    } 
    $cryptedPassword = password_hash($_POST["password1"], PASSWORD_DEFAULT);
    $password = $mysqli->real_escape_string($cryptedPassword);

    $today = date('d.m.Y');
    $ip = $_SERVER['REMOTE_ADDR'];
  
    $query = "INSERT INTO user (id, username, password, email, name, date_created, ip) VALUES
    (Null, '{$username}', '{$password}', '{$email}', '{$name}', NOW(), '{$ip}')";
    if ($mysqli->query($query)) {
        $id = $mysqli->insert_id;
        header("Location: ../login/index.php?user={$username}");
        return true;
    } else {
        error_log("Problem inserting {$query}");
        return false;
    }  
} //end function registerUser*/

if (count($_SESSION["error"]) > 0) {
    die(header("Location:" . BASE_URL . "/register/index.php?user={$_POST['username']}&email={$_POST['email']}&name={$_POST['name']}"));
} else {
    if($user->registerUser($_POST)) {
        unset($_SESSION["formAttempt"]);
//        header("Location: ../login/index.php?user={$_POST['username']}");
        header("Location:" . BASE_URL);
        exit ();

    } else {
        error_log("Problem registering user: {$_POST['email']}");
        $_SESSION["error"][] = "There is a problem with the registration process.";
        die(header("Location:" . BASE_URL . "/register/index.php?user={$_POST['username']}&email={$_POST['email']}&name={$_POST['name']}"));
    }
}

?>
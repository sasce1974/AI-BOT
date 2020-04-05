<?php
require_once("../functions.php");
$invalidAccess = true;
if (isset($_GET['user']) && $_GET['user'] != "") {
    $invalidAccess = false;
    $hash = $_GET['user'];
}
//if they've attempted the form but had a problem, we need to allow them in.
if (isset($_SESSION['formAttempt']) && $_SESSION['formAttempt']== true) {
    $invalidAccess = false;
    $hash = $_SESSION['hash'];
}
if ($invalidAccess) {
    die(header("Location: ../login/"));
}
?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../css/form.css">
<title>Reset Password</title>
</head>
<body>
<form id="loginForm" method="POST" action="reset-process.php">
<div>
    <fieldset>
    <legend>Reset Password</legend>
    <div id="errorDiv">
<?php
    if (isset($_SESSION['error']) && isset($_SESSION['formAttempt'])) {
        unset($_SESSION['formAttempt']);
        print "Errors encountered<br />\n";
        foreach ($_SESSION['error'] as $error) {
            print $error . "<br />\n";
        } //end foreach
    } //end if
?>
    </div>
    <label for="email">E-mail Address:* </label>
    <input type="email" id="email" name="email" required>
    <br />
    <label for="password1">Password:* </label>
    <input type="password" id="password1" name="password1" required>
    <br />
    <label for="password2">Password:* </label>
    <input type="password" id="password2" name="password2" required>
    <br />
<?php
    print "<input type=\"hidden\" name=\"hash\" value=\"{$hash}\">\n";
?>
    <input type="submit" id="submit" name="submit">
    </fieldset>
</div>
</form>
</body>
</html>
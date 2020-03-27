<?php
require_once("../functions.php");
$user = new User;
if($user->isLoggedIn) header("Location:" . BASE_URL);
if(isset($_REQUEST['user'])) $username = filter_var($_REQUEST['user'], FILTER_SANITIZE_STRING);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Login</title>
    <meta charset = "UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css?family=Spartan&display=swap" rel="stylesheet">
    <script type="text/javascript" src="../jquery.js"></script>
    <script type="text/javascript" src="../form.js"></script>
    <link rel="stylesheet" type="text/css" href="../form.css">
    <link rel="stylesheet" type="text/css" href="../css/messages.css">
</head>
<body>
    <form id="loginForm" method="POST" action="login-process.php">
        <div>
            <fieldset>

                  <?php
                  if (isset($_SESSION['formAttempt'])) unset($_SESSION['formAttempt']);

                  include "../inc/messages.inc.php";

                  //$user->logout(); //to logout user if it is alredy logged in. It works here better than on the begining as there it erases session with errors!
                  ?>

                <div id="left">
                    <h2>FREE SIGN UP</h2>
                    <h4>Don't lose your messages</h4>
                    <p><a href="../register/">Create your free account</a></p>
                    <hr>
                    <h2>DON'T WANT TO REGISTER?</h2>
                    <a href="guest.php/">Sign In as a GUEST</a>
                </div>
                <div id="right">
                    <h2>HAVE ACCOUNT? Sign In</h2>
                    <label for="username">Username: </label>
                    <input type="text" id="username" name="username" value="<?php print isset($username) ? $username : ""; ?>" required>
                    <span class="errorFeedback errorSpan" id="usernameError">Username is required</span><br>
                    <label for="password">Password: </label>
                    <input type="password" id="password" name="password" required>
                    <span class="errorFeedback errorSpan" id="passwordError">Password required</span><br>
                    <input type="submit" id="submit" name="submit" value="OK">
                    <br><hr>
                    <p style="text-align:right;">Forgotten password? Click <a href="../reset/emailpass.php">here</a></p>
                </div>
            </fieldset>
        </div>
    </form>
</body>
  
</html>

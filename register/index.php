<?php
require_once("../functions.php");
if(isset($_REQUEST['user'])) $username = filter_var($_REQUEST['user'], FILTER_SANITIZE_STRING);
if(isset($_REQUEST['email'])) $email = filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL);
if(isset($_REQUEST['name'])) $name = filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>HAI - New User Sign Up</title>
    <meta charset = "UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="../js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery-ui.js"></script>
    <link rel="stylesheet", type="text/css" href="../js/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="../css/form.css?x=3">
    <link rel="stylesheet" href="../css/messages.css?x=3">
<!--  <link rel="stylesheet" type="text/css" href="../datepicker-ui.css"><!--need a new css from jqueri-ui-->
  <script type="text/javascript">
    //error debugging function
    onerror = errorHandler;
    function errorHandler(message, url, line)  {
    out  = "Sorry, an error was encountered.\n\n";
    out += "Error: " + message + "\n";
    out += "URL: "   + url     + "\n";
    out += "Line: "  + line    + "\n\n";
    out += "Click OK to continue.\n\n";
    alert(out);
    return true;
    }
      function checkIfUserExist() {
        var user = $("#username").val().trim();
        if (user!="") {
            $.post("check_new_user.php",{"check_user":user},check_user);
        }
      }
      function check_user(data, textStatus){
        $("#user_check").html(data);
      }
      function checkIfEmailExist() {
        var email = $("#email").val().trim();
        if (email!="") {
            $.post("check_new_user.php",{"check_email":email},check_email);
        }
      }
      function check_email(data, textStatus){
        $("#email_check").html(data);
      }
    </script>
</head>
<body>

<span id="logo"><img src="../images/icons/icon-152x152.png" alt="logo"></span>
    <form id="newUserForm" method="POST" action="register-process.php">
        <div>
            <fieldset>
                <h3>Sign Up Free Account</h3>

                  <?php
                    if (isset($_SESSION['formAttempt'])) unset($_SESSION['formAttempt']);
                    include "../inc/messages.inc.php";
                  ?>
                <div class="input_group">
                    <label for="username">Username: </label>
                    <div class="input-subgroup">
                        <input type="text" id="username" name="username" value="<?php print isset($username) ? $username : ""; ?>" onblur ="checkIfUserExist()" autocomplete="username" required>
                        <span id="user_check"></span>
                        <span class="errorFeedback errorSpan" id="usernameError">Username required</span>
                    </div>
                </div>
                <div class="input_group">
                    <label for="password1">Password: </label>
                    <div class="input-subgroup">
                        <input type="password" id="password1" name="password1" autocomplete="new-password" required>
                        <span class="errorFeedback errorSpan" id="passwordError">Password required</span><br>
                    </div>
                </div>
                <div class="input_group">
                    <label for="password2">Repeat password: </label>
                    <div class="input-subgroup">
                        <input type="password" id="password2" name="password2" autocomplete="new-password" required>
                        <span class="errorFeedback errorSpan" id="password2Error">Passwords don’t match</span><br>
                    </div>
                </div>
                <div class="input_group">
                    <label for="email">E-mail: </label>
                    <div class="input-subgroup">
                        <input type="email" id="email" name="email" value="<?php print isset($email) ? $email : ""; ?>"
                               onblur="checkIfEmailExist()" required>
                        <span id="email_check"></span>
                        <span class="errorFeedback errorSpan" id="emailError">E-mail is required</span><br>
                    </div>
                </div>
                <div class="input_group">
                    <label for="name">Full name: </label>
                    <div class="input-subgroup">
                        <input type="text" id="name" name="name" value="<?php print isset($name) ? $name : ""; ?>" required><br>
                    </div>
                </div>
                <input type="submit" id="submit" name="submit" value="Sign Up"><br>
                <hr>
                <a href="../login/">To login to application click here</a>
                
            </fieldset>
        </div>
    </form>

<script>
    var messages = document.getElementsByClassName('message');
    if(messages.innerHTML != ""){
        setTimeout(function(){
            messages.innerHTML = "";
            $(".message").fadeOut(1500);
        }, 6000);
    }
</script>

<script>
    /** Register the Service Worker */

   /* if ("serviceWorker" in navigator) {
        window.addEventListener("load", function() {
            navigator.serviceWorker
                .register("/serviceWorker.js")
                .then(res => console.log("service worker registered"))
                .catch(err => console.log("service worker not registered", err))
        })
    }*/
</script>

</body>
  
</html>

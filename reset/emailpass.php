<?php
require_once("../functions.php");
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <title>Password recovery</title>

  <link rel="stylesheet" type="text/css" href="../form.css">
</head>
<body>
    <form id="passRecoveryForm" method="POST" action="email-process.php">
        <div>
            <fieldset>
                <legend>Pasword recovery</legend>
                <div id="errorDiv">
                  <?php
                    if (isset($_SESSION['error']) && isset($_SESSION['formAttempt'])) {
                      unset($_SESSION['formAttempt']);
                      print "Error <br> \n";
                      foreach ($_SESSION['error'] as $error) {
                        print $error . "<br> \n";
                      } //end foreach
                    } //end if
                  ?>
                </div>
                <label for="email">E-mail:* </label>
                <input type="email" id="email" name="email" required>
                
                <input type="submit" id="submit" name="submit">
            </fieldset>
        </div>
    </form>
</body>
  
</html>

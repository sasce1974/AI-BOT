<?php
require_once("functions.php");
$user = new User;
if(!$user->checkIfUserExistInTable()){
    die(header("Location:" . BASE_URL . "/login/"));
}

$deleted_users = $user->deleteOldGuestUsers(); // delete 1 day old guest users
if (!$user->isLoggedIn) {
    die(header("Location:" . BASE_URL . "/login/"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>HAI conversation</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css?family=Spartan&display=swap" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
<!--    <link rel="stylesheet" href="jquery-ui.css">-->
<!--    <script type="text/javascript" src="jquery-ui.js"></script>-->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/messages.css">
</head>
<body>
    <div id='welcome'>Welcome <?php print $user->name; ?>
        <a style="margin:10px" href="<?php echo BASE_URL; ?>/login/logout.php">Logout</a>
    </div>


        <?php
        include "inc/messages.inc.php";
        ?>



<form id="messenger" action="" method="post">
    <div id="messages"></div>
    <div id="hai_status"></div>
    <div id="insert">
        <input autofocus type="text" name="input" id="input" placeholder="Type your message here">
        <button type="button" onclick="sendMessage()">Send</button>
    </div>
</form>
<script src="js/scripts.js"></script>
</body>
</html>
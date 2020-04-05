<?php
require_once("functions.php");
$user = new User;
$deleted_users = $user->deleteOldGuestUsers(); // delete 1 day old guest users
//Check if the user is registered in database
//This is needed especially for guest users that are automatically deleted after
//24 hours, but if not been log out the user, they still exist in the session.
if(!$user->checkIfUserExistInTable()){
    die(header("Location:" . BASE_URL . "/login/"));
}

if (!$user->isLoggedIn) {
    die(header("Location:" . BASE_URL . "/login/"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>HAI conversation</title>

<!--    <link rel="manifest" href="manifest.json" />-->

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" rel="stylesheet">
<!--    <link rel="stylesheet" href="jquery-ui.css">-->
<!--    <script type="text/javascript" src="jquery-ui.js"></script>-->
    <link rel="stylesheet" href="css/style.css?x=8">
    <link rel="stylesheet" href="css/messages.css">
</head>
<body>
    <div id='welcome'>Welcome <?php print $user->name; ?>
        <span id="logo"><img src="images/icons/icon-152x152.png" alt="logo"></span>
        <a style="margin:10px; color:white;" href="<?php echo BASE_URL; ?>/login/logout.php"><i class="fas fa-power-off"></i> </a>
    </div>


        <?php
        include "inc/messages.inc.php";
        ?>



<form id="messenger" action="" method="post">
    <div id="messages"></div>
    <div id="hai_status"></div>
    <div id="insert">

        <input autofocus type="text" name="input" id="input" placeholder="Type your message here">
        <button type="button" onclick="sendMessage()"><i class="fas fa-radiation"></i> </button>
    </div>
</form>
<script src="js/scripts.js?x=3"></script>
</body>
</html>
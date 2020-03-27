<?php
require_once("functions.php");
$user = new User;
if (!$user->isLoggedIn) {
    die(header("Location: login/"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>HAI conversation</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script type="text/javascript" src="jquery.js"></script>
<style type="text/css">
    #messages{
        width: 92%;
        height:450px;
        overflow-y: scroll;
        border: 1px solid #ccf;
        border-radius: 10px;
        background-color: #ccc;
        word-wrap: normal;
        padding: 5px;
        margin: 10px;
    }
    .me{
        text-align: left;
        color: #11a;
        background-color: #dfd;
        float: left;
        clear: both;
        max-width: 270px;
        border-radius: 10px;
        margin: 10px;
        padding: 10px;
    }
    .hai{
        text-align: right;
        color: #1a1;
        background-color: #fdd;
        float: right;
        clear: both;
        max-width: 270px;
        border-radius: 10px;
        margin: 10px;
        padding: 10px;
    }
    #welcome{
        background-color: #ccf;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        margin: 0;
        box-shadow: 10px 10px 10px grey;
        text-align: right;
        padding: 10px;
    }
    body{
        padding: 0;
    }
    form{
        width: 400px;
    }
    form input {
        display: inline-block;
        //float: left;
        height: 2em;
        width: 72%;
        border-radius:10px;
        background-color: #bbf;
        margin:10px;
    }
    form input:focus{
        box-shadow: 0 0 10px #bbf;
    }
    form button{
        display: inline-block;
        margin: 10px;
        //float: right;
        height: 2em;
    }
@media only screen and (max-width: 800px) {
    form{
        width: 100%;
    }
    #messages{
        height: 350px;
    }
</style>
</head>
<body>
    <div id='welcome'>Welcome <?php print $user->name; ?><br><hr>To log out please click <a href="/hai/login/logout.php">HERE</a></div>

<form action="" method="post">
    <div id="messages"></div>
    <input type="text" name="input" id="input" placeholder="Type your message here">
    <button type="button" onclick="sendMessage()">Send</button>
</form>
<script>
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
</script>
<script>
    $("#input").keypress(function(e){
        if (e.which==13) {
            e.preventDefault();
            sendMessage();
        }
    });
    function sendMessage() {
        var message = $("#input").val();
        $.post("hai_respond.php", {"input":message}, showMe);
    }
    function showMe(data, textStatus) {
        $("#messages").html(data, textStatus);
        $("#input").val("");
        scrollDown();
    }
    function scrollDown() {
        $('#messages').stop().animate({
        scrollTop: $('#messages')[0].scrollHeight
        }, 800);
    }
</script>
</body>
</html>
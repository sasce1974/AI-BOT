<?php
/**
 * Created by PhpStorm.
 * User: Saso
 * Date: 4/4/2020
 * Time: 6:13 PM
 */

require_once("functions.php");
$user = new User;
if (!$user->isLoggedIn) {
    die(header("Location: login/"));
}

if($user->bad_words > 5){
    $user->logout();
    if (session_id() == "") {
        session_start();
    }
    $_SESSION['error'] = array();
    $_SESSION['error'][] = "You have been banned because of being rude!";

    print "<div class='message_bubble hai'>BYEBYE</div>";
    exit();
}


require_once "Respond.php";
$respond = new Respond();

if(isset($_POST['all_conversation'])){
    $conversation = $respond->getConversation();

    if(is_array($conversation)) {
        foreach ($conversation as $message) {
            foreach ($message as $item => $value) {
                print "<div class='message_bubble $item'>" . $value . "</div>";
            }
        }
    }else{
        print "<div class='message_bubble hai'>" . $conversation . "</div>";
    }
}


if(isset($_POST['input'])){
    $message = filter_input(INPUT_POST, 'input', FILTER_SANITIZE_STRING);
    $conversation = $respond->getAnswer($message);
    foreach ($conversation as $item=>$value){
        print "<div class='message_bubble $item squized'>" . $value . "</div>";
    }
}


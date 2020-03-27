<?php
/**
 * Created by PhpStorm.
 * User: Saso
 * Date: 3/25/2020
 * Time: 9:31 AM
 */

require_once("../functions.php");
$user = new User;


if(!$user->isLoggedIn){

    $new_guest_user_data = createGuestUser();

    if($new_guest_user_data && $user->registerUser($new_guest_user_data)){
        //login this user
        if($user->authenticate($new_guest_user_data['username'], $new_guest_user_data['password1'])){

            $_SESSION['isGuest'] = $user->isGuest = true;
            $_SESSION['message'][] = "You are signed in as a " . $user->username;
            header("Location:" . BASE_URL);
            exit();
        }else{
            $_SESSION['error'][] = "Cannot log in the guest!";
            header("Location:" . BASE_URL . "/login/");
            exit();
        }
    }else{
        $_SESSION['error'][] = "Error registering the guest user. Please try again.";
        header("Location:" . BASE_URL . "/login/");
        exit();
    }
}else{
    header("Location:" . BASE_URL);
    exit();
}



function createGuestUser(){
    //count guest users from user table to give the next guest number
    $con = connectPDO();
    $ip = $_SERVER['REMOTE_ADDR'];
    $q = "SELECT COUNT(id) FROM user WHERE ip = '$ip'";
    $query = $con->query($q);
    $users_from_same_ip = $query->fetchColumn();
    if($users_from_same_ip > 5 && ($ip != '127.0.0.1')){
        $_SESSION['error'][] = 'To many guest users from same address.';
        return false;
    }
    $next_number = rand(10, 100000);
    $data = array();
    $data['username'] = 'Guest_' . $next_number;
    $data['email'] = 'guest_' . $next_number . '@temp.com';
    $data['name'] = 'Guest_' . $next_number;
    $data['password1'] = 'noPassword';

    return $data;

    //how about the messages??? they should be deleted or not?


}




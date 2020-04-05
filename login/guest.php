<?php
/**
 * Created by PhpStorm.
 * User: Saso
 * Date: 3/25/2020
 * Time: 9:31 AM
 */

require_once("../functions.php");
require_once ("../Guest.php");

$user = new User;
$guest = new Guest();

//If there is no user logged in

if(!$user->isLoggedIn){

    //method createGuestUser creates and returns guest data @array|boolean

    $new_guest_user_data = $guest->createGuestUser();

    //if the user data is created and successfully registered...

    if($new_guest_user_data && $user->registerUser($new_guest_user_data)){

        //if the login of this user is successful...

        if($user->authenticate($new_guest_user_data['username'], $new_guest_user_data['password1'])){

            //initialize User isGuest property as true and redirect to index.php ...

            $_SESSION['isGuest'] = $user->isGuest = true;
            $_SESSION['message'][] = "You are signed in as a " . $user->username;
            header("Location:" . BASE_URL);
            exit();
        }else{

            //redirect to login if not successful authentication.
            //this should not happen normally...

            $_SESSION['error'][] = "Cannot log in the guest!";
            header("Location:" . BASE_URL . "/login/");
            exit();
        }
    }else{

        //redirect to register again if not successful registration...
        //this might happen in case no connection to database.

        $_SESSION['error'][] = "Error registering the guest user. Please try again.";
        header("Location:" . BASE_URL . "/login/");
        exit();
    }
}else{

    //if user is already logged in, just redirect directly to index.php

    header("Location:" . BASE_URL);
    exit();
}




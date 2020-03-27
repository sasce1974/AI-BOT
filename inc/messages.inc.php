<?php
/**
 * Created by PhpStorm.
 * User: Saso
 * Date: 3/26/2020
 * Time: 9:29 PM
 */


    if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
        print "<div class='message error'>";
        print "We are sorry, there was some error: <br> \n";
        foreach ($_SESSION['error'] as $error) {
            print $error . "<br> \n";
        } //end foreach
        print "</div>";
        unset($_SESSION['error']);
    } //end if
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        print "<div class='message'>";
        foreach ($_SESSION['message'] as $message) {
            print $message;
        }
        print "</div>";
        unset($_SESSION['message']);
    } //end if

<?php
/**
 * Created by PhpStorm.
 * User: Saso
 * Date: 3/28/2020
 * Time: 4:52 AM
 */

class Guest extends User
{

    /**
     * function createGuestUser() first checks if there are more than
     * 5 users already from same IP address (as percussion from bot attacks)
     * and then creates array of guest user data with: username, mokup email
     * name and password.
     * @return array|bool
     */
    public function createGuestUser(){
        //count guest users from user table with same IP address

        $ip = $_SERVER['REMOTE_ADDR'];
        $q = "SELECT COUNT(id) FROM user WHERE ip = '$ip'";
        $query = $this->con->query($q);
        $users_from_same_ip = $query->fetchColumn();
        if($users_from_same_ip > 5 && ($ip != '127.0.0.1')){
            $_SESSION['error'][] = 'To many guest users from same address.';
            return false;
        }
        $next_number = rand(1, 10000);
        $data = array();
        $data['username'] = 'Guest_' . $next_number;
        $data['email'] = 'guest_' . $next_number . '@temp.com';
        $data['name'] = 'Guest_' . $next_number;
        $data['password1'] = uniqid('guest');

        return $data;

    }
}
<?php
session_start();
require_once("dbcon.php");
require_once("UserClass.php");

function connectPDO(){
    try {
        $con = new PDO('mysql:host=' . DBHOST . ';dbname=' . DB, DBUSER, DBPASS);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $con;
    }catch (PDOException $e){
        error_message("We are sorry, there was some error in the process. 
        Please try again or wait for our personal to fix this issue." ,
            "Error in PDO connection: " . $e->getMessage() . " on line: " . $e->getLine());
        return false;
    }
}

function connectMysqli(){
    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
    return $mysqli;
}


?>
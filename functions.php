<?php
session_start();
require_once("dbcon.php");
require_once("UserClass.php");

function connectPDO(){
    $con = new PDO('mysql:host=' . DBHOST .';dbname=' . DB, DBUSER, DBPASS);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $con;
}

function connectMysqli(){
    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
    return $mysqli;
}


?>
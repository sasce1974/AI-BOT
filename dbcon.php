<?php


# **** SETTINGS **** #

//Errors will be emailed here:
$contact_email = "sasce1974@gmail.com";

//Determine local or real server:
$host = substr($_SERVER['HTTP_HOST'], 0, 5);
if(in_array($host, array('127.0', '192.1', 'local', 'hai.t'))){
    $local = true;
    $debug = true;
}else{
    $local = false;
}

//Determine location of files and URL in site
if($local){
    define('BASE_URI', 'C:\xampp\htdocs\hai\\');
    define('BASE_URL', 'http://hai.test');
    define ("DBHOST", "localhost");
    define ("DBUSER", "root");
    define ("DBPASS", "qSmU9JdK3kdx4W2");
    define ("DB", "hai");

    ini_set('display_errors', 1);
}else{

    define('BASE_URI', '/home/vol11_5/ezyro.com/ezyro_18927646/3delacto.com/htdocs/projects/hai/');
    define('BASE_URL', 'https://www.3delacto.com/projects/hai');
    define ("DBHOST", "sql307.ezyro.com");
    define ("DBUSER", "ezyro_18927646");
    define ("DBPASS", "Rusimka1944!");
    define ("DB", "ezyro_18927646_hia");

    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}


function isLocal(){
    global $local;
    return $local;
}


//Error management
if(!isset($debug)) $debug = false;


if(!isset($_SESSION['error'])) $_SESSION['error'] = array();

function my_error_handler($e_number, $e_message, $e_file, $e_line, $e_vars){
    global $local, $contact_email;
    //	Build	the	error	message:
    $message = "An error occurred in file " . $e_file . " on line " . $e_line . ", " . $e_message;
    //Append $e_vars to	the	$message:
    //$message .= print_r($e_vars, 1);

    if ($local){	//	Show	the	error.

        //debug_print_backtrace();
        error_log($message);
        $_SESSION['error'] = $message;
        header("Location: " . BASE_URL . "/error.php");
        exit();

    }else{
        //	Log	the	error:
        error_log ($message, 1, $contact_email);	//	Send email.

        //	Only print an error message if the error isn't a notice or strict.
        if(($e_number != E_NOTICE) && ($e_number < 2048)) {
            header("Location: " . BASE_URL . "/error.php?error_message=A system error occurred. We apologize for the inconvenience.");
            exit();
        }
    }//	End	of	$local	IF.

}//	End	of my_error_handler()	definition.

//	Use	my	error	handler:
set_error_handler('my_error_handler');

function error_message($user_message, $admin_message){
    if(!isset($_SESSION['error'])) $_SESSION['error'] = array();
    global $debug;
    if($debug){
        error_log($admin_message);
        $_SESSION['error'] = $admin_message;
        header("Location: " . BASE_URL . "/error.php");
        exit(404);
    }else{
        $_SESSION['error'] = $user_message;
        header("Location: " . BASE_URL . "/error.php");
        exit(404);
    }
}


?>
<?php


# **** SETTINGS **** #

//Errors will be emailed here:
$contact_email = "your_email_here@gmail.com";

//Determine local or real server:
$host = substr($_SERVER['HTTP_HOST'], 0, 5);
if(in_array($host, array('127.0', '192.1', 'local'))){
    $local = true;
    $debug = true;
}else{
    $local = false;
}

//Determine location of files and URL in site
if($local){
    define('BASE_URI', 'C:\path\to\xampp\htdocs\project\\');
    define('BASE_URL', 'localhost');
    define ("DBHOST", "localhost");
    define ("DBUSER", "user");
    define ("DBPASS", "your_mysql_password");
    define ("DB", "database_name");

    ini_set('display_errors', 1);
}else{

    define('BASE_URI', '/path/to/your/project/on/the/production/server/');
    define('BASE_URL', 'www.yourdomain.com');
    define ("DBHOST", "database-host.com");
    define ("DBUSER", "username");
    define ("DBPASS", "your_password");
    define ("DB", "database_name");

    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}





//Error management
if(!isset($debug)) $debug = false;


function my_error_handler($e_number, $e_message, $e_file, $e_line, $e_vars){
    global $local, $contact_email;
    //	Build	the	error	message:
    $message = "An error occurred in file " . $e_file . " on line " . $e_line . ", " . $e_message;


    if ($local){	//	Show the error message.

        header("Location: " . BASE_URL . "/error.php?error_message={$message}");
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
    global $debug;
    if($debug){
        header("Location: " . BASE_URL . "/error.php?error_message=" . $user_message . ". Technical info: " . $admin_message);
        exit(404);
    }else{
        header("Location: " . BASE_URL . "/error.php?error_message=" . $user_message);
        exit(404);
    }
}


?>
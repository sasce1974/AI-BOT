<?php
try{
class User {
    public $id;
    public $username;
    public $email;
    public $name;
    public $date_created;
    public $ip;
    public $isLoggedIn = false;

    public $isGuest = false;

    public $errorType = "fatal";

    
    function __construct() {
        if (session_id() == "") {
            session_start();
        }
        if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] == true) {
            $this->_initUser();
        }
    } //end __construct
    
    public function authenticate($user, $pass) {
        if (session_id() == "") {
            session_start();
        }
        $_SESSION['isLoggedIn'] = false;
        $this->isLoggedIn = false;
        
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
        if ($mysqli->connect_errno) {
            //error_message("There is some technical problem at the moment. Please try again.", "Cannot connect to MySQL: " . $mysqli->connect_error);
            error_log("Cannot connect to MySQL: " . $mysqli->connect_error);
            return false;
        }
        $safeUser = $mysqli->real_escape_string($user);
        $incomingPassword = $mysqli->real_escape_string($pass);
        $query = "SELECT * from user WHERE username = '{$safeUser}'";
        if (!$result = $mysqli->query($query)) {
            //error_message("There is no user " . $user,"Cannot retrieve account for {$user}");
            error_log("Cannot retrieve account for {$user}");
            return false;
        }
        // Will be only one row, so no while() loop needed
        $row = $result->fetch_assoc();
        $dbPassword = $row['password'];
        if (password_verify($incomingPassword,$dbPassword)==false) {
            //error_message("Wrong password.", "Wrong password for {$user}");
            error_log("Passwords for {$user} don't match");
            return false;
        }
        $this->id = $row['id'];
        $this->username = $row['username'];
        $this->email = $row['email'];
        $this->date_created = $row['date_created'];
        $this->name = $row['name'];
        $this->ip = $row['ip'];
        $this->isLoggedIn = true;

        $this->_setSession();
        return true;
    } //end function authenticate
    
     private function _setSession() {
        if (session_id() == '') {
            session_start();
        }
        $_SESSION['id'] = $this->id;
        $_SESSION['username'] = $this->username;
        $_SESSION['email'] = $this->email;
        $_SESSION['date_created'] = $this->date_created;
        $_SESSION['name'] = $this->name;
        $_SESSION['ip'] = $this->ip;
        $_SESSION['isLoggedIn'] = $this->isLoggedIn;
        $_SESSION['isGuest'] = $this->isGuest;
    } //end function setSession
    
    private function _initUser() {
        if (session_id() == '') {
            session_start();
        }
        $this->id = $_SESSION['id'];
        $this->username = $_SESSION['username'];
        $this->email = $_SESSION['email'];
        $this->date_created = $_SESSION['date_created'];
        $this->name = $_SESSION['name'];
        $this->ip = $_SESSION['ip'];
        $this->isLoggedIn = $_SESSION['isLoggedIn'];
        $this->isGuest = $_SESSION['isGuest'];
    } //end function initUser
    
    public function logout() {
        $this->isLoggedIn = false;
        if (session_id() == "") {
            session_start();
        }
        $_SESSION['isLoggedIn'] = false;
        foreach ($_SESSION as $key => $value) {
            $_SESSION[$key] = "";
            unset($_SESSION[$key]);
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $cookieParameters = session_get_cookie_params();
            setcookie(session_name(), '', time() - 28800,
            $cookieParameters['path'], $cookieParameters['domain'],
            $cookieParameters['secure'], $cookieParameters['httponly']);
        } //end if
        session_destroy();
        if($this->isGuest === true){
            $this->unregisterMe();
        }


    } //end function logout
    
    public function emailPass($user) {
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
        if ($mysqli->connect_errno) {
                error_log("Cannot connect to MySQL: " . $mysqli->connect_error);
                return false;
        }
        // first, lookup the user to see if they exist.
        $safeUser = $mysqli->real_escape_string($user);
        $query = "SELECT id, username, email FROM user WHERE username = '{$safeUser}'";
        if (!$result = $mysqli->query($query)) {
            $_SESSION['error'][] = "Unknown Error";
            return false;
        }
        if ($result->num_rows == 0) {
            $_SESSION['error'][] = "User not found";
            return false;
        }
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $hash = uniqid("",TRUE);
        $safeHash = $mysqli->real_escape_string($hash);
        $insertQuery = "INSERT INTO resetPassword (user_id, pass_key, date_created, status) VALUES
        ('{$id}', '{$safeHash}', NOW(), 'A')";
        if (!$mysqli->query($insertQuery)) {
            error_log("Problem inserting resetPassword row for " . $id);
            $_SESSION['error'][] = "Unknown problem";
            return false;
        }
        $urlHash = urlencode($hash);
        $domain = $_SERVER['HTTP_HOST'];
        $site = "/hai/reset";
        $resetPage = "/index.php";
        $fullURL = $domain . $site . $resetPage . "?user=" . $urlHash;
        //set up things related to the e-mail
        $to = $row['email'];
        $subject = "Password Reset for Site";
        $message = "Password reset requested for this site.\r\n\r\n";
        $message .= "Please go to this link to reset your password:\r\n";
        $message .= $fullURL;
        $headers = "From: 3delacto.com\r\n";
        mail($to,$subject,$message,$headers);
        return true;
    } //end function emailPass
    
    public function validateReset($formInfo) {
        $pass1 = $formInfo['password1'];
        $pass2 = $formInfo['password2'];
        if ($pass1 != $pass2) {
            $this->errorType = "nonfatal";
            $_SESSION['error'][] = "Passwords don't match";
            return false;
        }
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
        if ($mysqli->connect_errno) {
                error_log("Cannot connect to MySQL: " . $mysqli->connect_error);
                return false;
        }
        $decodedHash = urldecode($formInfo['hash']);
        $safeEmail = $mysqli->real_escape_string($formInfo['email']);
        $safeHash = $mysqli->real_escape_string($decodedHash);
        $query = "SELECT u.id as id, u.email as email FROM user u, resetPassword r WHERE " .
            "r.status = 'A' AND r.pass_key = '{$safeHash}' " .
            " AND u.email = '{$safeEmail}' " .
            " AND u.id = r.user_id";
        if (!$result = $mysqli->query($query)) {
            $_SESSION['error'][] = "Unknown Error";
            $this->errorType = "fatal";
            error_log("database error: " . $formInfo['email'] . " - " . $formInfo['hash']);
            return false;
        } else if ($result->num_rows == 0) {
            $_SESSION['error'][] = "Link not active or user not found";
            $this->errorType = "fatal";
            error_log("Link not active: " . $formInfo['email'] . " - " . $formInfo['hash']);
            return false;
        } else {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            if ($this->_resetPass($id, $pass1)) {
                return true;
            } else {
                $this->errorType = "nonfatal";
                $_SESSION['error'][] = "Error resetting password";
                error_log("Error resetting password: " . $id);
                return false;
            }
        }
    } //end function validateReset
    
    private function _resetPass($id, $pass) {
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
        if ($mysqli->connect_errno) {
            error_log("Cannot connect to MySQL: " . $mysqli->connect_error);
            return false;
        }
        $safeUser = $mysqli->real_escape_string($id);
        $newPass = password_hash($pass, PASSWORD_DEFAULT);
        $safePass = $mysqli->real_escape_string($newPass);
        $query = "UPDATE user SET password = '{$safePass}' WHERE id = '{$safeUser}'";
        if (!$mysqli->query($query)) {
            return false;
        } else {
            return true;
        }
    } //end function _resetPass

    //function @registerUser takes the array $_POST as argument from the register-process.php
    //or array provided by when registering a guest user (from a main - index page)
    function registerUser(array $userData) {
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DB);
        if ($mysqli->connect_errno) {
            error_log("Cannot connect to MySQL: " . $mysqli->connect_error);
            return false;
        }
        //$username = $mysqli->real_escape_string($_POST["username"]);
        $username = $mysqli->real_escape_string($userData["username"]);

//check for an existing username
        $findUser = "SELECT id FROM user WHERE username = '{$username}'";
        $findResult = $mysqli->query($findUser);
        $findRow = $findResult->fetch_assoc();
        if (isset($findRow['id']) && $findRow['id'] != "") {
            $_SESSION["error"][] = "Account with the same username already exist.";
            return false;
        }
        $findUser = $findResult = $findRow = null;
//        $email = $mysqli->real_escape_string($_POST["email"]);
        $email = $mysqli->real_escape_string($userData["email"]);
//check for an existing email
        $findUser = "SELECT id FROM user WHERE email = '{$email}'";
        $findResult = $mysqli->query($findUser);
        $findRow = $findResult->fetch_assoc();
        if (isset($findRow['id']) && $findRow['id'] != "") {
            $_SESSION["error"][] = "Account with the same e-mail already exist.";
            return false;
        }
        $findUser = $findResult = $findRow = null;
//        if (isset($_POST["name"])) {
        if (isset($userData["name"])) {
//            $name = $mysqli->real_escape_string($_POST["name"]);
//            $name = $mysqli->real_escape_string($userData["name"]);
            $name = filter_var($userData["name"], FILTER_SANITIZE_STRING);
        } else {
            $name = "Anonymous_user no: " . rand(1000000, 9999999);
        }

        //check for an existing name
        $findUser = "SELECT id FROM user WHERE name = '{$name}'";
        $findResult = $mysqli->query($findUser);
        $findRow = $findResult->fetch_assoc();
        if (isset($findRow['id']) && $findRow['id'] != "") {
            $_SESSION["error"][] = "Account with the same full name already exist.";
            return false;
        }
//        $cryptedPassword = password_hash($_POST["password1"], PASSWORD_DEFAULT);
        $cryptedPassword = password_hash($userData["password1"], PASSWORD_DEFAULT);
        $password = $mysqli->real_escape_string($cryptedPassword);

        $today = date('d.m.Y');
        $ip = $_SERVER['REMOTE_ADDR'];

        $query = "INSERT INTO user (id, username, password, email, name, date_created, ip) VALUES
    (Null, '{$username}', '{$password}', '{$email}', '{$name}', NOW(), '{$ip}')";
        if ($mysqli->query($query)) {
            $id = $mysqli->insert_id;
            $this->authenticate($username, $userData['password1']);
            return true;
        } else {
            error_log("Problem inserting {$query}");
            return false;
        }
    } //end function registerUser



    function unregisterMe(){
        $con = connectPDO();
        $query = $con->prepare("DELETE FROM user WHERE id = ?");
        $query->execute(array($this->id));
        if($query->rowCount() === 1){

            //TODO Unlog me also

            $postquery = $con->prepare("INSERT INTO deleted (id_vezbac, ime, time) VALUES (?, ?, NOW())");
            $postquery->execute(array($this->id, $this->name));
            if($postquery->rowCount() === 1) {
                $_SESSION['message'] = "You have successfully unregistered.<br> \n You can logout now to complete the process.";
                $con = null;
                return true;
            }
            return false;
        } else {
            $con = null;
            $_SESSION['error'] = "Unregistering can not be completed at the moment, please try later.";
            return false;
        }
    } //end unregisterMe


    public function deleteOldGuestUsers(){

        $con = connectPDO();
        $q = "DELETE FROM user WHERE username LIKE 'Guest_%' AND (UNIX_TIMESTAMP(date_created) + 86400) <  UNIX_TIMESTAMP()";
        $query = $con->query($q);
        //$con = null;
        return $query->rowCount();

        //TODO What about if someone is logged in?
        //Need to check in database if the user exists...

    }

    public function checkIfUserExistInTable(){
        $con = connectPDO();
        $query = $con->query("SELECT COUNT(id) FROM user WHERE id = '$this->id'");
        $r = $query->fetchColumn();

        if(+$r === 1){
            return true;
        }else{
            $this->logout();
            return false;
        }
    }



} //end class User
}catch(Exception $e){
    error_message("We are sorry, there was some error in the process. 
    Please try again or wait for our personal to fix this issue." ,
        "Error in User class: " . $e->getMessage() . " on line: " . $e->getLine());

}
?>
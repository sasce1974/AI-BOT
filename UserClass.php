<?php

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

    private $con = null;

    
    function __construct() {
        if (session_id() == "") {
            session_start();
        }

        $this->con = connectPDO();

        if(!isset($_SESSION['error'])) $_SESSION['error'] = array();

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


        $safeUser = filter_var($user, FILTER_SANITIZE_STRING);
        //$incomingPassword = $mysqli->real_escape_string($pass);
        $query = "SELECT * from user WHERE username = '{$safeUser}'";
        $result = $this->con->query($query);
        if ($result->rowCount() !== 1) {
            error_log("Cannot retrieve account for {$user}");
            return false;
        }

        // Will be only one row, so no while() loop needed
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $dbPassword = $row['password'];
        if (password_verify($pass,$dbPassword)==false) {
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
    
    public function emailPass($email) {
        // first, lookup the user to see if they exist.
        $email = filter_var($email, FILTER_SANITIZE_STRING);
        $query = "SELECT id, username, email FROM user WHERE email = ?";
        $result = $this->con->prepare($query);
        $result->execute(array($email));

        if ($result->rowCount() !== 1) {
            $_SESSION['error'][] = "User with this email not found";
            return false;
        }
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $id = $row['id'];
        $hash = uniqid("",TRUE);
        $safeHash = filter_var($hash, FILTER_SANITIZE_STRING);

        $insertQuery = "INSERT INTO resetPassword (user_id, pass_key, date_created, status) VALUES
        (?, ?, NOW(), 'A')";
        $query = $this->con->prepare($insertQuery);
        $query->execute(array($id, $safeHash));

        if ($query->rowCount() !== 1) {
            error_log("Problem inserting resetPassword row for " . $id);
            $_SESSION['error'][] = "Unknown problem";
            return false;
        }
        $urlHash = urlencode($hash);
        $domain = $_SERVER['HTTP_HOST'];
        if(isLocal()) {
            $site = "/reset";
        }else{
            $site = "/projects/hai/reset";
        }
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

        $decodedHash = urldecode($formInfo['hash']);
        $safeEmail = filter_var($formInfo['email'], FILTER_SANITIZE_EMAIL);
        //$safeHash = $mysqli->real_escape_string($decodedHash);
        $query = "SELECT u.id as id, u.email as email FROM user u, resetPassword r WHERE 
              r.status = 'A' AND r.pass_key = ? AND u.email = ? AND u.id = r.user_id";

        $q = $this->con->prepare($query);
        $q->execute(array($decodedHash, $safeEmail));

        if ($q->rowCount() === 0) {
            $_SESSION['error'][] = "Link not active or user not found";
            $this->errorType = "fatal";
            error_log("Link not active: " . $formInfo['email'] . " - " . $formInfo['hash']);
            return false;
        } else {
            $row = $q->fetch(PDO::FETCH_ASSOC);
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

        $safeUser = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $newPass = password_hash($pass, PASSWORD_DEFAULT);

        $query = "UPDATE user SET password = ? WHERE id = ?";
        $q = $this->con->prepare($query);
        $q->execute(array($newPass, $safeUser));

        if ($q->rowCount() !== 1) {
            return false;
        } else {
            return true;
        }
    } //end function _resetPass

    //function @registerUser takes the array $_POST as argument from the register-process.php
    //or array provided by when registering a guest user (from a main - index page)
    function registerUser(array $userData) {

        $username = filter_var($userData["username"], FILTER_SANITIZE_STRING);

//check for an existing username
        $findUser = "SELECT id FROM user WHERE username = ?";
        $q = $this->con->prepare($findUser);
        $q->execute(array($username));

        $findRow = $q->fetch(PDO::FETCH_ASSOC);
        if (isset($findRow['id']) && $findRow['id'] != "") {
            $_SESSION["error"][] = "Account with the same username already exist.";
            return false;
        }

        $email = filter_var($userData['email'], FILTER_SANITIZE_EMAIL);
//check for an existing email
        $findUser = "SELECT id FROM user WHERE email = ?";
        $findResult = $this->con->prepare($findUser);
        $findResult->execute(array($email));

        $findRow = $findResult->fetch(PDO::FETCH_ASSOC);
        if (isset($findRow['id']) && $findRow['id'] != "") {
            $_SESSION["error"][] = "Account with the same e-mail already exist.";
            return false;
        }


        if (isset($userData["name"])) {
            $name = filter_var($userData["name"], FILTER_SANITIZE_STRING);
        } else {
            $name = "Anonymous_user no: " . rand(1000000, 9999999);
        }

        //check for an existing name
        $findUser = "SELECT id FROM user WHERE name = ?";
        $findResult = $this->con->prepare($findUser);
        $findResult->execute(array($name));

        $findRow = $findResult->fetch(PDO::FETCH_ASSOC);
        if (isset($findRow['id']) && $findRow['id'] != "") {
            $_SESSION["error"][] = "Account with the same full name already exist.";
            return false;
        }

        $password = password_hash($userData["password1"], PASSWORD_DEFAULT);


        $ip = $_SERVER['REMOTE_ADDR'];

        $query = "INSERT INTO user (id, username, password, email, name, date_created, ip) 
                  VALUES(Null, ?, ?, ?, ?, NOW(), ?)";

        $r = $this->con->prepare($query);
        $r->execute(array($username, $password, $email, $name, $ip));

        if ($r->rowCount() === 1) {
            $this->authenticate($username, $userData['password1']);
            return true;
        } else {
            error_log("Problem inserting {$query}");
            return false;
        }
    } //end function registerUser



    function unregisterMe(){

        $query = $this->con->prepare("DELETE FROM user WHERE id = ?");
        $query->execute(array($this->id));
        if($query->rowCount() === 1){

            $postquery = $this->con->prepare("INSERT INTO deleted (id_vezbac, ime, time) VALUES (?, ?, NOW())");
            $postquery->execute(array($this->id, $this->name));
            if($postquery->rowCount() === 1) {
                $_SESSION['message'] = "You have successfully unregistered.";
                $this->logout();
                return true;
            }
            return false;
        } else {
            $_SESSION['error'][] = "Unregistering can not be completed at the moment, please try later.";
            return false;
        }
    } //end unregisterMe


    public function deleteOldGuestUsers($time = 86400){
        $q = "DELETE FROM user WHERE username LIKE 'Guest_%' AND (UNIX_TIMESTAMP(date_created) + $time) <  UNIX_TIMESTAMP()";
        $query = $this->con->query($q);
        return $query->rowCount();
    }

    public function checkIfUserExistInTable(){
        $query = $this->con->query("SELECT COUNT(id) FROM user WHERE id = '$this->id'");
        $r = $query->fetchColumn();

        if(+$r === 1){
            return true;
        }else{
            $this->logout();
            return false;
        }
    }


    function __destruct()
    {
        $this->con = null;
    }

} //end class User

?>
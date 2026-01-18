<?php
       
session_start();

include_once "../utils/database.php";


// for debugging
//echo "<pre>GET Content: " . print_r($_GET, true) . "</pre>";
//echo "<pre>POST Content: " . print_r($_POST, true) . "</pre>";    
// echo "<pre>Server Vars: " . print_r($_SERVER, true) . "</pre>";
//echo "<pre>SESSION Content: " . print_r($_SESSION, true) . "</pre>";

$loginErrMsg = "";

// somebody clicked login button?
if (isset($_POST["login"])) {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    $user = login($email, $password);

    //echo "<pre>Returned User Object: " . print_r($user) . "</pre>";

    if ($user != null) {

        //echo "<pre>Login successfull: " . print_r($user, true) . "</pre>";

        $_SESSION["uid"] = $user->id; 
        $_SESSION["email"] = $user->email;
        $_SESSION["name"] = $user->username;
        $_SESSION["permission"] = $user->permission;
        // ... 
        
    } else {
        $loginErrMsg = "Invalid Credentials!";
    }
}


if (isset($_POST["logout"])) {
    $_SESSION = [];
    session_destroy();
}
<?php
require_once 'config.php';

// logout user
if (session_status() === PHP_SESSION_ACTIVE || is_authenticated()) {
    // empty session
    $_SESSION = [];

    // delete browser session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // kill session
    session_destroy();
}

// go to login
header("Location: login.php");
exit();
?>

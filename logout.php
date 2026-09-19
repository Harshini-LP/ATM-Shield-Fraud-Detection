<?php
// 🛠️ நமது புதிய config.php-ஐ இணைக்கிறோம் (அதில் ஏற்கனவே ob_start மற்றும் session_start உள்ளது)
require_once 'config.php';

// 1. Clear all session variables stored in memory
$_SESSION = array();

// 2. Destroy the session cookie in the user's browser if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Completely destroy the session on the web server hosting environment
session_destroy();

// 4. Force safe redirection path route straight back to your ATM Shield home login gate
header("Location: index.php");
exit();
?>

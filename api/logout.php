<?php
require_once 'session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');

// Unset all of the session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear the remember me flag cookie
$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
setcookie('remember_me_flag', '', time() - 42000, '/', '', $is_https, true);

// Finally, destroy the session.
session_destroy();

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Logout successful.']);
?>

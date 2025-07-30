<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php'; // For the debug_log function

debug_log('--- check_session.php ---');
debug_log($_SESSION);

if (isset($_SESSION['user_id'])) {
    // User is logged in
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'loggedIn' => true,
        'user' => [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'subscription_status' => $_SESSION['subscription_status']
        ]
    ]);
} else {
    // User is not logged in
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'loggedIn' => false,
        'message' => 'User is not authenticated.'
    ]);
}
?>

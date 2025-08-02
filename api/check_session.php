<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php'; // For the debug_log function

debug_log('--- check_session.php ---');
debug_log($_SESSION);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT tour_completed, last_topic, last_card_index FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_details = $result->fetch_assoc();
    if (!$user_details) {
        $user_details = [];
    }

    // User is logged in
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'loggedIn' => true,
        'user' => array_merge([
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'subscription_status' => $_SESSION['subscription_status']
        ], $user_details)
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

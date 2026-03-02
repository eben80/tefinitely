<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'loggedIn' => true,
    'user' => [
        'user_id' => 1,
        'first_name' => 'Test',
        'role' => 'user',
        'subscription_status' => 'inactive'
    ]
]);
?>

<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'session_init.php';
require_once '../db/db_config.php';

/**
 * api/google_login.php
 * Handles Google One Tap / Sign-In credential response.
 */

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['credential'])) {
        throw new Exception('Missing Google credential.', 400);
    }

    $id_token = $data['credential'];

    // In production, GOOGLE_CLIENT_ID should be defined in db_config.php
    $client_id = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'YOUR_GOOGLE_CLIENT_ID';

    $client = new Google_Client(['client_id' => $client_id]);
    $payload = $client->verifyIdToken($id_token);

    if (!$payload) {
        throw new Exception('Invalid Google ID token.', 401);
    }

    $email = $payload['email'];
    $first_name = $payload['given_name'] ?? 'Google';
    $last_name = $payload['family_name'] ?? 'User';
    $google_id = $payload['sub'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, first_name, role, subscription_status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User exists, log them in
        $user = $result->fetch_assoc();
    } else {
        // User doesn't exist, create a new verified account
        $stmt_insert = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, email_verified) VALUES (?, ?, ?, 'GOOGLE_AUTH_USER', TRUE)");
        $stmt_insert->bind_param("sss", $first_name, $last_name, $email);
        $stmt_insert->execute();

        $new_id = $conn->insert_id;
        $user = [
            'id' => $new_id,
            'first_name' => $first_name,
            'role' => 'user',
            'subscription_status' => 'inactive'
        ];
    }

    // Start session
    init_session(true); // Default to remember me for social login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['subscription_status'] = $user['subscription_status'];

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful via Google.',
        'user' => [
            'first_name' => $user['first_name'],
            'role' => $user['role'],
            'subscription_status' => $user['subscription_status']
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>

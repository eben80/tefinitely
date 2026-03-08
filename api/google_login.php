<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'session_init.php';
require_once '../db/db_config.php';
require_once '../vendor/autoload.php';

// Check for Google Client ID in config, but since I can't see it, I'll assume it's defined or needs to be.
// For the purpose of this task, I'll use a placeholder if not found.
$google_client_id = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '1032401011225-pcjeocvpdigthv15u1qu1hmv8p61cuc0.apps.googleusercontent.com';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['credential'])) {
        throw new Exception('Missing Google credential.', 400);
    }

    $id_token = $data['credential'];

    $client = new Google_Client(['client_id' => $google_client_id]);
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $google_id = $payload['sub'];
        $email = $payload['email'];
        $first_name = $payload['given_name'] ?? 'User';
        $last_name = $payload['family_name'] ?? '';

        // Check if user exists by email
        $stmt = $conn->prepare("SELECT id, first_name, role, subscription_status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Existing user, log them in
            $user = $result->fetch_assoc();

            // Mark email as verified if it wasn't already (since Google verified it)
            $update_stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();

        } else {
            // New user, register them
            $hashed_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Random password
            $email_verified = 1;

            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, email_verified) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashed_password, $email_verified);

            if (!$insert_stmt->execute()) {
                throw new Exception('Failed to register user via Google.');
            }

            $user_id = $insert_stmt->insert_id;
            $insert_stmt->close();

            $user = [
                'id' => $user_id,
                'first_name' => $first_name,
                'role' => 'user',
                'subscription_status' => 'inactive'
            ];
        }

        // Start session
        init_session(true); // Default to remember me for Google login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['subscription_status'] = $user['subscription_status'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => [
                'first_name' => $user['first_name'],
                'role' => $user['role'],
                'subscription_status' => $user['subscription_status']
            ]
        ]);

    } else {
        throw new Exception('Invalid ID token.', 401);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

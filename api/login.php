<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php';

// --- Main Logic ---
try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    // Get the posted data
    $data = json_decode(file_get_contents('php://input'), true);

    // Basic validation
    if (!$data || !isset($data['username']) || !isset($data['password'])) {
        throw new Exception('Missing username or password.', 400);
    }

    $username = trim($data['username']);
    $password = trim($data['password']);

    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required.', 400);
    }

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT id, username, password, role, subscription_status FROM users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['subscription_status'] = $user['subscription_status'];

            debug_log('Login successful. Session data set for user: ' . $username);
            debug_log($_SESSION);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'user' => [
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'subscription_status' => $user['subscription_status']
                ]
            ]);
        } else {
            // Invalid password
            throw new Exception('Invalid username or password.', 401);
        }
    } else {
        // User not found
        throw new Exception('Invalid username or password.', 401);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the actual error
    debug_log("Login Error: " . $e->getMessage());

    // Send a generic, valid JSON error response
    $http_code = ($e->getCode() > 0) ? $e->getCode() : 500;
    http_response_code($http_code);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage() // Or a generic message like 'An internal error occurred.'
    ]);
}
?>

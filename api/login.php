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

    // Get the posted data from $_POST
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        throw new Exception('Missing email or password.', 400);
    }

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required.', 400);
    }

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT id, first_name, password, role, subscription_status FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['subscription_status'] = $user['subscription_status'];

            // debug_log('Login successful. Session data set for user: ' . $email);
            // debug_log($_SESSION);

            http_response_code(200);
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
            // Invalid password
            throw new Exception('Invalid email or password.', 401);
        }
    } else {
        // User not found
        throw new Exception('Invalid email or password.', 401);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the actual error
    // debug_log("Login Error: " . $e->getMessage());

    // Send a generic, valid JSON error response
    $http_code = ($e->getCode() > 0) ? $e->getCode() : 500;
    http_response_code($http_code);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage() // Or a generic message like 'An internal error occurred.'
    ]);
}
?>

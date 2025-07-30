<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!$data || !isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing username or password.']);
    exit;
}

$username = trim($data['username']);
$password = trim($data['password']);

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
    exit;
}

// Fetch user from the database
$stmt = $conn->prepare("SELECT id, username, password, role, subscription_status FROM users WHERE username = ?");
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
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    }
} else {
    // User not found
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
}

$stmt->close();
$conn->close();
?>

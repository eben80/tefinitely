<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all users
    $result = $conn->query("SELECT id, username, email, role, subscription_status, created_at FROM users");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    http_response_code(200);
    echo json_encode(['status' => 'success', 'users' => $users]);
} elseif ($method === 'POST') {
    // Update a user's subscription status
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['user_id']) || !isset($data['subscription_status'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing user_id or subscription_status.']);
        exit;
    }

    $user_id = $data['user_id'];
    $status = $data['subscription_status'];

    if (!in_array($status, ['active', 'inactive'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid status value.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET subscription_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $user_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'User status updated successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user status.']);
    }
    $stmt->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: User must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to update your email.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'New email address is required.']);
    exit;
}

$new_email = trim($data['email']);

// Validate email format
if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

try {
    // Check if the new email is already taken by another user
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt_check->bind_param("si", $new_email, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'This email address is already in use.']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Update the user's email
    $stmt_update = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt_update->bind_param("si", $new_email, $user_id);

    if ($stmt_update->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Email updated successfully.']);
    } else {
        throw new Exception("Failed to update email.");
    }
    $stmt_update->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
}

$conn->close();
?>

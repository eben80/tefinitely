<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: User must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to update your password.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['current_password']) || !isset($data['new_password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Current and new passwords are required.']);
    exit;
}

$current_password = $data['current_password'];
$new_password = $data['new_password'];

if (strlen($new_password) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'New password must be at least 8 characters long.']);
    exit;
}

try {
    // 1. Fetch the user's current hashed password
    $stmt_fetch = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    if ($result->num_rows !== 1) {
        throw new Exception("User not found.", 404);
    }
    $user = $result->fetch_assoc();
    $stored_hash = $user['password'];
    $stmt_fetch->close();

    // 2. Verify the current password
    if (!password_verify($current_password, $stored_hash)) {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
        exit;
    }

    // 3. Hash and update the new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt_update->bind_param("si", $new_hashed_password, $user_id);

    if ($stmt_update->execute()) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    } else {
        throw new Exception("Failed to update password.");
    }
    $stmt_update->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
}

$conn->close();
?>

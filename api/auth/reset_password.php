<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['token']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Token and new password are required.']);
    exit;
}

$token = $data['token'];
$new_password = $data['password'];

if (strlen($new_password) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

try {
    // 1. Find the token in the database
    $stmt_find = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt_find->bind_param("s", $token);
    $stmt_find->execute();
    $result = $stmt_find->get_result();

    if ($result->num_rows !== 1) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
        exit;
    }
    $reset_request = $result->fetch_assoc();
    $email = $reset_request['email'];
    $expires_at = strtotime($reset_request['expires_at']);
    $stmt_find->close();

    // 2. Check if the token has expired
    if (time() > $expires_at) {
        // Also delete the expired token
        $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt_delete->bind_param("s", $token);
        $stmt_delete->execute();
        $stmt_delete->close();

        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
        exit;
    }

    // 3. Hash the new password and update the user's record
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt_update->bind_param("ss", $hashed_password, $email);

    if (!$stmt_update->execute()) {
        throw new Exception("Failed to update password.");
    }
    $stmt_update->close();

    // 4. Delete the used token
    $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $stmt_delete->bind_param("s", $token);
    $stmt_delete->execute();
    $stmt_delete->close();

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Password has been reset successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
}

$conn->close();
?>

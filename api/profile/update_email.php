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

    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));

    // Update the user's pending_email and token
    $stmt_update = $conn->prepare("UPDATE users SET pending_email = ?, verification_token = ? WHERE id = ?");
    $stmt_update->bind_param("ssi", $new_email, $verification_token, $user_id);

    if ($stmt_update->execute()) {
        // Send verification email
        require_once __DIR__ . '/../services/EmailService.php';

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $verification_link = $protocol . $host . "/api/auth/verify_email.php?token=" . $verification_token;

        $subject = "Verify Your New Email Address - Tefinitely";
        $body_html = "<h1>Email Change Request</h1>
                      <p>You have requested to change your email address to <strong>{$new_email}</strong>. Please click the link below to verify this new email address:</p>
                      <p><a href='{$verification_link}'>{$verification_link}</a></p>
                      <p>If you did not request this change, please contact support.</p>";
        $body_text = "Email Change Request\n\nYou have requested to change your email address to {$new_email}. Please click the link below to verify this new email address:\n{$verification_link}\n\nIf you did not request this change, please contact support.";

        sendEmail($new_email, $subject, $body_html, $body_text);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'A verification email has been sent to your new email address. Please click the link in that email to confirm the change.']);
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

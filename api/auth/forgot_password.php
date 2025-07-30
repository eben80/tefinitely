<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email address is required.']);
    exit;
}

$email = trim($data['email']);

try {
    // 1. Check if user exists
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows !== 1) {
        // Don't reveal if an email is registered or not for security reasons.
        // Send a success response either way.
        echo json_encode(['status' => 'success', 'message' => 'If an account with that email exists, a password reset link has been sent.']);
        exit;
    }
    $stmt_check->close();

    // 2. Generate a secure token
    $token = bin2hex(random_bytes(32));

    // 3. Set expiration time (1 hour from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // 4. Store the token in the database
    $stmt_insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $email, $token, $expires_at);
    $stmt_insert->execute();
    $stmt_insert->close();

    // 5. !!! IMPORTANT - MOCKING EMAIL SENDING !!!
    // In a real application, you would use a mail library to send an email with a link like:
    // $reset_link = "https://ebski.co/tefinitely/reset_password.html?token=" . $token;
    // mail($email, "Password Reset Request", "Click here to reset your password: " . $reset_link);

    // For this implementation, we will return the token directly in the response for testing purposes.
    // This is INSECURE and should NOT be done in production.
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'If an account with that email exists, a password reset link has been sent.',
        '__debug_token' => $token // Exposing for testing
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
}

$conn->close();
?>

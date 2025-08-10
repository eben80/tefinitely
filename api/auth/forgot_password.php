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

    // 5. Send the password reset email
    require_once __DIR__ . '/../services/EmailService.php';

    // 6. Dynamically generate the reset link
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['PHP_SELF']);
    // Go up two directories from /api/auth to the root path
    $app_path = dirname(dirname($script_path));
    // Normalize to handle root install vs. subdirectory
    $app_path = ($app_path == '/' || $app_path == '\\') ? '' : $app_path;

    $reset_link = "{$protocol}://{$host}{$app_path}/reset_password.html?token=" . $token;
    $subject = "Password Reset Request";

    $body_html = "
        <h1>Password Reset Request</h1>
        <p>You are receiving this email because a password reset request was made for your account.</p>
        <p>Click the link below to reset your password:</p>
        <a href='{$reset_link}'>{$reset_link}</a>
        <p>If you did not request a password reset, you can safely ignore this email.</p>
    ";

    $body_text = "
        Password Reset Request\n
        You are receiving this email because a password reset request was made for your account.\n
        Click the link below to reset your password:\n
        {$reset_link}\n
        If you did not request a password reset, you can safely ignore this email.\n
    ";

    sendEmail($email, $subject, $body_html, $body_text);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'If an account with that email exists, a password reset link has been sent.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal error occurred.']);
}

$conn->close();
?>

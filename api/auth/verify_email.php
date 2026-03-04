<?php
require_once '../../db/db_config.php';

if (!isset($_GET['token'])) {
    die("Invalid request. Token is missing.");
}

$token = $_GET['token'];

// Check if token exists and find the user
$stmt = $conn->prepare("SELECT id, first_name, email, pending_email FROM users WHERE verification_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired verification token.");
}

$user = $result->fetch_assoc();
$user_id = $user['id'];
$pending_email = $user['pending_email'];

if ($pending_email) {
    // This was an email change request
    $stmt_update = $conn->prepare("UPDATE users SET email = ?, pending_email = NULL, verification_token = NULL, email_verified = TRUE WHERE id = ?");
    $stmt_update->bind_param("si", $pending_email, $user_id);
} else {
    // This was a new account registration
    $stmt_update = $conn->prepare("UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE id = ?");
    $stmt_update->bind_param("i", $user_id);
}

if ($stmt_update->execute()) {
    // Redirect to login page with success message
    header("Location: /login.html?verified=true");
} else {
    die("An error occurred during verification. Please try again later.");
}

$stmt_update->close();
$stmt->close();
$conn->close();
?>

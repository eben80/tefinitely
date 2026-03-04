<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!$data || !isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$first_name = trim($data['first_name']);
$last_name = trim($data['last_name']);
$email = trim($data['email']);
$password = trim($data['password']);

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'Email already taken.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Generate verification token
$verification_token = bin2hex(random_bytes(32));

// Insert the new user
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, verification_token) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $verification_token);

if ($stmt->execute()) {
    // Send verification email
    require_once __DIR__ . '/services/EmailService.php';

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $verification_link = $protocol . $host . "/api/auth/verify_email.php?token=" . $verification_token;

    $subject = "Verify Your Email - Tefinitely";
    $body_html = "<h1>Welcome, {$first_name}!</h1>
                  <p>Thank you for registering. Please click the link below to verify your email address:</p>
                  <p><a href='{$verification_link}'>{$verification_link}</a></p>
                  <p>If you did not create an account, no further action is required.</p>";
    $body_text = "Welcome, {$first_name}!\n\nThank you for registering. Please click the link below to verify your email address:\n{$verification_link}\n\nIf you did not create an account, no further action is required.";

    sendEmail($email, $subject, $body_html, $body_text);

    // Send notification to support
    $support_subject = "New User Registered: {$first_name} {$last_name}";
    $support_body_html = "<h1>New User Registration</h1>
                          <p>A new user has registered on Tefinitely:</p>
                          <ul>
                              <li><strong>Name:</strong> {$first_name} {$last_name}</li>
                              <li><strong>Email:</strong> {$email}</li>
                              <li><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</li>
                          </ul>";
    $support_body_text = "New User Registration\n\nA new user has registered on Tefinitely:\nName: {$first_name} {$last_name}\nEmail: {$email}\nDate: " . date('Y-m-d H:i:s');

    // Get sender_email from db_config.php indirectly if needed, but sendEmail uses it by default as support email
    sendEmail('tefinitely@gmail.com', $support_subject, $support_body_html, $support_body_text);

    http_response_code(201); // Created
    echo json_encode(['status' => 'success', 'message' => 'User registered successfully. Please check your email to verify your account.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to register user.']);
}

$stmt->close();
$conn->close();
?>

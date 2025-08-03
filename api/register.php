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

// Insert the new user
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

if ($stmt->execute()) {
    // Send welcome email
    require_once __DIR__ . '/services/EmailService.php';
    $subject = "Welcome to Tefinitely!";
    $body_html = "<h1>Welcome, {$first_name}!</h1><p>Thank you for registering. We are excited to have you on board.</p>";
    $body_text = "Welcome, {$first_name}!\nThank you for registering. We are excited to have you on board.";
    sendEmail($email, $subject, $body_html, $body_text);

    http_response_code(201); // Created
    echo json_encode(['status' => 'success', 'message' => 'User registered successfully.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Failed to register user.']);
}

$stmt->close();
$conn->close();
?>

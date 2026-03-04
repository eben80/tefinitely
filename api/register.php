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
    // Collect user metadata
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $os = "Unknown OS";
    $country = "Unknown Country";

    // Simple OS detection
    $os_array = [
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    ];
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os = $value;
            break;
        }
    }

    // Geolocation using ipapi.co (free tier)
    if ($ip_address !== 'Unknown' && $ip_address !== '127.0.0.1' && $ip_address !== '::1') {
        $geo_context = stream_context_create(['http' => ['timeout' => 2]]);
        $geo_data = @file_get_contents("https://ipapi.co/{$ip_address}/json/", false, $geo_context);
        if ($geo_data) {
            $geo_json = json_decode($geo_data, true);
            $country = $geo_json['country_name'] ?? "Unknown Country";
        }
    }

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
                              <li><strong>IP Address:</strong> {$ip_address}</li>
                              <li><strong>Country:</strong> {$country}</li>
                              <li><strong>Operating System:</strong> {$os}</li>
                              <li><strong>User Agent:</strong> {$user_agent}</li>
                              <li><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</li>
                          </ul>";
    $support_body_text = "New User Registration\n\nA new user has registered on Tefinitely:\nName: {$first_name} {$last_name}\nEmail: {$email}\nIP Address: {$ip_address}\nCountry: {$country}\nOperating System: {$os}\nUser Agent: {$user_agent}\nDate: " . date('Y-m-d H:i:s');

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

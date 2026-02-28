<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';
require_once '../services/EmailService.php';
require_once 'audit_logger.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['subject']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

$user_id = $data['user_id'];
$subject = $data['subject'];
$message_content = $data['message'];

// Fetch user email
$stmt = $conn->prepare("SELECT email, first_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

$recipient_email = $user['email'];
$first_name = $user['first_name'];

$body_html = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <h2>Hello, {$first_name}</h2>
        <p>" . nl2br(htmlspecialchars($message_content)) . "</p>
        <hr>
        <p style='font-size: 0.9rem; color: #666;'>This is a message from the TEFinitely support team.</p>
    </div>
";

$body_text = "Hello, {$first_name}\n\n" . $message_content . "\n\n---\nThis is a message from the TEFinitely support team.";

if (sendEmail($recipient_email, $subject, $body_html, $body_text)) {
    // Log the action
    logAdminAction($conn, $_SESSION['user_id'], 'send_email', $user_id, "Subject: $subject");

    echo json_encode(['status' => 'success', 'message' => 'Email sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email.']);
}

$stmt->close();
$conn->close();
?>

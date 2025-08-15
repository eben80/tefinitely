<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

require_once '../../db/db_config.php';
require_once __DIR__ . '/../services/EmailService.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $target = $data['target'] ?? '';
    $subject = $data['subject'] ?? '';
    $body_html = $data['body'] ?? '';
    $manual_emails = $data['manual_emails'] ?? '';

    if (empty($subject) || empty($body_html)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Subject and body cannot be empty.']);
        exit;
    }

    $recipients = [];
    if ($target === 'manual') {
        $emails = explode(',', $manual_emails);
        foreach ($emails as $email) {
            $trimmed_email = trim($email);
            if (filter_var($trimmed_email, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = $trimmed_email;
            }
        }
    } else {
        $query = "";
        if ($target === 'active') {
            $query = "SELECT email FROM users WHERE subscription_status = 'active'";
        } elseif ($target === 'inactive') {
            $query = "SELECT email FROM users WHERE subscription_status = 'inactive'";
        } elseif ($target === 'all') {
            $query = "SELECT email FROM users";
        }

        if (!empty($query)) {
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $recipients[] = $row['email'];
            }
        }
    }

    if (empty($recipients)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No valid recipients found for the selected target.']);
        exit;
    }

    // Convert HTML body to a plain text version
    $body_text = strip_tags($body_html);

    $sent_count = 0;
    $failed_count = 0;

    foreach ($recipients as $recipient) {
        if (sendEmail($recipient, $subject, $body_html, $body_text)) {
            $sent_count++;
        } else {
            $failed_count++;
        }
        // Add a small delay to avoid hitting sending limits
        usleep(100000); // 100ms
    }

    if ($sent_count > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "Emails sent: {$sent_count}, Failed: {$failed_count}."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => "Failed to send any emails. Please check server logs and email configuration."
        ]);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>

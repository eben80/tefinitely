<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['subject']) || !isset($data['message'])) {
        throw new Exception('Missing required fields.', 400);
    }

    $subject = trim($data['subject']);
    $message = trim($data['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $email = isset($data['email']) ? trim($data['email']) : '';

    if (empty($subject) || empty($message)) {
        throw new Exception('Subject and message are required.', 400);
    }

    if (!$user_id && empty($email)) {
        throw new Exception('Email is required for guest users.', 400);
    }

    // If user is logged in, use their email from the database if not provided
    if ($user_id && empty($email)) {
        $stmt_user = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_res = $stmt_user->get_result()->fetch_assoc();
        $email = $user_res['email'];
        $stmt_user->close();
    }

    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $email, $subject, $message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Your support request has been submitted.']);
    } else {
        throw new Exception('Failed to submit support request: ' . $conn->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

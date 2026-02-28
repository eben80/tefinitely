<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';
require_once 'audit_logger.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.', 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['ticket_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields.', 400);
    }

    $ticket_id = (int)$data['ticket_id'];
    $new_status = $data['status'];
    $allowed_statuses = ['open', 'in-progress', 'resolved'];

    if (!in_array($new_status, $allowed_statuses)) {
        throw new Exception('Invalid status.', 400);
    }

    $stmt = $conn->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $ticket_id);

    if ($stmt->execute()) {
        // Fetch ticket info for logging
        $stmt_info = $conn->prepare("SELECT user_id, subject FROM support_tickets WHERE id = ?");
        $stmt_info->bind_param("i", $ticket_id);
        $stmt_info->execute();
        $ticket_info = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();

        logAdminAction(
            $conn,
            $_SESSION['user_id'],
            'update_ticket_status',
            $ticket_info['user_id'],
            "Updated ticket #$ticket_id ('{$ticket_info['subject']}') status to $new_status"
        );

        echo json_encode(['status' => 'success', 'message' => 'Ticket status updated.']);
    } else {
        throw new Exception('Failed to update ticket status: ' . $conn->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $interval = filter_input(INPUT_POST, 'interval_minutes', FILTER_VALIDATE_INT);

    if ($id !== false && $interval !== false && $interval > 0) {
        $stmt = $pdo->prepare("UPDATE monitors SET interval_minutes = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$interval, $id, $_SESSION['user_id']]);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID or interval. Interval must be a positive integer.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

require_once '../db/db_config.php';

$result = $conn->query("SELECT id, dialogue_name FROM dialogues ORDER BY id");

$dialogues = [];
while ($row = $result->fetch_assoc()) {
    $dialogues[] = $row;
}

echo json_encode($dialogues, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

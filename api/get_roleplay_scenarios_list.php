<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
    exit;
}

require_once '../db/db_config.php';

$result = $conn->query("SELECT id, name FROM roleplay_scenarios ORDER BY id");

$scenarios = [];
while ($row = $result->fetch_assoc()) {
    $scenarios[] = $row;
}

echo json_encode($scenarios, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

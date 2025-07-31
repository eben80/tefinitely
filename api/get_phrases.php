<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if the user is authenticated and has an active subscription
if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403); // Forbidden
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. You need an active subscription to view this content.'
    ]);
    exit;
}

require_once '../db/db_config.php';

$mode = $_GET['mode'] ?? '';
$main_topic = $_GET['main_topic'] ?? '';
$sub_topic = $_GET['sub_topic'] ?? null;

if (empty($mode) || empty($main_topic)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Mode and main topic are required.']);
    exit;
}

if ($sub_topic) {
    $stmt = $conn->prepare("SELECT french_text, english_translation FROM phrases WHERE mode = ? AND main_topic = ? AND sub_topic = ?");
    $stmt->bind_param("sss", $mode, $main_topic, $sub_topic);
} else {
    $stmt = $conn->prepare("SELECT french_text, english_translation FROM phrases WHERE mode = ? AND main_topic = ?");
    $stmt->bind_param("ss", $mode, $main_topic);
}
$stmt->execute();
$result = $stmt->get_result();

$phrases = [];
while ($row = $result->fetch_assoc()) {
    $phrases[] = $row;
}

echo json_encode($phrases, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

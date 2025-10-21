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

$theme = $_GET['theme'] ?? '';
$section = $_GET['section'] ?? '';
$level = $_GET['level'] ?? '';

$stmt = $conn->prepare("SELECT id, french_text, english_translation FROM phrases WHERE theme = ? AND section = ? AND level = ?");
$stmt->bind_param("sss", $theme, $section, $level);
$stmt->execute();
$result = $stmt->get_result();

$phrases = [];
while ($row = $result->fetch_assoc()) {
    $phrases[] = $row;
}

echo json_encode($phrases, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

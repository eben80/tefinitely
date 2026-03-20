<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id, url, last_checked, last_changed, is_paused, interval_minutes, last_hash, created_at FROM monitors WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$monitors = $stmt->fetchAll();

echo json_encode(['status' => 'success', 'monitors' => $monitors]);
?>

<?php
require_once __DIR__ . '/../../db/db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$phrase_id = isset($data['phrase_id']) ? $data['phrase_id'] : null;
$matching_quality = isset($data['matching_quality']) ? $data['matching_quality'] : null;

if ($phrase_id === null || $matching_quality === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing phrase_id or matching_quality']);
    exit;
}

$sql = "INSERT INTO user_progress (user_id, phrase_id, matching_quality) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE matching_quality = VALUES(matching_quality)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $user_id, $phrase_id, $matching_quality);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User progress stored successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to store user progress']);
}

$stmt->close();
$conn->close();
?>

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to store progress.']);
    exit;
}

require_once '../../db/db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $user_id = $_SESSION['user_id'];
    $dialogue_id = $data['dialogue_id'] ?? null;
    $line_id = $data['line_id'] ?? null;
    $score = $data['score'] ?? null;

    if ($dialogue_id === null || $line_id === null || $score === null) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields: dialogue_id, line_id, and score.']);
        exit;
    }

    // UPSERT operation: Insert a new record, or update the existing one if the user_id and line_id composite key exists.
    $query = "
        INSERT INTO dialogue_progress (user_id, dialogue_id, line_id, score, attempts)
        VALUES (?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE
            score = VALUES(score),
            attempts = attempts + 1
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("iiid", $user_id, $dialogue_id, $line_id, $score);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Progress saved successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save progress: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>

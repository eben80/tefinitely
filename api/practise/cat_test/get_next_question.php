<?php
require_once '../../../api/session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../db/db_config.php';

if (!isset($_SESSION['cat_test'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No active CAT session.']);
    exit;
}

$theta = $_SESSION['cat_test']['theta'];
$answered_ids = $_SESSION['cat_test']['answered_ids'];

// Maximum Information Selection (Simplified): Select a question with difficulty (b) closest to the current theta estimate.
$placeholders = count($answered_ids) > 0 ? implode(',', array_fill(0, count($answered_ids), '?')) : 'NULL';
$query = "SELECT id, competency, cefr_target, estimated_difficulty, stem, option_a, option_b, option_c, option_d
          FROM cat_questions
          WHERE id NOT IN ($placeholders)
          ORDER BY ABS(estimated_difficulty - ?) ASC
          LIMIT 1";

$stmt = $conn->prepare($query);

if (count($answered_ids) > 0) {
    $types = str_repeat('s', count($answered_ids)) . 'd';
    $params = array_merge($answered_ids, [$theta]);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('d', $theta);
}

$stmt->execute();
$result = $stmt->get_result();
$question = $result->fetch_assoc();

if (!$question) {
    echo json_encode(['status' => 'success', 'finished' => true, 'message' => 'Question pool exhausted.']);
    exit;
}

// Don't send the correct answer to the client!
echo json_encode([
    'status' => 'success',
    'finished' => false,
    'question' => $question,
    'total_answered' => count($answered_ids)
]);

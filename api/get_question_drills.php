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

// For now, fetch all drills. Later, we can filter by theme/section.
$theme = $_GET['theme'] ?? '';
$section = $_GET['section'] ?? '';

// Basic query without filtering for now
$stmt = $conn->prepare("SELECT id, english_question, french_vocab_hints, expected_answer FROM question_drills");
// Later, we could add:
// $stmt = $conn->prepare("SELECT ... FROM question_drills WHERE theme = ? AND section = ?");
// $stmt->bind_param("ss", $theme, $section);

$stmt->execute();
$result = $stmt->get_result();

$drills = [];
while ($row = $result->fetch_assoc()) {
    $drills[] = $row;
}

echo json_encode($drills, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

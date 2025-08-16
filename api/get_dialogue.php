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

// For now, we'll fetch a specific dialogue. Later, this can be dynamic.
$dialogue_id = $_GET['id'] ?? 1; // Default to the first dialogue

$stmt = $conn->prepare(
    "SELECT d.id as dialogue_id, d.dialogue_name, dl.id as line_id, dl.speaker, dl.line_text, dl.line_order
     FROM dialogues d
     JOIN dialogue_lines dl ON d.id = dl.dialogue_id
     WHERE d.id = ?
     ORDER BY dl.line_order ASC"
);
$stmt->bind_param("i", $dialogue_id);
$stmt->execute();
$result = $stmt->get_result();

$dialogue = [
    'id' => $dialogue_id,
    'name' => '',
    'lines' => []
];

$first_row = true;
while ($row = $result->fetch_assoc()) {
    if ($first_row) {
        $dialogue['name'] = $row['dialogue_name'];
        $first_row = false;
    }
    $dialogue['lines'][] = [
        'id' => $row['line_id'],
        'speaker' => $row['speaker'],
        'line' => $row['line_text']
    ];
}

echo json_encode($dialogue, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

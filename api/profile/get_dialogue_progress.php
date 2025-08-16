<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to view progress.']);
    exit;
}

require_once '../../db/db_config.php';

$user_id = $_SESSION['user_id'];

// This query calculates the progress for each dialogue for the specific user.
// It counts the total lines in each dialogue and joins it with the user's progress.
$query = "
    SELECT
        d.id AS dialogue_id,
        d.dialogue_name,
        (SELECT COUNT(*) FROM dialogue_lines WHERE dialogue_id = d.id) AS total_lines,
        COUNT(dp.id) AS attempted_lines,
        AVG(dp.score) AS average_score
    FROM
        dialogues d
    LEFT JOIN
        dialogue_progress dp ON d.id = dp.dialogue_id AND dp.user_id = ?
    GROUP BY
        d.id, d.dialogue_name
    ORDER BY
        d.id;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$progress_data = [];
while ($row = $result->fetch_assoc()) {
    $coverage = 0;
    if ($row['total_lines'] > 0) {
        $coverage = ($row['attempted_lines'] / $row['total_lines']);
    }

    $progress_data[] = [
        'dialogue_id' => $row['dialogue_id'],
        'dialogue_name' => $row['dialogue_name'],
        'coverage' => $coverage,
        'average_score' => $row['average_score'] ? (float)$row['average_score'] : 0,
        'attempted_lines' => (int)$row['attempted_lines'],
        'total_lines' => (int)$row['total_lines']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'progress' => $progress_data]);
?>

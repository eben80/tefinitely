<?php
require_once __DIR__ . '/../../db/db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT p.theme, p.section, COUNT(up.id) as phrases_covered, AVG(up.matching_quality) as average_matching_quality
        FROM user_progress up
        JOIN phrases p ON up.phrase_id = p.id
        WHERE up.user_id = ?
        GROUP BY p.theme, p.section";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$progress = [];
while ($row = $result->fetch_assoc()) {
    $progress[] = $row;
}

echo json_encode(['status' => 'success', 'progress' => $progress]);

$stmt->close();
$conn->close();
?>

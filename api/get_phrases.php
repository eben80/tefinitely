<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php';

$theme = $_GET['theme'] ?? '';
$section = $_GET['section'] ?? 'general';

$stmt = $conn->prepare("SELECT french_text, english_translation FROM phrases WHERE theme = ? AND section = ?");
$stmt->bind_param("ss", $theme, $section);
$stmt->execute();
$result = $stmt->get_result();

$phrases = [];
while ($row = $result->fetch_assoc()) {
    $phrases[] = $row;
}

echo json_encode($phrases, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

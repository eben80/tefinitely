<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php';

try {
    $query = "SELECT DISTINCT section, theme FROM phrases ORDER BY section, theme";
    $result = $conn->query($query);

    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $section = $row['section'];
        $theme = $row['theme'];

        if (!isset($topics[$section])) {
            $topics[$section] = [];
        }
        $topics[$section][] = $theme;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'topics' => $topics]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch topics.']);
}

$conn->close();
?>

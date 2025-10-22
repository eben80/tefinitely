<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php'; // Correct path for db config

try {
    // Select all distinct topic information
    $query = "SELECT DISTINCT section, theme, level, topic_en, topic_fr FROM phrases ORDER BY section, theme";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $section = $row['section'];

        // Initialize the section array if it doesn't exist
        if (!isset($topics[$section])) {
            $topics[$section] = [];
        }

        // Add the full topic details to the section
        $topics[$section][] = [
            'theme' => $row['theme'],
            'level' => $row['level'],
            'topic_en' => $row['topic_en'],
            'topic_fr' => $row['topic_fr']
        ];
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'topics' => $topics]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch topics: ' . $e->getMessage()]);
}

$conn->close();
?>

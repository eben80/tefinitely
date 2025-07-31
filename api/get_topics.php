<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db/db_config.php';

try {
    $query = "SELECT DISTINCT mode, main_topic, sub_topic FROM phrases ORDER BY mode, main_topic, sub_topic";
    $result = $conn->query($query);

    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $mode = $row['mode'];
        $main_topic = $row['main_topic'];
        $sub_topic = $row['sub_topic'];

        if (!isset($topics[$mode])) {
            $topics[$mode] = [];
        }
        if (!isset($topics[$mode][$main_topic])) {
            $topics[$mode][$main_topic] = [];
        }
        if (!empty($sub_topic) && !in_array($sub_topic, $topics[$mode][$main_topic])) {
            $topics[$mode][$main_topic][] = $sub_topic;
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'topics' => $topics]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch topics.']);
}

$conn->close();
?>

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. You need an active subscription to view this content.']);
    exit;
}

require_once '../db/db_config.php';

try {
    $section_filter = isset($_GET['section']) ? $_GET['section'] : null;

    if ($section_filter) {
        $section_db_value = "Section " . $section_filter;
        $stmt = $conn->prepare("SELECT DISTINCT theme FROM phrases WHERE section = ? ORDER BY theme");
        $stmt->bind_param("s", $section_db_value);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT DISTINCT section, theme FROM phrases ORDER BY section, theme";
        $result = $conn->query($query);
    }

    $topics = [];
    if ($section_filter) {
        $topics[$section_filter] = [];
        while ($row = $result->fetch_assoc()) {
            $topics[$section_filter][] = $row['theme'];
        }
    } else {
        while ($row = $result->fetch_assoc()) {
            $section = $row['section'];
            $theme = $row['theme'];

            if (!isset($topics[$section])) {
                $topics[$section] = [];
            }
            $topics[$section][] = $theme;
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

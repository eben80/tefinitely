<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. You need an active subscription to view this content.']);
    exit;
}

require_once __DIR__ . '/../services/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $section_filter = isset($_GET['section']) ? $_GET['section'] : null;
    $topics = [];

    if ($section_filter) {
        $stmt = $conn->prepare("SELECT DISTINCT theme FROM phrases WHERE section = :section ORDER BY theme");
        $stmt->bindParam(':section', $section_filter, PDO::PARAM_STR);
        $stmt->execute();

        $themes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $topics[$section_filter] = $themes;

    } else {
        $query = "SELECT DISTINCT section, theme FROM phrases ORDER BY section, theme";
        $stmt = $conn->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
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
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch topics: ' . $e->getMessage()]);
}

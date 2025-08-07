<?php
require_once __DIR__ . '/../../db/db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Fetch all unique themes and sections, and their total phrase counts
$all_topics_sql = "SELECT
                        theme,
                        section,
                        COUNT(*) as total_phrases
                   FROM phrases
                   GROUP BY theme, section";
$all_topics_result = $conn->query($all_topics_sql);
$all_topics = [];
while ($row = $all_topics_result->fetch_assoc()) {
    $all_topics[] = $row;
}

// 2. Fetch user's actual progress
$user_progress_sql = "SELECT
                        p.theme,
                        p.section,
                        COUNT(DISTINCT up.phrase_id) as phrases_covered,
                        AVG(up.matching_quality) as average_matching_quality
                      FROM user_progress up
                      JOIN phrases p ON up.phrase_id = p.id
                      WHERE up.user_id = ?
                      GROUP BY p.theme, p.section";
$stmt = $conn->prepare($user_progress_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_progress_result = $stmt->get_result();

$user_progress_map = [];
while ($row = $user_progress_result->fetch_assoc()) {
    $key = $row['theme'] . '|' . $row['section'];
    $user_progress_map[$key] = $row;
}
$stmt->close();

// 3. Combine the two lists
$final_progress = [];
foreach ($all_topics as $topic) {
    $key = $topic['theme'] . '|' . $topic['section'];
    if (isset($user_progress_map[$key])) {
        // User has progress for this topic
        $progress_item = $user_progress_map[$key];
        $final_progress[] = [
            'theme' => $topic['theme'],
            'section' => $topic['section'],
            'phrases_covered' => (int)$progress_item['phrases_covered'],
            'average_matching_quality' => (float)$progress_item['average_matching_quality'],
            'total_phrases' => (int)$topic['total_phrases']
        ];
    } else {
        // User has no progress for this topic, add default entry
        $final_progress[] = [
            'theme' => $topic['theme'],
            'section' => $topic['section'],
            'phrases_covered' => 0,
            'average_matching_quality' => 0,
            'total_phrases' => (int)$topic['total_phrases']
        ];
    }
}

echo json_encode(['status' => 'success', 'progress' => $final_progress]);

$conn->close();
?>

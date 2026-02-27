<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$user_id = $_GET['user_id'] ?? null;
$timeframe = $_GET['timeframe'] ?? 'lifetime';

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
    exit;
}

$where_clause = "user_id = ?";
switch ($timeframe) {
    case '1h':
        $where_clause .= " AND created_at >= NOW() - INTERVAL 1 HOUR";
        break;
    case '24h':
        $where_clause .= " AND created_at >= NOW() - INTERVAL 24 HOUR";
        break;
    case '7d':
        $where_clause .= " AND created_at >= NOW() - INTERVAL 7 DAY";
        break;
    case '30d':
        $where_clause .= " AND created_at >= NOW() - INTERVAL 30 DAY";
        break;
    case 'lifetime':
    default:
        // No additional filtering
        break;
}

$stmt = $conn->prepare("SELECT openai_id, model, created_at FROM openai_calls_log WHERE $where_clause ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$calls = [];
while ($row = $result->fetch_assoc()) {
    $calls[] = $row;
}

echo json_encode(['status' => 'success', 'calls' => $calls]);

$stmt->close();
$conn->close();
?>

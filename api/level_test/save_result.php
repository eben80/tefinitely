<?php
require_once '../../api/session_init.php';
init_session();
require_once '../../db/db_config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$score = $data['score'] ?? null;
$estimated_level = $data['estimated_level'] ?? null;
$test_type = $data['test_type'] ?? 'vocabulary';

if ($score === null || !$estimated_level) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing score or estimated level']);
    exit;
}

// 1. Save result to history
$stmt = $conn->prepare("INSERT INTO level_test_results (user_id, score, estimated_level, test_type) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $user_id, $score, $estimated_level, $test_type);
$stmt->execute();
$stmt->close();

// 2. Update user's next allowed test date (7 days from now)
// Check if user is admin - admins are not restricted
$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_res = $role_stmt->get_result()->fetch_assoc();
$role_stmt->close();

if ($role_res['role'] !== 'admin') {
    $next_allowed = date('Y-m-d H:i:s', strtotime('+7 days'));
    $update_stmt = $conn->prepare("UPDATE users SET next_test_allowed_at = ? WHERE id = ?");
    $update_stmt->bind_param("si", $next_allowed, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

echo json_encode(['status' => 'success', 'message' => 'Result saved successfully']);

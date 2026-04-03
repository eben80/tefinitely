<?php
require_once '../../../api/session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../db/db_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check eligibility (same 7-day rule as standard test)
$stmt = $conn->prepare("SELECT next_test_allowed_at, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row['role'] !== 'admin') {
    $now = new DateTime();
    $next_allowed = $row['next_test_allowed_at'] ? new DateTime($row['next_test_allowed_at']) : null;
    if ($next_allowed && $now < $next_allowed) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You must wait 7 days between tests.']);
        exit;
    }
}

// Initialize session variables
$_SESSION['cat_test'] = [
    'user_id' => $user_id,
    'theta' => 0.0, // Starting at medium (B1)
    'sem' => 1.0,   // Initial standard error
    'answered_ids' => [],
    'responses' => [],
    'start_time' => date('Y-m-d H:i:s'),
    'status' => 'in_progress'
];

echo json_encode(['status' => 'success', 'message' => 'Session initialized']);

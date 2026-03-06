<?php
require_once '../../api/session_init.php';
init_session();
require_once '../../db/db_config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. You need an active subscription to take this test.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check eligibility
$stmt = $conn->prepare("SELECT role, next_test_allowed_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user_res['role'] !== 'admin') {
    if ($user_res['next_test_allowed_at'] && new DateTime() < new DateTime($user_res['next_test_allowed_at'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Test limit reached. You can only take the test once every 7 days.']);
        exit;
    }
}

// Fetch vocabulary questions from database
// Note: Only vocabulary test is supported as per requirement.
$query = "SELECT id, question, option_a as A, option_b as B, option_c as C, option_d as D, correct_option as correct, level FROM level_test_questions WHERE test_type = 'vocabulary'";
$result = $conn->query($query);

$questions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'id' => (int)$row['id'],
            'question' => $row['question'],
            'options' => [
                'A' => $row['A'],
                'B' => $row['B'],
                'C' => $row['C'],
                'D' => $row['D']
            ],
            'correct' => $row['correct'],
            'level' => $row['level']
        ];
    }
    echo json_encode(['status' => 'success', 'questions' => $questions]);
} else {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Question pool is currently empty. Please contact an administrator.']);
}

$conn->close();

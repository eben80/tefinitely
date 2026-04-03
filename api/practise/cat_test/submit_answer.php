<?php
require_once '../../../api/session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../db/db_config.php';

if (!isset($_SESSION['cat_test'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No active CAT session.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$question_id = $data['question_id'] ?? '';
$answer = $data['answer'] ?? '';

if (!$question_id || !$answer) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing question_id or answer.']);
    exit;
}

// Fetch question details including IRT parameters
$stmt = $conn->prepare("SELECT correct_option, estimated_difficulty FROM cat_questions WHERE id = ?");
$stmt->bind_param("s", $question_id);
$stmt->execute();
$q_details = $stmt->get_result()->fetch_assoc();

if (!$q_details) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Question not found.']);
    exit;
}

$is_correct = ($answer === $q_details['correct_option']);
$difficulty = (float)$q_details['estimated_difficulty'];

// --- IRT Update (Simplified 1PL/Rasch for stability) ---
// theta_new = theta_old + a * (response - P(correct))
// P(correct) = 1 / (1 + exp(-(theta - difficulty)))
$old_theta = (float)$_SESSION['cat_test']['theta'];
$learning_rate = 0.5; // K-factor equivalent

$prob_correct = 1.0 / (1.0 + exp(-($old_theta - $difficulty)));
$response_val = $is_correct ? 1.0 : 0.0;

$new_theta = $old_theta + $learning_rate * ($response_val - $prob_correct);

// --- Standard Error of Measurement (SEM) Update (Simplified) ---
// SEM = 1 / sqrt(Sum of Information)
// Info(theta) = P(theta) * (1 - P(theta))
$_SESSION['cat_test']['responses'][] = [
    'id' => $question_id,
    'difficulty' => $difficulty,
    'is_correct' => $is_correct,
    'info' => $prob_correct * (1.0 - $prob_correct)
];

$sum_info = 0;
foreach ($_SESSION['cat_test']['responses'] as $resp) {
    $sum_info += $resp['info'];
}
$new_sem = ($sum_info > 0) ? 1.0 / sqrt($sum_info) : 1.0;

// Update Session
$_SESSION['cat_test']['theta'] = $new_theta;
$_SESSION['cat_test']['sem'] = $new_sem;
$_SESSION['cat_test']['answered_ids'][] = $question_id;

$total_answered = count($_SESSION['cat_test']['answered_ids']);
$max_questions = 50;
$sem_threshold = 0.3;

$is_finished = ($new_sem < $sem_threshold || $total_answered >= $max_questions);

if ($is_finished) {
    finishTest($conn, $_SESSION['cat_test'], $new_theta);
}

echo json_encode([
    'status' => 'success',
    'is_correct' => $is_correct,
    'finished' => $is_finished,
    'new_theta' => $new_theta,
    'new_sem' => $new_sem,
    'total_answered' => $total_answered
]);

function finishTest($conn, $session_data, $final_theta) {
    $user_id = $session_data['user_id'];

    // Map theta to CEFR level
    // Typical scale: A1: < -1, A2: -1 to 0, B1: 0 to 1, B2: 1 to 2, C1: 2 to 3, C2: > 3
    $level = 'A1';
    if ($final_theta >= 3.0) $level = 'C2';
    elseif ($final_theta >= 2.0) $level = 'C1';
    elseif ($final_theta >= 1.0) $level = 'B2';
    elseif ($final_theta >= 0.0) $level = 'B1';
    elseif ($final_theta >= -1.0) $level = 'A2';

    // Calculate a "score" percentage for compatibility with existing UI (0-100)
    // Scale -3 to 4 into 0 to 100
    $score = (int)max(0, min(100, (($final_theta + 3) / 7) * 100));

    // Save result
    $stmt = $conn->prepare("INSERT INTO level_test_results (user_id, test_type, score, estimated_level) VALUES (?, 'cat_adaptive', ?, ?)");
    $stmt->bind_param("iis", $user_id, $score, $level);
    $stmt->execute();

    // Update eligibility (7 days)
    $next_allowed = date('Y-m-d H:i:s', strtotime('+7 days'));
    $stmt_user = $conn->prepare("UPDATE users SET next_test_allowed_at = ? WHERE id = ?");
    $stmt_user->bind_param("si", $next_allowed, $user_id);
    $stmt_user->execute();

    unset($_SESSION['cat_test']);
}

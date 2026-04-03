<?php
require_once '../session_init.php';
init_session();
header('Content-Type: application/json; charset=utf-8');
require_once '../../db/db_config.php';
require_once 'audit_logger.php';

// Security Check: Ensure user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data.']);
    exit;
}

$success_count = 0;
$failure_count = 0;
$errors = [];

$stmt = $conn->prepare("INSERT INTO cat_questions (id, competency, cefr_target, estimated_difficulty, stem, option_a, option_b, option_c, option_d, correct_option, rationale)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    competency = VALUES(competency),
    cefr_target = VALUES(cefr_target),
    estimated_difficulty = VALUES(estimated_difficulty),
    stem = VALUES(stem),
    option_a = VALUES(option_a),
    option_b = VALUES(option_b),
    option_c = VALUES(option_c),
    option_d = VALUES(option_d),
    correct_option = VALUES(correct_option),
    rationale = VALUES(rationale)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

foreach ($data as $index => $q) {
    // Basic validation
    if (!isset($q['id'], $q['competency'], $q['cefr_target'], $q['estimated_difficulty'], $q['stem'], $q['options'], $q['correct'])) {
        $failure_count++;
        $errors[] = "Item at index $index is missing required fields.";
        continue;
    }

    $id = $q['id'];
    $competency = $q['competency'];
    $cefr_target = $q['cefr_target'];
    $difficulty = (float)$q['estimated_difficulty'];
    $stem = $q['stem'];
    $oa = $q['options']['A'] ?? '';
    $ob = $q['options']['B'] ?? '';
    $oc = $q['options']['C'] ?? '';
    $od = $q['options']['D'] ?? '';
    $correct = $q['correct'];
    $rationale = $q['rationale'] ?? null;

    $stmt->bind_param("sssdsssssss", $id, $competency, $cefr_target, $difficulty, $stem, $oa, $ob, $oc, $od, $correct, $rationale);

    if ($stmt->execute()) {
        $success_count++;
    } else {
        $failure_count++;
        $errors[] = "Error inserting item $id: " . $stmt->error;
    }
}

$stmt->close();

if ($success_count > 0) {
    logAdminAction($conn, $_SESSION['user_id'], 'import_cat_questions', null, "Imported/Updated $success_count questions. Failures: $failure_count");
}

echo json_encode([
    'status' => 'success',
    'message' => "Import complete. $success_count successful, $failure_count failed.",
    'success_count' => $success_count,
    'failure_count' => $failure_count,
    'errors' => $errors
]);

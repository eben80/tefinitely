<?php
require_once '../../api/session_init.php';
init_session();
require_once __DIR__ . '/../../practise/tef_canada/section_a/api/openai.php';

set_time_limit(180); // Increase time limit for OpenAI generation
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. You need an active subscription to take this test.']);
    exit;
}

require_once '../../db/db_config.php';
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

// Note: Oral expression test has been removed. Only vocabulary is supported now.
$type = 'vocabulary';

$systemPrompt = "Expert French examiner. Generate a pool of 60 multiple-choice questions (10 per level A1-C2) for French VOCABULARY.

CRITICAL: ABSOLUTELY NO English/French cognates (endings like -tion, -ssion, -ité, -té, -able, -ible, -ent, -ant, -al, -el, -isme, -iste, -ure, -ence, -ance are BANNED). No Latin-root words used in English (e.g., avoid étudiant, possible, famille). Use Gallic roots (e.g., boulot, souhaiter, essuyer). For B2-C2, use uniquely French idioms (e.g., déclic, rabrouer, mitigé).

Each object in the JSON array:
{
  \"id\": int,
  \"question\": \"Short sentence with blank or simple question\",
  \"options\": {\"A\": \"...\", \"B\": \"...\", \"C\": \"...\", \"D\": \"...\"},
  \"correct\": \"A/B/C/D\",
  \"level\": \"A1-C2\"
}

Return ONLY the JSON array of 60 objects.";

$messages = [
    ["role" => "system", "content" => $systemPrompt],
    ["role" => "user", "content" => "Generate the 60 questions in French now."]
];

$response = openai_chat($messages);
$raw = trim($response['content'] ?? '');

// Try to extract JSON from the response if it's wrapped in markers
if (preg_match('/\[.*\]/s', $raw, $matches)) {
    $jsonText = $matches[0];
    $questions = json_decode($jsonText, true);
    if ($questions && count($questions) >= 50) { // Allow some flexibility but target 60
        echo json_encode(['status' => 'success', 'questions' => $questions]);
        exit;
    }
}

// Fallback if JSON parsing fails or not enough questions
http_response_code(500);
echo json_encode(['status' => 'error', 'message' => 'Failed to generate questions. Please try again.', 'debug' => $raw]);

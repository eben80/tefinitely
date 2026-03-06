<?php
require_once '../../api/session_init.php';
init_session();
require_once __DIR__ . '/../../practise/tef_canada/section_a/api/openai.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['subscription_status']) || $_SESSION['subscription_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. You need an active subscription to take this test.']);
    exit;
}

// Note: Oral expression test has been removed. Only vocabulary is supported now.
$type = 'vocabulary';

$systemPrompt = "You are an expert French language examiner. Your task is to generate a pool of 60 multiple-choice questions to determine a user's CEFR level (A1 to C2) in French VOCABULARY.

The pool must contain exactly 10 questions for each of the following levels:
- A1 (very basic)
- A2 (elementary)
- B1 (intermediate)
- B2 (upper intermediate)
- C1 (advanced)
- C2 (mastery)

CRITICAL ANTI-COGNATE CONSTRAINT: You MUST avoid all words that have similar spellings or sounds in English and French.
1. ABSOLUTELY BANNED: Any word ending in -tion, -ssion, -ité, -té, -able, -ible, -ent, -ant, -al, -el, -isme, -iste, -ure, -ence, -ance.
2. ABSOLUTELY BANNED: Latin-root words that exist in English (e.g., \"étudiant\", \"possible\", \"famille\", \"difficile\", \"étudier\").
3. PRIORITY: Use words with Germanic/Gallic roots (e.g., \"souhaiter\" instead of \"désirer\", \"boulot\" instead of \"travail\", \"essuyer\", \"accrocher\", \"éteindre\").
4. SCOPE: This applies to the question text, the correct answer, and ALL three distractors.
5. HIGHER LEVELS (B2-C2): Use sophisticated, uniquely French idioms and specific nouns/verbs that have no direct morphological equivalent in English (e.g., \"déclic\", \"rabrouer\", \"mitigé\", \"louper\", \"pénible\").

Each question must have:
- A clear question or a sentence with a blank.
- 4 options (A, B, C, D).
- Exactly one correct answer.
- A difficulty level (A1, A2, B1, B2, C1, C2).

Respond ONLY with a JSON array of 60 objects like this:
[
  {
    \"id\": 1,
    \"question\": \"Question text here...\",
    \"options\": {
      \"A\": \"Option A\",
      \"B\": \"Option B\",
      \"C\": \"Option C\",
      \"D\": \"Option D\"
    },
    \"correct\": \"A\",
    \"level\": \"A1\"
  },
  ...
]";

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

// Fallback if JSON parsing fails or not 20 questions
http_response_code(500);
echo json_encode(['status' => 'error', 'message' => 'Failed to generate questions. Please try again.', 'debug' => $raw]);
